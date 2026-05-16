<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-02-24 17:11:32
 * @LastEditTime : 2025-12-28 21:48:59
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//获取商家的链接
function zib_shop_get_author_url($author_id)
{
    return zib_get_user_home_url($author_id, array('tab' => 'product'));
}

//获取商家的名称带链接
function zib_shop_get_author_name_link($author_id, $class = '')
{

    $url          = zib_shop_get_author_url($author_id);
    $avatar       = zib_get_avatar_box($author_id, 'avatar-img', false, true);
    $display_name = get_the_author_meta('display_name', $author_id);

    return '<a class="display-name text-ellipsis ' . $class . '" href="' . $url . '">' . $avatar . $display_name . '</a>';
}

//获取商家客服的链接
function zib_shop_get_author_contact_link($author_id, $class = 'but', $text = '')
{
    $options = _pz('shop_author_contact_opt', []);

    $_svg = zib_get_svg('manual-service');
    $text = $text ? $text : '<icon>' . $_svg . '</icon><text>客服</text>';

    $is_more = false;
    if (!empty($options['more']) && is_array($options['more'])) {
        foreach ($options['more'] as $more) {
            if ($more['name']) {
                $is_more = true;
                break;
            }
        }
    }

    if ($is_more) {
        $args = array(
            'new'           => true,
            'tag'           => 'a',
            'class'         => $class,
            'data_class'    => 'modal-mini',
            'height'        => 240,
            'mobile_bottom' => true,
            'text'          => $text,
            'query_arg'     => array(
                'action'    => 'author_contact_modal',
                'author_id' => $author_id,
            ),
        );
        return zib_get_refresh_modal_link($args);
    } else {
        $msg_s = !empty($options['msg_s']) && _pz('message_s', true) && _pz('private_s', true);
        if ($msg_s) {
            return Zib_Private::get_but($author_id, $text, $class); //私信
        }
        return '';
    }
}

function zib_shop_get_author_addresses($author_id = 0)
{
    return zib_shop_get_user_addresses($author_id, true);
}

//获取商家发布的商品总数量
function zib_shop_get_author_product_count($author_id, $status = 'publish', $cut = true)
{
    if (!$author_id) {
        return 0;
    }

    $count = zib_get_user_post_count($author_id, $status, 'shop_product');
    return $cut ? _cut_count($count) : $count;
}

//判断是否是商家
function zib_shop_is_shop_author($author_id)
{
    //目前只是用了商品数量来判断是否是商家
    return zib_shop_get_author_product_count($author_id, 'publish', false) > 0;
}

//用户个人主页显示帖子
function zib_shop_author_main_tab_product($tab = array(), $author_id = 0)
{
    if (!zib_shop_is_shop_author($author_id)) {
        //不是商家，不显示商品tab
        return [];
    }

    return array(
        'title'         => '商品<count class="opacity8 ml3">' . zib_shop_get_author_product_count($author_id) . '</count>',
        'content_class' => 'product-lists-row',
        'route'         => true,
        'loader'        => zib_shop_get_lists_card_placeholder(_pz('shop_list_opt', [], 'list_style')),
    );
}
add_filter('author_main_tab_product', 'zib_shop_author_main_tab_product', 10, 2);

//商家主页商品tab内容
function zib_shop_author_tab_content_product()
{
    global $wp_query;
    $curauth = $wp_query->get_queried_object();
    if (empty($curauth->ID)) {
        return;
    }
    $author_id = $curauth->ID;

    $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'date';
    $order   = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'DESC';
    $status  = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 'publish';

    $orderby_lists = zib_shop_get_orderby_lists($orderby, $order);
    $orderby_lists = '<div class="shop-cat-filter-child ajax-item-header">' . $orderby_lists . '</div>';

    $html = $orderby_lists . zib_shop_get_user_shop_product_lists($author_id, 1, $orderby, $status);
    return $html;
}
add_action('main_author_tab_content_product', 'zib_shop_author_tab_content_product', 10);

//用户个人主页显示收藏
function zib_shop_favorite_lists_shop_product($content = '', $author_id = 0)
{
    $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'post__in';
    return zib_shop_get_user_shop_product_lists($author_id, 1, $orderby, 'favorite');
}
add_filter('author_favorite_lists_shop_product', 'zib_shop_favorite_lists_shop_product', 10, 2);

function zib_shop_author_favorite_types_filter($favorite_args, $author_id)
{
    $favorite_args['shop_product'] = [
        'name'    => '商品',
        'count'   => zib_shop_get_user_favorite_product_count($author_id),
        'orderby' => [
            'modified'       => '最近更新',
            'views'          => '最多查看',
            'score'          => '最高评分',
            'favorite_count' => '最多收藏',
            'sales_volume'   => '销售数量',
        ],
    ];
    return $favorite_args;
}
add_filter('author_favorite_types', 'zib_shop_author_favorite_types_filter', 10, 2);

//用户个人主页收藏数量
function zib_shop_author_tab_favorite_count($count, $author_id)
{
    $favorite_count = zib_shop_get_user_favorite_product_count($author_id, false);
    return $count + $favorite_count;
}
add_filter('author_tab_favorite_count', 'zib_shop_author_tab_favorite_count', 10, 2);
