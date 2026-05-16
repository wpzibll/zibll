<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2024-06-27 16:56:31
 */

header('Content-type:text/html; Charset=utf-8');
ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
require_once get_theme_file_path('/zibpay/sdk/xunhupay/api.php');
ob_end_clean();

//获取参数
$config = zibpay_get_payconfig('xunhupay');
//判断参数是否为空
if (!$config['wechat_appsecret'] && !$config['alipay_appsecret']) {
    echo 'failed';
    exit;
}

$my_plugin_id = 'zibpay_xunhupay';
$data         = $_POST;
foreach ($data as $k => $v) {
    $data[$k] = stripslashes($v);
}
//判断基本数据
if (!isset($data['hash']) || !isset($data['trade_order_id']) || !isset($data['plugins'])) {
    echo 'failed';
    exit;
}

$payment = str_replace($my_plugin_id . '_', '', $data['plugins']);
if ('wechat' == $payment) {
    $appsecret = $config['wechat_appsecret'];
    $payment   = 'wechat';
} else {
    $appsecret = $config['alipay_appsecret'];
    $payment   = 'alipay';
}

//签名验证
$hash = XH_Payment_Api::generate_xh_hash($data, $appsecret);
if ($data['hash'] != $hash) {
    //签名验证失败
    echo 'failed';
    exit;
}

if ($data['status'] == 'OD') {

    //准备订单数据
    $pay = array(
        'order_num' => $data['trade_order_id'],
        'pay_type'  => 'xunhupay_' . $payment,
        'pay_price' => $data['total_fee'],
        'pay_num'   => $data['transaction_id'],
    );

    // 更新订单状态
    $order = ZibPay::payment_order($pay);

    //以下是处理成功后输出，当支付平台接收到此消息后，将不再重复回调当前接口
    echo 'success';
    exit;
}
echo 'failed';
exit;
