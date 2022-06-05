<?php

class rcIngredient
{
    /**
     * @var int id of the ingredient
     */
    protected $id;

    /**
     * @var rcIngredientModel
     */
    protected $model;

    /**
     * @var rcLog
     */
    protected $logModel;

    /**
     * rcIngredient constructor.
     * @param int|null $id
     * @throws waDbException
     * @throws waException
     */
    public function __construct($id = null)
    {
        $this->setId($id);
        $this->model = new rcIngredientModel();
        $this->logModel = new rcLog(rcHelper::getClassNameForLog($this));
    }

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
     * @param array $data
     * @throws waException
     */
    public function save($data)
    {
        if ($ingredient = $this->model->getById($data['id'])) {
            $this->model->updateById($data['id'], $data);
            $this->logModel->log(array(
                "action" => "edit",
                "ingredient_id" => $this->id,
                "t_ingredient_name" => $ingredient["name"],
                "data_before" => serialize($ingredient),
                "data_after" => serialize($this->get()),
                "date_time" => date('Y-m-d H:i:s'),
                "contact_id" => wa()->getUser()->getId(),
            ));
        } else {
            $this->id = $this->model->insert($data);
            $this->logModel->log(array(
                "action" => "create",
                "ingredient_id" => $this->id,
                "t_ingredient_name" => $data["name"],
                "data_before" => "",
                "data_after" => serialize($this->get()),
                "date_time" => date('Y-m-d H:i:s'),
                "contact_id" => wa()->getUser()->getId(),
            ));
        }
        return $this->id;
    }

    public function delete()
    {
        if ($ingredient = $this->get()) {
            $this->model->updateById($this->id, array(
                "status" => 0
            ));
            $this->logModel->log(array(
                "action" => "delete",
                "ingredient_id" => $this->id,
                "t_ingredient_name" => $ingredient["name"],
                "data_before" => serialize($ingredient),
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
            0 => $this->model->getTableName().'.name',
            1 => $this->model->getTableName().'.code',
        );
        $this->model->setFetch('all');
        $this->model->setSelect(array(
            'id' => null,
            'name' => null,
            'code' => null,
            'status' => null,
            'price' => null
        ));
        if (!empty($params['search'])) {
            $logic = array(
                array(
                    'logic' => 'OR',
                    'fields' => array(
                        $this->model->getTableName().'.name' => null,
                        $this->model->getTableName().'.code' => null,
                    )
                ),
            );
            $this->model->setWhere(array(
                $this->model->getTableName().'.name' => array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%'),
                $this->model->getTableName().'.code' => array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%'),
            ), $logic);
        }
        $this->model->setGroupBy(array($this->model->getTableName().'.id'));
        $result['recordsFiltered'] = count($this->model->queryRun(false));
        $result['recordsTotal'] = $this->model->countAll();
        $this->model->setLimit($params['length'], $params['start']);
        if (isset($orders) && isset($orders[$params['column']])) {
            $this->model->setOrderBy(array($orders[$params['column']] => $params['direction']));
        }
        $ingredients = $this->model->queryRun();
        $statuses = wa()->getConfig()->getOption('status');
        foreach ($ingredients as $ingredient) {
            foreach ($statuses as $id => $status) {
                if ($id == $ingredient['status']) {
                    $ingredient['status'] = $status['name'];
                    break;
                }
            }
            $ingredient['name'] = "<a class='rc-color-black rc-underline rc-color-hover' href='?module=ingredient&action=edit&id=".$ingredient['id']."'>".htmlspecialchars($ingredient['name'], ENT_QUOTES)."</a>";
            array_push($result['data'], array(
                $ingredient['name'],
                htmlspecialchars($ingredient['code'], ENT_QUOTES),
                $ingredient['price']." ₽",
                htmlspecialchars($ingredient['status'], ENT_QUOTES)
            ));
        }
        return $result;
    }

    /**
     * @param $search
     * @param $ingredient_ids
     * @return array|int|mixed|string
     */
    public function getListForSelect($search, $ingredient_ids) {
        $where = array();
        $ingredient_ids = explode('+', $ingredient_ids);
        if (isset($this->id)) {
            unset($ingredient_ids[array_search($this->id, $ingredient_ids)]);
        }
        if (!empty($search)) {
            $where = array("name" => array("simile" => "LIKE", 'value' => '%'.htmlentities($search, ENT_QUOTES).'%'));
        }
        if (!empty($ingredient_ids)) {
            $where['id'] = array('simile' => 'NOT IN', 'value' => $ingredient_ids);
        }
        $this->model->setSelect(array("id" => null, "name" => "text"));
        if (!empty($where)) {
            $this->model->setWhere($where);
        }
        return $this->model->queryRun();
    }

    /**
     * @param $data
     * @throws waException
     */
    public function addTemplateData(&$data)
    {
        if (!empty($data['id'])) {
            if ($data['type'] == 'sku') {
                $sku = new rcSku($data['id']);
                $data += $sku->getFormData();
            } else {
                $product = new rcProduct($data['id']);
                $data['product'] = $product->get();
            }
        }
        $data['rc_object_type'] = $data['type'];
        $data["unit_settings"] = wa()->getConfig()->getOption("unit");
    }
}