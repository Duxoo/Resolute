<?php

class rcSettingsAction extends rcViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $settings = new rcSettings();
        $data['settings'] = wa()->getConfig()->getOption('settings');
        $data['settings_values'] = $settings->getSettings();
        $this->view->assign($data);
    }
}