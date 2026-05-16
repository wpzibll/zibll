<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-05-05 21:25:21
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|工具函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//引入资源文件
zib_require(array(
    'class.admin',
    'class.init',
    'setup',
    'plate',
    'template',
    'posts',
    'user',
    'term',
    'home',
    'single',
    'comment',
    'plate-cat',
    'edit',
    'edit-posts',
    'moderator',
    'msg',
), false, ZIB_BBS_REQUIRE_URI . 'inc/');

/**
 * @description: 制作new WP_Query orderby的args
 * @param {*} $orderby
 * @param {*} $args
 * @return {*}
 */
function zib_bbs_query_orderby_filter($orderby = 'date', $args = array())
{
    return zib_query_orderby_filter($orderby, $args);
}

/**
 * @description: 判断自己是不是文章的作者
 * @param {*} $post
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_is_the_author($post = null, $user_id = null)
{
    return zib_is_the_author($post, $user_id);
}

/**
 * @description: 获取帖子或者版块状态post_status的徽章
 * @param {*} $class
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_status_badge($class = '', $post = null)
{
    return zib_get_post_status_badge($class, $post);
}

/**
 * @description: 分页按钮统一接口
 * @param {*} $count_all 总数
 * @param {*} $ice_perpage 每页数量
 * @param {*} $page 当前页
 * @param {*} $ajax_url 加载地址
 * @return {*} html
 */
function zib_bbs_get_paginate($count_all, $page = 0, $ice_perpage = 0, $ajax_url = false, $type = null, $ias = 'auto')
{
    if ($count_all < $ice_perpage) {
        return;
    }

    global $wp_rewrite;
    $ice_perpage = $ice_perpage ? $ice_perpage : _pz('bbs_posts_per_page', 20);
    $type        = $type ? $type : _pz('bbs_posts_paginate_type', 'ajax_lists');
    $ice_perpage = $ice_perpage ? $ice_perpage : 20;

    if (!$page) {
        $page = zib_get_the_paged();
    }

    if (!$ajax_url) {
        $ajax_url = zib_url_del_paged(zib_get_current_url());

        if ($wp_rewrite->using_permalinks()) {
            $url_parts = explode('?', $ajax_url);
            if (isset($url_parts[0])) {
                $url_base = rtrim($url_parts[0], '/\\') . '/' . $wp_rewrite->pagination_base . '/%#%';
                $url_base .= isset($url_parts[1]) ? '?' . $url_parts[1] : '';
            } else {
                $url_base = rtrim($ajax_url, '/\\') . '/' . $wp_rewrite->pagination_base . '/%#%';
            }
        } else {
            $url_base = add_query_arg('paged', '%#%', $ajax_url);
        }
    } else {
        $ajax_url = preg_replace("/\/$wp_rewrite->pagination_base\/\d*/", '', $ajax_url);
        $url_base = add_query_arg('paged', '%#%', $ajax_url);
    }

    $next_class = 'next-page ajax-next';
    if ('ajax_lists' === $type) {
        //AJAX追加列表翻页模式
        $total_pages = ceil($count_all / $ice_perpage);
        $con         = '';
        if ($total_pages > $page) {
            $nex = _pz('ajax_trigger', '加载更多');

            $href = str_replace('%#%', $page + 1, $url_base);

            //自动加载下一页
            $ias_attr = ' class="' . $next_class . '"';
            if ($ias !== 'close') {
                $ias_max  = $ias === 'auto' ? _pz('bbs_posts_paginate_ias_max', 3) : (int) $ias;
                $ias_attr = (_pz('bbs_posts_paginate_ias_s', true) && ($page <= $ias_max || !$ias_max)) ? ' class="' . $next_class . ' lazyload" lazyload-action="ias"' : ' class="' . $next_class . '"';
            }

            $con .= '<div class="text-center theme-pagination ajax-pag"><div' . $ias_attr . '>';
            $con .= '<a href="' . esc_url($href) . '">' . $nex . '</a>';
            $con .= '</div></div>';
        }
        return $con;
    } else {
        //数字翻页模式
        $args = array(
            'url_base'     => $url_base,
            'link_sprintf' => '<a class="' . $next_class . ' %s" ajax-replace="true" href="%s">%s</a>',
            'total'        => $count_all, //总计条数
            'current'      => $page, //当前页码
            'page_size'    => $ice_perpage, //每页几条
            'class'        => 'pagenav ajax-pag',
        );

        return zib_get_paginate_links($args);
    }
}

