<?php
/*
* @Author : Qinver
* @Url : zibll.com
* @Date : 2025-03-10 21:25:31
 * @LastEditTime : 2026-05-05 22:24:30
* @Project : Zibll子比主题
* @Description : 更优雅的Wordpress主题|用于数据库操作
* Copyright (c) 2025 Qinver. Licensed under GPL-2.0-or-later.
* @Email : 770349780@qq.com
* @Read me : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
* @Remind : Zibll is released under GPL-2.0-or-later. Official source: https://www.zibll.com
*/

/**
 * 数据库查询构建器类
 * 用于构建SQL查询语句
 *
 * 使用示例:
 * $builder = new Zib_DB_Build();
 * $builder->table('wp_posts')
 * ->where(['post_status' => 'publish'])
 * ->whereIn('post_type', ['post', 'page'])
 * ->limit(10);
 * $result = $builder->get();
 *
 * 测试代码:
 * // 基本查询测试
 * $test1 = new Zib_DB_Build();
 * $sql1 = $test1->table('wp_users')
 * ->where(['user_status' => 0])
 * ->limit(5)
 * ->build();
 * var_dump($sql1);
 *
 * // 复杂条件测试
 * $test2 = new Zib_DB_Build();
 * $sql2 = $test2->table('wp_posts')
 * ->where(['post_status' => 'publish'])
 * ->whereIn('post_type', ['post', 'page'])
 * ->whereBetween('post_date', ['2023-01-01', '2023-12-31'])
 * ->orderBy('post_date', 'DESC')
 * ->limit(10)
 * ->build();
 * var_dump($sql2);
 *
 * // JOIN查询测试
 * $test3 = new Zib_DB_Build();
 * $sql3 = $test3->table('wp_posts', 'p')
 * ->join('wp_postmeta', 'pm', 'p.ID = pm.post_id')
 * ->where(['p.post_status' => 'publish', 'pm.meta_key' => '_thumbnail_id'])
 * ->select('p.ID, p.post_title, pm.meta_value')
 * ->limit(5)
 * ->build();
 * var_dump($sql3);
 */

class zib_db
{
    public $pk              = 'id';
    public $prefix          = '';
    public $table           = '';
    public $meta_table      = '';
    public $meta_on_columns = [];
    public $name            = '';
    public $alias           = '';
    public $where           = [];
    public $whereTime       = [];
    public $whereOr         = [];
    public $order           = [];
    public $limit           = '';
    public $offset          = 0;
    public $group           = '';
    public $having          = '';
    public $field           = '*';
    public $join            = [];
    public $distinct        = false;
    public $values          = [];
    public $prepareData     = [];
    public $sql             = '';
    public $whereSql        = '';
    public $result          = null;
    public $count           = 0;
    public $insert_id       = 0;
    public $exp             = [];
    public $wpdb            = null;
    public $data            = [];

    //构建函数
    public function __construct()
    {
        global $wpdb;
        $this->wpdb   = $wpdb;
        $this->prefix = $wpdb->prefix;
    }

    public function pk($pk)
    {
        $this->pk = $pk;
        return $this;
    }

    /**
     * 设置表名
     * @param string $table 表名
     * @return $this
     */
    public function table($table, $alias = null)
    {
        if ($alias) {
            $this->alias = $alias;
        }

        //先判断子查询
        $subQuerySql = $this->getSubQuerySql($table);
        if ($subQuerySql) {
            $this->table = $subQuerySql;
        } else {
            $this->table = $table;
        }

        return $this;
    }

    public function name($name)
    {
        $this->name  = $name;
        $this->table = $this->prefix . $name;
        return $this;
    }

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 插入数据并返回插入ID
     * @param array $data 数据
     * @return int 插入ID
     */
    public function insertGetId($data)
    {
        $this->insert($data);
        return $this->insert_id;
    }

    /**
     * 插入数据
     * @param array $data 数据
     * @return int 更新行数
     */
    public function insert($data)
    {
        $result          = $this->wpdb->insert($this->table, $data);
        $this->insert_id = $this->wpdb->insert_id;
        return $result;
    }

    /**
     * 删除数据
     * @param array $where 条件
     * @return int 删除行数
     */
    public function delete($where = null)
    {
        if ($where) {
            $this->where($where);
        }

        $this->buildWhere();
        if (!$this->whereSql) {
            return 0;
        }

        $this->sql = "DELETE FROM `$this->table` $this->whereSql";
        return $this->query($this->sql);
    }

    /**
     * 更新数据
     * @param array $data 数据
     * @param array $where 条件
     * @return int 更新行数
     */
    public function update(array $data = [], $where = null)
    {

        if ($where) {
            $this->where($where);
        }

        $this->buildWhere();
        if (!$this->whereSql) {
            return false;
        }

        $fields = array();
        $values = array();
        if ($data) {
            foreach ($data as $field => $val) {
                $fields[] = "`$field` = " . (is_int($val) ? '%d' : '%s');
                $values[] = $val;
            }
        }

        $set_sql = '';
        if ($fields) {
            $fields  = implode(', ', $fields);
            $set_sql = $this->wpdb->prepare($fields, $values);
        }

        //表达式
        if ($this->exp && is_array($this->exp)) {
            foreach ($this->exp as $exp => $value) {
                $set_sql .= ' ' . $exp . ' = ' . $value;
            }
        }

        $this->sql = "UPDATE `$this->table` SET $set_sql $this->whereSql";
        return $this->query($this->sql);
    }

