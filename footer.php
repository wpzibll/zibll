<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2023-09-16 13:53:11
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

?>
<footer class="footer">
	<?php if (function_exists('dynamic_sidebar')) {
    dynamic_sidebar('all_footer');
}?>
	<div class="container-fluid container-footer">
		<?php do_action('zib_footer_conter');?>
	</div>
</footer>
<?php
wp_footer();
?>

</body>
</html>