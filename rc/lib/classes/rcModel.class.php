<?php

/**
 * Class rcModel
 * @property $fields
 * @property $select
 * @property $join
 * @property $where
 * @property $logic
 * @property $group_by
 * @property $having
 * @property $having_logic
 * @property $order_by
 * @property $limit
 */
class rcModel extends waModel
{
    protected $data = array();
    protected $isolation_level = '';
    protected $fetch_type = 'all';
    protected $fetch_field = null;
    protected $fetch_del = false;
    protected $insert_table = '';
    protected $insert_row = '';
    protected $update_row = '';
    protected $delete_row = '';
    /**
     * @var array
     */
    protected $select, $join, $where, $logic, $group_by, $having, $having_logic, $order_by;
    protected $limit;
    protected $counter = 1;
    protected $max_allowed_packet = 1000000;
    protected $sub_queries = array();

    /**
     * waProModel constructor.
     * @param waModel|null $model
     * @throws waDbException
     * @throws waException
     */
    public function __construct(waModel $model = null)
    {
        parent::__construct();
        if (isset($model)) {
            $this->id = $model->getTableId();
            $this->table = $model->getTableName();
        }
        if ($this->table && !$this->fields) {
            $this->getMetadata();
        }
        $this->join = $this->table;
        $this->max_allowed_packet = $this->getVariables('max_allowed_packet');
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $properties = array('fields', 'select', 'join', 'where', 'group_by', 'having', 'order_by', 'limit');
        $result = null;
        if (in_array($name, $properties)) {
            $result = $this->$name;
        }
        return $result;
    }

    /**
     * @return array database connect data
     * @throws waException
     */
    protected function getDbSettings()
    {
        return include wa()->getConfig()->getRootPath().'/wa-config/db.php';
    }

    /**
     * @param $query
     * @throws waException
     */
    public function superQuery($query)
    {
        $data_base = $this->getDbSettings();
        $mysqli = new mysqli($data_base['default']['host'], $data_base['default']['user'], $data_base['default']['password'], $data_base['default']['database']);
        $mysqli->set_charset("utf8");
        $mysqli->multi_query($query);
        $mysqli->close();
    }

    /**
     * @throws waException
     */
    public function multiUpdate()
    {
        if (strlen($this->update_row) > 0) {
            $this->superQuery($this->update_row);
            $this->update_row = "";
        }
    }

    /**
     * @throws waException
     */
    public function multiDelete()
    {
        if (strlen($this->delete_row) > 0) {
            $this->superQuery($this->delete_row);
            $this->delete_row = "";
        }
    }

    /**
     * @throws waDbException
     */
    public function bulkInsert() {
        if (strlen($this->insert_row) > 0) {
            $this->query("INSERT IGNORE INTO {$this->table}{$this->insert_table} VALUES {$this->insert_row};");
        }
        $this->insert_row = '';
    }

    /**
     * @param string $name - variable name
     * @return array is_null($name), string isset($name)
     * @throws waDbException
     */
    protected function getVariables($name = null)
    {
        if (is_null($name)) {
            $result = $this->query("SHOW VARIABLES")->fetchAll('Variable_name', true);
        } else {
            $result = $this->query("SHOW VARIABLES LIKE s:var_name", array('var_name' => $name))->fetchAssoc();
            $result = $result['Value'];
        }
        return $result;
    }

    /**
     * @param string $fetch_type - all/assoc/field
     * @param null $fetch_field - column name
     * @param bool $fetch_del
     */
    public function setFetch($fetch_type, $fetch_field = null, $fetch_del = false)
    {
        $this->fetch_type = $fetch_type;
        $this->fetch_field = $fetch_field;
        $this->fetch_del = $fetch_del;
    }

    /**
     * @param $type
     * @throws waDbException
     */
    public function setIsolationLevel($type)
    {
        $types = array(
            0 => "READ UNCOMMITTED",
            1 => "READ COMMITTED",
            2 => "REPEATABLE READ",
            3 => "SERIALIZABLE",
        );
        if (isset($types[$type])) {
            $this->query("SET TRANSACTION ISOLATION LEVEL {$types[$type]};");
        }
    }

