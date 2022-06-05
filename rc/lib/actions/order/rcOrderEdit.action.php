<?php

class rcOrderEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $order = new rcOrder(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($order->getFormData());
    }
}