<?php

/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 17:40:41
 * @LastEditTime : 2026-05-05 23:37:08
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|工具函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * @description: 判断是否在微信APP内
 * @param {*} $exclude 排除企业微信、微信MC版、微信Windows版
 * @return {*}
 */
function zib_is_wechat_app($exclude = array('wxwork', 'windows', 'mac'))
{
    $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

    if (!strripos($useragent, 'micromessenger')) {
        return false;
    }

    //排除企业微信、微信MC版、微信Windows版
    if (in_array('wxwork', $exclude) && strripos($useragent, 'wxwork')) {
        return false;
    }

    if (in_array('windows', $exclude) && strripos($useragent, 'WindowsWechat')) {
        return false;
    }

    if (in_array('mac', $exclude) && strripos($useragent, 'MacWechat')) {
        return false;
    }

    return true;
}

//删除内容或者数组的两端空格
function zib_trim($Input)
{
    if (!is_array($Input)) {
        return trim($Input);
    }
    return array_map('zib_trim', $Input);
}

//判断是否是蜘蛛爬虫
function zib_is_crawler()
{

    static $zib_is_crawler = 'is-null';
    if ($zib_is_crawler !== 'is-null') {
        return $zib_is_crawler;
    }

    $bots = array(
        'Baidu'         => 'baiduspider',
        'Google Bot'    => 'google',
        '360spider'     => '360spider',
        'Sogou'         => 'spider',
        'soso.com'      => 'sosospider',
        'MSN'           => 'msnbot',
        'Alex'          => 'ia_archiver',
        'Lycos'         => 'lycos',
        'Ask Jeeves'    => 'jeeves',
        'Altavista'     => 'scooter',
        'AllTheWeb'     => 'fast-webcrawler',
        'Inktomi'       => 'slurp@inktomi',
        'Turnitin.com'  => 'turnitinbot',
        'Technorati'    => 'technorati',
        'Yahoo'         => 'yahoo',
        'Findexa'       => 'findexa',
        'NextLinks'     => 'findlinks',
        'Gais'          => 'gaisbo',
        'WiseNut'       => 'zyborg',
        'WhoisSource'   => 'surveybot',
        'Bloglines'     => 'bloglines',
        'BlogSearch'    => 'blogsearch',
        'PubSub'        => 'pubsub',
        'Syndic8'       => 'syndic8',
        'RadioUserland' => 'userland',
        'Gigabot'       => 'gigabot',
        'Become.com'    => 'become.com',
        'Yandex'        => 'yandex',
    );
    $useragent      = isset($_SERVER['HTTP_USER_AGENT']) ? addslashes(strtolower($_SERVER['HTTP_USER_AGENT'])) : '';
    $zib_is_crawler = false;
    if ($useragent) {
        foreach ($bots as $name => $lookfor) {
            if (!empty($useragent) && (false !== stripos($useragent, $lookfor))) {
                $zib_is_crawler = $name;
            }
        }
    }

    return $zib_is_crawler;
}

/**
 * @description: 浮点数四舍五入
 * @param {*} $value
 * @return {*}
 */
function zib_floatval_round($value, $to_string = true, $precision = 2)
{
    $value = floatval(round((float) $value, $precision));
    return $to_string ? (string) $value : $value;
}

/**后台生成二维码图片 */
function zib_get_qrcode_base64($url)
{
    //引入phpqrcode类库
    require_once get_theme_file_path('/inc/class/qrcode.class.php');
    $errorCorrectionLevel = 'L'; //容错级别
    $matrixPointSize      = 6; //生成图片大小
    ob_start();
    QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
    $data = ob_get_contents();
    ob_end_clean();

    $imageString = base64_encode($data);
    header('content-type:application/json; charset=utf-8');
    return 'data:image/jpeg;base64,' . $imageString;
}

/**
 * @description:
 * @param {*} $url
 * @return {*}
 */
function zib_get_img_auto_base64($url)
{
    $base64 = zib_get_img_base64($url);
    return $base64 ?: $url;
}

/**
 * @description: 将图片链接转为base64格式
 * @param {*} $url
 * @return {*}
 */
