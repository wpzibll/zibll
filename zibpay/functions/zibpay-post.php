<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-03-31 16:36:20
 * @LastEditTime : 2026-05-05 22:50:12
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|支付系统|文章模块
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 付费文章唤起收银台，仅适用于post类型
 * @param {*} $post_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zibpay_get_post_cashier_link($post_id = 0, $class = 'but jb-red', $con = '立即购买', $query_arg = array())
{
    if (!$post_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini',
        'class'         => 'cashier-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 300,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'pay_cashier_modal',
            'id'     => $post_id,
        ),
    );

    if ($query_arg) {
        $args['query_arg'] = array_merge($args['query_arg'], $query_arg);
    }

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 积分收银台内容
 * @param {*} $post_id
 * @return {*}
 */
function zibpay_pay_points_cashier_modal($post_id = 0, $price_type = '')
{
    $user_id  = get_current_user_id();
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type'] || !zibpay_post_is_points_modo($pay_mate)) {
        return;
    }

    $pay_type        = $pay_mate['pay_type'];
    $order_type_name = zibpay_get_pay_type_name($pay_type, true);
    //标题
    $pay_title = zibpay_get_post_pay_title($pay_mate, $post_id);

    $mark      = zibpay_get_points_mark();
    $mark      = '<span class="mr3 px12">' . $mark . '</span>';
    $pay_limit = !empty($pay_mate['pay_limit']) ? (int) $pay_mate['pay_limit'] : 0;
    $price     = (int) $pay_mate['points_price'];
    //会员价格
    $vip_price = 0;
    $pay_price = $price;
    if ($user_id) {
        $vip_level = zib_get_user_vip_level($user_id);
        if ($vip_level && _pz('pay_user_vip_' . $vip_level . '_s', true)) {
            $vip_price = isset($pay_mate['vip_' . $vip_level . '_points']) ? (int) $pay_mate['vip_' . $vip_level . '_points'] : 0;
            //会员金额和正常金额取更小值
            $pay_price = $vip_price < $price ? $vip_price : $price;
        }
    }

    if ($price_type == 'download_limit_over') {
        $price = !empty($pay_mate['download_limit_over_price']) ? (int) $pay_mate['download_limit_over_price'] : 0;
        if ($price <= 0) {
            return;
        }

        $original_price = '';
        $pay_price      = $price;
        $vip_price      = 0;
    }

    //商品卡片
    $con = '<div class="mb10 order-type-' . $pay_type . '"><span class="pay-tag badg badg-sm mr6">' . $order_type_name . '</span><span>' . $pay_title . '</span></div>';
    $con .= '<div class="mb10 muted-box padding-h6 line-16">';
    $con .= $pay_limit > 0 ? '' : '<div class="flex jsb ab"><span class="muted-2-color">价格</span><div><span>' . $mark . '<span class="em14">' . $price . '</span></span></div></div>';
    $con .= $vip_price ? '<div class="flex jsb ab"><span class="muted-2-color">会员价' . zibpay_get_vip_icon($vip_level, 'ml3') . '</span><span class="c-red">' . $mark . '<span class="em14">' . $pay_price . '</span></span></div>' : '';
    $con .= '</div>';

    //我的积分卡片
    $user_points = zibpay_get_user_points($user_id);
    $con .= '<div class="mb10 muted-box">';
    $con .= '<div class="flex jsb ab"><span class="muted-2-color">' . zib_get_svg('points-color', null, 'em12 mr6') . '我的积分</span><div><span class="c-green">' . $mark . '<span class="em14">' . $user_points . '</span></span></div></div>';
    $con .= '</div>';

    $form = '<form>';
    $form .= '<input type="hidden" name="post_id" value="' . $post_id . '">';
    $form .= '<input type="hidden" name="order_type" value="' . $pay_type . '">';
    $form .= '<input type="hidden" name="price_type" value="' . $price_type . '">';
    $form .= '<input type="hidden" name="action" value="points_initiate_pay">';
    $form .= '<button class="but jb-yellow padding-lg btn-block radius wp-ajax-submit mt10" >立即支付<span class="ml6 px12">' . zibpay_get_points_mark() . '</span>' . $pay_price . '</button>';
    $form .= '</form>';

    //如果积分不足
    if ($pay_price > $user_points) {
        $points_pay_link = zibpay_get_points_pay_link('but c-green padding-lg', '购买积分');
        $points_user_url = zib_get_user_center_url('balance');

        $form = '';
        $form .= '<div class="badg c-red btn-block mb20">抱歉，您的积分不足，暂时无法支付</div>';
        $form .= '<div class="modal-buts but-average"><a rel="nofollow" type="button" class="but padding-lg" href="' . $points_user_url . '">我的积分</a>' . $points_pay_link . '</div>';
    }

    $header = zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-cart-plus"></i>', '确认购买');
    return $header . $con . $form;
}

/**
 * @description: 现金收银台内容
 * @param {*} $post_id
 * @return {*}
 */
