<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-12-21 22:54:02
 * @LastEditTime : 2026-05-05 11:35:17
 */

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

$this_url = esc_url(admin_url('users.php?page=invit_code'));
$tab      = !empty($_GET['tab']) ? $_GET['tab'] : '';
$action   = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$s        = !empty($_REQUEST['s']) ? esc_sql($_REQUEST['s']) : '';

$invit_code = (array) _pz('invit_code_s'); //邀请码注册
if (!$invit_code || $invit_code === 'close') {
    echo '<div class="notice notice-warning"><p style="color:#fb590a;">注意：邀请码功能暂未启用。您可以在<a href="' . zib_get_admin_csf_url('用户互动/注册登录') . '">用户互动/注册登录</a>中启用此功能</p></div>';
}

if ($action) {
    switch ($action) {
        case 'add':
            $add_type = !empty($_REQUEST['add_type']) ? $_REQUEST['add_type'] : 'auto';
            if ($add_type === 'import') {
                $import_data     = !empty($_REQUEST['import_data']) ? $_REQUEST['import_data'] : '';
                $import_division = !empty($_REQUEST['import_division']) ? $_REQUEST['import_division'] : ' ';

                if (!$import_data) {
                    zib_admin_page_notice('错误！', '请粘贴您需要导入的数据', 'error');
                    break;
                }

                $import_data_array = explode("\r\n", $import_data);

                if (!$import_data_array) {
                    zib_admin_page_notice('错误！', '请输入需要生成的数量', 'error');
                    break;
                }

                $success_i = 0;
                $error_i   = 0;
                foreach ($import_data_array as $v) {
                    $v = explode($import_division, $v);
                    if (!empty($v[0])) {
                        $success_i++;
                        $_reward = !empty($v[1]) ? $v[1] : '';
                        ZibCardPass::add(array(
                            'password' => $v[0],
                            'type'     => 'invit_code',
                            'status'   => '0', //正常
                            'meta'     => array('reward' => wp_parse_args($_reward)),
                            'other'    => !empty($v[2]) ? $v[2] : '',
                        ));
                    } else {
                        $error_i++;
                    }
                }

                if ($success_i) {
                    zib_admin_page_notice('导入完成', '成功导入' . $success_i . '个充值卡' . ($error_i ? '，' . $error_i . '个导入失败' : ''));
                    break;
                } else {
                    zib_admin_page_notice('导入失败', '数据格式错误', 'error');
                    break;
                }

            } else {
                $auto_num = !empty($_REQUEST['auto_num']) ? (int) $_REQUEST['auto_num'] : 0;

                if (!$auto_num) {
                    zib_admin_page_notice('错误！', '请输入需要生成的数量', 'error');
                    break;
                }

                //生成充值卡
                $rand_password = 8;
                if (!empty($_REQUEST['auto_top_s'])) {
                    $rand_password = !empty($_REQUEST['auto_rand_password_limit']) ? (int) $_REQUEST['auto_rand_password_limit'] : 8;
                }
                $remarks = !empty($_REQUEST['auto_remarks']) ? $_REQUEST['auto_remarks'] : '';
                $reward  = !empty($_REQUEST['auto_reward']) ? $_REQUEST['auto_reward'] : '';

                zib_generate_invit_code($auto_num, $rand_password, $reward, $remarks);

                zib_admin_page_notice('完成！', '已自动生成' . $auto_num . '个邀请码');
                break;
            }

            zib_admin_page_notice('错误！', '参数传入错误', 'error');

            break;

        case 'delete':
            $delete_ids = !empty($_REQUEST['action_id']) ? $_REQUEST['action_id'] : 0;
            if (!$delete_ids) {
                zib_admin_page_notice('错误！', '未选择需要删除的内容', 'error');
                break;
            }
            $delete_i = ZibCardPass::delete(array(
                'id'   => $delete_ids,
                'type' => 'invit_code',
            ));

            zib_admin_page_notice('删除完成', '已删除' . $delete_i . '个邀请码');
            break;
    }
}

function zib_admin_page_notice($title = '', $msg = '', $type = 'success')
{
    $html = '';
    $html .= $title ? '<h3>' . $title . '</h3>' : '';
    $html .= $msg ? '<p>' . $msg . '</p>' : '';

    if ($html) {
        echo '<div class="notice notice-' . $type . '">' . $html . '</div>';
    }
}

$page_title = '邀请码管理';
$head_but   = '<a href="' . add_query_arg('tab', 'add', $this_url) . '" class="page-title-action">添加邀请码</a>';
$sub_but    = array();

//准备查询参数
$orderby     = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'modified_time';
$paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
$ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : 30;
$desc        = !empty($_REQUEST['desc']) ? $_REQUEST['desc'] : 'DESC';
$offset      = $ice_perpage * ($paged - 1);

