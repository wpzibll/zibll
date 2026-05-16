<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2023-07-20 12:41:34
 * @LastEditTime : 2026-05-05 20:50:03
 * @Email: 770349780@qq.com
 * @Project: Zibll子比主题
 * @Description: 更优雅的Wordpress主题
 * @Read me: 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind: Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 */

//由于添加了置顶文章重构查询，此函数只对第一页准确
function zib_get_the_found_posts()
{
    global $wp_query;
    if (isset($wp_query->found_posts)) {
        return $wp_query->found_posts;
    }
    return 0;
}

//获取分类全部文章的阅读总和
function zib_get_term_posts_meta_sum($term_id, $mata)
{
    $term_obj = get_term($term_id);
    if (!isset($term_obj->term_id)) {
        return 0;
    }

    $term_id = $term_obj->term_id;
    global $wpdb;
    $cache_num = wp_cache_get($term_id, 'term_posts_' . $mata . '_count', true);
    if ($cache_num === false) {
        $term_id_sql = $term_id;

        $children = get_term_children($term_id, $term_obj->taxonomy);
        if ($children) {
            $children[]  = $term_id;
            $term_id_sql = $children;
        }

        $db = zibDB::table($wpdb->posts)->alias('_p');
        $db->field('sum(meta_value)');
        $db->join([$wpdb->term_relationships, '_t'], '_p.ID = _t.object_id', 'LEFT');
        $db->join([$wpdb->postmeta, '_m'], '_p.ID = _m.post_id');
        $db->join([$wpdb->term_taxonomy, '_tt'], '_t.term_taxonomy_id = _tt.term_taxonomy_id');
        $db->where('_m.meta_key', $mata);
        $db->where('_p.post_type', 'post');
        $db->where('_p.post_status', 'publish');
        $db->where('_tt.term_id', $term_id_sql);
        $num = $db->sum('_m.meta_value');

        //添加缓存，12小时有效
        wp_cache_set($term_id, $num, 'term_posts_' . $mata . '_count', 43200);
    } else {
        $num = $cache_num;
    }

    return $num;
}

/**
 * 获取分类和专题的搜索按钮
 * @param int $term_id 分类或专题的ID
 * @param string $class 按钮的类名
 * @return string 搜索按钮的HTML
 */

function zib_get_term_search_btn($term_id, $class = '', $text = '')
{
    $term = get_term($term_id);
    if (is_wp_error($term) || !isset($term->name)) {
        return;
    }

    $args = array(
        'class'       => $class,
        'trem'        => $term->term_id,
        'trem_name'   => zib_str_cut($term->name, 0, 8),
        'type'        => 'post',
        'placeholder' => '在' . zib_get_taxonomy_name($term->taxonomy) . '[' . $term->name . ']中搜索',
    );

    if ($text) {
        $args['con'] = $text;
    }
    return zib_get_search_link($args);
}

/**
 * 获取分类封面的更多按钮
 * @param int $term_id 分类的ID
 * @param string $class 按钮的类名
 * @return string 更多按钮的HTML
 */
function zib_get_term_cover_more_btn($term_id, $class = '')
{
    $term = get_term($term_id);
    if (is_wp_error($term) || !isset($term->name)) {
        return;
    }

    $search = zib_get_term_search_btn($term_id, $class);

    $allow_view = '';
    if ($term->taxonomy == 'category') {
        $allow_view = zib_get_cat_allow_view_btn($term_id, '', '<i class="fa fa-unlock-alt"></i>');
    }

    return '<div class="page-cover-more-btns flex ac ' . $class . '">' . $allow_view . $search . '</div>';
}

/**
 * @description: 获取term的总查看量
 * @param {*} $term_id
 * @return {*}
 */
function zib_get_term_post_views_sum($term_id, $is_cut = false)
{
    if (!$term_id) {
        $term_id = get_queried_object_id();
    }

    //第一步通过缓存获取
    $cache_num = wp_cache_get($term_id, 'term_views_count', true);
    if (false !== $cache_num) {
        return _cut_count($cache_num);
    }

    $count = get_term_meta($term_id, 'views', true);

    return $is_cut ? _cut_count($count) : (int) $count;
}

