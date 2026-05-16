<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-23 23:16:33
 * @LastEditTime : 2025-08-07 21:56:44
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|论坛系统|编辑功能相关
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function zib_bbs()
{
    global $zib_bbs;

    if ($zib_bbs) {
        return $zib_bbs;
    }

    $zib_bbs                   = zib_bbs::instance();
    return $GLOBALS['zib_bbs'] = $zib_bbs;
}

//获取论坛首页地址
function zib_bbs_get_home_url()
{
    return zib_get_template_page_url('pages/forums.php', array(
        'pages/forums.php' => array('论坛首页', 'forums'),
    ));
}
