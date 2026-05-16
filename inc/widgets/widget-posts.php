<?php

add_action('widgets_init', 'widget_register_posts');
function widget_register_posts()
{
    register_widget('widget_ui_mian_posts');
    register_widget('widget_ui_oneline_posts');
    register_widget('widget_ui_mini_posts');
    register_widget('widget_ui_mini_tab_posts');
    register_widget('widget_ui_main_tab_posts');
}
class widget_ui_main_tab_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_main_tab_posts',
            'w_name'      => _name('多栏目文章(旧版即将删除)'),
            'classname'   => '',
            'description' => '旧版模块，已废弃，请使用多栏目文章(新)模块',
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
            'show_thumb'  => '',
            'show_meta'   => '',
            'show_number' => '',
            'type'        => 'auto',
            'limit_day'   => '',
            'limit'       => 6,
            'tabs'        => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => '热门文章',
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        echo '<div class="theme-box">';
        echo '<div class="index-tab">';
        echo '<ul class="list-inline scroll-x mini-scrollbar">';
        $_i  = 0;
        $nav = '';
        $con = '';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $nav_class = $_i == 0 ? 'active' : '';
                $id        = $this->get_field_id('tab_') . $_i;
                echo '<li class="' . $nav_class . '" ><a data-toggle="tab" href="#' . $id . '">' . $tabs['title'] . '</a></li>';
                $_i++;
            }
        }
        echo '</ul>';
        echo '</div>';
        $list_args = array(
            'type' => $instance['type'],
        );
        $_i2 = 0;

        echo '<div class="tab-content">';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $args = array(
                    'post_status'         => 'publish',
                    'cat'                 => $tabs['cat'],
                    'order'               => 'DESC',
                    'showposts'           => $instance['limit'],
                    'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
                    'ignore_sticky_posts' => 1,
                );
                $orderby = $tabs['orderby'];
                if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
                    $args['orderby'] = $orderby;
                } else {
                    $args['orderby']    = 'meta_value_num';
                    $args['meta_query'] = array(
                        array(
                            'key'   => $orderby,
                            'order' => 'DESC',
                        ),
                    );
                }
                if ($tabs['topics']) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'topics',
                            'terms'    => preg_split("/,|，|\s|\n/", $tabs['topics']),
                        ),
                    );
                }
                if ($instance['limit_day'] > 0) {
                    $current_time       = current_time('Y-m-d H:i:s');
                    $args['date_query'] = array(
                        array(
                            'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                            'before'    => $current_time,
                            'inclusive' => true,
                        ),
                    );
                }
                $con_class = $_i2 == 0 ? ' active in' : '';
                $id        = $this->get_field_id('tab_') . $_i2;
                echo '<div class="tab-pane fade' . $con_class . '" id="' . $id . '">';

                $the_query = new WP_Query($args);
                if ($instance['type'] == 'oneline_card') {
                    $list_args['type'] = 'card';
                    echo '<div class="swiper-container swiper-scroll" data-slideClass="posts-item">';
                    echo '<div class="posts-row swiper-wrapper">';
                    zib_posts_list($list_args, $the_query);
                    echo '</div>';
                    echo '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
                    echo '</div>';
                } else {
                    echo '<div>';
                    zib_posts_list($list_args, $the_query);
                    echo '</div>';
                }
                echo '</div>';
                $_i2++;
            }
        }
        echo '</div>';
        echo '</div>';
    }
    public function form($instance)
    {
        $defaults = array(
            'type'      => 'auto',
            'limit'     => 6,
            'limit_day' => '',
            'tabs'      => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => '热门文章',
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $img_html = '';
        $img_i    = 0;
        foreach ($instance['tabs'] as $category) {
            $_html_a = '<label>栏目' . ($img_i + 1) . '-标题（必填）：<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].title" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][title]" value="' . $instance['tabs'][$img_i]['title'] . '" /></label>';

            $_html_b = '<label>栏目' . ($img_i + 1) . '-分类限制：<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].cat" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][cat]" value="' . $instance['tabs'][$img_i]['cat'] . '" /></label>';
            $_html_b .= '<label>栏目' . ($img_i + 1) . '-专题：<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].topics" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][topics]" value="' . $instance['tabs'][$img_i]['topics'] . '" /></label>';

            $_html_c = '<label>栏目' . ($img_i + 1) . '-排序方式：
			<select style="width:100%;" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][orderby]">
			<option value="comment_count" ' . selected('comment_count', $instance['tabs'][$img_i]['orderby'], false) . '>评论数</option>
			<option value="views" ' . selected('views', $instance['tabs'][$img_i]['orderby'], false) . '>浏览量</option>
			<option value="like" ' . selected('like', $instance['tabs'][$img_i]['orderby'], false) . '>点赞数</option>
			<option value="favorite" ' . selected('favorite', $instance['tabs'][$img_i]['orderby'], false) . '>收藏数</option>
			<option value="date" ' . selected('date', $instance['tabs'][$img_i]['orderby'], false) . '>发布时间</option>
			<option value="modified" ' . selected('modified', $instance['tabs'][$img_i]['orderby'], false) . '>更新时间</option>
			<option value="rand" ' . selected('rand', $instance['tabs'][$img_i]['orderby'], false) . '>随机排序</option>
			</select></label>';

            $_tt  = '<div class="panel"><h4 class="panel-title">栏目' . ($img_i + 1) . '：' . $instance['tabs'][$img_i]['title'] . '</h4><div class="panel-hide panel-conter">';
            $_tt2 = '</div></div>';

            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_html_c . $_tt2 . '</div>';
            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . $this->get_field_name('tabs') . '" data-count="' . $img_i . '" class="button add_button add_lists_button">添加栏目</button>';
        $add_b .= '<button type="button" data-name="' . $this->get_field_name('tabs') . '" data-count="' . $img_i . '" class="button rem_lists_button">删除栏目</button>';
        $img_html .= $add_b;
        ?> <p>
			<div style="width:100%;font-size: 12px;color: #f63e98;">当前模块已在V8.0版本中弃用，请使用<code>多栏目文章(新)</code>模块，功能更强大，性能更好</div><br>
			<?php zib_cat_help()?>
			<?php zib_topics_help();
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        ?>
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo $instance['limit'];
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				限制时间（最近X天）：
				<input style="width:100%;" name="<?php echo $this->get_field_name('limit_day') ?>" type="number" value="<?php echo $instance['limit_day'] ?>" size="24" />
			</label>
		</p>

		<p>
			<label>
				列表显示模式：
				<select style="width:100%;" id="<?php echo $this->get_field_id('type');
        ?>" name="<?php echo $this->get_field_name('type');
        ?>">
					<option value="auto" <?php selected('auto', $instance['type']);
        ?>>默认（自动跟随主题设置)</option>
					<option value="card" <?php selected('card', $instance['type']);
        ?>>卡片模式</option>
					<option value="oneline_card" <?php selected('oneline_card', $instance['type']);
        ?>>单行滚动卡片模式</option>
					<option value="no_thumb" <?php selected('no_thumb', $instance['type']);
        ?>>无缩略图列表</option>
					<option value="mult_thumb" <?php selected('mult_thumb', $instance['type']);
        ?>>多图模式</option>
				</select>
			</label>
		</p>
		<?php echo $img_html; ?>
	<?php
}
}

