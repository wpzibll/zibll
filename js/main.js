/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime : 2026-05-05 23:27:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//libs-插件
//jQuery.cookie
jQuery.cookie = function (e, o, t) {
    if (void 0 === o) {
        var i = null;
        if (document.cookie && '' != document.cookie)
            for (var r = document.cookie.split(';'), n = 0; n < r.length; n++) {
                var c = jQuery.trim(r[n]);
                if (c.substring(0, e.length + 1) == e + '=') {
                    i = decodeURIComponent(c.substring(e.length + 1));
                    break;
                }
            }
        return i;
    }
    (t = t || {}), null === o && ((o = ''), (t.expires = -1));
    var a = '';
    if (t.expires && ('number' == typeof t.expires || t.expires.toUTCString)) {
        var l;
        'number' == typeof t.expires ? ((l = new Date()), l.setTime(l.getTime() + 24 * t.expires * 60 * 60 * 1e3)) : (l = t.expires), (a = '; expires=' + l.toUTCString());
    }
    var u = t.path ? '; path=' + t.path : '',
        s = t.domain ? '; domain=' + t.domain : '',
        m = t.secure ? '; secure' : '';
    document.cookie = [e, '=', encodeURIComponent(o), a, u, s, m].join('');
};
var lcs = {
    get: function (e) {
        return window.localStorage ? localStorage.getItem(e) : $.cookie(e);
    },
    set: function (e, o) {
        window.localStorage ? (localStorage[e] = o) : $.cookie(e, o);
    },
    remove: function (e) {
        window.localStorage ? localStorage.removeItem(e) : $.cookie(e, '');
    },
};

//lazySizes
(function (window, factory) {
    var lazySizes = factory(window, window.document, Date);
    window.lazySizes = lazySizes;
    if (typeof module == 'object' && module.exports) {
        module.exports = lazySizes;
    }
})(typeof window != 'undefined' ? window : {}, function l(window, document, Date) {
    // Pass in the windoe Date function also for SSR because the Date class can be lost
    'use strict';
    /*jshint eqnull:true */

    var lazysizes, lazySizesCfg;

    (function () {
        var prop;

        var lazySizesDefaults = {
            lazyClass: 'lazyload',
            loadedClass: 'lazyloaded',
            loadingClass: 'lazyloading',
            preloadClass: 'lazypreload',
            loadafterClass: 'lazyloadafter',
            errorClass: 'lazyerror',
            //strictClass: 'lazystrict',
            autosizesClass: 'lazyautosizes',
            srcAttr: 'data-src',
            srcsetAttr: 'data-srcset',
            sizesAttr: 'data-sizes',
            //preloadAfterLoad: false,
            minSize: 40,
            customMedia: {},
            init: true,
            expFactor: 1.5,
            hFac: 0.8,
            loadMode: 2,
            loadHidden: true,
            ricTimeout: 0,
            throttleDelay: 125,
        };

        lazySizesCfg = window.lazySizesConfig || window.lazysizesConfig || {};

        for (prop in lazySizesDefaults) {
            if (!(prop in lazySizesCfg)) {
                lazySizesCfg[prop] = lazySizesDefaults[prop];
            }
        }
    })();

    if (!document || !document.getElementsByClassName) {
        return {
            init: function () {},
            cfg: lazySizesCfg,
            noSupport: true,
        };
    }

    var docElem = document.documentElement;

    var supportPicture = window.HTMLPictureElement;

    var _addEventListener = 'addEventListener';

    var _getAttribute = 'getAttribute';

    /**
     * Update to bind to window because 'this' becomes null during SSR
     * builds.
     */
    var addEventListener = window[_addEventListener].bind(window);

    var setTimeout = window.setTimeout;

    var requestAnimationFrame = window.requestAnimationFrame || setTimeout;

    var requestIdleCallback = window.requestIdleCallback;

    var regPicture = /^picture$/i;

    var loadEvents = ['load', 'error', 'lazyincluded', '_lazyloaded'];

    var regClassCache = {};

    var forEach = Array.prototype.forEach;

    var hasClass = function (ele, cls) {
        if (!regClassCache[cls]) {
            regClassCache[cls] = new RegExp('(\\s|^)' + cls + '(\\s|$)');
        }
        return regClassCache[cls].test(ele[_getAttribute]('class') || '') && regClassCache[cls];
    };

    var addClass = function (ele, cls) {
        if (!hasClass(ele, cls)) {
            ele.setAttribute('class', (ele[_getAttribute]('class') || '').trim() + ' ' + cls);
        }
    };

    var removeClass = function (ele, cls) {
        var reg;
        if ((reg = hasClass(ele, cls))) {
            ele.setAttribute('class', (ele[_getAttribute]('class') || '').replace(reg, ' '));
        }
    };

    var addRemoveLoadEvents = function (dom, fn, add) {
        var action = add ? _addEventListener : 'removeEventListener';
        if (add) {
            addRemoveLoadEvents(dom, fn);
        }
        loadEvents.forEach(function (evt) {
            dom[action](evt, fn);
        });
    };

    var triggerEvent = function (elem, name, detail, noBubbles, noCancelable) {
        var event = document.createEvent('Event');

        if (!detail) {
            detail = {};
        }

        detail.instance = lazysizes;

        event.initEvent(name, !noBubbles, !noCancelable);

        event.detail = detail;

        elem.dispatchEvent(event);
        return event;
    };

    var updatePolyfill = function (el, full) {
        var polyfill;
        if (!supportPicture && (polyfill = window.picturefill || lazySizesCfg.pf)) {
            if (full && full.src && !el[_getAttribute]('srcset')) {
                el.setAttribute('srcset', full.src);
            }
            polyfill({ reevaluate: true, elements: [el] });
        } else if (full && full.src) {
            el.src = full.src;
        }
    };

    var getCSS = function (elem, style) {
        return (getComputedStyle(elem, null) || {})[style];
    };

    var getWidth = function (elem, parent, width) {
        width = width || elem.offsetWidth;

        while (width < lazySizesCfg.minSize && parent && !elem._lazysizesWidth) {
            width = parent.offsetWidth;
            parent = parent.parentNode;
        }

        return width;
    };

    var rAF = (function () {
        var running, waiting;
        var firstFns = [];
        var secondFns = [];
        var fns = firstFns;

        var run = function () {
            var runFns = fns;

            fns = firstFns.length ? secondFns : firstFns;

            running = true;
            waiting = false;

            while (runFns.length) {
                runFns.shift()();
            }

            running = false;
        };

        var rafBatch = function (fn, queue) {
            if (running && !queue) {
                fn.apply(this, arguments);
            } else {
                fns.push(fn);

                if (!waiting) {
                    waiting = true;
                    (document.hidden ? setTimeout : requestAnimationFrame)(run);
                }
            }
        };

        rafBatch._lsFlush = run;

        return rafBatch;
    })();

    var rAFIt = function (fn, simple) {
        return simple
            ? function () {
                  rAF(fn);
              }
            : function () {
                  var that = this;
                  var args = arguments;
                  rAF(function () {
                      fn.apply(that, args);
                  });
              };
    };

    var throttle = function (fn) {
        var running;
        var lastTime = 0;
        var gDelay = lazySizesCfg.throttleDelay;
        var rICTimeout = lazySizesCfg.ricTimeout;
        var run = function () {
            running = false;
            lastTime = Date.now();
            fn();
        };
        var idleCallback =
            requestIdleCallback && rICTimeout > 49
                ? function () {
                      requestIdleCallback(run, { timeout: rICTimeout });

                      if (rICTimeout !== lazySizesCfg.ricTimeout) {
                          rICTimeout = lazySizesCfg.ricTimeout;
                      }
                  }
                : rAFIt(function () {
                      setTimeout(run);
                  }, true);
        return function (isPriority) {
            var delay;

            if ((isPriority = isPriority === true)) {
                rICTimeout = 33;
            }

            if (running) {
                return;
            }

            running = true;

            delay = gDelay - (Date.now() - lastTime);

            if (delay < 0) {
                delay = 0;
            }

            if (isPriority || delay < 9) {
                idleCallback();
            } else {
                setTimeout(idleCallback, delay);
            }
        };
    };

    //based on http://modernjavascript.blogspot.de/2013/08/building-better-debounce.html
    var debounce = function (func) {
        var timeout, timestamp;
        var wait = 99;
        var run = function () {
            timeout = null;
            func();
        };
        var later = function () {
            var last = Date.now() - timestamp;

            if (last < wait) {
                setTimeout(later, wait - last);
            } else {
                (requestIdleCallback || run)(run);
            }
        };

        return function () {
            timestamp = Date.now();

            if (!timeout) {
                timeout = setTimeout(later, wait);
            }
        };
    };

    var loader = (function () {
        var preloadElems, isCompleted, resetPreloadingTimer, loadMode, started;

        var eLvW, elvH, eLtop, eLleft, eLright, eLbottom, isBodyHidden;

        var regImg = /^img$/i;
        var regIframe = /^iframe$/i;

        var supportScroll = 'onscroll' in window && !/(gle|ing)bot/.test(navigator.userAgent);

        var shrinkExpand = 0;
        var currentExpand = 0;

        var isLoading = 0;
        var lowRuns = -1;

        var resetPreloading = function (e) {
            isLoading--;
            if (!e || isLoading < 0 || !e.target) {
                isLoading = 0;
            }
        };

        var isVisible = function (elem) {
            if (isBodyHidden == null) {
                isBodyHidden = getCSS(document.body, 'visibility') == 'hidden';
            }

            return isBodyHidden || !(getCSS(elem.parentNode, 'visibility') == 'hidden' && getCSS(elem, 'visibility') == 'hidden');
        };

        var isNestedVisible = function (elem, elemExpand) {
            var outerRect;
            var parent = elem;
            var visible = isVisible(elem);

            eLtop -= elemExpand;
            eLbottom += elemExpand;
            eLleft -= elemExpand;
            eLright += elemExpand;

            while (visible && (parent = parent.offsetParent) && parent != document.body && parent != docElem) {
                visible = (getCSS(parent, 'opacity') || 1) > 0;

                if (visible && getCSS(parent, 'overflow') != 'visible') {
                    outerRect = parent.getBoundingClientRect();
                    visible = eLright > outerRect.left && eLleft < outerRect.right && eLbottom > outerRect.top - 1 && eLtop < outerRect.bottom + 1;
                }
            }

            return visible;
        };

        var checkElements = function () {
            var eLlen, i, rect, autoLoadElem, loadedSomething, elemExpand, elemNegativeExpand, elemExpandVal, beforeExpandVal, defaultExpand, preloadExpand, hFac;
            var lazyloadElems = lazysizes.elements;

            if ((loadMode = lazySizesCfg.loadMode) && isLoading < 8 && (eLlen = lazyloadElems.length)) {
                i = 0;

                lowRuns++;

                for (; i < eLlen; i++) {
                    if (!lazyloadElems[i] || lazyloadElems[i]._lazyRace) {
                        continue;
                    }

                    if (!supportScroll || (lazysizes.prematureUnveil && lazysizes.prematureUnveil(lazyloadElems[i]))) {
                        unveilElement(lazyloadElems[i]);
                        continue;
                    }

                    if (!(elemExpandVal = lazyloadElems[i][_getAttribute]('data-expand')) || !(elemExpand = elemExpandVal * 1)) {
                        elemExpand = currentExpand;
                    }

                    if (!defaultExpand) {
                        defaultExpand = !lazySizesCfg.expand || lazySizesCfg.expand < 1 ? (docElem.clientHeight > 500 && docElem.clientWidth > 500 ? 500 : 370) : lazySizesCfg.expand;

                        lazysizes._defEx = defaultExpand;

                        preloadExpand = defaultExpand * lazySizesCfg.expFactor;
                        hFac = lazySizesCfg.hFac;
                        isBodyHidden = null;

                        if (currentExpand < preloadExpand && isLoading < 1 && lowRuns > 2 && loadMode > 2 && !document.hidden) {
                            currentExpand = preloadExpand;
                            lowRuns = 0;
                        } else if (loadMode > 1 && lowRuns > 1 && isLoading < 6) {
                            currentExpand = defaultExpand;
                        } else {
                            currentExpand = shrinkExpand;
                        }
                    }

                    if (beforeExpandVal !== elemExpand) {
                        eLvW = innerWidth + elemExpand * hFac;
                        elvH = innerHeight + elemExpand;
                        elemNegativeExpand = elemExpand * -1;
                        beforeExpandVal = elemExpand;
                    }

                    rect = lazyloadElems[i].getBoundingClientRect();

                    if ((eLbottom = rect.bottom) >= elemNegativeExpand && (eLtop = rect.top) <= elvH && (eLright = rect.right) >= elemNegativeExpand * hFac && (eLleft = rect.left) <= eLvW && (eLbottom || eLright || eLleft || eLtop) && (lazySizesCfg.loadHidden || isVisible(lazyloadElems[i])) && ((isCompleted && isLoading < 3 && !elemExpandVal && (loadMode < 3 || lowRuns < 4)) || isNestedVisible(lazyloadElems[i], elemExpand))) {
                        unveilElement(lazyloadElems[i]);
                        loadedSomething = true;
                        if (isLoading > 9) {
                            break;
                        }
                    } else if (!loadedSomething && isCompleted && !autoLoadElem && isLoading < 4 && lowRuns < 4 && loadMode > 2 && (preloadElems[0] || lazySizesCfg.preloadAfterLoad) && (preloadElems[0] || (!elemExpandVal && (eLbottom || eLright || eLleft || eLtop || lazyloadElems[i][_getAttribute](lazySizesCfg.sizesAttr) != 'auto')))) {
                        autoLoadElem = preloadElems[0] || lazyloadElems[i];
                    }
                }

                if (autoLoadElem && !loadedSomething) {
                    unveilElement(autoLoadElem);
                }
            }
        };

        var throttledCheckElements = throttle(checkElements);

        var switchLoadingClass = function (e) {
            var elem = e.target;

            if (elem._lazyCache) {
                delete elem._lazyCache;
                return;
            }

            resetPreloading(e);
            addClass(elem, lazySizesCfg.loadedClass);
            removeClass(elem, lazySizesCfg.loadingClass);
            addRemoveLoadEvents(elem, rafSwitchLoadingClass);
            triggerEvent(elem, 'lazyloaded');

            //添加loadafterClass，防止后期二次触发动画
            setTimeout(function () {
                addClass(elem, lazySizesCfg.loadafterClass);
            }, 700);
        };
        var rafedSwitchLoadingClass = rAFIt(switchLoadingClass);
        var rafSwitchLoadingClass = function (e) {
            rafedSwitchLoadingClass({ target: e.target });
        };

        var changeIframeSrc = function (elem, src) {
            try {
                elem.contentWindow.location.replace(src);
            } catch (e) {
                elem.src = src;
            }
        };

        var handleSources = function (source) {
            var customMedia;

            var sourceSrcset = source[_getAttribute](lazySizesCfg.srcsetAttr);

            if ((customMedia = lazySizesCfg.customMedia[source[_getAttribute]('data-media') || source[_getAttribute]('media')])) {
                source.setAttribute('media', customMedia);
            }

            if (sourceSrcset) {
                source.setAttribute('srcset', sourceSrcset);
            }
        };

        var lazyUnveil = rAFIt(function (elem, detail, isAuto, sizes, isImg) {
            var src, srcset, parent, isPicture, event, firesLoad;

            if (!(event = triggerEvent(elem, 'lazybeforeunveil', detail)).defaultPrevented) {
                if (sizes) {
                    if (isAuto) {
                        addClass(elem, lazySizesCfg.autosizesClass);
                    } else {
                        elem.setAttribute('sizes', sizes);
                    }
                }

                srcset = elem[_getAttribute](lazySizesCfg.srcsetAttr);
                src = elem[_getAttribute](lazySizesCfg.srcAttr);

                if (isImg) {
                    parent = elem.parentNode;
                    isPicture = parent && regPicture.test(parent.nodeName || '');
                }

                firesLoad = detail.firesLoad || ('src' in elem && (srcset || src || isPicture));

                event = { target: elem };

                addClass(elem, lazySizesCfg.loadingClass);

                if (firesLoad) {
                    clearTimeout(resetPreloadingTimer);
                    resetPreloadingTimer = setTimeout(resetPreloading, 2500);
                    addRemoveLoadEvents(elem, rafSwitchLoadingClass, true);
                }

                if (isPicture) {
                    forEach.call(parent.getElementsByTagName('source'), handleSources);
                }

                if (srcset) {
                    elem.setAttribute('srcset', srcset);
                } else if (src && !isPicture) {
                    if (regIframe.test(elem.nodeName)) {
                        changeIframeSrc(elem, src);
                    } else {
                        elem.src = src;
                    }
                }

                if (isImg && (srcset || isPicture)) {
                    updatePolyfill(elem, { src: src });
                }
            }

            if (elem._lazyRace) {
                delete elem._lazyRace;
            }
            removeClass(elem, lazySizesCfg.lazyClass);

            rAF(function () {
                // Part of this can be removed as soon as this fix is older: https://bugs.chromium.org/p/chromium/issues/detail?id=7731 (2015)
                var isLoaded = elem.complete && elem.naturalWidth > 1;

                if (!firesLoad || isLoaded) {
                    if (isLoaded) {
                        addClass(elem, 'ls-is-cached');
                    }
                    switchLoadingClass(event);
                    elem._lazyCache = true;
                    setTimeout(function () {
                        if ('_lazyCache' in elem) {
                            delete elem._lazyCache;
                        }
                    }, 9);
                }
                if (elem.loading == 'lazy') {
                    isLoading--;
                }
            }, true);
        });

        var unveilElement = function (elem) {
            if (elem._lazyRace) {
                return;
            }
            var detail;

            var isImg = regImg.test(elem.nodeName);

            //allow using sizes="auto", but don't use. it's invalid. Use data-sizes="auto" or a valid value for sizes instead (i.e.: sizes="80vw")
            var sizes = isImg && (elem[_getAttribute](lazySizesCfg.sizesAttr) || elem[_getAttribute]('sizes'));
            var isAuto = sizes == 'auto';

            if ((isAuto || !isCompleted) && isImg && (elem[_getAttribute]('src') || elem.srcset) && !elem.complete && !hasClass(elem, lazySizesCfg.errorClass) && hasClass(elem, lazySizesCfg.lazyClass)) {
                return;
            }

            detail = triggerEvent(elem, 'lazyunveilread').detail;

            if (isAuto) {
                autoSizer.updateElem(elem, true, elem.offsetWidth);
            }

            elem._lazyRace = true;
            isLoading++;

            lazyUnveil(elem, detail, isAuto, sizes, isImg);
        };

        var afterScroll = debounce(function () {
            lazySizesCfg.loadMode = 3;
            throttledCheckElements();
        });

        var altLoadmodeScrollListner = function () {
            if (lazySizesCfg.loadMode == 3) {
                lazySizesCfg.loadMode = 2;
            }
            afterScroll();
        };

        var onload = function () {
            if (isCompleted) {
                return;
            }
            if (Date.now() - started < 999) {
                setTimeout(onload, 999);
                return;
            }

            isCompleted = true;

            lazySizesCfg.loadMode = 3;

            throttledCheckElements();

            addEventListener('scroll', altLoadmodeScrollListner, true);
        };

        return {
            _: function () {
                started = Date.now();

                lazysizes.elements = document.getElementsByClassName(lazySizesCfg.lazyClass);
                preloadElems = document.getElementsByClassName(lazySizesCfg.lazyClass + ' ' + lazySizesCfg.preloadClass);

                addEventListener('scroll', throttledCheckElements, true);

                addEventListener('resize', throttledCheckElements, true);

                addEventListener('pageshow', function (e) {
                    if (e.persisted) {
                        var loadingElements = document.querySelectorAll('.' + lazySizesCfg.loadingClass);

                        if (loadingElements.length && loadingElements.forEach) {
                            requestAnimationFrame(function () {
                                loadingElements.forEach(function (img) {
                                    if (img.complete) {
                                        unveilElement(img);
                                    }
                                });
                            });
                        }
                    }
                });

                if (window.MutationObserver) {
                    new MutationObserver(throttledCheckElements).observe(docElem, { childList: true, subtree: true, attributes: true });
                } else {
                    docElem[_addEventListener]('DOMNodeInserted', throttledCheckElements, true);
                    docElem[_addEventListener]('DOMAttrModified', throttledCheckElements, true);
                    setInterval(throttledCheckElements, 999);
                }

                addEventListener('hashchange', throttledCheckElements, true);

                //, 'fullscreenchange'
                ['focus', 'mouseover', 'click', 'load', 'transitionend', 'animationend'].forEach(function (name) {
                    document[_addEventListener](name, throttledCheckElements, true);
                });

                if (/d$|^c/.test(document.readyState)) {
                    onload();
                } else {
                    addEventListener('load', onload);
                    document[_addEventListener]('DOMContentLoaded', throttledCheckElements);
                    setTimeout(onload, 20000);
                }

                if (lazysizes.elements.length) {
                    checkElements();
                    rAF._lsFlush();
                } else {
                    throttledCheckElements();
                }
            },
            checkElems: throttledCheckElements,
            unveil: unveilElement,
            _aLSL: altLoadmodeScrollListner,
        };
    })();

    var autoSizer = (function () {
        var autosizesElems;

        var sizeElement = rAFIt(function (elem, parent, event, width) {
            var sources, i, len;
            elem._lazysizesWidth = width;
            width += 'px';

            elem.setAttribute('sizes', width);

            if (regPicture.test(parent.nodeName || '')) {
                sources = parent.getElementsByTagName('source');
                for (i = 0, len = sources.length; i < len; i++) {
                    sources[i].setAttribute('sizes', width);
                }
            }

            if (!event.detail.dataAttr) {
                updatePolyfill(elem, event.detail);
            }
        });
        var getSizeElement = function (elem, dataAttr, width) {
            var event;
            var parent = elem.parentNode;

            if (parent) {
                width = getWidth(elem, parent, width);
                event = triggerEvent(elem, 'lazybeforesizes', { width: width, dataAttr: !!dataAttr });

                if (!event.defaultPrevented) {
                    width = event.detail.width;

                    if (width && width !== elem._lazysizesWidth) {
                        sizeElement(elem, parent, event, width);
                    }
                }
            }
        };

        var updateElementsSizes = function () {
            var i;
            var len = autosizesElems.length;
            if (len) {
                i = 0;

                for (; i < len; i++) {
                    getSizeElement(autosizesElems[i]);
                }
            }
        };

        var debouncedUpdateElementsSizes = debounce(updateElementsSizes);

        return {
            _: function () {
                autosizesElems = document.getElementsByClassName(lazySizesCfg.autosizesClass);
                addEventListener('resize', debouncedUpdateElementsSizes);
            },
            checkElems: debouncedUpdateElementsSizes,
            updateElem: getSizeElement,
        };
    })();

    var init = function () {
        if (!init.i && document.getElementsByClassName) {
            init.i = true;
            autoSizer._();
            loader._();
        }
    };

    setTimeout(function () {
        if (lazySizesCfg.init) {
            init();
        }
    });

    lazysizes = {
        cfg: lazySizesCfg,
        autoSizer: autoSizer,
        loader: loader,
        init: init,
        uP: updatePolyfill,
        aC: addClass,
        rC: removeClass,
        hC: hasClass,
        fire: triggerEvent,
        gW: getWidth,
        rAF: rAF,
    };

    return lazysizes;
});

