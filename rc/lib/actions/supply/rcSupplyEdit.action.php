<?php

class rcSupplyEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $supply = new rcSupply(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($supply->getFormData());
    }
}