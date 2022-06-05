<?php

class rcOffer extends rcObject
{
    /**
     * @var rcOfferModel
     */
    protected $model;
    /**
     * @var rcOfferConditionItemsModel
     */
    protected $conditionItemsModel;
    /**
     * @var rcOfferContactGroupModel
     */
    protected $contactGroupModel;
    /**
     * @var rcOfferDatesModel
     */
    protected $datesModel;
    /**
     * @var rcOfferProfitItemsModel
     */
    protected $profitItemsModel;
    /**
     * @var rcOfferWeekdaysModel
     */
    protected $weekdaysModel;
    /**
     * @var rcShopOffersModel
     */
    protected $shopOffersModel;

        
    /**
     * @var rcOfferExceptionsModel
     */
    protected $exceptionsModel;

    /**
     * rcOffer constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->conditionItemsModel = new rcOfferConditionItemsModel();
        $this->contactGroupModel = new rcOfferContactGroupModel();
        $this->datesModel = new rcOfferDatesModel();
        $this->profitItemsModel = new rcOfferProfitItemsModel();
        $this->weekdaysModel = new rcOfferWeekdaysModel();
        $this->shopOffersModel = new rcShopOffersModel();
        $this->exceptionsModel = new rcOfferExceptionsModel();
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        if (isset($this->id)) {
            $shopModel = new rcShopModel();
            $productModel = new rcProductModel();
            $setModel = new rcSetModel();
            $contactCategoryModel = new waContactCategoryModel();

            // main info
            $result['info'] = $this->model->getById($this->id);

            // shops
            $this->shopOffersModel->setSelect(array(
                $shopModel->getTableName() . '.id' => null,
                'name' => null,
            ));
            $this->shopOffersModel->setJoin(array(
                array('right' => $shopModel->getTableName(), 'on' => array('shop_id' => 'id'))
            ));
            $this->shopOffersModel->setWhere(array(
                'offer_id' => array('simile' => '=', 'value' => $this->id)
            ));
            $result['shops'] = $this->shopOffersModel->queryRun();

            // exceptions
            $this->exceptionsModel->setSelect(array(
                $this->model->getTableName().'.id' => null,
                'name' => null,
            ));
            $this->exceptionsModel->setJoin(array(
                array('right' => $this->model->getTableName(), 'on' => array('offer_exception_id' => 'id'))
            ));
            $this->exceptionsModel->setWhere(array(
                'offer_id' => array('simile' => '=', 'value' => $this->id)
            ));
            $result['exceptions'] = $this->exceptionsModel->queryRun();

            // condition items
            // products
            $this->conditionItemsModel->setSelect(array(
                $productModel->getTableName() . '.id' => 'id',
                'name' => null,
                'offer_id' => null,
                'entity_id' => null,
                $this->conditionItemsModel->getTableName() . '.type' => null,
                'quantity' => null,
            ));
            $this->conditionItemsModel->setJoin(array(
                array('right' => $productModel->getTableName(), 'on' => array('entity_id' => 'id'))
            ));
            $this->conditionItemsModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id),
                    $this->conditionItemsModel->getTableName() . '.type' => array('simile' => '=', 'value' => 1)
                )
            );
            $result['condition_items']['products'] = $this->conditionItemsModel->queryRun();

            //sets
            $this->conditionItemsModel->setSelect(array(
                $setModel->getTableName() . '.id' => 'id',
                'name' => null,
                'offer_id' => null,
                'entity_id' => null,
                'type' => null,
                'quantity' => null,
            ));
            $this->conditionItemsModel->setJoin(array(
                array('right' => $setModel->getTableName(), 'on' => array('entity_id' => 'id'))
            ));
            $this->conditionItemsModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id),
                    'type' => array('simile' => '=', 'value' => 2)
                )
            );
            $result['condition_items']['set'] = $this->conditionItemsModel->queryRun();

            // profit items
            // products
            $this->profitItemsModel->setSelect(array(
                $productModel->getTableName() . '.id' => 'id',
                'name' => null,
                'offer_id' => null,
                'entity_id' => null,
                $this->profitItemsModel->getTableName() . '.type' => null,
                'quantity' => null,
                'profit_type' => null,
                'profit_value' => null
            ));
            $this->profitItemsModel->setJoin(array(
                array('right' => $productModel->getTableName(), 'on' => array('entity_id' => 'id'))
            ));
            $this->profitItemsModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id),
                    $this->profitItemsModel->getTableName() . '.type' => array('simile' => '=', 'value' => 1)
                )
            );
            $result['profit_items']['products'] = $this->profitItemsModel->queryRun();

            //sets
            $this->profitItemsModel->setSelect(array(
                $setModel->getTableName() . '.id' => 'id',
                'name' => null,
                'offer_id' => null,
                'entity_id' => null,
                'type' => null,
                'quantity' => null,
                'profit_type' => null,
                'profit_value' => null
            ));
            $this->profitItemsModel->setJoin(array(
                array('right' => $setModel->getTableName(), 'on' => array('entity_id' => 'id'))
            ));
            $this->profitItemsModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id),
                    'type' => array('simile' => '=', 'value' => 2)
                )
            );
            $result['profit_items']['set'] = $this->profitItemsModel->queryRun();

            // contact group
            $this->contactGroupModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id)
                )
            );
            $result['contact_groups'] = $contactCategoryModel->getAll();
            $result['contact_group'] = $this->contactGroupModel->queryRun();

            // dates
            $this->datesModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id)
                )
            );
            $result['dates'] = $this->datesModel->queryRun();

            // weekdays
            $this->weekdaysModel->setFetch('all', 'day_id');
            $this->weekdaysModel->setWhere(
                array(
                    'offer_id' => array('simile' => '=', 'value' => $this->id)
                )
            );
            $result['weekdays'] = $this->weekdaysModel->queryRun();
            return $result;
        }
        return null;
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function save($data)
    {
        $offer_time = wa()->getConfig()->getOption('offer_time');
        $log_data = array(
            "t_offer_name" => $data["offer"]["name"],
            "date_time" => date('Y-m-d H:i:s'),
            "contact_id" => wa()->getUser()->getId(),
        );
        if ($offer = $this->model->getById($this->id)) {
            if (empty($data["offer"]["name"])) {
                $data["offer"]["name"] = $offer['name'];
                $log_data['t_offer_name'] = $offer['name'];
            }
            if (!empty($data['offer']) && rcHelper::arrayChangeCheck($offer, $data['offer'])) {
                $this->model->updateById($this->id, $data["offer"]);
                $log_data['action'] = 'edit';
                $log_data['offer_id'] = $this->id;
                $log_data['data_before'] = serialize($offer);
                $offer = $this->model->getById($this->id);
                $log_data['data_after'] = serialize($offer);
                $this->log->log($log_data);
            }
            $method = (isset($offer_time[$offer['timing_type']]['code']) ? $offer_time[$offer['timing_type']]['code'] : 'none') . 'Update';
            if (method_exists($this, $method)) {
                $this->$method($data);
            }
            $this->conditionsUpdate($data);
        } else {
            $this->id = $this->model->insert($data['offer']);
            $log_data['action'] = 'create';
            $log_data['offer_id'] = $this->id;
            $log_data['data_after'] = serialize($this->model->getById($this->id));
            $this->log->log($log_data);
        }
        return array('error' => false, 'id' => $this->id, 'message' => 'Данные акции "' . $data['offer']['name'] . '" успешно обновлены');
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function weekdaysUpdate($data)
    {
        if (!empty($data["offer"]["weekdays"])) {
            $log = new rcLog('offerWeekdays');
            $log_data = array(
                "offer_id" => $this->id,
                "t_offer_name" => $data["offer"]["name"],
                "date_time" => date('Y-m-d H:i:s'),
                "contact_id" => wa()->getUser()->getId(),
            );
            $this->weekdaysModel->setFetch('all', 'day_id');
            $this->weekdaysModel->setWhere(array('offer_id' => array('simile' => '=', 'value' => $this->id)));
            $old_weekdays = $this->weekdaysModel->queryRun();
            foreach ($data["offer"]["weekdays"] as $weekday) {
                $log_data['day_id'] = $weekday['day_id'];
                if (empty($weekday['status'])) {
                    $weekday['status'] = 0;
                }
                if (empty($weekday['start_time'])) {
                    $weekday['start_time'] = '00:00';
                }
                if (empty($weekday['end_time'])) {
                    $weekday['end_time'] = '23:59';
                }
                $log_data['data_after'] = serialize($weekday);
                if (empty($old_weekdays[$weekday['day_id']])) {
                    if ($weekday['status'] != 0) {
                        $log_data['data_before'] = '';
                        $log_data['action'] = 'create';
                        $this->weekdaysModel->insert($weekday);
                        $log->log($log_data);
                    }
                } else {
                    if (rcHelper::arrayChangeCheck($old_weekdays[$weekday['day_id']], $weekday)) {
                        $log_data['data_before'] = serialize($old_weekdays[$weekday['day_id']]);
                        if ($weekday['status'] == 0) {
                            $log_data['action'] = 'delete';
                        } else {
                            $log_data['action'] = 'update';
                        }
                        $this->weekdaysModel->updateByField(array('offer_id' => $this->id, 'day_id' => $weekday['day_id']), $weekday);
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
    protected function datesUpdate($data)
    {
        if (isset($data['offer']['dates'])) {
            $old_dates = $this->getDates();
            $log = new rcLog('offerDates');
            $log_data = array(
                'offer_id' => $this->id,
                't_offer_name' => $data['offer']['name'],
                'date_time' => date('Y-m-d H:i:s'),
                'contact_id' => wa()->getUser()->getId(),
            );
            foreach ($data['offer']['dates'] as $date) {
                $date['day'] = intval(substr($date['date'], 0, 2));
                $date['month'] =  intval(substr($date['date'], 3, 2));
                $log_data['day'] = $date['day'];
                $log_data['month'] = $date['month'];
                if (isset($old_dates[$date['month'] . '_' . $date['day']])) {
                    if (rcHelper::arrayChangeCheck($old_dates[$date['month'] . '_' . $date['day']], $date)) {
                        $log_data['data_before'] = serialize($old_dates[$date['month'] . '_' . $date['day']]);
                        $log_data['action'] = 'update';
                        $this->datesModel->updateByField(array('offer_id' => $this->id, 'day' => $date['day'], 'month' => $date['month']), $date);
                        $log_data['data_after'] = serialize($this->datesModel->getByField(array('offer_id' => $this->id, 'day' => $date['day'], 'month' => $date['month'])));
                        $log->log($log_data);
                        unset($old_dates[$date['month'] . '_' . $date['day']]);
                    }
                } else {
                    $log_data['data_before'] = '';
                    $log_data['action'] = 'create';
                    $this->datesModel->insert($date);
                    $log_data['data_after'] = serialize($this->datesModel->getByField(array('offer_id' => $this->id, 'day' => $date['day'], 'month' => $date['month'])));
                    $log->log($log_data);
                }
            }
            if (!empty($old_dates)) {
                foreach ($old_dates as $date) {
                    $log_data['data_before'] = serialize($date);
                    $log_data['data_after'] = '';
                    $log_data['action'] = 'delete';
                    $this->datesModel->deleteByField(array('offer_id' => $this->id, 'day' => $date['day'], 'month' => $date['month']));
                    $log->log($log_data);
                }
            }
        }
    }

    /**
     * @throws waException
     */
    protected function getDates()
    {
        $dates = $this->datesModel->getByField('offer_id', $this->id, true);
        $result = array();
        foreach ($dates as $date) {
            $result[$date['month'] . '_' . $date['day']] = $date;
        }
        return $result;
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function conditionsUpdate($data)
    {
        if (isset($data['offer']['conditions'])) {
            if (isset($data['offer']['conditions']['contact_group'])) {
                $log = new rcLog('offerContactGroup');
                $log_data = array(
                    'offer_id' => $this->id,
                    't_offer_name' => $data['offer']['name'],
                    'date_time' => date('Y-m-d H:i:s'),
                    'contact_id' => wa()->getUser()->getId(),
                );
                $this->contactGroupModel->setFetch('all', 'group_id', 1);
                $this->contactGroupModel->setSelect(array('group_id' => array('group_id', 'tmp')));
                $this->contactGroupModel->setWhere(array('offer_id' => array('simile' => '=', 'value' => $this->id)));
                $old_groups = (array)$this->contactGroupModel->queryRun();
                foreach ($data['offer']['conditions']['contact_group'] as $id) {
                    if (isset($old_groups[$id])) {
                        unset($old_groups[$id]);
                    } else {
                        $this->contactGroupModel->insert(array('group_id' => $id, 'offer_id' => $this->id));
                        $log_data['group_id'] = $id;
                        $log_data['action'] = 'create';
                        $log->log($log_data);
                    }
                }
                if (!empty($old_groups)) {
                    $this->contactGroupModel->deleteByField('group_id', $old_groups);
                    foreach ($old_groups as $id) {
                        $log_data['group_id'] = $id;
                        $log_data['action'] = 'delete';
                        $log->log($log_data);
                    }
                }
            }
        }
    }

    /**
     * @throws waException
     */
    public function delete()
    {
        if ($offer = $this->model->getById($this->id)) {
            $this->model->updateById($this->id, array(
                'status' => 0
            ));
            $this->log->log(array(
                'action' => 'delete',
                'offer_id' => $this->id,
                't_offer_name' => $offer['name'],
                'data_before' => serialize($offer),
                'data_after' => serialize($this->model->getById($this->id)),
                'contact_id' => wa()->getUser()->getId(),
            ));
        }
    }

    /**
     * @param $data
     * @param $item_type
     * @return array
     * @throws waException
     */
    public function addEntity($data, $item_type)
    {
        $product_types = rcHelper::getConfigOption('product_type');
        $result = array('error' => false);
        $model = $item_type . 'ItemsModel';
        if (property_exists($this, $model)) {
            if (isset($this->id)) {
                if (isset($data[$product_types[$data['type']]['code']])) {
                    $offer = $this->model->getById($this->id);
                    $log = new rcLog('offer' . ucfirst($item_type) . 'Items');
                    $data['entity_id'] = $data[$product_types[$data['type']]['code']]['entity_id'];
                    $data['quantity'] = $data[$product_types[$data['type']]['code']]['quantity'];
                    if (isset($data[$product_types[$data['type']]['code']]['profit_type'])) {
                        $data['profit_type'] = $data[$product_types[$data['type']]['code']]['profit_type'];
                        $data['profit_value'] = $data[$product_types[$data['type']]['code']]['profit_value'];
                    }
                    $type = mb_strtolower($product_types[$data['type']]['name']);
                    $log_data = $data + array(
                        't_offer_name' => $offer['name'],
                        'contact_id' => wa()->getUser()->getId(),
                    );
                    $item_type_name = $item_type == 'profit' ? 'бонусов' : 'условий';
                    $log_data['description'] = "%t_user% %t_name% %t_action% {$type} из {$item_type_name} акции '%t_offer_name%'.";
                    $old = $this->$model->getByField(array(
                        'offer_id' => $data['offer_id'],
                        'type' => $data['type'],
                        'entity_id' => $data['entity_id']
                    ));
                    if ($old) {
                        if (rcHelper::arrayChangeCheck($old, $data)) {
                            $this->$model->updateByField(array(
                                'offer_id' => $data['offer_id'],
                                'type' => $data['type'],
                                'entity_id' => $data['entity_id']
                            ), array('quantity' => $data['quantity']));
                            $log_data['action'] = 'edit';
                            $log_data['data_before'] = serialize($old);
                            $log_data['data_after'] = serialize($this->$model->getByField(array(
                                'offer_id' => $data['offer_id'],
                                'type' => $data['type'],
                                'entity_id' => $data['entity_id']
                            )));
                            $log_data['quantity_before'] = $old['quantity'];
                            $log_data['quantity_after'] = $data['quantity'];
                            $log->log($log_data);
                        } else {
                            $item_type_name = $item_type == 'profit' ? 'бонусах' : 'условии';
                            $result = array('error' => true, 'message' => "Такой {$type} с тем же кол-вом уже есть в {$item_type_name} этой акции");
                        }
                    } else {
                        $this->$model->insert($data);
                        $log_data['action'] = 'create';
                        $log_data['quantity_after'] = $data['quantity'];
                        $log_data['data_after'] = serialize($this->$model->getByField(array(
                            'offer_id' => $data['offer_id'],
                            'type' => $data['type'],
                            'entity_id' => $data['entity_id']
                        )));
                        $log->log($log_data);
                    }
                } else {
                    $result = array('error' => true, 'message' => 'Ошибка получения типа элемента');
                }
            } else {
                $result = array('error' => true, 'message' => 'Нет такой сущности');
            }
        } else {
            $result = array('error' => true, 'message' => 'Ошибка определения типа сущности');
        }
        return $result;
    }

    /**
     * @param $type
     * @param $entity_id
     * @param $item_type
     * @return array
     * @throws waException
     */
    public function entityDelete($type, $entity_id, $item_type)
    {
        $result = array('error' => false);
        $model = $item_type . 'ItemsModel';
        if (property_exists($this, $model)) {
            if (isset($this->id) && isset($type) && isset($entity_id)) {
                $old = $this->$model->getByField(array(
                    'offer_id' => $this->id,
                    'type' => $type,
                    'entity_id' => $entity_id,
                ));
                if ($old) {
                    $product_types = rcHelper::getConfigOption('product_type');
                    $type_name = mb_strtolower($product_types[$type]['name']);
                    $offer = $this->model->getById($this->id);
                    $log = new rcLog('offerConditionItems');
                    $item_type_name = $item_type == 'profit' ? 'бонусов' : 'условий';
                    $log_data = array(
                        'offer_id' => $this->id,
                        'type' => $type,
                        'entity_id' => $entity_id,
                        't_offer_name' => $offer['name'],
                        'contact_id' => wa()->getUser()->getId(),
                        'action' => 'delete',
                        'quantity_before' => $old['quantity'],
                        'data_before' => serialize($old),
                        'description' => '%t_user% %t_name% %t_action% "' . $type_name . '" из "' . $item_type_name . '" акции "%t_offer_name%".',
                    );
                    $this->$model->deleteByField(array(
                        'offer_id' => $this->id,
                        'type' => $type,
                        'entity_id' => $entity_id,
                    ));
                    $log->log($log_data);
                }
            } else {
                $result = array('error' => true, 'message' => 'Нет такой сущности');
            }
        } else {
            $result = array('error' => true, 'message' => 'Ошибка определения типа сущности');
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
        $params['offer_id'] = $this->id;
        $params['list_type'] = $this->class_name;
        $shopCollection = new rcShopCollection();
        return $shopCollection->getList($params, $status);
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getExceptionList($params, $status = null)
    {
        $params['offer_id'] = $this->id;
        $params['list_type'] = $this->class_name;
        $offerCollection = new rcOfferCollection();
        return $offerCollection->getList($params, $status);
    }

    /**
     * @return array
     * @throws waException
     */
    protected function mainTab()
    {
        if (isset($this->id)) {
            $result['offer'] = $this->getData();
        }
        $result['fields'] = rcHelper::getFields($this->class_name);
        $result['config']['status'] = rcHelper::getConfigOption('status');
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    protected function timeTab()
    {
        return array(
            'offer' => $this->getData(),
            'offer_time' => wa()->getConfig()->getOption('offer_time'),
        );
    }

    /**
     * @return array
     * @throws waException
     */
    protected function conditionTab()
    {
        return array(
            'offer' => $this->getData(),
            'offer_condition' => wa()->getConfig()->getOption('offer_condition'),
            'offer_condition_type' => wa()->getConfig()->getOption('offer_condition_type'),
        );
    }

    /**
     * @return array
     * @throws waException
     */
    protected function profitTab()
    {
        return array(
            'offer' => $this->getData(),
            'offer_profit' => wa()->getConfig()->getOption('offer_profit'),
            'offer_profit_type' => wa()->getConfig()->getOption('offer_profit_type'),
        );
    }

    /**
     * @return array
     */
    protected function shopsTab()
    {
        return array(
            'offer' => $this->data,
        );
    }

    /**
     * @return array
     */
    protected function exceptionsTab()
    {
        return array(
            'offer' => $this->data,
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

    /**
     * @param $offer_id
     * @param $on
     * @return array
     * @throws waException
     */
    protected function setOffer($offer_id, $on)
    {
        $result = null;
        $log = new rcLog('offerExceptions');

        //check id
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора акции');
            return $result;
        }


        //check offer name
        $offer_name = $this->data['name'];
        if (empty($offer_name)) {
            $result = array('error' => true, 'message' => 'Ошибка получения названия акции');
            return $result;
        }

        //check offer_exception name
        $this->model->setFetch('field');
        $this->model->setSelect(array('name' => null));
        $this->model->setWhere(array('id' => array('simile' => '=', 'value' => $this->id)));
        $offer_exception_name = $this->model->queryRun();

        if (empty($offer_exception_name)) {
            $result = array('error' => true, 'message' => 'Ошибка получения названия ограниченной акции');
            return $result;
        }

        //create log data
        $log_data = array(
            'offer_id' => $this->id,
            'offer_exception_id' => $offer_id,
            't_offer_name' => $offer_name,
            't_offer_exception_name' => $offer_exception_name,
        );

        //main action
        if ($on) {

            if ($this->exceptionsModel->getById(array($this->id, $offer_id))) {
                $result = array('error' => true, 'message' => 'Акция уже заблокирована');
                return $result;
            }

            $this->exceptionsModel->insert(array('offer_id' => $this->id, 'offer_exception_id' => $offer_id));

            // add reverce exception
            if($this->id != $offer_id){
                $this->exceptionsModel->insert(array('offer_id' => $offer_id, 'offer_exception_id' => $this->id));
            }

            $log_data['action'] = 'on';
            $result = array('error' => false, 'message' => "Акция $offer_exception_name заблокирована");
        } else {

            if (!$this->exceptionsModel->getById(array($this->id, $offer_id))) {
                $result = array('error' => true, 'message' => "Это ограничение с акции уже снято");
                return $result;
            }

            $this->exceptionsModel->deleteById(array($this->id, $offer_id));
            $this->exceptionsModel->deleteById(array($offer_id,$this->id));

            $log_data['action'] = 'off';
            $result = array('error' => false, 'message' => 'Акция больше не имеет этого ограничения');
        }
        
        $log->log($log_data);
        return $result;
    }
}
