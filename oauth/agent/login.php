<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-07-04 00:38:13
 * @LastEditTime: 2022-10-26 02:46:05
 */

//启用 session
@session_start();

//获取后台配置
$config  = _pz('oauth_agent_client_option');
$oauth_s = (array) $config['oauth_s'];
$type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';

if (!$type || !$config['url']) {
    echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '代理社交登录配置参数错误')));
    exit();
}

if (!in_array($type, $oauth_s)) {
    echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '此登录方式未启用')));
    exit();
}

require_once get_theme_file_path('/oauth/sdk/agent.php');
$config['agent_back_url'] = (home_url('/oauth/agent/callback'));
$OAuth                    = new \agent\OAuth2($config, $type);

$url = $OAuth->getAuthUrl();
// 存储sdk自动生成的state，回调处理时候要验证
$_SESSION['OAUTH_AGENT_STATE'] = $OAuth->state;
// 储存返回页面
$_SESSION['oauth_rurl'] = !empty($_REQUEST["rurl"]) ? $_REQUEST["rurl"] : '';

if ($type == 'weixingzh') {
    $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';

    if ($action === 'check_callback') {
        //验证回调
        $get_data['state']  = !empty($_REQUEST['state']) ? $_REQUEST['state'] : '';
        $get_data['action'] = 'check_callback';

        $check_url = $OAuth->getCallbackUrl($get_data);

        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout(10000)->post($check_url);

        if (empty($response->success)) {
            echo json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '代理登录接口连接超时', 'url' => $check_url));
            exit();
        }

        $result = $response->json(true);
        if (!empty($result['goto']) && !empty($result['option']['FromUserName'])) {
            $goto_data['openid'] = $result['option']['FromUserName'];
            $goto_data['action'] = 'login';
            $goto_data['state']  = $OAuth->state;
            if (!empty($_REQUEST['oauth_rurl'])) {
                $goto_data['oauth_rurl'] = $_REQUEST['oauth_rurl'];
            }

            $result['goto'] = $OAuth->getCallbackUrl($goto_data);
        }

        echo json_encode($result);
        exit();
    }

    if (zib_weixingzh_is_qrcode()) {

        //获取二维码
        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout(10000)->post($url);
        //返回请求
        if (empty($response->success)) {
            echo json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '代理登录接口链接超时', 'url' => $url));
            exit();
        }
        $result = $response->json(true);

        //认证公众号所需参数
        if (!empty($result['url']) && !empty($result['state'])) {
            $result['url'] = add_query_arg(array('type' => $type, 'action' => 'check_callback'), home_url('/oauth/agent'));
        }

        echo json_encode($result);
        exit();
    }

}

// 跳转到登录页
header('location:' . $url);
