<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-20 20:46:28
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|首页函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//输出首页栏目
function zib_bbs_home_tab_content()
{
    $tabs_options = apply_filters('bbs_home_tab_options', _pz('bbs_home_tab', array()));

    if (!$tabs_options) {
        return;
    }

    if (is_super_admin() || zib_bbs_user_is_forum_admin()) {
        $tabs_options['pending'] = array(
            'title'       => zib_get_svg('approve', null, 'icon mr3') . '待审核',
            'post_status' => 'pending',
            'show'        => array('pc_s', 'm_s'),
            'loader'      => zib_bbs_get_placeholder(),
        );
    }

    $tab_nav     = zib_bbs_get_home_tab_nav($tabs_options);
    $tab_content = zib_bbs_get_home_tab_content($tabs_options);

    echo '<div class="fixed-wrap bbs-home-tab">';
    echo $tab_nav ? '<div class="fixed-wrap-nav zib-widget affix-header-sm"  offset-top="-8">' . $tab_nav . '</div>' : '';
    echo '<div class="fixed-wrap-content">';
    do_action('bbs_home_tab_content_top');
    echo $tab_content;
    do_action('bbs_home_tab_content_bottom');
    echo '</div>';
    echo '</div>';
}
add_action('bbs_home_page_content', 'zib_bbs_home_tab_content');

//获取首页栏目的tab标题
function zib_bbs_get_home_tab_nav($tabs_options)
{
    return zib_bbs_get_tab_nav('nav', $tabs_options, 'home', _pz('bbs_home_tab_swiper', true), _pz('bbs_home_tab_active_index', 1));
}

//获取首页栏目的tab内容
function zib_bbs_get_home_tab_content($tabs_options)
{

    $tabs_options['plate']['loader'] = zib_bbs_get_placeholder('home_plate', 2);
    //不同的placeholder

    return zib_bbs_get_tab_nav('content', $tabs_options, 'home', _pz('bbs_home_tab_swiper', true), _pz('bbs_home_tab_active_index', 1));
}

function zib_bbs_get_home_tab_content_quick_posts($html = '')
{
    $page = zib_get_the_paged();
    //快速发布
    if (1 == $page && _pz('bbs_home_tab_quick_posts_s', true)) {
        $show_index = _pz('bbs_home_tab_quick_posts_tab', 2);
        $index      = !empty($_GET['index']) ? (int) $_GET['index'] : _pz('bbs_home_tab_active_index', 1);
        if ($show_index == $index) {
            $quick_publish_args = array(
                'class' => 'ajax-item-header zib-widget',
            );
            $html = zib_bbs_edit::quick_posts($quick_publish_args) . $html;
        }
    }

    return $html;
}
add_filter('bbs_home_tab_content', 'zib_bbs_get_home_tab_content_quick_posts', 10);

function zib_bbs_get_home_tab_content_pending($html = '', $option = array())
{
    global $post;
    $lists = '';
    $page  = zib_get_the_paged();

    $args = array(
        'plate'       => false,
        'post_status' => $option['post_status'],
    );
    $posts = zib_bbs_get_posts_query($args);
    if ($posts->have_posts()) {
        while ($posts->have_posts()): $posts->the_post();
            $lists .= zib_bbs_get_posts_manage_list('alone ajax-item', true);
        endwhile;
        //帖子分页paginate
        $paginate = zib_bbs_get_paginate($posts->found_posts);
        if ($paginate) {
            $lists .= $paginate;
            $lists .= '<div class="post_ajax_loader" style="display:none;">' . zib_bbs_get_placeholder('home') . '</div>';
        }
        wp_reset_query();
    }

    if (!$lists) {
        $msg_args = array(
            'trash'   => '回收站暂无内容',
            'pending' => '暂无待审核内容',
        );
        $lists = zib_get_ajax_null($msg_args[$option['post_status']]);
    }

    $html = $lists;
    return $html;
}
add_filter('bbs_home_tab_content_pending', 'zib_bbs_get_home_tab_content_pending', 10, 2);

