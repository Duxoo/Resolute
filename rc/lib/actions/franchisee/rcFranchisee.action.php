<?php

class rcFranchiseeAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $entity = new rcFranchisee();
        $this->view->assign($entity->mainActionData());
    }
}