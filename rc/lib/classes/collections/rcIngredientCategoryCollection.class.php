<?php

class rcIngredientCategoryCollection extends rcCollection
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
     * @param string|array $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->categoryIngredientsModel = new rcCategoryIngredientsModel();
    }

    /**
     * @param array $row
     * @param string $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListName(&$row, $template)
    {
        $row['action'] = 'categoryEdit';
        $row['class_name'] = 'ingredient';
        parent::setListName($row, $template);
    }

    /**
     * @param int $id
     * @param bool $category
     * @return array
     */
    public function getChecked($id, $category = false)
    {
        $this->categoryIngredientsModel->setFetch('all', $category ? 'ingredient_id' : 'category_id', 1);
        $this->categoryIngredientsModel->setWhere(array(($category ? 'category_id' : 'ingredient_id') => array('simile' => '=', 'value' => $id)));
        return (array)$this->categoryIngredientsModel->queryRun();
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $this->model->setSelect(array('id' => null, 'name' => null));
        $this->model->setWhere(array('status' => array('simile' => '!=', 'value' => 0)));
        return (array)$this->model->queryRun();
    }

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function getPrepare(&$result, $params) {
        $ingredientModel = new rcIngredientModel();
        $this->categoryIngredientsModel->setFetch('all', 'category_id', 2);
        $this->categoryIngredientsModel->setSelect(array($this->categoryIngredientsModel->getTableName().'.*' => null));
        $this->categoryIngredientsModel->setJoin(array(
            array('right' => $ingredientModel->getTableName(), 'on' => array('ingredient_id' => 'id'))
        ));
        $statuses = rcHelper::getConfigOption('status', 'code');
        $this->categoryIngredientsModel->setWhere(array(
            'status' => array('simile' => '=', 'value' => $statuses['active']['id'])
        ));
        $ingredients = (array)$this->categoryIngredientsModel->queryRun();
        foreach ($result as $key => $category) {
            if (isset($ingredients[$category['id']])) {
                $result[$key]['ingredients'] = $ingredients[$category['id']];
            } else {
                unset($result[$key]);
            }
        }
    }
}