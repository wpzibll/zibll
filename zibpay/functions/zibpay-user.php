<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-05-05 22:58:45
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_user_center_page_sidebar_vip($con)
{
    if (!_pz('pay_user_vip_1_s') && !_pz('pay_user_vip_2_s')) {
        return $con;
    }
    $user_id   = get_current_user_id();
    $vip_level = zib_get_user_vip_level($user_id);

    if ($vip_level) {
        $button = zibpay_get_vip_card_icon($vip_level, 'em12 mr6') . '<span>' . _pz('pay_user_vip_' . $vip_level . '_name') . '</span>' . '<span class="ml10 badg jb-yellow vip-expdate-tag">' . zib_get_svg('time', null, 'mr3') . zib_get_user_vip_exp_date_text($user_id) . '</span>';
    } else {
        $button = zib_get_svg('vip_1', '0 0 1024 1024', 'em12 mr6') . '开通会员 尊享会员权益';
    }

    $con .= '<div class="mb20 mb10-sm payvip-icon btn-block badg pointer' . (!$user_id ? ' signin-loader' : '') . '" style="padding: 14px 10px;" data-onclick="[data-target=\'#user-tab-vip\']">' . $button . '<i class="fa fa-chevron-circle-right abs-right"></i></div>';
    return $con;
}
add_filter('user_center_page_sidebar', 'zib_user_center_page_sidebar_vip', 20);

function zib_user_center_page_sidebar_balance($con)
{
    $points_s      = _pz('points_s');
    $pay_balance_s = _pz('pay_balance_s');
    if (!$points_s && !$pay_balance_s) {
        return $con;
    }
    $user_id = get_current_user_id();

    $box = '';
    if ($pay_balance_s) {
        $user_balance = $user_id ? _cut_count(zibpay_get_user_balance($user_id)) : 0;

        $box .= '<div style="flex: 1;" class="mb10-sm zib-widget padding-10 flex1">
                    <div class="muted-color em09 mb6">余额<i class="ml6 fa fa-angle-right em12"></i></div>
                    <div class="flex jsb"><span class="font-bold c-blue-2 em12">' . $user_balance . '</span>' . zib_get_svg('money-color-2', null, 'em14') . '</div>
                </div>';
    }

    if ($points_s) {
        $user_points = $user_id ? _cut_count(zibpay_get_user_points($user_id)) : 0;

        $box .= '<div style="flex: 1;" class="mb10-sm zib-widget padding-10 flex1">
                    <div class="muted-color em09 mb6">积分<i class="ml6 fa fa-angle-right em12"></i></div>
                    <div class="flex jsb"><span class="font-bold c-yellow em12">' . $user_points . '</span>' . zib_get_svg('points-color', null, 'em14') . '</div>
                </div>';
    }

    $con .= '<div class="flex ab jsb col-ml6 pointer' . (!$user_id ? ' signin-loader' : '') . '" data-onclick="[data-target=\'#user-tab-balance\']">' . $box . '</div>';
    return $con;

}
add_filter('user_center_page_sidebar', 'zib_user_center_page_sidebar_balance', 30);

function zib_user_center_page_sidebar_income_rebate($con)
{
    $pay_income_s = _pz('pay_income_s');
    $pay_rebate_s = _pz('pay_rebate_s');
    if (!$pay_income_s && !$pay_rebate_s) {
        return $con;
    }
    $user_id = get_current_user_id();

    $box = '';
    if ($pay_income_s) {
        $today_data = zibpay_get_user_today_income_data($user_id);
        if ($today_data['sum']) {
            $a = '今日收入';
            $b = _cut_count(floatval($today_data['sum']));
        } else {
            $all_data = zibpay_get_user_income_data($user_id);
            $a        = '收入';
            $b        = _cut_count(floatval($all_data['sum']));
        }

        $c = _cut_count(zibpay_get_user_income_post_count($user_id));

        $box .= '<div style="flex: 1;" class="mb10-sm zib-widget padding-10 flex1 pointer" data-onclick="[data-target=\'#user-tab-income\']">
                    <div class="muted-color em09 mb6 flex ac"><div class="overflow-hidden"><div class="text-ellipsis">' . zib_get_svg('merchant-color', null, 'mr6') . '创作中心</div></div><i class="ml6 fa fa-angle-right em12"></i></div>
                    <div class="flex jsa text-center"><div class=""><div class="font-bold em12">' . $c . '</div><div class="px12 opacity5">商品</div></div><div class="overflow-hidden"><div class="font-bold em12">' . $b . '</div><div class="px12 opacity5 text-ellipsis">' . $a . '</div></div></div>
                </div>';

    }

    if ($pay_rebate_s) {
        $rebate_effective_data = zibpay_get_user_rebate_data($user_id, 'effective');

        if ($rebate_effective_data['sum']) {
            $a = '待提现';
            $b = _cut_count(floatval($rebate_effective_data['sum']));
        } else {
            $a               = '累计佣金';
            $rebate_all_data = zibpay_get_user_rebate_data($user_id, 'all');
            $b               = _cut_count(floatval($rebate_all_data['sum']));
        }
        $rebate_ratio = 0;
        if ($user_id) {
            $rebate_rule  = zibpay_get_user_rebate_rule($user_id);
            $rebate_ratio = $rebate_rule['type'] ? ($rebate_rule['ratio'] ? $rebate_rule['ratio'] : 0) : 0;
        }

        $box .= '<div style="flex: 1;" class="mb10-sm zib-widget padding-10 flex1 pointer" data-onclick="[data-target=\'#user-tab-rebate\']">
                    <div class="muted-color em09 mb6 flex ac"><div class="overflow-hidden"><div class="text-ellipsis">' . zib_get_svg('money-color', null, 'mr6') . '推广中心</div></div><i class="ml6 fa fa-angle-right em12"></i></div>
                    <div class="flex jsa text-center"><div class=""><div class="font-bold em12">' . $rebate_ratio . '%</div><div class="px12 opacity5">比例</div></div><div class="overflow-hidden"><div class="font-bold em12">' . $b . '</div><div class="px12 opacity5 text-ellipsis">' . $a . '</div></div></div>
                </div>';
    }

    $con .= '<div class="flex ab jsb col-ml6' . (!$user_id ? ' signin-loader' : '') . '">' . $box . '</div>';
    return $con;
}
add_filter('user_center_page_sidebar', 'zib_user_center_page_sidebar_income_rebate', 40);

