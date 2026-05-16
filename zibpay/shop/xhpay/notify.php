<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime : 2025-07-31 19:13:36
 */

/**
 * 迅虎pay异步通知
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$config = zibpay_get_payconfig('xhpay');
if (!$config['mchid'] || !$config['key']) {
    //判断参数是否为空
    exit('fail');
}

//判断是否开启此支付接口
if (_pz('pay_wechat_sdk_options') != 'xhpay' && _pz('pay_alipay_sdk_options') != 'xhpay') {
//    exit('fail');
}

require_once get_theme_file_path('/zibpay/sdk/xhpay/xhpay.class.php');
$xhpay  = new Xhpay($config);
$result = $xhpay->getNotify();
//file_put_contents(__DIR__ . '/notify_log.txt', json_encode($result));

if ($result && $result['return_code'] == 'SUCCESS') {
    //本地订单处理
    $type = str_replace('zibpay_', '', $result['attach']);
    $pay  = array(
        'order_num' => $result['out_trade_no'],
        'pay_type'  => $type,
        'pay_price' => $result['total_fee'] / 100,
        'pay_num'   => $result['order_id'],
    );
    // 更新订单状态
    $order = ZibPay::payment_order($pay);
    /**返回不在发送异步通知 */
    echo 'success';
}

exit();
