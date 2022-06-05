<?php

class rcScreenGetTabController extends rcJsonController
{
    public function execute()
    {
        $tab = waRequest::get("tab", null, waRequest::TYPE_STRING);
        $id = waRequest::get("id", null, waRequest::TYPE_INT);
        if ($tab) {
            $view = wa()->getView();
            $screenClass = new rcScreen($id);
            $view->assign("screen", $screenClass->get());
            $this->response = $view->fetch(wa()->getAppPath('templates/actions/screen/ScreenTab'.ucfirst($tab).'.html', 'rc'));
        }
    }
}