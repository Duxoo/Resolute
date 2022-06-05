<?php

class rcShopGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $shopCollection = new rcShopCollection();
        $this->response = $shopCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}