<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-03-15 13:26:22
 * @LastEditTime : 2026-05-05 22:51:05
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|支付功能：用户销售分成系统
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户的收入订单明细
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_order_lists($user_id)
{
    if (!$user_id) {
        return;
    }

    $paged         = zib_get_the_paged();
    $ice_perpage   = 10;
    $income_status = isset($_REQUEST['income_status']) ? (int) $_REQUEST['income_status'] : '';

    $db = ZibDB::name(zibpay::$order_table_name)->where('status', 1)->where('post_author', $user_id)->where('income_price', '>', 0)->order('pay_time', 'DESC')->page($paged, $ice_perpage);
    if ($income_status) {
        $db->where('income_status', $income_status);
    }
    $db_order  = $db->select()->result();
    $count_all = $db->count();

    $html  = '';
    $lists = '';
    if ($db_order) {
        foreach ($db_order as $order) {
            $order_num       = $order->order_num;
            $pay_time        = $order->pay_time;
            $post_id         = $order->post_id;
            $order_type_name = zibpay_get_pay_type_name($order->order_type);
            $pay_title       = $order_type_name ? '<div class="pay-tag badg badg-sm mr6">' . $order_type_name . '</div>' : '';
            if ($post_id) {
                $posts_title = get_the_title($post_id);
                $permalink   = get_permalink($post_id);
                $pay_title .= '<a target="_blank" class="" href="' . $permalink . '">' . $posts_title . '</a>';
            }

            $class         = 'order-type-' . $order->order_type;
            $income_status = $order->income_status ? '<span class="c-blue badg badg-sm">已提现</span>' : '<span class="c-yellow badg badg-sm">未提现</span>';
            if ($order->income_status == 3) {
                $income_status = '<span class="c-yellow badg badg-sm">提现待处理</span>';
            }

            $lists .= '<div class="jsb flex border-bottom padding-h10 ajax-item ' . $class . '">';
            $lists .= '<div class="">';
            $lists .= '<div class="mb6">' . $pay_title . '</div>';
            $lists .= '<div class="muted-2-color em09">订单号：' . $order_num . '</div>';
            $lists .= '<div class="muted-2-color em09">时间：' . $pay_time . '</div>';
            $lists .= '</div>';
            $lists .= '<div class="felx0 flex xx jsb"><div class="c-yellow"><span class="mr3 px12">' . zibpay_get_pay_mark() . '</span><b class="em14">' . floatval($order->income_price) . '</b></div><div class="text-right">' . $income_status . '</div></div>';
            $lists .= '</div>';
        }
        $ajax_url = add_query_arg(['action' => 'income_order_lists', 'user_id' => $user_id], admin_url('admin-ajax.php'));
        if (isset($_REQUEST['income_status'])) {
            $ajax_url = add_query_arg('income_status', $_REQUEST['income_status'], $ajax_url);
        }

        $lists .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url);
    } else {
        $lists .= zib_get_ajax_null('暂无订单', 60, 'null-order.svg');
    }

    return $lists;
}

