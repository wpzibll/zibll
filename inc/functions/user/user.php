<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime : 2025-07-25 14:09:22
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户认证相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

$functions = array(
    'user-auth',
    'user-level',
    'user-cap',
    'user-ban',
    'user-checkin',
    'user_medal',
    'invit-code',
    'page',
    'ajax',
);

foreach ($functions as $function) {
    $path = 'inc/functions/user/' . $function . '.php';
    require get_theme_file_path($path);
}

if (is_admin()) {
    require get_theme_file_path('inc/functions/user/admin/admin.php');
}

/**
 * @description: 获取用户中心的链接html
 * @param {*} $class
 * @param {*} $before
 * @param {*} $text
 * @return {*}
 */
function zib_get_user_center_link($class = '', $text = '用户中心')
{
    $url = zib_get_user_center_url();
    return '<a rel="nofollow" href="' . $url . '" class="' . $class . '">' . $text . '</a>';
}

//个人用户中心链接url
function zib_get_user_center_url($type = null, $tab = null)
{

    $slug = trim(_pz('user_center_rewrite_slug', 'user'));
    $slug = $slug ? $slug : 'user';

    if (get_option('permalink_structure')) {
        $url = home_url($slug . '/' . $type);
    } else {
        $url = add_query_arg('user_center', ($type ? $type : '1'), home_url());
    }

    if ($tab) {
        $url = add_query_arg('tab', $tab, $url);
    }

    return $url;
}

//用户中心的路由设置
function zib_user_center_rewrite_rules($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        $rewrite_slug = trim(_pz('user_center_rewrite_slug', 'user'));
        $rewrite_slug = $rewrite_slug ? $rewrite_slug : 'user';

        $new_rules[$rewrite_slug . '$']             = 'index.php?user_center=1';
        $new_rules[$rewrite_slug . '/([A-Za-z]+)$'] = 'index.php?user_center=$matches[1]';
        $wp_rewrite->rules                          = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'zib_user_center_rewrite_rules');

function zib_add_user_center_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'user_center';
    }
    return $public_query_vars;
}
add_filter('query_vars', 'zib_add_user_center_query_vars');

function zib_user_center_load_template()
{
    $user_center = get_query_var('user_center');
    if ($user_center) {
        global $wp_query;
        $wp_query->is_home = false;

        if (zib_is_close_sign()) {
            //如果全局关闭了登录注册功能，则直接404
            $wp_query->is_404 = true;
        } else {
            $wp_query->is_404 = false;
            $template         = get_theme_file_path('inc/functions/user/page/user-center.php');
            load_template($template);
            exit;
        }
    }
}
add_action('template_redirect', 'zib_user_center_load_template', 5);
//用户中心的路由设置结束

/**
 * @description: 获取用户的搜索按钮
 * @param {*} $user_id
 * @param {*} $type
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_user_search_link($user_id, $type = '', $class = '', $con = '')
{

    $user = get_userdata($user_id);
    $name = $user->display_name;

    $type_name = '内容';
    if ($type) {
        $type_name = zib_get_search_types()[$type];
    }

    $args = array(
        'class'       => $class,
        'trem'        => 'null',
        'con'         => $con,
        'user'        => $user_id,
        'type'        => $type,
        'placeholder' => '在用户[' . $name . ']中搜索' . $type_name,
    );

    return zib_get_search_link($args);
}

/**
 * @description: 获取查看用户的详细资料的按钮
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $text
 * @return {*}
 */
