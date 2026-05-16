<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2026-05-05 21:13:58
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Email         : 770349780@qq.com
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

// 开启链接管理
add_filter('pre_option_link_manager_enabled', '__return_true');

// 删除WordPress Emoji 表情
if (_pz('remove_emoji', true)) {
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
//开启文章格式
add_theme_support('post-formats', array('image', 'gallery', 'video'));
//开启特色图像
add_theme_support('post-thumbnails', array('post', 'page'));

/**
 * 主题启动时执行函数
 *
 * @return
 */
function zib_init_theme()
{
    global $pagenow;
    if ('themes.php' == $pagenow && isset($_GET['activated'])) {
        wp_redirect(zib_get_admin_csf_url());
    }
}
add_action('after_setup_theme', 'zib_init_theme');
add_action('after_switch_theme', 'zib_init_theme');

//删除google字体
if (_pz('remove_open_sans', true)) {
    function remove_open_sans()
    {
        wp_deregister_style('open-sans');
        wp_register_style('open-sans', false);
        wp_enqueue_style('open-sans', '');
    }
    add_action('init', 'remove_open_sans');
}

// 可选：由站点管理员主动关闭 WordPress 核心、插件、主题的更新检查与后台提示。
// 默认不启用，不影响 Zibll 官方开源版的安装和使用。
if (_pz('display_wp_update')) {
    remove_action('admin_init', '_maybe_update_core'); // 站点级设置：关闭 WordPress 核心更新检查
    remove_action('admin_init', '_maybe_update_plugins'); // 站点级设置：关闭插件更新检查
    remove_action('admin_init', '_maybe_update_themes'); // 站点级设置：关闭主题更新检查
    add_action('admin_menu', 'zib_display_wp_update_notices'); // 站点级设置：关闭后台更新提示

    add_filter('automatic_updater_disabled', '__return_true');
    remove_action('init', 'wp_schedule_update_checks');
    remove_action('wp_version_check', 'wp_version_check');
    remove_action('wp_update_plugins', 'wp_update_plugins');
    remove_action('wp_update_themes', 'wp_update_themes');
    add_filter('auto_update_core', '__return_false');
    add_filter('allow_major_auto_core_updates', '__return_false');
    add_filter('allow_dev_auto_core_updates', '__return_false');
    add_filter('allow_minor_auto_core_updates', '__return_false');
    add_filter('allow_major_auto_core_updates', '__return_false');
}
// 根据管理员配置关闭 WordPress 后台更新提示
function zib_display_wp_update_notices()
{
    remove_action('admin_notices', 'update_nag', 3); // 关闭后台更新提示
    remove_action('network_admin_notices', 'update_nag', 3);
}

//非关闭顶部admin_bar
if (_pz('hide_admin_bar', true) || !is_super_admin()) {
    add_filter('show_admin_bar', '__return_false');
}

if (_pz('disabled_pingback', true)) {
    // 阻止文章内相互 pingback
    add_action('pre_ping', '_noself_ping');
    function _noself_ping(&$links)
    {
        $home = get_option('home');
        foreach ($links as $l => $link) {
            if (0 === strpos($link, $home)) {
                unset($links[$l]);
            }
        }
    }
}

//禁用 XML-RPC 接口
if (_pz('disabled_xmlrpc', true)) {
    add_filter('xmlrpc_enabled', '__return_false');
    add_filter('xmlrpc_methods', '__return_empty_array');
    remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
}

// 注册菜单位置
if (function_exists('register_nav_menus')) {
    register_nav_menus(array(
        'topmenu'    => __('PC端顶部菜单', 'zibll'),
        'mobilemenu' => __('移动端菜单(最多支持两级菜单)', 'zibll'),
    ));
}

//禁用autoembed
if (_pz('disabled_autoembed', true)) {
    remove_filter('the_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
    remove_filter('widget_text_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
    remove_filter('widget_block_content', [$GLOBALS['wp_embed'], 'autoembed'], 8);
    remove_action('edit_form_advanced', [$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
    remove_action('edit_page_form', [$GLOBALS['wp_embed'], 'maybe_run_ajax_cache']);
}

//禁用古腾堡
if (_pz('close_gutenberg')) {
    add_filter('use_block_editor_for_post', '__return_false');
}

//禁用大尺寸缩略图裁剪
if (_pz('disabled_large_thumbnail')) {
    remove_image_size('1536x1536');
    remove_image_size('2048x2048');
    add_filter('intermediate_image_sizes', function ($sizes) {
        return array_merge(array_diff($sizes, array('medium_large', '1536x1536', '2048x2048')));
    }, 20);
}

//wp5.8禁用古腾堡小工具
// 禁止块编辑器管理Gutenberg插件中的小部件。
add_filter('gutenberg_use_widgets_block_editor', '__return_false');
// 禁止块编辑器管理小部件。
add_filter('use_widgets_block_editor', '__return_false');

//移出仪表盘：缓存和php检查
function zib_site_status_tests($tests)
{

    if (isset($tests['async']['page_cache'])) {
        unset($tests['async']['page_cache']);
    }

    if (isset($tests['direct']['php_version'])) {
        unset($tests['direct']['php_version']);
    }

    return $tests;
}
add_filter('site_status_tests', 'zib_site_status_tests');

//禁用php版本检查，提高速度
function zib_pre_http_request($pre, $parsed_args, $url)
{
    //禁止php 联网检查php版本
    $args = [
        'api.wordpress.org/core/serve-happy/', //PHP版本检查
        'api.wordpress.org/core/browse-happy/', //浏览器检查
        'api.wordpress.org/core/credits/', //贡献者检查
    ];

    foreach ($args as $arg) {
        if (strpos($url, $arg) !== false) {
            return array(
                'headers'       => array(),
                'body'          => '',
                'response'      => array(
                    'code'    => false,
                    'message' => false,
                ),
                'cookies'       => array(),
                'http_response' => null,
            );
        }
    }

    return $pre;
}
if (_pz('remove_dashboard_widgets', true)) {
    add_filter('pre_http_request', 'zib_pre_http_request', 10, 3);
}

//wp错误提醒优化
function zib_php_error_message_filter($msg)
{
    if (strpos($msg, 'Supplied nav_menu_item value missing property')) {
        $msg .= '<br>此问题请参看此教程进行处理：<a href="https://www.zibll.com/forum-post/16833.html">https://www.zibll.com/forum-post/16833.html</a>';
    }
    return $msg;
}
add_filter('wp_php_error_message', 'zib_site_status_tests');

function _name($name, $fenge = ' ')
{
    $n = 'Zibll';
    return $n . $fenge . $name;
}

//用zibll的登录页面代替系统的登录页面
function zib_replace_wp_login()
{

    $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
    if ($action) {
        $redirect_to          = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
        $tab                  = array();
        $tab['signin']        = array('login');
        $tab['signup']        = array('register');
        $tab['resetpassword'] = array('lostpassword', 'retrievepassword', 'resetpass', 'rp');
        $tab_v                = '';
        foreach ($tab as $key => $value) {
            if (in_array($action, $value)) {
                $tab_v = $key;
                break;
            }
        }
        if ($tab_v) {
            $url = add_query_arg('redirect_to', urlencode($redirect_to), zib_get_sign_url($tab_v));
            if (isset($_REQUEST['interim-login'])) {
                $url = add_query_arg('interim-login', 1, $url);
            }
            wp_safe_redirect($url);
            exit();
        }
    }
}

//用zibll的登录页面代替系统的登录页面
function zib_replace_wp_login_sign()
{
    $redirect_to = !empty($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
    $url         = add_query_arg('redirect_to', urlencode($redirect_to), zib_get_sign_url('signin'));
    if (isset($_REQUEST['interim-login'])) {
        $url = add_query_arg('interim-login', 1, $url); //兼容后台弹窗登录
    }
    wp_safe_redirect($url);
    exit();
}

function zib_replace_wp_login_add_action()
{
    if (_pz('replace_wp_login') && !is_user_logged_in()) {
        @session_start();
        $login_key = !empty($_GET['login-key']) ? $_GET['login-key'] : (!empty($_SESSION['WP_LOGIN_KEY']) ? $_SESSION['WP_LOGIN_KEY'] : false);

        if (!empty($_GET['login-key'])) {
            $_SESSION['WP_LOGIN_KEY'] = $_GET['login-key'];
        }

        @session_write_close();
        if (!$login_key || $login_key !== _pz('admin_sign_key')) {
            add_action('login_init', 'zib_replace_wp_login');
            add_action('login_form_login', 'zib_replace_wp_login_sign');
        }
    }
}
add_action('wp_loaded', 'zib_replace_wp_login_add_action');

function zib_del_wp_login_key()
{
    if (get_current_user_id()) {
        @session_start();
        @$_SESSION['WP_LOGIN_KEY'] = false;
        @session_write_close();
    }
}
add_action('wp_login', 'zib_del_wp_login_key');

//按组清理缓存
if (!function_exists('wp_cache_delete_group')) {
    function wp_cache_delete_group($group)
    {
        global $wp_object_cache;
        if (isset($wp_object_cache->cache[$group])) {
            unset($wp_object_cache->cache[$group]);
        }
    }
}

//按组清理缓存
//注意：此函数非常慢，不建议使用，如果需要按组清理缓存，建议将key作为数组保存到缓存中，然后循环清理
//可以参考zib_bbs_dynamic_score_ids_cache_flush函数
if (!function_exists('wp_cache_flush_group')) {
    function wp_cache_flush_group($group)
    {
        wp_cache_delete_group($group);
    }
}

//编辑文章-清理文章缓存
function zib_cache_delete_posts($post_id)
{
    //文章缩略图
    wp_cache_delete($post_id, 'post_thumbnail_url_thumbnail');
    wp_cache_delete($post_id, 'post_thumbnail_url_medium');
    wp_cache_delete($post_id, 'post_thumbnail_url_large');
    wp_cache_delete($post_id, 'post_thumbnail_url_full');
    //文章多图模式缩略图
    wp_cache_delete($post_id, 'post_multi_thumbnail');
}
add_action('save_post', 'zib_cache_delete_posts');

/**
 * @description: 获取所有管理员账号
 * @param {*}
 * @return {*}
 */
function zib_get_admin_users()
{
    return get_users('role=Administrator');
}

/**
 * @description: 获取所有管理员账号的邮箱
 * @param {*}
 * @return {*}
 */
function zib_get_admin_user_emails()
{
    $users = zib_get_admin_users();
    $email = array(get_option('admin_email'));
    if ($users) {
        foreach ($users as $user) {
            if (!empty($user->user_email) && !in_array($user->user_email, $email)) {
                $email[] = $user->user_email;
            }
        }
    }
    return $email;
}

/**
 * @description: 获取所有管理员账号的IDs
 * @param {*}
 * @return {*}
 */
function zib_get_admin_user_ids()
{
    $users = zib_get_admin_users();
    $ids   = array();
    if ($users) {
        foreach ($users as $user) {
            if (!empty($user->ID) && !in_array($user->ID, $ids)) {
                $ids[] = $user->ID;
            }
        }
    }
    return $ids;
}

//初始化文章参数
function zib_initialization_post_favorite($post_ID, $post, $update)
{
    if ($update) {
        return;
    }

    $post_type = $post->post_type;

    $key['post'] = array(
        'favorite' => 0,
        'views'    => zib_get_mt_rand_number(_pz('post_default_mate', '', 'views')),
        'like'     => zib_get_mt_rand_number(_pz('post_default_mate', '', 'like')),
    );

    if (!isset($key[$post_type])) {
        return;
    }

    foreach ($key[$post_type] as $k => $v) {
        add_post_meta($post_ID, $k, $v);
    }
}
add_action('save_post', 'zib_initialization_post_favorite', 99, 3);

//为作者添加前台的edit_posts
function zib_add_user_has_cap_edit_posts($allcaps, $caps)
{
    if (!empty($allcaps['edit_posts'])) {
        return $allcaps;
    }

    $user_id = get_current_user_id();
    if ($user_id && in_array('edit_posts', $caps)) {
        global $wp_query;
        if ($wp_query->is_single() && isset($wp_query->posts[0]->post_author) && $wp_query->posts[0]->post_author == $user_id) {
            $allcaps['edit_posts'] = true;
        }
    }
    return $allcaps;
}
add_filter('user_has_cap', 'zib_add_user_has_cap_edit_posts', 11, 2);

/*注册专题*/
function zib_register_topics()
{
    $labels = [
        'name'              => __('专题'),
        'singular_name'     => __('专题'),
        'search_items'      => __('搜索专题'),
        'all_items'         => __('所有专题'),
        'parent_item'       => __('父专题'),
        'parent_item_colon' => __('父专题:'),
        'edit_item'         => __('编辑专题'),
        'update_item'       => __('更新专题'),
        'add_new_item'      => __('添加新专题'),
        'new_item_name'     => __('新专题名称'),
        'menu_name'         => __('专题'),
    ];
    $args = [
        'description'       => '添加文章专题',
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'query_var'         => true,
    ];
    register_taxonomy('topics', ['post'], $args);
}
add_action('init', 'zib_register_topics');

//百度资源提交
function zib_post_baidu_resource_submission($post_id)
{

    $post = get_post($post_id);
    if (empty($post->ID) || 'publish' !== $post->post_status) {
        return;
    }

    $post_id = $post->ID;

    //重新提交
    if (!empty($_POST['xzh_post_resubmit'])) {
        //如果勾选了重新推送，则清空之前的数据
        zib_update_post_meta($post_id, 'xzh_tui_back', false);
    }

    $ok = zib_get_post_meta($post_id, 'xzh_tui_back', true);

    //已经提交成功
    if (!empty($ok['normal_push'])) {
        return;
    }
    $plink = get_permalink($post_id);

    $baidu = zib_baidu_resource_submission($plink);
    zib_update_post_meta($post_id, 'xzh_tui_back', $baidu);
}

if ((_pz('xzh_post_on') || _pz('xzh_post_daily_push')) && _pz('xzh_post_token')) {
    add_action('save_post', 'zib_post_baidu_resource_submission');
    add_action('saved_term', 'zib_term_baidu_resource_submission');
}

function zib_term_baidu_resource_submission($term_id)
{
    //重新提交
    if (!empty($_POST['xzh_post_resubmit'])) {
        //如果勾选了重新推送，则清空之前的数据
        zib_update_term_meta($term_id, 'xzh_tui_back', false);
    }
    $ok = zib_get_term_meta($term_id, 'xzh_tui_back', true);
    //已经提交成功
    if (!empty($ok['normal_push'])) {
        return;
    }
    $plink = get_term_link($term_id);

    $baidu = zib_baidu_resource_submission($plink);
    zib_update_term_meta($term_id, 'xzh_tui_back', $baidu);
}

//百度资源提交
function zib_baidu_resource_submission($url)
{

    if (!is_array($url)) {
        $urls   = array();
        $urls[] = $url;
    } else {
        $urls = $url;
    }

    if (!_pz('xzh_post_on') || !_pz('xzh_post_token') || !$urls) {
        return;
    }

    $timeout     = 5000; //5秒超时
    $token       = _pz('xzh_post_token');
    $site        = home_url();
    $result_meta = array();
    $api         = 'http://data.zz.baidu.com/urls?site=' . $site . '&token=' . $token;

    $http = new Yurun\Util\HttpRequest;

    //普通收录
    $response = $http->timeout($timeout)->post($api, implode("\n", $urls));
    $result   = $response->json();
    if (!empty($result->success)) {
        $result_meta['normal_push'] = true;
    } else {
        $result_meta['normal_push'] = false;
    }
    $result_meta['normal_result'] = $result;

    //快速收录
    if (_pz('xzh_post_daily_push')) {
        $api .= '&type=daily';
        $response = $http->timeout($timeout)->post($api, implode("\n", $urls));
        $result   = $response->json();
        if (!empty($result->success)) {
            $result_meta['daily_push'] = true;
        } else {
            $result_meta['daily_push'] = false;
        }
        $result_meta['daily_result'] = $result;
    }
    $result_meta['update_time'] = current_time('Y-m-d H:i:s');

    return $result_meta;
}

//获取用户id
function zib_get_user_id($id_or_email)
{
    $user_id = '';
    if (is_numeric($id_or_email)) {
        $user_id = (int) $id_or_email;
    } elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email))) {
        $user_id = $user->ID;
    } elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) {
        $user_id = (int) $id_or_email->user_id;
    }

    return $user_id;
}

//用户默认头像
function zib_default_avatar()
{
    return _pz('avatar_default_img') ?: ZIB_TEMPLATE_DIRECTORY_URI . '/img/avatar-default.png';
}

//懒加载占位图
function zib_get_lazy_thumb($size = '')
{
    $size = $size ? '-' . $size : '';
    return _pz('thumbnail') ?: ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail' . $size . '.svg';
}

//文章备用缩略图
function zib_get_spare_thumb()
{
    $spare_thumb = _pz('spare_thumbnail');
    if (empty($spare_thumb[0]['img'])) {
        return zib_get_lazy_thumb();
    }

    $spare_thumb_img = array_column($spare_thumb, 'img');

    return $spare_thumb_img[array_rand($spare_thumb_img, 1)];
}

function zib_get_data_avatar($user_id = '', $size = '', $alt = '')
{
    $args = array(
        'size'   => $size,
        'height' => $size,
        'width'  => $size,
        'alt'    => $alt,
    );
    $cache = wp_cache_get($user_id, 'user_avatar', true);
    if (false === $cache) {
        $avatar = zib_get_avatar(null, $user_id, $args);
        wp_cache_set($user_id, $avatar, 'user_avatar');
    } else {
        $avatar = $cache;
    }
    if (zib_is_lazy('lazy_avatar')) {
        $avatar = str_replace(' src=', ' src="' . zib_default_avatar() . '" data-src=', $avatar);
        $avatar = str_replace(' class="', ' class="lazyload ', $avatar);
    }
    return $avatar;
}

//刷新头像缓存
add_action('user_save_custom_avatar', function ($user_id) {
    wp_cache_delete($user_id, 'user_avatar');
    zib_get_data_avatar($user_id);
}, 10);

add_filter('pre_get_avatar', 'zib_get_avatar', 10, 3);
function zib_get_avatar($avatar, $id_or_email, $args)
{
    $user_id = zib_get_user_id($id_or_email);

    $custom_avatar = $user_id ? zib_get_user_meta($user_id, 'custom_avatar', true) : '';
    $alt           = $user_id ? get_the_author_meta('nickname', $user_id) . '的头像' . zib_get_delimiter_blog_name() : '头像';

    $avatar = $custom_avatar ? $custom_avatar : zib_default_avatar();

    //优化百度头像地址
    $avatar = str_replace('tb.himg.baidu.com', 'himg.bdimg.com', $avatar);
    $avatar = preg_replace('/^(https:|http:)/', '', $avatar);

    $args['size'] = esc_attr($args['size']);
    return '<img alt="' . esc_attr($alt) . '" src="' . esc_url($avatar) . '" class="avatar' . ($args['size'] ? ' avatar-' . $args['size'] : '') . ' avatar-id-' . $user_id . '"' . ($args['size'] ? ' height="' . $args['size'] . '" width="' . $args['size'] . '"' : '') . '>';
}

/**
 * @description: 添加定时任务调度周期
 * @param {*}
 * @return {*}
 */
function zib_cron_schedules_filter($schedules)
{
    return array_merge($schedules, [
        'monthly' => ['interval' => 2678400, 'display' => '每月一次'],
    ]);
}
add_filter('cron_schedules', 'zib_cron_schedules_filter');

// 侧边栏显示判断
function zib_is_show_sidebar()
{
    return apply_filters('zib_is_show_sidebar', zib_is_show_sidebar_filter());
}

function zib_is_show_sidebar_filter()
{
    $is = false;
    if (wp_is_mobile()) {
        return false;
    }
    if (is_single() || is_page()) {
        $show_layout = zib_get_post_meta(get_queried_object_id(), 'show_layout', true);
        $sites       = array('sidebar_left', 'sidebar_right');
        if (in_array($show_layout, $sites)) {
            return true;
        } elseif ('no_sidebar' == $show_layout) {
            return false;
        }
    }
    if (_pz('sidebar_home_s') && is_home()) {
        return true;
    }
    if (_pz('sidebar_single_s') && is_single()) {
        return true;
    }
    if (_pz('sidebar_page_s', false) && is_page()) {
        return true;
    }
    if (_pz('sidebar_cat_s') && is_category()) {
        return true;
    }
    if (_pz('sidebar_tag_s') && is_tag()) {
        return true;
    }
    if (_pz('sidebar_search_s') && is_search()) {
        return true;
    }

    if (is_page_template('pages/postsnavs.php')) {
        return true;
    }

    if (is_page_template('pages/newposts.php')) {
        return true;
    }
    if (is_page_template('pages/sidebar.php')) {
        return true;
    }
    return $is;
}

//获取随机布尔值
function zib_random_true($inex = 5)
{
    return (rand() % $inex === 0);
}

// 禁止非管理员登录后台
if (_pz('user_disable_admin', true)) {
    add_action('admin_init', 'zib_no_entry_backstage');
}
function zib_no_entry_backstage()
{
    if (!is_super_admin() && !stristr($_SERVER['PHP_SELF'], 'admin-ajax.php')) {
        wp_redirect(home_url());
        exit;
    }
}

//限制发布频率
function zib_brush_limit_post($post_data)
{

    if (!empty($post_data['ID']) || empty($post_data['post_author'])) {
        return;
    }

    //管理员不限制
    if (is_super_admin($post_data['post_author'])) {
        return;
    }

    $post_type   = isset($post_data['post_type']) ? $post_data['post_type'] : 'post';
    $post_author = (int) $post_data['post_author'];
    if (!$post_author) {
        return;
    }

    if (!in_array($post_type, ['post', 'plate', 'forum_post'])) {
        return;
    }

    global $wpdb;

    $brush_limit_post = _pz('brush_limit_post');
    $minutes_10       = isset($brush_limit_post['minutes_10']) ? (int) $brush_limit_post['minutes_10'] : 0;
    $day_1            = isset($brush_limit_post['day_1']) ? (int) $brush_limit_post['day_1'] : 0;
    $sql_prefix       = "SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type in ('post','plate','forum_post') AND post_author=$post_author"; //前缀

    if ($minutes_10) {
        $date_query_args = array(
            'after' => '10 minutes ago',
        );
        $date_query = new WP_Date_Query($date_query_args, 'post_date');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $minutes_10) {
            zib_send_json_error('您的操作过于频繁，请10分钟后再试');
        }
    }

    if ($day_1) {
        $date_query_args = array(
            'after' => date('Y-m-d', strtotime('-1 day')),
        );
        $date_query = new WP_Date_Query($date_query_args, 'post_date');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $day_1) {
            zib_send_json_error('您的提交已达今日上限，请明日再试');
        }
    }
}
add_action('zib_pre_insert_post', 'zib_brush_limit_post');

