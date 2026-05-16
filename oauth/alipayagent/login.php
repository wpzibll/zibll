<?php
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();

//获取后台配置

$OAuth = new \Yurun\OAuthLogin\QQ\OAuth2;
$OAuth->displayLoginAgent();
