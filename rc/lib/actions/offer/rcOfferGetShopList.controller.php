<?php

class rcOfferGetShopListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $offer = new rcOffer(waRequest::get("offer_id", null, waRequest::TYPE_INT));
        $this->response = $offer->getShopList($this->data_tables_params, 1);
    }
}