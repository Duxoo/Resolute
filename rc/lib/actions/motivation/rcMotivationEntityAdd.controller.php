<?php

class rcMotivationEntityAddController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post('data', array(), waRequest::TYPE_ARRAY);
        $motivation = new rcMotivation($data['motivation_id']);
        $this->response = $motivation->entityAdd($data);
    }
}