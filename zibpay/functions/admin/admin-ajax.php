<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-21 17:38:48
 * @LastEditTime : 2026-05-05 22:16:42
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取仪表盘数据
function zibpay_ajax_admin_statistics_data()
{

    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $send_data = array();

    $time_end         = current_time('Y-m-d 24:59:59');
    $time_start       = date('Y-m-d 00:00:00', strtotime('-7 day', strtotime($time_end)));
    $today_sales_data = zibpay_get_order_chart_data($time_start, $time_end, 'day');
    $time_start       = date('Y-m-d 00:00:00', strtotime('-7 month', strtotime($time_end)));
    $month_sales_data = zibpay_get_order_chart_data($time_start, $time_end, 'month');

    $send_data['mini_chart_data'] = array(
        'today_sales' => $today_sales_data,
        'month_sales' => $month_sales_data,
    );

    $send_data['todo_data'] = array(
        'shipping_count'   => zib_shop_get_shipping_status_count('0'),
        'after_sale_count' => zib_shop_get_after_sale_status_count([1, 2]),
        'withdraw_count'   => zibpay_get_withdraw_pending_count(),
    );

    $unit                        = '￥';
    $send_data['mini_card_data'] = array(
        array(
            [
                'title' => '总收款',
                'data'  => zibpay_get_order_statistics_totime('all')['sum'],
                'unit'  => $unit,
            ],
            [
                'title' => '今年收款',
                'data'  => zibpay_get_order_statistics_totime('thisyear')['sum'],
                'unit'  => $unit,
            ],
        ),
        array(
            [
                'title' => _pz('pay_user_vip_1_name', 'VIP1'),
                'data'  => zib_get_vip_user_count(1),
            ],
            [
                'title' => _pz('pay_user_vip_2_name', 'VIP2'),
                'data'  => zib_get_vip_user_count(2),
            ],
        ),
        array(
            [
                'title' => '总佣金',
                'data'  => zibpay_get_rebate_statistics_totime('all')['sum'],
                'unit'  => $unit,
            ],
            [
                'title' => '待提现',
                'data'  => zibpay_get_rebate_statistics_totime('all', '0')['sum'],
                'unit'  => $unit,
            ],
        ),
        array(
            [
                'title' => '总分成',
                'data'  => zibpay_get_income_statistics_totime('all')['sum'],
                'unit'  => $unit,
            ],
            [
                'title' => '待提现',
                'data'  => zibpay_get_income_statistics_totime('all', '0')['sum'],
                'unit'  => $unit,
            ],
        ),
    );

    zib_send_json_success($send_data);
}
add_action('wp_ajax_admin_statistics_data', 'zibpay_ajax_admin_statistics_data');

function zibpay_ajax_admin_order_chart_data()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $cycle         = !empty($_REQUEST['cycle']) ? $_REQUEST['cycle'] : 'day'; //周期 ： 年，月，周，天 | year，month，day。默认按天
    $default_time_ = array(
        'month' => '-5 month',
        'year'  => '-3 year',
    );

    $default_time__ = isset($default_time_[$cycle]) ? $default_time_[$cycle] : '-15 day';
    //按照时间查询
    $new_time   = current_time('Y-m-d H:i:s');
    $time_start = !empty($_REQUEST['time'][0]) ? $_REQUEST['time'][0] : date('Y-m-d 00:00:00', strtotime($default_time__, strtotime($new_time)));
    $time_end   = !empty($_REQUEST['time'][1]) ? $_REQUEST['time'][1] : date('Y-m-d 23:59:59', strtotime($new_time));
    $order_type = !empty($_REQUEST['order_type']) ? $_REQUEST['order_type'] : '';
    $chart_data = zibpay_get_order_chart_data($time_start, $time_end, $cycle, $order_type);

    $min_data = array();
    foreach ($chart_data['time'] as $k => $v) {
        $min_data[] = array(
            'time'  => $v,
            'nums'  => (int) $chart_data['nums'][$k],
            'price' => (float) $chart_data['price'][$k],
        );
    }

    $send_data = array(
        'table' => $min_data,
        'chart' => $chart_data,
    );

    zib_send_json_success($send_data);
}
add_action('wp_ajax_admin_order_chart_data', 'zibpay_ajax_admin_order_chart_data');

