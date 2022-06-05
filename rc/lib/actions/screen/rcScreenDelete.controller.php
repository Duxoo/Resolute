<?php

class rcScreenDeleteController extends rcJsonController
{
    public function execute()
    {
        $data = waRequest::post("data", null, waRequest::TYPE_ARRAY);
        $screenClass = new rcScreen($data['id']);
        $screenClass->delete();
    }
}