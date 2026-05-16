<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:35:21
 * @LastEditTime : 2025-11-07 19:58:57
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|短信验证码类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//阿里sdk
use Aliyun\DySDKLite\SignatureHelper;

//腾讯sdk
use Qcloud\Sms\SmsSingleSender;

class ZibSMS
{
    public static $to   = '';
    public static $code = '';
    public static $time = '30';

    public static function send($to, $code, $sdk = '')
    {
        if (!self::is_phonenumber($to)) {
            return array('error' => 1, 'ys' => 'danger', 'to' => $to, 'msg' => '手机号码格式有误');
        }

        $sdk = $sdk ? $sdk : _pz('sms_sdk');
        if (!$sdk) {
            return array('error' => 1, 'ys' => 'danger', 'to' => $to, 'msg' => '暂无短信接口，请与客服联系');
        }

        self::$to   = $to;
        self::$code = $code;

        switch ($sdk) {
            case 'ali':
                $result = self::ali_send($to, $code);
                break;
            case 'tencent':
                $result = self::tencent_send($to, $code);
                break;
            case 'smsbao':
                $result = self::smsbao_send();
                break;
            case 'fcykj':
                $result = self::fcykj_send();
                break;
        }

        if (!empty($result['result']) && empty($result['msg'])) {
            $result['msg'] = '短信已发送';
        }
        return $result;
    }
    public static function is_phonenumber($to)
    {

        $sms_number_limit_ch = _pz('sms_number_limit_ch', true);

        return $sms_number_limit_ch ? preg_match("/^1[3-9]{1}\d{9}$/", $to) : preg_match("/^(?:\d{5,}|\+\d{6,}|[\+]?\d{2,}[\-\s]\d{2,}[0-9|\-|\s]{0,15})$/", $to);
    }

    //华信云短信
    //未启用
    public static function huaxinyun_send()
    {
        $cofig = _pz('sms_huaxinyun_option');
        if (empty($cofig['username']) || empty($cofig['password']) || empty($cofig['template'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '华信云短信：缺少配置参数');
        }

        if (!stristr($cofig['template'], '{code}')) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '短信宝：模板内容缺少{code}变量符');
        }
        $content = str_replace('{code}', self::$code, $cofig['template']);
        $content = str_replace('{time}', self::$time, $content);

        $api_url   = 'https://dx.ipyy.net/sms.aspx';
        $curl_data = array(
            'action'   => 'send',
            'userid'   => '',
            'account'  => $cofig['username'],
            'password' => $cofig['password'],
            'mobile'   => self::$to,
            'extno'    => '',
            'content'  => $content,
            'sendtime' => '', //定时短信发送时间,格式 2016-12-06T08:09:10+08:00，

        );

        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout(6000)->post($api_url, $curl_data);

