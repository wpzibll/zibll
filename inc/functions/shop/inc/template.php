<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2025-12-23 21:24:04
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|页面UI模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//前台加载css和js文件
function zib_shop_enqueue_script()
{
    wp_enqueue_script('shop', ZIB_SHOP_ASSETS_URI . '/js/main.min.js', array(), THEME_VERSION, true);
    wp_enqueue_style('_shop', ZIB_SHOP_ASSETS_URI . '/css/main.min.css', array(), THEME_VERSION, 'all');
}

//只要开启了商城功能就加载
if (!is_admin()) {
    add_action('wp_enqueue_scripts', 'zib_shop_enqueue_script');
}

/**
 * @description: 骨架屏模板
 * @param {*}
 * @return {*}
 */
function zib_shop_get_lists_card_placeholder($args = array(), $i = 4)
{
    $defaults = [
        'class'          => '',
        'show_desc'      => true,
        'show_discount'  => true,
        'show_price'     => true,
        'text_center'    => true,
        'style'          => '',
        'thumb_scale'    => 100,
        'title_one_line' => false,
    ];
    $args = wp_parse_args($args, $defaults);
    $args['class'] .= $args['style'] ? ' style-' . $args['style'] : '';
    $thumb_attr = $args['thumb_scale'] && $args['thumb_scale'] != 100 ? ' style="--t-s:' . $args['thumb_scale'] . '%;"' : '';

    $html = '<posts class="product-item posts-item card ' . $args['class'] . '"' . $thumb_attr . '>
                <div class="item-thumbnail placeholder"></div>
                <div class="item-body product-item-body' . ($args['text_center'] ? ' text-center' : '') . '">
                    <h2 class="item-heading placeholder' . ($args['title_one_line'] ? ' text-ellipsis' : '') . '"></h2>';
    if ($args['show_desc']) {
        $html .= '<div class="item-excerpt placeholder k2"></div>';
    }
    if ($args['show_discount']) {
        $html .= '<div class="item-discount-badge scroll-x no-scrollbar"><i class="placeholder s1 mr6" style="height: 20px;border-radius: 100px;"></i><i class="placeholder s1 mr6" style="height: 20px;border-radius: 100px;"></i></div>';
    }
    if ($args['show_price']) {
        $html .= '<div class="item-price product-price"><div class="placeholder s1" style="height: 18px;"></div></div>';
    }

    $html .= '</div></posts>';

    $placeholder = str_repeat($html, $i);

    return $placeholder;
}

//输出页面模板主要内容
function zib_shop_term_page_template($type = 'home')
{
    do_action('shop_locate_template');
    do_action('shop_locate_template_' . $type);
    add_filter('zib_is_show_sidebar', '__return_false'); //不显示侧边栏
    add_filter('zib_frontend_set_input_array', 'zib_shop_term_frontend_set_input_array', 10, 3); //添加前台设置

    get_header();
    echo '<main id="shop">';
    echo '<div class="container">';
    do_action('shop_' . $type . '_page_content');
    echo '</div>';
    echo '</main>';
    get_footer();
}