$count_all = 0;
$db_data   = false;
$csf_args  = false;
$table     = false;
$pagenavi  = false;
$search    = false;
$page_html = false;

switch ($tab) {

    case 'add':
        $page_title = '添加邀请码';
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">返回列表</a>';

        $csf_fields = array();

        $csf_fields[] = array(
            'content' => '<p><b>在此添加邀请码</b></p>
            <li>如果您已经准备好了邀请码资料，请选择导入的方式添加</li>
            <li>您也可以采用系统生成的方式，自动批量添加邀请码</li>',
            'style'   => 'warning',
            'type'    => 'submessage',
        );

        $csf_fields[] = array(
            'id'      => 'add_type',
            'type'    => 'button_set',
            'title'   => '添加方式',
            'inline'  => true,
            'options' => array(
                'auto'   => '系统自动生成',
                'import' => '导入邀请码', //导入
            ),
            'default' => 'auto',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '生成数量',
            'id'         => 'auto_num',
            'default'    => 20,
            'min'        => 1,
            'max'        => 1000,
            'step'       => 10,
            'unit'       => '个',
            'desc'       => '需要生成多少个邀请码（单次生成数量太多可能会对服务器性能造成影响）',
            'type'       => 'spinner',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '标识',
            'desc'       => '对生成的邀请码做标记标识，方便后期查找管理',
            'id'         => 'auto_remarks',
            'default'    => 'invita_' . current_time('YmdHis'),
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '使用奖励',
            'subtitle'   => '',
            'id'         => 'auto_reward',
            'type'       => 'fieldset',
            'fields'     => CFS_Module::invit_code_reward(),
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '高级选项',
            'id'         => 'auto_top_s',
            'default'    => false,
            'type'       => 'switcher',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type|auto_top_s', '==|!=', 'auto|'),
            'title'      => '自定义位数',
            'class'      => 'compact',
            'id'         => 'auto_rand_password_limit',
            'default'    => 8,
            'min'        => 1,
            'max'        => 50,
            'step'       => 2,
            'unit'       => '位数',
            'desc'       => '自定义自动生成的长度（不能太短，太短可能会出现重复）',
            'type'       => 'spinner',
        );

        //导入邀请码
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'content'    => '<p><b>导入邀请码</b></p>
            <li>一行一个邀请码，单行格式为：<code>邀请码 奖励 标识</code></li>
            <li>邀请码、奖励、标识奖励默认使用空格分割，您可以在下方自定义分割符号，与您的数据对应即可</li>
            <li>奖励格式为：<code>level_integral=10&points=20&balance=30&vip=1&vip_time=5</code>，无需奖励填 <code>无</code></li>
            <li>单次导入数量太多可能会对服务器性能造成影响</li>
            <li>如果使用的是单密码模式，卡号部分随便填什么都行，但不能留空</li>',
            'style'      => 'warning',
            'type'       => 'submessage',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'content'    => '<p><b>数据示例</b></p>
            93fW0XAy 无 没有奖励<br>
            P86NpWki level_integral=100 奖励100经验值<br>
            P86NpWki points=10 奖励10积分<br>
            P86NpWki balance=10 奖励10元余额<br>
            P86NpWki balance=10&points=20 奖励10元余额和20积分<br>
            auF9D2b4 balance=30&vip=1&vip_time=5 奖励余额30和5天1级会员',
            'style'      => 'warning',
            'type'       => 'submessage',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'title'      => '邀请码数据',
            'id'         => 'import_data',
            'default'    => '',
            'attributes' => array(
                'rows'  => 10,
                'style' => 'resize: both;max-width: none;',
            ),
            'sanitize'   => false,
            'type'       => 'textarea',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'id'         => 'import_division', //分割
            'title'      => '自定义分隔符号',
            'subtitle'   => '',
            'class'      => 'mini-input',
            'default'    => ' ',
            'desc'       => '卡号和密码之间分割符号（默认为空格分割）',
            'type'       => 'text',
        );
        $csf_fields[] = array(
            'title'   => ' ',
            'type'    => 'content',
            'content' => '<button type="submit" class="but jb-blue">确认提交</button>',
        );

        $csf_args = array(
            'class'  => 'csf-profile-options',
            'method' => 'post',
            'value'  => array(),
            'hidden' => array(
                array(
                    'name'  => 'action',
                    'value' => 'add',
                ),
            ),
            'fields' => $csf_fields,
        );

        break;

    case 'export':

        $page_title = '导出邀请码';
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">返回列表</a>';

        $csf_fields = array();

        $csf_fields[] = array(
            'id'      => 'status',
            'type'    => 'radio',
            'title'   => '选择状态',
            'inline'  => true,
            'options' => array(
                'all'  => '全部',
                '0'    => '未使用', //导入
                'used' => '已使用', //导入
            ),
            'default' => 'all',
        );
        $csf_fields[] = array(
            'id'      => 'export_format',
            'type'    => 'radio',
            'title'   => '导出格式',
            'inline'  => true,
            'options' => array(
                'text' => '文本文档',
                'xls'  => 'Excel表格', //导入
            ),
            'default' => 'xls',
        );

        $csf_fields[] = array(
            'dependency' => array('export_format', '==', 'text'),
            'id'         => 'text_division', //分割
            'title'      => ' ',
            'subtitle'   => '分隔符号',
            'class'      => 'mini-input',
            'default'    => ' ',
            'desc'       => '卡号和密码之间分割符号（默认为空格分割）',
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'title'   => ' ',
            'type'    => 'content',
            'content' => '<button type="submit" class="but jb-blue">确认提交</button>',
        );

        $csf_args = array(
            'class'  => 'csf-profile-options',
            'method' => 'post',
            'action' => admin_url('admin-ajax.php'),
            'value'  => array(),
            'hidden' => array(
                array(
                    'name'  => 'action',
                    'value' => 'card_pass_export',
                ),
                array(
                    'name'  => 'type',
                    'value' => 'invit_code',
                ),
            ),
            'fields' => $csf_fields,
        );

        break;

    default: //文章类型的
        //默认页面，展示邀请码列表
        $pagenavi  = true;
        $sub_but[] = array(
            'name' => '全部',
            'href' => $this_url,
        );

        $head_but .= '<a href="' . add_query_arg(['tab' => 'export'], $this_url) . '" class="page-title-action">导出邀请码</a>';

        if ($s) {
            $head_but .= '<div><div class="update-nag" style="margin: 10px 0 0;">搜索 “' . $s . '” 的内容 </div></div>';
        } else {
            $sub_but[] = array(
                'name' => '未使用',
                'href' => add_query_arg('status', '0', $this_url),
            );

            $sub_but[] = array(
                'name' => '已使用',
                'href' => add_query_arg('status', 'used', $this_url),
            );
        }

        $where = array(
            'type' => 'invit_code', //余额充值
        );
        if (isset($_GET['status'])) {
            $where['status'] = $_GET['status'];
        }

        $db = ZibDB::name(ZibCardPass::$table_name);
        $db->where($where)->order($orderby, $desc)->page($paged, $ice_perpage);
        if ($s) {
            $db->whereLike(['card', 'password', 'other', 'meta'], $s);
        }

        $db_data   = $db->select()->result();
        $count_all = $db->count();

        $table = '<thead><tr><td style="color: #ff4a4a;text-align: center;">未找到对应内容，或暂无内容</td></tr></thead>';
        if ($db_data) {
            $table    = '';
            $theads[] = array('width' => '6%', 'orderby' => 'password', 'name' => '邀请码');
            $theads[] = array('width' => '10%', 'orderby' => '', 'name' => '使用奖励');
            //  $theads[] = array('width' => '10%', 'orderby' => '', 'name' => '邀请奖励');

            $theads[] = array('width' => '10%', 'orderby' => 'create_time', 'name' => '创建时间');
            $theads[] = array('width' => '10%', 'orderby' => 'modified_time', 'name' => '更新时间');
            $theads[] = array('width' => '6%', 'orderby' => 'status', 'name' => '状态');
            $theads[] = array('width' => '12%', 'orderby' => 'other', 'name' => '标识');

            $thead_th = '<td id="cb" class="manage-column column-cb check-column" style="width: 2%;"><label class="screen-reader-text" for="cb-select-all-1">全选</label><input id="cb-select-all-1" type="checkbox"></td>';
            foreach ($theads as $thead) {
                $orderby = '';
                if ($thead['orderby']) {
                    $orderby_url = add_query_arg('orderby', $thead['orderby']);
                    $orderby .= '<a title="降序" href="' . add_query_arg('desc', 'ASC', $orderby_url) . '"><span class="dashicons dashicons-arrow-up"></span></a>';
                    $orderby .= '<a title="升序" href="' . add_query_arg('desc', 'DESC', $orderby_url) . '"><span class="dashicons dashicons-arrow-down"></span></a>';
                    $orderby = '<span class="orderby-but">' . $orderby . '</span>';
                }
                $thead_th .= '<th class="" width="' . $thead['width'] . '">' . $thead['name'] . $orderby . '</th>';
            }
            $table .= '<thead><tr>' . $thead_th . '</tr></thead>';

            $tbody = '';
            foreach ($db_data as $msg) {
                $meta        = maybe_unserialize($msg->meta);
                $card_price  = zibpay_get_recharge_card_price($msg);
                $status_html = '<span style="color: #3d7ffd;">未使用</span>';

                if ($msg->status === 'used') {
                    $order_link_url = add_query_arg('page', 'zibpay_order_page', admin_url('admin.php')); //前缀
                    $status_html    = '<span style="color: #f93b3b;">已使用</span>';
                    if (!empty($meta['user_id'])) {
                        $status_html .= '<br><a target="_blank" href="' . zib_get_user_home_url($meta['user_id']) . '">' . get_the_author_meta('display_name', $meta['user_id']) . '</a>';
                    }
                }

                //正则匹配“自定义-005_shipped_848”，shipped_后后面的数字也是订单ID
                if (!empty($meta['shipped_order_id']) || preg_match('/shipped_(\d+)/', $msg->other, $matches)) {

                    $shipped_order_id = !empty($meta['shipped_order_id']) ? $meta['shipped_order_id'] : (int) $matches[1];
                    $status_html .= '<br>已售<a style="color: #ca7f5a;" target="_blank" href="' . zibpay_get_admin_shop_url('shipping', 'id=' . $shipped_order_id) . '"> [查看]</a>';
                }

                $other_a = '';
                if ($msg->other) {
                    $other_a = '<a href="' . add_query_arg('other', $msg->other, $this_url) . '">' . $msg->other . '</a>';
                }

                //奖励
                $reward_html          = '无';
                $referrer_reward_html = '无'; //推荐人

                if (!empty($meta['reward'])) {
                    $reward_html = '<div style="font-size: 12px;">' . zib_get_invit_code_reward_text($meta['reward'], '<br>') . '</div>';
                }

                if (!empty($meta['referrer_reward'])) {
                    $referrer_reward_html = '<div style="font-size: 11px;line-height: 1.3;">' . zib_get_invit_code_reward_text($meta['referrer_reward'], '<br>') . '</div>';
                }

                $tbody .= '<tr>';
                $tbody .= '<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-232">选择</label>
                <input id="cb-select-232" type="checkbox" name="action_id[]" value="' . $msg->id . '">
                    </th>';
                $tbody .= "<td><span data-clipboard-text='$msg->password' data-clipboard-tag='邀请码'>$msg->password</span></td>";

                $tbody .= "<td>$reward_html</td>";
                // $tbody .= "<td>$referrer_reward_html</td>";

                $tbody .= "<td>$msg->create_time</td>";
                $tbody .= "<td>$msg->modified_time</td>";
                $tbody .= "<td>$status_html</td>";
                $tbody .= "<td>$other_a</td>";
                $tbody .= '</tr>';
            }
            $table .= '<tbody>' . $tbody . '</tbody>';
        }

        $search = '<form class="form-inline form-order" method="post">
                    <div class="form-group" style="float: right;">
                        <input type="text" class="form-control" name="s" placeholder="搜索邀请码">
                        <button type="submit" class="button">提交</button>
                    </div>
                </form>';

        break;
}

