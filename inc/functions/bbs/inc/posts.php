<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-21 16:48:09
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|帖子类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取排序方式的选项参数

function zib_bbs_get_posts_order_options()
{
    $args = array(
        'name'                => '标题名称',
        'date'                => '最新发布',
        'modified'            => '最近更新',
        'last_reply'          => '最新回复',
        'dynamic_score'       => '推荐指数',
        'views'               => '最多查看',
        'score'               => '评分最高',
        'comment_count'       => '最多回复',
        'favorite_count'      => '最多收藏',
        'zibpay_price'        => '售价金额',
        'zibpay_points_price' => '积分金额',
        'sales_volume'        => '销售数量',
        'rand'                => '随机',
    );

    return apply_filters('bbs_posts_order_options', $args);
}

function zib_bbs_get_posts_type_options()
{
    return array(
        ''         => __('标准', 'zibll'),
        'question' => __('提问', 'zibll'),
        //  'atlas'    => __('图集', 'zibll'),
        //  'video'    => __('视频', 'zibll'),
    );
}

function zib_bbs_get_posts_topping_options()
{
    $options = array(
        '0' => '不置顶',
        '1' => '置顶',
        '2' => '超级置顶',
        '3' => '全局置顶',
    );
    return $options;
}

/**
 * @description: 获取帖子列表的主要函数
 * @param {*} array $args
 * @return {*}
 */
function zib_bbs_get_posts_list($args = array())
{
    $defaults = array(
        'class'        => 'ajax-item',
        'show_plate'   => true,
        'show_topic'   => true,
        'show_topping' => false,
    );

    $args = wp_parse_args($args, $defaults);

    global $post;
    $class = $args['class'] ? ' ' . $args['class'] : '';

    //准备参数
    $posts_id = $post->ID;
    $title    = zib_bbs_get_posts_lists_title('forum-title flex ac', '', $args['show_topping']);

    //用户
    $user_box = zib_bbs_get_posts_author_card_box($post, 'mb10');

    //底部按钮
    $plate_btn = '';
    if ($args['show_plate']) {
        $plate_id  = zib_bbs_get_plate_id($posts_id);
        $plate_btn = zib_bbs_get_plate_but($plate_id, 'but but-plate text-ellipsis');
    }
    $plate_btn = $plate_btn ? $plate_btn : '<span class="plate-null"></span>';
    $icon_meta = zib_bbs_get_posts_action_metas($posts_id, 'action-meta flex0 flex jsb');

    //封面
    $cover   = zib_bbs_posts_is_can_viewed($post) ? zib_bbs_get_posts_lists_cover($post) : '';
    $content = zib_bbs_get_posts_lists_content($post, $args['show_topic'], !$cover);

    //详细
    $html = '<posts class="forum-posts detail' . $class . '">';
    $html .= $user_box;
    $html .= $cover;
    $html .= '<div>' . $title . '</div>';
    $html .= $content;
    $html .= '<div class="flex ac jsb mt10">';
    $html .= $plate_btn;
    $html .= $icon_meta;
    $html .= '</div>';

    $html .= '</posts>';
    return $html;
}

//获取帖子管理的列表
function zib_bbs_get_posts_manage_list($class = 'ajax-item', $show_plate = false)
{
    global $post;

    $title             = zib_bbs_get_posts_lists_title('forum-title', '');
    $author_id         = get_the_author_meta('ID');
    $display_name_link = zib_get_user_name($author_id);
    $avatar_html       = '<div class="mr20 forum-user">';
    $content           = zib_bbs_get_posts_lists_content($post);

    $plate_btn = '';
    if ($show_plate) {
        $plate_id  = zib_bbs_get_plate_id($post->ID);
        $plate_btn = '<div class="flex ac jsb mt10 mr20">' . zib_bbs_get_plate_but($plate_id, 'but but-plate text-ellipsis') . '</div>';
    }

    $avatar_html .= zib_get_avatar_box($author_id, 'avatar-img forum-avatar');
    $avatar_html .= '<span class="show-sm ml6 flex ac" style="width: 90%;">' . $display_name_link . '</span>';
    $avatar_html .= '</div>';

    $time      = zib_get_post_time_tooltip($post);
    $time      = '<span class="icon-circle">' . zib_get_post_time_tooltip($post) . '</span>';
    $dropdown  = zib_bbs_get_posts_more_dropdown($post->ID, 'pull-right ml10', 'padding-6 opacity8', zib_get_svg('menu_2'), 'down');
    $dropdown  = '<div class="">' . $dropdown . '</div>';
    $info_down = '<div class="flex ac jsb item-meta em09-sm hh">';
    $info_down .= $plate_btn;
    $info_down .= '<div class="flex ac mt10">' . zib_get_avatar_box($author_id, 'avatar-mini mr6') . $display_name_link . $time . '</div>';
    $info_down .= '</div>';

    $info_html = '<div class="grow1">';
    $info_html .= '<div class="flex jsb">' . $title . $dropdown . '</div>';
    $info_html .= $content;
    $info_html .= $info_down;
    $info_html .= '</div>';

    $class = $class ? ' ' . $class : '';

    $html = '<posts class="forum-posts mini flex manage' . $class . '">';
    $html .= $info_html;
    $html .= '</posts>';

    return $html;
}

/**
 * @description: 获取帖子的幻灯片封面
 * @param {*} $show_breadcrumbs
 * @return {*}
 */
function zib_bbs_get_posts_slide_cover($post_id, $show_breadcrumbs = false)
{

    $cover       = '';
    $slides_imgs = explode(',', zib_get_post_meta($post_id, 'featured_slide', true));
    if (!empty($slides_imgs[0])) {
        $breadcrumbs = '';
        if ($show_breadcrumbs) {
            $breadcrumbs = zib_bbs_get_breadcrumbs();
            add_filter('single_show_breadcrumbs', '__return_false');
        }

        $slides_args = array(
            'class'        => 'imgbox-container',
            'scale_height' => true,
            'scale'        => (int) _pz('bbs_posts_cover_opt', 45, 'slide_ratio') ?: 45,
            'lazy'         => zib_is_lazy('lazy_bbs_list_thumb'),
            'button'       => true,
            'spacebetween' => 20,
        );

        $slides_args['html'] = $breadcrumbs ? '<div class="abs-center left-bottom single-cover-con">' . $breadcrumbs . '</div>' : '';
        $slides_args['lazy'] = zib_is_lazy('lazy_bbs_list_thumb', true);

        foreach ($slides_imgs as $slides_img) {
            $background = zib_get_attachment_image_src((int) $slides_img, 'full');
            $slide      = array(
                'background' => isset($background[0]) ? $background[0] : '',
            );
            $slides_args['slides'][] = $slide;
        }
        $cover = zib_new_slider($slides_args, false);
    }

    return $cover;
}

/**
 * @description: 获取帖子的图片封面
 * @param {*} $show_breadcrumbs
 * @return {*}
 */
function zib_bbs_get_posts_image_cover($post_id, $show_breadcrumbs = false)
{

    $cover = '';
    $image = zib_get_post_meta($post_id, 'cover_image', true);
    if ($image) {
        $title       = get_the_title($post_id) . zib_get_delimiter_blog_name();
        $breadcrumbs = '';
        if ($show_breadcrumbs) {
            $breadcrumbs = zib_bbs_get_breadcrumbs();
            add_filter('single_show_breadcrumbs', '__return_false');
            $breadcrumbs = $breadcrumbs ? '<div class="abs-center left-bottom single-cover-con">' . $breadcrumbs . '</div>' : '';
        }
        $scale_height = (int) _pz('bbs_posts_cover_opt', 45, 'image_ratio') ?: 45;
        $src          = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
        $img          = '<img ' . zib_get_lazy_attr('lazy_bbs_list_thumb', $image, 'fit-cover', $src) . ' alt="' . strip_tags($title) . '">';
        $cover        = '<div class="graphic imgbox-container" style="padding-bottom:' . $scale_height . '%">' . $img . $breadcrumbs . '</div>';
    }

    return $cover;
}

