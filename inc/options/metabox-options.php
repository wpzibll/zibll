<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:41:45
 * @LastEditTime : 2026-04-29 20:49:33
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台文章编辑配置项，仅在后天引用
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//链接列表模板
function zib_cfs_link_category()
{
    $options_linkcats     = array();
    $options_linkcats[0]  = '全部选择';
    $options_linkcats_obj = get_terms(['taxonomy' => 'link_category'], ['hide_empty' => false]);
    foreach ($options_linkcats_obj as $tag) {
        $options_linkcats[$tag->term_id] = $tag->name;
    }
    return $options_linkcats;
}

//页面模板-配置项
function zib_meta_box_page_templates_meta($post)
{

    $saved_template = (is_object($post) && !empty($post->page_template)) ? $post->page_template : 'default';

    $fields = array();

    //文档导航页面模板
    if ($saved_template === 'pages/documentnav.php') {
        $fields = array_merge($fields, array(
            array(
                'type'    => 'submessage',
                'style'   => 'info',
                'content' => '<b>文档导航页面模板：</b><br>选择一个一级分类（选择的一级分类下必须要有一些子分类），用于此页面的内容显示，系统会自动获取该分类下的二级分类以及文章，适合用作产品文档、帮助文档等页面<br><a target="_blank" href="https://www.zibll.com/%e4%b8%bb%e9%a2%98%e6%96%87%e6%a1%a3">查看演示</a>',
            ),
            array(
                'title'  => ' ',
                'id'     => 'documentnav_options',
                'type'   => 'fieldset',
                'fields' => array(
                    array(
                        'id'          => 'cat',
                        'title'       => '选择分类',
                        'default'     => '',
                        'options'     => 'categories',
                        'placeholder' => '选择分类',
                        'subtitle'    => '请选择一个一级分类',
                        'type'        => 'select',
                    ),
                    array(
                        'id'      => 'initial_content',
                        'title'   => '初始内容',
                        'default' => 'updated_posts',
                        'type'    => 'select',
                        'options' => array(
                            'page_content'  => __('显示页面内容'),
                            'date_posts'    => __('最近发布文章'),
                            'updated_posts' => __('最近更新文章'),
                            'views_posts'   => __('查看最多文章'),
                        ),
                    ),
                )),
        ));
    } elseif ($saved_template === 'pages/links.php') {
        $fields = array_merge($fields, array(
            array(
                'type'    => 'submessage',
                'style'   => 'info',
                'content' => '<b>网址导航页面模板：</b>用于显示链接的页面，支持链接提交，可用于创建‘友情链接’、‘网址导航’等页面 <br><a target="_blank" href="https://www.zibll.com/951.html">查看教程</a>',
            ),
            array(
                'title'   => __('显示页面内容', 'zibll'),
                'id'      => 'page_links_content_s',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('page_links_content_s', '!=', ''),
                'id'         => 'page_links_content_position',
                'title'      => ' ',
                'subtitle'   => '显示位置',
                'default'    => 'top',
                'class'      => 'compact',
                'inline'     => true,
                'type'       => 'radio',
                'options'    => array(
                    'top'    => __('链接列表上面'),
                    'bottom' => __('链接列表下面'),
                ),
            ),
            array(
                'title'   => __('显示搜索引擎模块', 'zibll'),
                'id'      => 'page_links_search_s',
                'type'    => 'switcher',
                'default' => false,
            ),

            array(
                'dependency'  => array('page_links_search_s', '!=', ''),
                'id'          => 'page_links_search_types',
                'title'       => ' ',
                'subtitle'    => '选择显示的搜索引擎类型',
                'placeholder' => '选择显示的搜索引擎类型',
                'desc'        => '启用后，会在页面上方显示搜索引擎搜索框，用户可以直接在页面上搜索，支持多选，支持拖动排序（至少选择一个）<br>启用后会调用当前页面的特色图片作为搜索背景图',
                'default'     => ['baidu', 'bing', 'sogou', '360'],
                'type'        => 'select',
                'class'       => 'compact',
                'options'     => array(
                    'self'   => '站内',
                    'baidu'  => '百度',
                    'google' => '谷歌',
                    'bing'   => '必应',
                    'sogou'  => '搜狗',
                    '360'    => '360',
                ),
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
            ),
            array(
                'id'          => 'page_links_category',
                'title'       => '选择显示分类(必填)',
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
                'desc'        => '选择要显示的链接分类，支持多选，支持拖动排序<br>当选择多个分类的时候，会以网址导航的形式显示，一个都不选则不会显示任何链接，当然你可以开启模块布局后添加其它小工具模块'
            ),
            array(
                'id'       => 'page_links_style',
                'title'    => '显示样式',
                'subtitle' => ' ',
                'default'  => 'card',
                'inline'   => true,
                'type'     => 'radio',
                'options'  => array(
                    'card'    => __('图文列表'),
                    'bigcard' => __('大卡片'),
                    'image'   => __('纯图片'),
                ),
            ),
            array(
                'title'   => '限制数量',
                'id'      => 'page_links_limit',
                'default' => 0,
                'type'    => 'spinner',
                'min'     => 0,
                'step'    => 5,
                'unit'    => '个',
                'desc'    => '每个分类最多显示多少个链接，填0则为不限制',
            ),
            array(
                'id'      => 'page_links_orderby',
                'title'   => '排序方式',
                'default' => 'name',
                'type'    => 'select',
                'options' => array(
                    'name'    => __('名称排序'),
                    'updated' => __('更新时间'),
                    'rating'  => __('链接评分'),
                    'rand'    => __('随机排序'),
                ),
            ),
            array(
                'id'       => 'page_links_order',
                'title'    => ' ',
                'subtitle' => ' ',
                'default'  => 'ASC',
                'class'    => 'compact',
                'inline'   => true,
                'type'     => 'radio',
                'options'  => array(
                    'ASC'  => __('升序'),
                    'DESC' => __('降序'),
                ),
            ),
            array(
                'title'   => __('外链重定向', 'zibll'),
                'id'      => 'page_links_go_s',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('新标签页打开', 'zibll'),
                'id'      => 'page_links_blank_s',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => __('添加nofollow标记', 'zibll'),
                'desc'    => __('nofollow标记用于告知搜索引擎建议不抓取，一般友情链接建议关闭', 'zibll'),
                'id'      => 'page_links_nofollow_s',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'title'   => __('显示提交链接模块', 'zibll'),
                'id'      => 'page_links_submit_s',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('page_links_submit_s', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('登录后才能提交', 'zibll'),
                'id'         => 'page_links_submit_sign_s',
                'type'       => 'switcher',
                'class'      => 'compact',
                'default'    => true,
            ),
            array(
                'dependency'  => array('page_links_submit_s', '!=', ''),
                'id'          => 'page_links_submit_cats',
                'title'       => ' ',
                'subtitle'    => '提交时允许选择的分类',
                'placeholder' => '选择分类',
                'class'       => 'compact',
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
                'desc'        => '选择用户提交链接时可以选择的分类，留空则会展示全部分类',
            ),
            array(
                'dependency' => array('page_links_submit_s', '!=', ''),
                'title'      => ' ',
                'subtitle'   => '提交链接模块：标题',
                'id'         => 'page_links_submit_title',
                'class'      => 'compact',
                'default'    => '申请入驻',
                'type'       => 'text',
            ),
            array(
                'dependency' => array('page_links_submit_s', '!=', ''),
                'id'         => 'page_links_submit_dec',
                'title'      => ' ',
                'subtitle'   => '提交链接模块：提交说明',
                'class'      => 'compact',
                'default'    => '<div>
    <li>您的网站已稳定运行，且有一定的文章量 </li>
    <li>原创、技术、设计类网站优先考虑</li>
    <li>不收录有反动、色情、赌博等不良内容或提供不良内容链接的网站</li>
    <li>您需要将本站链接放置在您的网站中</li>
    <li>请选择正方形的LOGO图像</li>
</div>',
                'attributes' => array(
                    'rows' => 6,
                ),
                'sanitize'   => false,
                'type'       => 'textarea',
            ),
        ));
    } else {
        $fields = array(
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '如果您选择了页面模板，部分模板有相关配置选项在此处进行配置<br>请先选一个页面模板，保存后，刷新页面后再查看此处的配置项',
            ),
        );
    }

    $value = array();
    if (!empty($post->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('post_meta');
        $zib_meta         = get_post_meta($post->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_post_meta($post->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }

    $csf_args = array(
        'class'  => '',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );

    ZCSF::instance('post_meta', $csf_args);
}

function zib_save_meta_box_page_templates_meta($post_id)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    $fields = array(
        'documentnav_options',
        'page_links_content_s',
        'page_links_content_position',
        'page_links_orderby',
        'page_links_order',
        'page_links_limit',
        'page_links_search_s',
        'page_links_search_types',
        'page_links_category',
        'page_links_style',
        'page_links_submit_s',
        'page_links_submit_sign_s',
        'page_links_submit_cats',
        'page_links_submit_title',
        'page_links_submit_dec',
        'page_links_go_s',
        'page_links_blank_s',
        'page_links_nofollow_s',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}

function zib_add_meta_box_page_templates_meta()
{
    add_meta_box('page_templates', '页面模板配置', 'zib_meta_box_page_templates_meta', array('page'), 'advanced', 'high');
}

add_action('add_meta_boxes', 'zib_add_meta_box_page_templates_meta');
add_action('save_post', 'zib_save_meta_box_page_templates_meta');

//高级筛选
function zib_admin_custom_filter_csf()
{
    if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') !== false || strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false) {
        CSF::createMetabox('custom_filter', array(
            'title'     => '高级自定义筛选',
            'post_type' => array('post'),
            'context'   => 'side',
            'data_type' => 'unserialize',
        ));
        CSF::createSection('custom_filter', array(
            'fields' => post_custom_filter::csf_fields(),
        ));
    }
}
add_action('zib_require_end', 'zib_admin_custom_filter_csf');

/**
 * @description: 一个构建后台列表批量编辑的input
 * @param {*}
 * @return {*}
 */
function zib_get_quick_edit_custom_input($fields_args, $id, $title = '', $desc = '')
{

    $item_html = '';
    foreach ($fields_args as $fields) {
        $options_html = ' ';
        switch ($fields['type']) {

            case 'number':
                $options_html = '<div class="flex ac hh">
                                    <select name="zib_bulk_edit[' . $id . '][' . $fields['id'] . '][operation]">
                                            <option value="ignore" selected="selected">不更改</option>
                                            <option value="set">设置为</option>
                                            <option value="plus">加</option>
                                            <option value="subtract">减</option>
                                            <option value="multiply">乘</option>
                                            <option value="division">除</option>
                                    </select>
                                    <input type="text" name="zib_bulk_edit[' . $id . '][' . $fields['id'] . '][val]" value="">
                                </div>';
                break;

            case 'checkbox':
                $select_options = !empty($fields['options']) ? $fields['options'] : array();
                if ($select_options) {
                    $select_options_html = '';
                    foreach ($select_options as $key => $value) {
                        $select_options_html .= '<div class="mr10"><label><input value="' . $key . '" type="checkbox" name="zib_bulk_edit[' . $id . '][' . $fields['id'] . '][val][]">' . $value . '</label></div>';
                    }
                    $options_html = '<div class="flex ac hh zib-bulk-checkbox">
                                        <select class="mr10" name="zib_bulk_edit[' . $id . '][' . $fields['id'] . '][operation]">
                                            <option value="ignore" selected="selected">不更改</option>
                                            <option value="set">设置为</option>
                                    </select>
                    ' . $select_options_html . '</div>';
                    break;
                } else {
                    $fields['options'] = array(
                        'ignore' => '不更改',
                        '1'      => '开启',
                        '0'      => '关闭',
                    );
                }

            case 'switcher':
                $fields['options'] = array(
                    'ignore' => '不更改',
                    '1'      => '开启',
                    '0'      => '关闭',
                );

            case 'radio':
            case 'select':
                $select_options      = $fields['options'];
                $select_options_html = '<option selected="selected" value="ignore">--不更改--</option>';
                foreach ($select_options as $key => $value) {
                    $select_options_html .= '<option value="' . $key . '">' . esc_attr($value) . '</option>';
                }
                $options_html = '<div class="flex ac hh zib-bulk-select"><select name="zib_bulk_edit[' . $id . '][' . $fields['id'] . ']">' . $select_options_html . '</select></div>';
                break;
        }

        if ($options_html) {
            $fields_title = !empty($fields['title']) ? $fields['title'] : (!empty($fields['label']) ? $fields['label'] : '');
            $fields_desc  = !empty($fields['desc']) ? '<div class="px12 zib-bulk-edit-desc">' . $fields['desc'] . '</div>' : '';

            $item_html .= '<div class="zib-bulk-edit-box">
                                <div>' . $fields_title . '</div>
                                ' . $options_html . $fields_desc . '
                            </div>';
        }

    }

    $title = $title ? '<legend class="inline-edit-legend">' . $title . '</legend>' : '';

    return $item_html ? '<fieldset style="margin-top: 20px">' . $title . '<div class="flex hh">' . $item_html . '</div>' . $desc . '</fieldset>' : '';
}

//高级筛选批量编辑和快速编辑
add_action('bulk_edit_custom_box', array('post_custom_filter', 'bulk_edit_custom_box'), 10, 2);
add_action('quick_edit_custom_box', array('post_custom_filter', 'bulk_edit_custom_box'), 10, 2);
add_action('save_post', array('post_custom_filter', 'bulk_save_post'), 10, 3);

class post_custom_filter
{

    public static function csf_fields()
    {
        $fields = array();

        $opts = _pz('custom_filter');
        if ($opts && is_array($opts)) {
            foreach ($opts as $opt) {
                if ($opt['key']) {

                    $options = array();
                    foreach ($opt['vals'] as $val) {
                        if ($val['key']) {
                            $name                 = $val['name'] ?: $val['key'];
                            $options[$val['key']] = $name;
                        }
                    }

                    if ($options) {
                        $fields[] = array(
                            'id'          => $opt['key'],
                            'title'       => $opt['name'] ?: $opt['key'],
                            'options'     => $options,
                            'placeholder' => '请选择' . $opt['name'] ?: $opt['key'],
                            'chosen'      => true,
                            'multiple'    => true,
                            'type'        => 'select',
                        );
                    }

                }
            }
        }

        if ($fields) {
            $fields = array_merge(array(array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '高级筛选：让文章实现更加精细化的分类，同时方便用户进行文章筛选
                <br><a target="_blank" href="' . zib_get_admin_csf_url('文章&列表/高级筛选') . '">管理高级筛选明细</a>',
            )), $fields);
        } else {
            $fields = array_merge(array(array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '高级筛选：让文章实现更加精细化的分类，同时方便用户进行文章筛选
                <div class="c-yellow">您暂未添加高级筛选明细，请先点击下方链接进行配置</div>
                <a target="_blank" href="' . zib_get_admin_csf_url('文章&列表/高级筛选') . '">添加高级筛选明细</a>',
            )), $fields);
        }

        return $fields;
    }

    /**
     * @description: 后台添加批量修改帖子参数的选项
     * @param {*} $column_name
     * @param {*} $post_type
     * @return {*}
     */
    public static function bulk_edit_custom_box($column_name, $post_type)
    {
        if ($post_type === 'post') {
            if ($column_name === 'taxonomy-topics') {

                echo self::edit_select();
            }
        }
    }

    public static function bulk_save_post($post_ID, $post, $update)
    {
        if (!$update || $post->post_type !== 'post' || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== 'edit-post') {
            return;
        }

        $opts = _pz('custom_filter');
        if ($opts && is_array($opts)) {
            foreach ($opts as $opt) {
                if ($opt['key'] && !empty($_REQUEST[$opt['key']])) {
                    update_post_meta($post_ID, $opt['key'], $_REQUEST[$opt['key']]);
                }
            }
        }

    }

    public static function edit_select()
    {
        $box = '';

        $opts = _pz('custom_filter');
        if ($opts && is_array($opts)) {
            foreach ($opts as $opt) {
                if ($opt['key']) {

                    $options = '';
                    foreach ($opt['vals'] as $val) {
                        if ($val['key']) {
                            $name = $val['name'] ?: $val['key'];
                            $options .= '<li class="popular-category"><label class="selectit  but but-sm" style="margin: 2px 5px;"><input value="' . $val['key'] . '" type="checkbox" name="' . $opt['key'] . '[]"> ' . $name . '</label></li>';
                        }
                    }

                    $box .= '<div class="inline-edit-col">
                                <span class="title inline-edit-categories-label">高级筛选：' . ($opt['name'] ?: $opt['key']) . '</span>
                                <ul class="cat-checklist category-checklist flex ac hh" style="height: auto;">' . $options . '</ul>
                            </div>';

                }
            }
        }

        return $box ? '<fieldset style="border: 1px solid #dbdbdb;padding: 0 10px;" class="inline-edit-col-left inline-edit-custom-filter">' . $box . '</fieldset>' : '';
    }

    public static function filters_options()
    {

        $opts    = _pz('custom_filter');
        $options = array();

        if ($opts && is_array($opts)) {
            foreach ($opts as $opt) {
                if ($opt['key']) {
                    $options[$opt['key']] = $opt['name'] ?: $opt['key'];

                }
            }
        }

        return $options;
    }

}

