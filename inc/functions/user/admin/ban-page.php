<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-01 17:08:02
 * @LastEditTime : 2025-12-23 22:11:46
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
$action       = !empty($_REQUEST['action']) ? $_REQUEST['action'] : false;
$process_type = !empty($_REQUEST['process_type']) ? $_REQUEST['process_type'] : false;

if ('process_submit' === $action && $process_type) {
    $process_user_id = !empty($_REQUEST['user_id']) ? (int) $_REQUEST['user_id'] : 0;
    $process         = !empty($_REQUEST['process']) ? (int) $_REQUEST['process'] : 0;
    $desc            = isset($_REQUEST['desc']) ? trim(strip_tags($_REQUEST['desc'])) : '';
    $process_id      = !empty($_REQUEST['process_id']) ? (int) $_REQUEST['process_id'] : 0;

    if ('user_report' === $process_type) {
        //处理举报信息
        $send_user = !empty($_REQUEST['send_user']) ? $_REQUEST['send_user'] : 0;
        zib_user_report_process($process_id, $send_user, $desc);
        echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">该举报信息处理完成</h4></div>';
    } else {
        //处理申诉信息
        if (zib_user_ban_appeal_process($process_id, $process_user_id, $process, $desc)) {
            echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">申诉处理成功</h4></div>';
        } else {
            echo '<div class="updated notice-alt"><h4 style="color: #ed2273;">申诉处理失败</h4></div>';
        }
    }
}

//准备参数
$page_url = add_query_arg('page', 'user_ban', admin_url('admin.php'));
$s        = !empty($_REQUEST['s']) ? $_REQUEST['s'] : false;

$WHERE = [];
$WHERE = array('type' => array('user_report', 'ban_appeal'));

//状态
if (isset($_REQUEST['status'])) {
    $WHERE['status'] = $_REQUEST['status'];
    $page_url        = add_query_arg('status', $_REQUEST['status'], $page_url);
}

if (isset($_REQUEST['type'])) {
    $WHERE['type'] = 'report' === $_REQUEST['type'] ? 'user_report' : 'ban_appeal';
    $page_url      = add_query_arg('type', $_REQUEST['type'], $page_url);
}
//用户
if (isset($_REQUEST['send_user'])) {
    $WHERE['send_user'] = $_REQUEST['send_user'];
}
//id
if (isset($_REQUEST['id'])) {
    $WHERE['id'] = $_REQUEST['id'];
}

if (isset($_REQUEST['report_user'])) {
    $WHERE['meta'] = 'like|%report_user_id_' . $_REQUEST['report_user'] . '%';
    if (isset($WHERE['send_user'])) {
        unset($WHERE['send_user']);
    }
}

