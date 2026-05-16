<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:59:52
 * @LastEditTime : 2026-04-20 20:42:17
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|AJAX执行类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//帖子评分
function zib_bbs_ajax_posts_score()
{

    global $zib_bbs;
    $id     = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 0;

    if (!$id) {
        wp_send_json_error(['msg' => '参数传入错误', 'code' => 'no_id']);
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], $action)) {
        wp_send_json_error(['msg' => '环境异常，请刷新页面后稍候再试', 'code' => 'env_error']);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['msg' => '请先登录', 'code' => 'no_logged']);
    }

    switch ($action) {
        case 'score_extra': //加分
        case 'score_deduct': //减分
            //扣分,加分
            // $old_score = (int)get_post_meta($id, 'score', true);
            $score_detail = zib_get_post_meta($id, 'score_detail', true);
            $score_detail = $score_detail ? $score_detail : array();

            $user_score = isset($score_detail[$user_id]) ? $score_detail[$user_id] : 0;

            if ('score_extra' == $action) {
                $max = apply_filters('bbs_user_score_extra_max', 5, $user_id); //用户最多加几分
                if (!$max) {
                    wp_send_json_error(['msg' => '您暂无加分权限']);
                }
                $new_user_score = $user_score + 1;
                if ($new_user_score > $max) {
                    wp_send_json_error(['msg' => '已为该内容加满' . $max . '分', 'ys' => 'success']);
                }
            } else {
                $max = apply_filters('bbs_user_score_deduct_max', 5, $user_id); //用户最多减去几分
                if (!$max) {
                    wp_send_json_error(['msg' => '您暂无减分权限']);
                }
                $new_user_score = $user_score - 1;
                if ($new_user_score < -$max) {
                    wp_send_json_error(['msg' => '已为该内容扣满' . $max . '分', 'ys' => 'warning']);
                }
            }
            $score_detail[$user_id] = $new_user_score;

            $score_detail = array_filter($score_detail);
            zib_update_post_meta($id, 'score_detail', $score_detail);

            $new_score = array_sum($score_detail);
            update_post_meta($id, 'score', $new_score);

            //保存文章作者一共得分
            $post      = get_post($id);
            $author_id = isset($post->post_author) ? $post->post_author : 0;
            if ($author_id) {
                wp_cache_delete($author_id, 'user_posts_score_sum'); //删除缓存
                $author_score = zib_bbs_get_user_posts_meta_sum($author_id, 'score'); //评分
                update_user_meta($author_id, 'score', $author_score);
                do_action('bbs_user_' . $action, $author_id, $id, $author_score);
                wp_cache_set($author_id, 'user_posts_score_sum', $author_score); //重新写入缓存
            }

            do_action('bbs_' . $action, $id, $user_id, $new_user_score); //添加挂钩

            $is_active = ('score_extra' == $action && $new_user_score > 0) || ('score_deduct' == $action && $new_user_score < 0);

            wp_send_json_success([
                'id'           => $id,
                'score_detail' => $score_detail,
                'active'       => $is_active,
                'text'         => (string) ((int) $new_score),
            ]);

            break;
        case 'follow_plate':

            break;
    }
}
add_action('wp_ajax_score_extra', 'zib_bbs_ajax_posts_score');
add_action('wp_ajax_nopriv_score_extra', 'zib_bbs_ajax_posts_score');
add_action('wp_ajax_score_deduct', 'zib_bbs_ajax_posts_score');
add_action('wp_ajax_nopriv_score_deduct', 'zib_bbs_ajax_posts_score');

//收藏帖子
function zib_bbs_ajax_favorite_posts()
{
    $id     = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 0;

    if (!$id) {
        wp_send_json_error(['msg' => '参数传入错误', 'code' => 'no_id']);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['msg' => '请先登录', 'code' => 'no_logged']);
    }

    $old_forum_posts = zib_get_user_meta($user_id, 'favorite_forum_posts', true);
    $favorite_count  = (int) get_post_meta($id, 'favorite_count', true);

    $old_forum_posts = $old_forum_posts ? $old_forum_posts : array();
    if (in_array($id, $old_forum_posts)) {
        //取消收藏
        $index = array_search($id, $old_forum_posts);
        unset($old_forum_posts[$index]);

        $new_count = $favorite_count - 1;
        $type      = 'cancel';
        $active    = false;
        $text      = '收藏';
    } else {
        //关注
        $type   = 'add';
        $text   = '已收藏';
        $active = true;

        $old_forum_posts[] = $id;
        $new_count         = $favorite_count + 1;
    }
    $new_count       = $new_count < 0 ? 0 : $new_count;
    $new_forum_posts = array_values($old_forum_posts);
    //保存用户数据
    zib_update_user_meta($user_id, 'favorite_forum_posts', $new_forum_posts);
    //保存帖子数据
    update_post_meta($id, 'favorite_count', $new_count);
    //更新缓存
    wp_cache_set($user_id, $new_forum_posts, 'bbs_user_favorite_forum_posts');

    do_action('bbs_favorite_posts', $id, $user_id, $type); //添加挂钩
    wp_send_json_success([
        'type'           => $type,
        'id'             => $id,
        'new_count'      => $new_count,
        'favorite_posts' => $new_forum_posts,
        'active'         => $active,
        'text'           => $text,
    ]);
}
add_action('wp_ajax_favorite_posts', 'zib_bbs_ajax_favorite_posts');
add_action('wp_ajax_nopriv_favorite_posts', 'zib_bbs_ajax_favorite_posts');

//执行文章设置为精华，设置为置顶
function zib_bbs_ajax_posts_meta_save()
{
    $post_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $action  = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $get_post = get_post($post_id);
    if (!$post_id || empty($get_post->ID)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
        exit;
    }

    //权限检查
    if (!zib_bbs_current_user_can($action, $post_id)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '暂无此权限')));
        exit;
    }

    $reload = true;
    switch ($action) {
        case 'posts_essence_set':
            $cancel = !empty($_REQUEST['cancel']) ? $_REQUEST['cancel'] : false;

            $val = $cancel ? '' : '1';
            update_post_meta($post_id, 'essence', $val);
            do_action('bbs_posts_essence_set', $post_id, $val); //添加挂钩

            $msg    = $cancel ? '已取消此内容的精华称号' : '已将此内容设置为精华';
            $reload = true;

            break;

        case 'posts_topping_set':
            $topping = !empty($_REQUEST['topping']) ? $_REQUEST['topping'] : 0;

            update_post_meta($post_id, 'topping', $topping);
            do_action('bbs_posts_topping_set', $post_id, $topping); //添加挂钩

            if ('0' == $topping) {
                $msg = '已取消置顶';
            } else {
                $name = zib_bbs_get_posts_topping_options();
                $name = isset($name[$topping]) ? $name[$topping] : '置顶';
                $msg  = '已' . $name . '此内容';
            }

            break;
    }

    echo(json_encode(array('error' => 0, 'ys' => '', 'reload' => $reload, 'msg' => $msg)));
    exit;
}
add_action('wp_ajax_posts_essence_set', 'zib_bbs_ajax_posts_meta_save');
add_action('wp_ajax_posts_topping_set', 'zib_bbs_ajax_posts_meta_save');

