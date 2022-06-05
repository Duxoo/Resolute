<?php

class rcLogCustomerModel extends rcLogModel
{
    protected $table = 'rc_log_customer';

    protected $template = "%t_user% %t_name% %t_action% Клиента %t_contact_name%.";
}