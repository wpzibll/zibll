<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-07 12:48:54
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|用户类函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取用户允许编辑的分类ID
function zib_bbs_get_user_can_plate_cat($is_edit = false)
{

    $query = array(
        'taxonomy'   => 'plate_cat', //分类法
        'order'      => 'DESC',
        'count'      => true,
        'hide_empty' => false,
        'orderby'    => 'count',
    );

    $new_query = new WP_Term_Query($query);
    if (!is_wp_error($new_query) && !empty($new_query->terms)) {
        return $new_query->terms;
    }

    return false;
}

//获取用户允许选择的版块类型
function zib_bbs_get_user_can_plate_type($is_edit = false)
{
    //暂未启用此函数的判断功能
    return array();
    //未启用
    $user_id = get_current_user_id();

    $all = array(
        ''      => __('标准', 'zibll'),
        'image' => __('图片', 'zibll'),
        'video' => __('视频', 'zibll'),
    );

    return $all;
}

//获取用户有多个版块
function zib_bbs_get_user_plate_count($user_id = 0, $status = 'publish', $cut = true)
{
    if (!$user_id) {
        return 0;
    }

    $count = zib_get_user_post_count($user_id, $status, 'plate');
    return $cut ? _cut_count($count) : $count;
}

//获取用户有多个帖子
function zib_bbs_get_user_posts_count($user_id = 0, $status = 'publish', $cut = true)
{
    if (!$user_id) {
        return 0;
    }

    $count = zib_get_user_post_count($user_id, $status, 'forum_post');
    return $cut ? _cut_count($count) : $count;
}

//获取用户管理的版块的数量
function zib_bbs_get_user_moderator_plate_count($user_id, $_cut = true)
{
    $moderator_plate = zib_bbs_get_user_plate_moderator_ids($user_id);
    $count_all       = count($moderator_plate);
    if ($_cut) {
        return _cut_count($count_all);
    } else {
        return $count_all;
    }
}

//获取用户版块关注的数量
function zib_bbs_get_user_follow_plate_count($user_id, $_cut = true)
{
    if (!$user_id) {
        return;
    }

    $cache_num = wp_cache_get($user_id, 'user_favorite_plate_count', true);
    if (false !== $cache_num) {
        $count_all = $cache_num;
    } else {
        $favorite_ids = zib_get_user_meta($user_id, 'follow_plate', true);
        if ($favorite_ids) {
            $args = array(
                'post_type'      => 'plate',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'paged'          => 0,
                'fields'         => 'ids',
                'post__in'       => $favorite_ids,
            );
            $the_query = new WP_Query($args);
            $count_all = $the_query->found_posts;
            wp_reset_query();
            //添加缓存
            wp_cache_set($user_id, $count_all, 'user_favorite_plate_count');
        } else {
            $count_all = '0';
        }
    }

    if ($_cut) {
        return _cut_count($count_all);
    } else {
        return $count_all;
    }
}

//刷新缓存
function zib_bbs_favorite_plate_cache_delete($plate_id, $user_id)
{
    wp_cache_delete($user_id, 'user_favorite_plate_count');
    zib_bbs_get_user_follow_plate_count($user_id);
}
add_action('bbs_follow_plate', 'zib_bbs_favorite_plate_cache_delete', 10, 2);

//获取用户帖子关注的数量
function zib_bbs_get_user_favorite_posts_count($user_id, $_cut = true)
{
    if (!$user_id) {
        return;
    }

    $cache_num = wp_cache_get($user_id, 'user_favorite_posts_count', true);
    if (false !== $cache_num) {
        $count_all = $cache_num;
    } else {
        $favorite_ids = zib_get_user_meta($user_id, 'favorite_forum_posts', true);

        if ($favorite_ids) {
            $args = array(
                'post_type'      => 'forum_post',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'paged'          => 0,
                'post__in'       => $favorite_ids,
                'fields'         => 'ids',
            );
            $the_query = new WP_Query($args);

            $count_all = $the_query->found_posts;
            wp_reset_query();
            //添加缓存
            wp_cache_set($user_id, $count_all, 'user_favorite_posts_count');
        } else {
            $count_all = '0';
        }
    }

    if ($_cut) {
        return _cut_count($count_all);
    }
    return $count_all;
}

