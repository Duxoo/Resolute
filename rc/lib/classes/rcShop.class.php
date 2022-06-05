<?php

class rcShop
{
    /**
     * @var int id of the shop
     */
    protected $id;
    /**
     * @var rcShopModel
     */
    protected $model;

    /**
     * @var rcLog
     */
    protected $logModel;

    /**
     * rcShop constructor.
     * @param int|null $id
     * @throws waDbException
     * @throws waException
     */
    public function __construct($id = null)
    {
        $this->setId($id);
        $this->model = new rcShopModel();
        $this->logModel = new rcLog(rcHelper::getClassNameForLog($this));
    }

    /**
     * @param int|null $id
     */
    protected function setId($id)
    {
        if (!empty(intval($id))) {
            $this->id = $id;
        }
    }

    /**
     * @return array|null
     */
    public function get()
    {
        return isset($this->id) ? $this->model->getById($this->id) : null;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->model->getAll();
    }

    /**
     * @param $data
     * @return array
     */
    public function save($data)
    {
        try {
            if ($shop = $this->model->getById($data['id'])) {
                $this->model->updateById($data['id'], $data);
                $this->logModel->log(array(
                    "action" => "edit",
                    "shop_id" => $this->id,
                    "t_shop_name" => $shop["name"],
                    "data_before" => serialize($shop),
                    "data_after" => serialize($this->get()),
                    "date_time" => date('Y-m-d H:i:s'),
                    "contact_id" => wa()->getUser()->getId(),
                ));
            } else {
                $this->id = $this->model->insert($data);
                $this->logModel->log(array(
                    "action" => "create",
                    "shop_id" => $this->id,
                    "t_shop_name" => $data["name"],
                    "data_before" => "",
                    "data_after" => serialize($this->get()),
                    "date_time" => date('Y-m-d H:i:s'),
                    "contact_id" => wa()->getUser()->getId(),
                ));
            }
            $result = array('error' => false, 'id' => $this->id);
        } catch (waException $wa) {
            $result = array('error' => true, 'message' => $wa->getMessage());
        }
        return $result;
    }

    /**
     * @throws waException
     */
    public function delete()
    {
        if ($shop = $this->get()) {
            $this->model->updateById($this->id, array(
                "status" => 0
            ));
            $this->logModel->log(array(
                "action" => "delete",
                "shop_id" => $this->id,
                "t_shop_name" => $shop["name"],
                "data_before" => serialize($shop),
                "data_after" => serialize($this->get()),
                "date_time" => date("Y-m-d H:i:s"),
                "contact_id" => wa()->getUser()->getId(),
            ));
        }
    }

    public function getList($params)
    {
        $result = array('data' => array(), 'recordsFiltered' => 0, 'recordsTotal' => 0);
        $orders = array(
            0 => 'name',
            1 => 'address',
            2 => 'code',
            3 => 'rent',
            4 => 'terminal_mac',
            5 => 'info_mac',
            6 => 'status',
        );
        $this->model->setFetch('all');
        $this->model->setSelect(array(
            'id' => null,
            'name' => null,
            'address' => null,
            'code' => null,
            'rent' => null,
            'latitude' => null,
            'longitude' => null,
            'terminal_mac' => null,
            'info_mac' => null,
            'status' => null,
        ));
        if (!empty($params['search'])) {
            $logic = array(
                array(
                    'logic' => 'OR',
                    'fields' => array(
                        'name' => null,
                        'address' => null,
                        'code' => null,
                    )
                ),
            );
            $this->model->setWhere(array(
                'name' => array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%'),
                'address' => array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%'),
                'code' => array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%'),
            ), $logic);
        }
        $this->model->setWhere(array(
            'status' => array('simile' => '=', 'value' => 1),
        ));
        $this->model->setGroupBy(array('id'));
        $result['recordsFiltered'] = count($this->model->queryRun(false));
        $result['recordsTotal'] = $this->model->countAll();
        $this->model->setLimit($params['length'], $params['start']);
        if (isset($orders) && isset($orders[$params['column']])) {
            $this->model->setOrderBy(array($orders[$params['column']] => $params['direction']));
        }
        $shops = $this->model->queryRun();
        $statuses = wa()->getConfig()->getOption('status');
        foreach ($shops as $shop) {
            foreach ($statuses as $id => $status) {
                if ($id == $shop['status']) {
                    $shop['status'] = $status['name'];
                    break;
                }
            }
            $shop['name'] = "<a class='rc-color-black rc-underline rc-color-hover' href='?module=shop&action=edit&id=".$shop['id']."'>".htmlspecialchars($shop['name'], ENT_QUOTES)."</a>";
            array_push($result['data'], array(
                $shop['name'],
                htmlspecialchars($shop['address'], ENT_QUOTES),
                htmlspecialchars($shop['code'], ENT_QUOTES),
                $shop['rent']." ₽",
                htmlspecialchars($shop['terminal_mac'], ENT_QUOTES),
                htmlspecialchars($shop['info_mac'], ENT_QUOTES),
                htmlspecialchars($shop['status'], ENT_QUOTES)
            ));
        }
        return $result;
    }

    /**
     * @return bool
     * @throws waException
     */
    public function getFormData()
    {
        $result['shop'] = $this->get();
        if ($result['shop']) {
            $result['status_settings'] = wa()->getConfig()->getOption("status");
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * @param $tab
     * @return array
     * @throws waException
     */
    public function getTabData($tab)
    {
        $result = array(
            "shop" => $this->get(),
            "status_settings" => wa()->getConfig()->getOption("status"),
            "motivation_profit_type" => wa()->getConfig()->getOption("motivation_profit_type"),
            "motivation_timing_type" => wa()->getConfig()->getOption("motivation_timing_type"),
        );//TODO распределить получение по закладкам
        return $result;
    }
}