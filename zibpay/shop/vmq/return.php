<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-12 00:20:44
 * @LastEditTime : 2026-01-02 18:58:45
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$config = zibpay_get_payconfig('vmqphp');
if (empty($config['apiurl']) || empty($config['key'])) {
    wp_die('支付参数错误');
}

if (_pz('pay_wechat_sdk_options') != 'vmqphp' && _pz('pay_alipay_sdk_options') != 'vmqphp') {
    //判断是否开启此支付接口
    wp_die('支付参数错误');
}

require_once get_theme_file_path('/zibpay/sdk/vmq/vmq.class.php');
$Notify        = new vmqphpPay($config);
$verify_result = $Notify->verifyNotify();

if ($verify_result) { //验证成功
    //本地订单处理
    $param = explode('|', $_GET['param']);
    $type  = !empty($param[0]) ? '_' . $param[0] : '';
    $type  = 'vmq' . $type;

    $pay = array(
        'order_num' => $_GET['payId'],
        'pay_type'  => $type,
        'pay_price' => $_GET['reallyPrice'],
        'pay_num'   => $_GET['payId'],
    );
    // 更新订单状态
    $order = ZibPay::payment_order($pay);
    /**返回页面 */
    $return_url = !empty($param[1]) ? $param[1] : home_url();
    wp_safe_redirect($return_url);
    exit;
} else {
    $REQUEST_URI = $_SERVER['REQUEST_URI'];
    $error_log   = current_time('mysql') . ' REQUEST_URI:' . $REQUEST_URI . ' $_GET:' . var_export($_GET, true);
    //file_put_contents(__DIR__ . '/error.log', $error_log, FILE_APPEND);
    wp_die('支付签名错误或支付金额异常');
}
wp_die('未知错误');
exit;
