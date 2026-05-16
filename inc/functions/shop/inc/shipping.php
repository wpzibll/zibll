<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-04-08 13:44:24
 * @LastEditTime : 2026-04-21 14:55:12
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//自动发货
function zib_shop_auto_shipping(array $order)
{
    //自动发货
    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $product_id      = $order['post_id'];

    $auto_delivery                       = zib_shop_get_product_config($product_id, 'auto_delivery');
    $auto_delivery['order_id']           = $order['id'];
    $auto_delivery['options_active_str'] = $order_meta_data['options_active_str'] ?? '';
    $auto_delivery['count']              = $order_meta_data['count'] ?? 1;
    $delivery_html                       = zib_shop_get_auto_delivery_content($auto_delivery); //自动发货内容
    $delivery_html                       = apply_filters('shop_auto_delivery_content', $delivery_html, $order);
    //如果未获取到内容，则自动发货失败，通知商家手动发货
    if (!$delivery_html) {
        //通知用户联系商家
        zib_shop_auto_delivery_fail_to_user($order, $order_meta_data);

        //通知商家手动发货
        zib_shop_notify_shipping($order, $order_meta_data);
        return;
    }

    $delivery_type = $auto_delivery['type'];
    if ($delivery_type === 'opts') {
        $delivery_type = $auto_delivery['opts'][$auto_delivery['options_active_str']]['opts_type'] ?? 'fixed'; //opts_type参数兼容
    }

    //虚拟商品发货
    zib_shop_virtual_shipping($order, $delivery_html, $delivery_type);
}

//获取自动发货内容
function zib_shop_get_auto_delivery_content($auto_delivery)
{
    $delivery_html = '';
    switch ($auto_delivery['type']) {
        case 'fixed':
            //固定内容
            $delivery_html = $auto_delivery['fixed_content'] ?? '';
            break;
        case 'invit_code': //邀请码
        case 'card_pass':
            $delivery_html = zib_shop_get_auto_delivery_card_pass_content($auto_delivery);
            break;
        case 'opts':
            //按选项分别配置
            $pot_auto_delivery             = $auto_delivery['opts'][$auto_delivery['options_active_str']] ?? [];
            $pot_auto_delivery['order_id'] = $auto_delivery['order_id'];
            $pot_auto_delivery['count']    = $auto_delivery['count'];

            //对二级参数做兼容处理
            $pot_auto_delivery['type'] = $pot_auto_delivery['opts_type']; //opts_type参数兼容
            if ($pot_auto_delivery['type'] == 'invit_code') {
                $pot_auto_delivery['invit_code_mode'] = $pot_auto_delivery['opts_invit_code_mode'] ?? ''; //邀请码模式，必做的兼容
            }

            $delivery_html = zib_shop_get_auto_delivery_content($pot_auto_delivery);
            break;
    }

    return $delivery_html;
}

//虚拟商品发货
function zib_shop_virtual_shipping(array $order, $delivery_html, $delivery_type = '')
{
    $order_meta_data                  = zibpay::get_meta($order['id'], 'order_data');
    $order_meta_data['shipping_data'] = $order_meta_data['shipping_data'] ?? [];

    $current_time                     = current_time('mysql');
    $order_meta_data['shipping_data'] = array_merge($order_meta_data['shipping_data'], [
        'delivery_time'    => $current_time, //发货时间
        'delivery_content' => $delivery_html, //发货内容
        'delivery_type'    => $delivery_type, //发货类型
    ]);

    //更新发货状态:直接确认收货，无需先设为已发货
    // zib_shop_update_order_shipping_status($order['id'], 1); //设置为已发货

    zibpay::update_meta($order['id'], 'order_data', $order_meta_data);
    zib_shop_order_receive_confirm($order['id'], 'auto', '虚拟商品自动确认收货', $order_meta_data);

    //发送消息
    zib_shop_virtual_shipping_to_user($order, $order_meta_data);
}

//获取快递状态
function zib_shop_get_express_state(array $order)
{
    $express_data = zib_shop_get_express_data($order);

    if (!$express_data) {
        return '';
    }

    if (!isset($express_data['state'])) {
        return '';
    }

    if (!empty($express_data['signed'])) {
        return '已签收';
    }

    if (!empty($express_data['state'])) {
        return $express_data['state'];
    }

    return '';
}

