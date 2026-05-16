<?php
/*
* @Author: Qinver
* @Url: zibll.com
* @Date: 2025-02-16 21:10:36
 * @LastEditTime : 2026-04-11 16:42:46
* @Email: 770349780@qq.com
* @Project: Zibll子比主题
* @Description: 商城功能|请求处理
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
*/

//ajax添加购物车
function zib_shop_ajax_cart_add()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(['msg' => '请先登录', 'code' => 'no_logged']);
    }

    $product_id  = !empty($_POST['product_id']) ? (int) $_POST['product_id'] : 0; //商品ID
    $count       = !empty($_POST['count']) ? (int) $_POST['count'] : 1; //数量
    $options_key = !empty($_POST['options_active']) ? $_POST['options_active'] : ''; //选项

    if (!$product_id) {
        zib_send_json_error(['msg' => '商品ID不能为空', 'code' => 'no_product_id']);
    }

    $product = get_post($product_id);
    if (!$product || $product->post_type !== 'shop_product') {
        zib_send_json_error(['msg' => '商品不存在', 'code' => 'no_product']);
    }

    //判断是否可以加入购物车
    $can_add = zib_shop_can_add_cart($product, $options_key);
    if (!$can_add) {
        zib_send_json_error(['msg' => '商品不能加入购物车', 'code' => 'no_product']);
    }

    //执行添加购物车
    $items = zib_shop_cart_add($product_id, $options_key, $count, $user_id);
    $count = zib_shop_get_cart_count($user_id, $items);

    return zib_send_json_success(['cart_items' => $items, 'count' => $count]);
}
add_action('wp_ajax_cart_add', 'zib_shop_ajax_cart_add');
add_action('wp_ajax_nopriv_cart_add', 'zib_shop_ajax_cart_add');

//ajax更新购物车
function zib_shop_ajax_update_cart()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error(['msg' => '请先登录', 'code' => 'no_logged']);
    }

    $cart_data = isset($_POST['cart_data']) ? $_POST['cart_data'] : array();

    //执行更新购物车
    $items = zib_shop_cart_update($cart_data, $user_id);
    $count = zib_shop_get_cart_count($user_id, $items);

    return zib_send_json_success(['msg' => '已更新购物车', 'cart_items' => $items, 'count' => $count]);
}
add_action('wp_ajax_update_cart', 'zib_shop_ajax_update_cart');
add_action('wp_ajax_nopriv_update_cart', 'zib_shop_ajax_update_cart');

//ajax获取相关推荐
function zib_shop_ajax_single_related()
{
    if (!_pz('shop_single_related_s', true)) {
        return;
    }

    $post_id = $_REQUEST['post_id'] ?? 0;
    if (!$post_id) {
        return;
    }

    $post = get_post($post_id);
    if (!$post) {
        return;
    }

    $ajax_url       = zib_get_admin_ajax_url('shop_single_related', ['post_id' => $post_id]);
    $related_config = _pz('shop_single_related_opt', array());
    $type           = $related_config['type'] ?: ['cat', 'discount', 'tag'];
    $orderby        = $related_config['orderby'] ?: 'views';
    $count          = $related_config['count'] ?: 12;
    $paginate       = $related_config['paginate'] ?: false;
    $paged          = zib_get_the_paged();

    $tax_query = array();
    if (in_array('cat', $type)) {
        $cat_id = get_the_terms($post, 'shop_cat');
        if ($cat_id) {
            $tax_query[] = array(
                'taxonomy' => 'shop_cat',
                'field'    => 'term_id',
                'terms'    => array_column((array) $cat_id, 'term_id'),
            );
        }
    }
    if (in_array('discount', $type)) {
        $discount_id = get_the_terms($post, 'shop_discount');
        if ($discount_id) {
            $tax_query[] = array(
                'taxonomy' => 'shop_discount',
                'field'    => 'term_id',
                'terms'    => array_column((array) $discount_id, 'term_id'),
            );
        }
    }
    if (in_array('tag', $type)) {
        $tag_id = get_the_terms($post, 'shop_tag');
        if ($tag_id) {
            $tax_query[] = array(
                'taxonomy' => 'shop_tag',
                'field'    => 'term_id',
                'terms'    => array_column((array) $tag_id, 'term_id'),
            );
        }
    }

    if (isset($tax_query[0])) {
        $tax_query['relation'] = 'OR';
    }

    $query_args = array(
        'ignore_sticky_posts' => 1, //忽略置顶
        'post_type'           => 'shop_product',
        'post_status'         => 'publish',
        'tax_query'           => $tax_query,
        'posts_per_page'      => $count,
        'post__not_in'        => array($post_id),
    );
    //排序
    $query_args = zib_query_orderby_filter($orderby, $query_args);

    //分页
    if ($paginate) {
        $query_args['paged'] = $paged;
    } else {
        $query_args['no_found_rows'] = true; //不查询分页需要的总数量
    }

    $list_card_args = $related_config['list_style'] ?? [];

    $query = new WP_Query($query_args);
    $lists = '';
    while ($query->have_posts()) {
        $query->the_post();
        $lists .= zib_shop_get_product_list_card($list_card_args);
    }
    wp_reset_query();

    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null('暂无内容', 10);
    }

    //分页paginate
    if ($paginate === 'ajax') {
        $lists .= zib_get_ajax_next_paginate($query->found_posts, $paged, $count, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no');
    } elseif ($paginate === 'number') {
        $lists .= zib_get_ajax_number_paginate($query->found_posts, $paged, $count, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'paged');
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    zib_ajax_send_ajaxpager($lists, false);
}
add_action('wp_ajax_shop_single_related', 'zib_shop_ajax_single_related');
add_action('wp_ajax_nopriv_shop_single_related', 'zib_shop_ajax_single_related');

//获取订单确认的模态框
function zib_shop_ajax_shop_confirm_modal()
{
    $is_cart  = !empty($_POST['is_cart']);
    $products = $_POST['products'] ?? array();
    $user_id  = get_current_user_id();

    if (empty($products)) {
        zib_send_json_error(['code' => 'products_error', 'msg' => '未选择商品']);
    }

    //获取订单确认数据
    $confirm_data = zib_shop_get_confirm_data($products);
    if (!$confirm_data) {
        zib_send_json_error(['code' => 'products_error', 'msg' => '未选择商品，或商品不存在']);
    }

    //登录判断
    if (!$user_id) {
        foreach ($confirm_data['product_data'] as $product_data) {
            if (!$product_data['guest_buy']) {
                zib_send_json_error(['code' => 'login_error', 'msg' => '请先登录']);
            }
        }
    }

    if ($confirm_data['is_mix']) {
        zib_send_json_error(['code' => 'mix_error', 'msg' => '积分商品和现金商品不能同时支付，请重新选择']);
    }

    //商品参数基本判断：库存，限购
    if (!empty($confirm_data['error_data'])) {
        foreach ($confirm_data['error_data'] as $error_data) {
            $error_data['msg']  = $error_data['error_msg'] ?? '商品参数错误';
            $error_data['code'] = $error_data['error_type'] ?? 'product_error';
            zib_send_json_error($error_data);
        }
    }

    $v_confirm_html = '';
    if (empty($_POST['no_html'])) {
        $v_confirm_html = file_get_contents(get_theme_file_path(ZIB_SHOP_REQUIRE_URI . 'template/v-confirm-modal.html'));
    }

    zib_send_json_success(['html' => $v_confirm_html, 'vue_data' => $confirm_data]);
}
add_action('wp_ajax_shop_confirm_modal', 'zib_shop_ajax_shop_confirm_modal');
add_action('wp_ajax_nopriv_shop_confirm_modal', 'zib_shop_ajax_shop_confirm_modal');

//用户下单
function zib_shop_ajax_submit_order()
{
    $user_id  = get_current_user_id();
    $products = $_POST['products'] ?? array();
    if (empty($products)) {
        zib_send_json_error(['code' => 'products_error', 'msg' => '未选择商品']);
    }

    $confirm_data = zib_shop_get_confirm_data($products);
    if (!$confirm_data) {
        zib_send_json_error(['code' => 'products_error', 'msg' => '未选择商品，或商品不存在']);
    }

    //登录判断
    if (!$user_id) {
        foreach ($confirm_data['product_data'] as $product_data) {
            if (!$product_data['guest_buy']) {
                zib_send_json_error(['code' => 'login_error', 'msg' => '请先登录']);
            }
        }
    }

    //判断是否混合支付
    if ($confirm_data['is_mix']) {
        zib_send_json_error(['code' => 'mix_error', 'msg' => '积分商品和现金商品不能同时支付，请返回购物车重新选择']);
    }

    //判断用户地址
    if ($confirm_data['shipping_has_express']) {
        if (empty($_POST['address_data']['name']) || empty($_POST['address_data']['phone']) || empty($_POST['address_data']['address'])) {
            zib_send_json_error(['code' => 'address_error', 'msg' => '请先设置收货地址']);
        }
    }

    //判断邮箱
    if ($confirm_data['shipping_has_auto'] && $confirm_data['email_fill'] !== 'off') {
        if (empty($_POST['user_email']) && $confirm_data['email_fill'] === 'required') {
            zib_send_json_error(['code' => 'email_error', 'msg' => '请输入邮箱', 'email_fill' => $confirm_data['email_fill']]);
        }

        //邮箱格式判断
        if (!empty($_POST['user_email']) && !filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            zib_send_json_error(['code' => 'email_error', 'msg' => '邮箱格式错误', 'email_fill' => $confirm_data['email_fill']]);
        }
    }

    //商品参数基本判断：库存，限购
    if (!empty($confirm_data['error_data'])) {
        foreach ($confirm_data['error_data'] as $error_data) {
            $error_data['msg']  = $error_data['error_msg'] ?? '商品参数错误';
            $error_data['code'] = $error_data['error_type'] ?? 'product_error';
            zib_send_json_error($error_data);
        }
    }

    //判断积分
    $is_points      = $confirm_data['pay_modo'] === 'points';
    $payment_method = $is_points ? 'points' : $_POST['payment_method'] ?? '';
    $payment_price  = $is_points ? (int) $confirm_data['total_data']['pay_points'] : zib_floatval_round($confirm_data['total_data']['pay_price']);
    $_post_price    = $is_points ? $_POST['points'] ?? 0 : $_POST['price'] ?? 0;
    $order_type     = zib_shop_get_order_type();

    //订单金额判断
    if (round((float) $_post_price, 2) !== round((float) $payment_price, 2)) {
        zib_send_json_error(['code' => 'price_error', 'msg' => '订单金额发生变化，请重新提交']);
    }

    if (!$is_points && (float) $payment_price > 0) {
        //支付方式选择
        if (!$payment_method) {
            zib_send_json_error(['code' => 'payment_method_error', 'msg' => '请选择支付方式']);
        }

        //支付方式合法判断
        $pay_methods = zib_shop_get_payment_methods();
        if (!isset($pay_methods[$payment_method])) {
            zib_send_json_error(['code' => 'payment_method_error', 'msg' => '支付方式错误']);
        }
    }

    //准备下单数据
    if ((float) $payment_price <= 0) {
        //金额为0，则使用余额或积分支付
        $payment_method = $is_points ? 'points' : 'balance';
        $payment_price  = 0;
    }

    //准备数据，并下单
    //创建一个新的支付数据，用于后续的订单创建
    $order_data     = [];
    $zibpay_payment = zibpay::add_payment([
        'method' => $payment_method,
        'price'  => $payment_price,
    ]);

    if (!$zibpay_payment) {
        zib_send_json_error(['code' => 'add_payment_error', 'msg' => '支付数据创建失败']);
    }

    $zibpay_payment_id = $zibpay_payment['id']; //支付数据ID
    foreach ($confirm_data['item_data'] as $author_id => $product_data_item) {
        foreach ($product_data_item as $product_id => $opt_items) {
            foreach ($opt_items as $opt_key => $item_data_item) {
                //判断必填项目
                if ($item_data_item['user_required']) {
                    $user_required_error = [];
                    foreach ($item_data_item['user_required'] as $key => $user_required_item) {
                        if (!$user_required_item['value']) {
                            $user_required_error[] = $user_required_item['name'];
                        }
                        unset($item_data_item['user_required'][$key]['key']);
                        unset($item_data_item['user_required'][$key]['desc']);
                    }

                    if ($user_required_error) {
                        zib_send_json_error(['code' => 'user_required_error', 'msg' => '请填写' . implode(',', $user_required_error)]);
                    }
                }
                //必填项目结束
                $__order_price = zib_shop_format_price($item_data_item['prices']['total_price'], $is_points);
                $__pay_price   = zib_shop_format_price($item_data_item['prices']['pay_price'], $is_points);

                $__pay_detail                   = [];
                $__pay_detail['payment_method'] = $payment_method;
                $__pay_detail[$payment_method]  = $__pay_price;

                if (!empty($item_data_item['prices']['total_discount'])) {
                    $__pay_detail['discount'] = (string) zib_shop_format_price($item_data_item['prices']['total_discount'], $is_points);
                }
                if ($is_points) {
                    $__pay_detail['points'] = $__pay_price;
                }
                $__mate_order_data = $item_data_item;
                //移出不需要的数据
                foreach (['stock_all', 'limit_buy'] as $key) {
                    if (isset($__mate_order_data[$key])) {
                        unset($__mate_order_data[$key]);
                    }
                }

                //发货信息
                $__mate_order_data['consignee'] = [];
                if ($item_data_item['shipping_type'] === 'auto') {
                    if (!empty($_POST['user_email'])) {
                        $__mate_order_data['consignee'] = [ //收货人
                            'email' => $_POST['user_email'],
                        ];
                    }
                } elseif ($item_data_item['shipping_type'] === 'express') {
                    $__mate_order_data['consignee'] = [ //收货地址
                        'address_data' => $_POST['address_data'],
                    ];
                }

                //推广返佣
                $referrer_id  = '';
                $rebate_price = '';
                //积分商品没有佣金
                if (!$is_points) {
                    $rebate_data  = zib_shop_get_order_rebate_data($item_data_item['product_id'], $user_id, $__pay_price);
                    $referrer_id  = $rebate_data['referrer_id'];
                    $rebate_price = $rebate_data['rebate_price'];
                }

                $__meta = [
                    'order_data' => $__mate_order_data, //订单额外数据
                    'pay_modo'   => $is_points ? 'points' : 'price',
                ];

                $__order_data = [
                    'count'        => $__mate_order_data['count'] ?? 1,
                    'post_id'      => $item_data_item['product_id'],
                    'post_author'  => $author_id,
                    'user_id'      => $user_id,
                    'product_id'   => $item_data_item['options_active_str'],
                    'order_type'   => $order_type,
                    'order_price'  => $__order_price, //原价
                    'pay_price'    => $__pay_price, //支付金额，优惠价
                    'payment_id'   => $zibpay_payment_id, //支付ID
                    'referrer_id'  => $referrer_id, //推广人ID
                    'rebate_price' => $rebate_price, //推广佣金
                    'pay_detail'   => $__pay_detail,
                    'meta'         => $__meta,
                ];

                //下单
                $add_order_data = zibpay::add_order($__order_data);
                if (is_wp_error($add_order_data)) {
                    zib_send_json_error(['code' => $add_order_data->get_error_code(), 'msg' => $add_order_data->get_error_message()]);
                }

                if ($add_order_data) {
                    $order_data[] = [
                        'order_id'   => $add_order_data['id'],
                        'payment_id' => $zibpay_payment_id,
                        'order_num'  => $add_order_data['order_num'],
                        'pay_price'  => $add_order_data['pay_price'],
                        'product_id' => $item_data_item['product_id'], //移出购物车依赖数据
                        'opt_key'    => $item_data_item['options_active_str'], //移出购物车依赖数据
                    ];
                } else {
                    zib_send_json_error(['code' => 'add_order_error', 'msg' => '订单创建失败']);
                }
            }
        }
    }

    //订单创建完成
    if ($confirm_data['config']['is_cart']) {
        //如果是从购物车过来的，则移出购物车
        zib_shop_cart_remove_multi($order_data, $user_id);
    }

    $send_data = [
        'order_data'   => $order_data,
        'payment_data' => $zibpay_payment,
    ];

    zib_send_json_success($send_data);
}
add_action('wp_ajax_shop_submit_order', 'zib_shop_ajax_submit_order');
add_action('wp_ajax_nopriv_shop_submit_order', 'zib_shop_ajax_submit_order');

/**
 * AJAX获取用户地址列表
 */
function zib_shop_ajax_get_user_addresses()
{
    $user_id = get_current_user_id();

    if (!$user_id) {
        zib_send_json_error('请先登录');
    }

    $addresses = zib_shop_get_user_addresses($user_id);

    wp_send_json_success($addresses);
}
add_action('wp_ajax_shop_get_user_addresses', 'zib_shop_ajax_get_user_addresses');

/**
 * AJAX保存用户地址
 */
function zib_shop_ajax_save_user_address()
{

    $author_address_context       = isset($_POST['action']) && $_POST['action'] == 'shop_save_author_address' ? true : false;
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        zib_send_json_error('请先登录');
    }

    $user_id = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    if ($user_id) {
        if ($user_id != $current_user_id && !current_user_can('administrator')) {
            zib_send_json_error('无权限操作');
        }
    } else {
        $user_id = $current_user_id;
    }

    $address = isset($_POST['address']) ? $_POST['address'] : array();

    if (empty($address)) {
        zib_send_json_error('地址数据不能为空');
    }

    // 过滤和验证数据
    $address = array(
        'id'         => isset($address['id']) ? sanitize_text_field($address['id']) : '',
        'name'       => isset($address['name']) ? sanitize_text_field($address['name']) : '',
        'phone'      => isset($address['phone']) ? sanitize_text_field($address['phone']) : '',
        'province'   => isset($address['province']) ? sanitize_text_field($address['province']) : '',
        'city'       => isset($address['city']) ? sanitize_text_field($address['city']) : '',
        'county'     => isset($address['county']) ? sanitize_text_field($address['county']) : '',
        'address'    => isset($address['address']) ? sanitize_text_field($address['address']) : '',
        'tag'        => isset($address['tag']) ? sanitize_text_field($address['tag']) : '',
        'is_default' => !empty($address['is_default']) && $address['is_default'] == 'true',
    );

    // 验证必填字段
    if (empty($address['name'])) {
        zib_send_json_error('请输入收货人姓名');
    }

    if (empty($address['phone'])) {
        zib_send_json_error('请输入手机号码');
    }

    if (empty($address['province']) || empty($address['city'])) {
        zib_send_json_error('地区信息不完整');
    }

    if (empty($address['address'])) {
        zib_send_json_error('请输入详细地址');
    }

    // 保存地址
    $result = zib_shop_save_user_address($address, $user_id, $author_address_context);

    zib_send_json_success(['msg' => '保存成功', 'data' => $result]);
}
add_action('wp_ajax_shop_save_user_address', 'zib_shop_ajax_save_user_address');
add_action('wp_ajax_shop_save_author_address', 'zib_shop_ajax_save_user_address');

