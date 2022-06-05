<?php

class rcAdditionDeleteController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $data = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        $productClass = new rcAddition($data['id']);
        $productClass->delete();
    }
}