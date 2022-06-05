<?php

class rcBackendPopupAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $data = waRequest::get('data', array(), waRequest::TYPE_ARRAY);
        $type = waRequest::get('type', '', waRequest::TYPE_STRING);
        $method = waRequest::get('method', null, waRequest::TYPE_STRING);
        $class_name = 'rc'.ucfirst($type);
        if (empty($type)) {
            $this->setError();
        } else {
            if (class_exists($class_name)) {
                $id = isset($data['id']) ? $data['id'] : null;
                $class_name = new $class_name($id);
                if ($class_name instanceof rcObject && method_exists($class_name, 'getPopupData')) {
                    $data = $class_name->getPopupData($data, $method);
                }
            }
            if (!empty($data)) {
                $this->view->assign($data);
            }
            $this->setTemplate(wa()->getAppPath('templates/popup/'.ucfirst($type).ucfirst($method).'.html'));
        }
    }
}