/**挂钩到用户中心 */
function zibpay_user_page_tabs_array($tabs_array)
{
    $tabs = array();

    //vip会员
    if (_pz('pay_user_vip_1_s') || _pz('pay_user_vip_2_s')) {
        $tabs['vip'] = array(
            'title'    => '我的会员',
            'nav_attr' => 'drawer-title="我的会员"',
            'loader'   => '<div class="zib-widget"><i class="placeholder s1"></i><p class="placeholder t1"></p>
            <p style="height: 110px;" class="placeholder k1"></p><p class="placeholder k2"></p><p style="height: 110px;" class="placeholder k1"></p><p class="placeholder t1"></p><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></div>',
        );
    }

    //余额或积分
    if (_pz('pay_balance_s') || _pz('points_s')) {
        $tabs['balance'] = array(
            'title'    => '我的资产',
            'nav_attr' => 'drawer-title="我的资产"',
            'loader'   => '<div class="row gutters-10 user-pay"><div class="col-sm-6"><div class="zib-widget"><div class="placeholder s1"></div><div class="em3x c-blue">--</div><i class="placeholder s1 mr10"></i><i class="placeholder s1"></i></div></div><div class="col-sm-6"><div class="zib-widget"><div class="placeholder s1"></div><div class="em3x c-blue">--</div><i class="placeholder s1 mr10"></i><i class="placeholder s1"></i></div></div></div><div class="box-body notop"><div class="title-theme"><b>订单明细</b></div></div>' . str_repeat('<div class="zib-widget"><p class="placeholder k1"></p><p class="placeholder t1"></p><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></div>', 3),
        );
    }

    //销售分成
    if (_pz('pay_income_s')) {
        $tabs['income'] = array(
            'title'    => '创作分成', //今日收入
            'nav_attr' => 'drawer-title="创作分成"',
            'loader'   => '<div class="row gutters-10 user-pay"><div class="col-sm-6"><div class="zib-widget"><div class="placeholder s1"></div><div class="em3x c-blue">--</div><i class="placeholder s1 mr10"></i><i class="placeholder s1"></i></div></div><div class="col-sm-6"><div class="zib-widget"><div class="placeholder s1"></div><div class="em3x c-blue">--</div><i class="placeholder s1 mr10"></i><i class="placeholder s1"></i></div></div></div><div class="box-body notop"><div class="title-theme"><b>订单明细</b></div></div>' . str_repeat('<div class="zib-widget"><p class="placeholder k1"></p><p class="placeholder t1"></p><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></div>', 3),
        );
    }

    //推广返利
    if (_pz('pay_rebate_s')) {
        $tabs['rebate'] = array(
            'title'    => '推广中心',
            'nav_attr' => 'drawer-title="推广中心"',
            'loader'   => '<div class="row gutters-10"><div class="col-sm-6"><div class="zib-widget jb-red" style="height: 136px;"></div></div>
            <div class="col-sm-6"><div style="height: 136px;" class="zib-widget jb-blue"></div></div></div><div class="zib-widget"><div class="box-body"><p class="placeholder k1"></p><p class="placeholder k2"></p><p class="placeholder k1" style="height: 120px;"></p><p class="placeholder t1"></p>
            <p class="placeholder k1"></p><p class="placeholder t1"></p><p class="placeholder k1"></p>
            <p class="placeholder k1"></p>
            <p class="placeholder k1"></p>
            </div></div>',
        );
    }

    //订单明细
    if (_pz('pay_show_user')) {
        $tabs['order'] = array(
            'title'    => '我的订单',
            'nav_attr' => 'drawer-title="我的订单"',
            'loader'   => '<div class="row gutters-10 user-pay"><div class="col-sm-6"><div class="zib-widget"><div class="placeholder s1"></div><div class="em3x c-blue">--</div><i class="placeholder s1 mr10"></i><i class="placeholder s1"></i></div></div><div class="col-sm-6"><div class="zib-widget"><div class="placeholder s1"></div><div class="em3x c-blue">--</div><i class="placeholder s1 mr10"></i><i class="placeholder s1"></i></div></div></div><div class="box-body notop"><div class="title-theme"><b>订单明细</b></div></div>' . str_repeat('<div class="zib-widget"><p class="placeholder k1"></p><p class="placeholder t1"></p><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></div>', 3),
        );

        add_filter('zib_user_center_page_sidebar_button_1_args', 'zibpay_user_center_page_sidebar_button_1_args_order');
    }

    if ($tabs) {
        return $tabs + $tabs_array;
    }
    return $tabs_array;
}
add_filter('user_ctnter_main_tabs_array', 'zibpay_user_page_tabs_array');

function zibpay_user_center_page_sidebar_button_1_args_order($buttons)
{
    $args = array(
        array(
            'html' => '',
            'icon' => zib_get_svg('order-color'),
            'name' => '我的订单',
            'tab'  => 'order',
        ),
    );
    return array_merge($args, $buttons);
}

//用户中心vip tab
function zibpay_user_page_tab_content_vip()
{
    $user_id = get_current_user_id();

    return zib_get_ajax_ajaxpager_one_centent(zibpay_user_vip_box($user_id));
}
add_filter('main_user_tab_content_vip', 'zibpay_user_page_tab_content_vip');

/**
 * 用户订单金额统计
 */
function zibpay_get_user_pay_price($user_id, $type = '', $order_type = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    $sum = 0;

    $db = ZibDB::name(zibpay::$order_table_name)->where('user_id', $user_id)->where('status', 1);
    if ($order_type) {
        $db->where('order_type', $order_type);
    }

    if ('order_price' == $type) {
        $sum = $db->sum('order_price');
    } elseif ('pay_price' == $type) {
        $sum = $db->sum('pay_price');
    }

    return $sum ? $sum : 0;
}

/**
 * 用户订单数量统计
 */
function zibpay_get_user_order_count($user_id, $type = '')
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    $db = ZibDB::name(zibpay::$order_table_name)->where('user_id', $user_id)->where('status', 1);
    if ($type) {
        $db->where('order_type', $type);
    }
    $count = $db->count('user_id');
    return $count;
}

/**
 * 用户中心统计信息
 */
