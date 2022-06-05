<?php

class rcInventoryEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $inventory = new rcInventory(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($inventory->getFormData());
    }
}