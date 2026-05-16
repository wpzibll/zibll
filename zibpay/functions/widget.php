<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-28 16:11:06
 * @LastEditTime : 2026-04-21 18:00:57
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//文章购买小工具
function zibpay_get_widget_box($args = array())
{
    $defaults = array(
        'theme' => 'jb-red',
    );
    $args = wp_parse_args((array) $args, $defaults);

    global $post;
    $pay_mate = get_post_meta($post->ID, 'posts_zibpay', true);

    if (!is_single() || empty($pay_mate['pay_type']) || $pay_mate['pay_type'] == 'no') {
        return;
    }

    //判断阅读权限
    $data = zib_get_post_allow_view_data($post);
    if (!$data['allow']) {
        return;
    }

    // 查询是否已经购买
    $paid = zibpay_is_paid($post->ID);
    if ($paid) {
        return zibpay_posts_paid_widget_box($pay_mate, $post->ID, $paid, $args['theme']);
    } else {
        return zibpay_posts_pay_widget_box($pay_mate, $post->ID, $args['theme']);
    }
}

//侧边栏小工具已经购买
function zibpay_posts_paid_widget_box($pay_mate = array(), $post_id = 0, $paid = array(), $theme = 'jb-red')
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$pay_mate) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (empty($pay_mate['pay_type']) || $pay_mate['pay_type'] == 'no') {
        return;
    }

    //查看原因
    $paid_type = $paid['paid_type'];
    $paid_name = zibpay_get_paid_type_name($paid_type);

    //商品类型
    $order_type_name = '';
    if ($paid_type != 'free') {
        $order_type_name = zibpay_get_pay_type_name($pay_mate['pay_type'], true);
        $order_type_name = '<badge class="pay-tag abs-center">' . $order_type_name . '</badge>';
    }

    $cuont        = '';
    $cuont_volume = zibpay_get_sales_volume($pay_mate, $post_id);
    if (_pz('pay_show_paycount', true) && $cuont_volume) {
        $cuont = '<badge class="img-badge hot jb-blue px12">已售 ' . $cuont_volume . '</badge>';
    }

    //更多内容
    $pay_details = !empty($pay_mate['pay_details']) ? '<div class="pay-details">' . $pay_mate['pay_details'] . '</div>' : '';
    //额外隐藏内容
    $pay_extra_hide = !empty($pay_mate['pay_extra_hide']) ? '<div class="pay-details">' . $pay_mate['pay_extra_hide'] . '</div>' : '';

    //商品属性
    $attribute = zibpay_get_product_attributes($pay_mate, $post_id);
    //演示地址
    $demo_link = zibpay_get_demo_link($pay_mate, $post_id);
    $demo_link = $demo_link ? '<div class="mt10">' . $demo_link . '</div>' : '';

    $get_link_name = array(
        1 => '<i class="fa fa-dot-circle-o fa-fw" aria-hidden="true"></i>查看内容',
        2 => '<i class="fa fa-download fa-fw" aria-hidden="true"></i>获取下载地址',
        5 => '<i class="fa fa-file-image-o fa-fw" aria-hidden="true"></i>查看图片',
        6 => '<i class="fa fa-play-circle fa-fw" aria-hidden="true"></i>查看视频',
    );
    $get_link = '<div class="mt10"><a href="javascript:(scrollTopTo(\'#posts-pay\',-50));" class="but padding-lg btn-block ' . $theme . '">' . (!empty($get_link_name[$pay_mate['pay_type']]) ? $get_link_name[$pay_mate['pay_type']] : '<i class="fa fa-dot-circle-o fa-fw" aria-hidden="true"></i>查看内容') . '</a></div>';

    //服务内容
    $service = zibpay_get_service();
    $service = $service ? '<div class="px12 muted-2-color">' . $service . '</div>' : '';

    $paid_box = '<div class="text-center mt10 box-body"><p><i class="fa fa-shopping-bag fa-3x" aria-hidden="true"></i></p><b class="em14">' . $paid_name . '</b></div>';
    if (stristr($paid_type, 'vip')) {
        //价格
        $paid_info = '<div class="flex jsb ab"><span class="em12">售价：</span><span>' . zibpay_get_show_price($pay_mate, $post_id, 'text-center') . '</span></div>';
        $paid_box .= '<div class="em09 paid-info">' . $paid_info . '</div>';
    } elseif ($paid_type == 'paid') {
        $mark      = zibpay_get_pay_mark();
        $mark      = '<span class="pay-mark">' . $mark . '</span>';
        $paid_info = '<div class="flex jsb"><span>订单号</span><span>' . zibpay_get_order_num_link($paid['order_num']) . '</span></div>';
        $paid_info .= '<div class="flex jsb"><span>支付时间</span><span>' . $paid['pay_time'] . '</span></div>';
        $paid_info .= '<div class="flex jsb"><span>支付金额</span><span>' . zibpay_get_order_pay_price($paid) . '</span></div>';

        //到期时间
        $order_expired_time = $paid['order_expired_time'] ?? 0;
        $order_expired_html = '';
        if ($paid['paid_type'] == 'paid' && $order_expired_time) {
            $order_expired_html = '<div class="flex jsb"><span>有效期</span><span data-toggle="tooltip" title="当前订单有效期截止到' . $order_expired_time . '，到期后需重新购买">' . $order_expired_time . '<i class="fa fa-question-circle-o ml6"></i></span></div>';
        }
        $paid_info .= $order_expired_html;
        $paid_box .= '<div class="em09 paid-info">' . $paid_info . '</div>';
    } elseif ($paid_type == 'free' && _pz('pay_free_logged_show') && !is_user_logged_in()) { //免费内容需要登录但未登录
        $pay_extra_hide = '';
        if (zib_is_close_sign()) { //是否开启登录功能
            $get_link = '<div class="mt10"><span class="badg padding-lg btn-block c-red em09"><i class="fa fa-info-circle mr10"></i>登录功能已关闭，暂时无法查看</span></div>';
        } else {
            $get_link = '<div class="mt10"><a href="javascript:;" class="but signin-loader padding-lg btn-block jb-blue"><i class="fa fa-sign-in"></i> 登录查看</a></div>';
        }
    }

    $order_type_class = 'order-type-' . $pay_mate['pay_type'];
    $html             = '<div class="zib-widget pay-box pay-widget ' . $order_type_class . '" style="padding: 0;">';
    $html .= $order_type_name;
    $html .= '<div class="relative-h ' . $theme . '" style="background-size:120%;">';
    $html .= '<div class="absolute radius ' . $theme . '" style="height: 200px;left: 75%;width: 200px;top: -34%;border-radius: 100%;"></div><div class="absolute ' . $theme . ' radius" style="height: 305px;width: 337px;left: -229px;border-radius: 100%;opacity: .7;"></div>';

    $html .= '<div class="relative box-body">';
    $html .= $paid_box;
    $html .= '</div>';

    $html .= '</div>';
    $html .= '<div class="box-body">';
    $html .= $service;
    $html .= $get_link;
    $html .= $demo_link;
    $html .= $attribute;
    $html .= $pay_details;
    $html .= $pay_extra_hide;
    $html .= '</div>';
    $html .= $cuont;
    $html .= '</div>';

    return $html;
}

