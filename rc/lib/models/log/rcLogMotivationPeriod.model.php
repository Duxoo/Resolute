<?php

class rcLogMotivationPeriodModel extends rcLogModel
{
    protected $table = 'rc_log_motivation_period';

    protected $template = "%t_user% %t_name% %t_action% период '%t_period%' в мотивации '%t_motivation_name%'.";
}