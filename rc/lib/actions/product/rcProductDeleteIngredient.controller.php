<?php

class rcProductDeleteIngredientController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $data = waRequest::post('data', NULL, waRequest::TYPE_ARRAY);
        $productClass = new rcProduct($data['id']);
        $productClass->deleteIngredient($data['dependent_id']);
    }
}