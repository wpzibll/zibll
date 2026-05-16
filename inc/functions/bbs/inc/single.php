<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-27 20:35:01
 * @LastEditTime : 2026-04-21 14:28:28
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|回复类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_bbs_posts_page_content()
{
    echo '<div class="fixed-wrap single-wrap">';
    echo zib_bbs_single_fixed_btns();
    echo '<div class="fixed-wrap-content">';
    do_action('bbs_posts_page_content_top');
    echo zib_bbs_get_single_cover();
    if (apply_filters('single_show_breadcrumbs', true)) {
        echo zib_bbs_get_breadcrumbs();
    }
    echo '<article id="article" class="article zib-widget forum-article relative">';
    zib_bbs_single_header();
    zib_bbs_single_content();
    zib_bbs_single_footer();
    echo '</article>';
    zib_bbs_comments_template();
    do_action('bbs_posts_page_content_bottom');
    echo '</div>';
    echo '</div>';
}

add_action('bbs_posts_page_content', 'zib_bbs_posts_page_content');

function zib_bbs_single_header()
{
    global $post;
    $title = zib_bbs_get_single_title($post);
    $info  = zib_bbs_get_single_info($post);

    echo '<div class="article-header clearfix">';
    echo $title;
    echo $info;
    echo '</div>';
}

//封面输出
function zib_bbs_get_single_cover()
{
    global $post;

    //判断阅读权限
    if (!zib_bbs_posts_is_can_viewed($post)) {
        return;
    }

    $cover = '';
    $video = zib_get_post_meta($post->ID, 'featured_video', true);
    if ($video) {
        $pic_url      = zib_get_post_meta($post->ID, 'cover_image', true);
        $scale_height = _pz('bbs_posts_cover_opt', 56, 'video_ratio');
        $cover        = zib_get_dplayer($video, $pic_url, $scale_height);
    }

    $cover = $cover ?: zib_bbs_get_posts_slide_cover($post->ID, true);
    $cover = $cover ?: zib_bbs_get_posts_image_cover($post->ID, true);

    return $cover ? '<div class="forum-single-cover mb20">' . $cover . '</div>' : '';
}

//文章页面内容输出
function zib_bbs_single_content()
{
    global $post;
    $article_nav = _pz('article_nav') ? ' data-nav="posts"' : '';
    add_filter('hidecontent_payshow_hide_content', 'zib_bbs_hidecontent_payshow_hide_content', 10, 3);

    //首先判断版块的阅读权限
    $plate_id   = zib_bbs_get_plate_id($post->ID);
    $plate_data = zib_bbs_get_allow_view_data(get_post($plate_id));
    if ($plate_data['open'] && !$plate_data['allow_reason']) {
        //板块阅读限制了
        echo '<div class="theme-box wp-posts-content"' . $article_nav . '>';
        echo '<div class="theme-box">' . $plate_data['not_html'] . '</div>';
        echo '</div>';
    } else {
        //再判断帖子的阅读权限
        $data = zib_bbs_get_allow_view_data($post);
        if ($data['open'] && !$data['allow_reason']) {
            $pay_hide_part = zib_get_post_meta($post->ID, 'pay_hide_part', true);

            echo '<div class="theme-box wp-posts-content"' . $article_nav . '>';
            echo '<div class="theme-box">' . $data['not_html'] . '</div>';
            if ($pay_hide_part) {
                the_content();
                //文章分页
                wp_link_pages(
                    array(
                        'before' => '<p class="text-center post-nav-links radius8 padding-6">',
                        'after'  => '</p>',
                    )
                );
            }
            echo '</div>';

        } else {
            $max_height_class = '';
            $max_height_style = '';
            if (_pz('bbs_posts_maxheight_s')) {
                $max_height_class = ' limit-height';
                $max_height       = (int) _pz('bbs_posts_maxheight');
                $max_height       = $max_height ?: 1000;
                $max_height_style = ' style="max-height:' . $max_height . 'px;" data-maxheight="' . ($max_height - 80) . '"';
            }

            echo '<div class="theme-box wp-posts-content' . $max_height_class . '"' . $article_nav . $max_height_style . '>';
            the_content();
            //文章分页
            wp_link_pages(
                array(
                    'before' => '<p class="text-center post-nav-links radius8 padding-6">',
                    'after'  => '</p>',
                )
            );
            echo '</div>';
            do_action('zib_bbs_posts_content_after', $post);
        }
    }

}