//挂钩首页版块tab内容
function zib_bbs_get_home_tab_content_plate($html = '', $option = array())
{

    $can_plate_add = zib_bbs_current_user_can('plate_add'); //判断是否有添加版块的权限)

    $orderby = !empty($option['cat_orderby']) ? $option['cat_orderby'] : 'count';
    $include = false;
    if ('include' == $orderby) {
        $include = $option['orderby_include'];
    }

    $cat_objs = zib_bbs_get_plate_cats_orderby($orderby, !$can_plate_add, $include);

    //添加前面挂钩
    $html          = apply_filters('bbs_home_tab_content_plate_before', $html, $option);
    $i             = 1;
    $plate_orderby = !empty($option['orderby']) ? $option['orderby'] : 'count';
    $is_mobile     = wp_is_mobile();
    if ($cat_objs) {
        foreach ($cat_objs as $cat_obj) {
            $add_link = zib_bbs_get_plate_add_link($cat_obj->term_id);
            $id       = 'home-plate-panel-' . $i;
            $title    = esc_attr($cat_obj->name);
            $name     = '<span class="title-theme" title="' . $title . '"><span class="text-ellipsis">' . $title . '</span>' . $add_link . '</span>';

            //折叠标题
            $top = '<h4 class="panel-title">';
            $top .= $is_mobile ? $name : '<a class="flex ac jsb" data-toggle="collapse" href="#' . $id . '">' . $name . '<span>' . '<i class="ml10 mr10 fa fa-angle-up em14 muted-2-color"></i></span></a>';
            $top .= '</h4>';

            $lists = zib_bbs_get_plate_main_lists(array('cat' => $cat_obj->term_id, 'orderby' => $plate_orderby));

            if (!$lists) {
                global $zib_bbs;
                $add_btn_con = '<span class="badg cir mr10 jb-blue em14">' . zib_get_svg('add') . '</span>创建新' . $zib_bbs->plate_name;
                $add_link    = zib_bbs_get_plate_add_link($cat_obj->term_id, 'plate-item flex ac forum-title', $add_btn_con);
                $lists       = $add_link;
            }

            if ($lists) {
                //折叠内容
                $panel_content = '<div id="' . $id . '" class="panel-collapse collapse in">';
                $panel_content .= '<div class="plate-lists">';
                $panel_content .= $lists;
                $panel_content .= '</div>';
                $panel_content .= '</div>';

                $html .= '<div class="panel panel-plate">';
                $html .= $top;
                $html .= $panel_content;
                $html .= '</div>';
            }

            $i++;
        }
    }

    //后挂钩
    $html = apply_filters('bbs_home_tab_content_plate_after', $html, $option);

    if (!$html) {
        $html = zib_get_null('暂时没有版块', 60, 'null.svg', '', 0, 170);
        $html .= '<div class="text-center mt20">' . zib_bbs_get_plate_add_link(0, 'but hollow c-blue padding-lg') . '</div>';
    }
    $html = '<div class="ajax-item">' . $html . '</div>';
    $html .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';

    return $html;
}
add_filter('bbs_home_tab_content_plate', 'zib_bbs_get_home_tab_content_plate', 10, 2);

//获取首页用户关注的版块
function zib_bbs_get_home_tab_content_follow_plate($html, $option)
{
    //没有开启则关闭
    $user_id = get_current_user_id();
    if (empty($option['user_follow']['s']) || !$user_id) {
        return $html;
    }

    $args = array(
        'showposts' => 20, //最多显示20
        'orderby'   => 'today_reply_count',
    );
    //第一步通过缓存获取
    $follow_plate = wp_cache_get($user_id, 'bbs_user_follow_plate', true);
    if (false === $follow_plate) {
        $follow_plate = zib_get_user_meta($user_id, 'follow_plate', true);
        wp_cache_set($user_id, $follow_plate, 'bbs_user_follow_plate');
    }
    if (!$follow_plate) {
        return $html;
    } else {
        $args['post__in'] = $follow_plate;
    }

    $slide_card = zib_bbs_get_plate_slide_card($args, 'mini', false, true);
    if (!$slide_card) {
        return $html;
    }

    $title = !empty($option['user_follow']['title']) ? $option['user_follow']['title'] : '我关注的版块';

    $html .= '<div class="panel panel-plate">';
    $html .= '<h4 class="panel-title" title="' . esc_attr($title) . '"><span class="title-theme">' . $title . '</span></h4>';
    $html .= $slide_card;
    $html .= '</div>';

    return $html;
}
add_filter('bbs_home_tab_content_plate_before', 'zib_bbs_get_home_tab_content_follow_plate', 9, 2);