//滑动手势minitouch
$.fn.minitouch = function (options) {
    var is_on = 'minitouch-isload';
    var _e = $(this);
    if (_e.data(is_on)) {
        return;
    }

    options = $.extend(
        {
            direction: 'bottom',
            selector: '',
            start_selector: '',
            depreciation: 50,
            stop: false,
            onStart: false,
            onIng: false,
            onEnd: false,
            inEnd: false,
        },
        options
    );
    var is_stop = false;
    var dep = options.depreciation;
    var startX = 0;
    var startY = 0;
    var endX = 0;
    var endY = 0;
    var angle = 0;
    var distanceX = 0;
    var distanceY = 0;
    var dragging = false;

    var cssTransition = function (a, b, c, d, s) {
        var e, f, g;
        d && ((b += 'px'), (c += 'px'), (e = 'translate3D(' + b + ',' + c + ' , 0)'), (f = {}), (g = cssT_Support()), (f[g + 'transform'] = e), (f[g + 'transition'] = g + 'transform 0s linear'), (f['cursor'] = s), 'null' == d && ((f[g + 'transform'] = ''), (f[g + 'transition'] = '')), a.css(f));
    };
    var cssT_Support = function () {
        var a = document.body || document.documentElement;
        a = a.style;
        return '' == a.WebkitTransition ? '-webkit-' : '' == a.MozTransition ? '-moz-' : '' == a.OTransition ? '-o-' : '' == a.transition ? '' : void 0;
    };

    var touch_selector = options.start_selector || options.selector;
    _e.on('touchstart pointerdown MSPointerDown', touch_selector, function (e) {
        startX = startY = endX = endY = angle = distanceX = distanceY = 0;
        startX = e.originalEvent.pageX || e.originalEvent.touches[0].pageX;
        startY = e.originalEvent.pageY || e.originalEvent.touches[0].pageY;
        dragging = !0;
        //兼容swiper
        if ($(e.target).parentsUntil(touch_selector, '.swiper-container,.scroll-x').length) {
            dragging = !1;
        }
    })
        .on('touchmove pointermove MSPointerMove', touch_selector, function (a) {
            var _move = options.start_selector ? (options.selector ? _e.find(options.selector) : _e.find(options.start_selector)) : $(this);
            if ($.isFunction(options.stop)) {
                is_stop = options.stop(_e, _move, startX, startY);
            }
            if (dragging && !is_stop) {
                endX = a.originalEvent.pageX || a.originalEvent.touches[0].pageX;
                endY = a.originalEvent.pageY || a.originalEvent.touches[0].pageY;
                distanceX = endX - startX;
                distanceY = endY - startY;
                angle = (180 * Math.atan2(distanceY, distanceX)) / Math.PI;
                'right' == options.direction && ((distanceY = 0), (distanceX = angle > -40 && angle < 40 && distanceX > 0 ? distanceX : 0));
                'left' == options.direction && ((distanceY = 0), (distanceX = (angle > 150 || angle < -150) && 0 > distanceX ? distanceX : 0));
                'top' == options.direction && ((distanceX = 0), (distanceY = angle > -130 && angle < -50 && 0 > distanceY ? distanceY : 0));
                'bottom' == options.direction && ((distanceX = 0), (distanceY = angle > 50 && angle < 130 && distanceY > 0 ? distanceY : 0));
                if (distanceX !== 0 || distanceY !== 0) {
                    a.preventDefault ? a.preventDefault() : (a.returnValue = !1);
                    cssTransition(_move, distanceX, distanceY, dragging, 'grab');
                    $.isFunction(options.onIng) && options.onIng(_e, _move, distanceX, distanceY);
                }
            }
        })
        .on('touchend touchcancel pointerup MSPointerUp', touch_selector, function () {
            var _move = options.start_selector ? (options.selector ? _e.find(options.selector) : _e.find(options.start_selector)) : $(this);
            if (dragging && !is_stop) {
                cssTransition(_move, 0, 0, 'null', '');
                $.isFunction(options.inEnd) && options.inEnd(_e, _move, distanceX, distanceY);
                if (Math.abs(distanceX) > dep || Math.abs(distanceY) > dep) {
                    $.isFunction(options.onEnd) && options.onEnd(_e, _move, distanceX, distanceY);
                }
                startX = startY = endX = endY = angle = distanceX = distanceY = 0;
                dragging = !1;
            }
        })
        .data(is_on, true);
};

//全局WIN变量
_win.bd = $('body');
_win.window = $(window);
_win.is_signin = !!_win.bd.hasClass('logged-in');
var _wid = _win.window.width();
var _hei = _win.window.height();

_win.bd.on('click', '[data-close]', function () {
    var e = $(this).attr('data-close');
    return $(e).removeClass('show in'), !1;
});

_win.bd.on('click', '[data-toggle-class]', function () {
    var c = $(this).attr('data-toggle-class') || 'show';
    var e = $(this).attr('data-target') || this;
    return $(e).toggleClass(c).trigger('toggleClass'), !1;
});

//只允许一个dropup弹出框
_win.bd.on('click', only_drop);
_win.bd.on('toggleClass', '.dropup,.dropdown', only_drop);

function only_drop(e) {
    var drop = $(e.target).closest('.dropup,.dropdown');
    $('.dropup.open,.dropdown.open').not(drop).removeClass('open');
}

_win.bd.on('click', '.alert-dismissible > .close', function () {
    $(this).parent().slideUp();
});

//按钮点击触发另一个按钮点击
_win.bd.on('click', '[data-onclick]', function () {
    var e = $(this).attr('data-onclick');
    return $(e).click();
});

//单次绑定事件，只能绑定一次事件
$.fn.OnlyOn = function (type, selector, data, fun) {
    var rnotwhite = /\S+/g;
    var is_oned = false;
    //提取第一个绑定类型
    type = type.match(rnotwhite)[0];
    //获取目前已经绑定的全部事件
    var events = $._data(this[0], 'events');

    if (events && typeof events['type'] != 'undefined') {
        $.each(events[type], function (i, item) {
            if (item.selector == selector && item.data == data) {
                is_oned = true;
                return false;
            }
        });
    }
    if (!is_oned) {
        this.on(type, selector, data, fun);
    } else {
        console.log('事件重复绑定{type：' + type + ',selector:' + selector + ',data:' + data + '}');
    }
};

$.fn.zib_ajax = function (data, success, noty, no_loading) {
    return zib_ajax($(this), data, success, noty, no_loading);
};

$.fn.countdown = function () {
    function getTimeRemaining(endtime, max_unit) {
        //endtime 为 ISO 8601 字符串
        var date = new Date();
        var now = date.getTime();
        var endDate = new Date(endtime); //设置截止时间

        var end = endDate.getTime();
        var total = end - now;

        var seconds = (total / 1000) % 60;
        seconds = seconds.toFixed(2);
        var mseconds = ~~(total % 1000);
        var minutes = ~~((total / 1000 / 60) % 60);
        var hours = ~~((total / (1000 * 60 * 60)) % 24);
        var days = ~~(total / (1000 * 60 * 60 * 24));

        if (max_unit === 'minute' && minutes > 0) {
            hours = 0;
            days = 0;
            minutes = ~~(total / 1000 / 60);
        }
        if (max_unit === 'hour' && hours > 0) {
            days = 0;
            hours = ~~(total / (1000 * 60 * 60));
        }

        seconds = seconds < 10 ? '0' + seconds : seconds;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return {
            total,
            days,
            hours,
            minutes,
            seconds,
            mseconds,
        };
    }

    $(this).each(function () {
        var _this = $(this);
        var endtime = _this.attr('data-countdown');

        if (!endtime) {
            return;
        }
        //整数显示秒
        var int_second = _this.attr('int-second');
        var max_unit = _this.attr('max-unit');

        _this.attr('title', '结束时间：' + endtime);

        if (int_second === 'auto') {
            var t = getTimeRemaining(endtime, max_unit);
            int_second = t.days > 0;
        }

        function updateClock() {
            var t = getTimeRemaining(endtime, max_unit);

            if (int_second) {
                //去除小数点后面的数
                t.seconds = t.seconds.toString().replace(/\.\d+$/, '');
            }

            if (_this.find('.day').length) {
                _this.find('.day').html(t.days);
            }
            if (_this.find('.hour').length) {
                _this.find('.hour').html(t.hours);
            }
            if (_this.find('.minute').length) {
                _this.find('.minute').html(t.minutes);
            }
            if (_this.find('.second').length) {
                _this.find('.second').html(t.seconds);
            } else {
                var html = '';
                if (t.days > 0) {
                    html += '<span class="day">' + t.days + '</span><span class="day-unit">天</span>';
                }
                if (t.hours > 0 || t.days > 0) {
                    html += '<span class="hour">' + t.hours + '</span><span class="hour-unit">小时</span>';
                }
                if (t.minutes > 0 || t.hours > 0 || t.days > 0) {
                    html += '<span class="minute">' + t.minutes + '</span><span class="minute-unit">分</span>';
                }

                html += '<span class="second">' + t.seconds + '</span><span class="second-unit">秒</span>';
                _this.html(html);
            }

            if (t.total <= 0) {
                clearInterval(timeinterval);
                var over_text = _this.attr('data-over-text') || '已结束';
                _this.html(over_text).trigger('countdown-over');
            }
        }

        updateClock();
        var timeinterval = setInterval(updateClock, int_second ? 1000 : 20);
        _this.removeAttr('data-countdown').addClass('countdown-box');
    });
};

_win.dplayer = {
    tbquire: ['dplayer'],
    obj: {},
};

//视频
function new_dplayer() {
    var selector = '.new-dplayer:not(.dplayer-initialized),[data-dplayer]:not(.dplayer-initialized)'; //选择器
    if ($(selector).length) {
        $(selector).each(function () {
            var _this = $(this);
            get_new_dplayer(_this, function (dplayer) {
                _this
                    .addClass('dplayer-initialized')
                    .siblings('.dplayer-featured')
                    .find('.switch-video')
                    .each(function () {
                        var _this = $(this);
                        var video_url = _this.attr('video-url');
                        var video_pic = _this.attr('video-pic');
                        _this.data({
                            'video-url': video_url,
                            'video-pic': video_pic,
                        });
                        _this.attr({
                            'dplayer-id': dplayer.container.id,
                        });
                        setTimeout(function () {
                            _this.removeAttr('video-url').removeAttr('video-pic');
                        }, 50);
                    });
            });
        });
    }
}

/**
 * @description:  获取新的dplayer
 * @param {*} _this 当前元素
 * @param {*} func 回调函数
 * @return {*} 返回dplayer的id
 */
function get_new_dplayer(_this, func) {
    var option = {};
    var video_url = _this.attr('video-url') || _this.data('video-url');
    var data_volume = _this.attr('data-volume');
    var i = parseInt((Math.random() + 1) * Math.pow(10, 4));

    if (_this.find('.dplayer-video-wrap').length || !video_url) return;

    _this.attr('id', 'dplayer-' + i);
    option.container = document.getElementById('dplayer-' + i);
    option.theme = '#f04494';
    option.video = {
        url: video_url,
        pic: _this.attr('video-pic') || '',
        type: _this.attr('video-type') || 'auto',
    };
    _this.attr('data-loop') && (option.loop = true);
    _this.attr('data-autoplay') && (option.autoplay = true);
    if (data_volume && data_volume < 1) {
        option.volume = data_volume;
    }
    if (data_volume === 'none') {
        option.volume = 0;
    }

    var option_attr = _this.attr('video-option');
    if (option_attr) {
        try {
            option_attr = JSON.parse(option_attr);
        } catch (e) {
            console.log(e);
        }
        $.extend(option, option_attr);
    }

    //第三方插件判断添加
    var v_u = video_url.toLowerCase();
    v_u.indexOf('.m3u') != -1 && _win.dplayer.tbquire.push('hls');
    v_u.indexOf('.mpd') != -1 && _win.dplayer.tbquire.push('dash');
    v_u.indexOf('.flv') != -1 && _win.dplayer.tbquire.push('flv');

    $('link#dplayer').length || $('dplayer').append('<link type="text/css" id="dplayer" rel="stylesheet" href="' + _win.uri + '/css/dplayer.min.css?ver=' + _win.ver + '">');

    _win.dplayer.tbquire.push('dplayer');

    tbquire(_win.dplayer.tbquire, function () {
        try {
            var newDPlayer = new DPlayer(option);
            newDPlayer.on('play', function () {
                _win.dplayer.play = newDPlayer;
            });

            _this.data('dplayer', newDPlayer);
            _win.dplayer.obj[i] = newDPlayer;

            _this.attr({
                'video-url': '',
                'video-pic': '',
            });

            $.isFunction(func) && func(newDPlayer);
        } catch (e) {
            console.error(e);
        }
    });
    return i;
}

