<?php

class rcOfferDateDeleteController extends rcJsonController
{
    public function execute()
    {
        $data = waRequest::post("data", array(), waRequest::TYPE_ARRAY);
        $offerClass = new rcOffer($data["id"]);
        $dates = explode("_", $data["dependent_id"]);
        $offerClass->dateDelete($dates[0], $dates[1]);
    }
}