<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-29 19:22:40
 * @LastEditTime : 2026-05-06 10:06:23
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//收银台
function zibpay_ajax_pay_cashier_modal()
{
    $id         = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $price_type = !empty($_REQUEST['price_type']) ? $_REQUEST['price_type'] : '';
    //判断文章是否存在
    $post = get_post($id);
    if (!$post) {
        zib_ajax_notice_modal('danger', '内容不存在或参数错误');
    }

    $user_id  = get_current_user_id();
    $pay_mate = get_post_meta($id, 'posts_zibpay', true);
    if (empty($pay_mate['pay_type']) || 'no' == $pay_mate['pay_type']) {
        zib_ajax_notice_modal('danger', '当前内容未设置付费，或参数错误');
    }

    $is_points = zibpay_post_is_points_modo($pay_mate);
    if ($is_points && !$user_id) {
        zib_ajax_notice_modal('danger', '请先登录');
    }

    //判断购买限制
    $pay_limit = !empty($pay_mate['pay_limit']) ? (int) $pay_mate['pay_limit'] : '0';
    if ($pay_limit > 0 && !$user_id) {
        zib_ajax_notice_modal('danger', '请先登录');
    }

    //购买权限
    if ($pay_limit > 0 && (_pz('pay_user_vip_1_s', true) || _pz('pay_user_vip_2_s', true))) {
        if (!$user_id) {
            zib_ajax_notice_modal('danger', '请先登录');
        }

        $vip_icon = zib_get_svg('vip_' . $pay_limit, '0 0 1024 1024', 'mr3');
        //开始限制购买权限
        $user_vip_level = zib_get_user_vip_level($user_id);

        if (!$user_vip_level) {
            $pay_vip_text = '开通会员';
        } elseif ($user_vip_level < $pay_limit) {
            $pay_vip_text = '升级' . _pz('pay_user_vip_' . $pay_limit . '_name');
        }

        if (!$user_vip_level || $user_vip_level < $pay_limit) {
            $pay_button = '<div class="mb20 em09"><span class="badg c-yellow em09"><i class="fa fa-fw fa-info-circle fa-fw mr6" aria-hidden="true"></i>您暂无购买权限，请先' . $pay_vip_text . '</span></div>';
            $pay_button .= '<a href="javascript:;" vip-level="' . $pay_limit . '" class="em09 but btn-block jb-vip' . $pay_limit . ($user_id ? ' pay-vip' : ' signin-loader') . ' padding-lg">' . $vip_icon . $pay_vip_text . '</a>';
            zib_ajax_notice_modal('warning', $pay_button);
        }
    }

    $_modal = $is_points ? zibpay_pay_points_cashier_modal($id, $price_type) : zibpay_pay_cashier_modal($id, $price_type);
    if (!$_modal) {
        zib_ajax_notice_modal('danger', '参数异常');
    }
    echo $_modal;
    exit;
}
add_action('wp_ajax_pay_cashier_modal', 'zibpay_ajax_pay_cashier_modal');
add_action('wp_ajax_nopriv_pay_cashier_modal', 'zibpay_ajax_pay_cashier_modal');

//用户订单列表
function zibpay_ajax_user_order()
{
    $html = zibpay_get_user_order();
    echo '<body style="display:none;"><main><div class="ajaxpager" id="user_order_lists">' . $html . '</div></main></body>';
    exit;
}
add_action('wp_ajax_user_pay_order', 'zibpay_ajax_user_order');

//订单详情
function zibpay_ajax_order_details_modal()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;
    $order    = zibpay::get_order($order_id);

    if (!$order) {
        zib_ajax_notice_modal('danger', '订单不存在');
    }

    //验证权限
    if ($order['user_id'] != get_current_user_id() && !zib_current_user_can('view_order')) {
        zib_ajax_notice_modal('danger', '您无权限查看此订单');
    }

    echo zibpay_get_order_details_modal($order);
    exit;
}
add_action('wp_ajax_order_details_modal', 'zibpay_ajax_order_details_modal');
add_action('wp_ajax_nopriv_order_details_modal', 'zibpay_ajax_order_details_modal');

//AJAX获取用户提现记录列表
function zibpay_ajax_rebate_user_withdraw_detail()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    //准备查询参数
    $user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : $user_id;
    if ($user_id != $user_id && !zib_current_user_can('view_withdraw_record')) {
        zib_send_json_error('您无权限查看此信息');
    }

    $ice_perpage = !empty($_REQUEST['ice_perpage']) ? (int) $_REQUEST['ice_perpage'] : 10;

    zib_ajax_send_ajaxpager(zibpay_get_withdraw_record_lists($user_id, $ice_perpage));
}
add_action('wp_ajax_withdraw_detail', 'zibpay_ajax_rebate_user_withdraw_detail');

//AJAX申请提现模态框
function zibpay_ajax_withdraw_record_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    echo zibpay_get_withdraw_record_modal($user_id);
    exit;
}
add_action('wp_ajax_withdraw_record_modal', 'zibpay_ajax_withdraw_record_modal');

