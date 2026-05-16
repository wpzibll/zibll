/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime: 2025-10-03 22:42:14
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

const poster = (function () {
    const DEBUG = 0;

    const WIDTH = 768;
    const HEIGHT = 1168;

    function init(config) {
        const $container = document.querySelector(config.selector);
        const $wrapper = createDom('div', 'id', 'wrapper');
        const $canvas = createDom('canvas', 'id', 'canvas', 'block');
        const $day = createDom('canvas', 'id', 'day');
        const $date = createDom('canvas', 'id', 'date');
        const $title = createDom('canvas', 'id', 'title');
        const $content = createDom('canvas', 'id', 'content');
        const $tags = createDom('canvas', 'id', 'tags');
        const $logo = createDom('canvas', 'id', 'logo');
        const $description = createDom('canvas', 'id', 'description');

        appendChilds($wrapper, $canvas, $day, $date, $title, $content, $logo, $tags, $description);
        $container.appendChild($wrapper);

        const date = new Date();

        // day
        const $weekarray = ['日', '一', '二', '三', '四', '五', '六'];
        const dayStyle = {
            font: '35px Arial',
            color: 'rgba(255, 255, 255, 1)',
            position: 'left',
        };
        drawOneline($day, dayStyle, '周' + $weekarray[date.getDay()]);

        // date
        const dateStyle = {
            font: '28px Arial',
            color: 'rgba(255, 255, 255, 1)',
            position: 'left',
        };
        drawOneline($date, dateStyle, date.getMonth() + 1 + ' / ' + date.getDate());

        // title canvas
        const titleStyle = {
            font: 'bold 35px Arial',
            lineHeight: 1.5,
            color: '#424242',
            length: 2,
            position: 'left',
        };
        titleStyle.font = (config.titleStyle && config.titleStyle.font) || titleStyle.font;
        titleStyle.color = (config.titleStyle && config.titleStyle.color) || titleStyle.color;
        titleStyle.position = (config.titleStyle && config.titleStyle.position) || titleStyle.position;
        drawMoreLines($title, titleStyle, config.title);

        // content canvas
        const contentStyle = {
            font: '25px Arial',
            lineHeight: 1.5,
            position: 'left',
            color: 'rgba(88, 88, 88, 1)',
        };
        contentStyle.font = (config.contentStyle && config.contentStyle.font) || contentStyle.font;
        contentStyle.color = (config.contentStyle && config.contentStyle.color) || contentStyle.color;
        contentStyle.lineHeight = (config.contentStyle && config.contentStyle.lineHeight) || contentStyle.lineHeight;
        contentStyle.position = (config.contentStyle && config.contentStyle.position) || contentStyle.position;
        drawMoreLines($content, contentStyle, config.content);

        // tags
        const tagsStyle = {
            font: '24px Roboto Slab',
            position: 'left',
            color: '#fc4e1f',
        };

        tagsStyle.color = (config.tagsStyle && config.tagsStyle.color) || tagsStyle.color;
        drawMoreLines($tags, tagsStyle, config.tags);

        // description
        const descriptionStyle = {
            font: '22px Arial',
            color: '#b4b4b4',
            lineHeight: 1.2,
            position: 'center',
        };
        drawMoreLines($description, descriptionStyle, config.description);

        // background image
        const image = new Image();
        image.crossOrigin = 'Anonymous';

        const logo = new Image();
        var logoLoadCount = 0;
        logo.crossOrigin = 'Anonymous';
        logo.src = config.logo;
        logo.onerror = function () {
            logoLoadCount++;
            if (logoLoadCount < 3) {
                logo.src = config.logo;
            }
        };

        const qrcode = new Image();
        qrcode.src = config.qrcode;

        var loadCount = 0;
        const setSrc = function () {
            if (loadCount == 0) {
                config.banner ? (image.src = config.banner) : (loadCount = 1);
            }
            if (loadCount == 1) {
                config.banner_default ? (image.src = config.banner_default) : (loadCount = 2);
            }
            if (loadCount == 2) {
                image.src = config.banner_spare;
            }
            onload();
        };
        const onload = function () {
            $canvas.width = WIDTH;
            $canvas.height = HEIGHT;
            image.onload = function () {
                const ctx = $canvas.getContext('2d');
                ctx.fillStyle = 'rgba(255, 255, 255, 1)';
                ctx.fillRect(0, 0, $canvas.width, $canvas.height);

                //绘制顶部主图
                var img_height = config.type === 'product' ? $canvas.width : $canvas.height / 2;
                const imgRect = coverImg($canvas.width, img_height, image.width, image.height);
                ctx.drawImage(image, imgRect.sx, imgRect.sy, imgRect.sWidth, imgRect.sHeight, 0, 0, $canvas.width, img_height);

                //绘制顶部日期
                ctx.drawImage($day, 0, 35);
                ctx.drawImage($date, 0, 85);

                //绘制标题竖线
                ctx.strokeStyle = '#00a1ff';
                ctx.lineWidth = 7;
                ctx.beginPath();
                ctx.lineCap = 'round';
                ctx.moveTo(40, img_height + 38);
                ctx.lineTo(40, img_height + 74);
                ctx.stroke();

                if (config.type === 'product') {
                    drawTextOverflow(ctx, config.title, $canvas.width, 1, 'bold 35px Arial', '#424242', 50, 20, img_height + 69);

                    //绘制价格
                    var price_color = config.is_points ? '#ff6f06' : '#FB3961';
                    var unit = config.is_points ? '积分' : '￥';
                    var price_height = img_height + 69 + 50 + 20;

                    var price_c, unit_c, original_price_c, original_unit_c;
                    if (config.is_points) {
                        price_c = drawTextOverflow(ctx, config.price, $canvas.width, 1, 'bold 40px Arial', price_color, 50, 0, price_height);
                        unit_c = drawTextOverflow(ctx, unit, $canvas.width, 1, '22px Arial', price_color, 50, 0 + 4 + price_c.width, price_height - 2);

                        if (config.original_price) {
                            original_price_c = drawTextOverflow(ctx, config.original_price, $canvas.width - price_c.width - unit_c.width, 1, '25px Arial', '#878787', 50, 8 + price_c.width + unit_c.width, price_height);
                            original_unit_c = drawTextOverflow(ctx, unit, $canvas.width - price_c.width - unit_c.width, 1, '22px Arial', '#878787', 50, 8 + 2 + price_c.width + unit_c.width + original_price_c.width, price_height - 1);

                            //绘制划掉原价
                            ctx.strokeStyle = '#878787';
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            ctx.lineCap = 'round';
                            ctx.moveTo(8 + price_c.width + unit_c.width + 42, price_height - 15);
                            ctx.lineTo(8 + price_c.width + unit_c.width + 42 + original_unit_c.width + original_price_c.width, price_height - 3);
                            ctx.stroke();
                        }
                    } else {
                        unit_c = drawTextOverflow(ctx, unit, $canvas.width, 1, '26px Arial', price_color, 50, -10, price_height - 1);
                        price_c = drawTextOverflow(ctx, config.price, $canvas.width, 1, 'bold 40px Arial', price_color, 50, -10 + unit_c.width, price_height);

                        if (config.original_price) {
                            original_unit_c = drawTextOverflow(ctx, unit, $canvas.width - price_c.width - unit_c.width, 1, '23px Arial', '#878787', 50, 0 + price_c.width + unit_c.width, price_height - 1);
                            original_price_c = drawTextOverflow(ctx, config.original_price, $canvas.width - price_c.width - unit_c.width, 1, '25px Arial', '#878787', 50, 0 + price_c.width + unit_c.width + original_unit_c.width, price_height);

                            //绘制划掉原价
                            ctx.strokeStyle = '#878787';
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            ctx.lineCap = 'round';
                            ctx.moveTo(0 + price_c.width + unit_c.width + 42, price_height - 15);
                            ctx.lineTo(0 + price_c.width + unit_c.width + 42 + original_unit_c.width + original_price_c.width, price_height - 3);
                            ctx.stroke();
                        }
                    }

                    //绘制标签
                    var price_width = price_c.width + unit_c.width + (original_unit_c && original_unit_c.width ? original_unit_c.width : 0) + (original_price_c && original_price_c.width ? original_price_c.width : 0);
                    var tags_width = $canvas.width - price_width - 20;

                    if (tags_width > 200) {
                        drawTextOverflow(ctx, config.tags, tags_width, 1, '25px Arial', '#fc4545', 50, price_width + 50, price_height);
                    }
                } else {
                    ctx.drawImage($title, 20, img_height + 40);
                    //绘制标签
                    ctx.drawImage($tags, 0, img_height + 160);

                    //绘制内容
                    ctx.drawImage($content, 0, img_height + 220);
                }

                //绘制底部logo和二维码
                ctx.strokeStyle = 'rgba(122, 122, 122, 0.2)';
                ctx.lineWidth = 2;
                ctx.rect(30, $canvas.height - 230, $canvas.width - 60, 200);
                ctx.stroke();

                const logoRect = containImg(80, $canvas.height - 190, 300, 100, logo.width, logo.height);
                ctx.drawImage(logo, logoRect.dx, logoRect.dy, logoRect.dWidth, logoRect.dHeight);

                ctx.drawImage(qrcode, $canvas.width - 200, $canvas.height - 200, 120, 120);

                ctx.drawImage($description, 0, $canvas.height - 60);

                const img = new Image();
                img.crossOrigin = 'Anonymous';
                img.src = $canvas.toDataURL('image/png');
                const radio = config.radio || 0.7;
                img.width = WIDTH * radio;
                img.height = HEIGHT * radio;
                img.className = 'loaded-img';
                ctx.clearRect(0, 0, $canvas.width, $canvas.height);
                $canvas.style.display = 'none';

                if ($container.querySelector('.loaded-img')) {
                    $container.querySelector('.loaded-img').src = img.src;
                } else {
                    $container.appendChild(img);
                }

                $container.removeChild($wrapper);
                $container.classList.add('loaded');
                $.isFunction(config.callback) && config.callback($container);
            };
            image.onerror = function () {
                loadCount++;
                if (loadCount >= 3) {
                    return $.isFunction(config.onerror) && config.onerror($container);
                }
                setSrc();
            };
        };

        setSrc();
    }

    /**
     * canvas多行文本溢出
     * @param {Object} context - canvas组件的绘图上下文
     * @param {String} text - 文本内容
     * @param {Number} maxWidth - 文本最大宽度
     * @param {Number} maxRow - 文本最多显示行数
     * @param {Number} fontSize - 字体样式
     * @param {String} color - 文本颜色
     * @param {Number} lineHeight - 文本行高
     * @param {Number} x - 文本的x坐标
     * @param {Number} y - 文本的y坐标
     */
    function drawTextOverflow(context, text, maxWidth, maxRow, font, color, lineHeight, x, y) {
        let arr = [];
        let temp = '';
        let row = [];
        maxWidth = maxWidth - 120;
        x = x + 38;

        text = text.replace(/[\r\n]/g, ''); // 去除回车换行符
        arr = text.split('');
        context.fillStyle = color;
        context.font = font;

        if (context.measureText(text).width <= maxWidth) {
            row.push(text);
        } else {
            for (let i = 0; i < arr.length; i++) {
                // 超出最大行数且字符有剩余，添加...
                if (row.length == maxRow && i < arr.length - 1) {
                    row[row.length - 1] += '...';
                    break;
                }

                // 字符换行计算
                if (context.measureText(temp).width < maxWidth) {
                    temp += arr[i];
                    // 遍历到最后一位字符
                    if (i === arr.length - 1) {
                        row.push(temp);
                    }
                } else {
                    i--; // 防止字符丢失
                    row.push(temp);
                    temp = '';
                }
            }
        }
        // 绘制文本
        var width = 0;
        for (let i = 0; i < row.length; i++) {
            width = Math.max(width, context.measureText(row[i]).width);
            context.fillText(row[i], x, y + i * lineHeight, maxWidth);
        }
        return {
            width: width,
            height: row.length * lineHeight,
        };
    }

    function containImg(sx, sy, box_w, box_h, source_w, source_h) {
        var dx = sx,
            dy = sy,
            dWidth = box_w,
            dHeight = box_h;
        if (source_w > source_h || (source_w == source_h && box_w < box_h)) {
            dHeight = (source_h * dWidth) / source_w;
            dy = sy + (box_h - dHeight) / 2;
        } else if (source_w < source_h || (source_w == source_h && box_w > box_h)) {
            dWidth = (source_w * dHeight) / source_h;
            dx = sx + (box_w - dWidth) / 2;
        }
        return {
            dx,
            dy,
            dWidth,
            dHeight,
        };
    }

    function coverImg(box_w, box_h, source_w, source_h) {
        var sx = 0,
            sy = 0,
            sWidth = source_w,
            sHeight = source_h;
        if (source_w > source_h || (source_w == source_h && box_w < box_h)) {
            sWidth = (box_w * sHeight) / box_h;
            sx = (source_w - sWidth) / 2;
        } else if (source_w < source_h || (source_w == source_h && box_w > box_h)) {
            sHeight = (box_h * sWidth) / box_w;
            sy = (source_h - sHeight) / 2;
        }
        return {
            sx,
            sy,
            sWidth,
            sHeight,
        };
    }

    function createDom(name, key, value, display = 'none') {
        const $dom = document.createElement(name);
        $dom.setAttribute(key, value);
        $dom.style.display = display;
        $dom.width = WIDTH;
        return $dom;
    }

    function appendChilds(parent, ...doms) {
        doms.forEach((dom) => {
            parent.appendChild(dom);
        });
    }

    function drawOneline(canvas, style, content) {
        const ctx = canvas.getContext('2d');
        canvas.height = parseInt(style.font.match(/\d+/), 10) + 20;
        ctx.font = style.font;
        ctx.fillStyle = style.color;
        ctx.textBaseline = 'top';

        let lineWidth = 0;
        let idx = 0;
        let truncated = false;
        for (let i = 0; i < content.length; i++) {
            lineWidth += ctx.measureText(content[i]).width;
            if (lineWidth > canvas.width - 60) {
                truncated = true;
                idx = i;
                break;
            }
        }

        let padding = 30;

        if (truncated) {
            content = content.substring(0, idx);
            padding = canvas.width / 2 - lineWidth / 2;
        }

        if (DEBUG) {
            ctx.strokeStyle = '#6fda92';
            ctx.strokeRect(0, 0, canvas.width, canvas.height);
        }

        if (style.position === 'center') {
            ctx.textAlign = 'center';
            ctx.fillText(content, canvas.width / 2, 2);
        } else if (style.position === 'left') {
            ctx.fillText(content, padding, 2);
        } else {
            ctx.textAlign = 'right';
            ctx.fillText(content, canvas.width - padding, 2);
        }
    }

    function drawMoreLines(canvas, style, content) {
        const ctx = canvas.getContext('2d');
        const fontHeight = parseInt(style.font.match(/\d+/), 10);

        if (DEBUG) {
            ctx.strokeStyle = '#6fda92';
            ctx.strokeRect(0, 0, canvas.width, canvas.height);
        }

        ctx.font = style.font;
        ctx.fillStyle = style.color;
        ctx.textBaseline = 'top';
        ctx.textAlign = 'center';

        let alignX = 0;

        if (style.position === 'center') {
            alignX = canvas.width / 2;
        } else if (style.position === 'left') {
            ctx.textAlign = 'left';
            alignX = 40;
        } else {
            ctx.textAlign = 'right';
            alignX = canvas.width - 40;
        }

        let lineWidth = 0;
        let lastSubStrIndex = 0;
        let offsetY = 2;
        for (let i = 0; i < content.length; i++) {
            lineWidth += ctx.measureText(content[i]).width;
            if (lineWidth > canvas.width - 120) {
                ctx.fillText(content.substring(lastSubStrIndex, i), alignX, offsetY);
                offsetY += fontHeight * style.lineHeight;
                lineWidth = 0;
                lastSubStrIndex = i;
            }
            if (i === content.length - 1) {
                ctx.fillText(content.substring(lastSubStrIndex, i + 1), alignX, offsetY);
            }
        }
    }

    return {
        init,
    };
})();

