<?php

class rcOfferSaveController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $offerData = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        if ($offerData) {
            $offerClass = new rcOffer($offerData['id']);
            $this->response = $offerClass->save($offerData);
        } else {
            $this->response = array("error" => 1, "message" => "Нет данных!");
        }
    }
}
