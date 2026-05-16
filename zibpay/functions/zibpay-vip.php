<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-04-07 13:50:03
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 判断用户可以购买的会员类型
 * @param {*}
 * @return {*}
 */
function zibpay_is_pay_vip_type($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $vip_level = zib_get_user_vip_level($user_id);

    $type = array(
        'pay'     => false,
        'renew'   => false,
        'upgrade' => false,
    );

    if ($vip_level) {
        //如果已经是会员
        $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);

        if ('Permanent' != $vip_exp_date && _pz('vip_opt', true, 'vip_renew')) {
            //续费
            if (_pz('pay_user_vip_' . $vip_level . '_s', true)) {
                $type['renew'] = $vip_level;
            }
        }
        if ($vip_level < 2 && _pz('vip_opt', true, 'vip_upgrade') && _pz('pay_user_vip_2_s', true)) {
            //升级
            if ('Permanent' == $vip_exp_date) {
                $type['upgrade'] = 'Permanent';
            } else {
                $type['upgrade'] = 'unit';
            }
        }
    } else {
        if (_pz('pay_user_vip_1_s', true)) {
            $type['pay'] = 1;
        }
        if (_pz('pay_user_vip_2_s', true)) {
            $type['pay'] = 2;
        }
    }
    return $type;
}

/**
 * @description: 用户中心的会员卡片
 * @param {*}
 * @return {*}
 */
function zibpay_user_vip_box($user_id)
{
    $vip_level = zib_get_user_vip_level($user_id);
    $vip_name  = array(1 => _pz('pay_user_vip_1_name'), 2 => _pz('pay_user_vip_2_name'));
    $html      = '';
    if ($vip_level) {

        $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);

        $vip_desc = 'Permanent' == $vip_exp_date ? '永久有效' : '到期时间：' . date('Y年m月d日', strtotime($vip_exp_date));
        $html .= '<div class="title-h-left"><b>我的会员</b></div>';
        $html .= '<div class="muted-2-color c-red">已开通' . $vip_name[$vip_level] . '，' . $vip_desc . '</div>';

        $html = '<div class="row gutters-5 mb20">';
        $html .= '<div class="col-sm-8">';
        $html .= zibpay_get_viped_card($user_id);
        $html .= '</div>';
        $html .= '</div>';

        //已经开通会员且还未到期
        $renew_card   = false;
        $upgrade_card = false;
        if ('Permanent' != $vip_exp_date && _pz('vip_opt', true, 'vip_renew')) {
            //续费会员
            $renew_card = zibpay_get_vip_card($vip_level, array('type' => 'renew'));
        }
        if ($vip_level < 2 && _pz('vip_opt', true, 'vip_upgrade')) {
            //如果当前会员等级小于2，则显示升级
            $upgrade_card = zibpay_get_vip_card(2, array('type' => 'upgrade'));
        }

        if ($renew_card || $upgrade_card) {
            $html .= '<div class="row gutters-5 mb20">';
            if ($renew_card) {
                $html .= '<div class="col-sm-6">';
                $html .= $renew_card;
                $html .= '</div>';
            }
            if ($upgrade_card) {
                $html .= '<div class="col-sm-6">';
                $html .= $upgrade_card;
                $html .= '</div>';
            }
            $html .= '</div>';
        }

    } else {
        //判断是否曾经开通过会员
        $vip_level_expired = (int) zib_get_user_meta($user_id, 'vip_level_expired', true);

        if ($vip_level_expired) {
            $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);
            if (strtotime($vip_exp_date)) {
                $html .= '<div class="ml6 mb10">';
                $html .= '<div class="title-h-left"><b>会员已过期</b></div>';
                $html .= '<div class="muted-2-color c-red">您的' . _pz('pay_user_vip_' . $vip_level . '_name') . '已过期，过期时间：' . date('Y年m月d日', strtotime($vip_exp_date)) . '</div>';
                $html .= '</div>';
            }
        }

        $points_exchange = zibpay_get_vip_points_exchange_link('', '<div class="payvip-icon btn-block badg em12" style="padding: 15px 5px;">' . zib_get_svg('points-color', null, 'mr6 em12') . '积分兑换会员<i class="fa fa-chevron-circle-right abs-right"></i></div>');

        $html .= '<div class="row gutters-7">';
        $html .= '<div class="col-sm-6">' . zibpay_get_vip_card(1) . '</div>';
        $html .= '<div class="col-sm-6">' . zibpay_get_vip_card(2) . '</div>';
        $html .= $points_exchange ? '<div class="col-sm-6">' . $points_exchange . '</div>' : '';
        $html .= '</div>';

    }
    return $html;
}

/**
 * @description: 会员续费商品明细
 * @param int $vip_level 会员等级
 * @return {*}
 */
