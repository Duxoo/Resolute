<?php

class rcShopMotivationEditAction extends rcViewAction
{
    /**
     * @throws waDbException
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $motivation_id = waRequest::get('id', NULL, waRequest::TYPE_INT);
        $motivationClass = new rcMotivation($motivation_id);
        $this->view->assign("shop", $motivationClass->get());
    }
}