//////---多栏目文章mini---////////
class widget_ui_mini_tab_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_mini_tab_posts',
            'w_name'      => _name('多栏目文章mini(旧版即将删除)'),
            'classname'   => '',
            'description' => '旧版模块，已废弃，请使用多栏目文章(新)模块',
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
            'title'        => '',
            'in_affix'     => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'show_thumb'   => '',
            'show_meta'    => '',
            'show_number'  => '',
            'limit'        => 6,
            'limit_day'    => '',
            'tabs'         => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => '热门文章',
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $class    = '';
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop' . $class . '"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        echo $title;
        echo '<div class="box-body posts-mini-lists zib-widget">';
        echo '<ul class="list-inline scroll-x mini-scrollbar tab-nav-theme">';
        $_i      = 0;
        $id_base = 'post_mini_';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $nav_class = $_i == 0 ? 'active' : '';
                $id        = $id_base . $_i;
                echo '<li class="' . $nav_class . '" ><a class="post-tab-toggle" data-toggle="tab" href="javascript:;" tab-id="' . $id . '">' . $tabs['title'] . '</a></li>';
                $_i++;
            }
        }
        echo '</ul>';
        $list_args = array(
            'show_thumb'  => $instance['show_thumb'] ? true : false,
            'show_meta'   => $instance['show_meta'] ? true : false,
            'show_number' => $instance['show_number'] ? true : false,
        );
        $_i2 = 0;

        echo '<div class="tab-content">';
        foreach ($instance['tabs'] as $tabs) {
            if ($tabs['title']) {
                $args = array(
                    'post_status'         => 'publish',
                    'cat'                 => $tabs['cat'],
                    'order'               => 'DESC',
                    'showposts'           => $instance['limit'],
                    'ignore_sticky_posts' => 1,
                    'no_found_rows'       => true, //不查询分页需要的总数量
                );
                $orderby = $tabs['orderby'];
                if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
                    $args['orderby'] = $orderby;
                } else {
                    $args['orderby']    = 'meta_value_num';
                    $args['meta_query'] = array(
                        array(
                            'key'   => $orderby,
                            'order' => 'DESC',
                        ),
                    );
                }
                if ($tabs['topics']) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'topics',
                            'terms'    => preg_split("/,|，|\s|\n/", $tabs['topics']),
                        ),
                    );
                }
                if ($instance['limit_day'] > 0) {
                    $current_time       = current_time('Y-m-d H:i:s');
                    $args['date_query'] = array(
                        array(
                            'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                            'before'    => $current_time,
                            'inclusive' => true,
                        ),
                    );
                }
                $con_class = $_i2 == 0 ? ' active in' : '';
                $id        = $id_base . $_i2;
                echo '<div class="tab-pane fade' . $con_class . '" tab-id="' . $id . '">';
                $the_query = new WP_Query($args);
                zib_posts_mini_list($list_args, $the_query);
                echo '</div>';
                $_i2++;
            }
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'in_affix'     => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'show_thumb'   => '',
            'show_meta'    => '',
            'show_number'  => '',
            'limit'        => 6,
            'limit_day'    => '',
            'tabs'         => array(),
        );
        $defaults['tabs'][] = array(
            'title'   => '热门文章',
            'cat'     => '',
            'topics'  => '',
            'orderby' => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $img_html = '';
        $img_i    = 0;
        foreach ($instance['tabs'] as $category) {
            $_html_a = '<label>栏目' . ($img_i + 1) . '-标题（必填）：<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].title" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][title]" value="' . $instance['tabs'][$img_i]['title'] . '" /></label>';

            $_html_b = '<label>栏目' . ($img_i + 1) . '-分类限制：<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].cat" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][cat]" value="' . $instance['tabs'][$img_i]['cat'] . '" /></label>';
            $_html_b .= '<label>栏目' . ($img_i + 1) . '-专题：<input style="width:100%;" type="text" id="' . $this->get_field_id('tabs') . '[' . $img_i . '].topics" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][topics]" value="' . $instance['tabs'][$img_i]['topics'] . '" /></label>';

            $_html_c = '<label>栏目' . ($img_i + 1) . '-排序方式：
			<select style="width:100%;" name="' . $this->get_field_name('tabs') . '[' . $img_i . '][orderby]">
			<option value="comment_count" ' . selected('comment_count', $instance['tabs'][$img_i]['orderby'], false) . '>评论数</option>
			<option value="views" ' . selected('views', $instance['tabs'][$img_i]['orderby'], false) . '>浏览量</option>
			<option value="like" ' . selected('like', $instance['tabs'][$img_i]['orderby'], false) . '>点赞数</option>
			<option value="favorite" ' . selected('favorite', $instance['tabs'][$img_i]['orderby'], false) . '>收藏数</option>
			<option value="date" ' . selected('date', $instance['tabs'][$img_i]['orderby'], false) . '>发布时间</option>
			<option value="modified" ' . selected('modified', $instance['tabs'][$img_i]['orderby'], false) . '>更新时间</option>
			<option value="rand" ' . selected('rand', $instance['tabs'][$img_i]['orderby'], false) . '>随机排序</option>
		</select></label>';
            $_tt  = '<div class="panel"><h4 class="panel-title">栏目' . ($img_i + 1) . '：' . $instance['tabs'][$img_i]['title'] . '</h4><div class="panel-hide panel-conter">';
            $_tt2 = '</div></div>';

            $img_html .= '<div class="widget_ui_slider_g">' . $_tt . $_html_a . $_html_b . $_html_c . $_tt2 . '</div>';

            $img_i++;
        }

        $add_b = '<button type="button" data-name="' . $this->get_field_name('tabs') . '" data-count="' . $img_i . '" class="button add_button add_lists_button">添加栏目</button>';
        $add_b .= '<button type="button" data-name="' . $this->get_field_name('tabs') . '" data-count="' . $img_i . '" class="button rem_lists_button">删除栏目</button>';
        $img_html .= $add_b;
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

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);

        ?> <p>
			<div style="width:100%;font-size: 12px;color: #f63e98;">当前模块已在V8.0版本中弃用，请使用<code>多栏目文章(新)</code>模块，功能更强大，性能更好</div>
			<?php zib_cat_help()?>
			<?php zib_topics_help()?>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_thumb'], 'on'); ?> id="<?php echo $this->get_field_id('show_thumb'); ?>" name="<?php echo $this->get_field_name('show_thumb'); ?>">显示缩略图
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_number'], 'on'); ?> id="<?php echo $this->get_field_id('show_number'); ?>" name="<?php echo $this->get_field_name('show_number'); ?>">显示编号
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_meta'], 'on'); ?> id="<?php echo $this->get_field_id('show_meta'); ?>" name="<?php echo $this->get_field_name('show_meta'); ?>">显示作者、时间、点赞等信息
			</label>
		</p>

		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo $instance['limit'];
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				限制时间（最近X天）：
				<input style="width:100%;" name="<?php echo $this->get_field_name('limit_day') ?>" type="number" value="<?php echo $instance['limit_day'] ?>" size="24" />
			</label>
		</p>

		<?php echo $img_html; ?>
	<?php
}
}

