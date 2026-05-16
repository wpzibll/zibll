<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:38
 * @LastEditTime : 2026-04-25 19:24:26
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**开启条件 */
function zib_admin_frontend_set_s()
{
    $is_on = true;
    if (!_pz('admin_frontend_set', true) || !is_super_admin() || (!is_category() && !is_tag() && !is_tax() && !is_page() && !is_single())) {
        $is_on = false;
    }

    $pid = get_queried_object_id();
    if (!$pid) {
        $is_on = false;
    }

    if (is_page_template('pages/postsnavs.php')) {
        $is_on = false;
    }

    return apply_filters('frontend_set_switch', $is_on);
}

/**前台编辑按钮 */
function zib_admin_frontend_set_botton($float)
{
    if (zib_admin_frontend_set_s()) {
        $float .= '<a href="javascript:;" data-toggle="modal" data-target="#modal_admin_set" title="设置页面参数" class="admin-set-page float-btn"><i class="fa fa-cog fa-spin" aria-hidden="true"></i><div class="abs-right badg c-red px12 admin-set-info" style="width:187px;">在此编辑此页面参数</div></a>';
    }
    return $float;
}
add_filter('zib_float_right', 'zib_admin_frontend_set_botton', 10, 2);

// AJAX-前台编辑
function zib_frontend_set_modal()
{
    if (!zib_admin_frontend_set_s()) {
        return;
    }

    $post_type = '';
    $taxonomy  = '';
    $object_id = get_queried_object_id();
    $title     = '';

    if (is_tax() || is_category() || is_tag()) {
        $type           = 'tax';
        $taxonomy       = get_queried_object()->taxonomy;
        $edit_post_link = get_edit_term_link($object_id, $taxonomy);
        $title          = get_taxonomy($taxonomy)->labels->singular_name;
    } else {
        $type           = 'post';
        $post_type      = get_post_type();
        $edit_post_link = get_edit_post_link($object_id);
        $title          = get_post_type_object($post_type)->labels->singular_name;
    }

    $header = '<div class="modal-header"><strong class="modal-title"><i class="fa fa-sliders mr10" aria-hidden="true"></i>' . $title . '设置</strong>
    <button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button>
                </div>';
    $footer = '<div class="modal-footer">
                    <a target="_blank" title="使用可视化布局配置此页面" data-toggle="tooltip" href="' . zib_get_customize_widgets_url() . '" class="but c-yellow-2 padding-lg"><i class="fa fa-pie-chart" aria-hidden="true"></i>模块布局</a>
                    <a target="_blank" href="' . $edit_post_link . '" class="but c-yellow padding-lg"><i class="fa fa-wordpress" aria-hidden="true"></i>后台编辑</a>
                    <button class="but jb-blue padding-lg wp-ajax-submit"><i class="fa fa-check" aria-hidden="true"></i>确认修改</button>
                </div>';

    $body = '<div class="modal-body">';
    $body .= zib_get_frontend_set_input($object_id, $type, $taxonomy, $post_type);
    $body .= '<input type="hidden" name="action" value="frontend_set_save">';
    $body .= '<input type="hidden" name="type" value="' . $type . '">';
    $body .= '<input type="hidden" name="taxonomy" value="' . $taxonomy . '">';
    $body .= '<input type="hidden" name="post_type" value="' . $post_type . '">';
    $body .= '<input type="hidden" name="id" value="' . $object_id . '">';
    $body .= zib_nonce_field('frontend_set_save');
    $body .= '</div>';
    $modal = '<div class="modal fade" id="modal_admin_set" tabindex="-1" role="dialog" aria-hidden="false" win-ajax-replace="modal_admin_set">
                <div class="modal-dialog" role="document">
                    <div class="modal-content page-set-modal">
                        <form>' . $header . $body . $footer . '</form>
                    </div>
                </div>
              </div>';
    echo $modal;
}
add_action('wp_footer', 'zib_frontend_set_modal', 10);

