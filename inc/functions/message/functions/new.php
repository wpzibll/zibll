<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 17:11:44
 * @LastEditTime : 2026-03-11 12:17:11
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**用户评论通过审核之后发消息给评论用户 */
add_action('comment_unapproved_to_approved', 'zib_newmsg_comment_approved', 99);
function zib_newmsg_comment_approved($comment)
{

    if (!$comment->user_id) {
        return;
    }

    $is_notify = zib_get_comment_meta($comment->comment_ID, 'is_notify', true);
    if ($is_notify) {
        return;
    }

    $_link      = get_comment_link($comment->comment_ID);
    $post       = get_post($comment->comment_post_ID);
    $post_title = zib_str_cut($post->post_title, 0, 16, '...');

    $post_link = get_permalink($comment->comment_post_ID);

    $title = '您发表的评论已通过审核:[' . $post_title . ']';

    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $message         = '您好！' . get_comment_author($comment->comment_ID) . '<br>';
    $message .= '您在[<a href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]中的评论，已经通过审核' . '<br>';
    $message .= '评论内容：' . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= '评论时间：' . $comment->comment_date . '<br>';
    $message .= '<br>';

    $message .= '您可以点击下方按钮查看评论<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">查看评论</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $comment->user_id,
        'type'         => 'comment',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    if (zib_msg_is_allow_receive($comment->user_id, 'comment')) {
        ZibMsg::add($msg_arge);
    }

    wp_new_comment_notify_postauthor($comment->comment_ID);
    zib_update_comment_meta($comment->comment_ID, 'is_notify', true);
}

/**用户评论通过审核之后，如果是回复给其它人，则给其他人发送消息以及邮件 */
add_action('comment_post', 'zib_newmsg_comment_approved_toparent', 99);
add_action('comment_unapproved_to_approved', 'zib_newmsg_comment_approved_toparent', 99);
function zib_newmsg_comment_approved_toparent($comment)
{
    $comment = get_comment($comment);

    if (!$comment->comment_parent || '1' != $comment->comment_approved) {
        return;
    }

    $_link          = get_comment_link($comment);
    $parent_id      = $comment->comment_parent;
    $parent_comment = get_comment($parent_id);
    $post           = get_post($parent_comment->comment_post_ID);

    if ($parent_comment->user_id) {

        //自己回复自己不通知
        if ($parent_comment->user_id == $comment->user_id) {
            return;
        }

        //自己是作者不通知
        if ($parent_comment->user_id == $post->post_author) {
            //    return;
        }
    }

    $post_title = zib_str_cut($post->post_title, 0, 16, '...');
    $post_link  = get_permalink($parent_comment->comment_post_ID);

    $title = '您发表的评论有新的回复:[' . $post_title . ']';

    $comment_content        = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $parent_comment_content = zib_comment_filters(get_comment_text($parent_comment->comment_ID), '', false);
    $comment_author         = get_comment_author($comment->comment_ID);
    $parent_comment_author  = get_comment_author($parent_comment->comment_ID);

    $message = '您好！' . $parent_comment_author . '<br>';
    $message .= '您在[<a href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]中的发表的评论，有新的回复内容' . '<br>';
    $message .= '您的评论：' . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $parent_comment_content . '</div>';
    $message .= '评论时间：' . $parent_comment->comment_date . '<br>';
    $message .= '<br>';

    $message .= '回复内容：' . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= '回复时间：' . $comment->comment_date . '<br>';
    $message .= '回复人：' . $comment_author . '<br>';
    $message .= '<br>';

    $message .= '您可以点击下方按钮查看回复内容<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">查看回复</a>' . '<br>';

    if ($parent_comment->user_id) {
        //创建站内新消息
        if (zib_msg_is_allow_receive($parent_comment->user_id, 'comment')) {
            $msg_arge = array(
                'send_user'    => 'admin',
                'receive_user' => $parent_comment->user_id,
                'type'         => 'comment',
                'title'        => $title,
                'content'      => $message,
                'meta'         => '',
                'other'        => '',
            );
            ZibMsg::add($msg_arge);
        }

        /**发送微信模板消息 */
        $c = zib_comment_filters(get_comment_text($comment->comment_ID), 'noimg', false);
        $c = zib_str_cut(trim(strip_tags($c)), 0, 17, '...');

        $wechat_template_data = array(
            'name'    => $comment_author,
            'time'    => $comment->comment_date,
            'content' => $c,
        );
        zib_wechat_template_send($parent_comment->user_id, 'comment_to_parent', $wechat_template_data, $_link);
    }

    //创建新的邮件通知
    if (_pz('email_comment_toparent', true)) {

        $email = get_comment_author_email($parent_comment);

        /**判断邮箱状态 */
        if (is_email($email) && !stristr($email, '@no')) {
            $title     = '您发表的评论已有新的回复:[' . $post_title . ']';
            /**发送邮件 */
            @wp_mail($email, $title, $message);
        }
    }

}