/**
 * @description: 论坛的主要TAB统一接口|一个页面只能有一个这样的调用
 * @param {*} $type
 * @param {*} $tabs_options
 * @param {*} $id_prefix
 * @param {*} $is_swiper
 * @return {*}
 */
function zib_bbs_get_tab_nav($type = 'nav', $options = array(), $id_prefix = 'home', $is_mobile_swiper = true, $active_index = 1)
{
    $active_index = !empty($_GET['index']) ? $_GET['index'] : $active_index;

    $active_index = (int) $active_index;

    $is_mobile           = wp_is_mobile();
    $is_swiper           = $is_mobile_swiper ? $is_mobile : false;
    $placeholder_default = zib_bbs_get_placeholder($id_prefix);
    $ajax_url            = zib_url_del_paged(zib_get_current_url());
    $html                = '';

    if (!$options || !is_array($options)) {
        return '';
    }

    //对数据进行处理
    $tabs_options = array();
    foreach ($options as $key => $opt) {
        if ($key && in_array($key, ['tabs', 'tabs_2', 'tabs_3'])) {
            foreach ($opt as $key => $opt_2) {
                if (isset($opt_2['show']) && ((in_array('pc_s', $opt_2['show']) && !$is_mobile) || (in_array('m_s', $opt_2['show']) && $is_mobile))) {
                    $tabs_options[] = $opt_2;
                }
            }
        } elseif (isset($opt['show']) && ((in_array('pc_s', $opt['show']) && !$is_mobile) || (in_array('m_s', $opt['show']) && $is_mobile))) {
            if (is_string($key)) {
                $tabs_options[$key] = $opt;
            } else {
                $tabs_options[] = $opt;
            }
        }
    }

    if (!$active_index || count($tabs_options) < $active_index) {
        $active_index = 1;
    }

    $i = 1;
    foreach ($tabs_options as $key => $opt) {
        $id = $id_prefix . '-tab-' . $i;

        $query_arg['index'] = $i;
        $ajax_href          = esc_url(add_query_arg($query_arg, $ajax_url));
        //开始构建
        if ('nav' == $type) {
            $is_active = $i == $active_index ? ' class="active"' : '';
            //nav按钮
            $name      = $opt['title'] ? $opt['title'] : '栏目';
            $href_attr = ' href="' . $ajax_href . '" data-route="' . $ajax_href . '"';

            $html .= $is_swiper
                ? '<li class="swiper-slide"><a' . $href_attr . ' tab-id="' . $id . '">' . $name . '</a></li>'
                : '<li' . $is_active . '><a' . $href_attr . ' data-toggle="tab" data-ajax="" data-target="#' . $id . '">' . $name . '</a></li>';
        } else {
            $is_active = $i == $active_index ? ' in active' : '';

            $loader = isset($opt['loader']) ? $opt['loader'] : $placeholder_default;

            $c_class = $is_swiper ? 'swiper-slide' : 'tab-pane fade' . $is_active;
            $html .= '<div class="ajaxpager ' . $c_class . '" id="' . $id . '">';
            $_key = is_string($key) ? $key : 'other';

            if (!$is_active) {
                $html .= '<span class="post_ajax_trigger hide"><a href="' . esc_url($ajax_href) . '" class="ajax_load ajax-next ajax-open"></a></span>';
                $html .= '<div class="post_ajax_loader">' . $loader . '</div>';
            } else {
                //第一页则直接显示内容
                $opt['index'] = $i;
                $content      = apply_filters('bbs_' . $id_prefix . '_tab_content_' . $_key, '', $opt);
                $html .= apply_filters('bbs_' . $id_prefix . '_tab_content', $content, $opt);
            }
            $html .= '</div>';
        }
        $i++;
    }

    if ('nav' == $type) {
        $html = $is_swiper ? '<div class="swiper-tab-nav swiper-scroll tab-nav-theme" swiper-tab-nav="tab-' . $id_prefix . '" scroll-nogroup="true"><div class="swiper-wrapper">' . $html . '</div></div>' : '<ul class="list-inline scroll-x mini-scrollbar tab-nav-theme">' . $html . '</ul>';
    } else {
        $html = $is_swiper ? '<div class="swiper-tab" swiper-tab="tab-' . $id_prefix . '" active-index="' . ($active_index - 1) . '"><div class="swiper-wrapper">' . $html . '</div></div>' : '<div class="tab-content bbs-main-tab-content">' . $html . '</div>';
    }

    return $html;
}

