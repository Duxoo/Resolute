<?php
class rcWorkerGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $workerCollection = new rcWorkerCollection();
        $this->response = $workerCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}