function zibpay_pay_cashier_modal($post_id = 0, $price_type = '')
{
    $user_id  = get_current_user_id();
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        return;
    }

    //获取此用户、此商品，待支付的订单
    $wait_payment_data = zibpay_get_current_user_post_wait_payment($post_id, $pay_mate['pay_type']);
    if ($wait_payment_data) {
        return zibpay_get_order_pay_modal_content($wait_payment_data, get_permalink($post_id));
    }

    $pay_type        = $pay_mate['pay_type'];
    $order_type_name = zibpay_get_pay_type_name($pay_type, true);
    //标题
    $pay_title = zibpay_get_post_pay_title($pay_mate, $post_id);
    //推广优惠金额
    $rebate_discount = zibpay_get_post_rebate_discount($post_id, $user_id);

    $mark      = zibpay_get_pay_mark();
    $mark      = '<span class="pay-mark px12">' . $mark . '</span>';
    $pay_limit = !empty($pay_mate['pay_limit']) ? (int) $pay_mate['pay_limit'] : 0;
    //价格
    $original_price = !empty($pay_mate['pay_original_price']) ? round((float) $pay_mate['pay_original_price'], 2) : 0;
    $price          = round((float) $pay_mate['pay_price'], 2);
    $pay_price      = $price;
    $original_price = $original_price && $original_price > $price ? '<div class="inline-block mr10"><span class="original-price">' . $mark . '<span>' . $original_price . '</span></span></div>' : '';
    //会员价格
    $vip_price = 0;
    if ($user_id) {
        $vip_level = zib_get_user_vip_level($user_id);
        if ($vip_level && _pz('pay_user_vip_' . $vip_level . '_s', true)) {
            $vip_price = isset($pay_mate['vip_' . $vip_level . '_price']) ? round((float) $pay_mate['vip_' . $vip_level . '_price'], 2) : 0;
            //会员金额和正常金额取更小值
            $pay_price = $vip_price < $price ? $vip_price : $price;
        }
    }

    if ($price_type == 'download_limit_over') {
        $price = !empty($pay_mate['download_limit_over_price']) ? round((float) $pay_mate['download_limit_over_price'], 2) : 0;
        if ($price <= 0) {
            return;
        }

        $original_price = '';
        $pay_price      = $price;
        $vip_price      = 0;
    }

    //商品卡片
    $con = '<div class="mb10 order-type-' . $pay_type . '"><span class="pay-tag badg badg-sm mr6">' . $order_type_name . '</span><span>' . $pay_title . '</span></div>';
    $con .= '<div class="mb10 muted-box padding-h6 line-16">';
    $con .= $pay_limit > 0 ? '' : '<div class="flex jsb ab"><span class="muted-2-color">价格</span><div>' . $original_price . '<span>' . $mark . '<span class="em14">' . $price . '</span></span></div></div>';
    $con .= $vip_price ? '<div class="flex jsb ab"><span class="muted-2-color">会员价' . zibpay_get_vip_icon($vip_level, 'ml3') . '</span><span>' . $mark . '<span class="em14">' . $vip_price . '</span></span></div>' : '';
    $con .= $rebate_discount ? '<div class="flex jsb ab"><span class="muted-2-color">推广折扣</span><div><span>' . '<span class="em14">-' . $rebate_discount . '</span></span></div></div>' : '';
    $con .= $rebate_discount || $vip_price ? '<div class="flex jsb ab"><span class="muted-2-color">支付金额</span><div class="font-bold c-red"><span>' . $mark . '<span class="em14">' . ($pay_price - $rebate_discount) . '</span></span></div></div>' : '';
    $con .= '</div>';

    $form = '<form>';
    $form .= '<input type="hidden" name="post_id" value="' . $post_id . '">';
    $form .= '<input type="hidden" name="order_type" value="' . $pay_type . '">';
    $form .= '<input type="hidden" name="price_type" value="' . $price_type . '">';
    $form .= zibpay_get_initiate_pay_input($pay_type, ($pay_price - $rebate_discount), $post_id);
    $form .= '</form>';

    $header = zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-cart-plus"></i>', '确认购买');
    return $header . $con . $form;
}

/**在文章页面插入产品购买模块 */
function zibpay_posts_pay_content()
{
    global $post;
    $pay_mate = get_post_meta($post->ID, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
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
        //添加处理挂钩
        $html = apply_filters('zibpay_posts_paid_box', '', $pay_mate, $post->ID);
        $html = $html ? $html : zibpay_posts_paid_box($pay_mate, $paid, $post->ID);
        echo $html;
    } else {
        //添加处理挂钩
        $html = apply_filters('zibpay_posts_pay_box', '', $pay_mate, $post->ID);
        $html = $html ? $html : zibpay_posts_pay_box($pay_mate, $post->ID);
        echo $html;
    }
}

$pay_box_position = _pz('pay_box_position', 'top');
$positions        = array(
    'box_top'    => 'zib_single_before',
    'top'        => 'zib_single_box_content_before',
    'bottom'     => 'zib_article_content_after',
    'box_bottom' => 'zib_single_after',
);
$pay_box_position = isset($positions[$pay_box_position]) ? $positions[$pay_box_position] : 'zib_single_box_content_before';
add_action($pay_box_position, 'zibpay_posts_pay_content', 1);

//获取购买的服务内容
function zibpay_get_service($class = 'inline-block mr10', $icon_class = 'c-red mr3')
{
    $_pz = _pz('pay_service');
    if (!$_pz || !is_array($_pz)) {
        return;
    }

    $html = '';
    foreach ($_pz as $service) {
        if (empty($service['value'])) {
            continue;
        }

        $icon  = !empty($service['icon']) ? zib_get_cfs_icon($service['icon'], 'fa-fw') : '<i class="fa-fw fa fa-check-circle-o" aria-hidden="true"></i>';
        $value = $service['value'];
        $html .= '<div class="' . $class . '">' . $icon . $value . '</div>';
    }
    return $html;
}

//获取购买的演示按钮
function zibpay_get_demo_link($pay_mate = array(), $post_id = 0, $class = 'but c-yellow padding-lg btn-block em09')
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$pay_mate) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (2 != $pay_mate['pay_type'] || empty($pay_mate['demo_link']['url'])) {
        return;
    }

    $url    = $pay_mate['demo_link']['url'];
    $text   = !empty($pay_mate['demo_link']['text']) ? $pay_mate['demo_link']['text'] : '查看演示';
    $target = !empty($pay_mate['demo_link']['target']) ? ' target="_blank"' : '';

    $link = '<a' . $target . ' href="' . esc_url($url) . '" class="' . $class . '"><i class="fa fa-link fa-fw" aria-hidden="true"></i>' . $text . '</a>';

    return $link;
}

//获取已售数量 销售数量
function zibpay_get_sales_volume($pay_mate = array(), $post_id = 0)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    //从缓存获取
    $cache_num = wp_cache_get($post_id, 'post_pay_cuont', true);
    if (false !== $cache_num) {
        return $cache_num;
    }

    $cuont = (int) get_post_meta($post_id, 'sales_volume', true);
    if ($cuont) {
        return $cuont;
    }

    if (!isset($pay_mate['pay_cuont'])) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }
    $pay_cuont_a = isset($pay_mate['pay_cuont']) ? (int) $pay_mate['pay_cuont'] : 0;

    $cuont = zibDB::name(zibpay::$order_table_name)->where('post_id', $post_id)->where('status', 1)->count();
    $cuont = $pay_cuont_a + $cuont;

    //添加缓存
    wp_cache_set($post_id, $cuont, 'post_pay_cuont');
    return $cuont > 0 ? $cuont : 0;
}

