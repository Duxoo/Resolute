<?php

class rcOrderGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $orderCollection = new rcOrderCollection();
        $this->response = $orderCollection->getList($this->data_tables_params);
    }
}