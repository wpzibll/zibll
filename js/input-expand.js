/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-17 23:26:27
 * @LastEditTime: 2025-09-25 18:48:45
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题||编辑框的额外按钮
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

//内容追加到编辑框
function grin(_this, val, fun) {
    var textarea = _this.parents('form').find('textarea.grin');
    var t_val = textarea.val();
    textarea
        .val(t_val + val)
        .focus()
        .click();
    $.isFunction(fun) && fun(_this, val);
}

//编辑框添加表情
_win.bd.on('click', '.dropdown-smilie .smilie-icon', function () {
    var _this = $(this);
    var smilie = _this.attr('data-smilie');
    smilie = '[g=' + smilie + ']';
    return grin(_this, smilie);
});

//编辑框添加代码
_win.bd.on('click', '.dropdown-code [type="submit"]', function () {
    var _this = $(this);
    var val = _this.parents('.dropdown-code').find('textarea').val();
    if (val.length < 2) return void notyf('请输入代码', 'warning');

    val = '[code]\n' + val + '\n[/code]\n';
    return grin(_this, val);
});

//编辑框添加图片
_win.bd.on('click', '.dropdown-image [type="submit"]', function () {
    var _this = $(this);
    var val = _this.parents('.dropdown-image').find('textarea').val();
    if (val.length < 6) return void notyf('请输入正确的图片地址', 'warning');
    val = '[img=' + val + ']\n';
    return grin(_this, val);
});

//上传图片
_win.bd.on('miniuploaded', '.input-expand-upload', function (a, data) {
    var img = '[img=' + data.img_url + ']\n';
    return grin($(this), img);
});

//按钮展开
_win.bd.on('click', '.btn-input-expand', function () {
    return $(this).parent().toggleClass('open').trigger('toggleClass'), !1;
});

//保存我的快捷回复后，自动更新
_win.bd.on('zib_ajax.success', '.user-quick-often-save-btn', function (e, data) {
    if (data.item) {
        $('.quick-reply-myitem-box').html(data.item);
    }
});

//快捷回复；选择内容，弹出操作按钮
_win.bd.on('click', '.quick-reply-item', function () {
    var _this = $(this);
    var quick_reply_action = 'quick-reply-action';
    var quick_active = 'quick-active';
    _this.siblings().removeClass(quick_active);
    _this
        .parent()
        .find('.' + quick_reply_action)
        .remove();

    _this
        .data('quick-reply', _this.text())
        .addClass('quick-active')
        .append('<div class="' + quick_reply_action + '"><div class="modal-buts but-average"><button type="button" class="but quick-reply-btn-insert" href="javascript:;">插入</button><button type="button" class="but c-blue padding-lg quick-reply-btn-send">发送</button></div></div>');
});

//快捷回复：插入内容或发送
_win.bd.on('click', '.quick-reply-action button', function () {
    var _this = $(this);
    var form = _this.closest('form'); //唯一的表单
    var quick_reply = _this.parents('.quick-reply-item').data('quick-reply');
    var textarea = form.find('textarea.grin');
    textarea.val(quick_reply);
    if (_this.hasClass('quick-reply-btn-send')) {
        form.find('.input-expand-submit').click();
    } else {
        textarea.focus();
    }
});