//批量编辑
add_action('bulk_edit_custom_box', array('zib_post_bulk_edit', 'edit_box'), 10, 2);
add_action('quick_edit_custom_box', array('zib_post_bulk_edit', 'edit_box'), 10, 2);
add_action('save_post', array('zib_post_bulk_edit', 'save'), 10, 3);
class zib_post_bulk_edit
{
    public static $permissible_posts_type = ['post'];
    public static $column_name            = 'taxonomy-topics';
    public static $bulk_id                = 'extended';

    public static function edit_box($column_name, $post_type)
    {
        if (!in_array($post_type, self::$permissible_posts_type) || $column_name !== self::$column_name) {
            return;
        }
        $fields_args = array(
            array(
                'id'    => 'views',
                'type'  => 'number',
                'title' => '阅读量',
            ),
            array(
                'id'    => 'like',
                'type'  => 'number',
                'title' => '点赞数',
            ),
            array(
                'id'      => 'show_layout',
                'type'    => 'radio',
                'title'   => '显示布局',
                'default' => 'false',
                'options' => array(
                    'false'         => '跟随主题',
                    'no_sidebar'    => '无侧边栏',
                    'sidebar_left'  => '侧边栏靠左',
                    'sidebar_right' => '侧边栏靠右',
                ),
            ),
            array(
                'id'    => 'no_article-navs',
                'type'  => 'checkbox',
                'label' => '不显示目录树',
            ),
            array(
                'id'    => 'article_maxheight_xz',
                'type'  => 'checkbox',
                'label' => '限制内容最大高度',
            ),
        );

        echo zib_get_quick_edit_custom_input($fields_args, self::$bulk_id);

    }