/**
 * AJAX删除用户地址
 */
function zib_shop_ajax_delete_user_address()
{
    $author_address_context       = isset($_POST['action']) && $_POST['action'] == 'shop_delete_author_address' ? true : false;
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        zib_send_json_error('请先登录');
    }

    $user_id = !empty($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    if ($user_id) {
        if ($user_id != $current_user_id && !current_user_can('administrator')) {
            zib_send_json_error('无权限操作');
        }
    } else {
        $user_id = $current_user_id;
    }

    $address_id = isset($_POST['address_id']) ? sanitize_text_field($_POST['address_id']) : '';

    if (empty($address_id)) {
        zib_send_json_error('地址ID不能为空');
    }

    $result = zib_shop_delete_user_address($address_id, $user_id, $author_address_context);
    zib_send_json_success(['msg' => '删除成功', 'data' => $result]);
}
add_action('wp_ajax_shop_delete_user_address', 'zib_shop_ajax_delete_user_address');
add_action('wp_ajax_shop_delete_author_address', 'zib_shop_ajax_delete_user_address');

//评价的模态框
function zib_shop_ajax_comment_modal()
{

    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', '请先登录');
    }

    $order_id = isset($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    if (!$order_id) {
        zib_ajax_notice_modal('danger', '订单ID不能为空');
    }

    $comment_status = zib_shop_get_order_comment_status($order_id);
    if ($comment_status == 1) {
        zib_ajax_notice_modal('danger', '订单已评价');
    }
    if ($comment_status == 2) {
        zib_ajax_notice_modal('danger', '当前订单存在售后，无法评价');
    }

    //评分组件
    $score_html = '';
    $score_html .= '<div class="mb10 shop-score-box flex ac">';
    $score_html .= '<div class="em09 muted-2-color score-title">商品评分</div><div class="score-icon em16 mr10 ml20" style=" height: 30px; "></div><div class="score-text em09 muted-2-color"></div>';
    $score_html .= '<input class="score-input" type="hidden" name="score[product]" value="0">';
    $score_html .= '</div>';
    if (zib_shop_get_order_delivery_type($order_id) === 'express') {
        $score_html .= '<div class="mb10 shop-score-box flex ac">';
        $score_html .= '<div class="em09 muted-2-color score-title">物流服务</div><div class="score-icon em16 mr10 ml20" style=" height: 30px; "></div><div class="score-text em09 muted-2-color"></div>';
        $score_html .= '<input class="score-input" type="hidden" name="score[shipping]" value="0">';
        $score_html .= '</div>';
    }
    $score_html .= '<div class="mb10 shop-score-box flex ac">';
    $score_html .= '<div class="em09 muted-2-color score-title">服务态度</div><div class="score-icon em16 mr10 ml20" style=" height: 30px;"></div><div class="score-text em09 muted-2-color"></div>';
    $score_html .= '<input class="score-input" type="hidden" name="score[service]" value="0">';
    $score_html .= '</div>';

    //图片上传
    $img_num  = _pz('shop_comment_img_num', 6);
    $img_html = '';
    if ($img_num > 0) {
        $img_html = '<div class="form-upload mb10">';
        $img_html .= '<div class="em09 muted-2-color mb6">图片</div>';
        $img_html .= '<div class="preview">'; //正方形
        $img_html .= '<div class="add"></div>';
        $img_html .= '</div>';
        $img_html .= '<input class="hide" type="file" zibupload="image_upload" multiple="multiple" multiple_max="' . $img_num . '" accept="image/gif,image/jpeg,image/jpg,image/png" name="image" action="image_upload" multiple="true">';
        $img_html .= '</div>';
    }

    $con = '<div class="mb20">' . $score_html . '</div>';
    $con .= '<div class="mb20">';
    $con .= '<textarea class="form-control" name="comment" tabindex="2" placeholder="' . _pz('shop_comment_placeholder', '请输入评价内容') . '" rows="4" autoheight="true" maxheight="188"></textarea>';
    $con .= '</div>';
    $con .= $img_html;

    $hidden_html = wp_nonce_field('shop_order_comment', '_wpnonce', false, false);
    $hidden_html .= '<input type="hidden" name="action" value="shop_order_comment">';
    $hidden_html .= '<input type="hidden" name="order_id" value="' . $order_id . '">';

    $footer = '<div class="but-average modal-buts"><button type="button" data-dismiss="modal" class="but">取消</button>';
    $footer .= '<button class="but c-blue" zibupload="submit" zibupload-nomust="true"><i class="fa fa-check" aria-hidden="true"></i>确认提交</button>';
    $footer .= $hidden_html;
    $footer .= '</div>';

    $title     = '<b>评价订单</b>';
    $over_time = zib_shop_get_order_comment_over_time($order_id);
    if ($over_time) {
        $time_remaining = zib_time_to_c($over_time);
        $title .= '<span class="px12 muted-2-color ml6"><span int-second="1" data-over-text="交易已关闭" data-countdown="' . $time_remaining . '"></span>后自动好评</span>';
    }

    $html = '<div class="touch border-title">' . $title . '</div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $html .= '<form class="order-comment-from form-upload">';
    $html .= '<div>' . $con . '</div>';
    $html .= $footer;
    $html .= '</form>';

    echo $html;
    exit;
}
add_action('wp_ajax_order_comment_modal', 'zib_shop_ajax_comment_modal');
add_action('wp_ajax_nopriv_order_comment_modal', 'zib_shop_ajax_comment_modal');

