<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-22 19:54:34
 * @LastEditTime : 2026-05-05 22:55:41
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//页面加载前保存推荐人
function zibpay_save_referrer()
{
    $aff = !empty($_REQUEST['ref']) ? $_REQUEST['ref'] : '';
    if ($aff) {
        @session_start();
        $_SESSION['ZIBPAY_REFERRER_ID'] = $aff;
    }
}
add_action('template_redirect', 'zibpay_save_referrer', 1);

//注册时保存推荐人id
function zibpay_register_save_referrer($user_id)
{
    $referrer_id = zibpay_get_referrer_id($user_id, false);
    update_user_meta($user_id, 'referrer_id', $referrer_id);
}
add_action('user_register', 'zibpay_register_save_referrer');

//支付成功后更新佣金数据
function zibpay_payment_order_updata_rebate($pay_order)
{
    if (empty($pay_order->referrer_id)) {
        return;
    }

    return zibpay_update_order_rebate_price($pay_order);

    $rebate_ratio = zibpay_get_user_rebate_rule($pay_order->referrer_id);
    $rebate_price = 0;
    //返利比例
    if (
        $rebate_ratio['type']
        && is_array($rebate_ratio['type'])
        && is_array($rebate_ratio['ratio'])
        && (in_array('all', $rebate_ratio['type']) || in_array($pay_order->order_type, $rebate_ratio['type']))
    ) {
        //佣金
        $effective_amount = zibpay_get_order_effective_amount($pay_order);
        $rebate_price     = $effective_amount ? floor(zibpay_get_order_effective_amount($pay_order) * $rebate_ratio['ratio']) / 100 : 0;
    }

    if (!$rebate_price) {
        return;
    }

    global $wpdb;
    $where = array('id' => $pay_order->id, 'status' => 1);
    $wpdb->update($wpdb->zibpay_order, ['rebate_price' => $rebate_price], $where);
}
add_action('payment_order_success', 'zibpay_payment_order_updata_rebate', 5);