/**
 * @description: 版块页面构建帖子列表的常规样式
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_posts_mini_list($class = 'ajax-item', $show_topping = false)
{
    //准备必要参数
    $class = $class ? ' ' . $class : '';
    global $post;

    $title             = zib_bbs_get_posts_lists_title('forum-title flex ac', 'text-ellipsis', $show_topping, true);
    $author_id         = get_the_author_meta('ID');
    $display_name_link = zib_get_user_name($author_id);
    $avatar_html       = '<div class="mr20 forum-user">';
    $avatar_html .= zib_get_avatar_box($author_id, 'avatar-img forum-avatar');
    $avatar_html .= '<span class="show-sm ml6 flex ac" style="width: 90%;">' . $display_name_link . '</span>';
    $avatar_html .= '</div>';

    $info_top = '';
    $info_top .= $title;

    $last_reply       = get_post_meta($post->ID, 'last_reply', true);
    $get_the_time     = get_the_time('Y-m-d H:i:s');
    $get_the_time_ago = zib_get_time_ago($get_the_time);

    if ($last_reply) {
        $time = '<span class="icon-circle" title="最后回复：' . $last_reply . '">' . zib_get_time_ago($last_reply) . '回复</span>';
    } else {

        $time = '<span class="icon-circle" title="发布时间：' . $get_the_time . '">' . $get_the_time_ago . '发布</span>';
    }

    if ('publish' !== $post->post_status) {
        $icon_meta = zib_bbs_get_posts_more_dropdown($post->ID, 'pull-right mrn10', 'padding-10 opacity8');
    } else {
        $icon_meta = zib_bbs_get_posts_icon_metas();
    }

    $info_down = '<div class="flex ac jsb item-meta">';

    $info_down .= '<div class="meta-left em09-sm flex">';
    $info_down .= '<span class="hide-sm">' . $display_name_link . '</span>';
    $info_down .= $time;
    $info_down .= '</div>';

    $info_down .= '<div class="meta-right">' . $icon_meta . '</div>';
    $info_down .= '</div>';

    $info_html = '<div class="entry-info">';
    $info_html .= $info_top;
    $info_html .= $info_down;
    $info_html .= '</div>';

    $html = '<posts class="forum-posts mini' . $class . '">';
    $html .= $avatar_html;
    $html .= $info_html;

    $html .= '</posts>';

    return $html;
}

//获取帖子在列表中的封面
function zib_bbs_get_posts_lists_cover($post)
{
    $cover = '';
    if (_pz('bbs_posts_cover_opt', true, 'lists_video_s')) {
        //视频封面
        $video = zib_get_post_meta($post->ID, 'featured_video', true);
        if ($video) {
            $scale_height = _pz('bbs_posts_cover_opt', 56, 'video_ratio');

            $pic_url  = zib_bbs_get_video_cover_pic($post);
            $pic_html = '<div class="thumb-video-placeholder flex jc"><i aria-hidden="true" class="fa fa-play-circle opacity5 em3x"></i></div>'; //占位符
            if ($pic_url) {
                $src      = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg';
                $pic_html = '<img class="lazyload fit-cover" ' . (zib_is_lazy('lazy_bbs_list_thumb', true) ? 'src="' . $src . '" data-src="' . $pic_url . '"' : 'src="' . $pic_url . '"') . ' alt="' . esc_attr(strip_tags(get_the_title($post))) . '">';
            }

            //   $cover        = zib_get_dplayer($video, $pic_url, $scale_height);
            $mute_attr = _pz('bbs_posts_cover_opt', true, 'lists_video_mute_s') ? ' data-volume="none"' : ' data-volume="100"';
            $cover     = '<div class="forum-thumb-video" style="--scale-height:' . $scale_height . '%;"><div class="video-thumb-box" video-url="' . esc_url($video) . '"' . $mute_attr . '><div class="img-thumb">' . $pic_html . '</div><div class="video-thumb"></div></div><div class="abs-center right-top"><i class="fa fa-play-circle em14 mt6 c-white opacity8" aria-hidden="true"></i></div></div>';
        }
    }

    $cover = $cover ?: zib_bbs_get_posts_slide_cover($post->ID); //幻灯片
    $cover = $cover ?: zib_bbs_get_posts_image_cover($post->ID); //图片

    return $cover ? '<div class="forum-lists-cover mb10">' . $cover . '</div>' : '';
}

function zib_bbs_get_video_cover_pic($post)
{

    $pic_url = zib_get_post_meta($post->ID, 'cover_image', true);

    if ($pic_url) {
        return $pic_url;
    }

    $spare_pic = _pz('bbs_posts_cover_opt', true, 'video_spare_pic');
    if (empty($spare_pic[0]['img'])) {
        return '';
    }

    $spare_pic_img = array_column($spare_pic, 'img');

    return $spare_pic_img[array_rand($spare_pic_img, 1)];
}

//获取文章列表的内容
function zib_bbs_get_posts_lists_content($post = null, $show_topic = true, $show_img = true)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    $posts_id        = $post->ID;
    $topic_link_icon = zib_get_svg('topic'); //话题按钮
    $topic_link      = $show_topic ? zib_bbs_get_posts_topic_link($posts_id, 'focus-color mr3', $topic_link_icon, $topic_link_icon, 1) : '';

    //如果内容被隐藏
    $is_hide = zib_bbs_get_posts_not_allow_view($post, _pz('bbs_post_lists_hide_pay_s'));
    if ($is_hide) {
        $topic_link = '<div class="content mt6">' . $topic_link . '</div>';
        return $topic_link . $is_hide;
    }

    //图像
    $imgs_html = '';
    if ($show_img) {
        $imgs_max = 4;
        $imgs     = zib_bbs_get_posts_lists_img($post, $imgs_max); //图像s
        if ($imgs) {
            $imgs_count = zib_get_post_imgs_count($post);
            $imgs_html  = '<div class="imgbox-container lists-imgs mt6 count-' . ($imgs_count <= $imgs_max ? $imgs_count : $imgs_max) . '">' . $imgs . '</div>';
        }
    }

    $excerpt = zib_bbs_get_posts_lists_excerpt($post, 'excerpt', ($imgs_html || !$show_img ? 60 : 120));
    $vote    = zib_bbs_get_vote($posts_id, 'mt10');
    $comment = zib_bbs_get_posts_lists_hot_comment($posts_id, 'mt10');

    $html = '<div>';
    $html .= '<div class="content mt6">';
    $html .= $topic_link;
    $html .= $excerpt;
    $html .= '</div>';
    $html .= $imgs_html;
    $html .= $vote;
    $html .= $comment;
    $html .= '</div>';

    return $html;
}

//获取内容阅读限制的图标按钮
function zib_bbs_get_posts_allow_view_btn($post = 0, $class = '', $not_show = true)
{

    if (!is_object($post)) {
        $post = get_post($post);
    }
    global $zib_bbs, $wp_query;
    if ($wp_query->get('post_type') !== 'plate') {
        //首先判断帖子的阅读权限
        $data      = zib_bbs_get_allow_view_data($post);
        $type_name = $zib_bbs->posts_name;

        if (!$data['open'] || !$data['html'] || ($not_show && !$data['allow_reason'])) {
            //文章没有限制,再判断板块限制
            $plate_id  = zib_bbs_get_plate_id($post->ID);
            $data      = zib_bbs_get_allow_view_data(get_post($plate_id));
            $type_name = $zib_bbs->plate_name;
        }

    } else {
        //当前为板块页面
        $data      = zib_bbs_get_allow_view_data($post);
        $type_name = $zib_bbs->posts_name;
    }

    if (!$data['open'] || !$data['html'] || ($not_show && !$data['allow_reason'])) {
        return;
    }

    $text = !empty($data['btn_icon']) ? $data['btn_icon'] : '<span class="badg badg-sm c-yellow"><i class="fa fa-unlock-alt"></i></span>';
    $con  = zib_str_remove_lazy($data['html']);

    $con .= $data['allow_reason'] ? '<div class="c-blue em09 mt10 text-center">' . $data['allow_reason'] . '</div>' : '';
    $title = '<i class="fa fa-unlock-alt mr6"></i>' . $type_name . '阅读限制';

    return '<span class="' . $class . '"  data-html="1" title="' . esc_attr($title) . '" data-content="' . esc_attr($con) . '" data-trigger="hover" data-placement="auto top" data-container="body" data-toggle="popover">' . $text . '</span>';
}

//判断帖子是否允许查看
function zib_bbs_posts_is_can_viewed($post)
{
    static $static_data = null;

    if (isset($static_data[$post->ID])) {
        return $static_data[$post->ID];
    }

    $static_data[$post->ID] = zib_bbs_get_posts_not_allow_view($post) ? false : true;
    return $static_data[$post->ID];
}

//获取禁止查看的文案，可以作为判断函数
function zib_bbs_get_posts_not_allow_view($post = null, $hide_pay = false)
{

    //首先判断版块的阅读权限
    $plate_id   = zib_bbs_get_plate_id($post->ID);
    $plate_data = zib_bbs_get_allow_view_data(get_post($plate_id));

    if ($plate_data['open'] && !$plate_data['allow_reason']) {
        return $plate_data['not_html'];
    }

    //再判断帖子的阅读权限
    $data           = zib_bbs_get_allow_view_data($post);
    $not_allow_view = ($data['open'] && !$data['allow_reason'] ? $data['not_html'] : '');

    if ($not_allow_view && $hide_pay && in_array($data['type'], array('pay', 'points'))) {
        $not_allow_view = '<div class="hide-post mt6"><div class=""><i class="fa fa-unlock-alt mr6"></i>此处内容已隐藏</div></div>';
    }

    return $not_allow_view;
}

function zib_bbs_get_posts_allow_view_options()
{
    $allow_view_types = array(
        ''        => '公开',
        'signin'  => '登录后可查看',
        'comment' => '评论后可查看',
        'roles'   => '部分用户可查看',
        'pay'     => '付费查看',
        'points'  => '支付积分查看',
    );
    return $allow_view_types;
}

//获取帖子付费盒子
function zib_bbs_get_posts_pay_box($posts_id)
{
    $pay_mate = get_post_meta($posts_id, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        return;
    }

    $cuont        = ''; //销售数量
    $user_id      = get_current_user_id();
    $cuont_volume = zibpay_get_sales_volume($pay_mate, $posts_id);
    if (_pz('pay_show_paycount', true) && $cuont_volume) {
        $cuont = '<badge class="img-badge hot jb-green px12 mt6">已售 ' . $cuont_volume . '</badge>';
    }
    $price           = zibpay_get_show_price($pay_mate, $posts_id, 'c-red'); //价格
    $vip_price       = zibpay_get_posts_vip_price($pay_mate);
    $pay_doc         = !empty($pay_mate['pay_doc']) ? $pay_mate['pay_doc'] : '此内容为' . zibpay_get_pay_type_name($pay_mate['pay_type']) . '，请付费后查看'; //简介
    $pay_button      = !$user_id ? '<a href="javascript:;" class="but jb-blue signin-loader padding-lg"><i class="fa fa-sign-in mr10" aria-hidden="true"></i>登录购买</a>' : zibpay_get_pay_form_but($pay_mate, $posts_id); //购买按钮
    $order_type_name = zibpay_get_pay_type_name($pay_mate['pay_type'], true);
    $order_type_name = '<div class="pay-tag abs-center">' . $order_type_name . '</div>';

    $order_type_class = 'order-type-' . $pay_mate['pay_type'];
    $html             = '<div class="zib-widget pay-box posts-paybox ' . $order_type_class . '" id="posts-pay">';
    $html .= $order_type_name;
    $html .= $cuont;
    $html .= '<div class="flex ab jsb hh"><div class="mt10 flex-auto">' . $price . $vip_price . '</div><div class="mt10 shrink0">' . $pay_button . '</div></div>';
    $html .= '<div class="pay-doc">' . $pay_doc . '</div>';
    $html .= '</div>';
    return $html;
}

//获取版块或者帖子的限制阅读的数据
function zib_bbs_get_allow_view_data($post = null)
{

    global $post_allow_view_data, $zib_bbs; //原因
    $data = array(
        'allow'        => true,
        'open'         => false,
        'type'         => '',
        'not_html'     => '',
        'allow_reason' => '',
        'html'         => '',
    );
    $posts_id = $post->ID;

    if (!$posts_id) {
        return $data;
    }

    if (isset($post_allow_view_data[$posts_id])) {
        return $post_allow_view_data[$posts_id];
    }

    $allow_view = get_post_meta($posts_id, 'allow_view', true);
    if (!$allow_view) {
        return $data;
    }
    $data['type'] = $allow_view;
    $data['open'] = true;

    $user_id = get_current_user_id();

    if (is_super_admin() || zib_bbs_user_is_forum_admin()) {
        //管理员可查看
        $data['allow_reason'] = '您是尊贵的管理员，可查看所有内容';
    }

    if ($user_id && $post->post_author == $user_id && !$data['allow_reason']) {
        //作者就是自己
        $data['allow_reason'] = '您是内容作者，可查看此内容';
    }

    //分区版主和版主
    $moderator_badge = zib_bbs_get_user_moderator_badge($user_id, $post);
    if (!$data['allow_reason'] && $moderator_badge) {
        $data['allow_reason'] = '您是' . $moderator_badge . '，可查看此内容';
    }

    $sign_btns = '<p><a href="javascript:;" class="signin-loader but jb-blue padding-lg"><i class="fa fa-fw fa-sign-in" aria-hidden="true"></i>登录</a>' . (!zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but jb-yellow padding-lg">' . zib_get_svg('signup') . '注册</a>' : '') . '</p>';
    $con       = '';
    $title     = '';

    switch ($allow_view) {
        case 'signin':
            $data['html'] = '<p class="separator muted-3-color em09">登录后可查看</p>';
            if (!$user_id) {
                $title = '内容已隐藏，请登录后查看';
                $con   = '<div class="text-center em09 mt20"><p class="separator muted-3-color mb20">登录后继续查看</p>' . $sign_btns . '</div>';
            } elseif (!$data['allow_reason']) {
                $data['allow_reason'] = '您已登录，可查看此内容';
            }
            break;

        case 'follow':
            $data['html'] = '<p class="separator muted-3-color em09">' . $zib_bbs->plate_follow_name . '后可查看</p>';
            if (!zib_bbs_is_followed_plate($posts_id, $user_id)) {
                $title = '内容已隐藏，' . $zib_bbs->plate_follow_name . '后可查看';

                if (!$user_id) {
                    $con = '<div class="text-center em09 mt20"><p class="separator muted-3-color mb20">请先登录</p>' . $sign_btns . '</div>';
                } else {
                    $follow_btn = zib_bbs_get_all_plate_follow_btn($posts_id, 'but hollow c-red padding-lg', zib_get_svg('add') . ' 立即' . $zib_bbs->plate_follow_name);
                    $con        = '<div class="text-center em09 mt20"><p class="separator muted-3-color mb20">' . $zib_bbs->plate_follow_name . '后继续查看</p>' . $follow_btn . '</div>';
                }

            } elseif (!$data['allow_reason']) {
                $data['allow_reason'] = '您已' . $zib_bbs->plate_follow_name . '，可查看此内容';
            }
            break;

        case 'pay':
        case 'points':
            $pay_mate = get_post_meta($posts_id, 'posts_zibpay', true);
            if (!empty($pay_mate['pay_type']) && 'no' !== $pay_mate['pay_type']) {
                $paid             = zibpay_is_paid($posts_id, $user_id);
                $data['html']     = '<p class="separator muted-3-color em09">付费内容，需' . ($allow_view === 'points' ? '支付积分' : '购买') . '后可查看</p>';
                $data['btn_icon'] = zibpay_get_post_mini_badge($pay_mate);

                if (!$paid) {
                    $pay_hide_part = zib_get_post_meta($post->ID, 'pay_hide_part', true);
                    $title         = $pay_hide_part ? '部分' : '';
                    $title .= '内容已隐藏';
                    $con = zib_bbs_get_posts_pay_box($posts_id);
                    $con = '<div class="text-center em09 mt20"><p class="separator muted-3-color mb20">登录后继续查看</p>' . $sign_btns . '</div>';
                    $con = zib_bbs_get_posts_pay_box($posts_id);
                } elseif (!$data['allow_reason']) {
                    $paid_type = $paid['paid_type'];
                    switch ($paid_type) {
                        case 'free':
                            $data['allow_reason'] = '此内容可免费查看';
                            break;
                        case 'vip1_free':
                        case 'vip2_free':
                            $data['allow_reason'] = '您是尊贵的' . zibpay_get_vip_icon($paid['vip_level'], 'em12') . _pz('pay_user_vip_' . $paid['vip_level'] . '_name') . '，可免费查看此内容';
                            break;
                        default:
                            $data['allow_reason'] = '您已购买此付费内容';
                    }
                }
            }
            break;

        case 'comment':
            $data['html'] = '<p class="separator muted-3-color em09">评论后可查看</p>';
            if (!$user_id) {
                $title = '内容已隐藏，请评论后查看';
                $con   = '<div class="text-center em09 mt20"><p class="separator muted-3-color mb20">登录后继续评论</p>' . $sign_btns . '</div>';
            } elseif (!zib_user_is_commented()) {
                $title = '内容已隐藏，请评论后查看';
                if (is_singular('forum_post')) {
                    $comment_href = 'javascript:(scrollTopTo(\'#respond\',-50));';
                } else {
                    $comment_href = get_comments_link($posts_id);
                }

                $con = '<div class="text-center em09 mt20" reply-show="true" reload-hash="#article"><p class="separator muted-3-color mb20">评论后继续查看</p><p><a rel="nofollow" href="' . $comment_href . '" class="but jb-blue padding-lg">' . zib_get_svg('comment') . '去评论</a></p></div>';
            } elseif (!$data['allow_reason']) {
                $data['allow_reason'] = '您已参与评论，可查看此内容';
            }

            break;

        case 'roles':
            $allow_roles = (array) get_post_meta($posts_id, 'allow_view_roles', true);
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
                $title = '内容已隐藏';

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
                $is_allow = false;
                if (!empty($allow_roles['vip'])) {
                    //会员判断
                    $my_vip = zib_get_user_vip_level($user_id);
                    if ($my_vip && $my_vip >= $allow_roles['vip']) {
                        if (!$data['allow_reason']) {
                            $data['allow_reason'] = '您是尊贵的' . zibpay_get_vip_icon($my_vip, 'em12') . _pz('pay_user_vip_' . $my_vip . '_name') . '，可查看此内容';
                        }
                        $is_allow = true;
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
                        $is_allow = true;
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
                        $is_allow = true;
                    } else {
                        $auth = $auth ? '<a rel="nofollow" class="but mm3" href="' . zib_get_user_center_url('auth') . '">' . $auth . '</a>' : '';
                    }
                }

                if (!$is_allow) {
                    $title = '内容已隐藏';
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

        if ($title) {
            $title = ('plate' === $post->post_type ? '该' . $zib_bbs->plate_name : '该' . $zib_bbs->posts_name) . $title;
        }

        $data['not_html'] = '<div class="hide-post mt6">';
        $data['not_html'] .= '<div class=""><i class="fa fa-unlock-alt mr6"></i>' . $title . '</div>';
        $data['not_html'] .= $con;
        $data['not_html'] .= '</div>';
    }
    return $post_allow_view_data[$posts_id] = $data;
}

/**
 * @description: 获取列表的简介
 * @param {*} $post
 * @param {*} $class
 * @param {*} $limit
 * @return {*}
 */
