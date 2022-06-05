<?php

class rcStockAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $stock = new rcStock();
        $this->view->assign($stock->mainActionData());
    }
}