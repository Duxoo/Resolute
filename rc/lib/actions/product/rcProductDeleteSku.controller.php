<?php

class rcProductDeleteSkuController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post('data', NULL, waRequest::TYPE_ARRAY);
        $productClass = new rcProduct($data['id']);
        $productClass->deleteSku($data['dependent_id']);
    }
}