<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime : 2026-03-16 13:49:41
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户认证相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取是否是认证用户
function zib_is_user_auth($id = 0)
{
    if (!$id) {
        $id = get_current_user_id();
    }

    return get_user_meta($id, 'auth', true);
}

//用户获取认证图标
function zib_get_user_auth_badge($id = 0, $class = '')
{
    if (!$id || !_pz('user_auth_s', true)) {
        return;
    }

    $auth = get_user_meta($id, 'auth', true);
    if (!$auth) {
        return;
    }

    $auth_name = zib_get_user_auth_name($id);
    $icon      = zib_get_svg('user-auth');

    return '<icon data-toggle="tooltip" title="' . esc_attr($auth_name) . '" class="user-auth-icon ' . $class . '">' . $icon . '</icon>';
}

//为用户中心添加认证标识
function zib_filter_author_header_desc_auth($desc, $user_id)
{
    return zib_get_user_auth_info_link($user_id, 'but') . $desc;
}

if (_pz('user_auth_s', true)) {
    add_filter('author_header_identity', 'zib_filter_author_header_desc_auth', 10, 3);
}

//获取用户认证的名称
function zib_get_user_auth_name($id = 0)
{
    if (!$id || !_pz('user_auth_s', true)) {
        return;
    }

    $auth_info = zib_get_user_meta($id, 'auth_info', true);
    if (!$auth_info) {
        return;
    }

    return isset($auth_info['name']) ? $auth_info['name'] : '官方认证';
}

