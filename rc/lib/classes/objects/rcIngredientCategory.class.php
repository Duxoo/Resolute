<?php

class rcIngredientCategory extends rcObject
{
    /**
     * @var rcIngredientCategoryModel
     */
    protected $model;

    /**
     * @var rcCategoryIngredientsModel
     */
    protected $categoryIngredientsModel;

    /**
     * rcIngredientCategory constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->categoryIngredientsModel = new rcCategoryIngredientsModel();
    }

    /**
     * @param $data
     * @return array
     * @throws waException
     */
    public function save($data)
    {
        $result = rcHelper::validate($data, 'ingredientCategory');
        if (!$result['error']) {
            if ($repeat = $this->checkRepeat($data)) {
                $result = array('error' => true, 'message' => 'Найдена другая категория #'.intval($repeat['id']).' с названием "'.htmlspecialchars($repeat['name'], ENT_QUOTES).'"');
            } else {
                try {
                    $log = new rcLog('ingredientCategory');
                    if (empty($this->data)) {
                        $id = $this->model->insert($data);
                        $log->log(array(
                            'action' => 'create',
                            'category_id' => $id,
                            't_category_name' => $data['name'],
                            'data_after' => serialize($this->model->getById($id)),
                        ));
                        $result = array('error' => false, 'message' => 'Категория успешно добавлена', 'reload' => true);
                    } else {
                        if (rcHelper::arrayChangeCheck($this->data, $data)) {
                            $this->model->updateById($this->data['id'], $data);
                            $log->log(array(
                                'action' => 'edit',
                                'category_id' => $this->data['id'],
                                't_category_name' => $data['name'],
                                'data_before' => serialize($this->data),
                                'data_after' => serialize($this->model->getById($this->data['id'])),
                            ));
                            $result = array('error' => false, 'message' => 'Категория успешно сохранена');
                        } else {
                            $result = array('error' => true, 'message' => 'Данные не были изменены');
                        }
                    }
                } catch (waException $wa) {
                    $result = array('error' => true, 'message' => $wa->getMessage());
                }
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return array|false
     */
    protected function checkRepeat($data)
    {
        $where['name'] = array('simile' => '=', 'value' => $data['name']);
        if (isset($this->data['id'])) {
            $where['id'] = array('simile' => '!=', 'value' => $this->data['id']);
        }
        $this->model->setWhere($where);
        return $this->model->queryRun();
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     * @throws waException
     */
    public function mainTab()
    {
        if (!empty($this->data)) {
            $result['category'] = $this->data;
        }
        $result['config']['status'] = rcHelper::getConfigOption('status');
        $result['fields'] = rcHelper::getFields('ingredientCategory');
        return $result;
    }

    /**
     * @param $ingredient
     * @param $status
     * @return array
     * @throws waException
     */
    public function setIngredient($ingredient, $status)
    {
        $log = new rcLog('categoryIngredients');
        $log_data['action'] = $status ? 'on' : 'off';
        if (empty($this->data)) {
            $result = array('error' => true, 'message' => 'Не удалось полуть названии категории');
        } else {
            $log_data['category_id'] = $this->data['id'];
            $log_data['t_category_name'] = $this->data['name'];
            $ingredient = new rcIngredient($ingredient);
            $ingredient = $ingredient->getData();
            if (empty($ingredient)) {
                $result = array('error' => true, 'message' => 'Не удалось полуть название ингредиента');
            } else {
                $log_data['ingredient_id'] = $ingredient['id'];
                $log_data['t_ingredient_name'] = $ingredient['name'];
                $check = $this->categoryIngredientsModel->getByField(array('category_id' => $this->data['id'], 'ingredient_id' => $ingredient['id']));
                if ($status) {
                    if ($check) {
                        $result = array('error' => true, 'message' => 'Ингредиент "'.htmlspecialchars($ingredient['name'], ENT_QUOTES).'" уже в категории');
                    } else {
                        $this->categoryIngredientsModel->insert(array('category_id' => $this->data['id'], 'ingredient_id' => $ingredient['id']));
                        $log->log($log_data);
                        $result = array('error' => false, 'message' => 'Ингредиент "'.htmlspecialchars($ingredient['name'], ENT_QUOTES).'" добавлен в категорию "'.htmlspecialchars($this->data['name'], ENT_QUOTES).'"');
                    }
                } else {
                    if ($check) {
                        $this->categoryIngredientsModel->deleteByField(array('category_id' => $this->data['id'], 'ingredient_id' => $ingredient['id']));
                        $log->log($log_data);
                        $result = array('error' => false, 'message' => 'Ингредиент "'.htmlspecialchars($ingredient['name'], ENT_QUOTES).'" удалён из категории "'.htmlspecialchars($this->data['name'], ENT_QUOTES).'"');
                    } else {
                        $result = array('error' => true, 'message' => 'Ингредиент "'.htmlspecialchars($ingredient['name'], ENT_QUOTES).'" уже удалён из категории "'.htmlspecialchars($this->data['name'], ENT_QUOTES).'"');
                    }
                }
            }
        }
        return $result;
    }
}