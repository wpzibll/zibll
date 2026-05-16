<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-04-17 17:49:02
 * @LastEditTime : 2026-05-06 10:10:02
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户余额系统 balance
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户余额
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_balance($user_id)
{
    $balance = get_user_meta($user_id, 'balance', true);
    return floatval(round((float) $balance, 2));
}

/**
 * @description: 获取用户提现中的余额
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_withdraw_ing_balance($user_id)
{
    $balance = get_user_meta($user_id, 'balance_withdraw_ing', true);
    return floatval($balance);
}

/**
 * @description: 获取用户待转入的
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_add_ing_balance($user_id)
{
    $balance = get_user_meta($user_id, 'balance_add_ing', true);
    return abs(floatval($balance));
}

/**
 * @description: 获取充值模态框的按钮
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zibpay_get_balance_charge_link($class = '', $con = '充值')
{
    $user_id = get_current_user_id();
    if (!$user_id || !_pz('pay_balance_s')) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => 'balance-charge-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'balance_charge_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取购买余额金额限制
 * @param {*}
 * @return {*}
 */
function zibpay_get_pay_balance_product_custom_limit()
{
    $option = _pz('pay_balance_product_custom_limit', array('min' => 10, 'max' => 500));

    return array(
        'min' => floatval($option['min']),
        'max' => floatval($option['max']),
    );
}

/**
 * @description: 用户余额变动统一接口
 * @param {*} $user_id
 * @param {*} $data
 * @return {*}
 */
function zibpay_update_user_balance($user_id, $data)
{
    $defaults = array(
        'order_num' => '', //订单号
        'value'     => 0, //值 整数为加，负数为减去
        'type'      => '', //中文说明
        'desc'      => '', //说明
        'time'      => current_time('Y-m-d H:i'),
    );
    $data          = wp_parse_args($data, $defaults);
    $data['value'] = floatval(round((float) $data['value'], 2));

    if (!$user_id || $data['value'] === 0) {
        return false;
    }

    $old_value = 0;
    if (!zibpay_user_balance_or_points_save_db('balance', $user_id, $data['value'], $old_value)) {
        return false;
    }

    $user_balance    = $old_value;
    $data['balance'] = $user_balance + $data['value']; //记录当前余额
    $data['balance'] = zib_floatval_round($data['balance']); //最大两位小数

    if ($data['balance'] < 0) {
        $data['balance'] = 0;
    }

    $record = zib_get_user_meta($user_id, 'balance_record', true);
    if (!$record || !is_array($record)) {
        $record = array();
    }

    $max        = 50; //最多保存多少条记录
    $record     = array_slice($record, 0, $max - 1, true); //数据切割，删除多余的记录
    $new_record = array_merge(array($data), $record);

    zib_update_user_meta($user_id, 'balance_record', $new_record);
    return true;
}

/**
 * @description: 更新用户余额或积分数据库
 * @param {string} $type 类型:balance,points
 * @param {number} $user_id 用户ID
 * @param {number} $val 值
 * @return {boolean} true:成功,false:失败
 */
function zibpay_user_balance_or_points_save_db($type, $user_id, $val, &$old_value = 0)
{
    if (!$type || !$user_id || !abs($val)) {
        return false;
    }

    $meta_key_args = [
        'balance' => 'balance',
        'points'  => 'points',
    ];

    if (!isset($meta_key_args[$type])) {
        return false;
    }

    $meta_key = $meta_key_args[$type];

    global $wpdb;
    //第一步：先获取储存id，判断是否存在此数据，不存在则为第一次首次存储
    $old_meta = zibDB::table($wpdb->usermeta)->where(['user_id' => $user_id, 'meta_key' => $meta_key])->find()->result();

    $abs_value = abs($val);
    $old_value = 0;
    if (empty($old_meta->user_id)) {
        //第一次首次存储
        if ($val <= 0) {
            return false;
        }

        //增加余额
        $sql      = "INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES (%d, %s, %f)";
        $sql_args = [$user_id, $meta_key, zib_floatval_round($abs_value)];
    } else {
        $old_value = floatval($old_meta->meta_value);

        //直接更新数据库，避免并发
        if ($val > 0) {
            //增加余额
            $sql      = "UPDATE {$wpdb->usermeta} SET meta_value = meta_value + %f WHERE user_id = %d AND meta_key = %s";
            $sql_args = [zib_floatval_round($abs_value), $user_id, $meta_key];
        } else {
            //减少余额
            $sql      = "UPDATE {$wpdb->usermeta} SET meta_value = meta_value - %f WHERE user_id = %d AND meta_key = %s AND meta_value >= %f";
            $sql_args = [zib_floatval_round($abs_value), $user_id, $meta_key, zib_floatval_round($abs_value)];
        }
    }

    if (!$wpdb->query($wpdb->prepare($sql, $sql_args))) {
        return false;
    }

    if (strstr($sql, 'INSERT')) {
        $mid = $wpdb->insert_id;
        do_action('added_user_meta', $mid, $user_id, $meta_key, $old_value + $val);
    } else {
        do_action('updated_user_meta', $old_meta->umeta_id, $user_id, $meta_key, $old_value + $val);
    }

    wp_cache_delete($user_id, 'user_meta');

    return true;
}

