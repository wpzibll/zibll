<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:28:47
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

//获取后台配置

$weiboOAuth = new \Yurun\OAuthLogin\Weibo\OAuth2;
$weiboOAuth->displayLoginAgent();