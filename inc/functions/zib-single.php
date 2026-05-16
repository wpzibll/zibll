<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2026-04-21 18:08:17
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//文章页主内容
function zib_single()
{
    zib_single_header();
    do_action('zib_single_before'); //添加钩子
    echo '<article class="article main-bg theme-box box-body radius8 main-shadow">';
    zib_single_content();
    echo '</article>';
    do_action('zib_single_after'); //添加钩子
}

function zib_single_after_box()
{
    if (_pz('yiyan_single_box')) {
        zib_yiyan('yiyan-box main-bg theme-box text-center box-body radius8 main-shadow');
    }

    if (_pz('post_authordesc_s')) {
        $args = array(
            'user_id'     => get_the_author_meta('ID'),
            'show_button' => false,
            'show_img_bg' => false,
            'class'       => 'author',
        );
        zib_get_user_card_box($args, true);
    }

    if (_pz('post_prevnext_s')) {
        zib_posts_prevnext();
    }

    if (_pz('post_related_s')) {
        zib_posts_related(_pz('related_title'), _pz('post_related_n'), _pz('post_related_orderby', 'views'));
    }
}
add_action('zib_single_after', 'zib_single_after_box');

function zib_get_breadcrumbs()
{
    //静态变量
    static $zib_get_breadcrumbs = null;
    if ($zib_get_breadcrumbs !== null) {
        return $zib_get_breadcrumbs;
    }

    if (!is_single() || !_pz('breadcrumbs_single_s', true)) {
        $zib_get_breadcrumbs = '';
        return $zib_get_breadcrumbs;
    }

    $categorys = get_the_category();
    if ($categorys) {
        $category = $categorys[0];
        $lin      = '<ul class="breadcrumb">
		<li><a href="' . get_bloginfo('url') . '"><i class="fa fa-map-marker"></i> ' . (!_pz('breadcrumbs_home_text', true) ? get_bloginfo('name') : '首页') . '</a></li><li>
		' . get_category_parents($category->term_id, true, ' </li><li> ') . (!_pz('breadcrumbs_single_text', true) ? get_the_title() : '正文') . '</li></ul>';

        $zib_get_breadcrumbs = $lin;
        return $zib_get_breadcrumbs;
    } else {
        $zib_get_breadcrumbs = '';
        return $zib_get_breadcrumbs;
    }
}

/**
 * @description: 获取文章的顶部封面
 * @param {*}
 * @return {*}
 */
function zib_single_header()
{
    $cover = zib_single_cover();
    echo $cover ? $cover : zib_get_breadcrumbs();
}