//评价订单
function zib_shop_order_comment()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $user = wp_get_current_user();
    if (!$user->exists()) {
        zib_send_json_error(['code' => 'login_error', 'msg' => '请先登录']);
    }

    $user_id  = $user->ID;
    $order_id = isset($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    if (!$order_id) {
        zib_send_json_error(['code' => 'order_id_error', 'msg' => '订单ID不能为空']);
    }

    $order = zibpay::get_order($order_id);
    if (!$order || $order['user_id'] != $user_id || !$order['post_id'] || $order['order_type'] != zib_shop_get_order_type()) {
        zib_send_json_error(['code' => 'order_error', 'msg' => '订单数据异常，请重试']);
    }

    //判断当前商品是否开启了评论
    $product_id = $order['post_id'];
    if (!zib_shop_product_is_open_comment($product_id)) {
        zib_send_json_error(['code' => 'product_error', 'msg' => '商品未开启评论']);
    }

    //判断订单是否已评价
    $comment_status = zib_shop_get_order_comment_status($order_id);
    if ($comment_status == -1) {
        zib_send_json_error(['code' => 'comment_error', 'msg' => '商品评价功能已关闭']);
    }
    if ($comment_status == 1) {
        zib_send_json_error(['code' => 'comment_error', 'msg' => '订单已评价，请勿重复评价']);
    }
    if ($comment_status == 2) {
        zib_send_json_error(['code' => 'comment_error', 'msg' => '当前订单存在售后，无法评价']);
    }

    $comment = isset($_POST['comment']) ? wp_filter_kses($_POST['comment']) : '';
    $score   = isset($_POST['score']) ? $_POST['score'] : array();

    if (zib_new_strlen($comment) < 4) {
        //    zib_send_json_error(['code' => 'comment_error', 'msg' => '评价内容过短']);
    }

    //评分组件
    $score_product = $score['product'] ?? 0;
    $score_service = $score['service'] ?? 0;

    if ($score_product < 1 || $score_service < 1) {
        zib_send_json_error(['code' => 'score_error', 'msg' => '请对商品进行评分']);
    }

    if (isset($score['shipping'])) {
        $score_shipping = $score['shipping'] ?? 0;
        if ($score_shipping < 1) {
            zib_send_json_error(['code' => 'score_error', 'msg' => '请对物流服务进行评分']);
        }
    }

    $img_ids = array();
    //图片处理
    if (!empty($_FILES['file'])) {
        //开始上传图像
        $img_ids = zib_php_upload('file', 0, false);

        if (!empty($img_ids['error'])) {
            zib_send_json_error($img_ids['msg']);
        }

        if (!is_array($img_ids)) {
            $img_ids = array($img_ids);
        }
    }

    $data = [
        'comment'    => $comment,
        'score_data' => $score,
        'img_ids'    => $img_ids,
    ];
    $comment_handle = zib_shop_order_comment_handle($order, $data);

    if (is_wp_error($comment_handle)) {
        zib_send_json_error(['code' => 'comment_error', 'msg' => $comment_handle->get_error_message()]);
    }

    $comment_handle['code']   = 'success';
    $comment_handle['reload'] = true;
    $comment_handle['msg']    = '评价成功';

    zib_send_json_success($comment_handle);
}
add_action('wp_ajax_shop_order_comment', 'zib_shop_order_comment');
add_action('wp_ajax_nopriv_shop_order_comment', 'zib_shop_order_comment');

//获取订单物流信息数据
function zib_shop_ajax_shipping_express_data()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }
    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    //判断订单类型
    if ($order['order_type'] != zib_shop_get_order_type()) {
        zib_send_json_error('当前订单类型不支持查询物流信息');
    }

    $express_data = zib_shop_get_express_data($order);

    $data = [
        'express_data' => $express_data ?: [],
    ];

    zib_send_json_success($data);
}
add_action('wp_ajax_shipping_express_data', 'zib_shop_ajax_shipping_express_data');

//确认收货模态框
function zib_shop_ajax_order_receive_confirm_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //判断订单类型
    if ($order['order_type'] != zib_shop_get_order_type()) {
        zib_ajax_modal_error('当前订单类型不支持确认收货');
    }

    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('权限不足');
    }

    //判断订单状态
    if ($order['status'] != 1) {
        zib_ajax_modal_error('订单状态不支持确认收货');
    }

    //判断是否已确认收货
    if (zib_shop_get_order_shipping_status($order_id) == 2) {
        zib_ajax_modal_error('订单已确认收货');
    }

    $header = zib_get_modal_colorful_header('jb-yellow', '<i class="fa fa-check-circle-o" aria-hidden="true"></i>', '确认收到货了吗？');

    $html = '';
    $html .= '<div class="text-center c-yellow" style="padding: 20px">';
    $html .= '为了保障您的权益，请收到商品后确认无误后，再确认收货';
    $html .= '</div>';

    $html .= '<div class="mt20 but-average">
        <input type="hidden" name="action" value="order_receive_confirm">
        <input type="hidden" name="order_id" value="' . $order_id . '">
        ' . zib_nonce_field('order_receive_confirm') . '
        <button class="but jb-yellow padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认收货</button>
    </div>';

    echo $header . '<form>' . $html . '</form>';
    exit;
}
add_action('wp_ajax_order_receive_confirm_modal', 'zib_shop_ajax_order_receive_confirm_modal');

//确认收货
function zib_shop_ajax_order_receive_confirm()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    //判断订单类型
    if ($order['order_type'] != zib_shop_get_order_type()) {
        zib_send_json_error('当前订单类型不支持确认收货');
    }

    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    //判断订单状态
    if ($order['status'] != 1) {
        zib_send_json_error('订单状态不支持确认收货');
    }

    //判断是否已确认收货
    if (zib_shop_get_order_shipping_status($order_id) == 2) {
        zib_send_json_success(['reload' => true, 'msg' => '已确认收货']);
    }

    //更新订单状态
    zib_shop_order_receive_confirm($order_id, 'user');

    zib_send_json_success(['reload' => true, 'goto' => zib_get_user_center_url('order', 'wait-evaluate'), 'msg' => '确认收货成功']);
}
add_action('wp_ajax_order_receive_confirm', 'zib_shop_ajax_order_receive_confirm');

//订单物流信息模态框
function zib_shop_ajax_order_express_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $shipping_type   = $order_meta_data['shipping_type'] ?? '';

    if ($shipping_type !== 'express') {
        zib_ajax_modal_error('当前订单不是快递发货订单，无法查询物流信息');
    }

    $express_data = zib_shop_get_express_data($order);
    if (isset($express_data['traces'])) {
        $express_data['traces'] = zib_shop_get_default_express_traces($order);
    }

    $express_data['express_company_name'] = $order_meta_data['shipping_data']['express_company_name'] ?? '';
    $express_data['express_number']       = $order_meta_data['shipping_data']['express_number'] ?? '';
    $express_data['address_data']         = $order_meta_data['consignee']['address_data'] ?? [];

    $html = zib_shop_get_express_modal($express_data);
    echo $html;
    exit;
}
add_action('wp_ajax_order_express_modal', 'zib_shop_ajax_order_express_modal');

//订单物流信息模态框
function zib_shop_ajax_order_after_sale_express_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $type            = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'user_return';
    $type            = $type === 'user_return' ? 'user_return' : 'author_return';
    $express_data    = zib_shop_get_after_sale_express_data($order_id, $type);
    $after_sale_data = zibpay::get_meta($order['id'], 'order_data.after_sale_data');

    if ($type == 'user_return') {
        $express_data['express_company_name'] = $after_sale_data['user_return_data']['express_company_name'] ?? '';
        $express_data['express_number']       = $after_sale_data['user_return_data']['express_number'] ?? '';
        $express_data['address_data']         = $after_sale_data['return_address'] ?? [];
    } else {
        $express_data['express_company_name'] = $after_sale_data['author_return_data']['express_company_name'] ?? '';
        $express_data['express_number']       = $after_sale_data['author_return_data']['express_number'] ?? '';
        $express_data['address_data']         = $after_sale_data['author_return_data']['address'] ?? [];
    }

    $html = zib_shop_get_express_modal($express_data);
    echo $html;
    exit;
}
add_action('wp_ajax_order_after_sale_express_modal', 'zib_shop_ajax_order_after_sale_express_modal');

