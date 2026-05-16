<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-31 20:07:39
 * @LastEditTime : 2026-05-05 19:26:01
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|私信系统
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class Zib_Private
{

    /**
     * @description: 获取聊天人数
     * @param int $user_id 用户ID
     * @return int 聊天人数
     */
    public static function get_chat_users_count($user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_id = (int) $user_id; //只允许整数，以避免SQL注入
        if (!$user_id) {
            return 0;
        }

        $db    = self::get_chat_users_db($user_id);
        $count = $db->count('a.id');

        return $count ? (int) $count : 0;
    }

    public static function get_chat_users_db($user_id)
    {

        $user_id = (int) $user_id;

        $sub_db = zibDB::name(ZibMsg::$table_name);
        $sub_db->where([
            ['status', '=', 0],
            ['type', '=', 'private'],
            ['relation' => 'or',
                ['send_user', '=', $user_id],
                ['receive_user', '=', $user_id],
            ],
        ]);
        $sub_db->group('receive_user+send_user');
        $sub_db->field('max(id) as id');
        //SELECT count(id),max(id) as id FROM aaa_zib_message WHERE (status = 0 AND type = 'private' AND (send_user = 1 OR receive_user = 1)) GROUP BY receive_user+send_user
        $db = zibDB::name(ZibMsg::$table_name)->alias('a');
        $db->join([$sub_db, 'b'], 'a.id = b.id');
        $db->field('count(a.id)');

        /*SELECT COUNT(a.id) FROM aaa_zib_message AS a INNER JOIN 
        (SELECT max(id) as id FROM aaa_zib_message  WHERE (status = 0 AND type = 'private' AND (send_user = 1 OR receive_user = 1)) GROUP BY receive_user+send_user) AS b
        ON (a.id = b.id)
        WHERE 1=1 LIMIT 0, 1 */

        return $db;
    }

    /**
     * @description: 获取聊天人明细
     * @param {*}
     * @return {*}
     */
    public static function get_chat_users($user_id = '', $offset = 0, $ice_perpage = 10)
    {
        global $wpdb;

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $user_id = (int) $user_id; //只允许整数，以避免SQL注入
        if (!$user_id) {
            return false;
        }
        $db = self::get_chat_users_db($user_id);
        $db->order('id', 'DESC')->field('a.*');
        if ('all' != $ice_perpage) {
            $db->limit($ice_perpage, $offset);
        }

        return $db->select()->result();
    }

    /**
     * @description: 获取聊天人明细列表
     * @param {*}
     * @return {*}
     */
    public static function get_chat_lists($user_id = '', $ice_perpage = 10, $paged = 1, $class = '')
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        //准备翻页参数
        $paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : $paged;
        $ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : $ice_perpage;
        $offset      = $ice_perpage * ($paged - 1);

        $user_db = self::get_chat_users($user_id, $offset, $ice_perpage);

        if (!$user_db) {
            return false;
        }

        $user_ids = array();
        $lists    = '';
        foreach ($user_db as $user) {
            $user = (array) $user;

            if (!is_numeric($user['send_user']) || !is_numeric($user['receive_user'])) {
                continue;
            }
            //准备url参数
            $ajax_query_arg = array(
                'action'   => 'private_window',
                '_wpnonce' => wp_create_nonce('private_window'), //安全验证
            );
            if ($user['send_user'] == $user_id) {
                //如果我是发件人
                $avatar                         = zib_get_data_avatar((int) $user['receive_user']);
                $display_name                   = get_userdata($user['receive_user'])->display_name ?? '未知用户';
                $ajax_query_arg['receive_user'] = $user['receive_user'];
            } else {
                //我是接收人
                $avatar                         = zib_get_data_avatar((int) $user['send_user']);
                $display_name                   = get_userdata($user['send_user'])->display_name ?? '未知用户';
                $ajax_query_arg['receive_user'] = $user['send_user'];
                if (!strstr($user['readed_user'] ?: '', '[' . $user_id . ']')) {
                    $avatar .= '<badge class="top point"></badge>';
                }
            }

            //黑名单
            $is_blacklist = self::is_blacklist($ajax_query_arg['receive_user'], $user_id);
            if ($is_blacklist) {
                $display_name = '<span class="mr3 c-red" data-toggle="tooltip" title="黑名单用户"><i class="fa fa-ban"></i></span>' . $display_name;
            }

            //时间显示
            $create_time = $user['create_time'];
            $create_time = '<span class="pull-right px12 muted-3-color" title="' . $create_time . '" data-placement="bottom">' . zib_get_time_ago($create_time) . '</span>';

            //将内容格式化
            $con = self::get_content($user['content'], 'mini');

            $ajax_url = esc_url(add_query_arg($ajax_query_arg, admin_url('admin-ajax.php')));

            $list = '';
            $list .= '<div class="flex relative msg-list">';
            $list .= '<div class="avatar-img shrink0 mr10">' . $avatar . '</div>';
            $list .= '<div class="flex1">';
            $list .= $create_time;
            $list .= '<dt class="text-ellipsis muted-color">' . $display_name . '</dt>';
            $list .= '<dd class="em09 muted-2-color text-ellipsis">' . $con . '</dd>';
            $list .= '</div>';
            $list .= '</div>';

            $lists .= '<div class="padding-h10 border-bottom chat-lists ' . $class . '"><a href="javascript:;"  data-toggle-class="toggle" data-target=".msg-private" ajax-target="#user_private_window" data-ajax="' . $ajax_url . '" ajax-replace="true">' . $list . '</a></div>';
        }
        //翻页按钮
        $ajax_url    = zib_get_current_url();
        $count_all   = self::get_chat_users_count($user_id);
        $next_paging = zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url, 'chat-pag text-center', 'chat-next', '', 'paged', 'no');
        if ($next_paging && 1 == $paged) {
            $lists .= '<div class="post_ajax_loader" style="display: none;"><div class="padding-h10 border-bottom"><div class="flex msg-list"><div class="avatar-img placeholder mr10"></div><div class="flex1"><dt class="placeholder" style="height: 18px;width: 45%;"></dt><dd style="height: 14px;" class="placeholder mt6"></dd></div></div></div><div class="padding-h10 border-bottom"><div class="flex msg-list"><div class="avatar-img placeholder mr10"></div><div class="flex1"><dt class="placeholder" style="height: 18px;width: 45%;"></dt><dd style="height: 14px;" class="placeholder mt6"></dd></div></div></div><div class="padding-h10 border-bottom"><div class="flex msg-list"><div class="avatar-img placeholder mr10"></div><div class="flex1"><dt class="placeholder" style="height: 18px;width: 45%;"></dt><dd style="height: 14px;" class="placeholder mt6"></dd></div></div></div></div>';
        }
        $lists .= $next_paging;

        return $lists;
    }

    /**
     * @description: 新增消息
     * @param arrar $values 数组
     * @return {*}
     */
    public static function add($values)
    {
        if (!_pz('message_s', true) || !_pz('private_s', true)) {
            return false;
        }

        $defaults = array(
            'send_user'    => '',
            'receive_user' => '',
            'type'         => 'private',
            'content'      => '',
            'parent'       => '',
            'status'       => '',
            'meta'         => '',
            'other'        => '',
        );
        $values = wp_parse_args((array) $values, $defaults);
        if (!$values['send_user']) {
            $values['send_user'] = get_current_user_id();
        }

        //没有登录、没有收件人、或者没有内容则结束
        if (!$values['content'] || !$values['receive_user'] || !$values['send_user']) {
            return false;
        }

        //创建标题
        $send_user_neme  = get_userdata($values['send_user'])->display_name ?? $values['send_user'];
        $values['title'] = '收到来自用户[' . $send_user_neme . ']的私信';

        return ZibMsg::add($values);
    }

    /**
     * @description: 获取私信消息列表
     * @param int $send_user 发件人ID
     * @param int $receive_user 收件人ID
     * @param int $offset 跳过的数量
     * @param int $ice_perpage 加载的数量
     * @param string  $orderby 排序依据
     * @param string  $decs 排序先后 'DESC'降序 | 'ASC'降序
     * @return object 返回数据库对象
     */
    public static function get_msg($send_user = '', $receive_user = '', $offset = 0, $ice_perpage = 10, $orderby = 'id', $decs = 'DESC')
    {
        $receive_user = (int) $receive_user;

        if (!$send_user) {
            $send_user = get_current_user_id();
        }
        $send_user = (int) $send_user; //只允许整数，以避免SQL注入

        if (!$receive_user || !$send_user) {
            return false;
        }

        $db = self::get_msg_db($send_user, $receive_user);
        $db->order($orderby, $decs);
        if ('all' !== $ice_perpage) {
            $db->limit($ice_perpage, $offset);
        }

        $result = $db->select()->result();
        return $result;
    }

    /**
     * @description: 获取私信消息数据库对象
     * @param int $send_user 发件人ID
     * @param int $receive_user 收件人ID
     * @return object 返回数据库对象
     */
    public static function get_msg_db($send_user, $receive_user)
    {

        $db    = zibDB::name(ZibMsg::$table_name);
        $where = [
            ['status', '=', 0],
            ['type', '=', 'private'],
            [
                'relation' => 'or',
                [['send_user', '=', $send_user], ['receive_user', '=', $receive_user]],
                [['send_user', '=', $receive_user], ['receive_user', '=', $send_user]],
            ],
        ];

        return $db->where($where);
    }

    /**
     * @description: 构建私信消息列表
     * @param {*}
     * @return {*}
     */
    public static function get_msg_lists_count($send_user = '', $receive_user = '')
    {
        $receive_user = (int) $receive_user;
        if (!$send_user) {
            $send_user = get_current_user_id();
        }
        $send_user = (int) $send_user;

        if (!$receive_user || !$send_user) {
            return false;
        }

        $db = self::get_msg_db($send_user, $receive_user);
        return $db->count();
    }

    /**
     * @description: 构建私信消息列表
     * @param {*}
     * @return {*}
     */
    public static function get_msg_lists($send_user = '', $receive_user = '', $class = 'private-item', $paged = 1, $ice_perpage = 10, $orderby = 'id', $decs = 'DESC')
    {

        $receive_user = (int) $receive_user;
        if (!$receive_user) {
            return false;
        }

        if (!$send_user) {
            $send_user = get_current_user_id();
        }

        //准备翻页参数
        $paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : $paged;
        $ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : $ice_perpage;
        $offset      = $ice_perpage * ($paged - 1);
        //全部消息
        $all_msg = self::get_msg($send_user, $receive_user, $offset, $ice_perpage, $orderby, $decs);

        //将私信设置为已读
        $where = array(
            'send_user'      => $receive_user,
            'receive_user'   => $send_user,
            'status'         => 0,
            'no_readed_user' => $send_user,
            'type'           => 'private',
        );
        ZibMsg::user_all_readed($where, $send_user);

        //数组反序
        $all_msg = array_reverse((array) $all_msg);

        $lists          = '';
        $ajax_query_arg = array(
            'action'       => 'user_private_lists',
            'receive_user' => $receive_user,
            '_wpnonce'     => wp_create_nonce('user_private_lists'), //安全验证
        );

        $ajax_url  = add_query_arg($ajax_query_arg, admin_url('admin-ajax.php'));
        $count_all = self::get_msg_lists_count($send_user, $receive_user);
        $lists .= zib_get_ajax_next_paginate($count_all, $paged, $ice_perpage, $ajax_url, 'private-pag px12 text-center', 'private-next', '<i class="fa fa-angle-up mr10"></i>加载历史消息');

        $previous_time = '0000-00-00 00:00:00';
        foreach ($all_msg as $msg) {
            $lists .= '<div class="' . $class . '">';
            $msg = (array) $msg;
            if (floor((strtotime($msg['create_time']) - strtotime($previous_time))) > 300) {
                //如果消息的时间差大于5分钟，则显示时间
                $lists .= '<div class="px12 muted-3-color text-center">' . date('Y-m-d H:i', strtotime($msg['create_time'])) . '</div>';
            }
            //消息内容模块
            $lists .= self::get_msg_box($msg);
            $lists .= '</div>';

            $previous_time = $msg['create_time'];
        }
        return $lists;
    }

    /**
     * @description: 构建列表
     * @param {*}
     * @return {*}
     */
    public static function get_msg_box($msg = array(), $user_id = '', $class = '')
    {
        $msg = (array) $msg;
        if (!$msg) {
            return false;
        }

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $lists        = '';
        $readed_badge = '';
        $msg          = (array) $msg;
        $con          = self::get_content($msg);
        if ($msg['send_user'] == $user_id) {
            //如果我是发件人
            $class_direction = ' right';
            $readed_badge    = self::is_readed($msg) ? '' : '<span class="readed-badge">未读</span>';
        } else {
            //我是接收人
            $class_direction = ' left';
        }
        $con    = '<span class="private-content main-bg comt-main">' . $con . $readed_badge . '</span>';
        $avatar = zib_get_data_avatar((int) $msg['send_user']);
        $avatar = '<div class="avatar-img">' . $avatar . '</div>';

        $lists .= '<div class="clearfix private-list ' . $class . $class_direction . '">';
        $lists .= $avatar . $con;
        $lists .= '</div>';

        return $lists;
    }

    /**
     * @description: 判断当前消息是否已读
     * @param {*} $msg
     * @return {*}
     */
    public static function is_readed($msg)
    {
        $msg = (array) $msg;

        if (!isset($msg['readed_user'])) {
            return false;
        }

        return (strstr($msg['readed_user'], '[' . $msg['receive_user'] . ']'));
    }

    /**
     * @description: 消息内容格式化
     * @param {*}
     * @return {*}
     */
    public static function get_content($msg, $type = '')
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = (array) $msg;
            $con = isset($msg['content']) ? $msg['content'] : '';
        } else {
            $con = $msg;
        }

        if (!$con) {
            return '';
        }

        $con = strip_tags($con);
        return ZibMsg::get_content($con, $type);
    }

    /**
     * @description: 消息窗口 发送私信
     * @param {*}
     * @return {*}
     */
    public static function get_input($send_user = '', $receive_user = '', $class = '')
    {
        $receive_user = (int) $receive_user;
        if (!$receive_user) {
            return false;
        }

        if (!$send_user) {
            $send_user = get_current_user_id();
        }

        $option = _pz('private_option', array());

        $input       = '';
        $placeholder = isset($option['placeholder']) ? $option['placeholder'] : '';
        $input .= '<div class="mb6"><textarea placeholder="' . esc_attr($placeholder) . '" class="form-control grin" name="receive" id="receive" rows="2" tabindex="1"></textarea></div>';

        $but = '';

        //插入表情
        if (!empty($option['smilie_s'])) {
            $but .= zib_get_input_expand_but('smilie');
        }
        //插入代码
        if (!empty($option['code_s'])) {
            $but .= zib_get_input_expand_but('code');
        }
        //插入图片
        if (!empty($option['image_s'])) {
            $but .= zib_get_input_expand_but('image', !empty($option['upload_img']), 'private');
        }

        if (!empty($option['quick_s'])) {
            $quick_often = isset($option['quick_often']) ? $option['quick_often'] : array();
            $but .= zib_get_input_expand_but('quick', $quick_often, 'private');
        }

        //提交
        $submit_text = isset($option['submit_text']) ? $option['submit_text'] : '<i class="fa fa-send-o"></i>发送';
        $but .= '<div class="pull-right"><button class="but c-blue send-private pw-1em input-expand-submit" name="submit" id="submit" tabindex="2">' . $submit_text . '</button></div>';

        //参数
        $but .= '<input type="hidden" name="send_user" value="' . $send_user . '">';
        $but .= '<input type="hidden" name="receive_user" value="' . $receive_user . '">';
        $but .= '<input type="hidden" name="action" value="send_private">';

        //安全效验
        $but .= wp_nonce_field('send_private', 'send_private_nonce', false, false);

        $html = '<form class="from-private">' . $input . $but . '</form>';
        return $html;
    }

    /**
     * @description: 消息窗口
     * @param {*}
     * @return {*}
     */
    public static function get_window($send_user = '', $receive_user = '')
    {
        $receive_user = (int) $receive_user;
        if (!$receive_user) {
            return false;
        }

        if (!$send_user) {
            $send_user = get_current_user_id();
        }

        //收件人信息
        $receive_data = get_userdata($receive_user);
        //消息列表
        $msg_lists = self::get_msg_lists($send_user, $receive_user);
        if (!$msg_lists) {
            $msg_lists = '';
        }

        //返回按钮
        $back_but = '<div class="abs-left visible-xs-block"><a href="javascript:;" class="muted-color" data-toggle-class="toggle" data-target=".msg-private"><i class="fa fa-angle-left"></i> 返回列表</a></div>';
        //设置按钮
        $set_but = '';
        $set_but .= '<div class="dropdown pull-right">';
        $set_but .= '<a href="javascript:;" class="muted-color padding-6" data-toggle="dropdown">';
        $set_but .= zib_get_svg('menu_2');
        $set_but .= '</a>';
        $set_but .= '<ul class="dropdown-menu">';

        $ajax_query_arg = array(
            'action'       => 'private_blacklist',
            'user_id'      => $send_user,
            'receive_user' => $receive_user,
            '_wpnonce'     => wp_create_nonce('private_set'), //安全验证
        );

        //加入黑名单
        $is_blacklist       = self::is_blacklist($receive_user, $send_user);
        $blacklist_but_text = $is_blacklist ? '移除黑名单' : '加入黑名单';
        $blacklist_url      = add_query_arg($ajax_query_arg, admin_url('admin-ajax.php'));
        $set_but .= '<li><a class="ajax-blacklist" href="javascript:;" ajax-href="' . $blacklist_url . '"><text>' . $blacklist_but_text . '</text></a></li>';

        //清空聊天记录
        /**
        $ajax_query_arg['action'] = 'clear_user_private';
        $clear_url = add_query_arg($ajax_query_arg, admin_url('admin-ajax.php'));

        $set_but .= '<li><a class="ajax-clear-msg" href="javascript:;" ajax-href="' . $clear_url . '">清空聊天记录</a></li>';
        // $set_but .= '<li role="separator" class="divider"></li>';
        // $set_but .= '<li><a href="#">投诉</a></li>';
         */

        $set_but .= '<li><a href="' . zib_get_user_home_url($receive_user) . '">查看此用户</a></li>';
        //举报或者封号
        $user_ban_link = zib_get_edit_user_ban_link($receive_user);
        if (!$user_ban_link && _pz('user_report_s', true)) {
            $user_ban_link = zib_get_report_link($receive_user, '', '', '举报此用户');
        }
        $set_but .= $user_ban_link ? '<li>' . $user_ban_link . '</li>' : '';

        $set_but .= '</ul>';
        $set_but .= '</div>';

        //用户
        $avatar    = zib_get_avatar_box((int) $receive_user, 'avatar-img mr6', false, true);
        $user_name = $avatar;

        $user_name .= $is_blacklist ? '<span class="mr3 c-red" data-toggle="tooltip" title="黑名单用户"><i class="fa fa-ban"></i></span>' : '';
        $user_name .= $receive_data->display_name ?? '未知用户';

        //构建头部
        $header = '';
        $header .= '<div class="private-window-header mb10 relative">';

        $header .= $back_but;
        $header .= $set_but;

        $header .= '<div class="text-center ml20">' . $user_name . '</div>';

        $header .= '</div>';

        //构建内容
        $con = '';
        $con .= '<div class="private-window-content mb10 scroll-y mini-scrollbar imgbox-container">';
        $con .= $msg_lists;
        $con .= '</div>';

        //构建输入框
        $input = '';
        $input .= '<div class="private-window-footer">';
        $input .= self::get_input($send_user, $receive_user);
        $input .= '';
        $input .= '';
        $input .= '</div>';

        $html = '';
        $html .= '<div class="private-window">';
        $html .= $header;
        $html .= $con;
        $html .= $input;
        $html .= '</div>';
        return $html;
    }

    /**
     * @description: 判断是否在黑名单|$judgment_id 是否是$user_id的黑名单
     * @param int $judgment_id 待判断的ID
     * @param int $user_id 用户id
     * @return {*}
     */
    public static function is_blacklist($judgment_id, $user_id = '')
    {
        $user_id = $user_id ? $user_id : get_current_user_id();

        $private_blacklist = get_user_meta($user_id, 'private_blacklist', true);
        $private_blacklist = $private_blacklist ? $private_blacklist : array();

        return in_array($judgment_id, $private_blacklist);
    }

    /**
     * @description: 获取私信按钮
     * @param {*}
     * @return {*}
     */
    public static function get_but($receive_user = '', $text = '私信', $class = '', $new_modal = false)
    {
        $receive_user    = (int) $receive_user;
        $current_user_id = get_current_user_id();

        if (!$receive_user || $current_user_id == $receive_user || !_pz('private_s', true) || !_pz('message_s', true) || zib_is_close_sign()) {
            return '';
        }

        if ($current_user_id) {

            //小黑屋禁封判断
            if (_pz('user_ban_s', true) && zib_user_is_ban($current_user_id)) {
                return;
            }

            $args = array(
                'tag'           => 'a',
                'class'         => $class,
                'data_class'    => 'full-sm',
                'text'          => $text,
                'mobile_bottom' => true,
                'query_arg'     => array('action' => 'private_window_modal', 'receive_user' => $receive_user),
                'height'        => 550,
                'new'           => $new_modal,
            );
            $but = zib_get_refresh_modal_link($args);
        } else {
            $but = '<a class="signin-loader ' . $class . '" href="javascript:;">' . $text . '</a>';
        }
        return $but;
    }
}
