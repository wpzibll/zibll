<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-04-10 20:18:56
 * @LastEditTime : 2025-11-30 12:04:53
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_get_post_more_dropdown($post, $class = '', $con_class = 'but cir post-drop-meta', $con = '', $direction = 'down')
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (empty($post->ID)) {
        return;
    }

    $con       = $con ? $con : zib_get_svg('menu_2');
    $class     = $class ? ' ' . $class : '';
    $con_class = $con_class ? ' class="' . $con_class . '"' : '';
    $action    = '';

    $user_ban = zib_get_edit_user_ban_link($post->post_author, '', '<i class="fa fa-ban mr6 fa-fw c-red"></i>禁封用户');
    if (!$user_ban) {
        $user_ban = zib_get_report_link($post->post_author, get_permalink($post), '', '<i class="fa fa-exclamation-triangle mr6 fa-fw c-red"></i>举报内容');
    }
    $action .= $user_ban ? '<li>' . $user_ban . '</li>' : '';

    //编辑
    $edit = zib_get_post_edit_link($post, '', '<i class="fa fa-fw fa-edit mr6"></i>编辑文章');
    $action .= $edit ? '<li>' . $edit . '</li>' : '';

    //删除
    $del = zib_bbs_post_delete_link($post, 'c-red', '<i class="fa fa-trash-o mr6 fa-fw"></i>删除文章');
    $action .= $del ? '<li>' . $del . '</li>' : '';

    if (!$action) {
        return;
    }

    $html = '<div class="drop' . $direction . ' more-dropup' . $class . '">';
    $html .= '<a href="javascript:;"' . $con_class . ' data-toggle="dropdown">' . $con . '</a>';
    $html .= '<ul class="dropdown-menu">';
    $html .= $action;
    $html .= '</ul>';
    $html .= '</div>';
    return $html;
}

/**
 * @description: 获取前台编辑文章的按钮
 * @param {*} $post
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_get_post_edit_link($post = null, $class = 'but c-blue', $con = '编辑文章')
{
    if (!_pz('post_article_s', true)) {
        return;
    }

    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (empty($post->ID)) {
        return;
    }

    if (!zib_current_user_can('new_post_edit', $post)) {
        return;
    }

    $url = zib_get_new_post_url($post->ID);

    return '<a rel="nofollow" href="' . $url . '" class="' . $class . '">' . $con . '</a>';
}

/**
 * @description: 获取前台投稿链接
 * @param {*}
 * @return {*}
 */
function zib_get_new_post_url($edit = 0)
{

    if (!_pz('post_article_s', true)) {
        return;
    }

    $url = zib_get_template_page_url('pages/newposts.php');
    if ($edit) {
        $url = add_query_arg('edit', $edit, $url);
    }

    return $url;
}

/**
 * @description: 获取删除帖子的连接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $con
 * @param {*} $tag
 * @return {*} zib_get_refresh_modal_link()
 */
