<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-01-07 22:54:50
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

zib_require(array(
    'order-class',
    'card-pass',
), false, 'zibpay/class/');

zib_require(array(
    'zibpay-func',
    'zibpay-post',
    'zibpay-order',
    'zibpay-ajax',
    'zibpay-download',
    'zibpay-user',
    'zibpay-vip',
    'zibpay-withdraw',
    'zibpay-rebate',
    'zibpay-income',
    'zibpay-points',
    'zibpay-balance',
    'zibpay-coupon',
    'income-ajax',
    'rebate-ajax',
    'balance-ajax',
    'zibpay-msg',
    'ajax',
    'widget',
), false, 'zibpay/functions/');

if (is_admin()) {
    zib_require(array(
        'admin',
        'admin-options',
        'admin-ajax',
    ), false, 'zibpay/functions/admin/');
}

/**挂钩到主题启动 */
function zibpay_creat_table_order()
{
    ZibPay::create_db();
    ZibCardPass::create_db();
}
add_action('admin_head', 'zibpay_creat_table_order');

/**创建编辑器短代码 */
//添加隐藏内容，付费可见
function zibpay_to_show($atts, $content = null)
{

    $a     = '#posts-pay';
    $_hide = '<div class="hidden-box"><a class="hidden-text" href="javascript:(scrollTopTo(\'' . $a . '\',-50));"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，请付费后查看</a></div>';
    global $post;

    $pay_mate = get_post_meta($post->ID, 'posts_zibpay', true);

    $paid = zibpay_is_paid($post->ID);
    /**如果未设置付费阅读功能，则直接显示 */
    if (empty($pay_mate['pay_type']) || '1' != $pay_mate['pay_type']) {
        return $content;
    }

    /**
     * 判断逻辑
     * 1. 管理登录
     * 2. 已经付费
     * 3. 必须设置了付费阅读
     */
    if (is_super_admin()) {
        return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - 管理员可见</div>' . do_shortcode($content) . '</div>';
    } elseif ($paid) {
        $paid_name = zibpay_get_paid_type_name($paid['paid_type']);
        return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - ' . $paid_name . '</div>' . do_shortcode($content) . '</div>';
    } else {
        return $_hide;
    }
}
add_shortcode('payshow', 'zibpay_to_show');

//为附件下载设置固定链接的路由
function zib_pay_get_download_url($post_id, $nonce = 0)
{
    if (!$nonce) {
        $nonce = wp_create_nonce('pay_down');
    }
    if (get_option('permalink_structure')) {
        return add_query_arg('key', $nonce, home_url('pay-download/' . $post_id));
    }
    return add_query_arg(array('pay_download' => $post_id, 'key' => $nonce), home_url());
}

function zib_pay_download_rewrite_rules($wp_rewrite)
{
    if (get_option('permalink_structure')) {
        $rewrite_slug                            = 'pay-download';
        $new_rules[$rewrite_slug . '/([0-9]+)$'] = 'index.php?pay_download=$matches[1]';
        $wp_rewrite->rules                       = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'zib_pay_download_rewrite_rules');

function zib_pay_download_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'pay_download';
    }
    return $public_query_vars;
}
add_filter('query_vars', 'zib_pay_download_query_vars');

function zib_pay_download_load_template()
{
    global $wp_query;
    $pay_download = get_query_var('pay_download');
    if ($pay_download) {
        global $wp_query;
        $wp_query->is_home = false;
        $wp_query->is_404  = false;

        $template = get_theme_file_path('zibpay/download.php');
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'zib_pay_download_load_template', 5);
//附件下载设置固定链接的路由结束

//定期自动清理为支付订单
// 钩子回调函数
function zib_pay_auto_clear_order()
{
    ZibPay::clear_order(14);
}

// 注册定期执行的任务
function zib_pay_schedule_event_auto_clear_order()
{
    if (!wp_next_scheduled('zib_auto_clear_order')) {
        $timestamp = strtotime('+1day 4:00am Asia/Shanghai');
        wp_schedule_event($timestamp, 'monthly', 'zib_auto_clear_order');
    }
}

add_action('admin_init', 'zib_pay_schedule_event_auto_clear_order');
add_action('zib_auto_clear_order', 'zib_pay_auto_clear_order');
