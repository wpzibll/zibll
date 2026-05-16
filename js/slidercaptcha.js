/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-15 12:33:01
 * @LastEditTime : 2025-11-09 21:25:15
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

(function ($) {
    'use strict';

    var SliderCaptcha = function (element, options) {
        this.$element = $(element);
        this.options = $.extend({}, SliderCaptcha.DEFAULTS, options);
        this.$element.css({
            position: 'relative',
            width: this.options.width + 'px',
            margin: '0 auto',
        });
        this.init();
    };

    SliderCaptcha.DEFAULTS = {
        width: 280, // canvas宽度
        height: 170, // canvas高度
        PI: Math.PI,
        sliderL: 42, // 滑块边长
        sliderR: 9, // 滑块半径
        offset: 8, // 容错偏差
        loadingText: '<i class="loading mr6"></i>加载中...',
        failedText: '请再试一次',
        barText: '向右滑动填充拼图',
        repeatIcon: 'fa fa-refresh',
        maxLoadCount: 3,
        onFail: function () {
            //失败执行的函数
        },
        onSuccess: function () {
            //成功执行的函数
        },
        setSrc: function () {
            //图片获取函数
            return '';
        },
        getX: null,
    };

    function Plugin(option) {
        return this.each(function () {
            var $this = $(this);
            var data = $this.data('lgb.SliderCaptcha');
            var options = typeof option === 'object' && option;
            if (data && !/reset/.test(option)) $(data.refreshIcon).click();
            if (!data) $this.data('lgb.SliderCaptcha', (data = new SliderCaptcha(this, options)));
            if (typeof option === 'string') data[option]();
        });
    }

    $.fn.sliderCaptcha = Plugin;
    $.fn.sliderCaptcha.Constructor = SliderCaptcha;

    var _proto = SliderCaptcha.prototype;
    _proto.init = function () {
        this.initDOM();
        this.initImg();
        this.bindEvents();
    };

    _proto.initDOM = function () {
        var createElement = function (tagName, className) {
            var elment = document.createElement(tagName);
            elment.className = className;
            return elment;
        };

        var createCanvas = function (width, height) {
            var canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            return canvas;
        };

        var canvas = createCanvas(this.options.width - 2, this.options.height); // 画布
        var block = canvas.cloneNode(true); // 滑块
        var sliderContainer = createElement('div', 'sliderContainer');
        var refreshIcon = createElement('botton', 'refreshIcon ' + this.options.repeatIcon);
        var sliderMask = createElement('div', 'sliderMask');
        var slider = createElement('div', 'captcha-slider');
        var sliderIcon = createElement('i', 'fa fa-chevron-right sliderIcon');
        var text = createElement('span', 'sliderText');

        canvas.className = 'captcha-body-bg placeholder';
        block.className = 'captcha-body-bar';
        text.innerHTML = this.options.barText;

        var el = this.$element;
        el.html($(canvas));
        el.append($(refreshIcon));
        el.append($(block));
        slider.appendChild(sliderIcon);
        sliderContainer.appendChild(sliderMask);
        sliderContainer.appendChild(slider);
        sliderContainer.appendChild(text);
        el.append($(sliderContainer));

        var _canvas = {
            canvas: canvas,
            block: block,
            sliderContainer: $(sliderContainer),
            refreshIcon: refreshIcon,
            slider: slider,
            sliderMask: sliderMask,
            sliderIcon: sliderIcon,
            text: $(text),
            canvasCtx: canvas.getContext('2d'),
            blockCtx: block.getContext('2d'),
        };

        if ($.isFunction(Object.assign)) {
            Object.assign(this, _canvas);
        } else {
            $.extend(this, _canvas);
        }
    };

    _proto.initImg = function () {
        var that = this;
        var isIE = window.navigator.userAgent.indexOf('Trident') > -1;
        var L = this.options.sliderL + this.options.sliderR * 2 + 3; // 滑块实际边长
        var drawImg = function (ctx, operation) {
            var l = that.options.sliderL;
            var r = that.options.sliderR;
            var PI = that.options.PI;
            var x = that.x;
            var y = that.y;
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.arc(x + l / 2, y - r + 2, r, 0.72 * PI, 2.26 * PI);
            ctx.lineTo(x + l, y);
            ctx.arc(x + l + r - 2, y + l / 2, r, 1.21 * PI, 2.78 * PI);
            ctx.lineTo(x + l, y + l);
            ctx.lineTo(x, y + l);
            ctx.arc(x + r - 2, y + l / 2, r + 0.4, 2.76 * PI, 1.24 * PI, true);
            ctx.lineTo(x, y);
            ctx.lineWidth = 2;
            ctx.fillStyle = 'rgba(100, 100, 100, 0.7)';
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.stroke();
            ctx[operation]();
            ctx.globalCompositeOperation = isIE ? 'xor' : 'destination-over';
        };

        var getRandomNumberByRange = function (start, end) {
            return Math.round(Math.random() * (end - start) + start);
        };

        var getRandomNumberByRangeToX = function (start, end) {
            if ($.isFunction(that.options.getX)) return that.options.getX(start, end);
            return getRandomNumberByRange(start, end);
        };

        var img = new Image();
        img.crossOrigin = 'Anonymous';
        var loadCount = 0;

        img.onload = function () {
            // 随机创建滑块的位置
            that.x = getRandomNumberByRangeToX(L + 10, that.options.width - (L + 10));
            that.y = getRandomNumberByRange(10 + that.options.sliderR * 2, that.options.height - (L + 10));
            drawImg(that.canvasCtx, 'fill');
            drawImg(that.blockCtx, 'clip');

            that.canvasCtx.drawImage(img, 0, 0, that.options.width - 2, that.options.height);
            that.blockCtx.drawImage(img, 0, 0, that.options.width - 2, that.options.height);
            var y = that.y - that.options.sliderR * 2 - 1;
            var ImageData = that.blockCtx.getImageData(that.x - 3, y, L, L);
            that.block.width = L;
            that.blockCtx.putImageData(ImageData, 0, y + 1);
            that.text.text(that.text.attr('data-text'));
        };

        img.onerror = function (e) {
            loadCount++;
            if (loadCount >= that.options.maxLoadCount) {
                that.text.text('加载失败').addClass('text-danger c-red');
                that.canvas.removeClass('placeholder');
                return;
            }
            img.localImages();
        };
        img.setSrc = function () {
            loadCount = 0;
            that.text.removeClass('text-danger');
            if ($.isFunction(that.options.setSrc)) {
                var src = that.options.setSrc();
                src ? (img.src = that.options.setSrc()) : img.localImages();
            }
        };
        img.localImages = function () {
            that.text.removeClass('text-danger');
            var src = 'https://picsum.photos/' + that.options.width + '/' + that.options.height + '/?image=' + Math.round(Math.random() * 20);
            if (isIE) {
                // IE浏览器无法通过img.crossOrigin跨域，使用ajax获取图片blob然后转为dataURL显示
                var xhr = new XMLHttpRequest();
                xhr.onloadend = function (e) {
                    var file = new FileReader(); // FileReader仅支持IE10+
                    file.readAsDataURL(e.target.response);
                    file.onloadend = function (e) {
                        img.src = e.target.result;
                    };
                };
                xhr.open('GET', src);
                xhr.responseType = 'blob';
                xhr.send();
            } else img.src = src;
        };
        img.setSrc();
        this.text.attr('data-text', this.options.barText);
        this.text.html(this.options.loadingText);
        this.img = img;
    };

    _proto.clean = function () {
        this.canvasCtx.clearRect(0, 0, this.options.width, this.options.height);
        this.blockCtx.clearRect(0, 0, this.options.width, this.options.height);
        this.block.width = this.options.width;
    };

    _proto.bindEvents = function () {
        var that = this;
        this.$element.on('selectstart', function () {
            return false;
        });

        $(this.refreshIcon).on('click', function () {
            that.text.text(that.options.barText);
            that.reset();
            if ($.isFunction(that.options.onRefresh)) that.options.onRefresh.call(that.$element);
        });

        var originX,
            originY,
            moveX,
            moveY,
            trail = [],
            isMouseDown = false;
        this.$element
            .on('touchstart pointerdown MSPointerDown', '.captcha-slider', function (e) {
                if (that.text.hasClass('text-danger')) return;
                originX = e.originalEvent.pageX || e.originalEvent.touches[0].pageX;
                originY = e.originalEvent.pageY || e.originalEvent.touches[0].pageY;
                isMouseDown = true;
            })
            .on('touchmove pointermove MSPointerMove', function (e) {
                if (isMouseDown) {
                    var eventX = e.originalEvent.pageX || e.originalEvent.touches[0].pageX;
                    var eventY = e.originalEvent.pageY || e.originalEvent.touches[0].pageY;
                    moveX = eventX - originX;
                    moveY = eventY - originY;
                    if (moveX >= 0 && moveX + 40 <= that.options.width) {
                        e.preventDefault ? e.preventDefault() : (e.returnValue = !1);
                        that.slider.style.left = moveX - 1 + 'px';
                        var blockLeft = ((that.options.width - 40 - 20) / (that.options.width - 40)) * moveX;
                        that.block.style.left = blockLeft + 'px';
                        that.sliderContainer.addClass('sliderContainer_active');
                        that.sliderMask.style.width = moveX + 4 + 'px';
                        trail.push(Math.round(moveY));
                    }
                }
            })
            .on('touchend touchcancel pointerup MSPointerUp', function () {
                if (isMouseDown && moveX >= 0) {
                    that.sliderContainer.removeClass('sliderContainer_active');
                    that.trail = trail;
                    var data = that.verify();
                    if (data.spliced && data.verified) {
                        that.sliderContainer.addClass('sliderContainer_success');
                        setTimeout(function () {
                            that.slider.style.left = 0;
                            that.block.style.left = 0;
                            that.sliderMask.style.width = 0;
                            that.sliderContainer.removeClass('sliderContainer_fail sliderContainer_success');
                        }, 500);
                        if ($.isFunction(that.options.onSuccess)) that.options.onSuccess.call(that.$element, data);
                    } else {
                        that.sliderContainer.addClass('sliderContainer_fail');
                        if ($.isFunction(that.options.onFail)) that.options.onFail.call(that.$element);
                        setTimeout(function () {
                            that.text.text(that.options.failedText);
                            that.reset();
                        }, 500);
                    }
                }
                moveX = 0;
                moveY = 0;
                isMouseDown = false;
            });
    };

    _proto.verify = function () {
        var arr = this.trail; // 拖动时y轴的移动距离
        var left = parseInt(this.block.style.left);
        var verified = false;
        var sum = function (x, y) {
            return x + y;
        };
        var square = function (x) {
            return x * x;
        };
        var average = arr.reduce(sum) / arr.length;
        var deviations = arr.map(function (x) {
            return x - average;
        });
        var stddev = Math.sqrt(deviations.map(square).reduce(sum) / arr.length);
        verified = stddev !== 0;
        return {
            spliced: Math.abs(left - this.x) < this.options.offset, //小于容差
            trail: arr,
            verified: verified, //距离
            distance: left,
        };
    };

    _proto.reset = function () {
        this.sliderContainer.removeClass('sliderContainer_fail sliderContainer_success');
        this.slider.style.left = 0;
        this.block.style.left = 0;
        this.sliderMask.style.width = 0;
        this.clean();
        this.text.attr('data-text', this.text.text());
        this.text.html(this.options.loadingText);
        this.img.setSrc();
    };
})(jQuery);