class widget_ui_mini_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_mini_posts',
            'w_name'      => _name('文章mini (旧版即将删除)'),
            'classname'   => '',
            'description' => '旧版模块，已废弃，请使用文章列表(新)模块',
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
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'limit'        => 6,
            'limit_day'    => '',
            'cat'          => '',
            'topics'       => '',
            'orderby'      => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $orderby  = $instance['orderby'];

        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $class    = '';
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop' . $class . '"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        echo $title;
        //    echo '<pre>'.json_encode($instance).'</pre>';

        $args = array(
            'post_status'         => 'publish',
            'cat'                 => str_replace('，', ',', $instance['cat']),
            'order'               => 'DESC',
            'showposts'           => $instance['limit'],
            'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
            'ignore_sticky_posts' => 1,
        );

        if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
            $args['orderby'] = $orderby;
        } else {
            $args['orderby']    = 'meta_value_num';
            $args['meta_query'] = array(
                array(
                    'key'   => $orderby,
                    'order' => 'DESC',
                ),
            );
        }
        if ($instance['topics']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topics',
                    'terms'    => preg_split("/,|，|\s|\n/", $instance['topics']),
                ),
            );
        }
        if ($instance['limit_day'] > 0) {
            $current_time       = current_time('Y-m-d H:i:s');
            $args['date_query'] = array(
                array(
                    'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                    'before'    => $current_time,
                    'inclusive' => true,
                ),
            );
        }
        $list_args = array(
            'show_thumb'  => isset($instance['show_thumb']) ? true : false,
            'show_meta'   => isset($instance['show_meta']) ? true : false,
            'show_number' => isset($instance['show_number']) ? true : false,
        );
        echo '<div class="box-body posts-mini-lists zib-widget">';
        $the_query = new WP_Query($args);
        zib_posts_mini_list($list_args, $the_query);
        echo '</div>';
        echo '</div>';
    }
    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'show_thumb'   => '',
            'show_meta'    => '',
            'show_number'  => '',
            'limit'        => 6, 'limit_day' => '',
            'topics'       => '',
            'cat'          => '',
            'orderby'      => 'views',
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
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>
		<p>
        <div style="width:100%;font-size: 12px;color: #f63e98;">当前模块已在V8.0版本中弃用，请使用<code>文章列表(新)</code>模块，功能更强大，性能更好</div>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_thumb'], 'on'); ?> id="<?php echo $this->get_field_id('show_thumb'); ?>" name="<?php echo $this->get_field_name('show_thumb'); ?>">显示缩略图
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_number'], 'on'); ?> id="<?php echo $this->get_field_id('show_number'); ?>" name="<?php echo $this->get_field_name('show_number'); ?>">显示编号
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['show_meta'], 'on'); ?> id="<?php echo $this->get_field_id('show_meta'); ?>" name="<?php echo $this->get_field_name('show_meta'); ?>">显示作者、时间、点赞等信息
			</label>
		</p>
		<p>
			<?php zib_cat_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('cat');
        ?>" name="<?php echo $this->get_field_name('cat');
        ?>" type="text" value="<?php echo str_replace('，', ',', $instance['cat']);
        ?>" size="24" />
		</p>
		<p>
			<?php zib_topics_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('topics');
        ?>" name="<?php echo $this->get_field_name('topics');
        ?>" type="text" value="<?php echo $instance['topics'];
        ?>" size="24" />
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo $instance['limit'];
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				限制时间（最近X天）：
				<input style="width:100%;" name="<?php echo $this->get_field_name('limit_day') ?>" type="number" value="<?php echo $instance['limit_day'] ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				排序：
				<select style="width:100%;" id="<?php echo $this->get_field_id('orderby');
        ?>" name="<?php echo $this->get_field_name('orderby');
        ?>">
					<option value="comment_count" <?php selected('comment_count', $instance['orderby']);
        ?>>评论数</option>
					<option value="views" <?php selected('views', $instance['orderby']);
        ?>>浏览量</option>
					<option value="like" <?php selected('like', $instance['orderby']);
        ?>>点赞数</option>
					<option value="favorite" <?php selected('favorite', $instance['orderby']);
        ?>>收藏数</option>
                			<option value="comment_count" <?php selected('sales_volume', $instance['orderby']);
        ?>>销售数量</option>
					<option value="date" <?php selected('date', $instance['orderby']);
        ?>>发布时间</option>
					<option value="modified" <?php selected('modified', $instance['orderby']);
        ?>>更新时间</option>
					<option value="rand" <?php selected('rand', $instance['orderby']);
        ?>>随机排序</option>
				</select>
			</label>
		</p>
	<?php
}
}

class widget_ui_mian_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_mian_posts',
            'w_name'      => _name('文章列表 (旧版即将删除)'),
            'classname'   => '',
            'description' => '旧版模块，已废弃，请使用文章列表(新)模块',
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
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'type'         => 'auto',
            'limit'        => 6, 'limit_day' => '',
            'cat'          => '',
            'topics'       => '',
            'orderby'      => 'views',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $orderby  = $instance['orderby'];

        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title = $instance['title'];
        $class = ' nobottom';
        if ($instance['type'] == 'card') {
            $class = '';
        }
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop clearfix' . $class . '"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        echo '<div class="theme-box">';
        echo $title;
        //    echo '<pre>'.json_encode($instance).'</pre>';

        $args = array(
            'post_status'         => 'publish',
            'cat'                 => str_replace('，', ',', $instance['cat']),
            'order'               => 'DESC',
            'showposts'           => $instance['limit'],
            'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
            'ignore_sticky_posts' => 1,
        );

        if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
            $args['orderby'] = $orderby;
        } else {
            $args['orderby']    = 'meta_value_num';
            $args['meta_query'] = array(
                array(
                    'key'   => $orderby,
                    'order' => 'DESC',
                ),
            );
        }
        if ($instance['topics']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topics',
                    'terms'    => preg_split("/,|，|\s|\n/", $instance['topics']),
                ),
            );
        }
        if ($instance['limit_day'] > 0) {
            $current_time = current_time('Y-m-d H:i:s');

            $args['date_query'] = array(
                array(
                    'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                    'before'    => $current_time,
                    'inclusive' => true,
                ),
            );
        }

        $list_args = array(
            'type' => $instance['type'],
        );

        $the_query = new WP_Query($args);
        echo '<div class="posts-row">';
        zib_posts_list($list_args, $the_query);
        echo '</div>';
        echo '</div>';
    }
    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'limit'        => 6, 'limit_day' => '',
            'type'         => 'auto',
            'topics'       => '',
            'cat'          => '',
            'orderby'      => 'views',
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
        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>
		<p>
        <div style="width:100%;font-size: 12px;color: #f63e98;">当前模块已在V8.0版本中弃用，请使用<code>文章列表(新)</code>模块，功能更强大，性能更好</div>
		</p>

		<p>

			<?php zib_cat_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('cat');
        ?>" name="<?php echo $this->get_field_name('cat');
        ?>" type="text" value="<?php echo str_replace('，', ',', $instance['cat']);
        ?>" size="24" />
		</p>
		<p>
			<?php zib_topics_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('topics');
        ?>" name="<?php echo $this->get_field_name('topics');
        ?>" type="text" value="<?php echo $instance['topics'];
        ?>" size="24" />
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" name="<?php echo $this->get_field_name('limit') ?>" type="number" value="<?php echo $instance['limit'] ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				限制时间（最近X天）：
				<input style="width:100%;" name="<?php echo $this->get_field_name('limit_day') ?>" type="number" value="<?php echo $instance['limit_day'] ?>" size="24" />
			</label>
		</p>

		<p>
			<label>
				列表显示模式：
				<select style="width:100%;" id="<?php echo $this->get_field_id('type');
        ?>" name="<?php echo $this->get_field_name('type');
        ?>">
					<option value="auto" <?php selected('auto', $instance['type']);
        ?>>默认（自动跟随主题设置)</option>
					<option value="card" <?php selected('card', $instance['type']);
        ?>>卡片模式</option>
					<option value="no_thumb" <?php selected('no_thumb', $instance['type']);
        ?>>无缩略图列表</option>
					<option value="mult_thumb" <?php selected('mult_thumb', $instance['type']);
        ?>>多图模式</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				排序：
				<select style="width:100%;" id="<?php echo $this->get_field_id('orderby');
        ?>" name="<?php echo $this->get_field_name('orderby');
        ?>">
					<option value="comment_count" <?php selected('comment_count', $instance['orderby']);
        ?>>评论数</option>
					<option value="views" <?php selected('views', $instance['orderby']);
        ?>>浏览量</option>
					<option value="like" <?php selected('like', $instance['orderby']);
        ?>>点赞数</option>
					<option value="favorite" <?php selected('favorite', $instance['orderby']);
        ?>>收藏数</option>
                	<option value="comment_count" <?php selected('sales_volume', $instance['orderby']);
        ?>>销售数量</option>
					<option value="date" <?php selected('date', $instance['orderby']);
        ?>>发布时间</option>
					<option value="modified" <?php selected('modified', $instance['orderby']);
        ?>>更新时间</option>
					<option value="rand" <?php selected('rand', $instance['orderby']);
        ?>>随机排序</option>
				</select>
			</label>
		</p>
	<?php
}
}