    public static function save($post_id, $post, $update)
    {
        if (!$update || empty($_REQUEST['zib_bulk_edit'][self::$bulk_id]) || !in_array($post->post_type, self::$permissible_posts_type) || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== 'edit-post') {
            return;
        }

        $zibpay_bulk_edit = $_REQUEST['zib_bulk_edit'][self::$bulk_id];
        foreach ($zibpay_bulk_edit as $field_id => $field_value) {
            switch ($field_id) {
                case 'views':
                case 'like':
                    $operation = $field_value['operation'];
                    if ($operation !== 'ignore' && is_numeric($field_value['val'])) {

                        $old_val = get_post_meta($post_id, $field_id, true);
                        $val     = (float) $field_value['val'];

                        switch ($operation) {
                            case 'set': //统一设置为
                                $new_val = $val;
                                break;
                            case 'plus':
                                $new_val = round($old_val + $val, 2);
                                break;
                            case 'subtract':
                                $new_val = round($old_val - $val, 2);
                                break;
                            case 'multiply':
                                $new_val = round($old_val * $val, 2);
                                break;
                            case 'division':
                                if ($val != 0 && $old_val != 0) {
                                    $new_val = round($old_val / $val, 2);
                                }
                                break;
                        }
                        $new_val = (int) $new_val;
                        $new_val = $new_val < 0 ? 0 : $new_val;
                        update_post_meta($post_id, $field_id, $new_val);
                    }

                    break;
                case 'show_layout':
                case 'no_article-navs':
                case 'article_maxheight_xz':

                    if ($field_value !== 'ignore') {
                        zib_update_post_meta($post_id, $field_id, $field_value);
                    }

                    break;
            }
        }

    }
}

