<?php

/**
 * Template name: Zibll-文章归档
 * Description:   A archives page
 */

get_header();
$post_id = get_queried_object_id();
$header_style = zib_get_page_header_style($post_id);
?>
<main class="container">
    <div class="content-wrap">
        <div class="content-layout">
        <?php  if($header_style != 1){echo zib_get_page_header($post_id);}?>
            <div class="theme-box radius8">
            <?php  if($header_style == 1){ echo zib_get_page_header($post_id);}?>
                <article>
                    <?php
                    $previous_year = $year = 0;
                    $previous_month = $month = 0;
                    $ul_open = false;

                    $myposts = get_posts('numberposts=-1&orderby=post_date&order=DESC');

                    foreach ($myposts as $post) :
                        setup_postdata($post);

                        $year = mysql2date('Y', $post->post_date);
                        $month = mysql2date('n', $post->post_date);
                        $day = mysql2date('j', $post->post_date);

                        if ($year != $previous_year || $month != $previous_month) :
                            if ($ul_open == true) :
                                echo '</ul></div>';
                            endif;

                            echo '<div class="zib-widget"><h4 class="text-center title-h-center">';
                            echo the_time('Y年M');
                            echo '</h4>';
                            echo '<ul class="list-inline">';
                            $ul_open = true;

                        endif;

                        $previous_year = $year;
                        $previous_month = $month;
                    ?>
                        <li class="author-set-left muted-color">
                            <time><?php the_time('j'); ?>日</time>
                        </li>
                        <li class="author-set-right">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?> </a>
                            <span class="muted-2-color ml6"><?php echo zib_get_svg('view') . get_post_view_count($before = '', $after = '') ?></span>
                            <?php comments_number('', '<span class="muted-2-color ml6">' . zib_get_svg('comment') . '1</span>', '<span class="muted-2-color ml6">' . zib_get_svg('comment') . '%</span>'); ?>
                            <?php $like = get_post_meta($post->ID, 'like', true);
                            echo $like ? '<span class="muted-2-color ml6">' . zib_get_svg('like') . $like . '</span>' : ''; ?>
                        </li>
                    <?php endforeach;
                        wp_reset_query();
                        wp_reset_postdata();
                    ?>
                    </ul>
                </article>
            </div>
            <?php comments_template('/template/comments.php', true); ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>

<?php

get_footer();
