<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2026-04-29 20:56:02
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取登录、注册页面路由
 * @param {*} $tab signin|signup|resetpassword
 * @return {*}
 */
function zib_get_sign_url($tab = 'signin')
{
    $url = zib_get_template_page_url('pages/user-sign.php');
    return add_query_arg('tab', $tab, $url);
}

function zib_get_repas_link($is_link = false, $class = 'muted-2-color', $text = '找回密码')
{
    if (is_page_template('pages/user-sign.php') && !$is_link) {
        return '<a class="' . $class . '"  href="#tab-resetpassword" data-toggle="tab">' . $text . '</a>';
    } else {
        $url = add_query_arg('redirect_to', urlencode(zib_get_current_url()), zib_get_sign_url('resetpassword'));
        return '<a rel="nofollow" class="' . $class . '" href="' . $url . '">' . $text . '</a>';
    }
}

function zib_get_sign_logo($url = false)
{
    $logo_html = '';
    $atl       = _pz('hometitle') ? _pz('hometitle') : get_bloginfo('name') . (get_bloginfo('description') ? _get_delimiter() . get_bloginfo('description') : '');

    $logo_img = zib_get_adaptive_theme_img(_pz('user_card_option', 0, 'user_logo'), _pz('user_card_option', 0, 'user_logo_dark'), $atl, 'class="lazyload"', zib_is_lazy('lazy_other', true));
    if (!$logo_img) {
        return;
    }

    if ($url) {
        $logo_img = '<a href="' . esc_url($url) . '">' . $logo_img . '</a>';
    }
    $logo_html .= '<div class="text-center"><div class="sign-logo box-body">';
    $logo_html .= $logo_img;
    $logo_html .= '</div></div>';
    return $logo_html;
}

/**登录 */
add_action('wp_footer', 'zib_sign_modal');
function zib_sign_modal()
{
    if (zib_is_close_sign(true) || is_user_logged_in()) {
        return;
    }

    $background     = _pz('user_modal_option', 0, 'background');
    $background_url = '';

    if ($background) {
        $background_array = explode(',', $background);
        $rand             = array_rand($background_array, 1);
        $background_url   = wp_get_attachment_url($background_array[$rand]);
    }

    $background_html = '';

    if ($background_url) {
        $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="fit-cover radius8 lazyload" src="' . zib_get_lazy_thumb('lg') . '" data-' : 'class="fit-cover radius8"';

        $atl = _pz('hometitle') ? _pz('hometitle') : get_bloginfo('name') . (get_bloginfo('description') ? _get_delimiter() . get_bloginfo('description') : '');
        $background_html .= '<div class="sign-img absolute hide-sm">';
        $background_html .= '<img ' . $lazy_attr . 'src="' . $background_url . '" alt="' . $atl . '">';
        $background_html .= '</div>';
    }
    $logo_html = '';
    if (_pz('user_modal_option', 0, 'show_logo')) {
        $logo_html .= zib_get_sign_logo();
    }

    ?>
    <div class="modal fade" id="u_sign" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="sign-content">
                <?php echo $background_html; ?>
                <div class="sign zib-widget blur-bg relative">
                    <button class="close" data-dismiss="modal">
                        <?php echo zib_get_svg('close', '0 0 1024 1024', 'ic-close'); ?>
                    </button>
                    <?php echo $logo_html; ?>
                    <?php echo zib_user_signtab_content(); ?>
                </div>
            </div>
        </div>
    </div>
<?php
}

function zib_user_signtab_content($tab = 'signin', $is_page = false, $echo = true)
{
    $tab_html    = '';
    $tab         = in_array($tab, array('signin', 'signup', 'resetpassword')) ? $tab : 'signin';
    $signup_form = zib_signup_form();
    $signin_form = zib_signin_form();

    //登录
    if ($signin_form) {
        $tab_html .= '<div class="tab-pane fade' . ('signin' === $tab ? ' active in' : '') . '" id="tab-sign-in">';
        $tab_html .= '<div class="box-body">';
        $tab_html .= '<div class="title-h-left fa-2x">登录</div>';
        $tab_html .= $signup_form ? '<a class="muted-color px12" href="#tab-sign-up" data-toggle="tab">没有账号？立即注册<i class="em12 ml3 fa fa-angle-right"></i></a>' : '';
        $tab_html .= '</div>';
        $tab_html .= $signin_form;
        $tab_html .= '</div>';
    }

    //注册
    if ($signup_form) {
        $tab_html .= '<div class="tab-pane fade' . ('signup' === $tab ? ' active in' : '') . '" id="tab-sign-up">';
        $tab_html .= '<div class="box-body">';
        $tab_html .= '<div class="title-h-left fa-2x">注册</div>';
        $tab_html .= $signin_form ? '<a class="muted-color px12" href="#tab-sign-in" data-toggle="tab">已有账号，立即登录<i class="em12 ml3 fa fa-angle-right"></i></a>' : '';
        $tab_html .= '</div>';
        $tab_html .= $signup_form;
        $tab_html .= '</div>';
    }

    if ($is_page) {
        //找回密码
        $tab_html .= '<div class="tab-pane fade' . ('resetpassword' === $tab ? ' active in' : '') . '" id="tab-resetpassword">';
        $tab_html .= '<div class="box-body">';
        $tab_html .= '<div class="title-h-left fa-2x">找回密码</div>';
        $tab_html .= '<a class="muted-color px12" href="#tab-sign-in" data-toggle="tab">登录</a><i class="icon-spot"></i><a class="muted-color px12" href="#tab-sign-up" data-toggle="tab">注册</a>';
        $tab_html .= '</div>';
        $tab_html .= zib_resetpassword_form();
        $tab_html .= '</div>';
    }

    //扫码登录
    if (zib_is_oauth_qrcode_s()) {
        $tab_html .= '<div class="tab-pane fade' . ('qrcode_signin' === $tab ? ' active in' : '') . '" id="tab-qrcode-signin">';
        $tab_html .= '<div class="box-body">';
        $tab_html .= '<div class="title-h-left fa-2x">扫码登录</div>';
        $tab_html .= '<span class="muted-3-color px12">使用<a class="muted-color" href="#tab-sign-in" data-toggle="tab">其它方式登录</a>' . ($signup_form ? '或<a class="muted-color" href="#tab-sign-up" data-toggle="tab">注册</a>' : '') . '</span>';
        $tab_html .= '<a class="muted-color px12 hide" href="#tab-qrcode-signin" data-toggle="tab">扫码登录</a>';
        $tab_html .= '</div>';
        $tab_html .= '<div class="qrcode-signin-container box-body text-center">';
        $tab_html .= '<p class="placeholder" style="height:180px;width:180px;margin:auto;"></p><p class="placeholder" style="height:27px;width:200px;margin:15px auto 0;"></p>';
        $tab_html .= '</div>';
        $agreement = zib_get_user_agreement('扫码登录即表示同意');
        if ($agreement) {
            $tab_html .= '<div class="muted-color mt10 text-center px12 opacity8">' . $agreement . '</div>';
        }
        $tab_html .= '</div>';
    }

    if ($echo) {
        echo '<div class="tab-content">' . $tab_html . '</div>';
    } else {
        return '<div class="tab-content">' . $tab_html . '</div>';
    }
}

