<?php
defined('ABSPATH') || exit;
if (!function_exists('zib_shop_get_order_type')) { function zib_shop_get_order_type() { return '10'; } }
if (!function_exists('zib_shop_get_home_url')) { function zib_shop_get_home_url() { return function_exists('home_url') ? home_url() : ''; } }

if (!function_exists('zib_shop_table_has_column')) {
    function zib_shop_table_has_column($table, $column) {
        global $wpdb;
        if (empty($wpdb) || !$table || !$column) { return false; }
        $table_safe = str_replace('`', '', $table);
        return (bool) $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table_safe}` LIKE %s", $column));
    }
}

if (!function_exists('zib_shop_status_count_sql')) {
    function zib_shop_status_count_sql($column, $status, $user_id = 0) {
        global $wpdb;
        if (empty($wpdb)) { return 0; }
        $table = !empty($wpdb->pay_order) ? $wpdb->pay_order : $wpdb->prefix . 'pay_order';
        if (!$table || !zib_shop_table_has_column($table, $column)) { return 0; }
        $table_safe = str_replace('`', '', $table);
        $where = array($wpdb->prepare('order_type=%s', zib_shop_get_order_type()));
        if (is_array($status)) {
            $status = array_values(array_filter(array_map('intval', $status), function($v){ return $v >= 0; }));
            if (!$status) { return 0; }
            $where[] = '`' . $column . '` IN (' . implode(',', $status) . ')';
        } else {
            $where[] = $wpdb->prepare('`' . $column . '`=%d', (int) $status);
        }
        if ($user_id) { $where[] = $wpdb->prepare('user_id=%d', (int) $user_id); }
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$table_safe}` WHERE " . implode(' AND ', $where));
    }
}

if (!function_exists('zib_shop_get_shipping_status_count')) {
    function zib_shop_get_shipping_status_count($status, $user_id = 0) {
        return zib_shop_status_count_sql('shipping_status', $status, $user_id);
    }
}
if (!function_exists('zib_shop_get_after_sale_status_count')) {
    function zib_shop_get_after_sale_status_count($status, $user_id = 0) {
        return zib_shop_status_count_sql('after_sale_status', $status, $user_id);
    }
}
if (!function_exists('zib_shop_get_express_companies_data')) { function zib_shop_get_express_companies_data() { return array('sf'=>'顺丰速运','sto'=>'申通快递','yt'=>'圆通速递','yd'=>'韵达快递','zto'=>'中通快递','ems'=>'EMS','jd'=>'京东物流','other'=>'其他'); } }
