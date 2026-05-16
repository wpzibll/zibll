<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-03-17 17:17:37
 * @LastEditTime : 2026-05-05 18:18:11
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|支付功能：用户积分系统
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户积分
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_points($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    $points = get_user_meta($user_id, 'points', true);

    return (int) $points;
}

/**
 * @description: 获取购买积分的模态框的按钮
 * @param {*} $class
 * @param {*} $con
 * @return {*}
 */
function zibpay_get_points_pay_link($class = '', $con = '购买积分')
{
    $user_id = get_current_user_id();
    if (!$user_id || !_pz('points_s') || !_pz('points_pay_s')) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => 'points-pay-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'points_pay_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取货币符号
 * @param {*}
 * @return {*}
 */
function zibpay_get_points_mark($class = 'icon')
{
    //声明静态变量，加速获取
    static $pay_mark = null;
    if (!$pay_mark) {
        $pay_mark = zib_get_svg('points', null, $class);
    }

    return $pay_mark;
}

/**
 * @description: 用户积分变动统一接口
 * @param {*} $user_id
 * @param {*} $data
 * @return {*}
 */
function zibpay_update_user_points($user_id, $data)
{
    $defaults = array(
        'order_num' => '', //订单号
        'value'     => 0, //值 整数为加，负数为减去
        'type'      => '', //类型说明
        'desc'      => '', //说明
        'time'      => current_time('Y-m-d H:i'),
    );
    $data          = wp_parse_args($data, $defaults);
    $data['value'] = (int) $data['value']; //只允许整数
    if (!$user_id || $data['value'] === 0) {
        return;
    }

    $old_value = 0;
    if (!zibpay_user_balance_or_points_save_db('points', $user_id, $data['value'], $old_value)) {
        return false;
    }

    $data['points'] = $old_value + $data['value']; //记录当前

    //最小为0
    if ($data['points'] < 0) {
        $data['points'] = 0;
    }

    $record = zib_get_user_meta($user_id, 'points_record', true);
    if (!$record || !is_array($record)) {
        $record = array();
    }

    $max        = 50; //最多保存多少条记录
    $record     = array_slice($record, 0, $max - 1, true); //数据切割，删除多余的记录
    $new_record = array_merge(array($data), $record);

    zib_update_user_meta($user_id, 'points_record', $new_record);
    return true;
}

/**
 * @description: 获取订单支付了多少积分
 * @param {*} $order
 * @return {*}
 */
function zibpay_get_order_pay_points($order)
{
    $order = (array) $order;
    if ($order['pay_type'] === 'points') {
        $pay_detail = maybe_unserialize($order['pay_detail']);
        $price      = isset($pay_detail['points']) ? $pay_detail['points'] : 0;
        return $price;
    }
    return 0;
}

/**
 * @description: 支付成功后，对积分变动的相关处理
 * @param {*} $pay_order
 * @return {*}
 */
function zibpay_payment_order_points($pay_order)
{
    $order_type = $pay_order->order_type;
    if ($order_type == 9 && _pz('points_pay_s')) {
        //如果是余额充值
        $product_id = $pay_order->product_id;
        if (!$product_id) {
            $pay_points = (int) ($pay_order->order_price * _pz('pay_points_rate'));
        } else {
            if (strstr($product_id, 'points_')) {
                //购买积分
                $product    = _pz('pay_points_product');
                $product_id = str_replace('points_', '', $product_id);
                $pay_points = $product[$product_id]['points'];
            } elseif (strstr($product_id, 'exchange_')) {
                //卡密兑换
                $card_db    = ZibCardPass::get_row(array('order_num' => $pay_order->pay_num, 'type' => 'points_exchange'));
                $pay_points = zibpay_get_pass_exchange_points($card_db);
            }
        }

        $data = array(
            'order_num' => $pay_order->order_num, //订单号
            'value'     => $pay_points, //值 整数为加，负数为减去
            'type'      => '购买积分',
            'desc'      => '', //说明
        );
        zibpay_update_user_points($pay_order->user_id, $data);
    }
}
if (_pz('points_s')) {
    add_action('payment_order_success', 'zibpay_payment_order_points', 7); //支付成功后更新数据
}

/**
 * @description: 获取购买积分金额限制
 * @param {*}
 * @return {*}
 */
function zibpay_get_pay_points_product_custom_limit()
{
    $option = _pz('pay_points_product_custom_limit', array('min' => 10, 'max' => 500));

    return array(
        'min' => (int) ($option['min']),
        'max' => (int) ($option['max']),
    );
}

/**
 * @description: 用户购买积分的模态框内容
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_points_pay_modal($user_id)
{

    $desc              = _pz('pay_points_desc');
    $desc              = $desc ? '<div class="muted-box muted-2-color padding-10 mb20 em09">' . $desc . '</div>' : '';
    $product           = _pz('pay_points_product');
    $custom_s          = _pz('pay_points_product_custom_s', true);
    $custom_limit      = zibpay_get_pay_points_product_custom_limit();
    $icon              = zib_get_svg('points');
    $default_pay_price = 0;
    $custom_limit_html = !empty($custom_limit['min']) ? '最低购买' . $custom_limit['min'] . '积分' : '';
    $custom_limit_html .= $custom_limit_html ? '，' : '';
    $custom_limit_html .= !empty($custom_limit['max']) ? '最高购买' . $custom_limit['max'] . '积分' : '';
    $custom_product = '<div class="" data-for="product" data-value="custom">
    <div class="relative flex ab">
        <span class="ml6 mr10 muted-color">' . $icon . '</span>
        <input class="line-form-input em16 key-color" style="padding: 1px;" name="custom" type="number" ' . (!empty($custom_limit['min']) ? ' limit-min="' . $custom_limit['min'] . '"' : '') . (!empty($custom_limit['max']) ? ' limit-max="' . $custom_limit['max'] . '"' : '') . ' warning-max="最高可购买1$积分" warning-min="最低需购买1$积分">
        <i class="line-form-line"></i>
    </div>
    <div class="muted-2-color em09 mt6">' . $custom_limit_html . '</div></div>';
    $header = '<div class="mb10 touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="modal-title flex ac"><span class="mr6 em14">' . zib_get_svg('points-color') . '</span>购买积分</b></div>';

    $product_html = '';
    $product_id   = 0;
    if ($product && is_array($product)) {
        foreach ($product as $product_i => $product_v) {
            $points    = $product_v['points'];
            $pay_price = $product_v['pay_price'];
            if ($product_i === 0) {
                $default_pay_price = $pay_price;
            }

            $vip_tag = $product_v['tag'];
            $vip_tag = $vip_tag ? '<div class="abs-right vip-tag badg ' . (!empty($product_v['tag_class']) ? $product_v['tag_class'] : 'jb-yellow') . '">' . $vip_tag . '</div>' : '';

            $product_html .= '<div class="zib-widget vip-product relative product-box' . ($product_i === 0 ? ' active' : '') . '"  data-for="product" data-value="' . $product_i . '">' . $vip_tag . '
        <div class="em14"><span class="px12">' . $icon . '</span>' . $points . '</div>
        <div class="c-red"><span class="px12">' . zibpay_get_pay_mark() . '</span><span class="em12">' . $pay_price . '</span></div>
        </div>';
        }
    }

    if ($product_html) {
        $product_html = '<div class="muted-color mb6">请选择需购买的积分</div>' . $product_html;
        if ($custom_s) {
            $product_html .= '<div class="muted-color mt20 mb6">自定义积分数量（1元=' . _pz('pay_points_rate') . '积分）</div>' . $custom_product;
        }
    }

    if (!$product_html) {
        $product_id   = 'custom';
        $product_html = '<div class="muted-color mb6">请输入需购买的积分数额</div>' . $custom_product;
    }

    //卡密支付
    if (_pz('points_pass_exchange_s')) {
        add_filter('zibpay_is_allow_card_pass_pay', '__return_true'); //添加卡密充值
        add_filter('zibpay_card_pass_payment_desc', function () {
            $password_desc = _pz('points_pass_exchange_desc');
            return $password_desc ? '<div class="muted-box muted-2-color padding-10 mb10 em09">' . $password_desc . '</div>' : '';
        });
        $payment_methods = zibpay_get_payment_methods(9);
        if (count($payment_methods) <= 1) {
            $product_html = '';
        }
    }

    $charge_html = $product_html ? '<div class="charge-box mb20">' . $product_html . '</div>' : '';
    $charge_html .= $desc;

    $hidden = '<input type="hidden" name="product" value="' . $product_id . '">';
    $hidden .= '<input type="hidden" name="order_type" value="9">';

    $pay_button = zibpay_get_initiate_pay_input(9);

    $form = '<form class="balance-charge-form mini-scrollbar scroll-y max-vh7">' . $charge_html . $hidden . $pay_button . '</form>';

    $html = '';
    $html .= $header . $form;

    return $html;
}

/**
 * @description: 获取用户积分变动记录
 * @param {*} $user_id
 * @return {*}
 */
function zibpay_get_user_points_record_lists($user_id = 0)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $record = (array) zib_get_user_meta($user_id, 'points_record', true);
    $lists  = '';

    foreach ($record as $k => $v) {
        if (isset($v['value']) && isset($v['points'])) {

            $_class = $v['value'] < 0 ? 'c-red' : 'c-blue';
            $badge  = '<span class="badg badg-sm mr6 ' . $_class . '">' . $v['type'] . '</span>';
            $lists .= '<div class="border-bottom padding-h10 flex jsb">';
            $lists .= '<div class="muted-2-color">';
            $lists .= '<div class="mb6">' . $badge . $v['desc'] . '</div>';
            $lists .= $v['order_num'] ? '<div class="em09">订单号：' . $v['order_num'] . '</div>' : '';
            $lists .= $v['time'] ? '<div class="em09">时间：' . $v['time'] . '</div>' : '';
            $lists .= '</div>';
            $lists .= '<div class="flex jsb xx text-right flex0 ml10 ab"><b class="em12 ' . $_class . '">' . ($v['value'] < 0 ? $v['value'] : '+' . $v['value']) . '</b><div class="em09 muted-2-color">积分：' . $v['points'] . '</div></div>';
            $lists .= '</div>';
        }
    }

    if (!$lists) {
        $lists = zib_get_null('暂无积分记录', 42, 'null-order.svg');
    } else {
        if (count($record) > 49) {
            $lists .= '<div class="text-center mt20 muted-3-color">最多显示近50条记录</div>';
        }
    }
    return $lists;
}

