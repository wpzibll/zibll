<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-18 23:36:57
 * @LastEditTime: 2024-07-05 12:35:26
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台用户编辑配置项目|仅在后台引用
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_render_profile_form_fields($profile_user)
{
    $fields = array(
        array(
            'type'    => 'content',
            'content' => '<h3>更多资料</h3><span style="color: #fb3f2c;">此处内容建议在前台用户中心修改</span>',
        ),
        array(
            'id'      => 'custom_avatar',
            'type'    => 'upload',
            'library' => 'image',
            'title'   => '自定义头像',
        ),
        array(
            'id'      => 'cover_image',
            'type'    => 'upload',
            'library' => 'image',
            'title'   => '自定义封面',
        ),
        array(
            'id'      => 'gender',
            'type'    => 'select',
            'title'   => '性别',
            'options' => array(
                '保密' => '保密',
                '男'  => '男',
                '女'  => '女',
            ),
            'default' => '保密',
        ),
        array(
            'id'          => 'qq',
            'type'        => 'text',
            'title'       => 'QQ',
            'placeholder' => '请输入QQ号',
        ),
        array(
            'id'          => 'weixin',
            'type'        => 'text',
            'title'       => '微信',
            'placeholder' => '请输入微信号',
        ),
        array(
            'id'          => 'weibo',
            'type'        => 'text',
            'title'       => '微博',
            'placeholder' => '请输入微博地址',
        ),
        array(
            'id'          => 'github',
            'type'        => 'text',
            'title'       => 'GitHub',
            'placeholder' => '请输入GitHub地址',
        ),
        array(
            'id'          => 'address',
            'type'        => 'text',
            'title'       => '住址',
            'placeholder' => '请输入住址',
        )
        , array(
            'id'          => 'phone_number',
            'type'        => 'text',
            'title'       => '绑定手机号',
            'placeholder' => '请输入手机号',
            'attributes'  => array(
                'data-readonly-id' => 'user_phone_number',
                'readonly'         => 'readonly',
            ),
            'desc'        => '手机号涉及到用户登录的功能，不建议后台修改<br><a href="javascript:;" class="but c-yellow remove-readonly" readonly-id="user_phone_number">修改用户手机号</a>',
        ),
    );

    $value = array();
    if (!empty($profile_user->ID)) {
        $option_meta_keys = zib_get_option_meta_keys('user_meta');
        $zib_meta         = get_user_meta($profile_user->ID, 'zib_other_data', true);
        foreach ($fields as $field) {
            if (!empty($field['id'])) {
                if (in_array($field['id'], $option_meta_keys)) {
                    if (isset($zib_meta[$field['id']])) {
                        $value[$field['id']] = $zib_meta[$field['id']];
                    }
                } else {
                    $meta = get_user_meta($profile_user->ID, $field['id']);
                    if (isset($meta[0])) {
                        $value[$field['id']] = $meta[0];
                    }
                }
            }
        }
    }

    $csf_args = array(
        'class'  => 'csf-profile-options',
        'value'  => $value,
        'form'   => false,
        'nonce'  => false,
        'fields' => $fields,
    );
    ZCSF::instance('profile_options', $csf_args);
}

add_action('show_user_profile', 'zib_render_profile_form_fields');
add_action('edit_user_profile', 'zib_render_profile_form_fields');

function zib_admin_save_profile($cuid)
{

    $fields = array(
        'custom_avatar',
        'cover_image',
        'gender',
        'qq',
        'weixin',
        'weibo',
        'github',
        'address',
        'phone_number',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            zib_update_user_meta($cuid, $field, $_POST[$field]);
        }
    }
}
add_action('personal_options_update', 'zib_admin_save_profile');
add_action('edit_user_profile_update', 'zib_admin_save_profile');
