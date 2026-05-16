<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-05-05 22:07:12
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|社交账号登录
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户配置
 * @param {*}
 * @return {*}
 */
function get_oauth_config($type = 'qq')
{
    $defaults = array(
        'appid'         => '',
        'appkey'        => '',
        'backurl'       => (home_url('/oauth/' . $type . '/callback')),
        'agent'         => false,
        'appkrivatekey' => '',
        'auto_reply'    => array(),
    );
    return wp_parse_args((array) _pz('oauth_' . $type . '_option'), $defaults);
}

/**
 * 处理返回数据，更新用户资料
 */
function zib_oauth_update_user($args)
{
    /** 需求数据明细 */
    $defaults = array(
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'avatar'      => '',
        'description' => '',
        'getUserInfo' => array(),
    );

    $args = wp_parse_args((array) $args, $defaults);

    // 初始化信息
    $openid_meta_key = 'oauth_' . $args['type'] . '_openid';
    $openid          = $args['openid'];

    $return_data = array(
        'redirect_url' => '',
        'msg'          => '',
        'error'        => true,
    );

    global $wpdb, $current_user;

    // 查询该openid是否已存在
    $user_exist = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", $openid_meta_key, $openid));

    // 查询已登录用户
    $current_user_id = get_current_user_id();

    //如果已经登录，且该openid已经存在
    if ($current_user_id && isset($user_exist) && $current_user_id != $user_exist) {
        $type_name          = zib_get_social_type_name($args['type']);
        $return_data['msg'] = '绑定失败，您当前的' . $type_name . '已绑定过其他账号。您可以退出当前账号后，再次使用' . $type_name . '直接登录，进入用户中心即可查看绑定信息或做解绑！';
        return $return_data;
    }

    // 该第三方登录方式已经绑定账号，且未登录。直接登录
    if (isset($user_exist) && (int) $user_exist > 0) {
        $user_exist = (int) $user_exist;

        //登录
        $user = get_user_by('id', $user_exist);
        wp_set_current_user($user_exist);
        wp_set_auth_cookie($user_exist, true);
        do_action('wp_login', $user->user_login, $user);

        $return_data['redirect_url'] = zib_get_user_center_url(); //重定向链接到用户中心
        $return_data['error']        = false;
        return $return_data;
    }

    //用户中心绑定逻辑
    if ($current_user_id) {
        // 已经登录，但openid未占用，则绑定，更新用户字段
        // 更新用户mate
        $args['user_id'] = $current_user_id;

        //绑定用户不更新以下数据
        $args['name']        = '';
        $args['description'] = '';

        zib_oauth_update_user_meta($args);
        // 准备返回数据
        $return_data['redirect_url'] = zib_get_user_center_url(); //重定向链接到用户中心
        $return_data['error']        = false;
        return $return_data;
    }

    //全新的第三方账号，跳转到绑定页面，由用户选择绑定或新建
    if (zib_oauth_new_is_to_page()) {
        @session_start();
        $_SESSION['zib_user_oauth_data'] = $args;
        $rurl                            = !empty($_SESSION['oauth_rurl']) ? $_SESSION['oauth_rurl'] : '';

        $url = add_query_arg('redirect_to', ($rurl), zib_get_sign_url('oauth'));
        header('location:' . $url);
        exit;
    }

    //全新的第三方账号，由系统自动创建一个账号并绑定
    $login_name = 'user' . mt_rand(1000, 9999) . mt_rand(1000, 9999);
    $user_pass  = wp_create_nonce(rand(10, 1000));
    $user_id    = wp_create_user($login_name, $user_pass);
    if (is_wp_error($user_id)) {
        //新建用户出错
        $return_data['msg'] = $user_id->get_error_message();
    } else {
        //新建用户成功
        update_user_meta($user_id, 'oauth_new', $args['type']);
        /**标记为系统新建用户 */
        //更新用户mate
        $args['user_id']    = $user_id;
        $args['login_name'] = $login_name;
        zib_oauth_update_user_meta($args, true);

        //登录
        $user = get_user_by('id', $user_id);
        wp_set_current_user($user_id, $user->user_login);
        wp_set_auth_cookie($user_id, true);
        do_action('wp_login', $user->user_login, $user);
        // 准备返回数据
        $return_data['redirect_url'] = zib_get_user_center_url(); //重定向链接到用户中心
        $return_data['error']        = false;
    }

    return $return_data;
}

/**
 * @description: AJAX方式在登录前绑定社交账号
 * @param {*} $user_id
 * @return {*}
 */
