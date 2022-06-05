<?php

class rcOfferGetExceptionListController extends rcDataTableController
{
    /**
     * @throws SmartyException
     * @throws waException
     */
    public function execute()
    {
        $offer = new rcOffer(waRequest::get("offer_id", null, waRequest::TYPE_INT));
        $this->response = $offer->getExceptionList($this->data_tables_params, 1);
    }
}