if ($s) {
    $WHERE = "
    `type` IN ('user_report','ban_appeal') and (
    `title` LIKE '%$s%' OR
     `content` LIKE '%$s%' OR
     `meta` LIKE '%$s%')";
    $page_url = add_query_arg('s', $s, $page_url);
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
    <h2>用户禁封申诉及用户举报管理</h2>
    <?php echo $s ? '<div class="mb10">"' . esc_attr($s) . '" 的搜索结果</div>' : ''; ?>
    <div class="">
        <a class="button<?php echo !($_REQUEST['type'] ?? '') ? ' button-primary' : ''; ?>" href="<?php echo remove_query_arg(['s', 'type'], $page_url); ?>">全部</a>
        <a class="button<?php echo ($_REQUEST['type'] ?? '') === 'ban' ? ' button-primary' : ''; ?>"
            href="<?php echo add_query_arg('type', 'ban', $page_url); ?>">禁封申诉</a>
        <a class="button<?php echo ($_REQUEST['type'] ?? '') === 'report' ? ' button-primary' : ''; ?>"
            href="<?php echo add_query_arg('type', 'report', $page_url); ?>">用户举报</a>
    </div>
    <ul class="subsubsub">
        <li class="all"><a href="<?php echo remove_query_arg(['s', 'status'], $page_url); ?>">全部</a> |</li>
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

    <div class="table-box" style="overflow-y: auto;width: 100%;">
        <table class="widefat fixed striped posts" style="min-width: 1000px;">
            <thead>
                <tr>
                    <?php
                    $theads   = array();
                    $theads[] = array('width' => '5%', 'orderby' => 'status', 'name' => '状态');
                    $theads[] = array('width' => '5%', 'orderby' => 'type', 'name' => '类型');
                    $theads[] = array('width' => '5%', 'orderby' => 'send_user', 'name' => '提交用户');
                    $theads[] = array('width' => '18%', 'orderby' => '', 'name' => '详情');
                    $theads[] = array('width' => '6%', 'orderby' => 'create_time', 'name' => '提交时间');
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
                        if ('admin' != $value->send_user) {
                            $user_data = get_userdata((int) $value->send_user);
                            $user_name = $user_data->display_name;
                            $user_name = '<a href="' . add_query_arg('send_user', (int) $value->send_user, $page_url) . '">' . $user_name . '</a>';

                            $user_name .= '<div class="row-actions">';
                            $user_name .= '<a href="' . get_edit_user_link((int) $value->send_user) . '">管理</a> | ';
                            $user_name .= '<a href="' . zib_get_user_home_url((int) $value->send_user) . '">查看</a>';
                            $user_name .= '</div>';
                        } else {
                            $user_name = '未登录用户';
                        }

                        $status     = $value->status;
                        $status_but = $status;
                        if (1 == $status) {
                            $status_but = 'user_report' !== $value->type ? '<span style=" color: #16a60a; ">已批准</span>' : '<span style=" color: #0989fd; ">处理完成</span>';
                        } elseif (2 == $status) {
                            $status_but = '<span style=" color: #fb4444; ">已拒绝</span>';
                        } elseif (0 == $status) {
                            $status_but = '<a class="button" href="' . add_query_arg(array('action' => 'process', 'id' => $value->id), $page_url) . '">立即处理</a>';
                        }

                        $type = 'user_report' === $value->type ? '<span style="color:#fb4444; ">不良举报</span>' : '<span style="color: #16a60a; ">禁封申诉</span>';
                        $mate = maybe_unserialize($value->meta);

                        $desc = '';
                        if ('user_report' === $value->type) {
                            $report_user_id = $mate['report_user_id'] ??0 ;
                            $desc           = '<div style="color:#fb4444; ">举报用户：' . zib_get_user_name_link($report_user_id) . '</div>';
                            $desc .= '<div>举报原因：' . ($mate['reason']??'') . '</div>';
                            $desc .= ($mate['desc'] ?? '') ? '<div>举报详情：' . $mate['desc'] . '</div>' : '';
                            $desc .= ($mate['url'] ?? '') ? '<div>违规链接：<a target="_blank" href="' . esc_url($mate['url']) . '">点击查看</a></div>' : '';
                        } else {
                            $is_ban = zib_get_user_ban_info($value->send_user);
                            if ($is_ban) {
                                $in_type = $is_ban['type'];
                                $desc    = '<div style="color:#fd6a11; ">禁封状态：' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '</div>';
                                $desc .= '<div>禁封原因：' . $is_ban['reason'] . '</div>';
                                $desc .= '<div>开始时间：' . $is_ban['time'] . '</div>';
                                $desc .= '<div>结束时间：' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '</div>';
                            } else {
                                if (1 != $status) {
                                    //更新当前消息的状态
                                    ZibMsg::set_status($value->id, 1);
                                }
                                $status_but = '<span style=" color: #0989fd; ">已解封</span>';
                                $desc       = '<div style="color:#fd6a11; ">此用户已解封</div><div class="row-actions"></div>';
                            }
                        }

                        if ('process' == $action && ($_REQUEST['id'] ?? 0) == $value->id) {
                            $status_but = '<span style="color: #fb4444; ">正在处理</span>';
                        }

                        echo "<tr>\n";
                        echo "<td>$status_but</td>\n";
                        echo "<td>$type</td>\n";
                        echo "<td>$user_name</td>\n";
                        echo "<td><div style=\"max-height:39px;overflow:hidden;\">$desc</div></td>\n";
                        echo "<td>$value->create_time</td>\n";
                        echo "<td>$value->modified_time</td>\n";

                        echo '</tr>';
                        $ii++;

                        if ('process' == $action && ($_REQUEST['id'] ?? 0) == $value->id) {
                            $process_list = $value;
                            $process_meta = maybe_unserialize($process_list->meta);
                            $process_id   = $process_list->id;
                            $csf_fields   = array();
                            if ('user_report' === $value->type) {
                                //处理用户举报
                                $report_user_id = $process_meta['report_user_id'];
                                $desc_html      = '';
                                $desc_html .= '举报原因：' . $process_meta['reason'] . '<br>';
                                $desc_html .= ($process_meta['url'] ? '违规链接：<a target="_blank" href="' . esc_url($process_meta['url']) . '">' . esc_url($process_meta['url']) . '</a><br>' : '');
                                $desc_html .= ($process_meta['desc'] ? '举报详情：' . $process_meta['desc'] . '<br>' : '');
                                $desc_html .= '提交时间：' . $process_meta['time'];
                                if ($process_meta['img'] && is_array($process_meta['img'])) {
                                    $img_html = '';
                                    foreach ($process_meta['img'] as $img_id) {
                                        $image_urls = wp_get_attachment_image_src($img_id, 'full');
                                        if (isset($image_urls[0])) {
                                            $img_html .= '<div class="preview-item"><img alt="举报举证" src="' . $image_urls[0] . '"></div>';
                                        }
                                    }
                                    $desc_html .= $img_html ? '<div>' . $img_html . '</div>' : '';
                                }

                                $is_ban   = zib_get_user_ban_info($report_user_id);
                                $ban_html = '';
                                if ($is_ban) {
                                    $in_type = $is_ban['type'];
                                    $ban_html .= '<div color: #fb3925;font-size: 1.1em;margin-bottom: 10px;>当前用户已被' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '</div>';
                                    $ban_html .= '禁封状态：' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '<br>';
                                    $ban_html .= '开始时间：' . $is_ban['time'] . '<br>';
                                    $ban_html .= '结束时间：' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '<br>';
                                    $ban_html .= '禁封原因：' . $is_ban['reason'] . '<br>';
                                    $ban_html .= ($is_ban['desc'] ? '禁封说明：' . $is_ban['desc'] . '<br>' : '');
                                    if (!empty($is_ban['no_appeal'])) {
                                        $ban_html .= '此次禁封禁止发起申诉' . '<br>';
                                        $ban_html .= '禁止申诉说明：' . $is_ban['no_appeal_desc'] . '<br>';
                                    }
                                } else {
                                    $ban_html = '用户状态正常<br>请查阅相关资料以及举证，如举报事实清楚，可以<a target="_blank" href="' . zib_get_user_home_url($report_user_id) . '">点击此处</a>对用户做禁封处理，并将处理结果填写至下方';
                                }

                                $process = '';
                                $process .= '<input name="process_id" type="hidden" value="' . $process_id . '">';
                                $process .= '<input name="send_user" type="hidden" value="' . $process_list->send_user . '">';
                                $process .= '<input name="user_id" type="hidden" value="' . $report_user_id . '">';
                                $process .= '<input name="process_type" type="hidden" value="user_report">';
                                $process .= '<input name="action" type="hidden" value="process_submit">';

                                $html_args   = array();
                                $html_args[] = array(
                                    'title' => '被举报用户',
                                    'con'   => zib_get_user_name_link($report_user_id),
                                );
                                $html_args[] = array(
                                    'title' => '举报信息',
                                    'con'   => $desc_html,
                                );
                                $html_args[] = array(
                                    'title' => '被举报用户禁封信息',
                                    'con'   => $ban_html . $process,
                                );
                                $html_args[] = array(
                                    'title' => '处理说明',
                                    'con'   => '<textarea style=" width: 95%; max-width: 500px;" rows="2" cols="40" name="desc" placeholder="请输入本次处理的方式及说明"></textarea><p class="description">此处内容会反馈给举报人</p>',
                                );
                            } else {

                                $desc_html   = '';
                                $ban_user_id = $process_list->send_user;

                                $is_ban = zib_get_user_ban_info($ban_user_id);
                                if ($is_ban) {
                                    $in_type = $is_ban['type'];
                                    $desc_html .= '<div color: #fb3925;font-size: 1.1em;margin-bottom: 10px;>当前用户已被' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '</div>';
                                    $desc_html .= '禁封状态：' . (2 == $in_type ? '禁封中(禁止登录)' : '小黑屋禁封中') . '<br>';
                                    $desc_html .= '开始时间：' . $is_ban['time'] . '<br>';
                                    $desc_html .= '结束时间：' . ($is_ban['banned_time'] ? $is_ban['banned_time'] : '永久') . '<br>';
                                    $desc_html .= '禁封原因：' . $is_ban['reason'] . '<br>';
                                    $desc_html .= ($is_ban['desc'] ? '禁封说明：' . $is_ban['desc'] . '<br>' : '');

                                    if (!empty($is_ban['no_appeal'])) {
                                        $desc_html .= '此次禁封禁止发起申诉' . '<br>';
                                        $desc_html .= '禁止申诉说明：' . $is_ban['no_appeal_desc'] . '<br>';
                                    }
                                } else {
                                    $desc_html .= '<div color: #fb3925;font-size: 1.1em;margin-bottom: 10px;>当前用户已解封</div>';
                                }

                                $data_html = '';
                                if (isset($process_meta['data'])) {
                                    foreach ($process_meta['data'] as $k => $v) {
                                        if ($v) {
                                            $data_html .= $k . '：' . $v . '<br>';
                                        }
                                    }
                                }

                                if (!$data_html) {
                                    $data_html = ($process_list->content);
                                }

                                $html_args   = array();
                                $html_args[] = array(
                                    'title' => '申诉用户',
                                    'con'   => zib_get_user_name_link($ban_user_id),
                                );
                                $html_args[] = array(
                                    'title' => '禁封信息',
                                    'con'   => $desc_html,
                                );
                                $html_args[] = array(
                                    'title' => '申诉信息',
                                    'con'   => $data_html,
                                );
                                $process = '';
                                if (!$is_ban) {
                                    $process .= '<p><input type="radio" name="process" id="process_1" value="1" checked="checked"><label for="process_1" style=" color: #036ee2; ">完成处理</label></p>';
                                } else {
                                    $process .= '<p><input type="radio" name="process" id="process_1" value="1" checked="checked"><label for="process_1" style=" color: #036ee2; ">申诉通过（解封该用户）</label></p>';
                                    $process .= '<p><input type="radio" name="process" id="process_2" value="2"><label for="process_2" style=" color:#eb1b65; ">申诉拒绝（保持原禁封状态）</label></p>';
                                }
                                $process .= '<input name="process_id" type="hidden" value="' . $process_id . '">';
                                $process .= '<input name="user_id" type="hidden" value="' . $ban_user_id . '">';
                                $process .= '<input name="process_type" type="hidden" value="ban_appeal">';
                                $process .= '<input name="action" type="hidden" value="process_submit">';

                                $html_args[] = array(
                                    'title' => '处理方式',
                                    'con'   => $process,
                                );
                                $html_args[] = array(
                                    'title' => '处理留言',
                                    'con'   => '<textarea style=" width: 95%; max-width: 500px;" rows="2" cols="40" name="desc" placeholder="给用户留言"></textarea><p class="description">如需给用户留言请填写此处，如果拒绝请填写拒绝原因</p>',
                                );
                            }
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
                            echo '<form action="' . add_query_arg('page', 'user_ban', admin_url('admin.php')) . '" method="post"><table class="form-table"><tbody>' . $html . '</tbody></table></form>';
                        }
                    }
                } else {
                    echo '<tr><td colspan="6" align="center"><strong>暂无申请记录</strong></td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php echo zibpay_admin_pagenavi($all_count, $ice_perpage); ?>
</div>