//获取推广返利促销标签
function zibpay_get_rebate_discount_tag($post_id = 0)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $rebate_discount = zibpay_get_post_rebate_discount($post_id);
    $discount_tag    = '';

    if ($rebate_discount) {
        $referrer_id = zibpay_get_referrer_id();

        $referrer_data = get_userdata($referrer_id);
        $discount_tag  = _pz('pay_rebate_text_discount');
        $discount_tag  = str_replace('%discount%', $rebate_discount, $discount_tag);
        $discount_tag  = str_replace('%referrer_name%', $referrer_data->display_name, $discount_tag);
    }
    return $discount_tag;
}

//免费内容-需要登录才能查看
function zibpay_posts_free_logged_show_box($pay_mate, $post_id = '')
{
    if (!$pay_mate) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        return;
    }

    $order_type_name = zibpay_get_pay_type_name($pay_mate['pay_type'], true);
    $order_type_name = str_replace('付费', '免费', $order_type_name);

    $cuont        = '';
    $cuont_volume = zibpay_get_sales_volume($pay_mate, $post_id);
    if (_pz('pay_show_paycount', true) && $cuont_volume) {
        $cuont = '<badge class="img-badge hot jb-blue px12">已售 ' . $cuont_volume . '</badge>';
    }
    $mark = zibpay_get_pay_mark();
    $mark = '<span class="pay-mark">' . $mark . '</span>';

    //会员价格
    $vip_price = zibpay_get_posts_vip_price($pay_mate);

    //标题
    $pay_title = zibpay_get_post_pay_title($pay_mate, $post_id);
    //价格
    $price = zibpay_get_show_price($pay_mate, $post_id, 'c-red');

    //更多内容
    $pay_details = !empty($pay_mate['pay_details']) ? '<div class="pay-details">' . $pay_mate['pay_details'] . '</div>' : '';

    //商品属性
    $attribute = zibpay_get_product_attributes($pay_mate, $post_id);

    //演示地址
    $demo_link = zibpay_get_demo_link($pay_mate, $post_id);
    //服务内容
    $service = zibpay_get_service('inline-block ml10');
    $service = $service ? '<div class="px12 muted-2-color mt10 text-right">' . $service . '</div>' : '';
    //付费类型
    $order_type_name = '<div class="pay-tag abs-center">' . $order_type_name . '</div>';

    //左侧图片
    $product_graphic = '';
    $post_thumbnail  = '';
    if (5 != $pay_mate['pay_type']) {
        if (6 == $pay_mate['pay_type']) {
            $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="fit-cover lazyload" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover"';

            $video_pic      = !empty($pay_mate['video_pic']) ? '<img ' . $lazy_attr . 'src="' . esc_attr($pay_mate['video_pic']) . '" alt="付费视频-' . esc_attr($pay_title) . '">' : zib_post_thumbnail();
            $post_thumbnail = $video_pic;
            $post_thumbnail .= '<div class="absolute graphic-mask" style="opacity: 0.2;"></div>';
            $post_thumbnail .= '<div class="abs-center text-center"><i class="fa fa-play-circle-o fa-4x opacity8" aria-hidden="true"></i></div>';
        } else {
            $post_thumbnail = zib_post_thumbnail();
        }

        $product_graphic = '<div class="flex0 relative mr20 hide-sm pay-thumb"><div class="graphic">';
        $product_graphic .= $post_thumbnail;
        $product_graphic .= '<div class="abs-center text-center left-bottom">';
        $product_graphic .= $demo_link ? '<div class="">' . $demo_link . '</div>' : '';
        $product_graphic .= '</div>';
        $product_graphic .= '</div></div>';
    } else {
        $product_graphic = zibpay_get_posts_pay_gallery_box($pay_mate);
        $product_graphic = str_replace('请付费后', '请登录后', $product_graphic);
    }

    //登录按钮
    if (zib_is_close_sign()) {
        //是否开启登录功能
        //简介
        $pay_doc = !empty($pay_mate['pay_doc']) ? $pay_mate['pay_doc'] : '';
        $button  = '<div class=""><span class="badg padding-lg btn-block c-red em09"><i class="fa fa-info-circle mr10"></i>登录功能已关闭，暂时无法查看</span></div>';
    } else {
        $pay_doc = !empty($pay_mate['pay_doc']) ? $pay_mate['pay_doc'] : '此内容为' . str_replace('付费', '免费', zibpay_get_pay_type_name($pay_mate['pay_type'])) . '，请登录后查看';
        $button  = '<div class=""><a href="javascript:;" class="but signin-loader padding-lg btn-block jb-blue"><i class="fa fa-sign-in"></i> 登录查看</a></div>';
    }

    //左侧图片结束
    $order_type_class = 'order-type-' . $pay_mate['pay_type'];
    $html             = '<div class="zib-widget pay-box  ' . $order_type_class . '" id="posts-pay">';
    $html .= '<div class="flex pay-flexbox">';
    $html .= $product_graphic;
    $html .= '<div class="flex1 flex xx jsb">';
    $html .= '<dt class="text-ellipsis pay-title"' . ($cuont ? 'style="padding-right: 48px;"' : '') . '>' . $pay_title . '</dt>';
    $html .= '<div class="mt6 em09 muted-2-color">' . $pay_doc . '</div>';
    $html .= '<div class="hide-sm">' . $price . '</div>';
    $html .= '<div class="text-right mt10">' . $button . '</div>';

    $html .= '';
    $html .= '</div>';
    $html .= '</div>';
    $html .= $service;
    $html .= $demo_link ? '<div class="mt10 visible-xs-block">' . $demo_link . '</div>' : '';
    $html .= $attribute;
    $html .= $pay_details;
    $html .= $order_type_name;
    $html .= $cuont;
    $html .= '</div>';

    return $html;
}

