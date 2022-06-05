<?php

class rcMotivation extends rcObject
{
    /**
     * @var rcMotivationModel
     */
    protected $model;
    /**
     * @var rcMotivationDatesModel
     */
    protected $datesModel;
    /**
     * @var rcMotivationItemsModel
     */
    protected $itemsModel;
    /**
     * @var rcMotivationPeriodModel
     */
    protected $periodModel;
    /**
     * @var rcMotivationWeekdaysModel
     */
    protected $weekdaysModel;

    /**
     * rcMotivation constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->datesModel = new rcMotivationDatesModel();
        $this->itemsModel = new rcMotivationItemsModel();
        $this->periodModel = new rcMotivationPeriodModel();
        $this->weekdaysModel = new rcMotivationWeekdaysModel();
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        if (isset($this->id)) {
            // main info todo change
            $result = $this->data;

            // dates
            $this->datesModel->setFetch('all', 'month_day');
            $this->datesModel->setWhere(array(
                    'motivation_id' => array('simile' => '=', 'value' => $this->id))
            );
            $result['dates'] = $this->datesModel->queryRun();

            // items
            $this->itemsModel->setWhere(array(
                    'motivation_id' => array('simile' => '=', 'value' => $this->id))
            );
            $result['items'] = $this->itemsModel->queryRun();

            // period
            $this->periodModel->setWhere(array(
                    'motivation_id' => array('simile' => '=', 'value' => $this->id))
            );
            $result['periods'] = $this->periodModel->queryRun();

            // weekdays
            $this->weekdaysModel->setFetch('all', 'day_id', 1);
            $this->weekdaysModel->setWhere(array(
                    'motivation_id' => array('simile' => '=', 'value' => $this->id))
            );
            $result['weekdays'] = $this->weekdaysModel->queryRun();

            return $result;
        }
        return null;
    }

    /**
     * @param $data
     * @return array|false[]
     * @throws waException
     */
    public function save($data)
    {
        $result = rcHelper::validate($data, $this->class_name);
        if (!$result['error']) {
            if (isset($this->id)) {
                if ($motivation = $this->model->getById($this->id)) {
                    if (rcHelper::arrayChangeCheck($motivation, $data)) {
                        $this->model->updateById($this->id, $data);
                        $this->log->log(array(
                            'action' => 'edit',
                            'motivation_id' => $this->id,
                            't_motivation_name' => $motivation['name'],
                            'data_before' => serialize($motivation),
                            'data_after' => serialize($this->model->getById($this->id)),
                        ));
                        $result = array('error' => false, 'id' => $this->id, 'message' => 'Мотивация успешно обновлена');
                    } else {
                        $result = array('error' => true, 'message' => 'Данные не были изменены');
                    }
                } else {
                    $result = array('error' => true, 'message' => 'Был получен индетификтор, однако, мотивация не была найдена');
                }
            } else {
                $this->id = $this->model->insert($data);
                $this->log->log(array(
                    'action' => 'create',
                    'motivation_id' => $this->id,
                    't_motivation_name' => $data['name'],
                    'data_before' => '',
                    'data_after' => serialize($this->model->getById($this->id)),
                ));
                $result = array('error' => false, 'id' => $this->id, 'message' => 'Мотивация успешно добавлена', 'reload' => true);
            }
        }
        return $result;
    }

