<?php

class rcSupplySaveItemController extends rcJsonController
{
    public function execute()
    {
        $this->response = array('error' => false, 'message' => 'Данные поставки успешно обновлены');
    }
}