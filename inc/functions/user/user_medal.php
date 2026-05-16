<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime : 2025-12-23 15:06:22
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户徽章相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 为用户添加徽章
 * @param {*} $user_id
 * @param {*} $medal_name
 * @param {*} $desc 备注
 * @return {*}
 */
function zib_add_user_medal($user_id, $medal_name, $remarks = '')
{
    $medal = zib_get_user_meta($user_id, 'medal_details', true);

    //新版做兼容处理
    if (!$medal && !is_array(get_user_meta($user_id, 'zib_other_data', true))) {
        $medal = get_user_meta($user_id, 'medal_details', true);
    }

    if (!$medal || !is_array($medal)) {
        $medal = array();
    }

    //不能重复添加
    if (isset($medal[$medal_name])) {
        return false;
    }

    //不能添加不存在勋章
    $medal_args = zib_get_single_medal_args($medal_name);
    if (!$medal_args) {
        return false;
    }

    $new = array(
        'name'    => $medal_name,
        'time'    => current_time('Y-m-d H:i:s'),
        'remarks' => $remarks,
    );

    //添加挂钩
    do_action('user_add_medal', $user_id, array_merge($new, $medal_args));
    $medal = array_merge(array($medal_name => $new), $medal);
    return zib_update_user_meta($user_id, 'medal_details', $medal);
}

/**
 * @description: 用户获得徽章给用户发消息
 * @param {*} $medal_name
 * @param {*} $medal_args
 * @return {*}
 */
function zib_user_add_medal_new_msg($user_id, $medal_args)
{
    if (!zib_msg_is_allow_receive($user_id, 'medal')) {
        return;
    }

    $title   = '恭喜您！获得新的徽章[' . $medal_args['name'] . ']';
    $message = '<div class="c-blue text-center mb20">恭喜您！获得新的徽章</div>';
    $message .= '<div class="mb20 medal-card medal-single-card relative" style="max-width: 360px;margin: auto;"><div class="medal-single-bg"><img class="fit-cover" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/medal/medal-background.svg" alt="徽章"></div><div class="relative"><img class="img-icon medal-icon mt20" src="' . $medal_args['icon'] . '" alt="徽章-' . $medal_args['name'] . '"><div class="mt6">' . $medal_args['name'] . '</div><div class="muted-2-color px12 mt6">' . $medal_args['desc'] . '</div><div class="c-blue px12 mt6">' . $medal_args['time'] . ' 获得</div></div></div>';
    $message .= '<div class="text-center" style="max-width: 360px;margin: auto;">' . zib_get_user_medal_info_link($user_id, 'mt20 but btn-block radius c-blue-2', zib_get_svg('medal-color') . '查看我的徽章') . '</div>';
    $message .= '';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'medal',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);
}
add_action('user_add_medal', 'zib_user_add_medal_new_msg', 10, 2);

/**
 * @description: 收回用户徽章
 * @param {*} $user_id
 * @param {*} $medal_name
 * @return {*}
 */
function zib_remove_user_medal($user_id, $medal_name)
{
    $medal = zib_get_user_meta($user_id, 'medal_details', true);

    //不能重复添加
    if (!isset($medal[$medal_name])) {
        return false;
    }

    unset($medal[$medal_name]);

    //添加挂钩
    do_action('user_remove_medal', $medal_name);
    return zib_update_user_meta($user_id, 'medal_details', $medal);
}

/**
 * @description: 获取用户的某一个徽章，可作为是否取得吗，某个徽章的判断函数
 * @param {*} $user_id
 * @param {*} $medal_name
 * @return {*}
 */
function zib_get_user_medal($user_id, $medal_name)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $medal_details = zib_get_user_medal_details($user_id);

    return isset($medal_details[$medal_name]) ? $medal_details[$medal_name] : false;
}

/**
 * @description: 获取用户获得的徽章明细
 * @param {*} $id
 * @return {*}
 */