//获取快递信息
function zib_shop_get_express_data(array $order)
{

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $shipping_data   = $order_meta_data['shipping_data'] ?? [];
    $express_data    = $order_meta_data['express_data'] ?? [];
    $consignee       = $order_meta_data['consignee'] ?? [];
    $shipping_type   = $order_meta_data['shipping_type'] ?? '';
    $delivery_type   = $shipping_data['delivery_type'] ?? '';

    //联网查询间隔
    $express_query_interval = (int) _pz('shop_express_query_interval') ?: 30; //30分钟

    if ($shipping_type == 'auto') {
        return false;
    }

    if ($delivery_type == 'express' && (empty($shipping_data['express_number']))) {
        return false;
    }

    //如果快递信息查询时间在30分钟内，则不查询
    if (!empty($express_data['query_time']) && strtotime("+ {$express_query_interval} minutes", strtotime($express_data['query_time'])) > strtotime(current_time('Y-m-d H:i:s'))) {
        return $express_data;
    }

    //如果状态为已签收，则不查询
    if (!empty($express_data['signed'])) {
        return $express_data;
    }

    if ($delivery_type === 'express') {
        //远程查询快递信息
        $express_data = zib_shop_remote_query_express_data($shipping_data['express_number'], $consignee['address_data']['phone']);

        if (empty($express_data['error'])) {
            unset($express_data['error']);
            unset($express_data['sdk']);

            //更新快递信息
            $order_meta_data['express_data'] = $express_data;
            zibpay::update_meta($order['id'], 'order_data', $order_meta_data);
        }
    }

    if (empty($express_data['traces'])) {
        $express_data['traces'] = zib_shop_get_default_express_traces($order);
    }

    return $express_data;
}

//获取物流跟踪记录
function zib_shop_get_default_express_traces(array $order)
{
    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    $express_data    = $order_meta_data['express_data'] ?? [];
    $traces          = $express_data['traces'] ?? [];

    if (empty($traces)) {
        // 如果没有物流信息，则根据发货状态创建基本的物流跟踪记录
        $pay_time        = $order['pay_time'] ?? '';
        $delivery_time   = $order_meta_data['shipping_data']['delivery_time'] ?? '';
        $receive_time    = $order_meta_data['shipping_data']['receive_time'] ?? '';
        $shipping_status = zib_shop_get_order_shipping_status($order['id']);
        $delivery_type   = $order_meta_data['shipping_data']['delivery_type'] ?? '';
        $delivery_remark = $order_meta_data['shipping_data']['delivery_remark'] ?? '';

        $traces = array();

        // 添加支付信息
        if ($pay_time) {
            $traces[] = array(
                'time'    => $pay_time,
                'context' => '用户完成支付',
            );
        }

        // 添加发货信息
        if ($delivery_time && $shipping_status >= 1) {
            $traces[] = array(
                'time'    => $delivery_time,
                'context' => '商家已发货' . ($delivery_type == 'no_express' ? '（商家选择无需物流发货）' : '') . ($delivery_remark ? '，商家备注：' . $delivery_remark : ''),
                'state'   => '已发货',
            );
        }

        // 添加收货信息
        if ($receive_time && $shipping_status >= 2) {
            $traces[] = array(
                'time'    => $receive_time,
                'context' => '用户已确认收货',
                'state'   => '已收货',
            );
        }

        $traces = array_reverse($traces);
    }

    return $traces;
}

//获取订单的物流类型
function zib_shop_get_order_delivery_type($order_id)
{
    return zibpay::get_meta($order_id, 'order_data.shipping_data.delivery_type');
}

//远程查询快递信息
function zib_shop_remote_query_express_data($express_number, $phone = '')
{
    $express_data = ZibExpress::query($express_number, $phone);
    return $express_data;
}

/**
 * 获取卡密自动发货内容
 * @param array $auto_delivery_config 自动发货配置 , 自动发货配置包含type, invit_code_key, card_pass_key , order_id , count
 * @return string 自动发货内容
 */
