<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-02-16 22:11:42
 * @LastEditTime : 2025-06-29 22:12:41
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题|商城小工具模块
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//引入文件
zib_require(array(
    'product',
    'term',
    'other',
), false, ZIB_SHOP_REQUIRE_URI . 'widgets/widgets-');

//注册小工具位置
function zib_shop_register_sidebar()
{

    $args = [
        'product' => [
            'sidebar'      => [
                'name' => '[商城]商品详情页-侧边栏',
                'desc' => '侧边栏位置较小，请添加尺寸较小的模块，侧边栏仅在电脑端显示，同时需对应页面开启侧边栏',
            ],
            'top_fluid'    => [
                'name' => '[商城]商品详情页-上方全宽度',
                'desc' => '显示在商品详情页的商品详情上方，全宽显示',
            ],
            'bottom_fluid' => [
                'name' => '[商城]商品详情页-底部全宽度',
                'desc' => '显示在商品详情页的最底部，全宽显示',
            ],
        ],
        'cart'    => [
            'top_fluid'    => [
                'name' => '[商城]购物车页面-上方全宽度',
                'desc' => '显示在商品详情页的商品详情上方，全宽显示',
            ],
            'bottom_fluid' => [
                'name' => '[商城]购物车页面-底部全宽度',
                'desc' => '显示在商品详情页的最底部，全宽显示',
            ],
        ],
    ];

    foreach ($args as $page_key => $page_v) {
        foreach ($page_v as $d_k => $value) {
            register_sidebar(array(
                'name'          => $value['name'],
                'id'            => 'shop_' . $page_key . '_' . $d_k,
                'description'   => $value['desc'],
                'before_widget' => '<div class="zib-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3>',
                'after_title'   => '</h3>',
            ));
        }
    }

}
add_action('widgets_init', 'zib_shop_register_sidebar');
