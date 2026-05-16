<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-09 22:46:56
 * @LastEditTime : 2026-04-11 15:45:35
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户中心页面的相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//输出用户中心页面的头部
function zib_user_page_header()
{
    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;

    $info_class = 'flex header-info relative hh';
    $cover      = get_user_cover_img($user_id);
    $dropup_btn = '';
    $avatar     = zib_get_avatar_box($user_id, 'avatar-img', false, false);

    $avatar = '<div class="hover-show relative">';
    $avatar .= zib_get_avatar_box($user_id, 'avatar-img', false, false);
    $avatar .= zib_get_user_avatar_set_link('absolute hover-show-con flex jc xx', '<i class="fa fa-camera mb6" aria-hidden="true"></i>修改头像') ?: ($user_id ? zib_get_user_home_link($user_id, 'absolute', '') : '');
    $avatar .= '</div>';

    $desc = '';
    $btns = '';

    if ($user_id) {
        $dropup_btn = '<div class="abs-center right-bottom box-body cover-btns">' . zib_get_user_page_header_dropup_btn($user_id) . '</div>';
        $name       = '<span class="display-name">' . zibpay_get_vip_icon(zib_get_user_vip_level($user_id), 'mr3') . $user->display_name . zib_get_user_auth_badge($user_id, 'ml3') . zib_get_user_level_badge($user_id, 'ml3') . '</span>';

        if (_pz('checkin_s')) {
            $btns = zib_get_user_checkin_btn('but c-blue ml10 pw-1em radius', '<i class="fa fa-calendar-check-o"></i>签到', '<i class="fa fa-calendar-check-o"></i>已签到');
        } else {
            $btns = zib_get_user_home_link($user_id, 'but c-blue ml10 pw-1em radius', '<i class="fa fa-map-marker"></i>我的主页');
        }

        if (_pz('message_s')) {
            $btns .= zibmsg_nav_radius_button($user_id, 'ml10');
        }

        $btns = '<div class="header-btns flex0 flex ac">' . $btns . '</div>';

        $desc = '<span class="but" data-clipboard-tag="用户名" data-clipboard-text="' . $user->user_login . '"><i class="fa fa-user-o"></i>' . $user->user_login . '</span>';
        $desc .= $user->user_email ? '<span class="but" data-clipboard-tag="邮箱" data-clipboard-text="' . $user->user_email . '"><i class="fa fa-envelope-o"></i>' . $user->user_email . '</span>' : '';

        $desc = apply_filters('user_page_header_desc', $desc, $user_id);

        $info_html_flex1 = '<div class="flex1">';
        $info_html_flex1 .= '<div class="em12 name">' . $name . '</div>';
        $info_html_flex1 .= '<div class="desc user-identity flex ac hh">' . $desc . '</div>';
        $info_html_flex1 .= '</div>';
    } else {
        $info_class .= ' signin-loader';
        $info_html_flex1 = '<a href="javascript:;" class="display-name">Hi！请登录</a>';
    }

    $info_html = '<div class="' . $info_class . '">';
    $info_html .= '<div class="flex0 header-avatar">';
    $info_html .= $avatar;
    $info_html .= '</div>';
    $info_html .= $info_html_flex1;
    $info_html .= $btns;
    $info_html .= '</div>';

    $html = '<div class="author-header mb20 radius8 main-shadow main-bg full-widget-sm">';
    $html .= '<div class="page-cover">' . $cover . '<div class="absolute linear-mask"></div>' . $dropup_btn . '</div>';
    $html .= '<div class="header-content">';
    $html .= $info_html;
    $html .= '</div>';
    $html .= '</div>';
    echo $html;
}
add_action('user_center_page_content', 'zib_user_page_header', 8);

function zib_get_user_page_nav_title($a = '', $b = '')
{
    return '<span class="flex ac">' . $a . '</span><span class="em09 muted-2-color">' . $b . '<i class="ml6 fa fa-angle-right show-sm em12"></i></span>';
}

function zib_get_user_page_header_dropup_btn($user_id)
{

    $lists = '<li>' . zib_get_user_home_link($user_id, '', '<i class="fa fa-map-marker mr6"></i>我的主页') . '</li>';
    $lists .= '<li>' . zib_get_user_cover_set_link('', '<i class="fa fa-camera mr6" aria-hidden="true"></i>修改封面') . '</li>';

    return '<span class="dropup pull-right"><a href="javascript:;" class="item mr3 toggle-radius" data-toggle="dropdown">' . zib_get_svg('menu_2') . '</a><ul class="dropdown-menu">' . $lists . '</ul></span>';
}

