<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-03 16:09:18
 * @LastEditTime : 2026-01-04 00:21:05
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|微信公众号模板消息
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */
/**
 * @description: 发送微信模板消息统一接口
 * @param {*} $user_id
 * @param {*} $type
 * @param {*} $data
 * @param {*} $url
 * @return {*}
 */
function zib_wechat_template_send($user_id, $type, $data, $url = '')
{
    //是否开启微信模板消息
    if (!_pz('wechat_template_msg_s')) {
        return ['error' => 1, 'msg' => '微信公众号模板消息功能未开启'];
    }

    //获取微信公众号配置
    $wxConfig = get_oauth_config('weixingzh');
    if (!$wxConfig['appid'] || !$wxConfig['appkey']) {
        return ['error' => 1, 'msg' => '微信公众号配置错误，请检查AppID或AppSecret'];
    }

    //获取模板ID
    $template_id = zib_get_wechat_template_id($type);
    if (!$template_id) {
        return ['error' => 1, 'msg' => '未设置该类型消息的模板ID'];
    }

    //获取用户微信open_id
    $open_id = zib_get_user_wechat_open_id($user_id);
    if (!$open_id) {
        return ['error' => 1, 'msg' => '用户未绑定微信'];
    }

    //data数据处理
    $data = zib_wechat_template_data_handle($type, $data);

    try {
        //发送消息
        require_once get_theme_file_path('/oauth/sdk/weixingzh.php');
        $WeChat = new \Weixin\GZH\OAuth2($wxConfig['appid'], $wxConfig['appkey']);
        $result = $WeChat->sendTemplateMsg($open_id, $template_id, $data, $url);
    } catch (Exception $e) {

        error_log('微信模板消息发送失败：' . $e->getMessage() . '，消息数据：' . json_encode(array(
            'touser'      => $open_id,
            'template_id' => $template_id,
            'url'         => $url,
            'data'        => $data,
        ), JSON_UNESCAPED_UNICODE));

        return ['error' => 1, 'msg' => $e->getMessage()];
    }

    return ['error' => 0, 'msg' => '发送成功', 'result' => $result, 'open_id' => $open_id, 'template_id' => $template_id, 'data' => $data];
}

/**
 * 处理微信模板消息的data数据
 * @param {*} $type
 * @param {*} $data
 * @return {*}
 */
function zib_wechat_template_data_handle($type, $data)
{

    $_pz      = _pz('wechat_template_ids', [], $type . '_keys');
    $new_data = [];
    $i        = 1;
    foreach ($data as $key => $value) {
        if (!empty($_pz[$key])) {
            if (strstr($_pz[$key], 'thing')) {
                if (mb_strlen($value, 'UTF-8') > 19) {
                    $value = mb_substr($value, 0, 16, 'UTF-8') . '...';
                }
            }
            $new_data[$_pz[$key]]['value'] = $value;
        } else {
            $new_data['keyword' . $i]['value'] = $value;
        }
        $i++;
    }
    return $new_data;
}

/**
 * @description: 给所有网站管理员发送模板消息
 * @param {*} $template_id
 * @param {*} $data
 * @param {*} $url
 * @param {*} $topcolor
 * @return {*}
 */
function zib_send_wechat_template_msg_to_admin($type, $data, $url = '')
{
    $ids = zib_get_admin_user_ids();
    if ($ids) {
        foreach ($ids as $user_id) {
            zib_wechat_template_send($user_id, $type, $data, $url);
        }
    }
}

/**
 * @description: 获取用户的微信open_id
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_wechat_open_id($user_id)
{
    return get_user_meta($user_id, 'oauth_weixingzh_openid', true);
}

/**
 * @description: 获取模板ID，可以用作判断函数
 * @param {*} $type
 * @return {*}
 */
function zib_get_wechat_template_id($type)
{

    $_pz = _pz('wechat_template_ids');

    if (empty($_pz[$type . '_s'])) {
        return false;
    }

    return isset($_pz[$type]) ? trim($_pz[$type]) : false;
}
