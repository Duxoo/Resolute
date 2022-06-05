<?php

class rcScreen extends rcObject
{
    /**
     * @var rcScreenModel
     */
    protected $model;
    /**
     * @var rcScreenElementModel
     */
    protected $screenElementModel;

    /**
     * rcScreen constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->screenElementModel = new rcScreenElementModel();
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        if (isset($this->id)) {
            $result['info'] = $this->data;
            $this->screenElementModel->setWhere(array(
                $this->screenElementModel->getTableName().'.screen_id' => array('simile' => '=', 'value' => $this->id)
            ));
            $this->screenElementModel->setOrderBy(array('sort' => 'ASC'));
            $result['positions'] = $this->screenElementModel->queryRun();
            return $result;
        }
        return null;
    }

    /**
     * @param $name
     * @param $positions
     * @return bool|int|resource
     * @throws waException
     */
    public function save($name, $positions)
    {
        foreach (array_reverse($positions) as $position) {
            if ($position['name']) {
                $positions = array_slice($positions, 0, $position['sort'] + 1);
                break;
            }
        }
        $screen = $this->data;
        if ($screen) {
            $this->model->updateById($this->id, array(
                'name' => $name,
            ));
            $this->screenElementModel->deleteByField('screen_id', $this->id);
            $this->setId($this->id);
            $this->log->log(array(
                'action' => 'edit',
                'screen_id' => $this->id,
                't_screen_name' => $screen['name'],
                'data_before' => serialize($screen),
                'data_after' => serialize($this->data),
            ));
        } else {
            $this->model->setFetch('field');
            $this->model->setSelect(array(
                'MAX(sort) + 1' => null
            ));
            $this->setId($this->model->insert(array('name' => $name, 'sort' => $this->model->queryRun())));
            if ($this->data) {
                $this->log->log(array(
                    'action' => 'create',
                    'screen_id' => $this->id,
                    't_screen_name' => $name,
                    'data_after' => serialize($this->data),
                ));
            }
        }
        foreach ($positions as &$position) {
            $position['screen_id'] = $this->id;
        }
        $this->screenElementModel->multipleInsert($positions);//TODO нормальное сохранение с логированием
        return $this->id;
    }

    /**
     * @throws waException
     */
    public function delete() {
        return $this->setStatus('delete');
    }

    /**
     * @return array
     */
    public function getName()
    {
        if (empty($this->id)) {
            $result = array('error' => true, 'message' => 'Ошибка получения идентификатора');
        } else {
            if (empty($this->data['name'])) {
                $result = array('error' => true, 'message' => 'Ошибка получения названия меню');
            } else {
                $result = array('error' => false, 'name' => $this->data['name']);
            }
        }
        return $result;
    }

    /**
     * @param $items
     * @throws waException
     */
    public function updateSort($items)
    {
        foreach ($items as $sort => $id) {
            $this->model->setUpdateRow(array(
                'data' => array('sort' => $sort),
                'where' => array('id' => $id),
            ));
        }
        $this->model->multiUpdate();
    }

    public function mainTab()
    {
        $result['screen'] = $this->getData();
        return $result;
    }

    public function shopTab()
    {
        $result['screen'] = $this->getData();
        return $result;
    }
}