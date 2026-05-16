<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 15:18:45
 * @LastEditTime : 2026-04-29 20:54:54
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//设置收款码
function zib_ajax_user_set_rewards()
{

    $cuid = get_current_user_id();
    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
        exit;
    }

    if (!wp_verify_nonce($_POST['upload_rewards_nonce'], 'upload_rewards')) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '安全验证失败，请稍候再试')));
        exit();
    }

    $weixin_lao_id = zib_get_user_meta($cuid, 'rewards_wechat_image_id', true);
    $alipay_lao_id = zib_get_user_meta($cuid, 'rewards_alipay_image_id', true);

    if (empty($_FILES['weixin']) && empty($_FILES['alipay']) && !$weixin_lao_id && !$alipay_lao_id) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请选择收款二维码')));
        exit();
    }

    if (!empty($_FILES['weixin'])) {
        $weixin_img_id = zib_php_upload('weixin');
        if (!empty($weixin_img_id['error'])) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $weixin_img_id['msg'])));
            exit();
        }
        if ($weixin_lao_id) {
            wp_delete_attachment($weixin_lao_id, true);
        }
        zib_update_user_meta($cuid, 'rewards_wechat_image_id', $weixin_img_id);
    }

    if (!empty($_FILES['alipay'])) {
        $alipay_img_id = zib_php_upload('alipay');
        if (!empty($alipay_img_id['error'])) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $alipay_img_id['msg'])));
            exit();
        }
        if ($alipay_lao_id) {
            wp_delete_attachment($alipay_lao_id, true);
        }
        zib_update_user_meta($cuid, 'rewards_alipay_image_id', $alipay_img_id);
    }

    if (!empty($_POST['rewards_title'])) {
        zib_update_user_meta($cuid, 'rewards_title', esc_attr(trim($_POST['rewards_title'])));
    }

    //刷新缓存
    wp_cache_delete($cuid, 'user_rewards_img_urls');
    zib_get_user_rewards_img_urls($cuid);

    echo(json_encode(array('error' => 0, 'hide_modal' => true, 'no_preview_reset' => 1, 'msg' => '设置成功')));

    exit();
}
add_action('wp_ajax_user_set_rewards', 'zib_ajax_user_set_rewards');

//修改用户资料
function zib_ajax_user_edit_datas()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);

    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
        exit;
    }

    $_POST['name'] = wp_slash(trim($_POST['name']));
    $_POST['url']  = trim($_POST['url']);

    //用户名判断
    if ($_POST['name'] !== $user->display_name) {
        zib_ajax_username_judgment('name');
    }

    if (!empty($_POST['desc'])) {
        if (zib_new_strlen($_POST['desc']) < 4) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '签名过短，不能低于4个字符')));
            exit();
        }
        if (zib_new_strlen($_POST['desc']) > 60) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '签名过长，不能超过60个字符')));
            exit();
        }
    }

    //昵称、签名合规性判断
    if (_pz('audit_user_desc')) {
        ZibAudit::ajax_text($_POST['desc'] . $_POST['name'], '昵称或签名');
    }

    if ($_POST['url'] && (!zib_is_url($_POST['url']))) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '网址格式错误')));
        exit();
    }

    if ($_POST['url'] && !$_POST['url_name']) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入个人网站名称')));
        exit();
    }

    if ($_POST['url_name'] && !$_POST['url']) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入个人网站链接')));
        exit();
    }

    if ($_POST['address'] && zib_new_strlen($_POST['address']) > 50) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '居住地格式错误')));
        exit();
    }

    if ($_POST['weibo'] && (!zib_is_url($_POST['weibo']) || zib_new_strlen($_POST['weibo']) > 50)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '微博格式错误')));
        exit();
    }

    if ($_POST['github'] && (!zib_is_url($_POST['github']) || zib_new_strlen($_POST['github']) > 50)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => 'GitHub格式错误')));
        exit();
    }

    if ($_POST['qq'] && !preg_match("/^[1-9]\d{4,16}$/", $_POST['qq'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => 'QQ格式错误')));
        exit();
    }

    if ($_POST['weixin'] && zib_new_strlen($_POST['weixin']) > 20) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '微信字数过长，限制在30字内')));
        exit();
    }

    if ($_POST['desc']) {
        update_user_meta($cuid, 'description', trim(strip_tags($_POST['desc'])));
    }

    zib_update_user_meta($cuid, 'qq', trim(strip_tags($_POST['qq'])));
    zib_update_user_meta($cuid, 'weixin', trim(strip_tags($_POST['weixin'])));
    zib_update_user_meta($cuid, 'weibo', trim(strip_tags($_POST['weibo'])));
    zib_update_user_meta($cuid, 'github', trim(strip_tags($_POST['github'])));
    zib_update_user_meta($cuid, 'url_name', trim(strip_tags($_POST['url_name'])));
    zib_update_user_meta($cuid, 'address', trim(strip_tags($_POST['address'])));
    zib_update_user_meta($cuid, 'privacy', trim(strip_tags($_POST['privacy'])));
    update_user_meta($cuid, 'gender', trim(strip_tags($_POST['gender'])));

    $datas = array('ID' => $cuid);
    if ($_POST['url']) {
        $datas['user_url'] = $_POST['url'];
    }

    if ($_POST['name']) {
        $datas['display_name'] = $_POST['name'];
        $datas['nickname']     = $_POST['name'];
    }

    $status = wp_update_user($datas);

    if (is_wp_error($status)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $status->get_error_message())));
        exit();
    }

    echo(json_encode(array('error' => 0, 'ys' => '', 'msg' => '用户资料修改成功')));
    exit();
}
add_action('wp_ajax_user_edit_datas', 'zib_ajax_user_edit_datas');

