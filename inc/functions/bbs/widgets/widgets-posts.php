<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-09 13:38:06
 * @LastEditTime : 2026-04-21 18:00:15
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|小工具模块函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

global $zib_bbs;
//主要的帖子输出列表
Zib_CFSwidget::create('zib_bbs_widget_ui_posts_lists', array(
    'title'       => '[' . $zib_bbs->forum_name . ']' . $zib_bbs->posts_name . '列表',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '显示' . $zib_bbs->posts_name . '列表，支持多种筛选、样式、排序、翻页等功能，可实现多种效果',
    'fields'      => array(
        array(
            'label'   => '仅显示当前' . $zib_bbs->plate_name . '的' . $zib_bbs->posts_name,
            'id'      => 'current_plate',
            'desc'    => '当此模块放置在' . $zib_bbs->plate_name . '页面的时候，开启此功能后，则按照当前' . $zib_bbs->plate_name . '进行筛选。可实现本版热门、本版精华等效果<div style="color: #ff6c6c;">开启此功能后，该模块只会在' . $zib_bbs->plate_name . '和' . $zib_bbs->posts_name . '页面显示</div>',
            'type'    => 'switcher',
            'default' => false,
        ),
        array(
            'dependency'  => array('current_plate', '==', '', '', 'visible'),
            'id'          => 'include_plate',
            'title'       => __('包含' . $zib_bbs->plate_name, 'zibll'),
            'desc'        => '仅显示所选' . $zib_bbs->plate_name . '的帖子，支持单选、多选。输入版块关键词搜索选择',
            'default'     => '',
            'options'     => 'post',
            'query_args'  => array(
                'post_type' => 'plate',
            ),
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'placeholder' => '输入关键词以搜索' . $zib_bbs->plate_name,
            'chosen'      => true,
            'multiple'    => true,
            'type'        => 'select',
        ),
        array(
            'dependency'  => array('include_plate|current_plate', '==|==', '|', '', 'visible'),
            'id'          => 'exclude_plate',
            'title'       => __('排除版块', 'zibll'),
            'desc'        => '排除所选版块的帖子，支持单选、多选。输入版块关键词搜索选择',
            'default'     => '',
            'options'     => 'post',
            'query_args'  => array(
                'post_type' => 'plate',
            ),
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'placeholder' => '输入关键词以搜索版块分类',
            'chosen'      => true,
            'multiple'    => true,
            'type'        => 'select',
        ),
        array(
            'id'          => 'include_topic',
            'title'       => __('包含' . $zib_bbs->topic_name, 'zibll'),
            'desc'        => '仅显示所选' . $zib_bbs->topic_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
            'default'     => '',
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy' => 'forum_topic',
            ),
            'placeholder' => '输入关键词以搜索' . $zib_bbs->topic_name,
            'chosen'      => true,
            'multiple'    => true,
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'type'        => 'select',
        ),
        array(
            'id'          => 'include_tag',
            'title'       => __('包含' . $zib_bbs->tag_name, 'zibll'),
            'desc'        => '仅显示所选' . $zib_bbs->tag_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
            'default'     => '',
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy' => 'forum_tag',
            ),
            'placeholder' => '输入关键词以搜索' . $zib_bbs->tag_name,
            'chosen'      => true,
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'multiple'    => true,
            'type'        => 'select',
        ),
        array(
            'title'       => '类型筛选',
            'id'          => 'bbs_type',
            'default'     => '',
            'type'        => 'checkbox',
            'placeholder' => '限制帖子类型，支持单选、多选',
            'inline'      => true,
            'options'     => 'zib_bbs_get_posts_type_options',
        ),
        array(
            'title'       => '阅读权限筛选',
            'id'          => 'allow_view',
            'default'     => [],
            'inline'      => true,
            'type'        => 'checkbox',
            'placeholder' => '不做其它筛选',
            'options'     => array(
                'signin'  => '登录后查看',
                'comment' => '评论后查看',
                'pay'     => '付费查看',
                'points'  => '支付积分查看',
                'roles'   => '部分用户可查看',
            ),
        ),
        array(
            'title'   => '其它筛选',
            'id'      => 'filter',
            'default' => [],
            'inline'  => true,
            'type'    => 'checkbox',
            'options' => array(
                'topping'         => '置顶帖子',
                'vote'            => '投票帖子',
                'essence'         => '精华帖子',
                'question_status' => '提问已解决',
                'is_hot'          => '热门帖子',
            ),
        ),
        array(
            'title'   => '排序方式',
            'id'      => 'orderby',
            'default' => 'date',
            'type'    => 'select',
            'options' => zib_bbs_get_posts_order_options(),
        ),
        array(
            'title'   => '列表样式',
            'id'      => 'style',
            'default' => 'detail',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                'detail'     => '详细内容',
                'mini'       => '简约风格',
                'minimalism' => '极简风格',
            ),
        ),
        array(
            'title'   => '列表独立',
            'id'      => 'alone',
            'desc'    => '每一个列表都独立显示为模块',
            'type'    => 'switcher',
            'default' => false,
        ),
        array(
            'title'   => '显示数量',
            'id'      => 'paged_size',
            'class'   => '',
            'default' => 10,
            'max'     => 20,
            'min'     => 4,
            'step'    => 1,
            'unit'    => '篇',
            'type'    => 'spinner',
        ),
        array(
            'id'      => 'paginate',
            'title'   => '翻页按钮',
            'default' => 'none',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                'none'       => __('不允许翻页', 'zibll'),
                'ajax_lists' => __('AJAX追加列表翻页', 'zibll'),
                'default'    => __('数字翻页按钮', 'zibll'),
            ),
        ),
    ),
));