//帖子设置置顶的模态框
function zib_bbs_ajax_posts_topping_set_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
    }
    if (!zib_bbs_current_user_can('posts_topping_set', $post->ID)) {
        zib_ajax_notice_modal('danger', '您没有该操作权限');
    }

    $html = zib_bbs_edit::topping($id);

    echo $html;
    exit;
}
add_action('wp_ajax_posts_topping_set_modal', 'zib_bbs_ajax_posts_topping_set_modal');

//帖子设置阅读权限的模态框
function zib_bbs_ajax_posts_allow_view_set_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
    }
    if (!zib_bbs_current_user_can('posts_allow_view_edit', $id)) {
        zib_ajax_notice_modal('danger', '权限不足');
    }
    $allow_view_set = zib_bbs_edit::allow_view_set_content($id);
    $header         = zib_get_modal_colorful_header('jb-yellow', '<i class="fa fa-unlock-alt"></i>', '设置阅读权限');
    $hidden_html    = '';
    $hidden_html .= '<input type="hidden" name="action" value="edit_allow_view">';
    $hidden_html .= '<input type="hidden" name="post_id" value="' . $id . '">';

    $footer = '<div class="mt20 but-average">';
    $footer .= $hidden_html;
    $footer .= '<button class="but jb-yellow padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认提交</button>';
    $footer .= '</div>';

    echo '<form class="dependency-box">';
    echo $header;
    echo '<div class="mini-scrollbar scroll-y max-vh5">' . $allow_view_set . '</div>';
    echo $footer;
    echo '</form>';
    exit;
}
add_action('wp_ajax_posts_allow_view_set_modal', 'zib_bbs_ajax_posts_allow_view_set_modal');

//执行帖子移动版块
function zib_bbs_ajax_posts_plate_move()
{
    $id       = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $plate_id = isset($_REQUEST['plate']) ? (int) $_REQUEST['plate'] : 0;
    global $zib_bbs;
    $plate_name = $zib_bbs->plate_name;

    if (!$plate_id) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '请选择' . $plate_name)));
        exit;
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('save_bbs');

    $post = get_post($id);
    if (empty($post->ID)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '内容不存在或参数传入错误')));
        exit;
    }
    //权限验证
    if (!zib_bbs_current_user_can('posts_plate_move', $post)) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '您没有该操作权限')));
        exit;
    }

    //新版块选择权限验证
    if (!zib_bbs_current_user_can('select_plate', $plate_id, $post)) {
        zib_send_json_error('抱歉！您暂无选择此' . $plate_name . '的权限，请重新选择' . $plate_name);
    }

    $old_id = zib_bbs_get_plate_id($id);
    if ($old_id == $plate_id) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '所选' . $plate_name . '未做任何修改')));
        exit;
    }

    //执行移动版块
    zib_bbs_posts_plate_move($id, $plate_id, $old_id);

    echo(json_encode(array('error' => 0, 'ys' => '', 'reload' => true, 'msg' => '切换成功')));
    exit;
}
add_action('wp_ajax_plate_move', 'zib_bbs_ajax_posts_plate_move');

//帖子移动版块的模态框
function zib_bbs_ajax_posts_plate_move_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
    }
    if (!zib_bbs_current_user_can('posts_plate_move', $post)) {
        echo zib_get_null('抱歉！暂无此权限', 30, 'null-cap.svg', '', 200, 150);
        exit;
    }

    $html = zib_bbs_edit::plate_move($post->ID);

    echo $html;
    exit;
}
add_action('wp_ajax_posts_plate_move_modal', 'zib_bbs_ajax_posts_plate_move_modal');

//删除内容的弹窗
function zib_bbs_ajax_delete_modal()
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;
    $id     = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    if (!$id) {
        zib_ajax_notice_modal('danger', '参数传入错误');
    }

    switch ($action) {

        case 'posts_delete_modal':

            $post = get_post($id);
            if (empty($post->ID)) {
                zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
            }
            if (!zib_bbs_current_user_can('posts_delete', $post)) {
                zib_ajax_notice_modal('danger', '您没有删除此内容的权限');
            }
            if ('forum_post' != $post->post_type) {
                zib_ajax_notice_modal('danger', '类型错误或参数传入错误');
            }

            $html = zib_bbs_edit::posts_delete($post);
            break;

        case 'plate_delete_modal':

            $post = get_post($id);
            if (empty($post->ID)) {
                zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
            }
            if (!zib_bbs_current_user_can('plate_delete', $post)) {
                zib_ajax_notice_modal('danger', '您没有删除此内容的权限');
            }
            if ('plate' != $post->post_type) {
                zib_ajax_notice_modal('danger', '类型错误或参数传入错误');
            }

            $html = zib_bbs_edit::plate_delete($post);
            break;

        case 'posts_audit_modal':
        case 'plate_audit_modal':
            $post = get_post($id);

            if (empty($post->ID)) {
                zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
            }
            if ('forum_post' === $post->post_type && !zib_bbs_current_user_can('posts_audit', $post)) {
                zib_ajax_notice_modal('danger', '您没有审核的权限');
            }
            if ('plate' === $post->post_type && !zib_bbs_current_user_can('plate_audit', $post)) {
                zib_ajax_notice_modal('danger', '您没有审核的权限');
            }

            $html = zib_bbs_edit::audit($post);
            break;

        case 'plate_cat_delete_modal':
        case 'forum_topic_delete_modal':
        case 'forum_tag_delete_modal':
            $term = get_term($id);

            if (empty($term->term_id)) {
                zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
            }

            $taxonomy = $term->taxonomy;
            if (!zib_bbs_current_user_can($taxonomy . '_delete', $term->term_id)) {
                zib_ajax_notice_modal('danger', '您没有删除此内容的权限');
            }

            $html = zib_bbs_edit::term_delete($term);
            break;
    }

    echo $html;
    exit;
}
add_action('wp_ajax_posts_delete_modal', 'zib_bbs_ajax_delete_modal');
add_action('wp_ajax_plate_delete_modal', 'zib_bbs_ajax_delete_modal');
add_action('wp_ajax_posts_audit_modal', 'zib_bbs_ajax_delete_modal');
add_action('wp_ajax_plate_audit_modal', 'zib_bbs_ajax_delete_modal');
add_action('wp_ajax_plate_cat_delete_modal', 'zib_bbs_ajax_delete_modal');
add_action('wp_ajax_forum_topic_delete_modal', 'zib_bbs_ajax_delete_modal');
add_action('wp_ajax_forum_tag_delete_modal', 'zib_bbs_ajax_delete_modal');

