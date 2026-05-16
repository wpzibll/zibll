<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-23 21:36:42
 * @LastEditTime : 2026-03-11 12:54:14
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//邮件smtp设置
function zib_mail_smtp($phpmailer)
{
    if (_pz('mail_smtps')) {
        $phpmailer->IsSMTP();
        $phpmailer->FromName   = _pz('mail_showname');
        $phpmailer->Host       = _pz('mail_host', 'smtp.qq.com');
        $phpmailer->Port       = _pz('mail_port', '465');
        $phpmailer->Username   = _pz('mail_smtp_name') ?: _pz('mail_name');
        $phpmailer->Sender     = _pz('mail_name', '88888888@qq.com');
        $phpmailer->Password   = _pz('mail_passwd', '123456789');
        $phpmailer->From       = _pz('mail_name', '88888888@qq.com');
        $phpmailer->SMTPAuth   = _pz('mail_smtpauth', true);
        $phpmailer->SMTPSecure = _pz('mail_smtpsecure', 'ssl');
    }
}
add_action('phpmailer_init', 'zib_mail_smtp');

//邮件发件人名称
function zib_mail_from_name($from_name)
{
    return _pz('mail_showname', get_bloginfo('name'));
}
add_filter('wp_mail_from_name', 'zib_mail_from_name');

/**
 * @description: 发送邮件给网站管理员统一接口，会发送给所有超级管理员账号
 * @param {*} $title
 * @param {*} $message
 * @return {*}
 */
function zib_mail_to_admin($title, $message)
{
    $emails = zib_get_admin_user_emails();
    if ($emails) {
        foreach ($emails as $e) {
            @wp_mail($e, $title, $message);
        }
    }
}

/**
 * 发送邮件
 * @param string|array $to 收件人邮箱地址
 * @param string $title 邮件标题
 * @param string $message 邮件内容
 */
function zib_send_email($to, $title, $message)
{
    if (is_array($to)) {
        //去重
        $to = array_unique($to);
        foreach ($to as $e) {
            zib_send_email($e, $title, $message);
        }
    } else {
        if ($to && is_email($to) && !stristr($to, '@no')) {
            @wp_mail($to, $title, $message);
        }
    }
}

