<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime: 2021-11-30 18:52:41
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|工具函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//输出页面模板主要内容
function zib_bbs_page_template($type = 'home')
{
    do_action('bbs_locate_template');
    do_action('bbs_locate_template_' . $type);

    if (!_pz('bbs_s', true)) {
        //如果为开启论坛功能，则直接404
        bbs_locate_template_nocan_edit();
    }

    get_header();
    $page_type = $type;
?>
    <main id="forum">
        <div class="container fluid-widget">
            <?php dynamic_sidebar('bbs_' . $page_type . '_top_fluid'); ?>
        </div>
        <?php do_action('bbs_' . $page_type . '_page_header'); ?>
        <div class="container">
            <div class="content-wrap">
                <div class="content-layout">
                    <?php
                    do_action('bbs_' . $page_type . '_page_content');
                    ?>
                </div>
            </div>
            <div class="<?php echo apply_filters('bbs_' . $page_type . '_sidebar_class', 'sidebar'); ?>">
                <?php dynamic_sidebar('bbs_' . $page_type . '_sidebar'); ?>
                <?php do_action('bbs_' . $page_type . '_page_sidebar'); ?>
            </div>
        </div>
        <div class="container fluid-widget">
            <?php dynamic_sidebar('bbs_' . $page_type . '_bottom_fluid'); ?>
        </div>
        <?php do_action('bbs_' . $page_type . '_page_footer'); ?>
    </main>
<?php
    get_footer();
}

//前台加载css和js文件
function zib_bbs_enqueue_script()
{
    wp_enqueue_script('forums', ZIB_BBS_ASSETS_URI . '/js/main.min.js', array(), THEME_VERSION, true);
    wp_enqueue_style('_forums', ZIB_BBS_ASSETS_URI . '/css/main.min.css', array(), THEME_VERSION, 'all');
}
if (_pz('bbs_s', true) && !is_admin()) {
    add_action('wp_enqueue_scripts', 'zib_bbs_enqueue_script');
}

/**
 * @description: 骨架屏模板
 * @param {*}
 * @return {*}
 */
function zib_bbs_get_placeholder($type = 'posts_detail_alone', $i = 4)
{

    $placeholder  = '<div class="user-info flex ac mb6"><div class="avatar-img"><div style="width: 100%;height: 100%;" class="placeholder radius"></div></div><div class="flex1 ml10"><div class="placeholder   k1" style="height: 15px;"></div><div class="placeholder s1 mt6"></div></div></div><h2 style="height: 40px;margin-top: 0;" class="forum-title placeholder k2"></h2><div class="flex ac jsb mt10"><i><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i><i><i class="placeholder s1 ml10"></i></i></div>';
    $placeholders = array(
        'posts_detail_alone'     => '<posts class="forum-posts detail alone">' . $placeholder . '</posts>',
        'posts_detail'           => '<posts class="forum-posts detail">' . $placeholder . '</posts>',
        'posts'                  => '<posts class="forum-posts detail">' . $placeholder . '</posts>',
        'posts_mini'             => '<posts class="forum-posts mini"><div class="mr20 forum-user"><div class="avatar-img forum-avatar"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><span class="placeholder s1 ml10 show-sm" style="margin-top: -3px;"></span></div><div class="entry-info flex xx jsb"><h2 class="forum-title"><div class="placeholder k1"></div></h2><div class="flex ac jsb item-meta"><div class="placeholder s1"></div><div class="placeholder s1"></div></div></div></posts>',
        'posts_minimalism'       => '<posts class="forum-posts minimalism"><div class="placeholder k2" style="width: 100%;"></div></posts>',
        'posts_minimalism_alone' => '<posts class="forum-posts minimalism alone"><div class="placeholder k2" style="width: 100%;"></div></posts>',
        'posts_mini_alone'       => '<posts class="forum-posts mini alone"><div class="mr20 forum-user"><div class="avatar-img forum-avatar"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><span class="placeholder s1 ml10 show-sm" style="margin-top: -3px;"></span></div><div class="entry-info flex xx jsb"><h2 class="forum-title"><div class="placeholder k1"></div></h2><div class="flex ac jsb item-meta"><div class="placeholder s1"></div><div class="placeholder s1"></div></div></div></posts>',
        'home_plate'             => '<div class="panel-plate mb20"><i class="placeholder s1" style="height: 20px;"></i><div class="flex scroll-plate"><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div></div></div><div class="panel-plate mb20"><i style="height: 20px;" class="placeholder s1"></i><div class="plate-lists"><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div><div class="plate-item"><div class="plate-thumb"><div style="height: 100%;" class="placeholder radius"></div></div><div class="item-info"><div class="placeholder k1"></div><div style="height: 30px;" class="placeholder k2 hide-sm mt10"></div><div class="placeholder s1 mt10"></div></div></div></div></div>',
    );
    $placeholders['home']  = $placeholders['posts_detail_alone'];
    $placeholders['plate'] = $placeholders['posts_mini'];

    if (!isset($placeholders[$type])) {
        return '';
    }

    $html = str_repeat($placeholders[$type], $i);

    return $html;
}

//加载404页面
function bbs_locate_template_nocan_edit($centent = '<p class="muted-2-color box-body separator" style="margin:60px 0;">未找到相关内容</p>', $args = array())
{
    zib_die_page($centent, $args);
}
