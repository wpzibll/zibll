<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-10-11 17:22:02
 * @LastEditTime: 2024-06-19 13:15:15
 */

/**
 * Template name: Zibll-用户身份认证
 * Description:   用户身份认证申请
 */


get_header();
$post_id = get_queried_object_id();
$header_style = zib_get_page_header_style();

?>
<main class="container">
    <div class="content-wrap">
        <div class="content-layout">
            <?php while (have_posts()) : the_post(); ?>
                <?php if ($header_style != 1) {
                    echo zib_get_page_header();
                } ?>
                <div class="nopw-sm box-body theme-box radius8 main-bg main-shadow">
                    <?php if ($header_style == 1) {
                        echo zib_get_page_header();
                    } ?>
                    <article class="article wp-posts-content">
                        <?php the_content();
                        wp_link_pages(
                            array(
                                'before'           => '<p class="text-center post-nav-links radius8 padding-6">',
                                'after'            => '</p>',
                            )
                        ); ?>
                    </article>
                </div>
            <?php endwhile; ?>
            <div class="theme-box radius8 main-bg main-shadow relative-h">
                <?php
                $user_id = get_current_user_id();
                $auth_badge = zib_get_user_auth_badge($user_id, 'mr6');
                $icon = zib_get_svg('user-auth');

                if ($auth_badge) {
                    //我已经是认证用户
                    $header = '<div style="height: 160px;" class="colorful-bg c-blue flex jc"><div class="colorful-make"></div><div class="text-center"><div class="em3x">' . $icon . '</div><div class="mt10 em12 padding-w10">我的认证信息</div></div></div>';
                    $auth_info = zib_get_user_meta($user_id, 'auth_info', true);

                    $name = isset($auth_info['name']) ? $auth_info['name'] : '官方认证';

                    $desc = isset($auth_info['desc']) ? '<div class="muted-2-color mt10">' . $auth_info['desc'] . '</div>' : '';
                    $time = isset($auth_info['time']) ? '<div class="muted-2-color mt10">认证时间：' . $auth_info['time'] . '</div>' : '';

                    $html = '<div style="padding: 30px 0;" class="colorful-bg c-blue flex ac jse">';
                    $html .= '<div class="colorful-make"></div>';
                    $html .= '<div class="text-center flex0">' . zib_get_svg('user-auth', null, 'icon em3x') . '<div class="mt10 padding-w10">我的认证信息</div></div>';

                    $html .= '<div class="box-body">';
                    $html .= '<div class="">' . $icon . '<b class="ml3">' . $name . '</b></div>';
                    $html .= $desc;
                    $html .= $time;
                    $html .= '</div>';
                    $html .= '</div>';

                    echo $html;
                } else {

                    $header = '<div class="colorful-make"></div><div class="text-center flex ac" style="height: 100px;"><div class="em2x">' . $icon . '</div><div class="em12 padding-w10">申请身份认证</div></div>';
                    $apply =  zib_get_user_auth_apply_link('colorful-bg c-blue flex jc', $header);

                    echo $apply;
                }

                ?>
            </div>
            <?php comments_template('/template/comments.php', true); ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>
<?php
get_footer();
