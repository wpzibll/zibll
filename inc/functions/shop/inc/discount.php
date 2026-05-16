<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2021-08-05 20:25:29
 * @LastEditTime : 2025-12-23 18:47:58
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题|商城系统|商品页面函数
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

/**
 * 获取商品的全部优惠活动
 * @param int $post_id 商品ID
 * @param bool $is_valid 是否只获取对当前商品有效的优惠活动
 * @return array 返回优惠活动数据
 */
function zib_shop_get_product_discount(int $post_id, $is_valid = true)
{

    $discount = get_the_terms($post_id, 'shop_discount');
    $data     = [
        'all'   => [],
        'valid' => [],
    ];
    if ($discount) {
        foreach ($discount as $item) {
            $discount_data = zib_shop_get_discount_data($item);
            $data['all'][] = $discount_data;
            if ($discount_data['is_valid']) {
                $data['valid'][] = $discount_data;
            }
        }
    }

    return $is_valid ? zib_shop_discount_sort($data['valid']) : zib_shop_discount_sort($data['all']);
}

//获取商品的重点活动
function zib_shop_get_product_important_discount(int $post_id, $is_valid = true)
{
    $discount = zib_shop_get_product_discount($post_id, $is_valid);

    //重点活动只取一个
    foreach ($discount as $item) {
        if ($item['is_important'] && $item['is_valid']) {
            return $item;
        }
    }

    return [];
}

/**
 * 按优先级或ID大小排序优惠政策
 *
 * @param array $discounts 优惠政策数组
 * @return array 排序后的优惠政策数组
 */
function zib_shop_discount_sort($discounts)
{
    if (!is_array($discounts) || empty($discounts)) {
        return $discounts;
    }

    // 使用 usort 函数进行自定义排序
    usort($discounts, function ($a, $b) {
        // 首先按照 priority 排序
        $priority_a = isset($a['priority']) ? intval($a['priority']) : 0;
        $priority_b = isset($b['priority']) ? intval($b['priority']) : 0;

        // 如果优先级不同，直接返回比较结果
        if ($priority_a !== $priority_b) {
            return $priority_a - $priority_b; // 数值小的排在前面
        }

        // 如果优先级相同，则按照 id 排序
        $id_a = isset($a['id']) ? intval($a['id']) : 0;
        $id_b = isset($b['id']) ? intval($b['id']) : 0;

        return $id_a - $id_b; // 数值小的排在前面
    });

    return $discounts;
}

/**
 * 判断优惠是否命中价格限制
 * @param array $discount_data 优惠数据，必须包含price_limit和discount_scope
 * @param array $dependency_data 依赖数据
 * 格式为[
 *  'item_data' => ['price' => 0, 'count' => 0],
 *  'product_data' => ['price' => 0, 'count' => 0],
 *  'author_data' => ['price' => 0, 'count' => 0],
 *  'total_data' => ['price' => 0, 'count' => 0],
 * ]
 * @return bool 是否命中价格限制
 */
function zib_shop_discount_price_limit_check(array $discount_data, array $dependency_data)
{
    $discount_price_limit = !empty($discount_data['price_limit']) ? (float) $discount_data['price_limit'] : 0;
    $discount_scope       = !empty($discount_data['discount_scope']) ? $discount_data['discount_scope'] : 'item';
    $item_price           = !empty($dependency_data['item_data']['price']) ? (float) $dependency_data['item_data']['price'] : 0;

    if (!$discount_price_limit) {
        return true;
    }

    switch ($discount_scope) {
        case 'item':
            return $item_price >= $discount_price_limit;
        case 'product':
            return $dependency_data['product_data']['price'] >= $discount_price_limit;
        case 'author':
            return $dependency_data['author_data']['price'] >= $discount_price_limit;
        case 'order':
            return $dependency_data['total_data']['price'] >= $discount_price_limit;
    }

    return true;
}

/**
 * 判断优惠是否命中用户身份限制
 * @param array $discount_data 优惠数据，必须包含user_limit
 * @param array $_user_data 用户数据，格式为[
 *  'vip_level' => 0,
 *  'auth' => false,
 * ]
 * @return bool 是否命中用户身份限制
 */