function zibpay_get_vip_renew_product($vip_level)
{
    //获取优惠类型
    $renew_type = _pz('vip_opt', 'discount', 'vip_renew_price_type');
    //活动商品数组
    if ('customize' == $renew_type) {
        $renew_product_args = (array) _pz('vip_opt', 'discount', 'vip_' . $vip_level . '_renew_product');
    } else {
        $renew_product_args = (array) _pz('vip_opt', '', 'vip_' . $vip_level . '_product');
    }
    $renew_product = array();
    $i             = 1;
    foreach ($renew_product_args as $vip_product) {
        $price = round($vip_product['price'], 2);
        if (!$price) {
            continue;
        }
        $discount_tag = '';
        if ('discount' == $renew_type) {
            $vip_renew_discount = round(_pz('vip_opt', 8, 'vip_renew_discount'), 1);
            $discount_tag       = $vip_renew_discount . '折优惠';
            $show_price         = $price;
            $price              = round(($price * $vip_renew_discount / 10), 2);
        } elseif ('reduce' == $renew_type) {
            $vip_renew_reduce = round(_pz('vip_opt', 10, 'vip_renew_reduce'), 1);
            $show_price       = $price;
            $price            = round(($price - $vip_renew_reduce), 2);
            if ($price <= 0) {
                $price = 0.01;
            }
            $discount_tag = '立减' . (round($vip_product['price'], 2) - $price) . '元';
        } else {
            $show_price   = $vip_product['show_price'];
            $discount_tag = $vip_product['tag'];
        }

        $time     = (int) $vip_product['time'];
        $unit     = isset($vip_product['unit']) && $vip_product['unit'] === 'day' ? 'day' : 'month';
        $time_tag = $time ? $time . ($unit === 'day' ? '天' : '个月') : '永久';

        if ($price <= 0.01) {
            $price = 0.01;
        }
        $renew_product[$i] = array(
            'renew_type' => $renew_type,
            'price'      => $price,
            'show_price' => ($price < $show_price ? $show_price : ''),
            'tag'        => ($price < $show_price ? $discount_tag : ''),
            'time'       => $time,
            'time_tag'   => $time_tag,
            'unit'       => $unit,
            'tag_class'  => !empty($vip_product['tag_class']) ? $vip_product['tag_class'] : '',
        );
        $i++;
    }
    return $renew_product;
}

/**
 * @description: 获取会员升级的商品
 * @param int $user_id 用户id
 * @param {*} $upgrade_type 升级类型
 * @return {*}
 */
function zibpay_get_vip_upgrade_product($user_id = 0, $upgrade_type = false)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);

    if (!$upgrade_type || !in_array($upgrade_type, array('Permanent', 'permanent', 'unit'))) {
        $is_pay_type  = zibpay_is_pay_vip_type($user_id);
        $upgrade_type = $is_pay_type['upgrade'];
    }

    $vip_upgrade_product = _pz('vip_opt', '', 'vip_upgrade_product');
    $upgrade_product     = array();
    if (in_array($upgrade_type, array('Permanent', 'permanent'))) {
        //永久会员升级
        $time_left          = 'Permanent';
        $upgrade_product[1] = array(
            'price'      => round($vip_upgrade_product['permanent_price'], 2),
            'show_price' => $vip_upgrade_product['permanent_show_price'],
            'tag'        => $vip_upgrade_product['permanent_tag'],
            'tag_class'  => $vip_upgrade_product['permanent_tag_class'],
            'time_tag'   => '永久会员',
            'time'       => 'Permanent',
        );
    } else {
        if (!isset($vip_upgrade_product['month_to_month_s']) || $vip_upgrade_product['month_to_month_s']) {

            //月费会员升级，按天计费
            $time_left = (strtotime($vip_exp_date) - strtotime(current_time('Y-m-d h:i:s'))) / 84600;
            if ($time_left < 1) {
                $time_left = 1;
            } else {
                $time_left = round($time_left);
            }
            $price = round(($time_left * $vip_upgrade_product['unit_price']), 2);

            $upgrade_product[1] = array(
                'price'      => $price,
                'show_price' => '',
                'tag'        => $vip_upgrade_product['unit_tag'],
                'tag_class'  => $vip_upgrade_product['unit_tag_class'],
                'time_tag'   => $time_left . '天',
                'time'       => $time_left,
            );

        }

        if (!empty($vip_upgrade_product['jump_s'])) {
            //如果允许跨越升级
            $upgrade_product[2] = array(
                'price'      => round($vip_upgrade_product['jump_price'], 2),
                'show_price' => round($vip_upgrade_product['jump_show_price'], 2),
                'tag'        => $vip_upgrade_product['jump_tag'],
                'tag_class'  => $vip_upgrade_product['jump_tag_class'],
                'time_tag'   => '永久会员',
                'time'       => 'Permanent',
            );
        }

    }
    return $upgrade_product;
}

/**
 * @description: 会员升级或者续费的购买模态框内容
 * @param {*}
 * @return {*}
 */