//修改收货地址模态框
function zib_shop_ajax_order_modify_address_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $shipping_type   = $order_meta_data['shipping_type'] ?? '';

    //必须为已支付
    if ($order['status'] != '1') {
        zib_ajax_modal_error('当前订单不是已支付订单，无法修改收货地址');
    }

    //必须为快递发货
    if ($shipping_type !== 'express') {
        zib_ajax_modal_error('当前订单不是快递发货订单，无法修改收货地址');
    }

    //已发货的不显示
    $shipping_status = zib_shop_get_order_shipping_status($order['id']);
    if ($shipping_status > 0) {
        zib_ajax_modal_error('当前订单已发货，无法修改收货地址');
    }

    //有售后不显示
    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if (in_array($after_sale_status, [1, 2])) {
        zib_ajax_modal_error('当前订单有售后，无法修改收货地址');
    }

    $header = '<div class="border-title touch"><div class="flex jc"><b>修改收货地址</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';

    //修改收货地址
    $html = '';
    //显示当前收货地址
    $address = $order_meta_data['consignee']['address_data'] ?? [];
    if ($address) {
        $html .= '<div class="address-box zib-widget flex at relative">
                    <div class="flex1">
                        <div class="mb6"><span class="mr6">' . $address['name'] . '</span><span class="">' . $address['phone'] . '</span></div>
                        <div class="muted-2-color em09">' . $address['city'] . $address['address'] . '</div>
                    </div>
                    <span class="badg badg-sm c-blue abs right-top">当前地址</span>
                </div>';
    }

    //查找正在进行中的收货地址修改申请
    $modifying_data = (array) zib_shop_get_order_modify_addressing_data($order_id);
    if ($modifying_data) {
        $new_address = $modifying_data['meta']['new_address'] ?? [];

        $html .= '<div class="address-box zib-widget flex at relative">
        <div class="flex1">
            <div class="mb6"><span class="mr6">' . $new_address['name'] . '</span><span class="">' . $new_address['phone'] . '</span></div>
            <div class="muted-2-color em09">' . $new_address['city'] . $new_address['address'] . '</div>
        </div>
        <span class="badg badg-sm c-yellow abs right-top">新地址</span>
    </div>';
        $html .= '<div class="muted-box muted-2-color mb20 c-yellow">你提交的地址修改申请正在处理中，请耐心等待</div>';
        $service_btn = zib_shop_get_author_contact_link($order['post_author'], 'but c-yellow', '联系客服');
        //撤销修改
        $cancel_btn = zib_shop_get_order_modify_address_cancel_link($modifying_data['id'], 'but c-blue', '撤销修改');
        $submit_btn = '<div class="modal-buts but-average">' . $service_btn . $cancel_btn . '</div>';
        $html .= $submit_btn;
        echo $header . $html;
        exit;
    }

    //显示选择新地址
    $html .= '
    <div class="order-address-box zib-widget relative">
        <div class="address-box-body" v-if="new_address.id">
            <span class="badg badg-sm c-yellow abs right-top">新地址</span>
            <div class="flex ac jsb pointer" @click="showAddressModal">
                <div class="address-content flex1 mr10">
                    <div class="address-detail flex ac">
                        <div class="name-phone mb6">
                            <span class="name">{{ new_address.name }}</span>
                            <span class="phone ml6">{{ new_address.phone }}</span>
                            <span class="badg badg-sm c-blue ml6" v-if="new_address.tag">{{ new_address.tag }}</span>
                        </div>
                    </div>
                    <div class="muted-2-color em09">{{ new_address.county + new_address.address }}</div>
                </div>
                <i class="fa fa-angle-right em12"></i>
            </div>
            <input type="hidden" name="new_address" :value="JSON.stringify(new_address)">
        </div>
        <div class="address-box-body" v-else>
            <div class="flex muted-color jsb ac pointer" @click="showAddressModal">
                <div class="icon-header mr20 shrink0">
                    <i class="fa fa-map-marker mr6"></i>
                    <span class="">选择新的收货地址</span>
                </div>
                <div class="flex ac overflow-hidden">
                    <div class="text-ellipsis muted-3-color"></div>
                    <i class="fa fa-angle-right em12 ml6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="muted-box muted-2-color mb20">如需要修改收货地址，请选择新的收货地址，修改收货地址需商家确认，如有疑问请联系商家</div>
';

    $service_btn = zib_shop_get_author_contact_link($order['post_author'], 'but c-yellow', '联系客服');
    $submit_btn  = '<div class="modal-buts but-average">' . $service_btn . '<button type="submit" class="but c-blue wp-ajax-submit">确认提交</button></div>';

    //显示保存按钮
    $html .= '<div>
        <input type="hidden" name="action" value="order_modify_address">
        <input type="hidden" name="order_id" value="' . $order_id . '">
        ' . zib_nonce_field('order_modify_address') . '
    </div>' . $submit_btn;

    $config = [
        'order_id'           => $order_id,
        'address_lists_data' => zib_shop_get_user_addresses($order['user_id']),
    ];

    echo $header . '<form id="order_modify_address"  v-cloak @vue:mounted="mounted" v-config=\'' . esc_attr(json_encode($config)) . '\'>' . $html . '</form>';
    exit;
}
add_action('wp_ajax_order_modify_address_modal', 'zib_shop_ajax_order_modify_address_modal');

//修改收货地址
function zib_shop_ajax_order_modify_address()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    $new_address = !empty($_REQUEST['new_address']) ? json_decode(wp_unslash($_REQUEST['new_address']), true) : [];
    if (empty($new_address['id'])) {
        zib_send_json_error(['msg' => '请选择新的收货地址', 'new_address' => $new_address]);
    }

    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('无权限操作');
    }

    //判断订单状态
    if ($order['status'] != '1') {
        zib_send_json_error('当前订单不是已支付订单，无法修改收货地址');
    }

    //判断订单类型
    if ($order['order_type'] != zib_shop_get_order_type()) {
        zib_send_json_error('当前订单类型不支持修改收货地址');
    }

    //必须为快递发货
    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $shipping_type   = $order_meta_data['shipping_type'] ?? '';

    if ($shipping_type !== 'express') {
        zib_send_json_error('当前订单不是快递发货订单，无法修改收货地址');
    }

    //已发货的不显示
    $shipping_status = zib_shop_get_order_shipping_status($order['id']);
    if ($shipping_status > 0) {
        zib_send_json_error('当前订单已发货，无法修改收货地址');
    }

    //判断订单是否有售后
    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if (in_array($after_sale_status, [1, 2])) {
        zib_send_json_error('当前订单有售后，无法修改收货地址');
    }

    //新旧地址是否相同
    $old_address = $order_meta_data['consignee']['address_data'] ?? [];
    if ($old_address['name'] == $new_address['name'] && $old_address['phone'] == $new_address['phone'] && $old_address['county'] == $new_address['county'] && $old_address['city'] == $new_address['city'] && $old_address['address'] == $new_address['address']) {
        zib_send_json_error('新旧地址相同，无需修改');
    }

    $process_url         = zib_get_user_center_url('order', 'modify-address');
    $product_id          = $order['post_id'];
    $post_data           = get_post($product_id);
    $options_active_name = $order_meta_data['options_active_name'] ?? '';
    $post_title          = $order_meta_data['product_title'] ?? '';

    if ($post_data) {
        $post_title = zib_str_cut($post_data->post_title, 0, 20, '...');
    }

    //创建消息通知
    $title   = '用户申请修改订单收货地址，请及时处理';
    $msg_con = '用户' . zib_get_user_name_link($order['user_id']) . '，正在申请修改订单收货地址，请及时处理<br>';
    $msg_con .= '订单号：' . $order['order_num'] . '<br>';
    $msg_con .= '商品：<a href="' . get_the_permalink($product_id) . '">' . $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']') . '</a><br>';

    $msg_con .= '新地址：' . $new_address['name'] . ' ' . $new_address['phone'] . ' ' . $new_address['county'] . $new_address['city'] . $new_address['address'] . '<br>';
    $msg_con .= '提交时间：' . current_time('Y-m-d H:i:s') . '<br><br>';
    $msg_con .= '您可以点击下方按钮快速处理此申请<br>';
    $msg_con .= zib_shop_get_modify_address_apply_link(null, 'but jb-blue padding-lg', '立即处理');
    $msg_args = array(
        'send_user'    => $order['user_id'],
        'receive_user' => $order['post_author'] ?: 'admin',
        'type'         => 'order_modify_address',
        'title'        => $title,
        'content'      => $msg_con,
        'status'       => 0,
        'other'        => 'order_id_' . $order_id, //必须，用于查找正在进行的收货地址修改申请
        'meta'         => array(
            'new_address' => $new_address,
            'order_id'    => $order['id'],
            'time'        => current_time('Y-m-d H:i:s'),
        ),
    );
    $add_msg = ZibMsg::add($msg_args);
    if (!$add_msg) {
        zib_send_json_error('提交失败，请稍候再试');
    }

    zib_send_json_success(['hide_modal' => true, 'msg' => '地址修改信息已提交，等待商家处理']);
}
add_action('wp_ajax_order_modify_address', 'zib_shop_ajax_order_modify_address');

//商家处理地址修改的模态框
function zib_shop_ajax_modify_address_apply_modal()
{
    $msg_id  = !empty($_REQUEST['msg_id']) ? (int) $_REQUEST['msg_id'] : 0;
    $user_id = get_current_user_id();

    $where = [
        'type'   => 'order_modify_address',
        'status' => 0,
    ];
    if ($msg_id) {
        $where['other'] = 'order_id_' . $msg_id;
    }

    if (!is_super_admin($user_id)) {
        $where['receive_user'] = $user_id;
    }

    $msg = ZibMsg::get($where, 'id', 0, 100);

    if (!$msg) {
        zib_ajax_modal_error('没有待处理的地址修改申请');
    }

    $lists = '';
    $html  = '';
    foreach ($msg as $item) {
        $item        = (array) $item;
        $meta        = maybe_unserialize($item['meta']);
        $new_address = $meta['new_address'] ?? [];
        $order_id    = $meta['order_id'] ?? 0;
        $order       = zibpay::get_order($order_id);
        if (!$order) {
            continue;
        }

        $product_box     = zib_shop_user_order_product_box($order);
        $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
        $old_address     = $order_meta_data['consignee']['address_data'] ?? [];
        $_item_html      = '';
        if ($old_address) {
            $_item_html .= '
            <div class="flex at jsb mb10">
                <div class="mr20 muted-2-color">旧地址</div>
                <div class="text-right">
                    <div class=""> <span class="font-bold">' . $old_address['name'] . '</span><span class="phone ml6">' . $old_address['phone'] . '</span></div>
                    <div class="muted-color em09">' . $old_address['county'] . $old_address['city'] . $old_address['address'] . '</div>
                </div>
            </div>';
        }

        $_item_html .= '
        <div class="flex at jsb mb10">
            <div class="mr20 muted-2-color">新地址</div>
            <div class="text-right">
                <div class=""> <span class="font-bold">' . $new_address['name'] . '</span><span class="phone ml6">' . $new_address['phone'] . '</span></div>
                <div class="muted-color em09">' . $new_address['county'] . $new_address['city'] . $new_address['address'] . '</div>
            </div>
        </div>';

        $_item_html .= '
        <div class="flex at jsb mb10">
            <div class="mr20 muted-2-color">处理方式</div>
            <div class="text-right">
                <div><label class="badg p2-10 pointer mr6"><input type="radio" checked="checked" name="data[' . $item['id'] . '][status]" value="1"> 批准</label><label class="badg p2-10 pointer"><input type="radio" name="data[' . $item['id'] . '][status]" value="2"> 驳回</label></div>
            </div>
        </div>

        <input type="text" class="form-control" name="data[' . $item['id'] . '][msg]" tabindex="1" value="" placeholder="点击输入处理备注或驳回原因">
        ';

        $lists .= '<div class="muted-box mb10">' . $product_box . $_item_html . '</div>';
    }

    $html .= '<div class="address-lists mini-scrollbar scroll-y max-vh7">' . $lists . '</div>';
    $html .= '<input type="hidden" name="action" value="modify_address_apply">';
    $html .= zib_nonce_field('modify_address_apply');
    $html .= '<div class="modal-buts but-average"><button type="submit" class="but c-blue wp-ajax-submit" data-confirm="确认处理所有地址修改申请？">确认处理</button></div>';
    $header = '<div class="border-title touch"><div class="flex jc"><b>处理地址修改申请</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    echo $header . '<form id="modify_address_apply">' . $html . '</form>';
    exit;
}
add_action('wp_ajax_modify_address_apply_modal', 'zib_shop_ajax_modify_address_apply_modal');

