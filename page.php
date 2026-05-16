<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2024-10-11 15:13:06
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

$page_id         = get_queried_object_id();
$header_style    = zib_get_page_header_style();
$content_style   = zib_get_page_content_style();
$container_class = 'container';
$container_class .= $content_style ? ' page-content-' . $content_style : '';
//小工具容器
$widgets_register_container = array();
if (get_post_meta($page_id, 'widgets_register', true)) {
    $widgets_register_container = (array) get_post_meta($page_id, 'widgets_register_container', true);
}

get_header();
//顶部全宽度小工具
if ($widgets_register_container && in_array('top_fluid', $widgets_register_container)) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('page_top_fluid_' . $page_id);
    echo '</div>';
}

?>
<main class="<?php echo $container_class .' page-id-'.$page_id ; ?>">
    <div class="content-wrap">
        <div class="content-layout">

            <?php
            if ($header_style != 1) {
                echo zib_get_page_header();
            }

            if ($widgets_register_container && in_array('top_content', $widgets_register_container)) {
                dynamic_sidebar('page_top_content_' . $page_id);
            }
            ?>
            <?php if ($content_style != 'not') { ?>
                <article class="article page-article main-bg theme-box box-body radius8 main-shadow">
                    <?php if ($header_style == 1) {
                        echo zib_get_page_header();
                    } ?>
                    <div class="wp-posts-content">
                        <?php the_content();
                        wp_link_pages(
                            array(
                                'before' => '<p class="text-center post-nav-links radius8 padding-6">',
                                'after'  => '</p>',
                            )
                        ); ?>
                    </div>
                </article>
            <?php } elseif ($header_style == 1) {
                echo zib_get_page_header();
            } ?>
            <?php
            comments_template('/template/comments.php', true);
            if ($widgets_register_container && in_array('bottom_content', $widgets_register_container)) {
                dynamic_sidebar('page_bottom_content_' . $page_id);
            }
            ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php

if ($widgets_register_container && in_array('bottom_fluid', $widgets_register_container)) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('page_bottom_fluid_' . $page_id);
    echo '</div>';
}

get_footer();