/**
 * @description: 获取特色图片的img内容
 * @param {*} $size
 * @param {*} $class
 * @param {*} $url
 * @return {*}
 */
function zib_bbs_get_thumbnail($post = null, $class = 'forum-thumbnail fit-cover')
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    $thumbnail_url = zib_bbs_get_thumbnail_url($post);
    $lazy_thumb    = zib_get_lazy_thumb();
    $r_attr        = '';
    $alt           = $post->post_title . zib_get_delimiter_blog_name();
    if (!$thumbnail_url) {
        $thumbnail_url = zib_get_spare_thumb();
        $r_attr        = ' data-thumb="default"';
    }
    if (zib_is_lazy('lazy_bbs_list_thumb')) {
        return sprintf('<img' . $r_attr . ' src="%s" data-src="%s" alt="%s" class="lazyload ' . $class . '">', $lazy_thumb, $thumbnail_url, $alt);
    } else {
        return sprintf('<img' . $r_attr . ' src="%s" alt="%s" class="' . $class . '">', $thumbnail_url, $alt);
    }
}

function zib_bbs_get_thumbnail_url($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (!isset($post->ID)) {
        return;
    }

    $r_src = zib_get_post_meta($post->ID, 'thumbnail_url', true);
    if (!$r_src && 'forum_post' === $post->post_type) {
        $posts_img = zib_get_post_img_urls($post);
        $r_src     = isset($posts_img[0]) ? $posts_img[0] : '';
    }

    return $r_src;
}

/**
 * @description: 获取版块或者帖子的热门徽章
 * @param {*} $id int 帖子或者版块的id
 * @param {*} $class
 * @param {*} $tag
 * @return {*}
 */
function zib_bbs_get_hot_badge($id, $class = 'img-badge top jb-red px12', $tag = 'span')
{
    $is_hot = wp_cache_get($id, 'bbs_is_hot');
    if (false === $is_hot) {
        $is_hot = get_post_meta($id, 'is_hot', true);
    }

    if (!$is_hot) {
        return;
    }

    $html = '<' . $tag . ' class="hot-badge ' . $class . '" title="热门" data-toggle="tooltip">' . zib_get_svg('hot-fill') . '</' . $tag . '>';
    return $html;
}

/**
 * @description: 通过缓存的方式获取posts_meta
 * @param {*} $meta meta类型：follow_count | views | follow_count
 * @param {*} $id 帖子或者版块的id
 * @return {*}
 */