    /**
     * @param array $insert_table = array('column',)
     */
    public function setInsertTable(array $insert_table)
    {
        $this->insert_table = "";
        foreach ($insert_table as $col) {
            if (strlen($this->insert_table) > 0) {
                $this->insert_table .= ", ";
            }
            $this->insert_table .= "{$this->escape($col)}";
        }
        $this->insert_table = "({$this->insert_table})";
    }

    /**
     * @param array $insert_data = array('value',)
     * @throws waDbException
     */
    public function setInsertRow(array $insert_data)
    {
        if (strlen($this->insert_row) > $this->max_allowed_packet/4) {
            $this->bulkInsert();
        }
        $insert_row = "";
        foreach ($insert_data as $col) {
            if (strlen($insert_row) > 0) {
                $insert_row .= ", ";
            }
            if (is_null($col)) {
                $insert_row .= "NULL";
            } else {
                $insert_row .= "'{$this->escape($col)}'";
            }
        }
        if (strlen($this->insert_row) > 0) {
            $this->insert_row .= ", ";
        }
        $this->insert_row .= "({$insert_row})";
    }

    /**
     * @param array $delete = array('column' => array('simile' => '=!=<=>=', 'value' => 'val'),)
     * @throws waException
     */

    public function setDeleteRow(array $delete)
    {
        if (strlen($this->delete_row) > $this->max_allowed_packet/4) {
            $this->multiDelete();
        }
        $where = '';
        foreach ($delete as $col => $val) {
            if (strlen($where) > 0) {
                $where .= " AND";
            } else {
                $where .= " WHERE";
            }
            if (is_null($val['value'])) {
                $where .= " {$col} {$val['simile']} NULL";
            } else {
                if (is_array($val['value'])) {
                    $simile = 'IN';
                } else {
                    $simile = $val['simile'];
                }
                $where .= " {$col} {$simile}";
            }
        }
        if (strlen($where) > 0) {
            $this->delete_row .= "DELETE FROM {$this->getTableName()} WHERE {$where};";
        }
    }

    /**
     * @param array $update = array('data' => array('column' => 'value',), 'where' => array('column' => 'value',))
     * @throws waException
     */
    public function setUpdateRow(array $update)
    {
        if (strlen($this->update_row) > $this->max_allowed_packet/4) {
            $this->multiUpdate();
        }
        $update_row = $this->updateRowCreate($update);
        if (!empty($result)) {
            $this->update_row .= "UPDATE {$this->getTableName()} SET {$update_row};";
        }
    }

    /**
     * @param array $update = array('data' => array('column' => 'value',), 'where' => array('column' => 'value',))
     * @return string update row
     */
    public function getUpdateRow(array $update)
    {
        $result = $this->updateRowCreate($update);
        if (!empty($result)) {
            $result = "UPDATE {$this->getTableName()} SET {$result};";
        }
        return $result;
    }

    /**
     * @param array $update
     * @return string
     */
    protected function updateRowCreate($update)
    {
        $result = "";
        if (!empty($update['data']) && !empty($update['where'])) {
            $update_set = "";
            foreach ($update['data'] as $col => $val) {
                if (strlen($update_set) > 0) {
                    $update_set .= ", ";
                }
                $update_set .= "{$this->escape($col)} = '{$this->escape($val)}'";
            }
            $update_where = "";
            foreach ($update['where'] as $col => $val) {
                if (strlen($update_where) > 0) {
                    $update_where .= " AND ";
                }
                $update_where .= "{$this->escape($col)} = '{$this->escape($val)}'";
            }
            $update_where = " WHERE {$update_where}";
            $result = $update_set.$update_where;
        }
        return $result;
    }

    /**
     * @param array $select = array('field' => 'as', 'field2' => array('as1', 'as2')),
     */
    public function setSelect(array $select)
    {
        $this->select = $select;
    }

