<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-09 22:11:51
 * @LastEditTime: 2023-10-26 21:35:37
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|消息中心的页面模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//建议搜索引擎不抓取
add_filter('wp_robots', 'zib_robots_no_robots');

$page_type = 'msg_center';
do_action('locate_template_' . $page_type);
get_header();
?>
<main class="main-min-height">
    <?php do_action($page_type . '_page_header');?>
    <div class="container">
        <?php do_action($page_type . '_page_content');?>
    </div>
    <?php do_action($page_type . '_page_footer');?>
</main>
<?php
get_footer();
