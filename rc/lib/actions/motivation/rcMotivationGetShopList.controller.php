<?php

class rcMotivationGetShopListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivation = new rcMotivation(waRequest::get('motivation_id', null, waRequest::TYPE_INT));
        $this->response = $motivation->getShopList($this->data_tables_params, 1);
    }
}