function zib_topics_cover($cat_id = '')
{
    $desc = trim(strip_tags(category_description()));

    global $wp_query;
    if (!$cat_id) {
        $cat_id = get_queried_object_id();
    }
    $cat = get_term($cat_id, 'topics');
    if (is_wp_error($cat) || !isset($cat->name)) {
        return;
    }

    $count = $cat->count;
    $title = '<b class="em12"><i class="fa fa-cube mr6" aria-hidden="true"></i>' . $cat->name . '</b>';

    if (_pz('topics_post_count_s', false) && $wp_query->get('paged') <= 1) {
        $count = zib_get_the_found_posts();
        $title .= '<span class="icon-spot">共' . $count . '篇</span>';
    }

    $more_btn = zib_get_term_cover_more_btn($cat_id);
    $img      = zib_get_taxonomy_img_url(null, null, _pz('topics_default_cover'));
    zib_page_cover($title, $img, $desc, $more_btn, true);
}

function zib_cat_cover($cat_id = '')
{
    if (!$cat_id) {
        $cat_id = get_queried_object_id();
    }
    $desc = trim(strip_tags(category_description()));

    global $wp_query;

    $cat = get_category($cat_id);
    if (is_wp_error($cat) || !isset($cat->name)) {
        return;
    }

    $title    = '<i class="fa fa-folder-open em12 mr10 ml6" aria-hidden="true"></i>' . $cat->cat_name;
    $more_btn = zib_get_term_cover_more_btn($cat_id);

    if (_pz('cat_post_count_s', true) && $wp_query->get('paged') <= 1) {
        $count = zib_get_the_found_posts();
        $title .= '<span class="icon-spot">共' . $count . '篇</span>';
    }
    if (_pz('page_cover_cat_s', true)) {
        $img = zib_get_taxonomy_img_url(null, null, _pz('cat_default_cover'));
        zib_page_cover($title, $img, $desc, $more_btn);
    } else {
        echo '<div class="zib-widget relative">';
        echo '<h4 class="title-h-left">' . $title . '</h4>';
        echo '<div class="muted-2-color">' . $desc . '</div>';
        echo $more_btn;
        echo '</div>';
    }
}

function zib_get_cat_allow_view_btn($cat_id, $class = '', $text = '')
{
    $allow_view = zib_get_post_single_cat_allow_view($cat_id);
    if (!$allow_view['html']) {
        return;
    }

    $text  = $text ? $text : '<span class="badg badg-sm c-yellow"><i class="fa fa-unlock-alt"></i></span>';
    $con   = zib_str_remove_lazy($allow_view['html']);
    $title = '<i class="fa fa-unlock-alt mr6"></i>文章阅读限制';

    return '<a class="' . $class . '" href="javascript:;"  data-html="1" title="' . esc_attr($title) . '" data-content="' . esc_attr($con) . '" data-trigger="hover" data-placement="auto bottom" data-container="body" data-toggle="popover">' . $text . '</a>';

}

function zib_tag_cover()
{
    $desc = trim(strip_tags(tag_description()));

    global $wp_query;
    $tag_id = get_queried_object_id();
    $tag    = get_tag($tag_id);
    if (is_wp_error($tag) || !isset($tag->name)) {
        return;
    }

    $title = '<i class="fa fa-tags em12 mr10 ml6" aria-hidden="true"></i>' . $tag->name;

    if (_pz('tag_post_count_s', true) && $wp_query->get('paged') <= 1) {
        $count = zib_get_the_found_posts();
        $title .= '<span class="icon-spot">共' . $count . '篇</span>';
    }

    $more_btn = zib_get_term_cover_more_btn($tag_id);
    if (_pz('page_cover_tag_s', true)) {
        $img = zib_get_taxonomy_img_url(null, null, _pz('tag_default_cover'));
        zib_page_cover($title, $img, $desc, $more_btn);
    } else {
        echo '<div class="zib-widget relative">';
        echo '<h4 class="title-h-left">' . $title . '</h4>';
        echo '<div class="muted-2-color">' . $desc . '</div>';
        echo $more_btn;
        echo '</div>';
    }
}

function zib_page_cover($title, $img, $desc, $more = '', $center = false)
{
    $paged = (get_query_var('paged', 1));
    $attr  = '';
    if ($paged && $paged > 1) {
        $title .= ' <small class="icon-spot">第' . $paged . '页</small>';
    } else {
        $attr = ' win-ajax-replace="page-cover"';
    }
    $src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
    $img = $img ? $img : _pz('page_cover_img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg');

    $lazy_attr = zib_get_lazy_attr('lazy_cover', $img, 'fit-cover', $src);

    $html = '<div' . $attr . ' class="page-cover zib-widget">';
    $html .= '<img ' . $lazy_attr . '>';
    $html .= '<div class="absolute ' . ($center ? 'page-mask' : 'linear-mask') . '"></div>';
    $html .= '<div class="list-inline box-body ' . ($center ? 'abs-center text-center' : 'page-cover-con') . '">';
    $html .= '<div class="' . ($center ? 'title-h-center' : 'title-h-left') . '">';
    $html .= '<b>' . $title . '</b>';
    $html .= '</div>';
    $html .= '<div class="em09 page-desc">' . $desc . '</div>';
    $html .= '</div>';
    $html .= $more;
    $html .= '</div>';

    echo $html;
}

