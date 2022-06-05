<?php

class rcSupplierShopListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplier = new rcSupplier(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->response = $supplier->getShopList($this->data_tables_params, 1);
    }
}