//用户中心页面的主内容
function zib_user_page_content()
{

    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;

    $tabs_array = apply_filters('user_ctnter_main_tabs_array', array());

    foreach ($tabs_array as $k => $v) {
        $tabs_array[$k]['nav_attr'] .= ' data-drawer="show" route="' . zib_get_user_center_url($k) . '" route-back="' . zib_get_user_center_url() . '"';
    }

    $tab_nav = zib_get_main_tab_nav('nav', $tabs_array, 'user', false, 'user_center');

    if ($user_id) {
        $tab_content = zib_get_main_tab_nav('content', $tabs_array, 'user', false, 'user_center');
    } else {
        $tab_content = '<div class="zib-widget flex jc" style="min-height: 360px;">' . zib_get_user_singin_page_box() . '</div>';
    }

    if ($tab_nav && $tab_content) {
        echo '<div class="user-center row gutters-10">';
        echo '<div class="col-sm-3">';
        echo '<div class="sidebar-user">';
        dynamic_sidebar('user_sidebar_top');
        echo '</div>';

        echo apply_filters('user_center_page_sidebar', '');
        echo '<div class="sidebar-user">';
        dynamic_sidebar('user_sidebar_bottom');
        echo '</div>';

        echo '<div class="hide">';
        echo $tab_nav;
        echo '</div>';
        echo '</div>';
        echo '<div class="user-center-content col-sm-9 drawer-sm right">';
        echo $tab_content;
        echo '</div>';
        echo '</div>';
    }
}
add_action('user_center_page_content', 'zib_user_page_content');

/**
 * @description: 用户中心第一行的显示按钮
 * @param {*} $tabs_array
 * @return {*}
 */
function zib_user_ctnter_main_tabs_array_filter_main($tabs_array)
{

    if (_pz('user_level_s', true)) {
        $tabs_array['level'] = array(
            'title'    => '我的等级',
            'nav_attr' => 'drawer-title="我的等级"',
            'loader'   => '<div class="row gutters-10"><div class="col-sm-5"><div class="colorful-bg jb-vip1 zib-widget"><div class="colorful-make"></div><div class="relative flex xx jsb" style="height: 146px;"><div class="placeholder k1"></div><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div></div><div class="col-sm-7"><div class="colorful-bg c-gray zib-widget"><div class="colorful-make"></div><div class="relative flex xx jsb" style="height: 146px;"><div class="placeholder k1"></div><div class="placeholder t1"></div><div class="placeholder s1"></div></div></div></div></div><div class="zib-widget"><div class="box-body"><p class="placeholder k1"></p><p class="placeholder k2"></p><p class="placeholder k1" style="height: 120px;"></p><p class="placeholder t1"></p><p class="placeholder k1"></p><p class="placeholder t1"></p><p class="placeholder k1"></p><p class="placeholder k1"></p><p class="placeholder k1"></p></div></div>',
        );
    }

    if (_pz('user_auth_s', true)) {
        $tabs_array['auth'] = array(
            'title'    => '官方认证',
            'nav_attr' => 'drawer-title="官方认证"',
            'loader'   => '<div style="padding: 40px 20px;" class="colorful-bg c-blue flex jc zib-widget"><div class="colorful-make"></div><div class="text-center"><div class="em4x"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-user-auth"></use></svg></div><div class="mt10 em14 padding-w10 font-bold mb40">官方认证</div><div class="placeholder" style="width: 120px;height: 30px;">  </div></div></div>',
        );
    }

    if (_pz('post_rewards_s') || _pz('pay_rebate_s')) {
        $tabs_array['rewards'] = array(
            'title'    => '打赏收款',
            'nav_attr' => 'drawer-title="打赏收款设置"',
            'loader'   => '<div class="zib-widget"><div class="mt10"><div class="placeholder k1 mb10"></div><div class="placeholder k1 mb10"></div><div class="placeholder s1"></div></div><p class="placeholder k1 mb30"></p><div class="placeholder t1 mb30"></div><p class="placeholder k1 mb30"></p><p style="height: 120px;" class="placeholder t1"></p></div>',
        );
    }

    $tabs_array['data'] = array(
        'title'         => '个人资料',
        'nav_attr'      => 'drawer-title="个人资料设置"',
        'content_class' => 'userdata-set',
        'loader'        => '<div class="zib-widget"><div class="mb30"><div class="author-set-left"><span class="avatar-img avatar-lg"><div class="placeholder avatar"></div></span></div><div class="author-set-right mt6"><div class="placeholder k1 mb10"></div><div class="placeholder k1 mb10"></div><div class="placeholder s1"></div></div></div><p class="placeholder k1 mb30"></p><div class="placeholder t1 mb30"></div><p class="placeholder k1 mb30"></p><p style="height: 120px;" class="placeholder t1"></p></div>',
    );

    $tabs_array['account'] = array(
        'title'         => '账户绑定及安全',
        'nav_attr'      => 'drawer-title="账户绑定及安全"',
        'content_class' => 'author-user-con',
        'loader'        => '<div class="zib-widget"><div class="mt10"><div class="placeholder k1 mb10"></div><div class="placeholder k1 mb10"></div><div class="placeholder s1"></div></div><p class="placeholder k1 mb30"></p><div class="placeholder t1 mb30"></div><p class="placeholder k1 mb30"></p><p style="height: 120px;" class="placeholder t1"></p></div>',
    );

    return $tabs_array;
}
add_filter('user_ctnter_main_tabs_array', 'zib_user_ctnter_main_tabs_array_filter_main');

