<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime: 2025-10-11 19:28:57
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_posts_list($args = array(), $new_query = false, $echo = true)
{

    $defaults = array(
        'type'          => 'auto',
        'no_author'     => false,
        'no_margin'     => false,
        'is_mult_thumb' => false,
        'is_no_thumb'   => false,
        'is_card'       => false,
        'is_category'   => is_category(),
        'is_search'     => is_search(),
        'is_home'       => is_home(),
        'author_archive' => call_user_func('is_' . 'author'),
        'is_tag'        => is_tag(),
        'is_topics'     => is_tax('topics'),
    );
    if (_pz('list_show_type', 'no_margin') == 'no_margin') {
        $defaults['no_margin'] = true;
    }

    $args = wp_parse_args((array) $args, $defaults);
    $legacy_author_key = 'is_' . 'author';
    if (isset($args[$legacy_author_key])) {
        $args['author_archive'] = $args[$legacy_author_key];
        unset($args[$legacy_author_key]);
    }

    $html = '';
    if ($new_query) {
        while ($new_query->have_posts()): $new_query->the_post();
            $html .= zib_mian_posts_while($args, false);
        endwhile;
    } else {
        while (have_posts()): the_post();
            $html .= zib_mian_posts_while($args, false);
        endwhile;
    }

    if (!$html) {
        $html = zib_get_ajax_null('暂无内容', '100', 'null-post.svg');
    }

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }

    wp_reset_query();
}

