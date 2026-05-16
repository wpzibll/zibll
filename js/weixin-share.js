/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2022-04-02 15:18:55
 * @LastEditTime: 2022-04-02 15:25:58
 */


tbquire(["weixin-sdk"], function () {
    var _data = WeChatShareDate;
    wx.config({
        debug: false,
        appId: _data.appId,
        timestamp: _data.timestamp,
        nonceStr: _data.nonceStr,
        signature: _data.signature,
        jsApiList: [
            'updateTimelineShareData',
            'updateAppMessageShareData'
        ]
    });

    wx.ready(function () {
        var _title = _data.title || document.title;
        var _link = _data.url || window.location.href;
        var _desc = _data.desc || $('meta[name="description"]').attr('content');
        var _img = _data.img;

        wx.updateTimelineShareData({
            title: _title,
            link: _link,
            imgUrl: _img
        });
        wx.updateAppMessageShareData({
            title: _title,
            desc: _desc,
            link: _link,
            imgUrl: _img
        });
    });
});