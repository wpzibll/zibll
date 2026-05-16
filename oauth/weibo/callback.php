<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2023-05-11 19:57:11
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

if (empty($_SESSION['YURUN_WEIBO_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//获取后台配置
$weiboConfig = get_oauth_config('weibo');

$weiboOAuth = new \Yurun\OAuthLogin\Weibo\OAuth2($weiboConfig['appid'], $weiboConfig['appkey'], $weiboConfig['backurl']);

if ($weiboConfig['agent']) {
    $weiboOAuth->loginAgentUrl = esc_url(home_url('/oauth/weiboagent'));
}

try {
// 获取accessToken，把之前存储的state传入，会自动判断。获取失败会抛出异常！
    $accessToken = $weiboOAuth->getAccessToken($_SESSION['YURUN_WEIBO_STATE']);
    $userInfo    = $weiboOAuth->getUserInfo(); //第三方用户信息
    $openid      = $weiboOAuth->openid; // 唯一ID
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

// 处理本地业务逻辑
if ($openid && $userInfo) {
    $userInfo['name'] = !empty($userInfo['screen_name']) ? $userInfo['screen_name'] : '';

    $oauth_data = array(
        'type'        => 'weibo',
        'openid'      => $openid,
        'name'        => $userInfo['name'],
        'avatar'      => '', //微博头像有防盗链
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
