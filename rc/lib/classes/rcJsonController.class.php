<?php

class rcJsonController extends waJsonController
{
    protected $view;
    /**
     * rcJsonController constructor.
     * @throws waException
     */
    public function __construct()
    {
        $module = waRequest::get('module', 'backend');
        $this->view = wa()->getView();
        $this->view->assign('app_path', wa()->getAppPath('', 'rc'));
        if (!wa()->getUser()->getRights('rc', $module)) {
            $this->display();
            die;
        }
    }
}