<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-05-05 22:28:46
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

global $wpdb;
$wpdb->zibpay_order      = $wpdb->prefix . 'zibpay_order';
$wpdb->zibpay_order_meta = $wpdb->prefix . 'zibpay_ordermeta';
$wpdb->zibpay_payment    = $wpdb->prefix . 'zibpay_payment'; //付款记录

/**
 * @description: 支付的订单系统
 * @param {*}
 * @return {*}
 */
class ZibPay
{
    //订单号前缀
    public static $payment_order_num_prefix = '520';
    public static $order_table_name         = 'zibpay_order';
    public static $order_meta_table_name    = 'zibpay_ordermeta';
    public static $payment_table_name       = 'zibpay_payment';

    /**
     * @description: 创建数据库
     * @param {*}
     * @return {*}
     */
    public static function create_db()
    {
        global $wpdb;
        /**判断没有则创建 */
        if (!$wpdb->get_var("show tables like '{$wpdb->zibpay_order}'")) {
            $wpdb->query("CREATE TABLE `$wpdb->zibpay_order` (
                `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) DEFAULT NULL COMMENT '用户id',
                `ip_address` varchar(50) DEFAULT NULL COMMENT 'ip地址',
                `product_id` varchar(50) DEFAULT NULL COMMENT '产品id',
                `post_id` BIGINT(20) DEFAULT NULL COMMENT '文章id',
                `post_author` BIGINT(20) DEFAULT NULL COMMENT '文章作者',
                `count` BIGINT(20) DEFAULT 1 COMMENT '数量',
                `order_num` varchar(50) DEFAULT NULL COMMENT '订单号',
                `order_price` double(10, 2) DEFAULT '0.00' COMMENT '订单价格',
                `order_type` varchar(50) DEFAULT '0' COMMENT '订单类型',
                `create_time` datetime DEFAULT NULL COMMENT '创建时间',
                `payment_id` BIGINT(20) DEFAULT NULL COMMENT '支付ID',
                `pay_num` varchar(50) DEFAULT NULL COMMENT '支付订单号',
                `pay_type` varchar(50) DEFAULT '0' COMMENT '支付类型',
                `pay_price` double(10, 2) DEFAULT NULL COMMENT '支付金额',
                `pay_detail` longtext COMMENT '支付详情',
                `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
                `referrer_id` BIGINT(20) DEFAULT NULL COMMENT '推荐人id',
                `rebate_price` double(10, 2) DEFAULT '0.00' COMMENT '推荐佣金',
                `rebate_status` varchar(50) DEFAULT '0' COMMENT '佣金提现状态',
                `rebate_detail` longtext COMMENT '佣金提现详情',
                `income_price` double(10, 2) DEFAULT '0.00' COMMENT '作者分成',
                `income_status` varchar(50) DEFAULT '0' COMMENT '分成状态',
                `income_detail` longtext COMMENT '分成详情',
                `status` varchar(50) DEFAULT '0' COMMENT '订单状态',
                `other` longtext COMMENT '其它',
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `post_id` (`post_id`),
                KEY `count` (`count`),
                KEY `order_num` (`order_num`),
                KEY `status` (`status`),
                KEY `order_type` (`order_type`),
                KEY `payment_id` (`payment_id`),
                KEY `pay_time` (
                    `pay_time`,
                    `pay_type`,
                    `pay_price`,
                    `id`
                ),
                KEY `post_author` (
                    `post_author`,
                    `referrer_id`,
                    `income_status`,
                    `rebate_status`,
                    `id`
                )
            ) ENGINE = InnoDB DEFAULT CHARSET = " . DB_CHARSET . " COMMENT = '订单明细'");
        } else {
            if (!$wpdb->get_row("SELECT column_name FROM information_schema.columns WHERE table_name='$wpdb->zibpay_order' and column_name ='post_author'")) {
                // 判断数据库推荐返利功能字段，无则添加
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD post_author BIGINT(20) DEFAULT '0' COMMENT '文章作者'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD income_price double(10,2) DEFAULT '0.00' COMMENT '作者分成'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD income_status varchar(50) DEFAULT '0' COMMENT '分成状态'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD income_detail longtext DEFAULT NULL COMMENT '分成详情'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD pay_detail longtext DEFAULT NULL COMMENT '收款详情'");

                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD referrer_id BIGINT(20) DEFAULT NULL COMMENT '推荐人id'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD rebate_price double(10,2) DEFAULT NULL COMMENT '返利金额'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD rebate_status varchar(255) DEFAULT '0' COMMENT '提现状态'");
                @$wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD rebate_detail varchar(2550) DEFAULT NULL COMMENT '提现详情'");
            }

            if (version_compare(THEME_VERSION, '7.0.5', '<=') && !$wpdb->get_row("show index from $wpdb->zibpay_order WHERE Key_name = 'user_id'")) {
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`user_id`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`post_id`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`order_num`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`status`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`order_type`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`pay_time`,`pay_type`,`pay_price`,`id`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`post_author`,`referrer_id`,`income_status`,`rebate_status`,`id`)"); //添加索引
            }

            if (!$wpdb->get_row("SELECT column_name FROM information_schema.columns WHERE table_name='$wpdb->zibpay_order' and column_name ='payment_id'")) {
                //8.2.0 新增支付ID和数量
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD `payment_id` BIGINT(20) DEFAULT NULL COMMENT '支付ID'");
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD `count` BIGINT(20) DEFAULT 1 COMMENT '数量'");
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`payment_id`)"); //添加索引
                $wpdb->query("ALTER TABLE $wpdb->zibpay_order ADD INDEX(`count`)"); //添加索引
            }
        }

        //V8.2 新增支付ID
        //创建订单元数据表
        if (!$wpdb->get_var("show tables like '{$wpdb->zibpay_order_meta}'")) {
            $wpdb->query("CREATE TABLE `$wpdb->zibpay_order_meta` (
                `meta_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `order_id` bigint(20) NOT NULL DEFAULT 0,
                `meta_key` varchar(255) DEFAULT NULL,
                `meta_value` longtext,
                PRIMARY KEY (`meta_id`),
                KEY `order_id` (`order_id`),
                KEY `meta_key` (`meta_key`)
            ) ENGINE = InnoDB DEFAULT CHARSET = " . DB_CHARSET . " COMMENT = '订单元数据'");
        }

        //创建联合付款数据表
        if (!$wpdb->get_var("show tables like '{$wpdb->zibpay_payment}'")) {
            $wpdb->query("CREATE TABLE `$wpdb->zibpay_payment` (
                `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `method` varchar(255) DEFAULT NULL,
                `price` double(10, 2) DEFAULT '0.00',
                `order_num` varchar(255) DEFAULT NULL,
                `status` varchar(255) DEFAULT '0',
                `create_time` datetime DEFAULT NULL,
                `pay_time` datetime DEFAULT NULL,
                `pay_num` varchar(255) DEFAULT NULL,
                `order_data` longtext DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `status` (`status`),
                KEY `order_num` (`order_num`),
                KEY `method` (`method`),
                KEY `pay_time` (`pay_time`,`pay_num`,`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = " . DB_CHARSET . " COMMENT = '联合付款'");
        }
    }

    //添加付款记录
    /**
     * @param array $values 付款记录数据 [method=>'',price=>''] 必传的值：method,price
     * @return int 付款记录ID
     */
    public static function add_payment(array $values)
    {

        $data = array(
            'method'      => $values['method'] ?? '',
            'price'       => $values['price'] ?? 0,
            'status'      => 0,
            'order_num'   => self::generate_payment_order_num(), // 订单号，
            'create_time' => current_time('mysql'),
            'order_data'  => isset($values['order_data']) ? maybe_serialize($values['order_data']) : '',
        );
        $data      = wp_unslash($data);
        $insert_id = ZibDB::name(self::$payment_table_name)->insertGetId($data);
        if ($insert_id) {
            $data['id'] = $insert_id;
            return $data;
        }

        return false;
    }

    //更新付款记录
    public static function update_payment(array $values)
    {
        if (empty($values['id'])) {
            return false;
        }

        if (isset($values['order_data'])) {
            $values['order_data'] = maybe_serialize($values['order_data']);
        }

        if (isset($values['create_time'])) {
            unset($values['create_time']);
        }

        $values = wp_unslash($values);

        return ZibDB::name(self::$payment_table_name)->where((int) $values['id'])->update($values);
    }

    //获取支付数据
    public static function get_payment($id_or_order_num)
    {
        if (self::is_payment_order($id_or_order_num) && strlen($id_or_order_num) === strlen(self::generate_payment_order_num())) {
            $where = ['order_num' => $id_or_order_num];
        } else {
            $where = ['id' => $id_or_order_num];
        }

        $data = ZibDB::name(self::$payment_table_name)->where($where)->find()->toArray();

        if ($data) {
            $data['order_data'] = maybe_unserialize($data['order_data']);
        }
        return $data;
    }

    //根据支付ID获取订单数据
    public static function get_order_by_payment_id($payment_id, $field = '*')
    {

        $data = ZibDB::name(self::$order_table_name)->where(['payment_id' => $payment_id])->field($field)->select()->toArrayMap([self::class, 'order_data_map']);
        return $data;
    }

    //获取单个订单数据
    public static function get_order($id_or_order_num, $field = '*')
    {
        if (!$id_or_order_num) {
            return false;
        }

        //静态变量
        static $order_data = [];
        $cache_key         = $id_or_order_num . '_' . $field;
        if (isset($order_data[$cache_key])) {
            return $order_data[$cache_key];
        }

        if (intval($id_or_order_num) > 2000000000000000000) {
            $where = [
                'relation' => 'or',
                ['order_num', '=', $id_or_order_num],
                ['id', '=', $id_or_order_num],
            ];
        } else {
            $where = ['id' => $id_or_order_num];
        }

        $field_str = $field;
        if ($field == 'all') {
            $field_str = '*';
        }

        $data = self::order_data_map(ZibDB::name(self::$order_table_name)->where($where)->field($field_str)->find()->toArray());
        if ($data && $field == 'all') {
            $data['meta'] = self::get_all_meta($data['id']);
        }

        $order_data[$cache_key] = $data;
        return $data;
    }

    /**
     * @description: 根据Array数据获取1条订单数据
     * @param array $where 例如：array('id' => '10');
     * @return {*}
     */
    public static function get_row($where)
    {

        $data = self::order_data_map(ZibDB::name(self::$order_table_name)->where($where)->find()->toArray());

        return $data;
    }

    public static function order_data_map($data)
    {
        if (!$data) {
            return [];
        }

        $data = (array) $data;
        foreach (['income_detail', 'pay_detail', 'rebate_detail', 'other'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = maybe_unserialize($data[$key]);
            }
        }

        return $data;
    }

    //生成一个payment订单号
    public static function generate_payment_order_num()
    {
        return self::$payment_order_num_prefix . current_time('ymdHis') . mt_rand(10, 99) . mt_rand(10, 99);
    }

    //获取单个元数据
    public static function get_meta($order_id, $meta_key = '', $default = '')
    {
        //注意：不能使用静态变量
        return zib_get_array_value(self::get_all_meta($order_id), $meta_key, $default);
    }

    //获取所有元数据
    public static function get_all_meta($order_id)
    {
        global $wpdb;

        //缓存
        $cache = wp_cache_get($order_id, 'zibpay_ordermeta');
        if ($cache !== false) {
            return $cache;
        }

        $meta_value_db = zibDB::name(self::$order_meta_table_name)->where('order_id', $order_id)->field('meta_value,meta_key')->select()->result();

        $meta_value = [];
        if ($meta_value_db) {
            foreach ($meta_value_db as $meta) {
                $meta_value[$meta->meta_key] = maybe_unserialize($meta->meta_value);
            }
        }

        wp_cache_set($order_id, $meta_value, 'zibpay_ordermeta');
        return $meta_value;
    }

    //保存元数据
    public static function update_meta($order_id, $meta_key, $meta_value)
    {
        global $wpdb;

        if (is_array($order_id) && !empty($order_id['id'])) {
            $order_id = $order_id['id'];
        } elseif (is_object($order_id) && !empty($order_id->id)) {
            $order_id = $order_id->id;
        }

        if (!$order_id) {
            return false;
        }

        $meta_value = maybe_serialize(wp_unslash($meta_value));

        //先判断$meta_key是否存在
        if ($wpdb->get_var("SELECT 1 FROM $wpdb->zibpay_order_meta WHERE order_id = $order_id AND meta_key = '$meta_key'") !== null) {
            $wpdb->update($wpdb->zibpay_order_meta, array('meta_value' => $meta_value), array('order_id' => $order_id, 'meta_key' => $meta_key));
            do_action('update_order_meta', $order_id, $meta_key, $meta_value);
        } else {
            $wpdb->insert($wpdb->zibpay_order_meta, array('order_id' => $order_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value));
            do_action('add_order_meta', $order_id, $meta_key, $meta_value);
        }

        do_action('save_order_meta', $order_id, $meta_key, $meta_value);
        wp_cache_delete($order_id, 'zibpay_ordermeta');
    }

    //删除元数据
    public static function delete_meta($order_id, $meta_key)
    {
        global $wpdb;
        $wpdb->delete($wpdb->zibpay_order_meta, array('order_id' => $order_id, 'meta_key' => $meta_key));
        do_action('delete_order_meta', $order_id, $meta_key);
        do_action('update_order_meta', $order_id, $meta_key, null);
        wp_cache_delete($order_id, 'zibpay_ordermeta');
    }

    /**
     * @description: 获取用户IP地址
     * @param {*}
     * @return {*}
     */
    public static function get_ip()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
    }

    /**
     * @description: 删除订单
     * @param int $order_num 订单号
     * @param int $id 订单ID
     * @return
     */
    public static function delete_order($order_id)
    {
        $order_id = (int) $order_id;
        if (!$order_id) {
            return false;
        }

        $delete_db = ZibDB::name(self::$order_table_name)->where($order_id)->delete();
        ZibDB::name(self::$order_meta_table_name)->where('order_id', $order_id)->delete();

        //添加钩子
        do_action('delete_order', $order_id);

        return $delete_db;
    }

    /**
     * @description: 清理无效订单
     * @param int $days_ago 多少天前的订单
     * @return {*}
     */
    public static function clear_order($days_ago = 15)
    {

        global $wpdb;
        $ago_time = date('Y-m-d H:i:s', strtotime("-$days_ago day", strtotime(current_time('Y-m-d H:i:s'))));
        $where    = "`status` = -1 and `create_time` < '$ago_time'";
        $wpdb->query("DELETE FROM $wpdb->zibpay_payment WHERE $where"); //删除无效的支付记录

        //线程查询出全部的订单ID
        $order_ids = $wpdb->get_col("SELECT id FROM $wpdb->zibpay_order WHERE $where");
        $count     = 0;
        if (!empty($order_ids)) {
            $count     = count($order_ids);
            $order_ids = implode(',', $order_ids);
            $wpdb->query("DELETE FROM $wpdb->zibpay_order WHERE id IN ($order_ids)"); //删除无效的订单
            $wpdb->query("DELETE FROM $wpdb->zibpay_order_meta WHERE order_id IN ($order_ids)"); //删除无效的订单元数据
        }

        return $count;
    }

    public static function order_query($args)
    {
        $args = wp_parse_args($args, [
            'orderby'  => 'id',
            'order'    => 'DESC',
            'paged'    => 1,
            'per_page' => 10,
            'field'    => '*',
        ]);
        $args['meta_query'] = $args['meta_query'] ?? [];

        $orderby_sql           = $args['orderby'];
        $orderby_meta_num_keys = ['shipping_status', 'comment_status', 'meta_value_num', 'after_sale_status', 'refund_price'];
        $orderby_meta_val_keys = ['pay_modo', 'meta_value', 'after_sale_type', 'after_sale_time', 'shipping_time'];

        // 处理 orderby 参数
        if (in_array($args['orderby'], $orderby_meta_num_keys)) {
            $alias                = '_meta_orderby_' . $args['orderby'];
            $orderby_sql          = $alias . '.meta_value+0';
            $args['meta_query'][] = [
                'key'   => $args['orderby'],
                'alias' => $alias,
            ];
        } elseif (in_array($args['orderby'], $orderby_meta_val_keys)) {
            $alias                = '_meta_orderby_' . $args['orderby'];
            $orderby_sql          = $alias . '.meta_value';
            $args['meta_query'][] = [
                'key'   => $args['orderby'],
                'alias' => $alias,
            ];
        }

        $db = ZibDB::name(self::$order_table_name);
        // 处理 search 参数
        $where_like_keys      = ['ip_address', 'order_num', 'pay_num'];
        $where_like_meta_keys = ['order_data'];

        $search_text = !empty($args['s']) ? trim($args['s']) : (!empty($args['search']) ? trim($args['search']) : '');
        if ($search_text) {
            if (!empty($args['search_columns'])) {
                $search_columns_sql = [];
                if (!is_array($args['search_columns'])) {
                    $args['search_columns'] = [$args['search_columns']];
                }

                foreach ($args['search_columns'] as $search_column) {
                    if (in_array($search_column, $where_like_keys)) {
                        $search_columns_sql[] = $search_column;
                    } elseif (in_array($search_column, $where_like_meta_keys)) {
                        $alias                = '_meta_search_' . $search_column;
                        $search_columns_sql[] = $alias . '.meta_value';
                        $args['meta_query'][] = [
                            'key'   => $search_column,
                            'alias' => $alias,
                        ];
                    } elseif ($search_column === 'user') {
                        $users_args = array(
                            'search'         => '*' . $search_text . '*',
                            'search_columns' => array('user_email', 'user_nicename', 'display_name', 'user_login'),
                            'count_total'    => false,
                            'number'         => -1,
                            'fields'         => 'ids',
                        );
                        $user_search = new WP_User_Query($users_args);
                        $users       = $user_search->get_results();
                        if (!empty($users)) {
                            $db->whereOr('user_id', 'in', (array) $users);
                        } else {
                            $db->whereOr(1, '=', 0); // 如果用户不存在，则不进行查询
                        }
                    } elseif ($search_column === 'post') {
                        $posts_args = array(
                            's'              => $search_text,
                            'post_type'      => ['shop_product', 'post'],
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                        );
                        $posts = get_posts($posts_args);
                        if (!empty($posts)) {
                            $db->whereOr('post_id', 'in', (array) $posts);
                        } else {
                            $db->whereOr(1, '=', 0); // 如果文章不存在，则不进行查询
                        }
                    }
                }

                if (!empty($search_columns_sql)) {
                    $db->whereLike($search_columns_sql, $search_text);
                }
            } else {

                $alias                = '_meta_search_order_data';
                $args['meta_query'][] = [
                    'key'   => 'order_data',
                    'alias' => $alias,
                ];

                /*
                SELECT * FROM zibll_zibpay_order AS _m 
                INNER JOIN zibll_zibpay_ordermeta AS _meta_search_order_data ON (_m.id = _meta_search_order_data.order_id)
                WHERE ((_meta_search_order_data.meta_key = 'order_data')) AND 
                (
                ip_address LIKE '%2503060901325810181%' 
                OR order_num LIKE '%2503060901325810181%' 
                OR pay_num LIKE '%2503060901325810181%' 
                OR _meta_search_order_data.meta_value LIKE '%2503060901325810181%'
                )
                ORDER BY id DESC LIMIT 0, 20
                */
                $db->whereLike(['ip_address', 'order_num', 'pay_num', $alias . '.meta_value'], $search_text);
            }
        }

        //处理常规参数
        $where_args = [];
        foreach (['user_id', 'id', 'ip_address', 'product_id', 'post_id', 'post_author', 'income_price', 'income_status', 'order_num', 'order_price', 'order_type', 'payment_id', 'pay_num', 'pay_type', 'pay_price', 'status', 'referrer_id', 'rebate_price', 'rebate_status'] as $_k) {
            if (isset($args[$_k])) {
                if (is_array($args[$_k])) {
                    $where_args[] = [$_k, 'in', $args[$_k]];
                } else {
                    $where_args[] = [$_k, '=', $args[$_k]];
                }
            }
        }

        //处理依赖
        if (isset($args['income_status'])) {
            $where_args[] = ['income_price', '>', 0];
        }
        //处理依赖
        if (isset($args['rebate_status'])) {
            $where_args[] = ['rebate_price', '>', 0];
        }

        //处理meta_query
        foreach (['shipping_status', 'comment_status', 'pay_modo', 'after_sale_status', 'after_sale_type'] as $_k) {
            if (isset($args[$_k])) {
                $args['meta_query'][] = [
                    'key'     => $_k,
                    'compare' => is_array($args[$_k]) ? 'in' : '=',
                    'value'   => $args[$_k],
                ];
            }
        }

        //处理meta_query，的时间
        foreach (['after_sale_time', 'shipping_time'] as $_k) {
            if (isset($args[$_k])) {
                $args['meta_query'][] = [
                    'key'     => $_k,
                    'compare' => 'between',
                    'value'   => $args[$_k],
                ];
            }
        }

        if (!empty($args['meta_key'])) {
            $_meta = [
                'key' => $args['meta_key'],
            ];
            if (!empty($args['meta_value'])) {
                $_meta['compare'] = is_array($args['meta_value']) ? 'in' : '=';
                $_meta['value']   = $args['meta_value'];
            }

            $args['meta_query'][] = $_meta;
        }

        $db->where($where_args);
        foreach (['create_time', 'pay_time'] as $_k) {
            if (isset($args[$_k])) {
                $db->whereTime($_k, $args[$_k]);
            }
        }

        if (!empty($args['meta_query'])) {
            $db->metaName(self::$order_meta_table_name);
            $db->metaQuery($args['meta_query'], ['id', 'order_id']);
        }

        if (!empty($args['where'])) {
            $db->where($args['where']);
        }
        $db->order($orderby_sql, $args['order'])->page($args['paged'], $args['per_page']);

        $data = [
            'query'  => $args,
            'db'     => $db,
            'orders' => [],
        ];

        if (!empty($args['field'])) {
            if ($args['field'] === 'count') {
                $data['total'] = $db->count();
            } else {
                $db->field($args['field']);
                $data['orders'] = $db->select()->toArrayMap([self::class, 'order_data_map']);
            }
        } else {
            $data['orders'] = $db->select()->toArrayMap([self::class, 'order_data_map']);
        }

        $data['sql'] = $db->getSql();
        if (!isset($data['total'])) {
            if (empty($args['no_found_rows']) || (isset($args['show']) && $args['show'] != -1)) {
                $total_db      = clone $db;
                $data['total'] = $total_db->count();
            } else {
                $data['total'] = count($data['orders'] ?? []);
            }
        }

        return $data;
    }

    /**
     * @description: 更新订单数据库的主函数
     * @param {*}
     * @return {*}
     */
    public static function update_order($values)
    {
        global $wpdb;
        $defaults = array(
            'id'            => '',
            'user_id'       => '',
            'ip_address'    => '',
            'product_id'    => '',
            'count'         => 1,
            'post_id'       => '',
            'post_author'   => '',
            'income_price'  => '',
            'income_status' => '',
            'income_detail' => '',
            'order_num'     => '',
            'order_price'   => '',
            'order_type'    => '',
            'create_time'   => '',
            'payment_id'    => 0,
            'pay_num'       => '',
            'pay_type'      => '',
            'pay_price'     => '',
            'pay_detail'    => '',
            'pay_time'      => '',
            'status'        => 0,
            'other'         => '',
            'referrer_id'   => '',
            'rebate_price'  => '',
            'rebate_status' => '',
            'rebate_detail' => '',
            'meta'          => [],
        );
        $values = wp_parse_args((array) $values, $defaults);

        $order_data = array(
            'user_id'       => $values['user_id'],
            'ip_address'    => $values['ip_address'],
            'product_id'    => $values['product_id'],
            'count'         => $values['count'],
            'post_id'       => $values['post_id'],
            'post_author'   => $values['post_author'],
            'income_price'  => $values['income_price'],
            'income_status' => $values['income_status'],
            'income_detail' => maybe_serialize($values['income_detail']),
            'order_price'   => $values['order_price'],
            'order_type'    => $values['order_type'],
            'create_time'   => current_time('mysql'),
            'payment_id'    => $values['payment_id'],
            'pay_num'       => $values['pay_num'],
            'pay_type'      => $values['pay_type'],
            'pay_price'     => $values['pay_price'],
            'pay_detail'    => maybe_serialize($values['pay_detail']),
            'pay_time'      => $values['pay_time'],
            'status'        => $values['status'],
            'referrer_id'   => $values['referrer_id'],
            'rebate_price'  => $values['rebate_price'],
            'rebate_status' => $values['rebate_status'],
            'rebate_detail' => maybe_serialize($values['rebate_detail']),
        );
        $order_data = wp_unslash($order_data);

        if (!empty($values['id'])) {
            //更新数据库
            unset($order_data['create_time']); //清除创建时间

            $order_data = array_filter($order_data); //清除为空的数组键。
            if (!$order_data) {
                return new WP_Error('order_data_error', '订单数据异常');
            }

            $where = array('id' => $values['id']);
            //执行更新
            if (false !== $wpdb->update($wpdb->zibpay_order, $order_data, $where)) {
                if ($values['other']) {
                    $order_data['other'] = $values['other'];
                    $order               = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->zibpay_order} WHERE id = %d", $values['id']));
                    self::update_other($order, $values['other']);
                }

                if ($values['meta']) {
                    foreach ($values['meta'] as $meta_key => $meta_value) {
                        self::update_meta($values['id'], $meta_key, $meta_value);
                    }
                    $order_data['meta'] = $values['meta'];
                }

                return self::order_data_map($order_data);
            }
        }

        //如果上面未更新，则创建新订单
        //添加商品作者
        if (!$order_data['post_author'] && $order_data['post_id']) {
            $post = get_post($order_data['post_id']);
            if (!empty($post->post_author)) {
                $order_data['post_author'] = $post->post_author;
            }
        }
        $order_data['user_id'] = $order_data['user_id'] ? $order_data['user_id'] : get_current_user_id();
        /**用户id */
        $order_data['create_time'] = current_time('Y-m-d H:i:s');
        /** 创建时间 **/
        $order_data['ip_address'] = self::get_ip();
        /**记录IP地址 */
        $order_data['order_num'] = current_time('ymdHis') . mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(100, 999); // 订单号
        /**创建订单号 */

        //执行前置判断
        $order_data = apply_filters('pre_order_create_data', $order_data);
        if (is_wp_error($order_data)) {
            return $order_data;
        }

        //执行新增
        if (false !== $wpdb->insert($wpdb->zibpay_order, $order_data)) {
            $order_data['id'] = $wpdb->insert_id;

            if ($values['other']) {
                self::update_other($order_data['order_num'], $values['other']);
                $order_data['other'] = $values['other'];
            }

            if ($values['meta']) {
                foreach ($values['meta'] as $meta_key => $meta_value) {
                    self::update_meta($order_data['id'], $meta_key, $meta_value);
                }
                $order_data['meta'] = $values['meta'];
            }

            do_action('order_created', $order_data);
            return self::order_data_map($order_data);
        }

        return new WP_Error('order_create_error', '订单创建失败');
    }

    /**
     * @description: 新增订单
     * @param {*}
     * @return {*}
     */
    public static function add_order($values)
    {
        return self::update_order($values);
    }

    //判断是payment订单，payment的$order_num有特殊前缀
    public static function is_payment_order($order_num)
    {
        return strpos($order_num, self::$payment_order_num_prefix) === 0;
    }

    //payment支付订单
    public static function payment_payment(array $data)
    {
        global $wpdb;

        $defaults = array(
            'order_num' => '', //订单号：必传
            'pay_type'  => '', //支付方式
            'pay_num'   => '', //支付单号：必传
        );

        $data = wp_parse_args((array) $data, $defaults);
        if (empty($data['order_num'])) {
            return false;
        }

        $payment = self::get_payment($data['order_num']);
        if (empty($payment['id'])) {
            return false;
        }

        $payment_data = [
            'pay_time' => current_time('Y-m-d H:i:s'),
            'pay_num'  => $data['pay_num'],
            'status'   => 1,
        ];

        $where = array('id' => $payment['id'], 'status' => ['0', '-1']);
        if (ZibDB::name(self::$payment_table_name)->where($where)->update($payment_data)) {
            //根据$payment_id获取订单数据
            $order_data = zibpay::get_order_by_payment_id($payment['id']);
            if (!empty($order_data[0])) {
                foreach ($order_data as $order) {
                    if ($order['status'] == 0) {
                        $payment_order_data = array(
                            'order_num' => $order['order_num'], //订单号
                            'pay_type'  => $data['pay_type'] ?: $payment['method'], //支付方式
                            'pay_num'   => $data['pay_num'], //支付单号
                        );

                        $payment_order_data['pay_detail'][$payment['method']] = $order['pay_price'];

                        self::payment_order($payment_order_data);
                    }
                }
            }

            return true;
        }
    }
    /**
     * 支付订单
     * @param array $values 订单数据 ，必传数据：order_num , pay_num , pay_type
     * @return bool|obj $order
     */
    public static function payment_order(array $values)
    {
        global $wpdb;
        $defaults = array(
            'order_num'  => '', //订单号
            'pay_type'   => '', //支付方式
            'pay_num'    => '', //支付单号
            'other'      => array(), //可选
            'pay_detail' => array(), //可选
        );
        $values = wp_parse_args((array) $values, $defaults);
        if (empty($values['order_num'])) {
            return false;
        }

        if (self::is_payment_order($values['order_num'])) {
            return self::payment_payment($values);
        }

        //准备参数
        $order_data = array(
            'pay_type' => $values['pay_type'],
            'pay_num'  => $values['pay_num'],
            'status'   => 1,
            'pay_time' => current_time('Y-m-d H:i:s'),
        );

        if (isset($values['pay_price'])) {
            $order_data['pay_price'] = $values['pay_price'];
        }

        //准备查询参数
        $where = array('order_num' => $values['order_num'], 'status' => ['0', '-1']);
        if (ZibDB::name(self::$order_table_name)->where($where)->update($order_data)) {
            $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->zibpay_order} WHERE order_num = %s AND status = %d", $values['order_num'], 1));
            if ($order) {
                if ($values['other']) {
                    self::update_other($order, $values['other']);
                }

                if ($values['pay_detail']) {
                    self::update_other_pay_detail($order, $values['pay_detail']);
                }

                do_action('payment_order_success', $order); //此处$order不能为数组，只能为对象
                return $order;
            }
        }

        return false;
    }

    //让订单失效
    //关闭订单
    public static function close_order($order_id, $type = 'timeout', $reason = '')
    {
        $get_order = self::get_order($order_id, 'payment_id');
        if (!empty($get_order['payment_id'])) {
            return self::close_payment($get_order['payment_id'], $type, $reason);
        }

        return self::close_order_single($order_id, $type, $reason);
    }

    //关闭单个订单
    public static function close_order_single($order_id, $type = 'timeout', $reason = '')
    {
        global $wpdb;

        if ($wpdb->update($wpdb->zibpay_order, array('status' => -1), array('id' => $order_id, 'status' => 0))) {
            $order_meta_data = self::get_meta($order_id, 'order_data');
            if (!$order_meta_data || !is_array($order_meta_data)) {
                $order_meta_data = array();
            }

            $order_meta_data['close_type']   = $type;
            $order_meta_data['close_reason'] = $reason;
            self::update_meta($order_id, 'order_data', $order_meta_data);

            do_action('order_closed', $order_id, $type, $reason);
            return $order_id;
        }
        return false;
    }

    //关闭payment订单
    public static function close_payment($id, $type = 'timeout', $reason = '')
    {
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$wpdb->zibpay_order} WHERE payment_id = %d AND status = %d", $id, 0));
        if (empty($ids)) {
            return false;
        }

        //先关闭payment订单
        $wpdb->update($wpdb->zibpay_payment, array('status' => -1), array('id' => $id, 'status' => 0));

        foreach ($ids as $order_id) {
            self::close_order_single($order_id, $type, $reason);
        }

        return true;
    }

    //订单退单
    public static function refund_order($order_id)
    {
        global $wpdb;
        if ($wpdb->update($wpdb->zibpay_order, array('status' => -2), array('id' => $order_id))) {

            do_action('order_refunded', $order_id);
            return true;
        }

        return false;
    }

    public static function update_other_payment($payment_id, $old_payment_method, $new_payment_method)
    {

        //如果旧支付方式为积分，或新旧相同。则不更新
        if ($old_payment_method === 'points' || $old_payment_method === $new_payment_method) {
            return false;
        }

        $order_data = self::get_order_by_payment_id($payment_id, 'id,pay_detail,pay_price');
        if (empty($order_data)) {
            return false;
        }

        global $wpdb;
        foreach ($order_data as $order) {
            if (isset($order['pay_detail'][$old_payment_method])) {
                $_price = $order['pay_detail'][$old_payment_method];
                unset($order['pay_detail'][$old_payment_method]);
            } else {
                $_price = $order['pay_price'];
            }

            $order['pay_detail']['payment_method']    = $new_payment_method;
            $order['pay_detail'][$new_payment_method] = $_price;
            $wpdb->update($wpdb->zibpay_order, array('pay_detail' => maybe_serialize(wp_unslash($order['pay_detail']))), array('id' => $order['id']));
        }

        return true;
    }

    public static function update_other_pay_detail($order_or_order_num, $detail_data)
    {
        if (!is_array($detail_data)) {
            return false;
        }

        global $wpdb;
        $order_array = (array) $order_or_order_num;
        if (!isset($order_array['pay_detail'])) {
            $order      = $wpdb->get_row($wpdb->prepare("SELECT id,pay_detail FROM {$wpdb->zibpay_order} WHERE order_num = %s", $order_or_order_num));
            $pay_detail = maybe_unserialize($order->pay_detail);
            $order_id   = $order->id;
        } else {
            $pay_detail = maybe_unserialize($order_array['pay_detail']);
            $order_id   = $order_array['id'];
        }

        if (!$pay_detail || !is_array($pay_detail)) {
            $pay_detail = array();
        }

        $pay_detail = array_merge($pay_detail, $detail_data);

        $pay_detail = maybe_serialize($pay_detail);

        return $wpdb->update($wpdb->zibpay_order, array('pay_detail' => $pay_detail), array('id' => $order_id));
    }

    /** !V8.2以后弃用此函数，改为 meta
     * @description: 更新订单其它数据
     * @param {*} $order 订单单个对象包含other数据，或者订单号
     * @param {*} $other
     * @return {*}
     */
    public static function update_other($order, $other)
    {
        if (!is_array($other)) {
            return false;
        }

        global $wpdb;

        $order_array = (array) $order;
        if (!isset($order_array['other'])) {
            $order     = $wpdb->get_row($wpdb->prepare("SELECT id,other FROM {$wpdb->zibpay_order} WHERE order_num = %s", $order));
            $old_other = maybe_unserialize($order->other);
            $order_id  = $order->id;
        } else {
            $old_other = maybe_unserialize($order_array['other']);
            $order_id  = $order_array['id'];
        }

        $old_other = is_array($old_other) ? $old_other : array();
        $other     = array_merge($old_other, $other);
        $other     = maybe_serialize($other);

        return $wpdb->update($wpdb->zibpay_order, array('other' => $other), array('id' => $order_id));
    }

    /**
     * @description: 设置提现状态
     * @param int||arrat $id 允许多选数组
     * @param mixed $values 值
     * @return boolr
     */
    public static function set_rebate_status($id, $values)
    {
        global $wpdb;

        $where = array('id' => $id);
        if (is_array($id)) {
            $id = implode(',', $id);
            return $wpdb->query("update $wpdb->zibpay_order set rebate_status = $values where id IN ($id)");
        } else {
            return $wpdb->update($wpdb->zibpay_order, array('rebate_status' => $values), $where);
        }
    }
}