/**
 * @description: 页面AJAX菜单
 * @param {*}
 * @return {*}
 */
function zib_ajax_option_menu($page = 'home', $class = 'ajax-option ajax-replace', $link_class = 'ajax-next', $attr = 'win-ajax-replace="filter"')
{
    if (!empty($_GET['nofilter'])) {
        return;
    }

    $page_args = array();
    if ($page == 'home') {
        $page_args['home'] = array(
            'cat'            => false,
            'cat_option'     => false,
            'topics'         => false,
            'topics_option'  => false,
            'tag'            => false,
            'custom'         => false,
            'tag_option'     => false,
            'orderby'        => _pz('home_list1_orderby_s'),
            'orderby_option' => _pz('home_list1_orderby_option'),
        );
    } else {
        if ($page === 'cat') {
            //分类自定义筛选
            $this_id = get_queried_object_id();
            $opt     = _pz('cat_custom_filter');
            if ($this_id && $opt && is_array($opt)) {
                foreach ($opt as $item) {
                    if (!empty($item['cats']) && is_array($item['cats']) && in_array($this_id, $item['cats'])) {
                        $page_args[$page] = array(
                            'cat'            => $item['cat_s'],
                            'cat_option'     => $item['cat_lists'],
                            'topics'         => $item['topics_s'],
                            'topics_option'  => $item['topics_lists'],
                            'tag'            => $item['tag_s'],
                            'tag_option'     => $item['tag_lists'],
                            'orderby'        => $item['orderby_s'],
                            'orderby_option' => $item['orderby_lists'],
                            'custom'         => !empty($item['custom_filters']),
                            'custom_filters' => !empty($item['custom_filters']) ? $item['custom_filters'] : array(),
                        );
                    }
                }
            }
        }

        if (!$page_args) {
            $page_args[$page] = array(
                'cat'            => _pz('ajax_list_' . $page . '_cat'),
                'cat_option'     => _pz('ajax_list_option_' . $page . '_cat'),
                'topics'         => _pz('ajax_list_' . $page . '_topics'),
                'topics_option'  => _pz('ajax_list_option_' . $page . '_topics'),
                'tag'            => _pz('ajax_list_' . $page . '_tag'),
                'tag_option'     => _pz('ajax_list_option_' . $page . '_tag'),
                'orderby'        => _pz($page . '_orderby_s'),
                'orderby_option' => _pz($page . '_orderby_option'),
                'custom'         => in_array($page, (array) _pz('custom_filter_show')),
                'custom_filters' => 'all',
            );
        }
    }

    $con = '';
    if ($page_args[$page]['cat']) {
        $con .= zib_get_option_terms_but($page_args[$page]['cat_option'], $link_class, '分类');
    }
    if ($page_args[$page]['topics']) {
        $con .= zib_get_option_terms_but($page_args[$page]['topics_option'], $link_class, '专题');
    }
    if ($page_args[$page]['tag']) {
        $con .= zib_get_option_terms_but($page_args[$page]['tag_option'], $link_class, '标签');
    }
    if ($page_args[$page]['custom']) {
        $con .= zib_get_option_custom_filter_but($link_class, $page_args[$page]['custom_filters']);
    }

    if ($page_args[$page]['orderby']) {
        $con .= zib_get_option_orderby_but($page_args[$page]['orderby_option'], $link_class);
    }

    if (!$con) {
        return;
    }

    $html = '<div class="' . $class . '" ' . $attr . '>' . $con . '</div>';
    $html .= '<div></div>'; //空白内容，解决css奇数偶数行
    echo $html;
}

function zib_get_option_list_orderby()
{
    $args = array(
        'modified'            => '更新',
        'date'                => '发布',
        'views'               => '浏览',
        'like'                => '点赞',
        'comment_count'       => '评论',
        'favorite'            => '收藏',
        'zibpay_price'        => '售价',
        'zibpay_points_price' => '积分',
        'sales_volume'        => '销量',
        'rand'                => '随机',
    );
    return $args;
}

