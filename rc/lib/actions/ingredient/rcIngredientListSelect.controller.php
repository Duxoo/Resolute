<?php

class rcIngredientListSelectController extends rcJsonController
{
    public function execute()
    {
        $search = waRequest::get('search', null, waRequest::TYPE_STRING);
        $ingredientCollection = new rcIngredientCollection();
        $this->response = $ingredientCollection->getListForSelect($search,
            waRequest::get('group_id', null, waRequest::TYPE_STRING),
            waRequest::get('current_id', null, waRequest::TYPE_INT));
    }
}