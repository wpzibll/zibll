<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-12-12 22:59:02
 * @LastEditTime : 2025-12-29 19:08:54
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

namespace Zib\Meilisearch;

class Client
{

    public $url    = null;
    public $apiKey = null;
    public $http   = null;
    public function __construct($url, $apiKey = null)
    {
        $this->url    = $url;
        $this->apiKey = $apiKey;

        $this->http = new ZibMeilisearchHttp($url, $apiKey);
    }

    public function index($uid)
    {
        return new ZibMeilisearchIndex($this->http, $uid);
    }

    public function updateIndex($uid, $data = ['primaryKey' => 'id'])
    {
        $_data = $this->http->patch('/indexes/' . $uid, $data);
    }

    public function stats()
    {
        return $this->http->get('/stats');
    }

    public function version()
    {
        $_data = $this->http->get('/version')->toArray();
        return $_data['pkgVersion'] ?? 'null';
    }

    public function getTasks($tasks_id = null)
    {
        $_data = $this->http->get('/tasks' . ($tasks_id ? '/' . $tasks_id : ''))->toArray();
        return $_data;
    }

    public function multiSearch(array $queries, array $federation = [])
    {
        $params = [
            'queries' => $queries,
        ];
        if ($federation) {
            $params['federation'] = $federation;
        }

        return $this->http->post('/multi-search', $params);
    }

}

class ZibMeilisearchIndex
{

    public $http        = null;
    public $uid         = null;
    public $path_prefix = 'indexes'; //前缀
    public $path        = '';

    public function __construct($http, $uid)
    {
        $this->http = $http;
        $this->uid  = $uid;

        $this->path = $this->path_prefix . '/' . $this->uid;
    }

    //更新索引主键
    public function updatePrimaryKey($pk = 'id')
    {
        return $this->http->patch($this->path, ['primaryKey' => $pk]);
    }

    //搜索
    public function search($key, array $options = [])
    {
        $params = [
            'q' => $key,
        ];

        $params = array_merge($options, $params);
        return $this->http->post($this->path . '/search', $params);
    }

    public function facetSearch($key, $FacetName, array $options = [])
    {

        $params = [
            'q'         => $key,
            'facetName' => $FacetName,
        ];
        $params = array_merge($options, $params);
        return $this->http->post($this->path . '/facet-search', $params);
    }

    public function addDocuments(array $data)
    {
        return $this->http->post($this->path . '/documents', $data);
    }

    //删除一个文档
    public function deleteDocument($id)
    {
        $id_array = is_array($id) ? $id : [$id];
        return $this->http->post($this->path . '/documents/delete-batch', $id_array);
    }

    public function deleteAllDocuments()
    {
        return $this->http->delete($this->path . '/documents');
    }

    public function resetFilterableAttributes()
    {
        return $this->http->delete($this->path . '/settings/filterable-attributes');
    }

    public function resetSortableAttributes()
    {
        return $this->http->delete($this->path . '/settings/sortable-attributes');
    }

    public function updateSortableAttributes(array $data)
    {
        return $this->http->put($this->path . '/settings/sortable-attributes', $data);
    }

    public function updateFilterableAttributes(array $data)
    {
        return $this->http->put($this->path . '/settings/filterable-attributes', $data);
    }

    public function updateRankingRules(array $data)
    {
        return $this->http->put($this->path . '/settings/ranking-rules', $data);
    }

    public function updateSynonyms(array $data)
    {
        return $this->http->put($this->path . '/settings/synonyms', $data);
    }

    public function getSynonyms(array $data)
    {
        return $this->http->get($this->path . '/settings/synonyms');
    }
}

class ZibMeilisearchHttp
{
    public $url     = '';
    public $api_url = '';
    public $key     = null;
    public $args    = [];
    public $result  = [];

    public function __construct($api_url, $key = null)
    {

        $api_url = rtrim($api_url, '/');

        $this->api_url = $api_url;
        $this->url     = $api_url;
        $this->key     = $key;

        $this->args = [
            'timeout' => 15,
        ];

        $header = [
            'Content-Type' => 'application/json',
        ];

        if ($key) {
            $header['authorization'] = 'Bearer ' . $key;
        }

        $this->header($header);
    }

