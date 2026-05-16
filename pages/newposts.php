<?php

/**
 * Template name: Zibll-写文章、投稿页面
 * Description:   用户前台发布文章的页面模板
 */

//引入核心文件
require_once get_theme_file_path('/inc/functions/compat/business-compat.php');

//前置判断，判断是否有编辑权限
$edit_id   = !empty($_REQUEST['edit']) ? $_REQUEST['edit'] : 0;
$edit_post = '';
if ($edit_id) {
    $edit_post = get_post($edit_id);
    if ((empty($edit_post->ID) || !zib_current_user_can('new_post_edit', $edit_post))) {
        wp_safe_redirect(home_url(remove_query_arg('edit')));
        return;
    }
    $edit_id = $edit_post->ID;
}

$cuid = get_current_user_id();

//不显示悬浮按钮
remove_action('wp_footer', 'zib_float_right');

//不显示底部按钮
remove_action('wp_footer', 'zib_footer_tabbar');

if (!$cuid) {
    $btn_txet = '审核';
} else {
    $btn_txet = '发布';
}

if (!_pz('post_article_s') || zib_is_close_sign()) {
    get_template_part('template/content-404');
    get_footer();
    exit();
}

//编辑器按钮|上传图片
if (zib_current_user_can('new_post_upload_img')) {
    add_filter('tinymce_upload_img', '__return_true');
}

//编辑器按钮，上传视频
if (zib_current_user_can('new_post_upload_video')) {
    add_filter('tinymce_upload_video', '__return_true');
}

if (zib_current_user_can('new_post_upload_file')) {
    add_filter('tinymce_upload_file', '__return_true');
}

//编辑器按钮，嵌入视频
if (zib_current_user_can('new_post_iframe_video')) {
    add_filter('tinymce_iframe_video', '__return_true');
}

//编辑器按钮，隐藏内容
if (zib_current_user_can('new_post_hide')) {
    add_filter('tinymce_hide', '__return_true');
}

//编辑器按钮，付费功能
$can_new_post_pay = zib_current_user_can('new_post_pay');
if ($can_new_post_pay) {
    add_filter('tinymce_hide_pay', '__return_true');
}

//建议搜索引擎不抓取
add_filter('wp_robots', 'zib_robots_no_robots');

//准备参数
$is_can_new = zib_current_user_can('new_post_add');
$in         = array(
    'ID'           => '',
    'post_title'   => '',
    'post_content' => '',
    'view_btn'     => '',
    'uptime_badge' => '',
    'cat_id'       => '',
    'text_tags'    => '',
    'post_status'  => '',
);
if (!empty($edit_post->ID)) {
    $is_can_new         = true; //拥有编辑权限，则拥有此权限
    $in                 = array_merge($in, (array) $edit_post);
    $is_edit            = true;
    $in['view_btn']     = '<a class="but c-blue" href="' . get_permalink($edit_post) . '"><i class="fa fa-file-text-o"></i> 预览文章</a>';
    $in['uptime_badge'] = '<span class="badg">最后保存：' . $in['post_modified'] . '</span>';

    if (is_super_admin()) {
        $in['view_btn'] .= '<a class="but c-yellow ml6" href="' . get_edit_post_link($edit_post) . '">后台编辑</a>';
    }

    $the_tags = get_the_tags($edit_post->ID);
    if ($the_tags) {
        $the_tags        = array_column((array) $the_tags, 'name');
        $in['text_tags'] = implode(', ', $the_tags);
    }
}

if (!$in['view_btn'] && $cuid) {
    //如果有草稿，则提示
    $draft_post_count = zib_get_user_post_count($cuid, 'draft', 'post');
    if ($draft_post_count) {
        $in['view_btn'] = '<a target="_blank" href="' . zib_get_user_home_url($cuid, array('tab' => 'post', 'post_status' => 'draft')) . '" class="but mr10 p2-10 px12">您已有' . $draft_post_count . '篇草稿 点击查看<i class="ml6 fa fa-angle-right em12"></i></a>';
    }
}

//文章封面
$featured_edit = '';
if (zib_current_user_can('new_post_image_cover')) {
    add_filter('featured_image_edit', '__return_true');

    if (zib_current_user_can('new_post_slide_cover')) {
        add_filter('featured_slide_edit', '__return_true');
    }
    if (zib_current_user_can('new_post_video_cover')) {
        add_filter('featured_video_edit', '__return_true');
    }

    $options = array(
        'video_ratio' => 50, //比例
        'slide_ratio' => 50, //比例
        'image_ratio' => 50, //比例
    );

    $featured_edit = zib_get_post_featured_edit_box($edit_id, 'mb20', $options);
}

