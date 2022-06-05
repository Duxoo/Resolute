<?php

class rcSetCollection extends rcCollection
{
    /**
     * @var rcSetModel
     */
    protected $model;
    /**
     * @var rcProductSetModel
     */
    protected $productSetModel;
    /**
     * @var rcProductModel
     */
    protected $productModel;

    /**
     * rcSetCollection constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->productModel = new rcProductModel();
        $this->productSetModel = new rcProductSetModel();
    }
}