_win.bd.on('click', '.swiper-slide.slide-dplayer:not(.slide-dplayer-played) > *', function () {
    var _this = $(this);
    var dplayer = _this.find('.new-dplayer').data('dplayer');
    if (dplayer) {
        dplayer.play();
        _this.parent().addClass('slide-dplayer-played');
    }
});

//视频列表图封面
function thumb_dplayer(mouseenter_e, box_e) {
    var $body = $('body');

    var thumb_dplayer_data_text = 'thumb_dplayer_data';
    var thumb_dplayer_data = $body.data(thumb_dplayer_data_text) || {};
    if (thumb_dplayer_data[mouseenter_e + box_e]) {
        return;
    }
    var ing_class = 'thumb-dplayer-playing';
    var thumb_hide_class = 'dplayer-thumb-hide';

    function to_play(_this_m, _this, dplayer) {
        _this.find('.dplayer-thumb').removeClass(thumb_hide_class);
        _win.dplayer.thumb_play = dplayer;
        _this.find('.progress').show();
        _this_m.addClass(ing_class);
    }

    function to_pause(_this_m, _this, dplayer) {
        dplayer.pause();
        _this.find('.dplayer-thumb').addClass(thumb_hide_class);
        _win.dplayer.thumb_play = false;
        _this.find('.progress').hide();
        _this_m.removeClass(ing_class);
    }

    function video_thumb_stop() {
        if (_win.dplayer.thumb_play) {
            var _this = $(_win.dplayer.thumb_play.container).closest(box_e);
            var _this_m = _this.closest(mouseenter_e);
            to_pause(_this_m, _this, _win.dplayer.thumb_play);
        }
    }

    function video_thumb_play() {
        var _this_m = $(this);
        var _this = _this_m.find(box_e);
        if (!_this.length) {
            return;
        }

        var dplayer = _this.find('.dplayer-thumb').data('dplayer');

        if (dplayer) {
            to_play(_this_m, _this, dplayer);
            return dplayer.play();
        }

        function dplayer_init(dplayer) {
            dplayer.on('loadeddata', function () {
                setTimeout(function () {
                    _this.find('.progress-bar').css({
                        width: '100%',
                        opacity: '0',
                        transition: 'width .2s, opacity .3s .3s',
                    });
                }, 100);

                setTimeout(function () {
                    _this.find('.progress').remove();
                }, 800);
            });

            setTimeout(function () {
                to_play(_this_m, _this, dplayer);
                dplayer.play();
            }, 200);
        }

        if (!_this.find('.dplayer-thumb').length) {
            var video_url = _this.attr('video-url');
            var video_volume = _this.attr('data-volume') || 'none';
            var video_pic = '';
            var scale_height = ~~_this.attr('scale-height');
            var progress = '<div class="progress progress-striped active progress-abs-bottom"><div class="progress-bar progress-bar-success"></div></div>';
            var _dplayer = $('<div class="dplayer-thumb-hide new-dplayer dplayer-thumb controller-hide' + (scale_height ? ' dplayer-scale-height' : '') + '"' + (scale_height ? ' style="--scale-height:' + scale_height + '%;"' : '') + ' data-loop="true" data-volume="' + video_volume + '" data-hide="true" video-url="' + video_url + '" video-pic="' + video_pic + '" video-type="auto"></div>');
            _this.find('.video-thumb').html(_dplayer).after(progress);

            setTimeout(function () {
                _this.find('.progress-bar').css({
                    transition: 'width 6s',
                    width: '80%',
                });
            }, 50);

            _this.attr('video-url', '');
            get_new_dplayer(_dplayer, dplayer_init);
        }
    }

    $body.on('mouseenter touchstart', mouseenter_e, video_thumb_play);
    $body.on('mouseleave', mouseenter_e, video_thumb_stop);

    var thumb_dplayer_init = 'thumb_dplayer_init';
    if (!$body.data(thumb_dplayer_init)) {
        $body
            .on('touchstart', function (e) {
                var _target = $(e.target);
                if (!_target.parents('.' + ing_class).length && _win.dplayer.thumb_play) {
                    video_thumb_stop();
                }
            })
            .data(thumb_dplayer_init, true);
    }

    thumb_dplayer_data[mouseenter_e + box_e] = true;
    $body.data(thumb_dplayer_data_text, thumb_dplayer_data);
}

//视频剧集
_win.bd.on('click', '.switch-video', function () {
    var _this = $(this);
    var dplayer = $('#' + _this.attr('dplayer-id')).data('dplayer');
    var video_url = _this.data('video-url');
    var video_pic = _this.data('video-pic');

    if (!video_url || !dplayer || _this.hasClass('active')) return;
    _this.addClass('active').siblings().removeClass('active');

    //第三方插件判断添加
    var v_u = video_url.toLowerCase();
    v_u.indexOf('.m3u') != -1 && _win.dplayer.tbquire.push('hls');
    v_u.indexOf('.mpd') != -1 && _win.dplayer.tbquire.push('dash');
    v_u.indexOf('.flv') != -1 && _win.dplayer.tbquire.push('flv');

    tbquire(_win.dplayer.tbquire, function () {
        try {
            var op = {
                url: video_url,
                type: 'auto',
            };
            if (video_pic) {
                op.pic = video_pic;
            }

            dplayer.switchVideo(op);
            setTimeout(function () {
                dplayer.play();
            }, 100);
        } catch (e) {
            console.log(e);
        }
    });
});

//iframe剧集切换
_win.bd.on('click', '.switch-iframe', function () {
    var _this = $(this);
    var video_url = _this.attr('iframe-url');
    var $iframe = _this.closest('.iframe-series').find('iframe');

    if (!video_url || _this.hasClass('active')) return;
    //执行切换
    try {
        $iframe[0].contentWindow.location.replace(video_url);
    } catch (c) {
        $iframe.attr('src', video_url);
    }
    //激活状态
    _this.addClass('active').siblings().removeClass('active');
});

//下载文件
_win.bd.on('click', '.file-download-btn', function () {
    var _this = $(this);
    var id = _this.attr('data-download-file');
    var nonce = _this.attr('download-nonce');
    _this.attr('target', '_blank');
    if (!id) return;
    var href = _win.ajax_url + '?action=download_file&file=' + id + '&nonce=' + nonce;
    _this.attr('target', '_blank').removeAttr('data-download-file').attr('href', href);
});

//文章古腾堡TAB
_win.bd.on('click', '.post-tab-toggle', function () {
    var _this = $(this);
    var tab_id = _this.attr('tab-id');
    if (_this.parent().hasClass('active')) return;
    var _con = _this
        .parent()
        .addClass('active')
        .siblings()
        .removeClass('active')
        .parent()
        .next()
        .find('[tab-id="' + tab_id + '"]');
    _con.siblings().removeClass('in');
    setTimeout(function () {
        _con.addClass('active').siblings().removeClass('active');
    }, 150);
    setTimeout(function () {
        _con.addClass('in');
    }, 160);
});

//幻灯片
_win.swiper = [];
_win.swiper.tab = {};
_win.swiper.scroll = {};
_win.swiper.new = {};

function new_swiper() {
    if ($('.swiper-scroll:not(.swiper-container-initialized),.new-swiper:not(.swiper-container-initialized),.swiper-tab:not(.swiper-container-initialized)').length) {
        $('link#swiper').length || $('head').append('<link type="text/css" id="swiper" rel="stylesheet" href="' + _win.uri + '/css/swiper.min.css?ver=' + _win.ver + '">');
        tbquire(['swiper'], function () {
            $('.swiper-scroll').each(function (e) {
                if ($(this).hasClass('swiper-container-initialized')) return;
                var option = {};
                var _this = $(this);
                var _eq = 'swiper-scroll-eq-' + e;
                var slideClass = _this.attr('data-slideClass') || false;
                slideClass && (option.slideClass = slideClass);

                if (!_this.attr('scroll-nogroup')) {
                    var c_w = _this.width();
                    var i_w = _this.find('.swiper-wrapper>*:eq(0)').outerWidth();
                    var slidesPerGroup = ~~(c_w / i_w);
                    option.slidesPerGroup = slidesPerGroup || 1;
                }

                option.autoplay = _this.attr('data-autoplay')
                    ? {
                          delay: ~~_this.attr('data-interval') || 4000,
                          disableOnInteraction: false,
                      }
                    : false;
                option.loop = _this.attr('data-loop');
                option.slidesPerView = 'auto';
                option.mousewheel = {
                    forceToAxis: true,
                };
                option.freeMode = true;
                option.freeModeSticky = true;

                option.navigation = {
                    nextEl: '.swiper-scroll.' + _eq + ' .swiper-button-next',
                    prevEl: '.swiper-scroll.' + _eq + ' .swiper-button-prev',
                };

                _this.addClass(_eq).attr('swiper-scroll-index', e);
                _win.swiper.scroll[e] = new Swiper('.swiper-scroll.' + _eq, option);
            });
            $('.swiper-tab').each(function (e) {
                if ($(this).hasClass('swiper-container-initialized')) return;
                var _eq = 'swiper-eq-' + e;
                var _this = $(this);

                var speed = ~~_this.attr('data-speed') || ~~((_this.width() + 1200) / 170) * 50;
                var initialSlide = ~~_this.attr('active-index');
                speed = speed < 300 ? 300 : speed;

                var option = {
                    loop: false,
                    initialSlide: initialSlide,
                    autoHeight: true,
                    spaceBetween: 20,
                    autoplay: false,
                    speed: speed,
                    pagination: {
                        el: '.swiper-tab.' + _eq + ' .swiper-pagination',
                        clickable: true,
                    },
                    on: {
                        init: function () {
                            auto_maxHeight();
                        },
                        slideChangeTransitionEnd: function (bvv) {
                            var b = $('.swiper-tab.' + _eq + ' .swiper-slide-active .post_ajax_trigger .ajax-open');
                            if (b.length) {
                                scrollTopTo($('.swiper-tab.' + _eq), -40);
                                b.attr('no-scroll', true).click();
                            }

                            var r = bvv.onStart && $('[swiper-scroll-index="' + e + '"] .swiper-slide-thumb-active [data-route]');
                            if (r && r.length) {
                                history.replaceState(null, document.title, r.attr('data-route'));
                            }
                            bvv.onStart = true;
                        },
                    },
                };

                var tab_id = _this.attr('swiper-tab');
                var tab_nav = $('[swiper-tab-nav="' + tab_id + '"]');
                if (tab_nav.length) {
                    var tab_nav_index = tab_nav.attr('swiper-scroll-index');
                    option['thumbs'] = {
                        swiper: _win.swiper.scroll[tab_nav_index],
                        autoScrollOffset: 2,
                    };
                    tab_nav.on('click', 'a[data-route]', function (e) {
                        e.preventDefault();
                    });
                }

                _this.addClass(_eq).attr('swiper-tab-index', e);
                _win.swiper.tab[e] = new Swiper('.swiper-tab.' + _eq, option);
            });
            $('.new-swiper').each(function (e) {
                if ($(this).hasClass('swiper-container-initialized')) return;

                var _eq = 'swiper-eq-' + e;
                var _this = $(this);
                var delay = ~~_this.attr('data-interval') || 6000;
                var autoplay = _this.attr('data-autoplay')
                    ? {
                          delay: delay,
                          disableOnInteraction: false,
                      }
                    : false;
                var auto_h = _this.attr('auto-height') ? true : false;
                var speed = ~~_this.attr('data-speed') || ~~((_this.width() + 1000) / 22) * 10;
                speed = speed < 400 ? 400 : speed;

                speed = autoplay === false ? speed / 1.6 : speed;

                var loop = _this.attr('data-loop') ? true : false;
                var parallax = _this.find('[data-swiper-parallax],[data-swiper-parallax-scale]').length ? true : false;
                var effect = _this.attr('data-effect') || 'slide';
                var direction = _this.attr('data-direction') || 'horizontal';
                var spaceBetween = ~~_this.attr('data-spaceBetween') || 0;
                var pagination_type = _this.attr('data-pagination-type') || 'bullets';

                _this.addClass(_eq).attr('swiper-new-index', e);

                var swiper_args = {
                    loop: loop,
                    autoHeight: auto_h,
                    direction: direction,
                    spaceBetween: spaceBetween,
                    parallax: parallax,
                    effect: effect,
                    lazy: {
                        loadPrevNext: !0,
                    },
                    autoplay: autoplay,
                    speed: speed,
                    pagination: {
                        el: '.new-swiper.' + _eq + ' .swiper-pagination',
                        type: pagination_type,
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.new-swiper.' + _eq + ' .swiper-button-next',
                        prevEl: '.new-swiper.' + _eq + ' .swiper-button-prev',
                    },
                    on: {
                        lazyImageReady: function (slideEl, imageEl) {
                            $(imageEl).addClass('lazyloaded');
                        },
                        slideChange: function () {
                            _this.find('.new-dplayer').each(function (index, item) {
                                var dplayer = $(item).data('dplayer');
                                if (dplayer) {
                                    dplayer.pause();
                                }
                            });
                        },
                        init: function () {
                            if (loop) {
                                //解决loop模式下，图片放大后，前后重复的图片没有添加no-imgbox的问题
                                _this.find('.swiper-slide-duplicate img').addClass('no-imgbox');
                            }
                        },
                    },
                };

                if (pagination_type === 'thumbnail') {
                    swiper_args.pagination.type = 'bullets';
                    swiper_args.pagination.bulletClass = 'pagination-thumbnail-item';
                    swiper_args.pagination.bulletActiveClass = 'pagination-thumbnail-item-active';
                    swiper_args.pagination.renderBullet = function (index, className) {
                        // console.log(this.slides[index], index);
                        var imgsrc, thumbnail;
                        var _img = $(this.slides[index]).find('img'); //第一个
                        if (_img.length) {
                            _img = $(_img[0]);
                            imgsrc = _img.attr('data-src') || _img.attr('src');
                        }

                        if (imgsrc) {
                            thumbnail = '<div class="' + className + '"><image class="obj-fit no-imgbox" src="' + imgsrc + '"></div>';
                            return thumbnail;
                        }
                        return '';
                    };
                }

                _win.swiper.new[e] = new Swiper('.new-swiper.' + _eq, swiper_args);
            });
        });
    }
}

window.swiper_tab_AutoHeight = debounce(function ($con) {
    var tab = $con.closest('[swiper-tab-index]');
    if (tab.length) {
        var tab_index = tab.attr('swiper-tab-index');
        _win.swiper.tab[tab_index].updateAutoHeight(500);
    }
}, 100);

$.fn.scrollX = function () {
    return this.each(function () {
        var _this = $(this);
        var is_on = 'is-on';
        if (!_this.data(is_on)) {
            _this.data(is_on, true);
            var btn_class = 'scroll-x-';
            var prev_btn_class = btn_class + 'prev';
            var next_btn_class = btn_class + 'next';
            var prev = $('<div class="' + prev_btn_class + '" style="display: none;"></div>');
            var next = $('<div class="' + next_btn_class + '" style="display: none;"></div>');
            init();
        }

        function scroll(type) {
            var data = get_data();
            _this.animate(
                {
                    scrollLeft: type === 'prev' ? data[0] : data[1],
                },
                300,
                'linear',
                hover_show
            );
        }

        function init() {
            _this.after(prev).after(next);
            prev.on('click', function () {
                scroll('prev');
            });
            next.on('click', scroll);
            _this.parent().hover(hover_show, function () {
                prev.hide();
                next.hide();
            });
        }

        function hover_show() {
            var data = get_data();
            if (data[3]) {
                prev.show();
            } else {
                prev.hide();
            }
            if (data[3] < data[2]) {
                next.show();
            } else {
                next.hide();
            }
        }

        function get_data() {
            var _this_w = _this.innerWidth();
            var _this_scrollLeft = _this.scrollLeft();
            var max = ~~(_this.prop('scrollWidth') - _this_w);

            var prev_scroll = ~~(_this_scrollLeft - _this_w);
            prev_scroll = prev_scroll > 0 ? prev_scroll : 0;
            var next_scroll = ~~(_this_scrollLeft + _this_w);
            next_scroll = next_scroll > max ? max : next_scroll;

            return [prev_scroll, next_scroll, max, _this_scrollLeft];
        }
    });
};

//让tab栏目的高亮元素显示在中间
$.fn.Tabcenter = function () {
    return this.each(function () {
        var _this = $(this);
        var _active = _this.find('.active');
        if (_active.length) {
            var _this_w = _this.innerWidth();
            var _active_w = _active.innerWidth();
            var _this_scrollLeft = _this.scrollLeft();
            var _active_left = _active.position().left;
            var max = ~~(_this.prop('scrollWidth') - _this_w);

            var _yd = ~~(_this_scrollLeft + _active_left - _this_w / 2 + _active_w / 2);

            _yd = _yd > 0 ? _yd : 0;
            _yd = _yd > max ? max : _yd;

            if (_yd != _this_scrollLeft) {
                _this.animate(
                    {
                        scrollLeft: _yd,
                    },
                    300
                );
            }
        }
    });
};

