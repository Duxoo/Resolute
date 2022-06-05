<?php

class rcShopMotivationAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $this->setTemplate("ShopMotivationEdit");
        $shopClass = new rcShop(waRequest::get("shop_id", null, waRequest::TYPE_INT));
        $this->view->assign("shop", $shopClass->get());
        $this->view->assign("motivation_profit_type", wa()->getConfig()->getOption("motivation_profit_type"));
        $this->view->assign("motivation_timing_type", wa()->getConfig()->getOption("motivation_timing_type"));
    }
}