<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-02-24 15:48:12
 * @LastEditTime : 2025-12-23 22:59:47
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题 | 购物车页面
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_cart_page_content()
{
    $user_id = get_current_user_id();

    //如果没有登录
    if (!$user_id) {
        echo '<div class="flex jc" style="min-height:60vh;">';
        echo '<div>';
        echo zib_get_null('', 0, 'null-user.svg');
        echo zib_get_user_singin_page_box('box-body flex1', 'Hi！请先登录');
        echo '</div>';
        echo '</div>';
        return;
    }

    //购物车商品列表
    $cart_discount_data = zib_shop_get_cart_vue_data($user_id);

    if (empty($cart_discount_data['cart_data'])) {
        echo zib_get_null('购物车空空的', 80, 'null-order.svg');
        return;
    }

    $lists_html = file_get_contents(get_theme_file_path(ZIB_SHOP_REQUIRE_URI . 'template/v-cart.html'));
    //购物车结算
    echo '<div class="v-cart vue-mount relative" v-cloak @vue:mounted="mounted" v-config=\'' . esc_attr(json_encode($cart_discount_data)) . '\'>';
    echo '<div>';
    echo $lists_html;
    echo '</div>';
    echo '</div>';
}
add_action('shop_cart_page_content', 'zib_shop_cart_page_content');

//添加body类
function zib_shop_cart_page_body_class($classes)
{
    $classes[] = 'shop-cart-page';
    return $classes;
}
add_filter('body_class', 'zib_shop_cart_page_body_class');

add_filter('echo_seo_title', '__return_true'); //开启SEO标题
add_filter('is_shop_cart_page', '__return_true'); //判断：是购物车页面
remove_filter('zib_nav_radius_button', 'zib_shop_add_nav_cart_button'); //移除顶部导航栏购物车按钮
add_filter('zib_is_show_sidebar', '__return_false'); //不显示侧边栏
remove_action('wp_footer', 'zib_footer_tabbar'); //移除底部导航栏

//加载页面，必须放在最下面
$page_type = 'cart';
do_action('shop_locate_template');
do_action('shop_locate_template_' . $page_type);

get_header();
echo '<main id="shop">';
echo '<div class="container fluid-widget">';
dynamic_sidebar('shop_' . $page_type . '_top_fluid');
echo '</div>';
echo '<div class="container">';
do_action('shop_' . $page_type . '_page_content');
echo '</div>';
echo '<div class="container fluid-widget">';
dynamic_sidebar('shop_' . $page_type . '_bottom_fluid');
echo '</div>';
echo '</main>';
get_footer();
