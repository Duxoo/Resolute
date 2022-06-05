<?php

class rcLogMotivationItemsModel extends rcLogModel
{
    protected $table = 'rc_log_motivation_items';

    protected $template = "%t_user% %t_name% %t_action% %t_type_name% '%t_entity_name%' в мотивации '%t_motivation_name%'.";
}