//关闭评论模态框
function zib_bbs_ajax_posts_comment_close_modal()
{
    $id   = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
    }
    if (!zib_bbs_current_user_can('comment_close', $post)) {
        zib_ajax_notice_modal('danger', '您没有关闭评论的权限');
    }
    $html = zib_bbs_edit::posts_comment_close($post);
    echo $html;
    exit;
}
add_action('wp_ajax_posts_comment_close_modal', 'zib_bbs_ajax_posts_comment_close_modal');

//关闭或开启评论
function zib_bbs_ajax_posts_comment_close()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $id   = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $post = get_post($id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
    }
    if (!zib_bbs_current_user_can('comment_close', $post)) {
        zib_ajax_notice_modal('danger', '您没有关闭评论的权限');
    }
    $comments_open = comments_open($post);
    $post_data     = ['ID' => $post->ID];
    if ($comments_open) {
        $post_data['comment_status'] = 'closed';
    } else {
        $post_data['comment_status'] = 'open';
    }

    $post_updated = wp_update_post($post_data, true);
    if (is_wp_error($post_updated)) {
        zib_send_json_error('操作失败：' . $post_updated->get_error_message());
    }

    zib_send_json_success(array('msg' => '评论状态已更新', 'reload' => true, 'hide_modal' => true));
}
add_action('wp_ajax_posts_comment_close', 'zib_bbs_ajax_posts_comment_close');

//执行审核版块或者文章的审核
function zib_bbs_ajax_plate_or_posts_audit()
{
    $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $post = get_post($id);

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    global $zib_bbs;

    if (empty($post->ID)) {
        zib_send_json_error('内容不存在或参数传入错误');
    }
    //权限判断
    if ('forum_post' === $post->post_type && !zib_bbs_current_user_can('posts_audit', $post)) {
        zib_send_json_error('您没有审核' . $zib_bbs->posts_name . '的权限');
    }
    if ('plate' === $post->post_type && !zib_bbs_current_user_can('plate_audit', $post)) {
        zib_send_json_error('您没有审核' . $zib_bbs->plate_name . '的权限');
    }

    if ('pending' === $post->post_status) {
        $method = !empty($_REQUEST['method']) ? $_REQUEST['method'] : '';
        if ('reject' === $method) {
            $post_status = 'pending';
            $mag         = '已驳回此内容，并已通知作者';
            $send_msg    = !empty($_REQUEST['msg']) ? trim(strip_tags($_REQUEST['msg'])) : '';
            if (!$send_msg) {
                zib_send_json_error('请填写驳回原因或修改建议');
            }
            zib_newmsg_publish_to_pending($post);
        } else {
            $post_status = 'publish';
            $mag         = '内容已审核发布';
        }
    } else {
        $post_status = 'pending';
        $mag         = '内容已驳回审核';
    }

    $reload = false;
    if ($post_status !== $post->post_status) {
        $post_updated = wp_update_post(
            array(
                'ID'          => $post->ID,
                'post_status' => $post_status,
            )
        );

        if (!$post_updated) {
            zib_send_json_error('操作失败，请刷新页面重试');
        }

        $reload = true;
    }

    zib_send_json_success(array('msg' => $mag, 'reload' => $reload, 'hide_modal' => true));
}
add_action('wp_ajax_plate_audit', 'zib_bbs_ajax_plate_or_posts_audit');
add_action('wp_ajax_posts_audit', 'zib_bbs_ajax_plate_or_posts_audit');

//执行删除版块或者文章的撤销
function zib_bbs_ajax_plate_or_posts_delete_revoke()
{
    $plate_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $action   = isset($_REQUEST['action']) ? $_REQUEST['action'] : 0;

    $plate = get_post($plate_id);

    if (!$plate_id || !$plate) {
        echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误')));
        exit;
    }
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    global $zib_bbs;

    if ('plate_delete_revoke' === $action) {
        $name = $zib_bbs->plate_name;
        if (!zib_bbs_current_user_can('plate_edit', $plate)) {
            echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '您没有恢复此' . $name . '的权限')));
            exit;
        }
    } else {
        $name = $zib_bbs->posts_name;
        if (!zib_bbs_current_user_can('posts_edit', $plate)) {
            echo json_encode((array('error' => 1, 'ys' => 'danger', 'msg' => '您没有恢复此' . $name . '的权限')));
            exit;
        }
    }

    //执行恢复删除文章
    wp_untrash_post($plate_id);

    $goto = get_permalink($plate_id);

    echo(json_encode(array('error' => 0, 'ys' => '', 'reload' => true, 'goto' => $goto, 'msg' => '该' . $name . '已移出回收站')));
    exit;
}
add_action('wp_ajax_plate_delete_revoke', 'zib_bbs_ajax_plate_or_posts_delete_revoke');
add_action('wp_ajax_posts_delete_revoke', 'zib_bbs_ajax_plate_or_posts_delete_revoke');

//帖子页的评分用户明细
function zib_bbs_ajax_score_user_lists()
{

    $id    = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
    $paged = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;

    $html = zib_bbs_get_score_user_lists($id, '', $paged);
    echo $html;
    exit;
}
add_action('wp_ajax_score_user_lists', 'zib_bbs_ajax_score_user_lists');
add_action('wp_ajax_nopriv_score_user_lists', 'zib_bbs_ajax_score_user_lists');

