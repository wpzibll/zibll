<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-10-28 15:27:34
 * @LastEditTime: 2022-10-24 14:26:02
 */

/**
 * 彩虹聚合登录SDK
 * 聚合登录请求类
 * 1.0
 **/
namespace CaiHong;

class Oauth
{
    private $apiurl;
    private $appid;
    private $appkey;
    private $callback;
    public $state;

    public function __construct($config)
    {
        $this->apiurl   = rtrim($config['apiurl'], '/') . '/connect.php';
        $this->appid    = $config['appid'];
        $this->appkey   = $config['appkey'];
        $this->callback = isset($config['callback']) ? $config['callback'] : '';
    }

    //获取登录跳转url
    public function login($type)
    {

        //-------生成唯一随机串防CSRF攻击
        $state       = md5(uniqid(rand(), true));
        $this->state = $state;

        //-------构造请求参数列表
        $keysArr = array(
            "act"          => "login",
            "appid"        => $this->appid,
            "appkey"       => $this->appkey,
            "type"         => $this->c_type($type),
            "redirect_uri" => $this->callback,
            "state"        => $state,
        );
        $login_url = $this->apiurl . '?' . http_build_query($keysArr);
        $response  = $this->get_curl($login_url);
        $arr       = json_decode($response, true);
        return $arr;
    }

    public function c_type($type)
    {

        switch ($type) {
            case 'weixin':
                return 'wx';
                break;

            case 'weibo':
                return 'sina';
                break;

        }

        return $type;
    }

    public function z_type($type)
    {

        switch ($type) {
            case 'wx':
                return 'weixin';
                break;

            case 'sina':
                return 'weibo';
                break;

        }

        return $type;
    }

    //登录成功返回网站
    public function callback()
    {
        //-------请求参数列表
        $keysArr = array(
            "act"    => "callback",
            "appid"  => $this->appid,
            "appkey" => $this->appkey,
            "code"   => $_GET['code'],
        );

        //------构造请求access_token的url
        $token_url = $this->apiurl . '?' . http_build_query($keysArr);
        $response  = $this->get_curl($token_url);

        $arr = json_decode($response, true);
        return $arr;
    }

    //查询用户信息
    public function query($type, $social_uid)
    {
        //-------请求参数列表
        $keysArr = array(
            "act"        => "query",
            "appid"      => $this->appid,
            "appkey"     => $this->appkey,
            "type"       => $type,
            "social_uid" => $social_uid,
        );

        //------构造请求access_token的url
        $token_url = $this->apiurl . '?' . http_build_query($keysArr);
        $response  = $this->get_curl($token_url);

        $arr = json_decode($response, true);
        return $arr;
    }

    private function get_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
