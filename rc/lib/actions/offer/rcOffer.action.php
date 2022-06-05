<?php

class rcOfferAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $offer = new rcOffer();
        $this->view->assign($offer->mainActionData());
    }
}