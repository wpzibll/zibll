<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-07-04 03:30:22
 * @LastEditTime: 2022-11-01 12:50:10
 */

//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

if (empty($_SESSION['YURUN_QQ_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//引入核心文件
require_once get_theme_file_path('/inc/functions/compat/business-compat.php');

//获取后台配置
$qqConfig = get_oauth_config('qq');

$qqOAuth = new \Yurun\OAuthLogin\QQ\OAuth2($qqConfig['appid'], $qqConfig['appkey'], $qqConfig['backurl']);

if ($qqConfig['agent']) {
    $qqOAuth->loginAgentUrl = esc_url(home_url('/oauth/qqagent'));
}

try {
// 获取accessToken，把之前存储的state传入，会自动判断。获取失败会抛出异常！
    $accessToken = $qqOAuth->getAccessToken($_SESSION['YURUN_QQ_STATE']);
    $userInfo    = $qqOAuth->getUserInfo(); //第三方用户信息
    $openid      = $qqOAuth->openid; // 唯一ID
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

// 处理本地业务逻辑
if ($openid && $userInfo) {
    $userInfo['name'] = !empty($userInfo['nickname']) ? $userInfo['nickname'] : '';

    $oauth_data = array(
        'type'        => 'qq',
        'openid'      => $openid,
        'name'        => $userInfo['name'],
        'avatar'      => !empty($userInfo['figureurl_qq_2']) ? $userInfo['figureurl_qq_2'] : '',
        'description' => '',
        'getUserInfo' => $userInfo,
    );

    //代理登录
    zib_agent_callback($oauth_data);

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
}

wp_safe_redirect(home_url());
exit;
