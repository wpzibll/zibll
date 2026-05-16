<?php
namespace Weixin\GZH;

class GZHException extends \Exception
{
}

class OAuth2
{
    protected $appid;
    protected $secret;
    protected $accessToken;
    public $state;
    public $ticket;
    public $callback;
    public $code_keyword;
    public $code_reply_template;
    public $code_expiration_time = 300; //验证码登录，验证码过期时间
    public $code_length          = 4; //验证码长度
    public $code_save_key        = 'weixingzh_code_data'; //验证码登录保存key
    public static $getQrcode_count;

    public function __construct($appid = null, $appSecret = null, $access_token = null)
    {
        $this->appid  = $appid;
        $this->secret = $appSecret;
        $this->getAccessToken();
    }

    public function CodeReply()
    {
        $callback = $this->callback;
        if (empty($callback['FromUserName'])) {
            return false;
        }

        if ((!isset($callback['Content']) || trim($callback['Content']) !== $this->code_keyword) && (!isset($callback['Event']) || $callback['Event'] !== 'subscribe')) {
            return false;
        }

        $msg = $this->code_reply_template;
        $msg = strstr($msg, '%code%') ? str_replace('%code%', $this->getCode($callback['FromUserName']), $msg) : $msg;
        $msg = strstr($msg, '%time%') ? str_replace('%time%', (string) $this->code_expiration_time, $msg) : $msg;
        $msg = strstr($msg, '%keyword%') ? str_replace('%keyword%', $this->code_keyword, $msg) : $msg;

        return $this->sendMessage($msg);
    }

    /**
     * @description: 获取一个用户open——ID
     * @param {*}
     * @return {*}
     */
    public function getUserKey($code)
    {
        $code      = strtoupper($code);
        $timestamp = (int) current_time('YmdHis');
        $data      = get_option($this->code_save_key);

        //如果重复了，则再次重新获取
        if (!isset($data[$code])) {
            return 0;
        }

        if ($timestamp > ($data[$code]['time'] + $this->code_expiration_time)) {
            return -1;
        }

        return $data[$code]['user_key'];
    }

    /**
     * @description: 获取一个验证码
     * @param {*}
     * @return {*}
     */
    public function getCode($user_key)
    {
        $timestamp = (int) current_time('YmdHis');
        $code      = $this->getVerificationCode();
        $data      = get_option($this->code_save_key);

        if (!$data || !is_array($data)) {
            $data = array();
        }

        //如果重复了，则再次重新获取
        if (isset($data[$code])) {
            return $this->getCode($user_key);
        }

        $new        = array();
        $new[$code] = array(
            'time'     => $timestamp,
            'user_key' => $user_key,
        );

        $data = array_merge($new, $data);
        $data = $this->DeleteCodeExpiredData($data); //删除过期数据

        //储存数据
        update_option($this->code_save_key, $data);

        return $code;
    }

    /**
     * @description: 删除过期数据
     * @param {*} $data
     * @return {*}
     */
    public function DeleteCodeExpiredData($data)
    {

        if (!$data || !is_array($data)) {
            return array();
        }

        $new_data  = array();
        $timestamp = (int) current_time('YmdHis');
        $user_keys = array();

        foreach ($data as $k => $v) {
            //时间没有过期，且user_key不存在
            if ($timestamp < ($v['time'] + $this->code_expiration_time) && !in_array($v['user_key'], $user_keys)) {
                $new_data[$k] = $v;
                $user_keys[]  = $v['user_key'];
            }
        }

        return $new_data;
    }

    /**
     * @description: 获取一个验证码
     * @param {*} $codelen 数量
     * @return {*}
     */
    private function getVerificationCode()
    {
        $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ1234567890';
        $_leng   = strlen($charset) - 1;
        $code    = '';
        for ($i = 1; $i <= $this->code_length; $i++) {
            $code .= $charset[mt_rand(0, $_leng)];
        }
        return strtoupper($code);
    }

    /**
     * @description: 获取access_token,token的有效时间为2小时，这里可以做下处理，提高效率不用每次都去获取
     * @param {*}
     * @return {*}
     */
    public function getAccessToken()
    {

        $accessToken_option = zib_get_option('weixingzh_access_token');
        $new_time           = strtotime('+300 Second'); //获取现在时间加5分钟

        if (!empty($accessToken_option['access_token']) && $accessToken_option['expiration_time'] > $new_time) {
            $this->accessToken = $accessToken_option['access_token'];
        } else {
            $this->accessToken = $this->getAccessTokenFromRemote();
        }
        return $this->accessToken;
    }