function zibpay_get_pay_userviped_content()
{
    global $current_user;
    $user_id   = $current_user->ID;
    $vip_level = zib_get_user_vip_level($user_id);

    if (!$vip_level) {
        return;
    }

    //公共参数
    $mark            = zibpay_get_pay_mark();
    $vip_more        = '<div class="muted-2-color mb10 em09 muted-box padding-10">' . _pz('pay_user_vip_more') . '</div>';
    $blog_name       = get_bloginfo('name');
    $_POST_vip_level = !empty($_POST['vip_level']) ? $_POST['vip_level'] : 1;
    $pay_button      = zibpay_get_initiate_pay_input(4);

    //准备续费产品
    $renew_tab     = '';
    $renew_tab_con = '';
    $is_pay_type   = zibpay_is_pay_vip_type($user_id);
    if ($is_pay_type['renew']) {
        $renew_tab = '<li class="relative ' . ($vip_level == $_POST_vip_level ? ' active' : '') . '">
                    <a class="" data-toggle="tab" href="#tab-payvip-' . $vip_level . '">' . zibpay_get_vip_card_mini($vip_level, 'renew') . '</a><div class="abs-right active-icon"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                </li>';
        $renew_product_args = zibpay_get_vip_renew_product($vip_level);
        $renew_product      = '';
        $foreach_i          = 1;
        foreach ($renew_product_args as $product_i => $vip_product) {
            $price      = $vip_product['price'];
            $show_price = $vip_product['show_price'] ? '<div class="original-price relative"><span class="px12 mr6">' . $mark . '</span>' . $vip_product['show_price'] . '</div>' : '';
            $price      = '<div class="product-price c-red"><span class="px12 mr6">' . $mark . '</span>' . $price . '</div>' . $show_price;

            $vip_tag = $vip_product['tag'];
            $vip_tag = $vip_tag ? '<div class="abs-right vip-tag badg ' . ($vip_product['tag_class'] ?: 'jb-yellow') . '">' . $vip_tag . '</div>': '';

            $vip_time = '<div class="muted-2-color c-blue">' . $vip_product['time_tag'] . '</div>';

            $renew_product .= '<label>';
            $renew_product .= '<input class="hide vip-product-input" type="radio" name="vip_product_id" value="renewvip_' . $vip_level . '_' . $product_i . '"' . (1 == $foreach_i ? ' checked="checked"' : '') . '>';
            $renew_product .= '<div class="zib-widget vip-product relative text-center product-box">';
            $renew_product .= $vip_tag . $price . $vip_time;
            $renew_product .= '</div>';
            $renew_product .= '</label>';
            $foreach_i++;
        }

        $vip_icon      = '<div class="payvip_icon mb10"><p class="em2x">' . zibpay_get_vip_card_icon($vip_level) . '</p>' . _pz('pay_user_vip_' . $vip_level . '_name') . '</div>';
        $vip_equity    = '<ul class="payvip_equity mt10">' . _pz('vip_opt', '', 'pay_user_vip_' . $vip_level . '_equity') . '</ul>';
        $renew_tab_con = '<div class="tab-pane fade' . ($vip_level == $_POST_vip_level ? ' active in' : '') . '" id="tab-payvip-' . $vip_level . '">';
        $renew_tab_con .= '<form>';
        $renew_tab_con .= '<div class="row">';
        $renew_tab_con .= '<div class="col-sm-5 text-center theme-box">' . $vip_icon . $vip_equity . '</div>';
        $renew_tab_con .= '<div class="col-sm-7">';
        $renew_tab_con .= '<div class="mb10 label-box">' . $renew_product . '</div>';
        $renew_tab_con .= $vip_more . $pay_button;
        $renew_tab_con .= '</div>';
        $renew_tab_con .= '</div>';
        $renew_tab_con .= '<input type="hidden" name="order_name" value="' . zibpay_get_pay_order_name('续费' . _pz('pay_user_vip_' . $vip_level . '_name')) . '">';
        $renew_tab_con .= '<input type="hidden" name="order_type" value="4">';
        $renew_tab_con .= '</form>';
        $renew_tab_con .= '</div>';
    }
    //续费结束

    //准备升级产品
    $upgrade_desc    = '';
    $upgrade_tab_con = '';
    $upgrade_tab     = '';
    if ($is_pay_type['upgrade']) {
        $upgrade_product_html = '';
        //如果用户会员小于2级，则显示升级
        $upgrade_product_args = zibpay_get_vip_upgrade_product($user_id, $is_pay_type['upgrade']);
        //$upgrade_product_html = json_encode($upgrade_product );
        $foreach_i = 1;
        foreach ($upgrade_product_args as $product_i => $upgrade_product) {
            $price      = $upgrade_product['price'];
            $show_price = $upgrade_product['show_price'] ? '<div class="original-price relative"><span class="px12 mr6">' . $mark . '</span>' . $upgrade_product['show_price'] . '</div>' : '';
            $price      = '<div class="product-price c-red"><span class="px12 mr6">' . $mark . '</span>' . $price . '</div>' . $show_price;

            $vip_tag = $upgrade_product['tag'];
            $vip_tag = $vip_tag ? '<div class="abs-right vip-tag badg ' . ($upgrade_product['tag_class'] ?: 'jb-yellow') . '">' . $vip_tag . '</div>': '';

            $vip_time = '<div class="muted-2-color c-blue">' . $upgrade_product['time_tag'] . '</div>';

            $upgrade_product_html .= '<label>';
            $upgrade_product_html .= '<input class="hide vip-product-input" type="radio" name="vip_product_id" value="upgradevip_2_' . $product_i . '"' . (1 == $foreach_i ? ' checked="checked"' : '') . '>';
            $upgrade_product_html .= '<div class="zib-widget vip-product relative text-center product-box">';
            $upgrade_product_html .= $vip_tag . $price . $vip_time;
            $upgrade_product_html .= '</div>';
            $upgrade_product_html .= '</label>';
            $foreach_i++;
        }

        if ($upgrade_product_html) {
            $upgrade_tab = '<li class="relative ' . (2 == $_POST_vip_level ? ' active' : '') . '">
                                <a class="" data-toggle="tab" href="#tab-payvip-2">' . zibpay_get_vip_card_mini(2, 'upgrade') . '</a><div class="abs-right active-icon"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                            </li>';

            $vip_icon        = '<div class="payvip_icon mb10"><p class="em2x">' . zibpay_get_vip_card_icon(2) . '</p>' . _pz('pay_user_vip_2_name') . '</div>';
            $vip_equity      = '<ul class="payvip_equity mt10">' . _pz('vip_opt', '', 'pay_user_vip_2_equity') . '</ul>';
            $upgrade_tab_con = '<div class="tab-pane fade' . (2 == $_POST_vip_level ? ' active in' : '') . '" id="tab-payvip-2">';
            $upgrade_tab_con .= '<form>';
            $upgrade_tab_con .= '<div class="row">';
            $upgrade_tab_con .= '<div class="col-sm-5 text-center theme-box">' . $vip_icon . $vip_equity . '</div>';
            $upgrade_tab_con .= '<div class="col-sm-7">';
            $upgrade_tab_con .= '<div class="mb10 label-box">' . $upgrade_product_html . '</div>';
            $upgrade_tab_con .= $vip_more . $pay_button;
            $upgrade_tab_con .= '</div>';
            $upgrade_tab_con .= '</div>';
            $upgrade_tab_con .= '<input type="hidden" name="order_name" value="' . zibpay_get_pay_order_name('升级' . _pz('pay_user_vip_' . 2 . '_name')) . '">';
            $upgrade_tab_con .= '<input type="hidden" name="order_type" value="4">';
            $upgrade_tab_con .= '</form>';
            $upgrade_tab_con .= '</div>';
        }
    }

    //构建HTML
    $html      = '';
    $avatar    = zib_get_data_avatar($current_user->ID);
    $user_name = $current_user->display_name;
    $vip_desc  = '<span class="badg c-yellow vip-expdate-tag">' . zib_get_user_vip_exp_date_text($user_id) . '</span>';
    $html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">' . zib_get_svg('close') . '</button>';
    $html .= '<ul class="list-inline user-box"><li><div class="avatar-img">' . $avatar . '</div></li><li><b>' . $user_name . zib_get_svg('vip_' . $vip_level, '0 0 1024 1024', 'em12 ml6') . '</b><div class="em09">' . $vip_desc . '</div></li></ul>';
    $html .= '<ul class="flex jsb mt10 mb20 vip-cardminis gutters-5">' . $renew_tab . $upgrade_tab . '</ul>';
    $html .= '<div class="tab-content mt10">' . $renew_tab_con . $upgrade_tab_con . '</div>';

    $html = '<div class="box-body payvip-modal">' . $html . '</div>';
    return $html;
}

