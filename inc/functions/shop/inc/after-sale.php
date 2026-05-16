<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-05-07 15:59:17
 * @LastEditTime: 2025-09-25 19:38:35
 * @Project      : Zibll子比主题
 * @Description  : 一款极其优雅的Wordpress主题|订单售后处理
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取用户申请售后链接
function zib_shop_get_order_after_sale_link(array $order, $class = '', $text = '')
{
    $class = $class ? ' ' . $class : '';

    if ($order['status'] != '1') {
        return;
    }

    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);

    if (!in_array($after_sale_status, [1, 2])) {
        $after_sale_opt = zib_shop_get_order_after_sale_opt($order);
        if (empty($after_sale_opt['can_apply'])) {
            return;
        }
    }

    if (!$text) {
        if (in_array($after_sale_status, [1, 2])) {
            $text = '售后详情';
            $class .= ' c-yellow';
        } else {
            $text = '申请售后';
        }
    }

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini full-sm',
        'height'        => 320,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => array(
            'action'   => 'order_after_sale_modal',
            'order_id' => $order['id'],
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取取消售后的链接
function zib_shop_get_order_after_sale_cancel_link(array $order, $class = '', $text = '')
{
    $class = $class ? ' ' . $class : '';

    if ($order['status'] != '1') {
        return;
    }

    //判断售后状态
    $after_sale_status = zib_shop_get_order_after_sale_status($order['id']);
    if (in_array($after_sale_status, [3, 4, 5])) {
        return;
    }

    if (!$text) {
        $text = '取消申请';
    }

    $form_data = array(
        'action'   => 'order_after_sale_cancel',
        'order_id' => $order['id'],
    );

    return '<a class="wp-ajax-submit' . $class . '" form-data="' . esc_attr(json_encode($form_data)) . '" href="javascript:;" data-confirm="确定取消此售后申请？">' . $text . '</a>';
}

//获取售后退货快递的链接，此函数不做判断，需要调用者自行判断
function zib_shop_get_order_after_sale_return_express_link(array $order, $class = '', $text = '填写快递信息')
{
    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 320,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => array(
            'action'   => 'order_after_sale_express_modal',
            'order_id' => $order['id'],
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取售后记录链接
function zib_shop_get_order_after_sale_record_link(array $order, $class = '', $text = '')
{
    if (!$text) {
        $text = '售后记录';
    }

    $args = array(
        'new'           => true,
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini full-sm',
        'height'        => 320,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => array(
            'action'   => 'order_after_sale_record_modal',
            'order_id' => $order['id'],
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取售后状态
function zib_shop_get_order_after_sale_status($order_id)
{
    $after_sale_status = (int) zibpay::get_meta($order_id, 'after_sale_status');
    return $after_sale_status;
}

//获取售后状态名称
function zib_shop_get_order_after_sale_status_name($after_sale_status = '')
{

    $status_name = [
        1 => '待处理',
        2 => '处理中',
        3 => '处理完成',
        4 => '用户取消',
        5 => '商家驳回',
    ];

    if (!$after_sale_status) {
        return $status_name;
    }

    return $status_name[$after_sale_status] ?? '';
}

//获取售后进度名称
function zib_shop_get_order_after_sale_progress_name($progress = '')
{
    $progress_name = [
        1 => '等待用户发货',
        2 => '等待商家处理',
        3 => '等待用户收货',
        4 => '处理完成',
    ];

    if (!$progress) {
        return $progress_name;
    }

    return $progress_name[$progress] ?? '';
}

//获取售后类型的名称
function zib_shop_get_after_sale_type_name($type = '')
{
    $type_name = [
        'refund'        => '仅退款',
        'refund_return' => '退货退款',
        'replacement'   => '换货',
        'warranty'      => '保修',
        'insured_price' => '保价',
    ];

    if (!$type) {
        return $type_name;
    }

    return $type_name[$type] ?? '';
}

//获取售后不同类型可以选择的原因
function zib_shop_get_after_sale_type_reason($type = '')
{
    $reason_default = [
        'refund'        => [
            ['t' => '与商家协商一致'],
            ['t' => '质量问题'],
            ['t' => '商品不喜欢'],
            ['t' => '物流问题'],
            ['t' => '退运费'],
            ['t' => '其他原因'],
        ],
        'refund_return' => [
            ['t' => '不想要了'],
            ['t' => '与商家协商一致'],
            ['t' => '质量问题'],
            ['t' => '商品不喜欢'],
            ['t' => '物流问题'],
            ['t' => '其他原因'],
        ],
    ];

    $reason = _pz('after_sale_reason') ?: $reason_default;
    if ($type) {
        return $reason[$type] ?? [];
    }

    return $reason;
}

//获取售后进度
function zib_shop_get_order_after_sale_progress(array $after_sale_data)
{
    $progress = $after_sale_data['progress'] ?? 0;

    return $progress;
}

//获取待发货剩余时间
//过期时间
function zib_shop_get_order_after_sale_return_express_over_time(array $order, array &$after_sale_data)
{
    if (!isset($after_sale_data['progress']) || $after_sale_data['progress'] != 1) {
        return false;
    }

    $max_time     = _pz('order_after_sale_return_express_max_day', 7) ?: 7; //不能为0
    $current_time = current_time('Y-m-d H:i:s');
    //返回最后到期时间，过期时间
    $last_time = strtotime('+' . $max_time . ' day', strtotime($after_sale_data['author_handle_time']));
    if (strtotime($current_time) > $last_time) {
        //更新订单状态为失效
        $after_sale_data['progress']      = 4;
        $after_sale_data['status']        = 4; //4 用户取消
        $after_sale_data['cancel_remark'] = '退货超时自动取消';

        zib_shop_after_sale_to_end($order, $after_sale_data);

        return 'over';
    }

    return $last_time;
}

//获取订单可以申请的售后类型
function zib_shop_get_order_after_sale_opt(array $order)
{
    $product_id      = $order['post_id'];
    $after_sale_opt  = zib_shop_get_product_after_sale_opt($product_id);
    $order_data      = zibpay::get_meta($order['id'], 'order_data');
    $shipping_status = zib_shop_get_order_shipping_status($order['id']); //0:未发货 1:已发货 2:已收货

    //确认收货时间
    $receive_time = $order_data['shipping_data']['receive_time'] ?? '';
    $receive_time = $receive_time ? strtotime($order_data['shipping_data']['receive_time']) : false;
    $current_time = strtotime(current_time('Y-m-d H:i:s'));
    $can_apply    = [];

    if ($shipping_status == 0) {
        //未发货
        $can_apply[] = 'refund'; //仅退款
    } elseif ($shipping_status == 1) {
        //还未确认收货
        if (!empty($after_sale_opt['refund'])) {
            $can_apply[] = 'refund'; //仅退款
        }
        //未发货不能退货退款
        if (!empty($after_sale_opt['refund_return'])) {
            $can_apply[] = 'refund_return'; //退货退款
        }
        if (!empty($after_sale_opt['insured_price'])) {
            $can_apply[] = 'insured_price'; //保价
        }
    } else {

        if (!empty($after_sale_opt['refund'])) {
            $max_day = $after_sale_opt['refund_max_day'] ?? 7;
            if (strtotime("+{$max_day} days", $receive_time) > $current_time) {
                $can_apply[] = 'refund';
            }
        }

        if (!empty($after_sale_opt['refund_return'])) {
            $max_day = $after_sale_opt['refund_return_max_day'] ?? 15;
            if (strtotime("+{$max_day} days", $receive_time) > $current_time) {
                $can_apply[] = 'refund_return';
            }
        }

        if (!empty($after_sale_opt['replacement'])) {
            $max_day = $after_sale_opt['replacement_max_day'] ?? 30;
            if (strtotime("+{$max_day} days", $receive_time) > $current_time) {
                $can_apply[] = 'replacement';
            }
        }

        if (!empty($after_sale_opt['warranty'])) {
            $max_day = $after_sale_opt['warranty_max_day'] ?? 365;
            if (strtotime("+{$max_day} days", $receive_time) > $current_time) {
                $can_apply[] = 'warranty';
            }
        }

        //保价
        if (!empty($after_sale_opt['insured_price'])) {
            $max_day = $after_sale_opt['insured_price_max_day'] ?? 15;
            if (strtotime("+{$max_day} days", $receive_time) > $current_time) {
                $can_apply[] = 'insured_price';
            }
        }
    }

    $after_sale_record = $order_data['after_sale_record'] ?? [];
    if ($after_sale_record) {
        foreach ($after_sale_record as $item) {
            //排除已同意的售后类型，相同类型只能申请一次
            if (in_array($item['type'], ['refund', 'refund_return', 'replacement', 'insured_price'])) {
                if (in_array($item['type'], $can_apply) && $item['status'] == 3) {
                    //移出can_apply
                    $can_apply = array_diff($can_apply, [$item['type']]);
                }
            }
            //退货退款后，就无法申请其他售后类型
            if ($item['type'] == 'refund_return' && $item['status'] == 3) {
                $can_apply = [];
            }
            //仅退款后，就无法申请退货退款和保价
            if ($item['type'] == 'refund' && $item['status'] == 3) {
                $can_apply = array_diff($can_apply, ['refund_return', 'insured_price']);
            }
            //申请换货后，就无法申请退货退款
            if ($item['type'] == 'replacement' && $item['status'] == 3) {
                $can_apply = array_diff($can_apply, ['refund_return']);
            }
            //保价后，就无法申请退货退款和换货
            if ($item['type'] == 'insured_price' && $item['status'] == 3) {
                $can_apply = array_diff($can_apply, ['refund_return', 'replacement']);
            }
        }
    }

    $after_sale_opt['can_apply'] = $can_apply;
    return $after_sale_opt;
}

//用户提交售后
function zib_shop_user_apply_after_sale(array $order, array $after_sale_data)
{
    $default_data = array(
        'type'            => '', //售后类型：必填
        'price'           => '', //退款金额
        'reason'          => '', //售后原因
        'remark'          => '', //售后备注
        'user_apply_time' => current_time('Y-m-d H:i:s'), //用户申请时间
    );
    $after_sale_data = array_merge($default_data, $after_sale_data);
    $order_id        = $order['id'];

    $order_data                    = zibpay::get_meta($order_id, 'order_data');
    $order_data['after_sale_data'] = $after_sale_data;

    //更新售后数据
    zibpay::update_meta($order_id, 'order_data', $order_data);
    //更新售后状态
    zibpay::update_meta($order_id, 'after_sale_status', 1);
    //更新评价状态
    if (zib_shop_get_order_comment_status($order_id) === 0) {
        zib_shop_update_order_comment_status($order_id, 2);
    }

    //更新售后类型
    zibpay::update_meta($order_id, 'after_sale_type', $after_sale_data['type']);
    //更新售后时间
    zibpay::update_meta($order_id, 'after_sale_time', $after_sale_data['user_apply_time']);

    //通知商家
    zib_shop_user_apply_after_sale_to_author($order, $order_data);
}

//商家处理售后
function zib_shop_after_sale_author_handle(array $order, $is_agreed, array $data)
{
    //更新售后状态
    $order_id                              = $order['id'];
    $order_data                            = zibpay::get_meta($order_id, 'order_data');
    $after_sale_data                       = $order_data['after_sale_data'] ?? [];
    $after_sale_type                       = $after_sale_data['type'];
    $after_sale_data['author_agreed']      = (int) $is_agreed;
    $after_sale_data['author_remark']      = $data['author_remark'] ?? '';
    $after_sale_data['author_handle_time'] = current_time('Y-m-d H:i:s');
    $after_sale_data['refund_channel']     = $data['refund_channel'] ?? '';

    if ($is_agreed) {
        //同意
        if (in_array($after_sale_type, ['refund', 'insured_price'])) {
            $after_sale_data['status'] = 3; //3 处理完成
            return zib_shop_after_sale_to_end($order, $after_sale_data); //结束售后流程
        }

        if (in_array($after_sale_type, ['refund_return', 'replacement', 'warranty'])) {
            $after_sale_data['status']         = 2; //2 商家同意：处理中
            $after_sale_data['return_address'] = $data['return_address'] ?? '';
            $after_sale_data['progress']       = 1; //进度:1 商家同意，等待用户发货

            //更新售后状态
            zibpay::update_meta($order_id, 'after_sale_status', $after_sale_data['status']);
            //更新售后时间
            zibpay::update_meta($order_id, 'after_sale_time', $after_sale_data['author_handle_time']);

            //更新售后数据
            $order_data['after_sale_data'] = $after_sale_data;
            zibpay::update_meta($order_id, 'order_data', $order_data);

            //通知用户
            zib_shop_after_sale_wait_user_return_to_user($order, $order_data);
        }

    } else {
        //拒绝，结束售后流程
        $after_sale_data['status']     = 5; //5 商家驳回
        $order_data['after_sale_data'] = $after_sale_data; //必要步骤，用于传递参数
        return zib_shop_after_sale_to_end($order, $after_sale_data);
    }
}

//用户退货快递，等待商家收货
function zib_shop_after_sale_return_express_handle(array $order, array $data)
{
    $order_id                            = $order['id'];
    $order_data                          = zibpay::get_meta($order_id, 'order_data');
    $after_sale_data                     = $order_data['after_sale_data'];
    $after_sale_data['user_return_time'] = current_time('Y-m-d H:i:s'); //用户发货时间
    $after_sale_data['progress']         = 2; //2 用户已发货，等待商家收货
    $after_sale_data['user_return_data'] = [
        'express_number'       => $data['express_number'] ?? '', //必须
        'express_company_name' => $data['express_company_name'] ?? '', //必须
        'consignee_phone'      => $after_sale_data['return_address']['phone'] ?? '', //必须
        'return_remark'        => $data['return_remark'] ?? '', //非必须
    ];

    $order_data['after_sale_data'] = $after_sale_data;
    zibpay::update_meta($order_id, 'order_data', $order_data);

    //更新售后时间
    zibpay::update_meta($order_id, 'after_sale_time', $after_sale_data['user_return_time']);

    //通知商家
    zib_shop_after_sale_user_returned_to_author($order, $order_data);
}

//退货退款处理
function zib_shop_after_sale_refund_return_author_handle(array $order, array $data)
{

    $order_data                                     = zibpay::get_meta($order['id'], 'order_data');
    $after_sale_data                                = $order_data['after_sale_data'];
    $after_sale_data['status']                      = 3;
    $after_sale_data['refund_return_author_remark'] = $data['author_remark'] ?? '';
    $after_sale_data['refund_channel']              = $data['refund_channel'] ?? '';

    zib_shop_after_sale_to_end($order, $after_sale_data);
}

//处理结束
function zib_shop_after_sale_to_end(array $order, array $after_sale_data)
{
    $default_data = array(
        'status'   => 3, //3 处理完成
        'end_time' => current_time('Y-m-d H:i:s'),
        'progress' => 4, //4 处理完成
    );

    $after_sale_data = array_merge($default_data, $after_sale_data);
    if (isset($after_sale_data['progress'])) {
        $after_sale_data['progress'] = 4;
    }

    $order_id                       = $order['id'];
    $after_sale_status              = $after_sale_data['status'];
    $order_data                     = zibpay::get_meta($order_id, 'order_data');
    $is_points                      = $order_data['pay_modo'] === 'points';
    $order_comment_status           = zib_shop_get_order_comment_status($order_id);
    $is_update_order_comment_status = false;

    //退款
    if ($after_sale_status == 3 && in_array($after_sale_data['type'], ['refund', 'insured_price', 'refund_return'])) {
        $price_data = $order_data['prices'];
        $refund     = $price_data['refund'] ?? 0;
        $refund += $after_sale_data['price'];
        $price_data['refund'] = zib_floatval_round($refund);
        $order_data['prices'] = $price_data;
        $product_title        = $order_data['product_title'];

        if ($after_sale_data['type'] == 'refund_return') {
            //退货退款，必须全额退款
            $refund_return_price      = $price_data['pay_price'];
            $price_data['refund']     = zib_floatval_round($refund_return_price);
            $order_data['prices']     = $price_data;
            $after_sale_data['price'] = $refund_return_price;
        }

        $type_name = [
            'refund'        => '商品退款',
            'refund_return' => '商品退货退款',
            'insured_price' => '商品保价',
        ];
        //退款渠道
        $refund_channel = $after_sale_data['refund_channel'] ?? '';
        if ($refund_channel === 'balance' && $after_sale_data['price'] > 0 && !$is_points) {
            //余额退款
            $user_balance_args = array(
                'order_num' => $order['order_num'], //订单号
                'value'     => $after_sale_data['price'], //值 整数为加，负数为减去
                'type'      => $type_name[$after_sale_data['type']], //中文说明
                'desc'      => '商品[' . $product_title . ']' . str_replace('商品', '', $type_name[$after_sale_data['type']]), //说明
            );

            zibpay_update_user_balance($order['user_id'], $user_balance_args);
        }

        //积分退款
        if ($is_points && $after_sale_data['price'] > 0) {
            $after_sale_data['refund_channel'] = 'points';
            $user_points_args                  = array(
                'order_num' => $order['order_num'], //订单号
                'value'     => $after_sale_data['price'], //值 整数为加，负数为减去
                'type'      => $type_name[$after_sale_data['type']], //中文说明
                'desc'      => '商品[' . $product_title . ']' . str_replace('商品', '', $type_name[$after_sale_data['type']]), //说明
            );
            zibpay_update_user_points($order['user_id'], $user_points_args);
        }

        //更新退款金额到单独的meta
        zibpay::update_meta($order_id, 'refund_price', $price_data['refund']);

        //处理订单
        $shipping_status = zib_shop_get_order_shipping_status($order_id);
        if ((!$shipping_status && $after_sale_data['type'] == 'refund') || $after_sale_data['type'] == 'refund_return') {
            //订单退单：包含：退货并退款、以及未发货退款
            zibpay::refund_order($order_id);

            //更新扣减销量
            zib_shop_update_product_sales_volume($order['post_id'], -$order_data['count']);

            //更新评价状态
            if ($order_comment_status !== 1) {
                zib_shop_update_order_comment_status($order_id, 2);
                $is_update_order_comment_status = true;
            }
        }
    }

    //更新售后状态
    zibpay::update_meta($order_id, 'after_sale_status', $after_sale_data['status']);
    //更新售后时间
    zibpay::update_meta($order_id, 'after_sale_time', $after_sale_data['end_time']);

    //更新评价状态
    if ($order_comment_status === 2 && !$is_update_order_comment_status) {
        zib_shop_update_order_comment_status($order_id, 0);
    }

    //添加售后记录
    $after_sale_record                                      = $order_data['after_sale_record'] ?? [];
    $after_sale_record[$after_sale_data['user_apply_time']] = $after_sale_data;
    $order_data['after_sale_record']                        = $after_sale_record;

    //重置售后数据
    $order_data['after_sale_data'] = $after_sale_data;
    zibpay::update_meta($order_id, 'order_data', $order_data);

    //售后处理结束，通知用户
    zib_shop_after_sale_to_end_to_user($order, $order_data);
}

//取消售后申请
function zib_shop_after_sale_cancel(array $order)
{

    $order_data                          = zibpay::get_meta($order['id'], 'order_data');
    $after_sale_data                     = $order_data['after_sale_data'] ?? [];
    $after_sale_data['status']           = 4; //4 用户取消
    $after_sale_data['user_cancel_time'] = current_time('Y-m-d H:i:s');

    zib_shop_after_sale_to_end($order, $after_sale_data);
}

//添加售后记录
function zib_shop_add_after_sale_record($order_id, $after_sale_data)
{
    $order_data                                             = zibpay::get_meta($order_id, 'order_data');
    $after_sale_record                                      = $order_data['after_sale_record'] ?? [];
    $after_sale_record[$after_sale_data['user_apply_time']] = $after_sale_data;

    $order_data['after_sale_record'] = $after_sale_record;
    zibpay::update_meta($order_id, 'order_data', $order_data);
}

function zib_shop_get_after_sale_record_timeline($after_sale_data, $_mark = '')
{
    if (!empty($after_sale_data['user_apply_time'])) {
        return [];
    }

    $timeline   = [];
    $type       = $after_sale_data['type'];
    $status     = $after_sale_data['status'];
    $type_names = [
        'refund'        => '<span class="badg badg-sm c-red">仅退款</span>',
        'refund_return' => '<span class="badg badg-sm c-yellow">退货退款</span>',
        'replacement'   => '<span class="badg badg-sm c-purple">换货</span>',
        'warranty'      => '<span class="badg badg-sm c-green-2">保修</span>',
        'insured_price' => '<span class="badg badg-sm c-yellow-2">保价</span>',
    ];
    $type_name = $type_names[$type];
    $content   = '';
    if ($after_sale_data['price'] ?? 0) {
        $content .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">申请金额</div><div class="flex0 c-red">' . $_mark . $after_sale_data['price'] . '</div></div>';
    }
    if ($after_sale_data['reason'] ?? '') {
        $content .= '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">申请原因</div><div class="ml20">' . esc_attr($after_sale_data['reason']) . '</div></div>';
    }
    if ($after_sale_data['remark'] ?? '') {
        $content .= '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">用户备注</div><div class="ml20">' . esc_attr($after_sale_data['remark']) . '</div></div>';
    }

    $timeline[] = [
        'time'    => $after_sale_data['user_apply_time'],
        'title'   => '用户发起申请' . $type_name,
        'content' => $content,
    ];

    if (!empty($after_sale_data['author_handle_time'])) {
        $author_agreed = isset($after_sale_data['author_agreed']) ? (int) $after_sale_data['author_agreed'] : -1;
        $agreed_text   = $author_agreed ? '<span class="c-green">商家同意</span>' : '<span class="c-red">商家拒绝</span>';
        $content       = '';
        if (!empty($after_sale_data['return_address']['name'])) {

        }

        $timeline[] = [
            'time'  => $after_sale_data['author_handle_time'],
            'title' => $agreed_text,
        ];
    }

    $content = '';
    if ($status <= 1) {
        $timeline[] = [
            'time'  => '',
            'title' => '等待商家处理',
        ];
        return $timeline;
    } elseif ($status == 2) {
        $timeline[] = [
            'time'  => '',
            'title' => '等待用户发货',
        ];
    } else {
    }
}

//获取售后记录列表
function zib_shop_get_after_sale_record_lists($order_id)
{

    $order_data        = zibpay::get_meta($order_id, 'order_data');
    $after_sale_record = $order_data['after_sale_record'] ?? [];
    if (!$after_sale_record) {
        return '';
    }

    $is_points = $order_data['pay_modo'] === 'points';
    $_mark     = '<span class="pay-mark px12">' . ($is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark()) . '</span>';

    $record_html  = '';
    $status_names = array(
        '1' => '<span class="badg badg-sm c-yellow">待处理</span>',
        '2' => '<span class="badg badg-sm c-blue">处理中</span>',
        '3' => '<span class="badg badg-sm c-green">已完成</span>',
        '4' => '<span class="badg badg-sm">已取消</span>',
        '5' => '<span class="badg badg-sm c-red">已驳回</span>',
    );

    // 按时间倒序排序
    krsort($after_sale_record);

    foreach ($after_sale_record as $time => $record) {
        $status             = $record['status'] ?? 1;
        $type               = $record['type'] ?? '';
        $price              = $record['price'] ?? 0;
        $reason             = $record['reason'] ?? '';
        $remark             = $record['remark'] ?? '';
        $author_remark      = $record['author_remark'] ?? '';
        $author_agreed      = isset($record['author_agreed']) ? (int) $record['author_agreed'] : -1;
        $user_apply_time    = $record['user_apply_time'] ?? '';
        $author_handle_time = $record['author_handle_time'] ?? '';
        $end_time           = $record['end_time'] ?? '';
        $type_name          = zib_shop_get_after_sale_type_name($type);

        $record_item = '<div class="after-sale-record-item mb10 muted-box padding-h10">';
        $record_item .= '<div class="flex ac jsb mb6 border-bottom padding-h6">';
        $record_item .= '<div class="flex ac"><b class="mr10">' . ($type_name ?: '售后') . '</b>' . $status_names[$status] . '</div>';
        $record_item .= '<div class="muted-2-color">' . $user_apply_time . '</div>';
        $record_item .= '</div>';

        if ($price) {
            $record_item .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">申请金额</div><div class="flex0 c-red">' . $_mark . $price . '</div></div>';
        }

        if ($reason) {
            $record_item .= '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">申请原因</div><div class="ml20">' . esc_attr($reason) . '</div></div>';
        }

        if ($remark) {
            $record_item .= '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">用户备注</div><div class="ml20">' . esc_attr($remark) . '</div></div>';
        }

        if ($author_agreed !== -1) {
            $agreed_text = $author_agreed ? '<span class="c-green">商家同意' . $type_name . '</span>' : '<span class="c-red">商家拒绝</span>';

            $record_item .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">处理结果</div><div class="flex0">' . $agreed_text . '</div></div>';

            if ($author_handle_time) {
                $record_item .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">处理时间</div><div class="flex0">' . $author_handle_time . '</div></div>';
            }

            if ($author_remark) {
                $record_item .= '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">商家备注</div><div class="ml20">' . esc_attr($author_remark) . '</div></div>';
            }
        }

        if ($status == 3) {
            $refund_channel = $record['refund_channel'] ?? '';

            if ($refund_channel) {
                $refund_channel_name = zibpay_get_refund_channel_name($refund_channel);

                $record_item .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">退款到</div><div class="flex0 c-blue-2">' . $refund_channel_name . '</div></div>';
            }
        }

        if (!empty($record['refund_return_author_remark'])) {
            $record_item .= '<div class="flex jsb padding-h6"><div class="flex0 mr10 muted-2-color">商家退货备注</div><div class="ml20">' . esc_attr($record['refund_return_author_remark']) . '</div></div>';
        }

        if ($end_time && in_array($status, [3, 4, 5]) && $end_time !== $author_handle_time) {
            $record_item .= '<div class="flex ac jsb padding-h6"><div class="flex0 mr10 muted-2-color">完成时间</div><div class="flex0">' . $end_time . '</div></div>';
        }

        $record_item .= '</div>';
        $record_html .= $record_item;
    }

    return $record_html;
}

//获取售后物流记录
function zib_shop_get_after_sale_express_data($order_id, $type = 'user_return')
{
    $meta_key       = 'order_data.after_sale_data.' . ($type === 'user_return' ? 'user_return' : 'author_return') . '_data';
    $_data          = zibpay::get_meta($order_id, $meta_key);
    $express_data   = $_data['express_data'] ?? [];
    $phone          = $_data['consignee_phone'] ?? ''; //收货人电话
    $express_number = $_data['express_number'] ?? '';

    if (empty($express_number)) {
        return $express_data;
    }

    //如果快递信息查询时间在30分钟内，则不查询
    //联网查询间隔
    $express_query_interval = (int) _pz('shop_express_query_interval') ?: 30; //30分钟
    if (!empty($express_data['query_time']) && strtotime("+ {$express_query_interval} minutes", strtotime($express_data['query_time'])) > strtotime(current_time('Y-m-d H:i:s'))) {
        return $express_data;
    }

    //远程查询快递信息
    $express_data = zib_shop_remote_query_express_data($express_number, $phone);
    if (empty($express_data['error'])) {
        unset($express_data['error']);
        unset($express_data['sdk']);
        zibpay::update_meta($order_id, $meta_key . '.express_data', $express_data);
    }

    return $express_data;
}

function zib_shop_get_after_sale_user_return_express_data($order_id)
{
    return zib_shop_get_after_sale_express_data($order_id, 'user_return');
}

function zib_shop_get_after_sale_author_return_express_data($order_id)
{
    return zib_shop_get_after_sale_express_data($order_id, 'author_return');
}
