<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Project       : Zibll子比主题
 * @Description   : Zibll open source edition version maintenance.
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Record the installed official open source version so administrators,
 * compatibility code, and extensions can read a stable public value.
 */
function zib_open_source_version_record()
{
    update_option('zibll_version', THEME_VERSION);
    update_option('zibll_open_source_version', '1.0.0');
}
add_action('after_switch_theme', 'zib_open_source_version_record');
add_action('csf_zibll_options_save_after', 'zib_open_source_version_record', 20);