//处理地址修改申请
function zib_shop_ajax_modify_address_apply()
{
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $data = !empty($_POST['data']) ? $_POST['data'] : [];
    if (empty($data)) {
        zib_send_json_error('处理数据不能为空');
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_send_json_error('请先登录');
    }

    $is_super_admin = is_super_admin($user_id);

    $error = [];
    foreach ($data as $key => $item) {
        $msg_id = $key;
        $status = $item['status'] ?? 1;
        $status = $status == 2 ? 2 : 1;
        $msg    = !empty($item['msg']) ? strip_tags(trim($item['msg'])) : '';

        $msg_data = (array) ZibMsg::get_row(['id' => $msg_id, 'type' => 'order_modify_address']);

        if (!$msg_data) {
            $error[] = '编号为' . $msg_id . '的地址修改申请不存在或用户已撤销此申请';
            continue;
        }

        if ($msg_data['post_author'] != $user_id && !$is_super_admin) {
            $error[] = '编号为' . $msg_id . '的地址修改申请无权限处理';
            continue;
        }

        $new_address = $msg_data['meta']['new_address'] ?? [];
        $order_id    = $msg_data['meta']['order_id'] ?? 0;
        $order       = zibpay::get_order($order_id);

        if (!$order) {
            $error[] = '编号为' . $msg_id . '的订单不存在';
            continue;
        }

        //处理订单收货地址
        $new_title = '商家已驳回您的订单地址修改申请';
        if ($status == 1) {
            //同意
            $new_title                                    = '订单地址已修改完成';
            $order_meta_data                              = zibpay::get_meta($order_id, 'order_data');
            $order_meta_data['consignee']['address_data'] = $new_address;
            zibpay::update_meta($order_id, 'order_data', $order_meta_data);
        }

        //更新当前消息状态
        $update_msg = ZibMsg::set_status($msg_id, $status);
        if (!$update_msg) {
            $error[] = '更新当前消息状态失败';
            continue;
        }

        //发送回复消息
        $product_id          = $order['post_id'];
        $post_data           = get_post($product_id);
        $options_active_name = $order_meta_data['options_active_name'] ?? '';
        $post_title          = $order_meta_data['product_title'] ?? '';

        if ($post_data) {
            $post_title = zib_str_cut($post_data->post_title, 0, 20, '...');
        }

        //发送新消息
        $new_msg_con = '商家已' . ($status == 1 ? '同意' : '驳回') . '您的订单地址修改申请<br>';
        $new_msg_con .= '订单号：' . $order['order_num'] . '<br>';
        $new_msg_con .= '商品：<a href="' . get_the_permalink($product_id) . '">' . $post_title . (!$options_active_name ? '' : '[' . $options_active_name . ']') . '</a><br>';
        $new_msg_con .= '新地址：' . $new_address['name'] . ' ' . $new_address['phone'] . ' ' . $new_address['county'] . $new_address['city'] . $new_address['address'] . '<br>';
        $new_msg_con .= $msg ? '处理备注：' . $msg . '<br>' : '';
        $new_msg_con .= '处理时间：' . current_time('Y-m-d H:i:s') . '<br><br>';
        $new_msg_args = array(
            'send_user'    => $order['post_author'],
            'receive_user' => $order['user_id'],
            'type'         => 'order_modify_address_reply',
            'title'        => $new_title,
            'content'      => $new_msg_con,
            'parent'       => $msg_id,
            'meta'         => array(
                'new_address' => $new_address,
                'order_id'    => $order['id'],
                'time'        => current_time('Y-m-d H:i:s'),
            ),
        );
        ZibMsg::add($new_msg_args);
    }

    if ($error) {
        zib_send_json_error(['msg' => implode('<br>', $error), 'hide_modal' => true]);
    }

    zib_send_json_success(['msg' => '处理成功', 'hide_modal' => true]);
}
add_action('wp_ajax_modify_address_apply', 'zib_shop_ajax_modify_address_apply');

//撤销修改收货地址
function zib_shop_ajax_modify_address_cancel()
{
    $msg_id = !empty($_REQUEST['msg_id']) ? (int) $_REQUEST['msg_id'] : 0;
    $msg    = (array) ZibMsg::get_row(['id' => $msg_id]);
    if (!$msg) {
        zib_send_json_error('撤销失败，请稍候再试');
    }

    //判断状态，如果状态为1，则说明已经处理了
    if ($msg['status'] == 1) {
        zib_send_json_error(['msg' => '当前申请商家已处理，如有疑问请联系商家', 'hide_modal' => true]);
    }

    //判断权限
    if ($msg['send_user'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('无权限操作');
    }

    //执行删除消息
    $delete_msg = ZibMsg::delete(['id' => $msg_id]);
    if (!$delete_msg) {
        zib_send_json_error('撤销失败，请稍候再试');
    }

    zib_send_json_success(['msg' => '撤销成功', 'hide_modal' => true]);
}
add_action('wp_ajax_modify_address_cancel', 'zib_shop_ajax_modify_address_cancel');

//订单自动发货内容显示框
function zib_shop_ajax_order_delivery_content_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }
    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $shipping_type   = $order_meta_data['shipping_type'] ?? '';

    if ($shipping_type !== 'auto') {
        return zib_shop_ajax_order_express_modal();
    }

    $shipping_data      = $order_meta_data['shipping_data'] ?? [];
    $delivery_type      = $shipping_data['delivery_type'] ?? '';
    $delivery_html      = $shipping_data['delivery_content'] ?? '';
    $delivery_time      = $shipping_data['delivery_time'] ?? '';
    $delivery_type_name = zib_shop_get_delivery_type_name($delivery_type);

    //自动发货内容显示
    $content = '';
    $content .= '<div class="muted-box mb10 mt10" style="white-space:pre-wrap;">' . $delivery_html . '</div>';
    $content .= '<div class="flex ac jsb mb10"><div class="mr20 muted-2-color">发货时间</div><div class="muted-color">' . $delivery_time . '</div></div>';
    $content .= '<div class="flex ac jsb mb20"><div class="mr20 muted-2-color">商品类型</div><div class="muted-color"><span class="badg badg-sm ml6 c-green">' . ($delivery_type_name ?: $delivery_type) . '</span></div></div>';

    $header = '<div class="border-title touch"><div class="flex jc"><b>发货信息</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $footer = '<div class="modal-buts but-average"><a type="button" data-dismiss="modal" class="but" href="javascript:;">确认</a></div>';

    echo $header . '<div class="mini-scrollbar scroll-y max-vh7">' . $content . '</div>' . $footer;
    exit;
}
add_action('wp_ajax_order_delivery_content_modal', 'zib_shop_ajax_order_delivery_content_modal');

//订单优惠明细模态框
function zib_shop_ajax_order_discount_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //判断权限
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $discount_hit    = $order_meta_data['discount_hit'] ?? [];

    $discount_lists = '';
    $total_discount = 0;
    foreach ($discount_hit as $discount) {
        if (empty($discount['preferential_amount'])) {
            //跳过
            continue;
        }
        $total_discount += $discount['preferential_amount'];
        $_name = $discount['name'] ?? '';

        $discount_data = zib_shop_get_discount_data($discount['id']);
        $badge         = $discount_data['small_badge'] ?? $_name;
        $title         = $discount_data['name'] ?? $_name;
        $desc          = $discount_data['desc'] ?? '';
        $price         = $discount['preferential_amount'] ?? 0;
        $desc          = $desc ? '<div class="opacity8 px12 mt3 text-ellipsis">' . $desc . '</div>' : '';
        $link          = $discount_data['link'] ?? '';
        $link          = false;
        $title         = $link ? '<a class="c-red" href="' . $link . '" target="_blank">' . $title . '<i class="fa fa-angle-right ml6"></i></a>' : $title;

        $discount_lists .= '<div class="discount-card muted-box mb10">
                            <div class="small-badge">' . $badge . '</div>
                                <div class="flex ab jsb">
                                    <div class="overflow-hidden">
                                        <div class="title">' . $title . '</div>' . $desc . '
                                    </div>
                                    <div class="flex0 ml20">-' . zib_floatval_round($price) . '</div>
                                </div>
                        </div>';

    }

    $discount_lists .= '<div class="flex muted-box">
                    <div class="text-ellipsis flex1 mr20">共计优惠</div>
                    <div class="desc c-red">-' . zib_floatval_round($total_discount) . '</div>
                </div>';

    $header = '<div class="border-title touch"><div class="flex jc"><b>订单优惠明细</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';

    $html = $header . '<div class="mini-scrollbar scroll-y max-vh7">' . $discount_lists . '</div>';

    echo $html;
    exit;
}
add_action('wp_ajax_order_discount_modal', 'zib_shop_ajax_order_discount_modal');

