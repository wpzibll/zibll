<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-24 17:17:23
 * @LastEditTime : 2026-03-16 16:24:21
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户禁封小黑屋相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 判断用户是否是封禁状态
 * @param {*} $user_id
 * @return {*} false:未禁封 1：小黑屋 2：禁止登录
 */
function zib_user_is_ban($user_id = 0)
{

    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $ban = (int) get_user_meta($user_id, 'banned', true); //已经禁止
    if (!$ban) {
        return false;
    }
    $ban_time = get_user_meta($user_id, 'banned_time', true); //结束时间
    if ($ban_time) {
        //如果有结束时间，则和现在时间对比
        $current_time = current_time('Y-m-d H:i:s');
        if (strtotime($ban_time) < strtotime($current_time)) {
            //已经到期
            zib_updata_user_ban($user_id, 0, array('desc' => '到期自动解封')); //更新状态为未禁封
            return false;
        }
    }
    return $ban;
}

//获取用户封禁信息
function zib_get_user_ban_info($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $is_ban = zib_user_is_ban($user_id);
    if (!$is_ban) {
        return false;
    }

    $info = zib_get_user_meta($user_id, 'banned_log', true); //记录

    return isset($info['current']) ? $info['current'] : array(
        'type' => $is_ban,
    );
}

//获取用户封禁信息
function zib_get_user_ban_log($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $info = zib_get_user_meta($user_id, 'banned_log', true); //记录
    if (isset($info['current'])) {
        unset($info['current']);
    }
    return $info;
}

//更新用户禁封状态
function zib_updata_user_ban($user_id = 0, $type = 0, array $info = array())
{
    if (!$user_id) {
        return false;
    }

    $banned_time = isset($info['banned_time']) ? $info['banned_time'] : 0;
    update_user_meta($user_id, 'banned', $type);
    update_user_meta($user_id, 'banned_time', $banned_time);

    if ($type) {
        $default = array(
            'type'           => $type,
            'time'           => current_time('Y-m-d H:i:s'),
            'banned_time'    => 0,
            'operator_id'    => get_current_user_id(), //操作员
            'reason'         => '', //原因
            'desc'           => '', //说明
            'no_appeal'      => 0, //禁止申诉
            'no_appeal_desc' => '', //禁止申诉说明
        );
        $info = array_merge($default, $info);

        $banned_log = array();
        $banned_log = zib_get_user_meta($user_id, 'banned_log', true);
        if (!$banned_log || !is_array($banned_log)) {
            $banned_log = array();
        }
        $banned_log['current'] = $info;
        $new_log               = array_merge(array($info), $banned_log);
        zib_update_user_meta($user_id, 'banned_log', $new_log);
    }
    do_action('updata_user_ban', $user_id, $type, $info);
}

//用户禁封状态改变给用户发消息
function zib_updata_user_ban_msg($user_id, $type, $info)
{
    $user_data = get_userdata($user_id);
    $con       = '';
    if (!isset($user_data->display_name)) {
        return;
    }

    switch ($type) {
        case 1:
            $title = '您的账号已被拉入小黑屋，将受到发布、评论等多种限制';
            break;
        case 2:
            $title = '您的账号已被禁封，禁封期间将无法进行登录';
            break;
        case 0:
            $title = '您的账号已解封';
            break;
    }
    if (0 != $type) {
        $con .= '<div style="margin:10px 0;color: #959595;font-size: 13px;">';
        $con .= '禁封类型：' . (2 == $type ? '账户禁封(禁止登录)' : '小黑屋禁封') . '<br>';
        $con .= '开始时间：' . $info['time'] . '<br>';
        $con .= '结束时间：' . ($info['banned_time'] ? $info['banned_time'] : '永久') . '<br>';
        $con .= '禁封原因：' . $info['reason'] . '<br>';
        $con .= '</div>';
        if (!empty($info['no_appeal'])) {
            $con .= '同时本次禁封您将不能发起申诉' . '<br>';
            $con .= '禁止申诉说明：' . $info['no_appeal_desc'] . '<br>';
        } else {
            $con .= '如有异议或需申请' . ($info['banned_time'] ? '提前' : '') . '解封，您可以登录后在用户中心发起申诉' . '<br>';
        }
    }

    $msg_con = '您好！' . zib_get_user_name_link($user_id) . '<br>';
    $msg_con .= $title . '<br>';
    $msg_con .= $con . '<br>';
    $msg_con .= (isset($info['desc']) ? $info['desc'] : '') . '<br>';
    $msg_con .= '如有其它疑问请与客服联系<br />';

    $msg_args = array(
        'send_user'    => 'admin',
        'receive_user' => $user_id,
        'type'         => 'user_ban',
        'title'        => $title,
        'content'      => $msg_con,
    );

    //创建消息
    if (2 != $type) {
        //封号不发消息，反正也没发登录
        ZibMsg::add($msg_args);
    }

    //邮件通知
    if (_pz('email_updata_user_ban', true) && is_email($user_data->user_email) && !stristr($user_data->user_email, '@no')) {

        @wp_mail($user_data->user_email, $title, $msg_con);
    }
}
add_action('updata_user_ban', 'zib_updata_user_ban_msg', 10, 3);

