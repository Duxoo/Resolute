<?php

class rcStockCollection extends rcCollection
{
    /**
     * @var rcShopModel
     */
    protected $model;

    /**
     * rcStockCollection constructor.
     * @param null $hash
     * @param null $model_name
     * @throws waException
     */
    public function __construct($hash = null, $model_name = 'rcShopModel')
    {
        parent::__construct($hash, $model_name);
    }
}