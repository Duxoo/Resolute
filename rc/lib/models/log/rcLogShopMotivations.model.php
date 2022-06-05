<?php

class rcLogShopMotivationsModel extends rcLogModel
{
    protected $table = 'rc_log_shop_motivations';

    protected $template = "%t_user% %t_name% %t_action% мотивацию '%t_motivation_name%' для точки '%t_shop_name%'.";
}