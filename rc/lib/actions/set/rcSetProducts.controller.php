<?php

class rcSetProductsController extends rcDataTableController
{
    public function execute()
    {
        $set = new rcSet(waRequest::get('id', null, 'int'));
        $this->response = $set->getProductsList($this->data_tables_params, waRequest::get('selected', false));
    }
}