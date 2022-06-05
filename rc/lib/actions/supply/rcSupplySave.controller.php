<?php

class rcSupplySaveController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $supply = new rcSupply(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $supply->save(waRequest::post('data', null, waRequest::TYPE_ARRAY));
    }
}