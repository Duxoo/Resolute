<?php

class rcOfferProfitEntityDeleteController extends rcJsonController
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
        $offerClass->entityDelete($ids[0], $ids[1], 'profit');
    }
}