/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime: 2025-09-29 00:43:40
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|消息系统的JS
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

_win.bd.on('click', '[ajax-target="#user_private_window"]', function () {
    _win.private_no_scroll = false;
});

/**消息列表滚动到最底部 */
function scroll_down() {
    var $e = $('.private-window-content');
    if ($e.length && !_win.private_no_scroll) {
        var _top = $e.prop('scrollHeight');
        _win.private_no_scroll = true;
        $e.animate(
            {
                scrollTop: _top,
            },
            300,
            'swing'
        );
    }
} 

/**ctrl+回车键提交 */
_win.bd.on('keydown', '#receive', function (e) {
    var keycode = e.which || e.keyCode;
    if ((e.metaKey || e.ctrlKey) && keycode == 13) {
        $(this).parents('.from-private').find('.send-private').click();
    }
});

/**发送私信 */
_win.bd.on('click', '.send-private', function () {
    var _this = $(this);
    zib_ajax(_this, 0, function (n) {
        if (n.html) {
            var _par = _this.parents('.private-window');
            _par.find('.private-window-content').append(n.html);
            _par.find('textarea').val('');
            _win.private_no_scroll = false;
            auto_fun();
        }
    });
    return !1;
});

/**消息内容翻页 */
_win.bd.on('click', '.private-next', function () {
    var _loader = '<div class="text-center padding-h10 px12"><i class="loading"></i><div>';
    post_ajax($(this).attr('no-scroll', true), '.private-window-content', '.private-window-content', '.private-item', _loader, '.private-pag', '.private-next', 'private-next px12', !1, '<span class="px12">已加载全部</span>', !0, '<i class="fa fa-angle-up mr10"></i>继续加载');
    return !1;
});

/*对话列表翻页 */
_win.bd.on('click', '.chat-next', function () {
    post_ajax($(this), '#user_chat_lists', '#user_chat_lists', '.chat-lists', '', '.chat-pag', '.chat-next');
    return !1;
});

/**切换加入黑名单
 * 清空聊天记录
 * 全部标为已读
 */
_win.bd.on('click', '.ajax-blacklist,.ajax-clear-msg,.ajax-readed', function () {
    var _this = $(this);
    var $text = _this.text();

    if (confirm('确认' + $text + '？') == 1) {
        zib_ajax(_this, 0, function (n) {
            if (n.text) {
                _this.find('text').text(n.text);
            }
        });
    }
    return !1;
});

/*删除new标签 */
_win.bd.on('click', '[ajax-target="#user_private_window"],[ajax-tab="#user_msg_content"]', function () {
    $(this).find('.avatar-img badge,.msg-img badge').remove();
});

_win.bd.on('post_ajax.ed', '#user_msg_content,#user_private_window', function (e, c) {
    var _new_count = $(c).find('[msg-new-count-obj]');
    if (!_new_count.length) {
        return;
    }
    var counts = $.parseJSON(_new_count.attr('msg-new-count-obj'));
    $.each(counts, function (e, count) {
        var _badge = $('badge[msg-cat="' + e + '"]');
        if (_badge.length) {
            if (count > 0) {
                _badge.html(count);
            } else {
                _badge.remove();
            }
        }
    });
});
