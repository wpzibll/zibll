<?php
add_action('widgets_init', 'unregister_d_widget');
function unregister_d_widget()
{
    unregister_widget('WP_Widget_RSS');
    unregister_widget('WP_Widget_Recent_Posts');
    unregister_widget('WP_Widget_Recent_Comments');
    unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Widget_Search');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Recent_Comments');
}
add_action('load-widgets.php', 'zib_register_widget_jsloader');

function zib_register_widget_jsloader()
{
    _jsloader(array('widget-set.min'));
    _cssloader(array('widget-set' => 'widget-set.min'));
}

$widgets = array(
    'more',
    'posts',
    'user',
    'slider',
);

foreach ($widgets as $widget) {
    $path = 'inc/widgets/widget-' . $widget . '.php';
    require_once get_theme_file_path($path);
}

// 注册小工具
function zib_main_register_sidebar()
{
    if (function_exists('register_sidebar')) {
        $sidebars = array();
        $pags     = array(
            'home'   => '首页',
            'single' => '文章页',
            'cat'    => '分类页',
            'tag'    => '标签页',
            'search' => '搜索页',
        );

        $poss = array(
            'top_fluid'      => '顶部全宽度',
            'top_content'    => '主内容上面',
            'bottom_content' => '主内容下面',
            'bottom_fluid'   => '底部全宽度',
            'sidebar'        => '侧边栏',
        );

        $sidebars[] = array(
            'name'        => '所有页面-顶部全宽度',
            'id'          => 'all_top_fluid',
            'description' => '显示在所有页面的顶部全宽度位置，由于位置较多，建议使用实时预览管理！',
        );

        $sidebars[] = array(
            'name'        => '所有页面-底部全宽度',
            'id'          => 'all_bottom_fluid',
            'description' => '显示在所有页面的底部全宽度位置，由于位置较多，建议使用实时预览管理！',
        );

        $sidebars[] = array(
            'name'        => '所有页面-侧边栏-顶部位置',
            'id'          => 'all_sidebar_top',
            'description' => '显示在所有侧边栏的最上面位置，由于位置较多，建议使用实时预览管理！',
        );

        $sidebars[] = array(
            'name'        => '所有页面-侧边栏-底部位置',
            'id'          => 'all_sidebar_bottom',
            'description' => '显示在所有侧边栏的最下面，由于位置较多，建议使用实时预览管理！',
        );

        $sidebars[] = array(
            'name'        => '所有页面-页脚区内部',
            'id'          => 'all_footer',
            'description' => '显示最底部页脚区域内部，由于位置较多，建议使用实时预览管理！',
        );

        foreach ($pags as $key => $value) {
            foreach ($poss as $poss_key => $poss_value) {
                $sidebars[] = array(
                    'name'        => $value . '-' . $poss_value,
                    'id'          => $key . '_' . $poss_key,
                    'description' => '显示在 ' . $value . ' 的 ' . $poss_value . ' 位置，由于位置较多，建议使用实时预览管理！',
                );
            }
        }

        $pags_full = array(
            'author' => '作者页',
            'user'   => '用户中心',
            'msg'    => '消息中心',
        );

        foreach ($pags_full as $key => $value) {
            $sidebars[] = array(
                'name'        => $value . '-顶部全宽度',
                'id'          => $key . '_top_fluid',
                'description' => '显示在 ' . $value . ' 的 内容顶部 位置，由于位置较多，建议使用实时预览管理！',
            );
            $sidebars[] = array(
                'name'        => $value . '-底部全宽度',
                'id'          => $key . '_bottom_fluid',
                'description' => '显示在 ' . $value . ' 的 内容底部 位置，由于位置较多，建议使用实时预览管理！',
            );
            if ($key === 'user') {
                $sidebars[] = array(
                    'name'        => $value . '-侧边栏顶部',
                    'id'          => $key . '_sidebar_top',
                    'description' => '显示在 ' . $value . ' 的 侧边栏顶部 位置，由于位置较多，建议使用实时预览管理！',
                );
                $sidebars[] = array(
                    'name'        => $value . '-侧边栏底部',
                    'id'          => $key . '_sidebar_bottom',
                    'description' => '显示在 ' . $value . ' 的 侧边栏底部 位置，由于位置较多，建议使用实时预览管理！',
                );
            }
        }

        $sidebars[] = array(
            'name'        => '前台投稿-侧边栏顶部',
            'id'          => 'newposts_sidebar_top',
            'description' => '显示在前台投稿页面的侧边栏顶部，由于宽度较小，请勿添加大尺寸模块，同时会在移动端显示',
        );
        $sidebars[] = array(
            'name'        => '前台投稿-侧边栏底部',
            'id'          => 'newposts_sidebar_bottom',
            'description' => '显示在前台投稿页面的侧边栏底部，由于宽度较小，请勿添加大尺寸模块，同时会在移动端显示',
        );
        $sidebars[] = array(
            'name'        => '移动端-弹出菜单底部',
            'id'          => 'mobile_nav_fluid',
            'description' => '显示在移动端弹出的菜单内部的下方，由于宽度较小，请勿添加大尺寸模块',
        );

        foreach ($sidebars as $value) {
            register_sidebar(array(
                'name'          => $value['name'],
                'id'            => $value['id'],
                'description'   => $value['description'],
                'before_widget' => '<div class="zib-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3>',
                'after_title'   => '</h3>',
            ));
        }
    }
}
add_action('widgets_init', 'zib_main_register_sidebar');

