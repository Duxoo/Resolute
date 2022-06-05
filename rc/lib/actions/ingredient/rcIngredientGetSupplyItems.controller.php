<?php

class rcIngredientGetSupplyItemsController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplyItemsCollection = new rcSupplyItemsCollection();
        $ingredient = new rcIngredient(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->data_tables_params['ingredient'] = $ingredient->getData();
        $this->data_tables_params['list_type'] = 'ingredient';
        $this->response = $supplyItemsCollection->getList($this->data_tables_params);
    }
}