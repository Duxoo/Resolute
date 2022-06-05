<?php

class rcLogOfferContactGroupModel extends rcLogModel
{
    protected $table = 'rc_log_offer_contact_group';

    protected $template = "%t_user% %t_name% %t_action% группу контактов '%t_offer_name%'.";
}