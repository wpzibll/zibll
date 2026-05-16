<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:37
 * @LastEditTime : 2026-05-05 20:54:48
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

zib_require(array(
    'main',
    'ajax',
    'user',
    'author',
    'sign_register',
    'comment',
    'documentnav',
    'media',
    'new_posts',
), false, 'action/');

/**设置验证码 */
function zib_get_captcha($counts = 6)
{
    $originalcode = '0,1,2,3,4,5,6,7,8,9';
    $originalcode = explode(',', $originalcode);
    $countdistrub = 10;
    $_dscode      = '';
    for ($j = 0; $j < $counts; $j++) {
        $dscode = $originalcode[rand(0, $countdistrub - 1)];
        $_dscode .= $dscode;
    }
    return strtolower($_dscode);
}

//重置验证码限制时间,减去多少秒
function zib_captcha_time($second = 40)
{
    @session_start();
    if (!empty($_SESSION['zib_captcha_time'])) {
        $_SESSION['zib_captcha_time'] = date('Y-m-d H:i:s', strtotime('-' . $second . ' second', strtotime($_SESSION['zib_captcha_time'])));
    }
}

/**发送验证码 */
function zib_send_captcha($to, $type = 'email')
{
    @session_start();
    $code = zib_get_captcha(6);
    /**保存验证码到session */
    $_SESSION['zib_captcha']         = $code;
    $_SESSION['zib_verification_to'] = $to;

    if (!empty($_SESSION['zib_captcha_time'])) {
        $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['zib_captcha_time']);
        if ($time_x < 60) {
            //剩余时间
            return array('error' => 1, 'remaining_time' => (60 - $time_x), 'ys' => 'danger', 'msg' => (60 - $time_x) . '秒后可重新发送');
        }
    }

    $_SESSION['zib_captcha_time']        = current_time('mysql');
    $_SESSION['zib_captcha_check_count'] = 1;
    $_SESSION['zib_captcha_check_time']  = 0;

    switch ($type) {
        case 'email':
            $result = false;
            if (is_email($to)) {
                $title   = '收到验证码';
                $message = "您正在本站进行验证操作，如非您本人操作，请忽略此邮件。\r\n\r\n验证码30分钟内有效，如果超时请重新获取";
                $message .= "\r\n\r\n";
                $message .= '您的邮箱为：' . $to . "\r\n\r\n";
                $message .= '您的验证码为：';
                $message .= '<p style="font-size:34px;color:#3095f1;"><span style="border-bottom: 1px dashed #ccc; z-index: 1; position: static;">' . $code . '</span></p>';
                $result = @wp_mail($to, $title, $message);
            }
            if ($result) {
                return array('error' => 0, 'result' => true, 'msg' => '验证码已发送至您的邮箱');
            } else {
                return array('error' => 1, 'ys' => 'danger', 'msg' => '验证码发送失败');
            }
            break;
        case 'phone':
            $result = ZibSMS::send($to, $code);
            if (!empty($result['result'])) {
                $result['msg'] = '验证码短信已发送';
            }
            return $result;
            break;
    }
}

//保存用户的验证成功时间、状态
function zib_set_verify_user($type, $user_id)
{
    @session_start();

    $_SESSION['verify_user_' . $type . '_' . $user_id] = current_time('mysql');
    zib_captcha_time();
}

function zib_ajax_is_verify_user($type, $user_id, $msg = '')
{
    $verify_user = zib_is_verify_user($type, $user_id, $msg);
    if ($verify_user['error']) {
        echo json_encode($verify_user);
        exit();
    }
    return true;
}

//校验用户验证是否通过
function zib_is_verify_user($type, $user_id, $msg = '')
{
    @session_start();

    if (empty($_SESSION['verify_user_' . $type . '_' . $user_id])) {
        return array('error' => 1, 'ys' => 'danger', 'msg' => $msg . '验证失败');
    } else {
        $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['verify_user_' . $type . '_' . $user_id]);
        if ($time_x > 1800) {
            //30分钟有效
            return array('error' => 1, 'ys' => 'danger', 'msg' => $msg . '验证已过期，请重新验证');
        }
        return array('error' => 0, 'result' => true, 'msg' => $msg . '验证成功');
    }
}

/**
 * @description: 错误频率限制，避免恶意攻击，包含邮箱短信验证，密码验证
 * @param {*} $type
 * @param {*} $user_id
 * @param {*} $msg
 * @return {*}
 */
