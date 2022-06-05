<?php

class rcOrder extends rcObject
{
    /**
     * @var rcOrderModel
     */
    protected $model;
    /**
     * @var rcOrderItemsModel
     */
    protected $itemsModel;
    /**
     * @var rcOrderWorkersModel
     */
    protected $workersModel;

    /**
     * rcOrder constructor.
     * @param null $id
     * @param null $model_name
     * @throws waException
     */
    public function __construct($id = null, $model_name = null)
    {
        $this->itemsModel = new rcOrderItemsModel();
        $this->workersModel = new rcOrderWorkersModel();
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
        if (isset($this->id)) {
            if (rcHelper::arrayChangeCheck($this->data, $data)) {
                $this->model->updateById($this->id, $data);
                $this->setId($this->id);
                $result['message'] = 'Заказ успешно обновлён';
            }
        } else {
            $result = array('error' => true, 'message' => 'Заказ не найден');
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

    public function mainActionData()
    {
        $result = parent::mainActionData();
        $result['not_added'] = true;
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainTab()
    {
        $result['order'] = $this->getData();
        if (!empty($result['order'])) {
            $shop = new rcShop($result['order']['shop_id']);
            $result['order']['shop_name'] = $shop->getName();
        }
        $result['fields'] = rcHelper::getFields($this->class_name);
        $result['config']['status'] = rcHelper::getConfigOption('status');
        $result['config']['payment_type'] = rcHelper::getConfigOption('payment_type');
        return $result;
    }

    /**
     * @return array
     */
    public function itemsTab()
    {
        $result['order'] = $this->getData();
        $this->itemsModel->setWhere(array('order_id' => array('simile' => '=', 'value' => $this->id)));
        $result['items'] = $this->itemsModel->queryRun();
        return $result;
    }
}