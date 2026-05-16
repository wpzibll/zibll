<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-03-25 13:52:02
 * @LastEditTime: 2024-06-19 13:21:37
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户签到checkin相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 获取用户签到应该奖励金额
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_checkin_should_reward($user_id = 0)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return array(
            'points'   => 0,
            'integral' => 0,
        );
    }

    $reward = array(
        'points'   => (int) _pz('checkin_reward_points', 0),
        'integral' => (int) _pz('checkin_reward_integral', 0),
    );

    //获取连续签到时间+1天为今天是第几天
    $reward_continuous_day = zib_get_user_checkin_reward_continuous_day($user_id) + 1;
    $reward_continuous_day = $reward_continuous_day <= zib_get_user_checkin_reward_continuous_max_day() ? $reward_continuous_day : 1;
    if ($reward_continuous_day > 1) {
        $reward = array(
            'points'   => (int) _pz('continuous_checkin_reward', 0, 'points_' . $reward_continuous_day),
            'integral' => (int) _pz('continuous_checkin_reward', 0, 'integral_' . $reward_continuous_day),
        );
    }

    $reward['continuous_day'] = $reward_continuous_day;
    return $reward;
}

/**
 * @description: 判断用户是否已经签到
 * @param {*} $user_id
 * @return {*}
 */
function zib_user_is_checkined($user_id = 0)
{
    return zib_get_user_the_checkin_details($user_id);
}

/**
 * @description: 获取用户最后签到时间
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_last_checkin_time($user_id = 0, $ago = false)
{
    $detail = zib_get_user_meta($user_id, 'checkin_detail', true);

    $last = is_array($detail) ? reset($detail) : array();

    $time = !empty($last['time']) ? $last['time'] : '';

    if ($ago && $time) {
        return zib_get_time_ago($time);
    }
    return $time;
}

/**
 * @description: 获取今日的签单详情
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_the_checkin_details($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $current_time = current_time('Y-m-d');
    $detail       = zib_get_user_meta($user_id, 'checkin_detail', true);

    return isset($detail[$current_time]) ? $detail[$current_time] : false;
}

/**
 * @description: 获取用户累计签到天数
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_checkin_all_day($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }
    return (int) get_user_meta($user_id, 'checkin_all_day', true);
}

/**
 * @description: 获取用户连续签到天数
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_checkin_continuous_day($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    return (int) get_user_meta($user_id, 'checkin_continuous_day', true);
}

/**
 * @description: 获取奖励用户连续签到天数（最大7天的连续奖励天数）
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_checkin_reward_continuous_day($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return 0;
    }

    $days = zib_get_user_meta($user_id, 'checkin_reward_days', true);
    $max  = zib_get_user_checkin_reward_continuous_max_day();
    if (!$days || !is_array($days) || count($days) > $max) {
        return 0;
    }

    $current_date = current_time('Y-m-d'); //今天
    $yesterday    = date('Y-m-d', strtotime('-1 day', strtotime($current_date))); //昨天

    if (array_search($yesterday, $days) === false) {
        //没有昨天 则未连续
        return 0;
    }

    return count($days);
}

/**
 * @description: 执行用户签到统一接口
 * @param {*} $user_id
 * @return {*}
 */
function zib_user_checkin($user_id)
{
    //未开启此功能或者今日已经签到
    if (!_pz('checkin_s') || zib_user_is_checkined($user_id)) {
        return;
    }

    //小黑屋禁封判断
    if (_pz('user_ban_s', true) && zib_user_is_ban($user_id)) {
        return;
    }

    $reward = zib_get_user_checkin_should_reward($user_id); //奖励参数

    //保存签到详情
    $detail   = zib_get_user_meta($user_id, 'checkin_detail', true);
    $the_data = array(
        'integral' => $reward['integral'],
        'points'   => $reward['points'],
        'time'     => current_time('Y-m-d H:i:s'),
    );
    if (!$detail || !is_array($detail)) {
        $detail = array();
    }

    $max        = apply_filters('user_checkin_detail_maximum', 30); //最多保存多少条记录
    $detail     = array_slice($detail, 0, $max - 1, true); //数据切割，删除多余的记录
    $new_detail = array(current_time('Y-m-d') => $the_data);
    $new_detail = array_merge($new_detail, $detail);

    //保存签到详情
    if (zib_update_user_meta($user_id, 'checkin_detail', $new_detail)) {

        //保存累计签到天数
        update_user_meta($user_id, 'checkin_all_day', (zib_get_user_checkin_all_day($user_id) + 1));

        //保存连续签到天数
        zib_update_checkin_continuous_day($user_id, $new_detail);

        //保存奖励记录
        zib_update_checkin_reward_day($user_id);

        do_action('user_checkined', $user_id, $the_data);

        return $the_data;
    }
}

/**
 * @description: 更新用户的连续签到天数
 * @param {*} $user_id
 * @return {*}
 */
