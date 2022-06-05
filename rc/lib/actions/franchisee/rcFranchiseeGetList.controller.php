<?php
class rcFranchiseeGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $franchiseeCollection = new rcFranchiseeCollection();
        $this->response = $franchiseeCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}
