<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-06-18 12:16:27
 * @LastEditTime : 2026-01-14 12:52:55
 */

//启用 session
@session_start();

//引入核心文件
require_once get_theme_file_path('/inc/functions/compat/business-compat.php');

//微信配置接口验证
if (!empty($_REQUEST['echostr']) && !empty($_REQUEST['signature'])) {

    if (_pz('oauth_weixingzh_option', 0, 'token_nocheck')) {
        echo $_REQUEST['echostr'];
        exit();
    }

    //微信接口校验
    $signature = $_GET['signature'];
    $timestamp = $_GET['timestamp'];
    $nonce     = $_GET['nonce'];

    $token  = _pz('oauth_weixingzh_option', '', 'token');
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode($tmpArr);
    $tmpStr = sha1($tmpStr);

    if ($tmpStr == $signature) {
        echo $_REQUEST['echostr'];
        exit();
    }
}

//获取后台配置
$current_user_id = get_current_user_id();
$wxConfig        = get_oauth_config('weixingzh');

if (empty($wxConfig['appid']) || empty($wxConfig['appkey'])) {
    wp_die('微信公众号配置错误，请先在后台配置参数');
}

//微信APP内跳转登录
if (!zib_weixingzh_is_qrcode()) {
    // 在微信APP内使用无感登录接口
    if (empty($_SESSION['YURUN_WEIXIN_STATE'])) {
        wp_safe_redirect(home_url());
        exit;
    }

    try {
        $wxOAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
        // 获取accessToken，把之前存储的state传入，会自动判断。获取失败会抛出异常！
        $accessToken = $wxOAuth->getAccessToken($_SESSION['YURUN_WEIXIN_STATE']);
        $userInfo    = $wxOAuth->getUserInfo(); //第三方用户信息
        $openid      = $wxOAuth->openid; // 唯一ID
    } catch (Exception $err) {
        zib_oauth_die($err->getMessage());
    }

    // 处理本地业务逻辑
    if ($openid && $userInfo) {
        $userInfo['name'] = !empty($userInfo['nickname']) ? $userInfo['nickname'] : '';

        $oauth_data = array(
            'type'        => 'weixingzh',
            'openid'      => $openid,
            'name'        => $userInfo['name'],
            'avatar'      => !empty($userInfo['headimgurl']) ? $userInfo['headimgurl'] : '',
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
    exit();
}

//扫码登录流程
require_once get_theme_file_path('/oauth/sdk/weixingzh.php');

try {
    $wxOAuth = new \Weixin\GZH\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
} catch (Exception $err) {
    echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $err->getMessage()), JSON_UNESCAPED_UNICODE));
    exit();
}

$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'callback';

