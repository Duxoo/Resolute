<?php

class rcFranchisee extends rcContact
{
    /**
     * @var rcFranchiseeModel
     */
    protected $model;

    /**
     * @return array|null
     */
    public function getData()
    {
        $result = null;
        if (isset($this->id)) {
            $result['contact'] = $this->contact_model->getById($this->id);
            $result['franchisee'] = $this->data;
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    protected function mainTab()
    {
        if (!empty($this->id)) {
            $result = $this->getData();
        }
        $result['franchisee_fields'] = rcHelper::getFields($this->class_name);
        $result['contact_fields'] = rcHelper::getFields('contact');
        $result['config']['status'] = rcHelper::getConfigOption('status');
        return $result;
    }


    /**
     * @param array $data
     * @return array
     */
    public function save(array $data)
    {
        try {
            $result = rcHelper::validate($data['contact'], 'contact');
            if (!$result['error']) {
                $result = rcHelper::validate($data['franchisee'], $this->class_name);
            }
            if (!$result['error']) {
                if (empty($this->id)) {
                    $this->setId($this->insertData($data));
                    $this->log->log(array(
                        'action' => 'create',
                        'franchisee_id' => $this->id,
                        'data_after' => serialize($this->getData()),
                    ));
                    $result = array('error' => false, 'id' => $this->id, 'reload' => true, 'message' => 'Франчайзи ' . $data['contact']['firstname'] . ' успешно добавлен');
                } else {
                    $franchisee = $this->getData();
                    if (rcHelper::arrayChangeCheck($franchisee, $data)) {
                        $this->contact->save($data['contact']);
                        $this->model->updateById($this->id, $data['franchisee']);
                        $this->log->log(array(
                            'action' => 'edit',
                            'franchisee_id' => $this->id,
                            'data_before' => serialize($franchisee),
                            'data_after' => serialize($this->getData()),
                        ));
                        $result = array('error' => false, 'id' => $this->id, 'message' => 'Франчайзи ' . $data['contact']['firstname'] . ' успешно обновлен');
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
     * @param array $data
     * @return int
     * @throws waException
     */
    protected function insertData(array $data)
    {
        $this->contact->save($data['contact']);
        $id = $this->contact->getId();
        $data['franchisee']['contact_id'] = $id;
        $this->model->insert($data['franchisee']);
        return $id;
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
        $params['franchisee_id'] = $this->id;
        $shopCollection = new rcShopCollection();
        return $shopCollection->getList($params, $status);
    }

    /**
     * @return array
     */
    public function shopsTab()
    {
        return $this->getData();
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
