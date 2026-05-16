<?php
    /*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2025-02-16 21:09:53
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

    wp_reset_postdata();
    if (zib_is_docs_mode()) {
        get_template_part('template/single-dosc');
        return;
    }
    get_header();
?>
<?php if (function_exists('dynamic_sidebar')) {
        echo '<div class="container fluid-widget">';
        dynamic_sidebar('all_top_fluid');
        dynamic_sidebar('single_top_fluid');
        echo '</div>';
    }
?>
<main role="main" class="container">
    <div class="content-wrap">
        <div class="content-layout">
            <?php
                //头部小工具
                if (function_exists('dynamic_sidebar')) {
                    dynamic_sidebar('single_top_content');
                }
                //主内容
                zib_single();

                //评论模块
                comments_template('/template/comments.php', true);

                //底部小工具
                if (function_exists('dynamic_sidebar')) {
                    dynamic_sidebar('single_bottom_content');
                }
            ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php
    if (function_exists('dynamic_sidebar')) {
        echo '<div class="container fluid-widget">';
        dynamic_sidebar('single_bottom_fluid');
        dynamic_sidebar('all_bottom_fluid');
        echo '</div>';
    }

get_footer();