<?php

class rcBackendAddTemplateAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $type = waRequest::get('type', '', 'string');
        $data = waRequest::get('data', array());
        $class_name = 'rc'.ucfirst($type);
        if (empty($type)) {
            $this->setError();
        } else {
            if (class_exists($class_name)) {
                $class_name = new $class_name();
                if (method_exists($class_name, 'addTemplateData')) {
                    $class_name->addTemplateData($data);
                }
            }
            $this->view->assign($data);
            $this->setTemplate(wa()->getAppPath('templates/add/'.ucfirst($type).'.html'));
        }
    }
}