//用户投稿后向管理员发送邮件
add_action('new_posts_pending', 'zib_email_newpost_contribution_to_admin', 99);
function zib_email_newpost_contribution_to_admin($post)
{

    $user_id = $post->post_author;
    $udata   = get_userdata($user_id);
    /**判断是否是管理员或者作者 */
    if (in_array('administrator', $udata->roles) || in_array('roles', $udata->roles)) {
        return false;
    }

    /**判断通知状态 */
    if (get_post_meta($post->ID, 'contribution_msg_to_admin', true)) {
        return false;
    }

    $blog_name = get_bloginfo('name');
    $_link     = admin_url('/edit.php?post_status=pending&post_type=post');
    $title     = '有新的文章投稿待审核：' . zib_str_cut(trim(strip_tags($post->post_title)), 0, 20);

    $message = '有新的文章投稿待审核<br>';
    $message .= '文章标题：' . trim(strip_tags($post->post_title)) . '<br>';
    $message .= '内容摘要：<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(125, 125, 125, 0.06); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '投稿时间：' . get_the_time('Y-m-d H:i:s', $post) . '<br>';

    $send_user = 'admin';
    if (_pz('post_article_user', 1) != $user_id) {
        $send_user = $user_id;
        $message .= '投稿用户：<a target="_blank" href="' . esc_url(zib_get_user_home_url($user_id)) . '">' . $udata->display_name . '</a><br>';
    }

    $message .= '<br>';
    $message .= '您可以点击下方按钮以审核投稿文章<br>';
    $message .= '<a class="but c-blue" target="_blank" style="margin-top: 20px" href="' . esc_url($_link) . '">查看待审核文章</a>' . '<br>';

    /**发送邮件 */
    update_post_meta($post->ID, 'contribution_msg_to_admin', true);

    $msg_arge = array(
        'send_user'    => $send_user,
        'receive_user' => 'admin',
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
    );
    //创建新消息
    ZibMsg::add($msg_arge);

    if (_pz('email_newpost_contribution_to_admin', true)) {
        zib_mail_to_admin($title, $message);
    }
}

/**当投稿的文章从草稿状态变更到已发布时 */
add_action('pending_to_publish', 'zib_newmsg_pending_to_publish', 99);
function zib_newmsg_pending_to_publish($post)
{
    $user_id = $post->post_author;
    //用户是否接收
    if (!$user_id && !zib_msg_is_allow_receive($user_id, 'posts')) {
        return;
    }

    /**判断是否登录后投稿 */
    if (_pz('post_article_user', 1) == $user_id) {
        return;
    }

    $udata = get_userdata($user_id);
    /**判断是否是管理员或者作者 */
    if (in_array('administrator', $udata->roles) || in_array('roles', $udata->roles)) {
        return;
    }
    $_link      = get_permalink($post->ID);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $title = '您发布的内容已通过审核：[' . $post_title . ']';

    $message = '您好！' . $udata->display_name . '<br>';
    $message .= '您发布的内容[' . $post->post_title . ']，已经通过审核' . '<br>';
    $message .= '内容摘要：<br>';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '提交时间：' . $post->post_date . '<br>';
    $message .= '审核时间：' . $post->post_modified . '<br>';
    $message .= '<br>';

    $message .= '您可以点击下方按钮查看此内容<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">立即查看</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);
}

