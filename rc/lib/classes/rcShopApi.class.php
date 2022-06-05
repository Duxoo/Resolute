<?php

class rcShopApi
{
    protected $shop_id;
    protected $result = array('error' => false);
    protected $key;

    /**
     * rcShopApi constructor.
     * @param $shop_id
     * @param null $key
     * @throws waDbException
     * @throws waException
     */
    public function __construct($shop_id, $key = null)
    {
        $shopModel = new rcShopModel();
        if ($shopModel->getById(intval($shop_id))) {
            $this->shop_id = intval($shop_id);
            $this->key = $key;
        }
        $this->check();
    }

    /**
     * auth check
     */
    protected function check()
    {
        if (empty($this->shop_id)) {
            $this->result = array('error' => true, 'Неверный идентификатор точки');
        } else {
            if ($this->key != 'ddd') {
                $this->result = array('error' => true, 'Неверный идентификатор точки');
            }
        }
    }

    /**
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function get()
    {
        if (!$this->result['error']) {
            $productModel = new rcProductModel();
            $this->result['data']['product'] = $productModel->getAll();
            $skuModel = new rcProductSkuModel();
            $this->result['data']['productSku'] = $skuModel->getAll();
            $ingredientModel = new rcIngredientModel();
            $this->result['data']['ingredient'] = $ingredientModel->getAll();
            $screenModel = new rcScreenModel();
            $this->result['data']['screen'] = $screenModel->getAll();
            $screenElementModel = new rcScreenElementModel();
            $this->result['data']['screenElement'] = $screenElementModel->getAll();
            $productAdditionsModel = new rcProductAdditionsModel();
            $productAdditionsModel->setSelect(array('id' => null, 'product_id' => null, 'addition_id' => null));
            $productAdditionsModel->setWhere(array('status' => array('simile' => '=', 'value' => 1)));
            $this->result['data']['productAdditions'] = $productAdditionsModel->queryRun();
        }
        return $this->result;
    }
}