<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-06-27 18:27:41
 * @LastEditTime: 2021-07-03 17:33:22
 */


class vmqphpPay
{

    var $config;

    function __construct($config)
    {
        $this->config = $config;
        $this->url_createOrder = rtrim($this->config['apiurl'], '/') . '/createOrder?';
        $this->api_key = $this->config['key'];
    }

    function get($parameter)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $url = $this->buildURL($parameter);

        //发送请求
        $http = new Yurun\Util\HttpRequest;
        $response = $http->timeout(10000)->get($url);
        //返回请求
        if (empty($response->success)) {
            return array('msg' => 'V免签接口链接超时，请稍候再试', 'url' => $url);
        }
        $result = $response->body();
        $resultData = json_decode($result, true);

        return $resultData;
    }

    // 数据发送
    function curl($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        return $responseText;
    }

    function buildURL($parameter)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $sign = $this->sign($parameter);

        $request_data = $parameter;
        $request_data['sign'] = $sign;
        $url = $this->url_createOrder . http_build_query($request_data);

        return $url;
    }

    function sign($parameter)
    {
        $sign_attr = $parameter['payId'] . $parameter['param'] . $parameter['type'] . $parameter['price'] . $this->api_key;

        $mysign = md5($sign_attr);

        return $mysign;
    }


    function verifyNotify()
    {
        $post = $_GET;
        if (!isset($post['payId']) || !isset($post['param']) || !isset($post['type']) || !isset($post['price']) || !isset($post['reallyPrice']) || !isset($post['sign'])) return false;

        $sign_attr = $post['payId'] . $post['param'] . $post['type'] . $post['price'] . $post['reallyPrice'] . $this->api_key;
        $mysign = md5($sign_attr);

        return $mysign == $post['sign'];
    }
}
