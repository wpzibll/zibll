<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-02 16:56:36
 * @LastEditTime : 2026-04-11 16:31:11
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统版主有关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户的分区版主身份的分类ID列表
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_get_user_cat_moderator_ids($user_id = 0, $only_id = true)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }
    $cache_key = 'cat_moderator_ids';
    $cache_num = wp_cache_get($user_id, $cache_key, true);
    if (false !== $cache_num) {
        return $cache_num;
    }

    $query = array(
        'taxonomy'   => 'plate_cat', //分类法
        'order'      => 'DESC',
        'count'      => false,
        'hide_empty' => false,
        'fields'     => 'ids',
        'meta_query' => array(
            array(
                'key'     => 'moderator',
                'value'   => ':"' . $user_id . '"',
                'compare' => 'LIKE',
            ),
        ),
    );

    $new_query = new WP_Term_Query($query);
    $ids       = array();

    if (!is_wp_error($new_query) && isset($new_query->terms)) {
        $ids = (array) $new_query->terms;
    }

    //添加缓存，长期有效
    wp_cache_set($user_id, $ids, $cache_key);

    return $ids;
}

//挂钩更新版主刷新缓存
add_action('updated_term_meta', function ($meta_id, $object_id, $meta_key, $_meta_value) {
    if ('moderator' === $meta_key && $_meta_value && is_array($_meta_value)) {
        foreach ($_meta_value as $user_id) {
            if (!is_super_admin($user_id)) {
                wp_cache_delete($user_id, 'cat_moderator_ids');
                zib_bbs_get_user_cat_moderator_ids($user_id);
            }
        }
    }
}, 10, 4);
//获取用户的分区版主身份的分类ID列表-结束

/**
 * @description: 获取用户的超级版主身份的版块ID列表
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_get_user_plate_author_ids($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $cache_num = wp_cache_get($user_id, 'plate_author_ids', true);
    if (false !== $cache_num) {
        return $cache_num;
    }

    $query_args = array(
        'post_type'      => 'plate',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'order'          => 'DESC',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );
    $wp_query = new WP_Query($query_args);
    $ids      = array();
    if (!empty($wp_query->posts)) {
        $ids = (array) $wp_query->posts;
    }

    //添加缓存，长期有效
    wp_cache_set($user_id, $ids, 'plate_author_ids');

    return $ids;
}

//挂钩保存帖子刷新缓存
function zib_bbs_user_plate_author_ids_cache_delete_save_post($post_ID, $post)
{
    if ('plate' === $post->post_type && !is_super_admin($post->post_author)) {
        wp_cache_delete($post->post_author, 'plate_author_ids');
        zib_bbs_get_user_plate_author_ids($post->post_author);
    }
}
add_action('save_post', 'zib_bbs_user_plate_author_ids_cache_delete_save_post', 10, 2);

/**
 * @description: 获取用户版主身份的版块ID列表
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_get_user_plate_moderator_ids($user_id = null)
{
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return [];
    }
    //先从缓存获取
    $cache_num = wp_cache_get($user_id, 'plate_moderator_ids', true);
    if (false !== $cache_num) {
        return $cache_num;
    }
    $query_args = array(
        'post_type'      => 'plate',
        'post_status'    => 'publish',
        'order'          => 'DESC',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => 'moderator',
                'value'   => ':"' . $user_id . '"',
                'compare' => 'LIKE',
            ),
        ),
    );
    $wp_query = new WP_Query($query_args);
    $ids      = array();
    if (!empty($wp_query->posts)) {
        $ids = (array) $wp_query->posts;
    }

    //添加缓存，长期有效
    wp_cache_set($user_id, $ids, 'plate_moderator_ids');

    return $ids;
}

//挂钩登录刷新缓存
function zib_bbs_user_ids_cache_delete_wp_login($user_login, $user)
{
    wp_cache_delete($user->ID, 'plate_moderator_ids');
    wp_cache_delete($user->ID, 'plate_author_ids');
    wp_cache_delete($user->ID, 'cat_moderator_ids');
}
add_action('wp_login', 'zib_bbs_user_ids_cache_delete_wp_login', 10, 2);

//挂钩更新版主刷新缓存
function zib_bbs_plate_moderator_ids_cache_delete_updated_post_meta($meta_id, $object_id, $meta_key, $_meta_value)
{
    if ('moderator' === $meta_key && $_meta_value && is_array($_meta_value)) {
        foreach ($_meta_value as $user_id) {
            if (!is_super_admin($user_id)) {
                wp_cache_delete($user_id, 'plate_moderator_ids');
            }
        }
    }
}
add_action('updated_post_meta', 'zib_bbs_plate_moderator_ids_cache_delete_updated_post_meta', 10, 4);

/**
 * @description: 获取用户在当前内容下的版主标签，自动判断
 * @param {*} $user_id
 * @param {*} $post
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_user_moderator_badge($user_id = 0, $post = null, $class = '')
{
    $is = zib_bbs_is_the_cat_moderator($post, $user_id); //分区版主判断
    if (!$is) {
        $is = zib_bbs_is_the_moderator($post, $user_id); //版主判断
    }

    if ($is) {
        return zib_bbs_get_moderator_badge($is, $class);
    }
    return '';
}

/**
 * @description: 判断是否是当前版块、帖子的超级版主
 * @param {*} $post 帖子或者版块
 * @param {*} $user_id
 * @return {*} false || author || moderator
 */
function zib_bbs_is_the_cat_moderator($post = null, $user_id = null, $is_cat = false)
{
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    $plate_cat_id = 0;
    if ($is_cat) {
        if (isset($post->term_id)) {
            $plate_cat_id = $post->term_id;
        } else {
            $plate_cat = get_term($post, 'plate_cat');
            if (isset($plate_cat->term_id)) {
                $plate_cat_id = $plate_cat->term_id;
            }
        }
    } else {
        $plate_id = 0;
        if (!is_object($post)) {
            $post = get_post($post);
        }

        if ('plate' === $post->post_type) {
            $plate_id = $post->ID;
        }
        if ('forum_post' === $post->post_type) {
            $plate_id = zib_bbs_get_plate_id($post->ID);
        }

        if (!$plate_id) {
            return false;
        }

        $plate_cat = get_the_terms($plate_id, 'plate_cat');

        if (!is_wp_error($plate_cat) && isset($plate_cat[0]->term_id)) {
            $plate_cat_id = $plate_cat[0]->term_id;
        }
    }

    if (!$plate_cat_id) {
        return false;
    }

    $moderator = get_term_meta($plate_cat_id, 'moderator', true);
    return ($moderator && is_array($moderator) && in_array($user_id, $moderator)) ? 'cat_moderator' : false;
}

/**
 * @description: 判断是否是当前版块、帖子的版主
 * @param {*} $post 帖子或者版块
 * @param {*} $user_id
 * @return {*} false || author || moderator
 */