/**文章已经付费模块 */
function zibpay_posts_paid_box($pay_mate, $paid, $post_id = '')
{

    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        return;
    }

    //判断免费资源且需要登录
    if ('free' == $paid['paid_type'] && _pz('pay_free_logged_show') && !is_user_logged_in()) {
        return zibpay_posts_free_logged_show_box($pay_mate, $post_id);
    }

    //标题
    $pay_title = zibpay_get_post_pay_title($pay_mate, $post_id);

    //简介
    $pay_doc = !empty($pay_mate['pay_doc']) ? $pay_mate['pay_doc'] : '';

    //销售数量
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
    //服务内容
    $service = zibpay_get_service('inline-block ml10');
    $service = $service ? '<div class="px12 muted-2-color mt10 text-right">' . $service . '</div>' : '';
    //订单类型
    $order_type_name = zibpay_get_pay_type_name($pay_mate['pay_type'], true);

    //查看原因
    $paid_type = $paid['paid_type'];
    $paid_name = zibpay_get_paid_type_name($paid_type);

    //订单类型
    $pay_type         = $pay_mate['pay_type'];
    $order_type_class = 'order-type-' . $pay_mate['pay_type'];

    $paid_box   = '';
    $header_box = '';
    $down_box   = '';

    switch ($pay_type) {
        // 根据支付接口循环进行支付流程
        case 1:
            break;
        case 2:
            //付费阅读 //付费下载

            //商品属性
            $attribute = zibpay_get_product_attributes($pay_mate, $post_id);

            if (_pz('pay_type_option', 0, 'down_alone_page')) {
                //判断是否开启独立下载页面
                $demo_link = zibpay_get_demo_link($pay_mate, $post_id, 'but jb-yellow padding-lg btn-block');
                $down_link = '<a target="_blank" href="' . add_query_arg('post', $post_id, zib_get_template_page_url('pages/download.php')) . '" class="but jb-blue padding-lg btn-block"><i class="fa fa-download fa-fw" aria-hidden="true"></i>资源下载</a>';
                $down_box  = '<div class="mt10">' . $down_link . '</div>';
                if ($demo_link) {
                    //判断是否有演示地址
                    $down_box = '<div class="but-group flex paid-down-group mt10">';
                    $down_box .= $demo_link;
                    $down_box .= $down_link;
                    $down_box .= '</div>';
                }
            } else {
                $down_buts = zibpay_get_post_down_buts($pay_mate, $paid_type, $post_id);
                $down_box  = '<div class="hidden-box show"><div class="hidden-text"><i class="fa fa-download mr6" aria-hidden="true"></i>资源下载</div>' . $down_buts . '</div>';
                $down_box .= zibpay_get_demo_link($pay_mate, $post_id);
            }

            $down_box .= $attribute;
            break;

        case 5:
            //付费图库
            $gallery     = '';
            $slide       = '';
            $pay_gallery = $pay_mate['pay_gallery'];
            if ($pay_gallery) {
                $gallery_ids = explode(',', $pay_gallery);
                $all_count   = count($gallery_ids);
                $i           = 1;
                $attachment  = '';
                foreach ((array) $gallery_ids as $id) {
                    $attachment = zib_get_attachment_image_src($id, _pz('thumb_postfirstimg_size'));
                    if (!empty($attachment[0])) {
                        $slide .= zibpay_get_posts_pay_gallery_box_image_slide($id, $i, $all_count, $pay_title, $attachment[0]);
                        $attachment = $attachment[0];
                        $i++;
                    }
                }
                $gallery .= '<div class="swiper-container swiper-scroll">';
                $gallery .= '<div class="swiper-wrapper imgbox-container">';
                $gallery .= $slide;
                $gallery .= '</div>';
                $gallery .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
                $gallery .= '</div>';

                $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="fit-cover lazyload" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover"';

                $header_box = '<div class="relative-h paid-gallery">';
                $header_box .= '<div class="absolute blur-10 opacity3"><img ' . $lazy_attr . 'src="' . esc_attr($attachment) . '" alt="付费图片-' . esc_attr($pay_title) . '"></div>';
                $header_box .= '<div style="margin-top: -20px;" class="relative mb6"><span class="badg b-theme badg-sm"> 共' . $all_count . '张图片 </span></div>';
                $header_box .= $gallery;
                $header_box .= '</div>';
            } else {
                $header_box = '<div class="b-red text-center" style="padding: 30px 10px;"><i class="fa fa-fw fa-info-circle mr10"></i>暂无图片内容，' . (is_super_admin() ? '请在后台添加' : '请与管理员联系') . '</div>';
            }

            break;
        case 6:
            //付费视频
            $video_url = $pay_mate['video_url'];
            $video_pic = $pay_mate['video_pic'];

            if ($video_url) {
                $scale_height = isset($pay_mate['video_scale_height']) ? $pay_mate['video_scale_height'] : 0;

                $header_box = zib_get_dplayer($video_url, $video_pic, $scale_height);
                //视频剧集
                $episode_array = isset($pay_mate['video_episode']) ? $pay_mate['video_episode'] : false;
                $episode_lists = '';
                $episode_index = 1;
                if ($episode_array && is_array($episode_array)) {
                    foreach ($episode_array as $episode) {
                        if (!empty($episode['url'])) {
                            $episode_index++;
                            $episode_title = $episode['title'] ? $episode['title'] : '第' . $episode_index . '集';
                            $episode_lists .= '<a href="javascript:;" class="switch-video text-ellipsis" data-index="' . $episode_index . '" video-url="' . $episode['url'] . '"><span class="mr6 badg badg-sm">' . $episode_index . '</span><i class="episode-active-icon"></i>' . $episode_title . '</a>';
                        }
                    }
                }

                $episode_html = '';
                if ($episode_lists) {
                    $episode_title = $pay_mate['video_title'] ? $pay_mate['video_title'] : '第1集';
                    $episode_html  = '<div class="featured-video-episode mt10">';
                    $episode_html .= '<a href="javascript:;" class="switch-video text-ellipsis active" data-index="1" video-url="' . $pay_mate['video_url'] . '"><span class="mr6 badg badg-sm">1</span><i class="episode-active-icon"></i>' . $episode_title . '</a>';
                    $episode_html .= $episode_lists;
                    $episode_html .= '</div>';
                }
                $pay_doc .= $episode_html;
            } else {
                $header_box = '<div class="b-red text-center" style="padding: 30px 10px;"><i class="fa fa-fw fa-info-circle mr10"></i>暂无视频内容，' . (is_super_admin() ? '请在后台添加' : '请与管理员联系') . '</div>';
            }
            break;
    }

    //已支付模块
    if ('free' == $paid_type) {
        //免费
        $paid_box        = '';
        $order_type_name = str_replace('付费', '免费', $order_type_name);
    } elseif ('paid' == $paid_type) {
        //已经购买
        $mark      = zibpay_get_pay_mark();
        $mark      = '<span class="pay-mark">' . $mark . '</span>';
        $paid_info = '<div class="flex jsb"><span>订单号</span><span>' . zibpay_get_order_num_link($paid['order_num']) . '</span></div>';
        $paid_info .= '<div class="flex jsb"><span>支付时间</span><span>' . $paid['pay_time'] . '</span></div>';
        $paid_info .= '<div class="flex jsb"><span>支付金额</span><span>' . zibpay_get_order_pay_price($paid) . '</span></div>';

        //到期时间
        $order_expired_time = $paid['order_expired_time'] ?? 0;
        $order_expired_html = '';
        if ($paid['paid_type'] == 'paid' && $order_expired_time) {
            $order_expired_html = '<div class="flex jsb"><span>订单有效期</span><span data-toggle="tooltip" title="当前订单有效期截止到' . $order_expired_time . '，到期后需重新购买">' . $order_expired_time . '<i class="fa fa-question-circle-o ml6"></i></span></div>';
        }
        $paid_info .= $order_expired_html;

        $paid_box .= '<div class="flex ac jb-green padding-10 em09">';
        $paid_box .= '<div class="text-center flex-auto"><div class="mb6"><i class="fa fa-shopping-bag fa-2x" aria-hidden="true"></i></div><b class="em12">' . $paid_name . '</b></div>';
        $paid_box .= '<div class="em09 paid-info flex-auto">' . $paid_info . '</div>';
        $paid_box .= '</div>';
    } elseif (stristr($paid_type, 'vip')) {
        //会员免费
        $paid_box .= '<div class="flex jsb ac payvip-icon box-body vipbg-v' . $paid['vip_level'] . '">';
        $paid_box .= zibpay_get_show_price($pay_mate, $post_id);
        $paid_box .= '<div class="flex0"><b class="em12">' . zibpay_get_vip_icon($paid['vip_level'], 'mr10 em12') . $paid_name . '</b></div>';
        $paid_box .= '</div>';
    }

    //构建内容
    $html = '<div class="pay-box zib-widget paid-box ' . $order_type_class . '" id="posts-pay">';
    $html .= $header_box;
    $html .= $paid_box;

    $html .= '<div class="box-body relative' . (6 == $pay_type ? ' dplayer-featured' : '') . '">';
    $html .= $cuont;

    $html .= '<div' . ($cuont ? ' style="padding-right: 48px;"' : '') . '><span class="badg c-red hollow badg-sm mr6">' . $order_type_name . '</span><b>' . $pay_title . '</b></div>';
    $html .= $pay_doc ? '<div class="mt10">' . $pay_doc . '</div>' : '';
    $html .= $down_box;
    $html .= $pay_details;
    $html .= $pay_extra_hide;
    $html .= $service;
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