//获取带返利的链接
function zibpay_get_rebate_link($user_id = '', $url = '')
{
    if (!$url) {
        $url = home_url('/');
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $rebate_id  = $user_id;
    $rebate_url = add_query_arg('ref', $rebate_id, $url);
    return $rebate_url;
}

/**
 * @description: 根据返利id查询用户id
 * @param int $user_id 用户ID（允许为空，为空则获取当前登录用户）
 * @param bool $return_args 是否返回用户数组
 * @return {*}
 */
function zibpay_get_referrer_id($user_id = null, $return_userdata = false)
{
    //首先查询用户保存的推荐人
    //根据主题设置识别模式判断是否根据推荐人返佣

    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    $referrer_id = '';
    if (_pz('pay_rebate_judgment') != 'link') {

        if ($user_id) {
            $referrer_id = get_user_meta($user_id, 'referrer_id', true);
        }

        if ($referrer_id) {
            return $return_userdata ? get_userdata($referrer_id) : $referrer_id;
        }
    }

    //再根据缓存查询
    @session_start();
    $referrer_id = !empty($_SESSION['ZIBPAY_REFERRER_ID']) ? $_SESSION['ZIBPAY_REFERRER_ID'] : '';
    if ($referrer_id) {
        //判断是否允许自己推广自己
        if ($user_id && $referrer_id == $user_id && !_pz('pay_rebate_self_s', true)) {
            return false;
        }

        return $return_userdata ? get_userdata($referrer_id) : (int) $referrer_id;
    }

    return false;
}

//获取用户中心rebate的链接
function zibpay_get_user_center_rebate_url()
{
    return zib_get_user_center_url('rebate');
}

//获取哪些类型的订单参与返利
function zibpay_get_rebate_types()
{
    return array(
        '1' => '付费阅读',
        '2' => '付费资源',
        '3' => '产品购买',
        '4' => '购买会员',
        '5' => '付费图片',
        '6' => '付费视频',
        '7' => '自动售卡',
        '9' => '购买积分',
    );
}

//获取当前的推荐人的返利比例
function zibpay_get_the_referrer_rebate_ratio($order_type, $pay_user_id = 0)
{
    $referrer_id = zibpay_get_referrer_id($pay_user_id);
    if (!$referrer_id) {
        return false;
    }
    return zibpay_get_referrer_rebate_ratio($referrer_id, $order_type);
}

//根据订单类型，和推荐人id，获取该推荐人的返利比例
function zibpay_get_referrer_rebate_ratio($referrer_id, $order_type)
{

    if (!_pz('pay_rebate_s') || !$referrer_id || !$order_type) {
        return false;
    }

    //字串符
    if (!isset(zibpay_get_rebate_types()[$order_type])) {
        return false;
    }

    $rebate_ratio = zibpay_get_user_rebate_rule($referrer_id);

    //返利比例
    if (
        isset($rebate_ratio['type'])
        && is_array($rebate_ratio['type'])
        && $rebate_ratio['ratio']
        && (in_array('all', $rebate_ratio['type']) || in_array($order_type, $rebate_ratio['type']))
    ) {
        return $rebate_ratio['ratio'];
    }

    return false;
}

//更新订单信息
function zibpay_update_order_rebate_price($pay_order)
{
    //暂未使用
    return;

    if (empty($pay_order->referrer_id)) {
        return;
    }

    $rebate_price = 0;
    $rebate_rule  = zibpay_get_referrer_rebate_ratio($pay_order->referrer_id, $pay_order->order_type);
    //返利比例
    if ($rebate_rule) {
        //佣金
        $effective_amount = zibpay_get_order_effective_amount($pay_order);
        $rebate_price     = $effective_amount ? round($effective_amount * $rebate_rule / 100, 2) : 0;
    }

    if (!$rebate_price) {
        return;
    }

    global $wpdb;
    $where = array('order_num' => $pay_order->order_num, 'status' => 1);
    $wpdb->update($wpdb->zibpay_order, ['rebate_price' => $rebate_price], $where);
}

/**
 * @description: 查询用户的返利比例,此函数已经效验了返利开关
 * @param int    $user_id 用户ID
 * @return int   array('type' => $rebate_s, 'ratio' => $rebate_ratio)
 */
function zibpay_get_user_rebate_rule($user_id)
{
    if (!$user_id) {
        return;
    }

    //查询独立设置的比例，有则返回
    $user_rebate_rule = zib_get_user_meta($user_id, 'rebate_rule', true);
    if (!empty($user_rebate_rule['switch']) && !empty($user_rebate_rule['ratio'])) {
        if (empty($user_rebate_rule['type'])) {
            return array('type' => false, 'ratio' => $user_rebate_rule['ratio']);
        }

        $rebate_s = zibpay_user_rebate_type_format($user_rebate_rule['type']);
        return array('type' => $rebate_s, 'ratio' => $user_rebate_rule['ratio']);
    }

    //查询用户会员级别
    $vip_l = (int) zib_get_user_vip_level($user_id);
    if ($vip_l) {
        //如果是会员，查询会员功能是否开启，返回对应的比例
        $rebate_s     = zibpay_user_rebate_type_format(_pz('rebate_rule', array(), 'pay_rebate_user_s_' . $vip_l));
        $rebate_ratio = (int) _pz('rebate_rule', array(), 'pay_rebate_ratio_vip_' . $vip_l);
        if ($rebate_s) {
            return array('type' => $rebate_s, 'ratio' => $rebate_ratio);
        }

    }

    //最后查询普通用户是否开启此功能
    $rebate_s     = zibpay_user_rebate_type_format(_pz('rebate_rule', array(), 'pay_rebate_user_s'));
    $rebate_ratio = (int) _pz('rebate_rule', array(), 'pay_rebate_ratio');

    return array('type' => $rebate_s, 'ratio' => $rebate_ratio);
}

/**
 * @description: 格式化用户保存的返利订单模式
 * @param {*}
 * @return {*}
 */
function zibpay_user_rebate_type_format($array)
{
    if (!is_array($array)) {
        return false;
    }

    if (in_array('all', $array) || !empty($array['all'])) {
        return array('all');
    }
    if (count($array) == count($array, 1)) {
        return $array;
    }

    $rebate_type = array_keys($array, true);
    if (!empty($rebate_type[0])) {
        return $rebate_type;
    }
    return false;
}

/**
 * @description: 获取用户佣金统计数据
 * @param {*} $user_id
 * @param {*} $status effective  |  invalid
 * @return {*}
 */
function zibpay_get_user_rebate_data($user_id, $status = 'all')
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

    $rebate_status = null;
    if ('effective' == $status) {
        $rebate_status = 0;
    } elseif ('invalid' == $status) {
        $rebate_status = 1;
    }

    global $wpdb;
    @$wpdb->query('SET group_concat_max_len=1024000'); //解决GROUP_CONCAT函数最大长度的限制

    $db = ZibDB::name(zibpay::$order_table_name)->field('GROUP_CONCAT(id) as ids,COUNT(*) as count,SUM(rebate_price) as sum')->where('referrer_id', $user_id)->where('status', 1)->where('rebate_price', '>', 0);
    if ($rebate_status !== null) {
        $db->where('rebate_status', $rebate_status);
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

//获取佣金统计数据
function zibpay_get_rebate_statistics_totime($time_type = 'all', $status = 'all')
{

    global $wpdb;
    $thismonth_time_where = zib_get_time_where_sql($time_type, 'pay_time');
    $status_where         = $status === 'all' ? '' : " and rebate_status = $status";
    $db_data              = (array) $wpdb->get_row("SELECT count(*) as count,SUM(rebate_price) as sum FROM $wpdb->zibpay_order WHERE rebate_price > 0 and `status` = 1 and pay_type != 'points' and $thismonth_time_where $status_where");

    $data = array(
        'count' => $db_data['count'] ?? 0,
        'sum'   => !empty($db_data['sum']) ? zib_floatval_round($db_data['sum']) : 0,
        'ids'   => '',
    );

    return $data;
}

/**
 * @description: 获取用户允许的返利订单类型的文案
 * @param array $rebate_type 订单类型的数组
 * @param string $delimiter 分割字符
 * @return $name
 */
function zibpay_get_user_rebate_type($rebate_type, $delimiter = '<\br>')
{
    if (!$rebate_type || !is_array($rebate_type)) {
        return '暂未参与';
    }

    if (in_array('all', $rebate_type)) {
        $name = '全部订单';
    } else {
        $i    = 1;
        $name = '';
        foreach ($rebate_type as $key) {
            $delimiter_1 = (1 != $i ? $delimiter : '');
            $name .= $delimiter_1 . zibpay_get_pay_type_name($key);
            $i++;
        }
    }
    return $name;
}

//用户中心 tab
function zibpay_user_page_tab_content_rebate()
{
    return zib_get_ajax_ajaxpager_one_centent(zibpay_user_content_rebate());
}
add_filter('main_user_tab_content_rebate', 'zibpay_user_page_tab_content_rebate');

function zibpay_user_content_rebate($user_id = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return;
    }

    $rebate_url   = zibpay_get_rebate_link($user_id);
    $rebate_rule  = zibpay_get_user_rebate_rule($user_id);
    $rebate_ratio = $rebate_rule['type'] ? ($rebate_rule['ratio'] ? $rebate_rule['ratio'] : 0) : 0;
    $rebate_type  = zibpay_get_user_rebate_type($rebate_rule['type'], '<span class="icon-spot"></span>');

    $rebate_all_data       = zibpay_get_user_rebate_data($user_id, 'all');
    $rebate_effective_data = zibpay_get_user_rebate_data($user_id, 'effective');
    $rebate_invalid_data   = zibpay_get_user_rebate_data($user_id, 'invalid');

    //分类金额
    $rebate_price_all       = $rebate_all_data['sum'];
    $rebate_price_effective = $rebate_effective_data['sum'];
    $rebate_price_invalid   = $rebate_invalid_data['sum'];

    //分类计数
    $rebate_count_all       = $rebate_all_data['count'];
    $rebate_count_effective = $rebate_effective_data['count'];
    $rebate_count_invalid   = $rebate_invalid_data['count'];

    //文案
    $text_desc          = _pz('pay_rebate_text_desc');
    $text_details_title = _pz('pay_rebate_text_details_title', '返佣规则及说明');
    $text_details       = _pz('pay_rebate_text_details');
    $pay_vip_but        = '';

    //顶部标题
    $title = '<div class="box-body">';
    $title .= '<div class="title-h-left"><b>推荐奖励</b></div>';
    $title .= $text_desc ? '<div class="muted-2-color">' . $text_desc . '</div>' : '';
    $title .= '</div>';

    // 佣金比例卡片
    $card = '<div class="col-sm-6"><div class="zib-widget jb-red relative-h" style="background-size:120%;">';
    $card .= '<div class="absolute jb-red radius" style="height: 145%;left: 70%;width: 76%;top: -77%;"></div>';
    $card .= '<div class="absolute jb-red radius" style="height: 183%;width: 81%;left: -26%;border-radius: 300px;"></div>';
    $card .= '<div class="relative">';
    $card .= '<p class="opacity8">佣金比例</p>';
    $card .= '<p class="em12"><b style="font-size:2em;">' . $rebate_ratio . '</b> %</p>';
    $card .= '<div class="em09">' . $rebate_type . '</div>';
    $card .= '</div>';
    $card .= '</div></div>';

    // 累计佣金卡片
    // 显示提现中数据
    $withdraw_ing = (array) zibpay_get_user_withdraw_ing($user_id);
    // 提现按钮
    $withdraw_but = '<div class="abs-right">' . zibpay_get_withdraw_link('but radius c-white', (!empty($withdraw_ing['meta']) ? '提现处理中' : '立即提现') . '<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>') . '</div>';

    $c_dec = '<span>累计￥' . floatval($rebate_price_all) . '</span>';
    if (!empty($withdraw_ing['meta']['withdraw_detail']['rebate'])) {
        $c_dec .= zibpay_get_withdraw_link('icon-spot', '提现中￥' . floatval($withdraw_ing['meta']['withdraw_detail']['rebate']));
    } else {
        $c_dec .= $rebate_price_invalid ? '<span class="icon-spot" data-toggle="tooltip" title="查看提现记录">' . zibpay_get_withdraw_record_link('', '已提现￥' . floatval($rebate_price_invalid) . '<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>') . '</span>' : '<span class="icon-spot">已提现￥' . floatval($rebate_price_invalid) . '</span>';
    }

    $card .= '<div class="col-sm-6"><div class="zib-widget jb-blue relative-h">';
    $card .= '<div class="absolute jb-blue radius" style=" height: 150%; left: 50%; opacity: 0.5; top: 50%; width: 60%; "></div>';
    $card .= '<div class="absolute jb-blue radius" style=" height: 145%; left: -22%; opacity: .8; width: 89%; "></div>';
    $card .= '<div class="relative">';
    $card .= '<p class="opacity8">我的佣金</p>';
    $card .= '<p class="em12">￥ <b class="em2x mr10">' . floatval($rebate_price_effective) . '</b>' . ($rebate_count_effective ? $rebate_count_effective . '笔' : '') . '</p>';

    $card .= '<div class="em09">' . $c_dec . '</div>';
    $card .= $withdraw_but;
    $card .= '</div>';
    $card .= '</div></div>';

    $show_user_s = _pz('pay_rebate_judgment') === 'all' && _pz('pay_rebate_show_users');
    // tab按钮
    $tab_but = '';
    $tab_but .= '<li class="active"><a data-toggle="tab" href="#rebate_tab_main">佣金详情</a></li>';
    $tab_but .= '<li class=""><a data-toggle="tab" data-ajax="" href="#rebate_tab_detail">佣金明细</a></li>'; //佣金明细
    $tab_but .= $show_user_s ? '<li class=""><a data-toggle="tab" data-ajax="" href="#rebate_tab_user">推荐用户</a></li>' : ''; //推荐用户

    $tab_but = '<b><ul style="margin-bottom: 20px;" class="list-inline scroll-x mini-scrollbar tab-nav-theme">' . $tab_but . '</ul></b>';

    // tab-列表内容
    //推荐二维码
    $rebate_poster = '';
    if (_pz('pay_rebate_poster_s', 1)) {
        $rebate_poster = '<a poster-share="rebate_' . $user_id . '" data-user="' . $user_id . '" href="javascript:;" class="clip-aut mb10 but c-cyan ml10"><i class="fa fa-qrcode"></i>推广海报</a>';
    }

    $tab_con   = '';
    $info_lits = '';
    $info_lits .= '<div class="mb10 mt10"><div class="author-set-left">推广链接</div><div class="author-set-right"><b data-clipboard-tag="推广链接" data-clipboard-text="' . $rebate_url . '" class="but mb10 c-red clip-aut mr10">' . $rebate_url . '</b><a data-clipboard-tag="推广链接" data-clipboard-text="' . $rebate_url . '" class="clip-aut mb10 but c-yellow">复制链接</a>' . $rebate_poster . '</div></div>';
    $info_lits .= '<div class="mb20"><div class="author-set-left">佣金比例</div><div class="author-set-right"><b class="badg mr10 c-red-2">' . $rebate_ratio . '%</b>' . $pay_vip_but . '</div></div>';
    $info_lits .= '<div class="mb20"><div class="author-set-left">返佣订单</div><div class="author-set-right"><b class="badg">' . $rebate_type . '</b></div></div>';
    $info_lits .= '<div class="mb20"><div class="author-set-left">累计佣金</div><div class="author-set-right"><b class="badg c-blue mr6 mb6">累计￥' . $rebate_price_all . '</b><span class="badg mr6 mb6">待提现￥' . $rebate_price_effective . '</span></div></div>';
    $info_lits .= $rebate_price_invalid ? '<div class="mb20"><div class="author-set-left">已提现</div><div class="author-set-right"><b class="badg c-blue mr6 mb6">￥' . $rebate_price_invalid . '</b>' . zibpay_get_withdraw_record_link('but mb6') . '</div></div>' : '';

    $info_lits = '<div class="rebate-lits">' . $info_lits . '</div>';
    //返佣介绍
    $info_desc = $text_details ? '<div class="title-h-left mb10"><b>' . $text_details_title . '</b></div><div class="muted-color">' . $text_details . '</div>' : 0;

    $tab_con .= '<div class="tab-pane fade active in" id="rebate_tab_main">' . $info_lits . $info_desc . '</div>';

    //佣金明细
    $detail           = '';
    $detail_ajax_href = esc_url(add_query_arg('action', 'rebate_detail', admin_url('admin-ajax.php')));
    //头部按钮
    if ($rebate_count_all) {
        //如果有佣金订单 则显示按钮和加载动画
        $detail .= '<div class="mb10">';
        $detail .= '<a ajax-replace="1" ajax-href="' . $detail_ajax_href . '" class="but mr10 ajax-next">全部 ' . $rebate_count_all . '</a>';
        $detail .= $rebate_count_invalid ? '<a ajax-replace="1" ajax-href="' . esc_url(add_query_arg('rebate_status', '1', $detail_ajax_href)) . '" class="but ajax-next mr10">已提现 ' . $rebate_count_invalid . '</a>' : '<span class="badg mr10">已提现 ' . $rebate_count_invalid . '</span>';
        $detail .= $rebate_count_effective ? '<a ajax-replace="1" ajax-href="' . esc_url(add_query_arg('rebate_status', '0', $detail_ajax_href)) . '" class="but ajax-next mr10">未提现 ' . $rebate_count_effective . '</a>' : '<span class="badg mr10">未提现 ' . $rebate_count_effective . '</span>';
        $detail .= '</div>';
        $detail .= '<span class="post_ajax_trigger"><a no-scroll="1" ajax-href="' . $detail_ajax_href . '" class="ajax_load ajax-next ajax-open"></a></span>';
        $detail .= '<div class="post_ajax_loader"> <div class="mt20 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p></div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div> </div>';
    } else {
        //如果没有则显示无
        $detail .= zib_get_null('暂无佣金订单', 40, 'null-money.svg');
    }

    $tab_con .= '<div class="tab-pane fade ajaxpager" id="rebate_tab_detail">' . $detail . '</div>';

    //推荐用户
    if ($show_user_s) {
        $user_lists_ajax_href = esc_url(add_query_arg('action', 'rebate_users', admin_url('admin-ajax.php')));
        $user_lists           = '';

        $user_lists .= '<span class="post_ajax_trigger"><a no-scroll="1" ajax-href="' . $user_lists_ajax_href . '" class="ajax_load ajax-next ajax-open"></a></span>';
        $user_lists .= '<div class="post_ajax_loader"> <div class="mt20 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p></div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div><div class="mt10 mb20"><i class="placeholder s1" style=" height: 20px; "></i><i class="placeholder s1 ml10" style=" height: 20px; width: 120px; "></i> <p class="placeholder k1"></p><p class="placeholder k2"></p>  </div> </div>';

        $tab_con .= '<div class="tab-pane fade ajaxpager text-center rebate-user-lists" id="rebate_tab_user">' . $user_lists . '</div>';
    }

    //汇总tab_con内容
    $tab_con = '<div class="tab-content">' . $tab_con . '</div>';

    $html = '<div class="row gutters-10">' . $card . '</div>';
    $html .= '<div class="zib-widget nop-sm">';
    $html .= $title;
    $html .= '<div class="box-body">' . $tab_but . $tab_con . '</div>';
    $html .= '</div>';

    return $html;
}
