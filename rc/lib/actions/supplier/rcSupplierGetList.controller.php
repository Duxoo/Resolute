<?php

class rcSupplierGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplierCollection = new rcSupplierCollection();
        $this->response = $supplierCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}