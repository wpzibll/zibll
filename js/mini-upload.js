/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime : 2026-05-05 23:27:54
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|图片上传封装插件
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

'use strict';

function miniupload() {
    var _body = $('body');
    var input_selector = '[zibupload="image_upload"]';
    var submit_selector = '[zibupload="submit"]';
    var form_selector = '.form-upload,.mini-upload,form';

    var add_html = '<div class="add"></div>';
    var add_selector = '.add';
    var mini_file = {};

    var pre_html;

    _body.on('change', input_selector, function (e) {
        var _this = $(this);
        var files = this.files || e.dataTransfer.files;
        var form = $(_this.parents(form_selector)[0]);

        var size_max = _this.attr('size_max') || Number(_win.upload_img_size) || 3;

        var pre_selector = _this.attr('data-preview') || '.preview';
        var pre = form.find(pre_selector);
        pre_html = pre.html();

        var accept = _this.attr('accept'); //允许上传的文件类型
        var multiple = _this.attr('multiple'); //是否允许多选
        var multiple_max = _this.attr('multiple_max'); //多选最多允许几个

        var accept_yanz = false;
        if (-1 !== accept.indexOf('image')) {
            accept_yanz = 'image';
        }
        if (-1 !== accept.indexOf('vedio')) {
            accept_yanz = 'vedio';
        }

        var upload_id = _this.attr('upload_id');
        //设置唯一ID标识
        if (!_this.attr('upload_id')) {
            upload_id = parseInt((Math.random() + 1) * Math.pow(10, 4));
            _this.attr('upload_id', upload_id);
        }

        if (!mini_file[upload_id]) {
            mini_file[upload_id] = [];
        }

        $.each(files, function (key, val) {
            var name = val.name;
            if (accept_yanz && -1 == val.type.indexOf(accept_yanz)) {
                //如果限制文件格式
                notyf('文件[' + name + ']格式错误', 'danger'), _this.val('');
            } else if (size_max && val.size > size_max * 1048567) {
                //限制文件大小
                notyf('文件[' + name + ']大小超过限制，最大' + size_max + 'M，请重新选择', 'danger');
            } else if (!pre.find('[preview-name="' + name + '"]').length) {
                //不允许重复文件
                mini_file[upload_id].push(val);
            }
        });

        var a_file = [];

        var ii = 1;
        var empty = false;
        $.each(mini_file[upload_id], function (key, val) {
            if (multiple_max && multiple_max < ii) {
                //限制文件数量
                notyf('文件数量过多！最多可选择' + multiple_max + '个文件', 'danger');
                return false;
            } else {
                ii++;
                a_file.push(val);
                ToB64(val, function (e) {
                    var img = '<img class="fit-cover" preview-name="' + val.name + '" src="' + e + '">';
                    if (multiple) {
                        if (!empty) {
                            pre.html('');
                            empty = true;
                        }
                        var cloce = '<div class="preview-remove" upload-id="' + upload_id + '" file-key="' + $.inArray(val, a_file) + '"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></div>';
                        img = '<div class="preview-item">' + img + cloce + '</div>';
                        pre.append(img).find(add_selector).remove();
                        if (multiple_max && multiple_max >= ii) {
                            pre.append(add_html);
                        }
                    } else {
                        pre.html(img);
                    }
                });
            }
        });
        mini_file[upload_id] = a_file;
        form.find('[auto-submit]').click();
    });

    _body.on('click', '.preview-remove', function () {
        var _this = $(this);
        var file_key = _this.attr('file-key');
        var upload_id = _this.attr('upload-id');
        _this.parent().animate(
            {
                width: 0,
            },
            200,
            'swing',
            function () {
                var pre = $(this).parent();
                $(this).remove();
                mini_file[upload_id].splice(file_key, 1);
                if (!pre.find(add_selector).length) {
                    pre.append(add_html);
                }
            }
        );
        return false;
    });

    _body.on('click', '.preview ' + add_selector, function () {
        var _this = $(this);
        var form = $(_this.parents(form_selector)[0]);
        form.find(input_selector).click();
    });

    //提交表单
    _body.on('click', submit_selector, function (e) {
        var _this = $(this);
        if (_this.attr('disabled')) {
            return false;
        }

        if (e.preventDefault) e.preventDefault();
        else e.returnValue = false;

        var _text = _this.html();
        var form = $(_this.parents(form_selector)[0]);
        var _input = form.find(input_selector);
        var formData = new FormData();

        //循环插入文件
        var is_files = _this.attr('zibupload-nomust');

        _input.each(function () {
            var _this = $(this);
            var upload_id = _this.attr('upload_id');
            if (upload_id && mini_file[upload_id]) {
                var ii = 0;
                var tag = _this.attr('data-tag') || 'file';
                $.each(mini_file[upload_id], function (key, val) {
                    var append_tag = ii ? tag + '_' + ii : tag;
                    formData.append(append_tag, val, val.name);
                    ii++;
                });
                if (ii > 1) {
                    formData.append(tag + '_file_count', ii);
                }
                is_files = ii;
            }
        });

        //必须选择图片判断
        if (!is_files) return notyf('请先选择待上传的文件！', 'danger');

        //添加其它数据
        var serializeObject = form.serializeObject();
        $.each(serializeObject, function (key, val) {
            formData.append(key, val);
        });

        //循环插入_POST内容
        form.find('[data-name]').each(function () {
            var _th = $(this);
            var n = _th.attr('data-name');
            var v = _th.val() || _th.attr('data-value');
            if (v === undefined) {
                v = '';
            }
            if (formData.get(n) === null) {
                formData.append(n, v);
            }
        });

        //人机验证
        if (formData.get('captcha_mode') && is_captcha(formData.get('captcha_mode'))) {
            tbquire(['captcha'], function () {
                CaptchaOpen(_this, formData.get('captcha_mode'));
            });
            return !1;
        }

        if (window.captcha) {
            if (window.captcha._this) {
                delete window.captcha._this;
            }

            formData.append('captcha', JSON.stringify(window.captcha));
            window.captcha = {}; //只能使用一次
        }

        /**
        for (var [a, b] of formData.entries()) {
            console.log(a, b);
        } */

        var miniuploaded = function (no_preview_reset) {
            _this.attr('disabled', false).html(_text);
            _input
                .attr('disabled', false)
                .val('')
                .each(function () {
                    var _this = $(this);
                    var upload_id = _this.attr('upload_id');
                    if (upload_id && mini_file[upload_id]) {
                        mini_file[upload_id] = [];
                    }
                });
            if (!no_preview_reset && pre_html) {
                form.find('.preview').html(pre_html);
            }
        };

        var notyf_id = 'miniupload_ajax';
        notyf('正在处理请稍等...', 'load', '', notyf_id);
        _this.attr('disabled', true).html('<span class="miniupload-ing"><i class="loading mr3 c-white"></i><span class="loading-text c-white">上传中<count class="px12 ml3"></count></span><div class="progress progress-striped active"><div class="progress-bar progress-bar-success" role="progressbar" style="width:0;"></div></div></span>');
        _input.attr('disabled', true);

        $.ajax({
            url: _win.ajax_url,
            type: 'POST',
            data: formData,
            // 告诉jQuery不要去处理发送的数据
            processData: false,
            cache: false,
            // 告诉jQuery不要去设置Content-Type请求头
            contentType: false,
            dataType: 'json',
            error: function (n) {
                var _msg = '操作失败 ' + n.status + ' ' + n.statusText + '，请刷新页面后重试';
                if (n.responseText && n.responseText.indexOf('致命错误') > -1) {
                    _msg = '网站遇到致命错误，请检查插件冲突或通过错误日志排除错误';
                }
                notyf(_msg, 'danger', '', notyf_id);
                miniuploaded();
            },
            xhr: jqXhr(function (e) {
                var percent = Math.round((e.loaded / e.total) * 100);
                _this.find('count').html(percent + '%');
                percent >= 100 && _this.find('.loading-text').html('处理中 ...');
                form.find('.progress .progress-bar').css('width', percent + '%');
            }),
            success: function (n) {
                if (n.error) {
                    _win.slidercaptcha = false;
                }
                var ys = n.ys ? n.ys : n.error ? 'danger' : '';
                notyf(n.msg || '操作成功', ys, '', notyf_id);
                miniuploaded(n.no_preview_reset);
                if (n.hide_modal) {
                    $(_this.parents('.modal')[0]).modal('hide');
                }
                if (n.url && n.replace_img) {
                    $('img' + n.replace_img).attr('src', n.url);
                }
                if (n.reload) {
                    if (n.goto) {
                        window.location.href = n.goto;
                        window.location.reload;
                    } else {
                        window.location.reload();
                    }
                }
                _this.trigger('miniuploaded', n, form);
            },
        });
    });

    //图片转Base64
    function ToB64(e, n) {
        if ('undefined' == typeof FileReader) return notyf('当前浏览器不支持图片上传，请更换浏览器', 'danger');
        var r = new FileReader();
        r.readAsDataURL(e),
            (r.onload = function (e) {
                n && n(e.target.result);
            });
    }

    //绑定上传进度
    function jqXhr(fun) {
        jqXhr.onprogress = fun;
        //使用闭包实现监听绑
        return function () {
            //通过$.ajaxSettings.xhr();获得XMLHttpRequest对象
            var xhr = $.ajaxSettings.xhr();
            //判断监听函数是否为函数
            if (typeof jqXhr.onprogress !== 'function') return xhr;
            //如果有监听函数并且xhr对象支持绑定时就把监听函数绑定上去
            if (jqXhr.onprogress && xhr.upload) {
                xhr.upload.onprogress = jqXhr.onprogress;
            }
            return xhr;
        };
    }
}

if (!_win.is_miniupload) {
    miniupload();
    _win.is_miniupload = true;
}
