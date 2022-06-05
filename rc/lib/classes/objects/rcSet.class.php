<?php

class rcSet extends rcObject
{
    /**
     * @var rcSetModel
     */
    protected $model;
    /**
     * @var rcProductSetModel
     */
    protected $productSetModel;
    /**
     * @var rcProductModel
     */
    protected $productModel;

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->productModel = new rcProductModel();
        $this->productSetModel = new rcProductSetModel();
    }

    public function getData()
    {
        $result = array();
        if (!empty($this->data)) {
            $result = $this->data;
            $result['products'] = $this->getProducts();
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
        $result = array('error' => false, 'message' => 'Данные уже обновлены');
        $log_data = array(
            'set_id' => $this->id,
            't_set_name' => $data['name'],
        );
        $old = array();
        if (isset($this->id)) {
            $old = $this->data;
            if (rcHelper::arrayChangeCheck($old, $data)) {
                $log_data['action'] = 'edit';
                $this->model->updateById($this->id, $data);
                $this->setId($this->id);
                $result['message'] = 'Список успешно обновлён';
            }
        } else {
            $log_data['action'] = 'create';
            $this->setId($this->model->insert($data));
            $result['message'] = 'Список успешно добавлен';
            $result['reload'] = true;
        }
        if (isset($log_data['action'])) {
            if (!empty($old)) {
                $log_data['data_before'] = serialize($old);
            }
            $log_data['data_after'] = serialize($this->data);
            $this->log->log($log_data);
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

    /**
     * @param $params
     * @param bool $in
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    public function getProductsList($params, $in = true)
    {
        $result = array('data' => array(), 'recordsFiltered' => 0, 'recordsTotal' => 0);
        $orders = array(
            0 => 'name',
        );
        $this->productModel->setFetch('field');
        $this->productModel->setSelect(array('COUNT(DISTINCT id)' => null));
        $where = array();
        $join_type = 'LEFT';
        if (!empty($this->id)) {
            if ($in) {
                $join_type = 'INNER';
                $where['set_id'] = array('simile' => '=', 'value' => $this->id);
            } else {
                $where['set_id'] = array('simile' => '!=', 'value' => $this->id);
                $where['set_id '] = array('simile' => 'IS', 'value' => null);
            }
        }
        $this->productModel->setJoin(array(
            array('type' => $join_type, 'right' => $this->productSetModel->getTableName(), 'on' => array('id' => 'product_id'))
        ));
        $this->productModel->setWhere($where, 'OR');
        $this->productModel->showQuery();
        $result['recordsTotal'] = $this->productModel->queryRun(false);
        if (!empty($params['search'])) {
            $where['name'] = array('simile' => 'LIKE', 'value' => '%' . $params['search'] . '%');
            $this->productModel->setWhere($where, array(
                'name' => null,
                array(
                    'logic' => 'OR',
                    'fields' =>array(
                        'set_id' => null,
                        'set_id ' => null,
                    )
                )
            ));
        }
        $result['recordsFiltered'] = $this->productModel->queryRun(false);
        $this->productModel->setFetch('all');
        $this->productModel->setSelect(array('*' => null));
        if (isset($orders) && isset($orders[$params['column']])) {
            $this->productModel->setOrderBy(array($orders[$params['column']] => $params['direction']));
        }
        $this->productModel->setGroupBy(array('id'));
        $this->productModel->setLimit($params['length'], $params['start']);
        $sets = $this->productModel->queryRun();
        $plus = rcHelper::getSvgTemplate('plus');
        $trash = rcHelper::getSvgTemplate('trash');
        foreach ($sets as $set) {
            if ($in) {
                $buttons = $trash;
            } else {
                $buttons = $plus;
            }
            $buttons = '<i class="rc-block rc-icon20 rc-float-right rc-fill-dark rc-fill-hover rc-pointer">'.$buttons.'</i>';
            array_push($result['data'], array(
                htmlspecialchars($set['name'], ENT_QUOTES),
                $buttons,
            ));
        }
        return $result;
    }

    /**
     * @return array
     */
    public function mainTab()
    {
        return array('set' => $this->data);
    }

    /**
     * @return array
     */
    public function productsTab()
    {
        return array('id' => $this->id);
    }
}