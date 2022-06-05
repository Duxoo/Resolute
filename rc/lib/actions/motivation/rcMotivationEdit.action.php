<?php

class rcMotivationEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $motivation = new rcMotivation(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($motivation->getFormData());
    }
}