/**
 * @description: 获取一个放置的from中的验证码input，包含 滑动验证码、腾讯云人机验证、图形验证码
 * @param {*}
 * @return {*}
 */
function zib_get_machine_verification_input($id = 'img_verification', $name = 'canvas_yz')
{
    $yz    = _pz('user_verification_type', 'slider');
    $input = '';

    if ('tcaptcha' === $yz) {
        $option = _pz('tcaptcha_option');
        if (!empty($option['api_secret_id']) && !empty($option['api_secret_key']) && !empty($option['appid']) && !empty($option['secret_key'])) {
            $input = '<input machine-verification="tcaptcha" type="hidden" name="captcha_mode" value="tcaptcha" tcaptcha-id="' . $option['appid'] . '">';
        }
    }

    if ('geetest' === $yz) {
        $option = _pz('geetest_option');
        if (!empty($option['id']) && !empty($option['key'])) {
            $input = '<input machine-verification="geetest" type="hidden" name="captcha_mode" value="geetest" geetest-id="' . $option['id'] . '">';
        }
    }

    if ('slider' === $yz) {
        $input = '<input machine-verification="slider" type="hidden" name="captcha_mode" value="slider" slider-id="">';
    }

    if ('image' === $yz) {
        $input = zib_get_img_verification_input($id, $name);
    }

    return $input;
}

/**
 * @description: 获取图形验证码input
 * @param {*} $id
 * @param {*} $name
 * @return {*}
 */
function zib_get_img_verification_input($id = 'img_verification', $name = 'canvas_yz')
{
    $yz = _pz('user_verification_type', 'slider');

    if ('image' != $yz) {
        return;
    }

    $input = '<div class="relative line-form mb10">';
    $input .= '<input machine-verification="image" type="text" name="' . $name . '" class="line-form-input" canvas-id="' . $id . '" autocomplete="off" tabindex="5">';
    $input .= '<div class="scale-placeholder">图形验证码</div>';
    $input .= '<span class="yztx abs-right pointer imagecaptcha" imagecaptcha-id="' . $id . '" data-toggle="tooltip" title="点击刷新"></span>';
    $input .= '<div class="abs-right match-ok muted-color"></div><i class="line-form-line"></i>';
    $input .= '</div>';
    return $input;
}

//找回密码的form
function zib_resetpassword_form($type = '')
{
    $html  = '';
    $input = '';

    //用户名
    $captcha_type = _pz('user_repas_captch_type', 'email');
    $input .= zib_get_sign_captch($captcha_type, 'resetpassword_captcha');

    $input .= '<div class="relative line-form mb10">';
    $input .= '<input type="password" name="password2" class="line-form-input" tabindex="3" placeholder="">';
    $input .= '<div class="scale-placeholder">设置新密码</div>';
    $input .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
    $input .= '</div>';

    $input .= '<div class="relative line-form mb10">';
    $input .= '<input type="password" name="repassword" class="line-form-input" tabindex="4" placeholder="">';
    $input .= '<div class="scale-placeholder">重复密码</div>';
    $input .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
    $input .= '</div>';
    //按钮
    $input .= '<div class="box-body">';
    $input .= '<input type="hidden" name="action" value="reset_password">';
    $input .= '<input type="hidden" name="repeat" value="1">';
    $input .= '<button type="button" class="but radius jb-green padding-lg signsubmit-loader btn-block">确认提交</button>';

    $input .= '</div>';

    $html = '<form id="sign-up">' . $input . '</form>';

    return $html;
}

/**
 * @description: 判断是否关闭了用户登录注册功能
 * @param {*} $but
 * @return {*}
 */
function zib_is_close_sign($but = false)
{
    if (is_super_admin()) {
        return false;
    }

    if (_pz('close_sign', false)) {
        return true;
    }

    if ($but && is_page_template('pages/user-sign.php')) {
        return true;
    }

    return false;
}

/**
 * @description: 判断是否关闭了新用户账号密码注册功能
 * @param {*}
 * @return {*}
 */
function zib_is_close_signup()
{
    return apply_filters('is_close_signup', (_pz('close_signup') || zib_is_close_signin()));
}

/**
 * @description: 判断是否关闭了账号密码登录功能
 * @param {*}
 * @return {*}
 */
function zib_is_close_signin()
{
    return apply_filters('is_close_signin', _pz('close_signin'));
}

/**
 * @description: 构建注册form
 * @param {*} $oauth_bind 是否是社交登录绑定注册
 * @return {*}
 */