/**
 * @description: 用户充值的模态框内容
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_balance_charge_modal($user_id)
{

    $current_user      = get_userdata($user_id);
    $desc              = _pz('pay_balance_desc');
    $desc              = $desc ? '<div class="muted-box muted-2-color padding-10 mb10 em09">' . $desc . '</div>' : '';
    $product           = _pz('pay_balance_product');
    $custom_s          = _pz('pay_balance_product_custom_s', true);
    $custom_limit      = zibpay_get_pay_balance_product_custom_limit();
    $mark              = zibpay_get_pay_mark();
    $custom_limit_html = !empty($custom_limit['min']) ? '最低充值' . $mark . $custom_limit['min'] : '';
    $custom_limit_html .= $custom_limit_html ? '，' : '';
    $custom_limit_html .= !empty($custom_limit['max']) ? '最高充值' . $mark . $custom_limit['max'] : '';
    $default_pay_price = 0; //默认支付金额
    $custom_product    = '<div class="" data-for="balance_product" data-value="custom">
    <div class="relative flex ab">
        <span class="ml6 mr10 muted-color shrink0">' . $mark . '</span>
        <input class="line-form-input em16 key-color" style="padding: 1px;" name="custom_price" type="number" ' . (!empty($custom_limit['min']) ? ' limit-min="' . $custom_limit['min'] . '"' : '') . (!empty($custom_limit['max']) ? ' limit-max="' . $custom_limit['max'] . '"' : '') . ' warning-max="最高可充值1$元" warning-min="最低需充值1$元">
        <i class="line-form-line"></i>
    </div>
    <div class="muted-2-color em09 mt6">' . $custom_limit_html . '</div></div>';

    $header = '<div class="mb10 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="mr6 em14">' . zib_get_svg('money-color-2') . '</span>余额充值</b></div>';

    $product_html = '';
    $product_id   = 0;
    if ($product && is_array($product)) {
        foreach ($product as $product_i => $product_v) {
            $price      = $product_v['price'];
            $show_price = $product_v['pay_price'] ?: $price;
            if ($product_i === 0) {
                $default_pay_price = $show_price; //默认支付金额
            }

            $vip_tag = $product_v['tag'];
            $vip_tag = $vip_tag ? '<div class="abs-right vip-tag badg ' . ($product_v['tag_class'] ?: 'jb-yellow') . '">' . $vip_tag . '</div>': '';

            $product_html .= '<div class="zib-widget vip-product relative product-box' . ($product_i === 0 ? ' active' : '') . '"  data-for="balance_product" data-value="' . $product_i . '">' . $vip_tag . '
        <div class="em14"><span class="px12">' . $mark . '</span>' . $price . '</div>
        <div class="c-red"><span class="px12">￥</span><span class="">' . $show_price . '</span>' . ($show_price < $price ? '<span class="ml6 c-yellow smail">省' . ($price - $show_price) . '</span>' : '') . '</div>
        </div>';
        }
    }

    if ($product_html) {
        $product_html = '<div class="muted-color mb6">请选择充值金额</div>' . $product_html;
        if ($custom_s) {
            $product_html .= '<div class="muted-color mt20 mb6">自定义充值金额</div>' . $custom_product;
        }
    }

    if (!$product_html) {
        $product_id   = 'custom';
        $product_html = '<div class="muted-color mb6">请输入充值金额</div>' . $custom_product;
    }

    //卡密支付
    if (_pz('pay_balance_pass_charge_s')) {
        add_filter('zibpay_is_allow_card_pass_pay', '__return_true'); //添加卡密充值
        add_filter('zibpay_card_pass_payment_desc', function () {
            $password_desc = _pz('pay_balance_pass_charge_desc');
            return $password_desc ? '<div class="muted-box muted-2-color padding-10 mb10 em09">' . $password_desc . '</div>' : '';
        });
        $payment_methods = zibpay_get_payment_methods(8);
        if (count($payment_methods) <= 1) {
            $product_html = '';
        }
    }

    $charge_html = $product_html ? '<div class="charge-box mb20">' . $product_html . '</div>' : '';
    $charge_html .= $desc;

    $hidden = '<input type="hidden" name="balance_product" value="' . $product_id . '">';
    $hidden .= '<input type="hidden" name="order_type" value="8">';
    $pay_button = zibpay_get_initiate_pay_input(8);

    $form = '<form class="balance-charge-form mini-scrollbar scroll-y max-vh7">' . $charge_html . $hidden . $pay_button . '</form>';

    $html = '';
    $html .= $header . $form;

    return $html;
}

/**
 * @description: 为支付方式添加卡密支付
 * @param {*} $payment_methods
 * @return {*}
 */
