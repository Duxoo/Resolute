<?php

class rcLogCategoryIngredientsModel extends rcLogModel
{
    protected $table = 'rc_log_category_ingredients';

    protected $template = "%t_user% %t_name% %t_action% ингредиент '%t_ingredient_name%' в категории '%t_category_name%'.";
}