function zib_bbs_hidecontent_payshow_hide_content($_hide, $content, $post)
{
    //判断帖子的阅读权限
    $data = zib_bbs_get_allow_view_data($post);
    if ($data['open'] && !$data['allow_reason']) {
        return $_hide;
    }

    return '<div class="hidden-box show"><div class="hidden-text">本文付费阅读内容：' . $data['allow_reason'] . '</div>' . do_shortcode($content) . '</div>';
}

//文章页面内容下方添加投票
add_action('zib_bbs_posts_content_after', function ($post) {
    echo zib_bbs_get_vote($post->ID, 'single-vote mb20');
});

function zib_bbs_single_footer()
{
    global $post;

    do_action('bbs_single_footer', $post);

    //标签和话题的按钮
    //底部按钮
    $favorite_btn = zib_bbs_get_posts_favorite_btn($post->ID, 'item');
    $share_btn    = zib_bbs_get_posts_share_btn($post, 'item');
    $rewards_btn  = zib_get_rewards_button($post->post_author, 'item');
    $dropdown     = zib_bbs_get_posts_more_dropdown($post->ID, 'relative pull-right px14 item drop-fixed-sm', 'toggle-radius opacity8');

    $action = '<div class="forum-article-footer flex ac jsb mb10 footer-actions">';
    $action .= '<div class="left flex ac">';
    $action .= $rewards_btn;
    $action .= '</div>';
    $action .= '<div class="right flex ac">';
    $action .= $share_btn;
    $action .= $favorite_btn;
    $action .= $dropdown;
    $action .= '</div>';
    $action .= '</div>';

    $html = '';
    $html .= $action;
    echo $html;
}

/**
 * @description: 获取帖子更多按钮的dropdown下拉框
 * @param {*} $term_id
 * @param {*} $class
 * @param {*} $direction 方向
 * @return {*}
 */
