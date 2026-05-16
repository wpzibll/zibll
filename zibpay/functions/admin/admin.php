<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-03 00:09:44
 * @LastEditTime : 2026-05-05 22:59:35
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//后台使用VUE的数据合并函数
function zibpay_admin_page_vue_data_filter($vue_data)
{

    add_filter('admin_shop_page_vue_data', function ($__vue_data) use ($vue_data) {
        return array_merge($__vue_data, $vue_data);
    });
}

if (_pz('pay_rebate_s')) {
    add_action('admin_notices', 'zib_withdraw_admin_notice', 1, 1);
}
function zib_withdraw_admin_notice()
{
    if (isset($_GET['page']) && in_array($_GET['page'], ['zibpay_withdraw', 'zibpay_page'])) {
        return;
    }

    $withdraw_count = ZibMsg::get_count(array(
        'type'   => 'withdraw',
        'status' => 0,
    ));
    if ($withdraw_count > 0) {
        $html = '<div class="notice notice-info is-dismissible">';
        $html .= '<h3>提现申请待处理</h3>';
        $html .= '<p>您有' . $withdraw_count . '个提现申请待处理</p>';
        $html .= '<p><a class="button" href="' . add_query_arg(array('page' => 'zibpay_withdraw', 'status' => 0), admin_url('admin.php')) . '">立即处理</a></p>';
        $html .= '</div>';
        echo $html;
    }
}

/**
 * @description: 后台用户列表添加会员筛选
 * @param {*}
 * @return {*}
 */
add_filter('views_users', 'zib_admin_user_views');
function zib_admin_user_views($views)
{

    $vip = isset($_REQUEST['vip']) ? $_REQUEST['vip'] : '';
    if (!$views) {
        $views = array();
    }

    for ($i = 1; $i <= 2; $i++) {
        if (_pz('pay_user_vip_' . $i . '_s', true)) {
            $views['vip' . $i] = '<a' . ($vip == $i ? ' class="current"' : '') . ' href="users.php?vip=' . $i . '">' . _pz('pay_user_vip_' . $i . '_name') . '</a>（' . zib_get_vip_user_count($i) . '）';
        }
    }
    return $views;
}

//为后台文章添加表格项目
function zib_admin_post_posts_columns($columns)
{
    $order    = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';
    $order_by = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $o_icon   = '<i class="opacity5 ml3 fa fa-long-arrow-' . ($order == 'asc' ? 'down' : 'up') . '"></i>';

    if (isset($columns['cb'])) {
        $add_columns['cb'] = $columns['cb'];
        unset($columns['cb']);
    }

    if (isset($columns['title'])) {
        $add_columns['title'] = $columns['title'];
        unset($columns['title']);
    }
    if (isset($columns['author'])) {
        $add_columns['author'] = $columns['author'];
        unset($columns['author']);
    }

    $add_columns['all_count'] = '<a href="' . add_query_arg(array('orderby' => 'views', 'order' => $order)) . '"><span>阅读' . ($order_by == 'views' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'like', 'order' => $order)) . '"><span>点赞' . ($order_by == 'like' ? $o_icon : '') . '</span></a> · <a href="' . add_query_arg(array('orderby' => 'favorite', 'order' => $order)) . '"><span>收藏' . ($order_by == 'favorite' ? $o_icon : '') . '</span></a>';
    $add_columns['pay_data']  = '<a href="' . add_query_arg(array('orderby' => 'sales_volume', 'order' => $order)) . '"><span>销售数据' . ($order_by == 'sales_volume' ? $o_icon : '') . '</span></a>';

    return array_merge($add_columns, $columns);
}
add_filter('manage_post_posts_columns', 'zib_admin_post_posts_columns');

function zib_admin_post_posts_custom_column($column_name, $posts_id)
{

    switch ($column_name) {
        case 'pay_data':
            $sales_volume    = (int) get_post_meta($posts_id, 'sales_volume', true);
            $sales_volume_db = zibDB::name(zibpay::$order_table_name)->where('post_id', $posts_id)->where('status', 1)->count();
            $posts_pay       = get_post_meta($posts_id, 'posts_zibpay', true);
            $zibpay_type     = isset($posts_pay['pay_type']) ? (int) $posts_pay['pay_type'] : 0;
            if (!$zibpay_type) {
                echo '<div style="font-size: 12px;">未开启付费</div>';
                break;
            }

            $pay_modo = isset($posts_pay['pay_modo']) ? $posts_pay['pay_modo'] : '0';
            if ($pay_modo === 'points') {
                $points_price = isset($posts_pay['points_price']) ? (int) $posts_pay['points_price'] : 0;
                $zibpay_price = '积分:' . $points_price;
            } else {
                $pay_price    = isset($posts_pay['pay_price']) ? round((float) $posts_pay['pay_price'], 2) : 0;
                $zibpay_price = zibpay_get_pay_mark() . ':' . $pay_price;
            }

            $paydown_log_link = $zibpay_type == 2 ? '<br>' . zibpay_get_paydown_log_admin_link('post', $posts_id, 'px12', $con = '[查看下载记录]') : '';

            $con = '<a style="color: ' . ['#ff4747', '#ee5307', '#1e8608', '#1a8a65', '#0c9cc8', '#086ae8', '#3353fd', '#4641e8', '#853bf2', '#e94df7', '#ca2b7d', '#d7354c', '#ff4747', '#8e24ac'][$zibpay_type] . ';" href="' . add_query_arg(array('zibpay_type' => $zibpay_type)) . '">' . zibpay_get_pay_type_name($zibpay_type) . $paydown_log_link . '</a>';
            $con .= '<div style="font-size: 12px;">' . $zibpay_price . ' · 销量' . $sales_volume . ($sales_volume_db ? '<a title="点击查看销售明细" href="' . zibpay_get_admin_shop_order_url('status=1&post_id=' . $posts_id) . '"> [真实销量' . $sales_volume_db . ']</a>' : '') . '</div>';
            echo '<div>' . $con . '</div>';
            break;

        case 'all_count':
            $views          = _cut_count((string) get_post_meta($posts_id, 'views', true));
            $score          = _cut_count((string) get_post_meta($posts_id, 'like', true));
            $favorite_count = _cut_count((string) get_post_meta($posts_id, 'favorite', true));

            echo '<div style="font-size: 12px;">阅读' . $views . ' · 点赞' . $score . ' · 收藏' . $favorite_count . '</div>';
            break;

    }
}
add_action('manage_post_posts_custom_column', 'zib_admin_post_posts_custom_column', 11, 2);

function zib_admin_restrict_manage_posts($post_type)
{
    if ('post' === $post_type) {
        $zibpay_type   = isset($_GET['zibpay_type']) ? (int) $_GET['zibpay_type'] : 0;
        $pay_type_args = array(
            '1' => '付费阅读',
            '2' => '付费下载',
            '5' => '付费图片',
            '6' => '付费视频',
            //  '7' => '自动售卡',
        );
        $option = '<option value="">商品类型</option>';
        foreach ($pay_type_args as $k => $name) {
            $option .= '<option ' . selected($k, $zibpay_type, false) . ' value="' . $k . '">' . $name . '</option>';
        }

        echo '<select class="form-control" name="zibpay_type">' . $option . '</select>';
    }
}
add_action('restrict_manage_posts', 'zib_admin_restrict_manage_posts');