function SliderCaptchaModal(action, _btn) {
    var modal_id = 'SliderCaptcha';
    /*global captcha*/
    captcha._this = _btn;

    if (!$('#' + modal_id).length) {
        var modal_html = '<div id="' + modal_id + '" class="modal flex jc fade" tabindex="-1" role="dialog" aria-hidden="false" style="display: none;user-select: none;z-index: 100000000;background:rgba(0,0,0,.5);"><div class="modal-mini modal-dialog" style="width: 340px;">    <div class="modal-content"><div class="modal-body"><div class="modal-colorful-header colorful-bg c-yellow" style="height: 100px;"><button class="close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="colorful-make"></div><div class="text-center"><div class="em2x"><i class="fa fa-shield"></i></div><div class="mt6 em12">滑动以完成验证</div></div></div><div style="padding: 10px 0;margin-top: 100px;"><div class="slidercaptcha"></div></div></div></div></div></div>';
        $('body').append(modal_html);
        $('#' + modal_id + ' [data-dismiss="modal"]').on('click', hide);
    }

    function str($str) {
        var a = get_random(11, 40);
        var b = get_random(11, 40);
        return a + '' + getPass(a) + $str + getPass(b) + b;
    }

    function get_random(min, max) {
        return Math.round(Math.random() * (min - max) + max);
    }

    function getPass(len) {
        var tmpCh = '';
        for (var i = 0; i < len; i++) {
            tmpCh += String.fromCharCode(Math.floor(Math.random() * 26) + 'a'.charCodeAt(0));
        }
        return tmpCh;
    }

    function hide() {
        $('#' + modal_id).removeClass('in');
        setTimeout(function () {
            $('#' + modal_id).hide();
        }, 300);
    }

    function show() {
        $('#' + modal_id).show();
        setTimeout(function () {
            $('#' + modal_id).addClass('in');
            $('#' + modal_id + ' .slidercaptcha').sliderCaptcha({
                onSuccess: function (res) {
                    var token = captcha.token;
                    var randstr = captcha.rand_str;
                    delete captcha.rand_str;
                    delete captcha.token;

                    if (!token || !randstr) {
                        return notyf('滑块验证数据获取失败，网站疑似SSL或https设置有误，请对照浏览器报错进行排查', 'danger');
                    }

                    var randstr_a = get_random(1, 9);
                    var randstr_b = get_random(15, 25);
                    var _a = ~~token.substring(0, 2);
                    var _b = ~~token.substring(token.length - 2, token.length);
                    var _c = ~~token.substring(_a + 2, token.length - _b - 2);

                    //成功执行的函数
                    captcha.ticket = str(res.distance);
                    captcha.randstr = randstr_a + '' + randstr.substring(randstr_a, randstr_b) + randstr_b;
                    captcha.spliced = res.spliced;
                    captcha.trail = JSON.stringify(res.trail);

                    setTimeout(function () {
                        hide();
                        captcha._this.click();
                        return false;
                    }, 200);
                },
                setSrc: function () {
                    //图片获取函数
                    return _win.uri + '/img/captcha/' + Math.round(Math.random() * 20) + '.jpg';
                },
                getX: function (min, max) {
                    //图片获取函数
                    var $_x = get_random(min, max);
                    captcha.ticket = 0;
                    captcha.randstr = 0;
                    captcha.spliced = 0;

                    $.ajax({
                        url: _win.uri + '/action/captcha.php',
                        data: {
                            type: 'slider',
                            randstr: str($_x),
                        },
                    }).done(function (data) {
                        captcha.token = data.token;
                        captcha.rand_str = data.rand_str;
                        captcha.check = data.check;
                    });

                    return $_x;
                },
            });
        }, 30);
    }

    action === 'show' && show();
    action === 'hide' && hide();
}
