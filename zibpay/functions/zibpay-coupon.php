<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2024-06-26 11:28:54
 * @LastEditTime : 2025-07-13 15:44:54
 * @Email: 770349780@qq.com
 * @Project: Zibll子比主题
 * @Description: 更优雅的Wordpress主题
 * @Read me: 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind: Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 * Copyright (c) 2024 Qinver. Licensed under GPL-2.0-or-later.
 */

/**
 * @description: 判断是否允许使用优惠码
 * @param {*} $pay_type
 * @param {*} $post_id
 * @return bool true
 */
function zibpay_is_allow_coupon($pay_type, $post_id = 0)
{
    //禁用的类型
    $disallow_pay_type = array(4, 8, 9);
    if (in_array((int) $pay_type, $disallow_pay_type)) {
        return false;
    }

    if (_pz('coupon_post_s')) {
        return true;
    }

    if ($post_id) {
        $pay_meta = get_post_meta($post_id, 'posts_zibpay', true);
        return !empty($pay_meta['coupon_s']) ? true : false;
    }

    return false;
}

/**
 * @description: 获取优惠码描述
 * @param {*} $pay_type
 * @param {*} $post_id
 * @return {*}
 */
function zibpay_get_coupon_desc($pay_type, $post_id = 0)
{
    $desc = '';
    if ($post_id) {
        $pay_meta = get_post_meta($post_id, 'posts_zibpay', true);
        $desc     = !empty($pay_meta['coupon_desc']) ? $pay_meta['coupon_desc'] : '';
    }

    if (!$desc) {
        $desc = _pz('coupon_desc');
    }

    return $desc;
}

/**
 * @description: 添加优惠码到数据库
 * @param {*} $type 类型，可选：coupon | vip_coupon
 * @param {*} $num
 * @param {*} $meta
 * @param {*} $rand_password
 * @param {*} $other
 * @return {*}
 */
function zibpay_generate_coupon($type = 'coupon', $num = 20, $post_id = 0, $meta = array(), $rand_password = 35, $other = '')
{
    $time = current_time('mysql');

    for ($i = 1; $i <= $num; $i++) {
        ZibCardPass::add(array(
            'password'      => ZibCardPass::rand_password($rand_password),
            'type'          => $type,
            'post_id'       => $post_id,
            'create_time'   => $time,
            'modified_time' => $time,
            'status'        => '0', //正常，未使用
            'meta'          => $meta,
            'other'         => $other,
        ));
    }

    return true;
}

//获取优惠码折扣描述
function zibpay_get_coupon_discount_text($discount)
{
    if ($discount['type'] == 'multiply') {
        return ($discount['val'] * 10) . '折';
    } else {
        return '立减' . $discount['val'];
    }
}

//过滤优惠码数据
function zibpay_filter_coupon_data($coupon)
{

    $coupon                   = (array) $coupon;
    $mate                     = maybe_unserialize($coupon['meta']);
    $coupon['discount']       = $mate['discount'];
    $coupon['discount_text']  = zibpay_get_coupon_discount_text($coupon['discount']);
    $coupon['title']          = $mate['title'];
    $coupon['reuse']          = $mate['reuse'];
    $coupon['used_count']     = isset($mate['used_count']) ? $mate['used_count'] : 0; //已使用次数
    $coupon['used_order_num'] = isset($mate['used_order_num']) ? $mate['used_order_num'] : array(); //已使用-订单号
    $coupon['expire_time']    = $mate['expire_time'] ?? '';

    if ($coupon['expire_time'] && current_time('timestamp') > strtotime($coupon['expire_time']) && $coupon['status'] !== 'used') {
        //已过期，标记为不可用
        ZibCardPass::update(array('id' => $coupon['id'], 'status' => 'used'));
        $coupon['status'] = 'used';
    }
    return $coupon;
}

