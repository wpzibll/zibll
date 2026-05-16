<?php
    /*
     * @Author        : Qinver
     * @Url           : zibll.com
     * @Date          : 2020-09-29 13:18:36
 * @LastEditTime : 2025-11-24 13:55:09
     * @Email         : 770349780@qq.com
     * @Project       : Zibll子比主题
     * @Description   : 一款极其优雅的Wordpress主题
     * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
     * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
     */

    get_header();
?>

<main>
    <div class="container">
        <?php
            zib_author_header();
            if (function_exists('dynamic_sidebar')) {
                echo '<div class="fluid-widget">';
                dynamic_sidebar('all_top_fluid');
                dynamic_sidebar('author_top_fluid');
                echo '</div>';
            }
            zib_author_content();
        ?>
    </div>
    <?php if (function_exists('dynamic_sidebar')) {
            echo '<div class="container fluid-widget">';
            dynamic_sidebar('author_bottom_fluid');
            dynamic_sidebar('all_bottom_fluid');
            echo '</div>';
        }
    ?>
</main>
<?php get_footer(); ?>