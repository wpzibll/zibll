<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-03 16:09:18
 * @LastEditTime: 2024-06-19 12:27:21
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|消息中心页面的相关内容
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//用户中心页面的主内容
function zib_msg_center_page_content()
{

    $user      = wp_get_current_user();
    $user_id   = isset($user->ID) ? (int) $user->ID : 0;
    $message_s = _pz('message_s', true);
    $private_s = _pz('private_s', true);
    if (!$message_s) {
        return;
    }

    $badge_news   = zibmsg_get_user_msg_count($user_id, '', 'top');
    $badge_posts  = zibmsg_get_user_msg_count($user_id, 'posts', 'top');
    $badge_like   = zibmsg_get_user_msg_count($user_id, 'like', 'top');
    $badge_system = zibmsg_get_user_msg_count($user_id, 'system', 'top');

    $img_uri = ZIB_TEMPLATE_DIRECTORY_URI . '/img/';

    $tabs_array['news'] = array(
        'title'         => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-news.svg', 'fit-cover') . ' alt="未读消息">未读消息' . $badge_news,
        'nav_attr'      => 'data-route="' . zibmsg_get_conter_url() . '"',
        'content_class' => '',
        'loader'        => str_repeat('<div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div>', 5),
    );
    $tabs_array['posts'] = array(
        'title'         => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-comment.svg', 'fit-cover') . ' alt="未读消息">文章评论' . $badge_posts,
        'nav_attr'      => 'data-route="' . zibmsg_get_conter_url('posts') . '"',
        'content_class' => '',
        'loader'        => str_repeat('<div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div>', 5),
    );
    $tabs_array['like'] = array(
        'title'         => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-followed.svg', 'fit-cover') . ' alt="未读消息">点赞喜欢' . $badge_like,
        'nav_attr'      => 'data-route="' . zibmsg_get_conter_url('like') . '"',
        'content_class' => '',
        'loader'        => str_repeat('<div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div>', 5),
    );
    if ($private_s) {
        $badge_private         = zibmsg_get_user_msg_count($user_id, 'private', 'top');
        $tabs_array['private'] = array(
            'title'         => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-private.svg', 'fit-cover') . ' alt="未读消息">私信消息' . $badge_private,
            'nav_attr'      => 'data-route="' . zibmsg_get_conter_url('private') . '"',
            'content_class' => '',
            'loader'        => '<div class="row msg-private"><div class="col-sm-4"><div class="padding-h10 border-bottom chat-lists "><div class="flex relative msg-list"><div class="avatar-img shrink0 mr10"><div class="placeholder avatar"></div></div><div class="flex1"><div class="placeholder k3" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div></div><div class="padding-h10 border-bottom chat-lists "><div class="flex relative msg-list"><div class="avatar-img shrink0 mr10"><div class="placeholder avatar"></div></div><div class="flex1"><div class="placeholder k3" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div></div><div class="padding-h10 border-bottom chat-lists "><div class="flex relative msg-list"><div class="avatar-img shrink0 mr10"><div class="placeholder avatar"></div></div><div class="flex1"><div class="placeholder k3" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div></div><div class="padding-h10 border-bottom chat-lists "><div class="flex relative msg-list"><div class="avatar-img shrink0 mr10"><div class="placeholder avatar"></div></div><div class="flex1"><div class="placeholder k3" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div></div><div class="padding-h10 border-bottom chat-lists "><div class="flex relative msg-list"><div class="avatar-img shrink0 mr10"><div class="placeholder avatar"></div></div><div class="flex1"><div class="placeholder k3" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div></div><div class="padding-h10 border-bottom chat-lists "><div class="flex relative msg-list"><div class="avatar-img shrink0 mr10"><div class="placeholder avatar"></div></div><div class="flex1"><div class="placeholder k3" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div></div></div><div id="user_private_window" class="col-sm-8"><div class="private-window"><div class="private-window-header mb10 text-center"><i class="loading mr10"></i>加载中...</div><div class="placeholder mb10 placeholder" style="height: 400px;"></div><div class="private-window-footer"><form class="from-private"><p><textarea placeholder="" class="form-control" rows="2" disabled="disabled"></textarea></p><div class="pull-right"><button class="but c-blue pw-1em" disabled="disabled"><i class="fa fa-send-o"></i>发送</button></div></form></div></div></div></div>',
        );
    }
    $tabs_array['system'] = array(
        'title'         => '<img ' . zib_get_lazy_attr('lazy_other', $img_uri . 'msg-system.svg', 'fit-cover') . ' alt="未读消息">系统通知' . $badge_system,
        'nav_attr'      => 'data-route="' . zibmsg_get_conter_url('system') . '"',
        'content_class' => '',
        'loader'        => str_repeat('<div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div>', 5),
    );

    $tabs_array = apply_filters('mag_ctnter_main_tabs_array', $tabs_array);

    $tab_nav = zib_get_main_tab_nav('nav', $tabs_array, 'msg', false, 'msg_center');
    if ($user_id) {
        $tab_content = zib_get_main_tab_nav('content', $tabs_array, 'msg', false, 'msg_center');
    } else {
        $tab_content = '<div style="min-height: 360px;" class="flex jc">' . zib_get_user_singin_page_box() . '</div>';
    }

    if ($tab_nav && $tab_content) {
        $html = '<div class="msg-center row gutters-10">';
        $html .= '<div class="col-sm-3">';
        $html .= '<div class="msg-center-nav lists-nav zib-widget">';
        $html .= $tab_nav;
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="msg-center-content col-sm-9">';
        $html .= '<div class="zib-widget">';
        $html .= $tab_content;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        echo $html;
    }
}
add_action('msg_center_page_content', 'zib_msg_center_page_content');

