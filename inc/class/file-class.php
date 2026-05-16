<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-11 11:35:21
 * @LastEditTime: 2023-11-18 21:13:59
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|文件管理类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class ZibFile
{

    //文件或者目录是否存在，区分文件大小写
    public static function file_exists_case($fileName)
    {
        if (file_exists($fileName) === false) {
            return false;
        }
        $status        = false;
        $directoryName = dirname($fileName);
        $fileArray     = glob($directoryName . '/*', GLOB_NOSORT);
        if (preg_match("/\\\|\//", $fileName)) {
            $array    = preg_split("/\\\|\//", $fileName);
            $fileName = $array[count($array) - 1];
        }
        foreach ($fileArray as $file) {
            if (preg_match("/{$fileName}/i", $file)) {
                $output = "{$directoryName}/{$fileName}";
                $status = true;
                break;
            }
        }
        return $status;
    }

    public static function path_readable($path)
    {
        $result = intval(is_readable($path));
        if ($result) {
            return $result;
        }
        $mode = self::get_mode($path);
        if (
            $mode &&
            strlen($mode) == 18 &&
            substr($mode, -9, 1) == 'r'
        ) { // -rwx rwx rwx(0777)
            return true;
        }
        return false;
    }
    public static function path_writeable($path)
    {
        $result = intval(is_writeable($path));
        if ($result) {
            return $result;
        }
        $mode = self::get_mode($path);
        if (
            $mode &&
            strlen($mode) == 18 &&
            substr($mode, -8, 1) == 'w'
        ) { // -rwx rwx rwx (0777)
            return true;
        }
        return false;
    }

    /**
     * @brief 读取文件/目录信息
     * @param string $file 文件名称
     * @return array 文件/目录信息
     */
    public static function getFileInfo($file)
    {
        if (!is_file($file) && !is_dir($file)) {
            return false;
        }

        $fileInfo               = array();
        $fileInfo['name']       = basename($file); //文件名
        $fileInfo['type']       = self::getFileType($file); //文件类型
        $fileInfo['dir']        = dirname($file); //目录名
        $fileInfo['size']       = is_dir($file) ? self::getDirSize($file) : self::get_filesize($file); //文件大小
        $fileInfo['ctime']      = filectime($file); //文件inode的修改时间
        $fileInfo['atime']      = fileatime($file); //上次访问时间
        $fileInfo['mtime']      = filemtime($file); //修改时间
        $fileInfo['group']      = filegroup($file); //文件的组
        $fileInfo['owner']      = fileowner($file); //文件所有者
        $fileInfo['perms']      = fileperms($file); //文件权限
        $fileInfo['readable']   = is_readable($file); //是否可读
        $fileInfo['writeable']  = is_writeable($file); //是否可写
        $fileInfo['executable'] = is_executable($file); //是否可执行
        $fileInfo['realpath']   = realpath($file); //绝对路径

        return $fileInfo;
    }

    /**
     * @brief 移动文件/目录下的文件（不包含当前文件夹）
     * @param string $resource 源文件/目录
     * @param string $target 目标文件/目录
     * @return bool 移动成功返回true 失败返回false
     */
    public static function move($resource, $target = null)
    {
        if (is_file($resource)) {
            return rename($resource, $target);
        } else if (is_dir($resource)) {
            is_dir($target) || self::mk_dir($target);
            $files = scandir($resource);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    self::move("$resource/$file", "$target/$file");
                }
            }
            @rmdir($resource);
        } else {
            return false;
        }
    }

    /**
     * @brief 读取文件类型(如果加载了fileinfo库 优先使用库函数)
     * @param string $fileName 文件名称
     * @return string 文件类型
     */
    public static function getFileType($fileName)
    {
        if (get_extension_funcs('fileinfo')) {
            $type = is_file($fileName) ? self::parseMimeType(self::getMimeType($fileName)) : false;

            $type = is_dir($fileName) ? 'dir' : '';

            return $type ? $type : self::getFileExt($fileName);
        } else {
            return self::getFileTypeByHead($fileName);
        }
    }

    /**
     * @brief 通过解析文件头信息获取文件类型
     * @param string $fileName 文件名称
     * @return string 文件类型
     */
    public static function getFileTypeByHead($fileName)
    {
        $fileRes = is_file($fileName) ? fopen($fileName, 'rb') : '';
        if (!$fileRes) {
            return false;
        }

        $bin = fread($fileRes, 15);
        fclose($fileRes);

        if ($bin) {
            foreach (self::getTypeList() as $key => $val) {
                $blen = strlen(pack('H*', $key)); //定义的文件头标记字节数
                $tbin = substr($bin, 0, intval($blen)); //需要比较的文件头长度

                if (strtolower($key) == strtolower(array_shift(unpack('H*', $tbin)))) {
                    return $val;
                }
            }
        }

        return 'unknown';
    }

    /**
     * @brief 文件头与文件类型映射表
     * @return array 映射列表
     */
    public static function getTypeList()
    {
        return array(
            "FFD8FFE1"                 => "jpg",
            "89504E47"                 => "png",
            "47494638"                 => "gif",
            "49492A00"                 => "tif",
            "424D"                     => "bmp",
            "41433130"                 => "dwg",
            "38425053"                 => "psd",
            "7B5C727466"               => "rtf",
            "3C3F786D6C"               => "xml",
            "68746D6C3E"               => "html",
            "44656C69766572792D646174" => "eml",
            "CFAD12FEC5FD746F"         => "dbx",
            "2142444E"                 => "pst",
            "D0CF11E0"                 => "xls/doc",
            "5374616E64617264204A"     => "mdb",
            "FF575043"                 => "wpd",
            "252150532D41646F6265"     => "eps/ps",
            "255044462D312E"           => "pdf",
            "E3828596"                 => "pwl",
            "504B0304"                 => "zip",
            "52617221"                 => "rar",
            "57415645"                 => "wav",
            "41564920"                 => "avi",
            "2E7261FD"                 => "ram",
            "2E524D46"                 => "rm",
            "000001BA"                 => "mpg",
            "000001B3"                 => "mpg",
            "6D6F6F76"                 => "mov",
            "3026B2758E66CF11"         => "asf",
            "4D546864"                 => "mid",
        );
    }

    /**
     * @description: 获取文件MD5
     * @param {*}
     * @return {*}
     */
    public static function md6($file)
    {
        if (!is_file($file)) {
            return false;
        }

        return md5_file($file);
    }

    /**
     * @description: 效验文件MD5
     * @param {*}
     * @return {*}
     */
    public static function verify_md6($file, $expected)
    {
        if (!is_file($file)) {
            return false;
        }

        $md = self::md6($file);
        if (32 == strlen($expected)) {
            $expected_raw = $expected;
        } else {
            $expected_raw = base64_decode($expected);
        }
        return $md == $expected_raw ? true : false;
    }

    /**
     * @description: 追加写入文件内容
     * @param {*}
     * @return {*}
     */
    public static function append($file, $content)
    {
        if (!$content || !is_file($file)) {
            return false;
        }

        file_put_contents($file, $content, FILE_APPEND);
    }

    /**
     * @description 随机获取文件
     * @param string $path 文件路径
     * @param string $ext  文件格式
     * @param int $max 最大数量
     * @return array 文件
     */
    public static function rand_file($path = '', $ext = '', $max = 1)
    {
        if (!$path) {
            $path = self::get_path_father(dirname(__FILE__));
        }

        self::recursion_dir($path, $dirs, $files);
        $files_count = count($files);
        $max         = $max > $files_count ? $files_count : $max;
        $file_array  = array();
        for ($i = 0; $i < $files_count; $i++) {
            $number = mt_rand(0, $files_count - 1);
            $file   = $files[$number];
            if ($ext) {
                if ($ext == self::get_path_ext($file)) {
                    $file_array[] = $file;
                }
            } else {
                $file_array[] = $file;
            }
            if ($max && count($file_array) > $max) {
                break;
            }
        }
        return $file_array;
    }

    /**
     * @brief 读取文件的mime类型 - 需要 php_fileinfo.dll扩展库
     * @param string $fileName 文件名称
     * @return string 文件的mime类型
     */
    public static function getMimeType($fileName)
    {
        return is_file($fileName) ? mime_content_type(realpath($fileName)) : false;
    }

    /**
     * @brief mime类型和普通类型互相转换
     * @param string $type 类型
     * @return 返回转换后的类型
     */
    public static function parseMimeType($type)
    {
        if (empty($type)) {
            return false;
        }

        $mime_type = array(
            'txt'  => 'text/plain',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'php'  => 'text/x-php',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'js'   => 'text/x-c++',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'swf'  => 'application/x-shockwave-flash',
            'flv'  => 'video/x-flv',

            // images
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            'exe'  => 'application/x-msdownload',
            'msi'  => 'application/x-msdownload',
            'cab'  => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3'  => 'audio/mpeg',
            'qt'   => 'video/quicktime',
            'mov'  => 'video/quicktime',

            // adobe
            'pdf'  => 'application/pdf',
            'psd'  => 'image/vnd.adobe.photoshop',
            'ai'   => 'application/postscript',
            'eps'  => 'application/postscript',
            'ps'   => 'application/postscript',

            // ms office
            'doc'  => 'application/msword',
            'rtf'  => 'application/rtf',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',

            // open office
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $type_reverse = array_flip($mime_type);

        return in_array($type, array_keys($mime_type)) ? $mime_type[$type] : $type_reverse[$type];
    }

    /**
     * @brief 读取文件扩展名
     * @param string $file 文件名
     * @return string/bool 读取成功返回扩展名 否则返回false
     */
    public static function getFileExt($fileName)
    {
        $fileInfo = is_file($fileName) ? pathinfo($fileName) : null;

        return $fileInfo ? strtolower($fileInfo['extension']) : false;
    }

    /**
     * @brief 读取文件夹大小
     * @param string $dir 文件夹名称
     * @return int 文件夹大小
     */
    public static function getDirSize($dir)
    {
        $dirSize = 0;

        if ($handle = opendir($dir)) {
            while (false !== ($item = readdir($handle))) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                if (is_dir("$dir/$item")) {
                    $dirSize += self::getDirSize("$dir/$item");
                } else {
                    $dirSize += filesize("$dir/$item");
                }
            }
        }
        closedir($handle);

        return $dirSize;
    }

    /**
     * @brief 判断是否为空目录
     * @param string $dir 目录名称
     * @return bool 空目录返回true 非空返回false
     */
    public static function isDirEmpty($dir)
    {
        if ($handle = opendir($dir)) {
            while (false !== ($item = readdir($handle))) {
                if ($item == '.' || $item == '..') {
                    continue;
                } else {
                    return false;
                }
            }
        }
        closedir($handle);

        return true;
    }

    /**
     * 获取一个路径(文件夹&文件) 当前文件[夹]名
     * test/11/ ==>11 test/1.c  ==>1.c
     */
    public static function get_path_this($path)
    {
        $path = str_replace('\\', '/', rtrim($path, '/'));
        $pos  = strrpos($path, '/');
        if ($pos === false) {
            return $path;
        }
        return substr($path, $pos + 1);
    }
    /**
     * 获取一个路径(文件夹&文件) 父目录
     * /test/11/==>/test/   /test/1.c ==>/www/test/
     */
    public static function get_path_father($path)
    {
        $path = str_replace('\\', '/', rtrim($path, '/'));
        $pos  = strrpos($path, '/');
        if ($pos === false) {
            return $path;
        }
        return substr($path, 0, $pos + 1);
    }

    /**
     * 获取扩展名
     */
    public static function get_path_ext($path)
    {
        $name = self::get_path_this($path);
        $ext  = '';
        if (strstr($name, '.')) {
            $ext = substr($name, strrpos($name, '.') + 1);
            $ext = strtolower($ext);
        }
        if (strlen($ext) > 3 && preg_match("/([\x81-\xfe][\x40-\xfe])/", $ext, $match)) {
            $ext = '';
        }
        return htmlspecialchars($ext);
    }

    //自动获取不重复文件(夹)名
    //如果传入$file_add 则检测存在则自定重命名  a.txt 为a{$file_add}.txt
    //$same_file_type  rename,replace,skip,folder_rename
    public static function get_filename_auto($path, $file_add = "", $same_file_type = 'replace')
    {
        if (is_dir($path) && $same_file_type != 'folder_rename') { //文件夹则忽略
            return $path;
        }
        //重名处理
        if (file_exists($path)) {
            if ($same_file_type == 'replace') {
                return $path;
            } else if ($same_file_type == 'skip') {
                return false;
            }
        }

        $i      = 1;
        $father = self::get_path_father($path);
        $name   = self::get_path_this($path);
        $ext    = self::get_path_ext($name);
        if (is_dir($path)) {
            $ext = '';
        }
        if (strlen($ext) > 0) {
            $ext  = '.' . $ext;
            $name = substr($name, 0, strlen($name) - strlen($ext));
        }
        while (file_exists($path)) {
            if ($file_add != '') {
                $path = $father . $name . $file_add . $ext;
                $file_add .= '-';
            } else {
                $path = $father . $name . '(' . $i . ')' . $ext;
                $i++;
            }
        }
        return $path;
    }

    /**
     * 获取文件夹详细信息,文件夹属性时调用，包含子文件夹数量，文件数量，总大小
     */
    public static function path_info($path)
    {
        if (!file_exists($path)) {
            return false;
        }

        $pathinfo   = self::_path_info_more($path); //子目录文件大小统计信息
        $folderinfo = self::getFileInfo($path);
        return array_merge($pathinfo, $folderinfo);
    }

    /**
     * 检查名称是否合法
     */
    public static function path_check($path)
    {
        $check = array('/', '\\', ':', '*', '?', '"', '<', '>', '|');
        $path  = rtrim($path, '/');
        $path  = self::get_path_this($path);
        foreach ($check as $v) {
            if (strstr($path, $v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 递归获取文件夹信息： 子文件夹数量，文件数量，总大小
     */
    public static function _path_info_more($dir, &$fileCount = 0, &$pathCount = 0, &$size = 0)
    {
        if (!$dh = @opendir($dir)) {
            return array('fileCount' => 0, 'folderCount' => 0, 'size' => 0);
        }

        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                $fileCount++;
                $size += self::get_filesize($fullpath);
            } else {
                self::_path_info_more($fullpath, $fileCount, $pathCount, $size);
                $pathCount++;
            }
        }
        closedir($dh);
        $pathinfo['fileCount']   = $fileCount;
        $pathinfo['folderCount'] = $pathCount;
        $pathinfo['size']        = $size;
        return $pathinfo;
    }

    // 判断文件夹是否含有子内容【区分为文件或者只筛选文件夹才算】
    public static function path_haschildren($dir, $checkFile = false)
    {
        $dir = rtrim($dir, '/') . '/';
        if (!$dh = @opendir($dir)) {
            return false;
        }

        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullpath = $dir . $file;
            if ($checkFile) { //有子目录或者文件都说明有子内容
                if (@is_file($fullpath) || is_dir($fullpath . '/')) {
                    return true;
                }
            } else { //只检查有没有文件
                if (@is_dir($fullpath . '/')) { //解决部分主机报错问题
                    return true;
                }
            }
        }
        closedir($dh);
        return false;
    }

    /**
     * @brief 删除文件/目录
     * @param string $resource 目录/文件
     * @return bool 删除成功返回true 否则返回false
     */
    public static function unlink($resource)
    {
        if (is_file($resource)) {
            return self::del_file($resource);
        } else if (is_dir($resource)) {
            return self::del_dir($resource);
        }
    }

    /**
     * 删除单个文件
     */
    public static function del_file($fullpath)
    {
        if (!@unlink($fullpath)) { // 删除不了，尝试修改文件权限
            @chmod($fullpath, 0777);
            if (!@unlink($fullpath)) {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 删除文件夹 传入参数编码为操作系统编码. win--gbk
     */
    public static function del_dir($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return true;
        }

        if (!$dh = opendir($dir)) {
            return false;
        }

        @set_time_limit(0);
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullpath = $dir . '/' . $file;
            if (!is_dir($fullpath)) {
                if (@!unlink($fullpath)) { // 删除不了，尝试修改文件权限
                    @chmod($fullpath, 0777);
                    if (@!unlink($fullpath)) {
                        return false;
                    }
                }
            } else {
                if (!self::del_dir($fullpath)) {
                    chmod($fullpath, 0777);
                    if (!self::del_dir($fullpath)) {
                        return false;
                    }

                }
            }
        }
        closedir($dh);
        if (@rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 复制文件夹
     * eg:将D:/wwwroot/下面wordpress复制到
     *    D:/wwwroot/www/explorer/0000/del/1/
     * 末尾都不需要加斜杠，复制到地址如果不加源文件夹名，
     * 就会将wordpress下面文件复制到D:/wwwroot/www/explorer/0000/del/1/下面
     * $from = 'D:/wwwroot/wordpress';
     * $to = 'D:/wwwroot/www/explorer/0000/del/1/wordpress';
     */

    public static function copy($source, $dest)
    {
        if (!$dest) {
            return false;
        }

        if (is_dir($source) && $source == substr($dest, 0, strlen($source))) {
            return false;
        }
        //防止父文件夹拷贝到子文件夹，无限递归

        @set_time_limit(0);
        $result = true;
        if (is_file($source)) {
            if ($dest[strlen($dest) - 1] == '/') {
                $__dest = $dest . "/" . basename($source);
            } else {
                $__dest = $dest;
            }
            $result = @copy($source, $__dest);
            @chmod($__dest, 0777);
        } else if (is_dir($source)) {
            if ($dest[strlen($dest) - 1] == '/') {
                $dest = $dest . basename($source);
            }
            if (!is_dir($dest)) {
                @mkdir($dest, 0777);
            }
            if (!$dh = opendir($source)) {
                return false;
            }

            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $result = self::copy($source . "/" . $file, $dest . "/" . $file);
            }
            closedir($dh);
        }
        return $result;
    }

    /**
     * 移动文件&文件夹；（同名文件夹则特殊处理）
     * 问题：win下，挂载磁盘移动到系统盘时rmdir导致遍历不完全；
     */
    public static function move_path2($source, $dest, $repeat_add = '', $repeat_type = 'replace')
    {
        if (!$dest) {
            return false;
        }

        if (is_dir($source) && $source == substr($dest, 0, strlen($source))) {
            return false;
        }
        //防止父文件夹拷贝到子文件夹，无限递归
        @set_time_limit(0);
        if (is_file($source)) {
            return self::move_file($source, $dest, $repeat_add, $repeat_type);
        } else if (is_dir($source)) {
            if ($dest[strlen($dest) - 1] == '/') {
                $dest = $dest . basename($source);
            }
            if (!file_exists($dest)) {
                @mkdir($dest, 0777);
            }
            if (!$dh = opendir($source)) {
                return false;
            }

            while (($file = readdir($dh)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                self::move_path($source . "/" . $file, $dest . "/" . $file, $repeat_add, $repeat_type);
            }
            closedir($dh);
            return @rmdir($source);
        }
    }

    /**
     * @description: 仅移动文件
     * @param {*}
     * @return {*}
     */
    public static function move_file($source, $dest, $repeat_add, $repeat_type)
    {
        if ($dest[strlen($dest) - 1] == '/') {
            $dest = $dest . "/" . basename($source);
        }
        if (file_exists($dest)) {
            $dest = self::get_filename_auto($dest, $repeat_add, $repeat_type); //同名文件处理规则
        }
        $result = intval(@rename($source, $dest));
        if (!$result) { // windows部分ing情况处理
            $result = intval(@copy($source, $dest));
            if ($result) {
                @unlink($source);
            }
        }
        return $result;
    }

    /**
     * @description: 移动文件或文件夹
     * @param {*}
     * @return {*}
     */
    public static function move_path($source, $dest, $repeat_add = '', $repeat_type = 'replace')
    {
        if (!$dest || !file_exists($source)) {
            return false;
        }

        if (is_dir($source)) {
            //防止父文件夹拷贝到子文件夹，无限递归
            if ($source == substr($dest, 0, strlen($source))) {
                return false;
            }
            //地址相同
            if (rtrim($source, '/') == rtrim($dest, '/')) {
                return false;
            }
        }

        @set_time_limit(0);
        if (is_file($source)) {
            return self::move_file($source, $dest, $repeat_add, $repeat_type);
        }
        self::recursion_dir($source, $dirs, $files, -1, 0);

        @mkdir($dest);
        foreach ($dirs as $f) {
            $path = $dest . '/' . substr($f, strlen($source));
            if (!file_exists($path)) {
                self::mk_dir($path);
            }
        }
        $file_success = 0;
        foreach ($files as $f) {
            $path = $dest . '/' . substr($f, strlen($source));
            $file_success += self::move_file($f, $path, $repeat_add, $repeat_type);
        }
        foreach ($dirs as $f) {
            @rmdir($f);
        }
        @rmdir($source);
        if ($file_success == count($files)) {
            self::del_dir($source);
            return true;
        }
        return false;
    }

    /*
     * 获取文件&文件夹列表(支持文件夹层级)
     * path : 文件夹 $dir ——返回的文件夹array files ——返回的文件array
     * $deepest 是否完整递归；$deep 递归层级
     */
    public static function recursion_dir($path, &$dir, &$file, $deepest = -1, $deep = 0)
    {
        $path = rtrim($path, '/') . '/';
        if (!is_array($file)) {
            $file = array();
        }

        if (!is_array($dir)) {
            $dir = array();
        }

        if (!$dh = opendir($path)) {
            return false;
        }

        while (($val = readdir($dh)) !== false) {
            if ($val == '.' || $val == '..') {
                continue;
            }

            $value = strval($path . $val);
            if (is_file($value)) {
                $file[] = $value;
            } else if (is_dir($value)) {
                $dir[] = $value;
                if ($deepest == -1 || $deep < $deepest) {
                    self::recursion_dir($value . "/", $dir, $file, $deepest, $deep + 1);
                }
            }
        }
        closedir($dh);
        return true;
    }

    /**
     * @description: 获取当前文件夹的所有文件
     * @param {*}
     * @return {*}
     */
    public static function dir_list($path)
    {
        self::recursion_dir($path, $dirs, $files);
        return array_merge($dirs, $files);
    }

    // 安全读取文件，避免并发下读取数据为空
    public static function file_read_safe($file, $timeout = 3)
    {
        if (!$file || !file_exists($file)) {
            return false;
        }

        $fp = @fopen($file, 'r');
        if (!$fp) {
            return false;
        }

        $startTime = microtime(true);
        do {
            $locked = flock($fp, LOCK_SH); //LOCK_EX|LOCK_NB
            if (!$locked) {
                usleep(mt_rand(1, 50) * 1000); //1~50ms;
            }
        } while ((!$locked) && ((microtime(true) - $startTime) < $timeout)); //设置超时时间
        if ($locked && filesize($file) >= 0) {
            $result = @fread($fp, filesize($file));
            flock($fp, LOCK_UN);
            fclose($fp);
            if (filesize($file) == 0) {
                return '';
            }
            return $result;
        } else {
            flock($fp, LOCK_UN);
            fclose($fp);
            return false;
        }
    }

    // 安全读取文件，避免并发下读取数据为空
    public static function file_wirte_safe($file, $buffer, $timeout = 3)
    {
        clearstatcache();
        if (strlen($file) == 0 || !$file || !file_exists($file)) {
            return false;
        }

        $fp        = fopen($file, 'r+');
        $startTime = microtime(true);
        do {
            $locked = flock($fp, LOCK_EX); //LOCK_EX
            if (!$locked) {
                usleep(mt_rand(1, 50) * 1000); //1~50ms;
            }
        } while ((!$locked) && ((microtime(true) - $startTime) < $timeout)); //设置超时时间
        if ($locked) {
            $tempFile = $file . '.temp';
            $result   = file_put_contents($tempFile, $buffer, LOCK_EX); //验证是否还能写入；避免磁盘空间满的情况
            if (!$result || !file_exists($tempFile)) {
                flock($fp, LOCK_UN);
                fclose($fp);
                return false;
            }
            @unlink($tempFile);

            ftruncate($fp, 0);
            rewind($fp);
            $result = fwrite($fp, $buffer);
            flock($fp, LOCK_UN);
            fclose($fp);
            clearstatcache();
            return $result;
        } else {
            flock($fp, LOCK_UN);
            fclose($fp);
            return false;
        }
    }

    /*
     * 文件搜索
     * $search 为包含的字符串
     * is_content 表示是否搜索文件内容;默认不搜索
     * is_case  表示区分大小写,默认不区分
     * file_ext  限制文件类型
     */
    public static function path_search($path, $search, $is_content = false, $file_ext = '', $is_case = false)
    {
        $result               = array();
        $result['fileList']   = array();
        $result['folderList'] = array();
        if (!$path) {
            return $result;
        }

        $ext_arr = explode("|", $file_ext);
        self::recursion_dir($path, $dirs, $files, -1, 0);
        $strpos = 'stripos'; //是否区分大小写
        if ($is_case) {
            $strpos = 'strpos';
        }

        $result_num     = 0;
        $result_num_max = 2000; //搜索文件内容，限制最多匹配条数
        foreach ($files as $f) {
            if ($result_num >= $result_num_max) {
                $result['error_info'] = $result_num_max;
                break;
            }

            //若指定了扩展名则只在匹配扩展名文件中搜索
            $ext = self::get_path_ext($f);
            if ($file_ext != '' && !in_array($ext, $ext_arr)) {
                continue;
            }

            //搜索内容则不搜索文件名
            if ($is_content) {
                if (!self::is_text_file($ext)) {
                    continue;
                }
                //在限定中或者不在bin中
                $search_info = self::file_search($f, $search, $is_case);
                if ($search_info !== false) {
                    $result_num += count($search_info['searchInfo']);
                    $result['fileList'][] = $search_info;
                }
            } else {
                $path_this = self::get_path_this($f);
                if ($strpos($path_this, $search) !== false) { //搜索文件名;
                    $result['fileList'][] = self::getFileInfo($f);
                    $result_num++;
                }
            }
        }
        if (!$is_content && $file_ext == '') { //没有指定搜索文件内容，且没有限定扩展名，才搜索文件夹
            foreach ($dirs as $f) {
                $path_this = self::get_path_this($f);
                if ($strpos($path_this, $search) !== false) {
                    $result['folderList'][] = array(
                        'name' => self::get_path_this($f),
                        'path' => $f,
                    );
                }
            }
        }
        return $result;
    }

    /**
     * @description: 文件内搜索；返回行及关键词附近行|优化搜索算法 提高100被性能
     * @param mixed $path 文件路径
     * @param mixed $search 搜索内容
     * @param mixed $is_case 是否区分大小写
     * @return {*}
     */
    public static function file_search($path, $search, $is_case)
    {
        $strpos = 'stripos'; //是否区分大小写
        if ($is_case) {
            $strpos = 'strpos';
        }

        //文本文件 超过40M不再搜索
        if (@filesize($path) >= 1024 * 1024 * 40) {
            return false;
        }
        $content = file_get_contents($path);
        if ($strpos($content, "\0") > 0) { // 不是文本文档
            unset($content);
            return false;
        }

        //文件没有搜索到目标直接返回
        if ($strpos($content, $search) === false) {
            unset($content);
            return false;
        }

        $pose       = 0;
        $file_size  = strlen($content);
        $arr_search = array(); // 匹配结果所在位置
        while ($pose !== false) {
            $pose = $strpos($content, $search, $pose);
            if ($pose !== false) {
                $arr_search[] = $pose;
                $pose++;
            } else {
                break;
            }
        }

        $arr_line = array();
        $pose     = 0;
        while ($pose !== false) {
            $pose = strpos($content, "\n", $pose);
            if ($pose !== false) {
                $arr_line[] = $pose;
                $pose++;
            } else {
                break;
            }
        }
        $arr_line[] = $file_size; //文件只有一行而且没有换行，则构造虚拟换行
        $result     = array(); //  [2,10,22,45,60]  [20,30,40,50,55]
        $len_search = count($arr_search);
        $len_line   = count($arr_line);
        for ($i = 0, $line = 0; $i < $len_search && $line < $len_line; $line++) {
            while ($arr_search[$i] <= $arr_line[$line]) {
                //行截取字符串
                $cur_pose = $arr_search[$i];
                $from     = $line == 0 ? 0 : $arr_line[$line - 1];
                $to       = $arr_line[$line];
                $len_max  = 300;
                if ($to - $from >= $len_max) { //长度过长处理
                    $from = $cur_pose - 20;
                    $from = $from <= 0 ? 0 : $from;
                    $to   = $from + $len_max;
                    //中文避免截断；（向前 向后找到分隔符后终止）
                    $token = array("\r", "\n", " ", "\t", ",", "/", "#", "_", "[", "]", "(", ")", "+", "-", "*", "/", "=", "&");
                    while (!in_array($content[$from], $token) && $from >= 0) {
                        $from--;
                    }
                    while (!in_array($content[$to], $token) && $to <= $file_size) {
                        $to++;
                    }
                }
                $line_str = substr($content, $from, $to - $from);
                if ($strpos($line_str, $search) === false) { //截取乱码避免
                    $line_str = $search;
                }

                $result[] = array('line' => $line + 1, 'str' => $line_str);
                if (++$i >= $len_search) {
                    break;
                }
            }
        }

        $info               = self::getFileInfo($path);
        $info['searchInfo'] = $result;
        unset($content);
        return $info;
    }

    /**
     * 修改文件、文件夹权限
     * @param  $path 文件(夹)目录
     * @return :string
     */
    public static function chmod_path($path, $mod)
    {
        if (!isset($mod)) {
            $mod = 0777;
        }

        if (!file_exists($path)) {
            return false;
        }

        if (is_file($path)) {
            return @chmod($path, $mod);
        }

        if (!$dh = @opendir($path)) {
            return false;
        }

        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullpath = $path . '/' . $file;
            self::chmod_path($fullpath, $mod);
            @chmod($fullpath, $mod);
        }
        closedir($dh);
        return @chmod($path, $mod);
    }

    /**
     * 判断路径是不是绝对路径
     * 返回true('/foo/bar','c:\windows').
     *
     * @return 返回true则为绝对路径，否则为相对路径
     */
    public static function path_is_absolute($path)
    {
        if (realpath($path) == $path) // *nux 的绝对路径 /home/my
        {
            return true;
        }

        if (strlen($path) == 0 || $path[0] == '.') {
            return false;
        }

        if (preg_match('#^[a-zA-Z]:\\\\#', $path)) // windows 的绝对路径 c:\aaa\
        {
            return true;
        }

        return (bool) preg_match('#^[/\\\\]#', $path); //绝对路径 运行 / 和 \绝对路径，其他的则为相对路径
    }

    public static function is_text_file($ext)
    {
        $ext_arr = array(
            "txt", "textile", 'oexe', 'inc', 'csv', 'log', 'asc', 'tsv', 'lnk', 'url', 'webloc', 'meta', "localized",
            "xib", "xsd", "storyboard", "plist", "csproj", "pch", "pbxproj", "local", "xcscheme", "manifest", "vbproj",
            "strings", 'jshintrc', 'sublime-project', 'readme', 'changes', "changelog", 'version', 'license', 'changelog',

            "abap", "abc", "as", "asp", 'aspx', "ada", "adb", "htaccess", "htgroups", "htgroups",
            "htpasswd", "asciidoc", "adoc", "asm", "a", "ahk", "bat", "cmd", "cpp", "c", "cc", "cxx", "h", "hh", "hpp",
            "ino", "c9search_results", "cirru", "cr", "clj", "cljs", "cbl", "cob", "coffee", "cf", "cson", "cakefile",
            "cfm", "cs", "css", "curly", "d", "di", "dart", "diff", "patch", "dockerfile", "dot", "dummy", "dummy", "e",
            "ge", "ejs", "ex", "exs", "elm", "erl", "hrl", "frt", "fs", "ldr", "ftl", "gcode", "feature", ".gitignore",
            "glsl", "frag", "vert", "gbs", "go", "groovy", "haml", "hbs", "handlebars", "tpl", "mustache", "hs", "hx",
            "html", "hta", "htm", "xhtml", "eex", "html.eex", "erb", "rhtml", "html.erb", "ini", 'inf', "conf", "cfg", "prefs", "io",
            "jack", "jade", "java", "ji", "jl", "jq", "js", "jsm", "json", "jsp", "jsx", "latex", "ltx", "bib",
            "lean", "hlean", "less", "liquid", "lisp", "ls", "logic", "lql", "lsl", "lua", "lp", "lucene", "Makefile", "makemakefile",
            "gnumakefile", "makefile", "ocamlmakefile", "make", "md", "markdown", "mask", "matlab", "mz", "mel",
            "mc", "mush", "mysql", "nix", "nsi", "nsh", "m", "mm", "ml", "mli", "pas", "p", "pl", "pm", "pgsql", "php",
            "phtml", "shtml", "php3", "php4", "php5", "phps", "phpt", "aw", "ctp", "module", "ps1", "praat",
            "praatscript", "psc", "proc", "plg", "prolog", "properties", "proto", "py", "r", "cshtml", "rd",
            "rhtml", "rst", "rb", "ru", "gemspec", "rake", "guardfile", "rakefile", "gemfile", "rs", "sass",
            "scad", "scala", "scm", "sm", "rkt", "oak", "scheme", "scss", "sh", "bash", "bashrc", "sjs", "smarty",
            "tpl", "snippets", "soy", "space", "sql", "sqlserver", "styl", "stylus", "svg", "swift", "tcl", "tex",
            "toml", "twig", "swig", "ts", "typescript", "str", "vala", "vbs", "vb", "vm", "v", "vh",
            "sv", "svh", "vhd", "vhdl", "wlk", "wpgm", "wtest", "xml", "rdf", "rss", "wsdl", "xslt", "atom", "mathml",
            "mml", "xul", "xbl", "xaml", "xq", "yaml", "yml",

            "cer", "reg", "config",
        );
        if (in_array($ext, $ext_arr)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取文件(夹)权限 rwx_rwx_rwx
     */
    public static function get_mode($file)
    {
        $Mode    = @fileperms($file);
        $theMode = ' ' . decoct($Mode);
        $theMode = substr($theMode, -4);
        $Owner   = array();
        $Group   = array();
        $World   = array();
        if ($Mode & 0x1000) {
            $Type = 'p';
        }
        // FIFO pipe
        elseif ($Mode & 0x2000) {
            $Type = 'c';
        }
        // Character special
        elseif ($Mode & 0x4000) {
            $Type = 'd';
        }
        // Directory
        elseif ($Mode & 0x6000) {
            $Type = 'b';
        }
        // Block special
        elseif ($Mode & 0x8000) {
            $Type = '-';
        }
        // Regular
        elseif ($Mode & 0xA000) {
            $Type = 'l';
        }
        // Symbolic Link
        elseif ($Mode & 0xC000) {
            $Type = 's';
        }
        // Socket
        else {
            $Type = 'u';
        }
        // UNKNOWN
        // Determine les permissions par Groupe
        $Owner['r'] = ($Mode & 00400) ? 'r' : '-';
        $Owner['w'] = ($Mode & 00200) ? 'w' : '-';
        $Owner['x'] = ($Mode & 00100) ? 'x' : '-';
        $Group['r'] = ($Mode & 00040) ? 'r' : '-';
        $Group['w'] = ($Mode & 00020) ? 'w' : '-';
        $Group['e'] = ($Mode & 00010) ? 'x' : '-';
        $World['r'] = ($Mode & 00004) ? 'r' : '-';
        $World['w'] = ($Mode & 00002) ? 'w' : '-';
        $World['e'] = ($Mode & 00001) ? 'x' : '-';
        // Adjuste pour SUID, SGID et sticky bit
        if ($Mode & 0x800) {
            $Owner['e'] = ($Owner['e'] == 'x') ? 's' : 'S';
        }

        if ($Mode & 0x400) {
            $Group['e'] = ($Group['e'] == 'x') ? 's' : 'S';
        }

        if ($Mode & 0x200) {
            $World['e'] = ($World['e'] == 'x') ? 't' : 'T';
        }

        $Mode = $Type . $Owner['r'] . $Owner['w'] . $Owner['x'] . ' ' .
            $Group['r'] . $Group['w'] . $Group['e'] . ' ' .
            $World['r'] . $World['w'] . $World['e'];
        return $Mode . '(' . $theMode . ')';
    }

    /**
     * @创建目录 支持多重目录创建
     * @param string $dir 目录路径
     * @return 创建成功返回true 遇到错误返回false
     */
    public static function mk_dir($dir, $mode = 0777)
    {
        if (!$dir) {
            return false;
        }

        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }
        if (!self::mk_dir(dirname($dir), $mode)) {
            return false;
        }
        return @mkdir($dir, $mode);
    }

    /**
     * 文件大小格式化
     *
     * @param  $ :$bytes, int 文件大小
     * @param  $ :$precision int  保留小数点
     * @return :string
     */
    public static function size_format($bytes, $precision = 2)
    {
        if ($bytes == 0) {
            return "0 B";
        }

        $unit = array(
            'TB' => 1099511627776, // pow( 1024, 4)
            'GB' => 1073741824, // pow( 1024, 3)
            'MB' => 1048576, // pow( 1024, 2)
            'kB' => 1024, // pow( 1024, 1)
            'B ' => 1, // pow( 1024, 0)
        );
        foreach ($unit as $un => $mag) {
            if (doubleval($bytes) >= $mag) {
                return round($bytes / $mag, $precision) . ' ' . $un;
            }

        }
    }

    /**
     * 获取URL中的文件名
     * @param {*}
     * @return {*}
     */
    public static function url_filename($url)
    {
        // 获取URL中的文件名和参数部分
        $parts    = parse_url($url);
        $filename = $parts['path'];
        // 获取文件名中的最后一个/符号的位置
        $slash = strrpos($filename, '/');

        // 如果文件名中没有找到/符号,则返回空字符串
        if ($slash === false) {
            return '';
        }

        // 返回文件名中最后一个/符号之前的部分
        return substr($filename, $slash + 1);
    }
    /**
     * 获取一个链接中的文件后缀名
     * @param string $url 链接地址
     * @return string 文件后缀名
     */
    public static function url_extension($url)
    {

        // 获取URL中的文件名和参数部分
        $parts = parse_url($url);
        if (empty($parts['path'])) {
            return '';
        }
        $filename = $parts['path'];

        // 获取文件名中的最后一个.符号的位置
        $dot = strrpos($filename, '.');

        // 如果文件名中没有找到.符号,则返回空字符串
        if ($dot === false) {
            return '';
        }

        // 返回文件名中最后一个.符号之后的部分
        return substr($filename, $dot + 1);
    }

    /**
     * @description: 获取远程文件的大小size
     * @param {*}
     * @return {*}
     */
    public static function remote_filesize($url, $user = "", $pw = "")
    {
        ob_start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1); //请求头部
        curl_setopt($ch, CURLOPT_NOBODY, 1); //不请求内容
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //超时5秒
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (!empty($user) && !empty($pw)) {
            $headers = array('Authorization: Basic ' . base64_encode("$user:$pw"));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $ok = curl_exec($ch);
        curl_close($ch);
        $head = ob_get_contents();
        ob_end_clean();

        $regex = '/(content-length:|Content-Length:)\s([0-9].+?)\s/';
        $count = preg_match($regex, $head, $matches);

        $filesize = 0;
        if (empty($matches[2]) && class_exists('Yurun\Util\HttpRequest', false)) {
            ////使用YurunHttp获取文件大小
            $httpRequest = new Yurun\Util\HttpRequest;
            //超时6秒，限速50kb
            $filesize = $httpRequest->timeout(5000, 5000)->limitRate(500 * 1024, 500 * 1024)->head($url)->getHeaderLine('Content-Length');
        } else {
            $filesize = $matches[2];
        }
        return (int) $filesize;
    }

    /**
     * @description: 获取文件大小size|解决大于2G 大小问题
     * @param {*}$path 文件路径
     * @return {*}
     */
    public static function get_filesize($path)
    {
        $result = false;
        $fp     = fopen($path, "r");
        if (!$fp = fopen($path, "r")) {
            return $result;
        }

        if (PHP_INT_SIZE >= 8) { //64bit
            $result = (float) (abs(sprintf("%u", @filesize($path))));
        } else {
            if (fseek($fp, 0, SEEK_END) === 0) {
                $result = 0.0;
                $step   = 0x7FFFFFFF;
                while ($step > 0) {
                    if (fseek($fp, -$step, SEEK_CUR) === 0) {
                        $result += floatval($step);
                    } else {
                        $step >>= 1;
                    }
                }
            } else {
                static $iswin;
                if (!isset($iswin)) {
                    $iswin = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN');
                }
                if ($iswin && class_exists("COM")) {
                    try {
                        $fsobj = new COM('Scripting.FileSystemObject');
                        $f     = $fsobj->GetFile(realpath($path));
                        $size  = $f->Size;
                    } catch (Exception $e) {
                        $size = null;
                    }
                    if (is_numeric($size)) {
                        $result = $size;
                    }
                } else {
                    // 开源版避免调用 exec/stat 等系统命令；文件大小回退到 PHP 原生实现。
                    $result = filesize($path);
                }
            }
        }
        fclose($fp);
        return $result;
    }

    /**
     * @description: 下载远程文件到服务器，支持fopen的打开都可以；支持本地、url
     * @param $from 远程地址
     * @param $fileName 本地保存的地址+名称
     * @return {*}
     */
    public static function download($from, $fileName, $time_limit = 180, $temp_path = '')
    {
        //设置php函数最大执行时间为永久
        @set_time_limit($time_limit);
        $fileTemp = $fileName . '.downloading';
        $fileTemp = $fileName . '.downloading';
        if ($fp = @fopen($from, "rb")) {
            if (!$downloadFp = @fopen($fileTemp, "wb")) {
                return false;
            }
            while (!feof($fp)) {
                if (!file_exists($fileTemp)) { //删除目标文件；则终止下载
                    fclose($downloadFp);
                    return false;
                }
                //对于部分fp不结束的通过文件大小判断
                clearstatcache();
                fwrite($downloadFp, fread($fp, 1024 * 200), 1024 * 200);
            }
            //下载完成，重命名临时文件到目标文件
            fclose($downloadFp);
            fclose($fp);
            if (!@rename($fileTemp, $fileName)) {
                unlink($fileName);
                return rename($fileTemp, $fileName);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @description: 解压缩
     * @param {*}
     * @return {*}
     */
    public static function unzip($from, $path)
    {

        if (!file_exists($from)) {
            return false;
        }
        if (class_exists('ZipArchive', false)) {
            $zip = new ZipArchive();
            if (!$zip->open($from)) {
                return false;
            }
            if (method_exists($zip, 'extractTo')) {
                $result = $zip->extractTo($path);
                $zip->close();
                return $result;
            }
        }
        require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
        $zip = new PclZip($from);
        if (method_exists($zip, 'extractTo')) {
            if ($archive->extract(PCLZIP_OPT_PATH, $path) == 0) {
                return false;
            } else {
                return true;
            }
        }
        //WP文件解压缩
        global $wp_filesystem;
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        $unzip = unzip_file($from, $path);
        if ($unzip == true && !is_wp_error($result)) {
            return true;
        }
        return false;
    }
}