//AJAX申请提现模态框
function zibpay_ajax_modal_apply_withdraw()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    echo zibpay_get_apply_withdraw_modal($user_id);
    exit;
}
add_action('wp_ajax_apply_withdraw_modal', 'zibpay_ajax_modal_apply_withdraw');

//ajax处理用户提现申请
function zibpay_ajax_apply_withdraw()
{

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $user_id = get_current_user_id();
    if (!$user_id || empty($_POST['user_id']) || $_POST['user_id'] != $user_id) {
        zib_send_json_error('处理出错，请刷新后重试');
    }
        //函数节流
    zib_ajax_debounce('apply_withdraw', $user_id);

    //判断是否有正在提现的申请
    $withdraw_ing = (array) zibpay_get_user_withdraw_ing($user_id);
    if (!empty($withdraw_ing['meta']['withdraw_price'])) {
        zib_send_json_error('您的申请已提交，请耐心等待');
    }

    $rewards_img_urls = zib_get_user_rewards_img_urls($user_id);
    $weixin           = $rewards_img_urls['weixin'];
    $alipay           = $rewards_img_urls['alipay'];

    if (!$weixin && !$alipay) {
        zib_send_json_error('请先完成收款设置');
    }

    //推广佣金
    $all_effective_sum = 0;
    $pay_rebate_s      = _pz('pay_rebate_s');
    $__rebate_sum      = 0;
    $__rebate_ids      = '';
    if ($pay_rebate_s) {
        $rebate_effective_data = zibpay_get_user_rebate_data($user_id, 'effective'); //佣金统计
        if (!isset($_REQUEST['rebate_ids']) || $_REQUEST['rebate_ids'] != $rebate_effective_data['ids']) {
            zib_send_json_error('您的推广佣金发生变动，请刷新页面后重新申请');
        }
        $all_effective_sum += $rebate_effective_data['sum'];
        $__rebate_sum = $rebate_effective_data['sum'];
        $__rebate_ids = $rebate_effective_data['ids'];
    }

    //收入分成
    $pay_income_s = _pz('pay_income_s');
    $__income_sum = 0;
    $__income_ids = '';
    if ($pay_income_s) {
        $income_price_effective = zibpay_get_user_income_data($user_id, 'effective'); //分成统计
        if (!isset($_REQUEST['income_ids']) || $_REQUEST['income_ids'] != $income_price_effective['ids']) {
            zib_send_json_error('您的创作分成发生变动，请刷新页面后重新申请');
        }
        $all_effective_sum += $income_price_effective['sum'];
        $__income_sum = $income_price_effective['sum'];
        $__income_ids = $income_price_effective['ids'];
    }

    //余额功能
    $pay_balance_s          = _pz('pay_balance_s');
    $pay_balance_withdraw_s = _pz('pay_balance_withdraw_s');
    $__user_balance         = 0;
    if ($pay_balance_s && $pay_balance_withdraw_s) {
        $user_balance = zibpay_get_user_balance($user_id); //余额统计
        $all_effective_sum += $user_balance;
        $__user_balance = $user_balance;
    }
    $all_effective_sum = round((float) $all_effective_sum, 2);
    //可用余额判断，避免出现时间差而导致金额错误
    $effective_sum = !empty($_REQUEST['effective_sum']) ? round((float) $_REQUEST['effective_sum'], 2) : 0;
    if (!$effective_sum || $effective_sum > $all_effective_sum) {
        zib_send_json_error('您的余额有变动，请刷新页面后重新申请');
    }

    //提现金额判断
    $withdraw_money_type = !empty($_REQUEST['withdraw_money_type']) ? $_REQUEST['withdraw_money_type'] : '';
    if ($withdraw_money_type === 'custom') {
        //自定义提现金额
        $custom_money = !empty($_REQUEST['custom_money']) ? round((float) $_REQUEST['custom_money'], 2) : 0;
        if (!$custom_money || $custom_money <= 0) {
            zib_send_json_error('请输入有效的提现金额');
        }
        $lowest_money = (int) _pz('pay_rebate_withdraw_lowest_money'); //提现限制
        if ($custom_money < $lowest_money) {
            zib_send_json_error('最低提现' . $lowest_money . '元，请修改您的提现金额');
        }
        if ($custom_money > $all_effective_sum) {
            zib_send_json_error('您最高可提现' . (int) $all_effective_sum . '元，请修改您的提现金额');
        }
        $__withdraw_price = $custom_money;
    } else {
        //全额提现
        if ((int) $effective_sum !== (int) $all_effective_sum) {
            zib_send_json_error('您的资产有变动，请刷新页面后重新申请');
        }
        $__withdraw_price = $all_effective_sum;
    }

    //判断结束，开始处理 ----------------------------

    //修改推广返佣的状态
    if ($__rebate_ids) {
        zibpay_withdraw_order_set_ing('rebate', $__rebate_ids);
    }
    //修改创作分成的状态
    if ($__income_ids) {
        zibpay_withdraw_order_set_ing('income', $__income_ids);
    }
    //修改余额的状态
    if ($withdraw_money_type === 'custom') {
        $__balance_sum = $__withdraw_price - ($__income_sum + $__rebate_sum);
    } else {
        //全额提现
        $__balance_sum = $__user_balance;
    }
    
    zibpay_withdraw_balance_set_ing($user_id, $__balance_sum);

    // 开始记录消息系统
    $service_charge   = _pz('withdraw_service_charge'); //提现手续费费率
    $__service_charge = round(($__withdraw_price * $service_charge) / 100, 2); //手续费
    $__payment_price  = $__withdraw_price - $__service_charge; //支付金额
    $process_url      = add_query_arg(array('page' => 'zibpay_withdraw', 'status' => '0'), admin_url('admin.php')); //佣金处理链接
    $__message        = !empty($_REQUEST['message']) ? esc_attr($_REQUEST['message']) : '';

    //准备通知消息
    $msg_con = '';
    $msg_con .= '用户：' . zib_get_user_name_link($user_id) . '，正在申请佣金提现' . '<br>';
    $msg_con .= '提现金额：' . $__withdraw_price . '元<br />';
    $msg_con .= '需支付金额：' . $__payment_price . '元' . ($__service_charge > 0 ? '(扣除' . $__service_charge . '元手续费)' : '') . '<br>';
    $msg_con .= '包含：' . ($__rebate_sum ? '推广佣金' . $__rebate_sum . '元. ' : '') . ($__income_sum ? '创作分成' . $__income_sum . '元. ' : '') . ($__balance_sum > 0 ? '余额' . $__balance_sum . '元. ' : '') . ($__balance_sum < 0 ? '其中' . abs($__balance_sum) . '元转入余额. ' : '') . '<br>';
    $msg_con .= '申请时间：' . current_time('Y-m-d H:i:s') . '<br>';
    $msg_con .= '<br>';
    $msg_con .= $__message ? '用户留言：' . '<br>' . $__message . '<br /><br />' : '';
    $msg_con .= '您可以点击下方按钮快速处理此申请' . '<br>';
    $msg_con .= '<a target="_blank" style="margin-top: 20px;" class="but jb-blue padding-lg" href="' . esc_url($process_url) . '">立即处理</a>' . '<br>';

    $msg_args = array(
        'send_user'    => $user_id,
        'receive_user' => 'admin',
        'type'         => 'withdraw',
        'title'        => '有新的提现申请待处理-用户：' . get_userdata($user_id)->display_name . '，金额：￥' . $__withdraw_price,
        'content'      => $msg_con,
        'meta'         => array(
            'withdraw_price'   => $__withdraw_price,
            'service_price'    => $__service_charge,
            'withdraw_message' => esc_sql($__message),
            'withdraw_orders'  => array(
                'rebate' => $__rebate_ids,
                'income' => $__income_ids,
            ),
            'withdraw_detail'  => array(
                'rebate'  => $__rebate_sum,
                'income'  => $__income_sum,
                'balance' => $__balance_sum,
            ),
        ),
    );

    //创建消息
    $add_msg = ZibMsg::add($msg_args);
    if (!$add_msg) {
        zib_send_json_error('提现系统出现错误，请与客服联系');
    }
    //添加处理挂钩
    do_action('user_apply_withdraw', $msg_args);

    zib_send_json_success(array('msg' => '提交成功，等待客服处理', 'reload' => 1));
}
add_action('wp_ajax_apply_withdraw', 'zibpay_ajax_apply_withdraw');