//排序方式
function zib_get_option_orderby_but($option = array(), $link_class = 'ajax-next')
{
    $defaults = array(
        'lists'    => array(),
        'dropdown' => false,
    );

    $option = wp_parse_args((array) $option, $defaults);
    if (!$option['lists'] && !$option['dropdown']) {
        return '';
    }

    $html     = '';
    $all_args = zib_get_option_list_orderby();

    $dropdown_but = '';
    $but          = '';
    $uri          = zib_url_del_paged(zib_get_current_url());

    foreach ($option['lists'] as $key) {
        $_class = $link_class;
        if (isset($_GET['orderby']) && $_GET['orderby'] == $key) {
            $_class = $link_class . ' focus-color';
        }
        $href = add_query_arg(array('orderby' => $key), $uri);
        $but .= '<a rel="nofollow" ajax-replace="true" class="' . $_class . '" href="' . esc_url($href) . '">' . $all_args[$key] . '</a>';
    }
    if ($option['dropdown']) {
        foreach ($all_args as $key => $value) {
            $_class = $link_class;
            if (isset($_GET['orderby']) && $_GET['orderby'] == $key) {
                $_class = $link_class . ' focus-color';
            }
            $href = add_query_arg(array('orderby' => $key), $uri);
            $dropdown_but .= '<li><a rel="nofollow" ajax-replace="true" class="' . $_class . '" href="' . esc_url($href) . '">' . $value . '</a></li>';
        }
    }

    if (!$but && !$dropdown_but) {
        return '';
    }

    $is_dropdown = ($option['dropdown'] && $dropdown_but) ? true : false;
    $d_but       = $is_dropdown ? '<a href="javascript:;" data-toggle="dropdown"><span name="cat">排序</span><i class="fa fa-fw fa-sort opacity5" aria-hidden="true"></i></a>' : '排序';

    $html .= '<div class="flex ac">';
    $html .= '<div class="option-dropdown splitters-this-r dropdown flex0">';
    $html .= $d_but;
    $html .= $is_dropdown ? '<ul class="dropdown-menu">' . $dropdown_but . '</ul>' : '';
    $html .= '</div>';
    $html .= '<ul class="list-inline scroll-x mini-scrollbar option-items">' . $but . '</ul>';
    $html .= '</div>';

    return $html;
}

function zib_get_option_terms_but($option = array(), $link_class = 'ajax-next', $text = '分类')
{
    $defaults = array(
        'lists'          => array(),
        'dropdown'       => false,
        'dropdown_lists' => array(),
    );

    $option = wp_parse_args((array) $option, $defaults);
    if (!$option['lists'] && (!$option['dropdown'] || !$option['dropdown_lists'])) {
        return '';
    }

    $html         = '';
    $dropdown_but = '';
    $but          = '';
    $this_id      = get_queried_object_id();
    $this_id_s[]  = $this_id;

    $_object = get_queried_object();

    if (!empty($_object->parent)) {
        $this_id_s[] = $_object->parent;
        $this_id_s   = array_merge($this_id_s, get_ancestors($_object->parent, $_object->taxonomy, 'taxonomy'));
    }

    $child_cat           = '';
    $child_name          = '';
    $delimiter_blog_name = zib_get_delimiter_blog_name();
    if ($option['lists']) {
        $lists = get_terms(array(
            'include' => $option['lists'],
            'orderby' => 'include',
        ));
        foreach ($lists as $term) {
            $_class = $link_class;
            $name   = zib_str_cut($term->name, 0, 8, '...');
            $href   = get_term_link($term);

            if (in_array($term->term_id, $this_id_s)) {
                $_class       = $link_class . ' focus-color';
                $children_ibj = _get_term_hierarchy($_object->taxonomy);
                if (!empty($children_ibj[$term->term_id])) {
                    $child_cat  = $children_ibj[$term->term_id];
                    $child_name = array(
                        'category' => '子分类',
                        'topics'   => '子专题',
                        'post_tag' => '子标签',
                    )[$_object->taxonomy];
                }
            }
            $but .= '<a ajax-replace="true" route="1" route-title="' . esc_attr($name) . $delimiter_blog_name . '" class="' . $_class . '" href="' . $href . '">' . $name . '</a>';
        }
    }
    if ($option['dropdown'] || $option['dropdown_lists']) {
        $lists = get_terms(array(
            'include' => $option['dropdown_lists'],
            'orderby' => 'include',
        ));
        foreach ($lists as $term) {
            $_class = $link_class;
            if ($this_id == $term->term_id) {
                $_class = $link_class . ' focus-color';
            }
            $name = zib_str_cut($term->name, 0, 8, '...');
            $href = get_term_link($term);
            $dropdown_but .= '<li><a ajax-replace="true" route="1" route-title="' . esc_attr($name) . $delimiter_blog_name . '" class="' . $_class . '" href="' . $href . '">' . $name . '</a></li>';
        }
    }
    if (!$but && !$dropdown_but) {
        return '';
    }

    $is_dropdown = ($option['dropdown'] && $dropdown_but) ? true : false;
    $d_but       = $is_dropdown ? '<a href="javascript:;" data-toggle="dropdown"><span name="cat">' . $text . '</span><i class="fa fa-fw fa-sort opacity5" aria-hidden="true"></i></a>' : $text;

    $html .= '<div class="flex ac">';
    $html .= '<div class="option-dropdown splitters-this-r dropdown flex0">';
    $html .= $d_but;
    $html .= $is_dropdown ? '<ul class="dropdown-menu">' . $dropdown_but . '</ul>' : '';
    $html .= '</div>';
    $html .= '<ul class="list-inline scroll-x mini-scrollbar option-items">' . $but . '</ul>';
    $html .= '</div>';

    if ($child_cat) {
        $html .= zib_get_option_terms_but(array('lists' => $child_cat), $link_class, $child_name);
    }
    return $html;
}