//文章 - 扩展
function zib_meta_box_post_main_meta($post)
{
    $fields = array(
        array(
            'title'   => '外链特色图像',
            'id'      => 'thumbnail_url',
            'library' => 'image',
            'type'    => 'upload',
            'default' => false,
            'desc'    => '支持直接输入链接用作特色图像<br/><span style="color:#ff4646;">此处设置仅在未设置wp特色图片时有效</span>',
        ),
    );
    if (_pz('article_image_cover')) {
        $fields = array_merge($fields, array(
            array(
                'title'   => '封面图',
                'id'      => 'cover_image',
                'library' => 'image',
                'type'    => 'upload',
                'default' => false,
                'desc'    => '在文章页顶部显示封面图',
            ),
        ));
    }

    if (_pz('list_thumb_slides_s') || _pz('article_slide_cover')) {
        $fields = array_merge($fields, array(
            array(
                'title'       => '特色幻灯片',
                'id'          => 'featured_slide',
                'type'        => 'gallery',
                'add_title'   => '添加图像',
                'edit_title'  => '编辑图像',
                'clear_title' => '清空图像',
                'default'     => false,
                'desc'        => '为该文章显示幻灯片封面或幻灯片略图（优先级>特色图像及封面图）',
            ),
        ));
    }

    if (_pz('list_thumb_video_s') || _pz('article_video_cover')) {
        $fields = array_merge($fields, array(
            array(
                'title'   => '特色视频',
                'id'      => 'featured_video',
                'type'    => 'upload',
                'preview' => false,
                'library' => 'video',
                'default' => false,
                'desc'    => '为该文章显示视频封面（优先级>幻灯片封面）',
            ),
            array(
                'dependency'  => array('featured_video', '!=', ''),
                'id'          => 'featured_video_title',
                'title'       => ' ',
                'subtitle'    => '本集标题',
                'desc'        => '如需添加剧集则需填写此处',
                'default'     => '',
                'placeholder' => '第1集',
                'class'       => 'compact',
                'type'        => 'text',
            ),
            array(
                'dependency'   => array('featured_video', '!=', '', '', 'visible'),
                'id'           => 'featured_video_episode',
                'type'         => 'group',
                'button_title' => '添加剧集',
                'class'        => 'compact',
                'title'        => '视频剧集',
                'subtitle'     => '为视频封面添加更多剧集',
                'default'      => array(),
                'fields'       => array(
                    array(
                        'id'       => 'title',
                        'title'    => ' ',
                        'subtitle' => '剧集标题',
                        'default'  => '',
                        'type'     => 'text',
                    ),
                    array(
                        'title'       => ' ',
                        'subtitle'    => '视频地址',
                        'id'          => 'url',
                        'class'       => 'compact',
                        'type'        => 'upload',
                        'preview'     => false,
                        'library'     => 'video',
                        'placeholder' => '选择视频或填写视频地址',
                        'default'     => false,
                    ),

                ),
            ),
            array(
                'id'    => 'subtitle',
                'type'  => 'text',
                'title' => '副标题',
            ),
            array(
                'id'       => 'views',
                'type'     => 'number',
                'title'    => '阅读量',
                'default'  => zib_get_mt_rand_number(_pz('post_default_mate', '', 'views')),
                'validate' => 'csf_validate_numeric',
            ),
            array(
                'id'       => 'like',
                'type'     => 'number',
                'title'    => '点赞数',
                'default'  => zib_get_mt_rand_number(_pz('post_default_mate', '', 'like')),
                'validate' => 'csf_validate_numeric',
            ),
            array(
                'id'      => 'show_layout',
                'type'    => 'radio',
                'title'   => '显示布局',
                'default' => 'false',
                'options' => array(
                    'false'         => '跟随主题',
                    'no_sidebar'    => '无侧边栏',
                    'sidebar_left'  => '侧边栏靠左',
                    'sidebar_right' => '侧边栏靠右',
                ),
            ),
            array(
                'id'    => 'no_article-navs',
                'type'  => 'checkbox',
                'label' => '不显示目录树',
            ),
            array(
                'id'    => 'article_maxheight_xz',
                'type'  => 'checkbox',
                'label' => '限制内容最大高度',
            ),
        ));
    }

    $value = array();
    if (!empty($post->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('post_meta');
        $zib_meta         = get_post_meta($post->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_post_meta($post->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }

    $csf_args = array(
        'class'  => '',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );

    ZCSF::instance('post_meta', $csf_args);
}

function zib_save_meta_box_post_main_meta($post_id)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    $fields = array(
        'thumbnail_url',
        'cover_image',
        'featured_slide',
        'featured_video',
        'featured_video_title',
        'featured_video_episode',
        'subtitle',
        'views',
        'like',
        'show_layout',
        'no_article-navs',
        'article_maxheight_xz',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}

function zib_add_meta_box_post_main_meta()
{
    add_meta_box('post_main', '文章扩展', 'zib_meta_box_post_main_meta', array('post'), 'side', 'high');
}
add_action('add_meta_boxes', 'zib_add_meta_box_post_main_meta');
add_action('save_post', 'zib_save_meta_box_post_main_meta');

//页面扩展
function zib_meta_box_page_main_meta($post)
{

    $fields = array(
        array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '当选择了页面模板后，部分模板以下配置可能会失效',
        ),
        array(
            'id'      => 'show_layout',
            'type'    => 'radio',
            'title'   => '显示布局',
            'default' => '',
            'options' => array(
                ''              => '跟随主题',
                'no_sidebar'    => '无侧边栏',
                'sidebar_left'  => '侧边栏靠左',
                'sidebar_right' => '侧边栏靠右',
            ),
        ),
        array(
            'id'      => 'page_header_style',
            'type'    => 'radio',
            'title'   => '标题样式',
            'default' => '',
            'options' => array(
                ''    => __('跟随主题', 'zibll'),
                'not' => __('不显示', 'zibll'),
                1     => __('简单样式', 'zibll'),
                2     => __('卡片样式', 'zibll'),
                3     => __('图文样式', 'zibll'),
            ),
        ),
        array(
            'id'      => 'page_content_style',
            'type'    => 'radio',
            'title'   => '内容样式',
            'desc'    => '全屏无背景样式通常用于全局使用HTML代码构建页面，属于高级用法，不建议新手使用',
            'default' => '',
            'options' => array(
                ''      => __('默认', 'zibll'),
                'not'   => __('不显示', 'zibll'),
                'nobox' => __('无背景', 'zibll'),
                'full'  => __('全屏无背景', 'zibll'),
            ),
        ),
        array(
            'id'      => 'layout_max_width',
            'title'   => '布局最大宽度',
            'desc'    => '为0时则以主题设置为准（不能低于1200）',
            'type'    => 'spinner',
            'default' => 0,
            'max'     => 3000,
            'min'     => 1200,
            'step'    => 50,
            'unit'    => 'px',
        ),
        array(
            'title' => '模块布局',
            'id'    => 'widgets_register',
            'type'  => 'switcher',
            'label' => '为该页面创建小工具容器',
        ),
        array(
            'dependency' => array('widgets_register', '!=', ''),
            'id'         => 'widgets_register_container',
            'type'       => 'checkbox',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => '创建容器位置',
            'desc'       => '请根据需要合理开启，如果用不到则不要开启<br>保存页面后即可进入小工具或模块配置添加模块<div class="c-yellow">注意：开启此功能的页面不能太多，太多会影响性能，建议控制在10个以内</div>',
            'options'    => array(
                'sidebar'        => __('侧边栏', 'zibll'),
                'top_fluid'      => __('顶部全宽度', 'zibll'),
                'top_content'    => __('主内容上面', 'zibll'),
                'bottom_content' => __('主内容下面', 'zibll'),
                'bottom_fluid'   => __('底部全宽度', 'zibll'),
            ),
        ));

    $value = array();
    if (!empty($post->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('post_meta');
        $zib_meta         = get_post_meta($post->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_post_meta($post->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }

    $csf_args = array(
        'class'  => '',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );

    ZCSF::instance('post_meta', $csf_args);
}

function zib_save_meta_box_page_main_meta($post_id)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    $fields = array(
        'show_layout',
        'page_header_style',
        'page_content_style',
        'layout_max_width',
        'widgets_register', //不能加入zib聚合
        'widgets_register_container', //不能加入zib聚合
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}

function zib_add_meta_box_page_main_meta()
{
    add_meta_box('post_main', '页面扩展', 'zib_meta_box_page_main_meta', array('page'), 'side', 'high');
}

add_action('add_meta_boxes', 'zib_add_meta_box_page_main_meta');
add_action('save_post', 'zib_save_meta_box_page_main_meta');

function zib_admin_baidu_resource_submission_csf()
{
    CSF::createMetabox('baidu_resource_submission', array(
        'title'     => '百度资源提交',
        'post_type' => array('post', 'page', 'plate', 'forum_post', 'shop_product'),
        'context'   => 'side',
        'data_type' => 'unserialize',
    ));
    CSF::createSection('baidu_resource_submission', array(
        'fields' => array(
            array(
                'title'   => __('百度资源提交', 'zibll'),
                'type'    => 'content',
                'content' => zib_get_baidu_resource_submission_metabox(),
            ),
        ),
    ));

    //为term添加百度资源提交
    CSF::createTaxonomyOptions('term_baidu_resource_submission', array(
        'title'     => '百度资源提交',
        'taxonomy'  => ['category', 'post_tag', 'topics', 'plate_cat', 'forum_topic', 'forum_tag', 'shop_cat', 'shop_tag', 'shop_discount'],
        'data_type' => 'unserialize',
    ));
    CSF::createSection('term_baidu_resource_submission', array(
        'fields' => array(
            array(
                'title'   => __('百度资源提交', 'zibll'),
                'type'    => 'content',
                'content' => zib_get_baidu_resource_submission_metabox(false),
            ),
        ),
    ));
}
if ((_pz('xzh_post_on') || _pz('xzh_post_daily_push')) && _pz('xzh_post_token')) {
    add_action('zib_require_end', 'zib_admin_baidu_resource_submission_csf');
}

function zib_get_baidu_resource_submission_metabox($is_post = true)
{
    if ($is_post) {
        if (isset($_GET['post'])) {
            $post_id = (int) $_GET['post'];
        } elseif (isset($_POST['post_ID'])) {
            $post_id = (int) $_POST['post_ID'];
        } else {
            $post_id = 0;
        }
        $tui = zib_get_post_meta($post_id, 'xzh_tui_back', true);
    } else {
        if (isset($_GET['tag_ID'])) {
            $term_id = (int) $_GET['tag_ID'];
        } else {
            $term_id = 0;
        }
        $tui = zib_get_term_meta($term_id, 'xzh_tui_back', true);
    }

    $Resubmit  = '';
    $show_text = '';
    if (!empty($tui['normal_push'])) {
        $show_text .= '<strong>普通收录：成功</strong> ' . json_encode($tui['normal_result']) . '<br>';
    } elseif (isset($tui['normal_push']) && false == $tui['normal_push']) {
        $show_text .= '<strong>普通收录：失败</strong> ' . json_encode($tui['normal_result']) . '<br>';
    }
    if (!empty($tui['daily_push'])) {
        $show_text .= '<strong>快速收录：成功</strong> ' . json_encode($tui['daily_result']) . '<br>';
    } elseif (isset($tui['daily_push']) && false == $tui['daily_push']) {
        $show_text .= '<strong>快速收录：失败</strong> ' . json_encode($tui['daily_result']) . '<br>';
    }
    if (!empty($tui['update_time'])) {
        $show_text .= '<strong>更新时间：</strong>' . $tui['update_time'] . '<br>';
        $Resubmit = '<span style="margin:0 20px 15px 0; display:inline-block;"><label><input type="checkbox" name="xzh_post_resubmit"> 重新提交</label></span>';
    }
    if (strstr(json_encode($tui), '成功') || strstr(json_encode($tui), '失败')) {
        $show_text .= json_encode($tui) . '<br>';
    }
    if ($show_text) {
        $show_text = '<div>提交结果:</div>' . $show_text;
    } else {
        $show_text = '发布、更新后刷新页面后可查看提交结果';
    }

    return $Resubmit . $show_text;
}

//文章、页面、帖子的独立seo
function zib_meta_box_seo_meta($post)
{

    $fields = array(
        array(
            'title'   => __('SEO预览', 'zibll'),
            'type'    => 'content',
            'content' => zib_get_seo_preview_box(),
        ),
        array(
            'title' => __('标题', 'zibll'),
            'id'    => 'title',
            'desc'  => 'Title 一般建议15到30个字符',
            'std'   => '',
            'type'  => 'text',
        ),
        array(
            'title' => __('关键词', 'zibll'),
            'id'    => 'keywords',
            'desc'  => 'Keywords 每个关键词用逗号隔开',
            'std'   => '',
            'type'  => 'text',
        ),
        array(
            'title' => __('描述', 'zibll'),
            'id'    => 'description',
            'desc'  => 'Description 一般建议50到150个字符',
            'std'   => '',
            'type'  => 'textarea',
        ),
        array(
            'type'       => 'accordion',
            'id'         => 'accordion',
            'accordions' => array(
                array(
                    'title'  => 'SEO优化建议',
                    'icon'   => 'fas fa-star',
                    'fields' => array(
                        array(
                            'title'   => ' ',
                            'type'    => 'content',
                            'content' => '<div style="color:#048cf0;margin-bottom:5px;">SEO标题优化建议：</div>
                        <li>主题默认会自动获取标题、副标题、网站名称作为SEO标题</li>
                        <li>标题内容应该紧扣页面的主要内容有吸引力</li>
                        <li>网站标题不要有过多的重复</li>
                        <li>第一个词放最重要的关键词</li>
                        <li>关键词只能重复2次，不要堆砌关键词</li>
                        <li>最后一个词放品牌词，不重要的词语</li>
                        <div style="color:#048cf0;margin-bottom:5px;margin-top:15px;">SEO关键词优化建议：</div>
                        <li>主题默认会自动获取分类及标签作为关键词，页面请单独自定义</li>
                        <li>关键词一般建议4到8个</li>
                        <li>尽量与网站定位一致</li>
                        <li>添加网站专属关键词</li>
                        <div style="color:#048cf0;margin-bottom:5px;margin-top:15px;">SEO描述优化建议：</div>
                        <li>主题默认会自动获取摘要、内容为SEO描述</li>
                        <li>description是对网页内容的精练概括</li>
                        <li>写成一段通顺有意义的话，要有吸引力</li>
                        <li>建议加入多个关键词，但不宜重复太多</li>
                        <div style="color:#f7497e;margin-bottom:5px;margin-top:15px;">优化建议来自互联网，仅供参考</div>',
                        ),
                    ),
                ),
            ),
        ),
    );

    $value = array();
    if (!empty($post->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('post_meta');
        $zib_meta         = get_post_meta($post->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_post_meta($post->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }

    $csf_args = array(
        'class'  => '',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );

    ZCSF::instance('post_meta', $csf_args);
}

function zib_save_meta_box_seo_meta($post_id)
{

    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    $fields = array(
        'title',
        'keywords',
        'description',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}

function zib_add_meta_box_seo_meta()
{
    $seo_meta_boxe_type = array('post', 'page', 'plate', 'forum_post', 'shop_product');
    add_meta_box('posts_seo', '独立SEO', 'zib_meta_box_seo_meta', $seo_meta_boxe_type, 'advanced', 'high');
}

if (_pz('post_keywords_description_s')) {
    add_action('add_meta_boxes', 'zib_add_meta_box_seo_meta');
    add_action('save_post', 'zib_save_meta_box_seo_meta');
}

function zib_get_seo_preview_box($type = 'post')
{
    $title       = '';
    $keywords    = '';
    $description = '';
    $html        = '';
    $permalink   = '';

    $after = (_pz('connector') ? _pz('connector') : '-') . get_bloginfo('name');
    if ($type == 'post') {
        if (isset($_GET['post'])) {
            $post_id = (int) $_GET['post'];
        } elseif (isset($_POST['post_ID'])) {
            $post_id = (int) $_POST['post_ID'];
        } else {
            $post_id = 0;
        }
        if ($post_id) {
            $post      = get_post($post_id);
            $permalink = get_permalink($post);

            $title = zib_get_post_meta($post->ID, 'title', true);
            $title = $title ? $title : $post->post_title . zib_get_post_meta($post->ID, 'subtitle', true) . $after;

            $keywords = zib_get_post_meta($post->ID, 'keywords', true);

            if (!$keywords) {
                if (get_the_tags($post->ID)) {
                    foreach (get_the_tags($post->ID) as $tag) {
                        $keywords .= $tag->name . ', ';
                    }
                }
                foreach (get_the_category($post->ID) as $category) {
                    $keywords .= $category->cat_name . ', ';
                }
                $keywords = substr_replace($keywords, '', -2);
            }
            $description = zib_get_post_meta($post->ID, 'description', true);
            if (!$description) {
                if (!empty($post->post_excerpt)) {
                    $description = $post->post_excerpt;
                } else {
                    $description = $post->post_content;
                }
                $description = trim(str_replace(array("\r\n", "\r", "\n", '　', ' '), ' ', str_replace('"', "'", strip_tags($description))));

                /**删除短代码内容 */
                $description = preg_replace('/\[payshow.*payshow\]||\[hidecontent.*hidecontent\]||\[reply.*reply\]||\[postsbox.*\]/', '', $description);

                $description = mb_substr($description, 0, 200, 'utf-8');
                if (!$description) {
                    $description = get_bloginfo('name') . '-' . trim(wp_title('', false));
                }
            }
        }
    }
    $html .= '<style>
    .zib-widget.seo-preview {
        padding: 15px 20px;
        border-radius: 10px;
        max-width: 600px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
    }
    .seo-title a{
        font-size: 18px;
        line-height: 22px;
        color: #2440b3;
        text-decoration: none;
    }
    .seo-description {
        margin:10px 0 5px 0;
    }
    .seo-keywords {
        opacity: .6;
        margin-top: 5px;
    }
    </style>';

    if (!$permalink) {
        return $html . '<div style=" text-align: center; padding: 30px 15px; color: #fc61a5; font-size: 14px; " class="zib-widget seo-preview"><div class="seo-title"><span class="dashicons dashicons-warning"></span> 请保存内容后 刷新页面查看SEO预览</div></div>';
    }
    $title       = $title ? $title : '<span style=" color: #fa4784; "><span class="dashicons dashicons-warning"></span> SEO标题或者文章标题为空</span>';
    $keywords    = $keywords ? $keywords : '<span style=" color: #fa4784; "><span class="dashicons dashicons-warning"></span> SEO关键词为空</span>';
    $description = $description ? $description : '<span style=" color: #fa4784; "><span class="dashicons dashicons-warning"></span> SEO描述或文章内容为空</span>';

    $html .= '<div class="zib-widget seo-preview">';
    $html .= '<div class="seo-header"></div>';
    $html .= '<div class="seo-title">';
    $html .= '<a class="" href="javascript:;">' . $title . '</a>';
    $html .= '</div>';

    $html .= '<div class="seo-description">' . $description . '</div>';
    $html .= '<a class="" href="javascript:;">' . $permalink . '</a>';
    $html .= '<div class="seo-keywords">';
    $html .= '<div class="">' . $keywords . '</div>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

//页面配置
add_action('zib_require_end', 'zib_admin_extend_metabox_page_config');
function zib_admin_extend_metabox_page_config()
{
    $imagepath = get_template_directory_uri() . '/img/';
    $f_imgpath = get_template_directory_uri() . '/inc/csf-framework/assets/images/';
    $new_badge = zib_get_csf_option_new_badge();

    $prefix = 'page_config';
    CSF::createMetabox($prefix, array(
        'title'     => __('页面配置', 'zibll') . $new_badge['8.1'],
        'post_type' => array('page'),
        'context'   => 'normal',
        'priority'  => 'high',
        'theme'     => 'light',
        'data_type' => 'serialize',
    ));

    CSF::createSection($prefix, array(
        'title'  => __('顶部多功能组件', 'zibll'),
        'fields' => array(
            array(
                'content' => '<p><b>功能说明：</b></p>
                此功能和<a target="_blank" href="' . zib_get_admin_csf_url('页面显示/顶部多功能组件') . '">主题配置-顶部多功能组件</a>功能一致，在此处可以单独为当前页面自定义此功能<br>
                注意：当选择了页面模板后，部分页面模板将不会显示此功能',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            array(
                'id'      => 'header_slider_show',
                'title'   => '启用顶部多功能组件',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'dependency' => array('header_slider_show', '!=', ''),
                'title'      => ' ',
                'class'      => 'compact',
                'subtitle'   => '顶部多功能组件的显示规则',
                'id'         => 'header_slider_show_type',
                'type'       => 'radio',
                'inline'     => true,
                'default'    => '',
                'options'    => array(
                    ''        => '全部显示',
                    'only_pc' => '仅在PC端显示',
                    'only_sm' => '仅在移动端显示',
                ),
            ),
            array(
                'dependency' => array('header_slider_show', '!=', ''),
                'title'      => '导航栏文字颜色',
                'id'         => 'header_slider_nav_color',
                'default'    => '#ffffff',
                'desc'       => __('当开启导航栏固定在顶部后，如果设置的背景图为浅色风格，则默认的白色文字可能会看不清，则推荐设置为黑色', 'zibll'),
                'type'       => 'color',
            ),
            array(
                'dependency'   => array('header_slider_show', '!=', '', '', 'visible'),
                'id'           => 'header_slider',
                'type'         => 'group',
                'min'          => '1',
                'button_title' => '添加背景项目',
                'title'        => '背景内容',
                'subtitle'     => '添加背景',
                'desc'         => '注意：此处如果添加多个背景项目则会以幻灯片(图片轮流切换)的形式展示，当只有一个项目时，则为单个图片或视频背景<div class="c-yellow">由于移动端多数浏览器不支持视频背景功能，所以移动端不会显示视频！</div>',
                'default'      => array(
                    array(
                        'background'    => $imagepath . 'slider-bg.jpg',
                        'link'          => array(
                            'url'    => 'https://www.zibll.com/',
                            'target' => '_blank',
                        ),
                        'text'          => array(
                            'desc'  => '',
                            'title' => '',
                        ),
                        'text_align'    => 'left-bottom',
                        'text_parallax' => 30,
                        'text_size_m'   => 20,
                        'text_size_pc'  => 30,
                    ),
                    array(
                        'background'    => $imagepath . 'user_t.jpg',
                        'link'          => array(
                            'url'    => 'https://www.zibll.com/',
                            'target' => '_blank',
                        ),
                        'text'          => array(
                            'desc'  => '',
                            'title' => '',
                        ),
                        'text_align'    => 'left-bottom',
                        'text_parallax' => 30,
                        'text_size_m'   => 20,
                        'text_size_pc'  => 30,
                    ),
                ),
                'fields'       => CFS_Module::add_slider(),
            ),
            array(
                'dependency' => array('header_slider_show', '!=', ''),
                'id'         => 'header_slider_option',
                'type'       => 'fieldset',
                'title'      => '背景显示设置',
                'subtitle'   => '顶部多功能组件的显示配置和幻灯片配置',
                'default'    => array(
                    'direction'    => 'horizontal',
                    'loop'         => true,
                    'button'       => true,
                    'pagination'   => true,
                    'effect'       => 'slide',
                    'scale_height' => false,
                    'auto_height'  => false,
                    'pc_height'    => 550,
                    'm_height'     => 280,
                    'spacebetween' => 0,
                    'speed'        => 0,
                    'autoplay'     => true,
                    'interval'     => 6,
                ),
                'fields'     => CFS_Module::slide(),
            ),

            array(
                'title'   => __('叠加搜索组件', 'zibll'),
                'id'      => 'header_slider_search_s',
                'type'    => 'checkbox',
                'inline'  => true,
                'options' => array(
                    'pc_s' => 'PC端显示',
                    'm_s'  => '移动端显示',
                ),
                'default' => array(),
            ),

            array(
                'dependency' => array('header_slider_search_s', '!=', ''),
                'id'         => 'header_slider_search_option',
                'class'      => 'compact',
                'type'       => 'fieldset',
                'title'      => ' ',
                'subtitle'   => '在背景上方叠加显示的搜索组件',
                'sanitize'   => false,
                'fields'     => array(
                    array(
                        'title'   => '热门搜索',
                        'label'   => '显示网站热门搜索关键词（移动端不会显示）',
                        'id'      => 'show_keywords',
                        'default' => true,
                        'type'    => 'switcher',
                    ),
                    array(
                        'dependency' => array('show_keywords', '!=', ''),
                        'title'      => ' ',
                        'subtitle'   => '关键词最多显示',
                        'id'         => 'popular_limit', //限制
                        'class'      => 'compact',
                        'default'    => 6,
                        'type'       => 'spinner',
                        'min'        => 2,
                        'max'        => 50,
                        'step'       => 2,
                        'unit'       => '个',
                    ),
                    array(
                        'id'      => 'show_input_cat',
                        'title'   => '分类搜索',
                        'label'   => '显示分类搜索选择',
                        'default' => false,
                        'type'    => 'switcher',
                    ),
                    array(
                        'dependency'  => array('show_input_cat', '!=', ''),
                        'id'          => 'in_cat',
                        'title'       => ' ',
                        'class'       => 'compact',
                        'default'     => '',
                        'options'     => 'categories',
                        'placeholder' => '选择分类',
                        'subtitle'    => '默认搜索的分类',
                        'chosen'      => true,
                        'type'        => 'select',
                    ),
                    array(
                        'dependency'  => array('show_input_cat', '!=', ''),
                        'id'          => 'more_cats',
                        'title'       => ' ',
                        'subtitle'    => '允许用户选择更多分类',
                        'default'     => '',
                        'class'       => 'compact',
                        'desc'        => '允许选择的更多分类，注意没有文章的分类不会显示',
                        'placeholder' => '允许选择的更多分类',
                        'options'     => 'categories',
                        'type'        => 'select',
                        'chosen'      => true,
                        'multiple'    => true,
                        'sortable'    => true,
                    ),
                    array(
                        'id'      => 'show_type',
                        'title'   => '显示类型选择',
                        'label'   => '显示切换搜索类型的按钮(文章，用户，版块，帖子，商品)',
                        'default' => false,
                        'type'    => 'switcher',
                    ),
                    array(
                        'id'      => 'in_type',
                        'default' => 'post',
                        'type'    => 'radio',
                        'title'   => '默认搜索类型',
                        'inline'  => true,
                        'options' => 'zib_get_search_types',
                    ),
                    array(
                        'title'      => __('额外内容', 'zibll'),
                        'subtitle'   => __('组件【上方】添加额外内容', 'zibll'),
                        'id'         => 'before_html',
                        'default'    => '<div class="em16 font-bold mb10">更优雅的Wordpress主题</div>
<div>这是导航栏幻灯片多功能组件，可在后台进行配置</div>',
                        'sanitize'   => false,
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 2,
                        ),
                    ),
                    array(
                        'title'      => __(' ', 'zibll'),
                        'subtitle'   => __('组件【下方】添加额外内容', 'zibll'),
                        'desc'       => __('支持HTML代码，请注意代码规范，同时请注意组件总高度！', 'zibll'),
                        'class'      => 'compact',
                        'id'         => 'after_html',
                        'default'    => '',
                        'sanitize'   => false,
                        'type'       => 'textarea',
                        'attributes' => array(
                            'rows' => 2,
                        ),
                    ),
                ),
            ),

            array(
                'title'   => __('叠加封面卡片组件', 'zibll'),
                'id'      => 'header_slider_card_s',
                'type'    => 'checkbox',
                'inline'  => true,
                'options' => array(
                    'pc_s' => 'PC端显示',
                    'm_s'  => '移动端显示',
                ),
                'default' => array(),
            ),
            array(
                'dependency' => array('header_slider_card_s', '!=', ''),
                'id'         => 'header_slider_card_option',
                'class'      => 'compact',
                'type'       => 'fieldset',
                'title'      => ' ',
                'subtitle'   => '在背景下方叠加显示的图标卡片组件',
                'sanitize'   => false,
                'default'    => array(
                    'show_widget_bg' => false,
                    'icon_radius4'   => false,
                    'cards'          => array(
                        array(
                            'icon'           => 'zibsvg-vip_1',
                            'customize_icon' => '',
                            'icon_class'     => 'c-yellow',
                            'link'           => array(),
                            'title'          => '图标卡片',
                            'desc'           => '这是一个图标卡片示例',
                        ),
                        array(
                            'icon'           => 'zibsvg-vip_2',
                            'customize_icon' => '',
                            'icon_class'     => 'c-blue-2',
                            'link'           => array(),
                            'title'          => '原创作品',
                            'desc'           => '这是一个图标卡片示例',
                        ),
                        array(
                            'icon'           => 'zibsvg-hot',
                            'customize_icon' => '',
                            'icon_class'     => 'jb-pink',
                            'link'           => array(),
                            'title'          => '灵感来源<badge class="ml6 jb-yellow">NEW</badge>',
                            'desc'           => '这是一个图标卡片示例',
                        ),
                        array(
                            'icon'           => 'fa fa-ioxhost',
                            'customize_icon' => '',
                            'icon_class'     => 'jb-cyan',
                            'link'           => array(),
                            'title'          => '系统工具 <badge class="ml6 jb-blue">GO <span class="fa fa-angle-right em12"></span></badge>',
                            'desc'           => '这是一个图标卡片示例',
                        ),
                    ),
                ),
                'fields'     => array(
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
                        'id'           => 'cards',
                        'title'        => '添加卡片',
                        'type'         => 'group',
                        'button_title' => '添加内容',
                        'default'      => array(),
                        'fields'       => array(
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
            ),
        ),
    ));

}

function zib_admin_widgets_register_menu_options()
{
    $prefix = 'zib_menu_options';
    CSF::createNavMenuOptions($prefix, array(
        'data_type' => 'serialize',
    ));

    CSF::createSection($prefix, array(
        'fields' => array(
            array(
                'id'    => 'icon',
                'title' => '图标',
                'type'  => 'icon',
            ),
            array(
                'id'    => 'badge',
                'type'  => 'text',
                'title' => '徽章',
            ),
            array(
                'dependency' => array('badge', '!=', ''),
                'title'      => '徽章背景色',
                'class'      => 'compact skin-color',
                'desc'       => '',
                'id'         => 'badge_class',
                'default'    => 'jb-red',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(),
            ),
            array(
                'title'   => '高级子菜单',
                'type'    => 'switcher',
                'id'      => 'submenu_s',
                'label'   => '使用UI更丰富、功能更强大的子菜单',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'dependency' => array('submenu_s', '!=', ''),
                'content'    => '<div><b>注意：</b></div>
                <div>1.此功能仅在当前菜单为一级菜单时生效</div>
                <div>2.当前菜单下的默认子项目将失效，仅显示下方配置的子菜单</div>
                <div>3.高级子菜单一般内容较多，请注意考虑移动端显示情况（PC端和移动端最好分开创建菜单）</div>',
                'style'      => 'warning',
                'type'       => 'submessage',
            ),
            array(
                'dependency' => array('submenu_s', '!=', ''),
                'title'      => '子菜单类型',
                'id'         => 'submenu_type',
                'type'       => 'radio',
                'inline'     => true,
                'default'    => 'graphic_card',
                'options'    => array(
                    'graphic_card'       => '图文卡片',
                    'multi_column_links' => '多栏目链接',
                ),
            ),
            array(
                'dependency' => array('submenu_s|submenu_type', '!=|==', '|graphic_card'),
                'id'         => 'graphic_card_opts',
                'type'       => 'fieldset',
                'fields'     => [
                    array(
                        'id'                     => 'items',
                        'type'                   => 'group',
                        'accordion_title_number' => '1',
                        'sanitize'               => false,
                        'button_title'           => '添加图文卡片',
                        'fields'                 => array(
                            array(
                                'placeholder' => '请输入标题',
                                'id'          => 'title',
                                'type'        => 'text',
                            ),
                            array(
                                'id'          => 'img',
                                'preview'     => true,
                                'library'     => 'image',
                                'class'       => 'compact',
                                'placeholder' => '图片',
                                'type'        => 'upload',
                            ),
                            array(
                                'id'           => 'link',
                                'type'         => 'link',
                                'add_title'    => '添加链接',
                                'edit_title'   => '编辑链接',
                                'remove_title' => '删除链接',
                            ),
                            array(
                                'id'       => 'badge',
                                'type'     => 'text',
                                'title'    => ' ',
                                'subtitle' => '徽章',
                            ),
                            array(
                                'dependency' => array('badge', '!=', ''),
                                'title'      => ' ',
                                'subtitle'   => '徽章背景色',
                                'class'      => 'compact skin-color',
                                'desc'       => '',
                                'id'         => 'badge_class',
                                'default'    => 'jb-red',
                                'type'       => 'palette',
                                'options'    => CFS_Module::zib_palette(),
                            ),
                        ),
                    ),
                    array(
                        'title'   => '排列方式',
                        'id'      => 'arrangement',
                        'type'    => 'radio',
                        'inline'  => true,
                        'default' => 'swiper',
                        'options' => array(
                            ''       => '自动换行',
                            'swiper' => '单行左右滚动',
                        ),
                    ),
                    array(
                        'id'      => 'img_scale',
                        'title'   => '图片长宽比例(宽:高)',
                        'default' => 100,
                        'max'     => 300,
                        'min'     => 20,
                        'step'    => 10,
                        'unit'    => '%',
                        'type'    => 'spinner',
                    ),
                    array(
                        'id'      => 'size',
                        'title'   => '尺寸',
                        'default' => 'md',
                        'inline'  => true,
                        'type'    => 'radio',
                        'options' => array(
                            'sm' => __('小', 'zibll'),
                            'md' => __('中', 'zibll'),
                            'xs' => __('大', 'zibll'),
                            'lg' => __('超大', 'zibll'),
                        ),
                    ),
                    array(
                        'dependency' => array('arrangement', '==', ''),
                        'title'      => '对齐方式',
                        'id'         => 'align',
                        'type'       => 'radio',
                        'inline'     => true,
                        'default'    => '',
                        'options'    => array(
                            ''    => '靠左',
                            'jc'  => '居中',
                            'jsa' => '平均分布',
                        ),
                    ),
                ],
            ),
            array(
                'dependency' => array('submenu_s|submenu_type', '!=|==', '|multi_column_links'),
                'id'         => 'multi_column_links_opts',
                'type'       => 'fieldset',
                'fields'     => [
                    array(
                        'id'                     => 'columns',
                        'type'                   => 'group',
                        'accordion_title_number' => '1',
                        'sanitize'               => false,
                        'button_title'           => '添加栏目',
                        'fields'                 => array(
                            array(
                                'placeholder' => '请输入栏目标题',
                                'id'          => 'title',
                                'type'        => 'text',
                            ),
                            array(
                                'dependency'   => array('img_icon', '==', '', '', 'visible'),
                                'id'           => 'icon',
                                'type'         => 'icon',
                                'button_title' => '标题图标',
                                'default'      => '',
                            ),
                            array(
                                'id'          => 'img_icon',
                                'preview'     => true,
                                'library'     => 'image',
                                'class'       => 'compact',
                                'placeholder' => '标题图片',
                                'desc'        => '设置图片将覆盖图标，同一栏目统一效果更佳',
                                'type'        => 'upload',
                            ),
                            array(
                                'id'           => 'link',
                                'type'         => 'link',
                                'add_title'    => '添加链接',
                                'edit_title'   => '编辑链接',
                                'remove_title' => '删除链接',
                            ),
                            array(
                                'title'                  => '子项目列表',
                                'id'                     => 'items',
                                'type'                   => 'group',
                                'accordion_title_number' => '1',
                                'sanitize'               => false,
                                'button_title'           => '添加子项目',
                                'fields'                 => array(
                                    array(
                                        'placeholder' => '请输入链接标题',
                                        'id'          => 'title',
                                        'type'        => 'text',
                                    ),
                                    array(
                                        'dependency'   => array('img_icon', '==', '', '', 'visible'),
                                        'id'           => 'icon',
                                        'type'         => 'icon',
                                        'button_title' => '标题图标',
                                        'default'      => '',
                                    ),
                                    array(
                                        'id'          => 'img_icon',
                                        'preview'     => true,
                                        'library'     => 'image',
                                        'class'       => 'compact',
                                        'placeholder' => '标题图片',
                                        'desc'        => '设置图片将覆盖图标，同一列表统一效果更佳',
                                        'type'        => 'upload',
                                    ),
                                    array(
                                        'id'           => 'link',
                                        'type'         => 'link',
                                        'add_title'    => '添加链接',
                                        'edit_title'   => '编辑链接',
                                        'remove_title' => '删除链接',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ],
            ),
            array(
                'dependency' => array('submenu_s|submenu_type', '!=|==', '|graphic_card'),
                'id'         => 'graphic_card_opts',
                'type'       => 'fieldset',
                'title'      => '子菜单配置',
                'fields'     => [

                ],
            ),

        ),
    ));
}
add_action('zib_require_end', 'zib_admin_widgets_register_menu_options');

function zib_admin_post_category_options_csf()
{
    $prefix = 'post_cat_config';
    CSF::createTaxonomyOptions($prefix, array(
        'title'     => '阅读权限配置',
        'taxonomy'  => 'category',
        'data_type' => 'serialize',
    ));

    $vip = array();
    if (_pz('pay_user_vip_1_s', true)) {
        if (_pz('pay_user_vip_2_s', true)) {
            $vip = array(
                1 => _pz('pay_user_vip_1_name') . '及以上会员可查看',
                2 => _pz('pay_user_vip_2_name') . '可查看',
            );
        } else {
            $vip = array(
                1 => _pz('pay_user_vip_1_name') . '可查看',
            );
        }
    }
    $vip = $vip ? array(
        '' => '不限制会员角色',
    ) + $vip : false;

    $level_max = _pz('user_level_max', 10);
    $level     = array();
    for ($i = 1; $i <= $level_max; $i++) {
        $level[$i] = _pz('user_level_opt', 'LV' . $i, 'name_' . $i);
    }
    $level = $level ? array(
        '' => '不限制等级角色',
    ) + $level : false;

    $allow_view_roles = array();
    if ($vip) {
        $allow_view_roles[] = array(
            'title'   => '会员阅读权限',
            'id'      => 'vip',
            'inline'  => true,
            'default' => '',
            'type'    => 'radio',
            'options' => $vip,
        );
    }
    if ($level) {
        $allow_view_roles[] = array(
            'title'   => '会员阅读权限',
            'id'      => 'level',
            'default' => '',
            'inline'  => true,
            'type'    => 'radio',
            'options' => $level,
        );
    }
    if (_pz('user_auth_s', true)) {
        $allow_view_roles[] = array(
            'label'   => '允许认证用户查看',
            'id'      => 'auth',
            'default' => false,
            'type'    => 'switcher',
        );
    }
    CSF::createSection($prefix, array(
        'fields' => array(
            array(
                'title'   => '文章查看限制',
                'default' => false,
                'desc'    => '设置当前分类下的文章查看限制',
                'id'      => 'allow_view',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    ''       => __('不限制', 'zibll'),
                    'signin' => __('登录后可查看', 'zibll'),
                    'roles'  => __('部分用户可查看', 'zibll'),
                ),
            ),
            array(
                'dependency' => array('allow_view', '==', 'roles'),
                'title'      => ' ',
                'subtitle'   => '可查看的用户组（满足其一）',
                'id'         => 'allow_view_roles',
                'type'       => 'fieldset',
                'class'      => 'compact',
                'default'    => [],
                'fields'     => $allow_view_roles,
            ),
        ),
    ));

}
add_action('zib_require_end', 'zib_admin_post_category_options_csf');

function zib_post_category_columns($columns)
{
    $columns['allow_view'] = __('文章查看限制', 'zibll');
    return $columns;
}
add_filter('manage_edit-category_columns', 'zib_post_category_columns');

function zib_post_category_column_column($columns, $column, $id)
{
    switch ($column) {
        case 'allow_view':
            $post_cat_config = get_term_meta($id, 'post_cat_config', true);
            if (empty($post_cat_config['allow_view'])) {
                return '<span class="opacity8">不限制</span>';
            }

            $allow_view = $post_cat_config['allow_view'];
            if ($allow_view == 'signin') {
                return '<span class="badg c-blue-2">登录可见</span>';
            }

            if ($allow_view == 'roles') {
                $allow_view_roles = $post_cat_config['allow_view_roles'] ?? [];
                $allow_view_name  = '<div class="em09">';
                $allow_view_name .= $allow_view_roles['vip'] ? '<span class="badg mm3 c-yellow">' . _pz('pay_user_vip_' . $allow_view_roles['vip'] . '_name') . '</span>' : '';
                $allow_view_name .= $allow_view_roles['level'] ? '<span class="badg mm3 c-green">' . 'LV' . $allow_view_roles['level'] . '</span>' : '';
                $allow_view_name .= $allow_view_roles['auth'] ? '<span class="badg mm3 c-blue">认证用户</span>' : '';
                $allow_view_name .= '</div>';
                return $allow_view_name;
            }

            break;
    }

    return $columns;
}
add_filter('manage_category_custom_column', 'zib_post_category_column_column', 10, 3);
