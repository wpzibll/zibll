<?php
defined('ABSPATH') || exit;

if (!function_exists('zibpay_admin_page_vue_data_filter')) {
    function zibpay_admin_page_vue_data_filter($vue_data)
    {
        add_filter('admin_shop_page_vue_data', function ($__vue_data) use ($vue_data) {
            return array_merge(is_array($__vue_data) ? $__vue_data : array(), is_array($vue_data) ? $vue_data : array());
        });
    }
}

if (!function_exists('zibpay_admin_asset_url')) {
    function zibpay_admin_asset_url($path)
    {
        return get_template_directory_uri() . '/zibpay/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('zibpay_admin_print_asset_tags')) {
    function zibpay_admin_print_asset_tags($vue_page = false)
    {
        static $printed = array();
        $key = $vue_page ? 'vue' : 'charts';
        if (!empty($printed[$key])) {
            return;
        }
        $printed[$key] = true;
        $ver = defined('THEME_VERSION') ? THEME_VERSION : time();
        $css = array(
            'css/pay-page.css',
        );
        if ($vue_page) {
            $css[] = 'css/element-plus.min.css';
            $css[] = 'css/main.css';
        }
        foreach ($css as $file) {
            echo '<link rel="stylesheet" href="' . esc_url(zibpay_admin_asset_url($file) . '?ver=' . rawurlencode($ver)) . '" />' . "\n";
        }
        $scripts = array(
            'js/echarts-c.min.js',
            'js/westeros.min.js',
        );
        if ($vue_page) {
            $scripts = array_merge(array(
                'js/vue.global.min.js',
                'js/vue-router.global.min.js',
                'js/element-plus.min.js',
                'js/element-plus-zh-cn.min.js',
            ), $scripts, array(
                'js/vue-echarts.min.js',
            ));
        }
        foreach ($scripts as $file) {
            echo '<script src="' . esc_url(zibpay_admin_asset_url($file) . '?ver=' . rawurlencode($ver)) . '"></script>' . "\n";
        }
    }
}

if (!function_exists('zibpay_admin_page_start')) {
    function zibpay_admin_page_start()
    {
        zibpay_admin_print_asset_tags(true);
        echo '<div class="wrap pay-admin-page">';
    }
}

if (!function_exists('zibpay_admin_page_vue_data_admin_footer')) {
    function zibpay_admin_page_vue_data_admin_footer()
    {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        $vue_pages = array('zibpay_page', 'pay');
        $legacy_pages = array(
            'zibpay_order_page',
            'zibpay_product_page',
            'zibpay_income_page',
            'zibpay_rebate_page',
            'zibpay_withdraw',
            'zibpay_charge_card_page',
            'zibpay_coupon_page',
            'zibpay_statistics_page',
        );
        if (!in_array($page, array_merge($vue_pages, $legacy_pages), true)) {
            return;
        }
        $data = apply_filters('admin_shop_page_vue_data', array());
        if (!is_array($data)) {
            $data = array();
        }

        $runtime_defaults = array(
            'ajax_url'                   => admin_url('admin-ajax.php'),
            '_wpnonce'                   => wp_create_nonce('admin_order_page_ajax_submit'),
            'admin_payment_submit_nonce' => wp_create_nonce('admin_payment_submit'),
            'admin_url'                  => admin_url(),
        );
        $data = array_merge($data, $runtime_defaults);
        if (empty($data['ajax_url'])) { $data['ajax_url'] = admin_url('admin-ajax.php'); }
        if (empty($data['_wpnonce'])) { $data['_wpnonce'] = wp_create_nonce('admin_order_page_ajax_submit'); }
        if (empty($data['admin_payment_submit_nonce'])) { $data['admin_payment_submit_nonce'] = wp_create_nonce('admin_payment_submit'); }

        $ver = defined('THEME_VERSION') ? THEME_VERSION : time();
        echo "\n<script>"
            . "window.ajaxurl = window.ajaxurl || " . wp_json_encode(admin_url('admin-ajax.php')) . ";"
            . "window._vue_data = " . wp_json_encode($data, JSON_UNESCAPED_UNICODE) . " || {};"
            . "window._vue_data.ajax_url = window._vue_data.ajax_url || window.ajaxurl;"
            . "window._vue_data._wpnonce = window._vue_data._wpnonce || " . wp_json_encode(wp_create_nonce('admin_order_page_ajax_submit')) . ";"
            . "</script>\n";
        if (in_array($page, $vue_pages, true)) {
            echo '<script src="' . esc_url(zibpay_admin_asset_url('js/admin-page.min.js') . '?ver=' . rawurlencode($ver)) . '"></script>' . "\n";
            echo '</div>';
        }
    }
}

if (!function_exists('zibpay_admin_page_local_randstr')) {
    function zibpay_admin_page_local_randstr($data, $randstr)
    {
        return is_string($data) && is_string($randstr) && strpos($data, $randstr) !== false;
    }
}

if (!function_exists('zibpay_include_admin_page_file')) {
    function zibpay_include_admin_page_file($relative)
    {
        $file = get_theme_file_path('/zibpay/page/' . ltrim($relative, '/'));
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<div class="wrap"><h1>商城中心</h1><p>页面文件不存在：' . esc_html($relative) . '</p></div>';
        }
    }
}

