<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2026-04-11 15:21:16
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//审核以及驳回
function zib_ajax_approve_comment()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (empty($_POST['comment_id'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '数据传入出错')));
        exit();
    }

    $comment_id = (int) $_POST['comment_id'];
    $comment    = get_comment($comment_id);
    if (!$comment) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '查找数据出错')));
        exit();
    }

    if (!zib_current_user_can('comment_audit', $comment)) {
        zib_send_json_error('权限不足');
    }

    $current = wp_get_comment_status($comment_id);

    if ('trash' == $current || !$current) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '评论已删除')));
        exit();
    }

    if (in_array($current, array('unapproved', 'spam'), true)) {
        $result = wp_set_comment_status($comment, 'approve', true);
        $status = 'approve';
        $msg    = '此评论已审核';
    } else {
        $result = wp_set_comment_status($comment, 'hold', true);
        $status = 'hold';
        $msg    = '已驳回此评论';
    }

    if (is_wp_error($result)) {
        $data = $result->get_error_message();
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $data)));
        exit;
    }
    $send = array(
        'comment_id' => $comment_id,
        'status'     => $status,
        'msg'        => $msg,
    );

    echo(json_encode($send));
    exit();
}
add_action('wp_ajax_approve_comment', 'zib_ajax_approve_comment');

function zib_ajax_get_comment()
{
    if (empty($_POST['comment_id'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '数据传入出错，请刷新页面')));
        exit();
    }

    $comment_id = absint($_POST['comment_id']);
    $comment    = get_comment($comment_id);
    if (!$comment) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '查找数据出错，请刷新页面')));
        exit();
    }

    if (!zib_current_user_can('comment_edit', $comment)) {
        zib_send_json_error('编辑权限不足');
    }

    if ('trash' == $comment->comment_approved) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '该评论已删除，请刷新页面')));
        exit();
    }

    echo(json_encode($comment));
    exit();
}

add_action('wp_ajax_get_comment', 'zib_ajax_get_comment');
add_action('wp_ajax_nopriv_get_comment', 'zib_ajax_get_comment');

//设置评论置顶
function zib_ajax_comment_set_topping()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $id      = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $comment = get_comment($id);
    if (empty($comment->comment_ID)) {
        zib_send_json_error(['msg' => '参数错误或评论已删除']);
    }

    if ($comment->comment_parent) {
        zib_send_json_error(['msg' => '该评论无法设置置顶']);
    }

    if (!zib_current_user_can('comment_set_topping', $comment)) {
        zib_send_json_error(['msg' => '权限不足']);
    }

    //刷新缓存
    $is_topping = get_comment_meta($id, 'topping', true);
    if ($is_topping) {
        update_comment_meta($id, 'topping', 0);
        zib_send_json_success(['msg' => '已取消置顶', 'reload' => true]);
    } else {
        update_comment_meta($id, 'topping', 1);
        do_action('comment_is_topping', $comment); //添加挂钩
        //刷新缓存
        zib_send_json_success(['msg' => '已置顶此评论', 'reload' => true]);
    }

}
add_action('wp_ajax_comment_set_topping', 'zib_ajax_comment_set_topping');

//删除评论
function zib_ajax_trash_comment()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (empty($_POST['comment_id'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '数据传入出错')));
        exit();
    }
    $comment_id = absint($_POST['comment_id']);
    $comment    = get_comment($comment_id);
    if (!$comment) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '查找数据出错')));
        exit();
    }

    if (!zib_current_user_can('comment_delete', $comment)) {
        zib_send_json_error('权限不足');
    }

    if ('trash' == $comment->comment_approved) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '评论已删除')));
        exit();
    }
    if (wp_trash_comment($comment)) {
        echo(json_encode(array('msg' => '评论已删除')));
    } else {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '操作失败')));
    }
    exit;
}

add_action('wp_ajax_trash_comment', 'zib_ajax_trash_comment');
add_action('wp_ajax_nopriv_trash_comment', 'zib_ajax_trash_comment');