//开始渲染
get_header();

?>
<main role="main" class="container">
    <form>
        <div class="content-wrap newposts-wrap">
            <div class="content-layout">
                <div class="zib-widget full-widget-sm editor-main-box" style="min-height:60vh;">
                    <?php echo $featured_edit; ?>
                    <div class="relative newposts-title">
                        <textarea type="text" class="line-form-input input-lg new-title" name="post_title" tabindex="1" rows="1" autoHeight="true" maxHeight="<?php echo(wp_is_mobile() ? 110 : 78); ?>" placeholder="<?php echo _pz('post_t_placeholder', '请输入标题'); ?>"><?php echo esc_attr($in['post_title']); ?></textarea>
                        <i class="line-form-line"></i>
                    </div>
                    <?php

if (!$is_can_new) {
    echo '<div class="flex jc" style="min-height:50vh;">';
    echo zib_get_nocan_info($cuid, 'new_post_add', '无法发布');
    echo '</div>';
} else {
    $editor_id = 'post_content';
    $settings  = array(
        'textarea_rows'  => 20,
        'editor_height'  => (wp_is_mobile() ? 460 : 470),
        'media_buttons'  => false,
        'default_editor' => 'tinymce',
        'quicktags'      => false,
        'editor_css'     => '<link rel="stylesheet" href="' . ZIB_TEMPLATE_DIRECTORY_URI . '/css/new-posts.min.css?ver=' . THEME_VERSION . '" type="text/css">',
        'teeny'          => false,
        'tinymce'        => array(
            'placeholder' => _pz('post_c_placeholder', '请输入内容'),
        ),
    );
    wp_editor($in[$editor_id], $editor_id, $settings);
}

?>
                    <?php echo '<div class="em09 flex ac hh"><span class="view-btn mr6 mt6">' . $in['view_btn'] . '</span><span class="modified-time mt6">' . $in['uptime_badge'] . '</span></div>'; ?>
                </div>
            </div>
        </div>

        <div class="sidebar show-sidebar">
            <?php dynamic_sidebar('newposts_sidebar_top'); ?>
            <?php if (!$cuid) {
    ?>
                <div class="zib-widget mb10-sm">
                    <div class="title-theme mb10">用户信息</div>
                    <p class="muted-3-color em09">请输入昵称</p>
                    <div class="mb20">
                        <input class="form-control" name="user_name" placeholder="请输入昵称">
                    </div>
                    <p class="muted-3-color em09">请输入您的联系方式</p>
                    <input class="form-control" name="contact_details" placeholder="输入联系方式">
                </div>
            <?php }?>
            <div class="zib-widget mb10-sm">
                <div class="title-theme mb10">文章参数</div>
                <div class="term-select-box">
                    <div class="newpost-cat-drop drop-select dropdown dependency-box">
                        <div data-toggle-class="open" data-target=".newpost-cat-drop" class="flex ac jsb drop-btn form-control mb6">
                            <div class="title-theme222"><i class="fa fa-fw fa-folder-open-o mr6" aria-hidden="true"></i>分类</div><i class="ml6 fa fa-angle-right em12"></i>
                        </div>
                        <div class="dropdown-menu fluid" style="margin-bottom: -4px;">
                            <div class="mini-scrollbar padding-w10 select-drop-box">
                                <?php
$opt         = _pz('post_article_cat', array()) ?: 'all';
$checked_ids = array();
if ($in['ID']) {
    $the_terms = get_the_terms($in['ID'], 'category');
    if ($the_terms && !is_wp_error($the_terms)) {
        $checked_ids = array_column((array) $the_terms, 'term_id');
    }
}

$topics_lists = zib_get_term_multiple_select_option($opt, $checked_ids);
echo $topics_lists ?: '暂无可选择的分类' . (is_super_admin() ? '<br>请在后台主题设置->文章功能->前台投稿中添加允许选择的分类<a class="c-red" target="_blank" href="' . zib_get_admin_csf_url('文章列表/前台投稿') . '">【去配置】</a>' : '');
?>
                            </div>
                        </div>
                    </div>

                    <?php

