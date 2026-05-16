<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2025-02-16 21:32:02
 * @LastEditTime: 2025-10-01 21:06:15
 * @Email: 770349780@qq.com
 * @Project: Zibll子比主题
 * @Description: 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 */

// 初始化，创建文章类型、论坛
class zib_shop
{
    public $s                    = false; //是否开启商城功能
    public $home_url             = null;
    public $edit_rewrite_slug    = '';
    public $product_rewrite_slug = '';
    public $cart_rewrite_slug    = '';
    public $rewrite_suffix       = '';
    public $shop_name            = '商城';
    public $cart_name            = '购物车';
    public $product_name         = '商品';
    public $tag_name             = '特色标签';
    public $cat_name             = '分类';
    public $discount_name        = '优惠活动';
    public $product_author_name  = '商家';
    public $currency_symbol      = '￥';
    public $is_admin_can         = false;

    public function __construct()
    {

    }

    public static function instance()
    {

        static $instance = null;
        if (null !== $instance) {
            return $instance;
        }

        $instance    = new self();
        $instance->s = _pz('shop_s');

        if ($instance->s) {
            //定义参数
            //路由别名
            // $instance->edit_rewrite_slug    = _pz('shop_edit_rewrite_slug') ?: 'shop-edit'; //暂未启用
            $instance->cart_rewrite_slug    = _pz('shop_cart_rewrite_slug') ?: 'cart';
            $instance->product_rewrite_slug = _pz('shop_product_rewrite_slug') ?: 'shop';
            $instance->rewrite_suffix       = _pz('shop_rewrite_suffix_html_s', true) ? '.html' : '';
            $instance->is_admin_can         = is_super_admin();
        }

        $instance->setup();
        return $instance;
    }

    /**
     * @description: 启动函数
     * @param {*}
     * @return {*}
     */
    public function setup()
    {
        new zib_shop_setup($this);
    }

    //主查询
    public function main_post_query($query)
    {

        //搜索页面的
        if ($query->is_search() && $query->is_main_query() && !$query->is_admin) {
            global $search_type;
            if ('product' === $search_type) {
                $query->set('post_type', 'shop_product');
            }
        }

        //商城分类、标签、优惠活动
        if ($query->is_main_query() && (is_tax('shop_cat') || is_tax('shop_discount') || is_tax('shop_tag'))) {
            $shop_list_opt  = _pz('shop_list_opt');
            $posts_per_page = $shop_list_opt['count'] ?? 12;
            $orderby        = $shop_list_opt['orderby'] ?? '';

            if ($orderby) {
                $orderby_keys      = zib_get_query_mate_orderby_keys();
                $mate_orderbys     = $orderby_keys['value'];
                $mate_orderbys_num = $orderby_keys['value_num'];

                if (in_array($orderby, $mate_orderbys_num)) {
                    $query->set('orderby', 'meta_value_num');
                    $query->set('meta_key', $orderby);
                } elseif (in_array($orderby, $mate_orderbys)) {
                    $query->set('orderby', 'meta_value');
                    $query->set('meta_key', $orderby);
                } else {
                    $query->set('orderby', $orderby);
                }
            }

            $posts_per_page = isset($_REQUEST['paged_size']) ? (int) $_REQUEST['paged_size'] : $posts_per_page;
            $query->set('post_type', 'shop_product');
            $query->set('posts_per_page', $posts_per_page);
        }
    }

    /**
     * @description: 为后台添加排序方式
     * @param {*} $query
     * @return {*}
     */
    public function admin_post_query($query)
    {}

