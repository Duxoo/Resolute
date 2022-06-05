<?php

class rcMotivation
{
    /**
     * @var rcMotivationModel
     */
    protected $model;
    /**
     * @var rcMotivationDatesModel
     */
    protected $datesModel;
    /**
     * @var rcMotivationItemsModel
     */
    protected $itemsModel;
    /**
     * @var rcMotivationPeriodModel
     */
    protected $periodModel;
    /**
     * @var rcMotivationWeekdaysModel
     */
    protected $weekdaysModel;
    /**
     * @var rcLog 
     */
    protected $logModel;
    /**
     * @var int id of the motivation
     */
    protected $id;

    /**
     * @throws waException
     * @throws waDbException
     */
    public function __construct($id = null)
    {
        $this->setId($id);
        $this->model = new rcMotivationModel();
        $this->datesModel = new rcMotivationDatesModel();
        $this->itemsModel = new rcMotivationItemsModel();
        $this->periodModel = new rcMotivationPeriodModel();
        $this->weekdaysModel = new rcMotivationWeekdaysModel();
        $this->logModel = new rcLog(rcHelper::getClassNameForLog($this));
    }

    /**
     * @param int $id
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
    public function get() {
        if (isset($this->id)) {
            // main info todo change
            $result["motivation"] = $this->model->getById($this->id);
            $result["id"] = $result["motivation"]["shop_id"];

            // dates
            $this->datesModel->setWhere(array(
                    "motivation_id" => array("simile" => "=", "value" => $this->id))
            );
            $result["dates"] = $this->datesModel->queryRun();

            // items
            $this->itemsModel->setWhere(array(
                    "motivation_id" => array("simile" => "=", "value" => $this->id))
            );
            $result["items"] = $this->datesModel->queryRun();

            // period
            $this->periodModel->setWhere(array(
                    "motivation_id" => array("simile" => "=", "value" => $this->id))
            );
            $result["period"] = $this->datesModel->queryRun();

            // weekdays
            $this->weekdaysModel->setWhere(array(
                    "motivation_id" => array("simile" => "=", "value" => $this->id))
            );
            $result["weekdays"] = $this->weekdaysModel->queryRun();

            return $result;
        }
        return null;
    }

    /**
     * @throws waException
     */
    public function save($data)
    {
        waLog::dump($this->model->getById($this->id));
        if ($motivation = $this->model->getById($this->id)) {
            $this->model->updateById($this->id, $data["motivation"]);
        } else {
            $this->id = $this->model->insert($data);
            /*$this->logModel->log(array(
                "action" => "create",
                "motivation_id" => $this->id,
                "t_motivation_name" => $data["name"],
                "data_before" => "",
                "data_after" => serialize($this->get()),
                "date_time" => date('Y-m-d H:i:s'),
                "contact_id" => wa()->getUser()->getId(),
            ));*/
        }
        return $this->id;
    }

    /**
     * @throws waException
     */
    public function delete()
    {
        if ($motivation = $this->get()) {
            $this->model->updateById($this->id, array(
                "status" => 0
            ));
            $this->logModel->log(array(
                "action" => "delete",
                "motivation_id" => $this->id,
                "t_motivation_name" => $motivation["name"],
                "data_before" => serialize($motivation),
                "data_after" => serialize($this->get()),
                "date_time" => date("Y-m-d H:i:s"),
                "contact_id" => wa()->getUser()->getId(),
            ));
        }
    }

    /**
     * @param $params
     * @return array
     */
    public function getList($params, $shop_id)
    {
        $result = array("data" => array(), "recordsFiltered" => 0, "recordsTotal" => 0);
        $orders = array(
            0 => "name",
        );
        $this->model->setFetch("all");
        $this->model->setSelect(array(
            "id" => null,
            "name" => null,
        ));

        $this->model->setWhere(array(
            "status" => array(
                'simile' => '=',
                'value' => 1
            ),
            "shop_id" => array(
                'simile' => '=',
                'value' => $shop_id
            ),
        ));

        if (!empty($params["search"])) {
            $logic = array(
                array(
                    "logic" => "OR",
                    "fields" => array(
                        "name" => null,
                    )
                ),
            );
            $this->model->setWhere(array(
                "name" => array("simile" => "LIKE", "value" => "%".$params["search"]."%"),
            ), $logic);
        }
        $this->model->setGroupBy(array($this->model->getTableName().".id"));
        $result["recordsFiltered"] = count($this->model->queryRun(false));
        $result["recordsTotal"] = $this->model->countAll();
        $this->model->setLimit($params["length"], $params["start"]);
        if (isset($orders) && isset($orders[$params["column"]])) {
            $this->model->setOrderBy(array($orders[$params["column"]] => $params["direction"]));
        }
        $motivations = $this->model->queryRun();
        foreach ($motivations as $motivation) {
            $motivation["name"] = "<a class='rc-color-black rc-underline rc-color-hover' href='?module=shop&action=motivationEdit&id=".$motivation['id']."'>".htmlspecialchars($motivation['name'], ENT_QUOTES)."</a>";
            array_push($result['data'], array(
                $motivation["name"]
            ));
        }
        return $result;
    }

    public function getTabData($tab)
    {
        $result["motivation"] = $this->get();//TODO распределить получение по закладкам
        return $result;
    }
}