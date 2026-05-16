<?php
defined('ABSPATH') || exit;

if (!function_exists('zibll_table_exists')) {
    function zibll_table_exists($table) {
        global $wpdb;
        if (empty($wpdb) || !$table) {
            return false;
        }
        return (bool) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    }
}

if (!function_exists('zibll_table_has_column')) {
    function zibll_table_has_column($table, $column) {
        global $wpdb;
        if (empty($wpdb) || !$table || !$column) {
            return false;
        }
        $table_safe = str_replace('`', '', $table);
        return (bool) $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table_safe}` LIKE %s", $column));
    }
}

if (!function_exists('zibll_ensure_order_schema')) {
    function zibll_ensure_order_schema() {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }
        $table = !empty($wpdb->pay_order) ? $wpdb->pay_order : $wpdb->prefix . 'pay_order';
        if (!$table || !zibll_table_exists($table)) {
            return;
        }
        $table_safe = str_replace('`', '', $table);
        $columns = array(
            'shipping_status' => "TINYINT(1) NOT NULL DEFAULT 0",
            'after_sale_status' => "TINYINT(1) NOT NULL DEFAULT 0",
        );
        foreach ($columns as $column => $definition) {
            if (!zibll_table_has_column($table_safe, $column)) {
                $wpdb->query("ALTER TABLE `{$table_safe}` ADD COLUMN `{$column}` {$definition}");
            }
        }
    }
}

if (!function_exists('zibll_result_to_array')) {
    function zibll_result_to_array($value) {
        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
        } elseif (is_object($value) && method_exists($value, 'result')) {
            $value = $value->result();
        }
        if (empty($value)) {
            return array();
        }
        if (is_object($value)) {
            return (array) $value;
        }
        if (is_array($value)) {
            $out = array();
            foreach ($value as $k => $v) {
                $out[$k] = is_object($v) ? (array) $v : $v;
            }
            return $out;
        }
        return array();
    }
}

if (!function_exists('zibll_flatten_prepare_values')) {
    function zibll_flatten_prepare_values($values) {
        $flat = array();
        foreach ((array) $values as $v) {
            if (is_array($v)) {
                foreach ($v as $vv) {
                    if (!is_array($vv) && !is_object($vv)) {
                        $flat[] = $vv;
                    }
                }
            } elseif (!is_object($v)) {
                $flat[] = $v;
            }
        }
        return $flat;
    }
}

if (!class_exists('zib_db')) {
    $db_class_file = trailingslashit(get_template_directory()) . 'inc/class/db-class.php';
    if (file_exists($db_class_file)) {
        require_once $db_class_file;
    }
}

if (!class_exists('ZibDB')) {
    class ZibDB {
        protected static function new_db() {
            if (class_exists('zib_db')) {
                return new zib_db();
            }
            return null;
        }

        public static function is_exists() {
            global $wpdb;
            return !empty($wpdb);
        }

        public static function name($name) {
            $db = self::new_db();
            if (!$db) {
                return new self();
            }
            return $db->name($name);
        }

        public static function table($table, $alias = null) {
            $db = self::new_db();
            if (!$db) {
                return new self();
            }
            return $db->table($table, $alias);
        }

        public static function prefix($prefix) {
            $db = self::new_db();
            if (!$db) {
                return new self();
            }
            return $db->prefix($prefix);
        }

        public function __call($name, $arguments) {
            return $this;
        }

        public function find($where = null) {
            return $this;
        }

        public function select($where = null) {
            return $this;
        }

        public function toArray() {
            return array();
        }

        public function toArrayMap($callback) {
            return array();
        }

        public function count($where = null) {
            return 0;
        }

        public function result() {
            return null;
        }
    }
}

if (!function_exists('zib_shop_get_order_type')) {
    function zib_shop_get_order_type() {
        return '10';
    }
}

if (!function_exists('zib_shop_get_home_url')) {
    function zib_shop_get_home_url() {
        return function_exists('home_url') ? home_url() : '';
    }
}

if (!function_exists('zib_shop_table_has_column')) {
    function zib_shop_table_has_column($table, $column) {
        global $wpdb;
        if (empty($wpdb) || !$table || !$column) {
            return false;
        }
        $table_safe = str_replace('`', '', $table);
        return (bool) $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table_safe}` LIKE %s", $column));
    }
}

if (!function_exists('zib_shop_status_count_sql')) {
    function zib_shop_status_count_sql($column, $status, $user_id = 0) {
        global $wpdb;
        if (empty($wpdb)) {
            return 0;
        }
        $table = !empty($wpdb->pay_order) ? $wpdb->pay_order : $wpdb->prefix . 'pay_order';
        if (!$table || !zib_shop_table_has_column($table, $column)) {
            return 0;
        }
        $table_safe = str_replace('`', '', $table);
        $where = array($wpdb->prepare('order_type=%s', zib_shop_get_order_type()));
        if (is_array($status)) {
            $status = array_values(array_filter(array_map('intval', $status), function($v) {
                return $v >= 0;
            }));
            if (!$status) {
                return 0;
            }
            $where[] = '`' . $column . '` IN (' . implode(',', $status) . ')';
        } else {
            $where[] = $wpdb->prepare('`' . $column . '`=%d', (int) $status);
        }
        if ($user_id) {
            $where[] = $wpdb->prepare('user_id=%d', (int) $user_id);
        }
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

if (!function_exists('zib_shop_get_express_companies_data')) {
    function zib_shop_get_express_companies_data() {
        return array('sf' => '顺丰速运', 'sto' => '申通快递', 'yt' => '圆通速递', 'yd' => '韵达快递', 'zto' => '中通快递', 'ems' => 'EMS', 'jd' => '京东物流', 'other' => '其他');
    }
}


if (!function_exists('zib_str_split')) {
    /**
     * Unicode-safe string splitter used by title animation widgets.
     */
    function zib_str_split($string) {
        if (!is_string($string) || $string === '') {
            return array();
        }

        if (function_exists('mb_str_split')) {
            return mb_str_split($string);
        }

        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($chars)) {
            return $chars;
        }

        return str_split($string);
    }
}

if (!function_exists('zibll_is_open_source_edition')) {
    /**
     * Marker helper for extensions that need to detect the official open source edition.
     */
    function zibll_is_open_source_edition() {
        return defined('ZIBLL_OPEN_SOURCE_EDITION') && ZIBLL_OPEN_SOURCE_EDITION;
    }
}
