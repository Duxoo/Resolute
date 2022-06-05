<?php

class rcCustomerEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $entity = new rcCustomer(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($entity->getFormData());
    }
}