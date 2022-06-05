<?php

class rcInventory extends rcObject
{
    /**
     * @var rcInventoryModel
     */
    protected $model;
    /**
     * @var rcInventoryDatesModel
     */
    protected $datesModel;

    /**
     * rcInventory constructor.
     * @param null $id
     * @param null $model_name
     * @throws waException
     */
    public function __construct($id = null, $model_name = null)
    {
        $this->datesModel = new rcInventoryDatesModel();
        parent::__construct($id, $model_name);
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function save($data)
    {
        $result = array('error' => false, 'message' => 'Данные уже обновлены');
        $data['edit_date_time'] = date('Y-m-d H:i:s');
        if (isset($this->id)) {
            if (rcHelper::arrayChangeCheck($this->data, $data)) {
                $this->model->updateById($this->id, $data);
                $this->setId($this->id);
                $result['message'] = 'Инвентаризация успешно обновлена';
            }
        } else {
            $data['date_time'] = date('Y-m-d H:i:s');//TODO
            $data['contact_id'] = wa()->getUser()->getId();
            $this->setId($this->model->insert($data));
            $result['message'] = 'Инвентаризация успешно добавлена';
            $result['reload'] = true;
        }
        $result['id'] = $this->id;
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function getData()
    {
        $result = array();
        if (!empty($this->data)) {
            $result = $this->data;
            $contact = new waContact($this->data['contact_id']);
            $result['contact_name'] = $contact->getName();
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainActionData()
    {
        $result = parent::mainActionData();
        $result['fields'] = rcHelper::getFields($this->class_name);
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainTab()
    {
        $result['inventory'] = $this->getData();
        if (!empty($result['inventory'])) {
            $shop = new rcShop($result['inventory']['shop_id']);
            $result['inventory']['shop_name'] = $shop->getName();
        }
        $result['config']['inventory_type'] = rcHelper::getConfigOption('inventory_type');
        $result['fields'] = rcHelper::getFields($this->class_name);
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function itemsTab()
    {
        $result['inventory'] = $this->getData();
        return $result;
    }
}