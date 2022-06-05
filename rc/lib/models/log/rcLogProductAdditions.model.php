<?php

class rcLogProductAdditionsModel extends rcLogModel
{
    protected $table = 'rc_log_product_additions';

    protected $template = "%t_user% %t_name% %t_action% дополнение '%t_addition_name%' в товаре '%t_product_name%'.";
}