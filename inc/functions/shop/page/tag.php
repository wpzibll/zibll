<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2025-07-07 15:43:20
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|加载页面
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_get_tag_header($tag_id)
{
    $tag  = get_term($tag_id, 'shop_tag');
    $desc = $tag->description ? '<div class="page-desc muted-color">' . $tag->description . '</div>' : '';

    $html = '<div class="shop-cat-filter-child"><div class=""><div class="title-h-left"><h1>' . $tag->name . '</h1></div>' . $desc . '</div></div>';

    $orderby_lists = zib_shop_get_the_trem_orderby_lists();
    $html .= '<div class="shop-cat-filter-orderby shop-cat-filter-child" win-ajax-replace="orderby"><span class="opacity5">排序</span>' . $orderby_lists . '</div>';

    $more_btn = zib_shop_get_term_header_more_btn($tag);

    $header = '<div class="shop-term-header"><div class="zib-widget shop-cat-filter relative">' . $html . $more_btn . '</div></div>';
    return $header;
}

function zib_shop_tag_page_content()
{

    global $wp_query;
    $cat_id = $wp_query->get_queried_object_id();
    $header = zib_shop_get_tag_header($cat_id);

    $html = '';
    $html .= '<div class="shop-term-main mb20"><div class="ajaxpager product-lists-row">';
    $html .= $header;
    $html .= zib_shop_get_main_product_lists();
    $html .= '</div></div>';

    echo $html;
}
add_action('shop_tag_page_content', 'zib_shop_tag_page_content');

//放置于最底部
zib_shop_term_page_template('tag');