function zib_bbs_widget_ui_posts_lists($args, $instance)
{

    $widget_id   = $args['widget_id'];
    $id_base     = 'zib_bbs_widget_ui_posts_lists';
    $index       = str_replace($id_base . '-', '', $widget_id);
    $alone       = !empty($instance['alone']);
    $style       = !empty($instance['style']) ? $instance['style'] : 'mini';
    $placeholder = 'posts_' . $style;
    $placeholder .= $alone ? '_alone' : '';
    $current_plate = 0;
    if (!empty($instance['current_plate'])) {
        $current_plate = zib_bbs_get_the_plate_id();
        if (!$current_plate) {
            return;
        }
    }

    $ias_args = array(
        'type'   => 'ias',
        'id'     => '',
        'class'  => '',
        'loader' => zib_bbs_get_placeholder($placeholder), // 加载动画
        'query'  => array(
            'action'        => 'ajax_widget_ui',
            'id'            => $id_base,
            'index'         => $index,
            'current_plate' => $current_plate,
        ),
    );

    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    Zib_CFSwidget::echo_before($instance);
    echo $alone ? '' : '<div class="zib-widget padding-h6">';
    echo zib_get_ias_ajaxpager($ias_args);
    echo $alone ? '' : '</div>';
    Zib_CFSwidget::echo_after($instance);
}

