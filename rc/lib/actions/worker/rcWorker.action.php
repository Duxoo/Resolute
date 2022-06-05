<?php

class rcWorkerAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $worker = new rcWorker();
        $this->view->assign($worker->mainActionData());
    }
}