//后台导出卡密数据
function zibpay_ajax_card_pass_export()
{
    if (!is_super_admin()) {
        wp_die('暂无此权限');
    }

    @set_time_limit(0);

    $export_format = !empty($_REQUEST['export_format']) ? esc_sql($_REQUEST['export_format']) : 'xls';
    $where         = array();
    $type          = !empty($_REQUEST['type']) ? esc_sql($_REQUEST['type']) : '';
    global $wpdb;
    $DB = ZibDB::table($wpdb->zibpay_card_password);
    if (isset($_REQUEST['status']) && $_REQUEST['status'] !== 'all') {
        $DB->where('status', $_REQUEST['status']);
    }
    if ($type) {
        $DB->where('type', $type);
    }

    switch ($type) {
        case 'balance_charge': //余额充值
            $field    = 'card,password,meta,other,status';
            $title    = array('卡号', '密码', '面额', '备注', '状态');
            $data_map = 'zib_card_pass_export_balance_charge_map';
            break;
        case 'points_exchange': //积分兑换
            $field    = 'card,password,meta,other,status';
            $title    = array('卡号', '密码', '面额', '备注', '状态');
            $data_map = 'zib_card_pass_points_exchange_charge_map';
            break;

        case 'vip_exchange': //会员兑换
            $field    = 'card,password,meta,other,status';
            $title    = array('卡号', '密码', '兑换会员', '备注', '状态');
            $data_map = 'zib_card_pass_vip_exchange_charge_map';
            break;

        case 'invit_code': //邀请码注册
            $field    = 'password,meta,status,other';
            $title    = array('邀请码', '奖励', '状态', '备注');
            $data_map = 'zib_card_pass_export_invit_code_map';
            break;

        case 'coupon': //优惠券
            $field    = 'password,post_id,meta,other';
            $title    = array('优惠码', '商品ID', '优惠折扣', '可使用次数', '已使用次数', '优惠码名称', '备注');
            $data_map = 'zib_card_pass_export_coupon_map';
            break;

        case 'vip_coupon': //会员优惠券-暂未启用
            $field    = 'password,meta,status,other';
            $title    = array('邀请码', '奖励', '状态', '备注');
            $data_map = 'zib_card_pass_export_invit_code_map';
            break;

        case 'custom': //自定义
            $field    = 'card,password,other,meta';
            $title    = array('卡号', '密码', '备注', '状态');
            $data_map = 'zib_card_pass_export_custom_map';
            break;

    }

    $db_data  = $DB->field($field)->select()->toArrayMap($data_map);
    $filename = $type . '_' . gmdate('d_m_Y');
    if (!$db_data) {
        wp_die('暂无可导出的内容');
    }

    switch ($export_format) {
        case 'text':
            $text_division = !empty($_REQUEST['text_division']) ? wp_unslash($_REQUEST['text_division']) : ' ';

            header('Content-type:application/octet-stream');
            header('Accept-Ranges:bytes');
            header('Content-Disposition:attachment;filename=' . $filename . '.txt');
            header('Pragma: no-cache');
            header('Pragma: public');
            header('Expires: 0');
            $data = $db_data;
            if (!empty($data)) {
                $_data = array();
                foreach ($data as $val) {
                    $val     = (array) $val;
                    $_data[] = implode($text_division, $val);
                }
                echo implode("\n", $_data);
            }

            break;
        default:
            zib_export_excel($db_data, $title, $filename);
    }

    exit;
}
add_action('wp_ajax_card_pass_export', 'zibpay_ajax_card_pass_export');

