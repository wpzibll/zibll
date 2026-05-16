<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2022-11-26 14:17:26
 * @LastEditTime : 2026-01-02 18:58:21
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|附件相关函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * 设置附件的附加帖子或文章
 */

function zib_media_attach_action($media, $parent_id)
{

    global $wpdb;
    $ids        = is_array($media) ? $media : array($media);
    $ids_string = implode(',', $ids);

    if (!$ids_string) {
        return;
    }

    $result = $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID IN ( $ids_string )", $parent_id));

    if (isset($result)) {
        foreach ($ids as $attachment_id) {
            $action = $parent_id == 0 ? 'detach' : 'attach';
            do_action('wp_media_attach_action', $action, $attachment_id, $parent_id);
        }
    }
}

function zib_new_post_media_attach_action($post)
{
    if (!is_object($post)) {
        $post = get_posts($post);
    }

    $post_content = $post->post_content;

    preg_match_all('/(data-edit-file-id|data-download-file)="(\d+)"/', $post_content, $matches);

    if (isset($matches[1][0])) {
        zib_media_attach_action($matches[2], $post->ID);
    }
}
add_action('new_edit_posts', 'zib_new_post_media_attach_action');
add_action('new_add_posts', 'zib_new_post_media_attach_action');
add_action('bbs_edit_posts', 'zib_new_post_media_attach_action');
add_action('bbs_add_posts', 'zib_new_post_media_attach_action');

//不显示附件页面
function zib_close_attachment_page()
{
    if (_pz('close_attachment_page')) {

        global $wp_query;
        if (is_attachment() && !is_super_admin()) {
            $wp_query->is_404 = (true);
        }
    }
}
add_action('template_redirect', 'zib_close_attachment_page');

// 上传文件自动重命名
function zib_new_filename($file)
{

    if (_pz('newfilename_type') !== 'random') {
        $file['name'] = current_time('YmdHis') . mt_rand(10, 99) . mt_rand(0, 9) . '-' . $file['name'];
    } else {
        $info         = pathinfo($file['name']);
        $ext          = empty($info['extension']) ? '' : '.' . $info['extension'];
        $md5          = md5($file['name']);
        $file['name'] = substr($md5, 0, 10) . current_time('YmdHis') . $ext;
    }

    return $file;
}
if (_pz('newfilename')) {
    add_filter('wp_handle_upload_prefilter', 'zib_new_filename', 99);
    add_filter('wp_handle_sideload_prefilter', 'zib_new_filename', 99);
}

/**
 * @description: 获取用户上传格式限制
 * @param {*} $user_id
 * @return {*}
 */
function zib_get_user_upload_mimes_limit($user_id = 0)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $options  = _pz('upload_file_mimes');
    $allow    = ''; //允许
    $prohibit = ''; //禁止

    if (isset($options[0])) {
        foreach ($options as $opt) {
            $type = $opt['type'];
            //只要登录就行
            if (zib_user_is_val_role($type, $user_id)) {
                if ($opt['pattern'] === 'prohibit') {
                    $prohibit .= ',' . $opt['val'];
                } else {
                    $allow .= ',' . $opt['val'];
                }
            }
        }
    }

    $allow    = ltrim(rtrim(trim($allow), ','), ',');
    $prohibit = ltrim(rtrim(trim($prohibit), ','), ',');

    $allow_array    = explode(',', $allow);
    $prohibit_array = explode(',', $prohibit);

    $mimes = array(
        'allow'                   => array(),
        'prohibit'                => array(),
        'allow_image_mime_to_ext' => array(),
    );

    foreach ($allow_array as $k) {
        $kv = explode('=', trim($k));

        if (!empty($kv[1])) {
            $kv[0] = trim($kv[0]);
            $kv[1] = trim($kv[1]);

            if ($kv[0] && $kv[1]) {
                $mimes['allow'][$kv[0]] = $kv[1];
            }

            if (strpos($kv[1], 'image/') !== false) {
                $mimes['allow_image_mime_to_ext'][$kv[1]] = $kv[0];
            }

        }
    }

    foreach ($prohibit_array as $k) {
        $kv = explode('=', trim($k));
        if (count($kv) == 2 && trim($kv[0]) && trim($kv[1])) {
            $mimes['prohibit'][trim($kv[0])] = trim($kv[1]);
        }
    }

    return $mimes;
}

