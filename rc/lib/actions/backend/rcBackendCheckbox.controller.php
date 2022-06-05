<?php

class rcBackendCheckboxController extends rcJsonController
{
    public function execute()
    {
        $id = waRequest::get("id", null, waRequest::TYPE_INT);
        $child_id = waRequest::get("child_id", null, waRequest::TYPE_INT);
        $type = waRequest::get("type", null, waRequest::TYPE_STRING);
        $child_type = waRequest::get("child_type", null, waRequest::TYPE_STRING);
        $on = waRequest::get("on");
        $class_name = 'rc'.ucfirst($type);
        if (class_exists($class_name)) {
            if (!empty($child_id) && empty($id)) {
                $class_name = new $class_name($child_id);
                if (method_exists($class_name, 'setStatus')) {
                    $this->response = $class_name->setStatus($on ? 1 : 2);
                } else {
                    $this->response = array('error' => true, 'message' => 'Ошибка получения метода');
                }
            } else {
                $class_name = new $class_name($id);
                if (method_exists($class_name, 'setChild')) {
                    $this->response = $class_name->setChild($child_id, $child_type, $on);
                } else {
                    $this->response = array('error' => true, 'message' => 'Ошибка получения метода');
                }
            }
        } else {
            $this->response = array('error' => true, 'message' => 'Ошибка получения класса');
        }
    }
}