<?php

class rcIngredientGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $ingredientCollection = new rcIngredientCollection();
        $this->response = $ingredientCollection->getList($this->data_tables_params,
            waRequest::get('status', null, waRequest::TYPE_INT));
    }
}