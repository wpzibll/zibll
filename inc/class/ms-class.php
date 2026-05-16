<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-12-06 16:06:33
 * @LastEditTime : 2026-02-01 13:43:43
 * @Project      : 智能搜索插件
 * @Description  : zib-智能搜索插件
 * Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用zib-智能搜索插件，插件源码有详细的注释，支持二次开发
 * @Remind       : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
 */

class zib_ms
{
    public $client            = null;
    public $posts_index_name  = 'posts';
    public $terms_index_name  = 'terms';
    public $users_index_name  = 'users';
    public $post_types        = ['post', 'page', 'plate', 'forum_post', 'shop_product'];
    public $postIndex         = null;
    public $termIndex         = null;
    public $userIndex         = null;
    public $post_status       = ['publish', 'draft', 'pending', 'trash'];
    public $stats             = [];
    public $posts_search_tabs = ['title', 'content'];
    public $s                 = '';
    public $search_result     = [];
    public $facets_result     = [];

    public function __construct($args = [])
    {
        $args = is_array($args) ? $args : [];
        $args = array_merge([
            'api_url'      => 'http://127.0.0.1:7700',
            'api_key'      => '',
            'index_prefix' => '',
        ], $args);

        $api_url      = $args['api_url'];
        $api_key      = $args['api_key'];
        $index_prefix = $args['index_prefix'] ?: self::unique_index_prefix();

        //如果没有$api_url，则返回错误
        if (!$api_url) {
            return;
        }

        $this->client           = new \Zib\Meilisearch\Client($api_url, $api_key);
        $this->posts_index_name = $index_prefix . $this->posts_index_name;
        $this->terms_index_name = $index_prefix . $this->terms_index_name;
        $this->postIndex        = $this->client->index($this->posts_index_name);
        $this->termIndex        = $this->client->index($this->terms_index_name);
        $this->userIndex        = $this->client->index($this->users_index_name);

        if (isset($args['post_types'])) {
            $this->set_posts_post_types($args['post_types']);
        }

        if (isset($args['post_status'])) {
            $this->set_posts_post_status($args['post_status']);
        }

        if (isset($args['posts_search_tabs'])) {
            $this->set_posts_search_tabs($args['posts_search_tabs']);
        }

        $this->init();
    }

    public function init()
    {
        add_filter('posts_clauses_request', [$this, 'posts_clauses'], 200, 2);
        add_action('save_post', [$this, 'sync_post'], 10, 2);
        add_action('delete_post', [$this, 'delete_post'], 10, 2);

        //保存meta数据
        add_action('updated_post_meta', [$this, 'sync_post_meta'], 10, 4);
    }

    //设置文章搜索
    public function set_posts_search_tabs($tabs)
    {
        if ($tabs && is_array($tabs)) {
            $this->posts_search_tabs = $tabs;
        }
    }

    //设置文章搜索
    public function set_posts_post_types($post_types)
    {
        if ($post_types && is_array($post_types)) {
            $this->post_types = $post_types;
        }
    }

    //设置文章搜索
    public function set_posts_post_status($post_status)
    {
        if ($post_status && is_array($post_status)) {
            $this->post_status = $post_status;
        }
    }

    public static function unique_index_prefix()
    {
        $url    = parse_url(get_bloginfo('url'));
        $domain = $url['host'];
        //为url . 改为_
        $url = str_replace('.', '_', $domain);
        return $url . '_';
    }

    //获取状态统计
    public function stats()
    {
        try {
            $stats            = $this->client->stats()->toArray();
            $stats['version'] = $this->client->version();
            $stats['error']   = 0;
        } catch (\Exception $e) {
            return ['error' => 1, 'msg' => $e->getMessage()];
        }

        return $stats;
    }

    public function sync_post_meta($meta_id, $object_id, $meta_key, $meta_value)
    {
        if (in_array($meta_key, ['plate_id', 'views', 'sales_volume', 'score'])) {
            $this->sync_post($object_id);
        }
    }

    public function delete_post($post_id)
    {
        try {
            $this->postIndex->deleteDocument($post_id);
        } catch (\Exception $e) {
            error_log('zib-meilisearch 删除数据失败，ID： ' . $post_id . ' ，错误信息： ' . $e->getMessage());
        }
    }

