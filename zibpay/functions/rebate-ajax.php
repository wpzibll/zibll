<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-28 16:11:06
 * @LastEditTime : 2026-05-06 09:59:09
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户佣金明细列表
 * @param {*}
 * @return {*}
 */
function zibpay_ajax_rebate_user_detail()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    //准备查询参数
    $user_id       = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : $user_id;
    $paged         = zib_get_the_paged();
    $ice_perpage   = !empty($_REQUEST['ice_perpage']) ? (int) $_REQUEST['ice_perpage'] : 10;
    $rebate_status = isset($_REQUEST['rebate_status']) ? (int) $_REQUEST['rebate_status'] : '';

    $db = ZibDB::name(zibpay::$order_table_name)->where('status', 1)->where('rebate_price', '>', 0)->where('referrer_id', $user_id)->order('pay_time', 'DESC')->page($paged, $ice_perpage);
    if ($rebate_status) {
        $db->where('rebate_status', $rebate_status);
    }
    $db_order  = $db->select()->result();
    $count_all = $db->count();

    $html  = '';
    $lists = '';

    if ($db_order) {
        foreach ($db_order as $order) {
            $order_num       = $order->order_num;
            $pay_time        = $order->pay_time;
            $post_id         = $order->post_id;
            $order_type_name = zibpay_get_pay_type_name($order->order_type);
            $pay_title       = $order_type_name ? '<div class="pay-tag badg badg-sm mr6">' . $order_type_name . '</div>' : '';
            if ($post_id) {
                $posts_title = get_the_title($post_id);
                $permalink   = get_permalink($post_id);
                $pay_title .= '<a target="_blank" class="" href="' . $permalink . '">' . $posts_title . '</a>';
            }

            $class         = 'order-type-' . $order->order_type;
            $rebate_status = $order->rebate_status ? '<span class="c-blue badg badg-sm">已提现</span>' : '<span class="c-yellow badg badg-sm">未提现</span>';
            if ($order->rebate_status == 3) {
                $rebate_status = '<span class="c-yellow badg badg-sm">提现待处理</span>';
            }

            $lists .= '<div class="jsb flex border-bottom padding-h10 ajax-item ' . $class . '">';
            $lists .= '<div class="">';
            $lists .= '<div class="mb6">' . $pay_title . '</div>';
            $lists .= '<div class="muted-2-color em09">订单号：' . $order_num . '</div>';
            $lists .= '<div class="muted-2-color em09">时间：' . $pay_time . '</div>';
            $lists .= '</div>';
            $lists .= '<div class="felx0 flex xx jsb"><div class="c-yellow"><span class="mr3 px12">' . zibpay_get_pay_mark() . '</span><b class="em14">' . floatval($order->rebate_price) . '</b></div><div class="text-right">' . $rebate_status . '</div></div>';
            $lists .= '</div>';
        }

        $ajax_url = add_query_arg('action', 'rebate_detail', admin_url('admin-ajax.php'));
        if (isset($_REQUEST['rebate_status'])) {
            $ajax_url = add_query_arg('rebate_status', $_REQUEST['rebate_status'], $ajax_url);
        }
        $lists .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url);
    } else {
        $lists .= zib_get_ajax_null('暂无订单', 60, 'null-order.svg');
    }

    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_rebate_detail', 'zibpay_ajax_rebate_user_detail');

//获取用户佣金推荐用户列表
function zibpay_ajax_rebate_user_user_lists()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $paged       = zib_get_the_paged();
    $ice_perpage = !empty($_REQUEST['ice_perpage']) ? (int) $_REQUEST['ice_perpage'] : 10;

    $users_args = array(
        'order'       => 'DESC',
        'orderby'     => 'user_registered',
        'number'      => $ice_perpage,
        'paged'       => $paged,
        'count_total' => true,
        'meta_query'  => array(
            array(
                'key'     => 'referrer_id',
                'value'   => $user_id,
                'compare' => '=',
            ),
        ),
        //    'fields'      => array('display_name', 'ID', 'user_email'),
    );

    $lists     = '';
    $query     = new WP_User_Query($users_args);
    $all_count = 0;
    if ($query) {
        $all_count   = $query->get_total();
        $get_results = $query->get_results();
        if ($get_results) {
            foreach ($get_results as $item) {
                $display_name = zib_get_user_name($item->ID);
                $avatar       = zib_get_avatar_box($item->ID);
                $desc         = get_date_from_gmt($item->user_registered);
                $btn          = zib_get_user_follow('focus-color px12 ml10 follow flex0', $item->ID);

                $lists .= '<div class="author-minicard radius8 relative-h ajax-item">
                    <ul class="list-inline relative">
                        <li>' . $avatar . '
                        </li>
                        <li>
                            <dl>
                                <dt class="flex ac">' . $display_name . $btn . '</dt>
                                <dd class="mt6 em09 muted-color text-ellipsis">' . zib_get_time_ago($desc) . '注册</dd>
                            </dl>
                        </li>
                    </ul>
                    </div>';
            }
        }
        $ajax_url = add_query_arg('action', 'rebate_users', admin_url('admin-ajax.php'));

        $lists .= zib_get_ajax_next_paginate($all_count, $paged, $ice_perpage, $ajax_url);

    } else {
        $lists .= zib_get_ajax_null('暂无推荐用户', 50, 'null-user.svg');
    }

    zib_ajax_send_ajaxpager($lists);

}
add_action('wp_ajax_rebate_users', 'zibpay_ajax_rebate_user_user_lists');