function zib_signup_form($oauth_bind = false)
{

    if (zib_is_close_signup() && !$oauth_bind) {
        return '';
    }

    $hidden_action = '';
    if ($oauth_bind) {
        $hidden_action .= '<input type="hidden" name="oauth_bind" value="' . $oauth_bind . '">';
    }

    $html       = '';
    $input      = '';
    $captch     = _pz('user_signup_captch'); //注册验证
    $invit_code = _pz('invit_code_s'); //邀请码注册

    //用户名
    $input .= '<div class="relative line-form mb10">';
    $input .= '<input type="text" name="name" class="line-form-input" tabindex="1" placeholder=""><i class="line-form-line"></i>';
    $input .= '<div class="scale-placeholder">设置用户名</div>';
    $input .= '</div>';

    if ($captch) {
        $captcha_type = _pz('captch_type', 'email');
        $input .= zib_get_sign_captch($captcha_type);
    }

    //密码
    $input .= '<div class="relative line-form mb10">';
    $input .= '<input type="password" name="password2" class="line-form-input" tabindex="3" placeholder="">';
    $input .= '<div class="scale-placeholder">设置密码</div>';
    $input .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
    $input .= '</div>';

    //重复密码
    if (!$captch || !_pz('user_signup_no_repas')) {
        $input .= '<div class="relative line-form mb10">';
        $input .= '<input type="password" name="repassword" class="line-form-input" tabindex="4" placeholder="">';
        $input .= '<div class="scale-placeholder">重复密码</div>';
        $input .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
        $input .= '</div>';
    }

    //人机验证
    if (!$captch) {
        $input .= zib_get_machine_verification_input('img_yz_signup_captcha');
    }

    //选填邀请码
    if ($invit_code === 'open') {
        $invit_code_desc      = _pz('invit_code_open_desc');
        $invit_code_desc_attr = $invit_code_desc ? ' data-toggle="tooltip" title="' . esc_attr($invit_code_desc) . '"' : '';
        $input .= '<div class="relative line-form mb10">';
        $input .= '<input type="text" name="invit_code" class="line-form-input" tabindex="0" placeholder="">';
        $input .= '<div class="scale-placeholder">邀请码(选填)</div>';
        $input .= '<div class="abs-right muted-2-color"' . $invit_code_desc_attr . '><i class="fa-fw fa fa-question-circle"></i></div><i class="line-form-line"></i>';
        $input .= '</div>';
    }

    //提交按钮
    $input .= '<div class="box-body">';
    $input .= '<input type="hidden" name="action" value="user_signup">';
    $input .= '<button type="button" class="but radius jb-green padding-lg signsubmit-loader btn-block">' . zib_get_svg('signup', '0 0 1024 1024', 'icon mr10') . ($oauth_bind ? '绑定' : '') . '注册</button>';

    $agreement = zib_get_user_agreement('注册即表示同意');
    if ($agreement) {
        if (_pz('user_agreement_check')) {
            $input .= zib_get_agreement_input('已阅读并同意', 'muted-color mt10 text-center px12 opacity8', false);
        } else {
            $input .= '<div class="muted-color mt10 text-center px12 opacity8">' . $agreement . '</div>';
        }
    }
    $input .= '</div>';
    $input .= $hidden_action;
    $html = '<form id="sign-up">' . $input . '</form>';

    //必须输入邀请码，才能注册
    if ($invit_code === 'must') {
        $invit_code_desc = _pz('invit_code_must_desc') ?: '请输入邀请码';

        $invit_code_html = '<div class="hide"><a class="hide" href="#tab-signup-signup" data-toggle="tab"></a></div>';
        $invit_code_html .= '<div class="tab-pane fade active in" id="tab-signup-invit">';
        $invit_code_html .= '<div class="mb10 flex jc" style="min-height:72px;"><div class="text-center">' . $invit_code_desc . '</div></div>';
        $invit_code_html .= zib_get_user_invit_code_must_verify_from();
        $invit_code_html .= '</div>';
        $html = '<div class="tab-content" id="sign-up">' . $invit_code_html . '<div class="tab-pane fade" id="tab-signup-signup"><form>' . $input . '</form></div></div>';
    }

    return $html;
}

/**
 * @description: 邀请码验证的from
 * @param {*}
 * @return {*}
 */
function zib_get_user_invit_code_must_verify_from()
{
    $action = 'invit_code_must_verify';

    $input = '';
    $input .= '<div class="mb20">';
    $input .= zib_get_machine_verification_input($action);

    $input .= '<div class="relative line-form mb10">';
    $input .= '<input type="text" name="invit_code" class="line-form-input" autocomplete="off" tabindex="1" placeholder=""><i class="line-form-line"></i>';
    $input .= '<div class="scale-placeholder">请填写邀请码</div>';
    $input .= wp_nonce_field($action, '_wpnonce', false, false); //安全效验
    $input .= '</div>';
    $input .= '</div>';

    $input .= '<input type="hidden" name="action" value="' . $action . '">';
    $input .= '<div class="box-body"><button type="button" class="but radius jb-blue padding-lg btn-block wp-ajax-submit invit-code-verify" next-tab="tab-signup-signup" tabindex="2">继续<i style="margin: 0 0 0 .5em;" class="em12 fa fa-angle-right"></i></button></div>';

    $form = '<form>' . $input . '</form>';

    return $form;
}

//登录form
function zib_signin_form($oauth_bind = false)
{

    $html  = '';
    $input = '';
    //首先判断未禁用账号密码登录功能
    if (!zib_is_close_signin() || $oauth_bind) {

        $phone_s           = _pz('user_signin_phone_s');
        $nopas_s           = _pz('user_signin_nopas_s');
        $nopas_active_type = _pz('user_signin_nopas_active', 'nopas'); //
        $hidden_action     = '';

        if ($oauth_bind) {
            $hidden_action .= '<input type="hidden" name="oauth_bind" value="' . $oauth_bind . '">';
        }
        if (!$nopas_s || $nopas_active_type !== 'only_nopas') {
            //用户名
            $name_placeholder = $phone_s ? '用户名/手机号/邮箱' : '用户名或邮箱';
            $input .= '<div class="relative line-form mb10">';
            $input .= '<input type="text" name="username" class="line-form-input" tabindex="1" placeholder=""><i class="line-form-line"></i>';
            $input .= '<div class="scale-placeholder">' . $name_placeholder . '</div>';
            $input .= '</div>';

            //密码
            $input .= '<div class="relative line-form mb10">';
            $input .= '<input type="password" name="password" class="line-form-input" tabindex="2" placeholder="">';
            $input .= '<div class="scale-placeholder">登录密码</div>';
            $input .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
            $input .= '</div>';

            //人机验证
            $input .= zib_get_machine_verification_input('img_yz_signin');

            //记住登录
            $input .= '<div class="relative line-form mb10 em09">';

            $input .= '<span class="muted-color form-checkbox"><input type="checkbox" id="remember" checked="checked" tabindex="4" name="remember" value="forever"><label for="remember" class="ml3">记住登录</label></span>';

            //找回密码
            $input .= '<span class="pull-right muted-2-color">';
            $input .= zib_get_repas_link($oauth_bind);
            //免密登录
            if ($nopas_s) {
                $input .= '<span class="opacity5"> | </span><a class="muted-2-color" data-toggle="tab" href="#tab-signin-nopas">免密登录</a> ';
            }
            $input .= '</span>';
            $input .= '</div>';

            //登录按钮
            $input .= '<div class="box-body">';
            $input .= '<input type="hidden" name="action" value="user_signin">';
            $input .= '<button type="button" class="but radius jb-blue padding-lg signsubmit-loader btn-block"><i class="fa fa-sign-in mr10"></i>' . ($oauth_bind ? '绑定' : '') . '登录</button>';
            $input .= '</div>';
            $input = '<form>' . $input . $hidden_action . '</form>';
        }

        //一键登录-免密登录-验证码登录
        if ($nopas_s) {
            $nopas_input = '';

            $nopas_type = _pz('user_signin_nopas_type', 'email');
            $nopas_input .= zib_get_sign_captch($nopas_type, 'signin_captcha');
            $nopas_input .= '<div class="relative line-form mb10 em09">';
            $nopas_input .= '<span class="muted-color form-checkbox"><input type="checkbox" id="remember2" checked="checked" tabindex="4" name="remember" value="forever"><label for="remember2" class="ml3">记住登录</label></span>';

            //找回密码
            $nopas_input .= '<span class="pull-right muted-2-color">';
            //免密登录
            $nopas_input .= $nopas_active_type !== 'only_nopas' ? '<a class="muted-2-color" data-toggle="tab" href="#tab-signin-pas">账号密码登录</a> ' : '';
            $nopas_input .= '</span>';
            $nopas_input .= '</div>';

            //登录按钮
            $nopas_input .= '<div class="box-body">';
            $nopas_input .= '<input type="hidden" name="action" value="user_signin_nopas">';
            $nopas_input .= '<button type="button" class="but radius jb-blue padding-lg signsubmit-loader btn-block"><i class="fa fa-sign-in mr10"></i>' . ($oauth_bind ? '绑定' : '') . '登录</button>';
            $nopas_input .= '</div>';

            $nopas_input = '<form>' . $nopas_input . $hidden_action . '</form>';

            //默认显示免密登录还是账号密码登录
            $nopas_active = $nopas_active_type !== 'pas';
            $html .= '<div class="tab-content">';
            $html .= '<div class="tab-pane fade' . ($nopas_active ? ' active in' : '') . '" id="tab-signin-nopas">' . $nopas_input . '</div>';
            $html .= '<div class="tab-pane fade' . ($nopas_active ? '' : ' active in') . '" id="tab-signin-pas">' . $input . '</div>';
            $html .= '</div>';
        } else {
            $html .= $input;
        }
    }

    //社交登录
    if (!$oauth_bind) {
        $social_login = zib_social_login(false);
        if ($social_login) {
            $html .= '<p class="social-separator separator muted-3-color em09">社交账号登录</p>';
            $html .= '<div class="social_loginbar">';
            $html .= $social_login;
            $html .= '</div>';
            $agreement = zib_get_user_agreement('使用社交账号登录即表示同意');
            if ($agreement) {
                $html .= '<div class="muted-color mt10 text-center px12 opacity8">' . $agreement . '</div>';
            }
        }
    }

    $html = '<div id="sign-in">' . $html . '</div>';

    return $html;
}