//文章购买模块
function zibpay_posts_pay_box($pay_mate = array(), $post_id = 0)
{
    if (!$pay_mate) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        return;
    }

    $order_type_name = zibpay_get_pay_type_name($pay_mate['pay_type'], true);

    $cuont        = '';
    $cuont_volume = zibpay_get_sales_volume($pay_mate, $post_id);
    if (_pz('pay_show_paycount', true) && $cuont_volume) {
        $cuont = '<badge class="img-badge hot jb-blue px12">已售 ' . $cuont_volume . '</badge>';
    }

    //会员价格
    $vip_price = zibpay_get_posts_vip_price($pay_mate);

    //标题
    $pay_title = zibpay_get_post_pay_title($pay_mate, $post_id);
    //价格
    $price = zibpay_get_show_price($pay_mate, $post_id, 'c-red');

    //简介
    $pay_doc = !empty($pay_mate['pay_doc']) ? $pay_mate['pay_doc'] : '此内容为' . zibpay_get_pay_type_name($pay_mate['pay_type']) . '，请付费后查看';

    //更多内容
    $pay_details = !empty($pay_mate['pay_details']) ? '<div class="pay-details">' . $pay_mate['pay_details'] . '</div>' : '';

    //商品属性
    $attribute = zibpay_get_product_attributes($pay_mate, $post_id);
    //购买按钮
    $pay_button = zibpay_get_pay_form_but($pay_mate, $post_id);
    //演示地址
    $demo_link = zibpay_get_demo_link($pay_mate, $post_id);
    //服务内容
    $service = zibpay_get_service('inline-block ml10');
    $service = $service ? '<div class="px12 muted-2-color mt10 text-right">' . $service . '</div>' : '';
    //付费类型
    $order_type_name = '<div class="pay-tag abs-center">' . $order_type_name . '</div>';

    //推广让利
    $discount_tag = zibpay_get_rebate_discount_tag($post_id);

    //左侧图片
    $product_graphic = '';
    $post_thumbnail  = '';
    if (5 != $pay_mate['pay_type']) {
        if (6 == $pay_mate['pay_type']) {
            $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="fit-cover lazyload" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover"';

            $video_pic      = !empty($pay_mate['video_pic']) ? '<img ' . $lazy_attr . 'src="' . esc_attr($pay_mate['video_pic']) . '" alt="付费视频-' . esc_attr($pay_title) . '">' : zib_post_thumbnail();
            $post_thumbnail = $video_pic;
            $post_thumbnail .= '<div class="absolute graphic-mask" style="opacity: 0.2;"></div>';
            $post_thumbnail .= '<div class="abs-center text-center"><i class="fa fa-play-circle-o fa-4x opacity8" aria-hidden="true"></i></div>';

            //视频剧集
            $episode_array = isset($pay_mate['video_episode']) ? $pay_mate['video_episode'] : false;
            if (is_array($episode_array)) {
                $episode_count = count($episode_array) + 1;
                if ($episode_count > 1) {
                    $pay_title = '<span class="badg badg-sm b-theme mr6"><i class="fa fa-play-circle mr3" aria-hidden="true"></i>共' . $episode_count . '集</span>' . $pay_title;
                }
            }
        } else {
            $post_thumbnail = zib_post_thumbnail();
        }

        $product_graphic = '<div class="flex0 relative mr20 hide-sm pay-thumb"><div class="graphic">';
        $product_graphic .= $post_thumbnail;
        $product_graphic .= '<div class="abs-center text-center left-bottom">';
        $product_graphic .= $demo_link ? '<div class="">' . $demo_link . '</div>' : '';
        $product_graphic .= $discount_tag ? '<div class="padding-6 jb-red px12">' . $discount_tag . '</div>' : '';
        $product_graphic .= '</div>';
        $product_graphic .= '</div></div>';
    } else {
        $product_graphic = zibpay_get_posts_pay_gallery_box($pay_mate);
    }

    //左侧图片结束
    $order_type_class = 'order-type-' . $pay_mate['pay_type'];
    $html             = '<div class="zib-widget pay-box  ' . $order_type_class . '" id="posts-pay">';
    $html .= '<div class="flex pay-flexbox">';
    $html .= $product_graphic;
    $html .= '<div class="flex-auto-h flex xx jsb">';
    $html .= '<dt class="text-ellipsis pay-title"' . ($cuont ? 'style="padding-right: 48px;"' : '') . '>' . $pay_title . '</dt>';
    $html .= '<div class="mt6 em09 muted-2-color">' . $pay_doc . '</div>';

    $html .= '<div class="">' . $price . '</div>';
    $html .= $discount_tag ? '<div class="visible-xs-block badg c-red px12 mb6">' . $discount_tag . '</div>' : '';
    $html .= $vip_price ? '<div>' . $vip_price . '</div>' : '';
    $html .= '<div class="text-right mt10">' . $pay_button . '</div>';

    $html .= '';
    $html .= '</div>';
    $html .= '</div>';
    $html .= $service;
    $html .= $demo_link ? '<div class="mt10 visible-xs-block">' . $demo_link . '</div>' : '';
    $html .= $attribute;
    $html .= $pay_details;
    $html .= $order_type_name;
    $html .= $cuont;
    $html .= '</div>';

    return $html;
}