///////单行滚动文章版块------//单行滚动文章版块------//单行滚动文章版块------//单行滚动文章版块------
class widget_ui_oneline_posts extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_oneline_posts',
            'w_name'      => _name('单行文章列表'),
            'classname'   => '',
            'description' => '显示文章列表，只显示一行，自动横向滚动',
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
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'type'         => 'auto',
            'limit'        => 6, 'limit_day' => '',
            'topics'       => '',
            'cat'          => '',
            'orderby'      => 'views',
            'order'        => 'DESC',
        );

        $instance = wp_parse_args((array) $instance, $defaults);
        $orderby  = $instance['orderby'];

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

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        echo $title;
        //    echo '<pre>'.json_encode($instance).'</pre>';

        $args = array(
            'post_status'         => 'publish',
            'cat'                 => str_replace('，', ',', $instance['cat']),
            'order'               => isset($instance['order']) && $instance['order'] == 'asc' ? 'ASC' : 'DESC',
            'showposts'           => $instance['limit'],
            'no_found_rows'       => true, //不需要分页，不查询分页需要的总数量
            'ignore_sticky_posts' => 1,
        );

        if ($orderby !== 'views' && $orderby !== 'favorite' && $orderby !== 'like') {
            $args['orderby'] = $orderby;
        } else {
            $args['orderby']    = 'meta_value_num';
            $args['meta_query'] = array(
                array(
                    'key'   => $orderby,
                    'order' => $args['order'], //ASC DESC
                ),
            );
        }
        if ($instance['topics']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'topics',
                    'terms'    => preg_split("/,|，|\s|\n/", $instance['topics']),
                ),
            );
        }
        if ($instance['limit_day'] > 0) {
            $current_time = current_time('Y-m-d H:i:s');

            $args['date_query'] = array(
                array(
                    'after'     => date('Y-m-d H:i:s', strtotime('-' . $instance['limit_day'] . ' day', strtotime($current_time))),
                    'before'    => $current_time,
                    'inclusive' => true,
                ),
            );
        }

        $list_args = array(
            'type' => 'card',
        );
        $the_query = new WP_Query($args);

        echo '<div class="swiper-container swiper-scroll" data-slideClass="posts-item">';
        echo '<div class="swiper-wrapper">';
        zib_posts_list($list_args, $the_query);
        echo '</div>';
        echo '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>';
        echo '</div>';
        echo '</div>';
    }

    public function form($instance)
    {
        $defaults = array(
            'title'        => '热门文章',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'limit'        => 6, 'limit_day' => '',
            'type'         => 'auto',
            'topics'       => '',
            'cat'          => '',
            'orderby'      => 'views',
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

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));
        echo zib_edit_input_construct($page_input);
        ?>

		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on'); ?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
			</label>
		</p>
		<p>
			<?php zib_cat_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('cat');
        ?>" name="<?php echo $this->get_field_name('cat');
        ?>" type="text" value="<?php echo str_replace('，', ',', $instance['cat']);
        ?>" size="24" />
		</p>
		<p>
			<?php zib_topics_help()?>
			<input style="width:100%;" id="<?php echo $this->get_field_id('topics');
        ?>" name="<?php echo $this->get_field_name('topics');
        ?>" type="text" value="<?php echo $instance['topics'];
        ?>" size="24" />
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit');
        ?>" name="<?php echo $this->get_field_name('limit');
        ?>" type="number" value="<?php echo $instance['limit'];
        ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				限制时间（最近X天）：
				<input style="width:100%;" name="<?php echo $this->get_field_name('limit_day') ?>" type="number" value="<?php echo $instance['limit_day'] ?>" size="24" />
			</label>
		</p>

		<p>
			<label>
				排序方式：
				<select style="width:100%;" id="<?php echo $this->get_field_id('orderby');
        ?>" name="<?php echo $this->get_field_name('orderby');
        ?>">
					<option value="comment_count" <?php selected('comment_count', $instance['orderby']);
        ?>>评论数</option>
					<option value="views" <?php selected('views', $instance['orderby']);
        ?>>浏览量</option>
					<option value="like" <?php selected('like', $instance['orderby']);
        ?>>点赞数</option>
					<option value="favorite" <?php selected('favorite', $instance['orderby']);
        ?>>收藏数</option>
        			<option value="comment_count" <?php selected('sales_volume', $instance['orderby']);
        ?>>销售数量</option>
					<option value="date" <?php selected('date', $instance['orderby']);
        ?>>发布时间</option>
					<option value="modified" <?php selected('modified', $instance['orderby']);
        ?>>更新时间</option>
					<option value="rand" <?php selected('rand', $instance['orderby']);
        ?>>随机排序</option>
				</select>
			</label>
            <label>
				<select style="width:100%;" id="<?php echo $this->get_field_id('order');
        ?>" name="<?php echo $this->get_field_name('order');
        ?>">
					<option value="desc" <?php selected('desc', $instance['orderby']);
        ?>>升序</option>
					<option value="asc" <?php selected('asc', $instance['orderby']);
        ?>>降序</option>
				</select>
			</label>
        </p>
        <label>
<?php
}
}

//分类、专题图文模块
Zib_CFSwidget::create('zib_widget_ui_term_card', array(
    'title'            => '分类图文卡片',
    'zib_title'        => true,
    'zib_affix'        => true,
    'zib_show'         => true,
    'description'      => '将分类、专题显示为图文卡片',
    'zib_animation_in' => false,
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
            'id'          => 'term_id',
            'title'       => '添加分类、专题',
            'desc'        => '选择并排序需要的分类、专题，如选择的分类(专题)下没有文章则不会显示',
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy' => array('topics', 'category'),
                'orderby'  => 'taxonomy',
            ),
            'placeholder' => '输入关键词以搜索分类或专题',
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'type'        => 'select',
        ),
        array(
            'title'   => __('卡片样式', 'zibll'),
            'id'      => 'type',
            'type'    => 'radio',
            'default' => 'style-1',
            'options' => array(
                'style-1' => '简单样式',
                'style-2' => '样式二',
                'style-3' => '样式三',
                'style-4' => '样式四',
            ),
        ),
        array(
            'id'      => 'height_scale',
            'title'   => '卡片长宽比例',
            'default' => 60,
            'max'     => 300,
            'min'     => 20,
            'step'    => 5,
            'unit'    => '%',
            'type'    => 'spinner',
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
            'title'   => '新窗口打开',
            'id'      => 'target_blank',
            'type'    => 'switcher',
            'default' => false,
        ),
    ),
));