//获取验证登录的input
function zib_get_sign_captch($captcha_type = 'email', $action = 'signup_captcha')
{

    $input_placeholder = '邮箱';
    if ('phone' == $captcha_type) {
        $input_placeholder = '手机号';
    } elseif ('email_phone' == $captcha_type) {
        $input_placeholder = '手机号或邮箱';
    }
    $input = '';
    $input .= '<div class="relative line-form mb10">';
    $input .= '<input change-show=".change-show" type="text" name="' . esc_attr($captcha_type) . '" class="line-form-input" tabindex="1" placeholder=""><i class="line-form-line"></i>';
    $input .= '<div class="scale-placeholder">' . $input_placeholder . '</div>';
    $input .= '</div>';
    $input .= zib_get_machine_verification_input('img_yz_' . $action);

    $input .= '<div class="relative line-form mb10 change-show">';
    $input .= '<input type="text" name="captch" class="line-form-input" autocomplete="off" tabindex="2" placeholder=""><i class="line-form-line"></i>';
    $input .= '<div class="scale-placeholder">验证码</div>';
    $input .= '<span class="yztx abs-right"><button type="button" form-action="' . $action . '" class="but c-blue captchsubmit">发送验证码</button></span>';
    $input .= '<div class="abs-right match-ok muted-color"><i class="fa-fw fa fa-check-circle"></i></div>';
    $input .= '<input type="hidden" name="captcha_type" value="' . $captcha_type . '">';
    $input .= wp_nonce_field($action, '_wpnonce', false, false); //安全效验
    $input .= '</div>';
    return $input;
}

/**退出登录 */
add_action('wp_footer', 'zib_signout_modal');
function zib_signout_modal()
{
    if (!is_user_logged_in()) {
        return;
    }

    global $current_user;

    $args = array(
        'id'              => 'modal_signout',
        'class'           => 'modal-sm',
        'colorful_header' => true,
        'header_class'    => 'jb-yellow',
        'header_icon'     => zib_get_svg('signout'),
        'title'           => '退出登录',
        'content'         => '<div class="ml10">
            <h4>您好！ ' . $current_user->display_name . '</h4><p class="c-red">确认要退出当前登录吗？</p></div>',
        'buttons_align'   => 'average', //left/centent/right/average
        'buttons'         => array(
            array(
                'attr'  => 'data-dismiss="modal"',
                'class' => '',
                'link'  => array(
                    'text' => '取消',
                ),
            ),
            array(
                'class' => 'c-red',
                'link'  => array(
                    'url'  => wp_logout_url(home_url()),
                    'text' => '确认退出',
                ),
            ),
        ),
    );

    zib_modal($args);
}

/**
 * @description: 获取用户协议和隐私声明的文字
 * @param {*} $before
 * @param {*} $glue
 * @return {*}
 */
function zib_get_user_agreement($before = '', $glue = '、')
{
    $user_agreement_s = _pz('user_agreement_s');
    $agreement_args   = array();
    if ($user_agreement_s) {
        $agreement_args[] = '<a class="focus-color" target="_blank" href="' . get_permalink(_pz('user_agreement_page')) . '">用户协议</a>';
    }

    $user_privacy_s = _pz('user_privacy_s');
    if ($user_privacy_s) {
        $agreement_args[] = '<a class="focus-color" target="_blank" href="' . get_permalink(_pz('user_privacy_page')) . '">隐私声明</a>';
    }

    if (!$agreement_args) {
        return;
    }

    return $before . implode($glue, $agreement_args);
}

/**
 * @description: 获取同意用户协议的input
 * @param {*} $before
 * @param {*} $checked
 * @return {*}
 */
function zib_get_agreement_input($before = '已阅读并同意', $class = 'muted-color mb20', $checked = true)
{
    $agreement = zib_get_user_agreement($before);
    if (!$agreement) {
        return;
    }

    $checked = $checked ? ' checked="checked"' : '';
    $input   = '<input name="user_agreement" id="user_agreement" type="checkbox"' . $checked . '>';
    $input .= '<label for="user_agreement" class="px12 ml6" style="font-weight:normal;">' . $agreement . '</label>';
    return '<div class="form-checkbox ' . $class . '">' . $input . '</div>';
}

/**
 * @description: 根据meta获取用户
 * @param {*} $field
 * @param {*} $value
 * @return {*}
 */
function zib_get_user_by($field = 'phone', $value = '')
{
    if (!$value) {
        return false;
    }

    $cache = wp_cache_get($value, 'user_by_' . $field, true);
    if (false !== $cache) {
        return $cache;
    }

    $query = new WP_User_Query(array('meta_key' => 'phone_number', 'meta_value' => $value));

    if (!is_wp_error($query) && !empty($query->get_results())) {
        $user = $query->get_results()[0];
        wp_cache_set($value, $user, 'user_by_' . $field);
        return $user;
    } else {
        return false;
    }
}

/**
 * @description: 绑定手机号，清空对应缓存
 * @param {*} $user_id
 * @param {*} $phone
 * @return {*}
 */
function zib_bind_phone_del_cache($user_id, $new_phone, $old_phone)
{
    wp_cache_delete($old_phone, 'user_by_phone');
}
add_action('zib_user_update_bind_phone', 'zib_bind_phone_del_cache', 10, 3);