//获取文章顶部封面
function zib_single_cover()
{
    global $post;

    //静态变量
    static $single_cover_html = null;
    if ($single_cover_html !== null) {
        return $single_cover_html;
    }

    $breadcrumbs = zib_get_breadcrumbs();

    $_thumb = '';
    if (!$_thumb && _pz('article_video_cover')) {
        $video = zib_get_post_meta($post->ID, 'featured_video', true);
        if ($video) {
            $get_title = get_the_title() . get_the_subtitle();
            $title     = '<i class="fa fa-play-circle-o mr6 opacity8" aria-hidden="true"></i>' . $get_title;

            $pic = zib_get_post_meta($post->ID, 'cover_image', true);
            $pic = $pic ? $pic : zib_post_thumbnail('full', 0, true);

            $scale_height = _pz('article_video_cover_option', 0, 'scale');
            $video_html   = zib_get_dplayer($video, $pic, $scale_height);

            //视频剧集
            $episode_array = zib_get_post_meta($post->ID, 'featured_video_episode', true);
            $episode_lists = '';
            $episode_index = 1;
            if ($episode_array && is_array($episode_array)) {
                foreach ($episode_array as $episode) {
                    if (!empty($episode['url'])) {
                        $episode_index++;
                        $episode_title = $episode['title'] ? $episode['title'] : '第' . $episode_index . '集';
                        $episode_lists .= '<a href="javascript:;" class="switch-video text-ellipsis" data-index="' . $episode_index . '" video-url="' . $episode['url'] . '"><span class="mr6 badg badg-sm">' . $episode_index . '</span><i class="episode-active-icon"></i>' . $episode_title . '</a>';
                    }
                }
            }

            $episode_html = '';
            if ($episode_lists) {
                $episode_title = zib_get_post_meta($post->ID, 'featured_video_title', true);
                $episode_title = $episode_title ? $episode_title : '第1集';
                $episode_html  = '<div class="featured-video-episode mt10">';
                $episode_html .= '<a href="javascript:;" class="switch-video text-ellipsis active" data-index="1" video-url="' . $video . '"><span class="mr6 badg badg-sm">1</span><i class="episode-active-icon"></i>' . $episode_title . '</a>';
                $episode_html .= $episode_lists;
                $episode_html .= '</div>';

                $title = '<span class="badg badg-sm b-theme mr6"><i class="fa fa-play-circle mr3" aria-hidden="true"></i>共' . $episode_index . '集</span>' . $get_title;
            }

            $metas = zib_get_single_meta_box('mb10 ml10 shrink0', 'up');
            $_thumb .= '<div class="single-video">';
            $_thumb .= $video_html;
            $_thumb .= '<div class="single-video-footer dplayer-featured">';
            $_thumb .= '<div class="flex jsb at">';
            $_thumb .= $breadcrumbs;
            $_thumb .= $metas;
            $_thumb .= '</div>';
            $_thumb .= '<h1 class="article-title">' . $title . '</h1>';
            $_thumb .= $episode_html;

            $_thumb .= '</div>';
            $_thumb .= '</div>';
        }
    }

    //幻灯片
    if (!$_thumb && _pz('article_slide_cover')) {
        $slides_imgs = explode(',', zib_get_post_meta($post->ID, 'featured_slide', true));
        if (!empty($slides_imgs[0])) {
            $slides_args          = _pz('article_slide_cover_option');
            $slides_args['class'] = 'mb20 single-cover-slide imgbox-container';
            $slides_args['echo']  = false;

            $title               = get_the_title() . get_the_subtitle();
            $title               = '<div class="abs-center left-bottom single-cover-con"><h1 class="article-title title-h-left">' . $title . '</h1>' . $breadcrumbs . '</div>';
            $metas               = zib_get_single_meta_box('cover-meta abs-right');
            $slides_args['html'] = $title . $metas;
            $slides_args['lazy'] = zib_is_lazy('lazy_cover', true);

            foreach ($slides_imgs as $slides_img) {
                $background = zib_get_attachment_image_src((int) $slides_img, 'full');
                $slide      = array(
                    'background' => isset($background[0]) ? $background[0] : '',
                );
                $slides_args['slides'][] = $slide;
            }
            $_thumb = zib_new_slider($slides_args, false);
        }
    }

    //图片
    if (!$_thumb && _pz('article_image_cover')) {
        $image = zib_get_post_meta($post->ID, 'cover_image', true);
        if ($image) {
            $title = get_the_title() . get_the_subtitle();
            $src   = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
            $img   = '<img ' . (zib_is_lazy('lazy_cover', true) ? 'class="fit-cover lazyload" src="' . $src . '" data-src="' . $image . '"' : 'class="fit-cover"  src="' . $image . '"') . ' alt="' . esc_attr(strip_tags($title)) . '">';
            $title = '<div class="abs-center left-bottom single-cover-con"><h1 class="article-title title-h-left">' . $title . '</h1>' . $breadcrumbs . '</div>';
            $metas = zib_get_single_meta_box('cover-meta abs-right');
            $_thumb .= '<div class="graphic mb20 single-cover imgbox-container">';
            $_thumb .= $img;
            $_thumb .= $title;
            $_thumb .= $metas;
            $_thumb .= '</div>';
        }
    }

    $html              = $_thumb ? '<div class="single-head-cover">' . $_thumb . '</div>' : '';
    $single_cover_html = $html;
    return $html;
}

function zib_single_content()
{
    zib_single_box_header();
    do_action('zib_single_box_content_before'); //添加钩子
    zib_single_box_content();
    do_action('zib_single_box_content_after'); //添加钩子
}

