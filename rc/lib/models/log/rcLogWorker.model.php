<?php

class rcLogWorkerModel extends rcLogModel
{
    protected $table = 'rc_log_worker';

    protected $template = "%t_user% %t_name% %t_action% рабочего %t_contact_name%.";
}