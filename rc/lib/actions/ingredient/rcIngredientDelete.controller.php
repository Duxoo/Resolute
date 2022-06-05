<?php

class rcIngredientDeleteController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $ingredientData = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        $ingredientClass = new rcIngredient($ingredientData['id']);
        $ingredientClass->delete();
    }
}