/**
 * @description: 获取优惠码
 * @param {*} $coupon_code 优惠码
 * @param {*} $post_id 文章ID或者商品ID
 * @param {*} $type 类型
 * @return {*}
 */
function zibpay_get_coupon($coupon_code, $type = null, $post_id = 0)
{
    $where = array('password' => $coupon_code, 'type' => array('vip_coupon', 'coupon'), 'status' => '0');

    if ($post_id) {
        $where['post_id'] = array($post_id, 0);
    }

    if ($type) {
        $where['type'] = $type;
    }

    $coupon = ZibCardPass::get_row($where);

    if ($coupon) {
        return zibpay_filter_coupon_data($coupon);
    }
    return false;
}

/**
 * @description: 获取一个优惠码的详细数据
 * @param {*} $coupon_code 优惠码
 * @param {*} $order_type 订单类型
 * @param {*} $args_id 文章ID或者商品ID
 * @return {*}
 */
function zibpay_get_coupon_data($coupon_code, $order_type = null, $args_id = 0)
{

    $type = 'coupon';
    if ($order_type == 4) {
        $type = 'vip_coupon';
    }

    return zibpay_get_coupon($coupon_code, $type, $args_id);
}

/**
 * 判断优惠码是否可用
 * @param $coupon_code 优惠码
 * @param $order_type  订单类型
 * @param int $args_id 文章ID或者商品ID
 * @return array 可用时返回优惠码数据，不可用时返回错误信息
 */
function zibpay_is_coupon_available($coupon_code, $order_type = null, $args_id = 0)
{

    $coupon = zibpay_get_coupon_data($coupon_code, $order_type);
    if (!$coupon) {
        return array('error' => true, 'msg' => '优惠码不存在或已使用');
    }

    if ($args_id && $coupon['post_id'] && $coupon['post_id'] != $args_id) {
        return array('error' => true, 'msg' => '优惠码不能用于当前商品');
    }

    //判断使用次数
    if ($coupon['reuse'] && $coupon['used_count'] >= $coupon['reuse']) {
        return array('error' => true, 'msg' => '优惠码已达到使用次数上限');
    }

    //判断有效期
    if ($coupon['expire_time'] && current_time('timestamp') > strtotime($coupon['expire_time'])) {
        return array('error' => true, 'msg' => '优惠码已过期');
    }

    return $coupon;
}

/**
 * 获取使用优惠码后的金额
 * @param $order_price 订单金额
 * @param $coupon 优惠码数据
 * @return float
 */
function zibpay_get_coupon_order_price($order_price, $coupon)
{
    if ($coupon['discount']['type'] == 'multiply') {
        $order_price = $order_price * $coupon['discount']['val'];
    } else {
        $order_price = $order_price - $coupon['discount']['val'];
    }

    return zib_floatval_round($order_price);
}

/**
 * 支付订单后，将优惠码标记为已使用
 * @param $order 订单对象数据
 */
function zibpay_payment_order_use_coupon($order)
{
    $other = maybe_unserialize($order->other);
    if (empty($other['coupon_id'])) {
        return;
    }

    $coupon = ZibCardPass::get_row(array('id' => $other['coupon_id']));
    if (!$coupon) {
        return;
    }

    $coupon                        = zibpay_filter_coupon_data($coupon);
    $meta_data                     = $coupon['meta'];
    $meta_data['used_count']       = ($meta_data['used_count'] ?? 0) + 1;
    $meta_data['used_order_num'][] = $order->order_num;

    $update_data = array(
        'id'   => $coupon['id'],
        'meta' => $meta_data,
    );

    if ($coupon['reuse'] && $meta_data['used_count'] >= $coupon['reuse']) {
        $update_data['status'] = 'used';
    }

    if ($coupon['reuse'] == 1 && !$coupon['order_num']) {
        $update_data['order_num'] = $order->order_num;
    }

    ZibCardPass::update($update_data);
}
add_action('payment_order_success', 'zibpay_payment_order_use_coupon', 10);
