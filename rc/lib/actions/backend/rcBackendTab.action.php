<?php

class rcBackendTabAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $id = waRequest::get("id", null, waRequest::TYPE_INT);
        $tab = waRequest::get("tab", null, waRequest::TYPE_STRING);
        $type = waRequest::get("type", null, waRequest::TYPE_STRING);
        $class_name = 'rc'.ucfirst($type);
        if ($tab && class_exists($class_name)) {
            $class_name = new $class_name($id);
            if (method_exists($class_name, 'getTabData')) {
                $data = $class_name->getTabData($tab);
                if ($data) {
                    $this->view->assign($data);
                }
            }
            $template_path = wa()->getAppPath('templates/actions/'.$type.'/'.ucfirst($type).'Tab'.ucfirst($tab).'.html');
            if (file_exists($template_path)) {
                $this->setTemplate($template_path);
            } else {
                $template_path = wa()->getAppPath('templates/tab/Tab'.ucfirst($tab).'.html');
                if (file_exists($template_path)) {
                    $this->setTemplate($template_path);
                } else {
                    $this->setError();
                }
            }
        } else {
            $this->setError();
        }
    }
}