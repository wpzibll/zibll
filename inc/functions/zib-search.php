<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-17 19:56:54
 * @LastEditTime : 2026-04-28 20:53:10
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|搜索功能相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//搜索页面的主要内容函数
function zib_search_content()
{
    global $s, $type, $cat, $user;

    if (!$s) {
        echo '<div class="zib-widget search-content">' . zib_get_ajax_null('请输入搜索关键词', '60', 'null-search.svg', '', '300') . '</div>';
        return;
    }

    $cat  = $cat ? $cat : false;
    $user = $user ? $user : false;

    if (!isset($_GET['type']) && $type) {
        $_GET['type'] = $type;
    }

    $tabs_args = [
        'post' => [
            'title'         => '文章',
            'content_class' => 'posts-row mtn6',
            'route'         => true,
            'loader'        => zib_get_author_tab_loader('post'),
        ],
        'user' => [
            'title'         => '用户',
            'content_class' => 'zib-widget',
            'route'         => true,
            'loader'        => str_repeat('<div class="author-minicard radius8 flex ac" style="display: inline-flex;"><div class="avatar-img mr10"><div class="avatar placeholder"></div></div><div class="flex1"><div class="placeholder k1 mb6"></div><i><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i></div></div>', 6),
        ],
    ];

    $tabs_args           = apply_filters('search_main_tabs_array', $tabs_args);
    $base_url            = add_query_arg(array('s' => $s, 'user' => $user), home_url());
    $delimiter_blog_name = zib_get_delimiter_blog_name();

    $tab_nav = '';
    foreach ($tabs_args as $k => $tab) {
        if ($type === $k) {
            $tab_nav .= '<li class="active"><a class="focus-color" href="javascript:;">' . $tab['title'] . '</a></li>';
        } else {
            $tab_nav .= '<li><a ajax-replace="true" route="1" route-title="' . esc_attr($s . ' 搜索' . $tab['title'] . $delimiter_blog_name) . '" class="ajax-next" href="' . esc_url(add_query_arg('type', $k, $base_url)) . '">' . $tab['title'] . '</a></li>';
        }
    }
    $tab_nav     = '<div class="list-inline scroll-x mini-scrollbar tab-nav-theme">' . $tab_nav . '</div>';
    $tab_content = apply_filters('main_search_tab_content_' . $type, '');
    $desc_text   = zib_get_search_desc(); //必须放在$tab_content下面，否则用户数量不显示
    $tab_filter  = zib_get_search_tab_nav_filter();

    echo '<div class="ajaxpager search-page-ajaxpager">
    <div class="ajax-item-header type-' . esc_attr($type) . '">
        <div class="search-desc-text mb10 mt10 muted-2-color">' . $desc_text . '</div>
        <div class="search-tab-header zib-widget relative padding-h10"><div>' . $tab_nav . '</div><div class="search-tab-filter">' . $tab_filter . '</div></div>
    </div>
    ' . $tab_content . '
    <div class="post_ajax_loader search-loader" style="display: none;"><div class="mb20"><div class="text-center muted-2-color mt20"><i class="loading mr10"></i>加载中...</div></div></div>
    </div>';
    return;
}

function zib_get_search_main_tab_nav($tabs_args = [], $id_prefix = 'search', $tab_swiper = false, $active_key_str = 'type', $tab_route = true, $base_url = '')
{
    $tab_nav = zib_get_main_tab_nav('nav', $tabs_args, $id_prefix, $tab_swiper, $active_key_str, $tab_route, $base_url);

    //移出全部的data-ajax，data-toggle="tab"，data-target="#search-tab-post"
    $tab_nav = preg_replace('/data-ajax/', '', $tab_nav);
    $tab_nav = preg_replace('/data-toggle="tab"/', '', $tab_nav);
    return $tab_nav;
}

//获取搜索标签的过滤器
function zib_get_search_tab_nav_filter()
{
    global $type;

    $keys = [
        'post'    => [
            'category' => '分类',
            'topics'   => '专题',
            'post_tag' => '标签',
        ],
        'forum'   => [
            'plate_id'    => '版块',
            'forum_topic' => '专题',
            'forum_tag'   => '标签',
        ],
        'product' => [
            'shop_cat'      => '分类',
            'shop_tag'      => '标签',
            'shop_discount' => '优惠',
        ],
        'plate'   => [
            'plate_cat' => '分类',
        ],
    ];

    $current_url = zib_url_del_paged(zib_get_current_url());
    $opts        = zib_get_search_facets_datas();

    $t_keys       = $keys[$type] ?? [];
    $in_term      = $_REQUEST['trem'] ?? '';
    $filter_lists = '';
    if (!empty($opts) && !empty($t_keys)) {
        foreach ($t_keys as $k => $name) {
            if (!empty($opts[$k])) {
                $_f_lists = '';
                if ($k === 'plate_id') {
                    $name = $GLOBALS['zib_bbs']->plate_name ?? '版块';
                    foreach ($opts[$k] as $item) {
                        if ($in_term == 'plate_' . $item['id']) {
                            $_f_lists .= '<span class="focus-color">' . $item['name'] . '</span>';
                        } else {
                            $_f_lists .= '<a rel="nofollow" ajax-replace="true" route="true" class="muted-color ajax-next" href="' . (add_query_arg(['trem' => 'plate_' . $item['id']], $current_url)) . '">' . $item['name'] . '</a>';
                        }
                    }
                } else {
                    $name = zib_get_taxonomy_name($k) ?? $name;
                    foreach ($opts[$k] as $item) {
                        if ($in_term == $item['id']) {
                            $_f_lists .= '<span class="focus-color">' . $item['name'] . '</span>';
                        } else {
                            $_f_lists .= '<a rel="nofollow" ajax-replace="true" route="true" class="muted-color ajax-next" href="' . (add_query_arg(['trem' => $item['id']], $current_url)) . '">' . $item['name'] . '</a>';
                        }
                    }
                }
                $filter_lists .= $_f_lists ? '<div class="flex ac mini-scrollbar scroll-x search-filter-item search-filter-terms"><span class="opacity5">' . $name . '</span>' . $_f_lists . '</div>' : '';
            }
        }
    }

    $filter_lists .= zib_get_search_orderby_lists();
    return $filter_lists;
}

