<?php

class rcBackendFormAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $type = waRequest::get('type', '', 'string');
        $method = waRequest::get('method', '', 'string');
        $id = waRequest::get("id", NULL, 'int');
        $class_name = 'rc'.ucfirst($type);
        if (empty($type)) {
            $this->setError();
        } else {
            if (!empty($id)) {
                if (class_exists($class_name)) {
                    $class_name = new $class_name($id);
                    if (method_exists($class_name, 'getFormData')) {
                        $data = $class_name->getFormData($method);
                        if ($data) {
                            $this->view->assign($data);
                        }
                    }
                }
            }
            $this->setTemplate(wa()->getAppPath('templates/forms/'.ucfirst($type).ucfirst($method).'.html'));
        }
    }
}