<?php

class rcLogSupplierModel extends rcLogModel
{
    protected $table = 'rc_log_supplier';

    protected $template = "%t_user% %t_name% %t_action% поставщика '%t_supplier_name%'.";
}