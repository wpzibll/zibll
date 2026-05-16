/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-10-28 13:16:44
 * @LastEditTime: 2021-12-22 22:58:32
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

function copyText(text, success, error, _this) {
    // 数字没有 .length 不能执行selectText 需要转化成字符串
    var textString = text.toString();
    var input = document.querySelector('#copy-input');
    if (!input) {
        input = document.createElement('input');
        input.id = "copy-input";
        input.readOnly = "readOnly"; // 防止ios聚焦触发键盘事件
        input.style.position = "fixed";
        input.style.left = "-2000px";
        input.style.zIndex = "-1000";
        _this.parentNode.appendChild(input)
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
        if (textbox.createTextRange) { //ie
            var range = textbox.createTextRange();
            range.collapse(true);
            range.moveStart('character', startIndex); //起始光标
            range.moveEnd('character', stopIndex - startIndex); //结束光标
            range.select(); //不兼容苹果
        } else { //firefox/chrome
            textbox.setSelectionRange(startIndex, stopIndex);
            textbox.select();
        }
    }
}

_win.bd.on('click', "[data-clipboard-text]", 'clipboard', function (e) {
    var _this = $(this);
    var text = _this.attr('data-clipboard-text');
    var tag = _this.attr('data-clipboard-tag') || '内容';

    copyText(text, function () {
        notyf(tag + "已复制")
    }, function () {
        notyf(tag + "复制失败，请手动复制", 'danger')
    }, this);
})