function zib_get_custom_filter_args()
{
    $opts        = _pz('custom_filter');
    $filter_args = array();
    if ($opts && is_array($opts)) {
        foreach ($opts as $opt) {
            if ($opt['key']) {
                $options = array();
                foreach ($opt['vals'] as $val) {
                    if ($val['key']) {
                        $name                 = $val['name'] ?: $val['key'];
                        $options[$val['key']] = $name;
                    }
                }

                if ($options) {
                    $filter_args[] = array(
                        'key'  => $opt['key'],
                        'name' => $opt['name'] ?: $opt['key'],
                        'vals' => $options,
                    );
                }
            }
        }
    }
    return $filter_args;
}

function zib_get_option_custom_filter_but($link_class = 'ajax-next', $filters = 'all')
{
    $filter_args = zib_get_custom_filter_args();
    $uri         = zib_url_del_paged(zib_get_current_url());
    $html        = '';
    foreach ($filter_args as $filter) {
        $vals_but = '';
        $is_focus = false;
        if ($filters === 'all' || (is_array($filters) && in_array($filter['key'], $filters))) {

            foreach ($filter['vals'] as $_key => $_name) {

                $_class = $link_class;
                $href   = add_query_arg($filter['key'], $_key, $uri);

                if (!empty($_GET[$filter['key']]) && $_GET[$filter['key']] == $_key) {
                    $_class .= '  focus-color';
                    $href     = add_query_arg($filter['key'], false, $uri);
                    $is_focus = true;
                }

                $vals_but .= '<a rel="nofollow" ajax-replace="true" class="' . $_class . '" href="' . $href . '">' . $_name . '</a>';
            }

            if ($is_focus) {
                $vals_but = '<a rel="nofollow" ajax-replace="true" class="' . $link_class . '" href="' . add_query_arg($filter['key'], false, $uri) . '">全部</a>' . $vals_but;
            }
        }
        if ($vals_but) {
            $html .= '<div class="flex ac">';
            $html .= '<div class="option-dropdown splitters-this-r dropdown flex0">';
            $html .= $filter['name'];
            $html .= '</div>';
            $html .= '<ul class="list-inline scroll-x mini-scrollbar option-items">' . $vals_but . '</ul>';
            $html .= '</div>';
        }
    }

    return $html;
}

/**
 * @description: 根据分类或专题的内容以及文章的聚合模块
 * @param {*} $args
 * @param {*} $echo
 * @return {*}
 */
