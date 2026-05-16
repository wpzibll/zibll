<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 10:19:48
 * @LastEditTime : 2026-04-29 21:19:50
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

zib_require(array(
    'inc/options/options-module',
    'inc/options/upgrade',
));
if (is_admin()) {
    zib_require(array(
        'admin-options',
        'metabox-options',
        'profile-options',
        'action',
    ), false, 'inc/options/');
}

//使用Font Awesome 4
add_filter('csf_fa4', '__return_true');

function zib_csf_custom_icons($icons)
{
    $html = '<div class="custom-svg-box"><div class="csf-icon-title" style=" padding: 8px; ">自定义SVG图标代码 <div class="mt6" style=" font-size: 12px; opacity: .8; font-weight: normal; ">插入SVG格式图标代码，请注意代码规范 | <a target="_blank" href="https://www.zibll.com/547.html" class="loginbtn">查看官网教程</a></div> </div>';
    $html .= '<div class="flex" style="margin:5px;position: relative;">
            <textarea placeholder="请粘贴SVG格式代码，示例：&lt;svg viewBox=&quot;0 0 1024 1024&quot;&gt;&lt;path d=&quot;...&quot;&gt;&lt;/path&gt;&lt;/svg&gt;" name="icon_html" rows="2" style="height: 108px;" class="flex1"></textarea>
            <div class="flex ac" style="margin:5px;position: absolute;right: 5px;bottom: 5px;">
                <div class="flex jc mr3 custom-svg-display" style="width: 28px;height: 28px;line-height: 28px;font-size: 24px;color: #555;text-align: center;border: 1px solid #ccc;background-color: #f7f7f7;border-radius: 2px;"></div> <a href="#" class="button button-primary custom-svg-submit" data-nonce="70527b7cf6">确认插入</a>
            </div>
        </div>
        <div class="custom-svg-lists"></div>
        </div>';
    echo $html;

    $icons[] = array(
        'title' => '主题内置SVG图标',
        'icons' => array(
            'zibsvg-like',
            'zibsvg-view',
            'zibsvg-comment',
            'zibsvg-quick-reply',
            'zibsvg-time',
            'zibsvg-search',
            'zibsvg-money',
            'zibsvg-right',
            'zibsvg-left',
            'zibsvg-reply',
            'zibsvg-circle',
            'zibsvg-close',
            'zibsvg-minus',
            'zibsvg-add',
            'zibsvg-check-circle',
            'zibsvg-handbag',
            'zibsvg-shopping-cart',
            'zibsvg-img-lists',
            'zibsvg-wallet',
            'zibsvg-gift',
            'zibsvg-transit',
            'zibsvg-return',
            'zibsvg-add-ring',
            'zibsvg-post',
            'zibsvg-posts',
            'zibsvg-huo',
            'zibsvg-favorite',
            'zibsvg-manual-service',
            'zibsvg-menu',
            'zibsvg-d-qq',
            'zibsvg-d-weibo',
            'zibsvg-d-wechat',
            'zibsvg-d-email',
            'zibsvg-user',
            'zibsvg-theme',
            'zibsvg-signout',
            'zibsvg-set',
            'zibsvg-signup',
            'zibsvg-user_rp',
            'zibsvg-pan_baidu',
            'zibsvg-lanzou',
            'zibsvg-onedrive',
            'zibsvg-tianyi',
            'zibsvg-menu_2',
            'zibsvg-alipay',
            'zibsvg-baidu',
            'zibsvg-dingtalk',
            'zibsvg-xunlei',
            'zibsvg-123pan',
            'zibsvg-alipan',
            'zibsvg-quark',
            'zibsvg-360yunpan',
            'zibsvg-huawei',
            'zibsvg-xiaomi',
            'zibsvg-gitee',
            'zibsvg-xiaohongshu',
            'zibsvg-zhihu',
            'zibsvg-douyin',
            'zibsvg-bilibili',
            'zibsvg-translate',
            'zibsvg-yidongyun',
            'zibsvg-ucpan',
            'zibsvg-comment-fill',
            'zibsvg-private',
            'zibsvg-hot-fill',
            'zibsvg-hot',
            'zibsvg-topping',
            'zibsvg-topic',
            'zibsvg-plate-fill',
            'zibsvg-extra-points',
            'zibsvg-deduct-points',
            'zibsvg-points',
            'zibsvg-tags',
            'zibsvg-user-auth',
            'zibsvg-vip_1',
            'zibsvg-vip_2',
            'zibsvg-qzone-color',
            'zibsvg-qq-color',
            'zibsvg-weibo-color',
            'zibsvg-poster-color',
            'zibsvg-copy-color',
            'zibsvg-user-color',
            'zibsvg-user-color-2',
            'zibsvg-add-color',
            'zibsvg-home-color',
            'zibsvg-money-color',
            'zibsvg-order-color',
            'zibsvg-cart-color',
            'zibsvg-gift-color',
            'zibsvg-security-color',
            'zibsvg-trend-color',
            'zibsvg-msg-color',
            'zibsvg-tag-color',
            'zibsvg-comment-color',
            'zibsvg-wallet-color',
            'zibsvg-money-color-2',
            'zibsvg-merchant-color',
            'zibsvg-medal-color',
            'zibsvg-points-color',
            'zibsvg-book-color',
            'zibsvg-ontop-color',
        ),
    );

    $icons = array_reverse($icons);
    return $icons;
}

