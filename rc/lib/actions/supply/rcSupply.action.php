<?php

class rcSupplyAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $supply = new rcSupply();
        $this->view->assign($supply->mainActionData());
    }
}