<?php
/*
* @Author: Qinver
* @Url: zibll.com
* @Date: 2025-02-22 13:55:24
 * @LastEditTime : 2026-04-09 21:18:06
* @Email: 770349780@qq.com
* @Project: Zibll子比主题
* @Description: 一款极其优雅的Wordpress主题|商城系统|商品列表函数
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

//获取商品列表
function zib_shop_get_main_product_lists($card_args = [])
{

    $shop_list_opt = _pz('shop_list_opt');
    $default       = $shop_list_opt['list_style'] ?? [];
    $card_args     = array_merge($default, $card_args);

    $lists = '';
    if (have_posts()) {
        global $wp_query;
        while (have_posts()): the_post();
            $lists .= zib_shop_get_product_list_card($card_args);
        endwhile;
        //帖子分页
        $paginate = zib_shop_get_paginate($wp_query->found_posts);
        if ($paginate) {
            $lists .= $paginate;
        }
        $lists .= '<div class="post_ajax_loader" style="display:none;">' . zib_shop_get_lists_card_placeholder($card_args) . '</div>';

    } else {
        $lists .= zib_get_ajax_null('内容空空如也', 100);
    }

    return $lists;
}

//商品列表
function zib_shop_get_product_list_card($args = [], $post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $defaults = [
        'class'          => '',
        'thumb_fit'      => 'contain',
        'show_desc'      => true,
        'show_discount'  => true,
        'show_sales'     => true, //是否显示销量 off不显示 min超量显示
        'show_sales_min' => 0,
        'show_price'     => true,
        'text_center'    => true,
        'title_one_line' => false,
        'thumb_scale'    => 100,
        'style'          => '',
    ];
    $args = wp_parse_args($args, $defaults);
    $args['class'] .= $args['style'] ? ' style-' . $args['style'] : '';
    if ($args['style'] === 'small') {
        $args['text_center'] = false;
    }

    $graphic_class = $args['thumb_fit'] === 'contain' ? 'contain' : '';
    $graphic       = zib_shop_get_product_list_graphic(['class' => $graphic_class, 'gradient-bg' => ($args['thumb_fit'] === 'contain'), 'show_sales_min' => $args['show_sales_min'], 'show_sales' => $args['show_sales'], 'link' => true], $post);
    $title         = zib_shop_get_product_title($post, ($args['title_one_line'] ? 'text-ellipsis' : ''));

    //显示desc
    $desc = '';
    if ($args['show_desc']) {
        $desc = zib_shop_get_product_config($post->ID, 'desc');
        $desc = '<div class="item-excerpt muted-color text-ellipsis">' . $desc . '</div>';
    }

    //显示价格
    $price = '';
    if ($args['show_price']) {
        $price = zib_shop_get_product_list_price($post->ID);
    }

    //活动
    $discount_badge = '';
    if ($args['show_discount']) {
        $discount_badge = zib_shop_get_product_discount_badges($post->ID);
        $discount_badge = '<div class="item-discount-badge scroll-x no-scrollbar">' . $discount_badge . '</div>';
    }

    $_class = $args['class'] ? ' ' . $args['class'] : '';
    $thumb_attr = $args['thumb_scale'] && $args['thumb_scale'] != 100 ? ' style="--t-s:' . $args['thumb_scale'] . '%;"' : '';

    $html   = '';
    $html .= '<posts class="product-item posts-item card ajax-item' . $_class . '"' . $thumb_attr . '>';
    $html .= $graphic;
    $html .= '<div class="item-body product-item-body ' . ($args['text_center'] ? 'text-center' : '') . '">';
    $html .= $title;
    $html .= $desc;
    $html .= $discount_badge;
    $html .= $price;
    $html .= '</div>';
    $html .= '</posts>';
    return $html;
}

//获取商品列表标题
function zib_shop_get_product_title($post = null, $class = '')
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    $class = $class ? ' ' . $class : '';
    $title = $post->post_title;
    $title = zib_shop_get_product_important_tag($post->ID, 'badg-sm mr3') . $title;

    $title = '<h2 class="item-heading' . $class . '"><a href="' . get_permalink($post) . '">' . $title . '</a></h2>';

    return $title;
}

//获取商品的列表的显示金额
function zib_shop_get_product_list_price($post_id)
{

    $start_price   = zib_shop_get_product_config($post_id, 'start_price'); //初始价格
    $price         = zib_shop_get_product_display_price($post_id); //折扣价
    $pay_modo      = zib_shop_get_product_config($post_id, 'pay_modo'); //支付方式
    $pay_mark      = $pay_modo === 'points' ? zibpay_get_points_mark() : zibpay_get_pay_mark();
    $class         = $pay_modo === 'points' ? 'c-yellow' : 'c-red';
    $show_price    = $start_price;
    $crossed_price = 0; //划线价格
    if ($start_price > $price) {
        $show_price    = round($price, 2);
        $crossed_price = round($start_price, 2);
    }

    $show_price    = zib_shop_format_price($show_price, $pay_modo === 'points');
    $crossed_price = $crossed_price ? zib_shop_format_price($crossed_price, $pay_modo === 'points') : '';

    $price_html = '<div class="text-ellipsis item-price product-price">';
    $price_html .= '<div class="show-price-box ' . $class . '"><span class="pay-mark px12">' . $pay_mark . '</span><b class="price">' . $show_price . '</b></div>';
    if ($crossed_price) {
        $price_html .= '<div class="crossed-price-box original-price ml6 muted-2-color"><span class="pay-mark px12">' . $pay_mark . '</span><span class="price">' . $crossed_price . '</span></div>';
    }
    $price_html .= '</div>';

    return $price_html;
}

//获取商品列表缩略图
function zib_shop_get_product_list_graphic($args = [], $post = null)
{

    $defaults = [
        'class'          => '',
        'show_sales'     => true, //是否显示销量 off不显示 min超量显示
        'show_sales_min' => 0,
        'link'           => true,
        'gradient-bg'    => false,
    ];
    $args           = wp_parse_args($args, $defaults);
    $attr           = '';
    $class          = $args['class'];
    $show_sales     = $args['show_sales'];
    $show_sales_min = $args['show_sales_min'];

    if ($args['gradient-bg']) {
        $class .= ' gradient-bg';
        $attr = ' data-opacity="0.1"';
    }

    if (!is_object($post)) {
        $post = get_post($post);
    }

    $sales_count = '';
    if ($show_sales !== 'off') {
        $sales_count = zib_shop_get_product_sales_volume($post->ID, false);
        if ($show_sales === 'min' && $sales_count <= $show_sales_min) {
            $sales_count = 0;
        }

        $sales_count = $sales_count ? '<badge class="img-badge jb-red px12">已售' . $sales_count . '</badge>' : '';
    }

    $html = '<div class="item-thumbnail ' . $class . '"' . $attr . '>';
    if ($args['link']) {
        $html .= '<a href="' . get_permalink($post) . '">';
    }
    $html .= zib_shop_get_product_thumbnail($post, 'fit-cover', 'medium');
    $html .= $sales_count;
    if ($args['link']) {
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
}

//挂钩搜索功能
function zib_shop_get_search_product()
{
    //开始构建内容
    global $paged;
    $lists     = '';
    $card_args = _pz('shop_list_opt', [], 'list_style');
    while (have_posts()): the_post();
        $lists .= zib_shop_get_product_list_card($card_args);
    endwhile;

    if ($lists) {
        $lists = $lists;
    } elseif ((int) $paged < 2) {
        $lists = zib_get_ajax_null('未找到相关商品', '75', 'null-search.svg');
    }

    $lists .= zib_paging(false, false);
    return $lists;
}
add_filter('main_search_tab_content_product', 'zib_shop_get_search_product', 10);

/**
 * 获取商品列表的排序列表
 * @param string $orderby 排序方式
 * @param string $order 排序顺序
 * @param string $current_url 当前URL
 * @return string 排序列表
 */