/**
 * @description: 文章购买模块的相册（付费图库）
 * @param {*} $id
 * @param {*} $i
 * @param {*} $all_count
 * @param {*} $pay_title
 * @param {*} $attachment
 * @return {*}
 */
function zibpay_get_posts_pay_gallery_box_image_slide($id, $i, $all_count, $pay_title = '', $attachment = '')
{
    $lazy_attr = zib_is_lazy('lazy_sider', true) ? 'class="fit-cover lazyload' . (_pz('imagelightbox') ? ' cursor-zoom-in' : '') . '" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover' . (_pz('imagelightbox') ? ' cursor-zoom-in' : '') . '"';

    $attachment      = $attachment ? $attachment : zib_get_attachment_image_src($id, _pz('thumb_postfirstimg_size'))[0];
    $attachment_full = zib_get_attachment_image_src($id, 'full')[0];
    $slide           = '<div class="swiper-slide mr10" style="width: 150px;">';
    $slide .= '';
    $slide .= '<div class="graphic" style="padding-bottom: 100%!important;">';
    $slide .= '<img ' . $lazy_attr . 'src="' . esc_attr($attachment) . '" data-full-url="' . esc_attr($attachment_full) . '" alt="付费图片-' . esc_attr($pay_title) . '">';
    $slide .= '<div class="abs-center right-top"><badge class="b-black opacity8 mr6 mt6">' . $i . '/' . $all_count . '</badge></div>';
    $slide .= '</div>';
    $slide .= '';
    $slide .= '</div>';

    return $slide;
}

//构建付费图片盒子
function zibpay_get_posts_pay_gallery_box($pay_mate = array(), $post_id = 0)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$pay_mate) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (5 != $pay_mate['pay_type']) {
        return;
    }

    $gallery = '';
    $slide   = '';
    //推广让利
    $discount_tag = zibpay_get_rebate_discount_tag($post_id);
    //标题
    $pay_title = zibpay_get_post_pay_title($pay_mate, $post_id);

    $post_thumbnail = zib_post_thumbnail();

    if (!empty($pay_mate['pay_gallery'])) {
        $gallery_ids = explode(',', $pay_mate['pay_gallery']);
        $all_count   = count($gallery_ids);

        $show = (int) $pay_mate['pay_gallery_show'];
        if ($show >= 1 && $all_count > $show) {
            $i          = 1;
            $attachment = '';
            foreach ((array) $gallery_ids as $id) {
                if ($i > $show) {
                    break;
                }
                $attachment = zib_get_attachment_image_src($id, _pz('thumb_postfirstimg_size'))[0];
                $slide .= zibpay_get_posts_pay_gallery_box_image_slide($id, $i, $all_count, $pay_title, $attachment);
                $i++;
            }
            if ($i <= $all_count) {
                $lazy_attr = zib_is_lazy('lazy_sider', true) ? 'class="fit-cover lazyload blur-10 no-imgbox" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover blur-10 no-imgbox"';

                $slide .= '<div class="swiper-slide mr10" style="width: 150px;">';
                $slide .= '<div class="graphic" style="padding-bottom: 100%!important;">';
                $slide .= '<img ' . $lazy_attr . 'src="' . esc_attr($attachment) . '" alt="付费图片-' . esc_attr($pay_title) . '">';
                $slide .= '<div class="absolute graphic-mask" style="opacity: 0.3;"></div>';
                $slide .= '<div class="abs-center text-center">请付费后查看<br>剩余' . ($all_count - $i + 1) . '张图片</div>';
                $slide .= '</div>';
                $slide .= '</div>';
            }

            $gallery = '';
            $gallery .= '<div class="swiper-container swiper-scroll">';
            $gallery .= '<div class="swiper-wrapper imgbox-container">';
            $gallery .= $slide;
            $gallery .= '</div>';
            $gallery .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
            $gallery .= '</div>';

            $product_graphic = '<div class="relative-h pay-gallery mr20 radius8 padding-10 flex ac">';
            $product_graphic .= $gallery;
            $product_graphic .= $discount_tag ? '<div class="padding-6 jb-red px12 abs-center text-center left-bottom hidden-xs">' . $discount_tag . '</div>' : '';

            $product_graphic .= '</div>';
        } else {
            $post_thumbnail  = zib_post_thumbnail();
            $product_graphic = '<div class="flex0 relative mr20 hide-sm pay-thumb"><div class="graphic">';
            $product_graphic .= '<div class="blur-10 absolute">' . $post_thumbnail . '</div>';
            $product_graphic .= '<div class="absolute graphic-mask" style="opacity: 0.3;"></div>';
            $product_graphic .= '<div class="abs-center text-center">共' . $all_count . '张图片<br>请付费后查看</div>';
            $product_graphic .= $discount_tag ? '<div class="padding-6 jb-red px12 abs-center text-center left-bottom">' . $discount_tag . '</div>' : '';
            $product_graphic .= '</div></div>';
        }
    } else {
        $product_graphic = '<div class="flex0 relative mr20 hide-sm pay-thumb"><div class="graphic">';
        $product_graphic .= '<img class="fit-cover" src="' . zib_get_lazy_thumb() . '">';
        $product_graphic .= '<div class="absolute graphic-mask" style="opacity: 0.6;"></div>';
        $product_graphic .= '<div class="abs-center text-center">' . (is_super_admin() ? '暂无图片，请在后台添加付费图片' : '暂无可查看图片，请与站长联系') . '</div>';
        $product_graphic .= $discount_tag ? '<div class="padding-6 jb-red px12 abs-center text-center left-bottom">' . $discount_tag . '</div>' : '';
        $product_graphic .= '</div></div>';
    }

    return $product_graphic;
}

