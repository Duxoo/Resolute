<?php

class rcSupplyDeleteItemController extends rcJsonController
{
    public function execute()
    {
        $this->response = array('error' => false, 'message' => 'Ингредиент удалён из поставки');
    }
}