//导出数据处理：导出优惠券
function zib_card_pass_export_coupon_map($data)
{
    $title  = array('优惠码', '商品ID', '优惠折扣', '可使用次数', '已使用次数', '优惠码名称', '备注');
    $coupon = zibpay_filter_coupon_data($data);

    return array(
        'password'      => $coupon['password'],
        'post_id'       => $coupon['post_id'],
        'discount_text' => $coupon['discount_text'],
        'reuse'         => $coupon['reuse'],
        'used_count'    => $coupon['used_count'],
        'title'         => $coupon['title'],
        'other'         => $coupon['other'],
    );
}

//导出数据处理：导出积分兑换
function zib_card_pass_points_exchange_charge_map($data)
{
    $data           = (array) $data;
    $data['status'] = $data['status'] === 'used' ? '已使用' : '未使用';
    $data['meta']   = zibpay_get_pass_exchange_points($data);
    return $data;
}

//导出数据处理：导出充值卡
function zib_card_pass_export_balance_charge_map($data)
{
    $data           = (array) $data;
    $data['status'] = $data['status'] === 'used' ? '已使用' : '未使用';
    $data['meta']   = zibpay_get_recharge_card_price($data);
    return $data;
}

//导出数据处理：导出充值卡
function zib_card_pass_vip_exchange_charge_map($data)
{
    $data           = (array) $data;
    $data['status'] = $data['status'] === 'used' ? '已使用' : '未使用';
    $meta           = zibpay_get_vip_exchange_card_data($data);
    $data['meta']   = build_query(array(
        'level' => $meta['level'],
        'time'  => $meta['time'],
        'unit'  => $meta['unit'], //天还是月
    ));

    return $data;
}

//导出数据处理：邀请码
function zib_card_pass_export_invit_code_map($data)
{
    $data           = (array) $data;
    $meta           = maybe_unserialize($data['meta']);
    $data['status'] = $data['status'] === 'used' ? '已使用' : '未使用';
    $data['meta']   = '无奖励';

    if (isset($meta['reward']) && is_array($meta['reward'])) {
        $data['meta'] = build_query($meta['reward']);
    }

    return $data;
}

function zib_card_pass_export_custom_map($data)
{
    $data = (array) $data;
    $meta = maybe_unserialize($data['meta']);
    if (!empty($meta['shipped_order_id'])) {
        $data['meta'] = '已售[OrderID:' . $meta['shipped_order_id'] . ']';
    } else {
        $data['meta'] = '';
    }
    return $data;
}

function zibpay_ajax_admin_assets_details()
{
    if (!is_super_admin()) {
        echo '权限不足';
        exit;
    }
    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;

    $balance_record_lists = zibpay_get_user_balance_record_lists($user_id);
    $points_record_lists  = zibpay_get_user_points_record_lists($user_id);

    echo '<div class="zib-tab">
    <div class="flex ac mb20">
        <div class="active"><a class="but zib-tab-toggle" href="javascript:;" tab-id="1">余额记录</a></div>
        <div class=""><a class="but zib-tab-toggle" href="javascript:;" tab-id="2">积分记录</a></div>
    </div>
    <div class="zib-tab-content">
        <div class="zib-tab-pane active in max-vh5" tab-id="1">' . $balance_record_lists . '</div>
        <div class="zib-tab-pane max-vh5" tab-id="2">' . $points_record_lists . '</div>
    </div>
    </div>';

    exit;

}
add_action('wp_ajax_admin_assets_details', 'zibpay_ajax_admin_assets_details');