switch ($action) {
    case 'code_check': //验证验证码是否正确

        //代理登录验证
        zib_agent_login();

        if (!empty($wxConfig['gzh_type']) && $wxConfig['gzh_type'] === 'not') {

            $code = !empty($_REQUEST['code']) ? esc_sql(strip_tags(trim($_REQUEST['code']))) : '';
            if (!$code) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入验证码')));
                exit();
            }

            //错误频率限制
            $is_captcha = zib_is_error_frequency_limit('weixingzh_code_check');
            if ($is_captcha['error']) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $is_captcha['msg'])));
                exit();
            }

            $user_key = $wxOAuth->getUserKey($code);

            if (!$user_key) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '验证码错误或已过期，请重新获取')));
                exit();
            }

            if ($user_key === -1) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '验证码已过期，请重新获取')));
                exit();
            }

            //重置错误频率限制
            zib_reset_error_frequency_limit('weixingzh_code_check');

            $goto_uery_arg = array(
                'action' => 'code_login',
                'openid' => $user_key,
                'nonce'  => wp_create_nonce('weixingzh_code_login_nonce'),
            );
            if (!empty($_REQUEST['oauth_rurl'])) {
                $goto_uery_arg['oauth_rurl'] = $_REQUEST['oauth_rurl'];
            }

            echo(json_encode(array('reload' => 1, 'msg' => '验证成功', 'openid' => $user_key, 'goto' => add_query_arg($goto_uery_arg, $wxConfig['backurl']))));
            exit();

        }
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '功能未启用')));
        exit();
        break;

    case 'code_login':
        //前台登录或者绑定

        //代理登录验证
        zib_agent_login();

        $openId = !empty($_REQUEST['openid']) ? $_REQUEST['openid'] : '';
        if (!$openId) {
            zib_oauth_die('参数传入错误');
        }

        // 处理本地业务逻辑
        $oauth_data = array(
            'type'        => 'weixingzh',
            'openid'      => $openId,
            'name'        => '',
            'avatar'      => '',
            'description' => '',
            'getUserInfo' => array(
                'openid'      => $openId,
                'name'        => '',
                'avatar'      => '',
                'description' => '',
            ),
        );

        //代理登录回调
        zib_agent_callback($oauth_data);

        //验证nonce，需放置到代理登录回调下面
        if (empty($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'weixingzh_code_login_nonce')) {
            zib_oauth_die('页面已过期，请重新登录');
        }
        $oauth_result = zib_oauth_update_user($oauth_data);

        if ($oauth_result['error']) {
            zib_oauth_die($oauth_result['msg']);
        } else {
            $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : (!empty($_REQUEST['oauth_rurl']) ? $_REQUEST['oauth_rurl'] : $oauth_result['redirect_url']);
            wp_safe_redirect($rurl);
            exit;
        }

        break;

    case 'callback':
        //接受微信发过来的信息
        $callback = $wxOAuth->callback();

        //未认证模式：发送验证码
        if (!empty($wxOAuth->callback) && !empty($wxConfig['gzh_type']) && $wxConfig['gzh_type'] === 'not') {
            $wxOAuth->code_keyword        = !empty($wxConfig['code_keyword']) ? $wxConfig['code_keyword'] : '验证码';
            $wxOAuth->code_reply_template = !empty($wxConfig['code_reply']) ? $wxConfig['code_reply'] : "您的验证码为：%code%\n有效期%time%秒，如过期或验证失败可以重新发送“%keyword%”获取验证码";

            if (isset($wxOAuth->callback['Event']) && $wxOAuth->callback['Event'] == 'subscribe') {
                //如果是首次关注扫码
                $wxOAuth->code_reply_template = !empty($wxConfig['code_subscribe_reply']) ? $wxConfig['code_subscribe_reply'] : $wxOAuth->code_reply_template;
            }

            if ($wxOAuth->CodeReply()) {
                exit();
            }
        }

        //认证模式，获取用户openid
        if ($callback) {
            $EventKey        = str_replace('qrscene_', '', $callback['EventKey']);
            $new_time_YmdHis = (int) current_time('YmdHis');
            //储存临时数据
            $weixingzh_event_data = zib_get_option('weixingzh_event_data'); //读取临时数据
            //清理过期数据，避免数据过大
            if ($weixingzh_event_data && is_array($weixingzh_event_data)) {
                foreach ($weixingzh_event_data as $k => $v) {
                    if ($new_time_YmdHis > ($v['update_time'] + 3600) || $v['FromUserName'] === $callback['FromUserName']) {
                        unset($weixingzh_event_data[$k]);
                    }
                }
            } else {
                $weixingzh_event_data = array();
            }

            $callback['update_time']         = $new_time_YmdHis;
            $weixingzh_event_data[$EventKey] = $callback;
            zib_update_option('weixingzh_event_data', $weixingzh_event_data, false);

            //给用户回复消息
            if (!empty($wxConfig['subscribe_msg']) && $callback['Event'] == 'subscribe') {
                $wxOAuth->sendMessage($wxConfig['subscribe_msg']);
                exit();
            } elseif (!empty($wxConfig['scan_msg']) && $callback['Event'] == 'SCAN') {
                $wxOAuth->sendMessage($wxConfig['scan_msg']);
                exit();
            }
        }

        //自动回复
        if (!empty($wxOAuth->callback)) {
            if (!empty($wxConfig['new_subscribe_msg']) && isset($wxOAuth->callback['Event']) && $wxOAuth->callback['Event'] === 'subscribe') {
                $wxOAuth->sendMessage($wxConfig['new_subscribe_msg']);
                exit();
            } else {
                $wxOAuth->autoReply($wxConfig['auto_reply']);
                exit();
            }
        }

        exit();
        break;

    case 'check_callback':
        //代理登录
        zib_agent_login();
        //前端验证是否回调
        $state = !empty($_REQUEST['state']) ? $_REQUEST['state'] : '';
        if (!$state) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
            exit();
        }

        $weixingzh_event_data = zib_get_option('weixingzh_event_data'); //读取临时数据
        if (!isset($weixingzh_event_data[$state])) {
            echo(json_encode(array('error' => 1)));
            exit();
        }

        //删除已使用过的数据
        $event_data = $weixingzh_event_data[$state];
        unset($weixingzh_event_data[$state]);
        zib_update_option('weixingzh_event_data', $weixingzh_event_data, false);

        $goto_uery_arg = array(
            'action' => 'login',
            'openid' => $event_data['FromUserName'],
        );

        if (!empty($_REQUEST['oauth_rurl'])) {
            $goto_uery_arg['oauth_rurl'] = $_REQUEST['oauth_rurl'];
        }

        echo(json_encode(array('goto' => add_query_arg($goto_uery_arg, $wxConfig['backurl']), 'option' => $event_data)));
        exit();

        break;

    case 'login':
        //前台登录或者绑定

        //代理登录验证
        zib_agent_login();

        $openId = !empty($_REQUEST['openid']) ? $_REQUEST['openid'] : '';
        if (!$openId) {
            wp_die('参数传入错误');
        }

        try {
            $userInfo = $wxOAuth->getUserInfo($openId); //第三方用户信息
        } catch (Exception $err) {
            zib_oauth_die($err->getMessage());
        }

        // 处理本地业务逻辑
        if (!empty($userInfo['openid'])) {
            $userInfo['name']   = !empty($userInfo['nickname']) ? $userInfo['nickname'] : '';
            $userInfo['avatar'] = !empty($userInfo['headimgurl']) ? $userInfo['headimgurl'] : '';

            $oauth_data = array(
                'type'        => 'weixingzh',
                'openid'      => $userInfo['openid'],
                'name'        => $userInfo['name'],
                'avatar'      => $userInfo['avatar'],
                'description' => '',
                'getUserInfo' => $userInfo,
            );

            //代理登录回调
            zib_agent_callback($oauth_data);

            $oauth_result = zib_oauth_update_user($oauth_data);

            if ($oauth_result['error']) {
                zib_oauth_die($oauth_result['msg']);
            } else {
                $rurl = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : (!empty($_REQUEST['oauth_rurl']) ? $_REQUEST['oauth_rurl'] : $oauth_result['redirect_url']);
                wp_safe_redirect($rurl);
                exit;
            }
        } else {
            zib_oauth_die();
            //   file_put_contents(__DIR__ . '/error.log', var_export($userInfo, TRUE));
        }

        break;
}

wp_safe_redirect(home_url());
exit;