/**
 * @description: 用户验证现有邮箱或手机的from构建
 * @param {*} $type
 * @param {*} $user_id
 * @param {*} $data 目前已经绑定的值
 * @return {*}
 */
function zib_get_user_verify_from($type = 'email')
{

    $name = array(
        'email' => '邮箱',
        'phone' => '手机',
    );

    $action = 'verify_user';

    $input = '';
    $input .= '<div class="mb20">';

    $input .= zib_get_machine_verification_input('img_yz_' . $action);

    $input .= '<div class="relative line-form mb10">';
    $input .= '<input type="text" name="captch" class="line-form-input" autocomplete="off" tabindex="1" placeholder=""><i class="line-form-line"></i>';
    $input .= '<div class="scale-placeholder">验证码</div>';
    $input .= '<span class="yztx abs-right"><button type="button" form-action="' . $action . '_captcha" class="but c-blue captchsubmit">发送验证码</button></span>';
    $input .= '<div class="abs-right match-ok muted-color"><i class="fa-fw fa fa-check-circle"></i></div>';
    $input .= '<input type="hidden" name="captcha_type" value="' . $type . '">';
    $input .= wp_nonce_field($action . '_captcha', '_wpnonce', false, false); //安全效验
    $input .= '</div>';
    $input .= '</div>';

    $input .= '<input type="hidden" name="action" value="' . $action . '">';
    $input .= '<button type="button" class="but jb-blue padding-lg btn-block user-verify-submit" next-tab="" tabindex="2"><i class="fa fa-shield"></i> 立即验证</button>';

    $form = '<form ajax-submit=".user-verify-submit">' . $input . '</form>';

    return $form;
}

/**
 * @description: 用户绑定邮箱或者手机的from构建
 * @param {*} $type
 * @param {*} $user_id
 * @param {*} $data 目前已经绑定的值
 * @return {*}
 */
function zib_get_user_bind_from($type = 'email', $user_id = 0)
{
    $user_id = $user_id ? $user_id : get_current_user_id();
    $input   = '';
    $form    = '';

    $input = '';
    $input .= '<div class="mb20">';
    if ('email' == $type && !_pz('user_bind_option', true, 'email_set_captch')) {
        $input .= '<div class="relative line-form mb10">';
        $input .= '<input type="text" name="email" class="line-form-input" tabindex="1" placeholder=""><i class="line-form-line"></i>';
        $input .= '<div class="scale-placeholder">请输入邮箱</div>';
        $input .= '</div>';
        $input .= zib_get_machine_verification_input('img_yz_bind_' . $type . '_captcha');
    } else {
        $input .= zib_get_sign_captch($type, 'bind_' . $type . '_captcha');
    }
    $input .= '</div>';

    $input .= zib_get_agreement_input();
    $input .= '<input type="hidden" name="action" value="user_bind_' . $type . '">';
    $input .= '<button type="button" class="but jb-blue padding-lg btn-block signsubmit-loader"><i class="fa fa-check"></i> 确认提交</button>';

    $form = '<form>' . $input . '</form>';

    return $form;
}