function zib_widget_ui_term_card($args, $instance)
{
    $defaults = array(
        'term_id'          => array(),
        'pc_row'           => 2,
        'm_row'            => 1,
        'orderby'          => 'modified',
        'order'            => 'desc',
        'count'            => 4,
        'target_blank'     => false,
        'obs_animation'    => '',
        'animation_repeat' => false,
        'type'             => 'style-1',
        'height_scale'     => 60,
        'mask_opacity'     => 10,
    );
    $instance = wp_parse_args($instance, $defaults);

    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['term_id'][0]) || !$show_class) {
        return;
    }

    //准备栏目
    $pc_row = (int) $instance['pc_row'];
    $m_row  = (int) $instance['m_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= $m_row > 1 ? ' col-xs-' . (int) (12 / $m_row) : '';

    $terms = get_terms(array(
        'include' => $instance['term_id'],
        'orderby' => 'include',
    ));
    $is_row       = count($terms) > 1;
    $target_blank = !empty($instance['target_blank']) ? '_blank' : '';
    $html         = '';
    if ($terms) {
        foreach ($terms as $term) {
            $default_img = '';
            if ($term->taxonomy == 'category') {
                $default_img = _pz('cat_default_cover');
                $icon        = '<i class="fa fa-folder-open-o mr6" aria-hidden="true"></i>';
            } elseif ($term->taxonomy == 'topics') {
                $default_img = _pz('topics_default_cover');
                $icon        = '<i class="fa fa-cube mr6" aria-hidden="true"></i>';
            }
            $img         = zib_get_taxonomy_img_url($term->term_id, null, $default_img);
            $name        = zib_str_cut($term->name, 0, 16, '...');
            $count       = (int) $term->count ? (int) $term->count : 0;
            $description = zib_str_cut($term->description, 0, 24, '...');

            $href = get_term_link($term);
            $card = array(
                'type'         => $instance['type'],
                'class'        => 'mb10',
                'img'          => $img,
                'alt'          => $name . '-' . $description,
                'link'         => array(
                    'url'    => $href,
                    'target' => $target_blank,
                ),
                'text1'        => $name,
                'text2'        => $description,
                'text3'        => $icon . $count . '篇文章',
                'lazy'         => true,
                'height_scale' => $instance['height_scale'],
                'mask_opacity' => $instance['mask_opacity'],
            );

            if ($instance['type'] == 'style-2') {
                $card['text1'] = $name;
                $card['text2'] = $description;
                $card['text3'] = '<item data-toggle="tooltip" title="共' . $count . '篇文章">' . $icon . $count . '</item>';
            } elseif ($instance['type'] == 'style-3') {
                $card['text1'] = $icon . $name;
                $card['text2'] = $description;
                $card['text3'] = '<i class="fa mr6 fa-file-text-o"></i>' . $count . '篇文章';
            } elseif ($instance['type'] == 'style-4') {
                $card['text1'] = $icon . $name;
                $card['text2'] = $description;
                $card['text3'] = '<item data-toggle="tooltip" title="共' . $count . '篇文章">' . $icon . $count . '</item>';
            }

            $attr      = '';
            $col_class = '';
            if ($instance['obs_animation']) {
                $col_class .= ' obs-animate ani-' . $instance['obs_animation'];
                $attr .= ' data-animation="' . esc_attr($instance['obs_animation']) . '"';
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
    }

    Zib_CFSwidget::echo_before($instance, ($is_row ? 'mb10' : 'mb20'));
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    Zib_CFSwidget::echo_after($instance);
}

//分类、专题聚合模块
Zib_CFSwidget::create('zib_widget_ui_term_lists_card', array(
    'title'            => '专题&分类聚合卡片',
    'zib_title'        => true,
    'zib_affix'        => true,
    'zib_show'         => true,
    'zib_animation_in' => false,
    'description'      => '将分类、专题以及文字内容显示为卡片',
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
            'id'      => 'pc_row',
            'title'   => '单行布局',
            'desc'    => '请根据此模块放置位置的宽度合理调整单行数量',
            'default' => 2,
            'max'     => 2,
            'min'     => 1,
            'step'    => 1,
            'unit'    => '个',
            'type'    => 'slider',
        ),
        array(
            'id'          => 'term_id',
            'title'       => '添加分类、专题',
            'desc'        => '选择并排序需要的分类、专题，如选择的分类(专题)下没有文章则不会显示',
            'options'     => 'categories',
            'query_args'  => array(
                'taxonomy' => array('topics', 'category'),
                'orderby'  => 'taxonomy',
            ),
            'placeholder' => '输入关键词以搜索分类或专题',
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'chosen'      => true,
            'multiple'    => true,
            'sortable'    => true,
            'type'        => 'select',
        ),
        array(
            'dependency' => array('term_id', '!=', ''),
            'id'         => 'orderby',
            'default'    => 'modified',
            'title'      => '排序方式',
            'type'       => 'select',
            'options'    => CFS_Module::posts_orderby(),
        ),
        array(
            'dependency' => array('term_id', '!=', ''),
            'id'         => 'order',
            'default'    => 'desc',
            'class'      => 'compact',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => array(
                'desc' => '升序',
                'asc'  => '降序',
            ),
        ),
        array(
            'dependency' => array('term_id', '!=', ''),
            'id'         => 'count',
            'title'      => '最大文章数量',
            'desc'       => '<div class="c-yellow">请确保所选的分类或专题内的文章数量均超过此数量，否则会出现布局错位的问题！</div>',
            'default'    => 4,
            'max'        => 20,
            'min'        => 1,
            'step'       => 1,
            'unit'       => '篇',
            'type'       => 'spinner',
        ),
        array(
            'dependency' => array('term_id', '!=', ''),
            'title'      => '新窗口打开',
            'id'         => 'target_blank',
            'type'       => 'switcher',
            'default'    => false,
        ),
    ),
));