function zibmsg_main_msg_tab_nav_content_filter($html)
{
    $html .= '<span class="hide"><a class="muted-2-color but hollow" data-toggle="tab" href="#user_msg_content" data-ajax="1"><i class="fa fa-bell fa-fw" aria-hidden="true"></i></a></span>';
    return $html;
}
add_filter('main_msg_tab_nav_content', 'zibmsg_main_msg_tab_nav_content_filter');

function zibmsg_main_msg_tab_content_content_filter($html)
{
    $html .= '<div class="tab-pane fade ajaxpager" id="user_msg_content">
                <div class="post_ajax_loader box-body nopw-sm">
                    <p class="placeholder"></p>
                    <p class="placeholder t1"></p>
                    <p class="placeholder k1"></p>
                    <p class="placeholder k2" style="height:240px;"></p>
                    <i class="placeholder s1"></i><i class="placeholder s1 ml10"></i>
                </div>
            </div>';
    return $html;
}
add_filter('main_msg_tab_content_content', 'zibmsg_main_msg_tab_content_content_filter');

//消息中心页面的全部未读消息tab
function zibmsg_page_main_tab_content_news()
{

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $count = (int) zibmsg_get_user_msg_count($user_id, '', '', true);

    $html        = '';
    $clear_types = zibmsg_get_clear_types();
    if ($clear_types) {
        $clear_time = (int) _pz('message_auto_clear_time', 365);
        if ($clear_time > 0) {
            $html .= '<div class="ajax-item abs-center right-top"><i data-toggle="tooltip" title="部分已读消息将会在' . $clear_time . '天后自动清理" class="muted-color mt20 mr10 fa fa-question-circle-o"></i></div>';
        }
    }

    if ($count >= 2) {
        $paged = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;

        if ((_pz('message_paginate_type', 'ajax_lists') == 'ajax_lists' && $paged < 2) || _pz('message_paginate_type', 'ajax_lists') != 'ajax_lists') {
            $html .= zib_get_msg_all_readed($user_id);
        }
    }
    $html .= zib_get_user_news_msg($user_id);

    return $html;
}
add_filter('main_msg_tab_content_news', 'zibmsg_page_main_tab_content_news');

//消息中心页面的互动消息、点赞消息、系统消息的Tab内容
function zibmsg_main_msg_tab_content_posts()
{
    return zib_get_user_cat_msg('posts');
}
add_filter('main_msg_tab_content_posts', 'zibmsg_main_msg_tab_content_posts');

function zibmsg_main_msg_tab_content_like()
{
    return zib_get_user_cat_msg('like');
}
add_filter('main_msg_tab_content_like', 'zibmsg_main_msg_tab_content_like');

function zibmsg_main_msg_tab_content_system()
{
    return zib_get_user_cat_msg('system');
}
add_filter('main_msg_tab_content_system', 'zibmsg_main_msg_tab_content_system');

function zibmsg_main_msg_tab_content_private()
{
    return zib_get_ajax_ajaxpager_one_centent(zib_ajax_user_msg_private());
}
add_filter('main_msg_tab_content_private', 'zibmsg_main_msg_tab_content_private');

/**
 * @description: 根据分类获取消息列表
 * @param {*} $cat
 * @return {*}
 */
