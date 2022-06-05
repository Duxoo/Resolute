<?php

class rcLogSettingsModel extends rcLogModel
{
    protected $table = 'rc_log_settings';

    protected $template = "%t_user% %t_name% %t_action% настройку '%t_settings_name%'.";
}