//获取首页推荐版块
function zib_bbs_get_home_tab_content_hot_plate($html = '', $option = array())
{
    //没有开启则关闭
    if (empty($option['hot_plate']['s'])) {
        return $html;
    }

    $args = array(
        'showposts' => $option['hot_plate']['count'],
    );

    $orderby = !empty($option['hot_plate']['orderby']) ? $option['hot_plate']['orderby'] : 'count';
    if ('include' === $orderby && !empty($option['hot_plate']['orderby_include'])) {
        $include          = $option['hot_plate']['orderby_include'];
        $args['post__in'] = $include;
        $args['orderby']  = 'post__in';
    } else {
        $args['orderby'] = $orderby;
    }

    $slide_card = zib_bbs_get_plate_slide_card($args);
    if (!$slide_card) {
        return $html;
    }

    $title = !empty($option['hot_plate']['title']) ? $option['hot_plate']['title'] : '热门推荐';

    $html .= '<div class="panel panel-plate">';
    $html .= '<h4 class="panel-title" title="' . esc_attr($title) . '"><span class="title-theme">' . $title . '</span></h4>';
    $html .= $slide_card;
    $html .= '</div>';

    return $html;
}
add_filter('bbs_home_tab_content_plate_before', 'zib_bbs_get_home_tab_content_hot_plate', 9, 2);

//挂钩首页版块tab内容:其它版块|帖子列表版
function zib_bbs_get_home_tab_content_other($html = '', $option = array())
{
    $page          = zib_get_the_paged();
    $include_plate = !empty($option['include_plate']) ? $option['include_plate'] : false;
    $include_tag   = !empty($option['include_tag']) ? $option['include_tag'] : false;
    $include_topic = !empty($option['include_topic']) ? $option['include_topic'] : false;
    $exclude_plate = !$include_plate && !empty($option['exclude_plate']) ? $option['exclude_plate'] : false;
    $orderby       = !empty($option['orderby']) ? $option['orderby'] : 'date';
    $plate_info    = !isset($option['plate_info']);
    $term_info     = !isset($option['term_info']);
    $style         = !empty($option['style']) ? $option['style'] : 'detail';
    $topping_s     = !empty($option['topping_s']);
    $aiax_class    = _pz('bbs_posts_paginate_type', 'ajax_lists') !== 'ajax_lists' || 1 == $page ? 'ajax-item' : '';
    $lists         = '';

    if (!$plate_info) {
        $lists .= zib_bbs_get_plate_header($include_plate, $aiax_class);
    }
    if (!$term_info) {
        if ((int) $include_topic) {
            $lists .= zib_bbs_get_term_header('forum_topic', (int) $include_topic, $aiax_class . ' mb20');
        } elseif ((int) $include_tag) {
            $lists .= zib_bbs_get_term_header('forum_tag', (int) $include_tag, $aiax_class . ' mb20');
        }
    }

    $args = array(
        'plate'         => $include_plate,
        'plate_exclude' => $exclude_plate,
        'topic'         => $include_topic,
        'tag'           => $include_tag,
        'orderby'       => $orderby,
    );
    if (isset($option['bbs_type'])) {
        //类型筛选
        $args['bbs_type'] = $option['bbs_type'];
    }
    if (isset($option['filter'])) {
        //其它筛选
        $args['filter'] = $option['filter'];
    }
    if (isset($option['allow_view'])) {
        //阅读权限筛选
        $args['allow_view'] = $option['allow_view'];
    }

    if ($topping_s) {
        if (1 == $page) {
            //在第一页显示置顶内容
            $topping_posts = zib_bbs_get_topping_posts_query(true);
            if ($topping_posts->have_posts()) {
                while ($topping_posts->have_posts()): $topping_posts->the_post();
                    if ('detail' == $style) {
                        $lists .= zib_bbs_get_posts_list(
                            array(
                                'class'        => 'alone ajax-item',
                                'show_plate'   => $plate_info,
                                'show_topping' => true,
                            )
                        );
                    } else {
                        $lists .= zib_bbs_get_posts_mini_list('alone ajax-item', true);
                    }
                endwhile;
                wp_reset_query();
            }
        }
        //其他内容排除置顶
        $args['topping'] = array('!=', '3');
    }

    if (isset($option['post_status']) && $option['post_status'] === 'pending') {
        $args['post_status'] = array('pending');
    }

    $posts = zib_bbs_get_posts_query($args);
    if ($posts->have_posts()) {
        while ($posts->have_posts()): $posts->the_post();
            if ('detail' == $style) {
                $lists .= zib_bbs_get_posts_list(
                    array(
                        'class'      => 'alone ajax-item',
                        'show_plate' => $plate_info,
                    )
                );
            } else {
                $lists .= zib_bbs_get_posts_mini_list('alone ajax-item');
            }
        endwhile;
        //帖子分页paginate
        $paginate = zib_bbs_get_paginate($posts->found_posts);
        if ($paginate) {
            $lists .= $paginate;
            $lists .= '<div class="post_ajax_loader" style="display:none;">' . zib_bbs_get_placeholder('home') . '</div>';
        }
        wp_reset_query();
    }

    if (!$lists) {
        $lists = zib_get_ajax_null('内容空空如也');
        if (1 == $page) {
            global $zib_bbs;
            $plate_add = zib_bbs_get_plate_add_link(0, 'ml10 mr10 mt10 but hollow c-blue padding-lg', '创建' . $zib_bbs->plate_name);
            $posts_add = zib_bbs_get_posts_add_page_link(0, 'ml10 mr10 mt10 but hollow c-green padding-lg', '发布' . $zib_bbs->posts_name);
            $lists .= '<div class="text-center mb20">';
            $lists .= $plate_add;
            $lists .= $posts_add;
            $lists .= '</div>';
        }
    }

    $html = $lists;
    return $html;
}
add_filter('bbs_home_tab_content_other', 'zib_bbs_get_home_tab_content_other', 10, 2);
add_filter('bbs_home_tab_content_synthesis', 'zib_bbs_get_home_tab_content_other', 10, 2);