function zib_get_user_cat_msg($cat = 'posts')
{
    $msg_cat = zib_get_msg_cat();

    $where = array(
        'status' => 0,
        'type'   => $msg_cat[$cat],
    );

    return zib_get_user_msg_lists('', $where, '', $cat);
}

/**
 * @description: 构建全部标记为已读的ajax按钮
 * @param {*}
 * @return {*}
 */
function zib_get_msg_all_readed($user_id, $cat = '', $class = 'border-bottom box-body notop nopw-sm ajax-item', $a_class = 'but', $text = '全部标为已读')
{

    $but  = zib_get_msg_all_readed_link($user_id, $cat, $a_class, $text);
    $html = '<div class="' . $class . '">' . $but . '</div>';
    return $html;
}

/**
 * @description: 获取全部已读的按钮
 * @param {*} $user_id
 * @param {*} $cat
 * @param {*} $class
 * @param {*} $text
 * @return {*}
 */
function zib_get_msg_all_readed_link($user_id, $cat = '', $class = 'but', $text = '全部标为已读')
{

    $ajax_query_arg = array(
        'action'   => 'msg_all_readed',
        'user_id'  => $user_id,
        '_wpnonce' => wp_create_nonce('msg_readed'), //安全验证
    );
    if ($cat) {
        $ajax_query_arg['cat'] = $cat;
    }
    $blacklist_url = add_query_arg($ajax_query_arg, admin_url('admin-ajax.php'));
    $but           = '<a class="ajax-readed ' . $class . '" href="javascript:;" ajax-href="' . $blacklist_url . '">' . $text . '</a>';
    return $but;
}

/**
 * @description: 获取用户未读消息的TAB主内容
 * @param {*}
 * @return {*}
 */
function zib_get_user_news_msg($user_id = '')
{
    global $current_user;
    if (!$user_id) {
        $user_id = $current_user->ID;
    }

    $where    = array('status' => 0, 'no_readed_user' => $user_id);
    $ajax_url = add_query_arg('action', 'user_news_msg', admin_url('admin-ajax.php'));
    if (!_pz('private_s', true)) {
        $where['type'] = '<>|private';
    }
    return zib_get_user_msg_lists($user_id, $where, $ajax_url);
}

/**
 * @description: 获取消息列表
 * @param array $where 例如：array('id' => '10');
 * @return {*}
 */
function zib_get_user_msg_lists($user_id = '', $where = array(), $ajax_url = '', $cat = 'news')
{
    global $current_user;
    if (!$user_id) {
        $user_id = $current_user->ID;
    }

    if (!$user_id) {
        return;
    }

    //准备查询参数
    $user_id     = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $user_id;
    $paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : 12;
    $offset      = $ice_perpage * ($paged - 1);

    $where['receive_user'] = zibmsg_get_receive_user_args($user_id);
    //获取数量和列表
    $count_all = ZibMsg::get_count($where);
    $db_msg    = ZibMsg::get($where, 'modified_time', $offset, $ice_perpage);
    $html      = '';
    $lists     = '';

    if ($count_all && $db_msg) {
        foreach ($db_msg as $msg) {
            $lists .= zib_get_msg_box($msg, 'ajax-item nopw-sm', $user_id, $cat);
        }
        $ajax_url = $ajax_url ? $ajax_url : zib_get_current_url();

        if (_pz('message_paginate_type', 'ajax_lists') == 'ajax_lists') {
            $paginate = zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url);
        } else {
            $paginate = zib_get_ajax_number_paginate($count_all, $paged, $ice_perpage, $ajax_url);
        }

        if ($paginate) {
            $lists .= $paginate;
            $lists .= $paged == 1 ? '<div class="post_ajax_loader" style="display: none;"><div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div><div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div><div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div><div class="border-bottom box-body nopw-sm"><ul class="list-inline relative msg-list"><li><a class="msg-img placeholder"></a></li><li><dl><dt class="placeholder k2" style=" width: 80%; "></dt><dd class="mt10"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></dd></dl></li></ul></div></div>' : '';
        }
    } else {
        $lists .= zib_get_ajax_null('暂无消息');
    }

    $html .= $lists;
    return $html;
}