//获取订单类型饼图数据
function zibpay_ajax_admin_type_pie_data()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'price';
    $time = !empty($_REQUEST['time']) ? $_REQUEST['time'] : 'all';

    //获取所有订单类型的统计
    global $wpdb;
    $where = array(
        ['status', '=', 1],
    );
    $field = array(
        'order_type' => 'order_type',
    );

    if ($type == 'count') {
        $field['sum(count)'] = 'data';
    } else {
        $field['sum(pay_price)'] = 'data';
        $where[]                 = ['pay_type', '!=', 'points'];
    }

    $new_db = ZibDB::table($wpdb->zibpay_order)->field($field)->where($where)->group('order_type');
    if ($time !== 'all') {
        $new_db->whereTime('pay_time', $time);
    }
    $db_data = $new_db->select()->toArray();

    $data = array(
        1  => array('value' => 0, 'name' => '付费阅读'), //文章，帖子
        2  => array('value' => 0, 'name' => '付费下载'), //文章
        5  => array('value' => 0, 'name' => '付费图片'), //文章
        6  => array('value' => 0, 'name' => '付费视频'), //文章
        4  => array('value' => 0, 'name' => '购买会员'), //用户
        8  => array('value' => 0, 'name' => '余额充值'), //用户
        9  => array('value' => 0, 'name' => '购买积分'), //用户
        10 => array('value' => 0, 'name' => '购买商品'), //商城，商品
    );

    $other = 0;
    foreach ($db_data as $value) {
        if (isset($data[$value['order_type']])) {
            $data[$value['order_type']]['value'] = zib_floatval_round($value['data']);
        } else {
            $other += zib_floatval_round($value['data']);
        }
    }

    if ($other > 0) {
        $data[] = array('value' => $other, 'name' => '其他');
    }

    //去掉所有的key
    $data = array_values($data);

    $send_data = array(
        'data'   => $data,
        'new_db' => $new_db,
    );
    zib_send_json_success($send_data);
}
add_action('wp_ajax_admin_type_pie_data', 'zibpay_ajax_admin_type_pie_data');

//获取热销商品数据
function zibpay_ajax_admin_hot_product_data()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $order_type = !empty($_REQUEST['order_type']) ? $_REQUEST['order_type'] : [];
    $pay_mode   = !empty($_REQUEST['pay_mode']) ? $_REQUEST['pay_mode'] : [];
    $time       = !empty($_REQUEST['time']) ? $_REQUEST['time'] : 'all';

    $where = array(
        ['status', '=', 1],
        ['post_id', '>', 0],
    );
    $field = array(
        'sum(count)'     => 'count',
        'sum(pay_price)' => 'price',
        'post_id'        => 'post_id',
    );

    if (!empty($pay_mode)) {
        $where[] = ['pay_mode', is_array($pay_mode) ? 'in' : '=', $pay_mode];
    } else {
        $where[] = ['pay_type', '!=', 'points'];
    }

    if (!empty($order_type)) {
        $where[] = ['order_type', is_array($order_type) ? 'in' : '=', $order_type];
    }

    global $wpdb;
    $limit  = 50;
    $new_db = ZibDB::table($wpdb->zibpay_order)->field($field)->where($where)->group('post_id')->order('price', 'desc')->limit($limit);
    if ($time !== 'all') {
        $new_db->whereTime('pay_time', $time);
    }
    $db_data = $new_db->select()->toArray();
    $data    = $db_data;

    $max = array(
        'count' => 0,
        'price' => 0,
    );
    foreach ($data as $key => $value) {
        $post       = get_post($value['post_id']);
        $post_title = $post ? $post->post_title : '';
        if (!$post_title) {
            $post_title = '商品已删除[ID:' . $value['post_id'] . ']';
        }
        $data[$key]['post_title']       = $post_title;
        $data[$key]['product_url']      = $post ? get_permalink($post) : '';
        $data[$key]['product_edit_url'] = $post ? get_edit_post_link($post) : '';
        $max['count']                   = max($max['count'], $value['count']);
        $max['price']                   = max($max['price'], $value['price']);
    }

    $send_data = array(
        'data'   => $data,
        'new_db' => $new_db,
        'max'    => $max,
    );
    zib_send_json_success($send_data);
}
add_action('wp_ajax_admin_hot_product_data', 'zibpay_ajax_admin_hot_product_data');

