<?php
/*
 * Setup bridge for the Zibll shop module in the official open source release.
 * This file registers runtime hooks for site-owner configurable shop features.
 */
defined('ABSPATH') || exit;

if (!class_exists('zib_shop_setup')) {
    class zib_shop_setup {
        public $shop;

        public function __construct($shop) {
            $this->shop = is_object($shop) ? $shop : null;
            if (!$this->shop) {
                return;
            }

            $this->setup($this->shop);
            $this->tool();
            $this->ajax();
            $this->admin_action();
        }

        /**
         * Explicit open-source availability check. This is an explicit runtime status check and does not represent licensing.
         */
        public function is_enabled() {
            return is_object($this->shop) && !empty($this->shop->s);
        }

        /**
         * Register local maintenance hooks required by the shop module.
         */
        public function tool() {
            if (function_exists('add_action') && function_exists('zibll_ensure_order_schema')) {
                add_action('admin_init', 'zibll_ensure_order_schema');
                add_action('admin_head', 'zibll_ensure_order_schema');
            }
            return $this;
        }

        /**
         * Existing shop AJAX callbacks are loaded by inc/functions/shop/action/action.php.
         * This hook gives extensions a stable open-source registration point.
         */
        public function ajax() {
            if (function_exists('do_action')) {
                do_action('zib_shop_ajax_hooks_loaded', $this->shop);
            }
            return $this;
        }

        /**
         * Register admin-side compatibility actions that are independent of licensing.
         */
        public function admin_action() {
            if (function_exists('add_action') && is_admin()) {
                add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            }
            return $this;
        }

        public function admin_enqueue_scripts() {
            if (!function_exists('get_current_screen') || !defined('ZIB_SHOP_ASSETS_URI')) {
                return;
            }

            $screen = get_current_screen();
            if (empty($screen)) {
                return;
            }

            $post_types = array('shop_product');
            $taxonomies = array('shop_cat', 'shop_tag', 'shop_discount');
            $is_shop_screen = in_array($screen->post_type, $post_types, true) || in_array($screen->taxonomy, $taxonomies, true);
            if (!$is_shop_screen) {
                return;
            }

            $ver = defined('THEME_VERSION') ? THEME_VERSION : null;
            wp_enqueue_style('zibll-shop-admin', ZIB_SHOP_ASSETS_URI . '/css/main.min.css', array(), $ver, 'all');
            wp_enqueue_script('zibll-shop-admin', ZIB_SHOP_ASSETS_URI . '/js/main.min.js', array('jquery'), $ver, true);
        }

        public function setup($shop) {
            if (!is_object($shop)) {
                return $shop;
            }

            if (empty($shop->cart_rewrite_slug)) {
                $shop->cart_rewrite_slug = function_exists('_pz') ? (_pz('shop_cart_rewrite_slug') ?: 'cart') : 'cart';
            }
            if (empty($shop->product_rewrite_slug)) {
                $shop->product_rewrite_slug = function_exists('_pz') ? (_pz('shop_product_rewrite_slug') ?: 'shop') : 'shop';
            }
            if ($shop->rewrite_suffix === '' && function_exists('_pz')) {
                $shop->rewrite_suffix = _pz('shop_rewrite_suffix_html_s', true) ? '.html' : '';
            }
            $shop->is_admin_can = function_exists('is_super_admin') ? is_super_admin() : false;

            add_action('init', array($shop, 'register_post_type'), 0);
            add_action('init', array($shop, 'register_post_statuses'), 1);
            add_action('init', array($shop, 'add_rewrite_rule'), 20);
            add_filter('query_vars', array($shop, 'query_vars'));
            add_filter('post_type_link', array($shop, 'post_type_link'), 10, 2);
            add_filter('redirect_canonical', array($shop, 'redirect_canonical'));
            add_action('template_redirect', array($shop, 'template_redirect'));

            add_action('pre_get_posts', array($shop, 'main_post_query'));
            add_action('pre_get_posts', array($shop, 'admin_post_query'), 999);

            add_action('saved_term', array($shop, 'initialization_term_meta'), 10, 4);
            add_action('save_post', array($shop, 'initialization_posts_meta'), 10, 3);

            add_action('admin_menu', array($shop, 'admin_menu_separator'));
            add_filter('manage_shop_product_posts_columns', array($shop, 'product_columns'));
            add_action('manage_shop_product_posts_custom_column', array($shop, 'product_custom_column'), 10, 2);

            add_filter('manage_edit-shop_cat_columns', array($shop, 'cat_columns'));
            add_filter('manage_shop_cat_custom_column', array($shop, 'cat_custom_column'), 10, 3);
            add_filter('manage_edit-shop_tag_columns', array($shop, 'tag_columns'));
            add_filter('manage_shop_tag_custom_column', array($shop, 'tag_custom_column'), 10, 3);
            add_filter('manage_edit-shop_discount_columns', array($shop, 'discount_columns'));
            add_filter('manage_shop_discount_custom_column', array($shop, 'discount_custom_column'), 10, 3);

            add_filter('comments_list_table_query_args', array($shop, 'comments_list_table_query_args'));
            add_filter('manage_edit-comments_columns', array($shop, 'comments_columns'));
            add_action('manage_comments_custom_column', array($shop, 'comments_custom_column'), 10, 2);
            add_filter('views_edit-comments', array($shop, 'manage_comments_nav'));

            add_filter('manage_users_columns', array($shop, 'users_columns'), 11);
            add_filter('manage_users_custom_column', array($shop, 'users_custom_column'), 10, 3);
            return $shop;
        }
    }
}
