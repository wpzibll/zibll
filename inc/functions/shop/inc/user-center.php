<?php
/*
* @Author : Qinver
* @Url : zibll.com
* @Date : 2025-03-06 19:47:03
 * @LastEditTime : 2026-03-14 23:17:53
* @Project : Zibll子比主题
* @Description : 更优雅的Wordpress主题
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
* @Email : 770349780@qq.com
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

function zib_shop_user_center_page_sidebar_order($con)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return $con;
    }

    $buttons = array(
        array(
            'name'  => '待支付',
            'icon'  => zib_get_svg('wallet'),
            'tab'   => 'wait-pay',
            'count' => zib_shop_get_user_order_count('wait-pay'),
        ),
        array(
            'name'  => '待发货',
            'icon'  => zib_get_svg('gift'),
            'tab'   => 'wait-shipped',
            'count' => zib_shop_get_user_order_count('wait-shipped'),
        ),
        array(
            'name'  => '待收货',
            'icon'  => zib_get_svg('transit'),
            'tab'   => 'wait-receive',
            'count' => zib_shop_get_user_order_count('wait-receive'),
        ),
        array(
            'name'  => '待评价',
            'icon'  => zib_get_svg('comment'),
            'tab'   => 'wait-evaluate',
            'count' => zib_shop_get_user_order_count('wait-evaluate'),
        ),
        array(
            'name'  => '售后',
            'icon'  => zib_get_svg('return'),
            'tab'   => 'after-sale',
            'count' => zib_shop_get_user_order_count('after-sale'),
        ),
    );

    $buttons_html = '';
    foreach ($buttons as $but) {
        $count = $but['count'] ? '<badge class="abs-tr">' . $but['count'] . '</badge>' : '';
        $buttons_html .= '<item class="shop-user-order-tab-btn pointer relative" second-tab="' . $but['tab'] . '" data-onclick="[data-target=\'#user-tab-order\'],[data-target=\'#user-order-tab-' . $but['tab'] . '\']"><div class="em16">' . $but['icon'] . '</div><div class="px12 muted-color mt3">' . $but['name'] . '</div>' . $count . '</item>';
    }

    $con .= '<div class="zib-widget padding-6 mb10-sm"><div class="padding-6 flex ac jsb"><div class="ml3">我的订单</div><div><a class="muted-2-color em09" href="javascript:;" data-onclick="[data-target=\'#user-tab-order\'],[data-target=\'#user-order-tab-all\']">全部<i class="ml6 fa fa-angle-right em12"></i></a></div></div><div class="flex ac jsb text-center padding-6 padding-w10 user-order-tab-btn-box">' . $buttons_html . '</div></div>';
    return $con;
}
add_filter('user_center_page_sidebar', 'zib_shop_user_center_page_sidebar_order');

function zib_shop_user_ctnter_main_tabs_array_filter_order($tabs_array)
{
    $loader = '<div class="zib-widget"><div class="mt10"><div class="placeholder k1 mb10"></div><div class="placeholder k1 mb10"></div><div class="placeholder s1"></div></div><p class="placeholder k1 mb30"></p><div class="placeholder t1 mb30"></div><p class="placeholder k1 mb30"></p><p style="height: 120px;" class="placeholder t1"></p></div>';

    $tabs_array['order'] = array(
        'title'         => '我的订单',
        'nav_attr'      => 'drawer-title="我的订单"',
        'content_class' => 'author-user-con',
        'loader'        => $loader,
    );

    remove_filter('zib_user_center_page_sidebar_button_1_args', 'zibpay_user_center_page_sidebar_button_1_args_order');
    return $tabs_array;
}
add_filter('user_ctnter_main_tabs_array', 'zib_shop_user_ctnter_main_tabs_array_filter_order', 20);

function zib_shop_user_center_page_sidebar_button_1_args_cart($buttons)
{
    $cart_buttons = array(array(
        'name' => '',
        'icon' => '',
        'tab'  => '',
        'html' => '<item class="icon-but-cart"><a href="' . zib_shop_get_cart_url() . '"><div class="em16">' . zib_get_svg('cart-color') . '</div><div class="px12 muted-color mt3">购物车</div></a></item>',
    ));
    return array_merge($cart_buttons, $buttons);
}
add_filter('zib_user_center_page_sidebar_button_1_args', 'zib_shop_user_center_page_sidebar_button_1_args_cart');

function zib_shop_user_order_tabs($tabs)
{

    $tabs['wait-shipped'] = array(
        'tab'   => 'wait-shipped',
        'title' => '待发货',
        'count' => zib_shop_get_user_order_count('wait-shipped'),
    );
    $tabs['wait-receive'] = array(
        'tab'   => 'wait-receive',
        'title' => '待收货',
        'count' => zib_shop_get_user_order_count('wait-receive'),
    );
    $tabs['wait-evaluate'] = array(
        'tab'   => 'wait-evaluate',
        'title' => '待评价',
        'count' => zib_shop_get_user_order_count('wait-evaluate'),
    );
    $tabs['after-sale'] = array(
        'tab'   => 'after-sale',
        'title' => '售后/退款',
        'count' => zib_shop_get_user_order_count('after-sale'),
    );

    return $tabs;
}
add_filter('user_page_order_tabs', 'zib_shop_user_order_tabs');

//订单卡片
function zib_shop_user_order_list_card($html, $order_type, $order)
{
    if ($order_type != zib_shop_get_order_type()) {
        return $html;
    }

    $post_id    = $order['post_id'];
    $status     = $order['status'];
    $post       = $post_id ? get_post($post_id) : null;
    $order_data = zibpay::get_meta($order['id'], 'order_data');
    $_count     = $order_data['count'] ?? 0;
    $_time      = $status >= 1 ? $order['pay_time'] : $order['create_time'];
    $_opt_name  = $order_data['options_active_name'];

    if ($post) {
        $_img   = zib_shop_get_order_thumb($order, 'radius8 fit-cover', 'medium');
        $_title = '<span class="font-bold">' . $post->post_title . '</span>';
    } else {
        $_img   = zib_get_svg('order-color', null, 'fit-cover muted-box');
        $_title = '<span class="font-bold">' . ($order_data['product_title'] ?? '商品已下架') . '</span>';
    }

    $is_points            = $order_data['pay_modo'] === 'points';
    $_mark                = '<span class="pay-mark px12">' . ($is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark()) . '</span>';
    $_unit_price          = $_mark . '<b>' . floatval($order_data['prices']['unit_price'] ?? 0) . '</b>';
    $_total_price         = $_mark . '<b>' . floatval($order_data['prices']['pay_price'] ?? 0) . '</b>';
    $_refund_price        = $order_data['prices']['refund'] ?? 0; //退款金额
    $_refund_price_box    = $_refund_price ? '<span class="muted-2-color em09 ml10">退款<b class="muted-color ml3">' . $_mark . zib_floatval_round($_refund_price) . '</b></span>' : '';
    $_pay_price_type_name = $status == '1' || $status == -2 ? '实付' : '应付';
    $_pay_price_type_name = '<span class="muted-2-color em09 mr3">' . $_pay_price_type_name . '</span>';
    $_total_price_box     = '<div class="text-right mt10">' . $_pay_price_type_name . $_total_price . $_refund_price_box . '</div>';

    $_after_sale    = '';
    $footer_left    = '';
    $_express       = '';
    $time_remaining = zibpay_get_order_pay_over_time($order);
    if ($time_remaining == 'over') {
        $status = -1;
    }

    if ($status == '-1') {
        $_status_name = '<span class="c-red">交易已关闭</span>';
    }

    if ($status == '0') {
        $time_remaining = zib_time_to_c($time_remaining);
        $_status_name   = '<span class="c-red">待支付 <span class="c-yellow px12 badg badg-sm" int-second="1" data-over-text="交易已关闭" data-countdown="' . $time_remaining . '"></span></span>';
    }

    if ($status == -2) {
        $after_sale_type       = $order_data['after_sale_data']['type'] ?? '';
        $after_sale_type_names = [
            'refund'        => '已退款',
            'refund_return' => '已退货退款',
        ];
        $_status_name = '<span class="c-red">' . $after_sale_type_names[$after_sale_type] . '</span>';
    }

    if ($status == '1') {
        $shipping_status = zib_shop_get_order_shipping_status($order['id']);
        if ($shipping_status == '0') {
            $_status_name = '<span class="c-yellow">待发货</span>';
        } elseif ($shipping_status == '1') {
            $_status_name  = '<span class="c-green">待收货</span>';
            $express_data  = $order_data['express_data'] ?? [];
            $shipping_data = $order_data['shipping_data'] ?? [];

            if (isset($express_data['state'])) {
                $_express = '<div class="text-ellipsis muted-color"><i class="fa fa-truck mr6 fa-fw"></i><b class="mr6' . ($express_data['state'] == '已签收' ? ' c-blue' : '') . '">' . $express_data['state'] . '</b><span>' . ($express_data['traces'][0]['context'] ?? '') . '</span></div>';
            } elseif (isset($shipping_data['delivery_time'])) {

                $delivery_type_name = $shipping_data['delivery_type'] == 'no_express' ? '<span class="ml6">商家选择无需物流发货</span>' : '';
                $delivery_remark    = $shipping_data['delivery_remark'] ? '<span class="ml10">' . $shipping_data['delivery_remark'] . '</span>' : '';

                $_express = '<div class="text-ellipsis muted-color"><i class="fa fa-truck mr6 fa-fw"></i><b class="mr6">已发货</b>' . $delivery_type_name . '<span class="ml10 muted-2-color">' . $shipping_data['delivery_time'] . '</span>' . $delivery_remark . '</div>';
            }

            if ($shipping_data['delivery_type'] === 'express') {
                $_express = zib_shop_get_order_express_link($order, 'padding-10 muted-box flex jsb ac mt10', '<div class="overflow-hidden">' . $_express . '</div><div class="flex0 ml10 muted-2-color"><i class="fa fa-angle-right em12"></i></div>');
            } else {
                $_express = '<div class="padding-10 muted-box flex ac mt10">' . $_express . '</div>';
            }

        } elseif ($shipping_status == '2') {
            $_status_name = '<span class="c-blue">交易完成</span>';
        }
    }

    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if (in_array($after_sale_status, [1, 2])) {
        $after_sale_data          = $order_data['after_sale_data'] ?? [];
        $type                     = $after_sale_data['type'] ?? '';
        $type_name                = '<span class="c-yellow ml10">' . zib_shop_get_after_sale_type_name($type) . '</span>';
        $return_express_over_time = zib_shop_get_order_after_sale_return_express_over_time($order, $after_sale_data);
        if ($return_express_over_time === 'over') {
            $after_sale_status = 4;
        }
        if ($after_sale_status !== 4) {
            $_status_name = '<span class="c-yellow">售后中</span>';
            if ($after_sale_status === 1) {
                $after_sale_status_html = '<span class="ml10 muted-color">等待商家处理</span><span class="ml10 muted-2-color">' . $after_sale_data['user_apply_time'] . '</span>';
            } elseif ($after_sale_status === 2) {
                $progress = zib_shop_get_order_after_sale_progress($after_sale_data);
                if ($progress === 1) {
                    if ($return_express_over_time) {
                        $time_remaining = zib_time_to_c($return_express_over_time);

                        $time_countdown = '<span class="ml10 em09 muted-2-color">剩余发货时间：<span class="c-yellow badg badg-sm" int-second="1" data-over-text="1秒" data-countdown="' . $time_remaining . '"></span></span>';
                    } else {
                        $time_countdown = '';
                    }

                    $after_sale_status_html = '<span class="ml10 muted-color">等待您发货</span>' . $time_countdown;
                } elseif ($progress === 2) {
                    $after_sale_status_html = '<span class="ml10 muted-color">您已发货，等待商家处理</span><span class="ml10 muted-2-color">' . $after_sale_data['user_return_time'] . '</span>';
                } elseif ($progress === 3) {
                    $after_sale_status_html = '<span class="ml10 muted-color">商家已发货，等待您收货</span><span class="ml10 muted-2-color">' . $after_sale_data['user_apply_time'] . '</span>';
                }
            }

            $_after_sale = '<div class="text-ellipsis muted-color"><i class="fa-fw fa fa-heart-o"></i><b class="ml6">售后中</b>' . $type_name . $after_sale_status_html . '</div>';
            $_after_sale = zib_shop_get_order_after_sale_link($order, 'padding-10 muted-box flex jsb ac mt10', '<div class="overflow-hidden">' . $_after_sale . '</div><div class="flex0 ml10 muted-2-color"><i class="fa fa-angle-right em12"></i></div>');
        }
    }

    $is_show_author = _pz('shop_author_show') && $order['post_author'];
    $_author_html   = '';
    if ($is_show_author) {
        //商家头像加商家名称
        $author_avatar = zib_get_avatar_box($order['post_author'], 'avatar-mini mr10', false, true);
        $author_name   = get_the_author_meta('display_name', $order['post_author']);
        $_author_html  = '<div class="flex ac jsb mb10"><a class="order-item-author" target="_blank" href="' . zib_shop_get_author_url($order['post_author']) . '"><div class="flex ac">' . $author_avatar . $author_name . '<i class="ml6 fa fa-angle-right em12"></i></div></a><div class="flex0">' . $_status_name . '</div></div>';
    }

    $footer_right = '<div class="">' . implode('', zib_shop_get_user_order_btns($order)) . '</div>';
    $order_footer = $_express;
    $order_footer .= $_after_sale;
    $order_footer .= $_total_price_box;
    $order_footer .= $footer_right || $footer_left ? '<div class="order-footer flex ac jsb hh"><div class="flex0 mr10 mt10">' . $footer_left . '</div><div class="flex0 mt10">' . $footer_right . '</div></div>' : '';

    $html = '
    <div class="zib-widget ajax-item mb10 order-item user-order-item order-type-' . $order_type . '">
        ' . $_author_html . '
        <div class="order-content flex show-order-modal pointer" data-order-id="' . $order['id'] . '">
            <div class="order-thumb mr10">' . $_img . '</div>
            <div class="flex1 flex jsb xx">
                <div class="flex1 flex jsb">
                    <div class="flex1 mr10">
                        <div class="order-title text-ellipsis mb6">' . $_title . '</div>
                        ' . $_opt_name . '
                        <div class="muted-color em09 mt6">' . $_time . '</div>
                    </div>
                    <div class="flex xx ab">
                        ' . ($is_show_author ? '' : '<div class="mb10">' . $_status_name . '</div>') . '
                        <div class="unit-price">' . $_unit_price . '</div>
                        ' . ($_count ? '<div class="count mt6 muted-color">x' . $_count . '</div>' : '') . '
                    </div>
                </div>
            </div>
        </div>
        ' . $order_footer . '
    </div>';

    return $html;
}
add_filter('user_order_list_card', 'zib_shop_user_order_list_card', 10, 3);

function zib_shop_user_order_details_modal($html, $order_type, $order)
{
    if ($order_type != zib_shop_get_order_type()) {
        return $html;
    }

    $header_title  = zib_shop_user_order_details_header_title($order);
    $consignee_box = zib_shop_user_order_details_consignee_box($order);
    $product_info  = zib_shop_user_order_details_info($order);
    $content       = $consignee_box . $product_info;
    $footer        = zib_shop_user_order_details_footer($order);

    $header = '<div class="touch text-center mb20">' . $header_title . '</div> <button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $html   = $header . '<div class="mini-scrollbar scroll-y max-vh7">' . $content . '</div>' . $footer;
    return $html;
}
add_filter('user_order_details_modal', 'zib_shop_user_order_details_modal', 10, 3);

function zib_shop_user_order_details_footer($order)
{

    $btns_html      = '<div class="">' . implode('', zib_shop_get_user_order_btns($order)) . '</div>';
    $author_contact = zib_shop_get_author_contact_link($order['post_author'], 'but', zib_get_svg('manual-service') . '<span class="muted-2-color ">客服</span>');
    $footer         = '<div class="flex ac jsb modal-full-footer">' . $author_contact . $btns_html . '</div>';
    return $footer;
}

function zib_shop_get_user_order_btns($order)
{
    $status = $order['status'];
    $btns   = [];

    // 根据订单状态添加按钮
    if ($status == 0) {
        $btns[] = zibpay_get_order_close_link($order, 'but ml6', '关闭订单');
        $btns[] = zibpay_get_order_pay_link($order, 'but c-red ml6', '立即支付');
    } elseif ($status == 1) {
        $delivery_type = zibpay::get_meta($order['id'], 'order_data.shipping_data.delivery_type');
        // 一次性获取所有按钮，避免多次函数调用
        $btns[] = zib_shop_get_order_after_sale_link($order, 'but ml6');
        if ($delivery_type === 'express') {
            $btns[] = zib_shop_get_order_express_link($order, 'but ml6', '查看物流');
        }
        $btns[] = zib_shop_get_order_comment_link($order, 'but ml6 c-green', '评价');
        $btns[] = zib_shop_get_order_receive_confirm_link($order, 'but ml6 c-blue', '确认收货');
    } elseif ($status == -2) {
        $btns[] = zib_shop_get_order_after_sale_record_link($order, 'but ml6 c-red', '售后详情');
    }

    // 清理空数组，只执行一次
    $btns      = array_filter($btns);
    $btn_count = count($btns);

    // 如果按钮数量不足，添加"再次购买"按钮
    if ($btn_count < 3) {
        $product_link = zib_shop_get_product_link($order['post_id'], 'but ml6 c-blue-2', '再次购买');
        if ($product_link) {
            array_unshift($btns, $product_link);
            $btn_count++;
        }
    }

    // 如果按钮数量仍不足且不是待支付状态，添加"加入购物车"按钮
    if ($btn_count < 3 && $status != 0) {
        $cart_link = zib_shop_get_order_add_cart_link($order, 'but ml6', '加入购物车');
        if ($cart_link) {
            array_unshift($btns, $cart_link);
        }
    }

    return $btns;
}

//订单详情
function zib_shop_user_order_details_info($order)
{
    $product_box    = zib_shop_user_order_product_box($order);
    $author_box     = '';
    $is_show_author = _pz('shop_author_show') && $order['post_author'];
    if ($is_show_author) {
        //商家头像加商家名称
        $author_avatar = zib_get_avatar_box($order['post_author'], 'avatar-mini mr10', false, true);
        $author_name   = get_the_author_meta('display_name', $order['post_author']);
        $author_box    = '<div class="flex ac jsb mb10"><a class="order-item-author" target="_blank" href="' . zib_shop_get_author_url($order['post_author']) . '"><div class="flex ac">' . $author_avatar . $author_name . '<i class="ml6 fa fa-angle-right em12"></i></div></a><div class="flex0"></div></div>';
    }

    $info_lists           = '';
    $order_data           = zibpay::get_meta($order['id'], 'order_data');
    $status               = $order['status'];
    $_pay_price_type_name = $status == 1 || $status == -2 ? '实付款' : '应付款';
    $is_points            = $order_data['pay_modo'] === 'points';
    $_mark                = '<span class="pay-mark px12">' . ($is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark()) . '</span>';
    $total_price_info     = '<span>' . $_mark . '<span>' . floatval($order_data['prices']['total_price']) . '</span></span>';
    $pay_price_info       = '<span class="c-red">' . $_mark . '<b>' . floatval($order_data['prices']['pay_price'] ?? '') . '</b></span>';
    $shipping_fee         = '<span class="pay-mark px12">' . (zibpay_get_pay_mark()) . '</span>' . ($order_data['prices']['shipping_fee'] ?? 0);
    $shipping_type        = $order_data['shipping_type'] ?? '';
    $total_discount       = $order_data['prices']['total_discount'] ?? 0;
    $refund_price         = $order_data['prices']['refund'] ?? 0; //退款金额
    $discount_lists       = '';
    if ($total_discount) {
        $discount_lists = '<div class="flex0 mr10 muted-2-color">优惠金额</div><div class="flex0">- <span>' . $_mark . zib_floatval_round($total_discount) . '</span>' . '<i class="fa fa-angle-right em12 ml6"></i></div>';
        $discount_lists = zib_shop_get_order_discount_link($order, 'flex ac jsb padding-h6', $discount_lists);
    }

    $gift_lists = '';
    if (!empty($order_data['gift_data'])) {
        foreach ($order_data['gift_data'] as $gift) {
            $gift_lists .= zib_shop_get_gift_type_name($gift) . '、';
        }
        $gift_lists = rtrim($gift_lists, '、');

        $gift_lists = '<div class="flex0 mr10 muted-2-color">赠品</div><div class="ml20 overflow-hidden flex jab ac"><div class="text-ellipsis">' . $gift_lists . '<i class="fa fa-angle-right em12 ml6"></i></div></div>';
        $gift_lists = zib_shop_get_order_gift_link($order, 'flex ac jsb padding-h6 overflow-hidden', $gift_lists);
    }

    $order_remark = $order_data['remark'] ?? ''; //订单备注
    $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">商品总价</div><div class="flex0">' . $total_price_info . '</div></div>';
    $info_lists .= $discount_lists; //优惠金额+优惠明细
    $info_lists .= $gift_lists; //赠品
    $info_lists .= $shipping_type === 'express' ? '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">运费</div><div class="flex0">' . $shipping_fee . '</div></div>' : ''; //运费
    $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">' . $_pay_price_type_name . '</div>' . $pay_price_info . '</div>';
    //退款金额
    if ($refund_price > 0) {
        $refund_lists = '<div class="flex0 mr10 muted-2-color">售后退款</div><div class="flex0 c-red">- <span>' . $_mark . zib_floatval_round($refund_price) . '</span>' . '<i class="fa fa-angle-right em12 ml6"></i></div>';
        $info_lists .= zib_shop_get_order_after_sale_record_link($order, 'flex ac jsb padding-h6', $refund_lists);
    } else {
        //有售后记录
        $after_sale_record = $order_data['after_sale_record'] ?? [];
        if ($after_sale_record) {
            $after_sale_record_count      = count($after_sale_record);
            $_after_sale_record_link_text = '<div class="flex0 mr10 muted-2-color">售后记录</div><div class="flex0"><span>已有' . $after_sale_record_count . '条记录<i class="fa fa-angle-right em12 ml6"></i></span></div>';
            $info_lists .= zib_shop_get_order_after_sale_record_link($order, 'flex ac jsb padding-h6', $_after_sale_record_link_text);
        }
    }

    $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">订单编号</div><div class="flex0"><span>' . $order['order_num'] . '</span><a href="javascript:;" class="copy-text icon-spot" data-clipboard-tag="订单号" data-clipboard-text="' . $order['order_num'] . '">复制</a></div></div>';
    $info_lists .= $order_remark ? '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">订单备注</div><div class="text-right ml20">' . esc_attr($order_remark) . '</div></div>' : ''; //订单备注
    $user_required = $order_data['user_required'] ?? [];
    if ($user_required) {
        foreach ($user_required as $user_required_item) {
            $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">' . $user_required_item['name'] . '</div><div class="flex0">' . $user_required_item['value'] . '</div></div>';
        }
    }

    $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">创建时间</div><div class="flex0">' . $order['create_time'] . '</div></div>';
    //已经支付
    if ($status == 1) {
        $info_lists .= (float) $order_data['prices']['pay_price'] > 0 ? '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">支付方式</div><div class="flex0">' . zibpay_get_order_pay_detail_lists($order) . '</div></div>' : '';
        $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">支付单号</div><div class="ml20 overflow-hidden flex ac flex"><div class="text-ellipsis">' . $order['pay_num'] . '</div><a href="javascript:;" class="flex flex0 ac copy-text icon-spot" data-clipboard-tag="支付单号" data-clipboard-text="' . $order['pay_num'] . '">复制</a></div></div>';
        $info_lists .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">支付时间</div><div class="flex0">' . $order['pay_time'] . '</div></div>';
        $shipping_status = zib_shop_get_order_shipping_status($order['id']);
        if ($shipping_status > 0) {
            //已发货
            $delivery_time = $order_data['shipping_data']['delivery_time'] ?? ''; //发货时间
            $receive_time  = $order_data['shipping_data']['receive_time'] ?? ''; //收货时间
            //发货备注
            $delivery_remark = $order_data['shipping_data']['delivery_remark'] ?? '';
            //收货备注
            $receive_remark = $order_data['shipping_data']['receive_remark'] ?? '';

            $info_lists .= $delivery_time ? '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">发货时间</div><div class="flex0">' . $delivery_time . '</div></div>' : '';
            $info_lists .= $delivery_remark ? '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">发货备注</div><div class="text-right ml20">' . esc_attr($delivery_remark) . '</div></div>' : '';
            $info_lists .= $receive_time ? '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">收货时间</div><div class="flex0">' . $receive_time . '</div></div>' : '';
            $info_lists .= $receive_remark ? '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">收货备注</div><div class="text-right ml20">' . esc_attr($receive_remark) . '</div></div>' : '';
        }
    }

    $info_lists = '<div class="">' . $info_lists . '</div>';

    return '<div class="zib-widget">' . $author_box . $product_box . $info_lists . '</div>';
}

//发货信息
function zib_shop_user_order_details_consignee_box($order)
{
    $order_data      = zibpay::get_meta($order['id'], 'order_data');
    $status          = $order['status'];
    $shipping_type   = $order_data['shipping_type'] ?? '';
    $shipping_status = zib_shop_get_order_shipping_status($order['id']);
    $consignee       = $order_data['consignee'] ?? [];
    $delivery_type   = $order_data['shipping_data']['delivery_type'] ?? '';

    $express_html = '';
    $address_html = '';

    if ($shipping_type === 'auto') {
        $delivery_type_name = zib_shop_get_delivery_type_name($delivery_type) ?: '虚拟商品';
        $emali              = $consignee['email'] ?? '';

        $express_html = '<div class="timeline-box ' . ($shipping_status > 0 ? '' : ' zib-widget mb10') . '">
            <div class="timeline-content">
                <div class="timeline-time"><span class="mr6">自动发货</span><span class="badg badg-sm">' . $delivery_type_name . '</span></div>
                ' . ($shipping_status == 0 && $status == 1 ? '<div class="c-yellow em09">自动发货失败，等待商家处理</div>' : '') . '
            </div>
            <div class="timeline-content"><div><span class="muted-2-color"><i class="fa fa-envelope-o mr6"></i>' . $emali . '</span></div></div>
        </div>';

        if ($shipping_status > 0) {
            //已发货
            $express_html = zib_shop_get_order_delivery_content_link($order, 'zib-widget flex jsb mb10', $express_html . '<div class="flex0 ml6 muted-2-color"><i class="fa fa-angle-right em12"></i></div>');
        }

        return $express_html;
    } else {
        //不为手动发货和自动发货，则显示收货地址
        if ($shipping_type !== 'manual') {
            $address      = $consignee['address_data'] ?? '';
            $address_html = '';
            if ($address) {
                $modify_address_button = zib_shop_get_order_modify_address_link($order, 'flex0 ml20 em09 muted-color', '修改<i class="fa fa-angle-right em12 ml6"></i>'); //修改地址的按钮
                $address_html          = '<div class="flex muted-color mt10 at">
                <div class="icon-header mr10"><i class="fa-fw fa fa-map-marker"></i></div>
                    <div class="flex1">
                        <div class=""><b>' . $address['city'] . $address['address'] . '</b></div>
                        <div class="em09 mt3"><span class="mr6">' . $address['name'] . '</span><span class="muted-2-color">' . $address['phone'] . '</span></div>
                    </div>
                    ' . $modify_address_button . '
                </div>';
            }
        }

        if ($status == 1) {
            if ($shipping_status > 0) {
                //已发货，或者已收货

                //如果商家选择无需物流发货
                if ($delivery_type === 'no_express') {
                    //发货备注
                    $delivery_remark      = $order_data['shipping_data']['delivery_remark'] ?? '';
                    $delivery_remark_html = $delivery_remark ? '<div class="em09 muted-2-color mt3">' . $delivery_remark . '</div>' : '<div class="em09 mt3 muted-2-color">商家选择无需物流发货</div>';

                    $express_html = '<div class="flex muted-color mb10">
                    <div class="icon-header mr10"><i class="fa-fw fa fa-truck"></i></div>
                    <div class="">
                        <div><b class="font-bold mr6">已发货</b>' . ($delivery_remark ? '<span class="em09 muted-color">商家选择无需物流发货</span>' : '') . '</div>
                        ' . $delivery_remark_html . '
                    </div>
                </div>';
                } elseif ($delivery_type === 'express') {
                    $express_data = $order_data['express_data'] ?? [];
                    if (isset($express_data['state'])) {
                        $context  = !empty($express_data['traces'][0]['context']) ? '<div class="text-ellipsis em09 muted-2-color mt3">' . $express_data['traces'][0]['context'] . '</div>' : '';
                        $time     = !empty($express_data['traces'][0]['time']) ? '<div class="em09 opacity8">' . $express_data['traces'][0]['time'] . '</div>' : '';
                        $_express = '<div class="flex muted-color mb10">
                    <div class="icon-header mr10"><i class="fa fa-truck fa-fw"></i></div>
                    <div class="overflow-hidden">
                        <div class="flex ac ' . ($express_data['state'] == '已签收' ? ' c-blue' : ' focus-color') . '"><b class="mr6">' . $express_data['state'] . '</b>' . ($time) . '</div>
                        ' . $context . '
                    </div>
                </div>';
                    } else {
                        $shipping_data = $order_data['shipping_data'] ?? [];
                        $context       = '<div class="text-ellipsis em09 muted-2-color mt3">' . $shipping_data['express_company_name'] . '：' . $shipping_data['express_number'] . '</div>';

                        $_express = '<div class="flex muted-color mb10">
                    <div class="icon-header mr10"><i class="fa fa-truck fa-fw"></i></div>
                    <div class="">
                        <div><b class="font-bold">已发货</b></div>
                        ' . $context . '
                    </div>
                </div>';
                    }

                    $express_html = zib_shop_get_order_express_link($order, 'flex jsb mb10', '<div class="overflow-hidden">' . $_express . '</div><div class="flex0 ml20 muted-2-color"><i class="fa fa-angle-right em12"></i></div>');
                } elseif ($shipping_status <= 1) {
                    $delivery_remark = $order_data['shipping_data']['delivery_remark'] ?? '';
                    $delivery_remark = $delivery_remark ? '<div class="em09 muted-2-color mt3">发货备注：' . $delivery_remark . '</div>' : '';
                    $delivery_time   = $order_data['shipping_data']['delivery_time'] ?? '';

                    $express_html = '<div class="flex muted-color">
                    <div class="icon-header mr10"><i class="fa-fw fa fa-truck"></i></div>
                    <div class="">
                        <div><b class="focus-color mr10">已发货</b><span class="em09 opacity8">' . $delivery_time . '</span></div>
                        ' . $delivery_remark . '
                    </div>
                </div>';
                }
            } else {
                $express_html = '<div class="flex muted-color">
                    <div class="icon-header mr10"><i class="fa-fw fa fa-truck"></i></div>
                    <div class="focus-color font-bold">等待商家发货</div>
                </div>';
            }
        }
    }

    $html = ($express_html || $address_html) ? '<div class="zib-widget mb10">' . $express_html . $address_html . '</div>' : '';
    return $html;
}

function zib_shop_user_order_details_header_title($order)
{
    $status         = $order['status'];
    $status_text    = '';
    $time_countdown = '';
    if ($status == 0) {
        $time_remaining = zibpay_get_order_pay_over_time($order);
        if ($time_remaining == 'over') {
            $status == -1;
        } else {
            $time_remaining = zib_time_to_c($time_remaining);
            $time_countdown = '<div class="mt6 em09 muted-2-color">剩余<span class="c-yellow badg badg-sm" int-second="1" data-over-text="1秒" data-countdown="' . $time_remaining . '"></span>自动关闭订单</div>';
        }
    }

    $order_data = zibpay::get_meta($order['id'], 'order_data');
    if ($status == -1) {
        $status_text = '<div class="font-bold">交易关闭</div>';
        $status_text .= !empty($order_data['close_reason']) ? '<div class="mt6 px12 muted-2-color">' . $order_data['close_reason'] . '</div>' : '';
    } elseif ($status == 0) {
        $status_text = '<div class="c-yellow mb6 font-bold">' . zib_get_svg('time') . ' 待支付</div>' . $time_countdown;
    } elseif ($status == 1) {
        //发货状态
        $shipping_status = zib_shop_get_order_shipping_status($order['id']);
        if ($shipping_status == 0) {
            $status_text = '<div class="font-bold">下单成功</div><span class="mt6 px12 muted-2-color font-normal">等待商家发货</span>';
        } elseif ($shipping_status == 1) {
            $express_state_text = '';
            $express_state      = zib_shop_get_express_state($order);
            if ($express_state) {
                $express_state_text = '<span class="ml6 px12 muted-2-color font-normal">' . $express_state . '</span>';
            }

            $status_text       = '<div class="font-bold">' . zib_get_svg('time') . ' 待收货' . $express_state_text . '</div>';
            $receipt_over_time = zib_shop_get_order_receipt_over_time($order['id']);

            if ($receipt_over_time && $receipt_over_time != 'over') {
                $receipt_over_time_time_remaining = zib_time_to_c($receipt_over_time);
                $status_text .= '<div class="mt6 em09 muted-2-color">剩余<span class="c-yellow badg badg-sm" int-second="1" data-over-text="1秒" data-countdown="' . $receipt_over_time_time_remaining . '"></span>自动确认收货</div>';
            }
        } else {
            $status_text = '<b class="inflex ac"><i class="fa fa-check-circle-o mr6 em12"></i>交易完成</b>';

            $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
            if (in_array($after_sale_status, [1, 2])) {
                $after_sale_data          = $order_data['after_sale_data'] ?? [];
                $type                     = $after_sale_data['type'] ?? '';
                $type_name                = '<span class="c-yellow ml10">' . zib_shop_get_after_sale_type_name($type) . '</span>';
                $return_express_over_time = zib_shop_get_order_after_sale_return_express_over_time($order, $after_sale_data);
                if ($return_express_over_time === 'over') {
                    $after_sale_status = 4;
                }
                if ($after_sale_status !== 4) {
                    if ($after_sale_status === 1) {
                        $status_text = '<div class="font-bold c-yellow">' . zib_get_svg('time') . ' 售后中</div><span class="mt6 px12 muted-2-color font-normal">等待商家处理</span>';
                    } elseif ($after_sale_status === 2) {
                        $progress = zib_shop_get_order_after_sale_progress($after_sale_data);
                        if ($progress === 1) {
                            if ($return_express_over_time) {
                                $time_remaining = zib_time_to_c($return_express_over_time);
                                $time_countdown = '<span class="ml10 em09 muted-2-color">剩余发货时间：<span class="c-yellow badg badg-sm" int-second="1" data-over-text="1秒" data-countdown="' . $time_remaining . '"></span></span>';
                            } else {
                                $time_countdown = '';
                            }

                            $after_sale_status_html = '<span class="ml10 muted-color">等待您发货</span>' . $time_countdown;
                        } elseif ($progress === 2) {
                            $after_sale_status_html = '<span class="ml10 muted-color">您已发货，等待商家处理</span><span class="ml10 muted-2-color">' . $after_sale_data['user_return_time'] . '</span>';
                        } elseif ($progress === 3) {
                            $after_sale_status_html = '<span class="ml10 muted-color">商家已发货，等待您收货</span><span class="ml10 muted-2-color">' . $after_sale_data['user_apply_time'] . '</span>';
                        }
                        $_after_sale = '<div class="text-ellipsis muted-color"><i class="fa-fw fa fa-heart-o"></i><b class="ml6">售后中</b>' . $type_name . $after_sale_status_html . '</div>';
                        $_after_sale = zib_shop_get_order_after_sale_link($order, 'padding-h10 zib-widget mb10 flex jsb ac mt10', '<div class="overflow-hidden">' . $_after_sale . '</div><div class="flex0 ml10 muted-2-color"><i class="fa fa-angle-right em12"></i></div>');

                        $status_text = '<div class="font-bold c-yellow">' . zib_get_svg('time') . ' 售后中</div><div class="mt6 px12 muted-2-color font-normal">等待您发货</div>' . $_after_sale;
                    }
                }
            }
        }
    } elseif ($status == -2) {
        $after_sale_type       = $order_data['after_sale_data']['type'] ?? '';
        $after_sale_type_names = [
            'refund'        => '已退款',
            'refund_return' => '已退货退款',
        ];

        $after_sale_type_name = $after_sale_type_names[$after_sale_type] ?? '已退款';

        $status_text = '<b class="inflex ac c-red"><i class="fa fa-check-circle-o mr6 em12"></i>' . $after_sale_type_name . '</b>';
    }

    return $status_text;
}

//订单商品列表
function zib_shop_user_order_product_box($order, $class = 'mb10')
{

    $order_data = zibpay::get_meta($order['id'], 'order_data');
    $count      = $order_data['count'] ?? 1;
    $_opt_name  = $order_data['options_active_name'] ? '<div class="muted-color em09 mt6">' . $order_data['options_active_name'] . '</div>' : '';
    $is_points  = $order_data['pay_modo'] === 'points';
    $_mark      = '<span class="pay-mark px12">' . ($is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark()) . '</span>';

    $_unit_discount_price = zib_floatval_round($order_data['prices']['total_discount_price'] / $count);
    $unit_discount_price  = $_mark . '<b>' . $_unit_discount_price . '</b>';
    $_unit_price          = zib_floatval_round($order_data['prices']['unit_price']);
    $unit_price           = $_unit_price > $_unit_discount_price ? '<div class="original-price muted-2-color">' . $_mark . '<span>' . $_unit_price . '</span></div>' : '';
    $_tag                 = '';

    $post_id = $order['post_id'];
    $post    = get_post($post_id);
    if ($post) {
        $_img   = zib_shop_get_order_thumb($order, 'radius8 fit-cover', 'medium');
        $_title = '<a href="' . get_permalink($post) . '" class="text-ellipsis mb6 font-bold">' . $post->post_title . '</a>';
    } else {
        $_img   = zib_get_svg('order-color', null, 'fit-cover muted-box');
        $_title = '<span class="text-ellipsis mb6 font-bold">' . ($order_data['product_title'] ?? '商品已下架') . '</span>';
    }

    $html = '';
    $html .= '<div class="order-content flex order-item ' . $class . '">
            <div class="order-thumb mr10">' . $_img . '</div>
            <div class="flex1 flex jsb xx">
                <div class="flex1 flex jsb">
                    <div class="flex1 mr10">
                        <div class="order-title text-ellipsis mb6">' . $_title . '</div>
                        ' . $_opt_name . '
                        <div class="muted-color em09 mt6">' . $_tag . '</div>
                    </div>
                    <div class="flex xx ab">
                        <div class="unit-price">' . $unit_discount_price . '</div>
                        ' . $unit_price . '
                        <div class="count mt6 muted-color">x' . $count . '</div>
                    </div>
                </div>
            </div>
        </div>';

    return $html;
}
