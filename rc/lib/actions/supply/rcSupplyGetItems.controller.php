<?php

class rcSupplyGetItemsController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplyItemsCollection = new rcSupplyItemsCollection();
        $supply = new rcSupply(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->data_tables_params['supply'] = $supply->getData();
        $this->data_tables_params['list_type'] = 'supply';
        $this->response = $supplyItemsCollection->getList($this->data_tables_params);
    }
}