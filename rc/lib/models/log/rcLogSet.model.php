<?php

class rcLogSetModel extends rcLogModel
{
    protected $table = 'rc_log_set';

    protected $template = "%t_user% %t_name% %t_action% список '%t_set_name%'.";
}