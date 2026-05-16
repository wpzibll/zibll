<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2026-04-29 21:01:41
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

require dirname(__FILE__) . '/../../../../wp-load.php';
$key     = checkpost('key');
$post_id = checkpost('pid');
$type    = checkpost('type');
$is_like = checkpost('is_like');
$wpnonce = checkpost('_wpnonce');
if (!wp_verify_nonce($wpnonce, 'post_action')) {
    print_r(json_encode(array('error' => 1, 'msg' => '环境异常，请刷新页面后稍候再试')));
    exit();
}

switch ($type) {
    case 'comment_like':
        posts_action('like-comment', $post_id, $key, '已赞！感谢您的支持', '点赞已取消', true);
        exit();
        break;

    case 'like':
        posts_action('like-posts', $post_id, $key, '已赞！感谢您的支持', '点赞已取消');
        exit();
        break;

    case 'favorite':
        if (!is_user_logged_in()) {
            print_r(json_encode(array('error' => 1, 'pid' => $post_id, 'type' => $type, 'msg' => '请先登录')));
            exit();
            break;
        }
        posts_action('favorite-posts', $post_id, $key, '已收藏此文章', '已取消收藏');
        exit();
        break;

    case 'follow_user':
        if (!is_user_logged_in()) {
            print_r(json_encode(array('error' => 1, 'pid' => $post_id, 'type' => $type, 'msg' => '请先登录')));
            exit();
            break;
        }
        follow_action('follow-user', 'followed-user', $post_id);
        exit();
        break;
}
exit();

/**
 * @description: 关注用户
 * @param {*}
 * @return {*}
 */
function follow_action($_name, $ed_name, $_ed_id, $add_msg = '已关注此用户', $rem_msg = '已取消关注此用户')
{
    $user_meta    = false;
    $is_in_meta   = false;
    $user_id      = get_current_user_id();
    $user_meta    = (array) zib_get_user_meta($user_id, $_name, true);
    $ed_user_meta = (array) zib_get_user_meta($_ed_id, $ed_name, true);
    if ($user_meta) {
        $is_in_meta = in_array($_ed_id, $user_meta);
    }

    if (!$user_meta || !$is_in_meta) {
        if (!$user_meta) {
            $user_meta = array($_ed_id);
        } else {
            $user_meta[] = $_ed_id;
        }
        if (!$ed_user_meta) {
            $ed_user_meta = array($user_id);
        } else {
            $ed_user_meta[] = $user_id;
        }

        $ed_user_meta = array_unique($ed_user_meta);
        $user_meta    = array_unique($user_meta);

        zib_update_user_meta($user_id, $_name, $user_meta); //明细
        zib_update_user_meta($_ed_id, $ed_name, $ed_user_meta); //明细

        //更新缓存，更新用户meta数据
        wp_cache_delete($user_id, 'user_follow_count');
        wp_cache_delete($_ed_id, 'user_followed_count');

        update_user_meta($user_id, $_name . '-count', zib_get_user_follow_count($user_id, 'follow'));
        update_user_meta($_ed_id, $ed_name . '-count', zib_get_user_follow_count($_ed_id, 'followed'));

        //添加处理挂钩
        do_action('follow-user', $user_id, $_ed_id, count($user_meta), count($ed_user_meta));

        print_r(json_encode(array('error' => 0, 'action' => 'add', 'follow-user' => $user_meta, 'followed-user' => $ed_user_meta, 'msg' => $add_msg, 'cuont' => '<i class="fa fa-heart mr6" aria-hidden="true"></i>已关注')));
        exit;
    }

    if ($is_in_meta) {
        //取消关注
        $h = array_search($_ed_id, $user_meta);
        unset($user_meta[$h]);
        $h2 = array_search($user_id, $ed_user_meta);
        unset($ed_user_meta[$h2]);

        $ed_user_meta = array_unique($ed_user_meta);
        $user_meta    = array_unique($user_meta);

        zib_update_user_meta($user_id, $_name, $user_meta);
        zib_update_user_meta($_ed_id, $ed_name, $ed_user_meta);

        //更新缓存，更新用户meta数据
        wp_cache_delete($user_id, 'user_follow_count');
        wp_cache_delete($_ed_id, 'user_followed_count');

        update_user_meta($user_id, $_name . '-count', zib_get_user_follow_count($user_id, 'follow'));
        update_user_meta($_ed_id, $ed_name . '-count', zib_get_user_follow_count($_ed_id, 'followed'));

        print_r(json_encode(array('error' => 0, 'action' => 'remove', 'follow-user' => $user_meta, 'followed-user' => $ed_user_meta, 'msg' => $rem_msg, 'cuont' => '<i class="fa fa-heart-o mr6" aria-hidden="true"></i>关注')));
        exit;
    }
    exit;
}

function posts_action($user_meta_name, $post_id, $key, $add_msg = '已完成', $rem_msg = '已取消', $is_comment = false)
{
    $user_meta  = array();
    $is_in_meta = false;
    $user_id    = get_current_user_id();

    if ($user_id) {
        $user_meta = (array) zib_get_user_meta($user_id, $user_meta_name, true);
        if ($user_meta) {
            $is_in_meta = in_array($post_id, $user_meta);
        }
    }
    if (!$user_meta || !$is_in_meta) {
        if (!$user_meta) {
            $user_meta = array($post_id);
        } else {
            $user_meta[] = $post_id;
        }

        action_update_meta($user_meta_name, $user_meta);
        if ($is_comment) {
            $g = (int) get_comment_meta($post_id, $key, true);
        } else {
            $g = (int) get_post_meta($post_id, $key, true);
        }
        if (!$g) {
            $g = 0;
        }
        $count = $g + 1;
        $count = $count < 1 ? 0 : $count;
        if ($is_comment) {
            update_comment_meta($post_id, $key, $count);
        } else {
            update_post_meta($post_id, $key, $count);
        }
        //添加处理挂钩
        do_action($user_meta_name, $post_id, $count, $user_id);
        print_r(json_encode(array('error' => 0, 'action' => 'add', 'post_id' => $post_id, '_post' => $_POST, 'key' => $key, 'is_in_meta' => $is_in_meta, 'user_meta' => $user_meta, 'msg' => $add_msg, 'cuont' => $count)));
        exit;
    }
    if ($is_in_meta) {
        $h = array_search($post_id, $user_meta);
        unset($user_meta[$h]);
        action_update_meta($user_meta_name, $user_meta);
        if ($is_comment) {
            $g = (int) get_comment_meta($post_id, $key, true);
        } else {
            $g = (int) get_post_meta($post_id, $key, true);
        }
        $count = $g - 1;
        $count = $count < 1 ? 0 : $count;

        if ($is_comment) {
            update_comment_meta($post_id, $key, $count);
        } else {
            update_post_meta($post_id, $key, $count);
        }
        print_r(json_encode(array('error' => 0, 'action' => 'remove', 'key' => $key, '_post' => json_encode($_POST), 'user_meta' => $user_meta, 'msg' => $rem_msg, 'cuont' => $count)));
        exit;
    }
    exit;
}
function action_update_meta($user_meta_name, $value)
{
    $user_id = get_current_user_id();
    if ($user_id) {
        $value = array_unique($value);
        zib_update_user_meta($user_id, $user_meta_name, $value);
    }
}
function checkpost($j)
{
    return isset($_POST[$j]) ? trim(htmlspecialchars($_POST[$j], ENT_QUOTES)) : '';
}
function isInStr($k, $l)
{
    $k = '-_-!' . $k;
    return (bool) strpos($k, $l);
}
