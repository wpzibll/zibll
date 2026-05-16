<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-05-06 10:40:37
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//微信支付读取session数据
function zibpay_initiate_pay_session_data()
{
    @session_start();
    if (!empty($_REQUEST['openid'])) {
        //微信跳转兼容
        if (!empty($_SESSION['ZIBPAY_POST'])) {
            $_POST    = array_merge($_SESSION['ZIBPAY_POST'], $_POST);
            $_REQUEST = array_merge($_REQUEST, $_POST);
        } else {
            zib_send_json_error('PHP session 数据获取失败');
        }
    }
}
add_action('wp_ajax_initiate_pay', 'zibpay_initiate_pay_session_data', 5);
add_action('wp_ajax_nopriv_initiate_pay', 'zibpay_initiate_pay_session_data', 5);

/**
 * Ajax-发起支付
 * 发起支付需要的POST参数
 * payment_id 支付单号(必传)
 * return_url 返回链接(选传)
 * payment_method 如需更改的支付方式(选传)
 */
function zibpay_ajax_initiate_pay($payment_id = 0)
{
    /**
     * 发起支付需要的POST参数
     * payment_id 支付单号(必传)
     * return_url 返回链接(选传)
     * payment_method 如需更改的支付方式(选传)
     */

    $payment_id = $payment_id ?: (!empty($_REQUEST['payment_id']) ? (int) $_REQUEST['payment_id'] : 0);
    $return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '';

    if (!$return_url) {
        if (get_current_user_id()) {
            $return_url = zib_get_user_center_url('order');
        } else {
            $return_url = home_url();
        }
    }

    if (empty($payment_id)) {
        zib_send_json_error(['code' => 'error', 'msg' => '参数错误']);
    }

    $payment_data = zibpay::get_payment($payment_id);
    if (empty($payment_data)) {
        zib_send_json_error(['code' => 'error', 'msg' => '支付数据不存在']);
    }

    if ($payment_data['status'] == '1') {
        zib_send_json_success(['code' => 'success', 'msg' => '订单已支付，请刷新页面']);
    }

    //判断订单是否已经关闭
    if ($payment_data['status'] == '-1') {
        zib_send_json_error(['code' => 'error', 'msg' => '订单已关闭，请刷新页面']);
    }

    //判断订单是否失效
    $time_remaining = zibpay_get_payment_pay_over_time($payment_data);
    if ($time_remaining == 'over') {
        zib_send_json_error(['code' => 'error', 'msg' => '订单已失效，请刷新页面重新下单']);
    }

    //根据$payment_id获取订单数据
    $order_data = zibpay::get_order_by_payment_id($payment_id);
    if (empty($order_data[0])) {
        zib_send_json_error(['code' => 'error', 'msg' => '订单错误，请重新下单']);
    }

    $old_payment_method        = $payment_data['method'];
    $is_points                 = $old_payment_method === 'points';
    $payment_method            = !empty($_REQUEST['payment_method']) ? $_REQUEST['payment_method'] : 0;
    $payment_data['order_num'] = zibpay::generate_payment_order_num();
    $payment_update_data       = [
        'id'        => $payment_id,
        'order_num' => $payment_data['order_num'],
    ];
    if (!$is_points && (float) $payment_data['price'] > 0 && $payment_method && $payment_method !== $old_payment_method && isset(zibpay_get_payment_methods($order_data[0]['order_type'])[$payment_method])) {
        //前台传递了支付方式，并且支付方式合法，则更新支付方式
        $payment_update_data['method'] = $payment_method;
        $payment_data['method']        = $payment_method;
    }

    //更新数据，刷新支付单号
    if (!zibpay::update_payment($payment_update_data)) {
        zib_send_json_error(['code' => 'update_payment_error', 'msg' => '支付数据更新失败']);
    }

    //更新对应订单的$_pay_detail[$payment_method]
    if ($payment_method !== $old_payment_method) {
        zibpay::update_other_payment($payment_id, $old_payment_method, $payment_method);
    }

    //发起支付数据
    $initiate_pay_data = array(
        'payment_id'     => $payment_id,
        'user_id'        => $order_data[0]['user_id'],
        'payment_method' => $payment_data['method'],
        'order_num'      => $payment_data['order_num'],
        'order_price'    => $payment_data['price'],
        'ip_address'     => $order_data[0]['ip_address'],
        'order_name'     => zibpay_get_pay_order_name(zibpay_get_pay_type_name($order_data[0]['order_type'])),
        'return_url'     => $return_url,
    );

    if ($is_points) {
        //积分支付

        //函数节流
        zib_ajax_debounce('points_change', $initiate_pay_data['user_id']);

        $initiate_pay_data['desc'] = zibpay_get_pay_type_name($order_data[0]['order_type']);
        $initiate_pay              = zibpay_points_initiate_pay($initiate_pay_data);

        if (empty($initiate_pay['error'])) {
            $initiate_pay['reload'] = true;
            $initiate_pay['goto']   = $return_url;
        }
    } else {
        //现金支付
        add_filter('pay_order_price_is_allow_0', '__return_true'); //允许订单金额为0
        $initiate_pay = zibpay_initiate_pay($initiate_pay_data);
    }

    //支付错误，同时返回支付模态框内容
    if (!empty($initiate_pay['error']) && empty($_REQUEST['payment_modal'])) {
        $initiate_pay['modal'] = zibpay_get_order_pay_modal_content($payment_data, $return_url);
    }

    $initiate_pay['float_btn'] = zibpay_get_user_wait_pay_float_window();

    //添加支付剩余时间
    $initiate_pay['over_time'] = zibpay_get_payment_pay_over_time($payment_data);
    if (is_numeric($initiate_pay['over_time'])) {
        $initiate_pay['over_time'] = zib_time_to_c(strtotime('-10 Second', $initiate_pay['over_time']));
    }

    /**返回数据 */
    header('Content-type:application/json;character=utf-8');
    echo json_encode($initiate_pay);
    exit();
}
add_action('wp_ajax_initiate_pay', 'zibpay_ajax_initiate_pay');
add_action('wp_ajax_nopriv_initiate_pay', 'zibpay_ajax_initiate_pay');

