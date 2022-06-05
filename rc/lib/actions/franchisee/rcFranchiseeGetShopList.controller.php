<?php

class rcFranchiseeGetShopListController extends rcDataTableController
{
    /**
     * @throws SmartyException
     * @throws waException
     */
    public function execute()
    {
        $franchisee = new rcFranchisee(waRequest::get('franchisee_id', null, waRequest::TYPE_INT));
        $this->response = $franchisee->getShopList($this->data_tables_params, 1);
    }
}