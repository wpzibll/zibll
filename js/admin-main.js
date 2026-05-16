/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime: 2025-10-20 21:14:33
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|后台UI-JavaScript
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

(function ($, document) {
    function cssTransition(n, t, o, i, e) {
        var r, a, s;
        i && ((t += 'px'), (o += 'px'), (r = 'translate3D(' + t + ',' + o + ' , 0)'), (a = {}), (s = cssT_Support()), (a[s + 'transform'] = r), (a[s + 'transition'] = s + 'transform 0s linear'), (a.cursor = e), 'null' == i && ((a[s + 'transform'] = ''), (a[s + 'transition'] = '')), n.css(a));
    }

    function cssT_Support() {
        var n = document.body || document.documentElement;
        n = n.style;
        return '' == n.WebkitTransition ? '-webkit-' : '' == n.MozTransition ? '-moz-' : '' == n.OTransition ? '-o-' : '' == n.transition ? '' : void 0;
    }
    $.fn.minitouch = function (n) {
        n = $.extend(
            {
                direction: 'bottom',
                selector: '',
                depreciation: 50,
                onStart: !1,
                onEnd: !1,
            },
            n
        );
        var t = $(this),
            o = ($('body'), n.depreciation),
            i = 0,
            e = 0,
            r = 0,
            a = 0,
            s = 0,
            c = 0,
            u = 0,
            l = !1;
        t.on('touchstart pointerdown MSPointerDown', n.selector, function (n) {
            (i = 0), (e = 0), (r = 0), (a = 0), (s = 0), (c = 0), (u = 0), (i = n.originalEvent.pageX || n.originalEvent.touches[0].pageX), (e = n.originalEvent.pageY || n.originalEvent.touches[0].pageY), (l = !0);
        })
            .on('touchmove pointermove MSPointerMove', n.selector, function (t) {
                (r = t.originalEvent.pageX || t.originalEvent.touches[0].pageX), (a = t.originalEvent.pageY || t.originalEvent.touches[0].pageY), (c = r - i), (u = a - e), (s = (180 * Math.atan2(u, c)) / Math.PI), 'right' == n.direction && ((u = 0), (c = s > -40 && s < 40 && c > 0 ? c : 0)), 'left' == n.direction && ((u = 0), (c = (s > 150 || s < -150) && 0 > c ? c : 0)), 'top' == n.direction && ((c = 0), (u = s > -130 && s < -50 && 0 > u ? u : 0)), 'bottom' == n.direction && ((c = 0), (u = s > 50 && s < 130 && u > 0 ? u : 0)), (0 === c && 0 === u) || (t.preventDefault(), cssTransition($(this), c, u, l, 'grab'));
            })
            .on('touchend touchcancel pointerup MSPointerUp', n.selector, function () {
                (Math.abs(c) > o || Math.abs(u) > o) && 0 != n.onEnd && n.onEnd(t), cssTransition($(this), 0, 0, 'null', ''), (l = !1), (i = 0), (e = 0), (r = 0), (a = 0), (s = 0), (c = 0), (u = 0);
            });
    };

    $(document).ready(function ($) {
        var _body = $('body');

        if (typeof $.fn.serializeObject !== 'function') {
            $.fn.serializeObject = function () {
                var o = {};
                var a = this.serializeArray();
                $.each(a, function () {
                    if (o[this.name] !== undefined) {
                        if (!o[this.name].push) {
                            o[this.name] = [o[this.name]];
                        }
                        o[this.name].push(this.value || '');
                    } else {
                        o[this.name] = this.value || '';
                    }
                });
                return o;
            };
        }

        if (_body.width() < 783) {
            $('#adminmenuwrap').minitouch({
                direction: 'left',
                onEnd: function () {
                    $('#wpwrap').removeClass('wp-responsive-open');
                },
            });
        }

        //系统通知
        function notyf(str, ys, time, id) {
            $('.notyn').length || _body.append('<div class="notyn"></div>');
            ys = ys || 'success';
            time = time || 5000;
            time = time < 100 ? time * 1000 : time;
            var id_attr = id ? ' id="' + id + '"' : '';
            var _html = $('<div class="noty1"' + id_attr + '><div class="notyf ' + ys + '">' + str + '</div></div>');
            var is_close = !id;
            if (id && $('#' + id).length) {
                $('#' + id)
                    .find('.notyf')
                    .removeClass()
                    .addClass('notyf ' + ys)
                    .html(str);
                _html = $('#' + id);
                is_close = true;
            } else {
                $('.notyn').append(_html);
            }
            is_close &&
                setTimeout(function () {
                    notyf_close(_html);
                }, time);
        }

        function notyf_close(_e) {
            _e.addClass('notyn-out');
            setTimeout(function () {
                _e.remove();
            }, 1000);
        }
        _body.on('click', '.noty1', function () {
            notyf_close($(this));
        });

        //点击复制
        function copyText(text, success, error, _this) {
            // 数字没有 .length 不能执行selectText 需要转化成字符串
            var textString = text.toString();
            var input = document.querySelector('#copy-input');
            if (!input) {
                input = document.createElement('input');
                input.id = 'copy-input';
                input.readOnly = 'readOnly'; // 防止ios聚焦触发键盘事件
                input.style.position = 'fixed';
                input.style.left = '-2000px';
                input.style.zIndex = '-1000';
                _this.parentNode.appendChild(input);
            }

            input.value = textString;
            // ios必须先选中文字且不支持 input.select();
            selectText(input, 0, textString.length);
            if (document.execCommand('copy')) {
                $.isFunction(success) && success();
            } else {
                $.isFunction(error) && error();
            }
            input.blur();

            // input自带的select()方法在苹果端无法进行选择，所以需要自己去写一个类似的方法
            // 选择文本。createTextRange(setSelectionRange)是input方法
            function selectText(textbox, startIndex, stopIndex) {
                if (textbox.createTextRange) {
                    //ie
                    var range = textbox.createTextRange();
                    range.collapse(true);
                    range.moveStart('character', startIndex); //起始光标
                    range.moveEnd('character', stopIndex - startIndex); //结束光标
                    range.select(); //不兼容苹果
                } else {
                    //firefox/chrome
                    textbox.setSelectionRange(startIndex, stopIndex);
                    textbox.select();
                }
            }
        }

        _body.on('click', '[data-clipboard-text]', function () {
            var _this = $(this);
            var text = _this.attr('data-clipboard-text');
            var tag = _this.attr('data-clipboard-tag') || '内容';

            copyText(
                text,
                function () {
                    notyf(tag + '已复制');
                },
                function () {
                    notyf(tag + '复制失败，请手动复制', 'danger');
                },
                this
            );
        });

        //---------------------------------------------------------------
        //每次都刷新的模态框
        _body.on('click', '[data-toggle="RefreshModal"]', function () {
            var _this = $(this);
            var dataclass = _this.attr('data-class') || 'modal-mini';
            var remote = _this.attr('data-remote');
            var height = _this.attr('data-height') || 200;
            var mobile_bottom = _this.attr('mobile-bottom') && $(window).width() < 769 ? ' bottom' : '';
            var modal_class = 'zib-modal flex jc fade' + mobile_bottom;
            var id = 'refresh_modal';
            var is_new = _this.attr('new');
            id += is_new ? parseInt((Math.random() + 1) * Math.pow(10, 4)) : '';
            var _id = '#' + id;

            if (!remote) {
                var action = _this.attr('data-action');
                var ajax_url = _this.attr('ajax-url') || (wp && wp.ajax && wp.ajax.settings && wp.ajax.settings.url) || '/wp-admin/admin-ajax.php';
                remote = ajax_url + '?action=' + action;
            }

            dataclass += ' zib-modal-dialog';
            var modal_html =
                '<div class="' +
                modal_class +
                '" id="' +
                id +
                '" tabindex="-1" role="dialog" aria-hidden="false">\
                        <div class="zib-modal-backdrop"></div><div class="' +
                dataclass +
                '" role="document"><div class="hide-btn dashicons dashicons-no-alt"></div>\
                            <div class="zib-modal-content"></div>\
                        </div>\
                            </div>';

            var loading = '<div class="zib-modal-body" style="display:none;"></div><div class="flex jc loading-mask absolute main-bg radius8"><div class="em2x opacity5"><i class="rotate-loading"></i></div></div>';

            var _modal = $(_id);
            if (_modal.length) {
                if (_modal.hasClass('in')) modal_class += ' in';
                _modal.removeClass().addClass(modal_class);
                _modal.find('.zib-modal-dialog').removeClass().addClass(dataclass);
                _modal.find('.loading-mask').fadeIn(200);
                _modal
                    .find('.zib-modal-content')
                    .css({
                        overflow: 'hidden',
                    })
                    .animate({
                        height: height,
                    });
            } else {
                _body.append(modal_html);
                _modal = $(_id);
                if (is_new) {
                    _modal.on('hide.modal', function () {
                        $(this).remove();
                    });
                }
                _modal.find('.zib-modal-content').html(loading).css({
                    height: height,
                    overflow: 'hidden',
                });
            }

            _modal.zib_modal('show');

            $.get(remote, null, function (data) {
                _modal
                    .find('.zib-modal-body')
                    .html(data)
                    .slideDown(200, function () {
                        _modal.trigger('loaded.modal').find('.loading-mask').fadeOut(200);
                        var b_height = $(this).outerHeight();
                        _modal.find('.zib-modal-content').animate(
                            {
                                height: b_height,
                            },
                            200,
                            'swing',
                            function () {
                                _modal.find('.zib-modal-content').css({
                                    height: '',
                                    overflow: '',
                                    transition: '',
                                });
                            }
                        );
                    });
            });

            return false;
        });

        $.fn.zib_modal = function ($action) {
            var _this = $(this);
            switch ($action) {
                case 'show': {
                    show(_this);
                    break;
                }
                case 'hide': {
                    hide(_this);
                    break;
                }
            }

            function show(_this) {
                _this.css('display', 'flex');
                setTimeout(function () {
                    _this.addClass('in').trigger('show.modal');
                }, 10);
            }

            function hide(_this) {
                _this.removeClass('in').trigger('hide.modal');
                setTimeout(function () {
                    _this.css('display', 'none');
                }, 300);
            }

            if (!_body.data('zib-modal-is-on')) {
                _body.on('click', '.zib-modal-backdrop,.hide-btn', function () {
                    $($(this).parents('.zib-modal.in')[0]).zib_modal('hide');
                });
                _body.data('zib-modal-is-on', true);
            }
        };

        //----------------------TAB-栏目--------------------------
        _body.on('click', '.zib-tab-toggle', function () {
            var _this = $(this);
            var tab_id = _this.attr('tab-id');
            if (_this.parent().hasClass('active')) return;
            var _con = _this
                .parent()
                .addClass('active')
                .siblings()
                .removeClass('active')
                .parent()
                .parent()
                .find('[tab-id="' + tab_id + '"]');
            _con.siblings().removeClass('in');
            setTimeout(function () {
                _con.addClass('active').siblings().removeClass('active');
            }, 150);
            setTimeout(function () {
                _con.addClass('in');
            }, 160);
        });

        //佣金确认
        _body.on('click', '.process-submit', function () {
            return confirm('确认处理此申请？');
        });

        //--------------------为后台设置：修改网站URL地方添加说明------------------------
        var admin_options_url_input = $('.options-general-php input#siteurl');
        if (admin_options_url_input.length) {
            var html = '<div class="flex ac admin-url-set-warning" style="color: #e13535;background: #fbedea;padding: 10px;border-radius: 6px;border: 1px solid #ffbdbd;margin-top: 6px;"><span class="mb6 em2x mr20 dashicons dashicons-warning"></span><div>直接修改WordPress地址或站点地址会导致网站严重错误，推荐根据zibll官网教程以及专用插件进行修改<br><a target="_blank" href="https://www.zibll.com/18629.html">查看详细教程</a> | <a target="_blank" href="https://www.zibll.com/19369.html">下载一键换域名插件</a></div></div>';
            admin_options_url_input.after(html);
        }

        /*-------------------后台首页设置文案修改-------- */
        var show_on_front_input = $('input[name="show_on_front"]:eq(0)');
        if (show_on_front_input.length) {
            var parent = show_on_front_input.parent();
            parent.html(parent.html().replace('您的最新文章', '子比首页[最新文章]'));

            var page_for_posts = $('select[name="page_for_posts"]:eq(0)');
            if (page_for_posts.length) {
                var page_for_posts_parent = page_for_posts.parent();
                page_for_posts_parent.html(page_for_posts_parent.html().replace('文章页：', '子比首页：')).parent().append('<p class="description em09">将网站主页更改为其他页面后，您可以新建一个页面并将其设置为原本的子比首页</p>');
            }

            var posts_per_page = $('[for="posts_per_page"]:eq(0)');
            if (posts_per_page.length) {
                posts_per_page.html(posts_per_page.html().replace('博客页面至多显示', '文章每页显示')).parent().next().append('<p class="description em09">第一页的置顶文章不在此数量内，您可以在<a href="/wp-admin/admin.php?page=zibll_options#tab=%e6%96%87%e7%ab%a0%e5%88%97%e8%a1%a8/%e6%96%87%e7%ab%a0%e5%88%97%e8%a1%a8">主题设置->文章列表</a>中设置翻页模式</p>');
            }
        }

        //--------------------为后台菜单：添加说明------------------------
        var menu_edit_instructions = $('.menu-edit .drag-instructions');
        if (menu_edit_instructions.length) {
            var menu_edit_desc = '<div class="c-yellow">PC端菜单请勿添加过多的一级菜单，过多会导致PC端导航栏显示不下，将自动折叠显示</div><div class="">移动端最多显示两级菜单，请合理使用高级子菜单以达到最佳效果 | <a target="_blank" href="https://www.zibll.com/1012.html">查看官方教程</a></div>';
            menu_edit_instructions.append(menu_edit_desc);
        }

        //----------后台固定链接配置：添加说明-----------
        var permalink_tags_box = $('.permalink-structure .available-structure-tags');
        if (permalink_tags_box.length) {
            permalink_tags_box.append('<div class="mt10 c-blue">推荐选择自定义结构，并设置为<code class="">/%post_id%.html</code></div><div class="mt10 c-yellow mb10">注意：请务必配置好服务器的伪静态功能，否则页面会出现404错误！<a target="_blank" href="https://www.zibll.com/3025.html">【查看教程】</a></div><button type="submit" style="cursor: pointer;" class="but jb-blue permalink-structure-auto-btn">自动填入推荐结构并保存</button>');
        }

        _body.on('click', '.permalink-structure-auto-btn', function () {
            $('.permalink-structure [name="permalink_structure"]').val('/%post_id%.html').click();
        });

        console.log('\n' + ' %c Zibll Theme %c https://zibll.com ' + '\n', 'color: #fadfa3; background: #030307; padding:3px; font-size:12px;', 'color: #2abd4e;background: #16171a; padding: 3px; font-size: 12px;');
    });
})(jQuery, document);
