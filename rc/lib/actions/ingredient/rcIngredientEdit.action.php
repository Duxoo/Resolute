<?php

class rcIngredientEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $ingredientClass = new rcIngredient(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($ingredientClass->getFormData());
    }
}