<?php

class rcIngredientSaveController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $ingredientClass = new rcIngredient(waRequest::post("id", NULL, waRequest::TYPE_INT));
        $this->response = $ingredientClass->save(waRequest::post("data", NULL, waRequest::TYPE_ARRAY));
    }
}