function zib_term_aggregation($args = array(), $echo = false)
{
    $defaults = array(
        'term_id'      => '',
        'class'        => '',
        'target_blank' => '',
        'taxonomy'     => '',
        'orderby'      => 'date',
        'order'        => 'DESC',
        'count'        => 6,
    );
    $args = wp_parse_args((array) $args, $defaults);

    if (!$args['term_id']) {
        return;
    }

    $term = get_term($args['term_id'], $args['taxonomy']);
    if (!$term) {
        return '';
    }

    $default_img = '';
    if ($term->taxonomy == 'category') {
        $default_img = _pz('cat_default_cover');
        $icon        = '<i class="fa fa-folder-open-o mr6" aria-hidden="true"></i>';
        $but_name    = '分类';
    } elseif ($term->taxonomy == 'topics') {
        $default_img = _pz('topics_default_cover');
        $icon        = '<i class="fa fa-cube mr6" aria-hidden="true"></i>';
        $but_name    = '专题';
    }
    $img         = zib_get_taxonomy_img_url($term->term_id, null, $default_img);
    $href        = get_term_link($term);
    $views_count = zib_get_term_post_views_sum($term->term_id, true);
    $more        = '<badge class="img-badge px12">' . zib_get_svg('hot') . ' ' . $views_count . '</badge>';

    $img_graphic = array(
        'type'         => '',
        'class'        => '',
        'img'          => $img,
        'alt'          => $but_name . '-' . $term->name,
        'link'         => array(
            'url'    => $href,
            'target' => (!empty($args['target_blank']) ? '_blank' : ''),
        ),
        'lazy'         => zib_is_lazy('lazy_cover'),
        'height_scale' => 0,
        'mask_opacity' => 0,
        'more'         => $more,
    );
    $img_html = zib_graphic_card($img_graphic);

    $img_html = '<div class="term-img flex0 em09-sm">' . $img_html . '</div>';

    $target_blank = !empty($args['target_blank']) ? ' target="_blank"' : '';
    $name         = '<a class="em14 key-color text-ellipsis"' . $target_blank . ' href="' . $href . '">' . $term->name . '</a>';

    $description = $term->description;
    if (!$description && is_super_admin()) {
        $description = '请在Wordress后台-文章-文章' . $but_name . '中添加描述！' . zib_get_term_admin_edit('立即编辑', $term);
    }
    $description = '<div class="text-ellipsis-2 muted-color">' . $description . '</div>';

    //准备文章
    $posts_args = array(
        'showposts'           => $args['count'],
        'ignore_sticky_posts' => 1,
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'order'               => $args['order'],
        'tax_query'           => array(
            array(
                'taxonomy' => $term->taxonomy,
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ),
        ),
    );

    //文章排序
    $orderby = $args['orderby'];
    if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
        $posts_args['orderby'] = $orderby;
    } else {
        $posts_args['orderby']    = 'meta_value_num';
        $posts_args['meta_query'] = array(
            array(
                'key'   => $orderby,
                'order' => $args['order'],
            ),
        );
    }

    $posts_html = '';
    $new_query  = new WP_Query($posts_args);
    $count      = $new_query->found_posts; //总数量
    $meta       = '<sapn class="px12 muted-3-color text-ellipsis"><i class="fa fa-file-text-o fa-fw"></i>' . $count . '篇文章</sapn>';
    $meta .= '<a class="but p2-10 px12 c-blue hide-sm shrink0"' . $target_blank . ' href="' . $href . '"><i class="fa fa-angle-right"></i>更多文章</a>';
    $meta = '<div class="term-meta flex jsb ac">' . $meta . '</div>';

    while ($new_query->have_posts()) {
        $new_query->the_post();
        $title = get_the_title() . get_the_subtitle(false);
        $title = '<div class="text-ellipsis"><a class="icon-circle mln3" ' . $target_blank . ' href="' . get_permalink() . '">' . $title . '</a></div>';
        $_meta = '';
        if ($orderby === 'views') {
            $_meta = get_post_view_count();
        } elseif ($orderby === 'favorite') {
            $_meta = get_post_favorite_count();
        } elseif ($orderby === 'like') {
            $_meta = get_post_like_count();
        } elseif ($orderby === 'comment_count') {
            $_meta = get_post_comment_count();
        } elseif ($orderby === 'date') {
            $_meta = '<i class="fa fa-clock-o mr3" aria-hidden="true"></i>' . zib_get_time_ago(get_the_time('Y-m-d H:i:s'));
        }
        if (!$_meta) {
            $_meta = '<i class="fa fa-clock-o mr3" aria-hidden="true"></i>' . zib_get_time_ago(get_the_modified_time('Y-m-d H:i:s'));
        }

        $posts_meta = '<div class="em09 muted-3-color flex0 ml10">' . $_meta . '</div>';
        $posts_html .= '<div class="mt10 flex jsb ac">' . $title . $posts_meta . '</div>';
    }
    wp_reset_query();

    $term_html = '<div class="zib-widget term-aggregation mb10">';
    $term_html .= '<div class="mb20 hover-zoom-img flex px12-sm px12-m-s">';
    $term_html .= $img_html;
    $term_html .= '<div class="term-title ml10 flex xx flex1 jsb">';
    $term_html .= $name;
    $term_html .= $description;
    $term_html .= $meta;
    $term_html .= '</div>';
    $term_html .= '</div>';
    $term_html .= $posts_html;
    $term_html .= '</div>';

    $html = '';
    $html .= $term_html;
    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