//
function zib_widget_ui_term_lists_card($args, $instance)
{
    $defaults = array(
        'term_id'          => array(),
        'pc_row'           => 2,
        'm_row'            => 1,
        'orderby'          => 'modified',
        'order'            => 'desc',
        'count'            => 4,
        'target_blank'     => false,
        'obs_animation'    => '',
        'animation_repeat' => false,
    );
    $instance = wp_parse_args($instance, $defaults);

    $show_class = Zib_CFSwidget::show_class($instance);
    if (empty($instance['term_id'][0]) || !$show_class) {
        return;
    }

    //准备栏目
    $pc_row = (int) $instance['pc_row'];

    $row_class = 'col-sm-' . (int) (12 / $pc_row);
    $row_class .= ' col-xs-12';

    $is_row       = count($instance['term_id']) > 1;
    $target_blank = !empty($instance['target_blank']) ? '_blank' : '';
    $html         = '';
    if ($instance['term_id']) {
        foreach ($instance['term_id'] as $key => $term_id) {
            $term_args = array(
                'term_id'      => $term_id,
                'class'        => '',
                'target_blank' => $target_blank,
                'orderby'      => $instance['orderby'],
                'order'        => isset($instance['order']) && $instance['order'] === 'asc' ? 'ASC' : 'DESC',
                'count'        => $instance['count'],
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
            $html .= zib_term_aggregation($term_args);
            $html .= '</div>';
        }
    }

    Zib_CFSwidget::echo_before($instance, 'clearfix mb6');
    echo $is_row ? '<div class="row gutters-5">' : '';
    echo $html;
    echo $is_row ? '</div>' : '';
    Zib_CFSwidget::echo_after($instance);
}

//热榜文章
Zib_CFSwidget::create('zib_widget_ui_hot_posts', array(
    'title'       => '热榜文章',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'size'        => 'mini',
    'description' => '显示文章榜单排名，此模块适合放置在侧边栏或移动菜单内',
    'fields'      => array(
        array(
            'id'      => 'orderby',
            'default' => 'views',
            'title'   => '榜单类型',
            'type'    => 'radio',
            'options' => array(
                'views'         => '热门榜单(按阅读量排序)',
                'like'          => '超赞榜单(按点赞量排序)',
                'comment_count' => '话题榜单(按评论量排序)',
                'favorite'      => '收藏榜单(按收藏量排序)',
            ),
        ),
        array(
            'id'      => 'limit_day',
            'title'   => '限制时间(最近X天)',
            'desc'    => '设置多少天内发布的文章有效，为0则不限制时间',
            'default' => 0,
            'max'     => 999999,
            'min'     => 0,
            'step'    => 1,
            'unit'    => '天',
            'type'    => 'spinner',
        ),
        array(
            'id'      => 'count',
            'title'   => '最大显示数量',
            'default' => 6,
            'max'     => 20,
            'min'     => 1,
            'step'    => 1,
            'unit'    => '篇',
            'type'    => 'spinner',
        ),
        array(
            'title'   => '新窗口打开',
            'id'      => 'target_blank',
            'type'    => 'switcher',
            'default' => false,
        ),
    ),
));

//
function zib_widget_ui_hot_posts($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    $html         = zib_hot_posts($instance);
    $args['size'] = 'mini';
    Zib_CFSwidget::echo_before($instance, '', $args);
    echo $html;
    Zib_CFSwidget::echo_after($instance, $args);
}

//付费商品
Zib_CFSwidget::create('zib_widget_ui_posts_pay', array(
    'title'       => '付费购买',
    'zib_title'   => false,
    'zib_affix'   => true,
    'zib_show'    => false,
    'size'        => 'mini',
    'description' => '显示当前文章的付费购买模块，推荐放置在侧边栏',
    'fields'      => array(
        array(
            'id'      => 'theme',
            'title'   => '色彩主题',
            'class'   => 'skin-color',
            'default' => 'jb-red',
            'type'    => 'palette',
            'options' => array(
                'jb-red'    => array('linear-gradient(135deg, #ffbeb4 10%, #f61a1a 100%)'),
                'jb-yellow' => array('linear-gradient(135deg, #ffd6b2 10%, #ff651c 100%)'),
                'jb-blue'   => array('linear-gradient(135deg, #b6e6ff 10%, #198aff 100%)'),
                'jb-green'  => array('linear-gradient(135deg, #ccffcd 10%, #52bb51 100%)'),
                'jb-purple' => array('linear-gradient(135deg, #fec2ff 10%, #d000de 100%)'),
                'jb-vip1'   => array('linear-gradient(25deg, #eab869 10%, #fbecd4 60%, #ffe0ae 100%)'),
                'jb-vip2'   => array('linear-gradient(317deg, #4d4c4c 30%, #878787 70%, #5f5c5c 100%)'),
            ),
        ),
    ),
));

//
function zib_widget_ui_posts_pay($args, $instance)
{
    $args['size'] = 'mini';
    $html         = zibpay_get_widget_box($instance);
    Zib_CFSwidget::echo_before($instance, '', $args);
    echo $html;
    Zib_CFSwidget::echo_after($instance, $args);
}

//文章列表-新
Zib_CFSwidget::create('zib_widget_ui_main_post', array(
    'title'       => '文章列表(新)',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '通过各种筛选、排序显示文章列表，支持多种显示模式',
    'fields'      => array(
        array(
            'title'   => '模块加载方式',
            'id'      => 'load_mode',
            'default' => 'ajax',
            'type'    => 'radio',
            'inline'  => true,
            'desc'    => __('ajax懒加载：当页面加载完后，根据用户需要在加载当前模块内容，可提高页面渲染效率', 'zibll'),
            'options' => array(
                'detail' => '直接加载',
                'ajax'   => 'ajax懒加载',
            ),
        ),
        array(
            'id'    => 'cat',
            'title' => __('分类限制', 'zibll'),
            'type'  => 'text',
        ),
        array(
            'id'    => 'topics',
            'title' => __('专题限制', 'zibll'),
            'desc'  => __('分类或专题限制请填写对应的ID，多个ID请用英文逗号隔开。如：1,2,3。支持负数进行排除，例如：-1,-2,-3。（在后台分类、专题列表中可查看ID）', 'zibll'),
            'type'  => 'text',
        ),
        array(
            'title'       => '商品类型筛选',
            'id'          => 'zibpay_type',
            'default'     => [],
            'inline'      => true,
            'type'        => 'checkbox',
            'placeholder' => '不做其它筛选',
            'options'     => array(
                '1' => '付费阅读',
                '2' => '付费下载',
                '5' => '付费图片',
                '6' => '付费视频',
            ),
        ),
        array(
            'title'   => '发布时间限制',
            'desc'    => '仅显示最近多少天发布的文章，为0则不限制',
            'id'      => 'limit_day',
            'class'   => '',
            'default' => 0,
            'min'     => 0,
            'step'    => 5,
            'unit'    => '天',
            'type'    => 'spinner',
        ),
        array(
            'id'       => 'orderby',
            'type'     => 'select',
            'default'  => '',
            'title'    => '排序方式',
            'subtitle' => '',
            'options'  => CFS_Module::posts_orderby(),
        ),
        array(
            'id'      => 'order',
            'default' => 'desc',
            'class'   => 'compact',
            'inline'  => true,
            'type'    => 'radio',
            'options' => array(
                'desc' => '升序',
                'asc'  => '降序',
            ),
        ),
        array(
            'title'   => '列表样式',
            'id'      => 'style',
            'default' => 'list',
            'type'    => 'radio',
            'desc'    => '<div class="c-yellow">注意：不同样式尺寸不同，请根据放置的位置合理选择。例如：放在宽度较小的侧边栏，则需选择mini样式，否则显示效果不佳</div>',
            'inline'  => true,
            'options' => array(
                'list' => '列表样式',
                'card' => '卡片样式',
                'mini' => 'mini列表',
            ),
        ),
        array(
            'dependency'  => array('style', '==', 'mini'),
            'title'       => 'mini列表配置',
            'id'          => 'mini_opt',
            'default'     => [],
            'inline'      => true,
            'type'        => 'checkbox',
            'placeholder' => '不做其它筛选',
            'options'     => array(
                'show_thumb'  => '显示缩略图',
                'show_number' => '显示编号（开启翻页后，只在第一页有效）',
                'show_meta'   => '显示作者、时间、点赞等信息',
            ),
        ),
        array(
            'title'   => '显示数量',
            'id'      => 'count',
            'class'   => '',
            'default' => 12,
            'max'     => 20,
            'min'     => 4,
            'step'    => 1,
            'unit'    => '篇',
            'type'    => 'spinner',
        ),
        array(
            'id'      => 'paginate',
            'title'   => '翻页按钮',
            'default' => '',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                ''       => __('不翻页', 'zibll'),
                'ajax'   => __('AJAX追加列表翻页', 'zibll'),
                'number' => __('数字翻页按钮', 'zibll'),
            ),
        ),
    ),
));

