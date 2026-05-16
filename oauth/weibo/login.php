<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:28:42
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

//获取后台配置
$weiboConfig = get_oauth_config('weibo');
$weiboOAuth = new \Yurun\OAuthLogin\Weibo\OAuth2($weiboConfig['appid'], $weiboConfig['appkey'], $weiboConfig['backurl']);

if ($weiboConfig['agent']) {
    $weiboOAuth->loginAgentUrl = esc_url(home_url('/oauth/weiboagent'));
}
//代理登录
zib_agent_login();
$url = $weiboOAuth->getAuthUrl();
$_SESSION['YURUN_WEIBO_STATE'] = $weiboOAuth->state;
// 储存返回页面
$_SESSION['oauth_rurl']  = !empty($_REQUEST["rurl"]) ? $_REQUEST["rurl"] : '';

header('location:' . $url);
