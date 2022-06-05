<?php

class rcAddition extends rcProduct
{
    /**
     * rcAddition constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        $this->class_name = 'product';
        $this->type = 2;
        parent::__construct($id);
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainActionData()
    {
        $result = parent::mainActionData();
        $result['module'] = 'addition';
        return $result;
    }

    /**
     * @param $type
     * @return array
     * @throws waException
     */
    protected function getStatusTab($type)
    {
        $this->class_name = 'addition';
        return parent::getStatusTab($type);
    }

    /**
     * @param array $params
     * @return array|false
     */
    public function get($params)
    {
        $result = false;
        if (!empty($this->id)) {
            $addition_table = $this->additionModel->getTableName();
            $shopAdditionsModel = new rcShopProductAdditionsModel();
            $this->additionModel->setSelect(array($addition_table.'.*' => null, 'price' => null));
            $this->additionModel->setJoin(array(
                array('type' => 'LEFT', 'right' => $shopAdditionsModel->getTableName(), 'on' => array(
                    'id' => 'product_addition_id',
                    $params['shop_id'] => 'shop_id',
                ))
            ));
            $this->additionModel->setWhere(array(
                'status' => array('simile' => '=', 'value' => 1),
                'addition_id' => array('simile' => '=', 'value' => $this->id),
            ));
            return $this->additionModel->queryRun();
        }
        return $result;
    }
}