function zib_bbs_is_the_moderator($post = null, $user_id = null)
{
    $plate_id = 0;
    $post     = get_post($post);
    if ('plate' === $post->post_type) {
        $plate_id = $post->ID;
    }
    if ('forum_post' === $post->post_type) {
        $plate_id = zib_bbs_get_plate_id($post->ID);
    }
    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id || !$plate_id) {
        return false;
    }

    $plate = get_post($plate_id);
    if ($plate->post_author == $user_id) {
        //作者，超级版主
        return 'plate_author';
    }

    $moderator = get_post_meta($plate->ID, 'moderator', true);
    return ($moderator && is_array($moderator) && in_array($user_id, $moderator)) ? 'moderator' : false;
}

//判断用户是否是论坛管理员
function zib_bbs_user_is_forum_admin($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    $forum_admin_ids = _pz('bbs_admin_users', false);
    if (is_array($forum_admin_ids) && in_array($user_id, $forum_admin_ids)) {
        return true;
    }
    return false;
}

/**
 * @description: 获取当前版块的版主数量
 * @param {*} $plate_id
 * @return {*}
 */
function zib_bbs_get_plate_moderator_count($plate_id = 0)
{
    $moderator = get_post_meta($plate_id, 'moderator', true);
    if (!$moderator || !is_array($moderator)) {
        return 0;
    }
    return count($moderator);
}

/**
 * @description: 执行为版块添加版主
 * @param {*} $type plate|cat
 * @param {*} $id
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_add_moderator($type, $id, $user_id)
{
    return zib_bbs_update_moderator('add', $type, $id, $user_id);
}

/**
 * @description: 执行为版块删除版主
 * @param {*} $type plate|cat
 * @param {*} $plate_id
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_delete_moderator($type, $id, $user_id)
{
    return zib_bbs_update_moderator('delete', $type, $id, $user_id);
}

function zib_bbs_update_moderator($action = 'add', $type = 'plate', $id = 0, $user_id = 0)
{
    if (!$id || !$user_id) {
        return false;
    }
    if ('cat' === $type) {
        $moderator = get_term_meta($id, 'moderator', true);
    } else {
        $moderator = get_post_meta($id, 'moderator', true);
    }
    if (!$moderator || !is_array($moderator)) {
        $moderator = array();
    }

    if ('delete' === $action) {
        //从数组删除
        if (in_array($user_id, $moderator)) {
            $index = array_search($user_id, $moderator);
            unset($moderator[$index]);
        }
    } else {
        //添加user_id
        if (!in_array($user_id, $moderator)) {
            $moderator[] = (string) $user_id;
        }
    }

    if ('cat' === $type) {
        update_term_meta($id, 'moderator', $moderator);

        //刷新缓存
        wp_cache_delete($user_id, 'cat_moderator_ids');
        zib_bbs_get_user_cat_moderator_ids($user_id);
    } else {
        update_post_meta($id, 'moderator', $moderator);

        //刷新缓存
        wp_cache_delete($user_id, 'plate_moderator_ids');
        zib_bbs_get_user_plate_moderator_ids($user_id);
    }

    return true;
}

/**
 * @description: 获取版块添加分区版主的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_add_cat_moderator_link($cat_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_add_moderator_link('cat', $cat_id, $class, $con);
}

/**
 * @description: 获取版块添加分区版主的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_add_plate_moderator_link($cat_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_add_moderator_link('plate', $cat_id, $class, $con);
}

/**
 * @description: 获取添加版主的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_add_moderator_link($type = 'plate', $id = 0, $class = '', $con = '')
{
    //权限判断
    $cap = 'cat' === $type ? 'cat_moderator_add' : 'moderator_add';
    if (!zib_bbs_current_user_can($cap, $id)) {
        return;
    }
    if (!$con) {
        global $zib_bbs;
        $con = zib_get_svg('add') . '添加' . ('cat' === $type ? $zib_bbs->cat_moderator_name : $zib_bbs->plate_moderator_name);
    }
    $url_var = array(
        'action' => 'moderator_add_modal',
        'id'     => $id,
        'type'   => $type,
    );

    $args = array(
        'tag'           => 'a',
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'class'         => $class,
        'height'        => 400,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_bbs_get_cat_moderator_add_user_link($user_id, $cat_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_moderator_add_user_link('cat', $user_id, $cat_id, $class, $con);
}

/**
 * @description: 获取版块添加分区版主的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_plate_moderator_add_user_link($user_id, $plate_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_moderator_add_user_link('plate', $user_id, $plate_id, $class, $con);
}

/**
 * @description: 获取将用户添加为版主的模态框按钮
 * @param {*} $type
 * @param {*} $user_id
 * @param {*} $id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_moderator_add_user_link($type = 'plate', $user_id = 0, $id = 0, $class = '', $con = '')
{

    //权限判断
    $cap = 'cat' === $type ? 'cat_moderator_add' : 'moderator_add';
    if (!$user_id || !zib_bbs_current_user_can($cap, $id)) {
        return;
    }
    if (!$con) {
        global $zib_bbs;
        $con = '设为' . ('cat' === $type ? $zib_bbs->cat_moderator_name : $zib_bbs->plate_moderator_name);
    }

    $url_var = array(
        'action'  => 'moderator_add_user_modal',
        'id'      => $id,
        'user_id' => $user_id,
        'type'    => $type,
    );

    $args = array(
        'new'           => true,
        'tag'           => 'a',
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'class'         => $class,
        'height'        => 280,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_bbs_get_edit_plate_moderator_link($plate_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_edit_moderator_link('plate', $plate_id, $class, $con);
}
function zib_bbs_get_edit_cat_moderator_link($plate_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_edit_moderator_link('plate', $plate_id, $class, $con);
}
/**
 * @description: 获取版块管理版主的链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_edit_moderator_link($type = 'plate', $id = 0, $class = '', $con = '')
{
    //权限判断
    $cap = 'cat' === $type ? 'cat_moderator_edit' : 'moderator_edit';
    if (!zib_bbs_current_user_can($cap, $id)) {
        return;
    }
    if (!$con) {
        global $zib_bbs;
        $con = '管理' . ('cat' === $type ? $zib_bbs->cat_moderator_name : $zib_bbs->plate_moderator_name);
    }

    $url_var = array(
        'action' => 'moderator_edit_modal',
        'id'     => $id,
        'type'   => $type,
    );

    $args = array(
        'tag'           => 'a',
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'class'         => $class,
        'height'        => 230,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取查看分区版主的模态框
function zib_bbs_get_cat_moderator_modal_link($cat_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_moderator_modal_link('cat', $cat_id, $class, $con);
}

//获取查看版主的模态框
function zib_bbs_get_plate_moderator_modal_link($plate_id = 0, $class = '', $con = '')
{
    return zib_bbs_get_moderator_modal_link('plate', $plate_id, $class, $con);
}

/**
 * @description: 获取版块查看版主或者分区版主的模态框链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_moderator_modal_link($type = 'plate', $id = 0, $class = '', $con = '')
{

    if (!$id) {
        return;
    }

    if (!$con) {
        global $zib_bbs;
        $con = '查看' . ('cat' === $type ? $zib_bbs->cat_moderator_name : $zib_bbs->plate_moderator_name);
    }

    $url_var = array(
        'action' => 'moderator_modal',
        'id'     => $id,
        'type'   => $type,
    );

    $args = array(
        'tag'           => 'a',
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'class'         => $class,
        'height'        => 230,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取将版主移出的模态链接
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_remove_moderator_link($type = 'plate', $user_id = 0, $id = 0, $class = '', $con = '移出')
{
    //权限判断
    $cap = 'cat' === $type ? 'cat_moderator_edit' : 'moderator_edit';
    if (!zib_bbs_current_user_can($cap, $id)) {
        return;
    }

    $url_var = array(
        'action'  => 'moderator_remove_modal',
        'id'      => $id,
        'user_id' => $user_id,
        'type'    => $type,
    );

    $args = array(
        'tag'           => 'a',
        'mobile_bottom' => true,
        'data_class'    => 'modal-mini',
        'class'         => $class,
        'height'        => 300,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 审批申请的链接按钮
 * @param {*} $user_id
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_apply_moderator_process_link($user_id, $class = '', $con = '')
{

    $url_var = array(
        'action'  => 'apply_moderator_process_modal',
        'user_id' => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'mobile_bottom' => true,
        'height'        => 300,
        'data_class'    => 'full-sm',
        'class'         => $class,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 申请版主的链接按钮
 * @param {*} $plate 允许为空
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zib_bbs_get_apply_moderator_link($plate = null, $class = '', $con = '')
{

    if (!is_object($plate)) {
        $plate = get_post($plate);
    }

    if (!empty($plate->ID) && 'plate' != $plate->post_type) {
        return;
    }
    $plate_id = !empty($plate->ID) ? $plate->ID : false;

    if (!$con) {
        global $zib_bbs;
        $con = '申请' . $zib_bbs->plate_moderator_name;
    }

    //已经是版主，不显示||管理员不显示
    //后台未开启：给没有权限的用户也显示
    if ((!_pz('bbs_show_apply_moderator', true) && !zib_bbs_current_user_can('apply_moderator')) || zib_bbs_is_the_moderator($plate) || is_super_admin()) {
        return;
    }

    //未登录显示登录按钮
    if (!get_current_user_id()) {
        return '<a href="javascript:;" class="signin-loader ' . $class . '">' . $con . '</a>';
    }

    $url_var = array(
        'action'   => 'apply_moderator_modal',
        'plate_id' => $plate_id,
    );

    $args = array(
        'tag'           => 'a',
        'mobile_bottom' => true,
        'data_class'    => 'full-sm',
        'class'         => $class,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_bbs_get_plate_moderator_query($plate = null, $paged = 1)
{
    return zib_bbs_get_moderator_query('plate', $plate, $paged);
}

function zib_bbs_get_cat_moderator_query($cat = null, $paged = 1)
{
    return zib_bbs_get_moderator_query('cat', $cat, $paged);
}

/**
 * @description: 当前版块的版主moderator列表查询
 * @param {*} $plate
 * @param {*} $paged
 * @return {*}
 */
