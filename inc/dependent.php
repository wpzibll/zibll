<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-12-23 22:31:32
 * @LastEditTime : 2026-04-25 19:21:28
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|前置依赖函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取模板页面的URL
 * @param {*} $template 模板路径
 * @param {*} $args 模板参数
 * @return {*} URL
 */
function zib_get_template_page_url($template, $args = array())
{
    $cache = wp_cache_get($template, 'page_url', true);
    if ($cache !== false) {
        return $cache;
    }

    $templates = array(
        'pages/newposts.php'  => array('发布文章', 'newposts'),
        'pages/user-sign.php' => array('登录/注册/找回密码', 'user-sign'),
        'pages/download.php'  => array('资源下载', 'download'),
    );
    $templates = array_merge($templates, $args);

    //查找是否已经存在
    $query_args = array(
        'orderby'                => 'date',
        'order'                  => 'ASC',
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'showposts'              => 1,
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => true,
        'post_type'              => 'page',
        'post_status'            => 'publish',
        'fields'                 => 'ids',
        'meta_query'             => array(
            array(
                'key'   => '_wp_page_template',
                'value' => $template,
            )),
    );
    $query = new WP_Query($query_args);
    $pages = $query->get_posts();

    $page_id = 0;
    if (!empty($pages[0])) {
        $page_id = $pages[0];
    } elseif (!empty($templates[$template][0])) {
        $one_page = array(
            'post_title'  => $templates[$template][0],
            'post_name'   => $templates[$template][1],
            'post_status' => 'publish',
            'post_type'   => 'page',
            'post_author' => 1,
        );

        $page_id = wp_insert_post($one_page);
        update_post_meta($page_id, '_wp_page_template', $template);
    }
    if ($page_id) {
        $url = get_permalink($page_id);
        wp_cache_set($template, $url, 'page_url');
        return $url;
    } else {
        return false;
    }
}

//获取经验值add的参数
function zib_get_user_integral_add_options()
{
    $options = array(
        'sign_up'         => array('首次注册', 20, '', '用户'),
        'sign_in'         => array('每日登录', 5, '每日登录', '用户'),
        'followed'        => array('被关注', 5, '有新的粉丝关注', '用户'),

        'post_new'        => array('发布文章', 5, '发布优质文章并审核通过', '文章'),
        'post_like'       => array('文章获赞', 1, '发布内容获得用户点赞，每篇文章最多加5次', '文章'),
        'post_favorite'   => array('文章被收藏', 2, '发布的内容被用户收藏', '文章'),
        'comment_new'     => array('发表评论', 2, '发表评论并审核通过', '文章'),
        'comment_like'    => array('评论获赞', 1, '发布评论获得用户点赞，每个评论最多加5次', '文章'),

        'bbs_posts_new'   => array('发布帖子', 3, '发布优质帖子并审核通过', '论坛'),
        'bbs_score_extra' => array('帖子被加分', 1, '帖子被加分，每篇帖子最多加5次', '论坛'),
        'bbs_essence'     => array('帖子评为精华', 2, '帖子评为精华', '论坛'),
        'bbs_posts_hot'   => array('帖子成为热门', 2, '帖子成为热门', '论坛'),
        'bbs_plate_new'   => array('创建版块', 2, '创建新版块并审核通过', '论坛'),
        'bbs_plate_hot'   => array('版块成为热门', 2, '创建的版块成为热门版块', '论坛'),
        'bbs_adopt'       => array('回答被采纳', 2, '回答被提问作者采纳', '论坛'),
        'bbs_comment_hot' => array('评论成为神评', 2, '发表的评论成为神评论', '论坛'),
    );
    return apply_filters('integral_add_options', $options);
}

/**
 * 从 'zibll_options' 数组中检索特定选项的值。
 *
 * @param string $name     选项的名称。
 * @param mixed  $default  如果未找到选项，则返回的默认值。
 * @param string $subname  （可选）如果选项是嵌套数组，则为选项的子名称。
 * @return mixed           选项的值，如果未找到则返回默认值。
 */
function _pz($name, $default = false, $subname = '')
{
    // 声明静态变量以加快检索速度
    static $options = null;
    if ($options === null) {
        $options = get_option('zibll_options');
    }

    if (isset($options[$name])) {
        if ($subname) {
            return isset($options[$name][$subname]) ? $options[$name][$subname] : $default;
        } else {
            return $options[$name];
        }
    }
    return $default;
}

//单独设置主题配置参数
function _spz($name, $value)
{
    $get_option        = get_option('zibll_options');
    $get_option        = is_array($get_option) ? $get_option : array();
    $get_option[$name] = $value;
    return update_option('zibll_options', $get_option);
}

