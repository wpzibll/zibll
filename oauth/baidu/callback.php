<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-07-04 03:29:16
 * @LastEditTime: 2022-11-25 13:13:30
 */
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 22:51:21
 */
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-01-08 22:13:24
 * @LastEditTime: 2021-01-09 14:16:01
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

if (empty($_SESSION['YURUN_BAIDU_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//获取后台配置
$Config = get_oauth_config('baidu');
$OAuth  = new \Yurun\OAuthLogin\Baidu\OAuth2($Config['appid'], $Config['appkey'], $Config['backurl']);

if ($Config['agent']) {
    $OAuth->loginAgentUrl = esc_url(home_url('/oauth/baiduagent'));
}
try {
// 获取accessToken，把之前存储的state传入，会自动判断。获取失败会抛出异常！
    $accessToken = $OAuth->getAccessToken($_SESSION['YURUN_BAIDU_STATE']);
    $userInfo    = $OAuth->getUserInfo(); //第三方用户信息
    $openid      = $OAuth->openid; // 唯一ID
} catch (Exception $err) {
    zib_oauth_die($err->getMessage());
}

// 处理本地业务逻辑
if ($openid && $userInfo) {

    $userInfo['nick_name'] = !empty($userInfo['realname']) ? $userInfo['realname'] : (!empty($userInfo['username']) ? $userInfo['username'] : (!empty($userInfo['uname']) ? $userInfo['uname'] : ''));
    $userInfo['name']      = $userInfo['nick_name'];
    $userInfo['avatar']    = !empty($userInfo['portrait']) ? 'https://himg.bdimg.com/sys/portraitn/item/' . $userInfo['portrait'] : '';

    $oauth_data = array(
        'type'        => 'baidu',
        'openid'      => $openid,
        'name'        => $userInfo['nick_name'],
        'avatar'      => $userInfo['avatar'],
        'description' => !empty($userInfo['userdetail']) ? $userInfo['userdetail'] : '',
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
