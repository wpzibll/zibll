<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-28 22:46:22
 * @LastEditTime: 2024-03-04 19:37:07
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */



//获取分类文章
function zib_get_documentnav_posts($new_query)
{
    //循环文章内容
    $posts_html = '';
    $posts_i = 1;
    if ($new_query->have_posts()) {
        while ($new_query->have_posts()) : $new_query->the_post();
            $time_ago = zib_get_time_ago(get_the_time('Y-m-d H:i:s'));
            $posts_html .= '<a class="list-group-item" ' . _post_target_blank() . ' href="' . get_permalink() . '">';
            $posts_html .= '<span class="badg badg-sm pull-right" title="' . get_the_time('Y-m-d H:i:s') . '">' . $time_ago;
            $posts_html .= '</span>';
            $posts_html .=  get_the_title() . '<span class="focus-color">' . get_the_subtitle(false) . '</span>';
            $posts_html .= '</a>';
            $posts_i++;
        endwhile;
        $html = '<div class="list-group main-shadow">';
        $html .= $posts_html;
        $html .= '</div>';
    } else {
        $html = '<div class="zib-widget">';
        $html .= zib_get_null('暂无相应内容', '40', 'null-2.svg');
        $html .= '</div>';
    }

    wp_reset_query();
    return $html;
}


/**
 * @description: 获取文档导航帖子
 * @param {*}
 * @return {*}
 */
function zib_ajax_get_documentnav_posts()
{
    global $wpdb;
    //准备查询参数
    $search = !empty($_REQUEST['search']) ? $_REQUEST['search'] : '';
    $cat_id = !empty($_REQUEST['cat_id']) ? $_REQUEST['cat_id'] : '';
    $one_cat = !empty($_REQUEST['one_cat']) ? $_REQUEST['one_cat'] : '';
    $paged = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $page_size = 12;
    if (!$search && !$cat_id) {
        echo (json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '参数传入错误！')));
        exit();
    }

    //准备查询参数
    $posts_args['cat'] = $cat_id;
    $posts_args['ignore_sticky_posts'] = 1;
    $posts_args['paged'] = $paged;
    $posts_args['posts_per_page'] = $page_size;
    $posts_args['post_status'] = 'publish';

    if ($search) {
        //保存历史搜索词
        zib_save_history_search($search);
        $posts_args['cat'] = $one_cat;
        $posts_args['s'] = $search;
    }

    $new_query = new WP_Query($posts_args);
    $html = zib_get_documentnav_posts($new_query);

    $args = array(
        'url_base'           => add_query_arg(array('paged' => '%#%', 'cat_id' => $cat_id, 'search' => $search), admin_url('admin-ajax.php')), // http://example.com/all_posts.php%#% : %#% 替换为页码。
        'total'              => $new_query->found_posts,  //总计条数
        'current'            => $paged,  //当前页码
        'page_size'          => $page_size, //每页几条
        'class'              => 'pagenav notop',
    );

    $html .= zib_get_paginate_links($args);

    echo (json_encode(array('html' => $html, 'total' => $new_query->found_posts, 'history_search' => zib_get_search_keywords_but(zib_get_search_history_keywords(), 'history'))));
    exit();
}
add_action('wp_ajax_documentnav_posts', 'zib_ajax_get_documentnav_posts');
add_action('wp_ajax_nopriv_documentnav_posts', 'zib_ajax_get_documentnav_posts');
