<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime : 2025-12-29 11:51:52
 */

$s    = trim(esc_attr(strip_tags($s)));
$cat  = !empty($_REQUEST['trem']) ? trim(strip_tags($_REQUEST['trem'])) : '';
$type = $search_type;
$user = !empty($_REQUEST['user']) ? (int) $_REQUEST['user'] : '';

$search_types     = zib_get_search_types();
$search_type_name = $search_types[$type];

$new_title = $s . ' ' . '搜索' . $search_type_name;
if ($paged > 1) {
    $new_title .= _get_delimiter() . '第' . $paged . '页';
}
$new_title .= zib_get_delimiter_blog_name();

//保存搜索历史
zib_save_history_search($s . ($type ? '&type=' . $type : ''));

if ($type != 'user' && $wp_query->have_posts() && (int)$paged <= 1) { 
    zib_update_search_keywords($s . ($type ? '&type=' . $type : ''));
}

get_header();
?>
<?php if (function_exists('dynamic_sidebar')) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('all_top_fluid');
    dynamic_sidebar('search_top_fluid');
    echo '</div>';
}

?>
<main class="container">
    <div class="content-wrap">
        <div class="content-layout">
            <?php if (function_exists('dynamic_sidebar')) {
                dynamic_sidebar('search_top_content');
            }
            ?>

            <?php
            $args = array(
                'class'      => 'main-search page-search-box',
                'show_posts' => false,
                'in_cat'     => $cat,
                'in_type'    => $type,
                'in_user'    => $user,
                's'          => $s,
            );
            echo '<div class="zib-widget"' . ($paged < 2 ? ' win-ajax-replace="search"' : '') . '>';
            zib_get_main_search($args, true);
            echo '</div>';
            ?>
            <?php
            zib_search_content();
            ?>
            <?php if (function_exists('dynamic_sidebar')) {
                dynamic_sidebar('search_bottom_content');
            }
            ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php if (function_exists('dynamic_sidebar')) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('search_bottom_fluid');
    dynamic_sidebar('all_bottom_fluid');
    echo '</div>';
}
?>
<?php get_footer();
