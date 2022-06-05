<?php

class rcScreenSaveController extends rcJsonController
{
    public function execute()
    {
        $positions = waRequest::post("p", null, waRequest::TYPE_ARRAY);
        $name = waRequest::post("name", null, waRequest::TYPE_STRING);
        $id = waRequest::post("id", null, waRequest::TYPE_INT);
        $screenClass = new rcScreen($id);
        $this->response['id'] = $screenClass->save($name, $positions);
    }
}