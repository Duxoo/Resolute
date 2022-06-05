<?php

class rcAdditionEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $addition = new rcAddition(waRequest::get("id", NULL, waRequest::TYPE_INT));
        $data = $addition->getFormData();
        $this->view->assign($data);
    }
}