<?php

class rcSupplierAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $supplier = new rcSupplier();
        $this->view->assign($supplier->mainActionData());
    }
}