function zib_get_search_facets_datas()
{
    //从缓存获取
    $cache = wp_cache_get('search_facets_datas', 'zib_cache_group');
    if ($cache !== false) {
        $_data = $cache;
    } else {
        $_opts = _pz('search_facets');
        $_data = [];
        if ($_opts && is_array($_opts)) {
            foreach ($_opts as $k => $ids) {
                if (empty($ids)) {
                    continue;
                }

                if ($k === 'plate_id') {
                    $get_posts = get_posts(array(
                        'post_type' => 'plate',
                        'include'   => $ids,
                        'orderby'   => 'include',
                    ));
                    foreach ($get_posts as $post) {
                        $plate_name  = zib_str_cut($post->post_title, 0, 15, '...');
                        $_data[$k][] = ['id' => $post->ID, 'name' => $plate_name];
                    }
                } else {
                    $get_terms = get_terms(array(
                        'include' => $ids,
                        'orderby' => 'include',
                    ));
                    foreach ($get_terms as $term) {
                        $term_name   = zib_str_cut($term->name, 0, 15, '...');
                        $_data[$k][] = ['id' => $term->term_id, 'name' => $term_name, 'taxonomy' => $term->taxonomy];
                    }
                }
            }
        }

        wp_cache_set('search_facets_datas', $_data, 'zib_cache_group');
    }

    return apply_filters('search_facets_datas', $_data);
}

function zib_search_facets_datas_cache_delete()
{
    wp_cache_delete('search_facets_datas', 'zib_cache_group');
}
add_action('csf_zibll_options_saved', 'zib_search_facets_datas_cache_delete');

//排序
function zib_get_search_orderby_lists()
{
    global $type;
    $current_url = zib_url_del_paged(zib_get_current_url());

    //排序
    $search_orderby = [
        'post'    => [
            'date'          => '最新',
            'views'         => '热门',
            'like'          => '点赞',
            'comment_count' => '评论',
            'favorite'      => '收藏',
            'sales_volume'  => '销量',
        ],
        'forum'   => [
            'date'           => '最新',
            'views'          => '热门',
            'comment_count'  => '评论',
            'favorite_count' => '收藏',
            'score'          => '评分',
            'sales_volume'   => '销量',
        ],
        'product' => [
            'date'           => '最新',
            'views'          => '热门',
            'sales_volume'   => '销量',
            'score'          => '评分',
            'favorite_count' => '收藏',
        ],
        'plate'   => [
            'date'         => '最新',
            'views'        => '热门',
            'follow_count' => '关注',
            'posts_count'  => '帖子数',
            'last_reply'   => '最新回帖',
        ],
        'user'    => [
            'name'       => '昵称',
            'registered' => '注册时间',
        ],
    ];

    $search_orderby = apply_filters('search_orderby_array', $search_orderby);
    $orderby_lists  = '';
    $in_orderby     = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';

    if (!isset($search_orderby[$type])) {
        return '';
    }

    foreach ($search_orderby[$type] as $key => $value) {
        if ($in_orderby == $key) {
            $orderby_lists .= '<span class="focus-color">' . $value . '</span>';
        } else {
            $orderby_lists .= '<a rel="nofollow" ajax-replace="true" class="muted-color ajax-next" href="' . (add_query_arg(['orderby' => $key], $current_url)) . '">' . $value . '</a>';
        }
    }

    $filter_lists = '';
    $filter_lists .= $orderby_lists ? '<div class="flex ac mini-scrollbar scroll-x search-filter-item search-filter-orderby"><span class="opacity5">排序</span>' . $orderby_lists . '</div>' : '';

    return $filter_lists;
}

//搜索内容的文字提示
function zib_get_search_desc()
{
    global $wp_query, $s, $type, $cat, $user, $search_total;
    if ($search_total === null) {
        $search_total = $wp_query->found_posts;
    }

    $text      = '';
    $user_text = '';
    if ($user) {
        $user = get_userdata($user);
        if (!empty($user->display_name)) {
            $user_text = '用户<a class="focus-color" href="' . zib_get_user_home_url($user->ID) . '"><b>' . esc_attr($user->display_name) . '</b></a>';
            $text .= $user_text;
        }
    }

    if ($cat) {
        $in_cat_name = '';
        $in_cat_type = '';
        $in_cat_link = '';
        if (stristr($cat, 'plate_')) {
            $get_post = get_post(str_replace('plate_', '', $cat));
            if (!empty($get_post->post_title)) {
                $in_cat_name = $get_post->post_title;
                $in_cat_type = $GLOBALS['zib_bbs']->plate_name ?? '版块';
                $in_cat_link = get_permalink($get_post->ID);
            }
        } else {
            $get_term = get_term($cat);
            if (!empty($get_term->name)) {
                $in_cat_name = $get_term->name;
                $in_cat_type = zib_get_taxonomy_name($get_term->taxonomy);
                $in_cat_link = get_term_link($get_term->term_id, $get_term->taxonomy);
            }
        }
        if ($in_cat_name) {
            $text .= $user_text ? '、' : '';
            $text .= $in_cat_type . ' <a class="focus-color" href="' . $in_cat_link . '"><b>' . $in_cat_name . '</b></a> ';
        }
    }
    $text = $text ? '在' . $text . '中' : '';

    $text .= '搜索 <a href="' . home_url('/?s=' . $s . '&type=' . $type) . '"><b class="search-key focus-color">' . $s . '</b></a> ，共找到 <b class="focus-color">' . $search_total . '</b> 个' . zib_get_search_types()[$type];
    return $text;
}

