<?php

class rcInventoryGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $inventoryCollection = new rcInventoryCollection();
        $this->response = $inventoryCollection->getList($this->data_tables_params);
    }
}