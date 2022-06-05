<?php

class rcMotivationGetEntityController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $motivation = new rcMotivation(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->response = $motivation->getEntitiesSelectTwo(waRequest::get('dependent_id', null, waRequest::TYPE_INT), waRequest::get('search', null, waRequest::TYPE_STRING_TRIM));
    }
}