function zib_bbs_get_moderator_query($type = 'plate', $obj = null, $paged = 1)
{
    $page_size = 99; //最多显示99
    $paged     = $paged < 1 ? $paged : 1;

    $exclude = false;
    if ('cat' === $type) {
        if (!is_object($obj)) {
            $obj = get_term($obj, 'plate_cat');
        }
        if (empty($obj->term_id)) {
            return false;
        }
        $moderator = get_term_meta($obj->term_id, 'moderator', true);
    } else {
        if (!is_object($obj)) {
            $obj = get_post($obj);
        }
        if (empty($obj->ID)) {
            return false;
        }
        $moderator = get_post_meta($obj->ID, 'moderator', true);
        $exclude   = array($obj->post_author);
    }

    if ($moderator && is_array($moderator)) {
        $include = $moderator;
    } else {
        return false;
    }

    $users_args = array(
        'exclude'     => $exclude,
        'include'     => $include,
        'order'       => 'DESC',
        'orderby'     => 'post_count',
        'number'      => $page_size,
        'paged'       => $paged,
        'count_total' => true,
        'fields'      => array('display_name', 'ID'),
    );

    $query = new WP_User_Query($users_args);
    if (!is_wp_error($query)) {
        return $query;
    }
    return false;
}

/**
 * @description: 获取版主的徽章
 * @param {*} $type moderator||author
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_moderator_badge($type = 'moderator', $class = '')
{
    switch ($type) {
        case 'moderator': //版块版主
            return zib_bbs_get_plate_moderator_badge($class);
            break;
        case 'plate_author': //版块作者
            return zib_bbs_get_plate_author_badge($class);
            break;
        case 'cat_moderator': //分区版主
            return zib_bbs_get_cat_moderator_badge($class);
            break;
    }
}

//获取版块作者的身份徽章
function zib_bbs_get_plate_author_badge($class = '')
{
    global $zib_bbs;
    $name = $zib_bbs->plate_author_name;
    return '<span class="badg badg-sm c-blue moderator-bagd ' . $class . '">' . $name . '</span>';
}

//获取版块版主的身份徽章
function zib_bbs_get_plate_moderator_badge($class = '')
{
    global $zib_bbs;
    $name = $zib_bbs->plate_moderator_name;
    return '<span class="badg badg-sm c-blue-2 moderator-bagd ' . $class . '">' . $name . '</span>';
}

//获取版块分类分区版主的身份徽章
function zib_bbs_get_cat_moderator_badge($class = '')
{
    global $zib_bbs;
    $name = $zib_bbs->cat_moderator_name;
    return '<span class="badg badg-sm c-green-2 moderator-bagd ' . $class . '">' . $name . '</span>';
}

//获取论坛管理员的身份徽章
function zib_bbs_get_forum_admin_badge($class = '')
{
    global $zib_bbs;
    $name = $zib_bbs->forum_name . '管理员';
    return '<span class="badg badg-sm c-red moderator-bagd ' . $class . '">' . $name . '</span>';
}

//获取用户身份的徽章
function zib_bbs_get_user_identity_badge($user_id = 0, $class = 'mr6')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    global $zib_bbs;
    $badge = '';
    if (zib_bbs_user_is_forum_admin($user_id) && !is_super_admin($user_id)) {
        $badge .= '<span class="badg badg-sm c-red ' . $class . '">' . $zib_bbs->forum_name . '管理员</span>';
    }
    $cat_moderator = zib_bbs_get_user_cat_moderator_ids($user_id);
    if ($cat_moderator) {
        $badge .= '<span data-toggle="tooltip" title="' . count($cat_moderator) . '个分区的' . $zib_bbs->cat_moderator_name . '" class="badg badg-sm c-green-2 moderator-bagd ' . $class . '">' . $zib_bbs->cat_moderator_name . '</span>';
    }
    $plate_author = zib_bbs_get_user_plate_author_ids($user_id);
    if ($plate_author) {
        $badge .= '<span data-toggle="tooltip" title="' . count($plate_author) . '个' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_author_name . '" class="badg badg-sm c-blue moderator-bagd ' . $class . '">' . $zib_bbs->plate_author_name . '</span>';
    }
    $plate_moderator = zib_bbs_get_user_plate_moderator_ids($user_id);
    if ($plate_moderator) {
        $badge .= '<span data-toggle="tooltip" title="' . count($plate_moderator) . '个' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_moderator_name . '" class="badg badg-sm c-blue-2 moderator-bagd ' . $class . '">' . $zib_bbs->plate_moderator_name . '</span>';
    }
    return $badge;
}

/**
 * @description: 当前版块的版主moderator列表显示
 * @param {*} $plate
 * @param {*} $paged
 * @return {*}
 */
