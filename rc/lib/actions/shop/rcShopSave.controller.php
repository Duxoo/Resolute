<?php

class rcShopSaveController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $shopClass = new rcShop(waRequest::post('id', NULL, waRequest::TYPE_INT));
        $this->response = $shopClass->save(waRequest::post("data", NULL, waRequest::TYPE_ARRAY));
    }
}