<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-03-16 14:36:57
 * @LastEditTime: 2022-04-01 22:20:40
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|分销系统的Ajax函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zibpay_ajax_income_order_lists()
{

    $user_id = get_current_user_id();

    zib_ajax_send_ajaxpager(zibpay_get_user_income_order_lists($user_id));
}
add_action('wp_ajax_income_order_lists', 'zibpay_ajax_income_order_lists');

function zibpay_ajax_income_post_lists()
{

    $user_id = get_current_user_id();

    zib_ajax_send_ajaxpager(zibpay_get_user_income_post_lists($user_id));
}
add_action('wp_ajax_income_post_lists', 'zibpay_ajax_income_post_lists');