//通过任务免费获取积分--------------

/**
 * @description: 获取获得免费积分的方法明细
 * @param {*}
 * @return {*}
 */
function zib_get_points_free_lists($user_id)
{
    $opt   = _pz('points_free_opt');
    $lists = '';

    $to_day = zibpay_get_user_today_free_points($user_id);

    if ($to_day) {
        $day_max  = _pz('points_free_opt', 100, 'day_max');
        $max_desc = $day_max > $to_day ? '每日可免费获取' . $day_max . '积分，超过后将不再获取' : '今日获取的免费积分已超过' . $day_max . '，将不再获取';
        $lists .= '<div class="border-bottom padding-h10"><div class="flex jsb ac"><div class="flex1 mr20"><div class="font-bold mb6">今日累计 <span class="focus-color">+' . $to_day . '</span></div><div class="muted-2-color em09">' . $max_desc . '</div></div><a class="muted-2-color shrink0" data-toggle="tab" href="#tab_points_date">每日详情<i class="fa fa-angle-right ml6 em12"></i></a></div></div>';
    }

    foreach (zib_get_user_integral_add_options() as $k => $v) {
        if ((int) $opt[$k] > 0 && 'sign_up' !== $k) {
            $lists .= '<div class="border-bottom padding-h10"><div class="flex jsb ac"><div class="flex1 mr20"><div class="font-bold mb6">' . $v[0] . '</div><div class="muted-2-color em09">' . $v[2] . '</div></div><span class="focus-color em14 shrink0"> ' . zib_get_svg('points-color', null, 'icon mr6 em09') . ' + ' . (int) $opt[$k] . '</span></div></div>';
        }
    }

    return $lists;
}

