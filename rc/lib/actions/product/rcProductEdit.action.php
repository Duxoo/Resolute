<?php

class rcProductEditAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $product = new rcProduct(waRequest::get('id', NULL, waRequest::TYPE_INT));
        $this->view->assign($product->getFormData());
    }
}