<?php

class rcShopGetListMotivationController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $shop = new rcShop(waRequest::get('shop_id', null, waRequest::TYPE_INT));
        $this->response = $shop->getMotivationList($this->data_tables_params, 1);
    }
}