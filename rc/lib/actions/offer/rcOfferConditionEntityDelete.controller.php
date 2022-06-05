<?php

class rcOfferConditionEntityDeleteController extends rcJsonController
{
    /**
     * @throws waDbException
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post("data", array(), waRequest::TYPE_ARRAY);
        $offerClass = new rcOffer($data["id"]);
        $ids = explode("_", $data["dependent_id"]);
        $this->response = $offerClass->entityDelete($ids[0], $ids[1], 'condition');
    }
}