function zib_bbs_get_moderator_lists($type = 'plate', $obj = null, $paged = 1)
{
    $lists    = '';
    $desc     = 'identity';
    $btn_type = 'follow';

    switch ($type) {
        case 'plate': //版块版主
            $query_type = 'plate';
            if (!is_object($obj)) {
                $obj = get_post($obj);
            }
            if (empty($obj->post_author)) {
                return;
            }
            $lists .= zib_bbs_get_moderator_lists_html($obj->post_author, $desc);
            $id = $obj->ID;
            break;
        case 'plate_remove': //版主移除
            $query_type = 'plate';
            $btn_type   = 'plate_remove';
            if (!is_object($obj)) {
                $obj = get_post($obj);
            }
            $id = $obj->ID;
            break;
        case 'cat': //分区版主
            $query_type = 'cat';
            if (!is_object($obj)) {
                $obj = get_term($obj, 'plate_cat');
            }
            $id = $obj->term_id;
            break;
        case 'cat_remove': //分区版主移除
            $btn_type   = 'cat_remove';
            $query_type = 'cat';
            if (!is_object($obj)) {
                $obj = get_term($obj, 'plate_cat');
            }
            $id = $obj->term_id;
            break;
    }

    $query = zib_bbs_get_moderator_query($query_type, $obj, $paged);
    if ($query) {
        $get_results = $query->get_results();
        if ($get_results) {
            foreach ($get_results as $item) {
                $lists .= zib_bbs_get_moderator_lists_html($item->ID, $desc, $btn_type, $id);
            }
        }
    }

    return $lists;
}

/**
 * @description: 获取版主用户信息的卡片
 * @param {*} $user_id
 * @param {*} $desc
 * @param {*} $btn_type follow||remove
 * @param {*} $plate_id
 * @param {*} $class
 * @return {*}
 */
