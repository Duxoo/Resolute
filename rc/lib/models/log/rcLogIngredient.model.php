<?php

class rcLogIngredientModel extends rcLogModel
{
    protected $table = "rc_log_ingredient";

    protected $template = "%t_user% %t_name% %t_action% ингредиент '%t_ingredient_name%'.";
}