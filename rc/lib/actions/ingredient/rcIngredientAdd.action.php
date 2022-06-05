<?php

class rcIngredientAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $this->setTemplate("IngredientEdit");
        $this->view->assign('status_settings', wa()->getConfig()->getOption('status'));
        $this->view->assign('dimension', wa()->getConfig()->getOption('dimension'));
    }
}