function zib_ajax_oauth_bind($user_id)
{
    $goto = false;
    if (!empty($_REQUEST['oauth_bind'])) {
        @session_start();
        if (empty($_SESSION['zib_user_oauth_data']['openid'])) {
            wp_logout();
            zib_send_json_error('异常错误，请重试');
        }
        $oauth_data            = $_SESSION['zib_user_oauth_data'];
        $oauth_data['user_id'] = $user_id;

        //判断是否已经绑定过
        if (zib_get_user_oauth_openid($oauth_data['type'], $user_id)) {
            $type_name = zib_get_social_type_name($oauth_data['type']);
            wp_logout();
            zib_send_json_error('该账号已绑定过' . $type_name . '账号');
        }

        //绑定用户不更新以下数据
        $oauth_data['name']        = '';
        $oauth_data['description'] = '';

        zib_oauth_update_user_meta($oauth_data, false);
        $_SESSION['zib_user_oauth_data'] = null;
    }
    return $goto;
}

//获取用户手动绑定账号的tab内容
function zib_get_oauth_bind_tab($args)
{
    /** 需求数据明细 */
    $defaults = array(
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'avatar'      => '',
        'description' => '',
        'getUserInfo' => array(),
    );

    $args       = wp_parse_args($args, $defaults);
    $avatar_img = $args['avatar'] ? $args['avatar'] : zib_default_avatar();
    $type_name  = zib_get_social_type_data()[$args['type']]['name'];

    $oauth_box = '<div class="oauth-data-box">
                    <div class="muted-box">
                        <div class="user-info flex ac relative">
                            <span class="avatar-img mr6"><img alt="avatar-img" src="' . esc_attr($avatar_img) . '" class="avatar"></span>
                            <div class="flex1 ml10"><b>您好！' . esc_attr($args['name']) . '</b><div class="mt6 px12 muted-2-color">您首次使用此<b class="badg badg-sm c-blue">' . $type_name . '账号</b>登录<br>请先绑定已有账号或创建新账号</div></div>
                        </div>
                    </div>
                </div>';

    $tab      = 'signin';
    $tab_html = '';
    //登录
    $tab_html .= '<div class="tab-pane fade' . ('signin' == $tab ? ' active in' : '') . '" id="tab-sign-in">';
    $tab_html .= '<div class="box-body">';
    $tab_html .= '<div class="title-h-left em14">绑定账号</div>';
    $tab_html .= '<a class="muted-color px12" href="#tab-sign-up" data-toggle="tab">没有账号？创建新账号<i class="em12 ml3 fa fa-angle-right"></i></a>';
    $tab_html .= '</div>';
    $tab_html .= zib_signin_form($args['type']);
    $tab_html .= '</div>';

    //注册
    $tab_html .= '<div class="tab-pane fade' . ('signup' == $tab ? ' active in' : '') . '" id="tab-sign-up">';
    $tab_html .= '<div class="box-body">';
    $tab_html .= '<div class="title-h-left em14">创建新账号</div>';
    $tab_html .= '<a class="muted-color px12" href="#tab-sign-in" data-toggle="tab">已有账号，立即绑定<i class="em12 ml3 fa fa-angle-right"></i></a>';
    $tab_html .= '</div>';
    $tab_html .= zib_signup_form($args['type']);
    $tab_html .= '</div>';

    return $oauth_box . '<div class="tab-content">' . $tab_html . '</div>';
}

/**
 * @description: 判断是否是跳转到新页面
 * @param {*}
 * @return {*}
 */
function zib_oauth_new_is_to_page()
{
    //系统设置
    $oauth_bind_type = _pz('oauth_bind_type');
    if ($oauth_bind_type === 'page') {
        return true;
    }

    //邀请码注册
    $user_invit_code = _pz('invit_code_s', 'close');
    if ($user_invit_code && $user_invit_code !== 'close') {
        return true;
    }

    return false;
}

/**
 * @description: 获取用户已经绑定的社交登录的openid，可以用作判断函数
 * @param {*} $type
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_oauth_openid($type, $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    return get_user_meta($user_id, 'oauth_' . $type . '_openid', true);
}

function zib_oauth_update_user_meta($args, $is_new = false)
{
    /** 需求数据明细 */
    $defaults = array(
        'user_id'     => '',
        /**用户id */
        'type'        => '',
        'openid'      => '',
        'name'        => '',
        'login_name'  => '',
        'avatar'      => '',
        'description' => '',
        'getUserInfo' => array(),
    );
    $args        = wp_parse_args((array) $args, $defaults);
    $getUserInfo = $args['getUserInfo'];
    unset($args['getUserInfo']);
    $getUserInfo['name']   = $args['name'];
    $getUserInfo['avatar'] = $args['avatar'];

    update_user_meta($args['user_id'], 'oauth_' . $args['type'] . '_openid', $args['openid']);
    zib_update_user_meta($args['user_id'], 'oauth_' . $args['type'] . '_getUserInfo', $getUserInfo);

    //自定义头像，无则添加
    $custom_avatar = zib_get_user_meta($args['user_id'], 'custom_avatar', true);
    if ($args['avatar'] && !$custom_avatar) {
        zib_update_user_meta($args['user_id'], 'custom_avatar', $args['avatar']);
    }

    //自定义简介，无则添加
    $description = get_user_meta($args['user_id'], 'description', true);
    if ($args['description'] && !$description) {
        update_user_meta($args['user_id'], 'description', $args['description']);
    }

    if ($is_new) {
        //新建用户，更新display_name
        $nickname = trim($args['name']);
        if (zib_is_username_judgment($nickname)['error']) {
            //判断用户名是否合法
            $nickname = $args['login_name'] ? str_replace('user', '用户', $args['login_name']) : '用户' . mt_rand(1000, 9999) . mt_rand(1000, 9999);
        }

        $user_datas = array(
            'ID'           => $args['user_id'],
            'display_name' => $nickname,
            'nickname'     => $nickname,
        );
        wp_update_user($user_datas);
    }
}

