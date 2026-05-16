<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-03-14 16:13:45
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|初始化函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

// 初始化BBS，创建文章类型、论坛
class zib_bbs
{
    public $home_url                = null;
    public $posts_edit_rewrite_slug = '';
    public $plate_rewrite_slug      = '';
    public $posts_rewrite_slug      = '';
    public $rewrite_suffix          = '';
    public $forum_name              = '';
    public $posts_name              = '';
    public $comment_name            = '';
    public $plate_name              = '';
    public $topic_name              = '';
    public $tag_name                = '';
    public $plate_follow_name       = '';
    public $plate_moderator_name    = '';
    public $plate_author_name       = '';
    public $cat_moderator_name      = '';
    public $exclude_search_posts    = '';
    public $posts_show_in_rest      = '';
    public $is_super_admin          = false;
    public $s                       = false;

    public function __construct()
    {}

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been ran previously
        if (null !== $instance) {
            return $instance;
        }

        $instance = new self();
        //定义参数
        //路由别名
        $instance->posts_edit_rewrite_slug = _pz('bbs_posts_edit_rewrite_slug') ?: 'posts-edit';
        $instance->plate_rewrite_slug      = _pz('bbs_plate_rewrite_slug') ?: 'forum';
        $instance->posts_rewrite_slug      = _pz('bbs_posts_rewrite_slug') ?: 'forum-post';
        $instance->rewrite_suffix          = _pz('bbs_rewrite_suffix_html_s', true) ? '.html' : '';

        //名称定义
        $instance->forum_name           = _pz('bbs_forum_display_name', '论坛') ?: '论坛';
        $instance->posts_name           = _pz('bbs_posts_display_name', '帖子') ?: '帖子';
        $instance->comment_name         = _pz('bbs_comment_display_name', '回复') ?: '回复';
        $instance->plate_name           = _pz('bbs_plate_display_name', '版块') ?: '版块';
        $instance->topic_name           = _pz('bbs_topic_display_name', '话题') ?: '话题';
        $instance->tag_name             = _pz('bbs_tag_display_name', '标签') ?: '标签';
        $instance->plate_moderator_name = _pz('bbs_plate_moderator_name', '版主') ?: '版主';
        $instance->plate_author_name    = _pz('bbs_plate_author_name', '超级版主') ?: '超级版主';
        $instance->cat_moderator_name   = _pz('bbs_cat_moderator_name', '分区版主') ?: '分区版主';
        $instance->plate_follow_name    = _pz('bbs_plate_follow_name', '关注') ?: '关注';

        //其他参数
        $instance->exclude_search_posts = _pz('bbs_exclude_search_posts', true);
        $instance->posts_show_in_rest   = _pz('bbs_posts_show_in_rest_s', false);
        $instance->is_super_admin       = is_super_admin();
        $instance->s                    = _pz('bbs_s', true);

        if ($instance->s) {
            $instance->setup();
        }