//专题
if (_pz('post_article_topics_s')) {

    $opt         = _pz('post_article_topics', array()) ?: 'all';
    $checked_ids = array();
    if ($in['ID']) {
        $the_terms = get_the_terms($in['ID'], 'topics');
        if ($the_terms && !is_wp_error($the_terms)) {
            $checked_ids = array_column((array) $the_terms, 'term_id');
        }
    }
    $topics_lists = zib_get_term_multiple_select_option($opt, $checked_ids, 'topics', 'topics');
    if ($topics_lists) {

        echo '
                    <div class="newpost-topics-drop drop-select dropdown dependency-box">
                        <div data-toggle-class="open" data-target=".newpost-topics-drop" class="flex ac jsb drop-btn form-control mb6">
                        <div class="title-theme222"><i class="fa fa-fw fa-cube mr6" aria-hidden="true"></i>专题</div><i class="ml6 fa fa-angle-right em12"></i>
                        </div>
                        <div class="dropdown-menu fluid" style="margin-bottom: -4px;">
                            <div class="mini-scrollbar padding-w10 select-drop-box">' . $topics_lists . '</div>
                        </div>
                    </div>';
    }
}

//高级筛选
if (_pz('post_article_custom_filter_s')) {

    $filter_html = '';
    $filter_args = zib_get_custom_filter_args();
    if ($filter_args) {
        foreach ($filter_args as $filter) {
            $checked_vals = array();
            $filter_item  = '';
            if ($in['ID']) {
                $checked_vals = (array) get_post_meta($in['ID'], $filter['key'], true);
                if ($checked_vals) {
                    foreach ($checked_vals as $c_v) {
                        $filter_html .= '<input type="hidden" name="' . $filter['key'] . '[]" value="' . $c_v . '">';
                    }
                }
            }

            foreach ($filter['vals'] as $filter_key => $filter_name) {
                $filter_item .= '<span class="tag-list ajax-item pointer' . (in_array($filter_key, $checked_vals) ? ' active' : '') . '" data-multiple="50" data-for="' . $filter['key'] . '" data-value="' . $filter_key . '"><span class="badg mm3">' . $filter_name . '</span></span>';
            }
            $filter_html .= '<div class="newpost-custom-filter-' . $filter['key'] . ' drop-select dropdown dependency-box">
                                                <div data-toggle-class="open" data-target=".newpost-custom-filter-' . $filter['key'] . '" class="flex ac jsb drop-btn form-control mb6">
                                                <div class="title-theme222"><i class="fa fa-fw fa-filter mr6" aria-hidden="true"></i>' . $filter['name'] . '</div><i class="ml6 fa fa-angle-right em12"></i>
                                                </div>
                                                <div class="dropdown-menu fluid" style="margin-bottom: -4px;">
                                                    <div class="padding-10 select-drop-box">' . $filter_item . '</div>
                                                </div>
                                            </div>';
        }
        echo $filter_html;
    }
}

?>

                </div>

                <?php
if (_pz('post_article_tag')) {
    echo '<div class="title-theme mb10 mt20">标签</div>
                        <p class="muted-3-color em09">填写文章的标签，每个标签用逗号隔开</p>
                        <textarea class="form-control" rows="3" name="tags" placeholder="输入文章标签" tabindex="6">' . $in['text_tags'] . '</textarea>';
}?>

            </div>

            <?php
if ($can_new_post_pay) {
    echo zib_newpost_get_paybox($in['ID']);
}?>
            <div class="zib-widget">
                <div class="text-center">
                    <p class="separator muted-3-color theme-box">Are you ready</p>
                    <?php

echo '<input type="hidden" name="posts_id" value="' . (int) $in['ID'] . '">';
echo wp_nonce_field('new_posts', '_wpnonce', false, false);
$btns = '';
if (!$is_can_new) {
    echo '<p class="em09 muted-3-color theme-box">暂无发布权限</p>';
} else {
    if ($cuid) {
        if ('publish' !== $in['post_status'] && 'pending' !== $in['post_status']) {
            $btns .= '<botton type="button" action="posts_draft" name="submit" class="but jb-green new-posts-submit padding-lg"><i class="fa fa-fw fa-dot-circle-o"></i>保存草稿</botton>';
        } elseif ($in['post_status']) {
            $btn_txet = '保存';
        }
    } else {
        echo '<p class="em09 muted-3-color theme-box">您当前未登录，不能保存草稿，文章提交' . $btn_txet . '之后不可再修改！</p>';
    }

    //人机验证
    if (_pz('verification_newposts_s')) {
        $verification_input = zib_get_machine_verification_input('newposts_submit');
        if ($verification_input) {
            echo '<div >' . $verification_input . '</div>';
        }
    }

    $btns .= '<botton type="button" action="posts_save" name="submit" class="ml10 but jb-blue new-posts-submit padding-lg"><i class="fa fa-fw fa-check-square-o"></i>提交' . $btn_txet . '</botton>';
}