function zib_mian_posts_while($args = array(), $echo = true)
{
    $defaults = array(
        'type'          => 'auto',
        'no_author'     => false,
        'no_margin'     => false,
        'is_mult_thumb' => false,
        'is_no_thumb'   => false,
        'is_card'       => false,
        'is_category'   => false,
        'is_search'     => false,
        'is_home'       => false,
        'author_archive' => false,
        'is_tag'        => false,
        'is_topics'     => false,
    );
    $args = wp_parse_args((array) $args, $defaults);
    $legacy_author_key = 'is_' . 'author';
    if (isset($args[$legacy_author_key])) {
        $args['author_archive'] = $args[$legacy_author_key];
        unset($args[$legacy_author_key]);
    }
    if ($args['author_archive']) {
        $args['no_author'] = true;
    }

    $is_card = $args['type'] == 'card' || $args['is_card'];

    if (!$is_card) {
        $list_type = _pz('list_type');
        if ($list_type == 'card') {
            $is_card = true;
        }
    }
    if (!$is_card && ($args['is_tag'] && _pz('list_card_tag')) || ($args['is_home'] && _pz('list_card_home')) || ($args['author_archive'] && _pz('list_card_author')) || ($args['is_topics'] && _pz('list_card_topics'))) {
        $is_card = true;
    }
    if (!$is_card) {
        $cat_ID  = get_queried_object_id();
        $fl_card = (array) _pz('list_card_cat');
        if ($fl_card && $cat_ID && in_array($cat_ID, $fl_card)) {
            $is_card = true;
        }
    }

    $html = $is_card ? zib_posts_mian_list_card($args) : zib_posts_mian_list_list($args);

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

//获取列表模式的文章列表
function zib_posts_mian_list_list($args = array())
{
    $defaults = array(
        'type'          => 'auto',
        'no_author'     => false,
        'no_margin'     => false,
        'is_mult_thumb' => false,
        'is_no_thumb'   => false,
        'is_card'       => false,
        'is_category'   => false,
        'is_search'     => false,
        'is_home'       => false,
        'author_archive' => false,
        'is_tag'        => false,
        'is_topics'     => false,
    );

    $args = wp_parse_args((array) $args, $defaults);

    //准备必要参数
    global $post;
    $graphic            = zib_get_posts_thumb_graphic();
    $title              = zib_get_posts_list_title();
    $badge              = zib_get_posts_list_badge($args);
    $meta               = zib_get_posts_list_meta(!$args['no_author'], false);
    $excerpt            = zib_get_excerpt();
    $get_permalink      = get_permalink();
    $_post_target_blank = _post_target_blank();

    $class = 'posts-item list ajax-item';
    $style = _pz('list_list_option', '', 'style');
    $class .= $style && $style != 'null' ? ' ' . $style : '';
    $html = '';

    $is_show_sidebar = zib_is_show_sidebar();
    $is_mult_thumb   = false;
    $is_no_thumb     = false;

    //判断多图模式和无图模式
    //在开启侧边栏的时候或者在移动端则允许此模式
    if (($is_show_sidebar || wp_is_mobile())) {
        $list_type = _pz('list_type');
        if ($args['is_no_thumb'] !== 'disable' && ($list_type == 'text' || ($list_type == 'thumb_if_has' && strstr($graphic, 'data-thumb="default"')))) {
            $is_no_thumb = true;
        } elseif ($args['is_mult_thumb'] !== 'disable') {
            $_thumb_count = zib_get_post_imgs_count($post);
            if ($_thumb_count > 2) {
                if (has_post_format(array('image', 'gallery'))) {
                    $is_mult_thumb = true;
                }
                if (!$is_mult_thumb) {
                    $category       = get_the_category();
                    $mult_thumb_cat = _pz('mult_thumb_cat');
                    if (!empty($category[0]) && $mult_thumb_cat) {
                        foreach ($category as $category1) {
                            if (in_array($category1->term_id, (array) $mult_thumb_cat)) {
                                $is_mult_thumb = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    $mult_thumb = '';
    if ($is_no_thumb) {
        $class .= ' no-thumb';
        $graphic = '';
    } elseif ($is_mult_thumb) {
        $class .= ' mult-thumb';
        $_thumb_x4  = zib_posts_multi_thumbnail($post);
        $is_contain = _pz('list_thumb_fit', '') == 'contain';
        $mult_thumb = '<a' . $_post_target_blank . ' class="thumb-items' . ($is_contain ? ' contain' : '') . '" href="' . $get_permalink . '">' . $_thumb_x4 . '</a>';

        $graphic = '';
    } else {
        if (_pz('list_list_option', '', 'img_position') == 'right') {
            $graphic_class = 'post-graphic order1';
        } else {
            $graphic_class = 'post-graphic';
        }
        $graphic = '<div class="' . $graphic_class . '">' . $graphic . '</div>';
    }

    $class .= $args['no_margin'] ? ' no_margin' : '';

    if ($style == 'style2') {
        $excerpt = '<div class="item-excerpt muted-color text-ellipsis-2 mb6">' . $excerpt . '</div>';
        $class .= ' flex xx';

        $html .= '<posts class="' . $class . '">';
        $html .= $title;
        $html .= '<div class="flex">';
        $html .= $graphic;
        $html .= '<div class="item-body flex xx flex1 jsb">';
        $html .= $mult_thumb ? $mult_thumb : $excerpt;

        $html .= $badge;
        $html .= $meta;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</posts>';
    } else {
        $excerpt = '<div class="item-excerpt muted-color text-ellipsis mb6">' . $excerpt . '</div>';
        $class .= ' flex';

        $html .= '<posts class="' . $class . '">';
        $html .= $graphic;
        $html .= '<div class="item-body flex xx flex1 jsb">';
        $html .= $title;
        $html .= $mult_thumb ? $mult_thumb : $excerpt;
        $html .= '<div>';
        $html .= $badge;
        $html .= $meta;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</posts>';
    }

    return $html;
}

//获取卡片模式的文章列表
function zib_posts_mian_list_card($args = array())
{
    //准备必要参数
    $graphic = zib_get_posts_thumb_graphic();
    $title   = zib_get_posts_list_title();
    $badge   = zib_get_posts_list_badge($args);
    $meta    = zib_get_posts_list_meta(empty($args['no_author']), true);

    $class = 'posts-item card ajax-item';
    $style = _pz('list_card_option', '', 'style');
    $class .= $style && $style != 'null' ? ' ' . $style : '';

    $html = '';
    $html .= '<posts class="' . $class . '">';
    $html .= $graphic;
    $html .= '<div class="item-body">';
    $html .= $title;
    $html .= $badge;
    $html .= $meta;
    $html .= '</div>';
    $html .= '</posts>';
    return $html;
}

//获取文章列表的底部meta
function zib_get_posts_list_meta($show_author = true, $is_card = false, $post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (!isset($post->ID)) {
        return;
    }

    if (_pz('list_orderby') == 'modified') {
        $time = get_the_modified_time('Y-m-d H:i:s', $post);
    } else {
        $time = get_the_time('Y-m-d H:i:s', $post);
    }
    $show_time = _pz('post_list_time', true);
    $time_ago  = zib_get_time_ago($time);

    if ($show_author && _pz('post_list_author')) {

        $author_name = '';
        $author_id   = $post->post_author;
        if (!$is_card || !$show_time) {
            //列表，不是卡片
            $user = get_userdata($author_id);
            if (isset($user->display_name)) {
                $author_name = '<span class="' . ($show_time ? 'hide-sm ' : '') . 'ml6">' . $user->display_name . '</span>';
            }
        }

        $time_ago = $show_time ? '<span title="' . esc_attr($time) . '" class="' . ($is_card ? 'ml6' : 'icon-circle') . '">' . $time_ago . '</span>' : '';

        $author    = zib_get_avatar_box($author_id, 'avatar-mini') . $author_name;
        $meta_left = '<item class="meta-author flex ac">' . $author . $time_ago . '</item>';
    } else {
        $meta_left = $show_time ? '<item title="' . esc_attr($time) . '" class="icon-circle mln3">' . $time_ago . '</item>' : '';
    }

    $meta_right = '<div class="meta-right">' . zib_get_posts_meta($post) . '</div>';

    $html = '<div class="item-meta muted-2-color flex jsb ac">';
    $html .= $meta_left;
    $html .= $meta_right;
    $html .= '</div>';
    return $html;
}

//获取文章列表的标签badge
function zib_get_posts_list_badge($args = array())
{
    $defaults = array(
        'is_category' => false,
        'is_tag'      => false,
        'is_topics'   => false,
    );
    global $post;

    $badeg      = '';
    $args       = wp_parse_args((array) $args, $defaults);
    $show_badge = (array) _pz('list_badge_show', array('pay', 'tag', 'topics', 'cat'));

    if (in_array('pay', $show_badge)) {
        /** 付费金额 */
        $badeg .= zib_get_posts_list_pay_tags($post);
    }
    if (!$args['is_category'] && in_array('cat', $show_badge)) {
        $badeg .= zib_get_cat_tags('but', '<i class="fa fa-folder-open-o" aria-hidden="true"></i>', '', 3);
    }
    if (!$args['is_topics'] && in_array('topics', $show_badge)) {
        $badeg .= zib_get_topics_tags(0, 'but', '<i class="fa fa-cube" aria-hidden="true"></i>', '', 3);
    }
    if (!$args['is_tag'] && in_array('tag', $show_badge)) {
        $badeg .= zib_get_posts_tags('but', '# ', '', 3);
    }

    if (!$badeg && empty($show_badge[0])) {
        return;
    }

    $html = '<div class="item-tags scroll-x no-scrollbar mb6">';
    $html .= $badeg;
    $html .= '</div>';
    return $html;
}

//获取文章列表中的付费价格
function zib_get_posts_list_pay_tags($post)
{
    $posts_pay     = get_post_meta($post->ID, 'posts_zibpay', true);
    $get_permalink = get_permalink($post);
    $html          = '';

    if (!empty($posts_pay['pay_type']) && $posts_pay['pay_type'] != 'no') {
        $order_type_name  = zibpay_get_pay_type_name($posts_pay['pay_type']);
        $pay_price        = round((float) ($posts_pay['pay_price'] ?? 0), 2);
        $points_price     = isset($posts_pay['points_price']) ? (int) $posts_pay['points_price'] : 0;
        $pay_modo         = isset($posts_pay['pay_modo']) ? $posts_pay['pay_modo'] : 0;
        $pay_user_vip_1_s = _pz('pay_user_vip_1_s', true);
        $pay_user_vip_2_s = _pz('pay_user_vip_2_s', true);

        //免费资源
        if (($pay_modo === 'points' && !$points_price) || ($pay_modo !== 'points' && !$pay_price)) {
            return '<a rel="nofollow" href="' . $get_permalink . '#posts-pay" class="meta-pay but jb-yellow">免费资源</a>';
        }

        //限制购买
        $pay_limit = !empty($posts_pay['pay_limit']) ? (int) $posts_pay['pay_limit'] : 0;
        if ($pay_limit > 0 && ($pay_user_vip_1_s || $pay_user_vip_2_s)) {
            return '<a rel="nofollow" href="' . $get_permalink . '#posts-pay" data-toggle="tooltip" title="' . $order_type_name . '" class="meta-pay but jb-vip' . $pay_limit . '">' . zibpay_get_vip_icon($pay_limit, '') . ' 会员专属</a>';
        }

        if ($pay_modo === 'points') {
            $mark = zibpay_get_points_mark('');
            $html = '<a rel="nofollow" href="' . $get_permalink . '#posts-pay" class="meta-pay but jb-yellow">' . $order_type_name . '<span class="em09 ml3">' . $mark . '</span>' . $points_price . '</a>';
        } else {
            $mark = zibpay_get_pay_mark();
            $html = '<a rel="nofollow" href="' . $get_permalink . '#posts-pay" class="meta-pay but jb-yellow">' . $order_type_name . '<span class="em09 ml3">' . $mark . '</span>' . $pay_price . '</a>';
        }

    }
    return $html;
}

//获取文章列表中的付费价格
function zib_get_posts_list_pay_badge($post)
{
    $posts_pay = get_post_meta($post->ID, 'posts_zibpay', true);

    if (!empty($posts_pay['pay_type']) && $posts_pay['pay_type'] != 'no') {
        $order_type_name  = zibpay_get_pay_type_name($posts_pay['pay_type']);
        $order_type_icon  = zibpay_get_pay_type_icon($posts_pay['pay_type'], 'mr3');
        $pay_price        = round((float) $posts_pay['pay_price'], 2);
        $points_price     = isset($posts_pay['points_price']) ? (int) $posts_pay['points_price'] : 0;
        $pay_modo         = isset($posts_pay['pay_modo']) ? $posts_pay['pay_modo'] : 0;
        $pay_user_vip_1_s = _pz('pay_user_vip_1_s', true);
        $pay_user_vip_2_s = _pz('pay_user_vip_2_s', true);

        //免费资源
        if (($pay_modo === 'points' && !$points_price) || ($pay_modo !== 'points' && !$pay_price)) {
            $order_type_name = str_replace('付费', '免费', $order_type_name);
            return '<item class="meta-pay badg badg-sm mr6 c-yellow"  data-toggle="tooltip" title="' . $order_type_name . '">' . $order_type_icon . '免费</item>';
        }

        //限制购买
        $pay_limit = !empty($posts_pay['pay_limit']) ? (int) $posts_pay['pay_limit'] : 0;
        if ($pay_limit > 0 && ($pay_user_vip_1_s || $pay_user_vip_2_s)) {
            return '<item class="meta-pay badg badg-sm mr6 jb-vip' . $pay_limit . '"  data-toggle="tooltip" title="' . $order_type_name . '">' . zibpay_get_vip_icon($pay_limit, 'mr3') . '会员专属</item>';
        }

        if ($pay_modo === 'points') {
            $mark = zibpay_get_points_mark('');
            return '<item class="meta-pay badg badg-sm mr6 c-yellow"  data-toggle="tooltip" title="' . $order_type_name . '">' . $order_type_icon . '<span class="em09">' . $mark . '</span>' . $points_price . '</item>';
        } else {
            $mark = zibpay_get_pay_mark();
            return '<item class="meta-pay badg badg-sm mr6 c-yellow"  data-toggle="tooltip" title="' . $order_type_name . '">' . $order_type_icon . '<span class="em09">' . $mark . '</span>' . $pay_price . '</item>';
        }
    }
}

//获取文章列表的标题
function zib_get_posts_list_title($class = 'item-heading')
{
    $get_permalink      = get_permalink();
    $_post_target_blank = _post_target_blank();
    $title              = get_the_title() . get_the_subtitle(true, 'focus-color');

    $html = '<h2 class="' . $class . '"><a' . $_post_target_blank . ' href="' . $get_permalink . '">' . $title . '</a></h2>';
    return $html;
}

//获取文章列表的图片
function zib_get_posts_thumb_graphic($class = 'item-thumbnail')
{
    global $post;
    $get_permalink     = get_permalink();
    $post_target_blank = _post_target_blank();

    $_thumb      = '';
    $format_icon = '';
    $is_contain  = _pz('list_thumb_fit', '') == 'contain';

    $video = zib_get_post_meta($post->ID, 'featured_video', true);
    if ($video) {
        $format_icon = '<i class="fa fa-play-circle em12 mt6 c-white opacity8" aria-hidden="true"></i>';
        if (!$_thumb && _pz('list_thumb_video_s')) {
            $img_thumb = zib_post_thumbnail('', 'fit-cover radius8');
            $mute_attr = _pz('list_thumb_video_mute_s', true) ? '  data-volume="none"' : '  data-volume="100"';
            $_thumb    = '<div class="video-thumb-box" video-url="' . esc_url($video) . '"' . $mute_attr . '><div class="img-thumb">' . $img_thumb . '</div><div class="video-thumb"></div></div>';
        }
    }

    $has_slides = false;
    if (!$_thumb && _pz('list_thumb_slides_s')) {
        $slides_imgs = explode(',', zib_get_post_meta($post->ID, 'featured_slide', true));
        if (!empty($slides_imgs[0])) {
            $format_icon = $format_icon or '<badge class="b-black opacity8 mt6"><i class="fa fa-image mr3" aria-hidden="true"></i>' . count($slides_imgs) . '</badge>';

            $slides_args = array(
                'class'       => $class,
                'button'      => false,
                'pagination'  => 1,
                'echo'        => false,
                'gradient_bg' => $is_contain,
            );

            foreach ($slides_imgs as $slides_img) {
                $background = zib_get_attachment_image_src((int) $slides_img, _pz('thumb_postfirstimg_size'));
                $slide      = array(
                    'background' => isset($background[0]) ? $background[0] : '',
                    'link'       => array(
                        'url'    => $get_permalink,
                        'target' => $post_target_blank,
                    ),
                );
                $slides_args['slides'][] = $slide;
            }

            $has_slides = true;
            $_thumb     = zib_new_slider($slides_args, false);
        }
    }

    if (!$_thumb) {
        $_thumb = zib_post_thumbnail('', 'fit-cover radius8');
        $_thumb = '<a' . $post_target_blank . ' href="' . $get_permalink . '">' . $_thumb . '</a>';
    }

    if (!$format_icon) {
        $format = get_post_format();
        if (in_array($format, array('image', 'gallery'))) {
            $img_count   = zib_get_post_imgs_count($post);
            $format_icon = $img_count > 0 ? '<badge class="b-black opacity8 mr6 mt6"><i class="fa fa-image mr3" aria-hidden="true"></i>' . $img_count . '</badge>' : '';
        } elseif ($format == 'video') {
            $format_icon = '<i class="fa fa-play-circle em12 mr6 mt6 c-white opacity8" aria-hidden="true"></i>';
        }
    }
    $format_icon = $format_icon ? '<div class="abs-center right-top">' . $format_icon . '</div>' : '';

    $sticky = '';
    if (zib_is_sticky()) {
        $sticky = '<badge class="img-badge left jb-red">置顶</badge>';
    }

    $attr = '';
    if ($is_contain && !$has_slides) {
        $class .= ' contain gradient-bg';
        $attr = ' data-opacity="0.2"';
    }

    $html = '<div class="' . $class . '"' . $attr . '>';
    $html .= $_thumb;
    $html .= $format_icon;
    $html .= $sticky;
    $html .= '</div>';
    return $html;
}

//迷你版文章列表
function zib_posts_mini_list($args = array(), $new_query = false)
{

    $defaults = array(
        'type'          => 'auto',
        'no_author'     => false,
        'no_margin'     => false,
        'is_mult_thumb' => false,
        'is_no_thumb'   => false,
        'is_card'       => false,
        'is_category'   => is_category(),
        'is_search'     => is_search(),
        'is_home'       => is_home(),
        'author_archive' => call_user_func('is_' . 'author'),
        'is_tag'        => is_tag(),
    );

    if (_pz('list_show_type', 'no_margin') == 'no_margin') {
        $defaults['no_margin'] = true;
    }
    $args   = wp_parse_args((array) $args, $defaults);
    $legacy_author_key = 'is_' . 'author';
    if (isset($args[$legacy_author_key])) {
        $args['author_archive'] = $args[$legacy_author_key];
        unset($args[$legacy_author_key]);
    }
    $number = 0;
    if ($new_query) {
        while ($new_query->have_posts()): $new_query->the_post();
            $number++;
            zib_posts_mini_while($args, $number);
        endwhile;
    } else {
        while (have_posts()): the_post();
            zib_posts_mini_while($args);
        endwhile;
    }
    wp_reset_query();
}

function zib_posts_mini_while($args = array(), $number = 0)
{
    $defaults = array(
        'class'       => '',
        'show_thumb'  => true,
        'show_meta'   => true,
        'show_number' => true,
        'echo'        => true,
    );

    $args          = wp_parse_args((array) $args, $defaults);
    $target_blank  = _post_target_blank();
    $get_permalink = get_permalink();

    global $post;

    $title = '<a ' . $target_blank . ' href="' . $get_permalink . '">' . get_the_title() . '<span class="focus-color">' . get_the_subtitle(false) . '</span></a>';
    if ($args['show_number']) {
        $cls   = array('c-red', 'c-yellow', 'c-purple', 'c-blue', 'c-green');
        $title = '<span class="badg badg-sm mr3 ' . (!empty($cls[$number - 1]) ? $cls[$number - 1] : '') . '">' . $number . '</span>' . $title;
    }
    $lists_class = 'posts-mini ' . $args['class'];
    $title_l     = '<h2 class="item-heading' . ($args['show_thumb'] ? ' text-ellipsis-2' : ' text-ellipsis' . (!$args['show_meta'] && !$args['show_number'] ? ' icon-circle' : '')) . '">' . $title . '</h2>';

    $thumb = '';
    if ($args['show_thumb']) {
        $_thumb = zib_post_thumbnail('', 'fit-cover radius8');
        $thumb  = '<div class="mr10"><div class="item-thumbnail"><a' . $target_blank . ' href="' . $get_permalink . '">' . $_thumb . '</a></div></div>';
    }

    $meta = '';
    if ($args['show_meta']) {
        if (_pz('list_orderby') == 'modified') {
            $time = get_the_modified_time('Y-m-d H:i:s', $post);
        } else {
            $time = get_the_time('Y-m-d H:i:s', $post);
        }
        $show_time = _pz('post_list_time', true);
        $time_ago  = zib_get_time_ago($time);

        if (_pz('post_list_author')) {
            global $authordata;
            $user_id = isset($authordata->ID) ? $authordata->ID : 0;
            $author  = zib_get_avatar_box($user_id, 'avatar-mini');

            if (!$show_time) {
                $time_ago = isset($authordata->display_name) ? $authordata->display_name : '';
            }

            $meta_left = '<item class="meta-author flex ac">' . $author . '<span class="ml6">' . $time_ago . '</span></item>';
        } else {
            $meta_left = $show_time ? '<item title="' . esc_attr($time) . '" class="icon-circle mln3">' . $time_ago . '</item>' : '';
        }

        /** 付费金额 */
        $pay_badge = zib_get_posts_list_pay_badge($post);

        if (!$meta_left) {
            $meta_left = $pay_badge;
            $pay_badge = '';
        }

        if (!$meta_left) {
            $meta_left = '<item></item>';
        }

        //阅读数量
        $meta_right = $pay_badge . '<item class="meta-view">' . zib_get_svg('view') . get_post_view_count('', '') . '</item>';

        $meta = '<div class="item-meta muted-2-color flex jsb ac' . (!$args['show_thumb'] ? ' mt6' : '') . '">';
        $meta .= $meta_left;
        $meta .= '<div class="meta-right">' . $meta_right . '</div>';
        $meta .= '</div>';
    }

    $html = '';
    $html .= '<div class="' . $lists_class . '">';
    $html .= $thumb;
    $html .= '<div class="posts-mini-con flex xx flex1 jsb">';
    $html .= $title_l;
    $html .= $meta;
    $html .= '</div>';
    $html .= '</div>';

    if ($args['echo']) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * 分页函数
 */
function zib_paging($ajax = true, $echo = true)
{

    if (is_singular()) {
        return;
    }

    global $wp_query, $paged, $wp_rewrite;

    $max_page = $wp_query->max_num_pages;
    if ($max_page == 1) {
        return;
    }

    $ajax = _pz('paging_ajax_s', true);
    if ($ajax) {
        //ias自动加载
        $nex = _pz('ajax_trigger', '加载更多');
        //  add_filter('next_posts_link_attributes', 'zib_next_posts_link_attributes_add_ias_class');
        $next_posts_link = get_next_posts_link($nex);
        if (!$next_posts_link) {
            return;
        }

        $ias_max = (int) _pz('ias_max', 3);
        $ias     = (_pz('paging_ajax_ias_s', true) && ($paged <= $ias_max || !$ias_max)) ? ' class="next-page ajax-next lazyload" lazyload-action="ias"' : '  class="next-page ajax-next"';

        $pag_html = $next_posts_link ? '<div class="text-center theme-pagination ajax-pag"><div' . $ias . '>' . $next_posts_link . '</div></div>' : '';
    } else {
        $wp_is_mobile = wp_is_mobile();
        $args         = array(
            'prev_text' => '<i class="fa fa-angle-left em12"></i><span class="hide-sm ml6">上一页</span>',
            'next_text' => '<span class="hide-sm mr6">下一页</span><i class="fa fa-angle-right em12"></i>',
            'type'      => 'array',
            'mid_size'  => $wp_is_mobile ? 1 : 2,
        );
        $array = paginate_links($args);
        if (!$array) {
            return;
        }

        //添加填写跳转翻页
        if ($max_page > 8) {

            $pagenum_link = html_entity_decode(get_pagenum_link());
            $url_parts    = explode('?', $pagenum_link);

            // Append the format placeholder to the base URL.
            $pagenum_link = trailingslashit($url_parts[0]) . '%_%';

            // URL base depends on permalink settings.
            $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
            $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged') : '?paged=%#%';
            $base = str_replace('%_%', $format, $pagenum_link);

            // Merge additional query vars found in the original URL into 'add_args' array.
            if (isset($url_parts[1])) {
                // Find the format argument.
                $format       = explode('?', str_replace('%_%', $format, $base));
                $format_query = isset($format[1]) ? $format[1] : '';
                wp_parse_str($format_query, $format_args);

                // Find the query args of the requested URL.
                wp_parse_str($url_parts[1], $url_query_args);

                // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
                foreach ($format_args as $format_arg => $format_arg_value) {
                    unset($url_query_args[$format_arg]);
                }

                $base = str_replace('%#%', 'asdavsdgsdgdfhfja1105123165102051561511a16ssdggsdgqqww', $base);

                if ($url_query_args) {
                    $base = add_query_arg($url_query_args, $base);
                }
                $base = str_replace('asdavsdgsdgdfhfja1105123165102051561511a16ssdggsdgqqww', '%#%', $base);
            }

            $array[] = '<a class="pag-jump page-numbers" href="javascript:;"><input autocomplete="off" max="' . $max_page . '" current="' . $paged . '" base="' . $base . '" type="' . ($wp_is_mobile ? 'number' : 'text') . '" class="form-control jump-input" name="pag-go"><span class="hi de-sm mr6 jump-text">跳转</span><i class="jump-icon fa fa-angle-double-right em12"></i></a>';
        }

        $pag_html = '<div class="pagenav ajax-pag">';
        $pag_html .= implode('', $array);
        $pag_html .= '</div>';
    }

    if ($echo) {
        echo $pag_html;
    } else {
        return $pag_html;
    }
}

function zib_next_posts_link_attributes_add_ias_class($attr = '')
{
    $attr .= ' class="ias-btn"';
    return $attr;
}

/**
 * @description: 简单的骨架屏幕构架
 * @param {*}
 * @return {*}
 */
function zib_get_post_placeholder($type = 'lists', $num = 1, $class = '')
{
    if ($type === 'card') {
        $h = '<div class="posts-item card ' . $class . '"><div class="item-thumbnail"><div class="radius8 item-thumbnail placeholder"></div> </div><div class="item-body "> <h2 class="item-excerpt placeholder t1 item-heading"></h2> <p class="mt10 placeholder k2"></p><i class="flex jsb"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i></div></div>';
    } else {
        $h = '<div class="posts-item list flex ' . $class . '"><div class="post-graphic"><div class="radius8 item-thumbnail placeholder"></div> </div><div class="item-body flex xx flex1 jsb"> <p class="placeholder t1"></p> <h4 class="item-excerpt placeholder k1"></h4><p class="placeholder k2"></p><i><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i></div></div>';
    }

    return str_repeat($h, $num);
}

/**
 * @description: 文章榜单
 * @param {*} $args
 * @param {*} $echo
 * @return {*}
 */
function zib_hot_posts($args = array(), $echo = false)
{
    $defaults = array(
        'orderby'      => 'views',
        'limit_day'    => 0,
        'target_blank' => '',
        'taxonomy'     => '',
        'orderby'      => 'date',
        'count'        => 6,
    );
    $args         = wp_parse_args((array) $args, $defaults);
    $target_blank = !empty($args['target_blank']) ? ' target="_blank"' : '';

    //准备文章
    $posts_args = array(
        'showposts'           => $args['count'],
        'ignore_sticky_posts' => 1,
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'order'               => 'DESC',
        'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
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
                'order' => 'DESC',
            ),
        );
    }
    //文章限制时间
    if ($args['limit_day'] > 0) {
        $current_time             = current_time('Y-m-d H:i:s');
        $posts_args['date_query'] = array(
            array(
                'after'     => date('Y-m-d H:i:s', strtotime('-' . $args['limit_day'] . ' day', strtotime($current_time))),
                'before'    => $current_time,
                'inclusive' => true,
            ),
        );
    }

    //循环文章内容
    $posts_html = '';
    $posts_i    = 1;
    $new_query  = new WP_Query($posts_args);
    //  echo json_encode($new_query);
    while ($new_query->have_posts()) {
        $new_query->the_post();
        $title = get_the_title() . get_the_subtitle(false);

        $top_bagd_class = array('', 'jb-red', 'jb-yellow');
        $top_bagd       = '<badge class="img-badge left hot ' . ($posts_i == 1 ? 'em12' : '') . (isset($top_bagd_class[$posts_i - 1]) ? $top_bagd_class[$posts_i - 1] : 'b-gray') . '"><i>TOP' . $posts_i . '</i></badge>';
        $_meta          = '';
        $time_ago       = '<i class="fa fa-clock-o mr3" aria-hidden="true"></i>' . zib_get_time_ago(get_the_time('Y-m-d H:i:s'));
        $permalink      = get_permalink();
        if ($orderby == 'favorite') {
            $_meta = get_post_favorite_count('', '人收藏');
        } elseif ($orderby == 'like') {
            $_meta = get_post_like_count('', '人点赞');
        } elseif ($orderby == 'comment_count') {
            $_meta = get_post_comment_count('', '条讨论');
        }
        if (!$_meta) {
            $_meta = get_post_view_count('', '人已阅读');
        }
        //排第一的文章
        if ($posts_i == 1) {
            $_thumb = zib_post_thumbnail('large', 'fit-cover radius8');
            $posts_html .= '<div class="relative">';
            $posts_html .= '<a' . $target_blank . ' href="' . $permalink . '">';
            $posts_html .= '<div class="graphic hover-zoom-img" style="padding-bottom: 60%!important;">';
            $posts_html .= $_thumb;
            $posts_html .= '<div class="absolute linear-mask"></div>';
            $posts_html .= '<div class="abs-center left-bottom box-body">';
            $posts_html .= '<div class="mb6"><span class="badg b-theme badg-sm">' . $_meta . '</span></div>';
            $posts_html .= zib_str_cut($title, 0, 32);
            $posts_html .= '</div>';
            $posts_html .= '</div>';
            $posts_html .= '</a>';
            $posts_html .= $top_bagd;
            $posts_html .= '</div>';
        } else {
            $_thumb = zib_post_thumbnail('large', 'fit-cover radius8');

            $img_html = '';
            $img_html .= '<a' . $target_blank . ' href="' . $permalink . '">';
            $img_html .= '<div class="graphic">';
            $img_html .= $_thumb;
            $img_html .= '</div>';
            $img_html .= '</a>';
            $posts_meta = '<div class="px12 muted-3-color text-ellipsis flex jsb"><span>' . $time_ago . '</span><span>' . $_meta . '</span></div>';

            $posts_html .= '<div class="flex mt15 relative hover-zoom-img">';
            $posts_html .= $img_html;
            $posts_html .= '<div class="term-title ml10 flex xx flex1 jsb">';
            $posts_html .= '<div class="text-ellipsis-2"><a class=""' . $target_blank . ' href="' . $permalink . '">' . $title . '</a></div>';
            $posts_html .= $posts_meta;
            $posts_html .= '</div>';
            $posts_html .= $top_bagd;
            $posts_html .= '</div>';
        }

        $posts_i++;
    }
    wp_reset_query();

    $html = '<div class="zib-widget hot-posts">' . $posts_html . '</div>';
    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}
