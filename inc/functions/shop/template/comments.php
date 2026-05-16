<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-06-15 16:39:43
 * @LastEditTime : 2026-04-22 21:05:09
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

defined('ABSPATH') or exit('无法直接加载此文件.');

function zib_shop_comment_header()
{

    global $post, $wp_rewrite;
    $product_id = get_queried_object_id();
    $count_t    = zib_shop_get_comment_count($post);
    $user_id    = get_current_user_id();
    $corderby   = !empty($_GET['corderby']) ? $_GET['corderby'] : '';
    $c_type     = !empty($_GET['ctype']) ? $_GET['ctype'] : '';
    $this_url   = add_query_arg('cpage', false, zib_get_current_url()); //筛选 过滤
    $this_url   = preg_replace("/\/$wp_rewrite->comments_pagination_base-([0-9]{1,})/", '', $this_url);

    $order = '<a rel="nofollow" class="but comment-orderby' . (!$corderby || 'comment_date_gmt' == $corderby ? ' b-theme' : '') . '" href="' . esc_url(add_query_arg('corderby', 'comment_date_gmt', $this_url)) . '">最新</a>';
    $order .= '<a rel="nofollow" class="but comment-orderby' . ('comment_like' == $corderby ? ' b-theme' : '') . '" href="' . esc_url(add_query_arg('corderby', 'comment_like', $this_url)) . '">最热</a>';
    $order = '<div class="comment-order-box but-average radius em09 shrink0">' . $order . '</div>';

    $score_data  = zib_get_post_meta($product_id, 'score_data', true);
    $c_has_image = !empty($score_data['counts']['has_image']) ? _cut_count($score_data['counts']['has_image']) : 0;
    $c_good      = !empty($score_data['counts']['good']) ? _cut_count($score_data['counts']['good']) : 0;
    $c_bad       = !empty($score_data['counts']['bad']) ? _cut_count($score_data['counts']['bad']) : 0;

    $type_filter_args = array(
        array(
            'name'  => '全部',
            'type'  => '',
            'count' => $count_t,
        ),
        array(
            'name'  => '有图',
            'type'  => 'image',
            'count' => $c_has_image,
        ),
        array(
            'name'  => '好评',
            'type'  => 'good',
            'count' => $c_good,
        ),
        array(
            'name'  => '中/差评',
            'type'  => 'bad',
            'count' => $c_bad,
        ),
    );

    $type_filter = '';
    foreach ($type_filter_args as $k => $v) {
        $active_class = $v['type'] == $c_type ? ' c-theme badg mr6' : ' comment-orderby but mr6';
        $active_attr  = $v['type'] == $c_type ? ' href="javascript:;"' : ' href="' . esc_url(add_query_arg('ctype', $v['type'], $this_url)) . '"';
        $type_filter .= '<a rel="nofollow"' . $active_attr . ' class="' . $active_class . '">' . $v['name'] . '<count class="ml3 em09">' . $v['count'] . '</count></a>';
    }

    //商品评分
    $score_html = '';
    // 如果有评分数据，则显示详细评分项
    if ($score_data && !empty($score_data['average'])) {
        $score_average           = $score_data['average'];
        $score_average_name_data = zib_shop_get_score_average_name_data($score_average);
        $score_average_name      = $score_average_name_data[0];
        $score_average_color     = $score_average_name_data[1];

        $score_average_html = '';
        if ($score_average) {
            $score_average_html = '<div class="">
                <div class="em09 muted-2-color">综合评分</div>
                <div class="c-red"><b class="em2x mr6">' . $score_average . '</b><span class="">' . $score_average_name . '</span></div>
                ' . zib_shop_get_star_badge($score_average, 'flex ac') . '
            </div>';
        }

        $score_data_html = '<div class="comment-score-detail flex xx ab">';
        // 遍历评分项目
        $item_name_args = [
            'product'  => '商品',
            'service'  => '服务',
            'shipping' => '物流',
        ];
        foreach ($item_name_args as $key => $name) {
            if (isset($score_data[$key])) {
                $item_score     = $score_data[$key];
                $progress_width = $item_score * 20; // 5分制转换为百分比
                $score_data_html .= '<div class="score-item flex ac">';
                $score_data_html .= '<div class="score-name em09 muted-2-color mr10">' . $name . '</div>';
                $score_data_html .= '<div class="vote-item comment-score-progress"><div class="vote-progress" style="width: ' . $progress_width . '%"></div></div>';
                $score_data_html .= '<div class="score-value c-red">' . $item_score . '</div>';
                $score_data_html .= '</div>';
            }
        }

        $score_data_html .= '</div>';
        $score_html = '<div class="comment-score flex ac jsb mb20">' . $score_average_html . $score_data_html . '</div>';
    }

    echo '<div class="comment-header border-bottom" win-ajax-replace="shop-comment-header">';
    echo $score_html;
    echo '<div class="comment-filter flex ac jsb mb20">'; //筛选
    echo '<div class="scroll-x mini-scrollbar mr10">' . $type_filter . '</div>';
    echo $order;
    echo '</div>';
    echo '</div>';
}

