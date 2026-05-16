<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-03 22:25:12
 * @LastEditTime: 2022-04-24 01:35:24
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|版块类函数|plate
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取版块分类的排序方式
function zib_bbs_get_plate_cat_order_options()
{
    $args = array(
        'last_reply' => '最后回帖',
        'last_post'  => '最后发帖',
        'views'      => '热度排序',
        'name'       => '名称排序',
        'count'      => '版块数量',
    );

    return apply_filters('bbs_plate_cat_order_options', $args);
}

//输出主内容
function zib_bbs_plate_cat_content()
{

    global $wp_query;
    //  $post_type = $wp_query->get('post_type');
    $taxonomy = $wp_query->get('taxonomy');
    $term_id  = $wp_query->get_queried_object_id();
    $page     = zib_get_the_paged();
    $orderby  = !empty($option['orderby']) ? $option['orderby'] : 'date';
    $header   = zib_bbs_get_term_header($taxonomy, $term_id);
    $args     = array(
        'post_type'   => 'plate',
        'post_status' => 'publish',
        'order'       => 'DESC',
        'showposts'   => -1,
        'tax_query'   => array(
            array(
                'taxonomy' => 'plate_cat',
                'terms'    => $term_id,
            ),
        ),
    );

    $args        = zib_bbs_query_orderby_filter($orderby, $args);
    $plate_query = new WP_Query($args);

    $lists = '';
    if ($plate_query->have_posts()) {
        while ($plate_query->have_posts()): $plate_query->the_post();
            global $post;
            $lists .= zib_bbs_get_main_plate('');
        endwhile;
        wp_reset_query();
    } else {
        $lists .= '<div class="flex jc" style="width:100%;">';
        $lists .= zib_get_null('该分类下暂无内容', 60, 'null.svg', '', 300);
        $lists .= '</div>';

        if (1 == $page) {
            $plate_add = zib_bbs_get_plate_add_link($term_id, 'ml10 mr10 mt10 but hollow c-blue padding-lg');
            $lists .= '<div class="text-center mb20"  style="width:100%;">';
            $lists .= $plate_add;
            $lists .= '</div>';
        }
    }

    $html = '';
    $html .= '<div class="plate-cat-main term-main">';
    $html .= $header;
    $html .= '<div class="plate-lists">';
    $html .= $lists;
    $html .= '</div>';
    $html .= '</div>';
    echo $html;
}
add_action('bbs_plate_cat_page_content', 'zib_bbs_plate_cat_content');

/**
 * @description: 获取添加版块分类的连接按钮
 * @param {*} $cat
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_plate_cat_add_link($cat = 0, $class = 'ml6 but hollow c-blue p2-10', $con = '<i class="fa fa-plus"></i>创建新的分类', $tag = 'botton', $new = false)
{
    if (!zib_bbs_current_user_can('plate_cat_add')) {
        return;
    }

    return zib_bbs_get_term_edit_link('plate_cat', $cat, $class, $con, $tag, $new);
}

/**
 * @description: 获取添加编辑版块分类的连接按钮
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_plate_cat_edit_link($cat = 0, $class = 'ml6 but hollow c-blue p2-10', $con = '<i class="fa fa-plus"></i>编辑此分类', $tag = 'botton', $new = false)
{
    if (!zib_bbs_current_user_can('plate_cat_edit')) {
        return;
    }

    return zib_bbs_get_term_edit_link('plate_cat', $cat, $class, $con, $tag, $new);
}

//版块分类页面不显示侧边栏
add_action('bbs_locate_template_plate_cat', function () {
    add_filter('zib_is_show_sidebar', '__return_false');
});

function zib_bbs_get_plate_cat_link($plate, $class = '')
{
    $plate_cat = get_the_terms($plate, 'plate_cat');

    if (is_wp_error($plate_cat) || empty($plate_cat[0]->term_id)) {
        return;
    }

    $url   = get_term_link($plate_cat[0]);
    $title = esc_attr($plate_cat[0]->name);
    return '<a title="' . $title . '" class="' . $class . '" href="' . $url . '">' . $title . '</a>';
}

//获取版主设置发帖权限的模态框链接
function zib_bbs_get_plate_cat_set_add_limit_link($id = 0, $class = '', $con = '', $tag = 'a')
{
    if (!$con) {
        global $zib_bbs;
        $con = '<i class="fa fa-fw fa-unlock-alt mr6"></i>设置权限限制';
    }
    return zib_bbs_get_set_add_limit_link('plate_cat', $id, $class, $con, $tag);
}
