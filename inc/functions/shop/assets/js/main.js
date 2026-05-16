/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-08-11 16:08:49
 * @LastEditTime : 2026-04-11 22:45:31
 */

(function () {
    //评分组件
    $.fn.shopScore = function () {
        return this.each(function () {
            var $this = $(this);
            if ($this.attr('is-on')) {
                return;
            }
            var score_value = $this.attr('score-value') || 0;

            // 创建星星
            var $icon_box = $this.find('.score-icon');
            var $text_box = $this.find('.score-text');
            var max_score = 5;
            var star_html = '';
            var star_svg = '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-stars"></use></svg>';

            // 初始化星星
            for (var i = 1; i <= max_score; i++) {
                var active_class = i <= score_value ? 'active' : '';
                star_html += '<i class="icon-star ' + active_class + '" data-score="' + i + '">' + star_svg + '</i>';
            }

            $icon_box.html(star_html);
            // 初始化评分文字
            if (score_value > 0) {
                updateScoreText(score_value);
            }

            $this.attr('is-on', true);

            // 显示评分文字
            function updateScoreText(score) {
                var text = '';
                if (score <= 1) {
                    text = '非常差';
                } else if (score <= 2) {
                    text = '较差';
                } else if (score <= 3) {
                    text = '一般';
                } else if (score <= 4) {
                    text = '较好';
                } else {
                    text = '非常好';
                }
                $text_box.text(text).attr('data-score', score);
                $this.attr('score-value', score);
                $this.find('.score-input').val(score);
            }

            // 绑定点击事件
            $this.on('click', '.icon-star', function () {
                var score = $(this).data('score');

                // 更新星星状态
                $icon_box.find('.icon-star').each(function () {
                    var star_score = $(this).data('score');
                    if (star_score <= score) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                });

                // 更新评分文字
                updateScoreText(score);
            });

            // 鼠标悬停预览效果
            $this
                .on('mouseenter', '.icon-star', function () {
                    var hover_score = $(this).data('score');

                    $icon_box.find('.icon-star').each(function () {
                        var star_score = $(this).data('score');
                        if (star_score <= hover_score) {
                            $(this).addClass('hover');
                        } else {
                            $(this).removeClass('hover');
                        }
                    });
                })
                .on('mouseleave', function () {
                    $icon_box.find('.icon-star').removeClass('hover');
                });
        });
    };

    //计算从购物车按钮到加入购物车动画的距离，返回X。Y
    function getAddAnimateDistance(form, to) {
        var form_top = form.offset().top;
        var to_top = to.offset().top;
        var form_left = form.offset().left;
        var to_left = to.offset().left;
        var form_width = form.width();
        var to_width = to.width();
        var form_height = form.height();
        var to_height = to.height();

        return {
            x: to_left - form_left - form_width / 2 + to_width / 2,
            y: to_top - form_top - form_height / 2 + to_height / 2,
        };
    }

    $(document).on('auto_fun', function () {
        $('.shop-score-box').shopScore();
    });

    function priceRounding(num, is_int) {
        var n = Number(num);
        if (isNaN(n)) return '0';
        var str = n.toFixed(is_int ? 0 : 2).toString();
        // 修复300.00变成3的bug，正确去除多余的0和小数点
        if (str.indexOf('.') > -1) {
            // 只去除小数点后多余的0
            str = str.replace(/(\.\d*?[1-9])0+$/, '$1'); // 去除小数点后多余的0
            str = str.replace(/\.0+$/, ''); // 如果小数点后全是0，去掉小数点和0
        }
        return Number(str);
    }

    function getDecimalHtml(value) {
        var value_str = priceRounding(value).toString();
        var parts = value_str.split('.');
        var html = parts[0];
        if (parts[1] && value_str.includes('.')) {
            html += '<span class="decimal-part">.' + parts[1] + '</span>';
        }
        return html;
    }

    //拼接选项
    function optsKeySplicing(option_key, item_key) {
        return '|' + option_key + '_' + item_key;
    }

    //拼接选项
    function optsKeyToStr(option) {
        var str = '';
        option.forEach(function (item_key, option_key) {
            str += optsKeySplicing(option_key, item_key);
        });
        return str;
    }

    //VUE响应对象转普通对象
    function vueToObj(vue_data) {
        if (!vue_data) {
            return {};
        }
        return JSON.parse(JSON.stringify(vue_data));
    }

    //拆封成数组
    /* eslint-disable */
    function opts_key_split(option_key_str) {
        option_key_str = option_key_str.replace(/^|/, ''); //去除第一个|

        var option_key_arr = option_key_str.split('|');
        var option_key_arr_new = [];
        option_key_arr.forEach(function (item) {
            var item_arr = item.split('_');
            if (item_arr.length === 2) {
                option_key_arr_new[item_arr[0]] = item_arr[1];
            }
        });

        return option_key_arr_new;
    }

    function viewport() {
        var e = window,
            a = 'inner';
        if (!('innerWidth' in window)) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        return { width: e[a + 'Width'], height: e[a + 'Height'] };
    }

    function isMobile() {
        return viewport().width < 768;
    }

    if (isMobile()) {
        $('.product-cover-slider img').each(function () {
            var $this = $(this);
            var $clone = $this.clone().addClass('no-imgbox').css('transform', 'scaleY(-1) translateY(calc(-100% + 33px))');
            $this.before($clone);
        });
    }

    //执行登录按钮
    function loginBtnClick() {
        $('.signin-loader:eq(0)').click();
    }

    var $body = $('body');

    $.fn.createModal = function (_config) {
        var _config = _config || {};
        var id = _config.id || '';
        var content = _config.content || '';
        var title = _config.title || '';
        var modal_class = _config.modal_class ? ' ' + _config.modal_class : '';
        var modal_dialog_class = _config.dialog_class ? ' ' + _config.dialog_class : '';
        var title_class = _config.title_class ? ' ' + _config.title_class : '';
        var _wid = $(window).width();
        title = title ? '<div class="touch ' + title_class + '">' + title + '</div>' : '';

        var html = '<div class="modal flex jc fade' + (_wid < 769 ? ' bottom' : '') + modal_class + '" id="' + id + '"><div class="modal-dialog' + modal_dialog_class + '" role="document"><div class="modal-content"><button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="modal-body">' + title + content + '</div></div><div class="touch-close show-sm"></div></div></div>';

        var _modal = $(html);
        _modal.appendTo($(this));

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
        return _modal;
    };

    var VueTemplate = {
        this_str: '',
        data_prefix: '',
        modal_id: '',
        cart_modal_id: 'shop-product-cart-modal',
        remark_modal_id: 'shop-product-remark-modal',
        address_modal_id: 'shop-product-address-modal',
        mounted_attr: '',
        mounted: function (param) {
            this.mounted_attr = param ? ' @vue:mounted="' + param + '"' : '';
            return this;
        },

        prefix: function (data_prefix) {
            this.this_str = data_prefix;
            this.data_prefix = data_prefix ? data_prefix + '.' : '';
            return this;
        },
        ModalId: function (modal_id) {
            this.modal_id = modal_id;
            return this;
        },
        productInfoBox: function () {
            return `
                <div class="touch product-info-box flex jsb p-d-mb" ${this.mounted_attr}>
                    <div class="item-thumbnail product-graphic mr10 flex jc gradient-bg nowave" data-opacity="0.1"><img class="alone-imgbox-img" imgbox-no-cache="true" :src="${this.data_prefix}main_image_url" :alt="${this.data_prefix}title"></div>
                    <div class="product-info flex jsb xx flex1">
                        <div class="product-title text-ellipsis font-bold mr20">{{ ${this.data_prefix}title }}</div>
                        <div class="cart-col-price product-price-box flex jsb ac">
                            <div class="price-box" :class="${this.data_prefix}pay_modo == 'points' ? 'c-yellow' : 'c-red'">
                                <div class="mr6">
                                    <span class="pay-mark" v-html="${this.data_prefix}show_mark"></span>
                                    <b class="price-str" v-price="${this.data_prefix}prices.total_discount_price"></b>
                                </div>
                                <div class="badg badg-sm c-yellow" v-if="${this.data_prefix}prices.total_discount">省 {{ ${this.data_prefix}prices.total_discount }}</div>
                            </div>
                        </div>
                        <div v-discount-badge="${this.data_prefix}discount" data-hit-discount="${this.data_prefix}discount_hit" class="product-discount-box scroll-x mini-scrollbar" @click="discountModal(${this.data_prefix}discount)"></div>
                    </div>
                </div>
                `;
        },

        productOptsBox: function () {
            return `
                <div class="product-opts-box mini-scrollbar scroll-y max-vh5 mb10">
                    <div v-if="${this.data_prefix}product_options.length > 0">
                        <div class="p-d-mb" v-for="(opt,opt_index) in ${this.data_prefix}product_options" :key="'opt-' + opt_index">
                            <div class="product-opt-title-box flex ac jsb">
                                <div class="product-opt-title">{{ opt.name }}</div>
                            <div class="pointer muted-2-color ml10 em09" @click="switchOptView(opt)" v-if="optHasImg(opt)"><svg class="icon mr3" aria-hidden="true"><use xlink:href="#icon-img-lists"></use></svg>{{ opt.view_mode === \'img\' ? \'列表\' :  \'图片\'  }}</div>
                            </div>
                            <div class="product-opt-content" :class="(opt.view_mode === \'img\' ? \'mode-img\' : \'\' ) + \' count-\' + opt.opts.length">
                                <div v-for="(item,item_index) in opt.opts" class="opt-item badg hollow" :class="item_index == ${this.data_prefix}options_active[opt_index] ? \'active\' : \'\'" @click="modalOptChange(opt_index,item_index)" :key="\'opt-\' + opt_index + \'-\' + item_index">
                                    <span class="opt-item-image" v-if="item.image">
                                        <img :src="item.image" alt="">
                                        <div class="abs-center right-top pointer alone-imgbox-img flex jc" :data-src="item.image"><i class="fa fa-expand" aria-hidden="true"></i></div>
                                    </span>
                                    <span class="opt-item-name">{{ item.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="padding-h10 flex ac jsb border-top mt10">
                        <div class="product-opt-title">数量</div>
                        <div class="product-buy-num-box flex ac em09 mb6">
                            <div class="ml10 flex mr10">
                                <div class="stock-desc muted-color" v-effect="$el.innerHTML = stockAllText(${this.data_prefix}stock_all)"></div>
                                <div class="limit-buy-desc  c-yellow ml6" v-limit-buy="${this.data_prefix}limit_buy"></div>
                            </div>
                            <div v-spinner="${this.data_prefix}selected_count" @change="modalCountChange" min="1" :max="spinnerMax(${this.this_str})"></div>
                        </div>
                    </div>
                </div>
                `;
        },

        createModal: function (content) {
            var modal_html = `
                <div class="modal flex jc fade" :class="is_mobile ? 'bottom' : ''" id="${this.modal_id}" style="display: none" key="${this.modal_id}" ${this.mounted_attr}>
                    <div class="shop-modal modal-mini full-sm modal-dialog" role="document">
                        <div class="modal-content">
                            <button class="close abs-close" data-dismiss="modal">
                                <svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg>
                            </button>
                            <div class="modal-body">
                                ${content}
                            </div>
                        </div>
                        <div class="touch-close show-sm"></div>
                    </div>
                </div>
            `;

            $body.append(modal_html);
            var _modal = $('#' + this.modal_id);

            if ($(window).width() < 769) {
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

            return _modal;
        },

        cartModalbtn: function () {
            return `
                <botton class="but jb-yellow-2 padding-lg btn-block" @click.prevent="cartModalSubmit">{{${this.data_prefix}cart_submit_btn_text}}</botton>
            `;
        },
        //下订单
        orderModalBtn: function () {
            return `
                <botton class="but jb-red padding-lg btn-block" @click.prevent="orderModalSubmit">{{${this.data_prefix}order_submit_btn_text}}</botton>
            `;
        },

        createCartModal: function () {
            if (!this.modal_id) {
                this.ModalId(this.cart_modal_id);
            }

            var modal_body = this.productInfoBox() + this.productOptsBox() + this.cartModalbtn();

            return this.createModal(modal_body);
        },

        //创建商品选择确认模态框
        createProductModal: function () {
            if (!this.modal_id) {
                this.ModalId(this.cart_modal_id);
            }

            var service_title = `<div class="border-title touch flex jc" v-if="service.length > 0">
            <div class="c-green text-ellipsis em09 mr20 ml20">
                <span :class="item_index !== 0 ? \'ml10\' : \'\'" class="service-item" v-for="(item,item_index) in service" :key="\'service-\' + item_index"><svg class="mr3 opacity5" aria-hidden="true"><use xlink:href="#icon-check-circle"></use></svg>{{ item.name }}</span>
            </div>
        </div>
        `;

            var modal_body = service_title + this.productInfoBox() + this.productOptsBox() + '<div class="but-group shop-paybtn-group flex">' + this.cartModalbtn() + this.orderModalBtn() + '</div>';

            return this.createModal(modal_body);
        },

        //备注模态框
        remarkModalBox: function () {
            return `
                <div class="border-title touch"><div class="flex jc"><b>备注</b></div></div>
                <div class="remark-modal-body">
                    <div class="remark-modal-content mb20" style="min-height: 150px;">
                        <textarea class="form-control" style="min-height: 120px;" v-model.trim="${this.data_prefix}remark" placeholder="请输入备注" @keyup.ctrl.enter="remarkModalSubmit"></textarea>
                    </div>
                </div>
                <botton class="but jb-blue padding-lg btn-block" @click.prevent="remarkModalSubmit">确认</botton>
            `;
        },
        createRemarkModal: function () {
            if (!this.modal_id) {
                this.ModalId(this.remark_modal_id);
            }

            var modal_body = this.remarkModalBox();
            return this.createModal(modal_body);
        },

        createUserRequiredModal: function () {
            if (!this.modal_id) {
                this.ModalId(this.user_required_modal_id);
            }

            var modal_body = `
            <div class="border-title touch"><div class="flex jc"><b>请填写必要信息</b></div></div>
            <div class="user-required-modal-body mini-scrollbar scroll-y max-vh5" style="min-height: 160px;">
                <div class="mb20 padding-6" v-for="(user_required_item,index) in ${this.data_prefix}user_required" :key="'user_required_item-' + index">
                    <div class="em09 muted-2-color mb6">{{ user_required_item.name }}</div>
                    <input type="text" class="form-control" name="title" :tabindex="index + 1" v-model.trim="user_required_item.value">
                    <div class="px12 muted-3-color mt3" v-if="user_required_item.desc" v-html="user_required_item.desc"></div>
                    <div class="px12 c-red mt3" v-if="user_required_item.error">{{ user_required_item.error }}</div>
                </div>
            </div>
            <botton class="but jb-blue padding-lg btn-block" @click.prevent="userRequiredModalSubmit">确认</botton>
            `;

            return this.createModal(modal_body);
        },

        userAddressModalBox: function () {
            return `
            <div class="border-title touch"><div class="flex jc"><b>{{ is_edit ? '编辑收货地址' : '添加收货地址' }}</b></div></div>
                <div class="address-form">
                    <!-- 收货人姓名 -->
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-user"></i></div>
                            <input type="text" class="form-control" v-model="form.name" placeholder="收货人姓名" maxlength="20" />
                        </div>
                        <div class="help-block error-block px12 c-red" v-if="errors.name">{{ errors.name }}</div>
                    </div>

                    <!-- 手机号码 -->
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                            <input type="tel" class="form-control" v-model="form.phone" placeholder="手机号码" maxlength="30" />
                        </div>
                        <div class="help-block error-block px12 c-red" v-if="errors.phone">{{ errors.phone }}</div>
                    </div>
                    
                    <!-- 地区选择 -->
                    <div class="form-group">
                        <div class="region-select flex" v-if="$.isEmptyObject(regions)">
                            <div class="flex1 mr10">
                                <input type="text" class="form-control" v-model="form.province" placeholder="请输入省份" />
                            </div>
                            <div class="flex1 mr10">
                                <input type="text" class="form-control" v-model="form.city" placeholder="请输入城市"/>
                            </div>
                            <div class="flex1">
                                <input type="text" class="form-control" v-model="form.county" placeholder="请输入区县"/>
                            </div>
                        </div>
                        <div class="region-select flex" v-else>
                            <div class="form-select flex1">
                                <select class="form-control" v-model="form.province" @change="provinceChange">
                                    <option value="">选择省份</option>
                                    <option v-for="(province, index) in regions" :key="'province-' + index" :value="index">{{ index }}</option>
                                </select>
                            </div>
                            <div class="form-select flex1 ml10">
                                <select class="form-control" v-model="form.city" @change="cityChange" ref="regionCitySelect">
                                    <option value="">选择城市</option>
                                    <option v-for="(city, index) in cities" :key="'city-' + index" :value="index">{{ index }}</option>
                                </select>
                            </div>
                            <div class="form-select flex1 ml10" v-if="!$.isEmptyObject(counties)">
                                <select class="form-control" v-model="form.county" ref="regionCountySelect">
                                    <option value="">选择区县</option>
                                    <option v-for="(county, index) in counties" :key="'county-' + index" :value="county">{{ county }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="help-block error-block px12 c-red" v-if="errors.region">{{ errors.region }}</div>
                    </div>

                    <!-- 详细地址 -->
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-map-marker"></i></div>
                            <input type="text" class="form-control" v-model="form.address" placeholder="详细地址，如街道、门牌号等" maxlength="100" />
                        </div>
                        <div class="help-block error-block px12 c-red" v-if="errors.address">{{ errors.address }}</div>
                    </div>

                    <!-- 地址标签 -->
                    <div class="form-group">
                        <div class="address-tags">
                            <div class="em09 muted-2-color mb10">地址标签：</div>
                            <div class="tag-list">
                                <span class="tag-item mb6 mr6 badg p2-10 pointer" v-for="tag in tag_list" :key="tag" :class="form.tag === tag ? 'active' : ''" @click="form.tag = tag">{{ tag }}</span>
                                <span v-if="!showCustomInput" class="badg p2-10 pointer mb6" :class="form.tag === customTag ? 'active' : ''" @click="showCustomTagInput">{{ customTag || '自定义' }}</span>
                                <span v-else class="tag-item custom-tag">
                                    <input type="text" class="badg-form badg p2-10 mb6" v-model="customTag" @blur="setCustomTag" @keyup.enter="setCustomTag" ref="customTagInput" maxlength="5" placeholder="自定义标签 最多5个字" />
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 设为默认地址 -->
                    <div class="muted-box padding-10 mb10">
                        <label class="muted-color flex ac jsb" style="font-weight: normal; margin: 0">
                            <input class="hide" type="checkbox" v-model="form.is_default" />
                            设为默认收货地址
                            <div class="form-switch flex0"></div>
                        </label>
                    </div>

                </div>
            <div class="modal-buts but-average">
                <button type="button" class="but" data-dismiss="modal">取消</button>
                <button type="button" class="but c-blue" @click="editSaveAddress" ref="editSaveAddressBtn">保存</button>
            </div>
            `;
        },

        userManageAddressModalBox: function () {
            return `
<div class="border-title touch">
    <div class="flex jc"><b>收货地址</b></div>
</div>
<div class="user-address-list-modal">
    <div class="address-list mini-scrollbar scroll-y max-vh5 mb10">
        <!-- 地址列表 -->
        <div class="address-item muted-box padding-10 mb10 pointer" v-for="(item, index) in addresses" :key="'address-' + index" :class="selectedAddressId === item.id ? 'active' : ''" @click="selectAddress(item)">
            <div class="address-content">
                <div class="address-detail muted-color flex ac">
                    <div class="name-phone mb6">
                        <span class="name">{{ item.name }}</span>
                        <span class="phone ml10">{{ item.phone }}</span>
                        <span class="badg badg-sm c-blue" v-if="item.tag">{{ item.tag }}</span>
                    </div>
                </div>
                <div class="address-detail muted-2-color em09 mb6">{{ item.province }} {{ item.city }} {{ item.county }} {{ item.address }}</div>
            </div>
            <div class="address-actions flex ac jsb">
                <span class="default-badge badg badg-sm c-red" v-if="item.is_default">默认</span>
                <button class="default-badge but but-sm c-blue flex ac" v-else @click.prevent.stop="setDefaultAddress" :item-id="item.id">设为默认</button>
                <div class="action-btns px12">
                    <button class="but p2-10" @click.prevent.stop="editAddress(item)">编辑</button>
                    <button class="but c-red p2-10 ml6" @click.prevent.stop="deleteAddress(item.id)" v-if="!item.is_default">删除</button>
                </div>
            </div>
        </div>

        <!-- 空状态 -->
        <div class="address-empty flex jc mt20 mb20" v-if="!addresses.length">
            <div class="empty-icon c-yellow text-center">
                <i class="fa fa-map-marker  em3x mb20"></i>
                <div class="empty-text">您还没有添加收货地址</div>
                <button type="button" class="mt20 but hollow c-blue padding-lg" @click="addNewAddress">立即添加</button>
            </div>
        </div>
    </div>
    <div class="modal-buts but-average">
        <button type="button" class="but c-blue" @click="addNewAddress">添加新地址</button>
    </div>
</div>

            `;
        },

        //用户添加地址
        userAddAddressModal: function () {
            if (!this.modal_id) {
                this.ModalId('user-address-modal');
            }

            var modal_body = this.userAddressModalBox();
            return this.createModal(modal_body);
        },

        //用户管理地址
        userManageAddressModal: function () {
            if (!this.modal_id) {
                this.ModalId('user-address-modal');
            }

            var modal_body = this.userManageAddressModalBox();
            return this.createModal(modal_body);
        },
    };

    //模板库
    function setWinCartCount(count) {
        $('[cart-count]').text(count).attr('cart-count', count);
    }

    //跳转到结算页面，并传post数据
    function goConfirmPage(data, is_new_window = false) {
        // 创建一个表单元素
        var $form = $('<form>', {
            action: data.url || window.location.href,
            method: 'POST',
            style: 'display: none;',
            target: is_new_window ? '_blank' : '',
        });

        // 添加商品数据
        if (data.products && Object.keys(data.products).length > 0) {
            // 遍历商品数据并添加到表单
            $.each(data.products, function (product_id, options) {
                $.each(options, function (option_key, count) {
                    $form.append(
                        $('<input>', {
                            type: 'hidden',
                            name: 'products[' + product_id + '][' + option_key + ']',
                            value: count,
                        })
                    );
                });
            });

            if (data.is_cart) {
                $form.append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'is_cart',
                        value: 1,
                    })
                );
            }

            // 添加表单到body并提交
            $body.append($form);
            $form.submit();
        }
    }

    //通用组件
    function createVueComponents() {
        return {
            win_width: $(window).width(),
            is_mobile: isMobile(),
            //库存显示文案
            stockAllText: function (stock_all) {
                if (isNaN(stock_all)) {
                    return '';
                }

                if (stock_all < 0) {
                    return '';
                }

                if (stock_all === 0) {
                    return '缺货';
                }

                return '库存: ' + stock_all;
            },
            spinnerMax: function (opt_data) {
                if (typeof opt_data !== 'object') {
                    opt_data = this;
                }

                var max = ~~opt_data.stock_all;

                if (max === -1) {
                    if (opt_data.limit_buy && opt_data.limit_buy.is_limit) {
                        return ~~opt_data.limit_buy.limit;
                    }
                } else if (opt_data.limit_buy && opt_data.limit_buy.is_limit && ~~opt_data.limit_buy.limit !== -1) {
                    max = Math.min(max, ~~opt_data.limit_buy.limit);
                }

                return max;
            },
            switchOptView: function (opt) {
                opt.view_mode = opt.view_mode === 'img' ? 'list' : 'img';
            },
            optHasImg: function (opt) {
                //只要有一个选项有图片，就返回true
                return opt.opts && opt.opts.length > 0 && opt.opts.some((opt) => opt.image);
            },
            //同步单个商品库存
            syncItemStock: function (opt_data, product_data) {
                var _this = opt_data;
                if (!product_data) {
                    product_data = opt_data;
                }
                if (product_data.stock_type === 'opts') {
                    var options_active_str = optsKeyToStr(_this.options_active);
                    if (product_data.stock_opts[options_active_str]) {
                        _this.stock_all = Number(product_data.stock_opts[options_active_str]);
                    } else {
                        _this.stock_all = 0;
                    }
                } else {
                    _this.stock_all = Number(product_data.stock_all);
                }

                if (_this.stock_all === -1) {
                    //无限库存
                    return;
                }

                //已选择的数量大于库存，则更新选择数量为库存数量
                if (_this.selected_count > _this.stock_all) {
                    _this.selected_count = _this.stock_all ? _this.stock_all : 1;
                }

                if (_this.stock_all === 0) {
                    //没有库存
                }
            },
            //同步单个商品优惠价格
            syncItemDiscountPrice: function (opt_item_data, discount_data_dependency, product_data) {
                if (!product_data) {
                    product_data = opt_item_data;
                }
                if (!discount_data_dependency) {
                    discount_data_dependency = {};
                }

                opt_item_data.discount_hit = []; //重置优惠命中
                var is_points = product_data.pay_modo && product_data.pay_modo === 'points';
                var item_total_discount_price = priceRounding(opt_item_data.prices.total_price, is_points);
                var item_discount = product_data.discount;

                if (item_discount) {
                    /**
                     * 执行当前选项的优惠政策计算
                     * 与php函数计算同步
                     * 同步文件：inc/functions/shop/inc/order.php:210
                     */

                    //准备依赖数据
                    var discount_dependency_price__item = item_total_discount_price / opt_item_data['selected_count'];
                    var discount_dependency__product = discount_data_dependency['product_data'];
                    var discount_dependency__author = discount_data_dependency['author_data'];
                    var discount_dependency__total = discount_data_dependency['total_data'];
                    var discount_dependency__user_data = discount_data_dependency['user_data'];

                    $.each(item_discount, function (index, discount_item_args) {
                        discount_item_args = vueToObj(discount_item_args);
                        discount_item_args.usesed_count = 0;
                        discount_item_args.preferential_amount = 0;

                        //开始计算优惠政策及优惠价格
                        //is_valid判断
                        if (!discount_item_args['is_valid']) {
                            return true;
                        }

                        // 1.金额限制判断
                        var discount_scope = discount_item_args['discount_scope'];
                        if (discount_item_args['price_limit']) {
                            if (discount_scope === 'item') {
                                if (discount_dependency_price__item < discount_item_args['price_limit']) {
                                    return true;
                                }
                            }
                            if (discount_scope === 'product') {
                                if (discount_dependency__product['price'] < discount_item_args['price_limit']) {
                                    return true;
                                }
                            }
                            if (discount_scope === 'author') {
                                if (discount_dependency__author['price'] < discount_item_args['price_limit']) {
                                    return true;
                                }
                            }
                            if (discount_scope === 'order') {
                                if (discount_dependency__total['price'] < discount_item_args['price_limit']) {
                                    return true;
                                }
                            }
                        }

                        // 2.用户身份限制
                        if (discount_item_args['user_limit']) {
                            if (discount_item_args['user_limit'] === 'vip' && discount_dependency__user_data['vip_level'] < 1) {
                                return true;
                            }
                            if (discount_item_args['user_limit'] === 'vip_2' && discount_dependency__user_data['vip_level'] < 2) {
                                return true;
                            }
                            if (discount_item_args['user_limit'] === 'auth' && !discount_dependency__user_data['auth']) {
                                return true;
                            }
                        }

                        // 3. 判断结束，命中优惠，根据优惠类型计算价格

                        //3.1:立减优惠计算
                        if (discount_item_args.discount_type === 'reduction') {
                            var _reduction_amount = Number(discount_item_args['reduction_amount']);
                            var reduction_amount = 0;
                            discount_item_args.usesed_count = 1;
                            if (discount_scope === 'item') {
                                discount_item_args.usesed_count = opt_item_data['selected_count'];
                                reduction_amount = _reduction_amount * opt_item_data['selected_count'];
                            }
                            if (discount_scope === 'product') {
                                reduction_amount = _reduction_amount * (opt_item_data['selected_count'] / discount_dependency__product['count']);
                            }
                            if (discount_scope === 'author') {
                                reduction_amount = _reduction_amount * (opt_item_data['selected_count'] / discount_dependency__author['count']);
                            }
                            if (discount_scope === 'order') {
                                reduction_amount = _reduction_amount * (opt_item_data['selected_count'] / discount_dependency__total['count']);
                            }

                            if (reduction_amount >= item_total_discount_price) {
                                //优惠金额不能大于总金额，也就是说金额不能为负数
                                reduction_amount = item_total_discount_price;
                            }

                            discount_item_args.preferential_amount = reduction_amount; //优惠的金额
                            item_total_discount_price -= reduction_amount; //计算优惠后价格
                        }

                        //3.2：打折优惠计算
                        if (discount_item_args.discount_type === 'discount') {
                            var old_total_discount_price = item_total_discount_price; //原总价记录一下

                            var discount = discount_item_args.discount_amount / 10;
                            item_total_discount_price *= discount;

                            discount_item_args.usesed_count = 1;
                            discount_item_args.preferential_amount = old_total_discount_price - item_total_discount_price;
                        }

                        //3.2：赠品核算
                        if (discount_item_args.discount_type === 'gift') {
                            if (!discount_item_args.gift_data || discount_item_args.gift_data.length === 0) {
                                discount_item_args.gift_data = [];
                            }
                            discount_item_args.gift_data.push(discount_item_args.gift_config);
                        }

                        //判断完成
                        discount_item_args.preferential_amount = priceRounding(discount_item_args.preferential_amount);
                        opt_item_data.discount_hit.push(discount_item_args);
                    });
                }

                opt_item_data.prices.unit_discount_price = priceRounding(item_total_discount_price / opt_item_data.selected_count, is_points); //折扣价
                opt_item_data.prices.total_discount_price = priceRounding(item_total_discount_price, is_points); //折扣价
                opt_item_data.prices.total_discount = priceRounding(opt_item_data.prices.total_price - opt_item_data.prices.total_discount_price);
            },
            //同步单个商品总价
            syncItemPrice: function (opt_data, product_data) {
                if (!product_data) {
                    product_data = opt_data;
                }

                var opt_data_prices = opt_data.prices;
                var price = Number(product_data.prices.start_price); //基础价格
                opt_data_prices.start_price = price;
                var is_points = opt_data.pay_modo && opt_data.pay_modo === 'points';

                if (opt_data.options_active.length > 0 && product_data.product_options.length > 0) {
                    $.each(opt_data.options_active, function (active_key, active_val) {
                        if (product_data.product_options[active_key] && product_data.product_options[active_key].opts && product_data.product_options[active_key].opts[active_val]) {
                            price += Number(product_data.product_options[active_key].opts[active_val].price_change);
                        }
                    });
                }

                price = price < 0 ? 0 : price;
                //单价
                var unit_price = priceRounding(price, is_points); //单价
                opt_data_prices.unit_price = unit_price; //单价
                opt_data_prices.unit_discount_price = unit_price; //折扣价

                //总价
                var total_price = priceRounding(price * opt_data.selected_count, is_points); //总价
                opt_data_prices.total_price = total_price; //总价
                opt_data_prices.total_discount_price = total_price; //折扣价
            },

            newModal: function (content, title) {
                title = title || '';
                if (title) {
                    title = '<div class="border-title touch"><div class="flex jc"><b>' + title + '</b></div></div>';
                }

                return refresh_modal({
                    class: 'shop-modal modal-mini full-sm',
                    mobile_from_bottom: true,
                    content: '<button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="">' + title + content + '</div>',
                });
            },
            paramsModal: function (params) {
                if (!params.length) {
                    return;
                }

                var html = '';
                $.each(params, function (index, item) {
                    html += '<div class="mb10 flex" style="min-width:80%;"><div class="author-set-left muted-2-color" style="min-width: 80px;">' + item.name + '</div><div class="author-set-right mt6">' + item.value + '</div></div>';
                });

                return this.newModal('<div class="mini-scrollbar scroll-y max-vh5" style="min-height: 20vh;">' + html + '</div>', '商品参数');
            },

            serviceModal: function (service) {
                var html = '';
                $.each(service, function (index, item) {
                    var image = item.image ? '<div class="badg em14 cir"><img src="' + item.image + '" alt="' + item.name + '"></div>' : '<div class="badg cir em14"><i class="fa fa-heart-o"></i></div>';
                    html += '<div class="mt10 mb20 flex">' + image + '<div class="ml10">' + item.name + '<div class="muted-2-color em09">' + item.desc + '</div></div></div>';
                });

                return this.newModal('<div class="mini-scrollbar scroll-y max-vh5" style="min-height: 20vh;">' + html + '</div>', '商品服务');
            },
            discountHitModal: function (item_data, discount_data, product_data) {
                var item_data_html = '';
                var total_discount_amount = 0;

                if ($.isEmptyObject(item_data)) {
                    return;
                }

                $.each(item_data, function (index, item) {
                    if ($.isEmptyObject(item.discount_hit)) {
                        return;
                    }

                    var thumbnail = item.thumbnail || product_data[item.product_id].thumbnail;
                    var title = item.title || product_data[item.product_id].title;
                    var options_active_name = item.options_active_name ? '<div class="px12 muted-2-color text-ellipsis">' + item.options_active_name + '</div>' : '';

                    item_data_html += '<div class="dis-hit-item muted-box mb10">';
                    item_data_html += `<div class="flex ac border-bottom" style="padding-bottom: 10px;">
                                <div class="badg cir">
                                ${thumbnail}
                                </div>
                                <div class="ml10 flex1">
                                    <div class="title text-ellipsis">${title}</div>
                                    ${options_active_name}
                                </div>
                                <div class="muted-2-color">x<span class="em12"> ${item.selected_count || item.count || 1}</span></div>
                            </div>`;

                    item_data_html += '<div class="">';
                    $.each(item.discount_hit, function (index, discount_item) {
                        var discount_name = discount_item.name || discount_data[discount_item.id].name;
                        var discount_smail_badge = discount_item.small_badge || discount_data[discount_item.id].small_badge;
                        var discount_usesed_count = discount_item.usesed_count && discount_item.usesed_count > 1 ? 'x' + discount_item.usesed_count : '';
                        if (discount_name === discount_smail_badge) {
                            discount_name = '';
                        }

                        discount_smail_badge = discount_smail_badge ? '<span class="badg badg-sm c-yellow mr3">' + discount_smail_badge + discount_usesed_count + '</span>' : '';

                        if (discount_item.preferential_amount) {
                            var discount_amount = priceRounding(discount_item.preferential_amount);
                            total_discount_amount += discount_amount;
                            item_data_html += `
                            <div class="mt10 flex">
                                <div class="text-ellipsis flex1 mr20 muted-2-color">${discount_smail_badge + discount_name}</div>
                                <div class="desc c-red">-${discount_amount}</div>
                            </div>
                            `;
                        }
                    });

                    item_data_html += '</div></div>';
                });

                total_discount_amount = priceRounding(total_discount_amount);
                var html = `
                ${item_data_html}
                <div class="flex muted-box">
                    <div class="text-ellipsis flex1 mr20">共计优惠</div>
                    <div class="desc c-red">-${total_discount_amount}</div>
                </div>
                `;

                return this.newModal('<div class="mini-scrollbar scroll-y max-vh5">' + html + '</div>', '优惠详情');
            },
            giftDesc: function (gift_data, discount_data) {
                if ($.isEmptyObject(gift_data)) {
                    return;
                }
                var text = [];
                $.each(gift_data, function (index, item) {
                    switch (item.gift_type) {
                        case 'vip_1':
                            text.push(item.vip_1_name);
                            break;
                        case 'vip_2':
                            text.push(item.vip_2_name);
                            break;
                        case 'auth':
                            text.push('认证资格');
                            break;
                        case 'level_integral':
                            text.push('经验值');
                            break;
                        case 'points':
                            text.push('积分');
                            break;
                        case 'product':
                            text.push('商品');
                            break;
                        case 'other':
                            text.push(item.other_info.name);
                            break;
                    }
                });

                return text.join('、');
            },
            giftModal: function (gift_data) {
                var gift_cards_html = '';
                $.each(gift_data, function (index, item) {
                    switch (item.gift_type) {
                        case 'vip_1':
                            var time_text = item.vip_time === 'Permanent' ? '永久' : item.vip_time + '天';
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">' + item.vip_1_name + '</div><div class="muted-2-color"> ' + time_text + '</div></div>';
                            break;
                        case 'vip_2':
                            var time_text = item.vip_time === 'Permanent' ? '永久' : item.vip_time + '天';
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">' + item.vip_2_name + '</div><div class="muted-2-color"> ' + time_text + '</div></div>';
                            break;
                        case 'auth':
                            var desc = '<div class=""> ' + item.auth_info.name + '</div>';
                            desc += item.auth_info.desc ? '<div class="px12"> ' + item.auth_info.desc + '</div>' : '';
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">认证资格</div><div class="muted-3-color"> ' + desc + '</div></div>';
                            break;
                        case 'level_integral':
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">经验值</div><div class="muted-2-color"> ' + item.level_integral + '</div></div>';
                            break;
                        case 'points':
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">积分</div><div class="muted-2-color"> ' + item.points + '</div></div>';
                            break;
                        case 'product':
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">商品</div><div class="muted-2-color"> ' + item.product_id + '</div></div>';
                            break;
                        case 'other':
                            gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">' + item.other_info.name + '</div><div class="muted-2-color"> ' + item.other_info.desc + '</div></div>';
                            break;
                    }
                });

                var gift_cards = `
                    <div class="gift-card muted-box mb10 relative">
                            <div class="gift-card-item">
                                ${gift_cards_html}
                            </div>
                    </div>
                    `;

                var discount_data_html = '<div class="mini-scrollbar scroll-y max-vh5">' + gift_cards + '</div>';

                return this.newModal(discount_data_html, '赠品详情');
            },
            discountModal: function (discount_data, hit_data, valid_show) {
                discount_data = discount_data;
                hit_data = hit_data;

                var _discount_data = [];
                if (!$.isEmptyObject(hit_data)) {
                    $.each(hit_data, function (index, item) {
                        _discount_data.push(discount_data[item.id]);
                    });
                } else {
                    _discount_data = discount_data;
                }

                if ($.isEmptyObject(_discount_data)) {
                    return;
                }

                var discount_cards = '';
                var gift_cards = '';
                $.each(_discount_data, function (index, item) {
                    if (!valid_show && !item.is_valid) {
                        return;
                    }

                    var discount_type = item.discount_type;
                    var left_main = '';
                    switch (discount_type) {
                        case 'reduction':
                            left_main = '<div class="discount-amount"><div class="price-text">' + item.reduction_amount + '</div><div class="price-type px12">立减</div></div>';
                            break;
                        case 'discount':
                            left_main = '<div class="discount-amount"><span class="price-text em14">' + getDecimalHtml(item.discount_amount) + '</span><span class="price-type px12 ml6">折</span></div>';
                            break;
                        case 'gift':
                            break;
                    }

                    //标题和简介
                    var right_main_h = '<div class="title">' + item.name + '</div>';
                    right_main_h += item.desc ? '<div class="desc opacity8 px12 mt3 text-ellipsis">' + item.desc + '</div>' : '';

                    //金额限制
                    var limit = [];
                    if (Number(item.price_limit) > 0) {
                        var discount_scope = item['discount_scope'];
                        var _text = '';
                        if (discount_scope === 'item') {
                            _text = '单价';
                        } else if (discount_scope === 'product') {
                            _text = '商品';
                        } else if (discount_scope === 'author') {
                            _text = '店铺';
                        } else if (discount_scope === 'order') {
                            _text = '跨店';
                        }

                        limit.push('<span class="badg badg-sm">' + _text + '满' + item.price_limit + '可用</span>');
                    }

                    //身份限制
                    if (item.user_limit) {
                        switch (item.user_limit) {
                            case 'vip':
                                limit.push('<span class="badg badg-sm">VIP可用</span>');
                                break;
                            case 'vip_2':
                                limit.push('<span class="badg badg-sm">VIP2可用</span>');
                                break;
                            case 'auth':
                                limit.push('<span class="badg badg-sm">认证用户可用</span>');
                                break;
                        }
                    }

                    right_main_h += limit ? '<div class="limit-info opacity8 px12 mt3">' + limit.join(' & ') + '</div>' : '';

                    //时间限制
                    if (item.time_limit && item.time_limit_config) {
                        if (item.time_limit_config.show_html) {
                            right_main_h += item.time_limit_config.show_html;
                        } else {
                            if (item.time_limit_config.countdown) {
                                right_main_h += '<div class="flex ac px12 mt3"><span class="mr3 opacity8 px12">活动仅剩</span><span class="badg badg-sm c-red" int-second="auto" data-countdown="' + item.time_limit_config.end + '" data-over-text="已结束">00分00秒</span></div>';
                            } else {
                                var _start = item.time_limit_config.start ? item.time_limit_config.start.replace(' 00:00:00', '') : '';
                                var _end = item.time_limit_config.end ? item.time_limit_config.end.replace(' 23:59:59', '') : '';

                                var start_h = _start ? _start + (_end ? ' - ' : '开始') : '';
                                var end_h = _end ? _end + (_start ? '' : '结束') : '';

                                right_main_h += '<div class="badg badg-sm mt3">' + start_h + end_h + '</div>';
                            }
                        }
                    }

                    if (item.discount_type === 'gift') {
                        var gift_cards_html = '';
                        $.each(item.gift_config, function (index, item) {
                            switch (item.gift_type) {
                                case 'vip_1':
                                    var time_text = item.vip_time === 'Permanent' ? '永久' : item.vip_time + '天';
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">' + item.vip_1_name + '</div><div class="muted-2-color"> ' + time_text + '</div></div>';
                                    break;
                                case 'vip_2':
                                    var time_text = item.vip_time === 'Permanent' ? '永久' : item.vip_time + '天';
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">' + item.vip_2_name + '</div><div class="muted-2-color"> ' + time_text + '</div></div>';
                                    break;
                                case 'auth':
                                    var desc = '<div class=""> ' + item.auth_info.name + '</div>';
                                    desc += item.auth_info.desc ? '<div class="px12"> ' + item.auth_info.desc + '</div>' : '';
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">认证资格</div><div class="muted-3-color"> ' + desc + '</div></div>';
                                    break;
                                case 'level_integral':
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">经验值</div><div class="muted-2-color"> ' + item.level_integral + '</div></div>';
                                    break;
                                case 'points':
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">积分</div><div class="muted-2-color"> ' + item.points + '</div></div>';
                                    break;
                                case 'product':
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">商品</div><div class="muted-2-color"> ' + item.product_id + '</div></div>';
                                    break;
                                case 'other':
                                    gift_cards_html += '<div class="gift-card-item flex mt6"><div class="gift-name">' + item.other_info.name + '</div><div class="muted-2-color"> ' + item.other_info.desc + '</div></div>';
                                    break;
                            }
                        });

                        gift_cards += `
                        <div class="gift-card muted-box mb10 relative">
                            <div class="small-badge">${item.small_badge}</div>
                            <a class="flex" href="${item.link}" target="_blank">
                                <div class="gift-card-info flex1">
                                    ${right_main_h}
                                </div>
                                <i class="fa fa-angle-right em12 ml10 mt10 opacity8"></i>
                            </a>
                                <div class="gift-card-item">
                                    ${gift_cards_html}
                                </div>
                        </div>
                        `;
                    } else {
                        discount_cards += `
                        <a class="discount-card-link" href="${item.link}" target="_blank">
                        <div class="discount-card muted-box mb10">
                            <div class="small-badge">${item.small_badge}</div>
                            <div class="flex ac">
                                <div class="discount-card-left">
                                    ${left_main}
                                </div>
                                <div class="discount-card-right flex1">
                                    ${right_main_h}
                                </div>
                                <i class="fa fa-angle-right em12 ml6 opacity8"></i>
                            </div>
                        </div>
                        </a>
                        `;
                    }
                });

                discount_cards = discount_cards ? '<div class="discount-card-list">' + discount_cards + '</div>' : '';
                gift_cards = gift_cards ? '<div class="discount-card-gift">' + (discount_cards ? '<div class="title-theme mb10 mt20">赠品</div>' : '') + gift_cards + '</div>' : '';

                var discount_data_html = '<div class="mini-scrollbar scroll-y max-vh5">' + discount_cards + gift_cards + '</div>';

                return this.newModal(discount_data_html, '优惠信息');
            },

            //用户添加或管理地址
        };
    }

    //------------------------------------------订单确认------------------------------------------
    //通过网络获取html代码，并插入到页面中
    function VShopConfirmModal(data) {
        var self = this;
        self.drawer_id = 'shop-confirm-modal';

        // 通过网络获取html代码
        self.ajaxget = function (data) {
            $('body').loading('show');
            data.action = 'shop_confirm_modal';
            var _html = '';
            var local_storage_key = 'confirm_modal_html_' + _win.ver;
            var $modal = $('#' + self.drawer_id);
            if ($modal.length) {
                data.no_html = true;
            } else {
                var localData = localStorage.getItem(local_storage_key);
                if (localData) {
                    data.no_html = true;
                    _html = localData;
                }
            }

            $.ajax({
                url: _win.ajax_url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (n) {
                    var ys = n.ys ? n.ys : n.error ? 'danger' : '';
                    if (n.msg) {
                        notyf(n.msg, ys);
                    }

                    if (n.html) {
                        localStorage.setItem(local_storage_key, n.html);
                    }

                    if (n.vue_data) {
                        // 构建一个模态框
                        self.confirmModal(n.html || _html, n.vue_data);
                    }
                    $('body').loading('hide');
                },
                error: function () {
                    $('body').loading('hide');
                },
            });
        };

        // 构建模态框
        self.confirmModal = function (html, vue_data) {
            var id = self.drawer_id;
            var $modal = $('#' + id);
            if (!$modal.length) {
                $body.createModal({
                    modal_class: 'shop-confirm-modal',
                    dialog_class: 'full-sm',
                    title_class: 'mb10 flex jc font-bold',
                    mobile_from_bottom: true,
                    content: '<div class="shop-modal-body" @vue:mounted="mounted">' + html + '</div>',
                    id: id,
                    title: '确认订单',
                });

                $modal = $('#' + id);
                VShopConfirm('#' + id, vue_data);
            } else {
                window.VueShopConfirmData = $.extend(window.VueShopConfirmData, vue_data);
                window.VueShopConfirmData.order_data = [];
                window.VueShopConfirmData.payment_data = [];

                // 滚动到顶部
                window.VueShopConfirmData.$nextTick(function () {
                    setTimeout(function () {
                        $modal.find('.scroll-y').scrollTop(0);
                    }, 100);
                });
            }

            $modal.modal('show');
        };

        // 初始化
        self.init = function () {
            self.ajaxget(data);
            return {
                drawer_id: self.drawer_id,
                $drawer: $('#' + self.drawer_id),
                ajaxget: self.ajaxget,
                createApp: self.createApp,
            };
        };

        return self.init();
    }

    //------------------------------------------地址管理------------------------------------------
    //获取全国地址数据
    function getChinaRegions() {
        function convertRegionsData(data) {
            const provinces = {};

            // 遍历原始数据进行分类
            $.each(data, function (code, name) {
                if (code.endsWith('0000')) {
                    // 省级
                    const provinceName = name;
                    provinces[provinceName] = {};
                } else if (code.endsWith('00') && !code.endsWith('0000')) {
                    // 市级
                    const provinceName = data[code.substring(0, 2) + '0000'];
                    const cityName = name;

                    if (provinces[provinceName]) {
                        provinces[provinceName][cityName] = [];
                    }
                } else {
                    // 区县级
                    const provinceName = data[code.substring(0, 2) + '0000'];
                    const cityName = data[code.substring(0, 4) + '00'];
                    const districtName = name;

                    if (provinceName && !provinces[provinceName]) {
                        provinces[provinceName] = {};
                    }

                    if (cityName && !provinces[provinceName][cityName]) {
                        provinces[provinceName][cityName] = [];
                    }

                    if (!cityName) {
                        provinces[provinceName][districtName] = [];
                    } else {
                        provinces[provinceName][cityName].push(districtName);
                    }
                }
            });

            return provinces;
        }

        // 先尝试从本地存储获取
        var local_storage_key = 'china_regions_' + _win.ver;
        var localData = localStorage.getItem(local_storage_key);
        if (localData) {
            try {
                return JSON.parse(localData);
            } catch (e) {
                // 如果解析失败，从远程获取
            }
        }

        // 本地没有数据或解析失败，从远程获取
        var regions = [];
        $.ajax({
            url: _win.uri + '/inc/functions/shop/assets/js/china-regions.json',
            type: 'GET',
            dataType: 'json',
            async: false,
            success: function (data) {
                var newData = convertRegionsData(data);
                // 保存到本地存储
                localStorage.setItem(local_storage_key, JSON.stringify(newData));
                regions = newData;
            },
            error: function (xhr, status, error) {
                console.error('获取地址数据失败:', error);
            },
        });

        return regions;
    }

    //地址管理VUE
    function VShopAddress() {
        if (window.VueShopAddressData) {
            return window.VueShopAddressData;
        }
        // 获取全国地址数据
        var chinaRegions = getChinaRegions();

        // 配置对象
        var config = {};
        var manage_modal_id = 'user-manage-address-modal';
        var add_modal_id = 'user-address-edit-modal';

        // 先创建两个模态框
        var $manage_modal = VueTemplate.ModalId(manage_modal_id).mounted('AddressesMounted').userManageAddressModal();
        var $add_modal = VueTemplate.ModalId(add_modal_id).mounted(false).userAddAddressModal();

        // 创建Vue应用
        var app_data = $.extend(
            {
                is_mobile: isMobile(),
                addresses: [],
                selectedAddressId: 0,
                is_edit: false,
                form: {
                    id: 0,
                    name: '',
                    phone: '',
                    province: '',
                    city: '',
                    county: '',
                    address: '',
                    tag: '家',
                    is_default: false,
                },
                errors: {},
                regions: chinaRegions,
                cities: [],
                counties: [],
                customTag: '',
                showCustomInput: false,
                submitting: false,
                tag_list: ['家', '公司', '学校'],

                // 方法
                AddressesMounted: function () {
                    //加载地址数据
                    //this.loadAddresses();
                },

                // 模态框操作方法
                addModal: function (is_show) {
                    $add_modal.modal(is_show ? 'show' : 'hide');
                },

                manageModal: function (is_show) {
                    $manage_modal.modal(is_show ? 'show' : 'hide');
                },
                // 加载地址列表
                loadAddresses() {
                    const _this = this;
                    $.ajax({
                        type: 'POST',
                        url: _win.ajax_url,
                        data: {
                            action: 'shop_get_user_addresses',
                        },
                        success(response) {
                            if (!response.error && response.data) {
                                _this.updateAddresses(response.data);
                            }
                        },
                    });
                },
                updateAddresses(addresses) {
                    var _this = this;
                    _this.addresses = addresses;
                    if (!_this.selectedAddressId) {
                        // 如果有默认地址，选中它
                        const defaultAddress = _this.addresses.find((addr) => addr.is_default);
                        if (defaultAddress) {
                            _this.selectedAddressId = defaultAddress.id;
                        } else if (_this.addresses.length > 0) {
                            // 如果没有默认地址，则选中第一个地址
                            _this.selectedAddressId = _this.addresses[0].id;
                        }
                    }

                    //回调函数
                    _this.updateAddressesCallback(addresses);
                },
                getDefaultAddress() {
                    // 查找默认地址
                    const defaultAddr = this.addresses.find((addr) => addr.is_default);
                    // 如果没有默认地址，则返回第一个地址
                    return defaultAddr || (this.addresses.length > 0 ? this.addresses[0] : []);
                },
                updateAddressesCallback: function (address) {
                    //回调函数
                },
                setUpdateAddressesCallback: function (callback) {
                    //设置回调函数
                    this.updateAddressesCallback = callback;
                },
                // 选择地址
                selectAddress(address) {
                    this.selectedAddressId = address.id;
                    //传入地址id

                    this.manageModal(false);
                    this.selectAddressCallback(address);
                },
                selectAddressCallback: function (address) {
                    //回调函数
                },
                setSelectAddressCallback: function (callback) {
                    //设置回调函数
                    this.selectAddressCallback = callback;
                },
                // 添加新地址
                addNewAddress() {
                    this.resetForm();
                    this.is_edit = false;
                    this.manageModal(false);
                    $add_modal.modal('show');
                },
                // 编辑地址
                editAddress(address) {
                    this.resetForm();
                    this.is_edit = true;

                    // 填充表单
                    this.form = { ...address };
                    if (this.form.tag && !this.tag_list.includes(this.form.tag)) {
                        this.customTag = this.form.tag;
                    }

                    // 加载城市和区县数据
                    this.provinceChange(true); // 省份变化
                    this.cityChange(true); // 城市变化

                    this.manageModal(false); //关闭地址管理模态框
                    this.addModal(true); //显示添加地址模态框
                },
                // 删除地址
                deleteAddress(address_id) {
                    if (!confirm('确定要删除这个地址吗？')) {
                        return;
                    }

                    const _this = this;
                    $.ajax({
                        type: 'POST',
                        url: _win.ajax_url,
                        data: {
                            action: 'shop_delete_user_address',
                            address_id: address_id,
                        },
                        success(response) {
                            if (!response.error) {
                                notyf('删除成功', 'success');
                                //移出列表
                                var new_addresses = _this.addresses.filter((addr) => addr.id !== address_id);
                                _this.updateAddresses(new_addresses);
                            } else {
                                notyf(response.msg || '删除失败', 'danger');
                            }
                        },
                        error() {
                            notyf('网络错误，请稍后重试', 'danger');
                        },
                    });
                },

                // 保存地址
                editSaveAddress(e) {
                    if (!this.validateForm()) {
                        return;
                    }

                    this.saveAddress(this.form, e);
                },
                // 保存新地址
                saveAddress(address, e) {
                    const _this = this;
                    var save_data = {
                        action: 'shop_save_user_address',
                        address: address,
                    };

                    var $target = e ? $(e.target) : $('<div></div>');

                    $target.zib_ajax(save_data, function (n) {
                        if (n.data) {
                            _this.updateAddresses(n.data);
                        }
                        _this.addModal(false);
                        setTimeout(() => {
                            _this.manageModal(true);
                        }, 50);
                    });
                },

                // 设置默认地址
                setDefaultAddress(e) {
                    var $target = $(e.target);
                    var item_id = $target.attr('item-id');

                    if (!item_id) {
                        return;
                    }

                    var address = vueToObj(this.addresses.find((addr) => addr.id === item_id));
                    if (!address) {
                        return;
                    }
                    address.is_default = true;
                    this.saveAddress(address, e);
                },

                // 验证表单
                validateForm() {
                    this.errors = {};

                    if (!this.form.name) {
                        this.errors.name = '请输入收货人姓名';
                    }

                    if (!this.form.phone) {
                        this.errors.phone = '请输入手机号码';
                    } else if (!/^(?:\d{5,}|\+\d{6,}|[\+]?\d{2,}[\-\s]\d{2,}[0-9|\-|\s]{0,15})$/.test(this.form.phone)) {
                        this.errors.phone = '请输入正确的手机号码';
                    }

                    if (!this.form.province || !this.form.city || (!this.form.county && !$.isEmptyObject(this.counties))) {
                        this.errors.region = '地区信息不完整';
                    }

                    if (!this.form.address) {
                        this.errors.address = '请输入详细地址';
                    }

                    return Object.keys(this.errors).length === 0;
                },

                // 重置表单
                resetForm() {
                    this.form = {
                        id: 0,
                        name: '',
                        phone: '',
                        province: '',
                        city: '',
                        county: '',
                        address: '',
                        tag: this.tag_list[0],
                        is_default: false,
                    };
                    this.errors = {};
                    this.cities = [];
                    this.counties = [];
                    this.customTag = '';
                    this.showCustomInput = false;
                },

                // 省份变化时更新城市列表
                provinceChange(keepSelected) {
                    this.cities = {};
                    if (keepSelected !== true) {
                        this.counties = [];
                        this.form.city = ''; // 清空城市
                        this.form.county = ''; // 清空区县
                    }

                    const province = vueToObj(this.regions[this.form.province]);
                    if (province) {
                        this.cities = province;
                    }

                    if (this.form.city) {
                        this.$nextTick(() => {
                            $(this.$refs.regionCitySelect).val(this.form.city);
                        });
                    }
                },

                // 城市变化时更新区县列表
                cityChange(keepSelected) {
                    this.counties = [];
                    if (keepSelected !== true) {
                        this.form.county = '';
                    }

                    const province = this.cities;
                    if (!province) return;
                    const counties = vueToObj(province[this.form.city]);
                    if (counties) {
                        this.counties = counties;
                    }

                    if (this.form.county) {
                        this.$nextTick(() => {
                            $(this.$refs.regionCountySelect).val(this.form.county);
                        });
                    }
                },
                // 显示自定义标签输入框
                showCustomTagInput() {
                    this.showCustomInput = true;
                    this.$nextTick(() => {
                        this.$refs.customTagInput.focus();
                    });
                },

                // 设置自定义标签
                setCustomTag() {
                    if (this.customTag.trim()) {
                        this.form.tag = this.customTag.trim();
                    }
                    this.showCustomInput = false;
                },
            },
            config
        );

        // 挂载Vue应用
        PetiteVueCreateApp(app_data, ['#' + manage_modal_id, '#' + add_modal_id]);

        window.VueShopAddressData = app_data;
        return app_data;
    }

    /*************** 修改地址 ****************/
    function VShopModifyAddress(selector) {
        var config = $(selector).attr('v-config');
        config = $.parseJSON(config);
        $(selector).removeAttr('v-config');

        var app_data = $.extend(
            createVueComponents(),
            {
                address_data: null,
                new_address: {},
                mounted: function () {
                    var _this = this;
                    _this.address_data = VShopAddress();

                    _this.address_data.setSelectAddressCallback(function (address) {
                        _this.new_address = vueToObj(address);
                    });

                    _this.address_data.setUpdateAddressesCallback(function (address) {
                        if (!_this.new_address.id) {
                            //    _this.new_address = _this.address_data.getDefaultAddress();
                        } else {
                            var address_data = address.find((addr) => addr.id === _this.new_address.id);
                            address_data && (_this.new_address = address_data);
                        }
                    });

                    //更新管理地址数据
                    _this.address_data.updateAddresses(vueToObj(_this.address_lists_data));
                },
                showAddressModal: function () {
                    var _this = this;
                    _this.address_data.selectedAddressId = _this.new_address.id || 0;
                    _this.address_data.manageModal(true);
                },
            },
            config
        );

        return PetiteVueCreateApp(app_data, [selector]);
    }

    //订单确认
    function VShopConfirm(selector, config_data) {
        if (!config_data) {
            var config = $(selector).attr('v-config');
            config = $.parseJSON(config);
            $(selector).removeAttr('v-config');
        } else {
            var config = config_data;
        }

        config.remark_modal_data = {
            remark: '',
        };
        var remark_modal_id = 'remark_modal_data';
        config.$remark_modal = VueTemplate.ModalId(remark_modal_id).prefix('remark_modal_data').createRemarkModal();

        config.user_required_modal_data = [];
        var user_required_modal_id = 'user_required_modal_data';
        config.$user_required_modal = VueTemplate.ModalId(user_required_modal_id).prefix('user_required_modal_data').createUserRequiredModal();

        config.payment_data = [];
        config.order_data = [];
        var app_data = $.extend(
            createVueComponents(),
            {
                address_data: null,
                mounted: function () {
                    var _this = this;
                    if (!_this.address_data) {
                        _this.address_data = VShopAddress();
                    }

                    _this.address_data.selectAddressCallback = function (address) {
                        _this.user_data.address_data = address;
                    };

                    _this.address_data.updateAddressesCallback = function (address) {
                        if (!_this.user_data.address_data.id) {
                            _this.user_data.address_data = _this.address_data.getDefaultAddress();
                        } else {
                            var address_data = address.find((addr) => addr.id === _this.user_data.address_data.id);
                            _this.user_data.address_data = address_data || _this.address_data.getDefaultAddress();
                        }
                    };

                    //更新管理地址数据
                    _this.address_data.updateAddresses(vueToObj(this.user_data.address_lists_data));
                },
                showRemarkModal: function (opt_data) {
                    this.remark_modal_data.remark = opt_data.remark || '';
                    this.remark_modal_data.opt_data = opt_data;

                    this.$remark_modal.modal('show');
                },
                showUserRequiredModal: function (opt_data) {
                    this.user_required_modal_data.user_required = vueToObj(opt_data.user_required);
                    this.user_required_modal_data.opt_data = opt_data;
                    this.$user_required_modal.modal('show');
                },
                userRequiredValue: function (user_required_name, opt_data) {
                    if (!opt_data.user_required.length) {
                        return false;
                    }

                    return opt_data.user_required.find((item) => item.name === user_required_name).value || '';
                },
                userRequiredModalSubmit: function () {
                    var user_required = this.user_required_modal_data.user_required;
                    var error = [];
                    $.each(user_required, function (index, item) {
                        item.value = $('<div>' + item.value + '</div>').text();
                        if (!item.value) {
                            item.error = '请输入' + item.name;
                            error.push(item.name);
                        } else {
                            item.error = null;
                        }
                    });

                    if (error.length > 0) {
                        return notyf('请输入' + error.join(','), 'warning');
                    }
                    this.$user_required_modal.modal('hide');
                    this.user_required_modal_data.opt_data.user_required = user_required;

                    setTimeout(() => {
                        this.user_required_modal_data.user_required = null;
                        this.user_required_modal_data.opt_data = null;
                    }, 300);
                },
                remarkModalSubmit: function () {
                    //转为纯文字，去除html标签
                    var _remark_data = $('<div>' + this.remark_modal_data.remark + '</div>').text();

                    this.remark_modal_data.opt_data.remark = _remark_data;
                    this.remark_modal_data.remark = '';
                    this.remark_modal_data.opt_data = null;
                    this.$remark_modal.modal('hide');
                },
                showAddressModal: function () {
                    var _this = this;
                    if (_this.user_data.address_data.id) {
                        //选中
                        _this.address_data.selectedAddressId = _this.user_data.address_data.id;
                    }

                    this.address_data.manageModal(true);
                },
                //提交或者发起支付
                submitOrInitiatePay: function (e) {
                    if (this.payment_data && this.payment_data.order_num) {
                        this.initiatePay(e);
                    } else {
                        this.submitOrder(e);
                    }
                },
                //显示折扣模态框
                showDiscountHitModal() {
                    var _this = this;
                    var item_data = [];
                    this.optsEach(function (author_id, product_id, opt_data) {
                        item_data.push(opt_data);
                    });

                    return this.discountHitModal(item_data, this.discount_data, this.product_data);
                },
                showGiftModal: function (opt_data) {
                    return this.giftModal(opt_data.gift_data);
                },
                //提交订单
                submitOrder: function (e) {
                    var _this = this;

                    //验证邮箱
                    if (_this.shipping_has_auto && !_this.user_data.email && _this.email_fill === 'required') {
                        return notyf('请输入邮箱', 'warning');
                    }

                    //验证地址
                    if (_this.shipping_has_express) {
                        if (!_this.user_data.address_data['name'] || !_this.user_data.address_data['phone'] || !_this.user_data.address_data['address']) {
                            return notyf('请选择收货地址', 'warning');
                        }
                    }

                    var cart_data = {};
                    var user_required_error = [];

                    this.optsEach(function (author_id, product_id, opt_data, options_str) {
                        //验证必填项
                        if (opt_data.user_required.length) {
                            $.each(opt_data.user_required, function (index, item) {
                                if (!item.value) {
                                    user_required_error.push(item.name);
                                }
                            });
                        }

                        if (user_required_error.length > 0) {
                            return false;
                        }

                        if (!cart_data[opt_data.product_id]) {
                            cart_data[opt_data.product_id] = {};
                        }
                        cart_data[opt_data.product_id][options_str] = {
                            count: opt_data.count,
                            remark: opt_data.remark,
                            user_required: opt_data.user_required,
                        };
                    });

                    if (user_required_error.length > 0) {
                        return notyf('请填写' + user_required_error.join(','), 'warning');
                    }

                    var order_data = {
                        products: cart_data,
                        pay_method: _this.pay_data.pay_methods_active,
                        pay_modo: _this.total_data.pay_modo,
                        price: _this.total_data.pay_price,
                        points: _this.total_data.pay_points,
                        address_data: _this.user_data.address_data,
                        user_email: _this.user_data.email,
                        payment_method: _this.pay_data.pay_methods_active,
                        action: 'shop_submit_order',
                    };

                    if (this.config.is_cart) {
                        order_data.is_cart = 1;
                    }

                    $(e.target).zib_ajax(
                        order_data,
                        function (n) {
                            //下单成功，发起支付
                            if (n.payment_data) {
                                //发起支付
                                _this.order_data = n.order_data;
                                _this.payment_data = n.payment_data;
                                _this.initiatePay();
                            }
                        },
                        'stop'
                    );
                },
                //发起支付
                initiatePay: function () {
                    this.$nextTick(() => {
                        var $zibpay_form = $(this.$refs.zibpay_form);
                        $zibpay_form
                            .find('.initiate-pay')
                            .on('zib_ajax.success', function (e, n) {
                                if (n.error) {
                                    //发起支付，但是失败
                                }
                            })
                            .click();
                    });
                },
                showAddAddressModal: function () {
                    this.address_data.addModal(true);
                },
                //支付方式变化
                paymentMethodChange(key) {
                    this.pay_data.pay_methods_active = key;
                },
                //循环函数构建
                optsEach: function (func) {
                    var _this = this;
                    $.each(_this.item_data, function (author_id, product_items) {
                        $.each(product_items, function (product_id, product_opt) {
                            $.each(product_opt, function (opt_key_str, opt_data) {
                                func(author_id, product_id, opt_data, opt_key_str);
                            });
                        });
                    });
                },
            },
            config
        );

        window.VueShopConfirmData = PetiteVue.reactive(app_data);
        return PetiteVueCreateApp(window.VueShopConfirmData, [selector, '#' + remark_modal_id, '#' + user_required_modal_id]);
    }

    //------------------------------------------购物车------------------------------------------
    function VShopCart(selector) {
        var config = $(selector).attr('v-config');
        config = $.parseJSON(config);
        $(selector).removeAttr('v-config');
        //创建购物车模态框
        config.$cart_modal = VueTemplate.prefix('cart_modal_data').createCartModal();
        config.product_checked = [];

        var app_data = $.extend(
            createVueComponents(),
            {
                mounted: function () {
                    var _this = this;
                    _this.syncTotal = debounce(_this._syncTotal, 50);
                    _this.syncPrice = debounce(_this._syncPrice, 50);
                    _this.ajaxUpdataCart = debounce(_this._ajaxUpdataCart, 300); //节流0.3秒

                    //同步商品选项
                    _this.syncCartOptions(true);
                    //同步价格
                    _this.syncPrice();
                    //同步库存
                    _this.syncStock();

                    //绑定屏幕宽度变化，同步更新is_mobile
                    $(window).on(
                        'resize',
                        debounce(function () {
                            _this.is_mobile = isMobile();
                        }, 200)
                    );
                },
                showItemDiscountHitModal(opt_data) {
                    return this.discountHitModal([opt_data], null, this.product_data);
                },
                showDiscountHitModal() {
                    var _this = this;
                    var item_data = [];
                    _this.CartOptsEach(function (author_id, product_id, opt_data, opt_key_str) {
                        if (opt_data.checked) {
                            item_data.push(opt_data);
                        }
                    });

                    return this.discountHitModal(item_data, null, this.product_data);
                },
                //列表数量变化
                listCountChange: function (opt_data) {
                    this.countChange(opt_data);
                    if (!opt_data.checked) {
                        this.checkItem(opt_data);
                    }
                },
                //模态框数量变化
                modalCountChange: function () {
                    this.countChange(this.cart_modal_data, true);
                    this.modalSyncItemPrice();
                },
                //模态框商品选项变化
                modalOptChange: function (opt_index, opt_val) {
                    this.cart_modal_data.options_active[opt_index] = opt_val;
                    this.syncItemStock(this.cart_modal_data, this.product_data[this.cart_modal_data.product_id]);
                    this.modalSyncItemPrice();

                    //主图切换，将自己的图赋予到主图位置
                    var main_image_url = this.product_data[this.cart_modal_data.product_id].product_options[opt_index].opts[opt_val].image;
                    if (main_image_url) {
                        this.cart_modal_data.main_image_url = main_image_url;
                    }
                },
                modalSyncItemPrice: function () {
                    this.syncItemPrice(this.cart_modal_data);
                    this.syncCartItemDiscountPrice(this.cart_modal_data);
                },
                syncCartItemDiscountPrice: function (opt_data) {
                    this.syncItemDiscountPrice(opt_data, this.discountDataDependency(opt_data), this.product_data[opt_data.product_id]);
                },
                discountDataDependency: function (item_data) {
                    var data = {
                        user_data: this.user_data,
                        product_data: {
                            price: 0,
                            count: 0,
                        },
                        author_data: {
                            price: 0,
                            count: 0,
                        },
                        total_data: {
                            price: 0,
                            count: 0,
                        },
                    };
                    if (!item_data || !item_data.prices) {
                        return data;
                    }

                    //如果商品被选中，则循环获取所有选中数据
                    var product_data = {};
                    var author_data = {};

                    if (item_data.checked) {
                        this.CartOptsEach(function (author_id, product_id, opt_data, opt_key_str) {
                            if (opt_data.checked) {
                                if (!product_data[product_id]) {
                                    product_data[product_id] = {
                                        price: 0,
                                        count: 0,
                                    };
                                }

                                if (!author_data[author_id]) {
                                    author_data[author_id] = {
                                        price: 0,
                                        count: 0,
                                    };
                                }

                                product_data[product_id].price += opt_data.prices.total_price;
                                product_data[product_id].count += opt_data.selected_count;

                                author_data[author_id].price += opt_data.prices.total_price;
                                author_data[author_id].count += opt_data.selected_count;

                                data.total_data.price += opt_data.prices.total_price;
                                data.total_data.count += opt_data.selected_count;
                            }
                        });

                        data.product_data = product_data[item_data.product_id];
                        data.author_data = author_data[item_data.author_id];
                    } else {
                        data.product_data.price = item_data.prices.total_price;
                        data.product_data.count = item_data.selected_count;
                        data.author_data.price = item_data.prices.total_price;
                        data.author_data.count = item_data.selected_count;
                        data.total_data.price = item_data.prices.total_price;
                        data.total_data.count = item_data.selected_count;
                    }

                    return data;
                },
                //购物车模态框提交
                cartModalSubmit: function () {
                    //选中
                    var _this_modal_data = this.cart_modal_data;
                    var _author_id = _this_modal_data.author_id;
                    var _product_id = _this_modal_data.product_id;
                    var _options_active_str = _this_modal_data.options_active_str;

                    if (_this_modal_data.product_options.length > 0 && _this_modal_data.options_active.length !== _this_modal_data.product_options.length) {
                        _this_modal_data.product_options.forEach(function (product_option, product_option_index) {
                            if (!_this_modal_data.options_active[product_option_index]) {
                                return notyf('请选择' + product_option.name, 'warning'), false;
                            }
                        });
                        return;
                    }

                    this.cart_data[_author_id][_product_id][_options_active_str].options_active_error = false;
                    this.cart_data[_author_id][_product_id][_options_active_str].options_active = vueToObj(_this_modal_data.options_active);
                    this.cart_data[_author_id][_product_id][_options_active_str].checked = true;
                    this.cart_data[_author_id][_product_id][_options_active_str].selected_count = _this_modal_data.selected_count;
                    this.cart_data[_author_id][_product_id][_options_active_str].stock_all = _this_modal_data.stock_all;
                    this.cart_data[_author_id][_product_id][_options_active_str].main_image_url = _this_modal_data.main_image_url;

                    console.log(_this_modal_data);

                    //同步商品选项
                    this.syncCartOptions();

                    //同步价格
                    this.syncPrice();

                    //同步总计
                    this.syncTotal();

                    //联网更新购物车数据
                    this.ajaxUpdataCart();

                    this.$cart_modal.modal('hide');
                },
                //显示商品选项模态框
                cartModal: function (opt_data) {
                    //赋值非响应式数据，避免数据互窜
                    var data = vueToObj(opt_data);
                    data.title = this.product_data[opt_data.product_id].title;
                    this.cart_modal_data = data;

                    this.$cart_modal.modal('show');
                },
                //同步商品选项，合并同选项
                syncCartOptions: function ($is_first) {
                    var _this = this;
                    var str_split = ' · ';

                    _this.CartOptsEach(function (author_id, product_id, opt_data, opt_key_str) {
                        //添加基本信息
                        //这是不变化的
                        var product_options = _this.product_data[product_id].product_options;

                        if ($is_first) {
                            opt_data.cart_submit_btn_text = _this.config.cart_submit_btn_text;
                            opt_data.product_id = product_id;
                            opt_data.author_id = author_id;
                            opt_data.product_title = _this.product_data[product_id].product_title;
                            opt_data.pay_modo = _this.product_data[product_id].pay_modo;
                            opt_data.show_mark = _this.product_data[product_id].show_mark;
                            opt_data.product_options = product_options;
                            opt_data.limit_buy = _this.product_data[product_id].limit_buy;
                            opt_data.thumbnail = _this.product_data[product_id].thumbnail;
                            opt_data.url = _this.product_data[product_id].url;
                            opt_data.discount = _this.product_data[product_id].discount;
                        }

                        opt_data.options_active_str = ''; //初始化
                        opt_data.options_active_name = ''; //初始化
                        opt_data.selected_count = ~~(opt_data.selected_count || 0);

                        if (product_options.length > 0) {
                            //有商品选项
                            if (typeof opt_data.options_active === 'object' && opt_data.options_active.length > 0) {
                                //选择了商品选项

                                opt_data.options_active_str = optsKeyToStr(opt_data.options_active);

                                if (!_this.isOptError(product_id, opt_data)) {
                                    //选中的选项合法
                                    $.each(opt_data.options_active, function (active_key, active_val) {
                                        if (product_options[active_key] && product_options[active_key].opts && product_options[active_key].opts[active_val]) {
                                            opt_data.options_active_name += product_options[active_key].opts[active_val].name + str_split;

                                            //首次同步主图
                                            if (!opt_data.main_image_url && product_options[active_key].opts[active_val].image) {
                                                opt_data.main_image_url = product_options[active_key].opts[active_val].image;
                                            }
                                        }
                                    });
                                }
                            }

                            if (opt_data.options_active_name === '') {
                                opt_data.options_active_name = '请选择商品选项'; //初始化
                            } else {
                                //去除最后一个分隔符
                                opt_data.options_active_name = opt_data.options_active_name.slice(0, -str_split.length);
                            }
                        }

                        //同步选项主图
                        if (!opt_data.main_image_url) {
                            opt_data.main_image_url = _this.product_data[product_id].main_image_url;
                        }

                        if (opt_data.options_active_str === '') {
                            opt_data.options_active_str = '0'; //初始化
                        }

                        if (opt_data.options_active_str != opt_key_str) {
                            $new_product_id_data = {};

                            //如果存在相同的商品选项，则合并数量
                            if (_this.cart_data[author_id][product_id][opt_data.options_active_str]) {
                                opt_data.selected_count += ~~_this.cart_data[author_id][product_id][opt_data.options_active_str].selected_count;
                                delete _this.cart_data[author_id][product_id][opt_data.options_active_str];
                            }
                            for (let key in _this.cart_data[author_id][product_id]) {
                                if (key === opt_key_str) {
                                    $new_product_id_data[opt_data.options_active_str] = opt_data;
                                } else {
                                    $new_product_id_data[key] = _this.cart_data[author_id][product_id][key];
                                }
                            }
                            _this.cart_data[author_id][product_id] = $new_product_id_data;
                        }
                    });
                },
                //获取购物车需要上传到服务器的数据
                getHttpData: function () {
                    var _this = this;
                    var cart_data = {};

                    _this.CartOptsEach(function (author_id, product_id, opt_data) {
                        if (!cart_data[product_id]) {
                            cart_data[product_id] = {};
                        }

                        var options_string = opt_data.options_active_str;
                        cart_data[product_id][options_string] = opt_data.selected_count;
                    });

                    return cart_data;
                },

                //循环opt_data数据，把最新的ajax同步给服务器
                _ajaxUpdataCart: function () {
                    var cart_data = this.getHttpData();
                    $.ajax({
                        type: 'POST',
                        url: _win.ajax_url,
                        data: {
                            action: 'update_cart',
                            cart_data: cart_data,
                        },
                        dataType: 'json',
                        error: function (response) {
                            console.error('更新购物车数据失败', response);
                        },
                        success: function (response) {
                            if (response.error) {
                                console.error(response.msg || '更新购物车数据失败', response);
                            }
                            if (response.count > 0) {
                                setWinCartCount(response.count);
                            }
                        },
                    });
                },

                //购物车移出选中商品
                removeChecked: function () {
                    if (!this.total_data.checked_data.length) {
                        return;
                    }
                    //系统二次确认
                    if (!confirm('确定要移出选中商品吗？')) {
                        return;
                    }

                    var _this = this;
                    $.each(_this.total_data.checked_data, function (index, item) {
                        _this.removeItem(item.author_id, item.product_id, item.opt_key);
                    });
                },

                //购物车移出一个商品类型
                removeItem: function (author_id, product_id, opt_key, is_confirm) {
                    var _this = this;

                    if (is_confirm) {
                        //系统二次确认
                        if (!confirm('确定要移出该商品吗？')) {
                            return;
                        }
                    }

                    var $el = $('.v-cart [data-author-id="' + author_id + '"] [data-product-id="' + product_id + '"] [data-optkey="' + opt_key + '"]');

                    $el.css({ background: 'rgba(255,46,46,0.05)' }).slideUp(200, function () {
                        //移出数据
                        delete _this.cart_data[author_id][product_id][opt_key];

                        if ($.isEmptyObject(_this.cart_data[author_id][product_id])) {
                            delete _this.cart_data[author_id][product_id];
                        }

                        if ($.isEmptyObject(_this.cart_data[author_id])) {
                            delete _this.cart_data[author_id];
                        }

                        //1.同步价格
                        _this.syncPrice();
                        //2.同步总计
                        _this.syncTotal();
                        //3.联网更新购物车数据
                        _this.ajaxUpdataCart();
                    });
                },
                //选择作者
                checkAuthor: function (author_id) {
                    var _this = this;
                    $.each(_this.cart_data[author_id], function (product_id, product_opt) {
                        $.each(product_opt, function (opt_key_str, opt_data) {
                            if (_this.author_data[author_id].checked_status) {
                                opt_data.checked = false;
                            } else {
                                if (opt_data.selected_count > 0 && opt_data.stock_all != 0 && !_this.isOptError(opt_data.product_id, opt_data)) {
                                    opt_data.checked = true;
                                } else {
                                    opt_data.checked = false;
                                }
                            }
                        });
                    });
                    //同步总计
                    this.syncTotal();
                },
                //选择全部
                checkAll: function () {
                    var _this = this;
                    _this.CartOptsEach(function (author_id, product_id, opt_data) {
                        if (_this.total_data.checked_status) {
                            opt_data.checked = false;
                        } else {
                            if (opt_data.selected_count > 0 && opt_data.stock_all != 0 && !_this.isOptError(opt_data.product_id, opt_data)) {
                                opt_data.checked = true;
                            } else {
                                opt_data.checked = false;
                            }
                        }
                    });
                    //同步总计
                    this.syncTotal();
                },
                //选择单个
                checkItem: function (opt_data) {
                    if (opt_data.stock_all == 0) {
                        opt_data.checked = false;
                        return notyf('商品库存不足', 'warning');
                    }

                    if (opt_data.selected_count == 0) {
                        opt_data.checked = false;
                        return notyf('请先选择购买数量', 'warning');
                    }

                    if (opt_data.checked) {
                        opt_data.checked = false;
                    } else {
                        if (this.isOptError(opt_data.product_id, opt_data)) {
                            opt_data.checked = false;
                            return notyf('请先选择商品选项', 'warning');
                        }

                        opt_data.checked = true;
                    }
                    //同步总计
                    this.syncTotal();
                },
                //提交确认订单
                goConfirm: function () {
                    var _this = this;
                    var cart_data = {};

                    if (!_this.total_data.is_can_pay) {
                        return;
                    }

                    _this.CartOptsEach(function (author_id, product_id, opt_data) {
                        if (opt_data.checked) {
                            if (!cart_data[product_id]) {
                                cart_data[product_id] = {};
                            }

                            var options_string = opt_data.options_active_str;
                            cart_data[product_id][options_string] = opt_data.selected_count;
                        }
                    });

                    var data = {
                        products: cart_data,
                        is_cart: true,
                    };

                    VShopConfirmModal(data);
                },
                //同步总计
                _syncTotal: function () {
                    var _this = this;
                    var all_checked_data = [];
                    var all_checked_count = 0;
                    var all_checked_price = 0;
                    var all_checked_points = 0;
                    var all_checked_discount_points = 0;
                    var all_checked_discount_price = 0;
                    var all_checked_price_count = 0;
                    var all_checked_points_count = 0;
                    var all_pay_modo = '';
                    var all_show_mark = '';
                    var each_i = 0;
                    var each_product_count = {};
                    var author_checked_int = {};
                    var all_checked_int = { yes: 0, no: 0 };
                    var all_discount_hit = [];
                    var is_edit = _this.config.is_edit;
                    var product_total_price = {};

                    var discountDataDependency = _this.discountDataDependency();

                    _this.CartOptsEach(function (author_id, product_id, opt_data) {
                        if (!author_checked_int[author_id]) {
                            author_checked_int[author_id] = {
                                yes: 0,
                                no: 0,
                            };
                        }

                        //如果有一个商品未选中，则全选为false
                        if (!opt_data.checked) {
                            author_checked_int[author_id]['no']++;
                            all_checked_int['no']++;
                        } else {
                            //如果有商品选项，但是没有选择正确的选项，则不选中
                            if (_this.isOptError(product_id, opt_data)) {
                                opt_data.checked = false;
                                author_checked_int[author_id]['no']++;
                                all_checked_int['no']++;

                                is_edit || debounce_notyf('请选择商品[' + _this.product_data[product_id].product_title + ']的商品选项', 'warning');
                            } else {
                                author_checked_int[author_id]['yes']++;
                                all_checked_int['yes']++;
                            }

                            //被选中的数据
                            each_i++;

                            //计算总计数
                            all_checked_count += ~~opt_data.selected_count;

                            //统计每个商品数量，用于限购和库存判断
                            if (!each_product_count[product_id]) {
                                each_product_count[product_id] = 0;
                            }
                            each_product_count[product_id] += ~~opt_data.selected_count;

                            //统计全部选中的数据
                            all_checked_data.push({
                                author_id: author_id,
                                product_id: product_id,
                                opt_key: opt_data.options_active_str,
                            });

                            if (each_i === 1) {
                                all_pay_modo = _this.product_data[product_id].pay_modo;
                                all_show_mark = _this.product_data[product_id].show_mark;
                            }

                            if (!product_total_price[product_id]) {
                                product_total_price[product_id] = 0;
                            }

                            //计算商品的总价格，以及总原价
                            var item_total_price = Number(opt_data.prices.total_price);
                            product_total_price[product_id] += item_total_price;

                            //计算总价格
                            if (_this.product_data[product_id].pay_modo === 'points') {
                                all_checked_points += item_total_price;
                                all_checked_discount_points += item_total_price;
                                all_checked_points_count++;
                            } else {
                                all_checked_price += item_total_price;
                                all_checked_discount_price += item_total_price;
                                all_checked_price_count++;
                            }
                        }
                    });

                    //设置商品总金额
                    $.each(product_total_price, function (product_id, product_total_price) {
                        _this.product_data[product_id].prices.total_price = product_total_price;
                    });

                    //设置作者选中状态
                    $.each(author_checked_int, function (author_id, author_data) {
                        _this.author_data[author_id].checked_status = '';
                        if (author_data['yes'] > 0) {
                            _this.author_data[author_id].checked_status = 'checked';
                            if (author_data['no'] > 0) {
                                _this.author_data[author_id].checked_status = 'portion';
                            }
                        }
                    });

                    //总选中状态
                    var _total_data = _this.total_data;
                    _total_data.checked_status = '';
                    if (all_checked_int['yes'] > 0) {
                        _total_data.checked_status = 'checked';
                        if (all_checked_int['no'] > 0) {
                            _total_data.checked_status = 'portion';
                        }
                    }

                    _total_data.pay_modo = all_pay_modo;
                    _total_data.show_mark = all_show_mark;
                    _total_data.checked_data = all_checked_data;
                    _total_data.count = all_checked_count;

                    _total_data.price_count = all_checked_price_count;
                    _total_data.points_count = all_checked_points_count;

                    _total_data.price = priceRounding(all_checked_price);
                    _total_data.discount_price = priceRounding(all_checked_discount_price);
                    _total_data.points = ~~all_checked_points;
                    _total_data.discount_points = ~~all_checked_discount_points;

                    //判断 is_can_pay ，限购，库存
                    var is_can_pay = all_checked_count > 0;
                    $.each(each_product_count, function (product_id, product_count) {
                        //判断库存是否充足
                        if (_this.isStockLow(product_id, product_count)) {
                            is_can_pay = false;

                            is_edit || debounce_notyf('商品[' + _this.product_data[product_id].title + ']库存不足，请调整选择', 'warning');
                        }

                        //判断限购
                        if (_this.isLimitBuy(product_id, product_count)) {
                            is_can_pay = false;
                            var msg = '商品[' + _this.product_data[product_id].title + ']限购' + _this.product_data[product_id].limit_buy.limit + '件，请调整选择';
                            if (_this.product_data[product_id].limit_buy.limit == 0) {
                                msg = '商品[' + _this.product_data[product_id].title + ']限购，无法购买，请调整选择';
                            }
                            is_edit || debounce_notyf(msg, 'warning');
                        }
                    });

                    _total_data.is_mix = false;
                    if (all_checked_price_count > 0 && all_checked_points_count > 0) {
                        //暂时不允许积分和现金混合支付，弹出错误
                        is_can_pay = false;
                        _total_data.is_mix = true;
                        is_edit || debounce_notyf('请勿同时选择积分和现金商品', 'error');
                    }

                    if (is_can_pay) {
                        //重新计算优惠价
                        _total_data.discount_points = 0;
                        _total_data.discount_price = 0;
                        _this.CartOptsEach(function (author_id, product_id, opt_data) {
                            _this.syncCartItemDiscountPrice(opt_data);
                            if (opt_data.checked) {
                                var opt_total_discount_price = Number(opt_data.prices.total_discount_price);
                                if (_total_data.pay_modo === 'points') {
                                    _total_data.discount_points += ~~opt_total_discount_price;
                                } else {
                                    _total_data.discount_price += priceRounding(opt_total_discount_price);
                                }
                            }
                        });
                    }

                    _total_data.is_can_pay = is_can_pay;
                },
                //判断选项是否错误
                isOptError: function (product_id, opt_data) {
                    opt_data.options_active_error = false;
                    if (!this.product_data[product_id].product_options.length) {
                        return false;
                    }

                    if (this.product_data[product_id].product_options.length > 0) {
                        if (this.product_data[product_id].product_options.length !== opt_data.options_active.length) {
                            opt_data.options_active_error = true;
                            return true;
                        }
                    }

                    return false;
                },
                //判断是否被限购
                isLimitBuy: function (product_id, count) {
                    if (this.product_data[product_id].limit_buy.is_limit && count > this.product_data[product_id].limit_buy.limit) {
                        return true;
                    }
                    return false;
                },
                //判断商品是否库存不足
                isStockLow: function (product_id, count) {
                    if (this.product_data[product_id].stock_all >= 0 && this.product_data[product_id].stock_type !== 'opts' && count > this.product_data[product_id].stock_all) {
                        return true; //库存不足
                    }
                    return false; //库存充足
                },
                //同步库存
                syncStock: function () {
                    var _this = this;
                    _this.CartOptsEach(function (author_id, product_id, opt_data) {
                        _this.syncItemStock(opt_data, _this.product_data[product_id]);
                    });
                },
                //数量变化
                countChange: function (opt_data, no_sync) {
                    if (opt_data.stock_all >= 0 && opt_data.selected_count > opt_data.stock_all) {
                        opt_data.selected_count = Math.max(opt_data.stock_all, 1);
                        return notyf('库存不足', 'warning');
                    }

                    //商品限购
                    if (this.isLimitBuy(opt_data.product_id, opt_data.selected_count)) {
                        opt_data.selected_count = Math.max(opt_data.selected_count - 1, 1);
                        var msg = '当前商品限购' + this.product_data[opt_data.product_id].limit_buy.limit + '件';
                        if (this.product_data[opt_data.product_id].limit_buy.limit == 0) {
                            msg = '当前商品限购，无法购买';
                        }
                        return notyf(msg, 'warning');
                    }

                    if (!no_sync) {
                        //2.同步价格（先）
                        this.syncPrice();

                        if (opt_data.checked) {
                            //2.同步总价
                            this.syncTotal();
                        }

                        //联网更新购物车数据
                        this.ajaxUpdataCart();
                    }
                },
                //同步价格
                _syncPrice: function () {
                    var _this = this;
                    //循环同步每一个商品选项价格
                    _this.CartOptsEach(function (author_id, product_id, opt_data) {
                        _this.syncItemPrice(opt_data, _this.product_data[product_id]);
                        _this.syncCartItemDiscountPrice(opt_data);
                    });
                },

                //循环函数构建
                CartOptsEach: function (func) {
                    var _this = this;
                    $.each(_this.cart_data, function (author_id, product_items) {
                        $.each(product_items, function (product_id, product_opt) {
                            $.each(product_opt, function (opt_key_str, opt_data) {
                                func(author_id, product_id, opt_data, opt_key_str);
                            });
                        });
                    });
                },
            },
            config
        );

        window.vue_cart_data = PetiteVue.reactive(app_data);

        //构建vue
        PetiteVueCreateApp(window.vue_cart_data);
    }

    //------------------------------------------商品详情页------------------------------------------
    function VShopDetail(selector) {
        var config = $(selector).attr('v-config');
        config = $.parseJSON(config);
        $(selector).removeAttr('v-config');

        config.stock_all = ~~config.stock_all;
        config.options_active_str = '';
        config.options_active = [];
        config.selected_count = 1;
        config.discount_hit = []; //命中的优惠
        config.important_discount = [];
        config.$cart_modal = VueTemplate.createProductModal();

        var app_data = $.extend(
            createVueComponents(),
            {
                getOptionsActiveName(options_active) {
                    if (options_active.length === 0) {
                        return '';
                    }

                    var separator = ' · ';
                    var _this = this;
                    var options_active_name = '';
                    options_active.forEach(function (opt, opt_index) {
                        options_active_name += _this.product_options[opt_index].opts[opt].name + separator;
                    });
                    return options_active_name.slice(0, -separator.length);
                },
                //初始任务
                mounted: function () {
                    var _this = this;

                    //处理初始选项
                    _this.product_options.forEach(function (opt, opt_index) {
                        //处理起始被选中的商品选项
                        _this.options_active[opt_index] = 0;
                        _this.options_active_str += optsKeySplicing(opt_index, '0');
                    });

                    //定义节流函数
                    _this.syncPrice = debounce(this._syncPrice, 100); //防抖
                    _this.cartModalSubmit = debounce(this._cartModalSubmit, 200);
                    _this.orderModalSubmit = debounce(this._orderModalSubmit, 200);

                    //同步库存
                    _this.syncStock();

                    //处理价格
                    _this.syncPrice();

                    //循环查找第一个重点活动
                    for (var i = 0; i < _this.discount.length; i++) {
                        if (_this.discount[i].is_important && _this.discount[i].is_valid) {
                            _this.important_discount = _this.discount[i];
                            break;
                        }
                    }

                    //绑定屏幕宽度变化，同步更新is_mobile
                    $(window).on(
                        'resize',
                        debounce(function () {
                            _this.is_mobile = isMobile();
                        }, 200)
                    );
                },
                cartBtnClick: function (e) {
                    if (this.is_mobile) {
                        //弹出窗口选择
                        this.cartModalShow();
                    } else {
                        this.addCart(e);
                    }
                },
                orderBtnClick: function (e) {
                    if (this.is_mobile) {
                        //弹出窗口选择
                        this.cartModalShow();
                    } else {
                        this.orderSubmit(e);
                    }
                },
                cartModalShow: function () {
                    this.$cart_modal.modal('show');
                },
                cartModalHide: function () {
                    this.is_mobile && this.$cart_modal.modal('hide');
                },
                modalOptChange: function (opt_index, item_index) {
                    this.optChange(opt_index, item_index);
                },
                modalCountChange: function (is_add) {
                    this.countChange(is_add);
                },
                _cartModalSubmit: function (e) {
                    this.addCart(e);
                },
                _orderModalSubmit: function (e) {
                    this.orderSubmit(e);
                    this.cartModalHide();
                },
                //商品详情页面，提交订单
                orderSubmit: function (e) {
                    if (!this.user_data.user_id && !this.guest_buy) {
                        loginBtnClick();
                        return;
                    }

                    var data = {
                        products: {},
                    };

                    data['products'][this.product_id] = {};
                    data['products'][this.product_id][this.options_active_str] = this.selected_count;

                    VShopConfirmModal(data);
                },
                //添加购物车
                addCart: function (e) {
                    if (!this.user_data.user_id) {
                        loginBtnClick();
                        return;
                    }

                    var $this = $(e.target);
                    var _this = this;
                    zib_ajax(
                        $this,
                        {
                            action: 'cart_add',
                            product_id: this.product_id,
                            options_active: this.options_active_str,
                            count: this.selected_count,
                        },
                        function (n) {
                            //关闭弹窗
                            _this.cartModalHide();

                            //执行加入购物车动画
                            _this.cartAddAnimate(n.count);
                        },
                        'stop'
                    );
                },
                cartAddAnimate: function (count) {
                    var thumbnail_url = this.main_image_url || this.thumbnail_url;
                    var image = '<img class="fit-cover" src="' + thumbnail_url + '">';
                    var cart_box = $('<div class="cart-add-animate flex jc fixed"><div class="animate-img"><div class="img-box">' + image + '</div></div></div>');
                    cart_box.appendTo('body');
                    //执行动画，先缩小

                    var $to,
                        timing = 'cubic-bezier(0.6, -0.57, 1, 1)';
                    if (this.is_mobile) {
                        $to = $('.tabbar-cart');
                    } else {
                        timing = 'linear';
                        $to = $('.nav-cart');
                    }

                    if (!$to.length) {
                        return cart_box.remove();
                    }

                    var distance = getAddAnimateDistance(cart_box, $to);
                    setTimeout(function () {
                        var _time = 0.4;
                        cart_box
                            .css({
                                transform: 'translate3d(0, ' + distance.y + 'px, 0)',
                                transition: 'transform ' + _time + 's ' + timing,
                            })
                            .find('.animate-img')
                            .css({
                                transform: 'translate3d(' + distance.x + 'px, 0, 0)',
                                transition: 'transform ' + _time + 's linear',
                            });
                        setTimeout(function () {
                            if (count > 0) {
                                setWinCartCount(count);
                            }
                            $to.addClass('add-animate');

                            setTimeout(function () {
                                $to.removeClass('add-animate');
                                if (count > 0) {
                                    setWinCartCount(count);
                                }
                            }, 300);
                            cart_box.remove();
                        }, _time * 1000);
                    }, 200);
                },
                //选项改变
                optChange: function (opt_id, item_id) {
                    this.options_active[opt_id] = item_id;

                    this.options_active_str = optsKeyToStr(this.options_active);

                    //同步库存
                    this.syncStock();

                    //同步价格
                    this.syncPrice();

                    //幻灯片图片切换
                    if ($('.swiper-slide[product-opt-index="' + opt_id + '_' + item_id + '"]').length > 0) {
                        var swiper = $('.product-cover-slider')[0].swiper;
                        //获取要切换到第几个
                        var index = $('.swiper-slide[product-opt-index="' + opt_id + '_' + item_id + '"]').index();
                        swiper.slideTo(index);
                    }

                    //主图切换，将自己的图赋予到主图位置
                    var main_image_url = this.product_options[opt_id].opts[item_id].image;
                    if (main_image_url) {
                        this.main_image_url = main_image_url;
                    }
                },
                //数量变化
                countChange: function () {
                    //同步价格
                    this.syncPrice();
                },
                countMaxChange: function () {
                    var max = this.selected_count + 1;
                    if (this.stock_all >= 0 && max > this.stock_all) {
                        return notyf('库存不足', 'warning');
                    }

                    //商品限购
                    if (this.limit_buy.is_limit && max > this.limit_buy.limit) {
                        var msg = '当前商品限购' + this.limit_buy.limit + '件';
                        if (this.limit_buy.limit == 0) {
                            msg = '当前商品限购，无法购买';
                        }
                        return notyf(msg, 'warning');
                    }
                },
                //同步价格
                _syncPrice: function () {
                    this.syncItemPrice(this);

                    var data = {
                        user_data: this.user_data,
                        product_data: {
                            price: this.prices.total_price,
                            count: this.selected_count,
                        },
                        author_data: {
                            price: this.prices.total_price,
                            count: this.selected_count,
                        },
                        total_data: {
                            price: this.prices.total_price,
                            count: this.selected_count,
                        },
                    };

                    this.syncItemDiscountPrice(this, data);
                },
                //同步库存
                syncStock: function () {
                    this.syncItemStock(this);
                },
                getOptStock: function () {
                    //获取选项库存
                },
            },
            config
        );

        //构建vue
        PetiteVueCreateApp(app_data);
    }

    //封装PetiteVue.createApp
    function PetiteVueCreateApp(app_data, selector) {
        var VueDirective = {
            'transition-group': function (ctx) {
                // 获取元素和绑定值
                const el = ctx.el;
                const elGetkey = function (el) {
                    return el.getAttribute('transition-key');
                };
                const elGetPosition = function (el) {
                    return {
                        left: el.offsetLeft,
                        top: el.offsetTop,
                    };
                };
                // 记录元素位置
                let childrenData = new Map();
                const recordPositions = () => {
                    const children = Array.from(el.children);
                    childrenData.clear();
                    children.forEach((child) => {
                        // 使用transition-key作为唯一标识
                        const transitionKey = elGetkey(child);
                        // 确保元素可见且有transition-key才记录位置
                        if (child.offsetParent !== null && transitionKey) {
                            childrenData.set(transitionKey, {
                                dom: child,
                                position: elGetPosition(child),
                            });
                        }
                    });
                };

                // 创建MutationObserver监听子元素变化
                const observer = new MutationObserver((mutations) => {
                    // 记录变化前的位置
                    const oldChildrenData = new Map(childrenData);
                    // 记录变化后的位置
                    recordPositions();
                    // 处理新增和移除的节点
                    const addedNodes = [];
                    const removedNodes = [];

                    //通过对比新旧数据，获取新增和移除的节点
                    childrenData.forEach((newPos, key) => {
                        if (!oldChildrenData.has(key)) {
                            addedNodes.push(childrenData.get(key));
                        }
                    });

                    oldChildrenData.forEach((oldPos, key) => {
                        if (!childrenData.has(key)) {
                            removedNodes.push(oldChildrenData.get(key));
                        }
                    });

                    // 为新增节点设置初始状态
                    addedNodes.forEach((val) => {
                        var node = val.dom;
                        node.style.opacity = '0';
                        node.style.transform = 'translate3d(20px, 0, 0)';
                    });

                    childrenData.forEach((val) => {
                        //基于新旧位置，设置初始位置
                        var node = val.dom;
                        const oldPos = oldChildrenData.get(elGetkey(node));
                        const newPos = childrenData.get(elGetkey(node));

                        if (oldPos && newPos) {
                            var left = oldPos.position.left - newPos.position.left;
                            var top = oldPos.position.top - newPos.position.top;
                            node.style.transform = `translate3d(${left}px, ${top}px, 0)`;
                        }
                    });

                    setTimeout(() => {
                        childrenData.forEach((val) => {
                            var node = val.dom;
                            node.style.transition = 'all 0.3s ease';
                            node.style.opacity = 1;
                            node.style.transform = 'translate3d(0, 0, 0)';
                            setTimeout(() => {
                                node.style.transform = '';
                                node.style.transition = '';
                                node.style.opacity = '';
                            }, 300);
                        });
                    }, 20);
                });

                // 开始观察
                setTimeout(() => {
                    recordPositions();
                    observe();
                }, 16);

                function observe() {
                    observer.observe(el, {
                        childList: true,
                        attributes: true,
                        subtree: false,
                    });
                }

                // 在元素销毁时断开观察器
                return () => {
                    observer.disconnect();
                };
            },
            transition: function (ctx) {
                // 获取元素和绑定值
                const el = ctx.el;
                const value = ctx.get();

                // 保存初始显示状态
                let isVisible = el.style.display !== 'none';

                // 设置过渡样式
                el.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                // 监听值变化
                ctx.effect(() => {
                    const newValue = ctx.get();

                    // 如果状态没变，不执行动画
                    if ((newValue && isVisible) || (!newValue && !isVisible)) {
                        return;
                    }

                    if (newValue) {
                        // 显示元素
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(-10px)';
                        el.style.display = '';

                        // 强制回流
                        el.offsetHeight;

                        // 执行显示动画
                        el.style.opacity = '1';
                        el.style.transform = 'translateY(0)';
                        isVisible = true;
                    } else {
                        // 执行隐藏动画
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(-10px)';

                        // 动画结束后隐藏元素
                        setTimeout(() => {
                            if (!ctx.get()) {
                                el.style.display = 'none';
                                isVisible = false;
                            }
                        }, 300);
                    }
                });

                // 初始状态设置
                if (!value && isVisible) {
                    el.style.display = 'none';
                    isVisible = false;
                } else if (value && !isVisible) {
                    el.style.display = '';
                    isVisible = true;
                }
            },

            'discount-badge': function (ctx) {
                function is_hit_discount(item) {
                    var getAttribute = ctx.el.getAttribute('data-hit-discount');
                    if (!getAttribute) {
                        return true;
                    }

                    var hit_discount = ctx.get(getAttribute);

                    if (!hit_discount || hit_discount.length === 0) {
                        return false;
                    }

                    return hit_discount.some(function (hit_item) {
                        return hit_item.id === item.id;
                    });
                }

                var updata = function (el, value) {
                    if (!value || value.length === 0) {
                        el.innerHTML = '';
                        el.classList.add('hide');
                        return;
                    }
                    el.classList.remove('hide');
                    var html = '';
                    var is_valid = ctx.modifiers && ctx.modifiers.hit;

                    value.forEach(function (item) {
                        if (item.is_valid || is_valid) {
                            html += '<span class="badge badge-discount' + (is_hit_discount(item) ? '' : ' no-hit') + '">' + (item.small_badge || item.name) + '</span>';
                        }
                    });
                    el.innerHTML = html;
                };

                updata(ctx.el, ctx.value);
                ctx.effect(() => {
                    updata(ctx.el, ctx.get());
                });
            },

            'limit-buy': function (ctx) {
                var updateLimitBuy = function (el, value) {
                    if (!value || !value.is_limit) {
                        el.innerHTML = '';
                        el.classList.add('hide');
                        return;
                    }

                    el.classList.remove('hide');
                    var text = '限购：' + value.limit;
                    if (value.limit == 0) {
                        text = '已限购';
                    }

                    el.innerHTML = text;

                    $(el).data('limitBuy', vueToObj(value));
                };

                //绑定事件
                $(ctx.el).on('click', function () {
                    var limit = $(this).data('limitBuy');
                    if (!limit || !limit.is_limit) {
                        return;
                    }

                    function getLimitItem(val, name) {
                        val = Number(val);
                        var text = '限购' + val + '件';
                        if (val === -1) {
                            text = '不限购';
                        }
                        if (val == 0) {
                            text = '无法购买';
                        }

                        return '<div class="flex ac jsb list-mt20"><div class="muted-2-color">' + name + '</div><div class="">' + text + '</div></div>';
                    }

                    var lists = '';
                    var content = '';
                    var title = '<div class="border-title touch"><div class="flex jc"><b>商品限购</b></div></div>';

                    $.each(limit.key_names, function (key, name) {
                        lists += getLimitItem(limit[key], name);
                    });

                    lists = (limit.desc ? '<div class="muted-color mb10">' + limit.desc + '</div>' : '') + '<div class="muted-box">' + lists + '</div>';
                    if (limit.limit_all || limit.bought_count) {
                        lists += '<div class="muted-box mt10 c-yellow text-center">此商品您已下单' + limit.bought_count + '件，' + (limit.limit ? '还可购买' + limit.limit + '件' : '已无法购买') + '</div>';
                    } else {
                        lists += '<div class="muted-box mt10 c-yellow text-center">此商品您可购买' + limit.limit + '件</div>';
                    }
                    content = '<div class="">' + lists + '</div>';

                    return refresh_modal({
                        class: 'shop-modal modal-mini full-sm',
                        mobile_from_bottom: true,
                        content: '<button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="">' + title + content + '</div>',
                    });
                });

                //添加鼠标
                $(ctx.el).css('cursor', 'help').attr('title', '查看限购规则');

                updateLimitBuy(ctx.el, ctx.value);
                ctx.effect(() => {
                    updateLimitBuy(ctx.el, ctx.get());
                });
            },

            price: function (ctx) {
                var updatePrice = function (el, value) {
                    if (value === undefined || value === null) {
                        el.innerHTML = '0';
                        return;
                    }

                    // 目标价格
                    const targetPrice = value;
                    const targetPrice_str = priceRounding(targetPrice).toString();
                    function get_html(value) {
                        var value_str = priceRounding(value).toString();
                        var parts = value_str.split('.');
                        var html = parts[0];
                        if (parts[1] && targetPrice_str.includes('.')) {
                            // 获取目标价格的小数位数
                            var targetParts = targetPrice_str.split('.');
                            var decimalPlaces = targetParts[1] ? targetParts[1].length : 0;
                            // 确保当前值的小数位数与目标价格一致
                            var decimalPart = parts[1] ? parts[1].substring(0, decimalPlaces) : '';
                            if (decimalPart.length < decimalPlaces) {
                                decimalPart = decimalPart.padEnd(decimalPlaces, '0');
                            }
                            html += '<span class="decimal-part">.' + decimalPart + '</span>';
                        }
                        return html;
                    }

                    // 封装数字动画函数
                    function animateNumber(element, from, to, duration) {
                        let start = null;
                        const step = (timestamp) => {
                            if (!start) start = timestamp;
                            const progress = Math.min((timestamp - start) / duration, 1);
                            const currentValue = from + (to - from) * progress;

                            // 将价格转换为字符串并确保有两位小数
                            element.innerHTML = get_html(currentValue);
                            if (progress < 1) {
                                window.requestAnimationFrame(step);
                            }
                        };

                        if (window.requestAnimationFrame) {
                            window.requestAnimationFrame(step);
                        } else {
                            element.innerHTML = get_html(targetPrice);
                        }
                    }

                    // 获取当前显示的价格（如果有）
                    let currentPrice = 0;
                    if (el.textContent) {
                        const currentText = el.textContent.replace(/[^\d.-]/g, '');
                        currentPrice = parseFloat(currentText) || 0;
                    }

                    // 执行动画
                    if (currentPrice !== targetPrice) {
                        animateNumber(el, currentPrice, targetPrice, 200);
                    }
                };

                // 初始更新
                updatePrice(ctx.el, ctx.value);

                // 当值变化时更新
                ctx.effect(() => {
                    updatePrice(ctx.el, ctx.get());
                });
            },
            spinner: function (ctx) {
                const assign = ctx.get(`(val) => { ${ctx.exp} = val }`);
                const trigger = (el, type) => {
                    const e = document.createEvent('HTMLEvents');
                    e.initEvent(type, true, true);
                    el.dispatchEvent(e);
                };
                var get_max = function () {
                    var max = ctx.el.getAttribute('max');
                    //如果是数字
                    if (!$.isNumeric(max)) {
                        return -1;
                    }

                    max = ~~max;
                    return max >= -1 ? max : -1;
                };

                var get_min = function () {
                    var min = ctx.el.getAttribute('min');
                    //如果是数字
                    if (!$.isNumeric(min)) {
                        return 1;
                    }

                    min = ~~min;
                    return min >= 1 ? min : 1;
                };

                var initSpinner = function (el, value) {
                    // 创建数量选择器的HTML结构
                    value = Number(value);
                    var max = get_max();
                    var min = get_min();

                    var html = '<div class="inline-flex number-spinner">';
                    html += '<span class="minus-btn' + (min !== -1 && value <= min ? ' is-disabled' : '') + '" data-action="minus">';
                    html += '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-minus"></use></svg>';
                    html += '</span>';
                    html += '<span class="number-input">' + value + '</span>';
                    html += '<span class="plus-btn' + (max !== -1 && value >= max ? ' is-disabled' : '') + '" data-action="plus">';
                    html += '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-add"></use></svg>';
                    html += '</span>';
                    html += '</div>';

                    el.innerHTML = html;
                    var $el = $(el);

                    $el.on('click', '.plus-btn', function () {
                        var max = get_max();
                        if (max !== -1) {
                            if (ctx.get() >= max) {
                                return trigger(el, 'max-change'), false;
                            }
                        }

                        assign(ctx.get() + 1);
                        trigger(el, 'change');
                        return false;
                    });

                    $el.on('click', '.minus-btn', function () {
                        var min = get_min();

                        if (min !== -1) {
                            if (ctx.get() <= min) {
                                return trigger(el, 'min-change'), false;
                            }
                        }

                        assign(ctx.get() - 1);
                        trigger(el, 'change');
                        return false;
                    });

                    //创建数字输入的弹窗
                    $el.on('click', '.number-input', function () {
                        input_number_modal(
                            {
                                min: get_min(),
                                max: get_max(),
                            },
                            function (val) {
                                assign(val);
                                trigger(el, 'change');
                            }
                        );
                    });

                    //监控max和min变化
                    //监控元素的attr变化
                    var observer = new MutationObserver(function (mutations) {
                        mutations.forEach(function (mutation) {
                            if (mutation.type === 'attributes') {
                                if (mutation.attributeName === 'max' || mutation.attributeName === 'min') {
                                    updateSpinner(ctx.el, ctx.get());
                                }
                            }
                        });
                    });

                    observer.observe(ctx.el, {
                        attributes: true,
                    });
                };

                var input_number_modal = function (config, callback) {
                    var _dasc = '';
                    var _min_attr = '';
                    var _max_attr = '';
                    if (config.min !== -1) {
                        _min_attr = ' limit-min="' + config.min + '" warning-min="最少1$件"';
                    }
                    if (config.max !== -1) {
                        if (config.max <= 3) {
                            return;
                        }
                        _dasc = '最多可购买' + config.max + '件';
                        _max_attr = ' limit-max="' + config.max + '" warning-max="最多1$件"';
                    }

                    _dasc = _dasc ? '<div class="muted-2-color mt6">' + _dasc + '</div>' : '';

                    var modal_title = '<div class="flex jc"><b>请输入数量</b></div>';
                    var modal_content = '<form><div style="mb20"><div class="mt20 relative"><input type="number" class="form-control" name="val"' + _min_attr + _max_attr + '></div>' + _dasc + ' </div><botton type="submit" class="mt20 but jb-blue padding-lg btn-block">确认</botton></form>';

                    //创建模态框
                    var $modal = refresh_modal({
                        class: 'shop-modal modal-mini full-sm',
                        mobile_from_bottom: true,
                        new: true,
                        content: '<button class="close abs-close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button><div class="">' + modal_title + modal_content + '</div>',
                    });

                    $modal.on('shown.bs.modal', function () {
                        $modal.find('input[name="val"]').focus();
                    });

                    $modal.find('[type="submit"]').on('click', function () {
                        $modal.find('form').submit();
                        return false;
                    });

                    $modal.find('form').on('submit', function (e) {
                        e.preventDefault();
                        var val = ~~$modal.find('input[name="val"]').val();
                        if (config.max !== -1 && val > config.max) {
                            return notyf('最大数量不能超过' + config.max, 'warning'), false;
                        }
                        if (config.min !== -1 && val < config.min) {
                            return notyf('最小数量不能低于' + config.min, 'warning'), false;
                        }

                        //执行回调
                        callback(val);

                        //关闭弹窗
                        $modal.modal('hide');
                        return false;
                    });
                };

                var updateSpinner = function (el, value) {
                    value = Number(value);
                    var max = get_max();
                    var min = get_min();

                    el.querySelector('.number-input').textContent = value;
                    el.querySelector('.minus-btn').classList.toggle('is-disabled', min !== -1 && value <= min);
                    el.querySelector('.plus-btn').classList.toggle('is-disabled', max !== -1 && value >= max);
                };

                // 初始更新
                initSpinner(ctx.el, ctx.get());

                // 当值变化时更新
                ctx.effect(() => {
                    updateSpinner(ctx.el, ctx.get());
                });
            },
        };

        var app = PetiteVue.createApp(app_data);
        //循环添加自定义指令
        $.each(VueDirective, function (key, value) {
            app.directive(key, value);
        });

        if (typeof selector === 'object') {
            $.each(selector, function (key, value) {
                app.mount(value);
            });
        } else {
            app.mount(selector);
        }
        return app;
    }

    //判断是否需要加载VUE
    if ($('.vue-mount').length) {
        tbquire(['main', 'petite-vue', 'pay'], function () {
            if ($('.v-shop-detail').length) {
                VShopDetail('.v-shop-detail');
            }
            if ($('.v-cart').length) {
                VShopCart('.v-cart');
            }
        });
    }

    //弹窗修改地址
    $body.on('loaded.bs.modal', '#refresh_modal', function () {
        if ($(this).find('#order_modify_address').length) {
            tbquire(['petite-vue'], function () {
                VShopModifyAddress('#order_modify_address');
            });
        }
    });

    $body.on('click', '.shop-user-order-tab-btn', function () {
        var $this = $(this);
        var $next = $('#user-tab-order > .post_ajax_trigger > .ajax-next');
        if ($next.length) {
            var second_tab = $this.attr('second-tab');
            if (second_tab) {
                $next.attr('href', $next.attr('href') + '&tab=' + second_tab);
            }
        }
    });

    $body.on('click', '.expand-toggle', function () {
        var $this = $(this);
        var expand_class = 'expanded';
        var is_show = $this.hasClass(expand_class); //展开
        var closest = $this.attr('closest-selector');
        var expand_count = ~~$this.attr('expand-count');
        var find_selector = $this.attr('find-selector');

        var $closest = null;
        if (closest) {
            $closest = $this.closest(closest);
        }
        if (!$closest || !$closest.length) {
            $closest = $this.parent();
        }
        find_selector = find_selector || '>*';
        find_selector += expand_count ? ':nth-child(n+' + expand_count + ')' : '';
        var $collapse_list = $closest.find(find_selector);

        var collapse_text = $this.attr('collapse-text') || '收起更多信息';
        var expand_text = $this.attr('expand-text') || '展开全部信息';

        if (is_show) {
            $collapse_list.addClass('hide');
            $this
                .removeClass(expand_class + ' hide')
                .find('.btn-text')
                .html(expand_text);
        } else {
            $collapse_list.removeClass('hide');
            $this.addClass(expand_class).find('.btn-text').html(collapse_text);
        }
    });
})();
