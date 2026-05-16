<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-04-09 12:19:12
 * @LastEditTime : 2025-07-21 21:25:26
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class ZibExpress
{
    // 接口类型常量
    const API_TYPE_KUAIDI100 = 'kuaidi100';
    const API_TYPE_ALIYUN    = 'aliyun';
    const API_TYPE_KDNIAO    = 'kdniao';

    /**
     * 获取支持的快递公司列表
     *
     * @return array 快递公司列表
     */
    public static function getExpressList()
    {
        return array(
            'shunfeng'       => '顺丰速运',
            'yuantong'       => '圆通速递',
            'zhongtong'      => '中通快递',
            'shentong'       => '申通快递',
            'yunda'          => '韵达快递',
            'ems'            => 'EMS',
            'jd'             => '京东物流',
            'tiantian'       => '天天快递',
            'debangwuliu'    => '德邦物流',
            'youzhengguonei' => '邮政包裹',
            'baishiwuliu'    => '百世快递',
            'jitu'           => '极兔速递',
            'youshuwuliu'    => '优速物流',
            'annengwuliu'    => '安能物流',
            'zhaijisong'     => '宅急送',
            // 可根据需要添加更多快递公司
        );
    }

    /**
     * 查询快递物流信息
     *
     * @param string $expressNumber 快递单号
     * @param string $phone 收件人或寄件人手机号码，部分快递需要
     * @param string $expressCompany 快递公司代码
     * @param string $apiType 使用的API类型，默认根据配置选择
     * @return array 查询结果
     */
    public static function query($expressNumber, $phone = '', $expressCompany = '', $apiType = '')
    {
        if (empty($expressNumber)) {
            return array('error' => 1, 'msg' => '快递单号不能为空');
        }

        // 如果未指定API类型，则使用配置中的默认API
        if (empty($apiType)) {
            $apiType = _pz('express_api_sdk', self::API_TYPE_KUAIDI100);
        }

        // 根据API类型调用不同的查询方法
        switch ($apiType) {
            case self::API_TYPE_KUAIDI100:
                $result = self::queryByKuaidi100($expressNumber, $phone, $expressCompany);
                break;
            case self::API_TYPE_ALIYUN:
                $result = self::queryByAliyun($expressNumber, $phone, $expressCompany);
                break;
            case self::API_TYPE_KDNIAO:
                $result = self::queryByKdniao($expressNumber, $phone, $expressCompany);
                break;
            default:
                return array('error' => 1, 'msg' => '不支持的API类型');
        }

        // 统一化处理返回结果
        return self::normalizeResult($result);
    }

    /**
     * 使用快递100接口查询
     *
     * @param string $expressNumber 快递单号
     * @param string $expressCompany 快递公司代码
     * @param string $phone 收件人或寄件人手机号码，部分快递需要
     * @return array 查询结果
     */
    private static function queryByKuaidi100($expressNumber, $phone = '', $expressCompany = '')
    {
        // 获取快递100配置
        $opt      = _pz('express_kuaidi100_opt');
        $customer = $opt['customer'] ?? '';
        $key      = $opt['key'] ?? '';

        if (empty($key) || empty($customer)) {
            return array('error' => 1, 'msg' => '快递100接口配置错误');
        }

        // 请求参数
        $param = array(
            //   'com' => $expressCompany,
            'num' => $expressNumber,
        );

        // 添加手机号参数（如果提供）
        if (!empty($phone)) {
            $param['phone'] = $phone;
        }

        $post_data             = array();
        $post_data['customer'] = $customer;
        $post_data['param']    = json_encode($param);
        $sign                  = md5($post_data['param'] . $key . $post_data['customer']);
        $post_data['sign']     = strtoupper($sign);

        // 发送请求
        $url      = 'https://poll.kuaidi100.com/poll/query.do';
        $response = wp_remote_post($url, array(
            'method'  => 'POST',
            'timeout' => 15,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'body'    => $post_data,
        ));

        if (is_wp_error($response)) {
            return array('error' => 1, 'msg' => '网络请求失败：' . $response->get_error_message());
        }

        $body   = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (isset($result['status']) && $result['status'] == '200') {
            $state_text = array(
                '0'  => '运输中',
                '1'  => '已揽收',
                '2'  => '疑难异常',
                '3'  => '已签收',
                '4'  => '退件签收',
                '5'  => '派送中',
                '6'  => '已退回',
                '7'  => '运转中',
                '8'  => '清关',
                '14' => '拒收',
            );
            $state_key = $result['state'] ?? $result['State'] ?? 0;

            $state = $state_text[$state_key] ?? '';

            return array(
                'error'         => 0,
                'sdk'           => self::API_TYPE_KUAIDI100,
                'company_code'  => $result['com'] ?? '',
                'company_name'  => '',
                'company_phone' => '',
                'update_time'   => $result['data'][0]['ftime'] ?? '',
                'state'         => $state,
                'signed'        => $state_key == '3',
                'traces'        => $result['data'] ?? array(),
            );
        } else {
            return array(
                'error' => 1,
                'sdk'   => self::API_TYPE_KUAIDI100,
                'msg'   => isset($result['message']) ? $result['message'] : '查询失败或配置错误',
            );
        }
    }

    /**
     * 使用阿里云接口查询
     *
     * @param string $expressNumber 快递单号
     * @param string $phone 收件人或寄件人手机号码，部分快递需要
     * @return array 查询结果
     */
    private static function queryByAliyun($expressNumber, $phone = '', $expressCompany = '')
    {
        // 获取阿里云配置
        $appcode = _pz('express_aliyun_opt')['appcode'] ?? '';

        if (empty($appcode)) {
            return array('error' => 1, 'msg' => '阿里云接口配置错误');
        }

        // 请求参数
        $url    = 'https://wdexpress.market.alicloudapi.com/gxali';
        $params = array(
            'n' => $expressNumber,
        );

        // 添加手机号参数，取手机号后四位
        if (!empty($phone)) {
            $params['n'] .= ':' . substr($phone, -4);
        }

        // 发送请求
        $response = wp_remote_get(add_query_arg($params, $url), array(
            'timeout' => 15,
            'headers' => array('Authorization' => 'APPCODE ' . $appcode),
        ));

        if (is_wp_error($response)) {
            return array('error' => 1, 'msg' => '网络请求失败：' . $response->get_error_message());
        }

        $body   = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (!empty($result['Success'])) {
            $state_text = array(
                '-1' => '单号或代码错误',
                '0'  => '暂无轨迹',
                '1'  => '已揽收',
                '2'  => '运输中',
                '3'  => '已签收',
                '4'  => '问题件',
                '5'  => '疑难件',
                '6'  => '退件签收',
            );

            $state = $state_text[$result['State']] ?? '';

            return array(
                'error'         => 0,
                'sdk'           => self::API_TYPE_ALIYUN,
                'company_code'  => $result['ShipperCode'] ?? '',
                'company_name'  => $result['Name'] ?? '',
                'company_phone' => !empty($result['CourierPhone']) ? $result['CourierPhone'] : (!empty($result['Phone']) ? $result['Phone'] : ''),
                'update_time'   => $result['updateTime'] ?? '',
                'state'         => $state,
                'signed'        => !empty($result['State']) && $result['State'] == '3',
                'traces'        => $result['Traces'] ?? array(),
            );
        } else {
            return array(
                'error'    => 1,
                'sdk'      => self::API_TYPE_ALIYUN,
                'msg'      => isset($result['Reason']) ? $result['Reason'] : 'APPCODE错误',
                'raw_data' => $result,
            );
        }
    }

    /**
     * 使用快递鸟接口查询
     *
     * @param string $expressNumber 快递单号
     * @param string $expressCompany 快递公司代码
     * @param string $phone 收件人或寄件人手机号码，部分快递需要
     * @return array 查询结果
     */
    private static function queryByKdniao($expressNumber, $phone = '', $expressCompany = '')
    {
        // 获取快递鸟配置
        $opt         = _pz('express_kdniao_opt');
        $EBusinessID = $opt['appid'] ?? '';
        $ApiKey      = $opt['apikey'] ?? '';

        if (empty($EBusinessID) || empty($ApiKey)) {
            return array('error' => 1, 'msg' => '快递鸟接口配置不完整');
        }

        // 请求参数
        $requestData = array(
            'LogisticCode' => $expressNumber,
            'Sort'         => 1, // 0:升序，1:降序
        );

        // 添加手机号参数，取手机号后四位
        if (!empty($phone)) {
            $requestData['CustomerName'] = substr($phone, -4);
        }

        $requestData = json_encode($requestData);

        // 签名生成
        $dataSign = urlencode(base64_encode(md5($requestData . $ApiKey)));

        $post_data = array(
            'EBusinessID' => $EBusinessID,
            'RequestType' => '8002', // 即时查询接口
            'RequestData' => urlencode($requestData),
            'DataType'    => '2', // 返回JSON格式
            'DataSign'    => $dataSign,
        );

        // 发送请求
        $url      = 'https://api.kdniao.com/api/dist';
        $response = wp_remote_post($url, array(
            'method'  => 'POST',
            'timeout' => 15,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
            'body'    => $post_data,
        ));

        if (is_wp_error($response)) {
            return array('error' => 1, 'msg' => '网络请求失败：' . $response->get_error_message());
        }

        $body   = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (isset($result['Success']) && $result['Success']) {

            $state_text = array(
                '0' => '暂无轨迹',
                '1' => '已揽收',
                '2' => '运输中',
                '3' => '已签收',
                '4' => '问题件',
                '5' => '转寄',
                '6' => '清关',
            );

            $state = $state_text[$result['State']] ?? '';

            if (!empty($result['StateEx'])) {
                if ($result['StateEx'] === '202') {
                    $state = '派送中';
                }
            }

            return array(
                'error'         => 0,
                'sdk'           => self::API_TYPE_KDNIAO,
                'company_code'  => $result['ShipperCode'] ?? '',
                'company_name'  => $result['Name'] ?? '',
                'company_phone' => !empty($result['CourierPhone']) ? $result['CourierPhone'] : (!empty($result['Phone']) ? $result['Phone'] : ''),
                'update_time'   => !empty($result['Traces'][0]['AcceptTime']) ? date('Y-m-d H:i:s', strtotime($result['Traces'][0]['AcceptTime'])) : '',
                'state'         => $state,
                'signed'        => !empty($result['State']) && $result['State'] == '3',
                'traces'        => $result['Traces'] ?? array(),
            );
        } else {
            return array(
                'error' => 1,
                'sdk'   => self::API_TYPE_KDNIAO,
                'msg'   => isset($result['Reason']) ? $result['Reason'] : '查询失败',
            );
        }
    }

    /**
     * 统一化处理不同接口返回的数据格式
     *
     * @param array $result 原始查询结果
     * @return array 统一格式的结果
     */
    private static function normalizeResult($result)
    {
        // 如果查询出错，直接返回错误信息
        if (!empty($result['error'])) {
            return $result;
        }

        $normalized = array(
            'error'       => 0,
            //  'sdk'         => $result['sdk'] ?? '',
            //  'company_code'  => $result['company_code'] ?? '',
            //  'company_name'  => $result['company_name'] ?? '',
            //  'company_phone' => $result['company_phone'] ?? '',
            'signed'      => !empty($result['signed']),
            'update_time' => $result['update_time'] ?? '',
            'query_time'  => current_time('Y-m-d H:i:s'),
            'state'       => $result['state'] ?? '',
            'traces'      => array(),
        );

        // 处理物流轨迹信息
        if (!empty($result['traces'])) {
            $traces = array();
            foreach ($result['traces'] as $item) {

                // 根据API类型调用不同的查询方法
                switch ($result['sdk']) {
                    case self::API_TYPE_KUAIDI100:
                        $traces[] = array(
                            'time'    => isset($item['ftime']) ? $item['ftime'] : '',
                            'context' => isset($item['context']) ? $item['context'] : '',
                        );
                        break;
                    case self::API_TYPE_ALIYUN:
                        $traces[] = array(
                            'time'    => isset($item['AcceptTime']) ? $item['AcceptTime'] : '',
                            'context' => isset($item['AcceptStation']) ? $item['AcceptStation'] : '',
                        );
                        break;
                    case self::API_TYPE_KDNIAO:
                        $traces[] = array(
                            'time'    => isset($item['AcceptTime']) ? date('Y-m-d H:i:s', strtotime($item['AcceptTime'])) : '',
                            'context' => isset($item['AcceptStation']) ? $item['AcceptStation'] : '',
                        );
                        break;
                }
            }

            // 按时间降序排序
            usort($traces, function ($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });

            //添加state
            if (in_array($result['state'], array('已揽收', '已签收', '运输中', '派送中')) && isset($traces[0])) {
                $traces[0]['state'] = $result['state'];
            }

            $normalized['traces'] = $traces;
        }

        return $normalized;
    }

    /**
     * 获取快递公司名称
     *
     * @param string $code 快递公司代码
     * @return string 快递公司名称
     */
    public static function getCompanyName($code)
    {
        $companies = self::getExpressList();
        return isset($companies[$code]) ? $companies[$code] : '未知快递';
    }

}