function zib_bbs_get_moderator_lists_html($user_id, $desc = '', $btn_type = 'follow', $id = 0, $class = 'padding-h10')
{

    $class        = $class ? ' ' . $class : '';
    $display_name = zib_get_user_name($user_id);
    $avatar       = zib_get_avatar_box($user_id);
    $btn          = '';

    switch ($btn_type) {
        case 'follow':
            $btn = zib_get_user_follow('follow c-red', $user_id);
            break;
        case 'plate_remove':
            $btn = zib_bbs_get_remove_moderator_link('plate', $user_id, $id, 'but px12 c-red');
            break;
        case 'cat_remove':
            $btn = zib_bbs_get_remove_moderator_link('cat', $user_id, $id, 'but px12 c-red');
            break;
        case 'add':
            $btn = zib_bbs_get_plate_moderator_add_user_link($user_id, $id, 'but px12 c-blue-2');
            break;
        case 'cat_add':
            $btn = zib_bbs_get_cat_moderator_add_user_link($user_id, $id, 'but px12 c-green-2');
            break;
    }

    if ('identity' === $desc) {
        $desc = zib_bbs_get_user_identity_badge($user_id);
    }

    $html = '<div class="user-info flex ac' . $class . '">';
    $html .= $avatar;
    $html .= '<div class="flex1 ml10 flex ac jsb">';
    $html .= '<div class="flex1">' . $display_name . '<div class="mt3">' . $desc . '</div></div>';
    $html .= $btn ? '<div class="flex0 em09 ml10">' . $btn . '</div>' : '';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

//版块添加版主的模态框
function zib_bbs_get_moderator_add_modal($id = '', $type = 'plate')
{

    global $zib_bbs;

    $type_name = $zib_bbs->plate_name;
    $action    = 'moderator_add_search';
    if ('cat' === $type) {
        $cat = get_term($id, 'plate_cat');
        if (empty($cat->term_id)) {
            return false;
        }
        $ID             = $cat->term_id;
        $title          = $cat->name;
        $moderator_name = $zib_bbs->cat_moderator_name;
        $type_name .= '分类';
        $header = '<div class="mb10 touch">' . zib_get_svg('plate-fill', null, 'mr6 c-green-2') . '<b>添加' . $moderator_name . '</b><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button></div>';
    } else {
        $plate = get_post($id);
        if (empty($plate->ID) || 'plate' !== $plate->post_type) {
            return false;
        }
        $title = $plate->post_title;

        $ID             = $plate->ID;
        $moderator_name = $zib_bbs->plate_moderator_name;
        $header         = '<div class="mb10 touch">' . zib_get_svg('plate-fill', null, 'mr6 c-blue-2') . '<b>添加' . $moderator_name . '</b><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button></div>';
    }

    $emby = zib_get_null('', 40, 'null-search.svg', '', 0, 150);

    //ajax搜索组件
    $search = '<div class="auto-search" ajax-url="' . zib_get_admin_ajax_url($action, array('id' => $ID, 'type' => $type)) . '">';
    $search .= '<div class="form-right-icon">';
    $search .= '<input type="text" name="s" class="form-control search-input" tabindex="1" value="" placeholder="请输入关键词以搜索用户" autocomplete="off">';
    $search .= '<div class="search-icon abs-right">' . zib_get_svg('search') . '</div>';
    $search .= '</div>';
    $search .= '<div class="mt20 mb10 muted-3-color separator search-remind em09">请输入关键词以搜索用户</div>';
    $search .= '<div class="search-container mini-scrollbar scroll-y max-vh5">' . $emby . '</div>';
    $search .= '</div>';

    $edit_link = zib_bbs_get_edit_moderator_link($type, $ID, 'focus-color', esc_attr($title));

    $html = '';
    $html .= '<div class="muted-2-color mb20">为' . $type_name . '<span class="focus-color">[' . $edit_link . ']</span>添加新的' . $moderator_name . '</div>';
    $html .= $search;
    return $header . $html;
}

//获取搜索可以添加为版主的用户
function zib_bbs_get_moderator_add_search_user($type = 'plate', $id = '', $s = '')
{

    //排除已经是当前版块的版主
    $exclude = array();

    if ($id) {
        if ('cat' === $type) {
            //排除已经是当前分类的分区版主
            $moderator = get_term_meta($id, 'moderator', true);
            if (is_array($moderator)) {
                $exclude = $moderator;
            }
        } else {
            //排除已经是当前版块的版主
            $moderator = get_post_meta($id, 'moderator', true);
            if (is_array($moderator)) {
                $exclude = $moderator;
            }
            //排除已经是当前版块的作者：超级版主
            $plate = get_post($id);
            if (isset($plate->post_author)) {
                $exclude[] = $plate->post_author;
            }
        }

    }

    //排除论坛管理员
    $forum_admin_ids = _pz('bbs_admin_users', false);
    if (is_array($forum_admin_ids)) {
        $exclude = array_merge($forum_admin_ids, $exclude);
    }

    $ice_perpage = 12; //最多12个
    $users_args  = array(
        'search'         => '*' . $s . '*',
        'exclude'        => $exclude,
        'role__not_in'   => ['administrator'], //排除超级管理员
        'search_columns' => array('user_email', 'user_nicename', 'display_name', 'user_login'),
        'count_total'    => false,
        'number'         => $ice_perpage,
        'fields'         => ['ID'],
    );
    $user_search = new WP_User_Query($users_args);
    $users       = $user_search->get_results();

    $lists    = '';
    $btn_type = 'cat' === $type ? 'cat_add' : 'add';
    if ($users) {
        foreach ($users as $user) {
            $desc = zib_bbs_get_user_identity_badge($user->ID);
            $desc = $desc ? $desc : zib_get_user_join_day_desc($user->ID, 'em09 muted-2-color');
            $lists .= zib_bbs_get_moderator_lists_html($user->ID, $desc, $btn_type, $id);
        }
    }

    return $lists;
}

//获取将用户添加为版主的模态框
function zib_bbs_get_moderator_add_user_modal($type = 'plate', $plate = null, $user_id = 0)
{
    global $zib_bbs;
    $type_name = $zib_bbs->plate_name;

    if ('cat' === $type) {
        $type_name .= '分类';
        $cat = get_term($plate, 'plate_cat');

        $ID             = $cat->term_id;
        $title          = $cat->name;
        $moderator_name = $zib_bbs->cat_moderator_name;
        $header         = zib_get_modal_colorful_header('c-green-2', zib_get_svg('plate-fill'), '添加' . $moderator_name);
    } else {
        $moderator_name = $zib_bbs->plate_moderator_name;
        $plate          = get_post($plate);
        $title          = esc_attr($plate->post_title);
        $ID             = $plate->ID;
        $header         = zib_get_modal_colorful_header('c-blue-2', zib_get_svg('plate-fill'), '添加' . $moderator_name);
    }

    $user_display_name = zib_get_user_name(array(
        'id'         => $user_id,
        'class'      => '',
        'name_class' => '',
        'vip'        => false,
        'auth'       => true,
        'level'      => true,
        'follow'     => false,
    ));

    $form = '<form>';
    $form .= '<div class="mb10"><b>将用户[' . $user_display_name . ']设置为' . $type_name . '<sapn class="c-theme">[' . $title . ']</sapn>的' . $moderator_name . '</b></div>';
    $form .= '<div class="mb20"><textarea class="form-control" name="desc" tabindex="1" placeholder="给用户留言" rows="2"></textarea></div>';
    $form .= '<div>
                    <input type="hidden" name="action" value="moderator_add_user">
                    <input type="hidden" name="type" value="' . $type . '">
                    <input type="hidden" name="id" value="' . $ID . '">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    ' . wp_nonce_field('moderator_add_user', '_wpnonce', false, false) . '
                    <div class="mt20 modal-buts but-average">
                        <button type="button" data-dismiss="modal" href="javascript:;" class="but">取消</button>
                        <button class="but c-blue wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认添加</button>
                    </div>
                </div>';
    $form .= '</form>';

    return $header . $form;
}

/**
 * @description: 获取将版主移出当前版块的模态框
 * @param {*} $type plate|cat
 * @param {*} $obj
 * @param {*} $user_id
 * @return {*}
 */
function zib_bbs_get_moderator_remove_modal($type, $obj, $user_id = 0)
{
    global $zib_bbs;
    $type_name = $zib_bbs->plate_name;

    if ('cat' === $type) {
        $type_name .= '分类';
        $cat = $obj;

        $ID             = $cat->term_id;
        $title          = $cat->name;
        $moderator_name = $zib_bbs->cat_moderator_name;
        $header         = zib_get_modal_colorful_header('c-green-2', zib_get_svg('plate-fill'), '添加' . $moderator_name);
    } else {
        $moderator_name = $zib_bbs->plate_moderator_name;
        $plate          = $obj;
        $title          = esc_attr($plate->post_title);
        $ID             = $plate->ID;
        $header         = zib_get_modal_colorful_header('c-blue-2', zib_get_svg('plate-fill'), '添加' . $moderator_name);
    }

    $user_display_name = zib_get_user_name(array(
        'id'         => $user_id,
        'class'      => '',
        'name_class' => '',
        'vip'        => false,
        'auth'       => true,
        'level'      => true,
        'follow'     => false,
    ));

    $form = '<form>';
    $form .= '<div class="mb10"><b>您正在删除用户[' . $user_display_name . ']在' . $type_name . '<sapn class="c-theme">[' . $title . ']</sapn>的' . $type_name . '身份</b></div>';
    $form .= '<div class="c-red mb6">确认要移出吗？</div>';
    $form .= '<div class="mb20"><textarea class="form-control" name="desc" tabindex="1" placeholder="请输入删除移出原因" rows="2"></textarea></div>';
    $form .= '<div class="mt20 but-average">
                    <input type="hidden" name="action" value="moderator_remove">
                    <input type="hidden" name="type" value="' . $type . '">
                    <input type="hidden" name="id" value="' . $ID . '">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    ' . wp_nonce_field('moderator_remove', '_wpnonce', false, false) . '
                    <button type="button" data-dismiss="modal" href="javascript:;" class="but">取消</button>
                    <button class="but c-red wp-ajax-submit"><i class="fa fa-trash-o" aria-hidden="true"></i>确认移出</button>
                </div>';
    $form .= '</form>';

    return $header . $form;
}

/**
 * @description: 执行将用户添加到版块的版主
 * @param {*} $type plate|cat
 * @param {*} $plate
 * @param {*} $user_id
 * @param {*} $desc
 * @return {*}
 */
function zib_bbs_moderator_add_user($type, $obj, $user_id = 0, $desc = '')
{
    global $zib_bbs;

    $type_name = $zib_bbs->plate_name;

    if ('cat' === $type) {
        $type_name .= '分类';
        $permalink      = get_term_link($obj, 'plate_cat');
        $ID             = $obj->term_id;
        $title          = $obj->name;
        $moderator_name = $zib_bbs->cat_moderator_name;
    } else {
        $ID             = $obj->ID;
        $moderator_name = $zib_bbs->plate_moderator_name;
        $permalink      = get_permalink($obj);
        $title          = zib_str_cut(esc_attr($obj->post_title), 0, 8);
    }

    //执行添加操作
    if (!zib_bbs_add_moderator($type, $ID, $user_id)) {
        return false;
    }

    //准备发送消息
    $userdata = get_userdata($user_id);
    if (empty($userdata->display_name)) {
        return;
    }
    $user_display_name = $userdata->display_name;
    $operator_id       = get_current_user_id();
    $msg_con           = '';
    $msg_con .= '您好！' . $user_display_name . '！<br />';
    $msg_con .= '您已成为' . $type_name . '[<a class="focus-color" href="' . $permalink . '">' . $title . '</a>]的' . $moderator_name . '<br>';

    $msg_con .= '操作用户：' . zib_get_user_name_link($operator_id) . '<br>';
    $msg_con .= '操作时间：' . current_time('Y-m-d H:i:s') . '<br>';
    $msg_con .= '<br>';
    $msg_con .= $desc . '<br>';
    $msg_con .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . $permalink . '">查看此' . $type_name . '</a>';

    $title    = '您已成为' . $type_name . '[' . $title . ']的' . $moderator_name;
    $msg_args = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'system',
        'title'        => $title,
        'content'      => $msg_con,
        'meta'         => array(
            'id'          => $ID,
            'operator_id' => $operator_id,
            'desc'        => $desc,
            'type'        => $type,
            'time'        => current_time('Y-m-d H:i:s'),
        ),
    );

    //创建消息
    ZibMsg::add($msg_args);

    //发送邮件
    if (_pz('email_bbs_apply_moderator_reply', true) && is_email($userdata->user_email) && !stristr($userdata->user_email, '@no')) {
        //发送邮件
        @wp_mail($userdata->user_email, $title, $msg_con);
    }
    return true;
}

