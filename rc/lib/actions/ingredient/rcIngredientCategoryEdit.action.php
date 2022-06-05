<?php

class rcIngredientCategoryEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $categoryClass = new rcIngredientCategory(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($categoryClass->getFormData());
    }
}