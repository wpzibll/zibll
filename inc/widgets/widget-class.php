<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-11-29 21:35:52
 * @LastEditTime : 2026-04-26 21:06:56
 */

class Zib_CFSwidget
{
    public static function create($id, $args = array())
    {
        $args = self::args($args);
        CSF::createWidget($id, $args);
    }
    public static function is_size_limit($wp_args = array())
    {
        if (!empty($wp_args['size']) && !empty($wp_args['id']) && is_super_admin()) {
            if ($wp_args['size'] == 'mini' && (!strstr($wp_args['id'], 'sidebar') && !strstr($wp_args['id'], 'nav'))) {
                return true;
            } elseif ($wp_args['size'] == 'big' && (strstr($wp_args['id'], 'sidebar') || strstr($wp_args['id'], 'nav'))) {
                return true;
            }
        }
        return false;
    }
    public static function echo_before($args = array(), $class = 'mb20', $wp_args = array())
    {
        $show_class = self::show_class($args);
        if ($show_class && $show_class !== true) {
            $class .= ' ' . $show_class;
        }
        $affix = !empty($args['sidebar_affix']) ? ' data-affix="true"' : '';

        //模块出现动画
        $animation      = !empty($args['animation_in']) ? $args['animation_in'] : '';
        $animation_attr = $animation ? ' data-animation="' . esc_attr($animation) . '"' : '';
        $animation_attr .= $animation && !empty($args['animation_repeat']) ? ' data-animation-repeat="true"' : '';
        if ($animation_attr && $animation) {
            $class .= ' obs-animate ani-' . $animation;
        }

        $title = self::show_title($args);
        do_action('zib_cfswidget_echo_before', $args, $class);
        $class_attr = $class ? ' class="' . esc_attr($class) . '"' : '';
        if (self::is_size_limit($wp_args)) {
            $desc       = $wp_args['size'] == 'mini' ? '此模块宽度较小，PC端放置在此处，显示效果不佳，建议更换到侧边栏等较窄的容器，或者开启仅移动端显示。' : '此模块宽度较大，放置在此处，显示效果不佳，建议更换为全宽度、主内容上下等较宽的容器';
            $size_limit = '<div class="badg btn-block c-red padding-lg"><i class="fa fa-fw fa-info-circle mr10"></i>此模块不推荐放置在此位置！' . $desc . '<br>(当前提醒只会在管理员登录时显示)</div>';
            echo '<div style="padding:6px;background:rgba(255, 0, 143, 0.1);">' . $size_limit;
        }
        echo '<div' . $affix . $class_attr . $animation_attr . '>';
        echo $title;
    }

    public static function echo_after($args = array(), $wp_args = array())
    {
        echo '</div>';
        if (self::is_size_limit($wp_args)) {
            echo '</div>';
        }
        do_action('zib_cfswidget_echo_after', $args);
    }

    public static function show_title($args = array())
    {
        if (empty($args['title'])) {
            return;
        }

        $title    = $args['title'];
        $subtitle = !empty($args['subtitle']) ? $args['subtitle'] : '';
        $subtitle = $subtitle ? '<small class="ml10">' . $subtitle . '</small>' : '';

        $link_url   = !empty($args['title_link']['url']) ? $args['title_link']['url'] : '';
        $link_text  = !empty($args['title_link']['text']) ? $args['title_link']['text'] : '<i class="fa fa-angle-right fa-fw"></i>更多';
        $link_blank = !empty($args['title_link']['target']) && $args['title_link']['target'] == '_blank' ? ' target="_blank"' : '';

        $more_but = $link_url ? '<div class="pull-right em09 mt3"><a' . $link_blank . ' href="' . $link_url . '" class="muted-2-color">' . $link_text . '</a></div>' : '';
        return '<div class="box-body notop"><div class="title-theme">' . $title . $subtitle . $more_but . '</div></div>';
    }