    /**
     * @return string
     */
    protected function getSelect()
    {
        $select = '';
        if (!empty($this->select)) {
            foreach ($this->select as $col => $as) {
                if ($as) {
                    if (is_array($as)) {
                        foreach ($as as $a) {
                            if (strlen($select) > 0) {
                                $select .= ", ";
                            }
                            $select .= "{$this->escape($col)} AS {$this->escape($a)}";
                        }
                    } else {
                        if (strlen($select) > 0) {
                            $select .= ", ";
                        }
                        $select .= "{$this->escape($col)} AS {$this->escape($as)}";
                    }
                } else {
                    if (strlen($select) > 0) {
                        $select .= ", ";
                    }
                    $select .= "{$this->escape($col)}";
                }
            }
        }
        if (empty($select)) {
            $select = '*';
        }
        return "SELECT {$select}";
    }

    /**
     * @param $sub_query string
     */
    public function setFromSelect($sub_query)
    {
        if (isset($this->sub_queries[$sub_query])) {//TODO
            $this->join = '('.$this->sub_queries[$sub_query].') AS tmp'.$this->counter;
            $this->select = '*';
            $this->where = '';
            $this->having = '';
            $this->group_by = '';
            $this->order_by = '';
            $this->limit = '';
        }
    }

    /**
     * @param array $join = array(array('type' => 'LEFT','left' => 'left_table','right' => 'right_table','on' => array('left_field' => 'right_field',)),)
     */
    public function setJoin(array $join)
    {
        $this->join = $join;
    }

    protected function getJoin()
    {
        $join = $this->table;
        if (!empty($this->join)) {
            foreach ($this->join as $table) {
                $sub_query = '';
                if (empty($table['type'])) {
                    $table['type'] = 'INNER';
                }
                $on = '';
                if (isset($table['left'])) {
                    $left_table = $table['left'];
                } else {
                    $left_table = $this->table;
                }
                if (isset($table['right'])) {
                    $right_table = $table['right'];
                    if (isset($this->sub_queries[$table['right']])) {
                        $sub_query = "({$this->sub_queries[$table['right']]}) AS ";
                    }
                } else {
                    $right_table = $this->table;
                }
                foreach ($table['on'] as $left_field => $right_field) {
                    if (strlen($on) > 0) {
                        $on .= " AND ";
                    }
                    if (is_numeric($left_field)) {
                        $left = $left_field;
                    } else {
                        $left = "{$left_table}.{$left_field}";
                    }
                    if (is_numeric($right_field)) {
                        $right = $right_field;
                    } else {
                        $right = "{$right_table}.{$right_field}";
                    }
                    $on .= "{$left} = {$right}";
                }
                $join .= " {$this->escape($table['type'])} JOIN {$this->escape($sub_query)}{$this->escape($right_table)} ON {$this->escape($on)}";
            }
        }
        return " FROM {$join}";
    }

    /**
     * @param array $where = array('column' => array('simile' => '!=/=/>/</>=/<=','value' => string/int/array),)
     * @param string/array $def_logic = AND/OR/array(
     *    'table.field' => null,
     *    array(
     *      'logic' => 'AND|OR',
     *      'fields' => array(
     *          0 => array(
     *            'logic' => 'AND|OR',
     *            'fields' => array(
     *                'table.field' => null,
     *             ),
     *          ),
     *          'table.field' => null,
     *       ),
     *    )
     * )
     */
    public function setWhere(array $where, $def_logic = 'AND')
    {
        $this->where = $where;
        if (!empty($def_logic)) {
            $this->logic = $def_logic;
        }
    }

    /**
     * @return string
     */
    protected function getWhere()
    {
        $where = '';
        if (is_array($this->logic)) {
            $this->where .= $this->setLogic($this->logic);
        } else {
            foreach ($this->where as $col => $val) {
                $logic = "";
                if (strlen($where) > 0) {
                    $logic = $this->logic;
                }
                $where .= " {$logic} {$this->setWhereValue($col, $val)}";
            }
        }
        if (strlen($where) > 0) {
            $where = ' WHERE'.$where;
        }
        return $where;
    }

