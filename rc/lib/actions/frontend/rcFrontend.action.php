<?php

class rcFrontendAction extends waViewAction
{
    public function __construct($params = null)
    {
        parent::__construct($params);

        if (!waRequest::isXMLHttpRequest()) {
            $this->setLayout(new rcFrontendLayout());
        }
    }

    public function execute()
    {
//        waLog::dump($_POST);
//        waLog::dump($_GET);
        waLog::dump($_SERVER);
//        parse_str(file_get_contents("php://input"),$data);
        waLog::dump(file_get_contents("php://input"));
        waLog::dump(json_decode(file_get_contents("php://input")));
//        waLog::dump(json_decode($data));
        if (wa()->getRouting()->getCurrentUrl()) {
            $this->setError();
        } else {
            if ($route = wa()->getRouting()->getRoute()) {
                $this->setThemeTemplate('home.html');
            } else {
                $this->setError();
            }
        }
    }

    protected function addCanonical($pagination = true)
    {
        $get_vars = waRequest::get();
        if ($pagination) {
            if (isset($get_vars['page'])) {
                unset($get_vars['page']);
            }
        }
        if (count($get_vars) > 0) {
            $this->layout->assign('canonical', wa()->getConfig()->getHostUrl().wa()->getConfig()->getRequestUrl(false, true));
        }
    }

    protected function addPrevNext($pages)
    {
        $page = waRequest::get('page', 1, 'int');
        $get_vars = waRequest::get();
        if (isset($get_vars['page'])) {
            unset($get_vars['page']);
        }
        if (count($get_vars) == 0) {
            if ($page > 1) {
                if ($page > 2) {
                    wa()->getResponse()->setMeta('prev', wa()->getConfig()->getHostUrl().wa()->getConfig()->getRequestUrl(false, true).'?page='.($page - 1));
                } else {
                    wa()->getResponse()->setMeta('prev', wa()->getConfig()->getHostUrl().wa()->getConfig()->getRequestUrl(false, true));
                }
            }
            if (isset($pages) && $pages > $page) {
                wa()->getResponse()->setMeta('next', wa()->getConfig()->getHostUrl().wa()->getConfig()->getRequestUrl(false, true).'?page='.($page + 1));
            }
        }
    }

    protected function setError($status = 404, $message = 'Page not found')
    {
        wa()->getResponse()->setTitle(_ws($message));
        wa()->getResponse()->setStatus($status);
        $this->view->assign(array(
            'error_code' => $status,
            'error_message' => _ws($message),
        ));
        $this->setThemeTemplate('error.html');
    }

    public function setMeta($meta)
    {
        wa()->getResponse()->setTitle($meta['title']);
        if (isset($meta['keywords'])) {
            wa()->getResponse()->setMeta('keywords', $meta['keywords']);
        }
        if (isset($meta['description'])) {
            wa()->getResponse()->setMeta('description', $meta['description']);
        }
    }
}