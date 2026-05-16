<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2025-02-16 21:10:36
 * @LastEditTime: 2025-10-17 22:06:55
 * @Email: 770349780@qq.com
 * @Project: Zibll子比主题
 * @Description: 商城功能
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 */

//加载页面文件

zib_require(array(
    'template',
    'home',
    'product',
    'comment',
    'single',
    'lists',
    'term',
    'discount',
    'cat',
    'tag',
    'user',
    'user-center',
    'author',
    'cart',
    'order',
    'shipping',
    'after-sale',
    'pay',
    'vue',
    'msg',
), false, ZIB_SHOP_REQUIRE_URI . 'inc/');

function zib_shop_get_cart_url()
{
    if (get_option('permalink_structure')) {
        $rewrite_slug = trim(_pz('shop_cart_rewrite_slug'));
        $rewrite_slug = $rewrite_slug ? $rewrite_slug : 'cart';

        return home_url($rewrite_slug);
    }

    return add_query_arg('shop_cart', '1', home_url());
}

/**
 * @description: 分页按钮统一接口
 * @param {*} $count_all 总数
 * @param {*} $ice_perpage 每页数量
 * @param {*} $page 当前页
 * @param {*} $ajax_url 加载地址
 * @return {*} html
 */
function zib_shop_get_paginate($count_all, $page = 0, $ice_perpage = 0, $ajax_url = false, $type = null, $ias = 'auto')
{

    global $wp_rewrite;
    $shop_list_opt = _pz('shop_list_opt');

    $ice_perpage      = $ice_perpage ? $ice_perpage : ($shop_list_opt['count'] ?? 12);
    $type             = $type ? $type : ($shop_list_opt['paginate'] ?? 'ajax');
    $paginate_ias_max = $shop_list_opt['ias_max'] ?? 3;
    $paginate_ias_s   = $shop_list_opt['ias_s'] ?? true;

    if ($count_all < $ice_perpage) {
        return;
    }

    if (!$page) {
        $page = zib_get_the_paged();
    }

    if (!$ajax_url) {
        $ajax_url = zib_url_del_paged(zib_get_current_url());

        if ($wp_rewrite->using_permalinks()) {
            $url_parts = explode('?', $ajax_url);
            if (isset($url_parts[0])) {
                $url_base = rtrim($url_parts[0], '/\\') . '/' . $wp_rewrite->pagination_base . '/%#%';
                $url_base .= isset($url_parts[1]) ? '?' . $url_parts[1] : '';
            } else {
                $url_base = rtrim($ajax_url, '/\\') . '/' . $wp_rewrite->pagination_base . '/%#%';
            }
        } else {
            $url_base = add_query_arg('paged', '%#%', $ajax_url);
        }
    } else {
        $ajax_url = preg_replace("/\/$wp_rewrite->pagination_base\/\d*/", '', $ajax_url);
        $url_base = add_query_arg('paged', '%#%', $ajax_url);
    }

    $next_class = 'next-page ajax-next';
    if ('ajax_lists' === $type || 'ajax' === $type) {
        //AJAX追加列表翻页模式
        $total_pages = ceil($count_all / $ice_perpage);
        $con         = '';
        if ($total_pages > $page) {
            $nex = _pz('ajax_trigger', '加载更多');

            $href = str_replace('%#%', $page + 1, $url_base);

            //自动加载下一页
            $ias_attr = ' class="' . $next_class . '"';
            if ($ias !== 'close') {
                $ias_max  = $ias === 'auto' ? $paginate_ias_max : (int) $ias;
                $ias_attr = ($paginate_ias_s && ($page <= $ias_max || !$ias_max)) ? ' class="' . $next_class . ' lazyload" lazyload-action="ias"' : ' class="' . $next_class . '"';
            }

            $con .= '<div class="text-center theme-pagination ajax-pag"><div' . $ias_attr . '>';
            $con .= '<a href="' . esc_url($href) . '">' . $nex . '</a>';
            $con .= '</div></div>';
        }
        return $con;
    } else {
        //数字翻页模式
        $args = array(
            'url_base'     => $url_base,
            'link_sprintf' => '<a class="' . $next_class . ' %s" ajax-replace="true" href="%s">%s</a>',
            'total'        => $count_all, //总计条数
            'current'      => $page, //当前页码
            'page_size'    => $ice_perpage, //每页几条
            'class'        => 'pagenav ajax-pag',
        );

        return zib_get_paginate_links($args);
    }
}

function zib_shop_get_star_badge($score, $class = 'inflex ac')
{
    $score = (float) $score;
    $score = $score > 5 ? 5 : $score;
    $score = $score < 0 ? 0 : $score;

    //一共5个星星
    $star_html = '';
    for ($i = 0; $i < 5; $i++) {
        $_class = '';
        if ($score >= $i + 1) {
            $_class = 'active';
        } elseif ($score >= $i + 0.5) {
            $_class = 'active-half';
        }

        $star_html .= '<i class="icon-star ' . $_class . '"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-stars"></use></svg></i>';
    }

    return '<div class="stars-badge-box ' . $class . '" data-toggle="tooltip" data-placement="top" title="' . $score . '分">' . $star_html . '</div>';
}

//金额数据格式化，积分int，现金float
function zib_shop_format_price($price, $is_points = false, $to_string = false)
{

    $value = floatval(round((float) $price, ($is_points ? 0 : 2)));
    return $to_string ? (string) $value : $value;
}

