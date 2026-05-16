<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2024-12-26 20:51:26
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_ajax_post_delete_modal()
{
    $post_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $post    = get_post($post_id);
    if (empty($post->ID)) {
        zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
    }
    if (!zib_current_user_can('new_post_delete', $post)) {
        zib_ajax_notice_modal('danger', '您没有删除此内容的权限');
    }

    if ('trash' === $post->post_status) {
        zib_ajax_notice_modal('info', '文章已删除，请刷新页面');
    }

    $post_id           = $post->ID;
    $title             = esc_attr($post->post_title);
    $all_comment_count = get_comments_number($post_id);

    $desc = '<div class="c-red mb20">当前文章下下，共有' . $all_comment_count . '次评论，确认要删除吗？</div>';

    $header = zib_get_modal_colorful_header('jb-red', '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>', '确认删除此文章？');

    $title = $title ? '<b>（' . $title . '）</b>' : '';
    $con   = '';
    $con .= '<div class="em12 mb10">您正在删除文章' . $title . '</div>';
    $con .= $desc;

    $hidden_html = '';
    $hidden_html .= '<input type="hidden" name="action" value="new_post_delete">';
    $hidden_html .= '<input type="hidden" name="id" value="' . $post->ID . '">';
    $hidden_html .= wp_nonce_field('new_post_delete', '_wpnonce', false, false); //安全效验

    $footer = '<div class="mt20 but-average">';
    $footer .= $hidden_html;
    $footer .= '<button type="button" data-dismiss="modal" href="javascript:;" class="but">取消</button>';
    $footer .= '<button class="but c-red wp-ajax-submit"><i class="fa fa-trash-o" aria-hidden="true"></i>确认删除</button>';
    $footer .= '</div>';

    $html = '<form class="plate-delete-form">';
    $html .= $header;
    $html .= '<div>';
    $html .= $con;
    $html .= $footer;
    $html .= '</div>';
    $html .= '</form>';

    echo $html;
    exit;
}
add_action('wp_ajax_post_delete_modal', 'zib_ajax_post_delete_modal');

//执行删除文章
function zib_ajax_new_post_delete()
{
    $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $post = get_post($id);

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if ('trash' === $post->post_status) {
        zib_send_json_success(array('msg' => '文章已经删除', 'reload' => true, 'goto' => home_url()));
    }

    if (!zib_current_user_can('new_post_delete', $post)) {
        zib_send_json_error('您没有删除此内容的权限');
    }

    //执行删除文章
    wp_trash_post($post->ID);
    zib_send_json_success(array('msg' => '文章已经删除', 'reload' => true, 'goto' => home_url()));
}
add_action('wp_ajax_new_post_delete', 'zib_ajax_new_post_delete');

