<?php

class rcOfferConditionEntityAddController extends rcJsonController
{
    /**
     * @throws waDbException
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post("data", null, waRequest::TYPE_ARRAY);
        if ($data) {
            $offerClass = new rcOffer($data["offer_id"]);
            $this->response = $offerClass->addEntity($data, 'condition');
        } else {
            $this->response = array("error" => 1, "message" => "Нет данных");
        }
    }
}