/**
 * @description: 购买会员的模态框构建
 * @param {*}
 * @return {*}
 */
function zibpay_get_pay_uservip_modal()
{

    $vip_level = !empty($_POST['vip_level']) ? $_POST['vip_level'] : 1;

    global $current_user;
    $avatar         = zib_get_data_avatar($current_user->ID);
    $user_vip_level = zib_get_user_vip_level($current_user->ID);
    $user_name      = $current_user->display_name;

    if ($user_vip_level) {
        return zibpay_get_pay_userviped_content($user_vip_level, $current_user->ID);
    }

    $vip_desc = _pz('pay_user_vip_desc');

    $user_info       = '';
    $vip_more        = '<div class="muted-2-color mb10 em09 muted-box padding-10">' . _pz('pay_user_vip_more') . '</div>';
    $mark            = zibpay_get_pay_mark();
    $mark            = '<span class="pay-mark">' . $mark . '</span>';
    $points_exchange = zibpay_get_vip_points_exchange_link('btn-block but mb10 padding-lg c-green charge-box', zib_get_svg('points-color', null, 'mr6 em12') . '积分兑换会员<i class="fa fa-angle-right em12 ml10"></i>');

    $tab_c = '';
    $tab_t = '';
    $con   = '';

    //卡密支付
    if (_pz('pay_vip_pass_charge_s')) {
        add_filter('zibpay_is_allow_card_pass_pay', '__return_true'); //添加卡密充值
        add_filter('zibpay_card_pass_payment_desc', function () {
            $password_desc = _pz('pay_vip_pass_charge_desc');
            return $password_desc ? '<div class="muted-box muted-2-color padding-10 mb10 em09">' . $password_desc . '</div>' : '';
        });
    }

    $pay_button = zibpay_get_initiate_pay_input(4);

    for ($vi = 1; $vi <= 2; $vi++) {
        if (!_pz('pay_user_vip_' . $vi . '_s', true)) {
            continue;
        }
        $card_args = array();
        $tab_t .= '<li class="relative ' . ($vip_level == $vi ? ' active' : '') . '">
                        <a class="" data-toggle="tab" href="#tab-payvip-' . $vi . '">' . zibpay_get_vip_card_mini($vi) . '</a><div class="abs-right active-icon"><i class="fa fa-check-circle" aria-hidden="true"></i></div>
                    </li>';

        $vip_icon   = '<div class="payvip_icon mb10"><p class="em2x">' . zibpay_get_vip_card_icon($vi) . '</p>' . _pz('pay_user_vip_' . $vi . '_name') . '</div>';
        $vip_equity = '<ul class="payvip_equity mt10">' . _pz('vip_opt', '', 'pay_user_vip_' . $vi . '_equity') . '</ul>';

        $vip_product_args = (array) _pz('vip_opt', '', 'vip_' . $vi . '_product');

        $vip_product_html = '';
        $product_i        = 0;
        $product_s        = _pz('vip_opt', true, 'vip_' . $vi . '_product_s');

        if ($product_s) {
            foreach ($vip_product_args as $vip_product) {
                $price = round($vip_product['price'], 2);
                if (!$price) {
                    continue;
                }

                $show_price = $vip_product['show_price'];
                $show_price = $vip_product['show_price'] ? '<div class="original-price relative">' . $mark . $vip_product['show_price'] . '</div>' : '';
                $price      = '<div class="product-price c-red"><span class="em09">' . $mark . '</span>' . $price . '</div>' . $show_price;

                $vip_tag = $vip_product['tag'];
                $vip_tag = $vip_tag ? '<div class="abs-right vip-tag badg ' . (!empty($vip_product['tag_class']) ? $vip_product['tag_class'] : 'jb-yellow') . '">' . $vip_tag . '</div>' : '';

                $vip_time = (int) $vip_product['time'];
                $unit     = isset($vip_product['unit']) && $vip_product['unit'] === 'day' ? 'day' : 'month';
                if ($vip_time) {
                    $vip_time = $vip_time . ($unit === 'day' ? '天' : '个月');
                } else {
                    $vip_time = '永久';
                }
                $vip_time = '<div class="muted-2-color c-blue">' . $vip_time . '</div>';

                $vip_product_html .= '<label>';
                $vip_product_html .= '<input class="hide vip-product-input" type="radio" name="vip_product_id" value="payvip_' . $vi . '_' . $product_i . '"' . (0 == $product_i ? ' checked="checked"' : '') . '>';
                $vip_product_html .= '<div class="zib-widget vip-product relative text-center product-box">';
                $vip_product_html .= $vip_tag . $price . $vip_time;
                $vip_product_html .= '</div>';
                $vip_product_html .= '</label>';
                $product_i++;
            }
        }

        $payvip_form = '<input type="hidden" name="order_name" value="' . zibpay_get_pay_order_name('开通' . _pz('pay_user_vip_' . $vi . '_name')) . '">
        <input type="hidden" name="order_type" value="4">';

        if ($vip_product_html) {
            $vip_form = '<form>
            <div class="row">
                <div class="col-sm-5 text-center theme-box">' . $vip_icon . $vip_equity . '</div>
                <div class="col-sm-7"><div class="mb10 charge-box">' . $vip_product_html . '</div>' . $points_exchange . $vip_more . $pay_button . '</div>
            </div>
            ' . $payvip_form . '
        </form>';

        } else {
            $vip_form = '<form>
            <div class="row">
                <div class="col-sm-5 text-center theme-box">' . $vip_icon . $vip_equity . '</div>
                <div class="col-sm-7">' . $vip_more . $points_exchange . '</div>
            </div>
            ' . $payvip_form . '
        </form>';
        }

        $tab_c .= '<div class="tab-pane fade' . ($vip_level == $vi ? ' active in' : '') . '" id="tab-payvip-' . $vi . '">' . $vip_form . '</div>';
    }

    $con .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><svg class="ic-close" viewBox="0 0 1024 1024"><path d="M573.44 512.128l237.888 237.696a43.328 43.328 0 0 1 0 59.712 43.392 43.392 0 0 1-59.712 0L513.728 571.84 265.856 819.712a44.672 44.672 0 0 1-61.568 0 44.672 44.672 0 0 1 0-61.568L452.16 510.272 214.208 272.448a43.328 43.328 0 0 1 0-59.648 43.392 43.392 0 0 1 59.712 0l237.952 237.76 246.272-246.272a44.672 44.672 0 0 1 61.568 0 44.672 44.672 0 0 1 0 61.568L573.44 512.128z"></path></svg></button>';
    $con .= '<ul class="list-inline user-box"><li><div class="avatar-img">' . $avatar . '</div></li><li><b>' . $user_name . '</b><div class="c-red em09">' . $vip_desc . '</div></li></ul>';
    $con .= '<ul class="flex jsb mt10 mb20 vip-cardminis gutters-5">' . $tab_t . '</ul>';
    $con .= '<div class="tab-content mt10">' . $tab_c . '</div>';

    $con = '<div class="box-body payvip-modal">' . $con . '</div>';

    return $con;
}