//保存前台投稿
function zib_ajax_new_posts()
{

    zib_ajax_verify_nonce('new_posts');

    $cuid        = get_current_user_id();
    $post_author = $cuid;

    if (!_pz('post_article_s')) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '投稿功能已关闭')));
        exit();
    }

    //人机验证
    if (_pz('verification_newposts_s')) {
        zib_ajax_man_machine_verification('newposts_submit');
    }

    $posts_id = !empty($_POST['posts_id']) ? $_POST['posts_id'] : 0;

    //权限判断
    if (!$posts_id && !zib_current_user_can('new_post_add')) {
        zib_send_json_error('抱歉您的权限不足，暂时无法发布');
    }

    if ($posts_id && !zib_current_user_can('new_post_edit', $posts_id)) {
        zib_send_json_error('抱歉您的权限不足，暂时无法编辑此文章');
    }

    $title   = !empty($_POST['post_title']) ? $_POST['post_title'] : false;
    $content = !empty($_POST['post_content']) ? $_POST['post_content'] : false;
    $cat     = !empty($_POST['category']) ? $_POST['category'] : false;
    $action  = !empty($_POST['action']) ? $_POST['action'] : false;

    if (empty($title)) {
        zib_send_json_error('请填写文章标题');
    }
    if (empty($content)) {
        zib_send_json_error('还未填写任何内容');
    }

    if ('posts_save' == $action) {
        $title_strlen_limit = _pz('post_article_title_strlen_limit') ?: array('min' => 5, 'max' => 30);

        if (zib_new_strlen($title) > $title_strlen_limit['max']) {
            zib_send_json_error('标题太长了，不能超过30个字');
        }
        if (zib_new_strlen($title) < $title_strlen_limit['min']) {
            zib_send_json_error('标题太短！');
        }
        if (zib_new_strlen($content) < 10) {
            zib_send_json_error('文章内容过少');
        }
        if (empty($cat)) {
            zib_send_json_error('请选择文章分类');
        }
    }

    //内容合规性判断
    $is_audit = false;
    if (zib_current_user_can('new_post_audit_no')) {
        //拥有免审核权限
        $is_audit = true;
    } else {
        //API审核（拥有免审核权限的用户无需API审核）
        if (_pz('audit_new_post')) {
            $api_is_audit = ZibAudit::is_audit(ZibAudit::ajax_text($title . $content));
            //API审核通过，且拥有免人工审核
            if ($api_is_audit && zib_current_user_can('new_post_audit_no_manual')) {
                $is_audit = true;
            }
        }
    }

    if (!$cuid) {
        //未登录
        if (empty($_POST['user_name'])) {
            zib_send_json_error('请输入昵称');
        }
        $post_author = _pz('post_article_user', 1);
        $lx          = !empty($_POST['contact_details']) ? ',联系：' . esc_attr($_POST['contact_details']) : '';
        $title       = $title . '[投稿-姓名：' . esc_attr($_POST['user_name']) . $lx . ']';
    }

    $cat  = array();
    $cat  = !empty($_POST['category']) ? (array) $_POST['category'] : false;
    $tags = preg_split("/,|，|\n/", $_POST['tags']);

    $postarr = array(
        'post_type'      => 'post',
        'post_title'     => $title,
        'post_status'    => 'draft',
        'ID'             => $posts_id,
        'post_content'   => $content,
        'post_category'  => $cat,
        'comment_status' => 'open',
    );

    if (_pz('post_article_tag')) {
        $postarr['tags_input'] = $tags;
    }

    $is_new = true;
    if (!$posts_id) {
        //新建时候，添加作者
        $postarr['post_author'] = $post_author;
    } else {
        //编辑文章
        $post_obj = get_post($posts_id, ARRAY_A);
        if (isset($post_obj['ID'])) {
            $is_new  = false;
            $postarr = array_merge($post_obj, $postarr);
        }
    }

    if ('posts_save' === $action) {
        $postarr['post_status'] = 'pending'; //默认为待审核状态 发布无需审核
        //通过了API审核或者自己拥有无需审核权限
        if ($is_audit) {
            $postarr['post_status'] = 'publish';
        }
    }

    //编辑器允许插入嵌入视频
    if (zib_current_user_can('new_post_iframe_video')) {
        add_filter('wp_kses_allowed_html', 'zib_allow_html_iframe_attributes', 99, 2);
    }

    //添加保存前的挂钩
    do_action('zib_pre_insert_post', $postarr);

    //保存文章
    $in_id = wp_insert_post($postarr, 1);

    if (is_wp_error($in_id)) {
        zib_send_json_error($in_id->get_error_message());
    }
    if (!$in_id) {
        zib_send_json_error('文章保存失败，请稍后再试');
    }

    zib_ajax_newpost_save_pay($in_id, false); //执行保存付费
    zib_ajax_newpost_save_featured($in_id, false); //保存封面

    //执行保存专题
    if (_pz('post_article_topics_s')) {
        $topics = !empty($_POST['topics']) ? (array) $_POST['topics'] : [];
        wp_set_post_terms($in_id, $topics, 'topics');
    }

    //执行保存高级筛选
    if (_pz('post_article_custom_filter_s')) {
        $filter_args = zib_get_custom_filter_args();
        if ($filter_args) {
            foreach ($filter_args as $filter) {
                update_post_meta($in_id, $filter['key'], (isset($_POST[$filter['key']]) ? (array) $_POST[$filter['key']] : array()));
            }
        }
    }

    $url              = get_permalink($in_id);
    $send             = array();
    $send['posts_id'] = $in_id;
    $send['url']      = $url;
    $new_post_obj     = get_post($in_id);

    //添加挂钩
    do_action('new_' . ($is_new ? 'add' : 'edit') . '_posts', $new_post_obj);

    switch ($postarr['post_status']) {
        case 'pending': //待审核
            //添加挂钩
            do_action('new_posts_pending', $new_post_obj);

            $send['msg'] = '内容已提交，正在等待审核';
            if ($cuid) {
                $send['reload'] = true;
                $send['goto']   = zib_get_user_home_url($post_author, array('post_status' => 'pending'));
            }

            break;
        case 'draft': //草稿
            $send['msg']       = '草稿已保存';
            $send['time']      = current_time('mysql');
            $send['posts_url'] = $url;

            break;
        default:
            $send['msg']    = '文章已发布';
            $send['reload'] = true;
            $send['goto']   = $url;
    }

    zib_send_json_success($send);
}
add_action('wp_ajax_posts_save', 'zib_ajax_new_posts');
add_action('wp_ajax_nopriv_posts_save', 'zib_ajax_new_posts');
add_action('wp_ajax_posts_draft', 'zib_ajax_new_posts');
add_action('wp_ajax_nopriv_posts_draft', 'zib_ajax_new_posts');