//获取用户认证的信息链接
function zib_get_user_auth_info_link($user_id = 0, $class = '')
{
    if (!$user_id || !_pz('user_auth_s', true)) {
        return;
    }

    $icon = zib_get_user_auth_badge($user_id, 'mr3');
    if (!$icon) {
        return;
    }

    $auth_name = zib_get_user_auth_name($user_id);

    $class = $class ? 'user-auth-info ' . $class : 'user-auth-info';

    $url_var = array(
        'action' => 'user_auth_info_modal',
        'id'     => $user_id,
    );

    $args = array(
        'tag'           => 'botton',
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $icon . $auth_name . '<i class="fa fa-angle-right" style="margin: 0 0 0 .3em;"></i>',
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取申请认证的链接
function zib_get_user_auth_apply_link($class = '', $text = '申请认证')
{
    if (!_pz('user_auth_s', true)) {
        return;
    }

    $class   = 'user-auth-apply ' . $class;
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '<a class="signin-loader ' . $class . '" href="javascript:;">' . $text . '</a>';
    }

    $icon = zib_get_user_auth_badge($user_id, 'mr6');
    if ($icon) {
        return;
    }

    $url_var = array(
        'action' => 'user_auth_apply_modal',
    );

    $args = array(
        'tag'           => 'botton',
        'class'         => $class,
        'height'        => 320,
        'mobile_bottom' => true,
        'text'          => $text,
        'query_arg'     => $url_var,
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取查看认证信息的模态框
function zib_get_user_auth_info_modal($user_id = 0)
{

    if (!$user_id || !_pz('user_auth_s', true)) {
        return;
    }

    $auth_badge = zib_get_user_auth_badge($user_id, 'mr6');
    if (!$auth_badge) {
        return zib_ajax_notice_modal('warning', '该用户暂未认证');
    }

    $icon = zib_get_svg('user-auth');

    $header    = zib_get_modal_colorful_header('c-blue', $icon, '官方认证');
    $auth_info = zib_get_user_meta($user_id, 'auth_info', true);

    $name = isset($auth_info['name']) ? $auth_info['name'] : '官方认证';

    $desc = isset($auth_info['desc']) ? '<div class="muted-2-color mt10">' . $auth_info['desc'] . '</div>' : '';
    $time = isset($auth_info['time']) ? '<div class="muted-2-color mt10">认证时间：' . $auth_info['time'] . '</div>' : '';

    $apply = '';
    if (get_current_user_id() != $user_id) {
        $apply = zib_get_user_auth_apply_link('but c-blue padding-lg btn-block mt20', '我也要申请认证');
    }

    $html = $header;
    $html .= '<div class="mb10">';
    $html .= '<div class="">' . $icon . '<b class="ml3">' . $name . '</b></div>';
    $html .= $desc;
    $html .= $time;
    $html .= '</div>';
    $html .= $apply;

    return $html;
}

//获取申请认证信息的模态框
function zib_get_user_auth_apply_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id || !_pz('user_auth_s', true)) {
        return;
    }

    $icon   = zib_get_svg('user-auth');
    $header = zib_get_modal_colorful_header('c-blue', $icon, '申请身份认证');

    $con = zib_get_user_auth_apply_from();

    $html = $header;
    $html .= $con;

    return $html;
}

//申请认证
function zib_get_user_auth_apply_from($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id || !_pz('user_auth_s', true)) {
        return;
    }

    $icon = zib_get_svg('user-auth');

    //申请记录
    $apply_record = zib_get_user_auth_apply_ing($user_id);

    $html = '';
    if ($apply_record) {
        //如果有申请记录，则显示记录
        $mate = $apply_record->meta;

        $name = isset($mate['name']) ? $mate['name'] : '';
        $desc = isset($mate['desc']) ? $mate['desc'] : '';
        $msg  = isset($mate['msg']) ? $mate['msg'] : '';
        $time = isset($mate['time']) ? $mate['time'] : '';
        $img  = isset($mate['img']) ? $mate['img'] : '';

        $desc = $desc ? '<div class="muted-2-color mt10">' . $desc . '</div>' : '';
        $time = $time ? '<div class="muted-2-color mt10">申请时间：' . $time . '</div>' : '';
        $msg  = $msg ? '<div class="muted-2-color mt10">申请说明：' . $msg . '</div>' : '';

        $img_html = '';
        if ($img && is_array($img)) {
            foreach ($img as $img_url) {
                $img_html .= '<div class="preview-item"><img alt="身份认证申请" src="' . $img_url . '"></div>';
            }
        }
        $img_html = $img_html ? '<div class="mt20">' . $img_html . '</div>' : '';

        $html .= '<div class="c-red mb10">您的申请正在审核中，请耐心等待</div>';
        $html .= '<div class="">' . $icon . '<b class="ml3">' . $name . '</b></div>';
        $html .= $desc;
        $html .= $msg;
        $html .= $time;
        $html .= $img_html;
        $html .= '<button class="but c-blue padding-lg btn-block mt20" data-dismiss="modal">确认</button>';
        return $html;
    } else {
        //没有申请记录，则开始新的申请

        $apply_option = _pz('apply_option');

        if (empty($apply_option['s'])) {
            //如果未开启申请功能，则显示禁止申请的提醒
            $disable_desc = !empty($apply_option['disable_desc']) ? '<div class="muted-color mt20">' . $apply_option['disable_desc'] . '</div>' : '';
            $html .= '<div class="c-red"><i class="fa mr6 fa-info-circle"></i>抱歉！暂时不能申请身份认证</div>';
            $html .= $disable_desc;
            $html .= '<button class="but padding-lg btn-block mt20" data-dismiss="modal">取消</button>';
            return $html;
        }

        if (_pz('user_level_s', true) && !empty($apply_option['limit_level']) && $apply_option['limit_level'] > zib_get_user_level($user_id)) {
            //如果未达到申请要求
            $limit_level  = zib_get_level_badge($apply_option['limit_level']);
            $disable_desc = '<div class="muted-color mt20">由于您的等级过低，暂时无法申请身份认证，请您升级到<span class="em12">' . $limit_level . '</span>之后再来申请</div>';
            $html .= '<div class="c-red"><i class="fa mr6 fa-info-circle"></i>抱歉！您暂时不能申请身份认证</div>';
            $html .= $disable_desc;
            $html .= '<button class="but padding-lg btn-block mt20" data-dismiss="modal">取消</button>';
            return $html;
        }

        $desc    = !empty($apply_option['desc']) ? '<div class="muted-color mb20">' . $apply_option['desc'] . '</div>' : '';
        $img_val = '';
        $img_val = $img_val ? $img_val : ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg';

        $img_html = '<div class="form-upload mb10">';
        $img_html .= '<div class="em09 muted-2-color mb6">证明材料</div>';
        $img_html .= '<div class="preview">'; //正方形
        $img_html .= '<div class="add"></div>';
        $img_html .= '</div>';
        $img_html .= '<input class="hide" type="file" zibupload="image_upload" multiple="multiple" multiple_max="3" accept="image/gif,image/jpeg,image/jpg,image/png" name="image" action="image_upload" multiple="true">';
        $img_html .= '</div>';

        $con = '';
        $con .= '<div class="mb20">';
        $con .= '<div class="em09 muted-2-color mb6">认证身份</div>';
        $con .= '<input type="text" class="form-control" name="name" tabindex="1" value="" placeholder="请输入身份名称">';
        $con .= '</div>';

        $con .= '<div class="mb20">';
        $con .= '<div class="em09 muted-2-color mb6">身份简介</div>';
        $con .= '<textarea class="form-control" name="desc" tabindex="2" placeholder="请输入申请的身份简介" rows="2"></textarea>';
        $con .= '</div>';

        $con .= '<div class="mb20">';
        $con .= '<div class="em09 muted-2-color mb6">申请说明</div>';
        $con .= '<textarea class="form-control" name="msg" tabindex="2" placeholder="请输入申请缘由及说明" rows="2"></textarea>';
        $con .= '</div>';
        $con .= $img_html;

        $hidden_html = wp_nonce_field('user_auth_apply', '_wpnonce', false, false);
        $hidden_html .= '<input type="hidden" name="action" value="user_auth_apply">';
        $hidden_html .= '<input type="hidden" name="user_id" value="' . $user_id . '">';

        $footer = '<div class="but-average modal-buts"><button type="button" data-dismiss="modal" class="but">取消</button>';
        $footer .= '<button class="but c-blue" zibupload="submit" zibupload-nomust="true"><i class="fa fa-check" aria-hidden="true"></i>确认提交</button>';
        $footer .= $hidden_html;
        $footer .= '</div>';

        $html = '<form class="auth-apply-from form-upload">';
        $html .= '<div class="mb20 mini-scrollbar scroll-y max-vh5" style="padding: 0 3px;">' . $desc . $con . '</div>';
        $html .= $footer;
        $html .= '</form>';
        return $html;
    }
}