function zib_get_img_base64($url)
{
    if (!$url) {
        return;
    }

    $cache_key = md5($url);
    $cache     = wp_cache_get($cache_key, 'image_base64', true);
    if ($cache !== false) {
        return $cache;
    }

    $base64_encode = base64_encode(file_get_contents($url));
    $base64        = $base64_encode ? 'data:image/jpeg;base64,' . $base64_encode : false;

    wp_cache_set($cache_key, $base64, 'image_base64');

    return $base64;
}

/**
 * 判断是否是有效的URL
 * @param string $url
 * @return bool
 */
function zib_is_url($url)
{
    return preg_match("/^(http:\/\/|https:\/\/).*$/", $url);
}

//判断是否启用了图片懒加载
function zib_is_lazy($key, $default = false)
{
    if (zib_is_crawler()) {
        return false;
    }

    return _pz($key, $default);
}

function zib_get_lazy_attr($key, $src, $class = '', $lazy_src = ZIB_TEMPLATE_DIRECTORY_URI . '/img/thumbnail.svg')
{
    return zib_is_lazy($key) ? ' class="lazyload ' . $class . '" src="' . $lazy_src . '" data-src="' . $src . '"' : ' class="' . $class . '" src="' . $src . '"';
}

//为已经添加了图片懒加载的元素移出懒加载的内容
function zib_str_remove_lazy($str = '')
{
    $pattern     = "/<img(.*?)src=('|\")(.*?)('|\") data-src=('|\")(.*?)('|\")(.*?)>/i";
    $replacement = '<img$1src=$5$6$7$8>';

    return preg_replace($pattern, $replacement, str_replace('lazyload', '', $str));
}

function zib_imgtobase64($img = '')
{
    $imageInfo = getimagesize($img);
    return 'data:' . $imageInfo['mime'] . ';base64,' . chunk_split(base64_encode(file_get_contents($img)));
}

//搜索数组多维数组
function zib_array_search($array, $search, $key = 'id', $value = 'count')
{
    //if (!is_array($array) || !is_object($array)) return array();
    $array = (array) $array;
    foreach ($array as $v) {
        $v = (array) $v;
        if ($search == $v[$key]) {
            return $v[$value];
        }
    }
    return false;
}

//中文文字计数
function zib_new_strlen($str, $charset = 'utf-8')
{
    //中文算一个，英文算半个
    return (int) ((strlen($str) + mb_strlen($str, $charset)) / 4);
}

//时间转为ios 8601格式
function zib_time_to_c($time)
{

    if (is_numeric($time)) {
        $time = date('Y-m-d H:i:s', intval($time));
    }

    $datetime = date_create($time, wp_timezone());
    return $datetime->format('c');
}

//时间倒序格式化
function zib_get_time_ago($time)
{
    if (is_int($time)) {
        $time = intval($time);
    } else {
        $time = strtotime($time);
    }

    if (!_pz('time_ago_s', true) && _pz('time_format')) {
        return date(_pz('time_format'), $time);
    }
    $ctime = intval(strtotime(current_time('mysql')));
    $t     = $ctime - $time; //时间差 （秒）

    if ($t < 0) {
        return date('Y-m-d H:i', $time);
    }
    $y = intval(date('Y', $ctime) - date('Y', $time)); //是否跨年
    if (0 == $t) {
        $text = '刚刚';
    } elseif ($t < 60) {
        //一分钟内
        $text = $t . '秒前';
    } elseif ($t < 3600) {
        //一小时内
        $text = floor($t / 60) . '分钟前';
    } elseif ($t < 86400) { //一天内
        $text = floor($t / 3600) . '小时前'; // 一天内
    } elseif ($t < 2592000) {
        //30天内
        if ($time > strtotime(date('Ymd', strtotime('-1 day')))) {
            $text = '昨天';
        } elseif ($time > strtotime(date('Ymd', strtotime('-2 days')))) {
            $text = '前天';
        } else {
            $text = floor($t / 86400) . '天前';
        }
    } elseif ($t < 31536000 && 0 == $y) {
        //一年内 不跨年
        $m = date('m', $ctime) - date('m', $time) - 1;

        if (0 == $m) {
            $text = floor($t / 86400) . '天前';
        } else {
            $text = $m . '个月前';
        }
    } elseif ($t < 31536000 && $y > 0) {
        //一年内 跨年
        $text = (12 - date('m', $time) + date('m', $ctime)) . '个月前';
    } else {
        $text = (date('Y', $ctime) - date('Y', $time)) . '年前';
    }

    return $text;
}