function zib_widget_ui_main_post($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!isset($instance['style']) || !$show_class) {
        return;
    }

    $style = $instance['style'] ? $instance['style'] : 'list';
    $class = 'widget-main-post mb20 style-' . $style;

    $main_html = '';
    $widget_id = $args['widget_id'];
    $id_base   = 'zib_widget_ui_main_post';
    $index     = str_replace($id_base . '-', '', $widget_id);

    if (isset($instance['load_mode']) && $instance['load_mode'] === 'ajax') {
        $placeholder = ''; //
        if ($style == 'mini') {
            $placeholder = str_repeat('<div class="posts-mini"><div class="placeholder k1"></div></div>', $instance['count']);
            $mini_opt    = $instance['mini_opt'] ? $instance['mini_opt'] : array();
            if (in_array('show_thumb', $mini_opt)) {
                $placeholder = str_repeat('<div class="posts-mini "><div class="mr10"><div class="item-thumbnail placeholder"></div></div><div class="posts-mini-con flex xx flex1 jsb"><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div>', $instance['count']);
            }
        } else {
            $placeholder_type = $style == 'card' ? 'card' : 'lists';
            $placeholder      = zib_get_post_placeholder($placeholder_type, $instance['count']);
        }

        $ias_args = array(
            'type'            => 'ias',
            'id'              => '',
            'class'           => '',
            'loader'          => $placeholder, // 加载动画
            'ajaxpager_class' => 'widget-ajaxpager',
            'query'           => array(
                'action' => 'ajax_widget_ui',
                'id'     => $id_base,
                'index'  => $index,
            ),
        );
        $main_html = zib_get_ias_ajaxpager($ias_args);
    } else {
        $main_html = zib_widget_ui_main_post_ajax($instance, true, add_query_arg(array(
            'action' => 'ajax_widget_ui',
            'id'     => $id_base,
            'index'  => $index,
        ), admin_url('/admin-ajax.php')));
    }

    //开始输出
    Zib_CFSwidget::echo_before($instance, $class);
    echo $style == 'mini' ? '<div class="zib-widget posts-mini-lists">' : '';
    echo $main_html;
    echo $style == 'mini' ? '</div>' : '';
    Zib_CFSwidget::echo_after($instance);
}

function zib_widget_ui_main_post_ajax($instance, $no_ajax = false, $ajax_url = null)
{
    $paged      = zib_get_the_paged();
    $style      = $instance['style'] ? $instance['style'] : 'list';
    $paginate   = $instance['paginate'] ? $instance['paginate'] : '';
    $paged_size = $instance['count'];
    $ajax_url   = $ajax_url ?: zib_get_current_url();

    $posts_args = array(
        'cat'         => $instance['cat'],
        'topics'      => $instance['topics'],
        'zibpay_type' => $instance['zibpay_type'],
        'orderby'     => $instance['orderby'],
        'order'       => isset($instance['order']) && $instance['order'] === 'asc' ? 'ASC' : 'DESC',
        'count'       => $instance['count'],
        'limit_day'   => isset($instance['limit_day']) ? (int) $instance['limit_day'] : 0,
    );

    //不需要翻页
    if (!$paginate) {
        $posts_args['no_found_rows'] = true;
        $paged                       = 1;
    }

    $posts_query = zib_get_posts_query($posts_args);
    $lists       = '';
    $mini_number = $paged * $paged_size - $paged_size;

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()): $posts_query->the_post();
            if ($style == 'card') {
                $lists .= zib_posts_mian_list_card(array());
            } elseif ($style == 'mini') {
            $mini_opt = $instance['mini_opt'] ? $instance['mini_opt'] : array();

            $mini_args = array(
                'class'       => 'ajax-item',
                'show_thumb'  => in_array('show_thumb', $mini_opt),
                'show_meta'   => in_array('show_meta', $mini_opt),
                'show_number' => in_array('show_number', $mini_opt),
                'echo'        => false,
            );
            $mini_number++;
            $lists .= zib_posts_mini_while($mini_args, $mini_number);
        } else {
            $lists .= zib_posts_mian_list_list(array('is_mult_thumb' => 'disable', 'is_no_thumb' => 'disable'));
        }

        endwhile;
        wp_reset_query();
    }
    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null('暂无内容', 10);
    }

    //分页paginate
    if ($paginate === 'ajax') {
        $lists .= zib_get_ajax_next_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no', '.widget-ajaxpager');
    } elseif ($paginate === 'number') {
        $lists .= zib_get_ajax_number_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'paged', '.widget-ajaxpager');
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    if ($no_ajax) {
        return '<div class="widget-ajaxpager">' . $lists . '</div>';
    }
    zib_ajax_send_ajaxpager($lists, false, 'widget-ajaxpager');
}

//文章列表-新
Zib_CFSwidget::create('zib_widget_ui_tab_post', array(
    'title'       => '多栏目文章(新)',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '多个TAB栏目切换显示文章，支持各种筛选、排序、多种显示模式、翻页等功能',
    'fields'      => array(
        array(
            'title'   => '列表样式',
            'id'      => 'style',
            'default' => 'mini',
            'type'    => 'radio',
            'inline'  => true,
            'desc'    => '<div class="c-yellow">注意：不同样式尺寸不同，请根据放置的位置合理选择。例如：放在宽度较小的侧边栏，则需选择mini样式，否则显示效果不佳</div>',
            'options' => array(
                'list' => '列表样式',
                'card' => '卡片样式',
                'mini' => 'mini列表',
            ),
        ),
        array(
            'dependency'  => array('style', '==', 'mini'),
            'title'       => 'mini列表配置',
            'id'          => 'mini_opt',
            'default'     => [],
            'inline'      => true,
            'type'        => 'checkbox',
            'placeholder' => '不做其它筛选',
            'options'     => array(
                'show_thumb'  => '显示缩略图',
                'show_number' => '显示编号（开启翻页后，只在第一页有效）',
                'show_meta'   => '显示作者、时间、点赞等信息',
            ),
        ),
        array(
            'title'   => '显示数量',
            'id'      => 'count',
            'class'   => '',
            'default' => 6,
            'max'     => 20,
            'min'     => 4,
            'step'    => 1,
            'unit'    => '篇',
            'type'    => 'spinner',
        ),
        array(
            'id'      => 'paginate',
            'title'   => '翻页按钮',
            'default' => '',
            'type'    => 'radio',
            'inline'  => true,
            'options' => array(
                ''       => __('不翻页', 'zibll'),
                'ajax'   => __('AJAX追加列表翻页', 'zibll'),
                'number' => __('数字翻页按钮', 'zibll'),
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
                    'title'   => '热门推荐',
                    'orderby' => 'views',
                ),
                array(
                    'title'   => '最近更新',
                    'orderby' => 'modified',
                ),
                array(
                    'title'   => '猜你喜欢',
                    'orderby' => 'rand',
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
                    'id'    => 'cat',
                    'title' => __('分类限制', 'zibll'),
                    'type'  => 'text',
                ),
                array(
                    'id'    => 'topics',
                    'title' => __('专题限制', 'zibll'),
                    'desc'  => __('分类或专题限制请填写对应的ID，多个ID请用英文逗号隔开。如：1,2,3。支持负数进行排除，例如：-1,-2,-3。（在后台分类、专题列表中可查看ID）', 'zibll'),
                    'type'  => 'text',
                ),
                array(
                    'title'       => '商品类型筛选',
                    'id'          => 'zibpay_type',
                    'default'     => [],
                    'inline'      => true,
                    'type'        => 'checkbox',
                    'placeholder' => '不做其它筛选',
                    'options'     => array(
                        '1' => '付费阅读',
                        '2' => '付费下载',
                        '5' => '付费图片',
                        '6' => '付费视频',
                    ),
                ),
                array(
                    'title'   => '发布时间限制',
                    'desc'    => '仅显示最近多少天发布的文章，为0则不限制',
                    'id'      => 'limit_day',
                    'class'   => '',
                    'default' => 0,
                    'min'     => 0,
                    'step'    => 5,
                    'unit'    => '天',
                    'type'    => 'spinner',
                ),
                array(
                    'id'       => 'orderby',
                    'type'     => 'select',
                    'default'  => '',
                    'title'    => '排序方式',
                    'subtitle' => '',
                    'options'  => CFS_Module::posts_orderby(),
                ),
                array(
                    'id'      => 'order',
                    'default' => 'desc',
                    'class'   => 'compact',
                    'inline'  => true,
                    'type'    => 'radio',
                    'options' => array(
                        'desc' => '升序',
                        'asc'  => '降序',
                    ),
                ),
            ),
        ),
    ),
));