    /**
     * @param $logic
     * @param string $def_logic
     * @return string
     */
    protected function setLogic($logic, $def_logic = 'AND')
    {
        $result = "";
        foreach ($logic as $field => $data) {
            if (strlen($result) > 0) {
                $result .= " {$this->escape($def_logic)} ";
            }
            if (isset($this->where[$field])) {
                $result .= "{$this->setWhereValue($field, $this->where[$field])}";
            } else {
                $result .= $this->setLogic($data['fields'], $data['logic']);
            }
        }
        return "({$result})";
    }

    /**
     * @param mixed $value
     * @return string $type
     */
    protected function getValType($value)
    {
        if (is_numeric($value)) {
            if (strpos($value, '.') || strpos($value, ',')) {
                $type = 'f';
            } else {
                $type = 'i';
            }
        } else {
            $type = 's';
        }
        return $type;
    }

    /**
     * @param $col
     * @param $val
     * @return string
     */
    protected function setWhereValue($col, $val)
    {
        $col = trim($col);
        $where = '';
        if (is_null($val['value'])) {
            $where .= "{$col} {$val['simile']} NULL";
        } else {
            $simile = strtoupper($val['simile']);
            if (is_array($val['value'])) {
                $temp = $val['value'];
                $check_type = array_shift($temp);
                if (isset($val['type']) && strlen($val['type']) > 0) {
                    $type = $val['type'];
                } else {
                    $type = $this->getValType($check_type);
                }
                if ($simile == 'BETWEEN') {
                    $this->data["value_{$this->counter}"] = $val['value']['from'];
                    $value = "{$type}:value_{$this->counter} AND ";
                    $this->counter++;
                    $this->data["value_{$this->counter}"] = $val['value']['to'];
                    $value .= "{$type}:value_{$this->counter}";
                } else {
                    $value = "({$type}:value_{$this->counter})";
                    $this->data["value_{$this->counter}"] = $val['value'];
                }
            } else {
                if (isset($val['agr_val'])) {
                    $value = "{$this->escape($val['value'])}";
                } else {
                    $type = $this->getValType($val['value']);
                    $value = "{$type}:value_{$this->counter}";
                    $this->data["value_{$this->counter}"] = $val['value'];
                }
            }
            switch (strtoupper($col)) {
                case 'COUNT':
                    $where .= "COUNT(*) {$simile} {$value}";
                    break;
                case 'MIN':
                    $where .= "MIN() {$simile} {$value}";
                    break;
                case 'MAX':
                    $where .= "MAX() {$simile} {$value}";
                    break;
                case 'AVG':
                    $where .= "AVG() {$simile} {$value}";
                    break;
                default:
                    $where .= "{$col} {$simile} {$value}";
            }
            $this->counter++;
        }
        return $where;
    }

    /**
     * @param $group_by = array('column',)
     */
    public function setGroupBy(array $group_by)
    {
        $this->group_by = $group_by;
    }

    /**
     * @return string
     */
    protected function getGroupBy()
    {
        $group_by = "";
        if (!empty($this->group_by)) {
            foreach ($this->group_by as $group) {
                if (strlen($group_by) > 0) {
                    $group_by .= ", {$this->escape($group)}";
                } else {
                    $group_by .= " GROUP BY {$this->escape($group)}";
                }
            }
        }
        return $group_by;
    }

    /**
     * @param array $having = array('column' => array('simile' => '!=/=/>/</>=/<=','value' => string/int/array),)
     * @param string/array $def_logic = AND/OR/array(
     *    array(
     *      'logic' => 'AND|OR',
     *      'fields' => array(
     *          0 => array(
     *            'logic' => 'AND|OR',
     *            'fields' => array(
     *                'table.field' => null,
     *             ),
     *          ),
     *          'table.field' => null,
     *       ),
     *    )
     * )
     */
    public function setHaving(array $having, $def_logic = 'AND')
    {
        $this->having = $having;
        if (!empty($def_logic)) {
            $this->having_logic = $def_logic;
        }
    }

