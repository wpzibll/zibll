<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime: 2023-08-12 12:06:48
 */

//获取句子文件的绝对路径
$path = dirname(__FILE__, 4) . "/yiyan.txt";
if (!file_exists($path)) {
    $path = dirname(__FILE__) . "/qv-yiyan.txt";
}
$file = file($path);

if (count($file) < 2) {
    exit('一言文件yiyan.txt，内容为空/&/');
}

//随机读取一行
$arr     = mt_rand(0, count($file) - 2);
$arr     = ($arr % 2 === 0) ? $arr + 1 : $arr;
$content = trim($file[$arr]) . '/&/' . trim($file[$arr + 1]);
//编码判断，用于输出相应的响应头部编码
if (isset($_GET['charset']) && !empty($_GET['charset'])) {
    $charset = $_GET['charset'];
    if (strcasecmp($charset, "gbk") == 0) {
        $content = mb_convert_encoding($content, 'gbk', 'utf-8');
    }
} else {
    $charset = 'utf-8';
}
header("Content-Type: text/html; charset=$charset");
echo $content;