    /**
     * @description: 后台评论筛选挂钩
     * @param {*} $args
     * @return {*}
     */
    public function comments_list_table_query_args($args)
    {
        // Do not force the global WordPress comments screen to shop_product.
        // Forcing post_type here hides normal article comments from moderation.
        // Product comments remain available when the admin explicitly filters by post_type=shop_product.
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
            $public_query_vars[] = 'shop_product_edit';
            $public_query_vars[] = 'shop_cart';
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

        add_rewrite_rule('^' . $this->product_rewrite_slug . '/([0-9]+)' . $this->rewrite_suffix . '/' . $paged_slug . '/?([0-9]{1,})/?$', 'index.php?post_type=shop_product&p=$matches[1]&paged=$matches[2]', 'top'); //商品详情页、翻页
        add_rewrite_rule('^' . $this->product_rewrite_slug . '/([0-9]+)' . $this->rewrite_suffix . '/' . $comments_slug . '-([0-9]{1,})/?$', 'index.php?post_type=shop_product&p=$matches[1]&cpage=$matches[2]', 'top'); //商品详情页、评论翻页

        add_rewrite_rule('^' . $this->product_rewrite_slug . '/([0-9]+)/?', 'index.php?post_type=shop_product&p=$matches[1]', 'top'); //商品详情页面

        //商品编辑：暂未启用
        // add_rewrite_rule('^' . $this->edit_rewrite_slug . '/([0-9]+)/?', 'index.php?shop_product_edit=$matches[1]', 'top'); //帖子编辑
        // add_rewrite_rule('^' . $this->edit_rewrite_slug . '/?', 'index.php?shop_product_edit=add', 'top'); //帖子新建

        //购物车
        add_rewrite_rule('^' . $this->cart_rewrite_slug . '/?', 'index.php?shop_cart=1', 'top');
    }

