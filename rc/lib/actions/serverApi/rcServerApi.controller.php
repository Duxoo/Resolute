<?php

class rcServerApiController extends rcJsonController
{
    public function execute()
    {
        $api = new rcServerApi();
        $this->response = $api->partUpload();
    }
}