function zib_is_error_frequency_limit($key, $msg = '')
{
    if (!_pz('error_frequency_limit', false)) {
        return array('error' => 0);
    }

    @session_start();
    //频率限制，前5次不限制，5-10次限制30秒一次，后面5分钟一次
    $count        = $_SESSION['zib_check_count_' . $key] ?? 1;
    $last_time    = $_SESSION['zib_check_time_' . $key] ?? 0;
    $current_time = current_time('timestamp');

    //第6-10次，需间隔30秒
    if ($count > 5 && $count <= 10 && (($last_time + 30) - $current_time) > 0) {
        $_SESSION['zib_check_count_' . $key] = $count + 1;
        return array('error' => 1, 'ys' => 'danger', 'msg' => '错误次数过多，请' . (($last_time + 30) - $current_time) . '秒后再试');
    }

    //第11次后，需间隔300秒
    if ($count > 10 && (($last_time + 180) - $current_time) > 0) {
        $_SESSION['zib_check_count_' . $key] = $count + 1;
        return array('error' => 1, 'ys' => 'danger', 'msg' => '错误次数超限，请' . (($last_time + 180) - $current_time) . '秒后再试');
    }

    $_SESSION['zib_check_count_' . $key] = $count + 1;
    $_SESSION['zib_check_time_' . $key]  = $current_time;
    return array('error' => 0);
}

/**
 * 重置错误频率限制
 * @param {*} $key
 * @return {*}
 */
function zib_reset_error_frequency_limit($key)
{
    @session_start();
    //验证成功
    if (isset($_SESSION['zib_check_count_' . $key])) {
        unset($_SESSION['zib_check_count_' . $key]);
    }
    if (isset($_SESSION['zib_check_time_' . $key])) {
        unset($_SESSION['zib_check_time_' . $key]);
    }
}

/**
 * 邮箱、手机验证码校验
 *
 */
function zib_is_captcha($to, $code, $msg_prefix = '')
{

    //错误频率限制
    $is_captcha = zib_is_error_frequency_limit('captcha');
    if ($is_captcha['error']) {
        return $is_captcha;
    }

    if (empty($_SESSION['zib_captcha']) || $_SESSION['zib_captcha'] != $code || empty($_SESSION['zib_verification_to']) || $_SESSION['zib_verification_to'] != $to) {
        return array('error' => 1, 'ys' => 'danger', 'msg' => $msg_prefix . '验证码错误');
    } else {
        if (!empty($_SESSION['zib_captcha_time'])) {
            $time_x = strtotime(current_time('mysql')) - strtotime($_SESSION['zib_captcha_time']);
            if ($time_x > 1800) {
                //30分钟有效
                return array('error' => 1, 'ys' => 'danger', 'msg' => $msg_prefix . '验证码已过期');
            }
        }
    }

    //重置错误频率限制
    zib_reset_error_frequency_limit('captcha');

    return array('error' => 0, 'result' => true, 'msg' => $msg_prefix . '验证码效验成功');
}

/**
 * @description: AJAX验证码判断，判断错误直接退出ajax
 * @param {*} $to
 * @param {*} $code
 * @return {*}
 */
function zib_ajax_is_captcha($to_name = 'email', $code_name = 'captch')
{
    $type_name = '';
    if ('email' == $to_name) {
        $type_name = '邮箱';
    } elseif ('phone' == $to_name) {
        $type_name = '短信';
    }
    if (empty($_REQUEST[$code_name])) {
        echo(json_encode(array('error' => 1, 'msg' => '请输入' . $type_name . '验证码')));
        exit();
    }
    if (empty($_REQUEST[$to_name])) {
        echo(json_encode(array('error' => 1, 'msg' => '缺少验证参数')));
        exit();
    }
    $is_captcha = zib_is_captcha($_REQUEST[$to_name], $_REQUEST[$code_name]);
    if ($is_captcha['error']) {
        echo json_encode($is_captcha);
        exit();
    }

    return true;
}

/**前端AJAX发送验证码 */
function zib_ajax_send_captcha($captcha_type, $to, $judgment = true)
{
    if ($judgment) {
        //人机验证
        $captcha      = zib_ajax_captcha_form_judgment($captcha_type, $to);
        $captcha_type = $captcha['type'];
        $to           = $captcha['to'];
    }
    $send = zib_send_captcha($to, $captcha_type);

    if (empty($send['error']) && empty($send['msg'])) {
        $send['msg'] = '验证码已发送';
    }
    echo json_encode($send);
    exit;
}

/**
 * @description: 执行用户隐私协议勾选检测 同意协议
 * @param {*} $id
 * @return {*}
 */
function zib_ajax_agree_agreement_judgment($name = 'user_agreement')
{
    if (zib_get_agreement_input() && empty($_REQUEST[$name])) {
        echo(json_encode(array('error' => 1, 'msg' => '请先阅读并同意用户协议')));
        exit();
    }
}

//wp-ajax安全验证
function zib_ajax_wp_verify_nonce($action = null, $name = '_wpnonce')
{

    if (!$action) {
        $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : -1;
    }

    if (!isset($_REQUEST[$name]) || !wp_verify_nonce($_REQUEST[$name], $action)) {
        zib_send_json_error('环境异常，请刷新页面后稍候再试', 'warning');
    }
}