//剩下的时间格式化
function zib_get_time_remaining($time, $over_text = '已过期')
{

    if (is_int($time)) {
        $time = intval($time);
    } else {
        $time = strtotime($time);
    }

    $ctime = intval(strtotime(current_time('mysql')));
    $t     = $time - $ctime; //时间差 （秒）

    if ($t <= 0) {
        return $over_text;
    }

    $y = intval(date('Y', $ctime) - date('Y', $time)); //是否跨年
    if ($t < 60) {
        //一分钟内
        $text = $t . '秒后';
    } elseif ($t < 3600) {
        //一小时内
        $text = floor($t / 60) . '分钟后';
    } elseif ($t < 86400) { //一天内
        $text = floor($t / 3600) . '小时后'; // 一天内
    } elseif ($t < 2592000) {
        //30天内
        $text = floor($t / 86400) . '天后';
    } elseif ($t < 31536000 && 0 == $y) {
        //一年内 不跨年
        $m = date('m', $ctime) - date('m', $time) - 1;
        if ($m > 0) {
            $text = $m . '月后';
        } else {
            $text = floor($t / 86400) . '天后';
        }
    } elseif ($t < 31536000 && $y > 0) {
        //一年内 跨年
        $text = (12 - date('m', $time) + date('m', $ctime)) . '月后';
    } else {
        $text = (date('Y', $time) - date('Y', $ctime)) . '年后';
    }

    return $text;
}

function zib_get_time_spend($time, $unit = 'day')
{
    if (is_int($time)) {
        $time = intval($time);
    } else {
        $time = strtotime($time);
    }

    $current_time = intval(strtotime(current_time('mysql')));
    $t            = $current_time - $time; //时间差 （秒）

    switch ($unit) {
        case 'day':
        case 'days':
            return floor($t / 86400);
            break;
    }

}

/**
 * 导出数据为excel表格
 * @param
 * array $data  一个二维数组,结构如同从数据库查出来的数组
 * array $title  excel的第一行标题,一个数组,如果为空则没有标题
 * String $filename 下载的文件名
 */
function zib_export_excel($data = array(), $title = array(), $filename = 'export_excel')
{
    header('Content-type:application/octet-stream');
    header('Accept-Ranges:bytes');
    header('Content-type:application/vnd.ms-excel');
    header('Content-Disposition:attachment;filename=' . $filename . '.xls');
    header('Pragma: no-cache');
    header('Expires: 0');

    //导出xls 开始
    if (!empty($title)) {
        foreach ($title as $k => $v) {
            $title[$k] = iconv('UTF-8', 'GB2312', $v);
        }
        $title = implode("\t", $title);
        echo "$title\n";
    }

    if (!empty($data)) {
        $_data = array();
        foreach ($data as $val) {
            $val = (array) $val;
            foreach ($val as $ck => $cv) {
                $val[$ck] = mb_convert_encoding($cv, 'GB2312', 'UTF-8');
            }
            $_data[] = implode("\t", $val);
        }
        echo implode("\n", $_data);
    }

    exit;
}

function zib_m_pc_is_show($opt)
{

    static $is_mobile = 'null';
    if ($is_mobile === 'null') {
        $is_mobile = wp_is_mobile();
    }

    $opt = (array) $opt;

    return (($is_mobile && in_array('m_s', $opt)) || (!$is_mobile && in_array('pc_s', $opt)));
}

/**
 * @description: 获取用户的ip地址
 * @param {*}
 * @return {*}
 */
function zib_get_remote_ip_addr()
{
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
}

function zib_get_geographical_position_by_qq($ip, $key, $Secret_key, $debug = false)
{

    //通过腾讯接口获取
    $api_url = 'http://apis.map.qq.com/ws/location/v1/ip?ip=' . $ip . '&key=' . $key;
    //签名校验
    if ($Secret_key) {
        $api_url .= '&sig=' . md5('/ws/location/v1/ip?ip=' . $ip . '&key=' . $key . $Secret_key);
    }

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->timeout(3000)->get($api_url);
    $body     = $response->json(true);

    if (empty($body['result']['ad_info'])) {
        if ($debug) {
            if (!empty($body['message']) && $body['message'] == '签名验证失败') {
                $body['message'] = '签名校验Secret key填写错误';
            }
            return $body;
        }
        return false;
    }

    $body_data = $body['result']['ad_info'];

    $data = array(
        'ip'       => $ip,
        'nation'   => !empty($body_data['nation']) ? $body_data['nation'] : '',
        'province' => !empty($body_data['province']) ? $body_data['province'] : '',
        'city'     => !empty($body_data['city']) ? $body_data['city'] : '',
        'district' => !empty($body_data['district']) ? $body_data['district'] : '',
        'sdk'      => 'qq',
    );

    return $data;
}

