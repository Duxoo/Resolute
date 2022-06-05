<?php

class rcDataTableController extends rcJsonController
{
    protected $data_tables_params = array(
        'direction' => 'ASC',
    );

    /**
     * rcDataTableController constructor.
     * @throws waException
     */
    public function __construct()
    {
        parent::__construct();
        $this->data_tables_params['page'] = waRequest::get('page', 0, waRequest::TYPE_INT);
        $this->data_tables_params['draw'] = waRequest::get('draw', 0, waRequest::TYPE_INT);
        $this->data_tables_params['start'] = waRequest::get('start', 0, waRequest::TYPE_INT);
        $this->data_tables_params['length'] = waRequest::get('length', 10, waRequest::TYPE_INT);
        if ($this->data_tables_params['length'] > 100 || $this->data_tables_params['length'] < 0) {
            $this->data_tables_params['length'] = 100;
        }
        $order = waRequest::get('order', null, waRequest::TYPE_ARRAY);
        if (!empty($order)) {
            $this->data_tables_params['order'] = 1;
            if (isset($order[0]['column'])) {
                $this->data_tables_params['column'] = intval($order[0]['column']);
            }
            if (isset($order[0]['dir'])) {
                $this->data_tables_params['direction'] = strtoupper($order[0]['dir']);
            }
        }
        if (!empty($_GET['search']['value'])) {
            $this->data_tables_params['search'] = $_GET['search']['value'];
        }
    }

    public function display()
    {
        $this->getResponse()->sendHeaders();
        if (!$this->errors) {
            echo json_encode($this->response);
        } else {
            echo json_encode(array('status' => 'fail', 'errors' => $this->errors));
        }
    }
}