//搜索用户的结果
function zib_get_search_content_user()
{

    global $s, $user, $paged;

    $user_paged  = !empty($_REQUEST['user_paged']) ? (int) $_REQUEST['user_paged'] : 1;
    $ice_perpage = 12;

    $users_args = array(
        'search'         => '*' . $s . '*',
        'search_columns' => array('user_email', 'user_nicename', 'display_name', 'user_login'),
        'count_total'    => true,
        'number'         => $ice_perpage,
        'paged'          => $user_paged,
        'orderby'        => isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'name',
        'order'          => isset($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC',
    );

    $users_args['count_total'] = true;
    $user_search               = new WP_User_Query($users_args);
    $users                     = $user_search->get_results();
    $GLOBALS['search_total']   = $user_search->total_users;

    $lists = '';
    if ($users) {
        foreach ($users as $user) {
            $lists .= zib_author_card($user->ID, 'ajax-item');
        }
    }

    if ($lists) {
        /**有搜索结果再保存搜索关键词 */
        if ($paged < 2) {
            zib_update_search_keywords($s . '&type=user');
        }
        $lists .= zib_get_ajax_next_paginate($user_search->total_users, $user_paged, $ice_perpage, home_url(remove_query_arg(array('trem', 'user'))), 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'user_paged');
    } else {
        if ($paged < 2) {
            $lists = zib_get_ajax_null('未找到相关用户', '75', 'null-user.svg');
        }
    }

    return $lists;
}
add_filter('main_search_tab_content_user', 'zib_get_search_content_user', 10, 4);

//搜索文章的结果
function zib_get_search_content_post()
{

    $args = array(
        'no_margin' => false,
        'is_card'   => false,
    );

    $html = zib_posts_list($args, false, false);
    $html .= zib_paging(false, false);

    return $html;
}
add_filter('main_search_tab_content_post', 'zib_get_search_content_post', 10);

//获取搜索按钮的统一函数
function zib_get_search_link($args)
{
    $defaults = array(
        'class'       => '',
        'con'         => zib_get_svg('search'),
        'trem'        => '',
        'trem_name'   => '',
        'type'        => '',
        'user'        => '',
        'placeholder' => '',
    );
    $args = wp_parse_args($args, $defaults);

    $args['class'] = $args['class'] ? ' ' . $args['class'] : '';

    $attr = '';
    $attr .= ($args['user']) ? ' search-user="' . $args['user'] . '"' : '';
    $attr .= ($args['type']) ? ' search-type="' . $args['type'] . '"' : '';
    $attr .= ($args['trem']) ? ' search-trem="' . $args['trem'] . '"' : '';
    $attr .= ($args['trem_name']) ? ' trem-name="' . $args['trem_name'] . '"' : '';
    $attr .= ($args['placeholder']) ? ' search-placeholder="' . $args['placeholder'] . '"' : '';

    return '<a' . $attr . ' class="main-search-btn' . $args['class'] . '" href="javascript:;">' . $args['con'] . '</a>';
}

//获取文档模式的分类搜索框
function zib_single_cat_search($cat_id)
{
    $cat_obj = get_category($cat_id);
    ?>
    <div class="theme-box zib-widget dosc-search">
        <div class="title-h-left"><b>搜索<?php echo $cat_obj->cat_name; ?></b></div>

        <?php
$more_cats = array();
    $more_cats = get_term_children($cat_id, 'category');
    array_push($more_cats, $cat_id);
    $args = array(
        'class'          => '',
        'show_keywords'  => false,
        'show_input_cat' => true,
        'show_more_cat'  => true,
        'placeholder'    => '搜索' . $cat_obj->cat_name,
        'in_cat'         => $cat_id,
        'more_cats'      => $more_cats,
    );
    zib_get_search_box($args, true);
    ?>
    </div>
<?php
}

function zib_get_search_keywords_text($s)
{
    $s     = str_replace('&amp;type', '&type', $s);
    $index = strpos($s, '&type=');
    return $index !== false ? substr($s, 0, $index) : $s;
}

//保存热门关键词
function zib_update_search_keywords($s)
{
    $s      = strip_tags($s);
    $s_text = zib_get_search_keywords_text($s);

    if (_pz('search_popular_key', true) && zib_new_strlen($s_text) >= 2 && zib_new_strlen($s_text) <= 8) {
        $keywords = zib_get_option('search_keywords');
        if (!is_array($keywords)) {
            $keywords = array();
        }

        $max_num      = (int) _pz('search_popular_key_num', 20) + 10;
        $keywords     = array_slice($keywords, 0, $max_num, true);
        $keywords[$s] = !empty($keywords[$s]) ? (int) $keywords[$s] + 1 : 1;
        arsort($keywords);

        zib_update_option('search_keywords', $keywords);
    }
}

//保存搜索历史
function zib_save_history_search($s)
{
    $s      = strip_tags($s);
    $s_text = zib_get_search_keywords_text($s);

    if (zib_new_strlen($s_text) >= 1 && zib_new_strlen($s_text) < 30) {
        $old_k = !empty($_COOKIE['history_search']) ? json_decode(stripslashes($_COOKIE['history_search'])) : array();
        if (!is_array($old_k)) {
            $old_k = array();
        }

        foreach ($old_k as $k => $v) {
            if (zib_get_search_keywords_text($v) == zib_get_search_keywords_text($s)) {
                unset($old_k[$k]);
            }
        }

        $max_num = 30;
        array_unshift($old_k, $s);
        $keywords = array_slice($old_k, 0, $max_num, true);
        setcookie('history_search', json_encode($keywords), time() + 3600 * 24 * 30, '/', '', false);
    }
}

//获取热门关键词
function zib_get_search_keywords()
{
    //置顶关键词
    $sticky   = _pz('search_popular_sticky');
    $sticky   = preg_split('/,|，/', $sticky);
    $keywords = zib_get_option('search_keywords');
    if (!$keywords || !is_array($keywords)) {
        $keywords = array();
    }

    $sticky_a = array();
    foreach ($sticky as $key) {
        $s_text = zib_get_search_keywords_text($key);
        if (zib_new_strlen($s_text) < 2) {
            continue;
        }

        if (isset($keywords[$key])) {
            unset($keywords[$key]);
        }
        $sticky_a[$key] = 999999;
    }

    $keywords = array_merge($sticky_a, $keywords);
    return $keywords;
}

function zib_search_keywords_edit_modal()
{
    if (!is_super_admin()) {
        zib_ajax_notice_modal('danger', '没有权限进行此操作！');
    }

    $keywords = zib_get_option('search_keywords');
    if (!$keywords || !is_array($keywords)) {
        $keywords = array();
    }

    $lists = '';
    $i     = 0;
    foreach ($keywords as $k => $v) {
        $lists .= '<div class="flex ac mb6 cloneable-item">
            <input type="input" class="form-control mr6" name="keywords[' . $i . '][name]" value="' . $k . '">
            <input type="number" class="form-control mr6" name="keywords[' . $i . '][count]" value="' . $v . '" style=" width: 100px; ">
            <i class="fa fa-trash-o em12 mr6 cloneable-remove" aria-hidden="true"></i>
        </div>';
        $i++;
    }

    if (!$lists) {
        zib_ajax_notice_modal('warning', '<div class="muted-3-color em09 mb20">暂无热门搜索关键词</div><div class="muted-3-color px12">如需修改置顶关键词，请在<a href="' . zib_get_admin_csf_url('全局功能/搜索功能') . '">主题配置/全局功能/搜索功能</a>中修改<br>此弹窗及按钮仅管理员登录时显示</div>');
    } else {
        $lists = '<div class="mb10 flex ac jsa pb10"><div class="w40 text-center">关键词</div><div class="w40 text-center">搜索次数</div></div><div class="cloneable">' . $lists . '</div><botton type="button" class="cloneable-add but block c-blue"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-add"></use></svg>添加关键词</botton>';
    }

    $form = '<form>
    <div class="mb20 mini-scrollbar scroll-y max-vh7">
        <div class="muted-3-color em09 mb20">在此处编辑已保存的搜索关键词，或修改搜索次数以重新排序。<br>使用后缀<badg class="badg badg-sm c-yellow">&type=xxx</badg>可定义搜索类型，可选类型有：post(文章：默认)、user(用户)、plate(板块)、forum(帖子)<br>如需修改置顶关键词，请在<a href="' . zib_get_admin_csf_url('全局功能/搜索功能') . '">主题配置/全局功能/搜索功能</a>中修改<br>备注：此弹窗及按钮仅管理员登录时显示</div>
        ' . $lists . '
    </div>
    <button class="but jb-blue padding-lg btn-block wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认保存</button>
    <input type="hidden" name="action" value="search_keywords_edit">
    ' . zib_nonce_field('search_keywords_edit') . '
    </form>';

    $title = '<div class="border-title touch"><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="flex jc"><b>编辑搜索关键词</b></div></div>';

    echo $title . $form;
    exit;
}
add_action('wp_ajax_search_keywords_edit_modal', 'zib_search_keywords_edit_modal');

function zib_search_keywords_edit()
{
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (!is_super_admin()) {
        zib_send_json_error('没有权限进行此操作！');
    }

    $keywords_input = !empty($_POST['keywords']) && is_array($_POST['keywords']) ? $_POST['keywords'] : array();
    $keywords       = array();
    foreach ($keywords_input as $item) {
        $key           = !empty($item['name']) ? trim(strip_tags($item['name'])) : '';
        $count         = !empty($item['count']) ? (int) $item['count'] : 0;
        $keywords_text = zib_get_search_keywords_text($key);
        if (!$keywords_text || !$count || zib_new_strlen($keywords_text) < 2) {
            continue;
        }
        $keywords[$key] = $count;
    }

    arsort($keywords);

    zib_update_option('search_keywords', $keywords);

    zib_send_json_success(['hide_modal' => true, 'msg' => '保存成功！刷新页面后生效']);
}
add_action('wp_ajax_search_keywords_edit', 'zib_search_keywords_edit');

//搜索关键词管理编辑按钮
function zib_get_search_keywords_edit_btn($class = 'pull-right muted-3-color', $con = '<i data-toggle="tooltip" title="编辑搜索关键词" class="fa em12 fa-edit"></i>')
{
    if (!is_super_admin()) {
        return '';
    }

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'mobile_bottom' => true,
        'height'        => 243,
        'data_class'    => 'modal-mini',
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'search_keywords_edit_modal',
            'type'   => 'term',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取搜索历史关键词
function zib_get_search_history_keywords()
{
    $old_k = !empty($_COOKIE['history_search']) ? json_decode(stripslashes($_COOKIE['history_search'])) : '';
    if (!is_array($old_k)) {
        return false;
    }

    return $old_k;
}

//获取搜索历史关键词
function zib_get_search_keywords_but($keywords = array(), $type = 'popular', $limit = 0)
{
    $k_i          = 1;
    $keyword_link = '';
    //echo var_dump($keywords);
    if (!is_array($keywords)) {
        return;
    }

    $k_text_array = array();
    foreach ($keywords as $key => $keyword) {

        if ($limit && $limit < $k_i) {
            break;
        }

        $key = 'history' === $type ? $keyword : $key;
        if (zib_new_strlen($key) < 2) {
            continue;
        }
        if ('popular' === $type && count($k_text_array) >= (int) _pz('search_popular_key_num', 20)) {
            continue;
        }

        $s_text = zib_get_search_keywords_text($key);
        if (zib_new_strlen($s_text) < 2 || in_array($s_text, $k_text_array)) {
            continue;
        }

        $k_text_array[] = $s_text;
        $keyword_link .= '<a class="search_keywords muted-2-color but em09 mr6 mb6" href="' . esc_url(home_url('/')) . '?s=' . esc_attr($key) . '">' . esc_attr($s_text) . '</a>';

        $k_i++;
    }
    return $keyword_link;
}

function zib_get_search_types()
{
    $types = array(
        'post' => '文章',
        'user' => '用户',
    );
    return apply_filters('search_types', $types);
}

//挂钩显示一个AJAX加载的核心搜索框
function zib_404_page_add_search()
{
    if (is_search() || (is_404() && _pz('404_search_s', true))) {
        return;
    }

    $args = array(
        'type'   => 'load',
        'class'  => '',
        'loader' => '<div class="search-input"><p><i class="placeholder s1 mr6"></i><i class="placeholder s1 mr6"></i><i class="placeholder s1 mr6"></i></p><p class="placeholder k2"></p>
        <p class="placeholder t1"></p><p><i class="placeholder s1 mr6"></i><i class="placeholder s1 mr6"></i><i class="placeholder s1 mr6"></i><i class="placeholder s1 mr6"></i></p><p class="placeholder k1"></p><p class="placeholder t1"></p><p></p>
        <p class="placeholder k1" style="height: 80px;"></p>
        </div>', // 加载动画
        'query'  => array(
            'action' => 'search_box',
        ),
    );

    echo '<div mini-touch="nav_search" touch-direction="top" class="main-search fixed-body main-bg box-body navbar-search nopw-sm">';
    echo '<div class="container">';
    echo '<div class="mb20 search-close-box"><button class="but cir" data-toggle-class data-target=".navbar-search" >' . zib_get_svg('close', null, 'ic-close') . '</button></div>';
    echo zib_get_remote_box($args);
    echo '</div>';
    echo '</div>';
}
add_action('wp_footer', 'zib_404_page_add_search');

function zib_get_main_search($args = array(), $echo = false)
{
    $search_cat  = _pz('search_cat', true);
    $search_type = _pz('search_type', true);

    $defaults = array(
        'class'          => '',
        'show_keywords'  => _pz('search_popular_key', true),
        'show_history'   => _pz('search_history', true),
        'show_posts'     => _pz('search_posts', true),
        'show_input_cat' => $search_cat,
        'more_cats'      => $search_cat ? _pz('search_more_cat_obj') : false,
        'in_cat'         => $search_cat ? _pz('search_cat_in') : false,
        'show_type'      => $search_type,
        'in_type'        => $search_type ? _pz('search_type_in') : '',
        'in_user'        => '',
    );
    $args = wp_parse_args($args, $defaults);

    return zib_get_search_box($args, $echo);
}

/**
 * @description: 获取搜索卡片的主要函数
 * @param {*} $args
 * @return {*}
 */
function zib_get_search_box($args = array(), $echo = false)
{
    $defaults = array(
        's'              => '',
        'class'          => '',
        'show_form'      => true,
        'show_keywords'  => true,
        'popular_limit'  => 0,
        'show_history'   => true,
        'keywords_title' => _pz('search_popular_title', '热门搜索'),
        'placeholder'    => _pz('search_placeholder', '开启精彩搜索'),
        'show_input_cat' => true,
        'show_posts'     => false,
        'show_type'      => false,
        'in_cat'         => '',
        'in_type'        => 'post',
        'in_user'        => '',
        'more_cats'      => array(),
    );
    $args                = wp_parse_args($args, $defaults);
    $args['placeholder'] = esc_attr($args['placeholder']);
    $form_html           = '';
    if ($args['show_form']) {
        //分类搜索
        $cat_html = '';
        if ($args['show_input_cat']) {
            $all_cat     = zib_get_search_cat($args['more_cats'], 'text-ellipsis');
            $input_cat   = '';
            $in_cat_name = '';
            if ($args['in_cat']) {
                if (stristr($args['in_cat'], 'plate_')) {
                    $get_post = get_post(str_replace('plate_', '', $args['in_cat']));
                    if (!empty($get_post->post_title)) {
                        $in_cat_name = $get_post->post_title;
                    }
                } else {
                    $get_term = get_term($args['in_cat']);
                    if (!empty($get_term->name)) {
                        $in_cat_name = $get_term->name;
                    }
                }

                if (!$all_cat) {
                    $all_cat = '<li data-for="trem" data-value="null"><a href="javascript:;" class="">全部</a></li>';
                    $all_cat .= '<li data-for="trem" data-value="' . $args['in_cat'] . '"><a href="javascript:;" class="text-ellipsis">' . zib_str_cut($in_cat_name, 0, 8) . '</a></li>';
                    $all_cat = '<ul class="dropdown-menu cat-drop">' . $all_cat . '</ul>';
                }
            }
            if ($in_cat_name || $all_cat) {
                $input_cat_name = $in_cat_name ? zib_str_cut($in_cat_name, 0, 6) : '请选择';
                $input_cat_name = '<span name="trem">' . $input_cat_name . '</span>';
                $input_cat_name .= $all_cat ? '<i class="fa ml6 fa-sort opacity5" aria-hidden="true"></i>' : '';
                $input_cat .= $all_cat ? '<a href="javascript:;" class="padding-h10" data-toggle="dropdown">' . $input_cat_name . '</a>' : $input_cat_name;
            }
            if ($all_cat) {
                $input_cat .= $all_cat;
                $input_cat = '<div class="dropdown">' . $input_cat . '</div>';
            }
            if ($input_cat) {
                $cat_html = '<div class="option-dropdown splitters-this-r search-drop">';
                $cat_html .= $input_cat;
                $cat_html .= '</div>';
            }
        }

        //类型
        $type_html = '';
        if ($args['show_type']) {
            $type_name_array = zib_get_search_types();
            $args['in_type'] = $args['in_type'] ? $args['in_type'] : 'post';
            $in_type_name    = $type_name_array[$args['in_type']];
            if ($cat_html) {
                $type_items = '';
                foreach ($type_name_array as $k => $v) {
                    $_class = $args['in_type'] == $k ? ' active' : '';
                    $type_items .= '<a class="but' . $_class . '" data-for="type" data-value="' . $k . '" href="javascript:;">' . $v . '</a>';
                    //  $type_items .= '<a href="javascript:;" class="text-ellipsis" data-for="type" data-value="' . $k . '">' . $v . '</a>';
                }

                $type_html = '<div class="flex jc mb10 search-type-but-average">';
                $type_html .= '<div class="but-average radius">';
                $type_html .= $type_items;
                $type_html .= '</div>';
                $type_html .= '</div>';
            } else {
                $type_items = '';
                foreach ($type_name_array as $k => $v) {
                    $type_items .= '<li><a href="javascript:;" class="text-ellipsis" data-for="type" data-value="' . $k . '">' . $v . '</a></li>';
                }
                $type_html = '<div class="option-dropdown splitters-this-r search-drop"><div class="dropdown">';
                $type_html .= '<a href="javascript:;" class="padding-h10" data-toggle="dropdown"><span name="type">' . $in_type_name . '</span><i class="fa ml6 fa-sort opacity5" aria-hidden="true"></i></a>';
                $type_html .= '<ul class="dropdown-menu">' . $type_items . '</ul>';
                $type_html .= '</div></div>';

                /**
                $type_html = '<div class="option-dropdown splitters-this-r search-drop" data-html="1" data-content="' . esc_attr($type_items) . '" data-trigger="click" data-placement="auto bottom" data-container="body" data-toggle="popover"><div class="dropdown">';
                $type_html .= '<a href="javascript:;" class="padding-h10" data-toggle="dropdown"><span name="type">' . $in_type_name . '</span><i class="fa ml6 fa-sort opacity5" aria-hidden="true"></i></a>';
                $type_html .= '</div></div>';
                 */
            }
        }
        $s          = esc_attr(strip_tags($args['s']));
        $input_html = '<div class="search-input-text">
                <input type="text" name="s" class="line-form-input" tabindex="1" value="' . $s . '"><i class="line-form-line"></i>
                <div class="scale-placeholder' . ($s ? ' is-focus' : '') . '" default="' . $args['placeholder'] . '">' . $args['placeholder'] . '</div>
                <div class="abs-right muted-color"><button type="submit" tabindex="2" class="null">' . zib_get_svg('search') . '</button>
                </div>
            </div>';

        if ($args['in_type'] || $type_html) {
            $input_html .= '<input type="hidden" name="type" value="' . $args['in_type'] . '">';
        }

        if ($args['in_cat'] || $cat_html) {
            $input_html .= '<input type="hidden" name="trem" value="' . $args['in_cat'] . '">';
        }

        if ($args['in_user']) {
            $input_html .= '<input type="hidden" name="user" value="' . $args['in_user'] . '">';
        }
        if (!$cat_html) {
            $cat_html  = $type_html;
            $type_html = '';
        }
        $form_html = '<form method="get" class="padding-10 search-form" action="' . esc_url(home_url('/')) . '">' . $type_html . '<div class="line-form">' . $cat_html . $input_html . '</div></form>';
    }

    //关键词
    $keywords_html = '';
    if ($args['show_keywords']) {
        $keywords     = zib_get_search_keywords();
        $keyword_link = zib_get_search_keywords_but($keywords, 'popular', $args['popular_limit']);
        if ($keyword_link) {
            //如果没有关键词，则不显示
            $admin_btn     = zib_get_search_keywords_edit_btn();
            $keywords_html = '<div class="search-keywords">
                                <p class="muted-color"><span>' . $args['keywords_title'] . '</span>' . $admin_btn . '</p>
                                <div>' . $keyword_link . '</div>
                            </div>';
        }
    }

    //历史关键词
    $history_html = '';
    if ($args['show_history']) {
        $keywords     = zib_get_search_history_keywords();
        $keyword_link = zib_get_search_keywords_but($keywords, 'history');
        if ($keyword_link) {
            //如果没有关键词，则不显示
            $history_html = '<div class="search-keywords history-search">
                                <p class="muted-color"><span>历史搜索</span><a class="pull-right trash-history-search muted-3-color" href="javascript:;"><i class="fa fa-trash-o em12" aria-hidden="true"></i></a></p>
                                <div>' . $keyword_link . '</div>
                            </div>';
        }
    }

    //热门文章
    $posts_html = '';
    if ($args['show_posts']) {
        $posts_html = '<div class="padding-10 relates relates-thumb">
        <p class="muted-color">热门文章</p>
        <div class="swiper-container swiper-scroll">
            <div class="swiper-wrapper">
                ' . zib_get_search_posts() . '
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>';
    }
    $class = $args['class'] ? ' ' . $args['class'] : '';
    if ($echo) {
        echo '<div class="search-input' . $class . '">' . $form_html . $keywords_html . $history_html . $posts_html . '</div>';
    } else {
        return '<div class="search-input">' . $form_html . $keywords_html . $history_html . $posts_html . '</div>';
    }
}

//搜索框热门文章
function zib_get_search_posts($count = 6, $orderby = 'views', $show_img = true)
{
    $args = array(
        'showposts'           => $count,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'post_type'           => 'post',
        'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
    );

    if ('views' !== $orderby) {
        $args['orderby'] = $orderby;
    } else {
        $args['orderby']  = 'meta_value_num';
        $args['meta_key'] = 'views';
    }

    $new_query = new WP_Query($args);
    $lists     = '';
    while ($new_query->have_posts()) {
        $new_query->the_post();
        $title = get_the_title() . get_the_subtitle(false);
        if ($show_img) {
            //$author = get_the_author();
            $time_ago = zib_get_time_ago(get_the_time('Y-m-d H:i:s'));
            $info     = '<item>' . $time_ago . '</item><item class="pull-right">' . zib_get_svg('view') . ' ' . get_post_view_count($before = '', $after = '') . '</item>';
            $img      = zib_post_thumbnail('', 'fit-cover', true);
            $img      = $img ? $img : zib_get_spare_thumb();
            $lists .= '<div class="swiper-slide em09 mr10" style="width:160px;">';
            $card = array(
                'type'         => 'style-3',
                'class'        => '',
                'img'          => $img,
                'alt'          => $title,
                'link'         => array(
                    'url'    => get_permalink(),
                    'target' => '',
                ),
                'text1'        => $title,
                'text2'        => zib_str_cut($title, 0, 29),
                'text3'        => $info,
                'lazy'         => true,
                'height_scale' => 70,
            );
            $lists .= zib_graphic_card($card, false);
            $lists .= '</div>';
        } else {
            $lists .= '<li><a class="icon-circle text-ellipsis" href="' . get_permalink() . '">' . get_the_title() . get_the_subtitle() . '</a></li>';
        }
    }
    wp_reset_query();
    return $lists;
}

/**
 * 搜索卡片
 */
function zib_get_search_cat($cat_ids = array(), $link_class = '')
{
    if (!$cat_ids) {
        return false;
    }

    $cats = get_terms(array(
        'include' => $cat_ids,
        'orderby' => 'include',
    ));

    if (empty($cats[0])) {
        return false;
    }

    $links = '';
    $links .= '<li data-for="trem" data-value="null"><a href="javascript:;" class="' . $link_class . '">请选择</a></li>';
    foreach ($cats as $cat) {
        $links .= '<li data-for="trem" data-value="' . $cat->term_id . '"><a href="javascript:;" class="' . $link_class . '">' . zib_str_cut($cat->name, 0, 8) . '</a></li>';
    }
    return $links ? '<ul class="dropdown-menu cat-drop">' . $links . '</ul>' : '';
}

//整理前台搜索的筛选参数
function zib_main_search_query_search_types($wp_query)
{
    if ($wp_query->is_search() && $wp_query->is_main_query() && !$wp_query->is_admin) {
        $type         = !empty($_REQUEST['type']) ? trim(strip_tags($_REQUEST['type'])) : '';
        $search_types = zib_get_search_types();
        if (!$type || !isset($search_types[$type])) {
            $type = _pz('search_type_in');

            if (!isset($search_types[$type])) {
                $type = 'post';
            }
        }
        $GLOBALS['search_type'] = $type; //保存到全局变量
    }
}
add_filter('parse_query', 'zib_main_search_query_search_types');

/**
 * @description: 为搜索增加按分类进行搜索的功能
 * @param {*}
 * @return {*}
 */
function zib_main_search_query($query)
{
    if ($query->is_search() && $query->is_main_query() && !$query->is_admin) {
        global $search_type;

        $cat  = !empty($_REQUEST['trem']) ? (int) $_REQUEST['trem'] : '';
        $user = !empty($_REQUEST['user']) ? trim($_REQUEST['user']) : '';

        if ('post' === $search_type) {
            if (_pz('search_no_page')) {
                $query->set('post_type', 'post');
            }
        }

        $exclude_cats = _pz('search_exclude_cats', array());
        if ($exclude_cats) {
            foreach ($exclude_cats as $h_cat) {
                $children     = get_term_children($h_cat, 'category');
                $exclude_cats = array_merge($exclude_cats, $children);
            }
            $query->set('category__not_in', $exclude_cats);
        }

        if ($cat) {
            $get_term = get_term($cat);
            if (!empty($get_term->name)) {
                $tax_query = array(array(
                    'taxonomy' => $get_term->taxonomy,
                    'field'    => 'id',
                    'terms'    => $get_term->term_id,
                ));
                $query->set('tax_query', $tax_query);
            }
        }
        if ($user) {
            $query->set('author', $user);
        }
    }
}
add_action('pre_get_posts', 'zib_main_search_query');

function zib_posts_clauses_request($clauses, $query)
{
    if ($query->is_search() && $query->is_main_query() && !$query->is_admin) {
        //如果类型为user，则不进行查询
        if (isset($_REQUEST['type']) && $_REQUEST['type'] === 'user') {
            $clauses['where'] = ' AND 1!=1';
        }
    }
    return $clauses;
}
add_filter('posts_clauses_request', 'zib_posts_clauses_request', 999, 2);

/**
 * @description: 挂钩WP_Query搜索仅匹配项目
 * @param {*} $search 搜索的内容
 * @param {*} $wp_query 查询对象
 * @return {*}
 */
function zib_search_custom_key($search, $wp_query)
{
    if (empty($wp_query->is_search) || empty($wp_query->get('s')) || empty($wp_query->query_vars['search_terms'])) {
        return $search;
    }

    //post_type 判断
    $is_on     = false;
    $post_type = (array) $wp_query->get('post_type');

    foreach ($post_type as $p_type) {
        if (!$is_on && in_array($p_type, array('post', 'forum_post', 'shop_product'))) {
            $is_on = true;
        }
    }

    if (!$is_on) {
        return $search;
    }

    global $wpdb;
    $q          = $wp_query->query_vars;
    $n          = !empty($q['exact']) ? '' : '%';
    $search     = array();
    $custom_key = zib_get_search_custom_key();
    foreach ((array) $q['search_terms'] as $term) {
        $s           = $n . $wpdb->esc_like($term) . $n;
        $this_search = array();
        foreach ($custom_key as $k_type) {
            switch ($k_type) {
                case 'title':
                    $this_search[] = $wpdb->prepare("$wpdb->posts.post_title LIKE %s", $s);
                    break;
                case 'content':
                    $this_search[] = $wpdb->prepare("$wpdb->posts.post_content LIKE %s", $s);
                    break;
                case 'excerpt':
                    $this_search[] = $wpdb->prepare("$wpdb->posts.post_excerpt LIKE %s", $s);
                    break;
                case 'post_tag':
                case 'category':
                case 'topics':
                case 'forum_tag':
                case 'forum_topic':
                case 'shop_tag':
                case 'shop_cat':
                case 'shop_discount':
                    $this_search[] = $wpdb->prepare('(zs_taxonomy.taxonomy = %s AND zs_terms.name LIKE %s)', $k_type, $s);
                    break;

                case 'coment':
                    $this_search[] = $wpdb->prepare('zs_coment.comment_content LIKE %s', $s);
                    break;

                case 'user':
                    $this_search[] = $wpdb->prepare('(zs_user.user_nicename LIKE %s OR zs_user.display_name LIKE %s)', $s, $s);
                    break;
            }
        }

        $search[] = '(' . implode(' OR ', $this_search) . ')';
    }

    if (!is_user_logged_in()) {
        $search[] = "($wpdb->posts.post_password = '')";
    }

    $search = ' AND ' . implode(' AND ', $search);

    add_filter('posts_join_request', 'zib_posts_join_request_filter', 10, 2);
    add_filter('posts_distinct_request', 'zib_posts_distinct_request_filter');

    return $search;
}

/**
 * @description: 搜索去重
 * @param {*}
 * @return {*} DISTINCT
 */
function zib_posts_distinct_request_filter($distinct)
{
    return 'DISTINCT';
}

/**
 * @description: 搜索关联表
 * @param {*}
 * @return {*} LEFT JOIN
 */
function zib_posts_join_request_filter($join)
{
    global $wpdb;
    $custom_key = zib_get_search_custom_key();
    if (in_array('coment', $custom_key)) {
        $join .= " LEFT JOIN $wpdb->comments AS zs_coment ON ($wpdb->posts.ID = zs_coment.comment_post_ID)";
    }

    if (in_array('user', $custom_key)) {
        $join .= " LEFT JOIN $wpdb->users AS zs_user ON ($wpdb->posts.post_author = zs_user.ID) ";
    }

    if (array_intersect(array('post_tag', 'category', 'topics', 'forum_tag', 'forum_topic', 'shop_tag', 'shop_cat', 'shop_discount'), $custom_key)) {
        $join .= " LEFT JOIN $wpdb->term_relationships AS zs_tr ON ($wpdb->posts.ID = zs_tr.object_id) ";
        $join .= " LEFT JOIN $wpdb->term_taxonomy AS zs_taxonomy ON (zs_tr.term_taxonomy_id = zs_taxonomy.term_taxonomy_id) ";
        $join .= " LEFT JOIN $wpdb->terms AS zs_terms ON (zs_taxonomy.term_id = zs_terms.term_id) ";
    }

    return $join;
}

/**
 * 获取搜索的自定义字段
 * @param {*}
 * @return array 自定义字段
 */
function zib_get_search_custom_key()
{
    $custom_key = _pz('search_custom_key');
    if (!$custom_key || !is_array($custom_key) || count($custom_key) < 1) {
        $custom_key = array('title', 'content', 'excerpt', 'post_tag', 'coment', 'forum_tag', 'shop_tag', 'shop_cat', 'shop_discount');
    }

    return $custom_key;
}

function zib_get_search_ms_opt()
{
    $search_ms_opt = _pz('search_ms_opt');
    if (!$search_ms_opt || !is_array($search_ms_opt)) {
        return [];
    }

    if (_pz('search_custom_key_s')) {
        $custom_key = _pz('search_custom_key');
        if ($custom_key && is_array($custom_key) && count($custom_key) >= 1) {
            $search_ms_opt['posts_search_tabs'] = $custom_key;
        }
    }

    return $search_ms_opt;
}

//加载搜索功能
function zib_search_init_add_action()
{

    $search_custom_key_s = _pz('search_custom_key_s', true);
    $search_ms_s         = _pz('search_ms_s');
    //启用自定义搜索匹配项目，必须为关闭Meilisearch智能搜索
    if ($search_custom_key_s && !$search_ms_s) {
        add_filter('posts_search', 'zib_search_custom_key', 50, 2);
    }

    //Meilisearch智能搜索
    if ($search_ms_s) {
        $search_ms_opt = zib_get_search_ms_opt();
        if ($search_ms_opt) {
            new zib_ms($search_ms_opt);
        }
    }
}
add_action('init', 'zib_search_init_add_action');