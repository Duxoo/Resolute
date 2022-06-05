<?php

class rcProductCollection extends rcCollection
{
    protected $type = 1;
    /**
     * @var rcProductModel
     */
    protected $model;
    /**
     * @var rcProductSkuModel
     */
    protected $skuModel;
    /**
     * @var rcProductIngredientModel
     */
    protected $productIngredientModel;
    /**
     * @var rcProductAdditionsModel
     */
    protected $additionModel;

    /**
     * rcProductCollection constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->skuModel = new rcProductSkuModel();
        $this->productIngredientModel = new rcProductIngredientModel();
        $this->additionModel = new rcProductAdditionsModel();
    }

    /**
     * @param array $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getList($params, $status = null)
    {
        $params['where']['type'] = array('simile' => '=', 'value' => $this->type);
        return parent::getList($params, $status);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array $logic
     */
    protected function setListSearch($params, &$where, &$logic)
    {
        $where['name'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $where['price'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
        $logic = array(
            'type' => null,
            array(
                'logic' => 'OR',
                'fields' => array('name' => null, 'price' => null)
            )
        );
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        $this->model->setSelect(array('name' => null, 'price' => null, 'id' => null));
    }

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function getPrepare(&$result, $params)
    {
        $statuses = rcHelper::getConfigOption('status', 'code');
        $this->skuModel->setFetch('all', 'product_id', 2);
        $this->skuModel->setWhere(array('status' => array('simile' => '=', 'value' => $statuses['active']['id'])));
        $sku = $this->skuModel->queryRun();
        $ingredientModel = new rcIngredientModel();
        $p_i_table = $this->productIngredientModel->getTableName();
        $this->productIngredientModel->setFetch('all', 'product_id', 2);
        $this->productIngredientModel->setSelect(array($p_i_table.'.*' => null));
        $this->productIngredientModel->setJoin(array(
            array('right' => $ingredientModel->getTableName(), 'on' => array('ingredient_id' => 'id', $statuses['active']['id'] => 'status'))
        ));
        $ingredients = $this->productIngredientModel->queryRun();
        foreach ($result as $key => $product) {
            if (empty($sku[$product['id']]) || empty($ingredients[$product['id']])) {
                unset($result[$key]);
            } else {
                $result[$key]['sku'] = $sku[$product['id']];
                $result[$key]['ingredients'] = $ingredients[$product['id']];
            }
        }
    }
}