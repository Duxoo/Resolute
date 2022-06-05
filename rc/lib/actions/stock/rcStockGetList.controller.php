<?php

class rcStockGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $stockCollection = new rcStockCollection();
        $this->response = $stockCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}