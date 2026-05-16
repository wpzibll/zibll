<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2025-07-18 16:09:04
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (!defined('ABSPATH')) exit;

get_header();

?>

<?php if (function_exists('dynamic_sidebar')) {
	echo '<div class="container fluid-widget">';
	dynamic_sidebar('all_top_fluid');
	dynamic_sidebar('home_top_fluid');
	echo '</div>';
}
?>
<main role="main" class="container">
	<?php
	$paged = zib_get_the_paged();
	?>
	<div class="content-wrap">
		<div class="content-layout">
			<?php
			if (function_exists('dynamic_sidebar')) {
				dynamic_sidebar('home_top_content');
			}
			$index_tab_nav = zib_index_tab_html();
			if ($index_tab_nav) {
			?>
				<div class="home-tab-content">
					<?php echo $index_tab_nav; ?>
					<div class="tab-content">
						<div class="posts-row ajaxpager tab-pane fade in active" id="index-tab-main">
							<?php
							zib_ajax_option_menu('home');
							zib_posts_list();
							zib_paging();
							?>
						</div>
						<?php if (1 == $paged) {
							echo zib_index_tab('content');
						}
						?>
					</div>
				</div>
			<?php } ?>
			<?php if (function_exists('dynamic_sidebar')) {
				dynamic_sidebar('home_bottom_content');
			}
			?>
		</div>
	</div>
	<?php get_sidebar(); ?>
</main>
<?php if (function_exists('dynamic_sidebar')) {
	echo '<div class="container fluid-widget">';
	dynamic_sidebar('home_bottom_fluid');
	dynamic_sidebar('all_bottom_fluid');
	echo '</div>';
}
?>
<?php get_footer();