function zib_get_geographical_position_by_amap($ip, $key, $Secret_key, $debug = false)
{
    //通过高德接口获取
    $api_url = 'http://restapi.amap.com/v3/ip?ip=' . $ip . '&key=' . $key;

    if ($Secret_key) {
        $api_url .= '&sig=' . md5('ip=' . $ip . '&key=' . $key . $Secret_key);
    }

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->timeout(3000)->get($api_url);
    $body     = $response->json(true);

    if (!isset($body['status']) || $body['status'] != 1) {
        if ($debug) {
            if (!empty($body['info']) && $body['info'] == 'INVALID_USER_SIGNATURE') {
                $body = ['message' => '数据签名秘钥填写错误', 'status' => 0];
            }
            return $body;
        }
        return false;
    }

    $body_data = $body;

    $data = array(
        'ip'       => $ip,
        'nation'   => !empty($body_data['nation']) ? $body_data['nation'] : '',
        'province' => !empty($body_data['province']) ? $body_data['province'] : '',
        'city'     => !empty($body_data['city']) ? $body_data['city'] : '',
        'district' => !empty($body_data['district']) ? $body_data['district'] : '',
        'sdk'      => 'amap',
    );

    return $data;
}

function zib_get_geographical_position_by_pconline($ip, $debug = false)
{

    //通过太平洋接口获取
    $api_url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=' . $ip;

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->timeout(3000)->get($api_url);
    $body     = $response->body('GB2312');

    if (!$body) {
        if ($debug) {
            return $body;
        }
        return false;
    }

    preg_match('/"pro":"(.*?)"/', $body, $pro);
    preg_match('/"city":"(.*?)"/', $body, $city);
    preg_match('/"region":"(.*?)"/', $body, $region);
    preg_match('/"addr":"(.*?)(\s)(.*?)"/', $body, $addr);

    $data = array(
        'ip'       => $ip,
        'nation'   => !empty($addr[1]) ? $addr[1] : '',
        'province' => !empty($pro[1]) ? $pro[1] : '',
        'city'     => !empty($city[1]) ? $city[1] : '',
        'district' => !empty($region[1]) ? $region[1] : '',
        'sdk'      => 'pconline',
    );

    return $data;
}

/**
 * 通过文件加锁实现堵塞进程，防止并发写入
 * 参看文档：https://juejin.cn/post/6914099548223143944
 */
function zib_lock_file($file_name)
{
    $temp_dir  = ZIB_TEMP_DIR;
    $lock_file = $temp_dir . '/' . $file_name . '.lock';

    $lockHandle = fopen($lock_file, 'w'); //创建并打开加锁文件
    if ($lockHandle && flock($lockHandle, LOCK_EX)) {
        return $lockHandle;
    } else {
        // 获取文件锁失败，抛出错误
        throw new Exception('文件夹"' . $temp_dir . '/"写入错误, 请检查文件权限及所有者！');
    }

    return false;
}

function zib_unlock_file($lockHandle)
{
    // 释放文件锁
    flock($lockHandle, LOCK_UN);
    // 关闭文件句柄
    if (is_resource($lockHandle)) {
        fclose($lockHandle);
    }
    return true;
}

/**
 * @description: 函数运行节流，防止函数被频繁调用
 * @param {*} $time 间隔时间，单位秒
 * @param {*} $args 传入的参数，用于判断是否是同一个请求
 * @return {*} true:通过检测，也就是没有超过频率限制，false:超过频率限制
 */
