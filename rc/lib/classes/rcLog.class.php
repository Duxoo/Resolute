<?php

class rcLog
{
    /**
     * @var int id from logModel
     */
    protected $id;
    /**
     * @var int entity type id of the log
     */
    protected $data;
    /**
     * @var rcLogModel main log model
     */
    protected $model, $logModel;

    /**
     * rcLog constructor.
     * @param mixed|int|string $type_id class name or type id
     * @param null $id
     * @throws waException
     */
    public function __construct($type_id = null, $id = null)
    {
        $this->id = intval($id);
        $this->model = new rcLogModel();
        $types = rcHelper::getLogTypes(is_numeric($type_id));
        if (isset($types[$type_id])) {
            $this->data = $types[$type_id];
            $model_name = 'rcLog'.ucfirst($this->data['type']).'Model';
            if (class_exists($model_name)) {
                $this->logModel = new $model_name();
            }
        }
    }

    /**
     * @return mixed|null
     * @throws waException
     */
    public function checkOn()
    {
        $on = new rcSettings('log_'.$this->data['type']);
        return $on->getValue();
    }

    /**
     * @param array $data
     * @throws waException
     */
    public function log($data = array())
    {
        if ($this->checkOn() && isset($this->logModel) && $this->logModel instanceof rcLogModel) {
            $data['contact_id'] = wa()->getUser()->getId();
            $data['type_id'] = $this->data['id'];
            $data['date_time'] = date('Y-m-d H:i:s');
            $this->logModel->log($data);
            $this->model->log($data);
        }
    }

    public function getHistory($params)
    {
        $result = array('data' => array(), 'recordsFiltered' => 0, 'recordsTotal' => 0);
        $orders = array(0 => 'name', 1 => 'code',);
        $this->model->setFetch('field');
        $this->model->setSelect(array('COUNT(*)' => null));
        $where = array();
        if (isset($this->data['id'])) {
            $where['type_id'] = array('simile' => '=', 'value' => $this->data['id']);
        }
        $contactModel = new waContactModel();
        $this->model->setJoin(array(
            array('right' => $contactModel->getTableName(), 'on' => array('contact_id' => 'id'))
        ));
        $this->model->setWhere($where);
        $result['recordsTotal'] = $this->model->queryRun(false);
        if (empty($params['search'])) {
            $result['recordsFiltered'] = $result['recordsTotal'];
        } else {
            $where['description'] = array('simile' => 'LIKE', 'value' => '%'.$params['search'].'%');
            $this->model->setWhere($where);
            $result['recordsFiltered'] = $this->model->queryRun(false);
        }
        $this->model->setFetch('all');
        $this->model->setSelect(array($this->model->getTableName().'.*' => null, 'name' => null));
        $this->model->setLimit($params['length'], $params['start']);
        $order = array('date_time' => 'DESC');
        if (isset($orders) && isset($orders[$params['column']])) {
            $order[$params['column']] = $params['direction'];
        }
        $this->model->setOrderBy($order);
        $result['base_data'] = $this->model->queryRun();
        foreach ($result['base_data'] as $row) {
            array_push($result['data'], array(
                '#'.intval($row['id']),
                htmlspecialchars($row['name'], ENT_QUOTES),
                htmlspecialchars($row['description'], ENT_QUOTES),
                htmlspecialchars($row['date_time'], ENT_QUOTES),
            ));
        }
        return $result;
    }
}