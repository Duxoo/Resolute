<?php

class rcScreenUpdateSortController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $screens = waRequest::post("items", null, waRequest::TYPE_ARRAY);
        $screenClass = new rcScreen();
        $screenClass->updateSort($screens);
    }
}