function zib_bbs_get_posts_meta($meta, $id)
{
    if (!$id) {
        global $post;
        $id = $post->ID;
    }

    //第一步通过缓存获取
    $cache_num = wp_cache_get($id, 'plate_' . $meta . '_count', true);
    if (false !== $cache_num) {
        return $cache_num;
    }

    //第二步通过全局变量获取
    global $bbs_plate_meta_count;
    if (!isset($bbs_plate_meta_count[$meta][$id])) {
        //第三步通过数据库查询
        $val = get_post_meta($id, $meta, true);
        //保存到全局变量
        $GLOBALS['bbs_plate_meta_count'][$meta][$id] = $val;
    } else {
        $val = $bbs_plate_meta_count[$meta][$id];
    }

    //添加缓存，长期有效
    wp_cache_set($id, $val, 'plate_' . $meta . '_count');
    return $val;
}

//作者总获赞
function zib_bbs_get_user_posts_meta_sum($user_id, $mata)
{
    return get_user_posts_meta_sum($user_id, $mata);
}

//数字不能为负数
function zib_number_not_negative($number = 0)
{
    if (is_string($number)) {
        $number = strip_tags($number); // No HTML
        $number = preg_replace('/[^0-9-]/', '', $number); // No number-format
    } elseif (!is_numeric($number)) {
        $number = 0;
    }
    $int                = intval($number);
    $not_less_than_zero = max(0, $int);
    return (int) $not_less_than_zero;
}

//更新版块的文章数量
function zib_bbs_updata_plate_posts_count($plate_id)
{
    //文章数量
    wp_cache_delete($plate_id, 'plate_posts_count_all');
    wp_cache_delete($plate_id, 'plate_posts_count_today');
    update_post_meta($plate_id, 'posts_count', zib_bbs_get_plate_posts_count($plate_id));
}

//保存文章时候，必要的保存数据行为
function zib_bbs_updata_posts_mata($post_ID)
{
    //保存该文章
    $post = get_post($post_ID);
    if (empty($post->ID)) {
        return;
    }

    $post_type = $post->post_type;

    if ('forum_post' == $post_type) {
        //保存的文章是帖子
        $plate_id = get_post_meta($post->ID, 'plate_id', true);
        if ($plate_id) {
            //更新版块文章数量
            zib_bbs_updata_plate_posts_count($plate_id);
            //更新版块的回帖数量
            zib_bbs_updata_plate_reply_count($plate_id);

            if ('publish' == $post->post_status) {
                //如果是发布文章
                $current_time = $post->post_date;
                //更新版块的最后发帖时间
                update_post_meta($plate_id, 'last_post', $current_time);
                //更新版块分类的最后发帖时间
                $plate_cat = get_the_terms($plate_id, 'plate_cat');
                if ($plate_cat) {
                    foreach ($plate_cat as $c_id) {
                        update_term_meta($c_id->term_id, 'last_post', $current_time);
                    }
                }
            }
        }
    }
    if ('plate' == $post_type) {
        //保存的文章是版块
        //更新版块文章数量
        zib_bbs_updata_plate_posts_count($post->ID);
        //更新版块的回帖数量
        zib_bbs_updata_plate_reply_count($post->ID);
    }
}
add_action('save_post', 'zib_bbs_updata_posts_mata', 99);

//更新版块的回帖数量
function zib_bbs_updata_plate_reply_count($plate_id)
{
    //回帖数量
    wp_cache_delete($plate_id, 'plate_reply_count_all');
    wp_cache_delete($plate_id, 'plate_reply_count_today');
    update_post_meta($plate_id, 'reply_count', zib_bbs_get_plate_reply_count($plate_id));
    update_post_meta($plate_id, 'today_reply_count', zib_bbs_get_plate_reply_count($plate_id, true));
}

//当帖子的评论数量发生改变时候更新对应的回帖数量
function zib_bbs_updata_comment_count($post_id, $new)
{
    $post      = get_post($post_id);
    $post_type = $post->post_type;

    if ('forum_post' == $post_type) {
        //保存的文章是帖子
        $plate_id = get_post_meta($post->ID, 'plate_id', true);
        if ($plate_id) {
            //更新版块文章数量
            zib_bbs_updata_plate_reply_count($plate_id);
        }
    }
}
add_action('wp_update_comment_count', 'zib_bbs_updata_comment_count', 11, 2);