/**付款成功后后更新用户数据 */
function zibpay_uservip_paysuccess($values)
{
    $pay_order = $values;
    if (empty($pay_order->user_id) || empty($pay_order->product_id) || 4 != $pay_order->order_type) {
        return;
    }

    $vip_product_id = explode('_', $pay_order->product_id);
    if (!isset($vip_product_id[0]) || !isset($vip_product_id[1]) || !isset($vip_product_id[2]) || 'vip' != $vip_product_id[0]) {
        return;
    }

    $pay_vip_level   = (int) $vip_product_id[1];
    $pay_vip_product = (int) $vip_product_id[2];
    $pay_vip_action  = $vip_product_id[3];

    //获取用户本来的会员等级
    $type_text         = '购买会员';
    $user_id           = $pay_order->user_id;
    $user_vip_level    = zib_get_user_vip_level($user_id);
    $new_date          = current_time('Y-m-d h:i:s');
    $user_vip_exp_date = $user_vip_level ? get_user_meta($pay_order->user_id, 'vip_exp_date', true) : $new_date;

    if ('renew' == $pay_vip_action) {
        //续费
        $type_text          = '续费会员';
        $renew_product_args = zibpay_get_vip_renew_product($pay_vip_level);
        $pay_vip_time       = $renew_product_args[$pay_vip_product]['time'];
        if ('Permanent' == $pay_vip_time || $pay_vip_time == 0) {
            //永久会员选项
            $new_vip_exp_date = 'Permanent';
        } else {
            //续费根据用户现有时间追加
            $unit             = $renew_product_args[$pay_vip_product]['unit'] === 'day' ? 'day' : 'month';
            $new_vip_exp_date = date('Y-m-d 23:59:59', strtotime("+ $pay_vip_time $unit", strtotime($user_vip_exp_date)));
        }
    } elseif ('upgrade' == $pay_vip_action) {
        //升级
        $type_text            = '升级会员';
        $upgrade_product_args = zibpay_get_vip_upgrade_product($user_id);
        $pay_vip_time         = $upgrade_product_args[$pay_vip_product]['time'];
        $new_vip_exp_date     = 'Permanent' == $pay_vip_time ? 'Permanent' : $user_vip_exp_date;
    } elseif ('exchange' == $pay_vip_action) {
        //卡密兑换
        $type_text    = '兑换会员';
        $product_args = zibpay_get_payed_vip_exchange_card_product($pay_order->pay_num);
        if (empty($product_args['time'])) {
            return;
        }
        $pay_vip_level    = $product_args['level'];
        $new_vip_exp_date = 'Permanent' == $product_args['time'] ? 'Permanent' : date('Y-m-d 23:59:59', strtotime('+' . $product_args['time'] . ' ' . $product_args['unit'], strtotime($new_date)));

    } elseif ('points' == $pay_vip_action) {
        //卡密兑换
        $type_text = '积分兑换';
        $lists_opt = _pz('pay_vip_points_exchange_product');
        if (empty($lists_opt[$pay_vip_product]['time'])) {
            return;
        }
        $pay_vip_level    = $lists_opt[$pay_vip_product]['level'];
        $new_vip_exp_date = 'Permanent' == $lists_opt[$pay_vip_product]['time'] ? 'Permanent' : date('Y-m-d 23:59:59', strtotime('+' . $lists_opt[$pay_vip_product]['time'] . ' ' . $lists_opt[$pay_vip_product]['unit'], strtotime($new_date)));

    } else {
        //购买会员的商品选项
        $pay_product_args = (array) _pz('vip_opt', '', 'vip_' . $pay_vip_level . '_product');
        $pay_vip_time     = (int) $pay_product_args[$pay_vip_product]['time'];

        if (0 == $pay_vip_time) {
            //永久会员选项
            $new_vip_exp_date = 'Permanent';
        } else {
            $unit             = isset($pay_product_args[$pay_vip_product]['unit']) && $pay_product_args[$pay_vip_product]['unit'] === 'day' ? 'day' : 'month';
            $new_vip_exp_date = date('Y-m-d 23:59:59', strtotime("+$pay_vip_time $unit", strtotime($new_date)));
        }
    }

    $data = array(
        'vip_level' => $pay_vip_level, //等级
        'exp_date'  => $new_vip_exp_date, //有效截至时间
        'type'      => $type_text, //中文说明
        'order_num' => $pay_order->order_num, //订单号
        'desc'      => '', //说明
    );
    zibpay_update_user_vip($user_id, $data);

}
add_action('payment_order_success', 'zibpay_uservip_paysuccess', 9);

