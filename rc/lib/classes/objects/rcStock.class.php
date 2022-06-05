<?php

class rcStock extends rcObject
{
    /**
     * @var rcShopModel
     */
    protected $model;

    public function __construct($id = null, $model_name = 'rcShopModel')
    {
        parent::__construct($id, $model_name);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = array();
        if (!empty($this->data)) {
            $result = $this->data;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function mainTab()
    {
        return array('stock' => $this->data);
    }

    /**
     * @return array
     * @throws waException
     */
    public function supplyTab()
    {
        return array('stock' => $this->data, 'fields' => rcHelper::getFields('supplyItems'));
    }

    /**
     * @param $params
     * @return array
     * @throws waException
     */
    public function getIngredients($params)
    {
        $params['list_type'] = $this->class_name;
        $params['shop_id'] = $this->id;
        $ingredientCollection = new rcIngredientCollection();
        return $ingredientCollection->getList($params);
    }
}