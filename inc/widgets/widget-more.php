<?php

add_action('widgets_init', 'widget_register_more');
function widget_register_more()
{
    register_widget('widget_ui_yiyan');
    register_widget('widget_ui_posts_navs');
    register_widget('widget_ui_new_comment');
    register_widget('widget_ui_links_lists_2');
    register_widget('widget_ui_notice');
    register_widget('widget_ui_search');
    register_widget('widget_ui_tag_cloud');
}

/**
 * 标签云
 */

class widget_ui_tag_cloud extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_tag_cloud',
            'w_name'      => _name('标签云'),
            'classname'   => '',
            'description' => '显示标签、分类、专题的标签云',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }
        extract($args);
        $defaults = array(
            'title'            => '',
            'mini_title'       => '',
            'more_but'         => '',
            'more_but_url'     => '',
            'in_affix'         => '',

            'taxonomy'         => 'post_tag',
            'show_count'       => '',
            'orderby'          => 'name',
            'number'           => 20,
            'blank'            => '',
            'color'            => 'rand',
            'fixed_width'      => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        $attr  = '';
        $class = 'theme-box';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';
        $title = apply_filters('zib_widget_title', $instance);
        echo $title;
        echo '<div class="zib-widget widget-tag-cloud author-tag' . ($instance['fixed_width'] ? ' fixed-width' : '') . '">';

        //新窗口打开
        $blank = $instance['blank'] ? ' target="_blank"' : '';

        //开始生成标签
        $get_terms_args = array(
            'taxonomy'   => $instance['taxonomy'],
            'orderby'    => $instance['orderby'],
            'order'      => 'DESC',
            'number'     => $instance['number'],
            'hide_empty' => false,
            'count'      => true,
        );
        $tags = get_terms($get_terms_args);

        $tag_link     = '';
        $rand_color_i = rand(0, 10);
        if (!empty($tags) && !is_wp_error($tags)) {
            foreach ($tags as $key => $tag) {
                $url  = esc_url(get_term_link(($tag->term_id), $tag->taxonomy));
                $name = esc_attr($tag->name);
                $cls  = array('c-blue', 'c-yellow', 'c-green', 'c-purple', 'c-red', '', 'c-blue-2', 'c-yellow-2', 'c-green-2', 'c-purple-2', 'c-red-2', '');
                if ($rand_color_i > 10) {
                    $rand_color_i = 0;
                }

                $tag_class = 'but ' . ('rand' != $instance['color'] ? $instance['color'] : $cls[$rand_color_i]);
                $rand_color_i++;
                $count = $instance['show_count'] ? '<span class="em09 tag-count"> (' . esc_attr($tag->count) . ')</span>' : '';
                $tag_link .= '<a' . $blank . ' href="' . $url . '" class="text-ellipsis ' . $tag_class . '">' . $name . $count . '</a>';
            }
        }

        echo $tag_link;
        echo '</div>';
        echo '</div>';
    }
    public function form($instance)
    {
        $defaults = array(
            'title'            => '标签云',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url'     => '',
            'in_affix'         => '',

            'taxonomy'         => 'post_tag',
            'show_count'       => '',
            'orderby'          => 'name',
            'number'           => 20,
            'blank'            => '',
            'color'            => 'rand',
            'fixed_width'      => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance     = wp_parse_args((array) $instance, $defaults);
        $page_input[] = array(
            'name'    => __('入场动画', 'zibll'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => '当页面滚动到此模块时，会自动触发此动画，注意：建议在某一个页面的模块中启用，并且要根据模块的实际情况适当开启，合理配置才能达到最佳效果',
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zibll'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        );

        $page_input[] = array(
            'name'  => __('标题：', 'zibll'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zibll'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zibll'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zibll'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'id'    => $this->get_field_name('in_affix'),
            'std'   => $instance['in_affix'],
            'desc'  => '侧栏随动 (仅在侧边栏有效)',
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
        );
        $taxonomies = get_taxonomies(array('show_tagcloud' => true), 'object');
        foreach ($taxonomies as $taxonomy => $tax) {
            $options[$taxonomy] = $tax->labels->name;
        }
        $page_input[] = array(
            'name'    => __('分类法', 'zibll'),
            'id'      => $this->get_field_name('taxonomy'),
            'std'     => $instance['taxonomy'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
            'options' => $options,
        );
        $page_input[] = array(
            'name'    => __('排序方式', 'zibll'),
            'id'      => $this->get_field_name('orderby'),
            'std'     => $instance['orderby'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
            'options' => array(
                'name'    => '名称',
                'count'   => '数量',
                'term_id' => '最新创建',
            ),
        );
        $page_input[] = array(
            'name'    => __('标签颜色', 'zibll'),
            'id'      => $this->get_field_name('color'),
            'std'     => $instance['color'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
            'options' => array(
                'rand'       => '随机颜色',
                'c-hui'      => '灰色',
                'c-blue'     => '蓝色',
                'c-blue-2'   => '深蓝色',
                'c-cyan'     => '青色',
                'c-yellow'   => '黄色',
                'c-yellow-2' => '橙黄色',
                'c-green'    => '绿色',
                'c-green-2'  => '墨绿色',
                'c-purple'   => '紫色',
                'c-purple-2' => '深紫色',
                'c-red'      => '粉红色',
                'c-red-2'    => '红色',
            ),
        );

        $page_input[] = array(
            'name'  => __('最大数量', 'zibll'),
            'id'    => $this->get_field_name('number'),
            'std'   => $instance['number'],
            'style' => 'margin: 10px auto;',
            'type'  => 'number',
        );
        $page_input[] = array(
            'id'    => $this->get_field_name('show_count'),
            'std'   => $instance['show_count'],
            'desc'  => '显示标签计数',
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
        );
        $page_input[] = array(
            'id'    => $this->get_field_name('fixed_width'),
            'std'   => $instance['fixed_width'],
            'desc'  => '固定宽度',
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
        );
        $page_input[] = array(
            'id'    => $this->get_field_name('blank'),
            'std'   => $instance['blank'],
            'desc'  => '新窗口打开',
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
        );
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
    }
}

/**
 *搜索小工具
 */
class widget_ui_search extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_search',
            'w_name'      => _name('搜索框'),
            'classname'   => '',
            'description' => '显示一个搜索框，多种显示效果',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }

    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);
        $defaults = array(
            'title'            => '搜索',
            'mini_title'       => '',

            'more_but'         => '',
            'more_but_url'     => '',
            'in_affix'         => '',

            'show_history'     => '',

            'class'            => '',
            'show_keywords'    => '',
            'keywords_title'   => '热门搜索',
            'placeholder'      => '开启精彩搜索',
            'show_input_cat'   => '',
            'show_more_cat'    => '',
            'in_cat'           => '',
            'more_cats'        => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $attr  = '';
        $class = 'theme-box';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';

        $title = apply_filters('zib_widget_title', $instance);
        echo $title;
        echo '<div class="zib-widget widget-search">';

        $args = array(
            'class'          => '',
            'show_keywords'  => $instance['show_keywords'],
            'show_history'   => $instance['show_history'],
            'keywords_title' => $instance['keywords_title'],
            'placeholder'    => $instance['placeholder'],
            'show_input_cat' => $instance['show_input_cat'],
            'show_more_cat'  => $instance['show_more_cat'],
            'in_cat'         => $instance['in_cat'],
        );
        if ($instance['more_cats']) {
            $args['more_cats'] = preg_split("/,|，|\s|\n/", $instance['more_cats']);
        }
        zib_get_search_box($args, true);

        echo '</div>';
        echo '</div>';
    }

    public function form($instance)
    {
        $defaults = array(
            'title'            => '搜索',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url'     => '',

            'class'            => '',
            'show_history'     => '',
            'show_keywords'    => '',
            'keywords_title'   => '热门搜索',
            'placeholder'      => '开启精彩搜索',
            'show_input_cat'   => '',
            'show_more_cat'    => '',
            'in_cat'           => '',
            'in_affix'         => '',
            'more_cats'        => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance   = wp_parse_args((array) $instance, $defaults);
        $page_input = array();

        $page_input[] = array(
            'name'  => __('标题：', 'zibll'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zibll'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zibll'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zibll'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );

        $page_input[] = array(
            'name'    => __('入场动画', 'zibll'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => '当页面滚动到此模块时，会自动触发此动画，注意：建议在某一个页面的模块中启用，并且要根据模块的实际情况适当开启，合理配置才能达到最佳效果',
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zibll'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
            </label>
        </p>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_keywords'], 'on'); ?> id="<?php echo $this->get_field_id('show_keywords'); ?>" name="<?php echo $this->get_field_name('show_keywords'); ?>"> 显示热门搜索关键词
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_history'], 'on'); ?> id="<?php echo $this->get_field_id('show_history'); ?>" name="<?php echo $this->get_field_name('show_history'); ?>"> 显示用户搜索历史
            </label>
        </p>
        <p>
            <label>
                热门搜索-标题：
                <input style="width:100%;" id="<?php echo $this->get_field_id('keywords_title');
        ?>" name="<?php echo $this->get_field_name('keywords_title');
        ?>" type="text" value="<?php echo $instance['keywords_title'];
        ?>" />
            </label>
        </p>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_input_cat'], 'on'); ?> id="<?php echo $this->get_field_id('show_input_cat'); ?>" name="<?php echo $this->get_field_name('show_input_cat'); ?>"> 显示分类
            </label>
        </p>

        <p>
            <label>
                默认已选择的分类：
                <select style="width:100%;" name="<?php echo $this->get_field_name('in_cat'); ?>">
                    <?php echo zib_widget_option('cat', $instance['in_cat']); ?>
                </select>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_more_cat'], 'on'); ?> id="<?php echo $this->get_field_id('show_more_cat'); ?>" name="<?php echo $this->get_field_name('show_more_cat'); ?>"> 显示更多分类选择框
            </label>
        </p>
        <p>
            <label>
                更多分类的ID（默认为全部分类，如需自定义则将分类的ID填入下方，多个ID用逗号隔开）：
                <input style="width:100%;" id="<?php echo $this->get_field_id('more_cats');
        ?>" name="<?php echo $this->get_field_name('more_cats');
        ?>" type="text" value="<?php echo $instance['more_cats'];
        ?>" />
            </label>
        </p>
    <?php
}
}

////---------公告栏--------、、、、、、、
class widget_ui_notice extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_notice',
            'w_name'      => _name('滚动公告'),
            'in_affix'    => '',
            'classname'   => '',
            'description' => '可做公告栏或者其他滚动显示内容',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function form($instance)
    {
        $defaults = array(
            'blank'            => '',
            'alignment'        => '',
            'radius'           => '',
            'null'             => '',
            'in_affix'         => '',
            'color'            => 'c-blue',
            'img_ids'          => array(),
            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $defaults['img_ids'][] = array(
            'title' => '子比主题，更优雅的Wordpress主题',
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $defaults['img_ids'][] = array(
            'title' => '更优雅的WordPress网站主题：子比主题！全面开启',
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $img_html = '';
        $img_i    = 0;

        foreach ($instance['img_ids'] as $category) {
            $_tt     = '<div class="panel"><h4 class="panel-title">消息' . ($img_i + 1) . '：' . $instance['img_ids'][$img_i]['title'] . '</h4><div class="panel-hide panel-conter">';
            $_html_a = '<label>消息' . ($img_i + 1) . '-内容（必填）：<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].title" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][title]" value="' . $instance['img_ids'][$img_i]['title'] . '" /></label>';
            $_html_b = '<label>消息' . ($img_i + 1) . '-图标（填写FA图标class）：<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].icon" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][icon]" value="' . $instance['img_ids'][$img_i]['icon'] . '" /></label>';
            $_html_b .= '<label>消息' . ($img_i + 1) . '-链接：<input style="width:100%;" type="text" id="' . $this->get_field_id('img_ids') . '[' . $img_i . '].href" name="' . $this->get_field_name('img_ids') . '[' . $img_i . '][href]" value="' . $instance['img_ids'][$img_i]['href'] . '" /></label>';

            $_tt2 = '</div></div>';
            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_tt2 . '</div>';
            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . $this->get_field_name('img_ids') . '" data-count="' . $img_i . '" class="button add_button add_notice_button">添加栏目</button>';
        $add_b .= '<button type="button" data-name="' . $this->get_field_name('img_ids') . '" data-count="' . $img_i . '" class="button rem_lists_button">删除栏目</button>';
        $img_html .= $add_b;
        //echo '<pre>' . json_encode($instance) . '</pre>';
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        ?>
        <p>
            显示一个公告栏，多个消息滚动显示,请注意控制长度，否则在移动端显示不全
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['blank'], 'on'); ?> id="<?php echo $this->get_field_id('blank'); ?>" name="<?php echo $this->get_field_name('blank'); ?>"> 链接新窗口打开
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['radius'], 'on'); ?> id="<?php echo $this->get_field_id('radius'); ?>" name="<?php echo $this->get_field_name('radius'); ?>"> 两边显示为圆形
            </label>
        </p>
        <p>
            <label>
                主题色彩：
                <select style="width:100%;" name="<?php echo $this->get_field_name('color'); ?>">
                    <option value="c-red" <?php selected('c-red', $instance['color']); ?>>透明粉红</option>
                    <option value="c-yellow" <?php selected('c-yellow', $instance['color']); ?>>透明黄</option>
                    <option value="c-blue" <?php selected('c-blue', $instance['color']); ?>>透明蓝</option>
                    <option value="c-green" <?php selected('c-green', $instance['color']); ?>>透明绿</option>
                    <option value="c-purple" <?php selected('c-purple', $instance['color']); ?>>透明紫</option>
                    <option value="c-red-2" <?php selected('c-red', $instance['color']); ?>>透明红</option>
                    <option value="c-yellow-2" <?php selected('c-yellow', $instance['color']); ?>>透明橘黄</option>
                    <option value="c-blue-2" <?php selected('c-blue', $instance['color']); ?>>透明深蓝</option>
                    <option value="c-green-2" <?php selected('c-green', $instance['color']); ?>>透明墨绿</option>
                    <option value="c-purple-2" <?php selected('c-purple', $instance['color']); ?>>透明深紫</option>
                    <option value="b-theme sbg" <?php selected('b-theme', $instance['color']); ?>>主题色</option>
                    <option value="b-red sbg" <?php selected('b-red', $instance['color']); ?>>红色</option>
                    <option value="b-yellow sbg" <?php selected('b-yellow', $instance['color']); ?>>黄色</option>
                    <option value="b-blue sbg" <?php selected('b-blue', $instance['color']); ?>>蓝色</option>
                    <option value="b-green sbg" <?php selected('b-green', $instance['color']); ?>>绿色</option>
                    <option value="b-purple sbg" <?php selected('b-purple', $instance['color']); ?>>紫色</option>
                </select>
            </label>
        </p>
        <p>
            <label>
                对齐方式：
                <select style="width:100%;" name="<?php echo $this->get_field_name('alignment'); ?>">
                    <option value="" <?php selected('', $instance['alignment']); ?>>靠左</option>
                    <option value="text-center" <?php selected('text-center', $instance['alignment']); ?>>居中</option>
                    <option value="text-right" <?php selected('text-right', $instance['alignment']); ?>>靠右</option>
                </select>
            </label>
        </p>
        <div class="widget_ui_slider_lists">
            <?php echo $img_html; ?>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox hide" type="checkbox" <?php checked($instance['null'], 'on'); ?> id="<?php echo $this->get_field_id('null'); ?>" name="<?php echo $this->get_field_name('null'); ?>"><a class="button ok_button">应用</a>
            </label>
        </div>
        <?php wp_enqueue_media(); ?>
    <?php
}

    public function widget($args, $instance)
    {

        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);

        $defaults = array(
            'blank'     => '',
            'alignment' => '',
            'radius'    => '',
            'null'      => '',
            'in_affix'  => '',
            'color'     => 'c-blue',
            'img_ids'   => array(),
        );

        $defaults['img_ids'][] = array(
            'title' => 'Zibll 官方开源版 1.0.0 已发布，欢迎参与贡献！',
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $defaults['img_ids'][] = array(
            'title' => '更优雅的WordPress网站主题：子比主题！全面开启',
            'icon'  => 'fa-home',
            'href'  => 'https://zibll.com',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $links = array(
            'class' => $instance['alignment'] . ' ' . $instance['color'] . ($instance['radius'] ? ' radius' : ' radius8'),
        );
        foreach ($instance['img_ids'] as $slide_img) {
            if ($slide_img['title']) {
                $slide = array(
                    'title' => $slide_img['title'],
                    'href'  => $slide_img['href'],
                    'blank' => $instance['blank'],
                    'icon'  => $slide_img['icon'],
                );
                $links['notice'][] = $slide;
            }
        }
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        zib_notice($links);
        echo '</div>';

        //echo '<pre>'.json_encode($instance).'</pre>';
        ?>

    <?php
}
}

/////链接列表-------------------------------
class widget_ui_links_lists_2 extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_links_lists_2',
            'w_name'      => _name('链接列表(新版)'),
            'classname'   => '',
            'description' => '速插入链接列表，很适合做友情链接，新版快链接列表模块，通过后台统一管理链接',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function form($instance)
    {
        $defaults = array(
            'title'            => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url'     => '',
            'in_affix'         => '',
            'show_box'         => '',
            'type'             => 'all',
            'blank'            => '',
            'go_link'          => '',
            'alignment'        => '',
            'links_cats'       => '',
            'links_orderby'    => 'name',
            'links_order'      => 'ASC',
            'links_limit'      => 0,
            'null'             => '',

            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $instance     = wp_parse_args((array) $instance, $defaults);
        $page_input[] = array(
            'name'  => __('标题：', 'zibll'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zibll'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zibll'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zibll'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );

        $page_input[] = array(
            'name'    => __('入场动画', 'zibll'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => '当页面滚动到此模块时，会自动触发此动画，注意：建议在某一个页面的模块中启用，并且要根据模块的实际情况适当开启，合理配置才能达到最佳效果',
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zibll'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);

        ?>
        <p>
            快速插入链接列表，你可搭配是否显示链接图片、简介等，但请注意统一性
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
            </label>
        </p>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_box'], 'on'); ?> id="<?php echo $this->get_field_id('show_box'); ?>" name="<?php echo $this->get_field_name('show_box'); ?>"> 显示框架盒子
            </label>
        </p>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['go_link'], 'on'); ?> id="<?php echo $this->get_field_id('go_link'); ?>" name="<?php echo $this->get_field_name('go_link'); ?>"> 链接重定向<a href="javascript:;" title="将非本站链接转为本站链接，有利于SEO"> ？</a>
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['nofollow'] ?? '', 'on'); ?> id="<?php echo $this->get_field_id('nofollow'); ?>" name="<?php echo $this->get_field_name('nofollow'); ?>"> 添加nofollow标记 <a href="javascript:;" title="nofollow标记用于告知搜索引擎建议不抓取，一般友情链接建议关闭"> ？</a>
            </label>
        </p>

        <p>
            <label>
                对齐方式：
                <select style="width:100%;" name="<?php echo $this->get_field_name('alignment'); ?>">
                    <option value="" <?php selected('', $instance['alignment']); ?>>靠左</option>
                    <option value="center" <?php selected('center', $instance['alignment']); ?>>居中</option>
                    <option value="right" <?php selected('right', $instance['alignment']); ?>>靠右</option>
                </select>
            </label>
        </p>
        <p>
            <label>
                显示样式：
                <select style="width:100%;" name="<?php echo $this->get_field_name('type'); ?>">
                    <option value="card" <?php selected('card', $instance['type']); ?>>图文模式</option>
                    <option value="bigcard" <?php selected('bigcard', $instance['type']); ?>>卡片模式</option>
                    <option value="image" <?php selected('image', $instance['type']); ?>>纯图模式</option>
                    <option value="simple" <?php selected('simple', $instance['type']); ?>>极简模式</option>
                </select>
            </label>
        </p>
        <p>
            <label>
                选择链接分类：
                <select style="width:100%;" name="<?php echo $this->get_field_name('links_cats'); ?>">
                    <option value="all" <?php selected('all', $instance['links_cats']); ?>>全部分类</option>

                    <?php
$options_linkcats_obj = get_terms(['taxonomy' => 'link_category'], ['hide_empty' => false]);
        foreach ($options_linkcats_obj as $tag) {
            $options_linkcats[$tag->term_id] = $tag->name;
            echo '<option value="' . $tag->term_id . '" ' . selected($tag->term_id, $instance['links_cats'], false) . '>' . $tag->name . '</option>';
        }
        ?>
                </select>
                <span style="margin-bottom: 3px;color: #047aea;">请在后台-链接-添加链接以及链接分类</span>
            </label>
        </p>
        <p>
            <label>
                排序方式：
                <select style="width:100%;" name="<?php echo $this->get_field_name('links_orderby'); ?>">
                    <option value="name" <?php selected('name', $instance['links_orderby']); ?>>名称排序</option>
                    <option value="updated" <?php selected('updated', $instance['links_orderby']); ?>>更新时间</option>
                    <option value="rating" <?php selected('rating', $instance['links_orderby']); ?>>链接评分</option>
                    <option value="rand" <?php selected('rand', $instance['links_orderby']); ?>>随机排序</option>
                </select>
            </label>
        </p>
        <p>
            <label>
                升序倒序：
                <select style="width:100%;" name="<?php echo $this->get_field_name('links_order'); ?>">
                    <option value="ASC" <?php selected('ASC', $instance['links_order']); ?>>升序</option>
                    <option value="DESC" <?php selected('DESC', $instance['links_order']); ?>>倒序</option>
                </select>
            </label>
        </p>
        <p>
            <label>
                最大显示数量（填0则为显示全部）：
                <input style="width:100%;" id="<?php echo $this->get_field_id('links_limit');
        ?>" name="<?php echo $this->get_field_name('links_limit');
        ?>" type="text" value="<?php echo $instance['links_limit'];
        ?>" />
            </label>
        </p>
    <?php
}

    public function widget($args, $instance)
    {

        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);

        $defaults = array(
            'title'            => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url'     => '',
            'alignment'        => '',
            'show_box'         => '',
            'go_link'          => '',
            'in_affix'         => '',
            'type'             => 'card',
            'links_cats'       => '',
            'links_orderby'    => 'name',
            'links_order'      => 'ASC',
            'links_limit'      => '-1',
            'blank'            => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $instance   = wp_parse_args((array) $instance, $defaults);
        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }
        $links = array();

        $links_args = array(
            'orderby'  => $instance['links_orderby'], //排序方式
            'order'    => $instance['links_order'], //升序还是降序
            'limit'    => $instance['links_limit'] ? (int) $instance['links_limit'] : -1, //最多显示数量
            'category' => $instance['links_cats'], //以逗号分隔的类别ID列表
        );

        if ($instance['links_cats'] == 'all') {
            unset($links_args['category']);
        }

        $links    = get_bookmarks($links_args);
        $nofollow = !empty($instance['nofollow']);

        $attr  = '';
        $class = 'links-widget mb20';
        $class .= $instance['alignment'] ? ' text-' . $instance['alignment'] : '';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';
        echo $title;
        echo '<div class="links-box links-style-' . $instance['type'] . $class . ($instance['show_box'] ? ' zib-widget' : '') . '">';
        echo zib_links_box($links, $instance['type'], $nofollow, $instance['go_link']);
        echo '</div>';
        echo '</div>';

        //echo '<pre>'.json_encode($instance).'</pre>';
        ?>

    <?php
}
}

class widget_ui_new_comment extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_new_comment',
            'w_name'      => _name('最近评论'),
            'classname'   => '',
            'description' => '显示网友最新的评论，建议显示在侧边栏',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }

    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);
        $defaults = array(
            'title'            => '',
            'in_affix'         => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url'     => '',
            'limit'            => 8,
            'outer'            => '1',
            'outpost'          => '',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        $attr     = '';
        $class    = 'theme-box';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';
        $title = apply_filters('zib_widget_title', $instance);
        echo $title;

        echo '<div class="box-body comment-mini-lists zib-widget">';
        zib_widget_comments($instance['limit'], $instance['outpost'], $instance['outer']);
        echo '</div>';
        echo '</div>';
    }

    public function form($instance)
    {
        $defaults = array(
            'title'            => '',
            'in_affix'         => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url'     => '',
            'limit'            => 8,
            'outer'            => '1',
            'obs_animation'    => '',
            'animation_repeat' => false,
            'outpost'          => '',
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $page_input[] = array(
            'name'  => __('标题：', 'zibll'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zibll'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zibll'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zibll'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'    => __('入场动画', 'zibll'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => '当页面滚动到此模块时，会自动触发此动画，注意：建议在某一个页面的模块中启用，并且要根据模块的实际情况适当开启，合理配置才能达到最佳效果',
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zibll'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        );
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);

        ?>

        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
            </label>
        </p>
        <p>
            <label>
                显示数目：
                <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo $instance['limit']; ?>" />
            </label>
        </p>
        <p>
            <?php zib_user_help('排除某用户ID'); ?>
            <label>
                <input class="widefat" id="<?php echo $this->get_field_id('outer'); ?>" name="<?php echo $this->get_field_name('outer'); ?>" type="text" value="<?php echo $instance['outer']; ?>" />
            </label>
        </p>
        <p>
            <label>
                排除某文章ID：
                <input class="widefat" id="<?php echo $this->get_field_id('outpost'); ?>" name="<?php echo $this->get_field_name('outpost'); ?>" type="number" value="<?php echo $instance['outpost']; ?>" />
            </label>
        </p>
    <?php
}
}

class widget_ui_posts_navs extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_posts_navs',
            'w_name'      => _name('文章目录树'),
            'classname'   => '',
            'description' => '显示文章的目录树，非文章、帖子页则不显示内容，同时标题超过3个才会显示',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }

    public function widget($args, $instance)
    {

        extract($args);
        $defaults = array(
            'title'      => '',
            'mini_title' => '',
            'in_affix'   => '',
        );
        $instance   = wp_parse_args((array) $instance, $defaults);
        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title = esc_html($instance['title']) . esc_html($mini_title);
        if ($title) {
            $title = ' data-title="' . $title . '"';
        }
        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        echo '<div' . $in_affix . ' class="posts-nav-box"' . $title . '></div>';
    }

    public function form($instance)
    {
        $defaults = array(
            'title'      => '文章目录',
            'in_affix'   => '',
            'mini_title' => '',
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        ?>
        <p>
            <label>
                <i style="width:100%;color:#f80;">显示文章的目录，添加在非文章/帖子页则不会显示任何内容。在实时预览添加此模块时，请注意查看是否在文章页。内容中标题超过3个才会显示</i>
            </label>
        </p>
        <p>
            <label>
                标题：
                <input style="width:100%;" id="<?php echo $this->get_field_id('title');
        ?>" name="<?php echo $this->get_field_name('title');
        ?>" type="text" value="<?php echo $instance['title'];
        ?>" />
            </label>
        </p>
        <p>
            <label>
                副标题：
                <input style="width:100%;" id="<?php echo $this->get_field_id('mini_title');
        ?>" name="<?php echo $this->get_field_name('mini_title');
        ?>" type="text" value="<?php echo $instance['mini_title'];
        ?>" />
            </label>
        </p>
        <p>
            <label>
                <input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
            </label>
        </p>
<?php
}
}

/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
/////----- //一言//------ //一言//------ //一言//------ //一言//------ //一言//----
class widget_ui_yiyan extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_yiyan',
            'w_name'      => _name('一言'),
            'classname'   => 'yiyan-box main-bg theme-box text-center box-body radius8 main-shadow',
            'description' => '这是一个显示一言的小工具，每次页面刷新或者每隔30秒会自动更新内容',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }

    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);
        $defaults = array(
            'title'            => '',
            'mini_title'       => '',
            'in_affix'         => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        $title    = apply_filters('zib_widget_title', $instance);

        $attr  = '';
        $class = 'theme-box';
        if ($instance['obs_animation']) {
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $class .= ' obs-animate ani-' . $instance['obs_animation'];
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        echo '<div' . $in_affix . ' class="' . $class . '" ' . $attr . '>';
        echo $title;
        echo '<div class="yiyan-box main-bg text-center box-body radius8 main-shadow">';
        echo '<div class="yiyan"></div>';
        echo '</div>';
        echo '</div>';
    }
    public function form($instance)
    {
        $defaults = array(
            'title'            => '',
            'mini_title'       => '',
            'in_affix'         => '',
            'more_but_url'     => '',
            'mini_title'       => '',
            'more_but'         => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'obs_animation'    => '',
            'animation_repeat' => false,
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $page_input[] = array(
            'name'  => __('标题：', 'zibll'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zibll'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zibll'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zibll'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'id'    => $this->get_field_name('in_affix'),
            'std'   => $instance['in_affix'],
            'desc'  => '侧栏随动（仅在侧边栏有效）',
            'style' => 'margin: 15px auto;',
            'type'  => 'checkbox',
        );
        $page_input[] = array(
            'name'    => __('入场动画', 'zibll'),
            'id'      => $this->get_field_name('obs_animation'),
            'std'     => $instance['obs_animation'],
            'style'   => 'margin: 10px auto;',
            'type'    => 'select',
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
            'desc'    => '当页面滚动到此模块时，会自动触发此动画，注意：建议在某一个页面的模块中启用，并且要根据模块的实际情况适当开启，合理配置才能达到最佳效果',
        );
        $page_input[] = array(
            'name'  => __('重复动画', 'zibll'),
            'id'    => $this->get_field_name('animation_repeat'),
            'std'   => $instance['animation_repeat'],
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
            'desc'  => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));

        echo zib_edit_input_construct($page_input);
    }
}

//图标卡片
Zib_CFSwidget::create('zib_widget_ui_icon_card', array(
    'title'       => '图标卡片[竖版]',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '图标与文案配合的特色卡片，文字在图标下方，更适合做移动端的图标按钮',
    'fields'      => array(
        array(
            'title'   => '模块背景',
            'type'    => 'switcher',
            'id'      => 'show_widget_bg',
            'label'   => '显示模块背景',
            'default' => false,
            'type'    => 'switcher',
        ),
        array(
            'id'       => 'pc_row',
            'title'    => '排列布局',
            'subtitle' => 'PC端单行排列数量',
            'default'  => 4,
            'class'    => 'button-mini',
            'default'  => 2,
            'options'  => array(
                1  => '1个',
                2  => '2个',
                3  => '3个',
                4  => '4个',
                6  => '6个',
                12 => '12个',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'       => 'm_row',
            'title'    => ' ',
            'subtitle' => '移动端单行排列数量',
            'decs'     => '请根据此模块放置位置的宽度合理调整单行数量，避免显示不佳',
            'default'  => 2,
            'class'    => 'compact button-mini',
            'default'  => 2,
            'options'  => array(
                1  => '1个',
                2  => '2个',
                3  => '3个',
                4  => '4个',
                6  => '6个',
                12 => '12个',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'      => 'size',
            'title'   => '图标尺寸微调',
            'default' => 0,
            'max'     => 10,
            'min'     => -10,
            'step'    => 1,
            'unit'    => '',
            'type'    => 'slider',
        ),
        array(
            'id'                     => 'cards',
            'title'                  => '添加图标',
            'subtitle'               => '<div style="color:#ee5a5a;font-size: 12px;"><i class="fa fa-fw fa-info-circle fa-fw"></i> 文案属于可选项目，同一组模块文案的字数差距不能太大，否则会出现不整齐的现象</div>',
            'type'                   => 'group',
            'button_title'           => '添加图标',
            'accordion_title_auto'   => false,
            'accordion_title_number' => true,
            'default'                => array(),
            'fields'                 => array(
                array(
                    'id'           => 'icon',
                    'type'         => 'icon',
                    'title'        => '选择图标',
                    'button_title' => '选择图标',
                    'default'      => 'fa fa-magic',
                ),
                array(
                    'title'      => '自定义图标',
                    'desc'       => '如您想使用非自带图标，可以在此输入自定义图标代码',
                    'class'      => 'compact',
                    'id'         => 'customize_icon',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'id'           => 'link',
                    'type'         => 'link',
                    'title'        => '跳转链接',
                    'default'      => array(),
                    'add_title'    => '添加链接',
                    'edit_title'   => '编辑链接',
                    'remove_title' => '删除链接',
                ),
                array(
                    'title'   => '图标样式',
                    'type'    => 'switcher',
                    'id'      => 'icon_radius',
                    'label'   => '显示图标背景',
                    'default' => false,
                    'type'    => 'switcher',
                ),
                array(
                    'dependency' => array('icon_radius', '!=', ''),
                    'title'      => ' ',
                    'subtitle'   => '图标样式',
                    'id'         => 'icon_class',
                    'class'      => 'compact skin-color',
                    'default'    => 'c-yellow',
                    'type'       => 'palette',
                    'options'    => CFS_Module::zib_palette(
                        array(
                            'transparent' => array('rgba(114, 114, 114, 0.1)'),
                        )
                    ),
                ),
                array(
                    'dependency' => array('icon_radius|icon_custom_color', '==|==', '|'),
                    'title'      => ' ',
                    'subtitle'   => '图标颜色',
                    'id'         => 'icon_color',
                    'class'      => 'compact skin-color',
                    'default'    => 'key-color',
                    'type'       => 'palette',
                    'options'    => array(
                        'key-color'  => array('#333'),
                        'c-red'      => array('rgba(255, 84, 115,1)'),
                        'c-red-2'    => array('rgba(194, 41, 46,1)'),
                        'c-yellow'   => array('rgba(255, 111, 6,1)'),
                        'c-yellow-2' => array('rgba(179, 103, 8,1)'),
                        'c-cyan'     => array('rgba(8, 196, 193, 1)'),
                        'c-blue'     => array('rgba(41, 151, 247,1)'),
                        'c-blue-2'   => array('rgba(77, 130, 249,1)'),
                        'c-green'    => array('rgba(18, 185, 40,1)'),
                        'c-green-2'  => array('rgba(72, 135, 24,1)'),
                        'c-purple'   => array('rgba(213, 72, 245,1)'),
                        'c-purple-2' => array('rgba(154, 72, 245,1)'),
                    ),
                ),
                array(
                    'dependency' => array('icon_radius', '==', ''),
                    'title'      => ' ',
                    'subtitle'   => '自定义图标颜色（如需选择预置颜色，请清空此处）',
                    'id'         => 'icon_custom_color',
                    'class'      => 'compact',
                    'default'    => '',
                    'type'       => 'color',
                ),
                array(
                    'title'      => __('文案标题', 'zibll'),
                    'id'         => 'title',
                    'desc'       => '第一行文案，字体稍大一点',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'      => __('文案简介', 'zibll'),
                    'id'         => 'desc',
                    'class'      => 'compact',
                    'desc'       => '第二行文案，字体稍小一点',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                ),
            ),
        ),
    ),
));

//图标卡片
function zib_widget_ui_icon_card($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['cards'][0]) || !$show_class) {
        return;
    }

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $cards  = $instance['cards'];
    $is_row = count($cards) > 1;
    $html   = '';
    if ($cards) {
        foreach ($cards as $card) {
            $html .= $is_row ? '<div class="' . $row_class . '">' : '';
            $card['icon_size'] = isset($instance['size']) && 0 != $instance['size'] ? (16 + (int) $instance['size']) : '';
            if (!$instance['show_widget_bg']) {
                $card['class'] = 'mb20';
            }
            $html .= zib_icon_card($card);
            $html .= $is_row ? '</div>' : '';
        }
    }
    Zib_CFSwidget::echo_before($instance, 'clearfix');
    echo $instance['show_widget_bg'] ? '<div class="zib-widget nobottom notop">' : '';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo $instance['show_widget_bg'] ? '</div>' : '';
    Zib_CFSwidget::echo_after($instance);
}

//视频
Zib_CFSwidget::create('zib_widget_ui_dplayer', array(
    'title'       => '视频',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '显示视频的模块，支持本地视频以及m3u8、mpd、flv等流媒体格式',
    'fields'      => array(
        array(
            'title'   => '视频地址',
            'id'      => 'url',
            'type'    => 'upload',
            'library' => 'video',
            'preview' => false,
            'default' => '',
            'desc'    => '输入视频地址或选择、上传本地视频',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'title'      => '视频封面',
            'id'         => 'pic',
            'type'       => 'upload',
            'library'    => 'image',
            'default'    => '',
            'desc'       => '为视频添加图片封面(可选)',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'title'      => '自动播放',
            'id'         => 'autoplay',
            'type'       => 'switcher',
            'label'      => '部分浏览器不兼容',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'id'         => 'loop',
            'title'      => '循环播放',
            'type'       => 'switcher',
            'label'      => '部分浏览器不兼容',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'id'         => 'volume',
            'title'      => '初始音量',
            'default'    => 100,
            'max'        => 100,
            'min'        => 0,
            'step'       => 5,
            'unit'       => '%',
            'type'       => 'slider',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'id'         => 'hide_controller',
            'title'      => '隐藏播放控件',
            'type'       => 'switcher',
            'label'      => '隐藏进度条及控制按钮',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'id'         => 'scale_height',
            'title'      => '固定长宽比例',
            'default'    => 0,
            'max'        => 200,
            'min'        => 0,
            'step'       => 5,
            'unit'       => '%',
            'type'       => 'slider',
            'desc'       => '为0则不固定长宽比例',
        ),
    ),
));

//视频
function zib_widget_ui_dplayer($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['url']) || !$show_class) {
        return;
    }

    $args = array(
        'class'        => '',
        'url'          => $instance['url'],
        'pic'          => $instance['pic'],
        'autoplay'     => $instance['autoplay'],
        'loop'         => $instance['loop'],
        'scale_height' => $instance['scale_height'],
        'volume'       => round(($instance['volume'] / 100), 2),
    );
    $dplayer = zib_new_dplayer($args, false);

    Zib_CFSwidget::echo_before($instance, 'mb20');
    echo '<div class="relative-h radius8' . (!empty($instance['hide_controller']) ? ' controller-hide' : '') . '">';
    echo $dplayer;
    echo '</div>';
    Zib_CFSwidget::echo_after($instance);
}

//超级嵌入
Zib_CFSwidget::create('zib_widget_ui_iframe', array(
    'title'       => '超级嵌入',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '嵌入其他在线内容，通常用于嵌入其它网站的视频播放器或音乐播放器，也可以嵌入其它任意在线内容',
    'fields'      => array(
        array(
            'id'          => 'url',
            'title'       => '嵌入地址',
            'placeholder' => '请输入需要嵌入的链接，或者直接粘贴iframe嵌入代码',
            'desc'        => '请输入需要嵌入的链接，或者直接粘贴iframe嵌入代码',
            'default'     => '',
            'attributes'  => array(
                'rows' => 4,
            ),
            'sanitize'    => false,
            'type'        => 'textarea',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'id'         => 'aspect',
            'title'      => '长宽比例设置',
            'default'    => 55,
            'max'        => 300,
            'min'        => 20,
            'step'       => 5,
            'unit'       => '%',
            'desc'       => '设置高度与宽度的占比，以保持对应的长宽比例',
            'type'       => 'slider',
        ),
        array(
            'dependency' => array('url', '!=', ''),
            'id'         => 'allowfullscreen',
            'type'       => 'switcher',
            'label'      => '允许内容全屏显示',
        ),
    ),

));

//超级嵌入
function zib_widget_ui_iframe($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['url']) || !$show_class) {
        return;
    }

    $url = $instance['url'];
    if (stristr($url, '<iframe') && stristr($url, '</iframe>')) {
        $iframe = $url;
    } else {
        $iframe = '<iframe class="lazyload"' . (!empty($instance['allowfullscreen']) ? ' allowfullscreen="allowfullscreen"' : '') . ' framespacing="0" border="0" frameborder="no" data-src="' . esc_url($url) . '"></iframe>';
    }

    Zib_CFSwidget::echo_before($instance, 'mb20');
    echo '<div class="wp-block-embed is-type-video relative-h radius8">';
    echo '<div style="padding-bottom:' . $instance['aspect'] . '%">';
    echo $iframe;
    echo '</div>';
    echo '</div>';
    Zib_CFSwidget::echo_after($instance);
}

//图文封面
Zib_CFSwidget::create('zib_widget_ui_graphic_cover', array(
    'title'            => '图文视频封面卡片',
    'zib_title'        => true,
    'zib_affix'        => true,
    'zib_show'         => true,
    'zib_animation_in' => false,
    'description'      => '',
    'fields'           => array(

        array(
            'title'   => '入场动画',
            'id'      => 'obs_animation',
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
        ),
        array(
            'dependency' => array('obs_animation', '!=', ''),
            'title'      => '重复动画',
            'id'         => 'animation_repeat',
            'type'       => 'switcher',
            'class'      => 'compact',
            'default'    => false,
            'desc'       => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        ),

        array(
            'id'       => 'pc_row',
            'title'    => '排列布局',
            'subtitle' => 'PC端单行排列数量',
            'default'  => 4,
            'options'  => array(
                1  => '1个',
                2  => '2个',
                3  => '3个',
                4  => '4个',
                6  => '6个',
                12 => '12个',
            ),
            'type'     => 'button_set',
            'class'    => 'button-mini',
        ),
        array(
            'id'       => 'm_row',
            'title'    => ' ',
            'subtitle' => '移动端单行排列数量',
            'decs'     => '请根据此模块放置位置的宽度合理调整单行数量，避免显示不佳',
            'class'    => 'compact button-mini',
            'default'  => 2,
            'options'  => array(
                1  => '1个',
                2  => '2个',
                3  => '3个',
                4  => '4个',
                6  => '6个',
                12 => '12个',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'      => 'mask_opacity',
            'title'   => '遮罩透明度',
            'help'    => '图片上显示的黑色遮罩层的透明度',
            'default' => 10,
            'max'     => 90,
            'min'     => 0,
            'step'    => 1,
            'unit'    => '%',
            'type'    => 'slider',
        ),
        array(
            'id'      => 'height_scale',
            'title'   => '封面长宽比例',
            'default' => 30,
            'max'     => 300,
            'min'     => 5,
            'step'    => 5,
            'unit'    => '%',
            'type'    => 'spinner',
        ),
        array(
            'id'       => 'font_size_pc',
            'title'    => '文字样式',
            'subtitle' => 'PC端字体大小',
            'default'  => 18,
            'max'      => 80,
            'min'      => 10,
            'step'     => 2,
            'unit'     => 'px',
            'type'     => 'spinner',
        ),
        array(
            'id'       => 'font_size_m',
            'title'    => ' ',
            'class'    => 'compact',
            'subtitle' => '移动端字体大小',
            'default'  => 14,
            'max'      => 80,
            'min'      => 10,
            'step'     => 2,
            'unit'     => 'px',
            'type'     => 'spinner',
        ),
        array(
            'id'    => 'font_bold',
            'class' => 'compact',
            'type'  => 'switcher',
            'label' => '粗体显示',
        ),
        array(
            'class'    => 'compact',
            'id'       => 'font_color',
            'title'    => ' ',
            'type'     => 'color',
            'subtitle' => '文字颜色',
        ),
        array(
            'id'           => 'covers',
            'title'        => '添加封面',
            'type'         => 'group',
            'button_title' => '添加内容',
            'default'      => array(),
            'desc'         => '<div class="c-yellow">注意：由于移动端多数浏览器不支持视频背景功能，所以移动端不会显示视频！</div>',
            'fields'       => array(
                array(
                    'title'   => __('图片背景', 'zibll'),
                    'id'      => 'image',
                    'default' => '',
                    'preview' => true,
                    'library' => 'image',
                    'type'    => 'upload',
                ),
                array(
                    'title'   => __('视频背景', 'zibll'),
                    'id'      => 'video',
                    'default' => '',
                    'class'   => 'compact',
                    'preview' => false,
                    'library' => 'video',
                    'type'    => 'upload',
                    'desc'    => '（必填）图片背景、视频背景至少二选一，如果同时设置则PC端视频优先（PC端视频加载失败则显示图片），<span class="c-yellow">移动端只显示图片</span>',
                ),
                array(
                    'title'   => __('显示规则', 'zibll') . zib_get_csf_option_new_badge()['7.1'],
                    'id'      => 'hide',
                    'type'    => 'radio',
                    'inline'  => true,
                    'options' => array(
                        ''   => '全部显示',
                        'pc' => 'PC端不显示',
                        'm'  => '移动端不显示',
                    ),
                    'default' => '',
                ),
                array(
                    'title'      => '文字',
                    'id'         => 'title',
                    'default'    => '',
                    'desc'       => '支持HTML代码',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                    'type'       => 'textarea',
                ),
                array(
                    'id'           => 'link',
                    'type'         => 'link',
                    'title'        => '跳转链接',
                    'default'      => array(),
                    'add_title'    => '添加链接',
                    'edit_title'   => '编辑链接',
                    'remove_title' => '删除链接',
                ),
            ),
        ),
    ),
));

function zib_widget_ui_graphic_cover($args, $instance)
{
    $defaults = array(
        'pc_row'           => 4,
        'm_row'            => 1,
        'font_size_pc'     => 18,
        'font_size_m'      => 14,
        'font_bold'        => false,
        'font_color'       => '',
        'covers'           => array(),
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    $show_class = Zib_CFSwidget::show_class($instance);
    if ((empty($instance['covers'][0]['image']) && empty($instance['covers'][0]['video'])) || !$show_class) {
        return;
    }

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $is_row = count($instance['covers']) > 1;
    $html   = '';
    $style  = '';
    $style .= $instance['font_size_pc'] && 14 != $instance['font_size_pc'] ? '--font-size:' . ((int) $instance['font_size_pc']) . 'px;' : '';
    $style .= $instance['font_size_m'] && 14 != $instance['font_size_m'] ? '--font-size-sm:' . ((int) $instance['font_size_m']) . 'px;' : '';
    $style .= $instance['font_bold'] ? '--font-weight:bold;--font-weight-sm:bold;' : '';
    $style .= $instance['font_color'] ? '--color:' . $instance['font_color'] . ';--color-sm:' . $instance['font_color'] . ';' : '';
    $style = $style ? ' style="' . $style . '"' : '';

    foreach ($instance['covers'] as $key => $cover) {

        //显示规则
        if (isset($cover['hide'])) {
            $is_mobile = wp_is_mobile();
            if ((!$is_mobile && $cover['hide'] === 'pc') || ($is_mobile && $cover['hide'] === 'm')) {
                continue;
            }
        }

        $more = $cover['title'] ? '<div class="abs-center text-center graphic-text this-font">' . $cover['title'] . '</div>' : '';
        $card = array(
            'class'        => 'noshadow mb10',
            'img'          => isset($cover['image']) ? $cover['image'] : '',
            'video'        => isset($cover['video']) ? $cover['video'] : '',
            'alt'          => strip_tags($cover['title']),
            'link'         => $cover['link'],
            'lazy'         => zib_is_lazy('lazy_cover'),
            'more'         => $more,
            'height_scale' => $instance['height_scale'],
            'mask_opacity' => $instance['mask_opacity'],
        );

        $attr      = '';
        $col_class = '';
        if ($instance['obs_animation']) {
            $col_class .= ' obs-animate ani-' . $instance['obs_animation'];
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $attr .= ' style=" --delay: ' . strval($key * 0.15) . 's; "';
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        if ($is_row) {
            $col_class .= ' ' . $row_class;
        }

        $html .= '<div class="' . $col_class . '" ' . $attr . '>';
        $html .= zib_graphic_card($card);
        $html .= '</div>';
    }

    Zib_CFSwidget::echo_before($instance, 'widget-graphic-cover ' . ($is_row ? 'mb10' : 'mb20'));
    echo '<div' . $style . '>';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo '</div>';

    Zib_CFSwidget::echo_after($instance);
}

//图标卡片
Zib_CFSwidget::create('zib_widget_ui_icon_cover_card', array(
    'title'       => '图标卡片[横版]',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '图标与文案配合的特色卡片，文字在图标右侧',
    'fields'      => array(
        array(
            'id'       => 'pc_row',
            'title'    => '排列布局',
            'subtitle' => 'PC端单行排列数量',
            'default'  => 4,
            'options'  => array(
                1 => '1个',
                2 => '2个',
                3 => '3个',
                4 => '4个',
                6 => '6个',
            ),
            'type'     => 'button_set',
            'class'    => 'button-mini',
        ),
        array(
            'id'       => 'm_row',
            'title'    => ' ',
            'subtitle' => '移动端单行排列数量',
            'decs'     => '请根据此模块放置位置的宽度合理调整单行数量，避免显示不佳',
            'class'    => 'compact button-mini',
            'default'  => 2,
            'options'  => array(
                1 => '1个',
                2 => '2个',
            ),
            'type'     => 'button_set',
        ),
        array(
            'title'   => '模块背景',
            'type'    => 'switcher',
            'id'      => 'show_widget_bg',
            'label'   => '整个模块显示背景，关闭后每个卡片都显示背景',
            'default' => false,
            'type'    => 'switcher',
        ),
        array(
            'title'   => '方形图标',
            'type'    => 'switcher',
            'id'      => 'icon_radius4',
            'label'   => '图标显示为正方形，而不是圆形',
            'default' => false,
            'type'    => 'switcher',
        ),
        array(
            'id'                     => 'cards',
            'title'                  => '添加卡片',
            'type'                   => 'group',
            'button_title'           => '添加内容',
            'accordion_title_auto'   => false,
            'accordion_title_number' => true,
            'default'                => array(),
            'fields'                 => array(
                array(
                    'id'           => 'icon',
                    'type'         => 'icon',
                    'title'        => '选择图标',
                    'button_title' => '选择图标',
                    'default'      => 'fa fa-magic',
                ),
                array(
                    'title'      => '自定义图标',
                    'desc'       => '如您想使用非自带图标，可以在此输入自定义图标代码',
                    'class'      => 'compact',
                    'id'         => 'customize_icon',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'    => ' ',
                    'subtitle' => '图标颜色',
                    'id'       => 'icon_class',
                    'class'    => 'compact skin-color',
                    'default'  => 'c-yellow',
                    'type'     => 'palette',
                    'options'  => CFS_Module::zib_palette(
                        array(
                            'transparent' => array('rgba(114, 114, 114, 0.1)'),
                        )
                    ),
                ),
                array(
                    'id'           => 'link',
                    'type'         => 'link',
                    'title'        => '跳转链接',
                    'default'      => array(),
                    'add_title'    => '添加链接',
                    'edit_title'   => '编辑链接',
                    'remove_title' => '删除链接',
                ),
                array(
                    'title'      => __('文案标题', 'zibll'),
                    'id'         => 'title',
                    'desc'       => '第一行文案，字体稍大一点',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'      => __('文案简介', 'zibll'),
                    'id'         => 'desc',
                    'class'      => 'compact',
                    'desc'       => '第二行文案，字体稍小一点（支持html，注意代码规范）',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                ),
            ),
        ),
    ),
));

function zib_widget_ui_icon_cover_card($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['cards'][0]) || !$show_class) {
        return;
    }

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $cards  = $instance['cards'];
    $is_row = count($cards) > 1;
    $html   = '';

    if ($cards) {
        foreach ($cards as $card) {
            $html .= $is_row ? '<div class="' . $row_class . '">' : '';
            $card['class'] = $instance['show_widget_bg'] ? 'padding-10' : 'zib-widget mb10';
            $card['icon_class'] .= $instance['icon_radius4'] ? ' radius4' : '';
            $html .= zib_icon_cover_card($card);
            $html .= $is_row ? '</div>' : '';
        }
    }

    Zib_CFSwidget::echo_before($instance, '');
    echo $instance['show_widget_bg'] ? '<div class="zib-widget padding-10">' : '<div class="mb10">';
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    echo '</div>';
    Zib_CFSwidget::echo_after($instance);
}

//横向滚动的合作伙伴模块
Zib_CFSwidget::create('zib_widget_ui_partners_scroll', array(
    'title'            => '横向滚动合作伙伴',
    'zib_title'        => true,
    'zib_affix'        => true,
    'zib_show'         => true,
    'zib_animation_in' => true,
    'description'      => '横向自动滚动的合作伙伴/赞助商/案例列表，支持多行滚动、鼠标悬停暂停、支持 Logo 图片或字体图标',
    'fields'           => array(
        array(
            'id'       => 'rows',
            'title'    => '显示行数',
            'subtitle' => 'PC端显示几行滚动',
            'default'  => 3,
            'options'  => array(
                1 => '1行',
                2 => '2行',
                3 => '3行',
                4 => '4行',
                5 => '5行',
                6 => '6行',
            ),
            'type'     => 'button_set',
            'class'    => 'button-mini',
        ),
        array(
            'id'       => 'm_rows',
            'title'    => ' ',
            'subtitle' => '移动端显示几行滚动',
            'class'    => 'compact button-mini',
            'default'  => 3,
            'options'  => array(
                1 => '1行',
                2 => '2行',
                3 => '3行',
                4 => '4行',
                5 => '5行',
                6 => '6行',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'       => 'style_type',
            'title'    => '卡片样式',
            'subtitle' => '选择合作伙伴卡片的呈现方式',
            'default'  => 'card',
            'options'  => array(
                'card' => '图文卡片(适合正方形logo)',
                'logo' => '纯 Logo',
                'chip' => '精简胶囊',
            ),
            'type'     => 'radio',
            'inline'   => true,
        ),
        array(
            'id'         => 'card_width',
            'title'      => '单卡片宽度',
            'help'       => 'PC端每张卡片的宽度（纯 Logo 模式下宽度会自适应图片，此项不生效）',
            'dependency' => array('style_type', '==', 'card'),
            'default'    => 200,
            'max'        => 500,
            'min'        => 80,
            'step'       => 10,
            'unit'       => 'px',
            'type'       => 'spinner',
        ),
        array(
            'dependency' => array('style_type', '!=', 'chip'),
            'id'         => 'card_height',
            'title'      => '卡片高度',
            'help'       => '每张卡片的高度，纯 Logo 模式建议 70-120',
            'default'    => 60,
            'max'        => 260,
            'min'        => 50,
            'step'       => 5,
            'unit'       => 'px',
            'type'       => 'spinner',
        ),
        array(
            'id'      => 'card_bg',
            'title'   => '卡片背景',
            'label'   => '显示每张合作伙伴卡片的背景',
            'default' => true,
            'type'    => 'switcher',
        ),
        array(
            'dependency' => array('card_bg', '!=', ''),
            'id'         => 'card_radius',
            'title'      => '卡片圆角',
            'default'    => 16,
            'max'        => 60,
            'min'        => 0,
            'step'       => 1,
            'unit'       => 'px',
            'type'       => 'slider',
        ),
        array(
            'id'      => 'speed',
            'title'   => '滚动速度(数越小越快)',
            'default' => 3,
            'max'     => 10,
            'min'     => 0.1,
            'step'    => 0.1,
            'unit'    => 's',
            'type'    => 'slider',
        ),
        array(
            'id'      => 'direction',
            'title'   => '滚动方向',
            'default' => 'alternate',
            'options' => array(
                'alternate' => '奇数行左，偶数行右',
                'left'      => '全部向左',
                'right'     => '全部向右',
            ),
            'type'    => 'radio',
            'inline'  => true,
        ),
        array(
            'id'      => 'pause_hover',
            'label'   => '鼠标悬停暂停',
            'default' => true,
            'type'    => 'switcher',
        ),
        array(
            'id'      => 'show_widget_bg',
            'label'   => '模块背景',
            'help'    => '显示整个模块外层背景',
            'default' => false,
            'type'    => 'switcher',
        ),
        array(
            'id'      => 'go_link',
            'label'   => '外链重定向',
            'help'    => '开启后，将链接转为内部go跳转链接',
            'default' => true,
            'type'    => 'switcher',
        ),
        array(
            'id'      => 'end_fade',
            'label'   => '两端渐隐',
            'help'    => '开启后，卡片两端会渐隐，使卡片无缝连接',
            'default' => true,
            'type'    => 'switcher',
        ),

        //添加链接获取方式，1.通过链接获取 2.手动添加
        array(
            'id'      => 'link_type',
            'title'   => '合作伙伴获取方式',
            'default' => 'link',
            'options' => array(
                'link'   => '通过链接获取',
                'manual' => '手动添加',
            ),
            'type'    => 'radio',
            'inline'  => true,
        ),
        //选择链接分类
        array(
            'dependency'  => array('link_type', '==', 'link'),
            'id'          => 'link_cats',
            'title'       => '选择链接分类',
            'placeholder' => '选择分类',
            'default'     => [],
            'type'        => 'select',
            'options'     => 'categories',
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'query_args'  => array(
                'taxonomy' => array('link_category'),
                'orderby'  => 'taxonomy',
            ),
            'desc'        => '需显示的链接分类，留空则为全部'
        ),
        //选择链接排序方式
        array(
            'dependency' => array('link_type', '==', 'link'),
            'title'      => '最大获取数量',
            'id'         => 'link_limit',
            'default'    => 50,
            'type'       => 'spinner',
            'min'        => 0,
            'step'       => 5,
            'unit'       => '个',
        ),
        array(
            'dependency' => array('link_type', '==', 'link'),
            'id'         => 'link_orderby',
            'title'      => '排序方式',
            'default'    => 'name',
            'type'       => 'select',
            'options'    => array(
                'name'    => __('名称排序'),
                'updated' => __('更新时间'),
                'rating'  => __('链接评分'),
                'rand'    => __('随机排序'),
            ),
        ),
        array(
            'dependency' => array('link_type', '==', 'link'),
            'id'         => 'page_links_order',
            'title'      => ' ',
            'subtitle'   => ' ',
            'default'    => 'ASC',
            'class'      => 'compact',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => array(
                'ASC'  => __('升序'),
                'DESC' => __('降序'),
            ),
        ),
        array(
            'dependency'             => array('link_type', '==', 'manual'),
            'id'                     => 'partners',
            'title'                  => '添加合作伙伴',
            'subtitle'               => '<div class="c-yellow px12"><i class="fa fa-fw fa-info-circle"></i> 建议每行至少添加10个合作伙伴以获得更好的滚动效果</div>',
            'type'                   => 'group',
            'button_title'           => '添加合作伙伴',
            'accordion_title_auto'   => false,
            'accordion_title_number' => true,
            'default'                => array(),
            'fields'                 => array(
                array(
                    'title'      => '名称',
                    'id'         => 'title',
                    'desc'       => '卡片第一行文案，建议简短',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'      => '简介',
                    'id'         => 'desc',
                    'class'      => 'compact',
                    'desc'       => '卡片第二行文案，支持 HTML（仅在图文卡片模式下显示）',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                ),
                array(
                    'title'   => 'Logo图片',
                    'id'      => 'image',
                    'default' => '',
                    'preview' => true,
                    'library' => 'image',
                    'type'    => 'upload',
                    'desc'    => '如果样式选择为图文卡片，则建议全部为正方形图片',
                ),
                array(
                    'id'           => 'link',
                    'type'         => 'link',
                    'title'        => '跳转链接',
                    'default'      => array(),
                    'add_title'    => '添加链接',
                    'edit_title'   => '编辑链接',
                    'remove_title' => '删除链接',
                ),
            ),
        ),
    ),
));

/**
 * 横向滚动合作伙伴 - 渲染函数
 * 布局：将所有合作伙伴均匀分配到若干行；每行 track 复制一份以保证无缝循环
 */
function zib_widget_ui_partners_scroll($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    //合并默认值，避免新老数据结构差异导致 notice
    $defaults = array(
        'rows'           => 1,
        'm_rows'         => 1,
        'style_type'     => 'card',
        'card_width'     => 220,
        'card_height'    => 90,
        'speed'          => 3,
        'direction'      => 'alternate',
        'pause_hover'    => true,
        'show_widget_bg' => true,
        'card_bg'        => true,
        'card_radius'    => 10,
        'partners'       => array(),
        'link_type'      => 'link',
        'link_orderby'   => 'name',
        'link_order'     => 'ASC',
        'link_cats'      => array(),
        'link_limit'     => 50,
        'go_link'        => true,
        'end_fade'       => true,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    $is_mobile = wp_is_mobile();
    $rows      = max(1, (int) ($is_mobile ? $instance['m_rows'] : $instance['rows']));

    //通过链接获取合作伙伴
    if ($instance['link_type'] === 'link') {
        $args = array(
            'orderby' => $instance['link_orderby'] ? $instance['link_orderby'] : 'name', //排序方式
            'order'   => $instance['link_order'] ? $instance['link_order'] : 'ASC', //升序还是降序
            'limit'   => $instance['link_limit'] ? (int) $instance['link_limit'] : -1, //最多显示数量
        );

        if ($instance['link_cats']) {
            $args['category'] = $instance['link_cats'];
        }

        $partners  = array();
        $bookmarks = get_bookmarks($args);
        foreach ($bookmarks as $bookmark) {
            $partners[] = array(
                'title' => $bookmark->link_name,
                'desc'  => $bookmark->link_description,
                'image' => $bookmark->link_image,
                'link'  => [
                    'url'    => $bookmark->link_url,
                    'target' => $bookmark->link_target,
                ],
            );
        }
        $instance['partners'] = $partners;
    }

    $partners = array_values($instance['partners']);
    if (empty($partners)) {
        return;
    }

    //将合作伙伴平均分配到每一行
    $rows_data = array_fill(0, $rows, array());
    foreach ($partners as $i => $partner) {
        $rows_data[$i % $rows][] = $partner;
    }

    //过滤掉空行
    $rows_data = array_values(array_filter($rows_data, function ($row) {
        return !empty($row);
    }));
    if (empty($rows_data)) {
        return;
    }

    //模块外层样式变量
    $style_vars = array(
        '--zps-card-w:' . (int) $instance['card_width'] . 'px',
        '--zps-card-h:' . (int) $instance['card_height'] . 'px',
        '--zps-card-radius:' . (int) $instance['card_radius'] . 'px',
        '--zps-speed:' . max(1, (int) $instance['speed']),
    );
    $style_attr = ' style="' . esc_attr(implode(';', $style_vars)) . '"';

    $wrap_class = 'zib-partners-scroll';
    $wrap_class .= ' zps-style-' . $instance['style_type'];
    $wrap_class .= $instance['pause_hover'] ? ' zps-pause-hover' : '';
    $wrap_class .= $instance['card_bg'] ? ' zps-card-bg' : '';
    $wrap_class .= $instance['end_fade'] ? ' zps-end-fade' : '';

    //开始输出
    Zib_CFSwidget::echo_before($instance, $instance['show_widget_bg'] ? 'zib-widget' : 'mb20');
    echo '<div class="' . esc_attr($wrap_class) . '"' . $style_attr . '>';
    foreach ($rows_data as $row_index => $row_partners) {
        //决定本行滚动方向
        switch ($instance['direction']) {
            case 'left':
                $dir = 'left';
                break;
            case 'right':
                $dir = 'right';
                break;
            default:
                $dir = ($row_index % 2 === 0) ? 'left' : 'right';
        }

        echo '<div class="zps-row zps-dir-' . $dir . '" data-zps-row>';
        echo '<div class="zps-track" data-zps-track>';

        //只输出 1 份种子卡片组，JS 会根据视口宽度按需克隆足够份数，保证无缝循环
        //.flex.shrink0 使 group 在 track 内作为不收缩的弹性子项
        echo '<div class="zps-group flex shrink0">';
        foreach ($row_partners as $partner) {
            echo zib_widget_partners_scroll_card($partner, $instance['style_type'], $instance['go_link']);
        }
        echo '</div>';

        echo '</div>';
        echo '</div>';
    }

    echo '</div>';

    Zib_CFSwidget::echo_after($instance);
}

/**
 * 横向滚动合作伙伴 - 渲染单张卡片
 */
function zib_widget_partners_scroll_card($partner, $style_type = 'card', $go_link = false)
{
    $defaults = array(
        'image' => '',
        'link'  => array('url' => '', 'target' => ''),
        'title' => '',
        'desc'  => '',
    );
    $partner = wp_parse_args((array) $partner, $defaults);

    $title = trim($partner['title']);
    $desc  = trim($partner['desc']);
    $image = !empty($partner['image']) ? $partner['image'] : '';

    //没有图片也没有标题则跳过，避免渲染空卡片
    if (!$image && !$title) {
        return '';
    }

    $alt     = esc_attr($title ? $title : '合作伙伴');
    $img_tag = $image ? '<img class="zps-img" src="' . esc_url($image) . '" alt="' . $alt . '" loading="lazy">' : '';

    //根据样式组合内容（所有 flex/间距/省略/颜色/字重都尽量走主题工具类）
    $inner = '';
    if ($style_type === 'logo') {
        //纯 Logo 模式：只显示图片
        if (!$image) {
            return '';
        }
        $inner .= $img_tag;
    } elseif ($style_type === 'chip') {
        //精简胶囊模式：小图标（仅在有图片时显示） + 名称
        if ($img_tag) {
            $inner .= '<span class="zps-chip-icon flex jc shrink0 mr6">' . $img_tag . '</span>';
        }
        $inner .= '<span class="zps-chip-title text-ellipsis">' . esc_html($title) . '</span>';
    } else {
        //图文卡片模式：左图 + 右文
        if ($img_tag) {
            $inner .= '<div class="zps-card-icon flex jc shrink0 mr10">' . $img_tag . '</div>';
        }
        $inner .= '<div class="zps-card-text flex xx flex1">';
        if ($title) {
            $inner .= '<div class="zps-card-title text-ellipsis font-bold">' . esc_html($title) . '</div>';
        }
        if ($desc) {
            $inner .= '<div class="zps-card-desc text-ellipsis muted-color em09 mt3">' . $desc . '</div>';
        }
        $inner .= '</div>';
    }

    //是否包裹链接
    $url    = !empty($partner['link']['url']) ? esc_url($partner['link']['url']) : 'javascript:void(0)';
    $target = !empty($partner['link']['target']) ? ' target="' . esc_attr($partner['link']['target']) . '" rel="noopener noreferrer"' : '';

    if ($go_link && $url !== 'javascript:void(0)') {
        $url = go_link($url, true);
    }

    //.flex.ac.shrink0 解决 display/对齐/不收缩，留给 CSS 只处理尺寸、圆角、过渡
    $base_class = 'zps-item flex ac shrink0';
    $tag_start  = $url ? '<a class="' . $base_class . '" href="' . $url . '"' . $target . '>' : '<div class="' . $base_class . '">';
    $tag_end    = $url ? '</a>' : '</div>';

    return $tag_start . $inner . $tag_end;
}

//复合信息卡片：图标 + 标题 + 标签 + 描述 + 行动链接
Zib_CFSwidget::create('zib_widget_ui_combo_info_card', array(
    'title'            => '复合信息卡片',
    'zib_title'        => true,
    'zib_affix'        => true,
    'zib_show'         => true,
    'zib_animation_in' => false,
    'description'      => '图标+标题+标签+简介+立即体验按钮的综合信息卡片，适合展示服务、应用、Agent 等内容',
    'fields'           => array(
        array(
            'title'   => '入场动画',
            'id'      => 'obs_animation',
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
        ),
        array(
            'dependency' => array('obs_animation', '!=', ''),
            'title'      => '重复动画',
            'id'         => 'animation_repeat',
            'type'       => 'switcher',
            'class'      => 'compact',
            'default'    => false,
            'desc'       => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        ),
        array(
            'id'       => 'pc_row',
            'title'    => '排列布局',
            'subtitle' => 'PC端单行排列数量',
            'default'  => 4,
            'options'  => array(
                2 => '2个',
                3 => '3个',
                4 => '4个',
                6 => '6个',
            ),
            'type'     => 'button_set',
            'class'    => 'button-mini',
        ),
        array(
            'id'       => 'm_row',
            'title'    => ' ',
            'subtitle' => '移动端单行排列数量',
            'class'    => 'compact button-mini',
            'default'  => 1,
            'options'  => array(
                1 => '1个',
                2 => '2个',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'       => 'pc_gap',
            'title'    => '卡片间距',
            'subtitle' => 'PC端卡片间距',
            'default'  => 10,
            'options'  => array(
                5  => '10px',
                10 => '20px',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'       => 'm_gap',
            'title'    => ' ',
            'subtitle' => '移动端卡片间距',
            'class'    => 'compact button-mini',
            'default'  => 5,
            'options'  => array(
                5  => '10px',
                10 => '20px',
            ),
            'type'     => 'button_set',
        ),
        array(
            'id'      => 'card_radius',
            'title'   => '卡片圆角',
            'default' => 16,
            'max'     => 30,
            'min'     => 0,
            'step'    => 1,
            'unit'    => 'px',
            'type'    => 'slider',
        ),
        array(
            'id'      => 'top_band',
            'label'   => '显示顶部渐变背景',
            'default' => true,
            'type'    => 'switcher',
        ),
        array(
            'id'         => 'top_band_height',
            'dependency' => array('top_band', '==', true),
            'title'      => '色带高度',
            'default'    => 150,
            'max'        => 400,
            'min'        => 100,
            'step'       => 20,
            'unit'       => 'px',
            'type'       => 'slider',
        ),
        array(
            'id'      => 'go_link',
            'label'   => '外链重定向',
            'help'    => '开启后，将链接转为内部go跳转链接',
            'default' => true,
            'type'    => 'switcher',
        ),

        array(
            'id'      => 'cta_text',
            'title'   => '默认按钮文案',
            'default' => '立即体验',
            'desc'    => '每张卡片可单独覆盖',
            'type'    => 'text',
        ),
        //按钮颜色
        array(
            'id'      => 'cta_color',
            'title'   => '默认按钮颜色',
            'class'   => 'skin-color',
            'default' => '',
            'type'    => 'palette',
            'options' => CFS_Module::zib_palette(
                ['' => array('rgba(225, 225, 225, .8)')], ['c']
            ),
        ),

        array(
            'id'                     => 'cards',
            'title'                  => '添加卡片',
            'type'                   => 'group',
            'button_title'           => '添加卡片',
            'accordion_title_auto'   => true,
            'accordion_title_number' => true,
            'default'                => array(),
            'fields'                 => array(

                array(
                    'title'      => '卡片标题',
                    'id'         => 'title',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 1,
                    ),
                ),
                array(
                    'title'   => '图标',
                    'id'      => 'icon_image',
                    'default' => '',
                    'preview' => true,
                    'library' => 'image',
                    'type'    => 'upload',
                    'desc'    => '建议使用背景透明的 PNG/SVG，显示在卡片左上角',
                ),
                array(
                    'id'                     => 'tags',
                    'title'                  => '标签',
                    'type'                   => 'group',
                    'button_title'           => '添加标签',
                    'accordion_title_auto'   => true,
                    'accordion_title_number' => false,
                    'default'                => array(),
                    'fields'                 => array(
                        array(
                            'id'    => 'text',
                            'title' => '标签文字',
                            'type'  => 'text',
                        ),
                        array(
                            'id'      => 'color',
                            'title'   => '标签颜色',
                            'class'   => 'skin-color',
                            'default' => '',
                            'type'    => 'palette',
                            'options' => CFS_Module::zib_palette(
                                ['' => array('rgba(225, 225, 225, 0.4)')]
                            ),
                        ),
                    ),
                ),
                array(
                    'title'      => '简介描述',
                    'id'         => 'desc',
                    'desc'       => '卡片描述文案，超出会自动省略（默认3行）',
                    'type'       => 'textarea',
                    'attributes' => array(
                        'rows' => 3,
                    ),
                ),
                array(
                    'id'           => 'link',
                    'type'         => 'link',
                    'title'        => '跳转链接',
                    'default'      => array(),
                    'add_title'    => '添加链接',
                    'edit_title'   => '编辑链接',
                    'remove_title' => '删除链接',
                ),
                array(
                    'title'       => '按钮文案（可选）',
                    'id'          => 'cta_text',
                    'type'        => 'text',
                    'placeholder' => '留空则使用默认按钮文案',
                ),
                array(
                    'id'      => 'cta_color',
                    'title'   => '按钮颜色（可选）',
                    'class'   => 'skin-color',
                    'default' => '',
                    'type'    => 'palette',
                    'options' => CFS_Module::zib_palette(
                        ['' => array('rgba(225, 225, 225, .8)')], ['c']
                    ),
                ),
                array(
                    'id'      => 'band_color',
                    'title'   => '顶部色带配色',
                    'default' => 'auto',
                    'options' => array(
                        'auto'   => '自动（按序循环）',
                        'blue'   => '蓝紫',
                        'cyan'   => '青蓝',
                        'green'  => '青绿',
                        'yellow' => '橙黄',
                        'pink'   => '粉红',
                        'purple' => '紫罗兰',
                        'gray'   => '中性灰',
                    ),
                    'type'    => 'select',
                ),
            ),
        ),
    ),
));

/**
 * 复合信息卡片 - 渲染函数
 */
function zib_widget_ui_combo_info_card($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['cards'][0]) || !$show_class) {
        return;
    }

    $defaults = array(
        'pc_row'           => 4,
        'm_row'            => 1,
        'card_radius'      => 12,
        'top_band'         => true,
        'top_band_height'  => 70,
        'show_widget_bg'   => false,
        'cta_text'         => '立即体验',
        'cards'            => array(),
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance  = wp_parse_args((array) $instance, $defaults);
    $is_mobile = wp_is_mobile();

    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    //栅格布局
    $col_class = 'col-sm-' . max(1, (int) (12 / $pc_row));
    $col_class .= ' col-xs-' . max(1, (int) (12 / $m_row));

    //组件级 CSS 变量
    $style_vars = array(
        '--zci-radius:' . (int) $instance['card_radius'] . 'px',
        '--zci-band-h:' . (int) $instance['top_band_height'] . 'px',
    );
    $style_attr = ' style="' . esc_attr(implode(';', $style_vars)) . '"';

    $wrap_class = 'zib-combo-info-card row gutters-' . (int) ($is_mobile ? $instance['m_gap'] : $instance['pc_gap']);
    $wrap_class .= $instance['top_band'] ? ' zci-has-band' : '';

    //默认 CTA 文案
    $default_cta       = $instance['cta_text'] !== '' ? $instance['cta_text'] : '立即体验';
    $default_cta_color = $instance['cta_color'] !== '' ? $instance['cta_color'] : '';

    //轮询分配"auto"色带颜色
    $auto_palette = array('blue', 'cyan', 'green', 'yellow', 'pink', 'purple');
    $auto_idx     = 0;

    $html = '';
    foreach ($instance['cards'] as $key => $card) {
        $card_html = zib_widget_combo_info_card_item($card, $default_cta, $default_cta_color, $auto_palette, $auto_idx, $instance['go_link']);
        if ($card_html === '') {
            continue;
        }

        $attr = '';
        if ($instance['obs_animation']) {
            $col_class .= ' obs-animate ani-' . $instance['obs_animation'];
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $attr .= ' style=" --delay: ' . strval($key * 0.15) . 's; "';
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $html .= '<div class="' . esc_attr($col_class) . '" ' . $attr . '>' . $card_html . '</div>';
    }

    if ($html === '') {
        return;
    }

    Zib_CFSwidget::echo_before($instance, 'mb20');
    echo '<div class="' . esc_attr($wrap_class) . '"' . $style_attr . '>';
    echo $html;
    echo '</div>';
    Zib_CFSwidget::echo_after($instance);
}

/**
 * 复合信息卡片 - 渲染单张卡片
 * @param array  $card         单张卡片数据
 * @param string $default_cta  默认按钮文案
 * @param array  $auto_palette 自动色带配色序列
 * @param int    $auto_idx     自动色带当前索引（引用传递，渲染后自增）
 */
function zib_widget_combo_info_card_item($card, $default_cta, $default_cta_color, $auto_palette, &$auto_idx, $go_link = false)
{
    $defaults = array(
        'icon_image' => '',
        'title'      => '',
        'tags'       => array(),
        'desc'       => '',
        'link'       => array('url' => '', 'target' => ''),
        'cta_text'   => '',
        'band_color' => 'auto',
    );
    $card = wp_parse_args((array) $card, $defaults);

    $title = trim($card['title']);
    $desc  = trim($card['desc']);
    if (!$title && !$desc && empty($card['icon_image'])) {
        return '';
    }

    //色带颜色：auto 则按序轮询，否则使用指定色
    $band = $card['band_color'];
    if (!$band || $band === 'auto') {
        $band = $auto_palette[$auto_idx % count($auto_palette)];
        $auto_idx++;
    }

    //图标
    $icon_html = '';
    if (!empty($card['icon_image'])) {
        $alt       = esc_attr($title ? $title : '图标');
        $icon_html = '<div class="zci-icon flex jc shrink0 mb20"><img src="' . esc_url($card['icon_image']) . '" alt="' . $alt . '" loading="lazy"></div>';
    }

    //标签：直接复用主题的 .badg.radius + 调色盘 class（c-blue / c-green...）
    //.badg 已内建 padding/radius/bg/color 变量消费，几乎零额外 CSS
    $tags_html = '';
    if (!empty($card['tags']) && is_array($card['tags'])) {
        $tags_html .= '<div class="zci-tags mb10 text-ellipsis">';
        foreach ($card['tags'] as $tag) {
            $tag_text  = isset($tag['text']) ? trim($tag['text']) : '';
            $tag_color = !empty($tag['color']) ? $tag['color'] : '';
            if ($tag_text === '') {
                continue;
            }
            $tags_html .= '<span class="badg p2-10 radius px12 ' . esc_attr($tag_color) . '">' . $tag_text . '</span>';
        }
        $tags_html .= '</div>';
    }

    //描述：text-ellipsis-3 自带 4.2em 高度 + 3 行省略
    $desc_html = $desc ? '<div class="zci-desc text-ellipsis-3 muted-color">' . $desc . '</div>' : '';

    //CTA 按钮：inflex ac gap6 提供布局，CSS 只负责色彩和 hover 箭头动效
    $cta_text  = !empty($card['cta_text']) ? $card['cta_text'] : $default_cta;
    $cta_color = !empty($card['cta_color']) ? $card['cta_color'] : $default_cta_color;
    $url       = !empty($card['link']['url']) ? $card['link']['url'] : '';
    $target    = !empty($card['link']['target']) ? ' target="' . esc_attr($card['link']['target']) . '" rel="noopener noreferrer"' : '';
    //外链重定向
    if ($url && $go_link) {
        $url = go_link($url, true);
    }

    $cta_html = '';
    if ($cta_text && $url) {
        $cta_html = '<a class="zci-cta inflex ac gap6 ' . esc_attr($cta_color) . '" href="' . esc_url($url) . '"' . $target . '>'
        . '<span>' . esc_html($cta_text) . '</span>'
            . '<i class="fa fa-arrow-right zci-arrow" aria-hidden="true"></i>'
            . '</a>';
    }

    //外层卡片：flex xx 提供列向布局
    $card_tag_start = '<div class="zci-card flex xx zci-band-' . esc_attr($band) . '">';
    $card_tag_end   = '</div>';

    //内容区：flex xx flex1 撑满卡片高度，CTA 用 margin-top:auto 贴底
    $inner = '<div class="zci-body flex xx flex1">';
    $inner .= $icon_html;
    if ($title) {
        $inner .= '<div class="zci-title font-bold text-ellipsis mb10">' . esc_html($title) . '</div>';
    }
    $inner .= $tags_html;
    $inner .= $desc_html;
    $inner .= $cta_html;
    $inner .= '</div>';

    return $card_tag_start . $inner . $card_tag_end;
}

//自定义文字卡片
Zib_CFSwidget::create('zib_widget_ui_text_title', array(
    'title'            => '文字标题',
    'zib_title'        => false,
    'zib_affix'        => true,
    'zib_show'         => true,
    'zib_animation_in' => false,
    'description'      => '显示标题、简介的文字模块，配置丰富，更加适合模块独立标题、简介的场景',
    'fields'           => array(
        array(
            'title'   => '入场动画',
            'id'      => 'obs_animation',
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
        ),
        array(
            'dependency' => array('obs_animation', '!=', ''),
            'title'      => '重复动画',
            'id'         => 'animation_repeat',
            'type'       => 'switcher',
            'class'      => 'compact',
            'default'    => false,
            'desc'       => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        ),

        array(
            'title'      => '标题',
            'id'         => 'title',
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 2,
            ),
            'desc'       => '注意：如果开启了入场动画，则不能使用代码，只能为纯文字！',
        ),

        //标题前图标
        array(
            'title'        => ' ',
            'subtitle'     => '标题前添加图标',
            'id'           => 'title_icon_left',
            'type'         => 'icon',
            'button_title' => '添加图标',
            'default'      => '',
        ),
        array(
            'title'        => ' ',
            'subtitle'     => '标题结尾添加图标',
            'id'           => 'title_icon_right',
            'class'        => 'compact',
            'type'         => 'icon',
            'button_title' => '添加图标',
            'default'      => '',
        ),
        array(
            'id'       => 'title_icon_width',
            'title'    => ' ',
            'subtitle' => '图标宽度(0为字体宽度，只对SVG图标有效)',
            'type'     => 'spinner',
            'class'    => 'compact',
            'default'  => 0,
            'max'      => 500,
            'min'      => 0,
            'step'     => 4,
            'unit'     => 'px',
        ),

        array(
            'title'      => '简介',
            'id'         => 'desc',
            'type'       => 'textarea',
            'attributes' => array(
                'rows' => 2,
            ),
            'desc'       => '注意：如使用html代码，请注意代码规范！',
        ),

        array(
            'title'   => '对齐方式',
            'id'      => 'align',
            'type'    => 'radio',
            'inline'  => true,
            'default' => 'center',
            'options' => array(
                'left'   => '左对齐',
                'center' => '居中',
                'right'  => '右对齐',
            ),
        ),

        array(
            'id'      => 'title_opts',
            'title'   => '标题样式',
            'type'    => 'fieldset',
            'default' => array(),
            'fields'  => array(
                array(
                    'id'      => 'color',
                    'title'   => '颜色',
                    'class'   => 'skin-color',
                    'default' => 'key-color',
                    'type'    => 'palette',
                    'options' => CFS_Module::zib_palette(array('' => array('#4f5359')), array('text', 'cg')),
                ),
                array(
                    'id'      => 'pc',
                    'title'   => 'PC端样式',
                    'type'    => 'fieldset',
                    'default' => array(),
                    'fields'  => zib_widget_ui_get_text_title_opts_fields(),
                ),
                array(
                    'id'      => 'm',
                    'title'   => '移动端样式',
                    'type'    => 'fieldset',
                    'default' => array(),
                    'fields'  => zib_widget_ui_get_text_title_opts_fields(),
                ),
            ),
        ),

        array(
            'id'      => 'desc_opts',
            'title'   => '简介样式',
            'type'    => 'fieldset',
            'default' => array(),
            'fields'  => array(
                array(
                    'id'      => 'color',
                    'title'   => '颜色',
                    'class'   => 'skin-color',
                    'default' => 'muted-color',
                    'type'    => 'palette',
                    'options' => CFS_Module::zib_palette(array('' => array('#4f5359')), array('text', 'cg')),
                ),
                array(
                    'id'      => 'pc',
                    'title'   => 'PC端样式',
                    'type'    => 'fieldset',
                    'default' => array(),
                    'fields'  => zib_widget_ui_get_text_title_opts_fields(true),
                ),
                array(
                    'id'      => 'm',
                    'title'   => '移动端样式',
                    'type'    => 'fieldset',
                    'default' => array(),
                    'fields'  => zib_widget_ui_get_text_title_opts_fields(true),
                ),
            ),
        ),

    ),
));

function zib_widget_ui_text_title($args, $instance)
{

    $show_class = Zib_CFSwidget::show_class($instance);
    if ((empty($instance['title']) && empty($instance['desc'])) || !$show_class) {
        return;
    }

    $defaults = array(
        'title_icon_width' => 0,
        'title'            => '',
        'desc'             => '',
        'title_icon_left'  => '',
        'title_icon_right' => '',
        'title_opts'       => array(),
        'desc_opts'        => array(),
        'align'            => 'center',
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args((array) $instance, $defaults);

    $title_style = '';
    $desc_style  = '';

    $title_class = '';
    $desc_class  = '';

    if (isset($instance['title_opts'])) {
        foreach ($instance['title_opts'] as $key => $value) {
            if (in_array($key, array('color')) || !is_array($value)) {
                continue;
            }

            foreach ($value as $_key => $_value) {
                $_unit = '';
                if (in_array($_key, array('font_size', 'margin_top', 'margin_bottom'))) {
                    $_unit = 'px';
                }

                $title_style .= '--' . $key . '-' . $_key . ':' . $_value . $_unit . ';';
            }
        }
    }
    if (isset($instance['desc_opts'])) {
        foreach ($instance['desc_opts'] as $key => $value) {
            if (in_array($key, array('color')) || !is_array($value)) {
                continue;
            }
            foreach ($value as $_key => $_value) {
                $_unit = '';
                if (in_array($_key, array('font_size', 'margin_top', 'margin_bottom'))) {
                    $_unit = 'px';
                }
                $desc_style .= '--' . $key . '-' . $_key . ':' . $_value . $_unit . ';';
            }
        }
    }

    $title = $instance['title'] ?? '';
    if ($title) {

        $title_icon_left  = $instance['title_icon_left'] ? zib_get_cfs_icon($instance['title_icon_left']) : '';
        $title_icon_right = $instance['title_icon_right'] ? zib_get_cfs_icon($instance['title_icon_right']) : '';
        $title_icon_width = $instance['title_icon_width'] ? 'style="--icon-width:' . $instance['title_icon_width'] . 'px;"' : '';

        $title_icon_left  = '<span class="text-title-icon mr6" ' . $title_icon_width . '>' . $title_icon_left . '</span>';
        $title_icon_right = '<span class="text-title-icon ml6" ' . $title_icon_width . '>' . $title_icon_right . '</span>';
    }

    $a_title = '';
    if ($instance['obs_animation'] && $title) {
        //如果开启了动画，将标题的每个文字拆分成span，每个span添加动画效果
        $t_class = ' obs-animate ani-' . $instance['obs_animation'];
        $t_attr  = '';
        $t_attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
        if ($instance['animation_repeat']) {
            $t_attr .= ' data-animation-repeat="true"';
        }

        if (!empty($instance['title_opts']['color']) && strpos($instance['title_opts']['color'], 'cg-') !== false) {
            $a_title                         = '<span class="' . $instance['title_opts']['color'] . ' ' . $t_class . '" ' . $t_attr . '">' . $title_icon_left . $title . $title_icon_right . '</span>';
            $instance['title_opts']['color'] = '';
        } else {
            $chars = zib_str_split($title);
            if ($title_icon_left) {
                $chars = array_merge(array($title_icon_left), $chars);
            }
            if ($title_icon_right) {
                $chars = array_merge($chars, array($title_icon_right));
            }

            foreach ($chars as $i => $char) {
                $a_title .= '<span class="' . $t_class . '" ' . $t_attr . ' data-delay="' . ($i * 0.1) . '" style=" --delay: ' . strval($i * 0.05) . 's; ">' . $char . '</span>';
            }
        }

        $title = $a_title;
    }

    if (!$instance['obs_animation']) {
        $title = $title_icon_left . $title . $title_icon_right;
    }

    $desc = $instance['desc'] ?? '';
    if ($instance['obs_animation'] && $desc) {
        $d_class = ' obs-animate ani-' . $instance['obs_animation'];
        $d_attr  = '';
        $d_attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
        if ($instance['animation_repeat']) {
            $d_attr .= ' data-animation-repeat="true"';
        }

        if ($title && strpos($a_title, '--delay:') !== false) {
            $d_attr .= ' style=" --delay: .6s; "';
        }

        if (!empty($instance['desc_opts']['color']) && strpos($instance['desc_opts']['color'], 'cg-') !== false) {
            $d_class .= ' ' . $instance['desc_opts']['color'];
            $instance['desc_opts']['color'] = '';
        }
        $desc = '<span class="' . $d_class . '" ' . $d_attr . '>' . $desc . '</span>';
    }

    $title = $title ? '<div class="text-title ' . $title_class . '" style="' . esc_attr($title_style) . '"><div class="text-title-inner inline-block ' . ($instance['title_opts']['color'] ?? '') . '">' . $title . '</div></div>' : '';
    $desc  = $desc ? '<div class="text-desc ' . $desc_class . '" style="' . esc_attr($desc_style) . '"><div class="text-desc-inner inline-block ' . ($instance['desc_opts']['color'] ?? '') . '">' . $desc . '</div></div>' : '';

    echo '<div class="widget-text-title mb20 text-' . $instance['align'] . '">' . $title . $desc . '</div>';
}

function zib_widget_ui_get_text_title_opts_fields($is_desc = false)
{

    $fields = array(
        //字体大小
        array(
            'id'       => 'font_size',
            'title'    => ' ',
            'subtitle' => '字体大小',
            'type'     => 'spinner',
            'default'  => 24,
            'max'      => 60,
            'min'      => 12,
            'step'     => 2,
            'unit'     => 'px',
        ),
        //粗体
        array(
            'id'       => 'font_weight',
            'title'    => ' ',
            'subtitle' => '字体粗细',
            'type'     => 'radio',
            'inline'   => true,
            'default'  => 'normal',
            'options'  => array(
                'normal'  => '正常',
                'bold'    => '粗体',
                'lighter' => '细体',
            ),
        ),
    );
    if (!$is_desc) {
        $fields[] = array(
            'id'       => 'margin_top',
            'title'    => ' ',
            'subtitle' => '上边距',
            'type'     => 'spinner',
            'default'  => 0,
            'max'      => 60,
            'min'      => 12,
            'step'     => 2,
            'unit'     => 'px',
        );
    }

    $fields[] = array(
        'id'       => 'margin_bottom',
        'title'    => ' ',
        'subtitle' => '下边距',
        'type'     => 'spinner',
        'default'  => 20,
        'max'      => 60,
        'min'      => 12,
        'step'     => 2,
        'unit'     => 'px',
    );

    return $fields;
}

//按钮小工具
Zib_CFSwidget::create('zib_widget_ui_buttons', array(
    'title'            => '按钮组',
    'zib_title'        => false,
    'zib_affix'        => true,
    'zib_show'         => true,
    'zib_animation_in' => false,
    'description'      => '显示单个或多个跳转按钮，支持多种样式显示',
    'fields'           => array(
        array(
            'title'   => '入场动画',
            'id'      => 'obs_animation',
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
        ),
        array(
            'dependency' => array('obs_animation', '!=', ''),
            'title'      => '重复动画',
            'id'         => 'animation_repeat',
            'type'       => 'switcher',
            'class'      => 'compact',
            'default'    => false,
            'desc'       => '开启后页面再次滚动到此模块时，会重复触发此动画(有一定的性能消耗，部分浏览器可能会出现卡顿闪烁现象)',
        ),
        array(
            'id'       => 'pc_width',
            'title'    => '按钮宽度',
            'subtitle' => 'PC端宽度（0为自动）',
            'type'     => 'spinner',
            'default'  => 0,
            'max'      => 500,
            'min'      => 0,
            'step'     => 4,
            'unit'     => 'px',
        ),
        array(
            'id'       => 'm_width',
            'title'    => ' ',
            'subtitle' => '移动端宽度（0为自动）',
            'type'     => 'spinner',
            'class'    => 'compact',
            'default'  => 0,
            'max'      => 500,
            'min'      => 0,
            'step'     => 4,
            'unit'     => 'px',
        ),
        array(
            'id'       => 'pc_padding',
            'title'    => '按钮尺寸',
            'subtitle' => 'PC端按钮尺寸',
            'type'     => 'radio',
            'inline'   => true,
            'default'  => 'large',
            'options'  => array(
                'small'  => '小尺寸',
                'medium' => '中尺寸',
                'large'  => '大尺寸',
            ),
        ),
        array(
            'id'       => 'm_padding',
            'title'    => ' ',
            'subtitle' => '移动端按钮尺寸',
            'type'     => 'radio',
            'inline'   => true,
            'class'    => 'compact',
            'default'  => 'medium',
            'options'  => array(
                'small'  => '小尺寸',
                'medium' => '中尺寸',
                'large'  => '大尺寸',
            ),
        ),
        array(
            'id'       => 'pc_gap',
            'title'    => '按钮间距',
            'subtitle' => 'PC端按钮间距',
            'type'     => 'spinner',
            'default'  => 12,
            'max'      => 60,
            'min'      => 0,
            'step'     => 4,
            'unit'     => 'px',
        ),
        array(
            'id'       => 'm_gap',
            'title'    => ' ',
            'subtitle' => '移动端按钮间距',
            'type'     => 'spinner',
            'class'    => 'compact',
            'default'  => 6,
            'max'      => 60,
            'min'      => 0,
            'step'     => 4,
            'unit'     => 'px',
        ),

        array(
            'title'   => '对齐方式',
            'id'      => 'align',
            'type'    => 'radio',
            'inline'  => true,
            'default' => 'center',
            'options' => array(
                'left'   => '左对齐',
                'center' => '居中',
                'right'  => '右对齐',
            ),
        ),

        array(
            'title'   => '两端圆角',
            'id'      => 'radius',
            'type'    => 'switcher',
            'default' => false,
        ),
        array(
            'title'   => '空心样式',
            'id'      => 'hollow',
            'type'    => 'switcher',
            'default' => false,
        ),
        array(
            'title'   => '外链重定向',
            'id'      => 'go_link',
            'type'    => 'switcher',
            'default' => true,
            'help'    => '开启后，将链接转为内部go跳转链接',
        ),

        array(
            'id'                     => 'items',
            'title'                  => '添加按钮',
            'type'                   => 'group',
            'button_title'           => '添加按钮',
            'accordion_title_auto'   => false,
            'accordion_title_number' => true,
            'default'                => array(),
            'fields'                 => array(
                array(
                    'title' => '按钮文字',
                    'id'    => 'text',
                    'type'  => 'text',
                ),
                array(
                    'title'        => ' ',
                    'subtitle'     => '按钮前图标',
                    'class'        => 'compact',
                    'id'           => 'icon_left',
                    'type'         => 'icon',
                    'button_title' => '选择图标',
                    'default'      => '',
                ),
                array(
                    'title'        => ' ',
                    'class'        => 'compact',
                    'subtitle'     => '按钮后图标',
                    'id'           => 'icon_right',
                    'type'         => 'icon',
                    'button_title' => '选择图标',
                    'default'      => '',
                ),
                array(
                    'title'        => '按钮链接',
                    'id'           => 'link',
                    'type'         => 'link',
                    'add_title'    => '添加链接',
                    'edit_title'   => '编辑链接',
                    'remove_title' => '删除链接',
                ),
                array(
                    'id'      => 'color',
                    'title'   => '按钮颜色',
                    'class'   => 'skin-color',
                    'default' => '',
                    'type'    => 'palette',
                    'desc'    => '注意：开启空心样式后，不能选择渐变色',
                    'options' => CFS_Module::zib_palette(
                        ['' => array('rgba(225, 225, 225, 0.4)')]
                    ),
                ),
            ),
        ),
    ),
));

function zib_widget_ui_buttons($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class || empty($instance['items'][0])) {
        return;
    }

    $default = [
        'obs_animation'    => '',
        'animation_repeat' => false,
        'pc_width'         => 0,
        'm_width'          => 0,
        'pc_padding'       => 'large',
        'm_padding'        => 'medium',
        'pc_gap'           => 12,
        'm_gap'            => 6,
        'align'            => 'center',
        'radius'           => false,
        'hollow'           => false,
        'go_link'          => true,
        'items'            => [],
    ];
    $instance = wp_parse_args((array) $instance, $default);

    $but_class = 'but';
    if ($instance['radius']) {
        $but_class .= ' radius';
    }
    if ($instance['hollow']) {
        $but_class .= ' hollow';
    }

    $buts = '';
    foreach ($instance['items'] as $key => $item) {
        if (!$item['text']) {
            continue;
        }
        $but_item_class = $but_class;
        $icon_left      = $item['icon_left'] ? zib_get_cfs_icon($item['icon_left']) : '';
        $icon_right     = $item['icon_right'] ? '<span class="ml6">' . zib_get_cfs_icon($item['icon_right']) . '</span>' : '';
        $but_item_class .= ' ' . $item['color'];
        $url    = !empty($item['link']['url']) ? $item['link']['url'] : 'javascript:void(0)';
        $target = !empty($item['link']['target']) ? ' target="' . esc_attr($item['link']['target']) . '" rel="noopener noreferrer"' : '';
        if ($url && $instance['go_link']) {
            $url = go_link($url, true);
        }

        $attr   = '';
        $_class = '';
        if ($instance['obs_animation']) {
            $_class .= ' obs-animate ani-' . $instance['obs_animation'];
            $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
            $attr .= ' style=" --delay: ' . strval($key * 0.15) . 's; "';
            if ($instance['animation_repeat']) {
                $attr .= ' data-animation-repeat="true"';
            }
        }

        $buts .= '<div class="' . $_class . '" ' . $attr . '><a class="' . $but_item_class . '" href="' . $url . '"' . $target . '>' . $icon_left . $item['text'] . $icon_right . '</a></div>';
    }

    if (!$buts) {
        return;
    }

    $style_attr = '';
    if ($instance['pc_width']) {
        $style_attr .= '--pc-width:' . $instance['pc_width'] . 'px;';
    }
    if ($instance['m_width']) {
        $style_attr .= '--m-width:' . $instance['m_width'] . 'px;';
    }
    if ($instance['pc_gap']) {
        $style_attr .= '--pc-gap:' . $instance['pc_gap'] . 'px;';
    }
    if ($instance['m_gap']) {
        $style_attr .= '--m-gap:' . $instance['m_gap'] . 'px;';
    }

    Zib_CFSwidget::echo_before($instance, 'widget-buttons mb20 pc-size-' . $instance['pc_padding'] . ' m-size-' . $instance['m_padding']);
    echo '<div class="widget-buttons-wrap flex hh align-' . $instance['align'] . '" style="' . $style_attr . '">' . $buts . '</div>';
    Zib_CFSwidget::echo_after($instance);
}

//布局间隔
Zib_CFSwidget::create('zib_widget_ui_layout_gap', array(
    'title'            => '布局-额外间距',
    'zib_title'        => false,
    'zib_affix'        => false,
    'zib_show'         => false,
    'zib_animation_in' => false,
    'description'      => '用于增加模块上下之间的间距',
    'fields'           => array(
        array(
            'id'      => 'pc_gap',
            'title'   => 'PC端间隔',
            'type'    => 'spinner',
            'default' => 10,
            'max'     => 60,
            'min'     => 0,
            'step'    => 4,
            'unit'    => 'px',
        ),
        array(
            'id'      => 'm_gap',
            'title'   => '移动端间隔',
            'type'    => 'spinner',
            'default' => 5,
            'max'     => 60,
            'min'     => 0,
            'step'    => 4,
            'unit'    => 'px',
        ),
    ),
));

function zib_widget_ui_layout_gap($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    $default = [
        'pc_gap' => 10,
        'm_gap'  => 5,
    ];
    $instance = wp_parse_args((array) $instance, $default);

    $style_attr = '';
    if ($instance['pc_gap']) {
        $style_attr .= '--pc-gap:' . $instance['pc_gap'] . 'px;';
    }
    if ($instance['m_gap']) {
        $style_attr .= '--m-gap:' . $instance['m_gap'] . 'px;';
    }

    echo '<div class="widget-layout-gap" style="' . $style_attr . '"></div>';
}