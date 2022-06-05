<?php

class rcApi
{
    /**
     * @var rcObject|rcCollection
     */
    protected $api;
    /**
     * @var array|false[]
     */
    protected $result = array('error' => false);

    /**
     * rcShopApi constructor.
     * @param string $type
     * @param int $shop_id
     * @param string $key
     * @throws waException
     */
    public function __construct($type, $shop_id = null, $key = null)
    {
        $this->setApi($type);
        $this->setKey($shop_id, $key);
    }

    /**
     * @param string $type
     */
    protected function setApi($type)
    {
        $class = 'rc'.ucfirst($type);
        if (class_exists($class)) {
            $class = new $class();
            if ($class instanceof rcCollection || $class instanceof rcObject) {
                $this->api = $class;
            }
        }
    }

    /**
     * @param int $shop_id
     * @param string $key
     * @throws waException
     */
    protected function setKey($shop_id, $key)
    {
        if (empty($this->api)) {
            $this->result = array('error' => true, 'message' => 'Ошибка идентификации класса');
        } else {
            $shop = new rcShop($shop_id);
            if ($shop = $shop->getData()) {
                if ($shop['status'] != 1) {
                    $this->result = array('error' => true, 'message' => 'Точка неактивна');
                } else {
                    $check_key = md5($shop_id.$shop['salt'].date('z'));
                    if ($key === $check_key) {
                        $this->result = array(
                            'error' => false,
                            'key' => $check_key,
                            'response_key' => md5($shop['salt'].$shop_id.date('z')),
                        );
                    } else {
                        $this->result = array('error' => true, 'message' => 'Ошибка формирования ключа');
                    }
                }
            } else {
                $this->result = array('error' => true, 'message' => 'Точка не найдена');
            }
        }
    }

    /**
     * @param $params
     * @return array|false[]
     */
    public function get($params)
    {
        return $this->method($params, 'get');
    }

    /**
     * @param $params
     * @return array|false[]
     */
    public function post($params)
    {
        return $this->method($params, 'post');
    }

    /**
     * @param $params
     * @return array|false[]
     */
    public function delete($params)
    {
        return $this->method($params, 'delete');
    }

    /**
     * @param $params
     * @return array|false[]
     */
    public function put($params)
    {
        return $this->method($params, 'put');
    }

    /**
     * @param $params
     * @param $method
     * @return array|false[]
     */
    protected function method($params, $method)
    {
        if (!$this->result['error']) {
            if ($this->api instanceof rcObject) {
                if (empty($params['id'])) {
                    $this->result = array('error' => true, 'message' => 'Элемент не найден');
                } else {
                    $this->api->setId($params['id']);
                }
            } else {
                if (method_exists($this->api, $method)) {
                    $this->result['data'] = $this->api->$method($params);
                } else {
                    $this->result = array('error' => true, 'message' => 'Метод не найден');
                }
            }
        }
        return $this->result;
    }
}