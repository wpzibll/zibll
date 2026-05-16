<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-12-23 22:31:32
 * @LastEditTime : 2025-08-05 23:06:51
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题 | 商品评论
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * 获取商品的分类中的配置
 */
function zib_shop_get_product_cat_config($post, $key, $sub_key = null, $default = '')
{

    if (!is_object($post)) {
        $post = get_post($post);
    }

    //依次按层级查找分类配置
    $cat = get_the_terms($post, 'shop_cat');
    if ($cat) {
        foreach ($cat as $c) {
            $config = zib_shop_get_cat_config($c->term_id, $key);
            if ((!$sub_key && $config) || ($sub_key && !empty($config[$sub_key]))) {
                return $config;
            }
        }

        foreach ($cat as $c) {
            if (!empty($c->parent)) {
                $config = zib_shop_get_product_parent_cat_config($c, $key, $sub_key);
                if ((!$sub_key && $config) || ($sub_key && !empty($config[$sub_key]))) {
                    return $config;
                }
            }
        }
    }

    return $default;
}

function zib_shop_get_product_parent_cat_config($cat, $key, $sub_key = null)
{
    if (!is_object($cat)) {
        $cat = get_term($cat, 'shop_cat');
    }

    if (!empty($cat->parent)) {
        $config = zib_shop_get_cat_config($cat->parent, $key);

        if ((!$sub_key && $config) || ($sub_key && !empty($config[$sub_key]))) {
            return $config;
        }

        $parent_cat = get_term($cat->parent, 'shop_cat');
        if (!empty($parent_cat->parent)) {
            return zib_shop_get_product_parent_cat_config($parent_cat, $key, $sub_key);
        }
    }

    return null;
}

function zib_shop_get_cat_sales_count($cat_id)
{
    $sales = (int) get_term_meta($cat_id, 'sales_volume', true);
    return $sales;
}

//获取分类的配置
function zib_shop_get_cat_config($cat_id, $key = null, $default = '')
{

    return zib_shop_get_term_config('shop_cat', $cat_id, $key, $default);
}