    //优化链接
    public function post_type_link($post_link, $post)
    {

        if (in_array($post->post_type, array('shop_product'))) {
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
        if ('shop_product' === $post_type && $wp_query->get('paged') > 1) {
            $redirect_url = false;
        }
        return $redirect_url;
    }

    //加载页面文件
    public function template_redirect()
    {

        global $wp_query, $new_title, $new_description;

        $post_type = $wp_query->get('post_type');
        $taxonomy  = $wp_query->get('taxonomy');

        $taxonomy_load_args = array(
            'shop_cat'      => 'cat',
            'shop_discount' => 'dis',
            'shop_tag'      => 'tag',
        );

        $post_load_args = array(
            'shop_product' => 'product',
        );

        if (!is_404()) {
            if ($taxonomy && is_string($taxonomy) && is_tax() && isset($taxonomy_load_args[$taxonomy])) {
                $template = get_theme_file_path(ZIB_SHOP_REQUIRE_URI . 'page/' . $taxonomy_load_args[$taxonomy] . '.php');
                load_template($template);
                exit;
            }

            if ($post_type && is_string($post_type) && is_single() && isset($post_load_args[$post_type])) {
                $template = get_theme_file_path(ZIB_SHOP_REQUIRE_URI . 'page/' . $post_load_args[$post_type] . '.php');
                load_template($template);
                exit;
            }
        }

        //新建、编辑页面：暂未启用
        $shop_product_edit = get_query_var('shop_product_edit');
        if ($shop_product_edit) {

            $wp_query->is_home = false;
            $wp_query->is_404  = false;

            $is_edit = (int) $shop_product_edit;
            if ($is_edit) {
                $new = '编辑' . $this->product_name;
            } else {
                $new = '创建' . $this->product_name;
            }
            $new .= _get_delimiter() . $this->shop_name . zib_get_delimiter_blog_name();
            $new_title = $new_description = $new;
            $template  = get_theme_file_path(ZIB_SHOP_REQUIRE_URI . 'page/product-edit.php');

            add_filter('echo_seo_title', '__return_true');
            load_template($template);
            exit;
        }

        //购物车
        if (get_query_var('shop_cart')) {
            $wp_query->is_home = false;
            $wp_query->is_404  = false;

            $new       = $this->cart_name . zib_get_delimiter_blog_name();
            $new_title = $new_description = $new;

            $template = get_theme_file_path(ZIB_SHOP_REQUIRE_URI . 'page/cart.php');
            load_template($template);
            exit;
        }

    }

    //初始化必要meta参数
    public function initialization_term_meta($term_id, $tt_id, $taxonomy, $update)
    {
        if ($update) {
            return;
        }

        if (!in_array($taxonomy, ['shop_cat', 'shop_discount', 'shop_tag'])) {
            return;
        }

        $key['shop_cat'] = array(
            'sales_volume' => '', //销量
            'views'        => 0,
        );

        $key['shop_discount'] = array(
            'sales_volume' => '', //销量
            'views'        => 0,
        );

        $key['shop_tag'] = array(
            'sales_volume' => '', //销量
            'views'        => 0,
        );

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
        if (!in_array($post_type, ['shop_product'])) {
            return;
        }

        $key['shop_product'] = array(
            'score'          => 0, //评分
            'sales_volume'   => 0, //销量
            'views'          => 0,
            'favorite_count' => 0, //收藏数量
        );

        foreach ($key[$post_type] as $k => $v) {
            add_post_meta($post_ID, $k, $v);
        }
    }

    //注册额外的文章状态
    public function register_post_statuses()
    {}

    //为后台菜单添加分割线
    public function admin_menu_separator()
    {

        $position = 34;
        global $menu;
        $menu[$position] = array('', 'read', 'separator3', '', 'wp-menu-separator');
    }

    //注册需要的文章类型
    public function register_post_type()
    {
        //注册商品
        register_post_type(
            'shop_product',
            array(
                'labels'              => array(
                    'name'          => $this->shop_name,
                    'singular_name' => $this->product_name,
                    'all_items'     => '所有' . $this->product_name,
                    'add_new'       => '创建新' . $this->product_name,
                    'add_new_item'  => '创建新' . $this->product_name,
                    'edit'          => '编辑' . $this->product_name,
                    'edit_item'     => '编辑' . $this->product_name,
                    'new_item'      => '新' . $this->product_name,
                    'view'          => '查看' . $this->product_name,
                    'view_item'     => '[' . $this->shop_name . ']查看' . $this->product_name,
                ),
                'supports'            => array(
                    'title',
                    'editor',
                    //  'excerpt',
                    'comments',
                    'author',
                ),
                'rewrite'             => array(
                    'slug'       => $this->product_rewrite_slug,
                    'with_front' => false,
                ),
                'menu_position'       => 35,
                'show_in_nav_menus'   => true,
                'exclude_from_search' => true, //前台排除搜索
                'public'              => true,
                'show_ui'             => $this->is_admin_can, //后台权限
                'can_export'          => true,
                'hierarchical'        => false,
                'query_var'           => true,
                'menu_icon'           => 'dashicons-store',
                'source'              => 'zibll',
                'show_in_rest'        => true,
            )
        );

        //分类
        $taxonomy_args = [
            'labels'            => [
                'name'              => __($this->product_name . $this->cat_name),
                'singular_name'     => __($this->product_name . $this->cat_name),
                'search_items'      => __('搜索' . $this->product_name . $this->cat_name),
                'all_items'         => __('所有' . $this->product_name . $this->cat_name),
                'parent_item'       => __('父' . $this->product_name . $this->cat_name),
                'parent_item_colon' => __('父' . $this->product_name . $this->cat_name . ':'),
                'edit_item'         => __('编辑' . $this->product_name . $this->cat_name),
                'update_item'       => __('更新' . $this->product_name . $this->cat_name),
                'add_new_item'      => __('添加新' . $this->product_name . $this->cat_name),
                'new_item_name'     => __('新' . $this->product_name . $this->cat_name . '名称'),
                'menu_name'         => __($this->product_name . $this->cat_name),
            ],
            'capabilities'      => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'assign_categories',
            ),
            // 'meta_box_cb'        => true, //普通编辑器侧边栏编辑的回调函数，如果为false,则不在普通编辑器侧边栏显示，保持默认请注销
            'description'       => $this->product_name . $this->cat_name,
            'hierarchical'      => true, //允许嵌套
            'show_ui'           => true, //后台权限
            'show_in_menu'      => $this->is_admin_can, //后台权限
            'show_in_rest'      => true,
            'show_admin_column' => true, //后台权限
            'query_var'         => true,
            'show_tagcloud'     => true, //不在标签云小工具中显示
        ];
        register_taxonomy('shop_cat', ['shop_product'], $taxonomy_args);

        //优惠活动，折扣
        $taxonomy_args = [
            'labels'             => [
                'name'              => __($this->product_name . $this->discount_name),
                'singular_name'     => __($this->product_name . $this->discount_name),
                'search_items'      => __('搜索' . $this->discount_name),
                'all_items'         => __('所有' . $this->discount_name),
                'parent_item'       => __('父' . $this->discount_name),
                'parent_item_colon' => __('父' . $this->discount_name . ':'),
                'edit_item'         => __('编辑' . $this->discount_name),
                'update_item'       => __('更新' . $this->discount_name),
                'add_new_item'      => __('添加新' . $this->discount_name),
                'new_item_name'     => __('新' . $this->discount_name . '名称'),
                'menu_name'         => __($this->discount_name),
            ],
            'capabilities'       => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'assign_categories',
            ),
            'description'        => $this->product_name . $this->discount_name,
            'hierarchical'       => false, //不允许嵌套
            'show_in_rest'       => false, //不在古腾堡编辑器中显示
            'meta_box_cb'        => false, //不在普通编辑器侧边栏显示
            'show_in_quick_edit' => false, //不在快速编辑中显示
            'show_ui'            => true, //显示可编辑
            'show_in_menu'       => $this->is_admin_can, //后台权限
            'show_admin_column'  => true, //后台权限
            'query_var'          => true,
            'show_tagcloud'      => true, //不在标签云小工具中显示
        ];
        register_taxonomy('shop_discount', ['shop_product'], $taxonomy_args);

