<?php

class rcAdditionCollection extends rcProductCollection
{
    /**
     * rcAdditionCollection constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        $this->class_name = 'product';
        $this->type = 2;
        parent::__construct($hash);
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListName(&$row, $template)
    {
        $row['class_name'] = 'addition';
        parent::setListName($row, $template);
    }

    /**
     * @param array $params
     * @return array|false|int|mixed|string
     * @throws waException
     */
    public function get($params)
    {
        $statuses = rcHelper::getConfigOption('status', 'code');
        $this->model->setFetch('all', 'id', 1);
        $this->model->setSelect(array('id' => array('id', 'tmp')));
        $this->model->setWhere(array('status' => array('simile' => '=', 'value' => $statuses['active']['id'])));
        $product_ids = $this->model->queryRun();
        if (empty($product_ids)) {
            $result = array();
        } else {
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
                'status' => array('simile' => '=', 'value' => $statuses['active']['id']),
                'product_id' => array('simile' => 'IN', 'value' => $product_ids),
                'addition_id' => array('simile' => 'IN', 'value' => $product_ids),
            ));
            $result = $this->additionModel->queryRun();
        }
        return $result;
    }
}