/**
 * @description: 开通会员统一接口
 * @param {*} $user_id
 * @param {*} $data
 * @return {*}
 */
function zibpay_update_user_vip($user_id, $data)
{
    $defaults = array(
        'vip_level' => '', //等级
        'exp_date'  => 0, //有效截至时间
        'type'      => '', //中文说明
        'order_num' => '', //订单号
        'desc'      => '', //说明
    );

    $data = wp_parse_args($data, $defaults);

    //更新用户数据
    update_user_meta($user_id, 'vip_exp_date', $data['exp_date']);
    update_user_meta($user_id, 'vip_level', $data['vip_level']);
}

/**
 * @description: 获取VIP用户徽章卡片
 * @param {*}
 * @return {*}
 */
function zibpay_get_viped_card($user_id)
{

    $vip_level = zib_get_user_vip_level($user_id);
    if (!$vip_level) {
        return;
    }
    $icon         = zibpay_get_vip_card_icon($vip_level);
    $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);
    $avatar       = zib_get_data_avatar($user_id);
    $exp_desc     = 'Permanent' == $vip_exp_date ? '永久有效' : '到期时间：' . date('Y年m月d日', strtotime($vip_exp_date));

    $name = '<div class="flex ac mb10">
                <div class="avatar-img mr10" style="margin-top: -4px;">' . $avatar . '</div>
                <div>
                    <div class="font-bold">' . $icon . ' ' . _pz('pay_user_vip_' . $vip_level . '_name') . '</div>
                    <div class="px12">' . zib_get_svg('time', null, 'mr6') . $exp_desc . '</div>
                </div>
            </div>';

    $img        = '<div class="vip-img abs-right">' . $icon . '</div>';
    $ba_icon    = '<div class="abs-center vip-baicon">' . $icon . '</div>';
    $vip_equity = '<ul class="mb10 relative">' . _pz('vip_opt', '', 'pay_user_vip_' . $vip_level . '_equity') . '</ul>';

    $card = '<div class="vip-card level-' . $vip_level . ' ' . zibpay_get_vip_theme($vip_level) . '" vip-level="' . $vip_level . '">
    ' . $ba_icon . $img . $name . $vip_equity . '
    </div>';

    return $card;
}

function zibpay_get_vip_card($level = 1, $args = array())
{
    if (!_pz('pay_user_vip_' . $level . '_s', true)) {
        return;
    }

    $defaults = array(
        'type' => 'auto',
    );

    $args = wp_parse_args((array) $args, $defaults);

    if ('renew' == $args['type']) {
        $button_text = '续费';
        $desc        = _pz('vip_opt', '', 'vip_renew_desc');
    } elseif ('upgrade' == $args['type']) {
        $button_text = '升级';
        $desc        = _pz('vip_opt', '', 'vip_upgrade_desc');
    } else {
        $button_text = '开通';
        $desc        = '';
    }

    $icon = zibpay_get_vip_card_icon($level);

    $vip_name     = _pz('pay_user_vip_' . $level . '_name');
    $action_class = is_user_logged_in() ? ' pay-vip' : ' signin-loader';
    $img          = '<div class="vip-img abs-right">' . $icon . '</div>';
    $name         = '<div class="vip-name mb10"><span class="mr6">' . $icon . '</span>' . $button_text . $vip_name . '</div>';
    $name .= $desc ? '<div class="mb6 font-bold">' . $desc . '</div>' : '';
    $button = '<a class="but jb-blue radius payvip-button" href="javascript:;">' . $button_text . $vip_name . '</a>';

    $ba_icon    = '<div class="abs-center vip-baicon">' . $icon . '</div>';
    $vip_equity = '<ul class="mb10 relative">' . _pz('vip_opt', '', 'pay_user_vip_' . $level . '_equity') . '</ul>';

    $card = '<div class="vip-card pointer level-' . $level . ' ' . zibpay_get_vip_theme($level) . $action_class . '" vip-level="' . $level . '">
    ' . $ba_icon . $img . '<div class="relative">' . $name . $vip_equity . $button . '</div>
    </div>';
    return $card;
}

function zibpay_get_vip_card_mini($level = 1, $type = '')
{

    $tax = '';
    if ('renew' == $type) {
        $tax = '续费';
    } elseif ('upgrade' == $type) {
        $tax = '升级';
    }
    $icon = zibpay_get_vip_card_icon($level);
    $name = '<div class="vip-icon">' . $icon . '</div><div class="vip-name">' . $tax . _pz('pay_user_vip_' . $level . '_name') . '</div>';

    $ba_icon = '<div class="abs-center vip-baicon">' . $icon . '</div>';

    $card = '<div class="vip-card vip-cardmini level-' . $level . ' ' . zibpay_get_vip_theme($level) . '">
    ' . $ba_icon . $name . '
    </div>';
    return $card;
}

