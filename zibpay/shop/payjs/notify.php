<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-11-17 11:31:51
 */

/**
 * payjs异步通知
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

if (empty($_POST['return_code']) || empty($_POST['attach'])) {
    echo '非法请求';
    exit();
}

$config = zibpay_get_payconfig('payjs');
if (!$config['mchid'] || !$config['key']) {
    //判断参数是否为空
    exit('fail');
}

if (_pz('pay_wechat_sdk_options') != 'payjs' && _pz('pay_alipay_sdk_options') != 'payjs') {
    //判断是否开启此支付接口
    //  exit('fail');
}

require_once get_theme_file_path('/zibpay/sdk/payjs/payjs.class.php');
$payjs     = new Payjs($config['mchid'], $config['key']);
$checkSign = $payjs->checkSign($_POST);

if ($checkSign && $_POST['return_code'] == 1 && $_POST['attach'] == 'zibpay_payjs') {
    //本地订单处理
    $type = (empty($_POST['return_code']) && $_POST['return_code'] == 'alipay') ? 'payjs_alipay' : 'payjs_wechat';
    $pay  = array(
        'order_num' => $_POST['out_trade_no'],
        'pay_type'  => $type,
        'pay_price' => $_POST['total_fee'] / 100,
        'pay_num'   => $_POST['payjs_order_id'],
    );
    // 更新订单状态
    $order = ZibPay::payment_order($pay);
    /**返回不在发送异步通知 */

    echo 'success';
}

exit();
