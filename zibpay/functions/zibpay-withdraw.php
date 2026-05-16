<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-04-17 17:49:02
 * @LastEditTime : 2026-05-06 10:35:46
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|支付系统：提现功能 withdraw
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取提现按钮
function zibpay_get_withdraw_link($class = 'but radius c-white', $con = '立即提现<i style="margin:0 0 0 10px;" class="fa fa-angle-right"></i>')
{

    $args = array(
        'class'         => $class,
        'data_class'    => 'full-sm',
        'mobile_bottom' => true,
        'height'        => 386,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'apply_withdraw_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取提现记录按钮
function zibpay_get_withdraw_record_link($class = 'but', $con = '查看提现记录<i style="margin:0 0 0 6px;" class="fa fa-angle-right"></i>')
{

    $args = array(
        'class'         => $class,
        'data_class'    => 'full-sm',
        'mobile_bottom' => true,
        'height'        => 386,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'withdraw_record_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 用户提现记录明细
 * @param {*} $user_id
 * @param {*} $ice_perpage
 * @return {*}
 */
function zibpay_get_withdraw_record_lists($user_id = 0, $ice_perpage = 10)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }
    $paged  = zib_get_the_paged();
    $offset = $ice_perpage * ($paged - 1);

    $offset = $ice_perpage * ($paged - 1);

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'withdraw',
    );
    if (isset($_REQUEST['status'])) {
        $msg_get_args['status'] = $_REQUEST['status'];
    }

    $db_msg    = ZibMsg::get($msg_get_args, 'modified_time', $offset, $ice_perpage);
    $count_all = ZibMsg::get_count($msg_get_args);
    $lists     = '';
    if ($db_msg) {
        foreach ($db_msg as $msg) {
            //准备参数
            $meta        = maybe_unserialize($msg->meta);
            $price       = $meta['withdraw_price'];
            $create_time = date('Y-m-d H:i', strtotime($msg->create_time));
            $status      = '<span class="badg c-yellow mr10 em09">待处理</span>';
            if ($msg->status == 1) {
                $status = '<span class="badg c-blue mr10 em09">已提现</span>';
            } elseif ($msg->status == 2) {
                $status = '<span class="badg c-red mr10 em09">已拒绝</span>';
            }

            $__withdraw_price = $meta['withdraw_price'];
            $__service_price  = isset($meta['service_price']) ? $meta['service_price'] : 0;

            $__rebate_sum  = isset($meta['withdraw_detail']['rebate']) ? $meta['withdraw_detail']['rebate'] : 0;
            $__income_sum  = isset($meta['withdraw_detail']['income']) ? $meta['withdraw_detail']['income'] : 0;
            $__balance_sum = isset($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;

            $withdraw_details = '' . ($__rebate_sum ? '推广佣金' . $__rebate_sum . '元' : '') . ($__income_sum ? ' 创作分成' . $__income_sum . '元' : '') . ($__balance_sum > 0 ? ' 余额' . $__balance_sum . '元' : '') . ($__balance_sum < 0 ? ' 其中' . abs($__balance_sum) . '元转入余额' : '') . '';
            //折叠
            $mag_collapse = '';
            $mag_collapse .= '<div id="msg_collapse_' . $msg->id . '" class="collapse ml6">';
            $mag_collapse .= '<div class="muted-3-color em09">';
            $mag_collapse .= '<div class="mt10">提现金额： <span class="ml10">￥ ' . $__withdraw_price . '</span></div>';
            $mag_collapse .= $withdraw_details ? '<div class="mt10">提现详情： <span class="ml10">' . $withdraw_details . '</span></div>' : '';
            $mag_collapse .= '<div class="mt10">申请时间： <span class="ml10">' . $msg->create_time . '</span></div>';
            $mag_collapse .= !empty($meta['withdraw_message']) ? '<div class="mt10">申请留言： <span class="ml10">' . $meta['withdraw_message'] . '</span></div>' : '';
            if ($msg->status) {
                $mag_collapse .= '<div class="mt10">处理结果： <span class="ml10">' . $status . '</span></div>';
                if ($msg->status == 1) {
                    $mag_collapse .= '<div class="mt10">支付金额： <span class="ml10">' . ($__service_price > 0 ? '<span class="badg c-red mr6">' . ($__withdraw_price - $__service_price) . '元</span><span class="badg">手续费' . $__service_price . '元</span>' : '<span class="badg c-red mr6">' . $__withdraw_price . '元</span>') . '</span></div>';
                }
                $mag_collapse .= '<div class="mt10">处理时间： <span class="ml10">' . $msg->modified_time . '</span></div>';
                $mag_collapse .= !empty($meta['admin_message']) ? '<div class="mt10">处理反馈： <span class="ml10">' . $meta['admin_message'] . '</span></div>' : '';
            }
            $mag_collapse .= '</div>';
            $mag_collapse .= '</div>';

            //开始构建列表
            $lists .= '<div class="ajax-item border-bottom" style="padding:8px 0;">';
            $lists .= '<div data-toggle="collapse" data-target="#msg_collapse_' . $msg->id . '" class="collapsed pointer meta-time muted-color flex ac jsb"><div class="flex ac em09-sm">' . $status . $create_time . '</div><div class="em12">￥' . $price . '<i class="fa fa-angle-down ml10"></i></div></div>';
            $lists .= $mag_collapse;
            $lists .= '</div>';

            $ajax_url = esc_url(add_query_arg('action', 'withdraw_detail', admin_url('admin-ajax.php')));
        }
        $lists .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no');
    } else {
        $lists .= zib_get_ajax_null('暂无提现记录', 60, 'null-order.svg');
    }

    return $lists;
}

/**
 * @description: 获取用户提现记录的明细模态框
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_withdraw_record_modal($user_id)
{
    if (!$user_id) {
        return;
    }

    //提现记录AJAX tab-content
    $withdraw_ajax_href = esc_url(add_query_arg('action', 'withdraw_detail', admin_url('admin-ajax.php')));

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'withdraw',
    );
    $withdraw_count_all     = ZibMsg::get_count($msg_get_args);
    $msg_get_args['status'] = 1;
    $withdraw_count_1       = ZibMsg::get_count($msg_get_args);

    if (!$withdraw_count_all) {
        return zib_get_null('暂无提现记录', 40, 'null-money.svg');
    }

    //过滤
    $filter = '<div class="mb10">';
    $filter .= '<a ajax-replace="1" ajax-href="' . $withdraw_ajax_href . '" class="but mr10 ajax-next">全部 ' . $withdraw_count_all . '</a>';
    $filter .= '<a ajax-replace="1" ajax-href="' . add_query_arg('status', 1, $withdraw_ajax_href) . '" class="but mr10 ajax-next">已处理 ' . $withdraw_count_1 . '</a>';
    $filter .= '<a ajax-replace="1" ajax-href="' . add_query_arg('status', 0, $withdraw_ajax_href) . '" class="but mr10 ajax-next">待处理</a>';
    $filter .= '</div>';

    $lists = zibpay_get_withdraw_record_lists($user_id);
    $lists .= '<div class="post_ajax_loader" style="display: none;"><i class="placeholder s1 mt10" style="height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i><i class="placeholder s1 mt10" style="height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i><i class="placeholder s1 mt10" style="height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i> <i class="placeholder s1 mt10" style=" height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i> <i class="placeholder s1 mt10" style=" height: 27px; "></i><i class="placeholder s1 ml10" style=" height: 27px; width: calc(100% - 81px); "></i></div>';

    $header = '<div class="mb20 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-jpy"></i></span>提现记录</b></div>';

    return $header . '<div class="ajaxpager">' . $filter . '<div class="mini-scrollbar scroll-y max-vh5">' . $lists . '</div></div>';
}

/**
 * @description: 用户提现的模态框
 * @param {*}
 * @return {*}
 */
function zibpay_get_apply_withdraw_modal()
{

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $text_details   = _pz('pay_rebate_withdraw_text_details'); //文案
    $lowest_money   = (int) _pz('pay_rebate_withdraw_lowest_money'); //提现限制
    $service_charge = _pz('withdraw_service_charge'); //提现手续费
    if ($service_charge) {
        $text_details .= '<div class="mt6 c-yellow">向您付款时会扣除' . $service_charge . '%的手续费</div>';
    }

    //判断是否有正在提现的申请
    $withdraw_ing = (array) zibpay_get_user_withdraw_ing($user_id);
    if (!empty($withdraw_ing['meta']['withdraw_price'])) {
        $header = zib_get_modal_colorful_header('jb-yellow', zib_get_svg('money'), '提现正在处理中');

        $__withdraw_price = $withdraw_ing['meta']['withdraw_price'];
        $__service_price  = isset($withdraw_ing['meta']['service_price']) ? $withdraw_ing['meta']['service_price'] : 0;

        $__rebate_sum  = isset($withdraw_ing['meta']['withdraw_detail']['rebate']) ? $withdraw_ing['meta']['withdraw_detail']['rebate'] : 0;
        $__income_sum  = isset($withdraw_ing['meta']['withdraw_detail']['income']) ? $withdraw_ing['meta']['withdraw_detail']['income'] : 0;
        $__balance_sum = isset($withdraw_ing['meta']['withdraw_detail']['balance']) ? $withdraw_ing['meta']['withdraw_detail']['balance'] : 0;

        $table_lists = '';
        $table_lists .= '<tr><th>提现金额</th><td>￥' . $__withdraw_price . '</td><td class="px12">' . ($__rebate_sum ? '推广佣金' . $__rebate_sum . '元<br>' : '') . ($__income_sum ? '创作分成' . $__income_sum . '元<br>' : '') . ($__balance_sum > 0 ? '余额' . $__balance_sum . '元' : '') . ($__balance_sum < 0 ? '其中' . abs($__balance_sum) . '元转入余额' : '') . '</td></tr>';
        $table_lists .= $__service_price > 0 ? '<tr class="c-blue"><th>付款金额</th><td>￥' . ($__withdraw_price - $__service_price) . '</td><td class="px12">扣除手续费' . $__service_price . '元</td></tr>' : '';
        $table_lists .= '<tr><th>提交时间</th><td>' . $withdraw_ing['create_time'] . '</td><td></td></tr>';
        $table = '<table class="table table-bordered table-mini"><tbody class="muted-color">' . $table_lists . '</tbody></table>';

        $html = $header;
        $html .= '<div class="c-red mb10">您的提现正在处理中，请您耐心等待</div>';
        $html .= $table;
        $html .= '<div class="muted-color mb20">' . $text_details . '</div>';
        return $html;
    }

    //开始构建
    $all_effective_sum = 0;
    $lists             = '';
    $hidden            = '';

    //推广返佣
    $pay_rebate_s = _pz('pay_rebate_s');
    if ($pay_rebate_s) {
        $rebate_effective_data = zibpay_get_user_rebate_data($user_id, 'effective'); //佣金统计
        $all_effective_sum += $rebate_effective_data['sum'];
        $hidden .= '<input type="hidden" name="rebate_ids" value="' . $rebate_effective_data['ids'] . '">';
        $lists .= $rebate_effective_data['sum'] ? '<tr class="em09"><td>推广佣金</td><td>￥' . $rebate_effective_data['sum'] . '</td><td>共' . $rebate_effective_data['count'] . '笔佣金订单</td></tr>' : '';
    }

    //收入分成
    $pay_income_s = _pz('pay_income_s');
    if ($pay_income_s) {
        $income_price_effective = zibpay_get_user_income_data($user_id, 'effective'); //分成统计
        $all_effective_sum += $income_price_effective['sum'];
        $hidden .= '<input type="hidden" name="income_ids" value="' . $income_price_effective['ids'] . '">';
        $lists .= $income_price_effective['sum'] ? '<tr class="em09"><td>创作分成</td><td>￥' . $income_price_effective['sum'] . '</td><td>共' . $income_price_effective['count'] . '笔分成订单</td></tr>' : '';
    }

    //余额功能
    $pay_balance_s          = _pz('pay_balance_s');
    $pay_balance_withdraw_s = _pz('pay_balance_withdraw_s'); //允许将余额提现
    if ($pay_balance_s && $pay_balance_withdraw_s) {
        $user_balance = zibpay_get_user_balance($user_id); //余额统计
        $all_effective_sum += $user_balance;
        $lists .= $user_balance ? '<tr class="em09"><td>余额</td><td>￥' . $user_balance . '</td><td></td></tr>' : 0;
    }

    if (!$all_effective_sum) {
        $header = zib_get_modal_colorful_header('jb-blue', zib_get_svg('money'), '提现申请');
        return $header . zib_get_null('暂无可提现的金额', 20, 'null-money.svg', '', 280, 150);
    }

    $html = '';
    $but  = '';
    $lists .= '<tr class="c-blue"><th>合计</th><td>￥' . $all_effective_sum . '</td><td></td></tr>';
    $hidden .= '<input type="hidden" name="effective_sum" value="' . $all_effective_sum . '">
                <input type="hidden" name="user_id" value="' . $user_id . '">
                <input type="hidden" name="action" value="apply_withdraw">';
    $hidden .= wp_nonce_field('apply_withdraw', '_wpnonce', false, false); //安全效验

    $table          = '<table class="table table-bordered table-mini"><thead class=""><tr><th>类型</th><th>金额</th><th>描述</th></tr></thead><tbody class="muted-color">' . $lists . '</tbody></table>';
    $collection_set = zib_get_user_collection_set_link('but c-yellow mr10 rewards-tabshow padding-lg', '收款设置', true);

    $rewards_img_urls = zib_get_user_rewards_img_urls($user_id);
    $weixin           = $rewards_img_urls['weixin'];
    $alipay           = $rewards_img_urls['alipay'];

    if (!$weixin && !$alipay) {
        $but .= '<div class="c-red mb20">您暂未设置收款码，请先完成收款设置</div>';
        $but .= '<div class="modal-buts but-average">' . $collection_set . '<div>';
    } elseif ($all_effective_sum >= $lowest_money) {
        if ($pay_balance_s) {
            //余额功能开启，可选部分金额提现
            $table .= '<div class="em09 muted-2-color mb6">提现金额</div>';
            $table .= '<div class="dependency-box mb20">
                        <div>
                            <label class="badg mr10 pointer"><input type="radio" checked="checked" name="withdraw_money_type" value="all"> 全额提现 ￥' . $all_effective_sum . '</label>
                            <label class="badg mr10 pointer"><input type="radio" name="withdraw_money_type" value="custom"> 提现部分金额</label>
                        </div>
                        <div style="display: none;" data-controller="withdraw_money_type" data-condition="==" data-value="custom">
                        <div class="em09 muted-2-color mt6">输入金额</div>
                        <div class="relative flex ab">
                            <span class="ml6 mr10 muted-color">￥</span>
                            <input name="custom_money" type="number" limit-min="' . $lowest_money . '" limit-max="' . (int) $all_effective_sum . '"  warning-max="最高可提现1$元" warning-min="最低需提现1$元" style="padding: 0;" class="line-form-input em16 key-color">
                            <i class="line-form-line"></i>
                        </div>
                        <div class="em09 c-yellow mt3">最低提现' . (int) $lowest_money . '元，最高提现' . (int) $all_effective_sum . '元' . ($pay_rebate_s || $pay_balance_s ? '，如果提现金额低于' . ($pay_rebate_s ? '佣金' : '') . ($pay_balance_s ? '分成' : '') . '总金额，剩余' . ($pay_rebate_s ? '佣金' : '') . ($pay_balance_s ? '分成' : '') . '则会全部转入余额' : '') . (!$pay_balance_withdraw_s ? '<span class="c-red">，转入余额后的金额将无法提现，建议您全额提现</span>' : '') . '</div>
                        </div>
                    </div>';
        }

        $but .= $collection_set . $hidden;
        $but .= '<button type="button" zibajax="submit" class="but c-blue padding-lg">提交申请</button>';
        $but = '<div class="mr6 mb20"><input type="text" name="message" placeholder="给客服留言" class="form-control"></div><div class="modal-buts but-average">' . $but . '<div>';
    } else {
        $but .= '<div class="c-red mb20">您当前的可提现金额低于' . $lowest_money . '元，暂时不能申请提现</div>';
        $but .= '<div class="modal-buts but-average">' . $collection_set . '<div>';
    }

    $header = '<div class="mb20 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="toggle-radius mr10 b-theme"><i class="fa fa-jpy"></i></span>提现申请</b></div>';
    $html .= $header;
    $html .= '<div class="muted-box muted-2-color mb20">' . $text_details . '</div>';
    $html .= '<form>' . $table . $but . '</form>';

    return $html;

}

/**
 * @description: 获取用户正在提现的订单
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_withdraw_ing($user_id)
{

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'withdraw',
        'status'    => 0,
    );
    return ZibMsg::get_row($msg_get_args);
}

/**
 * @description: 设置订单的提现状态为正在提现
 * @param {*}
 * @return {*}
 */
function zibpay_withdraw_order_set_ing($type = 'rebate', $ids = array())
{
    global $wpdb;
    if (in_array($type, ['rebate', 'income'])) {
        $status_key = $type . '_status';
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }
        return $wpdb->query("update $wpdb->zibpay_order set $status_key = 3 where id IN ($ids)");
    }

    return false;
}

/**
 * @description:
 * @param {*} $id
 * @param {*} $is_allow 是否批准提现
 * @param {*} $msg 给用户留言
 * @return {*}
 */
function zibpay_withdraw_process($id, $is_allow = true, $msg = '')
{
    //查找数据
    global $wpdb;
    $msg_db_where = array('id' => $id, 'type' => 'withdraw', 'status' => 0);
    $msg_db       = (array) ZibMsg::get_row($msg_db_where);
    if (empty($msg_db['meta']['withdraw_price'])) {
        return false;
    }

    $user_id = $msg_db['send_user'];
    $meta    = $msg_db['meta'];

    //设置推广返佣订单状态
    $status_set    = $is_allow ? 1 : 0;
    $rebate_orders = !empty($meta['withdraw_orders']['rebate']) ? $meta['withdraw_orders']['rebate'] : 0;
    $rebate_orders = is_array($rebate_orders) ? implode(',', $rebate_orders) : $rebate_orders;
    if ($rebate_orders) {

        $detail = '';
        if ($status_set) {
            $detail_set = maybe_serialize(array(
                'withdraw_id'   => $id,
                'withdraw_time' => current_time('Y-m-d H:i:s'),
            ));

            $detail = ",rebate_detail = '$detail_set'";
        }

        $wpdb->query("update $wpdb->zibpay_order set rebate_status = $status_set $detail where id IN ($rebate_orders)");
    }

    //设置创作分成订单状态
    $income_orders = !empty($meta['withdraw_orders']['income']) ? $meta['withdraw_orders']['income'] : 0;
    $income_orders = is_array($income_orders) ? implode(',', $income_orders) : $income_orders;
    if ($income_orders) {
        $detail = '';
        if ($status_set) {

            $detail_set = maybe_serialize(array(
                'withdraw_id'   => $id,
                'withdraw_time' => current_time('Y-m-d H:i:s'),
            ));

            $detail = ",income_detail = '$detail_set'";
        }

        $wpdb->query("update $wpdb->zibpay_order set income_status = $status_set $detail where id IN ($income_orders)");
    }

    //设置余额处理
    $balance_sum = !empty($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;
    if ($balance_sum > 0) {
        //提现金额：先把冻结金额还原
        $user_balance = zibpay_get_user_balance($user_id);
        update_user_meta($user_id, 'balance', ($user_balance + $balance_sum));
        update_user_meta($user_id, 'balance_withdraw_ing', 0);

        if ($is_allow) {
            //提现批准，设置余额扣除
            if (!zibpay_update_user_balance($user_id, array(
                'value' => 0 - $balance_sum, //转为负数，扣除余额
                'type'  => '提现',
                'desc'  => '', //说明
            ))) {
                //强制扣除，避免出现余额不足的而没有扣除正确的余额
                $_user_balance = $user_balance <= 0 ? 0 : $user_balance;
                update_user_meta($user_id, 'balance', $_user_balance);
            }
        }
    }

    if ($balance_sum < 0) {
        //提现金额小于0，也就是转入余额
        update_user_meta($user_id, 'balance_add_ing', 0); //转入中归零

        if ($is_allow) {
            //提现批准，设置将额外金额转入余额
            zibpay_update_user_balance($user_id, array(
                'value' => abs($balance_sum), //负数转正数，添加余额
                'type'  => '提现转入',
                'desc'  => '提现剩余金额自动转入余额', //说明
            ));
        }
        //提现拒绝，余额不变
    }

    //保存管理员留言
    ZibMsg::set_meta($id, 'admin_message', $msg);
    ZibMsg::set_status($id, ($is_allow ? 1 : 2));

    //准备新消息，发送给提现人
    $__withdraw_price = $meta['withdraw_price'];
    $__service_price  = isset($meta['service_price']) ? $meta['service_price'] : 0;
    $__rebate_sum     = isset($meta['withdraw_detail']['rebate']) ? $meta['withdraw_detail']['rebate'] : 0;
    $__income_sum     = isset($meta['withdraw_detail']['income']) ? $meta['withdraw_detail']['income'] : 0;
    $__balance_sum    = isset($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;

    $new_msg_con = '您好！ ' . zib_get_user_name_link($user_id) . '<br>';
    $new_msg_con .= '您的提现申请' . ($is_allow ? '已处理完成' : '被拒绝') . '<br>';
    $new_msg_con .= '提现金额：￥' . $__withdraw_price . '<br>';
    $new_msg_con .= ($__service_price > 0 ? '付款金额：￥' . ($__withdraw_price - $__service_price) . '（扣除' . $__service_price . '元手续费）' : '') . '<br>';
    $new_msg_con .= '包含：' . ($__rebate_sum ? '<br /> 推广佣金' . $__rebate_sum . '元' : '') . ($__income_sum ? '<br /> 创作分成' . $__income_sum . '元' : '') . ($__balance_sum > 0 ? '<br /> 余额' . $__balance_sum . '元' : '') . ($__balance_sum < 0 ? '<br /> 其中' . abs($__balance_sum) . '元转入余额' : '') . '<br>';
    $new_msg_con .= '<br />提交时间：' . $msg_db['create_time'] . '<br>';
    $new_msg_con .= '处理时间：' . current_time('Y-m-d H:i:s') . '<br>' . $msg;

    $new_msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'withdraw_reply',
        'title'        => '您的提现申请' . ($is_allow ? '已处理完成' : '被拒绝') . '，提现金额：￥' . $__withdraw_price,
        'content'      => $new_msg_con,
        'parent'       => $id,
        'meta'         => '',
        'other'        => '',
    );

    if (zib_msg_is_allow_receive($user_id, 'withdraw_reply')) {
        ZibMsg::add($new_msg_arge);
    }

    //添加挂钩
    do_action('withdraw_process_newmsg', $new_msg_arge, $msg_db);
    do_action('withdraw_process', $msg_db, $is_allow, $msg);
    return true;
}

/**
 * @description: 设置订单的提现状态为正在提现
 * @param {*} $user_id
 * @param {*} $money 金额
 * @return {*}
 */
function zibpay_withdraw_balance_set_ing($user_id, $money)
{
    global $wpdb;
    if ($money > 0) {
        $user_balance = zibpay_get_user_balance($user_id);

        $money = $money >= $user_balance ? $user_balance : $money;

        update_user_meta($user_id, 'balance', ($user_balance - $money));
        update_user_meta($user_id, 'balance_withdraw_ing', $money);
    }
    if ($money < 0) {
        update_user_meta($user_id, 'balance_add_ing', $money);
    }

    return true;
}

//获取待提现数量
function zibpay_get_withdraw_pending_count()
{
    $withdraw_count = ZibMsg::get_count(array(
        'type'   => 'withdraw',
        'status' => 0,
    ));

    return (int) $withdraw_count;
}
