<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2022-10-24 12:30:45
 * @LastEditTime: 2022-10-24 14:30:00
 */

//启用 session
@session_start();

if (empty($_SESSION['CLOGIN_STATE']) || empty($_GET['code']) || empty($_GET['state'])) {
    wp_safe_redirect(home_url());
    exit;
}

if ($_SESSION['CLOGIN_STATE'] !== $_GET['state']) {
    zib_oauth_die('环境异常，state验证失败，请刷新后重试');
}

//获取后台配置
$option  = _pz('clogin_option');
$oauth_s = !empty($option['oauth_s']) ? (array) $option['oauth_s'] : array();
$type    = !empty($_GET['type']) ? $_GET['type'] : false;
if (!$type || !$option['url'] || !$option['id'] || !$option['key'] || !_pz('clogin_s')) {
    zib_oauth_die('暂未启用此功能');
}

require_once get_theme_file_path('/oauth/sdk/clogin.php');

$config = array(
    'apiurl' => $option['url'],
    'appid'  => $option['id'],
    'appkey' => $option['key'],
);
$OAuth    = new \CaiHong\Oauth($config);
$userInfo = $OAuth->callback();
$openid   = !empty($userInfo['social_uid']) ? $userInfo['social_uid'] : false;

//出现错误
if (!empty($userInfo['code']) && $userInfo['code'] != 0 && $userInfo['msg']) {
    zib_oauth_die($userInfo['msg']);
}

// 处理本地业务逻辑
if ($openid && !empty($userInfo['type'])) {

    $userInfo['nick_name'] = !empty($userInfo['nickname']) ? $userInfo['nickname'] : '';
    $userInfo['name']      = $userInfo['nick_name'];
    $userInfo['avatar']    = !empty($userInfo['faceimg']) ? (strpos($userInfo['faceimg'], 'no_portrait') == false ? $userInfo['faceimg'] : '') : '';

    $oauth_data = array(
        'type'        => $OAuth->z_type($userInfo['type']),
        'openid'      => $openid,
        'name'        => $userInfo['nick_name'],
        'avatar'      => $userInfo['avatar'],
        'description' => '',
        'getUserInfo' => $userInfo,
    );

    $oauth_result = zib_oauth_update_user($oauth_data);

    if ($oauth_result['error']) {
        zib_oauth_die($oauth_result['msg']);
    } else {
        $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : $oauth_result['redirect_url'];
        wp_safe_redirect($rurl);
        exit;
    }
} else {
    zib_oauth_die();
    exit;
}

wp_safe_redirect(home_url());
exit;