//获取我的免费积分获取每日记录明细
function zib_get_user_free_points_date_detail_lists($user_id)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $detail = (array) zib_get_user_meta($user_id, 'free_points_detail', true);
    $lists  = '';
    foreach ($detail as $k => $v) {
        $lists .= '<div class="border-bottom padding-h6 flex jsb ac"><div class="em12">' . $k . '</div><div class="text-right shrink0 focus-color em14 font-bold">+ ' . $v . '</div></div>';
    }
    if (!$lists) {
        $lists = zib_get_null('暂无经验值获取明细');
    } elseif (count($detail) > 49) {
        $lists .= '<div class="text-center mt20 muted-3-color">最多显示近50条记录</div>';
    }
    return $lists;
}

//获取用户当天的免费积分总额
function zibpay_get_user_today_free_points($user_id)
{
    if (!$user_id) {
        return;
    }
    $current_date = current_time('Y-m-d');
    $detail       = zib_get_user_meta($user_id, 'free_points_detail', true);
    if (!$detail || !is_array($detail)) {
        $detail = array();
    }

    return isset($detail[$current_date]) ? $detail[$current_date] : 0;
}

/**
 * @description: 判断用户今日是否还可以获取免费积分
 * @param {*}
 * @return {*}
 */
function zibpay_user_is_allow_add_free_points($user_id)
{
    //判断是否超过今日上限
    $to_day  = zibpay_get_user_today_free_points($user_id);
    $day_max = _pz('points_free_opt', 100, 'day_max');
    if ($day_max && $to_day >= $day_max) {
        return false;
    }

    return true;
}

