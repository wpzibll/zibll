<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-03 16:09:18
 * @LastEditTime: 2021-06-19 14:45:44
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */


if (_pz('message_s', true)) {
    add_action('admin_menu', 'zibmsg_add_submenu_page');
}
//后台消息管理
function zibmsg_add_submenu_page()
{
    add_submenu_page('users.php', '消息管理', '消息管理', 'administrator', 'user_messags', 'zibmsg_submenu_page');
}
function zibmsg_submenu_page()
{
    require_once get_theme_file_path('inc/functions/message/functions/admin_page.php');
}

//管理用户消息
function zibmsg_user_row_actions($actions, $user)
{
    if (is_super_admin()) {
        $edit_link       = esc_url(add_query_arg(array('page' => 'user_messags', 'user_id' => $user->ID), admin_url('users.php')));
        $actions['zibmsg'] = '<a href="' . $edit_link . '">消息管理</a>';
    }
    return $actions;
}
add_filter('user_row_actions', 'zibmsg_user_row_actions', 99, 2);
