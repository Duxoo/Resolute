<?php

class rcWorker extends rcContact
{
    /**
     * @var rcWorkerModel
     */
    protected $model;

    /**
     * @return array|null
     */
    public function getData()
    {
        $result = null;
        if(isset($this->id)) {
            $result['contact'] = $this->contact_model->getById($this->id);
            $result['worker'] = $this->data;
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
            $result = $this->getData();
        }
        $result['worker_fields'] = rcHelper::getFields($this->class_name);
        $result['contact_fields'] = rcHelper::getFields('contact');
        $result['config']['status'] = rcHelper::getConfigOption('status');
        return $result;
    }


    /**
     * @param array $data
     * @return array
     */
    public function save(array $data)
    {
        try {
            $result = rcHelper::validate($data['contact'], 'contact');
            if(!$result['error'])
            {
                $result = rcHelper::validate($data['worker'], $this->class_name);
            }
            if (!$result['error']) {
                if (empty($this->id)) {
                    $this->id = $this->insertData($data);
                    $this->log->log(array(
                        'action' => 'create',
                        'worker_id' => $this->id,
                        'data_after' => serialize($this->getData()),
                    ));
                    $result = array('error' => false, 'id' => $this->id, 'reload'=>true, 'message' => 'Работник '.$data['contact']['firstname'].' успешно добавлен');
                } else {
                    $worker = $this->getData();
                    if (rcHelper::arrayChangeCheck($worker, $data)) {
                        $this->contact->save($data['contact']);
                        $this->model->updateById($this->id, $data['worker']);
                        $this->log->log(array(
                            'action' => 'edit',
                            'worker_id' => $this->id,
                            'data_before' => serialize($worker),
                            'data_after' => serialize($this->getData()),
                        ));
                        $result = array('error' => false, 'id' => $this->id, 'message' => 'Работник '.$data['contact']['firstname'].' успешно обновлен');
                    } else {
                        $result = array('error' => true, 'message' => 'Данные не были изменены');
                    }
                }
            }
        } catch (waException $wa) {
            $result = array('error' => true, 'message' => $wa->getMessage());
        }
        return $result;
    }

    /**
     * @param array $data
     * @return int
     * @throws waException
     */
    protected function insertData(array $data)
    {
        $this->contact->save($data['contact']);
        $id = $this->contact->getId();
        $data['worker']['contact_id'] = $id;
        $this->model->insert($data['worker']);
        return $id;
    }
}
