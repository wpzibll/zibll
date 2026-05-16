<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-22 20:24:57
 * @LastEditTime : 2025-12-23 13:38:40
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_bbs_get_tag_edit_link($id = 0, $class = 'ml6 but hollow c-blue p2-10', $con = '<i class="fa fa-plus"></i>创建新的', $tag = 'botton')
{
    return zib_bbs_get_term_edit_link('forum_tag', $id, $class, $con, $tag);
}

function zib_bbs_get_topic_edit_link($id = 0, $class = 'ml6 but hollow c-blue p2-10', $con = '<i class="fa fa-plus"></i>创建新的', $tag = 'botton')
{
    return zib_bbs_get_term_edit_link('forum_topic', $id, $class, $con, $tag);
}

function zib_bbs_get_term_query($args = array())
{

    $query = array(
        'taxonomy'   => array('plate_cat'), //分类法
        'orderby'    => 'count', //默认为版块数量
        'order'      => 'DESC',
        'count'      => true,
        'hide_empty' => true,
        'number'     => 0,
    );

    if (!empty($args['include'])) {
        $query['order'] = 'ASC';
    }
    if (!empty($args['orderby'])) {
        $query = zib_bbs_query_orderby_filter($args['orderby'], $query);
        unset($args['orderby']);
    }
    $query = array_merge($query, $args);

    $new_query = new WP_Term_Query($query);

    if (!is_wp_error($new_query) && isset($new_query->terms)) {
        return $new_query->terms;
    }

    return false;
}

/**
 * @description: 获取话题列表
 * @param {*} $term
 * @param {*} $style
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_topic_lists($term, $style = '', $class = 'padding-10')
{

    global $zib_bbs;
    $id          = $term->term_id;
    $name        = $term->name;
    $thumb       = zib_bbs_get_term_thumbnail($term, 'fit-cover radius4');
    $posts_count = _cut_count($term->count);
    $views_count = zib_bbs_get_term_views_cut_count($id);
    $icon        = zib_get_svg('topic');
    $permalink   = get_term_link($term);

    $info = '';
    $info .= '<span class="mr20">' . $zib_bbs->posts_name . ':' . $posts_count . '</span>';
    $info .= '<span class="">热度:' . $views_count . '</span>';

    $lists = '<a href="' . esc_url($permalink) . '"><div class="flex topic-item ' . $class . '">';
    $lists .= '<div class="square-box mr10 thumb">' . $thumb . '</div>';
    $lists .= '<div class="info flex jsb xx">';
    $lists .= '<div class="term-title">' . $icon . $name . $icon . '</div>';
    $lists .= '<div class="muted-3-color em09 desc">' . $info . '</div>';
    $lists .= '</div>';
    $lists .= '</div></a>';

    return $lists;
}

/**
 * @description: 获取删除按钮
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_term_modal_delete_link($taxonomy = '', $id = 0, $class = '', $con = '<i class="fa fa-trash-o fa-fw"></i>删除', $tag = 'botton')
{

    if (!$id || !zib_bbs_current_user_can('' . $taxonomy . '_delete', $id)) {
        return;
    }

    $class   = $taxonomy . 'delete-link ' . $class;
    $url_var = array(
        'action' => $taxonomy . '_delete_modal',
        'id'     => $id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'text'          => $con,
        'height'        => 274,
        'mobile_bottom' => true,
        'query_arg'     => $url_var,
    );
    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取添加版块分类的连接按钮
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_term_edit_link($taxonomy = '', $id = 0, $class = '', $con = '', $tag = 'botton', $new = false)
{

    //编辑权限判断
    if ($id && !zib_bbs_current_user_can($taxonomy . '_edit', $id)) {
        return;
    }

    //添加权限
    if (!$id && !zib_bbs_current_user_can($taxonomy . '_add')) {
        //如果是话题，则判断是否开启了话题按钮一直显示
        if ('forum_topic' !== $taxonomy || !_pz('bbs_show_new_topic', true)) {
            return;
        }
    }

    if (!get_current_user_id()) {
        //如果未登录则显示登录按钮
        return '<' . $tag . ' class="signin-loader ' . esc_attr($class) . '" href="javascript:;">' . $con . '</' . $tag . '>';
    }
    //编辑

    $class   = $taxonomy . '_link ' . $class;
    $url_var = array(
        'action' => $taxonomy . '_edit_modal',
        'id'     => $id,
    );

    $args = array(
        //    'new' => true,
        'new'           => $new,
        'tag'           => $tag,
        'class'         => $class,
        'text'          => $con,
        'height'        => 400,
        'data_class'    => 'full-sm',
        'mobile_bottom' => true,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取term的总查看量
 * @param {*} $term_id
 * @return {*}
 */