//首页关注的tab的版块推荐
function zib_bbs_get_home_tab_content_follow_tab_plate($html = '', $option = array())
{
    $page = zib_get_the_paged();
    //没有开启则关闭
    if (empty($option['plate']['s']) || 1 != $page) {
        return $html;
    }

    $args = array(
        'showposts' => $option['plate']['count'],
        'orderby'   => $option['plate']['orderby'],
    );
    $user_id = get_current_user_id();
    if ($user_id) {
        $follow_plate = wp_cache_get($user_id, 'bbs_user_follow_plate', true);
        if (false === $follow_plate) {
            $follow_plate = zib_get_user_meta($user_id, 'follow_plate', true);
            wp_cache_set($user_id, $follow_plate, 'bbs_user_follow_plate');
        }
        if ($follow_plate) {
            $args['post__not_in'] = $follow_plate; //排除自己已经关注的版块
        }
    }

    $slide_card = zib_bbs_get_plate_slide_card($args);
    if (!$slide_card) {
        return $html;
    }

    $title = !empty($option['hot_plate']['title']) ? $option['hot_plate']['title'] : '热门推荐';

    $html .= '<div class="ajax-item mb20">';
    $html .= '<div class="box-body notop"><div class="title-theme">' . $title . '</div></div>';
    $html .= $slide_card;
    $html .= '</div>';

    return $html;
}
add_filter('bbs_home_tab_content_follow', 'zib_bbs_get_home_tab_content_follow_tab_plate', 9, 2);

//首页关注的tab
function zib_bbs_get_home_tab_content_follow($html = '', $option = array())
{
    global $zib_bbs;
    $page    = zib_get_the_paged();
    $orderby = !empty($option['orderby']) ? $option['orderby'] : 'date';
    $style   = !empty($option['style']) ? $option['style'] : 'detail';
    $user_id = get_current_user_id();

    if (!$user_id) {
        $html .= zib_get_null('登录后继续查看', 20, 'null-user.svg', '', 0, 220) . zib_get_user_singin_page_box('mb20', '');
        return zib_get_ajax_ajaxpager_one_centent($html);
    }

    $follow = zib_get_user_meta($user_id, 'follow_plate', true);
    $lists  = '';
    if ($follow) {
        $args = array(
            'plate'   => $follow,
            'orderby' => $orderby,
        );

        $posts = zib_bbs_get_posts_query($args);
        if ($posts->have_posts()) {
            while ($posts->have_posts()): $posts->the_post();
                if ('detail' == $style) {
                    $lists .= zib_bbs_get_posts_list(
                        array(
                            'class' => 'alone ajax-item',
                        )
                    );
                } else {
                    $lists .= zib_bbs_get_posts_mini_list('alone ajax-item');
                }
            endwhile;
            //帖子分页paginate
            $paginate = zib_bbs_get_paginate($posts->found_posts);
            if ($paginate) {
                $lists .= $paginate;
                $lists .= '<div class="post_ajax_loader" style="display:none;">' . zib_bbs_get_placeholder('home') . '</div>';
            }
            wp_reset_query();
        }
    }
    if (!$lists) {
        $lists = zib_get_ajax_null('暂无内容', 60, 'null-love.svg');
        if (1 == $page) {

        }
    }

    $html .= $lists;
    return $html;
}
add_filter('bbs_home_tab_content_follow', 'zib_bbs_get_home_tab_content_follow', 10, 2);