function zib_shop_comment_box()
{
    $comment_name = '评价';
    echo '<div id="comments"><div class="comment-box product-article shop-comment-box" id="postcomments"><ol class="commentlist list-unstyled shop-commentlist">';
    zib_shop_comment_header();
    if (have_comments()) {
        wp_list_comments(
            array(
                'type'     => 'comment',
                'callback' => 'zib_shop_comment_list',
            )
        );
        $loader = '<div style="display:none;" class="post_ajax_loader"><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul></div>';
        echo $loader;
        zib_shop_comment_paginate();
    } else {
        echo zib_get_null('暂无' . $comment_name . '内容', 55, 'null.svg', 'comment comment-null');
        echo '<div class="pagenav hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }
    echo '</ol></div></div>';
}

//商家回复
function zib_shop_comment_author_reply_list($comment, $args, $depth)
{
    if (!$comment) {
        return false;
    }

    $comment_id = $comment->comment_ID;
    $con        = zib_comment_filters(get_comment_text($comment));

    if (!$con) {
        return false;
    }

    $html = '<li ' . comment_class('comment-author-reply', $comment, null, false) . ' id="comment-' . $comment_id . '">';
    $html .= '<ul class="list-inline">';
    $html .= '<li class="comt-main" id="div-comment-' . $comment_id . '">';
    $html .= '<div class="comment-footer">';
    $html .= '<div class="mb10 comment-content" id="comment-content-' . $comment_id . '"><b class="">商家回复：</b>' . $con . '</div>';
    $html .= '</div>';
    $html .= '</li>';
    $html .= '</ul>';

    echo $html;
}

function zib_shop_comment_list($comment, $args, $depth)
{

    if (!$comment) {
        return false;
    }

    if ($comment->comment_parent && $comment->user_id) {
        //商家回复
        $post = get_post($comment->comment_post_ID);
        if ($comment->user_id == $post->post_author) {
            zib_shop_comment_author_reply_list($comment, $args, $depth);
            return;
        } elseif ($comment->user_id == $post->comment_parent) {
            //追评

        }
    }

    $comment_id = $comment->comment_ID;
    $con        = zib_comment_filters(get_comment_text($comment));
    $header     = zib_get_comment_header($comment);
    $footer     = zib_get_comment_footer($comment, $depth);
    $score_data = zib_get_comment_meta($comment_id, 'score_data', true);

    if (!$con) {
        $con = '<div class="comment-null muted-2-color">' . (empty($score_data['auto_generated']) ? '用户未填写评价内容' : '系统默认好评') . '</div>';
    }

    $score_average_badge = '';
    if ($score_data && !empty($score_data['average'])) {
        $score_average           = $score_data['average'];
        $score_average_name_data = zib_shop_get_score_average_name_data($score_average);
        $score_average_badge     = '<span class="badg ' . $score_average_name_data[1] . ' badg-sm mr6 shrink0">' . $score_average_name_data[0] . '</span>';

        $score_average_badge .= zib_shop_get_star_badge($score_average, 'flex ac shrink0');
    }

    $order_data          = zib_get_comment_meta($comment_id, 'order_data', true);
    $options_active_name = $order_data['options_active_name'] ?? '';
    $score_average_badge .= $options_active_name ? '<span class="muted-2-color em09 ml10 text-ellipsis">' . $options_active_name . '</span>' : '';
    $score_average_badge = '<div class="comment-score-badge flex ac mb20 mb10-sm">' . $score_average_badge . '</div>';

    //图片
    $score_image      = $score_data['img_ids'] ?? [];
    $score_image_html = '';
    if ($score_image) {
        $imgs_max   = 99;
        $imgs_count = 0;
        foreach ($score_image as $img_id) {
            $img_src = zib_get_attachment_image_src($img_id);
            if (!empty($img_src[0])) {
                $imgs_count++;
                if ($imgs_count > $imgs_max) {
                    continue;
                }
                $lazy_attr = zib_get_lazy_attr('lazy_cover', $img_src[0], 'fit-cover');
                $score_image_html .= '<span><img ' . $lazy_attr . ' alt="评价图片"></span>';
            }
        }
        $score_image_html = '<div class="imgbox-container mb10 comment-score-imgs count-' . ($imgs_count <= $imgs_max ? $imgs_count : $imgs_max) . '">' . $score_image_html . '</div>';
    }

    $html = '<li ' . comment_class('', $comment, null, false) . ' id="comment-' . $comment_id . '">';
    $html .= '<ul class="list-inline">';
    $html .= '<li class="comt-main" id="div-comment-' . $comment_id . '">';
    $html .= $header;
    $html .= '<div class="comment-footer">';
    $html .= $score_average_badge;
    $html .= '<div class="mb10 comment-content" id="comment-content-' . $comment_id . '">' . $con . '</div>';
    $html .= $score_image_html;
    $html .= $footer;
    $html .= '</div>';
    $html .= '</li>';
    $html .= '</ul>';

    echo $html;
}

//移出作者标签
function zib_shop_comment_user_name_badge($name)
{
    //移出 <span class="badg c-green badg-sm flex0 ml3">作者</span>
    $name = preg_replace('/<span[^>]*>作者<\/span>/', '', $name);

    return '<span class="comment-user-name">' . $name . '</span>';
}
add_filter('comments_user_name_badge', 'zib_shop_comment_user_name_badge');

//移出回复按钮
function zib_shop_comment_reply_button($reply_text)
{
    return '';
}
add_filter('comment_reply_link', 'zib_shop_comment_reply_button');

//关闭评论置顶功能
add_filter('comment_topping_enabled', '__return_false');

//关闭评论编辑功能
add_filter('comment_edit_enabled', '__return_false');

zib_shop_comment_box();