    public function posts_clauses($clauses, $wp_query)
    {
        //非搜索不进行处理
        if (!$wp_query->is_search() || !$wp_query->get('s')) {
            return $clauses;
        }

        //对后台搜索不处理
        if ($wp_query->is_admin) {
            return $clauses;
        }

        global $wpdb;
        $s           = $wp_query->get('s');
        $paged       = $wp_query->get('paged');
        $post_type   = $wp_query->get('post_type');
        $page_size   = $wp_query->get('posts_per_page');
        $tax_query   = $wp_query->get('tax_query');
        $post_status = $wp_query->get('post_status') ?: 'publish';
        $meta_query  = $wp_query->get('meta_query');
        $paged       = $paged ?: 1;
        $page_size   = $page_size ?: 12;
        $this->s     = $s;

        if (is_string($post_type) && $post_type === 'any') {
            $post_type = ['post', 'page'];
        }

        //执行判断，post_type必须为$this->post_types中的一个
        if (!is_array($post_type) && !in_array($post_type, $this->post_types)) {
            return $clauses;
        }

        //执行判断，$post_type如果为数组，至少有一个在$this->post_types中
        if (is_array($post_type) && $post_type) {
            if (count(array_intersect($post_type, $this->post_types)) === 0) {
                return $clauses;
            }
        }

        if (is_string($post_status) && $post_status == 'all') {
            $post_status = $this->post_status;
        } else {
            //执行判断 post_status必须为$this->post_status中
            if (!is_array($post_status) && !in_array($post_status, $this->post_status)) {
                return $clauses;
            }

            //执行判断，post_status 如果为数组，至少有一个在$this->post_status中
            if (is_array($post_status) && $post_status) {
                if (count(array_intersect($post_status, $this->post_status)) === 0) {
                    return $clauses;
                }
            }
        }

        //准备搜索参数
        $filter        = [];
        $facets_filter = [];
        $options       = [
            'hitsPerPage'      => $page_size,
            'page'             => $paged,
            'matchingStrategy' => 'frequency',
            'facets'           => $this->facets_keys($post_type),
        ];

        //如果关键词 包含 (空格-)
        if (strpos($s, ' -') !== false || strpos($s, '+') !== false) {
            $options['matchingStrategy'] = 'all';
        }

        //处理post_type 筛选
        if ($post_type) {
            $_post_type_filter = is_array($post_type) ? 'post_type IN [' . implode(',', $post_type) . ']' : 'post_type = "' . $post_type . '"';
            $facets_filter[]   = $_post_type_filter;
            $filter[]          = $_post_type_filter;
        }

        //处理post_status 筛选
        if ($post_status) {
            $_post_status_filter = is_array($post_status) ? 'post_status IN [' . implode(',', $post_status) . ']' : 'post_status = "' . $post_status . '"';
            $facets_filter[]     = $_post_status_filter;
            $filter[]            = $_post_status_filter;
        }

        //处理分类排除
        $exclude_cats = $wp_query->get('category__not_in');
        if ($exclude_cats) {
            $_exclude_cats_filter = is_array($exclude_cats) ? 'category NOT IN [' . implode(',', $exclude_cats) . ']' : 'category != "' . $exclude_cats . '"';
            $facets_filter[]      = $_exclude_cats_filter;
            $filter[]             = $_exclude_cats_filter;

            $_exclude_cats_shop_cat_filter = is_array($exclude_cats) ? 'shop_cat NOT IN [' . implode(',', $exclude_cats) . ']' : 'shop_cat != "' . $exclude_cats . '"';
            $facets_filter[]               = $_exclude_cats_shop_cat_filter;
            $filter[]                      = $_exclude_cats_shop_cat_filter;
        }

        //处理tax_query筛选
        if (is_array($tax_query) && $tax_query) {
            foreach ($tax_query as $tax) {
                if ($tax['field'] === 'id' && in_array($tax['taxonomy'], ['category', 'post_tag', 'forum_topic', 'forum_tag', 'plate_cat', 'shop_cat', 'shop_tag', 'shop_discount'])) {
                    $filter[] = is_array($tax['terms']) ? $tax['taxonomy'] . ' IN [' . implode(',', $tax['terms']) . ']' : $tax['taxonomy'] . ' = "' . $tax['terms'] . '"';
                }
            }
        }

        //处理plate_id 筛选
        $meta_query = $wp_query->get('meta_query');
        if (is_array($meta_query) && $meta_query) {
            foreach ($meta_query as $meta) {
                if ($meta['key'] === 'plate_id') {
                    $filter[] = 'plate_id = "' . $meta['value'] . '"';
                }
            }
        }

        //处理post_author
        $post_author = $wp_query->get('author');
        if ($post_author) {
            $filter[] = is_array($post_author) ? 'post_author IN [' . implode(',', $post_author) . ']' : 'post_author = "' . $post_author . '"';
        }

        //处理orderby
        //$orderby = $wp_query->get('orderby') ?? '';
        $orderby = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : '';
        $orderby = $this->orderby_keys($post_type, $orderby);
        if ($orderby) {
            $order           = !empty($_REQUEST['order']) ? strtolower($_REQUEST['order']) : 'desc';
            $order           = $order === 'asc' ? 'asc' : 'desc';
            $options['sort'] = [$orderby . ':' . $order]; //转小写
        }

        $options['filter'] = $filter;

        try {
            $this->post_search($s, $options, $facets_filter);
            $hits = $this->getHits();
        } catch (\Exception $e) {
            error_log('zib-meilisearch 智能搜索失败，已退回原查询，查询关键词： ' . $s . ' ，失败信息： ' . $e->getMessage());
            return $clauses;
        }

        $ids         = array_column($hits, 'id');
        $ids_implode = $ids ? implode(',', $ids) : '';

        $clauses['where']    = ' AND ' . ($ids_implode ? "{$wpdb->posts}.ID IN ($ids_implode)" : ' 1 != 1');
        $clauses['orderby']  = $ids_implode ? "FIELD( {$wpdb->posts}.ID, $ids_implode ) ASC" : 'ID';
        $clauses['groupby']  = '';
        $clauses['join']     = '';
        $clauses['distinct'] = '';
        $clauses['limits']   = '';

        $GLOBALS['zib_ms_found_posts']   = $this->getTotalHits();
        $GLOBALS['zib_ms_max_num_pages'] = $this->getTotalPages();

        add_filter('found_posts', [$this, 'found_posts_filter'], 200, 2);
        add_filter('search_orderby_array', [$this, 'orderby_array_filter']);

        //使用缓存设置的方式，效率更高
        wp_cache_set('search_facets_datas', $this->search_facets_datas_filter(), 'zib_cache_group');
        return $clauses;
    }

