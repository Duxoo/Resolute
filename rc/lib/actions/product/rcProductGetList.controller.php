<?php

class rcProductGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $productCollection = new rcProductCollection();
        $this->response = $productCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}