//修改用户头像
function zib_ajax_user_upload_avatar()
{
    $cuid = get_current_user_id();
    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
        exit;
    }
    if (!wp_verify_nonce($_POST['upload_avatar_nonce'], 'upload_avatar')) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '安全验证失败，请稍候再试')));
        exit();
    }

    if (empty($_FILES['file'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请选择图像')));
        exit();
    }

    $img_id = zib_php_upload();
    if (!empty($img_id['error'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $img_id['msg'])));
        exit();
    }

    $image_url = wp_get_attachment_image_src($img_id, 'thumbnail');
    $lao_id    = zib_get_user_meta($cuid, 'custom_avatar_id', true);
    if ($lao_id) {
        wp_delete_attachment($lao_id, true);
    }
    zib_update_user_meta($cuid, 'custom_avatar_id', $img_id);
    zib_update_user_meta($cuid, 'custom_avatar', $image_url[0]);

    do_action('user_save_custom_avatar', $cuid, $img_id, $image_url[0]);

    echo(json_encode(array('error' => 0, 'hide_modal' => true, 'msg' => '头像修改成功', 'replace_img' => '.avatar-id-' . $cuid, 'img_id' => $img_id, 'url' => $image_url[0])));
    exit();
}
add_action('wp_ajax_user_upload_avatar', 'zib_ajax_user_upload_avatar');

add_action('wp_ajax_user_avatar_set_modal', function () {
    echo zib_get_user_avatar_set_modal();
    exit();
});
add_action('wp_ajax_user_cover_set_modal', function () {
    echo zib_get_user_cover_set_modal();
    exit();
});
add_action('wp_ajax_user_collection_set_modal', function () {
    echo zib_get_user_collection_modal();
    exit();
});

//修改用户封面图修
function zib_ajax_user_upload_cover()
{
    $cuid = get_current_user_id();
    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
        exit;
    }
    if (!wp_verify_nonce($_POST['upload_cover_nonce'], 'upload_cover')) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '安全验证失败，请稍候再试')));
        exit();
    }

    if (empty($_FILES['file'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请选择图像')));
        exit();
    }

    $img_id = zib_php_upload();
    if (!empty($img_id['error'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $img_id['msg'])));
        exit();
    }
    $image_url = wp_get_attachment_image_src($img_id, 'full');
    $lao_id    = zib_get_user_meta($cuid, 'cover_image_id', true);

    if ($lao_id) {
        wp_delete_attachment($lao_id, true);
    }

    zib_update_user_meta($cuid, 'cover_image_id', $img_id);
    zib_update_user_meta($cuid, 'cover_image', $image_url[0]);

    echo(json_encode(array('error' => 0, 'hide_modal' => true, 'msg' => '封面图修改成功', 'img_id' => $img_id, 'replace_img' => '.user-cover-id-' . $cuid, 'url' => $image_url[0])));
    exit();
}
add_action('wp_ajax_user_upload_cover', 'zib_ajax_user_upload_cover');