add_filter('csf_field_icon_add_icons', 'zib_csf_custom_icons');

//定义文件夹
function zib_csf_custom_csf_override()
{
    return 'inc/csf-framework';
}
add_filter('csf_override', 'zib_csf_custom_csf_override');

//自定义css、js
function zib_csf_add_custom_wp_enqueue()
{
    // Style
    wp_enqueue_style('csf_custom_css', get_template_directory_uri() . '/inc/csf-framework/assets/css/style.min.css', array(), THEME_VERSION);
    // Script
    wp_enqueue_script('csf_custom_js', get_template_directory_uri() . '/inc/csf-framework/assets/js/main.min.js', array('jquery'), THEME_VERSION);
}

add_action('csf_enqueue', 'zib_csf_add_custom_wp_enqueue');

//获取主题设置链接
function zib_get_admin_csf_url($tab = '')
{
    $tab                = trim(strip_tags($tab));
    $tab_array          = explode('/', $tab);
    $tab_array_sanitize = array();
    foreach ($tab_array as $tab_i) {
        $tab_array_sanitize[] = sanitize_title($tab_i);
    }
    $tab_attr = esc_attr(implode('/', $tab_array_sanitize));
    $url      = add_query_arg('page', 'zibll_options', admin_url('admin.php'));
    $url      = $tab ? $url . '#tab=' . $tab_attr : $url;
    return esc_url($url);
}

//备份主题数据
function zib_options_backup($type = '自动备份')
{
    $prefix  = 'zibll_options';
    $options = get_option($prefix);

    $options_backup = get_option($prefix . '_backup');
    if (!$options_backup) {
        $options_backup = array();
    }

    $time                  = current_time('Y-m-d H:i:s');
    $options_backup[$time] = array(
        'time' => $time,
        'type' => $type,
        'data' => $options,
    );

    //保留20次数据，删除多余的
    if (count($options_backup) > 20) {
        $options_backup = array_slice($options_backup, -20);
    }

    return update_option($prefix . '_backup', $options_backup);
}

function zib_csf_reset_to_backup()
{
    zib_options_backup('重置全部 自动备份');
}

add_action('csf_zibll_options_reset_before', 'zib_csf_reset_to_backup');

function zib_csf_reset_section_to_backup()
{
    zib_options_backup('重置选区 自动备份');
}

add_action('csf_zibll_options_reset_section_before', 'zib_csf_reset_section_to_backup');

//主题版本变更自动备份
function zib_new_zibll_to_backup()
{
    $prefix         = 'zibll_options';
    $options_backup = get_option($prefix . '_backup');
    $time           = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ('主题版本变更 自动备份' == $val['type']) {
                $time = $key;
                break;
            }
        }
    }

    if (!$time || strtotime($time) < strtotime('-30 minutes', current_time('timestamp'))) {
        zib_options_backup('主题版本变更 自动备份');

        //版本变更后刷新所有缓存
        wp_cache_flush();
    }
}
add_action('zibll_update_notices', 'zib_new_zibll_to_backup');

//定期自动备份
function zib_csf_save_section_to_backup()
{
    $prefix         = 'zibll_options';
    $options_backup = get_option($prefix . '_backup');
    $time           = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ('定期自动备份' == $val['type']) {
                $time = $key;
                break;
            }
        }
    }
    if (!$time || (floor((strtotime(current_time('Y-m-d H:i:s')) - strtotime($time)) / 3600) > 600)) {
        zib_options_backup('定期自动备份');
    }
}

