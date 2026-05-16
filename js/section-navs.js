/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime: 2024-12-27 12:07:56
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|文章目录树
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//严格模式
'use strict';

function section_navs(selector) {
    var navbox_selector = '.posts-nav-box';
    var _body = _win.bd;
    var nav_class = 'posts-nav-lists';
    var nav_selector = '.' + nav_class;
    var selector_s = selector + ' h1,' + selector + ' h2,' + selector + ' h3,' + selector + ' h4';

    var index = 0;

    function autoScrollToTop() {
        var hash = window.location.hash;
        if (hash && $(hash).length) {
            $('a[href="' + hash + '"]').click();
        }
    }

    var add_box = function () {
        var navs_lists = GetnavsLists();
        if (!navs_lists) return;

        $(navbox_selector).each(function () {
            var _this = $(this);
            var name = _this.attr('data-title') || '';
            name = name && '<div class="box-body notop"><div class="title-theme">' + name + '</div></div>';
            _this.append(name + '<div class="zib-widget"><div class="' + nav_class + ' scroll-y mini-scrollbar list-unstyled"></div></div>');
        });
        add_lists(navs_lists);
        setTimeout(function () {
            autoScrollToTop();
        }, 20);
    };

    //设置排除的元素
    function isExcludedElement(el) {
        return el.hasClass('item-heading') || el.parents('.no-nav').length;
    }

    function GetnavsLists() {
        var navs_lists = '';
        $(selector_s).each(function (indexInArray) {
            var _this = $(this);
            if (!isExcludedElement(_this)) {
                var tag = _this.prop('tagName');
                var text = _this.text();
                var _class = 'n-' + tag;
                _class += indexInArray == 1 ? ' active' : '';
                index = indexInArray;
                _this.attr('id', 'wznav_' + index);
                navs_lists += '<li class="' + _class + '"><a  class="text-ellipsis" href="#wznav_' + index + '">' + text + '</a></li>';
            }
        });
        return navs_lists;
    }

    var add_lists = function (navs_lists) {
        $(nav_selector).html('<ul class="bl relative nav">' + navs_lists + '</ul>');
        _body.on('click', nav_selector + ' a', function () {
            var _href = $($(this).attr('href'));
            _href.parents('.panel').each(function () {
                var _collapse = $(this).find('.collapse');
                if (_collapse.length) {
                    _collapse.collapse('show');
                }
            });

            $('.read-more-open').click();
            scrollTopTo(_href);
            return false;
        });

        _body.scrollspy({
            target: nav_selector,
            offset: 105,
        });
        collapse();
    };

    //显示折叠内容
    var collapse = function () {
        var find_selector = '.n-H1,.n-H2';
        $(nav_selector + ' .bl').each(function () {
            var _this = $(this);
            if (_this.innerHeight() > 380) {
                _this.find(find_selector).each(function () {
                    var _this = $(this);
                    var _n_h2 = _this.nextUntil(find_selector);
                    if (_n_h2.length) {
                        _n_h2.addClass('yc');
                        _this.append('<i class="fa fa-angle-right nav-toggle-collapse"></i>');
                    }
                });
            }
        });

        _body.on('click', nav_selector + ' .nav-toggle-collapse', function () {
            $(this).toggleClass('fa-rotate-90').parent().nextUntil(find_selector).toggleClass('yc');
        });
    };

    if ($(selector_s).length > 2) {
        add_box();
    }
}

section_navs('[data-nav]');