// 前台编辑的input
function zib_get_frontend_set_input($post_id, $type, $taxonomy, $post_type)
{
    $page_input = array();
    if ($type == 'post') {

        $page_input[] = array(
            'name'    => __('显示布局', 'zibll'),
            'id'      => 'show_layout',
            'std'     => zib_get_post_meta($post_id, 'show_layout', true),
            'type'    => 'radio',
            'options' => array(
                ''              => __('跟随主题', 'zibll'),
                'no_sidebar'    => __('无侧边栏', 'zibll'),
                'sidebar_left'  => __('侧边栏靠左', 'zibll'),
                'sidebar_right' => __('侧边栏靠右', 'zibll'),
            ),
        );

        $page_input[] = array(
            'name' => __('标题', 'zibll'),
            'id'   => 'post_title',
            'std'  => get_the_title($post_id),
            'type' => 'text',
        );

        if (is_single()) {
            $page_input[] = array(
                'name' => __('副标题', 'zibll'),
                'id'   => 'subtitle',
                'std'  => zib_get_post_meta($post_id, 'subtitle', true),
                'type' => 'text',
            );
            $page_input[] = array(
                'name'    => __('文章格式', 'zibll'),
                'id'      => 'post_format',
                'std'     => get_post_format($post_id),
                'type'    => 'select',
                'options' => array(
                    'standard' => __('标准', 'zibll'),
                    'image'    => __('图像', 'zibll'),
                    'gallery'  => __('画廊', 'zibll'),
                    'video'    => __('视频', 'zibll'),
                ),
            );
            $page_input[] = array(
                'name' => __('点赞数', 'zibll'),
                'id'   => 'like',
                'std'  => get_post_meta($post_id, 'like', true),
                'type' => 'number',
            );
            $page_input[] = array(
                'name' => __('阅读数', 'zibll'),
                'id'   => 'views',
                'std'  => get_post_meta($post_id, 'views', true),
                'type' => 'number',
            );
            $page_input[] = array(
                'name' => __('目录树', 'zibll'),
                'id'   => 'no_article-navs',
                'std'  => zib_get_post_meta($post_id, 'no_article-navs', true),
                'type' => 'checkbox',
                'desc' => __('不显示', 'zibll'),
            );
            $page_input[] = array(
                'name' => __('文章高度', 'zibll'),
                'id'   => 'article_maxheight_xz',
                'std'  => zib_get_post_meta($post_id, 'article_maxheight_xz', true),
                'type' => 'checkbox',
                'desc' => __('限制文章最大高度', 'zibll'),
            );
        }
        $page_input[] = array(
            'name'    => __('评论', 'zibll'),
            'id'      => 'comments_open',
            'std'     => comments_open($post_id) ? 'open' : '',
            'type'    => 'radio',
            'options' => array(
                'open' => __('开启', 'zibll'),
                ''     => __('关闭', 'zibll'),
            ),
        );
        if (is_page()) {
            $page_input[] = array(
                'name'    => __('标题样式', 'zibll'),
                'id'      => 'page_header_style',
                'std'     => zib_get_post_meta($post_id, 'page_header_style', true),
                'type'    => 'radio',
                'options' => array(
                    ''    => __('跟随主题', 'zibll'),
                    'not' => __('不显示', 'zibll'),
                    1     => __('简单样式', 'zibll'),
                    2     => __('卡片样式', 'zibll'),
                    3     => __('图文样式', 'zibll'),
                ),
            );
            $page_input[] = array(
                'name'    => __('内容样式', 'zibll'),
                'id'      => 'page_content_style',
                'std'     => zib_get_post_meta($post_id, 'page_content_style', true),
                'type'    => 'radio',
                'options' => array(
                    ''      => __('默认', 'zibll'),
                    'not'   => __('不显示', 'zibll'),
                    'nobox' => __('无背景', 'zibll'),
                    'full'  => __('全屏无背景', 'zibll'),
                ),
            );
            $page_input[] = array(
                'name' => __('布局宽度', 'zibll'),
                'id'   => 'layout_max_width',
                'std'  => (int) zib_get_post_meta($post_id, 'layout_max_width', 0),
                'type' => 'number',
                'desc' => __('为0时则以主题设置为准（不能低于1200）', 'zibll'),
            );

            $page_input[] = array(
                'name'    => '模块布局',
                'id'      => 'widgets_register',
                'type'    => 'radio',
                'options' => array(
                    '1' => __('开启', 'zibll'),
                    '0' => __('关闭', 'zibll'),
                ),
                'label'   => '为该页面创建小工具容器',
                'std'     => get_post_meta($post_id, 'widgets_register', true),
            );
            $page_input[] = array(
                'dependency' => array('widgets_register', '!=', ''),
                'id'         => 'widgets_register_container',
                'type'       => 'checkbox',
                'class'      => 'compact',
                'std'        => (array) get_post_meta($post_id, 'widgets_register_container', true),
                'name'       => '创建容器位置',
                'desc'       => '请根据需要合理开启，如果用不到则不要开启<br>保存页面后即可进入小工具或模块配置添加模块<div class="c-yellow">注意：开启此功能的页面不能太多，太多会影响性能，建议控制在10个以内</div>',
                'options'    => array(
                    'sidebar'        => __('侧边栏', 'zibll'),
                    'top_fluid'      => __('顶部全宽度', 'zibll'),
                    'top_content'    => __('主内容上面', 'zibll'),
                    'bottom_content' => __('主内容下面', 'zibll'),
                    'bottom_fluid'   => __('底部全宽度', 'zibll'),
                ),
            );
        }

    } else {
        $term_data = get_term($post_id, $taxonomy);

        $page_input[] = array(
            'name' => __('名称', 'zibll'),
            'id'   => 'term_name',
            'std'  => $term_data->name,
            'type' => 'text',
        );
        $page_input[] = array(
            'name' => __('描述', 'zibll'),
            'id'   => 'term_description',
            'std'  => $term_data->description,
            'type' => 'text',
        );
    }

    /**添加挂钩 */
    $page_input = apply_filters('zib_frontend_set_input_array', $page_input, $post_id, $type, $taxonomy, $post_type);
    $input      = zib_edit_input_construct($page_input);
    $input      = apply_filters('zib_frontend_set_input_html', $input, $post_id, $type, $taxonomy, $post_type);

    return $input;
}

