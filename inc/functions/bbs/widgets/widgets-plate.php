<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:38:06
 * @LastEditTime : 2025-12-01 12:10:30
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

global $zib_bbs;
//开始创建模块
//版块信息模块
Zib_CFSwidget::create('zib_bbs_widget_ui_plate_info', array(
    'title'       => '[' . $zib_bbs->forum_name . ']' . $zib_bbs->plate_name . '信息',
    'size'        => 'mini',
    'zib_affix'   => true,
    'zib_show'    => false,
    'description' => '显示当前' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_name . '信息，只能添加到' . $zib_bbs->plate_name . '页面的侧边栏',
    'fields'      => array(
        array(
            'content' => '显示当前' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_name . '信息，只能添加到' . $zib_bbs->plate_name . '或' . $zib_bbs->posts_name . '页面的侧边栏',
            'style'   => 'warning',
            'type'    => 'content',
        ),
    ),
));

function zib_bbs_widget_ui_plate_info($args, $instance)
{
    $obj = zib_bbs_get_the_plate();
    if (!$obj) {
        return;
    }

    Zib_CFSwidget::echo_before($instance);
    echo zib_bbs_get_plate_info_mini_box($obj, 'mb20');
    Zib_CFSwidget::echo_after($instance);
}

//版块信息模块
Zib_CFSwidget::create('zib_bbs_widget_ui_plate_moderator', array(
    'title'       => '[' . $zib_bbs->forum_name . ']' . $zib_bbs->plate_name . '管理员列表',
    'size'        => 'mini',
    'zib_affix'   => true,
    'zib_show'    => false,
    'description' => '显示当前' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_author_name . '和' . $zib_bbs->plate_moderator_name . '信息，只能添加到' . $zib_bbs->plate_name . '页面的侧边栏',
    'fields'      => array(
        array(
            'content' => '显示当前' . $zib_bbs->plate_name . '的' . $zib_bbs->plate_author_name . '和' . $zib_bbs->plate_moderator_name . '信息，只能添加到' . $zib_bbs->plate_name . '或' . $zib_bbs->posts_name . '页面的侧边栏',
            'style'   => 'warning',
            'type'    => 'content',
        ),
        array(
            'title'      => '标题内容',
            'id'         => 'title_text',
            'default'    => $zib_bbs->plate_name . $zib_bbs->plate_moderator_name,
            'attributes' => array(
                'rows' => 1,
            ),
            'type'       => 'textarea',
        ),
        array(
            'title'   => '申请入口',
            'id'      => 'apply_btn',
            'label'   => '显示申请按钮',
            'type'    => 'switcher',
            'default' => true,
        ),
    ),
));

//显示版主列表
function zib_bbs_widget_ui_plate_moderator($args, $instance)
{

    $obj = zib_bbs_get_the_plate();
    if (!$obj) {
        return;
    }

    $moderator_lists = zib_bbs_get_moderator_lists('plate', $obj);
    if ($moderator_lists) {
        $title     = !empty($instance['title_text']) ? '<div class="title-theme">' . $instance['title_text'] . '</div>' : '<div></div>';
        $apply_btn = !empty($instance['apply_btn']) ? zib_bbs_get_apply_moderator_link(0, 'but hollow c-blue p2-10 em09') : '';

        Zib_CFSwidget::echo_before($instance);
        echo '<div class="zib-widget moderator-box">';
        if ($title || $apply_btn) {
            if (!$apply_btn) {
                $apply_btn = zib_bbs_get_edit_plate_moderator_link($obj->ID, 'c-blue em09');
            }
            echo '<div class="mb10 flex jsb ac">' . $title . $apply_btn . '</div>';
        }
        echo $moderator_lists;
        echo '</div>';
        Zib_CFSwidget::echo_after($instance);
    }
}

