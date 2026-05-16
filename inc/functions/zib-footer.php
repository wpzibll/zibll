<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:37
 * @LastEditTime : 2026-04-29 20:59:44
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

// foot code
add_action('wp_footer', 'zib_footer', 98);
function zib_footer()
{
    $code = '';
    if (_pz('footcode')) {
        $code .= "<!--FOOTER_CODE_START-->\n" . _pz('footcode') . "\n<!--FOOTER_CODE_END-->\n";
    }
    if (_pz('trackcode')) {
        $code .= "<!--FOOTER_CODE_START-->\n" . _pz('trackcode') . "\n<!--FOOTER_CODE_END-->\n";
    }
    if (_pz('javascriptcode')) {
        $code .= '<script type="text/javascript">' . _pz('javascriptcode') . '</script>';
    }
    echo $code;
}

add_action('wp_footer', 'zib_win_var');
function zib_win_var()
{

    $views_record = false;
    if (is_singular()) {
        global $post;
        $post_ID = $post->ID;
        if ($post_ID) {
            $views_record = $post_ID;
        }
    }

    $highlight_dark_zt      = _pz('highlight_dark_zt', 'dracula');
    $highlight_white_zt     = _pz('highlight_zt', 'enlighter');
    $highlight_theme        = zib_get_theme_mode() == 'dark-theme' ? $highlight_dark_zt : $highlight_white_zt;
    $imagelightbox_thumbs_s = (array) _pz('imagelightbox_thumbs_s', array('m_s', 'pc_s'));
    $imagelightbox_zoom_s   = (array) _pz('imagelightbox_zoom_s', array('m_s', 'pc_s'));
    $imagelightbox_full_s   = (array) _pz('imagelightbox_full_s', array('pc_s'));
    $imagelightbox_play_s   = (array) _pz('imagelightbox_play_s', array('m_s', 'pc_s'));
    $imagelightbox_down_s   = (array) _pz('imagelightbox_down_s', array('m_s', 'pc_s'));
    $wp_is_mobile           = wp_is_mobile();
    $imgbox_thumbs          = (($wp_is_mobile && in_array('m_s', $imagelightbox_thumbs_s)) || (!$wp_is_mobile && in_array('pc_s', $imagelightbox_thumbs_s)));
    $imgbox_zoom            = (($wp_is_mobile && in_array('m_s', $imagelightbox_zoom_s)) || (!$wp_is_mobile && in_array('pc_s', $imagelightbox_zoom_s)));
    $imgbox_full            = (($wp_is_mobile && in_array('m_s', $imagelightbox_full_s)) || (!$wp_is_mobile && in_array('pc_s', $imagelightbox_full_s)));
    $imgbox_play            = (($wp_is_mobile && in_array('m_s', $imagelightbox_play_s)) || (!$wp_is_mobile && in_array('pc_s', $imagelightbox_play_s)));
    $imgbox_down            = (($wp_is_mobile && in_array('m_s', $imagelightbox_down_s)) || (!$wp_is_mobile && in_array('pc_s', $imagelightbox_down_s)));
    $current_url            = zib_get_current_url();
    $sign_url               = add_query_arg('redirect_to', urlencode($current_url), zib_get_sign_url('signin'));
    $split_minimum_size     = (int) _pz('split_minimum_size', 10);
    $split_minimum_size     = $split_minimum_size < 4 ? 4 : $split_minimum_size;

    ?>
    <script type="text/javascript">
        window._win = {
            views: '<?php echo $views_record; ?>',
            www: '<?php echo esc_url(home_url()); ?>',
            uri: '<?php echo esc_url(ZIB_TEMPLATE_DIRECTORY_URI); ?>',
            ver: '<?php echo THEME_VERSION; ?>',
            imgbox: '<?php echo (bool) _pz('imagelightbox', true); ?>',
            imgbox_type: '<?php echo _pz('imagelightbox_type', 'group'); ?>',
            imgbox_thumbs: '<?php echo $imgbox_thumbs; ?>',
            imgbox_zoom: '<?php echo $imgbox_zoom; ?>',
            imgbox_full: '<?php echo $imgbox_full; ?>',
            imgbox_play: '<?php echo $imgbox_play; ?>',
            imgbox_down: '<?php echo $imgbox_down; ?>',
            sign_type: '<?php echo _pz('user_sign_type'); ?>',
            signin_url: '<?php echo $sign_url; ?>',
            signup_url: '<?php echo add_query_arg('tab', 'signup', $sign_url); ?>',
            ajax_url: '<?php echo esc_url(set_url_scheme(admin_url('admin-ajax.php'))); ?>',
            ajaxpager: '<?php echo esc_html(_pz('ajaxpager')); ?>',
            ajax_trigger: '<?php echo _pz('ajax_trigger'); ?>',
            ajax_nomore: '<?php echo _pz('ajax_nomore'); ?>',
            qj_loading: '<?php echo _pz('qj_loading'); ?>',
            highlight_kg: '<?php echo (bool) _pz('highlight_kg'); ?>',
            highlight_hh: '<?php echo _pz('highlight_hh'); ?>',
            highlight_btn: '<?php echo _pz('highlight_btn'); ?>',
            highlight_zt: '<?php echo $highlight_theme; ?>',
            highlight_white_zt: '<?php echo $highlight_white_zt; ?>',
            highlight_dark_zt: '<?php echo $highlight_dark_zt; ?>',
            upload_img_size: '<?php echo zib_get_current_user_can_number('upload_img_size', 3); ?>',
            img_upload_multiple: '<?php echo _pz('image_upload_multiple', 6); ?>',
            upload_video_size: '<?php echo zib_get_current_user_can_number('upload_video_size', 30); ?>',
            upload_file_size: '<?php echo zib_get_current_user_can_number('upload_file_size', 30); ?>',
            upload_ext: '<?php echo implode('|', array_keys(get_allowed_mime_types())); ?>',
            user_upload_nonce: '<?php echo wp_create_nonce('user_upload'); ?>',
            post_action_nonce: '<?php echo wp_create_nonce('post_action'); ?>',
            is_split_upload: '<?php echo (bool) _pz('split_upload_s', true); ?>',
            split_minimum_size: '<?php echo $split_minimum_size; ?>',
            comment_upload_img: '<?php echo(_pz('comment_img') && _pz('comment_upload_img')); ?>',
            translate_config: '',
        }
    </script>
<?php
}

