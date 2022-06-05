<?php

class rcIngredientCategoryAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $ingredientCategory = new rcIngredientCategory();
        $this->view->assign($ingredientCategory->mainActionData());
    }
}