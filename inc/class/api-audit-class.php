<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:35:21
 * @LastEditTime : 2025-12-26 21:01:17
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|短信验证码类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class ZibAudit
{

    /**
     * @$conclusionType 1.合规，2.不合规，3.疑似，4.审核失败
     * @description: AJAX判断并返回
     * @param {*} $content
     * @param {*} $msg_prefix $msg 消息前缀
     * @param {*} $be_like 疑似是否也判断为不合规
     * @return {*}
     */
    public static function ajax_image($file_id = 'file', $msg_prefix = '', $be_like = null)
    {

        if (!empty($_FILES[$file_id]["tmp_name"]) && 0 === strpos($_FILES[$file_id]['type'], 'image/')) {
            return self::ajax('image', $_FILES[$file_id]["tmp_name"], $msg_prefix, $be_like);
        }
        return;
    }

    /**
     * @$conclusionType 1.合规，2.不合规，3.疑似，4.审核失败
     * @description: AJAX判断并返回
     * @param {*} $content
     * @param {*} $msg_prefix $msg 消息前缀
     * @param {*} $be_like 疑似是否也判断为不合规
     * @return {*}
     */
    public static function ajax_text($content, $msg_prefix = '', $be_like = null)
    {
        return self::ajax('text', $content, $msg_prefix, $be_like);
    }

    public static function ajax($type, $content='', $msg_prefix = '', $be_like = null)
    {
        //管理员内容不审核
        if (is_super_admin()) {
            return;
        }

        switch ($type) {
            case 'text':
                if (null === $be_like) {
                    $be_like == _pz('audit_be_like_text', false);
                }
                $res = self::text($content);
                break;
            case 'image':
            case 'img':
                if (null === $be_like) {
                    $be_like == _pz('audit_be_like_img', false);
                }
                $res = self::image($content);
                break;
        }

        if (!empty($res['conclusion_type']) && ((2 == $res['conclusion_type'] || ($be_like && 3 == $res['conclusion_type'])))) {
            $msg = $msg_prefix . $res['msg'];
            echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $msg)));
            exit();
        }
        return $res;
    }

    public static function text($content, $sdk = '')
    {
        $content = str_replace(array("\r\n", "\r", "\n"), "", strip_tags($content));
        return self::request($content, 'text', $sdk);
    }

    public static function image($content, $sdk = '')
    {
        return self::request($content, 'image', $sdk);
    }

    //图片转base64
    public static function image_base64($image)
    {
        $base64 = '';
        if ($fp = @fopen($image, "rb", 0)) {
            $gambar = fread($fp, filesize($image));
            fclose($fp);
            $base64 = base64_encode($gambar);
        }

        return $base64;
    }

    public static function request($content, $type = 'text', $sdk = null)
    {
        if (!$sdk) {
            switch ($type) {
                case 'text':
                    $sdk = _pz('api_audit_text_sdk');
                    break;
                case 'image':
                case 'img':
                    $type = 'image';
                    $sdk  = _pz('api_audit_img_sdk');
                    break;
            }
        }
        if (!$sdk || 'null' == $sdk) {
            return array('error' => 'nosdk', 'msg' => '未设置内容审核接口');
        }

        switch ($sdk) {
            case 'baidu':
                $result = self::baidu($content, $type);
                break;
        }

        if (!$result) {
            return array('error' => 'nosdk', 'msg' => '未设置内容审核接口或者其它未知错误');
        }

        return $result;
    }

    //百度获取access_token
    /**
     * @description: 输出
     * @param {*} $conclusionType 1.合规，2.不合规，3.疑似，4.审核失败
     * @param {*} $msg
     * @param {*} $data
     * @return {*}
     */
    public static function _return($conclusionType, $msg = "", $data = array())
    {
        if (!$conclusionType || 4 == $conclusionType) {
            return array('error' => '1', 'msg' => '暂无审核结果');
        }

        $conclusion_array = array(
            1 => '合规',
            2 => '不合规',
            3 => '疑似不合规',
        );

        $conclusion = isset($conclusion_array[$conclusionType]) ? $conclusion_array[$conclusionType] : '审核失败';

        return array(
            'conclusion'      => $conclusion,
            'conclusion_type' => $conclusionType,
            'msg'             => $msg,
            'data'            => $data,
        );
    }

    //将已经返回的内容，进行合规性判断
    public static function is_audit($return, $type = "text")
    {
        if (isset($return['conclusion_type'])) {
            if (1 == $return['conclusion_type']) {
                return true;
            }
            $be_like = _pz('audit_be_like_' . $type, false);
            if (2 == $return['conclusion_type'] && $be_like) {
                return true;
            }

        }
        return false;
    }

    //百度接口
    public static function baidu($content, $type)
    {

        $token = self::baidu_get_token();
        if (!empty($token['error'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $token['error']);
        }

        switch ($type) {
            case 'text':
                $timeout    = 100000; //超时10秒
                $msg_prefix = '内容';
                $api_url    = 'https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined';
                $curl_data  = array(
                    'text' => $content,
                );
                break;

            case 'image':
                $msg_prefix = '图片';
                $timeout    = 150000; //超时15秒
                if (zib_is_url($content)) {
                    $curl_data = array(
                        'imgUrl' => $content,
                    );
                } else {
                    $curl_data = array(
                        'image' => self::image_base64($content),
                    );
                }

                $api_url = 'https://aip.baidubce.com/rest/2.0/solution/v1/img_censor/v2/user_defined';
                break;
        }

        $api_url = $api_url . '?access_token=' . $token;

        //开始请求
        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout($timeout)->post($api_url, $curl_data);
        if (empty($response->success)) {
            return array('error' => '1', 'msg' => '百度内容审核接口链接超时，请稍候再试');
        }

        $res = $response->json(true);
        if (!empty($res['error_msg'])) {
            return array('error' => '1', 'msg' => '百度内容审核失败：' . $res['error_msg']);
        }

        $msg = $msg_prefix . $res['conclusion'];

        if (isset($res['data'][0])) {
            foreach ($res['data'] as $data) {
                if (!empty($data['msg'])) {
                    $msg .= '，' . $data['msg'];
                    if (isset($data['hits'][0])) {
                        $msg_words = '';
                        foreach ($data['hits'] as $hits) {
                            if (!empty($hits['words'][0])) {
                                foreach ($hits['words'] as $words) {
                                    if ($words) {
                                        $msg_words .= '“' . $words . '”、';
                                    }
                                }
                            }
                        }
                    }
                    $msg .= $msg_words ? '(' . rtrim($msg_words, '、') . ')' : '';
                }
            }
        }

        //汇总返回内容
        return self::_return($res['conclusionType'], $msg, $res);
    }

    //百度获取access_token
    public static function baidu_get_token()
    {
        //第一步从本地获取access_token
        $option_key         = 'audit_baidu_access_token';
        $accessToken_option = get_option($option_key);
        $new_time           = strtotime('+3000 Second'); //获取现在时间加50分钟

        if (!empty($accessToken_option['access_token']) && $accessToken_option['expiration_time'] > $new_time) {
            return $accessToken_option['access_token'];
        }

        $cofig = _pz('audit_sdk_baidu');
        if (empty($cofig['appkey']) || empty($cofig['secretkey'])) {
            return array('error' => '百度内容识别：缺少配置参数');
        }
        $api_url   = 'https://aip.baidubce.com/oauth/2.0/token';
        $curl_data = array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $cofig['appkey'],
            'client_secret' => $cofig['secretkey'],
        );
        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout(6000)->post($api_url, $curl_data);
        if (empty($response->success)) {
            return array('error' => '百度内容审核获取access_token链接超时，请稍候再试');
        }
        $res = $response->json(true);

        if (!empty($res['access_token'])) {
            //储存access_token到本地
            $res['expiration_time'] = strtotime('+' . $res['expires_in'] . ' Second');
            update_option($option_key, $res);
            return $res['access_token'];
        }

        return array('error' => '百度内容审核access_token获取失败：' . json_encode($res));
    }
}
