<?php

class rcLogShopOffersModel extends rcLogModel
{
    protected $table = 'rc_log_shop_offers';

    protected $template = "%t_user% %t_name% %t_action% акцию '%t_offer_name%' для точки '%t_shop_name%'.";
}