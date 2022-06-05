<?php

class rcShopMotivationSaveController extends rcJsonController
{
    public function execute()
    {
        $data = waRequest::post("data", null, waRequest::TYPE_ARRAY);
        $motivationClass = new rcMotivation($data["id"]);
        $motivationClass->save($data);
    }
}