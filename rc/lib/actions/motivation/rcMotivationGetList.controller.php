<?php

class rcMotivationGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivationCollection = new rcMotivationCollection();
        $this->response = $motivationCollection->getList($this->data_tables_params,
            waRequest::get("status", null, waRequest::TYPE_INT));
    }
}