<?php

class rcSettings
{
    protected $code;
    protected $settings;
    /**
     * @var rcSettingsModel
     */
    protected $model;

    /**
     * rcSettings constructor.
     * @param null $id
     * @throws waException
     */
    public function __construct($id = null)
    {
        $this->model = new rcSettingsModel();
        if (isset($id)) {
            if (is_numeric($id)) {
                $this->settings = $this->model->getById($id);
            } else {
                $this->code = $id;
                $this->settings = $this->model->getByField('code', $id);
            }
        }
    }

    /**
     * @return mixed|null
     * @throws waException
     */
    public function getValue()
    {
        $result = null;
        if (isset($this->settings)) {
            $result = $this->settings['value'];
        } else {
            $base = $this->getBaseSettings();
            if (isset($this->code) && isset($base[$this->code])) {
                $result = $base[$this->code]['default'];
            }
        }
        return $result;
    }

    /**
     * @param $settings
     * @param string|null $type
     * @return array
     */
    public function save($settings, $type = null)
    {
        try {
            $old = $this->model->getAll('code', 1);
            $base = $this->getBaseSettings($type);
            $log = new rcLog('settings');
            $result = array('error' => true, 'message' => 'Настройки уже соответствуют введённым');
            foreach ($settings as $code => $val) {
                if (isset($base[$code])) {
                    if ($base[$code]['default'] != $val && (empty($old[$code]) || $old[$code]['value'] != $val)) {
                        if (empty($val)) {
                            if (isset($old[$code])) {
                                $this->model->deleteByField('code', $code);
                                $log->log(array(
                                    'code' => $code,
                                    'action' => 'delete',
                                    'value_before' => $old[$code]['value'],
                                    't_settings_name' => $base[$code]['name']
                                ));
                                $result['error'] = false;
                            }
                        } else {
                            if (isset($old[$code])) {
                                $this->model->updateByField('code', $code, array('value' => $val));
                            } else {
                                $this->model->insert(array('code' => $code, 'value' => $val));
                            }
                            $action = 'edit';
                            if ($base[$code]['field']['tag'] == 'input' && $base[$code]['field']['type'] == 'checkbox') {
                                $action = 'on';
                            }
                            $log->log(array(
                                'code' => $code,
                                'action' => $action,
                                'value_before' => empty($old[$code]) ? $base[$code]['default'] : $old[$code]['value'],
                                'value_after' => $val,
                                't_settings_name' => $base[$code]['name']
                            ));
                            $result['error'] = false;
                        }
                    }
                    unset($base[$code]);
                }
            }
            foreach ($base as $code => $setting) {
                if ($setting['field']['tag'] == 'input' && $setting['field']['type'] == 'checkbox' && isset($old[$code])) {
                    $this->model->deleteByField('code', $code);
                    $log->log(array(
                        'code' => $code,
                        'action' => 'off',
                        'value_before' => $old[$code]['value'],
                        't_settings_name' => $setting['name']
                    ));
                    $result['error'] = false;
                }
            }
            if (!$result['error']) {
                $result['message'] = 'Настройки успешно сохранены';
            }
        } catch (waException $wa) {
            $result = array('error' => true, 'message' => $wa->getMessage());
        }
        return $result;
    }

    /**
     * @param string|null $type
     * @return array
     * @throws waException
     */
    public function getBaseSettings($type = null)
    {
        $result = array();
        $base = wa()->getConfig()->getOption('settings');
        foreach ($base as $group) {
            if (empty($type) || $type == $group['type']) {
                foreach ($group['fields'] as $code => $data) {
                    $result[$code] = $data;
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     * @throws waException
     */
    public function getSettings()
    {
        $this->model->setFetch('all', 'code', 1);
        $this->model->setSelect(array('code' => null, 'value' => null));
        $settings = (array)$this->model->queryRun();
        $base = $this->getBaseSettings();
        foreach ($base as $code => $value) {
            if (!isset($settings[$code])) {
                $settings[$code] = $value['default'];
            }
        }
        return $settings;
    }
}