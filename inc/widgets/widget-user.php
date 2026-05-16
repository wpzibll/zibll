<?php

add_action('widgets_init', 'widget_register_user');
function widget_register_user()
{
    register_widget('widget_ui_user_lists');
}

/////用户列表-----
class widget_ui_user_lists extends WP_Widget
{
    public function __construct()
    {
        $widget = array(
            'w_id'        => 'widget_ui_user_lists',
            'w_name'      => _name('用户列表'),
            'classname'   => '',
            'description' => '显示网站注册用户列表，多种排序方式。',
        );
        parent::__construct($widget['w_id'], $widget['w_name'], $widget);
    }
    public function widget($args, $instance)
    {
        if (!zib_widget_is_show($instance)) {
            return;
        }

        extract($args);

        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'include'      => '',
            'exclude'      => '',
            'hide_box'     => false,
            'number'       => 8,
            'orderby'      => 'user_registered',
            'order'        => 'DESC',
        );
        $instance   = wp_parse_args((array) $instance, $defaults);
        $mini_title = $instance['mini_title'];
        if ($mini_title) {
            $mini_title = '<small class="ml10">' . $mini_title . '</small>';
        }
        $title    = $instance['title'];
        $more_but = '';
        if ($instance['more_but'] && $instance['more_but_url']) {
            $more_but = '<div class="pull-right em09 mt3"><a href="' . $instance['more_but_url'] . '" class="muted-2-color">' . $instance['more_but'] . '</a></div>';
        }
        $mini_title .= $more_but;

        if ($title) {
            $title = '<div class="box-body notop"><div class="title-theme">' . $title . $mini_title . '</div></div>';
        }

        $in_affix = $instance['in_affix'] ? ' data-affix="true"' : '';

        $class = !$instance['hide_box'] ? ' zib-widget' : '';
        echo '<div' . $in_affix . ' class="theme-box">';
        echo $title;
        echo '<div class="text-center user_lists' . $class . '">';

        $users_args = array(
            'order'   => $instance['order'],
            'orderby' => $instance['orderby'],
            'number'  => $instance['number'],
            'orderby' => 'views',
        );

        if ($instance['include']) {
            $users_args['include'] = preg_split("/,|，|\s|\n/", $instance['include']);
        }
        if ($instance['exclude']) {
            $users_args['exclude'] = preg_split("/,|，|\s|\n/", $instance['exclude']);
        }
        if ('display_name' == $instance['orderby'] || 'post_count' == $instance['orderby'] || 'user_registered' == $instance['orderby']) {
            $users_args['orderby'] = $instance['orderby'];
        } else {
            $users_args['orderby']  = 'meta_value';
            $users_args['meta_key'] = $instance['orderby'];
        }