//为后台文章添加筛选和配置
function zib_admin_main_post_query($query)
{

    if ($query->is_main_query() && $query->is_admin) {
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 0;
        if ($orderby) {
            $orderby_keys      = zib_get_query_mate_orderby_keys();
            $mate_orderbys     = $orderby_keys['value'];
            $mate_orderbys_num = $orderby_keys['value_num'];

            if (in_array($orderby, $mate_orderbys_num)) {
                $query->set('orderby', 'meta_value_num');
                $query->set('meta_key', $orderby);
            } elseif (in_array($orderby, $mate_orderbys)) {
                $query->set('orderby', 'meta_value');
                $query->set('meta_key', $orderby);
            } else {
                $query->set('orderby', $orderby);
            }
        }

        $zibpay_type = isset($_GET['zibpay_type']) ? (int) $_GET['zibpay_type'] : 0;

        if ($zibpay_type) {
            $meta_query = $query->get('meta_query');

            $filters_meta_query = array(
                'key'   => 'zibpay_type',
                'value' => $zibpay_type,
            );

            $meta_query   = is_array($meta_query) ? $meta_query : array();
            $meta_query[] = $filters_meta_query;

            $query->set('meta_query', $meta_query);
        }

    }

}
add_action('pre_get_posts', 'zib_admin_main_post_query', 99);

//为后台用户列表添加表格项目
function zib_admin_users_list_table_query_args($args)
{
    $orderby           = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $orderby_keys      = zib_get_query_mate_orderby_keys();
    $mate_orderbys     = $orderby_keys['value'];
    $mate_orderbys_num = $orderby_keys['value_num'];

    if (in_array($orderby, $mate_orderbys_num)) {
        $args['orderby']  = 'meta_value_num';
        $args['meta_key'] = $orderby;
    } elseif (in_array($orderby, $mate_orderbys)) {
        $args['orderby']  = 'meta_value';
        $args['meta_key'] = $orderby;
    }

    //默认排序方式为注册时间
    if (!isset($_REQUEST['orderby'])) {
        $args['order']   = 'desc';
        $args['orderby'] = 'user_registered';
    }
    $vip = isset($_REQUEST['vip']) ? $_REQUEST['vip'] : '';
    for ($i = 1; $i <= 2; $i++) {
        if ($vip == $i && _pz('pay_user_vip_' . $i . '_s', true)) {
            $args['meta_key']   = 'vip_level';
            $args['meta_value'] = $i;
        }
    }

    //搜索手机号
    if ($args['search'] && !empty($_REQUEST['search_phone'])) {
        $search             = trim($args['search'], '*');
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key'     => 'phone_number',
                'value'   => $search,
                'compare' => 'like',
            ),
        );
        $args['search'] = '';
    }

    return $args;
}
add_filter('users_list_table_query_args', 'zib_admin_users_list_table_query_args');

//搜索手机号
add_action('manage_users_extra_tablenav', 'zib_manage_users_extra_tablenav');
function zib_manage_users_extra_tablenav()
{
    $html = '<div class="alignright ml10"><label class="button"><input class="hide-column-tog" name="search_phone" type="checkbox" value="on">按手机号搜索</label></div>';
    echo $html;
}

/**挂钩后台用户中心-用户列表 */
function zib_users_columns($columns)
{
    $orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
    $order   = isset($_REQUEST['order']) && 'desc' == $_REQUEST['order'] ? 'asc' : 'desc';

    unset($columns['role']);
    unset($columns['name']);
    unset($columns['posts']);
    unset($columns['email']);

    $columns['show_name'] = '<a href="' . add_query_arg(array('orderby' => 'display_name', 'order' => $order)) . '"><span>昵称</span></a>';
    $columns['show_name'] .= ' · <a href="' . add_query_arg(array('orderby' => 'email', 'order' => $order)) . '"><span>邮箱</span></a>';
    if (_pz('user_level_s')) {
        $columns['show_name'] .= ' · <a href="' . add_query_arg(array('orderby' => 'level', 'order' => $order)) . '"><span>等级</span></a>';
    }
    $columns['oauth'] = __('社交登录');
    $columns['oauth'] .= ' · <a href="' . add_query_arg(array('orderby' => 'phone_number', 'order' => $order)) . '"><span>手机号</span></a>';

    $points_s      = _pz('points_s');
    $pay_balance_s = _pz('pay_balance_s');
    if ($pay_balance_s || $points_s) { //资产
        $columns['assets'] = '<a href="' . add_query_arg(array('orderby' => 'balance', 'order' => $order)) . '"><span>余额</span></a>';
        $columns['assets'] .= ' · <a href="' . add_query_arg(array('orderby' => 'points', 'order' => $order)) . '"><span>积分</span></a>';
    }

    $columns['vip_type'] = '<a href="' . add_query_arg(array('orderby' => 'vip_level', 'order' => $order)) . '"><span>VIP会员</span></a>';
    if (_pz('pay_rebate_s')) {
        $columns['rebate_info'] = __('推广返利');
        $columns['referrer']    = '<a href="' . add_query_arg(array('orderby' => 'referrer_id', 'order' => $order)) . '"><span>推荐人</span></a>';
    }

    if (_pz('pay_income_s')) {
        $columns['income'] = __('创作分成');
    }

    $columns['all_time'] = '<a href="' . add_query_arg(array('orderby' => 'user_registered', 'order' => $order)) . '"><span>注册</span></a> · <a href="' . add_query_arg(array('orderby' => 'last_login', 'order' => $order)) . '"><span>登录</span></a>';
    $columns['count']    = '文章 · 评论';

    return $columns;
}

/**
 * @description: 后台用户表格添加自定义内容
 * @param {*}
 * @return {*}
 */