//获取资产排行数据
function zibpay_ajax_admin_asset_ranking_data()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $time = !empty($_REQUEST['time']) ? $_REQUEST['time'] : 'all';

    $rebate_data  = zibpay_get_order_ranking_data('rebate', $time);
    $income_data  = zibpay_get_order_ranking_data('income', $time);
    $points_data  = zibpay_get_user_ranking_data('points');
    $balance_data = zibpay_get_user_ranking_data('balance');

    $send_data = array(
        'rebate_data'  => $rebate_data,
        'income_data'  => $income_data,
        'points_data'  => $points_data,
        'balance_data' => $balance_data,
    );
    zib_send_json_success($send_data);
}
add_action('wp_ajax_admin_asset_ranking_data', 'zibpay_ajax_admin_asset_ranking_data');

//获取用户排行数据
function zibpay_get_user_ranking_data($type = 'points')
{
    global $wpdb;

    $field = array(
        '_mt.meta_value' => 'price',
        'display_name'   => 'display_name',
        'ID'             => 'user_id',
    );
    $meta_query = array(
        [
            'alias'   => '_mt',
            'key'     => 'points',
            'compare' => '>',
            'value'   => 0,
        ],
    );
    if ($type == 'balance') {
        $meta_query = array(
            [
                'alias'   => '_mt',
                'key'     => 'balance',
                'compare' => '>',
                'value'   => 0,
            ],
        );
    }

    $limit  = 50;
    $new_db = ZibDB::table($wpdb->users)->field($field)->order('_mt.meta_value+0', 'desc')->group('ID')->limit($limit);
    $new_db->metaTable($wpdb->usermeta)->metaQuery($meta_query, ['ID', 'user_id']);
    $db_data = $new_db->select()->toArray();

    foreach ($db_data as $key => $value) {
        $user_data                    = get_userdata($value['user_id']);
        $db_data[$key]['user_name']   = $user_data->display_name ?? '用户[ID:' . $value['user_id'] . ']';
        $db_data[$key]['user_avatar'] = zib_get_user_avatar_url($value['user_id']);
    }

    return $db_data;
}

//查询佣金资金排行数据
function zibpay_get_order_ranking_data($type = 'rebate', $time = 'all')
{
    global $wpdb;
    $where = array(
        ['status', '=', 1],
        ['pay_type', '!=', 'points'],
    );

    if ($type == 'income') {
        $where[] = ['income_price', '>', 0];
        $where[] = ['post_author', '>', 0];
        $field   = array(
            'sum(income_price)' => 'price',
            'post_author'       => 'user_id',
        );
        $group = 'post_author';
    } else {
        $where[] = ['rebate_price', '>', 0];
        $where[] = ['referrer_id', '>', 0];
        $field   = array(
            'sum(rebate_price)' => 'price',
            'referrer_id'       => 'user_id',
        );
        $group = 'referrer_id';
    }

    $limit   = 50;
    $new_db  = ZibDB::table($wpdb->zibpay_order)->field($field)->where($where)->whereTime('pay_time', $time)->group($group)->order('price', 'desc')->limit($limit);
    $db_data = $new_db->select()->toArray();

    foreach ($db_data as $key => $value) {
        $user_data                    = get_userdata($value['user_id']);
        $db_data[$key]['user_name']   = $user_data->display_name ?? '用户[ID:' . $value['user_id'] . ']';
        $db_data[$key]['user_avatar'] = zib_get_user_avatar_url($value['user_id']);
    }

    return $db_data;
}

//获取订单统计数据
function zibpay_get_order_chart_data($time_start, $time_end, $cycle = 'day', $order_type = [])
{
    global $wpdb;
    $cycle_format_array = array(
        'day'   => '%Y-%m-%d',
        'month' => '%Y-%m',
        'year'  => '%Y',
    );
    $cycle_format = $cycle_format_array[$cycle];

    //查询时间格式化
    $whereTime = zibpay_get_wheretime_between($time_start, $time_end, $cycle);
    $field     = array(
        "date_format(create_time, '$cycle_format')" => 'time',
        'sum(pay_price)'                            => 'price',
        'count(*)'                                  => 'count',
    );
    $where = array(
        ['status', '=', 1],
        ['pay_type', 'not in', ['points']],
    );
    if (!empty($order_type)) {
        $where[] = ['order_type', is_array($order_type) ? 'in' : '=', $order_type];
    } else {
        $where[] = ['order_type', '!=', 8];
    }

    $db_data = ZibDB::table($wpdb->zibpay_order)->field($field)->where($where)->whereTime('create_time', 'between', $whereTime)->group("date_format(create_time,'$cycle_format')")->select()->toArray();
    $filling = zibpay_get_time_charts_filling($cycle, array($time_start, $time_end));
    $nums    = $filling['data'];
    $total   = $filling['data'];
    $result  = $filling['time'];
    array_walk($db_data, function ($value, $key) use ($result, &$nums, &$total) {
        $index         = array_search($value['time'], $result);
        $nums[$index]  = $value['count'] ?? 0;
        $total[$index] = zib_floatval_round($value['price'] ?? 0);
    });

    $chart_data = [
        'time'  => $result,
        'nums'  => $nums,
        'price' => $total,
    ];

    return $chart_data;
}