echo $btns ? '<div class="but-average  ">' . $btns . '</div>' : '';
?>
                </div>
            </div>
            <?php dynamic_sidebar('newposts_sidebar_bottom'); ?>
        </div>
    </form>
</main>
<?php get_footer();

/**
 * @description: 多选term
 * @param {*} $ids
 * @param {*} $checked_ids
 * @param {*} $taxonomy
 * @param {*} $name
 * @return {*}
 */
function zib_get_term_multiple_select_option($ids, $checked_ids = array(), $taxonomy = 'category', $name = 'category')
{

    if (!isset($GLOBALS['multiple_select_' . $name])) {
        $GLOBALS['multiple_select_' . $name] = array();
    }

    $get_args = array(
        'taxonomy'   => $taxonomy,
        'include'    => $ids,
        'orderby'    => 'include',
        'hide_empty' => false,
    );
    if ($ids == 'all') {
        $get_args = array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => 50,
        );
        if ($taxonomy == 'category') {
            $get_args['parent'] = 0;
        }
    }

    $get_terms = get_terms($get_args);
    $lists     = '';

    if (isset($get_terms[0]->term_id)) {
        foreach ($get_terms as $term_objs) {
            if (!in_array($term_objs->term_id, $GLOBALS['multiple_select_' . $name])) {
                $GLOBALS['multiple_select_' . $name][] = $term_objs->term_id; //防止重复
                $lists .= '<div><label class="muted-color font-normal pointer"><input value="' . $term_objs->term_id . '" ' . (in_array($term_objs->term_id, $checked_ids) ? '  checked="checked"' : '') . ' type="checkbox" name="' . $name . '[]"><span class="ml6">' . $term_objs->name . '</span></label></div>';
                $term_hierarchy = _get_term_hierarchy($taxonomy);
                if (!empty($term_hierarchy[$term_objs->term_id])) {
                    $hierarchy_lists = zib_get_term_multiple_select_option($term_hierarchy[$term_objs->term_id], $checked_ids, $taxonomy, $name);
                    $lists .= $hierarchy_lists ? '<div class="ml20">' . $hierarchy_lists . '</div>' : '';
                }
            }
        }
    }
    return $lists;
}

