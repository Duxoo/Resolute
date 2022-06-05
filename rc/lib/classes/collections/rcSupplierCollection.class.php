<?php

class rcSupplierCollection extends rcCollection
{
    /**
     * @var rcSupplierModel
     */
    protected $model;
    /**
     * @var rcSupplierIngredientsModel
     */
    protected $supplierIngredientsModel;

    /**
     * rcSupplier constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->supplierIngredientsModel = new rcSupplierIngredientsModel();
    }

    /**
     * @param array $params
     * @param array $where
     * @param array $logic
     */
    protected function setListSearch($params, &$where, &$logic)
    {
        $where['name'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $where['contact_name'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $where['inn'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $logic = array(
            $this->model->getTableName().'.status' => null,
            array(
                'logic' => 'OR',
                'fields' => array('name' => null, 'contact_name' => null, 'inn' => null)
            )
        );
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setSelect(array('name' => null, 'contact_name' => null, 'id' => null));
        if (isset($params['ingredient_id'])) {
            $t_name = $this->supplierIngredientsModel->getTableName();
            $this->model->setSelect(array('name' => null, 'IF (min_purchase IS NULL, 0, min_purchase)' => 'min_purchase',
                'IF ('.$t_name.'.discrete IS NULL, 0, '.$t_name.'.discrete)' => 'discrete',
                'IF ('.$t_name.'.status IS NULL, 0, '.$t_name.'.status)' => 'status', 'unit' => null, 'id' => null));
            $this->model->setJoin(array(
                array('type' => 'LEFT', 'right' => $t_name, 'on' => array('id' => 'supplier_id', $params['ingredient_id'] => 'ingredient_id'))
            ));
        }
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function shopListSecond($params, &$where, &$logic)
    {
        $shopSuppliersModel = new rcShopSuppliersModel();
        $this->model->setSelect(array('name' => null, 'IF(shop_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopSuppliersModel->getTableName(), 'on' => array('id' => 'supplier_id', $params['shop_id'] => 'shop_id'))
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

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function setListResult(&$result, $params)
    {
        if (isset($params['ingredient_id'])) {
            $params['fields'] = array('name' => array('code' => 'name', 'viewed' => true)) + rcHelper::getFields('supplierIngredients');
            $params['config'] = rcHelper::getConfigOption(array('status', 'unit', 'dimension'));
            $ingredient = new rcIngredient($params['ingredient_id']);
            $params['config']['ingredient'] = $ingredient->getData();
        }
        parent::setListResult($result, $params);
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListMinPurchase(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['min_purchase'] = $view->fetch('string:'.$template);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListDiscrete(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['discrete'] = $view->fetch('string:'.$template);
    }
}