//帖子被删除后通知作者
add_action('bbs_posts_delete', 'zib_newmsg_trashed_post', 99);
function zib_newmsg_trashed_post($post)
{
    //用户是否接收
    if (empty($post->post_author) || empty($_REQUEST['msg_s'])) {
        return;
    }

    /**判断是否登录后投稿 */
    if ($post->post_type == 'post' && _pz('post_article_user', 1) == $post->post_author) {
        return;
    }

    $user_id = $post->post_author;
    $udata   = get_userdata($user_id);
    /**作者删除自己的的不通知 */
    if ($user_id == get_current_user_id()) {
        return;
    }

    $msg        = !empty($_REQUEST['msg']) ? trim(strip_tags($_REQUEST['msg'])) : '';
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');
    $title      = '您发布的内容已被删除：[' . $post_title . ']';

    $message = '您好！' . $udata->display_name . '<br>';
    $message .= '您发布的内容[' . $post->post_title . ']，已被删除' . '<br>';
    $message .= '内容摘要：<br>';
    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . trim(zib_str_cut(trim(strip_tags($post->post_content)), 0, 100, '...')) . '</div>';
    $message .= '发布时间：' . $post->post_date . '<br>';
    $message .= $msg ? '删除说明：' . $msg . '<br>' : '';
    $message .= '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);
}

//帖子或版块驳回后通知作者
add_action('publish_to_pending', 'zib_newmsg_publish_to_pending', 99);
function zib_newmsg_publish_to_pending($post)
{
    //用户是否接收
    if (empty($post->post_author) || empty($_REQUEST['msg_s'])) {
        return;
    }
    $msg     = !empty($_REQUEST['msg']) ? trim(strip_tags($_REQUEST['msg'])) : '';
    $user_id = $post->post_author;

    /**判断是否登录后投稿 */
    if ($post->post_type == 'post' && _pz('post_article_user', 1) == $user_id) {
        return;
    }

    $udata = get_userdata($user_id);
    /**作者自己驳回的不通知 */
    if ($user_id == get_current_user_id()) {
        return;
    }

    $_link      = get_permalink($post->ID);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $title = '您发布的内容被审核驳回：[' . $post_title . ']';

    $message = '您好！' . $udata->display_name . '<br>';
    $message .= '您发布的内容[' . $post->post_title . ']，已被驳回，当前为待审核状态' . '<br>';
    $message .= '内容摘要：<br>';

    $message .= '<div class="muted-box" style=" padding:10px 15px;border-radius:8px;background:rgba(141, 141, 141, 0.05); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '提交时间：' . $post->post_date . '<br>';
    $message .= '驳回时间：' . $post->post_modified . '<br>';
    $message .= $msg ? '驳回说明：' . $msg . '<br>' : '';
    $message .= '<br>';

    $message .= '您可以点击下方按钮查看此内容<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">立即查看</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );

    //创建新消息
    ZibMsg::add($msg_arge);
}

/**新的链接需要管理员审核 */
add_action('zib_ajax_frontend_links_submit_success', 'zib_newmsg_links_submit', 99);
function zib_newmsg_links_submit($data)
{
    if (!_pz('message_s', true) || $data['link_visible'] === 'Y') {
        return;
    }

    $linkdata = array(
        'link_name'        => esc_attr($data['link_name']),
        'link_url'         => esc_url($data['link_url']),
        'link_description' => !empty($data['link_description']) ? esc_attr($data['link_description']) : '无',
        'link_image'       => !empty($data['link_image']) ? '<img src="' . esc_attr($data['link_image']) . '" style=" width: 80px; height: auto; ">' : '空',
    );
    $_link = admin_url('link-manager.php?orderby=visible&order=asc');

    $title = '新的链接待审核：' . $linkdata['link_name'];

    $message = '网站有新的链接提交：<br>';
    $message .= '链接名称：' . $linkdata['link_name'] . '<br>';
    $message .= '链接地址：' . $linkdata['link_url'] . '<br>';
    $message .= '链接简介：' . $linkdata['link_description'] . '<br>';
    $message .= '链接Logo：' . $linkdata['link_image'] . '<br>';
    $message .= '<br>';

    $message .= '您可以点击下方按钮快速管理链接<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">管理链接</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => 'admin',
        'type'         => 'system',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

//文章有新的评论后给文章作者发通知的内容过滤
function zib_comment_notification_text_filter($notify_message, $comment_id)
{
    $comment         = get_comment($comment_id);
    $post_id         = $comment->comment_post_ID;
    $post            = get_post($post_id);
    $_link           = get_comment_link($comment);
    $comment_author  = get_comment_author($comment->comment_ID);
    $post_link       = get_permalink($comment->comment_post_ID);
    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);

    $notify_message = '您好！<br>';
    $notify_message .= '您发布的[<a href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]有新的评论' . '<br>';
    $notify_message .= '评论内容：<br>';
    $notify_message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $notify_message .= '评论用户：' . $comment_author . '<br>';
    $notify_message .= '评论时间：' . $comment->comment_date . '<br>';
    $notify_message .= '<br>';
    $notify_message .= '您可以点击下方按钮查看此评论<br>';
    $notify_message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">查看评论</a>' . '<br>';

    return $notify_message;
}
add_filter('comment_notification_text', 'zib_comment_notification_text_filter', 10, 2);

