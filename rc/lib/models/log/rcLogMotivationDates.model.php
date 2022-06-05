<?php

class rcLogMotivationDatesModel extends rcLogModel
{
    protected $table = 'rc_log_motivation_dates';

    protected $template = "%t_user% %t_name% %t_action% день '%t_date%' в мотивации '%t_motivation_name%'.";
}