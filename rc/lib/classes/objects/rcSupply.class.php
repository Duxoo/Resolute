<?php

class rcSupply extends rcObject
{
    /**
     * @var rcSupplyModel
     */
    protected $model;

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function save($data)
    {
        $result = array('error' => false, 'message' => 'Данные уже обновлены');
        if (isset($this->id)) {
            if (rcHelper::arrayChangeCheck($this->data, $data)) {
                $log_data['action'] = 'edit';
                $this->model->updateById($this->id, $data);
                $this->setId($this->id);
                $result['message'] = 'Поставка успешно обновлена';
            }
        } else {
            $data['contact_id'] = wa()->getUser()->getId();
            $this->setId($this->model->insert($data));
            $result['message'] = 'Поставка успешно добавлена';
            $result['reload'] = true;
        }
        $result['id'] = $this->id;
        return $result;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = array();
        if (!empty($this->data)) {
            $result = $this->data;
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainTab()
    {
        $result['supply'] = $this->getData();
        if (!empty($result['supply'])) {
            $shop = new rcShop($result['supply']['shop_id']);
            $result['supply']['shop_name'] = $shop->getName();
            $supplier = new rcSupplier($result['supply']['supplier_id']);
            $result['supply']['supplier_name'] = $supplier->getName();
        }
        $result['fields'] = rcHelper::getFields($this->class_name);
        $result['config']['status'] = rcHelper::getConfigOption('status');
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function itemsTab()
    {
        $result['supply'] = $this->getData();
        $result['fields'] = rcHelper::getFields('supplyItems');
        return $result;
    }
}