/**
 * @description: 免费获取积分的添加统一接口
 * @param {*} $user_id
 * @param {*} $value
 * @param {*} $key
 * @return {*}
 */
function zibpay_add_user_free_points($user_id = 0, $value = 0, $key = '')
{

    //判断用户还能增加经验值，允许。||是否超过今日上限
    if (!$user_id || !$value || !zibpay_user_is_allow_add_free_points($user_id)) {
        return;
    }

    //禁封判断
    if (_pz('user_ban_s', true) && zib_user_is_ban($user_id)) {
        return;
    }

    $data = array(
        'value' => $value, //值 整数为加，负数为减去
        'type'  => zib_get_user_points_add_options($key)[0], //类型说明
    );

    zibpay_update_user_points($user_id, $data);
    //记录每天明细
    zibpay_add_user_free_points_date_detail($user_id, $value);
}

/**
 * @description: 获取免费积分详情
 * @param {*} $key
 * @return {*}
 */
function zib_get_user_points_add_options($key)
{
    static $_options = null;
    if ($_options === null) {
        $_options            = zib_get_user_integral_add_options();
        $_options['checkin'] = array('签到奖励', 0, '', '');
    }

    if (isset($_options[$key])) {
        return $_options[$key];
    }
    return array('', 0, '', '');
}

/**
 * @description: 获取卡密的兑换的金额
 * @param {*} $db
 * @return {*}
 */
function zibpay_get_pass_exchange_points($db)
{
    $db = (array) $db;
    if (empty($db['meta'])) {
        return 0;
    }
    $meta = maybe_unserialize($db['meta']);

    return isset($meta['points']) ? (int) $meta['points'] : 0;
}

/**
 * @description: 记录用户每天获取的免费积分明细
 * @param {*} $user_id
 * @param {*} $value
 * @return {*}
 */
function zibpay_add_user_free_points_date_detail($user_id, $value)
{
    if (!$user_id || !$value) {
        return;
    }

    $current_date = current_time('Y-m-d');
    $detail       = zib_get_user_meta($user_id, 'free_points_detail', true);
    if (!$detail || !is_array($detail)) {
        $detail = array();
    }

    $max    = 50; //最多保存多少条记录
    $detail = array_slice($detail, 0, $max - 1, true); //数据切割，删除多余的记录

    if (isset($detail[$current_date])) {
        $detail[$current_date] += $value;
    } else {
        $detail = array_merge(array($current_date => $value), $detail);
    }

    zib_update_user_meta($user_id, 'free_points_detail', $detail);
}

