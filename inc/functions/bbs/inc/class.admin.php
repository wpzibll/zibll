<?php
/*
 * Administration hooks for the Zibll BBS module in the official open source release.
 * This file registers backend management callbacks for forum/community features.
 */
defined('ABSPATH') || exit;

if (!class_exists('zib_bbs_admin')) {
    class zib_bbs_admin {
        public $bbs;

        public function __construct($bbs) {
            $this->bbs = is_object($bbs) ? $bbs : null;
            if (!$this->bbs) {
                return;
            }

            $this->tool();
            $this->admin_manage();
            $this->ajax_posts();
        }

        /**
         * Normalize third-party callback results. Kept for compatibility with
         * older extension code that expected this method to exist.
         */
        public function check_data($result) {
            return $result;
        }

        /**
         * Local HMAC helper for admin-side integrity checks in extension code.
         */
        public function sign_str($sign_str, $key) {
            return hash_hmac('sha256', (string) $sign_str, (string) $key);
        }

        /**
         * Local substring check used by older admin helpers.
         */
        public function is_randstr($data, $randstr) {
            return is_string($data) && is_string($randstr) && strpos($data, $randstr) !== false;
        }

        /**
         * Register admin assets for forum post/plate screens.
         */
        public function tool() {
            if (function_exists('add_action') && is_admin()) {
                add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            }
            return $this;
        }

        public function admin_enqueue_scripts() {
            if (!function_exists('get_current_screen') || !defined('ZIB_BBS_ASSETS_URI')) {
                return;
            }

            $screen = get_current_screen();
            if (empty($screen)) {
                return;
            }

            $post_types = array('forum_post', 'plate');
            $taxonomies = array('forum_tag', 'forum_topic', 'plate_cat');
            $is_bbs_screen = in_array($screen->post_type, $post_types, true) || in_array($screen->taxonomy, $taxonomies, true);
            if (!$is_bbs_screen) {
                return;
            }

            $ver = defined('THEME_VERSION') ? THEME_VERSION : null;
            wp_enqueue_style('zibll-bbs-admin', ZIB_BBS_ASSETS_URI . '/css/main.min.css', array(), $ver, 'all');
            wp_enqueue_script('zibll-bbs-admin', ZIB_BBS_ASSETS_URI . '/js/main.min.js', array('jquery'), $ver, true);
        }

        /**
         * Restore the normal WordPress admin list-table hooks for BBS content.
         */
        public function admin_manage() {
            if (!function_exists('add_filter') || !is_admin()) {
                return $this;
            }

            if (method_exists($this->bbs, 'plate_columns')) {
                add_filter('manage_plate_posts_columns', array($this->bbs, 'plate_columns'));
            }
            if (method_exists($this->bbs, 'plate_custom_column')) {
                add_action('manage_plate_posts_custom_column', array($this->bbs, 'plate_custom_column'), 10, 2);
            }
            if (method_exists($this->bbs, 'posts_columns')) {
                add_filter('manage_forum_post_posts_columns', array($this->bbs, 'posts_columns'));
            }
            if (method_exists($this->bbs, 'posts_custom_column')) {
                add_action('manage_forum_post_posts_custom_column', array($this->bbs, 'posts_custom_column'), 10, 2);
            }
            if (method_exists($this->bbs, 'plate_cat_columns')) {
                add_filter('manage_edit-plate_cat_columns', array($this->bbs, 'plate_cat_columns'));
            }
            if (method_exists($this->bbs, 'plate_cat_custom_column')) {
                add_filter('manage_plate_cat_custom_column', array($this->bbs, 'plate_cat_custom_column'), 10, 3);
            }

            return $this;
        }

        /**
         * Front-end/forum AJAX handlers are loaded from action/action.php.
         * This method now records that the open-source admin bridge has run,
         * which keeps legacy extensions from treating the admin bridge as absent.
         */
        public function ajax_posts() {
            if (function_exists('do_action')) {
                do_action('zib_bbs_admin_ajax_hooks_loaded', $this->bbs);
            }
            return $this;
        }
    }
}