function zibpay_get_user_pay_statistical($user_id)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    $count_all = zibpay_get_user_order_count($user_id);

    $sumprice_all = zibpay_get_user_pay_price($user_id, 'pay_price');

    $con = '<div class="row gutters-10">';
    $con .= '<div class="col-sm-6">
            <div class="zib-widget" style="padding-left: 24px;">
                <div>
                ' . zib_get_svg('order-color', null, 'em12 mr6') . '全部订单
                </div>
                <div class="mt10" style="color: #8080f0;line-height: 1.2;">
                <span class="em3x font-bold mr6">' . $count_all . '</span>笔
                </div>
                <div class="abs-right em3x">' . zib_get_svg('order-color', null, 'em12') . '</div>
            </div>
        </div>';
    $con .= '<div class="col-sm-6">
        <div class="zib-widget" style="padding-left: 24px;">
            <div>
            ' . zib_get_svg('money-color', null, 'em12 mr6') . '支付金额
            </div>
            <div class="mt10" style="color: #fc7032;line-height: 1.2;">
            ' . zibpay_get_pay_mark() . '<span class="em3x font-bold ml6">' . $sumprice_all . '</span>
            </div>
            <div class="abs-right em3x">' . zib_get_svg('money-color', null, 'em12') . '</div>
        </div>
    </div>';

    $con .= '</div>';

    return $con;
}

/**
 * @description: 获取用户支付订单列表
 * @param int $user_id 用户ID：默认为当前登录ID
 * @param int $paged 获取的页码
 * @param int $ice_perpage 每页加载数量
 * @return {*}
 */
function zibpay_get_user_order($user_id = '', $paged = 1, $ice_perpage = 10)
{

    $user_id = $user_id ? $user_id : get_current_user_id();
    if (!$user_id) {
        return;
    }

    //准备查询参数
    $paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : $paged;
    $ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : $ice_perpage;
    $orderby     = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'id';

    $query_args = [
        'user_id'  => $user_id,
        'orderby'  => $orderby,
        'order'    => 'DESC',
        'paged'    => $paged,
        'per_page' => $ice_perpage,
    ];

    if (isset($_REQUEST['status'])) {
        $query_args['status'] = (int) $_REQUEST['status'];
    }

    $tab             = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
    $shop_order_type = zib_shop_get_order_type();

    switch ($tab) {
        //待支付
        case 'wait-pay':
            $query_args['status'] = 0;
            break;

        //已支付
        case 'paid':
            $query_args['status'] = 1;
            break;

        //已关闭
        case 'closed':
            $query_args['status'] = -1;
            break;

        //发货状态
        case 'wait-shipped': //待发货
        case 'wait-receive': //待收货
            $query_args['status']     = 1;
            $query_args['order_type'] = $shop_order_type;
            $query_args['meta_query'] = [
                [
                    'key'   => 'shipping_status',
                    'value' => $tab == 'wait-receive' ? 1 : 0, //0未发货，1已发货，2已收货
                ],
            ];

            if ($tab == 'wait-receive' && empty($_REQUEST['orderby'])) {
                $query_args['orderby'] = 'shipping_time';
            }
            break;

        //评价状态
        case 'wait-evaluate':
            $query_args['status']     = 1;
            $query_args['order_type'] = $shop_order_type;
            $query_args['meta_query'] = [
                [
                    'key'   => 'shipping_status',
                    'value' => 2, //0未发货，1已发货，2已收货：必须收货以后才能评价
                ],
                [
                    'key'   => 'comment_status',
                    'value' => 0, //0未评价，1已评价
                ],
            ];

            if (empty($_REQUEST['orderby'])) {
                $query_args['orderby'] = 'shipping_time';
            }

            break;

        //售后/退款
        case 'after-sale':
            $query_args['status']            = [-2, 1];
            $query_args['order_type']        = $shop_order_type;
            $query_args['after_sale_status'] = [1, 2, 3];
            if (empty($_REQUEST['orderby'])) {
                $query_args['orderby'] = 'after_sale_time';
            }
            break;

        default:
            break;
    }

    $db_order  = zibpay::order_query($query_args);
    $count_all = $db_order['total'] ?? 0;

    //缓存订单数量
    if ($tab !== 'after-sale') {
        $cache_key = 'user_order_count_' . $user_id . '_' . $tab;
        wp_cache_set($cache_key, $count_all, 'user_order_data');
    }

    $lists = '';
    if ($db_order['orders']) {
        foreach ($db_order['orders'] as $order) {
            $lists .= zibpay_get_user_order_list_card($order);
        }

        // 显示下一页按钮
        $ajax_url = zib_get_current_url();
        $lists .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url);
    } else {
        $lists .= zib_get_ajax_null('暂无订单', 40, 'null-order.svg');
    }

    $html = $lists;
    return $html;
}

function zibpay_get_user_order_list_card($order)
{
    $order_type = $order['order_type'];

    $html = apply_filters('user_order_list_card', '', $order_type, $order);
    if ($html) {
        return $html;
    }

    return zibpay_get_user_order_list_card_default($order);
}

