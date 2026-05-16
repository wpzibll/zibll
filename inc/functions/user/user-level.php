<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-09-22 10:30:38
 * @LastEditTime : 2025-05-07 14:21:16
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户等级level相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_main_user_tab_content_level()
{
    $user_id = get_current_user_id();

    $my_level_card = zib_get_user_level_card($user_id);
    $growth_card   = zib_get_user_integral_growth_card($user_id);
    $html          = '';
    $html .= '<div class="row gutters-10">';
    $html .= '<div class="col-sm-5">' . $my_level_card . '</div>';
    $html .= '<div class="col-sm-7">' . $growth_card . '</div>';
    $html .= '</div>';
    $html .= '<div class="zib-widget"><div class="box-body">
    <b><a class="but mr6" data-toggle="tab" href="#tab_integral_get">获取经验值</a><a class="but" data-toggle="tab" href="#tab_integral_my">我的经验值</a></b>
    <div class="tab-content mt20">
        <div class="tab-pane fade active in" id="tab_integral_get">' . zib_get_integral_add_lists() . '</div>
        <div class="tab-pane fade" id="tab_integral_my">' . zib_get_user_integral_detail_lists($user_id) . '</div>
        <div class="tab-pane fade" id="tab_integral_date">' . zib_get_user_integral_date_detail_lists($user_id) . '</div>
    </div>
    </div></div>';

    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_level', 'zib_main_user_tab_content_level');

//获取我的经验值获取记录明细
function zib_get_user_integral_detail_lists($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $detail                = (array) zib_get_user_meta($user_id, 'level_integral_detail', true);
    $lists                 = '';
    $options               = zib_get_user_integral_add_options();
    $options['invit_code'] = array('邀请码注册奖励', 0, '', '用户');
    $options['checkin']    = array('签到奖励', 0, '', '用户');
    $to_day                = zib_get_user_today_integral($user_id);

    if ($to_day) {
        $day_max  = _pz('user_integral_opt', 100, 'day_max');
        $max_desc = $day_max > $to_day ? '单日获得超过' . $day_max . '后，将不再累计经验值' : '今日累计获得已超过' . $day_max . '，将不再累计经验值';
        $lists .= '<div class="border-bottom padding-h10"><div class="flex jsb ac"><div class="flex1 mr20"><div class="font-bold mb6">今日累计 <span class="focus-color">+' . $to_day . '</span></div><div class="muted-2-color em09">' . $max_desc . '</div></div><a class="muted-2-color shrink0" data-toggle="tab" href="#tab_integral_date">每日详情<i class="fa fa-angle-right ml6 em12"></i></a></div></div>';
    }

    foreach ($detail as $k => $v) {
        if (isset($v['value']) && isset($v['integral'])) {
            $lists .= '<div class="border-bottom padding-h10"><div class="flex jsb ac">
            <div class="flex1 mr20"><div class="font-bold mb6">' . $options[$v['key']][0] . '</div><div class="muted-2-color em09">' . $v['time'] . '</div></div>
            <div class="text-right shrink0"><div class="focus-color em14 mb6">+ ' . $v['value'] . '</div><div class="muted-2-color em09">累计：' . $v['integral'] . '</div></div>
            </div></div>';
        }
    }

    if (!$lists) {
        $lists = zib_get_null('暂无经验值获取明细');
    } else {
        $lists .= '<div class="text-center mt20 muted-3-color">最多显示近50条记录</div>';
    }
    return $lists;
}

//获取我的经验值获取每日记录明细
function zib_get_user_integral_date_detail_lists($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $detail = (array) zib_get_user_meta($user_id, 'level_integral_date_detail', true);
    $lists  = '';
    foreach ($detail as $k => $v) {
        $lists .= '<div class="border-bottom padding-h6 flex jsb ac"><div class="em12">' . $k . '</div><div class="text-right shrink0 focus-color em14 font-bold">+ ' . $v . '</div></div>';
    }
    if (!$lists) {
        $lists = zib_get_null('暂无经验值获取明细');
    } else {
        $lists .= '<div class="text-center mt20 muted-3-color">最多显示近30条记录</div>';
    }
    return $lists;
}

//获取获得经验值的方法明细
function zib_get_integral_add_lists()
{
    $opt   = _pz('user_integral_opt');
    $lists = '';
    foreach (zib_get_user_integral_add_options() as $k => $v) {
        if ((int) $opt[$k] > 0 && 'sign_up' !== $k) {
            $lists .= '<div class="border-bottom padding-h10"><div class="flex jsb ac"><div class="flex1 mr20"><div class="font-bold mb6">' . $v[0] . '</div><div class="muted-2-color em09">' . $v[2] . '</div></div><span class="focus-color em14 shrink0"> ' . zib_get_svg('trend-color', null, 'icon mr6 em09') . ' + ' . (int) $opt[$k] . '</span></div></div>';
        }
    }
    return $lists;
}

//获取我的等级卡片
function zib_get_user_level_card($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return;
    }

    $level         = zib_get_user_level($user_id);
    $integral      = zib_get_user_integral($user_id);
    $next_integral = (int) _pz('user_level_opt', 0, 'upgrade_integral_' . ($level + 1));
    //异常判断
    if ($next_integral <= $integral) {
        zib_update_user_level('', $user_id, 'level_integral', $integral);
        $level         = zib_get_user_level($user_id);
        $integral      = zib_get_user_integral($user_id);
        $next_integral = (int) _pz('user_level_opt', 0, 'upgrade_integral_' . ($level + 1));
    }

    $max        = _pz('user_level_max', 10);
    $avatar_img = zib_get_data_avatar($user_id);
    $badge      = zib_get_level_badge($level, 'em12 mr10', false);
    $title      = esc_attr(_pz('user_level_opt', 'LV' . $level, 'name_' . $level));

    if ($level >= $max) {
        $integral_html = '<div class="flex jsb mb6 ab"><div class="em12">' . $integral . '/' . $integral . '</div><div class="opacity8">已为最高等级</div></div>';
        $proportion    = 100;
    } else {
        $integral_html = '<div class="flex jsb mb6 ab">
                            <div class="em12 font-bold">' . $integral . '/' . $next_integral . '</div>
                            <div class="opacity8 em09">升级还需<span class="em14 font-bold ml3 mr3">' . ($next_integral - $integral) . '</span>经验</div>
                        </div>';
        $proportion = (int) (($integral / $next_integral) * 100);
    }
    $progress = '<div class="integral-progress progress">
                    <div class="progress-bar" style="width: ' . $proportion . '%;"></div>
                </div>';

    $html = '<div class="colorful-bg jb-vip1 zib-widget"><div class="colorful-make"></div>';
    $html .= '<div class="relative flex xx jsb" style="height: 146px;">';
    $html .= '<div class="flex ac"><div class="avatar-mini mr6" style="margin-top: -4px;">' . $avatar_img . '</div><div class="font-bold">我的等级</div></div>';
    $html .= '<div class="flex ac em14">' . $badge . '<span class="">' . $title . '</span></div>';
    $html .= '<div class="">' . $integral_html . $progress . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

//获取经验值成长体系卡片
function zib_get_user_integral_growth_card($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    $my_level      = zib_get_user_level($user_id);
    $my_integral   = zib_get_user_integral($user_id);
    $max           = _pz('user_level_max', 10);
    $opts          = _pz('user_level_opt', array());
    $lists         = '';
    $to_day        = zib_get_user_today_integral($user_id);
    $date_btn_text = $to_day ? '今日 +' . $to_day : '查看详情';

    for ($i = 1; $i <= $max; $i++) {
        $badge    = zib_get_level_badge($i, 'em12');
        $integral = 1 === $i ? 0 : $opts['upgrade_integral_' . $i];
        $height   = 1 === $i ? 0 : (int) ($i / $max * 100);
        $lists .= '<div class="swiper-slide pillar-item' . ($i == $my_level ? ' active' : '') . '"><div class="value">' . $integral . '</div><div class="pillar" style="height:' . 86 * $height / 100 . 'px;"></div> <div class="level-name">' . $badge . '</div></div>';
    }

    $html = '<div class="colorful-bg c-gray zib-widget"><div class="colorful-make"></div>';
    $html .= '<div class="relative flex xx jsb" style="height: 146px;">';
    $html .= '<div class="flex ac jsb"><div><span class="mr6 font-bold">' . zib_get_svg('trend-color', null, 'icon mr6 em12') . '成长体系</span><a class="muted-2-color px12 opacity8" data-toggle="tab" href="#tab_integral_my">我的经验值:' . $my_integral . '</a></div><a class="muted-2-color em09 opacity8" data-toggle="tab" href="#tab_integral_date">' . $date_btn_text . '<i class="fa fa-angle-right ml6"></i></a></div>';
    $html .= '<div class="swiper-container swiper-scroll pillar-box" scroll-nogroup="1">
                <div class="swiper-wrapper">
                    ' . $lists . '
                </div>
            </div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

//获取用户等级
function zib_get_user_level($id = 0)
{
    if (!$id) {
        return false;
    }

    $level = (int) get_user_meta($id, 'level', true);
    if (!$level) {
        //初试等级1
        update_user_meta($id, 'level', 1);
        $level = 1;
    }
    $max = _pz('user_level_max', 10);
    return $level > $max ? $max : $level;
}

//获取用户当前经验值
function zib_get_user_integral($id = 0)
{
    if (!$id) {
        return false;
    }
    $user_integral = (int) get_user_meta($id, 'level_integral', true);
    return $user_integral;
}

//用户获取等级徽章
function zib_get_user_level_badge($id = 0, $class = '', $tip = true)
{
    if (!$id || !_pz('user_level_s', true)) {
        return;
    }

    $user_level = zib_get_user_level($id);
    return zib_get_level_badge($user_level, $class, $tip);
}

//用户获取等级徽章
function zib_get_level_badge($user_level = 0, $class = '', $tip = true)
{
    if (!$user_level) {
        return;
    }

    $icon_url  = _pz('user_level_opt', ZIB_TEMPLATE_DIRECTORY_URI . '/img/user-level-' . $user_level . '.png', 'icon_img_' . $user_level);
    $title     = esc_attr(_pz('user_level_opt', 'LV' . $user_level, 'name_' . $user_level));
    $tip_attr  = $tip ? ' data-toggle="tooltip"' : '';
    $lazy_attr = zib_get_lazy_attr('lazy_other', $icon_url, 'img-icon ' . $class, ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail-null.svg');
    $vip_badge = '<img ' . $lazy_attr . $tip_attr . ' title="' . esc_attr(strip_tags($title)) . '" alt="等级-' . esc_attr(strip_tags($title)) . zib_get_delimiter_blog_name() . '">';
    return $vip_badge;
}

/**
 * @description:
 * @param {*} $user_id
 * @param {*} $value
 * @param {*} $key
 * @param {*} $max 不限制最大值
 * @return {*}
 */
function zib_add_user_level_integral($user_id = 0, $value = 0, $key = '', $no_limit_day_max = false)
{
    //判断用户还能增加经验值，允许。||是否超过今日上限
    if (!$user_id || !$value) {
        return;
    }

    if (!$no_limit_day_max && !zib_user_is_allow_add_integral($user_id)) {
        return;
    }

    $user_integral = (int) get_user_meta($user_id, 'level_integral', true);

    $new = $value + $user_integral;

    //保存用户经验值获取明细
    zib_add_user_level_integral_detail($user_id, $value, $key, $new);

    //保存用户的等级经验值
    update_user_meta($user_id, 'level_integral', $new);
}

//判断用户还能增加经验值，允许
function zib_user_is_allow_add_integral($user_id)
{

    //判断是否超过今日上限
    $to_day  = zib_get_user_today_integral($user_id);
    $day_max = _pz('user_integral_opt', 100, 'day_max');
    if ($day_max && $to_day >= $day_max) {
        return false;
    }

    //禁封判断
    if (_pz('user_ban_s', true) && zib_user_is_ban($user_id)) {
        return false;
    }

    return true;
}

//获取用户当天的经验值
function zib_get_user_today_integral($user_id)
{
    if (!$user_id) {
        return;
    }
    $current_date = current_time('Y-m-d');
    $detail       = zib_get_user_meta($user_id, 'level_integral_date_detail', true);
    if (!$detail || !is_array($detail)) {
        $detail = array();
    }

    return isset($detail[$current_date]) ? $detail[$current_date] : 0;
}

//记录每日添加明细
function zib_add_user_level_integral_date_detail($user_id, $value)
{
    if (!$user_id || !$value) {
        return;
    }

    $current_date = current_time('Y-m-d');
    $detail       = zib_get_user_meta($user_id, 'level_integral_date_detail', true);
    if (!$detail || !is_array($detail)) {
        $detail = array();
    }

    $max    = 30; //最多保存多少条记录
    $detail = array_slice($detail, 0, $max - 1, true); //数据切割，删除多余的记录

    if (isset($detail[$current_date])) {
        $detail[$current_date] += $value;
    } else {
        $detail = array_merge(array($current_date => $value), $detail);
    }

    zib_update_user_meta($user_id, 'level_integral_date_detail', $detail);
}

//添加经验值的明细记录
function zib_add_user_level_integral_detail($user_id, $value, $key = '', $new = 0)
{
    if (!$user_id || !$value || !$key) {
        return;
    }

    //记录每天明细
    zib_add_user_level_integral_date_detail($user_id, $value);

    $detail = zib_get_user_meta($user_id, 'level_integral_detail', true);
    if (!$detail || !is_array($detail)) {
        $detail = array();
    }
    if (!$new) {
        $new = (int) get_user_meta($user_id, 'level_integral', true);
    }

    $max        = 50; //最多保存多少条记录
    $detail     = array_slice($detail, 0, $max - 1, true); //数据切割，删除多余的记录
    $new_detail = array_merge(array(array(
        'integral' => $new,
        'key'      => $key,
        'value'    => $value,
        'time'     => current_time('Y-m-d H:i:s'),
    )), $detail);

    zib_update_user_meta($user_id, 'level_integral_detail', $new_detail);
}

//挂钩更新用户等级
function zib_update_user_level($meta_id, $user_id, $meta_key, $_meta_value)
{
    if ('level_integral' == $meta_key) {
        $_meta_value = (int) $_meta_value;
        $user_level  = zib_get_user_level($user_id);
        $user_level  = $user_level ? $user_level : 1;
        $level_max   = _pz('user_level_max', 10);
        $new_level   = $user_level;

        if ($_meta_value < (int) _pz('user_level_opt', 0, 'upgrade_integral_' . ($user_level + 1))) {
            return;
        }

        if ($_meta_value >= (int) _pz('user_level_opt', 0, 'upgrade_integral_' . $level_max)) {
            $new_level = $level_max;
        } else {
            for ($i = $user_level; $i <= $level_max; $i++) {
                $upgrade   = (int) _pz('user_level_opt', 0, 'upgrade_integral_' . $i);
                $upgrade_n = (int) _pz('user_level_opt', 0, 'upgrade_integral_' . ($i + 1));
                if ($_meta_value >= $upgrade && $_meta_value < $upgrade_n) {
                    $new_level = $i;
                    break;
                    //达到升级要求
                }
            }
        }

        if ($new_level != $user_level) {
            update_user_meta($user_id, 'level', $new_level);
        }
    }
}
add_action('updated_user_meta', 'zib_update_user_level', 99, 4);
add_action("added_user_meta", 'zib_update_user_level', 99, 4);

//开启经验值添加
if (_pz('user_level_s', true)) {
    new zib_user_level_integral_add();
}

//开始挂钩添加用户等级的经验值
class zib_user_level_integral_add
{
    public function __construct()
    {
        add_action('user_checkined', array($this, 'user_checkined'), 10, 2); //签到

        add_action('user_register', array($this, 'sign_up'));
        add_action('admin_init', array($this, 'sign_in'));
        add_action('save_post', array($this, 'post_new'));
        add_action('like-posts', array($this, 'post_like'), 20, 3);
        add_action('favorite-posts', array($this, 'post_favorite'), 20, 3);

        add_action('comment_post', array($this, 'comment_new'));
        add_action('comment_unapproved_to_approved', array($this, 'comment_new'));
        add_action('like-comment', array($this, 'comment_like'), 20, 3);
        add_action('follow-user', array($this, 'followed'), 10, 2);

        add_action('bbs_score_extra', array($this, 'bbs_score_extra'), 20, 2); //帖子被加分
        add_action('bbs_posts_essence_set', array($this, 'bbs_essence'), 10, 2); //帖子成为精华
        add_action('posts_is_hot', array($this, 'bbs_posts_hot')); //热门帖子
        add_action('plate_is_hot', array($this, 'bbs_plate_hot')); //热门版块
        add_action('comment_is_hot', array($this, 'bbs_comment_hot')); //热门评论
        add_action('answer_adopted', array($this, 'bbs_adopt')); //回答被采纳
    }

    //签到
    public function user_checkined($user_id, $the_data)
    {

        if (!$user_id || !$the_data['integral']) {
            return;
        }

        zib_add_user_level_integral($user_id, $the_data['integral'], 'checkin');
    }

    //注册
    public function sign_up($user_id)
    {
        if (!$user_id) {
            return;
        }

        $value = _pz('user_integral_opt', 0, 'sign_up');
        if ($value) {
            zib_add_user_level_integral($user_id, $value, 'sign_up', true);
        }
    }

    //登录
    public function sign_in()
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $value = _pz('user_integral_opt', 0, 'sign_in');

        if ($value) {
            //每天仅一次
            $_time        = zib_get_user_meta($user_id, '_signin_integral_time', true);
            $current_time = current_time('Ymd');
            if ($_time >= $current_time) {
                return;
            }

            zib_update_user_meta($user_id, '_signin_integral_time', $current_time);
            zib_add_user_level_integral($user_id, $value, 'sign_in');
        }
    }

    //发布文章
    public function post_new($post_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $post_type = $post->post_type;
        if (in_array($post_type, array('forum_post', 'plate', 'post')) && 'publish' == $post->post_status) {
            $user_id = $post->post_author;
            if (!$user_id || zib_get_post_meta($post->ID, '_user_integral_new', true)) {
                return;
            }

            $value = _pz('user_integral_opt', 0, 'post_new');
            $key   = 'post_new';
            if ('forum_post' == $post_type) {
                $value = _pz('user_integral_opt', 0, 'bbs_posts_new');
                $key   = 'bbs_posts_new';
            }
            if ('plate' == $post_type) {
                $value = _pz('user_integral_opt', 0, 'bbs_plate_new');
                $key   = 'bbs_plate_new';
            }
            if ($value) {
                zib_update_post_meta($post->ID, '_user_integral_new', true);
                zib_add_user_level_integral($user_id, $value, $key);
            }
        }
    }

    //文章点赞
    public function post_like($post_id, $count, $action_user_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $user_id = $post->post_author;
        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }
        //一篇文章最多5次点赞加经验值
        $_this_add = (int) zib_get_post_meta($post->ID, '_user_integral_like', true);

        if (!$user_id || $_this_add >= 5) {
            return;
        }

        $value = _pz('user_integral_opt', 0, 'post_like');
        $key   = 'post_like';

        if ($value) {
            zib_update_post_meta($post->ID, '_user_integral_like', $_this_add + 1);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //文章被收藏
    public function post_favorite($post_id, $count, $action_user_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $user_id = $post->post_author;
        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        //一篇文章最多5次收藏加经验值
        $_this_add = (int) zib_get_post_meta($post->ID, '_user_integral_favorite', true);

        if (!$user_id || $_this_add >= 5) {
            return;
        }

        $key   = 'post_favorite';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_integral_favorite', $_this_add + 1);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //发布评论
    public function comment_new($comment)
    {

        $comment = get_comment($comment);

        if (empty($comment->user_id) || $comment->comment_approved != '1') {
            return;
        }

        $user_id = $comment->user_id;
        if (!$user_id || zib_get_comment_meta($comment->comment_ID, '_user_integral_new', true)) {
            return;
        }

        $key   = 'comment_new';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_integral_new', true);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //评论获赞
    public function comment_like($comment_id, $count, $action_user_id)
    {

        $comment = get_comment($comment_id);
        if (empty($comment->user_id)) {
            return;
        }

        $user_id = $comment->user_id;

        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        $_this_add = (int) zib_get_comment_meta($comment->comment_ID, '_user_integral_like', true);
        if (!$user_id || $_this_add >= 2) {
            return;
        }

        $key   = 'comment_like';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_integral_like', $_this_add + 1);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //被关注
    public function followed($follow_user_id, $followed_user_id)
    {

        $user_id = $followed_user_id;

        if (!$user_id) {
            return;
        }

        $_user_integral_followed = zib_get_user_meta($user_id, '_user_integral_followed');
        if ($_user_integral_followed && is_array($_user_integral_followed) && in_array($follow_user_id, $_user_integral_followed)) {
            return;
        }

        $key   = 'followed';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            if (is_array($_user_integral_followed)) {
                $_user_integral_followed[] = $follow_user_id;
            } else {
                $_user_integral_followed = array($follow_user_id);
            }

            zib_update_user_meta($user_id, '_user_integral_followed', $_user_integral_followed);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //帖子被加分
    public function bbs_score_extra($post_id, $action_user_id)
    {
        $post = get_post($post_id);
        if (empty($post->ID)) {
            return;
        }

        $user_id = $post->post_author;

        //自己给自己操作无效
        if ($action_user_id && $action_user_id == $user_id) {
            return;
        }

        $_this_add = (int) zib_get_post_meta($post->ID, '_user_integral_score_extra', true);
        if (!$user_id || $_this_add >= 5) {
            return;
        }

        $key   = 'bbs_score_extra';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_integral_score_extra', $_this_add + 1);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //帖子精华
    public function bbs_essence($post_id, $val)
    {
        $post = get_post($post_id);
        if (empty($post->ID) || !$val) {
            return;
        }

        $user_id   = $post->post_author;
        $_this_add = zib_get_post_meta($post->ID, '_user_integral_essence', true);
        if (!$user_id || $_this_add) {
            return;
        }

        $key   = 'bbs_essence';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_integral_essence', true);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //版块成为热门
    public function bbs_plate_hot($post)
    {

        if (!isset($post->post_author)) {
            return;
        }
        $_this_add = zib_get_post_meta($post->ID, '_user_integral_hot', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_plate_hot';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_integral_hot', true);
            zib_add_user_level_integral($post->post_author, $value, $key);
        }
    }

    //帖子成为热门
    public function bbs_posts_hot($post)
    {

        if (!isset($post->post_author)) {
            return;
        }

        $_this_add = zib_get_post_meta($post->ID, '_user_integral_hot', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_posts_hot';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_post_meta($post->ID, '_user_integral_hot', true);
            zib_add_user_level_integral($post->post_author, $value, $key);
        }
    }

    //评论成为热门
    public function bbs_comment_hot($comment)
    {
        $user_id = $comment->user_id;
        if (!$user_id) {
            return;
        }

        $_this_add = zib_get_comment_meta($comment->comment_ID, '_user_integral_hot', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_comment_hot';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_integral_hot', true);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //回答被采纳
    public function bbs_adopt($comment)
    {

        global $wp_filter, $wp_actions;

        $user_id = $comment->user_id;
        if (!$user_id) {
            return;
        }

        $_this_add = zib_get_comment_meta($comment->comment_ID, '_user_integral_adopt', true);
        if ($_this_add) {
            return;
        }

        $key   = 'bbs_adopt';
        $value = _pz('user_integral_opt', 0, $key);

        if ($value) {
            zib_update_comment_meta($comment->comment_ID, '_user_integral_adopt', true);
            zib_add_user_level_integral($user_id, $value, $key);
        }
    }

    //over
}
