<?php

class rcLogSupplierIngredientsModel extends rcLogModel
{
    protected $table = 'rc_log_supplier_ingredients';

    protected $template = "%t_user% %t_name% %t_action% игредиент %t_ingredient_name% предоставляемый поставщиком %t_supplier_name%.";
}