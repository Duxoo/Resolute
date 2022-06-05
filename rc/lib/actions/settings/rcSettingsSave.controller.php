<?php

class rcSettingsSaveController extends rcJsonController
{
    public function execute()
    {
        $settings = new rcSettings();
        $this->response = $settings->save(waRequest::post('settings', array()), waRequest::post('type', null, 'string'));
    }
}