<?php

class rcLogShopModel extends rcLogModel
{
    protected $table = "rc_log_shop";

    protected $template = "%t_user% %t_name% %t_action% точку '%t_shop_name%'.";
}