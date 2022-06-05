<?php

class rcStockGetIngredientsController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $stock = new rcStock(waRequest::get('id', null, waRequest::TYPE_INT));
        $this->response = $stock->getIngredients($this->data_tables_params);
    }
}