//订单详情模态框
function zibpay_get_order_details_modal($order)
{
    $order['status'] = zibpay_get_order_status($order);
    $order_type      = $order['order_type'];
    $html            = apply_filters('user_order_details_modal', '', $order_type, $order);
    if ($html) {
        return $html;
    }

    $current_time = current_time('Y-m-d H:i:s');
    $status       = (string) $order['status'];
    $status_name  = zibpay_get_order_status_name($status);
    $header_title = $status_name;
    if ($status == 0) {
        $time_remaining = zibpay_get_order_pay_over_time($order);
        if ($time_remaining == 'over') {
            $order['status'] = '-1';
            $status          = '-1';
        } else {
            $time_remaining = zib_time_to_c($time_remaining);
            $time_countdown = '<div class="mt6 em09 muted-2-color">剩余<span class="c-yellow badg badg-sm" int-second="1" data-over-text="1秒" data-countdown="' . $time_remaining . '"></span>自动关闭订单</div>';
            $header_title   = '<div class="c-yellow mb6 font-bold">' . zib_get_svg('time') . ' 待支付</div>' . $time_countdown;
        }
    }

    $footer_right    = '';
    $footer_left     = '';
    $_img            = '';
    $order_data      = zibpay::get_meta($order['id'], 'order_data');
    $order_type_name = zibpay_get_pay_type_name($order_type);
    $_count          = $order_data['count'] ?? 0;
    $post_id         = $order['post_id'];
    $post            = get_post($post_id);
    $_opt_name       = $order_data['options_active_name'] ?? '';
    $is_points       = $order['pay_type'] == 'points';
    $_mark           = '<span class="pay-mark px12">' . ($is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark()) . '</span>';
    $_title          = $order_data['product_title'] ?? '';

    if ($status == 0) {
        $footer_right = zibpay_get_order_close_link($order, 'but mr6', '关闭订单');
        $footer_right .= zibpay_get_order_pay_link($order, 'but c-red', '立即支付');

    } elseif ($status == -1) {
        $header_title = '<div class="font-bold">交易关闭</div>';
        $header_title .= !empty($order_data['close_reason']) ? '<div class="px12 muted-2-color font-normal">' . $order_data['close_reason'] . '</div>' : '';
    } elseif ($status == 1) {
        $header_title = '<b class="inflex ac"><i class="fa fa-check-circle-o mr6 em12"></i>交易完成</b>';
    }

    if (isset($order_data['prices']['unit_price'])) {
        $_unit_price = $order_data['prices']['unit_price']; //原价的单价
    } else {
        $_unit_price = $order['order_price'];
    }

    if (isset($order_data['prices']['pay_price'])) {
        $_total_price = $order_data['prices']['pay_price']; //总价的优惠价
    } else {
        $_total_price = $order['pay_price'] ?: $order['order_price'];

        if (!$_total_price && $order_type !== '10' && $_count <= 1) {
            $_unit_price  = zibpay_get_order_effective_amount($order);
            $_total_price = $_unit_price;
        }
    }

    $_total_price_type_name     = $status == '1' ? '实付' : '应付';
    $_unit_price                = $_mark . '<b>' . $_unit_price . '</b>';
    $_total_price               = $_mark . '<b>' . $_total_price . '</b>';
    $shop_s                     = _pz('shop_s');
    $post_order_expired_option  = false; //订单有效期选项
    $post_order_expired_is_over = false; //订单有效期是否已过期

    if ($post && isset($post->post_title)) {
        $post_type = $post->post_type;
        switch ($post_type) {
            case 'shop_product':
                if ($shop_s) {
                    $_img = zib_shop_get_order_thumb($post, 'radius8 fit-cover', 'medium');
                }
                break;
            case 'forum_posts': //论坛帖子
                $_img = zib_bbs_get_thumbnail($post, 'radius8 fit-cover');
                break;
            default:
                $_img = zib_post_thumbnail('medium', 'radius8 fit-cover', false, $post);
                break;
        }
        $_title                    = '<a href="' . get_the_permalink($post_id) . '" class="text-ellipsis mb6 font-bold">' . zibpay_get_order_title($order, 'font-bold') . '</a>';
        $post_order_expired_option = zibpay_get_post_order_expired_option($post_id);
    }

    $info_html = '';
    if (!empty($order_data['prices']['total_discount'])) {
        $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">优惠</div><div class="flex0">-' . $_mark . $order_data['prices']['total_discount'] . '</div></div>';
    }

    $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">' . $_total_price_type_name . '</div><div class="flex0">' . $_total_price . '</div></div>';
    $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">订单类型</div><div class="flex0">' . $order_type_name . '</div></div>';
    $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">订单编号</div><div class="ml20 overflow-hidden flex ac"><div class="text-ellipsis">' . $order['order_num'] . '</div><a href="javascript:;" class="flex flex0 ac copy-text icon-spot" data-clipboard-tag="订单号" data-clipboard-text="' . $order['order_num'] . '">复制</a></div></div>';
    $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">创建时间</div><div class="flex0">' . $order['create_time'] . '</div></div>';

    //已经支付
    if ($status == 1) {
        $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">支付方式</div><div class="flex0">' . zibpay_get_order_pay_detail_lists($order) . '</div></div>';
        $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">支付单号</div><div class="ml20 overflow-hidden flex ac"><div class="text-ellipsis">' . $order['pay_num'] . '</div><a href="javascript:;" class="flex flex0 ac copy-text icon-spot" data-clipboard-tag="支付单号" data-clipboard-text="' . $order['pay_num'] . '">复制</a></div></div>';
        $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">支付时间</div><div class="flex0">' . $order['pay_time'] . '</div></div>';

        //订单有效期
        if ($post_order_expired_option) {
            //先判断石头已经过期，没有过期显示到期时间，已经过期显示过期时间
            $post_order_expired_option_time = date('Y-m-d H:i:s', strtotime('+' . $post_order_expired_option['time'] . ' ' . $post_order_expired_option['unit'], strtotime($order['pay_time'])));

            if (strtotime($current_time) > strtotime($post_order_expired_option_time)) {
                $post_order_expired_is_over = true;
                $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">订单有效期</div><div class="flex0 c-yellow" data-toggle="tooltip" title="当前订单已过期，此付费内容需重新购买">' . $post_order_expired_option_time . '已过期</div></div>';
            } else {
                $info_html .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr6 muted-2-color">订单有效期</div><div class="flex0" data-toggle="tooltip" title="到期后此付费内容需重新购买">' . $post_order_expired_option_time . '</div></div>';
            }

        }

    }

    $info_html = '<div class="info-content">' . $info_html . '</div>';

    $footer_left = '';
    switch ($order_type) {

        /**
            $name = array(
            '1'  => '付费阅读', //文章，帖子
            '2'  => '付费资源', //文章
            '3'  => '产品购买', //页面，未使用
            '4'  => '购买会员', //用户
            '5'  => '付费图片', //文章
            '6'  => '付费视频', //文章
            '7'  => '自动售卡', //未启用
            '8'  => '余额充值', //用户
            '9'  => '购买积分', //用户
            '10' => '购买商品', //商城，商品
            );
             */

        case '1':
        case '5':
        case '6':
            if ($post) {
                $footer_left = '<a href="' . zib_get_user_home_url($post->post_author) . '" class="but">作者主页</a>';

                if ($status == 1 && !$post_order_expired_is_over) {
                    $footer_right .= '<a href="' . get_permalink($post) . '" class="but c-blue">去查看</a>';
                } elseif ($status == -1 || $post_order_expired_is_over) {
                    $footer_right .= '<a href="' . get_permalink($post) . '" class="but c-blue">重新购买</a>';
                }
            }
            break;
        case '11':
            if ($post) {
                if ($status == 1) {
                    $footer_right .= '<a href="' . get_permalink($post) . '" class="but c-blue">去查看</a>';
                }
            }
            break;

        case '2':
            if ($post) {
                $footer_left = '<a href="' . zib_get_user_home_url($post->post_author) . '" class="but">作者主页</a>';
                if ($status == 1 && !$post_order_expired_is_over) {
                    $footer_right .= '<a href="' . get_permalink($post) . '" class="but c-blue">去下载</a>';
                } elseif ($status == -1 || $post_order_expired_is_over) {
                    $footer_right .= '<a href="' . get_permalink($post) . '" class="but c-blue">重新购买</a>';
                }
            }
            break;

        case '4':
            $_img = '<img src="' . zibpay_get_vip_icon_img_url(1) . '" class="radius8 fit-cover vip-card">';
            if ($status != 0) {
                $footer_right .= '<a href="' . zib_get_user_center_url('vip') . '" class="but c-blue">我的会员</a>';
            }
            break;

        case '8':
            $_img = zib_get_svg('money-color-2', null, 'muted-box fit-cover c-blue-2');
            if ($status != 0) {
                $footer_right .= zibpay_get_balance_charge_link('but c-blue mr6', '再次充值');
                $footer_right .= '<a href="' . zib_get_user_center_url('balance') . '" class="but">我的余额</a>';
            }
            break;
        case '9':
            $_img = zib_get_svg('points-color', null, 'muted-box fit-cover c-yellow-2');
            if ($status != 0) {
                $footer_right .= zibpay_get_points_pay_link('but c-blue mr6', '再次购买');
                $footer_right .= '<a href="' . zib_get_user_center_url('balance') . '" class="but">我的积分</a>';
            }
            break;
    }

    $_title = $_title ? $_title : $order_type_name;
    $_img   = $_img ?: zib_get_svg('order-color', null, 'fit-cover muted-box');

    $content = '<div class="zib-widget">
        <div class="order-item user-order-item order-type-' . $order_type . '">
            <div class="order-content flex mb10">
                <div class="order-thumb mr10">' . $_img . '</div>
                <div class="flex1 flex jsb xx">
                    <div class="flex1 flex jsb">
                        <div class="flex1 mr10">
                            <div class="order-title text-ellipsis mb6">' . $_title . '</div>
                            ' . $_opt_name . '
                        </div>
                        <div class="flex xx ab">
                            <div class="unit-price">' . $_unit_price . '</div>
                            ' . ($_count ? '<div class="count mt6 muted-color">x' . $_count . '</div>' : '') . '
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ' . $info_html . '
    </div>';

    $footer_right = apply_filters('user_order_details_footer_right', $footer_right, $order);
    $footer_left  = apply_filters('user_order_details_footer_left', $footer_left, $order);

    $footer = $footer_left || $footer_right ? '<div class="flex ac jsb modal-full-footer"><div class="flex1">' . $footer_left . '</div><div class="flex0">' . $footer_right . '</div></div>' : '';
    $header = '<div class="touch text-center mb20">' . $header_title . '</div> <button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $html   = $header . '<div class="mini-scrollbar scroll-y max-vh7">' . $content . '</div>' . $footer;
    return $html;
}

