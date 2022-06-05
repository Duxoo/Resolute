<?php

class rcOfferGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $offerCollection = new rcOfferCollection();
        $this->response = $offerCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}