$('body').on('click', '[poster-share]', function () {
    var _this = $(this);
    var _id = _this.attr('poster-share');
    var modal_id = 'modal_poster_' + _id;
    var modal_selector = '#' + modal_id;

    var modal_html =
        '<div class="modal fade flex jc" id="' +
        modal_id +
        '" tabindex="-1" role="dialog" aria-hidden="false">\
    <div class="modal-dialog poster-share hover-show" role="document">\
    <div class="modal-content" style="height:220px;overflow:hidden">\
    <div class="poster-imgbox hover-show">\
    </div>\
    <div class="flex jc loading-mask absolute main-bg radius8"><div class="loading zts fa-2x"></div><div class="muted-2-color box-body">正在生成图片，请稍候...</div>\
    </div>\
    </div>\
    <div class="padding-6 abs-center hover-show-con text-center" style="top:auto;"><a type="button" href="javascript:;" class="but cir jb-blue poster-download hide"><i class="fa fa-download" aria-hidden="true"></i></a><button data-dismiss="modal" class="but cir jb-yellow ml10"><svg aria-hidden="true" class="icon em12"><use xlink:href="#icon-close"></use></svg></button></div>\
    </div>\
    </div>';

    $('.modal:not(' + modal_selector + ')').modal('hide');
    var _modal = $(modal_selector);

    if (_modal.length) {
        _modal.modal('show');
        return false;
    }

    $('body').append(modal_html);
    _modal = $(modal_selector);

    _modal.modal('show');

    var ajax_data = {
        action: 'poster_share_data',
        id: _id,
    };

    function posterDownload(e) {
        if (e == null) {
            return;
        }

        _modal
            .find('.poster-download')
            .attr('href', _modal.find('img').attr('src'))
            .attr('download', 'poster_' + _id + '.png')
            .removeClass('hide');

        var b_height = _modal.find('.poster-imgbox').outerHeight();
        _modal.find('.loading-mask').fadeOut(200);
        _modal.find('.modal-content').css({
            height: b_height,
            transition: '.2s',
        });
        setTimeout(function () {
            _modal.find('.modal-content').css({
                height: '',
                overflow: '',
                transition: '',
            });
        }, 200);
    }

    zib_ajax(
        _this,
        ajax_data,
        function (_poster) {
            poster.init({
                banner: _poster.banner,
                banner_default: _poster.banner_default,
                banner_spare: _poster.banner_spare,
                selector: modal_selector + ' .poster-imgbox',
                title: _poster.title,
                content: _poster.content,
                logo: _poster.logo,
                tags: _poster.tags,
                description: _poster.description,
                qrcode: _poster.qrcode,
                type: _poster.type,
                is_points: _poster.is_points,
                price: _poster.price,
                original_price: _poster.original_price,
                onerror: function () {
                    notyf('海报加载失败', 'danger');
                },
                callback: posterDownload,
            });
        },
        'stop',
        true
    );
});
