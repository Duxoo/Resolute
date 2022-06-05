<?php

class rcMotivationSaveController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivation = new rcMotivation(waRequest::post("id", null, waRequest::TYPE_INT));
        $this->response = $motivation->save(waRequest::post("data", null, waRequest::TYPE_ARRAY));
    }
}