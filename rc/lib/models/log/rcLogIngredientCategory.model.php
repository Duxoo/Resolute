<?php

class rcLogIngredientCategoryModel extends rcLogModel
{
    protected $table = 'rc_log_ingredient_category';

    protected $template = "%t_user% %t_name% %t_action% категорию ингредиентов '%t_category_name%'.";
}