<?php

class rcAdditionGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $additionCollection = new rcAdditionCollection();
        $this->response = $additionCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}