function zib_bbs_get_term_views_cut_count($term_id)
{
    return zib_get_term_post_views_sum($term_id, true);
}

/**
 * @description: 获取标签链接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $before
 * @param {*} $after
 * @param {*} $count
 * @return {*}
 */
function zib_bbs_get_posts_tag_link($posts_id = 0, $class = 'focus-color', $before = '', $after = '', $count = 0)
{
    $class .= ' tag-link';
    return zib_bbs_get_posts_term_links($posts_id, 'forum_tag', $class, $before, $after, $count);
}

/**
 * @description: 获取话题链接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $before
 * @param {*} $after
 * @param {*} $count
 * @return {*}
 */
function zib_bbs_get_posts_topic_link($posts_id = 0, $class = 'focus-color', $before = '', $after = '', $count = 0)
{
    $class .= ' topic-link';
    return zib_bbs_get_posts_term_links($posts_id, 'forum_topic', $class, $before, $after, $count);
}

/**
 * @description: 获取帖子的term链接
 * @param {*} $posts_id
 * @param {*} $taxonomy
 * @param {*} $class
 * @param {*} $before
 * @param {*} $after
 * @param {*} $count
 * @return {*}
 */
function zib_bbs_get_posts_term_links($posts_id = 0, $taxonomy = 'forum_topic', $class = 'but', $before = '', $after = '', $count = 0)
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    if (!$posts_id) {
        return;
    }

    $class = $class ? ' class="' . $class . '"' : '';

    $terms = get_the_terms($posts_id, $taxonomy);

    $links = '';
    if (!is_wp_error($terms) && !empty($terms[0])) {
        $i = 0;
        foreach ($terms as $term) {
            if ($count && $i == $count) {
                break;
            }

            $i++;
            $terms_name = esc_attr($term->name);
            $permalink  = get_term_link($term);
            $links .= '<a' . $class . ' href="' . $permalink . '">' . $before . $terms_name . $after . '</a>';
        }
    }
    return $links;
}

/**
 * @description: 获取term的图片
 * @param {*} $term
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_term_thumbnail($term = 0, $class = 'forum-thumbnail fit-cover', $show_default = true)
{
    if (!$term) {
        $term = get_queried_object();
    } else {
        $term = get_term($term);
    }

    $r_src = zib_get_taxonomy_img_url($term->term_id);

    if (!$r_src) {
        if (!$show_default) {
            return;
        } else {
            $r_src = zib_bbs_get_term_default_thumbnail();
        }
    }
    if (!$r_src) {
        return;
    }
    $lazy_thumb = zib_get_lazy_thumb();
    $r_attr     = '';
    $alt        = $term->name . zib_get_delimiter_blog_name();

    if (zib_is_lazy('lazy_bbs_list_thumb')) {
        return sprintf('<img' . $r_attr . ' src="%s" data-src="%s" alt="%s" class="lazyload ' . $class . '">', $lazy_thumb, $r_src, $alt);
    } else {
        return sprintf('<img' . $r_attr . ' src="%s" alt="%s" class="' . $class . '">', $r_src, $alt);
    }
}

//获取默认的随机图像
function zib_bbs_get_term_default_thumbnail()
{
    $img_ids = _pz('bbs_term_thumb');
    if ($img_ids) {
        $img_ids_array = explode(',', $img_ids);
        $rand          = array_rand($img_ids_array, 1);
        $url           = wp_get_attachment_url($img_ids_array[$rand]);
        return $url;
    }
}

/**
 * @description: 获取分类发名字
 * @param {*} $taxonomy
 * @return {*}
 */