/**
 * @description: 获取用户分成文章总数量
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_post_count($user_id)
{
    if (!$user_id) {
        return 0;
    }

    $posts_query = zibpay_get_user_income_posts_query($user_id);
    if ($posts_query && $posts_query->found_posts) {
        return $posts_query->found_posts;
    }
    return 0;
}

/**
 * @description: 我的付费商品明细
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_post_lists($user_id)
{
    if (!$user_id) {
        return;
    }
    $ice_perpage = 20;
    $orderby     = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'modified';
    $paged       = zib_get_the_paged();
    $ajax_url    = esc_url(add_query_arg(['action' => 'income_post_lists', 'user_id' => $user_id], admin_url('admin-ajax.php')));

    $posts_query = zibpay_get_user_income_posts_query($user_id, $paged, $ice_perpage, $orderby);

    $lists = '';
    if ($posts_query && $posts_query->have_posts()) {
        while ($posts_query->have_posts()): $posts_query->the_post();
            global $post;
            $post_id     = $post->ID;
            $posts_title = get_the_title();
            $permalink   = get_permalink();
            $all_income  = zibpay_get_post_income_data($post_id);
            $type_object = get_post_type_object($post->post_type);
            $type_badge  = '';
            if (isset($type_object->label)) {
                $type_badge = '<span class="badg badg-sm mr3">' . $type_object->label . '</span>';
            }

            $lists .= '<div class="zib-widget ajax-item mb10"><div class="muted-color"><a target="_blank" class="block text-ellipsis mb6" href="' . $permalink . '">' . $type_badge . $posts_title . '</a></div><div class="em09 muted-2-color">合计销售：<span class="c-yellow">' . ($all_income['count'] ?: 0) . '笔</span></div><div class="flex jsb ac em09 muted-2-color"><div class="mr20">合计收入：<span class="c-yellow">￥' . (floatval($all_income['income']) ?: 0) . '</span></div><div class="">' . zib_get_svg('time', null, 'mr6') . get_the_modified_time('Y-m-d H:i:s') . '</div></div></div>';
        endwhile;
        //帖子分页paginate
        $lists .= zib_get_ajax_next_paginate($posts_query->found_posts, $paged, $ice_perpage, $ajax_url);
    } else {
        if ($paged == 1) {
            $lists = zib_get_ajax_null('暂无付费内容', 40);
        }
    }
    wp_reset_query();

    return $lists;
}

/**
 * @description: 获取文章的分成数据
 * @param {*} $post_id
 * @return {*}
 */
function zibpay_get_post_income_data($post_id)
{

    static $all_data = null;
    if ($all_data === null) {
        $db       = ZibDB::name(zibpay::$order_table_name)->field('post_id,COUNT(*) as count,SUM(income_price) as income')->where('status', 1)->where('income_price', '>', 0)->group('post_id');
        $all_data = $db->select()->result();
    }

    if ($all_data) {
        foreach ($all_data as $data) {
            $data = (array) $data;
            if ($post_id == $data['post_id']) {
                return $data;
            }
        }
    }

    return array(
        'post_id' => $post_id,
        'count'   => 0,
        'income'  => 0,
    );
}

/**
 * @description: 获取用户今日分成统计数据
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_today_income_data($user_id, $status = 'all')
{
    $error = array(
        'count' => 0,
        'sum'   => 0,
    );
    if (!$user_id) {
        return $error;
    }

    $income_status = null;
    if ('effective' == $status) {
        $income_status = 0;
    } elseif ('invalid' == $status) {
        $income_status = 1;
    }

    $db = ZibDB::name(zibpay::$order_table_name)->field('COUNT(*) as count,SUM(income_price) as sum')->where('post_author', $user_id)->where('status', 1)->where('income_price', '>', 0)->whereTime('pay_time', 'today');
    if ($income_status !== null) {
        $db->where('income_status', $income_status);
    }

    $data = $db->select()->find()->toArray();
    if (!isset($data['count'])) {
        return $error;
    }

    return $data;
}

/**
 * @description: 获取用户分成统计数据
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_data($user_id, $status = 'all')
{
    $error = array(
        'count' => 0,
        'sum'   => 0,
        'ids'   => '',
    );
    if (!$user_id) {
        return $error;
    }

    //静态缓存
    static $this_data = null;
    if (isset($this_data[$user_id][$status])) {
        return $this_data[$user_id][$status];
    }

    $income_status = null;
    if ('effective' == $status) {
        $income_status = 0;
    } elseif ('invalid' == $status) {
        $income_status = 1;
    }

    global $wpdb;
    @$wpdb->query('SET group_concat_max_len=1024000'); //解决GROUP_CONCAT函数最大长度的限制

    $db = ZibDB::name(zibpay::$order_table_name)->field('GROUP_CONCAT(id) as ids,COUNT(*) as count,SUM(income_price) as sum')->where('post_author', $user_id)->where('status', 1)->where('income_price', '>', 0);
    if ($income_status !== null) {
        $db->where('income_status', $income_status);
    }
    $data = $db->select()->find()->toArray();

    if (!isset($data['count'])) {
        $this_data[$user_id][$status] = $error;
    } else {
        $this_data[$user_id][$status] = array(
            'count' => $data['count'] ?: 0,
            'sum'   => $data['sum'] ?: 0,
            'ids'   => $data['ids'] ?: '',
        );
    }

    return $this_data[$user_id][$status];
}

/**
 * @description: 查询用户分成比例
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_ratio($user_id)
{
    if (!$user_id) {
        return 0;
    }

    //查询独立设置的比例，有则返回
    $user_income_ratio = zib_get_user_meta($user_id, 'income_rule', true);
    if (!empty($user_income_ratio['switch'])) {
        return (int) $user_income_ratio['ratio'];
    }

    //查询用户会员级别
    $vip_l = (int) zib_get_user_vip_level($user_id);
    if ($vip_l) {
        return (int) _pz('income_rule', array(), 'ratio_vip_' . $vip_l);
    }
    return (int) _pz('income_rule', array(), 'ratio');
}

/**
 * @description: 获取用户积分的分成比例
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_points_ratio($user_id)
{
    if (!$user_id) {
        return 0;
    }

    //查询独立设置的比例，有则返回
    $user_income_ratio = zib_get_user_meta($user_id, 'income_rule', true);
    if (!empty($user_income_ratio['switch'])) {
        return (int) $user_income_ratio['points_ratio'];
    }

    //查询用户会员级别
    $vip_l = (int) zib_get_user_vip_level($user_id);
    if ($vip_l) {
        return (int) _pz('income_points_rule', array(), 'ratio_vip_' . $vip_l);
    }
    return (int) _pz('income_points_rule', array(), 'ratio');
}

/**
 * @description: 获取用户中心income的链接
 * @param {*}
 * @return {*}
 */
