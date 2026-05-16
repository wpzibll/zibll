<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-01 17:08:02
 * @LastEditTime: 2025-10-16 21:32:51
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台处理身份认证申请的页面
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (!defined('ABSPATH')) {
    exit;
}
$user_Info = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : false;

if ('process_submit' == $action) {
    $process_submit = array(
        'id'      => $_REQUEST['process_id'],
        'user_id' => $_REQUEST['user_id'],
        'status'  => $_REQUEST['process'],
        'msg'     => $_REQUEST['msg'],
        'name'    => $_REQUEST['name'],
        'desc'    => $_REQUEST['desc'],
    );

    $result = zib_user_auth_apply_process($process_submit);
    if ($result) {
        echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">申请处理成功</h4></div>';
    } else {
        echo '<div class="updated notice-alt"><h4 style="color: #ed2273;">申请处理失败</h4></div>';
    }
}

//准备参数
$page_url = add_query_arg('page', 'user_auth', admin_url('admin.php'));
$s        = !empty($_REQUEST['s']) ? $_REQUEST['s'] : false;

$WHERE = array('type' => 'auth_apply');

//状态
if (isset($_REQUEST['status'])) {
    $WHERE['status'] = $_REQUEST['status'];
}
//用户
if (isset($_REQUEST['send_user'])) {
    $WHERE['send_user'] = $_REQUEST['send_user'];
}
//id
if (isset($_REQUEST['id'])) {
    $WHERE['id'] = $_REQUEST['id'];
}

if ($s) {
    $WHERE = "
    `type` = 'auth_apply' and (
    `title` LIKE '%$s%' OR
     `content` LIKE '%$s%' OR
     `meta` LIKE '%$s%')";
    $page_url = $page_url . '&s=' . $s;
}

//////////
global $wpdb;
//统计数据
$all_count = ZibMsg::get_count($WHERE);

//分页计算
$ice_perpage = 20;
$pages       = ceil($all_count / $ice_perpage);
$page        = isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 1;
$offset      = $ice_perpage * ($page - 1);
//排序
$order = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'id';
$desc  = !empty($_REQUEST['desc']) ? $_REQUEST['desc'] : 'DESC';

$list = ZibMsg::get($WHERE, $order, $offset, $ice_perpage, $desc);

//echo json_encode($list);
?>
<style>
    .table-box>table {
        min-width: 740px;
    }

    .preview-item {
        position: relative;
        display: inline-block;
        width: 160px;
        height: 160px;
        border-radius: 4px;
        background: #fff;
        margin: 5px;
        vertical-align: top;
    }

    .preview-item img {
        -o-object-fit: contain;
        object-fit: contain;
        width: 100%;
        height: 100%;
    }
</style>

