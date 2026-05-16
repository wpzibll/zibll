<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-01-31 17:32:58
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统|用户功能函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * 获取用户地址列表
 *
 * @param int $user_id 用户ID
 * @return array 地址列表
 */
function zib_shop_get_user_addresses($user_id = 0, $author_address_context = false)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return array();
    }

    $meta_key  = $author_address_context ? 'author_addresses' : 'shop_addresses';
    $addresses = zib_get_user_meta($user_id, $meta_key, true);
    if (!$addresses || !is_array($addresses)) {
        return array();
    }

    $addresses = array_filter($addresses);
    return $addresses;
}

/**
 * 获取用户默认地址
 *
 * @param int $user_id 用户ID
 * @param bool $author_address_context 是否为作者地址
 * @return array|null 默认地址或null
 */
function zib_shop_get_user_default_address($user_id = 0, $author_address_context = false)
{
    $addresses = zib_shop_get_user_addresses($user_id, $author_address_context);

    if (empty($addresses)) {
        return [];
    }

    // 查找默认地址
    foreach ($addresses as $address) {
        if (!empty($address['is_default'])) {
            return $address;
        }
    }

    // 如果没有默认地址，返回第一个
    return $addresses[0];
}

/**
 * 保存用户地址
 *
 * @param array $address 地址数据
 * @param int $user_id 用户ID
 * @param bool $author_address_context 是否为作者地址
 * @return bool 是否保存成功
 */
function zib_shop_save_user_address($address, $user_id = 0, $author_address_context = false)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    // 验证地址数据
    if (empty($address['name']) || empty($address['phone']) || empty($address['province']) ||
        empty($address['city']) || empty($address['address'])) {
        return false;
    }

    // 获取现有地址
    $addresses = zib_shop_get_user_addresses($user_id, $author_address_context);

    // 生成地址ID
    if (empty($address['id'])) {
        $address['id'] = time() . rand(1000, 9999);
    }

    // 如果设为默认地址，则取消其他地址的默认状态
    if (!empty($address['is_default'])) {
        foreach ($addresses as $key => $addr) {
            $addresses[$key]['is_default'] = false;
        }
    }

    // 更新或添加地址
    $found = false;
    foreach ($addresses as $key => $addr) {
        if ($addr['id'] == $address['id']) {
            $addresses[$key] = $address; //
            $found           = true;
            break;
        }
    }

    if (!$found) {
        //添加到最上面
        $addresses = array_merge(array($address), $addresses);
    }

    // 如果只有一个地址，自动设为默认
    if (count($addresses) === 1) {
        $addresses[0]['is_default'] = true;
    }

    //循环排序 把默认地址排在最上面
    $default_address = null;
    foreach ($addresses as $key => $addr) {
        if ($addr['is_default']) {
            $default_address = $addr;
            unset($addresses[$key]);
        }
    }

    // 把默认地址添加到最上面
    if ($default_address) {
        $addresses = array_merge(array($default_address), $addresses);
    } else {
        //将第一个地址设为默认
        $addresses[0]['is_default'] = true;
    }

    $addresses = array_filter($addresses);

    // 保存地址
    $meta_key = $author_address_context ? 'author_addresses' : 'shop_addresses';
    zib_update_user_meta($user_id, $meta_key, $addresses);

    return $addresses;
}

/**
 * 删除用户地址
 *
 * @param string $address_id 地址ID
 * @param int $user_id 用户ID
 * @return bool 是否删除成功
 */
