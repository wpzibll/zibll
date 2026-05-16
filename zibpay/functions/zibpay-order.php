<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-12 21:42:08
 * @LastEditTime : 2026-05-06 10:19:12
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//提交订单
function zibpay_ajax_submit_order()
{

    $post_data      = $_POST;
    $order_type     = !empty($post_data['order_type']) ? (int) $post_data['order_type'] : 0;
    $payment_method = !empty($post_data['payment_method']) ? $post_data['payment_method'] : '';
    $user_id        = get_current_user_id();

    if (!$order_type || !$payment_method) {
        zib_send_json_error('请选择支付方式');
    }

    //准备数据
    $__data = array(
        'user_id'    => $user_id,
        'order_type' => $order_type,
        'post_id'    => !empty($post_data['post_id']) ? (int) $post_data['post_id'] : 0,
    );
    $__mate_order_data = array(
        'prices' => array(
        ),
        'count'  => 1,
    );

    //按类型整理完毕
    $_pay_detail = array(
        'payment_method' => $payment_method, //支付方式
    );
    $_other_data = [];
    switch ($order_type) {
        case 9: //购买积分
            if (!$user_id) {
                zib_send_json_error('请先登录');
            }

            if ($payment_method === 'card_pass') {
                if (_pz('points_pass_exchange_s')) {
                    //卡密支付
                    $password_card     = isset($post_data['card_pass']['card']) ? esc_sql(trim($post_data['card_pass']['card'])) : ''; //卡号
                    $password_password = isset($post_data['card_pass']['password']) ? esc_sql(trim($post_data['card_pass']['password'])) : ''; //密码

                    if (!$password_password) {
                        zib_send_json_error('请输入卡密');
                    }

                    $only_password = zibpay_card_pass_is_only_password($order_type);
                    if (!$only_password && !$password_card) {
                        zib_send_json_error('请输入卡号');
                    }

                    $get_args = array('card' => $password_card, 'password' => $password_password, 'type' => 'points_exchange');
                    if ($only_password) {
                        unset($get_args['card']);
                    }

                    //卡密查询
                    $card_db = ZibCardPass::get_row($get_args);

                    if (empty($card_db->id)) {
                        zib_send_json_error('卡号或密码错误');
                    }

                    if ($card_db->status != '0') {
                        zib_send_json_error('该卡密已使用');
                    }

                    $card_price = zibpay_get_pass_exchange_points($card_db);

                    if (!$card_price) {
                        zib_send_json_error('当前卡密可兑换的积分为0');
                    }

                    add_filter('pay_order_price_is_allow_0', '__return_true'); //卡密-允许订单金额为0

                    $__data['order_price'] = 0;
                    $__data['product_id']  = 'exchange_' . $card_db->id;

                    $__mate_order_data['card_pass_id'] = $card_db->id;

                    $GLOBALS['zibpay_card_pass'] = $card_db; //保存到全局变量
                } else {
                    zib_send_json_error('该功能已关闭');
                }
            } else {
                $product_id    = isset($post_data['product']) ? $post_data['product'] : 'custom';
                $custom_points = !empty($post_data['custom']) ? (int) $post_data['custom'] : 0;
                if ($product_id === 'custom') {
                    //自定义数额
                    if ($custom_points <= 0) {
                        zib_send_json_error('请输入积分数额');
                    }
                    $custom_limit = zibpay_get_pay_points_product_custom_limit();
                    if (!empty($custom_limit['min']) && $custom_points < $custom_limit['min']) {
                        zib_send_json_error('最低购买' . $custom_limit['min'] . '积分');
                    }
                    if (!empty($custom_limit['max']) && $custom_points > $custom_limit['max']) {
                        zib_send_json_error('最高可购买' . $custom_limit['max'] . '积分');
                    }

                    $__data['order_price'] = round(($custom_points / _pz('pay_points_rate')), 2);
                } else {
                    $product = _pz('pay_points_product');
                    if (!isset($product[$product_id])) {
                        zib_send_json_error('商品信息错误，请刷新页面后重试');
                    }

                    $__data['order_price'] = round((float) $product[$product_id]['pay_price'], 2);
                    $__data['product_id']  = 'points_' . $product_id;
                }
            }
            break;

        case 8: //余额充值
            if (!$user_id) {
                zib_send_json_error('请先登录');
            }
            if ($payment_method === 'card_pass') {
                if (_pz('pay_balance_pass_charge_s')) {
                    //卡密支付
                    $password_card     = isset($post_data['card_pass']['card']) ? esc_sql(trim($post_data['card_pass']['card'])) : ''; //卡号
                    $password_password = isset($post_data['card_pass']['password']) ? esc_sql(trim($post_data['card_pass']['password'])) : ''; //密码

                    if (!$password_password) {
                        zib_send_json_error('请输入卡密');
                    }

                    $only_password = zibpay_card_pass_is_only_password($order_type);
                    if (!$only_password && !$password_card) {
                        zib_send_json_error('请输入卡号');
                    }

                    //卡密查询
                    $recharge_card = zibpay_get_recharge_card($password_card, $password_password, $only_password);

                    if (empty($recharge_card->id)) {
                        zib_send_json_error('卡号或密码错误');
                    }

                    if ($recharge_card->status != '0') {
                        zib_send_json_error('该卡密已使用');
                    }

                    $card_price = zibpay_get_recharge_card_price($recharge_card);

                    if (!$card_price) {
                        zib_send_json_error('当前卡密可充值金额为0');
                    }

                    $__data['order_price']             = $card_price;
                    $__mate_order_data['card_pass_id'] = $recharge_card->id;

                    $GLOBALS['zibpay_card_pass'] = $recharge_card; //保存到全局变量
                } else {
                    zib_send_json_error('该功能已关闭');
                }
            } else {
                $balance_product = isset($post_data['balance_product']) ? $post_data['balance_product'] : 'custom';
                $custom_price    = !empty($post_data['custom_price']) ? round((float) $post_data['custom_price'], 2) : 0;
                if ($balance_product === 'custom') {
                    //自定义数额
                    if ($custom_price <= 0) {
                        zib_send_json_error('请输入充值金额');
                    }

                    $custom_limit = zibpay_get_pay_balance_product_custom_limit();
                    if (!empty($custom_limit['min']) && $custom_price < $custom_limit['min']) {
                        zib_send_json_error('最低充值' . $custom_limit['min']);
                    }
                    if (!empty($custom_limit['max']) && $custom_price > $custom_limit['max']) {
                        zib_send_json_error('最高充值' . $custom_limit['max']);
                    }
                    $__data['order_price'] = $custom_price;
                } else {
                    $product = _pz('pay_balance_product');
                    if (!isset($product[$balance_product])) {
                        zib_send_json_error('商品信息错误，请刷新页面后重试');
                    }

                    $price                 = round((float) $product[$balance_product]['price'], 2);
                    $pay_price             = round((float) $product[$balance_product]['pay_price'], 2);
                    $__data['order_price'] = $pay_price ?: $price;
                    $__data['product_id']  = 'balance_' . $balance_product;
                }
            }

            break;

        case 4: //会员开通、升级、续费
            if (!$user_id) {
                zib_send_json_error('请先登录');
            }

            //卡密兑换
            if ($payment_method === 'card_pass') {
                if (_pz('pay_vip_pass_charge_s')) {
                    //卡密支付
                    $password_card     = isset($post_data['card_pass']['card']) ? esc_sql(trim($post_data['card_pass']['card'])) : ''; //卡号
                    $password_password = isset($post_data['card_pass']['password']) ? esc_sql(trim($post_data['card_pass']['password'])) : ''; //密码

                    if (!$password_password) {
                        zib_send_json_error('请输入卡密');
                    }

                    $only_password = zibpay_card_pass_is_only_password($order_type);
                    if (!$only_password && !$password_card) {
                        zib_send_json_error('请输入卡号');
                    }

                    //卡密查询
                    $recharge_card = zibpay_get_vip_exchange_card($password_card, $password_password, $only_password);

                    if (empty($recharge_card->id)) {
                        zib_send_json_error('卡号或密码错误');
                    }

                    if ($recharge_card->status != '0') {
                        zib_send_json_error('该卡密已使用');
                    }

                    $vip_exchange_card_data = zibpay_get_vip_exchange_card_data($recharge_card);

                    if (!$vip_exchange_card_data['level'] && !$vip_exchange_card_data['time']) {
                        zib_send_json_error('当前卡密无法兑换会员');
                    }

                    add_filter('pay_order_price_is_allow_0', '__return_true'); //卡密-允许订单金额为0
                    $__data['order_price']             = 0;
                    $product_id_3                      = $vip_exchange_card_data['time'] === 'Permanent' ? 'Permanent' : $vip_exchange_card_data['time'] . $vip_exchange_card_data['unit'];
                    $__data['product_id']              = 'vip_' . $vip_exchange_card_data['level'] . '_' . $product_id_3 . '_exchange';
                    $GLOBALS['zibpay_card_pass']       = $recharge_card; //保存到全局变量
                    $__mate_order_data['card_pass_id'] = $recharge_card->id;
                    $__mate_order_data['vip_pay_type'] = 'exchange';
                } else {
                    zib_send_json_error('该功能已关闭');
                }
            } else {
                $vip_product_id = !empty($post_data['vip_product_id']) ? explode('_', $post_data['vip_product_id']) : '';
                if (empty($vip_product_id[0]) || !isset($vip_product_id[1]) || !isset($vip_product_id[2])) {
                    zib_send_json_error('会员数据传入错误');
                }
                $vip_action  = $vip_product_id[0];
                $vip_level   = (int) $vip_product_id[1];
                $vip_product = (int) $vip_product_id[2];
                if (!_pz('pay_user_vip_' . $vip_level . '_s', true)) {
                    zib_send_json_error('暂未提供此功能');
                }

                if ('renewvip' == $vip_action) {
                    //续费

                    //是否开启续费功能
                    if (!_pz('vip_opt', true, 'vip_renew')) {
                        zib_send_json_error('续费功能已关闭');
                    }

                    //当前会员信息判断
                    $user_vip_level = zib_get_user_vip_level($user_id);
                    if (!$user_vip_level || $user_vip_level !== $vip_level) {
                        zib_send_json_error('无法续费此会员');
                    }

                    //续费时间判断
                    $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);
                    if ('Permanent' == $vip_exp_date) {
                        zib_send_json_error('您已是永久会员，无需续费');
                    }

                    $vip_product_args = zibpay_get_vip_renew_product($vip_level);
                    if (!isset($vip_product_args[$vip_product])) {
                        zib_send_json_error('商品信息错误，请刷新页面后重试');
                    }
                    $__data['order_price']             = round($vip_product_args[$vip_product]['price'], 2);
                    $__data['product_id']              = 'vip_' . $vip_level . '_' . $vip_product . '_renew';
                    $__mate_order_data['vip_pay_type'] = 'renewvip';

                } elseif ('upgradevip' == $vip_action) {
                    //升级
                    //是否开启升级功能
                    if (!_pz('vip_opt', true, 'vip_upgrade')) {
                        zib_send_json_error('功能已关闭');
                    }

                    //当前会员信息判断
                    $user_vip_level = zib_get_user_vip_level($user_id);
                    if (!$user_vip_level || $user_vip_level >= $vip_level) {
                        zib_send_json_error('无法升级');
                    }

                    $vip_product_args = zibpay_get_vip_upgrade_product($user_id);
                    if (!isset($vip_product_args[$vip_product])) {
                        zib_send_json_error('商品信息错误，请刷新页面后重试');
                    }

                    $__data['order_price']             = round($vip_product_args[$vip_product]['price'], 2);
                    $__data['product_id']              = 'vip_' . $vip_level . '_' . $vip_product . '_upgrade';
                    $__mate_order_data['vip_pay_type'] = 'upgradevip';
                } else {
                    //购买
                    $vip_product_args = (array) _pz('vip_opt', '', 'vip_' . $vip_level . '_product');

                    if (!isset($vip_product_args[$vip_product])) {
                        zib_send_json_error('商品信息错误，请刷新页面后重试');
                    }
                    $__data['order_price']             = round($vip_product_args[$vip_product]['price'], 2);
                    $__data['product_id']              = 'vip_' . $vip_level . '_' . $vip_product . '_pay';
                    $__mate_order_data['vip_pay_type'] = 'pay';
                }
            }
            break;

        case 1: //文章：付费阅读
        case 2: //文章：付费资源
        case 5: //文章：付费图片
        case 6: //文章：付费视频
        case 7: //旧版发卡插件
            $post_id = !empty($post_data['post_id']) ? (int) $post_data['post_id'] : 0;
            if (!$post_id) {
                zib_send_json_error('商品数据获取错误');
            }

            $post = get_post($post_id);
            if (empty($post->post_author)) {
                zib_send_json_error('商品数据获取错误');
            }

            if (!$user_id && !_pz('pay_no_logged_in', true)) {
                zib_send_json_error('请先登录');
            }

            $pay_mate              = get_post_meta($post_id, 'posts_zibpay', true);
            $__data['post_author'] = $post->post_author;
            $__data['order_type']  = !empty($pay_mate['pay_type']) ? $pay_mate['pay_type'] : '';
            $__data['product_id']  = !empty($pay_mate['product_id']) ? $pay_mate['product_id'] : '';
            $__data['order_price'] = isset($pay_mate['pay_price']) ? round((float) $pay_mate['pay_price'], 2) : 0;

            if ($user_id) {
                //会员价格
                $vip_level = zib_get_user_vip_level($user_id);
                if ($vip_level && _pz('pay_user_vip_' . $vip_level . '_s', true) && isset($pay_mate['vip_' . $vip_level . '_price'])) {

                    if (!$pay_mate['vip_' . $vip_level . '_price']) {
                        zib_send_json_error('会员免费，请刷新页面', 'info');
                    }
                    $vip_price = round((float) $pay_mate['vip_' . $vip_level . '_price'], 2);
                    //会员金额和正常金额取更小值
                    $__data['order_price'] = $vip_price < $__data['order_price'] ? $vip_price : $__data['order_price'];
                }
            }

            if ($order_type == 2) {
                $price_type = !empty($_REQUEST['price_type']) ? $_REQUEST['price_type'] : '';
                if ($price_type == 'download_limit_over') {
                    $__data['order_price'] = !empty($pay_mate['download_limit_over_price']) ? round((float) $pay_mate['download_limit_over_price'], 2) : 0;
                }
            }

            $__mate_order_data['product_id']    = $post->ID;
            $__mate_order_data['product_title'] = $post->post_title;

            break;

        default:
            $__data = apply_filters('initiate_order_data_type_' . $order_type, $__data, $post_data);
            if (isset($__data['mate_order_data'])) {
                $__mate_order_data = array_merge($__mate_order_data, $__data['mate_order_data']);
                unset($__data['mate_order_data']);
            }

            if (isset($__data['payment_method'])) {
                $payment_method = $__data['payment_method'];
                unset($__data['payment_method']);
            }

            break;
    }

    if (!isset($__data['order_price'])) {
        zib_send_json_error('数据获取失败');
    }

    //订单没有金额
    //是否允许为0
    if ($__data['order_price'] <= 0 && (!apply_filters('pay_order_price_is_allow_0', false) || $__data['order_price'] != 0)) {
        zib_send_json_error('订单金额异常');
    }

    if (!empty($__data['post_author'])) {
        $__mate_order_data['author_id'] = $__data['post_author'];
    }

    //订单金额整理完毕
    $__mate_order_data['prices']['total_price'] = zib_floatval_round($__data['order_price']);
    $__mate_order_data['prices']['unit_price']  = zib_floatval_round($__mate_order_data['prices']['total_price'] / ($__mate_order_data['count'] ?: 1));
    $_total_price                               = $__mate_order_data['prices']['total_price'];
    $_pay_price                                 = $__data['order_price'];
    $_total_discount                            = 0;

    // 推荐返佣、让利功能----充值不返利。积分消费不返利
    $rebate_rule = 0;
    if (_pz('pay_rebate_s')) {
        $get_referrer_id = zibpay_get_referrer_id($user_id);
        if ($get_referrer_id) {
            //查询到推荐人
            $rebate_rule = zibpay_get_referrer_rebate_ratio($get_referrer_id, $order_type); //返利比例
            if ($rebate_rule) {
                //推广优惠
                $__data['referrer_id'] = $get_referrer_id;

                //查询推广优惠金额
                if (!empty($__data['post_id'])) {
                    $pay_mate = get_post_meta($__data['post_id'], 'posts_zibpay', true);
                    if (!empty($pay_mate['pay_rebate_discount'])) {
                        $_pay_detail['rebate_discount'] = round((float) $pay_mate['pay_rebate_discount'], 2);

                        $old_pay_price = $_pay_price;
                        $_pay_price -= $_pay_detail['rebate_discount'];
                        $_pay_price = $_pay_price < 0 ? 0 : $_pay_price; //订单最小值

                        $rebate_discount = zib_floatval_round(($__mate_order_data['prices']['total_price'] - $_pay_price));

                        $_total_discount += $rebate_discount;
                        $__mate_order_data['prices']['rebate_discount'] = $rebate_discount;
                    }
                }
            }
        }
    }

    //优惠券验证及使用：顺序必须排在后面
    $post_id     = !empty($post_data['post_id']) ? (int) $post_data['post_id'] : 0;
    $coupon_code = !empty($_REQUEST['coupon']) ? esc_sql($_REQUEST['coupon']) : '';
    if ($coupon_code && zibpay_is_allow_coupon($order_type, $post_id) && $payment_method !== 'card_pass') {
        //卡密支付不能用优惠码
        $coupon_data = zibpay_is_coupon_available($coupon_code, $order_type, $post_id);

        if (!empty($coupon_data['error'])) {
            zib_send_json_error(array('error_code' => 'coupon_error', 'msg' => $coupon_data['msg'], 'type' => 'warning'));
        }

        $old_pay_price         = $_pay_price;
        $_pay_price            = zibpay_get_coupon_order_price($_pay_price, $coupon_data);
        $_pay_price            = $_pay_price <= 0 ? 0 : $_pay_price;
        $_pay_detail['coupon'] = zib_floatval_round($old_pay_price - $_pay_price);
        $_total_discount += $_pay_detail['coupon'];

        $_other_data['coupon_id'] = $coupon_data['id'];
        $_other_data['coupon']    = array(
            'id'       => $coupon_data['id'],
            'password' => $coupon_data['password'],
            'discount' => $coupon_data['discount'],
        );

        $__mate_order_data['coupon_id']        = $coupon_data['id'];
        $__mate_order_data['coupon_data']      = $_other_data['coupon'];
        $__mate_order_data['prices']['coupon'] = $_pay_detail['coupon'];
    }

    //积分抵扣 //待处理
    /**
     * 积分抵扣，以及余额组合付款方式涉及到时差问题，可能会导致数据差错
     * 暂无有效方法，故关闭
     */
    if ($user_id && !empty($post_data['points_deduction']) && zibpay_is_allow_points_deduction($order_type)) {
        $points_deduction_rate  = _pz('points_deduction_rate', 30); //抵扣比例
        $user_points            = zibpay_get_user_points($user_id); //我的积分
        $points_deduction_price = round(($user_points / $points_deduction_rate), 2); //我的积分最高可抵扣金额

        if ($points_deduction_price >= $_pay_price) {
            //足够全额抵扣
            $_pay_detail['points_deduction'] = $_pay_price;
            //积分冻结
        } else {
            $_pay_detail['points_deduction'] = $points_deduction_price;
        }

        $__mate_order_data['prices']['points_deduction'] = $_pay_detail['points_deduction'];
        $_pay_price -= $_pay_detail['points_deduction'];
    }

    //此处顺序不能变
    //数据处理，避免精度问题
    foreach ($_pay_detail as $key => $value) {
        if (is_numeric($value)) {
            $_pay_detail[$key] = (string) round($value, 2);
        }
    }

    //保存推广佣金
    $effective_amount = $_pay_price;
    if (!empty($__data['referrer_id']) && $rebate_rule) {
        $rebate_effective_amount = $effective_amount; //分成有效金额
        $__data['rebate_price']  = $rebate_effective_amount > 0 ? round($rebate_effective_amount * $rebate_rule / 100, 2) : 0;
    }

    //保存创作分成
    if (!empty($__data['post_author']) && _pz('pay_income_s')) {
        //现金分成数据保存
        $income_ratio = zibpay_get_user_income_ratio($__data['post_author']);
        if ($income_ratio) {
            $income_effective_amount = !empty($__data['rebate_price']) ? $effective_amount - $__data['rebate_price'] : $effective_amount; //分成有效金额，需减去推荐佣金
            $income_price            = $income_effective_amount > 0 ? (string) round($income_effective_amount * $income_ratio / 100, 2) : 0;
            $__data['income_price']  = $income_price;
        }
    }

    if ($_total_discount) {
        $_pay_detail['discount'] = zib_floatval_round($_total_discount);
    }

    //价格计算完毕
    $_pay_price                                    = zib_floatval_round($_pay_price); //最终付款金额
    $_pay_detail[$payment_method]                  = $_pay_price;
    $__mate_order_data['prices']['pay_price']      = $_pay_price;
    $__mate_order_data['prices']['total_discount'] = zib_floatval_round($_total_discount);
    $__data['pay_price']                           = $_pay_price;
    $__data['pay_detail']                          = $_pay_detail;
    $__data['other']                               = $_other_data;
    $__data['meta']                                = [
        'order_data' => $__mate_order_data, //订单数据
        'pay_modo'   => $payment_method === 'points' ? 'points' : 'price',
    ];

    //准备数据，并下单
    $zibpay_payment = zibpay::add_payment([
        'method' => $payment_method,
        'price'  => $_pay_price,
    ]); //创建一个新的支付数据

    if (!$zibpay_payment) {
        zib_send_json_error(['code' => 'add_payment_error', 'msg' => '支付数据创建失败']);
    }

    $__data['payment_id'] = $zibpay_payment['id'];
    $zibpay_order         = ZibPay::add_order($__data);

    if (is_wp_error($zibpay_order)) {
        zib_send_json_error(['code' => $zibpay_order->get_error_code(), 'msg' => $zibpay_order->get_error_message()]);
    }

    if (!$zibpay_order) {
        zib_send_json_error(['code' => 'add_order_error', 'msg' => '订单创建失败']);
    }

    //准备支付数据
    $_POST['payment_id'] = $zibpay_payment['id'];
    zibpay_ajax_initiate_pay($zibpay_payment['id']); //发起支付
}
add_action('wp_ajax_submit_order', 'zibpay_ajax_submit_order');
add_action('wp_ajax_nopriv_submit_order', 'zibpay_ajax_submit_order');

//下单成功后，设置cookie
function zibpay_order_created_set_cookie($order)
{
    if (!get_current_user_id()) {
        //付费阅读、付费下载等免登陆购买
        $order = (array) $order;
        zibpay_posts_order_created_set_cookie($order);

        //未登录记录未支付数据
        $wait_pay_ids   = zibpay_get_cookie_wait_pay_ids();
        $wait_pay_ids[] = $order['id'];

        $expire = time() + (zibpay_get_order_pay_max_time() - 3) * 60;
        setcookie('zibpay_wait_pay_ids', implode(',', array_unique($wait_pay_ids)), $expire, '/', '', false);
    }
}
add_action('order_created', 'zibpay_order_created_set_cookie', 10);
