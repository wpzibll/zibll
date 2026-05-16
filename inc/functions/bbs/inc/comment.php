<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-11 15:33:51
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|回复类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 加载页面评论的主要函数
 * @param {*}
 * @return {*}
 */
function zib_bbs_comments_template()
{
    $file = ZIB_BBS_REQUIRE_URI . 'template/comments.php';
    comments_template($file, true);
}

function zib_bbs_get_respond()
{

    global $post, $current_user, $comment_author;
    $comments_open       = comments_open($post);
    $comments_open_class = $comments_open ? '' : ' comments-closed';

    $user_id = get_current_user_id();

    if (!$user_id) {
        //未登录
        $html = '';
        $html .= '<div class="text-center box-body">';
        $html .= '<div class="mb20 muted-3-color">请登录后发表评论</div>';
        $html .= '<p>';
        $html .= '<a href="javascript:;" class="signin-loader but c-blue padding-lg"><i class="fa fa-fw fa-sign-in mr10" aria-hidden="true"></i>登录</a>';
        $html .= !zib_is_close_signup() ? '<a href="javascript:;" class="signup-loader ml10 but c-yellow padding-lg"><i data-class="icon mr10" data-viewbox="0 0 1024 1024" data-svg="signup" aria-hidden="true"></i>注册</a>' : '';
        $html .= '</p>';
        $html .= '<div class="social_loginbar">';
        $html .= zib_social_login(false);
        $html .= '</div>';
        $html .= '</div>';

        return '<div id="respond" class="zib-widget' . $comments_open_class . '"><form id="commentform">' . $html . '</form></div>';
    }

    //权限判断
    if (!zib_bbs_current_user_can('comment_add')) {
        return '<div id="respond" class="zib-widget' . $comments_open_class . '"><form id="commentform">' . zib_bbs_get_nocan_info($user_id, 'comment_add', '暂时无法评论', 10, 200) . '</form></div>';
    }

    $placeholder = _pz('bbs_comment_placeholder');
    $post_id     = $post->ID;
    $textarea    = '<textarea placeholder="' . $placeholder . '" autoheight="true" maxheight="188" class="form-control grin" name="comment" id="comment" cols="100%" rows="4" tabindex="1"></textarea>';

    //人机验证
    if (_pz('verification_comment_s')) {
        $verification_input = zib_get_machine_verification_input('submit_comment');
        if ($verification_input) {
            $textarea .= '<div style="width: 230px;">' . $verification_input . '</div>';
        }
    }

    $hidden     = get_comment_id_fields($post_id);
    $hidden     .= zib_nonce_field('submit_comment');
    $avatar_img = zib_get_data_avatar($user_id);
    $vip_icon   = zibpay_get_vip_icon(zib_get_user_vip_level($user_id), 'em12 mr3');
    $vip_icon   = $vip_icon ? $vip_icon : '';

    $user = '<div class="comt-avatar mb10">' . $avatar_img . '</div>';
    $user .= '<p class="text-ellipsis muted-2-color">' . $vip_icon . $current_user->display_name . '</p>';
    $user = '<div class="comt-title text-center flex0 mr10">' . $user . '</div>';

    $btns_l = zib_bbs_get_respond_btns();
    $btns_l = '<div class="comt-tips-left">' . $btns_l . '</div>';

    $btns_r = '<a class="but c-red mr6" id="cancel-comment-reply-link" href="javascript:;">取消</a>';
    $btns_r .= '<button class="but c-blue pw-1em input-expand-submit comment-send" name="submit" id="submit" tabindex="2">提交回复</button>';
    $btns_r = '<div class="comt-tips-right flex0">' . $btns_r . '</div>';

    $html = '';
    $html .= '<div class="flex ac">';
    $html .= $user; //头像
    $html .= '<div class="comt-box grow1">';
    $html .= '<div class="action-text mb10 em09 muted-2-color"></div>';
    $html .= $textarea;
    $html .= $hidden;
    $html .= '<div class="ccomt-ctrl flex jsb">';
    $html .= $btns_l;
    $html .= $btns_r;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return '<div id="respond" class="mobile-fixed zib-widget' . $comments_open_class . '"><div class="fixed-body"></div><form id="commentform">' . $html . '</form></div>';
}

