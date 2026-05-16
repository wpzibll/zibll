<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-05-25 14:35:52
 * @LastEditTime : 2025-08-04 20:53:39
 */

/*
 *                        _oo0oo_
 *                       o8888888o
 *                       88" . "88
 *                       (| -_- |)
 *                       0\  =  /0
 *                     ___/`---'\___
 *                   .' \\|     |// '.
 *                  / \\|||  :  |||// \
 *                 / _||||| -:- |||||- \
 *                |   | \\\  - /// |   |
 *                | \_|  ''\---/''  |_/ |
 *                \  .-\__  '-'  ___/-. /
 *              ___'. .'  /--.--\  `. .'___
 *           ."" '<  `.___\_<|>_/___.' >' "".
 *          | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *          \  \ `_.   \_ __\ /__ _/   .-` /  /
 *      =====`-.____`.___ \_____/___.-`___.-'=====
 *                        `=---='
 *
 *
 *      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *            佛祖保佑       永不宕机     永无BUG
 *
 */

/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:35:21
 * @LastEditTime: 2020-12-23 22:31:32
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//添加强制HTTPS判断，兼容CDN SSL设置错误时网站也能访问
function zib_content_url_filter($url)
{
    if (!preg_match('/^https/', $url)) {
        $home_url = home_url();
        if (preg_match('/^https/', $home_url)) {
            $url = str_replace('http', 'https', $url);
        }
    }
    return $url;
}

add_filter('content_url', 'zib_content_url_filter');

//定义常量
define('ZIB_TEMPLATE_DIRECTORY_URI', get_template_directory_uri()); //本主题
define('ZIB_ROOT_PATH', dirname(__DIR__) . '/'); //本主题的路径
define('ZIB_TEMP_DIR', apply_filters('zib_temp_dir', WP_CONTENT_DIR . '/zib-temp')); //临时文件夹

$theme_data = wp_get_theme();
$_version   = !empty($theme_data['Version']) ? $theme_data['Version'] : '1.0.0';
define('THEME_VERSION', '1.0.0');
define('ZIBLL_VERSION', '1.0.0');
if (!defined('ZIBLL_OPEN_SOURCE_EDITION')) {
    define('ZIBLL_OPEN_SOURCE_EDITION', true);
}

//php版本判断
if (PHP_VERSION_ID < 70000) {
    wp_die('PHP 版本过低，请先升级php版本到7.0及以上版本，当前php版本为：' . PHP_VERSION);
}

/**
 * @description: 封装一个引入函数
 * @param {*}
 * @return {*}
 */
function zib_require($data, $is_once = false, $prefix = '')
{

    if (is_array($data)) {
        foreach ($data as $d) {
            zib_require($d, $is_once, $prefix);
        }
    } else {
        if ($is_once) {
            require_once get_theme_file_path($prefix . $data . '.php');
        } else {
            require get_theme_file_path($prefix . $data . '.php');
        }
    }
}

//载入文件
zib_require(array(
    'inc/dependent',
    'inc/functions/compat/business-compat',
    'vendor/autoload',
    'inc/class/class',
    'inc/options/options',
    'inc/codestar-framework/codestar-framework',
    'inc/widgets/widget-class',
    'inc/functions/functions',
    'inc/widgets/widget-index',
    'oauth/oauth',
    'zibpay/functions',
    'action/function',
    'inc/functions/rest-api/function',
    'inc/csf-framework/classes/zib-csf.class',
), true);

//主题文件引入完毕
do_action('zib_require_end');

//codestar演示
//require_once get_theme_file_path('/inc/codestar-framework/samples/admin-options.php');