    /**
     * @return string
     */
    protected function getHaving()
    {
        $having = '';
        if (is_array($this->having_logic)) {
            $having .= $this->setLogic($this->having_logic);
        } else {
            foreach ($this->having as $col => $val) {
                $logic = "";
                if (strlen($having) > 0) {
                    $logic = $this->having_logic;
                }
                $having .= " {$logic} {$this->setWhereValue($col, $val)}";
            }
        }
        if (strlen($having) > 0) {
            $having = ' HAVING'.$having;
        }
        return $having;
    }

    /**
     * @param $order_by = array('column' => 'direction',)
     */
    public function setOrderBy(array $order_by)
    {
        $this->order_by = $order_by;
    }

    /**
     * @return string
     */
    protected function getOrderBy()
    {
        $order_by = "";
        if (!empty($this->order_by)) {
            foreach ($this->order_by as $order => $direction) {
                if (strlen($order_by) > 0) {
                    $order_by .= ", {$this->escape($order)}";
                } else {
                    $order_by .= " ORDER BY {$this->escape($order)}";
                }
                if (isset($direction)) {
                    $order_by .= " {$this->escape($direction)}";
                }
            }
        }
        return $order_by;
    }

    /**
     * @param $length
     * @param $offset
     */
    public function setLimit($length, $offset = null)
    {
        $this->limit['length'] = intval($length);
        $this->limit['offset'] = intval($offset);
    }

    /**
     * @return string
     */
    protected function getLimit()
    {
        $limit = '';
        if (!empty($this->limit['length'])) {
            $limit = $this->limit['length'];
            if (!empty($this->limit['offset'])) {
                $limit = $this->limit['offset'].', '.$this->limit['length'];
            }
            $limit = ' LIMIT '.$limit;
        }
        return $limit;
    }

    /**
     * @param $field
     * @param $id
     * @return bool|mixed
     * @throws waDbException
     */
    public function getFieldById($field, $id)
    {
        $where = "";
        if (is_array($this->id) && is_array($id) && count($id) === count($this->id)) {
            foreach ($this->id as $key => $value) {
                if (strlen($where) > 0) {
                    $where .= " AND";
                }
                $where .= " {$this->escape($value)} = '{$this->escape($id[$key])}'";
            }
        } else {
            $where = "{$this->escape($this->id)} = '{$this->escape($id)}'";
        }
        if (strlen($where) > 0) {
            $where = " WHERE {$where}";
        }
        return $this->query("SELECT {$this->escape($field)} FROM {$this->table}{$where}")->fetchField();
    }

    public function queryReset()
    {
        $this->select = null;
        $this->join = null;
        $this->where = null;
        $this->logic = null;
        $this->group_by = null;
        $this->having = null;
        $this->having_logic = null;
        $this->order_by = null;
        $this->limit = null;
        $this->fetch_field = null;
        $this->fetch_del = false;
        $this->data = array();
        $this->sub_queries = array();
    }

    /**
     * @param null|array $exclude
     * @return string
     */
    protected function getQuery($exclude = null)
    {
        $result = '';
        $directions = array('select', 'join', 'where', 'groupBy', 'having', 'orderBy', 'limit');
        foreach ($directions as $direction) {
            if (!is_array($exclude) || !in_array($direction, $exclude)) {
                $method = 'get'.ucfirst($direction);
                if (method_exists($this, $method)) {
                    $result .= $this->$method();
                }
            }
        }
        return $result;
    }

