<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-12-05 19:56:56
 * @LastEditTime: 2023-04-05 23:37:47
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|支付宝异步回调
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
require_once dirname(__FILE__) . '/../../class/alipay-check.php';
ob_end_clean();

if (empty($_POST['sign'])) {
    echo '非法请求';
    exit();
}

$config = zibpay_get_payconfig('official_alipay');
if (_pz('pay_alipay_sdk_options') != 'official_alipay') {
    //判断是否开启此支付接口
    //  exit('fail');
}
 
$aliPay = new AlipayServiceCheck($config['publickey']);
//验证签名
$rsaCheck = $aliPay->rsaCheck($_POST);
if ($rsaCheck && $_POST['trade_status'] == 'TRADE_SUCCESS') {
    // 通知验证成功，可以通过POST参数来获取支付宝回传的参数
    $pay = array(
        'order_num' => $_POST['out_trade_no'],
        'pay_type'  => 'alipay',
        'pay_price' => $_POST['total_amount'],
        'pay_num'   => $_POST['trade_no'],
    );

    // 更新订单状态
    $order = ZibPay::payment_order($pay);
    /**返回不在发送异步通知 */
    echo "success";
    exit();
} else {
    // 通知验证失败
    file_put_contents(__DIR__ . '/notify_result.txt', '//AlipayServiceCheck:' . $rsaCheck . PHP_EOL . '$_POST:' . json_encode($_POST));
}
/**返回不在发送异步通知 */
echo "error";
exit();
