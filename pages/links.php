<?php

/**
 * Template name: Zibll-网址导航
 * Description:   sidebar page
 */

/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2025-09-28 21:15:46
 */

// 获取链接列表
function zib_page_links()
{

    $post_ID       = get_queried_object_id();
    $args_category = zib_get_post_meta($post_ID, 'page_links_category', true);

    if (!$args_category) {
        return;
    }

    $args_orderby            = zib_get_post_meta($post_ID, 'page_links_orderby', true);
    $args_order              = zib_get_post_meta($post_ID, 'page_links_order', true);
    $args_limit              = zib_get_post_meta($post_ID, 'page_links_limit', true);
    $page_links_style        = zib_get_post_meta($post_ID, 'page_links_style', true);
    $args_go_s               = zib_get_post_meta($post_ID, 'page_links_go_s', true);
    $args_blank_s            = zib_get_post_meta($post_ID, 'page_links_blank_s', true);
    $args_nofollow_s         = zib_get_post_meta($post_ID, 'page_links_nofollow_s', true);
    $page_links_submit_s     = zib_get_post_meta($post_ID, 'page_links_submit_s', true);
    $page_links_submit_title = $page_links_submit_s ? zib_get_post_meta($post_ID, 'page_links_submit_title', true) : '';
    $is_mobile               = wp_is_mobile();
    $links_con               = '';
    if (is_array($args_category) && count($args_category) > 1) {
        $link_cat_con = '';
        $link_cat_nav = '';
        foreach ($args_category as $cat_id) {
            $args = array(
                'orderby'  => $args_orderby ? $args_orderby : 'name', //排序方式
                'order'    => $args_order ? $args_order : 'ASC', //升序还是降序
                'limit'    => $args_limit ? (int) $args_limit : -1, //最多显示数量
                'category' => $cat_id,
            );
            $_link = zib_links_box(get_bookmarks($args), $page_links_style, $args_nofollow_s, $args_go_s, $args_blank_s);
            if ($_link) {
                $_id      = 'links-' . $cat_id;
                $get_term = get_term($cat_id);
                if (!isset($get_term->term_id)) {
                    continue;
                }
                $_title = get_term($cat_id)->name;
                $link_cat_con .= '<div class="mb20 links-cat-box"><div class="links-cat-header"><div class="title-theme"><div id="' . $_id . '">' . $_title . '</div></div></div><div class="links-box links-style-' . $page_links_style . '">' . $_link . '</div></div>';
                $link_cat_nav .= '<li class="n-H2"><a class="text-ellipsis" href="#' . $_id . '">' . $_title . '</a></li>';
            }
        }

        $page_links_submit_btn_nav = $page_links_submit_s ? '<div class="link-nav-submit"><a class="padding-h10 hollow but c-theme btn-block text-ellipsis" href="#submit-links-modal" data-toggle="modal">' . $page_links_submit_title . '</a></div>' : '';
        $page_links_submit_btn_con = $page_links_submit_s ? '<div class="link-cat-submit"><a class="padding-h10 hollow but c-theme btn-block text-ellipsis" href="#submit-links-modal" data-toggle="modal">' . $page_links_submit_title . '</a></div>' : '';

        $links_con = '<div class="fixed-wrap"><div class="fixed-wrap-nav affix-header-sm affix link-nav-box tab-auto-center-sm" offset-top="' . ($is_mobile ? '-8' : '') . '" offset-bottom="' . ($is_mobile ? '20' : '-30') . '"><div class="zib-widget"><ul class="nav list-inline scroll-x mini-scrollbar tab-nav-theme">' . $link_cat_nav . '</ul></div>' . $page_links_submit_btn_nav . '</div><div class="fixed-wrap-content">' . $link_cat_con . $page_links_submit_btn_con . '</div></div>';
    } else {
        $args = array(
            'orderby'  => $args_orderby ? $args_orderby : 'name', //排序方式
            'order'    => $args_order ? $args_order : 'ASC', //升序还是降序
            'limit'    => $args_limit ? (int) $args_limit : -1, //最多显示数量
            'category' => $args_category, //以逗号分隔的类别ID列表
        );

        $page_links_submit_btn_con = $page_links_submit_s ? '<div class="mt20"><a class="padding-h10 hollow but c-theme btn-block text-ellipsis" href="#submit-links-modal" data-toggle="modal">' . $page_links_submit_title . '</a></div>' : '';
        $links_con                 = '<div class="links-box links-style-' . $page_links_style . '">' . zib_links_box(get_bookmarks($args), $page_links_style, $args_nofollow_s, $args_go_s, $args_blank_s) . '</div>' . $page_links_submit_btn_con;
    }

    $html = '';
    if ($links_con) {
        $html = '<div class="links-page-container mb20">' . $links_con . '</div>';
    } elseif (is_super_admin()) {
        $html = '<div class="mb20"><a class="author-minicard links-card radius8" href="' . admin_url('link-manager.php') . '" target="_blank">添加链接</a></div>';
    } else {
        $html = '<div class="author-minicard links-card radius8 mb20">暂无链接</div>';
    }
    return $html;
}