function zib_ajax_newpost_save_pay($post_id = 0, $echo_success = true)
{

    $post_id  = $post_id ? $post_id : (!empty($_REQUEST['posts_id']) ? (int) $_REQUEST['posts_id'] : 0);
    $zibpay_s = !empty($_REQUEST['zibpay_s']) ? true : false;
    if (!isset($_POST['posts_zibpay']) || !isset($_POST['posts_zibpay']['pay_modo'])) {
        return;
    }

    $pay_modo       = $_POST['posts_zibpay']['pay_modo'] === 'points' ? 'points' : '0';
    $posts_zibpay   = $_POST['posts_zibpay'];
    $vip_input_s    = _pz('post_article_pay_vip_price_s');
    $vip_1_discount = _pz('post_article_pay_vip_1_discount', 100);
    $vip_2_discount = _pz('post_article_pay_vip_2_discount', 100);
    $pay_data       = array('pay_type' => 'no');

    if ($zibpay_s) {
        $pay_price_limit_pz = _pz('post_article_pay_price_limit');
        if ($pay_modo !== 'points') {
            $pay_price = !empty($posts_zibpay['pay_price']) ? round((float) $posts_zibpay['pay_price'], 2) : 0;
            if (_pz('post_article_pay_free_s')) {
                //金额允许设置为 0
                if (!isset($posts_zibpay['pay_price']) || !is_numeric($posts_zibpay['pay_price'])) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置付费内容的价格'));
                }
            } elseif (!$pay_price) {
                zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置付费内容的价格'));
            }

            if ($pay_price < 0) {
                zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置正确的价格'));
            }

            //价格区间限制
            $pay_price_limit = !empty($pay_price_limit_pz['price']) ?$pay_price_limit_pz['price'] : false;
            if ($pay_price_limit) {
                $pay_price_limit_min = !empty($pay_price_limit['min']) ?$pay_price_limit['min'] : 0;
                $pay_price_limit_max = !empty($pay_price_limit['max']) ?$pay_price_limit['max'] : 0;
                if($pay_price_limit_min && $pay_price < $pay_price_limit_min){
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '价格不能低于：' . $pay_price_limit_min));
                }
                if($pay_price_limit_max && $pay_price > $pay_price_limit_max){
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '价格不能高于：' . $pay_price_limit_max));
                }
            }


            if ($vip_input_s) {
                if (isset($posts_zibpay['vip_1_price']) && !$posts_zibpay['vip_1_price'] && $posts_zibpay['vip_1_price'] != '0') {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_1_name') . '价格'));
                }
                if (isset($posts_zibpay['vip_2_price']) && !$posts_zibpay['vip_2_price'] && $posts_zibpay['vip_2_price'] != '0') {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_2_name') . '价格'));
                }

                $vip_1_price = !empty($posts_zibpay['vip_1_price']) ? round((float) $posts_zibpay['vip_1_price'], 2) : 0;
                $vip_2_price = !empty($posts_zibpay['vip_2_price']) ? round((float) $posts_zibpay['vip_2_price'], 2) : 0;

                if ($vip_1_price > $pay_price) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '会员价不能高于普通价'));
                }

                if ($vip_2_price > $vip_1_price) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => _pz('pay_user_vip_2_name') . '会员价不能高于' . _pz('pay_user_vip_1_name') . '价格'));
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

        } else {
            $points_price = !empty($posts_zibpay['points_price']) ? (int) $posts_zibpay['points_price'] : 0;
            if (!$points_price) {
                zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置付费内容的积分价格'));
            }

            if ($points_price < 0) {
                zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置正确的积分价格'));
            }

            //价格区间限制
            $points_price_limit = !empty($pay_price_limit_pz['points']) ?$pay_price_limit_pz['points'] : false;
            if ($points_price_limit) {
                $points_price_limit_min = !empty($points_price_limit['min']) ?$points_price_limit['min'] : 0;
                $points_price_limit_max = !empty($points_price_limit['max']) ?$points_price_limit['max'] : 0;
                if($points_price_limit_min && $points_price < $points_price_limit_min){
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '积分价格不能低于：' . $points_price_limit_min));
                }
                if($points_price_limit_max && $points_price > $points_price_limit_max){
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '积分价格不能高于：' . $points_price_limit_max));
                }
            }

            if ($vip_input_s) {
                if (isset($posts_zibpay['vip_1_points']) && !$posts_zibpay['vip_1_points'] && $posts_zibpay['vip_1_points'] != '0') {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_1_name') . '积分售价'));
                }
                if (isset($posts_zibpay['vip_2_points']) && !$posts_zibpay['vip_2_points'] && $posts_zibpay['vip_2_points'] != '0') {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '请设置' . _pz('pay_user_vip_2_name') . '积分售价'));
                }

                $vip_1_points = !empty($posts_zibpay['vip_1_points']) ? (int) $posts_zibpay['vip_1_points'] : 0;
                $vip_2_points = !empty($posts_zibpay['vip_2_points']) ? (int) $posts_zibpay['vip_2_points'] : 0;

                if ($vip_1_points > $points_price) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => '会员价不能高于普通价'));
                }

                if ($vip_2_points > $vip_1_points) {
                    zib_send_json_error(array('posts_id' => $post_id, 'msg' => _pz('pay_user_vip_2_name') . '价格不能高于' . _pz('pay_user_vip_1_name') . '价格'));
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
        }
    }

    $pay_data['pay_doc'] = !empty($posts_zibpay['pay_doc']) ? strip_tags(trim($posts_zibpay['pay_doc'])) : '';
    if (!empty($posts_zibpay['pay_download'][0]['link'])) {

        foreach ($posts_zibpay['pay_download'] as $_k => $_v) {
            foreach ($_v as $k => $v) {
                $posts_zibpay['pay_download'][$_k][$k] = strip_tags(trim($v));
            }
        }

        $pay_data['pay_download'] = $posts_zibpay['pay_download'];
        if ($zibpay_s) {
            $pay_data['pay_type'] = 2;
        }
    }

    $old_data = get_post_meta($post_id, 'posts_zibpay', true);
    if (isset($old_data['pay_type']) && $pay_data['pay_type'] !== 'no' && $pay_data['pay_type'] !== 1 && $pay_data['pay_type'] !== 2) {
        $pay_data['pay_type'] = $old_data['pay_type'];
    }

    if (!isset($old_data['pay_type'])) {
        $old_data = array(
            'pay_type'            => 'no',
            'pay_limit'           => '0',
            'pay_modo'            => '0',
            'points_price'        => '',
            'vip_1_points'        => '',
            'vip_2_points'        => '',
            'pay_price'           => '',
            'vip_1_price'         => '',
            'vip_2_price'         => '',
            'pay_rebate_discount' => 0,
            'pay_cuont'           => 0,
            'pay_details'         => '',
        );
    }

    $pay_data = array_merge($old_data, $pay_data);

    update_post_meta($post_id, 'posts_zibpay', $pay_data);
    if ($echo_success) {
        zib_send_json_success(array('msg' => '文章付费功能保存成功', 'reload' => true, 'post_id' => $post_id));
    }
}

