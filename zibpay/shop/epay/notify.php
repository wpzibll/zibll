<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-12 00:20:44
 * @LastEditTime: 2024-04-22 18:10:21
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

if (empty($_REQUEST["sign"])) {
    if (!empty($_GET['redirect_url'])) {
        wp_safe_redirect($_GET['redirect_url']);
    }
    echo '非法请求';
    exit();
}

$config = zibpay_get_payconfig('epay');
if (empty($config['apiurl']) || empty($config['partner']) || empty($config['key'])) {
    exit('fail');
}

if (_pz('pay_wechat_sdk_options') != 'epay' && _pz('pay_alipay_sdk_options') != 'epay') {
    //判断是否开启此支付接口
    // exit('fail');
}

$redirect_url = false;
if (!empty($_GET['redirect_url'])) {
    $redirect_url = $_GET['redirect_url'];

    //移出多余参数，避免验证失败
    unset($_GET['redirect_url']);
}

require_once get_theme_file_path('/zibpay/sdk/epay/epay.class.php');
$EpayCore      = new EpayCore($config);
$verify_result = $EpayCore->verifyNotify();  //签名验证

if ($verify_result && $_GET['trade_status'] == 'TRADE_SUCCESS') {
    //验证成功
    //本地订单处理
    $pay = array(
        'order_num' => $_GET['out_trade_no'],
        'pay_type'  => 'epay_' . $_GET['type'],
        'pay_price' => $_GET['money'],
        'pay_num'   => $_GET['trade_no'],
    );
    // 更新订单状态
    $order = ZibPay::payment_order($pay);
    /**返回不在发送异步通知 */
    if (!$redirect_url) {
        echo 'success';
    }
}

if ($redirect_url) {
    wp_safe_redirect($redirect_url);
}

exit();