//保存帖子
function zib_bbs_ajax_edit_posts()
{

    global $zib_bbs;
    $cuid = get_current_user_id();

    //判断登录
    if (!$cuid) {
        zib_send_json_error(['msg' => '请先登录', 'code' => 'no_logged']);
    }

    $action       = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'bbs_posts_save';
    $post_id      = !empty($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
    $is_new       = !$post_id;
    $post_content = !empty($_REQUEST['post_content']) ? $_REQUEST['post_content'] : '';
    $post_title   = !empty($_REQUEST['post_title']) ? strip_tags(trim($_REQUEST['post_title'])) : '';
    $plate        = !empty($_REQUEST['plate']) ? (int) $_REQUEST['plate'] : 0;
    $tag_g        = !empty($_REQUEST['tag']) ? $_REQUEST['tag'] : 0;
    $topic        = !empty($_REQUEST['topic']) ? (int) $_REQUEST['topic'] : 0;
    $post_status  = 'draft'; //默认为草稿
    $tag          = array();
    if (is_array($tag_g)) {
        foreach ($tag_g as $t) {
            $tag[] = (int) $t;
        }
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('bbs_edit_posts');

    //人机验证
    if (_pz('verification_bbspost_s')) {
        zib_ajax_man_machine_verification('bbs_post_submit');
    }

    //权限验证
    if ($is_new) {
        if (!zib_bbs_current_user_can('posts_add')) {
            zib_send_json_error('暂无发布权限');
        }

    } else {
        if (!zib_bbs_current_user_can('posts_edit', $post_id)) {
            zib_send_json_error('您没有编辑此内容的权限');
        }
    }

    //版块选择验证
    if (!zib_bbs_current_user_can('select_plate', $plate, $post_id)) {
        zib_send_json_error('您暂无在此' . $zib_bbs->plate_name . '发布的权限，请重新选择' . $zib_bbs->plate_name);
    }

    //内容长度验证
    if (!$post_title) {
        zib_send_json_error('请填写标题');
    }

    $title_strlen_limit = _pz('bbs_post_title_strlen_limit') ?: array('min' => 5, 'max' => 30);

    if ($title_strlen_limit['max'] && zib_new_strlen($post_title) > $title_strlen_limit['max']) {
        zib_send_json_error('标题太长了，不能超过' . $title_strlen_limit['max'] . '个字');
    }

    if ('bbs_posts_save' == $action) {
        //提交保存、审核、发布
        if ($title_strlen_limit['min'] && zib_new_strlen($post_title) < $title_strlen_limit['min']) {
            zib_send_json_error('标题太短！');
        }

        if (!$plate) {
            zib_send_json_error('请选择' . $zib_bbs->plate_name);
        }
    }

    //内容合规性判断
    $is_audit = false;
    if (zib_bbs_current_user_can('posts_save_audit_no') || ($post_id && zib_bbs_current_user_can('posts_audit', $post_id)) || ($plate && zib_bbs_current_user_can('posts_audit', $plate))) {
        //拥有免审核权限，或者拥有人工审核此帖子的权限
        $is_audit = true;
    } else {
        //API审核（拥有免审核权限的用户无需API审核）
        if (_pz('audit_bbs_posts')) {
            $api_is_audit = ZibAudit::is_audit(ZibAudit::ajax_text($post_title . $post_content));
            //API审核通过，且拥有免人工审核
            if ($api_is_audit && zib_bbs_current_user_can('posts_save_audit_no_manual')) {
                $is_audit = true;
            }
        }
    }

    //如果是快速发布
    if (!empty($_REQUEST['is_quick'])) {
        //插入图片
        if (!empty($_REQUEST['images']) && is_array($_REQUEST['images'])) {
            foreach ($_REQUEST['images'] as $image_data) {
                $image_data = @json_decode(wp_unslash($image_data), true);
                if (!empty($image_data['src'])) {
                    $post_content .= '<p><img src="' . esc_url($image_data['src']) . '" alt="' . esc_attr($image_data['alt'] ?? '') . '"' . (!empty($image_data['id']) ? ' data-edit-file-id="' . ((int) $image_data['id']) . '"' : '') . (!empty($image_data['full']) ? ' data-full-url="' . esc_url($image_data['full']) . '"' : '') . '></p>';
                }
            }
        }

        //插入隐藏内容
        $hide_content = !empty($_REQUEST['hide_content']) ? $_REQUEST['hide_content'] : '';
        if ($hide_content) {
            $hide_type = !empty($_REQUEST['hide_type']) ? trim(strip_tags($_REQUEST['hide_type'])) : 'logged';

            $type_desc = array(
                'logged'  => '登录可见',
                'reply'   => '评论可见',
                'vip1'    => '会员可见',
                'payshow' => '付费可见',
                'vip2'    => _pz('pay_user_vip_2_name') . '可见',
            );

            if (!isset($type_desc[$hide_type])) {
                $hide_type = 'logged';
            }

            $post_content .= '<div class="tinymce-hide" contenteditable="false"><p class="hide-before">[hidecontent type="' . $hide_type . '" desc="隐藏内容：' . $type_desc[$hide_type] . '"]</p><div contenteditable="true">' . $hide_content . '</div><p class="hide-after">[/hidecontent]</p></div>';
        }
    }

    //发布状态判断
    if ('bbs_posts_save' === $action) {
        //如果是发布
        $post_status = 'pending'; //默认为待审核状态

        if ($is_audit) {
            //判断是否拥有无需审核、无需人工审核的权限
            $post_status = !empty($_REQUEST['post_status']) ? $_REQUEST['post_status'] : 'publish';
        }
    }

    $insert_args = array(
        'ID'           => $post_id,
        'post_type'    => 'forum_post',
        'post_title'   => $post_title,
        'post_status'  => $post_status,
        'post_content' => $post_content,
        'meta_input'   => array(
            'plate_id' => $plate,
        ),
    );

    if (!$post_id) {
        //新建时候，添加作者
        $insert_args['post_author']    = $cuid;
        $insert_args['comment_status'] = 'open';
    } else {
        //编辑帖子
        $post_obj = get_post($post_id, ARRAY_A);
        if (isset($post_obj['ID'])) {
            $insert_args = array_merge($post_obj, $insert_args);
        }
    }

    //编辑器允许插入嵌入视频
    if ((!$post_id && zib_bbs_current_user_can('posts_iframe_video')) || ($post_id && zib_bbs_current_user_can('posts_iframe_video', $post_id))) {
        add_filter('wp_kses_allowed_html', 'zib_allow_html_iframe_attributes', 99, 2);
    }

    //添加保存前的挂钩
    do_action('zib_pre_insert_post', $insert_args);

    //执行保存内容
    $insert_id = wp_insert_post($insert_args, true);

    if (is_wp_error($insert_id)) {
        //保存错误
        zib_send_json_error($insert_id->get_error_message());
    }

    //执行保存话题
    wp_set_post_terms($insert_id, array($topic), 'forum_topic');

    //执行保存标签
    wp_set_post_terms($insert_id, (array) $tag, 'forum_tag');

    //执行保存发布类型
    zib_bbs_ajax_edit_posts_bbs_type($insert_id, false, $is_new);

    //执行保存投票
    zib_bbs_ajax_edit_posts_vote($insert_id, false, $is_new);

    //执行保存阅读权限
    zib_bbs_ajax_edit_allow_view($insert_id, false, $is_new);

    //执行保存封面
    zib_bbs_ajax_edit_posts_featured($insert_id, false, $is_new);

    $new_post_obj = get_post($insert_id);
    $permalink    = get_permalink($insert_id);
    $send         = array(
        'post_id' => $insert_id,
        'goto'    => $permalink,
        'post'    => $new_post_obj,
    );

    //添加挂钩
    do_action('bbs_' . ($is_new ? 'add' : 'edit') . '_posts', $new_post_obj);

    switch ($post_status) {
        case 'pending': //待审核
            $send['reload'] = true;
            if ($cuid == $insert_args['post_author']) {
                $send['msg']  = '内容已提交，正在等待审核';
                $send['goto'] = zib_get_user_home_url($insert_args['post_author'], array('tab' => 'forum', 'status' => 'pending')); //?tab=forum&status=pending
            } else {
                $send['msg']  = '内容已修改，状态为待审核';
                $send['goto'] = get_permalink($plate);
            }
            break;
        case 'draft': //草稿
            if ($cuid == $insert_args['post_author']) {
                //我的草稿
                $send['msg']  = '草稿已保存';
                $send['html'] = '<div class="muted-2-color flex ac mt6 mr10"><a href="' . zib_get_user_home_url($insert_args['post_author'], array('tab' => 'forum', 'status' => 'draft')) . '" class="but mr10 p2-10 px12">我的草稿</a>草稿已保存 </div><div class="muted-3-color px12 mt6">最后更新：' . get_the_modified_time('Y-m-d H:i:s', $insert_id) . '</div>';
            } else {
                $send['msg']  = '内容已修改，保存为草稿';
                $send['html'] = '<div class="muted-2-color flex ac mt6 mr10">草稿已保存 </div><div class="muted-3-color px12 mt6">最后更新：' . get_the_modified_time('Y-m-d H:i:s', $insert_id) . '</div>';
            }

            break;
        default:
            $send['msg']    = $zib_bbs->posts_name . '已发布';
            $send['reload'] = true;
    }

    zib_send_json_success($send);
}
add_action('wp_ajax_bbs_posts_draft', 'zib_bbs_ajax_edit_posts');
add_action('wp_ajax_bbs_posts_save', 'zib_bbs_ajax_edit_posts');
add_action('wp_ajax_nopriv_bbs_posts_draft', 'zib_bbs_ajax_edit_posts');
add_action('wp_ajax_nopriv_bbs_posts_save', 'zib_bbs_ajax_edit_posts');

//有新的用户发帖待审核，给管理员发消息
function zib_bbs_add_posts_pending_msg($post)
{
    $user_id = $post->post_author;
    $udata   = get_userdata($user_id);

    /**判断通知状态 */
    if ($post->post_status !== 'pending' || zib_get_post_meta($post->ID, 'add_posts_pending_msg')) {
        return;
    }

    $plate_id          = zib_bbs_get_plate_id($post->ID);
    $msg_include_plate = _pz('msg_include_plate');

    if ($msg_include_plate && is_array($msg_include_plate) && !in_array($plate_id, $msg_include_plate)) {
        return;
    }

    global $zib_bbs;
    $posts_name = $zib_bbs->posts_name;
    $plate_name = $zib_bbs->plate_name;
    $plate      = get_post($plate_id);
    $plate_url  = get_permalink($plate);
    $title      = $plate_name . '[' . zib_str_cut(trim(strip_tags($plate->post_title)), 0, 20) . ']有新的' . $posts_name . '待审核：' . zib_str_cut(trim(strip_tags($post->post_title)), 0, 20);
    $send_user  = 'admin';

    $message = '有新的' . $posts_name . '待审核<br />';
    $message .= '标题：' . trim(strip_tags($post->post_title)) . '<br>';
    $message .= '内容摘要：<br />';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(125, 125, 125, 0.06); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '用户：' . zib_get_user_name_link($user_id) . '<br>';
    $message .= $plate_name . '：<a href="' . $plate_url . '">' . esc_attr($plate->post_title) . '</a><br />';
    $message .= '提交时间：' . get_the_time('Y-m-d H:i:s', $post) . '<br>';

    $message .= '<br>';
    $message .= '您可以<a href="' . $plate_url . '">点击此处</a>进入版块页面的待审核栏目，审核此内容<br />';

    /**发送邮件 */
    zib_update_post_meta($post->ID, 'add_posts_pending_msg', true);

    $msg_arge = array(
        'send_user'    => $send_user,
        'receive_user' => 'admin',
        'type'         => 'posts',
        'title'        => $title,
        'content'      => $message,
    );

    //创建新消息
    ZibMsg::add($msg_arge);

    if (_pz('email_bbs_posts_pending_to_admin', true)) {
        zib_mail_to_admin($title, $message);
    }

    //循环给版主，发送消息
    $send_user_ids = array();
    if (zib_bbs_user_can($plate->post_author, 'posts_audit', $post)) {
        //拥有审核他人帖子的权限
        $send_user_ids[] = $plate->post_author;
    }
    $moderator = get_post_meta($plate->ID, 'moderator', true);
    if ($moderator && is_array($moderator)) {
        foreach ($moderator as $v) {
            if (zib_bbs_user_can($v, 'posts_audit', $post)) {
                //拥有审核他人帖子的权限
                $send_user_ids[] = $v;
            }
        }
    }

    if ($send_user_ids && is_array($send_user_ids)) {
        $users_args = array(
            'include'     => $send_user_ids,
            'order'       => 'DESC',
            'orderby'     => 'post_count',
            'number'      => 99,
            'paged'       => 1,
            'count_total' => false,
            'fields'      => array('display_name', 'ID', 'user_email'),
        );

        $query = new WP_User_Query($users_args);
        if (!is_wp_error($query)) {
            $get_results = $query->get_results();
            if ($get_results) {
                foreach ($get_results as $item) {
                    $u_id = $item->ID;
                    if (is_super_admin($u_id)) {
                        continue;
                    }
                    $user_email               = $item->user_email;
                    $msg_arge['receive_user'] = $u_id;
                    //创建新消息
                    ZibMsg::add($msg_arge);
                    if (_pz('email_bbs_posts_pending_to_moderator', true)) {
                        @wp_mail($user_email, $title, $message);
                    }
                }
            }
        }
    }
}
add_action('bbs_add_posts', 'zib_bbs_add_posts_pending_msg');

//发布新的帖子为给版主发送消息-未启用
function zib_bbs_add_posts_publish_msg($post)
{
    $user_id = $post->post_author;

    /**判断通知状态 */
    if ($post->post_status !== 'publish' || zib_get_post_meta($post->ID, 'add_posts_publish_msg')) {
        return;
    }

    $plate_id          = zib_bbs_get_plate_id($post->ID);
    $msg_include_plate = _pz('msg_include_plate');

    if ($msg_include_plate && is_array($msg_include_plate) && !in_array($plate_id, $msg_include_plate)) {
        return;
    }

    global $zib_bbs;
    $posts_name = $zib_bbs->posts_name;
    $plate_name = $zib_bbs->plate_name;
    $plate      = get_post($plate_id);
    $plate_url  = get_permalink($plate);
    $title      = '您管理的' . $plate_name . '[' . zib_str_cut(trim(strip_tags($plate->post_title)), 0, 20) . ']收到了新' . $posts_name . '：' . zib_str_cut(trim(strip_tags($post->post_title)), 0, 20);
    $send_user  = 'admin';

    $message = '有新的' . $posts_name . '已发布<br />';
    $message .= '标题：' . trim(strip_tags($post->post_title)) . '<br>';
    $message .= '内容摘要：<br />';
    $message .= '<div class="muted-box" style="padding: 10px 15px; border-radius: 8px; background: rgba(125, 125, 125, 0.06); line-height: 1.7;">' . zib_str_cut(trim(strip_tags($post->post_content)), 0, 200, '...') . '</div>';
    $message .= '用户：' . zib_get_user_name_link($user_id) . '<br>';
    $message .= $plate_name . '：<a href="' . $plate_url . '">' . esc_attr($plate->post_title) . '</a><br />';
    $message .= '提交时间：' . get_the_time('Y-m-d H:i:s', $post) . '<br>';

    $message .= '<br>';
    $message .= '您可以<a href="' . get_permalink($post) . '">点击此处</a>查看此内容<br />';

    /**发送邮件 */
    zib_update_post_meta($post->ID, 'add_posts_publish_msg', true);

    $msg_arge = array(
        'send_user' => $send_user,
        'type'      => 'posts',
        'title'     => $title,
        'content'   => $message,
    );

    //循环给版主，发送消息
    $send_user_ids   = array();
    $send_user_ids[] = $plate->post_author;

    $moderator = get_post_meta($plate->ID, 'moderator', true);
    if ($moderator && is_array($moderator)) {
        foreach ($moderator as $v) {
            $send_user_ids[] = $v;
        }
    }

    if ($send_user_ids && is_array($send_user_ids)) {
        $users_args = array(
            'include'     => $send_user_ids,
            'order'       => 'DESC',
            'orderby'     => 'post_count',
            'number'      => 99,
            'paged'       => 1,
            'count_total' => false,
            'fields'      => array('display_name', 'ID', 'user_email'),
        );

        $query = new WP_User_Query($users_args);
        if (!is_wp_error($query)) {
            $get_results = $query->get_results();
            if ($get_results) {
                foreach ($get_results as $item) {
                    $u_id                     = $item->ID;
                    $user_email               = $item->user_email;
                    $msg_arge['receive_user'] = $u_id;
                    //创建新消息
                    ZibMsg::add($msg_arge);
                    if (_pz('email_bbs_posts_publish_to_moderator', true)) {
                        @wp_mail($user_email, $title, $message);
                    }
                }
            }
        }
    }
}
//add_action('bbs_add_posts', 'zib_bbs_add_posts_publish_msg');

//编辑帖子保存发布类型
function zib_bbs_ajax_edit_posts_bbs_type($post_id = 0, $echo_success = true)
{
    if (!isset($_REQUEST['bbs_type'])) {
        return;
    }
    $post_id  = $post_id ? $post_id : (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0);
    $bbs_type = $_REQUEST['bbs_type'];

    //保存
    update_post_meta($post_id, 'bbs_type', $bbs_type);
}

//编辑保存帖子的投票
function zib_bbs_ajax_edit_posts_vote($post_id = 0, $echo_success = true)
{
    if (!isset($_REQUEST['vote_s']) && !isset($_REQUEST['vote'])) {
        return;
    }

    $post_id       = $post_id ? $post_id : (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0);
    $s             = !empty($_REQUEST['vote_s']) ? true : false;
    $opt           = !empty($_REQUEST['vote']) ? (array) $_REQUEST['vote'] : 0;
    $vote_opt_null = array(
        'title'      => '',
        'type'       => 'single',
        'time'       => current_time('Y-m-d H:i:s'), //创建时间
        'time_limit' => 0, //有效时间限制
        'options'    => array(),
    );
    $opt = wp_parse_args($opt, $vote_opt_null);

    if ($s) {
        //如果开启了
        //对内容进行处理，避免xss
        $new_options = array();
        foreach ($opt['options'] as $v) {
            $v = trim(strip_tags($v));
            if (in_array($v, $new_options)) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '投票选项的内容不能相同'));
            }
            if (zib_new_strlen($v) > 1) {
                $new_options[] = esc_attr($v);
            }
        }
        $opt['options']    = $new_options;
        $opt['time_limit'] = (int) $opt['time_limit'];

        $opt['title'] = esc_attr(trim(strip_tags($opt['title'])));

        if (count($opt['options']) < 2) {
            //如果选项小于2
            zib_send_json_error(array('post_id' => $post_id, 'msg' => '投票选项太少或选项内容字数太短'));
        }

        if ($opt['title'] && zib_new_strlen($opt['title']) > 20) {
            zib_send_json_error(array('post_id' => $post_id, 'msg' => '投票标题太长，最多20个字'));
        }

        //保存开关
        update_post_meta($post_id, 'vote', true);
        zib_update_post_meta($post_id, 'vote_option', $opt);
        if ($echo_success) {
            zib_send_json_success(array('msg' => '投票信息已保存', 'vote_option' => $opt, 'post_id' => $post_id));
        }
    } else {
        //保存开关
        update_post_meta($post_id, 'vote', false);
        if ($echo_success) {
            zib_send_json_success(array('post_id' => $post_id, 'msg' => '投票已关闭'));
        }
    }
}