//更新版块回帖时间
function zib_bbs_updata_plate_reply_time($comment)
{
    //回帖数量
    $comment   = get_comment($comment);
    $post      = get_post($comment->comment_post_ID);
    $post_type = $post->post_type;

    //  wp_send_json_success([$comment, $post]);

    if (1 == $comment->comment_approved && 'forum_post' == $post_type && 'publish' == $post->post_status) {
        $current_time = $comment->comment_date;

        //更新自己的最后回复时间
        update_post_meta($post->ID, 'last_reply', $current_time);

        $plate_id = get_post_meta($post->ID, 'plate_id', true);
        if ($plate_id) {
            //更新版块的最会回复时间
            update_post_meta($plate_id, 'last_reply', $current_time);
            //更新版块分类的最后回帖时间
            $plate_cat = get_the_terms($plate_id, 'plate_cat');
            if ($plate_cat) {
                foreach ($plate_cat as $c_id) {
                    update_term_meta($c_id->term_id, 'last_reply', $current_time);
                }
            }
        }
    }
}
add_action('comment_post', 'zib_bbs_updata_plate_reply_time', 99);
add_action('comment_unapproved_to_approved', 'zib_bbs_updata_plate_reply_time', 99);

//更新阅读数量|版块|版块分类
function zib_bbs_updata_views($post_id = 0)
{
    $plate_id = zib_bbs_get_plate_id($post_id);

    if ($plate_id) {
        //更新版块的总查看数量
        $count = zib_bbs_get_plate_posts_views_sum($plate_id);
        wp_cache_set($plate_id, $count, 'plate_views_count');
        update_post_meta($plate_id, 'views', $count);

        $term_views_objs = zib_get_term_post_views_objs($plate_id);
        if ($term_views_objs) {
            foreach ($term_views_objs as $term) {
                wp_cache_set($term->id, $term->views, 'term_views_count');
                update_term_meta($term->id, 'views', $term->views);
            }
        }
    }
}
add_action('posts_views_record', 'zib_bbs_updata_views');

/**
 * @description: 查询板块下的的文章阅读量总和
 * @param {*} $post_id
 * @return {*}
 */
function zib_bbs_get_plate_posts_views_sum($plate_id)
{

    global $wpdb;

    $db = zibDB::table($wpdb->postmeta)->alias('_pm');
    $db->join([$wpdb->posts, '_p'], '_pm.post_id = _p.ID AND _p.post_status = "publish"');
    $db->where('_pm.meta_key', 'views');
    $db->where('_pm.post_id', 'in', zibDB::table($wpdb->postmeta)->where('meta_key', 'plate_id')->where('meta_value', $plate_id)->field('post_id'));
    $query = $db->sum('_pm.meta_value');

    return (int) $query;
}