/** 文章有新评论时候给作者发消息 */
add_filter('notify_post_author', 'zib_newmsg_new_comment', 10, 2);
function zib_newmsg_new_comment($maybe_notify, $comment_id)
{
    $comment = get_comment($comment_id);
    $post_id = $comment->comment_post_ID;
    $post    = get_post($post_id);

    //自己回复自己不通知
    if ($post->post_author == $comment->user_id) {
        return;
    }
    $notify_message = zib_comment_notification_text_filter('', $comment);
    $post_title     = zib_str_cut($post->post_title, 0, 20, '...');
    $title          = '有新的评论:[' . $post_title . ']';

    if (_pz('message_s', true) && zib_msg_is_allow_receive($post->post_author, 'comment')) {
        $msg_arge = array(
            'send_user'    => 'admin',
            'receive_user' => $post->post_author,
            'type'         => 'comment',
            'title'        => $title,
            'content'      => $notify_message,
            'meta'         => '',
            'other'        => '',
        );
        //创建新消息
        ZibMsg::add($msg_arge);
    }

    //发送微信模板消息
    $c                    = zib_comment_filters(get_comment_text($comment->comment_ID), 'noimg', false);
    $c                    = zib_str_cut(trim(strip_tags($c)), 0, 17, '...');
    $wechat_template_data = array(
        'name'    => get_comment_author($comment->comment_ID),
        'time'    => $comment->comment_date,
        'content' => $c,
        'post'    => $post->post_title,
    );
    zib_wechat_template_send($post->post_author, 'comment_to_postauthor', $wechat_template_data, get_comment_link($comment));

    return $maybe_notify;
}

/** 有评论待审核给管理员发消息 */
add_filter('comment_moderation_text', 'zib_newmsg_moderation_notify', 10, 2);
function zib_newmsg_moderation_notify($notify_message, $comment_id)
{

    $comment    = get_comment($comment_id);
    $post_id    = $comment->comment_post_ID;
    $post       = get_post($post_id);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $post_link       = get_permalink($comment->comment_post_ID);
    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $comment_author  = get_comment_author($comment->comment_ID);
    $_link           = get_comment_link($comment);

    $notify_message = '您好！<br>';
    $notify_message .= '在[<a href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]中有新的评论待审核' . '<br>';
    $notify_message .= '评论内容：<br>';
    $notify_message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $notify_message .= '评论用户：' . $comment_author . '<br>';
    $notify_message .= '评论时间：' . $comment->comment_date . '<br>';
    $notify_message .= '<br>';
    $notify_message .= '<a target="_blank" href="' . esc_url($_link) . '">您可以点此查看此评论</a><br>';
    $notify_message .= '也可以点击以下链接进入后台处理待审核的评论<br>';
    $notify_message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url(admin_url('edit-comments.php?comment_status=moderated#wpbody-content')) . '">后台审核</a>' . '<br>';

    $title = '有新的评论待审核:[' . $post_title . ']';
    if (_pz('message_s', true)) {
        $msg_arge = array(
            'send_user'    => 'admin',
            'receive_user' => 'admin',
            'type'         => 'comment',
            'title'        => $title,
            'content'      => $notify_message,
            'meta'         => '',
            'other'        => '',
        );
        //创建新消息
        ZibMsg::add($msg_arge);
    }
    return $notify_message;
}

