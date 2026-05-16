<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-10-14 13:54:18
 * @LastEditTime : 2025-07-31 19:11:10
 */

/**
 * 支付宝同步回调
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url(); // 支付后跳转返回地址

if (empty($_GET['token']) || (!empty($_GET['cancel']))) {
    wp_safe_redirect($return_url);
}

require_once get_theme_file_path('/zibpay/sdk/paypal/paypal.php');
require_once get_theme_file_path('/zibpay/sdk/paypal/httprequest.php');

$config = zibpay_get_payconfig('paypal');

$pay     = new \PayPal($config);
$request = $pay->doPayment();

//file_put_contents(__DIR__ . '/request.json', json_encode($request));

if (isset($request['ACK']) && $request['ACK'] == 'Success' && isset($request['TOKEN'])) {
    $order = $pay->getCheckoutDetails($request['TOKEN']);

    if (!isset($order['ACK']) || $order['ACK'] !== 'Success') {
        $err_msg = isset($order['L_LONGMESSAGE0']) ? '错误码：' . $order['L_LONGMESSAGE0'] : __('PayPal配置错误，或网络连接失败', 'zibll');
        wp_die('PayPal收款失败<br>' . $err_msg);
    }

    //  file_put_contents(__DIR__ . '/order.json', json_encode($order));

    //商户本地订单号
    $order_num = $order['INVNUM'];
    $pay_num   = $request['TRANSACTIONID'];

    $local_order = ZibDB::name('zibpay_payment')->where(['order_num' => $order_num])->find()->toArray();

    if (!$local_order) {
        wp_die('订单数据异常，请联系管理员');
    }

    $pay_order_data = array(
        'order_num' => $order_num,
        'pay_type'  => 'paypal',
        'pay_price' => $local_order['pay_price'],
        'pay_num'   => $pay_num,
    );

    // 更新订单状态
    $order = ZibPay::payment_order($pay_order_data);
}

wp_safe_redirect($return_url);
