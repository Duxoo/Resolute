<?php

class rcIngredientAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $ingredient = new rcIngredient();
        $this->view->assign($ingredient->mainActionData());
    }
}