//修改用户密码
function zib_ajax_user_change_password()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $user_id = get_current_user_id();
    if (!$user_id) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }
    if (empty($_POST['password']) || empty($_POST['password2'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '密码不能为空')));
        exit();
    }

    if (strlen($_POST['password']) < 6) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '密码至少6位')));
        exit();
    }

    if ($_POST['password'] !== $_POST['password2']) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '两次密码输入不一致')));
        exit();
    }

    //人机验证
    zib_ajax_man_machine_verification('img_yz_change_password');

    $oauth_new = get_user_meta($user_id, 'oauth_new', true);
    if (!$oauth_new) {

        if (empty($_POST['passwordold'])) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入原密码')));
            exit();
        }

        if ($_POST['passwordold'] == $_POST['password']) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '新密码和原密码不能相同')));
            exit();
        }

        //错误频率限制
        $is_frequency_limit = zib_is_error_frequency_limit('change_password');
        if ($is_frequency_limit['error']) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $is_frequency_limit['msg'])));
            exit();
        }

        $user  = get_user_by('id', $user_id);
        $valid = wp_check_password($_POST['passwordold'], $user->user_pass, $user->ID);

        if (!$valid) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '原密码错误')));
            exit();
        }
    }

    $status = wp_update_user(
        array(
            'ID'        => $user_id,
            'user_pass' => $_POST['password'],
        )
    );

    if (is_wp_error($status)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $status->get_error_message())));
        exit();
    }
    delete_user_meta($user_id, 'oauth_new');

    //重置错误频率限制
    zib_reset_error_frequency_limit('change_password');

    $msg = $oauth_new ? '密码设置成功' : '修改成功，下次请使用新密码登录';
    echo(json_encode(array('error' => 0, 'msg' => $msg)));
    exit();
}
add_action('wp_ajax_user_change_password', 'zib_ajax_user_change_password');

//用户解除第三方账户绑定
function zib_ajax_user_oauth_untying()
{
    if (empty($_POST['user_id']) || empty($_POST['type'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
        exit();
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $cuid = get_current_user_id();
    if (!$cuid || $cuid != $_POST['user_id']) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
        exit;
    }

    delete_user_meta($_POST['user_id'], 'oauth_' . $_POST['type'] . '_openid');
    zib_update_user_meta($_POST['user_id'], 'oauth_' . $_POST['type'] . '_getUserInfo', false);

    $goto = zib_get_user_center_url('account');

    echo(json_encode(array('error' => 0, 'ys' => '', 'reload' => true, 'goto' => $goto, 'msg' => '已解除绑定')));

    exit();
}
add_action('wp_ajax_user_oauth_untying', 'zib_ajax_user_oauth_untying');

///AJAX用户绑定邮箱发送验证码
function zib_ajax_bind_email_captcha()
{
    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);

    $email = $user->user_email;

    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }

    if (empty($_POST['email'])) {
        echo(json_encode(array('error' => 1, 'msg' => '请输入邮箱账号')));
        exit();
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo(json_encode(array('error' => 1, 'msg' => '邮箱格式错误')));
        exit();
    }

    if ($email == $_POST['email']) {
        echo(json_encode(array('error' => 1, 'msg' => '修改邮箱不能与现邮箱相同')));
        exit();
    }

    //绑定邮箱，验证邮箱合法性
    zib_ajax_email_judgment($_POST['email']);

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_bind_email_captcha');

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (email_exists($_POST['email'])) {
        echo(json_encode(array('error' => 1, 'msg' => '该邮箱已有绑定账号')));
        exit();
    }

    zib_ajax_send_captcha('email', $_POST['email'], false);
    exit();
}
add_action('wp_ajax_bind_email_captcha', 'zib_ajax_bind_email_captcha');