//添加系统允许上传的文件类型
function zib_upload_mimes_filter($mimes)
{
    $upload_mimes_limit = zib_get_user_upload_mimes_limit();
    $mimes              = array_merge($mimes, $upload_mimes_limit['allow']);
    $mimes              = array_diff($mimes, $upload_mimes_limit['prohibit']);

    return $mimes;
}
add_filter('upload_mimes', 'zib_upload_mimes_filter');

//添加系统允许上传的文件类型-添加图片类型的mimes_to_exts
function zib_upload_image_mimes_to_exts_filter($mimes_to_exts)
{
    $upload_mimes_limit = zib_get_user_upload_mimes_limit();
    $mimes_to_exts      = array_merge($mimes_to_exts, $upload_mimes_limit['allow_image_mime_to_ext']);

    return $mimes_to_exts;
}
add_filter('getimagesize_mimes_to_exts', 'zib_upload_image_mimes_to_exts_filter');

//在文章编辑页面的[添加媒体]只显示用户自己上传的文件
function zib_upload_media($wp_query_obj)
{
    global $current_user, $pagenow;
    if (!is_a($current_user, 'WP_User')) {
        return;
    }

    if ('admin-ajax.php' != $pagenow || !isset($_REQUEST['action']) || 'query-attachments' != $_REQUEST['action']) {
        return;
    }

    if (!current_user_can('manage_options') && !current_user_can('manage_media_library')) {
        $wp_query_obj->set('author', $current_user->ID);
    }

    return;
}
add_action('pre_get_posts', 'zib_upload_media');

//在[媒体库]只显示用户上传的文件
function zib_media_library($wp_query)
{
    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/upload.php') !== false) {
        if (!current_user_can('manage_options') && !current_user_can('manage_media_library')) {
            global $current_user;
            $wp_query->set('author', $current_user->id);
        }
    }
}
add_filter('parse_query', 'zib_media_library');

/**
 * @description: 获取文章前台编辑：设置特色图像、视频、幻灯片的编辑框
 * @param {*} $post_id
 * @param {*} $class
 * @return {*}
 */
function zib_get_post_featured_edit_box($post_id, $class = 'mb20', $options = array())
{
    if (!get_current_user_id()) {
        return;
    }

    $can_video = apply_filters('featured_video_edit', false, $post_id);
    $can_slide = apply_filters('featured_slide_edit', false, $post_id);
    $can_image = apply_filters('featured_image_edit', false, $post_id);
    $args      = array();

    if (!$can_video && !$can_slide && !$can_image) {
        return;
    }

    if ($can_video && !$args && $post_id) {
        $video = zib_get_post_meta($post_id, 'featured_video', true);
        if ($video) {
            $pic_url = zib_get_post_meta($post_id, 'cover_image', true);

            $args['type'] = 'video';
            $args['data'] = array(
                'url' => $video,
                'pic' => $pic_url,
            );
        }
    }

    if ($can_slide && !$args && $post_id) {
        $slides_imgs = explode(',', zib_get_post_meta($post_id, 'featured_slide', true));
        if (!empty($slides_imgs[0])) {
            $slides = array();
            foreach ($slides_imgs as $slides_img) {
                $background = zib_get_attachment_image_src((int) $slides_img, 'full');
                if (isset($background[0])) {
                    $slides[] = array(
                        'url' => $background[0],
                        'id'  => (int) $slides_img,
                    );
                }
            }
            if (isset($slides[0])) {
                $args['type'] = 'slide';
                $args['data'] = $slides;
            }
        }
    }

    if ($can_image && !$args && $post_id) {
        $img_url = zib_get_post_meta($post_id, 'cover_image', true) ?: zib_get_post_meta($post_id, 'thumbnail_url', true);
        if ($img_url) {
            $args['type'] = 'image';
            $args['data'] = array(
                'url' => $img_url,
            );
        }
    }

    $options['video'] = $can_video;
    $options['slide'] = $can_slide;
    $args['options']  = $options;

    return '<div class="' . $class . ' featured-edit" featured-args=\'' . json_encode($args) . '\'><div class="btns-full flex jc"></div></div>';
}

