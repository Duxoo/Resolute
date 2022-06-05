<?php

class rcProduct extends rcObject
{
    protected $type = 1;
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
     * rcProduct constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->skuModel = new rcProductSkuModel();
        $this->ingredientModel = new rcIngredientModel();
        $this->productIngredientModel = new rcProductIngredientModel();
        $this->additionModel = new rcProductAdditionsModel();
        if (isset($this->data['type'])) {
            $this->type = $this->data['type'];
        }
    }

    /**
     * @return null
     * @throws waException
     */
    public function getData()
    {
        $result = null;
        if (isset($this->id)) {
            $result['product'] = $this->data;
            $result['sku'] = $this->skuModel->getByField('product_id', $this->id, true);
            $this->ingredientModel->setSelect(array(
                $this->ingredientModel->getTableName() . '.id' => null,
                'name' => null,
                'code' => null,
                'price' => null,
                'dimension' => null,
                'amount' => null,
                'unit' => null
            ));
            $this->ingredientModel->setJoin(array(
                array('right' => $this->productIngredientModel->getTableName(), 'on' => array('id' => 'ingredient_id'))
            ));
            $this->ingredientModel->setWhere(array(
                'product_id' => array('simile' => '=', 'value' => $this->id),
                'sku_id' => array('simile' => '=', 'value' => 0),
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
        $types = rcHelper::getConfigOption('product_type');
        $data['product']['price'] = isset($data['sku'][0]['price']) ? $data['sku'][0]['price'] : 0;
        if ($this->type == 2) {
            $data['sku'][0]['name'] = $data['product']['name'];
        }
        $result = rcHelper::validate($data['product'], 'product');
        if (!$result['error']) {
            if (empty($data['sku'])) {
                $result = array('error' => true, 'message' => 'Артикул не должен быть пустым');
            } else {
                foreach ($data['sku'] as $sku) {
                    $result = rcHelper::validate($sku, 'sku');
                    if ($result['error']) {
                        break;
                    }
                }
                if (!$result['error']) {
                    $result = $this->ingredientsCheck($data['ingredient']);
                    if (!$result['error']) {
                        if (empty($this->id)) {
                            $this->setId($this->model->insert($data['product']));
                            if (!empty($this->data)) {
                                $this->log->log(array(
                                    'action' => 'create',
                                    'product_id' => $this->id,
                                    't_product_name' => $data['name'],
                                    'data_after' => serialize($this->data),
                                ));
                            }
                            $result = array('error' => false, 'message' => $types[$data['type']]['name'] . ' успешно добавлен', 'id' => $this->id);
                        } else {
                            $product = $this->data;
                            if (rcHelper::arrayChangeCheck($product, $data['product'])) {
                                $this->model->updateById($this->id, $data['product']);
                                $this->setId($this->id);
                                $this->log->log(array(
                                    'action' => 'edit',
                                    'product_id' => $this->id,
                                    't_product_name' => $product['name'],
                                    'data_before' => serialize($product),
                                    'data_after' => serialize($this->data),
                                ));
                            }
                            $result = array('error' => false, 'message' => $types[$product['type']]['name'] . ' успешно сохранён', 'id' => $this->id);
                        }
                        if (!empty($data['sku'])) {
                            $this->ingredientsUpdate($data['ingredient'], $data['product']);
                            $this->skuUpdate($data['sku'], $data['product']);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $ingredients
     * @return array|false[]
     * @throws waException
     */
    protected function ingredientsCheck($ingredients)
    {
        $ingredientModel = new rcIngredientModel();
        $ingredientModel->setFetch('all', 'id', 1);
        $ingredientModel->setSelect(array('id' => null, 'name' => null, 'status' => null, 'dimension' => null));
        $list = $ingredientModel->queryRun();
        $errors = array();
        $config = rcHelper::getConfigOption(array('unit', 'dimension'));
        foreach ($ingredients as $key => $ingredient) {
            if (empty($list[$ingredient['ingredient_id']])) {
                $errors[] = 'Ингредиент № '.($key+1).' не найден';
            } else {
                if ($list[$ingredient['ingredient_id']]['status'] != 1) {
                    $errors[] = 'Ингредиент "'.$list[$ingredient['ingredient_id']]['name'].'" недоступен';
                } else {
                    if (empty($config['unit'][$config['dimension'][$list[$ingredient['ingredient_id']]['dimension']]['code']][$ingredient['unit']])) {
                        $errors[] = 'Ошибка получения единицы измерения ингредиента "'.$list[$ingredient['ingredient_id']]['name'].'"';
                    }
                    if ($ingredient['amount'] < 0.001) {
                        $errors[] = 'Кол-во ингредиента "'.$list[$ingredient['ingredient_id']]['name'].'" не может быть меньше 0.001';
                    }
                }
            }
        }
        if(empty($errors)) {
            $result = array('error' => false);
        } else {
            $result = array('error' => true, 'message' => rcHelper::messageMerge($errors));
        }
        return $result;
    }

    /**
     * @param $sku_array
     * @param $product
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
                        $this->skuModel->updateById($sku['id'], $sku);
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
     * @throws waException
     */
    public function ingredientsUpdate($ingredients, $product, $sku = array())
    {
        $old_data = $this->getIngredientNames($ingredients, $sku);
        $this->productIngredientModel->setInsertTable(array('product_id', 'sku_id', 'ingredient_id', 'amount', 'unit'));
        $log = new rcLog('productIngredients');
        $product_types = rcHelper::getConfigOption('product_type');
        $base_log_data = array(
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
        return $this->setStatus('delete');
    }

    /**
     * @param $ingredient_id
     * @throws waException
     */
    public function deleteIngredient($ingredient_id)
    {
        if ($product = $this->model->getById($this->id)) {
            if ($log_data = $this->productIngredientModel->getByField(array('product_id' => $this->id, 'ingredient_id' => $ingredient_id, 'sku_id' => 0))) {
                $product_types = rcHelper::getConfigOption('product_type');
                $ingredient = $this->ingredientModel->getById($ingredient_id);
                $log = new rcLog('productIngredients');
                $log_data['data_before'] = serialize($log_data);
                $log_data['t_product_type'] = $product_types[$product['type']]['name'];
                $log_data['t_product_name'] = $product['name'];
                $log_data['t_ingredient_name'] = $ingredient['name'];
                $log_data['action'] = 'delete';
                $log->log($log_data);
                $this->productIngredientModel->deleteByField(array(
                    'product_id' => $this->id,
                    'ingredient_id' => $ingredient_id
                ));
            }
        }
    }

    /**
     * @param $sku_id
     * @throws waException
     */
    public function deleteSku($sku_id)
    {
        if (isset($this->id)) {
            $product = $this->model->getById($this->id);
            if ($sku = $this->skuModel->getById($sku_id)) {
                $statuses = rcHelper::getConfigOption('status', 'code');
                $this->skuModel->updateById($sku_id, array('status' => $statuses['delete']['id']));
                $this->log->log(array(
                    'action' => 'delete',
                    'product_id' => $this->id,
                    't_sku_name' => $sku['name'],
                    't_product_name' => $product['name'],
                    'data_before' => serialize($sku),
                ));
            }
        }
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainActionData()
    {
        $this->model->setFetch('all', 'status', 1);
        $this->model->setSelect(array('COUNT(*)' => null, 'status' => null));
        $this->model->setWhere(array('type' => array('simile' => '=', 'value' => $this->type)));
        $this->model->setGroupBy(array('status'));
        return array(
            'statuses' => rcHelper::getConfigOption('status'),
            'module' => $this->class_name,
            'name' => $this->type == 1 ? 'Товары' : 'Дополнения',
            'counts' => $this->model->queryRun(),
        );
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainTab()
    {
        if (isset($this->id)) {
            $result = $this->getData();
        }
        $shopClass = new rcShop();
        $result['sku_fields'] = rcHelper::getFields('sku');
        $result['config'] = rcHelper::getConfigOption(array('status', 'unit', 'dimension'));
        $result['shops'] = $shopClass->getAll();
        $result['fields'] = rcHelper::getFields('product');
        return $result;
    }

    public function additionsTab()
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

    /**
     * @param $id
     * @param $status
     * @return array
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

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function getPrepare(&$result, $params)
    {
        $statuses = rcHelper::getConfigOption('status', 'code');
        $result['sku'] = $this->skuModel->getByField(array('product_id' => $this->id, 'status' => $statuses['active']['id']));
        $ingredientModel = new rcIngredientModel();
        $p_i_table = $this->productIngredientModel->getTableName();
        $this->productIngredientModel->setSelect(array($p_i_table.'.*' => null));
        $this->productIngredientModel->setJoin(array(
            'right' => $ingredientModel->getTableName(), 'on' => array('ingredient_id' => 'id', $statuses['active']['id'] => 'status'),
        ));
        $this->productIngredientModel->setWhere(array('product_id' => array('simile' => '=', 'value' => $this->id)));
        $result['ingredients'] = $this->productIngredientModel->queryRun();
    }
}