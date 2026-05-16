<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime: 2024-10-11 14:38:28
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_index_tab($nav = 'nav')
{
    $tabs = _pz('home_lists', array());
    if (!$tabs || !is_array($tabs)) {
        return;
    }

    $tabs_ids = array_column($tabs, 'term_id');
    $terms    = array();
    if (!empty($tabs_ids[0])) {
        $terms = get_terms(array(
            'include' => $tabs_ids,
            'orderby' => 'include',
        ));
    }
    $html     = '';
    $i        = 0;
    $is_card  = false;
    $is_mobel = wp_is_mobile();
    if (!$is_card) {
        $list_type = _pz('list_type');
        if ($list_type == 'card') {
            $is_card = true;
        }
    }
    foreach ($terms as $term) {
        $g_c_t     = $term->name;
        $key       = array_search($term->term_id, $tabs_ids);
        $cat_t     = $tabs[$key]['title'] ? $tabs[$key]['title'] : $g_c_t;
        $query_arg = array('nofilter' => 'true');
        //分类法
        $taxonomy = $term->taxonomy;
        if (!empty($tabs[$key]['orderby'])) {
            $query_arg['orderby'] = $tabs[$key]['orderby'];
        }

        $link = esc_url(add_query_arg($query_arg, get_term_link($term)));
        if ('nav' == $nav) {
            $html .= '<li><a data-toggle="tab" data-ajax="' . $link . '" href="#index-tab-' . $i . '">' . $cat_t . '</a></li>';
        } elseif ('content' == $nav) {

            if (!$is_card) {
                $is_card = $taxonomy == 'topics' && _pz('list_card_topics');
            }
            if (!$is_card) {
                $fl_card = (array) _pz('list_card_cat');
                if ($fl_card && $term->term_id && in_array($term->term_id, $fl_card)) {
                    $is_card = true;
                }
            }
            $placeholder_type = $is_card ? 'card' : 'lists';
            $placeholder_num  = $is_card ? ($is_mobel ? 4 : 12) : ($is_mobel ? 4 : 6);
            $placeholder      = zib_get_post_placeholder($placeholder_type, $placeholder_num);
            $html .= '<div class="ajaxpager tab-pane fade" id="index-tab-' . $i . '">';
            $html .= '<span class="post_ajax_trigger"><a class="ajax_load ajax-next ajax-open" href="' . $link . '"></a></span>';
            $html .= '<div class="post_ajax_loader">' . $placeholder . '</div>';
            $html .= '</div>';
        }
        $i++;
    }

    return $html;
}

function zib_index_tab_html($nav = 'nav')
{
    if (!_pz('home_posts_list_s', true)) {
        return false;
    }

    $index_list_title = _pz('index_list_title');
    $paged            = zib_get_the_paged();

    //不是第一页
    if ($paged > 1) {
        return '<div class="box-body notop nobottom"><div class="title-theme">' . ($index_list_title ?: '最新发布') . '<small class="ml10">第' . $paged . '页</small></div></div>';
    }

    $index_tab_nav = zib_index_tab('nav');

    if (!$index_tab_nav) {
        return _pz('index_list_title') ? '<div class="box-body notop nobottom"><div class="title-theme">' . $index_list_title . '</div></div>' : '<div></div>';
    }

    $html = '<li class="active"><a data-toggle="tab" href="#index-tab-main">' . ($index_list_title ?: '最新发布') . '</a></li>' . $index_tab_nav;

    return '<div class="index-tab affix-header-sm relative home-tab-nav-box mb10" offset-top="-8"><ul style="width:100%;" class="scroll-x no-scrollbar">' . $html . '</ul></div>';
}