function zib_translate_config_footer()
{

    $translate_options = [];
    if (_pz('translate_s', false)) {
        $translate_options      = _pz('translate_config', []);
        $translate_options['s'] = true;

        if (!empty($translate_options['service_url'])) {
            $translate_options['service_url'] = preg_split("/,|，|\s|\n/", $translate_options['service_url']);
        }

        if (!empty($translate_options['ignore_text'])) {
            //中文逗号或英文逗号分隔
            $translate_options['ignore_text'] = preg_split("/,|，|\s|\n/", $translate_options['ignore_text']);
        }

    }

    echo '<script type="text/javascript">window._win.translate_config = ' . json_encode($translate_options) . ';</script>';
}
add_action('wp_footer', 'zib_translate_config_footer');

add_action('admin_footer', 'zib_win_console', 99);
add_action('wp_footer', 'zib_win_console', 99);
function zib_win_console()
{
    ?>
    <script type="text/javascript">
        console.log("数据库查询：<?php echo get_num_queries(); ?>次 | 页面生成耗时：<?php echo timer_stop(0, 6) * 1000 . 'ms'; ?>");
    </script>
<?php
}

if (_pz('zib_baidu_push_js')) {
    add_action('wp_footer', 'zib_baidu_push_js', 98);
}

function zib_baidu_push_js()
{
    ?>
    <!--baidu_push_js-->
    <script type="text/javascript">
        (function() {
            var bp = document.createElement('script');
            var curProtocol = window.location.protocol.split(':')[0];
            if (curProtocol === 'https') {
                bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
            } else {
                bp.src = 'http://push.zhanzhang.baidu.com/push.js';
            }
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(bp, s);
        })();
    </script>
    <!--baidu_push_js-->
<?php
}