// AJAX-前台编辑保存
function zib_frontend_set_save_ajax()
{
    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (empty($_POST['id'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '页面数据出错')));
        exit();
    }

    if (!is_super_admin() || !_pz('admin_frontend_set', true)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
        exit();
    }

    $type = $_POST['type'];
    if ($type === 'tax') {
        $taxonomy    = $_POST['taxonomy'];
        $object_data = get_term($_POST['id'], $taxonomy);
        if (!$object_data) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '未找到可用term数据')));
            exit();
        }
    } else {
        $object_data = get_post($_POST['id']);
        if (!$object_data) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '未找到可用post数据')));
            exit();
        }
    }

    /**添加执行挂钩 */
    do_action('zib_frontend_set_save', $object_data, $type);
    echo(json_encode(array('msg' => '保存成功，正在刷新页面', 'reload' => true)));
    exit();
}
add_action('wp_ajax_frontend_set_save', 'zib_frontend_set_save_ajax');

function zib_frontend_set_save($object_data, $type)
{
    if ($type === 'tax') {

        $term_id          = $object_data->term_id;
        $term_name        = isset($_POST['term_name']) ? $_POST['term_name'] : '';
        $term_description = isset($_POST['term_description']) ? $_POST['term_description'] : '';

        $term_data = array(
            'name'        => $term_name,
            'description' => $term_description,
        );

        $in_id = wp_update_term($term_id, $object_data->taxonomy, $term_data);
        if (is_wp_error($in_id)) {
            echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $in_id->get_error_message())));
            exit();
        }

    } else {
        $post_id = $object_data->ID;
        /**update_post_meta的保存 */
        $update_post_meta = array('like', 'subtitle', 'views', 'show_layout', 'page_header_style', 'page_content_style','layout_max_width', 'widgets_register', 'widgets_register_container');
        if (isset($_POST['widgets_register']) && !isset($_POST['widgets_register_container'])) {
            $_POST['widgets_register_container'] = array();
        }

        foreach ($update_post_meta as $meta) {
            if (isset($_POST[$meta])) {
                zib_update_post_meta($post_id, $meta, $_POST[$meta]);
            }
        }

        /**checkbox的保存 */
        $update_post_meta_checkbox = array('article_maxheight_xz', 'no_article-navs', 'page_show_header');
        foreach ($update_post_meta_checkbox as $meta) {
            $v = empty($_POST[$meta]) ? '' : '1';
            zib_update_post_meta($post_id, $meta, $v);
        }

        /**post_info的保存 */
        if (isset($_POST['post_format'])) {
            set_post_format($post_id, $_POST['post_format']);
        }

        if (isset($_POST['post_title']) || isset($_POST['comments_open'])) {
            $postarr = array(
                'ID' => $post_id,
            );

            if (isset($_POST['post_title'])) {
                $postarr['post_title'] = $_POST['post_title'];
            }

            if (isset($_POST['comments_open'])) {
                $postarr['comment_status'] = empty($_POST['comments_open']) ? '' : 'open';
            }

            $in_id = wp_update_post($postarr, 1);
            if (is_wp_error($in_id)) {
                echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $in_id->get_error_message())));
                exit();
            }
        }
    }

    return;
}
add_action('zib_frontend_set_save', 'zib_frontend_set_save', 10, 2);