function zib_get_user_medal_details($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    //声明静态变量，加速获取
    static $user_medal_details = array();
    if (isset($user_medal_details[$user_id])) {
        return $user_medal_details[$user_id];
    }

    $medal = zib_get_user_meta($user_id, 'medal_details', true);
    $new   = array();
    if ($medal && is_array($medal)) {
        $medal_args = zib_get_medal_args('item');
        foreach ($medal as $key => $val) {
            if (isset($medal_args[$key])) {
                $new[$key] = array_merge($val, $medal_args[$key]);
            }
        }
    }

    $user_medal_details[$user_id] = $new;
    return $new;
}

/**
 * @description: 获取用户佩戴的徽章
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_wear_medal_args($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $wear_medal = get_user_meta($user_id, 'wear_medal', true);
    $user_medal = zib_get_user_medal_details($user_id);

    if ($wear_medal && isset($user_medal[$wear_medal])) {
        return $user_medal[$wear_medal];
    }

    //如果用户未设置过固定佩戴则默认佩戴最新获取的徽章
    if (!$wear_medal && $user_medal) {
        foreach ($user_medal as $key => $val) {
            return $val;
        }
    }
    return false;
}

/**
 * @description: 获取单个徽章的参数
 * @param {*} $name
 * @return {*}
 */
function zib_get_single_medal_args($name)
{
    $medal_args = zib_get_medal_args('item');

    return (isset($medal_args[$name])) ? $medal_args[$name] : false;
}

/**
 * @description: 获取徽章配置参数
 * @param {*} $type 类型：item|cat|get
 * @return {*}
 */
function zib_get_medal_args($type = 'item')
{

    if (!_pz('user_medal_s')) {
        return false;
    }

    //先从缓存获取
    $cache = wp_cache_get('user_medal_args', 'zib_cache_group');
    if ($cache !== false && isset($cache['cat'])) {
        return $type ? $cache[$type] : $type;
    }

    //通过配置数据查询
    $args      = _pz('user_medal_args');
    $args      = apply_filters('user_medal_args', $args); //添加挂钩过滤
    $cat_args  = array();
    $item_args = array();
    $get_args  = array();

    if (isset($args[0]['cat_name'])) {
        foreach ($args as $cat) {
            $cat_name = $cat['cat_name'];
            if (isset($cat['items'][0])) {
                foreach ($cat['items'] as $item) {
                    if ($item['name'] && $item['icon'] && $item['get_type']) {
                        $item['cat']                        = $cat_name;
                        $cat_args[$cat_name][$item['name']] = $item;
                        $item_args[$item['name']]           = $item;
                        $get_args[$item['get_type']][]      = $item;
                    }
                }
            }
        }
    }

    $user_medal_args = array(
        'cat'  => $cat_args,
        'item' => $item_args,
        'get'  => $get_args,
    );

    //添加缓存
    wp_cache_set('user_medal_args', $user_medal_args, 'zib_cache_group');
    return $type ? $user_medal_args[$type] : $user_medal_args;
}

/**
 * @description: 保存主题配置的时候刷新缓存
 * @param {*}
 * @return {*}
 */
function zib_medal_args_cache_set()
{
    wp_cache_delete('user_medal_args', 'zib_cache_group');
    zib_get_medal_args();
}
add_action('csf_zibll_options_saved', 'zib_medal_args_cache_set');

/**
 * @description:
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_user_medal_info_link($user_id = 0, $class = '', $con = '徽章明细')
{
    if (!$user_id || !_pz('user_medal_s', true)) {
        return;
    }

    $class = $class ? 'user-medal-info ' . $class : 'user-medal-info';

    $query_arg = array(
        'action' => 'user_medal_info_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'full-sm',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $query_arg,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取单个徽章信息的模态框按钮
 * @param {*} $medal_name
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_single_medal_info_link($user_id, $medal_name, $class = '', $con = '')
{
    if (!$medal_name) {
        return;
    }

    $class = $class ? 'single-medal-info ' . $class : 'single-medal-info';

    $query_arg = array(
        'action'  => 'single_medal_info_modal',
        'name'    => $medal_name,
        'user_id' => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 201,
        'mobile_bottom' => true,
        'new'           => true,
        'text'          => $con,
        'query_arg'     => $query_arg,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取单个徽章的介绍的模态框
 * @param {*} $medal_name
 * @param {*} $is_current
 * @return {*}
 */
