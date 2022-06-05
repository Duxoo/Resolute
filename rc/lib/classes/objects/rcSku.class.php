<?php

class rcSku extends rcObject
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
        parent::__construct($id, 'rcProductSkuModel');
        $this->productModel = new rcProductModel();
        $this->productIngredientModel = new rcProductIngredientModel();
    }

    /**
     * @return mixed
     * @throws waException
     */
    public function mainTab()
    {
        $sku = $this->data;
        $product = new rcProduct($sku['product_id']);
        $data = $product->getData();
        $ingredients = $this->getSkuIngredients();
        if (!empty($ingredients)) {
            $data['product']['ingredients'] = $ingredients;
            $data['ingredients'] = $ingredients;
        }
        $data['rc_object_type'] = 'sku';
        $data['sku'] = $sku;
        $data['sku_fields'] = rcHelper::getFields('sku');
        $data['config'] = rcHelper::getConfigOption(array('unit', 'dimension'));
        return $data;
    }

    protected function getSkuIngredients()
    {
        $ingredientModel = new rcIngredientModel();
        $ingredientModel->setSelect(array(
            $ingredientModel->getTableName() . '.id' => null,
            'name' => null,
            'code' => null,
            'price' => null,
            'dimension' => null,
            'amount' => null,
            'unit' => null
        ));
        $ingredientModel->setJoin(array(
            array('right' => $this->productIngredientModel->getTableName(), 'on' => array('id' => 'ingredient_id'))
        ));
        $ingredientModel->setWhere(array(
            'sku_id' => array('simile' => '=', 'value' => $this->id)
        ));
        return $ingredientModel->queryRun();
    }

    /**
     * @param $data
     */
    protected function editPopupData(&$data)
    {
        $data += $this->getFormData();
    }

    /**
     * @param $data
     * @throws waException
     */
    public function addTemplateData(&$data)
    {
        $data['sku_fields'] = rcHelper::getFields('sku');
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function setIngredients($data)
    {
        $check = false;
        $this->productIngredientModel->setFetch('all', 'ingredient_id');
        $this->productIngredientModel->setWhere(array(
            'sku_id' => array('simile' => '=', 'value' => 0),
            'product_id' => array('simile' => '=', 'value' => $this->data['product_id']),
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
            $productClass = new rcProduct($this->data['product_id']);
            $product = $productClass->getData();
            $productClass->ingredientsUpdate($data['ingredient'], $product, $this->model->getById($this->id));
        } else {
            $this->productIngredientModel->deleteByField('sku_id', $this->id);
        }
        return array('error' => false, 'message' => 'Состав артикула успешно сохранён');
    }
}