function zib_shop_discount_user_limit_check($discount_data, $_user_data)
{
    if (empty($discount_data['user_limit'])) {
        return true;
    }

    switch ($discount_data['user_limit']) {
        case 'vip':
            return $_user_data['vip_level'] >= 1;
        case 'vip_2':
            return $_user_data['vip_level'] >= 2;
        case 'auth':
            return $_user_data['auth'];
    }

    return true;
}

/**
 * 计算价格的优惠价，注意此函数不执行判断，仅仅计算优惠价
 *  @param array  $discount_item_args discount数据
 *  @param array  $discount_dependency 依赖数据
 *  $discount_dependency 格式为：
 *   格式为[
 *      'item_data'    => ['price' => 0, 'count' => 0],
 *      'product_data' => ['price' => 0, 'count' => 0],
 *      'author_data'  => ['price' => 0, 'count' => 0],
 *      'total_data'   => ['price' => 0, 'count' => 0],
 *      'user_data'    => ['vip_level' => 0, 'auth' => false],
 *  ]
 *  @param mixed  $item_price 商品价格
 *  @param int    $item_count 商品数量
 *  @param array   $is_limit_check 是否检查优惠限制，默认不检查：['price', 'user']
 *  @return array 返回计算结果
 *  array(
 *      'usesed_count' => '',
 *      'preferential_amount' => '',
 *      'price' => '',
 *  )
 */
function zib_shop_discount_price_calculate(array $discount_item_args, array $discount_dependency, $item_price, $item_count = 1, $is_limit_check = [])
{
    $result = [
        'usesed_count'        => 0,
        'preferential_amount' => 0,
        'price'               => $item_price,
    ];

    if (in_array('price', $is_limit_check)) {
        if (!zib_shop_discount_price_limit_check($discount_item_args, $discount_dependency)) {
            return $result;
        }
    }

    if (in_array('user', $is_limit_check)) {
        if (!zib_shop_discount_user_limit_check($discount_item_args, $discount_dependency['user_data'])) {
            return $result;
        }
    }

    $discount_scope = $discount_item_args['discount_scope'] ?? 'item';
    if ($discount_item_args['discount_type'] === 'reduction') {
        $_reduction_amount      = (float) $discount_item_args['reduction_amount'];
        $reduction_amount       = 0;
        $result['usesed_count'] = 1;

        if ($discount_scope === 'item') {
            $reduction_amount       = $_reduction_amount * $item_count;
            $result['usesed_count'] = $item_count;
        }
        if ($discount_scope === 'product') {
            $reduction_amount = $_reduction_amount * ($item_count / (!empty($discount_dependency['product_data']['count']) ? $discount_dependency['product_data']['count'] : 1));
        }
        if ($discount_scope === 'author') {
            $reduction_amount = $_reduction_amount * ($item_count / (!empty($discount_dependency['author_data']['count']) ? $discount_dependency['author_data']['count'] : 1));
        }
        if ($discount_scope === 'order') {
            $reduction_amount = $_reduction_amount * ($item_count / (!empty($discount_dependency['total_data']['count']) ? $discount_dependency['total_data']['count'] : 1));
        }

        if ($reduction_amount >= $item_price) {
            //优惠金额不能大于总金额，也就是说金额不能为负数
            $reduction_amount = $item_price;
        }

        $result['preferential_amount'] = $reduction_amount; //优惠的金额
        $item_price -= $reduction_amount;
    }

    if ($discount_item_args['discount_type'] === 'discount') {
        $_old_price = $item_price; //原总价记录一下

        $discount = (float) $discount_item_args['discount_amount'] / 10;
        $item_price *= $discount;

        $result['usesed_count']        = 1;
        $result['preferential_amount'] = $_old_price - $item_price; //优惠金额
    }

    return $result;
}

//获取一个优惠活动数据
function zib_shop_get_discount_data($discount)
{

    $data = [];
    if (!is_object($discount)) {
        $discount = get_term($discount, 'shop_discount');
    }

    if (!isset($discount->term_id)) {
        return $data;
    }

    //静态变量
    static $discount_config = [];
    if (!empty($discount_config[$discount->term_id])) {
        return $discount_config[$discount->term_id];
    }

    $product_discount_config = zib_shop_get_discount_config($discount->term_id) ?: [];
    $data                    = array_merge([
        'id'             => $discount->term_id,
        'name'           => $discount->name,
        'small_badge'    => !empty($product_discount_config['small_badge']) ? $product_discount_config['small_badge'] : $discount->name,
        'desc'           => $discount->description,
        'link'           => get_term_link($discount),
        'price_limit'    => $product_discount_config['price_limit'] ?? 0,
        'user_limit'     => $product_discount_config['user_limit'] ?? 0,
        'time_limit'     => $product_discount_config['time_limit'] ?? 0,
        'priority'       => $product_discount_config['priority'] ?? 50,
        'discount_scope' => $product_discount_config['discount_scope'] ?? 'item',
    ], zib_shop_get_discount_policy($product_discount_config));

    //缓存优惠活动数据
    $discount_config[$discount->term_id] = $data;
    return $data;
}