function zib_update_checkin_continuous_day($user_id, $detail = null)
{

    if ($detail === null) {
        $detail = zib_get_user_meta($user_id, 'checkin_detail', true);
        if (!$detail || !is_array($detail)) {
            $detail = array();
        }
    }

    $current_date   = current_time('Y-m-d');
    $yesterday_date = date('Y-m-d', strtotime('-1 day', strtotime($current_date))); //昨天
    //判断昨天是否签到
    if (isset($detail[$yesterday_date])) {
        //昨天签到了，则连续签到天数+1
        $new_day = zib_get_user_checkin_continuous_day($user_id) + 1;
    } else {
        //昨天没有签到，则连续签到天数重置为1
        $new_day = 1;
    }

    update_user_meta($user_id, 'checkin_continuous_day', $new_day); //保存连续签到天数
}

/**
 * @description: 最大连续几天签到有奖励
 * @param {*}
 * @return {*}
 */
function zib_get_user_checkin_reward_continuous_max_day()
{
    return 7;
}

/**
 * @description: 更新用户连续奖励的天数数据
 * @param {*} $user_id
 * @return {*}
 */
function zib_update_checkin_reward_day($user_id)
{

    $max          = zib_get_user_checkin_reward_continuous_max_day();
    $detail       = zib_get_user_meta($user_id, 'checkin_reward_days', true);
    $current_date = current_time('Y-m-d'); //今天
    if (!$detail || !is_array($detail) || count($detail) >= $max) {
        //没有数据或者已经达到最大天数，都进行重置
        //重置
        $detail = array();
    } else {
        $yesterday = date('Y-m-d', strtotime('-1 day', strtotime($current_date))); //昨天
        if (array_search($yesterday, $detail) === false) {
            //没有昨天 则未连续
            $detail = array();
        }
    }
    $detail[] = $current_date;

    zib_update_user_meta($user_id, 'checkin_reward_days', $detail); //保存连续签到奖励的明细
}

/**
 * @description: 获取签到按钮 发起签到
 * @param {*} $class
 * @param {*} $text
 * @param {*} $ed_text
 * @return {*}
 */
function zib_get_user_checkin_btn($class = '', $text = '', $ed_text = '')
{
    if (!_pz('checkin_s')) {
        return;
    }

    $user_id = get_current_user_id();

    if (!$user_id) { //未登录
        $class .= ' signin-loader';
    } else {

        if (zib_user_is_checkined($user_id)) { //今日已签到，则显示签到详情按钮
            return zib_get_user_checkin_details_link($class, $ed_text);
        }

        $class .= ' initiate-checkin';
    }

    return '<a class="' . $class . '" href="javascript:;" form-action="user_checkin"  ed-text="' . esc_attr($ed_text) . '">' . $text . '</a>';
}

/**
 * @description: 获取签到详情的按钮链接
 * @param {*} $class
 * @param {*} $text
 * @return {*}
 */
