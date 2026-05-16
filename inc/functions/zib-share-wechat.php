<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-17 19:56:54
 * @LastEditTime: 2024-06-18 23:43:33
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|微信分享
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (_pz('wechat_share_s')) {
    new ZibWeChatShare(_pz('wechat_share_option'));
}

class ZibWeChatShare
{
    protected $appid;
    protected $secret;
    protected $accessToken;
    protected $Ticket;
    protected $onlyLogo;

    public function __construct($cofig)
    {
        if (empty($cofig['appid']) || empty($cofig['app_secret'])) {
            return;
        }
        $this->appid    = $cofig['appid'];
        $this->secret   = $cofig['app_secret'];
        $this->onlyLogo = !empty($cofig['only_logo']); //仅使用logo

        add_action('wp_footer', [$this, 'addFooterDate'], 99);
    }

    public function addFooterDate()
    {
        $url     = $this->getLink();
        $title   = '';
        $img     = $this->getImg();
        $desc    = '';
        $getSign = $this->getSign($url);

        ?>
<script type="text/javascript">
    window.WeChatShareDate = {
        appId: '<?php echo $getSign['appId']; ?>',
        timestamp: '<?php echo $getSign['timestamp']; ?>',
        nonceStr: '<?php echo $getSign['nonceStr']; ?>',
        signature: '<?php echo $getSign['signature']; ?>',
        url: '<?php echo $url; ?>',
        title: '<?php echo $title; ?>',
        img: '<?php echo $img; ?>',
        desc: '<?php echo $desc; ?>',
    }
</script>
        <?php
}

    //页面底部添加js内容
    public function addFooterJS()
    {
        $url     = $this->getLink();
        $title   = '';
        $img     = $this->getImg();
        $desc    = '';
        $getSign = $this->getSign($url);

        ?>
<script src="//res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<script type="text/javascript">

$(document).ready(function () {
    wx.config({
        debug: false,
        appId: '<?php echo $getSign['appId']; ?>',
        timestamp: '<?php echo $getSign['timestamp']; ?>',
        nonceStr: '<?php echo $getSign['nonceStr']; ?>',
        signature: '<?php echo $getSign['signature']; ?>',
        jsApiList: [
            'updateTimelineShareData',
            'updateAppMessageShareData'
        ]
    });

    wx.ready(function () {
        var _title = '<?php echo $title; ?>' || document.title;
        var _link = '<?php echo $url; ?>' || window.location.href;
        var _desc = '<?php echo $desc; ?>' || $('meta[name="description"]').attr('content');
        var _img = '<?php echo $img; ?>';

        wx.updateTimelineShareData({
            title: _title,
            link: _link,
            imgUrl: _img
        });
        wx.updateAppMessageShareData({
            title: _title,
            desc: _desc,
            link: _link,
            imgUrl: _img
        });
    });
});
</script>
        <?php
}

    /**
     * @description: 获取当前页面的链接
     * @param {*} $url
     * @return {*}
     */
    public function getLink()
    {

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url      = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        return $url;
    }

    /**
     * @description: 获取当前页面的图像
     * @param {*} $url
     * @return {*}
     */
    public function getImg()
    {
        //默认图像
        $default = _pz('iconpng') ?: _pz('favicon');

        if ($this->onlyLogo) {
            return $default;
        }

        $obj_id = get_queried_object_id();
        $pic    = '';
        if (is_tax() && $obj_id) {
            $pic = zib_get_taxonomy_img_url($obj_id, 'full');
        }

        if (is_single() && $obj_id) {
            $pic = zib_post_thumbnail('full', '', true, $obj_id);
        }

        if (call_user_func('is_' . 'author')) {
            global $wp_query;
            $curauth = $wp_query->get_queried_object();
            if (!empty($curauth->ID)) {
                $pic = zib_get_user_meta($curauth->ID, 'custom_avatar', true);
            }
        }

        return $pic ?: $default;
    }

    /**
     * @description: 获取验证参数
     * @param {*} $url
     * @return {*}
     */
    public function getSign($url)
    {
        $jsapiTicket = $this->getTicket();
        $timestamp   = time();
        $nonceStr    = $this->getNonceString();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $signStr = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($signStr);

        $signPackage = array(
            'appId'     => $this->appid,
            'nonceStr'  => $nonceStr,
            'timestamp' => $timestamp,
            'signature' => $signature,
        );

        return $signPackage;
    }

    /**
     * @description: 移动判断获取Ticket
     * @param {*}
     * @return {*}
     */
    private function getTicket()
    {
        $ticket_option = zib_get_option('wechatshare_ticket');
        $new_time      = strtotime('+300 Second'); //获取现在时间加5分钟

        if (!empty($ticket_option['ticket']) && $ticket_option['expiration_time'] > $new_time) {
            $this->Ticket = $ticket_option['ticket'];
        } else {
            $this->setTicketFromRemote();
        }

        return $this->Ticket;
    }

    /**
     * 通过远程接口设置JS Ticket.
     */
    private function setTicketFromRemote()
    {
        $tmpToken = $this->getAccessToken();

        // 从远程接口获取
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $tmpToken . '&type=jsapi';
        $res = json_decode($this->httpRequest($url), true);

        if (!empty($res['ticket'])) {
            //储存access_token到本地
            $res['expiration_time'] = strtotime('+' . $res['expires_in'] . ' Second');
            zib_update_option('wechatshare_ticket', $res);
            $this->Ticket = $res['ticket'];
        }
    }

    /**
     * @description: 移动判断获取AccessToken
     * @param {*}
     * @return {*}
     */
    private function getAccessToken()
    {
        $accessToken_option = zib_get_option('weixingzh_access_token');
        $new_time           = strtotime('+300 Second'); //获取现在时间加5分钟

        if (!empty($accessToken_option['access_token']) && $accessToken_option['expiration_time'] > $new_time) {
            $this->accessToken = $accessToken_option['access_token'];
        } else {
            $this->setAccessTokenFromRemote();
        }
        return $this->accessToken;
    }

    /**
     * @description: 远程获取AccessToken
     * @param {*}
     * @return {*}
     */
    private function setAccessTokenFromRemote()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
        $res = json_decode($this->httpRequest($url), true);

        if (!empty($res['access_token'])) {
            //储存access_token到本地
            $res['expiration_time'] = strtotime('+' . $res['expires_in'] . ' Second');
            update_option('wechatshare_access_token', $res);
            $this->accessToken = $res['access_token'];
        }
    }

    /**
     * 获取随机数.
     */
    private function getNonceString($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str   = '';
        $len   = strlen($chars);
        for ($i = 0; $i < $length; ++$i) {
            $str .= substr($chars, mt_rand(0, $len - 1), 1);
        }

        return $str;
    }

    /***
     * POST或GET请求
     * @url 请求url
     * @data POST数据
     * @return
     **/
    private function httpRequest($url, $data = "")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);  //超时10秒

        if (!empty($data)) {
            //判断是否为POST请求
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}
