<?php

class rcSetEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $set_id = waRequest::get("id", NULL, waRequest::TYPE_INT);
        $set = new rcSet($set_id);
        $this->view->assign('set', $set->getData());
    }
}