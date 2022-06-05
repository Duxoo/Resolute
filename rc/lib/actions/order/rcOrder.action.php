<?php

class rcOrderAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $order = new rcOrder();
        $this->view->assign($order->mainActionData());
    }
}