    public function getHits()
    {
        return $this->search_result['hits'] ?? [];
    }

    public function getTotalHits()
    {
        return $this->search_result['totalHits'] ?? 0;
    }

    public function getTotalPages()
    {
        return $this->search_result['totalPages'] ?? 0;
    }

    public function getFacets()
    {
        return $this->facets_result['facetDistribution'] ?? [];
    }

    public function getFacetsHits()
    {
        return $this->facets_result['hits'] ?? [];
    }

    public function search_facets_datas_filter()
    {
        $_data = [];
        //小于4个结果时候，不显示
        if ($this->getFacetsHits() <= 4) {
            return $_data;
        }

        $facets = $this->getFacets();
        if ($facets) {
            foreach ($facets as $k => $value) {
                if (empty($value)) {
                    continue;
                }

                //取前10个
                arsort($value);
                $ids = array_slice(array_keys($value), 0, 20);

                if ($k === 'plate_id') {
                    $get_posts = get_posts(array(
                        'post_type' => 'plate',
                        'post__in'  => $ids,
                        'orderby'   => 'post__in',
                    ));
                    foreach ($get_posts as $post) {
                        $plate_name  = zib_str_cut($post->post_title, 0, 15, '...') . '<span class="badg badg-sm cir ml3">' . $value[$post->ID] . '</span>';
                        $_data[$k][] = ['id' => $post->ID, 'name' => $plate_name];
                    }
                } else {
                    $get_terms = get_terms(array(
                        'include' => $ids,
                        'orderby' => 'include',
                    ));
                    foreach ($get_terms as $term) {
                        $term_name   = zib_str_cut($term->name, 0, 15, '...') . '<span class="badg badg-sm cir ml3">' . $value[$term->term_id] . '</span>';
                        $_data[$k][] = ['id' => $term->term_id, 'name' => $term_name, 'taxonomy' => $term->taxonomy];
                    }
                }
            }
        }

        return $_data;
    }

