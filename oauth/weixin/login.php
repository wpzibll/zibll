<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:28:53
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

//获取后台配置
$wxConfig = get_oauth_config('weixin');
$wxOAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($wxConfig['appid'], $wxConfig['appkey']);

if ($wxConfig['agent']) {
	$wxOAuth->loginAgentUrl = esc_url(home_url('/oauth/weixinagent'));
}
//代理登录
zib_agent_login();

$url = $wxOAuth->getAuthUrl($wxConfig['backurl']);

$_SESSION['YURUN_WEIXIN_STATE'] = $wxOAuth->state;
// 储存返回页面
$_SESSION['oauth_rurl']  = !empty($_REQUEST["rurl"]) ? $_REQUEST["rurl"] : '';

header('location:' . $url);