/**
 * @description: 获取版主列表的模态框
 * @param {*} $type plate|cat
 * @param {*} $id
 * @param {*} $is_edit
 * @return {*}
 */
function zib_bbs_get_moderator_modal($type, $id, $is_edit = false)
{
    global $zib_bbs;

    $moderator_name = $zib_bbs->plate_moderator_name;
    $type_name      = $zib_bbs->plate_name;
    $header_prefix  = $is_edit ? '管理' : '查看'; //标题前缀
    $btn            = $is_edit ? zib_bbs_get_add_moderator_link($type, $id, 'mt10 but jb-blue padding-lg block') : '';
    $lists_type     = $is_edit ? $type . '_remove' : $type;

    if ('cat' === $type) {
        $moderator_name = $zib_bbs->cat_moderator_name;
        $type_name .= '分类';
        $header = '<div class="mb10 touch">' . zib_get_svg('plate-fill', null, 'mr6 c-green-2') . '<b>' . $header_prefix . $moderator_name . '</b><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button></div>';
    } else {
        $header = '<div class="mb10 touch">' . zib_get_svg('plate-fill', null, 'mr6 c-blue-2') . '<b>' . $header_prefix . $moderator_name . '</b><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button></div>';
        $btn    = !$is_edit ? zib_bbs_get_apply_moderator_link($id, 'mt10 but jb-blue padding-lg block', '申请成为' . $moderator_name) : $btn;
    }

    if (!$btn && !$is_edit) {
        $btn = '<div class="but-average mt10">' . zib_bbs_get_add_moderator_link($type, $id, 'but jb-blue padding-lg') . zib_bbs_get_edit_moderator_link($type, $id, 'but jb-yellow padding-lg') . '</div>';
    }

    $lists = zib_bbs_get_moderator_lists($lists_type, $id);

    if (!$lists) {
        $header = zib_get_modal_colorful_header('jb-yellow', zib_get_svg('plate-fill'), '暂无' . $moderator_name);
        $lists  = '<div class="c-yellow box-body text-center">此' . $type_name . '暂时没有' . $moderator_name . '</div>';
    } else {
        $lists = '<div class="mini-scrollbar scroll-y max-vh5">' . $lists . '</div>';
    }

    return $header . $lists . $btn;
}

//版块管理版主的模态框
function zib_bbs_get_moderator_edit_modal($type = 'plate', $obj = '')
{
    return zib_bbs_get_moderator_modal($type, $obj, true);
}

/**
 * @description: 执行将版主从版块中移出
 * @param {*} $plate
 * @param {*} $user_id
 * @param {*} $desc
 * @return {*}
 */
function zib_bbs_moderator_remove_user($type = 'plate', $obj = null, $user_id = 0, $desc = '')
{
    global $zib_bbs;
    $type_name = $zib_bbs->plate_name;

    if ('cat' === $type) {
        $type_name .= '分类';
        $ID             = $obj->term_id;
        $title          = $obj->name;
        $permalink      = get_term_link($obj, 'plate_cat');
        $moderator_name = $zib_bbs->cat_moderator_name;
    } else {
        $ID             = $obj->ID;
        $permalink      = get_permalink($obj);
        $moderator_name = $zib_bbs->plate_moderator_name;
        $title          = zib_str_cut(esc_attr($obj->post_title), 0, 8);
    }

    if (!zib_bbs_delete_moderator($type, $ID, $user_id)) {
        return false;
    }

    //准备发送消息
    $userdata = get_userdata($user_id);
    if (empty($userdata->display_name)) {
        return;
    }

    $user_display_name = $userdata->display_name;
    $operator_id       = get_current_user_id();
    $msg_con           = '';
    $msg_con .= '您好！' . $user_display_name . '！<br />';
    $msg_con .= '您在' . $type_name . '[<a class="focus-color" href="' . $permalink . '">' . $title . '</a>]的' . $moderator_name . '身份已移出' . '<br>';

    $msg_con .= '操作用户：' . zib_get_user_name_link($operator_id) . '<br>';
    $msg_con .= '操作时间：' . current_time('Y-m-d H:i:s') . '<br>';
    $msg_con .= '<br>';
    $msg_con .= ($desc ?: '') . '<br>';
    $msg_con .= '如有疑问，请与管理员联系' . '<br>';

    $title    = '您在' . $type_name . '[' . $title . ']的' . $moderator_name . '身份已移出';
    $msg_args = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'system',
        'title'        => $title,
        'content'      => $msg_con,
        'meta'         => array(
            'id'          => $ID,
            'operator_id' => $operator_id,
            'desc'        => $desc,
            'type'        => $type,
            'time'        => current_time('Y-m-d H:i:s'),
        ),
    );

    //创建消息
    ZibMsg::add($msg_args);

    if (_pz('email_bbs_moderator_remove', true) && is_email($userdata->user_email) && !stristr($userdata->user_email, '@no')) {
        //发送邮件
        @wp_mail($userdata->user_email, $title, $msg_con);
    }
    return true;
}