/**
 * @description: 上传图片函数
 * @param {*} $file
 * @param {*} $post_id
 * @param {*} $ajax_audit
 * @param {*} $msg_prefix
 * @return {*}
 */
function zib_php_upload($file = 'file', $post_id = 0, $ajax_audit = 'auto', $msg_prefix = '', $is_split_upload = false)
{
    if (empty($_FILES)) {
        return array('error' => 1, '_FILES' => '', 'msg' => '上传信息错误，请重新选择文件');
    }

    if ($_FILES) {
        $overrides = array('test_form' => false);
        if ($is_split_upload) {
            $overrides['action'] = 'wp_handle_sideload';
        }

        require_once ABSPATH . 'wp-admin' . '/includes/image.php';
        require_once ABSPATH . 'wp-admin' . '/includes/file.php';
        require_once ABSPATH . 'wp-admin' . '/includes/media.php';

        if ('auto' == $ajax_audit) {
            $ajax_audit = _pz('audit_upload_img', false);
        }

        //图片api审核
        if ($ajax_audit && stristr($_FILES[$file]['type'], 'image')) {
            ZibAudit::ajax_image($file, $msg_prefix);
        }

        $attach_id = media_handle_upload($file, $post_id, [], $overrides);

        if (is_wp_error($attach_id)) {
            return array('error' => 1, '_FILES' => $_FILES, 'msg' => $attach_id->get_error_message());
        }

        $attach_id_s    = array();
        $_file_count_id = $file . '_file_count';
        if (!empty($_POST[$_file_count_id]) && $_POST[$_file_count_id] > 1) {
            for ($x = 1; $x < $_POST[$_file_count_id]; $x++) {
                $file_id = $file . '_' . $x;
                if (!empty($_FILES[$file_id])) {

                    //图片api审核
                    if ($ajax_audit && stristr($_FILES[$file_id]['type'], 'image')) {
                        ZibAudit::ajax_image($file_id, $msg_prefix);
                    }

                    $attach_id_x = media_handle_upload($file_id, $post_id, [], $overrides);
                    if (!is_wp_error($attach_id_x)) {
                        $attach_id_s[] = $attach_id_x;
                    }
                }
            }
        }

        if ($attach_id_s) {
            array_unshift($attach_id_s, $attach_id);
            return $attach_id_s;
        } else {
            return $attach_id;
        }
    }
}

//js获取文件数据
function zib_prepare_attachment_for_js($attachment)
{

    $attachment_data = wp_prepare_attachment_for_js($attachment);

    if ($attachment_data['type'] === 'image') {
        $attachment_data['large_url']     = !empty($attachment_data['sizes']['large']['url']) ? $attachment_data['sizes']['large']['url'] : $attachment_data['url'];
        $attachment_data['medium_url']    = !empty($attachment_data['sizes']['medium']['url']) ? $attachment_data['sizes']['medium']['url'] : $attachment_data['large_url'];
        $attachment_data['thumbnail_url'] = !empty($attachment_data['sizes']['thumbnail']['url']) ? $attachment_data['sizes']['thumbnail']['url'] : $attachment_data['medium_url'];
    }

    foreach (array('authorLink', 'editLink', 'icon', 'link', 'nonces') as $k) {
        if (isset($attachment_data[$k])) {
            unset($attachment_data[$k]);
        }
    }

    return $attachment_data;
}

/**
 * 本地文件限速下载
 * @param $file_path 文件路径
 * @param $file_name 文件名
 * @param $speed 速度 b/s
 * @return {*}
 */
