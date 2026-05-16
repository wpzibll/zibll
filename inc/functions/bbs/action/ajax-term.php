<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:59:52
 * @LastEditTime : 2026-04-11 16:21:24
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|AJAX执行类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//引入核心文件
require_once get_theme_file_path('/inc/functions/compat/business-compat.php');

//执行删除term
function zib_bbs_ajax_term_delete()
{
    $term_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $term    = get_term($term_id);

    if (!$term_id || empty($term->term_id)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
        exit;
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('save_bbs');

    $taxonomy = $term->taxonomy;
    $name     = zib_bbs_get_taxonomy_name($taxonomy);

    //权限检查
    if (!zib_bbs_current_user_can($taxonomy . '_delete', $term_id)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '您没有删除此' . $name . '的权限')));
        exit;
    }

    //执行删除文章
    wp_delete_term($term->term_id, $taxonomy);

    $goto = zib_bbs_get_home_url();

    echo(json_encode(array('error' => 0, 'ys' => '', 'reload' => true, 'goto' => $goto, 'msg' => $name . '（' . esc_attr($term->name) . '）已删除')));
    exit;
}
add_action('wp_ajax_plate_cat_delete', 'zib_bbs_ajax_term_delete');
add_action('wp_ajax_forum_topic_delete', 'zib_bbs_ajax_term_delete');
add_action('wp_ajax_forum_tag_delete', 'zib_bbs_ajax_term_delete');

//编辑版块分类、话题、标签的弹窗
function zib_bbs_ajax_term_edit_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    switch ($action) {
        case 'plate_cat_edit_modal':
            $html = zib_bbs_edit::plate_cat($id);
            break;
        case 'forum_topic_edit_modal':
            $html = zib_bbs_edit::topic($id);
            break;
        case 'forum_tag_edit_modal':
            $html = zib_bbs_edit::tag($id);
            break;
    }

    echo '' . $html . '';
    exit;
}
add_action('wp_ajax_plate_cat_edit_modal', 'zib_bbs_ajax_term_edit_modal');
add_action('wp_ajax_forum_topic_edit_modal', 'zib_bbs_ajax_term_edit_modal');
add_action('wp_ajax_forum_tag_edit_modal', 'zib_bbs_ajax_term_edit_modal');

//编辑或者添加term
function zib_bbs_ajax_save_term()
{
    global $zib_bbs;
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;

    $taxonomy = str_replace('save_', '', $action);
    $name     = zib_bbs_get_taxonomy_name($taxonomy);
    $name     = $name ? $name : '内容';

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('save_bbs');

    //用户基本权限验证
    $term_id = isset($_REQUEST['term_id']) ? (int) $_REQUEST['term_id'] : 0;
    if ($term_id && !zib_bbs_current_user_can($taxonomy . '_edit', $term_id)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '您没有编辑此' . $name . '的权限')));
        exit;
    }

    if (!$term_id && !zib_bbs_current_user_can($taxonomy . '_add')) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '您没有创建' . $name . '的权限')));
        exit;
    }

    $title   = !empty($_POST['title']) ? strip_tags(trim($_POST['title'])) : false;
    $content = !empty($_POST['desc']) ? strip_tags(trim($_POST['desc'])) : false;
    $slug    = !empty($_POST['slug']) ? strip_tags(trim($_POST['slug'])) : false;

    //标题验证
    if (!$title) {
        echo(json_encode(array('error' => 1, 'ys' => 'warning', 'msg' => '请输入' . $name . '标题')));
        exit();
    }
    if (zib_new_strlen($title) > 10) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '标题太长，不能超过10个字')));
        exit();
    }
    if (zib_new_strlen($title) <= 1) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '标题太短！')));
        exit();
    }

    //简介验证
    if (!$content) {
        echo(json_encode(array('error' => 1, 'ys' => 'warning', 'msg' => '请输入' . $name . '简介')));
        exit();
    }
    if (zib_new_strlen($content) > 50) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '简介太长，不能超过50个字')));
        exit();
    }
    if (zib_new_strlen($content) < 5) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '简介太短！')));
        exit();
    }

    //基础信息验证完毕
    //开始单独验证
    switch ($action) {
        case 'save_plate_cat':

            break;
        case 'save_forum_topic':

            //图像判断:如果没有ID则必须要有图片
            if (empty($_FILES['file']) && !$term_id) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请选择' . $name . '图像')));
                exit();
            }
            break;
        case 'save_forum_tag':

            break;
    }

    //图片api审核
    if (!empty($_FILES['file']) && _pz('audit_upload_img')) {
        ZibAudit::ajax_image('file');
    }

    //文字API审核
    if (_pz('audit_bbs_term')) {
        ZibAudit::ajax_text($title . $content);
    }

    //保存行为
    $insert_args = array(
        'name'        => $title,
        'description' => $content,
        'slug'        => $slug,
    );

    if ($term_id) {
        $type  = 'update';
        $in_id = wp_update_term($term_id, $taxonomy, $insert_args);
    } else {
        $type  = 'add';
        $in_id = wp_insert_term($title, $taxonomy, $insert_args);
    }
    if (is_wp_error($in_id)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $in_id->get_error_message())));
        exit();
    }

    //图片处理
    $image_url = '';
    if (!empty($_FILES['file']) && !empty($in_id['term_id'])) {
        //开始上传图像
        $img_id = zib_php_upload('file', 0, false);
        if (!empty($img_id['error'])) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $img_id['msg'])));
            exit();
        }
        $image_urls = wp_get_attachment_image_src($img_id, 'full');
        $image_url  = isset($image_urls[0]) ? $image_urls[0] : false;

        zib_update_term_meta($in_id['term_id'], 'cover_image', $image_url);
    }

    //执行保存发布限制
    if (isset($_POST['add_limit'])) {
        update_term_meta($in_id['term_id'], 'add_limit', (string) $_POST['add_limit']);
    }

    //刷新固定链接
    zib_flush_rewrite_rules();

    $text = $term_id ? '编辑' : '创建';
    $goto = get_term_link($in_id['term_id']);

    $data = array(
        'image_url'  => $image_url,
        'term_url'   => $goto,
        'msg'        => $name . $text . '成功',
        'term'       => get_term($in_id['term_id'], $taxonomy),
        'term_id'    => $in_id['term_id'],
        'taxonomy'   => $taxonomy,
        'type'       => $type,
        'hide_modal' => true,
    );
    zib_send_json_success($data);
}
add_action('wp_ajax_save_plate_cat', 'zib_bbs_ajax_save_term');
add_action('wp_ajax_save_forum_topic', 'zib_bbs_ajax_save_term');
add_action('wp_ajax_save_forum_tag', 'zib_bbs_ajax_save_term');