/**链接列表页面模板设置 */
function zib_frontend_set_input_array_download($page_input)
{
    $post_id = get_queried_object_id();
    if (is_page_template('pages/download.php')) {
        $page_input[] = array(
            'name'  => __('页面内容', 'zibll'),
            'class' => 'op-multicheck',
            'id'    => 'page_show_content',
            'std'   => zib_get_post_meta($post_id, 'page_show_content', true),
            'desc'  => __('显示页面内容', 'zibll'),
            'type'  => 'checkbox',
        );
    }
    return $page_input;
}
add_filter('zib_frontend_set_input_array', 'zib_frontend_set_input_array_download');

/**input框架构建函数 */
function zib_edit_input_construct($input)
{
    /**完整示例 */
    $Examples[] = array(
        'name'        => '显示名称',
        'id'          => 'Examples_id',
        'class'       => 'class',
        'question'    => 'question',
        'type'        => 'checkbox',
        'html'        => '<div>html</div>',
        'value'       => false,
        'std'         => false,
        'desc'        => 'desc',
        'placeholder' => 'placeholder',
        'options'     => array(
            'enlighter'  => __('默认浅色主题'),
            'bootstrap4' => __('浅色：Bootstrap'),
        ),
        'settings'    => array(
            'rows' => 3,
        ),
    );
    $output = '';
    foreach ($input as $meta) {
        $value_id    = isset($meta['id']) ? $meta['id'] : '';
        $std         = isset($meta['std']) ? $meta['std'] : '';
        $class       = isset($meta['class']) ? $meta['class'] : '';
        $question    = isset($meta['question']) ? $meta['question'] : '';
        $type        = isset($meta['type']) ? $meta['type'] : '';
        $placeholder = isset($meta['placeholder']) ? $meta['placeholder'] : '';
        $value       = '';
        $value       = isset($meta['value']) ? $meta['value'] : $std;
        $style       = isset($meta['style']) ? ' style="' . $meta['style'] . '"' : '';
        $class       = '';
        if (isset($meta['type'])) {
            $class .= ' option-' . $meta['type'];
        }
        if (isset($meta['class'])) {
            $class .= ' ' . $meta['class'];
        }
        $output .= '<div class="mb10 row ' . $class . '"' . $style . '>' . "\n";

        $output .= '<div class="heading col-xs-3 text-right">' . (isset($meta['name']) ? esc_html($meta['name']) : '') . '</div>' . "\n";

        $output .= '<div class="option col-xs-8">' . "\n";
        //echo json_encode($meta);
        switch ($type) {

            // Basic text input
            case 'text':
                $output .= '<input class="form-control" name="' . $value_id . '" type="text" value="' . esc_attr($value) . '"/>';
                break;

            // Password input
            case 'password':
                $output .= '<input class="form-control" name="' . $value_id . '" type="password" value="' . esc_attr($value) . '"/>';
                break;

            case 'html':
                $output .= $meta['html'];
                break;

            case 'number':
                $output .= '<input class="form-control" name="' . $value_id . '" type="number" value="' . esc_attr($value) . '"/>';
                break;

            case 'checkbox':
                $options = isset($meta['options']) ? $meta['options'] : array();
                if (!empty($options) && is_array($options)) {

                    foreach ($options as $key => $option) {
                        if (is_array($value)) {
                            $checked = in_array($key, $value) ? ' checked' : '';
                        } else {
                            $checked = $value == $key ? ' checked' : '';
                        }
                        $output .= '<span class="form-checkbox"><input name="' . $value_id . '[]" id="' . $value_id . $key . '" type="checkbox" ' . $checked . ' value="' . $key . '"/><label for="' . $value_id . $key . '" class="em09 muted-color ml6 mr10" style=" font-weight: normal; ">' . $option . '</label></span>';
                    }
                } else {
                    $output .= '<span class="form-checkbox"><input $value="' . $value . '" name="' . $value_id . '" id="' . $value_id . '" type="checkbox" ' . zib_checked($value, 1, false) . '/><label for="' . $value_id . '" class="em09 muted-color ml6" style=" font-weight: normal; ">' . ($meta['desc'] ?? '') . '</label></span>';
                }

                break;

            // Textarea
            case 'textarea':
                $rows = '4';

                if (isset($meta['settings']['rows'])) {
                    $custom_rows = $meta['settings']['rows'];
                    if (is_numeric($custom_rows)) {
                        $rows = $custom_rows;
                    }
                }

                $value = stripslashes($value);
                $output .= '<textarea class="form-control" name="' . $value_id . '" rows="' . $rows . '"' . $placeholder . '>' . esc_textarea($value) . '</textarea>';
                break;

            // Select Box
            case 'select':
                $output .= '<div class="form-select"><select class="form-control" name="' . $value_id . '">';

                foreach ($meta['options'] as $key => $option) {
                    $output .= '<option' . selected($value, $key, false) . ' value="' . esc_attr($key) . '">' . esc_html($option) . '</option>';
                }
                $output .= '</select></div>';
                break;

            // Radio Box
            case 'radio':
                foreach ($meta['options'] as $key => $option) {
                    $output .= '<label class="mr10"><input type="radio" name="' . $value_id . '" value="' . esc_attr($key) . '" ' . checked($value, $key, false) . ' /><span class="ml6 em09 muted-color" style=" font-weight: normal; ">' . esc_html($option) . '</span></label>';
                }
                break;
        }

        if (!empty($meta['desc']) && $type != 'checkbox') {
            $desc = $meta['desc'];
            $output .= '<div class="em09 muted-2-color">' . $desc . '</div>' . "\n";
        }

        if ($question) {
            $output .= '<span class="ml10" data-toggle="tooltip" title="' . esc_attr($question) . '"><i class="fa fa-question-circle c-red" aria-hidden="true"></i></span>' . "\n";
        }

        $output .= '</div>' . "\n";
        $output .= '</div>' . "\n";
    }

    return $output;
    // echo  json_encode( $get_mate);
    //  echo json_encode( $this->args);
}

function zib_checked($value = '', $key = '1', $echo = 1)
{
    $checked = array('on', '1', $key);
    $html    = '';
    if (in_array($value, $checked)) {
        $html = ' checked="checked"';
    }

    if ($echo) {
        echo $html;
    }

    return $html;
}
