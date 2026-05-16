<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-04-11 15:36:09
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**评论上传图片 */

/**私信上传图片 */
function zib_ajax_user_upload_image()
{
    //必须登录
    $cuid = get_current_user_id();
    if (!$cuid) {
        echo(json_encode(array('error' => 1, 'error_id' => 'nologged', 'ys' => 'danger', 'msg' => '请先登录')));
        exit;
    }

    if (!wp_verify_nonce($_POST['upload_image_nonce'], 'upload_image')) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '安全验证失败，请刷新页面后重试')));
        exit();
    }

    //开始上传
    $img_id = zib_php_upload();
    if (!empty($img_id['error'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $img_id['msg'])));
        exit();
    }

    $size    = !empty($_REQUEST['size']) ? $_REQUEST['size'] : 'large';
    $img_url = wp_get_attachment_image_src($img_id, $size)[0];

    echo(json_encode(array('error' => '', 'ys' => '', 'msg' => '图片已上传', 'img_url' => $img_url)));
    exit();
}
add_action('wp_ajax_user_upload_image', 'zib_ajax_user_upload_image');
add_action('wp_ajax_nopriv_user_upload_image', 'zib_ajax_user_upload_image');

//下载文件
function zib_ajax_download_file()
{
    $file_id = isset($_GET['file']) ? (int) $_GET['file'] : 0;

    //执行安全验证检查
    if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'download_file_' . $file_id)) {
        _default_wp_die_handler('下载链接已过期，请刷新页面重新下载！', '下载链接已过期');
    }

    $attachment = get_post($file_id);

    if (!isset($attachment->ID) || 'attachment' !== $attachment->post_type) {
        _default_wp_die_handler('抱歉！文件不存在或已删除！', '文件不存在');
    }

    //储存下载次数
    $download_amount = (int) get_post_meta($file_id, 'download_amount', true);
    update_post_meta($file_id, 'download_amount', $download_amount + 1);

    $file_path = get_attached_file($file_id);

    //本地文件下载
    zib_download_local_file($file_path);

    //跳转到附件地址
    $attachment_url = wp_get_attachment_url($attachment->ID);
    header('location:' . $attachment_url);
    echo '<html><head><meta name="robots" content="noindex, nofollow"><script>location.href = "' . $attachment_url . '";</script></head><body></body></html>';
    exit;
}
add_action('wp_ajax_download_file', 'zib_ajax_download_file');
add_action('wp_ajax_nopriv_download_file', 'zib_ajax_download_file');

