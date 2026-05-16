<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-23 23:16:33
 * @LastEditTime : 2026-04-08 20:53:19
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|发帖页面相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_bbs_posts_edit_page_content()
{
    $user_id = get_current_user_id();
    if ($user_id) {
        //已经登录
        $is_edit = (int) get_query_var('forum_post_edit');
        $is_can  = true;
        if ($is_edit) {
            //如果没有此文章的编辑权限
            if (!zib_bbs_current_user_can('posts_edit', $is_edit)) {
                $post   = get_post($is_edit);
                $is_can = false;

                if (isset($post->post_author) && $post->post_author == $user_id) {
                    $roles_lists = zib_bbs_get_cap_roles_lists('posts_edit');
                } else {
                    $roles_lists = zib_bbs_get_cap_roles_lists('posts_edit_other');
                }

                echo '<div class="zib-widget full-widget-sm" style="min-height:60vh;">';
                echo zib_get_null('抱歉！您暂无编辑此内容的权限', 60, 'null-cap.svg', '');
                echo $roles_lists ? '<div class="text-center"><div class="mb20 muted-2-color">成为以下用户组可编辑此内容</div>' . $roles_lists . '</div>' : '';
                echo '</div>';
            }

        } else {
            if (!zib_bbs_current_user_can('posts_add')) {
                //如果没有添加文章的权限
                $is_can = false;
                echo '<div class="zib-widget full-widget-sm" style="min-height:60vh;">';
                echo zib_bbs_get_nocan_info($user_id, 'posts_add', '无法发布');
                echo '</div>';
            }
        }

        if ($is_can) {
            zib_bbs_posts_edit_content();
        }

    } else {
        //没有登录
        echo '<div class="zib-widget full-widget-sm flex jc" style="min-height:60vh;">';
        echo zib_get_user_singin_page_box('box-body flex1', 'Hi！请先登录');
        echo '</div>';
    }
}
add_action('bbs_posts_edit_page_content', 'zib_bbs_posts_edit_page_content');

//页面主要内容显示
function zib_bbs_posts_edit_content()
{
    $is_edit = (int) get_query_var('forum_post_edit');
    $in_args = zib_bbs_edit::posts_in($is_edit);

    echo '<div class="zib-widget full-widget-sm editor-main-box" style="min-height:60vh;">';
    echo zib_bbs_edit::posts_featured($in_args['post_id']);
    echo zib_bbs_edit::posts_title($in_args['title']);
    zib_bbs_edit::posts_editor($in_args);
    echo zib_bbs_edit::desc_text($in_args); //提交按钮
    echo '</div>';
}

//侧边栏
function zib_bbs_posts_edit_sidebar_dynamic_top()
{
    dynamic_sidebar('bbs_new_posts_sidebar_top');
}
add_action('bbs_posts_edit_page_sidebar', 'zib_bbs_posts_edit_sidebar_dynamic_top', 5);

function zib_bbs_posts_edit_sidebar()
{
    $is_edit = (int) get_query_var('forum_post_edit');
    $in_args = zib_bbs_edit::posts_in($is_edit);

    echo zib_bbs_edit::posts_submit($in_args); //提交按钮
    echo zib_bbs_edit::status($in_args);
    echo zib_bbs_edit::allow_view_set($in_args['post_id']);
    echo zib_bbs_edit::plate_select($in_args);
    echo zib_bbs_edit::topic_select($in_args['topic']);
    echo zib_bbs_edit::tag_select($in_args['tag']);
    echo zib_bbs_edit::type_select($in_args['post_id']);
    echo zib_bbs_edit::vote_set($in_args['post_id']);
}
add_action('bbs_posts_edit_page_sidebar', 'zib_bbs_posts_edit_sidebar');

function zib_bbs_posts_edit_sidebar_dynamic_bottom()
{
    dynamic_sidebar('bbs_new_posts_sidebar_bottom');
}
add_action('bbs_posts_edit_page_sidebar', 'zib_bbs_posts_edit_sidebar_dynamic_bottom', 50);

add_action('bbs_locate_template_posts_edit', function () {
    //显示侧边栏
    add_filter('zib_is_show_sidebar', function () {
        return !wp_is_mobile();
    });

    //不显示悬浮按钮
    remove_action('wp_footer', 'zib_float_right');

    //不显示底部按钮
    remove_action('wp_footer', 'zib_footer_tabbar');
});

//获取发布帖子的链接url
function zib_bbs_get_posts_edit_url($id = 'add')
{
    if (get_option('permalink_structure')) {
        global $zib_bbs;
        $rewrite_slug = $zib_bbs->posts_edit_rewrite_slug;
        if ((int) $id) {
            $rewrite_slug .= '/' . (int) $id;
        }
        return home_url($rewrite_slug);
    }

    return add_query_arg('forum_post_edit', $id, home_url());
}
