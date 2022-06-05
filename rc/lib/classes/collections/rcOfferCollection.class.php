<?php

class rcOfferCollection extends rcCollection
{
    /**
     * @var rcOfferModel
     */
    protected $model;
    /**
     * @var rcOfferExceptionsModel
     */
    protected $exceptionsModel;
    /**
     * @var rcOfferConditionItemsModel
     */
    protected $conditionItemsModel;
    /**
     * @var rcOfferContactGroupModel
     */
    protected $contactGroupModel;
    /**
     * @var rcOfferDatesModel
     */
    protected $datesModel;
    /**
     * @var rcOfferProfitItemsModel
     */
    protected $profitItemsModel;
    /**
     * @var rcOfferWeekdaysModel
     */
    protected $weekdaysModel;
    /**
     * @var rcShopOffersModel
     */
    protected $shopOffersModel;

    /**
     * rcOffer constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->exceptionsModel = new rcOfferExceptionsModel();
        $this->conditionItemsModel = new rcOfferConditionItemsModel();
        $this->contactGroupModel = new rcOfferContactGroupModel();
        $this->datesModel = new rcOfferDatesModel();
        $this->profitItemsModel = new rcOfferProfitItemsModel();
        $this->weekdaysModel = new rcOfferWeekdaysModel();
        $this->shopOffersModel = new rcShopOffersModel();
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function shopListSecond($params, $where, $logic)
    {
        $shopOffersModel = new rcShopOffersModel();
        $this->model->setSelect(array('name' => null, 'IF(shop_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $shopOffersModel->getTableName(), 'on' => array('id' => 'offer_id', $params['shop_id'] => 'shop_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $params
     * @param array $where
     * @param array|string $logic
     */
    protected function offerListSecond($params, $where, $logic)
    {
        $exceptionOffersModel = new rcOfferExceptionsModel();
        $this->model->setSelect(array('name' => null, 'IF(offer_id IS NULL, 0, 1)' => 'checked', 'id' => null));
        $this->model->setJoin(array(
            array('type' => 'LEFT', 'right' => $exceptionOffersModel->getTableName(), 'on' => array('id' => 'offer_exception_id', $params['offer_id'] => 'offer_id'))
        ));
        $this->model->setWhere($where, $logic);
    }

    /**
     * @param array $fields
     */
    protected function offerFieldsList(&$fields)
    {
        $fields['checked'] = array('viewed' => true);
    }

    /**
     * @param array $fields
     */
    protected function shopFieldsList(&$fields)
    {
        $fields['checked'] = array('viewed' => true);
    }

    /**
     * @param array $result
     * @param array $params
     * @throws waException
     */
    protected function getPrepare(&$result, $params)
    {
        $config = rcHelper::getConfigOption(array('offer_profit_type', 'offer_time', 'offer_profit',
            'offer_condition_type', 'offer_condition'));
        $this->shopOffersModel->setFetch('all', 'offer_id', 1);
        $this->shopOffersModel->setSelect(array('offer_id' => array('id', 'offer_id')));
        if (!empty($params['shop_id'])) {
            $this->shopOffersModel->setWhere(array(
                'shop_id' => array('simile' => '=', 'value' => $params['shop_id'])
            ));
        }
        $on = $this->shopOffersModel->queryRun();
        $data = array(
            'profitItems' => array(),
            'exceptions' => array(),
            'conditionItems' => array(),
            'contactGroup' => array(),
            'weekdays' => array(),
            'dates' => array(),
        );
        foreach ($data as $model => $d) {
            $data[$model] = $this->getSubData($model);
        }
        foreach ($result as $key => $offer) {
            if (!in_array($offer['id'], $on)) {
                unset($result[$key]);
                continue;
            }
            if (empty($config['offer_time'][$offer['timing_type']])
                || $config['offer_time'][$offer['timing_type']]['code'] == 'never'
                || empty($config['offer_profit'][$offer['profit_type']])
                || empty($config['offer_condition'][$offer['condition_type']])) {
                unset($result[$key]);
            } else {
                switch ($config['offer_time'][$offer['timing_type']]['code']) {
                    case 'weekdays':
                        if (empty($data['weekdays'][$offer['id']])) {
                            unset($result[$key]);
                            continue 2;
                        } else {
                            $result[$key]['weekdays'] = $data['weekdays'][$offer['id']];
                        }
                        break;
                    case 'dates':
                        if (empty($data['dates'][$offer['id']])) {
                            unset($result[$key]);
                            continue 2;
                        } else {
                            $result[$key]['dates'] = $data['dates'][$offer['id']];
                        }
                        break;
                }
                switch ($config['offer_condition'][$offer['condition_type']]['code']) {
                    case 'products':
                        if (empty($data['conditionItems'][$offer['id']])) {
                            unset($result[$key]);
                            continue 2;
                        } else {
                            $result[$key]['condition_items'] = $data['conditionItems'][$offer['id']];
                        }
                        break;
                    case 'category':
                        if (empty($data['contactGroup'][$offer['id']])) {
                            unset($result[$key]);
                            continue 2;
                        } else {
                            $result[$key]['contact_groups'] = $data['contactGroup'][$offer['id']];
                        }
                        break;
                }
                if ($config['offer_profit'][$offer['profit_type']]['code'] == 'certain') {
                    if (empty($data['profitItems'][$offer['id']])) {
                        unset($result[$key]);
                        continue;
                    } else {
                        $result[$key]['profit_items'] = $data['profitItems'][$offer['id']];
                    }
                }
                if (isset($data['exceptions'][$offer['id']])) {
                    $result[$key]['exceptions'] = $data['exceptions'][$offer['id']];
                }
            }
        }
    }

    /**
     * @param $type
     * @return array
     * @throws waException
     */
    private function getSubData($type)
    {
        $result = array();
        $type = $type.'Model';
        if (!empty($this->$type) && $this->$type instanceof rcModel) {
            $this->$type->setFetch('all', 'offer_id', 2);
            if ($type == 'weekdays') {
                $statuses = rcHelper::getConfigOption('status', 'code');
                $this->$type->setWhere(array('status' => array('simile' => '=', 'value' => $statuses['active']['id'])));
            }
            $result = $this->$type->queryRun();
        }
        return $result;
    }
}