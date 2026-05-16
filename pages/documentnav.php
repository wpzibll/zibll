<?php

/**
 * Template name: Zibll-文档导航
 * Description: Document navigation
 */


global $post;
$post_id = $post->ID;
$this_options = zib_get_post_meta($post_id, 'documentnav_options', true);

$one_cat = !empty($this_options['cat']) ? $this_options['cat'] : 0;
$initial_content = !empty($this_options['initial_content']) ? $this_options['initial_content'] : 'updated_posts';

function zib_this_panel_group()
{
    global $one_cat;
    zib_this_panels($one_cat);
}

$is_on_open = true;
function zib_this_panels($cat_id = '', $data_parent = 'accordion', $class = 'panel')
{
    if (!$cat_id) return;

    $terms = _get_term_hierarchy('category');
    echo '<div class="panel-group posts-panel-group" id="' . $data_parent . '">';

    foreach ((array) $terms[$cat_id] as $child) {
        // echo 'child_id:' . json_encode($child) . '<br>';
        if ($cat_id === $child) {
            continue;
        }
        $cat_obj = get_category($child);

        global $is_on_open;
        if (!empty($terms[$child])) {
            echo '<div class="' . $class . '">';
            echo '<h4 class="panel-title">';
            echo '<a class="panel-toggle' . ($is_on_open ? '' : ' collapsed') . ' text-ellipsis" data-toggle="collapse" data-parent="#' . $data_parent . '" href="#dosc-nav-catid_' . $child . '"><i class="fa fa-angle-right mr10"></i>' . $cat_obj->cat_name . '</a>';
            echo '</h4>';
            echo '<div id="dosc-nav-catid_' . $child . '" class="panel-collapse' . ($is_on_open ? ' collapse in' : ' collapse') . '">';
            echo '<div class="category">';
            $is_on_open = false;
            zib_this_panels($child, 'dosc-cat-nav-panel-' . $child, 'panel panel-child');
            echo '</div>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<a class="cat-load text-ellipsis" cat-id="' . $child . '" href="javascript:;" title="' . $cat_obj->cat_name . '"><i class="loading"></i>' . $cat_obj->cat_name . '</a>';
        }
    }
    echo '</div>';
}

get_header();
?>
<main class="container">
    <div class="content-wrap">
        <div class="content-layout">
            <?php echo zib_get_page_header(); ?>

            <div class="row">
                <div class="col-sm-4">
                    <div class="theme-box zib-widget dosc-nav document-nav">
                        <?php zib_this_panel_group(); ?>
                    </div>
                </div>
                <div class="col-sm-8" role="main">
                    <div class="theme-box zib-widget document-search relative-h">
                        <?php
                        $args = array(
                            'class' => 'document-search',
                            'show_keywords' => false,
                            'show_history' => true,
                        );
                        zib_get_search_box($args, true);
                        ?>
                        <div style="z-index: 1;" class="search-loading absolute main-bg flex jc">
                            <i class="loading mr10 em12"></i> 正在搜索
                        </div>
                    </div>
                    <div class="document-nav-container" one-cat="<?php echo $one_cat; ?>">
                        <?php
                        if ($initial_content == 'page_content') {
                            while (have_posts()) : the_post();
                                echo '<div class="nopw-sm box-body theme-box radius8 main-bg main-shadow">';
                                echo '<article class="article wp-posts-content">';
                                the_content();
                                echo '</article>';
                                echo '</div>';
                            endwhile;
                        } else {

                            $args = array(
                                'cat' => $one_cat,
                                'order' => 'DESC',
                                'orderby' => 'modified',
                                'showposts' => 12,
                                'no_found_rows' => true, //不需要分页，不查询分页需要的总数量
                                'ignore_sticky_posts' => 1
                            );

                            if ($initial_content == 'date_posts') {
                                $args['orderby'] = 'date';
                            } elseif ($initial_content == 'views_posts') {
                                $args['orderby'] = 'meta_value_num';
                                $args['meta_query'] = array(
                                    array(
                                        'key' => 'views',
                                        'order' => 'DESC'
                                    )
                                );
                            }

                            $new_query = new WP_Query($args);
                            echo zib_get_documentnav_posts($new_query);
                        }

                        ?>
                    </div>
                </div>
            </div>

            <?php comments_template('/template/comments.php', true); ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php
get_footer();
