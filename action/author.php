<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-28 22:46:22
 * @LastEditTime : 2025-11-29 19:59:39
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 作者页-TAB内容：评论
 * @param {*}
 * @return {*}
 */
function zib_ajax_tab_author_comment($user_id = '')
{
    $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $user_id;
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    if (!$user_id) return;

    $lists = zib_get_author_comment($user_id, $paged, $post_type);
    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_author_comment', 'zib_ajax_tab_author_comment');
add_action('wp_ajax_nopriv_author_comment', 'zib_ajax_tab_author_comment');


/**
 * @description: 作者页-TAB内容：关注和粉丝
 * @param {*}
 * @return {*}
 */
function zib_ajax_tab_author_follow($user_id = '')
{
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $user_id;

    $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $user_id;
    $type = isset($_GET['type']) ? $_GET['type'] : 'follow';

    $lists = zib_get_follow_user_list($user_id, $type, $paged);
    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_author_follow', 'zib_ajax_tab_author_follow');
add_action('wp_ajax_nopriv_author_follow', 'zib_ajax_tab_author_follow');

/**
 * @description: 作者页-TAB内容：favorite收藏的文章
 * @param {*}
 * @return {*}
 */
function zib_ajax_tab_author_favorite_posts($user_id = '')
{
    $paged = isset($_GET['favorite_paged']) ? $_GET['favorite_paged'] : 1;
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $user_id;
    if (!$user_id) return;

    $lists = zib_get_favorite_posts_lists($user_id, $paged);
    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_author_favorite_posts', 'zib_ajax_tab_author_favorite_posts');
add_action('wp_ajax_nopriv_author_favorite_posts', 'zib_ajax_tab_author_favorite_posts');
