<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 23:52:09
 * @LastEditTime : 2026-04-11 15:38:59
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//免密登录
function zib_ajax_user_signin_nopas()
{
    if (is_user_logged_in()) {
        echo(json_encode(array('error' => 1, 'reload' => 1, 'msg' => '您已登录，请刷新页面')));
        exit;
    }

    $nopas_s = _pz('user_signin_nopas_s');
    if (!$nopas_s) {
        echo(json_encode(array('error' => 1, 'msg' => '暂未开启此登录方式')));
        exit;
    }

    $nopas_type = _pz('user_signin_nopas_type');

    $captcha      = zib_ajax_captcha_form_judgment($nopas_type);
    $captcha_type = $captcha['type'];
    $captcha_val  = $captcha['to'];

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_signin_captcha');

    //验证验证码
    zib_ajax_is_captcha($nopas_type);

    if ('email' == $captcha_type) {
        $user = get_user_by('email', $captcha_val);
        if (!$user) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到此邮箱注册账户')));
            exit();
        }
    } elseif ('phone' == $captcha_type) {
        $user = zib_get_user_by('phone', $captcha_val);
        if (!$user) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到此手机号注册账户')));
            exit();
        }
    }
    if (!$user) {
        echo(json_encode(array('error' => 1, 'msg' => '未找到您的用户信息')));
        exit();
    }

    //绑定第三方登录
    zib_ajax_oauth_bind($user->ID);

    //登录
    $remember = !empty($_POST['remember']) ? true : false;
    wp_set_current_user($user->ID, $user->user_login);
    wp_set_auth_cookie($user->ID, $remember);
    do_action('wp_login', $user->user_login, $user);

    $result = array('error' => 0, 'reload' => 1, 'msg' => '成功登录，页面跳转中');
    if (!empty($_REQUEST['redirect_to'])) {
        $result['goto'] = $_REQUEST['redirect_to'];
    }
    echo(json_encode($result));
    exit();
}
add_action('wp_ajax_user_signin_nopas', 'zib_ajax_user_signin_nopas');
add_action('wp_ajax_nopriv_user_signin_nopas', 'zib_ajax_user_signin_nopas');