function zib_bbs_get_posts_lists_excerpt($post = null, $class = 'excerpt', $limit = 120)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $post_status  = $post->post_status;
    $permalink    = $post_status === 'trash' ? 'javascript:;' : get_permalink($post);
    $target_blank = _pz('posts_target_blank') && $post_status !== 'trash' ? ' target="_blank"' : '';

    $content = strip_tags(strip_shortcodes($post->post_content));
    //替换多余空格
    $content = trim(preg_replace("/\s(?=\s)/", '', str_replace(array("\r", '&nbsp;', '　'), ' ', $content)));
    //内容裁剪
    $content = zib_str_cut($content, 0, $limit);

    //处理换行
    $content_array = explode("\n", $content, 5);

    //最多保留4行
    $new = '';
    if (is_array($content_array)) {
        $i = 0;
        foreach ($content_array as $con) {
            $_con = trim($con);
            if (zib_new_strlen($_con)) {
                $new .= $_con . '<br>';
                $i++;
            }
            if ($i >= 4) {
                break;
            }
        }
    }

    $new     = trim($new, '<br>');
    $content = $new ? '<a' . $target_blank . ' href="' . $permalink . '" class="' . $class . '">' . $new . '</a>' : '';
    return $content;
}

/**
 * @description: 文章和列表的用户卡片
 * @param {*} $post
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_posts_author_card_box($post, $class = '')
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    $posts_id    = $post->ID;
    $time_html   = zib_get_post_time_tooltip($post);
    $views       = _cut_count(get_post_meta($posts_id, 'views', true)); //查看数量
    $author_desc = $time_html . '<span class="icon-spot">' . $views . '次阅读</span>'; //查看数量
    $allow_view  = zib_bbs_get_posts_allow_view_btn($post, 'muted-2-color mr6'); //限制阅读
    if ($allow_view) {
        $author_desc = $allow_view . $author_desc;
    }

    $author_id = isset($post->post_author) ? $post->post_author : 0;
    $user_box  = zib_get_post_user_box($author_id, $author_desc, $class);

    return $user_box;
}

/**
 * @description: 获取版块链接按钮
 * @param {*} $plate_id
 * @param {*} $class
 * @param {*} $after
 * @return {*}
 */
