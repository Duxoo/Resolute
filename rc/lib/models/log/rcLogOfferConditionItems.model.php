<?php

class rcLogOfferConditionItemsModel extends rcLogModel
{
    protected $table = 'rc_log_offer_condition_items';

    protected $template = "%t_user% %t_name% %t_action% акцию '%t_offer_name%'.";
}