/**用户注册 */
function zib_ajax_user_signup()
{
    if (is_user_logged_in()) {
        echo(json_encode(array('error' => 1, 'reload' => 1, 'msg' => '你已经登录，请刷新页面')));
        exit;
    }

    //用户名判断
    zib_ajax_username_judgment('name');

    if (strlen($_POST['password2']) < 6) {
        echo(json_encode(array('error' => 1, 'msg' => '密码太短,至少6位')));
        exit();
    }

    $no_repas = _pz('user_signup_no_repas');
    $captch   = _pz('user_signup_captch');

    if ((!$no_repas || !$captch) && $_POST['password2'] !== $_POST['repassword']) {
        echo(json_encode(array('error' => 1, 'msg' => '两次密码输入不一致')));
        exit();
    }

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_signup_captcha');
    $captcha_type = '';
    $captcha_val  = '';
    if ($captch) {
        //执行验证码验证参数判断
        $_pz_captch_type = _pz('captch_type', 'email');

        $captcha      = zib_ajax_captcha_form_judgment($_pz_captch_type);
        $captcha_type = $captcha['type'];
        $captcha_val  = $captcha['to'];

        //注册验证邮件合法性
        if ('email' == $captcha_type) {
            zib_ajax_email_judgment($captcha_val);
        }

        //防止重复
        if ('email' == $captcha_type && email_exists($captcha_val)) {
            echo(json_encode(array('error' => 1, 'msg' => '该邮箱已注册，请登录')));
            exit();
        }
        if ('phone' == $captcha_type && zib_get_user_by('phone', $captcha_val)) {
            echo(json_encode(array('error' => 1, 'msg' => '该手机号已注册，请登录')));
            exit();
        }

        //执行验证码：验证判断
        zib_ajax_is_captcha($_pz_captch_type);
    }

    //执行邀请码验证判断
    $invit_code_obj = zib_ajax_invit_code_verify();

    //判断结束，新建用户
    $email = 'email' == $captcha_type ? $captcha_val : (!empty($_POST['email']) ? $_POST['email'] : '');

    $sanitized_user_login = wp_slash(trim($_REQUEST['name']));
    $errors               = new WP_Error();
    $errors               = apply_filters('registration_errors', $errors, $sanitized_user_login, $email);

    if ($errors->has_errors()) {
        echo(json_encode(array('error' => 1, 'wp_error' => $errors, 'msg' => $errors->get_error_message())));
        exit();
    }

    $status = wp_create_user($sanitized_user_login, $_POST['password2'], $email);

    if (is_wp_error($status)) {
        echo(json_encode(array('error' => 1, 'wp_error' => $status, 'msg' => $status->get_error_message())));
        exit();
    } elseif (!$status) {
        echo(json_encode(array('error' => 1, 'status' => $status, 'msg' => '系统出错，请稍候再试')));
        exit();
    }

    //绑定第三方登录
    zib_ajax_oauth_bind($status);

    //执行使用邀请码
    if ($invit_code_obj) {
        zib_use_invit_code($status, $invit_code_obj);
    }

    //登录
    $user = get_user_by('id', $status);
    wp_set_current_user($status, $user->user_login);
    wp_set_auth_cookie($status, true);
    do_action('wp_login', $user->user_login, $user);

    //保存用户手机号
    if ('phone' == $captcha_type) {
        update_user_meta($status, 'phone_number', $captcha_val);
    }

    $result = array('error' => 0, 'reload' => 1, 'msg' => '注册成功，欢迎您：' . $sanitized_user_login);
    //重定向返回页面
    if (!empty($_REQUEST['redirect_to'])) {
        $result['goto'] = $_REQUEST['redirect_to'];
    }
    echo(json_encode($result));
    exit();
}
add_action('wp_ajax_user_signup', 'zib_ajax_user_signup');
add_action('wp_ajax_nopriv_user_signup', 'zib_ajax_user_signup');

/**用户登录 */
function zib_ajax_user_signin()
{

    if (is_user_logged_in()) {
        echo(json_encode(array('error' => 1, 'msg' => '你已经登录，请刷新页面')));
        exit;
    }

    if (empty($_POST['username'])) {
        echo(json_encode(array('error' => 1, 'msg' => '请输入登录账号')));
        exit();
    }

    if (empty($_POST['password'])) {
        echo(json_encode(array('error' => 1, 'msg' => '请输入密码')));
        exit();
    }

    if (_pz('verification_signin_exclude') !== $_POST['username']) {
        //执行人机验证
        zib_ajax_man_machine_verification('img_yz_signin');
    }

    if (filter_var($_POST['username'], FILTER_VALIDATE_EMAIL)) {
        $user_data = get_user_by('email', $_POST['username']);
        if (empty($user_data)) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到此邮箱注册账户')));
            exit();
        }
    } elseif (_pz('user_signin_phone_s') && ZibSMS::is_phonenumber($_POST['username'])) {
        $user_data = zib_get_user_by('phone', $_POST['username']);
        if (empty($user_data)) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到此手机号注册账户')));
            exit();
        }
    } else {
        //用户名判断
        zib_ajax_username_judgment('username', true);

        $user_data = get_user_by('login', $_POST['username']);
        if (empty($user_data)) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到此用户名注册账户')));
            exit();
        }
    }

    //错误频率限制
    $is_frequency_limit = zib_is_error_frequency_limit('signin');
    if ($is_frequency_limit['error']) {
        echo(json_encode(array('error' => 1, 'msg' => $is_frequency_limit['msg'])));
        exit();
    }

    $username   = $user_data->user_login;
    $remember   = !empty($_POST['remember']) ? true : false;
    $login_data = array(
        'user_login'    => $username,
        'user_password' => $_POST['password'],
        'remember'      => $remember,
    );

    $user_verify = wp_signon($login_data);

    if (is_wp_error($user_verify)) {
        $error_message = '账号或密码错误';
        if ($user_verify->get_error_code() === 'error_frequency_limit') {
            $error_message = $user_verify->get_error_message();
        }

        echo(json_encode(array('error' => 1, 'msg' => $error_message)));
        exit();
    }

    //绑定第三方登录
    zib_ajax_oauth_bind($user_verify->ID);

    //重置错误频率限制
    zib_reset_error_frequency_limit('signin');

    $result = array('error' => 0, 'reload' => 1, 'msg' => '成功登录，页面跳转中');
    if (!empty($_REQUEST['redirect_to'])) {
        $result['goto'] = $_REQUEST['redirect_to'];
    }
    echo(json_encode($result));
    exit();
}
add_action('wp_ajax_user_signin', 'zib_ajax_user_signin');
add_action('wp_ajax_nopriv_user_signin', 'zib_ajax_user_signin');

