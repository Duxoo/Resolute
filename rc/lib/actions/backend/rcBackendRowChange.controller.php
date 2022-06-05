<?php

class rcBackendRowChangeController extends rcJsonController
{
    public function execute()
    {
        $id = waRequest::get('id', null, waRequest::TYPE_INT);
        $child_id = waRequest::get('child_id', null, waRequest::TYPE_INT);
        $type = waRequest::get('type', null, waRequest::TYPE_STRING);
        $child_type = waRequest::get('child_type', null, waRequest::TYPE_STRING);
        $field = waRequest::get('field', null, waRequest::TYPE_STRING);
        $value = waRequest::get('value', null, waRequest::TYPE_STRING);
        $class_name = 'rc'.ucfirst($type);
        if (class_exists($class_name)) {
            if (!empty($child_id) && empty($id)) {
                $id = $child_id;
                $child_id = null;
                $child_type = null;
            }
            $class_name = new $class_name($id);
            if ($class_name instanceof rcObject) {
                if (method_exists($class_name, 'changeField')) {
                    $this->response = $class_name->changeField($field, $value, $child_type, $child_id);
                } else {
                    $this->response = array('error' => true, 'message' => 'Ошибка получения метода');
                }
            } else {
                $this->response = array('error' => true, 'message' => 'Неверный тип класса');
            }
        } else {
            $this->response = array('error' => true, 'message' => 'Ошибка получения класса');
        }
    }
}