//更新判断是否是热门
function zib_bbs_updata_is_hot($post_id = 0)
{
    $post = get_post($post_id);
    if (empty($post->ID)) {
        return;
    }

    try {
        //热门帖子判断
        $post_type = $post->post_type;
        //判断是否是帖子
        if (!$post_id || 'forum_post' !== $post_type) {
            return;
        }

        //判断是否有版块
        $plate = zib_bbs_get_the_plate($post);
        if (empty($plate->ID)) {
            return;
        }

        $plate_id = $plate->ID;

        $views = get_post_meta($post_id, 'views', true); //帖子的阅读量
        $score = get_post_meta($post_id, 'score', true); //帖子的评分

        $comment_count = $post->comment_count;
        $plate_views   = get_post_meta($plate_id, 'views', true); //版块的阅读量
        $posts_count   = zib_bbs_get_plate_posts_count($plate_id); //版块文章总数量
        $posts_opt     = _pz('is_hot_posts');
        $opt_score     = isset($posts_opt['score']) ? $posts_opt['score'] : 2;

        $is_hot = (
            $views > $posts_opt['views'] && /**帖子的阅读量大于设定值 */
            $score > $opt_score && /**评分大于设定值 */
            $comment_count > $posts_opt['comment'] && /**帖子的评论数大于设定值 */
            ($views > ($plate_views / $posts_count * ($posts_opt['average']))) /** 阅读量高于平均值的多少倍*/
        );

        /**
        exit(json_encode(array(
        'views'          => $views,
        'views2'         => $posts_opt['views'],
        'score'          => $score,
        'score2'         => $opt_score,
        'comment_count'  => $comment_count,
        'comment_count2' => $posts_opt['comment'],
        'plate_views'    => $plate_views,
        'average'        => ($plate_views / $posts_count * ($posts_opt['average'])),
        'average2'       => $posts_opt['average'],
        'is_hot'         => $is_hot,
        )));
         */

        $is_hot = ($is_hot ? 1 : 0);
        $is_hot = apply_filters('bbs_is_hot_posts', $is_hot, $post);

        if (update_post_meta($post_id, 'is_hot', $is_hot) && $is_hot) {
            do_action('posts_is_hot', $post);
        }

        wp_cache_set($post_id, $is_hot, 'bbs_is_hot');

        //开始设置热门版块
        $plate_cat = get_the_terms($plate_id, 'plate_cat');
        if (isset($plate_cat[0])) {

            $is_hot       = 0;
            $plate_cat_id = $plate_cat[0]->term_id;
            $posts_count  = $plate_cat[0]->count;

            $cat_views       = get_term_meta($plate_cat_id, 'views', true); //整个分区的总阅读量
            $p_comment_count = get_post_meta($plate_id, 'reply_count', true);
            $p_posts_count   = get_post_meta($plate_id, 'posts_count', true);

            $p_opt = _pz('is_hot_plate');

            $is_hot = (
                $p_posts_count > $p_opt['posts_count'] && /**版块的帖子数量大于设置值 */
                $plate_views > $p_opt['views'] && /**版块的阅读量大于设置值 */
                $p_comment_count > $p_opt['comment'] && /**版块的评论量大于设置值 */
                ($plate_views > ($cat_views / $posts_count * ($p_opt['average']))) /**版块的阅读量高于平均值的多少倍 */
            );
            $is_hot = ($is_hot ? 1 : 0);
            $is_hot = apply_filters('bbs_is_hot_plate', $is_hot, $post);

            if (update_post_meta($plate_id, 'is_hot', $is_hot) && $is_hot) {
                do_action('plate_is_hot', $plate);
            }
            wp_cache_set($plate_id, $is_hot, 'bbs_is_hot');
        }
    } catch (Exception $e) {
    }
}
add_action('posts_views_record', 'zib_bbs_updata_is_hot');