function zibpay_get_payvip_button($level = 1, $class = 'but jb-yellow', $text = '立即开通')
{
    $button = '<a class="pay-vip ' . $class . '" href="javascript:;" vip-level="' . $level . '">' . $text . '</a>';
    return $button;
}

function zibpay_get_vip_card_icon($level = 1, $class = '', $tip = false)
{
    return zibpay_get_vip_icon($level, $class, false);
}

function zibpay_get_vip_icon($vip_level = 1, $class = 'em12 ml3', $tip = 1)
{
    if (!$vip_level) {
        return;
    }

    $vip_img_src = zibpay_get_vip_icon_img_url($vip_level);
    $tip_attr    = $tip ? ' data-toggle="tooltip"' : '';

    $lazy_attr = zib_get_lazy_attr('lazy_other', $vip_img_src, 'img-icon ' . $class, ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');

    $vip_badge = '<img ' . $lazy_attr . $tip_attr . ' title="' . _pz('pay_user_vip_' . $vip_level . '_name') . '" alt="' . _pz('pay_user_vip_' . $vip_level . '_name') . '">';

    return $vip_badge;
}

function zibpay_get_vip_icon_img_url($vip_level = 1)
{
    return _pz('vip_opt', ZIB_TEMPLATE_DIRECTORY_URI . '/img/vip-' . $vip_level . '.svg', 'vip_' . $vip_level . 'img_icon');
}

function zibpay_get_payvip_icon($user_id = 0, $class = '', $text = '开通会员')
{
    if (!$user_id || (!_pz('pay_user_vip_1_s', true) && !_pz('pay_user_vip_2_s', true))) {
        return;
    }

    $current_user_id = get_current_user_id();
    $vip_level       = zib_get_user_vip_level($user_id);
    if ($vip_level) {
        return zibpay_get_vip_icon($vip_level);
    } elseif ($user_id == $current_user_id) {
        $button = '<a class="pay-vip but jb-red radius4 payvip-icon ' . $class . '" href="javascript:;">' . zib_get_svg('vip_1', '0 0 1024 1024', 'em12 mr10') . $text . '</a>';
        return $button;
    }
}

function zibpay_get_vip_theme($level = 1)
{
    $icon = 1 == $level ? 'vip-theme1' : 'vip-theme2';
    return $icon;
}

/**
 * @description: 获取用户会员等级
 * @param {*}
 * @return {*}
 */
function zib_get_user_vip_level($user_id = null)
{
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    static $vip_level_users = array();

    if (isset($vip_level_users[$user_id])) {
        return $vip_level_users[$user_id];
    }

    $vip_level = (int) get_user_meta($user_id, 'vip_level', true);

    /**如果对应的会员等级关闭则返回false */
    if ($vip_level && !_pz('pay_user_vip_' . $vip_level . '_s', true)) {
        $vip_level = 0;
    }

    if ($vip_level) {
        $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);
        $current_time = current_time('Y-m-d h:i:s');
        //对比vip时间是否过期
        if ('Permanent' !== $vip_exp_date && strtotime($current_time) > strtotime($vip_exp_date)) {
            //会员已经过期
            update_user_meta($user_id, 'vip_level', 0);
            zib_update_user_meta($user_id, 'vip_level_expired', $vip_level);
            $vip_level = 0;
        }
    }

    $vip_level_users[$user_id] = $vip_level;
    return $vip_level;
}

/**
 * @description: 获取用户VIP到期时间的文案
 * @param {*}
 * @return {*}
 */
function zib_get_user_vip_exp_date_text($user_id = 0)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    $vip_level = (int) get_user_meta($user_id, 'vip_level', true);
    /**如果对应的会员等级关闭则返回false */
    if (!_pz('pay_user_vip_' . $vip_level . '_s', true)) {
        return false;
    }

    $vip_exp_date = get_user_meta($user_id, 'vip_exp_date', true);
    $zero1        = current_time('Y-m-d h:i:s');
    if (!$vip_exp_date) {
        return false;
    }

    if ('permanent' === strtolower($vip_exp_date)) {
        return '永久会员';
    }

    return ((strtotime($zero1) < strtotime($vip_exp_date))) ? date('Y年m月d日', strtotime($vip_exp_date)) : '会员已过期';
}

/**
 * @description: 获取网站的会员数量
 * @param {*}
 * @return {*}
 */
function zib_get_vip_user_count($vip_level = 0)
{

    $meta_query             = array();
    $meta_query['relation'] = 'AND';
    if (1 == $vip_level) {
        $meta_query[] = array(
            'key'     => 'vip_level',
            'value'   => 1,
            'compare' => '=',
        );
    } elseif (2 == $vip_level) {
        $meta_query[] = array(
            'key'     => 'vip_level',
            'value'   => 2,
            'compare' => '=',
        );
    } elseif (!$vip_level) {
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key'     => 'vip_level',
                'value'   => 1,
                'compare' => '=',
            ),
            array(
                'key'     => 'vip_level',
                'value'   => 2,
                'compare' => '=',
            ),
        );
    } else {
        return false;
    }
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key'     => 'vip_exp_date',
            'value'   => 'Permanent',
            'compare' => '=',
        ),
        array(
            'key'     => 'vip_exp_date',
            'value'   => current_time('Y-m-d h:i:s'),
            'compare' => '>=',
        ),
    );
    $args = array(
        'meta_query'   => $meta_query,
        'count_total ' => true,
    );
    $user_query = new WP_User_Query($args);
    $all_count  = $user_query->get_total();
    return $all_count ? $all_count : 0;
}

