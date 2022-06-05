<?php

class rcSupplyCollection extends rcCollection
{
    /**
     * @var rcSupplyModel
     */
    protected $model;
    /**
     * @var rcShopModel
     */
    protected $shopModel;
    /**
     * @var rcSupplierModel
     */
    protected $supplierModel;

    /**
     * rcStockCollection constructor.
     * @param null $hash
     * @param null $model_name
     * @throws waException
     */
    public function __construct($hash = null, $model_name = null)
    {
        $this->shopModel = new rcShopModel();
        $this->supplierModel = new rcSupplierModel();
        parent::__construct($hash, $model_name);
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setJoin(array(
            array('right' => $this->shopModel->getTableName(), 'on' => array('shop_id' => 'id')),
            array('right' => $this->supplierModel->getTableName(), 'on' => array('supplier_id' => 'id')),
        ));
        $this->model->setSelect(array(
            $this->model->getTableName().'.id' => null,
            $this->shopModel->getTableName().'.name' => 'shop_name',
            $this->supplierModel->getTableName().'.name' => 'supplier_name',
            'shop_id' => null,
            'supplier_id' => null,
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

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListSupplierName(&$row, $template)
    {
        $view = wa()->getView();
        $view->assign($row);
        $row['supplier_name'] = $view->fetch('string:'.$template);
    }
}