//限制评论频率
function zib_brush_limit_comment($approved, $commentdata)
{

    if (!empty($commentdata['comment_ID']) || empty($commentdata['comment_author'])) {
        return $approved;
    }

    //管理员不限制
    if (!empty($commentdata['user_id']) && is_super_admin($commentdata['user_id'])) {
        return $approved;
    }

    $user_id           = !empty($commentdata['user_id']) ? (int) $commentdata['user_id'] : 0;
    $comment_author_IP = !empty($commentdata['comment_author_IP']) ? esc_sql($commentdata['comment_author_IP']) : 0;
    $comment_author    = !empty($commentdata['comment_author']) ? esc_sql($commentdata['comment_author']) : '';

    //如果IP地址为本地，则为false
    if (preg_match('/^(?:127|172\.16|192\.168)\./', $comment_author_IP)) {
        $comment_author_IP = false;
    }

    global $wpdb;

    $brush_limit_post = _pz('brush_limit_comment');
    $minutes_10       = isset($brush_limit_post['minutes_10']) ? (int) $brush_limit_post['minutes_10'] : 0;
    $day_1            = isset($brush_limit_post['day_1']) ? (int) $brush_limit_post['day_1'] : 0;

    $sql_prefix = "SELECT COUNT(*) FROM `$wpdb->comments` WHERE ";
    $sql_prefix .= $user_id ? "user_id = $user_id" : ($comment_author_IP ? "comment_author_IP = '$comment_author_IP'" : "comment_author = '$comment_author'");

    if ($minutes_10) {
        $date_query_args = array(
            'after' => '10 minutes ago',
        );
        $date_query = new WP_Date_Query($date_query_args, 'comment_date');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $minutes_10) {
            $comment_flood_message = '您的操作过于频繁，请10分钟后再试';
            return new WP_Error('comment_flood', $comment_flood_message, 429);
        }
    }

    if ($day_1) {
        $date_query_args = array(
            'after' => date('Y-m-d', strtotime('-1 day')),
        );
        $date_query = new WP_Date_Query($date_query_args, 'comment_date');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $day_1) {
            $comment_flood_message = '您的提交已达今日上限，请明日再试';
            return new WP_Error('comment_flood', $comment_flood_message, 429);
        }
    }

    return $approved;
}
add_filter('pre_comment_approved', 'zib_brush_limit_comment', 99, 2);

