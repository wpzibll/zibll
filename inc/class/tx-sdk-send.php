<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:35:21
 * @LastEditTime: 2024-11-27 19:10:20
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|腾讯相关接口的统一请求类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class TxSDK_Send
{

    public $apiVersion    = '2019-07-22';
    public $action        = 'DescribeCaptchaResult';
    public $Language      = 'zh-CN';
    public $host          = 'captcha.tencentcloudapi.com';
    public $requestMethod = 'POST';
    public $signMethod    = 'HmacSHA1';
    public $secretId      = '';
    public $secretKey     = '';
    public $token         = '';
    public $timeout       = 10 * 1000; //10秒

    /**
     * @description:
     * @param {*} $secretId   API密钥：SecretId
     * @param {*} $secretKey  API密钥：SecretKey
     * @return {*}
     */
    public function __construct($secretId, $secretKey)
    {
        $this->secretId  = $secretId;
        $this->secretKey = $secretKey;
    }

    public function send($request)
    {
        $api_server = 'https://' . $this->host . '/';
        $request    = $this->formatRequestData($request);

        $http     = new Yurun\Util\HttpRequest;
        $response = $http->timeout($this->timeout)->send($api_server, $request, $this->requestMethod);

        return $response->json(true);
    }

    public function formatRequestData($request)
    {
        $param              = $request;
        $param["Action"]    = $this->action;
        $param["Nonce"]     = rand();
        $param["Timestamp"] = time();
        $param["Version"]   = $this->apiVersion;
        $param["Language"]  = $this->Language;
        $param["SecretId"]  = $this->secretId;

        $signStr            = $this->formatSignString($param);
        $param["Signature"] = $this->sign($signStr);
        return $param;
    }

    private function formatSignString($param)
    {

        $tmpParam = [];
        ksort($param);
        foreach ($param as $key => $value) {
            array_push($tmpParam, $key . "=" . $value);
        }
        $strParam = join("&", $tmpParam);
        $signStr  = strtoupper($this->requestMethod) . $this->host . "/?" . $strParam;
        return $signStr;
    }

    public function sign($signStr)
    {
        $secretKey     = $this->secretKey;
        $signMethod    = $this->signMethod;
        $signMethodMap = ["HmacSHA1" => "SHA1", "HmacSHA256" => "SHA256"];
        $signature     = base64_encode(hash_hmac($signMethodMap[$signMethod], $signStr, $secretKey, true));
        return $signature;
    }
}
