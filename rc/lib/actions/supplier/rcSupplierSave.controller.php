<?php

class rcSupplierSaveController extends rcJsonController
{
    public function execute()
    {
        $supplier = new rcSupplier(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $supplier->save(waRequest::post('data', null, waRequest::TYPE_ARRAY));
    }
}