function zib_download_local_file($file_path, $file_name = null, $speed = 0)
{

    if (!$file_path || stripos($file_path, ABSPATH) !== 0 || !file_exists($file_path)) {
        return false;
    }

    $fileSize            = filesize($file_path);
    $file_local_filename = $file_name ?: end(explode('/', $file_path));

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: public');
    header('Content-Description: File Transfer');
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_local_filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($file_path));

    //如果文件3秒能下载完成，则不用分段读取，直接输出文件
    if (!$speed || $fileSize < $speed * 3) {
        @ob_end_clean(); //清空缓冲区
        readfile($file_path);
        exit;
    }

    //设置文件最长执行时间
    set_time_limit(0);
    $buffer      = $speed; // 每次读取
    $bufferCount = 0;
    $fp          = fopen($file_path, 'rb'); //二进制方式打开文件
    fseek($fp, 0); //指针跳到文件开头
    ob_start(); //打开输出缓冲区，相当于开启了一个新的PHP程序，但是并不会输出任何内容
    ob_implicit_flush(); //强制每当有输出的时候,即刻把输出发送到浏览器

    //循环读取文件数据
    while (!feof($fp) && $fileSize - $bufferCount > 0) {
        $data = fread($fp, $buffer);
        $bufferCount += $buffer;
        echo $data; //输出文件
        ob_flush(); //把输出缓冲区的内容发送到浏览器
        flush(); //刷新缓冲区的内容
        sleep(1); //每秒
    }

    ob_end_clean(); //清空缓冲区
    fclose($fp);
    exit;
}

//后台大文件上传
if (_pz('split_upload_s', true)) {
    add_action('init', array('zib_file_chunk', 'init'));
} else {
    add_action('post-upload-ui', 'zib_file_chunk_option_remind');
}

function zib_file_chunk_option_remind()
{
    echo '<p class="c-yellow">如需上传大文件或经常上传失败，推荐开启<a target="_blank" href="' . zib_get_admin_csf_url('扩展增强/系统工具') . '">【大文件分片上传】</a>功能</p>';
}
class zib_file_chunk
{

    public static $temp_dir;
    public $chunk_size = 1024 * 2; //kb，不能修改此值！！

    /**
     * Make instance of the admin class.
     */
    public static function init()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been ran previously
        if (null !== $instance) {
            return $instance;
        }

