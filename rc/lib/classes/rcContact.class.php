<?php

class rcContact extends waContact
{
    protected $contact_type;

    /**
     * rcContact constructor.
     * @param null $id
     * @param array $options
     * @throws waException
     */
    public function __construct($id = null, $options = array())
    {
        parent::__construct($id, $options);
        if (isset($id)) {
            $this->setType($id);
        }
    }

    public function getType()
    {
        $types = array(
            'admin' => 'Администратор',
            'franchise' => 'Франчайзи',
            'barista' => 'Бариста',
        );
        return $types[$this->contact_type];
    }

    protected function setType($id)
    {
        $types = array(
            0 => 'admin',
            1 => 'franchise',
            2 => 'barista',
        );
        $this->contact_type = $types[0]; //TODO логика получения типа пользователя
    }
}