function zib_bbs_get_posts_more_dropdown($posts_id, $class = '', $con_class = '', $con = '', $direction = 'up')
{

    if (!$posts_id) {
        return;
    }

    global $zib_bbs, $bbs_posts_more_action;
    $con       = $con ? $con : zib_get_svg('menu_2');
    $class     = $class ? ' ' . $class : '';
    $con_class = $con_class ? ' class="' . $con_class . '"' : '';
    $name      = $zib_bbs->posts_name;

    if (!isset($bbs_posts_more_action[$posts_id])) {
        $action  = '';
        $post    = get_post($posts_id);
        $user_id = $post->post_author;
        if ('publish' === $post->post_status) {
            $essence_set = zib_bbs_get_posts_essence_set_link($posts_id, 'c-blue', 'c-yellow', '<badge class="badge-essence jb-blue" style="margin-right:8px;">精</badge>设为精华', '<badge class="badge-essence jb-yellow mr6">精</badge>取消精华');
            $action .= $essence_set ? '<li>' . $essence_set . '</li>' : '';

            $topping_set_icon = zib_get_svg('topping', null, 'icon mr6 fa-fw');
            $topping_set      = zib_bbs_get_posts_topping_set_link($posts_id, '', $topping_set_icon . '设置置顶');
            $action .= $topping_set ? '<li>' . $topping_set . '</li>' : '';

            $plate_move = zib_bbs_get_posts_plate_move_link($posts_id, '', zib_get_svg('plate-fill', null, 'icon mr6 fa-fw') . '切换' . $zib_bbs->plate_name);
            $action .= $plate_move ? '<li>' . $plate_move . '</li>' : '';

            $allow_view = zib_bbs_get_posts_allow_view_set_link($posts_id, '', '<i class="fa fa-unlock-alt mr6 fa-fw"></i>阅读权限');
            $action .= $allow_view ? '<li>' . $allow_view . '</li>' : '';

            $comment_close = zib_bbs_get_posts_comment_close_link($posts_id, '', zib_get_svg('comment', null, 'icon mr6 fa-fw') . '关闭评论', zib_get_svg('comment', null, 'icon mr6 fa-fw') . '开启评论');
            $action .= $comment_close ? '<li>' . $comment_close . '</li>' : '';
        }

        $edit = zib_bbs_get_posts_edit_page_link($posts_id, '', '<i class="fa fa-fw fa-edit mr6"></i>编辑' . $name);
        $action .= $edit ? '<li>' . $edit . '</li>' : '';

        $audit = zib_bbs_get_posts_audit_link($posts_id);
        $action .= $audit ? '<li>' . $audit . '</li>' : '';

        $del = zib_bbs_get_posts_delete_link($posts_id, 'c-red', '<i class="fa fa-trash-o mr6 fa-fw"></i>删除' . $name);
        $action .= $del ? '<li>' . $del . '</li>' : '';

        $user_ban = zib_get_edit_user_ban_link($user_id, '', '<i class="fa fa-ban mr6 fa-fw c-red"></i>禁封用户');
        if (!$user_ban && _pz('user_report_s', true)) {
            $user_ban = zib_get_report_link($user_id, get_permalink($post), '', '<i class="fa fa-exclamation-triangle mr6 fa-fw c-red"></i>举报');
        }

        $action .= $user_ban ? '<li>' . $user_ban . '</li>' : '';
        $bbs_posts_more_action[$posts_id] = $action;
    } else {
        $action = $bbs_posts_more_action[$posts_id];
    }

    if (!$action) {
        return;
    }

    $html = '<span class="drop' . $direction . ' more-dropup' . $class . '">';
    $html .= '<a href="javascript:;"' . $con_class . ' data-toggle="dropdown">' . $con . '</a>';
    $html .= '<ul class="dropdown-menu">';
    $html .= $action;
    $html .= '</ul>';
    $html .= '</span>';
    return $html;
}

/**
 * @description: 文章页面左侧浮动按钮
 * @param {*}
 * @return {*}
 */
function zib_bbs_single_fixed_btns()
{
    global $post;
    if (wp_is_mobile()) {
        return;
    }

    $btns         = '';
    $favorite_btn = zib_bbs_get_posts_favorite_btn($post->ID, '', '', '', true, false);
    $share_btn    = zib_bbs_get_posts_share_btn($post);

    $btns .= zib_bbs_get_score_box($post->ID);
    $btns .= $favorite_btn;
    $btns .= $share_btn;

    $btns = apply_filters('bbs_single_fixed_btns', $btns, $post->ID);
    $html = '<div class="fixed-wrap-nav single-fixed-btns" data-wrap=".forum-article"><div>' . $btns . '</div></div>';

    return $html;
}

add_action('bbs_single_footer', function ($post) {
    $term_link = zib_bbs_get_posts_topic_link($post->ID, 'but radius mm3 c-red', zib_get_svg('topic'));
    $term_link .= zib_bbs_get_posts_tag_link($post->ID, 'but radius mm3', '<i class="fa fa-tag" aria-hidden="true"></i>');
    $term_html = $term_link ? '<div class="article-tags mb20 em09">' . $term_link . '</div>' : '';
    echo $term_html;
});

add_action('bbs_single_footer', function ($post) {
    echo zib_bbs_get_score_box($post->ID, 'single-footer text-center', true);
});

add_action('bbs_single_footer', function ($post) {
    if (get_current_user_id()) {
        echo zib_get_respond_mobile('#respond', _pz('bbs_comment_placeholder'), 'mb20', true);
    }
});