//处理申请的模态框
function zib_bbs_get_apply_moderator_process_modal($user_id)
{

    global $zib_bbs;
    $moderator_name = $zib_bbs->plate_moderator_name;
    $plate_name     = $zib_bbs->plate_name;
    $processing     = zib_bbs_get_apply_moderator_processing($user_id);
    $header         = zib_get_modal_colorful_header('jb-blue', zib_get_svg('plate-fill'), '处理' . $moderator_name . '申请');

    if (!empty($processing->meta['plate_id'])) {
        //已存在申请中的流程
        $plate_id = $processing->meta['plate_id'];
        $plate    = get_post($plate_id);
        $time     = $processing->meta['time'];
        $desc     = $processing->meta['desc'];

        $info = '';
        $info .= '<div class="mb10">
                    <div class="author-set-left">申请用户</div>
                    <div class="author-set-right mt6">' . zib_get_user_name("id=$user_id") . '</div>
                </div>';
        $info .= '<div class="mb10">
                    <div class="author-set-left">申请' . $plate_name . '</div>
                    <div class="author-set-right mt6"><a class="focus-color" href="' . get_permalink($plate) . '">' . $plate->post_title . '</a></div>
                </div>';
        $info .= '<div class="mb10">
                    <div class="author-set-left">申请时间</div>
                    <div class="author-set-right mt6">' . $time . '</div>
                </div>';
        $info .= '<div class="mb10">
                    <div class="author-set-left">申请说明</div>
                    <div class="author-set-right mt6">' . $desc . '</div>
                </div>';
        $info = '<div class="mb20">' . $info . '</div>';

        $html = $info;
        $html .= '<form>';
        $html .= '<div class="mb20">
                        <div class="author-set-left">处理方式</div>
                        <div class="author-set-right mt6">
                            <span><label class="badg p2-10 pointer c-blue mr6"><input type="radio" name="process" value="1"><span class="ml6">批准申请</span></label></span>
                            <span><label class="badg p2-10 pointer c-red"><input type="radio" name="process" value="2"><span class="ml6">拒绝申请</span></label></span>
                        </div>
                    </div>';

        $html .= '<div class="mb20"><div class="author-set-left">处理留言</div><div class="author-set-right mt6"><textarea class="form-control" name="desc" placeholder="请输入处理留言" rows="2"></textarea></div></div>';
        $html .= '<a href="javascript:;" class="but jb-blue padding-lg block wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认提交</a>';
        $html .= '<input type="hidden" name="user_id" value="' . $user_id . '">';
        $html .= '<input type="hidden" name="action" value="apply_moderator_process">';
        $html .= wp_nonce_field('apply_moderator_process', '_wpnonce', false, false); //安全效验
        $html .= '</form>';
        return $header . '<div class="mini-scrollbar scroll-y max-vh5">' . $html . '</div>';
    } else {
        $html = '<div class="em12 text-center c-yellow" style="padding: 30px 0;">当前申请已处理完成</div>';

        return $header . $html;
    }

}

//申请版主的模态框
function zib_bbs_get_apply_moderator_modal($plate_id = 0)
{
    global $zib_bbs;
    $name    = $zib_bbs->plate_moderator_name;
    $can     = zib_bbs_current_user_can('apply_moderator', $plate_id);
    $can_ros = zib_bbs_get_cap_roles('apply_moderator');
    //申请条件，用户限制
    $roles_lists_html = '';
    $roles_lists      = zib_get_hascaps_roles_lists($can_ros);
    if ($roles_lists) {
        $roles_lists_html = '<div class="title-theme font-bold mb6">申请要求</div>';
        $roles_lists_html .= '<div class="mb10 em09 muted-2-color">以下用户组可申请成为版主</div>';
        $roles_lists_html .= '<div class="mb20">';
        $roles_lists_html .= $roles_lists;
        $roles_lists_html .= '</div>';
        $roles_lists_html .= '';
    }
    //申请说明
    $desc_html = '';
    $desc      = _pz('bbs_apply_moderator_desc');
    if ($desc) {
        $desc_html = '<div class="title-theme font-bold mb10">申请说明</div>';
        $desc_html .= '<div class="mb20 muted-color">';
        $desc_html .= $desc;
        $desc_html .= '</div>';
    }

    //模态框顶部
    $header_class = 'jb-blue';
    $header_text  = '申请成为' . $name;
    if (!$can) {
        $header_class = 'jb-yellow';
        $header_text  = '暂时不能申请';
    }
    $header = zib_get_modal_colorful_header($header_class, zib_get_svg('plate-fill'), $header_text);
    $con    = '';
    $footer = '';

    if ($can) {
        $plate        = get_post($plate_id);
        $plate_select = '';

        if (!empty($plate->ID)) {
            //指定版块，则显示已选择版块
            $plate_select .= '<div class="c-blue font-bold mb10">您正在申请' . $zib_bbs->plate_name . '[<b>' . $plate->post_title . '</b>]的' . $name . '</div>';
            $plate_select .= '<input type="hidden" name="plate_id" value="' . $plate->ID . '">';
        } else {
            $_plate_select = zib_get_apply_moderator_plate_select();
            if ($_plate_select) {
                $plate_select .= '<div class="mb20">';
                $plate_select .= '<div class="em09 muted-2-color mb6">请选择申请的' . $zib_bbs->plate_name . '</div>';
                $plate_select .= zib_get_apply_moderator_plate_select();
                $plate_select .= '</div>';
            }
        }

        if ($plate_select) {
            $form = '<form>';
            $form .= $plate_select;
            $form .= '<div class="mb20">';
            $form .= '<div class="em09 muted-2-color mb6">请输入申请理由及说明</div>';
            $form .= '<textarea class="form-control" name="desc" placeholder="请输入申请理由及说明" rows="2"></textarea>';
            $form .= '</div>';
            $form .= '<input type="hidden" name="action" value="apply_moderator">';
            $form .= wp_nonce_field('apply_moderator', '_wpnonce', false, false); //安全效验
            $form .= '<a href="javascript:;" class="but jb-blue padding-lg block wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>提交申请</a>';
            $form .= '</form>';
        } else {
            $form = '<h5 class="c-red mb20">抱歉！暂时没有您可以申请' . $name . '的' . $zib_bbs->plate_name . '</h5>';
        }

        $con = '';
        $con .= $roles_lists_html;
        $con .= $desc_html;
        $con .= $form;
    } else {
        $con = '<h5 class="c-red mb20">抱歉！您暂时无法申请成为' . $name . '</h5>';
        $con .= $roles_lists_html;
        $con .= $desc_html;
        $footer = '<div class="modal-buts but-average"><a type="button" data-dismiss="modal" class="but" href="javascript:;">取消</a></div>';
    }

    $html = $header . '<div class="mini-scrollbar scroll-y max-vh5" style="padding:0 3px;">' . $con . '</div>' . $footer;
    return $html;
}

//允许用户申请的版块明细
function zib_get_apply_moderator_plate_select($in_id = 0, $class = 'form-control')
{
    global $zib_bbs;
    $plate_name     = $zib_bbs->plate_name;
    $posts_per_page = -1;
    $paged          = 1;
    $orderby        = 'views';
    $option_html    = '';
    $user_id        = get_current_user_id();
    //已经是版主的不显示
    $args = array(
        'post_type'      => 'plate',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );
    $moderator_ids    = get_user_meta($user_id, 'moderator_ids', true);
    $plate_author_ids = get_user_meta($user_id, 'plate_author_ids', true);

    if ($moderator_ids && is_array($moderator_ids)) {
        $args['post__not_in'] = $moderator_ids; //排除自己已经是版主的版块
    }
    if ($plate_author_ids && is_array($plate_author_ids)) {
        $args['post__not_in'] += $plate_author_ids; //排除自己已经是超级版主的版块
    }

    $args        = zib_bbs_query_orderby_filter($orderby, $args);
    $plate_query = new WP_Query($args);
    if (!empty($plate_query->posts)) {
        foreach ($plate_query->posts as $posts) {
            $id = $posts->ID;
            //不是当前版块的版主
            $name = $posts->post_title;
            $option_html .= '<option' . selected($in_id, $id, false) . ' value="' . $id . '">' . esc_attr($name) . '</option>';
        }
    }
    return $option_html ? '<div class="form-select"><select class="' . $class . '" name="plate_id"><option value="">请选择' . $plate_name . '</option>' . $option_html . '</select></div>' : '';
}