/**
 * @description: AJAX验证方式判断，已做了AJAX返回
 * @param {*} $captcha_type
 * @param {*} $input
 * @return 错误直接退出，正确返回 $captcha_type 验证方式
 */
function zib_ajax_captcha_form_judgment($captcha_type = 'email', $input = '')
{

    $captcha_type = $captcha_type ? $captcha_type : (!empty($_REQUEST['captcha_type']) ? $_REQUEST['captcha_type'] : '');
    $input        = $input ? $input : (!empty($_REQUEST[$captcha_type]) ? $_REQUEST[$captcha_type] : '');
    $input        = esc_sql(trim($input));
    if (!$captcha_type) {
        echo(json_encode(array('error' => 1, 'msg' => '参数传入错误')));
        exit();
    }

    if ('email' == $captcha_type) {
        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            echo(json_encode(array('error' => 1, 'msg' => '邮箱格式错误')));
            exit();
        }
    } elseif ('phone' == $captcha_type) {
        if (!ZibSMS::is_phonenumber($input)) {
            echo(json_encode(array('error' => 1, 'phone' => $input, 'msg' => '手机号码格式有误')));
            exit();
        }
    } else {
        if (!$input) {
            echo(json_encode(array('error' => 1, 'msg' => '请输入邮箱或手机号')));
            exit();
        }
        if (ZibSMS::is_phonenumber($input)) {
            $captcha_type = 'phone';
        } elseif (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $captcha_type = 'email';
        } else {
            echo(json_encode(array('error' => 1, 'msg' => '手机号或邮箱格式错误')));
            exit();
        }
    }
    return array('type' => $captcha_type, 'to' => $input);
}

/**
 * @description: 空白内容
 * @param {*}
 * @return {*}
 */
function zib_get_ajax_error_html($args = array())
{

    $defaults = array(
        'error'  => 1,
        'class'  => '',
        'ys'     => 'danger',
        'margin' => '50',
        'id'     => '',
        'msg'    => '内容获取出错！',
    );
    $args = wp_parse_args((array) $args, $defaults);

    $id  = $args['id'] ? ' id="' . $args['id'] . '"' : '';
    $con = '错误：' . $args['msg'] . '，错误代码：' . $args['error'];

    $con = '<div class="ajax-item text-center text-' . $args['ys'] . ' ' . $args['class'] . '" style="padding:' . $args['margin'] . 'px 0;">' . $con . '</div>';
    $con .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    $html = '<div class="ajaxpager"' . $id . '>' . $con . '</div>';
    $html = '<body><main>' . $html . '</main></body>';

    return $html;
}

/**
 * @description: 判断是否是重复昵称
 * @param {*} $name
 * @return {*}
 */
function zib_is_repetition_username($name)
{
    $name    = esc_sql(trim($name));
    $db_name = false;
    if ($name) {
        global $wpdb;
        $db_name = zibDB::table($wpdb->users)->whereOr(['user_nicename' => $name, 'display_name' => $name])->count('1');
    }

    return $db_name;
}

/**
 * 用户邮件AJAX验证|错误直接结束ajax
 * @param {*} $email 邮箱
 * @return {*}
 */
function zib_ajax_email_judgment($email)
{

    if (is_disable_email($email)) {
        echo json_encode(array('error' => 1, 'msg' => '邮箱非法或为禁止的邮箱格式'));
        exit();
    }

    return true;
}

/**
 * @description: 用户名AJAX验证|错误直接结束ajax
 * @param {*} $name
 * @return {*}
 */
function zib_ajax_username_judgment($name, $simple = false)
{
    $user_name = !empty($_REQUEST[$name]) ? wp_slash(trim($_REQUEST[$name])) : '';

    $is = zib_is_username_judgment($user_name, $simple);
    if ($is['error']) {
        echo(json_encode($is));
        exit();
    }

    return true;
}

function zib_is_username_judgment($user_name, $logn_in = false)
{

    if (!$user_name) {
        return array('error' => 1, 'msg' => '请输入用户名');
    }

    if (zib_new_strlen($user_name) < 2) {
        return array('error' => 1, 'msg' => '用户名太短');
    }
    if (zib_new_strlen($user_name) > 16) {
        return array('error' => 1, 'msg' => '用户名太长');
    }
    if (!$logn_in) {
        if (is_numeric($user_name)) {
            return array('error' => 1, 'msg' => '用户名不能为纯数字');
        }
        if (filter_var($user_name, FILTER_VALIDATE_EMAIL)) {
            return array('error' => 1, 'msg' => '请勿使用邮箱账号作为用户名');
        }
        if (is_disable_username($user_name)) {
            return array('error' => 1, 'msg' => '昵称含保留或非法字符');
        }
        //重复昵称判断
        if (_pz('no_repetition_name', true) && zib_is_repetition_username($user_name)) {
            return array('error' => 1, 'msg' => '昵称已存在，请换一个试试');
        }
    }

    return array('error' => 0);
}

