<?php

class rcOfferDateSaveController extends rcJsonController
{
    public function execute()
    {
        $data = waRequest::post("data", array(), waRequest::TYPE_ARRAY);
        if (!empty($data)) {
            $offerClass = new rcOffer($data["offer_id"]);
            $this->response = $offerClass->saveData($data);
        } else {
            $this->response = array("error" => 1, "message" => "Нет данных");
        }
    }
}