//创建版主申请
function zib_bbs_apply_moderator_create($plate, $desc = '')
{
    global $zib_bbs;
    $plate_name     = $zib_bbs->plate_name;
    $moderator_name = $zib_bbs->plate_moderator_name;

    $plate   = get_post($plate);
    $user_id = get_current_user_id();
    if (empty($plate->ID) || !$user_id) {
        return false;
    }

    $userdata = get_userdata($user_id);
    if (empty($userdata->display_name)) {
        return false;
    }

    $moderator_count = zib_bbs_get_plate_moderator_count($plate->ID);
    $msg_con         = '';
    $msg_con .= '用户：' . zib_get_user_name_link($user_id, 'focus-color') . '，正在申请成为版主' . '<br>';
    $msg_con .= '申请' . $plate_name . '：<a class="focus-color" href="' . get_permalink($plate) . '">' . $plate->post_title . '</a>' . '<br>';
    $msg_con .= '该' . $plate_name . '由[' . zib_get_user_name_link($plate->post_author, 'focus-color') . ']创建于' . get_the_time('Y-m-d H:i:s', $plate) . '，';
    $msg_con .= $moderator_count ? '目前已有' . $moderator_count . '名' . $moderator_name . '<br /><br />' : '目前还没有' . $moderator_name . '<br /><br />';

    $msg_con .= '申请说明：<br />' . $desc . '<br>';
    $msg_con .= '申请时间：' . current_time('Y-m-d H:i:s') . '<br>';
    $msg_con .= '<br>';

    $msg_con .= '您可以点击下方按钮快速处理此申请' . '<br>';

    $motel_btn = zib_bbs_get_apply_moderator_process_link($user_id, 'but jb-blue padding-lg', '立即处理');
    $mail_url  = zib_bbs_get_apply_moderator_process_url();
    $mail_btn  = '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($mail_url) . '">立即处理</a>' . '<br>';

    $title    = $plate_name . '[' . zib_str_cut($plate->post_title, 0, 8) . ']有新的' . $moderator_name . '申请待处理-用户：' . $userdata->display_name;
    $msg_args = array(
        'send_user'    => $user_id,
        'receive_user' => 'admin',
        'type'         => 'moderator_apply',
        'title'        => $title,
        'content'      => $msg_con . $motel_btn,
        'meta'         => array(
            'plate_id' => $plate->ID,
            'desc'     => $desc,
            'time'     => current_time('Y-m-d H:i:s'),
        ),
    );
    //创建消息
    if (!ZibMsg::add($msg_args)) {
        return false;
    }
    //发送邮件
    if (_pz('email_bbs_apply_moderator_to_admin', true)) {
        //发送邮件
        zib_mail_to_admin($title, $msg_con . $mail_btn);
    }
    if (zib_bbs_current_user_can('moderator_apply_process', $plate->ID, $plate->post_author)) {
        //判断作者是否有审核的权限，有则发送 申请处理
        $msg_args['receive_user'] = $plate->post_author;
        ZibMsg::add($msg_args); //创建消息
        if (_pz('email_bbs_apply_moderator_to_admin', true)) {
            //发送邮件
            $post_author = get_userdata($plate->post_author);
            //发送邮件
            if (is_email($post_author->user_email) && !stristr($post_author->user_email, '@no')) {
                @wp_mail($post_author->user_email, $title, $msg_con . $mail_btn);
            }
        }
    }

    return true;
}

//查找获取已经在申请中还未处理的申请
function zib_bbs_get_apply_moderator_processing($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'moderator_apply',
        'status'    => 0,
    );
    return ZibMsg::get_row($msg_get_args);
}

//获取处理申请的链接url
function zib_bbs_get_apply_moderator_process_url()
{
    return zibmsg_get_conter_url();
}

//审核处理用户提交的版主申请
function zib_bbs_apply_moderator_process($user_id, $process, $desc = '')
{
    if (!$user_id || !$process) {
        return false;
    }

    $processing = zib_bbs_get_apply_moderator_processing($user_id);
    if (empty($processing->meta['plate_id'])) {
        return false;
    }

    //更新当前消息状态
    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'moderator_apply',
        'status'    => 0,
    );
    ZibMsg::set_status_batch($msg_get_args, $process);

    $userdata  = get_userdata($user_id);
    $plate_id  = $processing->meta['plate_id'];
    $plate     = get_post($plate_id);
    $permalink = get_permalink($plate);
    $time      = $processing->meta['time'];
    $desc      = $processing->meta['desc'];
    $title     = zib_str_cut(esc_attr($plate->post_title), 0, 8);

    global $zib_bbs;
    $plate_name     = $zib_bbs->plate_name;
    $moderator_name = $zib_bbs->plate_moderator_name;
    if (1 == $process) {
        //批准审核
        $msg_title   = $moderator_name . '申请已通过，恭喜您成为' . $plate_name . '[' . $plate->post_title . ']的' . $moderator_name;
        $status_text = '已通过';
        //执行添加版主
        zib_bbs_add_moderator('plate', $plate_id, $user_id);
    } else {
        //拒绝审核
        $status_text = '被拒绝';
        $msg_title   = '您的' . $moderator_name . '申请被拒绝';
    }

    $user_display_name = $userdata->display_name;
    $current_user_id   = get_current_user_id();
    $msg_con           = '';
    $msg_con .= '您好！' . $user_display_name . '<br />您于' . $time . ' 发起的' . $moderator_name . '申请' . $status_text . '<br>';
    $msg_con .= '申请' . $plate_name . '：<a class="focus-color" href="' . $permalink . '">' . $title . '</a><br />';
    $msg_con .= '申请时间：' . $time . '<br>';
    $msg_con .= '处理时间：' . current_time('Y-m-d H:i:s') . '<br>';
    $msg_con .= '处理人：' . zib_get_user_name_link($current_user_id) . '<br>';
    $msg_con .= '<br>';
    $msg_con .= $desc . '<br>';
    $msg_con .= '如有疑问请与管理员联系';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'moderator_apply_reply',
        'title'        => $msg_title,
        'content'      => $msg_con,
        'parent'       => $processing->id,
        'meta'         => array(
            'operating_user' => $current_user_id, //操作用户
        ),
    );
    //创建消息
    if (!ZibMsg::add($msg_arge)) {
        return false;
    }

    if (_pz('email_bbs_apply_moderator_reply', true)) {
        //发送邮件
        if (is_email($userdata->user_email) && !stristr($userdata->user_email, '@no')) {
            @wp_mail($userdata->user_email, $title, $msg_con);
        }
    }

    return true;
}
