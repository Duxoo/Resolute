<?php

class rcLogMotivationWeekdaysModel extends rcLogModel
{
    protected $table = 'rc_log_motivation_weekdays';

    protected $template = "%t_user% %t_name% %t_action% день недели '%t_weekday_name%' в мотивации '%t_motivation_name%'.";
}