        if (empty($response->success)) {
            return array('msg' => '链接超时，请稍候再试');
        }
        $result  = $response->json();
        $toArray = (array) $result;
        if (!empty($toArray['SuccessCounts'])) {
            $toArray['error']  = 0;
            $toArray['result'] = true;
        } else {
            $toArray['error']  = 1;
            $toArray['msg']    = $toArray['Description'];
            $toArray['result'] = false;
        }
        return $toArray;
    }

    //风吹雨短信
    public static function fcykj_send()
    {
        $cofig = _pz('sms_fcykj_option');
        if (empty($cofig['appid']) || empty($cofig['auth_token']) || empty($cofig['template_id'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '风吹雨短信：缺少配置参数');
        }

        $api_url   = 'https://sms.fcykj.net/api.php/Interfaced/sms_single';
        $curl_data = array(
            'appid'       => $cofig['appid'],
            'auth_token'  => $cofig['auth_token'],
            'template_id' => $cofig['template_id'], //模板id
            'content'     => self::$code . ',30', //模板变量
            'mobile'      => self::$to, //接收短信的电话号码
        );

        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout(6000)->post($api_url, $curl_data);
        if (empty($response->success)) {
            return array('msg' => '链接超时，请稍候再试');
        }
        $result  = $response->json();
        $toArray = (array) $result;
        if (!empty($toArray['code'])) {
            $toArray['error']  = 0;
            $toArray['result'] = true;
        } else {
            $toArray['error']  = 1;
            $toArray['msg']    = $toArray['msg'];
            $toArray['result'] = false;
        }
        return $toArray;
    }

    /**
     * @description: 短信宝
     * @param {*}
     * @return {*}
     */
    public static function smsbao_send()
    {
        $cofig = _pz('sms_smsbao_option');
        if (empty($cofig['userame']) || (empty($cofig['password']) && empty($cofig['template']))) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '短信宝：缺少配置参数');
        }

        if (!stristr($cofig['template'], '{code}')) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '短信宝：模板内容缺少{code}变量符');
        }

        $statusStr = array(
            '0'  => '短信发送成功',
            '-1' => '参数不全',
            '-2' => '服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！',
            '30' => '短信宝：密码错误',
            '40' => '短信宝：账号不存在',
            '41' => '短信宝：余额不足',
            '42' => '短信宝：帐户已过期',
            '43' => '短信宝：IP地址限制',
            '50' => '短信宝：内容含有敏感词',
            '51' => '手机号码不正确',
        );
        $smsapi = 'http://api.smsbao.com/';
        $user   = $cofig['userame']; //短信平台账号
        $pass   = md5($cofig['password']); //短信平台密码
        if (!empty($cofig['api_key'])) {
            $pass = $cofig['api_key'];
        }
        $content = str_replace('{code}', self::$code, $cofig['template']);
        $content = str_replace('{time}', self::$time, $content);
        $sendurl = $smsapi . 'sms?u=' . $user . '&p=' . $pass . '&m=' . self::$to . '&c=' . urlencode($content);
        $result  = file_get_contents($sendurl);

        $toArray = array();
        if ($result == 0) {
            $toArray['error']  = 0;
            $toArray['result'] = true;
        } else {
            $toArray['error']  = 1;
            $toArray['msg']    = (isset($statusStr[$result]) ? $statusStr[$result] : '短信发送失败') . ' | 错误码:' . $result;
            $toArray['result'] = false;
        }
        return $toArray;
    }

    //腾讯云短信发送
    public static function tencent_send($to, $code)
    {

        // https://cloud.tencent.com/document/product/382/9557

        $cofig = _pz('sms_tencent_option');
        if (empty($cofig['app_id']) || empty($cofig['app_key']) || empty($cofig['sign_name']) || empty($cofig['template_id'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '腾讯云短信：缺少配置参数');
        }
        // 短信应用 SDK AppID
        $app_id = $cofig['app_id'];
        // 短信应用 SDK AppKey
        $app_key = $cofig['app_key'];
        // 签名参数
        $sign_name = $cofig['sign_name'];
        // 短信模板 ID
        $template_id = $cofig['template_id'];

        try {
            $ssender = new SmsSingleSender($app_id, $app_key);
            $result  = $ssender->sendWithParam('86', $to, $template_id, array($code, '30'), $sign_name);
            $toArray = json_decode($result, true);

            if (isset($toArray['result']) && $toArray['result'] == 0) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error']  = 1;
                $toArray['msg']    = $toArray['errmsg'] . ' | 错误码:' . $toArray['result'];
                $toArray['result'] = false;
            }
            return $toArray;
        } catch (\Exception $e) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $e->getMessage());
        }
    }

    //阿里云:号码认证发送短信
    public static function ali_verify_send($to, $code, $cofig)
    {

        if (empty($cofig['keyid']) || empty($cofig['keysecret']) || empty($cofig['sign_name'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '阿里云短信：缺少配置参数');
        }
        //准备参数
        $access_keyid  = $cofig['keyid'];
        $access_secret = $cofig['keysecret'];
        $sign_name     = $cofig['sign_name'];
        $template_code = '100001';

        // fixme 必填：是否启用https
        $security = false;

        $params = array();
        // fixme 必填: 短信接收号码
        $params['PhoneNumber'] = $to;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params['SignName'] = $sign_name;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params['TemplateCode'] = $template_code;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = json_encode(array(
            'code' => $code,
            'min'  => self::$time,
        ));

        //引入阿里签名文件
        require_once __DIR__ . '/SignatureHelper.php';
        $helper = new SignatureHelper();
        try {
            $result = $helper->request(
                $access_keyid,
                $access_secret,
                'dypnsapi.aliyuncs.com',
                array_merge($params, array(
                    'RegionId' => 'cn-hangzhou',
                    'Action'   => 'SendSmsVerifyCode',
                    'Version'  => '2017-05-25',
                )),
                $security
            );
            $toArray = (array) $result;
            if (!empty($toArray['Code']) && $toArray['Code'] == 'OK') {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error'] = 1;
                $toArray['msg']   = $toArray['Message'] . ' | 错误码: ' . $toArray['Code'];
            }
            return $toArray;
        } catch (\Exception $e) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $e->getMessage());
        }
    }

    //阿里云发送短信
    public static function ali_send($to, $code)
    {
        // Download：https://github.com/aliyun/openapi-sdk-php

        $cofig    = _pz('sms_ali_option');
        $api_type = $cofig['api_type'] ?? '';
        if ($api_type === 'verify') {
            return self::ali_verify_send($to, $code, $cofig);
        }

        if (empty($cofig['keyid']) || empty($cofig['keysecret']) || empty($cofig['sign_name']) || empty($cofig['template_code'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '阿里云短信：缺少配置参数');
        }
        //准备参数
        $access_keyid  = $cofig['keyid'];
        $access_secret = $cofig['keysecret'];
        $sign_name     = $cofig['sign_name'];
        $template_code = $cofig['template_code'];
        $regionid      = !empty($cofig['regionid']) ? $cofig['regionid'] : 'cn-hangzhou';

        // fixme 必填：是否启用https
        $security = false;

        $params = array();
        // fixme 必填: 短信接收号码
        $params['PhoneNumbers'] = $to;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params['SignName'] = $sign_name;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params['TemplateCode'] = $template_code;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = json_encode(array(
            'code' => $code,
        ));

        //引入阿里签名文件
        require_once __DIR__ . '/SignatureHelper.php';
        $helper = new SignatureHelper();
        try {
            $result = $helper->request(
                $access_keyid,
                $access_secret,
                'dysmsapi.aliyuncs.com',
                array_merge($params, array(
                    'RegionId' => 'cn-hangzhou',
                    'Action'   => 'SendSms',
                    'Version'  => '2017-05-25',
                )),
                $security
            );
            $toArray = (array) $result;
            if (!empty($toArray['BizId'])) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error'] = 1;
                $toArray['msg']   = $toArray['Message'] . ' | 错误码: ' . $toArray['Code'];
            }
            return $toArray;
        } catch (\Exception $e) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $e->getMessage());
        }
    }

    //阿里云SDK3.0:alibabacloud/client/发送短信
    public static function ali_send_2($to, $code)
    {
        // Download：https://github.com/aliyun/openapi-sdk-php

        $cofig = _pz('sms_ali_option');
        if (empty($cofig['keyid']) || empty($cofig['keysecret']) || empty($cofig['sign_name']) || empty($cofig['template_code'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '阿里云短信：缺少配置参数');
        }
        $access_keyid  = $cofig['keyid'];
        $access_secret = $cofig['keysecret'];
        $sign_name     = $cofig['sign_name'];
        $template_code = $cofig['template_code'];
        $regionid      = !empty($cofig['regionid']) ? $cofig['regionid'] : 'cn-hangzhou';

        AlibabaCloud::accessKeyClient($access_keyid, $access_secret)
            ->regionId($regionid)
            ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId'      => $regionid,
                        'PhoneNumbers'  => $to,
                        'SignName'      => $sign_name,
                        'TemplateCode'  => $template_code,
                        'TemplateParam' => json_encode(['code' => $code]),
                    ],
                ])
                ->request();
            $toArray = $result->toArray();
            if (!empty($toArray['BizId'])) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error'] = 1;
                $toArray['msg']   = $toArray['Message'] . ' | 错误码:' . $toArray['Code'];
            }
            return $toArray;
        } catch (ClientException $e) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $e->getErrorMessage());
        } catch (ServerException $e) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $e->getErrorMessage());
        }
    }

    public static function tencent_send_2($to, $code)
    {
        //腾讯php sdk3.0发送短信
        $cofig = _pz('sms_tencent_option');
        if (empty($cofig['app_id']) || empty($cofig['secret_id']) || empty($cofig['secret_key']) || empty($cofig['sign_name']) || empty($cofig['template_id'])) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => '腾讯云短信：缺少配置参数');
        }
        $app_id        = $cofig['app_id'];
        $access_keyid  = $cofig['secret_id'];
        $access_secret = $cofig['secret_key'];
        $sign_name     = $cofig['sign_name'];
        $template_id   = $cofig['template_id'];
        $regionid      = !empty($cofig['regionid']) ? $cofig['regionid'] : 'ap-beijing';

        try {
            $cred = new Credential($access_keyid, $access_secret);

            // 实例化一个 http 选项，可选，无特殊需求时可以跳过
            $httpProfile = new HttpProfile();
            // $httpProfile->setReqMethod("GET");
            $httpProfile->setReqTimeout(10); // 请求超时时间，单位为秒（默认60秒）
            $httpProfile->setEndpoint('sms.tencentcloudapi.com'); // 指定接入地域域名（默认就近接入）

            // 实例化一个 client 选项，可选，无特殊需求时可以跳过
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);

            // 实例化 SMS 的 client 对象，clientProfile 是可选的

            $clientProfile = null;
            $client        = new SmsClient($cred, $regionid, $clientProfile);
            // 实例化一个 sms 发送短信请求对象，每个接口都会对应一个 request 对象。
            $req = new SendSmsRequest();

            $params = array(
                'PhoneNumberSet'   => array($to),
                'TemplateParamSet' => array($code, '30'),
                'TemplateID'       => $template_id,
                'Sign'             => $sign_name,
                'SmsSdkAppid'      => $app_id,
            );
            $req->fromJsonString(json_encode($params));

            $resp    = $client->SendSms($req);
            $toArray = @json_decode($resp->toJsonString(), true)['SendStatusSet'][0];
            if (($toArray['Code'] == 'Ok') && ($toArray['Message'] == 'send success')) {
                $toArray['error']  = 0;
                $toArray['result'] = true;
            } else {
                $toArray['error'] = 1;
                $toArray['msg']   = $toArray['Message'] . ' | ' . $toArray['Code'];
            }
            return $toArray;
        } catch (TencentCloudSDKException $e) {
            return array('error' => 1, 'ys' => 'danger', 'msg' => $e->getMessage());
        }
    }
}