function zibpay_get_user_center_income_url()
{
    return zib_get_user_center_url('income');
}

/**
 * @description: 获取用户商品的查询
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_income_posts_query($user_id = 0, $paged = 1, $posts_per_page = 10, $orderby = 'modified')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return;
    }

    $query_args = array(
        'post_type'      => ['forum_post', 'plate', 'post', 'page'],
        'post_status'    => ['publish'],
        'author'         => $user_id,
        'order'          => 'DESC',
        'orderby'        => $orderby,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'meta_query'     => array(
            array(
                'key'     => 'zibpay_type',
                'value'   => 1,
                'compare' => '>=',
            ),
            array(
                'key'     => 'zibpay_price',
                'value'   => 0,
                'compare' => '>',
            ),
        ),
    );
    return new WP_Query($query_args);
}

/**
 * @description: 积分分成更新订单的分成信息
 * @param {*} $pay_order
 * @return {*}
 */
function zibpay_update_order_income_price($pay_order)
{
    if (empty($pay_order->post_author) || empty($pay_order->post_id)) {
        return;
    }

    $post_author   = $pay_order->post_author;
    $income_points = zibpay_get_order_income_points($pay_order);
    if (!$income_points) {
        return;
    }

    //如果有积分分成
    $data = array(
        'order_num' => $pay_order->order_num, //订单号
        'value'     => $income_points, //值 整数为加，负数为减去
        'type'      => '积分分成',
        'desc'      => zibpay_get_pay_type_name($pay_order->order_type), //说明
    );

    do_action('author_points_income', $data, $pay_order); //获得积分分成，添加挂钩
    zibpay_update_user_points($post_author, $data);

    //更新订单的分成状态
    $income_detail                  = maybe_unserialize($pay_order->income_detail);
    $income_detail['withdraw_time'] = current_time('Y-m-d H:i:s');
    $income_detail['withdraw_type'] = 'points_auto';

    $updata_data = [
        'income_status' => 1,
        'income_detail' => maybe_serialize($income_detail),
    ];

    ZibDb::name('zibpay_order')->where('id', $pay_order->id)->update($updata_data);
}
add_action('payment_order_success', 'zibpay_update_order_income_price', 5);

/**
 * @description: 获取订单有多少积分分成
 * @param {*} $pay_order
 * @return {*}
 */