_win.bd.on('shown.bs.tab', 'a[data-ajax]', function () {
    $(this).closest('ul.list-inline').Tabcenter();
});
$('.tab-nav-theme,.tab-nav-but,.index-tab ul.list-inline').Tabcenter();

/**
 * @description: AJAX获取包装函数
 * @param {*} _this 传入点击按钮的自己,需要有href或ajax-href下一页的链接(必须)
 * @param {*} con 需要插入的父元素选择器 (必须)
 * @param {*} jcon 获取内容的父元素选择器 (必须)
 * @param {*} item 获取的列表选择器   (必须)
 * @param {*} loader 加载动画的内容 （非必须，有默认值）
 * @param {*} pag 获取的分页内容选择器 （必须）
 * @param {*} next 如果需要重新插入下一页链接：获取分页内容中的下一页 选择器
 * @param {*} trigger 将获取的下一页链接从新插入到的新的 按钮中-的class值
 * @param {*} replace 替换列表内容而不是追加|'ajax-replace'
 * @param {*} nomore 全部加载完成之后的文案
 * @param {*} top 将内容追加到顶部而不是底部
 * @return {*}
 */
function post_ajax(_this, con, jcon, item, loader, pag, next, trigger, replace, nomore, top, ajax_trigger) {
    //准备参数
    var $con = _this.closest(con);
    if ($con.attr('ing')) return !1;
    var $loader = $con.find('.post_ajax_loader');
    var $item = $con.find(item);
    var href = _this.attr('ajax-href') || _this.attr('href') || _this.find('a').attr('ajax-href') || _this.find('a').attr('href');
    jcon = jcon || con;
    ajax_trigger = ajax_trigger || (_win.ajax_trigger ? _win.ajax_trigger : '<i class="fa fa-arrow-right"></i>加载更多');
    replace = _this.attr('ajax-replace') || replace;
    nomore = nomore || _win.ajax_nomore;
    if ($loader.length) {
        loader = $loader.last().prop('outerHTML');
    } else {
        loader = loader || '<div class="theme-box box-body ajax-loading text-center"><h2 class="loading zts"></h2></div>';
        loader = '<div class="post_ajax_loader">' + loader + '</div>';
    }

    href &&
        $.ajax({
            type: 'POST',
            url: href,
            dataType: 'html',
            beforeSend: function () {
                if (replace) {
                    $con.find(item).remove();
                }

                $con.attr('ing', true)
                    .find('.post_ajax_trigger,.no-more,.post_ajax_loader,.noajax-pag,' + pag)
                    .remove();
                if ($con.find(item).length) {
                    top ? $item.first().before(loader) : $item.last().after(loader);
                } else {
                    $con.append(loader);
                }
                $con.find('.post_ajax_loader').fadeIn();
                swiper_tab_AutoHeight($con);
            },
            success: function (a) {
                a = $.parseHTML('<div>' + a + '</div>');
                $item = $con.find(item);
                var $a = $(a);
                //全页面全局替换
                $a.find('[win-ajax-replace]').each(function () {
                    var replace_key = $(this).attr('win-ajax-replace');
                    var replace_e = $('[win-ajax-replace="' + replace_key + '"]');
                    if (replace_e.length) {
                        replace_e.html($(this).html());
                        $(this).remove();
                    }
                });

                var $jcon = $a.find(jcon);
                var c_c = $jcon.find(item); //列表
                var c_p = $jcon.find(pag); //下一页
                var n_h = c_p.find(next).attr('href') || c_p.find(next).find('a').attr('href'); //下一页链接
                c_p = c_p.length ? c_p : '<div class="text-center mb20 padding-h10 muted-2-color no-more separator">' + nomore + '</div>'; //是否还有下一页
                c_p = $jcon.find('.noajax-pag').length ? $jcon.find('.noajax-pag') : c_p;

                //头部保留
                if (trigger) {
                    //将获取的下一页链接重新插入到的新的 按钮中
                    c_p = 'undefined' != typeof n_h ? '<span class="post_ajax_trigger"><a class="' + trigger + '" href="' + n_h + '">' + ajax_trigger + '</a></span>' : c_p;
                }

                //添加头部
                var $item_header = $jcon.find('.ajax-item-header');
                if ($item_header.length) {
                    $con.find('.ajax-item-header').remove(); //删除列表头部内容
                    $con.prepend($item_header);
                }

                $con.find('.post_ajax_trigger,.post_ajax_loader').remove(); //删除老的下一页链接/加载动画/列表头部内容

                if ($item.length) {
                    //原本有列表则追加
                    if (top) {
                        //追加方向为顶部
                        $item.first().before(c_c);
                        $con.find(item).first().before(c_p);
                    } else {
                        //追加方向为底部
                        $item.last().after(c_c);
                        $con.find(item).last().after(c_p);
                    }
                } else {
                    //原本无列表则添加
                    if (top) {
                        $con.append(c_p).append(c_c);
                    } else {
                        $con.append(c_c).append(c_p);
                    }
                }

                $con.append(loader).removeAttr('ing').trigger('post_ajax.ed', $(a)).find('>.post_ajax_loader').hide();

                if (!_this.attr('no-scroll')) {
                    var _scroll_To = replace ? $con : $(c_c[0]);
                    setTimeout(function () {
                        scrollTopTo(_scroll_To, -40);
                    }, 100);
                } else {
                    _win.auto_scroll = true;
                    setTimeout(function () {
                        _win.window.trigger('scroll');
                        _win.auto_scroll = false;
                    }, 20);
                }

                if (_this.attr('route')) {
                    var route_title = _this.attr('route-title') || $a.find('title').text();
                    history.replaceState(null, route_title, href);
                    if (route_title) {
                        $('head title').text(route_title);
                    }
                }

                swiper_tab_AutoHeight($con);
                auto_fun();
            },
        });
    return !1;
}

_win.bd.on('click', '[remote-box]', function () {
    var _this = $(this);
    var remote = _this.attr('remote-box');
    if (remote) {
        $.post(remote, null, function (data) {
            _this.html(data).removeAttr('remote-box');
            auto_fun();
        });
    }
});

_win.bd.on('show.bs.tab', 'a[data-ajax]', function () {
    var _this = $(this);
    var a = _this.data('target') || _this.attr('href');

    var selector = a + ' .post_ajax_trigger .ajax-next';
    var b = $(selector).attr('ajax-replace', true);
    if (b.length) {
        b.click();
    } else {
        var href = _this.attr('data-ajax');
        if (href) {
            var trigger = '<span class="post_ajax_trigger hide"><a ajax-href="' + href + '" class="ajax_load ajax-next ajax-open" ajax-replace="true"></a></span>';
            $(a).append(trigger);
            $(selector).click();
        }
    }
    if (_this.attr('only-one')) {
        _this.removeAttr('data-ajax');
    }
});

_win.bd.on('click', '[ajax-tab],[ajax-target]', function () {
    var _this = $(this);
    var replace = _this.attr('ajax-replace') ? ' ajax-replace="true"' : '';
    var target = _this.attr('ajax-tab') || _this.attr('ajax-target');
    var href = _this.attr('data-ajax');
    var trigger = '<span class="post_ajax_trigger hide"><a ajax-href="' + href + '" class="ajax_load ajax-next ajax-open"' + replace + '></a></span>';
    $(target).append(trigger);
    $(target + ' .post_ajax_trigger .ajax-next').click();
    $('a[href="' + target + '"]').click();
});

//文章手动ajax
_win.bd.on('click', '.ajax-next', function (e) {
    e.preventDefault();
    var _this = $(this);
    if (_this.hasClass('pag-jump')) return false;
    var _loader = '<div class="text-center muted-2-color mt20"><i class="loading mr10"></i>加载中...</div>';
    if (_this.attr('ajax-replace')) {
        _loader = '<div class="posts-item flex"><div class="post-graphic"><div class="radius8 item-thumbnail placeholder"></div> </div><div class="item-body flex xx flex1 jsb"> <p class="placeholder t1"></p> <h4 class="item-excerpt placeholder k1"></h4><p class="placeholder k2"></p><i><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i></div></div>';
        _loader += _loader;
        _loader += _loader;
    }
    _loader = '<div class="mb20">' + _loader + '</div>';

    var t_con = _this.attr('ajaxpager-target') || '.ajaxpager';
    var t_item = _this.attr('ajaxitem-target') || '.ajax-item';
    var $item1 = _this.parents(t_con + ':eq(0)').find(t_item + ':first');
    var $item1_class = $item1.attr('class');

    if ($item1_class && $item1_class.indexOf('posts-item') > -1) {
        if ($item1_class.indexOf('card') > -1) {
            var loader_class = $item1_class.replace('ajax-item', '');
            _loader = '<div class="' + loader_class + '"><div class="item-thumbnail"><div class="radius8 item-thumbnail placeholder"></div> </div><div class="item-body "> <h2 class="item-excerpt placeholder t1 item-heading"></h2> <p class="mt10 placeholder k2"></p><i class="flex jsb"><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i></div></div>';
            _loader = _loader.repeat(_wid > 766 ? (_wid > 992 && !$('.sidebar').length ? 8 : 6) : 4);
        } else if ($item1_class.indexOf('list') > -1) {
            _loader = '<div class="posts-item list flex"><div class="post-graphic"><div class="radius8 item-thumbnail placeholder"></div> </div><div class="item-body flex xx flex1 jsb"> <p class="placeholder t1"></p> <h4 class="item-excerpt placeholder k1"></h4><p class="placeholder k2"></p><i><i class="placeholder s1"></i><i class="placeholder s1 ml10"></i></i></div></div>';
            _loader = _loader.repeat(_wid > 766 ? 4 : 3);
        }
    }

    return post_ajax(_this, t_con, t_con, t_item, _loader, '.ajax-pag');
});