/**
 * 获取优惠活动数据，会先判断是否有效
 * @param mixed $data_or_id 优惠活动ID或优惠活动数据
 * @return array 返回优惠活动数据，会先判断是否有效
 */
function zib_shop_get_discount_policy($data_or_id)
{

    $data = [
        'is_valid'      => true,
        'discount_type' => '',
    ];
    $discount_config = $data_or_id;
    if (!is_array($data_or_id)) {
        $discount_config = zib_shop_get_discount_config($data_or_id) ?: [];
    }

    if (empty($discount_config['discount_type'])) {
        $data['is_valid']           = false;
        $data['discount_error']     = 'config_error';
        $data['discount_error_msg'] = '配置无效';
        return $data;
    }

    $data['discount_type'] = $discount_config['discount_type'];
    // 判断优惠类型
    switch ($data['discount_type']) {
        case 'reduction':
            $data['reduction_amount'] = (float) $discount_config['reduction_amount'];
            if ($data['reduction_amount'] <= 0) {
                $data['is_valid']           = false;
                $data['discount_error']     = 'config_error';
                $data['discount_error_msg'] = '立减金额必须大于0';

                return $data;
            }
            break;

        case 'discount':
            $data['discount_amount'] = (float) $discount_config['discount_amount'];
            if ($data['discount_amount'] < 0.01 || $data['discount_amount'] > 9.99) {
                $data['is_valid']           = false;
                $data['discount_error']     = 'config_error';
                $data['discount_error_msg'] = '折扣必须在0.01-9.99之间';
                return $data;
            }
            break;

        case 'gift':
            $data['gift_config'] = zib_shop_filter_discount_gift_config($discount_config['gift_config']);

            if (empty($data['gift_config'][0])) {
                $data['is_valid']           = false;
                $data['discount_error']     = 'config_error';
                $data['discount_error_msg'] = '赠品配置无效';
                return $data;
            }

            break;
    }

    // 判断时间限制
    if (!empty($discount_config['time_limit'])) {
        //开始时间
        $start           = $discount_config['time_limit_config']['start'] ?? '';
        $start_timestamp = $start ? strtotime($start) : 0;
        //结束时间
        $end           = $discount_config['time_limit_config']['end'] ?? '';
        $end_timestamp = $end ? strtotime($end) : 0;

        //当前时间
        $current_time = strtotime(current_time('Y-m-d H:i:s'));

        //判断是否在开始时间之前
        if ($start_timestamp && $current_time < $start_timestamp) {
            $data['is_valid']           = false;
            $data['discount_error']     = 'time_limit_start';
            $data['start_time']         = $start;
            $data['discount_error_msg'] = '活动未开始';
            return $data;
        }

        //判断是否已过结束时间
        if ($end_timestamp && $current_time > $end_timestamp) {
            $data['is_valid']           = false;
            $data['discount_error']     = 'time_limit_end';
            $data['end_time']           = $end;
            $data['discount_error_msg'] = '活动已结束';
            return $data;
        }

        $data['time_limit']                                  = true;
        $data['time_limit_config']                           = $discount_config['time_limit_config'] ?? [];
        $data['time_limit_config']['start_timestamp']        = $start_timestamp;
        $data['time_limit_config']['end_timestamp']          = $end_timestamp;
        $data['time_limit_config']['current_time']           = date('Y-m-d H:i:s', $current_time);
        $data['time_limit_config']['current_time_timestamp'] = $current_time;
        $data['time_limit_config']['countdown']              = !empty($discount_config['time_limit_config']['countdown']) && $end_timestamp;
    } else {
        $data['time_limit'] = false;
    }

    //判断重点活动
    $data['is_important'] = !empty($discount_config['is_important']);
    if ($data['is_important']) {
        $data['important_class'] = $discount_config['important_class'] ?? 'jb-red';
    }

    return $data;
}