// 挂钩AJAX-确认支付订单
function zibpay_check_pay()
{
    header('Content-type:application/json;character=utf-8');

    if (empty($_POST['order_num'])) {
        echo(json_encode(array('error' => 1, 'msg' => '还未生成订单')));
        exit();
    }

    $check_order_num = $_POST['order_num'];
    /**根据订单号查询订单 */

    $order_check_data = ZibDB::name('zibpay_payment')->field('id,order_num,create_time,status')->where('order_num', $check_order_num)->find()->toArray();

    if (empty($order_check_data['order_num'])) {
        echo(json_encode(array('error' => 1, 'msg' => '数据传入错误')));
        exit();
    }

    $order_check_data['over_time'] = zibpay_get_payment_pay_over_time($order_check_data);
    if ($order_check_data['status'] == -1) {
        echo(json_encode(array('error' => 1, 'status' => -1, 'msg' => '订单已关闭')));
        exit();
    }

    $create_time = $order_check_data['create_time'];

    if ($order_check_data['status'] != 1 && current_time('timestamp') > strtotime("$create_time +6 seconds")) {
        //通过远程接口查询，创建订单6秒之后在开始查询
        $check_sdk                        = !empty($_REQUEST['check_sdk']) ? $_REQUEST['check_sdk'] : '';
        $order_check_data['is_sdk_check'] = true;

        switch ($check_sdk) {
            case 'official_wechat':
                //微信官方扫码支付
                $config = zibpay_get_payconfig('official_wechat');
                if (empty($config['merchantid']) || empty($config['appid']) || empty($config['key'])) {
                    break;
                }

                $params         = new \Yurun\PaySDK\Weixin\Params\PublicParams;
                $params->appID  = $config['appid'];
                $params->mch_id = $config['merchantid'];
                $params->key    = $config['key'];

                $sdk                   = new \Yurun\PaySDK\Weixin\SDK($params);
                $request               = new \Yurun\PaySDK\Weixin\OrderQuery\Request;
                $request->out_trade_no = $check_order_num; // 微信订单号，与商户订单号二选一

                try {
                    $result = (array) $sdk->execute($request);
                    //查询到已经支付
                    if (!empty($result['trade_state']) && $result['trade_state'] === 'SUCCESS') {
                        $pay_order_data = array(
                            'order_num' => $check_order_num,
                            'pay_type'  => 'weixin',
                            'pay_price' => $result['total_fee'] / 100,
                            'pay_num'   => $result['transaction_id'],
                        );

                        // 更新订单状态
                        $order_check_data = (array) ZibPay::payment_order($pay_order_data);
                    }

                } catch (Exception $e) {
                    $order_check_data['sdk_check_error']     = true;
                    $order_check_data['sdk_check_error_msg'] = $e->getMessage();
                }

                break;

            case 'official_alipay':
                //支付宝当面付
                $config = zibpay_get_payconfig('official_alipay');
                if (empty($config['privatekey']) || empty($config['appid']) || empty($config['publickey'])) {
                    break;
                }

                $params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
                $params->appID         = $config['appid'];
                $params->appPrivateKey = $config['privatekey'];
                $params->appPublicKey  = $config['publickey'];

                // SDK实例化，传入公共配置
                $sdk                                   = new \Yurun\PaySDK\AlipayApp\SDK($params);
                $request                               = new \Yurun\PaySDK\AlipayApp\Params\Query\Request;
                $request->businessParams->out_trade_no = $check_order_num; // 订单支付时传入的商户订单号,和支付宝交易号不能同时为空。

                try {
                    $result = (array) $sdk->execute($request);
                    $result = !empty($result['alipay_trade_query_response']) ? $result['alipay_trade_query_response'] : '';

                    //查询到已经支付
                    if (!empty($result['trade_status']) && $result['trade_status'] === 'TRADE_SUCCESS') {
                        $pay_order_data = array(
                            'order_num' => $result['out_trade_no'],
                            'pay_type'  => 'alipay',
                            'pay_price' => $result['total_amount'],
                            'pay_num'   => $result['trade_no'],
                        );

                        // 更新订单状态
                        $order_check_data = (array) ZibPay::payment_order($pay_order_data);
                    }

                } catch (Exception $e) {
                    $order_check_data['sdk_check_error']     = true;
                    $order_check_data['sdk_check_error_msg'] = $e->getMessage();
                }

                break;

            case 'epay':
                //易支付
                $config = zibpay_get_payconfig('epay');
                if (empty($config['apiurl']) || empty($config['partner']) || empty($config['key'])) {
                    break;
                }

                require_once get_theme_file_path('/zibpay/sdk/epay/epay.class.php');
                $EpayCore = new EpayCore($config);
                $result   = $EpayCore->queryOrder($check_order_num);

                if (isset($result['status']) && $result['status'] == 1) {
                    $pay = array(
                        'order_num' => $result['out_trade_no'],
                        'pay_type'  => 'epay_' . $result['type'],
                        'pay_price' => $result['money'],
                        'pay_num'   => $result['trade_no'],
                    );
                    // 更新订单状态
                    $order_check_data = (array) ZibPay::payment_order($pay);
                } else {
                    $order_check_data['sdk_check_result'] = $result;
                }

                break;

            case 'xhpay':
                //迅虎PAY
                $config = zibpay_get_payconfig('xhpay');
                if (empty($config['mchid']) || empty($config['key'])) {
                    break;
                }
                //引入资源文件
                require_once get_theme_file_path('/zibpay/sdk/xhpay/xhpay.class.php');

                $xhpay  = new Xhpay($config);
                $result = $xhpay->query(['out_trade_no' => $check_order_num]);

                //查询到已经支付
                if (!empty($result['status']) && $result['status'] === 'complete') {
                    $type = str_replace('zibpay_', '', $result['attach']);
                    $pay  = array(
                        'order_num' => $result['out_trade_no'],
                        'pay_type'  => $type,
                        'pay_price' => $result['total_fee'] / 100,
                        'pay_num'   => $result['order_id'],
                    );
                    // 更新订单状态
                    $order_check_data = (array) ZibPay::payment_order($pay);
                } else {
                    $order_check_data['sdk_check_result'] = $result;
                }

                break;
        }
    }

    echo(json_encode($order_check_data));
    exit();
}
add_action('wp_ajax_check_pay', 'zibpay_check_pay');
add_action('wp_ajax_nopriv_check_pay', 'zibpay_check_pay');

/**发起支付函数 */
function zibpay_initiate_pay($order_data)
{
    //初始化默认数据
    $defaults = array(
        'user_id'        => 0, //用户ID，最好填上，用于微信jsapi支付
        'payment_method' => 'wechat', //支付方式 *必须
        'order_num'      => '', //订单号 *必须
        'order_price'    => 0, //订单金额 *必须
        'ip_address'     => '', //IP地址
        'order_name'     => get_bloginfo('name') . '支付', //订单名称
        'return_url'     => !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : home_url(), //返回地址
    );
    $order_data = wp_parse_args($order_data, $defaults);

    /**
     * GPL open-source payment bootstrap.
     *
     * The open-source edition does not instantiate a proprietary payment SDK.
     * Site owners or companion plugins may validate/replace the payment flow by
     * returning an array from this filter, or continue to use the bundled gateway
     * handlers selected below.
     */
    $pre_result = apply_filters('zibpay_pre_initiate_pay_result', null, $order_data);
    if (is_array($pre_result)) {
        return $pre_result;
    }

    do_action('zibpay_before_initiate_pay', $order_data);

    if (empty($order_data['order_num'])) {
        return array('error' => 1, 'msg' => '订单创建失败');
    }

    $order_data['order_price'] = zib_floatval_round($order_data['order_price']);

    //价格为0，直接付款
    //卡密支付，金额直接为0，所以此处需排除卡密支付
    if ((float) $order_data['order_price'] <= 0 && $order_data['payment_method'] !== 'card_pass') {
        $pay = array(
            'order_num' => $order_data['order_num'],
            'pay_type'  => 'balance',
            'pay_price' => 0,
            'pay_num'   => $order_data['order_num'],
        );

        // 更新订单状态
        ZibPay::payment_order($pay);
        return array('error' => 0, 'reload' => true, 'msg' => '支付成功', 'payok' => 1, 'return_url' => $order_data['return_url']);
    }

    /**准备付款接口 */
    $pay_sdk = '';
    switch ($order_data['payment_method']) {
        case 'balance':
        case 'card_pass':
        case 'paypal':
            $pay_sdk = $order_data['payment_method'];
            break;

        case 'wechat':
            $pay_sdk = _pz('pay_wechat_sdk_options');
            break;

        case 'alipay':
            $pay_sdk = _pz('pay_alipay_sdk_options');
            break;
    }

    //支付接口挂钩
    $pay_sdk = apply_filters('zibpay_initiate_paysdk', $pay_sdk, $order_data);

    if (!$pay_sdk || 'null' == $pay_sdk) {
        return array('error' => 1, 'msg' => '当前订单不支持此方式支付，请联系客服');
    }

    //支付结果挂钩
    $payresult = apply_filters('zibpay_initiate_' . $pay_sdk, $order_data);

    $payresult = array_merge($order_data, $payresult);
    return $payresult;
}