function zib_bbs_widget_ui_posts_lists_ajax($instance)
{

    $paged       = zib_get_the_paged();
    $style       = !empty($instance['style']) ? $instance['style'] : 'mini';
    $alone       = !empty($instance['alone']);
    $lists_class = $alone ? 'alone ajax-item' : 'ajax-item';
    $ajax_url    = zib_get_current_url();
    $paginate    = $instance['paginate'] ?? '';
    $paged_size  = $instance['paged_size'] ?? 12;

    $posts_args = array(
        'plate'         => $instance['include_plate'] ?? '',
        'plate_exclude' => $instance['exclude_plate'] ?? '',
        'topic'         => $instance['include_topic'] ?? '',
        'tag'           => $instance['include_tag'] ?? '',
        'orderby'       => $instance['orderby'] ?? 'date',
        'bbs_type'      => $instance['bbs_type'] ?? '',
        'filter'        => $instance['filter'] ?? [],
        'allow_view'    => $instance['allow_view'] ?? [],
        'paged'         => $paged,
        'paged_size'    => $paged_size,
    );
    $show_topping = $posts_args['filter'] && is_array($posts_args['filter']) && in_array('topping', $posts_args['filter']);

    if (!empty($_REQUEST['current_plate'])) {
        $posts_args['plate'] = $_REQUEST['current_plate'];
    }
    $posts = zib_bbs_get_posts_query($posts_args);

    $lists = '';
    if ($posts->have_posts()) {
        while ($posts->have_posts()): $posts->the_post();
            if ('detail' === $style) {
                $lists .= zib_bbs_get_posts_list('class=' . $lists_class . '&show_topping=' . $show_topping);
            } elseif ('minimalism' === $style) {
            $lists .= '<posts class="forum-posts minimalism ' . $lists_class . '">';
            $lists .= zib_bbs_get_posts_lists_title('forum-title', 'em09', $show_topping, true, false);
            $lists .= '</posts>';
        } else {
            $lists .= zib_bbs_get_posts_mini_list($lists_class, $show_topping);
        }
        endwhile;
        wp_reset_query();
    }
    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null('暂无内容', 10);
    }
    //帖子分页paginate
    if ('none' !== $paginate) {
        $paginate = zib_bbs_get_paginate($posts->found_posts, $paged, $paged_size, $ajax_url, $paginate, 'close');
        if (!$paginate && 1 == $paged) {
            $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
        } else {
            $lists .= $paginate;
        }
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }
    zib_ajax_send_ajaxpager($lists);
}

