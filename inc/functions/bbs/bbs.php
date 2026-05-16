<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-29 20:31:05
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//定义常量
define('ZIB_BBS_ASSETS_URI', ZIB_TEMPLATE_DIRECTORY_URI . '/inc/functions/bbs/assets'); //本主题
define('ZIB_BBS_REQUIRE_URI', '/inc/functions/bbs/'); //本主题require_once的地址前缀

//引入资源文件
zib_require(array(
    'inc/functions',
    'action/action',
), false, ZIB_BBS_REQUIRE_URI);

if (is_admin()) {
    zib_require(array(
        'option',
    ), false, ZIB_BBS_REQUIRE_URI . 'admin/');
}

//启动论坛系统
$zib_bbs = zib_bbs();
if (!$zib_bbs->s) {
    //关闭论坛功能，则后台不显示论坛的评论
    function zib_bbs_close_comments_list_table_query_args($args)
    {
        // Keep the native WordPress comments moderation list intact.
        return $args;
    }

    add_filter('comments_list_table_query_args', 'zib_bbs_close_comments_list_table_query_args');
} else {
    zib_require(array(
        'widgets/widgets',
        'inc/user-page',
    ), false, ZIB_BBS_REQUIRE_URI);

    if (is_admin()) {
        zib_require(array(
            'meta-option',
        ), false, ZIB_BBS_REQUIRE_URI . 'admin/');
    }

    //为搜索添加新的tpye
    function zib_bbs_search_types_filter($types)
    {
        global $zib_bbs;
        $types['plate'] = $zib_bbs->plate_name;
        $types['forum'] = $zib_bbs->posts_name;
        return $types;
    }
    add_filter('search_types', 'zib_bbs_search_types_filter');

    function zib_bbs_search_main_tabs_array_filter($tabs_args)
    {
        global $zib_bbs;
        $tabs_args['plate'] = [
            'title'         => $zib_bbs->plate_name,
            'content_class' => '',
            'route'         => true,
            'loader'        => '<div class="plate-lists"><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div></div>',
        ];
        $tabs_args['forum'] = [
            'title'         => $zib_bbs->posts_name,
            'content_class' => '',
            'route'         => true,
            'loader'        => zib_bbs_get_placeholder('posts_detail_alone'),
        ];
        return $tabs_args;
    }
    add_filter('search_main_tabs_array', 'zib_bbs_search_main_tabs_array_filter');

    //添加add新建按钮-版块
    function zib_bbs_new_add_btns_filter_bbs_plate()
    {
        global $zib_bbs;
        return zib_bbs_get_plate_add_link(0, 'btn-newadd', '<icon class="jb-yellow">' . zib_get_svg('plate-fill') . '</icon><text>创建' . $zib_bbs->plate_name . '</text>', 'a');
    }

    add_filter('new_add_btns_bbs_plate', 'zib_bbs_new_add_btns_filter_bbs_plate');

    //添加add新建按钮-话题
    function zib_bbs_new_add_btns_filter_bbs_topic()
    {
        global $zib_bbs;
        return zib_bbs_get_topic_edit_link(0, 'btn-newadd', '<icon class="jb-pink">' . zib_get_svg('topic') . '</icon><text>创建' . $zib_bbs->topic_name . '</text>', 'a');
    }

    add_filter('new_add_btns_bbs_topic', 'zib_bbs_new_add_btns_filter_bbs_topic');

    //添加add新建按钮-话题
    function zib_bbs_new_add_btns_filter_bbs_posts()
    {
        global $zib_bbs;
        return zib_bbs_get_posts_add_page_link(0, 'btn-newadd', '<icon class="jb-blue">' . zib_get_svg('posts') . '</icon><text>发布' . $zib_bbs->posts_name . '</text>', 'a');
    }

    add_filter('new_add_btns_bbs_posts', 'zib_bbs_new_add_btns_filter_bbs_posts');

    function zib_bbs_user_count_badges_filter($html, $user_id)
    {
        $post_n = _cut_count(zib_get_user_post_count($user_id, 'publish', 'forum_post'));
        global $zib_bbs;
        $html = '<a class="but c-blue-2 tag-forum-post" data-toggle="tooltip" title="共' . $post_n . '篇' . $zib_bbs->posts_name . '" href="' . zib_get_user_home_url($user_id, array('tab' => 'forum')) . '">' . zib_get_svg('posts') . $post_n . '</a>' . $html;

        return $html;
    }

    add_filter('user_count_badges', 'zib_bbs_user_count_badges_filter', 10, 2);

    function zib_bbs_user_sidebar_statistics_filter($args, $user_id)
    {
        global $zib_bbs;

        $data = array(array(
            'name'  => $zib_bbs->posts_name,
            'count' => zib_get_user_post_count($user_id, 'publish', 'forum_post'),
            'link'  => zib_get_user_home_url($user_id, array('tab' => 'forum')),
        ));
        return array_merge($data, $args);
    }

    add_filter('user_sidebar_statistics_args', 'zib_bbs_user_sidebar_statistics_filter', 10, 2);

    //过滤必要的付费参数
    function zibpay_post_pay_meta_sanitize($meta_value)
    {
        if (!isset($meta_value['pay_type'])) {
            $allow_view = !empty($_POST['forum_allow_view']['allow_view']) ? $_POST['forum_allow_view']['allow_view'] : '';
            if (in_array($allow_view, ['points', 'pay'])) {
                $meta_value['pay_type'] = '1';
                $meta_value['pay_modo'] = $allow_view === 'points' ? 'points' : '0';
            }
        }

        $meta_value = array_merge(
            array(
                'pay_type'            => 'no',
                'pay_limit'           => '0',
                'pay_modo'            => '0',
                'points_price'        => '',
                'vip_1_points'        => '',
                'vip_2_points'        => '',
                'pay_price'           => '',
                'vip_1_price'         => '',
                'vip_2_price'         => '',
                'pay_rebate_discount' => 0,
                'pay_cuont'           => 0,
            ), (array) $meta_value);

        return $meta_value;
    }

    add_filter('sanitize_post_meta_posts_zibpay_for_forum_post', 'zibpay_post_pay_meta_sanitize', 10);

    function zib_bbs_get_excerpt_filter($excerpt, $post)
    {
        if ($post->post_type !== 'forum_post') {
            return $excerpt;
        }

        $allow_view = zib_bbs_get_posts_not_allow_view($post);
        if ($allow_view && !strstr($allow_view, '部分内容已隐藏')) {
            return '';
        }

        return $excerpt;
    }

    add_filter('zib_get_excerpt', 'zib_bbs_get_excerpt_filter', 10, 2);
}

//添加add新建按钮-选项
function zib_bbs_new_add_btns_options($options)
{
    global $zib_bbs;
    $options['bbs_plate'] = '[' . $zib_bbs->forum_name . ']创建' . $zib_bbs->plate_name;
    $options['bbs_topic'] = '[' . $zib_bbs->forum_name . ']创建' . $zib_bbs->topic_name;
    $options['bbs_posts'] = '[' . $zib_bbs->forum_name . ']发布' . $zib_bbs->posts_name;
    return $options;
}
add_filter('new_add_btns_options', 'zib_bbs_new_add_btns_options');

//禁止免密购买
function zib_bbs_tourists_pay_is_allow_filter($is_allow, $post_id)
{
    if ($post_id && get_post_type($post_id) === 'forum_post') {
        return false;
    }
    return $is_allow;
}
add_filter('tourists_pay_is_allow', 'zib_bbs_tourists_pay_is_allow_filter', 10, 2);