function zib_get_taxonomy_name($taxonomy)
{
    $taxonomy_names = array(
        'category'      => '分类',
        'topics'        => '专题',
        'post_tag'      => '标签',
        'forum_topic'   => '话题',
        'forum_tag'     => '标签',
        'plate_cat'     => '分类',
        'shop_cat'      => '分类',
        'shop_tag'      => '标签',
        'shop_discount' => '优惠',
    );

    $taxonomy_names = apply_filters('taxonomy_names', $taxonomy_names);

    return $taxonomy_names[$taxonomy] ?? '';
}

function zib_post_cat_page_content()
{
    $cat_id = get_queried_object_id();
    $cat    = get_term($cat_id, 'category');
    if (is_wp_error($cat) || !isset($cat->name)) {
        return;
    }

    zib_cat_cover();

    //检查阅读限制
    $allow_view = zib_get_post_cat_allow_view($cat_id);
    if (!$allow_view['allow']) {
        echo '<div class="posts-row ajaxpager">';
        echo '<div class="ajax-item zib-widget mt20">' . zib_get_null('抱歉！您暂无查看此内容的权限', 20, 'null-cap.svg', '') . $allow_view['not_html'] . '</div>';
        echo '</div>';
        return;
    }

    echo '<div class="posts-row ajaxpager">';
    zib_ajax_option_menu('cat');
    zib_posts_list();
    zib_paging();
    echo '</div>';
}

//按分类层级获取阅读限制
function zib_get_post_cat_allow_view($cat_id)
{

    $data = zib_get_post_single_cat_allow_view($cat_id);
    if (!$data['allow']) {
        return $data;
    }

    $parent_id = get_term($cat_id)->parent ?? 0;
    if ($parent_id) {
        $data = zib_get_post_cat_allow_view($parent_id);
        return $data;
    }

    return $data;
}

