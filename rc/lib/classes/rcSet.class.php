<?php

class rcSet
{
    /**
     * @var int id of the set
     */
    protected $id;
    /**
     * @var rcSetModel
     */
    protected $model;

    protected $productSetModel;

    protected $productModel;

    public function __construct($id = null)
    {
        $this->setId($id);
        $this->model = new rcSetModel();
        $this->productModel = new rcProductModel();
        $this->productSetModel = new rcProductSetModel();
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

    public function get()
    {
        $result = array();
        if (!empty($this->id)) {
            $result = $this->model->getById($this->id);
            $result['products'] = $this->getProducts();
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function save($data)
    {
        $result = array('error' => false, 'message' => 'Данные уже обновлены');
        $log = new rcLog('set');
        $log_data = array(
            'set_id' => $this->id,
            'contact_id' => wa()->getUser()->getId(),
            't_set_name' => $data['name'],
        );
        $old = array();
        if (isset($this->id) && $old = $this->model->getById($this->id)) {
            if (rcHelper::arrayChangeCheck($old, $data)) {
                $log_data['action'] = 'edit';
                $this->model->updateById($this->id, $data);
                $result['message'] = 'Список успешно обновлён';
            }
        } else {
            $log_data['action'] = 'create';
            $this->id = $this->model->insert($data);
            $result['message'] = 'Список успешно добавлен';
            $result['reload'] = true;
        }
        if (isset($log_data['action'])) {
            if (!empty($old)) {
                $log_data['data_before'] = serialize($old);
            }
            $log_data['data_after'] = serialize($this->model->getById($this->id));
            $log->log($log_data);
        }
        $result['id'] = $this->id;
        return $result;
    }

    protected function getProducts()
    {
        $this->productModel->setSelect(array('id' => null, 'name' => null));
        $this->productModel->setJoin(array(
            array('right' => $this->productSetModel->getTableName(), 'on' => array('id' => 'product_id')),
        ));
        $this->productModel->setWhere(array('set_id' => array('simile' => '=', 'value' => $this->id)));
        return (array)$this->productModel->queryRun();
    }

    public function getList($params)
    {
        $result = array("data" => array(), "recordsFiltered" => 0, "recordsTotal" => 0);
        $orders = array(
            0 => "name",
        );
        $this->model->setFetch("all");
        if (!empty($params["search"])) {
            $this->model->setWhere(array(
                "name" => array("simile" => "LIKE", "value" => "%" . $params["search"] . "%"),
            ));
        }
        $result["recordsFiltered"] = count($this->model->queryRun(false));
        $result["recordsTotal"] = $this->model->countAll();
        $this->model->setLimit($params["length"], $params["start"]);
        if (isset($orders) && isset($orders[$params["column"]])) {
            $this->model->setOrderBy(array($orders[$params["column"]] => $params["direction"]));
        }
        $sets = $this->model->queryRun();
        foreach ($sets as $set) {
            $set["name"] = "<a class='rc-color-black rc-underline rc-color-hover' href='?module=set&action=edit&id=" . htmlspecialchars($set['id'], ENT_QUOTES) . "'>" . htmlspecialchars($set['name'], ENT_QUOTES) . "</a>";
            array_push($result['data'], array(
                $set["name"],
            ));
        }
        return $result;
    }

    /**
     * @param $search
     * @return array|false|int|mixed|string
     */
    public function getListForSelect($search) {
        $this->model->setSelect(array(
            "id" => null,
            "name" => "text"
        ));
        $this->model->setWhere(array(
            "status" => array("simile" => "=", 'value' => 1)
        ));
        if ($search) {
            $this->model->setWhere(array(
                "name" => array("simile" => "LIKE", 'value' => '%'.htmlentities($search, ENT_QUOTES).'%')
            ));
        }
        return $this->model->queryRun();
    }

    public function getTabData($tab)
    {
        $method = 'getTab' . ucfirst($tab);
        $result = array();
        if (method_exists($this, $method)) {
            $result = $this->$method();
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getTabMain()
    {
        return array('set' => $this->model->getById($this->id));
    }

    /**
     * @return array
     */
    public function getTabProduct()
    {
        return array('products' => $this->getProducts());
    }
}