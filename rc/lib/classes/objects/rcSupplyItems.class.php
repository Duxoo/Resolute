<?php

class rcSupplyItems extends rcObject
{
    /**
     * @var rcSupplyItemsModel
     */
    protected $model;
    /**
     * @var rcSupplyItemsHistoryModel
     */
    protected $historyModel;

    /**
     * rcSupplyItems constructor.
     * @param null $id
     * @param null $model_name
     * @throws waException
     */
    public function __construct($id = null, $model_name = null)
    {
        $this->historyModel = new rcSupplyItemsHistoryModel();
        parent::__construct($id, $model_name);
    }

    /**
     * @param $value
     * @throws waException
     */
    protected function autoInsert($value)
    {
        $supply = new rcSupply($value['supply_id']);
        $supply = $supply->getData();
        if (!empty($supply)) {
            $ingredient = new rcIngredient($value['ingredient_id']);
            $ingredient = $ingredient->getData();
            if (!empty($ingredient)) {
                $supplier = new rcSupplier($supply['supplier_id']);
                $supplier = $supplier->getData();
                if (!empty($supplier)) {
                    $supplierIngredientsModel = new rcSupplierIngredientsModel();
                    $supplier_ingredients = $supplierIngredientsModel->getById(array($supply['supplier_id'], $value['ingredient_id']));
                    if ($supplier_ingredients) {
                        $value['unit'] = $supplier_ingredients['unit'];
                        $value['dimension'] = $ingredient['dimension'];
                        if ($supply['date_time']) {
                            $value['count'] = $supplier_ingredients['min_purchase'];
                        }
                        $value['start_count'] = $supplier_ingredients['min_purchase'];
                        $this->id = $this->model->insert($value);
                        $this->data = $this->model->getById($this->id);
                    }
                }
            }
        }
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function editPopupData(&$data)
    {
        if (isset($data['id'])) {
            $data['id'] = explode('_', $data['id']);
            $this->setId(array('supply_id' => $data['id'][0], 'ingredient_id' => $data['id'][1]));
            $data['supply_item'] = $this->data;
            if ($data['supply_item']) {
                $supply = new rcSupply($data['supply_item']['supply_id']);
                $data['supply'] = $supply->getData();
                $supplier = new rcSupplier($data['supply']['supplier_id']);
                $data['supplier'] = $supplier->getData();
                $ingredient = new rcIngredient($data['supply_item']['ingredient_id']);
                $data['ingredient'] = $ingredient->getData();
                $supplierIngredientsModel = new rcSupplierIngredientsModel();
                $data['supplier_ingredient'] = $supplierIngredientsModel->getById(array(
                    'supplier_id' => $data['supplier']['id'], 'ingredient_id' => $data['ingredient']['id']));
                $data['config'] = rcHelper::getConfigOption(array('unit', 'dimension'));
            }
        }
    }

    protected function supplyItemsDeletePopupData() {}
}