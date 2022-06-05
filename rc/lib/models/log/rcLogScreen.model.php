<?php

class rcLogScreenModel extends rcLogModel
{
    protected $table = "rc_log_screen";

    protected $template = "%t_user% %t_name% %t_action% меню '%t_screen_name%'.";
}