<?php

class rcOfferAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $this->setTemplate("OfferEdit");
        $this->view->assign('status_settings', wa()->getConfig()->getOption('status'));
    }
}