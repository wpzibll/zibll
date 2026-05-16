<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:28:03
 */
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-12-12 15:58:40
 * @LastEditTime: 2020-12-13 11:21:31
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

if (empty($_SESSION['YURUN_GITHUB_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//获取后台配置
$githubConfig = get_oauth_config('github');

$githubOAuth = new \Yurun\OAuthLogin\Github\OAuth2($githubConfig['appid'], $githubConfig['appkey'], $githubConfig['backurl']);

if ($githubConfig['agent']) {
    $githubOAuth->loginAgentUrl = esc_url(home_url('/oauth/githubagent'));
}

try {
// 获取accessToken，把之前存储的state传入，会自动判断。获取失败会抛出异常！
    $accessToken = $githubOAuth->getAccessToken($_SESSION['YURUN_GITHUB_STATE']);
    $userInfo    = $githubOAuth->getUserInfo(); //第三方用户信息
    $openid      = $githubOAuth->openid; // 唯一ID
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

// 处理本地业务逻辑
if ($openid && $userInfo) {
    $oauth_data = array(
        'type'        => 'github',
        'openid'      => $openid,
        'name'        => !empty($userInfo['name']) ? $userInfo['name'] : '',
        'avatar'      => !empty($userInfo['avatar_url']) ? $userInfo['avatar_url'] : '',
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