/**
 * @description: 用户中心侧边栏的第一个：数据统计
 * @param {*} $con
 * @return {*}
 */
function zib_user_center_page_sidebar_statistics($con)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return $con;
    }

    $args = array();

    $author_tab_args = zib_get_author_main_tab_args($user_id);

    if (isset($author_tab_args['post'])) {
        $args[] = array(
            'name'  => '文章',
            'count' => zib_get_user_post_count($user_id, 'publish'),
            'link'  => zib_get_user_home_url($user_id, array('tab' => 'post')),
        );
    }

    $args[] = array(
        'name'  => '评论',
        'count' => get_user_comment_count($user_id),
        'link'  => isset($author_tab_args['comment']) ? zib_get_user_home_url($user_id, array('tab' => 'comment')) : '',
    );

    $args[] = array(
        'name'  => '收藏',
        'count' => zib_get_user_favorite_count($user_id),
        'link'  => isset($author_tab_args['favorite']) ? zib_get_user_home_url($user_id, array('tab' => 'favorite')) : '',
    );

    $args[] = array(
        'name'  => '粉丝',
        'count' => get_user_followed_count($user_id),
        'link'  => isset($author_tab_args['follow']) ? zib_get_user_home_url($user_id, array('tab' => 'follow')) : '',
    );

    $args = apply_filters('user_sidebar_statistics_args', $args, $user_id);
    $item = '';

    foreach ($args as $arg) {
        $item .= '<a class="user-statistics-item" href="' . ($arg['link'] ? $arg['link'] : 'javascript:;') . '"><div class="em14">' . _cut_count($arg['count']) . '</div><div class="em09 opacity5 mt6">' . $arg['name'] . '</div></a>';
    }

    $con .= '<div class="mb10-sm mb20 flex ac jsa zib-widget padding-10 text-center">' . $item . '</div>';
    return $con;
}
add_filter('user_center_page_sidebar', 'zib_user_center_page_sidebar_statistics');

/**
 * @description: 用户中心侧边栏-按钮组
 * @param {*}
 * @return {*}
 */
function zib_user_center_page_sidebar_button_1($con)
{

    $buttons = array(
        array(
            'html' => '',
            'icon' => '',
            'name' => '',
            'tab'  => '',
        ),
    );

    if (_pz('user_level_s', true)) {
        $buttons[] = array(
            'html' => '',
            'icon' => zib_get_svg('trend-color'),
            'name' => '我的等级',
            'tab'  => 'level',
        );
    }

    if (_pz('user_auth_s', true)) {
        $buttons[] = array(
            'html' => '',
            'icon' => zib_get_svg('user-auth'),
            'name' => '官方认证',
            'tab'  => 'auth',
        );
    }

    $buttons = apply_filters('zib_user_center_page_sidebar_button_1_args', $buttons);

    $buttons_html = '';
    foreach ($buttons as $but) {
        if (!empty($but['html'])) {
            $buttons_html .= $but['html'];
        } elseif (!empty($but['icon'])) {
            $buttons_html .= '<item class="icon-but-' . $but['tab'] . '" data-onclick="[data-target=\'#user-tab-' . $but['tab'] . '\']" ><div class="em16">' . $but['icon'] . '</div><div class="px12 muted-color mt3">' . $but['name'] . '</div></item>';
        }
    }

    $con .= $buttons_html ? '<div class="zib-widget padding-6 mb10-sm"><div class="padding-6 ml3">我的服务</div><div class="flex ac hh text-center icon-but-box user-icon-but-box">' . $buttons_html . '</div></div>' : '';
    return $con;
}
add_filter('user_center_page_sidebar', 'zib_user_center_page_sidebar_button_1', 50);

/**
 * @description: 用户中心侧边栏-按钮组2
 * @param {*}
 * @return {*}
 */
function zib_user_center_page_sidebar_button_2($con)
{

    $buttons = array(
        array(
            'html' => '',
            'icon' => zib_get_svg('user-color'),
            'name' => '个人资料',
            'tab'  => 'data',
        ),
    );

    if (_pz('post_rewards_s') || _pz('pay_rebate_s')) {
        $buttons[] = array(
            'html' => '',
            'icon' => zib_get_svg('gift-color'),
            'name' => '打赏收款',
            'tab'  => 'rewards',
        );
    }
    $buttons[] = array(
        'html' => '',
        'icon' => zib_get_svg('security-color'),
        'name' => '账户安全',
        'tab'  => 'account',
    );

    $buttons = apply_filters('zib_user_center_page_sidebar_button_2_args', $buttons);

    $buttons_html = '';
    foreach ($buttons as $but) {
        if ($but['html']) {
            $buttons_html .= $but['html'];
        } elseif ($but['icon']) {
            $buttons_html .= '<item class="icon-but-' . $but['tab'] . '" data-onclick="[data-target=\'#user-tab-' . $but['tab'] . '\']" ><div class="em16">' . $but['icon'] . '</div><div class="px12 muted-color mt3">' . $but['name'] . '</div></item>';
        }
    }

    $con .= $buttons_html ? '<div class="zib-widget padding-6"><div class="padding-6 ml3">功能设置</div><div class="flex ac hh text-center icon-but-box user-icon-but-box">' . $buttons_html . '</div></div>' : '';
    return $con;
}
add_filter('user_center_page_sidebar', 'zib_user_center_page_sidebar_button_2', 60);