if (_wid < 769 && $('.drawer-sm').length) {
    _win.bd.on('click', '[data-drawer]', function () {
        var _this = $(this);
        var title = _this.attr('drawer-title') || '';
        var drawer_selector = _this.attr('drawer-selector') || '.drawer-sm';
        var action = _this.attr('data-drawer');
        var route = _this.attr('route');
        var route_back = _this.attr('route-back');
        $(drawer_selector).Drawer(action, title, route, route_back);
    });

    var this_url = (window.parent.location.origin + window.parent.location.pathname).replace(/(\/*$)/g, '');
    _win.url_request = window.parent.location.search; //兼容支付的GetRequest函数
    setTimeout(function () {
        $('[data-drawer][route="' + this_url + '"]').click();
    }, 20);
} else {
    _win.bd.on('shown.bs.tab', '[data-drawer][route]', tabOnRoute);
    _win.bd.on('hide.bs.tab', '[data-drawer][route]', tabOnRoute_hidden);
}

//tab路由
_win.bd.on('shown.bs.tab', '[data-route]', tabOnRoute);
_win.bd.on('hide.bs.tab', '[data-route]', tabOnRoute_hidden);

function tabOnRoute_hidden() {
    var _this = $(this);
    if (!history.state) {
        var route = _this.attr('route') || _this.attr('data-route');
        var tab_id = _this.attr('data-target');
        if (route) {
            history.replaceState(
                {
                    tab_id: tab_id,
                },
                null,
                route
            );
        }
    }
}

function tabOnRoute() {
    var _this = $(this);
    if (_this.attr('onpopstate')) {
        _this.attr('onpopstate', false);
    } else {
        var route = _this.attr('route') || _this.attr('data-route');
        var tab_id = _this.attr('data-target');
        if (route) {
            routeGo(route, {
                tab_id: tab_id,
            });
        }
    }
}

window.onpopstate = function (event) {
    var tab = event.state && $('[data-toggle="tab"][data-target="' + event.state.tab_id + '"]');
    if (tab && tab.length) {
        tab.attr('onpopstate', true).click();
    }
};

/**
 * @description: 设置浏览器网址
 * @param {*} url
 * @param {*} title
 * @return {*}
 */
function routeGo(url, data, title) {
    title = title || document.title;
    if (url) {
        history.pushState(data, title, url);
    }
}

$.fn.loading = function (action) {
    var _this = $(this);
    action = action == 'show' || action == 'hide' ? action : _this.hasClass('loading-show') ? 'hide' : 'show';
    var loading_class = 'loading-backdrop';
    var loading_html = '<div class="' + loading_class + '" style="display:none;"><svg class="loading-circle" viewBox="0 0 50 50"><circle cx="25" cy="25" r="20"></circle></svg></div>';

    if (action == 'show') {
        _this
            .addClass('loading-show')
            .append(loading_html)
            .find('.' + loading_class)
            .fadeIn(200);
    } else {
        _this
            .removeClass('loading-show')
            .find('.' + loading_class)
            .fadeOut(200, function () {
                $(this).remove();
            });
    }
    return _this;
};

/**
 * @description: 移动端全屏抽屉
 * @param {*}
 * @return {*}
 */
$.fn.Drawer = function (action, title, route, route_back) {
    title = title || '';
    var _this = $(this);

    if (!_this.length) return;

    action = action == 'show' || action == 'hide' ? action : _this.hasClass('show') ? 'hide' : 'show';
    var _body = $('body');

    if (!_this.attr('on-start')) {
        _this.attr('on-start', true).minitouch({
            direction: 'right',
            onEnd: function () {
                _this.Drawer('hide');
            },
        });
    }

    function urlTo(url) {
        history.replaceState(null, document.title, url);
    }

    switch (action) {
        case 'show':
            var header = $('<div class="drawer-header flex ac"><div class="drawer-close"><i class="fa fa-angle-left em12"></i></div><div class="drawer-title">' + title + '</div></div>').on('click', '.drawer-close', function () {
                _this.Drawer('hide');
            });
            _body.append(header);
            setTimeout(function () {
                _body.addClass('drawer-show');
            }, 10);
            route_back && _this.data('route_back', route_back);
            route && urlTo(route);
            return _this.addClass('show');
        case 'hide':
            _body.removeClass('drawer-show');
            setTimeout(function () {
                $('.drawer-header').remove();
            }, 400);
            urlTo(_this.data('route_back'));
            return _this.removeClass('show');
        default:
    }
};

//文章幻灯片
if ($('.wp-block-carousel').length) {
    if (!$('link#swiper').length) {
        $('head').append('<link type="text/css" id="swiper" rel="stylesheet" href="' + _win.uri + '/css/swiper.min.css?ver=' + _win.ver + '">');
    }
    var _sc = $('.wp-block-carousel');
    var Sw = [];

    tbquire(['swiper'], function () {
        _sc.each(function (si) {
            var _this = $(this);
            var _eq = 'swiper-post-' + si;

            if (_this.find('.wp-block-gallery>.blocks-gallery-grid').length) {
                _this.find('.wp-block-gallery').html(_this.find('.wp-block-gallery>.blocks-gallery-grid').html());
            }

            _this.find('.wp-block-gallery').removeClass().addClass('swiper-wrapper').find('.blocks-gallery-item,>.wp-block-image').addClass('swiper-slide');
            _this.find('.carousel-control.left').replaceWith('<div class="swiper-button-next"></div>');
            _this.find('.carousel-control.right').replaceWith('<div class="swiper-button-prev"></div><div class="swiper-pagination"></div>');

            var _ss = _this.find('.carousel');
            var proportion = _ss.attr('proportion') || 0.6;
            var style = _ss.attr('style');

            _ss.addClass(_eq + ' new-swiper scale-height').attr('style', '--scale-height:' + proportion * 100 + '%;');
            _this.attr('style', style);

            var delay = ~~_ss.attr('data-interval') || 6000,
                loop = _ss.attr('data-jyloop') || false,
                effect = _ss.attr('data-effect') || 'slide';

            var speed = ~~((_ss.width() + 1200) / 250) * 100;

            Sw['swiper_wz_' + si] = new Swiper('.wp-block-carousel .' + _eq, {
                spaceBetween: 10,
                speed: speed,
                loop: loop,
                effect: effect,
                autoplay: {
                    delay: delay,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.' + _eq + ' .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.' + _eq + ' .swiper-button-next',
                    prevEl: '.' + _eq + ' .swiper-button-prev',
                },
            });
        });
    });
}

/**模态框居中 */
_win.bd.on('show.bs.modal loaded.bs.modal', '.modal:not(.flex)', function () {
    var o = $(this);
    var i = o.find('.modal-dialog');
    o.css('display', 'block'),
        i.css({
            'margin-top': Math.max(0, (_hei - i.height()) / 2),
        });
});

_win.bd.on('show.bs.modal loaded.bs.modal', '.modal', function () {
    auto_fun();
});

_win.modal_z_index = 1050;
_win.bd.on('show.bs.modal', '.modal', function () {
    _win.modal_z_index++;
    $('.modal.in').addClass('modal-parent'); //父亲节点
    $(this).css('z-index', _win.modal_z_index).removeClass('modal-parent');
});
_win.bd.on('hide.bs.modal', '.modal', function () {
    $('.modal.modal-parent').removeClass('modal-parent'); //父亲节点
});

//社交账号解绑
_win.bd.on('click', '.oauth-untying', function () {
    if (confirm('确认要解除账号绑定吗？') == true) {
        var _this = $(this),
            data = {
                action: 'user_oauth_untying',
                user_id: _this.attr('user-id'),
                type: _this.attr('untying-type'),
                _wpnonce: _this.attr('_wpnonce'),
            };
        return zib_ajax(_this, data), !1;
    }
});

//为用户授予勋章
_win.bd.on('click', '.medal-add-link', function () {
    if (confirm('确认要授予此徽章吗？') == true) {
        return zib_ajax($(this)), !1;
    }
});

//为用户收回勋章
_win.bd.on('click', '.medal-remove-link', function () {
    if (window.confirm('确认要收回此徽章吗？')) {
        zib_ajax($(this));
        return false;
    }
});

//打开对应的tab
_win.bd.on('click', '[tab-id]', function (e) {
    var tab = $(this).attr('tab-id');
    if (tab) {
        var _tab = $('a[data-target="#' + tab + '"]');
        if (!_tab.length) {
            _tab = $('a[href="#' + tab + '"]');
        }
        if (_tab.length) {
            e.preventDefault();
            _tab.tab('show');
        }
    }
});

//每次都刷新的模态框
_win.bd.on('click', '[data-toggle="RefreshModal"]', function (e) {
    e.preventDefault();
    var _this = $(this);
    var remote = _this.attr('data-remote');

    if (!remote) {
        var action = _this.attr('data-action');
        if (action) {
            remote = _win.ajax_url + '?action=' + action;
        }
    }

    refresh_modal({
        class: _this.attr('data-class') || '',
        height: _this.attr('data-height') || 300,
        mobile_from_bottom: !!_this.attr('mobile-bottom'),
        new: _this.attr('new'),
        touch_close: !_this.attr('no-touch'),
        remote: remote,
    });

    return false;
});

_win.bd.on('click', '.show-order-modal', function (e) {
    e.preventDefault();
    var _this = $(this);
    var order_id = _this.attr('data-order-id');
    var is_new = _this.attr('new-modal');
    refresh_modal({
        class: 'full-sm body-bg',
        height: 420,
        mobile_from_bottom: true,
        touch_close: true,
        new: is_new,
        remote: _win.ajax_url + '?action=order_details_modal&order_id=' + order_id,
    });
});

/**
 * 刷新模态框
 * @param {object} _config
 * @param {string} _config.class 模态框类名
 * @param {number} _config.height 模态框初始高度
 * @param {string} _config.mobile_from_bottom 移动端从底部弹出
 * @param {boolean} _config.touch_close 是否显示触摸关闭
 * @param {string} _config.remote 远程地址
 * @param {string} _config.content 模态框内容
 * @returns
 */
function refresh_modal(_config) {
    _config.mobile_from_bottom = typeof _config.mobile_from_bottom === 'undefined' ? true : _config.mobile_from_bottom; //默认从底部弹出
    var id = 'refresh_modal';
    var is_new = _config.new;
    var dataclass = _config.class || '';
    var height = _config.height || 200;
    var mobile_bottom = _config.mobile_from_bottom && _wid < 769 ? ' bottom' : '';
    var touch_close = typeof _config.touch_close === 'undefined' ? true : _config.touch_close;
    var modal_class = 'modal flex jc fade' + mobile_bottom;
    var remote = _config.remote || '';
    var content = _config.content || '';
    dataclass += ' modal-dialog';

    id += is_new ? parseInt((Math.random() + 1) * Math.pow(10, 4)) : '';
    var _id = '#' + id;
    var _modal = $(_id);
    if (_modal.length) {
        var is_in = _modal.hasClass('in');
        if (is_in) modal_class += ' in';
        var $modal_content = _modal.find('.modal-content');
        _modal.removeClass().addClass(modal_class);
        _modal.find('.modal-dialog').removeClass().addClass(dataclass);
        _modal.find('.loading-mask').fadeIn(200);
        _modal.find('.modal-body').fadeOut(200);

        $modal_content
            .css({
                overflow: 'hidden',
                height: is_in ? $modal_content.height() : 100,
            })
            .animate({
                height: height,
            });
    } else {
        var modal_html =
            '<div class="' +
            modal_class +
            '" id="' +
            id +
            '" tabindex="-1" role="dialog" aria-hidden="false">\
            <div class="' +
            dataclass +
            '" role="document">\
                <div class="modal-content">\
                </div>\
                </div>\
                </div>';

        var loading = '<div class="modal-body" style=""></div><div class="flex jc loading-mask absolute main-bg radius8"><div class="em2x opacity5"><svg class="loading-circle" viewBox="0 0 50 50"><circle cx="25" cy="25" r="20"></circle></svg></div></div>';

        _win.bd.append(modal_html);
        _modal = $(_id);

        _modal.on('hidden.bs.modal', function () {
            if (is_new) {
                $(this).remove();
            } else {
                $(this).find('.modal-content').html(loading);
            }
        });

        _modal.find('.modal-content').html(loading).css({
            height: height,
            overflow: 'hidden',
        });

        if (_wid < 769) {
            _modal.minitouch({
                direction: 'bottom',
                selector: '.modal-dialog',
                start_selector: '.modal-colorful-header,.touch-close,.touch',
                onEnd: function () {
                    _modal.modal('hide');
                },
                stop: function () {
                    return !_modal.hasClass('bottom');
                },
            });
        }
    }

    _modal.find('.touch-close').remove();
    if (mobile_bottom && touch_close) {
        _modal.find('.modal-dialog').append('<div class="touch-close"></div>');
    }

    _modal.modal('show');

    if (content) {
        _modal.find('.modal-body').html(content);
        _modal_content_open();
    } else if (remote) {
        $.post(remote, null, function (data) {
            _modal.find('.modal-body').html(data);
            setTimeout(function () {
                _modal.trigger('loaded.bs.modal');
            }, 20);
            _modal_content_open();
        });
    }

    function _modal_content_open() {
        _modal.find('.modal-body').fadeIn(50, function () {
            var b_height = $(this).outerHeight(true);
            _modal.find('.loading-mask').fadeOut(300);
            _modal.find('.modal-content').animate(
                {
                    height: b_height,
                },
                300,
                'swing',
                function () {
                    _modal.find('.modal-content').css({
                        height: '',
                        overflow: '',
                        transition: '',
                    });
                }
            );
        });
    }

    return _modal;
}

window.auto_maxHeight = debounce(z_auto, 100);
function z_auto() {
    _win.do_hh = document.documentElement.clientHeight;
    if (_win.do_hh > document.body.clientHeight) {
        var min_h = _win.do_hh - $('.footer').outerHeight() - $('.header').outerHeight() - 20;
        $('main').animate({
            'min-height': min_h,
        });
    }
}

window.auto_fun = debounce(z_auto_fun, 100);
function z_auto_fun() {
    $(document).trigger('auto_fun');

    //自动搜索
    $('.auto-search').AutoSearch();

    //控制器
    $('.dependency-box').dependency();

    //可复制
    $('.cloneable').cloneable();

    //倒计时
    $('[data-countdown]').countdown();

    auto_maxHeight();
    //兼容性图片懒加载
    $('img[data-src]:not(.lazyload,.lazyloaded,.lazyloading,.swiper-lazy)').addClass('lazyload');
    //支付功能
    $('.initiate-pay,.cashier-link,.pay-vip').length && tbquire(['pay']);

    if (window.location.search.indexOf('zippay')) {
        tbquire(['pay']);
    }

    //瀑布流布局，暂未启用，要抖动，且需要修改div结构
    /**
    tbquire(['waterfall'], function () {
        $('.waterfall-box').waterfall();
    });
    */

    //消息功能
    $('.from-private,.msg-center').length &&
        tbquire(['message'], function () {
            scroll_down();
        });
    $('.dropdown-smilie,.dropdown-code,.dropdown-image,.dropdown-quick-often').length && tbquire(['input-expand']);
    /**上传图片 */
    $('[zibupload]').length && tbquire(['mini-upload']);

    //海报分享
    $('[poster-share]').length && tbquire(['poster-share']); //海报分享

    //人机验证
    $('[machine-verification]').length && tbquire(['captcha']); //人机验证

    /* 提示工具*/
    $("[data-toggle='tooltip']").tooltip({
        container: 'body',
    });

    //登录注册
    $('.signsubmit-loader').length && tbquire(['sign-register']);

    //复制内容
    $('[data-clipboard-text]').length && tbquire(['clipboard']);

    $("[data-toggle='popover']").popover({
        sanitize: false,
    });

    //浮动功能
    if (_wid > 768) {
        $('.fixed-wrap-nav').autoAffix(20, 38);
        $('.relative>.scroll-x.no-scrollbar').scrollX();
    } else {
        $('.affix-header-sm').autoAffix(0, 20);
    }
    if (_wid > 900) {
        $('.sidebar').sidebarAffix();
    }

    //幻灯片检测
    new_swiper();

    //视频检测
    new_dplayer();

    // SVG-图标
    tbquire(['svg-icon'], () => {
        show_svg();
    });

    //高亮代码
    var _h_e = _win.highlight_kg ? 'pre code:not(.enlighter-origin)' : 'pre code.gl:not(.enlighter-origin),pre code.special:not(.enlighter-origin)';
    $(_h_e).length &&
        tbquire(['enlighterjs'], function () {
            var lin = _win.highlight_hh ? !0 : !1;
            $('link#enlighterjs').length || $('head').append('<link type="text/css" rel="stylesheet" href="' + _win.uri + '/js/enlighter/enlighterjs.min.css?ver=' + _win.ver + '" id="enlighterjs">');
            $(_h_e).enlight({
                linenumbers: lin,
                indent: 2,
                textOverflow: 'scroll',
                rawcodeDbclick: !0,
                rawButton: !1,
                infoButton: !1,
                windowButton: !1,
                theme: _win.highlight_zt,
            });
        });

    //图片灯箱
    _win.imgbox && $('.alone-imgbox-img,a[data-imgbox],.comment-content .box-img,.wp-posts-content img,.imgbox-container').length && tbquire(['imgbox']);
}

//页面滚动监听函数

//创建闭包立即执行函数
(function () {
    var scroll_throttle = throttle(function () {
        var h = document.documentElement.scrollTop + document.body.scrollTop,
            ontop = $('.ontop');
        h > 100 ? _win.bd.addClass('body-scroll') : _win.bd.removeClass('body-scroll');
        h > 400 ? ontop.addClass('show') : ontop.removeClass('show');
    }, 100);
    _win.window.scroll(scroll_throttle);

    var debounce_scroll_end = debounce(
        function () {
            $("[data-toggle='tooltip']").tooltip('hide');
        },
        500,
        true
    );
    _win.window.scroll(debounce_scroll_end);

    //滚动时隐藏一些元素
    var debounce_scrolling_hide = debounce(
        function () {
            _win.bd.addClass('scroll-ing');
        },
        500,
        true
    );
    var debounce_scrolling_hide_end = debounce(function () {
        _win.bd.removeClass('scroll-ing');
    }, 500);

    if ($('.scrolling-hide').length) {
        _win.window.scroll(debounce_scrolling_hide).scroll(debounce_scrolling_hide_end);
    }

    //向下滚动时候，隐藏一些元素
    var _last_scroll_top = 0;
    var debounce_scroll_down = debounce(
        function () {
            var _scroll_top = document.documentElement.scrollTop + document.body.scrollTop;
            if (_last_scroll_top > 100 && _scroll_top > _last_scroll_top) {
                //向下滚动
                if (!_win.auto_scroll) {
                    _win.bd.removeClass('scroll-up').addClass('scroll-down');
                }
            } else {
                //向上滚动
                if (!_win.auto_scroll || _last_scroll_top <= 100) {
                    _win.bd.removeClass('scroll-down').addClass('scroll-up');
                }
            }
            _last_scroll_top = _scroll_top;
        },
        10,
        true
    );

    if ($('.scroll-down-hide').length) {
        _win.window.scroll(debounce_scroll_down);
    }
})();

/**
 * @description: 平滑滚动到指定位置，或者指定元素的位置
 * @param {*} element 指定元素，或者元素选择器
 * @param {*} offset 偏移量
 * @return {*}
 */
function scrollTopTo(element, offset) {
    var scrollTop = 0;
    var body = $('body,html');
    var duration = duration || 300;
    offset = offset || 0;

    if (element) {
        var target = element instanceof jQuery ? element : $(element);
        scrollTop = target.length ? target.offset().top + offset - (body.hasClass('nav-fixed') ? $('.header').innerHeight() + 20 : 0) : 0;
    }

    if (Math.abs(scrollTop - body.scrollTop()) < 80) {
        return;
    }

    _win.auto_scroll = true;
    body.animate(
        {
            scrollTop: scrollTop,
        },
        duration,
        'swing',
        function () {
            setTimeout(function () {
                _win.auto_scroll = false;
            }, 20);
        }
    );
}

_win.bd.on('click', '.toggle-radius,.float-right a,.but-ripple,.but,.item-thumbnail,.menu-item >a,.yiyan-box,.relates-thumb li a', function (e) {
    var _th = $(this);
    if (!_th.hasClass('nowave') && !_th.hasClass('is-disabled')) {
        _th.css({
            overflow: 'hidden',
            position: 'relative',
        });
        var R;
        var waveWidth = ~~_th.outerWidth();
        var waveHeight = ~~_th.outerHeight();
        if (waveWidth < waveHeight) {
            R = waveHeight;
        } else {
            R = waveWidth;
        }
        var cllor = _th.css('color') || 'rgba(200, 200, 200)';
        var wave = $('<div></div>').css({
            display: 'block',
            //涟漪的颜色
            background: cllor,
            'border-radius': '50%',
            position: ' absolute',
            '-webkit-transform': 'scale(0)',
            transform: 'scale(0)',
            opacity: '.3',
            //涟漪的速度
            '-webkit-transition': 'all 1.5s cubic-bezier(0.22, 0.61, 0.36, 1)',
            transition: 'all 1.5s cubic-bezier(0.22, 0.61, 0.36, 1)',
            'z-index': '1',
            overflow: 'hidden',
            'pointer-events': 'none',
        });
        _th.append(wave);
        wave.css({
            width: R * 2 + 'px',
            height: R * 2 + 'px',
            top: e.pageY - _th.offset().top - R + 'px',
            left: e.pageX - _th.offset().left - R + 'px',
            transform: 'scale(1)',
            '-webkit-transform': 'scale(1)',
            opacity: 0,
        });
        setTimeout(function () {
            wave.remove();
        }, 2000);
    }
});

//侧栏浮动-侧栏随动
$.fn.sidebarAffix = function () {
    var _this = $(this);
    var _affix = _this.find('[data-affix]');
    var sidebar_class = 'sidebar-affix';
    if (!_affix.length) return _this;
    if (_this.children('.' + sidebar_class).length) return _this;

    var _body = $('body');
    var affix_width = _affix.innerWidth();
    var _header = $('.header');

    function nav_Height() {
        return _body.hasClass('nav-fixed') && (!_header.hasClass('scroll-down-hide') || !_body.hasClass('scroll-down')) ? _header.outerHeight(true) : 20;
    }

    var sidebarAffix_con = $('<div class="' + sidebar_class + ' affix-top" style="width:' + affix_width + 'px;"></div>');
    _this.prepend(sidebarAffix_con);

    function top_css() {
        sidebarAffix_con.css({
            top: nav_Height(),
        });
    }

    var last_top = 0;
    sidebarAffix_con
        .affix({
            offset: {
                top: function () {
                    var new_top = _this.outerHeight(true) + _this.offset().top - nav_Height();
                    if (new_top !== last_top && sidebarAffix_con.hasClass('affix')) {
                        top_css();
                    }
                    last_top = new_top;
                    return new_top;
                },
                bottom: function () {
                    var _wrap = _this.parent();
                    return document.body.clientHeight - _wrap.outerHeight() - _wrap.offset().top;
                },
            },
        })
        .on('affix-top.bs.affix', function () {
            sidebarAffix_con.html('');
        })
        .on('affix.bs.affix', function () {
            if (!sidebarAffix_con.find('[data-affix]').length) {
                sidebarAffix_con.append(_this.find('[data-affix]').clone());
            }
            top_css();
        })
        .find('[lazyload-action]:not(.lazyload)')
        .addClass('lazyload');

    if (sidebarAffix_con.hasClass('affix')) {
        sidebarAffix_con.trigger('affix.bs.affix');
    }
    if (sidebarAffix_con.hasClass('affix-bottom')) {
        sidebarAffix_con.trigger('affix-bottom.bs.affix');
    }

    return _this;
};

//内容浮动
$.fn.autoAffix = function (top, bottom) {
    top = ~~top;
    bottom = ~~bottom;
    var _this = $(this);
    if (_this.data('bs.affix')) {
        return _this;
    }
    var _body = $('body');
    var offset_top = ~~_this.attr('offset-top'); //偏移
    var offset_bottom = ~~_this.attr('offset-bottom'); //偏移
    var _wrap_attr = _this.attr('data-wrap');
    var _wrap = _wrap_attr ? $(_wrap_attr) : _this.parent();
    var _header = $('.header');

    function get_nav_Height() {
        return (_body.hasClass('nav-fixed') && (!_header.hasClass('scroll-down-hide') || !_body.hasClass('scroll-down')) ? _header.outerHeight() : 0) + top;
    }

    function remove_clone() {
        _this.next('.affix-clone').remove();
    }

    function top_css() {
        _this.css({
            top: get_nav_Height(),
        });
    }

    function this_clone() {
        if (!_this.next('.affix-clone').length) {
            _this.after(_this.clone().removeClass('affix-top affix affix-bottom').addClass('affix-clone').css('opacity', '0'));
        }
    }

    _wrap.css('min-height', _this.outerHeight() + bottom);
    var last_top = 0;

    _this
        .affix({
            offset: {
                top: function () {
                    var new_top = _this.parent().offset().top - get_nav_Height() + offset_top;
                    if (new_top !== last_top && _this.hasClass('affix')) {
                        top_css();
                    }
                    last_top = new_top;
                    return new_top;
                },
                bottom: function () {
                    return document.body.clientHeight - _wrap.outerHeight() - _wrap.offset().top + bottom + offset_bottom;
                },
            },
        })
        .on('affix-top.bs.affix', function () {
            _this.css({
                top: '',
                width: '',
            });
            remove_clone();
        })
        .on('affix.bs.affix', function () {
            this_clone(); //在自己的位置下复制一份自己
            top_css(); //设置固定位置
        })
        .on('affix-bottom.bs.affix', function () {
            this_clone();
        });

    if (_this.hasClass('affix')) {
        _this.trigger('affix.bs.affix');
    }
    if (_this.hasClass('affix-bottom')) {
        _this.trigger('affix-bottom.bs.affix');
    }
    if (_this.hasClass('affix-top')) {
        _this.trigger('affix-top.bs.affix');
    }

    return _this;
};

// 切换主题
_win.bd.on('click', '.toggle-theme', function (event) {
    var isDark = $('body').hasClass('dark-theme');

    // 检查浏览器是否支持 View Transition API
    if (!document.startViewTransition) {
        // 不支持则直接切换主题，不添加动画
        toggle();
        return;
    }
    const transition = document.startViewTransition(() => {
        toggle();
    });

    const { clientX, clientY } = event;
    const endRadius = Math.hypot(Math.max(clientX, innerWidth - clientX), Math.max(clientY, innerHeight - clientY));
    const clipPath = [`circle(0px at ${clientX}px ${clientY}px)`, `circle(${endRadius}px at ${clientX}px ${clientY}px)`];

    transition.ready.then(() => {
        document.documentElement.animate(
            {
                clipPath: isDark ? [...clipPath].reverse() : clipPath,
            },
            {
                duration: 500,
                easing: isDark ? 'ease-out' : 'ease-in',
                fill: 'both', // 保持动画结束状态，避免闪烁
                pseudoElement: isDark ? '::view-transition-old(root)' : '::view-transition-new(root)',
            }
        );
    });

    function toggle() {
        var highlight_white_zt = 'enlighter-t-' + _win.highlight_white_zt;
        var highlight_dark_zt = 'enlighter-t-' + _win.highlight_dark_zt;

        $('img[switch-src]').each(function () {
            var _this = $(this);
            var _src = _this.attr('data-src') || _this.attr('src');
            var _s_src = _this.attr('switch-src');
            _this.attr('src', _s_src).attr('switch-src', _src).attr('data-src', '');
        });

        var _enlighter = $('.enlighter-default');
        var _body = $('body');
        var _tinymce_body = $('#post_content_ifr').contents().find('body');

        if (!isDark) {
            $('meta[name="theme-color"]').attr('content', '#2F3135');
            document.documentElement.classList.add('dark');
            _tinymce_body.addClass('dark-theme');
            _enlighter.removeClass(highlight_white_zt).addClass(highlight_dark_zt);
            _body.addClass('dark-theme'),
                $.cookie('theme_mode', 'dark-theme', {
                    path: '/',
                });
        } else {
            $('meta[name="theme-color"]').attr('content', '#FDFCFE');
            document.documentElement.classList.remove('dark');
            _tinymce_body.removeClass('dark-theme');
            _enlighter.removeClass(highlight_dark_zt).addClass(highlight_white_zt);
            _body.removeClass('dark-theme'),
                $.cookie('theme_mode', 'white-theme', {
                    path: '/',
                });
        }
    }
});

/*==============点赞===收藏===关注===========*/
_win.bd.on('click', '[data-action]', function () {
    var _this = $(this);
    var s = _this.attr('data-pid');
    var key = _this.attr('data-action');
    var type = key;
    var _type = 'zibll' + type;
    var data = {
        type: type,
        key: key,
        pid: s,
    };
    if (!_win.is_signin) {
        var t = lcs.get(_type) || '';
        if (-1 !== t.indexOf(',' + s + ',')) return notyf('已赞过此' + (type == 'like' ? '文章' : '评论') + '了！', 'warning');
        t ? (t.length >= 160 ? ((t = t.substring(0, t.length - 1)), (t = t.substr(1).split(',')), t.splice(0, 1), t.push(s), (t = t.join(',')), lcs.set(_type, ',' + t + ',')) : lcs.set(_type, t + s + ',')) : lcs.set(_type, ',' + s + ',');
    }

    action_ajax(_this, data, s, type, '已赞！感谢您的支持');
});

function action_ajax(_this, data, pid, type, text) {
    if (_this.attr('disabled')) {
        return !1;
    }

    data._wpnonce = _win.post_action_nonce;
    var c = text || '处理完成';
    $.ajax({
        type: 'POST',
        url: _win.uri + '/action/action.php',
        dataType: 'json',
        data: data,
        beforeSend: function () {
            _this.attr('disabled', true).find('count').html('<i class="loading zts"></i>');
        },
        success: function (n) {
            // console.log(n);
            var ys = n.error ? 'danger' : '';
            if (n.action && n.action == 'remove') {
                _this.removeClass('actived action-animation');
                ys = 'warning';
            }

            if (n.action && n.action == 'add') {
                _this.addClass('actived action-animation');
            }

            notyf(n.msg || c, ys);
            _this
                .attr('disabled', false)
                .find('count')
                .html(n.cuont || '0');
            if (type == 'follow_user') {
                $('[data-action="follow_user"][data-pid="' + pid + '"]').each(function () {
                    $(this).find('count').html(n.cuont);
                });
            }
        },
    });
}

_win.bd.on('click', '[ajax-action]', function () {
    var _this = $(this);
    if (_this.attr('disabled')) return false;
    var id = _this.attr('data-id');
    var action = _this.attr('ajax-action');
    var _text = _this.find('text');
    _text = _text.length ? _text : _this.siblings('text');
    var _this_html = _text.html();
    _this.attr('disabled', true);
    _text.html('<i class="loading-spot"><i></i></i>');
    $.ajax({
        type: 'POST',
        url: _win.ajax_url,
        dataType: 'json',
        data: {
            id: id,
            action: action,
            _wpnonce: _this.attr('data-nonce'),
        },
        error: function (n) {
            var _msg = '操作失败 ' + n.status + ' ' + n.statusText + '，请刷新页面后重试';
            if (n.responseText && n.responseText.indexOf('致命错误') > -1) {
                _msg = '网站遇到致命错误，请检查插件冲突或通过错误日志排除错误';
            }
            notyf(_msg, 'danger');
            _this.attr('disabled', false);
            _text.html(_this_html);
        },
        success: function (n) {
            _this.attr('disabled', false);
            if (!n.data || !n.success) {
                _text.html(_this_html);
                if (n.data && n.data.msg) {
                    notyf(n.data.msg, n.data.ys || 'danger');
                }
            } else {
                n = n.data;
                if (n.show_modal && n.modal) {
                    //显示模态框
                    var modal_config = {
                        class: 'modal-mini full-sm',
                        height: 330,
                        mobile_from_bottom: true,
                        touch_close: true,
                        content: n.modal,
                    };

                    if (n.modal_config) {
                        modal_config = $.extend(modal_config, n.modal_config);
                    }

                    modal_config.content = n.modal;
                    refresh_modal(modal_config);
                    _text.html(_this_html);
                    return;
                }

                $('[ajax-action="' + action + '"][data-id="' + id + '"]').each(function () {
                    var _this = $(this);
                    var _text = _this.find('text');
                    _text = _text.length ? _text : _this.siblings('text');

                    _text.html(n.text || _this_html).addClass('ajaxed');

                    if (n.active) {
                        _this.addClass('active').siblings('.active').removeClass('active');
                    } else {
                        _this.removeClass('active');
                    }
                });
            }
        },
    });
});

//登录注册
_win.bd.on('click', '.signin-loader', function () {
    if (_win.sign_type == 'page') {
        window.location.href = _win.signin_url;
        window.location.reload;
    } else {
        $('.modal:not(#u_sign)').modal('hide');
        $('#u_sign').modal('show');

        if (_win.signin_wx_priority) {
            $('a[href="#tab-qrcode-signin"]').tab('show');
            $('.social-login-item.weixingzh:first').click();
        } else {
            $('a[href="#tab-sign-in"]').tab('show');
        }
    }
});

_win.bd.on('click', '.signup-loader', function () {
    if (_win.sign_type == 'page') {
        window.location.href = _win.signup_url;
        window.location.reload;
    } else {
        $('.modal:not(#u_sign)').modal('hide');
        $('#u_sign').modal('show');
        $('a[href="#tab-sign-up"]').tab('show');
    }
});

//扫码登录、绑定
_win.bd.on('click', '.qrcode-signin', function () {
    $('.modal:not(#u_sign)').modal('hide');
    $('#u_sign').modal('show');
    $('a[href="#tab-qrcode-signin"]').tab('show');

    var _this = $(this);
    var url = _this.attr('href');
    var container = $('.qrcode-signin-container');
    if (container.find('[get-only-one]').length) {
        container.find('[name="code"]').val('').trigger('change');
        return !1;
    }

    container.html('<p class="placeholder" style="height:180px;width:180px;margin:auto;"></p><p class="placeholder" style="height:27px;width:200px;margin:15px auto 0;"></p>');

    $.post(
        url,
        null,
        function (n) {
            n || notyf('二维码获取失败，请稍后再试', 'danger');
            var ys = n.ys ? n.ys : n.error ? 'danger' : '';
            n.msg && notyf(n.msg, ys);
            if (n && n.html) {
                container.html(n.html);
                _win.qrcode_signin = {
                    url: n.url,
                    state: n.state,
                };
                checkLogin();
            }
        },
        'json'
    );
    return !1;
});

function checkLogin() {
    if (_win.checkLoginTimer) return;

    //循环执行调整到ajax外部，避免移动端截图扫码，停止查询登录
    // 2秒后再次查询
    _win.checkLoginTimer = setInterval(checkLoginAjax, 2000);
}

function checkLoginAjax() {
    var url = _win.qrcode_signin.url;
    var state = _win.qrcode_signin.state;
    if (!url || !state) {
        clearInterval(_win.checkLoginTimer);
        _win.checkLoginTimer = false;
        return;
    }
    $.post(
        url,
        {
            state: state,
            oauth_rurl: window.location.href,
            action: 'check_callback',
        },
        function (n) {
            //做逻辑判断，登录跳转
            if (n && n.goto) {
                window.location.href = n.goto;
                window.location.reload;
            }
        },
        'json'
    );
}

//模态框关闭停止查询登录
_win.bd.on('hide.bs.modal', '.modal#u_sign', function () {
    _win.qrcode_signin = {
        url: false,
        state: false,
    };
});

//防抖函数
function throttle(fn, delay) {
    let valid = true;
    return function (...args) {
        if (!valid) {
            //休息时间 暂不接客
            return false;
        }
        // 工作时间，执行函数并且在间隔期内把状态位设为无效
        valid = false;
        setTimeout(function () {
            fn.apply(this, args);
            valid = true;
        }, delay);
    };
}

/**
 * @description: 节流函数
 * @param {*} callback 函数
 * @param {*} delay 时间
 * @param {*} immediate  是否立即执行 为true则计时开始就就执行
 * @return {*}
 */
// 定义一个debounce函数，用于函数防抖
function debounce(callback, delay, immediate) {
    // 定义一个变量，用于存放定时器
    var timeout;
    // 返回一个函数
    return function () {
        // 定义一个变量，用于存放this
        var context = this,
            // 定义一个变量，用于存放参数
            args = arguments;
        // 定义一个函数，用于清除定时器
        var later = function () {
            // 将定时器置为null
            timeout = null;
            // 如果不是立即执行，则执行回调函数
            if (!immediate) {
                callback.apply(context, args);
            }
        };
        // 如果是立即执行，且定时器不存在，则立即执行回调函数
        var callNow = immediate && !timeout;
        // 清除定时器
        clearTimeout(timeout);
        // 重新设置定时器
        timeout = setTimeout(later, delay);
        // 如果是立即执行，则立即执行回调函数
        if (callNow) {
            callback.apply(context, args);
        }
    };
}

//textarea自动高度
_win.bd.on('input focus', 'textarea[autoHeight]', function () {
    var elem = this;
    //获取元素上下边框的宽度，并去除单位
    var border = ~~getComputedStyle(elem).getPropertyValue('border-top-width').replace('px', '') + ~~getComputedStyle(elem).getPropertyValue('border-bottom-width').replace('px', '');

    var max = ~~$(elem).attr('maxHeight');
    elem.style.height = 'auto';

    if (max > elem.scrollHeight) {
        elem.style.height = elem.scrollHeight + border + 'px';
        elem.style.overflow = 'hidden';
    } else {
        elem.style.height = max + 'px';
        elem.style.overflow = 'auto';
    }
});

//赞赏模态框
_win.bd.on('click', '.rewards', function () {
    $('#rewards-popover').modal('show');
});
//用户中心
_win.bd.hasClass('author') && tbquire(['author']);

//文章导航
_win.bd.hasClass('page-template-postsnavs') && _win.bd.hasClass('logged-admin') && tbquire(['page-navs']);
//前台编辑
$('#modal_admin_set').length && tbquire(['page-edit']);
//评论
$('#commentform,#postcomments').length && tbquire(['comment']);
//通用模板js文件
_win.bd.hasClass('page-template') && tbquire(['page-template']);

//微信分享
window.WeChatShareDate && tbquire(['weixin-share']);

//搜索关键词高亮
if (_win.bd.hasClass('search')) {
    var val = $('.search-desc-text .search-key').text();
    try {
        var reg = new RegExp(val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i');
        $('.search-tab .item-heading a,.search-tab .item-excerpt,.search-tab .forum-title a,.search-tab .excerpt,#search-tab-user .display-name').each(function () {
            var $this = $(this);
            if ($this.text().search(reg) != -1) {
                $this.html(
                    $this.html().replace(reg, function (w) {
                        return '<b class="focus-color">' + w + '</b>';
                    })
                );
            }
        });
    } catch (e) {}
}

//数字填写翻页
_win.bd.on('click', '.pag-jump .jump-icon', function () {
    var _input = $(this).siblings('.jump-input');
    pag_jump_go(_input);
});

_win.bd.on(
    'input',
    '.pag-jump .jump-input',
    debounce(function () {
        pag_jump($(this));
    }, 200)
);

_win.bd.on('keydown', '.pag-jump .jump-input', function (event) {
    if (event.keyCode == 13) {
        pag_jump_go($(this));
    }
});

function pag_jump(_input) {
    var max = _input.attr('max');
    var current = _input.attr('current');
    var val = ~~_input.val();

    if (!val || val < 1 || val > max) {
        val = val > max ? max : '';

        _input.val(val);
    }
    return val == current ? 0 : val;
}

function pag_jump_go(_input) {
    var val = pag_jump(_input);
    if (val) {
        var base = _input.attr('base');
        var url = base.replace('%#%', val);
        var parent = _input.parent();
        if (parent.hasClass('ajax-next')) {
            _input.val('');
            var _ajax_link = parent.clone().removeClass('pag-jump').addClass('hide').attr('href', url);
            parent.append(_ajax_link);
            _ajax_link.click();
            _ajax_link.remove();
        } else {
            location.href = url;
            location.reload;
        }
    } else {
        _input.focus();
    }
}

//可复制的
$.fn.cloneable = function () {
    var cloneable_text = 'cloneable';
    var cloneable_add_e = '.' + cloneable_text + '-add';
    var cloneable_remove_e = '.' + cloneable_text + '-remove';
    var cloneable_item_e = '.' + cloneable_text + '-item';

    function max_min(_this, max, min) {
        var count = _this.children(cloneable_item_e).length;
        if (max && max <= count) {
            _this.nextAll(cloneable_add_e).hide();
        } else {
            _this.nextAll(cloneable_add_e).show();
        }
        if (min && min >= count) {
            _this.find(cloneable_remove_e).hide();
        } else {
            _this.find(cloneable_remove_e).show();
        }
    }

    return this.each(function () {
        var _this = $(this);
        var _item = _this.children(cloneable_item_e);
        var is_on = 'is-on';
        var click = 'click';

        var max = _this.data('max') || 0;
        var min = _this.data('min') || 0;
        max_min(_this, max, min);

        if (!_item.length || _this.attr(is_on)) return;

        var add_item = $(_item[0]).clone(false);
        add_item.find('input,textarea').val('');

        _this
            .attr(is_on, true)
            .on(click, cloneable_remove_e, function () {
                $($(this).parents(cloneable_item_e)[0]).remove();
                max_min(_this, max, min);
            })
            .nextAll(cloneable_add_e)
            .on(click, function () {
                var _add_item = add_item.clone(false);
                var count = _this.children(cloneable_item_e).length;

                _add_item.find('[name]').each(function () {
                    var $this = $(this);
                    var name = $this.attr('name');

                    if (name == '_pseudo' || !name.includes('[0]')) return;
                    $this.attr('name', $this.attr('name').replace('[0]', '[' + count + ']'));
                });

                _this.append(_add_item);
                max_min(_this, max, min);
            });
    });
};

//控制器
$.fn.dependency = function () {
    function _number(val) {
        return Number(val);
    }

    function checkBoolean(v) {
        switch (v) {
            case true:
            case 'true':
            case 1:
            case '1':
                return true;
            case null:
            case false:
            case 'false':
            case 0:
            case '0':
                return false;
            default:
                return v;
        }
    }

    function evalCondition(condition, val1, val2) {
        switch (condition) {
            case '==':
                return checkBoolean(val1) == checkBoolean(val2);
            case '!=':
                return checkBoolean(val1) != checkBoolean(val2);
            case '>=':
                return _number(val2) >= _number(val1);
            case '<=':
                return _number(val2) <= _number(val1);
            case '>':
                return _number(val2) > _number(val1);
            case '<':
                return _number(val2) < _number(val1);
            case 'any':
                if ($.isArray(val2)) {
                    for (let i = val2.length - 1; i >= 0; i--) {
                        if ($.inArray(val2[i], val1.split(',')) !== -1) {
                            return true;
                        }
                    }
                } else {
                    if ($.inArray(val2, val1.split(',')) !== -1) {
                        return true;
                    }
                }
                return false;
            case 'not-any':
                if ($.isArray(val2)) {
                    for (let i = val2.length - 1; i >= 0; i--) {
                        if ($.inArray(val2[i], val1.split(',')) == -1) {
                            return true;
                        }
                    }
                } else {
                    if ($.inArray(val2, val1.split(',')) == -1) {
                        return true;
                    }
                }
                return false;
            default:
                return false;
        }
    }

    return this.each(function () {
        var $this = $(this),
            $fields = $this.find('[data-controller]');

        if ($fields.length) {
            var is_on = 'is-on';

            $fields.each(function () {
                var $field = $(this);
                if ($field.attr(is_on)) {
                    return;
                }

                var controllers = $field.attr(is_on, true).data('controller').split('|'),
                    conditions = $field.data('condition').split('|'),
                    values = $field.data('value').toString().split('|');

                $.each(controllers, function (index, depend_id) {
                    var value = values[index] || '',
                        condition = conditions[index] || conditions[0] || '==';

                    $this.on('change', "[name='" + depend_id + "']", function () {
                        var $elem = $(this);
                        var _type = $elem.attr('type');
                        var val2 = _type == 'checkbox' ? $elem.is(':checked') : $elem.val();
                        var is_show = evalCondition(condition, value, val2);

                        $field.trigger('controller.change', is_show);
                        if (is_show) {
                            //  $field.slideDown(300)
                            $field.show();
                        } else {
                            $field.hide();
                            // $field.slideUp(300)
                        }
                    });
                });
            });
        }
    });
};

//搜索多选择
_win.bd.on('click', '[data-for]', function () {
    var _this = $(this);
    var _tt = _this.html();
    var _for = _this.attr('data-for');
    var _form = _this.parents('form');
    var _v = _this.attr('data-value');
    var multiple = ~~_this.attr('data-multiple');
    var _group = _this.closest('[for-group]');
    var active_outerHTML = _this.prop('outerHTML');
    if (!_group.length) {
        _group = _this.parent();
    }

    if (multiple > 1) {
        //允许多选
        _tt = '';
        active_outerHTML = '';
        var active_array = [];
        var _input = '';
        var is_active = _this.hasClass('active');

        if (is_active) {
            //已存在-删除
            _group.find('[data-for="' + _for + '"][data-value="' + _v + '"]').removeClass('active');
        } else {
            //添加前判断是否超过最大数量
            var ii = {};
            _group.find('[data-for="' + _for + '"].active').each(function () {
                ii[$(this).attr('data-value')] = 1;
            });

            if (Object.keys(ii).length >= multiple) {
                return notyf('最多可选择' + multiple + '个', 'danger');
            }
            //不存在-添加
            _group.find('[data-for="' + _for + '"][data-value="' + _v + '"]').addClass('active');
        }

        //循环所有的active
        _group.find('[data-for="' + _for + '"].active').each(function () {
            var _this_value = $(this).attr('data-value');
            //不重复
            if (active_array.indexOf(_this_value) == -1) {
                _tt += $(this).html();
                _input += '<input type="hidden" name="' + _for + '[]" value="' + _this_value + '">';
                active_array.push(_this_value);
                active_outerHTML += $(this).clone().addClass('outerhtml-copy').prop('outerHTML');
            }
        });

        //循环将所有的active_array添加active的calass
        $.each(active_array, function (index, value) {
            _group.find('[data-for="' + _for + '"][data-value="' + value + '"]').addClass('active');
        });

        _form.find("input[name='" + _for + "[]']").remove();
        _form.append(_input);
    } else {
        _group.find('[data-for="' + _for + '"]').removeClass('active');
        _group.find('[data-for="' + _for + '"][data-value="' + _v + '"]').addClass('active');

        _form
            .find("input[name='" + _for + "']")
            .val(_v)
            .trigger('change');
    }

    _form.find("span[name='" + _for + "']").html(_tt);
    _form.find('input[name=s]').focus();
    _form.find(".active-outerhtml[name='" + _for + "']").html(active_outerHTML);
});

_win.bd.on(
    'input',
    'input[limit-min],input[limit-max]',
    debounce(function () {
        var _this = $(this);
        var min = Number(_this.attr('limit-min'));
        var max = Number(_this.attr('limit-max'));
        var val = Number(_this.val());
        var text;

        var warning_class = 'limit-warning';
        _this.next('.' + warning_class).length || _this.after('<div class="limit-warning" style="display: none;"></div>');
        var _warning = _this.next('.' + warning_class);

        if (_this.val().length < 1) {
            _warning.html('').hide();
        } else if (val > max) {
            text = _this.attr('warning-max') || '最大可输入1$';
            _warning.html(text.replace('1$', max)).fadeIn(150);
        } else if (val < min) {
            text = _this.attr('warning-min') || '最小可输入1$';
            _warning.html(text.replace('1$', min)).fadeIn(150);
        } else {
            _warning.fadeOut(150);
        }
    }, 500)
);

//自动搜索组件
$.fn.AutoSearch = function () {
    return this.each(function () {
        var _this = $(this);
        var ajax_url = _this.attr('ajax-url') || _win.ajax_url;
        var min_length = 2;
        var search = 'search-';
        var d_search = '.' + search;
        var loading = search + 'loading';
        var input = d_search + 'input';
        var centent = search + 'centent';
        var _container = _this.find(d_search + 'container');
        var _remind = _this.find(d_search + 'remind');
        var _icon = _this.find(d_search + 'icon');
        var _icon_html = _icon.html();
        var min_length_remind = '请至少输入' + min_length + '个字符';
        var loading_icon = '<i class="loading em12"></i>';
        var loading_remind = loading_icon + '<span class="ml6">正在搜索，请稍候...</span>';
        var is_on = 'is-on';
        if (_this.data(is_on)) return;

        function ajax() {
            var data = {};
            var serializeObject = _this.serializeObject();
            $.each(serializeObject, function (key, val) {
                data[key] = val;
            });
            //循环插入_POST内容
            _this.find('input[name]').each(function () {
                var _th = $(this);
                var v = _th.val() || '';
                data[_th.attr('name')] = v;
            });

            _this.addClass(loading);
            _remind.html(loading_remind);
            _icon.html(loading_icon);
            _container.css({
                height: _container.outerHeight(),
                overflow: 'hidden',
            });
            $.post(
                ajax_url,
                data,
                function (result) {
                    var data_html = result.data;
                    if (data_html) {
                        _container.html('<div class="' + centent + '">' + data_html + '</div>').animate(
                            {
                                height: _container.children('.' + centent).outerHeight(),
                            },
                            200,
                            'swing',
                            function () {
                                _container.css({
                                    height: '',
                                    overflow: '',
                                    transition: '',
                                });
                            }
                        );
                    }
                    _this.removeClass(loading);
                    _remind.html(result.remind || '');
                    _icon.html(_icon_html);
                    auto_fun();
                },
                'json'
            );
        }

        function submitted(_input) {
            if (_input.val().length >= min_length) {
                ajax();
            } else {
                _remind.html(min_length_remind);
            }
        }

        _this
            .data(is_on, true)
            .find(input)
            .on(
                'input',
                debounce(function () {
                    submitted($(this));
                }, 500)
            )
            .on('keydown', function (event) {
                //禁止回车提交
                if (event.keyCode == 13) {
                    event.preventDefault();
                    submitted($(this));
                }
            });
    });
};

//搜索功能-恢复默认占位符
_win.bd.on('click', '.main-search [data-for]', function () {
    var _placeholder = $('.main-search').find('.scale-placeholder');
    if (_placeholder.length) {
        _placeholder.text(_placeholder.attr('default'));
    }
});

//搜索功能->删除多余的input
_win.bd.on('submit', '.main-search form', function () {
    var _this = $(this);
    var inputs = _this.serializeObject();
    $.each(inputs, function (k, v) {
        if ((!v || v == 'null') && k != 's') {
            _this.find('input[name="' + k + '"]').remove();
        }
    });
});

//搜索功能
_win.bd.on('click', '.main-search-btn', function () {
    var _this = $(this);
    var _search_form = $('.main-search form');
    $('.main-search').addClass('show');

    if (_search_form.length) {
        var attr_ = 'search-';
        var obj = {
            type: _this.attr(attr_ + 'type'),
            user: _this.attr(attr_ + 'user'),
            trem: _this.attr(attr_ + 'trem'),
        };

        $.each(obj, function (k, v) {
            if (v) {
                var _k = _search_form.find('input[name="' + k + '"]').val(v);
                var _data_for = _search_form.find('[data-for="' + k + '"][data-value="' + v + '"]').click();

                if (!_data_for.length && k == 'trem') {
                    var cat_name = _this.attr('trem-name');
                    var _cat_drop = _search_form.find('.cat-drop');

                    if (cat_name && _cat_drop.length) {
                        _cat_drop.append('<li data-for="trem" data-value="' + v + '"><a href="javascript:;">' + cat_name + '</a></li>');
                        _cat_drop.find('[data-value="' + v + '"]').click();
                    }
                }

                if (!_k.length) {
                    _search_form.append('<input type="hidden" name="' + k + '" value="' + v + '">');
                }
            }
        });

        var placeholder = _this.attr(attr_ + 'placeholder') || _search_form.find('.scale-placeholder').attr('default');
        _search_form.find('.scale-placeholder').text(placeholder);

        setTimeout(function () {
            _search_form.find('[name="s"]').focus();
        }, 100);
    }
});

//搜索页面关键词ajax
if ($('.search-page-ajaxpager').length) {
    _win.bd.on('click', '.page-search-box a.search_keywords', function () {
        var $this = $(this).clone();
        $this.addClass('ajax-next').attr('ajax-replace', 'true').attr('route', 'true');
        $('.search-page-ajaxpager').append($this);
        $this.click().remove();
        return false;
    });

    //搜索页面提交搜索ajax
    _win.bd.on('submit', '.page-search-box .search-form', function () {
        var $this = $(this);
        var data = $this.serializeObject();
        var url = new URL($this.attr('action'));
        url.search = new URLSearchParams(data).toString();

        var $link = $('<a class="ajax-next" ajax-replace="1" route="1" href="' + url.href + '"></a>');
        $('.search-page-ajaxpager').append($link);
        $link.click().remove();
        return false;
    });
}

function nav_folding() {
    var navbar_nav_t = '.navbar-nav';
    var sub_menu_t = 'sub-menu';
    var _collapse = $('.navbar-collapse');
    var surplus_width = _collapse.width();

    _collapse.find('>:not(' + navbar_nav_t + ')').each(function () {
        surplus_width -= $(this).outerWidth();
    });

    surplus_width -= $('.navbar-header').outerWidth() + 40;

    var navs_width = 66;
    var _folding = $('<li class="menu-item"><a href="javascript:void(0);"><svg class="em12" aria-hidden="true" data-viewbox="0 0 1024 1024" viewBox="0 0 1024 1024"><use xlink:href="#icon-menu_2"></use></svg></a><ul class="' + sub_menu_t + '"></ul></li>');
    $(navbar_nav_t + '>li').each(function () {
        navs_width += $(this).outerWidth();
        if (navs_width > surplus_width) {
            _folding.find('>.' + sub_menu_t).append($(this));
        }
    });

    if (_folding.find('>.' + sub_menu_t + ' li').length) {
        $(navbar_nav_t).append(_folding);
    }
}

//系统通知
window.debounce_notyf = debounce(notyf, 100, true);
function notyf(str, ys, time, id) {
    $('.notyn').length || _win.bd.append('<div class="notyn"></div>');
    ys = ys || 'success';
    if (ys == 'error') {
        ys = 'danger';
    }
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
_win.bd.on('click', '.noty1', function () {
    notyf_close($(this));
});

//切换密码显示
_win.passw = 1;
_win.bd.on('click', '.passw', function () {
    var _this = $(this);
    if (_win.passw == 1) {
        _this.find('.fa').addClass('fa-eye-slash');
        _this.siblings('input').attr('type', 'text');
        _win.passw = 2;
    } else {
        _this.find('.fa').removeClass('fa-eye-slash');
        _this.siblings('input').attr('type', 'password');
        _win.passw = 1;
    }
});

//绑定placeholder_scale操作
_win.bd.on(
    'input change focus',
    '.line-form-input',
    debounce(function () {
        placeholder_scale($(this));
    }, 100)
);

//执行placeholder_scale操作
function placeholder_scale(_this) {
    var val = _this.val();
    var placeholder = _this.siblings('.scale-placeholder');
    if (val.length > 0) {
        placeholder.addClass('is-focus');
    } else {
        placeholder.removeClass('is-focus');
    }
}

function zib_is_url(str) {
    return /^((http|https):\/\/)([a-z0-9-]{1,}.)?[a-z0-9-]{2,}.([a-z0-9-]{1,}.)?[a-z0-9]{2,}$/.test(str);
}

function is_mail(str) {
    return /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/.test(str);
}

//表单验证
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

//清空搜索关键词
_win.bd.on('click', '.trash-history-search', function () {
    if (confirm('确认要清空全部搜索记录？') == true) {
        $('.history-search')
            .slideUp()
            .delay(1000, function () {
                $(this).remove();
            });
        $.cookie('history_search', '');
    }
});

//用户签到
_win.bd.on('click', '.initiate-checkin', function () {
    zib_ajax(
        $(this),
        0,
        function (data) {
            if (data.details_link) {
                $('.initiate-checkin').each(function () {
                    var $link = $(data.details_link);
                    var _this = $(this);
                    var _class = _this.attr('class');
                    var _text = _this.attr('ed-text');
                    $link.addClass(_class).removeClass('initiate-checkin').html(_text);
                    _this.prop('outerHTML', $link.prop('outerHTML'));
                });
            }
        },
        '正在签到，请稍后...'
    );
});

//底部tabbar，购买
_win.bd.on('click', '.single-pay-tabbar', function () {
    scrollTo('#posts-pay', -50);
    $('#posts-pay .cashier-link').click();
});

/**wp-ajax-action */
_win.bd.on('submit', '[ajax-submit]', function () {
    var _this = $(this);
    var but = _this.attr('ajax-submit') || '.wp-ajax-submit,[zibajax="submit"]';
    return _this.find(but).click(), !1;
});

_win.bd.on('click', '.wp-ajax-submit,[zibajax="submit"]', function () {
    var _this = $(this);
    var confirm_text = _this.attr('data-confirm');
    if (confirm_text && !confirm(confirm_text)) {
        return false;
    }

    return zib_ajax($(this)), !1;
});

function is_captcha() {
    return window.captcha && !window.captcha.ticket;
}

/**
 * @description: ajax请求封装
 * @param {*} _this 按钮的jquery对象
 * @param {*} data 传递的数据
 * @param {*} success 成功后的回调函数
 * @param {*} noty 提示信息：”stop“为不提示
 * @param {*} no_loading 是否不显示加载动画
 * @return {*}
 */
function zib_ajax(_this, data, success, noty, no_loading) {
    if (_this.attr('disabled')) {
        return !1;
    }
    if (!data) {
        var _data = _this.attr('form-data');
        if (_data) {
            try {
                data = $.parseJSON(_data);
            } catch (e) {}
        }
        if (!data) {
            var form = _this.parents('form');
            data = form.serializeObject();
        }
    }

    var _action = _this.attr('form-action');
    if (_action) {
        data.action = _action;
    }

    //人机验证
    if (data.captcha_mode && is_captcha(data.captcha_mode)) {
        tbquire(['captcha'], function () {
            CaptchaOpen(_this, data.captcha_mode);
        });
        return !1;
    }

    if (window.captcha) {
        if (window.captcha._this) {
            delete window.captcha._this;
        }

        data.captcha = JSON.parse(JSON.stringify(window.captcha));
        window.captcha = {}; //只能使用一次
    }

    var _text = _this.html();
    var _loading = no_loading ? _text : '<i class="loading mr6"></i><text>请稍候</text>';
    noty != 'stop' && notyf(noty || '正在处理请稍后...', 'load', '', 'wp_ajax');
    _this.attr('disabled', true).html(_loading);
    var _url = _this.attr('ajax-href') || _win.ajax_url;

    $.ajax({
        type: 'POST',
        url: _url,
        data: data,
        dataType: 'json',
        error: function (n) {
            var _msg = '操作失败 ' + n.status + ' ' + n.statusText + '，请刷新页面后重试';
            if (n.responseText && n.responseText.indexOf('致命错误') > -1) {
                _msg = '网站遇到致命错误，请检查插件冲突或通过错误日志排除错误';
            }
            console.error('ajax请求错误，错误信息如下：', n);
            notyf(_msg, 'danger', '', noty != 'stop' ? 'wp_ajax' : '');
            _this.attr('disabled', false).html(_text);
        },
        success: function (n) {
            var ys = n.ys ? n.ys : n.error ? 'danger' : '';
            if (n.error) {
                _win.slidercaptcha = false;
            }
            if (noty != 'stop') {
                notyf(n.msg || '处理完成', ys, '', 'wp_ajax');
            } else if (n.msg) {
                notyf(n.msg, ys);
            }

            _this.attr('disabled', false).html(_text).trigger('zib_ajax.success', n); //完成
            $.isFunction(success) && success(n, _this, data);

            if (n.hide_modal) {
                _this.closest('.modal').modal('hide');
            }
            if (n.reload) {
                if (n.goto) {
                    window.location.href = n.goto;
                    window.location.reload;
                } else {
                    window.location.reload();
                }
            }
        },
    });
}

//AJAX执行完成后自动切换到下一个tab
_win.bd.on('zib_ajax.success', '[next-tab]', function (e, n) {
    var _next = $(this).attr('next-tab');
    if (_next && n && !n.error) {
        $('a[href="#' + _next + '"]').tab('show');
    }
});

/* erphpdown 登录使用弹出登录框
 * =========================================
 */
$('.erphp-login-must').each(function () {
    $(this).addClass('signin-loader');
});

//浏览器窗口调整自动化
_win.window.resize(
    debounce(function () {
        _wid = _win.window.width();
        auto_fun();
    }, 500)
);

$('.collapse').on('shown.bs.collapse', function () {
    auto_fun();
});

//文章限制高度
_win.bd.on('click', '.read-more-open', function () {
    $(this)
        .parents('.limit-height')
        .css({
            height: '',
            'max-height': '',
        })
        .find('.read-more')
        .remove();
});

function posts_limit_height() {
    if ($('.limit-height').length) {
        var r = $('.limit-height');
        if (r.data('posts-limit-ison')) {
            return;
        }
        var r_h = r.height();
        var r_m = r.attr('data-maxheight');
        if (~~r_h >= ~~r_m + 79) {
            var nn = '<div class="read-more"><a href="javascript:;" class="read-more-open">展开阅读全文<i class="fa ml10 fa-angle-down"></i></a></div>';
            r.append(nn).data('posts-limit-ison', true);
        }
    }
}

auto_fun();
thumb_dplayer(_wid > 640 ? '.posts-item .item-thumbnail,.forum-lists-cover' : '.posts-item,.forum-posts', '.video-thumb-box[video-url]');

//页面加载之后自动点击
$('[load-click]').attr('no-scroll', true).click();

//图片延迟懒加载-ias自动加载
$(document).on('lazybeforeunveil', function (e) {
    var _this = $(e.target);
    var lazyload_action = _this.attr('lazyload-action');
    setTimeout(function () {
        if (lazyload_action === 'ias') {
            if (_this.attr('remote-box')) {
                _this.click();
            } else {
                var _a = _this.is('.ajax-next,.ias-btn') ? _this : _this.find('.ajax-next,.ias-btn');
                _a.attr('no-scroll', true).click();
            }
        }
        if (lazyload_action == 'animated') {
            var animated = _this.attr('data-animated');
            animated && _this.addClass('animated ' + animated).css('visibility', 'unset');
        }
    }, 200);
    var bg = _this.attr('data-bg');
    if (bg) {
        _this.css('background-image', 'url(' + bg + ')');
    }
});

//性能检测，通过浏览器帧率
function fps_yh() {
    var lastTime = 0;
    var frames = 0;
    var fps = 0;
    var fps_average = 0;
    var currentTime;
    var stop = 0;

    function updateFPS() {
        currentTime = performance.now();
        frames++;
        if (currentTime - lastTime >= 300) {
            fps = Math.round((frames * 1000) / (currentTime - lastTime));
            frames = 0;
            lastTime = currentTime;

            if (fps > 5 && lastTime > 1600) {
                fps_average = fps_average || fps;
                fps_average = (fps_average + fps) / 2;

                if (fps_average < 20) {
                    stop++;
                }

                if (stop > 5) {
                    //执行函数
                    $('body').addClass('fps-accelerat');
                }
            }
        }

        if (stop <= 5 && lastTime < 15000) {
            requestAnimationFrame(updateFPS);
        } else {
            setCookie();
        }
    }

    function setCookie() {
        $.cookie('fps_accelerat', ~~fps_average, {
            path: '/',
            expires: 604800,
        });
    }

    requestAnimationFrame(updateFPS);
}

//fps性能优化
if (!$.cookie('fps_accelerat') && !$('body').hasClass('fps-accelerat')) {
    fps_yh();
}

/**------小工具：横向滚动合作伙伴模块--------**/
function partners_scroll_init() {
    /**
     * 初始化单个 widget 实例
     * 核心思路：测量单组卡片真实宽度，按需克隆足够数量的 group，
     * 然后让 CSS 动画精确按照"一组宽度"进行位移，达成无缝无断层循环
     */
    function init(widget) {
        if (!widget || widget.dataset.zpsReady === '1') return;
        var speed = parseFloat(widget.style.getPropertyValue('--zps-speed')) || 3;
        var rows = widget.querySelectorAll('[data-zps-row]');

        rows.forEach(function (row) {
            var track = row.querySelector('[data-zps-track]');
            if (!track) return;
            var seed = track.querySelector('.zps-group');
            if (!seed) return;

            /**
             * 按需克隆 group，确保 track 宽度 ≥ 视口宽度 + 单组宽度
             * 这样动画位移 = 单组宽度时，视口仍被完全填满，不会露出空白
             */
            var setup = function () {
                //先移除之前克隆的额外 group，保留最原始的那一组作为基准
                var extras = track.querySelectorAll('.zps-group.zps-clone');
                extras.forEach(function (el) {
                    el.remove();
                });

                var groupWidth = seed.scrollWidth;
                if (groupWidth < 10) return;

                var rowWidth = row.clientWidth || widget.clientWidth || window.innerWidth;
                //至少需要 2 份：1 份作为可见内容 + 1 份作为位移后补充
                //若单组宽度小于视口，需要克隆更多份以避免空白
                var needTotal = Math.max(groupWidth * 2, rowWidth + groupWidth);
                var copies = Math.ceil(needTotal / groupWidth);
                if (copies < 2) copies = 2;

                //克隆到目标数量（当前只有 1 份种子 group，需要再克隆 copies - 1 份）
                for (var i = 1; i < copies; i++) {
                    var clone = seed.cloneNode(true);
                    clone.classList.add('zps-clone');
                    clone.setAttribute('aria-hidden', 'true');
                    track.appendChild(clone);
                }

                //精确位移：每次循环移动"一组宽度"，保证内容完美对齐
                track.style.setProperty('--zps-shift', '-' + groupWidth + 'px');

                //按"每 100px 所需秒数"计算动画时长，让视觉滚动速度与卡片数量无关
                var duration = (groupWidth / 100) * speed;
                if (duration < 4) duration = 4;
                track.style.setProperty('--zps-duration', duration.toFixed(2) + 's');

                //显示出来（避免闪烁）
                track.setAttribute('data-zps-ready', '1');
            };

            //初次执行
            setup();

            //图片异步加载会改变 group 宽度，加载完后重算
            var imgs = seed.querySelectorAll('img');
            var pending = 0;
            imgs.forEach(function (img) {
                if (img.complete && img.naturalWidth > 0) return;
                pending++;
                var done = function () {
                    pending--;
                    if (pending <= 0) setup();
                };
                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
            });

            //窗口尺寸变化时重算（节流）
            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(setup, 200);
            });
        });

        widget.dataset.zpsReady = '1';
    }

    function initAll() {
        document.querySelectorAll('.zib-partners-scroll').forEach(init);
    }

    initAll();
}

/** 智能翻译 */
function translate_init() {
    var translate_config = _win.translate_config;
    if (translate_config.s) {
        tbquire(['translate'], function () {
            translate.selectLanguageTag.show = false;
            translate.language.setLocal(translate_config.local || 'chinese_simplified');
            if (translate_config.service_url) {
                translate.request.setHost(translate_config.service_url);
            }

            if (translate_config.ignore_text) {
                translate.ignore.text = translate_config.ignore_text;
            }

            if (translate_config.nomenclature && translate_config.nomenclature.length > 0) {
                //循环添加自定义术语
                $.each(translate_config.nomenclature, function (index, item) {
                    var from = item.from;
                    var to = item.to;
                    var value = item.value;

                    if (from == 'auto') {
                        from = '';
                    }

                    if (value && to) {
                        translate.nomenclature.append(from, to, value);
                    }
                });
            }

            if (translate_config.auto_discriminate_local) {
                translate.setAutoDiscriminateLocalLanguage();
            }

            if (translate_config.range && translate_config.range.length > 0) {
                translate.language.translateLanguagesRange = translate_config.range;
            }

            if (translate_config.loading) {
                translate.progress.api.startUITip(); //开始翻译时显示提示
            }

            var $language_dropdown = $('.language-dropdown .dropdown-menu');

            var translateStart = debounce(
                function () {
                    $('[data-language]').removeClass('active');
                    $language_dropdown.loading('show');
                },
                400,
                true
            );

            translate.lifecycle.changeLanguage.trigger.push(translateStart);

            //翻译结束后执行的函数
            var renderFinish = debounce(function (uuid) {
                $language_dropdown.loading('hide');
                $("[data-language='" + uuid.to + "']").addClass('active');
            }, 400);

            //翻译结束后执行的函数
            translate.lifecycle.execute.finally.push(renderFinish);

            //开始之前执行的函数
            if (_wid > 996) {
                $('.navbar-top .nav>li>a>.menu-item-title').each(function () {
                    var _this = $(this);
                    _this.css({
                        'max-width': ~~_this.outerWidth(true) + 5 + 'px',
                    });
                });
            }

            translate.ignore.class.push('translate-ignore');
            translate.ignore.tag.push('svg');

            translate.service.use(translate_config.service_use || 'client.edge');
            translate.listener.start(); //开启页面元素动态监控，js改变的内容也会被翻译，参考文档： http://translate.zvo.cn/4067.html
            translate.execute(); //完成翻译初始化，进行翻译
        });
    }
}

_win.bd.on('click', '.translate-button', function () {
    var _this = $(this);
    var language = _this.attr('data-language');
    translate && translate.changeLanguage(language);
});

/**
 * 模块出现动画
 * 给带有 data-animation-in 属性的元素绑定"进入视口"动画
 *
 * 用法：
 *   <div data-animation-in="fadeInUp">...</div>
 *   <div data-animation-in="zoomIn" data-animation-delay="200" data-animation-duration="800">...</div>
 *   <div data-animation-in="slideInLeft" data-animation-repeat>...</div>   每次进入视口都重播
 *
 * 属性说明：
 *   data-animation-in       动画 class 名（与 CSS 约定一致，会被加为 "animated xxx"）
 *   data-animation-delay    可选，纯数字视为 ms，带单位则原样应用（如 "0.3s"）
 *   data-animation-duration 可选，同上
 *   data-animation-repeat   可选，存在即离开视口后重置，再次进入再播；默认只播一次
 *
 * 特性：
 *   - 使用 IntersectionObserver 单例，内存占用低
 *   - MutationObserver 自动接管动态插入的元素（AJAX 加载/懒加载/无限滚动）
 *   - 尊重 prefers-reduced-motion 用户偏好
 *   - 环境不支持 IO 时直接显示，保证元素可见性
 */
(function () {
    var SELECTOR_class = 'obs-animate';
    var SELECTOR = '.' + SELECTOR_class;
    var BASE_CLS = 'in-view';
    var OBS_FLAG = '__zaniObserved';

    //旧浏览器兜底 / 用户偏好减少动效：直接显示，不做动画
    var noAnim = !('IntersectionObserver' in window) || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    if (noAnim) {
        removeAnimation(SELECTOR);
        return;
    }

    var rootMargin = '100px 0px 0px 0px';

    //执行函数添加防抖
    function execute(entries) {
        console.log(entries);
        for (var i = 0; i < entries.length; i++) {
            var entry = entries[i];
            var el = entry.target;
            var $el = $(el);
            $el.css('transition-delay', '');

            var is_repeat = el.hasAttribute('data-animation-repeat');
            if (entry.isIntersecting) {
                $el.css('transition-delay', i * 0.11 + 's');
                $el.addClass(BASE_CLS);

                //默认触发一次后停止观察；声明 data-animation-repeat 的保留循环
                if (!is_repeat) {
                    stop(el);
                }
            } else if (is_repeat) {
                //循环模式：离开视口时移除 class，下次进入再加回来
                $el.removeClass(BASE_CLS);
            }
        }
    }

    //单例 observer，所有目标共用
    var io = new IntersectionObserver(execute, {
        root: null,
        //稍微提前 10% 触发，视觉更自然
        rootMargin: rootMargin,
        threshold: 0.01,
    });

    //结束
    //停止监控，移出class，移出属性
    function stop(el) {
        if (!el || el.nodeType !== 1 || !el[OBS_FLAG]) {
            return;
        }
        el[OBS_FLAG] = false;
        io.unobserve(el);
        setTimeout(function () {
            removeAnimation(el);
        }, 50);
    }

    function removeAnimation(el) {
        var animation = $(el).attr('data-animation');
        var animation_class = 'ani-' + animation;
        $(el)
            .removeClass(BASE_CLS + ' ' + SELECTOR_class + ' ' + animation_class)
            .removeAttr('data-animation');
    }

    function register(el) {
        if (!el || el.nodeType !== 1 || el[OBS_FLAG]) {
            return;
        }
        el[OBS_FLAG] = true;
        io.observe(el);
    }

    function scan() {
        $(SELECTOR).each(function () {
            register(this);
        });
    }

    //首次扫描
    scan();
})();

//页面加载完毕之后加载的函数
$(document).ready(function () {
    setTimeout(function () {
        //页面加载完毕之后再延迟两秒加载的函数

        //检测placeholder_scale操作
        $('.line-form-input').each(function () {
            placeholder_scale($(this));
        });

        //文章阅读数量记录
        if (_win.views) {
            $.post(_win.ajax_url, {
                action: 'views_record',
                id: _win.views,
            });
        }

        setTimeout(function () {
            //获取登录用户数据，延迟2秒执行
            $.post(_win.ajax_url, {
                action: 'get_current_user',
            });
        }, 1000);

        //内容高度限制
        posts_limit_height();

        //触发scroll
        _win.window.trigger('scroll');
    }, 1000);

    //智能翻译
    translate_init();
    //横向滚动合作伙伴
    partners_scroll_init();

    /*菜单*/
    $('.navbar-top li.menu-item-has-children>a').each(function () {
        $(this).append('<i class="fa fa-angle-down ml6"></i>');
    });
    //菜单栏目超宽后自动折叠
    if (_wid > 996 && $('.header').height() > 81) {
        nav_folding();
    }

    //内容高度限制
    posts_limit_height();

    auto_maxHeight();
    //文章亮点保持相同高度
    if ($('.feature').length) {
        var _feh = 0,
            _fehm = 0;
        $('.feature').each(function () {
            var _th = $(this);
            (_feh = _th.find('.feature-icon').innerHeight() + _th.find('.feature-title').innerHeight() + _th.find('.feature-note').innerHeight()) > _fehm && (_fehm = _feh);
        });
        $('.feature').css('height', _fehm);
    }

    //js二维码
    $('.qrcode').length &&
        tbquire(['qrcode'], function () {
            $('.qrcode').each(function () {
                var _this = $(this),
                    text = _this.attr('data-qrcode'),
                    size = _this.attr('data-size') || 160;

                _this.qrcode({
                    width: size,
                    height: size,
                    correctLevel: 0,
                    text: text || document.URL,
                    background: '#fff',
                    foreground: '#333',
                });
            });
        });

    //图片延迟懒加载-ias自动加载
    $(document).on('lazyloaded', function (e) {
        var _this = $(e.target);
        swiper_tab_AutoHeight(_this);
    });

    $(document).on('lazyloaded', '.gradient-bg img', function (e) {
        var _this = $(e.target);
        var _parent = _this.closest('.gradient-bg');
        var opacity = _parent.attr('data-opacity');
        var c_11 = opacity ? 'a' : '';
        var c_12 = opacity ? ',' + opacity : '';

        tbquire(['grade'], function () {
            Grade(_parent[0], null, function (gradientData) {
                gradientData = gradientData[0];
                var rgba = gradientData.gradientData;
                var c_1 = 'rgb' + c_11 + '(' + rgba[0].rgba.slice(0, 3).join(',') + c_12 + ') 10%';
                var c_2 = 'rgb' + c_11 + '(' + rgba[1].rgba.slice(0, 3).join(',') + c_12 + ') 80%';

                $(gradientData.element)
                    .addClass('gradient-bg-loaded')
                    .css('background-image', 'linear-gradient(135deg, ' + c_1 + ', ' + c_2 + ')');
            });
        });
    });

    _win.qj_loading &&
        $('.qj_loading')
            .fadeOut(500)
            .delay(1e3, function () {
                $(this).remove(), $('#qj_dh_css').remove();
            });
    /*一言功能*/
    function yiyan_nr(n) {
        var yylink = _win.uri + '/yiyan/qv-yiyan.php',
            y_nr;
        $.ajax({
            type: 'POST',
            url: yylink,
        }).done(function (i) {
            var lines = i
                .replace(/\r\n|\r/g, '/&/')
                .trim()
                .split('/&/');
            if (lines) {
                var type = n.attr('type');
                if (type) {
                    y_nr = type === 'en' ? lines[0] : lines[1];
                } else {
                    y_nr = '<div class="cn">' + lines[0] + '</div><div class="en">' + lines[1] + '</div>';
                }
                n.html(y_nr);
            }
        });
    }

    $('.yiyan').each(function () {
        yiyan_nr($(this));
    }),
        setInterval(function () {
            $('.yiyan').each(function () {
                yiyan_nr($(this));
            });
        }, 3e4);
    $('.yiyan').on('click', function () {
        yiyan_nr($(this));
    });

    /*文章目录*/
    $('[data-nav] h1,[data-nav] h2,[data-nav] h3,[data-nav] h4').length > 2 && tbquire(['section-navs']);

    //手势控制
    $('[mini-touch]').each(function () {
        var _this = $(this);
        var fx = _this.attr('touch-direction');
        _this.minitouch({
            direction: fx,
            onEnd: function (e) {
                //移动端菜单
                if (_this.attr('mini-touch') === 'mobile-nav') {
                    $('body').removeClass('mobile-navbar-show');
                } else {
                    e.removeClass('show');
                }
            },
        });
    });
});

console.log('\n' + ' %c Zibll Theme %c https://zibll.com ' + '\n', 'color: #fadfa3; background: #030307; padding:3px; font-size:12px;', 'color: #2abd4e;background: #16171a; padding: 3px; font-size: 12px;');
