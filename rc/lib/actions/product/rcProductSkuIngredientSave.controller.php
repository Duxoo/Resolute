<?php

class rcProductSkuIngredientSaveController extends rcJsonController
{
    /**
     * @throws waDbException
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::post('data', array(), waRequest::TYPE_ARRAY);
        $sku = new rcSku($data['sku_id']);
        $this->response = $sku->setIngredients($data);
    }
}