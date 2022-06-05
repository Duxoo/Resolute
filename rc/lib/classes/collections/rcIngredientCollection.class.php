<?php

class rcIngredientCollection extends rcCollection
{
    /**
     * @var rcIngredientModel
     */
    protected $model;
    /**
     * @var rcSupplierIngredientsModel
     */
    protected $supplierIngredientsModel;

    /**
     * rcIngredientCollection constructor.
     * @param null $hash
     * @param null $model_name
     * @throws waException
     */
    public function __construct($hash = null, $model_name = null)
    {
        $this->supplierIngredientsModel = new rcSupplierIngredientsModel();
        parent::__construct($hash, $model_name);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array $logic
     */
    protected function setListSearch($params, &$where, &$logic)
    {
        $where['name'] = array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%');
        $where['code'] = array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%');
        $logic = array(
            $this->model->getTableName().'.status' => null,
            array(
                'logic' => 'OR',
                'fields' => array(
                    'name' => null,
                    'code' => null,
                )
            )
        );
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setSelect(array('name' => null, 'code' => null, 'price' => null, 'id' => null));
        if (isset($params['supplier_id'])) {
            $t_name = $this->supplierIngredientsModel->getTableName();
            $this->model->setJoin(array(
                array('type' => 'LEFT', 'right' => $t_name, 'on' => array('id' => 'ingredient_id', $params['supplier_id'] => 'supplier_id'))
            ));
            $this->model->setSelect(array('name' => null, 'IF (min_purchase IS NULL, 0, min_purchase)' => 'min_purchase',
                'IF ('.$t_name.'.discrete IS NULL, 0, '.$t_name.'.discrete)' => 'discrete',
                'IF ('.$t_name.'.status IS NULL, 0, '.$t_name.'.status)' => 'status', 'unit' => null, 'dimension' => null, 'id' => null));
        }
        if (isset($params['shop_id'])) {
            $supplyModel = new rcSupplyModel();
            $supplyItemsModel = new rcSupplyItemsModel();
            $supplyModel->setSelect(array('ingredient_id' => null, 'count' => null, 'unit' => null));
            $supplyModel->setJoin(array(
                array('right' => $supplyItemsModel->getTableName(), 'on' => array('id' => 'supply_id'))
            ));
            $supplyModel->setWhere(array('shop_id' => array('simile' => '=', 'value' => $params['shop_id'])));
            $this->model->setSubQuery($supplyModel->getSubQuery(), 'items');
            $this->model->setJoin(array(
                array('type' => 'LEFT', 'right' => 'items', 'on' => array('id' => 'ingredient_id'))
            ));
            $this->model->setSelect(array('name' => null, 'SUM(count)' => 'count', 'id' => null, 'dimension' => null, 'unit' => null));
            $this->model->setGroupBy(array('id'));
        }
    }

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function setListResult(&$result, $params)
    {
        if (isset($params['supplier_id'])) {
            $params['fields'] = array('name' => array('code' => 'name', 'viewed' => true)) + rcHelper::getFields('supplierIngredients');
            $params['config'] = rcHelper::getConfigOption(array('status', 'unit', 'dimension'));
        }
        if (isset($params['shop_id'])) {
            $params['fields'] = array('name' => array('code' => 'name', 'viewed' => true), 'count' => array('code' => 'count', 'viewed' => true));
            $params['config'] = rcHelper::getConfigOption(array('status', 'unit', 'dimension'));
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

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListCount(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['count'] = $view->fetch('string:'.$template);
    }
}