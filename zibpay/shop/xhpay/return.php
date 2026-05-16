<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-10-14 13:54:18
 * @LastEditTime : 2025-07-31 19:14:15
 */

/**
 * 支付宝同步回调
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

$return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url(); // 支付后跳转返回地址

if (!empty($_GET['order_num']) && !empty($_GET['sign']) && md5($_GET['order_num'] . 'zib_xhpay') === $_GET['sign']) {

    $out_trade_no = $_GET['order_num'];

    //根据订单号查询是否有付款成功
    $pay_order = ZibDB::name('zibpay_payment')->where(['order_num' => $out_trade_no, 'status' => 1])->find()->toArray();

    //查询未支付，则查询订单
    if (!$pay_order) {

        $config = zibpay_get_payconfig('xhpay');

        if ($config['mchid'] && $config['key']) {
            //引入资源文件
            require_once get_theme_file_path('/zibpay/sdk/xhpay/xhpay.class.php');

            $xhpay  = new Xhpay($config);
            $result = $xhpay->query(['out_trade_no' => $check_order_num]);

            //查询到已经支付
            if (!empty($result['return_code']) && $result['return_code'] === 'SUCCESS') {
                $type = str_replace('zibpay_', '', $result['attach']);
                $pay  = array(
                    'order_num' => $result['out_trade_no'],
                    'pay_type'  => $type,
                    'pay_price' => $result['total_fee'] / 100,
                    'pay_num'   => $result['order_id'],
                );
                // 更新订单状态
                $order_check = ZibPay::payment_order($pay);
            }
        }
    }
}

wp_safe_redirect($return_url);