/**
 * @description: 获取评分按钮
 * @param {*} $posts_id 帖子ID
 * @param {*} $class class
 * @param {*} $show_detail 是否显示详情
 * @param {*} $only_buts 是否仅显示按钮
 * @return {*}
 */
function zib_bbs_get_score_box($posts_id = 0, $class = '', $show_detail = false, $only_buts = false)
{
    if (!$posts_id) {
        global $post;
        $posts_id = $post->ID;
    }
    if (!$posts_id) {
        return;
    }

    $class   = $class ? ' ' . $class : '';
    $user_id = get_current_user_id();

    $score_detail              = zib_get_post_meta($posts_id, 'score_detail', true);
    $score_extra_active_class  = '';
    $score_deduct_active_class = '';

    $score_extra_action_attr  = '';
    $score_deduct_action_attr = '';
    if ($user_id) {
        $score_extra_action_attr  = ' ajax-action="score_extra" data-nonce="' . wp_create_nonce('score_extra') . '"';
        $score_deduct_action_attr = ' ajax-action="score_deduct" data-nonce="' . wp_create_nonce('score_deduct') . '"';
        if (isset($score_detail[$user_id])) {
            if ($score_detail[$user_id] > 0) {
                $score_extra_active_class = ' active';
            } elseif ($score_detail[$user_id] < 0) {
                $score_deduct_active_class = ' active';
            }
        }
    } else {
        $score_extra_active_class  = ' signin-loader';
        $score_deduct_active_class = ' signin-loader';
    }

    $score_extra  = '<a href="javascript:;"' . $score_extra_action_attr . ' class="btn-score extra' . $score_extra_active_class . '" data-id="' . $posts_id . '">' . zib_get_svg('extra-points') . '</a>';
    $score_deduct = '<a href="javascript:;"' . $score_deduct_action_attr . ' class="btn-score deduct' . $score_deduct_active_class . '" data-id="' . $posts_id . '">' . zib_get_svg('deduct-points') . '</a>';

    $text      = is_array($score_detail) && $score_detail ? _cut_count(array_sum($score_detail)) : '评分';
    $text_html = '<text>' . $text . '</text>';

    $score_detail_html = '';
    if ($show_detail && !$only_buts) {
        $count_score_detail = is_array($score_detail) && $score_detail ? count($score_detail) : 0;
        //评分明细
        $text_desc = _pz('bbs_score_text_desc', '欢迎为Ta评分');
        $text_desc = $count_score_detail ? $count_score_detail . '人已评分' : $text_desc;
        $text_desc = '<div class="desc em09 muted-3-color mt6 mb10">' . $text_desc . '</div>';

        $score_user_lists_args = array(
            'class' => 'score-users',
            'query' => array(
                'action' => 'score_user_lists',
                'id'     => $posts_id,
            ),
        );
        $score_user_lists_args['loader'] = str_repeat('<div class="score-user-item avatar-img placeholder radius"></div>', ($count_score_detail > 8 ? 8 : $count_score_detail));
        $score_user_lists                = is_array($score_detail) && $score_detail ? zib_get_remote_box($score_user_lists_args) : '';

        $score_detail_html = '<div class="score-box-detail">';
        $score_detail_html .= $text_desc;
        $score_detail_html .= $score_user_lists;
        $score_detail_html .= '</div>';
    }

    $html = $only_buts ? '' : '<div class="score-box' . $class . '">';
    $html .= '<div class="score-btns">';
    $html .= $score_extra . $text_html . $score_deduct;
    $html .= '</div>';
    $html .= $score_detail_html;
    $html .= $only_buts ? '' : '</div>';

    return $html;
}

