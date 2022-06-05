<?php

class rcFrontendApiErrorController extends rcFrontendApiController
{
    public function execute()
    {
        $this->response = array('error' => true, 'message' => 'Страница не существует');
    }
}