<?php

class rcScreenCollection extends rcCollection
{
    /**
     * @var rcScreenModel
     */
    protected $model;
    /**
     * @var rcScreenElementModel
     */
    protected $elementModel;
    /**
     * @var string
     */
    protected $icon;

    /**
     * rcScreen constructor.
     * @param null $hash
     * @throws waException
     */
    public function __construct($hash = null)
    {
        parent::__construct($hash);
        $this->elementModel = new rcScreenElementModel();
    }

    /**
     * @param array $params
     * @param null $status
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    public function getList($params, $status = null)
    {
        $this->icon = rcHelper::getSvgTemplate('menu');
        return parent::getList($params, $status);
    }

    /**
     * @param $row
     * @param $template
     * @throws SmartyException
     * @throws waException
     */
    protected function setListSort(&$row, $template)
    {
        $row['icon'] = $this->icon;
        $view = wa()->getView();
        $view->assign($row);
        $row['sort'] = $view->fetch('string:'.$template);
    }

    /**
     * @param array $result
     * @param array $params
     */
    protected function getPrepare(&$result, $params)
    {
        $this->elementModel->setFetch('all', 'screen_id', 2);
        $elements = $this->elementModel->queryRun();
        foreach ($result as $key => $screen) {
            if (empty($elements[$screen['id']])) {
                unset($result[$key]);
            } else {
                $result[$key]['elements'] = $elements[$screen['id']];
            }
        }
    }
}