    public function facets_keys($post_type = null)
    {
        $keys = [
            'post'         => [
                'category',
                'post_tag',
                'topics',
            ],
            'forum_post'   => [
                'forum_topic',
                'forum_tag',
                'plate_id',
            ],
            'shop_product' => [
                'shop_cat',
                'shop_tag',
                'shop_discount',
            ],
            'plate'        => [
                'plate_cat',
            ],
        ];

        if ($post_type !== null) {
            //如果是数组，则叠加返回
            if (is_array($post_type)) {
                $new_keys = [];
                foreach ($post_type as $type) {
                    $new_keys = array_merge($new_keys, $keys[$type] ?? []);
                }
                return $new_keys;
            }

            return $keys[$post_type] ?? [];
        }

        return $keys;
    }

    public function orderby_array_filter($orderby = [])
    {

        $search_orderby = [
            'post'    => [
                'date'         => '最新',
                'views'        => '热门',
                'sales_volume' => '销量',
            ],
            'forum'   => [
                'date'         => '最新',
                'views'        => '热门',
                'sales_volume' => '销量',
                'score'        => '评分',
            ],
            'product' => [
                'date'         => '最新',
                'views'        => '热门',
                'sales_volume' => '销量',
                'score'        => '评分',
            ],
            'plate'   => [
                'date'  => '最新',
                'views' => '热门',
            ],
        ];

        $orderby['post']    = $search_orderby['post'];
        $orderby['forum']   = $search_orderby['forum'];
        $orderby['product'] = $search_orderby['product'];
        $orderby['plate']   = $search_orderby['plate'];

        return $orderby;
    }

    public function orderby_keys($post_type = null, $orderby = null)
    {
        $keys = [
            'post'         => [
                'date'         => 'id',
                'views'        => 'views',
                'sales_volume' => 'sales',
            ],
            'forum_post'   => [
                'date'         => 'id',
                'views'        => 'views',
                'sales_volume' => 'sales',
                'score'        => 'score',
            ],
            'shop_product' => [
                'date'         => 'id',
                'views'        => 'views',
                'sales_volume' => 'sales',
                'score'        => 'score',
            ],
            'plate'        => [
                'date'  => 'id',
                'views' => 'views',
            ],
        ];

        if ($post_type !== null) {
            if (is_array($post_type)) {
                $new_keys = [];
                foreach ($post_type as $type) {
                    $new_keys = array_merge($new_keys, $keys[$type] ?? []);
                }

                $new_keys = array_unique($new_keys);
                if ($orderby !== null) {
                    return $new_keys[$orderby] ?? '';
                }
                return $new_keys;
            }

            if ($orderby !== null) {
                return $keys[$post_type][$orderby] ?? '';
            }

            return $keys[$post_type] ?? [];
        }

        return $keys;
    }

    public function found_posts_filter($found_posts, $wp_query)
    {
        if (isset($GLOBALS['zib_ms_found_posts']) && isset($GLOBALS['zib_ms_max_num_pages'])) {
            $wp_query->found_posts   = (int) $GLOBALS['zib_ms_found_posts'];
            $wp_query->max_num_pages = (int) $GLOBALS['zib_ms_max_num_pages'];
            $found_posts             = $GLOBALS['zib_ms_found_posts'];
            unset($GLOBALS['zib_ms_found_posts']);
            unset($GLOBALS['zib_ms_max_num_pages']);
            return $found_posts;
        }

        return $found_posts;
    }