function zib_output_users_columns($var, $column_name, $user_id)
{

    $user = get_userdata($user_id);

    switch ($column_name) {
        case 'show_name':
            $html = '<a title="在前台查看此用户" href="' . zib_get_user_home_url($user_id) . '">' . $user->display_name . '</a>';

            if (_pz('user_level_s')) {
                $user_level = zib_get_user_level($user_id);
                $title      = esc_attr(_pz('user_level_opt', 'LV' . $user_level, 'name_' . $user_level));
                $html .= ' · ' . $title;
            }
            if (_pz('user_ban_s')) {
                $is_ban = zib_user_is_ban($user_id);
                if ($is_ban) {
                    $is_ban = 2 === $is_ban ? '已封号' : '小黑屋';
                    $html .= ' · <code style="font-size: 12px;">' . $is_ban . '</code>';
                }
            }
            if (_pz('user_auth_s')) {
                $is_ban = zib_is_user_auth($user_id);
                if ($is_ban) {
                    $html .= ' · <code style="font-size: 12px;">已认证</code>';
                }
            }

            $html .= '<br><a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';
            if (_pz('user_medal_s')) {
                $medal_details = zib_get_user_medal_details($user_id);
                if ($medal_details) {
                    $i         = 0;
                    $icon_html = '';
                    $max_icon  = 3;
                    $count     = count($medal_details);

                    foreach ($medal_details as $k => $v) {
                        if ($i >= $max_icon) {
                            break;
                        }
                        $i++;
                        $icon_url = $v['icon'];
                        $icon_html .= '<img src="' . $v['icon'] . '" style="vertical-align: -3px;width: 14px;height: 14px;margin-right: 1px;margin-top: 2px;" data-toggle="tooltip" title="' . esc_attr($k) . '" alt="徽章-' . esc_attr($k) . '">';
                    }

                    $html .= '<div>' . $icon_html . '<span>徽章[' . $count . ']</span></div>';
                }

            }

            return '<div style="font-size: 12px;">' . $html . '</div>';
            break;
        case 'vip_type':
            $level = zib_get_user_vip_level($user_id);
            return $level ? _pz('pay_user_vip_' . $level . '_name') . '<br>' . zib_get_user_vip_exp_date_text($user_id) : '普通用户';
            break;
        case 'rebate_info':
            $rebate_ratio = zibpay_get_user_rebate_rule($user->ID);
            if (!$rebate_ratio['type'] || !is_array($rebate_ratio['type'])) {
                return '不返佣';
            }

            $rebate_type = zibpay_get_user_rebate_type($rebate_ratio['type'], '/');
            $html        = $rebate_type . '：' . $rebate_ratio['ratio'] . '%';

            $all     = zibpay_get_user_rebate_data($user_id, 'all')['sum'];
            $invalid = zibpay_get_user_rebate_data($user_id, 'effective')['sum'];
            $invalid = $invalid ? $invalid : 0;

            $html .= $all ? '<br><a title="查看明细" href="' . admin_url('admin.php?page=zibpay_rebate_page&referrer_id=' . $user->ID) . '">佣金累计：' . $all . ' · 待提现：' . $invalid . '</a>' : '<br>暂无佣金';

            return '<div style="font-size: 12px;">' . $html . '</div>';

            break;

        case 'income':

            $income_points_ratio    = zibpay_get_user_income_points_ratio($user_id);
            $income_ratio           = zibpay_get_user_income_ratio($user_id);
            $income_price_all       = zibpay_get_user_income_data($user_id, 'all');
            $income_price_effective = zibpay_get_user_income_data($user_id, 'effective');

            $html = '<div style="font-size: 12px;" title="分成比例">现金' . $income_ratio . '% · 积分' . $income_points_ratio . '%</div>';
            $html .= floatval($income_price_all['sum']) ? '<a title="查看明细" href="' . admin_url('admin.php?page=zibpay_income_page&post_author=' . $user->ID) . '">累计：' . (floatval($income_price_all['sum']) ?: '0') . ' · 待提现：' . (floatval($income_price_effective['sum']) ?: '0') . '</a>': '';
            return '<div style="font-size: 12px;">' . $html . '</div>';

            break;

        case 'assets':
            $user_points  = zibpay_get_user_points($user_id);
            $user_balance = _pz('pay_balance_s') ? zibpay_get_user_balance($user_id) : '';
            $html         = '<a title="查看消费记录" href="' . zibpay_get_admin_shop_order_url('pay_type=balance&user_id=' . $user->ID) . '">余额：' . $user_balance . '</a>';
            $html .= '<br><a title="查看消费记录" href="' . zibpay_get_admin_shop_order_url('pay_type=points&user_id=' . $user->ID) . '">积分：' . $user_points . '</a>';
            $html .= '<div class="row-actions px12">' . zibpay_get_user_assets_details_admin_link($user_id, '', '查看明细') . '</div>';

            $pay_down_log = zib_get_user_meta($user_id, 'pay_down_log', true);
            $html .= $pay_down_log ? '<div class="row-actions px12">' . zibpay_get_paydown_log_admin_link('user', $user_id, 'px12', '[付费资源下载记录]') . '</div>' : '';

            return '<div style="font-size: 12px;">' . $html . '</div>';
            break;

        case 'referrer':
            $referrer_id = get_user_meta($user_id, 'referrer_id', true);
            if ($referrer_id) {
                $referrer_name = get_userdata($referrer_id)->display_name;
                $level         = zib_get_user_vip_level($referrer_id);
                return '<a title="查看此用户" href="' . add_query_arg('s', $referrer_name, admin_url('users.php')) . '">' . $referrer_name . '</a>' . ($level ? '<br>' . _pz('pay_user_vip_' . $level . '_name') : '');
            }
            return '无';
            break;

        case 'all_time':
            $last_login = get_user_meta($user->ID, 'last_login', true);
            $last_login = $last_login ? '<span title="' . $last_login . '">' . zib_get_time_ago($last_login) . '登录</span>' : '--';

            $reg_time = get_date_from_gmt($user->user_registered);
            $reg_time = $reg_time ? '<span title="' . $reg_time . '">' . zib_get_time_ago($reg_time) . '注册</span>' : '--';

            //登录地址
            $addr      = '';
            $addr_data = zib_get_user_meta($user_id, 'user_addr', true);
            $addr      = zib_get_ip_geographical_position_badge($addr_data, 'city', '');
            $addr      = $addr ? '<br><code style="font-size: 12px;border-radius: 4px;padding: 1px 5px;" title="' . (!empty($addr_data['ip']) ? 'IP：' . $addr_data['ip'] : '') . '">IP:' . $addr : '</code>';
            return '<div style="font-size: 12px;">' . $reg_time . '<br>' . $last_login . $addr . '</div>';
            break;

        case 'oauth':

            $args   = array();
            $args[] = array(
                'name' => 'QQ',
                'type' => 'qq',
            );
            $args[] = array(
                'name' => '微信',
                'type' => 'weixin',
            );
            $args[] = array(
                'name' => '微信',
                'type' => 'weixingzh',
            );
            $args[] = array(
                'name' => '微博',
                'type' => 'weibo',
            );
            $args[] = array(
                'name' => 'GitHub',
                'type' => 'github',
            );
            $args[] = array(
                'name' => '码云',
                'type' => 'gitee',
            );
            $args[] = array(
                'name' => '百度',
                'type' => 'baidu',
            );
            $args[] = array(
                'name' => '支付宝',
                'type' => 'alipay',
            );
            $oauth = array();
            foreach ($args as $arg) {
                $name = $arg['name'];
                $type = $arg['type'];

                $bind_href = zib_get_oauth_login_url($type);
                if ($bind_href) {
                    $oauth_info = zib_get_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', true);
                    $oauth_id   = get_user_meta($user_id, 'oauth_' . $type . '_openid', true);
                    if ($oauth_info && $oauth_id) {
                        $oauth[] = $name;
                    }
                }
            }

            $html         = $oauth ? '已绑定' . implode('、', $oauth) : '未绑定社交账号';
            $phone_number = get_user_meta($user->ID, 'phone_number', true);
            $html .= $phone_number ? '<br>' . $phone_number : '<br>未绑定手机号';
            return '<div style="font-size: 12px;">' . $html . '</div>';
            break;
        case 'count':
            $com_n  = (int) get_user_comment_count($user_id);
            $post_n = (int) count_user_posts($user_id, 'post', true);

            $html = '';
            $html .= $post_n ? '<div><a href="edit.php?author=' . $user_id . '">文章[' . $post_n . ']</a></div>' : '<div>暂无文章</div>';
            $html .= $com_n ? '<div><a href="edit-comments.php?user_id=' . $user_id . '">评论[' . $com_n . ']</a></div>' : '<div>暂无评论</div>';

            return $html;
            break;
    }

    return $var;
}
add_filter('manage_users_columns', 'zib_users_columns');
add_filter('manage_users_custom_column', 'zib_output_users_columns', 10, 3);

//后台用户资料修改

