<?php

class rcProduct
{
    /**
     * @var int id of the product
     */
    protected $id;
    /**
     * @var rcProductModel
     */
    protected $model;
    /**
     * @var rcProductSkuModel
     */
    protected $skuModel;
    /**
     * @var rcIngredientModel
     */
    protected $ingredientModel;
    /**
     * @var rcProductIngredientModel
     */
    protected $productIngredientModel;
    /**
     * @var rcProductAdditionsModel
     */
    protected $additionModel;
    /**
     * @var rcLogModel
     */
    protected $logModel;

    /**
     * rcProduct constructor.
     * @param null $id
     * @throws waDbException
     * @throws waException
     */
    public function __construct($id = null)
    {
        $this->setId($id);
        $this->model = new rcProductModel();
        $this->skuModel = new rcProductSkuModel();
        $this->ingredientModel = new rcIngredientModel();
        $this->productIngredientModel = new rcProductIngredientModel();
        $this->additionModel = new rcProductAdditionsModel();
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
     * @return null
     * @throws waException
     */
    public function get()
    {
        $result = null;
        if (isset($this->id)) {
            // product
            $result['product'] = $this->model->getById($this->id);
            // sku
            $result["sku"] = $this->skuModel->getByField('product_id', $this->id, true);
            // ingredients
            $this->ingredientModel->setSelect(array(
                $this->ingredientModel->getTableName() . ".id" => null,
                "name" => null,
                "code" => null,
                "price" => null,
                "dimension_id" => null,
                "amount" => null,
                "unit" => null
            ));
            $this->ingredientModel->setJoin(array(
                array('right' => $this->productIngredientModel->getTableName(), 'on' => array('id' => 'ingredient_id'))
            ));
            $this->ingredientModel->setWhere(array(
                "product_id" => array("simile" => "=", "value" => $this->id),
                "sku_id" => array("simile" => "=", "value" => 0),
            ));
            $result['ingredients'] = $this->ingredientModel->queryRun();
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function save($data)
    {
        $types = wa()->getConfig()->getOption('product_type');
        $data["product"]['price'] = isset($data['sku'][0]['price']) ? $data['sku'][0]['price'] : 0;
        if (empty($this->id)) {
            $this->id = $this->model->insert($data["product"]);
            $this->logModel->log(array(
                "action" => "create",
                "product_id" => $this->id,
                "t_product_name" => $data["name"],
                "data_before" => "",
                "data_after" => serialize($this->get()),
                "contact_id" => wa()->getUser()->getId(),
            ));
            $result = array('error' => false, 'message' => $types[$data['type']]['name'] . ' успешно добавлен', 'id' => $this->id);
        } else {
            $product = $this->model->getById($this->id);
            $this->model->updateById($this->id, $data["product"]);
            foreach ($data["ingredient"] as $key => $i) {
                $data["ingredient"][$key]['product_id'] = $this->id;
            }
            $this->logModel->log(array(
                "action" => "edit",
                "product_id" => $this->id,
                "t_product_name" => $product["name"],
                "data_before" => serialize($product),
                "data_after" => serialize($this->get()),
                "contact_id" => wa()->getUser()->getId(),
            ));
            $result = array('error' => false, 'message' => $types[$product['type']]['name'] . ' успешно сохранён', 'id' => $this->id);
        }
        if (!empty($data["sku"])) {
            $this->ingredientsUpdate($data["ingredient"], $data["product"]);
            $this->skuUpdate($data["sku"], $data["product"]);
        }
        return $result;
    }

    /**
     * @param $sku_array
     * @param $product
     * @throws waDbException
     * @throws waException
     */
    protected function skuUpdate($sku_array, $product)
    {
        $this->skuModel->setFetch('all', 'id');
        $this->skuModel->setWhere(array('product_id' => array('simile' => '=', 'value' => $this->id)));
        $old_sku = $this->skuModel->queryRun();
        $log = new rcLog('productSku');
        $contact_id = wa()->getUser()->getId();
        foreach ($sku_array as $sku) {
            $sku['product_id'] = $this->id;
            if (empty($sku['name'])) {
                $sku['name'] = $product['name'];
            }
            $log_data = $sku;
            $log_data['data_after'] = serialize($sku);
            $log_data['contact_id'] = $contact_id;
            $log_data['t_product_name'] = $product['name'];
            $log_data['t_sku_name'] = $sku['name'];
            if (empty($sku['id'])) {
                $log_data['sku_id'] = $this->skuModel->insert($sku);
                $log_data['action'] = 'create';
                $log->log($log_data);
            } else {
                if (isset($old_sku[$sku['id']])) {
                    if (rcHelper::arrayChangeCheck($old_sku[$sku['id']], $sku)) {
                        $this->skuModel->updateById($sku["id"], $sku);
                        $log_data['sku_id'] = $sku['id'];
                        $log_data['data_before'] = serialize($old_sku[$sku['id']]);
                        $log_data['action'] = 'edit';
                        $log->log($log_data);
                    }
                }
            }
        }
    }

    /**
     * @param $ingredients
     * @param $product
     * @param array $sku
     * @throws waDbException
     * @throws waException
     */
    public function ingredientsUpdate($ingredients, $product, $sku = array())
    {
        $old_data = $this->getIngredientNames($ingredients, $sku);
        $this->productIngredientModel->setInsertTable(array('product_id', 'sku_id', 'ingredient_id', 'amount', 'unit'));
        $log = new rcLog('productIngredients');
        $product_types = wa()->getConfig()->getOption('product_type');
        $base_log_data = array(
            'contact_id' => wa()->getUser()->getId(),
            't_product_name' => $product['name'],
            't_sku_name' => '',
            'product_id' => $this->id,
            't_product_type' => $product_types[$product['type']]['name'],
        );
        if (empty($sku['id'])) {
            $sku['id'] = 0;
        } else {
            $base_log_data['sku_id'] = $sku['id'];
            $base_log_data['t_sku_name'] = ' артикула '.$sku['name'];
        }
        foreach ($ingredients as $ingredient) {
            if (!empty($ingredient['ingredient_id']) && !empty($old_data['names'][$ingredient['ingredient_id']])) {
                $ingredient['count'] = abs($ingredient['count']);
                $log_data = $ingredient + $base_log_data;
                $log_data['data_after'] = serialize($ingredient);
                $log_data['t_ingredient_name'] = $old_data['names'][$ingredient['ingredient_id']];
                if (isset($old_data['old'][$ingredient['ingredient_id']])) {
                    unset($old_data['old'][$ingredient['ingredient_id']]);
                    if (rcHelper::arrayChangeCheck($old_data['old'][$ingredient['ingredient_id']], $ingredient)) {
                        $this->productIngredientModel->updateByField(array('product_id' => $this->id, 'ingredient_id' => $ingredient['ingredient_id'], 'sku_id' => $sku['id']), $ingredient);
                        $log_data['action'] = 'edit';
                        $log_data['data_before'] = serialize($old_data['old'][$ingredient['ingredient_id']]);
                        $log->log($log_data);
                    }
                } else {
                    $ingredient['sku_id'] = $sku['id'];
                    $ingredient['product_id'] = $this->id;
                    $this->productIngredientModel->insert($ingredient);
                    $log_data['action'] = 'create';
                    $log->log($log_data);
                }
            }
        }
        foreach ($old_data['old'] as $val) {
            $log_data = $val + $base_log_data;
            $log_data['data_before'] = serialize($val);
            $log_data['t_ingredient_name'] = isset($old_data['names'][$val['ingredient_id']]) ? $old_data['names'][$val['ingredient_id']] : '?';
            $this->productIngredientModel->deleteByField(array('ingredient_id' => $val['ingredient_id'],'sku_id' => $val['sku_id'],'product_id' => $val['product_id']));
            $log_data['action'] = 'delete';
            $log->log($log_data);
        }
    }

    protected function getIngredientNames($ingredients, $sku)
    {
        if (empty($sku['id'])) {
            $sku['id'] = 0;
        }
        $this->productIngredientModel->setFetch('all', 'ingredient_id');
        $this->productIngredientModel->setWhere(array(
            'product_id' => array('simile' => '=', 'value' => $this->id),
            'sku_id' => array('simile' => '=', 'value' => $sku['id']),
        ));
        $result['old'] = (array)$this->productIngredientModel->queryRun();
        $ingredient_id = array();
        foreach ($result['old'] as $id => $val) {
            $ingredient_id[$id] = $id;
        }
        foreach ($ingredients as $val) {
            $ingredient_id[$val['ingredient_id']] = $val['ingredient_id'];
        }
        $this->ingredientModel->setFetch('all', 'id', 1);
        $this->ingredientModel->setSelect(array('id' => null, 'name' => null));
        $this->ingredientModel->setWhere(array('id' => array('simile' => 'IN', 'value' => $ingredient_id)));
        $result['names'] = $this->ingredientModel->queryRun();
        return $result;
    }

    /**
     * @throws waException
     */
    public function delete()
    {
        if ($product = $this->get()) {
            $this->model->updateById($this->id, array(
                "status" => 0
            ));
            $this->logModel->log(array(
                "action" => "delete",
                "product_id" => $this->id,
                "t_product_name" => $product['product']["name"],
                "data_before" => serialize($product),
                "data_after" => serialize($this->get()),
                "contact_id" => wa()->getUser()->getId(),
            ));
        }
    }

    /**
     * @param $ingredient_id
     * @throws waDbException
     * @throws waException
     */
    public function deleteIngredient($ingredient_id)
    {
        if ($product = $this->get()) {
            if ($log_data = $this->productIngredientModel->getByField(array('product_id' => $this->id, 'ingredient_id' => $ingredient_id, 'sku_id' => 0))) {
                $product_types = wa()->getConfig()->getOption('product_type');
                $ingredient = $this->ingredientModel->getById($ingredient_id);
                $log = new rcLog('productIngredients');
                $log_data['data_before'] = serialize($log_data);
                $log_data['t_product_type'] = $product_types[$product['type']]['name'];
                $log_data['t_product_name'] = $product['product']['name'];
                $log_data['t_ingredient_name'] = $ingredient['name'];
                $log_data['action'] = 'delete';
                $log->log($log_data);
                $this->productIngredientModel->deleteByField(array(
                    "product_id" => $this->id,
                    "ingredient_id" => $ingredient_id
                ));
            }
        }
    }

    public function deleteSku($sku_id)
    {
        if ($product = $this->get()) {
            $this->skuModel->deleteById($sku_id);
            /*$this->logModel->log(array(
                "action" => "delete",
                "product_id" => $this->id,
                "t_product_name" => $product["name"],
                "data_before" => serialize($product),
                "data_after" => serialize($this->get()),
                "date_time" => date("Y-m-d H:i:s"),
                "contact_id" => wa()->getUser()->getId(),
            ));*/
        }
    }

    /**
     * @param $params
     * @param bool $addition
     * @return array
     */
    public function getList($params, $addition = false)
    {
        $result = array("data" => array(), "recordsFiltered" => 0, "recordsTotal" => 0);
        $orders = array(
            0 => "name",
            1 => "price"
        );
        $this->model->setFetch("all");
        $this->model->setSelect(array(
            "id" => null,
            "name" => null,
            "price" => null,
        ));
        $type = 1;
        if ($addition) {
            $type = 2;
        }
        $this->model->setWhere(array(
            "type" => array(
                'simile' => '=',
                'value' => $type
            )
        ));
        if (!empty($params["search"])) {
            $logic = array(
                array(
                    "logic" => "OR",
                    "fields" => array(
                        "name" => null,
                        "price" => null
                    )
                ),
            );
            $this->model->setWhere(array(
                "name" => array("simile" => "LIKE", "value" => "%" . $params["search"] . "%"),
                "price" => array("simile" => "LIKE", "value" => "%" . $params["search"] . "%"),
            ), $logic);
        }
        $this->model->setGroupBy(array($this->model->getTableName() . ".id"));
        $result["recordsFiltered"] = count($this->model->queryRun(false));
        $result["recordsTotal"] = $this->model->countAll();
        $this->model->setLimit($params["length"], $params["start"]);
        if (isset($orders) && isset($orders[$params["column"]])) {
            $this->model->setOrderBy(array($orders[$params["column"]] => $params["direction"]));
        }
        $products = $this->model->queryRun();
        $module = "product";
        if ($addition) {
            $module = "addition";
        }
        foreach ($products as $product) {
            $product["name"] = "<a class='rc-color-black rc-underline rc-color-hover' href='?module=" . $module . "&action=edit&id=" . htmlspecialchars($product['id'], ENT_QUOTES) . "'>" . htmlspecialchars($product['name'], ENT_QUOTES) . "</a>";
            array_push($result['data'], array(
                $product["name"],
                wa_currency($product["price"], 'RUB')
            ));
        }
        return $result;
    }

    /**
     * @param $search
     * @return array|false|int|mixed|string
     */
    public function getListForSelect($search)
    {
        $this->model->setSelect(array(
            "id" => null,
            "name" => "text"
        ));
        $this->model->setWhere(array(
            "type" => array("simile" => "=", 'value' => 1)
        ));
        if ($search) {
            $this->model->setWhere(array(
                "name" => array("simile" => "LIKE", 'value' => '%' . htmlentities($search, ENT_QUOTES) . '%')
            ));
        }
        return $this->model->queryRun();
    }

    /**
     * @return bool
     * @throws waDbException
     * @throws waException
     */
    public function getFormData()
    {
        $shopClass = new rcShop();
        $result['product'] = $this->get();
        if ($result['product']) {
            $result['status_settings'] = wa()->getConfig()->getOption("status");
            $result['unit_settings'] = wa()->getConfig()->getOption("unit");
            $result['dimension_settings'] = wa()->getConfig()->getOption("dimension");
            $result['shops'] = $shopClass->getAll();
        } else {
            $result = false;
        }
        return $result;
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
     * @return bool
     * @throws waDbException
     * @throws waException
     */
    public function getTabMain()
    {
        return $this->getFormData();
    }

    public function getTabAdditions()
    {
        $result = array();
        if (!empty($this->id)) {
            $this->model->setSelect(array('id' => null, 'name' => null,));
            $this->model->setWhere(array(
                'status' => array('simile' => '!=', 'value' => 0),
                'type' => array('simile' => '=', 'value' => 2),
            ));
            $result['products'] = $this->model->queryRun();
            $this->additionModel->setFetch('all', 'addition_id');
            $this->additionModel->setWhere(array(
                'product_id' => array('simile' => '=', 'value' => $this->id),
            ));
            $result['additions'] = $this->additionModel->queryRun();
        }
        return $result;
    }

    public function setChild($id, $type, $status = 1)
    {
        $result = array('error' => true, 'message' => 'Не найден метод записи');
        $method = 'set'.ucfirst($type);
        if (method_exists($this, $method)) {
            $result = $this->$method($id, $status);
        }
        return $result;
    }

    /**
     * @param $id
     * @param $status
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function setAddition($id, $status)
    {
        $log = new rcLog('productAdditions');
        $log_data['status_after'] = $status;
        $this->model->setFetch('all', 'id', 1);
        $this->model->setSelect(array('id' => null, 'name' => null));
        $this->model->setWhere(array('id' => array('simile' => 'IN', 'value' => array($id, $this->id))));
        $names = $this->model->queryRun();
        $result = array('error' => true, 'message' => 'Не удалось полуть названии продукта или дополнения');
        if (!empty($names[$id]) && !empty($names[$this->id])) {
            $log_data['t_addition_name'] = $names[$id];
            $log_data['t_product_name'] = $names[$this->id];
            if ($addition = $this->additionModel->getByField(array('product_id' => $this->id, 'addition_id' => $id))) {
                if ($addition['status'] != $status) {
                    $this->additionModel->updateByField(array('product_id' => $this->id, 'addition_id' => $id), array('status' => $status));
                    $log_data['product_addition_id'] = $addition['id'];
                    $log_data['status_before'] = $addition['status'];
                    $log_data['action'] = 'edit';
                    $log->log($log_data);
                }
            } else {
                $log_data['product_addition_id'] = $this->additionModel->insert(array('product_id' => $this->id, 'addition_id' => $id, 'status' => $status));
                $log_data['action'] = 'create';
                $log->log($log_data);
            }
            $result = array('error' => false, 'message' => 'Дополнение '.$log_data['t_addition_name'].' '.($status ? '' : 'не').'доступно для товара '.$log_data['t_product_name']);
        }
        return $result;
    }
}