    public function post_search($key, $options = [], $facets_filter = [])
    {
        $options = wp_parse_args($options, [
            'q'                    => $key,
            'indexUid'             => $this->posts_index_name,
            'attributesToRetrieve' => ['id', 'views'], //返回文档中要显示的属性
            'attributesToSearchOn' => ['post_title', 'post_excerpt', 'post_content', 'term_name'], //限制搜索属性
            'hitsPerPage'          => 12,
            'page'                 => 1,
        ]);

        $facets_options           = $options;
        $facets_options['filter'] = $facets_filter;

        //移出$options
        unset($options['facets']);
        $data = $this->client->multiSearch([$options, $facets_options])->toArray();

        //搜索结果
        $this->search_result = $data['results'][0] ?? [];
        $this->facets_result = $data['results'][1] ?? [];
        return $this;
    }

    /**
     * 单独一个的搜索，未使用
     */
    public function search($key, $options = [])
    {
        $options = wp_parse_args($options, [
            'attributesToRetrieve' => ['id', 'views'], //返回文档中要显示的属性
            'attributesToSearchOn' => ['post_title', 'post_excerpt', 'post_content', 'term_name'], //限制搜索属性
            'hitsPerPage'          => 12,
            'page'                 => 1,
        ]);

        $data = $this->postIndex->search($key, $options);
        return $data;
    }

    public function facet_search($key, $FacetName)
    {

        $options = [
            'attributesToSearchOn' => ['post_title', 'post_excerpt', 'post_content', 'term_name'], //限制搜索属性
            'exhaustiveFacetCount' => true,
            'matchingStrategy'     => 'frequency',

        ];

        $data = $this->postIndex->facetSearch($key, $FacetName, $options)->toArray();

        return $data;
    }

    /**
     * 导入全部数据
     */
    public function import($paged = 1, $page_size = 500)
    {

        $posts = $this->get_posts($paged, $page_size);
        $total = $this->post_total();

        //更新过滤属性
        try {
            if ($paged === 1) {
                $this->sync_init();
            }
            $this->postIndex->addDocuments($posts);
        } catch (\Exception $e) {
            return ['error' => 1, 'msg' => '处理失败，错误信息：' . $e->getMessage()];
        }

        //计算已完成
        $completed = ($paged - 1) * $page_size + count($posts);
        //计算剩余数据
        $remaining = $total - $completed;
        return ['error' => 0, 'count' => count($posts), 'paged' => $paged, 'total' => $total, 'remaining' => $remaining, 'completed' => $completed];
    }

    //同步单个数据
    public function sync_post($post)
    {
        $post = get_post($post, 'ARRAY_A');
        if (!$post) {
            return ['error' => 1, 'msg' => '文章不存在'];
        }
        $post_data = $this->post_map($post);

        try {
            $this->postIndex->addDocuments([$post_data]);
        } catch (\Exception $e) {
            error_log('zib-meilisearch 同步单个数据失败，文章ID： ' . $post_data['id'] . ' ，错误信息： ' . $e->getMessage());
            return ['error' => 1, 'msg' => '同步单个数据失败，错误信息：' . $e->getMessage()];
        }

        return ['error' => 0, 'msg' => '文章数据同步成功，ID： ' . $post_data['id']];
    }

    /**
     * 同步初始化
     * @return bool 是否成功
     */
    public function sync_init()
    {
        $post_filterable_attributes = [
            'post_type',
            'post_status',
            'post_author',
            'category_names',
            'category',
            'post_tag',
            'topics',
            'forum_topic',
            'forum_tag',
            'plate_cat',
            'shop_cat',
            'shop_tag',
            'shop_discount',
            'plate_id',
            'views',
            'sales',
            'score',
        ];

        $post_sortable_attributes = [
            'views',
            'sales',
            'score',
            'id',
        ];

        $ranking_rules = [
            'words',
            'sort',
            'typo',
            'proximity',
            'attribute',
            'exactness',
        ];

        $this->postIndex->resetFilterableAttributes();
        $this->postIndex->resetSortableAttributes();
        $this->postIndex->updateSortableAttributes($post_sortable_attributes);
        $this->postIndex->updateFilterableAttributes($post_filterable_attributes);
        $this->postIndex->updateRankingRules($ranking_rules);
    }