function zib_bbs_get_plate_but($plate_id, $class = 'but but-plate', $after = '<i class="fa fa-angle-right ml6"></i>')
{
    if (!$plate_id || get_queried_object_id() == $plate_id) {
        return;
    }

    $class = $class ? ' class="' . $class . '"' : '';

    $title     = get_the_title($plate_id);
    $permalink = get_permalink($plate_id);
    $icon      = zib_get_svg('plate-fill');

    $link = '<a' . $class . ' href="' . $permalink . '">' . $icon . $title . $after . '</a>';
    return $link;
}

/**
 * @description: 获取新建帖子链接按钮
 * @param {*} $args
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_posts_add_page_link($args = array(), $class = 'but c-blue', $con = '发布')
{
    if (get_query_var('forum_post_edit')) {
        return;
    }

    $defaults = array(
        'id'       => 'add',
        'plate_id' => 0,
        'tag_id'   => 0,
        'bbs_type' => '',
    );

    $args      = wp_parse_args($args, $defaults);
    $class     = $class ? ' class="' . $class . '"' : '';
    $url       = zib_bbs_get_posts_edit_url($args['id']);
    $add_query = array();

    if (!(int) $args['id']) {
        unset($args['id']);
        foreach ($args as $k => $v) {
            if ($v) {
                $add_query[$k] = $v;
            }
        }
    }

    if ($add_query) {
        $url = add_query_arg($add_query, $url);
    }

    $link = '<a rel="nofollow"' . $class . ' href="' . $url . '">' . $con . '</a>';
    return $link;
}

/**
 * @description: 获取编辑帖子链接按钮
 * @param {*} $plate_id
 * @param {*} $class
 * @param {*} $after
 * @return {*}
 */
function zib_bbs_get_posts_edit_page_link($id = 0, $class = 'but c-blue', $con = '发布')
{
    if (!zib_bbs_current_user_can('posts_edit', $id)) {
        return;
    }
    return zib_bbs_get_posts_add_page_link(array('id' => $id), $class, $con);
}

/**
 * @description: 获取帖子设置为精华的按钮
 * @param {*} $plate_id
 * @param {*} $cat_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_posts_essence_set_link($posts_id = 0, $class = '', $cancel_class = '', $con = '设为精华', $cancel_con = '取消精华')
{

    if (!$posts_id || !zib_bbs_current_user_can('posts_essence_set', $posts_id)) {
        return;
    }

    $url_var = array(
        'action'   => 'posts_essence_set',
        'id'       => $posts_id,
        '_wpnonce' => wp_create_nonce('posts_essence_set'),
    );

    $meta = get_post_meta($posts_id, 'essence', true);

    if ($meta) {
        $class = $cancel_class;
        $class .= ' active';
        $con               = $cancel_con;
        $url_var['cancel'] = '1';
    }
    $ajax = add_query_arg($url_var, admin_url('admin-ajax.php'));
    $class .= ' essence-set wp-ajax-submit';

    return '<a class="' . $class . '" href="javascript:;" ajax-href="' . $ajax . '">' . $con . '</a>';
}

/**
 * @description: 获取帖子设置为置顶的按钮或者取消置顶
 * @param {*} $plate_id
 * @param {*} $cat_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_posts_topping_set_link($posts_id = 0, $class = '', $con = '设置置顶', $tag = 'a')
{

    if (!$posts_id || !zib_bbs_current_user_can('posts_topping_set', $posts_id)) {
        return;
    }

    $class .= ' topping-set';

    $url_var = array(
        'action' => 'posts_topping_set_modal',
        'id'     => $posts_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 320,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取帖子关闭评论的链接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $con
 * @param {*} $tag
 * @return {*}
 */