//获取商品属性
function zibpay_get_product_attributes($pay_mate = array(), $post_id = 0, $class = 'flex jsb', $separator = '')
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$pay_mate) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (2 != $pay_mate['pay_type'] || empty($pay_mate['attributes'])) {
        return;
    }

    $attr_html = '';
    foreach ((array) $pay_mate['attributes'] as $attr) {
        if (!empty($attr['key']) && !empty($attr['value'])) {
            $attr_html .= '<div class="' . $class . '">';
            $attr_html .= '<span class="attr-key flex0 mr20 opacity8">' . $attr['key'] . '</span>' . $separator;
            $attr_html .= '<span class="attr-value">' . $attr['value'] . '</span>';
            $attr_html .= '</div>';
        }
    }

    $html = '<div class="pay-attr mt10">';
    $html .= $attr_html;
    $html .= '</div>';
    return $html;
}

//获取标题
function zibpay_get_post_pay_title($pay_mate = array(), $post_id = 0)
{
    if (!$pay_mate) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    $pay_title = !empty($pay_mate['pay_title']) ? $pay_mate['pay_title'] : get_the_title($post_id) . zib_get_subtitle($post_id);
    return $pay_title;
}

//获取普通用户价格模块
function zibpay_get_show_price($pay_mate = array(), $post_id = 0, $class = 'px13')
{
    if (!$pay_mate) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    $mark = zibpay_get_pay_mark();
    $mark = '<span class="pay-mark">' . $mark . '</span>';

    //价格
    $original_price = !empty($pay_mate['pay_original_price']) && round((float) $pay_mate['pay_original_price'], 2) > round((float) $pay_mate['pay_price'], 2) ? '<span class="original-price" title="原价 ' . round((float) $pay_mate['pay_original_price'], 2) . '">' . $mark . round((float) $pay_mate['pay_original_price'], 2) . '</span>' : '';
    //促销标签
    $promotion_tag  = !empty($pay_mate['promotion_tag']) && !empty($pay_mate['pay_original_price']) ? '<badge>' . $pay_mate['promotion_tag'] . '</badge><br/>' : '';
    $original_price = $promotion_tag ? '<div class="inline-block ml10 text-left">' . $promotion_tag . $original_price . '</div>' : $original_price;
    $price          = '<div class="' . $class . '"><b class="em3x">' . $mark . round((float) $pay_mate['pay_price'], 2) . '</b>' . $original_price . '</div>';
    //限制购买
    $pay_limit = !empty($pay_mate['pay_limit']) ? (int) $pay_mate['pay_limit'] : '0';
    if ($pay_limit > 0 && (_pz('pay_user_vip_1_s', true) || _pz('pay_user_vip_2_s', true))) {
        $title = array(
            '1' => _pz('pay_user_vip_1_name') . '及以上会员可购买',
            '2' => '仅' . _pz('pay_user_vip_2_name') . '可购买',
        );
        $vip_icon = zib_get_svg('vip_' . $pay_limit, '0 0 1024 1024', 'mr3');

        $price = '<div class="' . $class . ' padding-h10"><b data-toggle="tooltip" title="' . $title[$pay_limit] . '" class="badg radius jb-vip' . $pay_limit . '" style="padding: 5px 20px;">' . $vip_icon . '会员专属资源</b></div>';
    } elseif (zibpay_post_is_points_modo($pay_mate)) {
        //积分
        $mark = zibpay_get_points_mark();

        $price = '<div class="c-yellow"><span class="em12 mr3">' . $mark . '</span><b class="em3x">' . $pay_mate['points_price'] . '</b><span class="px12 ml3">积分</span></div>';
    }

    return '<div class="price-box">' . $price . '</div>';
}

function zibpay_get_post_mini_badge($pay_mate)
{
    if (empty($pay_mate['pay_type']) || 'no' === $pay_mate['pay_type']) {
        return;
    }

    if (zibpay_post_is_points_modo($pay_mate)) {
        $mark  = zibpay_get_points_mark();
        $price = $pay_mate['points_price'];
        return '<span class="badg badg-sm c-yellow"><span class="em09 mr3">' . $mark . '</span>' . $price . '</span>';
    } else {
        $mark  = zibpay_get_pay_mark();
        $price = $pay_mate['pay_price'];
        return '<span class="badg badg-sm c-red">' . $mark . $price . '</span>';
    }

}

//获取带有form的购买按钮
function zibpay_get_pay_form_but($pay_mate = array(), $post_id = 0)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$pay_mate) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    $user_id        = get_current_user_id();
    $is_points_modo = zibpay_post_is_points_modo($pay_mate);
    $pay_button     = '';
    $remind         = '';

    //免登陆购买
    $pay_limit = !empty($pay_mate['pay_limit']) ? (int) $pay_mate['pay_limit'] : '0';
    if (!$user_id) {
        if (!_pz('pay_no_logged_in', true) || '0' != $pay_limit) {
            if (zib_is_close_sign()) {
                $pay_button = '<span class="badg px12 c-yellow">登录功能已关闭，暂时无法购买</span>';
            } else {
                //登录按钮
                $pay_button = '<a href="javascript:;" class="but jb-blue signin-loader padding-lg"><i class="fa fa-sign-in mr10" aria-hidden="true"></i>登录购买</a>';
            }
        } else {
            $remind = '<div class="pay-extra-hide px12 mt6" style="font-size:12px;">' . _pz('pay_no_logged_remind') . '</div>';
        }
    }

    if ($is_points_modo) {
        //积分购买
        if (!$user_id) {
            if (zib_is_close_sign()) {
                $pay_button = '<span class="badg px12 c-yellow">登录功能已关闭，暂时无法购买</span>';
            } else {
                //登录按钮
                $pay_button = '<a href="javascript:;" class="but jb-blue signin-loader padding-lg"><i class="fa fa-sign-in mr10" aria-hidden="true"></i>登录购买</a>';
            }
        }
    }

    //购买权限
    if ($pay_limit > 0 && (_pz('pay_user_vip_1_s', true) || _pz('pay_user_vip_2_s', true))) {
        $vip_icon = zib_get_svg('vip_' . $pay_limit, '0 0 1024 1024', 'mr3');

        //开始限制购买权限
        $user_vip_level = zib_get_user_vip_level($user_id);

        if (!$user_vip_level) {
            $pay_vip_text = '开通会员';
        } elseif ($user_vip_level < $pay_limit) {
            $pay_vip_text = '升级' . _pz('pay_user_vip_' . $pay_limit . '_name');
        }

        if (!$user_vip_level || $user_vip_level < $pay_limit) {
            $pay_button = '<div class="badg c-yellow em09 mb6"><i class="fa fa-fw fa-info-circle fa-fw mr6" aria-hidden="true"></i>您暂无购买权限，请先' . $pay_vip_text . '</div>';
            $pay_button .= '<a href="javascript:;" vip-level="' . $pay_limit . '" class="but btn-block jb-vip' . $pay_limit . ($user_id ? ' pay-vip' : ' signin-loader') . ' padding-lg">' . $vip_icon . $pay_vip_text . '</a>';
        }
    }

    if (!$pay_button) {
        add_filter('zibpay_is_show_paybutton', '__return_true');
        $pay_button = zibpay_get_post_cashier_link($post_id, 'but ' . ($is_points_modo ? 'jb-yellow' : 'jb-red')) . ($is_points_modo ? '' : $remind);
    }

    return $pay_button;
}

