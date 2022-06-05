<?php

class rcOrderCollection extends rcCollection
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
     * rcStockCollection constructor.
     * @param null $hash
     * @param null $model_name
     * @throws waException
     */
    public function __construct($hash = null, $model_name = null)
    {
        $this->itemsModel = new rcOrderItemsModel();
        $this->workersModel = new rcOrderWorkersModel();
        parent::__construct($hash, $model_name);
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $shopModel = new rcShopModel();
        $this->model->setJoin(array(
            array('right' => $shopModel->getTableName(), 'on' => array('shop_id' => 'id')),
        ));
        $this->model->setSelect(array(
            $this->model->getTableName().'.id' => null,
            $shopModel->getTableName().'.name' => 'shop_name',
            'price' => null,
            'date_time' => null,
            'shop_id' => null,
        ));
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListShopName(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['shop_name'] = $view->fetch('string:'.$template);
    }
}