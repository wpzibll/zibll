<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-04-14 20:39:44
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Email         : 770349780@qq.com
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

$post_id = get_query_var('pay_download') ?: (!empty($_GET['post_id']) ? $_GET['post_id'] : 0);
if (!isset($_GET['down_id']) || !$post_id) {
    wp_safe_redirect(home_url());
    exit;
}
$down_id = $_GET['down_id'];

//安全验证
if (_pz('pay_type_option', true, 'down_verify_nonce') && (!isset($_GET['key']) || !wp_verify_nonce($_GET['key'], 'pay_down'))) {
    zib_die_page('环境异常！请重新获取下载链接');
}

//判断用户是否已经购买
//查询是否已经购买
$paid    = apply_filters('pay_download_paid_data', zibpay_is_paid($post_id), $post_id);
$user_id = get_current_user_id();
if (!$paid) {
    zib_die_page('支付信息获取失败，请刷新后重试！');
}

//免费资源必须登录
if ($paid['paid_type'] == 'free' && _pz('pay_free_logged_show') && !$user_id) {
    zib_die_page('登录信息异常，请重新登录！');
}

//限制下载次数检测
if (stristr($paid['paid_type'], 'free') && zibpay_is_user_free_down_limit($user_id)) {
    zib_die_page('您今日下载免费资源个数已超限，请明日再下载');
}

//获取可下载数据
$down = zibpay_get_post_down_array($post_id);
if (empty($down[$down_id]['link'])) {
    zib_die_page('未获取到资源文件或下载链接已失效，请与管理员联系！');
}

//获取下载链接
$file_url   = rtrim(str_replace(['&amp;amp;', '&amp;'], '&', trim($down[$down_id]['link'])));
$home_url   = home_url('/');
$file_local = '';
if (stripos($file_url, $home_url) === 0) {
    $file_url_local = ABSPATH . rtrim(str_replace($home_url, '', $file_url)); //本地文件路径
    //本地文件
    if (file_exists($file_url_local) && is_file($file_url_local)) {
        $file_local = $file_url_local;
    }
}

//下载前钩子
do_action('zibpay_download_before', $post_id, $down_id, $paid, $file_url, $file_local);
$file_local = apply_filters('zibpay_download_file_local', $file_local, $file_url, $post_id, $down_id, $paid);

//不是本地文件
if (!$file_local) {
    header('location:' . $file_url);
    echo '<html><head><meta name="robots" content="noindex, nofollow"><script>location.href = "' . $file_url . '";</script></head><body></body></html>';
    exit;
}

//本地文件输出
$download_rate       = zibpay_get_user_down_speed($user_id); //下载速度
$file_local_filename = end(explode('/', $file_local));

//如果文件名包含_paydown_，则取后部分为文件名
if (strpos($file_local_filename, '_paydown_') !== false) {

    $file_local_filename_args = explode('_paydown_', $file_local_filename);
    $file_local_filename      = end($file_local_filename_args);
}

//下载文件
zib_download_local_file($file_local, $file_local_filename, $download_rate);
