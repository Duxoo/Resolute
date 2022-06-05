<?php

class rcFranchiseeEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $entity = new rcFranchisee(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($entity->getFormData());
    }
}