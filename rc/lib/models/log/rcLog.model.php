<?php

class rcLogModel extends rcModel
{
    protected $table = 'rc_log';

    protected $template;

    /**
     * @param array $data
     * @throws waException
     */
    public function log(&$data = array())
    {
        if (!empty($this->template) || !empty($data['description'])) {
            $this->setData($data);
            $this->setTemplate($data);
        }
        if (!empty($data)) {
            $data['entity_id'] = $this->insert($data);
        }
    }

    /**
     * @param array $data
     */
    protected function setTemplate(&$data)
    {
        if (empty($data['description'])) {
            $data['description'] = $this->template;
        }
        foreach ($data as $key => $value) {
            $data['description'] = str_replace('%'.$key.'%', $value, $data['description']);
        }
    }

    /**
     * @param $data
     * @throws waException
     */
    protected function setData(&$data) {
        $contact = new rcContact(wa()->getUser()->getId());
        $action_type = rcHelper::getActionType($data['action']);
        $data['date_time'] = date('Y-m-d H:i:s');
        $data['t_user'] = $contact->getType()['name'];
        $data['t_name'] = $contact->getName();
        $data['action_id'] = $action_type['id'];
        $data['t_action'] = $action_type['name'];
        if (isset($data['id'])) {
            unset($data['id']);
        }
    }
}