/**
 * @description: 获取option or meta数据的key
 * @param {*}
 * @return {*}
 */
function zib_get_option_meta_keys($type)
{
    $keys = array(
        'option'       => array('weixingzh_event_data', 'weixingzh_access_token', 'wechatshare_ticket', 'search_keywords', 'zibll_open_source_version'),
        'user_meta'    => array('author_addresses', 'shop_addresses', 'favorite_product', 'quick_often', 'vip_level_expired', '_user_points_followed', '_signin_points_time', 'free_points_detail', 'points_record', 'rebate_rule', 'income_rule', 'pay_down_number', 'balance_record', 'pay_down_log', 'user_addr', '_user_integral_followed', '_signin_integral_time', 'level_integral_date_detail', 'level_integral_detail', 'checkin_reward_days', 'checkin_detail', 'banned_log', 'auth_info', 'medal_details', 'qq', 'weixin', 'weibo', 'github', 'url_name', 'address', 'privacy', 'message_shield', 'favorite_forum_posts', 'follow_plate', 'like-comment', 'like-posts', 'favorite-posts', 'rewards_title', 'cover_image', 'cover_image_id', 'custom_avatar', 'custom_avatar_id', 'rewards_wechat_image_id', 'rewards_alipay_image_id', 'follow-user', 'followed-user'),
        'post_meta'    => array('follow_pay_args', 'score_data', '_user_integral_like', '_user_integral_hot', '_user_integral_essence', '_user_integral_score_extra', '_user_integral_favorite', '_user_integral_new', 'is_hot_notify', '_user_points_hot', '_user_points_essence', '_user_points_score_extra', '_user_points_favorite', '_user_points_like', '_user_points_new', 'vote_option', 'pay_hide_part', 'vote_data', 'add_posts_pending_msg', 'add_posts_publish_msg', 'score_detail', 'page_links_submit_dec', 'page_links_submit_cats', 'page_links_submit_sign_s', 'page_links_submit_title', 'page_links_blank_s', 'page_links_nofollow_s', 'page_links_go_s', 'page_links_submit_s', 'page_links_category', 'page_links_style', 'page_links_search_s', 'page_links_search_types', 'page_links_limit', 'page_links_order', 'page_links_orderby', 'page_links_content_position', 'page_links_content_s', 'documentnav_options', 'description', 'keywords', 'title', 'xzh_tui_back','layout_max_width','page_content_style', 'page_header_style', 'article_maxheight_xz', 'no_article-navs', 'show_layout', 'subtitle', 'featured_video_title', 'featured_video_episode', 'featured_slide', 'featured_video', 'cover_image', 'thumbnail_url', 'pay_down_log'),
        'comment_meta' => array('order_data', 'score_data', '_user_points_adopt', '_user_integral_adopt', '_user_integral_like', '_user_integral_hot', '_user_integral_new', 'is_notify', 'is_hot_notify', 'is_adopted_notify', 'comment_addr', '_user_points_hot', '_user_points_like', '_user_points_new'),
        'term_meta'    => array('xzh_tui_back', 'cover_image', 'term_seo'),
    );

    //添加user_meta
    $social_type = array(
        'qq',
        'weixin',
        'weixingzh',
        'weibo',
        'gitee',
        'baidu',
        'alipay',
        'dingtalk',
        'huawei',
        'xiaomi',
        'github',
        'google',
        'microsoft',
        'facebook',
        'twitter',
    );
    foreach ($social_type as $value) {
        $keys['user_meta'][] = 'oauth_' . $value . '_getUserInfo';
    }

    return $keys[$type];
}

//获取WP—option
function zib_get_option($key)
{
    $option_meta_keys = zib_get_option_meta_keys('option');
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta('option', $key);
    }

    return get_option($key);
}

//设置WP—option
function zib_update_option($key, $value)
{
    $option_meta_keys = zib_get_option_meta_keys('option');
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta('option', $key, $value);
    }

    return update_option($key, $value);
}

//zib - 用户user_meta
function zib_get_user_meta($id, $key, $single = false)
{
    $_type            = 'user_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('user', $id, $key, true);
}

function zib_update_user_meta($id, $key, $value)
{
    $_type            = 'user_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('user', $id, $key, $value);
}

//zib - 文章 post_meta
function zib_get_post_meta($id, $key, $single = false)
{
    $_type            = 'post_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('post', $id, $key, true);
}

function zib_update_post_meta($id, $key, $value)
{
    $_type            = 'post_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('post', $id, $key, $value);
}