    /**
     * 获取导入的文章数据
     * @param int $paged 页码
     * @param int $page_size 每页数量
     * @return array 文章数据
     */
    public function get_posts($paged = 1, $page_size = 100)
    {
        $DB = new zib_db();
        $DB->table($DB->wpdb->posts)->field('ID as id,post_date,post_title,post_content,post_excerpt,post_status,post_type,post_author')->order('ID')->page($paged, $page_size);
        $DB->where('post_type', $this->post_types);
        $DB->where('post_status', $this->post_status);

        $posts = $DB->select()->toArrayMap([$this, 'post_map']);

        return $posts;
    }

    /**
     * 获取总数
     */
    public function post_total()
    {
        $DB = new zib_db();
        $DB->table($DB->wpdb->posts);
        $DB->where('post_type', $this->post_types);
        $DB->where('post_status', $this->post_status);

        return $DB->count();
    }

    /**
     * 清除所有的数据
     */
    public function deleteAll()
    {
        return $this->postIndex->deleteAllDocuments();
    }

    //设置主键
    public function updatePrimaryKey()
    {
        return $this->postIndex->updatePrimaryKey('id');
    }

    public function schema()
    {
        $schema = [

        ];

        return $schema;
    }

    //配置搜索文章栏目
    public function post_map_is_search_tab($tab)
    {
        $posts_search_tabs = $this->posts_search_tabs;
        if (!$posts_search_tabs) {
            $posts_search_tabs = ['title', 'content'];
        }

        return in_array($tab, $posts_search_tabs);
    }

    public function post_map_terms($post_id, $taxonomy)
    {
        $data = [
            'ids'   => [],
            'names' => [],
        ];

        $terms = get_the_terms($post_id, $taxonomy);
        if (!is_wp_error($terms) && isset($terms[0]->term_id)) {
            foreach ($terms as $item) {
                $_d            = $this->post_term_data($item, $taxonomy);
                $data['ids']   = array_merge($data['ids'], $_d['ids']);
                $data['names'] = array_merge($data['names'], $_d['names']);
            }
        }

        return $data;
    }

    public function post_term_data($term_id, $taxonomy)
    {
        $data = [
            'ids'   => [],
            'names' => [],
        ];

        $term = get_term($term_id, $taxonomy);
        if (!is_wp_error($term) && isset($term->term_id)) {
            $data['ids'][]   = $term->term_id;
            $data['names'][] = $term->name;
            if (!empty($term->parent)) {
                $parent_data   = $this->post_term_data($term->parent, $taxonomy);
                $data['ids']   = array_merge($data['ids'], $parent_data['ids']);
                $data['names'] = array_merge($data['names'], $parent_data['names']);
            }
        }

        return $data;
    }

