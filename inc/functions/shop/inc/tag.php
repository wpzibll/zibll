<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-06-17 13:50:07
 * @LastEditTime : 2025-08-06 00:04:45
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | 商品标签
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * 获取商品标签
 */
function zib_shop_get_product_tags_data($product)
{

    if (is_object($product) && isset($product->ID)) {
        $product_id = $product->ID;
    } else {
        $product_id = $product;
    }

    //静态变量
    static $tag_data = [];

    if (isset($tag_data[$product_id])) {
        return $tag_data[$product_id];
    }

    $tags = get_the_terms($product_id, 'shop_tag');
    if (!$tags) {
        $tag_data[$product_id] = [
            'important' => [],
            'tags'      => [],
        ];
        return $tag_data[$product_id];
    }
    $tag_data = [];
    foreach ($tags as $item) {
        $_config      = zib_shop_get_tag_config($item->term_id);
        $is_important = !empty($_config['is_important']) ? true : false;

        $tag_data[] = [
            'id'           => $item->term_id,
            'name'         => $item->name,
            'slug'         => $item->slug,
            'priority'     => $_config['priority'] ?? 50,
            'class'        => $_config['class'] ?? '',
            'is_important' => $is_important,
        ];
    }

    $tag_data = zib_shop_discount_sort($tag_data);

    //选出第一个重点标签，其它的移出is_important
    $important_tag = [];
    foreach ($tag_data as $key => $item) {
        if ($item['is_important']) {
            $important_tag = $item;
            unset($tag_data[$key]);
            break;
        }
    }

    $tag_data[$product_id] = [
        'important' => $important_tag,
        'tags'      => $tag_data,
    ];
    return $tag_data[$product_id];
}

//获取商品important标签
function zib_shop_get_product_important_tag($product_id, $class = 'badg-sm')
{
    $tag_data      = zib_shop_get_product_tags_data($product_id);
    $important_tag = $tag_data['important'];
    if ($important_tag) {
        $class = $class ? ' ' . $class : '';
        return '<span class="badg ' . $important_tag['class'] . $class . '">' . $important_tag['name'] . '</span>';
    }
    return '';
}

//获取商品tags标签
function zib_shop_get_product_tags($product_id)
{
    $tag_data = zib_shop_get_product_tags_data($product_id);
    return $tag_data['tags'];
}

//获取标签的配置
function zib_shop_get_tag_config($tag_id, $key = null, $default = '')
{

    return zib_shop_get_term_config('shop_tag', $tag_id, $key, $default);
}
