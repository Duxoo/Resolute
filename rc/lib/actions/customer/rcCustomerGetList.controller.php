<?php
class rcCustomerGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $customerCollection = new rcCustomerCollection();
        $this->response = $customerCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}
