<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2022-06-23 23:27:48
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

//获取后台配置

$OAuth = new \Yurun\OAuthLogin\QQ\OAuth2;
$OAuth->displayLoginAgent();