/**右侧浮动按钮 */
function zib_float_right()
{

    $btn    = '';
    $option = _pz('float_btn');
    if (!$option || !is_array($option)) {
        return;
    }

    $is_mobile = wp_is_mobile();
    $user_id   = get_current_user_id();
    $tooltip   = !$is_mobile ? ' data-toggle="tooltip"' : '';

    foreach ($option as $key => $opt) {
        if (((empty($opt['pc_s']) && !$is_mobile) || (empty($opt['m_s']) && $is_mobile)) && 'more' != $key) {
            continue;
        }

        $style = !empty($opt['color']['color']) ? '--this-color:' . $opt['color']['color'] . ';' : '';
        $style .= !empty($opt['color']['bg']) ? '--this-bg:' . $opt['color']['bg'] . ';' : '';
        $style = $style ? ' style="' . $style . '"' : '';

        switch ($key) {
            case 'theme_mode':
                $btn .= '<a' . $style . ' class="float-btn toggle-theme hover-show"' . $tooltip . ' data-placement="left" title="切换主题" href="javascript:;"><i class="fa fa-toggle-theme"></i>
                </a>';
                break;
            case 'shop_cart':
                if (_pz('shop_s') && $user_id && !apply_filters('is_shop_cart_page', false)) {
                    $count = zib_shop_get_cart_count();
                    $url   = zib_shop_get_cart_url();
                    $count = $count ? $count : '';

                    $btn .= '<a' . $style . ' class="float-btn float-btn-shop-cart nowave"' . $tooltip . ' data-placement="left" title="购物车" href="' . $url . '">
                    ' . zib_get_svg('shopping-cart') . '<badge cart-count="' . $count . '">' . $count . '</badge>
                    </a>';
                }

                break;
            case 'back_top':
                $scrollTo = 'javascript:(scrollTopTo());';
                $btn .= '<a' . $style . ' class="float-btn ontop fade"' . $tooltip . ' data-placement="left" title="返回顶部" href="' . $scrollTo . '"><i class="fa fa-angle-up em12"></i></a>';
                break;
            case 'service_qq':
                $href = $opt['qq'] ? 'https://wpa.qq.com/msgrd?v=3&uin=' . $opt['qq'] . '&site=qq&menu=yes' : '';
                $html = '<a' . $style . ' class="float-btn service-qq"' . $tooltip . ' data-placement="left" title="QQ联系" target="_blank" href="' . $href . '"><i class="fa fa-qq"></i></a>';
                $btn .= $href ? $html : '';
                break;
            case 'pay_vip':
                $pay_user_vip_2_s = _pz('pay_user_vip_2_s', true);
                if (!zib_is_close_sign() && (_pz('pay_user_vip_1_s', true) || $pay_user_vip_2_s)) {
                    $icon = zib_get_svg('vip_1');

                    if ($user_id) {
                        $vip_level = zib_get_user_vip_level($user_id);
                        $title     = (1 == $vip_level ? '升级会员' : '开通会员');
                        if (!$vip_level || ($vip_level < 2 && $pay_user_vip_2_s)) {
                            //  $icon = zib_get_svg('vip_2');
                            $open_level = $pay_user_vip_2_s ? 2 : 1;
                            $btn .= '<a' . $style . ' class="float-btn pay-vip" vip-level="' . $open_level . '"' . $tooltip . ' data-placement="left" title="' . $title . '" href="javascript:;">' . $icon . '</a>';
                        }
                    } else {
                        $btn .= '<a' . $style . ' class="float-btn signin-loader"' . $tooltip . ' data-placement="left" title="开通会员" href="javascript:;">' . $icon . '</a>';
                    }
                }

                break;
            case 'service_wechat':
                $wechat_img = $opt['wechat_img'];
                if ($wechat_img) {
                    $s_src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-sm.svg';

                    $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="lazyload" src="' . $s_src . '" data-' : '';

                    $s_img = '';
                    $s_img .= '<div class="hover-show-con dropdown-menu">';
                    $s_img .= '<img class="radius4 relative" width="100%" ' . $lazy_attr . 'src="' . $wechat_img . '"  alt="扫码添加微信' . zib_get_delimiter_blog_name() . '">';
                    $s_img .= '</div>';
                    $btn .= '<a' . $style . ' class="float-btn service-wechat hover-show nowave" title="扫码添加微信" href="javascript:;"><i class="fa fa-wechat"></i>' . $s_img . '</a>';
                }
                break;

            case 'qrcode':
                $desc = $opt['desc'];
                $desc = $desc ? '<div class="mt6 px12 muted-color">' . $desc . '</div>' : '';

                $tag   = 'span';
                $icon  = '<i class="fa fa-qrcode"></i>';
                $class = 'float-btn qrcode-btn hover-show service-wechat';
                $hover = '<div class="hover-show-con dropdown-menu"><div class="qrcode" data-size="100"></div>' . $desc . '</div>';

                $btn .= '<' . $tag . $style . ' class="' . $class . '">' . $icon . $hover . '</' . $tag . '>';
                break;
            case 'add':

                if (!empty($opt['btns'])) {
                    $icon         = $opt['icon'] ? zib_get_cfs_icon($opt['icon']) : '<i class="fa fa-add"></i>';
                    $new_add_btns = zib_get_new_add_btns($opt['btns'], 'float-btn add-btn', $icon);
                    if ($style) {
                        $new_add_btns = str_replace('class="newadd-btns', $style . ' class="newadd-btns', $new_add_btns);
                    }
                    $btn .= $new_add_btns;
                }

                break;

            case 'more':
                foreach ($opt as $more_opt) {
                    if ((!empty($more_opt['pc_s']) && !$is_mobile) || (!empty($more_opt['m_s']) && $is_mobile)) {
                        $style = !empty($more_opt['color']['color']) ? '--this-color:' . $more_opt['color']['color'] . ';' : '';
                        $style .= !empty($more_opt['color']['bg']) ? '--this-bg:' . $more_opt['color']['bg'] . ';' : '';

                        $class        = 'float-btn more-btn';
                        $hover        = '';
                        $hover_style  = '';
                        $tooltip_attr = $tooltip;
                        $tag          = 'a';
                        if (!empty($more_opt['hover'])) {
                            $hover_style  = '';
                            $tooltip_attr = '';
                            $tag          = 'span';
                            $class .= ' hover-show';
                            if (!empty($more_opt['hover_width']) && 200 != $more_opt['hover_width']) {
                                $hover_style = ' style="width:' . $more_opt['hover_width'] . 'px;"';
                            }
                            $hover = '<div' . $hover_style . ' class="hover-show-con dropdown-menu">' . $more_opt['hover'] . '</div>';
                        }

                        $icon   = $more_opt['icon'] ? zib_get_cfs_icon($more_opt['icon']) : '<i class="fa fa-heart"></i>';
                        $href   = !empty($more_opt['link']['url']) ? $more_opt['link']['url'] : 'javascript:;';
                        $target = (!empty($more_opt['link']['target']) && !empty($more_opt['link']['url'])) ? ' target="' . $more_opt['link']['target'] . '"' : '';
                        $title  = $more_opt['desc'] ? $tooltip_attr . ' data-placement="left" title="' . $more_opt['desc'] . '"' : '';
                        $style  = $style ? ' style="' . $style . '"' : '';

                        if ('javascript:;' == $href) {
                            $class .= ' nowave';
                        }

                        $btn .= '<' . $tag . $style . ' class="' . $class . '"' . $target . $title . ' href="' . $href . '">' . $icon . $hover . '</' . $tag . '>';
                    }
                }

                break;
        }
    }

    $btn       = apply_filters('zib_float_right', $btn);
    $class     = _pz('float_btn_style', 'round') . ' position-' . _pz('float_btn_position', 'bottom');
    $filter_pz = (array) _pz('float_btn_filter_css', array('m_s'));
    $class .= (($is_mobile && in_array('m_s', $filter_pz)) || (!$is_mobile && in_array('pc_s', $filter_pz))) ? ' filter' : '';

    $class .= _pz('float_btn_scroll_hide') ? ' scrolling-hide' : '';
    $class .= _pz('float_btn_scroll_down_hide') ? ' scroll-down-hide' : '';

    echo '<div class="float-right ' . $class . '">' . $btn . '</div>';
}
add_action('wp_footer', 'zib_float_right');

