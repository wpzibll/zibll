<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-02-01 12:38:24
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台商品配置项模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class zib_shop_csf_module
{
    //商品排序方式
    public static function product_orderby_options($before = [])
    {

        return array_merge(
            array(
                'modified'       => '更新时间',
                'date'           => '发布时间',
                'views'          => '浏览量',
                'comment_count'  => '评论量',
                'favorite_count' => '收藏数量',
                'zibpay_price'   => '售价',
                'score'          => '评分',
                'sales_volume'   => '销量',
                'rand'           => '随机',
            ), $before
        );
    }

    //售后
    public static function after_sale($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的售后政策 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置售后政策，选择默认则使用主题设置中的售后政策';
        }
        if ($type == 'product') {
            $desc = '选择默认时会依次调用分类、主题设置中的售后政策，在此处可为当前商品单独设置售后政策';
        }

        $fields = array(
            array(
                'id'      => 'refund',
                'type'    => 'switcher',
                'title'   => '仅退款',
                'desc'    => '注意：未发货的商品都可以申请仅退款',
                'default' => false,
            ),
            array(
                'dependency' => array('refund', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('退款时效', 'zibll'),
                'id'         => 'refund_max_day',
                'class'      => 'compact',
                'type'       => 'number',
                'desc'       => '确认收货后多少天内可退款，单位：天',
                'default'    => 7,
                'min'        => 1,
                'step'       => 1,
                'unit'       => '天',
                'type'       => 'spinner',
            ),
            array(
                'title'   => '退货退款',
                'id'      => 'refund_return',
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'dependency' => array('refund_return', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('退货退款时效', 'zibll'),
                'id'         => 'refund_return_max_day',
                'class'      => 'compact',
                'desc'       => '确认收货后多少天内可退货退款，单位：天',
                'default'    => 15,
                'min'        => 1,
                'step'       => 1,
                'unit'       => '天',
                'type'       => 'spinner',
            ),

            /**
             * 暂未使用

            array(
            'title'   => '换货',
            'id'      => 'replacement',
            'type'    => 'switcher',
            'default' => true,
            ),
            array(
            'dependency' => array('replacement', '!=', ''),
            'title'      => ' ',
            'subtitle'   => __('换货时效', 'zibll'),
            'id'         => 'replacement_max_day',
            'class'      => 'compact',
            'type'       => 'number',
            'desc'       => '确认收货后多少天内可换货，单位：天',
            'default'    => 30,
            'min'        => 1,
            'step'       => 1,
            'unit'       => '天',
            'type'       => 'spinner',
            ),

            array(
            'title'   => '保修',
            'id'      => 'warranty',
            'type'    => 'switcher',
            'default' => false,
            ),
            array(
            'dependency' => array('warranty', '!=', ''),
            'title'      => ' ',
            'subtitle'   => __('保修时效', 'zibll'),
            'id'         => 'warranty_max_day',
            'class'      => 'compact',
            'type'       => 'number',
            'desc'       => '确认收货后多少天内可保修，单位：天',
            'default'    => 365,
            'min'        => 1,
            'step'       => 1,
            'unit'       => '天',
            'type'       => 'spinner',
            ),

             */
            //保价
            array(
                'id'      => 'insured_price',
                'type'    => 'switcher',
                'title'   => '保价',
                'default' => true,
            ),
            array(
                'dependency' => array('insured_price', '!=', ''),
                'title'      => ' ',
                'subtitle'   => __('保价时效', 'zibll'),
                'id'         => 'insured_price_max_day',
                'class'      => 'compact',
                'type'       => 'number',
                'desc'       => '确认收货后多少天内可保价，单位：天',
                'default'    => 7,
                'min'        => 1,
                'step'       => 1,
                'unit'       => '天',
                'type'       => 'spinner',
            ),
            array(
                'id'         => 'desc',
                'type'       => 'textarea',
                'title'      => '售后说明',
                'desc'       => '售后政策的描述、说明、规则等（支持html，注意格式规范）',
                'sanitize'   => false,
                'default'    => '',
                'attributes' => array(
                    'rows' => 2,
                ),
            ),

        );

        if ($type !== 'admin') {
            $fields =
            array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'title'   => '',
                    'default' => '',
                    'options' => array(
                        ''       => '默认',
                        'custom' => '自定义',
                    ),
                ),
                array(
                    'dependency' => array('type', '==', 'custom'),
                    'sanitize'   => false,
                    'id'         => 'opt',
                    'type'       => 'fieldset',
                    'fields'     => $fields,
                ),
            );
        }

        return array(
            'title'    => '售后政策',
            'sanitize' => false,
            'desc'     => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'id'       => $id_prefix . 'after_sale_opt',
            'type'     => 'fieldset',
            'fields'   => $fields,
        );
    }

    //服务
    public static function service($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的服务保障 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置服务保障，留空则使用主题设置中的服务保障';
        }
        if ($type == 'product') {
            $desc = '留空时会依次调用分类、主题设置中的服务保障，在此处可为当前商品单独设置服务保障';
        }

        return array(
            'id'           => $id_prefix . 'service',
            'type'         => 'group',
            'title'        => '自定义服务保障',
            'desc'         => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'class'        => '',
            'button_title' => '添加服务保障',
            'fields'       => array(
                array(
                    'id'    => 'name',
                    'class' => 'mini-input',
                    'type'  => 'text',
                    'title' => '名称(必填)',
                ),
                array(
                    'id'         => 'desc',
                    'type'       => 'textarea',
                    'title'      => '描述说明',
                    'desc'       => '服务保障的描述、说明、规则等（支持html，注意格式规范）',
                    'sanitize'   => false,
                    'default'    => '',
                    'attributes' => array(
                        'rows' => 2,
                    ),
                ),
                array(
                    'id'    => 'image',
                    'type'  => 'upload',
                    'title' => '图标图片',
                    'desc'  => '自定义服务保障的图标图片(可选，必须为正方形)',
                ),
            ),
        );
    }

    //限购
    public static function limit_buy($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的限购规则 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置限购规则，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的限购规则，在此处可为当前商品单独设置';
        }
        $options = array(
            'off' => '不限购',
            'on'  => '开启限购',
        );
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        return array(
            'id'     => $id_prefix . 'limit_buy',
            'type'   => 'fieldset',
            'desc'   => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'title'  => '商品限购',
            'fields' => array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'title'   => '',
                    'default' => $type === 'admin' ? 'off' : '',
                    'options' => $options,
                ),
                array(
                    'dependency' => array('type', '==', 'on'),
                    'title'      => ' ',
                    'subtitle'   => '普通用户限购',
                    'id'         => 'all',
                    'default'    => 1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => '件',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type|all', '==|>', 'on|-1'),
                    'id'         => 'auth',
                    'class'      => 'compact',
                    'title'      => ' ',
                    'subtitle'   => '认证用户限购',
                    'default'    => -1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => '件',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type|all', '==|>', 'on|-1'),
                    'id'         => 'vip_1',
                    'class'      => 'compact',
                    'title'      => ' ',
                    'subtitle'   => _pz('pay_user_vip_1_name', 'VIP1') . '限购',
                    'default'    => -1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => '件',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type|all', '==|>', 'on|-1'),
                    'id'         => 'vip_2',
                    'class'      => 'compact',
                    'title'      => ' ',
                    'subtitle'   => _pz('pay_user_vip_2_name', 'VIP2') . '限购',
                    'desc'       => '-1为不限购，0为不允许购买，' . _pz('pay_user_vip_2_name', 'VIP2') . '可购买数量需大于' . _pz('pay_user_vip_1_name', 'VIP2') . '<br>多身份用户取可购买的最大值',
                    'default'    => -1,
                    'min'        => -1,
                    'step'       => 1,
                    'unit'       => '件',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'on'),
                    'id'         => 'desc',
                    'type'       => 'text',
                    'title'      => '限购说明',
                    'desc'       => '开启限购后，在商品详情页会显示此说明',
                    'default'    => '',
                ),
            ),
        );
    }

    //商品详情页底部内容
    public static function content_after($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的详情页底部内容 (ps:分类可单独设置)支持html代码，注意格式规范';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一详情页底部追加内容，留空则使用主题设置中的配置';
        }
        return array(
            'id'         => $id_prefix . 'content_after',
            'type'       => 'textarea',
            'title'      => '详情页底部内容',
            'desc'       => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'sanitize'   => false,
            'default'    => '',
            'attributes' => array(
                'rows' => 3,
            ),
        );
    }

    //推广返佣
    public static function rebate($type = 'admin')
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的推广返佣规则 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置推广返佣规则，选择默认则使用主题设置中的配置';
        }

        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的的推广返佣规则，在此处可为当前商品单独设置（注意：积分商品没有佣金）';
        }

        $options = array(
            'off'   => '不参与',
            'ratio' => '按金额比例返佣',
            'fixed' => '固定金额返佣',
        );
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        return array(
            'id'     => $id_prefix . 'rebate',
            'type'   => 'fieldset',
            'desc'   => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'title'  => '推广返佣',
            'fields' => array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'default' => $type === 'admin' ? 'off' : '',
                    'options' => $options,
                ),
                array(
                    'dependency' => array('type', '==', 'ratio'),
                    'id'         => 'all_ratio',
                    'title'      => '普通用户佣金比例',
                    'default'    => 0,
                    'min'        => 0,
                    'max'        => 100,
                    'step'       => 2,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'ratio'),
                    'id'         => 'vip_1_ratio',
                    'title'      => _pz('pay_user_vip_1_name', 'VIP1') . '佣金比例',
                    'default'    => 0,
                    'class'      => 'compact',
                    'min'        => 0,
                    'max'        => 100,
                    'step'       => 2,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'ratio'),
                    'id'         => 'vip_2_ratio',
                    'class'      => 'compact',
                    'title'      => _pz('pay_user_vip_2_name', 'VIP2') . '佣金比例',
                    'default'    => 0,
                    'min'        => 0,
                    'max'        => 100,
                    'step'       => 2,
                    'unit'       => '%',
                    'type'       => 'spinner',
                    'desc'       => '需开启<a href="' . zib_get_admin_csf_url('支付付费/推广返佣') . '">推广返佣</a>功能',
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'all_fixed',
                    'title'      => '普通用户佣金金额',
                    'default'    => 0,
                    'min'        => 0,
                    'step'       => 5,
                    'unit'       => '元',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'vip_1_fixed',
                    'title'      => _pz('pay_user_vip_1_name', 'VIP1') . '佣金金额',
                    'default'    => 0,
                    'class'      => 'compact',
                    'min'        => 0,
                    'step'       => 5,
                    'unit'       => '元',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'vip_2_fixed',
                    'class'      => 'compact',
                    'title'      => _pz('pay_user_vip_2_name', 'VIP2') . '佣金金额',
                    'default'    => 0,
                    'min'        => 0,
                    'step'       => 5,
                    'unit'       => '元',
                    'desc'       => '需开启<a href="' . zib_get_admin_csf_url('支付付费/推广返佣') . '">推广返佣</a>功能',
                    'type'       => 'spinner',
                ),
            ),
        );
    }

    //运费
    public static function shipping_fee($type = 'admin', $dependency = [])
    {
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的运费规则 (ps:分类及商品可单独设置)';
        $class     = '';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置运费规则，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc  = '默认会使用依次调用分类、主题设置中的的运费规则，在此处可为当前商品单独设置';
            $class = '';
        }

        $options = array(
            'free'   => '免运费',
            'fixed'  => '固定收费',
            'amount' => '按付款金额收费',
        );
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }
        return array(
            'title'      => '运费配置',
            'sanitize'   => false,
            'dependency' => $dependency,
            'desc'       => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'id'         => $id_prefix . 'shipping_fee_opt',
            'type'       => 'fieldset',
            'class'      => $class,
            'fields'     => array(
                array(
                    'id'      => 'type',
                    'type'    => 'radio',
                    'inline'  => true,
                    'default' => $type === 'admin' ? 'free' : '',
                    'options' => $options,
                ),
                array(
                    'dependency' => array('type', '==', 'fixed'),
                    'id'         => 'fixed_fee',
                    'type'       => 'number',
                    'title'      => '运费金额',
                    'default'    => '12',
                ),
                //按照付款金额阶梯收费
                array(
                    'dependency' => array('type', '==', 'amount'),
                    'id'         => 'amount_fee',
                    'type'       => 'fieldset',
                    'fields'     => array(
                        array(
                            'id'       => 'fee',
                            'type'     => 'number',
                            'title'    => ' ',
                            'subtitle' => '基础运费',
                            'default'  => '12',
                        ),
                        array(
                            'id'       => 'free_amount',
                            'type'     => 'number',
                            'title'    => ' ',
                            'subtitle' => '满多少免运费',
                            'desc'     => '只针对于单一商品的单笔订单总金额',
                            'default'  => '6000',
                        ),
                    ),
                ),
                array(
                    'title'   => '配送说明',
                    'desc'    => '简短几个字描述配送说明，例如：24小时极速发货、顺丰包邮等等',
                    'id'      => 'desc',
                    'type'    => 'text',
                    'default' => '',
                ),
            ),
        );
    }

    //商品评论
    public static function comment($type = 'admin')
    {

        //暂未启用

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的评论规则 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置评论规则，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的的评论规则，在此处可为当前商品单独设置';
        }

        $options = array(
            'off' => '关闭',
            'on'  => '开启',
        );

        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        return array(
            'id'      => $id_prefix . 'comment_s',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => '商品评价',
            'desc'    => '是否启用评论评价功能<div style="color: #c0826d; ">' . $desc . '</div>',
            'default' => $type === 'admin' ? 'on' : '',
            'options' => $options,
        );
    }

    //类表的显示样式配置
    public static function list_style($sub_title = '')
    {
        return array(
            'title'    => '列表UI样式',
            'subtitle' => $sub_title,
            'id'       => 'list_style',
            'class'    => '',
            'type'     => 'fieldset',
            'fields'   => array(
                array(
                    'id'      => 'style',
                    'title'   => '列表显示样式',
                    'type'    => 'radio',
                    'inline'  => true,
                    'options' => array(
                        ''      => '竖向大卡片',
                        'small' => '横向小卡片',
                    ),
                    'default' => '',
                ),
                array(
                    'dependency' => array('style', '!=', 'small'),
                    'id'         => 'thumb_scale',
                    'title'      => '商品主图长宽比例',
                    'default'    => 100,
                    'max'        => 300,
                    'min'        => 20,
                    'step'       => 10,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
                array(
                    'id'      => 'thumb_fit',
                    'title'   => '商品主图填充方式',
                    'default' => '',
                    'type'    => 'radio',
                    'inline'  => true,
                    'options' => array(
                        ''        => __('cover 铺满', 'zibll'),
                        'contain' => __('contain 等比例缩放', 'zibll'),
                    ),
                ),
                array(
                    'id'      => 'title_one_line', //截断显示
                    'title'   => '标题只显示一行',
                    'type'    => 'switcher',
                    'default' => false,
                    'desc'    => '关闭则显示为两行，如果大多数商品标题字数少于10个字，则建议开启',
                ),
                array(
                    'id'      => 'show_desc',
                    'title'   => '显示商品描述',
                    'desc'    => '样式为横向小卡片时，只有开启标题只显示一行后才会显示描述',
                    'type'    => 'switcher',
                    'default' => false,
                ),
                array(
                    'id'      => 'show_price',
                    'title'   => '显示商品价格',
                    'type'    => 'switcher',
                    'default' => true,
                ),
                array(
                    'id'      => 'show_discount',
                    'title'   => '显示折扣标签',
                    'type'    => 'switcher',
                    'default' => true,
                ),
                array(
                    'title'   => __('销量显示', 'zibll'),
                    'id'      => 'show_sales',
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
                    'title'      => ' ',
                    'dependency' => array('show_sales', '==', 'min'),
                    'subtitle'   => __('销量超过多少显示', 'zibll'),
                    'id'         => 'show_sales_min',
                    'class'      => 'compact',
                    'default'    => 10,
                    'min'        => 0,
                    'step'       => 10,
                    'unit'       => '件',
                    'type'       => 'spinner',
                ),
                array(
                    'dependency' => array('style', '!=', 'small'),
                    'id'         => 'text_center',
                    'title'      => '内容居中显示',
                    'desc'       => '标题、价格、简介等信息居中显示',
                    'default'    => true,
                    'type'       => 'switcher',
                ),

            ),
        );
    }

    //商品详情页内容布局
    public static function content_layout($type = 'admin')
    {
        $options = array(
            'full' => '全屏',
            'box'  => '适应内容宽度',
            'side' => '适应内容宽度+侧边栏',
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        $desc = '为所有商品添加默认的布局样式(ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置布局样式，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的的布局样式，在此处可为当前商品单独设置';
        }

        $fields = array(
            'id'      => $id_prefix . 'content_layout',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => '商品详情页布局',
            'default' => $type === 'admin' ? 'full' : '',
            'desc'    => '商品详情页宽度填充方式，用于布局显示<a target="_blank" href="https://www.zibll.com/39221.html">查看官方教程</a><div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        return $fields;
    }

    //商品详情页内容布局
    public static function content_show_bg($type = 'admin')
    {
        $options = array(
            'on'  => '显示',
            'off' => '隐藏',
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        $desc = '为所有商品添加默认的背景样式 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置背景样式，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的的背景样式，在此处可为当前商品单独设置';
        }

        $fields = array(
            'id'      => $id_prefix . 'content_show_bg',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => '商品详情背景盒子',
            'default' => $type === 'admin' ? 'on' : '',
            'desc'    => '商品详情页的内容部分是否显示背景盒子，常用图片作为商品介绍，推荐隐藏，如果选择了带侧边栏布局则建议开启<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        return $fields;
    }

    //虚拟商品email填写
    public static function email_fill($type = 'admin')
    {
        $options = array(
            'required' => '必填',
            'fill'     => '选填',
            'off'      => '关闭',
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        $desc = '自动发货的虚拟商品是否需要用户填写email (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的配置，在此处可为当前商品单独设置';
        }

        $fields = array(
            'id'      => $id_prefix . 'email_fill',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => '自动发货商品email填写',
            'default' => $type === 'admin' ? 'required' : '',
            'desc'    => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        if ($type == 'product') {
            $fields['dependency'] = array('shipping_type', '==', 'auto');
            $fields['title']      = 'email邮箱信息';
        }

        return $fields;
    }

    //虚拟商品免登录购买
    public static function guest_buy($type = 'admin')
    {
        $options = array(
            'on'  => '允许',
            'off' => '禁止',
        );

        $id_prefix = $type === 'admin' ? 'shop_' : '';
        if ($type !== 'admin') {
            $options = array_merge(array('' => '默认'), $options);
        }

        $desc = '自动发货的虚拟商品是否允许免登陆购买 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的配置，在此处可为当前商品单独设置';
        }

        $fields = array(
            'id'      => $id_prefix . 'guest_buy_s',
            'type'    => 'radio',
            'inline'  => true,
            'title'   => '自动发货商品免登陆购买',
            'default' => $type === 'admin' ? 'off' : '',
            'desc'    => '<div style="color: #c0826d; ">' . $desc . '</div>',
            'options' => $options,
        );

        if ($type == 'product') {
            $fields['dependency'] = array('shipping_type', '==', 'auto');
            $fields['title']      = '免登陆购买';
        }

        return $fields;
    }

    //商品TAB栏目
    public static function single_tab($type = 'admin')
    {
        $fields    = array();
        $id_prefix = $type === 'admin' ? 'shop_' : '';
        $desc      = '为所有商品添加默认的TAB栏目 (ps:分类及商品可单独设置)';
        if ($type == 'cat') {
            $desc = '为当前分类下的商品统一设置TAB栏目，选择默认则使用主题设置中的配置';
        }
        if ($type == 'product') {
            $desc = '默认会使用依次调用分类、主题设置中的的TAB栏目，在此处可为当前商品单独设置';
        }

        $options = array(
            'disable' => '关闭',
            'custom'  => '自定义',
        );
        if ($type !== 'admin') {
            $options  = array_merge(array('' => '默认'), $options);
            $fields[] = array(
                'id'      => 'type',
                'type'    => 'radio',
                'inline'  => true,
                'title'   => '',
                'default' => $type === 'admin' ? 'disable' : '',
                'options' => $options,
            );
        }
        $fields[] = array(
            'dependency'   => $type !== 'admin' ? array('type', '==', 'custom') : false,
            'id'           => 'tabs',
            'type'         => 'group',
            'button_title' => '添加栏目',
            'title'        => $type !== 'admin' ? '自定义栏目' : '',
            'sanitize'     => false,
            'fields'       => array(
                array(
                    'id'    => 'title',
                    'type'  => 'text',
                    'class' => 'mini-input',
                    'title' => '名称（必填）',
                ),
                array(
                    'sanitize' => false,
                    'id'       => 'content',
                    'type'     => 'wp_editor',
                ),
            ),
        );

        return array(
            'title'    => '详情页TAB栏目',
            'subtitle' => $type === 'product' ? '添加更多自定义tab栏目' : '',
            'sanitize' => false,
            'desc'     => 'TAB栏目会显示在商品详情页面，通常用来添加商品的“产品参数”、“安装须知”、“常见问题”等内容<div style="color: #c0826d; ">' . $desc . '</div>',
            'id'       => $id_prefix . 'single_tabs',
            'type'     => 'fieldset',
            'fields'   => $fields,
        );
    }
}