//保存阅读权限
function zib_bbs_ajax_edit_allow_view($post_id = 0, $echo_success = true, $is_new = false)
{
    if (!isset($_REQUEST['allow_view']) && !isset($_REQUEST['allow_view_roles'])) {
        return;
    }

    $post_id = $post_id ? $post_id : (!empty($_REQUEST['post_id']) ? $_REQUEST['post_id'] : 0);

    $allow_view       = !empty($_REQUEST['allow_view']) ? $_REQUEST['allow_view'] : '';
    $allow_view_roles = !empty($_REQUEST['allow_view_roles']) ? (array) $_REQUEST['allow_view_roles'] : array();
    $posts_zibpay     = !empty($_REQUEST['posts_zibpay']) ? (array) $_REQUEST['posts_zibpay'] : array();
    $pay_hide_part    = !empty($_REQUEST['pay_hide_part']) ? true : false;

    $allow_view_roles   = array_filter($allow_view_roles);
    $pay_data           = array();
    $vip_input_s        = _pz('bbs_post_pay_vip_price_s');
    $vip_1_discount     = _pz('bbs_post_pay_vip_1_discount', 100);
    $vip_2_discount     = _pz('bbs_post_pay_vip_2_discount', 100);
    $pay_price_limit_pz = _pz('bbs_post_pay_price_limit');

    //权限判断

    if (($is_new && !zib_bbs_current_user_can('posts_allow_view_add')) || (!$is_new && !zib_bbs_current_user_can('posts_allow_view_edit', $post_id))) {
        zib_send_json_error(array('post_id' => $post_id, 'msg' => '权限不足'));
    }

    switch ($allow_view) {
        case 'roles':
            if (!$allow_view_roles) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '请至少选择一项允许查看的用户类型'));
            }
            break;

        case 'points':

            //权限管理
            if (($is_new && !zib_bbs_current_user_can('posts_allow_view_points')) || (!$is_new && !zib_bbs_current_user_can('posts_allow_view_points', $post_id))) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '权限不足'));
            }

            $points_price = !empty($posts_zibpay['points_price']) ? (int) $posts_zibpay['points_price'] : 0;
            if (!$points_price) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置阅读限制的积分价格'));
            }
            if ($points_price < 0) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置正确的积分价格'));
            }

            //价格区间限制
            $points_price_limit = !empty($pay_price_limit_pz['points']) ? $pay_price_limit_pz['points'] : false;
            if ($points_price_limit) {
                $points_price_limit_min = !empty($points_price_limit['min']) ? $points_price_limit['min'] : 0;
                $points_price_limit_max = !empty($points_price_limit['max']) ? $points_price_limit['max'] : 0;
                if ($points_price_limit_min && $points_price < $points_price_limit_min) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '积分价格不能低于：' . $points_price_limit_min));
                }
                if ($points_price_limit_max && $points_price > $points_price_limit_max) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '积分价格不能高于：' . $points_price_limit_max));
                }
            }

            if ($vip_input_s) {
                if (isset($posts_zibpay['vip_1_points']) && !$posts_zibpay['vip_1_points'] && $posts_zibpay['vip_1_points'] != '0') {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_1_name') . '积分售价'));
                }
                if (isset($posts_zibpay['vip_2_points']) && !$posts_zibpay['vip_2_points'] && $posts_zibpay['vip_2_points'] != '0') {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_2_name') . '积分售价'));
                }

                $vip_1_points = !empty($posts_zibpay['vip_1_points']) ? (int) $posts_zibpay['vip_1_points'] : 0;
                $vip_2_points = !empty($posts_zibpay['vip_2_points']) ? (int) $posts_zibpay['vip_2_points'] : 0;

                if ($vip_1_points > $points_price) {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => '会员价不能高于普通价'));
                }

                if ($vip_2_points > $vip_1_points) {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => _pz('pay_user_vip_2_name') . '价格不能高于' . _pz('pay_user_vip_1_name') . '价格'));
                }

            } else {
                $vip_1_points = (int) ($points_price * $vip_1_discount / 100);
                $vip_2_points = (int) ($points_price * $vip_2_discount / 100);
            }

            //不能小于0
            $vip_1_points = $vip_1_points < 0 ? 0 : $vip_1_points;
            $vip_2_points = $vip_2_points < 0 ? 0 : $vip_2_points;
            //不能大于正常价
            $vip_1_points = $vip_1_points > $points_price ? $points_price : $vip_1_points;
            $vip_2_points = $vip_2_points > $points_price ? $points_price : $vip_2_points;

            $pay_data = array(
                'pay_type'     => 1,
                'pay_modo'     => 'points',
                'points_price' => $points_price,
                'vip_1_points' => $vip_1_points,
                'vip_2_points' => $vip_2_points,
            );
            break;

        case 'pay':

            //权限管理
            if (($is_new && !zib_bbs_current_user_can('posts_allow_view_pay')) || (!$is_new && !zib_bbs_current_user_can('posts_allow_view_pay', $post_id))) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '权限不足'));
            }

            $pay_price = !empty($posts_zibpay['pay_price']) ? round((float) $posts_zibpay['pay_price'], 2) : 0;
            if (!$pay_price) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置阅读限制的支付金额'));
            }
            if ($pay_price < 0) {
                zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置正确的价格'));
            }

            //价格区间限制
            $pay_price_limit = !empty($pay_price_limit_pz['price']) ? $pay_price_limit_pz['price'] : false;
            if ($pay_price_limit) {
                $pay_price_limit_min = !empty($pay_price_limit['min']) ? $pay_price_limit['min'] : 0;
                $pay_price_limit_max = !empty($pay_price_limit['max']) ? $pay_price_limit['max'] : 0;
                if ($pay_price_limit_min && $pay_price < $pay_price_limit_min) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '价格不能低于：' . $pay_price_limit_min));
                }
                if ($pay_price_limit_max && $pay_price > $pay_price_limit_max) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '价格不能高于：' . $pay_price_limit_max));
                }
            }

            if ($vip_input_s) {
                if (isset($posts_zibpay['vip_1_price']) && !$posts_zibpay['vip_1_price'] && $posts_zibpay['vip_1_price'] != '0') {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_1_name') . '价格'));
                }
                if (isset($posts_zibpay['vip_2_price']) && !$posts_zibpay['vip_2_price'] && $posts_zibpay['vip_2_price'] != '0') {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_2_name') . '价格'));
                }

                $vip_1_price = !empty($posts_zibpay['vip_1_price']) ? round((float) $posts_zibpay['vip_1_price'], 2) : 0;
                $vip_2_price = !empty($posts_zibpay['vip_2_price']) ? round((float) $posts_zibpay['vip_2_price'], 2) : 0;

                if ($vip_1_price > $pay_price) {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => '会员价不能高于普通价'));
                }

                if ($vip_2_price > $vip_1_price) {
                    zib_send_json_error(array('post_id' => $post_id, 'msg' => _pz('pay_user_vip_2_name') . '会员价不能高于' . _pz('pay_user_vip_1_name') . '价格'));
                }

            } else {
                $vip_1_price = (int) ($pay_price * $vip_1_discount) / 100;
                $vip_2_price = (int) ($pay_price * $vip_2_discount) / 100;
            }

            //不能小于0
            $vip_1_price = $vip_1_price < 0 ? 0 : $vip_1_price;
            $vip_2_price = $vip_2_price < 0 ? 0 : $vip_2_price;
            //不能大于正常价
            $vip_1_price = $vip_1_price > $pay_price ? $pay_price : $vip_1_price;
            $vip_2_price = $vip_2_price > $pay_price ? $pay_price : $vip_2_price;

            $pay_data = array(
                'pay_type'    => 1,
                'pay_modo'    => '0',
                'pay_price'   => $pay_price,
                'vip_1_price' => $vip_1_price,
                'vip_2_price' => $vip_2_price,
            );
            break;
    }

    $pay_data['pay_doc'] = !empty($posts_zibpay['pay_doc']) ? strip_tags(trim($posts_zibpay['pay_doc'])) : '';

    $old_zibpay_data = get_post_meta($post_id, 'posts_zibpay', true);
    $old_zibpay_data = is_array($old_zibpay_data) ? $old_zibpay_data : array();
    update_post_meta($post_id, 'posts_zibpay', array_merge($old_zibpay_data, $pay_data));
    zib_update_post_meta($post_id, 'pay_hide_part', $pay_hide_part);
    update_post_meta($post_id, 'allow_view', $allow_view);
    update_post_meta($post_id, 'allow_view_roles', $allow_view_roles);
    if ($echo_success) {
        zib_send_json_success(array('msg' => '阅读权限已保存', 'reload' => true, 'allow_view_roles' => $allow_view_roles, 'allow_view' => $allow_view, 'post_id' => $post_id));
    }
}
add_action('wp_ajax_edit_allow_view', 'zib_bbs_ajax_edit_allow_view');