    /**
     * @param $products
     * @return array
     * @throws waException
     */
    public function saveProducts($products)
    {
        $result = array('error' => false, 'message' => 'Данные обновлены');
        if (empty($products)) {
            $result = array('error' => true, 'message' => 'Нет данных для обновления');
        } else {
            if (empty($this->id)) {
                $result = array('error' => true, 'message' => 'Ошибка получения мотивации');
            } else {
                $log = new rcLog('motivationItems');
                $motivation = $this->model->getById($this->id);
                $log_data = array(
                    'action' => 'edit',
                    'motivation_id' => $this->id,
                    't_motivation_name' => $motivation['name']
                );
                $temp = $this->getEntities();
                $old = array();
                foreach ($temp as $old_row) {
                    $old[$old_row['type']][$old_row['entity_id']] = $old_row;
                }
                foreach ($products as $key => $quantity) {
                    $quantity = intval($quantity) ? intval($quantity) : 1;
                    $key = explode('_', $key);
                    if (empty($old[$key[0]][$key[1]])) {
                        $result = array('error' => true, 'message' => 'Ошибка получения старых данных');
                        break;
                    } else {
                        if ($old[$key[0]][$key[1]]['quantity'] != $quantity) {
                            $this->itemsModel->updateById(array($this->id, $key[0], $key[1]), array('quantity' => $quantity));
                            $log_data['type'] = $key[0];
                            $log_data['entity_id'] = $key[1];
                            $log_data['t_type_name'] = $old[$key[0]][$key[1]]['type_name'];
                            $log_data['t_entity_name'] = $old[$key[0]][$key[1]]['name'];
                            $log->log($log_data);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function saveTime($data)
    {
        $times = rcHelper::getConfigOption('motivation_timing_type');
        if (empty($times[$data['timing_type']])) {
            $result = array('error' => true, 'message' => 'Ошибка получения типа периода действия');
        } else {
            if (empty($this->id)) {
                $result = array('error' => true, 'message' => 'Ошибка получения идентификатора мотивации');
            } else {
                try {
                    $data['motivation'] = $this->data;
                    if ($data['motivation']['timing_type'] != $data['timing_type']) {
                        $this->model->updateById($this->id, $data);
                        $this->setId($this->id);
                        $this->log->log(array(
                            'action' => 'edit',
                            't_motivation_name' => $data['motivation']['name'],
                            'data_before' => serialize($data['motivation']),
                            'data_after' => serialize($this->data),
                            'motivation_id' => $this->id,
                        ));
                    }
                    $this->saveWeekdays($data);
                    $this->saveDates($data);
                    $this->savePeriods($data);
                    $result = array('error' => false, 'message' => 'Период действия мотивации сохранён');
                } catch (waException $wa) {
                    $result = array('error' => true, 'message' => $wa->getMessage());
                }
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function saveWeekdays($data)
    {
        if (!empty($data['weekdays'])) {
            $this->weekdaysModel->setFetch('all', 'day_id', 1);
            $this->weekdaysModel->setSelect(array('day_id' => null, 'start_time' => null, 'end_time' => null, 'status' => null));
            $this->weekdaysModel->setWhere(array('motivation_id' => array('simile' => '=', 'value' => $this->id)));
            $weekdays = (array)$this->weekdaysModel->queryRun();
            $times = rcHelper::getConfigOption('motivation_timing_type', 'code');
            $log = new rcLog('motivationWeekdays');
            $log_data = array(
                'motivation_id' => $this->id,
                't_motivation_name' => $data['motivation']['name'],
            );
            foreach ($data['weekdays'] as $id => $weekday) {
                if (isset($times['weekdays']['values'][$id]) && strtotime($weekday['start_time']) < strtotime($weekday['end_time'])) {
                    $log_data['action'] = false;
                    $log_data['t_weekday_name'] = $times['weekdays']['values'][$id];
                    $log_data['day_id'] = $id;
                    $log_data['data_after'] = serialize(array('motivation_id' => $this->id, 'day_id' => $id) + $weekday);
                    if (isset($weekdays[$id])) {
                        $weekdays[$id]['start_time'] = substr($weekdays[$id]['start_time'] , 0, -3);
                        $weekdays[$id]['end_time'] = substr($weekdays[$id]['end_time'] , 0, -3);
                        if (rcHelper::arrayChangeCheck($weekdays[$id], $weekday)) {
                            $this->weekdaysModel->updateById(array($this->id, $id), $weekday);
                            $log_data['action'] = 'edit';
                            $log_data['data_before'] = serialize(array('motivation_id' => $this->id, 'day_id' => $id) + $weekdays[$id]);
                        }
                    } else {
                        if (isset($weekday['status'])) {
                            $this->weekdaysModel->insert(array('motivation_id' => $this->id, 'day_id' => $id) + $weekday);
                            $log_data['action'] = 'on';
                        }
                    }
                    if ($log_data['action']) {
                        $log->log($log_data);
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function saveDates($data)
    {
        if (!empty($data['dates'])) {
            $this->datesModel->setFetch('all', 'month_day', 1);
            $this->datesModel->setSelect(array('month_day' => null, 'start_time' => null, 'end_time' => null));
            $this->datesModel->setWhere(array('motivation_id' => array('simile' => '=', 'value' => $this->id)));
            $dates = $this->datesModel->queryRun();
            $log = new rcLog('motivationDates');
            $log_data = array(
                'motivation_id' => $this->id,
                't_motivation_name' => $data['motivation']['name'],
            );
            foreach ($data['dates'] as $date) {
                if (strtotime($date['start_time']) < strtotime($date['end_time'])) {
                    $id = str_replace('.', '', $date['date']);
                    $log_data['action'] = false;
                    $log_data['t_date'] = $date['date'];
                    unset($date['date']);
                    $log_data['month_day'] = $id;
                    $log_data['data_after'] = serialize(array('motivation_id' => $this->id, 'month_day' => $id) + $date);
                    if (isset($dates[$id])) {
                        if (rcHelper::arrayChangeCheck($dates[$id], $date)) {
                            $this->datesModel->updateById(array($this->id, $id), $date);
                            $log_data['action'] = 'edit';
                            $log_data['data_before'] = serialize(array('motivation_id' => $this->id, 'day_id' => $id) + $dates[$id]);
                        }
                    } else {
                        $this->datesModel->insert(array('motivation_id' => $this->id, 'month_day' => $id) + $date);
                        $log_data['action'] = 'create';
                    }
                    if ($log_data['action']) {
                        $log->log($log_data);
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function savePeriods($data)
    {
        if (!empty($data['periods'])) {
            $this->periodModel->setFetch('all', 'id');
            $this->periodModel->setWhere(array('motivation_id' => array('simile' => '=', 'value' => $this->id)));
            $periods = $this->periodModel->queryRun();
            $log = new rcLog('motivationPeriod');
            $log_data = array(
                'motivation_id' => $this->id,
                't_motivation_name' => $data['motivation']['name'],
            );
            foreach ($data['periods'] as $period) {
                if (strtotime($period['start_date']) < strtotime($period['end_date'])) {
                    $log_data['action'] = false;
                    $log_data['t_period'] = $period['start_date'].' - '.$period['end_date'];
                    if (isset($period['id']) && isset($periods[$period['id']])) {
                        if (rcHelper::arrayChangeCheck($periods[$period['id']], $period)) {
                            $this->periodModel->updateById($period['id'], $period);
                            $log_data['action'] = 'edit';
                            $log_data['data_before'] = serialize($periods[$period['id']]);
                        }
                    } else {
                        $period['motivation_id'] = $this->id;
                        $period['id'] = $this->periodModel->insert($period);
                        $log_data['action'] = 'create';
                    }
                    if ($log_data['action']) {
                        $log_data['motivation_period_id'] = $period['id'];
                        $log_data['data_after'] = serialize($this->periodModel->getById($period['id']));
                        $log->log($log_data);
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function entityAdd($data)
    {
        $data['quantity'] = intval($data['quantity']);
        if ($data['quantity'] > 0) {
            try {
                $types = rcHelper::getConfigOption('entity_type');
                $entityModel = $this->getEntityModel($types, $data['type']);
                if ($entityModel) {
                    $entity = $entityModel->getById($data['entity_id']);
                    if ($entity) {
                        $motivation = $this->model->getById($this->id);
                        if ($motivation) {
                            $this->itemsModel->insert($data);
                            $log = new rcLog('motivationItems');
                            $data['action'] = 'create';
                            $data['quantity_after'] = $data['quantity'];
                            $data['t_type_name'] = lcfirst($types[$data['type']]['name']);
                            $data['t_entity_name'] = $entity['name'];
                            $data['t_motivation_name'] = $motivation['name'];
                            $log->log($data);
                            $result = array('error' => false, 'message' => 'Добавление элемента прошло успешно');
                        } else {
                            $result = array('error' => true, 'message' => 'Ошибка получения мотивации');
                        }
                    } else {
                        $result = array('error' => true, 'message' => 'Ошибка получения елемента');
                    }
                } else {
                    $result = array('error' => true, 'message' => 'Ошибка получения типа елемента');
                }
            } catch (waException $wa) {
                $result = array('error' => true, 'message' => $wa->getMessage());
            }
        } else {
            $result = array('error' => true, 'message' => 'Кол-во елементов должно быть больше нуля');
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function entityDelete($data)
    {
        $data = explode('_', $data);
        if (empty($data[1]) || empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора');
        } else {
            if ($motivation = $this->itemsModel->getById(array($this->id, $data[0], $data[1]))) {
                $types = rcHelper::getConfigOption('entity_type');
                $entityModel = $this->getEntityModel($types, $data[0]);
                if ($entityModel) {
                    $entity = $entityModel->getById($motivation['entity_id']);
                    if ($entity) {
                        $motivation_name = $this->model->getById($this->id);
                        $this->itemsModel->deleteById(array($this->id, $data[0], $data[1]));
                        $log = new rcLog('motivationItems');
                        $motivation['action'] = 'delete';
                        $motivation['quantity_before'] = $motivation['quantity'];
                        $motivation['t_type_name'] = lcfirst($types[$motivation['type']]['name']);
                        $motivation['t_entity_name'] = $entity['name'];
                        $motivation['t_motivation_name'] = $motivation_name['name'];
                        $log->log($motivation);
                        $result = array('error' => false, 'message' => 'Удаление элемента прошло успешно');
                    } else {
                        $result = array('error' => true, 'message' => 'Ошибка получения елемента');
                    }
                } else {
                    $result = array('error' => true, 'message' => 'Ошибка получения типа елемента');
                }
            } else {
                $result = array('error' => true, 'message' => 'Ошибка получения записи');
            }
        }
        return $result;
    }

    /**
     * @param $month_day
     * @return array
     * @throws waException
     */
    public function deleteDate($month_day)
    {
        if (isset($this->id)) {
            if ($date = $this->datesModel->getById(array($this->id, $month_day))) {
                $motivation = $this->model->getById($this->id);
                $this->datesModel->deleteById(array($this->id, $month_day));
                $log = new rcLog('motivationDates');
                $log_data = array(
                    'action' => 'delete',
                    'motivation_id' => $this->id,
                    'month_day' => $month_day,
                    't_date' => substr($month_day, 0, 2).'.'.substr($month_day, 2, 2),
                    't_motivation_name' => $motivation['name'],
                    'data_before' => serialize($date),
                );
                $log->log($log_data);
                $result = array('error' => false, 'message' => 'День действия успешно удалён');
            } else {
                $result = array('error' => true, 'message' => 'Ошибка получения дня действия');
            }
        } else {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора мотивации');
        }
        return $result;
    }

    /**
     * @param $id
     * @return array
     * @throws waException
     */
    public function periodDelete($id)
    {
        if (isset($this->id)) {
            if ($period = $this->periodModel->getById($id)) {
                $motivation = $this->model->getById($this->id);
                $this->periodModel->deleteById($id);
                $log = new rcLog('motivationPeriods');
                $log_data = array(
                    'action' => 'delete',
                    'motivation_id' => $this->id,
                    'motivation_period_id' => $id,
                    't_period' => $period['start_date'].' - '.$period['end_date'],
                    't_motivation_name' => $motivation['name'],
                    'data_before' => serialize($period),
                );
                $log->log($log_data);
                $result = array('error' => false, 'message' => 'Период действия успешно удалён');
            } else {
                $result = array('error' => true, 'message' => 'Ошибка получения периода действия');
            }
        } else {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора мотивации');
        }
        return $result;
    }

    /**
     * @return array|null
     * @throws waException
     */
    public function getEntities()
    {
        $result = array();
        if (isset($this->id)) {
            $result = $this->itemsModel->getByField('motivation_id', $this->id, true);
            $types = rcHelper::getConfigOption('entity_type');
            foreach ($result as $row) {
                if (isset($types[$row['type']])) {
                    $types[$row['type']]['ids'][] = $row['entity_id'];
                }
            }
            foreach ($types as $id => $type) {
                if (!empty($types[$id]['ids'])) {
                    $entityModel = $this->getEntityModel($types, $id);
                    $entityModel->setFetch('all', 'id', 1);
                    $entityModel->setSelect(array('id' => null, 'name' => null, 'status' => null));
                    $entityModel->setWhere(array(
                        'id' => array('simile' => 'IN', 'value' => $types[$id]['ids'])
                    ));
                    $types[$id]['names'] = $entityModel->queryRun();
                }
            }
            foreach ($result as $key => $row) {
                if (isset($types[$row['type']]['names'])) {
                    $result[$key]['name'] = $types[$row['type']]['names'][$row['entity_id']]['name'];
                    $result[$key]['status'] = $types[$row['type']]['names'][$row['entity_id']]['status'];
                    $result[$key]['type_name'] = $types[$row['type']]['name'];
                }
            }
        }
        return $result;
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getShopList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['motivation_id'] = $this->id;
        $shopCollection = new rcShopCollection();
        return $shopCollection->getList($params, $status);
    }

    /**
     * @param $type
     * @param null $search
     * @return array
     * @throws waException
     */
    public function getEntitiesSelectTwo($type, $search = null)
    {
        $result = array();
        if (isset($this->id)) {
            $types = rcHelper::getConfigOption('entity_type');
            $entityModel = $this->getEntityModel($types, $type);
            if ($entityModel) {
                $entity_table = $entityModel->getTableName();
                $this->itemsModel->setSelect(array('id' => null, 'name' => 'text'));
                $this->itemsModel->setJoin(array(
                    array('type' => 'RIGHT', 'right' => $entity_table, 'on' => array('entity_id' => 'id', 'type' => $type, 'motivation_id' => $this->id))
                ));
                $where = array('entity_id' => array('simile' => 'IS', 'value' => null));
                if (!empty($search)) {
                    $where['name'] = array('simile' => 'LIKE', 'value' => '%'.$search.'%');
                }
                if ($types[$type]['code'] == 'product' || $types[$type]['code'] == 'addition') {
                    $where[$entity_table.'.type'] = array('simile' => '=', 'value' => $type);
                }
                $this->itemsModel->setWhere($where);
                $result = (array)$this->itemsModel->queryRun();
            }
        }
        return $result;
    }

    /**
     * @param array $types
     * @param int $type
     * @return false|rcModel
     */
    protected function getEntityModel(array $types, $type)
    {
        $result = false;
        if (isset($types[$type])) {
            $entity = 'rc'.ucfirst($types[$type]['code']);
            if (class_exists($entity)) {
                $entity = new $entity();
                if ($entity instanceof rcObject) {
                    if ($entity->model instanceof rcModel) {
                        $result = $entity->model;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function productAddPopupData(&$data)
    {
        if (isset($this->id)) {
            $data['config']['entity_type'] = rcHelper::getConfigOption('entity_type');
        }
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainTab()
    {
        if (isset($this->id)) {
            $result['motivation'] = $this->data;
        }
        $result['fields'] = rcHelper::getFields($this->class_name);
        $result['config'] = rcHelper::getConfigOption(array('motivation_profit_type', 'motivation_condition_type', 'motivation_timing_type', 'status'));
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function productsTab()
    {
        $result['motivation'] = $this->data;
        $result['products'] = $this->getEntities();
        return $result;
    }

    /**
     * @throws waException
     */
    public function timeTab()
    {
        $result['motivation'] = $this->getData();
        $result['config']['motivation_timing_type'] = rcHelper::getConfigOption('motivation_timing_type');
        return $result;
    }

    /**
     * @return array
     */
    public function shopsTab()
    {
        return array(
            'motivation' => $this->data
        );
    }

    /**
     * @param $shop_id
     * @param $on
     * @return array
     * @throws waException
     */
    protected function setShop($shop_id, $on)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора точки');
        } else {
            $shop = new rcShop($shop_id);
            $result = $shop->setChild($this->id, $this->class_name, $on);
        }
        return $result;
    }
}