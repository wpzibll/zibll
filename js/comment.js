/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime : 2026-04-11 15:19:53
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

$.fn.FixedInput = function (action) {
    var _this = $(this);
    if (!_this.attr('on-start')) {
        _this
            .on('click', '.fixed-body', function () {
                _this.FixedInput('hide');
            })
            .attr('on-start', true);
    }

    switch (action) {
        case 'show':
            _this.addClass('show');
            return setTimeout(() => {
                $(_this.find('textarea,input')[0]).focus();
            }, 100);
        case 'hide':
            _this.find('#cancel-comment-reply-link').click();
            _this.find('input,textarea').blur();
            _this.find('.dropup').removeClass('open');
            return _this.removeClass('show');
        default:
    }
};

//移动端浮动的input|暂时仅用在评论
_win.bd.on('click', '[fixed-input]', function () {
    var _this = $(this);
    //	var _input = $(_this.attr('data-input'));
    var _fixed = $(_this.attr('fixed-input'));
    scrollTopTo('#comments');
    _fixed.FixedInput('show');
    //_input.length && _input.focus();
});

$comments = $('#comments-title');
$cancel = $('#cancel-comment-reply-link');
$author = $('#comment-user-info');
$submit = $('#commentform #submit');
$com_ajax_url = _win.ajax_url;
$com_list = $('#postcomments .commentlist');

//评论ajax翻页
_win.bd.on('click', '.commentlist .pagenav a,.comment-orderby', function () {
    var _this = $(this);
    $('#cancel-comment-reply-link').click();

    if (_this.hasClass('pag-jump')) {
        _this.addClass('ajax-next');
        return;
    }

    var ajax_replace = !_this.attr('no-replace');
    return post_ajax($(this), '.commentlist', '.commentlist', '>.comment', '', '.pagenav', '', '', ajax_replace);
});

//编辑按钮，回复
_win.bd.on('click', '.comment-edit-link,.comment-reply-link', function () {
    var _this = $(this),
        commentid = _this.attr('data-commentid');
    var is_edit = _this.hasClass('comment-edit-link');
    return addComment.moveForm(_this, 'div-comment-' + commentid, commentid, 'respond', _this.attr('data-postid'), is_edit), scrollTopTo($('#div-comment-' + commentid), -10), !1;
});

//回车提交
_win.bd.on('keydown', '#comment', function (e) {
    var keycode = e.which || e.keyCode;
    if ((e.metaKey || e.ctrlKey) && keycode == 13) {
        $(this).closest('#commentform').find('#submit').click();
    }
});

//删除评论
_win.bd.on('click', '.comment-trash-link', function () {
    var _this = $(this);
    var commentid = _this.attr('data-commentid');
    var trash_data = {
        action: 'trash_comment',
        comment_id: commentid,
        _wpnonce: _this.attr('data-nonce'),
    };
    if (confirm('确认要删除此评论吗？') == 1) {
        zib_ajax(
            _this,
            trash_data,
            function (n) {
                n.error ||
                    $('#comment-' + commentid)
                        .slideUp()
                        .delay(1000, function () {
                            $(this).remove();
                        });
            },
            '正在处理请稍后...'
        );
    }
});

//审核评论
_win.bd.on('click', '.comment-approve-link', function () {
    var _this = $(this);
    var commentid = _this.attr('data-commentid');
    var trash_data = {
        action: 'approve_comment',
        comment_id: commentid,
        _wpnonce: _this.attr('data-nonce'),
    };
    var reply = $('#div-comment-' + commentid).find('.reply-link');

    zib_ajax(_this, trash_data, function (n) {
        if (!n.error) {
            var _text = n.status == 'hold' ? '批准' : '驳回';
            var _class = n.status == 'hold' ? 'approve' : 'unapprove';
            var _badg = n.status == 'hold' ? '<span class="badg c-red badg-sm">待审核</span>' : '';

            if (n.status == 'hold') {
                reply.remove();
            } else {
                reply.length || _this.parents('.dropdown').before('<span class="reply-link"><a class="comment-reply-link" data-commentid="' + commentid + '" href="javascript:;">回复</a></span>');
            }

            $('#div-comment-' + commentid)
                .find('.badge-approve')
                .html(_badg);
            _this.removeClass('approve unapprove').addClass(_class).find('text').html(_text);
        }
    });
});

function htmlEscapes(val) {
    var htmlEscapes = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    };
    var a = val.replace(/[&<>"']/g, function (match) {
        return htmlEscapes[match];
    });
    return a;
}

function htmlUnescapes(val) {
    var htmlUnescapes = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'",
    };
    var a = val.replace(/&(amp|lt|gt|quot|#039);/g, function (match) {
        return htmlUnescapes[match];
    });
    return a;
}

