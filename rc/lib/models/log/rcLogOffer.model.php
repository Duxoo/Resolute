<?php

class rcLogOfferModel extends rcLogModel
{
    protected $table = "rc_log_offer";

    protected $template = "%t_user% %t_name% %t_action% акцию '%t_offer_name%'.";
}