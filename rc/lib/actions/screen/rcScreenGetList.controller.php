<?php

class rcScreenGetListController extends rcDataTableController
{
    /**
     * @throws SmartyException
     * @throws waException
     */
    public function execute()
    {
        $screenCollection = new rcScreenCollection();
        $this->response = $screenCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}