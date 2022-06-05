<?php

class rcContactCollection extends rcCollection
{
    /**
     * @var rcModel
     */
    protected $contact_model;

    /**
     * rcContact constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->contact_model = new rcModel(new waContactModel());
    }

    /**
     * @param array $params
     */
    protected function setListSelect($params)
    {
        parent::setListSelect($params);
        $this->model->setJoin(array(
            array(
                'right' => $this->contact_model->getTableName(),
                'on' => array('contact_id' => 'id')
            )
        ));
    }
}