//zib - 评论 comment_meta
function zib_get_comment_meta($id, $key, $single = false)
{
    $_type            = 'comment_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('comment', $id, $key, true);
}

function zib_update_comment_meta($id, $key, $value)
{
    $_type            = 'comment_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('comment', $id, $key, $value);
}

//zib - 分类 term_meta
function zib_get_term_meta($id, $key, $single = false)
{
    $_type            = 'term_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_get_option_meta($_type, $key, $id);
    }

    return get_metadata('term', $id, $key, true);
}

function zib_update_term_meta($id, $key, $value)
{
    $_type            = 'term_meta';
    $option_meta_keys = zib_get_option_meta_keys($_type);
    if (in_array($key, $option_meta_keys)) {
        return zib_update_option_meta($_type, $key, $value, $id);
    }

    return update_metadata('term', $id, $key, $value);
}

/**
 * @description:获取 option or meta数据的统一函数
 * @param {*}
 * @param {*}
 * @return {*}
 */
function zib_get_option_meta($type, $key, $meta_id = 0)
{
    $type_args = array(
        'option'       => array('zib_other_options', 'get_option', ''),
        'user_meta'    => array('zib_other_data', 'get_user_meta', ''),
        'post_meta'    => array('zib_other_data', 'get_post_meta', ''),
        'comment_meta' => array('zib_other_data', 'get_comment_meta', ''),
        'term_meta'    => array('zib_other_data', 'get_term_meta', ''),
    );

    $options_key = $type_args[$type][0];
    $fun         = $type_args[$type][1];

    //先查询缓存
    $option_meta_data = wp_cache_get($type . ($meta_id ? '_' . $meta_id : ''), 'zib_option_meta_data');

    if (false === $option_meta_data) {
        if ($type === 'option') {
            $option_meta_data = (array) call_user_func($fun, $options_key);
        } else {
            $option_meta_data = (array) call_user_func($fun, $meta_id, $options_key, true);
        }

        wp_cache_set($type . ($meta_id ? '_' . $meta_id : ''), $option_meta_data, 'zib_option_meta_data');
    }

    return isset($option_meta_data[$key]) ? $option_meta_data[$key] : false;
}

/**
 * @description: 设置option or meta数据的统一函数
 * @param {*}
 * @return {*}
 */
function zib_update_option_meta($type, $key, $value, $meta_id = 0)
{
    $type_args = array(
        'option'       => array('zib_other_options', 'get_option', 'update_option'),
        'user_meta'    => array('zib_other_data', 'get_user_meta', 'update_user_meta'),
        'post_meta'    => array('zib_other_data', 'get_post_meta', 'update_post_meta'),
        'comment_meta' => array('zib_other_data', 'get_comment_meta', 'update_comment_meta'),
        'term_meta'    => array('zib_other_data', 'get_term_meta', 'update_term_meta'),
    );

    $options_key = $type_args[$type][0];
    $get_fun     = $type_args[$type][1];
    $set_fun     = $type_args[$type][2];

    if ($type === 'option') {
        $option_meta_data       = (array) call_user_func($get_fun, $options_key);
        $option_meta_data[$key] = $value;
        $set                    = call_user_func($set_fun, $options_key, $option_meta_data);
    } else {
        $option_meta_data       = (array) call_user_func($get_fun, $meta_id, $options_key, true);
        $option_meta_data[$key] = $value;
        $set                    = call_user_func($set_fun, $meta_id, $options_key, $option_meta_data);
    }

    //更新缓存
    wp_cache_set($type . ($meta_id ? '_' . $meta_id : ''), $option_meta_data, 'zib_option_meta_data');

    return $set;
}

//获取一个随机数
function zib_get_mt_rand_number($var)
{
    $defaults = array(
        'max' => 0,
        'min' => 0,
    );
    $var = wp_parse_args((array) $var, $defaults);

    // 添加参数验证 - 仅需这5行代码
    $min = (int) $var['min'];
    $max = (int) $var['max'];
    if ($min > $max) {
        list($min, $max) = array($max, $min);
    }

    return @mt_rand($min, $max);
}

function zib_get_csf_option_new_badge()
{
    $badges = array(
        '1.0.0'      => '<badge>1.0.0</badge>',
        'open-source' => '<badge>OPEN</badge>',
    );

    // Legacy numeric keys are kept empty for compatibility with existing option definitions.
    foreach (array(7, 8, 9) as $major) {
        for ($minor = 0; $minor <= 9; $minor++) {
            $badges[$major . '.' . $minor] = '';
        }
    }

    return $badges;
}


