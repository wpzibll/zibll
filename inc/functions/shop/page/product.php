<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2025-07-03 21:45:10
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城商品详情页
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_product_body_class($classes)
{

    $unset_keys = ['shop_product-template-default', 'single-shop_product', 'site-layout-1', 'site-layout-2', 'site-layout-3'];

    foreach ($unset_keys as $key) {
        $s = array_search($key, $classes);
        if ($s !== false) {
            unset($classes[$s]);
        }
    }

    $content_layout = zib_shop_get_product_content_layout();
    if ($content_layout === 'side') {
        $classes[] = 'site-layout-2';
    } else {
        $classes[] = 'site-layout-1';
    }

    $classes[] = 'content-layout-' . $content_layout;
    $classes[] = 'shop';
    return $classes;
}

$page_type = 'product';
do_action('shop_locate_template');
do_action('shop_locate_template_' . $page_type);
add_filter('zib_frontend_set_input_array', 'zib_shop_single_frontend_set_input_array', 10, 2); //添加前台设置
add_filter('body_class', 'zib_shop_product_body_class');

get_header();
echo '<main id="shop">';
do_action('shop_' . $page_type . '_page_header');
do_action('shop_' . $page_type . '_page_content');
echo '</main>';
get_footer();
