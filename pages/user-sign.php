<?php

/**
 * Template name: Zibll-登录/注册/找回密码
 * Description:   找回密码页面
 */

@session_start();
add_filter('echo_seo_title', '__return_true');
global $new_title, $new_description;
$redirect_to = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url();
$tab         = !empty($_GET['tab']) ? $_GET['tab'] : '';
$interim     = isset($_REQUEST['interim-login']);

$new_title_array = array(
    'signin'        => '用户登录',
    'signup'        => '新用户注册',
    'resetpassword' => '找回密码',
    'bind'          => '绑定账号',
    'oauth'         => '绑定账号',
);
$new_title = isset($new_title_array[$tab]) ? $new_title_array[$tab] : '登录、注册';
$new_title .= zib_get_delimiter_blog_name();
$new_description = $new_title;

//如果已经登录则退回
$user_bind_tab = false;
$user_id       = get_current_user_id();
if ($user_id) {
    //兼容后台临时登录
    if ($interim && is_super_admin()) {
        echo '<!DOCTYPE HTML><html><body class="login interim-login-success"><div style="padding: 60px 0;text-align: center;color: #1a88fd;">登录成功</div></body></html>';
        exit;
    } elseif ('bind' != $tab) {
        wp_safe_redirect($redirect_to);
        exit;
    }
    if ('bind' == $tab) {
        //强制绑定
        $bind_type     = zib_get_user_bind_type();
        $user_bind_tab = zib_get_user_bind_tab($bind_type, _pz('user_bind_option', '', 'mandatory_bind_text'));
        if (!$user_bind_tab) {
            wp_safe_redirect($redirect_to);
            exit;
        }
    }
} elseif ('bind' == $tab || ('oauth' == $tab && empty($_SESSION['zib_user_oauth_data']['openid']))) {
    //如果是绑定，但是未登录则返回登录页
    wp_safe_redirect(add_query_arg('tab', 'signin'));
    exit;
}

//社交账号登录绑定用户
$oauth_bind_html = '';
if ('oauth' === $tab && !empty($_SESSION['zib_user_oauth_data']['openid'])) {
    $oauth_bind_html = zib_get_oauth_bind_tab($_SESSION['zib_user_oauth_data']);
}

$background_html = '';
$background      = _pz('user_sign_page_option', 0, 'background');
if ($background) {
    $background_array = explode(',', $background);
    $rand             = array_rand($background_array, 1);
    $background_url   = wp_get_attachment_url($background_array[$rand]);

    if (zib_is_lazy('lazy_other', true)) {
        $background_html = '<div class="fixed" style=\'background:url(' . $background_url . ');background-repeat: no-repeat;background-size: cover;background-position: center;\'></div>';
    } else {
        $background_html = '<div class="fixed lazyload" data-bg="' . $background_url . '" style=\'background-repeat: no-repeat;background-size: cover;background-position: center;\'></div>';
    }
}

//卡片位置
$card_position  = _pz('user_sign_page_option', 'right', 'card_position');
$card_col_class = ' col-md-offset-6';
if ('left' == $card_position) {
    $card_col_class = '';
}
if ('center' == $card_position) {
    $card_col_class = ' col-md-offset-3';
}

//不显示移动端底部bar
remove_action('wp_footer', 'zib_footer_tabbar');
//不显示悬浮按钮
remove_action('wp_footer', 'zib_float_right');

//自定义主题模式
$card_position = _pz('user_sign_page_option', 'no', 'theme_mode');
if (in_array($card_position, array('white-theme', 'dark-theme'))) {
    add_filter('zib_theme_mode', function () {
        return _pz('user_sign_page_option', 'no', 'theme_mode');
    });
    add_filter('zib_theme_mode_button_positions', '__return_false');
}

?>

<!DOCTYPE HTML>
<html <?php echo 'lang="' . esc_attr(get_bloginfo('language')) . '"'; ?>>

<head>
    <meta charset="UTF-8">
    <link rel="dns-prefetch" href="//apps.bdimg.com">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=0.0">
    <meta http-equiv="Cache-Control" content="no-transform" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <meta name="robots" content="noindex, nofollow">
    <?php wp_head();?>
    <style>
        .page-template-user-sign {
            min-height: 500px;
        }

        .sign-page {
            min-height: 500px;
            padding-top: 70px;
        }

        .sign-row {
            height: 100%;
        }

        .sign-page .sign {
            width: 350px;
        }

        .sign-col {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .sign-col [data-dismiss="modal"] {
            display: none;
        }

        .oauth-data-box .avatar-img {
            --this-size: 60px;
        }
    </style>
</head>

<body <?php body_class(_bodyclass());?>>
    <?php
echo $background_html;
echo qj_dh_nr();

// file_put_contents(__DIR__ . '/error.json', json_encode($_SESSION));
if (_pz('user_sign_page_option', 0, 'show_header') && !$interim) {
    zib_header();
}
?>
    <main role="main" class="container sign-page absolute">
        <div class="row sign-row gutters-5">
            <div class="col-md-6<?php echo $card_col_class; ?> sign-col">
                <div style="padding:20px 0 60px 0">
                    <div class="sign zib-widget blur-bg relative">
                        <?php
if (_pz('user_sign_page_option', 0, 'show_logo')) {
    echo zib_get_sign_logo(home_url());
}
if ($oauth_bind_html) {
    echo $oauth_bind_html;
} elseif ($user_bind_tab) {
    //强制绑定
    $logout_url  = wp_logout_url(home_url());
    $oauth_new   = get_user_meta($user_id, 'oauth_new', true);
    $logout_url  = $oauth_new ? wp_nonce_url($logout_url, 'del-out', '_delout') : $logout_url;
    $logout_text = $oauth_new ? ' 注销账户' : ' 退出登录';
    $bind_html   = '';
    $bind_html .= '<div class="box-body">';
    $bind_html .= $user_bind_tab;
    $bind_html .= '</div>';
    $bind_html .= '<div class="box-body notop"><a class="but c-red padding-lg btn-block " href="' . $logout_url . '">' . zib_get_svg('signout') . $logout_text . ' </a></div>';
    echo $bind_html;
} else {
    if ($tab === 'signup' && zib_is_close_signup()) {
        $tab = 'signin';
    }
    zib_user_signtab_content($tab, true);
}
?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="notyn"></div>
    <?php
$footer = _pz('user_sign_page_option', 0, 'footer');
if ($footer && !$interim) {
    echo '<div class="text-center blur-bg fixed px12 opacity8" style="top: auto; height: auto;padding: 10px; ">' . $footer . '</div>';
}
wp_footer();
?>
</body>

</html>

<?php
