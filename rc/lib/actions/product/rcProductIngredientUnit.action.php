<?php

class rcProductIngredientUnitAction extends rcViewAction
{
    /**
     * @throws waDbException
     * @throws waException
     */
    public function execute()
    {
        $ingredient_id = waRequest::get('ingredient_id', null, 'int');
        $ingredient = new rcIngredient($ingredient_id);
        $data['ingredient'] = $ingredient->getData();
        $data['ingredient']['unit'] = waRequest::get('unit_id', null, 'int');
        $data['config'] = rcHelper::getConfigOption(array('unit', 'dimension'));
        $this->view->assign($data);
        $this->setTemplate(wa()->getAppPath('templates/actions/product/ProductIngredientUnits.html'));
    }
}