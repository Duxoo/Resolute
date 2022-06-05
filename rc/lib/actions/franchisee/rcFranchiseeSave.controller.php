<?php

class rcFranchiseeSaveController extends rcJsonController
{
    /**
     * @throws waException
     * @throws waDbException
     */
    public function execute()
    {
        $entityData = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        if ($entityData) {
            $entity = new rcFranchisee(waRequest::post('id', NULL, waRequest::TYPE_INT));
            $this->response = $entity->save($entityData);
        } else {
            $this->response = array('error' => 1, 'message' => 'Нет данных!');
        }
    }
}