//积分免费获取
if (_pz('points_s', true)) {
    new zibpay_points_free_add();
}

//开始挂钩添加用户等级的经验值
class zibpay_points_free_add
{
    public function __construct()
    {
        add_action('user_checkined', array($this, 'user_checkined'), 10, 2); //签到

        add_action('user_register', array($this, 'sign_up'));
        add_action('admin_init', array($this, 'sign_in'));
        add_action('save_post', array($this, 'post_new'));
        add_action('like-posts', array($this, 'post_like'), 20, 3);
        add_action('favorite-posts', array($this, 'post_favorite'), 20, 3);

        add_action('comment_post', array($this, 'comment_new'));
        add_action('comment_unapproved_to_approved', array($this, 'comment_new'));
        add_action('like-comment', array($this, 'comment_like'), 20, 3);
        add_action('follow-user', array($this, 'followed'), 20, 2);

        add_action('bbs_score_extra', array($this, 'bbs_score_extra'), 20, 2); //帖子被加分
        add_action('bbs_posts_essence_set', array($this, 'bbs_essence'), 20, 2); //帖子成为精华
        add_action('posts_is_hot', array($this, 'bbs_posts_hot')); //热门帖子
        add_action('plate_is_hot', array($this, 'bbs_plate_hot')); //热门版块
        add_action('comment_is_hot', array($this, 'bbs_comment_hot')); //热门评论
        add_action('answer_adopted', array($this, 'bbs_adopt')); //回答被采纳
    }

    //签到
    public function user_checkined($user_id, $the_data)
    {
        if (!$user_id || !$the_data['points']) {
            return;
        }

        zibpay_add_user_free_points($user_id, $the_data['points'], 'checkin');
    }

    //注册
    public function sign_up($user_id)
    {
        if (!$user_id) {
            return;
        }

        $value = _pz('points_free_opt', 0, 'sign_up');
        if ($value) {
            zibpay_add_user_free_points($user_id, $value, 'sign_up');
        }
    }

    //登录
    public function sign_in()
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $value = _pz('points_free_opt', 0, 'sign_in');