    /**
     * 数据整理
     */
    public function post_map($data)
    {

        $data       = (array) $data;
        $data['id'] = isset($data['id']) ? (int) $data['id'] : (isset($data['ID']) ? (int) $data['ID'] : '');
        $post_type  = isset($data['post_type']) ? $data['post_type'] : '';
        $term_name  = [];
        $term_ids   = [];
        $plate_id   = 0;

        if (!$data['id']) {
            return [];
        }

        $post_title   = isset($data['post_title']) && $this->post_map_is_search_tab('title') ? wp_strip_all_tags($data['post_title']) : '';
        $post_content = isset($data['post_content']) && $this->post_map_is_search_tab('content') ? wp_strip_all_tags(strip_shortcodes($data['post_content'])) : '';
        $post_excerpt = isset($data['post_excerpt']) && $this->post_map_is_search_tab('excerpt') ? $data['post_excerpt'] : '';
        if ($post_type === 'post') {
            //文章
            //专题，分类，标签

            foreach (['topics', 'category', 'post_tag'] as $taxonomy) {
                $terms_data          = $this->post_map_terms($data['id'], $taxonomy);
                $term_ids[$taxonomy] = $terms_data['ids'];
                if ($this->post_map_is_search_tab($taxonomy)) {
                    $term_name = array_merge($term_name, $terms_data['names']);
                }
            }
        } elseif ($post_type === 'forum_post') {
            //帖子
            //帖子话题，标签
            foreach (['forum_tag', 'forum_topic'] as $taxonomy) {
                $terms_data          = $this->post_map_terms($data['id'], $taxonomy);
                $term_ids[$taxonomy] = $terms_data['ids'];
                if ($this->post_map_is_search_tab($taxonomy)) {
                    $term_name = array_merge($term_name, $terms_data['names']);
                }
            }

            //帖子版块plate_id
            $plate_id = (int) get_post_meta($data['id'], 'plate_id', true);

        } elseif ($post_type === 'plate') {
            //板块
            //板块分类
            foreach (['plate_cat'] as $taxonomy) {
                $terms_data          = $this->post_map_terms($data['id'], $taxonomy);
                $term_ids[$taxonomy] = $terms_data['ids'];
                if ($this->post_map_is_search_tab($taxonomy)) {
                    $term_name = array_merge($term_name, $terms_data['names']);
                }
            }
        } elseif ($post_type === 'shop_product') {
            //商品
            //商品分类shop_cat，商品标签shop_tag，shop_discount
            foreach (['shop_cat', 'shop_tag', 'shop_discount'] as $taxonomy) {
                $terms_data          = $this->post_map_terms($data['id'], $taxonomy);
                $term_ids[$taxonomy] = $terms_data['ids'];
                if ($this->post_map_is_search_tab($taxonomy)) {
                    $term_name = array_merge($term_name, $terms_data['names']);
                }
            }

            //简介
            $post_excerpt .= zib_get_array_value(get_post_meta($data['id'], 'product_config', true), 'desc', '');
        }

        //搜索用户姓名
        if ($this->post_map_is_search_tab('user') && !empty($data['post_author'])) {
            $user_data = get_userdata($data['post_author']);
            if (isset($user_data->display_name)) {
                $term_name[] = $user_data->display_name;
            }
        }

        //搜索文章评论
        if ($this->post_map_is_search_tab('coment')) {
            $post_comments_DB = new zib_db();
            $post_comments_DB->table($post_comments_DB->wpdb->comments)->field('comment_content');
            $post_comments_DB->where('comment_approved', 1);
            $post_comments_DB->where('comment_type', 'comment');
            $post_comments_DB->where('comment_post_ID', $data['id']);

            $post_comments = $post_comments_DB->select()->toArray();
            foreach ($post_comments as $_c) {
                $post_excerpt .= wp_strip_all_tags(strip_shortcodes(zib_comment_filters($_c['comment_content'], '', false)));
            }
        }

        //阅读量
        $views = (int) get_post_meta($data['id'], 'views', true);

        //销量
        $sales = in_array($post_type, ['shop_product', 'post', 'forum_post']) ? (int) get_post_meta($data['id'], 'sales_volume', true) : 0;

        //评分
        $score = in_array($post_type, ['shop_product', 'forum_post']) ? (float) get_post_meta($data['id'], 'score', true) : 0;

        $_data = [
            'id'            => $data['id'],
            'post_title'    => $post_title,
            'post_content'  => $post_content,
            'post_excerpt'  => $post_excerpt,
            'term_name'     => $term_name,
            'post_date'     => isset($data['post_date']) ? get_the_date('', $data['id']) : '',
            'post_status'   => isset($data['post_status']) ? $data['post_status'] : '',
            'post_type'     => isset($data['post_type']) ? $data['post_type'] : '',
            'post_author'   => isset($data['post_author']) ? $data['post_author'] : '',
            'category'      => $term_ids['category'] ?? [],
            'post_tag'      => $term_ids['post_tag'] ?? [],
            'topics'        => $term_ids['topics'] ?? [],
            'forum_topic'   => $term_ids['forum_topic'] ?? [],
            'forum_tag'     => $term_ids['forum_tag'] ?? [],
            'plate_cat'     => $term_ids['plate_cat'] ?? [],
            'shop_cat'      => $term_ids['shop_cat'] ?? [],
            'shop_tag'      => $term_ids['shop_tag'] ?? [],
            'shop_discount' => $term_ids['shop_discount'] ?? [],
            'plate_id'      => $plate_id,
            'views'         => $views,
            'sales'         => $sales,
            'score'         => $score,
        ];

        return $_data;
    }

    public function user_map($data)
    {
        $data  = (array) $data;
        $_data = [
            'id' => $data['id'],

        ];
        return $_data;
    }

}
