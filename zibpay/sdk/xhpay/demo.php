<?php
require_once __DIR__ . '/vendor/autoload.php';
use Xunhu\Xunhupay\Xhpay;

$config = [
		    'mchid' => 'a0e5b19a8b4047c88184412997a421d1',   // 配置商户号
		    'key'   => 'a0c76773b8ca44ac9fa5100f5675c95f',   // 配置通信密钥
		];

// 初始化
$xhpay = new Xhpay($config);
$data = [
    'body' => '订单测试',                        // 订单标题
    'total_fee' => 1,                           // 订单金额
    'out_trade_no' => time(),                   // 订单号
    'type' => 'alipay',
    'notify_url' => '',    // 异步通知地址
];

$result = $xhpay->native($data);
print_r($result);