function zibpay_ajax_admin_paydown_log()
{
    if (!is_super_admin()) {
        echo '权限不足';
        exit;
    }
    $id        = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $type      = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'post';
    $paged     = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $page_size = 50;
    $count     = 0;

    if (!$id) {
        echo '参数错误';
        exit;
    }

    $meta_key = 'pay_down_log';
    $record   = $type === 'user' ? zib_get_user_meta($id, $meta_key, true) : zib_get_post_meta($id, $meta_key, true);
    if ($type === 'user') {
        $h_user         = get_userdata($id);
        $h_display_name = isset($h_user->display_name) ? $h_user->display_name : 'user_id:' . $id;
        $header         = '<div class="border-title">用户<b class="c-blue">[' . $h_display_name . ']</b>的下载记录</div>';
    } else {
        $h_post       = get_post($id);
        $h_post_title = isset($h_post->post_title) ? zib_str_cut($h_post->post_title, 0, 10) : 'post_id:' . $id;
        $header       = '<div class="border-title">文章<b class="c-blue">[' . $h_post_title . ']</b>的下载记录</div>';
    }

    $lists = '';
    if ($record && is_array($record)) {
        //数组分页
        $count    = count($record);
        $record   = array_chunk($record, $page_size);
        $record_p = $record[$paged - 1];
        foreach ($record_p as $k => $v) {
            $paid_name = '<badge class="badg badg-sm mr6 c-yellow">' . zibpay_get_paid_type_name($v['paid_type']) . '</badge>';
            $down_id   = '<div class="em09">资源序号：' . ($v['down_id'] + 1) . '</div>';
            $ip        = !empty($v['ip']) ? '<div class="em09">IP地址：' . $v['ip'] . '</div>' : '';
            $order_num = !empty($v['order_num']) ? '<div class="em09">订单号：<a href="' . zibpay_get_admin_shop_order_url('search=') . $v['order_num'] . '" target="_blank">' . $v['order_num'] . '</a></div>' : '';

            if ($type === 'user') {
                //需要输出文章信息
                $post   = get_post($v['post_id']);
                $_name  = isset($post->post_title) ? zib_str_cut($post->post_title, 0, 18) : 'post_id:' . $v['post_id'];
                $m_link = zibpay_get_paydown_log_admin_link('post', $v['post_id'], 'ml10 mr10 c-blue', '查看此文章下载记录');
            } else {
                //需要输出用户信息
                $user   = get_userdata($v['user_id']);
                $_name  = '未登录用户';
                $m_link = '';
                if (isset($user->display_name)) {
                    $_name  = zib_str_cut($user->display_name, 0, 18);
                    $m_link = zibpay_get_paydown_log_admin_link('user', $v['user_id'], 'ml10 mr10 c-blue', '查看此用户下载记录');
                }
            }

            $lists .= '<div class="border-bottom padding-h10">
                        <div>
                            <div class="mb6">' . $paid_name . $_name . '</div>' . $down_id . $order_num . $ip . '<div class="flex jsb ab em09"><span>时间：' . $v['time'] . '</span>' . $m_link . '</div>
                        </div>
                </div>';
        }

        $paged_html = '';
        if (isset($record[$paged - 2])) {
            //有下一页
            $paged_html .= zibpay_get_paydown_log_admin_link($type, $id, 'but c-yellow', '上一页', $paged - 1);
        }

        if (isset($record[$paged])) {
            //有下一页
            $paged_html .= zibpay_get_paydown_log_admin_link($type, $id, 'but c-blue', '下一页', $paged + 1);
        }

        $lists .= $paged_html ? '<div class="padding-h10 flex jc"><span class="mr10">共' . $count . '条 第' . $paged . '页</span>' . $paged_html . '</div>' : '';
    }

    if (!$lists) {
        $lists = zib_get_null('暂无下载记录', 42, 'null-order.svg');
    }

    echo $header . '<div class="max-vh5">' . $lists . '</div>';
    exit;
}
add_action('wp_ajax_admin_paydown_log', 'zibpay_ajax_admin_paydown_log');

//购买会员
function zibpay_pay_vip_modal()
{
    if (!is_user_logged_in()) {
        zib_send_json_error(array('ys' => 'danger', 'msg' => '请先登录', 'code' => 'no_logged'));
    }

    $modal = zibpay_get_pay_uservip_modal();
    zib_send_json_success(array('html' => $modal));
}
add_action('wp_ajax_pay_vip', 'zibpay_pay_vip_modal');
add_action('wp_ajax_nopriv_pay_vip', 'zibpay_pay_vip_modal');

//积分兑换会员
function zibpay_vip_points_exchange_modal()
{
    if (!_pz('points_s', true) || !_pz('pay_vip_points_exchange_s', true) || (!_pz('pay_user_vip_1_s', true) && !_pz('pay_user_vip_2_s', true))) {
        zib_ajax_notice_modal('danger', '当前功能已关闭');
    }

    if (!is_user_logged_in()) {
        zib_ajax_notice_modal('danger', '请先登录');
    }

    echo zibpay_get_vip_points_exchange_modal();
    exit;
}
add_action('wp_ajax_vip_points_exchange_modal', 'zibpay_vip_points_exchange_modal');

