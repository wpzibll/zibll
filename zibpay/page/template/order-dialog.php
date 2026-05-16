<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-21 15:27:57
 * @LastEditTime : 2026-04-29 22:25:24
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

$vue_data = array(
    'order_dialog_data'          => array(
        'order_id' => '',
    ),
    //赠品
    'gift_dialog_data'           => array(
        'gift_id' => '',
    ),

    //优惠券
    'discount_dialog_data'       => [],

    'express_dialog_data'        => [
        'title'        => '物流信息',
        'show'         => 0,
        'address_data' => [],
        'express_data' => [],
    ],

    'payment_dialog_data'        => [
        'show'      => 0,
        'pay_price' => 0,
        'pay_modo'  => '￥',
    ],

    'payment_methods_names'      => [
        'wechat'  => '微信',
        'alipay'  => '支付宝',
        'paypal'  => 'PayPal',
        'balance' => '余额',
        'other'   => '其他',
    ],
    'admin_payment_submit_nonce' => wp_create_nonce('admin_payment_submit'),
);

zibpay_admin_page_vue_data_filter($vue_data);

?>

<el-drawer
    v-model="express_dialog_data.show"
    :title="express_dialog_data.title"
    direction="rtl"
    :size="win.width>640 ? '600px' : '100%'"
    :destroy-on-close="true" z-index="100030">
    <div v-loading="loading.express_dialog">
        <div v-if="express_dialog_data.express_data.traces">
            <div v-if="express_dialog_data.address_data.name" class="mb20 text-box">
                <div class="flex">
                    <div class="opacity8 mr20 flex0">收件人</div>
                    <div class="flex1">
                        <div class="flex ac">
                            <b>{{ express_dialog_data.address_data.name }}</b>
                            <div class="ml10">{{ express_dialog_data.address_data.phone }}</div>
                        </div>
                        <div class="mt6">{{ express_dialog_data.address_data.province + express_dialog_data.address_data.city + express_dialog_data.address_data.county + express_dialog_data.address_data.address }}</div>
                    </div>
                    <el-button type="primary" plain size="small" @click="copyAddress(express_dialog_data.address_data)">复制地址</el-button>
                </div>
            </div>
            <el-timeline>
                <el-timeline-item
                    v-for="(item, index) in express_dialog_data.express_data.traces"
                    :key="index"
                    :timestamp="item.time"
                    :type="index === 0 ? 'success' : ''">
                    {{ item.context }}
                </el-timeline-item>
            </el-timeline>
        </div>
        <div v-else class="flex jc ac" style="height: 100%;">
            <el-empty description="暂无物流信息"></el-empty>
        </div>
    </div>
</el-drawer>


<el-dialog
    v-model="payment_dialog_data.show"
    title="手动补单"
    :width="win.width>640 ? '600px' : '100%'">
    <el-form v-if="[-1,0].includes(~~payment_dialog_data.status)" class="order-edit-form" :model="payment_dialog_data"
        :label-position="win.width>580 ? 'right' : 'top'" :label-width="win.width>580 ? '130px' : ''">
        <div class="text-box mb10">
            <div class="flex ac">
                <el-avatar shape="square" :size="60" :src="payment_dialog_data.product_info.thumbnail || ''" class="mr10"></el-avatar>
                <div class="flex1 mr10 overflow-hidden">
                    <div class="mb6 font-bold"><span class="el-text is-truncated">{{ payment_dialog_data.product_info.title }}</span></div>
                    <div class="el-text el-text--info is-truncated">{{ payment_dialog_data.product_info.opt_name }}</div>
                </div>
                <div class="text-right flex0">
                    <div class="mb6 font-bold">x{{ payment_dialog_data.order_data.count }}</div>
                    <div class="font-bold c-red">{{ payment_dialog_data.order_data.prices.pay_price }}</div>
                </div>
            </div>
            <div class="flex ac jsb mt20">
                <div class="opacity5 mr10 flex0">订单号：</div>
                <div>{{payment_dialog_data.order_num}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">下单时间：</div>
                <div>{{payment_dialog_data.create_time}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">购买用户：</div>
                <div>{{payment_dialog_data.user_info.name || '未知'}}</div>
            </div>
            <div class="flex ac jsb mt6" v-if="payment_dialog_data.order_data.remark">
                <div class="opacity5 mr10 flex0">下单备注：</div>
                <div>{{payment_dialog_data.order_data.remark || '无'}}</div>
            </div>
            <div class="flex jsb mt6 pointer" v-if="isExist(payment_dialog_data.order_data.gift_data)" @click="refreshModal('order_gift_modal&order_id='+payment_dialog_data.id)">
                <div class="opacity5 mr20 flex0">赠品：</div>
                <div class="flex ac">
                    <div>
                        <span v-for="(gift_item, index) in payment_dialog_data.order_data.gift_data" :key="gift_item.desc + '_shipping_details'">
                            {{index > 0 ? '、' : ''}}{{gift_item.desc}}
                        </span>
                    </div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>
        </div>
        <el-form-item label="支付方式">
            <el-select v-model="payment_dialog_data.payment_method" placeholder="请选择支付方式">
                <el-option v-for="(name, key) in payment_methods_names" :key="key" :label="name" :value="key"></el-option>
            </el-select>
        </el-form-item>
        <el-form-item label="支付金额">
            <el-input v-model="payment_dialog_data.pay_price" placeholder="请输入支付金额"></el-input>
        </el-form-item>
        <el-form-item label="支付订单号">
            <el-input v-model="payment_dialog_data.pay_num" placeholder="请输入支付订单号"></el-input>
        </el-form-item>
        <el-form-item>
            <el-button type="primary" :loading="loading.payment_dialog_submit_but" :disabled="!payment_dialog_data.payment_method || !payment_dialog_data.pay_price || !payment_dialog_data.pay_num" @click="paymentSubmit">确认提交</el-button>
        </el-form-item>

    </el-form>
    <div v-else class="flex jc ac" style="height: 100%;">
        <el-empty description="当前订单无法手动支付"></el-empty>
    </div>
</el-dialog>
