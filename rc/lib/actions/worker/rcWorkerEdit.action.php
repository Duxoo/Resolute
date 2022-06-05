<?php

class rcWorkerEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $worker = new rcWorker(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($worker->getFormData());
    }
}