//付费模块
function zib_newpost_get_paybox($in_id = 0)
{
    //默认参数
    $default = array(
        'pay_type'     => 'no',
        'pay_modo'     => '0',
        'points_price' => '',
        'vip_1_points' => '',
        'vip_2_points' => '', 
        'pay_price'    => '',
        'vip_1_price'  => '',
        'vip_2_price'  => '',
    );
    $points_s = _pz('points_s');
    $user_id  = get_current_user_id();

    $pay_mate    = (array) get_post_meta($in_id, 'posts_zibpay', true);
    $in          = array_merge($default, $pay_mate);
    $in_s        = $in['pay_type'] && $in['pay_type'] !== 'no';
    $vip_input_s = _pz('post_article_pay_vip_price_s');
    $vip_1_s     = _pz('pay_user_vip_1_s');
    $vip_2_s     = _pz('pay_user_vip_2_s');
    $money_icon  = zib_get_svg('money-color-2', null, 'mr6 em12');

    //付费类型
    $pay_type_args = array(
        'no' => '关闭',
        '1'  => '付费阅读',
        '2'  => '付费资源',
    );
    $pay_type_input = '';
    foreach ($pay_type_args as $k => $v) {
        $pay_type_input .= '<label class="badg p2-10 mr10 pointer"><input type="radio"' . (checked($in['pay_type'], $k, false)) . ' name="pay_type" value="' . $k . '"> ' . $v . '</label>';
    }
    $pay_type_input = '<div><p class="muted-3-color em09">设置付费内容</p><div>' . $pay_type_input . '</div></div>';

    $pay_type_input = '<div class="flex ac jsb padding-h10 border-bottom">
    <div class="flex ac">内容付费</div>
    <label style="margin: 0;"><input class="hide" name="zibpay_s" type="checkbox"' . ($in_s ? ' checked="checked"' : '') . '><div class="form-switch flex0"></div></label>
</div>';

    //支付类型
    $pay_modo_input = '<input type="hidden" name="posts_zibpay[pay_modo]" value="' . $in['pay_modo'] . '">';
    if ($points_s) {
        $pay_modo_input .= '<div class="flex ac jsb padding-h10">
            <div class="flex ac">支付类型</div>
            <div class="but-average radius em09">
                <span data-for="posts_zibpay[pay_modo]" data-value="0" class="but p2-10 pointer' . ($in['pay_modo'] !== 'points' ? ' active' : '') . '">现金支付</span>
                <span data-for="posts_zibpay[pay_modo]" data-value="points" class="but p2-10 pointer' . ($in['pay_modo'] === 'points' ? ' active' : '') . '">积分支付</span>
            </div>
        </div>';
    }

    $vip_pay_price_input  = '';
    $pay_price_limit_pz   = _pz('post_article_pay_price_limit');
    $pay_price_limit      = !empty($pay_price_limit_pz['price']) ? $pay_price_limit_pz['price'] : false;
    $pay_price_limit_min  = !empty($pay_price_limit['min']) ? $pay_price_limit['min'] : 0;
    $pay_price_limit_max  = !empty($pay_price_limit['max']) ? $pay_price_limit['max'] : 0;
    $pay_price_limit_attr = '';
    if ($pay_price_limit_min) {
        $pay_price_limit_attr .= ' limit-min="' . $pay_price_limit_min . '" warning-min="最低可设置：1$"';
    }
    if ($pay_price_limit_max) {
        $pay_price_limit_attr .= ' limit-max="' . $pay_price_limit_max . '" warning-max="最高可设置：1$"';
    }
    if ($vip_input_s && $vip_1_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(1, 'em12 mr6', false) . _pz('pay_user_vip_1_name') . '价格</div><input limit-min="0"' . $pay_price_limit_attr . ' type="number" name="posts_zibpay[vip_1_price]" value="' . $in['vip_1_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_input_s && $vip_2_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(2, 'em12 mr6', false) . _pz('pay_user_vip_2_name') . '价格</div><input limit-min="0"' . $pay_price_limit_attr . ' type="number" name="posts_zibpay[vip_2_price]" value="' . $in['vip_2_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_pay_price_input) {
        $vip_pay_price_input .= '<div class="px12 mt6 muted-color">会员价不能高于普通价，为0则为会员免费</div>';
    }
    //设置金额
    $pay_price_input = '<div class="mt10" data-controller="posts_zibpay[pay_modo]" data-condition="!=" data-value="points"' . ($in['pay_modo'] === 'points' ? ' style="display: none;"' : '') . '>
    <div class="relative">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . $money_icon . '设置价格</div><input' . $pay_price_limit_attr . ' type="number" name="posts_zibpay[pay_price]" value="' . $in['pay_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>' . $vip_pay_price_input . '</div>';

    //设置积分
    $vip_pay_price_input  = '';
    $pay_price_limit      = !empty($pay_price_limit_pz['points']) ? $pay_price_limit_pz['points'] : false;
    $pay_price_limit_min  = !empty($pay_price_limit['min']) ? $pay_price_limit['min'] : 0;
    $pay_price_limit_max  = !empty($pay_price_limit['max']) ? $pay_price_limit['max'] : 0;
    $pay_price_limit_attr = '';
    if ($pay_price_limit_min) {
        $pay_price_limit_attr .= ' limit-min="' . $pay_price_limit_min . '" warning-min="最低可设置：1$"';
    }
    if ($pay_price_limit_max) {
        $pay_price_limit_attr .= ' limit-max="' . $pay_price_limit_max . '" warning-max="最高可设置：1$"';
    }
    if ($vip_input_s && $vip_1_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(1, 'em12 mr6', false) . _pz('pay_user_vip_1_name') . '积分</div><input limit-min="0"' . $pay_price_limit_attr . ' type="number" name="posts_zibpay[vip_1_points]" value="' . $in['vip_1_points'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_input_s && $vip_2_s) {
        $vip_pay_price_input .= '<div class="relative mt6">
        <div class="flex ab">
            <div class="muted-color mb6 flex0">' . zibpay_get_vip_icon(2, 'em12 mr6', false) . _pz('pay_user_vip_2_name') . '积分</div><input limit-min="0"' . $pay_price_limit_attr . ' type="number" name="posts_zibpay[vip_2_points]" value="' . $in['vip_2_points'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
            <i class="line-form-line"></i>
        </div>
    </div>';
    }
    if ($vip_pay_price_input) {
        $vip_pay_price_input .= '<div class="px12 mt6 muted-color">会员价不能高于普通价，为0则为会员免费</div>';
    }
    if ($points_s) {
        $pay_price_input .= '<div class="mt10" data-controller="posts_zibpay[pay_modo]" data-condition="==" data-value="points"' . ($in['pay_modo'] !== 'points' ? ' style="display: none;"' : '') . '><div class="relative">
    <div class="flex ab">
        <div class="muted-color mb6 flex0">' . zib_get_svg('points-color', null, 'mr6 em12') . '设置积分</div><input type="number"' . $pay_price_limit_attr . ' name="posts_zibpay[points_price]" value="' . $in['points_price'] . '" style="padding: 0;" class="line-form-input em2x key-color text-right">
        <i class="line-form-line"></i>
    </div>
</div>' . $vip_pay_price_input . '</div>';
    }

    $download_html             = '';
    $add_btn_desc              = '请确保已经在文中添加了付费可见的隐藏内容';
    $desc                      = '如果您在文章中添加了付费可见的隐藏内容，请在此设置付费功能';
    $can_new_post_pay_download = zib_current_user_can('new_post_pay_download'); //是否允许添加付费下载

    if ($can_new_post_pay_download) {
        $download_html = zib_get_new_pay_download_box($pay_mate);
        $add_btn_desc .= '或已添加付费下载内容';
        $desc = '如果您在文章中添加了付费可见的隐藏内容或需要设置付费下载资源，请在此设置付费功能';
    }

    $add_btn_desc = '<div class="em09 mt10 muted-2-color">' . $add_btn_desc . '</div>';

    $income_desc = '';
    if (_pz('pay_income_s')) {
        $income_ratio = zibpay_get_user_income_ratio($user_id);
        if ($income_ratio) {
            $income_desc .= '<div class="c-blue px12 mt6">您已参与创作分成，本文获得的收益将与您分成，您可以进入<a rel="nofollow" target="_blank" class="c-blue-2" href="' . zib_get_user_center_url('income') . '">用户中心-创作分成</a>查看您的分成比例及分成详情</div>';
        }
    }

    $html = '<div class="zib-widget dependency-box mb10-sm">
            <div class="title-theme mb10">付费内容</div>
            <div>' . $pay_type_input . '
                <div data-controller="zibpay_s" data-condition="!=" data-value=""' . (!$in_s ? ' style="display: none;"' : '') . '>' . $pay_modo_input . $pay_price_input . $download_html . $add_btn_desc . '</div>
                <div class="em09 mt10 muted-2-color" data-controller="zibpay_s" data-condition="==" data-value=""' . ($in_s ? ' style="display: none;"' : '') . '>' . $desc . '</div>
                <div data-controller="zibpay_s" data-condition="!=" data-value=""' . (!$in_s ? ' style="display: none;"' : '') . '>' . $income_desc . '</div>
            </div>
        </div>';
    return $html;
}

//获取付费下载框
function zib_get_new_pay_download_box($pay_mate)
{
    $html = '<div class="flex ac jsb padding-h10">
        <b class="">付费下载</b>
        <div class="">
            <botton type="button" class="pay-dawnload-add but hollow p2-10 px12 c-blue">' . zib_get_svg('add') . '添加资源</botton>
        </div>
    </div>';

    $lists = '';
    if (isset($pay_mate['pay_download'][0]['link'])) {
        foreach ($pay_mate['pay_download'] as $key => $down_v) {
            $down_more = $down_v['more'] ? '<span class="badg">' . $down_v['more'] . '</span>' : '';
            $down_link = $down_v['link'] ? '<a href="javascript:;" class="flex1 name-link pay-dawnload-add-edit">' . $down_v['link'] . '</a>' : '';

            $lists .= '<div class="flex ac jsb lists-item">' . $down_link . '<a href="javascript:;" class="pay-dawnload-add-delete em12 ml10 hollow but cir c-red"><i title="fa fa-trash-o" class="fa fa-trash-o"></i></a>';
            $lists .= '<div class="hidden-input">';
            foreach ($down_v as $_name => $_val) {
                $lists .= '<input type="hidden" name="posts_zibpay[pay_download][' . $key . '][' . $_name . ']" data-depend-id="' . $_name . '" value="' . esc_attr($_val) . '">';
            }
            $lists .= '</div></div>';
        }
    }

    return '<div class="mt10 pay-dawnload-edit">' . $html . '<div class="mt10 px12 lists-box">' . $lists . '</div></div>';
}