function zib_fun_throttle($time, ...$args)
{

    $max_time  = 60; //最大时间，默认60秒
    $temp_dir  = ZIB_TEMP_DIR;
    $lock_file = $temp_dir . '/FunThrottleData.lock';
    $data_file = $temp_dir . '/FunThrottleData.json';
    $key       = md5(implode('', $args));

    // 如果临时目录不存在，则创建它
    if (!@is_dir($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }

    // 尝试获取文件锁
    $lockHandle = fopen($lock_file, 'w'); //创建并打开加锁文件
    if ($lockHandle && flock($lockHandle, LOCK_EX)) {
        // 获取文件锁成功，处理正常的业务逻辑
        //读取文件
        $file_fread    = file_exists($data_file) ? @file_get_contents($data_file) : '{}';
        $file_fread    = false === $file_fread ? '{}' : $file_fread;
        $file_data     = json_decode($file_fread, true);
        $now_strtotime = strtotime(current_time('Y-m-d H:i:s'));

        //清理超过最大时间的数据，避免数据过大+
        if ($file_data && is_array($file_data)) {
            if (!empty($file_data['last_time']) && $now_strtotime > strtotime("+$max_time seconds", $file_data['last_time'])) {
                $file_data = array();
            } else {
                foreach ($file_data as $k => $v) {
                    if ($now_strtotime > strtotime("+$max_time seconds", $v)) {
                        unset($file_data[$k]);
                    }
                }
            }
        } else {
            $file_data = array();
        }

        //判断是否超过限制时间
        if (!empty($file_data[$key]) && $now_strtotime <= strtotime("+$time seconds", $file_data[$key])) {

            // 释放文件锁
            flock($lockHandle, LOCK_UN);
            // 关闭文件句柄
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }

            return false;
        }

        //通过检测，也就是没有超过频率限制
        //记录新的请求时间
        $file_data[$key]        = $now_strtotime;
        $file_data['last_time'] = $now_strtotime;

        //写入文件
        try {
            // 将 PHP Warning（如权限不足导致的写入失败）转为异常，确保可被 catch 捕获
            set_error_handler(function ($severity, $message, $file, $line) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            });

            $write_result = file_put_contents($data_file, json_encode($file_data));
            if (false === $write_result) {
                throw new ErrorException('写入文件失败');
            }
        } catch (Throwable $e) {
            throw new Exception('文件夹"' . $temp_dir . '/"写入错误, 请检查文件权限及所有者！', 0, $e);
        } finally {
            restore_error_handler();
        }

        // 释放文件锁
        flock($lockHandle, LOCK_UN);
        // 关闭文件句柄
        if (is_resource($lockHandle)) {
            fclose($lockHandle);
        }

        return true;
    } else {
        // 获取文件锁失败，抛出错误
        throw new Exception('文件夹"' . $temp_dir . '/"写入错误, 请检查文件权限及所有者！');
    }
}

/**
 * @description:  获取时间查询的sql
 * @param {*} $time_type 查询类型，默认today，可选：
 *                       today:今天
 *                       yesterday:昨天
 *                       yester:昨天
 *                       thismonth:本月
 *                       lastmonth:上个月
 * thisyear：本年
 * all:全部
 * @param {*} $table_column 表字段，默认空
 * @return {*}
 */
function zib_get_time_where_sql($time_type = 'today', $table_column = '')
{

    $table_column = $table_column ? "`$table_column` " : '';

    static $this_data = null;
    if (isset($this_data[$time_type])) {
        return $table_column . $this_data[$time_type];
    }

    switch ($time_type) {
        case 'today':
            $todaytime  = current_time('Y-m-d');
            $time_where = "BETWEEN '$todaytime 00:00:00' AND '$todaytime 23:59:59'";
            break;

        case 'yesterday':
        case 'yester':
            $todaytime      = current_time('Y-m-d');
            $yesterday_time = date('Y-m-d', strtotime("$todaytime -1 day"));
            $time_where     = "BETWEEN '$yesterday_time 00:00:00' AND '$yesterday_time 23:59:59'";
            break;

        case 'thismonth': //本月
            $thismonth_time = current_time('Y-m');
            $current_time   = current_time('Y-m-d');
            $time_where     = "BETWEEN '$thismonth_time-01 00:00:00' AND '$current_time 23:59:59'";
            break;

        case 'lastmonth': //上个月
            $thismonth_time       = current_time('Y-m');
            $lastmonth_time_start = date('Y-m', strtotime("$thismonth_time -1 month"));
            $lastmonth_time_stop  = date('Y-m-d H:i:s', strtotime("$thismonth_time -1 seconds"));
            $time_where           = "BETWEEN '$lastmonth_time_start-01 00:00:00' AND '$lastmonth_time_stop'";
            break;

        case 'thisyear': //今年
            $thisyear_time = current_time('Y');
            $current_time  = current_time('Y-m-d');
            $time_where    = "BETWEEN '$thisyear_time-01-01 00:00:00' AND '$current_time 23:59:59'";
            break;

        case 'all': //所有
            $time_where = 'IS NOT NULL';
            break;

        default:
            $todaytime  = current_time('Y-m-d');
            $time_where = "BETWEEN '$todaytime 00:00:00' AND '$todaytime 23:59:59'";
    }

    $this_data[$time_type] = $time_where;
    return $table_column . $time_where;
}

