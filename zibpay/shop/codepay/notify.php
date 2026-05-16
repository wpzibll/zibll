<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-11-17 11:31:59
 */

/**
 * 码支付异步通知
 */

header('Content-type:text/html; Charset=utf-8');

ob_start();
require_once dirname(__FILE__) . '/../../../../../../wp-load.php';
ob_end_clean();

if (empty($_POST['sign'])) {
    echo '非法请求';
    exit();
}

$config = zibpay_get_payconfig('codepay');
if (!$config['id'] || !$config['key'] || !$config['token']) {
    //判断参数是否为空
    exit('fail');
}

if (_pz('pay_wechat_sdk_options') != 'codepay_wechat' && _pz('pay_alipay_sdk_options') != 'codepay_alipay') {
    //判断是否开启此支付接口
    //  exit('fail');
}

ksort($_POST); //排序post参数
reset($_POST); //内部指针指向数组中的第一个元素
$sign = ''; //初始化
foreach ($_POST as $key => $val) { //遍历POST参数
    if ($val == '' || $key == 'sign') {
        continue;
    }
    //跳过这些不签名
    if ($sign) {
        $sign .= '&';
    }
    //第一个字符串签名不加& 其他加&连接起来参数
    $sign .= "$key=$val"; //拼接为url参数形式
}
if (!$_POST['pay_no'] || md5($sign . $config['key']) != $_POST['sign']) { //不合法的数据
    exit('fail'); //返回失败 继续补单
} else {
    //成功
    $pay = array(
        'order_num' => $_POST['pay_id'],
        'pay_type'  => 'codepay',
        'pay_price' => $_POST['money'],
        'pay_num'   => $_POST['pay_no'],
    );
    // 更新订单状态
    $order = ZibPay::payment_order($pay);

    echo 'success';
    exit();
}
exit();