        echo zib_get_user_card_lists($users_args) ?: '未找到用户';
        echo '</div>';
        echo '</div>';
    }

    public function form($instance)
    {
        $defaults = array(
            'title'        => '',
            'mini_title'   => '',
            'more_but'     => '<i class="fa fa-angle-right fa-fw"></i>更多',
            'more_but_url' => '',
            'in_affix'     => '',
            'include'      => '',
            'exclude'      => '',
            'number'       => 8,
            'hide_box'     => '',
            'orderby'      => 'user_registered',
            'order'        => 'DESC',
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        $page_input[] = array(
            'name'  => __('标题：', 'zibll'),
            'id'    => $this->get_field_name('title'),
            'std'   => $instance['title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('副标题：', 'zibll'),
            'id'    => $this->get_field_name('mini_title'),
            'std'   => $instance['mini_title'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->文案：', 'zibll'),
            'id'    => $this->get_field_name('more_but'),
            'std'   => $instance['more_but'],
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            'name'  => __('标题右侧按钮->链接：', 'zibll'),
            'id'    => $this->get_field_name('more_but_url'),
            'std'   => $instance['more_but_url'],
            'desc'  => '设置为任意链接',
            'style' => 'margin: 10px auto;',
            'type'  => 'text',
        );
        $page_input[] = array(
            //    'name'  => __('显示背景盒子', 'zibll'),
            'id'    => $this->get_field_name('hide_box'),
            'std'   => $instance['hide_box'],
            'desc'  => '不显示背景盒子',
            'style' => 'margin: 10px auto;',
            'type'  => 'checkbox',
        );

        echo zib_get_widget_show_type_input($instance, $this->get_field_name('show_type'));

        echo zib_edit_input_construct($page_input);
        ?>

		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked($instance['in_affix'], 'on');?> id="<?php echo $this->get_field_id('in_affix'); ?>" name="<?php echo $this->get_field_name('in_affix'); ?>"> 侧栏随动（仅在侧边栏有效）
			</label>
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('number');
        ?>" name="<?php echo $this->get_field_name('number');
        ?>" type="number" value="<?php echo $instance['number'];
        ?>" size="24" />
			</label>
		</p>
		<p>
			<?php zib_user_help('包含的用户ID：');?>
			<label>
				<input style="width:100%;" id="<?php echo $this->get_field_id('include');
        ?>" name="<?php echo $this->get_field_name('include');
        ?>" type="text" value="<?php echo $instance['include'];
        ?>" />
			</label>
		</p>
		<p>
			<?php zib_user_help('排除的用户ID：');?>
			<label>
				<input style="width:100%;" id="<?php echo $this->get_field_id('exclude');
        ?>" name="<?php echo $this->get_field_name('exclude');
        ?>" type="text" value="<?php echo $instance['exclude'];
        ?>" />
			</label>
		</p>

		<p>
			<label>
				排序方式：
				<select style="width:100%;" id="<?php echo $this->get_field_id('orderby');
        ?>" name="<?php echo $this->get_field_name('orderby');
        ?>">
					<option value="display_name" <?php selected('display_name', $instance['orderby']);
        ?>>呢称</option>
					<option value="user_registered" <?php selected('user_registered', $instance['orderby']);
        ?>>注册时间</option>
					<option value="post_count" <?php selected('post_count', $instance['orderby']);
        ?>>文章数量</option>
					<option value="last_login" <?php selected('last_login', $instance['orderby']);
        ?>>最后登录时间</option>
					<option value="followed-user-count" <?php selected('followed-user-count', $instance['orderby']);
        ?>>粉丝数</option>
				</select>
			</label>
		</p>
		<p>
			<label>
				排序顺序：
				<select style="width:100%;" id="<?php echo $this->get_field_id('order');
        ?>" name="<?php echo $this->get_field_name('order');
        ?>">
					<option value="ASC" <?php selected('ASC', $instance['order']);
        ?>>升序</option>
					<option value="DESC" <?php selected('DESC', $instance['order']);
        ?>>降序</option>
				</select>
			</label>
		</p>

	<?php
}
}

/////用户信息---//用户信息---//用户信息---//用户信息---//用户信息------
Zib_CFSwidget::create('widget_ui_user', array(
    'title'       => '用户个人信息',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'size'        => 'mini',
    'description' => '未登录时候显示登录注册按钮，登录后显示登录用户的个人信息',
    'fields'      => array(
        array(
            'title'   => '显示封面',
            'id'      => 'show_img_bg',
            'type'    => 'switcher',
            'default' => true,
        ),
        array(
            'title'   => '未登录的文案',
            'id'      => 'loged_title',
            'type'    => 'text',
            'default' => 'HI！请登录',
        ),
        array(
            'title'   => '显示按钮',
            'desc'    => '登录后才会显示',
            'id'      => 'show_button',
            'type'    => 'switcher',
            'default' => true,
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'id'         => 'button_1',
            'default'    => 'post',
            'title'      => '按钮1：',
            'subtitle'   => '按钮类型',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => 'zib_new_add_btns_options',
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'subtitle'   => '按钮颜色',
            'id'         => 'button_1_class',
            'class'      => 'compact skin-color',
            'default'    => "jb-pink",
            'type'       => "palette",
            'options'    => CFS_Module::zib_palette(),
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'class'      => 'compact mini-input',
            'subtitle'   => '按钮文字',
            'id'         => 'button_1_text',
            'type'       => 'text',
            'default'    => '发布文章',
        ),

        array(
            'dependency' => array('show_button', '!=', ''),
            'id'         => 'button_2',
            'default'    => 'center',
            'title'      => '按钮2：',
            'subtitle'   => '按钮类型',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => array(
                'home'   => '个人主页',
                'center' => '用户中心',
            ),
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'subtitle'   => '按钮颜色',
            'id'         => 'button_2_class',
            'class'      => 'compact skin-color',
            'default'    => "jb-blue",
            'type'       => "palette",
            'options'    => CFS_Module::zib_palette(),
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'class'      => 'compact mini-input',
            'subtitle'   => '按钮文字',
            'id'         => 'button_2_text',
            'type'       => 'text',
            'default'    => '用户中心',
        ),
    ),
));

function widget_ui_user($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class || zib_is_close_sign()) {
        return;
    }

    $instance['user_id']      = get_current_user_id();
    $instance['show_posts']   = false;
    $instance['class']        = 'widget';
    $instance['show_checkin'] = true;

    if (!isset($instance['show_img_bg'])) {
        $instance['show_img_bg'] = true;
    }

    Zib_CFSwidget::echo_before($instance, 'mb20');
    if ($instance['user_id']) {
        //已经登录
        echo zib_get_user_card_box($instance);
    } else {
        //未登录
        $loged_title = !empty($instance['loged_title']) ? $instance['loged_title'] : 'Hi！请登录';
        $lazy_attr   = zib_is_lazy('lazy_other', true) ? 'class="lazyload fit-cover" src="' . zib_get_lazy_thumb() . '" data-' : 'class="fit-cover"';
        $cover       = $instance['show_img_bg'] ? '<div class="user-cover graphic" style="padding-bottom: 50%;"><img ' . $lazy_attr . 'src="' . _pz('user_cover_img', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user_t.jpg') . '"></div>' : '';
        $avatar      = '<span class="avatar-img avatar-lg"><img alt="默认头像" class="fit-cover avatar" src="' . zib_default_avatar() . '"></span>';
        $html        = '<div class="user-card zib-widget widget">' . $cover . '
        <div class="card-content mt10">
            <div class="user-content">
                <div class="user-avatar">' . $avatar . '</div>
                <div class="user-info mt10">
                    ' . zib_get_user_singin_page_box('', $loged_title) . '
                </div>
            </div>
        </div>
    </div>';

        echo $html;
    }
    Zib_CFSwidget::echo_after($instance);
}

/////文章作者信息----///文章作者信息----///文章作者信息----///文章作者信息----///文章作者信息------
Zib_CFSwidget::create('widget_ui_avatar', array(
    'title'       => '文章作者信息',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'size'        => 'mini',
    'description' => '显示当前文章作者的个人信息，只会在文章页、帖子页显示',
    'fields'      => array(
        array(
            'title'   => '显示封面',
            'id'      => 'show_img_bg',
            'type'    => 'switcher',
            'default' => true,
        ),
        array(
            'title'   => '显示文章',
            'id'      => 'show_posts',
            'type'    => 'switcher',
            'default' => true,
        ),
        array(
            'dependency' => array('show_posts', '!=', ''),
            'id'         => 'post_type',
            'default'    => 'post',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => '文章类型',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => array(
                'post'       => '文章',
                'forum_post' => '论坛帖子',
            ),
        ),
        array(
            'dependency' => array('show_posts', '!=', ''),
            'id'         => 'post_style',
            'default'    => 'post',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => '显示样式',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => array(
                'mini' => '简约风格',
                'card' => '图文卡片',
            ),
        ),
        array(
            'dependency' => array('show_posts', '!=', ''),
            'id'         => 'limit',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => '显示数量',
            'default'    => 6,
            'max'        => 12,
            'min'        => 2,
            'step'       => 1,
            'unit'       => '篇',
            'type'       => 'spinner',
        ),
        array(
            'dependency' => array('show_posts', '!=', ''),
            'id'         => 'orderby',
            'default'    => 'date',
            'class'      => 'compact',
            'title'      => ' ',
            'subtitle'   => '排序方式',
            'inline'     => true,
            'type'       => 'select',
            'options'    => array(
                'modified'       => '最近更新',
                'date'           => '最新发布',
                'views'          => '最多浏览',
                'like'           => '最多点赞[文章]',
                'comment_count'  => '最多评论',
                'favorite'       => '最多收藏[文章]',
                'favorite_count' => '最多收藏[帖子]',
                'score'          => '评分最高[帖子]',
                'rand'           => '随机',
            ),
        ),
        array(
            'title'   => '显示按钮',
            'desc'    => '当登录用户就是文章作者的时候才会显示',
            'id'      => 'show_button',
            'type'    => 'switcher',
            'default' => true,
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'id'         => 'button_1',
            'default'    => 'post',
            'title'      => '按钮1：',
            'subtitle'   => '按钮类型',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => 'zib_new_add_btns_options',
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'subtitle'   => '按钮颜色',
            'id'         => 'button_1_class',
            'class'      => 'compact skin-color',
            'default'    => "jb-pink",
            'type'       => "palette",
            'options'    => CFS_Module::zib_palette(),
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'class'      => 'compact mini-input',
            'subtitle'   => '按钮文字',
            'id'         => 'button_1_text',
            'type'       => 'text',
            'default'    => '发布文章',
        ),

        array(
            'dependency' => array('show_button', '!=', ''),
            'id'         => 'button_2',
            'default'    => 'center',
            'title'      => '按钮2：',
            'subtitle'   => '按钮类型',
            'inline'     => true,
            'type'       => 'radio',
            'options'    => array(
                'home'   => '个人主页',
                'center' => '用户中心',
            ),
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'subtitle'   => '按钮颜色',
            'id'         => 'button_2_class',
            'class'      => 'compact skin-color',
            'default'    => "jb-blue",
            'type'       => "palette",
            'options'    => CFS_Module::zib_palette(),
        ),
        array(
            'dependency' => array('show_button', '!=', ''),
            'title'      => ' ',
            'class'      => 'compact mini-input',
            'subtitle'   => '按钮文字',
            'id'         => 'button_2_text',
            'type'       => 'text',
            'default'    => '用户中心',
        ),
    ),
));

function widget_ui_avatar($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    global $post;
    if (!isset($post->post_author)) {
        return;
    }
    $instance['user_id'] = $post->post_author;
    $instance['class']   = 'widget';
    if (!isset($instance['show_img_bg'])) {
        $instance['show_img_bg'] = true;
    }
    if (isset($instance['show_img'])) {
        $instance['post_style'] = $instance['show_img'] ? 'card' : 'mini';
    }

    Zib_CFSwidget::echo_before($instance, 'mb20');
    echo zib_get_user_card_box($instance);
    Zib_CFSwidget::echo_after($instance);
}

//图标卡片
Zib_CFSwidget::create('zib_widget_ui_user_ranking', array(
    'title'       => '用户排行榜',
    'zib_title'   => true,
    'zib_affix'   => true,
    'zib_show'    => true,
    'description' => '根据不同项目对对用户进行排名，生成排行榜',
    'fields'      => array(
        array(
            'id'      => 'orderby',
            'default' => 'checkin_all_day',
            'title'   => '榜单类型',
            'type'    => "radio",
            'options' => array(
                'checkin_all_day'        => '累计签到天数',
                'checkin_continuous_day' => '连续签到天数',
                'points'                 => '用户积分',
                'followed-user-count'    => '粉丝数量',
            ),
        ),
        array(
            'id'      => 'number',
            'title'   => '最大显示数量',
            'default' => 6,
            'max'     => 20,
            'min'     => 1,
            'step'    => 1,
            'unit'    => '个',
            'type'    => 'spinner',
        ),
        array(
            'id'      => 'desc',
            'default' => 'desc',
            'title'   => '附加显示',
            'type'    => "radio",
            'options' => array(
                'desc'            => '用户签名',
                'user_registered' => '加入本站多少天',
                'last_login'      => '最后登录时间',
                'last_checkin'    => '最后签到时间',
            ), 
        ),
        array(
            'label'   => '显示排行徽章',
            'id'      => 'top_badge',
            'type'    => 'switcher',
            'default' => false,
        ),
        array(
            'label'   => '显示签到按钮',
            'id'      => 'checkin_btn',
            'type'    => 'switcher',
            'default' => false,
        ),
    ),
));

function zib_widget_ui_user_ranking($args, $instance)
{
    $show_class = Zib_CFSwidget::show_class($instance);
    if (!$show_class) {
        return;
    }

    $html = zib_user_ranking_lists($instance);
    Zib_CFSwidget::echo_before($instance, 'zib-widget user-ranking-box', $args);
    if (!empty($instance['checkin_btn'])) {
        $class = _pz('checkin_header_user_option', 'c-yellow', 'class');
        $text  = _pz('checkin_header_user_option', '签到领取今日奖励', 'text');

        $checkin_btn = zib_get_user_checkin_btn('but block mb20 padding-lg ' . $class, '<i class="fa fa-calendar-check-o"></i> ' . $text, '<i class="fa fa-calendar-check-o"></i> 今日已签到');
        echo $checkin_btn;
    }
    echo $html;
    Zib_CFSwidget::echo_after($instance, $args);
}

//用户列表小工具
function zib_user_ranking_lists($args)
{
    $defaults = array(
        'orderby'   => 'checkin_all_day',
        'number'    => 6,
        'exclude'   => '',
        'desc'      => 'desc',
        'top_badge' => false,
    );

    $args = wp_parse_args($args, $defaults);

    $users_args = array(
        'exclude'    => $args['exclude'],
        'order'      => 'DESC',
        'number'     => $args['number'],
        'orderby'    => 'meta_value_num',
        'meta_key'   => $args['orderby'],
        'meta_query' => array(
            'relation' => 'OR', //排除禁封用户
            array(
                'key'     => 'banned',
                'value'   => array(1, 2),
                'compare' => 'NOT IN',
            ),
            array(
                'key'     => 'banned',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );

    $users = get_users($users_args);

    $lists   = '';
    $posts_i = 1;
    if ($users) {
        foreach ($users as $user) {
            $user_id           = $user->ID;
            $desc              = get_user_desc($user_id);
            $avatar_box        = zib_get_avatar_box($user_id);
            $display_name_link = zib_get_user_name("id=$user_id");
            $top_bagd_class    = array('', 'jb-red', 'jb-yellow');
            $top_bagd          = $args['top_badge'] ? '<badge class="img-badge left hot ' . (isset($top_bagd_class[$posts_i - 1]) ? $top_bagd_class[$posts_i - 1] : 'b-gray') . '"><i>TOP' . $posts_i . '</i></badge>' : '';

            switch ($args['desc']) {
                case 'desc':
                    $desc = get_user_desc($user_id);
                    break;
                case 'user_registered':
                    $desc = zib_get_user_join_day_desc($user_id, '');
                    break;
                case 'last_login':

                    $last_login = get_user_meta($user->ID, 'last_login', true);
                    $desc       = zib_get_time_ago($last_login) . '登录';
                    break;
                case 'last_checkin':
                    $last_checkin_time = zib_get_user_last_checkin_time($user_id, true);
                    $desc              = $last_checkin_time ? $last_checkin_time . '已签到' : '未签到';
                    break;

            }

            switch ($args['orderby']) {
                case 'checkin_all_day':
                    $num         = zib_get_user_checkin_all_day($user_id);
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="累计签到' . $num . '天"><i class="fa fa-calendar-check-o"></i><span class="ml6">' . $num . '</span></span>';
                    break;
                case 'checkin_continuous_day':
                    $num         = zib_get_user_checkin_continuous_day($user_id);
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="连续签到' . $num . '天"><i class="fa fa-calendar-check-o"></i><span class="ml6">' . $num . '</span></span>';
                    break;
                case 'points':
                    $num         = _cut_count(zibpay_get_user_points($user_id));
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="积分：' . $num . '">' . zib_get_svg('points') . '<span class="ml6">' . $num . '</span></span>';
                    break;
                case 'followed-user-count':
                    $num         = get_user_followed_count($user_id);
                    $right_badge = '<span class="badg" data-toggle="tooltip" title="共' . $num . '个粉丝"><i class="fa fa-heart"></i><span class="ml6">' . $num . '</span></span>';

                    break;
            }

            $lists .= '<div class="user-ranking-item relative' . ($posts_i > 1 ? ' mt20' : '') . '">
                ' . $top_bagd . '
                <div class="user-info flex ac">
                    ' . $avatar_box . '
                    <div class="user-right flex flex1 ac jsb ml10">
                        <div class="flex1">
                            <div class="font-bold">' . $display_name_link . '</div>
                            <div class="mt3 em09 muted-color text-ellipsis">' . $desc . '</div>
                        </div>
                        <div class="ml20 flex0">' . $right_badge . '</div>
                    </div>
                </div></div>';

            $posts_i++;

        }
    }

    return $lists;
}