    /**
     * 设置表达式，表达式值不会过滤数据，请提前做好数据过滤，防止SQL注入
     * @param string $exp 表达式
     * @param string $value 值
     * @return $this
     */
    public function exp($exp, $value = null)
    {
        if ($value === null && is_array($exp)) {
            $this->exp = array_merge($this->exp, $exp);
        }

        if (is_string($exp) && is_string($value)) {
            $this->exp[$exp] = $value;
        }

        return $this;
    }

    /**
     * 字段值自增
     * @param string $field 字段名
     * @param int $value 增加值
     * @return $this
     */
    public function inc($field, $value = 1)
    {
        $this->exp($field, '`' . $field . '` + ' . $value);
        return $this;
    }

    /**
     * 字段值自减
     * @param string $field 字段名
     * @param int $value 减少值
     * @return $this
     */
    public function dec($field, $value = 1)
    {
        $this->exp($field, '`' . $field . '` - ' . $value);
        return $this;
    }

    /**
     * 查找一行数据
     * @param array $where 条件
     * @return $this
     */
    //查找一个数据
    public function find($where = null)
    {
        if ($where) {
            $this->where($where);
        }

        $this->limit(1);
        $this->build();
        $this->result = $this->wpdb->get_row($this->sql);
        return $this;
    }

    /**
     * 查找多行数据
     * @param array $where 条件
     * @return $this
     */
    public function select($where = null)
    {
        if ($where) {
            $this->where($where);
        }
        $this->build();
        $this->result = $this->wpdb->get_results($this->sql);
        return $this;
    }

    //查询总数量
    public function count($field = '*', $where = null)
    {
        return $this->getStatistics('COUNT(' . $field . ')', $where);
    }

    //获取最大值
    public function max($field, $where = null)
    {
        return $this->getStatistics('MAX(' . $field . ')', $where);
    }

    //获取最小值
    public function min($field, $where = null)
    {
        return $this->getStatistics('MIN(' . $field . ')', $where);
    }

    //获取平均值
    public function avg($field, $where = null)
    {
        return $this->getStatistics('avg(' . $field . ')', $where);
    }

    //获取总和
    public function sum($field, $where = null)
    {
        return $this->getStatistics('SUM(' . $field . ')', $where);
    }

    //获取单个数学值
    public function getStatistics($field = 'COUNT(*)', $where = null)
    {
        if ($where) {
            $this->where($where);
        }
        $this->limit(1);
        $this->field($field);
        $this->build();
        $this->wpdb->check_current_query = false;
        return $this->wpdb->get_var($this->sql);
    }

    //获取SQL语句
    public function getSql()
    {
        if (!$this->sql) {
            $this->build();
        }

        return $this->sql;
    }

    //构建SQL语句
    public function buildSql()
    {
        return '(' . $this->getSql() . ')';
    }

    //获取结果
    public function result()
    {
        return $this->result;
    }