        $instance            = new self();
        $instance::$temp_dir = ZIB_TEMP_DIR;
        $instance->start();
        return $instance;
    }

    /**
     * Load all action and filters.
     * @return void
     */
    public function start()
    {
        add_action('wp_ajax_zib_chunker', array($this, 'ajax_chunk_receiver'));
        add_filter('plupload_init', array($this, 'plupload_settings'));
        add_filter('plupload_default_settings', array($this, 'plupload_settings'));
        add_filter('plupload_default_params', array($this, 'plupload_default_params'));
        add_filter('upload_post_params', array($this, 'plupload_default_params'));
        add_filter('upload_size_limit', array($this, 'get_upload_limit'));
        add_action('post-upload-ui', array($this, 'post_html_upload_ui'));
    }

    public function post_html_upload_ui()
    {
        echo '<p>您可以在主题设置->权限管理中设置上传大小、上传格式限制<a target="_blank" href="' . zib_get_admin_csf_url('功能权限/上传权限') . '">【立即设置】</a><br>如需上传超大文件，建议在前台编辑器中上传，支持断点续传、并发上传，速度更快，更稳定<br>如果您使用的OSS/COS等第三方云储存，上传大文件会很容易超时失败</p>';
    }

    /**
     * @param $plupload_params
     * @return mixed
     */
    public function plupload_default_params($plupload_params)
    {

        $plupload_params['action'] = 'zib_chunker';

        return $plupload_params;
    }

    public static function chunk_save_init()
    {
        $temp_dir = self::$temp_dir;
        $del_time = 48 * 60 * 60; //删除时间

        // 如果临时目录不存在，则创建它
        if (!@is_dir($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }

        // 保护临时目录不被浏览。
        $index_pathname = $temp_dir . '/index.php';
        if (!file_exists($index_pathname)) {
            $file = fopen($index_pathname, 'w');
            if (false !== $file) {
                fwrite($file, "<?php\n// \n");
                fclose($file);
            }
        }

        // 扫描临时目录中超过 24 小时的文件并删除它们。
        $files = glob($temp_dir . '/*.part');
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                if (@filemtime($file) < time() - $del_time) {
                    @unlink($file);
                }
            }
        }
    }
    /**
     * ajax分片上传
     * @param $plupload_init
     * @return mixed
     */
    public function ajax_chunk_receiver()
    {

        if (empty($_FILES) || $_FILES['async-upload']['error']) {
            exit();
        }

        if (!is_user_logged_in() || !current_user_can('upload_files')) {
            wp_die(__('Sorry, you are not allowed to upload files.'));
        }
        check_admin_referer('media-form');

        $chunk        = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0; //zero index
        $current_part = $chunk + 1;
        $chunks       = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;
        $fileName     = isset($_REQUEST['name']) ? $_REQUEST['name'] : $_FILES['async-upload']['name'];
        $zib_temp_dir = self::$temp_dir;

        if ($chunk === 0) {
            $this->chunk_save_init();
        }

        $filePath            = sprintf('%s/%d-%s-%s.part', $zib_temp_dir, get_current_blog_id(), 'admin', sha1($fileName));
        $zib_max_upload_size = $this->get_upload_limit();

        if (file_exists($filePath) && filesize($filePath) + filesize($_FILES['async-upload']['tmp_name']) > $zib_max_upload_size) {

            if (!$chunks || $chunk == $chunks - 1) {
                @unlink($filePath);

                if (!isset($_REQUEST['short']) || !isset($_REQUEST['type'])) {
                    echo wp_json_encode(array(
                        'success' => false,
                        'data'    => array(
                            'message'  => __('文件大小已超过最大文件大小设置。', 'zibll'),
                            'filename' => $fileName,
                        ),
                    ));
                    wp_die();
                } else {
                    status_header(502);
                    printf(
                        '<div class="error-div error">%s <strong>%s</strong><br />%s</div>',
                        sprintf(
                            '<button type="button" class="dismiss button-link" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">%s</button>',
                            __('Dismiss')
                        ),
                        sprintf(
                            /* translators: %s: Name of the file that failed to upload. */
                            __('&#8220;%s&#8221; has failed to upload.'),
                            esc_html($fileName)
                        ),
                        __('文件大小已超过最大文件大小设置。', 'zibll')
                    );
                    exit;
                }
            }

            exit();
        }

        /** Open temp file. */
        if ($chunk == 0) {
            $out = @fopen($filePath, 'wb');
        } elseif (is_writable($filePath)) { //
            $out = @fopen($filePath, 'ab');
        } else {
            $out = false;
        }

        if ($out) {

            /** Read binary input stream and append it to temp file. */
            $in = @fopen($_FILES['async-upload']['tmp_name'], 'rb');

            if ($in) {
                while ($buff = fread($in, $this->chunk_size)) {
                    fwrite($out, $buff);
                }
            } else {
                /** Failed to open input stream. */
                /** Attempt to clean up unfinished output. */
                @fclose($out);
                @unlink($filePath);
                error_log("Error reading uploaded part $current_part of $chunks.");

                if (!isset($_REQUEST['short']) || !isset($_REQUEST['type'])) {
                    echo wp_json_encode(
                        array(
                            'success' => false,
                            'data'    => array(
                                'message'  => sprintf(__('读取上传分片出错 %1$d of %2$d.', 'zibll'), $current_part, $chunks),
                                'filename' => esc_html($fileName),
                            ),
                        )
                    );
                    wp_die();
                } else {
                    status_header(502);
                    printf(
                        '<div class="error-div error">%s <strong>%s</strong><br />%s</div>',
                        sprintf(
                            '<button type="button" class="dismiss button-link" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">%s</button>',
                            __('Dismiss')
                        ),
                        sprintf(
                            /* translators: %s: Name of the file that failed to upload. */
                            __('&#8220;%s&#8221; 上传失败'),
                            esc_html($fileName)
                        ),
                        sprintf(__('读取上传分片出错 %1$d of %2$d.', 'zibll'), $current_part, $chunks)
                    );
                    exit;
                }
            }

            @fclose($in);
            @fclose($out);
            @unlink($_FILES['async-upload']['tmp_name']);
        } else {

            if (!isset($_REQUEST['short']) || !isset($_REQUEST['type'])) {
                echo wp_json_encode(
                    array(
                        'success' => false,
                        'data'    => array(
                            'message'  => sprintf(__('分片上传打开临时文件%s时失败。请检查目录%s写入权限是否正常。您也可以尝试在主题设置中关闭分片上传功能', 'zibll'), esc_html($filePath), esc_html($zib_temp_dir)),
                            'filename' => esc_html($fileName),
                        ),
                    )
                );
                wp_die();
            } else {
                status_header(502);
                printf(
                    '<div class="error-div error">%s <strong>%s</strong><br />%s</div>',
                    sprintf(
                        '<button type="button" class="dismiss button-link" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">%s</button>',
                        __('Dismiss')
                    ),
                    sprintf(
                        /* translators: %s: Name of the file that failed to upload. */
                        __('&#8220;%s&#8221; has failed to upload.'),
                        esc_html($fileName)
                    ),
                    sprintf(__('分片上传打开临时文件%s时失败。请检查目录%s写入权限是否正常。您也可以尝试在主题设置中关闭分片上传功能', 'zibll'), esc_html($filePath), esc_html($zib_temp_dir))
                );
                exit;
            }
        }

        if (!$chunks || $chunk == $chunks - 1) {

            $_FILES['async-upload']['tmp_name'] = $filePath;
            $_FILES['async-upload']['name']     = $fileName;
            $_FILES['async-upload']['size']     = filesize($_FILES['async-upload']['tmp_name']);
            $wp_filetype                        = wp_check_filetype_and_ext($_FILES['async-upload']['tmp_name'], $_FILES['async-upload']['name']);
            $_FILES['async-upload']['type']     = $wp_filetype['type'];

            header('Content-Type: text/plain; charset=' . get_option('blog_charset'));

            if (!isset($_REQUEST['short']) || !isset($_REQUEST['type'])) {
                // Compatibility with Easy Digital Downloads plugin.
                if (function_exists('edd_change_downloads_upload_dir')) {
                    global $pagenow;
                    $pagenow = 'async-upload.php';
                    edd_change_downloads_upload_dir();
                }

                send_nosniff_header();
                nocache_headers();

                $this->wp_ajax_upload_attachment();
                exit('0');

            } else { //non-ajax like add new media page
                $post_id = 0;
                if (isset($_REQUEST['post_id'])) {
                    $post_id = absint($_REQUEST['post_id']);
                    if (!get_post($post_id) || !current_user_can('edit_post', $post_id)) {
                        $post_id = 0;
                    }

                }

                $id = media_handle_upload('async-upload', $post_id, [], [
                    'action'    => 'wp_handle_sideload',
                    'test_form' => false,
                ]);
                if (is_wp_error($id)) {
                    printf(
                        '<div class="error-div error">%s <strong>%s</strong><br />%s</div>',
                        sprintf(
                            '<button type="button" class="dismiss button-link" onclick="jQuery(this).parents(\'div.media-item\').slideUp(200, function(){jQuery(this).remove();});">%s</button>',
                            __('Dismiss')
                        ),
                        sprintf(
                            /* translators: %s: Name of the file that failed to upload. */
                            __('&#8220;%s&#8221; has failed to upload.'),
                            esc_html($_FILES['async-upload']['name'])
                        ),
                        esc_html($id->get_error_message())
                    );
                    exit;
                }

                if ($_REQUEST['short']) {
                    echo $id;
                } else {
                    $type = $_REQUEST['type'];

                    /**
                     * 过滤返回的上传附件的ID。
                     *
                     * The dynamic portion of the hook name, `$type`, refers to the attachment type.
                     *
                     * Possible hook names include:
                     *
                     *  - `async_upload_audio`
                     *  - `async_upload_file`
                     *  - `async_upload_image`
                     *  - `async_upload_video`
                     *
                     * @since 1.1.0
                     *
                     * @param int $id Uploaded attachment ID.
                     */
                    echo apply_filters("async_upload_{$type}", $id);
                }
            }
        }

        exit();
    }

    /**
     * @description: 返回当前用户的最大上传限制 (以字节为单位)
     * @param {*}
     * @return {*}
     */
    public function get_upload_limit()
    {
        $upload_file_size  = zib_get_current_user_can_number('upload_file_size', 30) * 1024 * 1024;
        $upload_video_size = zib_get_current_user_can_number('upload_video_size', 30) * 1024 * 1024;
        $upload_img_size   = zib_get_current_user_can_number('upload_img_size', 30) * 1024 * 1024;

        return max($upload_file_size, $upload_video_size, $upload_img_size);
    }

    /**
     * 从wp-admin/includes/ajax-actions.php复制，因为我们必须覆盖*媒体句柄上载函数的参数。
     */
    public function wp_ajax_upload_attachment()
    {
        check_ajax_referer('media-form');

        if (!current_user_can('upload_files')) {
            echo wp_json_encode(
                array(
                    'success' => false,
                    'data'    => array(
                        'message'  => __('Sorry, you are not allowed to upload files.'),
                        'filename' => esc_html($_FILES['async-upload']['name']),
                    ),
                )
            );

            wp_die();
        }

        if (isset($_REQUEST['post_id'])) {
            $post_id = $_REQUEST['post_id'];

            if (!current_user_can('edit_post', $post_id)) {
                echo wp_json_encode(
                    array(
                        'success' => false,
                        'data'    => array(
                            'message'  => __('Sorry, you are not allowed to attach files to this post.'),
                            'filename' => esc_html($_FILES['async-upload']['name']),
                        ),
                    )
                );

                wp_die();
            }
        } else {
            $post_id = null;
        }

        $post_data = !empty($_REQUEST['post_data']) ? _wp_get_allowed_postdata(_wp_translate_postdata(false, (array) $_REQUEST['post_data'])) : array();

        if (is_wp_error($post_data)) {
            wp_die($post_data->get_error_message());
        }

        // 如果上下文是自定义标题或背景，请确保上传的文件是图像.
        if (isset($post_data['context']) && in_array($post_data['context'], array('custom-header', 'custom-background'), true)) {
            $wp_filetype = wp_check_filetype_and_ext($_FILES['async-upload']['tmp_name'], $_FILES['async-upload']['name']);

            if (!wp_match_mime_types('image', $wp_filetype['type'])) {
                echo wp_json_encode(
                    array(
                        'success' => false,
                        'data'    => array(
                            'message'  => __('The uploaded file is not a valid image. Please try again.'),
                            'filename' => esc_html($_FILES['async-upload']['name']),
                        ),
                    )
                );

                wp_die();
            }
        }

        //this is the modded function from wp-admin/includes/ajax-actions.php
        $attachment_id = media_handle_upload('async-upload', $post_id, $post_data, [
            'action'    => 'wp_handle_sideload',
            'test_form' => false,
        ]);

        if (is_wp_error($attachment_id)) {
            echo wp_json_encode(
                array(
                    'success' => false,
                    'data'    => array(
                        'message'  => $attachment_id->get_error_message(),
                        'filename' => esc_html($_FILES['async-upload']['name']),
                    ),
                )
            );

            wp_die();
        }

        if (isset($post_data['context']) && isset($post_data['theme'])) {
            if ('custom-background' === $post_data['context']) {
                update_post_meta($attachment_id, '_wp_attachment_is_custom_background', $post_data['theme']);
            }

            if ('custom-header' === $post_data['context']) {
                update_post_meta($attachment_id, '_wp_attachment_is_custom_header', $post_data['theme']);
            }
        }

        $attachment = wp_prepare_attachment_for_js($attachment_id);
        if (!$attachment) {
            wp_die();
        }

        echo wp_json_encode(
            array(
                'success' => true,
                'data'    => $attachment,
            )
        );

        wp_die();
    }

    /**
     * Filter plupload settings.
     *
     * @since 1.1.0
     */
    public function plupload_settings($plupload_settings)
    {

        $plupload_settings['url']                      = admin_url('admin-ajax.php');
        $plupload_settings['filters']['max_file_size'] = $this->get_upload_limit() . 'b';
        $plupload_settings['chunk_size']               = $this->chunk_size . 'kb';
        $plupload_settings['max_retries']              = 1; //重试次数

        return $plupload_settings;
    }

}
