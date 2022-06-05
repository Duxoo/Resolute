<?php

class rcMotivationGetTabController extends rcJsonController
{
    public function execute()
    {
        $tab = waRequest::get("tab", null, waRequest::TYPE_STRING);
        $id = waRequest::get("id", null, waRequest::TYPE_INT);
        if ($tab) {
            $view = wa()->getView();
            $motivationClass = new rcMotivation($id);
            $view->assign("motivation", $motivationClass->get());
            $this->response = $view->fetch(wa()->getAppPath('templates/actions/motivation/MotivationTab'.ucfirst($tab).'.html', 'rc'));
        }
    }
}