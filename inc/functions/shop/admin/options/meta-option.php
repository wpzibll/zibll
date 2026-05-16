<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-04-30 14:39:01
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台商品配置项
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

add_action('zib_require_end', 'zib_shop_admin_product_metabox');
function zib_shop_admin_product_metabox()
{
    //页面限制：新增商品、编辑商品
    if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') === false && strpos($_SERVER['SCRIPT_NAME'], 'post.php') === false) {
        return;
    }

    $prefix = 'product_config';
    CSF::createMetabox($prefix, array(
        'title'     => __('商品配置', 'zibll'),
        'post_type' => array('shop_product'),
        'context'   => 'normal',
        'priority'  => 'high',
        'theme'     => 'light',
        'data_type' => 'serialize',
    ));

    CSF::createSection($prefix, array(
        'title'  => __('商品详情', 'zibll'),
        'fields' => array(
            //简介desc
            array(
                'title'      => '简介',
                'desc'       => '一句话介绍商品，内容不宜过多',
                'id'         => 'desc',
                'type'       => 'textarea',
                'sanitize'   => false,
                'default'    => '',
                'attributes' => array(
                    'rows' => 1,
                ),
            ),
            array(
                'title'       => '封面图片',
                'id'          => 'cover_images',
                'type'        => 'gallery',
                'add_title'   => '添加图像',
                'edit_title'  => '编辑图像',
                'clear_title' => '清空图像',
                'default'     => false,
                'desc'        => '选择多张图片作为商品封面图 (正方形图片效果最佳)',
            ),
            array(
                'title'        => '封面视频',
                'id'           => 'cover_videos',
                'class'        => '',
                'type'         => 'repeater',
                'button_title' => '添加视频',
                'desc'         => '封面视频会和封面图片同步显示，同时视频封面和图片封面的顺序保持一致，所以必须先设置图片封面，且图片数量必须大于等于视频数量',
                'min'          => 0,
                'fields'       => array(
                    array(
                        'id'          => 'url',
                        'type'        => 'upload',
                        'preview'     => false,
                        'library'     => 'video',
                        'placeholder' => '选择视频或填写视频地址',
                        'default'     => '',
                    ),
                ),
            ),
            array(
                'title'        => '商品参数',
                'id'           => 'params',
                'class'        => 'mini-flex-repeater',
                'type'         => 'repeater',
                'button_title' => '添加商品参数',
                'desc'         => '显示在商品详情页的参数，例如：规格、材质、尺寸等。你可以<a target="_blank" href="' . zib_get_admin_csf_url('商城商品/商品参数') . '">主题设置</a>中配置默认参数，每次新建商品时候，此处会自动调用',
                'min'          => 0,
                'default'      => _pz('shop_product_params_default', array()),
                'fields'       => array(
                    array(
                        'title' => '参数名称',
                        'id'    => 'name',
                        'type'  => 'text',
                    ),
                    array(
                        'title' => '参数值',
                        'id'    => 'value',
                        'type'  => 'text',
                    ),
                ),
            ),
            array(
                'title'       => '自定义主图',
                'desc'        => '默认会使用第一张封面图作为主图，如需要单独设置与第一张封面图片不同的主图，则在此自定义',
                'id'          => 'main_image',
                'type'        => 'upload',
                'preview'     => true,
                'library'     => 'image',
                'placeholder' => '选择图片或填写图片地址(正方形图片效果最佳)',
                'default'     => '',
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('价格&选项', 'zibll'),
        'fields' => array(
            array(
                'id'      => 'pay_modo',
                'title'   => '价格类型',
                'default' => '0',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    '0'      => __('普通商品（金钱购买）', 'zibll'),
                    'points' => __('积分商品（积分兑换，依赖于用户积分功能）', 'zibll'),
                ),
            ),
            array(
                'id'      => 'start_price',
                'title'   => '起始价格',
                'default' => '0',
                'desc'    => '起始价格为商品的默认价格，实际售价系统会根据商品的选项、优惠等自动调整(如果有多个商品选项，建议为不同规格的最低价)',
                'type'    => 'number',
            ),
            array(
                'id'           => 'product_options',
                'type'         => 'repeater',
                'title'        => '商品选项',
                'subtitle'     => '添加商品选项类型',
                'button_title' => '添加选项类型',
                'desc'         => '<div class="c-yellow">注意：如果设置了<code>按选项配置库存/按选项发货</code>功能，每次修改选项后，必须<code class="c-red">保存后并刷新页面</code>再重新配置<code>按选项配置库存/按选项发货</code>功能，否则数据会出错</div>',
                'default'      => array(),
                'fields'       => array(
                    array(
                        'id'    => 'name',
                        'type'  => 'text',
                        'class' => 'mini-input',
                        'title' => '选项名称(必填)',
                    ),
                    array(
                        'id'      => 'view_mode',
                        'type'    => 'button_set',
                        'desc'    => '注意：如果设置为图片，请确保当前选项下的选项值都有图片',
                        'title'   => '默认显示样式',
                        'default' => 'list',
                        'options' => array(
                            'list' => '列表',
                            'img'  => '图片',
                        ),
                    ),
                    array(
                        'id'           => 'opts',
                        'type'         => 'group',
                        'title'        => ' ',
                        'subtitle'     => '选项项目',
                        'class'        => '',
                        'button_title' => '添加当前选项值',
                        'fields'       => array(
                            array(
                                'id'    => 'name',
                                'type'  => 'text',
                                'title' => '名称(必填)',
                            ),
                            array(
                                'id'    => 'price_change',
                                'type'  => 'number',
                                'title' => '价格变化',
                                'desc'  => '当前选项价格相对于<code>起始价格</code>的变化值，正数为增加，负数为减少',
                            ),
                            array(
                                'id'    => 'image',
                                'type'  => 'upload',
                                'title' => '图片',
                                'desc'  => '如果需要设置选项图片，请确保当前选项下都设置了图片（正方形图片效果最佳）',
                            ),
                        ),
                    ),
                ),
            ),
            zib_shop_csf_module::rebate('product'),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('库存&限购', 'zibll'),
        'fields' => array(
            zib_shop_csf_module::limit_buy('product'),
            array(
                'id'      => 'stock_type',
                'type'    => 'radio',
                'inline'  => true,
                'title'   => '库存配置类型',
                'desc'    => '卡密类型的自动发货内容可开启<code>自动获取库存</code>功能，开启后会覆盖此处配置',
                'default' => 'all',
                'options' => array(
                    'all'  => '统一总库存',
                    'opts' => '按选项设置库存',
                ),
            ),
            array(
                'dependency' => array('stock_type', '==', 'all'),
                'id'         => 'stock_all',
                'type'       => 'number',
                'title'      => '库存总数量',
                'desc'       => '统一总库存则不区分商品选项，-1为无限库存，0为无库存无法购买',
                'default'    => -1,
                'min'        => -1,
                'step'       => 1,
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array('stock_type', '==', 'opts'),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<b>重要提醒：</b>如果您修改了商品选项，请务必先<code class="c-red">保存后并刷新页面</code>再重新配置下方库存，<code class="c-red">否则会出错</code>',
            ),
            array(
                'title'      => '库存数量',
                'dependency' => array('stock_type', '==', 'opts'),
                'id'         => 'stock_opts',
                'type'       => 'fieldset',
                'desc'       => '-1为无限库存，0为无库存无法购买',
                'fields'     => zib_shop_get_metabox_product_opts_cfs_fields([
                    'id_prefix' => '',
                    'class'     => 'title-auto-width',
                    'default'   => -1,
                    'min'       => -1,
                    'step'      => 1,
                    'type'      => 'spinner',
                ]),
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('发货&物流', 'zibll'),
        'fields' => array(

            array(
                'title'        => '用户必留信息',
                'id'           => 'user_required',
                'class'        => 'mini-flex-repeater',
                'type'         => 'repeater',
                'button_title' => '添加必留类型',
                'desc'         => '用户下单时必须要填写的信息，例如话费充值要求用户必留手机号等',
                'min'          => 0,
                'default'      => [],
                'fields'       => array(
                    array(
                        'title' => '必留*',
                        'id'    => 'name',
                        'type'  => 'text',
                    ),
                    array(
                        'title' => '说明文字',
                        'id'    => 'desc',
                        'type'  => 'text',
                    ),
                ),
            ),

            array(
                'id'      => 'shipping_type',
                'title'   => '发货类型',
                'default' => _pz('shop_product_shipping_type', 'express'),
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'express' => '物流快递发货',
                    'auto'    => '自动发货(虚拟商品)',
                    'manual'  => '手动发货(虚拟商品)',
                ),
            ),
            array(
                'dependency' => [array('shipping_type', '==', 'express'), array('pay_modo', '==', 'points', 'all')],
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '当前商品为积分兑换，运费只能为0，如果您设置了运费，则无效',
            ),
            zib_shop_csf_module::guest_buy('product'),
            zib_shop_csf_module::email_fill('product'),
            array(
                'dependency'  => array('shipping_type', '!=', 'express'),
                'id'          => 'shipping_delivery_desc',
                'title'       => '发货标题',
                'subtitle'    => '',
                'placeholder' => '简短几个字描述发货说明，例如24小时发货、预计3-5天发货等',
                'type'        => 'text',
                'default'     => '',
            ),
            array(
                'dependency' => array('shipping_type', '==', 'auto'),
                'title'      => ' ',
                'subtitle'   => '自动发货配置',
                'sanitize'   => false,
                'desc'       => '',
                'id'         => 'auto_delivery',
                'type'       => 'fieldset',
                'class'      => 'compact',
                'fields'     => array(
                    array(
                        'id'      => 'type',
                        'type'    => 'button_set',
                        'default' => 'fixed',
                        'options' => array(
                            'fixed'      => '固定内容',
                            'invit_code' => '邀请码',
                            'card_pass'  => '卡密',
                            'opts'       => '按选项分别配置',
                        ),
                    ),
                    array(
                        'dependency'  => array('type', '==', 'fixed'),
                        'id'          => 'fixed_content',
                        'type'        => 'textarea',
                        'placeholder' => '请输入需要发送给用户的内容，支持html代码，注意格式规范',
                        'sanitize'    => false,
                        'default'     => '',
                        'attributes'  => array(
                            'rows' => 3,
                        ),
                    ),
                    array(
                        'dependency' => array('type', '==', 'invit_code'),
                        'id'         => 'invit_code_mode',
                        'type'       => 'radio',
                        'inline'     => true,
                        'default'    => '',
                        'options'    => array(
                            ''         => '选择已创建的邀请码',
                            'auto_add' => '自动创建邀请码并发货',
                        ),
                    ),
                    array(
                        'dependency' => array('type', 'any', 'invit_code,card_pass'),
                        'id'         => 'auto_stock',
                        'type'       => 'switcher',
                        'default'    => true,
                        'title'      => '自动获取库存',
                        'label'      => '自动获取库存数量，关闭后则以设置的库存数量为准',
                    ),
                    array(
                        'dependency' => array('type|invit_code_mode', '==|==', 'invit_code|'),
                        'id'         => 'invit_code_key',
                        'type'       => 'text',
                        'title'      => '邀请码标识',
                        'subtitle'   => '根据标识筛选邀请码',
                        'desc'       => '如还未创建邀请码，请先创建邀请后在此处输入邀请码的备注 | <a target="_blank" href="' . admin_url('users.php?page=invit_code') . '">管理邀请码</a>',
                        'default'    => '',
                    ),
                    array(
                        'dependency' => array('type|invit_code_mode', '==|==', 'invit_code|auto_add'),
                        'id'         => 'invit_code_auto_add_opts',
                        'type'       => 'fieldset',
                        'fields'     => array(
                            array(
                                'title'    => '使用奖励',
                                'subtitle' => '',
                                'id'       => 'auto_reward',
                                'type'     => 'fieldset',
                                'fields'   => CFS_Module::invit_code_reward(),
                            ),
                            array(
                                'title'   => '标识前缀',
                                'desc'    => '对自动生成的邀请码做标记标识，方便后期查找管理',
                                'id'      => 'auto_remarks',
                                'default' => '',
                                'type'    => 'text',
                            ),
                        ),
                    ),
                    array(
                        'dependency' => array('type', '==', 'card_pass'),
                        'id'         => 'card_pass_key',
                        'type'       => 'text',
                        'title'      => '卡密标识   ',
                        'subtitle'   => '根据标识筛选卡密',
                        'desc'       => '如还未创建，请先创建后在此处输入卡密的标识 | <a target="_blank" href="' . admin_url('admin.php?page=zibpay_charge_card_page') . '">管理卡密</a> | <a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">管理优惠码</a>',
                        'default'    => '',
                    ),
                    array(
                        'dependency' => array('type', '==', 'opts'),
                        'type'       => 'submessage',
                        'style'      => 'warning',
                        'content'    => '<b>重要提醒：</b>如果您修改了商品选项，请务必先<code class="c-red">保存后并刷新页面</code>再重新配置下方参数，<code class="c-red">否则会出错</code>',
                    ),
                    array(
                        'dependency' => array('type', '==', 'opts'),
                        'id'         => 'opts',
                        'type'       => 'fieldset',
                        'fields'     => zib_shop_get_metabox_product_opts_cfs_fields([
                            'sanitize' => false,
                            'desc'     => '',
                            'type'     => 'fieldset',
                            'fields'   => array(
                                array(
                                    'id'      => 'opts_type',
                                    'type'    => 'button_set',
                                    'inline'  => true,
                                    'default' => 'fixed',
                                    'options' => array(
                                        'fixed'      => '固定内容',
                                        'invit_code' => '邀请码',
                                        'card_pass'  => '卡密',
                                    ),
                                ),
                                array(
                                    'dependency'  => array('opts_type', '==', 'fixed'),
                                    'id'          => 'fixed_content',
                                    'type'        => 'textarea',
                                    'placeholder' => '请输入发货内容，支持html代码，注意格式规范',
                                    'sanitize'    => false,
                                    'default'     => '',
                                    'attributes'  => array(
                                        'rows' => 3,
                                    ),
                                ),
                                array(
                                    'dependency' => array('opts_type', '==', 'invit_code'),
                                    'id'         => 'opts_invit_code_mode',
                                    'type'       => 'radio',
                                    'inline'     => true,
                                    'default'    => '',
                                    'options'    => array(
                                        ''         => '选择已创建的邀请码',
                                        'auto_add' => '自动创建邀请码并发货',
                                    ),
                                ),
                                array(
                                    'dependency' => array('opts_type', 'any', 'invit_code,card_pass'),
                                    'id'         => 'auto_stock',
                                    'type'       => 'switcher',
                                    'default'    => true,
                                    'title'      => '自动获取库存',
                                    'label'      => '自动获取库存数量，关闭后则以设置的库存数量为准',
                                ),
                                array(
                                    'dependency' => array('opts_type|opts_invit_code_mode', '==|==', 'invit_code|'),
                                    'id'         => 'invit_code_key',
                                    'type'       => 'text',
                                    'title'      => '邀请码标识',
                                    'subtitle'   => '根据标识筛选邀请码',
                                    'desc'       => '如还未创建邀请码，请先创建邀请后在此处输入邀请码的备注 | <a target="_blank" href="' . admin_url('users.php?page=invit_code') . '">管理邀请码</a>',
                                    'default'    => '',
                                ),
                                array(
                                    'dependency' => array('opts_type|opts_invit_code_mode', '==|==', 'invit_code|auto_add'),
                                    'id'         => 'invit_code_auto_add_opts',
                                    'type'       => 'fieldset',
                                    'fields'     => array(
                                        array(
                                            'title'    => '使用奖励',
                                            'subtitle' => '',
                                            'id'       => 'auto_reward',
                                            'type'     => 'fieldset',
                                            'fields'   => CFS_Module::invit_code_reward(),
                                        ),
                                        array(
                                            'title'   => '标识前缀',
                                            'desc'    => '对自动生成的邀请码做标记标识，方便后期查找管理',
                                            'id'      => 'auto_remarks',
                                            'default' => '',
                                            'type'    => 'text',
                                        ),
                                    ),
                                ),
                                array(
                                    'dependency'  => array('opts_type', '==', 'card_pass'),
                                    'id'          => 'card_pass_key',
                                    'type'        => 'text',
                                    'default'     => '',
                                    'placeholder' => '请输入卡密标识',
                                ),
                            ),
                        ]),
                    ),
                ),
            ),
            array(
                'dependency' => array('shipping_type', '==', 'auto'),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<code>自动发货(虚拟内容)</code>主要用于文本内容自动发送，用户付款后会将设置的信息通过私信和邮件的方式自动发送给用户
                <div class="c-yellow"><b>注意事项：</b>
                    <li>1.如果选择固定内容，请配合限购功能使用，因为用户即使购买多份，发货内容也是相同的，容易出现投诉问题</li>
                    <li>2.如果选择卡密，填写对应的标识以搜索并发送给给用户，如未创建卡密，则需先创建</li>
                    <li>3.选择卡密时，同时支持卡密和优惠码，选择邀请码时，支持自动创建或填写标识来选择已创建的邀请码</li>
                    <li>4.请正确的配置库存，已避免自动发货失败</li>
                    <li><a target="_blank" href="' . admin_url('users.php?page=invit_code') . '">管理邀请码</a> | <a target="_blank" href="' . admin_url('admin.php?page=zibpay_charge_card_page') . '">管理卡密</a> | <a target="_blank" href="' . admin_url('admin.php?page=zibpay_coupon_page') . '">管理优惠码</a></li>
                </div>',
            ),
            array(
                'dependency' => array('shipping_type', '==', 'manual'),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<code>手动发货(虚拟内容)</code>用于需要商家手动处理的虚拟商品，例话费充值、软件定制、技术服务等
                <div class="c-yellow"><b>注意事项：</b>
                    <li>此方式不会要求用户填写收货地址和邮箱</li>
                     <li>可以配合<code>用户必留信息</code>功能使用，例如话费充值要求用户必留手机号</li>
                </div>',
            ),
            zib_shop_csf_module::shipping_fee('product', array('shipping_type', '==', 'express')),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('售后&服务', 'zibll'),
        'fields' => array(
            zib_shop_csf_module::after_sale('product'),
            zib_shop_csf_module::service('product'),
        ),
    ));

    CSF::createSection($prefix, array(
        'title'  => __('UI&样式', 'zibll'),
        'fields' => array(
            zib_shop_csf_module::single_tab('product'),
            zib_shop_csf_module::content_layout('product'),
            zib_shop_csf_module::content_show_bg('product'),
        ),
    ));
}

if (!empty($_GET['post_type']) && $_GET['post_type'] === 'shop_product') {
    add_action('bulk_edit_custom_box', 'zib_shop_bulk_edit_custom_box_product', 50, 2);
    add_action('quick_edit_custom_box', 'zib_shop_bulk_edit_custom_box_product', 50, 2);
}
add_action('save_post', 'zib_shop_bulk_edit_save_post_product', 10, 3);
function zib_shop_bulk_edit_custom_box_product($column_name, $post_type)
{
    $permissible_posts_type = ['shop_product'];
    $_id                    = 'shop';

    if (!in_array($post_type, $permissible_posts_type) || $column_name !== 'taxonomy-shop_tag') {
        return;
    }

    $fields = array(
        array(
            'title'   => '优惠活动',
            'id'      => 'shop_discount',
            'options' => zib_shop_get_discount_meta_options(),
            'type'    => 'checkbox',
            'inline'  => true,
            'desc'    => '最多显示200个优惠活动 <a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=shop_discount&post_type=shop_product') . '">管理优惠活动</a> | <a target="_blank" href="' . admin_url('post-new.php?post_type=shop_product') . '">添加优惠活动</a>',
        ),
    );
    echo zib_get_quick_edit_custom_input($fields, $_id);
}

function zib_shop_bulk_edit_save_post_product($post_ID, $post, $update)
{

    $permissible_posts_type = ['shop_product'];
    $_id                    = 'shop';
    $screen                 = 'edit-shop_product';
    if (!$update || !in_array($post->post_type, $permissible_posts_type) || empty($_REQUEST['zib_bulk_edit'][$_id]) || empty($_REQUEST['screen']) || $_REQUEST['screen'] !== $screen) {
        return;
    }

    $zibpay_bulk_edit = $_REQUEST['zib_bulk_edit'][$_id];
    foreach ($zibpay_bulk_edit as $field_id => $field_value) {
        if ($field_value === 'ignore' || (isset($field_value['operation']) && $field_value['operation'] === 'ignore')) {
            continue;
        }

        switch ($field_id) {
            case 'shop_discount':
                $discount_ids = !empty($field_value['val']) ? array_map('intval', (array) $field_value['val']) : array();
                wp_set_post_terms($post_ID, $discount_ids, 'shop_discount', false);
                break;
        }
    }

}

//为商品添加优惠活动选择
function zib_shop_meta_box_product_discount($post)
{
    //获取商品的优惠活动
    $discount     = get_the_terms($post->ID, 'shop_discount');
    $discount_ids = array();
    if ($discount) {
        foreach ($discount as $item) {
            $discount_ids[] = $item->term_id;
        }
    }

    $fields = array(
        array(
            'id'      => 'meta_box_product_discount',
            'options' => zib_shop_get_discount_meta_options(),
            'type'    => 'checkbox',
            'inline'  => true,
            'default' => $discount_ids,
            'desc'    => '最多显示200个优惠活动<br><a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=shop_discount&post_type=shop_product') . '">管理优惠活动</a>',
        ),
    );

    $csf_args = array(
        'class'  => '',
        'value'  => [],
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
        'hidden' => array(
            array(
                'name'  => 'meta_box_save_product_discount', //必要，用于isset判断
                'value' => 'on',
            ),
        ),
    );

    ZCSF::instance('product_discount', $csf_args);
}

function zib_shop_get_discount_meta_options()
{
    //获取所有的优惠活动
    $terms = get_terms(array(
        'taxonomy'   => 'shop_discount',
        'hide_empty' => false,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'number'     => 200, //最多显示200个优惠活动
    ));
    if (is_wp_error($terms)) {
        return array();
    }
    $options = array();
    foreach ($terms as $term) {
        $discount_data = zib_shop_get_discount_data($term);
        $name          = $discount_data['name'];
        if (!empty($discount_data['discount_error'])) {
            switch ($discount_data['discount_error']) {
                case 'config_error':
                    $name .= '[配置无效]';
                    break;
                case 'time_limit_start':
                    $name .= '[未开始]';
                    break;
                case 'time_limit_end':
                    $name .= '[已结束]';
                    break;
                default:
                    $name .= '[失效]';
                    break;
            }
        } elseif (!empty($discount_data['discount_type'])) {
            switch ($discount_data['discount_type']) {
                case 'reduction':
                    $discount_text = '立减' . $discount_data['reduction_amount'];
                    break;
                case 'discount':
                    $discount_text = $discount_data['discount_amount'] . '折';
                    break;
                case 'gift':
                    $discount_text = '赠品';
                    break;
            }

            if ($discount_text !== $discount_data['name']) {
                $name .= '[' . $discount_text . ']';
            }
        }

        if (!empty($discount_data['is_important'])) {
            $name .= '[重点]';
        }

        $options[$term->term_id] = $name;
    }

    return $options;
}

function zib_shop_add_meta_box_product_discount()
{
    add_meta_box('shop_product_discount', '优惠活动', 'zib_shop_meta_box_product_discount', array('shop_product'), 'side', 'high');
}
add_action('add_meta_boxes', 'zib_shop_add_meta_box_product_discount');

function zib_shop_save_meta_box_product($post_id)
{
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return $post_id;
    }

    if (!empty($_POST['meta_box_save_product_discount'])) {
        $discount_ids = !empty($_POST['meta_box_product_discount']) ? array_map('intval', (array) $_POST['meta_box_product_discount']) : array();
        wp_set_post_terms($post_id, $discount_ids, 'shop_discount', false);
    }
}
add_action('save_post', 'zib_shop_save_meta_box_product');