//注册阅读量
function zib_post_views_record()
{

    $post_id = !empty($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    if ($post_id) {
        $post_views = (int) get_post_meta($post_id, 'views', true);
        $new        = $post_views + 1;
        update_post_meta($post_id, 'views', $new);

        $term_views_objs = zib_get_term_post_views_objs($post_id);
        if ($term_views_objs) {
            foreach ($term_views_objs as $term) {
                wp_cache_set($term->id, $term->views, 'term_views_count');
                update_term_meta($term->id, 'views', $term->views);
                $ancestors = get_ancestors($term->id, $term->taxonomy, 'taxonomy');
                if ($ancestors) {
                    foreach ($ancestors as $ancestors_id) {
                        $count = zib_get_term_posts_meta_sum($ancestors_id, 'views');
                        wp_cache_set($ancestors_id, $count, 'term_views_count');
                        update_term_meta($ancestors_id, 'views', $count);
                        $term_views_objs[] = array(
                            'id'         => $ancestors_id,
                            'views'      => $count,
                            'taxonomy'   => $term->taxonomy,
                            'is_parent ' => true,
                        );
                    }
                }
            }
        }
        do_action('posts_views_record', $post_id, $new);
        wp_send_json_success(array('post_id' => $post_id, 'views' => $new, 'term' => $term_views_objs, 'num_queries' => get_num_queries(), 'timer_stop' => timer_stop(0, 6) * 1000 . 'ms'));
    }
    wp_send_json_error(array('error' => '阅读量记录失败'));
}
add_action('wp_ajax_views_record', 'zib_post_views_record');
add_action('wp_ajax_nopriv_views_record', 'zib_post_views_record');

/**
 * @description: AJAX获取登录用户的数据
 * @param {*}
 * @return {*}
 */
function zib_ajax_get_current_user()
{
    $data = array(
        'id'           => 0,
        'is_logged_in' => false,
        'user_data'    => null,
    );

    $user_data = wp_get_current_user();
    if (isset($user_data->ID)) {
        $_data = (array) $user_data->data;
        unset($_data['user_pass']);
        $data = array(
            'id'           => $user_data->ID,
            'is_logged_in' => true,
            'user_data'    => $_data,
        );
    }

    do_action('ajax_get_current_user', $data['id'], $data);
    $data = apply_filters('ajax_get_current_user_data', $data);

    $data['num_queries'] = get_num_queries();
    $data['timer_stop']  = timer_stop(0, 6) * 1000 . 'ms';
    zib_send_json_success($data);
}
add_action('wp_ajax_get_current_user', 'zib_ajax_get_current_user');
add_action('wp_ajax_nopriv_get_current_user', 'zib_ajax_get_current_user');

/**
 * @description: 查询term的阅读量总和
 * @param {*} $post_id
 * @return {*}
 */
function zib_get_term_post_views_objs($post_id)
{

    $post_id = (int) $post_id;
    if (!$post_id) {
        return array();
    }

    global $wpdb;
    $sql_postmeta = $wpdb->postmeta;
    $sql_posts    = $wpdb->posts;
    $sql_term_rel = $wpdb->term_relationships;
    $sql_taxonomy = $wpdb->term_taxonomy;

    $sql = "SELECT $sql_taxonomy.term_id as id,$sql_taxonomy.taxonomy as taxonomy,SUM($sql_postmeta.meta_value) as views FROM $sql_postmeta
    INNER JOIN $sql_term_rel ON $sql_term_rel.term_taxonomy_id in (SELECT term_taxonomy_id FROM $sql_term_rel WHERE $sql_term_rel.object_id = $post_id )
    INNER JOIN $sql_taxonomy ON $sql_taxonomy.term_taxonomy_id = $sql_term_rel.term_taxonomy_id
    INNER JOIN $sql_posts ON $sql_posts.ID = $sql_term_rel.object_id AND $sql_posts.post_status = 'publish'
    WHERE $sql_term_rel.object_id = $sql_postmeta.post_id and $sql_postmeta.meta_key = 'views'
    GROUP BY $sql_taxonomy.term_id";

    $query = $wpdb->get_results($sql);
    return $query;
}

//获取文章分享的数据
function zib_ajax_poster_share_data()
{

    $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;

    if (stristr($id, 'term')) {
        //分类的分享
        $id = (int) str_replace('term_', '', $id);

        $term = get_term($id);
        if (empty($term->term_id)) {
            zib_send_json_error('参数错误，或内容已删除');
        }
        $obj = $term;

        $title      = trim(strip_tags($term->name));
        $desc       = zib_str_cut($term->description, 0, 160, '...');
        $banner_url = zib_get_taxonomy_img_url($term->term_id, 'full', '');

        $url = get_term_link($term);

        $tags = '';
        $tags .= $term->count ? '' . $term->count . '篇文章' : '';
    } elseif (stristr($id, 'rebate')) {
        //用户推荐返利的分享
        $user_id = (int) str_replace('rebate_', '', $id);
        $user    = get_userdata($user_id);
        if (empty($user->ID)) {
            zib_send_json_error('参数错误，或用户已删除');
        }
        $obj        = $user;
        $url        = zibpay_get_rebate_link($user_id);
        $banner_url = _pz('pay_rebate_poster_img');
        $title      = _pz('pay_rebate_poster_title');
        $desc       = _pz('pay_rebate_poster_desc');
        $tags       = '推荐人: ' . esc_attr($user->display_name);

    } elseif (stristr($id, 'user')) {
        //用户推荐返利的分享
        $user_id = (int) str_replace('user_', '', $id);
        $user    = get_userdata($user_id);
        if (empty($user->ID)) {
            zib_send_json_error('参数错误，或用户已删除');
        }
        $obj        = $user;
        $url        = zibpay_get_rebate_link($user_id);
        $banner_url = _pz('pay_rebate_poster_img');
        $title      = _pz('pay_rebate_poster_title');
        $desc       = _pz('pay_rebate_poster_desc');
        $tags       = '作者: ' . esc_attr($user->display_name);

    } else {
        $post = get_post($id);
        if (empty($post->ID)) {
            zib_send_json_error('参数错误，或内容已删除');
        }
        $obj      = $post;
        $subtitle = trim(strip_tags(zib_get_post_meta($post->ID, 'subtitle', true)));
        $title    = trim(strip_tags($post->post_title)) . $subtitle;
        $title    = zib_str_cut($title, 0, 32);

        $desc       = zib_get_excerpt(70, '...', $post);
        $url        = get_permalink($post);
        $banner_url = (zib_post_thumbnail('full', '', true, $post));

        $tags = '';
        if ('post' == $post->post_type) {
            $author = get_userdata($post->post_author)->display_name;
            $tags   = '作者: ' . esc_attr($author);
            $cat    = get_the_category($post->ID);
            $tags .= !empty($cat[0]) ? ' · 分类: ' . $cat[0]->cat_name : '';
        }
    }

    //返利链接
    $user_id = get_current_user_id();
    if (_pz('pay_rebate_s') && $user_id) {
        $url = zibpay_get_rebate_link($user_id, $url);
    }

    $data = array(
        'url'            => esc_url($url),
        'qrcode'         => zib_get_qrcode_base64($url),
        'banner'         => esc_url($banner_url),
        'banner_default' => esc_url(_pz('share_img_byimg')),
        'banner_spare'   => esc_url(ZIB_TEMPLATE_DIRECTORY_URI . '/img/share_img.jpg'),
        'logo'           => esc_url(_pz('share_logo')),
        'title'          => $title,
        'content'        => $desc,
        'tags'           => $tags,
        'description'    => _pz('share_desc', '扫描二维码阅读全文 '),
    );
    $data = apply_filters('poster_share_data', $data, $obj, $id);

    if (_pz('share_img_compatible_s')) {
        $data['banner']         = zib_get_img_auto_base64($data['banner']);
        $data['banner_default'] = zib_get_img_auto_base64($data['banner_default']);
        $data['logo']           = zib_get_img_auto_base64($data['logo']);
    }

    zib_send_json_success($data);
}
add_action('wp_ajax_poster_share_data', 'zib_ajax_poster_share_data');
add_action('wp_ajax_nopriv_poster_share_data', 'zib_ajax_poster_share_data');

function zib_ajax_share_modal()
{
    $id   = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 'post';

    switch ($type) {
        case 'post':
            $post = get_post($id);
            if (empty($post->ID)) {
                zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
            }

            //横排显示
            $html = zib_get_share('horizontal', $post);
            break;
        case 'term':
            $term = get_term($id);
            if (empty($term->term_id)) {
                zib_ajax_notice_modal('danger', '内容不存在或参数传入错误');
            }

            //横排显示
            $html = zib_get_term_share('horizontal', $term);
            break;
    }

    $dasc   = apply_filters('share_modal_header_text', '分享内容');
    $dasc   = $dasc ? '<span class="em09">' . $dasc . '</span>' : '';
    $header = zib_get_modal_colorful_header('jb-vip1', zib_get_svg('share', null, 'em14'), $dasc);
    echo $header . $html;
    exit;
}
add_action('wp_ajax_share_modal', 'zib_ajax_share_modal');
add_action('wp_ajax_nopriv_share_modal', 'zib_ajax_share_modal');

function zib_ajax_menu_search()
{

    $args = array(
        'show_form'     => false,
        'show_keywords' => _pz('search_popular_key', true),
        'show_history'  => _pz('search_history', true),
        'show_posts'    => _pz('search_posts', true),
    );

    $search = zib_get_search_box($args);

    if ($search) {
        $html = '<div class="dropdown-menu hover-show-con">';
        $html .= $search;
        $html .= '</div>';
        echo $html;
        exit();
    }
    exit('0');
}
add_action('wp_ajax_menu_search', 'zib_ajax_menu_search');
add_action('wp_ajax_nopriv_menu_search', 'zib_ajax_menu_search');

//使用ajax做安全检查
function zib_ajax_verify_nonce($action = null, $name = '_wpnonce')
{
    zib_ajax_wp_verify_nonce($action, $name);
}

/**
 * @description: ajax人机验证判断
 * @param {*} $captcha_type
 * @param {*} $input
 * @return {*}
 */
function zib_ajax_man_machine_verification($id = 0)
{
    @session_start();
    $_SESSION['machine_verification']      = false;
    $_SESSION['machine_verification_time'] = '';
    if (!$id) {
        $id = !empty($_REQUEST['action']) ? $_REQUEST['action'] : -1;
    }

    if (!empty($_REQUEST['captcha']) && is_string($_REQUEST['captcha'])) {
        @$_captcha = json_decode(stripslashes($_REQUEST['captcha']), true);
        if (!empty($_captcha)) {
            $_REQUEST['captcha'] = $_captcha;
        }
    }

    $type = _pz('user_verification_type', 'slider');
    if ('image' === $type) {
        if (empty($_REQUEST['canvas_yz']) || strlen($_REQUEST['canvas_yz']) < 4) {
            echo(json_encode(array('error' => 1, 'msg' => '请输入图形验证码')));
            exit();
        }

        if (empty($_SESSION['machine_img_code_' . $id]) || empty($_SESSION['machine_img_time_' . $id])) {
            echo(json_encode(array('error' => 1, 'msg' => '环境异常，请重新获取图形验证码')));
            exit();
        }

        if ($_SESSION['machine_img_code_' . $id] !== strtolower($_REQUEST['canvas_yz'])) {
            echo(json_encode(array('error' => 1, 'msg' => '图形验证码错误')));
            exit();
        }

        //300秒有效期
        if (($_SESSION['machine_img_time_' . $id] + 300) < time()) {

            unset($_SESSION['machine_img_code_' . $id]);
            unset($_SESSION['machine_img_time_' . $id]);

            echo(json_encode(array('error' => 1, 'msg' => '图形验证码已过期，请重新获取图形验证码')));
            exit();
        }
    }

    if ('slider' === $type) {
        if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['randstr']) || empty($_REQUEST['captcha']['spliced']) || empty($_REQUEST['captcha']['check'])) {
            echo(json_encode(array('error' => 1, 'msg' => '环境异常，请刷新后重新提交')));
            exit();
        }
        if (!zib_slider_captcha_verification($_REQUEST['captcha']['ticket'], $_REQUEST['captcha']['randstr'])) {
            echo(json_encode(array('error' => 1, 'msg' => '环境异常，人机验证失败')));
            exit();
        }
    }

    //腾讯云人机验证
    if ('tcaptcha' === $type) {
        $option = _pz('tcaptcha_option');
        if (!empty($option['api_secret_id']) && !empty($option['api_secret_key']) && !empty($option['appid']) && !empty($option['secret_key'])) {

            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['ticket'])) {
                echo(json_encode(array('error' => 1, 'msg' => '人机验证失败，请重试')));
                exit();
            }

            $verification = zib_tcaptcha_verification($_REQUEST['captcha']['ticket'], $_REQUEST['captcha']['randstr']);
            if ($verification['error']) {
                echo(json_encode(array('error' => 1, 'msg' => !empty($verification['msg']) ? $verification['msg'] : '环境异常，人机验证失败')));
                exit();
            }
        }
    }

    //极验行为验验证人机验证
    if ('geetest' === $type) {
        $option = _pz('geetest_option');
        if (!empty($option['id']) && !empty($option['key'])) {

            if (empty($_REQUEST['captcha']['ticket']) || empty($_REQUEST['captcha']['lot_number'])) {
                echo(json_encode(array('error' => 1, 'msg' => '人机验证失败，请重试')));
                exit();
            }

            $verification = zib_geetest_verification($_REQUEST['captcha']);
            if ($verification['error']) {
                echo(json_encode(array('error' => 1, 'msg' => !empty($verification['msg']) ? $verification['msg'] : '环境异常，人机验证失败')));
                exit();
            }
        }
    }

    $_SESSION['machine_verification']      = true;
    $_SESSION['machine_verification_time'] = current_time('Y-m-d H:i:s');

    return true;
}