/**
 * @description: 获取回复模块编辑器扩展按钮
 * @param {*}
 * @return {*}
 */
function zib_bbs_get_respond_btns()
{

    $html = '';
    if (_pz('bbs_comment_smilie', true)) {
        $html .= zib_get_input_expand_but('smilie');
    }
    if (_pz('bbs_comment_code', true)) {
        $html .= zib_get_input_expand_but('code');
    }
    if (_pz('bbs_comment_img', true)) {
        $html .= zib_get_input_expand_but('image', _pz('bbs_comment_upload_img', false), 'comment');
    }
    if (_pz('bbs_comment_quick_s', true)) {
        $html .= zib_get_input_expand_but('quick', _pz('bbs_comment_quick_often'), 'comment');
    }
    return $html;
}

/**
 * @description: 获取回复模块的标题
 * @param {*}
 * @return {*}
 */
function zib_bbs_get_comment_title()
{

    $corderby = !empty($_GET['corderby']) ? $_GET['corderby'] : '';

    global $wp_rewrite, $post;
    $url        = add_query_arg('cpage', false, zib_get_current_url()); //筛选 过滤
    $url        = preg_replace("/\/$wp_rewrite->comments_pagination_base-([0-9]{1,})/", '', $url);
    $title_name = zib_bbs_get_comment_title_name($post);

    $item = '<li class="mr6' . (!$corderby || 'comment_date_gmt' == $corderby ? ' active' : '') . '"></li>';
    $item .= '<li class="' . ('comment_like' == $corderby ? 'active' : '') . '"></li>';

    $order = '<a rel="nofollow" class="but comment-orderby' . (!$corderby || 'comment_date_gmt' == $corderby ? ' b-theme' : '') . '" href="' . esc_url(add_query_arg('corderby', 'comment_date_gmt', $url)) . '">最新</a>';
    $order .= '<a rel="nofollow" class="but comment-orderby' . ('comment_like' == $corderby ? ' b-theme' : '') . '" href="' . esc_url(add_query_arg('corderby', 'comment_like', $url)) . '">最热</a>';
    $order = '<div class="comment-order-box but-average radius em09 shrink0">' . $order . '</div>';

    //仅仅显示作者
    $author_id        = $post->post_author;
    $only_author      = !empty($_GET['only_author']);
    $only_author_text = $only_author ? '查看全部' : '只看作者';
    $only_author      = '<a rel="nofollow" class="but comment-orderby btn-only-author p2-10 em09 ml10" href="' . esc_url(add_query_arg('only_author', ($only_author ? false : $author_id), $url)) . '">' . $only_author_text . '</a>';

    $count_t = _cut_count($post->comment_count);
    $title   = '<div class="title-theme">' . $title_name . '<badge class="ml6 c-gray">' . $count_t . '</badge></div>';

    $html = '';
    $html .= '<div class="comment-filter flex ac jsb" win-ajax-replace="comment-order-btn">'; //筛选
    $html .= '<div class="flex ac shrink0">';
    $html .= $title;
    $html .= $only_author;
    $html .= '</div>';

    $html .= $order;
    $html .= '</div>';

    return $html;
}