//用户中心挂钩消息通知设置
function zib_msg_set_user_page_tabs_array($tabs_array)
{

    $tabs_array['msg'] = array(
        'title'    => '消息通知',
        'nav_attr' => 'drawer-title="消息通知设置"',
        'loader'   => '<div class="zib-widget"><div class="box-body notop nopw-sm"><div class="border-bottom box-body"><div style="width: 150px;" class="placeholder t1 mb10"></div><div class="placeholder t1"></div></div><div class="border-bottom box-body"><div style="width: 150px;" class="placeholder t1 mb10"></div><div class="placeholder t1"></div></div><div class="border-bottom box-body"><div style="width: 150px;" class="placeholder t1 mb10"></div><div class="placeholder t1"></div></div><div class="box-body nobottom"><div style="width: 150px;" class="placeholder t1"></div></div></div></div>',
    );

    return $tabs_array;
}
//用户中心按钮挂钩消息通知设置
function zib_msg_set_user_page_button_2_args($buttons)
{

    $args = array(
        array(
            'html' => '',
            'icon' => zib_get_svg('msg-color'),
            'name' => '消息通知',
            'tab'  => 'msg',
        ),
    );
    return array_merge($args, $buttons);
}

if (_pz('message_user_set', true) && _pz('message_s', true)) {
    add_filter('zib_user_center_page_sidebar_button_2_args', 'zib_msg_set_user_page_button_2_args');
    add_filter('user_ctnter_main_tabs_array', 'zib_msg_set_user_page_tabs_array');
    add_filter('main_user_tab_content_msg', 'zib_msg_set_user_page_tab_content');
}

//用户中心vip tab
function zib_msg_set_user_page_tab_content()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $msg_set = (array) zib_get_user_meta($user_id, 'message_shield', true);

    $but_args   = array();
    $but_args[] = array(
        'checked' => in_array('posts', $msg_set),
        'neme'    => 'posts',
        'title'   => '文章评论',
        'label'   => '接收文章、评论、收藏等相关消息',
    );
    $but_args[] = array(
        'checked' => in_array('like', $msg_set),
        'neme'    => 'like',
        'title'   => '点赞喜欢',
        'label'   => '接收点赞、关注等相关消息',
    );
    $but_args[] = array(
        'checked' => in_array('system', $msg_set),
        'neme'    => 'system',
        'title'   => '系统消息',
        'label'   => '接收订单、活动、等系统消息',
    );

    $set = '';
    foreach ($but_args as $but) {
        $checked = $but['checked'] ? '' : ' checked="checked"';

        $set .= '<div class="border-bottom box-body"><label class="flex jsb ac">';
        $set .= '<input class="hide"' . $checked . ' name="' . $but['neme'] . '" type="checkbox">';

        $set .= '<div class="flex1 mr20">';
        $set .= '<div class="em12 mb6">' . $but['title'] . '</div>';
        $set .= '<div class="muted-2-color" style="font-weight: normal;">' . $but['label'] . '</div>';
        $set .= '</div>';

        $set .= '<div class="form-switch flex0">';

        $set .= '</div>';
        $set .= '</label></div>';
    }

    $set .= '<div class="mt20 mr20 text-right">';
    $set .= '<input type="hidden" name="user_id" value="' . $user_id . '">';
    $set .= '<input type="hidden" name="action" value="message_shield">';
    $set .= wp_nonce_field('user_msg_set', '_wpnonce', false, false); //安全验证
    $set .= '<button type="button" zibajax="submit" class="but jb-blue padding-lg mt10" name="submit"><i class="fa fa-check mr10"></i>确认提交</button>';
    $set .= '</div>';

    $con = '';
    $con = '<form>' . $set . '</form>';

    $html = '<div class="zib-widget"><div class="box-body notop nopw-sm">' . $con . '</div></div>';
    return zib_get_ajax_ajaxpager_one_centent($html);
}

//为消息中心页面添加head
function zib_msg_center_seo()
{
    global $new_title, $new_description;
    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;

    if ($user_id) {
        $private_count = zibmsg_get_user_msg_count($user_id, 'private', '', true);
        $all_count     = zibmsg_get_user_msg_count($user_id, 'all', '', true);
        $new           = '';
        $new           = $private_count ? $private_count . '封私信/' : '';
        $new .= $all_count ? $all_count . '条消息' . _get_delimiter() : '';
        $new .= $user->display_name . _get_delimiter() . '消息中心';
    } else {
        $new = '消息中心';
    }
    $new .= zib_get_delimiter_blog_name();
    $new_title = $new_description = $new;
    add_filter('echo_seo_title', '__return_true');
}
add_action('locate_template_msg_center', 'zib_msg_center_seo');

//为消息中心页面添加小工具
add_action('msg_center_page_header', function () {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('all_top_fluid');
    dynamic_sidebar('msg_top_fluid');
    echo '</div>';
});
add_action('msg_center_page_footer', function () {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('msg_bottom_fluid');
    dynamic_sidebar('all_bottom_fluid');
    echo '</div>';
});