function zib_get_user_auth_apply_process_url($user_id)
{
    $url = admin_url('admin.php?page=user_auth');

    return add_query_arg(array('send_user' => $user_id, 'status' => 0), $url);
}

function zib_get_user_auth_apply_ing($user_id)
{

    $msg_get_args = array(
        'send_user' => $user_id,
        'type'      => 'auth_apply',
        'status'    => 0,
    );
    return ZibMsg::get_row($msg_get_args);
}

//设置用户认证
function zib_add_user_auth($user_id, $args)
{
    $defaults = array(
        'name' => '',
        'desc' => '',
        'time' => current_time('Y-m-d H:i'),
    );
    $args = wp_parse_args($args, $defaults);

    $auth_lv = 1;
    update_user_meta($user_id, 'auth', $auth_lv);
    zib_update_user_meta($user_id, 'auth_info', $args);

    update_meta_cache('user', array($user_id));
}

//处理申请
function zib_user_auth_apply_process($args)
{
    $defaults = array(
        'id'      => '',
        'user_id' => 0,
        'status'  => '',
        'msg'     => '',
        'name'    => '',
        'desc'    => '',
    );
    $args = wp_parse_args($args, $defaults);

    if (!$args['id'] || !$args['user_id'] || !$args['name']) {
        return false;
    }

    //更新当前消息的状态
    $up_status = ZibMsg::set_status($args['id'], $args['status']);
    //保存管理员留言
    ZibMsg::set_meta($args['id'], 'admin_message', $args['msg']);

    //准备参数
    $msg_db = ZibMsg::get_row(array('id' => $args['id']));

    $status_text = '已批准';
    if ('1' == $args['status']) {
        zib_add_user_auth($args['user_id'], array(
            'name' => $args['name'],
            'desc' => $args['desc'],
        ));
        $msg_title   = '身份认证通过，恭喜您成为认证用户';
        $status_text = '已通过';
    }
    if (2 == $args['status']) {
        $status_text = '被拒绝';
        $msg_title   = '您的身份认证申请被拒绝';
    }

    //准备新消息
    $user_display_name = get_userdata($args['user_id'])->display_name;
    $msg_con           = '';
    $msg_con .= '您好！' . $user_display_name . '<br />您于' . date('Y年m月d日 H:i', strtotime($msg_db->create_time)) . ' 发起的身份认证申请' . $status_text . '<br>';

    $msg_con .= '认证身份：' . $args['name'] . '<br>';
    $msg_con .= '身份简介：' . $args['desc'] . '<br>';
    $msg_con .= '申请时间：' . $msg_db->create_time . '<br>';
    $msg_con .= '处理时间：' . $msg_db->modified_time . '<br>';

    $msg_con .= '<br>';
    $msg_con .= $args['msg'] . '<br>';
    $msg_con .= '如有疑问请与客服联系';

    $msg_arge = array(
        'send_user'    => 'admin',
        'receive_user' => $args['user_id'],
        'type'         => 'auth_apply_reply',
        'title'        => $msg_title,
        'content'      => $msg_con,
        'parent'       => $args['id'],
    );
    //创建新消息
    if (zib_msg_is_allow_receive($msg_db->send_user, 'auth_apply_reply')) {
        ZibMsg::add($msg_arge);
    }

    //发送微信模板消息
    $wechat_template_data = array(
        'status' => $status_text,
        'name'   => $args['name'],
        'desc'   => $args['desc'],
        'time'   => $msg_db->modified_time,
    );
    zib_wechat_template_send($args['user_id'], 'auth_apply_process', $wechat_template_data, zib_get_user_center_url('auth'));

    do_action('user_auth_apply_process_newmsg', $msg_arge);
    return $up_status;
}

