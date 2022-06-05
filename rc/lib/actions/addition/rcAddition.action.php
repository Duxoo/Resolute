<?php

class rcAdditionAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $addition = new rcAddition();
        $this->view->assign($addition->mainActionData());
    }
}