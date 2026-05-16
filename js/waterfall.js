/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-18 16:05:57
 * @LastEditTime : 2025-07-18 16:06:22
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题|瀑布流布局
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

$.fn.waterfall = function () {
    this.each(function () {
        var $this = $(this);
        initWaterfall($this);

        if ($this.data('waterfall-init')) return;

        $this.data('waterfall-init', true);

        $(window).on(
            'resize',
            debounce(function () {
                initWaterfall($this);
            }, 100)
        );

        $this.on(
            'lazyloaded',
            'img',
            debounce(function () {
                initWaterfall($this);
            }, 50)
        );

        $this.on('post_ajax.ed', function () {
            initWaterfall($this);
        });
    });

    //瀑布流布局
    function initWaterfall(container) {
        if (!container.length) return;

        const items = container.find('.card');
        if (!items.length) return;

        //获取列数和间距
        const containerWidth = container.width();
        const itemWidth = items.eq(0).outerWidth();
        const columns = Math.floor(containerWidth / itemWidth);
        var horizontalGap = parseInt(container.data('h-gap')); // 水平间距
        var verticalGap = parseInt(container.data('v-gap')); // 垂直间距
        if (!horizontalGap) {
            horizontalGap = parseInt(items.eq(0).css('margin-left')) + parseInt(items.eq(0).css('margin-right'));
        }
        if (!verticalGap) {
            verticalGap = parseInt(items.eq(0).css('margin-top')) + parseInt(items.eq(0).css('margin-bottom'));
        }

        //计算第一个项目距离容器顶部的位置,减去自身margin-top
        const firstItemTop = items.eq(0).offset().top - container.offset().top - parseInt(items.eq(0).css('margin-top'));

        //初始化列高度数组
        let colHeights = new Array(columns).fill(firstItemTop);

        //遍历所有子项目
        items.each(function () {
            const $item = $(this);

            //找到最短的列
            const minHeight = Math.min(...colHeights);
            const minCol = colHeights.indexOf(minHeight);

            //计算位置,加入间距
            const left = minCol * itemWidth + horizontalGap * minCol;
            const top = minHeight + (minHeight > 0 ? verticalGap : 0);

            //设置位置
            $item
                .css({
                    position: 'absolute',
                    left: '0',
                    top: '0',
                    transform: 'translate3d(' + left + 'px,' + top + 'px,0)',
                })
                .addClass('waterfall-item');

            //更新列高度
            colHeights[minCol] += $item.height() + verticalGap;
        });

        //设置容器高度,考虑底部间距
        container.css('height', Math.max(...colHeights) + verticalGap + 'px');
    }
};