//获取单个分类的阅读限制
function zib_get_post_single_cat_allow_view($cat_id)
{
    $data = array(
        'allow'        => true,
        'type'         => '',
        'not_html'     => '',
        'allow_reason' => '',
        'html'         => '',
    );

    global $term_allow_view_data;
    if (isset($term_allow_view_data[$cat_id])) {
        return $term_allow_view_data[$cat_id];
    }

    $post_cat_config = get_term_meta($cat_id, 'post_cat_config', true);
    if (empty($post_cat_config['allow_view'])) {
        $term_allow_view_data[$cat_id] = $data;
        return $data;
    }

    if (is_super_admin()) {
        //管理员可查看
        $data['allow_reason'] = '您是尊贵的管理员，可查看所有内容';
        $data['allow']        = true;
    }

    $data['type'] = $post_cat_config['allow_view'];
    $sign_btns    = '<p><a href="javascript:;" class="signin-loader but jb-blue padding-lg"><i class="fa fa-fw fa-sign-in" aria-hidden="true"></i>登录</a>' . (!zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but jb-yellow padding-lg">' . zib_get_svg('signup') . '注册</a>' : '') . '</p>';
    $user_id      = get_current_user_id();
    $title        = '';
    $con          = '';
    switch ($data['type']) {
        case 'signin':
            $data['html'] = '<p class="separator muted-3-color em09">登录后可查看</p>';
            if (!$user_id) {
                $data['allow'] = false;
                $title         = '内容已隐藏，请登录后查看';
                $con           = '<div class="text-center em09 mt20"><p class="separator muted-3-color mb20">登录后继续查看</p>' . $sign_btns . '</div>';
            } elseif (!$data['allow_reason']) {
                $data['allow_reason'] = '您已登录，可查看此内容';
            }
            break;

        case 'follow':
            $data['html'] = '<p class="separator muted-3-color em09">收藏后可查看</p>';
            break;

        case 'pay':
        case 'points':

            break;

        case 'roles':
            $allow_roles = $post_cat_config['allow_view_roles'] ?? [];
            $vip         = '';
            $level       = '';
            $auth        = '';
            if (isset($allow_roles['vip'])) {
                if (1 == $allow_roles['vip']) {
                    $vip = zibpay_get_vip_icon(1, 'mr6 em12') . _pz('pay_user_vip_1_name') . (_pz('pay_user_vip_2_s', true) ? '及以上会员' : '');
                }
                if (2 == $allow_roles['vip']) {
                    $vip = zibpay_get_vip_icon(2, 'mr6 em12') . _pz('pay_user_vip_2_name');
                }
            }

            if (!empty($allow_roles['level'])) {
                $level = zib_get_level_badge($allow_roles['level'], 'mr6 em12') . '及更高等级';
            }
            if (!empty($allow_roles['auth'])) {
                $auth = zib_get_svg('user-auth', null, 'mr6 em12') . '认证用户';
            }

            $data['html'] = '<div class="text-center em09">';
            $data['html'] .= '<p class="separator muted-3-color">以下用户组可查看</p>';
            $data['html'] .= $vip ? '<span class="badg mm3">' . $vip . '</span>' : '';
            $data['html'] .= $level ? '<span class="badg mm3">' . $level . '</span>' : '';
            $data['html'] .= $auth ? '<span class="badg mm3">' . $auth . '</span>' : '';
            $data['html'] .= '</div>';

            if (!$user_id) {
                $data['allow'] = false;
                $title         = '内容已隐藏';

                $roles = '';
                $roles .= $vip ? '<span class="badg mm3">' . $vip . '</span>' : '';
                $roles .= $level ? '<span class="badg mm3">' . $level . '</span>' : '';
                $roles .= $auth ? '<span class="badg mm3">' . $auth . '</span>' : '';

                $con = '<div class="text-center em09 mt20">';
                $con .= '<p class="separator muted-3-color mb20">以下用户组可查看</p>';
                $con .= $roles;
                $con .= '<p class="separator muted-3-color mb20 mt20">登录后查看我的权限</p>' . $sign_btns . '';
                $con .= '</div>';
            } else {
                $data['allow'] = false;
                if (!empty($allow_roles['vip'])) {
                    //会员判断
                    $my_vip = zib_get_user_vip_level($user_id);
                    if ($my_vip && $my_vip >= $allow_roles['vip']) {
                        if (!$data['allow_reason']) {
                            $data['allow_reason'] = '您是尊贵的' . zibpay_get_vip_icon($my_vip, 'em12') . _pz('pay_user_vip_' . $my_vip . '_name') . '，可查看此内容';
                        }
                        $data['allow'] = true;
                    } else {
                        $vip = $vip ? '<a class="but mm3 pay-vip" vip-level="' . $allow_roles['vip'] . '" href="javascript:;">' . $vip . '</a>' : '';
                    }
                }

                if (!empty($allow_roles['level'])) {
                    $my_level = zib_get_user_level($user_id);
                    if ($my_level && $my_level >= $allow_roles['level']) {
                        if (!$data['allow_reason']) {
                            $data['allow_reason'] = '您的等级为' . zib_get_level_badge($my_level, 'mr6 em12') . '，可查看此内容';
                        }
                        $data['allow'] = true;
                    } else {
                        $level = $level ? '<a rel="nofollow" class="but mm3" href="' . zib_get_user_center_url('level') . '">' . $level . '</a>' : '';
                    }
                }
                if (!empty($allow_roles['auth'])) {
                    $my_auth = zib_get_user_auth_badge($user_id);
                    if ($my_auth) {
                        if (!$data['allow_reason']) {
                            $data['allow_reason'] = '您已是' . $my_auth . '认证用户，可查看此内容';
                        }
                        $data['allow'] = true;
                    } else {
                        $auth = $auth ? '<a rel="nofollow" class="but mm3" href="' . zib_get_user_center_url('auth') . '">' . $auth . '</a>' : '';
                    }
                }

                if (!$data['allow']) {
                    $title = '此分类内容已隐藏';
                    $roles = '';
                    $roles .= $vip ? $vip : '';
                    $roles .= $level ? $level : '';
                    $roles .= $auth ? $auth : '';

                    $con = '<div class="text-center em09 mt20">';
                    $con .= '<p class="separator muted-3-color mb20">以下用户组可查看</p>';
                    $con .= '<p>' . $roles . '</p>';
                    $con .= '</div>';
                }
            }

            break;

    }
    if ($con || $title) {
        $data['not_html'] = '<div class="hide-post mt6">';
        $data['not_html'] .= '<div class=""><i class="fa fa-unlock-alt mr6"></i>' . $title . '</div>';
        $data['not_html'] .= $con;
        $data['not_html'] .= '</div>';
    }

    $term_allow_view_data[$cat_id] = $data;
    return $data;
}
