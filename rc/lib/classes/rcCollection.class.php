<?php

class rcCollection
{
    /**
     * @var string|array
     */
    protected $hash;
    /**
     * @var rcModel
     */
    protected $model;
    /**
     * @var string
     */
    protected $class_name;
    /**
     * @var rcLog
     */
    protected $log;

    /**
     * rcCollection constructor.
     * @param $hash
     * @param null $model_name
     * @throws waException
     */
    public function __construct($hash = null, $model_name = null)
    {
        if (empty($this->class_name)) {
            $this->class_name = str_replace('Collection', '', rcHelper::getClassName($this));
        }
        if (empty($model_name)) {
            $model_name = 'rc' . ucfirst($this->class_name) . 'Model';
        }
        if (class_exists($model_name)) {
            $this->model = new $model_name();
        }
        $this->log = new rcLog($this->class_name);
        $this->setHash($hash);
    }

    /**
     * @param $hash
     */
    protected function setHash($hash)
    {
        if (!empty($hash)) {
            $this->hash = $hash;
        }
    }

    /**
     * @param null $search
     * @param null $ids
     * @param null $id 1+34+5
     * @return array
     * @throws waException
     * @throws waDbException
     */
    public function getListForSelect($search = null, $ids = null, $id = null)
    {
        $result = array();
        if ($this->model instanceof rcModel && isset($this->model->fields['id']) && isset($this->model->fields['name'])) {
            $this->model->setSelect(array('id' => null, 'name' => 'text'));
            $this->setWhereForSelect($search, $ids, $id);
            $result = (array)$this->model->queryRun();
        }
        return $result;
    }

    /**
     * @param string|null $search
     * @param string|null $ids  1+4+66
     * @param int|null $id
     * @throws waException
     */
    protected function setWhereForSelect($search = null, $ids = null, $id = null)
    {
        if (isset($this->model->fields['status'])) {
            $statuses = rcHelper::getConfigOption('status', 'code');
            $where = array('status' => array('simile' => '=', 'value' => $statuses['active']['id']));
        }
        if (!empty($ids)) {
            $ids = explode('+', $ids);
            if (!empty($id)) {
                $check = array_search($id, $ids);
                if ($check !== false) {
                    unset($ids[$check]);
                }
            }
            if (!empty($ids)) {
                $where['id'] = array('simile' => 'NOT IN', 'value' => $ids);
            }
        }
        if (!empty($search)) {
            $where = array('name' => array('simile' => 'LIKE', 'value' => '%'.htmlentities($search, ENT_QUOTES).'%'));
        }
        $this->model->setWhere(isset($where) ? $where : array());
    }