    /**
     * @description: 远程获取access_token
     * @param {*}
     * @return {*}
     */
    private function getAccessTokenFromRemote()
    {
        $url       = 'https://api.weixin.qq.com/cgi-bin/stable_token';
        $post_data = array(
            'grant_type' => 'client_credential',
            'appid'      => $this->appid,
            'secret'     => $this->secret,
        );

        $res = json_decode($this->httpRequest($url, json_encode($post_data)), true);

        if (!empty($res['access_token'])) {

            //储存access_token到本地
            $res['expiration_time'] = strtotime('+' . $res['expires_in'] . ' Second');
            zib_update_option('weixingzh_access_token', $res);

            $this->accessToken = $res['access_token'];
            return $res['access_token'];
        }

        if (!is_array($res)) {
            throw new GZHException('AccessToken获取失败：网络连接异常，无法访问微信API接口');
        }

        if (isset($res['errcode'])) {
            throw new GZHException('AccessToken获取失败，错误码：' . $res['errcode'] . '，错误信息：' . $this->errmsgToChinese($res['errmsg']));
        }

        throw new GZHException('AccessToken获取失败：' . json_encode($res));
    }

    /**
     * @description: 发送模板消息
     * @param {*} $open_id
     * @param {*} $template_id
     * @param {*} $url
     * @param {*} $data
     * @param {*} $topcolor
     * @return {*}
     */
    public function sendTemplateMsg($open_id, $template_id, $data, $url = '')
    {
        $accessToken = $this->getAccessToken();

        $api_url   = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $accessToken;
        $curl_data = array(
            'touser'      => $open_id,
            'template_id' => $template_id,
            'url'         => $url,
            'data'        => $data,
        );

        $result = json_decode($this->httpRequest($api_url, $curl_data), true);

        if (!is_array($result)) {
            throw new GZHException('发送模板消息失败：网络连接异常，无法访问微信API接口');
        }

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new GZHException('发送模板消息失败，错误码：' . $result['errcode'] . '，错误信息：' . $this->errmsgToChinese($result['errmsg']));
        }

