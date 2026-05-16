<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2022-10-24 12:26:25
 * @LastEditTime: 2022-10-24 14:18:29
 */

//启用 session
@session_start();

//获取后台配置
$option  = _pz('clogin_option');
$oauth_s = !empty($option['oauth_s']) ? (array) $option['oauth_s'] : array();
$type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';

if (!$type || !$option['url'] || !$option['id'] || !$option['key'] || !_pz('clogin_s')) {
    zib_oauth_die('暂未启用此功能');
}

if (!in_array($type, $oauth_s)) {
    zib_oauth_die('此登录方式未启用');
}

require_once get_theme_file_path('/oauth/sdk/clogin.php');

$config = array(
    'apiurl'   => $option['url'],
    'appid'    => $option['id'],
    'appkey'   => $option['key'],
    'callback' => home_url('/oauth/clogin/callback'),
);
$OAuth     = new \CaiHong\Oauth($config);
$get_login = $OAuth->login($type);

if (!empty($get_login['code']) && $get_login['code'] != 0 && !empty($get_login['msg'])) {
    zib_oauth_die('处理失败：' . $get_login['msg']);
}

if (!empty($get_login['url'])) {
    // 存储sdk自动生成的state，回调处理时候要验证
    $_SESSION['CLOGIN_STATE'] = $OAuth->state;
// 储存返回页面
    $_SESSION['oauth_rurl'] = !empty($_GET["rurl"]) ? $_GET["rurl"] : '';

    header('location:' . $get_login['url']);
} else {
    zib_oauth_die();
}
