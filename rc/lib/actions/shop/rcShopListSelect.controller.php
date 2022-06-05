<?php

class rcShopListSelectController extends rcJsonController
{
    public function execute()
    {
        $search = waRequest::get("search", null, waRequest::TYPE_STRING);
        $shopClass = new rcShop();
        $this->response = $shopClass->getListForSelect($search);
    }
}