        return $result;
    }

    //errmsg转换为中文
    private function errmsgToChinese($errmsg)
    {
        $errmsg_array = array(
            'invalid template_id'             => '模板ID无效或不存在',
            'invalid url size'                => '不合法的 URL 长度',
            'invalid appid'                   => '不合法的 AppID ，请开发者检查 AppID 的正确性',
            'invalid message type'            => '不合法的消息类型',
            'invalid openid'                  => '不合法的 OpenID ，请开发者确认 OpenID （该用户）是否已关注公众号',
            'system error'                    => '系统繁忙，此时请开发者稍候再试',
            'invalid appsecret'               => '不合法的 AppSecret ，请开发者检查 AppSecret 的正确性',
            'not in whitelist'                => 'IP 地址不在白名单中，请在接口开通时将 IP 地址添加到接口 IP 白名单中',
            'reach max api daily quota limit' => '调用超过天级别频率限制',
            'argument invalid'                => '参数内容错误，或参数格式错误',
            'require subscribe'               => '该用户已取消关注公众号',
        );

        //示例：invalid template_id rid: 694d28f7-09370de7-22d83d5d
        $msg = str_replace(array_keys($errmsg_array), array_values($errmsg_array), $errmsg);
        return $msg;
    }

    /***
     * POST或GET请求
     * @url 请求url
     * @data POST数据
     * @return
     **/
    private function httpRequest($url, $data = '')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5); //超时5秒

        if ($data) {
            $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
            //判断是否为POST请求
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
            ]);
        }

        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /***
     * 获取openID和unionId
     * @code 微信授权登录返回的code
     * @return
     **/
    public function getOpenIdOrUnionId($code)
    {
        $url  = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appid . '&secret=' . $this->secret . '&code=' . $code . '&grant_type=authorization_code';
        $data = $this->httpRequest($url);
        return $data;
    }

    /**
     * @description: 创建自定义菜单
     * @param {*} $data
     * @return {*}
     */
    public function createMenu($data = '')
    {

        $url    = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->accessToken;
        $result = $this->httpRequest($url, json_encode($data, JSON_UNESCAPED_UNICODE));
        return json_decode($result, true);
    }

    /**
     * 获取自定义菜单
     */
    public function getSelfMenu()
    {
        $url    = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token=' . $this->accessToken;
        $result = $this->httpRequest($url);
        return json_decode($result, true);
    }

    public function freepublishBatchget($paged = 1, $page_size = 20, $no_content = true)
    {

        $args = [
            'offset'     => ($paged - 1) * $page_size,
            'count'      => $page_size,
            'no_content' => $no_content ? 1 : 0,
        ];

        $url    = 'https://api.weixin.qq.com/cgi-bin/freepublish/batchget?access_token=' . $this->accessToken;
        $result = $this->httpRequest($url, json_encode($args, JSON_UNESCAPED_UNICODE));
        return json_decode($result, true);
    }

    /***
     * 回复消息
     * @msg 消息内容
     * @return
     **/
    public function sendMessage($msg = '')
    {
        $callback = $this->callback;

        if (empty($callback['FromUserName']) || empty($callback['ToUserName']) || !$msg) {
            return false;
        }

        $time    = time(); //时间戳
        $msgtype = 'text'; //消息类型：文本
        $textTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>';

        $fromUsername = $callback['FromUserName']; //请求消息的用户
        $toUsername   = $callback['ToUserName']; //"我"的公众号id
        $resultStrq   = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $msg);
        echo $resultStrq;
        return $resultStrq;
    }

    /***
     * 自动回复消息
     * @msg 消息内容
     * @return
     **/
    public function autoReply($args = array())
    {
        $callback = $this->callback;
        if (!empty($callback['MsgType'])) {
            switch ($callback['MsgType']) {
                case 'text':
                    $callback_content = trim($callback['Content']);
                    if (!empty($args['text'][0])) {
                        foreach ($args['text'] as $v) {
                            $in = trim($v['in']);
                            if ('include' === $v['mode']) {
                                if ($in && stristr($callback_content, $in)) {
                                    return $this->sendMessage($v['out']);
                                }
                            } elseif ('preg' === $v['mode']) {
                                if ($in && preg_match("$in", $callback_content)) {
                                    return $this->sendMessage($v['out']);
                                }
                            } else {
                                if ($in && $in == $callback_content) {
                                    return $this->sendMessage($v['out']);
                                }
                            }
                        }
                    }

                    //搜索回复
                    if (!empty($args['search']) && $callback_content) {
                        $search_message = $this->searchPost($callback_content, $args);
                        if ($search_message) {
                            return $this->sendMessage($search_message);
                        }
                    }

                    break;
                case 'image':
                    if (!empty($args['image'])) {
                        return $this->sendMessage($args['image']);
                    }
                    break;
                case 'voice':
                    if (!empty($args['voice'])) {
                        return $this->sendMessage($args['voice']);
                    }
                    break;
            }
            if (!empty($args['default'])) {
                return $this->sendMessage($args['default']);
            }
        }
    }

    /**
     * @description: 获取文章
     * @param {*} $args
     * @return {*}
     */
    public function searchPost($s, $opt = array())
    {

        $option    = !isset($opt['search_option']) ? [] : $opt['search_option'];
        $showposts = !empty($option['showposts']) ? $option['showposts'] : 6;
        $orderby   = !empty($option['orderby']) ? $option['orderby'] : 'date';

        $search_args = array(
            's'           => $s,
            'post_type'   => ['post'],
            'post_status' => 'publish',
            'showposts'   => $showposts,
            'orderby'     => $orderby,
            'order'       => 'DESC',
        );

        if (!empty($option['search_forum']) && _pz('bbs_s')) {
            $search_args['post_type'][] = 'forum_post';
        }

        if (!empty($option['search_product']) && _pz('shop_s')) {
            $search_args['post_type'][] = 'shop_product';
        }

        if (!empty($option['exclude'])) {
            $search_args['post__not_in'] = preg_split("/,|，|\s|\n/", $option['exclude']);
        }

        if (!empty($option['exclude_cat'])) {
            $search_args['category__not_in'] = preg_split("/,|，|\s|\n/", $option['exclude_cat']);
        }

        $mate_orderbys_num = array('score', 'plate_id', 'posts_count', 'reply_count', 'today_reply_count', 'follow_count', 'follow', 'views', 'like', 'favorite', 'zibpay_price', 'zibpay_points_price', 'sales_volume', 'balance', 'points', 'phone_number');
        if (in_array($orderby, $mate_orderbys_num)) {
            $search_args['orderby']  = 'meta_value_num';
            $search_args['meta_key'] = $orderby;
        }

        $query = new \WP_Query($search_args);
        $lists = '';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post     = get_post();
                $subtitle = trim(strip_tags(zib_get_post_meta($post->ID, 'subtitle', true)));
                $title    = trim(strip_tags($post->post_title)) . $subtitle;
                $title    = str_replace('&amp;', '&', $title);

                $lists .= '<a href="' . get_permalink() . '">' . $title . '</a>' . "\n\n";
            }
        }
        wp_reset_query();

        if ($lists) {
            return (!empty($option['title']) ? $option['title'] : '为您查询到以下内容') . "\n\n" . $lists . '<a href="' . home_url('/?s=') . esc_attr($s) . '">【查看更多搜索结果】</a>';
        }

        return false;
    }

    /***
     * 生成带参数的二维码|此方式暂未使用
     * 使用scene_id的方式，QR_SCENE为临时的整型参数值
     * @scene_id 自定义参数（整型）
     * @return
     **/
    public function getQrcodeById($repeat = true)
    {
        $state       = time() . mt_rand(11, 99);
        $this->state = (int) $state;

        $url  = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->accessToken;
        $data = array(
            'expire_seconds' => 3600, //二维码的有效时间（1小时）
            'action_name'    => 'QR_SCENE',
            'action_info'    => array('scene' => array('scene_id' => $this->state)),
        );
        $result = $this->httpRequest($url, json_encode($data));
        $result = json_decode($result, true);

        if (!empty($result['ticket'])) {
            $this->ticket = $result['ticket'];
            return $result;
        }

        if (!is_array($result)) {
            throw new GZHException('二维码获取失败：网络连接异常，无法访问微信API接口');
        }

        //如果access_token错误则在执行一次
        if (!empty($result['errmsg']) && stristr($result['errmsg'], 'access_token') && $repeat) {
            $this->getAccessTokenFromRemote();
            return $this->getQrcodeById(false);
        }

        if (isset($result['errcode'])) {
            throw new GZHException('二维码获取失败，错误码：' . $result['errcode'] . '，错误信息：' . $this->errmsgToChinese($result['errmsg']));
        }

        throw new GZHException('二维码获取失败：' . json_encode($result));
    }

    /***
     * 生成带参数的二维码
     * 使用 scene_str 方式，QR_STR_SCENE为临时的字符串参数值
     * @scene_str 自定义参数（字符串）
     * @return
     **/
    public function getQrcode($repeat = true)
    {
        $state       = time() . mt_rand(11, 99);
        $this->state = $state;
        $url         = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->accessToken;
        $data        = array(
            'expire_seconds' => 3600 * 24, //二维码的有效时间（1天）
            'action_name'    => 'QR_STR_SCENE',
            'action_info'    => array('scene' => array('scene_str' => $this->state)),
        );
        $result = $this->httpRequest($url, json_encode($data));
        $result = json_decode($result, true);
        if (!empty($result['ticket'])) {
            $this->ticket = $result['ticket'];
            return $result;
        }

        if (!is_array($result)) {
            throw new GZHException('二维码获取失败：网络连接异常，无法访问微信API接口');
        }

        //如果access_token错误则在执行一次
        if (!empty($result['errmsg']) && stristr($result['errmsg'], 'access_token') && $repeat) {
            $this->getAccessTokenFromRemote();
            return $this->getQrcode(false);
        }

        if (isset($result['errcode'])) {
            throw new GZHException('二维码获取失败，错误码：' . $result['errcode'] . '，错误信息：' . $this->errmsgToChinese($result['errmsg']));
        }

        throw new GZHException('二维码获取失败：' . json_encode($result));
    }

    /**
     * 换取二维码
     * @ticket
     * @return
     */
    public function generateQrcode()
    {

        $this->getQrcode();

        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $this->ticket;
    }

    /***
     * 通过openId获取用户信息
     * @openId
     * @return
     **/
    public function getUserInfo($openId)
    {

        //   $url  = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $this->accessToken . "&openid=" . $openId . "&lang=zh_CN";
        $url  = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->accessToken . '&openid=' . $openId . '&lang=zh_CN';
        $data = json_decode($this->httpRequest($url), true);

        if (!empty($data['openid'])) {
            return $data;
        }

        throw new GZHException('用户信息获取失败：' . json_encode($data));
    }

    /***
     * 回调函数
     **/
    public function callback()
    {
        $callbackXml = file_get_contents('php://input'); //获取返回的xml
        //下面是返回的xml
        //<xml><ToUserName><![CDATA[gh_f6b4da984c87]]></ToUserName> //微信公众号的微信号
        //<FromUserName><![CDATA[oJxRO1Y2NgWJ9gMDyE3LwAYUNdAs]]></FromUserName> //openid用于获取用户信息，做登录使用
        //<CreateTime>1531130986</CreateTime> //回调时间
        //<MsgType><![CDATA[event]]></MsgType>
        //<Event><![CDATA[SCAN]]></Event>
        //<EventKey><![CDATA[lrfun1531453236]]></EventKey> //上面自定义的参数（scene_str）
        //<Ticket><![CDATA[gQF57zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyY2ljbjB3RGtkZWwxbExLY3hyMVMAAgTvM0NbAwSAOgkA]]></Ticket> //换取二维码的ticket
        //</xml>

        $data = json_decode(json_encode(simplexml_load_string($callbackXml, 'SimpleXMLElement', LIBXML_NOCDATA)), true); //将返回的xml转为数组

        $this->callback = $data;
        if (!empty($data['FromUserName']) && !empty($data['EventKey']) && !empty($data['Event']) && in_array($data['Event'], array('subscribe', 'SCAN'))) {
            return $data;
        }
        return false;
    }
}