//海报分享数据
function zib_bbs_poster_share_data_filter($data, $obj, $id)
{
    if (isset($obj->post_type)) {
        //文章模式
        if ('forum_post' == $obj->post_type) {
            global $zib_bbs;
            $author   = get_userdata($obj->post_author)->display_name;
            $plate_id = zib_bbs_get_plate_id($obj->ID);
            $plate    = get_post($plate_id);
            $title    = trim(strip_tags($plate->post_title));
            $title    = zib_str_cut($title, 0, 12);

            $data['tags']   = '作者: ' . esc_attr($author) . ($title ? ' · ' . $zib_bbs->plate_name . ': ' . $title : '');
            $data['banner'] = zib_bbs_get_thumbnail_url($obj);
        }

        if ('plate' == $obj->post_type) {
            global $zib_bbs;
            $plate_id        = $obj->ID;
            $all_posts_count = zib_bbs_get_plate_posts_cut_count($plate_id);
            $tags            = $zib_bbs->posts_name . ': ' . $all_posts_count;

            $reply_count = zib_bbs_get_plate_reply_cut_count($plate_id);
            $tags .= $reply_count ? ' · ' . $zib_bbs->comment_name . ': ' . $reply_count : '';

            $views = zib_bbs_get_plate_views_cut_count($plate_id);
            $tags .= $views ? ' · 热度: ' . $views : '';

            $data['tags'] = $tags;
            if (!$data['banner']) {
                $data['banner'] = zib_bbs_get_thumbnail_url($obj);
            }
        }

    } elseif (isset($obj->term_id)) {
        //term模式
        if (in_array($obj->taxonomy, array('forum_topic', 'forum_tag', 'plate_cat'))) {
            global $zib_bbs;
            $term_id = $obj->term_id;

            $name = 'plate_cat' == $obj->taxonomy ? $zib_bbs->plate_name : $zib_bbs->posts_name;

            $views_count = zib_bbs_get_term_views_cut_count($term_id);

            $tags = $name . ': ' . _cut_count($obj->count);
            $tags .= $views_count ? ' · 热度: ' . $views_count : '';
            $data['tags'] = $tags;
            if (!$data['banner']) {
                $data['banner'] = zib_bbs_get_term_default_thumbnail();
            }
        }
    }

    return $data;
}
add_filter('poster_share_data', 'zib_bbs_poster_share_data_filter', 10, 3);

//获取创建限制的选项
function zib_bbs_get_add_limit_options($type = 'plate')
{
    $options = array();
    for ($i = 1; $i <= _pz('bbs_' . $type . '_add_limit_opt_max', 4); $i++) {
        $_id         = 'bbs_' . $type . '_add_limit_' . $i;
        $_name       = _pz('user_cap', array(), $_id);
        $_name       = !empty($_name['name']) ? $_name['name'] : '限制' . $i;
        $options[$i] = $_name;
    }

    if ($options || $type = 'plate') {
        $options = array_merge(array(0 => '系统默认'), $options);
    }
    return $options;
}

//seo
function zib_bbs_seo_head()
{
    global $post, $zib_bbs, $new_title;

    $seo_title = trim(zib_get_post_meta($post->ID, 'title', true));
    if ($seo_title) {
        $new_title = $seo_title;
    } else {
        $title     = trim(wp_title('', false));
        $delimiter = _get_delimiter();
        if ($post->post_type === 'plate') {
            $plate_cat = get_the_terms($post, 'plate_cat');
            $cat_title = '';
            if (isset($plate_cat[0]->term_id)) {
                $cat_title = trim($plate_cat[0]->name) . $delimiter;
            }
            $new_title = $title . $zib_bbs->forum_name . $delimiter . $title . $zib_bbs->plate_name . $delimiter . $cat_title . get_bloginfo('name');
        }
        if ($post->post_type === 'forum_post') {
            $new_title = '';
            $bbs_type  = zib_get_posts_bbs_type($post); //类型判断
            if ($bbs_type) {
                $type_options = zib_bbs_get_posts_type_options();
                if (isset($type_options[$bbs_type])) {
                    $new_title .= '【' . $type_options[$bbs_type] . '】';
                }
            }

            $new_title .= $title;
            $plate_id = zib_bbs_get_plate_id($post->ID);
            if ($plate_id) {
                $plate = get_post($plate_id);
                if (isset($plate->ID)) {
                    $new_title .= $delimiter . trim($plate->post_title) . $zib_bbs->forum_name;
                    $plate_cat = get_the_terms($plate, 'plate_cat');
                    if (isset($plate_cat[0]->term_id)) {
                        $new_title .= $delimiter . trim($plate_cat[0]->name);
                    }
                }
            }
            $new_title .= $delimiter . get_bloginfo('name');
        }
    }
}
if (_pz('post_keywords_description_s')) {
    add_action('bbs_locate_template_plate', 'zib_bbs_seo_head');
    add_action('bbs_locate_template_posts', 'zib_bbs_seo_head');
}
