<?php

class rcMotivationAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $motivation = new rcMotivation();
        $this->view->assign($motivation->mainActionData());
    }
}