//提交评论、修改评论
function zib_ajax_submit_comment()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //人机验证
    if (_pz('verification_comment_s')) {
        zib_ajax_man_machine_verification();
    }

    $edit_id = !empty($_POST['edit_comment_ID']) ? absint($_POST['edit_comment_ID']) : false;
    if (empty($_POST['comment']) || zib_new_strlen($_POST['comment']) < 2) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请输入内容')));
        exit();
    }

    $current = get_current_user_id();

    //内容合规性判断
    $is_audit = false;
    if (zib_current_user_can('comment_audit_no', (!empty($_POST['comment_post_ID']) ? $_POST['comment_post_ID'] : 0))) {
        //拥有免审核权限
        $is_audit = true;
    } else {
        //快速回复免审核
        if (in_array(trim($_POST['comment']), zib_get_quick_often_items())) {
            $is_audit = true;
        }

        //API审核（拥有免审核权限的用户无需API审核）
        if (!$is_audit && _pz('audit_comment')) {
            $api_is_audit = ZibAudit::is_audit(ZibAudit::ajax_text(zib_comment_filters($_POST['comment'])));
            //API审核通过，且拥有免人工审核
            if ($api_is_audit && zib_current_user_can('comment_audit_no_manual')) {
                $is_audit = true;
            }
        }
    }

    if ($is_audit) {
        add_filter('pre_comment_approved', function () {
            return 1;
        });
    }
    //内容合规性判断结束

    //管理员不限制评论时间,等待
    $wait_time = 15;
    if (is_super_admin()) {
        $wait_time = 2;
        add_filter('wp_is_comment_flood', '__return_false', 99);
    }

    if ($edit_id) {
        //编辑评论
        $comment = get_comment($edit_id);
        if (!$comment) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '查找数据出错')));
            exit();
        }

        if (!zib_current_user_can('comment_edit', $comment)) {
            zib_send_json_error('编辑权限不足');
        }
        if ($comment->comment_content == $_POST['comment']) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '内容未修改')));
            exit;
        }
        $update_comment = wp_update_comment([
            'comment_ID'      => $edit_id,
            'comment_content' => $_POST['comment'],
        ]);

        if (!$update_comment) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '评论修改失败')));
            exit;
        }

        echo(json_encode(array('error' => 0, 'html' => zib_comment_filters($_POST['comment']), 'wait_time' => $wait_time, 'msg' => '评论已修改')));
        exit;
    }

    $comment = wp_handle_comment_submission(wp_unslash($_POST));
    if (is_wp_error($comment)) {
        $data = $comment->get_error_data();
        if (!empty($data)) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $comment->get_error_message())));
            exit;
        } else {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '评论提交失败')));
            exit;
        }
    }

    if (!$current) {
        do_action('set_comment_cookies', $comment, wp_get_current_user());
    }

    $msg = '评论已提交';
    $msg .= '0' == $comment->comment_approved ? '，正在等待审核' : '';
    $depth = $comment->comment_parent ? 0 : 1;
    echo(json_encode(array('error' => 0, 'html' => zib_get_comments_list($comment, $depth, false), 'comment' => $comment, 'wait_time' => $wait_time, 'msg' => $msg)));
    exit;
}

add_action('wp_ajax_submit_comment', 'zib_ajax_submit_comment');
add_action('wp_ajax_nopriv_submit_comment', 'zib_ajax_submit_comment');

function zib_get_quick_often_items()
{
    $items = [];
    if (_pz('bbs_comment_quick_s', true)) {
        $bbs_comment_quick_often = _pz('bbs_comment_quick_often');
        if ($bbs_comment_quick_often && isset($bbs_comment_quick_often[0]['val'])) {
            $items = array_column($bbs_comment_quick_often, 'val');
        }
    }

    if (_pz('comment_quick_s', true)) {
        $comment_quick_often = _pz('comment_quick_often');
        if ($comment_quick_often && isset($comment_quick_often[0]['val'])) {
            $items = array_column($comment_quick_often, 'val');
        }
    }

    return $items;
}