/**
 * @description: 通过卡号和卡密查找是否有对应的卡密兑换
 * @param {*} $card
 * @param {*} $pass
 * @return {*}
 */
function zibpay_get_vip_exchange_card($card, $password, $only_password = false)
{

    $get_args = array(
        'card'     => $card,
        'password' => $password,
        'type'     => 'vip_exchange',
    );

    if ($only_password) {
        unset($get_args['card']);
    }

    $msg_db = ZibCardPass::get_row($get_args);
    return $msg_db;
}

/**
 * @description: 获取卡密的兑换参数
 * @param {*} $db
 * @return {*}
 */
function zibpay_get_vip_exchange_card_data($db)
{
    $db   = (array) $db;
    $meta = maybe_unserialize($db['meta'] ?? []);

    $unit_args = array(
        'day'   => '天',
        'month' => '个月',
        'year'  => '年',
    );

    $unit = isset($meta['unit']) ? $meta['unit'] : 'month';

    return array(
        'time'      => isset($meta['time']) ? $meta['time'] : 0,
        'unit'      => $unit, //天还是月
        'unit_show' => isset($unit_args[$unit]) ? $unit_args[$unit] : '个月', //天还是月
        'level'     => isset($meta['level']) ? (int) $meta['level'] : 1,
    );

    return isset($meta['price']) ? $meta['price'] : 0;
}

/**
 * @description: 获取以支付的卡密会员商品数据
 * @param {*} $db
 * @return {*}
 */
function zibpay_get_payed_vip_exchange_card_product($order_num)
{
    $db = ZibCardPass::get_row(array('order_num' => $order_num, 'type' => 'vip_exchange'));
    return zibpay_get_vip_exchange_card_data($db);
}

/**
 * @description: 获取积分兑换会员的链接
 * @param {*} $class
 * @param {*} $con
 * @param {*} $tag
 * @return {*}
 */
function zibpay_get_vip_points_exchange_link($class = '', $con = '兑换会员', $tag = 'a')
{
    if (!_pz('points_s', true) || !_pz('pay_vip_points_exchange_s', true) || (!_pz('pay_user_vip_1_s', true) && !_pz('pay_user_vip_2_s', true))) {
        return;
    }

    //已经是会员了，就无法再兑换了
    if (zib_get_user_vip_level()) {
        return;
    }

    $url_var = array(
        'action' => 'vip_points_exchange_modal',
    );

    $args = array(
        'tag'           => $tag,
        'new'           => true,
        'class'         => $class,
        'data_class'    => 'modal-mini full-sm',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zibpay_get_vip_points_exchange_modal()
{

    $user        = wp_get_current_user();
    $user_id     = $user->ID;
    $user_points = zibpay_get_user_points($user_id);
    $lists       = '';
    $min_val     = 0; //是否足够
    $lists_opt   = _pz('pay_vip_points_exchange_product');
    $desc        = _pz('pay_vip_points_exchange_desc');
    $desc        = $desc ? '<div class="muted-box muted-2-color padding-10 em09 mt20">' . $desc . '</div>' : '';
    $form        = '<div class="mt20">
    <input type="hidden" name="product_id" value="0">
    <input type="hidden" name="order_type" value="4">
    <input type="hidden" name="action" value="points_initiate_pay">
    <button class="mt6 but jb-yellow btn-block radius padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>立即兑换</button>
    </div>';

    $user_box = '<div class="muted-box padding-h10 mb20">
    <a rel="nofollow" class="flex jsb ac" href="' . zib_get_user_center_url('balance') . '">
        <span class="muted-2-color">' . zib_get_svg('points') . ' 我的积分</span>
        <span class="font-bold ml6 em14"><span class="c-green mr10">' . $user_points . '</span><i class="fa fa-angle-right"></i></span>
    </a>
        </div>';

    if ($lists_opt && is_array($lists_opt)) {
        foreach ($lists_opt as $k => $opt) {
            if (!empty($opt['points']) && !empty($opt['level']) && !empty($opt['time']) && _pz('pay_user_vip_' . $opt['level'] . '_s')) {

                $time = $opt['time'] === 'Permanent' ? '永久' : $opt['time'] . '<span class="px12">' . (['day' => '天', 'month' => '个月'][$opt['unit']]) . '</span>';
                $lists .= '<div class="vip-card  vip-theme' . $opt['level'] . ($k == 0 ? ' active' : '') . ' " data-for="product_id" data-value="' . $k . '">
                <span class=" ">' . zibpay_get_vip_card_icon($opt['level'], 'mr3') . _pz('pay_user_vip_' . $opt['level'] . '_name') . '</span>
                <div class="flex jsb ac hh em16 mt10">
                    <span class="shrink0 mr10">' . $time . '</span>
                    <span class="shrink0">' . $opt['points'] . '<span class="px12">积分</span></span>
                </div>
                </div>';

                if (!$min_val || $opt['points'] < $min_val) {
                    $min_val = $opt['points'];
                }
            }
        }
    }

    if (!$lists) {
        $lists = zib_get_null('暂无可兑换的会员', 30);
        $form  = '';
    } else {
        $lists = '<div class="muted-2-color mb6 em09">请选择需兑换的会员</div><div class="flex hh exchange-card-box">' . $lists . '</div>';
    }

    if ($lists && !$min_val > $user_points) {
        $form = '<div class="badg c-red btn-block">积分不足，暂时无法兑换</div>';
    }

    $html   = '<form class="">' . $user_box . $lists . $desc . $form . '</form>';
    $header = '<div class="mb10 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="mr6 em14">' . zib_get_svg('vip_1') . '</span>积分兑换会员</b></div>';

    return $header . $html;

}