//前台参与投票
function zib_bbs_ajax_submit_vote()
{
    $post_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : '';
    $voted   = !empty($_REQUEST['voted']) ? (array) $_REQUEST['voted'] : '';

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (!$voted) {
        zib_send_json_error('请选择投票选项');
    }
    $vote_ing = zib_get_post_meta($post_id, 'vote_data', true); //已经投票内容
    $vote_ing = (is_array($vote_ing)) ? $vote_ing : array();
    $user_id  = get_current_user_id();

    foreach ($voted as $index) {
        $vote_ing[$index][] = $user_id;
    }
    $new = $vote_ing;

    //内容去重复
    /**
    $new = array();
    foreach ($vote_ing as $k => $v) {
    $new[$k] = $v;
    }
     */
    zib_update_post_meta($post_id, 'vote_data', $new);
    zib_send_json_success(array('data' => zib_bbs_get_vote_data($post_id), 'vote_data' => $new));

}
add_action('wp_ajax_submit_vote', 'zib_bbs_ajax_submit_vote');

//版主申请
function zib_bbs_ajax_apply_moderator_modal()
{
    $plate_id = !empty($_REQUEST['plate_id']) ? (int) $_REQUEST['plate_id'] : 0;

    //已经是版主
    global $zib_bbs;
    if (zib_bbs_is_the_moderator($plate_id)) {
        zib_ajax_notice_modal('info', '您已是该' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_moderator_name);
    }

    //已经有申请中的流程
    $processing = zib_bbs_get_apply_moderator_processing();
    if ($processing) {
        //已经有申请中的流程
        $moderator_name = $zib_bbs->plate_moderator_name;
        $header         = zib_get_modal_colorful_header('jb-yellow', zib_get_svg('plate-fill'), '申请成为' . $moderator_name);

        $plate_title = get_the_title($processing->meta['plate_id']);
        $con         = '<h5 class="c-red mb20">您的' . $moderator_name . '申请审核处理中，请耐心等待！</h5>';
        $con .= '<div class="mb10 muted-2-color">申请' . $zib_bbs->plate_name . '：' . $plate_title . '</div>';
        $con .= '<div class="mb10 muted-2-color">提交说明：' . $processing->meta['desc'] . '</div>';
        $con .= '<div class="mb20 muted-2-color">提交时间：' . $processing->meta['time'] . '</div>';
        $con .= '<div class="modal-buts but-average"><a type="button" data-dismiss="modal" class="but" href="javascript:;">取消</a></div>';

        echo $header . $con;
        exit;
    }

    echo zib_bbs_get_apply_moderator_modal($plate_id);
    exit;
}
add_action('wp_ajax_apply_moderator_modal', 'zib_bbs_ajax_apply_moderator_modal');