/**移动端底部tab */
function zib_footer_tabbar()
{
    if (!wp_is_mobile()) {
        return;
    }

    //添加挂钩
    $btn = apply_filters('footer_tabbar', false);
    if (false === $btn) {

        $option = _pz('footer_tabbar');
        if (!_pz('footer_tabbar_s', true) || !$option || !is_array($option)) {
            return;
        }

        foreach ($option as $opt) {
            $icon      = !empty($opt['icon']) ? zib_get_cfs_icon($opt['icon']) : '';
            $icon      = !empty($opt['icon_c']) ? $opt['icon_c'] : $icon;
            $icon_size = !empty($opt['icon_size']) ? $opt['icon_size'] : 24;

            /**
            $active_icon = !empty($opt['active_icon']) ?  zib_get_cfs_icon($opt['active_icon']) : '';
            $active_icon = !empty($opt['active_icon_c']) ?  $opt['active_icon_c'] : $active_icon;
            $active_icon = $active_icon ?  $active_icon : $icon;
             */
            $text  = !empty($opt['text']) ? $opt['text'] : '';
            $badge = !empty($opt['badge']) ? $opt['badge'] : '';

            $type       = $opt['type'];
            $is_active  = false;
            $show_class = 'tabbar-' . $type;
            $show_class .= $is_active ? ' active' : '';

            switch ($type) {
                case 'home':
                    $url      = home_url();
                    $home_btn = zib_get_footer_tabbar_btn($icon, $text, $url, $show_class, $badge, $icon_size);

                    if (!empty($opt['ontop'])) {

                        $ontop_icon = !empty($opt['ontop_icon']) ? $opt['ontop_icon'] : zib_get_svg('ontop-color');
                        $ontop_text = !empty($opt['ontop_text']) ? '<text>' . $opt['ontop_text'] . '</text>' : '';

                        $btn .= '<span class="tabbar-item tabbar-ontop relative-h">';
                        $btn .= '<a class="ontop tabbar-item" href="javascript:(scrollTopTo());"><icon>' . $ontop_icon . '</icon>' . $ontop_text . '</a>';
                        $btn .= $home_btn;
                        $btn .= '</span>';
                    } else {
                        $btn .= $home_btn;
                    }
                    break;

                case 'pay_vip':
                    if (!zib_is_close_sign() && (_pz('pay_user_vip_1_s', true) || _pz('pay_user_vip_2_s', true))) {
                        $user_id = get_current_user_id();

                        if ($user_id) {
                            $vip_level = zib_get_user_vip_level($user_id);
                            if (!$vip_level) {
                                $show_class .= ' pay-vip';
                                $attr = ' vip-level="1"';
                                $btn .= zib_get_footer_tabbar_btn($icon, $text, '', $show_class, $badge, $icon_size, $attr);
                            } elseif (1 == $vip_level && _pz('pay_user_vip_2_s', true)) {
                                $show_class .= ' pay-vip';
                                $attr = ' vip-level="2"';
                                $btn .= zib_get_footer_tabbar_btn($icon, $text, '', $show_class, $badge, $icon_size, $attr);
                            } else {
                                $url = zib_get_user_center_url('vip');
                                $btn .= zib_get_footer_tabbar_btn($icon, $text, $url, $show_class, $badge, $icon_size, ' rel="nofollow"');
                            }
                        } else {
                            $show_class .= ' signin-loader';
                            $btn .= zib_get_footer_tabbar_btn($icon, $text, '', $show_class, $badge, $icon_size);
                        }
                    }

                    break;

                case 'link':
                    $url = !empty($opt['link']) ? $opt['link'] : '';
                    $btn .= zib_get_footer_tabbar_btn($icon, $text, $url, $show_class, $badge, $icon_size);
                    break;
                case 'user':
                    if (!zib_is_close_sign()) {
                        $url = zib_get_user_center_url();
                        $btn .= zib_get_footer_tabbar_btn($icon, $text, $url, $show_class, $badge, $icon_size, ' rel="nofollow"');
                    }
                    break;
                case 'msg':
                    if (_pz('message_s', true)) {
                        $url   = zibmsg_get_conter_url();
                        $badge = zibmsg_get_user_msg_count(0, '', '', true);
                        $btn .= zib_get_footer_tabbar_btn($icon, $text, $url, $show_class, $badge, $icon_size, ' rel="nofollow"');
                    }
                    break;
                case 'shop_cart':
                    if (_pz('shop_s', true)) {
                        $url   = zib_shop_get_cart_url();
                        $count = zib_shop_get_cart_count();

                        $icon = '<badge cart-count="' . $count . '">' . ($count ?: '') . '</badge>' . $icon;
                        $btn .= zib_get_footer_tabbar_btn($icon, $text, $url, $show_class, $badge, $icon_size, ' rel="nofollow"');
                    }
                    break;
                case 'add':
                    if (!empty($opt['btns'])) {
                        $show_class .= ' tabbar-item';
                        $icon_size = 24 != $icon_size ? ' style="font-size:' . $icon_size . 'px;"' : '';
                        $_text     = $text ? '<text>' . $text . '</text>' : '';
                        $_icon     = $icon ? '<icon' . $icon_size . '>' . $icon . '</icon>' : '';

                        $new_add_btns = zib_get_new_add_btns($opt['btns'], $show_class, $_icon . $_text);
                        $btn .= $new_add_btns;
                    }
                    break;
            }
        }
    }

    //占位符占用
    $main_class = 'footer-tabbar';
    $main_class .= _pz('footer_tabbar_scroll_hide') ? ' scrolling-hide' : '';
    $main_class .= _pz('footer_tabbar_scroll_down_hide') ? ' scroll-down-hide' : '';

    $html = $btn ? '<div class="' . $main_class . '">' . $btn . '</div><div class="footer-tabbar-placeholder"></div>' : '';
    echo $html;
}
add_action('wp_footer', 'zib_footer_tabbar');

