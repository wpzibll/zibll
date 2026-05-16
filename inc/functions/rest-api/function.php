<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2023-11-02 12:45:14
 * @LastEditTime: 2024-12-18 22:22:15
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//为WordPress官方的搜索api添加帖子类型
function zib_rest_post_search_query_args($query_args, $request)
{
    if (_pz('bbs_s', true)) {
        if (!empty($_REQUEST['post_type'])) {
            $query_args['post_type'] = esc_sql($_REQUEST['post_type']);
        } else {
            $query_args['post_type'] = array_merge((array) $query_args['post_type'], array('plate', 'forum_post'));
        }
    }
    return $query_args;
}
add_filter('rest_post_search_query', 'zib_rest_post_search_query_args', 10, 2);
