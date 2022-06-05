<?php

class rcLogOfferExceptionsModel extends rcLogModel
{
    protected $table = 'rc_log_offer_exceptions';

    protected $template = "%t_user% %t_name% %t_action% ограничение действия акции '%t_offer_name%'.";
}