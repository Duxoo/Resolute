<?php

class rcScreenAddAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
        $this->setTemplate("ScreenEdit");
    }
}