function zibpay_payment_methods_add_password($payment_methods)
{
    $payment_methods[] = 'password';
    return $payment_methods;
}

/**
 * @description: 支付成功后，对余额功能的相关处理
 * @param {*} $pay_order
 * @return {*}
 */
function zibpay_payment_order_balance($pay_order)
{
    $order_type = $pay_order->order_type;
    if ($order_type == 8) {
        //如果是余额充值
        $product_id = $pay_order->product_id;
        if (!$product_id) {
            $charge_price = $pay_order->order_price;
        } else {
            $product      = _pz('pay_balance_product');
            $product_id   = str_replace('balance_', '', $product_id);
            $charge_price = $product[$product_id]['price'];
        }

        $data = array(
            'order_num' => $pay_order->order_num, //订单号
            'value'     => $charge_price, //值 整数为加，负数为减去
            'type'      => '充值',
            'desc'      => '', //说明
        );
        zibpay_update_user_balance($pay_order->user_id, $data);
    }
}

if (_pz('pay_balance_s')) {
    add_action('payment_order_success', 'zibpay_payment_order_balance', 8); //支付成功后更新数据
}

/**
 * @description: 自动创建充值的卡密
 * @param {*} $num
 * @param {*} $price
 * @param {*} $rand_number
 * @param {*} $rand_password
 * @return {*}
 */
function zibpay_generate_pass_card($type = 'balance_charge', $num = 20, $meta = array(), $rand_number = 20, $rand_password = 35, $other = '')
{
    $time = current_time('mysql');

    for ($i = 1; $i <= $num; $i++) {
        ZibCardPass::add(array(
            'card'          => ZibCardPass::rand_number($rand_number),
            'password'      => ZibCardPass::rand_password($rand_password),
            'type'          => $type,
            'create_time'   => $time,
            'modified_time' => $time,
            'status'        => '0', //正常
            'meta'          => $meta,
            'other'         => $other,
        ));
    }

    return true;
}

/**
 * @description: 获取卡密的充值金额
 * @param {*} $db
 * @return {*}
 */
function zibpay_get_recharge_card_price($db)
{
    $db   = (array) $db;
    $meta = maybe_unserialize($db['meta']);

    return isset($meta['price']) ? $meta['price'] : 0;
}

/**
 * @description: 通过卡号和卡密查找是否有对应的卡密
 * @param {*} $card
 * @param {*} $pass
 * @return {*}
 */
function zibpay_get_recharge_card($card, $password, $only_password = false)
{

    $get_args = array(
        'card'     => $card,
        'password' => $password,
        'type'     => 'balance_charge',
    );

    if ($only_password) {
        unset($get_args['card']);
    }

    $msg_db = ZibCardPass::get_row($get_args);
    return $msg_db;
}

