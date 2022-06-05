<?php

class rcLogOfferWeekdaysModel extends rcLogModel
{
    protected $table = 'rc_log_offer_weekdays';

    protected $template = "%t_user% %t_name% %t_action% день недели действия акции '%t_offer_name%'.";
}