function zib_get_user_checkin_details_link($class = '', $text = '')
{
    if (!_pz('checkin_s')) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) { //未登录
        return '<a href="javascript:;" class="signin-loader ' . $class . '"><text>' . $text . '</a>';
    }

    $class .= ' checkin-details-link';

    $url_var = array(
        'action' => 'checkin_details_modal',
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取签到详情的模态框
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_checkin_details_modal($user_id)
{

    if (!$user_id) { //未登录
        return;
    }

    //用户卡片
    $user         = get_userdata($user_id);
    $user_card    = '';
    $points_s     = _pz('points_s');
    $user_level_s = _pz('user_level_s');
    if ($points_s) {
        $user_points = zibpay_get_user_points($user_id);
        $user_card .= '<a rel="nofollow" class="block flex1 muted-box padding-10" href="' . zib_get_user_center_url('balance') . '">
                        <div class="muted-2-color em09 mb6">我的积分</div>
                        <div class="em12">' . zib_get_svg('points', null, 'px12 mr6 muted-2-color') . $user_points . '<i class="muted-2-color px12 fa fa-angle-right ml6"></i></div>
                    </a>';
    }

    if ($user_level_s) {
        $level       = zib_get_user_level($user_id);
        $level_badge = zib_get_level_badge($level, 'em12 mr10', true);
        $integral    = zib_get_user_integral($user_id);

        $user_card .= '<a rel="nofollow" class="block flex1 muted-box padding-10 ml6" href="' . zib_get_user_center_url('level') . '">
                        <div class="muted-2-color em09 mb6 flex jsb ac"><div>我的等级</div>' . $level_badge . '</div>
                        <div class="em12">' . $integral . '<span class="px12 ml6 muted-2-color">经验值<i class="fa fa-angle-right ml6"></i></span></div>
                    </a>';
    }

    if ($user_card) {
        $checkin_all_day = zib_get_user_checkin_all_day($user_id);
        $checkin_all_day = $checkin_all_day ? '<div class="mb6 em09">累计签到<badge class="c-blue">' . $checkin_all_day . '</badge>天</div>' : '';
        $display_name    = $user->display_name;
        $avatar_img      = zib_get_avatar_box($user_id, 'avatar-img avatar-mini mr6', false, true);
        $user_card       = '<div class="mb20"><div class="flex ac jsb"><div class="mb10">' . $avatar_img . $display_name . '</div>' . $checkin_all_day . '</div><div class="flex ab jsb">' . $user_card . '</div></div>';
    }

    //签到奖励卡片
    $reward_badge   = '';
    $theday_details = zib_get_user_the_checkin_details($user_id);
    if ($theday_details) {
        $theday_reward = $theday_details['points'] ? '积分+' . $theday_details['points'] . ' ' : '';
        $theday_reward .= $theday_details['integral'] ? '经验值+' . $theday_details['integral'] : '';
        $checkin_btn = '<div class="em09 c-blue">
                                <div class="mb10"><i class="fa fa-calendar-check-o mr6"></i>今日已签到</div>
                                <div class="px12">' . $theday_reward . '</div>
                            </div>';

    } else {
        $checkin_btn = zib_get_user_checkin_btn('but c-blue ml10 p2-10 radius', '立即签到', '今日已签到');
    }
    $checkin_reward_points   = (int) _pz('checkin_reward_points', 0);
    $checkin_reward_integral = (int) _pz('checkin_reward_integral', 0);
    $reward_badge            = $checkin_reward_points && $points_s ? '<badge class=" p2-10 mr6 c-green"  data-toggle="tooltip" title="积分奖励' . $checkin_reward_points . '">' . zib_get_svg('points') . '+' . $checkin_reward_points . '</badge>' : '';
    $reward_badge .= $checkin_reward_integral && $user_level_s ? '<badge class=" p2-10 mr6 c-purple"  data-toggle="tooltip" title="经验值奖励' . $checkin_reward_integral . '"><i class="fa fa-line-chart"></i>+' . $checkin_reward_integral . '</badge>' : '';

    $card_1 = '<div class="zib-widget flex ac jsb">
                <div class="mr10">
                    <div class="em09 mb10">' . zib_get_svg('gift-color', null, 'mr6 em12') . '每日签到奖励</div>
                    <div class="flex ac jsb">' . $reward_badge . '</div>
                </div>
                ' . $checkin_btn . '
            </div>';

    //连续签到卡片
    $reward_continuous_day      = zib_get_user_checkin_reward_continuous_day($user_id);
    $reward_continuous_day_html = $reward_continuous_day > 1 ? '<div class="mb6 em09">已连续签到<badge class="c-blue">' . $reward_continuous_day . '</badge>天</div>' : '';
    $max_day                    = zib_get_user_checkin_reward_continuous_max_day();
    $continuous_lists           = zib_get_checkin_continuous_mini_card(1, $checkin_reward_points, $checkin_reward_integral, $theday_details);

    for ($d = 2; $d <= $max_day; $d++) {
        $_points   = (int) _pz('continuous_checkin_reward', 0, 'points_' . $d);
        $_integral = (int) _pz('continuous_checkin_reward', 0, 'integral_' . $d);
        $continuous_lists .= zib_get_checkin_continuous_mini_card($d, $_points, $_integral, ($reward_continuous_day >= $d));
    }

    $card_2 = '<div class="mt10">
                <div class="flex ac jsb"><div class="em09 mb10">' . zib_get_svg('gift-color', null, 'mr6 em12') . '连续签到奖励</div>' . $reward_continuous_day_html . '</div>
                <div class="flex ac jsb">' . $continuous_lists . '</div>
            </div>';

    $html = zib_get_modal_colorful_header('c-blue', '<i class="fa fa-calendar-check-o"></i>', '签到领奖励');
    $html .= $user_card;
    $html .= $card_1;
    $html .= $card_2;
    return $html;
}

function zib_get_checkin_continuous_mini_card($d, $_points, $_integral, $is_ed = false)
{

    $points_s     = _pz('points_s');
    $user_level_s = _pz('user_level_s');

    $_mini_card = '';
    $_mini_card .= $_points && $points_s ? '<div class="badg badg-sm ' . ($is_ed ? 'b' : 'c') . '-green"  data-toggle="tooltip" title="积分奖励' . $_points . '">' . zib_get_svg('points') . '<br/>+' . $_points . '</div>' : '';
    $_mini_card .= $_integral && $user_level_s ? '<div class="badg badg-sm ' . ($is_ed ? 'b' : 'c') . '-purple"  data-toggle="tooltip" title="经验值奖励' . $_integral . '"><i class="fa fa-line-chart"></i><br/>+' . $_integral . '</div>' : '';

    return '<div class="flex1 text-center" style="flex: 1;">
                <div class="mb6 checkin-mini-box">' . $_mini_card . '</div>
                <div class="muted-2-color smail' . ($is_ed ? ' c-blue' : '') . '">' . ($is_ed ? '<i class="fa fa-check-circle"></i>' : '') . '第' . $d . '天</div>
            </div>';
}