//刷新缓存
function zib_bbs_favorite_posts_cache_delete($posts_id, $user_id)
{
    wp_cache_delete($user_id, 'user_favorite_posts_count');
    zib_bbs_get_user_favorite_posts_count($user_id);
}
add_action('bbs_favorite_posts', 'zib_bbs_favorite_posts_cache_delete', 10, 2);

//获取用户版块列表
function zib_bbs_get_user_plate_lists($user_id = 0, $paged = 1, $orderby = 'date', $post_status = 'publish')
{
    if (!$user_id) {
        return;
    }

    $current_user_id = get_current_user_id();
    $ajax_url        = add_query_arg(array('user_id' => $user_id, 'action' => 'author_plate', 'orderby' => $orderby, 'status' => $post_status), admin_url('admin-ajax.php'));
    $posts_per_page  = 12;
    $args            = array(
        'post_type'      => 'plate',
        'author'         => $user_id,
        'post_status'    => $post_status,
        'order'          => 'DESC',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );

    //兼容follow关注的和moderator管理的
    if ('follow' === $post_status) {
        $args['post_status'] = 'publish';
        unset($args['author']);
        $follow = zib_get_user_meta($user_id, 'follow_plate', true);
        if ($follow) {
            $args['post__in'] = $follow;
        } else {
            return zib_get_ajax_null();
        }
    }

    if ('moderator' === $post_status) {
        $args['post_status'] = 'publish';
        unset($args['author']);
        $args['meta_query'] = array(
            array(
                'key'     => 'moderator',
                'value'   => ':"' . $user_id . '"',
                'compare' => 'LIKE',
            ),
        );
    }

    if (!$current_user_id || $current_user_id != $user_id) {
        $args['post_status'] = 'publish';
    }
    $args      = zib_bbs_query_orderby_filter($orderby, $args);
    $new_query = new WP_Query($args);

    if ('follow' === $post_status) {
        wp_cache_set($user_id, $new_query->found_posts, 'user_favorite_plate_count');
    } elseif ('moderator' === $post_status) {
    } else {
        wp_cache_set($user_id . '_' . $post_status, $new_query->found_posts, 'post_count_plate');
    }

    $html  = '';
    $lists = '';
    if ($new_query->have_posts()) {
        $lists = '<div class="plate-lists ajax-item">';
        while ($new_query->have_posts()): $new_query->the_post();
            $lists .= zib_bbs_get_main_plate();
        endwhile;
        $lists .= '</div>';
    }
    wp_reset_query();

    if (!$lists) {
        $lists = zib_get_ajax_null();
    }
    $paginate = zib_get_ajax_next_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url);

    return $lists . $paginate;
}

//获取用户帖子列表
function zib_bbs_get_user_posts_lists($user_id = 0, $paged = 1, $orderby = 'date', $post_status = 'publish')
{
    if (!$user_id) {
        return;
    }

    $current_user_id = get_current_user_id();
    $ajax_url        = add_query_arg(array('user_id' => $user_id, 'action' => 'author_forum_posts', 'orderby' => $orderby, 'status' => $post_status), admin_url('admin-ajax.php'));
    $posts_per_page  = 12;

    $args = array(
        'post_type'      => 'forum_post',
        'author'         => $user_id,
        'post_status'    => $post_status,
        'order'          => 'DESC',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );

    if (!$current_user_id || ($current_user_id != $user_id && !is_super_admin())) {
        $args['post_status'] = 'publish';
    }

    //兼容follow关注的和moderator管理的
    if ('favorite' === $post_status) {
        $args['post_status'] = 'publish';
        unset($args['author']);
        $follow = (array) zib_get_user_meta($user_id, 'favorite_forum_posts', true);
        if ($follow) {
            $args['post__in'] = array_reverse($follow);
        } else {
            return zib_get_ajax_null('暂无收藏内容');
        }
    }

    $args      = zib_bbs_query_orderby_filter($orderby, $args);
    $new_query = new WP_Query($args);

    if ('favorite' === $post_status) {
        wp_cache_set($user_id, $new_query->found_posts, 'user_favorite_posts_count');
    } else {
        wp_cache_set($user_id . '_' . $post_status, $new_query->found_posts, 'post_count_forum_post');
    }

    $lists = '';
    $style = 'detail';
    if ($new_query->have_posts()) {
        while ($new_query->have_posts()): $new_query->the_post();
            if ('detail' == $style) {
                $lists .= zib_bbs_get_posts_list();
            } else {
                $lists .= zib_bbs_get_posts_mini_list();
            }
        endwhile;
    }
    wp_reset_query();

    if (!$lists) {
        $lists = zib_get_ajax_null();
    }
    $paginate = zib_get_ajax_next_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url);

    return $lists . $paginate;
}