/**
 * @description: 获取文章付费会员价格
 * @param {*} $pay_mate
 * @param {*} $hide
 * @return {*}
 */
function zibpay_get_posts_vip_price($pay_mate, $hide = 0)
{

    if (zib_is_close_sign()) {
        return;
    }

    $mark             = zibpay_get_pay_mark();
    $user_id          = get_current_user_id();
    $action_class     = $user_id ? '' : ' signin-loader';
    $vip_level        = $user_id ? zib_get_user_vip_level($user_id) : false;
    $vip_price_con    = array();
    $price            = isset($pay_mate['pay_price']) ? round((float) $pay_mate['pay_price'], 2) : 0;
    $price_key_suffix = 'price'; //后缀
    if (zibpay_post_is_points_modo($pay_mate)) {
        $mark             = zibpay_get_points_mark();
        $price_key_suffix = 'points'; //后缀
        $price            = isset($pay_mate['points_price']) ? (int) $pay_mate['points_price'] : 0;
    }

    $mark = '<span class="px12">' . $mark . '</span>';

    for ($vi = 1; $vi <= 2; $vi++) {
        if (!_pz('pay_user_vip_' . $vi . '_s', true) || $hide == $vi) {
            continue;
        }
        $vip_price = !empty($pay_mate['vip_' . $vi . '_' . $price_key_suffix]) ? round((float) $pay_mate['vip_' . $vi . '_' . $price_key_suffix], 2) : 0;
        //会员价格与正常价格取最小值
        if ($vip_price >= $price) {
            continue;
        }

        $vip_price = $vip_price ? $mark . $vip_price : '免费';
        $vip_price = '<span class="em12 ml3 vip-price-text">' . $vip_price . '</span>';
        $vip_icon  = zib_get_svg('vip_' . $vi, '0 0 1024 1024', 'mr3 em14') . _pz('pay_user_vip_' . $vi . '_name');

        //action_class
        if ($user_id && (!$vip_level || $vip_level < $vi)) {
            $action_class = ' pay-vip';
        }

        if ($action_class) {
            $vip_price_con[] = '<span href="javascript:;" class="but vip-price ' . $action_class . '" vip-level="' . $vi . '" data-toggle="tooltip" title="开通' . _pz('pay_user_vip_' . $vi . '_name') . '">' . $vip_icon . $vip_price . '</span>';
        } else {
            $vip_price_con[] = '<span class="but vip-price" vip-level="' . $vi . '">' . $vip_icon . $vip_price . '</span>';
        }
    }
    $vip_price_html = implode('', $vip_price_con);
    if (count($vip_price_con) > 1) {
        $vip_price_html = '<span class="vip-price-buts but-average inline">' . $vip_price_html . '</span>';
    }

    return $vip_price_html;
}

//支付成之后，更新商品销量meta
function zibpay_update_posts_meta($pay_order)
{
    /**根据订单号查询订单 */
    $pay_order = (array) $pay_order;
    $post_id   = $pay_order['post_id'];
    zibpay_update_posts_sales_volume($post_id);
}
add_action('payment_order_success', 'zibpay_update_posts_meta');

//更新销量
function zibpay_update_posts_sales_volume($post_id)
{
    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    if (!in_array($post->post_type, ['post', 'forum_post'])) {
        return;
    }

    if ($post_id) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);

        $cuont = zibDB::name(zibpay::$order_table_name)->where('post_id', $post_id)->where('status', 1)->count();
        $cuont = !empty((int) $pay_mate['pay_cuont']) ? (int) $pay_mate['pay_cuont'] + $cuont : $cuont;
        $cuont = $cuont > 0 ? $cuont : 0;

        update_post_meta($post_id, 'sales_volume', $cuont);
        //添加缓存
        wp_cache_set($post_id, $cuont, 'post_pay_cuont');
    }
}

//必要挂载
//同步meta数据
function zibpay_post_pay_meta_update($meta_id, $post_id, $meta_key, $_meta_value)
{

    if ($meta_key !== 'posts_zibpay' || !isset($_meta_value['pay_type'])) {
        return;
    }

    $pay_type = $_meta_value['pay_type'] === 'no' ? 0 : (int) $_meta_value['pay_type'];
    $pay_modo = isset($_meta_value['pay_modo']) ? $_meta_value['pay_modo'] : '0';

    $pay_price    = $pay_modo !== 'points' && isset($_meta_value['pay_price']) ? round((float) $_meta_value['pay_price'], 2) : 0;
    $points_price = $pay_modo === 'points' && isset($_meta_value['points_price']) ? (int) $_meta_value['points_price'] : 0;

    if (!$pay_type) {
        $pay_modo     = 0;
        $pay_price    = 0;
        $points_price = 0;
    }

    update_post_meta($post_id, 'zibpay_type', $pay_type);
    update_post_meta($post_id, 'zibpay_modo', $pay_modo);
    update_post_meta($post_id, 'zibpay_price', $pay_price);
    update_post_meta($post_id, 'zibpay_points_price', $points_price);
    zibpay_update_posts_sales_volume($post_id);
}
add_action('updated_post_meta', 'zibpay_post_pay_meta_update', 99, 4);
add_action('added_post_meta', 'zibpay_post_pay_meta_update', 99, 4);