//获取文章时间的显示
function zib_get_post_time_tooltip($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $modified_time = get_the_modified_time('Y-m-d H:i:s', $post);

    if (isset($post->post_status) && $post->post_status !== 'publish') {
        return '<span data-toggle="tooltip" data-placement="bottom" title="' . date('Y年m月d日 H:i', strtotime($modified_time)) . '更新">' . zib_get_time_ago($modified_time) . '更新</span>';
    }

    $time = get_the_time('Y-m-d H:i:s', $post);
    if (strtotime($modified_time) > strtotime($time)) {
        //更新时间大于发布时间，显示更新时间
        $time_html = '<span data-toggle="tooltip" data-placement="bottom" title="' . date('Y年m月d日 H:i', strtotime($time)) . '发布">' . zib_get_time_ago($modified_time) . '更新</span>';
    } else {
        $time_html = '<span data-toggle="tooltip" data-placement="bottom" title="' . date('Y年m月d日 H:i', strtotime($time)) . '发布">' . zib_get_time_ago($time) . '发布</span>';
    }

    return $time_html;
}

/**
 * @description: 文章页文章头部
 * @param {*}
 * @return {*}
 */
function zib_single_box_header()
{

    $user_id       = get_the_author_meta('ID');
    $is_show_cover = zib_single_cover();

    $time_html    = _pz('post_single_hide_time_s') ? '' : zib_get_post_time_tooltip();
    $user_box     = zib_get_post_user_box($user_id, $time_html, 'article-avatar');
    $status_badge = zib_get_post_status_badge();

    $html = '<div class="article-header theme-box clearfix relative">';
    $html .= $status_badge;
    $html .= !$is_show_cover ? '<h1 class="article-title"> <a href="' . get_permalink() . '">' . get_the_title() . get_the_subtitle() . '</a></h1>' : '';
    $html .= '<div class="article-avatar">';
    $html .= $user_box;
    $html .= '<div class="relative"><i class="line-form-line"></i>';
    $html .= !$is_show_cover ? zib_get_single_meta_box() : '';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    echo $html;
}

function zib_get_single_meta_box($class = 'abs-right', $direction = 'down')
{
    if (!is_single()) {
        return;
    }
    $more_dropdown = zib_get_post_more_dropdown(null, 'pull-right', 'but cir post-drop-meta', zib_get_svg('menu_2'), $direction);
    $more_dropdown = $more_dropdown ? '<div class="clearfix ml6">' . $more_dropdown . '</div>' : '';

    return '<div class="flex ac single-metabox ' . $class . '"><div class="post-metas">' . zib_get_posts_meta() . '</div>' . $more_dropdown . '</div>';
}

/**
 * @description: 文章页文字导航显示判断
 * @param {*}
 * @return {*}
 */
function zib_is_show_posts_nav()
{
    global $post;
    $show_nav = zib_get_post_meta($post->ID, 'no_article-navs', true);
    if (_pz('article_nav') && !($show_nav)) {
        return true;
    }
    return false;
}

/**
 * @description: 文章页内容
 * @param {*}
 * @return {*}
 */
function zib_single_box_content()
{
    global $post;
    $show_nav         = zib_is_show_posts_nav();
    $is_max_height    = zib_get_post_meta($post->ID, 'article_maxheight_xz', true);
    $max_height_style = '';
    $max_height_class = '';
    $show_nav_data    = '';
    if ($show_nav) {
        $show_nav_data .= 'data-nav="posts"';
    }
    //文章高度限制
    if (_pz('article_maxheight_kg') || $is_max_height) {
        $max_height_class .= ' limit-height';
        $max_height       = (int) _pz('article_maxheight');
        $max_height       = $max_height ?: 1000;
        $max_height_style = ' style="max-height:' . $max_height . 'px;" data-maxheight="' . ($max_height - 80) . '"';
    }
    ?>
    <div class="article-content">
        <?php zib_single_content_header(); ?>
        <?php echo _pz('post_front_content'); ?>
        <div <?php echo $show_nav_data; ?><?php echo $max_height_style; ?>class="theme-box wp-posts-content<?php echo $max_height_class; ?>">
            <?php

    do_action('zib_posts_content_before', $post); //添加钩子

    the_content();

    //文章分页
    wp_link_pages(
        array(
            'before' => '<p class="text-center post-nav-links radius8 padding-6">',
            'after'  => '</p>',
        )
    );
    do_action('zib_posts_content_after', $post); //添加钩子
    echo _pz('post_after_content');
    ?>
            <?php tb_xzh_render_tail(); ?>
        </div>
        <?php zib_single_content_footer($post); ?>
    </div>
    <?php
zib_single_content_footer_action();
    ?>
<?php }

