<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:37
 * @LastEditTime : 2026-04-22 13:36:13
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//载入文件
zib_require(array(
    'zib-tool',
    'zib-theme',
    'zib-head',
    'zib-header',
    'zib-content',
    'zib-footer',
    'zib-index',
    'zib-category',
    'zib-author',
    'zib-post',
    'zib-posts-list',
    'zib-share',
    'zib-search',
    'zib-attachment',
    'zib-share-wechat',
    'user/user',
    'zib-user',
    'zib-page',
    'zib-single',
    'zib-comments-list',
    'zib-svg-icon',
    'zib-baidu',
    'zib-email',
    'zib-frontend-set',
    'message/functions',
    'bbs/bbs',
    'shop/shop',
), false, 'inc/functions/');

if (is_admin()) {
    zib_require(array(
        'inc/functions/admin/admin-main',
        'inc/functions/admin/admin-set',
    ));
}

//老版slider
function zib_get_img_slider($args)
{
    $defaults = array(
        'class'        => '',
        'type'         => '',
        'lazy'         => false,
        'pagination'   => true,
        'effect'       => 'slide',
        'button'       => true,
        'loop'         => true,
        'auto_height'  => false,
        'm_height'     => '',
        'pc_height'    => '',
        'autoplay'     => true,
        'interval'     => 4000,
        'spaceBetween' => 15,
        'echo'         => true,
    );
    $args         = wp_parse_args((array) $args, $defaults);
    $class        = $args['class'];
    $type         = $args['type'];
    $lazy         = $args['lazy'];
    $pagination   = $args['pagination'];
    $effect       = ' data-effect="' . $args['effect'] . '"';
    $button       = $args['button'];
    $loop         = $args['loop'] ? ' data-loop="true"' : '';
    $auto_h       = $args['auto_height'] ? ' auto-height="true"' : '';
    $interval     = $args['interval'] < 999 ? $args['interval'] * 1000 : $args['interval'];
    $autoplay     = $args['autoplay'] ? ' data-autoplay="' . $args['autoplay'] . '"' : '';
    $interval     = $args['interval'] && $args['autoplay'] ? $autoplay . ' data-interval="' . $interval . '"' : '';
    $spaceBetween = $args['spaceBetween'] ? ' data-spaceBetween="' . $args['spaceBetween'] . '"' : '';

    $style = '';
    if (!$auto_h) {
        $_h = !empty($args['m_height']) ? '--m-height :' . (int) $args['m_height'] . 'px;' : '';
        $_h .= !empty($args['pc_height']) ? '--pc-height :' . (int) $args['pc_height'] . 'px;' : '';
        $style = ' style="' . $_h . '"';
    }

    if (!$lazy && zib_is_lazy('lazy_sider')) {
        $lazy = true;
    }
    if (empty($args['slides'])) {
        return;
    }
    $slides           = '';
    $pagination_rigth = '';
    foreach ($args['slides'] as $slide) {
        $lazy_src         = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
        $s_class          = isset($slide['class']) ? $slide['class'] : '';
        $s_href           = isset($slide['href']) ? $slide['href'] : '';
        $s_image          = isset($slide['image']) ? $slide['image'] : '';
        $s_blank          = !empty($slide['blank']) ? ($s_href ? ' target="_blank"' : '') : '';
        $s_caption        = isset($slide['caption']) ? $slide['caption'] : '';
        $s_desc           = !empty($slide['desc']) ? '<div class="s-desc">' . $slide['desc'] . '</div>' : '';
        $pagination_rigth = !empty($slide['desc']) ? ' kaoyou' : ' kaoyou';
        $slides .= '<div class="swiper-slide' . ' ' . $s_class . '">' . $s_desc .
            '<' . ($s_href ? 'a' : 'span') . $s_blank . ($s_href ? ' href="' . $s_href . '"' : '') . '>
				<img class="lazyload swiper-lazy radius8" ' . ($lazy ? ' data-src="' . $s_image . '" src="' . $lazy_src . '"' : ' src="' . $s_image . '"') . '></' . ($s_href ? 'a' : 'span') . '>'
            . ($s_caption ? '<div class="carousel-caption">' . $s_caption . '</div>' : '') . '</div>';
    }
    $pagination = $pagination ? '<div class="swiper-pagination' . $pagination_rigth . '"></div>' : '';
    $button     = $button ? '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>' : '';

    $con = '<div class="new-swiper swiper-c ' . $class . '" ' . $effect . $loop . $auto_h . $interval . $spaceBetween . $style . '>
            <div class="swiper-wrapper">' . $slides . '</div>' .
        $button . $pagination . '</div>';
    if ($args['echo']) {
        echo '<div class="relative zib-slider theme-box">' . $con . '</div>';
    } else {
        return '<div class="relative zib-slider">' . $con . '</div>';
    }
}

/**
 * @description: slider构建函数
 * @param {*}
 * @return {*}
 */
function zib_new_slider($args, $echo = true)
{
    $defaults = array(
        'class'           => 'mb20',
        'type'            => '',
        'direction'       => 'horizontal',
        'lazy'            => false,
        'pagination'      => true,
        'pagination_type' => '', //可选：‘bullets’  圆点（默认）、‘fraction’  分式 、‘progressbar’  进度条、‘custom’ 自定义
        'effect'          => 'slide',
        'button'          => true,
        'loop'            => true,
        'scale_height'    => false,
        'blur_bg'         => false,
        'gradient_bg'     => false,
        'scale'           => 40,
        'auto_height'     => false,
        'm_height'        => '',
        'pc_height'       => '',
        'autoplay'        => true,
        'interval'        => 4000,
        'speed'           => 0,
        'slides'          => array(),
        'html'            => '',
    );
    $args = wp_parse_args((array) $args, $defaults);
    if (empty($args['slides'][0])) {
        return;
    }

    $is_mobile      = wp_is_mobile();
    $is_blur_bg     = $args['blur_bg'];
    $is_gradient_bg = $args['gradient_bg'];
    $class          = $args['class'] . ($is_blur_bg ? ' blur-background' : '');
    $class          = $args['class'] . ($is_gradient_bg ? ' gradient-background' : '');
    $type           = $args['type'];
    $lazy           = $args['lazy'];
    $pagination     = $args['pagination'];
    $effect         = ' data-effect="' . $args['effect'] . '"';
    $button         = $args['button'];
    $loop           = $args['loop'] ? ' data-loop="true"' : '';
    $auto_h         = ($args['auto_height'] && 'vertical' !== $args['direction']) ? ' auto-height="true"' : '';
    $autoplay       = $args['autoplay'] ? ' data-autoplay="' . $args['autoplay'] . '"' : '';
    $interval       = $args['interval'] < 999 ? $args['interval'] * 1000 : $args['interval'];
    $autoplay .= ($interval && $args['autoplay']) ? ' data-interval="' . $interval . '"' : '';
    $speed        = $args['speed'] && $args['speed'] > 299 ? ' data-speed="' . $args['speed'] . '"' : '';
    $direction    = $args['direction'] ? ' data-direction="' . $args['direction'] . '"' : '';
    $spaceBetween = isset($args['spacebetween']) ? ' data-spaceBetween="' . $args['spacebetween'] . '"' : '';

    $style = '';
    if ($args['scale_height'] && 'vertical' !== $args['direction']) {
        $_h = '--scale-height :' . (int) $args['scale'] . '%';
        $class .= ' scale-height';
        $auto_h = '';
    } elseif (!$auto_h) {
        $_h = !empty($args['m_height']) ? '--m-height :' . (int) $args['m_height'] . 'px;' : '';
        $_h .= !empty($args['pc_height']) ? '--pc-height :' . (int) $args['pc_height'] . 'px;' : '';
    } else {
        $_h = !empty($args['max_height']) ? '--max-height :' . (int) $args['max_height'] . 'px;' : '';
        $_h .= !empty($args['min_height']) ? '--min-height :' . (int) $args['min_height'] . 'px;' : '';
    }
    $style = ' style="' . $_h . '"';

    if (!$lazy && zib_is_lazy('lazy_sider')) {
        $lazy = true;
    }

    $zib_get_delimiter_blog_name = zib_get_delimiter_blog_name();
    $slides                      = '';
    $seo_alt                     = _pz('hometitle') ? _pz('hometitle') : '幻灯片' . $zib_get_delimiter_blog_name;
    $_i                          = 0;
    foreach ($args['slides'] as $slide) {
        $lazy_src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
        $s_class  = isset($slide['class']) ? $slide['class'] : '';

        if (isset($slide['hide'])) {
            if ((!$is_mobile && $slide['hide'] === 'pc') || ($is_mobile && $slide['hide'] === 'm')) {
                continue;
            }
        }

        $img_alt = !empty($slide['text']['title']) ? $slide['text']['title'] . $zib_get_delimiter_blog_name : '';
        if (!$img_alt) {
            $img_alt = !empty($slide['link']['text']) ? $slide['link']['text'] . $zib_get_delimiter_blog_name : '';
        }

        if (!$img_alt) {
            $img_alt = $seo_alt;
        }

        //视频背景
        $s_background = '';
        $b_video      = $slide['background_video'] ?? '';
        $b_img        = $slide['background'] ?? '';
        if ($b_video && !$is_mobile) {
            $video_poster_attr = $b_img ? ' poster="' . $slide['background'] . '"' : '';
            $s_class .= ' slide-background-video';
            $s_background = '<video autoplay="" loop="" muted="" class="fit-cover radius8' . ($lazy ? ' lazyload ' : '') . '" ' . ($lazy ? ' data-src="' . $b_video . '" src=""' : ' src="' . $b_video . '"') . $video_poster_attr . '></video><div class="absolute"></div>';
        }

        //视频内容 Dplayer
        $video = $slide['video'] ?? '';
        if (!$s_background && $video) {
            $s_class .= ' slide-dplayer';
            $s_background = zib_get_dplayer($video);
            if ($b_img) {
                $video_poster_attr = '';
                if ($is_gradient_bg) {
                    $video_poster_attr  = ' data-opacity="0.2"';
                    $video_poster_class = ' gradient-bg';
                }
                $s_background .= '<div class="absolute dplayer-poster' . $video_poster_class . '"' . $video_poster_attr . '><img class="no-imgbox radius8' . ($lazy ? ' lazyload swiper-lazy' : '') . '" ' . ($lazy ? ' data-src="' . $b_img . '" src="' . $lazy_src . '"' : ' src="' . $b_img . '"') . ' alt="' . $img_alt . '"><div class="absolute flex jc play-icon"><span class="toggle-radius"><i class="fa fa-play" aria-hidden="true"></i></span></div></div>';
            }
        }

        //图片背景
        if (!$s_background && $b_img) {
            $s_background = '<img class="radius8' . ($lazy ? ' lazyload swiper-lazy' : '') . '" ' . ($lazy ? ' data-src="' . $b_img . '" src="' . $lazy_src . '"' : ' src="' . $b_img . '"') . ' alt="' . $img_alt . '">';
        }

        if (!$s_background) {
            continue;
        }

        $blur_background = '';
        if ($is_blur_bg && $b_img) {
            $blur_background = '<div class="slide-blur-bg"><img class="no-imgbox ' . ($lazy ? ' lazyload swiper-lazy' : '') . '" ' . ($lazy ? ' data-src="' . $b_img . '" src="' . $lazy_src . '"' : ' src="' . $b_img . '"') . ' alt="' . $img_alt . '"></div>';
        }

        //更多图层
        $s_layers = '';

        if (!empty($slide['image_layer'][0]['image'])) {
            foreach ($slide['image_layer'] as $layer) {
                $layer_image = isset($layer['image']) ? $layer['image'] : '';
                if (!$layer_image) {
                    continue;
                }

                $layer_image = $layer_image ? '<img class="radius8' . ($lazy ? ' lazyload swiper-lazy' : '') . '" ' . ($lazy ? ' data-src="' . $layer_image . '" src="' . $lazy_src . '"' : ' src="' . $layer_image . '"') . ' alt="' . $img_alt . '">' : '';

                //视差滚动
                $layer_parallax = isset($layer['parallax']) ? (int) $layer['parallax'] : 0;
                $layer_parallax = $layer_parallax ? ' data-swiper-parallax="' . $layer_parallax . '%"' : '';

                //视差透明度
                $layer_parallax_opacity = isset($layer['parallax_opacity']) ? (int) $layer['parallax_opacity'] / 100 : 0;
                $layer_parallax .= ($layer_parallax && $layer_parallax_opacity && 1 !== $layer_parallax_opacity) ? ' data-swiper-parallax-opacity="' . $layer_parallax_opacity . '"' : '';

                //视差缩放
                $layer_parallax_scale = isset($layer['parallax_scale']) ? (int) $layer['parallax_scale'] / 100 : 0;
                $layer_parallax_scale = ($layer_parallax_scale && 1 !== $layer_parallax_scale) ? ' data-swiper-parallax-scale="' . $layer_parallax_scale . '"' : '';

                //前景图对齐
                $layer_class = '';
                if (!empty($layer['free_size'])) {
                    $layer_class = ' slide-layer';
                    $layer_class .= isset($layer['align']) ? ' text-' . $layer['align'] : '';
                }
                //图层动画
                $animate_attr = '';
                /**
                $animate = array(
                array(
                'value' => 'rubberBand',
                'duration' => '',
                'loop' => '',
                'delay' => '',
                ),
                );
                if (!empty($animate[0]['value'])) {
                $animate_attr = ' swiper-animate-effect="' . esc_attr(json_encode($animate)) . '"';
                $layer_class .= ' ani';
                }
                 */
                $s_layers .= '<div' . $animate_attr . ' class="absolute' . $layer_class . '"' . $layer_parallax . $layer_parallax_scale . '>' . $layer_image . '</div>';
            }
        }
        //链接
        $s_href  = isset($slide['link']['url']) ? $slide['link']['url'] : '';
        $s_blank = !empty($slide['link']['target']) ? ($s_href ? ' target="_blank"' : '') : '';
        //文案
        $slide_text = !empty($slide['text']['title']) ? $slide['text'] : '';
        $s_text     = !empty($slide_text['title']) ? '<div class="slide-title">' . $slide_text['title'] . '</div>' : '';
        $s_text .= !empty($slide_text['desc']) ? '<div class="slide-desc">' . $slide_text['desc'] . '</div>' : '';

        if ($s_text) {
            //控制位置class
            $s_text_class = 'abs-center slide-text';
            $s_text_class .= isset($slide_text['text_align']) ? ' ' . $slide_text['text_align'] : '';
            //字体大小
            $s_text_size = !empty($slide_text['text_size_pc']) ? '--text-size-pc:' . (int) $slide_text['text_size_pc'] . 'px;' : '';
            $s_text_size .= !empty($slide_text['text_size_m']) ? '--text-size-m:' . (int) $slide_text['text_size_m'] . 'px;' : '';

            $s_text_style = $s_text_size ? ' style="' . $s_text_size . '"' : '';
            $s_text       = '<div class="' . $s_text_class . '"' . $s_text_style . '>' . $s_text . '</div>';
            //视差滚动
            $s_text_parallax = isset($slide_text['parallax']) ? $slide_text['parallax'] : 0;
            if ($s_text_parallax) {
                $s_text_parallax = $s_text_parallax ? ' data-swiper-parallax="' . $s_text_parallax . '"' : '';
                $s_text          = '<div class="absolute"' . $s_text_parallax . '>' . $s_text . '</div>';
            }
        }

        $s_attr = '';
        if ($is_gradient_bg) {
            $s_class .= ' gradient-bg';
            $s_attr = ' data-opacity="0.2"';
        }

        $s_attr .= isset($slide['attr']) ? ' ' . $slide['attr'] : '';

        $slides .= '<div class="swiper-slide' . ' ' . $s_class . '"' . $s_attr . '>';
        $slides .= $blur_background;
        $slides .= $s_href ? '<a' . $s_blank . ' href="' . $s_href . '">' : '<span>';
        $slides .= $s_background;
        $slides .= $s_layers;
        $slides .= $s_text;
        $slides .= $s_href ? '</a>' : '</span>';
        $slides .= '</div>';

        $_i++;
    }

    if (!$slides) {
        return;
    }

    $slides = '<div class="swiper-wrapper">' . $slides . '</div>';

    $pagination      = $pagination && $_i > 1 ? '<div class="swiper-pagination kaoyou ' . ($args['pagination_type'] ? 'scroll-x no-scrollbar pagination-type-' . $args['pagination_type'] : '') . '"></div>' : '';
    $button          = $button && $_i > 1 ? '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>' : '';
    $pagination_type = $args['pagination_type'] && $pagination ? ' data-pagination-type="' . $args['pagination_type'] . '"' : '';

    if ($_i === 1) {
        $class .= ' swiper-container-initialized';
    }
    $con = '<div class="new-swiper ' . $class . '" ' . $pagination_type . $direction . $effect . $loop . $speed . $auto_h . $autoplay . $spaceBetween . $style . '>';
    $con .= $slides;
    $con .= $button;
    $con .= $pagination;
    $con .= $args['html'];
    $con .= '</div>';

    if ($echo) {
        echo '<div class="relative zib-slider">' . $con . '</div>';
    } else {
        return '<div class="relative zib-slider">' . $con . '</div>';
    }
}

