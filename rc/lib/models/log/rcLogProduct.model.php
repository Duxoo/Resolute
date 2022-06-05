<?php

class rcLogProductModel extends rcLogModel
{
    protected $table = "rc_log_product";

    protected $template = "%t_user% %t_name% %t_action% товар '%t_product_name%'.";
}