    //转换为数组
    public function toArray()
    {
        if (empty($this->result)) {
            return [];
        }

        if (is_object($this->result)) {
            return (array) $this->result;
        }

        $result = [];
        if (is_array($this->result)) {
            foreach ($this->result as $key => $value) {
                if (is_object($value)) {
                    $result[$key] = (array) $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    //转换为数组
    public function toArrayMap($callback)
    {
        if (empty($this->result)) {
            return [];
        }

        return array_map($callback, $this->toArray());
    }

    /**
     * 构建并返回SQL语句需要的参数
     * @param mixed $where 可选的额外条件
     * @return array 包含SQL语句和参数的数组
     */
    public function build($where = null)
    {
        if ($where !== null) {
            $this->where($where);
        }

        // 构建SELECT部分
        $sql = 'SELECT ';
        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }
        $sql .= $this->field;

        // 构建FROM部分
        $sql .= ' FROM ' . $this->table;
        if ($this->alias) {
            $sql .= ' AS ' . $this->alias;
        }

        // 构建JOIN部分
        if (!empty($this->join)) {
            foreach ($this->join as $join) {
                $sql .= ' ' . $join;
            }
        }

        // 构建WHERE部分
        $this->buildWhere();
        if ($this->whereSql) {
            $sql .= ' ' . $this->whereSql;
        } else {
            $sql .= ' WHERE 1=1';
        }

        // 添加GROUP BY子句
        if (!empty($this->group)) {
            $sql .= ' GROUP BY ' . $this->group;
        }

        // 添加HAVING子句
        if (!empty($this->having)) {
            $sql .= ' HAVING ' . $this->having;
        }

        // 添加ORDER BY子句
        if (!empty($this->order)) {
            $sql .= $this->buildOrderBy();
        }

        // 添加LIMIT子句
        if (!empty($this->limit)) {
            $sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
        }

        // 合并所有值
        $this->sql = $sql;
        return $this;
    }

    public function buildWhere()
    {
        $whereConditions = [];
        $whereValues     = [];
        // 处理普通WHERE条件
        if (!empty($this->where)) {
            $whereResult = $this->buildWhereConditions($this->where, 'AND');
            if (!empty($whereResult['conditions'])) {
                $whereConditions[] = '(' . $whereResult['conditions'] . ')';
                $whereValues       = array_merge($whereValues, $whereResult['values']);
            }
        }

        // 处理OR条件
        if (!empty($this->whereOr)) {
            $whereOrResult = $this->buildWhereConditions($this->whereOr, 'OR');
            if (!empty($whereOrResult['conditions'])) {
                $whereConditions[] = '(' . $whereOrResult['conditions'] . ')';
                $whereValues       = array_merge($whereValues, $whereOrResult['values']);
            }
        }

        // 添加WHERE子句
        if (!empty($whereConditions)) {
            $prepare_sql       = implode(' AND ', $whereConditions);
            $this->prepareData = [$prepare_sql, $whereValues];

            $this->whereSql = ' WHERE ' . $this->wpdb->prepare($prepare_sql, $whereValues);
        }

        return $this;
    }

    //执行查询
    public function query($sql = null)
    {
        if ($sql) {
            $this->sql = $sql;
        }

        $this->wpdb->check_current_query = false;
        return $this->wpdb->query($this->sql);
    }

    /**
     * 判断是否是子查询
     * @param mixed $value 值
     * @return bool
     */
    private function isSubQuery($value)
    {
        if ($value instanceof self) {
            return true;
        }

        return false;

        //以下未使用：有安全问题，容易导致SQL注入
        if (is_string($value)) {
            //如果是子查询的SQL语句：示例：( SELECT `id`,`name` FROM `think_user` WHERE `id` > 10 ) 或者 SELECT `id`,`name` FROM `think_user` WHERE `id` > 10
            //通过正则判断，必须包含SELECT空格 + 至少一个英文 + FROM空格 至少一个英文
            if (preg_match('/(SELECT|select)\s.*?\w+.*?(FROM|from)\s.*?\w+.*?/', $value, $matches)) {
                return true;
            }
        }

        return false;
    }

    //获取子查询的SQL语句
    private function getSubQuerySql($value)
    {
        //必须为zib_db对象
        if ($value instanceof self) {
            return $value->buildSql();
        }

        return '';

        //以下未使用：有安全问题，容易导致SQL注入
        if (is_string($value)) {
            if (preg_match('/(SELECT|select)\s.*?\w+.*?(FROM|from)\s.*?\w+.*?/', $value, $matches)) {
                //判断首位是否有括号
                //判断必须以空格加(开头，并且以)结尾
                if (preg_match('/^(\s){0,2}\(.*\)(\s){0,2}$/', $value)) {
                    return $value;
                } else {
                    return '(' . $value . ')';
                }
            }
        }
    }

    /**
     * 构建WHERE条件
     * @param array $conditions 条件数组
     * @param string $operator 条件连接符（AND/OR）
     * @return array 包含条件字符串和值的数组
     */
    private function buildWhereConditions($conditions, $operator = 'AND')
    {
        $whereConditions = [];
        $whereValues     = [];

        if (!empty($conditions['relation'])) {
            $operator = strtoupper($conditions['relation']);
        }

        foreach ($conditions as $_key => $_condition) {

            if ($_key === 'relation') {
                continue;
            }

            if (is_string($_condition)) {
                //禁止直接使用SQL语句
                error_log('zibdb:buildWhereConditions:禁止直接使用SQL语句');
                continue;
            } elseif (isset($_condition[0]) && is_array($_condition[0])) {
                //递归处理子条件
                $result = $this->buildWhereConditions($_condition);
                if (!empty($result['conditions'])) {
                    $whereConditions[] = '(' . $result['conditions'] . ')';
                    $whereValues       = array_merge($whereValues, $result['values']);
                }
            } else {
                $_k_0 = $_condition[0] ?? '';
                $_k_1 = $_condition[1] ?? '';

                if (!$_k_0) {
                    continue;
                }

                //处理NULL条件
                if (strtolower($_k_1) === 'is null') {
                    $whereConditions[] = "$_k_0 IS NULL";
                    continue;
                }

                //处理NOT NULL条件
                if (strtolower($_k_1) === 'is not null') {
                    $whereConditions[] = "$_k_0 IS NOT NULL";
                    continue;
                }

                // 处理EXISTS条件
                if (strtolower($_k_1) === 'exists') {
                    //必须为zib_db对象
                    if (!($_condition[2] instanceof self)) {
                        error_log('zibdb:buildWhereConditions:EXISTS条件必须为zib_db对象');
                        continue;
                    }
                    $whereConditions[] = 'EXISTS ' . $_condition[2]->buildSql() . ' ';
                    continue;
                }

                // 处理NOT EXISTS条件
                if (strtolower($_k_1) === 'not exists') {
                    //必须为zib_db对象
                    if (!($_condition[2] instanceof self)) {
                        error_log('zibdb:buildWhereConditions:NOT EXISTS条件必须为zib_db对象');
                        continue;
                    }
                    $whereConditions[] = 'NOT EXISTS ' . $_condition[2]->buildSql() . ' ';
                    continue;
                }

                //处理BETWEEN条件
                if (strtolower($_k_1) === 'between') {
                    $format1           = is_numeric($_condition[2][0]) ? '%d' : '%s';
                    $format2           = is_numeric($_condition[2][1]) ? '%d' : '%s';
                    $whereConditions[] = "$_k_0 BETWEEN $format1 AND $format2";
                    $whereValues[]     = $_condition[2][0];
                    $whereValues[]     = $_condition[2][1];
                    continue;
                }

                //处理NOT BETWEEN条件
                if (strtolower($_k_1) === 'not between') {
                    $format1           = is_numeric($_condition[2][0]) ? '%d' : '%s';
                    $format2           = is_numeric($_condition[2][1]) ? '%d' : '%s';
                    $whereConditions[] = "$_k_0 NOT BETWEEN $format1 AND $format2";
                    $whereValues[]     = $_condition[2][0];
                    $whereValues[]     = $_condition[2][1];
                    continue;
                }

                //处理普通条件
                if (isset($_condition[2])) {
                    $_operator = $_k_1;
                    $_value    = $_condition[2];
                } else {
                    $_operator = '=';
                    $_value    = $_k_1;
                }

                //处理IN条件
                if (is_array($_value)) {
                    $placeholders = [];
                    foreach ($_value as $item) {
                        $placeholders[] = is_int($item) ? '%d' : '%s';
                        $whereValues[]  = $item;
                    }
                    $_operator         = strtolower($_operator) === 'not in' ? 'NOT IN' : 'IN';
                    $placeholders_str  = implode(',', $placeholders);
                    $whereConditions[] = "$_k_0 $_operator ($placeholders_str)";
                    continue;
                }

                //判断$_operator是否合规，防止SQL注入
                if (!in_array(strtolower($_operator), ['=', '>', '<', '>=', '<=', '!=', '<>', 'like', 'not like', 'in', 'not in', 'exists', 'not exists', 'between', 'not between'])) {
                    continue;
                }

                //判断是否是子查询
                $subQuerySql = $this->getSubQuerySql($_value);
                if ($subQuerySql) {
                    $whereConditions[] = "$_k_0 $_operator $subQuerySql";
                    continue;
                }

                //最后=和!=的情况
                $placeholders_str  = is_int($_value) ? '%d' : '%s';
                $whereConditions[] = "$_k_0 $_operator $placeholders_str";
                $whereValues[]     = $_value;
            }
        }

        return [
            'conditions' => implode(" $operator ", $whereConditions),
            'values'     => $whereValues,
        ];
    }

    /**
     * 设置WHERE条件
     * @param mixed $where 条件或字段名 支持格式 (5) || (id,in,[1,2,3]) 或 (id,5) 或 ([id,'is null'])  || ([1,2,3]) || (['id' => 1,'type' => [1, 2, 3]]) || ([['id' => 1,'type' => [1, 2, 3]],['id' => 2,'type' => [1, 2, 3]]])
     * @param mixed $operator 操作符或值
     * @param mixed $value 值
     * @return $this
     */
    public function where($where, ...$args)
    {
        $this->_where_set('where', $where, ...$args);
        return $this;
    }

    /**
     * 设置OR条件
     * @param array $where 条件数组
     * @return $this
     */
    public function whereOr($where, ...$args)
    {
        $this->_where_set('whereOr', $where, ...$args);
        return $this;
    }

    private function _where_set($type, $where, ...$args)
    {
        $type = $type === 'where' ? 'where' : 'whereOr';

        if (is_array($where) && count($args) === 0) {
            $is_legal = false;
            /**
             * $where = [
             *      'id' => 1,
             *      'type' => [1, 2, 3],
             *      'time' => 'is null',
             *      'user' => [
             *          'operator' => '=',
             *          'value' => 1,
             *      ],
             *     ]
             */

            foreach ($where as $key => $value) {
                if (!is_int($key) && $key) {
                    $is_legal = true;
                    unset($where[$key]);

                    if (is_string($value) && in_array(strtolower($value), ['is null', 'is not null', 'not exists', 'exists'])) {
                        $this->{$type}[] = [$key, $value];
                    } else {

                        if (is_array($value) && isset($value['operator']) && isset($value['value'])) {
                            $this->{$type}[] = [$key, $value['operator'], $value['value']];
                        } else {
                            $this->{$type}[] = [$key, is_array($value) ? 'in' : '=', $value];
                        }
                    }
                }

                /**
                 * $where = [
                 *      'id' => 1,
                 *      'type' => [1, 2, 3],
                 *      ['time','>','2026-05-05']
                 *     ]
                 */

                //处理剩下的 ['time','>','2026-05-05']
                if ($is_legal && !empty($where)) {
                    foreach ($where as $key => $value) {
                        if (is_int($key) && is_array($value) && count($value) === 3) {
                            $this->{$type}[] = $value;
                        }
                    }
                }
            }

            $is_ids = true;
            //$where = [1,2,3] 这种格式
            if (!empty($where[0])) {
                foreach ($where as $key => $value) {
                    if (!is_numeric($value)) {
                        $is_ids = false;
                        break;
                    }
                }

                if ($is_ids) {
                    $where = [$this->pk, 'in', $where];
                }
            }

            if (!$is_legal && !$is_ids) {
                if (isset($where[0]) && is_array($where[0])) {
                    //$where = [['id' => 1,'type' => [1, 2, 3]],['id' => 2,'type' => [1, 2, 3]]] 这种格式

                    $this->{$type} = array_merge($this->where, $where);
                } else {
                    // $where = ['id‘,'=’，1] 这种格式
                    $this->{$type}[] = $where;
                }
            }

        } else {
            if (count($args) === 0) {
                if (is_numeric($where)) {
                    $this->{$type}[] = [$this->pk, '=', $where];
                } else {
                    $this->{$type}[] = $where;
                }
            } elseif (count($args) === 1) {
                if (is_string($args[0]) && in_array(strtolower($args[0]), ['is null', 'is not null', 'not exists', 'exists'])) {
                    $this->{$type}[] = [$where, $args[0]];
                } else {
                    $this->{$type}[] = [$where, (is_array($args[0]) ? 'in' : '='), $args[0]];
                }
            } elseif (count($args) === 2) {
                $operator        = $args[0];
                $value           = $args[1];
                $this->{$type}[] = [$where, $operator, $value];
            }
        }

        return $this;
    }

    /**
     * 设置时间条件，支持的用法：
     * whereTime('birthday', 'between', ['1970-10-1', '2000-10-1'])
     * whereTime('create_time', '>', '2023-01-01')
     * whereTime('update_time', '=', '2023-05-15')
     * whereTime('pay_time', 'today')
     * whereTime('pay_time', 'yesterday')
     * whereTime('pay_time', 'week')
     * whereTime('pay_time', 'month')
     * whereTime('pay_time', 'year')
     * @param string $field 字段名
     * @param string $operator 操作符或时间类型
     * @param mixed $time 时间值
     * @return $this
     */
    public function whereTime($field, ...$args)
    {
        if (empty($args)) {
            return $this;
        }

        $operator = $args[0];
        $time     = isset($args[1]) ? $args[1] : null;

        if ($operator === 'all') {
            return $this;
        }

        // 处理特殊时间类型
        if (is_string($operator) && in_array($operator, ['today', 'yesterday', 'yester', 'thisweek', 'week', 'thismonth', 'month', 'thisyear', 'year', 'lastmonth', 'lastyear']) && $time === null) {
            $range = [];
            switch ($operator) {
                case 'today': //今天
                    $todaytime = current_time('Y-m-d');
                    $range     = ["$todaytime 00:00:00", "$todaytime 23:59:59"];
                    break;

                case 'yesterday':
                case 'yester':
                    $todaytime      = current_time('Y-m-d');
                    $yesterday_time = date('Y-m-d', strtotime("$todaytime -1 day"));
                    $range          = ["$yesterday_time 00:00:00", "$yesterday_time 23:59:59"];
                    break;

                case 'thisweek': //本周
                case 'week': //本周
                    $range = [date('Y-m-d 00:00:00', strtotime('this week Monday')), date('Y-m-d 23:59:59', strtotime('this week Sunday'))];
                    break;

                case 'thismonth': //本月
                case 'month': //本月
                    $thismonth_time = current_time('Y-m');
                    $current_time   = current_time('Y-m-d');
                    $range          = ["$thismonth_time-01 00:00:00", "$current_time 23:59:59"];
                    break;

                case 'lastmonth': //上个月
                    $thismonth_time       = current_time('Y-m');
                    $lastmonth_time_start = date('Y-m', strtotime("$thismonth_time -1 month"));
                    $lastmonth_time_stop  = date('Y-m-d H:i:s', strtotime("$thismonth_time -1 seconds"));
                    $range                = ["$lastmonth_time_start-01 00:00:00", "$lastmonth_time_stop"];
                    break;

                case 'thisyear': //今年
                case 'year': //今年
                    $thisyear_time = current_time('Y');
                    $current_time  = current_time('Y-m-d');
                    $range         = ["$thisyear_time-01-01 00:00:00", "$current_time 23:59:59"];
                    break;

                case 'lastyear': //去年
                    $thisyear_time = current_time('Y');
                    $lastyear_time = date('Y', strtotime("$thisyear_time -1 year"));
                    $range         = ["$lastyear_time-01-01 00:00:00", "$lastyear_time-12-31 23:59:59"];
                    break;
            }
            $this->whereBetween($field, $range);
            return $this;
        }

        // 处理最近X天，最近X月，最近X年 格式为 last_3_day，last_3_month，last_3_year
        if (is_string($operator) && strpos($operator, 'last') !== false && strpos($operator, '_') !== false && $time === null) {
            $range        = [];
            $current_time = current_time('Y-m-d');

            //分割下划线
            $operator_array = explode('_', $operator);
            $operator_num   = $operator_array[1];
            $operator_type  = $operator_array[2];

            if ($operator_type === 'minute' || $operator_type === 'minutes') {
                $current_time = current_time('Y-m-d H:i:s');
                $range        = [date('Y-m-d H:i:s', strtotime("-$operator_num minutes", strtotime($current_time))), $current_time];
            }

            if ($operator_type === 'hour' || $operator_type === 'hours') {
                $current_time = current_time('Y-m-d H:i:s');
                $range        = [date('Y-m-d H:i:s', strtotime("-$operator_num hours", strtotime($current_time))), $current_time];
            }

            if ($operator_type === 'day' || $operator_type === 'days') {
                $range = [date('Y-m-d 00:00:00', strtotime("-$operator_num day", strtotime($current_time))), date('Y-m-d 23:59:59', strtotime($current_time))];
            }

            if ($operator_type === 'month' || $operator_type === 'months') {
                $range = [date('Y-m-d 00:00:00', strtotime("-$operator_num month", strtotime($current_time))), date('Y-m-d 23:59:59', strtotime($current_time))];
            }

            if ($operator_type === 'year' || $operator_type === 'years') {
                $range = [date('Y-m-d 00:00:00', strtotime("-$operator_num year", strtotime($current_time))), date('Y-m-d 23:59:59', strtotime($current_time))];
            }

            $this->whereBetween($field, $range);
            return $this;
        }

        //第二个参数是数组，并且第三个参数为空
        if (is_array($operator) && $time === null) {
            $this->whereBetween($field, $operator);
            return $this;
        }

        // 处理between条件
        if (is_string($operator) && strtolower($operator) === 'between' && is_array($time)) {
            $this->whereBetween($field, $time);
            return $this;
        }

        // 处理not between条件
        if (is_string($operator) && strtolower($operator) === 'not between' && is_array($time)) {
            $this->whereNotBetween($field, $time);
            return $this;
        }

        // 处理普通比较操作符
        if (is_string($operator) && in_array($operator, ['=', '>', '>=', '<', '<=', '<>', '!=']) && is_string($time) && $time !== null) {
            $this->whereTime[] = [$field, $operator, $time];
            $this->where([$field, $operator, $time]);
            return $this;
        }

        // 如果是单个时间值，默认为等于操作
        if ($time === null && !in_array($operator, ['today', 'yesterday', 'week', 'month', 'year'])) {
            $timeValue = is_string($operator) ? strtotime($operator) : $operator;
            if ($timeValue !== false) {
                $this->whereTime[] = [$field, '=', $timeValue];
                $this->where([$field, '=', $timeValue]);
            }
        }

        return $this;
    }

    /**
     * 设置LIKE条件
     * @param string|array $field 字段名或数组，字符串支持|分割
     * @param string $value 值:不需要%
     * @return $this
     */
    public function whereLike($field, $value, $or = true)
    {

        if (!is_array($field)) {
            $field = array($field);
        }

        foreach ($field as $field_item) {
            if ($or) {
                $this->whereOr([$field_item, 'LIKE', '%' . $value . '%']);
            } else {
                $this->where([$field_item, 'LIKE', '%' . $value . '%']);
            }
        }

        return $this;
    }

    /**
     * 设置NOT LIKE条件
     * @param string $field 字段名或条件数组
     * @param string $value 值（如果$field是字符串）
     * @return $this
     */
    public function whereNotLike($field, $value, $or = false)
    {
        if (!is_array($field)) {
            $field = explode('|', $field);
        }

        if (!is_array($field)) {
            $field = array($field);
        }

        foreach ($field as $field_item) {
            if ($or) {
                $this->whereOr([$field_item, 'NOT LIKE', '%' . $value . '%']);
            } else {
                $this->where([$field_item, 'NOT LIKE', '%' . $value . '%']);
            }
        }

        return $this;
    }

    /**
     * 设置BETWEEN条件
     * @param string $field 字段名
     * @param array $values 范围值 [min, max]
     * @return $this
     */
    public function whereBetween($field, $values)
    {
        if (is_string($values)) {
            $values = explode(',', $values);
        }

        $this->whereTime[] = [$field, 'BETWEEN', $values];
        $this->where([$field, 'BETWEEN', $values]);
        return $this;
    }

    /**
     * 设置NOT BETWEEN条件
     * @param string $field 字段名
     * @param array $values 范围值 [min, max]
     * @return $this
     */
    public function whereNotBetween($field, $values)
    {
        if (is_string($values)) {
            $values = explode(',', $values);
        }

        $this->whereTime[] = [$field, 'NOT BETWEEN', $values];
        $this->where([$field, 'NOT BETWEEN', $values]);
        return $this;
    }

    /**
     * 设置IS NULL条件
     * @param string $field 字段名
     * @return $this
     */
    public function whereNull($field)
    {
        $this->where([$field, 'IS NULL']);
        return $this;
    }

    /**
     * 设置IS NOT NULL条件
     * @param string|array $field 字段名或字段数组
     * @return $this
     */
    public function whereNotNull($field)
    {
        $this->where([$field, 'IS NOT NULL']);
        return $this;
    }

    /**
     * 设置IN条件
     * @param string $field 字段名
     * @param array|string $values 值数组 例如['1','2','3']，或者('1,2,3')
     * @return $this
     */
    public function whereIn($field, $values)
    {
        if (is_string($values) && strpos($values, '(') === false) {
            $values = '(' . $values . ')';
        }

        $this->where([$field, 'IN', $values]);
        return $this;
    }

    /**
     * 设置NOT IN条件
     * @param string $field 字段名
     * @param array $values 值数组
     * @return $this
     */
    public function whereNotIn($field, $values)
    {
        if (is_string($values) && strpos($values, '(') === false) {
            $values = '(' . $values . ')';
        }

        $this->where([$field, 'NOT IN', $values]);
        return $this;
    }

    /**
     * 设置EXISTS条件
     * @param string $subquery 子查询SQL
     * @return $this
     */
    public function whereExists($subquery)
    {

        $this->where([$subquery, 'EXISTS']);
        return $this;
    }

    /**
     * 设置NOT EXISTS条件
     * @param string $subquery 子查询SQL
     * @return $this
     */
    public function whereNotExists($subquery)
    {
        $this->where([$subquery, 'NOT EXISTS']);
        return $this;
    }

    public function buildOrderBy()
    {
        $order_str = [];
        foreach ($this->order as $_k => $_v) {
            $direction = strtoupper($_v);
            if (!in_array($direction, ['ASC', 'DESC'])) {
                $direction = 'ASC';
            }

            $order_str[] = esc_sql($_k) . ' ' . $direction;
        }

        return $order_str ? ' ORDER BY ' . implode(', ', $order_str) : '';
    }

    /**
     * 添加排序
     * @param string|array $order 排序条件 例如['id' => 'ASC', 'name' => 'DESC']
     * @return $this
     */
    public function order($order, $direction = 'ASC')
    {

        if (is_array($order)) {
            $this->order = array_merge($this->order, $order);
        } else {
            $this->order[$order] = $direction;
        }

        return $this;
    }

    /**
     * 设置排序
     * @param array $order_array 排序条件数组 例如['id' => 'ASC', 'name' => 'DESC']
     * @return $this
     */
    public function orderBy(array $order_array)
    {
        $this->order = $order_array;
        return $this;
    }

    /**
     * 设置LIMIT
     * @param int $limit 限制数量
     * @param int $offset 偏移量
     * @return $this
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit  = (int) $limit;
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * 设置分页
     * @param int $page 页码
     * @param int $page_size 每页数量
     * @return $this
     */
    public function page($page, $page_size)
    {
        $page   = max(1, intval($page));
        $offset = ($page - 1) * $page_size;
        return $this->limit($page_size, $offset);
    }

    /**
     * 设置GROUP BY，注意：传入数据未过滤，请注意sql注入
     * @param string $group 分组条件
     * @return $this
     */
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * 设置HAVING，注意：传入数据未过滤，请注意sql注入
     * @param string $having HAVING条件
     * @return $this
     */
    public function having($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * 设置查询字段
     * @param string|array $field 字段名或字段数组
     * @return $this
     */
    public function field($field)
    {
        if (is_array($field)) {
            $__field = [];
            foreach ($field as $_k => $_v) {
                $__field[] = $_k . ' AS ' . $_v;
            }
            $this->field = implode(', ', $__field);
        } else {
            $this->field = $field;
        }
        return $this;
    }

    /**
     * 添加JOIN
     * LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
     * INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
     * INNER JOIN $wpdb->term_taxonomy ON ( $wpdb->term_taxonomy.term_taxonomy_id =
    $wpdb->term_relationships.term_taxonomy_id )

     * @param string $join JOIN语句
     * @return $this
     */
    public function join($table, $on, $type = 'INNER')
    {
        $table_str = '';
        if (is_array($table) && isset($table[0]) && isset($table[1])) {
            //['语句', 'alias']

            //判断子查询
            if ($table[0] instanceof self) {
                $table[0] = $table[0]->buildSql();
            }

            $table_str = $table[0] . ' AS ' . $table[1];
        } elseif (is_array($table)) {
            foreach ($table as $table_item => $table_alias) {
                //['语句'=> 'alias'，'语句'=> 'alias']
                //判断子查询
                if ($table_item instanceof self) {
                    $table_item = $table_item->buildSql();
                }

                $table_str .= $table_item . ' AS ' . $table_alias . ', ';
            }
            $table_str = substr($table_str, 0, -2);
        } else {
            //按空格分割
            $table_str = explode(' ', $table);
            if (isset($table_str[1])) {
                $table_str = $table_str[0] . ' AS ' . $table_str[1];
            } else {
                $table_str = $table;
            }
        }

        $this->join[] = $type . ' JOIN ' . $table_str . ' ON (' . $on . ')';
        return $this;
    }

    public function metaName($name)
    {
        $this->meta_table = $this->prefix . $name;
        return $this;
    }

    public function metaTable($meta_table)
    {
        $this->meta_table = $meta_table;
        return $this;
    }

    /**
     * 设置meta查询
     * @param array $query_data 查询数据
     * $query_data =
     * [
     *     [
     *         'key' => 'user_id',
     *         'value' => '1',
     *         'compare' => '=', // =，in，!=，not in，like，not like，between，not between，exists，not exists
     *         'compare_key' => '=', // _m.meta_key = user_id
     *         'order' => 'DESC', // ASC
     *         'alias' => 'user_id', // _m.meta_value
     *      ],
     *      'relation' => 'AND', // OR
     *      [
     *          'key' => 'user_id',
     *          'value' => '1',
     *          'compare' => '=',
     *          'compare_key' => '=',
     *          'order' => 'DESC',
     *          'alias' => 'user_id',
     *      ],
     * ]
     * @param array $on_data 关联数据   ['ID', 'user_id']
     * @param string $meta_table 表名
     * @return $this
     */
    public function metaQuery(array $query_data, array $on_data, $meta_table = null)
    {
        if (!$this->alias) {
            $this->alias('_m');
        }

        if ($meta_table) {
            $this->metaTable($meta_table);
        }

        $this->meta_on_columns = $on_data;

        $ForQuery = $this->getMetaForQuery($query_data);

        if (!empty($ForQuery['join'])) {
            foreach ($ForQuery['join'] as $join) {
                $this->join($join[0], $join[1]);
            }
        }

        if (!empty($ForQuery['where'])) {
            $this->where($ForQuery['where']);
        }

        if (!empty($ForQuery['order'])) {
            $this->order($ForQuery['order']);
        }

        return $this;
    }

    /**
     * 解析meta查询
     * @param array $query_data 查询数据
     * @param int $depth 深度
     * @return array
     */
    public function getMetaForQuery($query_data, $depth = 0)
    {
        $meta_where = [];
        $join_sql   = [];
        $order_sql  = [];
        $meta_table = $this->meta_table;
        $meta_on    = $this->meta_on_columns;

        if (isset($query_data['relation'])) {
            $meta_where['relation'] = $query_data['relation'];
        }

        $_i = 0;
        foreach ($query_data as $_k => $_v) {
            if ($_k === 'relation') {
                continue;
            }

            if (is_array($_v) && !isset($_v['key']) && isset($_v[0])) {
                $_query_data = $this->getMetaForQuery($_v, $depth + 1);

                $meta_where[] = $_query_data['where'];
                $join_sql     = array_merge($join_sql, $_query_data['join']);
                $order_sql    = array_merge($order_sql, $_query_data['order']);
            } else {
                if (empty($_v['key'])) {
                    continue;
                }

                $_i++;
                $_meta_alias = !empty($_v['alias']) ? $_v['alias'] : '_mt_' . $depth . '_' . $_i;
                $join_sql[]  = [[$meta_table, $_meta_alias], $this->alias . '.' . $meta_on[0] . ' = ' . $_meta_alias . '.' . $meta_on[1]];

                // 处理meta_key
                $compare_key = !empty($_v['compare_key']) ? $_v['compare_key'] : (is_array($_v['key']) ? 'in' : '=');

                $_where = [
                    [$_meta_alias . '.meta_key', $compare_key, $_v['key']],
                ];

                if (isset($_v['value'])) {
                    $compare  = !empty($_v['compare']) ? $_v['compare'] : (is_array($_v['value']) ? 'in' : '=');
                    $_where[] = [$_meta_alias . '.meta_value', $compare, $_v['value']];
                }

                if (!empty($_v['order'])) {
                    $order_sql[$_meta_alias . '.meta_value'] = $_v['order'];
                }

                $meta_where[] = $_where;
            }
        }

        return [
            'where' => $meta_where,
            'join'  => $join_sql,
            'order' => $order_sql,
        ];
    }

    /**
     * 设置表别名
     * @param string $alias 别名
     * @return $this
     */
    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * 设置DISTINCT
     * @param bool $distinct 是否使用DISTINCT
     * @return $this
     */
    public function distinct($distinct = true)
    {
        $this->distinct = $distinct;
        return $this;
    }
}
