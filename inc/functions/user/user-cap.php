<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-11-12 16:19:06
 * @LastEditTime: 2024-12-08 20:42:02
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|用户权限相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 判断用户是否拥有某个权限
 * @param {*} $user_id
 * @param {*} $capability
 * @param {array} $args
 * @return {*}
 */
function zib_user_can($user_id, $capability, ...$args)
{
    $is_can = false;
    //第一步最高权限，管理员判断
    if (is_super_admin($user_id)) {
        $is_can = true;
    }

    if (!$is_can) {
        $cap_roles = zib_get_cap_roles($capability);
        switch ($capability) {
            case 'new_post_delete':
            case 'new_post_edit':
                if (!empty($args[0])) {
                    if (!is_object($args[0])) {
                        $post = get_post($args[0]);
                    } else {
                        $post = $args[0];
                    }

                    //自己必须是本人
                    if (isset($post->post_author) && $post->post_author == $user_id) {
                        if ('draft' === $post->post_status) {
                            //草稿允许直接删除或编辑
                            $is_can = true;
                        } else {
                            $is_can = zib_is_can_roles($user_id, $cap_roles);
                        }
                    }
                }
                break;

            case 'comment_view': //查看评论
                $is_can = zib_is_can_roles($user_id, $cap_roles);
                if (!$is_can && !empty($args[0])) {
                    $post = get_post($args[0]);
                    if (isset($post->ID)) {
                        if ($post->post_author == $user_id) {
                            //自己是当前文章作者，判断自己是否能修改自己的评论
                            $_roles = zib_get_cap_roles('comment_edit_my_post');
                            $is_can = zib_is_can_roles($user_id, $_roles);
                        }
                        if (!$is_can) {
                            $_roles = zib_get_cap_roles('comment_edit_other');

                            //版主判断
                            if (!empty($_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                                $is_can = true;
                            }

                            //超级版主判断
                            if (!empty($_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                                $is_can = true;
                            }

                            //分区版主判断
                            if (!empty($_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                                $is_can = true;
                            }
                        }
                    }
                }

                break;

            case 'comment_audit_no': //免审核直接发布
                $is_can = zib_is_can_roles($user_id, $cap_roles);

                if (!$is_can && !empty($args[0])) {
                    $is_can = zib_user_can($user_id, 'comment_audit', 0, $args[0]);
                }

                break;

            case 'comment_set_topping': //将自己管理下的评论设置 置顶 
                //传入第一个参数为评论
                if (!empty($args[0])) {
                    $comment = get_comment($args[0]);
                    if (!empty($comment->comment_post_ID)) {
                        $post = get_post($comment->comment_post_ID);
                        if (isset($post->ID)) {
                            if ($post->post_author == $user_id) {
                                //自己是当前文章作者
                                $_roles = zib_get_cap_roles($capability . '_my_post');
                                $is_can = zib_is_can_roles($user_id, $_roles);
                            }

                            if (!$is_can) {
                                $_roles = zib_get_cap_roles($capability . '_other');

                                //版主判断
                                if (!empty($_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                                    $is_can = true;
                                }

                                //超级版主判断
                                if (!empty($_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                                    $is_can = true;
                                }

                                //分区版主判断
                                if (!empty($_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                                    $is_can = true;
                                }
                            }

                        }
                    }
                }
                break;

            case 'comment_edit': //编辑评论
            case 'comment_delete': //删除评论
            case 'comment_audit': //审核评论

                //传入第一个参数为评论
                if (!empty($args[0])) {
                    $comment = get_comment($args[0]);
                    if (!empty($comment->user_id)) {
                        if ($user_id && $comment->user_id == $user_id && 'comment_audit' !== $capability) {
                            //自己就是评论作者
                            $is_can = zib_is_can_roles($user_id, $cap_roles);
                        }
                        if (!$is_can) {
                            $post = get_post($comment->comment_post_ID);
                            if (isset($post->ID)) {
                                if ($post->post_author == $user_id) {
                                    //自己是当前文章作者
                                    $_roles = zib_get_cap_roles($capability . '_my_post');
                                    $is_can = zib_is_can_roles($user_id, $_roles);
                                }
                                if (!$is_can) {
                                    $_roles = zib_get_cap_roles($capability . '_other');

                                    //版主判断
                                    if (!empty($_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                                        $is_can = true;
                                    }

                                    //超级版主判断
                                    if (!empty($_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                                        $is_can = true;
                                    }

                                    //分区版主判断
                                    if (!empty($_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                                        $is_can = true;
                                    }
                                }
                            }
                        }
                    }
                }

                //传入第二个参数为评论所在的文章
                if (!$is_can && !empty($args[1])) {
                    $post = get_post($args[1]);
                    if (isset($post->ID)) {
                        if ($post->post_author == $user_id) {
                            //自己是当前文章作者
                            $_roles = zib_get_cap_roles($capability . '_my_post');
                            $is_can = zib_is_can_roles($user_id, $_roles);
                        }
                        if (!$is_can) {
                            $_roles = zib_get_cap_roles($capability . '_other');

                            //版主判断
                            if (!empty($_roles['moderator']) && zib_bbs_is_the_moderator($post, $user_id)) {
                                $is_can = true;
                            }

                            //超级版主判断
                            if (!empty($_roles['plate_author']) && zib_bbs_is_the_moderator($post, $user_id) === 'plate_author') {
                                $is_can = true;
                            }

                            //分区版主判断
                            if (!empty($_roles['cat_moderator']) && zib_bbs_is_the_cat_moderator($post, $user_id)) {
                                $is_can = true;
                            }
                        }
                    }
                }
                break;

            default:
                //默认执行参数
                $is_can = zib_is_can_roles($user_id, $cap_roles);
                break;
        }
    }

    return apply_filters('zib_user_can', $is_can, $user_id, $capability, $args);
}

/**
 * @description: 当前用户能力判断
 * @param {*} $capability
 * @param {array} $args
 * @return {*}
 */
function zib_current_user_can($capability, ...$args)
{
    return zib_user_can(get_current_user_id(), $capability, ...$args);
}

/**
 * @description: 判断用户是否是对应的角色，用于zib_user_can的基本判断
 * @param {*} $user_id
 * @param {*} $roles 角色数组
 * @return {*}
 */
function zib_is_can_roles($user_id, $cap_roles = array())
{
    if (!empty($cap_roles['all'])) {
        //如果该能力包含all或者logged，这直接return true;
        return true;
    }
    if (!empty($cap_roles['logged']) && $user_id && get_current_user_id() == $user_id) {
        //如果该能力包含all或者logged，这直接return true;
        return true;
    }
    if ($user_id && !empty($cap_roles['vip'])) {
        //vip判断
        $vip_level = zib_get_user_vip_level($user_id);
        if ($vip_level && $vip_level >= $cap_roles['vip']) {
            return true;
        }
    }
    if ($user_id && !empty($cap_roles['level'])) {
        //用户等级判断
        $level = zib_get_user_level($user_id);
        if ($level && $level >= $cap_roles['level']) {
            return true;
        }
    }
    if ($user_id && !empty($cap_roles['auth']) && zib_is_user_auth($user_id)) {
        //认证用户判断
        return true;
    }

    //添加判断挂钩
    if (apply_filters('is_can_roles', false, $user_id, $cap_roles)) {
        return true;
    }
    return false;
}

/**
 * @description: 获取拥有能力的角色数组
 * @param {*} $capability 能力key名称
 * @param {array} $default
 * @return {*}
 */
function zib_get_cap_roles($capability, array $default = array())
{
    global $all_cap_roles;
    if (!isset($all_cap_roles)) {
        $all_cap_roles = _pz('user_cap');
    }

    if (isset($all_cap_roles[$capability])) {
        return $all_cap_roles[$capability];
    } else {
        return $default;
    }
}

/**
 * @description: 获取一个能力的拥有者列表
 * @param {*} $capability
 * @return {*}
 */
function zib_get_cap_roles_lists($capability, $class = 'badg mm3')
{

    return zib_get_hascaps_roles_lists(zib_get_cap_roles($capability), $class);
}

//获取拥有此权限的角色列表
function zib_get_hascaps_roles_lists($roles = array(), $class = 'badg mm3')
{

    //前置判断
    if (!$roles || !is_array($roles)) {
        return;
    }
    $lists = '';
    if (isset($roles['vip'])) {
        $vip = '';
        if (1 == $roles['vip']) {
            $_class = 'jb-vip1 ' . $class;
            $vip    = zibpay_get_vip_icon(1, 'mr6 em12') . _pz('pay_user_vip_1_name') . (_pz('pay_user_vip_2_s', true) ? '及以上会员' : '');
        }
        if (2 == $roles['vip']) {
            $_class = 'jb-vip2 ' . $class;
            $vip    = zibpay_get_vip_icon(2, 'mr6 em12') . _pz('pay_user_vip_2_name');
        }
        $lists .= $vip ? '<span class="' . $_class . '">' . $vip . '</span>' : '';
    }

    if (!empty($roles['level'])) {
        $lists .= '<span class="' . $class . '">' . zib_get_level_badge($roles['level'], 'mr6 em12') . '及更高等级</span>';
    }

    if (!empty($roles['auth'])) {
        $lists .= '<span class="' . $class . '">' . zib_get_svg('user-auth', null, 'mr6 em12') . '认证用户</span>';
    }

    return apply_filters('hascaps_roles_lists', $lists, $roles, $class);
}

//获取权限不足的提醒内容
function zib_get_nocan_info($user_id, $capability, $msg = '', $margin = 30, $width = 280, $height = 0)
{

    $can_info = '';
    if ($user_id) {
        $can_info = zib_get_user_ban_nocan_info($user_id, $msg, $margin, $width, $height);
    }

    if (!$can_info) {
        $null_msg = '抱歉！权限不足';
        $null_msg .= $msg ? '，' . $msg : '';
        $can_info = zib_get_null($null_msg, $margin, 'null-cap.svg', '', $width, $height);

        if (!$user_id) {
            //未登录
            $can_info = zib_get_user_singin_page_box('mt20', 'Hi！请先登录');
        } else {
            $roles_lists = zib_get_cap_roles_lists($capability);
            $can_info .= $roles_lists ? '<div class="text-center mb20"><div class="em09 muted-2-color mb20">成为以下用户组可拥有此权限</div>' . $roles_lists . '</div>' : '';
        }
    }

    return $can_info ? '<div class="nocan-info">' . $can_info . '</div>' : '';
}

/**
 * @description: 获取当前登录用户权限允许的数字
 * @param {*} $capability
 * @param {*} $default
 * @return {*}
 */
function zib_get_current_user_can_number($capability, $default = '')
{
    return zib_get_user_can_number(get_current_user_id(), $capability, $default);
}

/**
 * @description: 获取用户权限允许的数字
 * @param {*} $user_id
 * @param {*} $capability
 * @param {*} $default
 * @return {*}
 */
function zib_get_user_can_number($user_id, $capability, $default = '')
{

    $options = _pz($capability);

    //没有参数
    if (empty($options[0]['type'])) {
        return $default; //默认
    }

    $args = array();
    foreach ($options as $opt) {
        $opt['val'] = (float) $opt['val'];
        if ($opt['type'] === 'default') {
            $default = $opt['val'];
        }

        if ((!isset($args[$opt['type']]) || $args[$opt['type']] < $opt['val']) && (!$default || $opt['val'] > $default)) {
            $args[$opt['type']] = (float) $opt['val'];
        }
    }

    if (!$user_id || !$args) {
        return $default;
    }

    //重新排序
    arsort($args);
    // {"admin":50,"cat_moderator":44,"plate_author":33,"auth":22,"vip_level_2":20,"user_level_5":5}

    return zib_screen_user_can_val($user_id, $args) ?: $default;
}

//筛选
function zib_screen_user_can_val($user_id, $args)
{
    if (!$user_id) {
        return false;
    }

    $val = false;
    foreach ($args as $k => $v) {
        if (zib_user_is_val_role($k, $user_id)) {
            return $v;
        }
    }

    return $val;
}

function zib_user_is_val_role($role, $user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $v = true;

    //所有
    if ($role == 'all' || $role == 'default') {
        return $v;
    }

    //只要登录就行
    if ($role == 'logged' && $user_id) {
        return $v;
    }

//管理员
    if ($role == 'admin' && is_super_admin($user_id)) {
        return $v;
    }

//认证用户
    if ($role == 'auth' && zib_is_user_auth($user_id)) {
        return $v;
    }

//会员等级
    if (stristr($role, 'vip_level_')) {
        $vip_level      = str_replace('vip_level_', '', $role);
        $user_vip_level = zib_get_user_vip_level($user_id);
        if ($user_vip_level && $user_vip_level >= $vip_level) {
            return $role;
        }
    }

//用户等级
    if (stristr($role, 'user_level_')) {
        $level      = str_replace('user_level_', '', $role);
        $user_level = zib_get_user_level($user_id);
        if ($level && $user_level >= $level) {
            return $role;
        }
    }

    if (apply_filters('is_can_roles', false, $user_id, array($role => true))) {
        return $v;
    }

    return false;
}
