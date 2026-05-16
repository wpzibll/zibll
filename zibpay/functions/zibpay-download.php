<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:50
 * @LastEditTime : 2026-03-30 19:02:51
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取用户已下载的次数
function zibpay_get_user_free_downloaded_number($user_id = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $time = current_time('Y-m-d');

    $user_mate        = zib_get_user_meta($user_id, 'pay_down_number', true);
    $user_down_number = !empty($user_mate[$time]) ? count($user_mate[$time]) : 0;

    return $user_down_number;
}

/**
 * 获取用户下载次数限制的数量
 * @param {*}
 * @return {*}
 */
function zibpay_get_user_free_down_limit($user_id = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $download_limit = 0;
    $user_vip_level = zib_get_user_vip_level($user_id);
    if ($user_vip_level && _pz('pay_user_vip_' . $user_vip_level . '_s', true)) {
        $download_limit = _pz('vip_benefit', 0, 'pay_download_limit_vip_' . $user_vip_level);
    } else {
        $download_limit = _pz('vip_benefit', 0, 'pay_download_limit');
    }
    return $download_limit;
}

/**
 * 获取用户的下载速度
 * @param {*}
 * @return {*}
 */
function zibpay_get_user_down_speed($user_id = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return 0;
    }

    $user_vip_level = zib_get_user_vip_level($user_id);
    if ($user_vip_level && _pz('pay_user_vip_' . $user_vip_level . '_s', true)) {
        $down_speed = _pz('vip_benefit', 0, 'pay_download_speed_vip_' . $user_vip_level);
    } else {
        $down_speed = _pz('vip_benefit', 0, 'pay_download_speed');
    }
    return (int) $down_speed * 1024;
}

/**
 * 将远程文件下载到本地，在提供给用户下载
 * @param {*}
 * @return {*}
 */
function zibpay_download_remote_to_local($file_local, $file_url)
{
    $file_size_opt = (int) _pz('pay_type_option', 0, 'down_remote_to_local_size');
    $file_ext_opt  = strtolower(_pz('pay_type_option', '', 'down_remote_to_local_ext')); //转小写
    if (!$file_url || !$file_size_opt || !$file_ext_opt || !_pz('pay_type_option', '', 'down_remote_to_local_s')) {
        return $file_local;
    }

    //使用inc/functions/zib-attachment.php中的zib_file_chunk的临时目录，会自动删除24小时候的临时文件
    $temp_dir      = ZIB_TEMP_DIR;
    $file_name_md5 = md5($file_url);
    $file_name     = ZibFile::url_filename($file_url);
    $filePath      = sprintf('%s/%s', $temp_dir, $file_name_md5 . '_paydown_' . $file_name);

    //如果文件已经存在，则直接返回
    if (file_exists($filePath)) {
        return $filePath;
    }

    //如果文件不存在，则下载文件
    //1.根据链接判断文件格式
    $file_ext = ZibFile::url_extension($file_name);
    if (!$file_ext || !stristr($file_ext_opt . ',', strtolower($file_ext) . ',')) {
        return $file_local;
    }

    //2.获取文件大小，并判断是否超过限制
    $file_size = ZibFile::remote_filesize($file_url);
    if (!$file_size || $file_size > $file_size_opt * 1024) {
        return $file_local;
    }

    //3.如果临时目录不存在，则创建它
    if (!@is_dir($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }

    //4.下载文件
    $http          = new Yurun\Util\HttpRequest;
    $download_file = $http->timeout(180000, 10000)->download($filePath, $file_url);

    //下载失败
    if (!$download_file || empty($download_file->success)) {
        return $file_local;
    }

    return $filePath;
}
add_filter('zibpay_download_file_local', 'zibpay_download_remote_to_local', 10, 2);

/**
 * 判断用户是否已经超过最大下载次数
 * @param {*}
 * @return
 */
function zibpay_is_user_free_down_limit($user_id = '')
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return false;
    }

    $download_limit = zibpay_get_user_free_down_limit($user_id);
    if (!$download_limit) {
        return false;
    }

    //已经下载的次数
    $user_down_number = zibpay_get_user_free_downloaded_number($user_id);
    if ($user_down_number >= $download_limit) {
        return true;
    }

    return false;
}