    /**
     * @param array $params
     * @param null|int $status
     * @return array
     * @throws waException
     */
    public function getList($params, $status = null)
    {
        $result = array('data' => array(), 'recordsFiltered' => 0, 'recordsTotal' => 0);
        $this->model->setFetch('field');
        $this->model->setSelect(array('COUNT(*)' => null));
        $where = empty($params['where']) ? array() : $params['where'];
        if (isset($status)) {
            $where[$this->model->getTableName().'.status'] = array('simile' => '=', 'value' => $status);
        }
        $logic = 'AND';
        $this->setListConditions($params, $where, $logic);
        $this->model->setWhere($where, $logic);
        $result['recordsTotal'] = $this->model->queryRun(false);
        if (empty($params['search'])) {
            $result['recordsFiltered'] = $result['recordsTotal'];
        } else {
            $this->setListSearch($params, $where, $logic);
            if (isset($params['list_type'])) {
                $method = $params['list_type'].'List';
                if (method_exists($this, $method)) {
                    $this->$method($params, $where, $logic);
                }
            }
            $this->model->setWhere($where, $logic);
            $result['recordsFiltered'] = $this->model->queryRun(false);
        }
        $this->model->setFetch('all');
        $this->setListSelect($params);
        if (isset($params['list_type'])) {
            $method = $params['list_type'].'ListSecond';
            if (method_exists($this, $method)) {
                $this->$method($params, $where, $logic);
            }
        }
        $this->model->setLimit($params['length'], $params['start']);
        if (isset($params['column'])) {
            $this->model->setOrderBy(array($params['column'] + 1 => $params['direction']));
        }
        $result['base_data'] = $this->model->queryRun();
        $this->setListResult($result, $params);
        return $result;
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function setListConditions($params, &$where, &$logic) {}

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function setListSearch($params, &$where, &$logic)
    {
        $where['name'] = array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%');
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setSelect(array('name' => null, 'id' => null));
    }

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function setListResult(&$result, $params)
    {
        if (!empty($result['base_data'])) {
            $col_templates = array();
            $fields = empty($params['fields']) ? rcHelper::getFields($this->class_name) : $params['fields'];
            if (isset($params['list_type'])) {
                $method = $params['list_type'].'FieldsList';
                if (method_exists($this, $method)) {
                    $this->$method($fields);
                }
            }
            foreach ($result['base_data'] as $key => $row) {
                $current_row = array();
                foreach ($fields as $code => $field) {
                    if (isset($field['viewed'])) {
                        $method = 'setList'.ucfirst(rcHelper::getMethodByName($code));
                        if (method_exists($this, $method)) {
                            $this->setListColTemplate($col_templates, $code);
                            $row['config'] = empty($params['config']) ? null : $params['config'];
                            $row['app_path'] = wa()->getAppPath('', 'rc');
                            $this->$method($row, $col_templates[$code]);
                            if (isset($row[$code])) {
                                array_push($current_row, $row[$code]);
                            }
                        } else {
                            if (isset($row[$code])) {
                                array_push($current_row, htmlspecialchars($row[$code], ENT_QUOTES));
                            }
                        }
                    }
                }
                array_push($result['data'], $current_row);
            }
            unset($result['base_data']);
        }
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListName(&$row, $template)
    {
        $view = wa()->getView();
        if (empty($row['class_name'])) {
            $row['class_name'] = $this->class_name;
        }
        $view->assign($row);
        $row['name'] = $view->fetch('string:'.$template);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListId(&$row, $template)
    {
        $view = wa()->getView();
        if (empty($row['class_name'])) {
            $row['class_name'] = $this->class_name;
        }
        $view->assign($row);
        $row['id'] = $view->fetch('string:'.$template);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListStatus(&$row, $template)
    {
        $view = wa()->getView();
        if (empty($row['class_name'])) {
            $row['class_name'] = $this->class_name;
        }
        $view->assign($row);
        $row['status'] = $view->fetch('string:'.$template);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListChecked(&$row, $template)
    {
        $view = wa()->getView();
        $row['app_path'] = wa()->getAppPath('', 'rc');
        $view->assign($row);
        $row['checked'] = $view->fetch('string:'.$template);
    }

    /**
     * @param $row
     * @param $template
     */
    protected function setListPrice(&$row, $template)
    {
        $row['price'] = wa_currency($row['price'], '').' ₽';
    }

    /**
     * @param array $col_templates
     * @param string $col_name
     * @throws waException
     */
    protected function setListColTemplate(&$col_templates, $col_name)
    {
        if (empty($col_templates[$col_name])) {
            $col_templates[$col_name] = 'Шаблон не найден';
            $path = wa()->getAppPath('templates/datatable/'.$this->class_name.'/'.$col_name.'.html', 'rc');
            if (!file_exists($path)) {
                $path = wa()->getAppPath('templates/datatable/'.$col_name.'.html', 'rc');
            }
            if (file_exists($path)) {
                $col_templates[$col_name] = file_get_contents($path);
            }
        }
    }

    /**
     * @param array $params
     * @return array
     * @throws waException
     */
    public function get($params)
    {
        $fields = $this->model->fields;
        $select = array();
        $exclude = array('shop_id', 'status');
        foreach ($fields as $field => $val) {
            if (!in_array($field, $exclude)) {
                $select[$field] = null;
            }
        }
        $where = array();
        if (isset($fields['status'])) {
            $statuses = rcHelper::getConfigOption('status', 'code');
            $where['status'] = array('simile' => '=', 'value' => $statuses['active']['id']);
        }
        if (isset($fields['shop_id']) && !empty($params['shop_id'])) {
            $where['shop_id'] = array('simile' => '=', 'value' => $params['shop_id']);
        }
        $this->model->setSelect($select);
        $this->model->setWhere($where);
        $result = $this->model->queryRun();
        $this->getPrepare($result, $params);
        return $result;
    }

    /**
     * @param array $result
     * @param array $params
     */
    protected function getPrepare(&$result, $params) {}
}