/**
 * @description: dplayer简单的视频构建
 * @param {*} $url 视频地址
 * @param {*} $pic 视频封面
 * @param {*} $scale_height 视频高度比例
 * @return {*}
 */
function zib_get_dplayer($url, $pic = '', $scale_height = 0)
{
    $args = array(
        'url'          => $url,
        'pic'          => $pic,
        'scale_height' => $scale_height,
    );
    return zib_new_dplayer($args, false);
}

//dplayer视频构建
function zib_new_dplayer($args, $echo = true)
{

    $defaults = array(
        'class'           => '',
        'autoplay'        => false,
        'loop'            => false,
        'hide_controller' => false,
        'preload'         => 'auto', //values: 'none', 'metadata', 'auto'
        'volume'          => 1,
        'scale_height'    => 0,
        'mutex'           => true, //开始播放时暂停其他播放器
        'type'            => 'auto',
        'url'             => '',
        'pic'             => '',
    );
    $args = wp_parse_args((array) $args, $defaults);
    if (empty($args['url'])) {
        return;
    }

    $option = array();
    if ($args['autoplay']) {
        $option['autoplay'] = true;
    }

    if ($args['loop']) {
        $option['loop'] = true;
    }

    if ('auto' != $args['preload']) {
        $option['preload'] = $args['preload'];
    }

    if (1 != $args['volume']) {
        $option['volume'] = $args['volume'];
    }

    if (!$args['mutex']) {
        $option['mutex'] = false;
    }

    if ($args['hide_controller']) {
        $args['class'] .= ' controller-hide';
    }

    $option_attr = $option ? ' video-option=\'' . json_encode($option) . '\'' : '';

    $attr = 'video-url="' . $args['url'] . '"';
    $attr .= $args['pic'] ? ' video-pic="' . $args['pic'] . '"' : '';
    $attr .= $args['type'] ? ' video-type="' . $args['type'] . '"' : '';
    $attr .= $option_attr;

    if ($args['scale_height'] > 0) {
        $args['class'] .= ' dplayer-scale-height';
        $attr .= ' style="--scale-height:' . $args['scale_height'] . '%;"';
    }

    $pic_img = '';
    if ($args['pic']) {
        $_lazy_attr = zib_get_lazy_attr('lazy_other', $args['pic'], 'absolute fit-contain dplayer-initial-img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg'); //初始化
        $pic_img    = '<img ' . $_lazy_attr . ' alt="pic">';
    }

    $img  = '<div class="graphic" style="padding-bottom:50%;">' . $pic_img . '<div class="abs-center text-center"><i class="fa fa-play-circle fa-4x muted-3-color opacity5" aria-hidden="true"></i></div></div>';
    $html = '<div class="new-dplayer ' . $args['class'] . '" ' . $attr . '>' . $img . '</div>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * @description: 获取用户点赞、查看数量
 * @param {*}
 * @return {*}
 */
function zib_get_user_badges($user_id)
{
    if (!$user_id) {
        return;
    }

    $like_n     = get_user_posts_meta_count($user_id, 'like');
    $view_n     = get_user_posts_meta_count($user_id, 'views');
    $com_n      = get_user_comment_count($user_id);
    $followed_n = _cut_count(get_user_followed_count($user_id));
    $post_n     = _cut_count(zib_get_user_post_count($user_id, 'publish'));

    $html = '';
    $html .= '<a class="but c-blue tag-posts" data-toggle="tooltip" title="共' . $post_n . '篇文章" href="' . zib_get_user_home_url($user_id) . '">' . zib_get_svg('post') . $post_n . '</a>';
    $html .= '<a class="but c-green tag-comment" data-toggle="tooltip" title="共' . $com_n . '条评论" href="' . zib_get_user_home_url($user_id, array('tab' => 'comment')) . '">' . zib_get_svg('comment') . $com_n . '</a>';

    if ($followed_n) {
        $html .= '<a class="but c-yellow tag-follow" data-toggle="tooltip" title="共' . $followed_n . '个粉丝" href="' . zib_get_user_home_url($user_id, array('tab' => 'follow')) . '"><i class="fa fa-heart em09"></i>' . $followed_n . '</a>';
    } else {
        if ($like_n) {
            $html .= '<span class="badg c-yellow tag-like" data-toggle="tooltip" title="获得' . $like_n . '个点赞">' . zib_get_svg('like') . $like_n . '</span>';
        }
    }
    $html .= '<span class="badg c-red tag-view" data-toggle="tooltip" title="人气值 ' . $view_n . '">' . zib_get_svg('hot') . $view_n . '</span>';

    return apply_filters('user_count_badges', $html, $user_id);
}

function zib_yiyan($class = 'zib-yiyan', $before = '', $after = '')
{
    $yiyan = '<div class="' . $class . '">' . $before . '<div data-toggle="tooltip" data-original-title="点击切换一言" class="yiyan"></div>' . $after . '</div>';
    echo $yiyan;
}

function zib_posts_prevnext()
{
    $current_category = get_the_category();
    $prev_post        = get_previous_post($current_category, '');
    $next_post        = get_next_post($current_category, '');
    if (!empty($prev_post)):
        $prev_title = $prev_post->post_title;
        $prev_link  = 'href="' . get_permalink($prev_post->ID) . '"';
    else:
        $prev_title = '无更多文章';
        $prev_link  = 'href="javascript:;"';
    endif;
    if (!empty($next_post)):
        $next_title = $next_post->post_title;
        $next_link  = 'href="' . get_permalink($next_post->ID) . '"';
    else:
        $next_title = '无更多文章';
        $next_link  = 'href="javascript:;"';
    endif;
    ?>
    <div class="theme-box" style="height:99px">
        <nav class="article-nav">
            <div class="main-bg box-body radius8 main-shadow">
                <a <?php echo $prev_link; ?>>
                    <p class="muted-2-color"><i class="fa fa-angle-left em12"></i><i
                            class="fa fa-angle-left em12 mr6"></i>上一篇</p>
                    <div class="text-ellipsis-2">
                        <?php echo $prev_title; ?>
                    </div>
                </a>
            </div>
            <div class="main-bg box-body radius8 main-shadow">
                <a <?php echo $next_link; ?>>
                    <p class="muted-2-color">下一篇<i class="fa fa-angle-right em12 ml6"></i><i
                            class="fa fa-angle-right em12"></i></p>
                    <div class="text-ellipsis-2">
                        <?php echo $next_title; ?>
                    </div>
                </a>
            </div>
        </nav>
    </div>
<?php
}

function zib_posts_related($related_title = '相关阅读', $limit = 6, $orderby = 'views')
{
    global $post;
    $thumb_s   = _pz('post_related_type') == 'img';
    $categorys = get_the_terms($post, 'category');
    $topics    = get_the_terms($post, 'topics');
    $tags      = get_the_terms($post, 'post_tag');

    $posts_args = array(
        'showposts'           => $limit,
        'ignore_sticky_posts' => 1,
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'order'               => 'DESC',
        'no_found_rows'       => true, //不查询分页需要的总数量
        'tax_query'           => array(
            'relation' => 'OR',
            array(
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => array_column((array) $categorys, 'term_id'),
            ),
            array(
                'taxonomy' => 'topics',
                'field'    => 'term_id',
                'terms'    => array_column((array) $topics, 'term_id'),
            ),
            array(
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => array_column((array) $tags, 'term_id'),
            ),
        ),
    );

    $posts_args = zib_query_orderby_filter($orderby, $posts_args);

    $posts_lits = '';
    $new_query  = new WP_Query($posts_args);
    while ($new_query->have_posts()) {
        $new_query->the_post();
        if (_pz('post_related_type') == 'list') {
            $posts_lits .= zib_posts_mini_while(array('echo' => false, 'show_number' => false));
        } else {
            if ($thumb_s) {
                $title    = get_the_title() . get_the_subtitle(false);
                $time_ago = zib_get_time_ago(get_the_time('Y-m-d H:i:s'));
                $info     = '<item>' . $time_ago . '</item><item class="pull-right">' . zib_get_svg('view') . ' ' . get_post_view_count($before = '', $after = '') . '</item>';
                $img      = zib_post_thumbnail('', 'fit-cover', true);
                $img      = $img ? $img : zib_get_spare_thumb();
                $card     = array(
                    'type'         => 'style-3',
                    'class'        => 'mb10',
                    'img'          => $img,
                    'alt'          => $title,
                    'link'         => array(
                        'url'    => get_permalink(),
                        'target' => '',
                    ),
                    'text1'        => $title,
                    'text2'        => zib_str_cut($title, 0, 45, '...'),
                    'text3'        => $info,
                    'lazy'         => true,
                    'height_scale' => 70,
                );
                $posts_lits .= '<div class="swiper-slide mr10">';
                $posts_lits .= zib_graphic_card($card);
                $posts_lits .= '</div>';
            } else {
                $posts_lits .= '<li><a class="icon-circle" href="' . get_permalink() . '">' . get_the_title() . get_the_subtitle() . '</a></li>';
            }
        }
    }
    wp_reset_query();
    wp_reset_postdata();

    echo '<div class="theme-box relates' . ($thumb_s ? ' relates-thumb' : '') . '">
            <div class="box-body notop">
                <div class="title-theme">' . $related_title . '</div>
            </div>';

    echo '<div class="zib-widget">';
    echo $thumb_s ? '<div class="swiper-container swiper-scroll"><div class="swiper-wrapper">' : '<ul class="no-thumb">';
    if (!$posts_lits) {
        echo '<li>暂无相关文章</li>';
    } else {
        echo $posts_lits;
    }
    echo $thumb_s ? '</div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>' : '</ul>';
    echo '</div></div>';
}

// 获取文章标签
function zib_get_posts_tags($class = 'but', $before = '', $after = '', $count = 0)
{
    global $post;
    $tags = get_the_tags($post->ID);
    return zib_get_tags($tags, $class, $before, $after, $count);
}

//数组按一个值从新排序
function arraySort($arrays, $sort_key, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC)
{
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            $key_arrays[] = $array->$sort_key;
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}

// 获取标签
function zib_get_tags($tags, $class = 'but', $before = '', $after = '', $count = 0, $ajax_replace = false)
{
    $html = '';
    if (!empty($tags[0])) {
        $ii     = 0;
        $tags_s = arraySort($tags, 'count');
        if (!empty($tags_s[0])) {
            foreach ($tags_s as $tag_id) {
                $ii++;
                $url = get_tag_link($tag_id);
                $tag = get_tag($tag_id);
                $html .= '<a href="' . $url . '"' . ($ajax_replace ? ' ajax-replace="true"' : '') . ' title="查看此标签更多文章" class="' . $class . '">' . $before . $tag->name . $after . '</a>';
                if ($count && $count == $ii) {
                    break;
                }
            }
        }
    }
    return $html;
}

// 获取专题标签
function zib_get_topics_tags($pid = '', $class = 'but', $before = '', $after = '', $count = 0)
{
    if (!$pid) {
        global $post;
        $pid = $post->ID;
    }
    $category = get_the_terms($pid, 'topics');
    $cat      = '';
    if (!empty($category[0])) {
        $ii = 0;
        foreach ($category as $category1) {
            $ii++;
            $cls = array('c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red');
            $cat .= '<a class="' . $class . ' ' . $cls[$ii - 1] . '" title="查看此专题更多文章" href="' . get_category_link($category1->term_id) . '">' . $before . $category1->name . $after . '</a>';
            if ($count && $ii == $count) {
                break;
            }
        }
    }
    return $cat;
}

// 获取分类标签
function zib_get_cat_tags($class = 'but', $before = '', $after = '', $count = 0)
{
    $category = get_the_category();
    $cat      = '';
    if (!empty($category[0])) {
        $ii = 0;
        foreach ($category as $category1) {
            $ii++;
            $cls = array('c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', 'c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red');
            $cat .= '<a class="' . $class . ' ' . $cls[$ii - 1] . '" title="查看更多分类文章" href="' . get_category_link($category1->term_id) . '">' . $before . $category1->cat_name . $after . '</a>';
            if ($count && $ii == $count) {
                break;
            }
        }
    }
    return $cat;
}

// 获取文章meta标签
function zib_get_posts_meta($post = null)
{

    if (!is_object($post)) {
        $post = get_post($post);
    }
    $post_id = $post->ID;

    $meta         = '';
    $comment_href = '';
    $is_single    = is_single($post);
    if (comments_open($post) && !_pz('close_comments')) {
        if ($is_single) {
            $comment_href = 'javascript:(scrollTopTo(\'#comments\'));';
        } else {
            $comment_href = get_comments_link($post_id);
        }
        $meta .= '<item class="meta-comm"><a rel="nofollow" data-toggle="tooltip" title="去评论" href="' . $comment_href . '">' . zib_get_svg('comment') . get_comments_number($post_id) . '</a></item>';
    }
    $meta .= '<item class="meta-view">' . zib_get_svg('view') . get_post_view_count('', '', $post_id) . '</item>';
    if (_pz('post_like_s', true) && $post->post_type == 'post') {
        $meta .= '<item class="meta-like">' . zib_get_svg('like') . (zib_get_post_like('', $post_id, '', true) ?: '0') . '</item>';
    }

    return $meta;
}

// 链接列表盒子
function zib_links_box($links = array(), $type = 'card', $nofollow = true, $go_link = false, $blank_s = false)
{
    if (!$links) {
        return '';
    }

    $html = '';
    $i    = 0;
    foreach ($links as $link) {
        $link = (array) $link;

        if (empty($link['href']) && !empty($link['link_url'])) {
            $link['href'] = $link['link_url'];
        }
        if (empty($link['title']) && !empty($link['link_name'])) {
            $link['title'] = $link['link_name'];
        }
        if (empty($link['src']) && !empty($link['link_image'])) {
            $link['src'] = $link['link_image'];
        }
        if (empty($link['desc']) && !empty($link['link_description'])) {
            $link['desc'] = $link['link_description'];
        }
        if ($blank_s) {
            $link['blank'] = true;
        }
        if (empty($link['blank']) && !empty($link['link_target'])) {
            $link['blank'] = $link['link_target'];
        }

        if (!empty($link['href']) && !empty($link['title'])) {
            $href = empty($link['href']) ? '' : esc_url($link['href']);

            if (!empty($link['go_link']) || $go_link) {
                $href = go_link($href, true);
            }

            $title = empty($link['title']) ? '' : esc_attr($link['title']);
            $src   = empty($link['src']) ? '' : esc_url($link['src']);

            $blank    = empty($link['blank']) ? '' : ' target="_blank"';
            $dec      = empty($link['desc']) ? '' : esc_attr($link['desc']);
            $img      = '<img class="lazyload avatar" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-sm.svg" data-src="' . $src . '">';
            $data_dec = $dec ? ' title="' . $title . '" data-content="' . $dec . '" ' : ' data-content="' . $title . '"';

            if ('bigcard' == $type) {
                $html .= '<div class="author-minicard links-card radius8 big-card">
                <a' . $blank . ' href="' . $href . '" title="' . $dec . '"' . ($nofollow ? ' rel="nofollow"' : '') . '>
                    <div class="avatar-img link-img">' . $img . '</div>
                    <div class="text-ellipsis mt20 font-bold">' . $title . '</div>
                    ' . ($dec ? '<dd class="mt6 avatar-dest em09 muted-3-color text-ellipsis">' . $dec . '</dd>' : '') . '
                </a>
                </div>';
            } elseif ('image' == $type) {
                $html .= '<a ' . $blank . ' class="avatar-img link-img link-only-img" data-trigger="hover" data-toggle="popover" data-placement="top"' . $data_dec . ' href="' . $href . '"' . ($nofollow ? ' rel="nofollow"' : '') . '>' . $img . '</a>';
            } elseif ('simple' == $type) {
                $sc = 0 == $i ? '' : 'icon-spot';
                $html .= '<a ' . $blank . ' class="' . $sc . '" data-trigger="hover" data-toggle="popover" data-placement="top"' . $data_dec . ' href="' . $href . '"' . ($nofollow ? ' rel="nofollow"' : '') . '>' . $title . '</a>';
            } else {
                $html .= '<div class="author-minicard links-card radius8"><a' . $blank . ' href="' . $href . '" title="' . $dec . '"' . ($nofollow ? ' rel="nofollow"' : '') . '>
                <ul class="list-inline">
                    <li><div class="avatar-img link-img">' . $img . '</div>
                    </li>
                    <li>
                        <dl>
                            <dt class="text-ellipsis">' . $title . '</dt>
                             ' . ($dec ? '<dd class="mt3 avatar-dest em09 muted-3-color text-ellipsis">' . $dec . '</dd>' : '') . '
                        </dl>
                    </li>
                </ul>
            </a></div>';
            }
            $i++;
        }
    }
    return $html;
}

/**
 * @description: 获取帖子或者版块状态post_status的徽章
 * @param {*} $class
 * @param {*} $posts_id
 * @return {*}
 */
function zib_get_post_status_badge($class = '', $post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    if (!isset($post->ID)) {
        return;
    }

    $post_status     = $post->post_status;
    $status_img_name = array(
        'pending' => 'pending-badge',
        'draft'   => 'draft-badge',
        'trash'   => 'trash-badge',
    );
    if (!isset($status_img_name[$post_status])) {
        return;
    }

    $class     = $class ? ' ' . $class : '';
    $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="lazyload fit-cover" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg" data-' : '';
    $html      = '<span class="img-badge top badge-status' . $class . '"><img ' . $lazy_attr . 'src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/' . $status_img_name[$post_status] . '.svg" alt="状态：' . $post_status . '"></span>';
    return $html;
}

// 公告栏
function zib_notice($args = array(), $echo = true)
{
    $defaults = array(
        'class'    => 'c-blue',
        'interval' => 5000,
        'notice'   => array(),
    );

    $args = wp_parse_args((array) $args, $defaults);

    $interval = ' data-interval="' . $args['interval'] . '"';
    $i        = 0;
    $slides   = '';
    foreach ($args['notice'] as $notice) {
        if (!empty($notice['title'])) {
            $href    = empty($notice['href']) ? '' : $notice['href'];
            $title   = empty($notice['title']) ? '' : $notice['title'];
            $icon    = empty($notice['icon']) ? '' : '<div class="relative bulletin-icon mr6"><i class="abs-center fa ' . $notice['icon'] . '"></i></div>';
            $blank   = empty($notice['blank']) ? '' : ' target="_blank"';
            $s_class = ' notice-slide';
            $slides .= '<div class="swiper-slide' . ' ' . $s_class . '">
            <a class="text-ellipsis"' . $blank . ($href ? ' href="' . $href . '"' : '') . '>'
                . $icon . $title . '</a>
            </div>';
            $i++;
        }
    }

    $html = '<div class="new-swiper" ' . $interval . ' data-direction="vertical" data-loop="true" data-autoplay="1">
            <div class="swiper-wrapper">' . $slides . '</div>
            </div>';

    if ($echo) {
        echo '<div class="swiper-bulletin ' . $args['class'] . '">' . $html . '</div>';
    } else {
        return $html;
    }
}

// 弹出通知
function zib_system_notice()
{
    if (isset($_COOKIE['showed_system_notice']) || !_pz('system_notice_s', true)) {
        return;
    }

    //显示策略
    $policy = _pz('system_notice_policy');
    if ($policy) {
        switch ($policy) {
            case 'signin':
                if (get_current_user_id()) {
                    return;
                }
                break;

            case 'vip':
            case 'vip_2':
                $user_id = get_current_user_id();
                if ($user_id) {
                    $vip = zib_get_user_vip_level($user_id);
                    if (($vip && $policy === 'vip') || ($vip == 2 && $policy === 'vip_2')) {
                        return;
                    }
                }

                break;

            case 'auth':
                $user_id = get_current_user_id();
                if ($user_id) {
                    $auth = zib_is_user_auth($user_id);
                    if ($auth) {
                        return;
                    }
                }
                break;
        }
    }

    $args = array(
        'id'            => 'modal-system-notice',
        'class'         => _pz('system_notice_size', 'modal-sm'),
        'style'         => '',
        'title'         => _pz('system_notice_title'),
        'content'       => _pz('system_notice_content'),
        'buttons'       => _pz('system_notice_button'),
        'buttons_class' => 'but' . (_pz('system_notice_radius') ? ' radius' : ''),
    );

    if (_pz('system_notice_title_style', 'default') == 'colorful') {
        $args['colorful_header'] = true;
        $args['header_icon']     = zib_get_cfs_icon(_pz('system_notice_title_icon', 'fa fa-heart'));
        $args['header_class']    = _pz('system_notice_title_class', 'jb-blue');
    }

    zib_modal($args);
    $expires = round(_pz('system_notice_expires', 24) / 24, 3);
    $script  = '<script type="text/javascript">';
    $script .= 'window.onload = function(){
        setTimeout(function () {$(\'#modal-system-notice\').modal(\'show\');
        ' . ($expires > 0 ? '$.cookie("showed_system_notice","showed", {path: "/",expires: ' . $expires . '});' : '') . '
    }, 500)};';
    $script .= '</script>';
    echo $script;
}

add_action('wp_footer', 'zib_system_notice', 10);

//模态框构建
function zib_modal($args = array())
{
    $defaults = array(
        'id'              => '',
        'class'           => '',
        'style'           => '',
        'colorful_header' => false,
        'header_class'    => 'jb-blue',
        'header_icon'     => '<i class="fa fa-heart"></i>',
        'title'           => '',
        'content'         => '',
        'buttons_align'   => 'right', //left/centent/right/average
        'buttons'         => array(),
        'buttons_class'   => 'but',
    );

    $args = wp_parse_args((array) $args, $defaults);
    if (!$args['title'] && !$args['content']) {
        return;
    }

    $close_btn = '<button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>';
    $title     = '<h4>' . $args['title'] . '</h4>';
    $content   = '<div>' . $args['content'] . '</div>';

    if ($args['colorful_header']) {
        $content   = '<div style="padding: 1px;">' . zib_get_modal_colorful_header($args['header_class'], $args['header_icon'], $args['title']) . $content . '</div>';
        $close_btn = '';
        $title     = '';
    }
    $args['buttons'] = (array) $args['buttons'];
    $buttons         = array();
    if (!empty($args['buttons'][0])) {
        foreach ($args['buttons'] as $but_args) {
            if (!empty($but_args['link']['text'])) {
                $href          = !empty($but_args['link']['url']) ? esc_url($but_args['link']['url']) : 'javascript:;';
                $buttons_class = !empty($but_args['class']) ? ' ' . $but_args['class'] : '';
                $target        = !empty($but_args['link']['target']) ? ' target=' . $but_args['link']['target'] : '';
                $attr          = !empty($but_args['attr']) ? ' ' . $but_args['attr'] : '';
                $buttons[]     = '<a type="button"' . $target . $attr . ' class="' . $args['buttons_class'] . $buttons_class . '" href="' . $href . '">' . $but_args['link']['text'] . '</a>';
            }
        }
    }
    $button_box_class = 'modal-buts box-body notop text-' . $args['buttons_align'];
    if ('average' == $args['buttons_align']) {
        $button_box_class = 'modal-buts but-average';
    }
    //按钮平均分布

    ?>
    <div class="modal fade" id="<?php echo $args['id']; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog                                                                                                                                                                         <?php echo $args['class']; ?>"
            <?php echo 'style="' . $args['style'] . '"'; ?> role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <?php echo $close_btn . $title . $content; ?>
                </div>
                <?php if ($buttons) {
        echo '<div class="' . $button_box_class . '">' . implode($buttons) . '</div>';
    }?>
            </div>
        </div>
    </div>
<?php
}

function zib_get_admin_ajax_url($action = false, $query_arg = array())
{
    $url = admin_url('admin-ajax.php');
    if ($action) {
        $query_arg['action'] = $action;
    }
    if ($query_arg) {
        return add_query_arg($query_arg, $url);
    }
    return $url;
}

/**
 * @description: 获取通过ajax获取内容的模态框链接
 * @param {*} $args
 *  $defaults = array(
'new'           => false,
'tag'           => 'botton',
'class'         => '',
'data_class'    => '',
'remote'        => '',
'text'          => '按钮',
'attr'          => '',
'mobile_bottom' => false,
'query_arg'     => false,
'height'        => false,
);
 * @return {*}
 */
function zib_get_refresh_modal_link($args = array())
{
    $defaults = array(
        'new'           => false,
        'tag'           => 'botton',
        'class'         => '',
        'data_class'    => '',
        'remote'        => '',
        'text'          => '按钮',
        'attr'          => '',
        'mobile_bottom' => false,
        'query_arg'     => false,
        'height'        => false,
    );
    $args = wp_parse_args($args, $defaults);
    if (!$args['remote'] && !$args['query_arg']) {
        return;
    }

    if (!$args['remote'] && $args['query_arg']) {
        $args['remote'] = zib_get_admin_ajax_url(null, $args['query_arg']);
    }
    $data_attr = $args['attr'] ? ' ' . $args['attr'] : '';
    $data_attr .= $args['new'] ? ' new="new"' : '';
    $data_attr .= $args['data_class'] ? ' data-class="' . $args['data_class'] . '"' : '';
    $data_attr .= $args['mobile_bottom'] ? ' mobile-bottom="true"' : '';
    $data_attr .= (int) $args['height'] ? ' data-height="' . (int) $args['height'] . '"' : '';

    $link = '<' . $args['tag'] . $data_attr . ' data-remote="' . esc_url($args['remote']) . '" class="' . esc_attr($args['class']) . '" href="javascript:;" data-toggle="RefreshModal">' . $args['text'] . '</' . $args['tag'] . '>';
    return $link;
}

//获取空白的模态框链接
function zib_get_blank_modal_link($args = array())
{
    $defaults = array(
        'id'         => 'blank_modal_' . mt_rand(100, 999),
        'link_class' => '',
        'remote'     => '',
        'text'       => '',
    );
    $args = wp_parse_args((array) $args, $defaults);

    $link = '<a class="' . esc_attr($args['link_class']) . '" href="javascript:;" data-toggle="modal" data-target="#' . esc_attr($args['id']) . '" data-remote="' . esc_url($args['remote']) . '">' . $args['text'] . '</a>';
    return $link . zib_get_blank_modal($args);
}

/**
 * @description: 空白模态框构建，适用于带AJAX的模态框
 * @param {*}
 * @return {*}
 */
function zib_get_blank_modal($args = array())
{
    $defaults = array(
        'id'              => '',
        'class'           => '',
        'flex'            => 'jc',
        'mobile_bottom'   => false,
        'style'           => '',
        'colorful_header' => false,
        'content'         => '<div class="modal-body"><div class="box-body"><p class="placeholder t1"></p> <h4 style="height:120px;" class="placeholder k1"></h4><p class="placeholder k2"></p><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></div></div>',
    );
    $args = wp_parse_args((array) $args, $defaults);

    if ($args['colorful_header']) {
        $args['content'] = '<div style="padding: 1px;">' . zib_get_modal_colorful_header('jb-blue', '<i class="loading"></i>') . $args['content'] . '</div>';
    }

    $modal_class = 'modal fade';

    $html = '';
    $html .= '<div class="' . $modal_class . '" id="' . $args['id'] . '" tabindex="-1" role="dialog">';
    $html .= '<div class="modal-dialog ' . $args['class'] . '" style="' . $args['style'] . '" role="document">';
    $html .= '<div class="modal-content">';
    $html .= $args['content'];
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * @description: 获取模态框的炫彩头部
 * @param {*} $class
 * @param {*} $icon
 * @param {*} $cetent
 * @param {*} $close_btn
 * @return {*}
 */
function zib_get_modal_colorful_header($class = 'jb-blue', $icon = '', $cetent = '', $close_btn = true)
{
    $html = '<div class="modal-colorful-header colorful-bg ' . $class . '">';
    $html .= $close_btn ? '<button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>' : '';
    $html .= '<div class="colorful-make"></div>';
    $html .= '<div class="text-center">';
    $html .= $icon ? '<div class="em2x">' . $icon . '</div>' : '';
    $html .= $cetent ? '<div class="mt10 em12 padding-w10">' . $cetent . '</div>' : '';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * @description: 万能-构建AJAX的tab内容
 * @param {*}
 * @return {*}
 */
function zib_get_ajax_tab($type = 'nav', $tabs = array(), $args = array())
{
    $example   = array();
    $example[] = array(
        'name'     => '例子',
        'id'       => 'posts-example',
        'ajax_url' => '',
        'content'  => '',
        'action'   => 'posts_example',
        'class'    => 'example',
        'loader'   => '',
        'active'   => false,
    );

    $defaults = array(
        'ajax_url'  => admin_url('admin-ajax.php'),
        'nav_class' => '',
        'loader'    => '',
        'no_scroll' => true,
        'ias'       => false,
    );

    $args = wp_parse_args($args, $defaults);

    $html = '';
    foreach ($tabs as $tab) {
        $action    = !empty($tab['action']) ? $tab['action'] : '';
        $id        = !empty($tab['id']) ? $tab['id'] : 'tab_' . $action;
        $name      = !empty($tab['name']) ? $tab['name'] : '';
        $class     = !empty($tab['class']) ? ' ' . $tab['class'] : '';
        $ajax_url  = !empty($tab['ajax_url']) ? $tab['ajax_url'] : $args['ajax_url'];
        $loader    = !empty($tab['loader']) ? $tab['loader'] : $args['loader'];
        $is_active = !empty($tab['active']);
        $content   = !empty($tab['content']) ? $tab['content'] : '';

        if (!$action) {
            continue;
        }

        if ('nav' == $type) {
            $html .= '<li' . ($is_active ? ' class="active"' : '') . '><a class="' . $args['nav_class'] . '" data-toggle="tab"' . ($content ? '' : ' data-ajax=""') . ' href="#' . $id . '">' . $name . '</a></li>';
        } else {
            $a_attr   = $args['no_scroll'] ? ' no-scroll="true"' : '';
            $div_attr = '';

            if ($is_active) {
                $class .= ' in active';
                if ($args['ias'] && !$content) {
                    $div_attr .= ' lazyload-action="ias"';
                    $class .= ' lazyload';
                }
            }

            $html .= '<div class="tab-pane fade ajaxpager' . $class . '" id="' . $id . '"' . $div_attr . '>';
            if ($content) {
                $html .= $content;
            } else {
                $html .= '<span class="post_ajax_trigger hide"><a' . $a_attr . ' ajax-href="' . esc_url(add_query_arg('action', $action, $ajax_url)) . '" class="ajax_load ajax-next ajax-open"></a></span>';
                $html .= '<div class="post_ajax_loader">' . $loader . '</div>';
            }
            $html .= '</div>';
        }
    }
    return $html;
}

/**
 * @description: 判断自己是不是文章的作者
 * @param {*} $post
 * @param {*} $user_id
 * @return {*}
 */
function zib_is_the_author($post = null, $user_id = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (empty($post->post_author)) {
        return false;
    }
    return $post->post_author == $user_id;
}

//获取社交登录的链接
//自动判断是否开启此社交登录方式，可直接当做判断函数使用
function zib_get_oauth_login_url($type, $rurl = '')
{
    if (!$rurl) {
        $rurl = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : zib_get_current_url();
    }

    static $login_args = array();

    if (isset($login_args[$type])) {
        $login_url = $login_args[$type];
    } else {
        $login_url         = apply_filters('zib_oauth_login_url', '', $type);
        $login_args[$type] = $login_url;
    }

    if ($login_url) {
        return add_query_arg('rurl', urlencode($rurl), $login_url);
    }

    return false;
}

//彩虹聚合登录
function zib_get_agent_clogin_login_url($url, $type)
{
    if (!$url && _pz('clogin_s')) {
        //彩虹聚合登录
        $option  = _pz('clogin_option');
        $oauth_s = !empty($option['oauth_s']) ? (array) $option['oauth_s'] : array();

        if ($option['url'] && $option['id'] && $option['key'] && in_array($type, $oauth_s)) {
            return home_url('oauth/clogin?type=' . $type);
        }
    }
    return $url;
}
add_filter('zib_oauth_login_url', 'zib_get_agent_clogin_login_url', 20, 2);

//zibll代理登录
function zib_get_agent_oauth_login_url($url, $type)
{
    if (!$url && _pz('oauth_agent', 'close') === 'client') {
        //zibll代理登录
        $option  = _pz('oauth_agent_client_option');
        $oauth_s = !empty($option['oauth_s']) ? (array) $option['oauth_s'] : array();
        if ($option['url'] && $option['key'] && in_array($type, $oauth_s)) {
            return home_url('/oauth/agent?type=' . $type);
        }
    }

    return $url;
}
add_filter('zib_oauth_login_url', 'zib_get_agent_oauth_login_url', 30, 2);

/**
 * @description: 获取自带的社交账号登录链接
 * @param {*} $type
 * @return {*}
 */
function zib_get_self_oauth_login_url($url, $type)
{
    if (!$url && _pz('oauth_' . $type . '_s')) {
        $url = home_url('oauth/' . $type);
    }

    return $url;
}
add_filter('zib_oauth_login_url', 'zib_get_self_oauth_login_url', 50, 2);

//判断扫码登录功能是否开启
function zib_is_oauth_qrcode_s()
{
    $qrcode_type = array('weixingzh'); //使用扫码登录的类型
    foreach ($qrcode_type as $type) {
        if (zib_get_oauth_login_url($type)) {
            return true;
        }
    }
    return false;
}

//社交登录按钮构建
function zib_social_login($echo = true)
{
    if (zib_is_close_sign()) {
        return;
    }

    $buttons = '';
    if (_pz('social') && function_exists('xh_social_loginbar')) {
        $buttons = xh_social_loginbar('', false);
    } else {
        $b_c  = _pz('oauth_button_lg') ? ' button-lg' : '';
        $args = zib_get_social_type_data();
        foreach ($args as $arg) {
            $type = $arg['type'];
            $name = $arg['name'];
            $icon = zib_get_cfs_icon($arg['icon']);
            if ('alipay' == $type) {
                if (wp_is_mobile() && !strpos($_SERVER['HTTP_USER_AGENT'], 'Alipay')) {
                    continue;
                }
                //移动端并且不是支付宝APP不显示支付宝
            }

            $href = zib_get_oauth_login_url($type);
            if ($href) {
                $_class = $type . ($b_c ? $b_c : ' toggle-radius');
                if (!empty($arg['qrcode'])) {
                    $_class .= ' qrcode-signin';
                }

                $buttons .= '<a rel="nofollow" title="' . $name . '登录" href="' . esc_url($href) . '" class="social-login-item ' . $_class . '">' . $icon . ($b_c ? $name . '登录' : '') . '</a>';
            }
        }
    }
    if ($echo && $buttons) {
        echo '<p class="social-separator separator muted-3-color em09">社交账号登录</p>';
        echo '<div class="social_loginbar">';
        echo $buttons;
        echo '</div>';
    } else {
        return $buttons;
    }
}

/**
 * @description: 获取社交登录的类型名字
 * @param {*} $type
 * @return {*}
 */
function zib_get_social_type_name($type)
{
    $type_name = zib_get_social_type_data();
    return isset($type_name[$type]['name']) ? $type_name[$type]['name'] : '第三方';
}

/**
 * @description: 判断微信公众号是否是扫码模式
 * @param {*}
 * @return {*}
 */
function zib_weixingzh_is_qrcode()
{
    //不在微信内
    if (!zib_is_wechat_app(array('wxwork'))) {
        return true;
    }

    if (_pz('oauth_agent', 'close') === 'client') {
        if (_pz('oauth_agent_client_option', '', 'gzh_type') === 'not') {
            return true;
        }
    } elseif (_pz('oauth_weixingzh_option', '', 'gzh_type') === 'not') {
        return true;
    }

    return false;
}

/**
 * @description: 获取全部社交登录的资料
 * @param {*}
 * @return {*}
 */
function zib_get_social_type_data()
{
    $args       = array();
    $args['qq'] = array(
        'name'     => 'QQ',
        'type'     => 'qq',
        'class'    => 'c-blue',
        'name_key' => 'nickname',
        'icon'     => 'fa fa-qq',
    );
    $args['weixin'] = array(
        'name'     => '微信',
        'type'     => 'weixin',
        'class'    => 'c-green',
        'name_key' => 'nickname',
        'icon'     => 'fa fa-weixin',
    );
    $args['weixingzh'] = array(
        'name'   => '微信',
        'type'   => 'weixingzh',
        'class'  => 'c-green',
        'icon'   => 'fa fa-weixin',
        'qrcode' => zib_weixingzh_is_qrcode(),
    );
    $args['weibo'] = array(
        'name'     => '微博',
        'type'     => 'weibo',
        'class'    => 'c-red',
        'name_key' => 'screen_name',
        'icon'     => 'fa fa-weibo',
    );
    $args['gitee'] = array(
        'name'     => '码云',
        'type'     => 'gitee',
        'name_key' => 'name',
        'class'    => '',
        'icon'     => 'zibsvg-gitee',
    );
    $args['baidu'] = array(
        'name'  => '百度',
        'type'  => 'baidu',
        'class' => '',
        'icon'  => 'zibsvg-baidu',
    );
    $args['alipay'] = array(
        'name'  => '支付宝',
        'type'  => 'alipay',
        'class' => 'c-blue',
        'icon'  => 'zibsvg-alipay',
    );
    $args['dingtalk'] = array(
        'name'  => '钉钉',
        'type'  => 'dingtalk',
        'class' => 'c-blue',
        'icon'  => 'zibsvg-dingtalk',
    );
    $args['huawei'] = array(
        'name'  => '华为',
        'type'  => 'huawei',
        'class' => 'c-blue',
        'icon'  => 'zibsvg-huawei',
    );
    $args['xiaomi'] = array(
        'name'  => '小米',
        'type'  => 'xiaomi',
        'class' => 'c-blue',
        'icon'  => 'zibsvg-xiaomi',
    );
    $args['github'] = array(
        'name'     => 'GitHub',
        'type'     => 'github',
        'class'    => '',
        'name_key' => 'name',
        'icon'     => 'fa fa-github',
    );
    $args['google'] = array(
        'name'  => 'Google',
        'type'  => 'google',
        'class' => 'c-blue',
        'icon'  => 'fa fa-google',
    );
    $args['microsoft'] = array(
        'name'  => 'Microsoft',
        'type'  => 'microsoft',
        'class' => 'c-blue',
        'icon'  => 'fa fa-windows',
    );
    $args['facebook'] = array(
        'name'  => 'Facebook',
        'type'  => 'facebook',
        'class' => 'c-blue',
        'icon'  => 'fa fa-facebook',
    );
    $args['twitter'] = array(
        'name'  => 'Twitter',
        'type'  => 'twitter',
        'class' => 'c-blue',
        'icon'  => 'fa fa-twitter',
    );

    //此处新增时候，需要在inc/dependent.php：156同步新增type

    return $args;
}

//微信app内自动登录
//限制时间&&开启此功能&&在微信APP内&&未登录状态&&开启了微信公众号登录
function zib_weixingzh_sign_script()
{

    //判断微信公众号功能是否开启
    //判断是否已经登录
    if (!zib_get_oauth_login_url('weixingzh') || get_current_user_id()) {
        return;
    }

    if (zib_weixingzh_is_qrcode()) {
        //PC端扫码登录
        //在PC端点击登录优先显示微信扫码登录
        if (_pz('weixingzh_priority')) {
            $script = '<script type="text/javascript">';
            $script .= '_win.signin_wx_priority = true;';
            $script .= is_page_template('pages/user-sign.php') && isset($_GET['tab']) && $_GET['tab'] === 'signin' ? 'window.onload = function(){
                            $(\'.social-login-item.weixingzh:first\').click();
                        };' : '';
            $script .= '</script>';
            echo $script;
        }
    } else {
        //不是扫码登录，也就是微信APP内登录
        //在微信APP内自动弹出微信登录
        if (!isset($_COOKIE['showed_weixingzh_auto']) && _pz('weixingzh_auto')) {
            $expires = round(_pz('weixingzh_auto_expires', 24) / 24, 3);
            $script  = '<script type="text/javascript">';
            $script .= 'window.onload = function(){setTimeout(function () {
                            var _w = $(\'.social-login-item.weixingzh:first\');
                            if(_w.length){
                                window.location.href=_w.attr(\'href\');
                                ' . ($expires > 0 ? '$.cookie("showed_weixingzh_auto","showed", {path: "/",expires: ' . $expires . '});' : '') . '
                            }
                        }, 100)};';
            $script .= '</script>';
            echo $script;
        }
    }
}

add_action('wp_footer', 'zib_weixingzh_sign_script', 99);

// 链接提交的模态框
function zib_submit_links_modal($args = array())
{
    $defaults = array(
        'class'      => '',
        'title'      => '申请入驻',
        'dec'        => '',
        'show_title' => true,
        'sign'       => true,
        'cats'       => [],
    );

    $args = wp_parse_args((array) $args, $defaults);

    $title = $args['title'];
    if ($title) {
        $title = '<div class="mb20"><button class="close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-pencil-square-o"></i></span>' . $title . '</b></div>';
    }

    $input = '';

    if ($args['dec']) {
        $input .= '<div class="muted-box em09">' . $args['dec'] . '</div>';
    }

    if ($args['sign'] && !get_current_user_id()) {
        $input .= '<div class="muted-box text-center">';
        $input .= '<div class="mb20 muted-3-color">请先登录</div>';
        $input .= '<p>';
        $input .= '<a href="javascript:;" class="signin-loader but c-blue padding-lg"><i class="fa fa-fw fa-sign-in mr10" aria-hidden="true"></i>登录</a>';
        $input .= !zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but c-yellow padding-lg"><i data-class="icon mr10" data-viewbox="0 0 1024 1024" data-svg="signup" aria-hidden="true"></i>注册</a>' : '';
        $input .= '</p>';
        $input .= '<div class="social_loginbar">';
        $input .= zib_social_login(false);
        $input .= '</div>';
        $input .= '</div>';
    } else {
        $cats_query_args = array(
            'taxonomy'   => array('link_category'),
            'hide_empty' => false,
        );

        if ($args['cats']) {
            $cats_query_args['include'] = $args['cats'];
            $cats_query_args['orderby'] = 'include';
        }
        $cats_query = new WP_Term_Query($cats_query_args);

        $cats_options = '';
        if (!is_wp_error($cats_query) && !empty($cats_query->terms)) {
            foreach ($cats_query->terms as $item) {
                $cats_options .= '<option value="' . $item->term_id . '">' . $item->name . '</option>';
            }
        }
        $cats_options = $cats_options ? '<div class="col-sm-12 mb10">
            <div class="em09 muted-2-color mb6">网站类别</div>
            <div class="form-select"><select name="link_category" class="form-control">' . $cats_options . '</select></div>
        </div>' : '';

        $input .= '<form class="form-horizontal mt10 form-upload">';

        $input .= '<div class="row gutters-5">
                        <div class="col-sm-6 mb10">
                            <div class="em09 muted-2-color mb6">网站名称（必填）</div>
                            <input type="text" class="form-control" id="link_name" name="link_name" placeholder="请输入网站名称">
                        </div>
                        <div class="col-sm-6 mb10">
                            <div class="em09 muted-2-color mb6">网站地址（必填）</div>
                            <input type="text" class="form-control" id="link_url" name="link_url" placeholder="https://...">
                        </div>

                    <div class="col-sm-12 mb10">
                        <div class="em09 muted-2-color mb6">网站简介</div>
                        <input type="text" class="form-control" id="link_description" name="link_description" placeholder="一句话介绍网站">
                    </div>
                     ' . $cats_options . '
                    <div class="col-sm-12 mb10">
                        <div class="em09 muted-2-color mb6">LOGO图像</div>
                        <label class="pointer"><div class="preview preview-square"><img class="fit-cover" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg" alt="添加图片"></div>
                            <input class="hide" type="file" zibupload="image_upload" accept="image/gif,image/jpeg,image/jpg,image/png" name="image">
                        </label>
                        <div class="px12 muted-2-color mb6">请选择正方形LOGO图像，支持jpg/png/gif格式</div>
                    </div>
                </div>';

        //人机验证
        if (_pz('verification_links_s')) {
            $verification_input = zib_get_machine_verification_input('frontend_links_submit');
            if ($verification_input) {
                $input .= '<div class="col-sm-9" style="max-width: 300px;">' . $verification_input . '</div>';
            }
        }

        $input .= '<div class="text-right edit-footer">
                        <button type="button" zibupload="submit" class="but c-blue padding-lg input-expand-upload" name="submit"><i class="fa fa-check" aria-hidden="true"></i>确认提交</button>
                    </div>';

        $input .= wp_nonce_field('frontend_links_submit', '_wpnonce', false, false); //安全效验
        $input .= '<input type="hidden" name="action" value="frontend_links_submit">';
        $input .= '</form>';
    }

    $card = $input;

    $html = '<div class="modal fade" id="submit-links-modal" tabindex="-1" role="dialog" aria-hidden="false">    <div class="modal-dialog" role="document">    <div class="modal-content" style=""><div class="modal-body">' . $title . $card . '</div></div>    </div>    </div>';

    return $html;
}

//获取新建按钮
function zib_get_new_add_btns($types = array('post'), $class = '', $con = '')
{

    if (zib_is_close_sign()) {
        return;
    }

    $btns = array();
    $html = '';
    foreach ($types as $type) {
        $_btn = apply_filters('new_add_btns_' . $type, '');
        if ($_btn) {
            $btns[] = $_btn;
        }
    }

    if (!$btns) {
        return;
    }

    if (!isset($btns[1])) {
        //只有一个按钮
        $pattern     = "/<a(.*?) class=('|\")(.*?)('|\")(.*?)>(.*?)<\/a>/i";
        $replacement = '<a$1 class="newadd-btns ' . $class . ' $3"$5>' . $con . '</a>';
        return preg_replace($pattern, $replacement, $btns[0]);
    } else {
        //有多个按钮
        $html = '<span class="newadd-btns hover-show ' . $class . '">
                    ' . $con . '
                    <div class="hover-show-con dropdown-menu drop-newadd">' . implode('', $btns) . '</div>
                </span>';
    }
    return $html;
}

//新建按钮的选项
function zib_new_add_btns_options()
{
    return apply_filters('new_add_btns_options', array('post' => '文章投稿'));
}

//新建按钮-前台投稿
function zib_new_add_btns_post_filter()
{
    if (_pz('post_article_s', true) && !is_page_template('pages/newposts.php')) {
        $href = zib_get_new_post_url();
        $icon = '<icon class="jb-green"><i class="fa fa-pencil-square"></i></icon>';
        return '<a rel="nofollow" class="btn-newadd" href="' . $href . '">' . $icon . '<text>' . _pz('post_article_btn_txte', '发布文章') . '</text></a>';
    }
    return;
}

add_filter('new_add_btns_post', 'zib_new_add_btns_post_filter');

/**
 * @description: 编辑器按钮扩展
 * @param {*}
 * @return {*}
 */
function zib_get_input_expand_but($type = 'smilie', $args_1 = null, $args_2 = null)
{
    $but      = '';
    $dropdown = '';

    //根据类型进行循环
    switch ($type) {
        case 'quick':
            $btn_title   = '快捷回复';
            $quick_id    = $args_2;
            $quick_often = $args_1 ? $args_1 : array();
            $but         = '<a class="but btn-input-expand input-quick mr6" href="javascript:;">' . zib_get_svg('quick-reply') . '<span class="hide-sm">' . $btn_title . '</span></a>';

            $sys_lists  = '';
            $sys_is_has = false;
            //系统快捷回复
            if ($quick_often) {
                foreach ($quick_often as $item) {
                    $sys_lists .= '<div class="quick-reply-item">' . esc_attr($item['val']) . '</div>';
                }
            }
            if (!$sys_lists) {
                $sys_lists = '<div class="text-center muted-2-color" style="margin: 60px 0;">系统暂无快捷回复</div>';
            } else {
                $sys_is_has = true;
            }

            //用户的快捷回复
            $user_lists      = '';
            $user_is_has     = false;
            $current_user_id = get_current_user_id();
            if ($current_user_id) {
                $user_quick_often = zib_get_user_quick_often($current_user_id);
                if ($user_quick_often) {
                    foreach ($user_quick_often as $item) {
                        $user_lists .= '<div class="quick-reply-item">' . esc_attr($item) . '</div>';
                    }
                }
                if (!$user_lists) {
                    $user_lists = '<div class="quick-reply-myitem-box"><div class="text-center muted-2-color" style="margin: 40px 0 30px;">暂无快捷回复</div></div><div class="padding-10">' . zib_get_user_edit_quick_often_link('but block c-blue', zib_get_svg('add') . '添加快捷回复') . '</div>';
                } else {
                    $user_is_has = true;
                    $user_lists  = '<div class="quick-reply-myitem-box">' . $user_lists . '</div><div class="padding-10">' . zib_get_user_edit_quick_often_link('but block', zib_get_svg('quick-reply') . '编辑我的快捷回复') . '</div>';
                }
            }

            if ($user_lists) {
                $sys_is_active = !$user_is_has && $sys_is_has;
                $dropdown      = '<div class="relative dropdown-quick-often">
                                <ul class="list-inline tab-nav-theme text-center">
                                    <li class="' . ($sys_is_active ? 'active' : '') . '"><a class="ml3 mr3" data-toggle="tab" href="#input_expand_quick_often_sys_' . $quick_id . '">系统</a></li>
                                    <li class="' . (!$sys_is_active ? 'active' : '') . '"><a class="ml3 mr3" data-toggle="tab" href="#input_expand_quick_often_user_' . $quick_id . '">我的</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div class="' . ($sys_is_active ? ' in active' : '') . ' tab-pane fade quick-often-box scroll-y mini-scrollbar" id="input_expand_quick_often_sys_' . $quick_id . '">' . $sys_lists . '</div>
                                    <div class="' . (!$sys_is_active ? ' in active' : '') . ' tab-pane fade quick-often-box scroll-y mini-scrollbar" id="input_expand_quick_often_user_' . $quick_id . '">' . $user_lists . '</div>
                                </div>
                            </div>';
            } else {
                $dropdown = '<div class="relative dropdown-quick-often"><div class="quick-often-box scroll-y mini-scrollbar">' . $sys_lists . '</div></div>';
            }

            break;

        case 'smilie':
            $but              = '<a class="but btn-input-expand input-smilie mr6" href="javascript:;"><i class="fa fa-fw fa-smile-o"></i><span class="hide-sm">表情</span></a>';
            $smilie_icon_args = array('aoman', 'baiyan', 'bishi', 'bizui', 'cahan', 'ciya', 'dabing', 'daku', 'deyi', 'doge', 'fadai', 'fanu', 'fendou', 'ganga', 'guzhang', 'haixiu', 'hanxiao', 'zuohengheng', 'zhuakuang', 'zhouma', 'zhemo', 'zhayanjian', 'zaijian', 'yun', 'youhengheng', 'yiwen', 'yinxian', 'xu', 'xieyanxiao', 'xiaoku', 'xiaojiujie', 'xia', 'wunai', 'wozuimei', 'weixiao', 'weiqu', 'tuosai', 'tu', 'touxiao', 'tiaopi', 'shui', 'se', 'saorao', 'qiudale', 'qinqin', 'qiaoda', 'piezui', 'penxue', 'nanguo', 'liulei', 'liuhan', 'lenghan', 'leiben', 'kun', 'kuaikule', 'ku', 'koubi', 'kelian', 'keai', 'jingya', 'jingxi', 'jingkong', 'jie', 'huaixiao', 'haqian', 'aini', 'OK', 'qiang', 'quantou', 'shengli', 'woshou', 'gouyin', 'baoquan', 'aixin', 'bangbangtang', 'xiaoyanger', 'xigua', 'hexie', 'pijiu', 'lanqiu', 'juhua', 'hecai', 'haobang', 'caidao', 'baojin', 'chi', 'dan', 'kulou', 'shuai', 'shouqiang', 'yangtuo', 'youling');
            $smilie_icon      = '';
            $img_url          = ZIB_TEMPLATE_DIRECTORY_URI . '/img/smilies/';
            $lazy_attr        = zib_is_lazy('lazy_other', true) ? 'class="lazyload" data-' : '';
            foreach ($smilie_icon_args as $smilie_i) {
                $smilie_icon .= '<a class="smilie-icon" href="javascript:;" data-smilie="' . $smilie_i . '"><img ' . $lazy_attr . 'src="' . $img_url . $smilie_i . '.gif" alt="[' . $smilie_i . ']" /></a>';
            }
            $dropdown = '<div class="dropdown-smilie scroll-y mini-scrollbar">' . $smilie_icon . '</div>';
            break;
        case 'code':
            $but = '<a class="but btn-input-expand input-code mr6" href="javascript:;"><i class="fa fa-fw fa-code"></i><span class="hide-sm">代码</span></a>';

            $dropdown = '<div class="dropdown-code">';
            $dropdown .= '<p>请输入代码：</p>';
            $dropdown .= '<p><textarea rows="6" tabindex="1" class="form-control input-textarea" placeholder="在此处粘贴或输入代码"></textarea></p>';
            $dropdown .= '<div class="text-right"><a type="submit" class="but c-blue pw-1em" href="javascript:;">确认</a></div>';
            $dropdown .= '</div>';

            break;

        case 'image':
            $upload    = $args_1;
            $upload_id = $args_2;
            if (!is_user_logged_in()) {
                $upload = false;
            }

            $but = '<a class="but btn-input-expand input-image mr6" href="javascript:;"><i class="fa fa-fw fa-image"></i><span class="hide-sm">图片</span></a>';

            $dropdown = '<div class="tab-content">';

            //第一个tab|输入图片地址
            $dropdown .= '<div class="tab-pane fade in active dropdown-image" id="image-tab-' . $upload_id . '-1">';
            $dropdown .= '<p>请填写图片地址：</p>';
            $dropdown .= '<p><textarea rows="2" tabindex="1" class="form-control input-textarea" style="height:95px;" placeholder="http://..."></textarea></p>';
            $dropdown .= '<div class="text-right">';
            if ($upload) {
                $dropdown .= '<a class="but c-yellow mr10 pw-1em" data-toggle="tab" href="#image-tab-' . $upload_id . '-2" data-onclick="#input_' . $upload_id . '_image_upload">上传图片</a>';
            }
            $dropdown .= '<a type="submit" class="but c-blue pw-1em" href="javascript:;">确认</a>';
            $dropdown .= '</div>';
            $dropdown .= '</div>';

            if ($upload) {
                //第二个tab|上传图片
                $dropdown .= '<div class="tab-pane fade dropdown-image" id="image-tab-' . $upload_id . '-2">';
                $dropdown .= '<p><a class="muted-color" data-toggle="tab" href="#image-tab-' . $upload_id . '-1"><i class="fa fa-angle-left mr6"></i>填写图片地址</a></p>';

                $from = '<div class="form-upload">
                            <label style="width:100%;" class="pointer">
                                <div class="preview text-center mb6"><img style="width:100%;height:96px;object-fit:cover;" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg' . '"></div>
                                <input class="hide" type="file" id="input_' . $upload_id . '_image_upload" zibupload="image_upload" accept="image/gif,image/jpeg,image/jpg,image/png" name="image_upload" action="image_upload">
                            </label>
                            <div class="text-right">
                                <button type="button" zibupload="submit" auto-submit="true" class="but jb-blue pw-1em input-expand-upload" name="submit">确认上传</button>
                                <input type="hidden" data-name="action" data-value="user_upload_image">
                                <input type="hidden" data-name="upload_image_nonce" data-value="' . wp_create_nonce('upload_image') . '">
                            </div>
                    </div>';

                $dropdown .= $from;

                $dropdown .= '</div>';
            }

            $dropdown .= '</div>';
            break;

        default:
            break;
    }

    $con = $but . '<div class="dropdown-menu">' . $dropdown . '</div>';
    return '<span class="dropup relative ' . $type . '">' . $con . '</span>';
}

function zib_get_input_expand_but_smilie()
{
    
    


    
}


/**
 * @description: 图文卡片
 * @param {*}
 * @return {*}
 */
function zib_graphic_card($args = array(), $echo = false)
{
    $defaults = array(
        'type'         => '',
        'hide'         => '',
        'class'        => 'mb20',
        'img'          => '',
        'video'        => '',
        'alt'          => '图片',
        'link'         => array(
            'url'    => '',
            'target' => '',
        ),
        'text'         => '',
        'text1'        => '',
        'text2'        => '',
        'text3'        => '',
        'more'         => '',
        'lazy'         => true,
        'height_scale' => 0,
        'mask_opacity' => 0,
    );

    $args = wp_parse_args((array) $args, $defaults);
    if (!$args['img'] && !$args['video']) {
        return;
    }

    $args['class'] .= ' ' . $args['type'];
    $lazy = $args['lazy'];

    //显示规则
    if ($args['hide']) {
        $is_mobile = wp_is_mobile();
        if ((!$is_mobile && $args['hide'] === 'pc') || ($is_mobile && $args['hide'] === 'm')) {
            return;
        }
    }

    $img = '';
    if ($args['video'] && !wp_is_mobile()) {
        $video_poster_attr = $args['img'] ? ' poster="' . $args['img'] . '"' : '';
        $img               = '<video autoplay="" loop="" muted="" class="fit-cover' . ($lazy ? ' lazyload' : '') . '" ' . ($lazy ? 'data-src="' . $args['video'] . '" src=""' : 'src="' . $args['video'] . '"') . $video_poster_attr . '></video>';
    } elseif ($args['img']) {
        $lazy_src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail.svg';
        $img      = '<img class="fit-cover' . ($lazy ? ' lazyload' : '') . '" ' . ($lazy ? 'data-src="' . $args['img'] . '" src="' . $lazy_src . '"' : 'src="' . $args['img'] . '"') . ' alt="' . ($args['alt'] ?: '图片') . zib_get_delimiter_blog_name() . '">';
    }

    if (!$img) {
        return;
    }

    $mask = $args['mask_opacity'] ? '<div class="absolute graphic-mask" style="opacity: ' . ((int) $args['mask_opacity'] / 100) . ';"></div>' : '';
    $text = '';
    if ('style-2' === $args['type']) {
        $text = '<div class="abs-center conter-conter graphic-text">';
        $text .= $args['text1'] ? '<div class="title-h-center"><b>' . $args['text1'] . '</b></div>' : '';
        $text .= $args['text2'] ? '<div class="em09 opacity8">' . $args['text2'] . '</div>' : '';
        $text .= '</div>';
        if ($args['text3']) {
            $text .= '<div class="abs-center right-top">';
            $text .= '<badge class="b-black opacity8">' . $args['text3'] . '</badge>';
            $text .= '</div>';
        }
    } elseif ('style-3' === $args['type']) {
        $text = '<div class="abs-center left-bottom graphic-text text-ellipsis">';
        $text .= $args['text1'];
        $text .= '</div>';
        $text .= '<div class="abs-center left-bottom graphic-text">';
        $text .= '<div class="em09 opacity8">' . $args['text2'] . '</div>';
        $text .= $args['text3'] ? '<div class="px12 opacity8 mt6">' . $args['text3'] . '</div>' : '';
        $text .= '</div>';
    } elseif ('style-4' === $args['type']) {
        $text = '';
        $text .= '<div class="abs-center right-top">';
        $text .= '<badge class="b-black opacity8 mr6 mt6">' . $args['text3'] . '</badge>';
        $text .= '</div>';
    } else {
        $text = $args['text1'] ? '<div class="title-h-left"><b>' . $args['text1'] . '</b></div>' : '';
        $text .= $args['text3'] ? '<div class="em09 opacity8">' . $args['text3'] . '</div>' : '';
        $text .= $args['text2'] ? '<div class="em09">' . $args['text2'] . '</div>' : '';
        $text = $text ? '<div class="abs-center left-bottom graphic-text">' . $text . '</div>' : '';
    }
    $text .= $args['more'] ? $args['more'] : '';

    $height_scale = $args['height_scale'] ? ' style="padding-bottom: ' . (int) $args['height_scale'] . '%!important;"' : '';
    $html         = $args['link']['url'] ? '<a' . ($args['link']['target'] ? ' target="' . $args['link']['target'] . '"' : '') . ' href="' . $args['link']['url'] . '">' : '';
    $html .= 'style-4' == $args['type'] ? '<div class="main-shadow radius8 main-bg mb10">' : '';
    $html .= '<div class="graphic hover-zoom-img ' . $args['class'] . '"' . $height_scale . '>';
    $html .= $img;
    $html .= $mask;
    $html .= $text;
    $html .= 'style-4' == $args['type'] ? '</div>' : '';
    if ('style-4' === $args['type']) {
        $html .= '<div class="padding-10">';
        $html .= '<div class="text-ellipsis"> ' . $args['text1'] . '</div>';
        $html .= $args['text2'] ? '<div class="muted-2-color em09 text-ellipsis mt6"> ' . $args['text2'] . '</div>' : '';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= $args['link']['url'] ? '</a>' : '';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * @description: 图文卡片
 * @param {*} $args
 * @param {*} $echo
 * @return {*}
 */
function zib_icon_card($args = array(), $echo = false)
{
    $defaults = array(
        'type'              => '',
        'class'             => 'box-body nopw-sm',
        'icon'              => '',
        'icon_size'         => '',
        'customize_icon'    => '',
        'link'              => array(
            'url'    => '',
            'target' => '',
        ),
        'icon_radius'       => '',
        'icon_color'        => '',
        'icon_custom_color' => '',
        'icon_class'        => '',
        'title'             => '',
        'desc'              => '',
    );
    $args = wp_parse_args((array) $args, $defaults);
    if (!$args['customize_icon'] && !$args['icon']) {
        return;
    }

    $args['title'] = trim($args['title']);
    $icon          = $args['customize_icon'] ? $args['customize_icon'] : zib_get_cfs_icon($args['icon']);
    $icon_class    = $args['icon_radius'] ? 'card-icon toggle-radius fa-3x ' . ($args['icon_class'] ? $args['icon_class'] : $args['icon_color']) : 'card-icon fa-4x ' . $args['icon_color'];

    $icon          = '<span class="' . $icon_class . '"' . (!$args['icon_radius'] && $args['icon_custom_color'] ? ' style="color:' . ($args['icon_custom_color']) . ';"' : '') . '>' . $icon . '</span>';
    $icon          = $args['link']['url'] ? '<a' . ($args['link']['target'] ? ' target="' . $args['link']['target'] . '"' : '') . ' href="' . $args['link']['url'] . '">' . $icon . '</a>' : $icon;
    $icon          = $args['icon_size'] ? '<span style="font-size:' . $args['icon_size'] . 'px;">' . $icon . '</span>' : $icon;
    $args['title'] = $args['link']['url'] && $args['title'] ? '<a class="main-color" ' . ($args['link']['target'] ? ' target="' . $args['link']['target'] . '"' : '') . ' href="' . $args['link']['url'] . '">' . $args['title'] . '</a>' : $args['title'];

    $class = $args['class'] . ' ' . $args['type'];

    $title = $args['title'] ? '<div class="mt10 em12 text-ellipsis"> ' . $args['title'] . '</div>' : '';
    $title .= trim($args['desc']) ? '<div class="muted-color mt6"> ' . trim($args['desc']) . '</div>' : '';

    $html = '';
    $html .= '<div class="icon-card ' . $class . '">';
    $html .= $icon;
    $html .= $title ? '<div class="px12-sm"> ' . $title . '</div>' : '';
    $html .= '</div>';
    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * @description: 图文卡片
 * @param {*} $args
 * @param {*} $echo
 * @return {*}
 */
function zib_icon_cover_card($args = array(), $echo = false)
{
    $defaults = array(
        'class'             => 'zib-widget',
        'icon'              => '',
        'icon_size'         => 25,
        'customize_icon'    => '',
        'link'              => array(
            'url'    => '',
            'target' => '',
        ),
        'icon_radius'       => '',
        'icon_color'        => '',
        'icon_custom_color' => '',
        'icon_class'        => '',
        'title'             => '',
        'desc'              => '',
    );

    $args = wp_parse_args((array) $args, $defaults);
    if (!$args['customize_icon'] && !$args['icon']) {
        return;
    }

    $icon = $args['customize_icon'] ? $args['customize_icon'] : zib_get_cfs_icon($args['icon'], 'em09');

    $icon_class = $args['icon_class'] ? ' ' . $args['icon_class'] : '';
    $icon       = '<div class="icon-cover-icon badg cir' . $icon_class . '" style="font-size: ' . $args['icon_size'] . 'px;">' . $icon . '</div>';

    $title = $args['title'] ? '<div class="em12 text-ellipsis font-bold"> ' . $args['title'] . '</div>' : '';
    $title .= $args['desc'] ? '<div class="muted-color mt6 text-ellipsis"> ' . $args['desc'] . '</div>' : '';

    $html = '';
    $html .= $args['link']['url'] ? '<a class="main-color" ' . ($args['link']['target'] ? ' target="' . $args['link']['target'] . '"' : '') . ' href="' . $args['link']['url'] . '">' : '';
    $html .= '<div class="icon-cover-card flex ac ' . $args['class'] . '">';
    $html .= $icon;
    $html .= $title ? '<div class="icon-cover-desc ml10 flex1 px12-sm"> ' . $title . '</div>' : '';
    $html .= '</div>';
    $html .= $args['link']['url'] ? '</a>' : '';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * @description: 获取AJAX分页按钮的函数|仅显示下一页
 * @param {*} $count_all  列表总数量
 * @param {*} $page  当前页码
 * @param {*} $ice_perpage  每页加载数量
 * @param {*} $ajax_url  原始链接
 * @param {*} $pag_class  分页按钮类名
 * @param {*} $next_class  下一页按钮类名
 * @param {*} $nex  按钮内容
 * @param {*} $query_key  分页参数key
 * @param {*} $scroll  是否自动滑动
 * @param {*} $ajaxpager_target  目标类名
 * @return {*}
 */
function zib_get_ajax_next_paginate($count_all, $page = 1, $ice_perpage = 10, $ajax_url = '', $pag_class = 'text-center theme-pagination ajax-pag', $next_class = 'next-page ajax-next', $nex = '', $query_key = 'paged', $scroll = null, $ajaxpager_target = false)
{

    $total_pages = ceil($count_all / $ice_perpage);
    $con         = '';
    if ($total_pages > $page) {
        $nex = $nex ? $nex : _pz('ajax_trigger', '加载更多');
        if (!$ajax_url) {
            $ajax_url = home_url(remove_query_arg($query_key));
        }
        $attr = '';
        if ($scroll === 'no') {
            $attr = ' no-scroll="true"';
        } else {
            $attr = $scroll ? ' scroll-selector="' . $scroll . '"' : '';
        }

        if ($ajaxpager_target) {
            $attr .= ' ajaxpager-target="' . $ajaxpager_target . '"';
        }

        $href = esc_url(add_query_arg(array($query_key => $page + 1), $ajax_url));
        $con .= '<div class="' . $pag_class . '"><div' . $attr . ' class="' . $next_class . '">';
        $con .= '<a href="' . ($href) . '" paginate-all="' . $count_all . '" paginate-perpage="' . $ice_perpage . '">' . $nex . '</a>';
        $con .= '</div></div>';
    }
    return $con;
}

/**
 * @description: 获取AJAX数字分页按钮的函数|显示数字分页
 * @param {*} $count_all  列表总数量
 * @param {*} $paged  当前页码
 * @param {*} $ice_perpage  每页加载数量
 * @param {*} $ajax_url  原始链接
 * @param {*} $pag_class  分页按钮类名
 * @param {*} $next_class  下一页按钮类名
 * @param {*} $query_key  分页参数key
 * @param {*} $ajaxpager_target  目标类名
 * @return {*}
 */
function zib_get_ajax_number_paginate($count_all, $paged = 1, $ice_perpage = 10, $ajax_url = '', $pag_class = 'ajax-pag', $next_class = 'next-page ajax-next', $query_key = 'paged', $ajaxpager_target = false)
{
    $args = array(
        'url_base'         => add_query_arg(array($query_key => '%#%'), $ajax_url), // http://example.com/all_posts.php%#% : %#% 替换为页码。
        'link_sprintf'     => '<a class="' . $next_class . ' %s" ajax-replace="true" href="%s">%s</a>', // 1.class 2.link 3.内容
        'total'            => $count_all, //总计条数
        'current'          => $paged, //当前页码
        'page_size'        => $ice_perpage, //每页几条
        'class'            => 'pagenav ' . $pag_class,
        'ajaxpager_target' => false,
    );

    if ($ajaxpager_target) {
        $args['link_sprintf'] = '<a ajaxpager-target="' . $ajaxpager_target . '" class="' . $next_class . ' %s" ajax-replace="true" href="%s">%s</a>';
    }

    return zib_get_paginate_links($args);
}

//标准数字分页按钮构建
function zib_get_paginate_links($args)
{

    $defaults = array(
        'url_base'     => '', // http://example.com/all_posts.php%#% : %#% 替换为页码。
        'link_sprintf' => '<a class="%s" href="%s">%s</a>', // 1.class 2.link 3.内容
        'total'        => 0, //总计条数
        'current'      => 1, //当前页码
        'page_size'    => 12, //每页几条
        'prev_text'    => '<i class="fa fa-angle-left em12"></i><span class="hide-sm ml6">上一页</span>', //上一页按钮文字
        'next_text'    => '<span class="hide-sm mr6">下一页</span><i class="fa fa-angle-right em12"></i>', //下一页按钮文字
        'array'        => false,
        'class'        => 'pagenav ajax-pag',
    );

    $args = wp_parse_args($args, $defaults);

    $current      = (int) $args['current'];
    $total        = (int) $args['total'];
    $total_pages  = ceil($total / $args['page_size']); //总计页面格式
    $link_base    = $args['url_base'];
    $link_sprintf = $args['link_sprintf'];
    $wp_is_mobile = wp_is_mobile();
    $end_size     = 1;
    $mid_size     = $wp_is_mobile ? 0 : 2;
    $jump_size    = $wp_is_mobile ? 4 : 8;

    if ($total_pages < 2) {
        return;
    }

    $page_links = array();
    $dots       = false;

    //上一页
    if ($args['prev_text'] && $current && 1 < $current) {
        $link         = $link_base ? str_replace('%#%', $current - 1, $link_base) : 'javascript:void(0);';
        $page_links[] = sprintf($link_sprintf, 'prev page-numbers', esc_url($link), $args['prev_text']);
    }

    //循环数字
    for ($n = 1; $n <= $total_pages; $n++):
        if ($n == $current):
            $page_links[] = sprintf('<span class="page-numbers current">%s</span>', $n);
            $dots         = true;
        else:
            if ($n <= $end_size || ($current && $n >= $current - $mid_size && $n <= $current + $mid_size) || $n > $total_pages - $end_size):
                $link          = $link_base ? str_replace('%#%', $n, $link_base) : 'javascript:void(0);';
                $sprintf_class = 'page-numbers';
                /**
                if ($n == $current + 1) {
                $sprintf_class .= ' current-next';
                }
                if ($n == $current - 1) {
                $sprintf_class .= ' current-prev';
                }

                if ($n == 1) {
                $sprintf_class .= ' page-first';
                }
                if ($n == $total_pages) {
                $sprintf_class .= ' page-last';
                }
                 */

                $page_links[] = sprintf($link_sprintf, $sprintf_class, esc_url($link), $n);

                $dots = true;
            elseif ($dots):
                $page_links[] = '<span class="page-numbers dots">' . __('&hellip;') . '</span>';

                $dots = false;
            endif;
        endif;
    endfor;

    //下一页
    if ($args['next_text'] && $current && $current < $total_pages) {
        $link         = $link_base ? str_replace('%#%', $current + 1, $link_base) : 'javascript:void(0);';
        $page_links[] = sprintf($link_sprintf, 'next page-numbers', esc_url($link), $args['next_text']);
    }

    //填写跳转翻页
    if ($total_pages > $jump_size && $link_base) {
        $page_links[] = sprintf($link_sprintf, 'pag-jump page-numbers', 'javascript:;', '<input autocomplete="off" max="' . $total_pages . '" current="' . $current . '" base="' . $link_base . '" type="' . ($wp_is_mobile ? 'number' : 'text') . '" class="form-control jump-input" name="pag-go"><span class="hi de-sm mr6 jump-text">跳转</span><i class="jump-icon fa fa-angle-double-right em12"></i>');
    }

    if ($args['array']) {
        return $page_links;
    }

    $html = '<div class="' . $args['class'] . '">';
    $html .= implode('', $page_links);
    $html .= '</div>';
    return $html;
}

function zib_get_remote_box($args)
{
    $defaults = array(
        'type'   => 'ias',
        'class'  => '',
        'loader' => '<i class="loading-spot"><i></i></i>', // 加载动画
        'url'    => admin_url('/admin-ajax.php'), // url
        'query'  => false, // add_query_arg
    );
    $args        = wp_parse_args($args, $defaults);
    $args['url'] = add_query_arg($args['query'], $args['url']);
    $attr        = ' remote-box="' . esc_url($args['url']) . '"';

    if ('ias' == $args['type']) {
        $attr .= ' lazyload-action="ias"';
        $args['class'] .= ' lazyload';
    }
    if ('load' == $args['type']) {
        $attr .= ' load-click';
    }

    $class = $args['class'] ? ' class="' . $args['class'] . '"' : '';
    return '<div' . $class . $attr . '>' . $args['loader'] . '</div>';
}

/**
 * @description: ajax方式输出ajaxpager内容
 * @param {*} $html
 * @param {*} $is_one  是否是一个独立内容，不需要分页
 * @return {*}
 */
function zib_ajax_send_ajaxpager($html, $is_one = false, $ajaxpager_calss = 'ajaxpager')
{

    if ($is_one) {
        $html = zib_get_ajax_ajaxpager_one_centent($html);
    }

    echo '<body style="display:none;"><main><div class="' . $ajaxpager_calss . '">' . $html . '</div></main></body>';
    exit;
}

function zib_ajax_modal_error($msg = '')
{
    zib_ajax_notice_modal('danger', $msg);
}

/**
 * @description: ajax模态框通知
 * @param {*} $type success|info|warning|danger
 * @param {*} $msg
 * @return {*}
 */
function zib_ajax_notice_modal($type = 'warning', $msg = '')
{
    $type_class = array(
        'success' => 'blue',
        'info'    => 'green',
        'warning' => 'yellow',
        'danger'  => 'red',
    );
    $icon_class = array(
        'success' => '<i class="fa fa-check fa-2x" aria-hidden="true"></i>',
        'info'    => '<i class="fa fa-bell-o fa-2x" aria-hidden="true"></i>',
        'warning' => '<i class="fa fa-exclamation-triangle fa-2x" aria-hidden="true"></i>',
        'danger'  => '<i class="fa fa-times-circle-o fa-2x" aria-hidden="true"></i>',
    );

    $class = isset($type_class[$type]) ? $type_class[$type] : 'yellow';
    $icon  = isset($icon_class[$type]) ? $icon_class[$type] : '<i class="fa fa-exclamation-triangle fa-2x" aria-hidden="true"></i>';

    $header = zib_get_modal_colorful_header('jb-' . $class, $icon);

    $html = '';
    $html .= $header;
    $html .= '<div class="em12 text-center c-' . $class . '" style="padding: 30px 0;">' . $msg . '</div>';
    echo $html;
    exit;
}

function zib_get_ajax_ajaxpager_one_centent($html)
{
    $html = '<div class="ajax-item">' . $html . '</div>';
    $html .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    return $html;
}

/**
 * @description: AJAX空白内容
 * @param {*}
 * @return {*}
 */
function zib_get_ajax_null($text = '暂无内容', $margin = '60', $img = 'null.svg')
{
    $html = zib_get_null($text, $margin, $img, 'ajax-item');
    $html .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    return $html;
}

/**
 * @description: 空白内容
 * @param {*}
 * @return {*}
 */
function zib_get_null($text = '暂无内容', $margin = '60', $img = 'null.svg', $class = '', $width = 280, $height = 0)
{
    $text = $text ? '<p style="margin-top:' . $margin . 'px;" class="em09 muted-3-color separator">' . $text . '</p>' : '';
    if ($height) {
        $style = $width ? 'max-width:' . $width . 'px;' : '';
        $style .= $height ? 'height:' . $height . 'px;' : '';
    } else {
        $style = $width ? 'width:' . $width . 'px;' : '';
    }
    $style .= 'opacity: .7;';
    $html = '<div class="text-center ' . $class . '" style="padding:' . $margin . 'px 0;"><img style="' . $style . '" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/' . $img . '">' . $text . '</div>';
    return $html;
}

/**
 * @description: 前台构建一个自动AJAX加载的ajaxpager
 * @param {*} $args
 * @return {*}
 */
function zib_get_ias_ajaxpager($args)
{
    $defaults = array(
        'type'            => 'ias',
        'id'              => '',
        'class'           => '',
        'loader'          => '<i class="loading-spot"><i></i></i>', // 加载动画
        'url'             => admin_url('/admin-ajax.php'), // url
        'query'           => false, // add_query_arg
        'ajaxpager_class' => 'ajaxpager',
    );

    $args = wp_parse_args($args, $defaults);
    if ($args['query']) {
        $args['url'] = add_query_arg($args['query'], $args['url']);
    }

    $id    = $args['id'] ? ' id="' . $args['id'] . '"' : '';
    $class = $args['class'] ? ' ' . $args['class'] : '';
    $attr  = 'ias' == $args['type'] ? ' lazyload-action="ias"' : '';

    $html = '';
    $html .= '<div class="' . $args['ajaxpager_class'] . ' lazyload' . $class . '"' . $id . $attr . '>';
    $html .= '<span class="post_ajax_trigger hide"><a' . ('load' == $args['type'] ? ' load-click' : '') . ($args['ajaxpager_class'] ? ' ajaxpager-target=".' . $args['ajaxpager_class'] . '"' : '') . ' ajax-href="' . esc_url($args['url']) . '" class="ajax_load ajax-next ajax-open"></a></span>';
    $html .= '<div class="post_ajax_loader">' . $args['loader'] . '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * @description: 获取当前页码的统一接口
 * @param {*}
 * @return {*}
 */
function zib_get_the_paged()
{
    $paged = isset($_REQUEST['paged']) ? (int) $_REQUEST['paged'] : 0;
    if ($paged) {
        return $paged;
    }
    $paged = (int) get_query_var('paged');
    if ($paged) {
        return $paged;
    }
    $paged = (int) get_query_var('page');
    if ($paged) {
        return $paged;
    }
    return 1;
}

/**
 * @description: 链接删除分页页码
 * @param {*} $url
 * @return {*}
 */
function zib_url_del_paged($url)
{
    $url = remove_query_arg(array('paged'), $url);
    global $wp_rewrite;
    $url = preg_replace("/\/$wp_rewrite->pagination_base\/\d*/", '', $url);

    return $url;
}

/**
 * @description: 获取当前页面的链接函数
 * @param {*}
 * @return {*}
 */
function zib_get_current_url()
{
    $home_url = home_url();
    $home_url = preg_replace('/^(http|https)(:\/\/)(?)([^\/]+).*$/im', '$1$2$3', $home_url);

    return $home_url . add_query_arg(null, false);
}

/**
 * @description: 后台页面的页面显示
 * @param {*} $total_count
 * @param {*} $number_per_page
 * @return {*}
 */
function zibpay_admin_pagenavi($total_count, $number_per_page = 15)
{
    $current_page = isset($_GET['paged']) ? $_GET['paged'] : 1;

    if (isset($_GET['paged'])) {
        unset($_GET['paged']);
    }

    $total_pages = ceil($total_count / $number_per_page);

    $first_page_url = add_query_arg('paged', 1);
    $last_page_url  = add_query_arg('paged', $total_pages);

    if ($current_page > 1 && $current_page < $total_pages) {
        $prev_page     = $current_page - 1;
        $prev_page_url = add_query_arg('paged', $prev_page);

        $next_page     = $current_page + 1;
        $next_page_url = add_query_arg('paged', $next_page);
    } elseif (1 == $current_page) {
        $prev_page_url  = '#';
        $first_page_url = '#';
        if ($total_pages > 1) {
            $next_page     = $current_page + 1;
            $next_page_url = add_query_arg('paged', $next_page);
        } else {
            $next_page_url = '#';
            $last_page_url = '#';
        }
    } elseif ($current_page == $total_pages) {
        $prev_page     = $current_page - 1;
        $prev_page_url = add_query_arg('paged', $prev_page);
        $next_page_url = '#';
        $last_page_url = '#';
    }
    ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <span class="displaying-num">每页 <?php echo $number_per_page; ?> 共<?php echo $total_count; ?></span>
            <span class="pagination-links">
                <a class="first-page button                                                                                                                                                                                                                                           <?php if (1 == $current_page) {
        echo 'disabled';
    }
    ?>" title="前往第一页" href="<?php echo $first_page_url; ?>">«</a>
                <a class="prev-page button                                                                                                                                                                                                                                     <?php if (1 == $current_page) {
        echo 'disabled';
    }
    ?>" title="前往上一页" href="<?php echo $prev_page_url; ?>">‹</a>
                <span class="paging-input">第 <?php echo $current_page; ?> 页，共 <span
                        class="total-pages"><?php echo $total_pages; ?></span> 页</span>
                <a class="next-page button                                                                                                                                                                                                                                     <?php if ($current_page == $total_pages) {
        echo 'disabled';
    }
    ?>" title="前往下一页" href="<?php echo $next_page_url; ?>">›</a>
                <a class="last-page button                                                                                                                                                                                                                                     <?php if ($current_page == $total_pages) {
        echo 'disabled';
    }
    ?>" title="前往最后一页" href="<?php echo $last_page_url; ?>">»</a>
            </span>
        </div>
        <br class="clear">
    </div>
<?php
}

/**
 * @description: 页面主要TAB统一接口|一个页面只能有一个这样的调用
 * @param {*} $type
 * @param {*} $tabs_options
 * 结构示例：$opt_shili = array(
'key' => array(
'title' => '栏目1',
'loader' => '',
),
'key_2' => array(
'title' => '栏目2',
'loader' => '',
),
);˝
 * @param {*} $id_prefix
 * @param {*} $is_swiper
 * @return {*}
 */
function zib_get_main_tab_nav($type = 'nav', $tabs_options = array(), $id_prefix = 'home', $is_mobile_swiper = true, $active_key_str = 'tab', $route = false, $base_url = '')
{
    //开始
    if (!$tabs_options || !is_array($tabs_options)) {
        return '';
    }

    //$active_key = !empty($_GET[$active_key_str]) ? $_GET[$active_key_str] : '';
    $active_key = get_query_var($active_key_str);
    $active_key = $active_key ? $active_key : (!empty($_GET[$active_key_str]) ? $_GET[$active_key_str] : '');

    if (!isset($tabs_options[$active_key])) {
        $active_key = '';
    }

    $is_mobile = wp_is_mobile();
    $is_swiper = $is_mobile_swiper ? wp_is_mobile() : false;

    $placeholder_default = '<div class="mb20"><div class="text-center muted-2-color mt20"><i class="loading mr10"></i>加载中...</div></div>';

    $ajax_url = zib_url_del_paged($base_url ? $base_url : zib_get_current_url());

    $html         = '';
    $i            = 1;
    $active_index = 1;
    foreach ($tabs_options as $key => $opt) {
        if (1 === $i && !$active_key) {
            $active_key = $key;
        }

        $id = $id_prefix . '-tab-' . $key;

        $query_arg[$active_key_str] = $key;
        $ajax_href                  = esc_url(add_query_arg($query_arg, $ajax_url));
        //开始构建
        if ('nav' == $type) {
            $is_active = $key == $active_key ? ' class="active"' : '';

            if ($is_mobile && $is_active && 1 !== $i) {
                $is_active = ' class="active lazyload" lazyload-action="ias"';
            }

            //nav按钮
            $name = $opt['title'] ? $opt['title'] : '栏目';
            $attr = !empty($opt['nav_attr']) ? ' ' . $opt['nav_attr'] : '';
            $attr .= ($route || !empty($opt['route'])) ? ' data-route="' . $ajax_href . '"' : '';

            $href_attr = ($route || !empty($opt['route'])) ? ' href="' . $ajax_href . '"' : ' href="javascript:;"';

            $html .= $is_swiper
                ? '<li class="swiper-slide"><a' . $attr . $href_attr . '  tab-id="' . $id . '">' . $name . '</a></li>'
                : '<li' . $is_active . '><a' . $attr . $href_attr . ' data-toggle="tab" data-ajax data-target="#' . $id . '">' . $name . '</a></li>';
        } else {
            $is_active = $key == $active_key ? ' in active' : '';

            $c_class = $is_swiper ? 'swiper-slide' : 'tab-pane fade' . $is_active;
            $c_class .= !empty($opt['content_class']) ? ' ' . $opt['content_class'] : '';

            $html .= '<div class="ajaxpager ' . $c_class . '" id="' . $id . '">';
            $_key = is_string($key) ? $key : 'other';

            if (!empty($opt['content'])) {
                $html .= $opt['content'];
            } elseif (!$is_active) {
                //只要不是选中页面，则都显示为AJAX
                $html .= '<span class="post_ajax_trigger hide"><a href="' . $ajax_href . '" class="ajax_load ajax-next ajax-open"></a></span>';
            } else {
                //第一页则直接显示内容
                $active_index = $i;
                $opt['index'] = $i;
                $html .= apply_filters('main_' . $id_prefix . '_tab_content_' . $_key, '', $opt);
            }

            $loader = isset($opt['loader']) ? $opt['loader'] : $placeholder_default;
            $html .= '<div class="post_ajax_loader" style="display: none;">' . $loader . '</div>';
            $html .= '</div>';
        }
        $i++;
    }

    if ('nav' == $type) {
        $html = apply_filters('main_' . $id_prefix . '_tab_nav_content', $html);
        $html = $is_swiper ? '<div class="swiper-tab-nav swiper-scroll tab-nav-theme" swiper-tab-nav="tab-' . $id_prefix . '" scroll-nogroup="true"><div class="swiper-wrapper">' . $html . '</div></div>' : '<ul class="list-inline scroll-x mini-scrollbar tab-nav-theme">' . $html . '</ul>';
    } else {
        $html = apply_filters('main_' . $id_prefix . '_tab_content_content', $html);
        $html = $is_swiper ? '<div class="swiper-tab" swiper-tab="tab-' . $id_prefix . '" active-index="' . ($active_index - 1) . '" active-key="' . $active_key . '"><div class="swiper-wrapper">' . $html . '</div></div>' : '<div class="tab-content main-tab-content">' . $html . '</div>';
    }

    return $html;
}

function zib_get_cfs_icon($val, $class = '')
{
    if (!$val) {
        return;
    }

    $class = $class ? ' ' . $class : '';
    if (stristr($val, 'zibsvg-')) {
        return zib_get_svg(str_replace('zibsvg-', '', $val), null, 'icon' . $class);
    }

    if (stristr($val, 'customsvg-')) {
        return str_replace('<svg ', '<svg class="icon' . $class . '"', str_replace('customsvg-', '', $val));
    }

    return '<i class="' . $val . $class . '" aria-hidden="true"></i>';
}

//获取ID
function zib_get_id_by_post_or_term($obj, $type = '')
{
    if (isset($obj->term_id)) {
        return $obj->term_id;
    }
    if (isset($obj->ID)) {
        return $obj->ID;
    }

    if ('post' === $type) {
        return zib_get_id_by_post_or_term(get_post($obj));
    }
    if ('term' === $type) {
        return zib_get_id_by_post_or_term(get_term($obj));
    }
    return false;
}

/**
 * @description: 根据ip地址获取地理位置
 * @param {*} $ip
 * @param {*} $format
 * @return {*}
 */
function zib_get_geographical_position_by_ip($ip)
{
    if (!$ip || strstr($ip, '0.0.0.') || strstr($ip, '192.168.') || strstr($ip, '127.0.')) {
        return false;
    }

    $sdk = _pz('ip_addr_sdk');

    if ($sdk === 'qq') {
        $ip_addr_sdk = _pz('ip_addr_sdk_qq');
        if (!empty($ip_addr_sdk['appkey'])) {
            return zib_get_geographical_position_by_qq($ip, $ip_addr_sdk['appkey'], $ip_addr_sdk['secretkey']);
        }
    }

    if ($sdk === 'amap') {
        $ip_addr_sdk = _pz('ip_addr_sdk_amap');
        if (!empty($ip_addr_sdk['appkey'])) {
            return zib_get_geographical_position_by_amap($ip, $ip_addr_sdk['appkey'], $ip_addr_sdk['secretkey']);
        }
    }

    if ($sdk === 'polling') {
        $qq_data   = array();
        $amap_data = array();
        $data      = array();

        //通过qq查询
        $ip_addr_sdk = _pz('ip_addr_sdk_qq');
        if (!empty($ip_addr_sdk['appkey'])) {
            $qq_data = zib_get_geographical_position_by_qq($ip, $ip_addr_sdk['appkey'], $ip_addr_sdk['secretkey']);
            if (!empty($qq_data['province'])) {
                return $qq_data;
            }
        }

        //通过高德查询
        $ip_addr_sdk = _pz('ip_addr_sdk_amap');
        if (!empty($ip_addr_sdk['appkey'])) {
            $amap_data = zib_get_geographical_position_by_amap($ip, $ip_addr_sdk['appkey'], $ip_addr_sdk['secretkey']);
            if (!empty($amap_data['province'])) {
                return $amap_data;
            }
        }

        //太平洋公共接口
        $data = zib_get_geographical_position_by_pconline($ip);
        if (!empty($data['province'])) {
            return $data;
        }

        //都没有身份，则进行对比国家
        if (!empty($qq_data['nation'])) {
            return $qq_data;
        }

        if (!empty($amap_data['nation'])) {
            return $amap_data;
        }

        if (!empty($data['nation'])) {
            return $data;
        }

        return false;
    }

    return zib_get_geographical_position_by_pconline($ip);
}

//获取nonce字段
function zib_nonce_field($action = -1, $name = '_wpnonce', $referer = false, $display = false)
{
    return wp_nonce_field($action, $name, $referer, $display);
}

/**
 * @description:
 * @param {*} $data
 * @param {*} $type 显示精度 province 省，city 市
 * @return {*}
 */
function zib_get_ip_geographical_position_badge($data, $type = 'province', $class = '')
{

    if (!$data) {
        return;
    }

    $nation      = !empty($data['nation']) ? $data['nation'] : ''; //国家
    $city        = !empty($data['city']) ? $data['city'] : $nation; //省
    $province    = !empty($data['province']) ? $data['province'] : ''; //市
    $replace_str = array('省', '市', '特别行政区');

    if ($type === 'city') {
        $text = $province === $city || !$province ? str_replace($replace_str, '', $city) : $province . $city;
    } else {
        //默认精准到市
        $text = str_replace($replace_str, '', $province);
        if (!$text && $city) {
            $text = str_replace($replace_str, '', $city);
        }
    }
    if (!$text) {
        return;
    }

    return '<span class="' . $class . '">' . $text . '</span>';
}

function zib_bbs_frontend_set_save($object_data, $type)
{

    $_pz_key = ['bbs_home_tab_active_index'];
    foreach ($_pz_key as $key) {
        if (isset($_POST[$key])) {
            _spz($key, $_POST[$key]);
        }
    }

    //判断文章类型
    if ($type === 'post') {
    }
}
add_action('zib_frontend_set_save', 'zib_bbs_frontend_set_save', 10, 2);

//添加单次任务，一秒后执行刷新固定链接
function zib_flush_rewrite_rules()
{
    //添加WordPress单次任务，时间为当前时间2秒后
    wp_schedule_single_event(time() + 1, 'task_flush_rewrite_rules');
}
add_action('task_flush_rewrite_rules', 'flush_rewrite_rules');

//加载一个错误页面
function zib_die_page($message = '', $args = array())
{
    header('Content-Type: text/html; charset=charset=UTF-8');

    $img = '<img src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/404.svg">';
    if (isset($args['img'])) {
        $img = $args['img'] ? '<img src="' . $args['img'] . '">' : '';
    }

    if (isset($args['title'])) {
        $GLOBALS['new_title'] = $args['title'];
        add_filter('echo_seo_title', '__return_true');
    }

    get_header();
    ?>
    <main class="container flex ac">
        <div class="f404 flex1">
            <?php echo $img . '<div class="f404-msg mt20">' . $message . '</div>'; ?>
        </div>
    </main>
<?php
get_footer();
    exit;
}
