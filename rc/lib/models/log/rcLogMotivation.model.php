<?php

class rcLogMotivationModel extends rcLogModel
{
    protected $table = "rc_log_motivation";

    protected $template = "%t_user% %t_name% %t_action% мотивацию '%t_motivation_name%'.";
}