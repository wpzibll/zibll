<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-07-04 00:38:13
 * @LastEditTime : 2025-05-29 18:22:33
 */

//启用 session
@session_start();

if (empty($_SESSION['OAUTH_AGENT_STATE'])) {
    wp_safe_redirect(home_url());
    exit;
}

//获取后台配置
$config                   = _pz('oauth_agent_client_option');
$config['agent_back_url'] = (home_url('/oauth/agent/callback'));
$current_user_id          = get_current_user_id();

require_once get_theme_file_path('/oauth/sdk/agent.php');
$OAuth = new \agent\OAuth2($config);

$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
switch ($action) {
    case 'code_check':
        $code = !empty($_REQUEST['code']) ? esc_sql(strip_tags(trim($_REQUEST['code']))) : '';
        if (!$code) {
            echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入验证码')));
            exit();
        }

        $OAuth->type        = 'weixingzh';
        $get_data['action'] = 'code_check';
        $get_data['code']   = $code;
        $check_url          = $OAuth->getCallbackUrl($get_data);

        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout(10000)->post($check_url);

        if (empty($response->success)) {
            echo json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '代理登录接口连接超时', 'url' => $check_url, 'error' => $response->getError()));
            exit();
        }

        $result = $response->json(true);
        if (!empty($result['goto'])) {
            $goto_data['openid'] = $result['openid'];
            $goto_data['action'] = 'code_login';

            if (!empty($_REQUEST['oauth_rurl'])) {
                $goto_data['oauth_rurl'] = $_REQUEST['oauth_rurl'];
            }

            $result['goto'] = $OAuth->getCallbackUrl($goto_data);
        }

        echo json_encode($result);
        exit();

}

if (!empty($_REQUEST['openid']) && !empty($_REQUEST['type']) && $OAuth->verifySign()) {

    //处理多余数据：移除签名
    if (isset($_REQUEST['sign'])) {
        unset($_REQUEST['sign']);
    }

    //处理多余数据：移除回跳链接，保存回跳链接到本地
    if (isset($_REQUEST['oauth_rurl'])) {
        $_SESSION['oauth_rurl'] = $_REQUEST['oauth_rurl'];
        unset($_REQUEST['oauth_rurl']);
    }

    $oauth_data = $_REQUEST;
    if (!isset($oauth_data['getUserInfo'])) {
        $oauth_data['getUserInfo'] = $_REQUEST;
    }

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