//设置特色封面
function zib_bbs_ajax_edit_posts_featured($post_id = 0, $echo_success = true)
{
    $post_id = $post_id ? $post_id : (!empty($_REQUEST['posts_id']) ? (int) $_REQUEST['posts_id'] : 0);

    //未传入数据，不修改
    if (!isset($_REQUEST['featured_data']['type'])) {
        return;
    }

    $type = $_REQUEST['featured_data']['type'];
    $data = isset($_REQUEST['featured_data']['data']) ? $_REQUEST['featured_data']['data'] : array('url' => false, 'pic' => false, 'ids' => false);

    switch ($type) {
        case 'video': //视频
            if ($data['url']) {
                zib_update_post_meta($post_id, 'featured_video', $data['url']);
            }
            if ($data['pic']) {
                zib_update_post_meta($post_id, 'cover_image', $data['pic']);
            }
            break;

        case 'slide': //幻灯片
            if ($data['ids']) {
                zib_update_post_meta($post_id, 'featured_video', false);
                zib_update_post_meta($post_id, 'featured_slide', implode(',', $data['ids']));
            }
            break;

        case 'image': //图像
            if ($data['url']) {
                zib_update_post_meta($post_id, 'featured_video', false);
                zib_update_post_meta($post_id, 'featured_slide', false);
                zib_update_post_meta($post_id, 'cover_image', $data['url']);
            }
            break;

        case 'close': //删除
            zib_update_post_meta($post_id, 'featured_video', false);
            zib_update_post_meta($post_id, 'featured_slide', false);
            zib_update_post_meta($post_id, 'cover_image', false);

            break;
        default:
    }

}

//手动设置评论为神评论
function zib_bbs_ajax_comment_set_hot()
{
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $id      = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $comment = get_comment($id);
    if (empty($comment->comment_ID)) {
        zib_send_json_error(['msg' => '参数错误或评论已删除']);
    }

    if (!zib_bbs_current_user_can('comment_set_hot', $comment)) {
        zib_send_json_error(['msg' => '权限不足']);
    }

    //刷新缓存
    wp_cache_delete($comment->comment_post_ID, 'posts_lists_hot_comment');
    $is_hot = get_comment_meta($id, 'is_hot', true);
    if ($is_hot) {
        update_comment_meta($id, 'is_hot', 0);
        zib_send_json_success(['msg' => '已取消此评论的神评称号', 'reload' => true]);
    } else {
        update_comment_meta($id, 'is_hot', 1);
        do_action('comment_is_hot', $comment); //添加挂钩
        //刷新缓存
        zib_send_json_success(['msg' => '已将此评论设为神评', 'reload' => true]);
    }

}
add_action('wp_ajax_comment_set_hot', 'zib_bbs_ajax_comment_set_hot');