//提交评论
_win.bd.on('click', '#commentform #submit', function () {
    var _this = $(this);
    var _form = _this.parents('#commentform');
    var inputs = _form.serializeObject();
    if ($author.length && $author.attr('require_name_email')) {
        if (inputs.author.length < 2 || inputs.email.length < 4) {
            return notyf('请输入昵称和邮箱', 'warning'), $author.addClass('open').find('[name="author"]').focus(), !1;
        }
        if (!is_mail(inputs.email)) {
            return notyf('邮箱格式错误', 'warning'), $author.addClass('open').find('[name="email"]').focus(), !1;
        }
    }
    if (inputs.comment.length < 4) {
        return notyf('评论内容过少', 'warning'), $('#comment').focus(), !1;
    }

    inputs.comment = htmlEscapes(inputs.comment);

    inputs.action = 'submit_comment';
    zib_ajax(_this, inputs, function (n) {
        var data = n.html;
        if (!n.error && data) {
            if (inputs.edit_comment_ID) {
                //编辑评论后提交
                $cancel.click();
                $('#comment-content-' + inputs.edit_comment_ID).html(data);
            } else {
                //新建评论
                var data = data.replace(/class="comment/, 'style="display:none;" class="comment ajax-comment');
                var respond = $('#respond');
                var $com_list = $('#postcomments .commentlist');
                var is_comment_parent = respond.parent().parent('.comment');

                if (is_comment_parent.length) {
                    var _children = is_comment_parent.find('>.children');
                    if (_children.length) {
                        _children.prepend(data);
                    } else {
                        is_comment_parent.append('<ul class="children">' + data + '</ul>');
                    }
                } else {
                    if (!$com_list.length) {
                        respond.after('<div id="postcomments"><ol class="commentlist list-unstyled">' + data + '</ol></div>');
                    } else {
                        var $order = $('#postcomments .commentlist>.comment-filter');
                        if ($order.length) {
                            $order.after(data);
                        } else {
                            $com_list.prepend(data);
                        }
                    }
                }

                if ($('[reply-show]').length && n.comment) {
                    if (~~n.comment.comment_approved > 0) {
                        var hash = $($('[reply-show]')[0]).attr('reload-hash'); //跳转

                        if (hash) {
                            window.location.hash = hash;
                        }
                        window.location.reload();
                    } else {
                        setTimeout(function () {
                            notyf('审核后通过后即可查看隐藏内容', 'warning');
                        }, 200);
                    }
                }

                $cancel.click();
                $('#postcomments .ajax-comment').slideDown(200).removeClass('ajax-comment');
                $('#postcomments .comment-null').slideUp(200);
            }
            auto_fun();
            wait_for(n.wait_time);
            $('#comment').val('');
            $('[name="canvas_yz"]').val('');
            $('.imagecaptcha').click();
            $('#respond').FixedInput('hide');
        }
    });
    return false;
});

var addComment = {
    moveForm: function (_this, commId, parentId, respondId, postId, num) {
        var t = this,
            div,
            comm = t.I(commId),
            respond = t.I(respondId),
            cancel = t.I('cancel-comment-reply-link'),
            parent = t.I('comment_parent'),
            post = t.I('comment_post_ID');
        var action_text_r = $('#' + respondId + ' .action-text');
        $('.comment-footer').show();
        if (num) {
            var $submit_html = $submit.html();
            action_text_r.html('编辑此内容');
            t.I('comment').value = ''; //清空内容
            $('#comment_parent').attr('name', 'edit_comment_ID');
            $submit.attr('disabled', true).html('<i class="loading mr6"></i><span>加载中</span>');
            $('#comment').attr('disabled', true).val('正在获取内容，请稍后...');
            $('#' + commId)
                .find('.comment-footer')
                .hide();
            var data = {
                action: 'get_comment',
                comment_id: parentId,
            };
            zib_ajax(
                _this,
                data,
                function (n) {
                    n.error && $cancel.click();
                    $submit.html($submit_html).attr('disabled', false);
                    $('#comment').attr('disabled', false).val(htmlUnescapes(n.comment_content)).focus();
                },
                'stop'
            );
        }

        action_text_r.html(_this.attr('data-replyto'));

        t.respondId = respondId;
        postId = postId || false;

        if (!t.I('wp-temp-form-div')) {
            div = document.createElement('div');
            div.id = 'wp-temp-form-div';
            div.style.display = 'none';
            respond.parentNode.insertBefore(div, respond);
        }
        !comm ? ((temp = t.I('wp-temp-form-div')), (t.I('comment_parent').value = '0'), temp.parentNode.insertBefore(respond, temp), temp.parentNode.removeChild(temp)) : comm.parentNode.insertBefore(respond, comm.nextSibling);

        // pcsheight()
        if (post && postId) post.value = postId;
        parent.value = parentId;
        cancel.style.display = '';
        cancel.onclick = function () {
            var t = addComment,
                temp = t.I('wp-temp-form-div'),
                respond = t.I(t.respondId);
            $('#comment_parent').attr('name', 'comment_parent');
            t.I('comment_parent').value = '0';
            $('.comment-footer').show();
            $('#comment').val('').trigger('input');
            setTimeout(function () {
                $('#' + respondId).removeClass('show');
            }, 10);
            action_text_r.html('');
            if (temp && respond) {
                temp.parentNode.insertBefore(respond, temp);
                temp.parentNode.removeChild(temp);
            }
            this.style.display = 'none';
            this.onclick = null;
            return false;
        };
        try {
            setTimeout(function () {
                $('#' + respondId).FixedInput('show');
            }, 10);
        } catch (e) {}
        return false;
    },
    I: function (e) {
        return document.getElementById(e);
    },
};

//等待
function wait_for($time) {
    var wait = $time || 15;
    var $submit_html = $submit.html();

    function wait_ing() {
        if (wait > 0) {
            $submit.html('<div style="width:55px;"><i class="loading mr10"></i>' + wait + '</div>').attr('disabled', true);
            wait--;
            setTimeout(wait_ing, 1000);
        } else {
            $submit.html($submit_html).attr('disabled', false);
            wait = 15;
        }
    }

    wait_ing();
}