//注册防刷限制
function zib_brush_limit_register($errors, $sanitized_user_login, $user_email)
{

    $brush_limit_post = _pz('brush_limit_register');
    $minutes_10       = isset($brush_limit_post['minutes_10']) ? (int) $brush_limit_post['minutes_10'] : 0;
    $day_1            = isset($brush_limit_post['day_1']) ? (int) $brush_limit_post['day_1'] : 0;
    $ip_addr          = zib_get_remote_ip_addr();
    //如果没有IP地址，或者IP地址为本地，则退出
    if (!$ip_addr || preg_match('/^(?:127|172\.16|192\.168)\./', $ip_addr)) {
        return $errors;
    }

    global $wpdb;
    $sql_prefix = "SELECT COUNT( * ) FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) WHERE ( {$wpdb->usermeta}.meta_key = 'register_ip' AND {$wpdb->usermeta}.meta_value = '$ip_addr' )"; //前缀

    if ($minutes_10) {
        $date_query_args = array(
            'after' => '10 minutes ago',
        );
        $date_query = new WP_Date_Query($date_query_args, 'user_registered');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $minutes_10) {
            $errors->add('brush_limit', '您的操作过于频繁，请10分钟后再试');
        }
    }

    if ($day_1) {
        $date_query_args = array(
            'after' => date('Y-m-d', strtotime('-1 day')),
        );
        $date_query = new WP_Date_Query($date_query_args, 'user_registered');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $day_1) {
            $errors->add('brush_limit', '您的账号注册已达今日上限，请明日再试');
        }
    }

    return $errors;
}
add_filter('registration_errors', 'zib_brush_limit_register', 50, 3);

//限制上传频率
function zib_brush_limit_upload($file)
{

    $user_id = get_current_user_id();

    //管理员不限制
    if (!$user_id || is_super_admin($user_id)) {
        return $file;
    }

    $post_type   = 'attachment';
    $post_author = $user_id;

    global $wpdb;

    $brush_limit_post = _pz('brush_limit_upload');
    $minutes_10       = isset($brush_limit_post['minutes_10']) ? (int) $brush_limit_post['minutes_10'] : 0;
    $day_1            = isset($brush_limit_post['day_1']) ? (int) $brush_limit_post['day_1'] : 0;
    $sql_prefix       = "SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = '$post_type' AND post_author=$post_author"; //前缀

    if ($minutes_10) {
        $date_query_args = array(
            'after' => '10 minutes ago',
        );
        $date_query = new WP_Date_Query($date_query_args, 'post_date');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $minutes_10) {
            $file['error'] = '您的操作过于频繁，请10分钟后再试';
            $file['debug'] = array(
                'sql'   => $sql,
                'count' => $count,
                'opt'   => $minutes_10,
            );
            return $file;
        }
    }

    if ($day_1) {
        $date_query_args = array(
            'after' => date('Y-m-d', strtotime('-1 day')),
        );
        $date_query = new WP_Date_Query($date_query_args, 'post_date');
        $sql        = $sql_prefix . $date_query->get_sql();
        $count      = $wpdb->get_var($sql);

        if ($count > $day_1) {
            $file['error'] = '您的文件上传已达今日上限，请明日再试';
            $file['debug'] = array(
                'sql'   => $sql,
                'count' => $count,
                'opt'   => $day_1,
            );

            return $file;
        }
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'zib_brush_limit_upload', 99);

// 分类链接删除 'category'
if (_pz('no_categoty') && !function_exists('no_category_base_refresh_rules')) {
    register_activation_hook(__FILE__, 'no_category_base_refresh_rules');
    add_action('created_category', 'no_category_base_refresh_rules');
    add_action('edited_category', 'no_category_base_refresh_rules');
    add_action('delete_category', 'no_category_base_refresh_rules');
    function no_category_base_refresh_rules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    register_deactivation_hook(__FILE__, 'no_category_base_deactivate');
    function no_category_base_deactivate()
    {
        remove_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');
        // We don't want to insert our custom rules again
        no_category_base_refresh_rules();
    }

    // Remove category base
    add_action('init', 'no_category_base_permastruct');
    function no_category_base_permastruct()
    {
        global $wp_rewrite, $wp_version;
        if (version_compare($wp_version, '3.4', '<')) {
            // For pre-3.4 support
            $wp_rewrite->extra_permastructs['category'][0] = '%category%';
        } else {
            $wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
        }
    }

    // Add our custom category rewrite rules
    add_filter('category_rewrite_rules', 'no_category_base_rewrite_rules');
    function no_category_base_rewrite_rules($category_rewrite)
    {
        //var_dump($category_rewrite); // For Debugging

        $category_rewrite = array();
        $categories       = get_categories(array('hide_empty' => false));
        foreach ($categories as $category) {
            $category_nicename = $category->slug;
            if ($category->parent == $category->cat_ID) // recursive recursion
            {
                $category->parent = 0;
            } elseif (0 != $category->parent) {
                $category_nicename = get_category_parents($category->parent, false, '/', true) . $category_nicename;
            }

            $category_rewrite['(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/page/?([0-9]{1,})/?$']                  = 'index.php?category_name=$matches[1]&paged=$matches[2]';
            $category_rewrite['(' . $category_nicename . ')/?$']                                    = 'index.php?category_name=$matches[1]';
        }
        // Redirect support from Old Category Base
        global $wp_rewrite;
        $old_category_base                               = get_option('category_base') ? get_option('category_base') : 'category';
        $old_category_base                               = trim($old_category_base, '/');
        $category_rewrite[$old_category_base . '/(.*)$'] = 'index.php?category_redirect=$matches[1]';

        //var_dump($category_rewrite); // For Debugging
        return $category_rewrite;
    }
    // Add 'category_redirect' query variable
    add_filter('query_vars', 'no_category_base_query_vars');
    function no_category_base_query_vars($public_query_vars)
    {
        $public_query_vars[] = 'category_redirect';
        return $public_query_vars;
    }

    // Redirect if 'category_redirect' is set
    add_filter('request', 'no_category_base_request');
    function no_category_base_request($query_vars)
    {
        //print_r($query_vars); // For Debugging
        if (isset($query_vars['category_redirect'])) {
            $catlink = trailingslashit(get_option('home')) . user_trailingslashit($query_vars['category_redirect'], 'category');
            status_header(301);
            header("Location:$catlink");
            exit();
        }
        return $query_vars;
    }
}

//颜色转换
function hex_to_rgba($hex, $a)
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $a   = $a ? ',' . $a : '';
    $rgb = 'rgb' . ($a ? 'a' : '') . '(' . $r . ',' . $g . ',' . $b . $a . ')';
    return $rgb;
}
// 加载css和js文件
add_action('wp_enqueue_scripts', '_load_scripts');
function _load_scripts()
{
    if (!is_admin()) {
        wp_deregister_script('jquery');

        wp_deregister_script('l10n');

        $purl = ZIB_TEMPLATE_DIRECTORY_URI;

        _cssloader(array(
            'bootstrap'   => 'bootstrap.min',
            'fontawesome' => 'font-awesome.min',
            'main'        => 'main.min',
        ));

        // page css
        if (is_page_template('pages/postsnavs.php')) {
            _cssloader(array('page-navs' => 'page-navs.min'));
        }

        //jq放在顶部，堵塞CSS加载进程，可以防止页面晃动
        wp_enqueue_script('jquery', $purl . '/js/libs/jquery.min.js', false, THEME_VERSION, false);
        wp_enqueue_script('bootstrap', $purl . '/js/libs/bootstrap.min.js', array('jquery'), THEME_VERSION, true);
        _jsloader(array('loader'));
    }
}

function _cssloader($arr)
{
    foreach ($arr as $key => $item) {
        $href = $item;
        if (strstr($href, '//') === false) {
            $href = ZIB_TEMPLATE_DIRECTORY_URI . '/css/' . $item . '.css';
        }
        wp_enqueue_style('_' . $key, $href, array(), THEME_VERSION, 'all');
    }
}
function _jsloader($arr)
{
    foreach ($arr as $item) {
        wp_enqueue_script('_' . $item, ZIB_TEMPLATE_DIRECTORY_URI . '/js/' . $item . '.js', array(), THEME_VERSION, true);
    }
}

function _get_delimiter()
{
    return _pz('connector') ? _pz('connector') : '-';
}

//文章列表新窗口打开
function _post_target_blank()
{
    return _pz('target_blank') ? ' target="_blank"' : '';
}

//中文用户名注册
function chinese_username($username, $raw_username, $strict)
{
    $username = wp_strip_all_tags($raw_username);
    $username = remove_accents($username);
    $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
    $username = preg_replace('/&.+?;/', '', $username); // Kill entities
    if ($strict) {
        $username = preg_replace('|[^a-z\p{Han}0-9 _.\-@]|iu', '', $username);
    }
    $username = trim($username);
    $username = preg_replace('|\s+|', ' ', $username);
    return $username;
}
add_filter('sanitize_user', 'chinese_username', 99, 3);
function chinese_nicename($nicename)
{
    //中文用户名->解决数据库nicename字段varchar(50)导致的字串符长
    return urldecode($nicename);
}
add_filter('pre_user_nicename', 'chinese_nicename');

//用户中心链接重写，重定向格式化
function zib_author_link($link, $author_id, $author_nicename)
{
    global $wp_rewrite;
    $author_id = (int) $author_id;
    $link      = $wp_rewrite->get_author_permastruct();

    if (empty($link)) {
        $file = home_url('/');
        $link = $file . '?author=' . $author_id;
    } else {
        $link = str_replace('%author%', $author_id, $link);
        $link = home_url(user_trailingslashit($link));
    }

    return $link;
}
add_filter('author_link', 'zib_author_link', 10, 3);

function zib_author_link_request($query_vars)
{
    if (isset($query_vars['author_name'])) {
        $author_id = (int) $query_vars['author_name'];
        if ($author_id) {
            $query_vars['author'] = $author_id;
            unset($query_vars['author_name']);
        }
    }
    return $query_vars;
}
add_filter('request', 'zib_author_link_request');

function get_the_subtitle($span = true, $class = '', $post_id = null)
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $subtitle = zib_get_post_meta($post_id, 'subtitle', true);

    $class = $class ? ' class="' . $class . '"' : '';
    if (!empty($subtitle)) {
        if ($span) {
            return '<span' . $class . '>' . $subtitle . '</span>';
        } else {
            return $subtitle;
        }
    } else {
        return;
    }
}

function zib_get_subtitle($post_id)
{
    if (!$post_id) {
        return;
    }

    $subtitle = zib_get_post_meta($post_id, 'subtitle', true);
    return $subtitle;
}

//小工具可视化编辑连接
function zib_get_customize_widgets_url($url = null)
{
    $url = $url ? $url : zib_get_current_url();
    return esc_url(
        add_query_arg(
            array(
                'url'       => $url,
                'autofocus' => array('panel' => 'widgets'),
                'return'    => urlencode($url),
            ),
            admin_url('customize.php')
        )
    );
}

//主题切换
function zib_get_theme_mode()
{
    $theme_mode = _pz('theme_mode');
    if (_pz('theme_mode_button', true) && isset($_COOKIE['theme_mode'])) {
        $theme_mode = $_COOKIE['theme_mode'];
    } else {
        $time = current_time('G');
        if ('time-auto' == $theme_mode) {
            if ($time >= 20 || $time < 8) {
                $theme_mode = 'dark-theme';
            } else {
                $theme_mode = 'white-theme';
            }
        }
    }
    return apply_filters('zib_theme_mode', $theme_mode);
}

//位置
function zib_get_theme_mode_button_positions()
{
    return (array) apply_filters('zib_theme_mode_button_positions', (array) _pz('theme_mode_button', array('pc_nav', 'm_menu')));
}

//根据主题筛选图片
function zib_get_adaptive_theme_img($white_src = '', $dark_src = '', $alt = '', $more = '', $lazy = false)
{
    if (!$dark_src && !$white_src) {
        return;
    }

    if (!$alt) {
        $alt = get_bloginfo('name');
    }

    if (!$dark_src) {
        $dark_src = $white_src;
    }

    if (!$white_src) {
        $white_src = $dark_src;
    }

    $lazy_src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-sm.svg';
    if (zib_get_theme_mode() == 'dark-theme') {
        $img = '<img ' . ($lazy ? 'src="' . $lazy_src . '" data-' : '') . 'src="' . $dark_src . '" switch-src="' . $white_src . '" alt="' . $alt . '" ' . $more . '>';
    } else {
        $img = '<img ' . ($lazy ? 'src="' . $lazy_src . '" data-' : '') . 'src="' . $white_src . '" switch-src="' . $dark_src . '" alt="' . $alt . '" ' . $more . '>';
    }
    return $img;
}