/**
 * @description: 用户中心绑定、修改密码的tab
 * @param {*} $tab
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_center_bind_tab($tab = 'email', $user_id = '')
{
    $user_id = $user_id ? $user_id : get_current_user_id();
    $udata   = get_userdata($user_id);

    $tab_html = '';

    $tab = in_array($tab, array('email', 'phone', 'change_password')) ? $tab : 'email';
    $tab_html .= '<div class="hide">';
    $tab_html .= '<a href="#tab-bind-email" data-toggle="tab">修改邮箱</a>';
    $tab_html .= '<a href="#tab-change-password" data-toggle="tab">修改密码</a>';
    $tab_html .= '<a href="#tab-bind-phone" data-toggle="tab">绑定手机</a>';
    $tab_html .= '</div>';

    //email
    $user_email      = $udata->user_email;
    $title           = $user_email ? '修改邮箱' : '绑定邮箱';
    $bind_email_from = zib_get_user_bind_from('email', $user_id);
    if ($user_email && _pz('user_bind_option', true, 'email_set_captch')) {
        //如果需要验证，则验证老邮箱
        $bind_email_html = '<div class="tab-content">';
        $bind_email_html .= '';

        $bind_email_html .= '<ul class="mb20 step-simple">';
        $bind_email_html .= '<li class="active"><a href="#tab-bind-email-verify" data-toggle="tab">验证老邮箱</a></li>';
        $bind_email_html .= '<li><span>验证新邮箱</span><a href="#tab-bind-email-bind" data-toggle="tab" class="hide"></a></li>';
        $bind_email_html .= '<li><span>修改成功</span></li>';
        $bind_email_html .= '</ul>';

        $bind_email_html .= '<div class="tab-pane fade active in" id="tab-bind-email-verify">';
        $bind_email_html .= '<p class="muted-2-color">请在下方获取验证码以验证您的老邮箱<span class="badg">' . $user_email . '</span></p>';
        $bind_email_html .= zib_get_user_verify_from('email', $user_email);
        $bind_email_html .= '</div>';

        $bind_email_html .= '<div class="tab-pane fade" id="tab-bind-email-bind">';
        $bind_email_html .= '<p class="muted-2-color">请输入您需要修改的新邮箱账号</p>';
        $bind_email_html .= $bind_email_from;
        $bind_email_html .= '</div>';

        $bind_email_html .= '</div>';
    } else {
        $bind_email_html = $bind_email_from;
    }
    $tab_html .= '<div class="tab-pane fade' . ('email' == $tab ? ' active in' : '') . '" id="tab-bind-email">';
    $tab_html .= zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-envelope-o"></i>', $title);
    $tab_html .= $bind_email_html;
    $tab_html .= '</div>';

    $phone = false;
    if (_pz('user_bind_option', false, 'bind_phone')) {
        //绑定手机
        $phone = zib_get_user_phone_number($user_id);
        $title = $phone ? '修改手机' : '绑定手机';

        $bind_phone_from = zib_get_user_bind_from('phone', $user_id);
        if ($phone) {
            //如果需要验证，则验证老邮箱
            $bind_phone_html = '<div class="tab-content">';

            $bind_phone_html .= '<ul class="mb20 step-simple">';
            $bind_phone_html .= '<li class="active"><a href="#tab-bind-phone-verify" data-toggle="tab">验证老手机</a></li>';
            $bind_phone_html .= '<li><span>验证新手机</span><a href="#tab-bind-phone-bind" data-toggle="tab" class="hide"></a></li>';
            $bind_phone_html .= '<li><span>修改成功</span></li>';
            $bind_phone_html .= '</ul>';

            $bind_phone_html .= '<div class="tab-pane fade active in" id="tab-bind-phone-verify">';
            $bind_phone_html .= '<p class="muted-2-color">请在下方获取验证码以验证您的老手机号<br/><span class="badg">' . $phone . '</span></p>';
            $bind_phone_html .= zib_get_user_verify_from('phone');
            $bind_phone_html .= '</div>';

            $bind_phone_html .= '<div class="tab-pane fade" id="tab-bind-phone-bind">';
            $bind_phone_html .= '<p class="muted-2-color">请输入您需要修改的新手机号码</p>';
            $bind_phone_html .= $bind_phone_from;
            $bind_phone_html .= '</div>';

            $bind_phone_html .= '</div>';
        } else {
            $bind_phone_html = $bind_phone_from;
        }

        $tab_html .= '<div class="tab-pane fade' . ('phone' == $tab ? ' active in' : '') . '" id="tab-bind-phone">';
        $tab_html .= zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-phone"></i>', $title);
        $tab_html .= $bind_phone_html;
        $tab_html .= '</div>';
    }

    //修改密码
    $oauth_new = get_user_meta($user_id, 'oauth_new', true);

    $title = $oauth_new ? '设置密码' : '修改密码';

    $tab_html .= '<div class="tab-pane fade' . ('change_password' == $tab ? ' active in' : '') . '" id="tab-change-password">';
    $tab_html .= zib_get_modal_colorful_header('jb-yellow', '<i class="fa fa-unlock-alt"></i>', $title);
    $tab_html .= '<form>';
    $tab_html .= '<div class="mb20">';
    if (!$oauth_new) {
        $tab_html .= '<div class="relative line-form mb10">';
        $tab_html .= '<input type="password" name="passwordold" class="line-form-input" tabindex="1" placeholder="">';
        $tab_html .= '<div class="scale-placeholder">请输入原密码</div>';
        $tab_html .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
        $tab_html .= '</div>';
    } else {
        $tab_html .= '<input type="hidden" name="oauth_new" value="' . $oauth_new . '">';
    }
    $tab_html .= '<div class="relative line-form mb10">';
    $tab_html .= '<input type="password" name="password" class="line-form-input" tabindex="2" placeholder="">';
    $tab_html .= '<div class="scale-placeholder">请输入新密码</div>';
    $tab_html .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
    $tab_html .= '</div>';
    $tab_html .= '<div class="relative line-form mb10">';
    $tab_html .= '<input type="password" name="password2" class="line-form-input" tabindex="3" placeholder="">';
    $tab_html .= '<div class="scale-placeholder">请再次输入新密码</div>';
    $tab_html .= '<div class="abs-right passw muted-2-color"><i class="fa-fw fa fa-eye"></i></div><i class="line-form-line"></i>';
    $tab_html .= '</div>';
    $tab_html .= zib_get_machine_verification_input('img_yz_change_password');
    $tab_html .= '</div>';

    $tab_html .= '<input type="hidden" name="action" value="user_change_password">';
    $tab_html .= zib_nonce_field('user_change_password'); //安全效验
    $tab_html .= '<button type="button" class="but jb-blue padding-lg btn-block signsubmit-loader"><i class="fa fa-check"></i> 确认提交</button>';
    $tab_html .= '</form>';
    if (!$oauth_new && ($user_email || $phone)) {
        $tab_html .= '<div class="text-right mt6">';
        $tab_html .= '<a data-toggle="tab" href="#tab-reset-password" class="em09 muted-2-color">忘记原密码？点击重设密码</a>';
        $tab_html .= '</div>';
    }
    $tab_html .= '</div>';

    //重设密码
    if (!$oauth_new && ($user_email || $phone)) {
        $tab_html .= '<div class="tab-pane fade" id="tab-reset-password">';
        $tab_html .= zib_get_modal_colorful_header('jb-yellow', '<i class="fa fa-unlock-alt"></i>', '重设密码');
        $tab_html .= '<div class="text-riht mb10 ml10">';
        $tab_html .= '<a data-toggle="tab" href="#tab-change-password" class="em09 but p2-10"><i class="fa fa-angle-left em12"></i> 返回使用原密码修改密码</a>';
        $tab_html .= '</div>';
        $tab_html .= zib_resetpassword_form();
        $tab_html .= '</div>';
    }

    return '<div class="tab-content box-body nopw-sm">' . $tab_html . '</div>';
}

/**
 * @description: 强制绑定、提醒绑定邮箱、手机的tab内容
 * @param {*} $tab
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_bind_tab($bind_type = array('email', 'phone'), $reminder_text = '')
{
    $tab           = $bind_type[0];
    $udata         = wp_get_current_user();
    $user_id       = (isset($udata->ID) ? (int) $udata->ID : 0);
    $tab_html      = '';
    $reminder_text = $reminder_text ? '<div class="mb20 em09 muted-2-color">' . $reminder_text . '</div>' : '';
    //email
    if (empty($udata->user_email) && in_array('email', $bind_type)) {
        $title = '绑定邮箱';
        $tab_html .= '<div class="tab-pane fade' . ('email' == $tab ? ' active in' : '') . '" id="tab-bind-email">';
        $tab_html .= zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-envelope-o"></i>', $title);
        $tab_html .= $reminder_text;
        $tab_html .= zib_get_user_bind_from('email', $user_id, false);
        $tab_html .= '</div>';
    } else {
        $tab = 'phone';
    }

    $phone = zib_get_user_phone_number($user_id);
    if (!$phone && in_array('phone', $bind_type)) {
        //绑定手机
        $title = '绑定手机';
        $tab_html .= '<div class="tab-pane fade' . ('phone' == $tab ? ' active in' : '') . '" id="tab-bind-phone">';
        $tab_html .= zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-phone"></i>', $title);
        $tab_html .= $reminder_text;
        $tab_html .= zib_get_user_bind_from('phone', $user_id, false);
        $tab_html .= '</div>';
    }

    if (!$tab_html) {
        return '';
    }

    $tab_html .= '<div class="hide">';
    $tab_html .= '<a href="#tab-bind-email" data-toggle="tab">修改邮箱</a>';
    $tab_html .= '<a href="#tab-bind-phone" data-toggle="tab">绑定手机</a>';
    $tab_html .= '</div>';
    return '<div class="tab-content">' . $tab_html . '</div>';
}

//强制绑定邮箱或手机，页面重定向
function zib_redirect_user_bind_page()
{
    $bind_type = (array) zib_get_user_bind_type('mandatory_bind');

    if (!$bind_type || is_super_admin()) {
        return;
    }

    //用户协议页面
    $user_agreement_s = _pz('user_agreement_s');
    $the_id           = get_the_ID();
    if ($user_agreement_s && $the_id && $the_id === (int) _pz('user_agreement_page')) {
        return;
    }

    //隐私声明页面
    $user_privacy_s = _pz('user_privacy_s');
    if ($user_privacy_s && $the_id && $the_id === (int) _pz('user_privacy_page')) {
        return;
    }

    $user        = wp_get_current_user();
    $tab         = !empty($_GET['tab']) ? $_GET['tab'] : '';
    $redirect_to = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url();
    if (!empty($user->ID) && !is_admin() && 'bind' != $tab) {
        //已经登录
        $email = $user->user_email;
        if (!$email && in_array('email', $bind_type)) {
            $bind_url = add_query_arg('redirect_to', urlencode($redirect_to), zib_get_sign_url('bind'));
            wp_safe_redirect($bind_url);
            exit;
        }

        $phone = zib_get_user_phone_number($user->ID);
        if (!$phone && in_array('phone', $bind_type)) {
            $bind_url = add_query_arg('redirect_to', urlencode($redirect_to), zib_get_sign_url('bind'));
            wp_safe_redirect($bind_url);
            exit;
        }
    }
}
add_action('template_redirect', 'zib_redirect_user_bind_page');

//挂载用户提醒绑定模态框显示
function zib_bind_reminder_modal()
{
    $user      = wp_get_current_user();
    $bind_type = zib_get_user_bind_type('bind_reminder');

    if (isset($_COOKIE['showed_bind_reminder']) || empty($user->ID) || !$bind_type || is_admin() || is_page_template('pages/user-sign.php')) {
        return;
    }

    //准备
    $user_bind_tab = zib_get_user_bind_tab($bind_type, _pz('user_bind_option', '', 'bind_reminder_text'));
    if (!$user_bind_tab) {
        return;
    }

    $modal = '<div class="modal fade" id="bind_reminder" tabindex="-1" role="dialog">';
    $modal .= '<div class="modal-dialog modal-mini" role="document">';
    $modal .= '<div class="modal-content">';
    $modal .= '<div class="modal-body">';
    $modal .= '<div class="box-body nopw-sm">' . $user_bind_tab . '</div>';
    $modal .= '</div>';
    $modal .= '</div>';
    $modal .= '</div>';
    $modal .= '</div>';

    $expires = round(_pz('user_bind_option', 24, 'bind_reminder_expires') / 24, 3);
    $modal .= '<script type="text/javascript">';
    $modal .= 'window.onload = function(){setTimeout(function () {$(\'#bind_reminder\').modal(\'show\');
        ' . ($expires > 0 ? '$.cookie("showed_bind_reminder","showed", {path: "/",expires: ' . $expires . '});' : '') . '
    }, 1000)};';
    $modal .= '</script>';

    echo $modal;
}
add_action('wp_footer', 'zib_bind_reminder_modal');

//挂载自动弹出登录弹窗
function zib_sign_modal_auto_show()
{
    $user_id = get_current_user_id();
    if ($user_id || !_pz('sign_modal_auto_s')) {
        return;
    }

    if (isset($_COOKIE['showed_sign_modal']) || _pz('user_sign_type') !== 'modal' || is_admin() || is_page_template('pages/user-sign.php')) {
        return;
    }

    $expires = round((int) _pz('sign_modal_auto_expires', 1) / 24, 3);
    $time    = ((int) _pz('sign_modal_auto_waiting_time', 5)) * 1000;

    $html = '<script type="text/javascript">';
    $html .= 'window.onload = function(){setTimeout(function () {
        $(\'.signin-loader:first\').click();
        ' . ($expires > 0 ? '$.cookie("showed_sign_modal","showed", {path: "/",expires: ' . $expires . '});' : '') . '
    }, ' . $time . ')};';
    $html .= '</script>';

    echo $html;
}
add_action('wp_footer', 'zib_sign_modal_auto_show');

/**
 * @description: 获取强制绑定或绑定提醒的配置
 * @param {*} $pz_name
 * @return {*}
 */
