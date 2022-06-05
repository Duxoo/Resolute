<?php

class rcProductListSelectController extends rcJsonController
{
    public function execute()
    {
        $productCollection = new rcProductCollection();
        $this->response = $productCollection->getListForSelect(waRequest::get('search', null, waRequest::TYPE_STRING));
    }
}