//ajax验证优惠码
function zibpay_ajax_coupon_submit()
{
    $post_id    = !empty($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
    $coupon     = !empty($_REQUEST['coupon']) ? esc_sql($_REQUEST['coupon']) : '';
    $order_type = !empty($_REQUEST['order_type']) ? (int) $_REQUEST['order_type'] : 0;

    if (!$coupon) {
        zib_send_json_error('参数错误');
    }

    $coupon_data = zibpay_is_coupon_available($coupon, $order_type, $post_id);

    if (!empty($coupon_data['error'])) {
        zib_send_json_error($coupon_data['msg']);
    }

    $coupon_data['msg'] = '优惠码可用，请尽快使用，以免被抢用或过期';
    zib_send_json_success($coupon_data);

}
add_action('wp_ajax_coupon_submit', 'zibpay_ajax_coupon_submit');
add_action('wp_ajax_nopriv_coupon_submit', 'zibpay_ajax_coupon_submit');

//关闭订单模态框
function zibpay_ajax_close_order_modal()
{

    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;

    if (!$order_id) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    $order = zibpay::get_order($order_id, 'id,order_num,user_id,status');
    if (!$order) {
        zib_ajax_notice_modal('danger', '订单不存在');
    }

    //订单状态判断
    if ($order['status'] == '-1') {
        zib_ajax_notice_modal('info', '订单已关闭');
    }

    if ($order['status'] == '1') {
        zib_ajax_notice_modal('info', '订单已支付');
    }

    //判断用户权限
    if ($order['user_id'] != get_current_user_id() && !zib_current_user_can('order_close', $order)) {
        zib_ajax_notice_modal('danger', '权限不足');
    }

    $cancel_reason = [
        '暂时不需要了',
        '价格不合适',
        '信息填写错误',
        '支付遇到问题',
        '商家原因',
        '其他原因',
    ];

    $cancel_reason_radio = '';
    foreach ($cancel_reason as $key => $value) {
        $checked = $key === 0 ? 'checked' : '';
        $cancel_reason_radio .= '<label class="flex jsb ac padding-h6"><span style="font-weight: normal;">' . $value . '</span><input ' . $checked . ' type="radio" name="cancel_reason" value="' . $value . '"></label>';
    }

    $cancel_reason_radio = '<div class="form-radio">' . $cancel_reason_radio . '</div>';

    $header = '<div class="touch border-title flex jc c-yellow">确定取消该订单吗？</div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $con    = '<form class="zib-modal-form">';
    $con .= '<div class="mb20">';
    $con .= '<div class="mb10 muted-2-color">请选择取消原因：</div>';
    $con .= $cancel_reason_radio;
    $con .= '<div class="mt10 other-reason-input" style="display:none;">';
    $con .= '<textarea class="form-control" name="other_reason" placeholder="请填写取消原因" rows="3"></textarea>';
    $con .= '</div>';
    $con .= '</div>';
    $con .= '<input type="hidden" name="action" value="close_order">';
    $con .= '<input type="hidden" name="order_id" value="' . $order_id . '">';
    $con .= wp_nonce_field('close_order', '_wpnonce', false, false);

    $footer = '<div class="mt20 but-average">';
    $footer .= '<button class="but jb-yellow padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认取消</button>';
    $footer .= '</div></form>';

    echo $header . $con . $footer;
    exit;
}
add_action('wp_ajax_close_order_modal', 'zibpay_ajax_close_order_modal');
add_action('wp_ajax_nopriv_close_order_modal', 'zibpay_ajax_close_order_modal');

//取消订单
function zibpay_ajax_close_order()
{
    $order_id = !empty($_REQUEST['order_id']) ? (int) $_REQUEST['order_id'] : 0;

    if (!$order_id) {
        zib_send_json_error('参数错误');
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    $order = zibpay::get_order($order_id, 'id,order_num,user_id,status');
    if (!$order) {
        zib_send_json_error('订单不存在');
    }

    if ($order['status'] == 1) {
        zib_send_json_error('订单已支付，无法关闭');
    }

    //判断用户权限
    if ($order['user_id'] != get_current_user_id() && !zib_current_user_can('order_close', $order)) {
        zib_send_json_error('权限不足');
    }

    $reason = !empty($_REQUEST['cancel_reason']) ? sanitize_text_field($_REQUEST['cancel_reason']) : '其他原因';
    if ($reason === '其他原因' && !empty($_REQUEST['other_reason'])) {
        $reason = sanitize_text_field($_REQUEST['other_reason']);
    }

    //关闭订单
    zibpay::close_order($order_id, 'user', $reason);

    zib_send_json_success(['reload' => true, 'msg' => '订单已关闭']);
}
add_action('wp_ajax_close_order', 'zibpay_ajax_close_order');
add_action('wp_ajax_nopriv_close_order', 'zibpay_ajax_close_order');

function zibpay_ajax_order_pay_modal($payment_id = null)
{
    $payment_id = $payment_id ?: (!empty($_REQUEST['payment_id']) ? (int) $_REQUEST['payment_id'] : 0);

    if (empty($payment_id)) {
        zib_ajax_notice_modal('danger', '参数错误');
    }

    $payment_data = zibpay::get_payment($payment_id);
    if (empty($payment_data)) {
        zib_ajax_notice_modal('danger', '支付数据不存在');
    }

    //判断订单是否已支付
    if ($payment_data['status'] == '1') {
        zib_ajax_notice_modal('success', '订单已支付，请刷新页面');
    }

    //判断订单是否已经关闭
    if ($payment_data['status'] == '-1') {
        zib_ajax_notice_modal('danger', '订单已关闭，请刷新页面');
    }

    $html = zibpay_get_order_pay_modal_content($payment_data);
    echo $html;
    exit;
}
add_action('wp_ajax_order_pay_modal', 'zibpay_ajax_order_pay_modal');
add_action('wp_ajax_nopriv_order_pay_modal', 'zibpay_ajax_order_pay_modal');

//获取订单支付模态框内容
function zibpay_get_order_pay_modal_content(array $payment_data, $return_url = null)
{
    $payment_id = $payment_data['id'];

    //判断订单是否失效
    $time_remaining = zibpay_get_payment_pay_over_time($payment_data);
    if ($time_remaining == 'over') {
        $header = zib_get_modal_colorful_header('jb-red', '<i class="fa fa-times-circle-o fa-2x" aria-hidden="true"></i>');
        $html   = $header;
        $html .= '<div class="em12 text-center c-red" style="padding: 30px 0;">订单已关闭，请重新下单</div>';
        return $html;
    }

    //根据$payment_id获取订单数据
    $orders_data = zibpay::get_order_by_payment_id($payment_id);
    if (empty($orders_data[0])) {
        $header = zib_get_modal_colorful_header('jb-red', '<i class="fa fa-times-circle-o fa-2x" aria-hidden="true"></i>');
        $html   = $header;
        $html .= '<div class="em12 text-center c-red" style="padding: 30px 0;">订单错误，请重新下单</div>';
        return $html;
    }

    //开始构建模态框
    $user_id             = get_current_user_id();
    $html                = '';
    $lists               = '';
    $_total_price        = 0;
    $_total_pay_price    = 0;
    $_total_count        = 0;
    $_total_shipping_fee = 0;
    $_i                  = 0;
    $_expand_count       = 3;
    $shop_s              = _pz('shop_s');
    $order_type          = 1; //第一个post_id
    $first_post_id       = 0;

    foreach ($orders_data as $order) {
        $_order_data  = zibpay::get_meta($order['id'], 'order_data');
        $post_id      = $order['post_id'];
        $_order_thumb = zibpay_get_order_order_thumb($order, 'mr10');
        $_opt_name    = '';
        $order_type   = $order['order_type'];
        $_count       = $_order_data['count'] ?? 1;
        $_total_count += $_count;
        $_i++;

        if (!$first_post_id) {
            $first_post_id = $post_id;
        }

        if (isset($_order_data['prices']['pay_price'])) {
            $_unit_price = $_order_data['prices']['unit_price']; //原价的单价
            $_total_price += $_order_data['prices']['total_price'];
            $_total_pay_price += $_order_data['prices']['pay_price'];
            $_total_shipping_fee += $_order_data['prices']['shipping_fee'] ?? 0;
        } else {
            $_unit_price = $order['order_price'];
            if (!$_unit_price && $order_type !== '10' && $_count == 1) {
                $_unit_price = zibpay_get_order_effective_amount($order);
            }
            $_total_price += $_unit_price;
            $_total_pay_price += $_unit_price;
        }

        $_title    = zibpay_get_order_title($order, 'text-ellipsis mb6');
        $_opt_name = $_opt_name ? '<div class="muted-color em09 text-ellipsis">' . $_opt_name . '</div>' : '';

        $lists .= '
            <div class="show-order-modal flex mb10 order-item border-bottom ac padding-h10' . ($_expand_count <= $_i ? ' hide' : '') . '" data-order-id="' . $order['id'] . '">
                ' . $_order_thumb . '
                <div class="flex1 flex jsb xx">
                    <div class="flex1 flex jsb">
                        <div class="flex1 mr20">
                            ' . $_title . $_opt_name . '
                            <div class="muted-color em09 mt6 text-ellipsis">' . $order['order_num'] . '</div>
                        </div>
                        <div class="flex xx ab">
                            <div class="unit-price">' . $_unit_price . '</div>
                            ' . ($_count ? '<div class="count mt6 muted-color">x' . $_count . '</div>' : '') . '
                        </div>
                    </div>
                </div>
                <i class="fa fa-angle-right em12 ml10 muted-2-color"></i>
            </div>
            ';
    }

    if ($_i >= $_expand_count) {
        $text = '查看其余' . ($_i - $_expand_count + 1) . '笔订单信息';
        $lists .= '<div class="pointer expand-toggle text-center mb10" closest-selector=".paid-modal-lists-box" expand-count="' . $_expand_count . '" expand-text="' . $text . '" collapse-text="收起更多订单信息" >
                <div class="muted-2-color em09"><span class="btn-text">' . $text . '</span><i class="ml6 fa fa-angle-down"></i></div>
            </div>';
    }

    $is_points = $payment_data['method'] === 'points';
    $pay_mark  = $is_points ? zibpay_get_points_mark() : zibpay_get_pay_mark();

    $total_html = '<div class="order-info-box muted-box mb10">
            <div class="order-info-body">
                <div class="order-info-item flex at jsb mb10">
                    <div class="item-label muted-color">
                        支付单号
                    </div>
                    <div class="item-value">' . $payment_data['order_num'] . '</div>
                </div>
                <div class="order-info-item flex at jsb mb10">
                    <div class="item-label muted-color">
                        总价
                        <span class="muted-3-color px12">共' . $_total_count . '件</span>
                    </div>
                    <div class="item-value">
                        <div class="order-pay-prices-box flex abl xx">
                            <div class="order-pay-prices-item flex abl">
                                <span class="pay-mark px12">' . $pay_mark . '</span>
                                <span class="price-str">' . $_total_price . '</span>
                            </div>
                        </div>
                    </div>
                </div>
                ' . ($_total_shipping_fee ? '
                <div class="order-info-item flex ac jsb mb10">
                    <div class="item-label muted-color">运费</div>
                    <div class="item-value flex abl">
                        <span class="pay-mark px12">' . $pay_mark . '</span>
                        <span class="price-str">' . zib_floatval_round($_total_shipping_fee) . '</span>
                    </div>
                </div>
                ' : '') . (
        $_total_price - $_total_pay_price > 0 ? '
                <div class="order-info-item flex ac jsb mb10">
                    <div class="item-label muted-color">优惠</div>
                    <div class="item-value">
                        <div class="order-pay-prices-box flex abl xx">
                            <div class="order-pay-prices-item flex abl c-red">
                                <span class="mr3">-</span>
                                <span class="pay-mark px12">' . $pay_mark . '</span>
                                <span class="price-str">' . zib_floatval_round($_total_price - $_total_pay_price) . '</span>
                            </div>
                        </div>
                    </div>
                </div>' : '') . '
                <div class="order-info-item flex ac jsb">
                    <div class="item-label muted-color">合计</div>
                    <div class="item-value">
                        <div class="order-pay-prices-box flex abl xx">
                            <div class="order-pay-prices-item flex abl c-red em12">
                                <span class="pay-mark px12">' . $pay_mark . '</span>
                                <span class="price-str">' . zib_floatval_round($_total_pay_price) . '</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

    if (!$return_url) {
        if ($user_id) {
            $return_url = zib_get_user_center_url('orders');
        } elseif ($first_post_id) {
            //获取文章的链接
            $return_url = get_permalink($first_post_id);
        } else {
            $return_url = home_url();
        }
    }

    $form = '';
    if ($is_points) {
        $user_points = zibpay_get_user_points($user_id);

        $form .= '<div class="mb10 muted-box">';
        $form .= '<div class="flex jsb ab"><span class="muted-2-color">' . zib_get_svg('points-color', null, 'em12 mr6') . '我的积分</span><div><span class="c-green">' . $pay_mark . '<span class="em14">' . $user_points . '</span></span></div></div>';
        $form .= '</div>';

        $form = '<form>';
        $form .= $return_url ? '<input type="hidden" name="return_url" value="' . $return_url . '">' : '';
        $form .= '<input type="hidden" name="payment_id" value="' . $payment_id . '">';
        $form .= '<button class="but jb-yellow padding-lg btn-block radius initiate-pay mt10" >立即支付<span class="ml6 px12">' . zibpay_get_points_mark() . '</span>' . $_total_pay_price . '</button>';
        $form .= '</form>';

        //如果积分不足
        if ($_total_pay_price > $user_points) {
            $points_pay_link = zibpay_get_points_pay_link('but c-green padding-lg', '购买积分');
            $points_user_url = zib_get_user_center_url('balance');

            $form = '';
            $form .= '<div class="badg c-red btn-block mb20">抱歉，您的积分不足，暂时无法支付</div>';
            $form .= '<div class="modal-buts but-average"><a rel="nofollow" type="button" class="but padding-lg" href="' . $points_user_url . '">我的积分</a>' . $points_pay_link . '</div>';
        }

    } else {
        $form = '<form class="mt10">';
        $form .= '<input type="hidden" name="payment_modal" value="true">';
        $form .= '<input type="hidden" name="payment_id" value="' . $payment_id . '">';
        $form .= $return_url ? '<input type="hidden" name="return_url" value="' . $return_url . '">' : '';
        $form .= zibpay_get_initiate_pay_input($order_type, $_total_pay_price, $post_id, true);
        $form .= '</form>';
    }

    if ($time_remaining) {
        $time_remaining = zib_time_to_c($time_remaining);
        $time_remaining = '<span class="ml6 c-yellow px12 badg badg-sm" int-second="1" data-over-text="交易已关闭" data-countdown="' . $time_remaining . '"></span>';
    }

    $header = '<div class="touch border-title flex jc font-bold">立即支付' . $time_remaining . '</div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $html   = $header . '<div class="mini-scrollbar scroll-y paid-modal-content"><div class="paid-modal-lists-box">' . $lists . '</div>' . $total_html . '</div>' . $form;

    return $html;
}