function zib_shop_delete_user_address($address_id, $user_id = 0, $author_address_context = false)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id || !$address_id) {
        return false;
    }

    $addresses = zib_shop_get_user_addresses($user_id, $author_address_context);

    if (empty($addresses)) {
        return false;
    }

    $found      = false;
    $is_default = false;

    // 查找并删除地址
    foreach ($addresses as $key => $address) {
        if ($address['id'] == $address_id) {
            $is_default = !empty($address['is_default']);
            unset($addresses[$key]);
            $found = true;
            break;
        }
    }

    if (!$found) {
        return false;
    }

    // 重新索引数组
    $addresses = array_values($addresses);

    // 如果删除的是默认地址且还有其他地址，则将第一个地址设为默认
    if ($is_default && !empty($addresses)) {
        $addresses[0]['is_default'] = true;
    }

    // 保存地址
    $meta_key = $author_address_context ? 'author_addresses' : 'shop_addresses';
    return zib_update_user_meta($user_id, $meta_key, $addresses) ? $addresses : false;
}

//获取用户帖子关注的数量
function zib_shop_get_user_favorite_product_count($user_id, $_cut = true)
{
    if (!$user_id) {
        return;
    }

    $cache_num = wp_cache_get($user_id, 'user_favorite_product_count', true);
    if (false !== $cache_num) {
        $count_all = $cache_num;
    } else {
        $favorite_ids = zib_get_user_meta($user_id, 'favorite_product', true);

        if ($favorite_ids) {
            $args = array(
                'post_type'      => 'shop_product',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'paged'          => 0,
                'post__in'       => $favorite_ids,
                'fields'         => 'ids',
            );
            $the_query = new WP_Query($args);

            $count_all = $the_query->found_posts;
            wp_reset_query();
            //添加缓存
            wp_cache_set($user_id, $count_all, 'user_favorite_product_count');
        } else {
            $count_all = '0';
        }
    }

    if ($_cut) {
        return _cut_count($count_all);
    }
    return $count_all;
}

//刷新缓存
function zib_shop_favorite_product_cache_delete($posts_id, $user_id)
{
    wp_cache_delete($user_id, 'user_favorite_product_count');
    zib_shop_get_user_favorite_product_count($user_id);
}
add_action('shop_favorite_product', 'zib_shop_favorite_product_cache_delete', 10, 2);

function zib_shop_get_user_shop_product_lists($user_id = 0, $paged = 1, $orderby = 'date', $post_status = 'publish')
{
    if (!$user_id) {
        return;
    }

    $order = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
    $order = $order === 'ASC' ? 'ASC' : 'DESC';

    $current_user_id = get_current_user_id();
    $ajax_url        = add_query_arg(array('user_id' => $user_id, 'action' => 'author_shop_product', 'order' => $order, 'orderby' => $orderby, 'status' => $post_status), admin_url('admin-ajax.php'));
    $config          = _pz('shop_list_opt', array());
    $posts_per_page  = $config['count'] ?? 12;
    $list_card_args  = $config['list_style'] ?? [];

    $args = array(
        'post_type'      => 'shop_product',
        'author'         => $user_id,
        'post_status'    => $post_status,
        'order'          => $order,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );

    if (!$current_user_id || ($current_user_id != $user_id && !is_super_admin())) {
        $args['post_status'] = 'publish';
    }

    //兼容follow关注的和moderator管理的
    if ('favorite' === $post_status) {
        $args['post_status'] = 'publish';
        unset($args['author']);
        $follow = (array) zib_get_user_meta($user_id, 'favorite_product', true);
        if ($follow) {
            $args['post__in'] = array_reverse($follow);
        } else {
            return zib_get_ajax_null('暂无收藏内容');
        }
    }

    $args      = zib_query_orderby_filter($orderby, $args);
    $new_query = new WP_Query($args);

    if ('favorite' === $post_status) {
        wp_cache_set($user_id, $new_query->found_posts, 'user_favorite_product_count');
    } else {
        wp_cache_set($user_id . '_' . $post_status, $new_query->found_posts, 'post_count_favorite_product');
    }

    $lists = '';
    while ($new_query->have_posts()) {
        $new_query->the_post();
        $lists .= zib_shop_get_product_list_card($list_card_args);
    }
    wp_reset_query();

    if (!$lists && 2 > $paged) {
        $lists = zib_get_ajax_null();
    }
    $paginate = zib_get_ajax_next_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url);

    return $lists . $paginate;
}