function zib_shop_get_auto_delivery_card_pass_content($auto_delivery_config)
{

    $type   = $auto_delivery_config['type'];
    $count  = $auto_delivery_config['count'] ?? 1;
    $is_add = false;
    //自动创建在发货
    if ($type === 'invit_code') {
        $invit_code_mode = $auto_delivery_config['invit_code_mode'] ?? '';
        if ($invit_code_mode === 'auto_add') {
            $is_add = true;

            //自动创建的参数
            $invit_code_auto_add_opts = $auto_delivery_config['invit_code_auto_add_opts'] ?? [];

            $remarks = !empty($invit_code_auto_add_opts['auto_remarks']) ? $invit_code_auto_add_opts['auto_remarks'] : '';
            $reward  = !empty($invit_code_auto_add_opts['auto_reward']) ? $invit_code_auto_add_opts['auto_reward'] : '';

            if (!$remarks) {
                $remarks = 'invit_auto_create_' . current_time('YmdHis');
            }

            //执行创建
            $db_data = zib_generate_invit_code($count, 8, $reward, $remarks);
        }
    }

    //搜索发货
    if (!$is_add) {
        $where_other = $type === 'invit_code' ? $auto_delivery_config['invit_code_key'] ?? '' : $auto_delivery_config['card_pass_key'] ?? '';
        if (!$where_other) {
            return '';
        }

        $where = array(
            'other'  => $where_other,
            'status' => '0',
        );

        if ($type === 'invit_code') {
            $where['type'] = 'invit_code';
        }

        $db_data = ZibCardPass::get($where, 'id', 0, $count, 'ASC');
    }

    if (!$db_data || !isset($db_data[0])) {
        return '';
    }
    $delivery_html = '';
    foreach ($db_data as $card_pass_data) {
        $card_pass_data         = (array) $card_pass_data;
        $card_pass_data['meta'] = maybe_unserialize($card_pass_data['meta']);
        $card_pass_data['other'] .= '_shipped_' . $auto_delivery_config['order_id'];
        $card_pass_data['meta']                     = is_array($card_pass_data['meta']) ? $card_pass_data['meta'] : [];
        $card_pass_data['meta']['shipped_time']     = current_time('Y-m-d H:i:s');
        $card_pass_data['meta']['shipped_order_id'] = $auto_delivery_config['order_id'];

        ZibCardPass::update($card_pass_data);

        if ($type == 'invit_code') {
            $delivery_html .= '<div class="flex ac" style="font-weight: bold; display: flex; align-items: center;margin: 5px;"><span>邀请码： </span><span class="badg c-blue" style="color: #2997f7; background: rgba(41, 151, 247, 0.1); border-radius: 4px; padding: .3em .6em;" data-clipboard-tag="邀请码" data-clipboard-text="' . esc_attr($card_pass_data['password']) . '">' . $card_pass_data['password'] . '</span></div>';
        } else {
            $card_name = '卡号';
            $pass_name = '卡密';
            if (in_array($card_pass_data['type'], array('vip_coupon', 'coupon'))) {
                $pass_name = '优惠码';
            }

            if (!empty($card_pass_data['meta']['custom_name']['card_name'])) {
                $card_name = $card_pass_data['meta']['custom_name']['card_name'];
            }
            if (!empty($card_pass_data['meta']['custom_name']['password_name'])) {
                $pass_name = $card_pass_data['meta']['custom_name']['password_name'];
            }

            $delivery_html .= $card_pass_data['card'] ? '<div class="flex ac" style="font-weight: bold; display: flex; align-items: center;margin: 5px;"><span>' . $card_name . '： </span><span class="badg c-blue" style="color: #2997f7; background: rgba(41, 151, 247, 0.1); border-radius: 4px; padding: .3em .6em;" data-clipboard-tag="卡号" data-clipboard-text="' . esc_attr($card_pass_data['card']) . '">' . $card_pass_data['card'] . '</span></div>' : '';
            $delivery_html .= '<div class="flex ac" style="font-weight: bold; display: flex; align-items: center;margin: 5px;"><span>' . $pass_name . '： </span><span class="badg c-blue" style="color: #2997f7; background: rgba(41, 151, 247, 0.1); border-radius: 4px; padding: .3em .6em;" data-clipboard-tag="卡密" data-clipboard-text="' . esc_attr($card_pass_data['password']) . '">' . $card_pass_data['password'] . '</span></div>';
        }
    }

    return $delivery_html;
}