    /**
     * @param boolean $reset
     * @return array|string|int|mixed all/assoc; string field; default false
     * @throws waDbException
     */
    public function queryRun($reset = true)
    {
        $query = $this->getQuery();
        $result = false;
        if (!empty($query)) {
            switch ($this->fetch_type) {
                case 'all':
                    $result = $this->query($query, $this->data)->fetchAll($this->fetch_field, $this->fetch_del);
                    break;
                case 'assoc':
                    $result = $this->query($query, $this->data)->fetchAssoc();
                    break;
                case 'field':
                    $result = $this->query($query, $this->data)->fetchField();
                    break;
                default:
                    $result = false;
            }
            if ($reset) {
                $this->queryReset();
            }
        }
        return $result;
    }

    public function getSubQuery()
    {
        return array(
            'query' => $this->getQuery(),
            'data' => $this->data,
            'counter' => $this->counter,
        );
    }

    public function setSubQuery($query, $name)
    {
        if (isset($query['query']) && strlen($query['query']) > 0) {
            $this->sub_queries[$name] = $query['query'];
            if (isset($query['data']) && count($query['data']) > 0) {
                $this->data = $query['data'];
                $this->counter = $query['counter'];
            }
        }
    }

    /**
     * @param string $key array key
     * @param string $value array value
     * @param boolean $reset
     * @return array one-dimensional
     * @throws waDbException
     */
    public function getOneDimensional($key, $value, $reset = true)
    {
        $result = $this->query("SELECT {$this->escape($key)}, {$this->escape($value)}".$this->getQuery(array('select')), $this->data)->fetchAll($key, true);
        if ($reset) {
            $this->queryReset();
        }
        return $result;
    }

    /**
     * @param string $type select/insert/update
     */
    public function showQuery($type = 'select')
    {
        $types = array(
            'insert' => "INSERT IGNORE INTO {$this->table}{$this->insert_table} VALUES {$this->insert_row};",
            'update' => $this->update_row,
            'select' => $this->getQuery(),
        );
        $this->setValues($types['select']);
        waLog::log($types[$type], 'waProModel.log');
    }

    /**
     * @param $query
     */
    protected function setValues(&$query)
    {
        $query = str_replace('i:value', 'value', $query);
        $query = str_replace('s:value', 'value', $query);
        $query = str_replace('f:value', 'value', $query);
        foreach ($this->data as $placeholder => $value) {
            if (is_array($value)) {
                $value = '('.implode(', ', $value).')';
            }
            $query = str_replace($placeholder, $value, $query);
        }
    }

    /**
     * @param $set
     * @throws waDbException
     */
    public function groupUpdate($set)
    {
        $set_row = "";
        foreach ($set as $field => $value) {
            if (strlen($set_row) > 0) {
                $set_row .= ",";
            }
            if (is_null($value)) {
                $set_row .= " {$this->escape($field)} = NULL";
            } else {
                $set_row .= " {$this->escape($field)} = '{$this->escape($value)}'";
            }
        }
        if (strlen($set_row) > 0) {
            $this->query("UPDATE {$this->table} SET {$set_row}{$this->where}", $this->data);
        }
    }

    /**
     * @throws waDbException
     */
    public function clear()
    {
        $this->query("DELETE FROM {$this->table}");
        if ($this->id == 'id') {
            $this->query("ALTER TABLE {$this->table} AUTO_INCREMENT = 1;");
        }
    }

    /**
     * @param $id
     * @throws waDbException
     */
    public function deleteByIdLow($id)
    {
        $this->query("DELETE FROM {$this->table} WHERE id <= ".intval($id));
    }

    /**
     * @throws waDbException
     */
    public function deleteBy()
    {
        $this->query("DELETE FROM {$this->table}{$this->getWhere()}", $this->data);
    }

    public function showData()
    {
        waLog::dump($this->data, 'waProModel.log');
    }

    /**
     * @param $app
     * @return array|bool
     * @throws waDbException
     */
    public function getTables($app)
    {
        $result = false;
        if (!empty($app)) {
            $result = $this->query("SHOW TABLES LIKE s:pattern", array('pattern' => $app.'_%'))->fetchAll();
        }
        return $result;
    }
}