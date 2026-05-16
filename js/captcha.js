/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2022-04-05 14:37:36
 * @LastEditTime: 2023-11-02 20:21:45
 */

function CaptchaInit() {
    var _mode = $('[machine-verification]');
    var mode = _mode.attr('machine-verification');
    var _body = $('body');
    window.captcha = {};

    function get_img(_this) {
        $.ajax({
            url: _win.uri + '/action/captcha.php',
            data: {
                type: 'image',
                id: _this.attr('imagecaptcha-id'),
            },
        }).done(function (data) {
            _this.html('<img alt="点击刷新" src="' + data.img + '">');
        });
    }

    switch (mode) {
        case 'image':
            var _code = $('.imagecaptcha');
            _code.each(function () {
                get_img($(this));
            });

            _body.on('click', '.imagecaptcha', function () {
                get_img($(this));
            });

            break;

        case 'slider':
            tbquire(['slidercaptcha']);

            break;
        case 'tcaptcha':
            var $btn = $('#TencentCaptcha.zib-tcaptcha');
            var appid = _mode.attr('tcaptcha-id');
            if (!$btn.length) {
                _body.append('<div class="hide zib-tcaptcha" id="TencentCaptcha" data-appid="' + appid + '" data-cbfn="TCaptchaOK" data-biz-state=""></div>');
            }
            tbquire(['tcaptcha-sdk']);

            break;

        case 'geetest':
            GeetestOpenInit(false);
            break;
    }
}
CaptchaInit();

/*global SliderCaptchaModal, initGeetest4*/
/**
 * 打开验证码弹窗
 * @param {object} _this - 当前对象
 * @param {string} mode - 模式，可选值为'slider'、'geetest'、'tcaptcha'
 * @returns {number} - 返回1表示打开失败
 */
function CaptchaOpen(_this, mode) {
    switch (mode) {
        case 'slider':
            tbquire(['slidercaptcha'], function () {
                SliderCaptchaModal('show', _this);
            });
            break;
        case 'geetest':
            window.captcha._this = _this;
            GeetestOpenInit(true);
            break;
        case 'tcaptcha':
            TCaptchaOpen(_this);
            break;
    }
    return !1;
}

//腾讯验证码弹窗
function TCaptchaOpen(_this) {
    window.captcha._this = _this;
    tbquire(['tcaptcha-sdk'], function () {
        $('#TencentCaptcha.zib-tcaptcha').click();
    });
}

function GeetestOpenInit(open) {
    var _mode = $('[machine-verification]');
    if (window.GeetestCaptcha) {
        open && window.GeetestCaptcha.showCaptcha();
        return;
    }
    if (!_mode.length) {
        return;
    }
    tbquire(['https://static.geetest.com/v4/gt4.js'], function () {
        initGeetest4(
            {
                captchaId: _mode.attr('geetest-id'),
                product: 'bind',
            },
            function (captchaObj) {
                captchaObj
                    .onReady(function () {
                        window.GeetestCaptcha = captchaObj;
                        open && captchaObj.showCaptcha();
                    })
                    .onSuccess(function () {
                        var getValidate = captchaObj.getValidate();
                        window.captcha.captcha_output = getValidate.captcha_output;
                        window.captcha.gen_time = getValidate.gen_time;
                        window.captcha.lot_number = getValidate.lot_number;
                        window.captcha.ticket = getValidate.pass_token;
                        window.captcha._this.click();
                    });
            }
        );
    });
}

//腾讯验证码回调
function TCaptchaOK(res) {
    window.captcha.ticket = 0;
    window.captcha.randstr = 0;
    if (res.ret === 0) {
        window.captcha.ticket = res.ticket;
        window.captcha.randstr = res.randstr;
        window.captcha._this.click();
    }
}