//OAuth登录处理页路由
function zib_oauth_page_rewrite_rules($wp_rewrite)
{
    if ($ps = get_option('permalink_structure')) {
        $new_rules['oauth/([A-Za-z]+)$']          = 'index.php?oauth=$matches[1]';
        $new_rules['oauth/([A-Za-z]+)/callback$'] = 'index.php?oauth=$matches[1]&oauth_callback=1';
        $wp_rewrite->rules                        = $new_rules + $wp_rewrite->rules;
    }
}
add_action('generate_rewrite_rules', 'zib_oauth_page_rewrite_rules');

function zib_add_oauth_page_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'oauth'; // 添加参数白名单oauth，代表是各种OAuth登录处理页
        $public_query_vars[] = 'oauth_callback';
    }
    return $public_query_vars;
}
add_filter('query_vars', 'zib_add_oauth_page_query_vars');

function zib_oauth_page_template()
{
    $oauth          = strtolower(get_query_var('oauth')); //转换为小写
    $oauth_callback = get_query_var('oauth_callback');
    if ($oauth) {
        if (in_array($oauth, array('clogin', 'agent', 'gitee', 'giteeagent', 'alipay', 'alipayagent', 'baidu', 'baiduagent', 'qq', 'qqagent', 'weixin', 'weixinagent', 'weixingzh', 'weibo', 'weiboagent', 'github', 'githubagent'))):
            global $wp_query;
            $wp_query->is_home = false;
            $wp_query->is_page = false;
            $template_file     = $oauth_callback ? '/oauth/' . $oauth . '/callback.php' : '/oauth/' . $oauth . '/login.php';
            $template          = get_theme_file_path($template_file);

            load_template($template);
            exit;
        else:
            // 非法路由处理
            unset($oauth);
            return;
        endif;
    }
}
add_action('template_redirect', 'zib_oauth_page_template', 5);

/**
 * @description: 社交账号登录的错误页面
 * @param {*} $msg
 * @return {*}
 */
function zib_oauth_die($msg = '异常错误，请重试', $t = '')
{

    if (!$t) {
        $t = get_current_user_id() ? '绑定失败' : '登录失败';
    }

    $con = '<h4 class="c-red box-body separator mb30">' . $t . '</h4>';
    $con .= '<div  class="mb20 muted-box text-left" style=" max-width: 600px; margin: auto; ">' . $msg . '</div>';
    $args = array(
        'img'   => ZIB_TEMPLATE_DIRECTORY_URI . '/img/null-user.svg',
        'title' => $t . zib_get_delimiter_blog_name(),
    );

    zib_die_page($con, $args);
}

//代理登录执行函数
function zib_agent_login()
{
    //启用 session
    @session_start();
    header('Content-Type: application/json; charset=UTF-8');

    if (!empty($_REQUEST['agent_back_url']) && !empty($_REQUEST['sign'])) {
        require_once get_theme_file_path('/oauth/sdk/agent.php');

        $oauth_agent = _pz('oauth_agent', 'close');
        $config      = _pz('oauth_agent_server_option');
        if ($oauth_agent != 'server') {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '错误：未启用代理登录服务')));
            exit();
        }

        $agent_oauth = new \agent\OAuth2($config);
        if (!$agent_oauth->verifySign()) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '错误：代理登录签名错误或密钥错误')));
            exit();
        }
        $_SESSION['agent_back_url'] = $_REQUEST['agent_back_url'];
    } else {
        $_SESSION['agent_back_url'] = '';
    }
}

//代理登录回调执行函数
function zib_agent_callback($oauth_data)
{
    //启用 session
    @session_start();

    if (!empty($_SESSION['agent_back_url'])) {
        require_once get_theme_file_path('/oauth/sdk/agent.php');

        $config = _pz('oauth_agent_server_option');

        $agent_oauth                = new \agent\OAuth2($config);
        $back_url                   = $agent_oauth->getBackUrl($_SESSION['agent_back_url'], $oauth_data);
        $_SESSION['agent_back_url'] = '';
        header('location:' . $back_url);
        exit;
    }
}
