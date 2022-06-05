<?php

class rcWorkerSaveController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $workerData = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        if ($workerData) {
            $worker = new rcWorker(waRequest::post('id', NULL, waRequest::TYPE_INT));
            $this->response = $worker->save($workerData);
        } else {
            $this->response = array('error' => 1, 'message' => 'Нет данных!');
        }
    }
}