//获取数据库查询时间的区间时间
function zibpay_get_wheretime_between($time_start, $time_end, $cycle = 'day')
{
    $wheretime_format_array = array(
        'day'   => array('Y-m-d 00:00:00', 'Y-m-d 23:59:59'),
        'month' => array('Y-m-01 00:00:00', 'Y-m-31 23:59:59'),
        'year'  => array('Y-01-01 00:00:00', 'Y-12-30 23:59:59'),
    );
    $wheretime_format = $wheretime_format_array[$cycle];
    $time_start       = date($wheretime_format[0], strtotime($time_start));
    $time_end         = date($wheretime_format[1], strtotime($time_end));
    $whereTime        = array($time_start, $time_end);
    return $whereTime;
}

//获取时间填充数据
function zibpay_get_time_charts_filling($cycle, $time)
{
    $cycle_format_array = array(
        'day'   => 'Y-m-d',
        'month' => 'Y-m',
        'year'  => 'Y',
    );
    $count_x = array(
        'day'   => 86400,
        'month' => 259200,
        'year'  => 'Y',
    );

    $new_time   = current_time('mysql');
    $time_start = $time[0];
    $time_end   = !empty($time[1]) ? $time[1] : '';

    if (!$time_end) {
        $time_start = $new_time;
        $time_end   = $time[0];
    }

    if (strtotime($time_end) > strtotime($new_time)) {
        $time_end = $new_time;
    }
    //结束时间不高于当前时间

    if (strtotime($time_end) < strtotime($time_start)) {
        throw new Exception('结束时间不能小于开始时间');
    }

    if ('day' == $cycle) {
        $count = ceil((strtotime($time_end) - strtotime($time_start)) / 86400);
    } elseif ('month' == $cycle) {
        $date1_stamp                     = strtotime($time_end);
        $date2_stamp                     = strtotime($time_start);
        list($date_1['y'], $date_1['m']) = explode('-', date('Y-m', $date1_stamp));
        list($date_2['y'], $date_2['m']) = explode('-', date('Y-m', $date2_stamp));
        $count                           = abs($date_1['y'] - $date_2['y']) * 12 + ($date_1['m'] - $date_2['m']) + 1;
    }

    for ($i = $count - 1; 0 <= $i; $i--) {
        $time_end_sum = date($cycle_format_array[$cycle], strtotime($time_end));
        $result[]     = date($cycle_format_array[$cycle], strtotime('-' . $i . ' ' . $cycle, strtotime($time_end_sum)));
        $data[]       = 0;
    }

    $asd = array(
        'time'       => $result,
        'data'       => $data,
        'count'      => $count,
        'cycle'      => $cycle,
        'time_start' => $time_start,
        'time_end'   => $time_end,
    );

    return array(
        'time' => $result,
        'data' => $data,
    );
}

//获取售后列表
function zibpay_ajax_admin_after_sale_table_list()
{

    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $db_data = zibpay_ajax_get_order_lits_data([
        'status'            => [-2, 1],
        'after_sale_status' => [1, 2, 3, 4, 5],
        'order_type'        => zib_shop_get_order_type(),
    ]);

    $data = array(
        'lits_data'    => $db_data['orders'],
        'count'        => $db_data['total'],
        'query_args'   => $db_data['query_args'],
        'sql'          => $db_data['sql'], //刚刚查询使用的SQL语句
        'status_count' => [
            '1' => zib_shop_get_after_sale_status_count('1'),
            '2' => zib_shop_get_after_sale_status_count('2'),
        ],
    );

    zib_send_json_success($data);
}
add_action('wp_ajax_admin_after_sale_table_list', 'zibpay_ajax_admin_after_sale_table_list');