//处理申请后邮件发送
if (_pz('email_auth_apply_process', true)) {
    add_action('user_auth_apply_process_newmsg', 'zib_mail_user_auth_apply_process');
    function zib_mail_user_auth_apply_process($data)
    {
        $user_id = $data['receive_user'];
        $udata   = get_userdata($user_id);
        /**判断邮箱状态 */
        $user_email = $udata->user_email;
        if (!is_email($user_email) || stristr($user_email, '@no')) {
            return false;
        }

        /**发送邮件 */
        @wp_mail($udata->user_email, $data['title'], $data['content']);
    }
}

//用户中心显示认证页面内容
function zib_main_user_tab_content_auth()
{
    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;

    if (!$user_id || !_pz('user_auth_s', true)) {
        return;
    }

    $icon = zib_get_svg('user-auth');

    $apply_option = _pz('apply_option');

    if (zib_is_user_auth($user_id)) {
        //已经认证
        $auth_info = zib_get_user_meta($user_id, 'auth_info', true);
        $name      = isset($auth_info['name']) ? $auth_info['name'] : '官方认证';

        $desc = isset($auth_info['desc']) ? '<div class="muted-2-color mt10">' . $auth_info['desc'] . '</div>' : '';
        $time = isset($auth_info['time']) ? '<div class="muted-2-color mt10">认证时间：' . $auth_info['time'] . '</div>' : '';

        $con = '<div class="text-left">';
        $con .= '<div class="em12">' . $icon . '<b class="ml6">' . $name . '</b></div>';
        $con .= $desc;
        $con .= $time;
        $con .= '</div>';
    } else {
        $apply_desc = !empty($apply_option['desc']) ? '<div class="muted-color mb20">' . $apply_option['desc'] . '</div>' : '';
        $desc       = !empty($apply_option['desc']) ? '<div class="muted-color mb20">' . $apply_option['desc'] . '</div>' : '';
        $desc .= zib_get_user_auth_apply_link('but jb-blue padding-lg btn-block mt20', '申请认证');
        if (empty($apply_option['s'])) {
            //如果未开启申请功能，则显示禁止申请的提醒
            $disable_desc = !empty($apply_option['disable_desc']) ? '<div class="muted-color mt20">' . $apply_option['disable_desc'] . '</div>' : '';

            $desc = '<div class="c-red"><i class="fa mr6 fa-info-circle"></i>抱歉！暂时不能申请身份认证</div>';
            $desc .= $disable_desc;
        }
        if (_pz('user_level_s', true) && !empty($apply_option['limit_level']) && $apply_option['limit_level'] > zib_get_user_level($user_id)) {
            //如果未达到申请要求
            $limit_level  = zib_get_level_badge($apply_option['limit_level']);
            $disable_desc = '<div class="muted-color mt20">由于您的等级过低，暂时无法申请身份认证，请您升级到<span class="em12">' . $limit_level . '</span>之后再来申请</div>';
            $desc         = '<div class="c-red"><i class="fa mr6 fa-info-circle"></i>抱歉！您暂时不能申请身份认证</div>';
            $desc .= $disable_desc;
        }

        $con = '<div class="text-left">';
        $con .= '<div class="em12 mb10">' . $icon . '<b class="ml6">申请认证</b></div>';
        $con .= $desc;
        $con .= '</div>';
    }

    $html = '<div style="padding: 40px 20px;" class="colorful-bg c-blue flex jc zib-widget"><div class="colorful-make"></div>';
    $html .= '<div class="text-center">';
    $html .= '<div class="em4x">' . $icon . '</div><div class="mt10 em14 padding-w10 font-bold mb40">官方认证</div>';
    $html .= $con;
    $html .= '</div>';
    $html .= '</div>';

    return zib_get_ajax_ajaxpager_one_centent($html);
}

add_filter('main_user_tab_content_auth', 'zib_main_user_tab_content_auth');