//PayPal发起支付
function zibpay_zibpay_initiate_paypal($order_data)
{
    //获取参数
    $config = zibpay_get_payconfig('paypal');
    if (empty($config['username']) || empty($config['password']) || empty($config['signature'])) {
        return array('error' => 1, 'msg' => 'PayPal接口缺少配置参数');
    }

    require_once get_theme_file_path('/zibpay/sdk/paypal/paypal.php');
    require_once get_theme_file_path('/zibpay/sdk/paypal/httprequest.php');

    $total = bcmul($order_data['order_price'], $config['rates'], 2);

    $return_url       = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();
    $config['cancel'] = add_query_arg(['return_url' => urlencode($return_url), 'cancel' => 'cancel'], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/paypal/return.php');
    $config['return'] = add_query_arg(['return_url' => urlencode($return_url), 'order_num' => $order_data['order_num']], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/paypal/return.php');

    $PayPal = new \PayPal($config);
    $result = $PayPal->doExpressCheckout($total, $order_data['order_name'], $order_data['order_num'], $config['currency']);

    if (!is_string($result)) {
        return array('error' => 1, 'result' => $result, 'msg' => isset($result['L_LONGMESSAGE0']) ? 'PayPal接口错误：' . $result['L_LONGMESSAGE0'] : __('PayPal配置错误，或网络连接失败', 'zibll'));
    }

    return array('open_url' => true, 'url' => $result);
}

//V免签发起支付
function zibpay_initiate_vmqphp($order_data)
{
    //获取参数
    $config = zibpay_get_payconfig('vmqphp');
    if (empty($config['apiurl']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => 'V免签接口缺少配置参数');
    }

    require_once get_theme_file_path('/zibpay/sdk/vmq/vmq.class.php');

    //建立请求
    $PaySubmit = new vmqphpPay($config);

    $payment_method = 'alipay' == $order_data['payment_method'] ? 2 : 1;
    $return_url     = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();
    $param          = $order_data['payment_method'] . '|' . $return_url;

    $parameter = array(
        'payId'     => $order_data['order_num'], //本地订单号
        'type'      => $payment_method,
        'price'     => $order_data['order_price'],
        'notifyUrl' => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/vmq/notify.php',
        'returnUrl' => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/vmq/return.php',
        'param'     => $param,
        'isHtml'    => 0,
    );

    if (empty($config['no_open'])) {
        $parameter['isHtml'] = 1;
        $url                 = $PaySubmit->buildURL($parameter);
        return array('url' => $url, 'open_url' => true);
    }

    $get_json = $PaySubmit->get($parameter);

    if (isset($get_json['code']) && 1 == $get_json['code'] && !empty($get_json['data']['payUrl'])) {
        $result['url_qrcode']  = zib_get_qrcode_base64($get_json['data']['payUrl']);
        $reallyPrice           = !empty($get_json['data']['reallyPrice']) ? round($get_json['data']['reallyPrice'], 2) : $order_data['order_price'];
        $result['order_price'] = $reallyPrice;
        $result['more_html']   = '<div class="badg btn-block c-yellow em09 padding-h10">请扫码后支付' . $reallyPrice . '元，为了确保支付成功，请注意付款金额请勿出错</div>';
        return $result;
    }
    $msg = !empty($get_json['msg']) ? $get_json['msg'] : '接口请求错误';
    return array('error' => 1, 'msg' => $msg);
}

//易支付发起支付
function zibpay_initiate_epay($order_data)
{
    //获取参数
    $config = zibpay_get_payconfig('epay');
    if (empty($config['apiurl']) || empty($config['partner']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => '易支付缺少配置参数');
    }

    require_once get_theme_file_path('/zibpay/sdk/epay/epay.class.php');

    $payment_method = 'alipay' == $order_data['payment_method'] ? 'alipay' : 'wxpay';

    $parameter = array(
        'pid'          => trim($config['partner']),
        'type'         => $payment_method,
        'notify_url'   => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/epay/notify.php',
        'return_url'   => add_query_arg(['redirect_url' => urlencode($order_data['return_url'])], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/epay/notify.php'),
        'out_trade_no' => $order_data['order_num'], //本地订单号
        'name'         => $order_data['order_name'],
        'money'        => $order_data['order_price'],
        'sitename'     => get_bloginfo('name'),
        'clientip'     => zib_get_remote_ip_addr() ?: '127.0.0.1',
    );

    //建立请求
    $is_mobile = wp_is_mobile();
    $EpayCore  = new EpayCore($config);

    if ($is_mobile || empty($config['qrcode'])) {
        $html_text = $EpayCore->pagePay($parameter);
        return array('form_html' => '<div class="hide">' . $html_text . '</div>');
    } else {
        $result = $EpayCore->apiPay($parameter);

        if (isset($result['code']) && 1 == $result['code'] && (!empty($result['code_url']) || !empty($result['qrcode']))) {
            $_code_url            = !empty($result['code_url']) ? ($result['code_url']) : $result['qrcode'];
            $result['url_qrcode'] = zib_get_qrcode_base64(urldecode($_code_url));
            $result['check_sdk']  = 'epay'; //接口查询

            if (!empty($result['money'])) {
                if ($result['order_price'] != $result['money']) {
                    $result['more_html'] = '<div class="badg btn-block c-yellow em09 padding-h10">请扫码后支付' . $result['money'] . '元，为了确保支付成功，请注意付款金额请勿出错</div>';
                }
                $result['order_price'] = $result['money'];
            }
        } elseif (isset($result['code']) && 1 == $result['code'] && (!empty($result['payurl']))) {
            $result['url']      = $result['payurl'];
            $result['open_url'] = true;
        } else {
            $result['error'] = 1;
            $result['msg']   = !empty($result['msg']) ? $result['msg'] : '收款码请求失败';
        }

        return $result;
    }
}

/**支付宝官方发起支付 */
function zibpay_initiate_official_alipay($order_data = array())
{

    //获取参数
    $config = zibpay_get_payconfig('official_alipay');

    // 判断是否开启H5
    if (wp_is_mobile() && $config['h5'] && $config['webappid'] && $config['webprivatekey']) {
        if (empty($config['publickey'])) {
            return array('error' => 1, 'msg' => '缺少支付宝公钥参数');
        }
        /**支付宝企业支付-手机网站支付产品 */
        // 公共配置
        $params        = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $config['webappid'];
        /**网站应用-APPID */
        $params->appPrivateKey = $config['webprivatekey'];
        /**网站应用-应用私钥 */

        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

        // 支付接口
        $request                               = new \Yurun\PaySDK\AlipayApp\Wap\Params\Pay\Request;
        $request->notify_url                   = ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/alipay/notify.php'; // 支付后通知地址（作为支付成功回调，这个可靠）
        $request->return_url                   = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url(); // 支付后跳转返回地址
        $request->return_url                   = add_query_arg(['return_url' => urlencode($request->return_url), 'app_type' => 'wap'], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/alipay/return.php'); // 新版回调地址测试
        $request->businessParams->out_trade_no = $order_data['order_num']; // 商户订单号
        $request->businessParams->total_amount = $order_data['order_price']; // 价格
        $request->businessParams->subject      = $order_data['order_name']; // 商品标题

        $pay->prepareExecute($request, $url, $data);
        if (empty($data['sign'])) {
            return array('error' => 1, 'msg' => 'APPID或应用私钥错误，导致签名失败');
        }

        return array('open_url' => 1, 'url' => $url);
    } elseif ($config['webappid'] && $config['webprivatekey'] && (empty($config['privatekey']) || empty($config['appid']))) {
        /**支付宝企业支付-电脑网站支付 */
        if (empty($config['publickey'])) {
            return array('error' => 1, 'msg' => '缺少支付宝公钥参数');
        }
        // 公共配置
        $params        = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $config['webappid'];
        /**网站应用-APPID */
        $params->appPrivateKey = $config['webprivatekey'];
        /**网站应用-应用私钥 */
        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

        // 支付接口
        $request             = new \Yurun\PaySDK\AlipayApp\Page\Params\Pay\Request;
        $request->notify_url = ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/alipay/notify.php'; // 支付后通知地址（作为支付成功回调，这个可靠）
        $request->return_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url(); // 支付后跳转返回地址

        $request->return_url = add_query_arg(['return_url' => urlencode($request->return_url), 'app_type' => 'wap'], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/alipay/return.php'); // 新版回调地址测试

        $request->businessParams->out_trade_no = $order_data['order_num']; // 商户订单号
        $request->businessParams->total_amount = $order_data['order_price']; // 价格
        $request->businessParams->subject      = $order_data['order_name']; // 商品标题

        $pay->prepareExecute($request, $url, $data);
        if (empty($data['sign'])) {
            return array('error' => 1, 'msg' => 'APPID或应用私钥错误，导致签名失败');
        }

        return array('open_url' => 1, 'url' => $url);
    } else {
        /**支付宝当面付 */
        if (empty($config['privatekey']) || empty($config['appid'])) {
            return array('error' => 1, 'msg' => '支付宝后台配置无效');
        }
        if (empty($config['publickey'])) {
            return array('error' => 1, 'msg' => '缺少支付宝公钥参数');
        }

        // 配置文件
        $params                = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID         = $config['appid'];
        $params->appPrivateKey = $config['privatekey'];
        $params->appPublicKey  = $config['publickey'];
        // SDK实例化，传入公共配置
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        // 支付接口
        $request                               = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request;
        $request->notify_url                   = ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/alipay/notify.php'; // 支付后通知地址
        $request->businessParams->out_trade_no = $order_data['order_num']; // 商户订单号
        $request->businessParams->total_amount = $order_data['order_price']; // 价格
        $request->businessParams->subject      = $order_data['order_name']; // 商品标题

        // 调用接口
        try {
            $data = $pay->execute($request);
        } catch (Exception $e) {
            $e_msg = $e->getMessage();
            if (strpos($e_msg, '同步返回数据验证失败') !== false) {
                $e_msg = '同步返回数据验证失败，请检查支付宝公钥是否正确';
            }
            return array('error' => 1, 'msg' => $e_msg);
        }

        if (!empty($data['alipay_trade_precreate_response']['qr_code'])) {
            $data['alipay_trade_precreate_response']['url_qrcode'] = zib_get_qrcode_base64($data['alipay_trade_precreate_response']['qr_code']);
            $data['alipay_trade_precreate_response']['msg']        = '处理完成，请扫码支付';
            if (wp_is_mobile()) {
                $data['alipay_trade_precreate_response']['more_html'] = '<a href="' . esc_url($data['alipay_trade_precreate_response']['qr_code']) . '" class="but btn-block c-blue em09 padding-h10">跳转到支付宝APP付款</a>';
            }

            $data['alipay_trade_precreate_response']['check_sdk'] = 'official_alipay'; //接口查询
            return $data['alipay_trade_precreate_response'];
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    }
}

/**微信官方企业支付发起支付 */
function zibpay_initiate_official_wechat($order_data = array())
{

    //获取参数
    $config = zibpay_get_payconfig('official_wechat');
    if (empty($config['merchantid']) || empty($config['appid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => '微信支付后台配置无效');
    }

    $params = new \Yurun\PaySDK\Weixin\Params\PublicParams;

    $params->appID  = $config['appid'];
    $params->mch_id = $config['merchantid'];
    $params->key    = $config['key'];

    // SDK实例化，传入公共配置
    $pay = new \Yurun\PaySDK\Weixin\SDK($params);

    //JSAPI判断
    $zibpay_is_wechat_app = zib_is_wechat_app();

    $gzh_appid  = $config['appid'];
    $open_id    = false;
    $return_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();

    // 判断是否开启手机版跳转
    if (wp_is_mobile() && $config['h5'] && !$zibpay_is_wechat_app) {
        // H5支付接口
        $request                   = new \Yurun\PaySDK\Weixin\H5\Params\Pay\Request;
        $request->body             = $order_data['order_name']; // 商品描述
        $request->out_trade_no     = $order_data['order_num']; // 订单号
        $request->total_fee        = round($order_data['order_price'] * 100); // 订单总金额，单位为：分
        $request->spbill_create_ip = !empty($order_data['ip_address']) ? $order_data['ip_address'] : '127.0.0.1'; // 客户端ip，必须传正确的用户ip，否则会报错
        $request->notify_url       = ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/weixin/notify.php'; // 异步通知地址
        $request->scene_info       = new \Yurun\PaySDK\Weixin\H5\Params\SceneInfo;
        //场景信息
        $request->scene_info->type     = 'Wap'; // 可选值：IOS、Android、Wap
        $request->scene_info->wap_url  = home_url(); //h5支付返回地址
        $request->scene_info->wap_name = zib_str_cut(get_bloginfo('name'), 0, 12); //WAP 网站名
        // 调用接口
        $result = $pay->execute($request);
        if ($pay->checkResult()) {
            /**支付订单成功 */
            $result['open_url'] = 1;
            $redirect_url       = add_query_arg(['order_num' => $order_data['order_num'], 'sign' => md5($order_data['order_num'] . 'zib_official_wechat'), 'return_url' => urlencode($return_url)], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/weixin/return.php');
            $result['url']      = add_query_arg('redirect_url', ($redirect_url), $result['mweb_url']);

            return $result;
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    } elseif ($config['jsapi'] && $zibpay_is_wechat_app) {
        //1.从微信登录的用户中获取openid
        $wx_oauth_config = get_oauth_config('weixingzh');
        if ($order_data['user_id'] && $wx_oauth_config['appid'] === $gzh_appid) {
            $open_id = get_user_meta($order_data['user_id'], 'oauth_weixingzh_openid', true);
        }
        //2.从跳转连接中获取openid
        if (!$open_id && !empty($_REQUEST['openid'])) {
            $open_id = $_REQUEST['openid']; //用户微信openid
        }
        //仍然没有openid则使用接口跳转获取
        if (!$open_id) {
            //获取openid
            $redirect_uri = add_query_arg(array(
                'zippay'     => 'wechat',
                'action'     => 'get_gzh_open_id',
                'return_url' => urlencode($return_url),
            ), admin_url('admin-ajax.php'));

            $api_url  = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
            $api_data = array(
                'appid'         => $gzh_appid,
                'redirect_uri'  => $redirect_uri,
                'response_type' => 'code',
                'scope'         => 'snsapi_base',
                'state'         => 'zib_pay_wechat',
            );

            $url                     = $api_url . http_build_query($api_data) . '#wechat_redirect';
            $_SESSION['ZIBPAY_POST'] = $_POST;
            return array('open_url' => 1, 'url' => $url);
        }

        //JSAPI模式，在微信APP内调用
        $request                   = new \Yurun\PaySDK\Weixin\JSAPI\Params\Pay\Request;
        $request->body             = $order_data['order_name']; // 商品描述
        $request->out_trade_no     = $order_data['order_num']; // 订单号
        $request->total_fee        = round($order_data['order_price'] * 100); // 订单总金额，单位为：分
        $request->spbill_create_ip = !empty($order_data['ip_address']) ? $order_data['ip_address'] : '127.0.0.1'; // 客户端ip，必须传正确的用户ip，否则会报错
        $request->notify_url       = ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/weixin/notify.php'; // 异步通知地址

        $request->openid = $open_id; // 必须设置openid

        // 调用接口
        $result = $pay->execute($request);
        if ($pay->checkResult()) {
            $request            = new \Yurun\PaySDK\Weixin\JSAPI\Params\JSParams\Request;
            $request->prepay_id = $result['prepay_id'];
            $jsapiParams        = $pay->execute($request);
            // 最后需要将数据传给js，使用WeixinJSBridge进行支付
            $result['jsapiParams']  = $jsapiParams;
            $result['jsapi_return'] = add_query_arg(['order_num' => $order_data['order_num'], 'sign' => md5($order_data['order_num'] . 'zib_official_wechat'), 'return_url' => urlencode($return_url)], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/weixin/return.php');
            return $result;
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    } else {
        // PC扫码支付接口
        $request                   = new \Yurun\PaySDK\Weixin\Native\Params\Pay\Request;
        $request->body             = $order_data['order_name']; // 商品描述
        $request->out_trade_no     = $order_data['order_num']; // 订单号
        $request->total_fee        = round($order_data['order_price'] * 100); // 订单总金额，单位为：分
        $request->spbill_create_ip = empty($order_data['ip_address']) ? $order_data['ip_address'] : '127.0.0.1'; // 客户端ip，必须传正确的用户ip，否则会报错
        $request->notify_url       = ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/weixin/notify.php'; // 异步通知地址
        // 调用接口
        $result   = $pay->execute($request);
        $shortUrl = $result['code_url'];
        if (is_array($result) && $shortUrl) {
            $result['url_qrcode'] = zib_get_qrcode_base64($shortUrl);
            $result['check_sdk']  = 'official_wechat'; //接口查询

            return $result;
        } else {
            return array('error' => 1, 'msg' => $pay->getError() . ' ' . $pay->getErrorCode());
        }
    }
}

//微信官方支付获取openid
function zib_ajax_get_gzh_open_id()
{
    $return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '';
    $code       = !empty($_REQUEST['code']) ? $_REQUEST['code'] : '';

    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';

    $config = zibpay_get_payconfig('official_wechat');
    if (!$config['appsecret']) {
        $wxConfig            = get_oauth_config('weixingzh');
        $config['appid']     = $wxConfig['appid'];
        $config['appsecret'] = $wxConfig['appkey'];
    }

    $url_data = array(
        'appid'      => $config['appid'],
        'secret'     => $config['appsecret'],
        'code'       => $code,
        'grant_type' => 'authorization_code',
    );
    $http     = new Yurun\Util\HttpRequest;
    $response = $http->timeout(10000)->get($url, $url_data);
    $result   = $response->json(true);

    if (!empty($result['openid'])) {
        $return_url = add_query_arg(array('zippay' => 'wechat', 'openid' => $result['openid']), $return_url);
        header('location:' . $return_url);
        exit();
    } else {
        wp_die(
            '<h3>' . __('微信支付错误：') . '</h3>' .
            '<p>' . json_encode($result) . '</p>',
            403
        );
        exit;
    }
}
add_action('wp_ajax_get_gzh_open_id', 'zib_ajax_get_gzh_open_id');
add_action('wp_ajax_nopriv_get_gzh_open_id', 'zib_ajax_get_gzh_open_id');

/**讯虎虎皮椒V3发起支付 */
function zibpay_initiate_xunhupay($order_data = array())
{

    $payment = 'alipay' == $order_data['payment_method'] ? 'alipay' : 'wechat';

    //获取参数
    $config = zibpay_get_payconfig('xunhupay');
    if ('wechat' == $payment && empty($config['wechat_appid']) && empty($config['wechat_appsecret'])) {
        return array('error' => 1, 'msg' => '未设置appid或者appsecret');
    }
    if ('alipay' == $payment && empty($config['alipay_appid']) && empty($config['alipay_appsecret'])) {
        return array('error' => 1, 'msg' => '未设置appid或者appsecret');
    }

    require_once get_theme_file_path('/zibpay/sdk/xunhupay/api.php');

    $trade_order_id = $order_data['order_num'];

    if ('wechat' == $payment) {
        $appid     = $config['wechat_appid'];
        $appsecret = $config['wechat_appsecret'];
        $payment   = 'wechat';
    } else {
        $appid     = $config['alipay_appid'];
        $appsecret = $config['alipay_appsecret'];
        $payment   = 'alipay';
    }

    $my_plugin_id = 'zibpay_xunhupay_' . $payment;
    $home_url     = home_url();

    $data = array(
        'version'        => '1.1', //固定值，api 版本，目前暂时是1.1
        'lang'           => 'zh-cn', //必须的，zh-cn或en-us 或其他，根据语言显示页面
        'plugins'        => $my_plugin_id, //必须的，根据自己需要自定义插件ID，唯一的，匹配[a-zA-Z\d\-_]+
        'appid'          => $appid, //必须的，APPID
        'trade_order_id' => $trade_order_id, //必须的，网站订单ID，唯一的，匹配[a-zA-Z\d\-_]+
        'payment'        => $payment, //必须的，支付接口标识：wechat(微信接口)|alipay(支付宝接口)
        'total_fee'      => $order_data['order_price'], //人民币，单位精确到分(测试账户只支持0.1元内付款)
        'title'          => $order_data['order_name'], //必须的，订单标题，长度32或以内
        'time'           => time(), //必须的，当前时间戳，根据此字段判断订单请求是否已超时，防止第三方攻击服务器
        'notify_url'     => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/xunhupay/notify.php', //必须的，支付成功异步回调接口
        'return_url'     => !empty($order_data['return_url']) ? $order_data['return_url'] : $home_url, //必须的，支付成功后的跳转地址
        'callback_url'   => !empty($order_data['return_url']) ? $order_data['return_url'] : $home_url, //必须的，支付发起地址（未支付或支付失败，系统会会跳到这个地址让用户修改支付信息）
        'modal'          => null, //可空，支付模式 ，可选值( full:返回完整的支付网页; qrcode:返回二维码; 空值:返回支付跳转链接)
        'nonce_str'      => str_shuffle(time()), //必须的，随机字符串，作用：1.避免服务器缓存，2.防止安全密钥被猜测出来
    );
    if ('wechat' == $payment) {
        $data['type']     = 'WAP';
        $data['wap_url']  = $home_url;
        $data['wap_name'] = $home_url;
    }

    $hashkey      = $appsecret;
    $data['hash'] = XH_Payment_Api::generate_xh_hash($data, $hashkey);

    $url = 'https://api.xunhupay.com/payment/do.html';
    if (!empty($config['api_url'])) {
        $url = $config['api_url'];
    }

    try {
        $response = XH_Payment_Api::http_post($url, json_encode($data));
        $result   = $response ? json_decode($response, true) : null;
        if (!$result) {
            throw new Exception('Internal server error', 500);
        }

        $hash = XH_Payment_Api::generate_xh_hash($result, $hashkey);
        if (!isset($result['hash']) || $hash != $result['hash']) {
            throw new Exception('Invalid sign!', 500);
        }

        if (0 != $result['errcode']) {
            throw new Exception($result['errmsg'], $result['errcode']);
        }

        $pay_url = $result['url'];

        $result['open_url'] = wp_is_mobile();
        return $result;
    } catch (Exception $e) {
        //echo "errcode:{$e->getCode()},errmsg:{$e->getMessage()}";
        return array('error' => 1, 'errcode' => $e->getCode(), 'msg' => $e->getMessage());
    }
}

//PAYJS发起支付
function zibpay_initiate_payjs($order_data)
{
    //获取参数
    $config = zibpay_get_payconfig('payjs');
    if (empty($config['mchid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => '未设置mchid或者key');
    }

    require_once get_theme_file_path('/zibpay/sdk/payjs/payjs.class.php');

    $mchid          = $config['mchid'];
    $key            = $config['key'];
    $payment_method = 'alipay' == $order_data['payment_method'] ? 'alipay' : '';
    $data           = [
        'mchid'        => $mchid, //商户号
        'total_fee'    => round($order_data['order_price'] * 100), //金额。单位：分
        'out_trade_no' => $order_data['order_num'], //本地订单号
        'body'         => $order_data['order_name'], //订单标题
        'notify_url'   => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/payjs/notify.php', //异步通知的回调地址
        'type'         => $payment_method, //支付宝交易传值：alipay ，微信支付无需此字段
        'attach'       => 'zibpay_payjs', //用户自定义数据，在notify的时候会原样返回
    ];

    $payjs = new Payjs($mchid, $key);

    if (zib_is_wechat_app() && 'wechat' === $order_data['payment_method']) {
        //微信内使用收银台模式
        $data['callback_url'] = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url(); //用户支付成功后，前端跳转地址。
        $data['auto']         = 1; //auto=1：无需点击支付按钮，自动发起支付。
        $data['logo']         = _pz('iconpng'); //auto=1：无需点击支付按钮，自动发起支付。
        $url                  = $payjs->cashier($data);
        if (isset($result['status']) && 0 == $result['status']) {
            return array('error' => 1, 'msg' => $result['return_msg']);
        }
        return array('open_url' => 1, 'url' => $url);
    }

    //扫码支付
    $result = $payjs->native($data);

    if (isset($result['return_code']) && isset($result['qrcode'])) {
        $result['url_qrcode'] = $result['qrcode'];
    } else {
        $result = array('error' => 1, 'result' => $result, 'msg' => !empty($result['return_msg']) ? $result['return_msg'] : 'PayJS收款接口连接失败');
    }
    return $result;
}

//讯虎迅虎PAY发起支付（虎皮椒V4）
function zibpay_initiate_xhpay($order_data)
{
    //获取参数
    $config = zibpay_get_payconfig('xhpay');
    if (empty($config['mchid']) || empty($config['key'])) {
        return array('error' => 1, 'msg' => '未设置商户号或者API秘钥');
    }

    $is_mobile    = wp_is_mobile();
    $is_alipay_v2 = !empty($config['alipay_v2']);
    $mchid        = $config['mchid'];
    $key          = $config['key'];

    //引入资源文件
    require_once get_theme_file_path('/zibpay/sdk/xhpay/xhpay.class.php');

    $payment_method = 'alipay' === $order_data['payment_method'] ? 'alipay' : 'wechat';

    $order_data['order_name'] = strtolower($order_data['order_name']); //订单名称转小写，避免出错
    $data                     = [
        'mchid'        => $mchid, //商户号
        'total_fee'    => round($order_data['order_price'] * 100), //金额。单位：分
        'out_trade_no' => $order_data['order_num'], //本地订单号
        'body'         => $order_data['order_name'], //订单标题
        'goods_detail' => $order_data['order_name'], //订单标题
        'notify_url'   => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/xhpay/notify.php', //异步通知的回调地址
        'type'         => $payment_method, //支付宝交易传值：alipay ，微信支付无需此字段
        'attach'       => 'zibpay_xhpay_' . $payment_method, //用户自定义数据，在notify的时候会原样返回
    ];
    $return_url = !empty($order_data['return_url']) ? $order_data['return_url'] : home_url();

    $xhpay = new Xhpay($config);
    if (zib_is_wechat_app() && 'wechat' === $payment_method) {
        //微信内JSAPI支付
        if (empty($_REQUEST['openid'])) {
            //第一步，跳转到获取openid的页面，储存_POST信息
            $return_url              = add_query_arg('zippay', 'wechat', $return_url);
            $url                     = 'https://admin.xunhuweb.com/pay/openid?mchid=' . $mchid . '&redirect_url=' . urlencode($return_url);
            $_SESSION['ZIBPAY_POST'] = $_POST;
            return array('open_url' => 1, 'url' => $url);
        } else {
            //第二步，发起JSAPI支付
            $data['openid']       = $_REQUEST['openid']; //用户微信openid
            $data['redirect_url'] = urlencode($return_url);

            $result = $xhpay->jsapi($data);
            if (strtolower($result['return_code']) == 'success' && $result['jsapi']) {
                $result['jsapiParams'] = json_decode($result['jsapi']);
                return $result;
            } else {
                return array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
            }
        }
    }

    if ($is_mobile && $payment_method === 'wechat' && (!isset($config['wx_h5']) || $config['wx_h5'])) {
        //手机端微信H5支付
        $data['wap_url']  = add_query_arg(['order_num' => $order_data['order_num'], 'sign' => md5($order_data['order_num'] . 'zib_xhpay'), 'return_url' => urlencode($return_url)], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/xhpay/return.php');
        $data['wap_name'] = get_bloginfo('name'); //网站名称（建议与网站名称一致）。
        $result           = $xhpay->h5($data);
        if ($result['return_code'] == 'SUCCESS' && $result['mweb_url']) {
            return array('open_url' => 1, 'url' => add_query_arg('redirect_url', $data['wap_url'], $result['mweb_url']));
        } else {
            return array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
        }
    }

    if ($is_mobile && $payment_method === 'alipay') {

        $data['redirect_url'] = add_query_arg(['order_num' => $order_data['order_num'], 'sign' => md5($order_data['order_num'] . 'zib_xhpay'), 'return_url' => urlencode($return_url)], ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/xhpay/return.php');

        if (!empty($config['alipay_v2'])) {
            //手机端支付宝2.0WAP新接口
            $result = $xhpay->wap($data);
            if ($result['return_code'] == 'SUCCESS' && $result['mweb_url']) {
                return array('open_url' => 1, 'url' => $result['mweb_url']);
            } else {
                return array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
            }
        }

        //收银台模式
        $url = $xhpay->cashier($data);
        if ($url) {
            return array('open_url' => 1, 'url' => $url);
        }
    }

    //支付宝新2.0接口
    if (!empty($config['alipay_v2']) && $payment_method === 'alipay') {
        $data['trade_type'] = 'WEB';
    }

    //默认扫码支付
    $result = $xhpay->native($data);
    if ('SUCCESS' == $result['return_code'] && $result['code_url']) {
        $result['check_sdk']  = 'xhpay';
        $result['url_qrcode'] = zib_get_qrcode_base64($result['code_url']);
    } else {
        $result = array('error' => 1, 'msg' => $result['return_msg'] . ':' . $result['err_msg']);
    }
    return $result;
}

/**码支付发起支付 */
function zibpay_initiate_codepay($order_data = array())
{

    $payment = 'alipay' == $order_data['payment_method'] ? 'alipay' : 'wechat';

    //获取参数
    $config = zibpay_get_payconfig('codepay');
    if (empty($config['id']) || empty($config['key']) || empty($config['token'])) {
        return array('error' => 1, 'msg' => '码支付配置错误');
    }

    if ('wechat' == $payment) {
        $type = 3;
    } else {
        $type = 1;
    }

    $codepay_id  = $config['id']; //这里改成码支付ID
    $codepay_key = $config['key']; //这是您的通讯密钥

    $data = array(
        'id'         => $codepay_id, //你的码支付ID
        'token'      => $config['token'], //你的码支付token
        'pay_id'     => $order_data['order_num'], //唯一标识 订单号
        'type'       => $type, //1支付宝支付 3微信支付 2QQ钱包
        'price'      => $order_data['order_price'], //金额
        'param'      => 'zibpay', //自定义参数
        'notify_url' => ZIB_TEMPLATE_DIRECTORY_URI . '/zibpay/shop/codepay/notify.php', //通知地址
        'return_url' => !empty($order_data['return_url']) ? $order_data['return_url'] : home_url(), //跳转地址
    ); //构造需要传递的参数

    ksort($data); //重新排序$data数组
    reset($data); //内部指针指向数组中的第一个元素

    $sign = ''; //初始化需要签名的字符为空
    $urls = ''; //初始化URL参数为空

    foreach ($data as $key => $val) {
        //遍历需要传递的参数
        if ('' == $val || 'sign' == $key) {
            continue;
        } //跳过这些不参数签名
        if ('' != $sign) {
            //后面追加&拼接URL
            $sign .= '&';
            $urls .= '&';
        }
        $sign .= "$key=$val"; //拼接为url参数形式
        $urls .= "$key=" . urlencode($val); //拼接为url参数形式并URL编码参数值
    }
    $query = $urls . '&sign=' . md5($sign . $codepay_key) . '&page=4'; //创建订单所需的参数
    //    $query = $urls.'&page=4'; //创建订单所需的参数
    $api_url = !empty($config['apiurl']) ? $config['apiurl'] : 'https://api.xiuxiu888.com/';
    $url     = rtrim($api_url, '/') . "/creat_order/?{$query}"; //支付页面

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->ua('YurunHttp')->get($url);

    $result     = $response->body();
    $resultData = json_decode($result, true);

    if (isset($resultData['status']) && 0 == $resultData['status']) {
        //返回真实金额
        $money = !empty($resultData['money']) ? round($resultData['money'], 2) : $order_data['order_price'];
        return array('url_qrcode' => $resultData['qrcode'], 'order_price' => $money, 'more_html' => '<div class="badg btn-block c-yellow em09 padding-h10">请扫码后支付' . $money . '元，为了确保支付成功，请注意付款金额请勿出错</div>');
    }

    $msg = !empty($resultData['msg']) ? $resultData['msg'] : '码支付接口请求错误';
    return array('error' => 1, 'msg' => $msg);
}

//余额支付
function zibpay_initiate_balance($order_data = array())
{

    if (empty($order_data['user_id'])) {
        return array('error' => 1, 'msg' => '请先登录');
    }

    //函数节流
    zib_ajax_debounce('balance_change', $order_data['user_id']);

    $order_type = !empty($_POST['order_type']) ? $_POST['order_type'] : 0;
    if ($order_type && !zibpay_is_allow_balance_pay($order_type)) {
        return array('error' => 1, 'msg' => '当前交易不支持余额支付');
    }

    $user_balance = zibpay_get_user_balance($order_data['user_id']);
    if ($user_balance < $order_data['order_price']) {
        return array('error' => 1, 'msg' => '余额不足，请先充值');
    }

    //余额变动
    $blog_name = get_bloginfo('name');
    $data      = array(
        'order_num' => $order_data['order_num'], //订单号
        'value'     => -$order_data['order_price'], //值 整数为加，负数为减去
        'type'      => '余额支付',
        'desc'      => str_replace('-' . $blog_name, '', str_replace($blog_name . '-', '', $order_data['order_name'])), //说明
    );

    if (!zibpay_update_user_balance($order_data['user_id'], $data)) {
        return array('error' => 1, 'msg' => '余额不足，请先充值');
    }

    //订单变动
    $pay = array(
        'order_num'  => $order_data['order_num'],
        'pay_type'   => 'balance',
        'pay_price'  => $order_data['order_price'],
        'pay_num'    => $order_data['order_num'],
        'pay_detail' => array(
            'balance' => $order_data['order_price'],
        ),
    );

    // 更新订单状态
    ZibPay::payment_order($pay);
    return array('error' => 0, 'reload' => true, 'msg' => '支付成功', 'payok' => 1, 'return_url' => $order_data['return_url']);
}

//卡密支付
function zibpay_initiate_card_pass($order_data = array())
{

    global $zibpay_card_pass;

    if (!isset($zibpay_card_pass->id)) {
        return array('error' => 1, 'msg' => '异常错误');
    }

    //函数节流
    zib_ajax_debounce('card_pass_initiate_pay', $zibpay_card_pass->id);

    if (!empty($order_data['user_id'])) {
        $card_pass_new_meta = array(
            'user_id' => $order_data['user_id'],
        );
    }

    // 更新卡密状态
    zibpay_use_card_pass($zibpay_card_pass, $order_data['order_num'], $card_pass_new_meta);

    // 更新订单状态
    $pay = array(
        'order_num' => $order_data['order_num'],
        'pay_type'  => 'card_pass',
        'pay_price' => 0,
        'pay_num'   => $order_data['order_num'], //必须和order_num一致
    );
    ZibPay::payment_order($pay);

    $msg = ($order_data['order_type'] ?? 0) == 8 ? '卡密充值成功，充值金额：' . $order_data['order_price'] : '卡密兑换成功';
    return array('error' => 0, 'reload' => true, 'msg' => $msg, 'payok' => 1, 'return_url' => $order_data['return_url']);
}

//积分支付
function zibpay_points_initiate_order()
{

    $order_type = !empty($_REQUEST['order_type']) ? (int) $_REQUEST['order_type'] : 0;
    $user_id    = get_current_user_id();

    if (!$user_id) {
        zib_send_json_error('请先登录');
    }

    //函数节流
    zib_ajax_debounce('points_change', $user_id);

    $__data = array(
        'user_id'     => $user_id,
        'order_price' => 0,
        'pay_type'    => 'points',
        'pay_price'   => 0,
        'pay_time'    => current_time('Y-m-d H:i:s'),
    );

    $__mate_order_data = array(
        'prices' => array(
        ),
        'count'  => 1,
    );

    switch ($order_type) {
        case 4: //会员开通、升级、续费
            if (!isset($_REQUEST['product_id']) || $_REQUEST['product_id'] == 'null') {
                zib_send_json_error('请选择需要兑换的会员');
            }

            //已经是会员了，就无法再兑换了
            if (zib_get_user_vip_level($user_id)) {
                return;
            }

            $product_id = (int) $_REQUEST['product_id'];
            $lists_opt  = _pz('pay_vip_points_exchange_product');
            if (empty($lists_opt[$product_id]['points'])) {
                zib_send_json_error('会员商品不存在，请重新选择');
            }

            $product_id_5                      = $lists_opt[$product_id]['time'] === 'Permanent' ? 'Permanent' : $lists_opt[$product_id]['time'] . $lists_opt[$product_id]['unit'];
            $__data['product_id']              = 'vip_' . $lists_opt[$product_id]['level'] . '_' . $product_id . '_points_' . $product_id_5;
            $__data['order_type']              = $order_type;
            $__price                           = (int) $lists_opt[$product_id]['points'];
            $__mate_order_data['vip_pay_type'] = 'points_exchange';
            break;

        default: //文章类型的
            $post_id  = !empty($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
            $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
            $post     = get_post($post_id);
            if (empty($post->ID) || empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type'] || !zibpay_post_is_points_modo($pay_mate)) {
                zib_send_json_error('商品数据获取错误');
            }

            $__data['order_type']  = $pay_mate['pay_type'];
            $__data['post_id']     = $post_id;
            $__data['post_author'] = $post->post_author;
            $__data['order_type']  = $pay_mate['pay_type'];
            $__price               = (int) $pay_mate['points_price'];

            $__mate_order_data['author_id']     = $post->post_author;
            $__mate_order_data['product_id']    = $post->ID;
            $__mate_order_data['product_title'] = $post->post_title;

            //会员优惠价
            $vip_level = zib_get_user_vip_level($user_id);
            if ($vip_level && _pz('pay_user_vip_' . $vip_level . '_s', true)) {
                $vip_price = isset($pay_mate['vip_' . $vip_level . '_points']) ? (int) $pay_mate['vip_' . $vip_level . '_points'] : 0;
                //会员金额和正常金额取更小值
                $__price = $vip_price < $__price ? $vip_price : $__price;
            }

            if ($order_type == 2) {
                $price_type = !empty($_REQUEST['price_type']) ? $_REQUEST['price_type'] : '';
                if ($price_type === 'download_limit_over') {
                    $__price = !empty($pay_mate['download_limit_over_price']) ? (int) $pay_mate['download_limit_over_price'] : 0;
                }
            }

            if ($__price <= 0) {
                zib_send_json_error('商品售价错误');
            }

    }

    //我的积分
    $user_points = zibpay_get_user_points($user_id);
    if ($__price > $user_points) {
        zib_send_json_error('积分不足，暂时无法支付');
    }

    //分成数据
    if (_pz('pay_income_s') && !empty($__data['post_author'])) {
        $points_ratio  = zibpay_get_user_income_points_ratio($__data['post_author']);
        $income_points = (int) (($__price * $points_ratio) / 100);
        if ($income_points > 0) {
            $__data['income_detail'] = array(
                'points' => $income_points,
            );
        }
    }

    $__mate_order_data['prices']['pay_price']   = $__price;
    $__mate_order_data['prices']['total_price'] = $__price;
    $__mate_order_data['prices']['unit_price']  = (int) ($__price / $__mate_order_data['count'] ?: 1);

    $__data['order_price'] = $__price;
    $__data['pay_price']   = $__price;
    $__data['pay_detail']  = array(
        'points' => $__price,
    );
    $__data['meta'] = array(
        'order_data' => $__mate_order_data,
        'pay_modo'   => 'points',
    );

    //创建新订单
    $order = ZibPay::add_order($__data);
    if (is_wp_error($order)) {
        zib_send_json_error(['code' => $order->get_error_code(), 'msg' => $order->get_error_message()]);
    }

    if (!$order) {
        zib_send_json_error('订单创建失败');
    }

    $initiate_pay_data = array(
        'user_id'        => $user_id,
        'payment_method' => 'points',
        'order_num'      => $order['order_num'],
        'order_price'    => $__price,
        'desc'           => zibpay_get_pay_type_name($order_type),
    );

    //发起支付
    $initiate_pay = zibpay_points_initiate_pay($initiate_pay_data);
    if ($initiate_pay['error']) {
        zib_send_json_error($initiate_pay['msg']);
    }

    zib_send_json_success(['reload' => true, 'msg' => '购买成功']);
}
add_action('wp_ajax_points_initiate_pay', 'zibpay_points_initiate_order');
add_action('wp_ajax_nopriv_points_initiate_pay', 'zibpay_points_initiate_order');

/**
 * 积分支付
 * @param array $order_data 必须的参数：
 *  - user_id: 用户ID
 *  - order_num: 订单号
 *  - order_price: 价格
 *  - desc: 描述
 * @return array
 */
function zibpay_points_initiate_pay(array $order_data)
{

    $user_id = $order_data['user_id'];
    //我的积分
    $user_points = zibpay_get_user_points($user_id);
    if ($order_data['order_price'] > $user_points) {
        return ['error' => 1, 'msg' => '积分不足，暂时无法支付'];
    }

    $order_data['order_price'] = (int) $order_data['order_price'];

    if ($order_data['order_price'] > 0) {
        //更新用户积分
        $update_points_data = array(
            'order_num' => $order_data['order_num'], //订单号
            'value'     => -$order_data['order_price'], //值 整数为加，负数为减去
            'type'      => '积分支付', //类型说明
            'desc'      => $order_data['desc'] ?? '购买商品', //说明
        );

        $update_points = zibpay_update_user_points($user_id, $update_points_data);

        if (!$update_points) {
            return ['error' => 1, 'msg' => '支付失败，请检查积分是否足够，或刷新后重试'];
        }
    }

    //支付订单
    $pay = array(
        'order_num'  => $order_data['order_num'],
        'pay_type'   => 'points',
        'pay_num'    => $order_data['order_num'],
        'pay_price'  => $order_data['order_price'],
        'pay_detail' => array(
            'points' => $order_data['order_price'],
        ),
    );

    // 更新订单状态
    ZibPay::payment_order($pay);

    return ['error' => 0, 'msg' => '支付成功'];
}

/**
 * Register bundled GPL payment gateway handlers.
 *
 * These filters bind the packaged gateway implementations to the payment
 * dispatcher so administrator-configured gateway credentials can create
 * payment requests normally.
 */
function zibpay_register_open_source_gateway_handlers()
{
    $handlers = array(
        'official_wechat'   => 'zibpay_initiate_official_wechat',
        'official_alipay'   => 'zibpay_initiate_official_alipay',
        'xhpay'             => 'zibpay_initiate_xhpay',
        'payjs'             => 'zibpay_initiate_payjs',
        'epay'              => 'zibpay_initiate_epay',
        'vmqphp'            => 'zibpay_initiate_vmqphp',
        'paypal'            => 'zibpay_zibpay_initiate_paypal',
        'balance'           => 'zibpay_initiate_balance',
        'card_pass'         => 'zibpay_initiate_card_pass',
        'xunhupay_wechat'   => 'zibpay_initiate_xunhupay',
        'xunhupay_alipay'   => 'zibpay_initiate_xunhupay',
        'codepay_wechat'    => 'zibpay_initiate_codepay',
        'codepay_alipay'    => 'zibpay_initiate_codepay',
    );

    foreach ($handlers as $gateway => $callback) {
        if (function_exists($callback)) {
            add_filter('zibpay_initiate_' . $gateway, $callback, 10, 1);
        }
    }
}
zibpay_register_open_source_gateway_handlers();
