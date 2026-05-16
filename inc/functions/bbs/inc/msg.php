<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-07 16:51:48
 * @LastEditTime : 2025-12-26 18:59:24
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|消息功能相关
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//回答被采纳推送消息
add_action('answer_adopted', 'zib_bbs_answer_adopted_msg', 10, 2);
function zib_bbs_answer_adopted_msg($comment, $desc = '')
{

    if (!$comment->user_id) {
        return;
    }

    //只通知一次
    $is_notify = zib_get_comment_meta($comment->comment_ID, 'is_adopted_notify', true);
    if ($is_notify) {
        return;
    }

    $_link      = get_comment_link($comment->comment_ID);
    $post       = get_post($comment->comment_post_ID);
    $post_title = zib_str_cut($post->post_title, 0, 16, '...');

    $post_link = get_permalink($comment->comment_post_ID);

    $title = '您发表的回答已被采纳：提问[' . $post_title . ']';

    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), 'noimg', false);
    $message         = '您好！' . get_comment_author($comment->comment_ID) . '<br>';
    $message .= '您在提问[<a class="muted-color" href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]中的回答，已被采纳' . '<br>';
    $message .= '回答内容：' . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= '回答时间：' . $comment->comment_date . '<br>';
    $message .= '采纳时间：' . current_time('Y-m-d H:i:s') . '<br>';
    $message .= '<br>';
    $message .= $desc ? '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $desc . '</div>' : '';
    $message .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($_link) . '">查看回答</a>' . "<br>";

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
    zib_update_comment_meta($comment->comment_ID, 'is_adopted_notify', true);

    //发送邮件
    if (_pz('email_bbs_answer_adopted', true)) {
        //发送邮件
        $userdata = get_userdata($comment->user_id);
        if (is_email($userdata->user_email) && !stristr($userdata->user_email, '@no')) {
            @wp_mail($userdata->user_email, $title, $message);
        }
    }
}

//热门评论消息
add_action('comment_is_hot', 'zib_bbs_comment_is_hot_msg');
function zib_bbs_comment_is_hot_msg($comment)
{

    if (!$comment->user_id) {
        return;
    }

    //只通知一次
    $is_notify = zib_get_comment_meta($comment->comment_ID, 'is_hot_notify', true);
    if ($is_notify) {
        return;
    }

    global $zib_bbs;
    $_link      = get_comment_link($comment->comment_ID);
    $post       = get_post($comment->comment_post_ID);
    $post_title = zib_str_cut($post->post_title, 0, 16, '...');

    $post_link = get_permalink($comment->comment_post_ID);

    $title = '您发表的评论已成为热门评论：' . $zib_bbs->posts_name . '[' . $post_title . ']';

    $comment_content = zib_comment_filters(get_comment_text($comment->comment_ID), '', false);
    $message         = '您好！' . get_comment_author($comment->comment_ID) . '<br>';
    $message .= '您在' . $zib_bbs->posts_name . '[<a class="muted-color" href="' . esc_url($post_link) . '">' . $post->post_title . '</a>]中的评论，已成为热门评论' . '<br>';
    $message .= '评论内容：' . '<br>';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(141, 141, 141, 0.05); line-height: 1.7;">' . $comment_content . '</div>';
    $message .= '评论时间：' . $comment->comment_date . '<br>';
    $message .= '<br>';
    $message .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($_link) . '">查看评论</a>' . "<br>";

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $comment->user_id,
        'type'         => 'hot',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    if (zib_msg_is_allow_receive($comment->user_id, 'comment')) {
        zib_update_comment_meta($comment->comment_ID, 'is_hot_notify', true);
        ZibMsg::add($msg_arge);
    }
};

//热门帖子||热门版块
function zib_bbs_new_msg_is_hot($post)
{
    if (!$post->post_author) {
        return;
    }
    $user_id  = $post->post_author;
    $userdata = get_userdata($user_id);
    if (empty($userdata->display_name)) {
        return;
    }

    //只通知一次
    $is_notify = zib_get_post_meta($post->ID, 'is_hot_notify', true);
    if ($is_notify) {
        return;
    }

    global $zib_bbs;
    if ('plate' === $post->post_type) {
        $name = $zib_bbs->plate_name;
    } else {
        $name = $zib_bbs->posts_name;
    }
    $post_title = zib_str_cut($post->post_title, 0, 16, '...');
    $title      = '您的' . $name . '[' . $post_title . ']已成为热门' . $name;

    $message = '您好！' . zib_get_user_name_link($user_id) . '<br>';
    $message .= $title . '<br>';
    $message .= '时间：' . current_time("Y-m-d H:i:s") . "<br>";
    $message .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . get_permalink($post) . '">查看' . $name . '</a>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'hot',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    if (zib_msg_is_allow_receive($user_id, 'hot')) {
        zib_update_post_meta($post->ID, 'is_hot_notify', true);
        ZibMsg::add($msg_arge);
    }

}
add_action('posts_is_hot', 'zib_bbs_new_msg_is_hot');
add_action('plate_is_hot', 'zib_bbs_new_msg_is_hot');

//精华帖子消息
function zib_bbs_posts_essence_msg($post, $val)
{
    $post = get_post($post);
    if (empty($post->post_author) || !$val) {
        return;
    }

    $user_id  = $post->post_author;
    $userdata = get_userdata($user_id);
    if (empty($userdata->display_name)) {
        return;
    }
    global $zib_bbs;
    $name = $zib_bbs->posts_name;

    $post_title  = zib_str_cut($post->post_title, 0, 16, '...');
    $title       = '您的' . $name . '[' . $post_title . ']被评为精华' . $name;
    $operator_id = get_current_user_id();

    $message = '您好！' . zib_get_user_name_link($user_id) . '<br>';
    $message .= $title . '<br>';
    $message .= '操作用户：' . zib_get_user_name_link($operator_id) . "<br>";
    $message .= '操作时间：' . current_time("Y-m-d H:i:s") . "<br>";
    $message .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . get_permalink($post) . '">查看' . $name . '</a>';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'hot',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    if (zib_msg_is_allow_receive($user_id, 'hot')) {
        ZibMsg::add($msg_arge);
    }
}
add_action('bbs_posts_essence_set', 'zib_bbs_posts_essence_msg', 10, 2);
