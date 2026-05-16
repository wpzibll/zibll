<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime: 2025-10-01 20:49:14
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//定义常量
define('ZIB_SHOP_ASSETS_URI', ZIB_TEMPLATE_DIRECTORY_URI . '/inc/functions/shop/assets'); //本主题
define('ZIB_SHOP_REQUIRE_URI', '/inc/functions/shop/'); //本主题require_once的地址前缀

//引入公共函数资源文件
zib_require(array(
    'inc/public',
    'inc/class.setup',
    'inc/class.init',
), false, ZIB_SHOP_REQUIRE_URI);
$GLOBALS['zib_shop'] = zib_shop::instance();
global $zib_shop;

//引入后台配置文件
if (is_admin()) {
    zib_require(array(
        'admin',
    ), false, ZIB_SHOP_REQUIRE_URI . 'admin/');
}

//引入商城资源文件
if ($zib_shop->s) {
    //开启了商城功能
    zib_require(array(
        'inc/functions',
        'widgets/widgets',
        'action/action',
    ), false, ZIB_SHOP_REQUIRE_URI);

    do_action('zib_shop_init');
} else {
    //未开启商城功能需要执行的函数
    function zib_shop_close_comments_list_table_query_args($args)
    {
        // Keep the native WordPress comments moderation list intact.
        return $args;
    }

    add_filter('comments_list_table_query_args', 'zib_shop_close_comments_list_table_query_args');
}

/**文章短代码 */
function zib_shop_add_shortcode_productbox($atts, $content = null)
{
    global $zib_shop;
    if (!$zib_shop->s) {
        return '';
    }

    extract(shortcode_atts(array(
        'id' => '0',
    ), $atts));
    $con = '';
    if ($id) {
        $post = get_post($id);
        if (!empty($post->ID)) {
            $shop_list_opt           = _pz('shop_list_opt');
            $card_args               = $shop_list_opt['list_style'] ?? [];
            $card_args['style']      = 'small';
            $card_args['show_price'] = 1;
            $card_args['show_desc']  = 0;

            $con = zib_shop_get_product_list_card($card_args, $post);
        }
    }
    if (!$con && is_super_admin()) {
        $con = '<div class="hidden-box"><div class="text-center">[productbox id="' . $id . '"]</div><div class="hidden-text">未找到商品，请重新设置短代码商品ID</div></div>';
    }
    return $con;
}
add_shortcode('productbox', 'zib_shop_add_shortcode_productbox');