//获取发布模态框的按钮
function zib_get_add_modal_link($class = '', $text = '发布', $args = array())
{
    $defaults = array(
        'class'         => $class,
        'mobile_bottom' => true,
        'height'        => 163,
        'data_class'    => 'modal-mini',
        'text'          => $text,
        'query_arg'     => array(
            'action'    => 'add_btns_modal',
            'post_id'   => get_the_ID(),
            'object_id' => get_queried_object_id(),
        ),
    );
    $args = wp_parse_args($args, $defaults);

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取按钮连接
function zib_get_footer_tabbar_btn($icon = '', $text = '', $url = '', $class = '', $badge = '', $icon_size = 24, $attr = '')
{
    if (!$icon && !$text) {
        return;
    }

    $icon_size  = 24 != $icon_size ? ' style="font-size:' . $icon_size . 'px;"' : '';
    $url        = $url ? $url : 'javascript:;';
    $_text      = $text ? '<text>' . $text . '</text>' : '';
    $_text_attr = $text ? ' title="' . $text . '"' : '';
    $_icon      = $icon ? '<icon' . $icon_size . '>' . $icon . '</icon>' : '';
    $_badge     = $badge ? '<badge>' . $badge . '</badge>' : '';
    $class .= ' tabbar-item';
    $_text_attr .= $attr ? ' ' . $attr : '';
    return '<a class="' . $class . '"' . $_text_attr . ' href="' . $url . '">' . $_icon . $_text . $_badge . '</a>';
}

//文章页面显示
function zib_get_single_footer_tabbar($btn)
{

    if (!_pz('footer_tabbar_s', true)) {
        return;
    }

    $opt = _pz('footer_tabbar_single');
    if (!is_single() || !isset($opt['s']) || 'extend' != $opt['s']) {
        return $btn;
    }

    global $post;
    if (!isset($post->post_type) || 'post' != $post->post_type) {
        return $btn;
    }

    $post_id = $post->ID;

    $btn = '';

    //评论
    $comments_open = (comments_open($post_id) && !_pz('close_comments'));

    if (_pz('post_like_s')) {
        $btn .= zib_get_post_like('tabbar-item single-action-tabbar');
    }

    $btn .= zib_get_post_favorite('tabbar-item single-action-tabbar');
    if (_pz('share_s')) {
        $btn .= zib_get_post_share_btn($post, 'tabbar-item single-action-tabbar');
    }

    $comments_btn = '';
    if (apply_filters('zibpay_is_show_paybutton', false)) {
        $btn .= '<div class="tabbar-item but jb-red single-pay-tabbar">立即购买</div>';
        if ($comments_open) {
            $comment_count = _cut_count(get_comments_number($post_id));
            $comment_count = $comment_count ?: '';
            $comments_btn  = '<a href="javascript:;" class="tabbar-item single-action-tabbar" fixed-input="#respond">' . zib_get_svg('comment') . '<count>' . $comment_count . '</count></a>';
        }
    } else {
        $comments_btn = $comments_open ? zib_get_respond_mobile('#respond', esc_attr(_pz('comment_text')), 'tabbar-item', false) : '';
    }

    return $comments_btn . $btn;
}
add_filter('footer_tabbar', 'zib_get_single_footer_tabbar');

//-----底部页脚内容------
if (_pz('fcode_template') == 'template_1') {
    add_action('zib_footer_conter', 'zib_footer_con');
}
function zib_footer_con()
{

    $show_xs_1 = _pz('footer_t1_m_s');
    $show_xs_3 = _pz('footer_mini_img_m_s', true);
    $html      = '';
    $box       = '<li' . (!$show_xs_1 ? ' class="hidden-xs"' : '') . ' style="max-width: 300px;">' . zib_footer_con_1() . '</li>';
    $box .= '<li style="max-width: 550px;">' . zib_footer_con_2() . '</li>';
    $box .= '<li' . (!$show_xs_3 ? ' class="hidden-xs"' : '') . '>' . zib_footer_con_3() . '</li>';

    $c_code = _pz('fcode_customize_code');
    $c_code = $c_code ? '<p class="footer-conter">' . $c_code . '</p>' : '';
    $html   = '<ul class="list-inline">' . $box . '</ul>';
    $html .= $c_code;
    echo $html;
}

function zib_footer_con_1()
{
    $html = '';

    if (_pz('footer_t1_img')) {
        $html .= '<p><a class="footer-logo" href="' . esc_url(home_url()) . '" title="' . _pz('hometitle') . '">
                    ' . zib_get_adaptive_theme_img(_pz('footer_t1_img'), _pz('footer_t1_img_dark'), _pz('hometitle'), 'class="lazyload" style="height: 40px;"', zib_is_lazy('lazy_other', true)) . '
                </a></p>';
    }

    if (_pz('footer_t1_t')) {
        $html .= '<p class="title-h-left">' . _pz('footer_t1_t') . '</p>';
    }

    if (_pz('fcode_t1_code')) {
        $html .= '<div class="footer-muted em09">' . _pz('fcode_t1_code') . '</div>';
    }
    return $html;
}

function zib_footer_con_2()
{
    $html = '';

    if (_pz('fcode_t2_code_1')) {
        $html .= '<p class="fcode-links">' . _pz('fcode_t2_code_1') . '</p>';
    }

    if (_pz('fcode_t2_code_2')) {
        $html .= '<div class="footer-muted em09">' . _pz('fcode_t2_code_2') . '</div>';
    }
    $s_src  = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-sm.svg';
    $m_show = _pz('footer_contact_m_s', true) ? '' : ' hidden-xs';
    $html .= '<div class="footer-contact mt10' . $m_show . '">';
    if ((!wp_is_mobile() || _pz('footer_contact_m_s', true)) && _pz('footer_contact_wechat_img')) {
        $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="lazyload" src="' . $s_src . '" data-' : '';

        $s_img = '';
        $s_img .= '<div class="hover-show-con footer-wechat-img">';
        $s_img .= '<img style="box-shadow: 0 5px 10px rgba(0,0,0,.2); border-radius:4px;" height="100" ' . $lazy_attr . 'src="' . _pz('footer_contact_wechat_img') . '" alt="扫一扫加微信' . zib_get_delimiter_blog_name() . '">';
        $s_img .= '</div>';

        $html .= '<a class="toggle-radius hover-show nowave" href="javascript:;">' . zib_get_svg('d-wechat') . $s_img . '</a>';
    }
    if (_pz('footer_contact_qq')) {
        $html .= '<a class="toggle-radius" data-toggle="tooltip" target="_blank" title="QQ联系" href="https://wpa.qq.com/msgrd?v=3&uin=' . _pz('footer_contact_qq') . '&site=qq&menu=yes">' . zib_get_svg('d-qq', '-50 0 1100 1100') . '</a>';
    }
    if (_pz('footer_contact_weibo')) {
        $html .= '<a class="toggle-radius" data-toggle="tooltip" title="微博" target="_blank" href="' . _pz('footer_contact_weibo') . '">' . zib_get_svg('d-weibo') . '</a>';
    }
    if (_pz('footer_contact_email')) {
        $html .= '<a class="toggle-radius" data-toggle="tooltip" title="发邮件" href="mailto:' . _pz('footer_contact_email') . '">' . zib_get_svg('d-email', '-20 80 1024 1024') . '</a>';
    }

    //更多图标联系方式
    $more_contact = (array) _pz('footer_contact_more');
    if ($more_contact) {
        foreach ($more_contact as $contact) {
            $img  = !empty($contact['img']) ? $contact['img'] : '';
            $url  = !empty($contact['link']) ? $contact['link'] : '';
            $icon = !empty($contact['icon']) ? zib_get_cfs_icon($contact['icon']) : '<i class="fa fa-heart"></i>';
            $text = !empty($contact['desc']) ? $contact['desc'] : '';

            if ($img) {
                $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="lazyload" src="' . $s_src . '" data-' : '';
                $s_img     = '';
                $s_img .= '<div class="hover-show-con footer-wechat-img">';
                $s_img .= '<img style="box-shadow: 0 5px 10px rgba(0,0,0,.2); border-radius:4px;" height="100" ' . $lazy_attr . 'src="' . esc_url($img) . '" alt="' . $text . zib_get_delimiter_blog_name() . '">';
                $s_img .= '</div>';

                $html .= '<a class="toggle-radius hover-show nowave" href="javascript:;">' . $icon . $s_img . '</a>';
            } elseif ($url) {
                $html .= '<a class="toggle-radius" data-toggle="tooltip" target="_blank" title="' . $text . '" href="' . esc_url($url) . '">' . $icon . '</a>';
            }
        }
    }

    $html .= '</div>';
    return $html;
}

function zib_footer_con_3()
{
    $html = '';
    $imgs = (array) _pz('footer_mini_img');
    if (!$imgs) {
        return;
    }

    $s_src     = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-sm.svg';
    $lazy_attr = zib_is_lazy('lazy_other', true) ? 'class="lazyload" src="' . $s_src . '" data-' : '';

    foreach ($imgs as $img) {
        if (!empty($img['image'])) {
            $text = !empty($img['text']) ? $img['text'] : '';
            $html .= '<div class="footer-miniimg"' . ($text ? ' data-toggle="tooltip" title="' . $text . '"' : '') . '>
            <p>
            <img ' . $lazy_attr . 'src="' . $img['image'] . '" alt="' . $text . zib_get_delimiter_blog_name() . '">
            </p>
            <span class="opacity8 em09">' . $text . '</span>
        </div>';
        }
    }
    return $html;
}