function zib_bbs_get_taxonomy_name($taxonomy = '')
{
    global $zib_bbs;

    if (!$taxonomy) {
        $taxonomy = get_query_var('taxonomy');
    }
    $taxonomy_names = array(
        'forum_topic' => $zib_bbs->topic_name,
        'forum_tag'   => $zib_bbs->tag_name,
        'plate_cat'   => $zib_bbs->plate_name . '分类',
    );
    return isset($taxonomy_names[$taxonomy]) ? $taxonomy_names[$taxonomy] : '';
}

function zib_bbs_get_term_header($taxonomy = '', $term_id = 0, $class = '')
{
    if (!$term_id) {
        $term_id = get_queried_object_id();
    }
    if (!$taxonomy) {
        $taxonomy = get_query_var('taxonomy');
    }

    global $zib_bbs;
    $p_name = $zib_bbs->posts_name;
    if ('plate_cat' == $taxonomy) {
        $p_name = $zib_bbs->plate_name;
    }

    $term      = get_term($term_id, $taxonomy);
    $permalink = get_term_link($term);

    $taxonomy_names = zib_bbs_get_taxonomy_name($taxonomy);
    $taxonomy_badge = '<span class="badg b-theme badg-sm mb6">' . $taxonomy_names . '</span>';
    if ('plate_cat' == $taxonomy) {
        $taxonomy_badge .= zib_bbs_get_plate_cat_add_limit_btn($term_id, 'badg badg-sm b-yellow ml6 mb6');
    }

    $title = esc_attr($term->name);
    if ('forum_topic' == $taxonomy) {
        $_icon = zib_get_svg('topic');
        $title = $_icon . $title . $_icon;
    }
    $title = '<h1 class="forum-title em14 text-ellipsis"><a title="' . esc_attr($title) . '" href="' . esc_url($permalink) . '">' . $title . '</a></h1>';

    $excerpt        = esc_attr($term->description);
    $excerpt        = '<div class="desc px12-sm mt6">' . $excerpt . '</div>';
    $excerpt        = apply_filters('bbs_' . $taxonomy . '_header_excerpt', $excerpt, $term);
    $thumb          = zib_bbs_get_term_thumbnail($term);
    $thumbnail_link = $thumb ? '<div class="plate-thumb flex0 mr20">' . $thumb . '</div>' : '';
    $blur_bg        = $thumb ? '<div class="abs-blur-bg">' . $thumb . '</div><div class="absolute forum-mask"></div>' : '';
    //计数显示
    $count_mate      = '';
    $count_mate_text = '';
    $all_posts_count = _cut_count($term->count);
    $views_count     = zib_bbs_get_term_views_cut_count($term_id);

    $count_mate .= '<item class="mate-posts"><div class="em09 opacity5 mb6">' . $p_name . '数</div><div class="em14"> ' . $all_posts_count . '</div></item>';
    $count_mate .= '<item class="mate-views"><div class="em09 opacity5 mb6">阅读量</div><div class="em14"> ' . $views_count . '</div></item>';
    $count_mate      = '<div class="count-mates text-center flex0 ml10' . ($thumb ? ' hide-sm' : '') . '">' . $count_mate . '</div>';
    $count_mate_text = $thumb ? '<div class="px12 mt10 show-sm"><item class="mate-posts mr10">' . $p_name . ' ' . $all_posts_count . '</item><item class="mate-reply mr10">阅读 ' . $views_count . '</item></div>' : '';

    $more_dropdown = zib_bbs_get_term_header_more_btn($term_id, $taxonomy);

    $class .= $blur_bg ? ' blur-header' : '';

    $html = '<div class="forum-header relative-h mb20' . $class . '">';
    $html .= $blur_bg;
    $html .= $more_dropdown;
    $html .= '<div class="relative flex ac header-content">';
    $html .= $thumbnail_link;
    $html .= '<div class="item-info flex1">';
    $html .= $taxonomy_badge . $title;
    $html .= $count_mate_text;
    $html .= $excerpt;
    $html .= '</div>';
    $html .= $count_mate;
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

//为话题的头部添加按钮
function zib_bbs_forum_topic_header_excerpt_filter($excerpt, $term)
{
    global $zib_bbs;
    $term_id     = $term->term_id;
    $btns        = '';
    $term_author = get_term_meta($term_id, 'term_author', true);
    if ($term_author) {
        $term_author_data = get_userdata($term_author);
        if (!isset($term_author_data->display_name)) {
            return;
        }
        $btns .= '<a href="' . zib_get_user_home_url($term_author) . '" class="but c-white mr10 mt6">' . zib_get_avatar_box($term_author, 'avatar-mini mr6', false) . esc_attr($term_author_data->display_name) . '<i class="ml6 fa fa-angle-right em12" style="margin-right: 0;"></i></a>';
    }

    $btns .= zib_bbs_get_posts_add_page_link(array('topic_id' => $term_id), 'but c-white mt6', zib_get_svg('add') . '发布' . $zib_bbs->posts_name);
    $excerpt .= $btns ? '<div class="mt6 em09 moderator-btns">' . $btns . '</div>' : '';
    return $excerpt;
}
add_filter('bbs_forum_topic_header_excerpt', 'zib_bbs_forum_topic_header_excerpt_filter', 10, 2);

//为话题的头部添加按钮
function zib_bbs_plate_cat_header_excerpt_filter($excerpt, $term)
{
    global $zib_bbs;
    $term_id          = $term->term_id;
    $btns             = '';
    $moderator_avatar = '';
    $class            = 'but c-white mt6 mr6';

    $moderator = get_term_meta($term_id, 'moderator', true);
    if (is_array($moderator)) {
        $i = 1;
        foreach ($moderator as $user_id) {
            $moderator_avatar .= '<span class="avatar-mini moderator-avatar">' . zib_get_data_avatar($user_id) . '</span>';
            if (3 === $i) {
                break;
            }
            ++$i;
        }
    }

    if ($moderator_avatar) {
        $moderator_link = zib_bbs_get_cat_moderator_modal_link($term_id, $class, $moderator_avatar . count($moderator) . '名' . $zib_bbs->cat_moderator_name . '<i class="ml6 fa fa-angle-right em12" style="margin-right: 0;"></i>');
    } else {
        $moderator_link = zib_bbs_get_add_cat_moderator_link($term_id, $class, '添加' . $zib_bbs->cat_moderator_name . '<i class="ml6 fa fa-angle-right em12" style="margin-right: 0;"></i>');
    }
    $btns .= $moderator_link;
    $btns .= zib_bbs_get_plate_add_link($term_id, $class, zib_get_svg('add') . '创建' . $zib_bbs->plate_name);
    $excerpt .= $btns ? '<div class="mt6 em09 moderator-btns">' . $btns . '</div>' : '';
    return $excerpt;
}
add_filter('bbs_plate_cat_header_excerpt', 'zib_bbs_plate_cat_header_excerpt_filter', 10, 2);

//输出主内容
function zib_bbs_term_tab_content()
{
    global $wp_query;
    //  $post_type = $wp_query->get('post_type');
    $taxonomy = $wp_query->get('taxonomy');
    $term_id  = $wp_query->get_queried_object_id();
    $header   = zib_bbs_get_term_header($taxonomy, $term_id);

    $lists = '';
    if (have_posts()) {
        global $wp_query;
        while (have_posts()): the_post();
            $lists .= zib_bbs_get_posts_list(
                array(
                    'class'      => 'alone ajax-item',
                    'show_topic' => ('forum_topic' != $taxonomy),
                )
            );
        endwhile;
        //帖子分页
        $paginate = zib_bbs_get_paginate($wp_query->found_posts);
        if ($paginate) {
            $lists .= $paginate;
            $lists .= '<div class="post_ajax_loader" style="display:none;">' . zib_bbs_get_placeholder('posts_detail_alone') . '</div>';
        }
    } else {
        $lists .= zib_get_ajax_null('内容空空如也', 100);
    }

    $html = '';
    $html .= '<div class="term-main">';
    $html .= $header;
    $html .= '<div class="ajaxpager">';
    $html .= $lists;
    $html .= '</div>';
    $html .= '</div>';

    echo $html;
}
add_action('bbs_forum_topic_page_content', 'zib_bbs_term_tab_content');
add_action('bbs_forum_tag_page_content', 'zib_bbs_term_tab_content');

//获取搜索按钮
function zib_bbs_get_term_search_btn($term, $class = '')
{

    global $zib_bbs;
    $term  = get_term($term);
    $title = esc_attr($term->name);

    $args = array(
        'class'       => $class,
        'trem'        => $term->term_id,
        'trem_name'   => zib_str_cut($title, 0, 8),
        'type'        => 'forum',
        'placeholder' => '在' . zib_bbs_get_taxonomy_name($term->taxonomy) . '[' . $title . ']中搜索' . $zib_bbs->posts_name,
    );

    return zib_get_search_link($args);
}

function zib_bbs_get_term_share_btn($term, $class = '')
{
    return zib_get_term_share_btn($term, $class . ' btn-share', true);

    return '<a href="javascript:;" class="btn-share item em12"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-share"></use></svg></a>';
}

function zib_bbs_get_term_header_more_btn($term_id, $taxonomy, $class = '')
{

    $html = '';

    if ('forum_topic' == $taxonomy) {
        //搜索
        $share = zib_bbs_get_term_search_btn($term_id, 'item');
        $html .= $share ? $share : '';
    }

    //分享
    $share = zib_bbs_get_term_share_btn($term_id, 'item');
    $html .= $share ? $share : '';

    //更多按钮
    $dropdown = zib_bbs_get_term_more_dropdown($term_id, $taxonomy, 'pull-right', 'item mr3');
    $html .= $dropdown ? $dropdown : '';

    if (!$html) {
        return;
    }

    $class = $class ? ' ' . $class : '';
    return '<div class="flex ac more-btns' . $class . '">' . $html . '</div>';
}

/**
 * @description: 获取term更多按钮的dropdown下拉框
 * @param {*} $term_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_term_more_dropdown($term_id, $taxonomy, $class = '', $con_class = '', $con = '')
{

    if (!$term_id) {
        return;
    }

    $con       = $con ? $con : zib_get_svg('menu_2');
    $class     = $class ? ' ' . $class : '';
    $con_class = $con_class ? ' class="' . $con_class . '"' : '';

    $action = '';
    $name   = zib_bbs_get_taxonomy_name($taxonomy);

    $add = zib_bbs_get_term_edit_link($taxonomy, 0, '', zib_get_svg('add', null, 'icon mr6 fa-fw') . '创建新' . $name, 'a');
    $action .= $add ? '<li>' . $add . '</li>' : '';

    $edit = zib_bbs_get_term_edit_link($taxonomy, $term_id, '', zib_get_svg('set', null, 'icon mr6 fa-fw') . '编辑此' . $name, 'a');
    $action .= $edit ? '<li>' . $edit . '</li>' : '';

    if ('plate_cat' === $taxonomy) {
        $add_limit = zib_bbs_get_plate_cat_set_add_limit_link($term_id);
        $action .= $add_limit ? '<li>' . $add_limit . '</li>' : '';
    }

    $del = zib_bbs_get_term_modal_delete_link($taxonomy, $term_id, 'c-red', '<i class="fa fa-trash-o mr6 fa-fw"></i>删除此' . $name, 'a');
    $action .= $del ? '<li>' . $del . '</li>' : '';

    if (!$action) {
        return;
    }

    $html = '<span class="dropdown' . $class . '">';
    $html .= '<a href="javascript:;"' . $con_class . ' data-toggle="dropdown">' . $con . '</a>';
    $html .= '<ul class="dropdown-menu">';
    $html .= $action;
    $html .= '</ul>';
    $html .= '</span>';
    return $html;
}

function zib_bbs_taxonomy_names_filter($taxonomy_names)
{
    $taxonomy_names['forum_topic'] = $GLOBALS['zib_bbs']->topic_name;
    $taxonomy_names['forum_tag']   = $GLOBALS['zib_bbs']->tag_name;
    $taxonomy_names['plate_cat']   = '分类';
    return $taxonomy_names;
}

add_filter('taxonomy_names', 'zib_bbs_taxonomy_names_filter');
