<?php
/*
* @Author : Qinver
* @Url : zibll.com
* @Date : 2025-04-07 17:39:04
 * @LastEditTime : 2026-01-31 17:52:53
* @Project : Zibll子比主题
* @Description : 更优雅的Wordpress主题
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
* @Email : 770349780@qq.com
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

global $zib_shop;

zib_require(array(
    'option-module',
    'admin-option',
), false, ZIB_SHOP_REQUIRE_URI . 'admin/options/');

//引入商城资源文件
if ($zib_shop->s) {
    zib_require(array(
        'meta-option',
        'term-option',
    ), false, ZIB_SHOP_REQUIRE_URI . 'admin/options/');
    zib_require(array(
        'ajax',
    ), false, ZIB_SHOP_REQUIRE_URI . 'admin/actions/');

    //后台订单待处理通知
    function zib_shop_add_admin_notice()
    {
        if (isset($_GET['page']) && in_array($_GET['page'], ['zibpay_withdraw'])) {
            return;
        }

        $shipping_count   = zib_shop_get_shipping_status_count('0');
        $after_sale_count = zib_shop_get_after_sale_status_count([1, 2]);

        $notice = $shipping_count > 0 ? $shipping_count . '个订单待发货' : '';
        $notice .= $after_sale_count > 0 ? ($notice ? '，' : '') . $after_sale_count . '个售后待处理' : '';

        if ($shipping_count > 0 || $after_sale_count > 0) {
            $html = '<div class="notice notice-info is-dismissible">';
            $html .= '<h3>商城订单待处理</h3>';
            $html .= '<p>您有' . $notice . '</p>';
            $html .= '<p>';
            $html .= $shipping_count > 0 ? '<a class="button" style=" margin-right: 10px; " href="' . zibpay_get_admin_shop_url('shipping', 'shipping_status=0') . '" class="button-primary">去发货</a>' : '';
            $html .= $after_sale_count > 0 ? '<a class="button" href="' . zibpay_get_admin_shop_url('after-sale', 'after_sale_status=1,2') . '" class="button-primary">处理售后</a>' : '';
            $html .= '</p>';
            $html .= '</div>';
            echo $html;
        }
    }
    add_action('admin_notices', 'zib_shop_add_admin_notice');

    //后台订单地址修改通知
    function zib_shop_add_admin_order_address_notice()
    {

        //获取所有的待处理订单
        $where = [
            'type'   => 'order_modify_address',
            'status' => 0,
        ];

        $count = ZibMsg::get_count($where);
        if ($count > 0) {
            $html = '<div class="notice notice-info is-dismissible">';
            $html .= '<h3>订单收货地址修改待处理</h3>';
            $html .= '<p>有' . $count . '个订单收货地址修改申请待处理，请在前台消息中心，点击修改地址消息，进行处理</p>';
            $html .= '<p><a class="button" href="' . zibmsg_get_conter_url('system') . '" class="button-primary">去处理</a></p>';
            $html .= '</div>';
            echo $html;
        }

    }
    add_action('admin_notices', 'zib_shop_add_admin_order_address_notice');
}