//极验行为验验证人机验证
function zib_geetest_verification($data)
{
    $option = _pz('geetest_option');
    if (empty($option['id']) || empty($option['key'])) {
        return array('error' => 1, 'msg' => '后台参数错误，请与客服联系');
    }

    if (empty($data['ticket']) || empty($data['lot_number']) || empty($data['gen_time']) || empty($data['captcha_output'])) {
        return array('error' => 1, 'msg' => '人机验证参数异常，请重新验证');
    }

    //准备参数
    $api_server     = 'http://gcaptcha4.geetest.com/validate?captcha_id=' . $option['id'];
    $captcha_key    = $option['key'];
    $lot_number     = $data['lot_number'];
    $captcha_output = $data['captcha_output'];
    $pass_token     = $data['ticket'];
    $gen_time       = $data['gen_time'];

    //生成签名
    $sign_token = hash_hmac('sha256', $lot_number, $captcha_key);

    $query = array(
        'lot_number'     => $lot_number,
        'captcha_output' => $captcha_output,
        'pass_token'     => $pass_token,
        'gen_time'       => $gen_time,
        'sign_token'     => $sign_token,
    );

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->post($api_server, $query);
    $result   = $response->json(true);

    if (!isset($result['result'])) {
        return array('error' => 1, 'msg' => '极验人机验证服务链接失败');
    }

    if ($result['result'] === 'success') {
        return array('error' => 0);
    }

    return array('error' => 1, 'msg' => '极验人机验证失败' . ((!empty($result['reason']) ? '：' . $result['reason'] : '')) . ((!empty($result['msg']) ? '：' . $result['msg'] : '')));
}

