<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-10-15 23:13:27
 * @LastEditTime : 2025-07-25 15:03:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|ajax加密文件函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: ajax 返回错误
 * @param {*} $data
 * @param {*} $type danger warning info success
 * @return {*}
 */
function zib_send_json_error($data = false, $type = 'danger')
{

    $send_data = array(
        'error' => true,
        'ys'    => $type,
    );

    if ($data && is_array($data)) {
        $send_data = array_merge($data, $send_data);
    } elseif ($data && is_string($data)) {
        $send_data['msg'] = $data; //兼容消息模式
    }

    header('Content-Type: application/json');
    echo json_encode($send_data);
    exit();
}

/**
 * @description: ajax 返回成功
 * @param {*} $data
 * @param {*} $type danger warning info success
 * @return {*}
 */
function zib_send_json_success($data = false, $type = '')
{
    $send_data = array(
        'error' => false,
    );

    if ($type) {
        $send_data['type'] = $type;
    }

    if ($data && is_array($data)) {
        $send_data = array_merge($data, $send_data);
    } elseif ($data && is_string($data)) {
        $send_data['msg'] = $data; //兼容消息模式
    }

    //设置头
    header('Content-Type: application/json');
    echo json_encode($send_data);
    exit();
}