/**
 * @description: 获取回答采纳的按钮 采纳此回答
 * @param {*} $plate_id
 * @param {*} $cat_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_comment_adopt_link($comment, $class = 'but but-sm c-cyan', $con = '采纳此回答', $tag = 'span')
{

    $comment = get_comment($comment);
    //评论状态判断
    if (!isset($comment->comment_ID) || wp_get_comment_status($comment) !== 'approved') {
        return;
    }
    $posts_id   = $comment->comment_post_ID;
    $comment_id = $comment->comment_ID;
    //权限判断
    if (!zib_bbs_current_user_can('question_answer_adopt', $posts_id)) {
        return;
    }

    //已经被采纳判断
    $is_adopted = get_comment_meta($comment_id, 'adopted', true);
    if ($is_adopted) {
        return;
    }

    $class .= ' answer-adopt  answer-adopt-id-' . $comment_id;

    $url_var = array(
        'action' => 'answer_adopt_modal',
        'id'     => $comment_id,
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

//已经采纳的徽章
function zib_bbs_get_comment_adopted_badeg($comment_id, $class = '')
{
    $is_adopted = get_comment_meta($comment_id, 'adopted', true);
    if (!$is_adopted) {
        return;
    }
    return '<span class="badge-question jb-cyan' . $class . '" title="已采纳该回答" data-toggle="tooltip"><i class="fa fa-check-circle"></i><text>已采纳</text></span>';
}

//获取将回答设置为已采纳的确认模态框
function zib_bbs_get_answer_adopt_modal($comment_id)
{
    $header = zib_get_modal_colorful_header('jb-cyan', '<i class="fa fa-check-circle em12"></i>', '采纳该回答');

    $form = '<form>';
    $form .= '<div class="mb10"><b>如果此回答对您有用，请采纳此回答</b></div>';
    $form .= '<div class="mb20"><textarea class="form-control" name="desc" tabindex="1" placeholder="给该回答作者留言" rows="2"></textarea></div>';
    $form .= '<div class="mt20 but-average">
                    <input type="hidden" name="action" value="answer_adopt">
                    <input type="hidden" name="comment_id" value="' . $comment_id . '">
                    ' . wp_nonce_field('answer_adopt', '_wpnonce', false, false) . '
                    <button type="button" data-dismiss="modal" href="javascript:;" class="but">取消</button>
                    <button class="but c-blue wp-ajax-submit answer-adopt-submit"><i class="fa fa-check" aria-hidden="true"></i>确认采纳</button>
                </div>';
    $form .= '</form>';

    return $header . $form;
}

//执行采纳回答
function zib_bbs_answer_adopt($comment = 0, $desc = '')
{

    if (!is_object($comment)) {
        $comment = get_comment($comment);
    }

    $user_id    = $comment->user_id;
    $comment_id = $comment->comment_ID;
    $post_id    = $comment->comment_post_ID;

    update_post_meta($post_id, 'question_status', 1);
    update_comment_meta($comment_id, 'adopted', 1);

    do_action('answer_adopted', $comment, $desc); //添加挂钩

    return $comment;
}

//为评论底部添加采纳回答的按钮
function zib_bbs_comment_footer_info_add_question_btn($info, $comment, $depth)
{
    $comment_id = $comment->comment_ID;
    if ((int) $depth <= 1) {
        $posts_id = $comment->comment_post_ID;
        $bbs_type = get_post_meta($posts_id, 'bbs_type', true); //类型判断
        if ('question' === $bbs_type) {
            $adopted_badeg = zib_bbs_get_comment_adopted_badeg($comment_id);
            if ($adopted_badeg) {
                $info .= $adopted_badeg;
            } else {
                $info .= zib_bbs_get_comment_adopt_link($comment);
            }
        }
    }
    return $info;
}

add_filter('comment_footer_info', 'zib_bbs_comment_footer_info_add_question_btn', 10, 3);

//获取帖子评论的标题名称
function zib_bbs_get_comment_title_name($post = null)
{
    global $zib_bbs;
    $neme = $zib_bbs->comment_name;

    $bbs_type = zib_get_posts_bbs_type($post); //类型判断
    if ('question' === $bbs_type) {
        $neme = '回答';
    }

    return $neme;
}

//输出评论分页
function zib_bbs_comment_paginate()
{
    echo zib_get_comment_paginate(_pz('comment_paginate_type'), _pz('comment_paging_ajax_ias_s'), _pz('comment_paging_ajax_ias_max', 3));
}

//获取帖子的热门评论，显示在列表的
function zib_bbs_get_posts_lists_hot_comment($posts_id = 0)
{
    if ($posts_id) {
        $posts_id = get_the_ID();
    }

    //第一步通过缓存获取
    $cache = wp_cache_get($posts_id, 'posts_lists_hot_comment', true);
    if (false !== $cache) {
        return $cache;
    }

    $args = array(
        'number'     => 1,
        'post_id'    => $posts_id,
        'status'     => 1,
        'type'       => 'comment',
        'meta_query' => array(
            array(
                'key'   => 'is_hot',
                'value' => 1,
            ),
        ),
    );
    $comments = get_comments($args);
    $html     = '';
    if (!empty($comments[0])) {
        $comment = $comments[0];
        $html    = '<div class="hot-comment mt10">';
        $html .= zib_get_comment_header($comment);
        $html .= '<div class="comment-content">' . zib_comment_filters(get_comment_text($comment)) . '</div>';
        $html .= '</div>';
    }

    //添加缓存，长期有效
    wp_cache_set($posts_id, $html, 'posts_lists_hot_comment');

    return $html;
}

//获取神评的徽章
function zib_bbs_get_comment_hot_badge($id, $class = '')
{
    $is_hot = get_comment_meta($id, 'is_hot', true);
    if (!$is_hot) {
        return;
    }

    $class     = $class ? ' ' . $class : '';
    $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="lazyload fit-cover" src="' . zib_get_lazy_thumb() . '" data-' : '';
    $html      = '<span class="img-badge top badge-status comment-hot' . $class . '"><img ' . $lazy_attr . 'src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/hot-comment.svg" alt="热门评论"></span>';
    return $html;
}

//热门评论判断并设置
function zib_bbs_updata_comment_is_hot($comment_id)
{
    $comment = get_comment($comment_id);
    if (!$comment) {
        return;
    }

    $post = get_post($comment->comment_post_ID);
    if (!isset($post->post_type) || 'forum_post' !== $post->post_type) {
        return; //不是论坛的文章类型退出
    }

    $like_min = _pz('is_hot_comment', 10, 'like');
    //查找评论
    $args = array(
        'number'     => 1,
        'post_id'    => $post->ID,
        'status'     => 1,
        'type'       => 'comment',
        'order'      => 'DESC',
        'orderby'    => 'meta_value_num comment_date_gmt',
        'meta_key'   => 'comment_like',
        'meta_query' => array(
            array(
                'key'     => 'comment_like',
                'value'   => $like_min,
                'compare' => '>=',
            ),
        ),
    );
    $comments = get_comments($args);
    if (!empty($comments[0]->comment_ID)) {
        $args = array(
            'number'     => -1,
            'post_id'    => $post->ID,
            'status'     => 1,
            'type'       => 'comment',
            'order'      => 'DESC',
            'fields'     => 'ids',
            'meta_query' => array(
                array(
                    'key'   => 'is_hot',
                    'value' => 1,
                ),
            ),
        );
        $cancel = get_comments($args); //查找之前的热门并取消
        if (!empty($cancel[0])) {
            update_comment_meta($cancel[0], 'is_hot', 0);
        }
        update_comment_meta($comments[0]->comment_ID, 'is_hot', 1); //设置为热门
        do_action('comment_is_hot', $comments[0]); //添加挂钩
        //刷新缓存
        wp_cache_delete($post->ID, 'posts_lists_hot_comment');
        zib_bbs_get_posts_lists_hot_comment($post->ID);
    }
}

add_action('like-comment', 'zib_bbs_updata_comment_is_hot');

//为评论添加神评徽章
add_filter('comment_header', function ($html, $comment) {
    $post_type = get_post_type($comment->comment_post_ID);
    if ($post_type !== 'forum_post') {
        return $html;
    }

    $html = zib_bbs_get_comment_hot_badge($comment->comment_ID) . $html;
    return $html;
}, 10, 2);

//为评论的姓名添加版主显示
add_filter('comments_user_name_badge', function ($badge, $comment) {
    if (isset($comment->comment_post_ID) && !empty($comment->user_id)) {
        $badge .= zib_bbs_get_user_moderator_badge($comment->user_id, $comment->comment_post_ID, 'ml3 flex0');
    }
    return $badge;
}, 10, 2);

//评论按钮显示设置神评论
function zib_bbs_comment_action_set_hot($lists, $comment)
{

    $post_type = get_post_type($comment->comment_post_ID);
    if ($post_type !== 'forum_post') {
        return $lists;
    }

    if (zib_bbs_current_user_can('comment_set_hot', $comment)) {
        $is_hot = get_comment_meta($comment->comment_ID, 'is_hot', true);
        $text   = !$is_hot ? '设为神评' : '取消神评'; //参数
        $lists  = '<li><a class="wp-ajax-submit" form-action="comment_set_hot" form-data="' . esc_attr(json_encode(['id' => $comment->comment_ID, '_wpnonce' => wp_create_nonce('comment_set_hot')])) . '" href="javascript:;">' . zib_get_svg('hot', null, 'mr10 fa-fw') . $text . '</a></li>' . $lists . '';
    }

    return $lists;
}
add_filter('comments_action_lists', 'zib_bbs_comment_action_set_hot', 10, 2);
