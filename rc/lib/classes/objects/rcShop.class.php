<?php

class rcShop extends rcObject
{
    /**
     * @var rcShopModel
     */
    protected $model;

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->model->getAll();
    }

    /**
     * @param $data
     * @return array
     */
    public function save($data)
    {
        try {
            $result = rcHelper::validate($data, $this->class_name);
            if (!$result['error']) {
                if (empty($this->id)) {
                    $this->setId($this->model->insert($data));
                    $this->log->log(array(
                        'action' => 'create',
                        'shop_id' => $this->id,
                        't_shop_name' => $data['name'],
                        'data_after' => serialize($this->data),
                    ));
                    $result = array('error' => false, 'id' => $this->id, 'message' => 'Точка ' . $data['name'] . ' успешно добавлена');
                } else {
                    $shop = $this->data;
                    if (rcHelper::arrayChangeCheck($shop, $data)) {
                        $this->model->updateById($this->id, $data);
                        $this->setId($this->id);
                        $this->log->log(array(
                            'action' => 'edit',
                            'shop_id' => $this->id,
                            't_shop_name' => $shop['name'],
                            'data_before' => serialize($shop),
                            'data_after' => serialize($this->data),
                        ));
                        $result = array('error' => false, 'id' => $this->id, 'message' => 'Точка ' . $data['name'] . ' успешно обновлена');
                    } else {
                        $result = array('error' => true, 'message' => 'Данные не были изменены');
                    }
                }
            }
        } catch (waException $wa) {
            $result = array('error' => true, 'message' => $wa->getMessage());
        }
        return $result;
    }

    /**
     * @throws waException
     */
    public function delete()
    {
        return $this->setStatus('delete');
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getOfferList($params, $status = null)
    {
        $params['shop_id'] = $this->id;
        $params['list_type'] = $this->class_name;
        $offerCollection = new rcOfferCollection();
        return $offerCollection->getList($params, $status);
    }

    /**
     * @param null $offer_id
     * @return array
     */
    protected function getOffers($offer_id = null)
    {
        $shopOffersModel = new rcShopOffersModel();
        $where = array();
        $shopOffersModel->setFetch('all', isset($offer_id) ? 'shop_id' : 'offer_id', 1);
        $shopOffersModel->setSelect(array('shop_id' => null, 'offer_id' => null));
        if (isset($offer_id)) {
            $where['offer_id'] = array('simile' => '=', 'value' => $offer_id);
        } else {
            if (isset($this->id)) {
                $where['shop_id'] = array('simile' => '=', 'value' => $this->id);
            }
        }
        $shopOffersModel->setWhere($where);
        return (array)$shopOffersModel->queryRun();
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getMotivationList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['shop_id'] = $this->id;
        $motivationCollection = new rcMotivationCollection();
        return $motivationCollection->getList($params, $status);
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getSupplierList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['shop_id'] = $this->id;
        $supplierCollection = new rcSupplierCollection();
        return $supplierCollection->getList($params, $status);
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getFranchiseeList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['shop_id'] = $this->id;
        $franchiseeCollection = new rcFranchiseeCollection();
        return $franchiseeCollection->getList($params, $status);
    }

    /**
     * @param $params
     * @return array
     */
    public function getInventoryDatesList($params)
    {
        $result = array();
        if (!empty($this->id)) {
            $datesModel = new rcInventoryDatesModel();
            $datesModel->setWhere(array('shop_id' => array('simile' => '=', 'value' => $this->id)));
            $datesModel->setOrderBy(array('month * 100 + day' => 'DESC'));
            $result = (array)$datesModel->queryRun();
        }
        return $result;
    }

    /**
     * @param null $franchisee_id
     * @return array
     */
    protected function getFranchisee($franchisee_id = null)
    {
        $shopFranchiseeModel = new rcShopFranchiseesModel();
        $where = array();
        $shopFranchiseeModel->setFetch('all', isset($franchisee_id) ? 'shop_id' : 'franchisee_id', 1);
        $shopFranchiseeModel->setSelect(array('shop_id' => null, 'franchisee_id' => null));
        if (isset($franchisee_id)) {
            $where['franchisee_id'] = array('simile' => '=', 'value' => $franchisee_id);
        } else {
            if (isset($this->id)) {
                $where['shop_id'] = array('simile' => '=', 'value' => $this->id);
            }
        }
        $shopFranchiseeModel->setWhere($where);
        return (array)$shopFranchiseeModel->queryRun();
    }

    /**
     * @param null $motivation_id
     * @return array
     */
    protected function getMotivations($motivation_id = null)
    {
        $shopMotivationsModel = new rcShopMotivationsModel();
        $where = array();
        $shopMotivationsModel->setFetch('all', isset($motivation_id) ? 'shop_id' : 'motivation_id', 1);
        $shopMotivationsModel->setSelect(array('shop_id' => null, 'motivation_id' => null));
        if (isset($motivation_id)) {
            $where['motivation_id'] = array('simile' => '=', 'value' => $motivation_id);
        } else {
            if (isset($this->id)) {
                $where['shop_id'] = array('simile' => '=', 'value' => $this->id);
            }
        }
        $shopMotivationsModel->setWhere($where);
        return (array)$shopMotivationsModel->queryRun();
    }

    /**
     * @return array
     * @throws waException
     */
    protected function mainTab()
    {
        $result['shop'] = $this->getData();
        $result['fields'] = rcHelper::getFields($this->class_name);
        $result['config']['status'] = rcHelper::getConfigOption('status');
        $result['config']['fields'] = rcHelper::getFields('shop');
        return $result;
    }

    /**
     * @return array
     */
    protected function motivationTab()
    {
        return array(
            'shop' => $this->getData(),
        );
    }

    /**
     * @return array
     */
    protected function offerTab()
    {
        return array(
            'shop' => $this->getData(),
        );
    }

    /**
     * @return array
     */
    protected function inventoryDatesTab()
    {
        return array(
            'shop' => $this->getData(),
        );
    }

    /**
     * @return array
     */
    protected function supplierTab()
    {
        return array(
            'shop' => $this->getData(),
        );
    }

    /**
     * @param $offer_id
     * @param $on
     * @return array
     * @throws waException
     */
    protected function setOffer($offer_id, $on)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора точки');
        } else {
            $this->model->setFetch('field');
            $this->model->setSelect(array('name' => null));
            $this->model->setWhere(array('id' => array('simile' => '=', 'value' => $this->id)));
            $shop_name = $this->model->queryRun();
            if (empty($shop_name)) {
                $result = array('error' => true, 'message' => 'Ошибка получения названия точки');
            } else {
                $offerModel = new rcOfferModel();
                $offerModel->setFetch('field');
                $offerModel->setSelect(array('name' => null));
                $offerModel->setWhere(array('id' => array('simile' => '=', 'value' => $offer_id)));
                $offer_name = $offerModel->queryRun();
                if (empty($offer_name)) {
                    $result = array('error' => true, 'message' => 'Ошибка получения названия акции');
                } else {
                    $log_data = array(
                        'offer_id' => $offer_id,
                        'shop_id' => $this->id,
                        't_shop_name' => $shop_name,
                        't_offer_name' => $offer_name,
                    );
                    $shopOffersModel = new rcShopOffersModel();
                    if ($on) {
                        if ($shopOffersModel->getById(array($this->id, $offer_id))) {
                            $result = array('error' => true, 'message' => 'Акция уже доступна для точки');
                        } else {
                            $shopOffersModel->insert(array('offer_id' => $offer_id, 'shop_id' => $this->id));
                            $log_data['action'] = 'on';
                            $result = array('error' => false, 'message' => 'Акция доступна для точки');
                        }
                    } else {
                        if ($shopOffersModel->getById(array($this->id, $offer_id))) {
                            $shopOffersModel->deleteById(array($this->id, $offer_id));
                            $log_data['action'] = 'off';
                            $result = array('error' => false, 'message' => 'Акция заблокирована для точки');
                        } else {
                            $result = array('error' => true, 'message' => 'Акция уже заблокирована для точки');
                        }
                    }
                    if (isset($log_data['action'])) {
                        $log = new rcLog('shopOffers');
                        $log->log($log_data);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $motivation_id
     * @param $on
     * @return array
     * @throws waException
     */
    protected function setMotivation($motivation_id, $on)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора точки');
        } else {
            $this->model->setFetch('field');
            $this->model->setSelect(array('name' => null));
            $this->model->setWhere(array('id' => array('simile' => '=', 'value' => $this->id)));
            $shop_name = $this->model->queryRun();
            if (empty($shop_name)) {
                $result = array('error' => true, 'message' => 'Ошибка получения названия точки');
            } else {
                $motivationModel = new rcMotivationModel();
                $motivationModel->setFetch('field');
                $motivationModel->setSelect(array('name' => null));
                $motivationModel->setWhere(array('id' => array('simile' => '=', 'value' => $motivation_id)));
                $motivation_name = $motivationModel->queryRun();
                if (empty($motivation_name)) {
                    $result = array('error' => true, 'message' => 'Ошибка получения названия мотивации');
                } else {
                    $log_data = array(
                        'motivation_id' => $motivation_id,
                        'shop_id' => $this->id,
                        't_shop_name' => $shop_name,
                        't_motivation_name' => $motivation_name,
                    );
                    $shopMotivationsModel = new rcShopMotivationsModel();
                    if ($on) {
                        if ($shopMotivationsModel->getById(array($this->id, $motivation_id))) {
                            $result = array('error' => true, 'message' => 'Мотивация уже доступна для точки');
                        } else {
                            $shopMotivationsModel->insert(array('shop_id' => $this->id, 'motivation_id' => $motivation_id));
                            $log_data['action'] = 'on';
                            $result = array('error' => false, 'message' => 'Мотивация доступна для точки');
                        }
                    } else {
                        if ($shopMotivationsModel->getById(array($this->id, $motivation_id))) {
                            $shopMotivationsModel->deleteById(array($this->id, $motivation_id));
                            $log_data['action'] = 'off';
                            $result = array('error' => false, 'message' => 'Мотивация заблокирована для точки');
                        } else {
                            $result = array('error' => true, 'message' => 'Мотивация уже заблокирована для точки');
                        }
                    }
                    if (isset($log_data['action'])) {
                        $log = new rcLog('shopMotivations');
                        $log->log($log_data);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $franchisee_id
     * @param $on
     * @return array
     * @throws waException
     */
    protected function setFranchisee($franchisee_id, $on)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора точки');
        } else {
            $this->model->setFetch('field');
            $this->model->setSelect(array('name' => null));
            $this->model->setWhere(array('id' => array('simile' => '=', 'value' => $this->id)));
            $shop_name = $this->data['name'];

            $franchiseeModel = new rcModel(new waContactModel());
            $franchiseeModel->setFetch('field');
            $franchiseeModel->setSelect(array('name' => null));
            $franchiseeModel->setWhere(array('id' => array('simile' => '=', 'value' => $franchisee_id)));
            $franchisee_name = $franchiseeModel->queryRun();
            if (empty($franchisee_name)) {
                $result = array('error' => true, 'message' => 'Ошибка получения имени франчайзи');
            } else {
                $log_data = array(
                    'franchisee_id' => $franchisee_id,
                    'shop_id' => $this->id,
                    't_shop_name' => $shop_name,
                    't_franchisee_name' => $franchisee_name,
                );
                $shopfranchiseesModel = new rcShopFranchiseesModel();
                if ($on) {
                    if ($shopfranchiseesModel->getById(array($this->id, $franchisee_id))) {
                        $result = array('error' => true, 'message' => 'Франчайзи уже владеет точкой');
                    } else {
                        $shopfranchiseesModel->insert(array('shop_id' => $this->id, 'franchisee_id' => $franchisee_id));
                        $log_data['action'] = 'on';
                        $result = array('error' => false, 'message' => 'Франчайзи теперь владеет точкой');
                    }
                } else {
                    if ($shopfranchiseesModel->getById(array($this->id, $franchisee_id))) {
                        $shopfranchiseesModel->deleteById(array($this->id, $franchisee_id));
                        $log_data['action'] = 'off';
                        $result = array('error' => false, 'message' => 'Франчайзи больше не владеет точкой');
                    } else {
                        $result = array('error' => true, 'message' => 'Франчайзи уже не владеет данной точкой');
                    }
                }
                if (isset($log_data['action'])) {
                    $log = new rcLog('shopFranchisees');
                    $log->log($log_data);
                }
            }
        }
        return $result;
    }

    /**
     * @param $supplier_id
     * @param $on
     * @return array
     */
    protected function setSupplier($supplier_id, $on)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора точки');
        } else {
            $shopSupplierModel = new rcShopSuppliersModel();
            if ($on) {
                $shopSupplierModel->insert(array('shop_id' => $this->id, 'supplier_id' => $supplier_id));
                $result = array('error' => false, 'message' => 'Поставщик доступен для точки');
            } else {
                $shopSupplierModel->deleteById(array($this->id, $supplier_id));
                $result = array('error' => false, 'message' => 'Поставщик недоступен для точки');
            }
        }
        return $result;
    }
}
