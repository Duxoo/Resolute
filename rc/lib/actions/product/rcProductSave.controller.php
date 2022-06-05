<?php

class rcProductSaveController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $productData = waRequest::post('data', NULL, waRequest::TYPE_ARRAY);
        if ($productData) {
            $productClass = new rcProduct($productData['id']);
            $this->response = $productClass->save($productData);
        } else {
            $this->response = array('error' => 1, 'message' => 'Нет данных!');
        }
    }
}