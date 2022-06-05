<?php

class rcBackendPopupController extends waJsonController
{
    public function execute()
    {
        $view = wa()->getView();
        $view->assign(waRequest::get('data'));
        $this->response = $view->fetch(wa()->getAppPath('templates/popup/'.waRequest::get('template', null, 'string').'.html', 'rc'));
    }
}