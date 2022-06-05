<?php

class rcShopCollection extends rcCollection
{
    /**
     * @var rcShopModel
     */
    protected $model;

    /**
     * @param array $params
     * @param array $where
     * @param array $logic
     */
    protected function setListSearch($params, &$where, &$logic)
    {
        $where['name'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $where['address'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $where['code'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $logic = array(
            $this->model->getTableName().'.status' => null,
            array(
                'logic' => 'OR',
                'fields' => array('name' => null, 'address' => null, 'code' => null)
            )
        );
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setSelect(array('name' => null, 'code' => null, 'rent' => null, 'terminal_mac' => null, 'id' => null));
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function supplierListSecond($params, &$where, &$logic)
    {
        $shopSuppliersModel = new rcShopSuppliersModel();
        $this->model->setSelect(array('name' => null, 'IF(supplier_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopSuppliersModel->getTableName(), 'on' => array('id' => 'shop_id', $params['supplier_id'] => 'supplier_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function offerListSecond($params, &$where, &$logic)
    {
        $shopOffersModel = new rcShopOffersModel();
        $this->model->setSelect(array('name' => null, 'IF(offer_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopOffersModel->getTableName(), 'on' => array('id' => 'shop_id', $params['offer_id'] => 'offer_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $fields
     */
    protected function offerFieldsList(&$fields)
    {
        $name = $fields['name'];
        $fields = array();
        $fields['name'] = $name;
        $fields['checked'] = array('viewed' => true);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function motivationListSecond($params, $where, $logic)
    {
        $shopMotivationsModel = new rcShopMotivationsModel();
        $this->model->setSelect(array('name' => null, 'IF(motivation_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopMotivationsModel->getTableName(), 'on' => array('id' => 'shop_id', $params['motivation_id'] => 'motivation_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $fields
     */
    protected function motivationFieldsList(&$fields)
    {
        $name = $fields['name'];
        $fields = array();
        $fields['name'] = $name;
        $fields['checked'] = array('viewed' => true);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function franchiseeListSecond($params, $where, $logic)
    {
        $shopFranchiseeModel = new rcShopFranchiseesModel();
        $this->model->setSelect(array('name' => null, 'IF(franchisee_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopFranchiseeModel->getTableName(), 'on' => array('id' => 'shop_id', $params['franchisee_id'] => 'franchisee_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $fields
     */
    protected function supplierFieldsList(&$fields)
    {
        $name = $fields['name'];
        $fields = array();
        $fields['name'] = $name;
        $fields['checked'] = array('viewed' => true);
    }

    /**
     * @param array $fields
     */
    protected function franchiseeFieldsList(&$fields)
    {
        $name = $fields['name'];
        $fields = array();
        $fields['name'] = $name;
        $fields['checked'] = array('viewed' => true);
    }

    /**
     * @param $row
     * @param $template
     */
    protected function setListRent(&$row, $template)
    {
        $row['rent'] = wa_currency($row['rent'], '').' ₽';
    }
}