?>


<div class="wrap">
    <style>
        .orderby-but {
            position: relative;
        }

        .orderby-but>a {
            opacity: .4;
            position: absolute;
            transform: translateY(-3px);
            transition: .3s;
        }

        .orderby-but>a+a {
            transform: translateY(6px);
        }

        .orderby-but:hover a {
            opacity: .6;
        }

        .orderby-but>a:hover {
            opacity: 1;
        }
    </style>
    <h1 class="wp-heading-inline"><?php echo $page_title; ?></h1>
    <?php echo $head_but; ?>
    <?php
$but_html = '';
if ($sub_but) {
    foreach ($sub_but as $but) {
        $but_html .= '<li><a href="' . $but['href'] . '">' . $but['name'] . '</a></li> | ';
    }
}

echo '<div class="order-header"><ul class="subsubsub">' . substr($but_html, 0, -2) . '</ul>' . $search . '</div>';

if ($table) {

    echo '<div class="clear"></div>';
    echo '<form class="" method="post">';
    echo '<div class="bulkactions" style="margin: 10px 0;">
			<label for="bulk-action-selector-top" class="screen-reader-text">选择批量操作</label><select name="action" id="bulk-action-selector-top">
                <option value="-1">批量操作</option>
                    <option value="delete">删除</option>
                </select>
                <input type="submit" class="button action" value="应用">
		</div>';

    echo '<div style="overflow-y: auto;width: 100%;">';
    echo '<table class="widefat fixed striped posts table table-bordered" style="min-width: 1000px;">';
    echo $table;
    echo '</table>';
    echo '</div>';
    echo '</form>';
    echo '<div class="clear"></div>';

} elseif ($csf_args) {
    ZCSF::instance('add_msg', $csf_args);
}
if ($page_html) {
    echo $page_html;
}
if ($pagenavi) {
    zibpay_admin_pagenavi($count_all, $ice_perpage);
}

?>


</div>