//储存用户下载次数
function zibpay_updata_pay_down_mate($post_id, $down_id, $paid)
{

    $user_id = get_current_user_id();

    //储存用户免费资源下载次数
    if (stristr($paid['paid_type'], 'free') && zibpay_get_user_free_down_limit($user_id)) {
        $time                   = current_time('Y-m-d');
        $user_mate              = zib_get_user_meta($user_id, 'pay_down_number', true);
        $user_mate              = $user_mate ? $user_mate : array();
        $today                  = !empty($user_mate[$time]) ? $user_mate : array();
        $today[$time][$post_id] = 1;
        zib_update_user_meta($user_id, 'pay_down_number', $today);
    }

    //记录用户下载日志
    $user_log_data = array(
        'paid_type' => $paid['paid_type'],
        'order_num' => !empty($paid['order_num']) ? $paid['order_num'] : '', //订单号
        'post_id'   => $post_id,
        'down_id'   => $down_id,
    );
    zibpay_updata_user_down_log($user_id, $user_log_data);

    //记录文章下载日志
    $poat_log_data = array(
        'paid_type' => $paid['paid_type'],
        'order_num' => !empty($paid['order_num']) ? $paid['order_num'] : '', //订单号
        'user_id'   => $user_id,
        'down_id'   => $down_id,
    );
    zibpay_updata_post_down_log($post_id, $poat_log_data);

}
add_action('zibpay_download_before', 'zibpay_updata_pay_down_mate', 10, 3);

/**
 * 更新用户下载记录明细
 * @param {*}
 * @return {*}
 */
function zibpay_updata_user_down_log($user_id, $data)
{

    $max = (int) _pz('pay_type_option', 0, 'down_user_log'); //最大记录数
    if (!$max) {
        return false;
    }

    $defaults = array(
        'paid_type' => '',
        'order_num' => '', //订单号
        'post_id'   => '',
        'down_id'   => '',
        'time'      => current_time('Y-m-d H:i'),
        'ip'        => zib_get_remote_ip_addr(),
    );
    $data = wp_parse_args($data, $defaults);
    if (!$data['post_id'] || !$data['paid_type']) {
        return false;
    }

    $meta_key = 'pay_down_log';
    $record   = zib_get_user_meta($user_id, $meta_key, true);
    if (!$record || !is_array($record)) {
        $record = array();
    }

    $record     = array_slice($record, 0, $max - 1, true); //数据切割，删除多余的记录
    $new_record = array_merge(array($data), $record);

    return zib_update_user_meta($user_id, $meta_key, $new_record);
}

/**
 * 更新文章的下载记录明细
 * @param {*}
 * @return {*}
 */
function zibpay_updata_post_down_log($post_id, $data)
{
    $max = (int) _pz('pay_type_option', 0, 'down_post_log'); //最大记录数
    if (!$max) {
        return false;
    }

    $defaults = array(
        'paid_type' => '',
        'order_num' => '', //订单号
        'user_id'   => '',
        'down_id'   => '',
        'time'      => current_time('Y-m-d H:i'),
        'ip'        => zib_get_remote_ip_addr(),
    );
    $data = wp_parse_args($data, $defaults);
    if (!$data['paid_type']) {
        return false;
    }

    $meta_key = 'pay_down_log';
    $record   = zib_get_post_meta($post_id, $meta_key, true);
    if (!$record || !is_array($record)) {
        $record = array();
    }

    $record     = array_slice($record, 0, $max - 1, true); //数据切割，删除多余的记录
    $new_record = array_merge(array($data), $record);

    return zib_update_post_meta($post_id, $meta_key, $new_record);
}