/**
 * 设置数组值，支持.分割的键，会依次按层级设置
 * @param array|object $array 数组或对象
 * @param string $key 键 支持.分割的键
 * @param mixed $value 要设置的值
 * @return array 设置后的数组
 */
function zib_set_array_value($array, $key, $value)
{
    if (is_object($array)) {
        $array = (array) $array;
    }

    if (!is_array($array)) {
        $array = [];
    }

    if (!$key) {
        return $value;
    }

    if (!strpos($key, '.')) {
        //如果key不包含.，则直接设置对应的值
        $array[$key] = $value;
        return $array;
    }

    //用.分割key，递归设置值
    $keys        = explode('.', $key);
    $current_key = $keys[0];

    if (!isset($array[$current_key]) || !is_array($array[$current_key])) {
        $array[$current_key] = [];
    }

    $array[$current_key] = zib_set_array_value(
        $array[$current_key],
        implode('.', array_slice($keys, 1)),
        $value
    );

    return $array;
}

/**
 * 获取数组值，支持.分割的，会依次按层级获取
 * @param array $array 数组
 * @param string $key 键 支持.分割的键
 * @param mixed $default 默认值
 * @return mixed 值
 */
function zib_get_array_value($array, $key = null, $default = '')
{
    if (is_object($array)) {
        $array = (array) $array;
    }

    if (!$key) {
        return $array;
    }

    if (!is_array($array)) {
        return $default;
    }

    if (!strpos($key, '.')) {
        //如果key不包含.，则直接返回对应的值
        return $array[$key] ?? $default;
    }

    //用.分割key，通过isset获取对应的值
    $keys  = explode('.', $key);
    $value = $array;

    foreach ($keys as $key) {
        if (isset($value[$key])) {
            $value = $value[$key];
        } else {
            return $default;
        }
    }

    return $value;
}

/**
 * @description: 数据库where构建
 * @param {*} $where 支持数组，多维数组
 * @return {*}
 */
function zib_db_where_build($where)
{
    $conditions = array();
    $values     = array();

    // 如果是字符串，直接返回
    if (!is_array($where)) {

        if (is_numeric($where)) {
            return array(
                'conditions' => '`id` = %d',
                'values'     => array($where),
            );
        }

        return array(
            'conditions' => $where,
            'values'     => array(),
        );
    }

    // 格式化数组为SQL条件
    foreach ($where as $field => $value) {
        // 处理NULL值
        if (is_null($value)) {
            $conditions[] = "`$field` IS NULL";
            continue;
        }

        // 处理数组值（IN查询）
        if (is_array($value)) {
            $placeholders = array();
            foreach ($value as $item) {
                $placeholders[] = is_numeric($item) ? '%d' : '%s';
                $values[]       = $item;
            }
            $placeholders_str = implode(',', $placeholders);
            $conditions[]     = "`$field` IN ($placeholders_str)";
            continue;
        }

        // 处理特殊操作符（如 >, <, >=, <=, <>, LIKE）
        if (is_string($value) && stristr($value, '|')) {
            $parts        = explode('|', $value);
            $operator     = $parts[0];
            $val          = $parts[1];
            $format       = is_numeric($val) ? '%d' : '%s';
            $conditions[] = "`$field` $operator $format";
            $values[]     = $val;
            continue;
        }

        // 处理普通等于条件
        $format       = is_numeric($value) ? '%d' : '%s';
        $conditions[] = "`$field` = $format";
        $values[]     = $value;
    }

    // 拼接条件
    $conditions_str = implode(' AND ', $conditions);

    return array(
        'conditions' => $conditions_str,
        'values'     => $values,
    );
}
