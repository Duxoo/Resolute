<?php

class rcScreen
{
    /**
     * @var int id of the product
     */
    protected $id;
    /**
     * @var rcScreenModel
     */
    protected $model;
    /**
     * @var rcScreenElementModel
     */
    protected $screenElementModel;
    /**
     * @var rcLog
     */
    protected $logModel;

    /**
     * rcScreen constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->setId($id);
        $this->model = new rcScreenModel();
        $this->screenElementModel = new rcScreenElementModel();
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
    public function get()
    {
        if (isset($this->id)) {
            $result["info"] = $this->model->getById($this->id);
            $this->screenElementModel->setWhere(array(
                $this->screenElementModel->getTableName().'.screen_id' => array('simile' => '=', 'value' => $this->id)
            ));
            $this->screenElementModel->setOrderBy(array("sort" => "ASC"));
            $result["positions"] = $this->screenElementModel->queryRun();
            return $result;
        }
        return null;
    }

    /**
     * @param $name
     * @param $positions
     */
    public function save($name, $positions)
    {
        foreach (array_reverse($positions) as $position) {
            if ($position["name"]) {
                $positions = array_slice($positions, 0, $position['sort'] + 1);
                break;
            }
        }
        if ($screen = $this->model->getById($this->id)) {
            $this->model->updateById($this->id, array(
                "name" => $name,
            ));
            $this->screenElementModel->deleteByField("screen_id", $this->id);
            $this->logModel->log(array(
                "action" => "edit",
                "screen_id" => $this->id,
                "t_screen_name" => $screen["name"],
                "data_before" => serialize($screen),
                "data_after" => serialize($this->get()),
                "date_time" => date('Y-m-d H:i:s'),
                "contact_id" => wa()->getUser()->getId(),
            ));
        } else {
            $this->model->setFetch("field");
            $this->model->setSelect(array(
                "MAX(sort) + 1" => null
            ));
            $this->id = $this->model->insert(array(
                "name" => $name,
                "sort" => $this->model->queryRun()
            ));
            $this->logModel->log(array(
                "action" => "create",
                "screen_id" => $this->id,
                "t_screen_name" => $name,
                "data_before" => "",
                "data_after" => serialize($this->get()),
                "date_time" => date('Y-m-d H:i:s'),
                "contact_id" => wa()->getUser()->getId(),
            ));
        }
        foreach ($positions as &$position) {
            $position["screen_id"] = $this->id;
        }
        $this->screenElementModel->multipleInsert($positions);
        return $this->id;
    }

    public function delete() {
        if ($screen = $this->model->getById($this->id)) {
            $this->model->updateById($this->id, array(
                "status" => 0
            ));
            $this->logModel->log(array(
                "action" => "delete",
                "screen_id" => $this->id,
                "t_screen_name" => $screen["name"],
                "data_before" => serialize($screen),
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
            0 => "sort",
            1 => 'name',
        );
        $this->model->setFetch('all');
        $this->model->setSelect(array(
            'id' => null,
            'name' => null,
            'sort' => null,
        ));
        if (!empty($params['search'])) {
            $logic = array(
                array(
                    'logic' => 'OR',
                    'fields' => array(
                        $this->model->getTableName().'.name' => null,
                    )
                ),
            );
            $this->model->setWhere(array(
                $this->model->getTableName().'.name' => array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%'),
            ), $logic);
        }
        $this->model->setGroupBy(array($this->model->getTableName().'.id'));
        $result['recordsFiltered'] = count($this->model->queryRun(false));
        $result['recordsTotal'] = $this->model->countAll();
        $this->model->setLimit($params['length'], $params['start']);
        if (isset($orders) && isset($orders[$params['column']])) {
            $this->model->setOrderBy(array($orders[$params['column']] => $params['direction']));
        }
        $screens = $this->model->queryRun();
        $icon = file_get_contents(wa()->getConfig()->getAppPath('img/svg/')."menu.svg");
        foreach ($screens as $screen) {
            $screen['name'] = "<a class='rc-color-black rc-underline rc-color-hover' href='?module=screen&action=edit&id=".$screen['id']."'>".htmlspecialchars($screen['name'], ENT_QUOTES)."</a>";
            array_push($result['data'], array(
                "<div class='js-sort rc-icon20 rc-pointer rc-fill-dark rc-fill-hover' data-item='".$screen['id']."'>".$icon."</div>",
                $screen['name'],
            ));
        }
        return $result;
    }

    public function getListForSelect($search) {
        $this->model->setSelect(array(
            "id" => null,
            "name" => "text"
        ));
        if ($search) {
            $this->model->setWhere(array(
                "name" => array("simile" => "LIKE", 'value' => '%'.htmlentities($search, ENT_QUOTES).'%')
            ));
        }
        if ($this->id) {
            $this->model->setWhere(array(
                "id" => array("simile" => "!=", 'value' => $this->id)
            ));
        }
        return $this->model->queryRun();
    }

    /**
     * @param $items
     * @throws waException
     */
    public function updateSort($items) {
        foreach ($items as $sort => $id) {
            $this->model->setUpdateRow(array(
                "data" => array("sort" => $sort),
                "where" => array("id" => $id),
            ));
        }
        $this->model->multiUpdate();
    }

    public function getTabData($tab)
    {
        $result["screen"] = $this->get();//TODO распределить получение по закладкам
        return $result;
    }
}