//前台分片上传文件
function zib_ajax_user_split_upload()
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

    //准备资料
    $file_type                = isset($_REQUEST['file_type']) ? $_REQUEST['file_type'] : '';
    $file_size                = isset($_REQUEST['file_size']) ? (int) $_REQUEST['file_size'] : 0;
    $split_chunks_count       = isset($_REQUEST['split_chunks_count']) ? (int) $_REQUEST['split_chunks_count'] : 0;
    $split_current_chunk      = isset($_REQUEST['split_current_chunk']) ? (int) $_REQUEST['split_current_chunk'] : 0;
    $split_current_chunk_part = $split_current_chunk + 1;
    $file_name                = isset($_REQUEST['file_name']) ? $_REQUEST['file_name'] : $_FILES[$file_id]['file_name'];
    $temp_dir                 = ZIB_TEMP_DIR;

    if (!$file_size) {
        zib_send_json_error('文件大小获取失败，请重新选择文件');
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒(只检查前两个分片)
    if ($split_current_chunk < 1) {
        zib_ajax_verify_nonce('user_upload');
    }

    switch ($file_type) {
        case 'image':

            $max_size = zib_get_current_user_can_number('upload_img_size', 3);
            //文件大小判断
            if ($file_size > $max_size * 1048567) {
                zib_send_json_error('图片大小超过限制，最大' . $max_size . 'M，请重新选择');
            }

            break;

        case 'video':

            $max_size = zib_get_current_user_can_number('upload_video_size', 30);
            //文件大小判断
            if ($file_size > $max_size * 1048567) {
                zib_send_json_error('视频大小超过限制，最大' . $max_size . 'M，请重新选择');
            }

            break;

        default:

            $max_size = zib_get_current_user_can_number('upload_file_size', 30);
            //文件大小判断
            if ($file_size > $max_size * 1048567) {
                zib_send_json_error('文件大小超过限制，最大' . $max_size . 'M，请重新选择');
            }

            break;
    }

    //分片判断结束
    //创建缓存目录
    // 仅在第一个块上运行
    if ($split_current_chunk === 0) {
        zib_file_chunk::chunk_save_init();
    }

    $file_name_sha1 = md5($file_name);
    $blog_id        = get_current_blog_id();

    //判断文件大小
    $all_part_size  = filesize($_FILES[$file_id]['tmp_name']);
    $uploaded_parts = glob(sprintf('%s/%d-%s-%s.part', $temp_dir, $blog_id, $file_name_sha1, '*'));
    if ($uploaded_parts && is_array($uploaded_parts)) {
        foreach ($uploaded_parts as $part) {
            $all_part_size += filesize($part);
        }
        if ($all_part_size > $max_size * 1048567) {
            zib_send_json_error('文件大小超过限制，最大' . $max_size . 'M，请重新选择');
        }
    }

    //移动文件
    $chunk_part    = sprintf('%d-%s-%d.part', $blog_id, $file_name_sha1, $split_current_chunk);
    $chunk_path    = sprintf('%s/%s', $temp_dir, $chunk_part);
    $move_new_file = move_uploaded_file($_FILES[$file_id]['tmp_name'], $chunk_path);

    if (!$move_new_file) {
        $move_new_file = copy($_FILES[$file_id]['tmp_name'], $chunk_path);
        @unlink($_FILES[$file_id]['tmp_name']);
    }

    if (!$move_new_file) {
        zib_send_json_error(['msg' => '文件移动失败，请重新选择', 'move_new_file' => $move_new_file, 'chunk_path' => $chunk_path]);
    }

    //准备返回数据
    $send_data = array(
        'progress'      => round(($split_current_chunk_part / $split_chunks_count) * 100, 2) . '%',
        'file_size'     => $file_size,
        'uploaded_size' => $all_part_size,
        'chunk_part'    => $chunk_part,
        'file_name'     => $file_name,
        'current_chunk' => $split_current_chunk,
        'chunks_count'  => $split_chunks_count,
        'file_name_md5' => $file_name_sha1,
        'file_md5'      => md5_file($chunk_path),
    );

    zib_send_json_success($send_data);
}
add_action('wp_ajax_user_split_upload', 'zib_ajax_user_split_upload');

/**
 * ajax合并分片
 * @param {*}
 * @return {*}
 */
