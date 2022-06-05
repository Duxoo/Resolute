<?php

class rcLogOfferDatesModel extends rcLogModel
{
    protected $table = 'rc_log_offer_dates';

    protected $template = "%t_user% %t_name% %t_action% день действия акции '%t_offer_name%'.";
}