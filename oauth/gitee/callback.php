<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:27:55
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

if (empty($_SESSION['YURUN_GITEE_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//获取后台配置
$Config = get_oauth_config('gitee');
$OAuth  = new \Yurun\OAuthLogin\Gitee\OAuth2($Config['appid'], $Config['appkey'], $Config['backurl']);

if ($Config['agent']) {
    $OAuth->loginAgentUrl = esc_url(home_url('/oauth/giteeagent'));
}
try {
// 获取accessToken，把之前存储的state传入，会自动判断。获取失败会抛出异常！
    $accessToken = $OAuth->getAccessToken($_SESSION['YURUN_GITEE_STATE']);
    $userInfo    = $OAuth->getUserInfo(); //第三方用户信息
    $openid      = $OAuth->openid; // 唯一ID
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}
// 处理本地业务逻辑
if ($openid && $userInfo) {

    $userInfo['nick_name'] = !empty($userInfo['name']) ? $userInfo['name'] : (!empty($userInfo['login']) ? $userInfo['login'] : '');
    $userInfo['name']      = $userInfo['nick_name'];
    $userInfo['avatar']    = !empty($userInfo['avatar_url']) ? (strpos($userInfo['avatar_url'], 'no_portrait') == false ? $userInfo['avatar_url'] : '') : '';

    $oauth_data = array(
        'type'        => 'gitee',
        'openid'      => $openid,
        'name'        => $userInfo['nick_name'],
        'avatar'      => $userInfo['avatar'],
        'description' => !empty($userInfo['bio']) ? $userInfo['bio'] : '',
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
    exit;
}

wp_safe_redirect(home_url());
exit;