function _bodyclass()
{
    $class = '';

    $class .= zib_get_theme_mode();

    if (is_super_admin()) {
        $class .= ' logged-admin';
    }
    $paged = zib_get_the_paged();
    if (_pz('nav_fixed') || (is_home() && 1 == $paged && _pz('index_slide_s') && _pz('index_slide_position', 'top') == 'header' && _pz('index_slide_src_1'))) {
        $class .= ' nav-fixed';
    }

    if (zib_is_show_sidebar()) {
        $show_layout = zib_get_post_meta(get_queried_object_id(), 'show_layout', true);
        if ('sidebar_left' == $show_layout) {
            $layout_class = ' site-layout-3';
        } elseif ('sidebar_right' == $show_layout) {
            $layout_class = ' site-layout-2';
        } else {
            $layout_class = _pz('sidebar_layout') == 'left' ? ' site-layout-3' : ' site-layout-2';
        }
        $class .= $layout_class;
    } else {
        $class .= ' site-layout-1';
    }

    if ((is_single() || is_page()) && get_post_format()) {
        $class .= ' postformat-' . get_post_format();
    }

    //关闭高斯模糊
    if (_pz('close_backdrop')) {
        $class .= ' fps-accelerat';
    } elseif (!empty($_COOKIE['fps_accelerat']) && $_COOKIE['fps_accelerat'] < 20) {
        $class .= ' fps-accelerat';
    }

    return apply_filters('zib_add_bodyclass', trim($class));
}

function _cut_count($number)
{
    $number = (float) $number;
    if ($number > 999999) {
        return round($number / 10000, 0) . 'W+';
    }
    if ($number > 9999) {
        return round($number / 10000, 1) . 'W+';
    }
    return $number;
}

function zib_get_user_favorite_count($author_id, $cut = true)
{
    if (!$author_id) {
        return;
    }

    $count_all = apply_filters('author_tab_favorite_count', get_user_favorite_post_count($author_id, false), $author_id);
    if ($cut) {
        return _cut_count($count_all);
    }
    return $count_all;
}