function zib_csf_user_vip_fields()
{
    $args       = array();
    $profile_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
    if (!$profile_id && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
        $profile_id = get_current_user_id();
    }

    $vip_dec = '<h3>会员设置</h3><p>修改用户的会员信息，请确保主题设置中的<code>VIP会员功能</code>已开启</p>';

    if ($profile_id) {
        $vip_exp_date = get_user_meta($profile_id, 'vip_exp_date', true);
        $vip_level    = zib_get_user_vip_level($profile_id);

        $vip_dec .= '当前用户：';
        if ($vip_level) {
            if ('Permanent' == $vip_exp_date) {
                $vip_dec .= '已开通<code>' . _pz('pay_user_vip_' . $vip_level . '_name') . '</code>，永久有效';
            } else {
                $vip_dec .= '已开通<code>' . _pz('pay_user_vip_' . $vip_level . '_name') . '</code>，到期时间：' . date('Y年m月d日', strtotime($vip_exp_date));
            }
        } else {
            $vip_level_expired = (int) zib_get_user_meta($profile_id, 'vip_level_expired', true);
            if ($vip_level_expired) {
                $vip_dec .= '开通的<code>' . _pz('pay_user_vip_' . $vip_level_expired . '_name') . '</code>已过期，过期时间：' . date('Y年m月d日', strtotime($vip_exp_date));
            } else {
                $vip_dec .= '未开通会员';
            }
        }
    }

    $args[] = array(
        'type'    => 'content',
        'content' => $vip_dec,
    );

    $args[] = array(
        'id'      => 'vip_level',
        'type'    => 'radio',
        'title'   => 'VIP会员设置',
        'default' => '0',
        'desc'    => '在此直接修改此用户的会员信息，涉及到用户权益请谨慎修改',
        'options' => array(
            '0' => '普通用户',
            '1' => _pz('pay_user_vip_1_name'),
            '2' => _pz('pay_user_vip_2_name'),
        ),
    );
    $args[] = array(
        'id'         => 'vip_exp_date',
        'dependency' => array('vip_level', '>=', '1'),
        'type'       => 'date',
        'title'      => '会员有效期',
        'desc'       => '<p>请输入或选择有效期，请确保格式正确，例如：<code>2020-10-10 23:59:59</code></p>如果需要设置为“永久有效会员”，请手动设置为：<code>Permanent</code>',
        'settings'   => array(
            'dateFormat'  => 'yy-mm-dd 23:59:59',
            'changeMonth' => true,
            'changeYear'  => true,
        ),
    );
    return $args;
}

function zib_csf_user_points_balance_fields($type = 'points')
{

    $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;

    if (!$user_id && defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
        $user_id = get_current_user_id();
    }

    $user_points  = zibpay_get_user_points($user_id);
    $user_balance = _pz('pay_balance_s') ? zibpay_get_user_balance($user_id) : '';

    $text       = $type === 'balance' ? '余额' : '积分';
    $type_class = $type === 'balance' ? 'c-blue-2' : 'c-green';
    $action     = 'admin_update_user_' . $type; //管理
    $val        = $type === 'balance' ? zibpay_get_user_balance($user_id) : zibpay_get_user_points($user_id); //
    if ($type === 'balance') {
    }

    $con = '<div class="options-notice">
        <div class="explain">
        <p><b>您可以在此处手动为用户添加或扣除' . $text . '</b></p>
        <ajaxform class="ajax-form">
            <p class="flex ac"><select ajax-name="type">
                    <option value="">请选择添加或扣除</option>
                    <option value="add">添加</option>
                    <option value="delete">扣除</option>
                </select>
                <input style="max-width:120px;" ajax-name="val" type="number" placeholder="请输入数额"></p>
            <p class="">
                <div class="">请填写添加或扣除的简短说明</div>
                <input type="text" placeholder="添加或扣除的简短说明" style="width: 95%;" ajax-name="decs">
            </p>
            <div class="ajax-notice"></div>
            <p><a href="javascript:;" class="but ajax-submit ' . $type_class . '"> 确认提交</a></p>
            <input type="hidden" ajax-name="action" value="' . $action . '">
            <input type="hidden" ajax-name="user_id" value="' . $user_id . '">
            <input type="hidden" ajax-name="_wpnonce" value="' . wp_create_nonce( $action ) . '">
        </ajaxform>
    </div></div>';

    $args[] = array(
        'type'    => 'content',
        'content' => '<h3>用户' . $text . '</h3>',
    );
    $args[] = array(
        'title'   => '当前' . $text,
        'type'    => 'content',
        'content' => '<span class="but ' . $type_class . '">' . $val . '</span>',
    );
    $args[] = array(
        'class'   => 'compact',
        'type'    => 'submessage',
        'style'   => 'warning',
        'content' => $con,
    );

    return $args;

}

$points_s      = _pz('points_s');
$pay_balance_s = _pz('pay_balance_s');

if ($pay_balance_s) {
    CSF::createProfileOptions('user_balance', array(
        'data_type' => 'unserialize',
    ));
    CSF::createSection('user_balance', array(
        'fields' => zib_csf_user_points_balance_fields('balance'),
    ));
}
if ($points_s) {
    CSF::createProfileOptions('user_points', array(
        'data_type' => 'unserialize',
    ));
    CSF::createSection('user_points', array(
        'fields' => zib_csf_user_points_balance_fields('points'),
    ));
}

function zib_admin_user_vip_csf()
{
    CSF::createProfileOptions('user_vip', array(
        'data_type' => 'unserialize',
    ));
    CSF::createSection('user_vip', array(
        'fields' => zib_csf_user_vip_fields(),
    ));
}

if (is_super_admin()) {
    add_action('zib_require_end', 'zib_admin_user_vip_csf');
    add_action('show_user_profile', 'zib_render_profile_rebate_income_form_fields', 20);
    add_action('edit_user_profile', 'zib_render_profile_rebate_income_form_fields', 20);
    add_action('personal_options_update', 'zib_admin_save_profile_rebate_income');
    add_action('edit_user_profile_update', 'zib_admin_save_profile_rebate_income');
}

function zib_render_profile_rebate_income_form_fields($profile_user)
{
    $fields = array();
    $value  = array();

    if (_pz('pay_income_s', true)) {
        if (!empty($profile_user->ID)) {
            $value = array(
                'income_rule' => zib_get_user_meta($profile_user->ID, 'income_rule', true),
            );
        }

        $fields = array_merge($fields, array(
            array(
                'id'     => 'income_rule',
                'type'   => 'fieldset',
                'fields' => array(
                    array(
                        'type'    => 'content',
                        'content' => '<h3>创作分成</h3>在此处您可以单独为此用户设置创作分成比例',
                    ),
                    array(
                        'id'    => 'switch',
                        'type'  => 'switcher',
                        'title' => '独立设置',
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'ratio',
                        'type'       => 'spinner',
                        'title'      => '现金分成比例',
                        'desc'       => '为0则不参与分成',
                        'min'        => 0,
                        'max'        => 100,
                        'step'       => 5,
                        'unit'       => '%',
                        'default'    => 0,
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'points_ratio',
                        'type'       => 'spinner',
                        'title'      => '积分分成比例',
                        'desc'       => '为0则不参与分成（用户采用积分支付的订单给与作者的分成比例）',
                        'min'        => 0,
                        'max'        => 100,
                        'step'       => 5,
                        'unit'       => '%',
                        'default'    => 0,
                    ),
                ),
            ),
        ));
    }

    if (_pz('pay_rebate_s', true)) {
        if (!empty($profile_user->ID)) {
            $value += array(
                'rebate_rule' => zib_get_user_meta($profile_user->ID, 'rebate_rule', true),
            );
        }

        $fields = array_merge($fields, array(
            array(
                'id'     => 'rebate_rule',
                'type'   => 'fieldset',
                'fields' => array(
                    array(
                        'type'    => 'content',
                        'content' => '<h3>推广返利</h3>在此处您可以单独为此用户设置返利规则。为用户开启独立设置后，则不受主题设置的规则约束',
                    ),
                    array(
                        'id'    => 'switch',
                        'type'  => 'switcher',
                        'title' => '独立设置',
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'type',
                        'type'       => 'checkbox',
                        'title'      => '返利订单',
                        'desc'       => '给用户返利的订单类型<br>全部关闭，则代表此用户不参与推广返佣',
                        'options'    => CFS_Module::rebate_type(),
                        'default'    => array('all'),
                    ),
                    array(
                        'dependency' => array('switch', '!=', ''),
                        'id'         => 'ratio',
                        'type'       => 'spinner',
                        'title'      => '佣金比例',
                        'min'        => 0,
                        'max'        => 100,
                        'step'       => 5,
                        'unit'       => '%',
                        'default'    => 10,
                    ),
                ),
            ),
        ));
    }

    $csf_args = array(
        'class'  => 'csf-profile-options',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );
    ZCSF::instance('profile_options', $csf_args);
}