//订单卡片
function zibpay_get_user_order_list_card_default($order)
{
    $order_type      = $order['order_type'];
    $order_type_name = zibpay_get_pay_type_name($order_type);
    $post_id         = $order['post_id'];
    $status          = (string) $order['status'];
    $post            = $post_id ? get_post($post_id) : null;

    //准备参数
    $_img                       = '';
    $_title                     = '';
    $_opt_name                  = $order_data['options_active_name'] ?? '';
    $_unit_price                = '';
    $_total_price               = '';
    $_total_price_type_name     = '';
    $_status_name               = '';
    $_shipping_desc             = '';
    $_btns                      = '';
    $order_data                 = zibpay::get_meta($order['id'], 'order_data');
    $_count                     = $order_data['count'] ?? 0;
    $_time                      = $status >= 1 ? $order['pay_time'] : $order['create_time'];
    $shop_s                     = _pz('shop_s');
    $post_order_expired_is_over = false;

    if (isset($order_data['prices']['unit_price'])) {
        $_unit_price = $order_data['prices']['unit_price']; //原价的单价
    } else {
        $_unit_price = $order['order_price'];
    }

    if (isset($order_data['prices']['pay_price'])) {
        $_total_price = $order_data['prices']['pay_price']; //总价的优惠价
    } else {
        $_total_price = $order['pay_price'] ?: $order['order_price'];

        if (!$_total_price && $order_type !== '10' && $_count <= 1) {
            $_unit_price  = zibpay_get_order_effective_amount($order);
            $_total_price = $_unit_price;
        }
    }

    $_total_price_type_name = $status == '1' ? '实付' : '应付';

    if ($post && !empty($post->post_title)) {
        $_img_url = zibpay_get_post_thumbnail_url($post);
        $alt      = $post->post_title . zib_get_delimiter_blog_name();
        $_img     = '<img class="radius8 fit-cover" src="' . $_img_url . '" alt="' . $alt . '">';
        $_title   = '<div class="text-ellipsis mb6 font-bold">' . $post->post_title . '</div>';

        if ($status == '1') {
            $post_order_expired_option = zibpay_get_post_order_expired_option($post_id);
            if ($post_order_expired_option) {
                $post_order_expired_option_time = date('Y-m-d H:i:s', strtotime('+' . $post_order_expired_option['time'] . ' ' . $post_order_expired_option['unit'], strtotime($order['pay_time'])));
                $current_time                   = current_time('Y-m-d H:i:s');
                if (strtotime($current_time) > strtotime($post_order_expired_option_time)) {
                    $post_order_expired_is_over = true;
                }
            }
        }
    } else {
        $_title = $order_data['product_title'] ?? '';
    }

    if ($status == '-1') {
        $_status_name = '<span class="c-red">交易已关闭</span>';
    }

    if ($status == '0') {
        $time_remaining = zibpay_get_order_pay_over_time($order);
        if ($time_remaining == 'over') {
            $order['status'] = '-1';
            $status          = '-1';
            $_status_name    = '<span class="c-red">交易已关闭</span>';
        } else {
            $time_remaining = zib_time_to_c($time_remaining);
            $_status_name   = '<span class="c-red">待支付 <span class="c-yellow px12 badg badg-sm" int-second="1" data-over-text="交易已关闭" data-countdown="' . $time_remaining . '"></span></span>';
        }
    }

    if ($status == '1') {
        $_status_name = '<span class="c-green">已支付</span>';
    }

    if ((int) $status == 0) {
        $_btns = zibpay_get_order_close_link($order, 'but mr6', '关闭订单');
        $_btns .= zibpay_get_order_pay_link($order, 'but c-red');
    }

    //积分还是现金订单
    $_opt_name   = $_opt_name ? '<div class="muted-color em09">' . $_opt_name . '</div>' : ($order_type == '10' ? '' : '<span class="pay-tag badg badg-sm mr6">' . $order_type_name . '</span>');
    $is_points   = $order['pay_type'] == 'points';
    $points_mark = zibpay_get_points_mark();
    $pay_mark    = zibpay_get_pay_mark();
    $_mark       = $is_points ? $points_mark : $pay_mark;
    $_mark       = '<span class="pay-mark px12">' . $_mark . '</span>';

    $_total_price = $_total_price && $_total_price !== $_unit_price ? $_mark . '<b>' . $_total_price . '</b>' : '';
    $_unit_price  = $_mark . '<b>' . $_unit_price . '</b>';

    $_img = $_img ?: zib_get_svg('order-color', null, 'fit-cover muted-box');
    switch ($order_type) {

        /**
            $name = array(
            '1'  => '付费阅读', //文章，帖子
            '2'  => '付费资源', //文章
            '3'  => '产品购买', //页面，未使用
            '4'  => '购买会员', //用户
            '5'  => '付费图片', //文章
            '6'  => '付费视频', //文章
            '7'  => '自动售卡', //未启用
            '8'  => '余额充值', //用户
            '9'  => '购买积分', //用户
            '10' => '购买商品', //商城，商品
            );
             */

        case '1':
        case '5':
        case '6':
            if ($post) {
                if ($status == 1 && !$post_order_expired_is_over) {
                    $_btns .= '<a href="' . get_permalink($post) . '" class="but c-blue">去查看</a>';
                } elseif ($status == -1 || $post_order_expired_is_over) {
                    $_btns .= '<a href="' . get_permalink($post) . '" class="but c-blue">重新购买</a>';
                }
            }
            break;
        case '11':
            if ($post) {
                if ($status == 1) {
                    $_btns .= '<a href="' . get_permalink($post) . '" class="but c-blue">去查看</a>';
                }
            }
            break;
        case '2':
            if ($post) {
                if ($status == 1 && !$post_order_expired_is_over) {
                    $_btns .= '<a href="' . get_permalink($post) . '" class="but c-blue">去下载</a>';
                } elseif ($status == -1 || $post_order_expired_is_over) {
                    $_btns .= '<a href="' . get_permalink($post) . '" class="but c-blue">重新购买</a>';
                }
            }

            break;

        case '4':
            $_img = '<img src="' . zibpay_get_vip_icon_img_url(1) . '" class="radius8 fit-cover vip-card">';
            $_btns .= '<a href="' . zib_get_user_center_url('vip') . '" class="but ml6">我的会员</a>';
            break;

        case '8':
            $_img = zib_get_svg('money-color-2', null, 'muted-box fit-cover c-blue-2');
            if ($status != 0) {
                $_btns .= zibpay_get_balance_charge_link('but c-blue mr6', '再次充值');
                $_btns .= '<a href="' . zib_get_user_center_url('balance') . '" class="but">我的余额</a>';
            }
            break;
        case '9':
            $_img = zib_get_svg('points-color', null, 'muted-box fit-cover c-yellow-2');
            if ($status != 0) {
                $_btns .= zibpay_get_points_pay_link('but c-blue mr6', '再次购买');
                $_btns .= '<a href="' . zib_get_user_center_url('balance') . '" class="but">我的积分</a>';
            }
            break;

        case '10':
            if ($shop_s) {

            }

            break;
    }

    $_author_html = '';
    if ($order['post_author']) {
        //商家头像加商家名称
        $author_avatar = zib_get_avatar_box($order['post_author'], 'avatar-mini mr10', false, true);
        $author_name   = get_the_author_meta('display_name', $order['post_author']);
        $_author_html  = '<div class="flex ac jsb mb10"><a class="order-item-author" target="_blank" href="' . zib_get_user_home_url($order['post_author']) . '"><div class="flex ac">' . $author_avatar . $author_name . '<i class="ml6 fa fa-angle-right em12"></i></div></a><div class="flex0">' . $_status_name . '</div></div>';
    }

    $_btns_box = '';
    $_btns     = apply_filters('user_order_card_btns', $_btns, $order);
    if ($_btns) {
        $_btns_box = '<div class="text-right mt10">' . $_btns . '</div>';
    }

    $_total_price_box = '';
    if ($_total_price) {
        $_total_price_box = '<div class="text-right"><span class="muted-2-color em09 mr3">' . $_total_price_type_name . '</span>' . $_total_price . '</div>';
    }

    $order_footer = '';
    if ($_shipping_desc || $_total_price_box || $_btns_box) {
        $order_footer = '<div class="order-footer mt10">' . $_shipping_desc . $_total_price_box . $_btns_box . '</div>';
    }

    $html = '
    <div class="zib-widget ajax-item mb10 order-item user-order-item order-type-' . $order_type . '">
        ' . $_author_html . '
        <div class="order-content flex show-order-modal pointer" data-order-id="' . $order['id'] . '">
            <div class="order-thumb mr10">' . $_img . '</div>
            <div class="flex1 flex jsb xx">
                <div class="flex1 flex jsb">
                    <div class="flex1 mr10">
                        <div class="order-title">' . $_title . '</div>
                        ' . $_opt_name . '
                        <div class="muted-color em09 mt6">' . $_time . '</div>
                    </div>
                    <div class="flex xx ab">
                        ' . ($_author_html ? '' : '<div class="mb10">' . $_status_name . '</div>') . '
                        <div class="unit-price">' . $_unit_price . '</div>
                        ' . ($_count > 1 ? '<div class="count mt6 muted-color">x' . $_count . '</div>' : '') . '
                    </div>
                </div>
            </div>
        </div>
        ' . $order_footer . '
    </div>';

    return $html;
}