//页面小工具
function zib_page_register_sidebar()
{
    $register_pages = zib_get_widgets_register_pages();
    if (!$register_pages) {
        return;
    }

    $args = array(
        'sidebar'        => __('侧边栏', 'zibll'),
        'top_fluid'      => __('顶部全宽度', 'zibll'),
        'top_content'    => __('主内容上面', 'zibll'),
        'bottom_content' => __('主内容下面', 'zibll'),
        'bottom_fluid'   => __('底部全宽度', 'zibll'),
    );
    foreach ($register_pages as $page) {
        $meta_value = maybe_unserialize($page->meta_value);

        if ($meta_value && is_array($meta_value)) {
            foreach ($meta_value as $key) {
                if (!isset($args[$key])) {
                    continue;
                }
                $name = zib_str_cut($page->post_title, 0, 10, '...');
                register_sidebar(array(
                    'name'          => '[页面:' . $name . ']-' . $args[$key],
                    'id'            => 'page_' . $key . '_' . $page->ID,
                    'description'   => '显示在[页面：' . $name . ']的 ' . $args[$key] . ' 位置',
                    'before_widget' => '<div class="zib-widget %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<h3>',
                    'after_title'   => '</h3>',
                ));
            }
        }
    }
}
add_action('widgets_init', 'zib_page_register_sidebar');

/**
 * @description: 获取页面小工具注册信息
 * @return {*}
 */
function zib_get_widgets_register_pages()
{

    $cache = wp_cache_get('widgets_register_pages', 'zib_cache_group', true);
    if (false !== $cache) {
        return $cache;
    }

    global $wpdb;
    $sql = "SELECT  $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_value
            FROM $wpdb->posts
                INNER JOIN $wpdb->postmeta ON (
                     $wpdb->posts.ID =  $wpdb->postmeta.post_id
                )
                INNER JOIN $wpdb->postmeta AS mt1 ON ( $wpdb->posts.ID = mt1.post_id)
            WHERE
                1 = 1
                AND ( (
                         $wpdb->postmeta.meta_key = 'widgets_register_container'
                        AND  $wpdb->postmeta.meta_value LIKE '%;}'
                    )
                    AND (
                        mt1.meta_key = 'widgets_register'
                        AND mt1.meta_value = 1
                    )
                )
                AND  $wpdb->posts.post_type = 'page'
                AND ( (
                         $wpdb->posts.post_status = 'publish'
                    )
                )
            GROUP BY  $wpdb->posts.ID
            ORDER BY
        $wpdb->posts.post_date ASC";

    $results = (array) $wpdb->get_results($sql);

    wp_cache_set('widgets_register_pages', $results, 'zib_cache_group');
    return $results;
}

//保存数据时候刷新缓存
function zib_widgets_register_pages_update_cache()
{
    wp_cache_delete('widgets_register_pages', 'zib_cache_group');
}
add_action('save_post_page', 'zib_widgets_register_pages_update_cache');

//挂钩一个通用ajax获取小工具内容
function zib_ajax_widget_ui()
{
    $id          = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $index       = isset($_REQUEST['index']) ? $_REQUEST['index'] : 0;
    $all_options = get_option('widget_' . $id);

    //判断是函数
    if (!function_exists($id . '_ajax') || !isset($all_options[$index])) {
        return;
    }

    call_user_func($id . '_ajax', $all_options[$index]);
    exit;
}
add_action('wp_ajax_ajax_widget_ui', 'zib_ajax_widget_ui');
add_action('wp_ajax_nopriv_ajax_widget_ui', 'zib_ajax_widget_ui');

function zib_cat_help()
{
    ?>
	<div>
		分类限制：<a class="cat-help-button" style="font-weight:bold;color: #ff0039;text-decoration:none;background: #ffe8e8;width: 1.5em;line-height: 1.5em;text-align: center;display: inline-block;border-radius: 50%;" href="javascript:;">?</a>
		<div class="cat-help-con" style="display:none;padding: 5px;border: 1px solid #ddd;margin: 5px 0;background: #f7f8f9;border-radius: 8px;font-size: 12px;">
			<p>分类限制通过分类的id进行分类筛选，可以选择某些分类或者排除某些分类。示例及id列表如下</p>
			<b>分类限制示例：</b>
			<p>
			<div>仅仅显示分类ID为"10"的文章</div>
			<div style="padding: 6px;background: #ececec">10</div>
			</p>
			<p>
			<div>显示包含分类ID为"10、11、12、13"的文章</div>
			<div style="padding: 6px;background: #ececec">10,11,12,13</div>
			</p>
			<p>
			<div>排除分类ID为"10、11、12、13"的文章</div>
			<div style="padding: 6px;background: #ececec">-10,-11,-12,-13</div>
			</p>
			<p>
			<div>排除分类ID为"10"的文章</div>
			<div style="padding: 6px;background: #ececec">-10</div>
			</p>
		</div>
	</div>
<?php
}
/**
 * 专题帮助
 *
 * @param string $name
 * @return void
 */

