<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-10-14 13:54:18
 * @LastEditTime : 2025-07-31 19:08:49
 */

/**
 * 支付宝同步回调
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url(); // 支付后跳转返回地址

if (!empty($_GET['out_trade_no']) && !empty($_GET['app_type']) && !empty($_GET['app_id']) && !empty($_GET['method']) && !empty($_GET['sign']) && !empty($_GET['app_id'])) {

    global $wpdb;
    $out_trade_no = $_GET['out_trade_no'];

    //根据订单号查询是否有付款成功
    $pay_order = ZibDB::name('zibpay_payment')->where(['order_num' => $out_trade_no, 'status' => 1])->find()->toArray();

    //查询未支付，则查询订单
    if (!$pay_order) {
        $config = zibpay_get_payconfig('official_alipay');
        if ($_GET['app_type'] === 'wap' && $config['webappid'] && $config['webprivatekey'] && $config['webappid'] === $_GET['app_id']) {
            //网站应用:电脑网站支付+手机网站支付

            $params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
            $params->appID         = $config['webappid'];
            $params->appPrivateKey = $config['webprivatekey'];
            $params->appPublicKey  = $config['publickey'];

            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
            // 支付接口
            $request                               = new \Yurun\PaySDK\AlipayApp\Params\Query\Request;
            $request->businessParams->out_trade_no = $out_trade_no; // 订单支付时传入的商户订单号,和支付宝交易号不能同时为空。

            // 调用接口
            $result = $pay->execute($request);
            $result = !empty($result['alipay_trade_query_response']) ? $result['alipay_trade_query_response'] : '';

            if ($pay->checkResult() && !empty($result['trade_status']) && $result['trade_status'] == 'TRADE_SUCCESS') {
                //查询成功，更新订单状态
                $pay = array(
                    'order_num' => $result['out_trade_no'],
                    'pay_type'  => 'alipay',
                    'pay_price' => $result['total_amount'],
                    'pay_num'   => $result['trade_no'],
                );

                // 更新订单状态
                $order = ZibPay::payment_order($pay);
            }
        }
    }
}

wp_safe_redirect($return_url);