        if ($value) {
            //每天仅一次
            $_time        = zib_get_user_meta($user_id, '_signin_points_time', true);
            $current_time = current_time('Ymd');
            if ($_time >= $current_time) {
                return;
            }

            zib_update_user_meta($user_id, '_signin_points_time', $current_time);
            zibpay_add_user_free_points($user_id, $value, 'sign_in');
        }
    }

    //发布文章
    public function post_new($post_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $post_type = $post->post_type;
        if (in_array($post_type, array('forum_post', 'plate', 'post')) && 'publish' == $post->post_status) {
            $user_id = $post->post_author;
            if (!$user_id || zib_get_post_meta($post->ID, '_user_points_new', true)) {
                return;
            }

            $value = _pz('points_free_opt', 0, 'post_new');
            $key   = 'post_new';
            if ('forum_post' == $post_type) {
                $value = _pz('points_free_opt', 0, 'bbs_posts_new');
                $key   = 'bbs_posts_new';
            }
            if ('plate' == $post_type) {
                $value = _pz('points_free_opt', 0, 'bbs_plate_new');
                $key   = 'bbs_plate_new';
            }
            if ($value) {
                zib_update_post_meta($post->ID, '_user_points_new', true);
                zibpay_add_user_free_points($user_id, $value, $key);
            }
        }
    }

    //文章点赞
    public function post_like($post_id, $count, $action_user_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $user_id = $post->post_author;
        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        //一篇文章最多5次点赞加经验值
        $_this_add = (int) zib_get_post_meta($post->ID, '_user_points_like', true);

        if (!$user_id || $_this_add >= 5) {
            return;
        }

        $value = _pz('points_free_opt', 0, 'post_like');
        $key   = 'post_like';

        if ($value) {
            zib_update_post_meta($post->ID, '_user_points_like', $_this_add + 1);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //文章被收藏
    public function post_favorite($post_id, $count, $action_user_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $user_id = $post->post_author;
        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        //一篇文章最多5次收藏加经验值
        $_this_add = (int) zib_get_post_meta($post->ID, '_user_points_favorite', true);

        if (!$user_id || $_this_add >= 5) {
            return;
        }

        $key   = 'post_favorite';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_points_favorite', $_this_add + 1);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //发布评论
    public function comment_new($comment)
    {

        $comment = get_comment($comment);

        if (empty($comment->user_id) || $comment->comment_approved != '1') {
            return;
        }

        $user_id = $comment->user_id;
        if (!$user_id || zib_get_comment_meta($comment->comment_ID, '_user_points_new', true)) {
            return;
        }

        $key   = 'comment_new';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_points_new', true);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //评论获赞
    public function comment_like($comment_id, $count, $action_user_id)
    {

        $comment = get_comment($comment_id);
        if (empty($comment->user_id)) {
            return;
        }

        $user_id = $comment->user_id;

        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        $_this_add = (int) zib_get_comment_meta($comment->comment_ID, '_user_points_like', true);
        if (!$user_id || $_this_add >= 2) {
            return;
        }

        $key   = 'comment_like';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_points_like', $_this_add + 1);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //被关注
    public function followed($follow_user_id, $followed_user_id)
    {

        $user_id = $followed_user_id;

        if (!$user_id) {
            return;
        }

        $_user_points_followed = zib_get_user_meta($user_id, '_user_points_followed');
        if ($_user_points_followed && is_array($_user_points_followed) && in_array($follow_user_id, $_user_points_followed)) {
            return;
        }

        $key   = 'followed';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            if (is_array($_user_points_followed)) {
                $_user_points_followed[] = $follow_user_id;
            } else {
                $_user_points_followed = array($follow_user_id);
            }

            zib_update_user_meta($user_id, '_user_points_followed', $_user_points_followed);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //帖子被加分
    public function bbs_score_extra($post_id, $action_user_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $user_id = $post->post_author;

        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        $_this_add = (int) zib_get_post_meta($post->ID, '_user_points_score_extra', true);
        if (!$user_id || $_this_add >= 5) {
            return;
        }

        $key   = 'bbs_score_extra';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_points_score_extra', $_this_add + 1);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //帖子精华
    public function bbs_essence($post_id, $val)
    {
        $post = get_post($post_id);
        if (empty($post->ID) || !$val) {
            return;
        }

        $user_id   = $post->post_author;
        $_this_add = zib_get_post_meta($post->ID, '_user_points_essence', true);
        if (!$user_id || $_this_add) {
            return;
        }

        $key   = 'bbs_essence';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_points_essence', true);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //版块成为热门
    public function bbs_plate_hot($post)
    {

        if (!isset($post->post_author)) {
            return;
        }
        $_this_add = zib_get_post_meta($post->ID, '_user_points_hot', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_plate_hot';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_points_hot', true);
            zibpay_add_user_free_points($post->post_author, $value, $key);
        }
    }

    //帖子成为热门
    public function bbs_posts_hot($post)
    {

        if (!isset($post->post_author)) {
            return;
        }

        $_this_add = zib_get_post_meta($post->ID, '_user_points_hot', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_posts_hot';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_points_hot', true);
            zibpay_add_user_free_points($post->post_author, $value, $key);
        }
    }

    //评论成为热门
    public function bbs_comment_hot($comment)
    {
        $user_id = $comment->user_id;
        if (!$user_id) {
            return;
        }

        $_this_add = zib_get_comment_meta($comment->comment_ID, '_user_points_hot', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_comment_hot';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_points_hot', true);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //回答被采纳
    public function bbs_adopt($comment)
    {

        $user_id = $comment->user_id;
        if (!$user_id) {
            return;
        }

        $_this_add = zib_get_comment_meta($comment->comment_ID, '_user_points_adopt', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_adopt';
        $value = _pz('points_free_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_points_adopt', true);
            zibpay_add_user_free_points($user_id, $value, $key);
        }
    }

    //over
}