//用户绑定邮箱
function zib_ajax_user_bind_email()
{
    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);

    $email = $user->user_email;

    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }

    //执行用户隐私协议是否同意
    zib_ajax_agree_agreement_judgment();
    //执行邮箱格式判断
    $captcha     = zib_ajax_captcha_form_judgment('email');
    $captcha_val = $captcha['to'];

    if ($email == $_POST['email']) {
        echo(json_encode(array('error' => 1, 'msg' => '修改邮箱不能与现邮箱相同')));
        exit();
    }

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_bind_email_captcha');

    //绑定邮箱，验证邮箱合法性
    zib_ajax_email_judgment($captcha_val);

    //判断是否需要验证码验证
    if (_pz('user_bind_option', true, 'email_set_captch')) {
        //执行验证码：验证判断
        zib_ajax_is_captcha('email');
        //执行老邮箱验证判断
        if ($email) {
            zib_ajax_is_verify_user('email', $cuid, '老邮箱');
        }
    }

    if (email_exists($captcha_val)) {
        echo(json_encode(array('error' => 1, 'msg' => '该邮箱已有绑定账号')));
        exit();
    }

    $status = wp_update_user(
        array(
            'ID'         => $cuid,
            'user_email' => $captcha_val,
        )
    );

    if (is_wp_error($status)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', $status->get_error_message())));
        exit();
    }

    do_action('zib_user_bind_email', $cuid, $captcha_val, $email);

    $msg = $email ? '邮箱修改成功' : '邮箱绑定成功';
    echo(json_encode(array('error' => 0, 'msg' => $msg)));
    exit();
}
add_action('wp_ajax_user_bind_email', 'zib_ajax_user_bind_email');

///AJAX用户绑定邮箱发送验证码
function zib_ajax_bind_phone_captcha()
{
    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);

    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }

    //执行手机号格式判断
    $captcha = zib_ajax_captcha_form_judgment('phone');
    $phone   = get_user_meta($cuid, 'phone_number', true);

    if ($phone == $_POST['phone']) {
        echo(json_encode(array('error' => 1, 'msg' => '手机号不能与现手机号相同')));
        exit();
    }

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_bind_phone_captcha');
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (zib_get_user_by('phone', $_POST['phone'])) {
        echo(json_encode(array('error' => 1, 'msg' => '该手机号已被绑定')));
        exit();
    }

    zib_ajax_send_captcha('phone', $_POST['phone'], false);
    exit();
}
add_action('wp_ajax_bind_phone_captcha', 'zib_ajax_bind_phone_captcha');

//用户绑定手机号
function zib_ajax_user_bind_phone()
{
    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);

    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }

    //执行用户隐私协议是否同意
    zib_ajax_agree_agreement_judgment();
    //执行手机号格式判断
    $captcha     = zib_ajax_captcha_form_judgment('phone');
    $captcha_val = $captcha['to'];

    $old_phone = get_user_meta($cuid, 'phone_number', true); //老手机号

    if ($old_phone == $captcha_val) {
        echo(json_encode(array('error' => 1, 'msg' => '手机号不能与现手机号相同')));
        exit();
    }

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_bind_phone_captcha');

    //执行验证码：验证判断
    zib_ajax_is_captcha('phone');

    //执行老手机号码验证判断
    if ($old_phone) {
        zib_ajax_is_verify_user('phone', $cuid, '老手机号');
    }

    //更新新手机号
    update_user_meta($cuid, 'phone_number', $captcha_val);
    if ($old_phone) {
        do_action('zib_user_update_bind_phone', $cuid, $captcha_val, $old_phone); //添加挂钩
    }

    do_action('zib_user_bind_phone', $cuid, $captcha_val, $old_phone);

    $msg = $old_phone ? '手机号修改成功' : '手机号绑定成功';
    echo(json_encode(array('error' => 0, 'msg' => $msg)));
    exit();
}
add_action('wp_ajax_user_bind_phone', 'zib_ajax_user_bind_phone');

//用户账户功能设置模态框
function zib_ajax_user_set_modal()
{

    $user_data = wp_get_current_user();
    $cuid      = (isset($user_data->ID) ? (int) $user_data->ID : 0);
    if (!$cuid) {
        zib_ajax_notice_modal('danger', '登录状态异常，请刷新后重试');
    }

    $tab = !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : 'email';
    echo zib_get_user_center_bind_tab($tab, $cuid);
    exit();
}
add_action('wp_ajax_user_set_modal', 'zib_ajax_user_set_modal');