//获取评分明细
function zib_bbs_get_score_user_lists($posts_id = 0, $class = '', $paged = 1)
{
    if (!$posts_id) {
        return '';
    }

    $class = $class ? ' ' . $class : '';

    $page_size = 16; //每页显示16

    $score_detail = zib_get_post_meta($posts_id, 'score_detail', true);

    if (!is_array($score_detail) || !$score_detail) {
        return '';
    }

    $lists = '';

    $cuunt = count($score_detail);
    if ($cuunt > $page_size) {
        $array_chunk  = array_chunk($score_detail, $page_size, true);
        $score_detail = isset($array_chunk[$paged - 1]) ? $array_chunk[$paged - 1] : $score_detail;
        //   return json_encode($array_chunk) . json_encode($score_detail).$paged;
    }

    foreach ($score_detail as $user_id => $score) {
        if (!$score) {
            continue;
        }

        $user_data = get_userdata($user_id);
        if (!isset($user_data->ID)) {
            continue;
        }

        $avatar = zib_get_data_avatar($user_id);
        $_class = $score > 0 ? ' extra' : ' deduct';
        $score  = $score > 0 ? '+' . $score : $score;
        $title  = esc_attr($user_data->display_name) . ' ' . $score . '分';

        $lists .= '<div class="score-user-item avatar-img' . $_class . $class . '" data-toggle="tooltip" title="' . $title . '">';
        $lists .= $avatar;
        $lists .= '<div class="avatar-icontag">' . $score . '</div>';
        $lists .= '</div>';
    }

    if (isset($array_chunk[$paged])) {
        $score_user_lists_args = array(
            'type'   => '',
            'class'  => 'pointer mt10 muted-2-color',
            'loader' => '查看更多<i class="fa fa-angle-right em12 ml6"></i>',
            'query'  => array(
                'action' => 'score_user_lists',
                'id'     => $posts_id,
                'paged'  => $paged + 1,
            ),
        );
        $lists .= zib_get_remote_box($score_user_lists_args);
    }
    return $lists;
}

/**
 * @description: 获取帖子页面的帖子简略信息
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_single_info($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (!isset($post->ID)) {
        return;
    }

    $posts_id   = $post->ID;
    $title_link = '';

    if (wp_is_mobile()) {
        return zib_bbs_get_posts_author_card_box($posts_id);
    }

    $author_id    = isset($post->post_author) ? $post->post_author : 0;
    $allow_view   = zib_bbs_get_posts_allow_view_btn($posts_id, 'item'); //限制阅读
    $time_html    = zib_get_post_time_tooltip($post);
    $views        = get_post_meta($posts_id, 'views', true); //查看
    $reply        = get_comments_number($posts_id); //回复
    $display_name = zib_get_user_name("id=$author_id&class=&name_class=focus-color");

    $left = '<div class="meta-left">';
    $left .= $display_name;
    $left .= '<span class="icon-spot">' . $time_html . '</span>';
    $left .= '</div>';

    $right = '<div class="meta-right">';
    $right .= $allow_view;
    $right .= '<item class="item item-view">' . zib_get_svg('view') . _cut_count($views) . '</item>';
    $right .= '<a class="item item-comment" href="javascript:(scrollTopTo(\'#commentform\',-100));">' . zib_get_svg('comment') . _cut_count($reply) . '</a>';
    $right .= zib_bbs_get_posts_more_dropdown($post->ID, 'relative pull-right px14 item', '', '', 'down');
    $right .= '</div>';

    $html = '<div class="flex ac jsb forum-article-meta">';
    $html .= $left;
    $html .= $right;
    $html .= '</div>';

    return $html;
}

/**
 * @description: 获取帖子页面的帖子标题
 * @param {*} $posts_id
 * @return {*}
 */
function zib_bbs_get_single_title($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }
    if (!isset($post->ID)) {
        return;
    }
    $posts_id  = $post->ID;
    $title     = get_the_title($post);
    $permalink = get_permalink($post);

    $hot      = zib_bbs_get_hot_badge($posts_id);
    $essence  = zib_bbs_get_essence_badge('jb-red mr3', $posts_id);
    $question = zib_bbs_get_question_badge('mr3', $posts_id);
    $status   = zib_bbs_get_status_badge('', $posts_id);
    $topping  = zib_bbs_get_topping_badge();

    return $status . $hot . '<h1 class="article-title"><a href="' . $permalink . '" title="' . esc_attr($title) . '">' . $topping . $essence . $question . $title . '</a></h1>';
}

