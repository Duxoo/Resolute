<?php

class rcMotivationDateDeleteController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivation = new rcMotivation(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $motivation->deleteDate(waRequest::post('dependent_id', null, waRequest::TYPE_INT));
    }
}