function zib_link_get_search_cover()
{
    global $post;
    $post_id = $post->ID;

    if (!zib_get_post_meta($post_id, 'page_links_search_s', true)) {
        return;
    }

    $search_types = zib_get_post_meta($post_id, 'page_links_search_types', true);
    if (!$search_types) {
        return;
    }

    $search_types_args = array(
        'self'   => array(
            'name'       => '站内',
            'input_name' => 's',
            'url'        => home_url(),
        ),
        'baidu'  => array(
            'name'       => '百度',
            'input_name' => 'wd',
            'url'        => 'https://www.baidu.com/s',
        ),
        'google' => array(
            'name'       => '谷歌',
            'input_name' => 'q',
            'url'        => 'https://www.google.com/search?',
        ),
        'bing'   => array(
            'name'       => '必应',
            'input_name' => 'q',
            'url'        => 'https://cn.bing.com/search?',
        ),
        'sogou'  => array(
            'name'       => '搜狗',
            'input_name' => 'query',
            'url'        => 'https://www.sogou.com/web?',
        ),
        '360'    => array(
            'name'       => '360',
            'input_name' => 'q',
            'url'        => 'https://www.so.com/s?',
        ),
    );

    $type_nav = '';
    $type_con = '';

    //默认action
    $i = 0;

    foreach ($search_types as $k) {
        if (isset($search_types_args[$k])) {

            $type_nav .= '<li class="' . ($i === 0 ? ' active' : '') . '"><a class="" data-toggle="tab" data-target="#link-page-' . $post_id . '-' . $k . '" href="javascript:;">' . $search_types_args[$k]['name'] . '</a></li>';
            $type_con .= '<div class="tab-pane' . ($i === 0 ? ' in active' : '') . '" id="link-page-' . $post_id . '-' . $k . '" >
            <form method="get" target="_blank" class="padding-10 search-form" action="' . $search_types_args[$k]['url'] . '">
                 <div class="line-form blur-bg">
                <div class="search-input-text">
                    <input type="text" name="' . $search_types_args[$k]['input_name'] . '" class="line-form-input" tabindex="1" value="" autocomplete="off"><i class="line-form-line"></i>
                    <div class="scale-placeholder">' . $search_types_args[$k]['name'] . '搜索</div>
                    <div class="abs-right muted-color"><button type="submit" tabindex="2" class="null"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-search"></use></svg></button>
                    </div>
                </div>
                </div>

            </form>
            </div>';
            $i++;
        }
    }

    $title             = get_the_title($post);
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

    $search = '<div class="header-slider-search abs-center"><div class="header-slider-search-more text-center before"><div class="em14 font-bold mb10">' . $title . '</div></div>
        <div class="search-input">
            <div class="flex jc"><ul class="list-inline scroll-x mini-scrollbar tab-nav-theme">' . $type_nav . '</ul></div>
            <div class="tab-content">' . $type_con . '</div>
        </div>
        </div>';

    $html = '<div class="page-cover theme-box radius8 main-shadow link-page-search-cover">
        <img ' . (zib_is_lazy('lazy_cover', true) ? 'class="fit-cover no-scale lazyload" src="' . $src . '" data-src="' . $img . '"' : 'class="fit-cover no-scale"  src="' . $img . '"') . '>
        ' . $search . '</div>';

    return $html;
}

//开始输出
$post_id                     = get_queried_object_id();
$page_id                     = $post_id;
$header_style                = zib_get_page_header_style();
$page_links_content_s        = zib_get_post_meta($post_id, 'page_links_content_s', true);
$page_links_content_position = zib_get_post_meta($post_id, 'page_links_content_position', true);
$page_links_submit_s         = zib_get_post_meta($post_id, 'page_links_submit_s', true);
$content_style               = zib_get_page_content_style($post_id);
$container_class = 'container';
$container_class .= $content_style ? ' page-content-' . $content_style : '';

//小工具容器
$widgets_register_container = array();
if (get_post_meta($post_id, 'widgets_register', true)) {
    $widgets_register_container = (array) get_post_meta($post_id, 'widgets_register_container', true);
}

get_header();
//顶部全宽度小工具
if ($widgets_register_container && in_array('top_fluid', $widgets_register_container)) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('page_top_fluid_' . $post_id);
    echo '</div>';
}
?>
<main class="container <?php echo $container_class; ?>">
    <div class="content-wrap">
        <div class="content-layout">
            <?php while (have_posts()): the_post(); ?>
                <?php
                echo zib_get_page_header();
                echo zib_link_get_search_cover();
                if ($widgets_register_container && in_array('top_content', $widgets_register_container)) {
                    dynamic_sidebar('page_top_content_' . $post_id);
                }
                if ($page_links_content_position != 'top') {
                    echo zib_page_links();
                }
                if ($page_links_content_s && $content_style !== 'not') {
                    echo '<div class="zib-widget"><article class="article wp-posts-content">';
                    the_content();
                    echo '</article>';
                    wp_link_pages(
                        array(
                            'before' => '<p class="text-center post-nav-links radius8 padding-6">',
                            'after'  => '</p>',
                        )
                    );
                    echo '</div>';
                }
                if ($page_links_content_position == 'top') {
                    echo zib_page_links();
                }
                ?>
                <?php ?>
            <?php endwhile; ?>
            <?php
            comments_template('/template/comments.php', true);
            if ($widgets_register_container && in_array('bottom_content', $widgets_register_container)) {
                dynamic_sidebar('page_bottom_content_' . $post_id);
            } ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php
if ($page_links_submit_s) {
    $submit_args = array(
        'title' => zib_get_post_meta($post_id, 'page_links_submit_title', true),
        'dec'   => zib_get_post_meta($post_id, 'page_links_submit_dec', true),
        'sign'  => zib_get_post_meta($post_id, 'page_links_submit_sign_s', true),
        'cats'  => zib_get_post_meta($post_id, 'page_links_submit_cats', true),
    );
    echo zib_submit_links_modal($submit_args);
}
if ($widgets_register_container && in_array('bottom_fluid', $widgets_register_container)) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('page_bottom_fluid_' . $post_id);
    echo '</div>';
}
get_footer();
?>