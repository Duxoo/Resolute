<?php

class rcViewAction extends waViewAction
{
    /**
     * rcViewAction constructor.
     * @param null $params
     * @throws waException
     */
    public function __construct($params = null)
    {
        parent::__construct($params);
        $module = waRequest::get('module', 'backend');
        $this->view->assign("app_path", wa()->getAppPath('', 'rc'));
        if (!wa()->getUser()->getRights('rc', $module)) {
            $this->display();
            die;
        }
    }

    /**
     * @param int $status
     * @param string $message
     * @throws waException
     */
    protected function setError($status = 404, $message = 'Page not found')
    {
        wa()->getResponse()->setTitle(_ws($message));
        wa()->getResponse()->setStatus($status);
        $this->view->assign(array(
            'error_code' => $status,
            'error_message' => _ws($message),
        ));
        $this->setTemplate(wa()->getAppPath('templates/actions/Error.html'));
    }
}