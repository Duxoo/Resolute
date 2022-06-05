<?php

class rcScreenAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $screen = new rcScreen();
        $this->view->assign($screen->mainActionData());
    }
}