<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2026-04-29 19:41:46
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */


/**
 * Load the theme text domain for translations.
 *
 * Several legacy widget and option definitions are registered while the theme
 * files are being loaded. Loading the text domain before those definitions
 * prevents WordPress 6.7+ from triggering just-in-time translation notices for
 * the zibll domain. The after_setup_theme hook is kept as a safe retry for
 * child themes and custom loaders.
 */
function zibll_load_theme_textdomain()
{
    if (!is_textdomain_loaded('zibll')) {
        load_theme_textdomain('zibll', get_template_directory() . '/languages');
    }
}
zibll_load_theme_textdomain();
add_action('after_setup_theme', 'zibll_load_theme_textdomain');

require_once get_theme_file_path('/inc/inc.php');

/**
 * 如果您需要添加一些自定义的PHP代码
 * 您可以在当前目录下新建一个 func.php 的文件，然后在最顶部写上 <?php ，再写入您的php代码
 * 主题会自动判断文件进行引入
 * 使用此方式更新或维护主题时，func.php 文件的内容将不会被覆盖（手动覆盖仍然会覆盖）
 * 当然需要注意php的代码规范，错误代码将会引起网站严重错误！
 */
if (file_exists(get_theme_file_path('/func.php'))) {
    require_once get_theme_file_path('/func.php');
}