//订单赠品明细模态框
function zib_shop_ajax_order_gift_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $discount_hit    = $order_meta_data['discount_hit'] ?? [];

    $gift_cards = '';
    foreach ($discount_hit as $discount) {
        if ($discount['discount_type'] !== 'gift' || empty($discount['gift_data'])) {
            //跳过
            continue;
        }

        $_name         = $discount['name'] ?? '';
        $discount_data = zib_shop_get_discount_data($discount['id']);
        $badge         = $discount_data['small_badge'] ?? $_name;
        $title         = $discount_data['name'] ?? $_name;
        $desc          = $discount_data['desc'] ?? '';
        $desc          = $desc ? '<div class="opacity8 px12 mt3 text-ellipsis">' . $desc . '</div>' : '';
        $link          = $discount_data['link'] ?? '';

        $gift_data       = $discount['gift_data'] ?? [];
        $gift_cards_html = '';

        foreach ($gift_data as $gift) {
            switch ($gift['gift_type']) {
                case 'vip_1':
                    $time_text = $gift['vip_time'] === 'Permanent' ? '永久' : $gift['vip_time'] . '天';
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">' . $gift['vip_1_name'] . '</div><div class="muted-2-color"> ' . $time_text . '</div></div>';
                    break;
                case 'vip_2':
                    $time_text = $gift['vip_time'] === 'Permanent' ? '永久' : $gift['vip_time'] . '天';
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">' . $gift['vip_2_name'] . '</div><div class="muted-2-color"> ' . $time_text . '</div></div>';
                    break;
                case 'auth':
                    $auth_desc = '<div class=""> ' . $gift['auth_info']['name'] . '</div>';
                    $auth_desc .= isset($gift['auth_info']['desc']) ? '<div class="px12"> ' . $gift['auth_info']['desc'] . '</div>' : '';
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">认证资格</div><div class="muted-3-color"> ' . $auth_desc . '</div></div>';
                    break;
                case 'level_integral':
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">经验值</div><div class="muted-2-color"> ' . $gift['level_integral'] . '</div></div>';
                    break;
                case 'points':
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">积分</div><div class="muted-2-color"> ' . $gift['points'] . '</div></div>';
                    break;
                case 'product':
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">商品</div><div class="muted-2-color"> ' . $gift['product_id'] . '</div></div>';
                    break;
                case 'other':
                    $gift_cards_html .= '<div class="gift-card-item flex mt6"><div class="gift-name">' . $gift['other_info']['name'] . '</div><div class="muted-2-color"> ' . $gift['other_info']['desc'] . '</div></div>';
                    break;
            }
        }

        $gift_info = '<div class="gift-card-info flex1"><div class="title">' . $title . '</div>' . $desc . '</div>';

        $gift_info = $link ? '<a class="flex jsb" href="' . $link . '" target="_blank">' . $gift_info . '<i class="fa fa-angle-right em12 ml20 mt10"></i></a>' : $gift_info;

        $gift_cards .= '
        <div class="gift-card muted-box mb10 relative">
            <div class="small-badge">' . $badge . '</div>
            ' . $gift_info . '
            <div class="gift-card-item">
                ' . $gift_cards_html . '
            </div>
        </div>
        ';

    }

    $header = '<div class="border-title touch"><div class="flex jc"><b>订单赠品明细</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';

    $html = $header . '<div class="mini-scrollbar scroll-y max-vh5">' . $gift_cards . '</div>';

    echo $html;
    exit;
}
add_action('wp_ajax_order_gift_modal', 'zib_shop_ajax_order_gift_modal');