function zib_get_order_details_page_url()
{

}

//获取订单关闭链接
function zibpay_get_order_close_link($order, $class = '', $text = '取消订单')
{
    $class = $class ? ' ' . $class : '';
    if ($order['status'] != '0') {
        return;
    }

    $order_id  = $order['id'];
    $order_num = $order['order_num'];

    $url_var = array(
        'action'    => 'close_order_modal',
        'order_num' => $order_num,
        'order_id'  => $order_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini full-sm',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => $url_var,
    );
    return zib_get_refresh_modal_link($args);
}

//获取订单支付链接
function zibpay_get_order_pay_link($order, $class = '', $text = '立即支付')
{
    $class = $class ? ' ' . $class : '';
    if (!$order || $order['status'] != '0' || !$order['payment_id']) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini full-sm',
        'height'        => 330,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => array(
            'action'     => 'order_pay_modal',
            'order_num'  => $order['order_num'],
            'order_id'   => $order['id'],
            'payment_id' => $order['payment_id'],
        ),
    );
    return zib_get_refresh_modal_link($args);
}

/**
 * 获取用户、商品、待支付的有效订单
 */
function zibpay_get_current_user_post_wait_payment($post_id, $order_type)
{
    if (!$post_id || !$order_type) {
        return false;
    }

    if (!in_array((int) $order_type, apply_filters('not_repeat_create_order_type', array(1, 2, 5, 6, 11)))) {
        return false;
    }

    $user_id = get_current_user_id();
    $where   = [
        'post_id'    => $post_id,
        'status'     => 0,
        'order_type' => $order_type,
    ];
    if ($user_id) {
        $where['user_id'] = $user_id;
    } elseif (_pz('pay_no_logged_in', true) && !empty($_COOKIE['zibpay_' . $post_id])) {
        $where['order_num'] = $_COOKIE['zibpay_' . $post_id];
    } else {
        return false;
    }

    $order_db = ZibDB::name('zibpay_order')->where($where)->field('payment_id')->order('id', 'DESC');

    //获取距离超时还有5分钟以上时间的订单
    $max_time       = zibpay_get_order_pay_max_time() - 3; //至少还有5分钟时间可支付
    $current_time   = current_time('Y-m-d H:i:s');
    $where_max_time = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 . ' Second', strtotime($current_time)));
    $order_db->where('create_time', '>', $where_max_time);
    $payment_db = $order_db->find()->toArray();

    if (empty($payment_db['payment_id'])) {
        return false;
    }

    $payment_data = zibpay::get_payment($payment_db['payment_id']);
    if (empty($payment_data)) {
        return false;
    }

    return $payment_data;
}