/**
 * 获取用户订单数量
 * @param string $status 订单状态 可选值：wait-pay,paid,closed,wait-shipped,wait-receive,wait-evaluate,after-sale
 * @param int $user_id 用户ID
 * @return int 订单数量
 */
function zib_shop_get_user_order_count($status = 'wait-pay', $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    //缓存
    $cache_key = 'user_order_count_' . $user_id . '_' . $status;
    $count     = wp_cache_get($cache_key, 'user_order_data');
    if (false !== $count) {
        return $count;
    }

    $order_type = zib_shop_get_order_type();
    $query_args = [
        'user_id' => $user_id,
        'field'   => 'count',
    ];

    switch ($status) {
        //待支付
        case 'wait-pay':

            //获取距离超时间
            $max_time          = zibpay_get_order_pay_max_time(); //分钟
            $current_timestamp = current_time('timestamp');
            $where_max_time    = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 . ' Second', $current_timestamp));

            $query_args['status'] = 0;
            $query_args['where']  = [
                ['create_time', '>', $where_max_time],
            ];

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
            $query_args['order_type'] = $order_type;
            $query_args['status']     = 1;
            $query_args['meta_query'] = [
                [
                    'key'   => 'shipping_status',
                    'value' => $status == 'wait-receive' ? 1 : 0, //0未发货，1已发货，2已收货
                ],
            ];

            if ($status == 'wait-receive') {
                $max_time                   = _pz('order_receipt_max_day', 15) ?: 15; //默认15天
                $current_timestamp          = current_time('timestamp');
                $where_max_time             = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 * 60 * 24 . ' Second', $current_timestamp));
                $query_args['meta_query'][] = [
                    'key'     => 'shipping_time',
                    'value'   => $where_max_time,
                    'compare' => '>',
                ];
            }

            break;

        //评价状态
        case 'wait-evaluate':
            $query_args['order_type'] = $order_type;
            $query_args['status']     = 1;
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

            $max_time                   = _pz('shop_comment_max_day', 15) ?: 15; //默认15天
            $current_timestamp          = current_time('timestamp');
            $where_max_time             = date('Y-m-d H:i:s', strtotime('-' . $max_time * 60 * 60 * 24 . ' Second', $current_timestamp));
            $query_args['meta_query'][] = [
                'key'     => 'shipping_time',
                'value'   => $where_max_time,
                'compare' => '>',
            ];
            break;

        //售后/退款
        case 'after-sale':
            $query_args['order_type']        = $order_type;
            $query_args['status']            = [-2, 1];
            $query_args['after_sale_status'] = [1, 2];

            break;

        default:
            break;
    }

    $db_order = zibpay::order_query($query_args);

    //缓存
    wp_cache_set($cache_key, $db_order['total'] ?? 0, 'user_order_data');
    return $db_order['total'] ?? 0;
}

//更新订单状态缓存
function zib_shop_user_order_count_cache_delete($order_id, $meta_key)
{
    if (in_array($meta_key, ['shipping_status', 'comment_status', 'after_sale_status'])) {
        $order = zibpay::get_order($order_id, 'user_id');
        if ($order['user_id']) {
            switch ($meta_key) {
                case 'shipping_status':
                    wp_cache_delete('user_order_count_' . $order['user_id'] . '_wait-shipped', 'user_order_data');
                    wp_cache_delete('user_order_count_' . $order['user_id'] . '_wait-receive', 'user_order_data');
                    break;
                case 'comment_status':
                    wp_cache_delete('user_order_count_' . $order['user_id'] . '_wait-evaluate', 'user_order_data');
                    break;
                case 'after_sale_status':
                    wp_cache_delete('user_order_count_' . $order['user_id'] . '_after-sale', 'user_order_data');
                    break;
            }
        }
    }
}
add_action('save_order_meta', 'zib_shop_user_order_count_cache_delete', 10, 2);
