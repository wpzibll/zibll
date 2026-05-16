<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime: 2025-10-23 00:08:03
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|加载页面
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_get_dis_header($dis_id)
{
    $dis      = get_term($dis_id, 'shop_discount');
    $dis_data = zib_shop_get_discount_data($dis);

    $small_badge = $dis_data['small_badge'];
    $small_badge = $small_badge === $dis->name ? '' : '<span class="mb6 badg p2-10 mr6 ' . (!empty($dis_data['important_class']) ? $dis_data['important_class'] : 'jb-red') . '">' . $small_badge . '</span>';

    $discount_type = $dis_data['discount_type'];
    $discount_text = '';
    switch ($discount_type) {
        case 'reduction':
            $discount_text = '立减' . $dis_data['reduction_amount'];
            break;
        case 'discount':
            $discount_text = $dis_data['discount_amount'] . '折';
            break;
    }

    if ($discount_text && $discount_text !== $dis_data['small_badge']) {
        $small_badge .= '<b class="c-red badg p2-10 mb6">' . $discount_text . '</b>';
    }

    $title = $small_badge . '<h1 class="normal-title">' . $dis->name . '</h1>';
    $desc  = $dis->description ? '<div class="page-desc muted-color mt6">' . $dis->description . '</div>' : '';

    //金额限制
    $limit = [];
    if ((float) $dis_data['price_limit'] > 0) {

        $discount_scope = $dis_data['discount_scope'];
        $text           = '';
        if ($discount_scope === 'item') {
            $text = '单价';
        } elseif ($discount_scope === 'product') {
            $text = '商品';
        } elseif ($discount_scope === 'author') {
            $text = '店铺';
        } elseif ($discount_scope === 'order') {
            $text = '跨店';
        }

        $limit[] = '<span class="badg badg-sm c-yellow">' . $text . '满' . $dis_data['price_limit'] . '可用</span>';
    }

    //身份限制
    if ($dis_data['user_limit']) {
        switch ($dis_data['user_limit']) {
            case 'vip':
                $limit[] = '<span class="badg badg-sm c-yellow">VIP可用</span>';
                break;
            case 'vip_2':
                $limit[] = '<span class="badg badg-sm c-yellow">VIP2可用</span>';
                break;
            case 'auth':
                $limit[] = '<span class="badg badg-sm c-yellow">认证用户可用</span>';
                break;
        }
    }

    //时间限制
    $time_countdown = '';
    if (!empty($dis_data['time_limit']) && !empty($dis_data['time_limit_config'])) {
        //判断是否有倒计时
        if (!empty($dis_data['time_limit_config']['countdown'])) {

            $countdown_end_time  = zib_time_to_c($dis_data['time_limit_config']['end']);

            $time_countdown = '<div class="flex ac mt6 em12"><span class="mr3 opacity5 px12">活动仅剩</span><span class="em12 badg badg-sm c-red" int-second="auto" data-countdown="' . $countdown_end_time . '" data-over-text="已结束">00分00秒</span></div>';
        } else {
            $_start = str_replace(' 00:00:00', '', $dis_data['time_limit_config']['start']);
            $_end   = str_replace(' 23:59:59', '', $dis_data['time_limit_config']['end']);

            $start_h = $_start ? $_start . ($_end ? ' - ' : '开始') : '';
            $end_h   = $_end ? $_end . ($_start ? '' : '结束') : '';
            $limit[] = '<div class="badg badg-sm c-yellow-2">活动时间：' . $start_h . $end_h . '</div>';
        }
    }

    $limit      = $limit ? '<div class="limit-info mt6">' . implode(' & ', $limit) . '</div>' : '';
    $limit_html = '<div class="flex ac jsb hh">' . $limit . $time_countdown . '</div>';

    //赠品
    $gift = '';
    if (!empty($dis_data['gift_config'][0])) {
        $gift_text = '';
        foreach ($dis_data['gift_config'] as $gift_item) {
            $gift_text .= '<div class="badg badg-sm c-blue mm3">' . $gift_item['desc'] . '</div>';
        }

        $gift = $gift_text ? '<div class="mt3"><span class="muted-2-color">赠送</span>' . $gift_text . '</div>' : '';
    }

    $error = '';
    if (!empty($dis_data['discount_error'])) {

        switch ($dis_data['discount_error']) {
            case 'time_limit_start':
                $error = $dis_data['discount_error_msg'] . '，开始时间：' . $dis_data['start_time'];
                break;

            case 'time_limit_end':
                $error = $dis_data['discount_error_msg'] . '，结束时间：' . $dis_data['end_time'];
                break;

            case 'config_error':
                if (is_super_admin()) {
                    $error = '<span data-toggle="tooltip" data-placement="top" title="当前优惠活动配置错误，请检查配置！(此内容仅管理员可见)">配置错误：' . $dis_data['discount_error_msg'] . '</span>';
                }
                break;
        }

        $error = $error ? '<div class="badg p2-10 jb-yellow mt6"><i class="fa fa-info-circle"></i> ' . $error . '</div>' : '';
    }

    $html          = '<div class="shop-cat-filter-child"><div style="width: 100%;">' . $title . $desc . $gift . $limit_html . $error . '</div></div>';
    $orderby_lists = zib_shop_get_the_trem_orderby_lists();
    $html .= '<div class="shop-cat-filter-orderby shop-cat-filter-child" win-ajax-replace="orderby"><span class="opacity5">排序</span>' . $orderby_lists . '</div>';

    $more_btn = zib_shop_get_term_header_more_btn($dis);
    $header   = '<div class="shop-term-header"><div class="zib-widget shop-cat-filter relative">' . $html . $more_btn . '</div></div>';
    return $header;
}

function zib_shop_dis_page_content()
{
    global $wp_query;
    $cat_id = $wp_query->get_queried_object_id();
    $header = zib_shop_get_dis_header($cat_id);

    $html = '';
    $html .= '<div class="shop-term-main mb20"><div class="ajaxpager product-lists-row">';
    $html .= $header;
    $html .= zib_shop_get_main_product_lists();
    $html .= '</div></div>';

    echo $html;
}
add_action('shop_dis_page_content', 'zib_shop_dis_page_content');

//放置于最底部
zib_shop_term_page_template('dis');
