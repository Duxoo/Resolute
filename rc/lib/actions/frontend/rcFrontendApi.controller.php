<?php

class rcFrontendApiController extends waJsonController
{
    /**
     * @var string
     */
    protected $type = '';

    /**
     * @throws waException
     */
    public function execute()
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']).(empty($_SERVER['HTTP_METHOD']) ? '' : ucfirst($_SERVER['HTTP_METHOD']));
        parse_str(file_get_contents("php://input"),$data);
        $data['id'] = waRequest::param('id', null, 'int');
        $data['shop_id'] = waRequest::param('shop_id', null, 'int');
        $api = new rcApi($this->type, $data['shop_id'], $_SERVER['HTTP_KEY']);
        if (method_exists($api, $method)) {
            $this->response = $api->$method($data);
        } else {
            $this->response = array('error' => true, 'message' => 'Метод отсутствует');
        }
    }

    public function display()
    {
        if (isset($this->response['key'])) {
            unset($this->response['key']);
        }
        $this->getResponse()->sendHeaders();
        if ($this->errors) {
            echo json_encode(array('status' => 'fail', 'errors' => $this->errors));
        } else {
            echo json_encode($this->response);
        }
    }
}