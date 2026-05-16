<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2025-12-28 21:45:25
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|加载页面
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_get_cat_breadcrumbs($cat_id)
{
    $breadcrumbs = '<i class="fa fa-map-marker mr6"></i>';
    // 网站首页
    if (_pz('shop_breadcrumbs_home', true)) {
        $breadcrumbs .= '<li><a href="' . home_url() . '">首页</a></li>';
    }

    // 商城首页
    if (_pz('shop_breadcrumbs_shop_home', true)) {
        $home_url = zib_shop_get_home_url();
        if ($home_url) {
            $home_name = _pz('shop_breadcrumbs_shop_home_name') ?: '商城';
            $breadcrumbs .= '<li><a href="' . esc_url($home_url) . '">' . $home_name . '</a></li>';
        }
    }

    $args = array(
        'separator' => '</li><li>',
        'link'      => true,
        'format'    => 'name',
        'inclusive' => false,
    );

    $parents = '<li>' . get_term_parents_list($cat_id, 'shop_cat', $args) . '</li>';
    $parents = str_replace('href="', 'route="1" ajax-replace="1" class="ajax-next" href="', $parents);
    $breadcrumbs .= str_replace('<li></li>', '', $parents);

    $term = get_term($cat_id, 'shop_cat');
    $breadcrumbs .= '<li>' . $term->name . '</li>';

    $filter = '<div class="breadcrumb">' . $breadcrumbs . '</div>';
    return $filter;
}

function zib_shop_get_cat_header($cat_id)
{

    $breadcrumbs = zib_shop_get_cat_breadcrumbs($cat_id);
    $filter      = zib_shop_get_cat_filter($cat_id);

    $header = '<div class="shop-term-header ajax-item-header">' . $breadcrumbs . $filter . '</div>';

    return $header;
}

