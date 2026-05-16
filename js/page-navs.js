/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-09-29 13:18:40
 * @LastEditTime  : 2020-10-17 21:12:01
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */
//文章导航
$("button[evt='action']").on("click", function (t) {
    t = t || window.event;
    var a = t.target || t.srcElement,
        e = $(a),
        n = e.parents("form"),
        r = n.serializeObject(),
        c = [];
    return $("#cat-sortable-ok .sortable-cat-item").each(function () {
        c.push($(this).attr("data-id"));
    }), r.navs_show_cat_id = c, save_userdata(r, "保存成功！", "reload"), !1;
});

function save_userdata(b, c, d) {
    notyf("正在处理请稍等...", "load", "", "user_ajax"),
        c = c || "处理完成";
    $.ajax({
        type: "POST",
        url: _win.uri + '/action/post_navs.php',
        data: b,
        dataType: "json",
        success: function (n) {
            if (n.error) {
                n.msg && notyf(n.msg, (n.error ? 'danger' : ""), "", "user_ajax");
            } else {
                notyf(n.msg || c, "", "", "user_ajax");
                "reload" == d && location.reload();
            }
        }
    });
}

$(document).ready(function () {
    $("#cat-sortable").length && tbquire(["sortablejs"], function () {
        var e = document.getElementById("cat-sortable"),
            t = document.getElementById("cat-sortable-ok"),
            a = {
                group: "cat_shared",
                multiDrag: !0,
                chosenClass: "chosen",
                fallbackClass: !0,
                animation: 400
            };
        Sortable.create(e, a), Sortable.create(t, a);
    });
});