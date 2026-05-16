/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-27 22:39:18
 * @LastEditTime: 2025-02-03 15:38:52
 */

'use strict';
$.fn.minitouch = function (options) {
    // 合并默认选项和用户提供的选项
    options = $.extend({
        direction: 'bottom',
        selector: '',
        start_selector: '',
        depreciation: 50,
        stop: false,
        onStart: false,
        onIng: false,
        onEnd: false,
        inEnd: false,
    }, options);

    var $element = $(this); // 当前元素
    var isStop = false; // 是否停止拖动
    var threshold = options.depreciation; // 拖动阈值
    var initialX = 0, initialY = 0; // 初始触摸坐标
    var finalX = 0, finalY = 0; // 结束触摸坐标
    var angle = 0; // 触摸角度
    var deltaX = 0, deltaY = 0; // 移动距离
    var isDragging = false; // 是否正在拖动

    // CSS过渡效果函数
    var applyCssTransition = function (element, translateX, translateY, isDragging, cursorStyle) {
        var transformValue, cssProperties = {};
        if (isDragging) {
            translateX += "px";
            translateY += "px";
            transformValue = "translate3D(" + translateX + "," + translateY + " , 0)";
            var prefix = getCssTransitionSupport();
            cssProperties[prefix + "transform"] = transformValue;
            cssProperties[prefix + "transition"] = prefix + "transform 0s linear";
            cssProperties["cursor"] = cursorStyle;
        } else {
            cssProperties[getCssTransitionSupport() + "transform"] = "";
            cssProperties[getCssTransitionSupport() + "transition"] = "";
        }
        element.css(cssProperties);
    }

    // 检测CSS过渡支持
    var getCssTransitionSupport = function () {
        var style = document.body.style || document.documentElement.style;
        return style.WebkitTransition ? "-webkit-" : 
               style.MozTransition ? "-moz-" : 
               style.OTransition ? "-o-" : 
               style.transition ? "" : 
               void 0;
    }

    var touchSelector = options.start_selector || options.selector;

    // 触摸开始事件
    $element.on('touchstart pointerdown MSPointerDown', touchSelector, function (event) {
        initialX = initialY = finalX = finalY = angle = deltaX = deltaY = 0;
        initialX = event.originalEvent.pageX || event.originalEvent.touches[0].pageX;
        initialY = event.originalEvent.pageY || event.originalEvent.touches[0].pageY;
        isDragging = true;

        // 兼容swiper
        if ($(event.target).parentsUntil(touchSelector, '.swiper-container,.scroll-x').length) {
            isDragging = false;
        }
    })
    // 触摸移动事件
    .on("touchmove pointermove MSPointerMove", touchSelector, function (event) {
        var $moveElement = options.start_selector ? 
                           (options.selector ? $element.find(options.selector) : $element.find(options.start_selector)) : 
                           $(this);

        if ($.isFunction(options.stop)) {
            isStop = options.stop($element, $moveElement, initialX, initialY);
        }

        if (isDragging && !isStop) {
            finalX = event.originalEvent.pageX || event.originalEvent.touches[0].pageX;
            finalY = event.originalEvent.pageY || event.originalEvent.touches[0].pageY;
            deltaX = finalX - initialX;
            deltaY = finalY - initialY;
            angle = 180 * Math.atan2(deltaY, deltaX) / Math.PI;

            // 根据方向限制移动距离
            if (options.direction === "right") {
                deltaY = 0;
                deltaX = (angle > -40 && angle < 40 && deltaX > 0) ? deltaX : 0;
            } else if (options.direction === "left") {
                deltaY = 0;
                deltaX = (angle > 150 || angle < -150) && deltaX < 0 ? deltaX : 0;
            } else if (options.direction === "top") {
                deltaX = 0;
                deltaY = (angle > -130 && angle < -50 && deltaY < 0) ? deltaY : 0;
            } else if (options.direction === "bottom") {
                deltaX = 0;
                deltaY = (angle > 50 && angle < 130 && deltaY > 0) ? deltaY : 0;
            }

            if (deltaX !== 0 || deltaY !== 0) {
                event.preventDefault ? event.preventDefault() : event.returnValue = false;
                applyCssTransition($moveElement, deltaX, deltaY, isDragging, 'grab');
                $.isFunction(options.onIng) && options.onIng($element, $moveElement, deltaX, deltaY);
            }
        }
    })
    // 触摸结束事件
    .on('touchend touchcancel pointerup MSPointerUp', touchSelector, function () {
        var $moveElement = options.start_selector ? 
                           (options.selector ? $element.find(options.selector) : $element.find(options.start_selector)) : 
                           $(this);

        if (isDragging && !isStop) {
            applyCssTransition($moveElement, 0, 0, "null", '');
            $.isFunction(options.inEnd) && options.inEnd($element, $moveElement, deltaX, deltaY);
            if (Math.abs(deltaX) > threshold || Math.abs(deltaY) > threshold) {
                $.isFunction(options.onEnd) && options.onEnd($element, $moveElement, deltaX, deltaY);
            }
            initialX = initialY = finalX = finalY = angle = deltaX = deltaY = 0;
            isDragging = false;
        }
    });
}