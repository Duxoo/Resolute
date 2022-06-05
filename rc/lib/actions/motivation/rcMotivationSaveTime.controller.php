<?php

class rcMotivationSaveTimeController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivation = new rcMotivation(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $motivation->saveTime(waRequest::post('data', array(), waRequest::TYPE_ARRAY));
    }
}