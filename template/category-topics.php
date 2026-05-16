<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2021-06-19 12:21:13
 */
 
get_header(); ?>
<?php if (function_exists('dynamic_sidebar')) {
	echo '<div class="container fluid-widget">';
	dynamic_sidebar('all_top_fluid');
	dynamic_sidebar('cat_top_fluid');
	echo '</div>';
}
?>
<main role="main" class="container">
	<div class="content-wrap">
		<div class="content-layout">
			<?php if (function_exists('dynamic_sidebar')) {
				dynamic_sidebar('cat_top_content');
			}
			?>
			<?php
			zib_topics_cover();
			echo '<div class="posts-row ajaxpager">';
			zib_ajax_option_menu('topics');
			zib_posts_list();
			zib_paging();
			echo '</div>';
			?>
			<?php if (function_exists('dynamic_sidebar')) {
				dynamic_sidebar('cat_bottom_content');
			}
			?>
		</div>
	</div>
</main>
<?php if (function_exists('dynamic_sidebar')) {
	echo '<div class="container fluid-widget">';
	dynamic_sidebar('cat_bottom_fluid');
	dynamic_sidebar('all_bottom_fluid');
	echo '</div>';
}
?>
<?php get_footer(); ?>