//主要的帖子输出列表
Zib_CFSwidget::create('zib_bbs_widget_ui_tab_posts', array(
    'title'       => '[' . $zib_bbs->forum_name . ']多栏目' . $zib_bbs->posts_name . '列表',
    'zib_title'   => false,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '多个TAB栏目切换显示' . $zib_bbs->posts_name . '列表，支持多种筛选、排序、样式、翻页等功能',
    'fields'      => array(
        array(
            'title'   => '列表样式',
            'id'      => 'style',
            'default' => 'detail',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                'detail'     => '详细内容',
                'mini'       => '简约风格',
                'minimalism' => '极简风格',
            ),
        ),
        array(
            'title'   => '列表独立',
            'id'      => 'alone',
            'desc'    => '每一个列表都独立显示为模块',
            'type'    => 'switcher',
            'default' => false,
        ),
        array(
            'title'   => '显示数量',
            'id'      => 'paged_size',
            'class'   => '',
            'default' => 10,
            'max'     => 20,
            'min'     => 4,
            'step'    => 1,
            'unit'    => '篇',
            'type'    => 'spinner',
        ),
        array(
            'id'      => 'paginate',
            'title'   => '翻页按钮',
            'default' => 'none',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                'none'       => __('不允许翻页', 'zibll'),
                'ajax_lists' => __('AJAX追加列表翻页', 'zibll'),
                'default'    => __('数字翻页按钮', 'zibll'),
            ),
        ),

        array(
            'id'                     => 'tabs',
            'type'                   => 'group',
            'accordion_title_number' => true,
            'button_title'           => '添加栏目',
            'sanitize'               => false,
            'title'                  => '栏目',
            'default'                => array(
                array(
                    'title' => '热门推荐',
                ),
            ),
            'fields'                 => array(
                array(
                    'id'         => 'title',
                    'title'      => '标题（必填）',
                    'desc'       => '栏目显示的标题，支持HTML代码，注意代码规范',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                    'sanitize'   => false,
                    'type'       => 'textarea',
                ),
                array(
                    'label'   => '仅显示当前' . $zib_bbs->plate_name . '的' . $zib_bbs->posts_name,
                    'id'      => 'current_plate',
                    'desc'    => '当此模块放置在' . $zib_bbs->plate_name . '页面的时候，开启此功能后，则按照当前' . $zib_bbs->plate_name . '进行筛选。可实现本版热门、本版精华等效果<div style="color: #ff6c6c;">开启此功能后，该栏目只会在' . $zib_bbs->plate_name . '和' . $zib_bbs->posts_name . '页面显示</div>',
                    'type'    => 'switcher',
                    'default' => false,
                ),
                array(
                    'dependency'  => array('current_plate', '==', '', '', 'visible'),
                    'id'          => 'include_plate',
                    'title'       => __('包含' . $zib_bbs->plate_name, 'zibll'),
                    'desc'        => '仅显示所选' . $zib_bbs->plate_name . '的帖子，支持单选、多选。输入版块关键词搜索选择',
                    'default'     => '',
                    'options'     => 'post',
                    'query_args'  => array(
                        'post_type' => 'plate',
                    ),
                    'ajax'        => true,
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'placeholder' => '输入关键词以搜索' . $zib_bbs->plate_name,
                    'chosen'      => true,
                    'multiple'    => true,
                    'type'        => 'select',
                ),
                array(
                    'dependency'  => array('include_plate|current_plate', '==|==', '|', '', 'visible'),
                    'id'          => 'exclude_plate',
                    'title'       => __('排除版块', 'zibll'),
                    'desc'        => '排除所选版块的帖子，支持单选、多选。输入版块关键词搜索选择',
                    'default'     => '',
                    'options'     => 'post',
                    'query_args'  => array(
                        'post_type' => 'plate',
                    ),
                    'ajax'        => true,
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'placeholder' => '输入关键词以搜索版块分类',
                    'chosen'      => true,
                    'multiple'    => true,
                    'type'        => 'select',
                ),
                array(
                    'id'          => 'include_topic',
                    'title'       => __('包含' . $zib_bbs->topic_name, 'zibll'),
                    'desc'        => '仅显示所选' . $zib_bbs->topic_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
                    'default'     => '',
                    'options'     => 'categories',
                    'query_args'  => array(
                        'taxonomy' => 'forum_topic',
                    ),
                    'placeholder' => '输入关键词以搜索' . $zib_bbs->topic_name,
                    'chosen'      => true,
                    'multiple'    => true,
                    'ajax'        => true,
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'type'        => 'select',
                ),
                array(
                    'id'          => 'include_tag',
                    'title'       => __('包含' . $zib_bbs->tag_name, 'zibll'),
                    'desc'        => '仅显示所选' . $zib_bbs->tag_name . '的' . $zib_bbs->posts_name . '，支持单选、多选。输入关键词搜索选择',
                    'default'     => '',
                    'options'     => 'categories',
                    'query_args'  => array(
                        'taxonomy' => 'forum_tag',
                    ),
                    'placeholder' => '输入关键词以搜索' . $zib_bbs->tag_name,
                    'chosen'      => true,
                    'ajax'        => true,
                    'settings'    => array(
                        'min_length' => 2,
                    ),
                    'multiple'    => true,
                    'type'        => 'select',
                ),
                array(
                    'title'       => '类型筛选',
                    'id'          => 'bbs_type',
                    'default'     => '',
                    'type'        => 'checkbox',
                    'placeholder' => '限制帖子类型，支持单选、多选',
                    'inline'      => true,
                    'options'     => 'zib_bbs_get_posts_type_options',
                ),
                array(
                    'title'       => '阅读权限筛选',
                    'id'          => 'allow_view',
                    'default'     => [],
                    'inline'      => true,
                    'type'        => 'checkbox',
                    'placeholder' => '不做其它筛选',
                    'options'     => array(
                        'signin'  => '登录后查看',
                        'comment' => '评论后查看',
                        'pay'     => '付费查看',
                        'points'  => '支付积分查看',
                        'roles'   => '部分用户可查看',
                    ),
                ),
                array(
                    'title'   => '其它筛选',
                    'id'      => 'filter',
                    'default' => [],
                    'inline'  => true,
                    'type'    => 'checkbox',
                    'options' => array(
                        'topping'         => '置顶帖子',
                        'vote'            => '投票帖子',
                        'essence'         => '精华帖子',
                        'question_status' => '提问已解决',
                        'is_hot'          => '热门帖子',
                    ),
                ),
                array(
                    'title'   => '排序方式',
                    'id'      => 'orderby',
                    'default' => 'date',
                    'type'    => 'select',
                    'options' => zib_bbs_get_posts_order_options(),
                ),
            ),
        ),
    ),
));

