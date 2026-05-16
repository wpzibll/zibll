<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2025-07-07 15:11:16
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */
function zib_share()
{
    echo zib_get_share();
}

/**
 * 获取分类term的分享按钮
 * @param mixed $term 分类term
 * @param string $class
 * @param bool $modal
 * @return string
 */
function zib_get_term_share_btn($term = null, $class = '', $modal = false)
{
    if (!is_object($term)) {
        $term = get_term($term);
    }

    if (empty($term->term_id)) {
        return;
    }

    $is_m = wp_is_mobile();
    $icon = zib_get_svg('share');

    if ($is_m || $modal) {

        $con  = $icon . '<text>分享</text>';
        $args = array(
            'tag'           => 'a',
            'class'         => $class,
            'mobile_bottom' => true,
            'height'        => 243,
            'data_class'    => 'modal-mini',
            'text'          => $con,
            'query_arg'     => array(
                'action' => 'share_modal',
                'id'     => $term->term_id,
                'type'   => 'term',
            ),
        );

        //每次都刷新的modal
        return zib_get_refresh_modal_link($args);
    } else {
        $class = $class ? ' ' . $class : '';

        return '<span class="hover-show dropup' . $class . '">
        ' . $icon . '<text>分享</text><div class="zib-widget hover-show-con share-button dropdown-menu">' . zib_get_term_share('', $term) . '</div></span>';
    }
}

//获取文章分享按钮
function zib_get_post_share_btn($post = null, $class = '', $modal = false)
{
    $post = get_post($post);

    $is_m = wp_is_mobile();
    $icon = zib_get_svg('share');
    if ($is_m || $modal) {

        $con  = $icon . '<text>分享</text>';
        $args = array(
            'tag'           => 'a',
            'class'         => $class,
            'mobile_bottom' => true,
            'height'        => 243,
            'data_class'    => 'modal-mini',
            'text'          => $con,
            'query_arg'     => array(
                'action' => 'share_modal',
                'id'     => $post->ID,
                'type'   => 'post',
            ),
        );

        //每次都刷新的modal
        return zib_get_refresh_modal_link($args);
    } else {
        $class = $class ? ' ' . $class : '';

        return '<span class="hover-show dropup' . $class . '">
        ' . $icon . '<text>分享</text><div class="zib-widget hover-show-con share-button dropdown-menu">' . zib_get_share('', $post) . '</div></span>';
    }
}

function zib_get_share($class = '', $post = null)
{
    $btns = zib_get_posts_share_btns(_pz('share_items', array('qzone', 'weibo', 'qq', 'poster', 'copy')), $post);
    if (!$btns) {
        return;
    }

    $class = $class ? ' class="' . $class . '"' : '';
    return '<div' . $class . '>' . $btns . '</div>';
}

/**
 * @description: 获取文章分享列表
 * @param {*} $btns_opt
 * @param {*} $post
 * @return {*}
 */
function zib_get_posts_share_btns($btns_opt, $post = null)
{
    $post = get_post($post);

    if (empty($post->ID)) {
        return;
    }

    $subtitle = trim(strip_tags(zib_get_post_meta($post->ID, 'subtitle', true)));

    $title = trim(strip_tags(get_the_title($post))) . $subtitle;

    $desc = zib_get_excerpt(160, '...', $post);
    $pic  = zib_post_thumbnail('full', '', true, $post);

    $url = get_permalink($post);

    return zib_get_share_btns($btns_opt, $title, $desc, $pic, $url, $post->ID);
}

/**
 * @description: 获取分类term的分享
 * @param {*} $class
 * @param {*} $term
 * @return {*}
 */
function zib_get_term_share($class = '', $term = null)
{

    $btns = zib_get_term_share_btns(_pz('share_items', array('qzone', 'weibo', 'qq', 'poster', 'copy')), $term);

    if (!$btns) {
        return;
    }

    $class = $class ? ' class="' . $class . '"' : '';
    return '<div' . $class . '>' . $btns . '</div>';
}

function zib_get_term_share_btns($btns_opt, $term = null)
{
    $term = get_term($term);

    if (empty($term->term_id)) {
        return;
    }

    $title = trim(strip_tags($term->name));

    $desc = zib_str_cut($term->description, 0, 160, '...');
    $pic  = zib_get_taxonomy_img_url($term->term_id, 'full');

    $url = get_term_link($term);

    return zib_get_share_btns($btns_opt, $title, $desc, $pic, $url, 'term_' . $term->term_id);
}

/**
 * @description: 获取分享按钮列表
 * @param {*} $btns_opt
 * @param {*} $title
 * @param {*} $content
 * @param {*} $pic
 * @param {*} $url
 * @param {*} $poster_id
 * @return {*}
 */
function zib_get_share_btns($btns_opt, $title, $content, $pic, $url, $poster_id = 0)
{
    if (!$btns_opt || !is_array($btns_opt)) {
        return;
    }

    if (!$poster_id) {
        $poster_id = get_queried_object_id();
    }

    $link_title = $title . zib_get_delimiter_blog_name();

    //返利链接
    $user_id = get_current_user_id();
    if (_pz('pay_rebate_s') && $user_id) {
        $url = zibpay_get_rebate_link($user_id, $url);
    }

    $url = esc_url($url);
    $pic = esc_url($pic);

    //彩色
    $args = array(
        'qzone'  => array(
            'href'   => 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . $url . '&#38;title=' . $link_title . '&#38;pics=' . $pic . '&#38;summary=' . $content,
            'icon'   => zib_get_svg('qzone-color'),
            'text'   => 'QQ空间',
            'target' => '_blank',
        ),
        'weibo'  => array(
            'href'   => 'https://service.weibo.com/share/share.php?url=' . $url . '&#38;title=' . $link_title . '&#38;pic=' . $pic . '&#38;searchPic=false',
            'icon'   => zib_get_svg('weibo-color'),
            'text'   => '微博',
            'target' => '_blank',
        ),
        'qq'     => array(
            'href'   => 'https://connect.qq.com/widget/shareqq/index.html?url=' . $url . '&#38;title=' . $link_title . '&#38;pics=' . $pic . '&#38;desc=' . $content,
            'icon'   => zib_get_svg('qq-color'),
            'text'   => 'QQ好友',
            'target' => '_blank',
        ),
        'poster' => array(
            'href'   => 'javascript:;',
            'icon'   => zib_get_svg('poster-color'),
            'text'   => '海报分享',
            'target' => '',
            'attr'   => 'poster-share="' . $poster_id . '"',
        ),
        'copy'   => array(
            'href'   => 'javascript:;',
            'icon'   => zib_get_svg('copy-color'),
            'text'   => '复制链接',
            'target' => '',
            'attr'   => 'data-clipboard-text="' . $url . '" data-clipboard-tag="链接"',
        ),
    );

    if (!_pz('share_img')) {
        unset($args['poster']);
    }

    $btns = '';

    foreach ($btns_opt as $key) {
        if (isset($args[$key])) {
            $target = $args[$key]['target'] ? '  target="_blank"' : '';
            $attr   = !empty($args[$key]['attr']) ? ' ' . $args[$key]['attr'] : '';
            $href   = $args[$key]['href'] ? $args[$key]['href'] : 'javascript:;';

            $btns .= '<a rel="nofollow" class="share-btn ' . $key . '"' . $target . $attr . ' title="' . $args[$key]['text'] . '" href="' . $href . '"><icon>' . $args[$key]['icon'] . '</icon><text>' . $args[$key]['text'] . '<text></a>';
        }
    }

    return $btns;
}
