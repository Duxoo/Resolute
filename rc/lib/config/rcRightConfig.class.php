<?php

class rcRightConfig extends waRightConfig
{
    const RIGHT_READ = 0;
    const RIGHT_EDIT = 1;
    const RIGHT_FULL = 2;

    /**
     * @throws waException
     */
    public function init()
    {
        $this->addItem('settings', _w('Can manage settings'));
        wa('rc')->event('rights.config', $this);
    }
}