<?php

class rcSupplierEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $supplier = new rcSupplier(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->view->assign($supplier->getFormData());
    }
}