function zib_get_user_bind_type($pz_name = 'mandatory_bind')
{
    if (zib_is_close_sign()) {
        return array();
    }

    $bind_pz   = _pz('user_bind_option', 'close', $pz_name);
    $bind_type = array();
    if ('email_phone' === $bind_pz) {
        $bind_type = array('email', 'phone');
    } elseif (in_array($bind_pz, array('phone', 'email'))) {
        $bind_type[] = $bind_pz;
    }
    return $bind_type;
}

/**
 * @description: 获取隐藏的手机号码
 * @param {string} $phone
 * @return {*}
 */
function zib_get_hide_phone($phone)
{
    $phone = (string) $phone;
    if (strlen($phone) > 10) {
        return substr_replace($phone, '****', 3, 4);
    }
    return $phone;
}

/**
 * @description: 获取隐藏的手机号码
 * @param {string} $phone
 * @return {*}
 */
function zib_get_hide_email($emali)
{
    $emali      = (string) $emali;
    $emali_args = explode('@', $emali);

    if (isset($emali_args[0])) {
        if (strlen($emali_args[0]) < 5) {
            return substr($emali_args[0], 0, 2) . '***' . substr($emali_args[0], -2) . '@' . $emali_args[1];
        }
        return substr($emali_args[0], 0, 2) . '****' . substr($emali_args[0], -4) . '@' . $emali_args[1];
    }

    return $emali;
}

/**
 * @description: 获取用户的手机号码
 * @param {int} $user_id
 * @return {*}
 */
function zib_get_user_phone_number($user_id, $hide = true)
{
    $phone = get_user_meta($user_id, 'phone_number', true);

    if (!$phone) {
        return false;
    }

    if ($hide) {
        return zib_get_hide_phone($phone);
    }

    return $phone;
}

/**
 * @description: 获取用户页面的链接
 * @param {*} $id
 * @return {*}
 */
function zib_get_user_home_url($id = 0, $query = false)
{
    if (!$id) {
        $id = get_current_user_id();
    }

    if (!$id) {
        return;
    }

    //先从缓存查询
    $cache = wp_cache_get($id, 'user_home_url', true);
    if ($cache !== false) {
        $url = $cache;
    } else {
        $url = get_author_posts_url($id);
        wp_cache_set($id, 'user_home_url', $url);
    }

    if ($query) {
        $url = add_query_arg($query, $url);
    }

    return $url;
}

/**
 * @description: 获取用户个人主页的按钮
 * @param {*} $class
 * @param {*} $before
 * @param {*} $text
 * @return {*}
 */
function zib_get_user_home_link($id, $class = '', $text = '个人主页')
{
    $url = zib_get_user_home_url($id);
    return '<a href="' . $url . '" class="' . $class . '">' . $text . '</a>';
}

/**
 * @description: 获取用户卡片
 * @param {*} $user_id
 * @param {*} $desc
 * @param {*} $class
 * @return {*}
 */
