<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:28:58
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

$wxOAuth = new \Yurun\OAuthLogin\Weixin\OAuth2;
$wxOAuth->displayLoginAgent();