//通知商家发货
function zib_shop_notify_shipping($order, $order_meta_data = null)
{
    //通知商家发货
    if (!$order_meta_data) {
        $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    }

    //更新发货时间
    zib_shop_update_order_shipping_time($order['id']);
    add_action('shop_notify_shipping', $order);

    return zib_shop_notify_shipping_to_author($order, $order_meta_data);
}

//修改发货信息
function zib_shop_update_order_shipping($order, $shipping_data)
{
    $order_meta_data                  = zibpay::get_meta($order['id'], 'order_data');
    $order_meta_data['shipping_data'] = $order_meta_data['shipping_data'] ?? [];
    $order_meta_data['shipping_data'] = array_merge($order_meta_data['shipping_data'], $shipping_data);

    if (isset($order_meta_data['express_data'])) {
        unset($order_meta_data['express_data']);
    }

    zibpay::update_meta($order['id'], 'order_data', $order_meta_data);
}

//手动发货
function zib_shop_manual_shipping($order, $shipping_data)
{

    $order_meta_data = zibpay::get_meta($order['id'], 'order_data');
    if ($order_meta_data['shipping_type'] === 'auto') {
        $delivery_html = $shipping_data['delivery_content'];
        if (!empty($shipping_data['delivery_remark'])) {
            $delivery_html .= '<div class="muted-2-color">商家备注：' . $shipping_data['delivery_remark'] . '</div>';
        }

        zib_shop_virtual_shipping($order, $delivery_html, $shipping_data['delivery_type']);
        return true;
    } elseif ($order_meta_data['shipping_type'] === 'manual') {

    }

    //是否存在旧的发货快递单号
    $old_express_number = $order_meta_data['shipping_data']['express_number'] ?? '';

    //快递发货
    $delivery_type   = $shipping_data['delivery_type'];
    $delivery_remark = $shipping_data['delivery_remark'];

    if (!$delivery_remark && $old_express_number) {
        $delivery_remark = '商家更新发货快递信息';
    }

    $current_time = current_time('mysql');
    if (!isset($order_meta_data['shipping_data'])) {
        $order_meta_data['shipping_data'] = [];
    }
    $order_meta_data['shipping_data'] = array_merge($order_meta_data['shipping_data'], [
        'delivery_time'   => $current_time, //发货时间
        'receive_time'    => '', //收货时间
        'delivery_type'   => $delivery_type, //发货类型
        'delivery_remark' => $delivery_remark, //发货备注
    ]);

    //快递发货
    if ($delivery_type === 'express') {
        $order_meta_data['shipping_data']['express_number']       = $shipping_data['express_number'];
        $order_meta_data['shipping_data']['express_company_name'] = $shipping_data['express_company_name'];
    }

    //更新发货状态
    zib_shop_update_order_shipping_status($order['id'], 1); //设置为已发货
    zibpay::update_meta($order['id'], 'order_data', $order_meta_data); //更新订单数据

    //更新发货时间
    zib_shop_update_order_shipping_time($order['id']);

    if (!$old_express_number) {
        //如果旧的快递单号存在，则不发送邮件，避免重复发送
        zib_shop_manual_shipping_to_user($order, $order_meta_data);
    }
}