/**
 * @description: 使用卡密
 * @param {*} $args
 * @return {*}
 */
function zibpay_use_card_pass($zibpay_card_pass, $order_num, $new_meta = array())
{

    if (!is_array($zibpay_card_pass->meta)) {
        $zibpay_card_pass->meta = array();
    }

    $data = array(
        'id'        => $zibpay_card_pass->id,
        'status'    => 'used',
        'order_num' => $order_num,
        'meta'      => array_merge($zibpay_card_pass->meta, $new_meta),
    );

    return ZibCardPass::update($data);
}

/**
 * @description: 获取用户余额使用记录
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_balance_record_lists($user_id = 0)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $record = (array) zib_get_user_meta($user_id, 'balance_record', true);
    $lists  = '';

    foreach ($record as $k => $v) {
        if (isset($v['value']) && isset($v['balance'])) {
            $_class = $v['value'] < 0 ? 'c-red' : 'c-blue';
            $badge  = '<span class="badg badg-sm mr6 ' . $_class . '">' . $v['type'] . '</span>';
            $lists .= '<div class="border-bottom padding-h10 flex jsb">';
            $lists .= '<div class="muted-2-color">';
            $lists .= '<div class="mb6">' . $badge . $v['desc'] . '</div>';
            $lists .= $v['order_num'] ? '<div class="em09">订单号：' . $v['order_num'] . '</div>' : '';
            $lists .= $v['time'] ? '<div class="em09">时间：' . $v['time'] . '</div>' : '';
            $lists .= '</div>';
            $lists .= '<div class="flex jsb xx text-right flex0 ml10 ab"><b class="em12 ' . $_class . '">' . ($v['value'] < 0 ? $v['value'] : '+' . $v['value']) . '</b><div class="em09 muted-2-color">余额：' . $v['balance'] . '</div></div>';
            $lists .= '</div>';
        }
    }

    if (!$lists) {
        $lists = zib_get_null('暂无余额记录', 42, 'null-order.svg');
    } else {
        if (count($record) > 49) {
            $lists .= '<div class="text-center mt20 muted-3-color">最多显示近50条记录</div>';
        }
    }
    return $lists;
}

/**
 * @description: 用户钱包卡片
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_wallet_mini_box($user_id = 0, $class = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }
    $points_s      = _pz('points_s');
    $pay_balance_s = _pz('pay_balance_s');
    if (!$points_s && !$pay_balance_s) {
        return;
    }

    $box = '';
    if ($pay_balance_s) {
        $user_balance = _cut_count(zibpay_get_user_balance($user_id));

        $box .= '<a rel="nofollow" style="flex: 1;" class="muted-box padding-6 flex1" href="' . zib_get_user_center_url('balance') . '">
                    <div class="muted-2-color px12">余额<i class="ml6 fa fa-angle-right em12"></i></div>
                    <div class="flex jsb"><span class="font-bold c-blue-2">' . $user_balance . '</span>' . zib_get_svg('money-color-2', null, 'em14') . '</div>
                </a>';

    }
    if ($points_s) {
        $user_points = _cut_count(zibpay_get_user_points($user_id));

        $box .= '<a rel="nofollow" style="flex: 1;" class="muted-box padding-6 flex1" href="' . zib_get_user_center_url('balance') . '">
                    <div class="muted-2-color px12">积分<i class="ml6 fa fa-angle-right em12"></i></div>
                    <div class="flex jsb"><span class="font-bold c-yellow">' . $user_points . '</span>' . zib_get_svg('points-color', null, 'em14') . '</div>
                </a>';
    }

    return '<div class="flex ab jsb col-ml6">' . $box . '</div>';
}

//用户中心挂钩
function zibpay_user_content_balance($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }
    $card          = '';
    $tab_but       = '';
    $tab_content   = '';
    $points_s      = _pz('points_s');
    $pay_balance_s = _pz('pay_balance_s');
    $card_i        = 0;

    if ($pay_balance_s) {
        $card_i++;
        $pay_balance_withdraw_s = _pz('pay_balance_withdraw_s');
        $mark                   = zibpay_get_pay_mark();
        $user_balance           = zibpay_get_user_balance($user_id);
        $charge_link            = zibpay_get_balance_charge_link('but b-blue-2', '充值');
        $withdraw_ing           = zibpay_get_user_withdraw_ing_balance($user_id);
        $add_ing                = zibpay_get_user_add_ing_balance($user_id); //带转入余额
        $transfer_link          = zibpay_get_transfer_link('balance', 'but c-blue-2 mr6 hollow');
        $withdraw_record_link   = '';
        $right_top_btn          = '';
        if ($pay_balance_withdraw_s) {
            $withdraw_record_link = zibpay_get_withdraw_record_link('icon-spot');
            if (!$transfer_link) {
                $transfer_link = zibpay_get_withdraw_link('but c-blue-2 mr6 hollow', '提现' . ($withdraw_ing ? '处理中<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>' : ''));
            } else {
                $right_top_btn = zibpay_get_withdraw_link('muted-color px12', '提现' . ($withdraw_ing ? '处理中' : '') . '<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>');
            }
        }
        $card .= '<div class="col-sm-6">
                <div class="zib-widget relative" style="padding-left: 24px;">
                    <div class="flex jsb">
                        <div class="mb6">
                        ' . zib_get_svg('money-color-2', null, 'em12 mr6') . '余额
                        </div>
                        ' . $right_top_btn . '
                    </div>
                    <div class="flex jsb ac withdraw-amount-box">
                        <div class="mb6 c-blue-2">' . $mark . '<span class="font-bold ml6 ' . (strlen((string) $user_balance) > 7 ? 'em2x' : 'em3x') . '">' . $user_balance . '</span></div>
                        <div class="flex0">' . $transfer_link . $charge_link . '</div>
                    </div>
                    <div class="muted-2-color em09">' . ($add_ing ? '<span data-toggle="tooltip" title="提现待转入">待转入 ' . $add_ing . '</span>' : '<span>待转入 ' . $add_ing . '</span>') . $withdraw_record_link . '</div>
                </div>
            </div>';

        // tab-列表内容：余额变动
        $tab_but .= '<li class="active"><a data-toggle="tab" href="#record_tab_balance">余额记录</a></li>';
        $tab_content .= '<div class="tab-pane fade active in" id="record_tab_balance">';
        $tab_content .= zibpay_get_user_balance_record_lists($user_id);
        $tab_content .= '</div>';
    }

    if ($points_s) {
        $card_i++;
        $user_points     = zibpay_get_user_points($user_id);
        $points_pay_link = zibpay_get_points_pay_link('but c-green', '购买积分');
        $transfer_link   = zibpay_get_transfer_link('points', 'but c-green mr6 hollow');
        $right_top_btn   = zib_get_user_checkin_btn('muted-color px12', '签到领积分<i class="fa fa-angle-right em12 ml3"></i>', '今日已签到<i class="fa fa-angle-right em12 ml3"></i>');
        $bottom_link     = zibpay_get_vip_points_exchange_link('muted-2-color em09', '积分兑换会员<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i>', 'div') ?: '<div class="muted-2-color em09 pointer" data-onclick="[href=\'#record_tab_points_free\']">做任务赚积分<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i></div>';

        $card .= '<div class="col-sm-6">
                    <div class="zib-widget relative" style="padding-left: 24px;">
                        <div class="flex jsb">
                            <div class="mb6">
                            ' . zib_get_svg('points-color', null, 'em12 mr6') . '积分
                            </div>
                            ' . $right_top_btn . '
                        </div>
                        <div class="flex jsb ac withdraw-amount-box">
                            <div class="mb6 c-green">' . zib_get_svg('points') . '<span class="font-bold ml6 ' . (strlen((string) $user_points) > 7 ? 'em2x' : 'em3x') . '">' . $user_points . '</span></div>
                            <div class="flex0">' . $transfer_link . $points_pay_link . '</div>
                        </div>
                        ' . $bottom_link . '
                    </div>
                </div>';
        // tab-列表内容：积分变动
        $tab_but .= '<li class="' . (!$pay_balance_s ? 'active' : '') . '"><a data-toggle="tab" href="#record_tab_points">积分记录</a></li>';
        $tab_but .= '<li class=""><a data-toggle="tab" href="#record_tab_points_free">积分任务</a></li>';

        $tab_content .= '<div class="tab-pane fade' . (!$pay_balance_s ? '  active in' : '') . '" id="record_tab_points">' . zibpay_get_user_points_record_lists($user_id) . '</div>';
        $tab_content .= '<div class="tab-pane fade" id="record_tab_points_free">' . zib_get_points_free_lists($user_id) . '</div>';
        $tab_content .= '<div class="tab-pane fade" id="tab_points_date">' . zib_get_user_free_points_date_detail_lists($user_id) . '</div>';
    }

    if ($card_i < 2) {
        $card = str_replace('class="col-sm-6"', 'class="col-sm-12"', $card);
    }

    $html = '<div class="row gutters-10">' . $card . '</div><div class="zib-widget"><div class="padding-w10 nop-sm"><ul class="list-inline scroll-x mini-scrollbar tab-nav-theme font-bold">' . $tab_but . '</ul><div class="tab-content">' . $tab_content . '</div></div></div>';

    return $html;
}

function zibpay_user_page_tab_content_balance()
{
    return zib_get_ajax_ajaxpager_one_centent(zibpay_user_content_balance());
}
add_filter('main_user_tab_content_balance', 'zibpay_user_page_tab_content_balance');

/**
 * @description: 获取转账的链接
 * @param {*} $type 类型 points|balance
 * @param {*} $class
 * @param {*} $recipient
 * @param {*} $con
 * @return {*}
 */