//滑动拼图验证
function zib_slider_captcha_verification($Ticket, $Randstr)
{

    if (empty($_SESSION['machine_slider_x']) || empty($_SESSION['machine_slider_rand_str'])) {
        return false;
    }

    $machine_slider_x        = $_SESSION['machine_slider_x'];
    $machine_slider_rand_str = $_SESSION['machine_slider_rand_str'];

    $T_a = (int) substr($Ticket, 0, 2);
    $T_b = (int) substr($Ticket, -2);
    $T_x = (int) substr($Ticket, $T_a + 2, $T_b - 2);

    if (absint($T_x - $machine_slider_x) > 8) {
        return false;
    }

    $R_a = (int) substr($Randstr, 0, 1);
    $R_b = (int) substr($Randstr, -2);
    $R_x = substr($machine_slider_rand_str, $R_a, $R_b - $R_a);
    if ($R_a . $R_x . $R_b !== $Randstr) {
        return false;
    }

    return true;
}

//腾讯验证码验证
function zib_tcaptcha_verification($Ticket, $Randstr)
{

    $UserIP = ZibPay::get_ip();
    $option = _pz('tcaptcha_option');
    if (empty($option['api_secret_id']) || empty($option['api_secret_key']) || empty($option['appid']) || empty($option['secret_key'])) {
        return array('error' => 1, 'msg' => '后台参数错误，请与客服联系');
    }

    $params = array(
        'CaptchaType'  => 9,
        'Ticket'       => $Ticket,
        'UserIp'       => $UserIP,
        'Randstr'      => $Randstr,
        'CaptchaAppId' => (int) $option['appid'],
        'AppSecretKey' => $option['secret_key'],
    );
    $cred = new TxSDK_Send($option['api_secret_id'], $option['api_secret_key']);
    $send = $cred->send($params);

    if (!isset($send['Response'])) {
        return array('error' => 1, 'msg' => '腾讯云人机验证服务链接失败');
    }

    $resp = $send['Response'];

    if (!empty($resp['Error']['Message'])) {
        return array('error' => 1, 'error_data' => $resp, 'msg' => '腾讯智能验证：' . $resp['Error']['Message']);
    }

    if (isset($resp['CaptchaMsg'])) {
        if ($resp['CaptchaCode'] === 1 || strtolower($resp['CaptchaMsg']) === 'ok') {
            return array('error' => 0);
        } elseif ($resp['CaptchaMsg']) {
            return array('error' => 1, 'msg' => '人机验证失败：' . $resp['CaptchaMsg']);
        }
    }
    return array('error' => 1);
}

