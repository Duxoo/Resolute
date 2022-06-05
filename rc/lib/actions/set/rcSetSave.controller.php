<?php

class rcSetSaveController extends rcJsonController
{
    /**
     * @throws waDbException
     * @throws waException
     */
    public function execute()
    {
        $id = waRequest::post("id", NULL, waRequest::TYPE_INT);
        $data = waRequest::post("data", NULL, waRequest::TYPE_ARRAY);
        if ($data) {
            $set = new rcSet($id);
            $this->response = $set->save($data);
        } else {
            $this->response = array("error" => true, "message" => "Нет данных!");
        }
    }
}