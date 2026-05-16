<?php
/*
* @Author : Qinver
* @Url : zibll.com
* @Date : 2025-02-27 19:09:53
 * @LastEditTime : 2026-04-21 15:07:56
* @Project : Zibll子比主题
* @Description : 更优雅的Wordpress主题 | 统一处理VUE数据
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
* @Email : 770349780@qq.com
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

function zib_shop_get_product_single_vue_data($post)
{

    $data         = zib_shop_get_product_vue_data($post);
    $data['btns'] = [
        'favorite' => zib_shop_get_product_favorite_btn($post->ID),
        'service'  => zib_shop_get_author_contact_link($post->post_author),
        'share'    => zib_shop_get_product_share_btn($post->ID),
    ];
    $data['user_data']             = zib_shop_get_user_vue_data();
    $data['cart_submit_btn_text']  = '加入购物车';
    $data['order_submit_btn_text'] = '立即下单';
    $data['lazy_src']              = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
    return $data;
}

//获取购物车VUE数据
function zib_shop_get_cart_vue_data($user_id)
{

    $cart_items = zib_shop_get_cart_items($user_id);

    if (!$cart_items) {
        return [];
    }

    $product_ids = array_keys($cart_items);
    $query_args  = array(
        'ignore_sticky_posts' => true,
        'post_type'           => 'shop_product',
        'post_status'         => 'publish',
        'orderby'             => 'ID',
        'post__in'            => $product_ids,
        'showposts'           => 500, //最大500
        'no_found_rows'       => true,
    );

    //减少查询字段，提高性能
    add_filter('posts_fields', function ($fields, $query) {
        global $wpdb;
        return "{$wpdb->posts}.ID, {$wpdb->posts}.post_author, {$wpdb->posts}.post_title,{$wpdb->posts}.post_type";
    }, 10, 2);

    $new_query = new WP_Query($query_args);

    //商家，商品，选项
    $shop_author_show = (bool) _pz('shop_author_show', true);
    $cart_data        = array();
    $product_data     = array();
    $author_data      = array();
    $discount_data    = array();
    if (!is_wp_error($new_query) && !empty($new_query->posts)) {
        foreach ($new_query->posts as $posts_item) {
            $product_data[$posts_item->ID]         = zib_shop_get_product_vue_data($posts_item);
            $author_data[$posts_item->post_author] = zib_shop_get_author_vue_data($posts_item->post_author);

            foreach ($cart_items[$posts_item->ID] as $item_key => $item_count) {
                $cart_data[$posts_item->post_author][$posts_item->ID][$item_key] = [
                    'selected_count'       => $item_count,
                    'product_id'           => $posts_item->ID,
                    'options_active_str'   => $item_key,
                    'options_active_name'  => '请选择商品选项',
                    'options_active'       => zib_shop_product_options_to_array($item_key),
                    'stock_all'            => -1, //库存交给JS处理
                    'checked'              => false,
                    'options_active_error' => false, //有选项，但是选项失效或没有选择，JS处理
                    'prices'               => [
                        //价格交给JS处理
                        'start_price'          => 0, //初始价格，不变
                        'unit_price'           => 0, //单价
                        'unit_discount_price'  => 0, //优惠价
                        'total_price'          => 0, //小计原价
                        'total_discount_price' => 0, //小计优惠价
                        'total_discount'       => 0, //优惠金额
                    ],
                ];
            }
        }
    }

    return [
        'cart_modal_data'     => zib_shop_get_cart_modal_vue_data(),
        'product_data'        => $product_data,
        'author_data'         => $author_data,
        'cart_data'           => $cart_data,
        'discount_data'       => $discount_data,
        'user_data'           => zib_shop_get_user_vue_data(),
        'cart_original_items' => $cart_items, //原始
        //总数据
        'total_data'          => [
            'show_mark'       => '',
            'pay_modo'        => '',
            'checked_status'  => '',
            'count'           => 0,
            'price'           => 0, //总价
            'discount_price'  => 0, //总优惠价
            'points'          => 0, //总积分
            'discount_points' => 0, //总优惠积分
            'is_can_pay'      => false, //可以结算
            'is_mix'          => false, //是否混合支付
            'checked_data'    => [],
        ],
        'config'              => [
            'is_edit'              => false,
            'cart_submit_btn_text' => '确认选择',
            'author_show'          => $shop_author_show,
            'lists_placeholder'    => zib_shop_get_lists_card_placeholder(_pz('shop_list_opt', [], 'list_style')),
        ],
    ];
}

//获取商品VUE数据
function zib_shop_get_product_vue_data($post)
{

    if (!is_object($post)) {
        $post = get_post($post);
    }

    $product_id = $post->ID;
    $_configs   = zib_shop_get_product_config($product_id);
    $params     = [];
    if ($_configs['params'] && is_array($_configs['params'])) {
        $params = array_filter($_configs['params'], function ($value) {
            return $value['name'] && $value['value'];
        });
    }

    $user_required = [];
    if (isset($_configs['user_required']) && is_array($_configs['user_required'])) {
        foreach ($_configs['user_required'] as $user_required_item) {
            if ($user_required_item['name']) {
                $user_required[] = array_merge($user_required_item, ['value' => '']);
            }
        }
    }

    $stock_data = zib_shop_get_product_stock($product_id);

    $data = [
        'product_id'         => $product_id,
        'title'              => $post->post_title,
        'url'                => get_the_permalink($post),
        'thumbnail'          => zib_shop_get_product_thumbnail($post),
        'thumbnail_url'      => zib_shop_get_product_thumbnail_url($post, 'full'),
        'pay_mark'           => zibpay_get_pay_mark(),
        'points_mark'        => zibpay_get_points_mark(),
        'desc'               => $_configs['desc'] ?? '',
        'pay_modo'           => $_configs['pay_modo'],
        'params'             => $params,
        'user_required'      => $user_required,
        'prices'             => [
            //价格交给JS处理
            'start_price'          => $_configs['start_price'], //初始价格，不变
            'unit_price'           => 0, //单价
            'unit_discount_price'  => 0, //优惠价
            'total_price'          => 0, //小计原价
            'total_discount_price' => 0, //小计优惠价
            'total_discount'       => 0, //优惠金额
        ],
        'product_options'    => !empty($_configs['product_options']) ? $_configs['product_options'] : [], //规格
        'limit_buy'          => zib_shop_get_product_limit_buy_config($post->ID),
        'tags'               => zib_shop_get_product_tags_data($product_id),
        'discount'           => zib_shop_get_product_discount($post->ID, false),
        'discount_hit'       => [],
        'service'            => zib_shop_get_product_service($product_id),
        'sales_count'        => zib_shop_get_product_show_sales_count($product_id),
        'stock_type'         => $stock_data['stock_type'],
        'stock_all'          => $stock_data['stock_all'],
        'stock_opts'         => $stock_data['stock_opts'],
        'shipping_type'      => $_configs['shipping_type'], //自动发货还是快递
        'shipping_fee_opt'   => [], //运费规则
        'shipping_title'     => '快递发货', //运费标题
        'shipping_desc'      => '', //运费描述
        'auto_delivery_type' => $_configs['auto_delivery']['type'] ?? '', //自动发货类型：邀请码，卡密等
        'guest_buy'          => zib_shop_product_is_allow_guest_buy($post->ID), //是否允许游客购买
        'email_fill'         => zib_shop_get_product_email_fill_config($post->ID), //是否需要用户填写email
    ];

    $data['main_image_url'] = $data['thumbnail_url'];
    $data['show_mark']      = $data['pay_modo'] === 'points' ? $data['points_mark'] : $data['pay_mark'];
    $data['is_points']      = $data['pay_modo'] === 'points';

    if ($data['shipping_type'] === 'auto') {
        $data['shipping_title'] = !empty($_configs['shipping_delivery_desc']) ? $_configs['shipping_delivery_desc'] : '自动发货';
        if ($data['auto_delivery_type'] == 'invit_code') {
            $data['shipping_title'] .= '<span class="badg badg-sm c-yellow ml6 shrink0">邀请码</span>';
        } elseif ($data['auto_delivery_type'] == 'card_pass') {
            $data['shipping_title'] .= '<span class="badg badg-sm c-yellow ml6 shrink0">卡密</span>';
        }
    } elseif ($data['shipping_type'] === 'manual') {
        $data['shipping_title'] = !empty($_configs['shipping_delivery_desc']) ? $_configs['shipping_delivery_desc'] : '商家发货';
    } elseif ($data['shipping_type'] === 'express') {
        $data['shipping_fee_opt'] = zib_shop_get_product_shipping_fee_config($product_id);

        $desc = $data['shipping_fee_opt']['desc'] ?? '';

        if ($data['shipping_fee_opt']['type'] === 'free') {
            $data['shipping_title'] = '快递发货<span class="badg badg-sm c-blue ml6 shrink0">包邮</span>';
            $data['shipping_desc']  = $desc;
        }

        if ($data['shipping_fee_opt']['type'] === 'fixed') {
            $data['shipping_title'] = '快递发货<span class="ml6 shrink0">运费 ' . $data['pay_mark'] . $data['shipping_fee_opt']['fixed_fee'] . '</span>';
            $data['shipping_desc']  = $desc;
        }

        if ($data['shipping_fee_opt']['type'] === 'amount') {
            $data['shipping_title'] = $desc ?: '快递发货';
            $data['shipping_desc']  = '运费 ' . $data['pay_mark'] . $data['shipping_fee_opt']['amount_fee']['fee'];
            $data['shipping_desc'] .= '，当前商品满' . $data['shipping_fee_opt']['amount_fee']['free_amount'] . '包邮';
        }
    }

    return $data;
}

//获取商家数据
function zib_shop_get_author_vue_data($author_id)
{

    $shop_author_show = (bool) _pz('shop_author_show', true);

    $data = [
        'author_id'      => $author_id,
        'name'           => $shop_author_show ? get_the_author_meta('display_name', $author_id) : '',
        'url'            => $shop_author_show ? zib_shop_get_author_url($author_id) : '',
        'avatar'         => $shop_author_show ? zib_get_avatar_box($author_id, 'avatar-img', false, true) : '',
        'checked_status' => '',
    ];

    return $data;
}

//获取用户数据
function zib_shop_get_user_vue_data($user_id = 0)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $udata = get_userdata($user_id);

    $data = [
        'user_id'            => $user_id,
        'vip_level'          => $user_id ? zib_get_user_vip_level($user_id) : 0,
        'auth'               => $user_id ? zib_is_user_auth($user_id) : false,
        'email'              => $user_id ? esc_attr($udata->user_email) : '',
        'address_lists_data' => zib_shop_get_user_addresses($user_id),
        'address_data'       => zib_shop_get_user_default_address($user_id),
        'points'             => zibpay_get_user_points($user_id),
        'favorite_ajax_url'  => $user_id ? add_query_arg(array('user_id' => $user_id, 'action' => 'author_shop_product', 'status' => 'favorite'), admin_url('admin-ajax.php')) : '',
    ];
    return $data;
}

//获取购物车模态框数据
function zib_shop_get_cart_modal_vue_data($btn_text = '确认选择')
{
    return [
        'cart_submit_btn_text' => $btn_text,
        'prices'               => [
            //价格交给JS处理
            'start_price'          => 0, //初始价格，不变
            'unit_price'           => 0, //单价
            'unit_discount_price'  => 0, //优惠价
            'total_price'          => 0, //小计原价
            'total_discount_price' => 0, //小计优惠价
            'total_discount'       => 0, //优惠金额
        ],
        'discount'             => [],
        'limit_buy'            => [],
        'product_options'      => [],
    ];
}
