<?php

class rcStockEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $stock = new rcStock(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($stock->getFormData());
    }
}