//分享卡片数据
function zib_shop_poster_share_data_filter($data, $obj, $id)
{

    if (isset($obj->post_type)) {
        //文章模式：商品分享
        if ('shop_product' == $obj->post_type) {
            $title = trim(strip_tags($obj->post_title));
            $tags  = zib_shop_get_product_discount($obj->ID);
            if (empty($tags)) {
                $tags = zib_shop_get_product_tags($obj);
            }

            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    if (zib_new_strlen($data['tags']) > 26) {
                        break;
                    }
                    $data['tags'] .= $tag['name'] . '、';
                }
            }

            $data['tags'] = trim($data['tags'], '、');

            $start_price = zib_shop_get_product_config($obj->ID, 'start_price'); //初始价格
            $price       = zib_shop_get_product_display_price($obj->ID); //折扣价
            $pay_modo    = zib_shop_get_product_config($obj->ID, 'pay_modo'); //支付方式

            $data['content']        = '';
            $data['title']          = $title;
            $data['type']           = 'product'; //类型
            $data['is_points']      = $pay_modo === 'points'; //是否积分支付
            $data['price']          = (string) $price; //价格
            $data['original_price'] = $start_price > $price ? (string) $start_price : '';
            $data['banner']         = zib_shop_get_product_thumbnail_url($obj);
        }
    }

    return $data;
}
add_filter('poster_share_data', 'zib_shop_poster_share_data_filter', 10, 3);

//为搜索添加新的tpye
function zib_shop_search_types_filter($types)
{
    $types['product'] = '商品';
    return $types;
}
add_filter('search_types', 'zib_shop_search_types_filter');

function zib_shop_search_main_tabs_array_filter($tabs_args)
{
    $tabs_args['product'] = [
        'title'         => '商品',
        'content_class' => 'product-lists-row',
        'route'         => true,
        'loader'        => zib_shop_get_lists_card_placeholder(_pz('shop_list_opt', [], 'list_style')),
    ];

    return $tabs_args;
}
add_filter('search_main_tabs_array', 'zib_shop_search_main_tabs_array_filter');

/**
 * 获取订单物流信息模态框
 * @param array $data 订单数据 必要的数据：express_company_name, express_number, traces, consignee
 * @return string 模态框html
 */
function zib_shop_get_express_modal(array $data)
{
    $express_company_name = $data['express_company_name'] ?? '';
    $express_number       = $data['express_number'] ?? '';
    $traces               = $data['traces'] ?? [];
    $address_data         = $data['address_data'] ?? [];

    //构建物流信息box
    $express_box = '';
    if ($express_number) {
        $express_box .= '<div class="flex ac jsb express-info-header border-bottom padding-h10">
                <div class="flex1">
                    <span class="mr6">' . $express_company_name . '</span>
                    <span class="muted-color">' . $express_number . '</span>
                </div>
                <div class="flex0">
                    <a href="javascript:void(0)" class="muted-2-color" data-clipboard-text="' . $express_number . '" data-clipboard-tag="单号">复制</a>
                </div>
            </div>';
    }

    //构建物流时间线
    $express_timeline = '';
    $showed_state     = [];
    $_i               = 0;
    $_expand_count    = 4; //折叠展开
    foreach ($traces as $trace) {
        $state      = $trace['state'] ?? '';
        $state_html = '';
        if (!in_array($state, $showed_state)) {
            $state_html     = $state ? '<b class="mr6">' . $state . '</b>' : '';
            $showed_state[] = $state;
        }

        $express_timeline .= '<div class="timeline-content' . ($_i >= $_expand_count ? ' hide' : '') . '">
                <div class="timeline-time">' . $state_html . '<span class="em09">' . $trace['time'] . '</span></div>
                <div class="timeline-context px12 muted-2-color">' . zib_shop_handle_express_context($trace['context']) . '</div>
            </div>';
        $_i++;
    }

    if ($_i >= $_expand_count) {
        $express_timeline .= '<div class="timeline-content pointer expand-toggle" closest-selector=".timeline-box" expand-count="' . $_expand_count . '" expand-text="查看全部物流信息" collapse-text="收起更多物流信息" >
                <div class="muted-2-color em09"><span class="btn-text">查看全部物流信息</span><i class="ml6 fa fa-angle-down"></i></div>
            </div>';
    }

    $express_timeline = $express_timeline ? '<div class="timeline-box border-bottom padding-h6 mb10">' . $express_timeline . '</div>' : '';

    //构建收货人信息box
    $consignee_box = '';
    if ($address_data) {
        $consignee_box = '<div class="flex muted-color">
                <div class="icon-header mr10"><i class="fa-fw fa fa-map-marker"></i></div>
                <div class="">
                    <div class=""><span class="mr6 muted-2-color">送至</span><b class="">' . $address_data['city'] . $address_data['address'] . '</b></div>
                    <div class=""><span class="mr6">' . $address_data['name'] . '</span><span class="muted-2-color">' . $address_data['phone'] . '</span></div>
                </div>
            </div>';
    }

    $header = '<div class="border-title touch"><div class="flex jc"><b>物流信息</b></div></div><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';

    $html = $header . '<div class="mini-scrollbar scroll-y max-vh7">' . $express_box . $express_timeline . $consignee_box . '</div>';

    return $html;
}
