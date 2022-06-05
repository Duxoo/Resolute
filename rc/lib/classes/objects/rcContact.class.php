<?php

class rcContact extends rcObject
{
    protected $contact;
    protected $contact_type;

    /**
     * @var rcModel
     */
    protected $contact_model;

    /**
     * rcContact constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->contact = new waContact($this->id);
        $this->contact_model = new rcModel(new waContactModel());
    }

    public function getType()
    {
        return $this->contact_type;
    }

    /**
     * @param $id
     * @throws waException
     */
    protected function setType($id)
    {
        $types = rcHelper::getConfigOption('contact_types');
        $this->contact_type = $types[$id];
    }

    public function getName()
    {
        return $this->contact->getName();
    }
}