function zib_get_single_medal_info_modal($medal_name, $user_id = 0)
{
    $args = zib_get_single_medal_args($medal_name);
    if (!$args) {
        return zib_get_null('参数传入错误或没有此徽章', '30', 'null-2.svg');
    }

    $is_current = $user_id && $user_id == get_current_user_id();
    $time_html  = '';
    $wear_html  = '';
    if ($is_current) {
        //是自己
        $is_has = zib_get_user_medal($user_id, $medal_name);
        if ($is_has) {
            $time_html = '<div class="c-blue px12 mt10">' . ($is_has['time']) . ' 获得</div>';
            $wear_html = '<div class="box-body">' . zib_get_medal_wear_link($medal_name, 'but btn-block radius', '<span class="badg btn-block c-blue">当前佩戴此徽章</span>') . '</div>';
        } else {
            $time_html = '<div class="c-yellow px12 mt20">暂未获得</div>';
        }
    } elseif ($user_id && $args['get_type'] === 'manually_add') {
        //不是自己，如果拥有收回徽章权限则显示收回徽章的按钮
        $manually  = zib_get_medal_manually_remove_link($user_id, $medal_name, 'but btn-block radius c-red', '收回此徽章');
        $wear_html = $manually ? '<div class="box-body">' . $manually . '</div>' : '';
    }

    $lazy_attr = zib_get_lazy_attr('lazy_other', $args['icon'], 'img-icon medal-icon', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');

    $html = '<button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>
                <div class="box-body medal-card mb20 mt20 medal-single-card relative">
                    <div class="medal-single-bg">
                        <img class="fit-cover" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/medal/medal-background.svg" alt="徽章">
                    </div>
                    <div class="relative">
                        <img ' . $lazy_attr . ' alt="徽章-' . esc_attr($args['name']) . zib_get_delimiter_blog_name() . '">
                        <div class="mt20">' . ($args['name']) . '</div>
                        <div class="muted-2-color px12 mt10">' . ($args['desc']) . '</div>' . $time_html . '
                    </div>
                </div>' . $wear_html;

    return $html;
}

/**
 * @description: 获取用户佩戴徽章的按钮
 * @param {*} $medal_name
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_medal_wear_link($medal_name, $class = 'but btn-block radius', $is_wear_html = '')
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $wear_medal_args = zib_get_user_wear_medal_args($user_id);
    $is_wear         = isset($wear_medal_args['name']) && $wear_medal_args['name'] === $medal_name;

    //不允许取消佩戴
    if ($is_wear) {
        return $is_wear_html;
    }

    $class .= ' wp-ajax-submit medal-wear-link';
    $class .= !$is_wear ? ' jb-blue' : 'c-blue';
    $name      = !$is_wear ? '佩戴此徽章' : '取消佩戴';
    $form_data = array(
        'action'  => 'user_medal_wear',
        'name'    => $medal_name,
        'user_id' => $user_id,
    );

    return '<a class="' . $class . '" form-data="' . esc_attr(json_encode($form_data)) . '" href="javascript:;">' . $name . '</a>';
}

/**
 * @description: 获取佩戴的徽章的图标
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $tooltip
 * @param {*} $link
 * @return {*}
 */
function zib_get_medal_wear_icon($user_id, $class = '', $tooltip = true, $link = true)
{
    if (!$user_id || !_pz('user_medal_s', true)) {
        return;
    }

    $wear_medal = zib_get_user_wear_medal_args($user_id);

    if (!isset($wear_medal['icon'])) {
        return;
    }
    $tooltip_attr = $tooltip ? ' data-toggle="tooltip"' : '';
    $lazy_attr    = zib_get_lazy_attr('lazy_other', $wear_medal['icon'], $class . ' img-icon medal-icon', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
    return '<img ' . $lazy_attr . $tooltip_attr . ' title="' . esc_attr($wear_medal['name']) . '"  alt="徽章-' . esc_attr($wear_medal['name']) . zib_get_delimiter_blog_name() . '">';
}

/**
 * @description: 徽章明细模态框
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_medal_info_modal($user_id)
{

    $user = get_userdata($user_id);
    if (!isset($user->display_name)) {
        return;
    }
    $current_id   = get_current_user_id();
    $display_name = $user_id != $current_id ? $user->display_name : '我';
    $avatar_img   = zib_get_avatar_box($user_id, 'avatar-img', false, false);
    $user_name    = '<span class="em09">' . $display_name . '的徽章' . '</span>';

    $wear_medal = zib_get_user_wear_medal_args($user_id);
    if ($wear_medal) {
        $lazy_attr = zib_get_lazy_attr('lazy_other', $wear_medal['icon'], 'img-icon medal-icon ml3', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
        $user_name .= '<img ' . $lazy_attr . ' data-toggle="tooltip" title="当前佩戴：' . esc_attr($wear_medal['name']) . '"  alt="徽章-' . esc_attr($wear_medal['name']) . zib_get_delimiter_blog_name() . '">';
    } else {
        $user_name .= '<span class="em09 ml6"><span class="badg-sm badg c-red">暂无徽章</span></span>';
    }

    $icon       = '';
    $header     = zib_get_modal_colorful_header('c-blue', $avatar_img, $user_name);
    $medal_info = zib_get_user_medal_info($user_id);

    if (!$medal_info) {
        $medal_info = zib_get_null('暂无徽章', '30', 'null-2.svg');
    }

    $my           = '';
    $manually_add = zib_get_user_medal_manually_add_link($user_id, 'but c-green', zib_get_svg('add-ring') . '授予徽章');
    if ($user_id != $current_id) {
        $my = zib_get_user_medal_info_link($current_id, 'but c-blue-2', zib_get_svg('medal-color') . '查看我的徽章');
    }

    $html = $header . '<div class="mini-scrollbar scroll-y max-vh5">' . $medal_info . '</div>';
    if ($my || $manually_add) {
        $html .= '<div class="modal-buts but-average">' . $my . $manually_add . '</div>';
    }

    return $html;
}

/**
 * @description: 获取用户的徽章明细
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_medal_info($user_id = 0)
{
    if (!$user_id || !_pz('user_medal_s', true)) {
        return;
    }

    $current_id = get_current_user_id();
    if ($current_id == $user_id) {
        //    return zib_get_current_user_medal_info();
    }

    $medal_details      = zib_get_user_medal_details($user_id);
    $medal_details_html = '';
    if ($medal_details || $current_id == $user_id) {
        $medal_cat_args = zib_get_medal_args('cat');
        foreach ($medal_cat_args as $k => $v) {

            $is_has_html = '';
            $no_has_html = '';
            foreach ($v as $item_k => $item_v) {
                $is_has = isset($medal_details[$item_k]);
                if ($current_id != $user_id && !$is_has) {
                    continue;
                }

                $icon_url  = $item_v['icon'];
                $lazy_attr = zib_get_lazy_attr('lazy_other', $icon_url, 'img-icon medal-icon', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');

                $icon_html = '<div class="medal-card' . ($is_has ? ' is-has' : ' no-has') . '">
                <img ' . $lazy_attr . ' alt="徽章-' . esc_attr($item_k) . zib_get_delimiter_blog_name() . '">
                <div class="muted-color px12 mt6">' . esc_attr($item_k) . '</div>
                </div>';

                if ($is_has) {
                    $is_has_html .= '<div class="medal-list">' . zib_get_single_medal_info_link($user_id, $item_k, '', $icon_html) . '</div>';
                } else {
                    $no_has_html .= '<div class="medal-list">' . zib_get_single_medal_info_link($user_id, $item_k, '', $icon_html) . '</div>';
                }

            }

            if ($is_has_html || $no_has_html) {
                $medal_details_html .= '<div class="medal-cat-box muted-box mb20">';
                $medal_details_html .= '<div class="mb20"><b class="medal-cat-name">' . $k . '</b></div>';
                $medal_details_html .= '<div class="medal-list-box flex at hh">' . $is_has_html . $no_has_html . '</div>';
                $medal_details_html .= '</div>';
            }

        }
    }

    return $medal_details_html;
}

/**
 * @description: 个人主页展示徽章
 * @param {*} $desc
 * @param {*} $user_id
 * @return {*}
 */
function zib_author_header_identity_filter_medal($desc, $user_id)
{

    $medal = zib_get_user_medal_show_link($user_id);
    if (!$medal) {
        $medal = zib_get_user_medal_manually_add_link($user_id, 'but', zib_get_svg('medal-color') . '授予徽章<i class="fa fa-angle-right" style="margin: 0 0 0 .3em;"></i>');
    }

    return $medal . $desc;
}

/**
 * @description: 用户中心展示徽章
 * @param {*} $desc
 * @param {*} $user_id
 * @return {*}
 */
function zib_user_page_header_desc_filter_medal($desc, $user_id)
{

    $my = zib_get_user_medal_show_link($user_id);
    if (!$my) {
        $my = zib_get_user_medal_info_link($user_id, 'but', zib_get_svg('medal-color') . '获取徽章<i class="fa fa-angle-right" style="margin: 0 0 0 .3em;"></i>');
    }

    return $my . $desc;
}
if (_pz('user_medal_s', true)) {
    add_filter('user_page_header_desc', 'zib_user_page_header_desc_filter_medal', 10, 3);
    add_filter('author_header_identity', 'zib_author_header_identity_filter_medal', 10, 3);
}

/**
 * @description: 获取展示用户徽章按钮的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $max_icon
 * @return {*}
 */
function zib_get_user_medal_show_link($user_id, $class = 'but', $max_icon = 3)
{

    if (!$user_id || !_pz('user_medal_s', true)) {
        return;
    }

    $medal_details = zib_get_user_medal_details($user_id);
    if (!$medal_details) {
        return;
    }
    $i         = 0;
    $icon_html = '';
    $count     = count($medal_details);

    foreach ($medal_details as $k => $v) {
        if ($i >= $max_icon) {
            break;
        }
        $i++;
        $icon_url  = $v['icon'];
        $lazy_attr = zib_get_lazy_attr('lazy_other', $icon_url, 'img-icon medal-icon', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
        $icon_html .= '<img ' . $lazy_attr . ' data-toggle="tooltip" title="' . esc_attr($k) . '" alt="徽章-' . esc_attr($k) . zib_get_delimiter_blog_name() . '">';
    }
    return zib_get_user_medal_info_link($user_id, $class, $icon_html . '<span class="ml3">' . $count . '枚徽章<i class="fa fa-angle-right" style="margin: 0 0 0 .3em;"></i></span>');
}

/**
 * @description: 获取为用户手动授予徽章的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_user_medal_manually_add_link($user_id = 0, $class = '', $con = '授予徽章')
{
    if (!$user_id || !_pz('user_medal_s', true) || !zib_current_user_can('medal_manually_set')) {
        return;
    }

    $class = $class ? 'medal-manually-add-link ' . $class : 'medal-manually-add-link';

    $query_arg = array(
        'action' => 'user_medal_manually_add_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => '',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $query_arg,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 为用户授予徽章的按钮
 * @param {*} $medal_name
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_medal_manually_add_link($user_id, $medal_name, $class = 'but btn-block radius', $con = '授予')
{

    if (!_pz('user_medal_s', true) || !zib_current_user_can('medal_manually_set')) {
        return;
    }

    $is_has = zib_get_user_medal($user_id, $medal_name);
    if ($is_has) {
        return;
    }

    $class .= ' medal-add-link';

    $form_data = array(
        'action'  => 'user_medal_manually_add',
        'name'    => $medal_name,
        'user_id' => $user_id,
    );

    return '<a class="' . $class . '" form-data="' . esc_attr(json_encode($form_data)) . '" href="javascript:;">' . $con . '</a>';
}

/**
 * @description: 收回用户徽章的按钮
 * @param {*} $medal_name
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_medal_manually_remove_link($user_id, $medal_name, $class = 'but btn-block radius', $con = '收回')
{
    if (!_pz('user_medal_s', true) || !zib_current_user_can('medal_manually_set')) {
        return;
    }

    $is_has = zib_get_user_medal($user_id, $medal_name);
    if (!$is_has) {
        return;
    }

    $class .= ' medal-remove-link';

    $form_data = array(
        'action'  => 'user_medal_manually_remove',
        'name'    => $medal_name,
        'user_id' => $user_id,
    );

    return '<a class="' . $class . '" form-data="' . esc_attr(json_encode($form_data)) . '" href="javascript:;">' . $con . '</a>';
}

/**
 * @description: 为用户授予徽章的模态框
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_medal_manually_add_modal($user_id = 0)
{
    $user = get_userdata($user_id);
    if (!isset($user->display_name)) {
        return;
    }

    $display_name = $user->display_name;
    $avatar_img   = zib_get_avatar_box($user_id, 'avatar-img', false, false);
    $user_name    = '<span class="em09">为<span class="focus-color">' . $display_name . '</span>授予新的徽章' . '</span>';
    $header       = zib_get_modal_colorful_header('c-blue', $avatar_img, $user_name);

    $medal_manually_add_lists = '';
    $medal_cat_args           = zib_get_medal_args('cat');
    foreach ($medal_cat_args as $k => $v) {

        $no_has_html = '';
        foreach ($v as $item_k => $item_v) {
            if ($item_v['get_type'] !== 'manually_add') {
                continue;
            }

            $icon_url  = $item_v['icon'];
            $lazy_attr = zib_get_lazy_attr('lazy_other', $icon_url, 'img-icon medal-icon', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');

            $icon_html = '<div class="medal-card">
            <img ' . $lazy_attr . ' alt="徽章-' . esc_attr($item_k) . zib_get_delimiter_blog_name() . '">
            <div class="mt6 em09">' . esc_attr($item_k) . '</div>
            <div class="muted-2-color px12 mt6">' . esc_attr($item_v['desc']) . '</div>
            </div>';

            $no_has_html .= '<div class="medal-list flex xx jsb">' . $icon_html . zib_get_medal_manually_remove_link($user_id, $item_k, 'but btn-block radius px12 c-red mt6') . zib_get_medal_manually_add_link($user_id, $item_k, 'but btn-block radius px12 c-blue mt6') . '</div>';
        }

        if ($no_has_html) {
            $medal_manually_add_lists .= '<div class="medal-cat-box muted-box mb20">';
            $medal_manually_add_lists .= '<div class="mb20"><b class="medal-cat-name">' . $k . '</b></div>';
            $medal_manually_add_lists .= '<div class="medal-list-box flex hh">' . $no_has_html . '</div>';
            $medal_manually_add_lists .= '</div>';
        }
    }

    if (!$medal_manually_add_lists) {
        $medal_manually_add_lists = zib_get_null('暂无可授予的徽章', '30', 'null-2.svg');
    }

    $html = $header . '<div class="mini-scrollbar scroll-y max-vh5">' . $medal_manually_add_lists . '</div>';

    return $html;
}

//开始挂钩添加用户等级的经验值
class zib_auto_add_user_medal
{

    public static $user_id = 0;
    public static $option  = null;

    public static function init($user_id)
    {

        if (!$user_id) {
            return;
        }

        self::$user_id = $user_id;
        if (self::$option === null) {
            self::$option = zib_get_medal_args('get');
        }

        if (self::$option) {
            if (isset(self::$option['manually_add'])) {
                unset(self::$option['manually_add']);
            }
            foreach (self::$option as $get_type => $item) {
                if ($item && method_exists('zib_user_float_data', $get_type)) {
                    $user_get_val = call_user_func(['zib_user_float_data', $get_type], self::$user_id); //数字数据
                    self::judge_execute($item, $user_get_val);
                }
            }
        }
    }

    //判断并执行
    public static function judge_execute($item, $user_val)
    {
        $user_val = (float) $user_val;
        foreach ($item as $args) {
            $get_val    = (float) $args['get_val'];
            $medal_name = $args['name'];
            if ($user_val >= $get_val) {
                zib_add_user_medal(self::$user_id, $medal_name);
            }
            //    file_put_contents(__DIR__ . '/medal.txt', date('Y-m-d H:i:s') . ': ' . $medal_name . '：( ' . $user_val . ' >= ' . $get_val . ' )' . PHP_EOL, FILE_APPEND);
        }
    }

}
if (_pz('user_medal_s')) {
    add_action('ajax_get_current_user', array('zib_auto_add_user_medal', 'init'));
}

/**
 * @description: 获取用户的数字数据
 * @param {*}
 * @return {*}
 */
class zib_user_float_data
{

    //注册时间达到X天
    public static function registration_time($user_id)
    {

        $user_data       = get_userdata($user_id);
        $user_registered = get_date_from_gmt($user_data->user_registered, 'Y-m-d H:i:s');
        $current_time    = current_time('Y-m-d H:i:s');
        $data            = round(((strtotime($current_time) - strtotime($user_registered)) / 86400), 4);
        return $data;
    }

    //发布文章数量达到X篇
    public static function new_post($user_id)
    {
        $data = zib_get_user_post_count($user_id, 'publish', 'post');
        return $data;
    }

    //发布的文章获得X次点赞
    public static function like($user_id)
    {
        $data = get_user_posts_meta_sum($user_id, 'like');
        return $data;
    }

    //发布评论数量达到X条
    public static function comment($user_id)
    {
        $data = get_user_comment_count($user_id);
        return $data;
    }

    //发布的评论获得X次点赞
    public static function comment_like($user_id)
    {
        $data = get_user_comment_meta_sum($user_id, 'comment_like');
        return $data;
    }

    //获的X个粉丝
    public static function followed($user_id)
    {
        $data = get_user_followed_count($user_id);
        return $data;
    }

    //发布的内容有X被收藏
    public static function favorite($user_id)
    {
        $data = get_user_posts_meta_sum($user_id, 'favorite');
        return $data;
    }

    //发布的内容总阅读量达到X
    public static function views($user_id)
    {
        $data = get_user_posts_meta_sum($user_id, 'views');
        return $data;
    }

    //【论坛】发布帖子数量达到X篇
    public static function bbs_post($user_id)
    {
        $data = zib_get_user_post_count($user_id, 'publish', 'forum_post');
        return $data;
    }

    //【论坛】发布的帖子有X篇成为热门
    public static function bbs_post_hot($user_id)
    {
        $data = zib_get_user_meta_query_post_count($user_id, 'is_hot', 'forum_post');
        return $data;
    }

    //【论坛】发布的帖子有X篇成为精华帖子
    public static function bbs_essence($user_id)
    {
        $data = zib_get_user_meta_query_post_count($user_id, 'essence', 'forum_post');
        return $data;
    }

    //【论坛】发布的帖子累计评分达到X分
    public static function bbs_score($user_id)
    {
        $data = get_user_posts_meta_sum($user_id, 'score');
        return $data;
    }

    //【论坛】创建的板块数量达到X个
    public static function bbs_plate($user_id)
    {
        $data = zib_get_user_post_count($user_id, 'publish', 'plate');
        return $data;
    }

    //【论坛】创建的板块有X个成为热门板块
    public static function bbs_plate_hot($user_id)
    {
        $data = zib_get_user_meta_query_post_count($user_id, 'is_hot', 'plate');
        return $data;
    }

    //【论坛】有X次回答被采纳
    public static function bbs_adopt($user_id)
    {
        $data = zib_get_user_meta_query_comment_count($user_id, 'adopted');
        return $data;
    }

    //【论坛】发表的评论有X条成为神评论
    public static function bbs_comment_hot($user_id)
    {
        $data = zib_get_user_meta_query_comment_count($user_id, 'is_hot');
        return $data;
    }

    //【创作分成】累计获得的分成金额到达X元
    public static function pay_income($user_id)
    {
        $data = zibpay_get_user_income_data($user_id, 'all')['sum'];
        return $data;
    }

    //【推广返佣】累计获得的佣金金额到达X元
    public static function pay_rebate($user_id)
    {
        $data = zibpay_get_user_rebate_data($user_id, 'all')['sum'];
        return $data;
    }

    //累计签到天数达到X天
    public static function checkin_all_day($user_id)
    {
        $data = zib_get_user_checkin_all_day($user_id);
        return $data;
    }

}