/**
 * @description: 判断用户是否是对应的角色，通用版，不判断是否属于自己管理
 * @param {*} $user_id
 * @param {*} $roles 角色数组
 * @return {*}
 */
function zib_bbs_is_can_roles($user_id, $cap_roles = array())
{
    if ($user_id && !empty($cap_roles['cat_moderator'])) {
        //分区版主判断
        if (zib_bbs_get_user_cat_moderator_ids($user_id)) {
            return true;
        }
    }
    if ($user_id && !empty($cap_roles['plate_author'])) {
        //超级版主判断
        if (zib_bbs_get_user_plate_author_ids($user_id)) {
            return true;
        }
    }
    if ($user_id && !empty($cap_roles['moderator'])) {
        //版主判断
        if (zib_bbs_get_user_plate_moderator_ids($user_id)) {
            return true;
        }
    }

    return false;
}

function zib_bbs_is_can_roles_filter($false, $user_id, $cap_roles)
{
    return zib_bbs_is_can_roles($user_id, $cap_roles);
}
add_filter('is_can_roles', 'zib_bbs_is_can_roles_filter', 10, 3);

function zib_bbs_user_can_filter($is_can, $user_id, $capability, $args)
{
    if (!$is_can) {
        switch ($capability) {
            case 'set_user_ban':
                if (zib_bbs_user_is_forum_admin($user_id)) {
                    return true;
                }
                break;
        }
    }
    return $is_can;
}
add_filter('zib_user_can', 'zib_bbs_user_can_filter', 10, 4);

/**
 * @description: 获取拥有能力的角色数组
 * @param {*} $capability 能力key名称
 * @param {array} $default
 * @return {*}
 */
function zib_bbs_get_cap_roles($capability, array $default = array())
{
    $capability = 'bbs_' . $capability;
    return zib_get_cap_roles($capability, $default);
}

/**
 * @description: 获取一个能力的拥有者列表
 * @param {*} $capability
 * @return {*}
 */
function zib_bbs_get_cap_roles_lists($capability, $class = 'badg mm3')
{
    $capability = 'bbs_' . $capability;
    return zib_get_cap_roles_lists($capability, $class);
}

//添加挂钩显示角色列表的内容
function zib_bbs_hascaps_roles_lists_filter($lists, $roles, $class)
{

    global $zib_bbs;
    //分区版主
    if (!empty($roles['cat_moderator'])) {
        $lists .= '<span class="c-green-2 ' . $class . '">' . $zib_bbs->cat_moderator_name . '</span>';
    }
    //超级版主
    if (!empty($roles['plate_author'])) {
        $lists .= '<span class="c-blue-2 ' . $class . '">' . $zib_bbs->plate_author_name . '</span>';
    }
    //版主
    if (!empty($roles['moderator'])) {
        $lists .= '<span class="c-blue ' . $class . '">' . $zib_bbs->plate_moderator_name . '</span>';
    }
    return $lists;
}
add_filter('hascaps_roles_lists', 'zib_bbs_hascaps_roles_lists_filter', 10, 3);

function zib_bbs_get_nocan_info($user_id, $capability, $msg = '', $margin = 30, $width = 280, $height = 0)
{
    return zib_get_nocan_info($user_id, 'bbs_' . $capability, $msg, $margin, $width, $height);
}

/**
 * @description: 判断当前登录用户是否拥有某个权限
 * @param {*} $capability
 * @param {array} $args
 * @return {*}
 */
function zib_bbs_current_user_can($capability, ...$args)
{
    return zib_bbs_user_can(get_current_user_id(), $capability, ...$args);
}