//获取下载按钮
function zibpay_get_post_down_buts($pay_mate, $paid_type = 'pay', $post_id = '')
{
    if (empty($pay_mate['pay_download'])) {
        return '<div class="muted-2-color text-center">暂无可下载资源</div>';
    }

    $down     = zibpay_get_post_down_array($pay_mate);
    $con      = '';
    $down_but = '';
    if (!$post_id) {
        global $post;
        $post_id = $post->ID;
    }

    $down_url = zib_pay_get_download_url($post_id);

    if ($down) {
        foreach ($down as $key => $down_v) {
            $down_link      = add_query_arg(array('down_id' => $key), $down_url);
            $down_v['name'] = isset($down_v['name']) ? trim($down_v['name']) : '';
            $down_name      = !empty($down_v['name']) ? $down_v['name'] : '本地下载';
            $icon           = '<i class="fa fa-download" aria-hidden="true"></i>';
            $class          = 'b-theme';
            if (stripos($down_v['link'], 'weiyun.')) {
                $class .= ' weiyun';
                $down_name = $down_v['name'] ? $down_v['name'] : '腾讯微云';
                $icon      = zib_get_svg('weiyun', '0 0 1400 1024');
            } elseif (stripos($down_v['link'], 'baidu')) {
                $class .= ' baidu';
                $down_name = $down_v['name'] ? $down_v['name'] : '百度网盘';
                $icon      = zib_get_svg('pan_baidu');
            } elseif (stripos($down_v['link'], 'lanzou')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '蓝奏云';
                $class .= ' lanzou';
                $icon = zib_get_svg('lanzou');
            } elseif (stripos($down_v['link'], 'onedrive') || stripos($down_v['link'], 'sharepoint')) {
                $down_name = $down_v['name'] ? $down_v['name'] : 'OneDrive';
                $class .= ' onedrive';
                $icon = zib_get_svg('onedrive');
            } elseif (stripos($down_v['link'], '.139.')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '移动云盘';
                $class .= ' b-blue';
                $icon = zib_get_svg('yidongyun');
            } elseif (stripos($down_v['link'], '.189.')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '天翼云';
                $class .= ' tianyi';
                $icon = zib_get_svg('tianyi');
            } elseif (stripos($down_v['link'], 'ctfile')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '城通网盘';
                $class .= ' ctfile';
                $icon = zib_get_svg('ctfile', '0 0 1260 1024');
            } elseif (stripos($down_v['link'], 'pan.xunlei')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '迅雷云盘';
                $class .= ' b-blue';
                $icon = zib_get_svg('xunlei');
            } elseif (stripos($down_v['link'], '123pan') || stripos($down_v['link'], '123912.com') || stripos($down_v['link'], '123684.com') || stripos($down_v['link'], '123865.com')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '123云盘';
                $class .= ' b-blue';
                $icon = zib_get_svg('123pan');
            } elseif (stripos($down_v['link'], 'alipan')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '阿里云盘';
                $class .= ' alipan';
                $icon = zib_get_svg('alipan');
            } elseif (stripos($down_v['link'], 'quark')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '夸克网盘';
                $class .= ' baidu';
                $icon = zib_get_svg('quark');
            } elseif (stripos($down_v['link'], 'yunpan')) {
                $down_name = $down_v['name'] ? $down_v['name'] : '360云盘';
                $class .= ' b-blue';
                $icon = zib_get_svg('360yunpan');
            } elseif (stripos($down_v['link'], 'uc.cn')) {
                $down_name = $down_v['name'] ? $down_v['name'] : 'UC网盘';
                $class .= ' b-black';
                $icon = zib_get_svg('ucpan');
            }

            $class = !empty($down_v['class']) ? $down_v['class'] : $class;
            $icon  = !empty($down_v['icon']) && $down_v['icon'] != 'fa fa-download' ? zib_get_cfs_icon($down_v['icon']) : $icon;

            $copy_key  = !empty($down_v['copy_key']) ? $down_v['copy_key'] : (!empty($down_v['copy_val']) ? '更多内容' : '');
            $down_more = $down_v['more'] ? $down_v['more'] : $copy_key;

            $down_more = $down_more ? '<span class="badg">' . $down_v['more'] . '</span>' : '';

            if (!empty($down_v['copy_val'])) {
                $copy_key  = esc_attr($copy_key);
                $copy_val  = esc_attr($down_v['copy_val']);
                $down_more = '<a href="javascript:;" ' . ($copy_key ? 'data-toggle="tooltip" title="点击复制' . $copy_key . '"' : '') . 'data-clipboard-tag="' . $copy_key . '" data-clipboard-text="' . $copy_val . '" class="but">' . $down_v['more'] . '</a>';
            }

            $down_but .= '<div class="but-download flex ac"><a target="_blank" href="' . $down_link . '" class="mr10 but ' . $class . '">' . $icon . $down_name . '</a>' . $down_more . '</div>';
        }
    }

    if (!$down_but) {
        return '<div class="muted-2-color text-center">暂无可下载资源</div>';
    }

    $down_but = '<div class="flex ac hh">' . $down_but . '</div>';

    //限制下载次数
    $download_limit_html = '';
    $user_id             = get_current_user_id();
    if ($user_id && stristr($paid_type, 'free')) {
        //免费资源限制下载次数
        $download_limit   = 0;
        $user_vip_level   = zib_get_user_vip_level($user_id);
        $user_down_number = zibpay_get_user_free_downloaded_number($user_id);

        if ($user_vip_level && _pz('pay_user_vip_' . $user_vip_level . '_s', true)) {
            $download_limit = _pz('vip_benefit', 0, 'pay_download_limit_vip_' . $user_vip_level);
        } else {
            $download_limit = _pz('vip_benefit', 0, 'pay_download_limit');
        }

        if ($download_limit) {
            $surplus = $download_limit - $user_down_number; //计算剩余下载次数
            if ($surplus <= 0) {
                $is_allow_buy = !empty($pay_mate['download_limit_over_price']) && (float) $pay_mate['download_limit_over_price'] > 0;

                $down_but = '<div class=""><span class="badg c-red btn-block">您今日下载免费资源个数已超限，请明日再下载' . ($is_allow_buy ? '或购买此商品' : '') . '</span></div>';
                $down_but .= $is_allow_buy ? zibpay_get_post_cashier_link($post_id, 'but jb-red mt20', '立即购买', array('price_type' => 'download_limit_over')) : '';
            } else {
                $_text    = $user_vip_level ? '您是尊贵的' . _pz('pay_user_vip_' . $user_vip_level . '_name') . '，' : '您';
                $down_but = '<div class=""><span class="badg c-red btn-block">' . $_text . '今日还可下载' . $surplus . '个免费资源</span></div>' . $down_but;
            }
        }

        $download_limit       = _pz('vip_benefit', 0, 'pay_download_limit');
        $download_limit_vip_1 = _pz('pay_user_vip_1_s', true) ? _pz('vip_benefit', 0, 'pay_download_limit_vip_1') : 0;
        $download_limit_vip_2 = _pz('pay_user_vip_2_s', true) ? _pz('vip_benefit', 0, 'pay_download_limit_vip_2') : 0;

        if ($download_limit || $download_limit_vip_1 || $download_limit_vip_2) {
            $download_limit_html = '<div class="mb10" style=" padding: 10px 20px; background:var(--muted-border-color); border-radius: 4px; ">';
            $download_limit_html .= '<div class="mb6">免费资源每日可下载：</div>';
            $download_limit_html .= $download_limit ? '<div class="mb6">普通用户：' . ($download_limit ? $download_limit . '个' : '不限制') . '</div>' : '';
            $download_limit_html .= $download_limit_vip_1 ? '<div class="mb6">' . _pz('pay_user_vip_1_name') . '：' . ($download_limit_vip_1 ? $download_limit_vip_1 . '个' : '不限制') . '</div>' : '';
            $download_limit_html .= $download_limit_vip_2 ? '<div class="">' . _pz('pay_user_vip_2_name') . '：' . ($download_limit_vip_2 ? $download_limit_vip_2 . '个' : '不限制') . '</div>' : '';
            $download_limit_html .= '</div>';
        }
    }

    $con .= '<div>';
    $con .= $download_limit_html;
    $con .= $down_but;
    $con .= '</div>';

    return $con;
}