//分类筛选
function zib_shop_get_cat_filter($cat_id)
{
    $cat                 = get_term($cat_id, 'shop_cat');
    $hierarchy           = _get_term_hierarchy('shop_cat');
    $delimiter_blog_name = zib_get_delimiter_blog_name();

    //自己的子分类
    $child_terms = !empty($hierarchy[$cat_id]) ? get_terms(['include' => $hierarchy[$cat_id], 'taxonomy' => 'shop_cat', 'hide_empty' => false]) : array();
    //自己的同级分类
    $parent_id = $cat->parent;
    if ($parent_id) {
        $sibling_terms = !empty($hierarchy[$parent_id]) ? get_terms(['include' => $hierarchy[$parent_id], 'taxonomy' => 'shop_cat', 'hide_empty' => false]) : array();
    } else {
        //所有一级分类
        $sibling_terms = get_terms(['parent' => 0, 'taxonomy' => 'shop_cat', 'hide_empty' => false]);
    }

    //如果没有子分类，在查找自己的同父级分类
    $parent_terms = array();
    if ((empty($child_terms) || is_wp_error($child_terms))) {
        $parent_term = $parent_id ? get_term($parent_id, 'shop_cat') : null;
        if (!empty($parent_term->parent) && !empty($hierarchy[$parent_term->parent])) {
            $parent_terms = get_terms(['include' => $hierarchy[$parent_term->parent], 'taxonomy' => 'shop_cat', 'hide_empty' => false]);
        } elseif ($parent_id) {
            $parent_terms = get_terms(['parent' => 0, 'taxonomy' => 'shop_cat', 'hide_empty' => false]);
        }
    }

    //如果有子分类
    $tab_cat = '';
    if (!empty($child_terms) && !is_wp_error($child_terms)) {
        $tab_cat = '<div class="shop-cat-filter-tab list-inline scroll-x mini-scrollbar tab-nav-theme">';
        foreach ($sibling_terms as $_term) {
            if ($_term->term_id == $cat_id) {
                $tab_cat .= '<li class="active"><span class="focus-color">' . $_term->name . '</span></li>';
            } else {
                $tab_cat .= '<li><a ajax-replace="true" route="1" route-title="' . esc_attr($_term->name) . '-商城' . $delimiter_blog_name . '" class="ajax-next" href="' . get_term_link($_term->term_id) . '">' . $_term->name . '</a></li>';
            }
        }
        $tab_cat .= '</div>';

        $tab_cat .= '<div class="shop-cat-filter-child">';
        $tab_cat .= '<span class="focus-color">全部</span>';
        foreach ($child_terms as $_term) {
            $tab_cat .= '<a ajax-replace="true" route="1" route-title="' . esc_attr($_term->name) . '-商城' . $delimiter_blog_name . '" class="ajax-next" href="' . get_term_link($_term->term_id) . '">' . $_term->name . '</a>';
        }
        $tab_cat .= '</div>';
    } else {
        //有父级分类
        if (!empty($parent_terms) && !is_wp_error($parent_terms)) {
            $tab_cat = '<div class="shop-cat-filter-tab list-inline scroll-x mini-scrollbar tab-nav-theme">';
            foreach ($parent_terms as $_term) {
                if ($_term->term_id == $parent_id) {
                    $tab_cat .= '<li class="active"><span class="focus-color">' . $_term->name . '</span></li>';
                } else {
                    $tab_cat .= '<li><a ajax-replace="true" route="1" route-title="' . esc_attr($_term->name) . '-商城' . $delimiter_blog_name . '" class="ajax-next" href="' . get_term_link($_term->term_id) . '">' . $_term->name . '</a></li>';
                }
            }
            $tab_cat .= '</div>';

            $tab_cat .= '<div class="shop-cat-filter-child">';
            $tab_cat .= '<a ajax-replace="true" route="1" route-title="' . esc_attr($_term->name) . '-商城' . $delimiter_blog_name . '" class="ajax-next" href="' . get_term_link($parent_id) . '">全部</a>';
            foreach ($sibling_terms as $_term) {
                if ($_term->term_id == $cat_id) {
                    $tab_cat .= '<span class="focus-color">' . $_term->name . '</span>';
                } else {
                    $tab_cat .= '<a ajax-replace="true" route="1" route-title="' . esc_attr($_term->name) . '-商城' . $delimiter_blog_name . '" class="ajax-next" href="' . get_term_link($_term->term_id) . '">' . $_term->name . '</a>';
                }
            }
            $tab_cat .= '</div>';

        } else {
            $tab_cat = '<div class="shop-cat-filter-tab list-inline scroll-x mini-scrollbar tab-nav-theme">';
            foreach ($sibling_terms as $_term) {
                if ($_term->term_id == $cat_id) {
                    $tab_cat .= '<li class="active"><span class="focus-color">' . $_term->name . '</span></li>';
                } else {
                    $tab_cat .= '<li><a ajax-replace="true" route="1" route-title="' . esc_attr($_term->name) . '-商城' . $delimiter_blog_name . '" class="ajax-next" href="' . get_term_link($_term->term_id) . '">' . $_term->name . '</a></li>';
                }
            }
            $tab_cat .= '</div>';
        }
    }

    $orderby_lists = zib_shop_get_the_trem_orderby_lists();
    $tab_cat .= '<div class="shop-cat-filter-orderby shop-cat-filter-child"><span class="opacity5">排序</span>' . $orderby_lists . '</div>';
    $tab_cat .= zib_shop_get_term_header_more_btn($cat);

    $filter = '<div class="shop-cat-filter zib-widget relative">';
    $filter .= $tab_cat;
    $filter .= '</div>';
    return $filter;
}

function zib_shop_cat_page_content()
{

    global $wp_query;
    $cat_id           = $wp_query->get_queried_object_id();
    $header           = zib_shop_get_cat_header($cat_id);
    $list_style_style = zib_shop_get_cat_config($cat_id, 'list_style_style');
    $card_args        = [];
    if ('default' !== $list_style_style) {
        $card_args['style'] = $list_style_style;
    }

    $html = '';
    $html .= '<div class="shop-term-main mb20"><div class="ajaxpager product-lists-row">';
    $html .= $header;
    $html .= zib_shop_get_main_product_lists($card_args);
    $html .= '</div></div>';

    echo $html;
}
add_action('shop_cat_page_content', 'zib_shop_cat_page_content');

//放置于最底部
zib_shop_term_page_template('cat');