        //标签
        $taxonomy_args = [
            'labels'             => [
                'name'              => __($this->product_name . $this->tag_name),
                'singular_name'     => __($this->product_name . $this->tag_name),
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
            'capabilities'       => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'assign_categories',
            ),
            'description'        => $this->product_name . $this->tag_name,
            'hierarchical'       => false, //不允许嵌套
            'show_in_rest'       => true, //古腾堡编辑器中显示
            'show_in_quick_edit' => true, //快速编辑中显示
            'show_ui'            => true, //显示可编辑
            'show_in_menu'       => true, //后台权限
            'show_admin_column'  => true, //后台权限
            'query_var'          => true,
            'show_tagcloud'      => true, //不在标签云小工具中显示
        ];

        register_taxonomy('shop_tag', ['shop_product'], $taxonomy_args);
    }

    //后台用户表格
    public function users_columns($columns)
    {
        //  $columns['shop_data'] = $this->shop_name;

        return $columns;
    }

    //后台用户表格
    public function users_custom_column($var, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'shop_data':

                //暂未使用

                $html = '***';

                return $html;
                break;
        }
        return $var;
    }

    /**
     * @description: WP后商品表格
     * @param {*} $columns
     * @return {*}
     */
    public function product_columns($columns)
    {
        $order    = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';
        $order_by = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
        $o_icon   = '<i class="opacity5 ml3 fa fa-long-arrow-' . ($order == 'asc' ? 'down' : 'up') . '"></i>';

        $columns['author'] = $this->product_author_name;

        $new_columns = array(
            'cb'    => $columns['cb'],
            'title' => $columns['title'],
        );

        $new_columns['pay_data']  = '<a href="' . add_query_arg(array('orderby' => 'sales_volume', 'order' => $order)) . '"><span>销量' . ($order_by == 'sales_volume' ? $o_icon : '') . '</span></a>';
        $new_columns['all_count'] = '<a href="' . add_query_arg(array('orderby' => 'score', 'order' => $order)) . '"><span>评价' . ($order_by == 'score' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'views', 'order' => $order)) . '"><span>阅读' . ($order_by == 'views' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'favorite_count', 'order' => $order)) . '"><span>收藏' . ($order_by == 'favorite_count' ? $o_icon : '') . '</span></a>';

        if (isset($columns['comments'])) {
            unset($columns['comments']);
        }

        $columns = array_merge($new_columns, $columns);
        return $columns;
    }

    /**
     * @description: WP后台商品表格
     * @param {*} $column_name
     * @param int $plate_id
     * @return {*}
     */
    public function product_custom_column($column_name, $id)
    {
        switch ($column_name) {
            case 'pay_data':
                $sales = zib_shop_get_product_sales_volume($id);
                $data  = zib_shop_get_product_config($id);

                $is_points = ($data['pay_modo'] ?? '') == 'points' ? true : false;
                $price     = $data['start_price'] ?? 0;
                $html      = '<div class="px12">起始价:' . ($is_points ? '积分' : '￥') . $price . '</div>';

                $html .= '<a title="点击查看销售明细" href="' . zibpay_get_admin_shop_order_url('status=1&post_id=' . $id) . '">销量：' . $sales . '</a>';

                echo $html;
                break;

            case 'all_count':
                $score_data = zib_get_post_meta($id, 'score_data');
                if (!isset($score_data['average'])) {
                    $score = '<span class="badg">暂无评价</span>';
                } else {
                    $average = $score_data['average'] ?? 0;
                    $count   = $score_data['count'] ?? 0;
                    $class   = $average >= 3.5 ? 'c-green' : 'c-red';
                    $score   = '<a href="/wp-admin/edit-comments.php?p=' . $id . '&comment_status=approved"><span class="badg ' . $class . '">' . $average . '分</span><span style="font-size: 12px;">' . ($count ? '(' . $count . '次)' : '') . '</span></a>';
                }

                $views          = _cut_count((string) get_post_meta($id, 'views', true));
                $favorite_count = _cut_count((string) get_post_meta($id, 'favorite_count', true));

                echo $score . '<div style="font-size: 12px;">阅读' . $views . ' · 收藏' . $favorite_count . '</div>';
                break;
        }
    }

    /**
     * @description: 挂钩为后台的分类表格添加列
     * @param {*} $columns
     * @return {*}
     */
    public function cat_columns($columns)
    {

        return $columns;

        if (isset($columns['cb'])) {
            $add_columns['cb'] = $columns['cb'];
            unset($columns['cb']);
        }
        if (isset($columns['name'])) {
            $add_columns['name'] = $columns['name'];
            unset($columns['name']);
        }

        //    $add_columns['sales'] = __('销量', 'zibll');

        return array_merge($add_columns, $columns);
    }

    //挂钩为后台的版块分类表格添加列
    public function cat_custom_column($columns, $column, $id)
    {
        switch ($column) {
            case 'sales':
                $sales   = zib_shop_get_cat_sales_count($id);
                $columns = $sales ? $sales : 0;
                return $columns;

                break;
        }

        return $columns;
    }

    public function tag_columns($columns)
    {
        $columns['priority'] = __('优先级', 'zibll');
        return $columns;
    }

    public function tag_custom_column($columns, $column, $id)
    {
        switch ($column) {
            case 'priority':

                $config = zib_shop_get_tag_config($id);

                $priority = $config['priority'] ?? 50;
                $priority = $priority . '.' . $id;
                $priority = '<span class="badg ' . ($config['class'] ?? '') . '">' . $priority . '</span>';

                if (!empty($config['is_important'])) {
                    $priority .= '<span class="badg ' . $config['class'] . '">重点</span>';
                }

                return $priority;
                break;
        }

        return $columns;
    }

    public function discount_columns($columns)
    {

        if (isset($columns['cb'])) {
            $add_columns['cb'] = $columns['cb'];
            unset($columns['cb']);
        }
        if (isset($columns['name'])) {
            $add_columns['name'] = $columns['name'];
            unset($columns['name']);
        }

        $add_columns['discount'] = __('活动', 'zibll');
        $add_columns['limit']    = __('限制', 'zibll');
        $add_columns['priority'] = __('优先级', 'zibll');

        if (isset($columns['slug'])) {
            unset($columns['slug']);
        }

        return array_merge($add_columns, $columns);
    }

    //挂钩为后台的版块分类表格添加列
    public function discount_custom_column($columns, $column, $id)
    {
        switch ($column) {
            case 'discount':
                $discount_data = zib_shop_get_discount_data($id);
                $name          = $discount_data['small_badge'] !== '赠品' ? '<span class="badg">' . $discount_data['small_badge'] . '</span>' : '';
                if (!empty($discount_data['is_important'])) {
                    $name .= '<span class="badg ' . $discount_data['important_class'] . '">重点</span>';
                }

                if (!empty($discount_data['discount_error'])) {
                    $name .= '<span class="badg c-red">[错误：' . $discount_data['discount_error_msg'] . ']</span>';
                } elseif (!empty($discount_data['discount_type'])) {
                    switch ($discount_data['discount_type']) {
                        case 'reduction':
                            $discount_text = '立减' . $discount_data['reduction_amount'];
                            break;
                        case 'discount':
                            $discount_text = $discount_data['discount_amount'] . '折';
                            break;
                        case 'gift':
                            $gift_config = $discount_data['gift_config'];

                            $gift_list = '';
                            foreach ($gift_config as $gift) {
                                $gift_list .= $gift['desc'] . '、';
                            }

                            $discount_text = '赠品:' . $gift_list;
                            break;
                    }

                    if ($discount_text !== $discount_data['name']) {
                        $name .= '<span class="badg c-blue">' . $discount_text . '</span>';
                    }
                }

                return $name;

                break;

            case 'priority':

                $priority = zib_shop_get_discount_config($id, 'priority', 50);
                return $priority . '.' . $id;
                break;

            case 'limit':
                $discount_data = zib_shop_get_discount_data($id);
                $limit         = '';
                if ($discount_data['price_limit']) {
                    $limit .= '<span class="badg c-yellow">满' . $discount_data['price_limit'] . '可用</span>';
                }
                if ($discount_data['user_limit']) {
                    $_array = [
                        'vip'   => 'VIP',
                        'vip_2' => 'VIP2',
                        'auth'  => '认证用户',
                    ];

                    $limit .= '<span class="badg c-yellow">' . $_array[$discount_data['user_limit']] . '可用</span>';
                }
                if ($discount_data['time_limit'] && !empty($discount_data['time_limit_config'])) {
                    $_start = str_replace(' 00:00:00', '', $discount_data['time_limit_config']['start'] ?? '');
                    $_end   = str_replace(' 23:59:59', '', $discount_data['time_limit_config']['end'] ?? '');

                    $start_h = $_start ? $_start . ($_end ? ' - ' : '开始') : '';
                    $end_h   = $_end ? $_end . ($_start ? '' : '结束') : '';
                    $limit .= '<span class="badg c-yellow">' . $start_h . $end_h . '</span>';
                }

                return $limit;
                break;
        }

        return $columns;
    }

    public function comments_columns($columns)
    {
        $columns['response_2'] = $columns['response'];
        //   unset($columns['response']);
        return $columns;
    }

    public function comments_custom_column($column, $comment_id)
    {
        switch ($column) {
            case 'response_2':
                $wp_list_table = new WP_Comments_List_Table();
                $wp_list_table->column_response($comment_id);

                break;
        }
    }

    public function manage_comments_comment_text($comment_text, $comment, $args)
    {

        $post_id   = $comment->comment_post_ID;
        $post_type = get_post_type($post_id);
        if ($post_type !== 'shop_product') {
            return $comment_text;
        }

        $comment_id = $comment->comment_ID;
        $score_data = zib_get_comment_meta($comment_id, 'score_data', true);

        if (!$comment_text) {
            $comment_text = '<span class="opacity8">' . (empty($score_data['auto_generated']) ? __('用户未填写评价内容', 'zibll') : '系统默认好评') . '</span>';
        }

        $score_average_badge = '';
        if ($score_data && !empty($score_data['average'])) {
            $class = $score_data['average'] >= 3.5 ? 'c-green' : 'c-red';
            $score_average_badge .= '<span class="badg badg-sm ' . $class . '">评分：' . $score_data['average'] . '</span>';
        }

        $order_data          = zib_get_comment_meta($comment_id, 'order_data', true);
        $options_active_name = $order_data['options_active_name'] ?? '';
        $score_average_badge .= $options_active_name ? '<span class="badg badg-sm ml6">' . $options_active_name . '</span>' : '';
        $score_average_badge = $score_average_badge ? '<div class="">' . $score_average_badge . '</div>' : '';

        //图片
        $score_image      = $score_data['img_ids'] ?? [];
        $score_image_html = '';
        if ($score_image) {
            $imgs_max   = 99;
            $imgs_count = 0;
            foreach ($score_image as $img_id) {
                $img_src = zib_get_attachment_image_src($img_id);
                if (!empty($img_src[0])) {
                    $imgs_count++;
                    if ($imgs_count > $imgs_max) {
                        continue;
                    }
                    $score_image_html .= '<span><img src="' . $img_src[0] . '" alt="评价图片"></span>';
                }
            }
            $score_image_html = '<div class="imgbox-container comment-score-imgs count-' . ($imgs_count <= $imgs_max ? $imgs_count : $imgs_max) . '">' . $score_image_html . '</div>';
        }

        return $comment_text . $score_image_html . $score_average_badge;
    }

    //后台评论表格
    public function manage_comments_nav($views)
    {
        add_filter('comment_text', array($this, 'manage_comments_comment_text'), 8, 3);
        return $views;
    }

}
