<?php

class rcSku
{
    protected $id;
    /**
     * @var rcProductSkuModel
     */
    protected $model;
    /**
     * @var rcProductModel
     */
    protected $productModel;
    /**
     * @var rcProductIngredientModel
     */
    protected $productIngredientModel;

    public function __construct($id = null)
    {
        $this->model = new rcProductSkuModel();
        $this->productModel = new rcProductModel();
        $this->productIngredientModel = new rcProductIngredientModel();
        if (!empty($id)) {
            $this->id = intval($id);
        }
    }

    /**
     * @return mixed
     * @throws waDbException
     * @throws waException
     */
    public function getFormData()
    {
        $sku = $this->model->getById($this->id);
        $product = new rcProduct($sku['product_id']);
        $data['product'] = $product->get();
        $ingredients = $this->getSkuIngredients();
        if (!empty($ingredients)) {
            $data['product']['ingredients'] = $ingredients;
        }
        $data['rc_object_type'] = 'sku';
        $data['sku'] = $sku;
        $data['unit_settings'] = wa()->getConfig()->getOption("unit");
        $data['dimension_settings'] = wa()->getConfig()->getOption("dimension");
        return $data;
    }

    protected function getSkuIngredients()
    {
        $ingredientModel = new rcIngredientModel();
        $ingredientModel->setSelect(array(
            $ingredientModel->getTableName() . ".id" => null,
            "name" => null,
            "code" => null,
            "price" => null,
            "dimension_id" => null,
            "amount" => null,
            "unit" => null
        ));
        $ingredientModel->setJoin(array(
            array('right' => $this->productIngredientModel->getTableName(), 'on' => array('id' => 'ingredient_id'))
        ));
        $ingredientModel->setWhere(array(
            "sku_id" => array("simile" => "=", "value" => $this->id)
        ));
        return $ingredientModel->queryRun();
    }

    public function getPopupData(&$data, $method)
    {
        $method = $method.'PopupData';
        if (method_exists($this, $method)) {
            $this->$method($data);
        }
        return $data;
    }

    /**
     * @param $data
     * @throws waDbException
     * @throws waException
     */
    protected function editPopupData(&$data)
    {
        $data += $this->getFormData();
    }

    /**
     * @param $data
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function setIngredients($data)
    {
        $check = false;
        $this->productIngredientModel->setFetch('all', 'ingredient_id');
        $this->productIngredientModel->setWhere(array(
            'sku_id' => array('simile' => '=', 'value' => 0),
            'product_id' => array('simile' => '=', 'value' => $data['product_id']),
        ));
        $product_ingredients = $this->productIngredientModel->queryRun();
        foreach ($data['ingredient'] as $ingredient) {
            if (rcHelper::arrayChangeCheck($product_ingredients[$ingredient['ingredient_id']], $ingredient)) {
                $check = true;
                break;
            } else {
                unset($product_ingredients[$ingredient['ingredient_id']]);
            }
        }
        if ($check || count($product_ingredients) > 0) {
            $productClass = new rcProduct($data['product_id']);
            $product = $productClass->get();
            $productClass->ingredientsUpdate($data['ingredient'], $product, $this->model->getById($this->id));
        } else {
            $this->productIngredientModel->deleteByField('sku_id', $this->id);
        }
        return array('error' => false, 'message' => 'Состав артикула успешно сохранён');
    }
}