//筛选优惠活动中有效的买赠配置，避免出现错误配置
function zib_shop_filter_discount_gift_config($gift_config)
{
    $valid_gift_config = [];
    $has_vip1          = false;
    $has_vip2          = false;
    foreach ($gift_config as $item) {
        $gift_type = $item['gift_type'];
        switch ($gift_type) {
            case 'vip_1':
                if (_pz('pay_user_vip_1_s', true) && (is_numeric($item['vip_time']) || $item['vip_time'] == 'Permanent' || $item['vip_time'] == 'permanent')) {
                    $valid_gift_config[] = [
                        'vip_1_name' => _pz('pay_user_vip_1_name', 'VIP'),
                        'vip_time'   => $item['vip_time'],
                        'gift_type'  => $gift_type,
                        'desc'       => (($item['vip_time'] == 'Permanent' || $item['vip_time'] == 'permanent') ? '永久' : $item['vip_time'] . '天') . _pz('pay_user_vip_1_name', 'VIP'),
                    ];
                    $has_vip1 = true;
                }
                break;
            case 'vip_2':
                if (_pz('pay_user_vip_2_s', true) && (is_numeric($item['vip_time']) || $item['vip_time'] == 'Permanent' || $item['vip_time'] == 'permanent')) {
                    $valid_gift_config[] = [
                        'vip_2_name' => _pz('pay_user_vip_2_name', 'VIP2'),
                        'vip_time'   => $item['vip_time'],
                        'gift_type'  => $gift_type,
                        'desc'       => (($item['vip_time'] == 'Permanent' || $item['vip_time'] == 'permanent') ? '永久' : $item['vip_time'] . '天') . _pz('pay_user_vip_2_name', 'VIP2'),
                    ];
                    $has_vip2 = true;
                }
                break;
            case 'auth':
                if (_pz('user_auth_s', true) && !empty($item['auth_info']['name'])) {
                    $valid_gift_config[] = [
                        'gift_type' => $gift_type,
                        'auth_info' => $item['auth_info'],
                        'desc'      => $item['auth_info']['name'] . '认证',
                    ];
                }
                break;
            case 'level_integral':
                if (_pz('user_level_s', true) && (int) $item['level_integral'] > 0) {
                    $valid_gift_config[] = [
                        'gift_type'      => $gift_type,
                        'level_integral' => (int) $item['level_integral'],
                        'desc'           => (int) $item['level_integral'] . '经验值',
                    ];
                }
                break;
            case 'points':
                if (_pz('points_s', true) && (int) $item['points'] > 0) {
                    $valid_gift_config[] = [
                        'gift_type' => $gift_type,
                        'points'    => (int) $item['points'],
                        'desc'      => (int) $item['points'] . '积分',
                    ];
                }
                break;
            case 'product':
                if ((int) $item['product_id'] > 0) {
                    $valid_gift_config[] = [
                        'gift_type'  => $gift_type,
                        'product_id' => (int) $item['product_id'],
                    ];
                }
            case 'other':
                if (!empty($item['other_info']['name'])) {
                    $valid_gift_config[] = [
                        'gift_type'  => $gift_type,
                        'other_info' => $item['other_info'],
                        'desc'       => $item['other_info']['name'],
                    ];
                }
                break;
        }
    }

    if ($has_vip1 && $has_vip2) {
        //移出vip1
        $valid_gift_config = array_filter($valid_gift_config, function ($item) {
            return $item['gift_type'] !== 'vip_1';
        });
    }

    return $valid_gift_config;
}

//获取赠品类型的名称
function zib_shop_get_gift_type_name($gift_data)
{
    switch ($gift_data['gift_type']) {
        case 'vip_1':
            return _pz('pay_user_vip_1_name', 'VIP');
            break;
        case 'vip_2':
            return _pz('pay_user_vip_2_name', 'VIP2');
            break;
        case 'auth':
            return '认证资格';
            break;
        case 'level_integral':
            return '经验值';
            break;
        case 'points':
            return '积分';
            break;
        case 'product':
            return '商品';
            break;
        case 'other':
            return $gift_data['other_info']['name'];
            break;
    }
}

//获取折扣的配置
function zib_shop_get_discount_config($discount_id, $key = null, $default = '')
{

    return zib_shop_get_term_config('shop_discount', $discount_id, $key, $default);
}