function zib_bbs_post_delete_link($post = null, $class = '', $con = '<i class="fa fa-trash-o fa-fw"></i>删除', $tag = 'a')
{
    if (!_pz('post_article_s', true)) {
        return;
    }

    if (!is_object($post)) {
        $post = get_post($post);
    }

    if (empty($post->ID)) {
        return;
    }

    if (!zib_current_user_can('new_post_delete', $post) || 'trash' === $post->post_status) {
        return;
    }

    $url_var = array(
        'action' => 'post_delete_modal',
        'id'     => $post->ID,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//设置默认主循环排序方式
function exclude_single_posts_orderby($query)
{
    $list_orderby = _pz('list_orderby', 'data');
    if (in_array($list_orderby, array('data', 'modified')) && $query->is_main_query()) {
        $query->set('orderby', $list_orderby);
    }
}
add_action('pre_get_posts', 'exclude_single_posts_orderby');

//首页文章排除
function exclude_single_posts_home($query)
{
    $exclude_cats = array();
    if ($query->is_home() && $query->is_main_query()) {
        $home_exclude_posts = _pz('home_exclude_posts', array()) ?: array();
        if ($home_exclude_posts) {
            $query->set('post__not_in', $home_exclude_posts);
        }

        $home_exclude_cats = _pz('home_exclude_cats', array());
        if ($home_exclude_cats) {
            foreach ($home_exclude_cats as $h_cat) {
                $children          = get_term_children($h_cat, 'category');
                $home_exclude_cats = array_merge($home_exclude_cats, $children);
            }
            $exclude_cats = array_merge($exclude_cats, $home_exclude_cats);
        }

        $d_cats = _pz('docs_mode_cats');
        if (_pz('docs_mode_exclude') && $d_cats) {
            foreach ($d_cats as $d_cat) {
                $children = get_term_children($d_cat, 'category');
                $d_cats   = array_merge($d_cats, $children);
            }
            $exclude_cats = array_merge($exclude_cats, $d_cats);
        }
        $query->set('category__not_in', $exclude_cats);
    }
}
add_action('pre_get_posts', 'exclude_single_posts_home');

//文章多重筛选代码
//通过pre_get_posts钩子筛选
function zib_sift_posts_per_page($query)
{
    //is_category()即为分类页面有效，自行更换。
    //$query->is_main_query()使得仅对默认的页面主查询有效
    //: !is_admin()避免影响后台文章列表

    if ((is_category() || is_tag() || is_home() || is_tax('topics')) && $query->is_main_query() && !is_admin()) {
        // 分类
        if (isset($_GET['cat'])) {
            $cat = $_GET['cat'];
            $query->set('cat', $cat);
        }
        //  标签
        if (isset($_GET['tag'])) {
            $tag = $_GET['tag'];
            $query->set('tag', $tag);
        }
        // 自定义分类法：taxonomy  topics
        if (isset($_GET['topics'])) {
            $array_temp = array(array('taxonomy' => 'topics', 'terms' => preg_split("/,|，|\s|\n/", $_GET['topics'])));
            $query->set('tax_query', $array_temp);
        }

        // 自定义字段：mate type
        if (isset($_GET['type'])) {
            $array_temp = array('key' => 'type', 'value' => $_GET['type'], 'compare' => '=');
        }

        $filter_args = zib_get_custom_filter_args();
        foreach ($filter_args as $filters) {

            if (!empty($_GET[$filters['key']])) {

                $meta_query = $query->get('meta_query');

                $filters_meta_query = array(
                    'key'     => $filters['key'],
                    'value'   => esc_sql($_GET[$filters['key']]),
                    'compare' => 'LIKE',
                );

                $meta_query   = is_array($meta_query) ? $meta_query : array();
                $meta_query[] = $filters_meta_query;

                $query->set('meta_query', $meta_query);
            }
        }
    }
}
add_action('pre_get_posts', 'zib_sift_posts_per_page', 9999);

/**
 * @description: 查询需要显示的置顶文章，可以用作判断函数
 * @param {*} $query
 * @param {*} $exclude_home 排除首页配置
 * @return {*}
 */
function zib_get_sticky_posts($query)
{

    $sticky_s     = (array) _pz('sticky_s', array('home', 'cat'));
    $this_is      = (in_array('home', $sticky_s)) && $query->is_home() || (in_array('cat', $sticky_s) && $query->is_category()) || (in_array('tag', $sticky_s) && $query->is_tag()) || (in_array('topics', $sticky_s) && $query->is_tax('topics'));
    $sticky_posts = ($query->is_main_query() && !is_admin() && $this_is) ? (get_option('sticky_posts') ?: array()): array();

    return $sticky_posts;
}

//设置置顶查询，将置顶文章排除在外
function zib_pre_main_query_sticky($query)
{

    $sticky = zib_get_sticky_posts($query);
    if ($sticky) {
        $not_in = (array) $query->get('post__not_in');
        $not_in = array_merge($sticky, $not_in);
        $query->set('post__not_in', $not_in);
    }

    $query->set('ignore_sticky_posts', true);
}
add_action('pre_get_posts', 'zib_pre_main_query_sticky', 999);

//将置顶文章插入到主循环中
function zib_the_posts_sticky($posts, $query)
{

    $sticky = zib_get_sticky_posts($query);

    if (!empty($sticky) && $query->get('paged') <= 1) {
        $get_posts_args = array(
            'post__in'       => $sticky,
            'post_type'      => $query->get('post_type'),
            'post_status'    => 'publish',
            'posts_per_page' => count($sticky),
            'tax_query'      => $query->tax_query,
            'no_found_rows'  => true,
        );

        if ($query->is_category()) {
            $get_posts_args['cat'] = $query->get('cat');
        } elseif ($query->is_tag()) {
            $get_posts_args['tag_id'] = $query->get('tag_id');
        } elseif ($query->is_tax('topics')) {
            $get_posts_args['tax_query'] = array(
                array(
                    'taxonomy' => 'topics',
                    'terms'    => $query->queried_object_id,
                ),
            );
        }

        $new_query = new WP_Query($get_posts_args);
        $stickies  = $new_query->get_posts();

        $sticky_offset = 0;
        foreach ($stickies as $sticky_post) {
            array_splice($posts, $sticky_offset, 0, array($sticky_post));
            $sticky_offset++;
        }

        //添加全局变量，用于列表的置顶便签判断
        $query->found_posts += ($sticky_offset);
        $GLOBALS['showed_sticky_posts'] = $sticky;
    }

    return $posts;
}
add_action('the_posts', 'zib_the_posts_sticky', 999, 2);

//文章排序
//通过pre_get_posts钩子筛选
function zib_sift_posts_per_orde($query)
{
    //正反顺序
    if (isset($_GET['order']) && $query->is_main_query() && !is_admin()) {
        $order = 'DESC' == $_GET['order'] ? 'DESC' : 'ASC';
        $query->set('order', $order);
    }
    //按照什么排序
    if (isset($_GET['orderby']) && $query->is_main_query() && !is_admin()) {
        $orderby           = $_GET['orderby'];
        $orderby_keys      = zib_get_query_mate_orderby_keys();
        $mate_orderbys     = $orderby_keys['value'];
        $mate_orderbys_num = $orderby_keys['value_num'];

        if (in_array($orderby, $mate_orderbys_num)) {
            $query->set('orderby', 'meta_value_num');
            $query->set('meta_key', $orderby);
        } elseif (in_array($orderby, $mate_orderbys)) {
            $query->set('orderby', 'meta_value');
            $query->set('meta_key', $orderby);
        } else {
            $query->set('orderby', $orderby);
        }
    }

    //帖子状态
    global $wp_query;
    $curauth = $wp_query->get_queried_object();

    if (isset($_GET['post_status']) && $query->is_main_query() && (is_super_admin() || (!empty($curauth->ID) && get_current_user_id() === $curauth->ID))) {
        $query->set('post_status', $_GET['post_status']);
    }
}
add_action('pre_get_posts', 'zib_sift_posts_per_orde', 9999);

function zib_is_sticky($post_id = 0)
{
    global $showed_sticky_posts;
    if (!$showed_sticky_posts) {
        return false;
    }

    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (is_array($showed_sticky_posts)) {
        $stickies = array_map('intval', $showed_sticky_posts);
        return in_array($post_id, $stickies, true);
    }

    return false;
}

/**
 * @description: 过滤new WP_Query orderby的args
 * @param {*} $orderby
 * @param {*} $args
 * @return {*}
 */
function zib_query_orderby_filter($orderby, $args = array())
{

    $orderby_keys      = zib_get_query_mate_orderby_keys();
    $mate_orderbys     = $orderby_keys['value'];
    $mate_orderbys_num = $orderby_keys['value_num'];

    if (in_array($orderby, $mate_orderbys_num)) {
        $args['orderby']  = 'meta_value_num';
        $args['meta_key'] = $orderby;
    } elseif (in_array($orderby, $mate_orderbys)) {
        $args['orderby']  = 'meta_value';
        $args['meta_key'] = $orderby;
    } else {
        $args['orderby'] = $orderby;
    }

    if (!isset($args['order'])) {
        $args['order'] = 'DESC';
    }

    return $args;
}

function zib_get_query_mate_orderby_keys()
{
    return apply_filters('query_mate_orderby_keys', array(
        'value_num' => array('sales_count', 'score', 'plate_id', 'posts_count', 'reply_count', 'today_reply_count', 'follow_count', 'follow', 'views', 'like', 'favorite', 'favorite_count', 'zibpay_price', 'price', 'zibpay_points_price', 'sales_volume', 'balance', 'points', 'phone_number', 'vip_level', 'level', 'referrer_id'),
        'value'     => array('last_reply', 'last_post', 'last_login'),
    ));
}

/**
 * @description: 获取文章查询
 * @param {*} $args 查询参数
 * @return {*}
 */
function zib_get_posts_query($args = array())
{

    if (!isset($args['paged'])) {
        $args['paged'] = zib_get_the_paged();
    }
    if (!isset($args['count'])) {
        $args['count'] = isset($_REQUEST['count']) ? (int) $_REQUEST['count'] : 12;
    }

    $args_key = array('cat', 'topics', 'orderby');
    foreach ($args_key as $key) {
        if (!isset($args[$key])) {
            $args[$key] = isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
        }
    }

    $query_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'order'               => isset($args['order']) && in_array($args['order'], ['asc', 'ASC']) ? 'ASC' : 'DESC',
        'posts_per_page'      => $args['count'],
        'paged'               => $args['paged'],
    );

    if (!empty($args['cat'])) {
        $query_args['cat'] = str_replace('，', ',', $args['cat']);
    }

    if (!empty($args['topics'])) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy'         => 'topics',
                'terms'            => preg_split("/,|，|\s|\n/", $args['topics']),
                'include_children' => true,
            ),
        );
    }

    if (!empty($args['zibpay_type'][0])) {
        $query_args['meta_query'] = array(
            array(
                'key'     => 'zibpay_type',
                'value'   => $args['zibpay_type'],
                'compare' => 'IN',
            ));
    }

    if (!empty($args['limit_day'])) {
        $current_time             = current_time('Y-m-d H:i:s');
        $query_args['date_query'] = array(
            array(
                'after'     => date('Y-m-d H:i:s', strtotime('-' . $args['limit_day'] . ' day', strtotime($current_time))),
                'before'    => $current_time,
                'inclusive' => true,
            ),
        );
    }

    //不需要分页
    if (!empty($args['no_found_rows'])) {
        $query_args['no_found_rows'] = true; //不统计总数
        $query_args['paged']         = 1;
    }

    $query_args = zib_query_orderby_filter($args['orderby'], $query_args);

    return new WP_Query($query_args);
}