function zib_get_post_user_box($user_id, $desc = '', $class = '')
{

    $class = $class ? ' ' . $class : '';

    $display_name = zib_get_user_name("id=$user_id");
    $avatar       = zib_get_avatar_box($user_id);
    $follow       = zib_get_user_follow('px12-sm ml10 follow but c-red', $user_id); //关注
    $Private_but  = Zib_Private::get_but($user_id, zib_get_svg('private') . '私信', 'ml6 but c-blue px12-sm'); //私信

    if (!$Private_but && get_current_user_id() != $user_id) {
        $Private_but = zib_get_rewards_button($user_id, 'ml6 but c-blue px12-sm'); //打赏
    }

    $desc = $desc ? $desc : get_user_desc($user_id);

    $html = '<div class="user-info flex ac' . $class . '">';
    $html .= $avatar;
    $html .= '<div class="user-right flex flex1 ac jsb ml10">';
    $html .= '<div class="flex1">';
    $html .= $display_name;
    $html .= '<div class="px12-sm muted-2-color text-ellipsis">';
    $html .= $desc;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="flex0 user-action">';
    $html .= $follow;
    $html .= $Private_but;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * @description: 获取用户名字带链接
 * @param {*} $args
 *            $defaults = array(
 *               'id'         => 0,
 *               'class'      => 'flex ac flex1',
 *               'name_class' => '',
 *               'vip'        => false,
 *               'auth'       => true,
 *               'level'      => true,
 *               'follow'     => false,
 *            );
 * @return {*}
 */
function zib_get_user_name($args = array())
{
    $defaults = array(
        'id'         => 0,
        'class'      => 'flex ac flex1',
        'name_class' => '',
        'vip'        => false,
        'medal'      => true,
        'auth'       => true,
        'level'      => true,
        'follow'     => false,
    );
    if (is_numeric($args)) {
        $defaults['id'] = (int) $args;
        $args           = $defaults;
    } else {
        $args = wp_parse_args($args, $defaults);
    }
    if (!$args['id']) {
        $args['id'] = get_current_user_id();
    }
    if (!$args['id']) {
        return;
    }

    $medal     = $args['medal'] ? zib_get_medal_wear_icon($args['id'], 'ml3') : '';
    $auth      = $args['auth'] ? zib_get_user_auth_badge($args['id'], 'ml3') : '';
    $level     = $args['level'] ? zib_get_user_level_badge($args['id'], 'ml3') : '';
    $follow    = $args['follow'] ? zib_get_user_follow('focus-color ml10 follow flex0', $args['id']) : '';
    $name_link = zib_get_user_name_link($args['id'], $args['name_class'], $args['vip']);
    $html      = '<name class="' . $args['class'] . '">' . $name_link . $auth . $medal . $level . $follow . '</name>';
    return apply_filters('user_show_name', $html, $args['id']);
}

function zib_get_user_name_link($user_id, $class = '', $show_vip = false)
{
    $user = get_userdata($user_id);
    if (!isset($user->display_name)) {
        return;
    }
    $display_name = $user->display_name;
    $url          = zib_get_user_home_url($user_id);
    $vip_icon     = '';
    if ($show_vip) {
        $vip_icon = apply_filters('user_name_badge', zibpay_get_vip_icon(zib_get_user_vip_level($user_id), 'mr3'), $user_id);
    }

    return '<a class="display-name text-ellipsis ' . $class . '" href="' . $url . '">' . $vip_icon . $display_name . '</a>';
}

//获取用户头像
function zib_get_avatar_box($user_id, $class = 'avatar-img', $link = true, $vip = true)
{
    $avatar_img = zib_get_data_avatar($user_id);
    $vip_icon   = '';
    if ($vip) {
        $vip_icon = zib_get_avatar_badge($user_id);
    }

    $html = '<span class="' . $class . '">' . $avatar_img . $vip_icon . '</span>';

    if ($link && $user_id) {
        $helf = zib_get_user_home_url($user_id);
        $html = '<a href="' . $helf . '">' . $html . '</a>';
    }
    return $html;
}

function zib_get_user_avatar_url($user_id)
{
    return zib_get_user_meta($user_id, 'custom_avatar', true) ?: zib_default_avatar();
}

//获取用户头像的badge
function zib_get_avatar_badge($user_id = 0, $tip = 1)
{
    if (!$user_id) {
        return;
    }

    $vip_level = zib_get_user_vip_level($user_id);

    $vip_badge = '';
    $tip_attr  = $tip ? ' data-toggle="tooltip"' : '';
    if ($vip_level) {
        $vip_img_src = zibpay_get_vip_icon_img_url($vip_level);

        $lazy_src  = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail.svg';
        $lazy_attr = zib_is_lazy('lazy_avatar', true) ? 'class="lazyload avatar-badge" src="' . $lazy_src . '" data-' : 'class="avatar-badge" ';

        $vip_badge = '<img ' . $lazy_attr . 'src="' . $vip_img_src . '"' . $tip_attr . ' title="' . _pz('pay_user_vip_' . $vip_level . '_name') . '" alt="' . _pz('pay_user_vip_' . $vip_level . '_name') . '">';
    }

    return apply_filters('user_avatar_badge', $vip_badge, $user_id);
}

/**
 * @description: 在底部显示用户打赏模态框
 * @param {*}
 * @return {*}
 */
function zib_rewards_modal()
{
    global $rewards_user_ids;
    if (is_array($rewards_user_ids)) {
        foreach ($rewards_user_ids as $user_id) {
            echo zib_get_rewards_modal($user_id);
        }
    }
}
add_action('wp_footer', 'zib_rewards_modal');

/**
 * @description: 获取空白打赏模态框
 * @param {*} $user_ID
 * @return {*}
 */
function zib_get_rewards_modal($user_ID)
{
    global $is_show_rewards;

    if ($is_show_rewards && $is_show_rewards = $user_ID) {
        return;
    }

    if (!$user_ID || !_pz('post_rewards_s')) {
        return;
    }

    $args = array(
        'id'              => 'rewards-modal-' . $user_ID,
        'class'           => 'modal-mini rewards-popover',
        'style'           => '',
        'colorful_header' => true,
        'content'         => '<div class="modal-body"><ul class="flex jse mb10 text-center rewards-box"><li><p class="placeholder s1"></p><div class="rewards-img"> <h4 class="placeholder fit-cover"></h4></div></li> <li><p class="placeholder s1"></p><div class="rewards-img"> <h4 class="placeholder fit-cover"></h4></div></li></ul></div>',
    );

    $GLOBALS['is_show_rewards'] = $user_ID;
    return zib_get_blank_modal($args);
}

// 记录用户登录时间
function zib_save_user_last_login($user_id)
{
    if ($user_id) {
        $time       = current_time('Y-m-d');
        $last_login = get_user_meta($user_id, 'last_login', true);
        if (!stristr($last_login, $time)) {
            update_user_meta($user_id, 'last_login', current_time('Y-m-d H:i:s'));
        }
    }
}
add_action('ajax_get_current_user', 'zib_save_user_last_login');