    //可用于是否显示判断
    public static function show_class($instance)
    {
        $show_type = isset($instance['show_type']) ? $instance['show_type'] : 'all';

        $wp_is_mobile = wp_is_mobile();
        if ($show_type == 'only_pc' && $wp_is_mobile) {
            return '';
        }

        if ($show_type == 'only_sm' && !$wp_is_mobile) {
            return '';
        }

        if (!empty($instance['show_id_type']) && !empty($instance['show_ids'])) {
            if (is_singular()) {
                $the_id   = get_the_ID();
                $show_ids = preg_split("/,|，|\s|\n/", $instance['show_ids']);

                if ($instance['show_id_type'] == 'show' && !in_array($the_id, $show_ids)) {
                    return '';
                }
                if ($instance['show_id_type'] == 'hide' && in_array($the_id, $show_ids)) {
                    return '';
                }
            }
        }
        if ($show_type == 'only_pc') {
            return 'hidden-xs';
        }

        if ($show_type == 'only_sm') {
            return 'visible-xs-block';
        }

        return true;
    }
    public static function args($args = array())
    {
        $args['title'] = 'Zibll ' . $args['title'];
        $more_args     = array();
        if (!empty($args['zib_title'])) {
            unset($args['zib_title']);
            $more_args[] = array(
                'title'      => '模块标题',
                'id'         => 'title',
                'default'    => '',
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            );
            $more_args[] = array(
                'dependency' => array('title', '!=', ''),
                'title'      => ' ',
                'subtitle'   => '副标题',
                'class'      => 'compact',
                'id'         => 'subtitle',
                'default'    => '',
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            );
            $more_args[] = array(
                'dependency'   => array('title', '!=', ''),
                'title'        => '标题右侧链接',
                'class'        => 'compact',
                'id'           => 'title_link',
                'desc'         => '标题、副标题均支持HTML代码，请注意代码规范！',
                'type'         => 'link',
                'add_title'    => '添加链接',
                'edit_title'   => '编辑链接',
                'remove_title' => '删除链接',
            );
        }
        if (!empty($args['zib_affix'])) {
            unset($args['zib_affix']);
            $more_args[] = array(
                'title'    => '侧栏随动',
                'subtitle' => '',
                'id'       => 'sidebar_affix',
                'label'    => '仅在侧边栏有效',
                'type'     => 'switcher',
                'default'  => false,
            );
        }
        if (!empty($args['zib_show'])) {
            unset($args['zib_show']);
            $more_args[] = array(
                'title'   => __('显示规则', 'zibll'),
                'id'      => 'show_type',
                'type'    => 'radio',
                'default' => 'all',
                'options' => array(
                    'all'     => 'PC端/移动端均显示',
                    'only_pc' => '仅在PC端显示',
                    'only_sm' => '仅在移动端显示',
                ),
            );

            $more_args[] = array(
                'title'   => __('按ID显示或隐藏', 'zibll'),
                'id'      => 'show_id_type',
                'type'    => 'radio',
                'default' => '',
                'options' => array(
                    'show' => '仅在以下ID中显示',
                    'hide' => '在以下ID中隐藏',
                ));
            $more_args[] = array(
                'id'    => 'show_ids',
                'class' => 'compact',
                'type'  => 'text',
                'desc'  => '<span class="px12">当此模块添加在文章、帖子、板块、商品页面时候，可以通过此处设置ID，来控制显示或隐藏此模块，多个ID用英文逗号隔开。<br>例如：1,2,3</span>',
            );
        }

        if (!empty($args['zib_animation_in']) || !isset($args['zib_animation_in'])) {
            unset($args['zib_animation_in']);
            $more_args[] = array(
                'title'   => '入场动画',
                'id'      => 'animation_in',
                'type'    => 'select',
                'default' => '',
                'desc'    => '当页面滚动到此模块时，会自动触发此动画，注意：建议在某一个页面的模块中启用，并且要根据模块的实际情况适当开启，合理配置才能达到最佳效果',
                'options' => array(
                    ''           => '无动画',
                    'fade'       => '淡入',
                    'slideup'    => '从下往上滑出',
                    'slidedown'  => '从上往下滑出',
                    'slideright' => '从左往右滑出',
                    'slideleft'  => '从右往左滑出',
                    'zoomin'     => '由小变大',
                    'zoomout'    => '由大变小',
                ),
            );

            $more_args[] = array(
                'dependency' => array('animation_in', '!=', ''),
                'title'      => '重复动画',
                'id'         => 'animation_repeat',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => false,
                'desc'       => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
            );
        }

        $args['fields'] = array_merge($more_args, $args['fields']);
        return $args;
    }
}

function zib_cfswidget_customize_controls_init()
{
    //加载css代码
    wp_enqueue_style('zib-customize-controls', ZIB_TEMPLATE_DIRECTORY_URI . '/css/customize-controls.min.css', array(), THEME_VERSION);
}
add_action('customize_controls_init', 'zib_cfswidget_customize_controls_init');