function zib_get_user_details_data_link($user_id, $class = '', $text = '更多资料')
{
    if (!$user_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => 'user-details-link ' . $class,
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'height'        => 330,
        'text'          => $text,
        'query_arg'     => array(
            'action' => 'user_details_data_modal',
            'id'     => $user_id,
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取一个完整的登录按钮盒子
function zib_get_user_singin_page_box($class = 'box-body', $hi = 'Hi！请登录')
{
    if (get_current_user_id() || zib_is_close_sign()) {
        return;
    }

    $social_login_html = '';
    $social_login      = zib_social_login(false);

    if ($social_login) {
        $social_login_html = '<div class="social-separator separator muted-3-color em09 mt20 mb20">社交账号登录</div><div class="social_loginbar">' . $social_login . '</div>';
    }

    $html = '<div class="text-center ' . $class . '">
                <p class="muted-color box-body em12">' . $hi . '</p>
                <p>
                    <a href="javascript:;" class="signin-loader but jb-blue padding-lg"><i class="fa fa-fw fa-sign-in" aria-hidden="true"></i>登录</a>
                    ' . (!zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but jb-yellow padding-lg">' . zib_get_svg('signup') . '注册</a>' : '') . '
                </p>
                ' . $social_login_html . '
            </div>';
    return $html;
}

//获取用户详细资料
function zib_get_user_details_data_modal($user_id = '', $class = 'mb10 flex', $t_class = 'muted-2-color', $v_class = '')
{
    if (!$user_id) {
        return;
    }

    $current_id = get_current_user_id();
    $udata      = get_userdata($user_id);
    if (!$udata) {
        return;
    }

    $privacy = zib_get_user_meta($user_id, 'privacy', true);

    $datas = array(
        array(
            'title'   => '签名',
            'value'   => get_user_desc($user_id, false),
            'spare'   => '未知',
            'no_show' => false,
        ),
        array(
            'title'   => '注册时间',
            'value'   => get_date_from_gmt($udata->user_registered),
            'spare'   => '未知',
            'no_show' => false,
        ), array(
            'title'   => '最后登录',
            'value'   => get_user_meta($user_id, 'last_login', true),
            'spare'   => '未知',
            'no_show' => false,
        ), array(
            'title'   => '邮箱',
            'value'   => esc_attr($udata->user_email),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '性别',
            'value'   => esc_attr(get_user_meta($user_id, 'gender', true)),
            'spare'   => '保密',
            'no_show' => true,
        ), array(
            'title'   => '地址',
            'value'   => esc_textarea(zib_get_user_meta($user_id, 'address', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '个人网站',
            'value'   => zib_get_url_link($user_id),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => 'QQ',
            'value'   => esc_attr(zib_get_user_meta($user_id, 'qq', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '微信',
            'value'   => esc_attr(zib_get_user_meta($user_id, 'weixin', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => '微博',
            'value'   => esc_url(zib_get_user_meta($user_id, 'weibo', true)),
            'spare'   => '未知',
            'no_show' => true,
        ), array(
            'title'   => 'Github',
            'value'   => esc_url(zib_get_user_meta($user_id, 'github', true)),
            'spare'   => '未知',
            'no_show' => true,
        ),
    );

    $lists = '';

    //用户认证
    if (_pz('user_auth_s', true)) {
        $auth_name = zib_get_user_auth_info_link($user_id, 'c-blue');
        $auth_name = $auth_name ? $auth_name : '未认证';
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">认证</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $auth_name . '</div>';
        $lists .= '</div>';
    }

    //用户徽章
    if (_pz('user_medal_s', true)) {
        $user_medal = zib_get_user_medal_show_link($user_id, '', 5);
        $user_medal = $user_medal ? $user_medal : '暂无徽章';

        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">徽章</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . $user_medal . '</div>';
        $lists .= '</div>';
    }

    foreach ($datas as $data) {
        if (!is_super_admin() && $data['no_show'] && 'public' != $privacy && $current_id != $user_id) {
            if (('just_logged' == $privacy && !$current_id) || 'just_logged' != $privacy) {
                $data['value'] = '用户未公开';
            }
        }
        $lists .= '<div class="' . $class . '" style="min-width: 50%;">';
        $lists .= '<div class="author-set-left ' . $t_class . '" style="min-width: 80px;">' . $data['title'] . '</div>';
        $lists .= '<div class="author-set-right mt6' . $v_class . '">' . ($data['value'] ? $data['value'] : $data['spare']) . '</div>';
        $lists .= '</div>';
    }

    $header = '<div class="mb10 border-bottom touch" style="padding-bottom: 12px;">';
    $header .= '<button class="close ml10" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>';
    $header .= '<div class="" style="">';
    $header .= zib_get_post_user_box($user_id);
    $header .= '</div>';
    $header .= '</div>';

    $html = '<div class="mini-scrollbar scroll-y max-vh5 flex hh">' . $lists . '</div>';
    return $header . $html;
}

function zib_get_url_link($user_id, $class = 'focus-color')
{
    $user_url = get_userdata($user_id)->user_url;
    $url_name = zib_get_user_meta($user_id, 'url_name', true) ?: $user_url;
    $user_url = go_link($user_url, true);
    return $user_url ? '<a class="' . $class . '" href="' . esc_url($user_url) . '" target="_blank">' . esc_attr($url_name) . '</a>' : 0;
}

//获取用户加入本站天数
function zib_get_user_join_days($user_id = 0)
{
    $user_data = get_userdata($user_id);

    if (!empty($user_data->user_registered)) {
        return zib_get_time_spend(get_date_from_gmt($user_data->user_registered), 'days') + 1;
    }
    return false;
}

//获取用户加入本站天数的文案
function zib_get_user_join_day_desc($user_id = 0, $calss = 'em09 muted-2-color')
{
    $join_day = zib_get_user_join_days($user_id);
    $name     = _pz('user_join_day_my_name');
    if ($join_day) {
        return '<span class="' . $calss . '">已加入' . $name . $join_day . '天</span>';
    }
}

//用户登录时候，保存用户位置信息
function zib_updata_user_addr_meta($user_login, $user)
{
    if (!_pz('user_city_s', true)) {
        return;
    }
    $user_addr = zib_get_geographical_position_by_ip(zib_get_remote_ip_addr());
    if (!empty($user_addr['province']) || !empty($user_addr['nation'])) {
        zib_update_user_meta($user->ID, 'user_addr', $user_addr);
    }
}
add_action('wp_login', 'zib_updata_user_addr_meta', 10, 2);

//用户注册时候，保存用户位置信息
function zib_add_user_ip_addr_meta($user_id)
{
    $register_ip = zib_get_remote_ip_addr();
    if ($register_ip) {
        update_user_meta($user_id, 'register_ip', $register_ip);
    }
}
add_action('user_register', 'zib_add_user_ip_addr_meta');

//为用户个人主页显示用户地理位置
function zib_filter_author_header_desc_user_city($desc, $user_id)
{
    if (!_pz('user_city_s', true)) {
        return $desc;
    }

    $addr_data = zib_get_user_meta($user_id, 'user_addr', true);
    return $desc . zib_get_ip_geographical_position_badge($addr_data, _pz('user_city_type'), 'badg');
}
add_filter('author_header_identity', 'zib_filter_author_header_desc_user_city', 5, 2);
add_filter('user_page_header_desc', 'zib_filter_author_header_desc_user_city', 20, 2);

function zib_logout_delete_user()
{
    if (!empty($_GET['_delout']) && !empty($_GET['_wpnonce'])) {
        if (wp_verify_nonce($_GET['_delout'], 'del-out') && wp_verify_nonce($_GET['_wpnonce'], 'log-out')) {
            if (!function_exists('wp_delete_user')) {
                include_once ABSPATH . 'wp-admin/includes/user.php';
            }

            wp_delete_user(get_current_user_id());
        }
    }
}
add_action('login_form_logout', 'zib_logout_delete_user');

/**
 * @description: 获取用户的快捷回复明细
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_quick_often($user_id = null)
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) {
        return false;
    }

    return zib_get_user_meta($user_id, 'quick_often', true);
}

/**
 * @description: 保存用户的快捷回复
 * @param {*} $user_id
 * @param {*} $quick_often
 * @return {*}
 */
function zib_save_user_quick_often($user_id = null, $quick_often = array())
{
    $user_id = $user_id ?: get_current_user_id();
    if (!$user_id) {
        return false;
    }

    return zib_update_user_meta($user_id, 'quick_often', $quick_often);
}

/**
 * @description:   获取用户的快捷回复编辑按钮
 * @param {*} $class    类名
 * @param {*} $con    内容
 * @return {*}
 */
function zib_get_user_edit_quick_often_link($class = '', $con = '添加')
{
    if (!is_user_logged_in()) {
        return;
    }

    $args = array(
        'new'           => true,
        'tag'           => 'a',
        'class'         => 'edit-quick-often-link ' . $class,
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'user_edit_quick_often',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}