//用户权限判断
function zib_bbs_user_can($user_id, $capability, ...$args)
{
    if (!$user_id) {
        //论坛权限需要登录
        return false;
    }

    //第一步最高权限，管理员判断
    if (is_super_admin($user_id) || zib_bbs_user_is_forum_admin($user_id)) {
        return true;
    }

    //第二步，用户禁封判断
    if (zib_user_is_ban($user_id)) {
        return false;
    }

    //先查询角色列表
    $cap_roles = zib_bbs_get_cap_roles($capability);

    switch ($capability) {
        case 'comment_set_hot': //将自己管理下的评论设置为神评论
            //传入第一个参数为评论
            if (!empty($args[0])) {
                $comment = get_comment($args[0]);
                if (!empty($comment->comment_post_ID)) {
                    $post = get_post($comment->comment_post_ID);
                    if (isset($post->ID)) {

                        //版主
                        if (!empty($cap_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                            return true;
                        }

                        //超级版主判断
                        if (!empty($cap_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                            return true;
                        }

                        //分区版主判断
                        if (!empty($cap_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                            return true;
                        }

                    }
                }
            }
            return false;
            break;

        //新建和编辑：判断作者拥有，自己不是作者则判断：编辑权限 “posts_edit”
        case 'posts_type_question':
        case 'posts_upload_img': //发帖上传图片
        case 'posts_upload_video': //发帖上传视频
        case 'posts_upload_file': //发帖上传文件
        case 'posts_iframe_video': //发帖嵌入视频
        case 'posts_hide': //发帖隐藏内容
        case 'posts_image_cover': //发帖允许设置帖子封面（图片封面）
        case 'posts_slide_cover': //发帖设置封面时候允许设置【幻灯片封面】
        case 'posts_video_cover': //发帖设置封面时候允许设置【视频封面】

            //我是作者，判断自己是否有这个权限。我不是作者，判断我有没有管理此内容的权限
            if (!empty($args[0])) {
                $post = get_post($args[0]);
                if (isset($post->post_author) && $post->post_author != $user_id) {
                    //传入了参数，判断是自己修改自己的，还是管理员修改他人的
                    //自己不是作者,则判断是否有此文章的编辑权限
                    return zib_bbs_user_can($user_id, 'posts_edit', $post);
                }
            }

            //未传入参数，也就是新建，直接判断新建权限
            if (zib_is_can_roles($user_id, $cap_roles)) {
                return true;
            }

            return false;
            break;

        case 'select_plate': //能否选择此版块
            if (empty($args[0])) {
                return true;
            }

            //如果有第二个参数，则是编辑状态
            if (!empty($args[1])) {
                $post = get_post($args[1]);
                if (isset($post->post_author) && $post->post_author != $user_id) {
                    //自己不是作者，则仅需查看是否有编辑状态即可
                    return zib_bbs_user_can($user_id, 'posts_edit_other', $post);
                }
            }

            $add_limit = get_post_meta($args[0], 'add_limit', true);
            if ($add_limit) {
                $_roles = zib_bbs_get_cap_roles('posts_add_limit_' . $add_limit);
                return zib_is_can_roles($user_id, $_roles);
            }
            return true;

            break;

        case 'select_plate_cat': //能否选择此版块

            if (empty($args[0])) {
                return true;
            }

            //如果有第二个参数，则是编辑状态
            if (!empty($args[1])) {
                $post = get_post($args[1]);
                if (isset($post->post_author) && $post->post_author != $user_id) {
                    //自己不是作者，则仅需查看是否有编辑状态即可
                    return zib_bbs_user_can($user_id, 'plate_edit_other', $post);
                }
            }

            $add_limit = get_term_meta($args[0], 'add_limit', true);
            if ($add_limit) {
                $_roles = zib_bbs_get_cap_roles('plate_add_limit_' . $add_limit);
                return zib_is_can_roles($user_id, $_roles);
            }
            return true;

            break;

        case 'posts_add': //发布帖子
        case 'plate_add': //创建版块
            if (!empty($args[0])) {
                if ('posts_add' === $capability) {
                    if (zib_bbs_is_the_moderator($args[0], $user_id)) {
                        //当前帖子的版主不限制
                        return true;
                    }
                    if (zib_bbs_is_the_cat_moderator($args[0], $user_id)) {
                        //当前帖子的分区版主不限制
                        return true;
                    }
                } else {
                    if (zib_bbs_is_the_cat_moderator($args[0], $user_id, true)) {
                        //当前版块的分区版主不限制
                        return true;
                    }
                }
            }

            //首先判读是否有发帖权限
            if (!zib_is_can_roles($user_id, $cap_roles)) {
                return false;
            } elseif (!empty($args[0])) {
                //有上级ID，则是否有选择上级的权限

                $_capability = 'posts_add' === $capability ? 'select_plate' : 'select_plate_cat';
                return zib_bbs_user_can($user_id, $_capability, $args[0]);
            }

            return true;
            break;

        //基础判断
        case 'posts_vote_add': //发起投票
        case 'posts_allow_view_add': //发布添加阅读权限
        case 'apply_moderator': //申请成为版主
        case 'add_url_slug': //添加的时候设置URL别名
        case 'edit_url_slug': //修改的时候设置URL别名
        case 'plate_cat_add': //添加的版块分类
        case 'forum_topic_add': //添加话题
        case 'forum_tag_add': //添加标签
        case 'comment_add': //添加评论
        case 'posts_save_audit_no': //发布帖子无需审核直接发布
        case 'posts_save_audit_no_manual': //发布帖子无需[人工审核]

            //角色判断
            if (zib_is_can_roles($user_id, $cap_roles)) {
                return true;
            }
            return false;

            break;

        //自己管理自己，非自己管理_other
        case 'posts_edit': //编辑帖子
        case 'plate_edit': //编辑版块
        case 'posts_delete': //删除帖子
        case 'plate_delete': //删除版块
        case 'posts_plate_move': //帖子移动版块
        case 'plate_plate_cat_edit': //为自己创建的版块切换版块分类
        case 'posts_allow_view_edit': //修改设置阅读权限
        case 'posts_vote_edit': //修改投票
        case 'question_answer_adopt': //采纳回答
        case 'comment_close': //关闭评论

            if (!isset($args[0])) {
                return false;
            }
            $post = get_post($args[0]);
            if (!$post) {
                return false;
            }
            if (isset($post->post_author) && $post->post_author == $user_id) {
                if ('question_answer_adopt' === $capability) {
                    return true; //只要自己是作者就可以采纳自己的评论
                }

                //如果帖子状态为草稿，则仅需要判断是否有添加权限即可
                if ($post->post_status === 'draft' && in_array($capability, ['posts_edit', 'posts_plate_move', 'posts_allow_view_edit', 'posts_vote_edit'])) {
                    $_capability = $capability === 'posts_plate_move' ? 'posts_add' : str_replace('_edit', '_add', $capability);
                    return zib_bbs_user_can($user_id, $_capability);
                }

                //角色判断
                if (zib_is_can_roles($user_id, $cap_roles)) {
                    return true;
                }

            } else {
                //如果自己不是作者，则检查用户是否是该版块或者帖子的版主、超级版主、管理员
                $_roles = zib_bbs_get_cap_roles($capability . '_other');
                if (strstr($capability, 'posts')) {

                    //编辑帖子
                    //版主判断
                    if (!empty($_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                        return true;
                    }

                    //超级版主判断
                    if (!empty($_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                        return true;
                    }

                    //分区版主判断
                    if (!empty($_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                        return true;
                    }

                } else {

                    //编辑版块
                    //版主判断
                    if (!empty($_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                        return true;
                    }

                    //分区版主判断
                    if (!empty($_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                        return true;
                    }
                }
            }
            break;

        case 'plate_cat_set_add_limit': //版块分类设置版块创建限制
            if (empty($args[0])) {
                //没有版块分类的ID，则为新建版块
                if (zib_is_can_roles($user_id, $cap_roles)) {
                    return true;
                }
            } else {
                //有版块分类的ID，则为修改版块
                $term_id = $args[0];
                //判断是不是分区版主
                if (zib_bbs_is_the_cat_moderator($term_id, $user_id, true)) {
                    if (zib_is_can_roles($user_id, $cap_roles)) {
                        return true;
                    }
                }

                $term_author = get_term_meta($term_id, 'term_author', true);
                if ($term_author && $term_author == $user_id) {
                    //自己就是创建者
                    if (zib_is_can_roles($user_id, $cap_roles)) {
                        return true;
                    }
                }
            }
            break;

        case 'posts_allow_view_pay': //付费阅读
        case 'posts_allow_view_points': //支付积分阅读
        case 'plate_set_allow_view': //修改设置阅读权限
        case 'plate_set_add_limit': //版块设置发贴限制
        case 'plate_set_follow_pay': //版块设置收费关注

            //区分新建和修改编辑
            //新建或者自己就是作者编辑自己的 :zib_is_can_roles
            //编辑且自己不是作者（编辑其他人的内容）：'_other'判断
            if (!empty($args[0]) && !zib_bbs_is_the_author($args[0], $user_id)) {
                //有参数且自己还不是作者
                //如果自己不是作者，则检查用户是否是该帖子的管理权限
                return zib_bbs_user_can($user_id, $capability . '_other', $args[0]);
            }

            if (zib_is_can_roles($user_id, $cap_roles)) {
                return true;
            }

            break;

        //term的权限
        case 'plate_cat_edit': //编辑版块分类
        case 'plate_cat_delete': //编辑版块分类
        case 'forum_topic_edit': //编辑话题
        case 'forum_topic_delete': //编辑话题
        case 'forum_tag_edit': //编辑标签
        case 'forum_tag_delete': //编辑标签

            if (!isset($args[0])) {
                return false;
            }
            $term_id = $args[0];

            //如果是版块分类，则判断是不是分区版主
            if (in_array($capability, ['plate_cat_edit', 'plate_cat_delete'])) {
                if (zib_bbs_is_the_cat_moderator($term_id, $user_id, true)) {
                    if (zib_is_can_roles($user_id, $cap_roles)) {
                        return true;
                    }
                }
            }

            $term_author = get_term_meta($term_id, 'term_author', true);
            if ($term_author && $term_author == $user_id) {
                //自己就是创建者
                if (zib_is_can_roles($user_id, $cap_roles)) {
                    return true;
                }
            } else {
                $_roles = zib_bbs_get_cap_roles($capability . '_other');

                //论坛角色判断：分区版主、超级版主、版主
                if (zib_bbs_is_can_roles($user_id, $_roles)) {
                    return true;
                }
            }

            break;

        //以下权限：仅针对自己管理下的权限
        case 'posts_essence_set': //设置精华
        case 'posts_topping_set': //设置置顶
        case 'posts_vote_ing_edit': //修改已经进行中的投票
        case 'posts_audit'; //审核帖子
        case 'plate_edit_other'; //编辑其它人的帖子
        case 'posts_edit_other'; //编辑其它人的帖子
        case 'posts_delete_other'; //删除其它人的帖子
        case 'plate_set_add_limit_other'; //为自己管理的版块设置[发帖限制]
        case 'plate_set_allow_view_other'; //为自己管理的版块设置[查看权限]
        case 'plate_set_follow_pay_other'; //为自己管理的版块设置[收费关注]
        case 'posts_allow_view_points_other'; //为自己管理的版块设置[积分支付查看]
        case 'posts_allow_view_pay_other'; //为自己管理的版块设置[付费查看]
        case 'moderator_add': //添加版主
        case 'moderator_edit': //编辑版主
        case 'moderator_apply_process': //审核版主申请

            if (!isset($args[0])) {
                return false;
            }
            $post = get_post($args[0]);
            if (!$post) {
                return false;
            }
            //版主判断
            if (!empty($cap_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                return true;
            }

            //超级版主判断
            if (!empty($cap_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                return true;
            }

            //分区版主判断
            if (!empty($cap_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                return true;
            }
            break;

        default:
            //角色判断
            if (zib_is_can_roles($user_id, $cap_roles)) {
                return true;
            }
            return false;
            break;

    }

    return false;
}

//等级经验值参数的定义
function zib_bbs_integral_add_options_filter($options)
{
    global $zib_bbs;

    $bbs = array(
        'bbs_posts_new'   => array('发布' . $zib_bbs->posts_name, 10, '发布优质' . $zib_bbs->posts_name . '并审核通过', '论坛'),
        'bbs_posts_hot'   => array($zib_bbs->posts_name . '成为热门', 10, '发布' . $zib_bbs->posts_name . '成为热门', '论坛'),
        'bbs_score_extra' => array($zib_bbs->posts_name . '被加分', 10, '发布的' . $zib_bbs->posts_name . '获得加分，每篇' . $zib_bbs->posts_name . '最多加5次', '论坛'),
        'bbs_essence'     => array($zib_bbs->posts_name . '评为精华', 10, '发布' . $zib_bbs->posts_name . '被评为精华内容', '论坛'),
        'bbs_plate_new'   => array('创建' . $zib_bbs->plate_name, 10, '创建' . $zib_bbs->plate_name . '块并审核通过', '论坛'),
        'bbs_plate_hot'   => array($zib_bbs->plate_name . '成为热门', 10, '创建的' . $zib_bbs->plate_name . '成为热门版块', '论坛'),
    );
    return array_merge($options, $bbs);
}
add_filter('integral_add_options', 'zib_bbs_integral_add_options_filter');
