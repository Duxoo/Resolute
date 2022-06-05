<?php

class rcLogProductIngredientsModel extends rcLogModel
{
    protected $table = 'rc_log_product_ingredients';

    protected $template = "%t_user% %t_name% %t_action% ингредиент '%t_ingredient_name%'%t_sku_name% %t_product_type% '%t_product_name%'.";
}