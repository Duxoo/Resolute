<?php

class rcSupplier extends rcObject
{
    protected $model;
    /**
     * @var rcSupplierIngredientsModel
     */
    protected $supplierIngredientsModel;

    /**
     * rcSupplier constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->supplierIngredientsModel = new rcSupplierIngredientsModel();
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return array
     */
    public function save($data)
    {
        try {
            $result = rcHelper::validate($data, 'supplier');
            if (!$result['error']) {
                if ($result['message'] = $this->repeatCheck($data)) {
                    $result['error'] = true;
                } else {
                    if (empty($this->id)) {
                        $this->setId($this->model->insert($data));
                        $this->log->log(array(
                            'action' => 'create',
                            'supplier_id' => $this->id,
                            't_supplier_name' => $data['name'],
                            'data_after' => serialize($this->data),
                        ));
                        $result = array('error' => false, 'id' => $this->id, 'message' => 'Точка ' . htmlspecialchars($data['name'], ENT_QUOTES) . ' успешно добавлена', 'reload' => true);
                    } else {
                        $supplier = $this->data;
                        if (rcHelper::arrayChangeCheck($supplier, $data)) {
                            $this->model->updateById($data['id'], $data);
                            $this->setId($this->id);
                            $this->log->log(array(
                                'action' => 'edit',
                                'supplier_id' => $this->id,
                                't_supplier_name' => $data['name'],
                                'data_before' => serialize($supplier),
                                'data_after' => serialize($this->data),
                            ));
                            $result = array('error' => false, 'id' => $this->id, 'message' => 'Точка ' . htmlspecialchars($data['name'], ENT_QUOTES) . ' успешно обновлена');
                        } else {
                            $result = array('error' => true, 'message' => 'Данные не были изменены');
                        }
                    }
                }
            }
        } catch (waException $wa) {
            $result = array('error' => true, 'message' => $wa->getMessage());
        }
        return $result;
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getIngredientList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['supplier_id'] = $this->id;
        $ingredientCollection = new rcIngredientCollection();
        return $ingredientCollection->getList($params, $status);
    }

    /**
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getShopList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['supplier_id'] = $this->id;
        $shopCollection = new rcShopCollection();
        return $shopCollection->getList($params, $status);
    }

    /**
     * @param $ingredient
     * @param $field
     * @param $value
     * @return array|false[]
     * @throws waException
     */
    public function ingredientFieldSave($ingredient, $field, $value)
    {
        if (empty($ingredient)) {
            $result = array('error' => true, 'message' => 'Ошибка получения ингредиента');
        } else {
            if (empty($this->id)) {
                $result = array('error' => true, 'message' => 'Ошибка получения поставщика');
            } else {
                $supplier = $this->model->getById($this->id);
                $result = rcHelper::validateField($field, $value, 'supplierIngredients');
                if (!$result['error']) {
                    $fields = rcHelper::getFields('supplierIngredients');
                    $field_data = $fields[$field];
                    $ingredient = new rcIngredient($ingredient);
                    $ingredient = $ingredient->getData();
                    $old = $this->supplierIngredientsModel->getById(array($this->id, $ingredient['id']));
                    $log = new rcLog('supplierIngredients');
                    $log_data = array(
                        'supplier_id' => $this->id,
                        'ingredient_id' => $ingredient['id'],
                        'action' => $field == 'status' ? ($value ? 'on' : 'off') : 'edit',
                        'field' => $field,
                        'value_after' => $value,
                        't_ingredient_name' => $ingredient['name'],
                        't_supplier_name' => $supplier['name'],
                    );
                    if (empty($old)) {
                        if ($field == 'status') {
                            $result = array('error' => true, 'message' => 'Прежде чем привязать ингредиент к поставщику, укажите единицу измерения');
                        } else {
                            $this->supplierIngredientsModel->insert(array('supplier_id' => $this->id, 'ingredient_id' => $ingredient['id'], $field => $value, 'status' => 0));
                            $log->log($log_data);
                            $result = array('error' => false, 'message' => 'Поле ' . $field_data['name'] . ' успешно обновлено');
                        }
                    } else {
                        $log_data['value_before'] = $old[$field];
                        if ($field == 'status' && $value == 1 && empty($old['unit'])) {
                            $result = array('error' => true, 'message' => 'Прежде чем привязать ингредиент к поставщику, укажите единицу измерения');
                        } else {
                            if ($value != $old[$field]) {
                                $this->supplierIngredientsModel->updateById(array($this->id, $ingredient['id']), array($field => $value));
                                $log->log($log_data);
                                $result = array('error' => false, 'message' => 'Поле ' . $field_data['name'] . ' успешно обновлено');
                            } else {
                                $result = array('error' => true, 'message' => 'Поле ' . $field_data['name'] . ' уже изменено');
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    protected function mainTab()
    {
        if (!empty($this->id)) {
            $result['supplier'] = $this->data;
        }
        $result['config']['status'] = rcHelper::getConfigOption('status');
        $result['fields'] = rcHelper::getFields('supplier');
        return $result;
    }

    /**
     * @return array
     */
    protected function shopTab()
    {
        $result['id'] = $this->id;
        return $result;
    }

    /**
     * @return array
     */
    protected function ingredientsTab()
    {
        $result['id'] = $this->id;
        return $result;
    }

    /**
     * @param $shop_id
     * @param $on
     * @return array
     * @throws waException
     */
    protected function setShop($shop_id, $on)
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора точки');
        } else {
            $shop = new rcShop($shop_id);
            $result = $shop->setChild($this->id, $this->class_name, $on);
        }
        return $result;
    }
}