function zib_user_ban_appeal_process($msg_id, $user_id, $type, $desc = '')
{
    //更新当前消息的状态
    ZibMsg::set_status($msg_id, $type);

    if (1 == $type) {
        zib_updata_user_ban($user_id, 0, array('desc' => $desc)); //更新状态为未禁封
        return true;
    } else {
        $is_ban = zib_get_user_ban_info($user_id);
        if (!$is_ban) {
            return true;
        }
        $user_data = get_userdata($user_id);
        $is_type   = $is_ban['type'];
        if (!isset($user_data->display_name)) {
            return false;
        }
        if (0 != $is_type) {
            $con = '';
            $con .= '';
            $con .= '<div style="margin:10px 0;color: #959595;font-size: 13px;">';
            $con .= '禁封类型：' . (2 == $is_type ? '账户禁封(禁止登录)' : '小黑屋禁封') . '<br>';
            $con .= '开始时间：' . $is_ban['time'] . '<br>';
            $con .= '结束时间：' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '<br>';
            $con .= '禁封原因：' . $is_ban['reason'] . '<br>';
            $con .= '</div>';
            if (!empty($is_ban['no_appeal'])) {
                $con .= '同时本次禁封您将不能发起申诉' . '<br>';
                $con .= '禁止申诉说明：' . $is_ban['no_appeal_desc'] . '<br>';
            } else {
                $con .= '如有异议或需申请' . ($is_ban['banned_time'] ? '提前' : '') . '解封，您可以登录后在用户中心发起申诉' . '<br>';
            }
        }
        $title   = '您的账号解封申诉暂未通过审核';
        $msg_con = '您好！' . zib_get_user_name_link($user_id) . '<br>';
        $msg_con .= $title . '<br>';
        $msg_con .= $con . '<br /><br />';
        $msg_con .= $desc . '<br>';
        $msg_con .= '如有其它疑问请与客服联系<br />';
        $msg_args = array(
            'send_user'    => 'admin',
            'receive_user' => $user_id,
            'type'         => 'user_ban',
            'title'        => $title,
            'content'      => $msg_con,
        );

        //创建消息
        if (2 != $is_type) {
            //封号不发站内消息，反正也没发登录
            ZibMsg::add($msg_args);
        }
        //邮件通知
        if (_pz('email_updata_user_ban', true) && is_email($user_data->user_email) && !stristr($user_data->user_email, '@no')) {
            @wp_mail($user_data->user_email, $title, $msg_con);
        }
        return true;
    }
}

