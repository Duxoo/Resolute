<?php

class rcSetListSelectController extends rcJsonController
{
    public function execute()
    {
        $setCollection = new rcSetCollection();
        $this->response = $setCollection->getListForSelect(waRequest::get("search", null, waRequest::TYPE_STRING));
    }
}