//获取发货列表
function zibpay_ajax_admin_shipping_table_list()
{

    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $db_data = zibpay_ajax_get_order_lits_data(
        [
            'status'     => 1,
            'order_type' => zib_shop_get_order_type(),
        ]
    );

    $data = array(
        'lits_data'    => $db_data['orders'],
        'count'        => $db_data['total'],
        'query_args'   => $db_data['query_args'],
        'sql'          => $db_data['sql'], //刚刚查询使用的SQL语句
        'status_count' => [
            '0' => zib_shop_get_shipping_status_count('0'),
            '1' => zib_shop_get_shipping_status_count('1'),
        ],
    );

    zib_send_json_success($data);
}
add_action('wp_ajax_admin_shipping_table_list', 'zibpay_ajax_admin_shipping_table_list');

//获取订单列表
function zibpay_ajax_admin_order_table_list()
{

    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('您没有权限访问此页面');
    }

    $db_data = zibpay_ajax_get_order_lits_data();

    $data = array(
        'lits_data'  => $db_data['orders'],
        'count'      => $db_data['total'],
        'query_args' => $db_data['query_args'],
        'sql'        => $db_data['sql'], //刚刚查询使用的SQL语句
    );

    $db_1   = clone $db_data['db'];
    $db_2   = clone $db_data['db'];
    $db_3   = clone $db_data['db'];
    $db_4   = clone $db_data['db'];
    $db_5   = clone $db_data['db'];
    $data_1 = $db_1->field('sum(count) as count,sum(order_price) as order_price')->where('pay_type', '!=', 'points')->find()->toArray();
    $data_2 = $db_2->field('sum(count) as count,sum(order_price) as order_price')->where('pay_type', '=', 'points')->find()->toArray();

    $data_3 = $db_3->field('sum(income_price) as income_price,sum(rebate_price) as rebate_price')->where([['status', '=', 1]])->find()->toArray();

    $data_4 = $db_4->field('sum(income_price) as income_price')->where([['status', '=', 1], ['income_status', '!=', 1]])->find()->toArray();
    $data_5 = $db_5->field('sum(rebate_price) as rebate_price')->where([['status', '=', 1], ['rebate_status', '!=', 1]])->find()->toArray();

    $statistics_data = [
        [
            '现金销量' => (int) ($data_1['count'] ?: 0),
            '积分销量' => (int) ($data_2['count'] ?: 0),
        ],
        [
            '现金金额' => zib_floatval_round($data_1['order_price'] ?: 0),
            '积分金额' => (int) ($data_2['order_price'] ?: 0),
        ],
        [
            '佣金合计' => zib_floatval_round($data_3['rebate_price']),
            '待提现'  => zib_floatval_round($data_5['rebate_price']),
        ],
        [
            '分成合计' => zib_floatval_round($data_3['income_price']),
            '待提现'  => zib_floatval_round($data_4['income_price']),
        ],
    ];

    $data['statistics_data'] = $statistics_data;
    zib_send_json_success($data);
}
add_action('wp_ajax_admin_order_table_list', 'zibpay_ajax_admin_order_table_list');

//清理订单
function zibpay_ajax_admin_clear_order()
{
    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    $result = zibpay::clear_order(14);
    if ($result) {
        zib_send_json_success('清理完成[共清理：' . $result . '个订单]');
    } else {
        zib_send_json_success('没有需要清理的订单');
    }
}
add_action('wp_ajax_admin_clear_order', 'zibpay_ajax_admin_clear_order');

