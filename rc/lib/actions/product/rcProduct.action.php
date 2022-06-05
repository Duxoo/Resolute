<?php

class rcProductAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $product = new rcProduct();
        $this->view->assign($product->mainActionData());
    }
}