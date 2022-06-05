<?php

class rcOrderWorkersModel extends rcModel
{
    protected $table = 'rc_order_workers';
    protected $id = array('order_id', 'worker_id');
}