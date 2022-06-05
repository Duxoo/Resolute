<?php

class rcLogProductSkuModel extends rcLogModel
{
    protected $table = "rc_log_product_sku";

    protected $template = "%t_user% %t_name% %t_action% артикул '%t_sku_name%' товара '%t_product_name%'.";
}