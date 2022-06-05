<?php

class rcShopDeleteController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $shopData = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        $shopClass = new rcShop($shopData['id']);
        $shopClass->delete();
    }
}