    //添加路径
    public function path($path)
    {
        $path      = trim($path, '/');
        $this->url = $this->api_url . '/' . $path;
        return $this;
    }

    //添加请求查询参数
    public function params($args)
    {
        if ($args) {
            $this->args['body'] = $args;
        } elseif (isset($this->args['body'])) {
            unset($this->args['body']);
        }
        return $this;
    }

    //添加请求headre
    public function header($header)
    {
        $this->args['headers'] = $header;
        return $this;
    }

    public function method($method)
    {
        $method = strtoupper($method);
        if (in_array($method, ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'OPTIONS', 'PATCH'])) {
            $this->args['method'] = $method;
        } else {
            $this->args['method'] = 'POST';
        }

        return $this;
    }

    public function patch($path, $params = [])
    {
        $this->path($path)->params($params);
        $this->method('PATCH');
        $this->remote();
        return new ZibMeilisearchResult($this->result);
    }

    public function get($path, $params = [])
    {
        $this->path($path)->params($params);
        $this->method('GET');
        $this->remote();
        return new ZibMeilisearchResult($this->result);
    }

    public function post($path, $params = [])
    {
        $this->path($path)->params($params);
        $this->method('POST');
        $this->remote();
        return new ZibMeilisearchResult($this->result);
    }

    public function put($path, $params = [])
    {
        $this->path($path)->params($params);
        $this->method('PUT');
        $this->remote();
        return new ZibMeilisearchResult($this->result);
    }

    public function delete($path, $params = [])
    {
        $this->path($path)->params($params);
        $this->method('DELETE');
        $this->remote();
        return new ZibMeilisearchResult($this->result);
    }

    public function remote()
    {
        // 发送请求
        if (isset($this->args['body'])) {
            $this->args['body'] = json_encode($this->args['body']);
        }

        $response = wp_remote_post($this->url, $this->args);

        $this->autoError($response);

        $body   = $response['body'] ?? '';
        $result = @json_decode($body, true);

        $this->result = $result;

        return $this;
    }

    //自动判断错误
    public function autoError($response)
    {

        if (is_wp_error($response)) {
            throw new \Exception('ZibMeilisearchHttp网络请求失败：' . $response->get_error_message());
            return [];
        }

        $body          = $response['body'] ?? '';
        $result        = @json_decode($body, true);
        $response_code = $response['response']['code'] ?? 400;

        $code_msg = [
            'missing_authorization_header' => '权限异常：该接口必须填写密钥',
            'invalid_api_key'              => 'API密钥错误',
        ];

        $body_code = $result['code'] ?? '';

        if ((int) $response_code > 400) {
            if ($body_code && isset($code_msg[$body_code])) {
                throw new \Exception('ZibMeilisearchHttp错误：' . $code_msg[$body_code]);
            }

            if (!empty($result['message'])) {
                throw new \Exception('ZibMeilisearchHttp错误：' . $result['message'] . ($body_code ? '，错误代码：' . $body_code : ''));
            }

            throw new \Exception('ZibMeilisearchHttp请求错误：' . json_encode($response));
        }

        if (!is_array($result)) {
            throw new \Exception('ZibMeilisearchHttp响应数据异常：响应非json数据');
        }
    }
}

class ZibMeilisearchResult
{
    public $data = null;
    public function __construct($data)
    {
        $this->data = (array) $data;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function getHits()
    {

        return $this->data['hits'] ?? [];
    }

    public function getTotalHits()
    {

        return $this->data['totalHits'] ?? 0;
    }

    public function getTotalPages()
    {

        return $this->data['totalPages'] ?? 0;
    }

    public function getProcessingTime()
    {
        return $this->data['processingTimeMs'] ?? 0;
    }

    public function getFacetDistribution()
    {

        return $this->data['facetDistribution'] ?? [];
    }
    public function getFacetStats()
    {
        return $this->data['facetStats'] ?? [];
    }

}