/**前端AJAX链接提交 */
function zib_ajax_frontend_links_submit()
{

    //人机验证
    if (_pz('verification_links_s')) {
        zib_ajax_man_machine_verification();
    }

    //执行安全验证检查，验证不通过自动结束并返回提醒
    zib_ajax_verify_nonce();

    if (isset($_COOKIE['zib_links_submit_time'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '操作过于频繁，请稍候再试')));
        exit();
    }
    if (empty($_POST['link_name'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请填写网站名称')));
        exit();
    }
    if (empty($_POST['link_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请填写链接地址')));
        exit();
    }

    if (!zib_is_url($_POST['link_url'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '网站地址格式错误')));
        exit();
    }

    //logo图像
    $logo_img_id = zib_php_upload();
    if (!empty($logo_img_id['error'])) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $logo_img_id['msg'])));
        exit();
    }
    $logo_image_url = wp_get_attachment_image_src($logo_img_id, 'medium')[0];

    /**准备数据 */
    $linkdata = array(
        'link_name'        => esc_attr($_POST['link_name']),
        'link_url'         => esc_url($_POST['link_url']),
        'link_description' => !empty($_POST['link_description']) ? esc_attr($_POST['link_description']) : '',
        'link_image'       => $logo_image_url,
        'link_visible'     => is_super_admin() ? 'Y' : 'N',
        'link_category'    => !empty($_POST['link_category']) ? $_POST['link_category'] : array(),
    );

    $linkdata = wp_unslash(sanitize_bookmark($linkdata, 'db'));

    //禁止重复提交
    global $wpdb;
    $is_repeat = zibDB::table($wpdb->links)->where('link_url', $linkdata['link_url'])->count('1');
    if ($is_repeat) {
        zib_send_json_error('您的链接已提交，请勿重复提交');
    }

    /**添加链接 */
    $links_id = wp_insert_link($linkdata);
    if (is_wp_error($links_id)) {
        echo(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => $links_id->get_error_message())));
        exit();
    }

    //设置浏览器缓存限制提交的间隔时间
    $expire = time() + 30;
    setcookie('zib_links_submit_time', time(), $expire, '/', '', false);

    $send_msg = $linkdata['link_visible'] === 'Y' ? '提交成功' : '提交成功，等待管理员处理';
    echo(json_encode(array('msg' => $send_msg, 'reload' => true)));
    /**添加执行挂钩 */
    do_action('zib_ajax_frontend_links_submit_success', $linkdata);
    exit();
}
add_action('wp_ajax_frontend_links_submit', 'zib_ajax_frontend_links_submit');
add_action('wp_ajax_nopriv_frontend_links_submit', 'zib_ajax_frontend_links_submit');