//挂钩:新建/支付/关闭，更新缓存数据
add_action('order_created', 'zibpay_user_wait_pay_order_count_cache_delete');
add_action('order_closed', 'zibpay_user_wait_pay_order_count_cache_delete');
add_action('payment_order_success', 'zibpay_user_wait_pay_order_count_cache_delete');
function zibpay_user_wait_pay_order_count_cache_delete($order)
{
    if (is_object($order)) {
        $order = (array) $order;
    } elseif (is_numeric($order)) {
        $order = zibpay::get_order($order, 'user_id');
    }

    $user_id = $order['user_id'] ?? 0;

    if (!$user_id) {
        return;
    }

    wp_cache_delete('user_orders_' . $user_id . '_wait-pay', 'user_order_data');
    wp_cache_delete('user_order_count_' . $user_id . '_wait-pay', 'user_order_data');
}

//获取用户待支付订单的数量
function zibpay_get_user_wait_pay_order_count($user_id)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    //缓存
    $cache_key = 'user_order_count_' . $user_id . '_wait-pay';
    $count     = wp_cache_get($cache_key, 'user_order_data');
    if (false !== $count) {
        return $count;
    }

    $query_args = [
        'user_id' => $user_id,
        'field'   => 'count',
        'status'  => 0,
    ];

    //获取距离超时间
    $max_time          = zibpay_get_order_pay_max_time();
    $current_timestamp = current_time('timestamp');
    $where_max_time    = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 . ' Second', $current_timestamp));

    $query_args['where'] = [
        ['create_time', '>', $where_max_time],
    ];

    $db_order = zibpay::order_query($query_args);

    //缓存
    wp_cache_set($cache_key, $db_order['total'] ?? 0, 'user_order_data');
    return $db_order['total'] ?? 0;
}

//获取用户待支付订单的明细
function zibpay_get_user_wait_pay_orders($user_id)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return [];
    }

    //缓存
    $cache_key = 'user_orders_' . $user_id . '_wait-pay';
    $cache     = wp_cache_get($cache_key, 'user_order_data');
    if (false !== $cache) {
        return $cache;
    }

    $where = [
        'user_id' => $user_id,
        'status'  => 0,
    ];

    $order_db = ZibDB::name('zibpay_order')->where($where)->order('id', 'DESC')->limit(5);

    //获取距离超时还有5分钟以上时间的订单
    $max_time          = zibpay_get_order_pay_max_time() - 3; //至少还有3分钟时间可支付
    $current_timestamp = current_time('timestamp');
    $where_max_time    = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 . ' Second', $current_timestamp));
    $order_db->where('create_time', '>', $where_max_time);
    $payment_db = $order_db->select()->toArray();

    //缓存
    wp_cache_set($cache_key, $payment_db, 'user_order_data');
    return $payment_db;
}

//获取cookie中的待支付订单
function zibpay_get_cookie_wait_pay_orders()
{

    //从cookie中获取待支付订单
    $wait_pay_ids = zibpay_get_cookie_wait_pay_ids();
    if (!$wait_pay_ids) {
        return [];
    }

    $where = [
        'id'     => $wait_pay_ids,
        'status' => 0,
    ];
    $order_db = ZibDB::name('zibpay_order')->where($where)->order('id', 'DESC')->limit(5);

    //获取距离超时还有5分钟以上时间的订单
    $max_time          = zibpay_get_order_pay_max_time() - 3; //至少还有3分钟时间可支付
    $current_timestamp = current_time('timestamp');
    $where_max_time    = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 . ' Second', $current_timestamp));
    $order_db->where('create_time', '>', $where_max_time);
    $payment_db = $order_db->select()->toArray();

    return $payment_db;
}