        new zib_bbs_admin($instance);
        return $instance;
    }

    /**
     * @description: 启动函数
     * @param {*}
     * @return {*}
     */
    public function setup()
    {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_post_statuses'));
        add_action('init', array($this, 'add_rewrite_rule'));
        add_filter('query_vars', array($this, 'query_vars'));
        add_filter('user_has_cap', array($this, 'user_has_cap'), 11, 2);

        add_action('saved_term', array($this, 'initialization_term_meta'), 10, 4);
        add_action('save_post', array($this, 'initialization_posts_meta'), 10, 3);

        add_action('template_redirect', array($this, 'template_redirect'));
        add_action('pre_get_posts', array($this, 'wp_query'));
        add_filter('redirect_canonical', array($this, 'redirect_canonical'));
        add_filter('body_class', array($this, 'body_class'));
        add_filter('post_type_link', array($this, 'post_type_link'), 10, 2);
        // add_filter('comment_status_links', array($this, 'views_comments'));

        add_action('pre_get_posts', array($this, 'admin_post_query'), 999);
        add_action('pre_get_posts', array($this, 'main_post_query'));

        add_action('restrict_manage_posts', array($this, 'plate_select'));
        add_action('admin_menu', array($this, 'admin_menu_separator'));
        add_action('trashed_post', array($this, 'trashed_plate'));

        add_filter('manage_users_columns', array($this, 'users_columns'), 11);
        add_filter('manage_users_custom_column', array($this, 'users_custom_column'), 10, 3);
        add_filter('wp_untrash_post_status', array($this, 'untrash_post_status'), 10, 2);
    }

    /**
     * @description: 后台添加批量修改帖子参数的选项
     * @param {*} $column_name
     * @param {*} $post_type
     * @return {*}
     */
    public function bulk_edit_custom_box($column_name, $post_type)
    {
        if ($post_type === 'forum_post') {
            if ($column_name === 'plate_id') {

                echo '<fieldset class="inline-edit-col-left">

                <div class="inline-edit-col">
                    <label>
                        <span class="title">板块</span>
                        ' . zib_bbs_edit::posts_plate_select(0, '', 'plate_id', '—不更改—') . '
                    </label>
                </div>';
            }
        }

        if ($post_type === 'plate') {

        }
    }

    public function bulk_save_post($post_ID, $post, $update)
    {
        if (!$update || empty($_REQUEST['plate_id']) || $post->post_type !== 'forum_post' || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== 'edit-forum_post') {
            return;
        }

        $plate_id = (int) $_REQUEST['plate_id'];
        if ($plate_id) {
            update_post_meta($post_ID, 'plate_id', $plate_id);
        }

    }

    public function main_post_query($query)
    {

        //搜索页面的
        if ($query->is_search() && $query->is_main_query() && !$query->is_admin) {
            global $search_type;
            $cat  = !empty($_REQUEST['trem']) ? trim($_REQUEST['trem']) : '';
            $type = empty($search_type) ? (!empty($_REQUEST['type']) ? trim(strip_tags($_REQUEST['type'])) : '') : $search_type;

            if ('forum' === $type) {
                $query->set('post_type', 'forum_post');
            }

            if ('plate' === $type) {
                $query->set('post_type', 'plate');
            }

            if ($cat && stristr($cat, 'plate_')) {
                $plate_meta_query = array(
                    'key'   => 'plate_id',
                    'value' => str_replace('plate_', '', $cat),
                );
                $meta_query   = $query->get('meta_query');
                $meta_query   = is_array($meta_query) ? $meta_query : array();
                $meta_query[] = $plate_meta_query;

                $query->set('meta_query', $meta_query);
            }
        }
    }

    //从回收站移出，直接发布，而不是默认的草稿
    public function untrash_post_status($new_status, $post_id)
    {
        $post = get_post($post_id);
        if ('plate' == $post->post_type || 'forum_post' == $post->post_type) {
            $new_status = 'publish';
        }
        return $new_status;
    }

    //删除版块同时删除对应的帖子
    public function trashed_plate($plate_id)
    {
        $plate = get_post($plate_id);

        global $wpdb;
        if ('plate' == $plate->post_type && zib_bbs_get_plate_posts_count($plate_id)) {
            $sql = "UPDATE $wpdb->posts,$wpdb->postmeta SET $wpdb->posts.post_status = 'trash'
                    WHERE $wpdb->posts.post_type = 'forum_post' AND $wpdb->postmeta.meta_value = $plate_id AND $wpdb->posts.post_status = 'publish'
                    AND $wpdb->posts.ID = $wpdb->postmeta.post_id and $wpdb->postmeta.meta_key = 'plate_id'";
            $wpdb->query($sql);
        }
    }

    //为后台菜单添加分割线
    public function admin_menu_separator()
    {

        $position = 39;
        global $menu;
        $menu[$position] = array('', 'read', 'separator3', '', 'wp-menu-separator');
    }

    //后台帖子添加版块筛选
    public function plate_select($post_type)
    {

        if ('forum_post' === $post_type) {
            echo zib_bbs_edit::posts_plate_select();
        }
    }

    /**
     * @description: 为后台添加排序方式
     * @param {*} $query
     * @return {*}
     */
    public function admin_post_query($query)
    {
        if ($query->is_main_query() && is_admin()) {
            $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : '';
            if ($post_type && in_array($post_type, ['forum_post', 'plate'])) {
                $query->set('post_type', $post_type);
            }

            /**
             * 由于在zibpay/functions/admin/admin.php中已添加过相同代码，
             * 则此处无需再添加
            $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : '';
            if ($orderby) {
            $orderby_query = zib_bbs_query_orderby_filter($orderby);
            if (isset($orderby_query['meta_key'])) {
            $query->set('meta_key', $orderby_query['meta_key']);
            }
            if (isset($orderby_query['orderby'])) {
            $query->set('orderby', $orderby_query['orderby']);
            }
            }
             */

            $plate_id = isset($_GET['plate_id']) ? $_GET['plate_id'] : '';
            if ($plate_id) {
                if ($query->get('meta_query')) {
                    $meta_query = $query->get('meta_query');
                }
                $meta_query[] = array(
                    'key'     => 'plate_id',
                    'value'   => $plate_id,
                    'compare' => (is_array($plate_id) ? 'IN' : '='),
                );
                $query->set('meta_query', $meta_query);
            }
        }
    }

    /**
     * @description: 挂载显示论坛筛选按钮
     * @param {*} $views
     * @return {*}
     */
    public function views_comments($views_o)
    {
        $post_id   = isset($_REQUEST['p']) ? $_REQUEST['p'] : '';
        $post_type = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : '';
        if ($post_id && !$post_type) {
            $post      = get_post($post_id);
            $post_type = isset($post->post_type) ? $post->post_type : '';
        }

        $forum_attr = 'forum_post' == $post_type ? ' class="current"' : '';
        $post_attr  = 'post' == $post_type ? ' class="current"' : '';

        $forum_count = get_comments(
            array(
                'post_type' => 'forum_post',
                'count'     => true,
            )
        );
        $post_count = get_comments(
            array(
                'post_type' => 'post',
                'count'     => true,
            )
        );
        if (isset($views_o['all'])) {
            $views['all'] = $views_o['all'];
            unset($views_o['all']);
        }

        $views['forum_post'] = '<a' . $forum_attr . ' href="' . add_query_arg('post_type', 'forum_post') . '">' . $this->forum_name . $this->comment_name . '<span class="count">（<span class="trash-count">' . $forum_count . '</span>）</span></a>';
        $views['post']       = '<a' . $post_attr . ' href="' . add_query_arg('post_type', 'post') . '">文章评论<span class="count">（<span class="trash-count">' . $post_count . '</span>）</span></a>';

        return array_merge($views, $views_o);
    }

    /**
     * @description: 后台评论筛选挂钩
     * @param {*} $args
     * @return {*}
     */
    public function comments_list_table_query_args($args)
    {
        // Do not force the global WordPress comments screen to ordinary posts.
        // Forum comments remain available when the admin explicitly filters by post_type=forum_post.
        return $args;
    }

    /**
     * @description: 加入路由白名单
     * @param {*} $public_query_vars
     * @return {*}
     */
    public function query_vars($public_query_vars)
    {
        if (!is_admin()) {
            $public_query_vars[] = 'forum_post_edit';
        }
        return $public_query_vars;
    }

    /**
     * @description: 挂钩相应的固定链接需求
     * @param {*}
     * @return {*}
     */
    public function add_rewrite_rule()
    {
        global $wp_rewrite;
        $paged_slug    = $wp_rewrite->pagination_base;
        $comments_slug = $wp_rewrite->comments_pagination_base;

        add_rewrite_rule('^' . $this->plate_rewrite_slug . '/([0-9]+)' . $this->rewrite_suffix . '/' . $paged_slug . '/?([0-9]{1,})/?$', 'index.php?post_type=plate&p=$matches[1]&paged=$matches[2]', 'top'); //版块、翻页
        add_rewrite_rule('^' . $this->posts_rewrite_slug . '/([0-9]+)' . $this->rewrite_suffix . '/' . $paged_slug . '/?([0-9]{1,})/?$', 'index.php?post_type=forum_post&p=$matches[1]&paged=$matches[2]', 'top'); //帖子、翻页
        add_rewrite_rule('^' . $this->posts_rewrite_slug . '/([0-9]+)' . $this->rewrite_suffix . '/' . $comments_slug . '-([0-9]{1,})/?$', 'index.php?post_type=forum_post&p=$matches[1]&cpage=$matches[2]', 'top'); //帖子、评论翻页

        add_rewrite_rule('^' . $this->plate_rewrite_slug . '/([0-9]+)/?', 'index.php?post_type=plate&p=$matches[1]', 'top'); //版块
        add_rewrite_rule('^' . $this->posts_rewrite_slug . '/([0-9]+)/?', 'index.php?post_type=forum_post&p=$matches[1]', 'top'); //帖子

        //帖子编辑
        add_rewrite_rule('^' . $this->posts_edit_rewrite_slug . '/([0-9]+)/?', 'index.php?forum_post_edit=$matches[1]', 'top'); //帖子编辑
        add_rewrite_rule('^' . $this->posts_edit_rewrite_slug . '/?', 'index.php?forum_post_edit=add', 'top'); //帖子新建
        // file_put_contents(__DIR__ . '/error.json', json_encode($wp_rewrite));

    }

    //优化链接
    public function post_type_link($post_link, $post)
    {

        if (in_array($post->post_type, array('forum_post', 'plate'))) {
            global $wp_rewrite;
            $post_link = $wp_rewrite->get_extra_permastruct($post->post_type);
            if (!empty($post_link)) {
                $post_link = str_replace("%$post->post_type%", $post->ID, $post_link);
                $post_link = $this->rewrite_suffix ? home_url(untrailingslashit($post_link)) . $this->rewrite_suffix : home_url(user_trailingslashit($post_link));
            } else {
                $post_link = add_query_arg(
                    array(
                        'post_type' => $post->post_type,
                        'p'         => $post->ID,
                    ),
                    ''
                );
                $post_link = home_url($post_link);
            }
        }
        return $post_link;
    }

    //调整页面重定向检查，避免出现不能翻页
    public function redirect_canonical($redirect_url)
    {
        global $wp_query;
        $post_type = $wp_query->get('post_type');
        if ('plate' === $post_type && $wp_query->get('paged') > 1) {
            $redirect_url = false;
        }
        return $redirect_url;
    }

    //加载页面文件
    public function template_redirect()
    {

        global $wp_query;

        $post_type      = $wp_query->get('post_type');
        $taxonomy       = $wp_query->get('taxonomy');
        $post_load_args = array(
            'plate'       => 'plate',
            'forum_post'  => 'posts',
            'plate_cat'   => 'plate-cat',
            'forum_topic' => 'forum-topic',
            'forum_tag'   => 'forum-tag',
        );

        if (!is_404()) {
            if ($taxonomy && is_string($taxonomy) && is_tax() && isset($post_load_args[$taxonomy])) {
                $template = get_theme_file_path(ZIB_BBS_REQUIRE_URI . 'page/' . $post_load_args[$taxonomy] . '.php');
                load_template($template);
                exit;
            }

            if ($post_type && is_string($post_type) && is_single() && isset($post_load_args[$post_type])) {
                $template = get_theme_file_path(ZIB_BBS_REQUIRE_URI . 'page/' . $post_load_args[$post_type] . '.php');
                load_template($template);
                exit;
            }
        }

        //新建、编辑帖子页面
        $forum_post_edit = get_query_var('forum_post_edit');
        if ($forum_post_edit) {
            global $wp_query, $new_title, $new_description;

            $wp_query->is_home = false;
            $wp_query->is_404  = false;

            $is_edit = (int) $forum_post_edit;
            if ($is_edit) {
                $new = '编辑' . $this->posts_name;
            } else {
                $new = '新建' . $this->posts_name;
            }
            $new .= _get_delimiter() . $this->forum_name . zib_get_delimiter_blog_name();
            $new_title = $new_description = $new;
            $template  = get_theme_file_path(ZIB_BBS_REQUIRE_URI . 'page/posts-edit.php');

            add_filter('echo_seo_title', '__return_true');
            load_template($template);
            exit;
        }

    }

    //调整wp_query
    public function wp_query($wp_query)
    {

        if ($wp_query->is_main_query() && (is_tax('forum_topic') || is_tax('forum_tag'))) {

            $posts_per_page = _pz('bbs_posts_per_page', 20);
            $posts_per_page = isset($_REQUEST['paged_size']) ? (int) $_REQUEST['paged_size'] : $posts_per_page;

            $wp_query->set('post_type', 'forum_post');
            $wp_query->set('posts_per_page', $posts_per_page);
        }

    }

    public function body_class($classes)
    {
        if (in_array('site-layout-1', $classes) && (is_page_template('pages/forums.php') || in_array('single-plate', $classes))) {
            $classes   = array_diff($classes, ['site-layout-1']);
            $classes[] = 'site-layout-2';
        }
        return $classes;
    }

    //初始化必要meta参数
    public function initialization_term_meta($term_id, $tt_id, $taxonomy, $update)
    {
        //跳过更新
        if ($update) {
            return;
        }

        if (!in_array($taxonomy, ['plate_cat', 'forum_topic', 'forum_tag'])) {
            return;
        }

        $key['plate_cat'] = array(
            'last_reply' => '',
            'last_post'  => '',
            'views'      => 0,
        ); //版块分类

        $key['forum_topic'] = array(
            'views'          => 0,
            'favorite_count' => 0,
        ); //帖子的话题

        $key['forum_tag'] = array(
            'views'          => 0,
            'favorite_count' => 0,
        ); //帖子的标签

        foreach ($key[$taxonomy] as $k => $v) {
            add_term_meta($term_id, $k, $v);
        }
        add_term_meta($term_id, 'term_author', get_current_user_id());
    }

    /**
     * 初始化必要meta参数
     * @param int $post_ID 新建的文章ID
     * @param object $post 新建的文章对象
     * @param bool $update 是否是更新文章
     * @return {*}
     */
    public function initialization_posts_meta($post_ID, $post, $update)
    {
        if ($update) {
            return;
        }

        $post_type = $post->post_type;
        if (!in_array($post_type, ['plate', 'forum_post'])) {
            return;
        }

        $key['plate'] = array(
            'plate_type'        => '',
            'last_reply'        => '',
            'last_post'         => '',
            'posts_count'       => 0,
            'reply_count'       => 0,
            'today_reply_count' => 0,
            'views'             => 0,
            // 'like' => 0,
            'follow_count'      => 0,
        );
        $key['forum_post'] = array(
            'bbs_type'       => '',
            'last_reply'     => '',
            'score'          => 0,
            'views'          => 0,
            'dynamic_score'  => 0,
            // 'like' => 0,
            'topping'        => 0,
            'favorite_count' => 0,
        );

        foreach ($key[$post_type] as $k => $v) {
            add_post_meta($post_ID, $k, $v);
        }
    }

    //注册额外的文章状态
    public function register_post_statuses()
    {
        //注册状态-》关闭
        register_post_status(
            'closed',
            array(
                'label'                     => _x('关闭', 'post', 'zibll'),
                'label_count'               => _nx_noop('已关闭 <span class="count">(%s)</span>', '已关闭 <span class="count">(%s)</span>', 'post', 'zibll'),
                'public'                    => true,
                'show_in_admin_status_list' => true,
                'show_in_admin_all_list'    => true,
                'exclude_from_search'       => false,
                'source'                    => 'zibll',
            )
        );

        //注册状态-》隐藏
        register_post_status(
            'hidden',
            array(
                'label'                     => _x('Hidden', 'post', 'zibll'),
                'label_count'               => _nx_noop('Hidden <span class="count">(%s)</span>', 'Hidden <span class="count">(%s)</span>', 'post', 'zibll'),
                'private'                   => true,
                'exclude_from_search'       => true,
                'show_in_admin_status_list' => true,
                'show_in_admin_all_list'    => false,
                'source'                    => 'zibll',
            )
        );

        global $wp_post_statuses;

        if (!empty($wp_post_statuses['trash'])) {

            // User can view trash so set internal to false
            if (current_user_can('view_trash')) {
                $wp_post_statuses['trash']->internal  = false;
                $wp_post_statuses['trash']->protected = true;

                // User cannot view trash so set internal to true
            } else {
                $wp_post_statuses['trash']->internal = true;
            }
        }
    }

    //给用户添加权限，让非管理可以查看其他人待审核内容
    public function user_has_cap($allcaps, $caps)
    {
        if (!empty($allcaps['edit_posts'])) {
            return $allcaps;
        }

        $user_id = get_current_user_id();
        if ($user_id && in_array('edit_others_posts', $caps)) {

            global $wp_query;
            $post_type = $wp_query->get('post_type');
            $post_id   = $wp_query->get('p');

            if ('forum_post' === $post_type && $wp_query->is_single()) {
                $post = get_post($post_id);
                if (zib_bbs_current_user_can('posts_audit', $post)) {
                    //拥有审核他人帖子的权限
                    $allcaps['edit_others_posts'] = true;
                }
            }
        }

        return $allcaps;
    }

    //注册需要的文章类型
    public function register_post_type()
    {
        //注册版块
        register_post_type(
            'plate',
            array(
                'labels'              => array(
                    'name'          => $this->forum_name . $this->plate_name,
                    'singular_name' => $this->forum_name,
                    'all_items'     => '所有' . $this->plate_name,
                    'add_new'       => '添加' . $this->plate_name,
                    'add_new_item'  => '创建新的' . $this->plate_name,
                    'edit'          => '编辑',
                    'edit_item'     => '编辑' . $this->plate_name,
                    'new_item'      => '新' . $this->plate_name,
                    'view'          => '查看' . $this->plate_name,
                    'view_item'     => '查看' . $this->plate_name,
                ),
                'supports'            => array(
                    'title',
                    'excerpt',
                    'author',
                ),
                'rewrite'             => array(
                    'slug'       => $this->plate_rewrite_slug,
                    'with_front' => false,
                ),
                'menu_position'       => 40,
                'exclude_from_search' => true, //搜索排除
                'show_in_nav_menus'   => true,
                'public'              => true,
                'show_ui'             => $this->is_super_admin, //后台权限
                'can_export'          => true,
                'hierarchical'        => true,
                'query_var'           => true,
                'menu_icon'           => 'dashicons-buddicons-forums',
                'source'              => 'zibll',
            )
        );

        $taxonomy_args = [
            'labels'            => [
                'name'              => __($this->forum_name . $this->plate_name . '分类'),
                'singular_name'     => __($this->forum_name . $this->plate_name . '分类'),
                'search_items'      => __('搜索' . $this->plate_name . '分类'),
                'all_items'         => __('所有' . $this->plate_name . '分类'),
                'parent_item'       => __('父' . $this->plate_name . '分类'),
                'parent_item_colon' => __('父' . $this->plate_name . '分类' . ':'),
                'edit_item'         => __('编辑' . $this->plate_name . '分类'),
                'update_item'       => __('更新' . $this->plate_name . '分类'),
                'add_new_item'      => __('添加新' . $this->plate_name . '分类'),
                'new_item_name'     => __('新' . $this->plate_name . '分类' . '名称'),
                'menu_name'         => __('分类'),
            ],
            'description'       => $this->forum_name . $this->plate_name . '分类',
            'hierarchical'      => true, //不允许嵌套
            'show_ui'           => $this->is_super_admin, //后台权限
            'show_in_menu'      => $this->is_super_admin, //后台权限
            'show_in_rest'      => true,
            'show_admin_column' => true, //后台权限
            'query_var'         => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false, //不在标签云小工具中显示
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'assign_categories',
            ),
        ];
        register_taxonomy('plate_cat', ['plate'], $taxonomy_args);

        //注册帖子
        register_post_type(
            'forum_post',
            array(
                'labels'              => array(
                    'name'          => $this->forum_name . $this->posts_name,
                    'singular_name' => $this->posts_name,
                    'all_items'     => '所有' . $this->posts_name,
                    'add_new'       => '添加' . $this->posts_name,
                    'add_new_item'  => '创建新的' . $this->posts_name,
                    'edit'          => '编辑' . $this->posts_name,
                    'edit_item'     => '编辑' . $this->posts_name,
                    'new_item'      => '新' . $this->posts_name,
                    'view'          => '查看' . $this->posts_name,
                    'view_item'     => '[' . $this->forum_name . ']查看' . $this->posts_name,
                ),
                'supports'            => array(
                    'title',
                    'editor',
                    'excerpt',
                    'comments',
                    'author',
                ),
                'rewrite'             => array(
                    'slug'       => $this->posts_rewrite_slug,
                    'with_front' => false,
                ),
                'menu_position'       => 40,
                'show_in_nav_menus'   => true,
                'exclude_from_search' => $this->exclude_search_posts, //前台排除搜索
                'public'              => true,
                'show_ui'             => $this->is_super_admin, //后台权限
                'can_export'          => true,
                'hierarchical'        => false,
                'query_var'           => true,
                'menu_icon'           => 'dashicons-buddicons-topics',
                'source'              => 'zibll',
                'show_in_rest'        => $this->posts_show_in_rest,
            )
        );

        $taxonomy_args = [
            'labels'            => [
                'name'              => __($this->forum_name . $this->topic_name),
                'singular_name'     => __($this->forum_name . $this->topic_name),
                'search_items'      => __('搜索' . $this->topic_name),
                'all_items'         => __('所有' . $this->topic_name),
                'parent_item'       => __('父' . $this->topic_name),
                'parent_item_colon' => __('父' . $this->topic_name . ':'),
                'edit_item'         => __('编辑' . $this->topic_name),
                'update_item'       => __('更新' . $this->topic_name),
                'add_new_item'      => __('添加新' . $this->topic_name),
                'new_item_name'     => __('新' . $this->topic_name . '名称'),
                'menu_name'         => __($this->topic_name),
            ],
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'assign_categories',
            ),
            'description'       => $this->posts_name . $this->topic_name,
            'hierarchical'      => false, //不允许嵌套
            'show_ui'           => $this->is_super_admin, //后台权限
            'show_in_menu'      => $this->is_super_admin, //后台权限
            'show_in_rest'      => true,
            'show_admin_column' => true, //后台权限
            'query_var'         => true,
            'show_tagcloud'     => true, //不在标签云小工具中显示
        ];
        register_taxonomy('forum_topic', ['forum_post'], $taxonomy_args);

        //为新的文章类型，注册分类和标签分类法
        $taxonomy_args = [
            'labels'            => [
                'name'              => __($this->forum_name . $this->tag_name),
                'singular_name'     => __($this->forum_name . $this->tag_name),
                'search_items'      => __('搜索' . $this->tag_name),
                'all_items'         => __('所有' . $this->tag_name),
                'parent_item'       => __('父' . $this->tag_name),
                'parent_item_colon' => __('父' . $this->tag_name . ':'),
                'edit_item'         => __('编辑' . $this->tag_name),
                'update_item'       => __('更新' . $this->tag_name),
                'add_new_item'      => __('添加新' . $this->tag_name),
                'new_item_name'     => __('新' . $this->tag_name . '名称'),
                'menu_name'         => __($this->tag_name),
            ],
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'assign_categories',
            ),
            'description'       => $this->posts_name . $this->tag_name,
            'hierarchical'      => false, //不允许嵌套
            'show_ui'           => $this->is_super_admin, //后台权限
            'show_in_menu'      => $this->is_super_admin, //后台权限
            'show_in_rest'      => true,
            'show_admin_column' => true, //后台权限
            'query_var'         => true,
            'show_tagcloud'     => true, //不在标签云小工具中显示
        ];

        register_taxonomy('forum_tag', ['forum_post'], $taxonomy_args);
    }

    //后台用户表格
    public function users_columns($columns)
    {
        $columns['bbs_count'] = $this->plate_name . ' · ' . $this->posts_name;

        return $columns;
    }

    //后台用户表格
    public function users_custom_column($var, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'bbs_count':
                $plate_count = zib_bbs_get_user_plate_count($user_id);
                $posts_count = zib_bbs_get_user_posts_count($user_id);

                $html = '<a href="edit.php?post_type=plate&author=' . $user_id . '">' . $this->plate_name . '：' . $plate_count . '</a><br><a href="edit.php?post_type=forum_post&author=' . $user_id . '">' . $this->posts_name . '：' . $posts_count . '</a>';

                return $html;
                break;
        }
        return $var;
    }

    /**
     * @description: WP后台版块表格
     * @param {*} $columns
     * @return {*}
     */
    public function plate_columns($columns)
    {
        $order    = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';
        $order_by = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
        $o_icon   = '<i class="opacity5 ml3 fa fa-long-arrow-' . ($order == 'asc' ? 'down' : 'up') . '"></i>';

        if (isset($columns['author'])) {
            unset($columns['author']);
        }
        $columns['author'] = $this->plate_author_name;

        $columns['moderator'] = $this->plate_moderator_name;

        $columns['all_count'] = '<a href="' . add_query_arg(array('orderby' => 'posts_count', 'order' => $order)) . '"><span>' . $this->posts_name . ($order_by == 'posts_count' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'views', 'order' => $order)) . '"><span>阅读' . ($order_by == 'views' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'follow_count', 'order' => $order)) . '"><span>' . $this->plate_follow_name . ($order_by == 'follow_count' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'reply_count', 'order' => $order)) . '"><span>' . $this->comment_name . ($order_by == 'reply_count' ? $o_icon : '') . '</span>';

        $columns['last_time'] = '<a href="' . add_query_arg(array('orderby' => 'last_post', 'order' => $order)) . '"><span>最后发布' . ($order_by == 'last_post' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'last_reply', 'order' => $order)) . '"><span>最后' . $this->comment_name . ($order_by == 'last_reply' ? $o_icon : '') . '</span></a>';

        return $columns;
    }

    /**
     * @description: WP后台版块表格
     * @param {*} $column_name
     * @param int $plate_id
     * @return {*}
     */
    public function plate_custom_column($column_name, $plate_id)
    {
        switch ($column_name) {
            case 'moderator':
                $moderator_ids = get_post_meta($plate_id, 'moderator', true);

                $lists = '';
                if (is_array($moderator_ids)) {
                    foreach ($moderator_ids as $user_id) {
                        $user = get_userdata($user_id);
                        $lists .= '<a href="' . zib_get_user_home_url($user_id) . '">' . $user->display_name . '</a>、';
                    }
                }
                $lists = trim($lists, '、') ?: '--';
                echo $lists;
                break;

            case 'all_count':
                echo '<div style="font-size: 12px;">' . $this->posts_name . zib_bbs_get_plate_posts_count($plate_id) . ' · 热度' . zib_bbs_get_plate_views_cut_count($plate_id) . ' · ' . $this->plate_follow_name . zib_bbs_get_plate_follow_cut_count($plate_id) . ' · ' . $this->comment_name . zib_bbs_get_plate_reply_cut_count($plate_id) . '</div>';
                break;
            case 'last_time':

                $last_post  = get_post_meta($plate_id, 'last_post', true);
                $last_post  = $last_post ? '<span title="' . $last_post . '">' . zib_get_time_ago($last_post) . '</span>' : '——';
                $last_reply = get_post_meta($plate_id, 'last_reply', true);
                $last_reply = $last_reply ? '<span title="' . $last_reply . '">' . zib_get_time_ago($last_reply) . '</span>' : '——';

                echo '<div style="font-size: 12px;">';
                echo $last_post . ' · ' . $last_reply;
                echo '</div>';

                break;
        }
    }

    /**
     * @description: WP后台帖子表格
     * @param {*} $columns
     * @return {*}
     */
    public function posts_columns($columns)
    {
        $order    = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';
        $order_by = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
        $o_icon   = '<i class="opacity5 ml3 fa fa-long-arrow-' . ($order == 'asc' ? 'down' : 'up') . '"></i>';

        if (isset($columns['cb'])) {
            $add_columns['cb'] = $columns['cb'];
            unset($columns['cb']);
        }
        if (isset($columns['title'])) {
            $add_columns['title'] = $columns['title'];
            unset($columns['title']);
        }

        $add_columns['plate_id']  = '<a href="' . add_query_arg(array('orderby' => 'plate_id', 'order' => $order)) . '">' . $this->plate_name . ($order_by == 'plate_id' ? $o_icon : '') . '</a>';
        $add_columns['all_count'] = '<a href="' . add_query_arg(array('orderby' => 'views', 'order' => $order)) . '"><span>阅读' . ($order_by == 'views' ? $o_icon : '') . '</span></a>·<a href="' . add_query_arg(array('orderby' => 'score', 'order' => $order)) . '"><span>评分' . ($order_by == 'score' ? $o_icon : '') . '</span></a>·<a href="' . add_query_arg(array('orderby' => 'favorite_count', 'order' => $order)) . '"><span>收藏' . ($order_by == 'favorite_count' ? $o_icon : '') . '</span></a></a>';

        return array_merge($add_columns, $columns);
    }

    //后台帖子表格添加列
    public function posts_custom_column($column_name, $posts_id)
    {
        switch ($column_name) {
            case 'plate_id':
                $plate_id = zib_bbs_get_plate_id($posts_id);
                if (!$plate_id) {
                    echo '<span style="color:#ee4545;">未选择' . $this->plate_name . '</span>';
                    break;
                }

                $plate = get_post($plate_id);
                if (empty($plate->ID)) {
                    echo '<span style="color:#ee4545;">' . $this->plate_name . '不存在或者已删除</span>';
                    break;
                }
                $trash = '';
                if ('trash' == $plate->post_status) {
                    $trash = '<span style="color:#ee4545;">[回收站]</span>';
                }

                $permalink = get_permalink($plate_id);
                $edit      = get_edit_post_link($plate_id);

                $title = esc_attr(get_the_title($plate_id));

                $title = '<a class="row-title" href="' . add_query_arg('plate_id', $plate_id) . '">' . $title . '</a>';

                $follow_count = zib_bbs_get_plate_follow_cut_count($plate_id);
                $reply_count  = zib_bbs_get_plate_reply_cut_count($plate_id);
                $views_count  = zib_bbs_get_plate_views_cut_count($plate_id);

                $html = '';
                $html .= '<div>' . $title . $trash . '</div>';
                $html .= '<div style="font-size: 12px;opacity: .8;">' . $this->posts_name . zib_bbs_get_plate_posts_count($plate_id) . ' · 热度' . $views_count . ' · ' . $this->plate_follow_name . $follow_count . ' · ' . $this->comment_name . $reply_count . '</div>';
                $html .= '<div class="row-actions"><span class="view"><a href="' . $permalink . '">查看</a></span> | <span class="view"><a href="' . $edit . '">编辑</a></span></div>';
                $html .= '';
                $html .= '';

                echo $html;
                break;

            case 'all_count':
                $views          = _cut_count((string) get_post_meta($posts_id, 'views', true));
                $score          = _cut_count((string) get_post_meta($posts_id, 'score', true));
                $favorite_count = _cut_count((string) get_post_meta($posts_id, 'favorite_count', true));

                echo '<div style="font-size: 12px;">阅读' . $views . ' · 评分' . $score . ' · 收藏' . $favorite_count . '</div>';
                break;
        }
    }

    /**
     * @description: 挂钩为后台的版块分类表格添加列
     * @param {*} $columns
     * @return {*}
     */
    public function plate_cat_columns($columns)
    {

        if (isset($columns['cb'])) {
            $add_columns['cb'] = $columns['cb'];
            unset($columns['cb']);
        }
        if (isset($columns['name'])) {
            $add_columns['name'] = $columns['name'];
            unset($columns['name']);
        }

        $add_columns['moderator'] = $this->cat_moderator_name;

        return array_merge($add_columns, $columns);
    }

    //挂钩为后台的版块分类表格添加列
    public function plate_cat_custom_column($columns, $column, $id)
    {
        if ('moderator' === $column) {
            $moderator_ids = get_term_meta($id, 'moderator', true);

            $lists = '';
            if (is_array($moderator_ids)) {
                foreach ($moderator_ids as $user_id) {
                    $user = get_userdata($user_id);
                    $lists .= '<a href="' . zib_get_user_home_url($user_id) . '">' . $user->display_name . '</a>、';
                }
            }
            $columns = $lists ? trim($lists, '、') : '--';
        }
        return $columns;
    }

}
