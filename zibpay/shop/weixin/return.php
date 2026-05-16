<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-10-14 13:54:18
 * @LastEditTime : 2025-07-31 19:12:55
 */

/**
 * 支付宝同步回调
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url(); // 支付后跳转返回地址

if (!empty($_GET['order_num']) && !empty($_GET['sign']) && md5($_GET['order_num'] . 'zib_official_wechat') === $_GET['sign']) {

    global $wpdb;
    $out_trade_no = $_GET['order_num'];

    //根据订单号查询是否有付款成功
    $pay_order = ZibDB::name('zibpay_payment')->where(['order_num' => $out_trade_no, 'status' => 1])->find()->toArray();

    //查询未支付，则查询订单
    if (!$pay_order) {

        $config = zibpay_get_payconfig('official_wechat');

        if ($config['appid'] && $config['merchantid'] && $config['key']) {
            $params         = new \Yurun\PaySDK\Weixin\Params\PublicParams;
            $params->appID  = $config['appid'];
            $params->mch_id = $config['merchantid'];
            $params->key    = $config['key'];

            $sdk                   = new \Yurun\PaySDK\Weixin\SDK($params);
            $request               = new \Yurun\PaySDK\Weixin\OrderQuery\Request;
            $request->out_trade_no = $out_trade_no; // 微信订单号，与商户订单号二选一

            $result = (array) $sdk->execute($request);

            //查询到已经支付
            if (!empty($result['trade_state']) && $result['trade_state'] === 'SUCCESS') {
                $pay = array(
                    'order_num' => $out_trade_no,
                    'pay_type'  => 'weixin',
                    'pay_price' => $result['total_fee'] / 100,
                    'pay_num'   => $result['transaction_id'],
                );

                // 更新订单状态
                $order_check = ZibPay::payment_order($pay);
            }

        }
    }
}

wp_safe_redirect($return_url);
