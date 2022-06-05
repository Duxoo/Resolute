<?php

class rcShopGetListOfferController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $shop = new rcShop(waRequest::get("shop_id", null, waRequest::TYPE_INT));
        $this->response = $shop->getOfferList($this->data_tables_params, 1);
    }
}