<?php

class rcSupplyItemsCollection extends rcCollection
{
    /**
     * @var rcSupplyItemsModel
     */
    protected $model;
    /**
     * @var rcSupplyItemsHistoryModel
     */
    protected $historyModel;

    public function __construct($hash = null, $model_name = null)
    {
        $this->historyModel = new rcSupplyItemsHistoryModel();
        parent::__construct($hash, $model_name);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     * @throws waException
     */
    protected function setListConditions($params, &$where, &$logic)
    {
        $statuses = rcHelper::getConfigOption('status', 'code');
        $ingredientModel = new rcIngredientModel();
        $i_t_name = $ingredientModel->getTableName();
        $supplyModel = new rcSupplyModel();
        $s_name = $supplyModel->getTableName();
        $supplierModel = new rcSupplierModel();
        $s_t_name = $supplierModel->getTableName();
        switch ($params['list_type']) {
            case 'supply':
                $supplierIngredientsModel = new rcSupplierIngredientsModel();
                $s_i_t_name = $supplierIngredientsModel->getTableName();
                $this->model->setJoin(array(
                    array('type' => 'RIGHT', 'right' => $s_i_t_name, 'on' => array('supply_id' => $params['supply']['id'],
                        'ingredient_id' => 'ingredient_id')),
                    array('left' => $s_i_t_name, 'right' => $i_t_name, 'on' => array('ingredient_id' => 'id')),
                ));
                $where[$s_i_t_name.'.status'] = array('simile' => '=', 'value' => $statuses['active']['id']);
                $where['supplier_id'] = array('simile' => '=', 'value' => $params['supply']['supplier_id']);
                break;
            case 'ingredient':
                $this->model->setJoin(array(
                    array('right' => $s_name, 'on' => array('supply_id' => 'id')),
                    array('left' => $s_name, 'right' => $s_t_name, 'on' => array('supplier_id' => 'id')),
                ));
                $where[$s_name.'.status'] = array('simile' => '=', 'value' => $statuses['active']['id']);
                $where['ingredient_id'] = array('simile' => '=', 'value' => $params['ingredient']['id']);
                break;
            case 'stock':
                $this->model->setJoin(array(
                    array('right' => $s_name, 'on' => array('supply_id' => 'id')),
                    array('right' => $i_t_name, 'on' => array('ingredient_id' => 'id')),
                    array('left' => $s_name, 'right' => $s_t_name, 'on' => array('supplier_id' => 'id')),
                ));
                $where[$s_name.'.status'] = array('simile' => '=', 'value' => $statuses['active']['id']);
                $where[$i_t_name.'.status'] = array('simile' => '=', 'value' => $statuses['active']['id']);
                $where['shop_id'] = array('simile' => '=', 'value' => $params['shop_id']);
                break;
        }
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function setListSearch($params, &$where, &$logic)
    {
//        $where['name'] = array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%');TODO
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setSelect(array('*' => null));
        $name = $this->model->getTableName();
        $ingredientModel = new rcIngredientModel();
        $i_name = $ingredientModel->getTableName();
        $supplyModel = new rcSupplyModel();
        $s_name = $supplyModel->getTableName();
        switch ($params['list_type']) {
            case 'supply':
                $this->model->setSelect(array('name' => null, 'IF (count IS NULL, 0, count)' => 'count',
                    'IF (count IS NULL, 0, start_count)' => 'start_count', $name.'.id' => 'item_id',
                    $name.'.unit' => null, $i_name.'.dimension' => null, $i_name.'.id' => null, 'min_purchase' => null,
                    'discrete' => null, $i_name.'.id ' => 'ingredient_id'));
                break;
            case 'ingredient':
                $this->model->setSelect(array('IF (date_time IS NULL, NOW(), date_time)' => 'sort_date_time', $s_name.'.id' => null,
                    'name' => null, 'count' => null, 'start_count' => null, $name.'.unit' => null, 'supplier_id' => null,
                    $name.'.supply_id' => null, $name.'.ingredient_id' => null, $name.'.id' => 'item_id',
                    'IF (date_time IS NULL, 0, date_time)' => 'date_time'));
                break;
            case 'stock':
                $supplierModel = new rcSupplierModel();
                $this->model->setSelect(array('IF (date_time IS NULL, NOW(), date_time)' => 'sort_date_time', $s_name.'.id' => null,
                    $i_name.'.name' => 'ingredient_name', $supplierModel->getTableName().'.name' => null, 'count' => null,
                    'start_count' => null, $name.'.unit' => null, 'supplier_id' => null, $name.'.supply_id' => null,
                    $name.'.ingredient_id' => null, $name.'.id' => 'item_id', 'IF (date_time IS NULL, 0, date_time)' => 'date_time'));
                break;
        }
    }

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function setListResult(&$result, $params)
    {
        $params['fields'] = array('name' => array('code' => 'name', 'viewed' => true)) + rcHelper::getFields('supplyItems');
        $params['config'] = rcHelper::getConfigOption(array('unit', 'dimension'));
        switch ($params['list_type']) {
            case 'supply':
                $params['config']['tabs']['ingredient'] = 'supply';
                $params['config']['supply'] = $params['supply'];
                break;
            case 'ingredient':
                $params['config']['tabs'] = array('supply' => 'items', 'supplier' => 'ingredients');
                $params['fields'] = array('date_time' => array('code' => 'date_time', 'viewed' => true),
                        'id' => array('code' => 'id', 'viewed' => true)) + $params['fields'];
                $params['config']['ingredient'] = $params['ingredient'];
                break;
            case 'stock':
                $params['config']['tabs'] = array('supply' => 'items', 'supplier' => 'ingredients', 'ingredient' => 'supply');
                $params['fields'] = array('date_time' => array('code' => 'date_time', 'viewed' => true),
                        'id' => array('code' => 'id', 'viewed' => true),
                        'ingredient_name' => array('code' => 'ingredient_name', 'viewed' => true)) + $params['fields'];
                $params['config']['shop_id'] = $params['shop_id'];
                break;
        }
        parent::setListResult($result, $params);
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListName(&$row, $template)
    {
        if (isset($row['config']['supply'])) {
            $row['class_name'] = 'ingredient';
        }
        if (isset($row['config']['ingredient']) || isset($row['config']['shop_id'])) {
            $row['class_name'] = 'supplier';
            $row['id'] = $row['supplier_id'];
        }
        parent::setListName($row, $template);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListId(&$row, $template)
    {
        if (isset($row['config']['ingredient']) || isset($row['config']['shop_id'])) {
            $row['class_name'] = 'supply';
        }
        parent::setListId($row, $template);
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListIngredientName(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['ingredient_name'] = $view->fetch('string:'.$template);
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListCount(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['count'] = $view->fetch('string:'.$template);
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListStartCount(&$row, $template)
    {
        $view = wa()->getView();
        if (isset($row['config']['supply'])) {
            $row['supply_id'] = $row['config']['supply']['id'];
        }
        $view->assign($row);
        $row['start_count'] = $view->fetch('string:'.$template);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListDateTime(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['date_time'] = $view->fetch('string:'.$template);
    }
}