//设置特色封面
function zib_ajax_newpost_save_featured($post_id = 0, $echo_success = true)
{
    $post_id = $post_id ? $post_id : (!empty($_REQUEST['posts_id']) ? (int) $_REQUEST['posts_id'] : 0);

    //未传入数据，不修改
    if (!isset($_REQUEST['featured_data']['type'])) {
        return;
    }

    $type = $_REQUEST['featured_data']['type'];
    $data = $_REQUEST['featured_data']['data'];

    switch ($type) {
        case 'video': //视频
            if ($data['url']) {
                zib_update_post_meta($post_id, 'featured_video', $data['url']);
            }
            if ($data['pic']) {
                zib_update_post_meta($post_id, 'cover_image', $data['pic']);
                zib_update_post_meta($post_id, 'thumbnail_url', $data['pic']);
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
                zib_update_post_meta($post_id, 'thumbnail_url', $data['url']);
            }
            break;

        case 'close': //删除
            zib_update_post_meta($post_id, 'featured_video', false);
            zib_update_post_meta($post_id, 'featured_slide', false);
            zib_update_post_meta($post_id, 'cover_image', false);

            break;
        default:
    }

    if ($echo_success) {
        zib_send_json_success(array('msg' => '封面保存成功', 'reload' => true, 'post_id' => $post_id));
    }
}