function zib_shop_get_orderby_lists($orderby = '', $order = '', $current_url = '')
{

    if (!$orderby) {
        $orderby = _pz('shop_list_opt', 'data', 'orderby');
    }

    $current_url = $current_url ?: zib_url_del_paged(zib_get_current_url());
    $all_args    = array(
        'date'           => '最新',
        'views'          => '最热',
        'favorite_count' => '收藏',
        'score'          => '评分',
        'sales_volume'   => '销量',
    );

    $lists = '';
    foreach ($all_args as $key => $value) {
        if ($orderby == $key) {
            $lists .= '<span class="focus-color">' . $value . '</span>';
        } else {
            $href = add_query_arg(array('orderby' => $key), $current_url);
            $lists .= '<a rel="nofollow" ajax-replace="true" class="ajax-next" href="' . esc_url($href) . '">' . $value . '</a>';
        }
    }

    if ($orderby == 'zibpay_price') {
        if (!$order) {
            global $wp_query;
            $order = $wp_query->get('order');
        }

        $_order = $order === 'DESC' ? 'ASC' : 'DESC';
        $_icon  = '<i class="opacity5 ml3 fa fa-long-arrow-' . ($_order === 'DESC' ? 'up' : 'down') . '"></i>';
        $href   = add_query_arg(array('orderby' => 'zibpay_price', 'order' => $_order), $current_url);

        $lists .= '<a rel="nofollow" ajax-replace="true" class="ajax-next focus-color" href="' . esc_url($href) . '">售价' . $_icon . '</a>';
    } else {
        $lists .= '<a rel="nofollow" ajax-replace="true" class="ajax-next" href="' . esc_url(add_query_arg(array('orderby' => 'zibpay_price'), $current_url)) . '">售价</a>';
    }

    return $lists;
}