/**
 * ajax 函数节流 ,防止高并发带来的问题
 * @param string $key 键名
 * @param string $args 参数
 * @param int $time 时间
 * @param string $msg 提示信息
 * @return bool
 */
function zib_ajax_debounce($key, $args, $time = 3, $msg = '操作过于频繁，请%time%秒后再试')
{

    $is_throttle = zib_fun_throttle($time, $key, $args);
    if (is_wp_error($is_throttle) && is_super_admin()) {
        zib_send_json_error(['msg' => '节流函数异常，可能会导致数据错误，请及时处理！错误信息：' . $is_throttle->get_error_message() . '（此消息仅管理员可见）', 'error_msg' => $is_throttle->get_error_message()]);
    }

    if (!$is_throttle) {
        $msg = strstr($msg, '%time%') ? str_replace('%time%', $time, $msg) : $msg;
        zib_send_json_error(['msg' => $msg, 'key' => $key, 'args' => $args]);
    }

    return true;
}

//查询文章
function zib_ajax_query_posts_lists()
{
    $posts_per_page = isset($_GET['count']) ? (int) $_GET['count'] : 12;
    $paged          = zib_get_the_paged();
    $style          = isset($_GET['style']) ? $_GET['style'] : 'text';
    $paginate       = isset($_GET['paginate']) ? $_GET['paginate'] : '';
    $blank          = isset($_GET['blank']) ? $_GET['blank'] : '';
    $ajax_url       = zib_get_current_url();

    $args = array(
        'paged' => $paged,
    );
    //不需要分页
    $paginate = isset($_GET['paginate']) ? $_GET['paginate'] : '';
    if (!$paginate) {
        $args['no_found_rows'] = true; //不统计总数
    }

    //查询文章
    $new_query = zib_get_posts_query($args);

    $list_args = array(
        'show_thumb'  => $style === 'minicard',
        'show_meta'   => $style === 'minicard',
        'show_number' => false,
        'echo'        => false,
        'class'       => 'ajax-item',
        'blank'       => $blank,
    );
    $posts_lists = '';
    if ($new_query->have_posts()) {
        while ($new_query->have_posts()): $new_query->the_post();
            $posts_lists .= zib_posts_mini_while($list_args, $new_query);
        endwhile;
    }
    wp_reset_query();

    if (1 == $paged && !$posts_lists) {
        $posts_lists = zib_get_ajax_null('暂无内容', 10) . json_encode($new_query);
    }

    if ($paginate === 'ajax') {
        $posts_lists .= zib_get_ajax_next_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url, 'text-center theme-pagination ajax-pag', 'next-page ajax-next', '', 'paged', 'no');
    } elseif ($paginate === 'number') {
        $posts_lists .= zib_get_ajax_number_paginate($new_query->found_posts, $paged, $posts_per_page, $ajax_url);
    } else {
        $posts_lists .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    }

    zib_ajax_send_ajaxpager($posts_lists);
}
add_action('wp_ajax_query_posts_lists', 'zib_ajax_query_posts_lists');
add_action('wp_ajax_nopriv_query_posts_lists', 'zib_ajax_query_posts_lists');
