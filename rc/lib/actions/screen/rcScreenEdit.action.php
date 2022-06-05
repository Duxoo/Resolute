<?php

class rcScreenEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $screen = new rcScreen(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($screen->getFormData());
    }
}