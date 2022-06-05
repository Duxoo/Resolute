<?php

class rcShopGetListSupplierController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $shop = new rcShop(waRequest::get('shop_id', null, waRequest::TYPE_INT));
        $this->response = $shop->getSupplierList($this->data_tables_params, 1);
    }
}