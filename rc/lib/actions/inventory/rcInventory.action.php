<?php

class rcInventoryAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $inventory = new rcInventory();
        $this->view->assign($inventory->mainActionData());
    }
}