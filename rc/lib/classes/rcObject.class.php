<?php

/**
 * Class rcObject
 * @property $id
 * @property $class_name
 * @property $model
 */
class rcObject
{
    /**
     * @var int|array
     */
    protected $id;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $class_name;

    /**
     * @var rcModel
     */
    protected $model;

    /**
     * @var rcLog
     */
    protected $log;

    /**
     * rcObject constructor.
     * @param null $id
     * @param string|null $model_name
     * @throws waException
     */
    public function __construct($id = null, $model_name = null)
    {
        if (empty($this->class_name)) {
            $this->class_name = rcHelper::getClassName($this);
        }
        if (empty($model_name)) {
            $model_name = 'rc' . ucfirst($this->class_name) . 'Model';
        }
        if (class_exists($model_name)) {
            $this->model = new $model_name();
        }
        $this->log = new rcLog($this->class_name);
        $this->setId($id);
    }

    /**
     * @param $name
     * @return mixed|int|string|false
     */
    public function __get($name)
    {
        $get = array('id', 'class_name', 'model');
        $result = false;
        if (in_array($name, $get)) {
            $result = $this->$name;
        }
        return $result;
    }

    /**
     * @param $name
     * @param $value
     * @throws waException
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            if ($name === 'id' && !empty($value)) {
                $this->setId($value);
            }
        }
    }

    /**
     * @param $value
     * @throws waException
     */
    public function setId($value)
    {
        if ($this->model instanceof rcModel) {
            if (is_array($value)) {
                if ($this->data = $this->model->getByField($value)) {
                    $this->id = $this->data['id'];
                } else {
                    $method = 'autoInsert';
                    if (method_exists($this, $method)) {
                        $this->$method($value);
                    }
                }
            } else {
                if ($this->data = $this->model->getById($value)) {
                    $this->id = intval($value);
                }
            }
        }
    }

    /**
     * @param $value
     */
    protected function autoInsert($value) {}

