# xunhupay

## 简介
微信支付宝官方个人H5支付接口 无需营业执照个人可申请H5支付，不挂机、微信支付宝官方结算，资金不中转安全有保障的支付接口。申请地址：https://pay.xunhuweb.com/

其它版本: [XunhuPay Laravel 开发包](https://gitee.com/wpweixin/xunhupay-laravel)

## 安装

通过 Composer 安装

```bash
composer require xunhu/xunhupay dev-master
```

## 使用方法

首先在业务中引入

```php
<?php
use Xunhu\Xunhupay\Xhpay;

// 配置通信参数
$config = [
    'mchid' => '',   // 配置商户号
    'key'   => ''   // 配置密钥
];

// 初始化
$xhpay = new Xhpay($config);
```

其次开始使用

- 扫码支付

```php
// 构造订单基础信息
$data = [
    'body' => '订单测试',                        // 订单标题
    'total_fee' => 2,                           // 订单金额(分)
    'out_trade_no' => time(),                   // 订单号
    'type' => 'wechat',                         // 支付类型(alipay：支付宝，wechat：微信)不填默认为wechat
    'notify_url' => 'http://www.xunhuweb.com'    // 异步通知地址
];

$result = $xhpay->native($data);
print_r($result);
```
- 收银台模式支付（直接在微信浏览器打开）

```php
// 构造订单基础信息
$data = [
    'body' => '订单测试',                         // 订单标题
    'total_fee' => 2,                            // 订单金额(分)
    'out_trade_no' => time(),                    // 订单号
    'type' => 'wechat',             			// 支付类型(alipay：支付宝，wechat：微信)不填默认为wechat
    'notify_url' => 'https://pay.xunhuweb.com/',     // 异步通知地址
    'redirect_url' => 'https://pay.xunhuweb.com/'  // 支付后前端跳转地址
];
$url = $xhpay->cashier($data);
header("Location:". htmlspecialchars_decode($url,ENT_NOQUOTES));
exit;
```

- JSAPI模式支付

```php
// 构造订单基础信息
$data = [
    'body' => '订单测试',                         // 订单标题
    'total_fee' => 2,                            // 订单金额(分)
    'out_trade_no' => time(),                    // 订单号
    'notify_url' => 'https://pay.xunhuweb.com/',     // 异步通知地址
    'openid' => '',                 // 用户微信openid
];

$result = $xhpay->jsapi($data);
print_r($result);
```
- 微信H5支付

```php
// 构造订单基础信息
$data = [
    'body' => '订单测试',                         // 订单标题
    'total_fee' => 2,                            // 订单金额(分)
    'out_trade_no' => time(),                    // 订单号
    'notify_url' => 'https://pay.xunhuweb.com/', // 异步通知地址
    'wap_url' => "https://pay.xunhuweb.com/",	//WAP跳转域名（需与发起支付的域名保持一致，请联系管理员配置H5域名）
    'wap_name' => 'XunhuPay'					//网站名称（建议与网站名称一致）
];

$result = $xhpay->h5($data);
$url =$result['mweb_url'];
header("location: {$url}");
exit;
```
- 查询订单

```php
// 根据订单号查询订单状态
$data = [
    'out_trade_no' => '',     // 商户订单号(两个必填其一)
    'order_id' => '78e6249dbdf24397933af7a74c99e31d',     // 平台返回订单号(两个必填其一)
];
$result = $xhpay->query($data);
print_r($result);
```
- 退款

```php
// 根据订单号退款
$data = [
	'out_trade_no' => '',     // 商户订单号(两个必填其一)
    'order_id' => "78e6249dbdf24397933af7a74c99e31d",// 平台返回订单号(两个必填其一)
    'refund_desc' => "退款"			//退款理由
];
$rst = $xhpay->refund($data);
print_r($result);
```
- 接收异步通知

```php
// 接收异步通知,无需关注验签动作,已自动处理
$data = $xhpay->getNotify();
if ($data['return_code'] == 'SUCCESS') {
    ob_clean();
    print 'success';   //当支付平台接收到此消息后，将不再重复回调当前接口
}else{
    echo $data['msg'];
}
// 接收信息后自行处理
```
## 安全相关
如果您在使用过程中发现各种 bug，请积极反馈，我会尽早修复