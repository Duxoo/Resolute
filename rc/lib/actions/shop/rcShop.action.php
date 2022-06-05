<?php

class rcShopAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $shop = new rcShop();
        $this->view->assign($shop->mainActionData());
    }
}