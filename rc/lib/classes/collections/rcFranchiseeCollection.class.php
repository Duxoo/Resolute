<?php

class rcFranchiseeCollection extends rcContactCollection
{
    /**
     * @var rcFranchiseeModel
     */
    protected $model;

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function shopListSecond($params, $where, $logic)
    {
        $shopFranchiseeModel = new rcShopFranchiseesModel();
        $this->model->setSelect(array('name' => null, 'IF(shop_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopFranchiseeModel->getTableName(), 'on' => array('id' => 'franchisee_id', $params['shop_id'] => 'shop_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $fields
     */
    protected function shopFieldsList(&$fields)
    {
        $name = $fields['name'];
        $fields = array();
        $fields['name'] = $name;
        $fields['checked'] = array('viewed' => true);
    }
}