<?php

class rcCustomer extends rcObject
{
    /**
     * @var rcCustomerModel
     */
    protected $model;

    /**
     * @return array|null
     */
    public function getData()
    {
        $result = null;
        if(isset($this->id)) {
            $result = $this->data;
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
            $result['customer'] = $this->getData();
        }
        $result['fields'] = rcHelper::getFields($this->class_name);
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
            
            $result = rcHelper::validate($data, $this->class_name);
            if (!$result['error']) {
                if (empty($this->id)) {
                    $this->id = $this->model->insert($data);
                    $this->log->log(array(
                        'action' => 'create',
                        'customer_id' => $this->id,
                        'data_after' => serialize($this->getData()),
                    ));
                    $result = array('error' => false, 'id' => $this->id, 'reload'=>true, 'message' => 'Клиент '.$data['name'].' успешно добавлен');
                } else {
                    $customer = $this->getData();
                    if (rcHelper::arrayChangeCheck($customer, $data)) {
                        $this->model->updateById($this->id, $data);
                        $this->log->log(array(
                            'action' => 'edit',
                            'customer_id' => $this->id,
                            'data_before' => serialize($customer),
                            'data_after' => serialize($this->getData()),
                        ));
                        $result = array('error' => false, 'id' => $this->id, 'message' => 'Клиент '.$data['name'].' успешно обновлен');
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
}
