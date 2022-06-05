<?php

class rcShopGetTabController extends rcJsonController
{
    public function execute()
    {
        $tab = waRequest::get("tab", null, waRequest::TYPE_STRING);
        $id = waRequest::get("id", null, waRequest::TYPE_INT);
        if ($tab) {
            $view = wa()->getView();
            $shopClass = new rcShop($id);
            $view->assign("shop", $shopClass->get());
            $this->view->assign("motivation_profit_type", wa()->getConfig()->getOption("motivation_profit_type"));
            $this->view->assign("motivation_timing_type", wa()->getConfig()->getOption("motivation_timing_type"));
            $this->response = $view->fetch(wa()->getAppPath('templates/actions/shop/ShopTab'.ucfirst($tab).'.html', 'rc'));
        }
    }
}