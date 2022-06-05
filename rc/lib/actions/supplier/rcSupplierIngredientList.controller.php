<?php

class rcSupplierIngredientListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplier = new rcSupplier(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->response = $supplier->getIngredientList($this->data_tables_params);
    }
}