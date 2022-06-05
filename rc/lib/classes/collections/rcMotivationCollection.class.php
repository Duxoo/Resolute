<?php

class rcMotivationCollection extends rcCollection
{
    /**
     * @var rcMotivationModel
     */
    protected $model;
    /**
     * @var rcMotivationDatesModel
     */
    protected $datesModel;
    /**
     * @var rcMotivationItemsModel
     */
    protected $itemsModel;
    /**
     * @var rcMotivationPeriodModel
     */
    protected $periodModel;
    /**
     * @var rcMotivationWeekdaysModel
     */
    protected $weekdaysModel;

    /**
     * rcMotivation constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->datesModel = new rcMotivationDatesModel();
        $this->itemsModel = new rcMotivationItemsModel();
        $this->periodModel = new rcMotivationPeriodModel();
        $this->weekdaysModel = new rcMotivationWeekdaysModel();
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function shopListSecond($params, $where, $logic)
    {
        $shopMotivationsModel = new rcShopMotivationsModel();
        $this->model->setSelect(array('name' => null, 'IF(shop_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopMotivationsModel->getTableName(), 'on' => array('id' => 'motivation_id', $params['shop_id'] => 'shop_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $fields
     */
    protected function shopFieldsList(&$fields)
    {
        $fields['checked'] = array('viewed' => true);
    }
}