function zib_admin_save_profile_rebate_income($cuid)
{

    $fields = array(
        'rebate_rule',
        'income_rule',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_user_meta($cuid, $field, $_POST[$field]);
        }
    }
}

/**
 * @description: 文章付费设置的数据转换
 * @param {*}
 * @return {*}
 */
function zibpay_post_meta_to_csf($post_type, $post)
{
    $post_id = !empty($post->ID) ? $post->ID : '';

    if (!$post_id) {
        return;
    }

    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);

    if (!empty($pay_mate['pay_download']) && !is_array($pay_mate['pay_download'])) {
        $pay_download_args        = zibpay_get_post_down_array($pay_mate);
        $pay_mate['pay_download'] = $pay_download_args;
        update_post_meta($post_id, 'posts_zibpay', $pay_mate);
    }
}
add_action('add_meta_boxes', 'zibpay_post_meta_to_csf', 1, 2);

//添加文章付费参数
if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') !== false || strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false) {
    CSF::createMetabox('posts_zibpay', zibpay_post_mate_csf_meta());
    CSF::createSection('posts_zibpay', array(
        'fields' => zibpay_post_mate_csf_fields(),
    ));
}

function zibpay_post_mate_csf_meta()
{
    $meta = array(
        'title'     => '付费功能',
        'post_type' => array('post'),
        'data_type' => 'serialize',
    );
    return apply_filters('zib_add_pay_meta_box_meta', $meta);
}

function zibpay_get_pay_type_options()
{
    return array(
        'no' => __('关闭', 'zibll'),
        '1'  => __('付费阅读', 'zibll'),
        '2'  => __('付费下载', 'zibll'),
        '5'  => __('付费图片', 'zibll'),
        '6'  => __('付费视频', 'zibll'),
    );
}

/**
 * @description: 文章post_mate的设置数据
 * @param {*}
 * @return {*}
 */
