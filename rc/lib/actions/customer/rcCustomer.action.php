<?php

class rcCustomerAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $entity = new rcCustomer();
        $this->view->assign($entity->mainActionData());
    }
}