function zib_widget_ui_tab_post($args, $instance)
{

    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class || empty($instance['tabs'])) {
        return;
    }

    $style = $instance['style'] ? $instance['style'] : 'list';
    $class = 'widget-tab-post style-' . $style;
    $class .= $style == 'mini' ? ' posts-mini-lists zib-widget' : ' index-tab relative-h';

    $main_html = '';
    $widget_id = $args['widget_id'];
    $id_base   = 'zib_widget_ui_tab_post';
    $index     = str_replace($id_base . '-', '', $widget_id);

    $placeholder = ''; //
    if ($style == 'mini') {
        $placeholder = str_repeat('<div class="posts-mini"><div class="placeholder k1"></div></div>', $instance['count']);
        $mini_opt    = $instance['mini_opt'] ? $instance['mini_opt'] : array();
        if (in_array('show_thumb', $mini_opt)) {
            $placeholder = str_repeat('<div class="posts-mini "><div class="mr10"><div class="item-thumbnail placeholder"></div></div><div class="posts-mini-con flex xx flex1 jsb"><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div>', $instance['count']);
        }
    } else {
        $placeholder_type = $style == 'card' ? 'card' : 'lists';
        $placeholder      = zib_get_post_placeholder($placeholder_type, $instance['count']);
    }

    $tabs_con  = '';
    $tabs_nav  = '';
    $tabs_i    = 1;
    $tabs      = $instance['tabs'];
    $ajax_href = add_query_arg(array(
        'action' => 'ajax_widget_ui',
        'id'     => $id_base,
        'index'  => $index,
    ), admin_url('/admin-ajax.php'));

    foreach ($instance['tabs'] as $tabs_key => $tabs) {
        if (empty($tabs['title'])) {
            continue;
        }
        $tab_id    = $widget_id . '-' . $tabs_i;
        $nav_class = $tabs_i == 1 ? 'active' : '';
        $con_class = $tabs_i == 1 ? ' active in' : '';

        $con_html = '';
        if ($tabs_i == 1) {
            $con_html = zib_widget_ui_tab_post_ajax($instance, true, add_query_arg('tab', $tabs_key, $ajax_href), $tabs_key);
        } else {
            $con_html .= '<span class="post_ajax_trigger hide"><a ajaxpager-target=".widget-ajaxpager" href="' . add_query_arg('tab', $tabs_key, $ajax_href) . '" class="ajax_load ajax-next ajax-open" no-scroll="true"></a></span>';
        }
        $con_html .= '<div class="post_ajax_loader" style="display: none;">' . $placeholder . '</div>';

        $tabs_nav .= '<li class="' . $nav_class . '"><a' . ($tabs_i !== 1 ? ' data-ajax' : '') . ' data-toggle="tab" href="#' . $tab_id . '">' . $tabs['title'] . '</a></li>';
        $tabs_con .= '<div class="tab-pane fade' . $con_class . '" id="' . $tab_id . '"><div class="widget-ajaxpager">' . $con_html . '</div></div>';

        $tabs_i++;
    }

    if (!$tabs_nav) {
        return;
    }

    $main_html = '
        <div class="relative' . ($style == 'card' ? ' mb20' : '') . '">
        <ul class="list-inline scroll-x no-scrollbar' . ($style == 'mini' ? ' tab-nav-theme' : '') . '">
            ' . $tabs_nav . '
        </ul>
        </div>
        <div class="tab-content">
            ' . $tabs_con . '
        </div>';

    //开始输出
    Zib_CFSwidget::echo_before($instance, $class);
    echo $main_html;
    Zib_CFSwidget::echo_after($instance);
}

function zib_widget_ui_tab_post_ajax($instance, $no_ajax = false, $ajax_url = null, $tab = 0)
{

    $paged      = zib_get_the_paged();
    $style      = $instance['style'] ? $instance['style'] : 'list';
    $paginate   = $instance['paginate'] ? $instance['paginate'] : '';
    $paged_size = $instance['count'];
    $ajax_url   = $ajax_url ?: zib_get_current_url();
    $tab        = $tab ? $tab : (isset($_REQUEST['tab']) ? (int) $_REQUEST['tab'] : 0);

    $posts_args = array(
        'cat'         => $instance['tabs'][$tab]['cat'] ?? '',
        'topics'      => $instance['tabs'][$tab]['topics'] ?? '',
        'zibpay_type' => isset($instance['tabs'][$tab]['zibpay_type']) ? $instance['tabs'][$tab]['zibpay_type'] : '',
        'orderby'     => $instance['tabs'][$tab]['orderby'] ?? '',
        'order'       => isset($instance['tabs'][$tab]['order']) && $instance['tabs'][$tab]['order'] === 'asc' ? 'ASC' : 'DESC',
        'count'       => $instance['count'] ?? 6,
        'limit_day'   => isset($instance['tabs'][$tab]['limit_day']) ? (int) $instance['tabs'][$tab]['limit_day'] : 0,
    );

    //不需要翻页
    if (!$paginate) {
        $posts_args['no_found_rows'] = true;
        $paged                       = 1;
    }

    $posts_query = zib_get_posts_query($posts_args);
    $lists       = '';
    $mini_number = $paged * $paged_size - $paged_size;

    if ($posts_query->have_posts()) {
        while ($posts_query->have_posts()): $posts_query->the_post();
            if ($style == 'card') {
                $lists .= zib_posts_mian_list_card(array());
            } elseif ($style == 'mini') {
            $mini_opt = $instance['mini_opt'] ? $instance['mini_opt'] : array();

            $mini_args = array(
                'class'       => 'ajax-item',
                'show_thumb'  => in_array('show_thumb', $mini_opt),
                'show_meta'   => in_array('show_meta', $mini_opt),
                'show_number' => in_array('show_number', $mini_opt),
                'echo'        => false,
            );
            $mini_number++;
            $lists .= zib_posts_mini_while($mini_args, $mini_number);
        } else {
            $lists .= zib_posts_mian_list_list(array('is_mult_thumb' => 'disable', 'is_no_thumb' => 'disable'));
        }

        endwhile;
        wp_reset_query();
    }
    if (1 == $paged && !$lists) {
        $lists = zib_get_ajax_null('暂无内容', 10);
    }

    //分页paginate
    if ($paginate === 'ajax') {
        $lists .= zib_get_ajax_next_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no', '.widget-ajaxpager');
    } elseif ($paginate === 'number') {
        $lists .= zib_get_ajax_number_paginate($posts_query->found_posts, $paged, $paged_size, $ajax_url, 'ajax-pag', 'next-page ajax-next', 'paged', '.widget-ajaxpager');
    } else {
        $lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    if ($no_ajax) {
        return $lists;
    }
    zib_ajax_send_ajaxpager($lists, false, 'widget-ajaxpager');

}