if (!function_exists('zibpay_shop_admin_page')) {
    function zibpay_shop_admin_page()
    {
        zibpay_include_admin_page_file('shop.php');
    }
}

if (!function_exists('zibpay_page')) {
    function zibpay_page()
    {
        zibpay_shop_admin_page();
    }
}

if (!function_exists('zibpay_statistics_page')) {
    function zibpay_statistics_page()
    {
        zibpay_admin_print_asset_tags(false);
        echo '<div class="wrap pay-dashboard-widget">';
        zibpay_include_admin_page_file('index.php');
        echo '</div>';
    }
}

if (!function_exists('zibpay_income_page')) { function zibpay_income_page() { zibpay_include_admin_page_file('income.php'); } }
if (!function_exists('zibpay_order_page')) { function zibpay_order_page() { zibpay_include_admin_page_file('order.php'); } }
if (!function_exists('zibpay_product_page')) { function zibpay_product_page() { zibpay_include_admin_page_file('product.php'); } }
if (!function_exists('zibpay_rebate_page')) { function zibpay_rebate_page() { zibpay_include_admin_page_file('rebate.php'); } }
if (!function_exists('zibpay_withdraw_page')) { function zibpay_withdraw_page() { zibpay_include_admin_page_file('withdraw.php'); } }
if (!function_exists('zibpay_charge_card_page')) { function zibpay_charge_card_page() { zibpay_include_admin_page_file('charge-card.php'); } }
if (!function_exists('zibpay_coupon_page')) { function zibpay_coupon_page() { zibpay_include_admin_page_file('coupon.php'); } }

if (!function_exists('zibpay_add_settings_menu')) {
    function zibpay_add_settings_menu()
    {
        if (!function_exists('add_menu_page')) {
            return;
        }
        add_menu_page('商城中心', 'zibll商城中心', 'manage_options', 'zibpay_page', 'zibpay_shop_admin_page', 'dashicons-cart', 58);
        add_submenu_page('zibpay_page', '商城中心', '商城中心', 'manage_options', 'zibpay_page', 'zibpay_shop_admin_page');
        add_submenu_page('zibpay_page', '商品管理', '商品管理', 'manage_options', 'edit.php?post_type=shop_product');
        add_submenu_page('zibpay_page', '订单明细', '订单明细', 'manage_options', 'zibpay_order_page', 'zibpay_order_page');
        add_submenu_page('zibpay_page', '商品明细', '商品明细', 'manage_options', 'zibpay_product_page', 'zibpay_product_page');
        add_submenu_page('zibpay_page', '分成明细', '分成明细', 'manage_options', 'zibpay_income_page', 'zibpay_income_page');
        add_submenu_page('zibpay_page', '佣金明细', '佣金明细', 'manage_options', 'zibpay_rebate_page', 'zibpay_rebate_page');
        add_submenu_page('zibpay_page', '提现管理', '提现管理', 'manage_options', 'zibpay_withdraw', 'zibpay_withdraw_page');
        add_submenu_page('zibpay_page', '卡密管理', '卡密管理', 'manage_options', 'zibpay_charge_card_page', 'zibpay_charge_card_page');
        add_submenu_page('zibpay_page', '优惠码管理', '优惠码管理', 'manage_options', 'zibpay_coupon_page', 'zibpay_coupon_page');
        add_submenu_page('zibpay_page', '数据统计', '数据统计', 'manage_options', 'zibpay_statistics_page', 'zibpay_statistics_page');
    }
}

if (function_exists('add_action')) {
    add_action('admin_menu', 'zibpay_add_settings_menu', 20);
    add_action('admin_footer', 'zibpay_admin_page_vue_data_admin_footer', 99);
}
