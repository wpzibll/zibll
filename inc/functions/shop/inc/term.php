<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-03-15 14:26:53
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统|分类、标签、折扣等功能函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_get_the_trem_orderby_lists()
{
    global $wp_query;
    $order   = $wp_query->get('order');
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : _pz('shop_list_opt', 'data', 'orderby');

    return zib_shop_get_orderby_lists($orderby, $order);
}

function zib_shop_get_term_header_more_btn($term, $class = 'abs right-top', $item_class = '')
{
    // $share  = zib_get_term_share_btn($term, $item_class . ' btn-share', true);
    $search = zib_shop_get_term_search_btn($term, $item_class);

    return '<div class="more-btns flex ac ' . $class . '">' . $search . '</div>';
}

/**
 * 获取分类、标签、折扣的搜索按钮
 * @param int $term_id 分类、标签、折扣的ID
 * @param string $class 按钮的类名
 * @return string 搜索按钮的HTML
 */
function zib_shop_get_term_search_btn($term, $class = '')
{

    if (!is_object($term)) {
        $term = get_term($term);
    }

    $title = esc_attr($term->name);

    $args = array(
        'class'       => $class,
        'trem'        => $term->term_id,
        'trem_name'   => zib_str_cut($title, 0, 8),
        'type'        => 'product',
        'placeholder' => '在' . zib_shop_get_taxonomy_name($term->taxonomy) . '[' . $title . ']中搜索商品',
    );

    return zib_get_search_link($args);
}

/**
 * 获取分类、标签、折扣的名称
 * @param string $taxonomy 分类、标签、折扣
 * @return string 分类、标签、折扣的名称
 */
function zib_shop_get_taxonomy_name($taxonomy = '')
{
    if (!$taxonomy) {
        $taxonomy = get_query_var('taxonomy');
    }
    $taxonomy_names = array(
        'shop_cat'      => '分类',
        'shop_tag'      => '标签',
        'shop_discount' => '优惠活动',
    );
    return isset($taxonomy_names[$taxonomy]) ? $taxonomy_names[$taxonomy] : '';
}

/**
 * 获取分类、标签、折扣的配置
 * @param string $taxonomy 分类、标签、折扣
 * @param int $term_id 分类、标签、折扣的ID
 * @param string $key 配置项的键
 * @param string $default 配置项的默认值
 * @return mixed 配置项的值
 */
function zib_shop_get_term_config($taxonomy, $term_id, $key = null, $default = '')
{
    if (is_object($term_id) && isset($term_id->term_id)) {
        $term_id = $term_id->term_id;
    }

    //定义静态变量
    static $configs = array();
    if (!isset($configs[$term_id])) {
        $configs[$term_id] = get_term_meta($term_id, $taxonomy . '_config', true) ?: array();
    }

    return zib_get_array_value($configs[$term_id], $key, $default);
}

function zib_shop_term_frontend_set_input_array($input_array, $term_id, $type)
{
    if ($type !== 'tax') {
        return $input_array;
    }

    $term_data = get_term($term_id);
    $taxonomy  = $term_data->taxonomy;

    if ($taxonomy === 'shop_cat') {
        $input_array[] = array(
            'id'      => 'list_style_style',
            'name'    => '列表显示样式',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                'default' => '默认(尊循主题配置)',
                ''        => '竖向大卡片',
                'small'   => '横向小卡片',
            ),
            'std'     => zib_shop_get_term_config($taxonomy, $term_id, 'list_style_style', 'default'),
        );
    }

    if ($taxonomy === 'shop_discount') {
        //小标签
        $input_array[] = array(
            'id'   => 'small_badge',
            'name' => '小标签',
            'type' => 'text',
            'std'  => zib_shop_get_term_config($taxonomy, $term_id, 'small_badge', ''),
        );
        $input_array[] = array(
            'id'   => 'priority',
            'name' => '优先级',
            'type' => 'number',
            'std'  => zib_shop_get_term_config($taxonomy, $term_id, 'priority', 50),
        );
        $input_array[] = array(
            'id'       => 'is_important',
            'name'     => '重点活动',
            'type'     => 'radio',
            'inline'   => true,
            'options'  => array(
                '1' => '是',
                '0' => '否',
            ),
            'question' => '重点活动将在商品金额位置着重显示，注意：一个商品选择了多个重点标签时，则按照优先级的第一个为准',
            'std'      => zib_shop_get_term_config($taxonomy, $term_id, 'is_important'),
        );
    }

    if ($taxonomy === 'shop_tag') {
        $input_array[] = array(
            'id'   => 'priority',
            'name' => '优先级',
            'type' => 'number',
            'std'  => zib_shop_get_term_config($taxonomy, $term_id, 'priority', 50),
        );
        $input_array[] = array(
            'id'       => 'is_important',
            'name'     => '重点标签',
            'type'     => 'radio',
            'inline'   => true,
            'options'  => array(
                '1' => '是',
                '0' => '否',
            ),
            'question' => '重点标签将在重要位置着重显示，注意：一个商品选择了多个重点标签时，则按照优先级的第一个为准',
            'std'      => zib_shop_get_term_config($taxonomy, $term_id, 'is_important'),
        );
    }

    return $input_array;
}

//保存
function zib_shop_term_frontend_set_save($object_data, $type)
{
    if ($type !== 'tax') {
        return;
    }

    $taxonomy = $object_data->taxonomy;
    if (in_array($taxonomy, array('shop_cat', 'shop_tag', 'shop_discount'))) {
        $config = zib_shop_get_term_config($taxonomy, $object_data->term_id);
        $keys   = array('list_style_style', 'small_badge', 'priority', 'is_important');
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $config[$key] = $_POST[$key];
            }
        }

        update_term_meta($object_data->term_id, $taxonomy . '_config', $config);
    }
}
add_action('zib_frontend_set_save', 'zib_shop_term_frontend_set_save', 10, 2);
