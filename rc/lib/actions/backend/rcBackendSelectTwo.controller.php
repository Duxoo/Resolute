<?php

class rcBackendSelectTwoController extends rcJsonController
{
    public function execute()
    {
        $type = waRequest::get('class', '', 'string');
        $class_name = 'rc'.ucfirst($type).'Collection';
        if (class_exists($class_name)) {
            $class_name = new $class_name();
            if (method_exists($class_name, 'selectTwo')) {
                $this->response = $class_name->getListForSelect(
                    waRequest::get('search', null, waRequest::TYPE_STRING),
                    waRequest::get('group_id', null, waRequest::TYPE_STRING),
                    waRequest::get('current_id', null, waRequest::TYPE_INT)
                );
            }
        }
    }
}