function zib_topics_help()
{
    ?>
	<div>
		专题选择：<a class="cat-help-button" style="font-weight:bold;color: #ff0039;text-decoration:none;background: #ffe8e8;width: 1.5em;line-height: 1.5em;text-align: center;display: inline-block;border-radius: 50%;" href="javascript:;">?</a>
		<div class="cat-help-con" style="display:none;padding: 5px;border: 1px solid #ddd;margin: 5px 0;background: #f7f8f9;border-radius: 8px;font-size: 12px;">
			<p>输入专题ID，输入多个ID请用英文逗号分割</p>
		</div>
	</div>
<?php
}
function zib_user_help($name = '')
{
    ?>
	<div>
		<?php echo $name; ?><a class="cat-help-button" style="font-weight:bold;color: #ff0039;text-decoration:none;background: #ffe8e8;width: 1.5em;line-height: 1.5em;text-align: center;display: inline-block;border-radius: 50%;" href="javascript:;">?</a>
		<div class="cat-help-con" style="display:none;padding: 5px;border: 1px solid #ddd;margin: 5px 0;background: #f7f8f9;border-radius: 8px;font-size: 12px;">
			<p>输入用户ID，输入多个ID请用英文逗号分割</p>
		</div>
	</div>
<?php
}

function zib_widget_option($type = 'cat', $selected = '')
{
    $html = '<option value="" ' . selected('', $selected, false) . '>未选择</option>';
    if ('cat' == $type) {
        $args = array(
            'orderby'    => 'count',
            'order'      => 'DESC',
            'hide_empty' => false,
        );
        $options_cat = get_categories($args);
        foreach ($options_cat as $category) {
            $title = rtrim(get_category_parents($category->cat_ID, false, '>'), '>') . '[ID:' . $category->cat_ID . '][共' . $category->count . '篇]';
            $_id   = $category->cat_ID;
            $html .= '<option value="' . $_id . '" ' . selected($_id, $selected, false) . '>' . $title . '</option>';
        }
    }
    return $html;
}

add_filter('zib_widget_title', 'zib_widget_filter_title', 11);
function zib_widget_filter_title($instance = array())
{
    $html     = '';
    $defaults = array(
        'title'        => '',
        'mini_title'   => '',
        'more_but'     => '',
        'more_but_url' => '',
    );
    $instance   = wp_parse_args((array) $instance, $defaults);
    $mini_title = $instance['mini_title'] ? '<small class="ml10 muted-color">' . $instance['mini_title'] . '</small>' : '';
    $title      = $instance['title'] ? $instance['title'] : '';
    $more_but   = '';
    if ($instance['more_but'] && $instance['more_but_url']) {
        $more_but = '<div class="pull-right em09 mt3"><a href="' . esc_url($instance['more_but_url']) . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
    }
    $mini_title .= $more_but;
    if ($title) {
        $title = '<div class="box-body notop"><div class="title-theme">' . $title . $mini_title . '</div></div>';
    }
    return $title;
}

function zib_get_widget_show_type_input($args, $name)
{
    $type = !empty($args['show_type']) ? $args['show_type'] : '';

    return '<div class=""><p class="mb6">显示规则</p><ul>
    <li><label><input type="radio" name="' . $name . '" value="all"' . (!$type || $type === 'all' ? ' checked="checked"' : '') . '><span class="ml6">PC端/移动端均显示</span></label></li>
    <li><label><input type="radio" name="' . $name . '" value="only_pc"' . ($type === 'only_pc' ? ' checked="checked"' : '') . '><span class="ml6">仅在PC端显示</span></label></li>
    <li><label><input type="radio" name="' . $name . '" value="only_sm"' . ($type === 'only_sm' ? ' checked="checked"' : '') . '><span class="ml6">仅在移动端显示</span></label></li>
    </ul></div>';
}

function zib_widget_is_show($args)
{

    $show_type = !empty($args['show_type']) ? $args['show_type'] : '';
    if (!$show_type) {
        return true;
    }

    if ($show_type == 'only_pc' && wp_is_mobile()) {
        return false;
    }

    if ($show_type == 'only_sm' && !wp_is_mobile()) {
        return false;
    }

    if ($show_type == 'only_pc') {
        return 'hidden-xs';
    }

    if ($show_type == 'only_sm') {
        return 'visible-xs-block';
    }

    return true;
}