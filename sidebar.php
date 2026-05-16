<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2024-10-11 15:17:52
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (!zib_is_show_sidebar() || wp_is_mobile()) {
	return;
}
?>
<div class="sidebar">
	<?php
	if (function_exists('dynamic_sidebar')) {
		if (!is_page()) {
			dynamic_sidebar('all_sidebar_top');
		}
		if (is_home()) {
			dynamic_sidebar('home_sidebar');
		} elseif (is_category() || is_tax('topics')) {
			dynamic_sidebar('cat_sidebar');
		} elseif (is_tag()) {
			dynamic_sidebar('tag_sidebar');
		} elseif (is_search()) {
			dynamic_sidebar('search_sidebar');
		} elseif (is_single()) {
			dynamic_sidebar('single_sidebar');
		} elseif (is_page()) {
			global $widgets_register_container, $page_id;
			if ($widgets_register_container && in_array('sidebar', $widgets_register_container)) {
				dynamic_sidebar('page_sidebar_' . $page_id);
			}
		}
		if (!is_page()) {
			dynamic_sidebar('all_sidebar_bottom');
		}
	}
	?>
</div>