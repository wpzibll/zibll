<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime: 2021-11-08 16:26:57
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|论坛首页模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

 //建议搜索引擎不抓取
add_filter('wp_robots', 'zib_robots_no_robots');

$page_type = 'posts_edit';
do_action('bbs_locate_template');
do_action('bbs_locate_template_' . $page_type);

get_header();
?>
<main id="forum">
    <?php do_action('bbs_' . $page_type . '_page_header');?>
    <form id="bbs-posts-edit">
        <div class="container">
            <div class="content-wrap">
                <div class="content-layout">
                    <?php do_action('bbs_' . $page_type . '_page_content');?>
                </div>
            </div>
            <div class="sidebar show-sidebar">
                <?php do_action('bbs_' . $page_type . '_page_sidebar');?>
            </div>
        </div>
    </form>
</main>
<?php
get_footer();