//修改资料页面
function zib_main_user_tab_content_data()
{

    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;

    if (!$user_id) {
        return;
    }

    $privacy = zib_get_user_meta($user_id, 'privacy', true);
    $gender  = get_user_meta($user_id, 'gender', true);

    $html            = '';
    $avatar          = zib_get_avatar_box($user_id, 'avatar-img avatar-lg', false, false);
    $avatar_set_link = zib_get_user_avatar_set_link('but c-blue p2-10 em09 ml6 hollow shrink0', '修改头像');

    $html .= '<div class="mb30">
                <div class="author-set-left">' . $avatar . '</div>
                <div class="author-set-right mt10">
                    <div class="flex ac"><b class="em12">' . esc_attr($user->display_name) . '</b>' . $avatar_set_link . '</div>
                    <div class="muted-2-color mt6">注册时间：' . get_date_from_gmt($user->user_registered) . '</div>
                    <div class="muted-2-color mt6">最后登录：' . get_user_meta($user_id, 'last_login', true) . '</div>
              </div>
            </div>';

    $html .= '<div class="mb30">
                <div class="author-set-left">昵称</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="name" value="' . esc_attr($user->display_name) . '" placeholder="请输入用户名">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">个人签名</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="desc" value="' . esc_attr(get_user_meta($user_id, 'description', true)) . '" placeholder="请简短的介绍自己">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">隐私设置</div>
                <div class="author-set-right form-select">
                    <select class="form-control" name="privacy">
                        <option value="not_show"' . selected('not_show', $privacy, false) . '>社交资料 所有人都不可见</option>
                        <option value="public" ' . selected('public', $privacy, false) . '>社交资料 所有人可见</option>
                        <option value="just_logged" ' . selected('just_logged', $privacy, false) . '>社交资料 仅注册用户可见</option>
                    </select>
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">性别</div>
                <div class="author-set-right form-select">
                    <select class="form-control" name="gender">
                        <option value="保密" ' . selected('保密', $gender, false) . '>保密</option>
                        <option value="男" ' . selected('男', $gender, false) . '>男</option>
                        <option value="女" ' . selected('女', $gender, false) . '>女</option>
                    </select>
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">居住地</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="address" value="' . esc_attr(zib_get_user_meta($user_id, 'address', true)) . '" placeholder="请输入居住地址">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">个人网站</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="url_name" value="' . esc_attr(zib_get_user_meta($user_id, 'url_name', true)) . '" placeholder="请输入网站名称">
                    <input type="input" class="form-control" name="url" style="margin-top:10px" value="' . esc_attr($user->user_url) . '" placeholder="请输入网址">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">QQ</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="qq" value="' . esc_attr(zib_get_user_meta($user_id, 'qq', true)) . '" placeholder="请输入QQ">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">微信</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="weixin" value="' . esc_attr(zib_get_user_meta($user_id, 'weixin', true)) . '" placeholder="请输入微信">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">微博</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="weibo" value="' . esc_attr(zib_get_user_meta($user_id, 'weibo', true)) . '" placeholder="请输入微博地址">
                </div>
            </div>
            <div class="mb30">
                <div class="author-set-left">Github</div>
                <div class="author-set-right">
                    <input type="input" class="form-control" name="github" value="' . esc_attr(zib_get_user_meta($user_id, 'github', true)) . '" placeholder="请输入Github地址">
                </div>
            </div>
            <div class="mb10">
                <div class="author-set-left"></div>
                <div class="author-set-right">
                    <input type="hidden" name="user_id" value="' . $user_id . '">
                    <input type="hidden" name="action" value="user_edit_datas">
                    '.zib_nonce_field('user_edit_datas').'
                    <button type="button" zibajax="submit" class="but jb-blue padding-lg" name="submit"><i class="fa fa-check mr10"></i>确认提交</button>
                </div>
            </div>';

    $html = '<form class="zib-widget">' . $html . '</form>';
    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_data', 'zib_main_user_tab_content_data');

function zib_get_user_avatar_set_link($class = '', $text = '修改头像')
{
    if (!_pz('avatar_upload_s', true)) {
        return;
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => 'avatar-set-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 410,
        'text'          => $text,
        'query_arg'     => array(
            'action' => 'user_avatar_set_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_get_user_cover_set_link($class = '', $text = '修改封面')
{

    if (!_pz('user_cover_upload_s', true)) {
        return;
    }

    $user_id = get_current_user_id();

    if (!$user_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => 'avatar-set-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 330,
        'text'          => $text,
        'query_arg'     => array(
            'action' => 'user_cover_set_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

function zib_get_user_collection_set_link($class = '', $text = '收款设置', $new_modal = false)
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => 'collection-set-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 423,
        'text'          => $text,
        'query_arg'     => array(
            'action' => 'user_collection_set_modal',
        ),
    );
    if ($new_modal) {
        $args['new'] = true;
    }

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

//获取用户中心绑定邮箱等功能的按钮
function zib_get_user_set_link($class = '', $tab = '', $text = '')
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $args = array(
        'tag'           => 'a',
        'class'         => 'collection-set-link ' . $class,
        'mobile_bottom' => true,
        'height'        => 220,
        'data_class'    => 'modal-mini',
        'text'          => $text,
        'query_arg'     => array(
            'tab'    => $tab,
            'action' => 'user_set_modal',
        ),
    );

    //每次都刷新的modal
    return zib_get_refresh_modal_link($args);
}

/**
 * @description: 头像设置的模态框
 * @param {*}
 * @return {*}
 */
function zib_get_user_avatar_set_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $add = ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg';

    $form = '<form class="mini-upload">';
    $form .= '<div class="form-upload mb20">';
    $form .= '<p class="muted-2-color">选择一张图片作为头像，支持jpg、gif格式，最大' . zib_get_current_user_can_number('upload_img_size', 3) . 'M，建议尺寸150x150</p>';
    $form .= '<label class="pointer flex ac" style="width: 100%;">';
    $form .= '<div class="preview upload-preview radius4" style="width: 140px;height: 140px;">'; //正方形
    $form .= '<img class="fit-cover" src="' . $add . '">';
    $form .= '</div>';
    $form .= '<div class="preview upload-preview radius">'; //正方形
    $form .= '<img class="fit-cover" src="' . $add . '">';
    $form .= '</div>';
    $form .= '<input class="hide" type="file" zibupload="image_upload" accept="image/gif,image/jpeg,image/jpg" name="image_upload" action="image_upload">';
    $form .= '</label>';
    $form .= '</div>';

    $form .= '<div class="modal-buts but-average"><button type="button" action="info.upload" zibupload="submit" class="but c-blue" name="submit"><i class="fa fa-check mr10"></i>确认修改</button></div>';

    $form .= wp_nonce_field('upload_avatar', 'upload_avatar_nonce', false, false) . '<input type="hidden" name="user_id" value="' . $user_id . '"><input type="hidden" name="action" value="user_upload_avatar">';
    $form .= '</form>';

    $header = zib_get_modal_colorful_header('jb-blue', zib_get_svg('circle'), '修改头像');

    $html = $header . $form;

    return $html;
}

/**
 * @description: 封面设置的模态框
 * @param {*}
 * @return {*}
 */
function zib_get_user_cover_set_modal()
{
    $user_id = get_current_user_id();
    if (!$user_id) {
        return;
    }

    $add = ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg';

    $form = '<form class="mini-upload">';
    $form .= '<div class="form-upload mb20">';
    $form .= '<p class="muted-2-color">选择一张深色图片作为个人封面，支持jpg、gif格式，最大' . zib_get_current_user_can_number('upload_img_size', 3) . 'M，建议尺寸1000x500</p>';
    $form .= '<label class="pointer" style="width: 100%;">';
    $form .= '<div class="cover-preview radius8 relative">';
    $form .= '<div class="preview-container preview abs-center">';
    $form .= '<img class="fit-cover" src="' . $add . '">';
    $form .= '</div>';
    $form .= '</div>';
    $form .= '<input class="hide" type="file" zibupload="image_upload" accept="image/gif,image/jpeg,image/jpg" name="image_upload" action="image_upload">';
    $form .= '</label>';
    $form .= '</div>';

    $form .= '<div class="modal-buts but-average"><button type="button" action="info.upload" zibupload="submit" class="but c-blue" name="submit"><i class="fa fa-check mr10"></i>确认修改</button></div>';

    $form .= wp_nonce_field('upload_cover', 'upload_cover_nonce', false, false) . '<input type="hidden" name="user_id" value="' . $user_id . '"><input type="hidden" name="action" value="user_upload_cover">';
    $form .= '</form>';

    $header = zib_get_modal_colorful_header('jb-blue', zib_get_svg('circle'), '修改个人封面');
    $html   = $header . $form;

    return $html;
}

function zib_main_user_tab_content_account()
{

    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;
    if (!$user_id) {
        return;
    }

    $con   = '';
    $email = $user->user_email;

    if ($email) {
        $btn  = zib_get_user_set_link('but c-yellow-2 p2-10 but hollow', 'email', '修改');
        $desc = '' . esc_attr($email);
    } else {
        $btn  = zib_get_user_set_link('but c-blue p2-10 but hollow', 'email', '绑定');
        $desc = '暂未绑定';
    }

    $con .= '<div class="oauth-bind-box"><div class=""><div class="flex ac jsb muted-box">
                <div class="flex ac type-logo"><span class="b-blue circular mr6 em14"><i class="fa fa-envelope"></i></span><span class="">邮箱</span></div>
                <div class="overflow-hidden"><div class="text-ellipsis muted-2-color">' . $desc . '</div></div>
                <div class="shrink0">' . $btn . '</div>
                </div></div></div>';

    $title = '<div class="title-h-left"><b>绑定邮箱</b></div>';
    $title .= '<div class="muted-2-color mb20">绑定邮箱账号，及时接收订单、审核等重要信息</div>';

    $html = '<div class="box-body">' . $title . $con . '</div>';
    $html = apply_filters('user_center_account_setup', $html, $user_id, $user); //设置
    $html = '<div class="zib-widget account-set nopw-sm">' . $html . '</div>';

    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_account', 'zib_main_user_tab_content_account');

if (_pz('user_bind_option', false, 'bind_phone')) {
    add_filter('user_center_account_setup', 'zib_oauth_phone_set', 10, 2);
}
function zib_oauth_phone_set($html, $user_id)
{

    if (!$user_id) {
        return;
    }

    $phone = zib_get_user_phone_number($user_id);
    $con   = '';

    if ($phone) {
        $btn  = zib_get_user_set_link('but c-yellow p2-10 but hollow', 'phone', '修改');
        $desc = '' . esc_attr($phone);
    } else {
        $btn  = zib_get_user_set_link('but c-blue p2-10 but hollow', 'phone', '绑定');
        $desc = '暂未绑定';
    }

    $con .= '<div class="oauth-bind-box"><div class=""><div class="flex ac jsb muted-box">
                <div class="flex ac type-logo"><span class="b-blue-2 circular mr6 em14"><i class="fa fa-phone"></i></span><span class="">手机号</span></div>
                <div class="muted-2-color">' . $desc . '</div>
                <div class="">' . $btn . '</div>
                </div></div></div>';

    $title = '<div class="title-h-left"><b>绑定手机</b></div>';
    $title .= '<div class="muted-2-color mb20">绑定手机号，提高账户安全性</div>';

    return $html . '<div class="box-body">' . $title . $con . '</div>';
}

function zib_oauth_set($html, $user_id)
{
    if (!$user_id || _pz('social')) {
        return;
    }

    $con  = '';
    $rurl = zib_get_user_center_url('account');
    $args = zib_get_social_type_data();

    foreach ($args as $arg) {
        $name = $arg['name'];
        $type = $arg['type'];
        $icon = zib_get_cfs_icon($arg['icon']);

        $bind_href = zib_get_oauth_login_url($type, $rurl);
        if ($bind_href) {
            $oauth_info = zib_get_user_meta($user_id, 'oauth_' . $type . '_getUserInfo', true);
            $oauth_id   = get_user_meta($user_id, 'oauth_' . $type . '_openid', true);
            if ($oauth_info && $oauth_id) {
                //已绑定
                $user_name   = !empty($oauth_info['name']) ? esc_attr($oauth_info['name']) : (!empty($oauth_info['nick_name']) ? esc_attr($oauth_info['nick_name']) : $name . '账号');
                $user_avatar = !empty($oauth_info['avatar']) ? $oauth_info['avatar'] : '';

                if ($user_avatar) {

                    //优化百度头像地址
                    $user_avatar = str_replace('tb.himg.baidu.com', 'himg.bdimg.com', $user_avatar);
                    $user_avatar = preg_replace('/^(https:|http:)/', '', $user_avatar);

                    $lazy_attr   = zib_get_lazy_attr('lazy_avatar', $user_avatar, 'avatar', ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
                    $user_avatar = '<span class="avatar-img avatar-sm mr6" style="--this-size: 22px;"><img ' . $lazy_attr . ' alt="' . $name . '头像' . zib_get_delimiter_blog_name() . '"></span>';
                }

                $desc = $user_avatar ? $user_avatar . $user_name : '已绑定“' . $user_name . '”';
                $desc = '<div class="muted-2-color text-ellipsis mr10 ml20"  data-toggle="tooltip"  title="已绑定' . $name . '账号">' . $desc . '</div>';
                $btn  = '<a data-toggle="tooltip" href="javascript:;" _wpnonce="' . wp_create_nonce('user_oauth_untying') . '" title="解绑' . $name . '账号" user-id="' . $user_id . '" untying-type="' . $type . '" class="em09 p2-10 oauth-untying but hollow c-yellow">解绑</a>';

            } else {
                //还未绑定
                if ('alipay' == $type) {
                    if (wp_is_mobile() && !strpos($_SERVER['HTTP_USER_AGENT'], 'Alipay')) {
                        continue;
                    }
                    //移动端并且不是支付宝APP不显示支付宝
                }

                $class = '';
                if (!empty($arg['qrcode'])) {
                    $class .= ' qrcode-signin';
                }

                $desc = '<div class="muted-2-color">暂未绑定</div>';
                $btn  = '<a title="绑定' . $name . '账号" href="' . esc_url(add_query_arg(array('bind' => $type), $bind_href)) . '" class="em09 p2-10 but hollow ' . $class . ' c-blue">绑定</a>';
            }

            $con .= '<div class="mb10"><div class="flex ac jsb muted-box">
                        <div class="flex ac type-logo"><span class="social-login-item circular mr6 em14 ' . $type . '">' . $icon . '</span><span class="">' . $name . '</span></div>
                        <div class="overflow-hidden">' . $desc . '</div>
                        <div class="shrink0">' . $btn . '</div>
                    </div></div>';

        }
    }

    if (!$con) {
        return $html;
    }

    $html .= '<div class="box-body oauth-set">';
    $html .= '<div class="title-h-left"><b>绑定社交账号</b></div><div class="muted-2-color mb20">绑定社交账号，您可更安全、更快速的登录本站</div>';
    $html .= '<div class="flex hh oauth-bind-box gutters-5">' . $con . '</div>';
    $html .= '</div>';
    return $html;
}
add_filter('user_center_account_setup', 'zib_oauth_set', 10, 2);

function zib_passwordold_set($html, $user_id)
{
    $oauth_new = get_user_meta($user_id, 'oauth_new', true);

    $subtitle = $oauth_new ? '您还未设置过密码，请在此设置新密码' : '定期修改密码有助于账户安全';
    $btn      = zib_get_user_set_link('but c-blue-2 hollow', 'change_password', ($oauth_new ? '设置新密码' : '修改密码'));

    $con = '<div class="oauth-bind-box"><div class=""><div class="flex ac jsb muted-box">
                <div class="flex ac type-logo"><span class="b-purple circular mr6 em14"><i class="fa fa-unlock-alt"></i></span><span class="">账户密码</span></div>
                <div class="">' . $btn . '</div>
                </div></div></div>';

    $con   = '<div>' . $con . '</div>';
    $title = '<div class="title-h-left"><b>账户密码</b></div>';
    $title .= '<div class="muted-2-color mb20">' . $subtitle . '</div>';

    return $html . '<div class="box-body">' . $title . $con . '</div>';
}
add_filter('user_center_account_setup', 'zib_passwordold_set', 10, 2);

//绑定用户微信的模态框
function zib_user_center_oauth_qrcode_modal()
{
    if (zib_is_oauth_qrcode_s() && zib_weixingzh_is_qrcode()) {
        add_action('wp_footer', 'zib_oauth_qrcode_modal', 11, 2);
    }
}
add_action('user_center_page_content', 'zib_user_center_oauth_qrcode_modal');

function zib_oauth_qrcode_modal($html)
{
    $tab_html = '<div class="tab-pane fade active in" id="tab-qrcode-signin">';
    $tab_html .= '<div class="box-body">';
    $tab_html .= '<div class="title-h-left em12">绑定微信账号</div>';
    $tab_html .= '<a class="muted-color px12 hide" href="#tab-qrcode-signin" data-toggle="tab">扫码登录</a>';
    $tab_html .= '</div>';
    $tab_html .= '<div class="qrcode-signin-container box-body text-center">';
    $tab_html .= '<p class="placeholder" style="height:180px;width:180px;margin:auto;"></p><p class="placeholder" style="height:27px;width:200px;margin:15px auto 0;"></p>';
    $tab_html .= '</div>';
    $agreement = zib_get_user_agreement('扫码绑定即表示同意');
    if ($agreement) {
        $tab_html .= '<div class="muted-color mt10 text-center px12 opacity8">' . $agreement . '</div>';
    }
    $tab_html .= '</div>';

    $html .= '<div class="modal fade" id="u_sign" tabindex="-1" role="dialog">';
    $html .= '<div class="modal-dialog" role="document">';
    $html .= '<div class="sign-content">';
    $html .= '<div class="sign zib-widget blur-bg relative">';
    $html .= '<button class="close" data-dismiss="modal">' . zib_get_svg('close', '0 0 1024 1024', 'ic-close') . '</button>';
    $html .= $tab_html;
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    echo $html;
    //return $html;
}

//用户中心-收款码设置 rewards
function zib_main_user_tab_content_rewards()
{
    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;
    if (!$user_id) {
        return;
    }

    $post_rewards_s = _pz('post_rewards_s');
    $rewards_title  = zib_get_user_meta($user_id, 'rewards_title', true);

    $form = '';
    if ($post_rewards_s) {
        $form .= '<div class="mb40">';
        $form .= '<div class="title-h-left"><b>设置打赏标题</b></div>';
        $form .= '<div class="line-form">';
        $form .= '<input type="input" class="line-form-input" name="rewards_title" value="' . esc_attr($rewards_title) . '" placeholder="文章很赞！支持一下吧">';
        $form .= '<i class="line-form-line"></i>';
        $form .= '</div>';
        $form .= '</div>';
    }

    $form .= '<div class="mb40">';
    $form .= '<div class="title-h-left"><b>设置收款码</b></div>';
    $form .= '<p class="muted-2-color">选择您的收款码上传，支持jpg、gif、png格式，最大' . zib_get_current_user_can_number('upload_img_size', 3) . 'M</p>';
    $form .= zib_get_user_collection_upload_centent();
    $form .= '</div>';

    $form .= '<div class="mt10 text-center">';
    $form .= '<button type="button" action="info.upload" zibupload="submit" zibupload-nomust="true" class="but jb-blue padding-lg" name="submit"><i class="fa fa-check mr10"></i>确认修改</button>';
    $form .= '</div>';

    $html = '<form class="set-rewards-form mini-upload zib-widget"><div class="padding-h10" style="max-width: 502px;margin: auto;">' . $form . '</div></form>';
    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_rewards', 'zib_main_user_tab_content_rewards');

/**
 * @description: 收款码修改的内容
 * @param {*}
 * @return {*}
 */
function zib_get_user_collection_upload_centent()
{
    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;
    if (!$user_id) {
        return;
    }

    $rewards_img_urls = zib_get_user_rewards_img_urls($user_id);
    $weixin           = $rewards_img_urls['weixin'];
    $alipay           = $rewards_img_urls['alipay'];
    $add              = '<img style="width: 100%;" src="' . ZIB_TEMPLATE_DIRECTORY_URI . '/img/upload-add.svg" alt="点击上传收款码">';

    $weixin_img = $weixin ? '<img class="fit-cover" src="' . esc_attr($weixin) . '" alt="微信收款码">' : $add;
    $alipay_img = $alipay ? '<img class="fit-cover" src="' . esc_attr($alipay) . '" alt="支付宝收款码">' : $add;

    $html = '<div class="flex ac">';
    $html .= '<label style="width: 100%;" class="pointer text-center">
                <div class="preview weixin upload-preview radius4" style="width: 140px;height: 140px;">' . $weixin_img . '</div>
                <div class="em09 c-blue">上传微信二维码</div>
                <input class="hide" type="file" zibupload="image_upload" data-preview=".preview.weixin" accept="image/gif,image/jpeg,image/jpg,image/png" data-tag="weixin" name="image_upload" action="image_upload">
            </label>';
    $html .= '<label style="width: 100%;" class="pointer text-center">
                <div class="preview alipay upload-preview radius4" style="width: 140px;height: 140px;">' . $alipay_img . '</div>
                <div class="em09 c-blue">上传支付宝二维码</div>
                <input class="hide" type="file" zibupload="image_upload" data-preview=".preview.alipay" accept="image/gif,image/jpeg,image/jpg,image/png" data-tag="alipay" name="image_upload" action="image_upload">
            </label>';

    $html .= '</div>';
    $html .= '<input type="hidden" name="user_id" value="' . $user_id . '">';
    $html .= '<input type="hidden" name="action" value="user_set_rewards">';
    $html .= wp_nonce_field('upload_rewards', 'upload_rewards_nonce', false, false);

    return $html;
}

//获取修改收款码的模态框内容
function zib_get_user_collection_modal()
{

    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;
    if (!$user_id) {
        return;
    }

    $form = '<div class="mb20">';
    $form .= '<div class="muted-2-color">选择您的收款码上传，支持jpg、gif、png格式，最大' . zib_get_current_user_can_number('upload_img_size', 3) . 'M</div>';
    $form .= zib_get_user_collection_upload_centent();
    $form .= '</div>';
    $form .= '<div class="modal-buts but-average">';
    $form .= '<a type="button" data-dismiss="modal" class="but" href="javascript:;">取消</a><button type="button" action="info.upload" zibupload="submit" class="but c-blue padding-lg" name="submit"><i class="fa fa-check mr10"></i>确认修改</button>';
    $form .= '</div>';

    $html   = '<form class="set-rewards-form mini-upload">' . $form . '</form>';
    $header = zib_get_modal_colorful_header('jb-blue', '<i class="fa fa-qrcode"></i>', '设置收款码');

    return $header . $html;
}

//为用户中心页面添加head
function zib_user_center_seo()
{
    global $new_title, $new_description;
    $user    = wp_get_current_user();
    $user_id = isset($user->ID) ? (int) $user->ID : 0;

    if ($user_id) {
        $new = $user->display_name . '的用户中心';
    } else {
        $new = '用户中心';
    }
    $new .= zib_get_delimiter_blog_name();
    $new_title = $new_description = $new;
    add_filter('echo_seo_title', '__return_true');
}
add_action('locate_template_user_center', 'zib_user_center_seo');

//为用户中心页面添加小工具
add_action('user_center_page_content', function () {
    echo '<div class="fluid-widget">';
    dynamic_sidebar('all_top_fluid');
    dynamic_sidebar('user_top_fluid');
    echo '</div>';
}, 9);
add_action('user_center_page_footer', function () {
    echo '<div class="container fluid-widget">';
    dynamic_sidebar('user_bottom_fluid');
    dynamic_sidebar('all_bottom_fluid');
    echo '</div>';
});
