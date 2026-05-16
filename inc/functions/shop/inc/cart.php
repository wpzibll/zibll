<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-02-24 13:18:40
 * @LastEditTime: 2025-10-06 21:18:44
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | 购物车
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//在顶部导航显示购物车按钮
function zib_shop_add_nav_cart_button($but, $user_id)
{
    //用户未登录则不显示
    if (!$user_id) {
        return $but;
    }

    $count = zib_shop_get_cart_count($user_id);
    $url   = zib_shop_get_cart_url();
    $icon  = '<span class="toggle-radius cart-icon nav-cart"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-shopping-cart"></use></svg></span>';
    $icon .= '<badge class="top" cart-count="' . $count . '">' . ($count ?: '') . '</badge>';

    $cart_button = '<div class="nav-radius-icon ml10"><a href="' . $url . '">' . $icon . '</a></div>';

    return $but . $cart_button;
}
add_filter('zib_nav_radius_button', 'zib_shop_add_nav_cart_button', 10, 2);

/**
 * 购物车商品列表
 * 数据结构为：$items[$product_id][$options_string] = $count
 *
 */
function zib_shop_get_cart_items($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    //如果用户未登录，则返回空数组
    if (!$user_id) {
        return array();
    }

    //创建静态变量
    static $cart_items = array();
    if (isset($cart_items[$user_id])) {
        return $cart_items[$user_id];
    }

    $items = get_user_meta($user_id, 'shop_cart', true);
    if (!$items || !is_array($items)) {
        $items = array();
    }

    $cart_items[$user_id] = $items;
    return $items;
}

/**
 * 更新购物车全部商品
 * @param array $cart_data 购物车数据
 * @param int $user_id 用户ID
 * @return array 购物车数据
 */
function zib_shop_cart_update($cart_data, $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return [];
    }

    update_user_meta($user_id, 'shop_cart', $cart_data);
    return $cart_data;
}

//添加购物车商品
function zib_shop_cart_add($product_id, $options_key = '', $count = 1, $user_id = 0)
{
    $options_string = zib_shop_product_options_to_string($options_key);

    $items = zib_shop_get_cart_items($user_id);
    if (isset($items[$product_id][$options_string])) {
        $items[$product_id][$options_string] += $count;
    } else {
        $items[$product_id][$options_string] = $count;
    }

    update_user_meta($user_id, 'shop_cart', $items);
    return $items;
}

//减少购物车商品，如果数量为0，则删除当前组合
function zib_shop_cart_reduce($product_id, $options_key = '', $count = 1, $user_id = 0)
{
    $options_string = zib_shop_product_options_to_string($options_key);

    $items = zib_shop_get_cart_items($user_id);
    if (isset($items[$product_id][$options_string])) {
        $items[$product_id][$options_string] -= $count;
        if ($items[$product_id][$options_string] <= 0) {
            unset($items[$product_id][$options_string]); //删除当前组合。未使用
        }
    }

    update_user_meta($user_id, 'shop_cart', $items);
    return $items;
}

//删除购物车商品
function zib_shop_cart_remove($product_id, $options_key = '', $user_id = 0)
{
    $items          = zib_shop_get_cart_items($user_id);
    $options_string = zib_shop_product_options_to_string($options_key);

    if (isset($items[$product_id][$options_string])) {
        unset($items[$product_id][$options_string]);
    } elseif (isset($items[$product_id])) {
        unset($items[$product_id]);
    }

    update_user_meta($user_id, 'shop_cart', $items);
}

/**
 * 一次性删除多个购物车商品
 * @param array $carts 购物车商品
 * 数据结构为：[
 * ]
 *
 * @param int $user_id 用户ID
 * @return array 购物车数据
 */
function zib_shop_cart_remove_multi(array $carts, $user_id = 0)
{
    $items = zib_shop_get_cart_items($user_id);
    foreach ($carts as $cart) {
        if (isset($items[$cart['product_id']][$cart['opt_key']])) {
            unset($items[$cart['product_id']][$cart['opt_key']]);
        }
    }

    //清理为空的
    foreach ($items as $product_id => $opt_items) {
        if (isset($items[$product_id]) && empty($items[$product_id])) {
            unset($items[$product_id]);
        }
    }

    update_user_meta($user_id, 'shop_cart', $items);
}

//获取购物车商品总数量
function zib_shop_get_cart_count($user_id = 0, $items = [])
{
    if (!$items) {
        $items = zib_shop_get_cart_items($user_id);
    }

    $count = 0;
    foreach ($items as $item) {
        $count += count($item);
    }

    return $count;
}

//清空购物车
function zib_shop_cart_empty($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    update_user_meta($user_id, 'shop_cart', array());
}

//判断是否可以加入购物车
function zib_shop_can_add_cart($product, $options_key = '0')
{
    if (!is_object($product)) {
        $product = get_post($product);
    }

    if (!$product) {
        return false;
    }

    if ($product->post_type !== 'shop_product') {
        return false;
    }

    if ($product->post_status !== 'publish') {
        return false;
    }

    //判断商品选项是否存在
    if (!zib_shop_product_options_is_exists($product, $options_key)) {
        return false;
    }

    return true;
}