/**
 * @description: 对链接进行数组处理
 * @param int|array $post_id 文章ID或者posts_zibpay的post_meta数组
 * @return {*}
 */
function zibpay_get_post_down_array($post_id = '')
{
    //允许传入数组
    if (is_array($post_id) && !empty($post_id['pay_download'])) {
        $pay_mate = $post_id;
    } else {
        if (!$post_id) {
            global $post;
            $post_id = !empty($post->ID) ? $post->ID : '';
        }
        $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    }

    if (!$pay_mate) {
        return array();
    }

    //新版兼容
    if (is_array($pay_mate['pay_download'])) {
        return $pay_mate['pay_download'];
    }
    $down     = explode("\r\n", $pay_mate['pay_download']);
    $down_obj = array();
    if (empty($down)) {
        return array();
    }
    $ii = 0;
    foreach ($down as $down_v) {
        //如果没有链接则跳出
        $down_v = explode('|', $down_v);
        if (empty($down_v[0])) {
            continue;
        }

        $down_obj[$ii] = array(
            'link'  => trim($down_v[0]),
            'name'  => !empty($down_v[1]) ? trim($down_v[1]) : '',
            'more'  => !empty($down_v[2]) ? trim($down_v[2]) : '',
            'class' => !empty($down_v[3]) ? trim($down_v[3]) : '',
        );
        $ii++;
    }
    return $down_obj;
}

/**v5.0已经弃用 */
function zibpay_edit_posts_file_upload()
{
    //echo json_encode($_FILES);
    if (is_uploaded_file($_FILES['zibpayFile']['tmp_name']) && is_user_logged_in() && current_user_can('publish_posts')) {
        $vname = $_FILES['zibpayFile']['name'];
        if ($vname != '') {
            $filename = substr(md5(current_time('YmdHis')), 0, 10) . mt_rand(11, 99) . strrchr($vname, '.');
            //上传路径
            $upfile = WP_CONTENT_DIR . '/uploads/zibpaydown/';
            if (!file_exists($upfile)) {
                mkdir($upfile, 0777, true);
            }
            $file_path = WP_CONTENT_DIR . '/uploads/zibpaydown/' . $filename;
            if (move_uploaded_file($_FILES['zibpayFile']['tmp_name'], $file_path)) {
                echo home_url() . '/wp-content/uploads/zibpaydown/' . $filename;
                exit;
            }
        }
    }
}
//add_action('wp_ajax_zibpay_file_upload', 'zibpay_edit_posts_file_upload');
