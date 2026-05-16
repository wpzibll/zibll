/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-04-07 19:39:36
 * @LastEditTime : 2026-04-29 22:36:37
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

(function ($, window, document) {
    $(document).ready(function () {
        function debounce(callback, delay, immediate) {
            var timeout;
            return function () {
                var context = this,
                    args = arguments;
                var later = function () {
                    timeout = null;
                    if (!immediate) {
                        callback.apply(context, args);
                    }
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, delay);
                if (callNow) {
                    callback.apply(context, args);
                }
            };
        }

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

        var ElementPlus = window.ElementPlus;
        const Main = {
            data() {
                return $.extend(
                    {
                        svg: {
                            refresh: '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path fill="currentColor" d="M771.776 794.88A384 384 0 0 1 128 512h64a320 320 0 0 0 555.712 216.448H654.72a32 32 0 1 1 0-64h149.056a32 32 0 0 1 32 32v148.928a32 32 0 1 1-64 0v-50.56zM276.288 295.616h92.992a32 32 0 0 1 0 64H220.16a32 32 0 0 1-32-32V178.56a32 32 0 0 1 64 0v50.56A384 384 0 0 1 896.128 512h-64a320 320 0 0 0-555.776-216.384z"></path></svg>',
                            table_option: '<svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path fill="currentColor" d="M389.44 768a96.064 96.064 0 0 1 181.12 0H896v64H570.56a96.064 96.064 0 0 1-181.12 0H128v-64zm192-288a96.064 96.064 0 0 1 181.12 0H896v64H762.56a96.064 96.064 0 0 1-181.12 0H128v-64zm-320-288a96.064 96.064 0 0 1 181.12 0H896v64H442.56a96.064 96.064 0 0 1-181.12 0H128v-64z"></path></svg>',
                        },
                        loading: {
                            shipping_table_list: true,
                            shipping_dialog_submit_but: false,
                            update_option_but: false,
                            express_traces: true,
                            after_sale_record_html: false,
                        },
                        win: {
                            width: window.innerWidth,
                        },
                    },
                    window._vue_data
                );
            },
            watch: {
                //路由变化
                $route: function (val) {
                    var path = val.path;
                    //去掉开头的/
                    path = path.replace(/^\//, '');
                    path = path.replace(/-/g, '_');

                    if (this[path + '_data']) {
                        //执行参数过滤
                        var params = val.query;
                        for (var key in params) {
                            var params_val = params[key];
                            if (key == 'search') {
                                this[path + '_data'].search = params_val;
                                continue;
                            }

                            if (key == 'search_filter') {
                                this[path + '_data'].search_filter = [params_val];
                                continue;
                            }

                            if (['status', 'shipping_status', 'after_sale_status', 'order_type', 'rebate_status', 'income_status'].includes(key)) {
                                //将字符串转换为数组
                                if (params_val.includes(',')) {
                                    params_val = params_val.split(',');
                                } else {
                                    params_val = [params_val];
                                }
                                params_val = params_val.map((item) => ~~item);
                            }

                            if (['after_sale_type'].includes(key)) {
                                //将字符串转换为数组
                                if (params_val.includes(',')) {
                                    params_val = params_val.split(',');
                                } else {
                                    params_val = [params_val];
                                }
                            }

                            this[path + '_data'].filter[key] = params_val;
                        }

                        this.getTableList(path);
                    } else {
                        this.getDashboardData();
                    }
                },
                'dashboard.order_chart_data.show_tab': function (val) {
                    // 取消监听
                    if (!this.dashboard.order_chart_data[val].is_init) {
                        this.getOrderChartData(val);
                    }
                },
            },
            mounted() {
                setTimeout(function () {
                    $('.shop-page-loading').fadeOut(300);
                }, 100);

                var _this = this;
                //绑定窗口变化更新width
                window.addEventListener(
                    'resize',
                    debounce(function () {
                        _this.win.width = window.innerWidth;
                    }, 100)
                );
            },
            created() {
                let _this = this;
                // 用 Lodash 的防抖函数
                _this.ajax = debounce(_this._ajax, 100);
            },
            methods: {
                menuGo(path) {
                    this.go(path, {});
                },
                go(path, params) {
                    params = params || {};
                    this.$router.push({ path: path, query: params });
                },
                goParams(params) {
                    this.$router.replace({ path: this.$router.currentRoute.value.path, query: params });
                },
                //仪表盘数据
                getDashboardData() {
                    var _this = this;
                    _this.getOrderChartData();
                    _this.getStatisticsData(); //统计数据
                    _this.getTypePieData(); //订单类型饼图
                    _this.getHotData(); //热销商品
                    _this.getAssetData(); //资产数据
                },
                //统计数据
                getStatisticsData() {
                    var _this = this;
                    var _main = _this.dashboard;
                    _main.statistics_loading = true;
                    _this._ajax('admin_statistics_data', {}, function (data) {
                        if (data.mini_card_data) {
                            _main.mini_card_data = data.mini_card_data;
                        }

                        if (data.todo_data) {
                            _main.todo_data = data.todo_data;
                        }

                        if (data.mini_chart_data) {
                            var __today_chart = _main.mini_chart_data.today_sales.chart;
                            __today_chart.xAxis.data = data.mini_chart_data.today_sales.time;
                            __today_chart.series.data = data.mini_chart_data.today_sales.price; //销售额
                            _main.mini_chart_data.today_sales.data = __today_chart.series.data[0];

                            var today = __today_chart.series.data.slice(-1)[0];
                            var lastday = __today_chart.series.data.slice(-2)[0];

                            //计算同比
                            var ratio = priceRounding((today - lastday) / (lastday > 0 ? lastday : 1));
                            _main.mini_chart_data.today_sales.data = today;
                            _main.mini_chart_data.today_sales.ratio = _this.priceCut(ratio, 'price');

                            var __month_chart = _main.mini_chart_data.month_sales.chart;
                            __month_chart.xAxis.data = data.mini_chart_data.month_sales.time;
                            __month_chart.series.data = data.mini_chart_data.month_sales.price; //销售额

                            var month_today = __month_chart.series.data.slice(-1)[0];
                            var month_lastday = __month_chart.series.data.slice(-2)[0];

                            //计算同比
                            ratio = priceRounding((month_today - month_lastday) / (month_lastday || 1));
                            _main.mini_chart_data.month_sales.data = month_today;
                            _main.mini_chart_data.month_sales.ratio = _this.priceCut(ratio, 'price');
                        }

                        _main.statistics_loading = false;
                    });
                },
                //热销商品
                getHotData() {
                    var _this = this;
                    _this.loading.hot_product_data = true;
                    _this._ajax(
                        'admin_hot_product_data',
                        {
                            order_type: _this.dashboard.hot_product_data.order_type,
                            time: _this.dashboard.hot_product_data.time,
                        },
                        function (data) {
                            _this.dashboard.hot_product_data.data = data.data;

                            //计算总销量和总销售额
                            _this.dashboard.hot_product_data.max = data.max;
                            _this.sortHotData();
                        }
                    );
                },
                //热销商品排序
                sortHotData() {
                    var _main = this.dashboard.hot_product_data;
                    var data = _main.data;
                    if (_main.type == 'price') {
                        data.sort((a, b) => b.price - a.price);
                    } else {
                        data.sort((a, b) => b.count - a.count);
                    }
                },
                //资产数据
                getAssetData() {
                    var _this = this;
                    var _main = _this.dashboard.asset_data;
                    _this.loading.asset_data = true;
                    _this._ajax('admin_asset_ranking_data', { time: _main.time }, function (data) {
                        _main.rebate_data = data.rebate_data;
                        _main.income_data = data.income_data;
                        _main.points_data = data.points_data;
                        _main.balance_data = data.balance_data;
                    });
                },
                //订单类型饼图
                getTypePieData(type) {
                    var _this = this;
                    var _main = _this.dashboard.type_pie_data;
                    type = type || _main.type;
                    _this.loading.type_pie = true;
                    _this._ajax(
                        'admin_type_pie_data',
                        {
                            time: _main.time,
                            type: type,
                        },
                        function (data) {
                            _main.chart.series.data = data.data;
                        }
                    );
                },
                //仪表盘订单统计图表
                getOrderChartData(cycle) {
                    var _this = this;
                    cycle = cycle || _this.dashboard.order_chart_data.show_tab;
                    var _main = _this.dashboard.order_chart_data[cycle];
                    _main.is_init = true;
                    _this.loading.order_chart = true;

                    _this._ajax(
                        'admin_order_chart_data',
                        {
                            order_type: _main.order_type,
                            cycle: cycle,
                            time: _this.timefilterFormat(_main.timefilter),
                        },
                        function (data) {
                            _main.chart.xAxis.data = data.chart.time; //X坐标
                            _main.chart.series[0].data = data.chart.price; //销售额
                            _main.chart.series[1].data = data.chart.nums; //销量

                            _main.minitable = data.table; //表格
                        }
                    );
                },
                //作者地址提交
                AuthorAddressSubmit() {
                    var _this = this;

                    var save_data = {
                        address: _this.author_data.new_address_dialog_data,
                        user_id: _this.author_data.author_id,
                    };

                    _this.loading.author_address_submit_but = true;

                    _this.ajax('shop_save_author_address', save_data, function (data) {
                        if (!data.error && data.data) {
                            _this.author_data.new_address_dialog_show = 0;
                            _this.after_sale_data.return_address_dialog_data = data.data;
                            _this.after_sale_data.handle_dialog_data.author_info.author_address = _this.after_sale_data.return_address_dialog_data;
                        }
                    });
                },
                //设置默认地址
                setDefaultAddress(address) {
                    address.is_default = true;
                    var _this = this;
                    _this.author_data.new_address_dialog_data = address;
                    _this.AuthorAddressSubmit();
                },
                //编辑地址
                editAddress(address) {
                    this.author_data.new_address_dialog_data = address;
                    this.author_data.new_address_dialog_show = 1;
                },
                //删除地址
                deleteAddress(address_id) {
                    if (!confirm('确定删除此地址？')) {
                        return;
                    }

                    var _this = this;
                    var ajax_data = {
                        address_id: address_id,
                    };

                    this.ajax('shop_delete_author_address', ajax_data, function (data) {
                        if (!data.error && data.data) {
                            _this.after_sale_data.return_address_dialog_data = data.data;
                            _this.after_sale_data.handle_dialog_data.author_info.author_address = _this.after_sale_data.return_address_dialog_data;
                        }
                    });
                },
                //选择地址
                afterSaleSelectAddress(address) {
                    this.after_sale_data.handle_dialog_data.return_address = address;
                    this.after_sale_data.return_address_dialog_show = 0;
                },
                //添加新地址
                afterSaleAddNewAddress() {
                    this.author_data.new_address_dialog_show = 1;
                    this.author_data.new_address_dialog_data = {
                        id: '',
                        name: '',
                        phone: '',
                        province: '',
                        city: '',
                        county: '',
                        address: '',
                        tag: '',
                        is_default: false,
                    };
                },
                //售后状态筛选
                afterSaleStatusChange(status) {
                    status = status ? [~~status] : [];
                    this.after_sale_data.filter.after_sale_status = status;
                    this.getTableList('after_sale');
                },
                //售后物流弹窗
                afterSaleExpressDialog(row, type) {
                    var _this = this;
                    type = type || 'user_return';
                    this.loading.express_dialog = true;
                    this.express_dialog_data.show = 1;
                    this.express_dialog_data.type = type;
                    this.express_dialog_data.express_data = [];
                    if (type === 'user_return') {
                        this.express_dialog_data.address_data = row.after_sale_data.return_address;
                    }
                    if (type === 'author_return') {
                        this.express_dialog_data.address_data = row.order_data.consignee.return_address;
                    }

                    this.ajax('after_sale_express_data', { id: row.id, type: type }, function (data) {
                        if (data.express_data) {
                            _this.express_dialog_data.express_data = data.express_data;
                        }
                    });
                },

                /**手动补单弹窗 */
                paymentOrderDialog(row) {
                    this.payment_dialog_data = row;
                    this.payment_dialog_data.show = 1;

                    this.payment_dialog_data.payment_method = 'other';
                    this.payment_dialog_data.pay_price = Number(row.prices.pay_price).toFixed(2);
                },
                //手动补单提交
                paymentSubmit() {
                    var _this = this;
                    _this.loading.payment_dialog_submit_but = true;
                    var payment_dialog_data = _this.payment_dialog_data;
                    var ajax_data = {
                        order_num: payment_dialog_data.order_num,
                        payment_method: payment_dialog_data.payment_method,
                        pay_price: payment_dialog_data.pay_price,
                        pay_num: payment_dialog_data.pay_num,
                        _wpnonce: _this.admin_payment_submit_nonce,
                    };

                    _this.ajax('admin_payment_submit', ajax_data, function (data) {
                        if (!data.error) {
                            //关闭弹窗
                            _this.payment_dialog_data.show = 0;
                            _this.refreshTheTableList();
                        }
                    });
                },
                afterSaleHandle(row) {
                    //显示处理弹窗
                    this.after_sale_data.handle_dialog_data = row;
                    this.after_sale_data.handle_dialog_show = 1;
                    this.after_sale_data.details_drawer_show = 0;

                    //获取退回地址：查找默认地址
                    var author_address = row.author_info.author_address;
                    if (author_address) {
                        this.after_sale_data.handle_dialog_data.return_address = author_address.find((item) => item.is_default);
                    } else {
                        this.after_sale_data.handle_dialog_data.return_address = null;
                    }
                },
                afterSaleDetails(row) {
                    var _this = this;
                    var _this_after_sale_data = _this.after_sale_data;

                    _this.shipping_data.details_drawer_show = false;
                    _this_after_sale_data.details_drawer_show = 1;
                    _this_after_sale_data.details_drawer_data = row;
                    _this_after_sale_data.handle_dialog_show = 0;
                    _this_after_sale_data.details_drawer_data.after_sale_record_html = '';
                    _this.loading.after_sale_record_html = false;
                    if (_this_after_sale_data.details_drawer_data.after_sale_record_count < 1) {
                        return;
                    }
                    _this.loading.after_sale_record_html = true;
                    _this.ajax('admin_after_sale_record_html', { id: row.id }, function (data) {
                        if (data.html) {
                            _this_after_sale_data.details_drawer_data.after_sale_record_html = data.html;
                        }
                    });
                },
                afterSaleReturnAddressDialog() {
                    this.author_data.author_id = this.after_sale_data.handle_dialog_data.author_info.author_id;
                    this.after_sale_data.return_address_dialog_show = 1;
                    this.after_sale_data.return_address_dialog_data = this.after_sale_data.handle_dialog_data.author_info.author_address || [];
                },
                afterSaleHandleSubmit() {
                    var _this = this;
                    _this.loading.handle_dialog_submit_but = true;
                    var after_sale_dialog_data = _this.after_sale_data.handle_dialog_data;

                    var ajax_data = {
                        id: after_sale_dialog_data.id,
                        handle_type: after_sale_dialog_data.handle_type,
                        author_remark: after_sale_dialog_data.author_remark,
                        refund_channel: after_sale_dialog_data.pay_modo === 'points' ? 'points' : after_sale_dialog_data.refund_channel,
                        return_address: after_sale_dialog_data.return_address,
                    };

                    if (after_sale_dialog_data.handle_type !== 'agree') {
                        if (!confirm('确认拒绝此售后申请？')) {
                            _this.loading.handle_dialog_submit_but = false;
                            return;
                        }
                    } else if (['refund', 'insured_price'].includes(after_sale_dialog_data.after_sale_type)) {
                        //系统提醒，确定同意退款，并将退款金额打入用户账户
                        var text = after_sale_dialog_data.refund_channel == 'balance' || after_sale_dialog_data.refund_channel === 'points' ? '确定同意退款？同意后系统将自动退款到用户账户！' : '确定同意退款，并已将退款金额打入用户账户？';
                        if (!confirm(text)) {
                            _this.loading.handle_dialog_submit_but = false;
                            return;
                        }
                        ajax_data.price = after_sale_dialog_data.after_sale_data.price;
                    }

                    _this.ajax('admin_after_sale_handle_submit', ajax_data, function (data) {
                        if (!data.error) {
                            //关闭弹窗
                            _this.after_sale_data.handle_dialog_show = 0;
                            _this.refreshTheTableList();
                        }
                    });
                },

                //售后退款退货
                afterSalerefundReturnSubmit() {
                    var _this = this;
                    _this.loading.handle_dialog_submit_but = true;
                    var after_sale_dialog_data = _this.after_sale_data.handle_dialog_data;

                    var ajax_data = {
                        id: after_sale_dialog_data.id,
                        handle_type: after_sale_dialog_data.handle_type,
                        author_remark: after_sale_dialog_data.author_remark,
                        refund_channel: after_sale_dialog_data.pay_modo === 'points' ? 'points' : after_sale_dialog_data.refund_channel,
                        return_address: after_sale_dialog_data.return_address,
                    };

                    _this.ajax('admin_after_sale_refund_return_handle', ajax_data, function (data) {
                        if (!data.error) {
                            //关闭弹窗
                            _this.after_sale_data.handle_dialog_show = 0;
                            _this.refreshTheTableList();
                        }
                    });
                },

                //发货提交
                shippingSubmit() {
                    var _this = this;
                    _this.loading.shipping_dialog_submit_but = true;
                    var shipping_dialog_data = _this.shipping_data.shipping_dialog_data;
                    var ajax_data = {
                        id: shipping_dialog_data.id,
                        express_number: shipping_dialog_data.express_number,
                        express_company_name: shipping_dialog_data.express_company_name,
                        delivery_content: shipping_dialog_data.delivery_content,
                        delivery_remark: shipping_dialog_data.delivery_remark,
                        delivery_type: shipping_dialog_data.manual_delivery_type,
                    };

                    _this.ajax('admin_shipping_submit', ajax_data, function (data) {
                        if (!data.error) {
                            //关闭弹窗
                            _this.shipping_data.shipping_dialog_show = 0;
                            _this.refreshTheTableList();
                        }
                    });
                },
                //批量发货提交
                shippingBatchSubmit() {
                    var _this = this;
                    _this.loading.shipping_dialog_submit_but = true;
                    var shipping_dialog_data = _this.shipping_data.batch_shipping_dialog_data;

                    console.log(shipping_dialog_data);

                    var ajax_data = {
                        ids: shipping_dialog_data.orders.map((item) => item.id).join(','),
                        express_number: shipping_dialog_data.express_number,
                        express_company_name: shipping_dialog_data.express_company_name,
                        delivery_content: shipping_dialog_data.delivery_content,
                        delivery_remark: shipping_dialog_data.delivery_remark,
                        delivery_type: shipping_dialog_data.manual_delivery_type,
                    };

                    _this.ajax('admin_batch_shipping_submit', ajax_data, function (data) {
                        if (!data.error) {
                            //关闭弹窗
                            _this.shipping_data.batch_shipping_dialog_show = 0;
                            _this.refreshTheTableList();
                        }
                    });
                },

                //刷新当前路由下的TableList
                refreshTheTableList() {
                    var path = this.$router.currentRoute.value.path;
                    path = path.replace(/^\//, '');
                    path = path.replace(/-/g, '_');

                    if (this[path + '_data']) {
                        this.getTableList(path);
                    }
                },

                //发货详情
                shippingDetails(row) {
                    this.shipping_data.details_drawer_data = row;
                    this.shipping_data.details_drawer_show = 1;
                    this.shipping_data.shipping_dialog_show = 0;
                    this.after_sale_data.details_drawer_show = 0;
                    this.loading.express_traces = 0;

                    if (row.shipping_status >= 1 && row.shipping_type === 'express' && row.shipping_data.express_number) {
                        this.loading.express_traces = true;
                        this.getExpressData(row);
                    }
                },
                //批量发货选择
                shippingSelectionChange(selection) {
                    this.shipping_data.selection_data = selection;
                },
                //批量发货
                shippingSelectionDelivery() {
                    var _this = this;

                    _this.shipping_data.batch_shipping_dialog_show = 1;
                    var express_orders = [];
                    var manual_orders = [];
                    var auto_orders = [];
                    _this.shipping_data.selection_data.forEach(function (item) {
                        if (item.shipping_status != 0 || item.status != 1) {
                            return;
                        }

                        if (item.shipping_type == 'auto') {
                            auto_orders.push(item);
                        }
                        if (item.shipping_type == 'manual') {
                            manual_orders.push(item);
                        }
                        if (item.shipping_type == 'express') {
                            express_orders.push(item);
                        }
                    });

                    _this.shipping_data.batch_shipping_dialog_data.express_orders = express_orders;
                    _this.shipping_data.batch_shipping_dialog_data.manual_orders = manual_orders;
                    _this.shipping_data.batch_shipping_dialog_data.auto_orders = auto_orders;
                    _this.shipping_data.batch_shipping_dialog_data.orders = express_orders.concat(manual_orders).concat(auto_orders);

                    this.shipping_data.batch_shipping_dialog_data.manual_delivery_type = 'express';
                    this.shipping_data.batch_shipping_dialog_data.express_number = '';
                    this.shipping_data.batch_shipping_dialog_data.express_company_name = '';
                    this.shipping_data.batch_shipping_dialog_data.delivery_content = '';
                    this.shipping_data.batch_shipping_dialog_data.delivery_remark = '';
                    //弹窗
                    _this.shipping_data.batch_shipping_dialog_show = 1;
                },
                //批量移出订单
                batchRemoveOrder(row) {
                    this.shipping_data.batch_shipping_dialog_data.orders = this.shipping_data.batch_shipping_dialog_data.orders.filter(function (item) {
                        return item.id !== row.id;
                    });
                    this.shipping_data.batch_shipping_dialog_data.express_orders = this.shipping_data.batch_shipping_dialog_data.express_orders.filter(function (item) {
                        return item.id !== row.id;
                    });
                    this.shipping_data.batch_shipping_dialog_data.manual_orders = this.shipping_data.batch_shipping_dialog_data.manual_orders.filter(function (item) {
                        return item.id !== row.id;
                    });
                    this.shipping_data.batch_shipping_dialog_data.auto_orders = this.shipping_data.batch_shipping_dialog_data.auto_orders.filter(function (item) {
                        return item.id !== row.id;
                    });
                },
                //清空批量发货订单
                shippingSelectionClear() {
                    this.shipping_data.selection_data = [];
                    this.$refs.shippingTableRef.clearSelection();
                },
                getExpressData(row) {
                    this.ajax('shipping_express_data', { order_id: row.id }, function (data) {
                        if (data.express_data) {
                            row.express_data = data.express_data;
                        }
                    });
                },
                shippingStatusChange(status) {
                    status = status !== '' ? [~~status] : [];
                    this.shipping_data.filter.shipping_status = status;
                    this.getTableList('shipping');
                },
                //显示发货弹窗
                showShippingDialog(row) {
                    //判断订单状态
                    if (row.status == 0) {
                        ElementPlus.ElNotification.error('订单未付款，无法发货');
                        return;
                    }

                    if (row.status == -1) {
                        ElementPlus.ElNotification.error('订单已关闭，无法发货');
                        return;
                    }

                    if (row.shipping_status == 2) {
                        ElementPlus.ElNotification.error('订单已完成，无法发货');
                        return;
                    }

                    this.shipping_data.shipping_dialog_data = row;
                    this.shipping_data.shipping_dialog_data.manual_delivery_type = 'express';
                    this.shipping_data.shipping_dialog_show = 1;
                    this.shipping_data.details_drawer_show = 0;

                    if (row.shipping_status != 0) {
                        this.shipping_data.shipping_dialog_data.manual_delivery_type = row.shipping_data.delivery_type || 'express';
                        this.shipping_data.shipping_dialog_data.express_number = row.shipping_data.express_number || '';
                        this.shipping_data.shipping_dialog_data.express_company_name = row.shipping_data.express_company_name || '';
                        this.shipping_data.shipping_dialog_data.delivery_content = row.shipping_data.delivery_content || '';
                        this.shipping_data.shipping_dialog_data.delivery_remark = row.shipping_data.delivery_remark || '';
                    }
                },

                afterSaleDbSort(column_data) {
                    this.DbSort(column_data, 'after_sale');
                },
                shippingDbSort(column_data) {
                    this.DbSort(column_data, 'shipping');
                },
                orderDbSort(column_data) {
                    this.DbSort(column_data, 'order');
                },
                DbSort(column_data, table_name) {
                    this[table_name + '_data'].order = column_data.order;
                    this[table_name + '_data'].orderby = column_data.prop;
                    this.getTableList(table_name);
                },
                dBRefresh(table_name) {
                    //清空排序
                    this[table_name + '_data'].order = '';
                    this[table_name + '_data'].orderby = '';
                    //清空搜索
                    this[table_name + '_data'].search = '';
                    this[table_name + '_data'].search_filter = '';
                    //清空筛选
                    this[table_name + '_data'].filter = {};

                    //循环timefilter
                    for (let key in this[table_name + '_data'].timefilter) {
                        this[table_name + '_data'].timefilter[key] = [];
                    }

                    //清空分页
                    this[table_name + '_data'].current_page = 1;
                    this[table_name + '_data'].page_size = 20;

                    this.goParams({});
                    // this.getTableList(table_name);
                },
                dBSearch(table_name) {
                    this.getTableList(table_name);
                },
                dbFilter(table_name) {
                    this.getTableList(table_name);
                },
                dBPagChange(table_name, is_current) {
                    this.getTableList(table_name, is_current);
                },
                //获取表格信息统一接口
                getTableList(table_name, is_pag) {
                    var _this = this;
                    _this.loading[table_name + '_table_list'] = true;
                    var _main = _this[table_name + '_data'];
                    var ajax_data = {
                        paged: _main.current_page,
                        pagesize: _main.page_size,
                        order: _main.order,
                        orderby: _main.orderby,
                        search: _main.search,
                        search_filter: _main.search_filter,
                        filter: _main.filter,
                        timefilter: _this.getDbTimefilter(_main.timefilter),
                    };

                    if (is_pag === 'current') {
                        ajax_data.pag_total = _main.total;
                    }
                    _this.ajax('admin_' + table_name + '_table_list', ajax_data, function (data) {
                        _main.lits_data = data.lits_data || [];
                        _main.total = ~~(data.count || 0); //计数
                        _main.status_count = data.status_count || [];

                        if (data.statistics_data) {
                            _main.statistics_data = data.statistics_data;
                        }
                    });
                },

                timefilterFormat(time) {
                    if (!time || !Array.isArray(time) || time.length !== 2) {
                        return [];
                    }

                    let formatDate = function (date, format) {
                        if (!date) return '';

                        const d = new Date(date);
                        const year = d.getFullYear();
                        const month = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        const hours = String(d.getHours()).padStart(2, '0');
                        const minutes = String(d.getMinutes()).padStart(2, '0');
                        const seconds = String(d.getSeconds()).padStart(2, '0');

                        return format.replace('YYYY', year).replace('MM', month).replace('DD', day).replace('HH', hours).replace('mm', minutes).replace('ss', seconds);
                    };

                    let start_time = time[0] ? formatDate(time[0], 'YYYY-MM-DD 00:00:00') : '';
                    let end_time = time[1] ? formatDate(time[1], 'YYYY-MM-DD 23:59:59') : '';

                    return [start_time, end_time];
                },
                getDbTimefilter(timefilter) {
                    for (let key in timefilter) {
                        timefilter[key] = this.timefilterFormat(timefilter[key]);
                    }
                    return timefilter;
                },
                priceFormat(price, pay_modo) {
                    if (pay_modo === 'points') {
                        return ~~price + '积分';
                    }

                    return (
                        this.marks.pay +
                        Number(price)
                            .toFixed(2)
                            .toString()
                            .replace(/\.?0+$/, '')
                    );
                },
                priceCut(price, pay_modo) {
                    if (pay_modo === 'points' || price > 10000) {
                        price = ~~price;
                    }

                    price = Number(price)
                        .toFixed(2)
                        .toString()
                        .replace(/\.?0+$/, '');

                    //根据金额判断是否裁切到万
                    if (price > 100000) {
                        price =
                            (price / 10000)
                                .toFixed(2)
                                .toString()
                                .replace(/\.?0+$/, '') + 'w';
                    }

                    return price;
                },
                isExist(data) {
                    return !$.isEmptyObject(data);
                },
                copyAddress(address_data) {
                    var text = address_data.name + ' ' + address_data.phone + ' ' + address_data.province + address_data.city + address_data.county + address_data.address;
                    this.copy(text);
                },
                copy(text) {
                    var input = document.createElement('input'); // 直接构建input
                    input.value = text; // 设置内容
                    document.body.appendChild(input); // 添加临时实例

                    if (navigator.userAgent.match(/ipad|iphone/i)) {
                        var range = document.createRange();
                        range.selectNodeContents(input);
                        var selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        input.setSelectionRange(0, 999999);
                    } else {
                        input.select(); // 选择实例内容
                    }

                    var message = {
                        offset: 40,
                        type: 'danger',
                        message: '复制失败，请手动复制',
                        zIndex: 10000010,
                    };
                    try {
                        if (document.execCommand('Copy')) {
                            // 执行复制
                            message = {
                                offset: 40,
                                type: 'success',
                                message: '内容已复制',
                                zIndex: 10000010,
                            };
                        }
                    } catch (err) {}
                    ElementPlus.ElNotification(message);
                    document.body.removeChild(input); // 删除临时实例
                },
                closeOrder(row) {
                    row.status = -1;
                },
                clearOrder() {
                    if (!confirm('清理订单会删除2周前所有已关闭的订单，不可恢复！确认清理订单？\n备注：系统每个月会自动清理一次已关闭订单，您可以无需手动清理')) {
                        return;
                    }
                    var _this = this;
                    _this._ajax('admin_clear_order', {}, function () {
                        _this.dBRefresh('order');
                    });
                },
                dateShortcutsDate() {
                    return [
                        {
                            text: '最近7天',
                            value: (() => {
                                const end = new Date();
                                const start = new Date();
                                start.setDate(start.getDate() - 7);
                                return [start, end];
                            })(),
                        },
                        {
                            text: '本月',
                            value: (() => {
                                const end = new Date();
                                const start = new Date();
                                start.setDate(1);
                                return [start, end];
                            })(),
                        },
                        {
                            text: '今年',
                            value: (() => {
                                const end = new Date();
                                const start = new Date(new Date().getFullYear(), 0);
                                return [start, end];
                            })(),
                        },
                        {
                            text: '去年',
                            value: (() => {
                                const end = new Date(new Date().getFullYear(), 0);
                                end.setDate(end.getDate() - 1);
                                const start = new Date(new Date().getFullYear() - 1, 0);
                                return [start, end];
                            })(),
                        },
                        {
                            text: '最近三个月',
                            value: (() => {
                                const end = new Date();
                                const start = new Date();
                                start.setMonth(start.getMonth() - 3);
                                return [start, end];
                            })(),
                        },
                        {
                            text: '最近六个月',
                            value: (() => {
                                const end = new Date();
                                const start = new Date();
                                start.setMonth(start.getMonth() - 6);
                                return [start, end];
                            })(),
                        },
                    ];
                },
                _ajax(action, data, fun, error_fun) {
                    var _this = this;
                    data = data || {};
                    data.action = action;
                    data._wpnonce = _this._wpnonce;
                    $.ajax({
                        url: _this.ajax_url,
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function (response) {
                            var type, title, message;
                            if (response.msg) {
                                type = response.error ? 'error' : response.ys || 'success';
                                title = response.title || '';
                                message = response.msg;
                                ElementPlus.ElNotification({
                                    offset: 40,
                                    title: title,
                                    message: message,
                                    type: type,
                                    zIndex: 10000010,
                                });
                            }
                            _this.stopLoading(); //停止动画
                            fun && fun(response);
                        },
                        error: function (response) {
                            ElementPlus.ElNotification({
                                offset: 40,
                                title: '错误',
                                message: '请求失败，请根据浏览器开发者工具查看错误信息',
                                type: 'error',
                                zIndex: 10000010,
                            });
                            console.error('ajax请求错误 status:' + response.status + ' ' + response.statusText + ' responseText:' + response.responseText, response);
                            error_fun && error_fun(response);
                            _this.stopLoading(); //停止动画
                        },
                    });
                },
                stopLoading() {
                    var loading = this.loading;
                    for (let key in loading) {
                        loading[key] = false;
                    }
                },
                refreshModal(action) {
                    var link = $('<a href="javascript:;" data-toggle="RefreshModal" data-action="' + action + '" style="display: none;"></a>');
                    link.appendTo('body');
                    link.click().remove();
                },
            },
        };

        /**
         * 倒计时组件优化版
         * - 如果剩余时间大于24小时，仅显示“X天X小时”，且不循环更新
         * - 否则显示“mm:ss”，并每秒更新
         */
        const CountDown = {
            props: {
                endTime: {
                    type: [String, Number],
                    required: true,
                },
                format: {
                    type: String,
                    default: 'mm:ss',
                },
            },
            data() {
                return {
                    time: 0, // 剩余秒数
                    timer: null,
                    over24h: false, // 是否大于24小时
                };
            },
            computed: {
                formatTime() {
                    if (this.time <= 0) {
                        return '已结束';
                    }
                    // 超过24小时，显示“X天X小时”
                    if (this.over24h) {
                        const days = Math.floor(this.time / 86400);
                        const hours = Math.floor((this.time % 86400) / 3600);
                        return `${days}天${hours}小时`;
                    }
                    // 否则显示“mm:ss”
                    const minutes = Math.floor(this.time / 60);
                    const seconds = this.time % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },
            },
            methods: {
                getEndTime() {
                    // 如果是时间戳(数字)
                    if (typeof this.endTime === 'number') {
                        return this.endTime;
                    }
                    // 如果是日期字符串
                    const timestamp = Date.parse(this.endTime);
                    if (!isNaN(timestamp)) {
                        return timestamp;
                    }
                    // 如果是时间戳字符串
                    const numericTime = Number(this.endTime);
                    if (!isNaN(numericTime)) {
                        return numericTime;
                    }
                    return 0;
                },
                startTimer() {
                    if (this.timer) {
                        clearInterval(this.timer);
                    }
                    const endTime = this.getEndTime();
                    const now = new Date().getTime();
                    // 统一以秒为单位
                    this.time = Math.max(0, Math.floor((endTime - now) / 1000));
                    // 判断是否大于24小时
                    if (this.time > 86400) {
                        this.over24h = true;
                        // 超过24小时不需要定时器
                        return;
                    } else {
                        this.over24h = false;
                    }
                    if (this.time <= 0) {
                        this.$emit('timeup');
                        return;
                    }
                    // 小于24小时，启动定时器
                    this.timer = setInterval(() => {
                        this.time--;
                        if (this.time <= 0) {
                            clearInterval(this.timer);
                            this.$emit('timeup');
                        }
                    }, 1000);
                },
            },
            mounted() {
                this.startTimer();
            },
            beforeUnmount() {
                if (this.timer) {
                    clearInterval(this.timer);
                }
            },
            template: `
                <span class="countdown" :title="'剩余时间：'+formatTime">{{ formatTime }}</span>
            `,
        };

        Main.components = {
            CountDown,
        };

        const app = window.Vue.createApp(Main);
        const router = window.VueRouter.createRouter({
            history: window.VueRouter.createWebHashHistory(),
            routes: [],
        });
        app.component('v-chart', window.VueECharts);
        app.use(ElementPlus, {
            locale: window.ElementPlusLocaleZhCn,
        });
        app.use(router);
        app.mount('#zibpay_app');
    });
})(jQuery, window, document);