///AJAX用户验证：发送验证码
function zib_ajax_verify_user_captcha()
{
    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);
    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }

    if (empty($_REQUEST['captcha_type'])) {
        echo(json_encode(array('error' => 1, 'msg' => '参数传入错误')));
        exit();
    }

    $type = $_REQUEST['captcha_type'];
    if ('email' == $type) {
        $to = $user->user_email;
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo(json_encode(array('error' => 1, 'msg' => '用户邮箱格式错误，请于管理员联系')));
            exit();
        }
    } elseif ('phone' == $type) {
        $to = get_user_meta($cuid, 'phone_number', true);
        if (!$to) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到绑定的手机号')));
            exit();
        }
    }

    //执行人机验证
    zib_ajax_man_machine_verification('img_yz_verify_user');
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //发送验证码
    zib_ajax_send_captcha($type, $to, false);
    exit();
}
add_action('wp_ajax_verify_user_captcha', 'zib_ajax_verify_user_captcha');

///AJAX用户验证：执行验证
function zib_ajax_verify_user()
{

    $user = wp_get_current_user();
    $cuid = (isset($user->ID) ? (int) $user->ID : 0);
    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '登录状态异常，请刷新后重试')));
        exit;
    }

    if (empty($_REQUEST['captcha_type'])) {
        echo(json_encode(array('error' => 1, 'msg' => '参数传入错误')));
        exit();
    }

    $type = $_REQUEST['captcha_type'];
    if ('email' == $type) {
        $to = $user->user_email;
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo(json_encode(array('error' => 1, 'msg' => '用户邮箱格式错误，请于管理员联系')));
            exit();
        }
    } elseif ('phone' == $type) {
        $to = get_user_meta($cuid, 'phone_number', true);
        if (!$to) {
            echo(json_encode(array('error' => 1, 'msg' => '未找到绑定的手机号')));
            exit();
        }
    }

    //执行验证码：验证判断
    if (empty($_REQUEST['captch'])) {
        echo(json_encode(array('error' => 1, 'msg' => '请输入验证码')));
        exit();
    }

    $is_captcha = zib_is_captcha($to, $_REQUEST['captch']);
    if ($is_captcha['error']) {
        echo json_encode($is_captcha);
        exit();
    }
    //验证成功，保存@session_start();信息

    /**保存验证码到缓存 */
    zib_set_verify_user($type, $cuid);

    echo(json_encode(array('error' => 0, 'msg' => '验证成功')));
    exit();
}
add_action('wp_ajax_verify_user', 'zib_ajax_verify_user');

function zib_ajax_user_rewards_modal()
{
    $user_ID = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;

    $rewards_title = zib_get_user_meta($user_ID, 'rewards_title', true);
    $rewards_title = $rewards_title ? $rewards_title : '文章很赞！支持一下吧';
    $weixin_img    = '';
    $alipay_img    = '';

    $rewards_img_urls = zib_get_user_rewards_img_urls($user_ID);
    $weixin           = $rewards_img_urls['weixin'];
    $alipay           = $rewards_img_urls['alipay'];

    if ($weixin) {
        $weixin_img = '<li><p class="muted-2-color">微信扫一扫</p><div class="rewards-img"><img class="fit-cover" src="' . $weixin . '"></div></li>';
    }
    if ($alipay) {
        $alipay_img = '<li><p class="muted-2-color">支付宝扫一扫</p><div class="rewards-img"><img class="fit-cover" src="' . $alipay . '"></div></li>';
    }

    $modal_header = zib_get_modal_colorful_header('jb-blue', zib_get_svg('money', null, 'em12'), $rewards_title);
    $html         = $modal_header;
    $html .= '<ul class="flex jse mb10 text-center rewards-box">';
    $html .= $weixin_img;
    $html .= $alipay_img;
    $html .= '</ul>';

    echo '<div class="modal-body">' . $html . '</div>';
    exit();
}
add_action('wp_ajax_user_rewards_modal', 'zib_ajax_user_rewards_modal');
add_action('wp_ajax_nopriv_user_rewards_modal', 'zib_ajax_user_rewards_modal');

//查看用户全部详细资料的模态框
function zib_ajax_user_details_data_modal()
{
    $user_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';

    $user = get_userdata($user_id);
    if (!$user_id || empty($user->ID)) {
        zib_ajax_notice_modal('danger', '用户不存在或参数传入错误');
    }

    echo zib_get_user_details_data_modal($user_id);
    exit();
}
add_action('wp_ajax_user_details_data_modal', 'zib_ajax_user_details_data_modal');
add_action('wp_ajax_nopriv_user_details_data_modal', 'zib_ajax_user_details_data_modal');
