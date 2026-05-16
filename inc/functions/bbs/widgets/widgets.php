<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-08 13:51:44
 * @LastEditTime : 2025-06-29 21:25:25
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//引入文件
zib_require(array(
    'posts',
    'plate',
    'term',
    'other',
), false, ZIB_BBS_REQUIRE_URI . 'widgets/widgets-');

//注册小工具位置
function zib_bbs_register_sidebar()
{
    global $zib_bbs;

    $pags = array(
        'home'  => '首页',
        'plate' => $zib_bbs->plate_name . '页',
        'posts' => $zib_bbs->posts_name . '页',
    );

    $poss = array(
        'sidebar'        => '侧边栏',
        'top_fluid'      => '顶部全宽度',
        'top_content'    => '主内容上面',
        'bottom_content' => '主内容下面',
        'bottom_fluid'   => '底部全宽度',
    );

    foreach ($pags as $key => $value) {
        foreach ($poss as $poss_key => $poss_value) {
            $sidebars[] = array(
                'name'        => '[' . $zib_bbs->forum_name . ']' . $value . '-' . $poss_value,
                'id'          => 'bbs_' . $key . '_' . $poss_key,
                'description' => '显示在 ' . $zib_bbs->forum_name . $value . ' 的 ' . $poss_value . ' 位置' . ($poss_key === 'sidebar' ? '，由于宽度较小，请勿添加大尺寸模块' : '') . '，由于位置较多，建议使用实时预览管理！',
            );
        }
    }

    $sidebars[] = array(
        'name'        => '[' . $zib_bbs->forum_name . ']' . '发帖页面—侧边栏顶部',
        'id'          => 'bbs_new_posts_sidebar_top',
        'description' => '显示在发帖页面的侧边栏顶部，由于宽度较小，请勿添加大尺寸模块，同时会在移动端显示',
    );
    $sidebars[] = array(
        'name'        => '[' . $zib_bbs->forum_name . ']' . '发帖页面—侧边栏底部',
        'id'          => 'bbs_new_posts_sidebar_bottom',
        'description' => '显示在发帖页面的侧边栏底部，由于宽度较小，请勿添加大尺寸模块，同时会在移动端显示',
    );

    foreach ($sidebars as $value) {
        register_sidebar(array(
            'name'          => $value['name'],
            'id'            => $value['id'],
            'description'   => $value['description'],
            'before_widget' => '<div class="zib-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3>',
            'after_title'   => '</h3>',
        ));
    }

}

add_action('widgets_init', 'zib_bbs_register_sidebar');
//添加容器
add_action('bbs_plate_page_content', function () {
    dynamic_sidebar('bbs_plate_top_content');
}, 1);
add_action('bbs_plate_page_content', function () {
    dynamic_sidebar('bbs_plate_bottom_content');
}, 99);
add_action('bbs_home_tab_content_top', function () {
    dynamic_sidebar('bbs_home_top_content');
});
add_action('bbs_home_tab_content_bottom', function () {
    dynamic_sidebar('bbs_home_bottom_content');
});
add_action('bbs_posts_page_content_top', function () {
    dynamic_sidebar('bbs_posts_top_content');
});
add_action('bbs_posts_page_content_bottom', function () {
    dynamic_sidebar('bbs_posts_bottom_content');
});
