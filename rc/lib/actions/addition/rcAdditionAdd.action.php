<?php

class rcAdditionAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $shopClass = new rcShop();
        $this->setTemplate("AdditionEdit");
        $this->view->assign('status_settings', wa()->getConfig()->getOption('status'));
        $this->view->assign("unit_settings", wa()->getConfig()->getOption("unit"));
        $this->view->assign("shops", $shopClass->getAll());
    }
}