<?php

class rcBackendLayout extends waLayout
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->view->assign(array(
            'page' => $this->getPage(),
            'menu' => rcHelper::getBackendMenu(),
            'app_path' => wa()->getAppPath('', 'rc'),
        ));
//        chmod($_SERVER['DOCUMENT_ROOT']."/server.py",0777);
//        exec("chmod +x {$_SERVER['DOCUMENT_ROOT']} /server.py");
//        exec($_SERVER['DOCUMENT_ROOT']."/server.py 1 2 3 4", $out, $status);
//        echo exec("apt-get install python -y");
//        print_r($out);
//        echo $status;
    }

    /**
     * @return mixed|string
     * @throws waException
     */
    protected function getPage()
    {
        $page = false;
        $module = waRequest::get('module', 'backend');
        if (wa()->getUser()->getRights('rc', $module)) {
            $default_page = 'backend';
            $page = waRequest::get('action', ($module == 'backend') ? $default_page : 'default');
            if ($module != 'backend') {
                $page = $module.':'.$page;
            }
            $plugin = waRequest::get('plugin');
            if ($plugin) {
                if ($module == 'backend') {
                    $page = ':'.$page;
                }
                $page = $plugin.':'.$page;
            }
        }
        return $page;
    }
}