function zib_ajax_user_split_upload_merge()
{

    //必须登录
    $cuid = get_current_user_id();
    if (!$cuid) {
        zib_send_json_error('登录失效，请刷新页面重新登录');
    }

    //执行安全验证检查：合并动作同样必须校验 nonce，避免登录态下被 CSRF 触发。
    zib_ajax_verify_nonce('user_upload');

    //准备资料
    $file_id            = 'file';
    $split_chunks_count = isset($_REQUEST['split_chunks_count']) ? (int) $_REQUEST['split_chunks_count'] : 0;
    $file_name          = isset($_REQUEST['file_name']) ? $_REQUEST['file_name'] : '';
    $temp_dir           = ZIB_TEMP_DIR;
    $file_name_sha1     = md5($file_name);
    $blog_id            = get_current_blog_id();

    //最后一个分片上传，合并文件
    $target_file = sprintf('%s/%d-%s-%s', $temp_dir, $blog_id, $file_name_sha1, 'merge.part'); //合并后的文件路径
    $fp          = fopen($target_file, 'ab'); //以写入的形式打开文件
    for ($i = 0; $i <= $split_chunks_count - 1; $i++) {
        $chunkFile = sprintf('%s/%d-%s-%d.part', $temp_dir, $blog_id, $file_name_sha1, $i);
        $chunkData = @file_get_contents($chunkFile);
        if (!$chunkData || !file_exists($chunkFile)) {
            //如果缺少分片
            @fclose($fp);
            @unlink($target_file);

            $uploaded_md5_data = zib_get_split_uploaded_md5_data($file_name, $split_chunks_count);
            zib_send_json_success(['msg' => '文件缺少分片，正在重新上传缺失分片', 'ys' => 'warning', 'chunk_loss' => true, 'progress' => ($uploaded_md5_data['uploaded_count'] / $split_chunks_count), 'uploaded_chunks' => $uploaded_md5_data['uploaded_chunks'], 'uploaded_count' => $uploaded_md5_data['uploaded_count'], 'chunkFile' => $chunkFile]);
        }

        fwrite($fp, $chunkData);
    }
    fclose($fp);

    //重新创建 $_FILES 全局变量并传递给 WordPress
    $_FILES[$file_id]['tmp_name'] = $target_file;
    $_FILES[$file_id]['name']     = $file_name;
    $_FILES[$file_id]['size']     = filesize($_FILES[$file_id]['tmp_name']);
    $wp_filetype                  = wp_check_filetype_and_ext($target_file, $file_name);
    $_FILES[$file_id]['type']     = $wp_filetype['type'];

    //合并时间
    $merge_timer_stop = timer_stop(0, 6) * 1000;
    //开始上传
    $upload_id = zib_php_upload($file_id, 0, 'auto', '', true);

    if (!empty($upload_id['error'])) {
        zib_send_json_error(['msg' => $upload_id['msg'], '_FILES' => $_FILES[$file_id]]);
    }

    //上传成功，删除分片
    for ($i = 0; $i <= $split_chunks_count - 1; $i++) {
        $unlink_part = sprintf('%s/%d-%s-%d.part', $temp_dir, $blog_id, $file_name_sha1, $i);
        @unlink($unlink_part);
    }

    $attachment_data                     = zib_prepare_attachment_for_js($upload_id);
    $attachment_data['merge_timer_stop'] = $merge_timer_stop;
    $attachment_data['timer_stop']       = timer_stop(0, 6) * 1000;
    zib_send_json_success($attachment_data);
}
add_action('wp_ajax_user_split_upload_merge', 'zib_ajax_user_split_upload_merge');

/**
 * ajax获取已经上传过的分片信息
 * @param {*}
 * @return {*}
 */
function zib_ajax_user_split_uploaded_chunk()
{
    $file_name          = isset($_REQUEST['file_name']) ? $_REQUEST['file_name'] : '';
    $split_chunks_count = isset($_REQUEST['split_chunks_count']) ? (int) $_REQUEST['split_chunks_count'] : 0;

    //必须登录
    $cuid = get_current_user_id();
    if (!$cuid) {
        zib_send_json_error('登录失效，请刷新页面重新登录');
    }

    if (!$file_name || $split_chunks_count < 1) {
        zib_send_json_error('参数异常');
    }

    zib_ajax_verify_nonce('user_upload');

    $md5_data = zib_get_split_uploaded_md5_data($file_name, $split_chunks_count);

    zib_send_json_success($md5_data);
}
add_action('wp_ajax_user_split_uploaded_chunk', 'zib_ajax_user_split_uploaded_chunk');

/**
 * 获取已经上传过的分片信息
 * @param $file_name 文件名
 * @param $count 分片总数
 * @return array 已上传的分片信息
 */
function zib_get_split_uploaded_md5_data($file_name, $count)
{

    $temp_dir       = ZIB_TEMP_DIR;
    $file_name_sha1 = md5($file_name);
    $blog_id        = get_current_blog_id();
    $result         = array(
        'uploaded_chunks' => [],
        'uploaded_count'  => 0,
    );

    //验证前台传入的已上传切片MD5是否与后台计算的一致，一致则表示切片上传成功
    for ($i = 0; $i <= $count - 1; $i++) {
        $ed_chunk_path = sprintf('%s/%d-%s-%d.part', $temp_dir, $blog_id, $file_name_sha1, $i);
        if (file_exists($ed_chunk_path)) {
            $result['uploaded_chunks']['chunk_' . $i] = md5_file($ed_chunk_path);
        }
    }

    $result['uploaded_count'] = count($result['uploaded_chunks']);
    return $result;
}