<div class="wrap">
    <h2>用户身份认证申请管理</h2>
    <?php echo $s ? '<div>"' . esc_attr($s) . '" 的搜索结果</div>' : ''; ?>
    <div class="">
        <ul class="subsubsub">
            <li class="all"><a href="<?php echo $page_url; ?>">全部</a> |</li>
            <li class="all"><a href="<?php echo add_query_arg('status', '0', $page_url); ?>">待处理</a> |</li>
            <li class="all"><a href="<?php echo add_query_arg('status', '1', $page_url); ?>">已批准</a> |</li>
            <li class="all"><a href="<?php echo add_query_arg('status', '2', $page_url); ?>">已拒绝</a></li>
        </ul>
        <div class="order-header" style="margin: 6px 0 12px;float: right;">
            <form class="form-inline form-order" method="post" action="<?php echo $page_url; ?>">
                <div class="form-group">
                    <input type="text" class="form-control" name="s" placeholder="搜索记录">
                    <button type="submit" class="button button-primary">提交</button>
                </div>
            </form>
            <?php
            if ('process' == $action) {
                echo '<a href="' . add_query_arg('page', 'user_auth', admin_url('admin.php')) . '">查看全部</a>';
            }
            ?>
        </div>
    </div>

    <div class="table-box" style="overflow-y: auto;width: 100%;">
        <table class="widefat fixed striped posts" style="min-width: 1000px;">
            <thead>
                <tr>
                    <?php
                    $theads   = array();
                    $theads[] = array('width' => '5%', 'orderby' => 'status', 'name' => '状态');
                    $theads[] = array('width' => '5%', 'orderby' => 'send_user', 'name' => '申请用户');
                    $theads[] = array('width' => '5%', 'orderby' => '', 'name' => '认证名称');
                    $theads[] = array('width' => '18%', 'orderby' => '', 'name' => '认证简介');
                    $theads[] = array('width' => '6%', 'orderby' => 'create_time', 'name' => '申请时间');
                    $theads[] = array('width' => '6%', 'orderby' => 'modified_time', 'name' => '更新时间');

                    foreach ($theads as $thead) {
                        $orderby = '';
                        if ($thead['orderby']) {
                            $orderby_url = add_query_arg('orderby', $thead['orderby'], $page_url);
                            $orderby .= '<a title="降序" href="' . add_query_arg('desc', 'ASC', $orderby_url) . '"><span class="dashicons dashicons-arrow-up"></span></a>';
                            $orderby .= '<a title="升序" href="' . add_query_arg('desc', 'DESC', $orderby_url) . '"><span class="dashicons dashicons-arrow-down"></span></a>';
                            $orderby = '<span class="orderby-but">' . $orderby . '</span>';
                        }
                        echo '<th class="" width="' . $thead['width'] . '">' . $thead['name'] . $orderby . '</th>';
                    } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($list) {
                    $ii = 1;
                    foreach ($list as $value) {
                        //整理数据
                        $user_data = get_userdata((int) $value->send_user);
                        $user_name = '';
                        if ($user_data) {
                            $user_name = $user_data->display_name;
                            $user_name = '<a href="' . add_query_arg('send_user', (int) $value->send_user, $page_url) . '">' . $user_name . '</a>';
                            // 添加管理和查看链接
                            $user_name .= '<div class="row-actions">';
                            $user_name .= '<a href="' . get_edit_user_link((int) $value->send_user) . '">管理</a> | ';
                            $user_name .= '<a href="' . zib_get_user_home_url((int) $value->send_user) . '">查看</a>';
                            $user_name .= '</div>';
                        } else {
                            $user_name = '<span>用户不存在</span>';
                        }

                        $status     = $value->status;
                        $status_but = $status;
                        if (1 == $status) {
                            $status_but = '<span style=" color: #0989fd; ">已批准</span>';
                        } elseif (2 == $status) {
                            $status_but = '<span style=" color: #fb4444; ">已拒绝</span>';
                        } elseif (0 == $status) {
                            $status_but = '<a class="button" href="' . add_query_arg(array('action' => 'process', 'id' => $value->id), $page_url) . '">立即处理</a>';
                        }
                        if ('process' == $action && $WHERE['id'] == $value->id) {
                            $status_but = '<span style="color: #fb4444; ">正在处理</span>';
                        }

                        $mate = maybe_unserialize($value->meta);

                        $name = isset($mate['name']) ? $mate['name'] : '';
                        $desc = isset($mate['desc']) ? $mate['desc'] : '';
                        $msg  = isset($mate['msg']) ? $mate['msg'] : '';
                        $time = isset($mate['time']) ? $mate['time'] : '';
                        $img  = isset($mate['img']) ? $mate['img'] : '';

                        echo "<tr>\n";
                        echo "<td>$status_but</td>\n";
                        echo "<td>$user_name</td>\n";
                        echo "<td>$name</td>\n";
                        echo "<td><div style=\"max-height:39px;overflow:hidden;\">$desc</div></td>\n";
                        echo "<td>$value->create_time</td>\n";
                        echo "<td>$value->modified_time</td>\n";

                        echo '</tr>';
                        $ii++;
                        // 构建处理函数
                        if ('process' == $action && $WHERE['id'] == $value->id) {

                            $html_args = array();

                            $html_args[] = array(
                                'title' => '认证身份名称',
                                'con'   => '<input style=" width: 95%; max-width: 500px; " name="name" type="text" value="' . $name . '">',
                            );
                            $html_args[] = array(
                                'title' => '认证身份简介',
                                'con'   => '<input style=" width: 95%; max-width: 500px; " name="desc" type="text" value="' . $desc . '">',
                            );
                            $html_args[] = array(
                                'title' => '用户申请说明',
                                'con'   => $msg,
                            );

                            $img_html = '';
                            if ($img && is_array($img)) {
                                foreach ($img as $img_url) {
                                    $img_html .= '<div class="preview-item"><img alt="身份认证申请" src="' . $img_url . '"></div>';
                                }
                            }
                            $img_html = $img_html ? '<div>' . $img_html . '</div>' : '无';

                            $html_args[] = array(
                                'title' => '用户申请举证',
                                'con'   => $img_html,
                            );

                            $html_args[] = array(
                                'title' => '处理留言',
                                'con'   => '<input style=" width: 95%; max-width: 500px; " name="msg" type="text" value="" placeholder="给用户留言"><p class="description">如需给用户留言请填写此处，如果拒绝请填写拒绝原因</p>',
                            );

                            $process = '';
                            $process .= '<p><input type="radio" name="process" id="process_1" value="1" checked="checked"><label for="process_1" style=" color: #036ee2; ">批准申请</label></p>';
                            $process .= '<p><input type="radio" name="process" id="process_2" value="2"><label for="process_2" style=" color:#eb1b65; ">拒绝申请</label></p>';
                            $process .= '<input name="process_id" type="hidden" value="' . esc_attr($value->id) . '">';
                            $process .= '<input name="user_id" type="hidden" value="' . ((int) $value->send_user) . '">';
                            $process .= '<input name="action" type="hidden" value="process_submit">';

                            $html_args[] = array(
                                'title' => '',
                                'con'   => $process,
                            );

                            $html_args[] = array(
                                'title' => '',
                                'con'   => '<p><button type="submit" class="button button-primary process-submit">确认提交</button></p>',
                            );
                            $html = '';

                            foreach ($html_args as $html_arg) {
                                $html .= '<tr>';
                                $html .= '<th>' . $html_arg['title'] . '</th>';

                                $html .= '<td>';
                                $html .= $html_arg['con'];
                                $html .= '</td>';

                                $html .= '</tr>';
                            }
                            echo '<form action="' . add_query_arg('page', 'user_auth', admin_url('admin.php')) . '" method="post"><table class="form-table"><tbody>' . $html . '</tbody></table></form>';
                        }
                    }
                } else {
                    echo '<tr><td colspan="6" align="center"><strong>暂无申请记录</strong></td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    if (!empty($list[0]) && 'process' == $action) { ?>



    <?php } ?>
    <?php echo zibpay_admin_pagenavi($all_count, $ice_perpage); ?>
</div>