function zibpay_get_order_income_points($pay_order)
{
    $pay_order = (array) $pay_order;
    if (!empty($pay_order['income_detail'])) {
        $income_detail = maybe_unserialize($pay_order['income_detail']);

        if (!empty($income_detail['points'])) {
            return $income_detail['points'];
        }
    }
    return 0;
}

//用户中心 tab
function zibpay_user_page_tab_content_income()
{
    return zib_get_ajax_ajaxpager_one_centent(zibpay_user_content_income());
}
add_filter('main_user_tab_content_income', 'zibpay_user_page_tab_content_income');

/**
 * @description: 获取用户中心统计卡片
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_user_content_income($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $today_data             = zibpay_get_user_today_income_data($user_id);
    $all_data               = zibpay_get_user_income_data($user_id);
    $income_ratio           = zibpay_get_user_income_ratio($user_id);
    $income_points_ratio    = zibpay_get_user_income_points_ratio($user_id);
    $income_price_invalid   = zibpay_get_user_income_data($user_id, 'invalid');
    $income_price_effective = zibpay_get_user_income_data($user_id, 'effective');

    // 卡片1

    $card = '<div class="col-sm-6"><div class="colorful-bg c-cyan zib-widget"><div class="colorful-make"></div>';
    $card .= '<div class="relative">';
    $card .= '<p class="opacity8">今日收入</p>';
    $card .= '<p class="em12">￥<b class="em2x mr10">' . (floatval($today_data['sum']) ?: '0') . '</b> ' . ($today_data['count'] && $today_data['sum'] ? $today_data['count'] . '笔' : '') . '</p>';
    $card .= '<div class="em09">现金分成 ' . ($income_ratio ?: '0') . '%' . (_pz('points_s') ? '<span class="icon-spot">积分分成 ' . ($income_points_ratio ?: '0') . '%</span>': '') . '</div>';
    $card .= '</div>';
    $card .= '</div></div>';

    // 卡片2
    $withdraw_ing = (array) zibpay_get_user_withdraw_ing($user_id);
    // 提现按钮
    $withdraw_but = '<div class="abs-right">' . zibpay_get_withdraw_link('but radius c-red', (!empty($withdraw_ing['meta']) ? '提现处理中' : '立即提现') . '<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>') . '</div>';
    $c_dec        = '累计￥' . (floatval($all_data['sum']) ?: '0');
    if (!empty($withdraw_ing['meta']['withdraw_detail']['income'])) {
        $c_dec .= zibpay_get_withdraw_link('icon-spot', '提现中￥' . floatval($withdraw_ing['meta']['withdraw_detail']['income']));
    } else {
        $__invalid = floatval($income_price_invalid['sum']);
        $c_dec .= $__invalid ? '<span class="icon-spot" data-toggle="tooltip" title="查看提现记录">' . zibpay_get_withdraw_record_link('', '已提现￥' . $__invalid . '<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>') . '</span>' : '<span class="icon-spot">已提现￥' . ($__invalid ?: '0') . '</span>';
    }

    $card .= '<div class="col-sm-6"><div class="colorful-bg c-red zib-widget"><div class="colorful-make" style="transform: rotate(36deg) scale(0.8);"></div>';
    $card .= '<div class="relative">';
    $card .= '<p class="opacity8">我的收入</p>';
    $card .= '<p class="em12">￥<b class="em2x mr10">' . (floatval($income_price_effective['sum']) ?: '0') . '</b> ' . ($income_price_effective['count'] && $income_price_effective['sum'] ? $income_price_effective['count'] . '笔' : '') . '</p>';
    $card .= '<div class="em09">' . $c_dec . '</div>';
    $card .= $withdraw_but;
    $card .= '</div>';
    $card .= '</div></div>';
    $card = '<div class="row gutters-10">' . $card . '</div>';

    //tab内容
    $tab_but = '';
    $tab_but .= '<li class="active"><a data-toggle="tab" href="#income_tab_main">创作分成</a></li>';
    //分成明细
    $tab_but .= '<li class=""><a data-toggle="tab" data-ajax="" href="#income_tab_post">我的商品</a></li>';
    $tab_but .= '<li class=""><a data-toggle="tab" data-ajax="" href="#income_tab_order">收入明细</a></li>';

    // tab-列表内容
    //tab-主内容
    $tab_content = '';
    $tab_content .= '<div class="tab-pane fade active in" id="income_tab_main">';
    $tab_content .= _pz('pay_income_desc_details');
    if (_pz('pay_income_desc_page_s')) {
        $tab_content .= '<div><a class="but c-blue" href="' . get_permalink(_pz('pay_income_desc_page')) . '">查看详细说明</a></div>';
    }
    $tab_content .= '</div>';

    //tab-我的商品明细
    $tab_content .= '<div class="tab-pane fade ajaxpager" id="income_tab_post">';
    $tab_content .= '<span class="post_ajax_trigger"><a no-scroll="1" ajax-href="' . esc_url(add_query_arg(['action' => 'income_post_lists', 'user_id' => $user_id], admin_url('admin-ajax.php'))) . '" class="ajax_load ajax-next ajax-open"></a></span>';
    $tab_content .= '<div class="post_ajax_loader"> <i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2 mb20"></p> <i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p> </div>';
    $tab_content .= '</div>';

    //tab-我的收入明细
    $tab_content .= '<div class="tab-pane fade ajaxpager" id="income_tab_order">';
    if ($all_data['count']) {
        //如果有佣金订单 则显示按钮和加载动画
        $order_ajax_href = esc_url(add_query_arg(['action' => 'income_order_lists', 'user_id' => $user_id], admin_url('admin-ajax.php')));
        $tab_content .= '<div class="mb10">';
        $tab_content .= '<a ajax-replace="1" ajax-href="' . $order_ajax_href . '" class="but mr10 ajax-next">全部 ' . $all_data['count'] . '</a>';
        $tab_content .= $income_price_invalid['count'] ? '<a ajax-replace="1" ajax-href="' . esc_url(add_query_arg('rebate_status', '1', $order_ajax_href)) . '" class="but ajax-next mr10">已提现 ' . $income_price_invalid['count'] . '</a>' : '<span class="badg mr10">已提现 0</span>';
        $tab_content .= $income_price_effective['count'] ? '<a ajax-replace="1" ajax-href="' . esc_url(add_query_arg('rebate_status', '0', $order_ajax_href)) . '" class="but ajax-next mr10">未提现 ' . $income_price_effective['count'] . '</a>' : '<span class="badg mr10">未提现 0</span>';
        $tab_content .= '</div>';
        $tab_content .= '<span class="post_ajax_trigger"><a no-scroll="1" ajax-href="' . $order_ajax_href . '" class="ajax_load ajax-next ajax-open"></a></span>';
        $tab_content .= '<div class="post_ajax_loader"> <div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p></div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div> </div>';
    } else {
        //如果没有则显示无
        $tab_content .= zib_get_null('暂无收入订单', 40, 'null-money.svg');
    }
    $tab_content .= '</div>';

    $tab = '<div class="zib-widget"><div class="padding-w10 nop-sm"><ul style="margin-bottom: 20px;" class="list-inline scroll-x mini-scrollbar tab-nav-theme font-bold">' . $tab_but . '</ul><div class="tab-content">' . $tab_content . '</div></div></div>';

    $html = $card . $tab;
    return $html;
}

//获取分成统计数据
function zibpay_get_income_statistics_totime($time_type = 'all', $status = 'all')
{

    global $wpdb;
    $thismonth_time_where = zib_get_time_where_sql($time_type, 'pay_time');
    $status_where         = $status === 'all' ? '' : " and income_status = $status";
    $db_data              = (array) $wpdb->get_row("SELECT count(*) as count,SUM(income_price) as sum FROM $wpdb->zibpay_order WHERE income_price > 0 and `status` = 1 and pay_type != 'points' and $thismonth_time_where $status_where");

    $data = array(
        'count' => $db_data['count'] ?? 0,
        'sum'   => !empty($db_data['sum']) ? zib_floatval_round($db_data['sum']) : 0,
        'ids'   => '',
    );

    return $data;
}
