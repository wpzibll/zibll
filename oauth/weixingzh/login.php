<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2024-12-22 12:00:48
 */
//启用 session
@session_start();
// 要求noindex
//wp_no_robots();
require_once get_theme_file_path('/oauth/sdk/weixingzh.php');

//获取后台配置
$wxConfig = get_oauth_config('weixingzh');
if (!$wxConfig['appid'] || !$wxConfig['appkey']) {
    echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '微信公众号参数错误')));
    exit();
}

//代理登录
zib_agent_login();
$_SESSION['oauth_rurl'] = !empty($_REQUEST["rurl"]) ? $_REQUEST["rurl"] : ''; // 储存返回页面

try {
    //未认证模式：发送验证码
    if (!empty($wxConfig['gzh_type']) && $wxConfig['gzh_type'] === 'not') {
        $code_keyword = !empty($wxConfig['code_keyword']) ? $wxConfig['code_keyword'] : '登录';
        $backurl      = $wxConfig['backurl'];

        if (!empty($_SESSION['agent_back_url']) && !empty($_REQUEST['sign'])) {
            //代理登录
            $backurl = $_SESSION['agent_back_url'];
        }

        if (empty($wxConfig['code_qrcode'])) {
            echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '未设置公众号二维码，请联系站长')));
            exit();
        }

        $type_text = (!empty($_REQUEST["bind"]) ? '绑定' : '登录');

        $text = '微信扫码' . (!empty($_REQUEST["bind"]) ? '绑定' : '登录');

        $html = '<img class="signin-qrcode-img" src="' . zib_get_img_auto_base64($wxConfig['code_qrcode']) . '" alt="' . $text . '" style="width: 150px;min-height: 150px;">';
        $html .= '<form ajax-submit get-only-one="true"><div class="mt20 muted-color"><i class="c-green fa fa-weixin mr6"></i>关注公众号后发送<b class="badg badg-sm c-red">“' . $code_keyword . '”</b>获取验证码</div>
        <div class="relative line-form mb10">
            <input type="text" name="code" class="line-form-input text-center em12" tabindex="1" placeholder="" style="letter-spacing: .8em;padding: 0 2em .1em;"><i class="line-form-line"></i><div class="scale-placeholder">请输入验证码</div>
        </div>
        <div class="box-body nobottom">
            <input type="hidden" name="action" value="code_check">
            <button type="button" tabindex="2" class="but radius jb-blue padding-lg wp-ajax-submit btn-block" ajax-href="' . $backurl . '">' . $type_text . '</button>
        </div>
        </form>';

        echo (json_encode(array('html' => $html)));
        exit();
    }

    // 在微信APP内使用此接口
    if (zib_is_wechat_app(array('wxwork'))) {
        $wxOAuth                        = new \Yurun\OAuthLogin\Weixin\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
        $url                            = $wxOAuth->getWeixinAuthUrl($wxConfig['backurl']);
        $_SESSION['YURUN_WEIXIN_STATE'] = $wxOAuth->state; //储存验证信息

        header('location:' . $url);
        exit();
    }

    $WeChat                             = new \Weixin\GZH\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
    $qrcode_array                       = $WeChat->getQrcode(); //生成二维码
    $qrcode                             = zib_get_qrcode_base64($qrcode_array['url']);
    $_SESSION['YURUN_WEIXIN_GZH_STATE'] = $WeChat->state; //储存验证信息

    $text = '微信扫码' . (!empty($_REQUEST["bind"]) ? '绑定' : '登录');
    $html = '<img class="signin-qrcode-img" src="' . $qrcode . '" alt="' . $text . '">';
    $html .= '<div class="text-center mt20 em12"><i class="c-green fa fa-weixin mr6"></i>' . $text . '</div>';

    echo (json_encode(array('html' => $html, 'url' => $wxConfig['backurl'], 'state' => $WeChat->state)));
    exit();
} catch (\Exception $e) {
    echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $e->getMessage())));
    exit();
}