//售后申请模态框
function zib_shop_ajax_order_after_sale_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);
    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    $user_id = $order['user_id'];
    //权限判断
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_ajax_modal_error('无权限操作');
    }

    $order_data               = zibpay::get_meta($order['id'], 'order_data');
    $after_sale_status        = zib_shop_get_order_after_sale_status($order['id']);
    $is_points                = $order_data['pay_modo'] === 'points';
    $_mark                    = '<span class="pay-mark px12">' . ($is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark()) . '</span>';
    $total_price              = zibpay_get_order_pay_detail_lists($order);
    $shipping_status          = zib_shop_get_order_shipping_status($order['id']); //0:未发货 1:已发货 2:已收货
    $return_express_over_time = 0;

    if ($after_sale_status === 2) {
        $return_express_over_time = zib_shop_get_order_after_sale_return_express_over_time($order, $order_data['after_sale_data']);
        if ($return_express_over_time === 'over') {
            $after_sale_status = 4;
            $order_data        = zibpay::get_meta($order['id'], 'order_data');
        }
    }

    if ($shipping_status == 0) {
        $shipping_status_name = '<span class="badg badg-sm c-yellow">待发货</span>';
        $product_time         = '<div class="flex ac jsb mt10"><div class="flex0 mr10 muted-2-color">付款时间</div><div class="flex0"><span>' . $order['pay_time'] . '</span></span></div></div>';
    }

    if ($shipping_status == 1) {
        $shipping_status_name = '<span class="badg badg-sm c-yellow">待收货</span>';
        $product_time         = '<div class="flex ac jsb mt10"><div class="flex0 mr10 muted-2-color">发货时间</div><div class="flex0"><span>' . $order_data['shipping_data']['delivery_time'] . '</span></span></div></div>';
    }

    if ($shipping_status == 2) {
        $shipping_status_name = '<span class="badg badg-sm c-green">已完成</span>';
        $product_time         = '<div class="flex ac jsb mt10"><div class="flex0 mr10 muted-2-color">收货时间</div><div class="flex0"><span>' . $order_data['shipping_data']['receive_time'] . '</span></span></div></div>';
    }

    $product_box = '<div class="muted-box mb10 padding-10">' . zib_shop_user_order_product_box($order, '') . '
    <div class="flex ac jsb mt10"><div class="flex0 mr10 muted-2-color">付款明细</div><div class="flex0"><span>' . $total_price . '</span></span></div></div>
    <div class="flex ac jsb mt10"><div class="flex0 mr10 muted-2-color">订单状态</div><div class="flex0"><span>' . $shipping_status_name . '</span></span></div></div>
    ' . $product_time . '
    </div>';

    if (!$after_sale_status || $after_sale_status > 2) {
        //有售后记录
        $after_sale_record = $order_data['after_sale_record'] ?? [];
        if ($after_sale_record) {
            $after_sale_record_count      = count($after_sale_record);
            $_after_sale_record_link_text = '<div class="flex0 mr10 muted-2-color">售后记录</div><div class="flex0"><span>已有' . $after_sale_record_count . '条记录<i class="fa fa-angle-right em12 ml6"></i></span></div>';
            $product_box .= zib_shop_get_order_after_sale_record_link($order, 'muted-box mb10 padding-10 flex ac jsb', $_after_sale_record_link_text);
        }

        //无售后，申请售后
        $after_sale_opt  = zib_shop_get_order_after_sale_opt($order);
        $after_sale_desc = $after_sale_opt['desc'] ?? '';
        $after_sale_desc = $after_sale_desc ? '<div class="muted-box muted-2-color padding-10 mb10 em09">' . $after_sale_desc . '</div>' : '';

        $can_apply  = $after_sale_opt['can_apply'];
        $type_radio = '';
        $type_con   = '';
        foreach ($can_apply as $apply) {
            if (in_array($apply, ['refund', 'refund_return'])) {
                $reason       = zib_shop_get_after_sale_type_reason($apply);
                $reason_radio = '';
                foreach ($reason as $item) {
                    $reason_radio .= '<label><input type="radio" name="' . $apply . '_reason" value="' . $item['t'] . '"><span class="p2-10 mr6 but but-radio">' . $item['t'] . '</span></label>';
                }
                $type_con .= '<div class="form-but-radio mb10" data-controller="type" data-condition="==" data-value="' . $apply . '" style="display: none;"><div class="mb6 muted-2-color em09">请选择选择原因</div><div class="flex1">' . $reason_radio . '</div></div>';
            }

            $type_radio .= '<label><input type="radio" name="type" value="' . $apply . '"><span class="p2-10 mr6 but but-radio">' . zib_shop_get_after_sale_type_name($apply) . '</span></label>';
        }

        //退款金额
        if ($shipping_status > 0) {
            $limit_max = $order_data['prices']['pay_price'] ?? 0;
            $type_con .= '<div class="mb10 relative" data-controller="type" data-condition="any" data-value="refund,insured_price" style="display: none;"><div class="flex ab"><div class="muted-color mb6 flex0">请输入退款金额</div><input limit-min="0" warning-min="最低可设置：1$" limit-max="' . $limit_max . '" warning-max="最高可设置：1$" type="number" name="price" value="" style="padding: 0;" class="line-form-input em16 key-color text-right"><i class="line-form-line"></i></div></div>';
        } else {
            $type_con .= '<input type="hidden" name="price" value="' . $order_data['prices']['pay_price'] . '">';
        }

        //收款配置
        $rewards_img_urls    = zib_get_user_rewards_img_urls($user_id);
        $weixin              = $rewards_img_urls['weixin'];
        $alipay              = $rewards_img_urls['alipay'];
        $collection_text_img = '';
        if ($weixin) {
            $collection_text_img .= '<img class="img-icon ml6" src="' . $weixin . '" alt="微信收款">';
        }
        if ($alipay) {
            $collection_text_img .= '<img class="img-icon ml6" src="' . $alipay . '" alt="支付宝收款">';
        }

        if (!$collection_text_img) {
            $collection_text_img = '<span class="c-red">请先配置收款信息</span>';
        }

        $collection_text = '<div class="flex0 muted-color">收款信息</div><div class="flex ac"><span>' . $collection_text_img . '<i class="fa fa-angle-right em12 ml10"></i></span></div>';
        $collection_btn  = zib_get_user_collection_set_link('padding-h6 flex ac jsb', $collection_text, true);
        //积分和余额付款的不显示
        if (!in_array($order['pay_type'], ['points', 'balance'])) {
            $type_con .= '<div class="mb20" data-controller="type" data-condition="any" data-value="refund,insured_price,refund_return" style="display: none;border-bottom: 1px solid var(--main-border-color);">' . $collection_btn . '</div>';
        }

        //申请备注
        $type_con .= '<div class="mb10"><div class="mb6 muted-2-color em09">售后留言</div><textarea class="form-control" name="remark" tabindex="2" placeholder="请输入售后理由、说明等需要告知商家的信息" rows="2"></textarea><div class="px12 c-yellow mt3" data-controller="type" data-condition="==" data-value="replacement" style="display: none;">请详细描述您需要更换的商品选项及参数</div></div>';

        //售后说明
        $type_con .= $after_sale_desc;

        //联系客服按钮加替提交按钮
        $service_btn = zib_shop_get_author_contact_link($order['post_author'], 'but c-yellow', '联系客服');
        $submit_btn  = '<div class="modal-buts but-average">' . $service_btn . '<button type="submit" class="but c-blue wp-ajax-submit">提交申请</button></div>';
        //添加隐藏参数
        $type_con .= '<input type="hidden" name="action" value="order_after_sale_apply">
        <input type="hidden" name="order_id" value="' . $order['id'] . '">';
        $type_con .= wp_nonce_field('order_after_sale_apply', '_wpnonce', false, false);

        if (!$type_radio) {
            zib_ajax_modal_error('当前订单不支持申请售后');
        }

        $type_radio = '<div class="form-but-radio mb10"><div class="mb6 muted-2-color em09">请选择售后类型</div><div class="flex1">' . $type_radio . '</div></div>';
        $content    = '<form class="dependency-box"><div class="mini-scrollbar scroll-y max-vh7">' . $product_box . $type_radio . $type_con . '</div>' . $submit_btn . '</form>';

        $header = '<div class="border-title touch"><div class="flex jc"><b>申请售后</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
        $html   = $header . $content;
        echo $html;
        exit;
    }

    if ($after_sale_status === 1) {
        $after_sale_data = $order_data['after_sale_data'] ?? [];
        $type            = $after_sale_data['type'] ?? '';
        $price           = $after_sale_data['price'] ?? 0;
        $reason          = $after_sale_data['reason'] ?? '';
        $remark          = $after_sale_data['remark'] ?? '';
        $user_time       = $after_sale_data['user_apply_time'] ?? '';

        // 售后申请已提交UI
        $type_name = zib_shop_get_after_sale_type_name($type);
        $content   = '<div class="text-center">';
        $content .= '<div class="muted-2-color mb20">您的' . $type_name . '申请已提交，正在等待商家处理</div>';

        // 申请详情
        $content .= '<div class="muted-box padding-10 text-left mb20">';
        $content .= '<div class="flex ac jsb"><span class="muted-2-color">售后类型：</span><span class="c-blue">' . $type_name . '</span></div>';
        if ($price) {
            $content .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">退款金额：</span><span class="c-red">' . $price . '</span></div>';
        }
        if ($reason) {
            $content .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">申请原因：</span><span>' . $reason . '</span></div>';
        }
        if ($remark) {
            $content .= '<div class="flex jsb mt10"><span class="muted-2-color flex0 mr10">申请备注：</span><span>' . $remark . '</span></div>';
        }
        $content .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">申请时间：</span><span>' . $user_time . '</span></div>';
        $content .= '</div>';

        // 底部按钮
        $service_btn = zib_shop_get_author_contact_link($order['post_author'], 'but c-yellow', '联系商家');
        $cancel_btn  = zib_shop_get_order_after_sale_cancel_link($order, 'but c-blue', '取消申请') ?: '<button type="button" class="but c-blue" data-dismiss="modal">关闭</button>';
        $content .= '<div class="mt20 modal-buts but-average">' . $service_btn . $cancel_btn . '</div>';
        $content .= '</div>';

        $header = '<div class="border-title touch"><div class="flex jc"><b>售后申请详情</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';

        $header = zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-check-circle-o"></i>', '售后申请已提交');

        $html = $header . $content;
        echo $html;
        exit;
    }

    if ($after_sale_status === 2) {
        //商家已同意：
        //退货退款：用户填写退货快递信息
        //换货：用户填写换货快递信息
        //保修：用户填写保修快递信息

        $after_sale_data = $order_data['after_sale_data'] ?? [];
        $type            = $after_sale_data['type'] ?? '';
        $price           = $after_sale_data['price'] ?? 0;
        $reason          = $after_sale_data['reason'] ?? '';
        $remark          = $after_sale_data['remark'] ?? '';
        $user_time       = $after_sale_data['user_apply_time'] ?? '';
        $progress        = zib_shop_get_order_after_sale_progress($after_sale_data);

        // 通用内容准备
        $type_name     = zib_shop_get_after_sale_type_name($type);
        $progress_name = zib_shop_get_order_after_sale_progress_name($progress);

        // 申请详情 - 通用部分
        $info_box = '<div class="muted-box padding-10 text-left mb20">';
        $info_box .= '<div class="flex ac jsb"><span class="muted-2-color">售后类型：</span><span class="c-blue">' . $type_name . '</span></div>';

        // 退款金额（如果有）
        if ($price) {
            $info_box .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">退款金额：</span><span class="c-red">' . $price . '</span></div>';
        }

        // 申请原因和备注
        if ($reason) {
            $info_box .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">申请原因：</span><span>' . $reason . '</span></div>';
        }

        $info_box .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">申请时间：</span><span>' . $user_time . '</span></div>';

        if ($remark) {
            $info_box .= '<div class="flex jsb mt10"><span class="muted-2-color flex0 mr10">申请备注：</span><span>' . $remark . '</span></div>';
        }

        if (!empty($after_sale_data['author_remark'])) {
            $info_box .= '<div class="flex jsb mt10"><span class="muted-2-color flex0 mr10">商家备注：</span><span>' . $after_sale_data['author_remark'] . '</span></div>';
        }

        $info_box .= '<div class="flex ac jsb mt10"><span class="muted-2-color flex0 mr10">商家同意时间：</span><span>' . ($after_sale_data['author_handle_time'] ?? '') . '</span></div>';

        // 退货地址信息
        if ($progress == 1 && !empty($after_sale_data['return_address'])) {
            $address        = $after_sale_data['return_address'];
            $clipboard_text = $address['name'] . ' ' . $address['phone'] . ' ' . $address['province'] . $address['city'] . $address['county'] . $address['address'];
            $info_box .= '<div class="flex jsb mt10"><span class="muted-2-color flex0 mr10">退货地址：</span>
                            <div class="flex1 text-right">
                                <div class="mb6">' . $address['name'] . ' ' . $address['phone'] . '<a href="javascript:;" class="c-blue icon-spot" data-clipboard-tag="地址信息" data-clipboard-text="' . $clipboard_text . '">复制</a></div>
                                <div class="mb6">' . $address['province'] . $address['city'] . $address['county'] . $address['address'] . '</div>
                            </div>
            </div>'; //
        }

        $info_box .= '</div>'; //通用内容结束

        //填写快递的信息
        if ($progress == 1) {
            // 快递公司选择
            $express_companies      = zib_shop_get_express_companies_data();
            $express_company_option = '<option value="">请选择</option>';
            foreach ($express_companies as $express_company) {
                $express_company_option .= '<option value="' . $express_company . '">' . $express_company . '</option>';
            }

            $input_box = '
            <div class="mb10 muted-2-color em09"><i class="fa fa-truck mr6"></i>请填写退货快递信息</div>
            <div class="muted-box padding-10 mb10" style="padding: 0 10px;">
                <div class="relative padding-h10">
                    <div class="flex ac">
                        <div class="muted-color flex0 mr10">快递单号</div><input type="text" name="express_number" value="" style="padding: 0;" class="line-form-input key-color text-right">
                        <i class="line-form-line"></i>
                    </div>
                </div>
                <div class="relative padding-h10">
                    <div class="flex ac">
                        <div class="muted-color flex0 mr10">快递公司</div>
                        <select name="express_company_name" class="line-form-input key-color text-right" style="padding: 0;">' . $express_company_option . '</select>
                        <i class="line-form-line"></i>
                    </div>
                </div>
                <div class="relative padding-h10">
                    <div class="flex ac">
                        <div class="muted-color flex0 mr10">发货备注</div><input type="text" name="return_remark" value="" style="padding: 0;" class="line-form-input key-color text-right">
                        <i class="line-form-line"></i>
                    </div>
                </div>
            </div>';
            $input_box .= '<input type="hidden" name="action" value="order_after_sale_return_express">
                            <input type="hidden" name="order_id" value="' . $order['id'] . '">';
            $input_box .= wp_nonce_field('order_after_sale_return_express', '_wpnonce', false, false);
        }

        // 快递信息（进度2显示）
        if ($progress == 2) {
            $user_return_data = $after_sale_data['user_return_data'];
            $express_data     = $user_return_data['express_data'] ?? [];
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

                $express_number       = $user_return_data['express_number'];
                $express_company_name = $user_return_data['express_company_name'];
                $return_remark        = $user_return_data['return_remark'];
                $context              = '<div class="text-ellipsis em09 muted-2-color mt3">' . $after_sale_data['user_return_time'] . '已发货' . ($return_remark ? '，备注：' . $return_remark : '') . '</div>';
                $_express             = '<div class="flex muted-color mb10">
                                            <div class="icon-header mr10"><i class="fa fa-truck fa-fw"></i></div>
                                            <div class="">
                                                <div><b class="font-bold mr10">' . $express_company_name . '</b>' . $express_number . '</div>
                                                ' . $context . '
                                            </div>
                                        </div>';
            }

            $return_express_box = zib_shop_get_order_after_sale_return_express_link($order, 'flex jsb mb10', '<div class="overflow-hidden">' . $_express . '</div><div class="flex0 ml20 muted-2-color"><i class="fa fa-angle-right em12"></i></div>');
            $address            = $after_sale_data['return_address'];

            $input_box = '
                        <div class="muted-box padding-10 mb20">
                        ' . $return_express_box . '
                        <div class="flex muted-color">
                        <div class="icon-header mr10"><i class="fa-fw fa fa-map-marker"></i></div>
                        <div class="">
                            <div class=""><b class="">' . $address['province'] . $address['city'] . $address['county'] . $address['address'] . '</b></div>
                            <div class="em09 mt3"><span class="mr6">' . $address['name'] . '</span><span class="muted-2-color">' . $address['phone'] . '</span></div>
                        </div>
                    </div></div>';
        }

        // 回寄快递信息（进度3显示）
        if ($progress == 3 && !empty($after_sale_data['return_express_info'])) {
            $express = $after_sale_data['return_express_info'];

        }

        $content = '<div class="mini-scrollbar scroll-y max-vh5">' . $info_box . $input_box . '</div>';
        $footer  = '';
        // 底部按钮 - 联系商家按钮通用
        $service_btn = zib_shop_get_author_contact_link($order['post_author'], 'but c-yellow', '联系商家');

        // 根据进度显示不同的按钮
        if ($progress == 1) {
            $express_btn = '<a href="javascript:;" class="but c-blue wp-ajax-submit" data-confirm="请确认发货">确认发货</a>'; //退货快递信息
            //取消售后
            $cancel_btn = zib_shop_get_order_after_sale_cancel_link($order, 'but c-red', '撤销售后');
            $footer .= '<div class="mt20 modal-buts but-average">' . $service_btn . $cancel_btn . $express_btn . '</div>';
            $header_text = '<div class="font-bold c-yellow"><i class="fa fa-truck mr6"></i>等待您发货</div>';
            if ($return_express_over_time) {
                $time_remaining = zib_time_to_c($return_express_over_time);

                $time_countdown = '<div class="mt6 px12 muted-2-color">剩余<span class="badg badg-sm" int-second="1" data-over-text="1秒" data-countdown="' . $time_remaining . '"></span>自动关闭此售后</div>';
                $header_text .= $time_countdown;
            }

            $header = '<div class="touch text-center mb20">' . $header_text . '<button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button></div>';
        } elseif ($progress == 2) {
            $record_btn = zib_shop_get_order_after_sale_return_express_link($order, 'but c-blue', '查看物流信息');
            $footer .= '<div class="mt20 modal-buts but-average">' . $service_btn . $record_btn . '</div>';

            $header = zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-refresh fa-spin"></i>', '等待商家处理');
        } elseif ($progress == 3) {
            $confirm_btn = '<a href="javascript:;" class="but c-blue wp-ajax-submit" form-data=\'' . json_encode(['action' => 'order_after_sale_confirm', 'order_id' => $order['id']]) . '\' data-confirm="确认已收到货物？">确认收货</a>';
            $record_btn  = zib_shop_get_order_after_sale_record_link($order, 'but c-blue', '售后记录');
            $footer .= '<div class="mt20 modal-buts but-average">' . $service_btn . $confirm_btn . $record_btn . '</div>';

            $header = zib_get_modal_colorful_header('jb-green', '<i class="fa fa-check-circle"></i>', '商家已处理完成');
        }

        $content .= '</div>';
        $html = $header . '<form>' . $content . $footer . '</form>';
        echo $html;
        exit;
    }

}
add_action('wp_ajax_order_after_sale_modal', 'zib_shop_ajax_order_after_sale_modal');