///AJAX注册发送验证码
function zib_ajax_signup_captcha()
{

    //判断不能重复
    $captcha = zib_ajax_captcha_form_judgment(_pz('captch_type', 'email'));

    $captcha_type = $captcha['type'];
    $to           = $captcha['to'];
    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_signup_captcha');

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //注册验证邮件合法性
    if ('email' == $captcha_type) {
        zib_ajax_email_judgment($to);
    }

    if ('email' == $captcha_type && email_exists($to)) {
        echo(json_encode(array('error' => 1, 'msg' => '该邮箱已注册，请登录')));
        exit();
    }
    if ('phone' == $captcha_type && zib_get_user_by('phone', $to)) {
        echo(json_encode(array('error' => 1, 'msg' => '该手机号已注册，请登录')));
        exit();
    }

    zib_ajax_send_captcha($captcha_type, $to, false);
    exit();
}
add_action('wp_ajax_signup_captcha', 'zib_ajax_signup_captcha');
add_action('wp_ajax_nopriv_signup_captcha', 'zib_ajax_signup_captcha');

/**前端登录AJAX发送验证码 */
function zib_ajax_signin_captcha()
{

    //判断不能重复
    $captcha      = zib_ajax_captcha_form_judgment(_pz('user_signin_nopas_type', 'email'));
    $captcha_type = $captcha['type'];
    $to           = $captcha['to'];

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_signin_captcha');

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if ('email' == $captcha_type && !email_exists($to)) {
        echo(json_encode(array('error' => 1, 'msg' => '该邮箱尚未注册或绑定账号')));
        exit();
    }
    if ('phone' == $captcha_type && !zib_get_user_by('phone', $to)) {
        echo(json_encode(array('error' => 1, 'msg' => '该手机号尚未注册或绑定账号')));
        exit();
    }

    zib_ajax_send_captcha($captcha_type, $to, false);
    exit();
}
add_action('wp_ajax_signin_captcha', 'zib_ajax_signin_captcha');
add_action('wp_ajax_nopriv_signin_captcha', 'zib_ajax_signin_captcha');

