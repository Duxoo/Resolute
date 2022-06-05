<?php

class rcScreenListSelectController extends rcJsonController
{
    public function execute()
    {
        $screenCollection = new rcScreenCollection();
        $this->response = $screenCollection->getListForSelect(waRequest::get('search', null, waRequest::TYPE_STRING));
    }
}