//版块列表
Zib_CFSwidget::create('zib_bbs_widget_ui_plate_lists', array(
    'title'       => '[' . $zib_bbs->forum_name . ']' . $zib_bbs->plate_name . '列表',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '显示' . $zib_bbs->plate_name . '列表，支持多种筛选、样式、排序、翻页等功能，可实现多种效果',
    'fields'      => array(
        array(
            'id'          => 'cat',
            'title'       => __('分类筛选', 'zibll'),
            'desc'        => '仅显示所选分类的帖子，支持单选、多选。输入关键词搜索选择',
            'default'     => '',
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy' => 'plate_cat',
            ),
            'placeholder' => '输入关键词以搜索分类',
            'chosen'      => true,
            'multiple'    => true,
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'type'        => 'select',
        ),
        array(
            'title'       => '其它筛选',
            'id'          => 'filter',
            'default'     => '',
            'type'        => "select",
            'placeholder' => '不做其它筛选',
            'options'     => array(
                'is_hot' => '热门' . $zib_bbs->plate_name,
            ),
        ),
        array(
            'title'   => '排序方式',
            'id'      => 'orderby',
            'default' => 'date',
            'type'    => "select",
            'options' => zib_bbs_get_plate_order_options(),
        ),
        array(
            'title'   => '列表样式',
            'id'      => 'style',
            'default' => 'default',
            'type'    => "radio",
            'inline'  => true,
            'options' => array(
                'default' => '默认列表',
                'card'    => '单行滚动卡片',
            ),
        ),
        array(
            'title'   => '显示数量',
            'id'      => 'showposts',
            'class'   => '',
            'default' => 6,
            'max'     => 20,
            'min'     => 4,
            'step'    => 1,
            'unit'    => '篇',
            'type'    => 'spinner',
        ),
    ),
));

function zib_bbs_widget_ui_plate_lists($args, $instance)
{

    $widget_id = $args['widget_id'];
    $id_base   = 'zib_bbs_widget_ui_plate_lists';
    $index     = str_replace($id_base . '-', '', $widget_id);

    $placeholder = '<div class="plate-lists"><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div><div class="plate-item flex"><div class="plate-thumb placeholder radius"></div><div class="item-info flex1"><div class="info-header"><h2 class="forum-title placeholder k1"></h2></div><div class="placeholder s1 mt3"></div></div></div></div>';

    if (isset($instance['style']) && $instance['style'] === 'card') {
        $placeholder = '<div class="panel-plate" style="overflow: hidden;"><div class="flex scroll-plate"><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card" style="margin-right: 10px;"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div><div class="plate-card"><div class="plate-thumb"><div style="width: 100%; height: 100%;" class="placeholder radius"></div></div><div style="height: 30px;" class="placeholder k2 mt10"></div><div class="placeholder s1 mt20"></div></div></div></div>';
    }

    $ias_args = array(
        'type'   => 'ias',
        'loader' => $placeholder, // 加载动画
        'query'  => array(
            'action' => 'ajax_widget_ui',
            'id'     => $id_base,
            'index'  => $index,
        ),
    );

    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    Zib_CFSwidget::echo_before($instance);
    echo zib_get_ias_ajaxpager($ias_args);
    Zib_CFSwidget::echo_after($instance);
}

function zib_bbs_widget_ui_plate_lists_ajax($instance)
{

    $style = $instance['style'] ? $instance['style'] : 'default';

    $posts_args = array(
        'cat'       => $instance['cat'],
        'filter'    => $instance['filter'],
        'orderby'   => $instance['orderby'],
        'showposts' => $instance['showposts'],
    );

    $lists = '';
    if ('card' == $style) {
        $lists = zib_bbs_get_plate_slide_card($posts_args);
    } else {
        $lists = '<div class="plate-lists">' . zib_bbs_get_plate_main_lists($posts_args) . '</div>';
    }

    if (!$lists && is_super_admin()) {
        $lists = zib_get_ajax_null('该模块没有内容显示', 20);
    }

    zib_ajax_send_ajaxpager($lists, true);
}
