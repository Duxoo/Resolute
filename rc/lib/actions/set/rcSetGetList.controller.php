<?php

class rcSetGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $setCollection = new rcSetCollection();
        $this->response = $setCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}