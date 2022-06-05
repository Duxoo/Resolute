<?php

class rcMotivationAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $this->setTemplate("MotivationEdit");
        $this->view->assign('status_settings', wa()->getConfig()->getOption('status'));
    }
}