//售后退货，发货
function zib_shop_ajax_order_after_sale_return_express()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;

    //环境验证
    zib_ajax_verify_nonce();

    $order = zibpay::get_order($order_id);
    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if ($after_sale_status !== 2) {
        zib_send_json_error('订单状态变化，请刷新页面后重试');
    }

    $order_data      = zibpay::get_meta($order['id'], 'order_data');
    $after_sale_data = $order_data['after_sale_data'] ?? [];
    $progress        = zib_shop_get_order_after_sale_progress($after_sale_data);
    $consignee_phone = $after_sale_data['return_address']['phone'] ?? '';

    if ($progress !== 1) {
        zib_send_json_error('订单状态变化，请刷新页面后重试');
    }

    $express_number       = !empty($_REQUEST['express_number']) ? sanitize_text_field($_REQUEST['express_number']) : '';
    $express_company_name = !empty($_REQUEST['express_company_name']) ? sanitize_text_field($_REQUEST['express_company_name']) : '';
    $return_remark        = !empty($_REQUEST['return_remark']) ? sanitize_text_field($_REQUEST['return_remark']) : '';

    if (!$express_number) {
        zib_send_json_error('请输入快递单号');
    }

    if (!$express_company_name) {
        zib_send_json_error('请选择快递公司');
    }

    $data = [
        'express_number'       => $express_number,
        'express_company_name' => $express_company_name,
        'return_remark'        => $return_remark,
    ];
    zib_shop_after_sale_return_express_handle($order, $data);

    zib_send_json_success(['order_id' => $order_id, 'hide_modal' => true, 'msg' => '退货快递信息已提交，等待商家处理']);
}
add_action('wp_ajax_order_after_sale_return_express', 'zib_shop_ajax_order_after_sale_return_express');

//售后申请
function zib_shop_ajax_order_after_sale_apply()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);

    //环境验证
    zib_ajax_verify_nonce();

    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    //判断订单类型
    if ($order['order_type'] != zib_shop_get_order_type()) {
        zib_send_json_error('当前订单类型不支持申请售后');
    }

    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    //判断订单状态
    if ($order['status'] != 1) {
        zib_send_json_error('订单状态不支持申请售后');
    }

    //判断售后状态
    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if ($after_sale_status && in_array($after_sale_status, [1, 2])) {
        zib_send_json_error('当前订单已申请售后，请勿重复申请');
    }

    $type   = !empty($_REQUEST['type']) ? sanitize_text_field($_REQUEST['type']) : '';
    $price  = !empty($_REQUEST['price']) ? zib_floatval_round($_REQUEST['price']) : 0;
    $remark = !empty($_REQUEST['remark']) ? sanitize_text_field($_REQUEST['remark']) : '';

    $order_data = zibpay::get_meta($order['id'], 'order_data');
    $limit_max  = $order_data['prices']['pay_price'] ?? 0;

    if (!$type) {
        zib_send_json_error('请选择售后类型');
    }

    if (in_array($type, ['refund', 'refund_return', 'insured_price']) && !in_array($order['pay_type'], ['points', 'balance'])) {
        $rewards_img_urls = zib_get_user_rewards_img_urls($order['user_id']);
        $weixin           = $rewards_img_urls['weixin'];
        $alipay           = $rewards_img_urls['alipay'];

        if (!$weixin && !$alipay) {
            zib_send_json_error('请先完成收款设置');
        }
    }

    $reason = '';
    switch ($type) {
        case 'refund': //退款
            if ($limit_max && !$price) {
                zib_send_json_error('请输入退款金额' . $limit_max);
            }
            if ($price > $limit_max) {
                zib_send_json_error('退款金额不能大于订单总金额：' . $limit_max);
            }
            $reason = !empty($_REQUEST['refund_reason']) ? sanitize_text_field($_REQUEST['refund_reason']) : '';
            break;
        case 'refund_return': //退货退款
            $reason = !empty($_REQUEST['refund_return_reason']) ? sanitize_text_field($_REQUEST['refund_return_reason']) : '';
            break;
        case 'replacement': //换货
            if (!$remark) {
                zib_send_json_error('请输入售后留言，详细描述您需要更换的商品选项及参数');
            }
            break;
        case 'warranty': //保修
            break;
        case 'insured_price': //保价
            if ($limit_max && !$price) {
                zib_send_json_error('请输入保价退款金额');
            }
            if ($price > $limit_max) {
                zib_send_json_error('保价金额不能大于订单总金额：' . $limit_max);
            }
            break;
        default:
            zib_send_json_error('请选择售后类型');
    }

    //售后记录
    $after_sale_data = array(
        'type'   => $type,
        'price'  => $price,
        'reason' => $reason,
        'remark' => $remark,
    );

    //用户申请售后
    zib_shop_user_apply_after_sale($order, $after_sale_data);

    zib_send_json_success(['order_id' => $order['id'], 'hide_modal' => true, 'msg' => '售后申请已提交，等待商家处理']);
}
add_action('wp_ajax_order_after_sale_apply', 'zib_shop_ajax_order_after_sale_apply');

//取消售后申请
function zib_shop_ajax_order_after_sale_cancel()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);

    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    //判断售后状态
    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if (in_array($after_sale_status, [3, 4, 5])) {
        zib_send_json_error('当前申请售后已处理完成，无法取消');
    }

    if ($after_sale_status == 2) {
        //
    }

    //取消售后申请
    zib_shop_after_sale_cancel($order);
    zib_send_json_success(['order_id' => $order['id'], 'hide_modal' => true, 'msg' => '售后申请已取消']);
}
add_action('wp_ajax_order_after_sale_cancel', 'zib_shop_ajax_order_after_sale_cancel');

function zib_shop_ajax_order_after_sale_record_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);

    if (!$order) {
        zib_ajax_modal_error('订单不存在');
    }

    //判断权限：自己或管理员
    if ($order['user_id'] != get_current_user_id() && !current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    $record_html = zib_shop_get_after_sale_record_lists($order['id']);
    if (!$record_html) {
        zib_ajax_modal_error('当前订单没有售后记录');
    }

    $header  = '<div class="border-title touch"><div class="flex jc"><b>售后记录</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $content = '<div class="mini-scrollbar scroll-y max-vh7">' . $record_html . '</div>';

    $html = $header . $content;

    echo $html;
    exit;
}
add_action('wp_ajax_order_after_sale_record_modal', 'zib_shop_ajax_order_after_sale_record_modal');

//收藏商品
function zib_shop_ajax_favorite_product()
{
    $id      = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $user_id = get_current_user_id();

    if (!$id) {
        zib_send_json_error('参数错误');
    }

    if (!$user_id) {
        zib_send_json_error('请先登录');
    }

    $is_follow          = zib_get_user_meta($user_id, 'favorite_product', true);
    $plate_follow_count = (int) get_post_meta($id, 'favorite_count', true);

    $is_follow = $is_follow ? $is_follow : array();
    if (in_array($id, $is_follow)) {
        //取消关注
        $index = array_search($id, $is_follow);
        unset($is_follow[$index]);

        $new_count = $plate_follow_count - 1;
        $type      = 'cancel';
        $active    = false;
        $text      = '收藏';
    } else {
        //关注
        $type   = 'add';
        $text   = '已收藏';
        $active = true;

        $is_follow[] = $id;
        $new_count   = $plate_follow_count + 1;
    }
    $new_count = $new_count < 0 ? 0 : $new_count;
    $is_follow = array_values($is_follow);

    zib_update_user_meta($user_id, 'favorite_product', $is_follow);

    update_post_meta($id, 'favorite_count', $new_count);

    do_action('shop_favorite_product', $id, $user_id); //添加挂钩
    wp_send_json_success([
        'type'             => $type,
        'id'               => $id,
        'count'            => $new_count,
        'favorite_product' => $is_follow,
        'active'           => $active,
        'text'             => $text,
    ]);

}
add_action('wp_ajax_favorite_product', 'zib_shop_ajax_favorite_product');

//用户个人主页显示商品
function zib_shop_ajax_user_shop_product()
{

    $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
    $paged   = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $status  = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '';

    $lists = zib_shop_get_user_shop_product_lists($user_id, $paged, $orderby, $status);
    zib_ajax_send_ajaxpager($lists);
}
add_action('wp_ajax_author_shop_product', 'zib_shop_ajax_user_shop_product');
add_action('wp_ajax_nopriv_author_shop_product', 'zib_shop_ajax_user_shop_product');

//作者联系客服
function zib_shop_ajax_author_contact_modal()
{

    $author_id = isset($_REQUEST['author_id']) ? (int) $_REQUEST['author_id'] : 0;
    if (!$author_id) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    $_svg    = zib_get_svg('manual-service');
    $options = _pz('shop_author_contact_opt', []);
    $msg_s   = !empty($options['msg_s']) && _pz('message_s', true) && _pz('private_s', true);

    $lists = '';
    if (!empty($options['more']) && is_array($options['more'])) {
        foreach ($options['more'] as $more) {
            if ($more['name']) {
                $icon = !empty($more['icon']) ? zib_get_cfs_icon($more['icon']) : $_svg;
                $desc = !empty($more['desc']) ? '<div class="desc mt6 muted-2-color">' . $more['desc'] . '</div>' : '';
                $img  = !empty($more['img']) ? '<div class="img ml10" style="width: 60px;"><img class="alone-imgbox-img" src="' . $more['img'] . '" alt="' . $more['name'] . '"></div>' : '';

                $lists .= $more['link'] ?
                '<a class="flex list-mt20" href="' . $more['link'] . '" target="_blank" rel="nofollow">
                    <div class="icon fa-fw em12 mr20 ml20">' . $icon . '</div>
                    <div class="info flex1"><div class="name em12">' . $more['name'] . '</div>' . $desc . '</div> ' . $img . '
                </a>' :
                '<div class="flex list-mt20">
                    <div class="icon fa-fw em12 mr20 ml20">' . $icon . '</div>
                    <div class="info flex1"><div class="name em12">' . $more['name'] . '</div>' . $desc . '</div> ' . $img . '
                </div>';
            }
        }
    }

    if ($msg_s) {
        $name     = !empty($options['msg_name']) ? $options['msg_name'] : '站内客服';
        $desc     = !empty($options['msg_desc']) ? '<div class="desc mt6 muted-2-color">' . $options['msg_desc'] . '</div>' : '';
        $msg_text = '<div class="icon fa-fw em12 mr20 ml20">' . $_svg . '</div>
                    <div class="info"><div class="name em12">' . $name . '</div>' . $desc . '</div>';

        $msg_link = Zib_Private::get_but($author_id, $msg_text, 'flex at list-mt20', true); //私信
        $lists    = $msg_link . $lists;
    }

    $header = zib_get_modal_colorful_header('jb-blue', $_svg, '联系客服');
    echo $header . '<div class="mini-scrollbar scroll-y max-vh5 padding-h10">' . $lists . '</div>';
    exit;
}
add_action('wp_ajax_author_contact_modal', 'zib_shop_ajax_author_contact_modal');
add_action('wp_ajax_nopriv_author_contact_modal', 'zib_shop_ajax_author_contact_modal');
