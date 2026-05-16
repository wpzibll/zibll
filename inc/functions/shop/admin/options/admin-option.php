<?php
/*
* @Author: Qinver
* @Url: zibll.com
* @Date: 2025-02-16 21:10:36
 * @LastEditTime : 2026-04-11 16:24:59
* @Email: 770349780@qq.com
* @Project: Zibll子比主题
* @Description: 商城功能 - 后台配置文件
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

function zib_shop_csf_admin_options()
{

    $prefix = 'zibll_options';

    //页面限制：后台主题配置
    //非自身保存的ajax不执行
    $no_create = ((wp_doing_ajax() && (empty($_POST['action']) || !strstr($_POST['action'], 'csf_' . $prefix))) || (!wp_doing_ajax() && (empty($_GET['page']) || $_GET['page'] !== $prefix)));
    if ($no_create) {
        return;
    }

    $new_badge = zib_get_csf_option_new_badge();
    $imagepath = get_template_directory_uri() . '/img/';

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => '实物商城',
        'icon'        => 'fa fa-fw fa-cart-arrow-down',
        'description' => '',
        'fields'      => array(
            array(
                'content' => '<p><b>商城系统</b>是独立的商品发布系统，支持实物、虚拟等任意商品，支持发布、优惠、下单、发货、售后、评价等全流程功能，UI设计精美，购物体验对标淘宝京东等大厂商城APP</p>
            <li>销售商品需先配置好<a href="' . zib_get_admin_csf_url('支付付费/收款接口') . '">收款接口</a></li>
            <li>依赖于用户登录注册功能，如果关闭了<a href="' . zib_get_admin_csf_url('用户互动/注册登录') . '">注册登录功能</a>，则请同时关闭此功能</li>
            <li><a target="_blank" href="https://www.zibll.com/zibll_word/%e5%95%86%e5%9f%8e%e5%8a%9f%e8%83%bd">查看官方教程</a></li>
            ',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            array(
                'title'   => '商城系统',
                'label'   => '启用实物商城功能',
                'id'      => 'shop_s',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'title'       => '商城首页链接',
                'id'          => 'shop_home_url',
                'default'     => '',
                'desc'        => '面包屑导航等功能依赖于此处配置，您可以新建一个页面，作为商城首页，并在此处填写对应链接。<a target="_blank" href="https://www.zibll.com/39214.html">查看官方教程</a>',
                'type'        => 'text',
                'placeholder' => 'https://',
            ),
            array(
                'title'    => '商品列表配置',
                'subtitle' => '分类页/搜索页/用户页的列表默认样式，小工具模块可单独设置样式',
                'id'       => 'shop_list_opt',
                'type'     => 'fieldset',
                'fields'   => array(
                    array(
                        'title'   => __('默认排序方式', 'zibll'),
                        'id'      => 'orderby',
                        'options' => zib_shop_csf_module::product_orderby_options(),
                        'type'    => 'select',
                        'default' => 'date',
                    ),
                    array(
                        'title'   => '单页显示数量',
                        'id'      => 'count',
                        'class'   => '',
                        'default' => 12,
                        'max'     => 20,
                        'min'     => 4,
                        'step'    => 1,
                        'unit'    => '个',
                        'type'    => 'spinner',
                    ),
                    array(
                        'id'      => 'paginate',
                        'title'   => '翻页模式',
                        'default' => 'ajax',
                        'type'    => 'radio',
                        'inline'  => true,
                        'options' => array(
                            'ajax'   => __('AJAX追加列表翻页', 'zibll'),
                            'number' => __('数字翻页按钮', 'zibll'),
                        ),
                    ),
                    array(
                        'dependency' => array('paginate', '==', 'ajax'),
                        'title'      => ' ',
                        'subtitle'   => 'AJAX翻页自动加载',
                        'class'      => 'compact',
                        'id'         => 'ias_s',
                        'type'       => 'switcher',
                        'label'      => '页面滚动到列表尽头时，自动加载下一页',
                        'default'    => true,
                    ),
                    array(
                        'dependency' => array('paginate|ias_s', '==|!=', 'ajax|'),
                        'title'      => ' ',
                        'subtitle'   => '自动加载页数',
                        'desc'       => 'AJAX翻页自动加载最多加载几页（为0则不限制，直到加载全部商品）',
                        'id'         => 'ias_max',
                        'class'      => 'compact',
                        'default'    => 3,
                        'max'        => 10,
                        'min'        => 0,
                        'step'       => 1,
                        'unit'       => '页',
                        'type'       => 'spinner',
                    ),
                    zib_shop_csf_module::list_style('默认商品列表UI配置'),
                ),
            ),

            array(
                'title'    => '客服配置',
                'subtitle' => '',
                'id'       => 'shop_author_contact_opt',
                'type'     => 'fieldset',
                'desc'     => '商城的联系客服按钮可以添加多种联系方式，但是至少需要添加一个<br>私信功能需要开启<a href="' . zib_get_admin_csf_url('用户互动/消息通知') . '">站内通知和私信</a>功能',
                'fields'   => array(
                    array(
                        'title'   => __('站内私信', 'zibll'),
                        'id'      => 'msg_s',
                        'type'    => 'switcher',
                        'default' => true,
                    ),
                    array(
                        'dependency' => array('msg_s', '!=', ''),
                        'id'         => 'msg_name',
                        'class'      => 'compact',
                        'title'      => ' ',
                        'subtitle'   => '显示名称',
                        'default'    => '立即联系',
                        'type'       => 'text',
                    ),
                    array(
                        'dependency' => array('msg_s', '!=', ''),
                        'id'         => 'msg_desc',
                        'class'      => 'compact',
                        'title'      => ' ',
                        'subtitle'   => '说明或备注',
                        'type'       => 'textarea',
                        'sanitize'   => false,
                        'default'    => '7*24小时在线，专业为您服务',
                        'attributes' => array(
                            'rows' => 1,
                        ),
                    ),
                    array(
                        'title'        => ' ',
                        'subtitle'     => '客服联系方式',
                        'id'           => 'more',
                        'type'         => 'group',
                        'desc'         => '注意：如需要删除此处所有联系方式，仅保留私信，请在此项中仅保留一个联系方式并将名称留空即可',
                        'button_title' => '添加客服联系方式',
                        'min'          => 0,
                        'default'      => array(
                            array(
                                'name' => '微信客服',
                                'desc' => '工作日9:00-18:00在线',
                                'icon' => 'fa fa-weixin',
                                'link' => '',
                                'img'  => $imagepath . 'qrcode.png',
                            ),
                            array(
                                'name' => 'QQ客服',
                                'desc' => '工作日9:00-18:00在线',
                                'icon' => 'fa fa-qq',
                                'link' => 'https://wpa.qq.com/msgrd?v=3&site=qq&menu=yes&uin=1234567788',
                                'img'  => '',
                            ),
                            array(
                                'name' => '电话联系',
                                'desc' => '400-888-8888转88',
                                'icon' => 'fa fa-phone',
                                'link' => 'tel://10086',
                                'img'  => '',
                            ),
                        ),
                        'fields'       => array(
                            array(
                                'title' => '名称(必填)',
                                'id'    => 'name',
                                'type'  => 'text',
                            ),
                            array(
                                'title'      => '说明或备注',
                                'id'         => 'desc',
                                'sanitize'   => false,
                                'type'       => 'textarea',
                                'class'      => 'compact',
                                'default'    => '',
                                'attributes' => array(
                                    'rows' => 1,
                                ),
                            ),
                            array(
                                'id'           => 'icon',
                                'class'        => 'compact',
                                'type'         => 'icon',
                                'title'        => '图标',
                                'button_title' => '选择图标',
                                'default'      => 'fa fa-heart',
                            ),
                            array(
                                'id'    => 'link',
                                'type'  => 'text',
                                'title' => '跳转链接',
                            ),
                            array(
                                'id'      => 'img',
                                'class'   => 'compact',
                                'title'   => __('二维码图片', 'zibll'),
                                'default' => '',
                                'library' => 'image',
                                'type'    => 'upload',
                            ),
                        ),
                    ),
                ),
            ),

        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => '商品详情页',
        'icon'        => 'fa fa-fw fa-bookmark-o',
        'description' => '',
        'fields'      => array(
            array(
                'title'   => __('面包屑导航', 'zibll'),
                'id'      => 'shop_breadcrumbs_s',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('shop_breadcrumbs_s', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('显示网站首页', 'zibll'),
                'id'         => 'shop_breadcrumbs_home',
                'class'      => 'compact',
                'type'       => 'switcher',
                'default'    => true,
            ),
            array(
                'dependency' => array('shop_breadcrumbs_s', '!=', ''),
                'title'      => ' ',
                'label'      => '如果您将商城首页设置为网站首页，那么请关闭此处',
                'subtitle'   => __('显示商城首页', 'zibll'),
                'id'         => 'shop_breadcrumbs_shop_home',
                'class'      => 'compact',
                'type'       => 'switcher',
                'default'    => false,
            ),
            array(
                'dependency' => array('shop_breadcrumbs_s|shop_breadcrumbs_shop_home', '!=|!=', '|'),
                'id'         => 'shop_breadcrumbs_shop_home_name',
                'desc'       => '请确保已经创建商城首页并设置好<a href="' . zib_get_admin_csf_url('商城商品/实物商城') . '">商城首页链接</a>',
                'class'      => 'compact mini-input',
                'title'      => ' ',
                'subtitle'   => '商城首页显示名称',
                'default'    => '商城',
                'type'       => 'text',
            ),
            zib_shop_csf_module::content_layout('admin'),
            zib_shop_csf_module::content_show_bg('admin'),
            zib_shop_csf_module::single_tab('admin'),
            zib_shop_csf_module::content_after('admin'),
            array(
                'title'   => __('详情页相关推荐', 'zibll'),
                'id'      => 'shop_single_related_s',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('shop_single_related_s', '!=', ''),
                'title'      => ' ',
                'subtitle'   => '相关推荐参数配置',
                'id'         => 'shop_single_related_opt',
                'class'      => 'compact',
                'type'       => 'fieldset',
                'fields'     => array(
                    array(
                        'title'   => __('标题', 'zibll'),
                        'id'      => 'title',
                        'default' => '推荐',
                        'type'    => 'text',
                    ),
                    array(
                        'title'   => '关联类型',
                        'id'      => 'type',
                        'type'    => 'checkbox',
                        'inline'  => true,
                        'default' => ['cat', 'discount', 'tag'],
                        'options' => array(
                            'cat'      => '分类',
                            'discount' => '活动',
                            'tag'      => '标签',
                        ),
                    ),
                    array(
                        'title'   => __('排序方式', 'zibll'),
                        'id'      => 'orderby',
                        'options' => zib_shop_csf_module::product_orderby_options(),
                        'type'    => 'select',
                        'default' => 'views',
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
                    zib_shop_csf_module::list_style('详情页相关推荐列表UI配置'),
                ),
            ),
        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => '商品参数',
        'icon'        => 'fa fa-fw fa-trello',
        'description' => '',
        'fields'      => array(
            array(
                'content' => '商品的大多数参数均支持<b>参数继承</b>，系统获取配置时候会自动按照商品、商品的分类、主题设置的参数依次获取 | <a target="_blank" href="https://www.zibll.com/39217.html">查看官方教程</a>',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),
            array(
                'title'       => '商品默认主图',
                'id'          => 'shop_main_image_default',
                'default'     => '',
                'preview'     => true,
                'library'     => 'image',
                'placeholder' => '选择图片或填写图片地址(正方形图片效果最佳)',
                'type'        => 'upload',
            ),
            array(
                'title'        => '商品参数模板',
                'id'           => 'shop_product_params_default',
                'class'        => 'mini-flex-repeater',
                'type'         => 'repeater',
                'button_title' => '添加商品参数',
                'desc'         => '默认商品参数模板，每次新建商品时候，会自动调用',
                'min'          => 0,
                'default'      => array(),
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
            zib_shop_csf_module::limit_buy('admin'),
            zib_shop_csf_module::shipping_fee('admin'),
            zib_shop_csf_module::guest_buy('admin'),
            zib_shop_csf_module::email_fill('admin'),
            zib_shop_csf_module::rebate('admin'),
            zib_shop_csf_module::after_sale('admin'),
            zib_shop_csf_module::service('admin'),
        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => '物流&发货',
        'icon'        => 'fa fa-fw fa-truck',
        'description' => '',
        'fields'      => array(
            array(
                'id'      => 'express_api_sdk',
                'default' => 'kuaidi100',
                'title'   => '快递查询接口',
                'desc'    => '<a target="_blank" href="https://www.zibll.com/39206.html">查看官方教程</a>',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'kuaidi100' => __('快递100', 'zibll'),
                    'aliyun'    => __('阿里云', 'zibll'),
                    'kdniao'    => __('快递鸟', 'zibll'),
                ),
            ),
            array(
                'title'   => '快递查询间隔',
                'desc'    => '通过接口查询快递信息，间隔时间越短，查询越频繁',
                'id'      => 'shop_express_query_interval',
                'default' => 240,
                'max'     => 100000000,
                'min'     => 10,
                'step'    => 10,
                'unit'    => '分钟',
                'type'    => 'spinner',
            ),
            array(
                'id'         => 'express_kdniao_opt',
                'type'       => 'accordion',
                'title'      => '快递鸟',
                'subtitle'   => '接口配置',
                'accordions' => array(
                    array(
                        'title'  => '快递鸟接口配置',
                        'fields' => array(
                            array(
                                'title'   => '用户ID',
                                'id'      => 'appid',
                                'default' => '',
                                'type'    => 'text',
                            ),
                            array(
                                'title'   => 'API KEY',
                                'id'      => 'apikey',
                                'default' => '',
                                'type'    => 'text',
                                'desc'    => '获取地址：<a href="https://biz.kdniao.com/account-center/api" target="_blank">快递鸟</a>',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'id'         => 'express_kuaidi100_opt',
                'type'       => 'accordion',
                'title'      => '快递100',
                'subtitle'   => '接口配置',
                'accordions' => array(
                    array(
                        'title'  => '快递100接口配置',
                        'fields' => array(
                            array(
                                'title'   => 'API Key',
                                'id'      => 'key',
                                'default' => '',
                                'type'    => 'text',
                            ),
                            array(
                                'title'   => 'customer',
                                'id'      => 'customer',
                                'default' => '',
                                'type'    => 'text',
                                'desc'    => '申请地址：<a href="https://api.kuaidi100.com/manager/v2/query/overview" target="_blank">快递100</a>',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'id'         => 'express_aliyun_opt',
                'type'       => 'accordion',
                'title'      => '阿里云快递',
                'subtitle'   => '接口配置',
                'accordions' => array(
                    array(
                        'title'  => '阿里云全球快递物流查询接口配置',
                        'fields' => array(
                            array(
                                'title'   => 'AppCode',
                                'id'      => 'appcode',
                                'default' => '',
                                'type'    => 'text',
                                'desc'    => '申请地址：<a href="https://market.aliyun.com/apimarket/detail/cmapi023201#sku=yuncode17201000019" target="_blank">全球快递物流查询</a>',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'title'   => '物流公司',
                'id'      => 'shop_shipping_company',
                'type'    => 'textarea',
                'desc'    => '添加允许发货的物流公司，用于发货时选择，用逗号隔开',
                'default' => '顺丰快递,圆通快递,中通快递,韵达快递,申通快递,菜鸟快递,邮政快递,极兔速递,德邦快递,京东快递,EMS,百世快递',
            ),
            array(
                'content' => '<div><b>快递接口测试</b>
                <br/>输入快递单号，在此发送测试快递接口</div>
                <ajaxform class="ajax-form">
                <div class="">
                    <input class="mt6 mr10" type="text" style="max-width:400px;" ajax-name="express_number" placeholder="快递单号">
                </div>
                 <div class="">
                    <input class="mt6 mr10" type="text" style="max-width:400px;" ajax-name="phone" placeholder="手机号">
                    <div class="c-yellow">(选填)部分快递需要传入收件人手机号才能查询</div>
                </div>
                <a href="javascript:;" class="but jb-yellow ajax-submit mt6"><i class="fa fa-paper-plane-o"></i>查询快递</a>
                <div class="ajax-notice mt10"></div>
                <input type="hidden" ajax-name="action" value="test_express_query">
                </ajaxform>',
                'style'   => 'warning',
                'type'    => 'submessage',
            ),

        ),
    ));

    CSF::createSection($prefix, array(
        'parent'      => 'shop',
        'title'       => '其它设置',
        'icon'        => 'fa fa-fw fa-life-ring',
        'description' => '',
        'fields'      => array(
            array(
                'title'   => __('销量显示', 'zibll'),
                'id'      => 'shop_sales_show',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    ''    => '显示',
                    'off' => '不显示',
                    'min' => '超量显示',
                ),
                'default' => '',
            ),
            array(
                'dependency' => array('shop_sales_show', '==', 'min'),
                'title'      => ' ',
                'subtitle'   => __('销量超过多少显示', 'zibll'),
                'id'         => 'shop_sales_show_min',
                'class'      => 'compact',
                'default'    => 10,
                'min'        => 0,
                'step'       => 10,
                'unit'       => '件',
                'type'       => 'spinner',
            ),

            array(
                'title'   => __('确认收货时效', 'zibll'),
                'id'      => 'order_receipt_max_day',
                'type'    => 'number',
                'desc'    => '发货后，用户最大确认收货时间，超过后将自动确认收货',
                'default' => 15,
                'min'     => 1,
                'step'    => 1,
                'unit'    => '天',
                'type'    => 'spinner',
            ),
            array(
                'title'   => __('评价时效', 'zibll'),
                'id'      => 'shop_comment_max_day',
                'type'    => 'number',
                'desc'    => '用户确认收货后，多少天以内可以评价，超时则自动好评',
                'default' => 15,
                'min'     => 1,
                'step'    => 1,
                'unit'    => '天',
                'type'    => 'spinner',
            ),
            array(
                'title'   => '评价内容占位符',
                'id'      => 'shop_comment_placeholder',
                'class'   => 'compact',
                'type'    => 'text',
                'default' => '展开说说对商品的看法吧~',
            ),
            //评论允许上传的图片数量，填0则不允许上传图片
            array(
                'title'   => '评价上传图片',
                'desc'    => '评价时允许上传的图片数量，填0则不允许上传图片',
                'class'   => 'compact',
                'id'      => 'shop_comment_img_num',
                'type'    => 'spinner',
                'default' => 6,
                'min'     => 0,
                'max'     => 20,
                'step'    => 1,
            ),
            array(
                'title'   => __('售后退货时效', 'zibll'),
                'id'      => 'order_after_sale_return_express_max_day',
                'type'    => 'number',
                'desc'    => '用户售后退货的最大发货时间，超时则自动取消售后',
                'default' => 7,
                'min'     => 1,
                'step'    => 1,
                'unit'    => '天',
                'type'    => 'spinner',
            ),
            array(
                'title'      => '售后可选原因',
                'subtitle'   => '申请售后时用户可以选择的原因',
                'id'         => 'after_sale_reason',
                'sanitize'   => false,
                'type'       => 'accordion',
                'accordions' => array(
                    array(
                        'title'  => '仅退款原因',
                        'fields' => array(
                            array(
                                'id'           => 'refund',
                                'class'        => 'mini-flex-repeater',
                                'type'         => 'repeater',
                                'button_title' => '添加原因',
                                'default'      => array(
                                    ['t' => '与商家协商一致'],
                                    ['t' => '质量问题'],
                                    ['t' => '商品不喜欢'],
                                    ['t' => '物流问题'],
                                    ['t' => '退运费'],
                                    ['t' => '其他原因'],
                                ),
                                'fields'       => array(
                                    array(
                                        'default' => '',
                                        'id'      => 't',
                                        'type'    => 'text',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    array(
                        'title'  => '退货退款原因',
                        'fields' => array(
                            array(
                                'id'           => 'refund_return',
                                'class'        => 'mini-flex-repeater',
                                'type'         => 'repeater',
                                'button_title' => '添加原因',
                                'default'      => array(
                                    ['t' => '不想要了'],
                                    ['t' => '与商家协商一致'],
                                    ['t' => '质量问题'],
                                    ['t' => '商品不喜欢'],
                                    ['t' => '物流问题'],
                                    ['t' => '其他原因'],
                                ),
                                'fields'       => array(
                                    array(
                                        'default' => '',
                                        'id'      => 't',
                                        'type'    => 'text',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            //商家模式
            array(
                'id'      => 'shop_author_show',
                'class'   => '',
                'title'   => '显示商家信息',
                'desc'    => '如果网站有多个管理员在后台发布商品，则推荐打开（暂不支持非管理员发布）<br>如需在商家的个人主页显示商品列表，请在<a href="' . zib_get_admin_csf_url('页面显示/用户主页') . '">页面显示/用户主页</a>中设置是否启用及排序',
                'default' => true,
                'type'    => 'switcher',
            ),
            //固定链接设置
            array(
                'id'      => 'shop_rewrite_suffix_html_s',
                'class'   => '',
                'title'   => '链接URL后缀.html',
                'desc'    => '商品详情页URL将以.html结尾，有利于SEO',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'title'      => '链接URL别名',
                'subtitle'   => '商品页面URL别名', //产品
                'id'         => 'shop_product_rewrite_slug',
                'default'    => 'shop',
                'class'      => 'mini-input',
                'attributes' => array(
                    'data-readonly-id' => 'shop_slug',
                    'readonly'         => 'readonly',
                ),
                'type'       => 'text',
            ),
            array(
                'title'      => ' ',
                'subtitle'   => '购物车页面URL别名', //产品
                'id'         => 'shop_cart_rewrite_slug',
                'default'    => 'cart',
                'class'      => 'compact mini-input',
                'attributes' => array(
                    'data-readonly-id' => 'shop_slug',
                    'readonly'         => 'readonly',
                ),
                'type'       => 'text',
                'desc'       => 'URL别名为开启固定链接之后对应网址的地址目录<div style="color:#ff4021;"><i class="fa fa-fw fa-info-circle fa-fw"></i>如非必要，请勿修改</div>
                <br><a href="javascript:;" class="but jb-yellow remove-readonly" readonly-id="shop_slug">我要修改</a>',
            ),
        ),
    ));
}
add_action('zib_require_end', 'zib_shop_csf_admin_options');