function get_user_favorite_post_count($user_id, $_cut = true)
{
    if (!$user_id) {
        return;
    }

    $cache_num = wp_cache_get($user_id, 'user_favorite_post_count', true);
    if (false !== $cache_num) {
        $count_all = $cache_num;
    } else {
        $favorite_ids = array_reverse((array) zib_get_user_meta($user_id, 'favorite-posts', true));
        if ($favorite_ids) {
            $args = array(
                'posts_per_page' => 1,
                'paged'          => 0,
                'post__in'       => $favorite_ids,
                'fields'         => 'ids',
            );
            $the_query = new WP_Query($args);

            $count_all = $the_query->found_posts;
            //添加缓存
            wp_cache_set($user_id, $count_all, 'user_favorite_post_count');
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
function zib_user_favorite_post_count_update_cache($meta_id, $user_id, $meta_key, $_meta_value)
{
    if ('favorite-posts' === $meta_key) {
        wp_cache_delete($user_id, 'user_favorite_post_count');
        get_user_favorite_post_count($user_id);
    }
}
add_action('updated_user_meta', 'zib_user_favorite_post_count_update_cache', 10, 4);
add_action('added_user_meta', 'zib_user_favorite_post_count_update_cache', 10, 4);

//获取文章评论数量
function get_post_comment_count($before = '评论[', $after = ']', $post_id = 0)
{
    if (!comments_open() || _pz('close_comments')) {
        return;
    }

    $post_id = $post_id ? $post_id : get_the_ID();
    $like    = _cut_count(get_comments_number($post_id));
    return $before . $like . $after;
}

//获取文章点赞数量
function get_post_like_count($before = '点赞[', $after = ']', $post_id = 0)
{
    if (!_pz('post_like_s')) {
        return;
    }

    $post_id = $post_id ? $post_id : get_the_ID();
    $like    = _cut_count(get_post_meta($post_id, 'like', true));
    return $before . $like . $after;
}

function get_post_favorite_count($before = '收藏[', $after = ']', $post_id = 0)
{
    if (zib_is_close_sign()) {
        return;
    }

    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $views = _cut_count(get_post_meta($post_id, 'favorite', true));
    return $before . $views . $after;
}

function get_post_view_count($before = '阅读[', $after = ']', $post_id = 0)
{
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }
    $views = _cut_count(get_post_meta($post_id, 'views', true));
    return $before . $views . $after;
}

function zib_str_cut($str, $start = 0, $width = 100, $trimmarker = '...')
{
    if (('' == $str) || is_null($str)) {
        return $str;
    }

    if (zib_new_strlen($str) < ($width - 2)) {
        return $str;
    }

    $code  = 'UTF-8';
    $str   = mb_convert_encoding($str, $code, mb_detect_encoding($str, array('UTF-8', 'ASCII', 'GB2312', 'GBK')));
    $start = (int) $start > 0 ? (int) $start : 0;
    $len   = (int) $width > 0 ? (int) $width * 2 : null;
    $cl    = $byteL    = 0;
    $sub   = '';
    $sLen  = mb_strlen($str, $code);

    for ($i = 0; $i < $sLen; $i++) {
        $val = mb_substr($str, $i, 1, $code);
        $cl  = ord($val) >= 128 ? 2 : 1;
        $byteL += $cl;

        if ($start >= $byteL) {
            //还不到开始位
            continue;
        }

        if (
            is_null($len) //取完
            || (($len -= $cl) >= 0) //取本字时不超过
        ) {
            $sub .= $val;
        } else {
            //取超了
            $trimmarker && ($sub .= $trimmarker);
            break;
        }
    }
    return $sub;
}

function zib_get_excerpt($limit = 90, $after = '...', $post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $excerpt = trim(zib_get_post_meta($post->ID, 'description', true));

    if (!$excerpt) {
        if (!empty($post->post_excerpt)) {
            $excerpt = get_the_excerpt($post);
        } else {
            $excerpt = strip_shortcodes($post->post_content);

        }
    }

    $excerpt = apply_filters('zib_get_excerpt', $excerpt, $post);
    $excerpt = trim(preg_replace("/\s(?=\s)/", '', str_replace(array("\r\n", "\r", "\n", '　'), ' ', str_replace('"', "'", strip_tags($excerpt)))));

    $excerpt = zib_str_cut($excerpt, 0, $limit, $after);
    return $excerpt;
}

/**
 * @description: 主-获取文章缩略图
 * @param {*}
 * @return {*}
 */
function zib_post_thumbnail($size = '', $class = 'fit-cover', $show_url = false, $post = null)
{
    if (!$size) {
        $size = _pz('thumb_postfirstimg_size');
    }

    if (!is_object($post)) {
        $post = get_post($post);
    }

    //查询缓存
    $cache_url = wp_cache_get($post->ID, 'post_thumbnail_url_' . $size, true);
    if (false === $cache_url) {
        $img_url = '';

        //特色图像
        $post_thumbnail_id = get_post_thumbnail_id($post->ID); //特色图像
        if ($post_thumbnail_id) {
            $image_src = zib_get_attachment_image_src($post_thumbnail_id, $size);
            $img_url   = isset($image_src[0]) ? $image_src[0] : '';
        }

        //外链特色图像
        if (!$img_url) {
            $img_url = zib_get_post_meta($post->ID, 'thumbnail_url', true);
        }

        //文章首图
        if (!$img_url && _pz('thumb_postfirstimg_s', true)) {
            $post_img_urls = zib_get_post_img_urls($post);
            $img_url       = isset($post_img_urls[0]) ? $post_img_urls[0] : '';

            if ($img_url && ($size && 'full' !== $size)) {
                $img_id = zib_get_image_id($img_url);
                if ($img_id) {
                    $img = wp_get_attachment_image_src($img_id, $size);
                    if (isset($img[0])) {
                        $img_url = $img[0];
                    }
                }
            }
        }

        //分类图
        if (!$img_url && _pz('thumb_catimg_s', true)) {
            $category = get_the_category();
            foreach ($category as $cat) {
                $img_url = zib_get_taxonomy_img_url($cat->cat_ID, $size);
                if ($img_url) {
                    break;
                }
            }
        }

        wp_cache_set($post->ID, $img_url, 'post_thumbnail_url_' . $size);
    } else {
        $img_url = 'no' == $cache_url ? '' : $cache_url;
    }

    //输出链接
    if ($show_url) {
        return $img_url;
    }

    $lazy_thumb = zib_get_lazy_thumb();
    $r_attr     = '';
    $alt        = $post->post_title . zib_get_delimiter_blog_name();
    if (!$img_url) {
        $img_url = zib_get_spare_thumb();
        $r_attr  = ' data-thumb="default"';
    }

    if (zib_is_lazy('lazy_posts_thumb')) {
        return sprintf('<img' . $r_attr . ' src="%s" data-src="%s" alt="%s" class="lazyload ' . $class . '">', $lazy_thumb, $img_url, $alt);
    } else {
        return sprintf('<img' . $r_attr . ' src="%s" alt="%s" class="' . $class . '">', $img_url, $alt);
    }
}

function zib_get_attachment_image_src($img_id, $size = false)
{
    $url = '';
    if (!$size || 'full' == $size) {
        $file = get_post_meta($img_id, '_wp_attached_file', true);
        if ($file) {
            // Get upload directory.
            $uploads = wp_get_upload_dir();
            if ($uploads && false === $uploads['error']) {
                // Check that the upload base exists in the file location.
                if (0 === strpos($file, $uploads['basedir'])) {
                    // Replace file location with url location.
                    $url = str_replace($uploads['basedir'], $uploads['baseurl'], $file);
                } elseif (false !== strpos($file, 'wp-content/uploads')) {
                    // Get the directory name relative to the basedir (back compat for pre-2.7 uploads).
                    $url = trailingslashit($uploads['baseurl'] . '/' . _wp_get_attachment_relative_path($file)) . wp_basename($file);
                } else {
                    // It's a newly-uploaded file, therefore $file is relative to the basedir.
                    $url = $uploads['baseurl'] . "/$file";
                }
            }
        }
        if (empty($url)) {
            $url = get_the_guid($img_id);
        }
        return array($url, 0, 0);
    } else {
        return wp_get_attachment_image_src($img_id, $size);
    }
}

//列表多图模式获取文章图片
function zib_posts_multi_thumbnail($post)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    $cache_html = wp_cache_get($post->ID, 'post_multi_thumbnail', true);
    if (false === $cache_html) {
        $class = 'fit-cover radius8';
        $html  = zib_get_post_imgs($post, _pz('thumb_postfirstimg_size'), $class, 4, true);
        if (zib_is_lazy('lazy_posts_thumb')) {
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
 * @description: 获取文章内的图片
 * @param {*}
 * @return {*}
 */
function zib_get_post_imgs($post = null, $size = '', $class = '', $count = 4, $show_badge = false)
{
    $images = zib_get_post_img_urls($post);
    $html   = '';
    $i      = 0;
    $alt    = $post->post_title . zib_get_delimiter_blog_name();

    foreach ($images as $src) {

        if ($size && 'full' != $size) {
            $img_id = zib_get_image_id($src);
            if ($img_id) {
                $img = wp_get_attachment_image_src($img_id, $size);
                if (isset($img[0])) {
                    $src = $img[0];
                }
            }
        }

        if ($count && $i == $count - 1) {
            $badge = '';
            if ($show_badge) {
                $count_surplus = count($images) - $count; //剩余数量
                $badge         = $count_surplus ? '<div class="abs-center right-top"><badge class="b-black mr6 mt6 em09"><i class="fa fa-image mr3" aria-hidden="true"></i>+' . $count_surplus . '</badge></div>' : '';
            }
            $html .= '<span>' . $badge . '<img src="' . $src . '" class="' . $class . '" alt="' . esc_attr(strip_tags($alt)) . '"></span>';
            break;
        } else {
            $html .= '<span><img src="' . $src . '" class="' . $class . '" alt="' . esc_attr(strip_tags($alt)) . '"></span>';
        }

        $i++;
    }

    return $html;
}

//获取文章中的图片数量
function zib_get_post_imgs_count($post)
{
    return count((array) zib_get_post_img_urls($post));
}

//从文章中获取图片数组
function zib_get_post_img_urls($post = null)
{
    if (!is_object($post)) {
        $post = get_post($post);
    }

    //静态变量
    static $post_content_img = array();

    if (!isset($post_content_img[$post->ID])) {
        $content = strip_shortcodes($post->post_content); //删除短代码

        preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
        $images                      = $strResult[1] ? $strResult[1] : array();
        $post_content_img[$post->ID] = $images;
    } else {
        $images = $post_content_img[$post->ID];
    }

    return $images;
}

function zib_get_delimiter_blog_name()
{
    static $_this = null;
    if ($_this) {
        return $_this;
    }
    $_this = _get_delimiter() . get_bloginfo('name');
    return $_this;
}

/**
 * @description: 通过图片链接获取图片ID
 * @param {*}
 * @return {*}
 */
function zib_get_image_id($img_url)
{
    $cache_key = md5($img_url);
    $post_id   = wp_cache_get($cache_key, 'wp_attachment_id', true);

    if ($post_id === false) {
        $attr    = wp_upload_dir();
        $baseurl = preg_replace("/^(https:\/\/|http:\/\/)/", '', $attr['baseurl']) . '/';
        $post_id = '';

        if (stristr($img_url, $baseurl)) {
            $sql_1       = '//' . preg_replace("/^(https:\/\/|http:\/\/)/", '', $img_url);
            $pattern     = "/(.*\/)(.*)(-\d+x\d+\.)(.*)/";
            $replacement = '$1$2.$4';
            $sql_1       = preg_replace($pattern, $replacement, $sql_1);
            $sql_1       = preg_replace("/\?.+/", $replacement, $sql_1);

            //最后一个字符如果是.，则去掉
            if (substr($sql_1, -1) === '.') {
                $sql_1 = substr($sql_1, 0, -1);
            }

            if ($sql_1) {
                global $wpdb;

                $db = zibDB::table($wpdb->posts)->field('id');
                $db->where('post_type', 'attachment');
                $db->where('guid', ['https:' . $sql_1, 'http:' . $sql_1]);
                $db->limit(2);
                $db_results = $db->select()->result();

                //SELECT * FROM aaa_posts  WHERE (post_type = 'attachment' AND guid IN ('https://aaa.zibll.com/wp-content/uploads/2025/07/image-22.png','http://aaa.zibll.com/wp-content/uploads/2025/07/image-22.png')) LIMIT 0, 2

                if ($db_results) {
                    if (count($db_results) >= 2) {
                        $post_id = '';
                    } else {
                        $post_id = !empty($db_results[0]->id) ? $db_results[0]->id : '';
                    }
                }
            }
        }

        //添加缓存，时间永久
        wp_cache_set($cache_key, $post_id, 'wp_attachment_id');
    }

    return $post_id;
}

//图片灯箱
if (_pz('imagelightbox')) {
    add_filter('the_content', 'imgbox_replace');
    function imgbox_replace($content)
    {
        $pattern     = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|ico|tiff|svg)(\?.*?|)('|\")(.*?)>(.*?)<\/a>/i";
        $replacement = '<a$1href="javascript:;" box-img=$2$3.$4$5$6 data-imgbox="imgbox"$7>$8</a>';
        $content     = preg_replace($pattern, $replacement, $content);
        return $content;
    }
}

//文章图片异步加载
if (zib_is_lazy('lazy_posts_content')) {
    add_filter('the_content', 'lazy_img_replace');
    function lazy_img_replace($content)
    {
        $pattern = "/<img(.*?)src=('|\")([^>]*).(bmp|gif|jpeg|jpg|png|ico|tiff|svg|webp)('|\")(.*?)>/i";

        $replacement = '<img$1src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg' . '" data-src=$2$3.$4$5 $6>';
        $content     = preg_replace($pattern, $replacement, $content);
        $pattern     = "/<img(.*?)srcset=('|\")([^>]*)('|\")(.*?)>/i";
        $replacement = '<img$1data-srcset=$2$3$4 $5>';
        $content     = preg_replace($pattern, $replacement, $content);
        return $content;
    }
}

//昵称是否有保留字符
function is_disable_username($name)
{
    $disable_reg_keywords = _pz('user_nickname_out');
    $disable_reg_keywords = preg_split("/,|，|\s|\n/", $disable_reg_keywords);

    if (!$disable_reg_keywords || !$name) {
        return false;
    }
    foreach ($disable_reg_keywords as $keyword) {
        if ($keyword && (stristr($name, $keyword) || $keyword == $name)) {
            return true;
        }
    }
    return false;
}

/**
 * 邮箱是否有保留字符
 * @param {*}
 * @return {*}
 */
function is_disable_email($email)
{
    $disable_reg_keywords = _pz('user_email_out');
    $disable_reg_keywords = preg_split("/,|，|\s|\n/", $disable_reg_keywords);

    if (!$disable_reg_keywords || !$email) {
        return false;
    }

    $user_email_limit_type = _pz('user_email_limit_type', 'not');

    foreach ($disable_reg_keywords as $keyword) {
        if ($keyword) {
            if ($user_email_limit_type === 'allow') {
                //白名单模式
                if (stristr($email, $keyword) || $keyword == $email) {
                    return false;
                }
            } else {
                //黑名单模式
                if (stristr($email, $keyword) || $keyword == $email) {
                    return true;
                }
            }
        }
    }

    //循环结束，没有任何匹配
    if ($user_email_limit_type === 'allow') {
        //白名单模式
        return true;
    } else {
        return false;
    }
}

//外链重定向添加路由
function zib_add_gophp_query_vars($public_query_vars)
{
    if (!is_admin()) {
        $public_query_vars[] = 'golink'; // 添加参数白名单
    }
    return $public_query_vars;
}
add_filter('query_vars', 'zib_add_gophp_query_vars');

function zib_gophp_template()
{
    $golink = get_query_var('golink');
    if ($golink) {
        global $wp_query;
        $wp_query->is_home = false;
        $wp_query->is_page = true; //将该模板改为页面属性，而非首页
        $template          = get_theme_file_path('/go.php');

        @session_start();
        $_SESSION['GOLINK'] = $golink;
        load_template($template);
        exit;
    }
}
add_action('template_redirect', 'zib_gophp_template', 5);

function zib_get_gourl($url)
{
    $url   = base64_encode($url);
    $nonce = '';
    if (_pz('go_link_nonce_s')) {
        $nonce = '&nonce=' . wp_create_nonce('go_link_nonce');
    }

    return esc_url(home_url('?golink=' . $url . $nonce));
}

//评论者链接重定向
add_filter('get_comment_author_link', 'add_redirect_comment_link', 5);
add_filter('comment_text', 'add_redirect_comment_link', 99);
function add_redirect_comment_link($text = '')
{
    return go_link($text);
}

function go_link($text = '', $link = false)
{
    if (!$text || !_pz('go_link_s')) {
        return $text;
    }
    if ($link) {
        if (zib_is_go_link($text)) {
            $text = zib_get_gourl($text);
        }
        return $text;
    }
    preg_match_all("/<a(.*?)href='(.*?)'(.*?)>/", $text, $matches);
    if ($matches) {
        foreach ($matches[2] as $val) {
            if (zib_is_go_link($val)) {
                $text = str_replace("href=\"$val\"", 'href="' . zib_get_gourl($val) . '" ', $text);
            }
        }
        foreach ($matches[1] as $val) {
            $text = str_replace('<a' . $val, '<a' . $val . ' target="_blank" ', $text);
        }
    }
    return $text;
}

if (_pz('go_link_s') && _pz('go_link_post')) {
    add_filter('the_content', 'the_content_nofollow', 999);
    function the_content_nofollow($content)
    {
        $pattern = '/<a(.*?)href="(.*?)"(.*?)>/';
        preg_match_all($pattern, $content, $matches);
        if ($matches) {
            foreach ($matches[2] as $val) {
                if (zib_is_go_link($val)) {
                    $content = str_replace("href=\"$val\"", 'href="' . zib_get_gourl($val) . '" ', $content);
                }
            }
        }
        return $content;
    }
}

if (_pz('post_img_auto_alt', true)) {
    add_filter('the_content', 'the_content_img_auto_alt', 999);
    function the_content_img_auto_alt($content)
    {
        global $post;
        $alt = zib_title(false);
        //清除为空的alt
        $content = str_replace('alt=""', '', $content);
        preg_match_all('/<img (.*?)\/>/', $content, $images);

        if (!is_null($images)) {
            foreach ($images[1] as $index => $value) {
                preg_match('/alt="(.*?)"/', $value, $is_alt);

                if (empty($is_alt[1])) {
                    //判断没有alt则添加
                    $new_img = str_replace('<img', '<img alt="图片[' . ($index + 1) . ']' . _get_delimiter() . esc_attr(strip_tags($alt)) . '"', $images[0][$index]);
                    $content = str_replace($images[0][$index], $new_img, $content);
                }
            }
        }
        return $content;
    }
}

//外链重定向判断
function zib_is_go_link($url)
{

    if (strpos($url, '://') == false) {
        return false;
    }

    if (strpos($url, zib_get_url_top_host())) {
        return false;
    }

    $go_link_exclude_domain = _pz('go_link_exclude_domain');
    if ($go_link_exclude_domain) {
        $exclude_domain = preg_split("/,|，|\s|\n/", $go_link_exclude_domain);
        if (in_array(zib_get_url_top_host($url), $exclude_domain)) {
            return false;
        }

    }

    return true;
}

//获取顶级域名
function zib_get_url_top_host($url = '')
{
    $url   = $url ? $url : home_url();
    $url   = strtolower($url); //首先转成小写
    $hosts = parse_url($url);
    $host  = !empty($hosts['host']) ? $hosts['host'] : $url;
    //查看是几级域名
    $data = explode('.', $host);
    $n    = count($data);
    //判断是否是双后缀
    $preg = '/[\w].+\.(com|net|org|gov|edu)\.cn$/';
    if (($n > 2) && preg_match($preg, $host)) {
        //双后缀取后3位
        $host = $data[$n - 3] . '.' . $data[$n - 2] . '.' . $data[$n - 1];
    } elseif (($n > 1)) {
        //非双后缀取后两位
        $host = $data[$n - 2] . '.' . $data[$n - 1];
    }
    return $host;
}

// 给分类连接添加SEO
function _get_tax_meta($id = 0, $field = '')
{
    $ops = zib_get_term_meta($id, 'term_seo', true);

    if (empty($ops)) {
        return '';
    }

    if (empty($field)) {
        return $ops;
    }

    return isset($ops[$field]) ? $ops[$field] : '';
}

//内容删除空格
function trimall($str)
{
    $limit = array(' ', '　', "\t", "\n", "\r");
    $rep   = array('', '', '', '', '');
    return str_replace($limit, $rep, $str);
}

// 打赏按钮
function zib_get_rewards_button($user_ID, $class = 'ml6 but c-blue', $before = '', $after = '')
{
    if (!$user_ID || !_pz('post_rewards_s')) {
        return;
    }

    $text   = _pz('post_rewards_text', '赞赏');
    $text   = '<text>' . $text . '</text>';
    $before = $before ? $before : zib_get_svg('money');

    $rewards_img_urls = zib_get_user_rewards_img_urls($user_ID);

    $weixin = $rewards_img_urls['weixin'];
    $alipay = $rewards_img_urls['alipay'];

    if (!$weixin && !$alipay) {
        return;
    }

    $user_ids[]                  = $user_ID;
    $GLOBALS['rewards_user_ids'] = $user_ids;
    $remote                      = add_query_arg(['id' => $user_ID, 'action' => 'user_rewards_modal'], admin_url('admin-ajax.php'));
    return '<a href="javascript:;" data-toggle="modal" data-target="#rewards-modal-' . $user_ID . '" data-remote="' . $remote . '" class="rewards ' . $class . '">' . $before . $text . $after . '</a>';
}

function zib_get_user_rewards_img_urls($user_id, $size = 'full')
{

    $cache = wp_cache_get($user_id, 'user_rewards_img_urls', true);
    if ($cache && is_array($cache)) {
        return $cache;
    }

    $weixin = zib_get_user_meta($user_id, 'rewards_wechat_image_id', true);
    $alipay = zib_get_user_meta($user_id, 'rewards_alipay_image_id', true);

    $weixin = $weixin ? zib_get_attachment_image_src($weixin, $size) : '';
    $alipay = $alipay ? zib_get_attachment_image_src($alipay, $size) : '';

    $urls = array(
        'weixin' => isset($weixin[0]) ? $weixin[0] : '',
        'alipay' => isset($alipay[0]) ? $alipay[0] : '',
    );

    wp_cache_set($user_id, $urls, 'user_rewards_img_urls');

    return $urls;
}

// 写文章、投稿按钮
function zib_get_write_posts_button($class = 'but b-theme', $text = '写文章', $before = '', $after = '')
{
    if (!_pz('post_article_s', true) || is_page_template('pages/newposts.php')) {
        return;
    }

    $class .= ' start-new-posts';
    $href = zib_get_new_post_url();
    return '<a rel="nofollow" target="_blank" href="' . $href . '" class="' . $class . '">' . $before . $text . $after . '</a>';
}

//获取评论点赞按钮
function zib_get_comment_like($class = '', $pid = '', $text = '', $count = false, $before = '', $after = '')
{
    if (!_pz('comment_like_s') || !$pid) {
        return;
    }

    $like = get_comment_meta($pid, 'comment_like', true);
    if (false === $like) {
        update_comment_meta($pid, 'comment_like', '0');
    }
    $like   = _cut_count($like);
    $svg    = zib_get_svg('like', null, 'icon mr3');
    $before = $before ? $before : $svg;
    if (zib_is_my_com_like($pid)) {
        $class .= ' actived';
    }

    if ($count) {
        return $like;
    }
    return '<a href="javascript:;" data-action="comment_like" class="' . $class . '" data-pid="' . $pid . '">' . $before . '<text>' . $text . '</text><count>' . ($like ? $like : 0) . '</count></a>';
}

//前台管理员编辑按钮
function zib_get_term_admin_edit($title = '编辑', $term = null, $class = 'admin-edit', $before = '', $after = '')
{
    if (!is_super_admin()) {
        return;
    }

    $bef  = $before ? $before : '<span class="' . $class . '" data-toggle="tooltip" title="' . $title . '">';
    $aft  = $after ? $after : '</span>';
    $name = '[编辑]';
    $link = edit_term_link($name, $bef, $aft, $term, false);
    return $link;
}

//获取文章点赞按钮
function zib_get_post_like($class = '', $pid = '', $text = '点赞', $count = false, $before = '', $after = '')
{
    if (!_pz('post_like_s')) {
        return;
    }

    $pid    = $pid ? $pid : get_the_ID();
    $like   = _cut_count(get_post_meta($pid, 'like', true));
    $svg    = zib_get_svg('like');
    $before = $before ? $before : $svg;
    if (zib_is_my_like($pid)) {
        $class .= ' actived';
    }
    if ($count) {
        return $like;
    }
    return '<a href="javascript:;" data-action="like" class="' . $class . '" data-pid="' . $pid . '">' . $before . '<text>' . $text . '</text><count>' . ($like ? $like : 0) . '</count></a>';
}

//获取用户关注按钮
function zib_get_user_follow($class = '', $follow_id = '', $text = '<i class="fa fa-heart-o mr3" aria-hidden="true"></i>关注', $ok_text = '<i class="fa fa-heart mr3" aria-hidden="true"></i>已关注', $before = '', $after = '')
{

    if (!$follow_id || get_current_user_id() == $follow_id || zib_is_close_sign()) {
        return;
    }

    if (zib_is_my_follow($follow_id)) {
        $class .= ' actived';
        $text = $ok_text;
    }

    $before = $before;
    $action = ' data-action="follow_user"';

    if (!is_user_logged_in()) {
        $action = '';
        $class .= ' signin-loader';
    }
    return '<a href="javascript:;"' . $action . ' class="' . $class . '" data-pid="' . $follow_id . '">' . $before . '<count>' . $text . '</count></a>';
}

//判断文章模式
function zib_is_docs_mode($pid = '', $cat_id = '')
{

    $d_cats         = array();
    $docs_mode_cats = _pz('docs_mode_cats');

    if ($docs_mode_cats) {
        $d_cats = $docs_mode_cats;
    }
    if (!$d_cats) {
        return false;
    }

    /**分类页检测 */
    if (is_category() && !$cat_id) {
        $cat_id = get_queried_object_id();
    }
    if ($cat_id && in_array($cat_id, $d_cats)) {
        return $cat_id;
    }

    /**文章页检测 */
    if (is_single() && !$pid) {
        $pid = get_queried_object_id();
    }
    foreach ($d_cats as $c_id) {
        $posts = get_posts(array(
            'category'    => $c_id,
            'numberposts' => -1,
        ));
        foreach ($posts as $post) {
            if ($post->ID == $pid) {
                return $c_id;
            }

        }
    }
    return false;
}

//获取文章收藏
function zib_get_post_favorite($class = '', $pid = '', $text = '收藏', $count = false, $icon = '')
{

    if (zib_is_close_sign()) {
        return;
    }

    $pid            = $pid ? $pid : get_the_ID();
    $favorite_count = get_post_meta($pid, 'favorite', true);
    if ($count) {
        return $favorite_count;
    }

    $count = $favorite_count ? _cut_count($favorite_count) : '';
    if (zib_is_my_favorite($pid)) {
        $class .= ' actived';
    }
    $icon = $icon ? $icon : zib_get_svg('favorite');

    $action = ' data-action="favorite"';
    if (!is_user_logged_in()) {
        $action = '';
        $class .= ' signin-loader';
    }
    return '<a href="javascript:;"' . $action . ' class="' . $class . '" data-pid="' . $pid . '">' . $icon . '<text>' . $text . '</text><count>' . $count . '</count></a>';
}

//判断是否关注
function zib_is_my_follow($pid = '')
{
    $current_id = get_current_user_id();
    if (!$current_id || !$pid) {
        return false;
    }

    $value = zib_get_user_meta($current_id, 'follow-user', true);
    $value = $value ? maybe_unserialize($value) : array();
    return in_array($pid, $value) ? true : false;
}

//判断是否品评论点赞
function zib_is_my_com_like($pid = '')
{
    $current_id = get_current_user_id();
    if (!$current_id) {
        return false;
    }

    $pid   = $pid ? $pid : get_the_ID();
    $value = zib_get_user_meta($current_id, 'like-comment', true);
    $value = $value ? maybe_unserialize($value) : array();
    return in_array($pid, $value) ? true : false;
}
//判断是否文章点赞
function zib_is_my_like($pid = '')
{
    $current_id = get_current_user_id();
    if (!$current_id) {
        return false;
    }

    $pid   = $pid ? $pid : get_the_ID();
    $value = zib_get_user_meta($current_id, 'like-posts', true);
    $value = $value ? maybe_unserialize($value) : array();
    return in_array($pid, $value) ? true : false;
}
//判断是否收藏文章
function zib_is_my_favorite($pid = '')
{
    $current_id = get_current_user_id();
    if (!$current_id) {
        return false;
    }

    $pid   = $pid ? $pid : get_the_ID();
    $value = zib_get_user_meta($current_id, 'favorite-posts', true);
    return $value && is_array($value) && in_array($pid, $value) ? true : false;
}

/**
 * @description: 获取用户的具有特殊meta_query查询的文章总数量
 * @param {*} $user_id
 * @param {*} $type topping|is_hot|essence|question_status
 * @return {*}
 */
function zib_get_user_meta_query_post_count($user_id, $type = 'topping', $post_type = 'forum_post')
{
    if (!$user_id) {
        return;
    }

    $cache = wp_cache_get($user_id, 'user_' . $type . '_' . $post_type . '_count', true);
    if (false !== $cache) {
        return $cache;
    }

    $query_args = array(
        'post_type'           => $post_type,
        'post_status'         => ['publish'],
        'showposts'           => 1,
        'ignore_sticky_posts' => true,
        'author'              => $user_id,
        'fields'              => 'ids',
    );

    switch ($type) {
        case 'topping': //置顶
            $query_args['meta_query'][] = array(
                'key'     => 'topping',
                'value'   => array('1', '2', '3'),
                'compare' => 'IN',
            );
            break;

        case 'is_hot': //热门
        case 'vote': //投票
        case 'essence': //精华
        case 'question_status': //提问已解答
            $query_args['meta_query'][] = array(
                'key'   => $type,
                'value' => 1,
            );
            break;

        default:
            return 0;
    }

    $query = new WP_Query($query_args);
    $count = isset($query->found_posts) ? $query->found_posts : 0;

    //设置缓存
    wp_cache_set($user_id, $count, 'user_' . $type . '_' . $post_type . '_count');

    return $count;
}

//刷新缓存
function zib_user_meta_query_post_count_cache_delete($meta_id, $post_id, $meta_key, $_meta_value)
{
    if (in_array($meta_key, array('topping', 'is_hot', 'vote', 'essence', 'question_status'))) {
        $post = get_post($post_id);
        if (isset($post->post_author)) {
            wp_cache_delete($post->post_author, 'user_' . $meta_key . '_' . $post->post_type . '_count');
        }
    }
}
add_action('updated_post_meta', 'zib_user_meta_query_post_count_cache_delete', 99, 4);
add_action('added_post_meta', 'zib_user_meta_query_post_count_cache_delete', 99, 4);

/**
 * @description: 获取用户文章的mate求和
 * @param {*} $user_id
 * @param {*} $mata
 * @return {*}
 */
function get_user_posts_meta_sum($user_id, $mata)
{
    $user_id = (int) $user_id;
    if (!$user_id) {
        return 0;
    }

    global $wpdb;

    $cache_num = wp_cache_get($user_id, 'user_posts_' . $mata . '_sum', true);
    if (false === $cache_num) {
        $db = zibDB::table($wpdb->posts)->alias('_p');
        $db->field('sum(meta_value)');
        $db->join([$wpdb->postmeta, '_m'], '_p.ID = _m.post_id');
        $db->where('_p.post_author', $user_id);
        $db->where('_m.meta_key', $mata);
        $db->where('_p.post_status', 'publish');
        $num = $db->sum('_m.meta_value');
        wp_cache_set($user_id, $num, 'user_posts_' . $mata . '_sum', 43200);
    } else {
        $num = $cache_num;
    }
    return $num ? $num : 0;
}

/**
 * @description: 获取用户评论的mate求和
 * @param {*} $user_id
 * @param {*} $mata
 * @return {*}
 */
function get_user_comment_meta_sum($user_id, $mata)
{

    $user_id = (int) $user_id;
    if (!$user_id) {
        return 0;
    }

    global $wpdb;
    $cache_num = wp_cache_get($user_id, 'user_comment_' . $mata . '_sum', true);

    if (false === $cache_num) {

        $db = zibDB::table($wpdb->comments)->alias('_c');
        $db->field('sum(meta_value)');
        $db->join([$wpdb->commentmeta, '_m'], '_c.comment_ID = _m.comment_id');
        $db->where('_c.user_id', $user_id);
        $db->where('_m.meta_key', $mata);
        $db->where('_c.comment_approved', '1');
        $num = $db->sum('_m.meta_value');

        wp_cache_set($user_id, $num, 'user_comment_' . $mata . '_sum', 43200);
    } else {
        $num = $cache_num;
    }
    return $num ? $num : 0;
}

//作者总获赞
function get_user_posts_meta_count($user_id, $mata)
{
    return _cut_count(get_user_posts_meta_sum($user_id, $mata));
}

//作者总粉丝数量
function get_user_followed_count($user_id)
{
    return zib_get_user_follow_count($user_id, 'followed');
}

//作者评论数
function get_user_comment_count($user_id, $comments_status = 'approve', $cut = true)
{
    if (!$user_id) {
        return;
    }

    $cache_num = wp_cache_get($user_id, 'user_comment_' . $comments_status . '_count', true);
    if (false === $cache_num) {
        $args = array(
            'user_id'   => $user_id,
            'status'    => $comments_status,
            'count'     => true,
            'post_type' => ['post'],
        );
        if (_pz('bbs_s')) {
            $args['post_type'][] = 'forum_post';
        }

        $count = get_comments($args);
        wp_cache_set($user_id, $count, 'user_comment_' . $comments_status . '_count');
    } else {
        $count = $cache_num;
    }
    return $cut ? _cut_count($count) : (int) $count;
}

//刷新缓存
function zib_comment_del_cache_status($new_status, $old_status, $comment)
{
    $user_id = $comment->user_id;
    if ($user_id) {
        wp_cache_delete($user_id, 'user_comment_approve_count');
        wp_cache_delete($user_id, 'user_comment_all_count');
    }
}
add_action('transition_comment_status', 'zib_comment_del_cache_status', 10, 3);

function zib_comment_del_cache($comment_ID)
{
    $comment = get_comment($comment_ID);
    $user_id = $comment->user_id;
    if ($user_id) {
        wp_cache_delete($user_id, 'user_comment_approve_count');
        wp_cache_delete($user_id, 'user_comment_all_count');
    }
}
add_action('comment_post', 'zib_comment_del_cache', 10);

function zib_comment_del_cache_trashed_comment($comment_ID, $comment)
{
    $user_id = $comment->user_id;
    if ($user_id) {
        wp_cache_delete($user_id, 'user_comment_approve_count');
        wp_cache_delete($user_id, 'user_comment_all_count');
    }
}
add_action('trashed_comment', 'zib_comment_del_cache_trashed_comment', 10, 2);

//作者签名
function get_user_desc($user_id, $yiyan = true)
{

    if ($yiyan && _pz('yiyan_avatar_desc')) {
        return '<span class="yiyan" type="' . (_pz('yiyan_avatar_desc_type', 'cn')) . '"></span>';
    }

    $des = get_user_meta($user_id, 'description', true);
    if (!$des) {
        $des = _pz('user_desc_std', '这家伙很懒，什么都没有写...');
    }
    return esc_attr($des);
}

// 获取分类封面图片
function zib_get_taxonomy_img_url($term_id = null, $size = 'full', $default = false)
{
    //return '';
    if (!$term_id) {
        $term_id = get_queried_object_id();
    }
    $img   = '';
    $cache = wp_cache_get($term_id, 'taxonomy_image_' . $size, true);
    if (false === $cache) {
        $img = zib_get_term_meta($term_id, 'cover_image', true);
        if ($img) {
            if ($size && 'full' != $size) {
                $img_id = zib_get_image_id($img);
                if ($img_id) {
                    $img = wp_get_attachment_image_src($img_id, $size);
                    $img = !empty($img[0]) ? $img[0] : '';
                }
            }
            //缓存数据
            if ($img) {
                wp_cache_set($term_id, $img, 'taxonomy_image_' . $size);
            } else {
                //缓存1天
                wp_cache_set($term_id, 'noimg', 'taxonomy_image_' . $size);
            }
        }
    } elseif ('noimg' == $cache) {
        $img = '';
    } else {
        $img = $cache;
    }
    if ($default) {
        return ('' != $img) ? $img : $default;
    } else {
        return $img;
    }

}

/**
 * @description: 判断是否关注/是否收藏等
 * @param {*} $meta meta_key 的类型 : follow_plate || favorite_forum_posts || favorite_product
 * @param {*} $pid 帖子或者版块或者分类的id
 * @return {*} bool
 */
function zib_is_my_meta_ed($meta, $pid, $current_id = 0)
{
    $current_id = $current_id ? $current_id : get_current_user_id();
    if (!$current_id || !$pid) {
        return false;
    }

    $value = zib_get_user_meta($current_id, $meta, true);
    return ($value && in_array($pid, (array) $value));
}

//作者封面图
function get_user_cover_img($user_id)
{
    $url = get_user_cover_img_url($user_id);

    $cover_lazy_attr = zib_get_lazy_attr('lazy_cover', $url, 'fit-cover user-cover user-cover-id-' . $user_id, ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-lg.svg');
    $img             = '<img ' . $cover_lazy_attr . ' alt="用户封面">';
    return $img;
}

function get_user_cover_img_url($user_id)
{
    $img         = $user_id ? zib_get_user_meta($user_id, 'cover_image', true) : '';
    $default_img = _pz('user_cover_img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg');
    return $img ? $img : $default_img;
}

add_action('init', 'custom_button');
function custom_button()
{
    add_filter('mce_external_plugins', 'add_plugin');
    add_filter('mce_buttons', 'zib_register_button');
    add_filter('mce_buttons_2', 'zib_register_button_2');
}

//前端编辑器第一行按钮
function zib_register_button($buttons)
{
    $is_mobile = wp_is_mobile();
    $is_admin  = is_admin();
    $buttons   = ['zib_h2', 'zib_h3', 'bold', 'bullist', 'numlist', 'alignleft', 'aligncenter', 'alignright', 'link', 'spellchecker'];
    if ($is_mobile) {
        $buttons = ['zib_h2', 'bold', 'bullist', 'link', 'spellchecker'];
    }

    if (!$is_admin) {
        //不是在后台
        $buttons[] = 'zib_img';
        $buttons[] = 'zib_video';

        if ((apply_filters('tinymce_upload_file', false))) {
            $buttons[] = 'zib_file';
        }
    }

    if ((apply_filters('tinymce_hide', false) || $is_admin)) {
        $buttons[] = 'zib_hide';
    }

    if (!$is_mobile) {
        $buttons[] = 'zib_quote';
    }
    $buttons[] = 'precode';
    $buttons[] = 'fullscreen';
    $buttons[] = 'wp_adv';

    return $buttons;
}

function zib_register_button_2($buttons)
{
    $is_mobile = wp_is_mobile();

    $buttons = ['styleselect', 'fontsizeselect', 'forecolor'];

    if ($is_mobile) {
        $buttons[] = 'zib_quote';
    }

    $buttons[] = 'removeformat';
    $buttons[] = 'undo';
    $buttons[] = 'redo';
    if (!$is_mobile) {
        $buttons[] = 'wp_help';
    }
    return $buttons;
}

//添加按钮动作
function add_plugin($plugin_array)
{
    $plugin_array['precode']   = ZIB_TEMPLATE_DIRECTORY_URI . '/js/precode.js?ver=' . THEME_VERSION . '';
    $plugin_array['zib_video'] = ZIB_TEMPLATE_DIRECTORY_URI . '/js/editextend.min.js?ver=' . THEME_VERSION . '';
    $plugin_array['zib_img']   = ZIB_TEMPLATE_DIRECTORY_URI . '/js/editextend.min.js?ver=' . THEME_VERSION . '';
    $plugin_array['zib_hide']  = ZIB_TEMPLATE_DIRECTORY_URI . '/js/editextend.min.js?ver=' . THEME_VERSION . '';
    $plugin_array['zib_quote'] = ZIB_TEMPLATE_DIRECTORY_URI . '/js/editextend.min.js?ver=' . THEME_VERSION . '';
    $plugin_array['zib_file']  = ZIB_TEMPLATE_DIRECTORY_URI . '/js/editextend.min.js?ver=' . THEME_VERSION . '';
    return $plugin_array;
}

add_filter('mce_css', function ($mce_css) {
    $mce_css .= $mce_css ? ',' : '';
    $mce_css .= ZIB_TEMPLATE_DIRECTORY_URI . '/css/tinymce.min.css?ver=' . THEME_VERSION . ',' . ZIB_TEMPLATE_DIRECTORY_URI . '/css/font-awesome.min.css?ver=' . THEME_VERSION . '';
    return $mce_css;
});

//为编辑器添加全局变量
add_action('wp_enqueue_editor', function () {

    $wp_is_mobile      = wp_is_mobile();
    $_pz_float_toolbar = (array) _pz('mce_float_toolbar', array('pc_s', 'm_s')) ?: array('');
    $float_toolbar     = (($wp_is_mobile && in_array('m_s', $_pz_float_toolbar)) || (!$wp_is_mobile && in_array('pc_s', $_pz_float_toolbar)));

    echo '<script type="text/javascript">var mce = {
            is_admin:"' . is_admin() . '",
            img_allow_upload:"' . apply_filters('tinymce_upload_img', false) . '",
            img_multiple_max:"' . _pz('image_upload_multiple', 6) . '",
            video_allow_upload:"' . apply_filters('tinymce_upload_video', false) . '",
            video_allow_iframe:"' . apply_filters('tinymce_iframe_video', false) . '",
            file_allow_upload:"' . apply_filters('tinymce_upload_file', false) . '",
            hide_pay:"' . (apply_filters('tinymce_hide_pay', false) || (is_super_admin() && is_admin())) . '",
            float_toolbar:"' . $float_toolbar . '",
        }</script>';
});

//为编辑器添加允许的标签(高亮代码)
function zib_allow_html_precode_attributes($data, $context)
{
    if ($context === 'post') {
        $allowedAttributes = array(
            'data-enlighter-language'    => true,
            'data-enlighter-theme'       => true,
            'data-enlighter-group'       => true,
            'data-enlighter-title'       => true,
            'data-enlighter-linenumbers' => true,
            'data-enlighter-highlight'   => true,
            'data-enlighter-lineoffset'  => true,
            'contenteditable'            => true,
        );

        if (isset($data['pre'])) {
            $data['pre'] = array_merge($data['pre'], $allowedAttributes);
        }
        if (isset($data['code'])) {
            $data['code'] = array_merge($data['code'], $allowedAttributes);
        }

        foreach (array('div', 'p', 'span') as $tag) {
            $data[$tag] = array_merge($data['code'], $allowedAttributes = array(
                'contenteditable' => true,
            ));
        }

        if (is_super_admin()) {
            $data['svg'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'aria-hidden'   => true,
                'data-icon'     => true,
                'role'          => true,
                'xmlns'         => true,
                'viewbox'       => true,
                'width'         => true,
                'height'        => true,
                'fill'          => true,
                'stroke'        => true,
                'transform'     => true,
                'stroke-width'  => true,
                'transform'     => true,
                'fill-rule'     => true,
                'target-height' => true,
                'target-width'  => true,
                'target-x'      => true,
                'target-y'      => true,
                'node-id'       => true,
            );
            $data['path'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'd'             => true,
                'fill'          => true,
                'xmlns'         => true,
                'viewbox'       => true,
                'width'         => true,
                'height'        => true,
                'fill'          => true,
                'stroke'        => true,
                'stroke-width'  => true,
                'transform'     => true,
                'fill-rule'     => true,
                'target-height' => true,
                'target-width'  => true,
                'target-x'      => true,
                'target-y'      => true,
                'node-id'       => true,
            );

            $data['g'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'd'             => true,
                'fill'          => true,
                'xmlns'         => true,
                'viewbox'       => true,
                'width'         => true,
                'height'        => true,
                'fill'          => true,
                'stroke'        => true,
                'transform'     => true,
                'stroke-width'  => true,
                'transform'     => true,
                'fill-rule'     => true,
                'target-height' => true,
                'target-width'  => true,
                'target-x'      => true,
                'target-y'      => true,
                'node-id'       => true,
            );

            $data['clipPath'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'd'             => true,
                'fill'          => true,
                'xmlns'         => true,
                'viewbox'       => true,
                'width'         => true,
                'height'        => true,
                'fill'          => true,
                'stroke'        => true,
                'transform'     => true,
                'stroke-width'  => true,
                'transform'     => true,
                'fill-rule'     => true,
                'target-height' => true,
                'target-width'  => true,
                'target-x'      => true,
                'target-y'      => true,
                'node-id'       => true,
            );
            $data['defs'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'd'             => true,
                'fill'          => true,
                'xmlns'         => true,
                'viewbox'       => true,
                'width'         => true,
                'height'        => true,
                'fill'          => true,
                'stroke'        => true,
                'transform'     => true,
                'stroke-width'  => true,
                'transform'     => true,
                'fill-rule'     => true,
                'target-height' => true,
                'target-width'  => true,
                'target-x'      => true,
                'target-y'      => true,
                'clip-path'     => true,
                'node-id'       => true,
            );
            $data['radialGradient'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'cx'            => true,
                'cy'            => true,
                'fx'            => true,
                'fy'            => true,
                'gradientUnits' => true,
                'r'             => true,
                'rev'           => true,
                'spreadMethod'  => true,
                'x1'            => true,
                'x2'            => true,
                'y1'            => true,
                'y2'            => true,
                'node-id'       => true,

            );
            $data['linearGradient'] = array(
                'style'         => true,
                'id'            => true,
                'class'         => true,
                'cx'            => true,
                'cy'            => true,
                'fx'            => true,
                'fy'            => true,
                'gradientUnits' => true,
                'r'             => true,
                'rev'           => true,
                'spreadMethod'  => true,
                'x1'            => true,
                'x2'            => true,
                'y1'            => true,
                'y2'            => true,
                'node-id'       => true,
            );
            $data['stop'] = array(
                'offset'        => true,
                'stop-color'    => true,
                'class'         => true,
                'cx'            => true,
                'cy'            => true,
                'fx'            => true,
                'fy'            => true,
                'gradientUnits' => true,
                'r'             => true,
                'rev'           => true,
                'spreadMethod'  => true,
                'node-id'       => true,
            );
            $data['use'] = array(
                'xlink:href' => true,
            );
        }
    }

    return $data;
}
add_filter('wp_kses_allowed_html', 'zib_allow_html_precode_attributes', 99, 2);

//为编辑器添加允许的标签(超级嵌入)
function zib_allow_html_iframe_attributes($data, $context)
{
    if ($context === 'post') {
        $allowedAttributes = array(
            'id'              => true,
            'src'             => true,
            'class'           => true,
            'border'          => true,
            'height'          => true,
            'width'           => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'contenteditable' => true,
            'data-*'          => true,
        );

        if (isset($data['iframe'])) {
            $data['iframe'] = array_merge($data['iframe'], $allowedAttributes);
        } else {
            $data['iframe'] = $allowedAttributes;
        }
    }

    return $data;
}

//为编辑器添加的allowfullscreen，输出时候移除
add_filter('the_content', 'zib_the_content_remove_contenteditable');
function zib_the_content_remove_contenteditable($content)
{
    $content = str_replace(' contenteditable="', ' mce-contenteditable="', $content);
    return $content;
}

//为编辑器加入body class
function zib_tiny_mce_before_init_filter($mceInit, $editor_id)
{
    if ('post_content' === $editor_id) {
        $mceInit['body_class'] .= ' ' . zib_get_theme_mode();
    }
    return $mceInit;
}
add_filter('tiny_mce_before_init', 'zib_tiny_mce_before_init_filter', 10, 2);

//编辑器的下载模块试配
function zib_the_content_download_link_replace($content)
{

    $pattern = '/<a(.*?)data-download-file="(.*?)"(.*?)>/';
    preg_match_all($pattern, $content, $matches);

    if ($matches) {
        foreach ($matches[2] as $val) {

            $nonce   = wp_create_nonce('download_file_' . $val);
            $content = str_replace(" data-download-file=\"$val\"", " download-nonce=\"$nonce\" data-download-file=\"$val\"", $content);

        }
    }

    return $content;
}
add_filter('the_content', 'zib_the_content_download_link_replace');

//添加隐藏内容，回复可见
function reply_to_read($atts, $content = null)
{
    $a = '#commentform';
    extract(shortcode_atts(array('notice' => '<a class="hidden-text" href="javascript:(scrollTopTo(\'' . $a . '\',-50));"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，请评论后刷新页面查看.</a>'), $atts));
    $_hide = '<div class="hidden-box">' . $notice . '</div>';
    $_show = '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容</div>' . do_shortcode($content) . '</div>';

    if (is_super_admin()) {
        //管理员登陆直接显示内容
        return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - 管理员可见</div>' . do_shortcode($content) . '</div>';
    } else {
        if (zib_user_is_commented()) {
            return $_show;
        } else {
            return $_hide;
        }
    }
}
add_shortcode('reply', 'reply_to_read');

//判断是否已经评论
function zib_user_is_commented($user_id = 0, $post_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id) {
        return false;
    }

    $q_args = [
        'comment_post_ID'  => $post_id,
        'comment_approved' => '1',
        'number'           => 1,
        'count'            => true,
    ];

    if ($user_id) {
        $q_args['user_id'] = $user_id;
    } elseif (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {
        $email                  = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH]);
        $q_args['author_email'] = $email;
    } else {
        return false;
    }

    $comment = get_comments($q_args);

    return $comment ? true : false;
}

/**文章短代码 */
function add_shortcode_postsbox($atts, $content = null)
{
    extract(shortcode_atts(array(
        'post_id' => '0',
    ), $atts));
    $con = '';
    if ($post_id) {
        $post = get_post($post_id);
        if (!empty($post->ID)) {
            $_thumb    = zib_post_thumbnail('', 'fit-cover radius8 no-imgbox', false, $post);
            $title     = zib_get_posts_list_pay_badge($post) . get_the_title($post) . get_the_subtitle(true, 'focus-color', $post->ID);
            $meta      = zib_get_posts_list_meta(true, true, $post);
            $permalink = get_permalink($post);

            $con = '<div class="article-postsbox relative-h radius8">
            <div class="posts-mini posts-item relative gradient-bg" data-opacity="0.1">
            <div class="mr10"><div class="item-thumbnail"><a href="' . $permalink . '">' . $_thumb . '</a></div></div>
                <div class="posts-mini-con flex xx flex1 jsb">
                    <div class="item-heading text-ellipsis-2 main-color">
                        <a class="main-color" href="' . $permalink . '">' . $title . '</a>
                    </div>
                    ' . $meta . '
                </div>
                </div>
            </div>';
        }
    }
    if (!$con && is_super_admin()) {
        $con = '<div class="hidden-box"><div class="text-center">[postsbox post_id="' . $post_id . '"]</div><div class="hidden-text">未找到文章，请重新设置短代码文章ID</div></div>';
    }
    return $con;
}
add_shortcode('postsbox', 'add_shortcode_postsbox');

/**文章短代码 */
function add_shortcode_postslists($atts, $content = null)
{
    $atts = shortcode_atts(array(
        'orderby'  => 'date',
        'cat'      => '',
        'topics'   => '',
        'style'    => 'text',
        'count'    => 12,
        'paginate' => false,
        'blank'    => false,
        'action'   => 'query_posts_lists',
    ), $atts);

    $placeholder = str_repeat('<div class="posts-mini"><div class="placeholder k1"></div></div>', 8);

    if ($atts['style'] === 'minicard') {
        $placeholder = str_repeat('<div class="posts-mini"><div class="mr10"><div class="item-thumbnail placeholder"></div></div><div class="posts-mini-con flex xx flex1 jsb"><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div>', 6);
    }

    $ias_args = array(
        'type'   => 'ias',
        'id'     => '',
        'class'  => 'posts-mini-lists mb20',
        'loader' => $placeholder, // 加载动画
        'query'  => $atts,
    );

    return zib_get_ias_ajaxpager($ias_args);
}
add_shortcode('postslists', 'add_shortcode_postslists');

add_shortcode('hidecontent', 'add_shortcode_hidecontent');
function add_shortcode_hidecontent($atts, $content = null)
{

    extract(shortcode_atts(array(
        'type'      => 'reply',
        'is_logged' => '',
        'password'  => '',
        'img_id'    => '',
        'img_url'   => '',
        'desc'      => '',
    ), $atts));

    $content = do_shortcode($content);
    if ('<span>' === substr($content, -6) && '</span>' === substr($content, 0, 7)) {
        $content = substr($content, 7, -6);
    }

    if ('<p class="hide-after">' === substr($content, -22) && '</p>' === substr($content, 0, 4)) {
        $content = substr($content, 4, -22);
    }

    $user_id   = get_current_user_id();
    $type_text = array(
        'reply'    => '评论可见',
        'payshow'  => '付费阅读',
        'logged'   => '登录可见',
        'password' => '密码验证',
        'vip1'     => _pz('pay_user_vip_1_name') . '可见',
        'vip2'     => _pz('pay_user_vip_2_name') . '可见',
    );

    global $post;
    if (is_super_admin()) {
        //管理员登陆直接显示内容
        return '<div class="hidden-box show"><div class="hidden-text">[' . $type_text[$type] . ']隐藏内容 - 管理员可见</div>' . ($content) . '</div>';
    }

    //通过挂钩直接显示
    if (apply_filters('hidecontent_is_show', false, $type)) {
        return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容</div>' . ($content) . '</div>';
    }

    if ($user_id && $user_id == $post->post_author) {
        //作者直接显示内容
        return '<div class="hidden-box show"><div class="hidden-text">[' . $type_text[$type] . ']隐藏内容 - 作者可见</div>' . ($content) . '</div>';
    }

    switch ($type) {
        case 'reply':
            $a     = '#commentform';
            $_hide = '<div class="hidden-box" reply-show="true" reload-hash="#hidden-box-comment"><a class="hidden-text" href="javascript:(scrollTopTo(\'' . $a . '\',-50));"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，请评论后刷新页面查看.</a></div>';
            $_show = '<div class="hidden-box show" id="hidden-box-comment"><div class="hidden-text">本文隐藏内容</div>' . ($content) . '</div>';

            if (zib_user_is_commented()) {
                return $_show;
            } else {
                return $_hide;
            }
            break;

        case 'payshow':
            $a        = '#posts-pay';
            $_hide    = '<div class="hidden-box"><a class="hidden-text" href="javascript:(scrollTopTo(\'' . $a . '\',-50));"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，请付费后查看</a></div>';
            $pay_mate = get_post_meta($post->ID, 'posts_zibpay', true);
            $paid     = zibpay_is_paid($post->ID);
            /**如果未设置付费阅读功能，则直接显示 */
            if (empty($pay_mate['pay_type']) || 'no' === $pay_mate['pay_type']) {
                return $content;
            }

            /**
             * 判断逻辑
             * 1. 管理登录
             * 2. 已经付费
             * 3. 必须设置了付费阅读
             */
            if ($paid) {
                $paid_name = zibpay_get_paid_type_name($paid['paid_type']);
                if ('free' == $paid['paid_type'] && _pz('pay_free_logged_show') && !$user_id) {
                    return '<div class="hidden-box"><a class="hidden-text signin-loader" href="javascript:;"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;免费资源，请登录后查看</a></div>';
                } else {
                    //已经购买，且有效

                    //获取订单到期时间
                    $order_expired_time = $paid['order_expired_time'] ?? 0;
                    $order_expired_html = '';
                    if ($paid['paid_type'] == 'paid' && $order_expired_time) {
                        $order_expired_html = '<span class="badg badg-sm c-yellow">' . $order_expired_time . '前可查看</span>';
                    }

                    return '<div class="hidden-box show"><div class="hidden-text">本文付费阅读内容 - ' . $paid_name . $order_expired_html . '</div>' . ($content) . '</div>';
                }
            } else {
                return apply_filters('hidecontent_payshow_hide_content', $_hide, $content, $post);
            }
            break;
        case 'logged':
            if ($user_id > 0) {
                return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - 登录可见</div>' . ($content) . '</div>';
            } else {
                return '<div class="hidden-box"><a class="hidden-text signin-loader" href="javascript:;"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;隐藏内容，请登录后查看</a></div>';
            }
            break;
        case 'vip1':
        case 'vip2':
            $vip_level = (int) zib_get_user_vip_level($user_id);
            if ('vip1' == $type) {
                $vip_l = 1;
            } else {
                $vip_l = 2;
            }
            if ($user_id > 0) {
                if (!$vip_level) {
                    return '<div class="hidden-box"><a class="hidden-text pay-vip" vip-level="' . $vip_l . '" href="javascript:;"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，' . $type_text['vip' . $vip_l] . '<br><i class="fa fa-diamond"></i>&nbsp;&nbsp;请开通会员后查看</a></div>';
                } elseif ($vip_level < $vip_l) {
                    return '<div class="hidden-box"><a class="hidden-text pay-vip" vip-level="' . $vip_l . '" href="javascript:;"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，' . $type_text['vip' . $vip_l] . '<br><i class="fa fa-diamond"></i>&nbsp;&nbsp;请升级会员后查看</a></div>';
                } else {
                    return '<div class="hidden-box show"><div class="hidden-text">本文隐藏内容 - ' . $type_text['vip' . $vip_l] . '</div>' . ($content) . '</div>';
                }
            } else {
                return '<div class="hidden-box"><a class="hidden-text signin-loader" href="javascript:;"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;此处内容已隐藏，' . $type_text['vip' . $vip_l] . '<br><i class="fa fa-sign-in"></i>&nbsp;&nbsp;请登录后查看特权</a></div>';
            }
            break;
        case 'password':
            $html = '';
            $pas  = !empty($_POST['hidecontent-password']) ? $_POST['hidecontent-password'] : '';

            if ($pas && $pas == $password) {
                $html = '<div class="hidden-box show"><div class="hidden-text">此处隐藏内容</div>' . ($content) . '</div>';
            } else {
                $img  = $img_url ? '<div class="flex0 hidden-pass-img mr10"><img class="no-imgbox" alt="' . zib_title(false) . '" src="' . $img_url . '"></div>' : '';
                $from = '<form class="flex" action="' . esc_url(zib_get_current_url()) . '" method="POST">';
                $from .= '<input type="text" name="hidecontent-password" class="form-control" placeholder="请输入密码">';
                $from .= '<button type="submit" class="but c-blue ml10 flex0">提交</button>';
                $from .= '</form>';

                $html .= '<div class="hidden-box flex em09-sm">';
                $html .= $img;
                $html .= '<div class="flex1 flex xx jsb padding-10 nopw-sm noph-sm">';
                $html .= '<dd class="title-theme em09-sm">隐藏内容，输入密码后查看</dd>';
                $html .= $desc ? '<div class="muted-color mt6 mb6">' . $desc . '</div>' : '';
                $html .= $pas ? '<div class="px12 c-red">密码输入错误，请重新输入</div>' : '';
                $html .= $from;
                $html .= '</div>';
                $html .= '</div>';
            }

            return $html;

            break;
    }
}

function zib_get_svg($name, $viewBox = null, $class = 'icon')
{
    $viewBox = $viewBox ? ' data-viewBox="' . $viewBox . '" viewBox="' . $viewBox . '"' : '';
    return '<svg class="' . $class . '" aria-hidden="true"' . $viewBox . '><use xlink:href="#icon-' . $name . '"></use></svg>';
}

function zib_svg($name = '', $viewBox = '0 0 1024 1024', $class = 'icon')
{
    if ($name) {
        return '<i data-class="' . $class . '" data-viewBox="' . $viewBox . '" data-svg="' . $name . '" aria-hidden="true"></i>';
    }
}

//函数调试代码-函数性能测试
function zib_microtime_float()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}
function ZFuncTime($func, $arr = null)
{
    //查询当前 timestamp 精确到 microseconds

    $time_taken = 0;
    $time_start = zib_microtime_float();

    if (null == $arr) {
        //Call a user function given by the first parameter
        call_user_func($func);
    } else {
        //Call a user function given with an array of parameters
        call_user_func_array($func, $arr);
    }

    $time_end   = zib_microtime_float();
    $time_taken = $time_taken + ($time_end - $time_start);

    $log = array(
        '数据库查询' => get_num_queries(),
        '页面加载'  => (timer_stop(0, 10) * 1000),
        '测试函数'  => $func,
        '测试参数'  => $arr,
        '执行时间'  => ($time_taken * 1000),
    );

    $html = '<script type="text/javascript">';
    $html .= 'console.log(' . json_encode($log) . ')';
    $html .= '</script>';
    echo $html;
}
