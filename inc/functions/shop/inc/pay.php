<?php
/*
* @Author : Qinver
* @Url : zibll.com
* @Date : 2025-03-04 11:54:28
 * @LastEditTime : 2026-01-30 22:30:25
* @Project : Zibll子比主题
* @Description : 更优雅的Wordpress主题
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
* @Email : 770349780@qq.com
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

//获取支付方式
function zib_shop_get_payment_methods()
{
    $pay_methods = zibpay_get_payment_methods(zib_shop_get_order_type());
    return $pay_methods;
}

//允许余额支付
function zib_shop_is_allow_balance_pay_filter($is, $pay_type)
{
    if ($pay_type == zib_shop_get_order_type()) {
        return true;
    }
    return $is;
}
add_filter('zibpay_is_allow_balance_pay', 'zib_shop_is_allow_balance_pay_filter', 10, 2); //允许余额支付

//不允许卡密支付
function zib_shop_is_allow_card_pass_pay_filter($is, $pay_type)
{
    if ($pay_type == zib_shop_get_order_type()) {
        return false;
    }
    return $is;
}
add_filter('zibpay_is_allow_card_pass_pay', 'zib_shop_is_allow_card_pass_pay_filter', 10, 2); //不允许卡密支付

function zib_shop_get_payment_data($pay_type = 'price')
{

    $data = [
        'pay_methods'         => [],
        'pay_methods_active'  => '',
        'user_balance'        => '',
        'user_points'         => '',
        'balance_charge_link' => '',
        'points_pay_link'     => '',
        'user_balance_url'    => zib_get_user_center_url('balance'),
        'pay_type'            => zib_shop_get_order_type(), //固定值，表示商品购买
        'return_url'          => zib_get_user_center_url('order'), //返回地址
    ];

    $user_id = get_current_user_id();
    if ($pay_type === 'points') {

        $data['pay_methods'] = [
            'points' => array(
                'name' => '积分支付',
                'img'  => '<svg aria-hidden="true"><use xlink:href="#icon-points-color"></use></svg>',
            ),
        ];

        $data['pay_methods_active'] = 'points';
        $data['user_points']        = zibpay_get_user_points($user_id);
        $data['user_points_url']    = zib_get_user_center_url('balance');
        $data['points_pay_link']    = zibpay_get_points_pay_link('but c-yellow', '购买积分');
        return $data;
    }

    //获取支付方式
    $pay_methods                = zib_shop_get_payment_methods();
    $pay_methods_keys           = array_keys($pay_methods);
    $pay_methods_active         = $pay_methods_keys[0] ?? '';
    $data['pay_methods']        = $pay_methods;
    $data['pay_methods_active'] = $pay_methods_active;

    if ($user_id && in_array('balance', $pay_methods_keys)) {
        //存在余额支付，则显示用户余额
        $user_balance = zibpay_get_user_balance($user_id);
        //余额充值的链接
        $balance_charge_link = zibpay_get_balance_charge_link('but c-yellow', '充值');

        $data['user_balance']        = $user_balance;
        $data['balance_charge_link'] = $balance_charge_link;
    }

    if (!$data['pay_methods']) {
        $data['error_msg'] = is_super_admin() ? '<a href="' . zib_get_admin_csf_url('支付付费/收款接口') . '" class="but c-red btn-block">请先在主题设置中配置收款方式及收款接口</a>' : '<span class="badg px12 c-yellow-2">暂时无法购买，请与客服联系</span>';
    }

    return $data;
}

//挂钩下单成功后
add_action('order_created', 'zib_shop_order_created', 10, 1);
function zib_shop_order_created($order)
{
    if ($order['order_type'] != zib_shop_get_order_type()) {
        return;
    }

    //付款成功弹窗通知准备-cookie
    zib_shop_modal_notice_set_cookie($order['id']);

    //扣减库存
    $product_id      = $order['post_id'];
    $product_opt_str = $order['meta']['order_data']['options_active_str'];
    zib_shop_product_deduct_stock($product_id, $product_opt_str, $order['count']);
}

//挂钩关闭订单，添加库存恢复
add_action('order_closed', 'zib_shop_order_closed', 10, 1); //订单关闭
add_action('order_refunded', 'zib_shop_order_closed', 10, 1); //订单退单
function zib_shop_order_closed($order_id)
{
    $order = zibpay::get_order($order_id, 'all');
    if ($order['order_type'] != zib_shop_get_order_type()) {
        return;
    }

    $product_id      = $order['post_id'];
    $product_opt_str = $order['meta']['order_data']['options_active_str'];
    zib_shop_product_add_stock($product_id, $product_opt_str, $order['count']);
}

//挂钩订单付款成功后
add_action('payment_order_success', 'zib_shop_order_payment_success', 10, 2);
function zib_shop_order_payment_success($order)
{
    $order = zibpay::order_data_map($order);
    if ($order['order_type'] != zib_shop_get_order_type()) {
        return;
    }

    //更新发货状态为待发货
    zib_shop_update_order_shipping_status($order['id'], 0);

    //准备发货
    //1. 判断是否需要发货，还是自动发货
    $shipping_type = zib_shop_get_product_config($order['post_id'], 'shipping_type');
    if ($shipping_type === 'auto') {
        //自动发货
        zib_shop_auto_shipping($order);
    } else {
        //通知商家发货
        zib_shop_notify_shipping($order);
    }

    //更新商品销量
    zib_shop_update_product_sales_volume($order['post_id'], $order['count']);
}

//用户支付成功后，准备通知弹窗通知-储存cookie内容
function zib_shop_modal_notice_set_cookie($order_id)
{

    $cookie_key = 'shop_pay_success_notice';
    //获取已存在的cookie
    $cookie         = $_COOKIE[$cookie_key] ?? '';
    $cookie_array   = $cookie ? explode(',', $cookie) : [];
    $cookie_array[] = (string) $order_id;
    $cookie_str     = implode(',', array_unique($cookie_array));

    $_COOKIE[$cookie_key] = $cookie_str;
    $max_time             = zibpay_get_order_pay_max_time() + 3; //分钟

    setcookie($cookie_key, $cookie_str, time() + $max_time * 60, '/');
}

//挂钩WP—footer显示用户支付成功弹窗通知
add_action('wp_footer', 'zib_shop_pay_success_modal_notice_footer');
function zib_shop_pay_success_modal_notice_footer()
{
    $cookie_key = 'shop_pay_success_notice';
    $cookie     = $_COOKIE[$cookie_key] ?? '';
    if (!$cookie) {
        return;
    }
    $cookie_array = explode(',', $cookie);

    if (!$cookie_array) {
        return;
    }

    //获取所有已支付的订单
    $orders = zibpay::order_query([
        'status'        => 1,
        'order_type'    => zib_shop_get_order_type(),
        'where'         => [
            ['id', 'in', $cookie_array],
        ],
        'no_found_rows' => true,
        'field'         => 'id,order_num,pay_time,post_id,user_id',
    ]);

    if (!$orders['orders']) {
        return;
    }

    $is_logged_in               = is_user_logged_in();
    $auto_delivery_notice_lists = '';
    foreach ($orders['orders'] as $order) {
        $order_id      = $order['id'];
        $shipping_type = zib_shop_get_product_config($order['post_id'], 'shipping_type');

        if ($shipping_type === 'auto') {
            $shipping_status = zib_shop_get_order_shipping_status($order_id);
            $order_meta_data = zibpay::get_meta($order_id, 'order_data');
            $post_title      = $order_meta_data['product_title'] ?? '';
            $auto_delivery_notice_lists .= '<div class="border-top padding-h15">';
            $auto_delivery_notice_lists .= '<div class="font-bold">' . $post_title . (!empty($order_meta_data['options_active_name']) ? '[' . $order_meta_data['options_active_name'] . ']' : '') . '</div>';
            $auto_delivery_notice_lists .= '<div class="muted-color em09 flex ac jsb"><span>' . $order['order_num'] . '</span><span>' . $order['pay_time'] . '</span></div>';
            if ($shipping_status == 0) {
                $auto_delivery_notice_lists .= zib_shop_get_author_contact_link($order['user_id'], '', '<div class="c-yellow muted-box mt6 padding-10">您购买的商品自动发货失败，点击此处与客服联系</div>');
            } else {
                $delivery_html = $order_meta_data['shipping_data']['delivery_content'] ?? '';
                $auto_delivery_notice_lists .= '<div class="muted-box mt6 padding-10">' . $delivery_html . '</div>';
            }
            $auto_delivery_notice_lists .= '</div>';
        }
    }

    if ($auto_delivery_notice_lists) {
        $modal_html = '<div class="mb10"><div class="flex jc c-green"><b>请查收您购买的商品</b></div></div>
                        <div class="max-vh5 scroll-y mini-scrollbar">' . $auto_delivery_notice_lists . ' </div>
                        <botton type="button" class="mt10 but jb-green padding-lg btn-block auto-delivery-notice-received">我知道了</botton>';
    } else {
        $modal_html = '
        <div class="flex ac mb20 padding-h15">
            <div class="flex1 mr10">
                <div class="font-bold em12 c-green">等待商家发货</div>
                <div class="muted-color em09 mt6">商品正在准备中，将尽快为您发货</div>
            </div>
            <div class="em2x"><span class="badg cir c-green"><i class="fa fa-truck"></i></span></div>
        </div>
        <div class="flex ac">
            <div class="flex-auto"><a href="javascript:;" class="but hollow padding-lg btn-block" data-dismiss="modal">继续逛逛</a></div>
            ' . ($is_logged_in ? '<div class="flex-auto ml6"><a href="' . zib_get_user_center_url('order') . '" class="but c-blue padding-lg btn-block hollow view-order-link">查看订单</a></div>' : '') . '
        </div>';
    }

    $modal_html = '
        <div class="modal fade" id="shop_auto_delivery_notice" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="shop-modal modal-mini modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-colorful-header colorful-bg jb-green"><button class="close' . ($auto_delivery_notice_lists ? ' hide' : '') . '" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="colorful-make"></div><div class="text-center"><div class="em3x"><svg class="icon" viewBox="0 0 1024 1024" p-id="7086"><path d="M497.38035 839.365L146.90735 478.482l90.274-73.48L440.71035 566.58a2193.217 2193.217 0 0 1 520.398-457.429l21.033 50.399C749.24735 377.812 558.28835 684.187 497.38035 839.365z m442.81-394.473a389.652 389.652 0 0 1 6.286 71.304 432.6 432.6 0 1 1-270.723-400.76V27.268A535.203 535.203 0 0 0 512.12735 0.02a496.394 496.394 0 0 0-199.291 39.818 530.297 530.297 0 0 0-163.64 109.132A506.861 506.861 0 0 0 39.88035 312.68a517.896 517.896 0 0 0 0 398.584 530.297 530.297 0 0 0 109.131 163.64 508.368 508.368 0 0 0 163.711 109.202 517.896 517.896 0 0 0 398.583 0 530.297 530.297 0 0 0 163.64-109.132 508.383 508.383 0 0 0 109.131-163.64 497.162 497.162 0 0 0 39.819-199.29c0-23.081-2.062-48.224-4.267-71.304h-79.765v4.266z"></path></svg></div><div class="mt10 em12 padding-w10">恭喜您！支付成功</div></div></div>
                    ' . $modal_html . '
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function(){
                //屏幕宽度小于768时，弹窗底部对齐
                if($(window).width() < 768){
                    $(\'#shop_auto_delivery_notice\').addClass(\'bottom  flex jc\');
                }

                setTimeout(function(){
                    $(\'#shop_auto_delivery_notice\').modal({ backdrop: \'static\', show: true, keyboard: false });
                    ' . (!$auto_delivery_notice_lists ? '$.cookie(\'' . $cookie_key . '\', \'\', { path: \'/\', expires: -1 });' : '') . '
                }, 100);

                $(\'.auto-delivery-notice-received\').click(function(){
                    //弹出二次确认
                    if(confirm(\'请确认已保存收货内容！' . ($is_logged_in ? '如需再次查看，您可在用户中心->我的订单查看' : '关闭此弹窗后，内容将不再显示！') . '\')){
                        $.cookie(\'' . $cookie_key . '\', \'\', { path: \'/\', expires: -1 }); //删除cookie
                        $(\'#shop_auto_delivery_notice\').modal(\'hide\');
                    }
                     return false;
                });

            });
        </script>';

    echo $modal_html;
}