/**
 * @description: 帖子页面的面包屑导航
 * @param {*}
 * @return {*}
 */
function zib_bbs_get_breadcrumbs()
{

    global $post, $zib_bbs;
    if ('forum_post' != $post->post_type || !_pz('bbs_breadcrumbs_s', true)) {
        return;
    }

    $plate_id = zib_bbs_get_plate_id($post->ID);

    $html = '';
    $icon = '<i class="fa fa-map-marker"></i> ';

    if (_pz('bbs_breadcrumbs_home', true)) {
        $html .= '<li><a href="' . home_url() . '">' . $icon . '首页</a></li>';
        $icon = '';
    }

    if (_pz('bbs_breadcrumbs_bbs_home', true)) {
        $home_name = _pz('bbs_breadcrumbs_bbs_home_name') ?: '社区';
        $home_url  = zib_bbs_get_home_url();
        $html .= '<li><a href="' . $home_url . '">' . $icon . $home_name . '</a></li>';
        $icon = '';
    }

    if ($plate_id && _pz('bbs_breadcrumbs_plate_cat', true)) {
        $plate_cat_link = zib_bbs_get_plate_cat_link($plate_id);
        $html .= $plate_cat_link ? '<li>' . $icon . $plate_cat_link . '</li>' : '';
        $icon = '';
    }

    $plate      = get_post($plate_id);
    $plate_name = $plate->post_title;
    $plate_url  = get_permalink($plate);
    $html .= '<li><a href="' . $plate_url . '"> ' . $icon . $plate_name . '</a></li>';

    $html .= '<li>正文</li>';

    return '<ul class="breadcrumb">' . $html . '</ul>';
}

/**
 * @description: 挂钩论坛帖子页面显示移动端底部tab
 * @param {*} $btn
 * @return {*}
 */
function zib_bbs_get_single_footer_tabbar($btn)
{
    $opt = _pz('footer_tabbar_single');
    if (!is_single() || !isset($opt['s']) || 'extend' != $opt['s']) {
        return $btn;
    }

    global $post;
    if (!isset($post->post_type) || 'forum_post' != $post->post_type) {
        return $btn;
    }

    $posts_id = $post->ID;
    $btn      = '';

    //评论

    $btn .= zib_bbs_get_score_box($posts_id, 'tabbar-item single-action-tabbar bbs-bar', false, false); //加分
    $btn .= zib_bbs_get_posts_favorite_btn($posts_id, 'tabbar-item single-action-tabbar bbs-bar', '', ''); //收藏
    $btn .= zib_bbs_get_posts_share_btn($post, 'tabbar-item single-action-tabbar bbs-bar', true); //分享

    if (apply_filters('zibpay_is_show_paybutton', false)) {
        $btn .= '<a class="tabbar-item but jb-red single-pay-tabbar" href="javascript:(scrollTopTo(\'#posts-pay\',-50));">立即购买</a>';
        $comment_count = _cut_count(get_comments_number($posts_id));
        $comment_count = $comment_count ?: '';
        $comments_btn  = '<a href="javascript:;" class="tabbar-item single-action-tabbar" fixed-input="#respond">' . zib_get_svg('comment') . '<count>' . $comment_count . '</count></a>';

    } else {
        $c_placeholder = esc_attr(_pz('bbs_comment_placeholder'));

        $comments_btn = zib_get_respond_mobile('#respond', $c_placeholder, 'tabbar-item', true); //评论
    }

    return $comments_btn . $btn;
    return $btn;
}

add_filter('footer_tabbar', 'zib_bbs_get_single_footer_tabbar');