//确认收货统一接口
function zib_shop_order_receive_confirm($order_id, $type = 'user', $note = '', $order_meta_data = null)
{
    //确认收货
    $order           = zibpay::get_order($order_id);
    $order_meta_data = $order_meta_data ?: zibpay::get_meta($order_id, 'order_data');
    $current_time    = current_time('mysql');
    $order_user_id   = $order['user_id'] ?? 0;

    //更新收货时间，收货备注
    $order_meta_data['shipping_data'] = $order_meta_data['shipping_data'] ?? [];
    $order_meta_data['shipping_data'] = array_merge($order_meta_data['shipping_data'], [
        'receive_time'   => $current_time,
        'receive_type'   => $type,
        'receive_remark' => $note,
    ]);

    //收货：虚拟赠品自动生效
    $gift_data = $order_meta_data['gift_data'] ?? [];
    if ($gift_data && $order_user_id) {
        foreach ($gift_data as $gift) {
            switch ($gift['gift_type']) {
                case 'vip_1':
                case 'vip_2':

                    $vip_level      = zib_get_user_vip_level($order_user_id);
                    $gift_vip_level = $gift['gift_type'] === 'vip_1' ? 1 : 2;
                    //已经是会员的，不能降级
                    if ($vip_level && $vip_level > $gift_vip_level) {
                        break;
                    }

                    $new_date          = current_time('Y-m-d h:i:s');
                    $user_vip_exp_date = $vip_level ? get_user_meta($order_user_id, 'vip_exp_date', true) : $new_date;
                    //已经是永久会员的，不在变化
                    if ($user_vip_exp_date === 'Permanent') {
                        break;
                    }

                    $new_vip_exp_date = ($gift['vip_time'] == 'Permanent' || $gift['vip_time'] == 'permanent') ? 'Permanent' : date('Y-m-d 23:59:59', strtotime('+ ' . $gift['vip_time'] . ' days', strtotime($user_vip_exp_date)));
                    $data             = array(
                        'vip_level' => $gift_vip_level, //等级
                        'exp_date'  => $new_vip_exp_date, //有效截至时间
                        'type'      => '购买商品赠送', //中文说明
                        'order_num' => $order['order_num'], //订单号
                        'desc'      => '', //说明
                    );
                    zibpay_update_user_vip($order_user_id, $data);
                    break;

                case 'auth':
                    //认证资格，如果用户没有认证资格，则添加认证资格
                    if (!zib_is_user_auth($order_user_id)) {
                        $auth_args = [
                            'name' => $gift['auth_info']['name'],
                            'desc' => $gift['auth_info']['desc'] ?? '',
                        ];
                        zib_add_user_auth($order_user_id, $auth_args);
                    }
                    break;

                case 'level_integral':
                    //经验值
                    zib_add_user_level_integral($order_user_id, $gift['level_integral'], 'shop_gift', true);
                    break;
                case 'points':
                    //积分
                    $points_args = [
                        'value'     => $gift['points'],
                        'type'      => '购买商品赠送',
                        'desc'      => '',
                        'order_num' => $order['order_num'],
                    ];
                    zibpay_update_user_points($order_user_id, $points_args);
                    break;
                case 'product':
                    //暂未启用

                    break;
                case 'other':
                    //无需处理

                    break;
            }

        }
    }

    //设置为已收货
    zib_shop_update_order_shipping_status($order_id, 2);

    //初始化评论状态
    zib_shop_init_order_comment_status($order);

    //更新发货时间
    zib_shop_update_order_shipping_time($order_id);

    zibpay::update_meta($order_id, 'order_data', $order_meta_data);
}

//处理物流上下文数据，通过正则表达式替换手机号，为html
function zib_shop_handle_express_context($context)
{
    //替换手机号
    // 匹配手机号（11位数字）和座机号（区号-号码格式）
    $context = preg_replace('/(1\d{10})|(\d{3,4}-\d{7,8})/', '<a class="focus-color" href="tel:$0">$0</a>', $context);
    return $context;
}

//获取发货类型名称
function zib_shop_get_delivery_type_name($delivery_type)
{
    $args = array(
        'express'    => '快递',
        'no_express' => '无需物流',
        'auto'       => '自动发货',
        'fixed'      => '虚拟资源',
        'invit_code' => '邀请码',
        'card_pass'  => '卡密',
        'opts'       => '虚拟资源',
        'manual'     => '虚拟内容',
    );
    return $args[$delivery_type] ?? '';
}

function zib_shop_update_order_shipping_time($order_id)
{
    //更新物流时间
    zibpay::update_meta($order_id, 'shipping_time', current_time('Y-m-d H:i:s'));
}
