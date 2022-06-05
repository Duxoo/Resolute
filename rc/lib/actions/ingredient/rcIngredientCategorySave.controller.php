<?php

class rcIngredientCategorySaveController extends rcJsonController
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $category = new rcIngredientCategory(waRequest::post("id", NULL, waRequest::TYPE_INT));
        $this->response = $category->save(waRequest::post("data", NULL, waRequest::TYPE_ARRAY));
    }
}