//获取售后记录列表
function zibpay_ajax_admin_after_sale_record_html()
{

    if (!_pz('shop_s')) {
        zib_send_json_error('商城系统已关闭');
    }

    //非管理员禁止访问
    if (!current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    $order_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    $record_html = zib_shop_get_after_sale_record_lists($order_id);

    zib_send_json_success(['html' => $record_html]);
}
add_action('wp_ajax_admin_after_sale_record_html', 'zibpay_ajax_admin_after_sale_record_html');

function zibpay_ajax_order_query($args = [])
{
    $paged         = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1; //当前页码
    $pagesize      = !empty($_REQUEST['pagesize']) ? $_REQUEST['pagesize'] : 20; //每页数量
    $order         = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'desc'; //排序
    $order         = $order == 'asc' || $order == 'ascending' ? 'ASC' : 'DESC';
    $orderby       = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'id'; //排序值
    $search        = !empty($_REQUEST['search']) ? (trim($_REQUEST['search'])) : ''; //搜索
    $search_filter = !empty($_REQUEST['search_filter']) ? $_REQUEST['search_filter'] : '';

    $query_args = array_merge([
        'orderby'  => $orderby,
        'order'    => $order,
        'paged'    => $paged,
        'per_page' => $pagesize,
    ], $args);

    if ($search) {
        $query_args['s'] = $search;
        if ($search_filter) {
            $query_args['search_columns'] = $search_filter;
        }
    }

    //筛选
    $filter = !empty($_REQUEST['filter']) ? $_REQUEST['filter'] : '';
    if ($filter && is_array($filter)) {
        foreach ($filter as $key => $val) {
            if ($val !== '') {
                $query_args[$key] = $val;
            }
        }
    }

    //时间查询
    $timefilter = !empty($_REQUEST['timefilter']) ? $_REQUEST['timefilter'] : '';
    if ($timefilter && is_array($timefilter)) {
        foreach ($timefilter as $key => $val) {
            $query_args[$key] = $val;
        }
    }

    $db_data               = zibpay::order_query($query_args);
    $db_data['query_args'] = $query_args;

    return $db_data;
}

function zibpay_ajax_get_order_lits_data($args = [])
{

    $shop_s  = _pz('shop_s', true);
    $db_data = zibpay_ajax_order_query($args);
    $marks   = [
        'pay'    => zibpay_get_pay_mark(),
        'points' => zibpay_get_points_mark(),
    ];

    if (!empty($db_data['orders'])) {
        foreach ($db_data['orders'] as $key => $order) {
            $meta                                        = zibpay::get_meta($order['id']);
            $meta_order_data                             = $meta['order_data'] ?? [];
            $db_data['orders'][$key]['pay_detail_lists'] = zibpay_get_order_pay_detail_lists($order, '、');
            $db_data['orders'][$key]['meta']             = $meta;
            $db_data['orders'][$key]['type_name']        = zibpay_get_pay_type_name($order['order_type']);
            $db_data['orders'][$key]['pay_modo']         = $meta['pay_modo'] ?? ($order['pay_type'] === 'points' ? 'points' : 'price');
            $db_data['orders'][$key]['show_mark']        = $db_data['orders'][$key]['pay_modo'] === 'points' ? $marks['points'] : $marks['pay'];

            //对旧数据的积分兼容
            if ($order['pay_type'] === 'points') {
                if (!(int) $order['order_price'] && !empty($order['pay_detail']['points'])) {
                    $order['order_price']                   = $order['pay_detail']['points'];
                    $db_data['orders'][$key]['order_price'] = $order['order_price'];
                }

                if ($order['status'] == 1 && !empty($order['pay_detail']['points'])) {
                    if (!(int) $order['pay_price']) {
                        $order['pay_price']                   = $order['pay_detail']['points'];
                        $db_data['orders'][$key]['pay_price'] = $order['pay_price'];
                    }

                    if (!empty($order['income_detail']['points']) && !(int) $order['income_status']) {
                        $order['income_status']                   = 1;
                        $db_data['orders'][$key]['income_status'] = 1;
                    }
                }
            }

            if (!isset($db_data['orders'][$key]['count'])) {
                $db_data['orders'][$key]['count'] = $meta_order_data['count'] ?? 1;
            }

            if ($order['status'] == 0) {
                $time_remaining = zibpay_get_order_pay_over_time($order);
                if ($time_remaining == 'over') {
                    $db_data['orders'][$key]['status'] = -1;
                } elseif ($time_remaining) {
                    $db_data['orders'][$key]['close_time'] = date('Y-m-d H:i:s', $time_remaining);
                }
            }

            //整理发货信息
            $db_data['orders'][$key]['shipping_time']   = $meta['shipping_time'] ?? '';
            $db_data['orders'][$key]['shipping_status'] = $meta['shipping_status'] ?? 0;
            $db_data['orders'][$key]['express_data']    = $meta_order_data['express_data'] ?? [];
            $db_data['orders'][$key]['shipping_data']   = $meta_order_data['shipping_data'] ?? [];
            $db_data['orders'][$key]['shipping_type']   = $meta_order_data['shipping_type'] ?? '';
            $db_data['orders'][$key]['consignee']       = $meta_order_data['consignee'] ?? [];

            if ($db_data['orders'][$key]['shipping_status'] == 1) {
                //如果是待收货，则判断待收货时效
                $receipt_over_time = $shop_s ? zib_shop_get_order_receipt_over_time($order['id']) : false;
                if ($receipt_over_time == 'over') {
                    $db_data['orders'][$key]['shipping_status'] = 2;
                } elseif ($receipt_over_time) {
                    $db_data['orders'][$key]['shipping_receipt_over_time'] = date('Y-m-d H:i:s', $receipt_over_time);
                }
            }

            //整理售后信息
            $db_data['orders'][$key]['after_sale_time']         = $meta['after_sale_time'] ?? '';
            $db_data['orders'][$key]['after_sale_status']       = $meta['after_sale_status'] ?? 0;
            $db_data['orders'][$key]['after_sale_data']         = $meta_order_data['after_sale_data'] ?? [];
            $db_data['orders'][$key]['after_sale_type']         = $meta_order_data['after_sale_data']['type'] ?? '';
            $db_data['orders'][$key]['after_sale_record']       = $meta_order_data['after_sale_record'] ?? [];
            $db_data['orders'][$key]['after_sale_record_count'] = 0;
            if (!empty($meta_order_data['after_sale_record'])) {
                $db_data['orders'][$key]['after_sale_record_count'] = count(array_keys($meta_order_data['after_sale_record']));
            }
            if ($db_data['orders'][$key]['after_sale_status'] == 2) {
                $return_express_over_time = $shop_s ? zib_shop_get_order_after_sale_return_express_over_time($order, $meta_order_data['after_sale_data']) : false;
                if ($return_express_over_time === 'over') {
                    $db_data['orders'][$key]['after_sale_status'] = 4;
                } elseif ($return_express_over_time) {
                    $db_data['orders'][$key]['after_sale_return_express_over_time'] = date('Y-m-d H:i:s', $return_express_over_time);
                }
            }

            //整理价格信息
            if (!isset($meta_order_data['prices'])) {
                $meta_order_data['prices'] = [
                    'pay_price'   => $order['order_price'],
                    'total_price' => $order['order_price'],
                    'unit_price'  => $order['order_price'],
                ];

                if (!empty($order['pay_price']) || $order['status'] == 1) {
                    $meta_order_data['prices']['pay_price'] = $order['pay_price'];
                }

                if ($meta_order_data['prices']['pay_price'] < $meta_order_data['prices']['unit_price']) {
                    $_discount_price                             = zib_floatval_round($meta_order_data['prices']['unit_price'] - $meta_order_data['prices']['pay_price']);
                    $meta_order_data['prices']['total_discount'] = $_discount_price;
                    if (!empty($order['pay_detail']['rebate_discount'])) {
                        $meta_order_data['prices']['rebate_discount'] = $order['pay_detail']['rebate_discount'];
                    }
                }
            }
            $db_data['orders'][$key]['prices'] = $meta_order_data['prices'];

            $user_info = [
                'name' => '未登录用户',
            ];

            if ($order['user_id']) {
                $udata     = get_userdata($order['user_id']);
                $user_info = [
                    'user_id'   => $order['user_id'],
                    'name'      => $udata ? $udata->display_name : '用户[ID:' . $order['user_id'] . ']',
                    'email'     => $udata ? $udata->user_email : '',
                    'avatar'    => zib_get_user_avatar_url($order['user_id']),
                    'rewards'   => $udata ? zib_get_user_rewards_img_urls($order['user_id']) : [],
                    'home_url'  => $udata ? zib_get_user_home_url($order['user_id']) : '',
                    'admin_url' => $udata ? '/wp-admin/users.php?s=' . esc_attr($udata->user_email ?: $udata->display_name ?: $udata->login): '',
                ];
            }

            $product_info = [
                'title'    => !empty($meta['order_data']['product_title']) ? $meta['order_data']['product_title'] : $db_data['orders'][$key]['type_name'],
                'opt_name' => !empty($meta['order_data']['options_active_name']) ? $meta['order_data']['options_active_name'] : ($db_data['orders'][$key]['product_id'] ?: ''),
                'thumb'    => zibpay_get_order_order_thumb($order),
            ];

            if ($order['post_id']) {
                $product                   = get_post($order['post_id']);
                $product_info['thumbnail'] = zibpay_get_post_thumbnail_url($product);
                $product_info['url']       = $product ? get_the_permalink($product) : '';
                $product_info['edit_url']  = $product ? get_edit_post_link($product, 'no') : '';
                $product_info['title']     = $product->post_title ?? '商品已删除[ID:' . $order['post_id'] . ']';
            }

            $author_info = [
                'name' => '无',
            ];

            if ($order['post_author']) {
                $author      = get_userdata($order['post_author']);
                $author_info = [
                    'author_id'      => $order['post_author'],
                    'name'           => $author->display_name ?? '用户[ID:' . $order['post_author'] . ']',
                    'avatar'         => zib_get_user_avatar_url($order['post_author']),
                    'author_address' => $shop_s ? zib_shop_get_author_addresses($order['post_author']) : [],
                ];
            }

            $rebate_info = [
                'price'       => 0,
                'status'      => 0,
                'referrer_id' => 0,
            ];
            if ($order['referrer_id'] && $order['rebate_price'] > 0) {
                $referrer    = get_userdata($order['referrer_id']);
                $rebate_info = [
                    'price'         => $order['rebate_price'],
                    'status'        => $order['rebate_status'],
                    'referrer_id'   => $order['referrer_id'],
                    'detail'        => $order['rebate_detail'] ?? [],
                    'referrer_info' => [
                        'name'   => $referrer->display_name ?? '用户[ID:' . $order['referrer_id'] . ']',
                        'avatar' => zib_get_user_avatar_url($order['referrer_id']),
                    ],
                ];
            }

            $income_info = [
                'points' => 0,
                'price'  => 0,
                'status' => 0,
            ];

            if ($order['post_author'] && ($order['income_price'] > 0 || !empty($order['income_detail']['points']))) {
                $income_info = [
                    'price'       => $db_data['orders'][$key]['pay_modo'] === 'points' ? ($order['income_detail']['points'] ?? 0) : $order['income_price'],
                    'status'      => $order['income_status'],
                    'author_info' => $author_info,
                    'detail'      => $order['income_detail'] ?? [],
                ];
            }

            $db_data['orders'][$key]['order_data']   = $meta_order_data; //非必要
            $db_data['orders'][$key]['rebate_info']  = $rebate_info;
            $db_data['orders'][$key]['income_info']  = $income_info;
            $db_data['orders'][$key]['product_info'] = $product_info;
            $db_data['orders'][$key]['user_info']    = $user_info;
            $db_data['orders'][$key]['author_info']  = $author_info;
        }
    }

    return $db_data;
}

//手动补单提交
function zibpay_ajax_admin_payment_submit()
{
    $order_num      = !empty($_REQUEST['order_num']) ? $_REQUEST['order_num'] : '';
    $payment_method = !empty($_REQUEST['payment_method']) ? $_REQUEST['payment_method'] : '';
    $pay_price      = !empty($_REQUEST['pay_price']) ? (float) $_REQUEST['pay_price'] : 0;
    $pay_num        = !empty($_REQUEST['pay_num']) ? $_REQUEST['pay_num'] : '';

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce('admin_order_page_ajax_submit');

    //判断管理员权限
    if (!current_user_can('administrator')) {
        zib_send_json_error('权限不足');
    }

    if (!$order_num) {
        zib_send_json_error('参数错误');
    }

    if (!$payment_method) {
        zib_send_json_error('请选择支付方式');
    }

    if (!$pay_num) {
        zib_send_json_error('请输入支付单号');
    }

    //执行补单
    $pay_data = array(
        'order_num' => $order_num,
        'pay_type'  => $payment_method,
        'pay_price' => $pay_price,
        'pay_num'   => $pay_num,
    );

    // 执行补单
    $order = ZibPay::payment_order($pay_data);

    if ($order) {
        //更新付款的pay_detail
        global $wpdb;
        $pay_detail = array(
            $payment_method  => $pay_price,
            'payment_method' => $payment_method,
        );
        $wpdb->update($wpdb->zibpay_order, array('pay_detail' => $pay_detail), array('id' => $order->id));

        zib_send_json_success('补单成功');
    } else {
        zib_send_json_error('补单失败，未找到可补单的订单');
    }
}
add_action('wp_ajax_admin_payment_submit', 'zibpay_ajax_admin_payment_submit');