function zib_bbs_get_posts_comment_close_link($posts_id = 0, $class = '', $con = '关闭评论', $open_con = '开启评论', $tag = 'a')
{
    if (!$posts_id || !zib_bbs_current_user_can('comment_close', $posts_id)) {
        return;
    }

    $comments_open = comments_open($posts_id);
    $text          = $comments_open ? $con : $open_con;

    $class .= ' comment-close-set';

    $url_var = array(
        'action' => 'posts_comment_close_modal',
        'id'     => $posts_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 246,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取帖子设置阅读权限的链接按钮
 * @param {*} $plate_id
 * @param {*} $cat_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_posts_allow_view_set_link($posts_id = 0, $class = '', $con = '设置置顶', $tag = 'a')
{

    if (!$posts_id || !zib_bbs_current_user_can('posts_allow_view_edit', $posts_id)) {
        return;
    }

    $class .= ' allow-view-set';

    $url_var = array(
        'action' => 'posts_allow_view_set_modal',
        'id'     => $posts_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 268,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取审核帖子的链接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $con
 * @param {*} $tag
 * @return {*} zib_get_refresh_modal_link()
 */
function zib_bbs_get_posts_audit_link($posts_id = 0, $class = '', $con = '审核内容', $revoke_con = '审核驳回', $tag = 'a')
{
    if (!$posts_id || !zib_bbs_current_user_can('posts_audit', $posts_id)) {
        return;
    }

    if (!zib_bbs_current_user_can('posts_audit', $posts_id)) {
        return;
    }

    if (!is_object($posts_id)) {
        $get_post = get_post($posts_id);
    }

    if ('trash' === $get_post->post_status) {
        return;
    }

    if ('pending' !== $get_post->post_status) {
        $con = $revoke_con;
        $class .= 'c-yellow';
    } else {
        $class .= 'c-blue';
    }

    $icon    = zib_get_svg('approve', null, 'icon fa-fw mr6');
    $class   = 'posts-audit ' . $class;
    $url_var = array(
        'action' => 'posts_audit_modal',
        'id'     => $posts_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $icon . $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取删除帖子的连接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $con
 * @param {*} $tag
 * @return {*} zib_get_refresh_modal_link()
 */
function zib_bbs_get_posts_delete_link($posts_id = 0, $class = '', $con = '<i class="fa fa-trash-o fa-fw"></i>删除', $revoke_con = '<i class="fa fa-trash-o fa-fw mr6"></i>撤销删除', $tag = 'a')
{

    if (!$posts_id || !zib_bbs_current_user_can('posts_delete', $posts_id)) {
        return;
    }
    if (!is_object($posts_id)) {
        $get_post = get_post($posts_id);
    }
    $class = 'posts-delete ' . $class;

    if (empty($get_post->post_status) || 'trash' === $get_post->post_status) {
        if (!zib_bbs_current_user_can('posts_edit', $posts_id)) {
            return;
        }

        $class .= ' wp-ajax-submit';

        $url_var = array(
            'action'   => 'posts_delete_revoke',
            'id'       => $posts_id,
            '_wpnonce' => wp_create_nonce('posts_delete_revoke'),
        );

        $ajax = add_query_arg($url_var, admin_url('admin-ajax.php'));
        return '<' . $tag . ' class="' . $class . '" href="javascript:;" ajax-href="' . $ajax . '">' . $revoke_con . '</' . $tag . '>';
    }

    $url_var = array(
        'action' => 'posts_delete_modal',
        'id'     => $posts_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 260,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取帖子移动版块的连接按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $con
 * @param {*} $tag
 * @return {*} zib_get_refresh_modal_link()
 */
function zib_bbs_get_posts_plate_move_link($posts_id = 0, $class = '', $con = '<i class="fa fa-trash-o fa-fw"></i>移动版块', $tag = 'a')
{

    if (!$posts_id || !zib_bbs_current_user_can('posts_plate_move', $posts_id)) {
        return;
    }

    $class = 'posts-plate-move ' . $class;

    $url_var = array(
        'action' => 'posts_plate_move_modal',
        'id'     => $posts_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 306,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取文章内的图片
 * @param {*}
 * @return {*}
 */
function zib_bbs_get_posts_lists_img($post = null, $img_max = 4)
{

    if (!is_object($post)) {
        $post = get_post($post);
    }

    $cache_html = wp_cache_get($post->ID, 'post_multi_thumbnail', true);
    if (false === $cache_html) {
        $class = 'fit-cover radius8' . (_pz('imagelightbox') ? ' cursor-zoom-in' : '');
        $html  = zib_get_post_imgs($post, _pz('bbs_thumb_size'), $class, $img_max, true);
        if (zib_is_lazy('lazy_bbs_list_thumb')) {
            $html = str_replace(' src=', ' src="' . zib_get_lazy_thumb() . '" data-src=', $html);
            $html = str_replace(' class="', ' class="lazyload ', $html);
        }
        wp_cache_set($post->ID, $html, 'post_multi_thumbnail');
    } else {
        $html = $cache_html;
    }
    return $html;
}

/**
 * @description: 获取帖子的计数meta
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_posts_icon_metas($posts_id = 0)
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    $views      = get_post_meta($posts_id, 'views', true); //查看
    $score      = get_post_meta($posts_id, 'score', true); //评分
    $reply      = get_comments_number($posts_id); //回复
    $allow_view = zib_bbs_get_posts_allow_view_btn($posts_id, 'muted-2-color', false); //限制阅读

    $html = '';
    $html .= $allow_view ? '<item>' . $allow_view . '</item>' : '';
    $html .= '<item>' . zib_get_svg('view') . _cut_count($views) . '</item>';
    $html .= '<item>' . zib_get_svg('comment') . _cut_count($reply) . '</item>';
    $html .= '<item>' . zib_get_svg('extra-points') . _cut_count($score) . '</item>';

    return $html;
}

function zib_bbs_get_posts_action_metas($posts_id = 0, $main_class = 'action-meta')
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    $user_id = get_current_user_id();

    $html = '';
    //评分按钮
    $score       = get_post_meta($posts_id, 'score', true); //评分
    $score       = $score ? _cut_count($score) : '加分';
    $score_lists = zib_get_post_meta($posts_id, 'score_detail', true); //评分明细
    $class       = '';
    $action_attr = '';
    if ($user_id) {
        if (is_array($score_lists) && isset($score_lists[$user_id])) {
            $class = 'active';
        }
        $action_attr = ' ajax-action="score_extra"';
    } else {
        $class = 'signin-loader';
    }
    //  $html .= '<a href="javascript:;"' . $action_attr . ' class="item btn-score ' . $class . '" data-id="' . $posts_id . '">' . zib_get_svg('extra-points') . '<text>' . $score . '</text></a>';

    $score_btns = zib_bbs_get_score_box($posts_id, 'item', false, true); //评分

    $html .= $score_btns;

    //回复(评论)按钮
    $reply        = get_comments_number($posts_id); //回复
    $reply        = $reply ? _cut_count($reply) : '回复';
    $comment_href = get_comments_link();
    $html .= '<a rel="nofollow" href="' . $comment_href . '" class="item">' . zib_get_svg('comment') . '<text>' . $reply . '</text></a>';

    //分享按钮
    $share_btn = zib_bbs_get_posts_share_btn($posts_id, 'item', true);

    $html .= $share_btn;

    return '<div class="' . $main_class . '">' . $html . '</div>';
}

function zib_bbs_get_score_btns()
{}

function zib_bbs_get_posts_share_btn($post, $class = '', $modal = false)
{
    return zib_get_post_share_btn($post, 'btn-share ' . $class, $modal);
}

/**
 * @description: 获取文章收藏按钮
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $text
 * @param {*} $ok_text
 * @return {*}
 */
function zib_bbs_get_posts_favorite_btn($posts_id, $class = '', $text = '收藏', $ok_text = '收藏', $icon = true, $count = true)
{

    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    $user_id = get_current_user_id();
    if (zib_is_my_meta_ed('favorite_forum_posts', $posts_id)) {
        $class .= ' active';
        $text = $ok_text;
    }
    $action = ' ajax-action="favorite_posts"';
    $icon   = $icon ? zib_get_svg('favorite') : '';

    if ($count) {
        $count = get_post_meta($posts_id, 'favorite_count', true);
        $text .= $count ? '<count class="ml3">' . $count . '</count>' : '';
    }
    if (!$user_id) {
        $action = '';
        $class .= ' signin-loader';
    }
    return '<a href="javascript:;"' . $action . ' class="btn-favorite ' . $class . '" data-id="' . $posts_id . '">' . $icon . '<text>' . $text . '</text></a>';
}

/**
 * @description: 获取帖子的标题
 * @param {*} $class
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_posts_lists_title($class = 'forum-title flex ac', $link_class = 'text-ellipsis', $show_topping = false, $show_badge = false, $show_status = true)
{

    global $post;
    $posts_id = $post->ID;

    if (!$posts_id) {
        return;
    }

    $post_status  = $post->post_status;
    $title        = get_the_title($posts_id);
    $permalink    = $post_status === 'trash' ? 'javascript:;' : get_permalink($posts_id);
    $target_blank = _pz('posts_target_blank') && $post_status !== 'trash' ? ' target="_blank"' : '';

    $hot      = zib_bbs_get_hot_badge($posts_id);
    $status   = $show_status ? zib_bbs_get_status_badge('', $post) : '';
    $essence  = zib_bbs_get_essence_badge();
    $question = zib_bbs_get_question_badge('mr3', $post);
    $topping  = $show_topping ? zib_bbs_get_topping_badge() : '';

    $badge = '';
    if ($show_badge) {
        $imgs_count = zib_get_post_imgs_count($post);
        $badge      = $imgs_count ? '<badge class="b-black ml3"><i aria-hidden="true" class="fa fa-image mr3"></i>' . $imgs_count . '</badge>' : '';
    }

    return $status . $hot . '<h2 class="' . $class . '"><a class="' . $link_class . '"' . $target_blank . ' href="' . $permalink . '" title="' . esc_attr($title) . '">' . $topping . $essence . $question . $title . $badge . '</a></h2>';
}

/**
 * @description: 获取帖子精华徽章
 * @param {*} $class
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_essence_badge($class = 'jb-red mr3', $posts_id = 0)
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    if (!$posts_id) {
        return;
    }

    $class = $class ? ' ' . $class : '';

    $meta = get_post_meta($posts_id, 'essence', true);
    if (!$meta) {
        return '';
    }

    $html = '<badge class="badge-essence' . $class . '" title="精华" data-toggle="tooltip">精</badge>';
    return $html;
}

//获取帖子的类型
function zib_get_posts_bbs_type($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (!isset($post->bbs_type)) {
        return $post->bbs_type;
    }
    $bbs_type       = get_post_meta($post->ID, 'bbs_type', true);
    $bbs_type       = $bbs_type ? $bbs_type : '';
    $post->bbs_type = $bbs_type;
    return $bbs_type;
}

/**
 * @description: 获取帖子置顶徽章
 * @param {*} $class
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_question_badge($class = 'mr3', $post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    $bbs_type = zib_get_posts_bbs_type($post); //类型判断
    if ('question' !== $bbs_type) {
        return;
    }

    $question_status = get_post_meta($post->ID, 'question_status', true); //提问状态

    $question_status_array = array(
        false => '提问',
        1     => '已解决',
        2     => '已解决',
    );

    if ($question_status) {
        $title = '提问已解决';
        $con   = '<icon><i class="fa fa-check-circle"></i></icon><text>' . _pz('bbs_question_ok_badge_name', '已解决') . '</text>';
    } else {
        $title = '提问';
        $con   = '<icon><i class="fa fa-question-circle-o"></i></icon><text>' . _pz('bbs_question_badge_name', '提问') . '</text>';
    }

    $class = $class ? ' ' . $class : '';
    $html  = '<badge class="badge-question jb-cyan' . $class . '" title="' . $title . '" data-toggle="tooltip">' . $con . '</badge>';
    return $html;
}

/**
 * @description: 获取帖子徽章
 * @param {*} $class
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_topping_badge($class = 'jb-blue mr3', $posts_id = 0)
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }

    $meta = get_post_meta($posts_id, 'topping', true);

    $titles = zib_bbs_get_posts_topping_options();
    $title  = isset($titles[$meta]) ? $titles[$meta] : '置顶';

    if (!$meta) {
        return '';
    }

    $class = $class ? ' ' . $class : '';
    $html  = '<badge class="badge-topping' . $class . '" title="' . $title . '" data-toggle="tooltip">' . zib_get_svg('topping') . '</badge>';
    return $html;
}

/**
 * @description: 获取文章的投票
 * @param {*} $posts_id
 * @param {*} $class
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_get_vote($posts_id = 0, $class = '', $user_id = 0)
{

    if (!$posts_id) {
        global $post;
        $posts_id = $post->id;
    }
    if (!$posts_id) {
        return;
    }
    if ($user_id) {
        $user_id = get_current_user_id();
    }
    $is_vote  = get_post_meta($posts_id, 'vote', true); //投票开关
    $vote_opt = zib_get_post_meta($posts_id, 'vote_option', true); //投票选项

    if (!$is_vote || !isset($vote_opt['options']) || count($vote_opt['options']) < 2) {
        return;
    }

    $types_names = array(
        'single'   => '单选',
        'multiple' => '多选',
        'pk'       => '双选PK',
    );
    $vote_opt_null = array(
        'title'      => '',
        'type'       => 'single', //创建时间
        'time'       => '', //创建时间
        'time_limit' => 0, //有效时间限制
        'options'    => array(
            '', '',
        ),
    );
    $vote_opt    = array_merge($vote_opt_null, $vote_opt);
    $number_type = _pz('bbs_vote_number_type', 'percentage');
    $number_html = '';
    if ('number' == $number_type) {
        $number_html = '<div class="vote-number"></div>';
    } elseif ('percentage' == $number_type) {
        $number_html = '<div class="vote-percentage"></div>';
    }

    //内容
    $vote_data  = zib_bbs_get_vote_data($posts_id);
    $user_voted = (array) zib_bbs_get_user_voted($posts_id); //已投票
    $lists      = '';
    $is_allow   = !$user_voted;
    foreach ($vote_opt['options'] as $k => $v) {
        $_class = in_array($k, $user_voted) ? ' is-voted' : '';
        $_count = isset($vote_data[$k]) ? $vote_data[$k] : 0;

        $lists .= '<div class="vote-item' . $_class . '" data-voted="' . $_count . '" data-index="' . $k . '">';
        $lists .= '<div class="vote-progress"></div>' . $number_html . esc_attr($v) . '';
        $lists .= '</div>';
    }

    if ('multiple' == $vote_opt['type']) {
        //多选
        if (!$user_voted) {
            $lists .= '<botton type="button" class="but c-blue vote-submit block radius" style="display: none;"><i class="fa fa-fw fa-check"></i>提交</botton>';
        }
    }

    //标题
    $icon  = '<i class="fa fa-bar-chart"></i>';
    $title = $vote_opt['title'] ? $vote_opt['title'] : ('px' == $vote_opt['type'] ? '参与投票' : '参与PK投票');
    $title = '<div class="vote-title mb10 relative flex ac"><span class="toggle-radius mr6 c-blue">' . $icon . '</span>' . $title . '<div class="abs-right"><i class="loading em12"></i></div></div>';

    //底部内容
    $type = '<span>' . $types_names[$vote_opt['type']] . '</span>';
    $time = '长期有效';
    if ($vote_opt['time_limit']) {
        //如果有时间限制
        $remaining_time = zib_get_time_remaining(strtotime($vote_opt['time'] . ' +' . $vote_opt['time_limit'] . ' day'), '已结束');
        if ('已结束' == $remaining_time) {
            $is_allow = false;
        } else {
            $remaining_time .= '结束';
        }
        $time = $remaining_time;
    }
    $time = '<span class="icon-spot">' . $time . '</span>';

    $user_count = $vote_data['users'] ? '<span class="vote-user-count">' . count($vote_data['users']) . '</span>人已参与' : '';
    if ($user_voted) {
        $user_count .= '<span class="icon-spot">您已投票</span>';
    } elseif ($is_allow) {
        $user_count .= '<span class="vote-start ' . ($user_count ? 'icon-spot' : '') . '">点击选项以投票</span>';
    }
    $user_count = $user_count ? '<span class="pull-right">' . $user_count . '</span>' : '';

    $footer = '<div class="muted-2-color vote-footer mt10 em09 px12-sm">' . $type . $time . $user_count . '</div>';

    if (get_current_user_id()) {
        $class .= $is_allow ? ' vote-allow' : '';
    } else {
        $class .= ' signin-loader';
    }
    $class .= ' type-' . $vote_opt['type'];
    $html = '<div class="vote-box lazyload ' . $class . '" data-type="' . $vote_opt['type'] . '" data-post-id="' . $posts_id . '" data-voted-all="' . $vote_data['all'] . '" data-nonce="' . wp_create_nonce('submit_vote') . '">';
    $html .= $title;
    $html .= '<div class="vote-lists">' . $lists . '</div>';
    $html .= $footer;
    $html .= '</div>';

    return $html;
}

/**
 * @description: 判断用户已经投票，已经投票则返回投票的序号
 * @param {*} $posts_id
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_get_user_voted($posts_id = 0, $user_id = 0)
{
    $data = array();
    if (!$posts_id) {
        global $post;
        $posts_id = $post->id;
    }
    if (!$posts_id) {
        return $data;
    }
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return $data;
    }
    $vote_ing_l = array(
        0 => array(1, 3, 5),
        1 => array(1, 3, 5),
    );
    $vote_ing = zib_get_post_meta($posts_id, 'vote_data', true); //已经投票内容

    if (!$vote_ing || !is_array($vote_ing)) {
        return $data;
    }
    foreach ($vote_ing as $k => $v) {
        if (in_array((int) $user_id, $v)) {
            $data[] = $k;
        }
    }
    return $data;
}

//获取数据
function zib_bbs_get_vote_data($posts_id = 0)
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->id;
    }
    if (!$posts_id) {
        return;
    }
    $data = array('all' => 0, 'users' => array());

    $vote_ing = zib_get_post_meta($posts_id, 'vote_data', true); //已经投票内容

    if (!$vote_ing || !is_array($vote_ing)) {
        return $data;
    }
    $users = array();
    foreach ($vote_ing as $k => $v) {
        $data[$k] = count((array) $v);
        $data['all'] += $data[$k];
        $users = array_merge($users, $v);
    }
    $data['users'] = array_unique($users);
    return $data;
}

//挂钩搜索功能
function zib_bbs_get_search_posts()
{
    //开始构建内容
    global $paged;
    $lists = '';
    $style = 'detail';
    while (have_posts()): the_post();
        if ('detail' == $style) {
            $lists .= zib_bbs_get_posts_list(
                array(
                    'class' => 'alone ajax-item',
                )
            );
        } else {
            $lists .= zib_bbs_get_posts_mini_list();
        }
    endwhile;

    if ($lists) {
        $lists = $lists;
    } elseif ((int) $paged < 2) {
        $lists = zib_get_ajax_null('未找到相关内容', '75', 'null-search.svg');
    }

    $lists .= zib_paging(false, false);
    return $lists;
}
add_filter('main_search_tab_content_forum', 'zib_bbs_get_search_posts', 10);

/**
 * @description: 查询置顶文章
 * @param {*} $plate_id
 * @param {*} $is_3
 * @return {*}
 */
function zib_bbs_get_topping_posts_query($is_3 = false, $plate_id = 0)
{
    if (!$plate_id) {
        $plate_id = get_queried_object_id();
    }

    $query_args = array(
        'post_type'     => 'forum_post',
        'post_status'   => ['publish'],
        'order'         => 'DESC',
        'orderby'       => 'meta_value_num modified',
        'meta_key'      => 'topping',
        'showposts'     => -1, //不限制数量，显示所有
        'no_found_rows' => true, //不需要分页，不查询分页需要的总数量
        'meta_query'    => array(
            'relation' => 'OR',
            array(
                array(
                    'key'     => 'plate_id',
                    'value'   => $plate_id,
                    'compare' => (is_array($plate_id) ? 'IN' : '='),
                ),
                array(
                    'key'     => 'topping',
                    'value'   => array('1', '2'),
                    'compare' => 'IN',
                    'order'   => 'DESC',
                ),
            ),
            array(
                'key'   => 'topping',
                'value' => '3',
                'order' => 'DESC',
            ),
        ),
    );

    if ($is_3) {
        $query_args['meta_query'] = array(
            array(
                array(
                    'key'   => 'topping',
                    'value' => '3',
                    'order' => 'DESC',
                ),
            ),
        );
    } else {
        $query_args['meta_query'] = array(
            array(
                'key'     => 'plate_id',
                'value'   => $plate_id,
                'compare' => (is_array($plate_id) ? 'IN' : '='),
            ),
            array(
                'key'     => 'topping',
                'value'   => array('1', '2'),
                'compare' => 'IN',
                'order'   => 'DESC',
            ),
        );
    }
    $query = new WP_Query($query_args);
    return $query;
}

/**
 * @description: 获取帖子列表的主查询函数
 * @param {*} $args 接受全部WP_Query选项
 * @return {*}
 */
function zib_bbs_get_posts_query($args = array())
{
    $posts_per_page = _pz('bbs_posts_per_page', 20);
    $posts_per_page = isset($args['paged_size']) ? (int) $args['paged_size'] : $posts_per_page;
    $paged          = zib_get_the_paged();
    $orderby        = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : (isset($args['orderby']) ? $args['orderby'] : 'modified');
    $plate          = isset($_REQUEST['plate']) ? $_REQUEST['plate'] : get_the_ID();

    if (isset($args['orderby'])) {
        unset($args['orderby']);
    }
    if (isset($args['plate'])) {
        $plate = $args['plate'];
        unset($args['plate']);
    }

    $query_args = array(
        'post_type'      => 'forum_post',
        'post_status'    => ['publish'],
        'order'          => 'DESC',
        'orderby'        => $orderby,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );

    //话题
    if (!empty($args['topic'])) {
        $query_args['tax_query'][] = array(
            'taxonomy'         => 'forum_topic',
            'field'            => 'id',
            'terms'            => (array) $args['topic'],
            'include_children' => true,
        );
    }
    if (isset($args['topic'])) {
        unset($args['topic']);
    }
    //标签
    if (!empty($args['tag'])) {
        $query_args['tax_query'][] = array(
            'taxonomy'         => 'forum_tag',
            'field'            => 'id',
            'terms'            => (array) $args['tag'],
            'include_children' => true,
        );
    }
    if (isset($args['tag'])) {
        unset($args['tag']);
    }

    //版块显示
    if ($plate) {
        $query_args['meta_query'] = array(
            array(
                'key'     => 'plate_id',
                'value'   => $plate,
                'compare' => (is_array($plate) ? 'IN' : '='),
            ),
        );
    }
    //版块排除
    if (!empty($args['plate_exclude'])) {
        $query_args['meta_query'] = array(
            array(
                'key'     => 'plate_id',
                'value'   => $args['plate_exclude'],
                'compare' => (is_array($args['plate_exclude']) ? 'NOT IN' : '!='),
            ),
        );
    }
    if (isset($args['plate_exclude'])) {
        unset($args['plate_exclude']);
    }

    $query_args = zib_bbs_query_orderby_filter($orderby, $query_args);

    //其它筛选
    $other_filter_args = array();
    if (isset($args['filter'])) {
        $o_filter = is_array($args['filter']) ? $args['filter'] : array($args['filter']);
        foreach ($o_filter as $_f) {
            if (in_array($_f, array('essence', 'vote', 'is_hot', 'question_status'))) {
                $other_filter_args[] = array(
                    'key'   => $_f,
                    'value' => 1,
                );
            }
            if ('topping' === $_f) {
                $other_filter_args[] = array(
                    'key'     => 'topping',
                    'value'   => array('1', '2', '3'),
                    'compare' => 'IN',
                );
            }
        }

        if (count($other_filter_args) > 1) {
            $other_filter_args['relation'] = 'OR';
        }
        $query_args['meta_query'][] = $other_filter_args;

        unset($args['filter']);
    }

    //阅读权限筛选
    if (!empty($args['allow_view'])) {
        $query_args['meta_query'][] = array(
            'key'     => 'allow_view',
            'value'   => $args['allow_view'],
            'compare' => (is_array($args['allow_view']) ? 'IN' : '='),
        );
        unset($args['allow_view']);
    }

    //类型筛选
    if (!empty($args['bbs_type'])) {
        $query_args['meta_query'][] = array(
            'key'     => 'bbs_type',
            'value'   => $args['bbs_type'],
            'compare' => (is_array($args['bbs_type']) ? 'IN' : '='),
        );
        unset($args['bbs_type']);
    }

    //置顶文章
    if (isset($args['topping'])) {
        if ($args['topping']) {
            if (isset($args['topping'][1])) {
                $query_args['meta_query'][] = array(
                    'key'     => 'topping',
                    'value'   => $args['topping'][1],
                    'compare' => $args['topping'][0],
                    'order'   => 'DESC',
                );
            } else {
                $query_args['meta_key']     = 'topping';
                $query_args['meta_query'][] = array(
                    'key'     => 'topping',
                    'value'   => array('1', '2', '3'),
                    'compare' => 'IN',
                    'order'   => 'DESC',
                );
                $query_args['orderby']   = 'meta_value_num';
                $query_args['showposts'] = -1;
            }
        } else {
            $query_args['meta_query'][] =
            array(
                'key'     => 'topping',
                'value'   => array('1', '2', '3'),
                'compare' => 'NOT IN',
            );
        }

        unset($args['topping']);
    }

    $query_args = array_merge($query_args, $args);
    return new WP_Query($query_args);
}

/**
 * 推荐指数排序，获取最大值
 */
function zib_bbs_get_dynamic_score_max($_join, $_where)
{
    $cache_key = md5($_join . $_where);
    $cache     = wp_cache_get($cache_key, 'bbs_dynamic_score_max');
    if ($cache !== false) {
        return $cache;
    }

    global $wpdb;

    $_sql              = "SELECT MAX(z_mt_score.meta_value + 10) FROM {$wpdb->posts} $_join WHERE 1=1 $_where LIMIT 1";
    $max_dynamic_score = (int) $wpdb->get_var($_sql);

    //缓存5分钟
    $cache_minutes = _pz('recommend_score_opt', 10, 'cache_time');
    $cache_minutes = $cache_minutes > 0 ? $cache_minutes : 10;
    wp_cache_set($cache_key, $max_dynamic_score, 'bbs_dynamic_score_max', $cache_minutes * 60);
    return $max_dynamic_score;
}

/**
 * 推荐指数排序方式：
 * 获取最大值，并缓存，提高性能
 */
function zib_bbs_get_dynamic_score_ids($clauses, $query)
{

    global $wpdb;
    $postmeta_table_name = $wpdb->postmeta;
    $posts_table_name    = $wpdb->posts;

    $clauses['join'] .= "
    INNER JOIN $postmeta_table_name AS z_mt_score ON ($posts_table_name.ID = z_mt_score.post_id)
    ";
    $clauses['where'] .= " AND z_mt_score.meta_key = 'recommend_score'";

    $_join     = $clauses['join'];
    $_where    = $clauses['where'];
    $cache_key = md5($_join . $_where);
    $cache     = wp_cache_get($cache_key, 'bbs_dynamic_score_ids');

    //将所有的$cache_key保存到一个缓存
    $cache_keys = wp_cache_get('bbs_dynamic_score_ids_cache_keys', 'zib_cache_group');
    if (!$cache_keys || !is_array($cache_keys)) {
        $cache_keys = [];
    }
    $cache_keys[] = $cache_key;
    wp_cache_set('bbs_dynamic_score_ids_cache_keys', $cache_keys, 'zib_cache_group');

    if ($cache !== false) {
        return $cache;
    }

    //获取最大值
    $max_dynamic_score = zib_bbs_get_dynamic_score_max($_join, $_where);

    //准备参数
    $opt       = _pz('recommend_score_opt', [], 'time');
    $one_time  = !empty($opt['one_time']) ? (int) $opt['one_time'] : 24;
    $one_off   = !empty($opt['one_off']) ? (int) $opt['one_off'] : 10;
    $two_time  = !empty($opt['two_time']) ? (int) $opt['two_time'] : 7;
    $two_off   = !empty($opt['two_off']) ? (int) $opt['two_off'] : 50;
    $last_time = !empty($opt['last_time']) ? (int) $opt['last_time'] : 3;

    $one_time  = $one_time > 0 ? $one_time : 24;
    $one_off   = $one_off > 0 && $one_off < 100 ? $one_off : 10;
    $two_time  = $two_time > 0 ? $two_time : 7;
    $two_off   = $two_off > 0 && $two_off < 100 ? $two_off : 50;
    $last_time = $last_time > 0 ? $last_time : 3;

    $one_time_unit  = zib_floatval_round($max_dynamic_score * $one_off / 100 / $one_time, false);
    $two_time_max   = zib_floatval_round($max_dynamic_score * (100 - $one_off) / 100, false);
    $two_time_unit  = zib_floatval_round($two_time_max * $two_off / 100 / $two_time, false);
    $last_time_max  = zib_floatval_round($two_time_max * (100 - $two_off) / 100, false);
    $last_time_unit = zib_floatval_round($last_time_max / $last_time, false);

    //24小时内:给他最大值，按小时衰减10%
    //24小时到7天内：按天再衰减50%
    //超过7天：按月衰减为0
    $clauses['fields'] = "{$wpdb->posts}.ID ,
        (
            CASE
                WHEN TIMESTAMPDIFF(HOUR, post_date, NOW()) <= $one_time THEN $max_dynamic_score - (TIMESTAMPDIFF(HOUR, post_date, NOW()) * $one_time_unit)
                WHEN TIMESTAMPDIFF(DAY, post_date, NOW() - INTERVAL $one_time HOUR) <= $two_time THEN $two_time_max - (TIMESTAMPDIFF(DAY, post_date, NOW() - INTERVAL $one_time HOUR) * $two_time_unit)
                WHEN TIMESTAMPDIFF(MONTH, post_date, NOW() - INTERVAL $two_time DAY) <= $last_time THEN $last_time_max - (TIMESTAMPDIFF(MONTH, post_date, NOW() - INTERVAL $two_time DAY) * $last_time_unit)
                ELSE 0
            END
        )
        + z_mt_score.meta_value
        as dynamic_score
        ";

    $order              = $query->get('order');
    $clauses['orderby'] = "dynamic_score $order,$posts_table_name.post_date $order";

    //查找IDS
    $where    = isset($clauses['where']) ? $clauses['where'] : '';
    $groupby  = isset($clauses['groupby']) ? $clauses['groupby'] : '';
    $join     = isset($clauses['join']) ? $clauses['join'] : '';
    $orderby  = isset($clauses['orderby']) ? $clauses['orderby'] : '';
    $distinct = isset($clauses['distinct']) ? $clauses['distinct'] : '';
    $fields   = isset($clauses['fields']) ? $clauses['fields'] : '';
    $limits   = isset($clauses['limits']) ? $clauses['limits'] : '';
    if (!empty($groupby)) {
        $groupby = 'GROUP BY ' . $groupby;
    }

    $request_sql = "SELECT $distinct $fields
					FROM {$wpdb->posts} $join
					WHERE 1=1 $where
					$groupby
					ORDER BY $orderby
                    ";
    $results   = (array) $wpdb->get_results($request_sql, ARRAY_A);
    $posts_ids = array_map('absint', array_column($results, 'ID'));

    //缓存5分钟
    $cache_minutes = $opt['cache_time'] ?? 10;
    $cache_minutes = $cache_minutes > 0 ? $cache_minutes : 10;
    wp_cache_set($cache_key, $posts_ids, 'bbs_dynamic_score_ids', $cache_minutes * 60);
    return $posts_ids;
}

//挂钩推荐指数查询
function zib_bbs_dynamic_score_posts_clauses($clauses, $query)
{
    if ($query->get('post_type') !== 'forum_post' || $query->get('orderby') !== 'dynamic_score') {
        return $clauses;
    }

    global $wpdb;

    //计算最大dynamic_score
    $ids         = zib_bbs_get_dynamic_score_ids($clauses, $query);
    $ids_implode = $ids ? implode(',', $ids) : '';

    //重写$clauses
    $clauses['where']    = ' AND ' . ($ids_implode ? "{$wpdb->posts}.ID IN ($ids_implode)" : ' 1 != 1');
    $clauses['orderby']  = $ids_implode ? "FIELD( {$wpdb->posts}.ID, $ids_implode ) ASC" : 'ID';
    $clauses['distinct'] = '';
    $clauses['join']     = '';
    $clauses['groupby']  = '';

    return $clauses;
}
add_filter('posts_clauses', 'zib_bbs_dynamic_score_posts_clauses', 10, 2);

//计算推荐指数的值
function zib_bbs_calculate_posts_recommend_score($posts_id)
{

    $recommend_score_opt = _pz('recommend_score_opt');
    $comment_x           = !empty($recommend_score_opt['comment']) ? (int) $recommend_score_opt['comment'] : 2; //评论系数
    $score_deduct_x      = -(!empty($recommend_score_opt['score_deduct']) ? (int) $recommend_score_opt['score_deduct'] : 15); //扣分系数
    $score_extra_x       = !empty($recommend_score_opt['score_extra']) ? (int) $recommend_score_opt['score_extra'] : 10; //加分系数
    $view_x              = !empty($recommend_score_opt['views']) ? (int) $recommend_score_opt['views'] : 1; //浏览量系数
    $favorite_x          = !empty($recommend_score_opt['favorite']) ? (int) $recommend_score_opt['favorite'] : 10; //收藏系数

    $comment_num      = (int) get_comments_number($posts_id);
    $views_num        = (int) get_post_meta($posts_id, 'views', true); //查看
    $favorite_num     = (int) get_post_meta($posts_id, 'favorite_count', true);
    $score_deduct_num = 0;
    $score_extra_num  = 0;

    $score_detail = zib_get_post_meta($posts_id, 'score_detail', true);
    if (is_array($score_detail) && $score_detail) {
        foreach ($score_detail as $score) {
            if ($score > 0) {
                $score_extra_num += (abs((int) $score));
            } elseif ($score < 0) {
                $score_deduct_num += (abs((int) $score));
            }
        }
    }

    $recommend_score = $comment_x * $comment_num + $score_deduct_x * $score_deduct_num + $score_extra_x * $score_extra_num + $view_x * $views_num + $favorite_x * $favorite_num;
    return $recommend_score;
}

//保存帖子的推荐分
function zib_bbs_save_posts_recommend_score($post)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (empty($post->ID) || !isset($post->post_type) || 'forum_post' !== $post->post_type) {
        return;
    }

    update_post_meta($post->ID, 'recommend_score', zib_bbs_calculate_posts_recommend_score($post->ID));
}

function zib_bbs_update_posts_recommend_score_meta($meta_id, $post_id, $meta_key, $_meta_value)
{
    if (in_array($meta_key, array('views', 'score', 'favorite_count'))) {
        zib_bbs_save_posts_recommend_score($post_id);
    }
}
add_action('updated_post_meta', 'zib_bbs_update_posts_recommend_score_meta', 99, 4);
add_action('added_post_meta', 'zib_bbs_update_posts_recommend_score_meta', 99, 4);

function zib_bbs_update_posts_recommend_score_comment($comment_ID, $comment)
{
    zib_bbs_save_posts_recommend_score($comment->comment_post_ID);
}
add_action('untrashed_comment', 'zib_bbs_update_posts_recommend_score_comment', 10, 2);
add_action('trashed_comment', 'zib_bbs_update_posts_recommend_score_comment', 10, 2);

/**
 * 未使用
 * 单次加分或减分
 */
function zib_bbs_update_posts_recommend_score($post, $type = 'views', $is_minus = false)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (empty($post->ID) || !isset($post->post_type) || 'forum_post' !== $post->post_type) {
        return;
    }

    //获取之前的值
    $old_value = (int) get_post_meta($post->ID, 'recommend_score', true);

    $opt   = _pz('recommend_score_opt');
    $num_x = 0;
    switch ($type) {
        case 'views':
            $num_x = !empty($opt['views']) ? (int) $opt['views'] : 1;
            break;
        case 'favorite':
            $num_x = !empty($opt['favorite']) ? (int) $opt['favorite'] : 10;
            break;
        case 'comment':
            $num_x = !empty($opt['comment']) ? (int) $opt['comment'] : 5;
            break;
        case 'score_deduct':
            $num_x = -(!empty($opt['score_deduct']) ? (int) $opt['score_deduct'] : 15);
            break;
        case 'score_extra':
            $num_x = !empty($opt['score_extra']) ? (int) $opt['score_extra'] : 10;
            break;
    }

    if ($is_minus) {
        $num_x = -$num_x;
    }

    $new_value = intval($old_value + $num_x);

    //3.更新推荐指数
    update_post_meta($post->ID, 'recommend_score', $new_value);
}

//帖子通过审核，刷新缓存
function zib_bbs_refresh_dynamic_score_cache($post)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (empty($post->ID) || 'forum_post' !== $post->post_type) {
        return;
    }

    zib_bbs_dynamic_score_ids_cache_flush();
}
add_action('pending_to_publish', 'zib_bbs_refresh_dynamic_score_cache');
add_action('trash_to_publish', 'zib_bbs_refresh_dynamic_score_cache');
add_action('publish_to_pending', 'zib_bbs_refresh_dynamic_score_cache');
add_action('bbs_posts_delete', 'zib_bbs_refresh_dynamic_score_cache');

//帖子新发布，刷新缓存
function zib_bbs_refresh_dynamic_score_cache_new_post($post_id, $post, $update)
{
    if ($update || $post->post_status !== 'publish') {
        return;
    }
    zib_bbs_dynamic_score_ids_cache_flush();
}
add_action('save_post_forum_post', 'zib_bbs_refresh_dynamic_score_cache_new_post', 99, 3);

//切换板块，刷新缓存
function zib_bbs_dynamic_score_ids_cache_flush()
{
    $cache_keys = wp_cache_get('bbs_dynamic_score_ids_cache_keys', 'zib_cache_group');
    if ($cache_keys && is_array($cache_keys)) {
        foreach ($cache_keys as $cache_key) {
            wp_cache_delete($cache_key, 'bbs_dynamic_score_ids');
        }
    }

    wp_cache_delete('bbs_dynamic_score_ids_cache_keys', 'zib_cache_group');
}
add_action('posts_plate_move', 'zib_bbs_dynamic_score_ids_cache_flush');
