<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:59:52
 * @LastEditTime : 2026-04-11 16:33:16
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|AJAX执行类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//用户个人主页显示版块列表
function zib_bbs_ajax_user_plate_lists()
{

    $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    $paged   = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $status  = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '';

    $lists = zib_bbs_get_user_plate_lists($user_id, $paged, $orderby, $status);
    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_author_plate', 'zib_bbs_ajax_user_plate_lists');
add_action('wp_ajax_nopriv_author_plate', 'zib_bbs_ajax_user_plate_lists');

//用户个人主页显示版块列表
function zib_bbs_ajax_user_posts_lists()
{

    $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    $paged   = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $status  = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '';

    $lists = zib_bbs_get_user_posts_lists($user_id, $paged, $orderby, $status);
    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_author_forum_posts', 'zib_bbs_ajax_user_posts_lists');
add_action('wp_ajax_nopriv_author_forum_posts', 'zib_bbs_ajax_user_posts_lists');

//版主管理
function zib_bbs_ajax_moderator_edit_modal()
{
    $id   = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';

    //权限判断
    $cap = 'cat' === $type ? 'cat_moderator_edit' : 'moderator_edit';
    if (!zib_bbs_current_user_can($cap, $id)) {
        zib_ajax_notice_modal('danger', '权限不足');
    }

    echo zib_bbs_get_moderator_edit_modal($type, $id);
    exit;
}
add_action('wp_ajax_moderator_edit_modal', 'zib_bbs_ajax_moderator_edit_modal');

//查看版主的弹窗
function zib_bbs_ajax_moderator_modal()
{
    $id   = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';

    echo zib_bbs_get_moderator_modal($type, $id);
    exit;
}
add_action('wp_ajax_nopriv_moderator_modal', 'zib_bbs_ajax_moderator_modal');
add_action('wp_ajax_moderator_modal', 'zib_bbs_ajax_moderator_modal');

//版主添加
function zib_bbs_ajax_moderator_add_modal()
{
    $id   = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';
    $cap  = 'cat' === $type ? 'cat_moderator_add' : 'moderator_add';
    //权限判断
    if (!zib_bbs_current_user_can($cap, $id)) {
        zib_ajax_notice_modal('danger', '权限不足');
    }

    echo zib_bbs_get_moderator_add_modal($id, $type);
    exit;
}
add_action('wp_ajax_moderator_add_modal', 'zib_bbs_ajax_moderator_add_modal');

//版主添加的时候，搜索用户
function zib_bbs_ajax_moderator_add_search()
{
    $id   = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $s    = !empty($_POST['s']) ? strip_tags(trim($_POST['s'])) : '';
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';
    $cap  = 'cat' === $type ? 'cat_moderator_add' : 'moderator_add';

    $lists = zib_bbs_get_moderator_add_search_user($type, $id, $s);
    if ($lists) {
        zib_send_json_success(array('data' => $lists, 'remind' => '搜索到以下用户'));
    } else {
        zib_send_json_success(array('data' => zib_get_null('', 40, 'null-user.svg', '', 0, 150), 'remind' => '未找到相关用户'));
    }
    exit;
}
add_action('wp_ajax_moderator_add_search', 'zib_bbs_ajax_moderator_add_search');

//将用户设置为版主的确认弹窗
function zib_bbs_ajax_moderator_add_user_modal()
{
    global $zib_bbs;
    $plate_name = $zib_bbs->plate_name;
    $user_id    = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $id         = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $type       = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';

    if ('cat' === $type) {
        if (zib_bbs_is_the_cat_moderator($id, $user_id, true)) {
            zib_ajax_notice_modal('warning', '该用户已是该' . $plate_name . '分类的' . $zib_bbs->cat_moderator_name);
        }
    } else {
        //是否是版主判断
        if (zib_bbs_is_the_moderator($id, $user_id)) {
            zib_ajax_notice_modal('warning', '该用户已是该' . $plate_name . $zib_bbs->plate_moderator_name);
        }
    }

    echo zib_bbs_get_moderator_add_user_modal($type, $id, $user_id);
    exit;
}
add_action('wp_ajax_moderator_add_user_modal', 'zib_bbs_ajax_moderator_add_user_modal');

//处理为版块添加版主
function zib_bbs_ajax_moderator_add_user()
{
    $id      = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $desc    = !empty($_POST['desc']) ? strip_tags(trim($_POST['desc'])) : '';
    $type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';

    //执行安全验证
    zib_ajax_verify_nonce();

    if ('cat' === $type) {
        $obj = get_term($id);
        if (empty($obj->term_id)) {
            zib_send_json_error('参数错误或内容已删除');
        }
        //权限检查
        if (!zib_bbs_current_user_can('cat_moderator_add', $obj, $user_id)) {
            zib_send_json_error('暂无添加权限');
        }
    } else {
        $obj = get_post($id);
        if (empty($obj->ID)) {
            zib_send_json_error('参数错误或内容已删除');
        }
        //权限检查
        if (!zib_bbs_current_user_can('moderator_add', $obj, $user_id)) {
            zib_send_json_error('暂无添加权限');
        }
    }

    if (zib_bbs_moderator_add_user($type, $obj, $user_id, $desc)) {
        global $zib_bbs;
        $moderator_name = 'cat' === $type ? $zib_bbs->cat_moderator_name : $zib_bbs->plate_moderator_name;
        zib_send_json_success(array('msg' => '已将此用户设置为' . $moderator_name, 'hide_modal' => true));
    } else {
        zib_send_json_error('处理失败，请刷新页面后重试');
    }
}
add_action('wp_ajax_moderator_add_user', 'zib_bbs_ajax_moderator_add_user');

//版主移出的模态框
function zib_bbs_ajax_moderator_remove_modal()
{
    $id      = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';
    $cap     = 'cat' === $type ? 'cat_moderator_edit' : 'moderator_edit';

    //参数判断
    if (!$id || !$user_id) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    //权限判断
    if (!zib_bbs_current_user_can($cap, $id)) {
        zib_ajax_notice_modal('danger', '权限不足');
    }

    //是否是版主判断
    if ('cat' === $type) {
        $obj = get_term($id);
        if (empty($obj->term_id)) {
            zib_ajax_notice_modal('warning', '参数错误或内容已删除');
        }
        if (!zib_bbs_is_the_cat_moderator($obj, $user_id, true)) {
            zib_ajax_notice_modal('warning', '该用户已移出，请刷新页面');
        }
    } else {
        $obj = get_post($id);
        if (empty($obj->ID)) {
            zib_ajax_notice_modal('warning', '参数错误或内容已删除');
        }
        if (!zib_bbs_is_the_moderator($obj, $user_id)) {
            zib_ajax_notice_modal('warning', '该用户已移出，请刷新页面');
        }
    }

    echo zib_bbs_get_moderator_remove_modal($type, $obj, $user_id);
    exit;
}
add_action('wp_ajax_moderator_remove_modal', 'zib_bbs_ajax_moderator_remove_modal');

//处理版主移出
function zib_bbs_ajax_moderator_remove()
{
    $id      = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $type    = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'plate';
    $desc    = !empty($_REQUEST['desc']) ? strip_tags(trim($_REQUEST['desc'])) : 0;
    $cap     = 'cat' === $type ? 'cat_moderator_edit' : 'moderator_edit';

    //执行安全验证
    zib_ajax_verify_nonce();

    //参数判断
    if (!$id || !$user_id) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    //权限判断
    if (!zib_bbs_current_user_can($cap, $id)) {
        zib_ajax_notice_modal('danger', '权限不足');
    }

    //是否是版主判断
    if ('cat' === $type) {
        $obj = get_term($id);
        if (empty($obj->term_id)) {
            zib_send_json_error('参数错误或内容已删除');
        }
        if (!zib_bbs_is_the_cat_moderator($obj, $user_id, true)) {
            zib_send_json_error('该用户已移出，请刷新页面');
        }
    } else {
        $obj = get_post($id);
        if (empty($obj->ID)) {
            zib_send_json_error('参数错误或内容已删除');
        }
        if (!zib_bbs_is_the_moderator($obj, $user_id)) {
            zib_send_json_error('该用户已移出，请刷新页面');
        }
    }

    if (zib_bbs_moderator_remove_user($type, $obj, $user_id, $desc)) {
        global $zib_bbs;
        zib_send_json_success(array('msg' => '已将此用户移出' . ('cat' === $type ? $zib_bbs->cat_moderator_name : $zib_bbs->plate_moderator_name), 'reload' => true));
    } else {
        zib_send_json_error('处理失败，请刷新页面后重试');
    }
}
add_action('wp_ajax_moderator_remove', 'zib_bbs_ajax_moderator_remove');

//处理申请提交
function zib_bbs_ajax_apply_moderator()
{
    $plate_id = !empty($_REQUEST['plate_id']) ? (int) $_REQUEST['plate_id'] : 0;
    $desc     = !empty($_REQUEST['desc']) ? strip_tags(trim($_REQUEST['desc'])) : 0;

    $plate = get_post($plate_id);
    if (empty($plate->ID)) {
        zib_send_json_error('参数错误或内容已删除');
    }
    //权限检查
    if (!zib_bbs_current_user_can('apply_moderator', $plate_id)) {
        zib_send_json_error('暂无申请权限');
    }
    //执行安全验证
    zib_ajax_verify_nonce();
    if (zib_new_strlen($desc) < 6) {
        zib_send_json_error('申请说明太少，无法申请');
    }

    if (zib_bbs_apply_moderator_create($plate, $desc)) {
        zib_send_json_success(array('msg' => '申请提交成功，请耐心等待', 'hide_modal' => true));
    } else {
        zib_send_json_error('申请提交失败，请刷新页面后重试');
    }

}
add_action('wp_ajax_apply_moderator', 'zib_bbs_ajax_apply_moderator');

//版主申请处理
function zib_bbs_ajax_apply_moderator_process_modal()
{
    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;

    //登录判断
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        zib_ajax_notice_modal('danger', '请先登录');
    }

    $processing = zib_bbs_get_apply_moderator_processing($user_id);

    //权限判断
    if (!zib_bbs_current_user_can('moderator_apply_process', $processing->meta['plate_id'] ?? 0)) {
        zib_ajax_notice_modal('danger', '您没有处理此申请的权限');
    }

    echo zib_bbs_get_apply_moderator_process_modal($user_id);
    exit;
}
add_action('wp_ajax_apply_moderator_process_modal', 'zib_bbs_ajax_apply_moderator_process_modal');
add_action('wp_ajax_nopriv_apply_moderator_process_modal', 'zib_bbs_ajax_apply_moderator_process_modal');

//处理申请提交审批
function zib_bbs_ajax_apply_moderator_process()
{
    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $desc    = !empty($_REQUEST['desc']) ? strip_tags(trim($_REQUEST['desc'])) : 0;
    $process = !empty($_REQUEST['process']) ? (int) ($_REQUEST['process']) : 0;

    if (1 != $process && 2 != $process) {
        zib_send_json_error('请选择处理方式');
    }

    //执行安全验证
    zib_ajax_verify_nonce();

    $processing = zib_bbs_get_apply_moderator_processing($user_id);
    //权限判断
    if (!zib_bbs_current_user_can('moderator_apply_process', $processing->meta['plate_id'] ?? 0)) {
        zib_ajax_notice_modal('danger', '您没有处理此申请的权限');
    }

    $result = zib_bbs_apply_moderator_process($user_id, $process, $desc);

    if ($result) {
        $msg = 1 == $process ? '已批准此申请' : '已拒绝此申请';
        zib_send_json_success(array('msg' => $msg, 'hide_modal' => true));
    } else {
        zib_send_json_error('处理失败，请刷新页面后重试');
    }

}
add_action('wp_ajax_apply_moderator_process', 'zib_bbs_ajax_apply_moderator_process');
