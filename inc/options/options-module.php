<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:41:45
 * @LastEditTime : 2026-04-28 20:31:32
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class CFS_Module
{

    public static function footer_tabbar()
    {
        $args = array(
            array(
                'id'      => 'icon_fa',
                'class'   => 'compact',
                'type'    => 'color_group',
                'title'   => '',
                'desc'    => '自定义颜色请注意日间、夜间模式的适配',
                'options' => array(
                    'color' => '自定义图标颜色',
                    'bg'    => '自定义背景颜色',
                ),
            ),
        );
        return $args;
    }

    public static function float_btn($true = true)
    {
        $args = array(
            array(
                'id'      => 'pc_s',
                'title'   => '',
                'label'   => __('PC端显示', 'zibll'),
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'id'      => 'm_s',
                'class'   => 'compact',
                'title'   => '',
                'label'   => __('移动端显示', 'zibll'),
                'type'    => 'switcher',
                'default' => true,
            ),
            array(
                'id'      => 'color',
                'class'   => 'compact',
                'type'    => 'color_group',
                'title'   => '',
                'desc'    => '自定义颜色请注意日间、夜间模式的适配',
                'options' => array(
                    'color' => '自定义图标颜色',
                    'bg'    => '自定义背景颜色',
                ),
            ),
        );
        return $args;
    }

    public static function add_slider()
    {
        $f_imgpath = get_template_directory_uri() . '/inc/csf-framework/assets/images/';
        $args      = array();

        $args[] = array(
            'title'   => __('图片背景', 'zibll'),
            'id'      => 'background',
            'default' => '',
            'preview' => true,
            'library' => 'image',
            'type'    => 'upload',
        );
        $args[] = array(
            'title'   => __('视频背景', 'zibll') . zib_get_csf_option_new_badge()['7.1'],
            'id'      => 'background_video',
            'default' => '',
            'class'   => 'compact',
            'preview' => false,
            'library' => 'video',
            'type'    => 'upload',
            'desc'    => '（必填）图片背景、视频背景至少二选一，如果同时设置则PC端视频优先（PC端视频加载失败则显示图片)<div class="c-yellow">如果需要在移动端显示，则必须设置图片背景，移动端只显示图片</div>',
        );
        $args[] = array(
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
        );
        $args[] = array(
            'id'           => 'link',
            'type'         => 'link',
            'title'        => '跳转链接',
            'default'      => array(),
            'add_title'    => '添加链接',
            'edit_title'   => '编辑链接',
            'remove_title' => '删除链接',
        );
        $args[] = array(
            'id'                     => 'image_layer',
            'type'                   => 'group',
            'accordion_title_number' => true,
            'accordion_title_auto'   => false,
            'accordion_title_prefix' => '图层',
            'button_title'           => '添加图层',
            'title'                  => '叠加图层',
            'subtitle'               => '添加更多图层',
            'desc'                   => '添加额外的幻灯片图层，配合图层设置及幻灯片其它设置，可轻松制作出漂亮无比的幻灯片',
            'fields'                 => array(
                array(
                    'title'   => __('图层图片', 'zibll'),
                    'id'      => 'image',
                    'default' => '',
                    'preview' => true,
                    'library' => 'image',
                    'type'    => 'upload',
                ),
                array(
                    'title'   => '自由尺寸',
                    'type'    => 'switcher',
                    'id'      => 'free_size',
                    'class'   => 'compact',
                    'desc'    => '如果图层的尺寸和背景图的尺寸不一致，可开启此项以自定义图层对齐方向',
                    'default' => false,
                    'type'    => 'switcher',
                ),
                array(
                    'dependency' => array('image|free_size', '!=|!=', '|'),
                    'title'      => '图层对齐',
                    'id'         => 'align',
                    'inline'     => true,
                    'type'       => 'radio',
                    'class'      => 'compact',
                    'default'    => 'center',
                    'options'    => array(
                        'left'   => '靠左显示',
                        'center' => '居中显示',
                        'right'  => '靠右显示',
                    ),
                ), array(
                    'dependency' => array('image', '!=', ''),
                    'id'         => 'parallax',
                    'class'      => 'compact',
                    'desc'       => '提前或延后进入视线，负值为延后，正值为提前，0为关闭[-200~200]',
                    'title'      => '视差滚动',
                    'default'    => 0,
                    'max'        => 200,
                    'min'        => -200,
                    'step'       => 5,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ), array(
                    'dependency' => array('image', '!=', ''),
                    'id'         => 'parallax_scale',
                    'desc'       => '放大或缩小进入视线，原图大小的百分之多少[1~200]',
                    'class'      => 'compact',
                    'title'      => '视差缩放',
                    'default'    => 100,
                    'max'        => 200,
                    'min'        => 1,
                    'step'       => 5,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ), array(
                    'dependency' => array('image|parallax', '!=|!=', '|'),
                    'id'         => 'parallax_opacity',
                    'desc'       => '以百分之多少的透明度进入视线[1~100]<br>视差功能对浏览器性能有一定影响，如果图层较多，不建议全部开启',
                    'class'      => 'compact',
                    'title'      => '视差透明',
                    'default'    => 100,
                    'max'        => 100,
                    'min'        => 1,
                    'step'       => 5,
                    'unit'       => '%',
                    'type'       => 'spinner',
                ),
            ),
        );
        $args[] = array(
            'id'         => 'text',
            'type'       => 'accordion',
            'title'      => '叠加文案',
            'accordions' => array(
                array(
                    'title'  => '幻灯片叠加文案',
                    'fields' => array(
                        array(
                            'title'      => '幻灯片文案',
                            'subtitle'   => '幻灯片标题',
                            'id'         => 'title',
                            'default'    => '',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'type'       => 'textarea',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'title'      => '幻灯片简介',
                            'id'         => 'desc',
                            'class'      => 'compact',
                            'default'    => '',
                            'desc'       => '标题、简介均支持HTML代码，请注意代码规范及标签闭合',
                            'attributes' => array(
                                'rows' => 1,
                            ),
                            'type'       => 'textarea',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'title'      => '显示位置',
                            'id'         => 'text_align',
                            'type'       => 'image_select',
                            'class'      => 'compact image-miniselect',
                            'default'    => 'left-bottom',
                            'desc'       => '前景图显示位置及文案位置需合理搭配',
                            'options'    => array(
                                'left-bottom'   => $f_imgpath . 'left-bottom.jpg',
                                'left-conter'   => $f_imgpath . 'left-conter.jpg',
                                'conter-conter' => $f_imgpath . 'conter-conter.jpg',
                                'conter-bottom' => $f_imgpath . 'conter-bottom.jpg',
                                'right-conter'  => $f_imgpath . 'right-conter.jpg',
                                'right-bottom'  => $f_imgpath . 'right-bottom.jpg',
                            ),
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'id'         => 'text_size_pc',
                            'class'      => 'compact',
                            'title'      => 'PC端字体大小',
                            'default'    => 30,
                            'max'        => 50,
                            'min'        => 12,
                            'step'       => 1,
                            'unit'       => 'PX',
                            'type'       => 'spinner',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'id'         => 'text_size_m',
                            'class'      => 'compact',
                            'title'      => '移动端字体大小',
                            'desc'       => '在此设置标题的字体大小，简介的大小为标题大小的60%，最小12px<br>字体越大，文案周边的间距也越大！字体大小请根据内容合理调整，避免在某些设备显示不全',
                            'default'    => 20,
                            'max'        => 50,
                            'min'        => 12,
                            'step'       => 1,
                            'unit'       => 'PX',
                            'type'       => 'spinner',
                        ),
                        array(
                            'dependency' => array('title', '!=', ''),
                            'id'         => 'parallax',
                            'class'      => 'compact',
                            'desc'       => '视差滚动功能为较背景滚动的时间差，为负数则滚动慢一拍，为正数则滚动快一拍，为0则关闭',
                            'title'      => '文案视差滚动',
                            'default'    => 40,
                            'max'        => 200,
                            'min'        => -200,
                            'step'       => 10,
                            'unit'       => '%',
                            'type'       => 'spinner',
                        ),
                    ),
                ),
            ),
        );

        return $args;
    }

    public static function page_type()
    {
        return array(
            'home'        => '首页',
            'topics'      => '专题页',
            'category'    => '分类页',
            'tag'         => '标签页',
            'author'      => '用户页',
            'single'      => '文章页',
            'search'      => '搜索页',
            'forum_home'  => '[论坛]首页',
            'forum_plate' => '[论坛]板块页面',
            'forum_post'  => '[论坛]帖子页面',
            'page'        => '其它页面',
        );
    }

    public static function posts_orderby($orderby = array())
    {
        return array_merge($orderby, array(
            'modified'            => '更新时间',
            'date'                => '发布时间',
            'views'               => '浏览数量',
            'like'                => '点赞数量',
            'comment_count'       => '评论数量',
            'favorite'            => '收藏数量',
            'zibpay_price'        => '销售价格',
            'zibpay_points_price' => '积分售价',
            'sales_volume'        => '销售数量',
            'rand'                => '随机排序',
        ));
    }

    public static function zib_palette($palette = array(), $show = array('c', 'b', 'jb'))
    {
        if (in_array('c', $show)) {
            $palette = array_merge($palette, array(
                'c-red'      => array('rgba(255, 84, 115, .4)'),
                'c-red-2'    => array('rgba(194, 41, 46, 0.4)'),
                'c-yellow'   => array('rgba(255, 111, 6, 0.4)'),
                'c-yellow-2' => array('rgba(179, 103, 8, 0.4)'),
                'c-cyan'     => array('rgba(8, 196, 193, .4)'),
                'c-blue'     => array('rgba(41, 151, 247, .4)'),
                'c-blue-2'   => array('rgba(77, 130, 249, .4)'),
                'c-green'    => array('rgba(18, 185, 40, .4)'),
                'c-green-2'  => array('rgba(72, 135, 24, .4)'),
                'c-purple'   => array('rgba(213, 72, 245, 0.4)'),
                'c-purple-2' => array('rgba(154, 72, 245, 0.4)'),
            ));
        }
        if (in_array('b', $show)) {
            $palette = array_merge($palette, array(
                'b-red'    => array('#f74b3d'),
                'b-yellow' => array('#f3920a'),
                'b-cyan'   => array('#08c4c1'),
                'b-blue'   => array('#0a8cf3'),
                'b-green'  => array('#1fd05a'),
                'b-purple' => array('#c133f5'),
                'b-black'  => array('#121517'),
            ));
        }
        if (in_array('jb', $show)) {
            $palette = array_merge($palette, array(
                'jb-red'    => array('linear-gradient(135deg, #ffbeb4 10%, #f61a1a 100%)'),
                'jb-pink'   => array('linear-gradient(135deg, #ff5e7f 30%, #ff967e 100%)'),
                'jb-yellow' => array('linear-gradient(135deg, #ffd6b2 10%, #ff651c 100%)'),
                'jb-cyan'   => array('linear-gradient(140deg, #039ab3 10%, #58dbcf 90%)'),
                'jb-blue'   => array('linear-gradient(135deg, #b6e6ff 10%, #198aff 100%)'),
                'jb-green'  => array('linear-gradient(135deg, #ccffcd 10%, #52bb51 100%)'),
                'jb-purple' => array('linear-gradient(135deg, #fec2ff 10%, #d000de 100%)'),
                'jb-vip1'   => array('linear-gradient(25deg, #eab869 10%, #fbecd4 60%, #ffe0ae 100%)'),
                'jb-vip2'   => array('linear-gradient(317deg, #4d4c4c 30%, #878787 70%, #5f5c5c 100%)'),
            ));
        }

        if (in_array('text', $show)) {
            $palette = array_merge($palette, array(
                'focus-color'   => array('url(' . get_template_directory_uri() . '/inc/csf-framework/assets/images/skin-color-focus.svg)  no-repeat  center/100% 100%'),
                'key-color'     => array('#333'),
                'muted-color'   => array('#777777'),
                'muted-2-color' => array('#999'),
                'muted-3-color' => array('#b1b1b1'),
                'c-red'         => array('rgba(255, 84, 115)'),
                'c-red-2'       => array('rgba(194, 41, 46)'),
                'c-yellow'      => array('rgba(255, 111, 6)'),
                'c-yellow-2'    => array('rgba(179, 103, 8)'),
                'c-cyan'        => array('rgba(8, 196, 193)'),
                'c-blue'        => array('rgba(41, 151, 247)'),
                'c-blue-2'      => array('rgba(77, 130, 249)'),
                'c-green'       => array('rgba(18, 185, 40)'),
                'c-green-2'     => array('rgba(72, 135, 24)'),
                'c-purple'      => array('rgba(213, 72, 245)'),
                'c-purple-2'    => array('rgba(154, 72, 245)'),
            ));
        }

        if (in_array('cg', $show)) {
            $palette = array_merge($palette, array(
                'cg-gray'     => array('linear-gradient(to right, #373737, #8f8f8f)'),
                'cg-white'    => array('linear-gradient(to right, #fff, #acacac)'),
                'cg-red'      => array('linear-gradient(to right,rgb(249, 160, 164),rgb(212, 0, 85))'),
                'cg-red-2'    => array('linear-gradient(to right, #cb0d0d, #ff8ad3)'),
                'cg-yellow'   => array('linear-gradient(to right, #f5c06d, #dc2114)'),
                'cg-yellow-2' => array('linear-gradient(to right, #b1510c, #a8af2d)'),
                'cg-blue'     => array('linear-gradient(to right, #65b2f5, #115ebe)'),
                'cg-blue-2'   => array('linear-gradient(to right, #2854d8, #8771e9)'),
                'cg-cyan'     => array('linear-gradient(to right, #5cbebc, #0383aa)'),
                'cg-green'    => array('linear-gradient(to right, #70d37e, #54960d)'),
                'cg-green-2'  => array('linear-gradient(to right, #089406, #63caa9)'),
                'cg-purple'   => array('linear-gradient(to right, #de87e5, #7e15dd)'),
                'cg-purple-2' => array('linear-gradient(to right, #bd22dd, #aa82e8)'),
            ));
        }

        return $palette;
    }

    public static function gzh_menu()
    {
        $con = '<div class="options-notice"><div class="explain">
            <p>微信公众号<code>已认证的服务号</code>启用服务器之后，且微信登录功能正常后，可在此设置微信自定义菜单</p>
            <li>在下方粘贴公众号自定义菜单的json配置代码后提交即可</li>
            <li>如果失败，会直接显示微信返回的错误码，可按照错误进行分析处理</li>
            <li>设置好成功后会有几分钟的延迟才能生效，请耐心等待</li>
            <li><a target="_blank" href="https://www.zibll.com/2916.html">点此查看官网教程</a></li>
            <ajaxform class="ajax-form">
                <p><textarea ajax-name="json" row="5" placeholder="请粘贴公众号自定义菜单的json配置代码" style="width: 100%;height: 299px;"></textarea></p>
                <div class="ajax-notice"></div>
                <p><a href="javascript:;" class="but jb-blue ajax-submit"><i class="fa fa-paper-plane-o"></i> 设置自定义菜单</a>
                <a href="javascript:;" ajax-url="' . zib_get_admin_ajax_url('weixin_gzh_get_menu') . '" class="but mr6 ajax-get">获取当前菜单</a>
                <a href="javascript:;" ajax-url="' . zib_get_admin_ajax_url('weixin_gzh_freepublish_batchget') . '" class="but mr6 ajax-get">获取已发布消息列表</a>
                </p>
                <input type="hidden" ajax-name="action" value="weixin_gzh_menu">
            </ajaxform>
        </div></div>';

        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function audit_test()
    {
        $test_img = get_template_directory_uri() . '/inc/csf-framework/assets/images/audit_test.jpg';

        $con = '<div class="options-notice">
        <div class="explain">
        <p>如果您已经完成了接口配置，可以在此处进行审核测试，只要有审核结果显示则表示接入正常</p>
        <p><b>· 文本审核测试</b></p>
        <ajaxform class="ajax-form">
        <p><textarea ajax-name="content" placeholder="请输入一些内容进行测试" style="width: 100%;max-width: 500px;"></textarea></p>
        <div class="ajax-notice"></div>
        <p><a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> 文本测试</a></p>
        <input type="hidden" ajax-name="action" value="text_audit_test">
        </ajaxform>

        <p><b>· 使用以下示例图片进行图片审核测试</b></p>
        <ajaxform class="ajax-form">
        <p><img alt="图片测试" src="' . $test_img . '" width="99" height="99"></p>
        <div class="ajax-notice"></div>
        <p><a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> 图片测试</a></p>
        <input type="hidden" ajax-name="action" value="img_audit_test">
        </ajaxform>

        </div></div>';

        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function email_test()
    {
        $con = '<div class="options-notice">
        <div class="explain">
        <p><b>您可以在下方测试邮件发送功能是否正常，请输入您的邮箱账号：</b></p>
        <ajaxform class="ajax-form">
        <div class="flex ac hh"><input class="mt6 mr10" type="text" style="max-width:300px;" ajax-name="email" value="' . get_option('admin_email') . '" placeholder="88888888@qq.com"><a href="javascript:;" class="but jb-yellow ajax-submit mt6"><i class="fa fa-paper-plane-o"></i> 发送测试邮件</a></div>
        <div class="ajax-notice mt6"></div>
        <input type="hidden" ajax-name="action" value="test_send_mail">
        </ajaxform>
        </div></div>';
        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function wechat_template_msg_args()
    {
        return array(
            //    'shop_auto_delivery_fail'          => ['自动发货失败通知用户', 'status=失败说明&name=商品名称&num=订单号&time=付款时间'],
            'shop_notify_shipping_to_author'   => ['通知商家发货', 'name=商品名称&num=订单号&time=付款时间&desc=发货说明'],
            'shop_express_shipping'            => ['快递发货通知用户', 'name=商品名称&num=订单号&time=发货时间&express=快递公司&number=快递单号'],
            'shop_after_sale_to_author'        => ['用户申请售后通知商家', 'name=商品名称&num=订单号&time=售后申请时间&type=售后类型&user=申请用户'],
            'shop_after_sale_wait_user_return' => ['用户申请售后等待用户发货通知用户', 'name=商品名称&num=订单号&time=售后申请时间&type=售后类型&desc=处理说明'],
            'shop_after_sale_end'              => ['售后订单处理完成通知用户', 'name=商品名称&num=订单号&time=售后申请时间&type=售后类型&end=处理完成时间&status=处理结果说明'],
            'payment_order'                    => ['新订单通知用户', 'name=商品名称&price=金额&time=付款时间&num=订单号'],
            'payment_order_admin'              => ['新订单通知管理员', 'name=商品名称&price=金额&user=购买用户&time=时间&num=订单号'],
            'payment_order_to_income'          => ['获得创作分成通知作者', 'name=收入内容&price=收入金额&time=时间&num=订单号'],
            'payment_order_to_referrer'        => ['获得推荐佣金通知推荐人', 'name=推广内容&price=收入金额&time=时间&num=订单号'],
            'apply_withdraw_admin'             => ['用户申请提现通知管理员', 'user=申请用户&price=申请金额&time=时间'],
            'withdraw_process'                 => ['提现处理后通知提现用户', 'create_time=申请时间&process_time=处理时间&status=处理结果|常量：已处理完成/被拒绝&price=申请金额&received_price=到账金额&service_price=手续费'],
            'auth_apply_admin'                 => ['用户提交身份认证通知管理员', 'name=认证名称&time=认证时间&user=申请用户'],
            'auth_apply_process'               => ['身份认证处理后通知用户', 'status=审核结果|常量：已通过/被拒绝&name=认证名称&desc=认证简介&time=处理时间'],
            'report_user_admin'                => ['收到举报后通知管理员', 'user=被举报用户&time=举报时间&reason=举报原因&desc=举报详情'],
            'report_process'                   => ['处理用户举报后通知举报人', 'reason=举报原因&time=举报时间&desc=处理说明'],
            'bind_phone'                       => ['绑定或修改手机号通知用户', 'name=用户昵称&time=操作时间&num=手机号'],
            'bind_email'                       => ['绑定或修改邮箱通知用户', 'name=用户昵称&time=操作时间&num=邮箱'],
            'comment_to_postauthor'            => ['新评论通知文章作者', 'name=评论用户&time=评论时间&content=评论内容&post=文章标题'],
            'comment_to_parent'                => ['评论有新回复通知用户', 'name=评论用户&time=评论时间&content=评论内容'],
        );
    }

    public static function wechat_template_id()
    {
        $args = array();

        foreach (self::wechat_template_msg_args() as $k => $v) {
            $args[] = array(
                'id'      => $k . '_s',
                'type'    => 'switcher',
                'default' => false,
                'title'   => $v[0],
            );
            $args[] = array(
                'dependency' => array($k . '_s', '!=', ''),
                'id'         => $k,
                'class'      => 'compact',
                'title'      => ' ',
                'subtitle'   => '模板ID',
                'default'    => '',
                'type'       => 'text',
            );

            wp_parse_str($v[1], $keys);
            $o_keys = [];

            foreach ($keys as $id => $title) {
                $title_array = explode('|', $title);

                $o_keys[] = array(
                    'id'       => $id,
                    'type'     => 'text',
                    'class'    => 'compact mini-input',
                    'title'    => $title_array[0] ?? $title,
                    'subtitle' => $title_array[1] ?? '',
                );
            }

            $args[] = array(
                'dependency' => array($k . '_s', '!=', ''),
                'id'         => $k . '_keys',
                'title'      => ' ',
                'subtitle'   => '模板参数',
                'type'       => 'fieldset',
                'class'      => 'mini-flex-repeater compact',
                'fields'     => $o_keys,
            );
        }

        return $args;
    }

    public static function wechat_template_test()
    {
        $type_option = '<option value="">请选择需测试的模板</option>';
        foreach (self::wechat_template_msg_args() as $k => $v) {
            $type_option .= '<option value="' . $k . '"> ' . $v[0] . '</option>';
        }

        $con = '<div class="options-notice">
        <div class="explain">
        <p><b>微信公众号模板消息测试</b></p>
        <p>测试前请先保存配置，再确保您当前账号已扫码绑定微信公众号</p>
        <ajaxform class="ajax-form">
        <div class="ajax-notice"></div>
        <p class="flex ac hh"><select ajax-name="type" style="margin-right: 20px;">' . $type_option . '</select><a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> 提交测试</a></p>
        <input type="hidden" ajax-name="action" value="test_wechat_template_test">
        </ajaxform>
        </div></div>';
        return array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => $con,
        );
    }

    public static function vip_product()
    {
        return array(
            array(
                'id'      => 'price',
                'title'   => '执行价',
                'default' => '699',
                'type'    => 'number',
                'unit'    => '元',
            ),
            array(
                'id'      => 'show_price',
                'title'   => '原价',
                'desc'    => '显示在执行价格前面，并划掉',
                'default' => '999',
                'type'    => 'number',
                'unit'    => '元',
                'class'   => 'compact',
            ),
            array(
                'id'         => 'tag',
                'title'      => '促销标签',
                'class'      => 'compact',
                'desc'       => '支持HTML，请注意控制长度',
                'attributes' => array(
                    'rows' => 1,
                ),
                'type'       => 'textarea',
            ),
            array(
                'dependency' => array('tag', '!=', ''),
                'title'      => '标签颜色',
                'id'         => 'tag_class',
                'class'      => 'compact skin-color',
                'default'    => 'jb-yellow',
                'type'       => 'palette',
                'options'    => CFS_Module::zib_palette(array(), array('b', 'jb')),
            ),

            array(
                'title'   => '会员有效时间',
                'id'      => 'time',
                'class'   => 'compact',
                'desc'    => '开通会员的时长。填<code>0</code>则为永久会员',
                'default' => 3,
                'max'     => 3600,
                'min'     => 0,
                'step'    => 1,
                'unit'    => '',
                'type'    => 'spinner',
            ),
            array(
                'dependency' => array('time', '!=', ''),
                'title'      => ' ',
                'subtitle'   => '时长单位',
                'id'         => 'unit',
                'class'      => 'compact',
                'type'       => 'radio',
                'default'    => 'month',
                'inline'     => true,
                'options'    => array(
                    'day'   => '天',
                    'month' => '月',
                ),
            ),
            array(
                'dependency' => array('time', '==', 0),
                'type'       => 'submessage',
                'style'      => 'success',
                'content'    => '<strong>会员有效时间已设置为：<code>永久会员</code></strong>',
            ),
        );
    }

    public static function rebate_type()
    {
        return
        array(
            'all' => '全部订单',
            '1'   => '付费阅读',
            '2'   => '付费资源',
            '4'   => '购买会员',
            '5'   => '付费图片',
            '6'   => '付费视频',
            '9'   => '购买积分',
        );
    }

    public static function slide($hide = array())
    {
        $args = array();
        return array(
            array(
                'id'      => 'direction',
                'default' => 'horizontal',
                'title'   => '幻灯片方向',
                'inline'  => true,
                'type'    => 'radio',
                'options' => array(
                    'horizontal' => '左右切换',
                    'vertical'   => '上下切换',
                ),
            ),
            array(
                'title'   => '循环切换',
                'class'   => 'compact',
                'id'      => 'loop',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'title'   => '显示翻页按钮',
                'class'   => 'compact',
                'id'      => 'button',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'title'   => '显示指示器',
                'type'    => 'switcher',
                'id'      => 'pagination',
                'class'   => 'compact',
                'default' => false,
                'type'    => 'switcher',
            ),
            array(
                'id'      => 'effect',
                'default' => 'slide',
                'class'   => 'compact',
                'title'   => '切换动画',
                'type'    => 'select',
                'options' => array(
                    'slide'     => __('滑动', 'zibll'),
                    'fade'      => __('淡出淡入', 'zibll'),
                    'cube'      => __('3D方块', 'zibll'),
                    'coverflow' => __('3D滑入', 'zibll'),
                    'flip'      => __('3D翻转', 'zibll'),
                ),
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                ),
                'title'      => '按比例自动高度',
                'type'       => 'switcher',
                'id'         => 'scale_height',
                'default'    => true,
                'type'       => 'switcher',
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '!=', ''),
                ),
                'id'         => 'scale',
                'class'      => 'compact',
                'title'      => '长宽比例',
                'default'    => 35,
                'max'        => 300,
                'min'        => 10,
                'step'       => 5,
                'unit'       => '%',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '!=', ''),
                ),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<i class="fa fa-info-circle fa-fw"></i> 开启按比例自动高度后，幻灯片会按照设置的比例保持高度<br>同时下方PC端高度和移动端高端将失效',
            ),
            array(
                'dependency' => array(
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'title'      => '自动高度',
                'class'      => 'compact',
                'type'       => 'switcher',
                'id'         => 'auto_height',
                'default'    => false,
                'type'       => 'switcher',
            ),
            array(
                'dependency' => array(
                    array('auto_height', '!=', ''),
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'id'         => 'max_height',
                'class'      => 'compact',
                'title'      => '最大高度',
                'default'    => 500,
                'max'        => 800,
                'min'        => 120,
                'step'       => 20,
                'unit'       => 'PX',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array(
                    array('auto_height', '!=', ''),
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'id'         => 'min_height',
                'title'      => '最小高度',
                'class'      => 'compact',
                'default'    => 180,
                'max'        => 500,
                'min'        => 100,
                'step'       => 20,
                'unit'       => 'PX',
                'type'       => 'spinner',
            ),
            array(
                'dependency' => array(
                    array('auto_height', '!=', ''),
                    array('direction', '!=', 'vertical'),
                    array('scale_height', '==', ''),
                ),
                'type'       => 'submessage',
                'style'      => 'warning',
                'content'    => '<i class="fa fa-info-circle fa-fw"></i> 开启自动高度后，会根据幻灯片背景图自动调节每张幻灯片高度<br>请注意幻灯片图片的长宽比例不能差距太大，否则会显示不佳！<br>请在上方设置最大、最小高度，避免幻灯片过大过小，同时下方的PC端高度和移动端高端将失效',
            ),
            array(
                'id'      => 'pc_height',
                'class'   => 'compact',
                'title'   => '电脑端高度',
                'default' => 400,
                'max'     => 800,
                'min'     => 120,
                'step'    => 20,
                'unit'    => 'PX',
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'm_height',
                'title'   => '移动端高度',
                'class'   => 'compact',
                'default' => 200,
                'max'     => 500,
                'min'     => 100,
                'step'    => 20,
                'unit'    => 'PX',
                'type'    => 'spinner',
            ),
            array(
                'id'      => 'spacebetween',
                'title'   => '幻灯片间距',
                'default' => 15,
                'max'     => 500,
                'min'     => 0,
                'step'    => 5,
                'unit'    => 'PX',
                'type'    => 'spinner',
            ),
            array(
                'id'       => 'speed',
                'title'    => '切换速度',
                'subtitle' => '切换过程的时间(越小越快)',
                'desc'     => '设置为“0”，则为自动模式：根据幻灯片大小自动设置最佳速度',
                'class'    => 'compact',
                'default'  => 0,
                'max'      => 3000,
                'min'      => 0,
                'step'     => 100,
                'unit'     => '毫秒',
                'type'     => 'spinner',
            ),
            array(
                'title'   => '自动播放',
                'type'    => 'switcher',
                'id'      => 'autoplay',
                'class'   => 'compact',
                'default' => true,
                'type'    => 'switcher',
            ),
            array(
                'dependency' => array('autoplay', '!=', ''),
                'id'         => 'interval',
                'title'      => '停顿时间',
                'subtitle'   => '自动切换的时间间隔(越小越快)',
                'class'      => 'compact',
                'default'    => 4,
                'max'        => 20,
                'min'        => 0,
                'step'       => 1,
                'unit'       => '秒',
                'type'       => 'spinner',
            ),
        );
    }

    public static function custom_filters_options()
    {}

    public static function orderby()
    {
        return array(
            array(
                'id'          => 'lists',
                'title'       => '显示排序方式',
                'options'     => array(
                    'modified'            => '更新',
                    'date'                => '发布',
                    'views'               => '浏览',
                    'like'                => '点赞',
                    'comment_count'       => '评论',
                    'favorite'            => '收藏',
                    'zibpay_price'        => '售价',
                    'zibpay_points_price' => '积分',
                    'sales_volume'        => '销量',
                    'rand'                => '随机',
                ),
                'type'        => 'select',
                'placeholder' => '选择需要的排序方式按钮',
                'default'     => array('modified', 'views', 'like', 'comment_count'),
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
            ),
            array(
                'title'   => __('更多排序方式', 'zibll'),
                'id'      => 'dropdown',
                'class'   => 'compact',
                'default' => false,
                'label'   => '用下拉框显示全部排序方式',
                'type'    => 'switcher',
            ),
        );
    }

    public static function ajax_but($type = '')
    {
        $ajax       = true;
        $query_args = array();
        if ('topics' == $type) {
            $type       = 'tag';
            $query_args = array('taxonomy' => 'topics');
        }

        $desc = '选择并排序需要显示的按钮';
        $desc .= 'tags' == $type ? '' : '，建议选择同级内容，会自动获取二级三级内容<br/>所选按钮内没有文章则不会显示';

        $placeholder = $ajax ? '输入关键词搜索内容' : '选择并排序需要显示的按钮';
        return array(
            array(
                'id'          => 'lists',
                'title'       => '按钮列表',
                'options'     => $type,
                'query_args'  => $query_args,
                'type'        => 'select',
                'placeholder' => $placeholder,
                'chosen'      => true,
                'desc'        => $desc,
                'multiple'    => true,
                'sortable'    => true,
                'ajax'        => $ajax,
                'settings'    => array(
                    'min_length' => 2,
                ),
            ),
            array(
                'title'   => __('下拉列表', 'zibll'),
                'id'      => 'dropdown',
                'class'   => 'compact',
                'default' => false,
                'label'   => '用下拉框显示更多内容',
                'type'    => 'switcher',
            ),
            array(
                'dependency'  => array('dropdown', '!=', ''),
                'id'          => 'dropdown_lists',
                'desc'        => '请勿添加过多，避免显示很难看',
                'class'       => 'compact',
                'title'       => '下拉菜单列表',
                'options'     => $type,
                'query_args'  => $query_args,
                'type'        => 'select',
                'placeholder' => $placeholder,
                'chosen'      => true,
                'multiple'    => true,
                'sortable'    => true,
                'ajax'        => $ajax,
                'settings'    => array(
                    'min_length' => 2,
                ),
            ),
        );
    }

    public static function invit_code_reward()
    {
        $args   = array();
        $args[] = array(
            'title'   => '经验值',
            'id'      => 'level_integral',
            'default' => 0,
            'type'    => 'number',
            'unit'    => '经验值',
        );
        $args[] = array(
            'title'   => '积分',
            'id'      => 'points',
            'default' => 0,
            'class'   => 'compact',
            'type'    => 'number',
            'unit'    => '积分',
        );
        $args[] = array(
            'title'   => '余额',
            'id'      => 'balance',
            'default' => 0,
            'class'   => 'compact',
            'type'    => 'number',
            'unit'    => '余额',
        );
        $args[] = array(
            'title'   => 'VIP会员',
            'id'      => 'vip',
            'type'    => 'radio',
            'inline'  => true,
            'default' => '',
            'options' => array(
                ''  => '无',
                '1' => _pz('pay_user_vip_1_name'),
                '2' => _pz('pay_user_vip_2_name'),
            ),
        );
        $args[] = array(
            'dependency' => array('vip', '!=', ''),
            'title'      => ' ',
            'subtitle'   => '会员赠送时长',
            'desc'       => '单位为天，填<code>Permanent</code>为永久',
            'id'         => 'vip_time',
            'default'    => '',
            'class'      => 'compact',
            'type'       => 'text',
        );

        return $args;
    }

    public static function points_free()
    {
        $args   = array();
        $args[] = array(
            'content' => '设置每个任务可免费获取的积分值<br><i class="fa fa-fw fa-info-circle fa-fw"></i> 如果以下对应的功能未开启，请将分值设置为0',
            'style'   => 'warning',
            'type'    => 'submessage',
            'class'   => 'text-center',
        );

        $args[] = array(
            'title'   => '每日上限',
            'desc'    => '一个用户每天最多可获取多少免费积分，请勿低于单项值(包含签到奖励)',
            'id'      => 'day_max',
            'default' => 100,
            'max'     => 1000,
            'min'     => 0,
            'step'    => 1,
            'type'    => 'spinner',
        );

        $group_k_2 = null;

        foreach (zib_get_user_integral_add_options() as $k => $v) {
            $group_k = $v[3];
            $args[]  = array(
                'title'   => '[' . $group_k . ']' . $v[0],
                'class'   => $group_k === $group_k_2 ? 'compact' : '',
                'id'      => $k,
                'default' => $v[1],
                'max'     => 1000,
                'min'     => 0,
                'step'    => 1,
                'type'    => 'spinner',
            );
            $group_k_2 = $v[3];
        }

        return $args;
    }

    public static function user_integral()
    {

        //分组
        $args   = array();
        $args[] = array(
            'content' => '在此设置经验值的获取得分方式<br><i class="fa fa-fw fa-info-circle fa-fw"></i> 如果以下对应的功能未开启，请将分值设置为0',
            'style'   => 'warning',
            'type'    => 'submessage',
            'class'   => 'text-center',
        );

        $args[] = array(
            'title'   => '每日上限',
            'desc'    => '一个用户每天最多加多少经验值，请勿低于单项值(包含签到奖励)',
            'id'      => 'day_max',
            'default' => 100,
            'max'     => 1000,
            'min'     => 0,
            'step'    => 1,
            'type'    => 'spinner',
        );
        $group_k_2 = null;

        foreach (zib_get_user_integral_add_options() as $k => $v) {
            $group_k = $v[3];
            $args[]  = array(
                'title'   => '[' . $group_k . ']' . $v[0],
                'class'   => $group_k === $group_k_2 ? 'compact' : '',
                'id'      => $k,
                'default' => $v[1],
                'max'     => 1000,
                'min'     => 0,
                'step'    => 1,
                'type'    => 'spinner',
            );
            $group_k_2 = $v[3];
        }
        return $args;
    }

    public static function checkin_reward()
    {
        $tab = array();
        for ($i = 2; $i <= 7; $i++) {
            $tab[] = array(
                'title'  => '第' . $i . '天',
                'fields' => array(
                    array(
                        'id'      => 'points_' . $i,
                        'title'   => '奖励积分',
                        'default' => $i * 20,
                        'type'    => 'number',
                        'unit'    => '积分',
                    ),
                    array(
                        'id'      => 'integral_' . $i,
                        'title'   => '奖励经验值',
                        'default' => $i * 30,
                        'type'    => 'number',
                        'unit'    => '经验值',
                        'class'   => 'compact',
                    ),
                ),
            );
        }
        return $tab;
    }

    public static function user_level_tab()
    {
        $max = _pz('user_level_max', 10);
        $tab = array();
        for ($i = 1; $i <= $max; $i++) {
            $tab[] = array(
                'title'  => 'Lv ' . $i,
                'fields' => array(
                    array(
                        'title'   => __('等级图标', 'zibll'),
                        'id'      => 'icon_img_' . $i,
                        'desc'    => __('自定义等级的小图标，显示在昵称后方(建议尺寸120x50)') . ($i > 10 ? '<br>主题内置了10个等级图标，如需开启更高等级需要自己制作等级图标' : ''),
                        'default' => ($i < 11 ? ZIB_TEMPLATE_DIRECTORY_URI . '/img/user-level-' . $i . '.png' : ''),
                        'preview' => true,
                        'library' => 'image',
                        'type'    => 'upload',
                    ),
                    array(
                        'title'   => '等级名称',
                        'id'      => 'name_' . $i,
                        'default' => 'LV' . $i,
                        'type'    => 'text',
                    ),
                    array(
                        'title'   => '升级经验',
                        'class'   => ((1 === $i) ? 'hide' : ''),
                        'desc'    => '当用户的等级经验值达到多少时，升级到此等级<div class="c-yellow"><i class="fa fa-fw fa-info-circle fa-fw"></i>经此验值必须高于上一级的经验值，否则会出现错误</div>',
                        'id'      => 'upgrade_integral_' . $i,
                        'default' => ($i - 1) * 500 * $i,
                        'min'     => 0,
                        'step'    => 50,
                        'type'    => 'number',
                    ),
                ),
            );
        }

        return $tab;
    }

    public static function user_can_type_options($exclude = array(), $add = array())
    {
        $options = array(
            'default' => '最小默认值',
            'logged'  => '已登录用户',
        );

        $level_max = _pz('user_level_max', 10);
        for ($i = 2; $i <= $level_max; $i++) {
            $options['user_level_' . $i] = '等级达到[' . $i . '级]';
        }

        $vip_max = 2;
        for ($i = 1; $i <= $vip_max; $i++) {
            $options['vip_level_' . $i] = 'VIP会员等级达到[' . $i . '级]';
        }

        $options['auth']          = '已认证用户';
        $options['moderator']     = '拥有版主身份';
        $options['plate_author']  = '拥有超级版主身份';
        $options['cat_moderator'] = '拥有分区版主身份';
        $options['admin']         = '超级管理员';

        if ($exclude) {
            foreach ($exclude as $exclude_roles_key) {
                unset($options[$exclude_roles_key]);
            }
        }

        return array_merge($options, $add);
    }

    public static function user_can_user_fields()
    {
        $user_fields        = array();
        $user_fields['all'] = array(
            'title'   => '所有人',
            'default' => false,
            'label'   => '包含未登录的游客(开启后任何人都拥有此权限)',
            'class'   => 'compact mini',
            'id'      => 'all',
            'type'    => 'switcher',
        );
        $user_fields['logged'] = array(
            'title'   => '已登录用户',
            'default' => false,
            'label'   => '开启后只要用户登录就拥有此权限',
            'class'   => 'compact mini',
            'id'      => 'logged',
            'type'    => 'switcher',
        );

        $user_level_max = _pz('user_level_max', 10);
        if (_pz('user_level_s', true)) {
            $user_fields['level'] = array(
                'class'   => 'compact mini',
                'title'   => '用户等级',
                'id'      => 'level',
                'default' => 0,
                'max'     => $user_level_max,
                'min'     => -1,
                'step'    => 1,
                'unit'    => '级',
                'type'    => 'spinner',
            );
        }
        if (_pz('pay_user_vip_1_s', true)) {
            $user_fields['vip'] = array(
                'title'   => '会员等级',
                'id'      => 'vip',
                'default' => 0,
                'max'     => 2,
                'min'     => -1,
                'class'   => 'compact mini',
                'step'    => 1,
                'unit'    => '级',
                'type'    => 'spinner',
            );
        }
        $user_fields['auth'] = array(
            'title'   => '认证用户',
            'default' => false,
            'id'      => 'auth',
            'class'   => 'compact mini',
            'type'    => 'switcher',
        );
        $user_fields['moderator'] = array(
            'title'   => '版主',
            'default' => false,
            'class'   => 'compact mini',
            'id'      => 'moderator',
            'type'    => 'switcher',
        );
        $user_fields['plate_author'] = array(
            'title'   => '超级版主',
            'default' => false,
            'label'   => '版块作者',
            'class'   => 'compact mini',
            'id'      => 'plate_author',
            'type'    => 'switcher',
        );
        $user_fields['cat_moderator'] = array(
            'title'   => '分区版主',
            'default' => false,
            'class'   => 'compact mini',
            'id'      => 'cat_moderator',
            'type'    => 'switcher',
        );
        return $user_fields;
    }

    public static function user_caps()
    {
        $new_badge                     = zib_get_csf_option_new_badge();
        $roles_all                     = array('all', 'logged', 'level', 'vip', 'auth', 'cat_moderator', 'plate_author', 'moderator');
        $user_all_caps                 = array();
        $user_all_caps['用户功能'] = array(
            array(
                'id'      => 'user_report',
                'name'    => '举报其它用户(举报不良信息)',
                'help'    => '此权限依赖于[用户举报]功能',
                'default' => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'balance_transfer',
                'name'          => '将[余额]转账给其他用户' . $new_badge['7.4'],
                'desc'          => '此权限依赖于[余额转账]功能',
                'desc'          => '需启用<a href="' . zib_get_admin_csf_url('支付付费/余额充值') . '">用户余额以及余额转账</a>功能',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'points_transfer',
                'name'          => '将[积分]转账给其他用户' . $new_badge['7.4'],
                'desc'          => '需启用<a href="' . zib_get_admin_csf_url('用户互动/用户积分') . '">用户积分以及积分转账</a>功能',
                'exclude_roles' => array('all'),
            ),
        );
        $user_all_caps['用户操作,管理其他用户'] = array(
            array(
                'id'    => 'set_user_ban',
                'name'  => '设置用户封禁状态(将其它用户封号、拉入小黑屋)',
                'roles' => array('cat_moderator'),
                'help'  => '默认为管理员权限，论坛管理员也拥有此权限，此权限依赖于[用户封禁]功能',
            ),
            array(
                'id'    => 'medal_manually_set',
                'name'  => '为用户授予徽章',
                'roles' => array('cat_moderator'),
                'help'  => '默认为管理员权限，此权限依赖于[用户徽章]功能',
            ),
        );
        $user_all_caps['前台投稿'] = array(
            array(
                'id'      => 'new_post_add',
                'name'    => '发布新的文章',
                'default' => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_audit_no',
                'name'          => '发布投稿无需审核直接发布',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_audit_no_manual',
                'name'          => '发布投稿无需[人工审核]直接发布',
                'desc'          => '需启用<a href="' . zib_get_admin_csf_url('扩展增强/api内容审核') . '">api内容审核</a>功能，API审核通过后直接发布',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_upload_img',
                'name'          => '发布投稿允许在编辑器上传图片',
                'desc'          => '启用后在<a href="' . zib_get_admin_csf_url('功能权限/上传权限') . '">功能权限/上传权限</a>中设置批量上传和图片大小限制',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_upload_video',
                'name'          => '发布投稿允许在编辑器上传视频',
                'desc'          => '启用后在<a href="' . zib_get_admin_csf_url('功能权限/上传权限') . '">功能权限/上传权限</a>中设置大小限制',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_iframe_video',
                'name'          => '发布投稿允许在编辑器插入iframe嵌入视频',
                'exclude_roles' => array('all'),
                'default'       => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_upload_file',
                'name'          => '发布投稿允许在编辑器上传文件及插入附件下载模块' . $new_badge['7.0'],
                'exclude_roles' => array('all'),
                'desc'          => '启用后在<a href="' . zib_get_admin_csf_url('功能权限/上传权限') . '">功能权限/上传权限</a>中设置大小限制',
            ),
            array(
                'id'      => 'new_post_hide',
                'name'    => '发布投稿允许在编辑器发布隐藏内容',
                'default' => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_image_cover',
                'name'          => '发帖允许设置帖子封面（图片封面）',
                'exclude_roles' => array('all'),
                'desc'          => '设置封面时，用户可以上传内容，如果不想用户上传，请关闭此权限',
            ),
            array(
                'id'            => 'new_post_slide_cover',
                'name'          => '发帖设置封面时候允许设置【幻灯片封面】',
                'exclude_roles' => array('all'),
                'desc'          => '依赖于【发帖允许设置帖子封面（图片封面）】权限',
            ),
            array(
                'id'            => 'new_post_video_cover',
                'name'          => '发帖设置封面时候允许设置【视频封面】',
                'exclude_roles' => array('all'),
                'desc'          => '依赖于【发帖允许设置帖子封面（图片封面）】权限',
            ),
            array(
                'id'            => 'new_post_pay',
                'name'          => '发布投稿允许在设置付费内容',
                'desc'          => '此功能建议与<a href="' . zib_get_admin_csf_url('支付付费/创作分成') . '">创作分成</a>功能配合使用，如果未开启<a href="' . zib_get_admin_csf_url('支付付费/创作分成') . '">创作分成</a>功能，则用户设置的付费收益全部属于站长',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_pay_download',
                'name'          => '发布投稿允许在设置【付费下载】' . $new_badge['7.0'],
                'desc'          => '依赖于上方的“发布投稿允许在设置付费内容”权限',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'            => 'new_post_edit',
                'name'          => '修改自己发布的投稿',
                'exclude_roles' => array('all'),
                'default'       => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'new_post_delete',
                'name'          => '删除自己发布的投稿',
                'exclude_roles' => array('all'),
            ),
        );

        $user_all_caps['评论'] = array(
            array(
                'id'      => 'comment_view',
                'name'    => '查看评论',
                'default' => array(
                    'all' => true,
                ),
                'desc'    => '拥有编辑自己文章或他人文章评论权限的用户会直接拥有此权限',
            ),
            array(
                'id'            => 'comment_edit',
                'name'          => '修改自己发布的评论',
                'exclude_roles' => array('all'),
                'default'       => array(
                    'logged' => true,
                ),
            ),
            array(
                'id'            => 'comment_delete',
                'name'          => '删除自己发布的评论',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'   => 'comment_audit_no',
                'name' => '发布评论无需审核直接发布',
                'desc' => '此权限会完全覆盖wp设置-讨论中的审核规则，请谨慎开启<div class="c-yellow">拥有所在文章【批准、驳回评论权限】的用户直接拥有此权限</div>',
            ),
            array(
                'id'   => 'comment_audit_no_manual',
                'name' => '发布评论无需[人工审核]直接发布',
                'desc' => '需启用<a href="' . zib_get_admin_csf_url('扩展增强/api内容审核') . '">api内容审核</a>功能，API审核通过后直接发布<br/>此审核优先级大于wp默认审核规则，如果API审核未通过，则按照wp默认(<a href="' . admin_url('options-discussion.php') . '">wp设置-讨论</a>)规则进行判断',
            ),
        );
        $user_all_caps['评论管理,管理其他人发布的评论'] = array(
            array(
                'id'            => 'comment_set_topping_my_post',
                'name'          => '设置【自己发布的文章(帖子)】下的评论的评论置顶' . $new_badge['7.8'],
                'exclude_roles' => array('all'),
                'default'       => array(
                    'moderator'     => true,
                    'plate_author'  => true,
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'      => 'comment_set_topping_other',
                'name'    => '设置自己管理下的帖子评论的评论置顶' . $new_badge['7.8'],
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'            => 'comment_edit_my_post',
                'name'          => '修改【自己发布的文章(帖子)】下的评论',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'      => 'comment_edit_other',
                'name'    => '修改自己管理下的帖子评论',
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'            => 'comment_audit_my_post',
                'name'          => '审核(批准、驳回)【自己发布的文章(帖子)】下的评论',
                'exclude_roles' => array('all'),
                'default'       => array(
                    'moderator'     => true,
                    'plate_author'  => true,
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'      => 'comment_audit_other',
                'name'    => '审核(批准、驳回)自己管理下的帖子评论',
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'moderator'     => true,
                    'plate_author'  => true,
                    'cat_moderator' => true,
                ),
            ),
            array(
                'id'            => 'comment_delete_my_post',
                'name'          => '删除[自己发布的文章(帖子)]下的评论',
                'exclude_roles' => array('all'),
            ),
            array(
                'id'      => 'comment_delete_other',
                'name'    => '删除自己管理下的帖子评论',
                'roles'   => array('moderator', 'plate_author', 'cat_moderator'),
                'default' => array(
                    'cat_moderator' => true,
                ),
            ),
        );

        return $user_all_caps;
    }

    //用户权限fields
    public static function user_can_fields($caps = array(), $con = '')
    {
        $fields   = array();
        $fields[] = array(
            'content' => $con . '<div style="color:#f97113;"><i class="fa fa-fw fa-info-circle fa-fw"></i>注意事项：
            <br/> 1、每一项用户能力（权限）启用都是按照自上而下的顺序设置，例如 用户评论：开启登录用户，那么只要登录的用户即可拥有此权限，就无需再设置等级或者其它
            <br/> 2、部分权限涉及到一些敏感功能，请注意相关风险！
            <br/> 3、由于功能权限较多，部分权限逻辑稍微复杂，请一定要仔细查看、设置！
            <br/> 4、设置的过程中如果出现混乱，可以重置选区将当前页面的用户权限恢复到初始值
            <br/><a target="_blank" href="https://www.zibll.com/3090.html">查看官方教程</a>
            </div>',
            'style'   => 'warning',
            'type'    => 'submessage',
        );

        $user_fields = self::user_can_user_fields();
        foreach ($caps as $group_key => $group) {
            //分组
            $caps = array();
            foreach ($group as $key => $val) {
                $_fields = $user_fields;
                if (isset($val['roles'])) {
                    $_fields = array();
                    foreach ($val['roles'] as $roles_key) {
                        $_fields[] = $user_fields[$roles_key];
                    }
                } elseif (isset($val['exclude_roles'])) {
                    foreach ($val['exclude_roles'] as $exclude_roles_key) {
                        unset($_fields[$exclude_roles_key]);
                    }
                }

                $caps[] = array(
                    'title'  => $val['name'],
                    'fields' => array(array(
                        'id'      => $val['id'],
                        'default' => isset($val['default']) ? $val['default'] : array(),
                        'desc'    => isset($val['desc']) ? $val['desc'] : '',
                        'help'    => isset($val['help']) ? $val['help'] : '',
                        'type'    => 'fieldset',
                        'fields'  => $_fields,
                    )),
                );
            }

            $group_title     = $group_key;
            $group_subtitle  = '';
            $group_key_array = explode(',', $group_key);
            if (isset($group_key_array[0])) {
                $group_title = $group_key_array[0];
            }
            if (isset($group_key_array[1])) {
                $group_subtitle = $group_key_array[1];
            }

            $fields[] = array(
                'id'         => 'user_cap',
                'type'       => 'accordion',
                'class'      => 'accordion-mini',
                'title'      => $group_title,
                'subtitle'   => $group_subtitle,
                'accordions' => $caps,
            );
        }

        return $fields;
    }

    public static function translate_language_options($show_auto = true)
    {

        $languages = array(
            'chinese_simplified'  => '简体中文',
            'chinese_traditional' => '繁体中文',
            'english'             => '英语',
            'korean'              => '韩语',
            'japanese'            => '日语',
            'french'              => '法语',
            'italian'             => '意大利语',
            'deutsch'             => '德语',
            'portuguese'          => '葡萄牙语',
            'spanish'             => '西班牙语',
            'russian'             => '俄语',
            'arabic'              => '阿拉伯语',
            'swedish'             => '瑞典语',
            'turkish'             => '土耳其语',
            'ukrainian'           => '乌克兰语',
            'vietnamese'          => '越南语',
            'afrikaans'           => '南非荷兰语',
            'albanian'            => '阿尔巴尼亚语',
            'amharic'             => '阿姆哈拉语',
            'azerbaijani'         => '阿塞拜疆语',
            'bengali'             => '孟加拉语',
            'bosnian'             => '波斯尼亚语',
            'bulgarian'           => '保加利亚语',
            'burmese'             => '缅甸语',
            'catalan'             => '加泰罗尼亚语',
            'croatian'            => '克罗地亚语',
            'czech'               => '捷克语',
            'danish'              => '丹麦语',
            'dutch'               => '荷兰语',
            'estonian'            => '爱沙尼亚语',
            'filipino'            => '菲律宾语',
            'finnish'             => '芬兰语',
            'greek'               => '希腊语',
            'gujarati'            => '古吉拉特语',
            'haitian_creole'      => '海地克里奥尔语',
            'hebrew'              => '希伯来语',
            'hindi'               => '印地语',
            'hungarian'           => '匈牙利语',
            'icelandic'           => '冰岛语',
            'indonesian'          => '印尼语',
            'irish'               => '爱尔兰语',
            'kannada'             => '卡纳达语',
            'khmer'               => '高棉语',
            'lao'                 => '老挝语',
            'latvian'             => '拉脱维亚语',
            'lithuanian'          => '立陶宛语',
            'malagasy'            => '马尔加什语',
            'malay'               => '马来语',
            'malayalam'           => '马拉雅拉姆语',
            'marathi'             => '马拉地语',
            'maltese'             => '马耳他语',
            'nepali'              => '尼泊尔语',
            'norwegian'           => '挪威语',
            'oriya'               => '奥里亚语',
            'persian'             => '波斯语',
            'polish'              => '波兰语',
            'punjabi'             => '旁遮普语',
            'romanian'            => '罗马尼亚语',
            'slovak'              => '斯洛伐克语',
            'slovene'             => '斯洛文尼亚语',
            'swahili'             => '斯瓦希里语',
            'thai'                => '泰语',
            'tamil'               => '泰米尔语',
            'telugu'              => '泰卢固语',
            'urdu'                => '乌尔都语',
            'welsh'               => '威尔士语',
        );

        if ($show_auto) {
            $languages['auto'] = '自动识别';
        }

        return $languages;
    }

    public static function vip_tab($level = 1)
    {
        return array(
            array(
                'dependency' => array('pay_user_vip_' . $level . '_s', '!=', '', 'all', 'visible'),
                'id'         => 'pay_user_vip_' . $level . '_equity',
                'title'      => '会员权益简介',
                'subtitle'   => _pz('pay_user_vip_' . $level . '_name') . '简介',
                'default'    => '<li>全站资源折扣购买</li>
<li>部分内容免费阅读</li>
<li>一对一技术指导</li>
<li>VIP用户专属QQ群</li>',
                'help'       => '使用自定义HTML代码，每行用li标签包围',
                'attributes' => array(
                    'rows' => 4,
                ),
                'sanitize'   => false,
                'type'       => 'textarea',
            ),
            array(
                'dependency' => array('pay_user_vip_1_s', '!=', '', 'all', 'visible'),
                'id'         => 'vip_' . $level . '_product_s',
                'title'      => '购买会员',
                'label'      => '关闭后则不能付费购买会员，但可通过积分兑换会员，请确保已开启积分以及积分兑换会员功能',
                'default'    => true,
                'type'       => 'switcher',
            ),
            array(
                'dependency'             => array('pay_user_vip_' . $level . '_s|vip_' . $level . '_product_s', '!=|', '|', 'all', 'visible'),
                'id'                     => 'vip_' . $level . '_product',
                'title'                  => '会员商品',
                'subtitle'               => _pz('pay_user_vip_' . $level . '_name') . '的商品选项',
                'type'                   => 'group',
                'accordion_title_prefix' => '价格：￥',
                'max'                    => 8,
                'button_title'           => '添加会员商品',
                'class'                  => 'compact',
                'default'                => array(
                    array(
                        'price'      => '99',
                        'show_price' => '199',
                        'tag'        => '<i class="fa fa-fw fa-bolt"></i> 限时特惠',
                        'time'       => 3,
                        'unit'       => 'month',
                    ),
                    array(
                        'price'      => '199',
                        'show_price' => '299',
                        'tag'        => '<i class="fa fa-fw fa-bolt"></i> 站长推荐',
                        'time'       => 6,
                        'unit'       => 'month',
                    ),
                ),
                'fields'                 => CFS_Module::vip_product(),
            ),
            array(
                'dependency' => array('pay_user_vip_' . $level . '_s', '!=', '', 'all', 'visible'),
                'title'      => __('会员图标', 'zibll'),
                'id'         => 'vip_' . $level . 'img_icon',
                'desc'       => __('自定义' . _pz('pay_user_vip_' . $level . '_name') . '的图标，(建议尺寸300x300)'),
                'default'    => ZIB_TEMPLATE_DIRECTORY_URI . '/img/vip-' . $level . '.svg',
                'preview'    => true,
                'library'    => 'image', 'type' => 'upload',
            ),
        );
    }

    public static function backup()
    {
        $csf            = array();
        $prefix         = 'zibll_options';
        $options        = get_option($prefix . '_backup');
        $lists          = '暂无备份数据！';
        $admin_ajax_url = admin_url('admin-ajax.php', 'relative');
        $delete_but     = '';
        if ($options) {
            $lists   = '';
            $options = array_reverse($options);
            $count   = 0;
            foreach ($options as $key => $val) {
                $ajax_url = add_query_arg('key', $key, $admin_ajax_url);
                $del      = '<a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'options_backup_delete', '_wpnonce' => wp_create_nonce('options_backup_delete')), $ajax_url) . '" data-confirm="确认要删除此备份[' . $key . ']？删除后不可恢复！" class="but c-yellow ajax-get ml10">删除</a>';
                $restore  = '<a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'options_backup_restore', '_wpnonce' => wp_create_nonce('options_backup_restore')), $ajax_url) . '" data-confirm="确认将主题设置恢复到此备份吗？[' . $key . ']？" class="but c-blue ajax-get ml10">恢复</a>';
                $lists .= '<div class="backup-item flex ac jsb">';
                $lists .= '<div class="item-left"><div>' . $val['time'] . '</div><div> [' . $val['type'] . ']</div></div>';
                $lists .= '<span class="shrink-0">' . $restore . $del . '</span>';
                $lists .= '</div>';
                $count++;
            }
            if ($count > 3) {
                $delete_but = '<a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'options_backup_delete_surplus', 'key' => 'all', '_wpnonce' => wp_create_nonce('options_backup_delete_surplus')), $admin_ajax_url) . '" data-confirm="确认要删除多余的备份数据吗？删除后不可恢复！" class="but jb-red ajax-get">删除备份 保留最新三份</a>';
            }
        }
        $csf[] = array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> 备份&恢复</h3>
            <ajaxform class="ajax-form">
            <div style="margin:10px 0">
            <p>系统会在重置、更新等重要操作时自动备份主题设置，您可以此进行恢复备份或手动备份</p>
            <p>恢复备份后，请先保存一次主题设置，然后刷新后再做其它操作！</p>
            <p class="c-yellow">系统最多只能保存20次备份，如需长期保存，请手动下载后存留</p>
            <p class="c-yellow">请注意：恢复非当前网站或非当前主题版本的备份数据，可能会出现异常</p>
            <p><b>备份列表：</b></p>
            <div class="card-box backup-box">
            ' . $lists . '
            </div>
            </div>
            <a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'options_backup', '_wpnonce' => wp_create_nonce('options_backup')), $admin_ajax_url) . '" class="but jb-blue ajax-get">备份当前配置</a>
            ' . $delete_but . '
            <div class="ajax-notice" style="margin-top: 10px;"></div>
            </ajaxform>',
        );

        $csf[] = array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> 导入&导出</h3>
            <ajaxform class="ajax-form">
            <div style="margin:10px 0">
            <p>您可以在此处将主题配置导出为json文件，同时也可以使用json格式的配置内容进行配置导入，导入时请确保json格式正确</p>
            <textarea ajax-name="import_data" style="width: 100%;min-height: 200px;" placeholder="粘贴导出的json数据以进行导入"></textarea>
            </div>
            <input type="hidden" ajax-name="action" value="options_import">
            ' . zib_nonce_field('options_import') . '
            <a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> 导入配置</a>
            <a href="' . add_query_arg(array('action' => 'csf-export', 'unique' => $prefix, 'nonce' => wp_create_nonce('csf_backup_nonce')), $admin_ajax_url) . '" class="but jb-green" target="_blank">导出当前配置</a>
            <div class="ajax-notice" style="margin-top: 10px;"></div>
            </ajaxform>',
        );

        return $csf;
    }


    public static function docs_environment()
    {
        $csf = array();

            $docs = '<div class="flex hh zibll-doscs">';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/375.html">Zibll子比主题历史更新日志</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/3025.html">网站伪静态及固定链接设置教程-解决404错误问题</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/43082.html">Meilisearch搜索配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/47233.html">多语言智能翻译配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/46856.html">常见CDN缓存加速配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/46784.html">论坛付费板块、付费圈子配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/15886.html">文章高级筛选分类教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/15873.html">评论及用户显示IP归属地教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/951.html">网址导航页面创建教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39206.html">商城快递查询接口配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39214.html">商城首页创建教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39217.html">商品参数继承及配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39221.html">商品详情页布局教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/39144.html">商城系统入门教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/26764.html">商品优惠码配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/25892.html">网站背景图配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/13972.html">文件上传格式、大小限制配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/11852.html">视频封面图集封面配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/10537.html">用户徽章系统配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/8663.html">卡密充值到余额功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/655.html">接入支付宝收款接口教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7327.html">用户发布付费内容参与创作分成教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/18579.html">积分转账、余额转账功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7342.html">余额充值、余额支付功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7349.html">积分、签到功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/?s=人机验证">人机验证相关功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/3090.html">用户权限管理系统使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/8673.html">用户邀请码注册功能使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/zibll_word/%e7%a4%be%e5%8c%ba%e8%ae%ba%e5%9d%9b">社区论坛系列教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/41904.html">论坛帖子推荐指数排序方式详解</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2997.html">API内容审核使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2983.html">手机底部TAB栏目配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2976.html">用户等级系统配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2956.html">用户身份认证功能使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1816.html">网站布局设置、模块配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2290.html">第三方账号登录：代理登录教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2234.html">主题强大漂亮的代码高亮功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1802.html">添加广告位教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1717.html">文章目录树使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/?s=短信">短信验证码功能相关教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/?s=视频">视频功能、视频剧集功能教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1071.html">推广返佣、推荐奖励使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1246.html">新版幻灯片使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1244.html">消息系统-站内通知-用户私信功能详解</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1012.html">导航菜单添加自定义徽章及多种样式菜单教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1222.html">正确使用自定义代码示例及教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/8681.html">微信公众号模板消息推送教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2916.html">微信公众号配置自定义菜单教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/7297.html">微信分享有图教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2206.html">主题接入微信登录图文教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/1001.html">主题接入Github登录图文教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/979.html">主题接入QQ登录图文教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/958.html">文章列表显示模式设置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/886.html">海报分享功能详细教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/2195.html">古腾堡编辑器-在文章中插入TAB栏目教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/860.html">古腾堡编辑器-在文章中插入其他文章卡片教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/853.html">古腾堡编辑器-隐藏内容模块使用教程></a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/767.html">主题VIP会员系统详细使用教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/720.html">邮件SMTP发送邮件教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/689.html">编辑器增强-古腾堡编辑器块入门详解</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/683.html">强大的图片灯箱功能详解</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/675.html">使用古腾堡块在文章中插入幻灯片教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/580.html">主题付费阅读、付费资源功能详解</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/529.html">主题导航菜单设置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/519.html">主题常用功能设置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/498.html">主题前端显示配置教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/18629.html">WordPress换域名教程</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/46.html">主题详细安装教程/更新教程/首次配置指南</a></li>';
            $docs .= '<li><a target="_blank" href="https://www.zibll.com/zibll_word">更多主题文档及教程</a></li>';
            $docs .= '</div>';

            $docs .= '<style>.zibll-doscs>li{  min-width: calc(50% - 14px);margin-left: 14px;}</style>';

        $csf[] = array(
            'title'   => '主题文档',
            'type'    => 'content',
            'style'   => 'success',
            'content' => $docs,
        );

        $csf[] = array(
            'title'   => '系统环境',
            'type'    => 'content',
            'content' => '<div style="margin-left:14px;"><li><strong>操作系统</strong>： ' . PHP_OS . ' </li>
            <li><strong>运行环境</strong>： ' . $_SERVER['SERVER_SOFTWARE'] . ' </li>
            <li><strong>PHP版本</strong>： ' . PHP_VERSION . ' </li>
            <li><strong>PHP上传限制</strong>： ' . ini_get('upload_max_filesize') . ' （推荐50M及以上）</li>
            <li><strong>PHP内存限制</strong>： ' . ini_get('memory_limit') . ' （推荐1024M及以上）</li>
            <li><strong>WordPress版本</strong>： ' . get_bloginfo('version') . '</li>
            <li><strong>系统信息</strong>： ' . php_uname() . ' </li>
            <li><strong>服务器时间</strong>： ' . current_time('mysql') . '</li></div>
            <a class="but c-yellow" href="' . admin_url('site-health.php?tab=debug') . '">查看更多系统信息</a>',
        );
        $csf[] = array(
            'title'   => '推荐环境',
            'type'    => 'content',
            'content' => '<div style="margin-left:14px;"><li><strong>WordPress</strong>：5.0+，推荐使用最新版</li>
            <li><strong>PHP</strong>：PHP7.0及以上</li>
            <li><strong>服务器配置</strong>：无要求，根据内容量选择，推荐2H4G5M</li>
            <li><strong>操作系统</strong>：无要求，不推荐使用Windows系统</li></div>',
        );
        return $csf;
    }

    }
