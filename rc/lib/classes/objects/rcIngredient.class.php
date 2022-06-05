<?php

class rcIngredient extends rcObject
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
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function save($data)
    {
        $result = rcHelper::validate($data, 'ingredient');
        if (!$result['error']) {
            if (isset($this->id) && !empty($this->data)) {
                if (rcHelper::arrayChangeCheck($this->data, $data)) {
                    $this->model->updateById($this->id, $data);
                    $this->log->log(array(
                        'action' => 'edit',
                        'ingredient_id' => $this->id,
                        't_ingredient_name' => $this->data['name'],
                        'data_before' => serialize($this->data),
                        'data_after' => serialize($this->model->getById($this->id)),
                    ));
                    $result = array('error' => false, 'id' => $this->id, 'message' => 'Ингредиент успешно обновлён');
                } else {
                    $result = array('error' => true, 'message' => 'Данные не были изменены');
                }
            } else {
                $this->setId($this->model->insert($data));
                if (!empty($this->data)) {
                    $this->log->log(array(
                        'action' => 'create',
                        'ingredient_id' => $this->id,
                        't_ingredient_name' => $data['name'],
                        'data_before' => '',
                        'data_after' => serialize($this->data),
                    ));
                }
                $result = array('error' => false, 'reload'=>true, 'id' => $this->id, 'message' => 'Ингредиент успешно добавлен');
            }
        }
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
     * @param $params
     * @param null $status
     * @return array
     * @throws waException
     */
    public function getSupplierList($params, $status = null)
    {
        $params['list_type'] = $this->class_name;
        $params['ingredient_id'] = $this->id;
        $supplierCollection = new rcSupplierCollection();
        return $supplierCollection->getList($params, $status);
    }

    /**
     * @param $supplier_id
     * @param $field
     * @param $value
     * @return array|false[]
     * @throws waException
     */
    public function supplierFieldSave($supplier_id, $field, $value)
    {
        $supplier = new rcSupplier($supplier_id);
        return $supplier->ingredientFieldSave($this->data, $field, $value);
    }

    /**
     * @param $data
     * @throws waException
     */
    public function addTemplateData(&$data)
    {
        $data['rc_object_type'] = $data['type'];
        $data['unit_settings'] = rcHelper::getConfigOption('unit');
    }

    /**
     * @return array
     * @throws waException
     */
    protected function mainTab()
    {
        if (isset($this->id)) {
            $data['ingredient'] = $this->getData();
        }
        $data['config'] = rcHelper::getConfigOption(array('status', 'dimension'));
        $data['fields'] = rcHelper::getFields('ingredient');
        return $data;
    }

    /**
     * @return array
     */
    protected function categoriesTab()
    {
        $categoryCollection = new rcIngredientCategoryCollection();
        $result['categories'] = $categoryCollection->getCategories();
        $result['checked'] = $categoryCollection->getChecked($this->id);
        return $result;
    }

    /**
     * @return array
     */
    protected function suppliersTab()
    {
        $result['id'] = $this->id;
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    protected function supplyTab()
    {
        $result['ingredient'] = $this->getData();
        $result['fields'] = rcHelper::getFields('supplyItems');
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    protected function mainCategoryTab()
    {
        $category = new rcIngredientCategory(waRequest::get('id', null, waRequest::TYPE_INT));
        return $category->getFormData();
    }

    /**
     * @return array
     */
    protected function categoryIngredientsTab()
    {
        $categoryCollection = new rcIngredientCategoryCollection();
        $result['checked'] = $categoryCollection->getChecked(waRequest::get('id', null, waRequest::TYPE_INT), true);
        $this->model->setSelect(array('id' => null, 'name' => null));
        $this->model->setWhere(array('status' => array('simile' => '!=', 'value' => 0)));
        $result['ingredients'] = $this->model->queryRun();
        return $result;
    }

    /**
     * @param $id
     * @param $status
     * @return array
     * @throws waException
     */
    public function setCategory($id, $status)
    {
        $category = new rcIngredientCategory($id);
        return $category->setIngredient($this->data, $status);
    }
}