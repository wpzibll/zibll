<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2026-01-30 21:47:26
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商品分类配置项
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_shop_admin_term_extend_metabox()
{
    //页面限制：分类、标签、优惠
    if (strpos($_SERVER['SCRIPT_NAME'], 'edit-tags.php') === false
        && strpos($_SERVER['SCRIPT_NAME'], 'term.php') === false
        && (empty($_REQUEST['action']) || empty($_REQUEST['taxonomy']))
    ) {
        return;
    }

    $cat_prefix      = 'shop_cat_config';
    $discount_prefix = 'shop_discount_config';
    $tag_prefix      = 'shop_tag_config';

    CSF::createTaxonomyOptions($cat_prefix, array(
        'title'     => '分类选项',
        'taxonomy'  => 'shop_cat',
        'data_type' => 'serialize',
    ));

    CSF::createTaxonomyOptions($discount_prefix, array(
        'title'     => '优惠配置',
        'taxonomy'  => 'shop_discount',
        'data_type' => 'serialize',
    ));

    CSF::createTaxonomyOptions($tag_prefix, array(
        'title'     => '标签配置',
        'taxonomy'  => 'shop_tag',
        'data_type' => 'serialize',
    ));

    //分类配置
    CSF::createSection($cat_prefix, array(
        'title'  => __('商品统一参数配置', 'zibll'),
        'fields' => array(
            zib_shop_csf_module::limit_buy('cat'),
            zib_shop_csf_module::shipping_fee('cat'),
            zib_shop_csf_module::guest_buy('cat'),
            zib_shop_csf_module::email_fill('cat'),
            zib_shop_csf_module::after_sale('cat'),
            zib_shop_csf_module::rebate('cat'),
            zib_shop_csf_module::service('cat'),
            zib_shop_csf_module::single_tab('cat'),
            zib_shop_csf_module::content_after('cat'),
            zib_shop_csf_module::content_layout('cat'),
            zib_shop_csf_module::content_show_bg('cat'),
        ),
    ));

    //分类配置
    CSF::createSection($cat_prefix, array(
        'title'  => __('分类页面UI配置', 'zibll'),
        'fields' => array(
            array(
                'id'      => 'list_style_style',
                'title'   => '列表显示样式',
                'type'    => 'radio',
                'inline'  => true,
                'options' => array(
                    'default' => '默认(尊循主题配置)',
                    ''        => '竖向大卡片',
                    'small'   => '横向小卡片',
                ),
                'default' => 'default',
            ),
        ),
    ));

    //标签
    CSF::createSection($tag_prefix, array(
        'title'  => __('标签配置', 'zibll'),
        'fields' => array( //商品金额限制
            array(
                'title'   => __('优先级设置', 'zibll'),
                'id'      => 'priority',
                'desc'    => '<div class="c-yellow">同一种商品有多个标签时，优先级将影响显示顺序<br>数值越小，优先级越高，则先显示（相同优先级，则按标签创建的时间排序，先创建的先显示）</div>',
                'type'    => 'spinner',
                'default' => 50,
            ),
            array(
                'title'   => '标签颜色',
                'id'      => 'class',
                'class'   => 'compact skin-color',
                'default' => '',
                'type'    => 'palette',
                'options' => CFS_Module::zib_palette([
                    '' => array('rgb(213, 213, 213)'),
                ]),
            ),
            array(
                'title'   => __('重点标签', 'zibll'),
                'id'      => 'is_important',
                'desc'    => '重点标签将在重要位置着重显示，注意：一个商品选择了多个重点标签时，则按照优先级的第一个为准',
                'type'    => 'switcher',
                'default' => false,
            ),
        ),
    ));

    //优惠优先级
    CSF::createSection($discount_prefix, array(
        'title'  => __('配置', 'zibll'),
        'fields' => array( //商品金额限制
            array(
                'title'   => __('小标签', 'zibll'),
                'id'      => 'small_badge',
                'desc'    => '简短几个字的标签，描述优惠政策，例如：立减200、限时5折、买2送1等',
                'type'    => 'text',
                'default' => '',
            ),
            array(
                'title'   => __('优先级设置', 'zibll'),
                'id'      => 'priority',
                'desc'    => '<div class="c-yellow">同一种商品参加了多个打折、立减活动，优先级就十分重要，直接影响优惠计算先后的顺序<br>数值越小，优先级越高，则先计算（相同优先级，则按优惠创建的时间排序，先创建的先计算）</div>',
                'type'    => 'spinner',
                'default' => 50,
            ),
            array(
                'title'   => __('重点活动', 'zibll'),
                'id'      => 'is_important',
                'desc'    => '重点活动将在商品金额位置着重显示，注意：一个商品选择了多个重点标签时，则按照优先级的第一个为准',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'title'   => '重点活动背景色',
                'id'      => 'important_class',
                'class'   => 'compact skin-color',
                'default' => 'jb-red',
                'type'    => 'palette',
                'options' => CFS_Module::zib_palette([], ['b', 'jb']),
            ),
        ),
    ));

    //优惠配置
    CSF::createSection($discount_prefix, array(
        'title'  => __('优惠政策', 'zibll'),
        'fields' => array(
            array(
                'type'    => 'submessage',
                'style'   => 'danger',
                'content' => '<b>重要提醒：</b>请合理配置优惠幅度和参与限制，避免出现因优惠配置问题而导致下单金额异常的情况',
            ),
            array(
                'title'   => __('优惠计算范围', 'zibll'),
                'id'      => 'discount_scope',
                'type'    => 'button_set',
                'options' => array(
                    'item'    => '单件商品',
                    'product' => '商品',
                    'author'  => '店铺',
                    'order'   => '订单',
                ),
                'default' => 'item',
            ),
            array(
                'type'    => 'submessage',
                'style'   => 'warning',
                'content' => '<b>范围配置说明：只影响立减优惠和金额限制范围</b>
                <ul>
                <li><code>单件商品</code>：针对每一件商品（例如：立减10元，如果用户一个商品同时下单了5件，则每件优惠10元，一共优惠50元）</li>
                <li><code>商品</code>：针对商品总量（例如：立减10元，如果用户一个商品同时下单了5件，则一共优惠10元，每件优惠2元）</li>
                <li><code>店铺</code>：针对店铺总量（例如：立减10元，如果用户在同一家店铺里购买了多件商品，则这些商品一共优惠10元），类似于店铺优惠</li>
                <li><code>订单</code>：针对订单总量（例如：立减10元，用户单笔订单不管多少件商品，也不管在哪家店铺购买，都一共优惠10元）类似于：跨店优惠、平台优惠</li>
                <li>注意：同理，如果设置了金额限制，则也是按照相同范围计算是否达标，打折方式的优惠计算，是按照单个商品的优惠金额计算的</li>
                <li>单件商品和商品的区别：主要针对于同一个商品单笔下单多件时进行区分</li>
                </ul>
                ',
            ),
            array(
                'title'   => __('优惠类型', 'zibll'),
                'id'      => 'discount_type',
                'type'    => 'button_set',
                'options' => array(
                    'reduction' => '立减',
                    'discount'  => '打折',
                    'gift'      => '买赠',
                ),
                'default' => 'reduction',
            ),
            array(
                'dependency' => array('discount_type', '==', 'reduction'),
                'title'      => __('立减金额', 'zibll'),
                'id'         => 'reduction_amount',
                'type'       => 'number',
                'default'    => 0,
            ),
            array(
                'dependency' => array('discount_type', '==', 'discount'),
                'title'      => __('几点几折', 'zibll'),
                'desc'       => '必须在0.01-9.99之间，例如：5.5折，输入5.5',
                'id'         => 'discount_amount',
                'type'       => 'number',
                'default'    => 0,
                'unit'       => '折',
            ),
            array(
                'dependency'   => array('discount_type', '==', 'gift'),
                'id'           => 'gift_config',
                'type'         => 'group',
                'button_title' => '添加赠品',
                'desc'         => '注意：除了其它物品，其他相同类型的赠品只能添加一个',
                'fields'       => array(
                    array(
                        'title'   => __('类型', 'zibll'),
                        'id'      => 'gift_type',
                        'inline'  => true,
                        'type'    => 'radio',
                        'options' => array(
                            'vip_1'          => _pz('pay_user_vip_1_name'),
                            'vip_2'          => _pz('pay_user_vip_2_name'),
                            'auth'           => '认证用户',
                            'level_integral' => '经验值',
                            'points'         => '积分',
                            'other'          => '其它物品',
                            //   'product'        => '商品'  //暂未启用
                        ),
                        'default' => 'product',
                    ),
                    array(
                        'dependency' => array('gift_type', 'any', 'vip_1,vip_2'),
                        'title'      => '会员赠送时长',
                        'desc'       => '单位为天，填<code>Permanent</code>为永久<br>注意：1.已经是会员用户，不会降级赠送<br>2.已经是永久会员的，也不会赠送',
                        'id'         => 'vip_time',
                        'default'    => '',
                        'class'      => 'compact',
                        'type'       => 'text',
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'level_integral'),
                        'title'      => '经验值',
                        'id'         => 'level_integral',
                        'default'    => 0,
                        'type'       => 'number',
                        'unit'       => '经验值',
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'points'),
                        'title'      => '积分',
                        'id'         => 'points',
                        'default'    => 0,
                        'type'       => 'number',
                        'unit'       => '积分',
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'product'),
                        'title'      => '商品ID',
                        'desc'       => '输入需要赠送的商品ID，建议选择没有商品选项的商品，如果有商品选项，则自动获取第一个选项',
                        'id'         => 'product_id',
                        'default'    => 0,
                        'type'       => 'number',
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'auth'),
                        'desc'       => '如果用户已经是认证用户，则不参与赠送',
                        'id'         => 'auth_info',
                        'type'       => 'fieldset',
                        'class'      => 'compact',
                        'fields'     => array(
                            array(
                                'title' => '认证名称',
                                'id'    => 'name',
                                'type'  => 'text',
                            ),
                            array(
                                'title' => '认证简介',
                                'class' => 'compact',
                                'id'    => 'desc',
                                'type'  => 'text',
                            ),
                        ),
                    ),
                    array(
                        'dependency' => array('gift_type', '==', 'other'),
                        'desc'       => '',
                        'id'         => 'other_info',
                        'type'       => 'fieldset',
                        'class'      => 'compact',
                        'fields'     => array(
                            array(
                                'title' => '物品名称',
                                'id'    => 'name',
                                'type'  => 'text',
                            ),
                            array(
                                'title' => '赠送说明',
                                'class' => 'compact',
                                'id'    => 'desc',
                                'type'  => 'text',
                            ),
                        ),
                    ),
                ),
            ),
        )));

    CSF::createSection($discount_prefix, array(
        'title'  => __('优惠限制', 'zibll'),
        'fields' => array( //商品金额限制
            array(
                'title'   => __('金额限制', 'zibll'),
                'desc'    => __('根据设置的范围，计算<code>总原价</code>是否达标', 'zibll'),
                'id'      => 'price_limit',
                'type'    => 'number',
                'default' => 0,
            ),
            array(
                'title'   => __('用户身份限制', 'zibll'),
                'id'      => 'user_limit',
                'type'    => 'radio',
                'inline'  => true,
                'desc'    => '哪些用户可参与优惠',
                'options' => array(
                    ''      => '不限制',
                    'vip'   => 'VIP1及以以上',
                    'vip_2' => 'VIP2',
                    'auth'  => '认证用户',
                ),
                'default' => '',
            ),
            array(
                'title'   => __('限时优惠', 'zibll'),
                'id'      => 'time_limit',
                'type'    => 'switcher',
                'default' => false,
            ),
            array(
                'dependency' => array('time_limit', '!=', ''),
                'id'         => 'time_limit_config',
                'type'       => 'fieldset',
                'fields'     => array(
                    //开始时间
                    array(
                        'title'    => ' ',
                        'subtitle' => __('开始时间(可选)', 'zibll'),
                        'id'       => 'start',
                        'type'     => 'date',
                        'settings' => array(
                            'dateFormat'  => 'yy-mm-dd 00:00:00',
                            'changeMonth' => true,
                            'changeYear'  => true,
                        ),
                        'default'  => '',
                    ),
                    //结束时间
                    array(
                        'title'    => ' ',
                        'subtitle' => __('结束时间(必填)', 'zibll'),
                        'class'    => 'compact',
                        'id'       => 'end',
                        'type'     => 'date',
                        'settings' => array(
                            'dateFormat'  => 'yy-mm-dd 23:59:59',
                            'changeMonth' => true,
                            'changeYear'  => true,
                        ),
                        'default'  => '',
                    ),
                    array(
                        'title'   => __('倒计时', 'zibll'),
                        'id'      => 'countdown',
                        'desc'    => '启用后，会在醒目位置显示倒计时',
                        'type'    => 'switcher',
                        'default' => false,
                    ),
                ),
            ),

        ),
    ));

}
add_action('zib_require_end', 'zib_shop_admin_term_extend_metabox');
