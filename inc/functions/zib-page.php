<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2026-04-25 19:25:22
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */
function zib_get_page_header($post_id = '')
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $header_style = zib_get_page_header_style($post_id);
    if (!$header_style) {
        return;
    }

    $title = get_the_title($post_id);
    $html  = '';
    if ($header_style == 1) {
        $html = '<div class="box-body notop"><h3 class="title-h-center text-center mt10">' . $title . '</h3></div>';
    } elseif ($header_style == 2) {
        $html = '<div class="zib-widget"><div><h3 class="title-h-center text-center">' . $title . '</h3></div></div>';
    } elseif ($header_style == 3) {
        $img               = '';
        $post_thumbnail_id = get_post_thumbnail_id($post_id);
        if ($post_thumbnail_id) {
            $image = wp_get_attachment_image_src($post_thumbnail_id, 'full');
            $img   = !empty($image[0]) ? $image[0] : '';
        }
        if (!$img) {
            $img = zib_get_post_meta($post_id, 'thumbnail_url', true);
        }

        $src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
        $img = $img ? $img : _pz('page_header_cover_img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg');

        $html = '<div class="page-cover theme-box radius8 main-shadow">
        <img ' . (zib_is_lazy('lazy_cover', true) ? 'class="fit-cover no-scale lazyload" src="' . $src . '" data-src="' . $img . '"' : 'class="fit-cover no-scale"  src="' . $img . '"') . '>
        <div class="absolute page-mask"></div>
            <div class="list-inline box-body abs-center text-center">
                <div class="title-h-center">
                    <h3>' . $title . '</h3>
                </div>
            </div>
        </div>';
    }
    return $html;
}

function zib_get_page_header_style($post_id = '')
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $header_style = zib_get_post_meta($post_id, 'page_header_style', true);
    if ($header_style == 'not') {
        return false;
    }
    if (!$header_style) {
        $header_style = _pz('page_header_style');
    }

    return $header_style;
}

function zib_get_page_content_style($post_id = '')
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }

    $style = zib_get_post_meta($post_id, 'page_content_style', true);
    return $style;
}