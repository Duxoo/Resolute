<?php

class rcMotivationSaveProductsController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivation = new rcMotivation(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $motivation->saveProducts(waRequest::post('data', array(), waRequest::TYPE_ARRAY));
    }
}