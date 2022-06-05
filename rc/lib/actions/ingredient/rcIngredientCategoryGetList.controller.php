<?php

class rcIngredientCategoryGetListController extends rcDataTableController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $categoryCollection = new rcIngredientCategoryCollection();
        $this->response = $categoryCollection->getList($this->data_tables_params);
    }
}