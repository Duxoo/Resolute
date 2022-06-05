<?php

class rcFrontendApiGetController extends waJsonController
{
    public function execute()
    {
        $shopApi = new rcShopApi(waRequest::param('shop_id', null, 'int'), 'ddd');//TODO нормально получить ключ
        $method = strtolower($_SERVER["REQUEST_METHOD"]);
        if (method_exists($shopApi, $method)) {
            $this->response = $shopApi->$method();
        } else {
            $this->response = array('error' => true, 'message' => 'Данный метод недоступен для данного объекта');
        }
    }
}