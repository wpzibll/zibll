<?php

/**
 * Template name: Zibll-资源下载
 * Description:   download page
 */

if (empty($_GET['post'])) {
    get_header();
    get_template_part('template/content-404');
    get_footer();
    exit;
}
$post_id = (int) $_GET['post'];

function zibpay_get_down_html($post_id)
{
    $pay_mate = get_post_meta($post_id, 'posts_zibpay', true);
    $html     = '';
    if (empty($pay_mate['pay_type']) || empty($pay_mate['pay_type']) || $pay_mate['pay_type'] != '2') {
        return get_template_part('template/content-404');
    }

    // 查询是否已经购买
    $paid_obj    = zibpay_is_paid($post_id);
    $posts_title = get_the_title($post_id) . zib_get_post_meta($post_id, 'subtitle', true);
    $pay_title   = !empty($pay_mate['pay_title']) ? $pay_mate['pay_title'] : $posts_title;
    $pay_doc     = !empty($pay_mate['pay_doc']) ? $pay_mate['pay_doc'] : '';
    $pay_details = !empty($pay_mate['pay_details']) ? $pay_mate['pay_details'] : '';
    if ($paid_obj) {
        //已经购买直接显示下载盒子

        $paid_name = zibpay_get_paid_type_name($paid_obj['paid_type']);
        $paid_name = '<b class="badg jb-red mr6" style="font-size: 12px; padding: 2px 10px; line-height: 1.4; "><i class="fa fa-check mr6" aria-hidden="true"></i>' . $paid_name . '</b>';

        $pay_extra_hide = !empty($pay_mate['pay_extra_hide']) ? '<div class="pay-extra-hide">' . $pay_mate['pay_extra_hide'] . '</div>' : '';

        $dowmbox = '<div style="margin-bottom:3em;">' . zibpay_get_post_down_buts($pay_mate, $paid_obj['paid_type'], $post_id) . '</div>';

        if ($paid_obj['paid_type'] == 'free' && _pz('pay_free_logged_show') && !is_user_logged_in()) {
            $dowmbox        = '<div class="alert jb-yellow em12" style="margin: 2em;"><b>免费资源，请登录后下载</b></div>';
            $pay_extra_hide = zib_get_user_singin_page_box('pay-extra-hide', 'Hi！请先登录');
        }

        $html = '<div class="article-header theme-box"><div class="article-title"><a href="' . get_permalink($post_id) . '#posts-pay">' . $pay_title . '</a></div>' . $paid_name . '</div>';

        $html .= '<div>' . $pay_doc . '</div>';
        $html .= '<div class="muted-2-color em09" style="margin: 2em 0;">' . $pay_details . '</div>';

        $html .= '<div style="margin-bottom: 2em;" class="pay-box">' . $dowmbox . $pay_extra_hide . '</div>';
    } else {
        //未购买
        $html = '<div class="article-header theme-box"><div class="article-title"><a href="' . get_permalink($post_id) . '#posts-pay">' . $pay_title . '</a></div></div>';

        $html .= '<div>' . $pay_doc . '</div>';
        $html .= '<div class="muted-2-color em09" style="margin: 2em 0;">' . $pay_details . '</div>';
        $html .= '<div class="alert jb-red em12" style="margin: 2em;"><b>暂无下载权限</b></div>';
        $html .= '<a style="margin-bottom: 2em;" href="' . get_permalink($post_id) . '#posts-pay" class="but jb-yellow padding-lg"><i class="fa fa-long-arrow-left" aria-hidden="true"></i><span class="ml10">返回文章</span></a>';
    }

    return '<div  class="pay-box">' . $html . '</div>';
}

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
<style>
    .but-download>.but,
    .but-download>span {

        min-width: 200px;
        padding: .5em;
        margin-top: 10px;
    }

    .pay-extra-hide {
        background: var(--muted-border-color);
        display: block;
        margin: 10px;
        padding: 20px;
        color: var(--muted-color);
        border-radius: 4px;
    }
</style>
<main class="<?php echo $container_class; ?>">
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
            <?php if ($content_style != 'not') {?>
                <article class="article page-article main-bg theme-box box-body radius8 main-shadow">
                    <?php if ($header_style == 1) {
    echo zib_get_page_header();
}?>
                    <?php
echo zibpay_get_down_html($post_id);
    $page_links_content_s = get_post_meta(get_queried_object_id(), 'page_show_content', true);
    if ($page_links_content_s) {
        echo '<div class="wp-posts-content">';

        the_content();
        wp_link_pages(
            array(
                'before' => '<p class="text-center post-nav-links radius8 padding-6">',
                'after'  => '</p>',
            )
        );
        echo '</div>';
    } ?>

                </article>
            <?php } elseif ($header_style == 1) {
    echo zib_get_page_header();
}?>
            <?php
if ($widgets_register_container && in_array('bottom_content', $widgets_register_container)) {
    dynamic_sidebar('page_bottom_content_' . $page_id);
}
?>
        </div>
    </div>
    <?php get_sidebar();?>
</main>
<?php

if ($widgets_register_container && in_array('bottom_fluid', $widgets_register_container)) {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('page_bottom_fluid_' . $page_id);
    echo '</div>';
}

get_footer();
exit;
