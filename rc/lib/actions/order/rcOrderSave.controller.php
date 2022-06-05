<?php

class rcOrderSaveController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $order = new rcOrder(waRequest::post('id', null, waRequest::TYPE_INT));
        $this->response = $order->save(waRequest::post('data', null, waRequest::TYPE_ARRAY));
    }
}