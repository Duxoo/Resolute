<?php

class rcShopEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $shop = new rcShop(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($shop->getFormData());
    }
}