/**邮件内容过滤器 */
add_filter('wp_mail', 'zib_get_mail_content');
function zib_get_mail_content($mail)
{
    $mail        = (array) $mail;
    $message     = !empty($mail['message']) ? nl2br($mail['message']) : '';
    $blog_name   = get_bloginfo('name');
    $description = _pz('mail_description', _pz('description', wp_title('', false)));
    $description = trim($description);
    $logo        = _pz('logo_src');

    $con_more = _pz('mail_more_content');
    $bg       = ZIB_TEMPLATE_DIRECTORY_URI . '/img/mail-bg.png?ver=' . THEME_VERSION;

    $content = '<style>
		.zibll-email-box .but{
			display: inline-block;
			border-radius: 4px;
			padding: 5px 22px;
			text-align: center;
			background: linear-gradient(135deg, #59c3fb 10%, #268df7 100%) !important;
			color: #fff !important;
            line-height: 1.4;
			text-decoration: none;
		}
		.zibll-email-box img{
			max-width: 100%;
		}
        .zibll-email-box img.smilie-icon{
			max-width: 2em;
		}
		.zibll-email-box a{
			text-decoration: none !important;
		}
        .zibll-email-box .muted-box{
			background: rgba(141, 141, 141, 0.05);
			padding: 10px 15px;
			border-radius: 8px;
			line-height: 1.7;
		}

	</style>
	<div class="zibll-email-box" style="background:#ecf1f3;padding:2%;">
		<div style="max-width:701px;height:auto; margin:0px auto;background: #fff;box-shadow: 6px 3px 5px rgba(0,0,0,0.05);-webkit-box-shadow: 6px 3px 10px rgba(0,0,0,0.05);-moz-box-shadow: 6px 3px 5px rgba(0,0,0,0.05);-ms-box-shadow: 6px 3px 5px rgba(0,0,0,0.05);-o-box-shadow: 6px 3px 5px rgba(0,0,0,0.05);">
			<div style="">
				<div style="padding-top: 50px;max-width:200px;width: 40%; margin:auto;">
                    <img style="width: 100%;height: auto;" src="' . $logo . '">
				</div>
				<div style="color:#55798d;padding: 40px 10%;"><br>
					<div style="overflow-wrap:break-word;line-height:30px;font-size: 14px;">
					' . $message . '
					</div>
				</div>
			</div>
			<div style="border-top:2px dashed rgba(125, 136, 140, 0.15);">
				<div style="color:#507383;font-size:14px;line-height: 1.4;padding: 40px 10%;">
					<div style="font-size: 20px;font-weight: bold;">' . $blog_name . '</div>
					<div style="margin:20px 0;color: #6a8895;min-height:4.2em;white-space: pre-wrap;">' . $description . '</div>
					<div style="">' . $con_more . '</div>
				</div>
				<img src="' . $bg . '" style="width: 100%;height: auto;">
			</div>
		</div>
	</div>
	';

    //去除原本的前缀
    $blogname        = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $mail['subject'] = str_replace("[$blogname]", '', $mail['subject']);

    $mail_title_prefix = _pz('mail_title_prefix');
    $mail['subject']   = $mail_title_prefix . $mail['subject'];
    $mail['message']   = $content;
    $mail['headers']   = array('Content-Type: text/html; charset=UTF-8');
    return $mail;
}

//用户收到私信之后向用户发送邮件通知
if (_pz('email_private_receive') && _pz('message_s') && _pz('private_s')) {
    add_action('zib_add_message', 'zib_private_receive_email', 99);
}
function zib_private_receive_email($msg)
{
    $msg = (array) $msg;
    if ('private' != $msg['type']) {
        return false;
    }

    $send_user_id    = $msg['send_user'];
    $receive_user_id = $msg['receive_user'];
    $send_udata      = get_userdata($send_user_id);
    $receive_udata   = get_userdata($receive_user_id);

    //用户功能权限判断
    $send_limit = _pz('email_private_receive_limit', 'all');
    switch ($send_limit) {
        case 'admin':
            if (!is_super_admin($receive_user_id)) {
                return false;
            }

            break;
        case 'vip2':
            $vip_level = zib_get_user_vip_level($receive_user_id);
            if (!$vip_level || $vip_level < 2) {
                return false;
            }

            break;
        case 'vip':
            $vip_level = zib_get_user_vip_level($receive_user_id);
            if (!$vip_level) {
                return false;
            }

            break;
    }

    /**判断邮箱状态 */
    if (!is_email($receive_udata->user_email) || stristr($receive_udata->user_email, '@no')) {
        return false;
    }

    $_link = zibmsg_get_conter_url('private');
    $title = '收到用户[' . $send_udata->display_name . ']发来的私信';

    $message = '您好！' . $receive_udata->display_name . '<br>';
    $message .= '收到一条新的私信消息<br />';
    $message .= '用户：' . $send_udata->display_name . '<br>';
    $message .= '内容：' . '<br>';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . Zib_Private::get_content($msg) . '</div>';
    $message .= '时间：' . $msg['create_time'] . '<br>';
    $message .= '<br>';

    $message .= '您可以打开下方链接查看此消息<br />';
    $message .= '<a target="_blank" style="margin-top: 20px" href="' . esc_url($_link) . '">' . $_link . '</a>' . '<br>';

    /**发送邮件 */
    @wp_mail($receive_udata->user_email, $title, $message);
}

/**用户评论通过审核之后向用户发送邮件 */
if (_pz('email_comment_approved', true)) {
    add_action('comment_unapproved_to_approved', 'zib_comment_approved_email', 99);
}
function zib_comment_approved_email($comment)
{

    $user_id = $comment->user_id;
    $udata   = get_userdata($user_id);

    /**判断邮箱状态 */
    if (!is_email($udata->user_email) || stristr($udata->user_email, '@no')) {
        return false;
    }

    $post_title = get_the_title($comment->comment_post_ID);
    $_link      = get_comment_link($comment->comment_ID);
    $post_title = get_the_title($comment->comment_post_ID);
    $post_tlink = get_the_permalink($comment->comment_post_ID);

    $title = '您发布的评论已通过审核';

    $message = '您好！' . get_comment_author($comment->comment_ID) . '<br>';
    $message .= '您在[<a class="muted-color" href="' . esc_url($post_tlink) . '">' . $post_title . '</a>]中的评论，已经通过审核' . '<br>';
    $message .= '评论内容：' . '<br>';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . get_comment_text($comment->comment_ID) . '</div>';
    $message .= '评论时间：' . $comment->comment_date . '<br>';
    $message .= '<br>';

    $message .= '您可以打开下方链接查看评论<br />';
    $message .= '<a target="_blank" style="margin-top: 20px" href="' . esc_url($_link) . '">' . $_link . '</a>' . '<br>';

    /**发送邮件 */
    @wp_mail($udata->user_email, $title, $message);
}

// 文章或帖子审核通过、或驳回后，给作者发邮件
if (_pz('email_newpost_to_publish', true)) {
    add_action('pending_to_publish', 'zib_email_pending_to_publish', 99);
    add_action('publish_to_pending', 'zib_email_publish_to_pending', 99);
}

/**当投稿的文章从草稿状态变更到已发布时 */
function zib_email_publish_to_pending($post)
{
    $user_id = $post->post_author;
    //用户是否接收
    if (!$user_id || empty($_REQUEST['msg_s'])) {
        return;
    }
    $msg = !empty($_REQUEST['msg']) ? trim(strip_tags($_REQUEST['msg'])) : '';

    /**判断是否登录后投稿 */
    if ($post->post_type == 'post' && _pz('post_article_user', 1) == $user_id) {
        return;
    }

    $udata = get_userdata($user_id);
    /**作者自己驳回的不通知 */
    if ($user_id == get_current_user_id()) {
        return;
    }

    /**判断邮箱状态 */
    if (!is_email($udata->user_email) || stristr($udata->user_email, '@no')) {
        return false;
    }

    $_link = get_permalink($post->ID);
    $title = '您发布的内容已被驳回';

    $message = '您好！' . $udata->display_name . '<br>';
    $message .= '您发布的内容[' . zib_str_cut($post->post_title, 0, 20, '...') . ']，已被驳回，当前为待审核状态' . '<br>';
    $message .= '内容摘要：<br />';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '提交时间：' . get_the_time('Y-m-d H:i:s', $post) . '<br>';
    $message .= '驳回时间：' . $post->post_modified . '<br>';
    $message .= $msg ? '驳回说明：' . $msg . '<br>' : '';
    $message .= '<br>';

    $message .= '您可以打开下方链接查看此内容<br />';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">立即查看</a>' . '<br>';

    /**发送邮件 */
    @wp_mail($udata->user_email, $title, $message);
}

function zib_email_pending_to_publish($post)
{

    $user_id = $post->post_author;
    /**判断是否登录后投稿 */
    if (_pz('post_article_user', 1) == $user_id) {
        return false;
    }

    /**判断通知状态 */
    if (get_post_meta($post->ID, 'pending_to_publish_email', true)) {
        return false;
    }

    $udata = get_userdata($user_id);
    /**判断是否是管理员或者作者 */
    if (in_array('administrator', $udata->roles) || in_array('roles', $udata->roles)) {
        return false;
    }

    /**判断邮箱状态 */
    if (!is_email($udata->user_email) || stristr($udata->user_email, '@no')) {
        return false;
    }

    $_link = get_permalink($post->ID);
    $title = '您发布的内容已通过审核';

    $message = '您好！' . $udata->display_name . '<br>';
    $message .= '您发布的内容[' . $post->post_title . ']，已经通过审核' . '<br>';
    $message .= '内容摘要：<br />';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '提交时间：' . get_the_time('Y-m-d H:i:s', $post) . '<br>';
    $message .= '审核时间：' . get_the_modified_time('Y-m-d H:i:s', $post) . '<br>';
    $message .= '<br>';

    $message .= '您可以打开下方链接查看此内容<br />';
    $message .= '<a target="_blank" style="margin-top: 20px" href="' . esc_url($_link) . '">' . $_link . '</a>' . '<br>';

    /**发送邮件 */
    update_post_meta($post->ID, 'pending_to_publish_email', true);
    @wp_mail($udata->user_email, $title, $message);
}

/**用户提交链接向管理员发送邮件 */
if (_pz('email_links_submit_to_admin', true)) {
    add_action('zib_ajax_frontend_links_submit_success', 'zib_links_submit_email_to_admin', 99);
}
function zib_links_submit_email_to_admin($data)
{

    if ($data['link_visible'] === 'Y') {
        return false;
    }

    $linkdata = array(
        'link_name'        => esc_attr($data['link_name']),
        'link_url'         => esc_url($data['link_url']),
        'link_description' => !empty($data['link_description']) ? esc_attr($data['link_description']) : '无',
        'link_image'       => !empty($data['link_image']) ? '<img src="' . esc_attr($data['link_image']) . '" style=" width: 80px; height: auto; ">' : '空',
    );
    $_link = admin_url('link-manager.php?orderby=visible&order=asc');

    $title = '有新的链接提交待审核：' . $linkdata['link_name'];

    $message = '网站有新的链接提交：<br />';
    $message .= '链接名称：' . $linkdata['link_name'] . '<br>';
    $message .= '链接地址：' . $linkdata['link_url'] . '<br>';
    $message .= '链接简介：' . $linkdata['link_description'] . '<br>';
    $message .= '链接Logo：' . $linkdata['link_image'] . '<br>';
    $message .= '<br>';

    $message .= '您可以打开下方地址以审核该链接<br />';
    $message .= '<a target="_blank" style="margin-top: 20px" href="' . esc_url($_link) . '">' . $_link . '</a>' . '<br>';
    /**发送邮件 */
    zib_mail_to_admin($title, $message);
}
