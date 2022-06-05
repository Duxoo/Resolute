<?php

class rcIngredientSupplierListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplier = new rcIngredient(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->response = $supplier->getSupplierList($this->data_tables_params);
    }
}