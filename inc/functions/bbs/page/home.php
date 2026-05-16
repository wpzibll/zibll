<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2025-07-03 22:34:18
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|论坛首页模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_bbs_home_frontend_set_input_array($input_array, $term_id, $type)
{

    $input_array   = [];
    $input_array[] = array(
        'name' => __('标题', 'zibll'),
        'id'   => 'post_title',
        'std'  => get_the_title($term_id),
        'type' => 'text',
    );

    $input_array[] = array(
        'name' => '栏目默认显示',
        'desc' => '默认显示第几个栏目TAB',
        'id'   => 'bbs_home_tab_active_index',
        'type' => 'number',
        'std'  => _pz('bbs_home_tab_active_index', 2),
    );

    return $input_array;
}
add_filter('zib_frontend_set_input_array', 'zib_bbs_home_frontend_set_input_array', 10, 3); //添加前台设置


zib_bbs_page_template('home');