//获取编辑用户禁封状态的模态框
function zib_get_edit_user_ban_link($user_id = 0, $class = '', $con = '', $ed_con = '')
{

    if (!$user_id || !_pz('user_ban_s', true) || is_super_admin($user_id) || !zib_current_user_can('set_user_ban') || get_current_user_id() == $user_id) {
        return;
    }

    if (!$con) {
        $con = '<i class="fa fa-ban mr6"></i>禁封此用户';
        if (zib_user_is_ban($user_id)) {
            $con = '<i class="fa fa-ban mr6"></i>解封此用户';
        }
    }

    $url_var = array(
        'action' => 'set_user_ban_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'height'        => 360,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_get_user_ban_log_link($user_id = 0, $class = '', $con = '', $ed_con = '')
{

    if (!$user_id || !_pz('user_ban_s', true) || is_super_admin($user_id) || !zib_current_user_can('set_user_ban')) {
        return;
    }

    if (!$con) {
        $con = '<i class="fa fa-ban mr6"></i>查看禁封记录';
    }

    $url_var = array(
        'action' => 'user_ban_log_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'new'           => true,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//查看自己的禁封信息
function zib_get_user_ban_info_link($user_id = 0, $class = '', $con = '')
{

    if ((!$user_id || get_current_user_id() != $user_id) && (!zib_current_user_can('set_user_ban'))) {
        return;
    }

    if (!$con) {
        $con = '<i class="fa fa-ban mr6"></i>查看禁封状态';
    }

    $url_var = array(
        'action' => 'user_ban_info_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//申诉链接
function zib_get_user_ban_appeal_link($user_id = 0, $class = '', $con = '<i class="fa fa-gavel mr6"></i>申诉')
{

    if (!$user_id || get_current_user_id() != $user_id) {
        return;
    }

    $url_var = array(
        'action' => 'user_ban_appeal_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_get_user_ban_options()
{
    return array(
        1 => '拉入小黑屋',
        2 => '禁封用户',
    );
}

//用户申诉模态框
function zib_get_user_ban_appeal_modal($user_id = 0)
{
    $is_ban = zib_get_user_ban_info($user_id);
    $header = zib_get_modal_colorful_header('c-blue', '<i class="fa fa-gavel"></i>', '禁封申诉');
    $header = '<div class="mb10 edit-header touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close icon') . '</button><b class="flex ac c-blue"><span class="toggle-radius mr10 c-blue"><i class="fa fa-gavel"></i></span>禁封申诉</b></div>';
    if (!$is_ban) {
        $con = ' <div class="text-center c-yellow" style="padding: 50px 0;">您已解封，请刷新页面</div>';
    } else {
        $in_type   = $is_ban['type'];
        $no_appeal = !empty($is_ban['no_appeal']); //是否禁止申诉 不能申诉

        if ($no_appeal) {
            //如果禁止申诉
            $no_appeal_dasc = !empty($is_ban['no_appeal_desc']) ? '<div class="mt10 muted-box ">' . $is_ban['no_appeal_desc'] . '</div>' : '';
            $con            = '<div>
                                <div class="c-yellow box-body">抱歉！您当前已无法进行申诉</div>
                                ' . $no_appeal_dasc . '
                            </div>';
        } else {
            $action  = 'user_ban_appeal';
            $pz_desc = _pz('ban_appeal_desc');
            $pz_desc = $pz_desc ? '<div class="mb10 em09">' . $pz_desc . '</div>' : '';
            $px_data = _pz('ban_appeal_keys');
            $input   = '';
            $footer  = '';
            $ing     = zib_get_user_ban_appeal_ing($user_id);
            if ($ing) {
                $ing_data = $ing->meta['data'];
                $input    = '<div class="c-yellow badg block mb6">您的申诉正在审核中，请耐心等待</div>';
                foreach ($ing_data as $k => $v) {
                    $input .= '<div class="flex"><div class="author-set-left" style="min-width: 80px;">' . $k . '</div><div class="author-set-right mt6">' . $v . '</div></div>';
                }
                $input .= '<div class="flex mb20"><div class="author-set-left" style="min-width: 80px;">提交时间</div><div class="author-set-right mt6">' . $ing->meta['time'] . '</div></div>';
            } else {
                if ($px_data) {
                    foreach ($px_data as $v) {
                        $input .= '<div class="mb10"><div class="muted-2-color mb6">' . $v['t'] . '</div><input class="form-control" type="input" name="data[' . $v['t'] . ']"></div>';
                    }
                }
                $input .= '<div class="mb10"><div class="muted-2-color mb6">请填写申诉说明</div><textarea class="form-control mb20" name="data[申诉说明]" tabindex="2" placeholder="详细的说明更容易通过审核喔" rows="3"></textarea></div>';
                $footer = '
                <div class="but-average modal-buts">
                    <input type="hidden" name="action" value="' . $action . '">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    ' . wp_nonce_field($action, '_wpnonce', false, false) . '
                    <button type="button" data-dismiss="modal" class="but">取消</button>
                    <button class="but c-blue wp-ajax-submit">提交申诉</button>
                </div>';
            }

            $con = '
            <form>
                <div class="scroll-y mini-scrollbar max-vh5">
                    <div class="mb10 muted-box padding-10 em09">
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">用户名</div><div class="author-set-right mt6">' . zib_get_user_name("id=$user_id&vip=0&medal=0") . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">状态</div><div class="author-set-right mt6">' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">开始时间</div><div class="author-set-right mt6">' . $is_ban['time'] . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">结束时间</div><div class="author-set-right mt6">' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">原因</div><div class="author-set-right mt6">' . $is_ban['reason'] . '</div></div>
                        ' . ($is_ban['desc'] ? '<div class="flex"><div class="author-set-left" style="min-width: 80px;">说明</div><div class="author-set-right mt6">' . $is_ban['desc'] . '</div></div>' : '') . '
                    </div>
                    ' . $input . $pz_desc . '
                </div>
                ' . $footer . '
            </form>';
        }

    }
    return $header . $con;
}

//查看自己禁封状态的模态框
function zib_get_ban_info_modal($user_id = 0)
{
    $is_ban = zib_get_user_ban_info($user_id);
    $header = zib_get_modal_colorful_header('c-gray', '<i class="fa fa-ban"></i>', '小黑屋禁封中');
    if (!$is_ban) {
        $con = ' <div class="text-center c-yellow" style="padding: 50px 0;">您已解封，请刷新页面</div>';
    } else {
        $in_type   = $is_ban['type'];
        $no_appeal = !empty($is_ban['no_appeal']); //是否禁止申诉 不能申诉

        if ($no_appeal) {
            //如果禁止申诉
            $no_appeal_dasc = !empty($is_ban['no_appeal_desc']) ? '<div class="mt6">' . $is_ban['no_appeal_desc'] . '</div>' : '';
            $footer         = '<div class="muted-box em09">
                                <div class="c-yellow">您当前已无法进行申诉</div>
                                ' . $no_appeal_dasc . '
                            </div>';
        } else {
            $appeal_link = zib_get_user_ban_appeal_link($user_id, 'but c-blue');
            $footer      = '<div class="but-average modal-buts">
                        <button type="button" data-dismiss="modal" class="but">取消</button>
                        ' . $appeal_link . '
                    </div>';
        }
        $con = '
                <div class="mb20">
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">用户名</div><div class="author-set-right mt6">' . zib_get_user_name("id=$user_id&vip=0&medal=0") . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">状态</div><div class="author-set-right mt6">' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">开始时间</div><div class="author-set-right mt6">' . $is_ban['time'] . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">结束时间</div><div class="author-set-right mt6">' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">原因</div><div class="author-set-right mt6">' . $is_ban['reason'] . '</div></div>
                    ' . ($is_ban['desc'] ? '<div class="flex"><div class="author-set-left" style="min-width: 80px;">说明</div><div class="author-set-right mt6">' . $is_ban['desc'] . '</div></div>' : '') . '
                </div>
                ' . $footer;
    }
    return $header . $con;
}

//查看用户禁封记录的模态框
function zib_get_ban_log_modal($user_id = 0)
{
    $logs     = zib_get_user_ban_log($user_id);
    $header   = zib_get_modal_colorful_header('c-yellow', '<i class="fa fa-ban"></i>', '禁封记录');
    $log_html = '';
    if (!$logs) {
        $desc = ' <div class="text-center c-yellow" style="padding: 50px 0;">该用户暂无禁封记录</div>';
    } else {
        $desc = ' <div class="c-yellow mb10">该用户共有' . count($logs) . '次禁封记录</div>';
        $i    = 1;
        foreach ($logs as $log) {
            $no_appeal      = !empty($log['no_appeal']); //是否禁止申诉 不能申诉
            $no_appeal_dasc = !empty($log['no_appeal_desc']) ? $log['no_appeal_desc'] : '';
            $no_appeal_html = $no_appeal ? '<div class="flex"><div class="author-set-left" style="min-width: 80px;"><span class="c-red">禁止申诉</span></div><div class="author-set-right mt6">' . $no_appeal_dasc . '</div></div>' : '';
            $log_html .= '
            <div class="em09 muted-box muted-2-color mb6 padding-10">
                <div class="border-bottom flex jsb p-b6"><div class="muted-color"><badge class="mr6 c-blue">' . $i . '</badge>' . $log['time'] . '</div><span class="badg badg-sm ' . (2 == $log['type'] ? 'c-red' : 'c-yellow') . '">' . (2 == $log['type'] ? '禁封' : '小黑屋') . '</span></div>
                <div class="flex"><div class="author-set-left" style="min-width: 80px;">结束时间</div><div class="author-set-right mt6">' . ($log['banned_time'] ? $log['banned_time'] : '永久') . '</div></div>
                <div class="flex"><div class="author-set-left" style="min-width: 80px;">原因</div><div class="author-set-right mt6">' . $log['reason'] . '</div></div>
                ' . ($log['desc'] ? '<div class=" flex"><div class="author-set-left" style="min-width: 80px;">说明</div><div class="author-set-right mt6">' . $log['desc'] . '</div></div>' : '') . '
                ' . $no_appeal_html . '
            </div>
            ';
            ++$i;
        }
    }
    $html = '
    <div class="flex ac mb6">' . zib_get_avatar_box($user_id, 'avatar-mini mr6') . zib_get_user_name("id=$user_id&vip=0&medal=0") . '</div>
    ' . $desc . '
    <div class="scroll-y mini-scrollbar max-vh5">' . $log_html . '</div>
    ';
    return $header . $html;
}

//获取设置用户禁封状态的模态框
function zib_get_set_user_ban_modal($user_id = 0)
{
    $is_ban = zib_get_user_ban_info($user_id);
    $action = 'set_user_ban';
    $log    = zib_get_user_ban_log($user_id);

    $log_view = $log && count($log) > 1 ? zib_get_user_ban_log_link($user_id, 'em09 c-yellow', '当前用户已有' . count($log) . '次禁封记录，请谨慎操作') : '';

    if ($is_ban) {
        $in_type      = $is_ban['type'];
        $header_titel = 2 == $in_type ? '解封用户' : '移出小黑屋';
        $header       = zib_get_modal_colorful_header('c-yellow', '<i class="fa fa-ban"></i>', $header_titel);
        $log_view     = $log_view ? '<div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">禁封记录</div><div class="author-set-right mt6">' . $log_view . '</div></div>' : '';
        $html         = '<form>';
        $html .= '
                <div class="mb20">
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">用户</div><div class="author-set-right mt6">' . zib_get_user_name("id=$user_id&vip=0&medal=0&class=inflex ac badg p2-10 em09") . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">状态</div><div class="author-set-right mt6">' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋') . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">开始时间</div><div class="author-set-right mt6">' . $is_ban['time'] . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">结束时间</div><div class="author-set-right mt6">' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">原因</div><div class="author-set-right mt6">' . $is_ban['reason'] . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">说明</div><div class="author-set-right mt6">' . $is_ban['desc'] . '</div></div>
                    ' . $log_view . '
                </div>
                <div><textarea class="form-control mb20" name="desc" tabindex="2" placeholder="给用户的留言" rows="2" autoHeight="true" maxHeight="110"></textarea></div>
                ';

        $html .= '<div class="but-average modal-buts">
                    <input type="hidden" name="ban" value="0">
                    <input type="hidden" name="action" value="' . $action . '">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    ' . wp_nonce_field($action, '_wpnonce', false, false) . '
                    <button type="button" data-dismiss="modal" class="but">取消</button>
                    <button class="but c-yellow wp-ajax-submit">确认' . $header_titel . '</button>
                </div>';
        $html .= '</form>';

        return $header . $html;
    }

    $header  = '<div class="mb10 edit-header touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="flex ac c-red"><span class="toggle-radius mr10 c-red"><i class="fa fa-ban"></i></span>禁封用户</b></div>';
    $options = array(
        '1' => array(
            'name' => '拉入小黑屋',
            'desc' => '将用户拉入小黑屋后，用户将失去所有的发布权限及大部分的操作权限，例如发帖、评论等',
        ),
        '2' => array(
            'name' => '禁封用户',
            'desc' => '禁封的用户将不能登录',
        ),
    );

    $input = '';
    $desc  = '';
    foreach ($options as $key => $v) {
        $desc .= '<div class="" data-controller="ban" data-condition="==" data-value="' . $key . '"' . (1 == $key ? '' : ' style="display: none;"') . ' >' . $v['desc'] . '</div>';
        $input .= '<label><input type="radio"' . (checked(1, $key, false)) . ' name="ban" value="' . $key . '"><span class="p2-10 mr6 but but-radio">' . $v['name'] . '</span></label>';
    }

    //禁封时间
    $time_html = '';
    $time_args = array(
        1   => '1天',
        7   => '7天',
        14  => '14天',
        30  => '30天',
        100 => '100天',
        0   => '永久',
    );
    foreach ($time_args as $k => $v) {
        $time_html .= '<label><input type="radio"' . (checked(7, $k, false)) . ' name="time_day" value="' . $k . '"><span class="p2-10 mr6 but but-radio">' . $v . '</span></label>';
    }

    $reason_args = _pz('ban_preset_reason');
    $reason_html = '';
    if ($reason_args) {
        foreach ($reason_args as $v) {
            $reason_html .= '<label><input type="radio" name="reason" value="' . esc_attr($v['t']) . '"><span class="p2-10 mr6 but but-radio">' . $v['t'] . '</span></label>';
        }
    }

    $reason_html .= '<label><input type="radio" name="reason" value="other"><span class="p2-10 mr6 but but-radio">其它原因</span></label><div class="mb20" data-controller="reason" data-condition="==" data-value="other" style="display: none;"><input type="text" style="display: block;" class="form-control" name="reason_other" tabindex="1" placeholder="请填写禁封原因"></div>';

    $html = '<form class="dependency-box">';
    $html .= '
            <div class="scroll-y mini-scrollbar max-vh7">
                <div class="mt10 mb20">
                    <div class="flex ac mb6">' . zib_get_avatar_box($user_id, 'avatar-mini mr6') . zib_get_user_name("id=$user_id&vip=0&medal=0") . '</div>
                    ' . $log_view . '
                </div>
                <div class="mb6 muted-2-color">禁封方式</div>
                <div class="form-radio form-but-radio">' . $input . '</div><div class="em09 c-red opacity8 mb10">' . $desc . '</div>
                <div class="muted-2-color mb6">禁封时间</div><div class="form-radio mb10 form-but-radio">' . $time_html . '</div>
                <div class="muted-2-color mb6">选择禁封原因</div><div class="form-radio mb10 form-but-radio">' . $reason_html . '</div>
                <div class="muted-2-color mb6">禁封说明</div><textarea class="form-control mb20" name="desc" tabindex="2" placeholder="请输入禁封说明或给用户的留言" rows="2" autoHeight="true" maxHeight="110"></textarea>
                <div class="mb20"><label class="flex jsb ac" style="font-weight: normal;"><input class="hide" name="delete_content" type="checkbox"><div class="flex1 mr20"><div class="muted-2-color mb3">删除全部内容</div><div class="muted-3-color px12">将该用户发布的全部文章、帖子、评论移至回收站</div></div><div class="form-switch flex0"></div></label></div>
                <div class="mb20">
                    <div class="mb6"><label class="flex jsb ac" style="font-weight: normal;"><input class="hide" name="no_appeal" type="checkbox"><div class="flex1 mr20"><div class="muted-2-color mb3">禁止用户申诉</div><div class="muted-3-color px12">开启后用户将无法进行申诉</div></div><div class="form-switch flex0"></div></label></div>
                    <div class="" data-controller="no_appeal" data-condition="!=" data-value="" style="display: none;"><textarea class="form-control" name="no_appeal_desc" tabindex="1" placeholder="请填写禁止申诉说明" rows="2"></textarea></div>
                </div>
                </div>';

    $html .= '<div class="mt20 but-average modal-buts">
                <input type="hidden" name="action" value="' . $action . '">
                <input type="hidden" name="user_id" value="' . $user_id . '">
                ' . wp_nonce_field($action, '_wpnonce', false, false) . '
                <button type="button" data-dismiss="modal" class="but">取消</button>
                <button class="but c-red wp-ajax-submit"><i class="fa fa-ban"></i>确认禁封</button>
            </div>';
    $html .= '</form>';

    return $header . $html;
}

function zib_get_user_ban_badge($user_id = 0, $class = 'avatar-badge', $tip = 1)
{

    $is_ban = zib_user_is_ban($user_id);
    if (!$is_ban) {
        return;
    }
    $title    = 2 === $is_ban ? '违规已封号' : '小黑屋禁封中';
    $tip_attr = $tip ? ' data-toggle="tooltip"' : '';
    $badge    = '<img class="' . $class . '" ' . $tip_attr . ' src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/user-ban' . (2 === $is_ban ? '-2' : '') . '.svg" title="' . $title . '" alt="' . esc_attr(strip_tags($title)) . '">';

    return $badge;
}

//获取处理申诉的链接
function zib_get_user_ban_appeal_process_url($user_id)
{
    $url = admin_url('admin.php?page=user_ban');

    return add_query_arg(array('send_user' => $user_id, 'type' => 'ban', 'status' => 0), $url);
}

//查询已经存在的申诉
function zib_get_user_ban_appeal_ing($user_id)
{

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'ban_appeal',
        'status'    => 0,
    );
    return ZibMsg::get_row($msg_get_args);
}

//执行挂钩显示
if (_pz('user_ban_s', true)) {

                                                                                                                                                        //用户个人主页显示禁封按钮
    function zib_author_header_drop_lists_ban($lists, $author_id)
    {
        $link = zib_get_edit_user_ban_link($author_id);
        if (!$link && _pz('user_report_s', true)) {
            $link = zib_get_report_link($author_id, '', '', '<i class="fa fa-exclamation-triangle mr6 c-red"></i>举报用户');
        }

        $lists .= $link ? '<li>' . $link . '</li>' : '';
        return $lists;
    }
    add_filter('author_header_drop_lists', 'zib_author_header_drop_lists_ban', 10, 2);

    //评论按钮显示
    function zib_ban_comments_action_lists_filter($lists, $comment)
    {
        $user_id = $comment->user_id;
        $link    = zib_get_edit_user_ban_link($user_id, '', '<i class="fa fa-ban mr6 fa-fw c-red"></i>禁封用户');
        if (!$link && _pz('user_report_s', true)) {
            $link = zib_get_report_link($user_id, get_comment_link($comment), '', '<i class="fa fa-exclamation-triangle mr10 fa-fw c-red"></i>举报');
        }

        $lists .= $link ? '<li>' . $link . '</li>' : '';
        return $lists;
    }
    add_filter('comments_action_lists', 'zib_ban_comments_action_lists_filter', 10, 2);

    //挂钩权限管理
    function zib_ban_user_can_filter($is_can, $user_id)
    {
        if (zib_user_is_ban($user_id)) {
            return false;
        }
        return $is_can;
    }
    add_filter('zib_user_can', 'zib_ban_user_can_filter', 99, 2);

    //用户头像显示
    function zib_ban_avatar_badge_filter($badge, $user_id)
    {
        $ban_badge = zib_get_user_ban_badge($user_id);
        if ($ban_badge) {
            return $ban_badge;
        }
        return $badge;
    }
    add_filter('user_avatar_badge', 'zib_ban_avatar_badge_filter', 11, 2);

    //用户昵称显示
    function zib_ban_name_badge_filter($badge, $user_id)
    {
        $ban_badge = zib_get_user_ban_badge($user_id, 'img-icon mr3');
        if ($ban_badge) {
            return $ban_badge;
        }
        return $badge;
    }
    add_filter('user_name_badge', 'zib_ban_name_badge_filter', 11, 2);

    //用户中心显示
    function zib_ban_author_header_identity_filter($badge, $user_id)
    {
        $is_ban = zib_user_is_ban($user_id);
        if (1 == $is_ban) {
            return '<span class="badg"><img class="img-icon mr3" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/user-ban.svg" alt="小黑屋禁封中">小黑屋禁封中</span>' . $badge;
        }
        if (2 == $is_ban) {
            return '<span class="badg c-red"><img class="img-icon mr3" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/user-ban-2.svg" alt="违规已封号">违规已封号</span>' . $badge;
        }
        return $badge;
    }
    add_filter('author_header_identity', 'zib_ban_author_header_identity_filter', 11, 2);

    //用户中心显示详情
    function zib_ban_page_header_desc_filter($desc, $user_id)
    {
        $ban_badge = zib_get_user_ban_badge($user_id, 'img-icon mr3');
        if ($ban_badge) {
            $ban_badge = zib_get_user_ban_info_link($user_id, 'but', $ban_badge . '小黑屋禁封中 <i class="ml6"><i class="fa fa-angle-right em12"></i></i>');
            return $ban_badge . $desc;
        }
        return $desc;
    }
    add_filter('user_page_header_desc', 'zib_ban_page_header_desc_filter', 11, 2);

    //禁封用户限制登录
    function zib_user_ban_restrict_login()
    {
        $user_id = get_current_user_id();
        if ($user_id) {
            if (zib_user_is_ban($user_id) === 2) {
                $is_ban       = zib_get_user_ban_info($user_id);
                $in_type      = $is_ban['type'];
                $user         = get_userdata($user_id);
                $display_name = $user->display_name;

                $con = '
                <div class="c-red box-body separator mb30">抱歉，您的账号当前处于禁封状态，暂时无法登录</div>
                <div class="mb20 muted-box text-left" style=" max-width: 600px; margin: auto; ">
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">用户名</div><div class="author-set-right mt6">' . $display_name . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">状态</div><div class="author-set-right mt6">' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋') . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">开始时间</div><div class="author-set-right mt6">' . $is_ban['time'] . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">结束时间</div><div class="author-set-right mt6">' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '</div></div>
                    <div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">原因</div><div class="author-set-right mt6">' . $is_ban['reason'] . '</div></div>
                    ' . ($is_ban['desc'] ? '<div class="flex"><div class="author-set-left" style="min-width: 80px;">说明</div><div class="author-set-right mt6">' . $is_ban['desc'] . '</div></div>' : '') . '
                ';
                if (!empty($info['no_appeal'])) {
                    $con .= '<div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">申诉权限</div><div class="author-set-right mt6 c-red">本次禁封您将不能发起申诉</div></div>';
                    $con .= '<div class="mb10 flex"><div class="author-set-left" style="min-width: 80px;">禁止申诉说明</div><div class="author-set-right mt6">' . $is_ban['no_appeal_desc'] . '</div></div>';
                    $con .= '<div class="mt20 text-center"><a href="' . wp_logout_url(home_url()) . '" class="but c-yellow padding-lg hollow mr20">退出登录</a></div>';
                } else {
                    $con .= '<div class="mt20 text-center"><a href="' . wp_logout_url(home_url()) . '" class="but c-yellow padding-lg hollow mr20">退出登录</a>' . zib_get_user_ban_appeal_link($user_id, 'but c-blue padding-lg hollow') . '</div>';
                }
                $con .= '</div>';
                //退出登录
                // wp_logout();
                //加载错误页面
                zib_die_page($con, array('img' => ZIB_TEMPLATE_DIRECTORY_URI . '/img/null-user.svg'));
            }
        }
    }
    add_action('template_redirect', 'zib_user_ban_restrict_login', 1);
}

//用户权限提醒
function zib_get_user_ban_nocan_info($user_id, $msg = '暂时无法操作', $margin = 30, $width = 280, $height = 0)
{
    $in_type = zib_user_is_ban($user_id);
    if (!$in_type) {
        return;
    }

    $_msg = 2 === $in_type ? '账号禁封中' : '小黑屋禁封中';
    $_msg .= $msg ? '，' . $msg : '';
    $ban_badge = zib_get_user_ban_info_link($user_id, 'muted-3-color', zib_get_user_ban_badge($user_id, 'img-icon mr3') . $_msg . '<i class="ml6"><i class="fa fa-angle-right em12"></i></i>');

    return zib_get_null($ban_badge, $margin, 'null-cap.svg', '', $width, $height);
}

/************以下为举报功能 */
//获取处理举报的链接report
function zib_get_user_report_process_url($user_id)
{
    $url = admin_url('admin.php?page=user_ban');

    return add_query_arg(array('report_user' => $user_id, 'type' => 'report', 'status' => 0), $url);
}

//查询已经存在的申诉
function zib_get_user_report_ing($user_id)
{
    $current_user_id = get_current_user_id();

    $msg_get_args = array(
        'send_user' => ($current_user_id ? $current_user_id : 'admin'),
        'type'      => 'user_report',
        'status'    => 0,
        'meta'      => 'like|%report_user_id_' . $user_id . '%',
    );
    return ZibMsg::get_row($msg_get_args);
}

//获取举报链接
function zib_get_report_link($user_id, $url = '', $class = '', $con = '<i class="fa fa-exclamation-triangle mr6"></i>举报')
{
    //不能举报管理员
    //拥有封号权限的用户不显示举报按钮
    //已经被封号的不显示
    if (!$user_id || get_current_user_id() == $user_id || !_pz('user_report_s', true) || is_super_admin($user_id) || !zib_current_user_can('user_report') || zib_current_user_can('set_user_ban') || zib_user_is_ban($user_id)) {
        return;
    }

    $url_var = array(
        'url'      => urlencode($url),
        'user_id'  => $user_id,
        '_wpnonce' => wp_create_nonce('report_modal'),
        'action'   => 'report_modal',
    );
    $args = array(
        'tag'           => 'a',
        'class'         => $class,
        'height'        => 320,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_get_report_modal($user_id, $url = '')
{
    $ing = zib_get_user_report_ing($user_id);
    //当前已经存在被举报的记录
    if ($ing) {
        $current_user_id = get_current_user_id();
        $header          = zib_get_modal_colorful_header('c-yellow', '<i class="fa fa-exclamation-triangle"></i>', '举报不良信息');
        $con             = '<div class="c-yellow box-body mb10"' . ($current_user_id ? '' : ' style=" padding: 60px 0; "') . '>已收到该用户的被举报信息，我们会尽快审核处理，感谢您的反馈</div>';
        if (get_current_user_id()) {
            //如果是登录状态，则显示详情
            $ing_data       = $ing->meta;
            $report_user_id = $ing_data['report_user_id'];
            $con .= '
                    <div class="mb10 muted-box padding-10 em09">
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">举报用户</div><div class="author-set-right mt6">' . zib_get_user_name("id=$report_user_id&vip=0&medal=0") . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">举报原因</div><div class="author-set-right mt6">' . $ing_data['reason'] . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">详细说明</div><div class="author-set-right mt6">' . $ing_data['desc'] . '</div></div>
                        <div class="flex"><div class="author-set-left" style="min-width: 80px;">提交时间</div><div class="author-set-right mt6">' . $ing_data['time'] . '</div></div>
                    </div>';
        }
        return $header . $con;
    }

    $action = 'report_user';
    $header = '<div class="mb10 edit-header touch"><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button><b class="flex ac c-red"><span class="toggle-radius mr10 c-red"><i class="fa fa-exclamation-triangle"></i></span>举报用户</b></div>';

    //举报原因
    $reason_args = _pz('ban_preset_reason');
    $reason_html = '<input type="hidden" name="reason" value="">';
    if ($reason_args) {
        foreach ($reason_args as $v) {
            $reason_html .= '<span data-for="reason" data-value="' . esc_attr($v['t']) . '" class="mr6 mb6 badg p2-10 pointer">' . $v['t'] . '</span>';
        }
    }

    $reason_html .= '<span data-for="reason" data-value="other" class="mr6 mb6 badg p2-10 pointer">其它原因</span><div class="mb20" data-controller="reason" data-condition="==" data-value="other" style="display: none;"><textarea class="form-control" name="reason_other" tabindex="1" placeholder="请填写禁封原因" rows="2"></textarea></div>';

    //举报须知
    $desc = _pz('user_report_desc');
    $desc = $desc ? '<div class="em09 padding-h10 muted-box mb10">' . $desc . '</div>' : '';

    //举报上传图片
    $img_html = '';
    if (_pz('user_report_img_s')) {
        $img_html = '<div class="form-upload mb10">';
        $img_html .= '<div class="em09 muted-2-color mb6">添加图片举证</div>';
        $img_html .= '<div class="preview">'; //正方形
        $img_html .= '<div class="add"></div>';
        $img_html .= '</div>';
        $img_html .= '<input class="hide" type="file" zibupload="image_upload" multiple="multiple" multiple_max="3" accept="image/gif,image/jpeg,image/jpg,image/png" name="image" action="image_upload" multiple="true">';
        $img_html .= '</div>';
    }

    $html = '<form class="dependency-box">';
    $html .= '
            <div class="scroll-y mini-scrollbar max-vh7">
                <div class="mt10 mb20">
                    <div class="flex ac mb6">' . zib_get_avatar_box($user_id, 'avatar-mini mr6') . zib_get_user_name("id=$user_id&vip=0&medal=0") . '</div>
                </div>
                <div class="form-radio mb20 form-but-radio"><div class="em09 muted-2-color mb6">举报原因</div>' . $reason_html . '</div>
                <div class="mb20"><div class="em09 muted-2-color mb6">违规链接</div><input type="text" class="form-control" name="url" tabindex="1" value="' . $url . '" placeholder="请输入违规链接"></div>
                <div class="mb20"><div class="em09 muted-2-color mb6">详细描述</div><textarea class="form-control" name="desc" tabindex="2" placeholder="请详细描述违规事项" rows="2" autoHeight="true" maxHeight="110"></textarea></div>
                ' . $img_html . $desc . '
            </div>';

    $html .= '<div class="but-average modal-buts">
                <input type="hidden" name="action" value="' . $action . '">
                <input type="hidden" name="user_id" value="' . $user_id . '">
                ' . wp_nonce_field($action, '_wpnonce', false, false) . '
                <button type="button" data-dismiss="modal" class="but">取消</button>
                <button class="but c-blue" zibupload="submit" zibupload-nomust="true"><i class="fa fa-check" aria-hidden="true"></i>确认提交</button>
            </div>';
    $html .= '</form>';

    return $header . $html;
}

//处理举报信息
function zib_user_report_process($msg_id, $user_id, $desc = '')
{
    //更新当前消息的状态
    ZibMsg::set_status($msg_id, 1);
    ZibMsg::set_meta($msg_id, 'process_desc', $desc);

    if ($user_id && is_numeric($user_id)) {
        $user_data = get_userdata($user_id);
        if (!isset($user_data->display_name)) {
            return;
        }
        $title   = '您提交的举报信息已处理完成，感谢您的反馈';
        $msg_con = '您好！' . zib_get_user_name_link($user_id) . '<br>';
        $msg_con .= $title . '<br>';
        $msg_con .= $desc . '<br>';
        $msg_con .= '如有其它疑问请与客服联系<br />';

        $msg_args = array(
            'send_user'    => 'admin',
            'receive_user' => $user_id,
            'type'         => 'user_report_reply',
            'title'        => $title,
            'content'      => $msg_con,
        );
        ZibMsg::add($msg_args);

        //邮件通知
        if (_pz('email_report_process', true) && is_email($user_data->user_email) && !stristr($user_data->user_email, '@no')) {
            @wp_mail($user_data->user_email, $title, $msg_con);
        }

        //发送微信模板消息
        $wechat_template_id = zib_get_wechat_template_id('report_process');
        if ($wechat_template_id) {
            /**
            {{first.DATA}}
            举报时间：{{keyword1.DATA}}
            举报场景：{{keyword2.DATA}}
            举报结果：{{keyword3.DATA}}
            {{remark.DATA}}
             */

            //准备参数
            $msg_db = ZibMsg::get_row(array('id' => $msg_id));

            //发送微信模板消息
            $wechat_template_data = array(
                'time'   => $msg_db->create_time,
                'reason' => !empty($msg_db->meta['reason']) ? $msg_db->meta['reason'] : '不良信息举报',
                'desc'   => $desc ?: '您提交的举报信息已处理完成，感谢您的反馈',
            );
            zib_wechat_template_send($user_id, 'report_process', $wechat_template_data);
        }

    }
    return true;
}
