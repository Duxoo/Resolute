<?php

class rcOfferGetTabController extends rcJsonController
{
    public function execute()
    {
        $tab = waRequest::get("tab", null, waRequest::TYPE_STRING);
        $id = waRequest::get("id", null, waRequest::TYPE_INT);
        if ($tab) {
            $view = wa()->getView();
            $offerClass = new rcOffer($id);
            $view->assign("offer", $offerClass->get());
            $view->assign("offer_time", wa()->getConfig()->getOption("offer_time"));
            $view->assign("offer_condition", wa()->getConfig()->getOption("offer_condition"));
            $view->assign("offer_profit", wa()->getConfig()->getOption("offer_profit"));
            $view->assign("offer_condition_type", wa()->getConfig()->getOption("offer_condition_type"));
            $view->assign("offer_profit_type", wa()->getConfig()->getOption("offer_profit_type"));
            $this->response = $view->fetch(wa()->getAppPath('templates/actions/offer/OfferTab'.ucfirst($tab).'.html', 'rc'));
        }
    }
}