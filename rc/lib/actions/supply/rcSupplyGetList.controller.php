<?php

class rcSupplyGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplyCollection = new rcSupplyCollection();
        $this->response = $supplyCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}