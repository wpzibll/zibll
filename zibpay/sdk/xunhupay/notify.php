<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime : 2025-07-13 20:49:51
 */
/**
 * 支付成功异步回调接口
 *
 * 当用户支付成功后，支付平台会把订单支付信息异步请求到本接口(最多5次)
 *
 * @date 2017年3月13日
 * @copyright 重庆迅虎网络有限公司
 */
require_once 'api.php';

/**
 * 回调数据
 * @var array(
 *       'trade_order_id'，商户网站订单ID
'total_fee',订单支付金额
'transaction_id',//支付平台订单ID
'order_date',//支付时间
'plugins',//自定义插件ID,与支付请求时一致
'status'=>'OD'//订单状态，OD已支付，WP未支付
 *   )
 */

$appid        = '2147483647'; //测试账户，仅支持一元内支付
$appsecret    = '160130736b1ac0d54ed7abe51e44840b'; //测试账户，仅支持一元内支付
$my_plugin_id = 'my-plugins-id';

$data = $_POST;
foreach ($data as $k => $v) {
    $data[$k] = stripslashes($v);
}
if (!isset($data['hash']) || !isset($data['trade_order_id'])) {
    echo 'failed';exit;
}

//自定义插件ID,请与支付请求时一致
if (isset($data['plugins']) && $data['plugins'] != $my_plugin_id) {
    echo 'failed';exit;
}

//APP SECRET
$appkey = $appsecret;
$hash   = XH_Payment_Api::generate_xh_hash($data, $appkey);
if ($data['hash'] != $hash) {
    //签名验证失败
    echo 'failed';exit;
}

//商户订单ID
$trade_order_id = $data['trade_order_id'];

if ($data['status'] == 'OD') {

} else {
    //处理未支付的情况
}

//以下是处理成功后输出，当支付平台接收到此消息后，将不再重复回调当前接口
echo 'success';
exit;