function zib_shop_get_metabox_product_opts_cfs_fields($args = array())
{

    //页面限制：新增商品、编辑商品
    if (strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') === false && strpos($_SERVER['SCRIPT_NAME'], 'post.php') === false) {
        return;
    }

    $post_id = !empty($_GET['post']) ? (int) $_GET['post'] : null;

    if (!$post_id) {
        return array(array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '您当前正在新建文章，请先配置好商品选项，<code>保存并刷新页面后</code>再配置此处',
        ));
    }

    $product_options = zib_shop_get_product_config($post_id, 'product_options') ?: array();
    $stock_opts      = array();

    // 获取所有选项组合
    $option_names  = array();
    $option_values = array();
    $split_symbol  = '$|$'; //定义一个特殊分割符号，避免选项名称中包含

    foreach ($product_options as $key => $option) {
        if (!isset($option['opts'][0]['name'])) {
            continue;
        }
        $option_names[] = $option['name'] ?? '';
        $values         = array();
        foreach ($option['opts'] as $key_2 => $opt) {
            $values[] = $key . $split_symbol . $key_2 . $split_symbol . $opt['name'];
        }
        $option_values[] = $values;
    }

    if (empty($option_values[0])) {
        return array(array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '未找到商品选项，如需设置此处，请先配置好商品选项，<code>保存并刷新页面后</code>再配置此处',
        ));
    }

    // 生成所有组合
    $combinations = array(array()); // 初始化为包含一个空数组
    foreach ($option_values as $key => $values) {
        $temp = array();
        foreach ($combinations as $combination) {
            foreach ($values as $value) {
                $temp[] = array_merge($combination, array($value));
            }
        }
        $combinations = $temp; // 更新组合为新生成的组合
    }

    // 构建库存选项
    foreach ($combinations as $combination) {
        $title = '';
        $id    = $args['id_prefix'] ?? ''; //前缀
        foreach ($combination as $value) {
            //分割成数组
            $value = explode($split_symbol, $value);
            $title .= '|' . $value[2];
            $id .= zib_shop_product_options_key_splicing($value[0], $value[1]);
        }

        $stock_opts[] = array_merge(array(
            'id'       => $id,
            'type'     => 'text',
            'title'    => ' ',
            'subtitle' => trim($title, '|'),
        ), $args);
    }

    return $stock_opts;
}