//挂钩the_content
function zib_single_content_allow_view($content)
{
    $data = zib_get_post_allow_view_data();
    if (!$data['allow']) {
        return $data['not_html'];
    }

    return $content;
}
add_filter('the_content', 'zib_single_content_allow_view');

//挂钩简介
function zib_single_content_excerpt_allow_view($excerpt, $post)
{

    $data = zib_get_post_allow_view_data($post);
    if (!$data['allow']) {
        return '';
    }

    return $excerpt;
}
add_filter('zib_get_excerpt', 'zib_single_content_excerpt_allow_view', 10, 2);

//获取文章的阅读权限数据
function zib_get_post_allow_view_data($post = null)
{

    $data = array(
        'allow'        => true,
        'type'         => '',
        'not_html'     => '',
        'allow_reason' => '',
        'html'         => '',
    );

    $post = get_post($post);
    if (!$post || $post->post_type !== 'post') {
        return $data;
    }

    //全局变量，避免多次查询
    global $post_allow_view_data;
    if (isset($post_allow_view_data[$post->ID])) {
        return $post_allow_view_data[$post->ID];
    }

    $cats = get_the_category($post->ID);
    if (!$cats) {
        return $data;
    }

    //循环判断分类的阅读权限
    foreach ($cats as $cat) {
        $allow_view = zib_get_post_cat_allow_view($cat->term_id);
        if (!$allow_view['allow']) {
            return $allow_view;
        }
    }

    $post_allow_view_data[$post->ID] = $data;
    return $data;
}

function zib_single_content_header()
{
    if (_pz('yiyan_single_content_header')) {
        zib_yiyan('article-yiyan theme-box text-center radius8 main-shadow yiyan-box');
    }
}

/**
 * @description: 文章页文章底部
 * @param {*}
 * @return {*}
 */
function zib_single_content_footer($post)
{
    $cat = zib_get_topics_tags('', 'but ml6 radius', '<i class="fa fa-cube" aria-hidden="true"></i>');
    $cat .= zib_get_cat_tags('but ml6 radius', '<i class="fa fa-folder-open-o" aria-hidden="true"></i>');
    $tags = zib_get_posts_tags('but ml6 radius', '# ');

    if (_pz('yiyan_single_content_footer')) {
        zib_yiyan('article-yiyan theme-box text-center radius8 main-shadow yiyan-box');
    }

    do_action('zib_article_content_after', $post);

    if (_pz('post_copyright_s')) {
        echo '<div class="em09 muted-3-color"><div><span>©</span> 版权声明</div><div class="posts-copyright">' . _pz('post_copyright') . '</div></div>';
    }

    echo '<div class="text-center theme-box muted-3-color box-body separator em09">THE END</div>';
    if ($cat || $tags) {
        echo '<div class="theme-box article-tags">' . $cat . '<br>' . $tags . '</div>';
    }
}

function zib_single_content_footer_action()
{
    $user_id         = get_the_author_meta('ID');
    $favorite_button = zib_get_post_favorite('action action-favorite');

    echo '<div class="text-center muted-3-color box-body em09">' . _pz('post_button_toptext', '喜欢就支持一下吧') . '</div>';
    echo '<div class="text-center post-actions">';
    if (_pz('post_like_s')) {
        echo zib_get_post_like('action action-like');
    }
    if (_pz('post_rewards_s')) {
        echo zib_get_rewards_button($user_id, 'action action-rewards');
    }
    if (_pz('share_s')) {
        echo zib_get_post_share_btn(null, 'action action-share');
    }

    echo $favorite_button;
    echo '</div>';
}