function zib_bbs_widget_ui_tab_posts($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class || empty($instance['tabs'])) {
        return;
    }

    $widget_id = $args['widget_id'];
    $id_base   = 'zib_bbs_widget_ui_tab_posts';
    $index     = str_replace($id_base . '-', '', $widget_id);
    $alone     = !empty($instance['alone']);

    $tabs_con  = '';
    $tabs_nav  = '';
    $tabs_i    = 1;
    $tabs      = $instance['tabs'];
    $ajax_href = add_query_arg(array(
        'action' => 'ajax_widget_ui',
        'id'     => $id_base,
        'index'  => $index,
    ), admin_url('/admin-ajax.php'));

    $alone            = !empty($instance['alone']);
    $style            = !empty($instance['style']) ? $instance['style'] : 'mini';
    $placeholder_type = 'posts_' . $style;
    $placeholder_type .= $alone ? '_alone' : '';
    $placeholder = zib_bbs_get_placeholder($placeholder_type);

    foreach ($instance['tabs'] as $tabs_key => $tabs) {
        if (empty($tabs['title'])) {
            continue;
        }
        $tab_id    = $widget_id . '-' . $tabs_i;
        $nav_class = $tabs_i == 1 ? 'active' : '';
        $con_class = $tabs_i == 1 ? ' active in' : '';

        if (!empty($tabs['current_plate'])) {
            $current_plate = zib_bbs_get_the_plate_id();
            if (!$current_plate) {
                continue;
            }
            $ajax_href = add_query_arg('current_plate', $current_plate, $ajax_href);
        }

        if ($tabs_i == 1) {
            $ias_args = array(
                'type'   => 'ias',
                'loader' => $placeholder, // 加载动画
                'url'    => $ajax_href,
            );

            $con_html = zib_get_ias_ajaxpager($ias_args);
        } else {
            $con_html = '';
            $con_html .= '<span class="post_ajax_trigger hide"><a href="' . add_query_arg('tab', $tabs_key, $ajax_href) . '" class="ajax_load ajax-next ajax-open" no-scroll="true"></a></span>';
            $con_html .= '<div class="post_ajax_loader" style="display: none;">' . $placeholder . '</div>';
        }

        $tabs_nav .= '<li class="' . $nav_class . '"><a' . ($tabs_i !== 1 ? ' data-ajax' : '') . ' data-toggle="tab" href="#' . $tab_id . '">' . $tabs['title'] . '</a></li>';
        $tabs_con .= '<div class="tab-pane fade' . $con_class . '" id="' . $tab_id . '"><div class="ajaxpager' . (!$alone ? ' zib-widget padding-h6' : ' mb20') . '">' . $con_html . '</div></div>';
        $tabs_i++;
    }

    if (!$tabs_nav) {
        return;
    }

    $main_html = '
        <div class="index-tab rectangular relative mb10">
            <ul class="list-inline scroll-x no-scrollbar">
                ' . $tabs_nav . '
            </ul>
        </div>
        <div class="tab-content">
            ' . $tabs_con . '
        </div>';

    //开始输出
    Zib_CFSwidget::echo_before($instance, 'widget-tab-bbs-posts style-' . $style);
    echo $main_html;
    Zib_CFSwidget::echo_after($instance);
}

function zib_bbs_widget_ui_tab_posts_ajax($instance)
{
    $tab                    = isset($_REQUEST['tab']) ? (int) $_REQUEST['tab'] : 0;
    $tab_args               = isset($instance['tabs'][$tab]) ? $instance['tabs'][$tab] : array();
    $tab_args['style']      = $instance['style'] ?? 'mini';
    $tab_args['alone']      = $instance['alone'] ?? false;
    $tab_args['paginate']   = $instance['paginate'] ?? 'none';
    $tab_args['paged_size'] = $instance['paged_size'] ?? 10;

    zib_bbs_widget_ui_posts_lists_ajax($tab_args);
}
