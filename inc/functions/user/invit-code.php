<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime: 2025-10-15 20:29:49
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|邀请码相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_ajax_invit_code_must_verify()
{

    //执行人机验证，验证不通过自动结束并返回提醒
    zib_ajax_man_machine_verification();

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    //执行邀请码验证
    $invit_code = zib_ajax_invit_code_verify();

    zib_send_json_success(array('code' => $invit_code->password, 'msg' => '邀请码有效，请继续完成注册'));
}
add_action('wp_ajax_invit_code_must_verify', 'zib_ajax_invit_code_must_verify');
add_action('wp_ajax_nopriv_invit_code_must_verify', 'zib_ajax_invit_code_must_verify');

//AJAX方式验证邀请码是否存在
function zib_ajax_invit_code_verify($code = null)
{
    if (!$code) {
        $code = !empty($_REQUEST['invit_code']) ? trim($_REQUEST['invit_code']) : false;
    }
    $invit_code_s = _pz('invit_code_s'); //邀请码注册模式

    //必须输入但是用户没有输入，则警告
    if (!$code && $invit_code_s === 'must') {
        zib_send_json_error('请输入邀请码');
    }

    //不是必须，且用户没有输入，退出
    if (!$code) {
        return false;
    }

    //用户输入了
    //邀请码查询
    $invit_code = zib_get_valid_invit_code(esc_sql($code));

    if (empty($invit_code->id)) {
        zib_send_json_error('邀请码无效或已使用' . ($invit_code_s !== 'must' ? '，请修改或删除邀请码' : ''));
    }

    return $invit_code;
}

/**
 * @description: 查询一个有效的邀请码
 * @param {*} $code
 * @return {*}
 */
function zib_get_valid_invit_code($code)
{
    return ZibCardPass::get_row(array('password' => $code, 'type' => 'invit_code', 'status' => '0'));
}

/**
 * @description:使用邀请码
 * @param {*} $user_id
 * @param {*} $invit_code
 * @return {*}
 */
function zib_use_invit_code($user_id, $invit_code_obj)
{
    if (empty($invit_code_obj->id)) {
        return;
    }

    if (!is_array($invit_code_obj->meta)) {
        $invit_code_obj->meta = array();
    }

    $data = array(
        'id'     => $invit_code_obj->id,
        'status' => 'used',
        'meta'   => array_merge(
            $invit_code_obj->meta,
            array(
                'user_id' => $user_id, //使用者
            )
        ),
    );
    ZibCardPass::update($data);
    do_action('zib_use_invit_code', $user_id, $invit_code_obj);
}

/**
 * @description: 自动创建邀请码
 * @param {*} $num
 * @param {*} $price
 * @param {*} $rand_number
 * @param {*} $rand_password
 * @return {*}
 */
function zib_generate_invit_code($num, $rand_password = 8, $reward = array(), $other = '')
{
    $meta = array('reward' => $reward);
    $time = current_time('mysql');
    $data = [];

    for ($i = 1; $i <= $num; $i++) {
        $data[] = ZibCardPass::add(array(
            'password'      => ZibCardPass::rand_password($rand_password),
            'type'          => 'invit_code',
            'create_time'   => $time,
            'modified_time' => $time,
            'status'        => '0', //正常
            'meta'          => $meta,
            'other'         => $other,
        ));
    }

    return $data;
}

/**
 * @description: 获取奖励的文字介绍
 * @param {*} $reward
 * @return {*}
 */
function zib_get_invit_code_reward_text($reward = array(), $separator = '、')
{
    if (!$reward) {
        return '';
    }
    $data = array();

    foreach (CFS_Module::invit_code_reward() as $type) {
        $key  = $type['id'];
        $name = trim($type['title']) ? trim($type['title']) : $type['subtitle'];
        if (!empty($reward[$key])) {
            $data[] = $name . '：' . $reward[$key];
        }
    }

    if (!$data) {
        return '';
    }

    return implode($separator, $data);
}

//奖励
function zib_use_invit_code_reward($user_id, $invit_code)
{
    $invit_code = (array) $invit_code;
    $reward     = !empty($invit_code['meta']['reward']) ? (array) $invit_code['meta']['reward'] : false;

    if (!$reward) {
        return;
    }

    foreach ($reward as $k => $v) {
        if (!$v) {
            continue;
        }
        switch ($k) {
            case 'level_integral':
                $v = (int) $v;
                if ($v && _pz('user_level_s')) {
                    zib_add_user_level_integral($user_id, $v, 'invit_code', true);
                }
                break;

            case 'points':
                $v = (int) $v;
                if ($v && _pz('points_s')) {
                    $data = array(
                        'order_num' => '', //订单号
                        'value'     => $v, //值 整数为加，负数为减去
                        'type'      => '邀请奖励', //中文说明
                        'desc'      => '使用邀请码注册奖励', //说明
                    );
                    zibpay_update_user_points($user_id, $data);
                }
                break;

            case 'balance':
                $v = (float) $v;
                if ($v && _pz('pay_balance_s')) {
                    $data = array(
                        'order_num' => '', //订单号
                        'value'     => $v, //值 整数为加，负数为减去
                        'type'      => '邀请奖励', //中文说明
                        'desc'      => '使用邀请码注册奖励', //说明
                    );
                    zibpay_update_user_balance($user_id, $data);
                }
                break;

            case 'vip':
                $v = (int) $v;
                if ($v > 0 && _pz('pay_user_vip_' . $v . '_s')) {
                    $vip_time = !empty($reward['vip_time']) ? $reward['vip_time'] : 0;
                    $exp_date = 0;
                    if (strtolower($vip_time) === 'permanent') {
                        $exp_date = 'Permanent';
                    } else {
                        $exp_date = (int) $vip_time;
                        if ($exp_date > 0) {
                            $exp_date = date('Y-m-d 23:59:59', strtotime("+$exp_date day", strtotime(current_time('Y-m-d H:i:s'))));
                        }
                    }

                    if ($exp_date) {
                        $data = array(
                            'vip_level' => $v, //等级
                            'exp_date'  => $exp_date, //有效截至时间
                            'type'      => '邀请奖励', //中文说明
                            'desc'      => '使用邀请码注册赠送', //说明
                        );
                        zibpay_update_user_vip($user_id, $data);
                    }

                }
                break;
        }
    }

}
add_action('zib_use_invit_code', 'zib_use_invit_code_reward', 10, 2);

//用户自己有邀请码
//和推荐返利绑定