add_action('csf_zibll_options_saved', 'zib_csf_save_section_to_backup');

//保存主题时候保存必要的wp设置
function zib_save_zibll_wp_options()
{
    $version = get_option('zibll_version');
    if (!$version) {
        //首次安装主题的必要设置
        update_option('default_comments_page', 'oldest');
        update_option('comment_order', 'asc');
        update_option('comment_registration', 1);
        update_option('require_name_email', 1);
        update_option('thread_comments', 1);
        update_option('thread_comments_depth', 3);

        update_option('medium_size_w', 800);
        update_option('medium_size_h', 800);
    }

    //zibll_version 不可加入zib聚合
    update_option('zibll_version', THEME_VERSION);

    /**
     * 刷新固定连接
     */
    zib_flush_rewrite_rules();
}
add_action('csf_zibll_options_save_after', 'zib_save_zibll_wp_options');

//主题版本变更后发送通知
function zib_notice_update()
{
    $version    = get_option('zibll_version');
    $theme_data = wp_get_theme();
    if ($version && version_compare($version, $theme_data['Version'], '<')) {
        $up = array();
        do_action('zibll_update_notices', $theme_data['Version']);

        $up_desc = !empty($up['update_description']) ? '<p>' . $up['update_description'] . '</p>' : '';
        $con     = '<div class="notice notice-success is-dismissible">
				<h2 style="color:#fd4c73;"><i class="fa fa-heart fa-fw"></i> Zibll 子比主题版本已变更</h2>
                ' . $up_desc . '
                <p>欢迎使用zibll子比主题V' . THEME_VERSION . '，使用前请务必先配置好伪静态和固定链接，否则会出现404错误！<a target="_bank" style="color:#217ff9;" href="https://www.zibll.com/3025.html">查看官网教程</a></p>
                <p><a target="_bank" style="color:#217ff9;box-shadow:none !important;" href="https://www.zibll.com/375.html">查看版本记录</a></p>
                <p>版本变更后系统会自动刷新缓存，但如果遇到异常，建议手动<b style="color:#ff321d;">清空缓存、刷新CDN</b>！</p>
                <p>保存一下<a href="' . zib_get_admin_csf_url() . '">主题设置</a>后，此通知会自动关闭</p>
                <p><a class="button" style="margin: 2px;" href="' . zib_get_admin_csf_url() . '">体验新功能</a><a class="button" style="margin: 2px;" href="' . zib_get_admin_csf_url('文档环境') . '">查看主题文档</a><a target="_blank" class="button" style="margin: 2px;" href="https://www.zibll.com/375.html">查看更新日志</a></p>
			</div>';
        echo $con;
    }
}
add_action('admin_notices', 'zib_notice_update');

//主题首次新安装通知
function zib_notice_new_install()
{
    $version = get_option('zibll_version');
    if (!$version) {
        $theme_version = wp_get_theme()['Version'];
        do_action('zibll_new_install_notices', $theme_version);

        $con = '<div class="notice notice-success is-dismissible">
				<h2 style="color:#fd4c73;"><i class="fa fa-heart fa-fw"></i> 感谢您使用子比主题</h2>
                <p>使用zibll子比主题请务必先配置好伪静态和固定链接，否则会出现404错误！<a target="_bank" style="color:#217ff9;" href="https://www.zibll.com/3025.html">查看官网教程</a></p>
                <p><a class="button" style="margin: 2px;" href="' . zib_get_admin_csf_url('文档环境') . '">查看主题文档</a><a target="_blank" class="button" style="margin: 2px;" href="https://www.zibll.com/375.html">查看更新日志</a></p>
			</div>';
        echo $con;
    }
}
add_action('admin_notices', 'zib_notice_new_install');

//伪静态检测通知
function zib_notice_permalink_structure()
{
    if (!get_option('permalink_structure')) {
        $con = '<div class="notice notice-error is-dismissible">
            <h2 style="color:#f73d3f;"><i class="dashicons-before dashicons-admin-settings"></i> 请完成固定链接设置</h2>
            <p>您的网站还未完成固定链接配置，部分页面会出现404错误，请先完成伪静态和固定链接设置</p>
            <p><a class="button button-primary" style="margin: 2px;" href="' . admin_url('options-permalink.php') . '">立即设置</a><a target="_blank" class="button" style="margin: 2px;" href="https://www.zibll.com/3025.html">查看官网教程</a></p>
        </div>';
        echo $con;
    }
}
add_action('admin_notices', 'zib_notice_permalink_structure');