    /**
     * @param $id
     * @param $type
     * @param int $status
     * @return array
     */
    public function setChild($id, $type, $status = 1)
    {
        $result = array('error' => true, 'message' => 'Не найден метод записи');
        $method = 'set'.ucfirst($type);
        if (method_exists($this, $method)) {
            $result = $this->$method($id, $status);
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainActionData()
    {
        $data = rcHelper::getLogType($this->class_name);
        $result = array(
            'module' => $this->class_name,
            'name' => isset($data['cases'][6]) ? $data['cases'][6] : 'Объекты',
        );
        if ($this->model instanceof rcModel) {
            $fields = $this->model->fields;
            if (isset($fields['status'])) {
                $this->model->setFetch('all', 'status', 1);
                $this->model->setSelect(array('COUNT(*)' => null, 'status' => null));
                $this->model->setGroupBy(array('status'));
                $result['statuses'] = rcHelper::getConfigOption('status');
                $result['counts'] = $this->model->queryRun();
            }
        }
        return $result;
    }

    /**
     * @param $tab
     * @return array
     */
    public function getTabData($tab)
    {
        $method = $tab.'Tab';
        $result = array();
        if (method_exists($this, $method)) {
            $result = $this->$method();
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function activeTab()
    {
        return $this->getStatusTab('active');
    }

    /**
     * @return array
     * @throws waException
     */
    public function moderationTab()
    {
        return $this->getStatusTab('moderation');
    }

    /**
     * @return array
     * @throws waException
     */
    public function deleteTab()
    {
        return $this->getStatusTab('delete');
    }

    /**
     * @param $type
     * @return array
     * @throws waException
     */
    protected function getStatusTab($type)
    {
        $statuses = rcHelper::getConfigOption('status', 'code');
        return array(
            'action' => '?module='.$this->class_name.'&action=getList&status='.$statuses[$type]['id'],
            'fields' => rcHelper::getFields($this->class_name),
        );
    }

    /**
     * @param string $method
     * @return array
     */
    public function getFormData($method = '')
    {
        $method = ucfirst(strlen($method) ? $method : 'main').'Tab';
        $data = array();
        if (method_exists($this, $method)) {
            $data = $this->$method();
        }
        return $data;
    }

    /**
     * @param $data
     * @param $method
     * @return mixed
     */
    public function getPopupData($data, $method)
    {
        $method = $method.'PopupData';
        if (method_exists($this, $method)) {
            $this->$method($data);
        }
        return $data;
    }

    /**
     * @param $field
     * @param $value
     * @param null $child_type
     * @param null $child_id
     * @return array
     */
    public function changeField($field, $value, $child_type = null, $child_id = null)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора');
        } else {
            if (empty($child_type)) {
                if (method_exists($this, 'saveField')) {
                    $result = $this->saveField($field, $value);
                } else {
                    $result = array('error' => true, 'message' => 'Ошибка получения метода');
                }
            } else {
                if (empty($child_id)) {
                    $result = array('error' => true, 'message' => 'Ошибка получения идентификатора дочерней записи');
                } else {
                    $method = $child_type.'FieldSave';
                    if (method_exists($this, $method)) {
                        $result = $this->$method($child_id, $field, $value);
                    } else {
                        $result = array('error' => true, 'message' => 'Ошибка получения метода');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return false|string
     * @throws waException
     */
    protected function repeatCheck($data)
    {
        $result = false;
        $where = array();
        $or_logic = array();
        $check_fields = rcHelper::getFields($this->class_name);
        if ($check_fields) {
            foreach ($check_fields as $field => $field_data) {
                if (isset($data[$field]) && isset($field_data['unique'])) {
                    $where[$field] = array('simile' => '=', 'value' => $data[$field]);
                    $or_logic[$field] = null;
                }
            }
            if (!empty($where)) {
                if (empty($this->id)) {
                    $logic = 'OR';
                } else {
                    $where['id'] = array('simile' => '!=', 'value' => $this->id);
                    $logic = array(
                        'id' => null,
                        array(
                            'logic' => 'OR',
                            'fields' => $or_logic,
                        )
                    );
                }
                if ($this->model instanceof rcModel) {
                    $this->model->setWhere($where, $logic);
                    $rows = $this->model->queryRun();
                    if ($rows) {
                        $result = 'Найдены совпадения для уникальных полей:</br>';
                        rcHelper::repeatCheck($result, $check_fields, $data, $rows, 'У '.$this->getNameCase(1).' ');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $status
     * @return array
     * @throws waException
     */
    public function setStatus($status)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора '.$this->getNameCase(1));
        } else {
            $statuses = rcHelper::getConfigOption('statuses', is_numeric($status) ? null : 'code');
            if (empty($statuses[$status])) {
                $result = array('error' => true, 'message' => 'Ошибка идентификации статуса');
            } else {
                if (empty($this->data)) {
                    $result = array('error' => true, 'message' => 'Ошибка получения данных '.$this->getNameCase(1));
                } else {
                    if ($this->data['status'] == $status) {
                        $result = array('error' => true, 'message' => 'Статус '.$this->getNameCase(1).' уже изменён');
                    } else {
                        if ($this->model instanceof rcModel) {
                            $this->model->updateById($this->id, array('status' => $status));
                            $log_data = array(
                                'action' => $statuses[$status]['code'],
                                $this->class_name.'_id' => $this->id,
                                't_'.$this->class_name.'_name' => $this->data['name'],
                                'data_before' => serialize($this->data),
                            );
                            $this->data['status'] = $status;
                            $log_data['data_after'] = serialize($this->data);
                            $this->log->log($log_data);
                        }
                        $result = array('error' => false, 'message' => 'Статус '.$this->getNameCase(1).' успешно обновлён');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param int $case
     * @return mixed|string
     * @throws waException
     */
    protected function getNameCase($case = 0)
    {
        $cases = rcHelper::getConfigOption('object_name_cases');
        if (isset($cases[$this->class_name]['cases'])) {
            $cases = $cases[$this->class_name]['cases'];
        } else {
            $cases = array(
                'объект',
                'объекта',
                'объекту',
                'объекта',
                'объектом',
                'об объекте',
            );
        }
        if (empty($cases[$case])) {
            $case = 0;
        }
        return $cases[$case];
    }

    /**
     * @param array $params
     * @return array
     */
    public function get($params)
    {
        return $this->data;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return isset($this->data['name']) ? $this->data['name'] : null;
    }
}