<?php

class rcShopAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $this->setTemplate("ShopEdit");
        $this->view->assign('status_settings', wa()->getConfig()->getOption('status'));
    }
}