//找回密码发送验证码
function zib_ajax_resetpassword_captcha($captcha_type = '', $send = '')
{

    $captcha      = zib_ajax_captcha_form_judgment(_pz('user_repas_captch_type', 'email'));
    $captcha_type = $captcha['type'];
    $to           = $captcha['to'];

    //人机验证
    zib_ajax_man_machine_verification('img_yz_resetpassword_captcha');

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $current_user_id = get_current_user_id();
    if ($current_user_id) {
        //如果已经登录，则仅允许修改自己的账户
        if ('email' == $captcha_type) {
            $udata      = get_userdata($current_user_id);
            $user_email = $udata->user_email;
            if (!$user_email) {
                echo(json_encode(array('error' => 1, 'msg' => '您的账号暂未绑定邮箱')));
                exit();
            }
            if ($user_email != $to) {
                echo(json_encode(array('error' => 1, 'msg' => '您输入邮箱账号与您绑定的邮箱不一致')));
                exit();
            }
        }
        if ('phone' == $captcha_type) {
            $phone = get_user_meta($current_user_id, 'phone_number', true);
            if (!$phone) {
                echo(json_encode(array('error' => 1, 'msg' => '您的账号暂未绑定手机号')));
                exit();
            }

            if ($phone != $to) {
                echo(json_encode(array('error' => 1, 'msg' => '您输入手机号与您绑定的手机号不一致')));
                exit();
            }
        }
    }

    if ('email' == $captcha_type) {
        $user_data = get_user_by('email', $to);
        if (empty($user_data->ID)) {
            echo(json_encode(array('error' => 1, 'msg' => '该邮箱尚未注册或绑定账号')));
            exit();
        }
    }
    if ('phone' == $captcha_type) {
        $user_data = zib_get_user_by('phone', $to);
        if (empty($user_data->ID)) {
            echo(json_encode(array('error' => 1, 'msg' => '该手机号尚未注册或绑定账号')));
            exit();
        }
    }

    $allow = apply_filters('allow_password_reset', true, $user_data->ID);
    if (!$allow) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '此用户不允许重置密码')));
        exit();
    }

    zib_ajax_send_captcha($captcha_type, $to, false);
    exit();
}
add_action('wp_ajax_resetpassword_captcha', 'zib_ajax_resetpassword_captcha');
add_action('wp_ajax_nopriv_resetpassword_captcha', 'zib_ajax_resetpassword_captcha');

//找回密码
function zib_ajax_reset_password($captcha_type = '', $send = '')
{

    if (empty($_POST['repassword']) || empty($_POST['password2'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '密码不能为空')));
        exit();
    }

    if (strlen($_POST['repassword']) < 6) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '密码至少6位')));
        exit();
    }

    if ($_POST['repassword'] !== $_POST['password2']) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '两次密码输入不一致')));
        exit();
    }

    $nopas_type = _pz('user_repas_captch_type', 'email');

    //获取输入内容
    $captcha      = zib_ajax_captcha_form_judgment($nopas_type);
    $captcha_type = $captcha['type'];
    $captcha_val  = $captcha['to'];

    //人机验证
    zib_ajax_man_machine_verification('img_yz_resetpassword_captcha');

    //验证验证码
    zib_ajax_is_captcha($nopas_type);

    if ('email' == $captcha_type) {
        $user_data = get_user_by('email', $captcha_val);
    }
    if ('phone' == $captcha_type) {
        $user_data = zib_get_user_by('phone', $captcha_val);
    }

    $current_user_id = get_current_user_id();
    if ($current_user_id && (!$user_data || $current_user_id != $user_data->ID)) {
        //如果已经登录，则仅允许修改自己的账户
        $captcha_type_name = array(
            'email' => '邮箱账号',
            'phone' => '手机号',
        );
        echo(json_encode(array('error' => 1, 'msg' => '您的' . $captcha_type_name[$captcha_type] . '输入错误')));
        exit();
    }

    if (!$user_data) {
        echo(json_encode(array('error' => 1, 'msg' => '未查询到您的账号信息')));
        exit();
    }

    //修改密码
    $status = wp_update_user(
        array(
            'ID'        => $user_data->ID,
            'user_pass' => $_POST['password2'],
        )
    );

    if (is_wp_error($status)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', $status->get_error_message())));
        exit();
    }

    if (!$current_user_id) {
        wp_set_current_user($user_data->ID, $user_data->user_login);
        wp_set_auth_cookie($user_data->ID, true);
        do_action('wp_login', $user_data->user_login, $user_data);
    }

    echo(json_encode(array('error' => 0, 'reload' => 1, 'goto' => esc_url(home_url()), 'msg' => '密码重设成功！请牢记新密码')));
    exit;
}
add_action('wp_ajax_reset_password', 'zib_ajax_reset_password');
add_action('wp_ajax_nopriv_reset_password', 'zib_ajax_reset_password');