function zibpay_post_mate_csf_fields()
{
    //对老板数据做兼容处理
    $post_id = !empty($_GET['post']) ? (int) $_GET['post'] : 0;
    if ($post_id) {
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
        if (!empty($pay_mate['pay_download']) && !is_array($pay_mate['pay_download'])) {
            $pay_mate['pay_download'] = zibpay_get_post_down_array($pay_mate);
            update_post_meta($post_id, 'posts_zibpay', $pay_mate);
        }
    }
    $pay_cuont_default = zib_get_mt_rand_number(_pz('pay_cuont_default', 0));
    $new_badge = zib_get_csf_option_new_badge();

    $fields = array(
        array(
            'title'   => '付费模式',
            'id'      => 'pay_type',
            'type'    => 'radio',
            'default' => 'no',
            'inline'  => true,
            'options' => 'zibpay_get_pay_type_options',
        ),
        //显示购买用户权限
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '购买权限',
            'id'         => 'pay_limit',
            'type'       => 'radio',
            'default'    => '0',
            'desc'       => '设置此处可实现会员专享资源功能，配置对应的会员价格可实现专享免费资源<br/><i class="fa fa-fw fa-info-circle fa-fw"></i> 使用此功能，请确保付费会员功能已开启，否则会出错',
            'options'    => array(
                '0' => __('所有人可购买', 'zibll'),
                '1' => _pz('pay_user_vip_1_name') . '及以上会员可购买',
                '2' => '仅' . _pz('pay_user_vip_2_name') . '可购买',
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '支付类型',
            'id'         => 'pay_modo',
            'type'       => 'radio',
            'default'    => _pz('pay_modo_default', 0),
            'options'    => array(
                '0'      => __('普通商品（金钱购买）', 'zibll'),
                'points' => __('积分商品（积分购买，依赖于用户积分功能）', 'zibll'),
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '订单时效' . $new_badge['8.7'],
            'id'         => 'order_expired_s',
            'default'    => false,
            'type'       => 'switcher',
            'desc'       => '设置订单时效后，用户购买后在订单时效内可查看内容，超时后需再次购买才能查看（注意：设置后建议在简介或内容中提醒用户订单时效，避免用户投诉）',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('order_expired_s', '!=', ''),
            ),
            'id'         => 'order_expired_opt',
            'type'       => 'fieldset',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => '有效时间',
            'sanitize'   => false,
            'fields'     => array(
                array(
                    'title'   => '订单有效期',
                    'id'      => 'time',
                    'default' => 0,
                    'type'    => 'number',
                ),
                array(
                    'title'   => '单位',
                    'type'    => 'select',
                    'class'   => 'compact',
                    'id'      => 'unit',
                    'options' => array(
                        'day'    => '天',
                        'hour'   => '小时',
                        'minute' => '分钟',
                    ),
                    'default' => 'day',
                ),
            ),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'id'         => 'points_price',
            'title'      => '积分售价',
            'class'      => '',
            'default'    => _pz('points_price_default'),
            'type'       => 'number',
            'unit'       => '积分',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . '积分售价',
            'id'         => 'vip_1_points',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_1_name') . '免费',
            'default'    => _pz('vip_1_points_default'),
            'type'       => 'number',
            'unit'       => '积分',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . '积分售价',
            'id'         => 'vip_2_points',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_2_name') . '免费',
            'default'    => _pz('vip_2_points_default'),
            'type'       => 'number',
            'unit'       => '积分',
            'desc'       => '会员价格不能高于售价',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_price',
            'title'      => '执行价',
            'subtitle'   => '填0则为免费',
            'default'    => _pz('pay_price_default', '0.01'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_original_price',
            'title'      => '原价',
            'class'      => 'compact',
            'subtitle'   => '显示在执行价格前面，并划掉',
            'default'    => _pz('pay_original_price_default'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_original_price', '!=', ''),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => ' ',
            'subtitle'   => '促销标签',
            'class'      => 'compact',
            'id'         => 'promotion_tag',
            'sanitize'   => false,
            'type'       => 'textarea',
            'default'    => _pz('pay_promotion_tag_default', '<i class="fa fa-fw fa-bolt"></i> 限时特惠'),
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . '价格',
            'id'         => 'vip_1_price',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_1_name') . '免费',
            'default'    => _pz('vip_1_price_default'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . '价格',
            'id'         => 'vip_2_price',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_2_name') . '免费',
            'default'    => _pz('vip_2_price_default'),
            'type'       => 'number',
            'unit'       => '元',
            'desc'       => '会员价格不能高于执行价',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => '推广折扣',
            'id'         => 'pay_rebate_discount',
            'class'      => 'compact',
            'subtitle'   => __('通过推广链接购买，额外优惠的金额', 'zibll'),
            'desc'       => __('1.需开启推广返佣功能  2.注意此金不能超过实际购买价，避免出现负数', 'zibll'),
            'default'    => _pz('pay_rebate_discount', 0),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array('pay_type', '==', 2),
            'id'         => 'download_limit_over_price',
            'title'      => '免费下载次数超限后价格' . $new_badge['8.7'],
            'desc'       => '如开启免费资源每日下载次数限制，则用户下载次数超限后，可按照此价格进行购买（填0则不可购买）<a href="' . zib_get_admin_csf_url('支付付费/vip-会员') . '">配置每日下载次数限制</a>',
            'class'      => '',
            'default'    => _pz('pay_download_limit_over_price',0),
            'type'       => 'number',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '销量浮动',
            'id'         => 'pay_cuont',
            'subtitle'   => __('为真实销量增加或减少的数量', 'zibll'),
            'default'    => $pay_cuont_default,
            'type'       => 'number',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '优惠码',
            'label'      => __('允许使用优惠码', 'zibll'),
            'desc'       => __('开启后请在<a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">优惠码管理</a>中添加优惠码<div class="c-yellow">由于php特性，此功能有一定风险可能会出现优惠码被多个订单同时使用的情况，建议仅在特殊活动时，短时间开启</div>', 'zibll'),
            'id'         => 'coupon_s',
            'default'    => false,
            'type'       => 'switcher',
        ),
        array(
            'dependency' => array('coupon_s|pay_type', '!=|!=', '|no'),
            'title'      => ' ',
            'subtitle'   => __('优惠券默认说明', 'zibll'),
            'class'      => 'compact',
            'id'         => 'coupon_desc',
            'default'    => '',
            'desc'       => '用户填写优惠码时，展示的提醒内容，支持html代码，请注意代码规范',
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency'  => array('pay_type', '==', 5),
            'title'       => '付费图片',
            'id'          => 'pay_gallery',
            'type'        => 'gallery',
            'add_title'   => '新增图片',
            'edit_title'  => '编辑图片',
            'clear_title' => '清空图片',
            'default'     => false,
        ),
        array(
            'dependency' => array('pay_type|pay_gallery', '==|!=', '5|'),
            'title'      => ' ',
            'subtitle'   => '免费查看',
            'class'      => 'compact',
            'id'         => 'pay_gallery_show',
            'default'    => _pz('pay_gallery_show_default', 1),
            'min'        => 0,
            'step'       => 1,
            'unit'       => '张',
            'desc'       => '设置可免费查看前几张图片的数量，不能大于付费图片数量，否则无效',
            'type'       => 'spinner',
        ),
        array(
            'dependency' => array('pay_type', '==', 6),
            'title'      => '视频资源',
            'id'         => 'video_url',
            'type'       => 'upload',
            'library'    => 'video',
            'preview'    => false,
            'default'    => '',
            'desc'       => '输入视频地址或选择、上传本地视频',
        ),
        array(
            'dependency' => array('pay_type|video_url', '==|!=', '6|'),
            'title'      => ' ',
            'subtitle'   => '视频封面(可选)',
            'class'      => 'compact',
            'id'         => 'video_pic',
            'type'       => 'upload',
            'library'    => 'image',
            'default'    => '',
        ),
        array(
            'dependency'  => array('pay_type|video_url', '==|!=', '6|'),
            'id'          => 'video_title',
            'title'       => ' ',
            'subtitle'    => '本集标题(如需添加剧集则需填写此处)',
            'placeholder' => '第1集',
            'default'     => '',
            'class'       => 'compact',
            'type'        => 'text',
        ),
        array(
            'dependency'   => array('pay_type|video_url', '==|!=', '6|'),
            'id'           => 'video_episode',
            'type'         => 'group',
            'button_title' => '添加剧集',
            'class'        => 'compact',
            'title'        => '更多剧集',
            'subtitle'     => '为付费视频添加更多剧集',
            'default'      => array(),
            'fields'       => array(
                array(
                    'id'       => 'title',
                    'title'    => ' ',
                    'subtitle' => '剧集标题',
                    'default'  => '',
                    'type'     => 'text',
                ),
                array(
                    'title'       => ' ',
                    'subtitle'    => '视频地址',
                    'id'          => 'url',
                    'class'       => 'compact',
                    'type'        => 'upload',
                    'preview'     => false,
                    'library'     => 'video',
                    'placeholder' => '选择视频或填写视频地址',
                    'default'     => false,
                ),

            ),
        ),
        array(
            'dependency' => array('pay_type', '==', '6'),
            'id'         => 'video_scale_height',
            'title'      => '视频设置',
            'subtitle'   => '固定长宽比例',
            'default'    => 0,
            'max'        => 200,
            'min'        => 0,
            'step'       => 5,
            'unit'       => '%',
            'type'       => 'spinner',
            'desc'       => '为0则不固定长宽比例',
        ),
        array(
            'dependency'   => array('pay_type', '==', 2),
            'id'           => 'pay_download',
            'type'         => 'group',
            'button_title' => '添加资源',
            'title'        => '资源下载',
            'sanitize'     => false,
            'class'        => 'pay-download-group',
            'fields'       => array(
                array(
                    'title'       => __('下载地址', 'zibll'),
                    'id'          => 'link',
                    'placeholder' => '上传文件或输入下载地址',
                    'preview'     => false,
                    'type'        => 'upload',
                    'desc'        => '部分云盘的分享链接直接粘贴，可自动识别链接及提取码',
                ),
                array(
                    'title'      => '资源备注',
                    'desc'       => '按钮旁边的额外内容，例如：提取密码、解压密码等',
                    'id'         => 'more',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'    => '点击复制',
                    'subtitle' => '复制的名称',
                    'class'    => 'compact',
                    'default'  => '',
                    'id'       => 'copy_key',
                    'type'     => 'text',
                ),
                array(
                    'title'    => ' ',
                    'subtitle' => '复制的内容',
                    'class'    => 'compact',
                    'default'  => '',
                    'id'       => 'copy_val',
                    'type'     => 'text',
                    'desc'     => '为“资源备注”按钮添加点击复制功能，请设置复制名称和复制内容',
                ),
                array(
                    'id'           => 'icon',
                    'type'         => 'icon',
                    'title'        => '自定义按钮图标',
                    'button_title' => '选择图标',
                    'default'      => 'fa fa-download',
                ),
                array(
                    'title'      => '自定义按钮文案',
                    'class'      => 'compact',
                    'id'         => 'name',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'   => '自定义按钮颜色',
                    'class'   => 'compact skin-color',
                    'desc'    => '按钮图标、文案、颜色默认均会自动获取，建议为空即可。<br>上方的按钮图标为主题自带的fontawesome 4图标库，如需添加其它图标可采用HTML代码，请注意代码规范！<br><a href="https://www.zibll.com/547.html" target="_blank">使用阿里巴巴Iconfont图标详细图文教程</a>',
                    'id'      => 'class',
                    'type'    => 'palette',
                    'options' => CFS_Module::zib_palette(),
                ),
            ),
        ),
        array(
            'dependency'   => array('pay_type', '==', 2),
            'id'           => 'attributes',
            'type'         => 'group',
            'button_title' => '添加属性',
            'title'        => '资源属性',
            'default'      => _pz('pay_attributes_default', array()),
            'fields'       => array(
                array(
                    'title'   => '属性名称',
                    'default' => '',
                    'id'      => 'key',
                    'type'    => 'text',
                ),
                array(
                    'title'   => '属性内容',
                    'class'   => 'compact',
                    'default' => '',
                    'id'      => 'value',
                    'type'    => 'text',
                ),
            ),
        ),
        array(
            'dependency'   => array('pay_type', '==', 2),
            'title'        => '演示地址',
            'id'           => 'demo_link',
            'default'      => array(),
            'add_title'    => '添加演示',
            'edit_title'   => '编辑地址',
            'remove_title' => '移除演示地址',
            'type'         => 'link',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '商品信息',
            'subtitle'   => __('商品标题', 'zibll'),
            'desc'       => __('（可选）如需要单独显示商品标题请填写此项', 'zibll'),
            'id'         => 'pay_title',
            'type'       => 'text',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => ' ',
            'subtitle'   => __('商品简介', 'zibll'),
            'id'         => 'pay_doc',
            'desc'       => __('（可选）如需要单独显示商品介绍请填写此项', 'zibll'),
            'class'      => 'compact',
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 1,
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => ' ',
            'subtitle'   => '更多详情',
            'id'         => 'pay_details',
            'desc'       => __('（可选）显示在商品卡片下方的内容（支持HTML代码，请注意代码规范）', 'zibll'),
            'class'      => 'compact',
            'default'    => _pz('pay_details_default'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 3,
            ),
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => ' ',
            'subtitle'   => '额外隐藏内容',
            'id'         => 'pay_extra_hide',
            'desc'       => __('（可选）付费后显示的额外隐藏内容（支持HTML代码，请注意代码规范）', 'zibll'),
            'class'      => 'compact',
            'default'    => _pz('pay_extra_hide_default'),
            'sanitize'   => false,
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 3,
            ),
        ),

        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'content'    => '<li><qc style="color:#fb2121;background:undefined">付费阅读</qc>功能需要配合<qc style="color:#fb2121;background:undefined">短代码</qc>或者古腾堡<qc style="color:#fb2121;background:undefined">隐藏内容块</qc>使用 </li><li>古腾堡编辑器：添加块-zibll主题模块-隐藏内容块-设置隐藏模式为：付费阅读 </li><li>经典编辑器：插入短代码： <code>[hidecontent type="payshow"]</code> 隐藏内容 <code>[/hidecontent]</code> </li><li><a href="https://www.zibll.com/580.html" target="_blank">官方教程</a> | <a href="' . zib_get_admin_csf_url('商城配置') . '" target="_blank">商城设置</a></li>',
            'style'      => 'warning',
            'type'       => 'submessage',
        ),
    );
    return apply_filters('zib_add_pay_meta_box_args', $fields);
}

/**
 * 获取管理员查看付费下载记录明细的链接
 * @param {*}
 * @return {*}
 */
function zibpay_get_paydown_log_admin_link($type, $id, $class = '', $con = '下载记录', $paged = 1)
{
    if (!$id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => '' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action' => 'admin_paydown_log',
            'type'   => $type,
            'id'     => $id,
            'paged'  => $paged,
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 获取管理员查看用户资产记录明细的链接
 * @param {*}
 * @return {*}
 */
function zibpay_get_user_assets_details_admin_link($user_id, $class = '', $con = '查看明细')
{

    $args = array(
        'tag'           => 'a',
        'data_class'    => 'modal-mini full-sm',
        'class'         => '' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $con,
        'query_arg'     => array(
            'action'  => 'admin_assets_details',
            'user_id' => $user_id,
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//后台列表中的批量编辑和快速编辑
add_action('bulk_edit_custom_box', 'zibpay_bulk_edit_custom_box', 50, 2);
add_action('quick_edit_custom_box', 'zibpay_bulk_edit_custom_box', 50, 2);
add_action('save_post', 'zibpay_bulk_edit_save_post', 10, 3);
function zibpay_bulk_edit_custom_box($column_name, $post_type)
{
    $permissible_posts_type = ['post'];
    if (!in_array($post_type, $permissible_posts_type) || $column_name !== 'taxonomy-topics') {
        return;
    }

    $item_html   = '';
    $fields_args = array(
        array(
            'id'      => 'pay_type',
            'type'    => 'radio',
            'title'   => '付费模式',
            'options' => zibpay_get_pay_type_options(),
        ),
        array(
            'id'      => 'pay_limit',
            'type'    => 'radio',
            'title'   => '购买权限',
            'options' => array(
                '0' => __('所有人可购买', 'zibll'),
                '1' => _pz('pay_user_vip_1_name') . '及以上会员可购买',
                '2' => '仅' . _pz('pay_user_vip_2_name') . '可购买',
            ),
        ),
        array(
            'id'      => 'pay_modo',
            'type'    => 'radio',
            'title'   => '支付类型',
            'options' => array(
                '0'      => __('普通商品', 'zibll'),
                'points' => __('积分商品', 'zibll'),
            ),
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'id'         => 'points_price',
            'title'      => '积分售价',
            'class'      => '',
            'default'    => _pz('points_price_default'),
            'type'       => 'number',
            'unit'       => '积分',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . '积分售价',
            'id'         => 'vip_1_points',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_1_name') . '免费',
            'default'    => _pz('vip_1_points_default'),
            'type'       => 'number',
            'unit'       => '积分',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '==', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . '积分售价',
            'id'         => 'vip_2_points',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_2_name') . '免费',
            'default'    => _pz('vip_2_points_default'),
            'type'       => 'number',
            'unit'       => '积分',
            'desc'       => '会员价格不能高于售价',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_price',
            'title'      => '执行价',
            'default'    => _pz('pay_price_default', '0.01'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'id'         => 'pay_original_price',
            'title'      => '原价',
            'class'      => 'compact',
            'subtitle'   => '显示在执行价格前面，并划掉',
            'default'    => _pz('pay_original_price_default'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_1_name') . '价格',
            'id'         => 'vip_1_price',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_1_name') . '免费',
            'default'    => _pz('vip_1_price_default'),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => _pz('pay_user_vip_2_name') . '价格',
            'id'         => 'vip_2_price',
            'class'      => 'compact',
            'subtitle'   => '填0则为' . _pz('pay_user_vip_2_name') . '免费',
            'default'    => _pz('vip_2_price_default'),
            'type'       => 'number',
            'unit'       => '元',
            'desc'       => '会员价格不能高于执行价',
        ),
        array(
            'dependency' => array('pay_type', '==', 2),
            'id'         => 'download_limit_over_price',
            'title'      => '免费下载次数超限后价格',
            'class'      => '',
            'default'    => '',
            'type'       => 'number',
        ),
        array(
            'dependency' => array(
                array('pay_type', '!=', 'no'),
                array('pay_modo', '!=', 'points'),
            ),
            'title'      => '推广折扣',
            'id'         => 'pay_rebate_discount',
            'class'      => 'compact',
            'subtitle'   => __('通过推广链接购买，额外优惠的金额', 'zibll'),
            'desc'       => __('1.需开启推广返佣功能  2.注意此金不能超过实际购买价，避免出现负数', 'zibll'),
            'default'    => _pz('pay_rebate_discount', 0),
            'type'       => 'number',
            'unit'       => '元',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '销量浮动',
            'id'         => 'pay_cuont',
            'subtitle'   => __('为真实销量增加或减少的数量', 'zibll'),
            'default'    => '',
            'type'       => 'number',
        ),
        array(
            'dependency' => array('pay_type', '!=', 'no'),
            'title'      => '允许使用优惠码',
            'id'         => 'coupon_s',
            'default'    => false,
            'type'       => 'switcher',
        ),
    );

    echo zib_get_quick_edit_custom_input($fields_args, 'pay', '付费参数', '<div class="c-yellow">批量设置付费参数，请仔细检查配置内容，更新后无法撤回。请注意配置逻辑，例如会员价不能高于普通价，执行价不能高于原价等</div>');
}

//批量编辑保存
function zibpay_bulk_edit_save_post($post_id, $post, $update)
{
    $permissible_posts_type = ['post', ''];
    if (!$update || !in_array($post->post_type, $permissible_posts_type) || empty($_REQUEST['zib_bulk_edit']['pay']) || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== 'edit-post') {
        return;
    }

    $zibpay_bulk_edit = $_REQUEST['zib_bulk_edit']['pay'];
    $pay_data         = get_post_meta($post_id, 'posts_zibpay', true);
    if (!$pay_data || !is_array($pay_data)) {
        $pay_data = array();
    }
    foreach ($zibpay_bulk_edit as $field_id => $field_value) {
        switch ($field_id) {
            case 'pay_type':
            case 'pay_limit':
            case 'pay_modo':
                if ($field_value !== 'ignore') {
                    $pay_data[$field_id] = $field_value;
                }

                break;

            case 'points_price':
            case 'vip_1_points':
            case 'vip_2_points':
            case 'pay_original_price':
            case 'pay_price':
            case 'vip_1_price':
            case 'vip_2_price':
            case 'download_limit_over_price':
            case 'pay_rebate_discount':
            case 'pay_cuont':

                $operation = $field_value['operation'];
                $pay_val   = isset($pay_data[$field_id]) ? (float) $pay_data[$field_id] : 0; // 0 is default value

                if ($operation !== 'ignore' && is_numeric($field_value['val'])) {
                    $val = (float) $field_value['val'];
                    switch ($operation) {
                        case 'set': //统一设置为
                            $pay_val = $val;
                            break;
                        case 'plus':
                            $pay_val = round($pay_val + $val, 2);
                            break;
                        case 'subtract':
                            $pay_val = round($pay_val - $val, 2);
                            break;
                        case 'multiply':
                            $pay_val = round($pay_val * $val, 2);
                            break;
                        case 'division':
                            if ($pay_val != 0 && $val != 0) {
                                $pay_val = round($pay_val / $val, 2);
                            }
                            break;
                    }

                    if (in_array($field_id, array('points_price', 'vip_1_points', 'vip_2_points', 'pay_cuont'))) {
                        $pay_val = (int) $pay_val;
                    }
                    $pay_data[$field_id] = $pay_val < 0 ? 0 : $pay_val;
                }
                break;
        }

    }

    update_post_meta($post_id, 'posts_zibpay', $pay_data);
}

//后台仪表盘增加付费文章统计
add_action('wp_dashboard_setup', 'zibpay_add_dashboard_widgets');
function zibpay_add_dashboard_widgets()
{
    if (is_super_admin()) {
        wp_add_dashboard_widget(
            'zibpay_dashboard_widget',
            '商城统计',
            'zibpay_dashboard_widget_function'
        );
    }
}

//后台仪表盘小部件
function zibpay_dashboard_widget_function()
{
    wp_enqueue_style('zibpay_page', get_template_directory_uri() . '/zibpay/assets/css/pay-page.css', array(), THEME_VERSION);
    wp_enqueue_script('highcharts', get_template_directory_uri() . '/zibpay/assets/js/highcharts.js', array('jquery'), THEME_VERSION);
    wp_enqueue_script('westeros', get_template_directory_uri() . '/zibpay/assets/js/westeros.min.js', array('jquery', 'highcharts'), THEME_VERSION);

    echo '<div class="pay-dashboard-widget">';
    require_once get_stylesheet_directory() . '/zibpay/page/index.php';
    echo '</div>';
}

//后台仪表盘增加付费文章统计
function zibpay_get_admin_dashboard_data()
{
    global $wpdb;
    $today     = zibpay_get_order_statistics_totime('today');
    $yester    = zibpay_get_order_statistics_totime('yester');
    $thismonth = zibpay_get_order_statistics_totime('thismonth');
    $lastmonth = zibpay_get_order_statistics_totime('lastmonth');
    $all       = zibpay_get_order_statistics_totime('all');
    $thisyear  = zibpay_get_order_statistics_totime('thisyear');
    $rebate    = '';

    $_all                 = (array) $wpdb->get_row("SELECT SUM(rebate_price) as rebate,SUM(income_price) as income  FROM $wpdb->zibpay_order WHERE  `status` = 1");
    $thismonth_time_where = zib_get_time_where_sql('thismonth', 'pay_time');
    $_thismonth           = (array) $wpdb->get_row("SELECT SUM(rebate_price) as rebate,SUM(income_price) as income FROM $wpdb->zibpay_order WHERE  `status` = 1 and $thismonth_time_where");

    $rebate = array(
        'all'       => isset($_all['rebate']) ? floatval($_all['rebate']) : 0,
        'thismonth' => isset($_thismonth['rebate']) ? floatval($_thismonth['rebate']) : 0,
    );
    $income = array(
        'all'       => isset($_all['income']) ? floatval($_all['income']) : 0,
        'thismonth' => isset($_thismonth['income']) ? floatval($_thismonth['income']) : 0,
    );

    $_rebate_1 = $wpdb->get_var("SELECT SUM(rebate_price) FROM $wpdb->zibpay_order WHERE  `status` = 1 and `rebate_status` = 1");
    $_income_1 = $wpdb->get_var("SELECT SUM(income_price) FROM $wpdb->zibpay_order WHERE  `status` = 1 and `income_status` = 1");

    //有效
    $rebate['effective'] = ($rebate['all'] - $_rebate_1);
    $income['effective'] = ($income['all'] - $_income_1);

    $data = array(
        array(
            'top'    => '今日订单',
            'val'    => $today['count'],
            'bottom' => '昨日订单：' . $yester['count'],
        ),
        array(
            'top'    => '今日收款',
            'val'    => ($today['sum'] > 1000) ? (int) $today['sum'] : $today['sum'],
            'bottom' => '昨日收款：' . $yester['sum'],
        ),
        array(
            'top'    => '本月订单',
            'val'    => $thismonth['count'],
            'bottom' => '上月订单：' . $lastmonth['count'],
        ),
        array(
            'top'    => '本月收款',
            'val'    => ($thismonth['sum'] > 10000) ? (int) $thismonth['sum'] : $thismonth['sum'],
            'bottom' => '上月收款：' . $lastmonth['sum'],
        ),
        array(
            'top'    => '有效单量',
            'val'    => $all['count'],
            'bottom' => '今年订单：' . $thisyear['count'],
        ),
        array(
            'top'    => '有效收款',
            'val'    => ($all['sum'] > 10000) ? (int) $all['sum'] : $all['sum'],
            'bottom' => '今年收款：' . $thisyear['sum'],
        ),
        array(
            'top'    => '总分成',
            'val'    => ($income['all'] > 10000) ? (int) $income['all'] : $income['all'],
            'bottom' => '未提现:' . $income['effective'] . ' · 本月:' . $income['thismonth'],
        ),
        array(
            'top'    => '总佣金',
            'val'    => ($rebate['all'] > 10000) ? (int) $rebate['all'] : $rebate['all'],
            'bottom' => '未提现:' . $rebate['effective'] . ' · 本月:' . $rebate['thismonth'],
        ),
    );
    return $data;
}
