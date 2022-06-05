<?php

class rcOfferEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $offer = new rcOffer(waRequest::get("id", NULL, waRequest::TYPE_INT));
        $this->view->assign($offer->getFormData());
    }
}
