<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-15 23:13:27
 * @LastEditTime : 2025-07-25 15:02:39
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|ajax加密文件函数2
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取我的文件明细
function zib_ajax_current_user_attachments()
{
    //必须登录
    $cuid = get_current_user_id();

    if (!$cuid) {
        zib_send_json_error('登录失效，请刷新页面重新登录');
    }

    $post_mime_type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'image';
    $orderby        = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'date';
    $order          = !empty($_REQUEST['order']) && $_REQUEST['order'] === 'ASC' ? 'ASC' : 'DESC';
    $exclude        = !empty($_REQUEST['exclude']) ? $_REQUEST['exclude'] : false;
    $search         = !empty($_REQUEST['search']) ? $_REQUEST['search'] : false;

    $posts_per_page = 48;
    $paged          = zib_get_the_paged();
    $query          = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit,private',
        'paged'          => $paged,
        'author'         => $cuid,
        'orderby'        => $orderby,
        'order'          => $order,
        'posts_per_page' => $posts_per_page,
        'post__not_in'   => $exclude,
    );

    if ($search) {
        $query['s'] = $search;
        add_filter('wp_allow_query_attachment_by_filename', '__return_true');
    }

    if ($post_mime_type !== 'file') {
        $query['post_mime_type'] = $post_mime_type;
    }

    if (is_super_admin($cuid)) {
        unset($query['author']);
    }

    $attachments_query = new WP_Query($query);

    $posts       = array_map('zib_prepare_attachment_for_js', $attachments_query->posts);
    $posts       = array_filter($posts);
    $total_posts = $attachments_query->found_posts;
    $max_pages   = ceil($total_posts / $posts_per_page);

    $send_data = array(
        'lists'       => $posts,
        'all_pages'   => $max_pages,
        'all_count'   => $total_posts,
        'query'       => $query,
        'num_queries' => get_num_queries(),
        'timer_stop'  => timer_stop(0, 6) * 1000 . 'ms',
    );

    zib_send_json_success($send_data);
}
add_action('wp_ajax_current_user_attachments', 'zib_ajax_current_user_attachments');
add_action('wp_ajax_nopriv_current_user_attachments', 'zib_ajax_current_user_attachments');

//前台上传文件
function zib_ajax_user_upload_file()
{

    $file_id = 'file';
    if (empty($_FILES[$file_id])) {
        zib_send_json_error('上传信息错误，请重新选择文件');
    }

    //必须登录
    $cuid = get_current_user_id();

    if (!$cuid) {
        zib_send_json_error('登录失效，请刷新页面重新登录');
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $file_type = !empty($_REQUEST['file_type']) ? $_REQUEST['file_type'] : '';

    if ($file_type == 'file') {

        if (false !== strpos($_FILES[$file_id]['type'], '/')) {
            list($type, $subtype) = explode('/', $_FILES[$file_id]['type']);
        } else {
            list($type, $subtype) = array($_FILES[$file_id]['type'], '');
        }

        $file_type = $type;
    }

    switch ($file_type) {
        case 'image':
            //文件类型判断
            if (!stristr($_FILES[$file_id]['type'], 'image')) {
                zib_send_json_error('文件不属于图片格式');
            }

            $max_size = zib_get_current_user_can_number('upload_img_size', 3);
            //文件大小判断
            if ($_FILES[$file_id]['size'] > $max_size * 1024000) {
                zib_send_json_error('图片大小超过限制，最大' . $max_size . 'M，请重新选择');
            }

            break;

        case 'video':

            //文件类型判断
            if (!stristr($_FILES[$file_id]['type'], 'video')) {
                zib_send_json_error('文件不属于视频格式');
            }

            $max_size = zib_get_current_user_can_number('upload_video_size', 30);
            //文件大小判断
            if ($_FILES[$file_id]['size'] > $max_size * 1024000) {
                zib_send_json_error('视频大小超过限制，最大' . $max_size . 'M，请重新选择');
            }

            break;

        default:

            $max_size = zib_get_current_user_can_number('upload_file_size', 30);
            //文件大小判断
            if ($_FILES[$file_id]['size'] > $max_size * 1024000) {
                zib_send_json_error('文件大小超过限制，最大' . $max_size . 'M，请重新选择');
            }

            break;
    }

    //开始上传
    $upload_id = zib_php_upload();

    if (!empty($upload_id['error'])) {
        zib_send_json_error($upload_id['msg']);
    }

    $attachment_data = zib_prepare_attachment_for_js($upload_id);

    zib_send_json_success($attachment_data);
}
add_action('wp_ajax_user_upload', 'zib_ajax_user_upload_file');
add_action('wp_ajax_nopriv_user_upload', 'zib_ajax_user_upload_file');

//ajax搜索框
function zib_ajax_search_box()
{
    echo zib_get_main_search();
    exit;
}
add_action('wp_ajax_search_box', 'zib_ajax_search_box');
add_action('wp_ajax_nopriv_search_box', 'zib_ajax_search_box');
