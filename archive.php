<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2023-10-12 12:05:48
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (is_tax('topics')) {
	get_template_part('template/category-topics');
	return;
}

get_header();
$pagedtext = '';
if ($paged && $paged > 1) {
	$pagedtext = ' <small>第' . $paged . '页</small>';
}
?> 
<main role="main" class="container">
	<div class="content-wrap">
		<div class="content-layout">
			<div class="main-bg text-center box-body radius8 main-shadow theme-box">
				<h4 class="title-h-center">
					<?php
					if (is_day()) echo the_time('Y年m月j日');
					elseif (is_month()) echo the_time('Y年m月');
					elseif (is_year()) echo the_time('Y年');
					?>的文章<small class="ml10"><?php echo $pagedtext ?></small>
				</h4>
			</div>
			<?php
			echo '<div class="posts-row ajaxpager">';
			zib_posts_list();
			zib_paging();
			echo '</div>';
			?>
		</div>
	</div>
	<?php get_sidebar(); ?>
</main>

<?php get_footer(); ?>