/**用户文章获得点赞发消息 */
add_action('like-posts', 'zib_newmsg_post_like', 99, 3);
function zib_newmsg_post_like($post_id, $count, $user_id = 0)
{
    $post = get_post($post_id);
    //判断是否是自己操作
    if ($user_id == $post->post_author) {
        return;
    }

    if (!zib_msg_is_allow_receive($post->post_author, 'like')) {
        return;
    }

    $post_title = zib_str_cut($post->post_title, 0, 20, '...');

    $title = '您发布的内容获得点赞：[' . $post_title . ']，共计' . $count . '次点赞';

    $message = '';
    $message .= '您发布的内容获得新的点赞！' . '<br>';
    $message .= '文章：[' . $post->post_title . ']<br>';
    $message .= '共计点赞：' . $count . '次<br>';
    if ($user_id) {
        $data = get_userdata($user_id);
        $message .= '点赞用户：<a target="_blank" href="' . esc_url(zib_get_user_home_url($user_id)) . '">' . $data->display_name . '</a><br>';
    }

    $_link = get_permalink($post_id);
    $message .= '您可以点击下方按钮查看文章<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">查看文章</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $post->post_author,
        'type'         => 'like',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

/**用户评论获得点赞发消息 */
add_action('like-comment', 'zib_newmsg_comment_like', 99, 3);
function zib_newmsg_comment_like($comment_id, $count, $user_id = 0)
{

    $comment = get_comment($comment_id);

    //判断是否是自己操作
    if ($user_id == $comment->user_id) {
        return;
    }

    if (!zib_msg_is_allow_receive($comment->user_id, 'like')) {
        return;
    }

    $post_id    = $comment->comment_post_ID;
    $post       = get_post($post_id);
    $post_link  = get_permalink($post_id);
    $post_title = zib_str_cut($post->post_title, 0, 20, '...');
    $_link      = get_comment_link($comment->comment_ID);
    $title      = '您在文章[' . $post_title . ']中的评论获得点赞，共计' . $count . '次点赞';

    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);

    $message = '您好！' . get_comment_author($comment->comment_ID) . '<br>';
    $message .= '您在[<a href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]中的评论，获得新的点赞' . '<br>';
    $message .= '评论内容：' . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= '评论时间：' . $comment->comment_date . '<br>';

    $message .= '共计点赞：' . $count . '次<br>';
    if ($user_id) {
        $data = get_userdata($user_id);
        $message .= '点赞用户：<a target="_blank" href="' . esc_url(zib_get_user_home_url($user_id)) . '">' . $data->display_name . '</a><br>';
    }

    $message .= '您可以点击下方按钮查看此评论<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">查看评论</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $comment->user_id,
        'type'         => 'like',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

/**文章被收藏发消息 */
add_action('favorite-posts', 'zib_newmsg_post_favorite', 99, 3);
function zib_newmsg_post_favorite($post_id, $count, $user_id = 0)
{

    $post = get_post($post_id);
    //判断是否是自己操作
    if ($user_id == $post->post_author) {
        return;
    }

    if (!zib_msg_is_allow_receive($post->post_author, 'favorite')) {
        return;
    }

    $post_title = zib_str_cut($post->post_title, 0, 20, '...');
    $udata      = get_userdata($user_id);
    $user_name  = zib_str_cut($udata->display_name, 0, 8, '...');
    $user_url   = esc_url(zib_get_user_home_url($user_id));

    $title = '用户[' . $user_name . ']收藏了您发布的内容[' . $post_title . ']';

    $message = '';
    $message .= '有用户收藏了您发布的内容' . '<br>';
    $message .= '标题：[' . $post->post_title . ']<br>';
    $message .= '发布时间：' . $post->post_date . '<br>';

    $message .= '收藏用户：<a target="_blank" href="' . $user_url . '">' . $udata->display_name . '</a><br>';

    $_link = get_permalink($post_id);
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . esc_url($_link) . '">查看内容</a>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-red ml10" href="' . $user_url . '">查看用户</a>' . '<br>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $post->post_author,
        'type'         => 'favorite',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

/**有新的粉丝、用户被关注 */
add_action('follow-user', 'zib_newmsg_followed', 99, 4);
function zib_newmsg_followed($follow_user_id, $followed_user_id, $follow_count = 0, $followed_count = 0)
{
    if (!zib_msg_is_allow_receive($followed_user_id, 'followed')) {
        return;
    }

    //判断是否是自己操作
    if ($follow_user_id == $followed_user_id) {
        return;
    }

    $udata     = get_userdata($follow_user_id);
    $user_name = zib_str_cut($udata->display_name, 0, 8, '...');
    $user_url  = esc_url(zib_get_user_home_url($follow_user_id));

    $title = '您有新的粉丝：[' . $user_name . ']';

    $message = '';
    $message .= '有用户关注您' . '<br>';
    $message .= '用户：<a target="_blank" href="' . $user_url . '">' . $udata->display_name . '</a><br>';
    $message .= '粉丝总数：' . $followed_count . '个<br>';

    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-red mr10" href="' . $user_url . '">查看用户</a>';
    $message .= '<a target="_blank" style="margin-top: 20px;padding:5px 20px" class="but jb-blue" href="' . zib_get_user_home_url($followed_user_id, array('tab' => 'follow')) . '">查看所有粉丝</a>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $followed_user_id,
        'type'         => 'followed',
        'title'        => $title,
        'content'      => $message,
        'meta'         => '',
        'other'        => '',
    );
    //创建新消息
    ZibMsg::add($msg_arge);
}

//用户绑定手机号通知
add_action('zib_user_bind_phone', 'zib_newmsg_bind_phone', 99, 3);
function zib_newmsg_bind_phone($user_id, $new_phone, $old_phone)
{
    $udata = get_userdata($user_id);

    $blog_name = get_bloginfo('name');
    $new_phone = zib_get_hide_phone($new_phone);
    $old_phone = $old_phone ? zib_get_hide_phone($old_phone) : false;

    $title       = $old_phone ? '绑定手机号修改成功' : '手机号绑定成功';
    $action_text = $old_phone ? '由' . $old_phone . '修改为' . $new_phone . '<br><br>' : '手机号：' . $new_phone . '<br><br>';

    $message = '您好，' . $udata->display_name . '!<br>';
    $message .= '您的账号' . ($old_phone ? '绑定的手机号已修改' : '已成功绑定手机号') . '<br>';
    $message .= $action_text;
    $message .= '如非您本人操作，请及时与客服联系';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'system',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    ZibMsg::add($msg_arge);
    if (_pz('email_update_bind_phone', true)) {
        if (is_email($udata->user_email) && !stristr($udata->user_email, '@no')) {
            @wp_mail($udata->user_email, $title, $message);
        }
    }

    //发送微信模板消息
    $wechat_template_data = array(
        'name' => $udata->user_login . '（' . ($old_phone ? '手机号修改成功' : '手机号绑定成功') . '）',
        'num'  => $new_phone,
        'time' => current_time('Y-m-d H:i:s'),
    );
    zib_wechat_template_send($user_id, 'bind_phone', $wechat_template_data, zib_get_user_center_url('account'));
}

//用户绑定有邮箱通知
add_action('zib_user_bind_email', 'zib_newmsg_bind_email', 99, 3);
function zib_newmsg_bind_email($user_id, $new, $old)
{
    $udata     = get_userdata($user_id);
    $new       = zib_get_hide_email($new);
    $old       = $old ? zib_get_hide_email($old) : false;

    $title       = $old ? '邮箱绑定成功' : '绑定邮箱修改成功';
    $action_text = $old ? '由' . $old . '修改为' . $new . '<br><br>' : '绑定邮箱：' . $new;

    $message = '您好，' . $udata->display_name . '!<br>';
    $message .= '您的账号' . ($old ? '已完成邮箱绑定' : '绑定邮箱修改成功') . '<br>';
    $message .= $action_text;
    $message .= '如非您本人操作，请及时与客服联系';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'system',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    ZibMsg::add($msg_arge);

    //发送微信模板消息
    $wechat_template_data = array(
        'name' => $udata->user_login . '（' . ($old ? '邮箱修改成功' : '邮箱绑定成功') . '）',
        'time' => current_time('Y-m-d H:i:s'),
        'num'  => $new,
    );
    zib_wechat_template_send($user_id, 'bind_email', $wechat_template_data, zib_get_user_center_url('account'));
}
