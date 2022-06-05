<?php

class rcLogShopFranchiseesModel extends rcLogModel
{
    protected $table = 'rc_log_shop_franchisees';

    protected $template = "%t_user% %t_name% %t_action% франчайзи '%t_contact_name%' для точки '%t_shop_name%'.";
}