function zibpay_get_transfer_link($type = 'points', $class = '', $recipient = 0, $con = '转账')
{

    $user_id = get_current_user_id();
    if (!$user_id || ($type === 'points' && (!_pz('points_s') || !_pz('points_transfer_s'))) || ($type === 'balance' && (!_pz('pay_balance_s') || !_pz('pay_balance_transfer_s')))) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => 'transfer-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action'    => 'pay_transfer_modal',
            'type'      => $type,
            'recipient' => $recipient,
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zibpay_get_pay_transfer_modal($type = 'points', $recipient = 0)
{

    $user_id = get_current_user_id();
    $titel   = '';

    //权限判断
    if (!zib_current_user_can($type . '_transfer')) {
        return '<div class="touch">' . zib_get_nocan_info($user_id, $type . '_transfer', '无法转账', 20, 0, 220) . '</div>';
    }

    if ($type === 'balance') {
        $type_name      = '余额';
        $max_val        = zibpay_get_user_balance($user_id);
        $icon_svg       = zib_get_svg('money-color-2');
        $mark           = zibpay_get_pay_mark();
        $desc           = _pz('pay_balance_transfer_desc');
        $service_charge = _pz('pay_balance_transfer_service_charge');
        $desc .= $service_charge ? '<div class="mt6 c-yellow">转账时将扣除' . $service_charge . '%的手续费</div>' : '';
    } else {
        $type_name      = '积分';
        $max_val        = zibpay_get_user_points($user_id);
        $icon_svg       = zib_get_svg('points-color');
        $mark           = zib_get_svg('points');
        $desc           = _pz('points_transfer_desc');
        $service_charge = _pz('points_service_charge');
        $desc .= $service_charge ? '<div class="mt6 c-yellow">转账时将扣除' . $service_charge . '%的手续费</div>' : '';
    }

    //余额不足，无法转账
    if (!$max_val) {
        return zib_get_modal_colorful_header('c-yellow', $icon_svg, $type_name . '转账') . '<div class="em12 text-center c-red" style="padding: 30px 0;">您的' . $type_name . '不足，暂时无法转账</div>';
    }

    //ajax搜索组件
    $search_user_con = zib_get_null('', 40, 'null-search.svg', '', 0, 150);
    $search_action   = 'transfer_user_search';
    $search          = '<div class="auto-search" ajax-url="' . zib_get_admin_ajax_url($search_action, array('type' => $type)) . '">';
    $search .= '<div class="form-right-icon">';
    $search .= '<input type="text" name="s" class="form-control search-input" tabindex="1" value="" placeholder="请输入关键词以搜索用户" autocomplete="off">';
    $search .= '<div class="search-icon abs-right">' . zib_get_svg('search') . '</div>';
    $search .= '</div>';
    $search .= '<div class="mt20 mb10 muted-3-color separator search-remind em09">搜索用户进行转账</div>';
    $search .= '<div class="search-container mini-scrollbar scroll-y max-vh5">' . $search_user_con . '</div>';
    $search .= '</div>';

    $recipient_user = zibpay_get_transfer_recipient_user_card($recipient, true);

    $form = '<div class="mb20"><a data-value="0" data-for="recipient" data-toggle="tab" href="#pay_transfer_modal_1" aria-expanded="true" class="muted-color"><i class="fa fa-angle-left em12 mr6"></i>重新选择用户</a></div>
        <div class="transfer-user"><span class="flex ac muted-box" name="recipient">' . $recipient_user . '</span></div>';

    $form .= '<div class="muted-color mt20 mb6">请输入转账金额</div>
    <div class="" data-for="balance_product" data-value="custom">
    <div class="relative flex ab">
        <span class="ml6 mr10 muted-color shrink0">' . $mark . '</span>
        <input class="line-form-input em16 key-color" style="padding: 1px;" name="price" type="number" limit-max="' . $max_val . '" limit-min="0.00001" warning-max="最高可转账1$" warning-min="金额不能为0">
        <i class="line-form-line"></i>
    </div>
    <div class="muted-2-color em09 mt6 mb10">最多可转账' . $max_val . '</div></div>';

    $form .= $desc ? '<div class="muted-box muted-2-color padding-10 mb10 em09">' . $desc . '</div>' : '';

    $form .= '<div class="mt20">
    <input type="hidden" name="type" value="' . $type . '">
    <input type="hidden" name="recipient" value="' . $recipient . '">
    <input type="hidden" name="action" value="pay_transfer">
    ' . zib_nonce_field('pay_transfer') . '
    <button class="mt6 but jb-yellow btn-block radius padding-lg wp-ajax-submit" data-confirm="确认要转账给此用户吗？ 此操作无法撤回，涉及到资金安全，请再次确认转账用户及金额正确！"><i class="fa fa-check" aria-hidden="true"></i>确认转账</button>
            </div>';

    $html = '<form><div class="tab-content">
        <a data-toggle="tab" href="#pay_transfer_modal_2" class="hide"></a>
        <div class="tab-pane fade' . ($recipient_user ? '' : ' active in') . '" id="pay_transfer_modal_1">' . $search . '</div>
        <div class="tab-pane fade' . ($recipient_user ? ' active in' : '') . '" id="pay_transfer_modal_2">' . $form . '</div>
    </div></form>';

    $header = '<div class="mb10 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="mr6 em14">' . $icon_svg . '</span>' . $type_name . '转账</b></div>';

    return $header . $html;
}

/**
 * @description: 获取转账用户的卡片
 * @param {*} $recipient
 * @return {*}
 */
function zibpay_get_transfer_recipient_user_card($user_id, $is_con = false)
{
    $user = get_userdata($user_id);
    if (!isset($user->display_name)) {
        return;
    }

    $display_name = zib_get_user_name($user_id);
    $avatar       = zib_get_avatar_box($user_id);
    $btn          = '<div class="muted-color">给他转账<i style="margin:0 0 0 6px;" class="fa fa-angle-right em12"></i></div>';
    $info         = $user->user_email ? zib_get_hide_email($user->user_email) : $user->user_login;

    $html = !$is_con ? '<div data-for="recipient" data-value="' . $user_id . '" data-onclick="[href=\'#pay_transfer_modal_2\']" class="user-info flex ac padding-h6 border-bottom pointer">' : '';
    $html .= $avatar;
    $html .= '<div class="flex1 ml10 flex ac jsb">';
    $html .= '<div class="flex1">' . $display_name . '<div class="mt3 em09 muted-2-color">' . $info . '</div></div>';
    $html .= $btn ? '<div class="flex0 em09 ml10 recipient-user-btn">' . $btn . '</div>' : '';
    $html .= '</div>';
    $html .= !$is_con ? '</div>' : '';
    return $html;
}
