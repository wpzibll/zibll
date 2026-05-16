<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:36
 * @LastEditTime: 2025-02-18 13:10:04
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (!$_POST) {
    exit;
}

require dirname(__FILE__) . '/../../../../wp-load.php';

if (!is_super_admin()) {
    print_r(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '权限不足')));
    exit;
}

if (empty($_POST['action']) && empty($_POST['page_id'])) {
    exit;
}

if (empty($_POST['paged'])) {
    $_POST['paged'] = 1;
}

switch ($_POST['action']) {

    case 'post-navs.settings':

        if (empty($_POST['navs_show_cat_id'])) {
            print_r(json_encode(array('error' => 1, 'ys' => 'danger', 'msg' => '请选择需要显示的分类')));
            exit();
        }

        if ($_POST['navs_page_name']) {
            update_post_meta($_POST['page_id'], 'navs_page_name', $_POST['navs_page_name']);
        }

        if ($_POST['navs_page_desc']) {
            update_post_meta($_POST['page_id'], 'navs_page_desc', $_POST['navs_page_desc']);
        }

        if ($_POST['navs_show_cat_id']) {
            update_post_meta($_POST['page_id'], 'navs_show_cat_id', $_POST['navs_show_cat_id']);
        }

        print_r(json_encode($_POST['navs_show_cat_id']));

        exit();
        break;

    default:
        exit();
        break;

}
