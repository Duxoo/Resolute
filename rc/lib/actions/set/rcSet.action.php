<?php

class rcSetAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $set = new rcSet();
        $this->view->assign($set->mainActionData());
    }
}