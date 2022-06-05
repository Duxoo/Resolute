<?php

class rcInventorySaveController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $inventory = new rcInventory(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $inventory->save(waRequest::post('data', null, waRequest::TYPE_ARRAY));
    }
}