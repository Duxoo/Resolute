<?php

class rcStockGetSupplyItemsController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supplyItemsCollection = new rcSupplyItemsCollection();
        $this->data_tables_params['shop_id'] = waRequest::get('id', null, waRequest::TYPE_INT);
        $this->data_tables_params['list_type'] = 'stock';
        $this->response = $supplyItemsCollection->getList($this->data_tables_params);
    }
}