//侧边栏小工具购买
function zibpay_posts_pay_widget_box($pay_mate = array(), $post_id = 0, $theme = 'jb-red')
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$pay_mate) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (empty($pay_mate['pay_type']) || $pay_mate['pay_type'] == 'no') {
        return;
    }

    //商品类型
    $order_type_name = zibpay_get_pay_type_name($pay_mate['pay_type'], true);
    $order_type_name = '<badge class="pay-tag abs-center">' . $order_type_name . '</badge>';

    $cuont        = '';
    $cuont_volume = zibpay_get_sales_volume($pay_mate, $post_id);
    if (_pz('pay_show_paycount', true) && $cuont_volume) {
        $cuont = '<badge class="img-badge hot jb-blue px12">已售 ' . $cuont_volume . '</badge>';
    }
    //价格
    $price = zibpay_get_show_price($pay_mate, $post_id, 'text-center mt10');
    //会员价格
    $vip_price = zibpay_get_posts_vip_price($pay_mate);
    //推广让利
    $discount_tag = zibpay_get_rebate_discount_tag($post_id);
    $discount_tag = $discount_tag ? '<div class="text-center padding-6 jb-yellow px12">' . $discount_tag . '</div>' : '';

    //更多内容
    $pay_details = !empty($pay_mate['pay_details']) ? '<div class="pay-details">' . $pay_mate['pay_details'] . '</div>' : '';
    //商品属性
    $attribute  = zibpay_get_product_attributes($pay_mate, $post_id);
    $pay_button = zibpay_get_pay_form_but($pay_mate, $post_id);
    //演示地址
    $demo_link = zibpay_get_demo_link($pay_mate, $post_id);
    $demo_link = $demo_link ? '<div class="mt10">' . $demo_link . '</div>' : '';
    //服务内容
    $service          = zibpay_get_service();
    $service          = $service ? '<div class="px12 muted-2-color">' . $service . '</div>' : '';
    $order_type_class = 'order-type-' . $pay_mate['pay_type'];
    $html             = '<div class="zib-widget pay-box pay-widget ' . $order_type_class . '" style="padding: 0;">';
    $html .= $order_type_name;
    $html .= '<div class="relative-h ' . $theme . '" style="background-size:120%;">';
    $html .= '<div class="absolute radius ' . $theme . '" style="height: 200px;left: 75%;width: 200px;top: -34%;border-radius: 100%;"></div><div class="absolute ' . $theme . ' radius" style="height: 305px;width: 337px;left: -229px;border-radius: 100%;opacity: .7;"></div>';
    $html .= '<div class="relative box-body">';
    $html .= $price;
    $html .= '</div>';
    $html .= '<div class="relative">' . $vip_price . '</div>';
    $html .= '</div>';
    $html .= $discount_tag;
    $html .= '<div class="box-body">';
    $html .= $service;
    $html .= '<div class="mt10">' . $pay_button . '</div>';
    $html .= $demo_link;
    $html .= $attribute;
    $html .= $pay_details;
    $html .= '</div>';
    $html .= $cuont;
    $html .= '</div>';

    return $html;
}