function zibpay_get_cookie_wait_pay_ids()
{
    $wait_pay_ids = !empty($_COOKIE['zibpay_wait_pay_ids']) ? $_COOKIE['zibpay_wait_pay_ids'] : '';
    if (!$wait_pay_ids) {
        return [];
    }

    //分隔符
    $wait_pay_ids = explode(',', $wait_pay_ids);

    if (!$wait_pay_ids || !is_array($wait_pay_ids)) {
        $wait_pay_ids = [];
    }
    return $wait_pay_ids;
}

//在页面显示待支付订单浮窗
function zibpay_user_wait_pay_float_window()
{

    $float_window = zibpay_get_user_wait_pay_float_window();

    if ($float_window) {
        echo $float_window;
    }
}
if (_pz('pay_wait_float_s', true)) {
    add_action('wp_footer', 'zibpay_user_wait_pay_float_window');
}

//获取用户待支付订单的浮窗
function zibpay_get_user_wait_pay_float_window()
{

    $user_id = get_current_user_id();
    if ($user_id) {
        $wait_pay_orders = zibpay_get_user_wait_pay_orders($user_id);
    } else {
        $wait_pay_orders = zibpay_get_cookie_wait_pay_orders();
    }

    if (!$wait_pay_orders) {
        return '';
    }

    $lists          = '';
    $time_remaining = '';
    $count          = 0;
    foreach ($wait_pay_orders as $order) {
        $time_remaining = zibpay_get_order_pay_over_time($order);
        if ($time_remaining == 'over') {
            continue;
        }

        $count++;
        $_order_thumb = zibpay_get_order_order_thumb($order, 'mr6');
        $_title       = zibpay_get_order_title($order, 'text-ellipsis mr6 em09');
        $is_points    = $order['pay_type'] === 'points';
        $pay_mark     = $is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark();
        $_price       = '<div class="flex shrink0 abl c-red"><span class="pay-mark px12">' . $pay_mark . '</span>' . zib_floatval_round($order['pay_price']) . '</div>';

        $time_remaining = zib_time_to_c($time_remaining);
        $countdown_html = '<span class="c-yellow px12 countdown-box" int-second="1" data-countdown="' . $time_remaining . '" data-over-text="交易已关闭"></span>';
        $lists .= '
            <div class="flex list-mt10 order-item" data-class="modal-mini full-sm" mobile-bottom="true" data-height="330" data-toggle="RefreshModal" data-action="order_pay_modal&payment_id=' . $order['payment_id'] . '">
                ' . $_order_thumb . '
                <div class="flex1">
                    <div class="flex ac jsb">' . $_title . $_price . '</div>
                    <div class="flex jsb ac mt6">' . $countdown_html . '<a class="ml10 but c-red px12 p2-10">立即支付</a></div>
                </div>
            </div>
            ';
    }

    $html = '';
    if ($lists) {
        $countdown_html = '<text class="countdown-box" int-second="1" data-countdown="' . $time_remaining . '" data-over-text="00:00" max-unit="minute"><span class="minute"></span>:<span class="second"></span></text>';
        $html           = '<div class="float-right round position-top float-right-wait-pay"><span style="--this-bg:rgba(255, 82, 128, 0.9);--this-color:#ffffff;" class="float-btn wait-pay-btn hover-show nowave" data-placement="left" href="javascript:;">
        <svg class="icon em12" aria-hidden="true"><use xlink:href="#icon-wallet"></use></svg>
        <text style="font-size: 10px;">待支付</text>
        ' . $countdown_html . '
        <div style="width:240px;" class="hover-show-con dropdown-menu">' . $lists . '</div></span></div>';
    }

    return $html;
}

//用户中心订单tab
function zibpay_user_page_tab_content_order()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $tabs = array(
        'all'      => array(
            'tab'   => 'all',
            'title' => '全部',
        ),
        'wait-pay' => array(
            'tab'   => 'wait-pay',
            'title' => '待支付',
            'count' => zibpay_get_user_wait_pay_order_count($user_id),
        ),
    );
    $tabs = apply_filters('user_page_order_tabs', $tabs);

    $loader        = '<div class="post_ajax_loader"> <div class="mt20 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p></div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div> </div>';
    $tab_ajax_href = zib_get_admin_ajax_url('user_pay_order');
    $id_prefix     = 'user-order-tab-';
    $user_url      = zib_get_user_center_url('order');
    $active_tab    = !empty($_REQUEST['tab']) ? $_REQUEST['tab'] : 'all';
    $content_html  = '';
    $nav_html      = '';
    foreach ($tabs as $tab) {
        $is_active = $active_tab == $tab['tab'];
        $id        = $id_prefix . $tab['tab'];
        if ($is_active) {
            $order_ias = array(
                'id'     => '',
                'class'  => 'user-pay-statistical mb20',
                'loader' => str_repeat('<div class="zib-widget"><p class="placeholder k1"></p><p class="placeholder t1"></p><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></div>', 3), // 加载动画
                'query'  => array('tab' => $tab['tab']), // add_query_arg
                'url'    => $tab_ajax_href, // url
            );
            $html = zib_get_ias_ajaxpager($order_ias);
        } else {
            $html = '<span class="post_ajax_trigger"><a no-scroll="1" ajax-href="' . add_query_arg('tab', $tab['tab'], $tab_ajax_href) . '" class="ajax_load ajax-next ajax-open"></a></span>';
            $html .= $loader;
        }

        $nav_html .= '<li class="' . ($is_active ? 'active' : '') . '"><a data-target="#' . $id . '" href="javascript:;" data-route="' . add_query_arg('tab', $tab['tab'], $user_url) . '" data-toggle="tab" data-ajax>' . $tab['title'] . (!empty($tab['count']) ? '<badge class="ml3 b-red">' . $tab['count'] . '</badge>' : '') . '</a></li>';
        $content_html .= '<div class="ajaxpager tab-pane fade' . ($is_active ? ' active in' : '') . '" id="' . $id . '">' . $html . '</div>';
    }

    $html = '<div class="user-order-tabs"><div class="index-tab rectangular relative mb10"><ul class="list-inline scroll-x mini-scrollbar">' . $nav_html . '</ul></div><div class="tab-content main-tab-content">' . $content_html . '</div></div>';

    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_order', 'zibpay_user_page_tab_content_order');
