<?php

/**
 * Class rcHelper helper class of the resolute center
 */
class rcHelper
{
    /**
     * @param bool $reverse
     * @return array|mixed|null
     * @throws waException
     */
    static public function getLogTypes($reverse = false)
    {
        $types = wa()->getConfig()->getOption('log_types');
        if ($reverse) {
            foreach ($types as $id => $type) {
                $types[$type['id']] = $type;
                unset($types[$id]);
            }
        }
        return $types;
    }

    /**
     * @param $type
     * @return false|mixed
     * @throws waException
     */
    static public function getLogType($type)
    {
        $types = self::getLogTypes(is_numeric($type));
        $result = false;
        if (isset($types[$type])) {
            $result = $types[$type];
        }
        return $result;
    }
    /**
     * @throws waException
     */
    static public function getBackendMenu()
    {
        $result = self::getConfigOption('menu');
        $stop = false;
        foreach ($result as $id => $menu) {
            foreach ($menu['children'] as $child_id => $child) {
                if ($child['module'] == waRequest::get('module')) {
                    $result[$id]['active'] = true;
                    $result[$id]['children'][$child_id]['active'] = true;
                    $stop = true;
                    break;
                }
            }
            if ($stop) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param $name
     * @return mixed|string
     */
    static public function getMethodByName($name)
    {
        $result = '';
        $name = explode('_', $name);
        foreach ($name as $v) {
            if (empty($result)) {
                $result = $v;
            } else {
                $result .= ucfirst($v);
            }
        }
        return $result;
    }

    /**
     * @param $action
     * @param string $field
     * @return bool|mixed
     * @throws waException
     */
    static public function getActionType($action, $field = 'all')
    {
        $result = false;
        $actions = wa()->getConfig()->getOption('action_types');
        if (isset($actions[$action])) {
            $result = isset($actions[$action][$field]) ? $actions[$action][$field] : $actions[$action];
        } else {
            foreach ($actions as $id => $value) {
                if ($value['id'] == $action) {
                    $result = isset($value[$field]) ? $value[$field] : $value;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param $class
     * @return string
     */
    static public function getClassName($class)
    {
        return lcfirst(substr(get_class($class), 2));
    }

    /**
     * @param string|array|null $name
     * @param string|null $field
     * @return array|null
     * @throws waException
     */
    static public function getConfigOption($name = null, $field = null)
    {
        $result = null;
        if (empty($name)) {
            $result = wa()->getConfig()->getOption();
        } else {
            if (is_array($name)) {
                $config = wa()->getConfig()->getOption();
                foreach ($name as $code) {
                    if (isset($config[$code])) {
                        $result[$code] = $config[$code];
                        self::keyReplace($result[$code], $field);
                    }
                }
            } else {
                $result = wa()->getConfig()->getOption($name);
                self::keyReplace($result, $field);
            }
        }
        return $result;
    }

    /**
     * @param $result
     * @param $field
     */
    static private function keyReplace(&$result, $field)
    {
        if (isset($field)) {
            $temp = array();
            foreach ($result as $r) {
                if (isset($r[$field])) {
                    $temp[$r[$field]] = $r;
                } else {
                    break;
                }
            }
            $result = $temp;
        }
    }

    /**
     * @param $old
     * @param $new
     * @return bool
     */
    static public function arrayChangeCheck($old, $new)
    {
        $changed = false;
        foreach ($new as $field => $value) {
            if (!isset($old[$field]) || $old[$field] != $value) {
                $changed = true;
                break;
            }
        }
        return $changed;
    }

    /**
     * @param string $result
     * @param $check_fields
     * @param $data
     * @param $rows
     * @param string $row_start
     */
    static public function repeatCheck(&$result, $check_fields, $data, $rows, $row_start = '')
    {
        foreach ($rows as $row) {
            $row_string = '';
            foreach ($check_fields as $field => $field_data) {
                if (isset($field_data['unique']) && $row[$field] == $data[$field]) {
                    if (empty($row_string)) {
                        $row_string .= $row_start;
                        if (isset($row['name'])) {
                            $row_string .= '"'.htmlspecialchars($row['name'], ENT_QUOTES).'" ';
                        }
                    } else {
                        $row_string .= ', ';
                    }
                    $row_string .= $field_data['unique'];
                }
            }
            $result .= $row_string.'.</br>';
        }
    }

    /**
     * @param $rows
     * @param $name
     * @param $class
     * @param null $selected
     * @return string
     */
    static public function buildSelect($rows, $name, $class, $selected = null)
    {
        $result = '<select class="'.htmlspecialchars($class, ENT_QUOTES).'" name="'.htmlspecialchars($name, ENT_QUOTES).'"><option value="-1">---</option>';
        foreach ($rows as $key => $value) {
            $s = '';
            if (isset($key) && $key == $selected) {
                $s = ' selected';
            }
            if (isset($value['cases'][0])) {
                $value['name'] = $value['cases'][0];
            }
            $result .= '<option value="'.htmlspecialchars($key, ENT_QUOTES).'" '.$s.'>'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $result.'</select>';
    }

    /**
     * @param null $type
     * @return mixed|null
     * @throws waException
     */
    static public function getFields($type = null)
    {
        $fields = self::getConfigOption('fields');
        if (isset($type)) {
            if (isset($fields[$type])) {
                $fields = $fields[$type];
            } else {
                $fields = false;
            }
        } else {
            $fields = false;
        }
        return $fields;
    }

    /**
     * @param array $data
     * @param string $type
     * @return array
     * @throws waException
     */
    static public function validate(array &$data, $type)
    {
        $result = array('error' => false);
        $fields = self::getFields($type);
        $errors = array();
        if (empty($data)) {
            $result = array('error' => true, 'message' => 'Ошибка получения данных');
        } else {
            if ($fields) {
                foreach ($fields as $code => $field) {
                    if (!empty($field)) {
                        if (empty($data[$code]) && strlen($data[$code]) === 0 && empty($field['nullable'])) {
                            $errors[] = 'поле "'.$field['name'].'" должно быть заполнено';
                        } else {
                            self::fieldValidation($field, $errors, $data[$code]);
                        }
                    }
                }
            }
        }
        if (!empty($errors)) {
            $result = array('error' => true, 'message' => self::messageMerge($errors));
        }
        return $result;
    }

    /**
     * @param $field
     * @param $value
     * @param $type
     * @return array|false[]
     * @throws waException
     */
    static public function validateField($field, $value, $type)
    {
        $result = array('error' => false);
        $fields = self::getFields($type);
        $errors = array();
        if (isset($fields[$field])) {
            $field = $fields[$field];
            if (empty($value) && strlen($value) === 0 && empty($field['nullable'])) {
                $errors[] = 'поле "'.$field['name'].'" должно быть заполнено';
            } else {
                self::fieldValidation($field, $errors, $value);
            }
        }
        if (!empty($errors)) {
            $result = array('error' => true, 'message' => self::messageMerge($errors));
        }
        return $result;
    }

    /**
     * @param $field
     * @param $errors
     * @param $value
     * @throws waException
     */
    static public function fieldValidation($field, &$errors, &$value)
    {
        if (isset($value)) {
            if (isset($field['max']) && $value > $field['max']) {
                $errors[] = 'значения поля "'.$field['name'].'" не должно быть больше '.$field['max'];
            }
            if (isset($field['min']) && $value < $field['min']) {
                $errors[] = 'значения поля "'.$field['name'].'" не должно быть меньше '.$field['min'];
            }
            switch ($field['type']) {
                case 'string':
                case 'text':
                    if (isset($field['regexp'])) {
                        if (preg_match($field['regexp'], $value)) {
                            $errors[] = 'в поле "'.$field['name'].'" присутствуют недопустимые символы';
                        }
                    }
                    if (isset($field['max_length']) && mb_strlen($value) > $field['max_length']) {
                        $errors[] = 'поле "'.$field['name'].'" не должно быть длиннее '.$field['max_length'];
                    }
                    if (isset($field['min_length']) && mb_strlen($value) < $field['min_length']) {
                        $errors[] = 'поле "'.$field['name'].'" не должно быть короче '.$field['min_length'];
                    }
                    break;
                case 'decimal':
                    if (!is_numeric($value)) {
                        $errors[] = 'поле "'.$field['name'].'" должно быть числом';
                    }
                    break;
                case 'int':
                    if (!(is_numeric($value)&&(int)$value==$value)) {
                        $errors[] = 'поле "'.$field['name'].'" должно быть целочисленным';
                    }
                    break;
                case 'config':
                    if (empty($field['code'])) {
                        $errors[] = 'ошибка получения конфигурационных данных для поля "'.$field['name'].'"';
                    } else {
                        if (isset($field['config'])) {
                            $config = self::getConfigOption($field['config']);
                        } else {
                            $config = self::getConfigOption($field['code']);
                        }
                        if (empty($config[$value])) {
                            $check = true;
                            foreach ($config as $config_val) {
                                if (isset($config_val[$value])) {
                                    $check = false;
                                }
                            }
                            if ($check) {
                                $errors[] = 'ошибка получения значения конфигурационных данных для поля "'.$field['name'].'"';
                            }
                        }
                    }
                    break;
                case 'checkbox':
                    if (!in_array($value, array(0,1))) {
                        $value = 1;
                    }
                    break;
                case 'date':
                    //TODO валидация даты
                    break;
                case 'time':
                    //TODO валидация времени
                    break;
                default:
                    $errors[] = 'ошибка получения типа данных для поля "'.$field['name'].'"';
            }
        } else {
            if ($field['type'] == 'checkbox') {
                $value = 0;
            }
        }
    }

    /**
     * @return string
     * @throws SmartyException
     * @throws waException
     */
    static public function getCheckBoxTemplate()
    {
        $check = wa()->getView();
        $check->assign('app_path', wa()->getAppPath('', 'rc'));
        return $check->fetch(wa()->getAppPath('/templates/check.html', 'rc'));
    }

    /**
     * @param $svg
     * @return string
     * @throws SmartyException
     * @throws waException
     */
    static public function getSvgTemplate($svg)
    {
        $check = wa()->getView();
        return $check->fetch(wa()->getAppPath('/img/svg/'.$svg.'.svg', 'rc'));
    }

    static public function getName($type, $id)
    {
        $result = array('error' => true,);
        $class = 'rc'.ucfirst($type);
        if (class_exists($class)) {
            $class = new $class($id);
            if (method_exists($class, 'getName')) {
                $result = $class->getName();
            }
        }
        return $result;
    }

    /**
     * @param array $messages
     * @return string
     */
    static public function messageMerge(array $messages)
    {
        $result = '';
        if (is_array($messages)) {
            foreach ($messages as $key => $value) {
                if (!is_string($value)) {
                    unset($messages[$key]);
                }
            }
            $result = '<p>'.trim(implode(', ', $messages)).'.</p>';
        }
        return $result;
    }

    static public function clearBase()
    {
        $tables = array(
            new rcMotivationDatesModel(),
            new rcMotivationItemsModel(),
            new rcMotivationPeriodModel(),
            new rcMotivationWeekdaysModel(),
            new rcMotivationModel(),
            new rcOfferDatesModel(),
            new rcOfferConditionItemsModel(),
            new rcOfferContactGroupModel(),
            new rcOfferProfitItemsModel(),
            new rcOfferWeekdaysModel(),
            new rcOfferModel(),
            new rcProductAdditionsModel(),
            new rcProductIngredientModel(),
            new rcProductSkuModel(),
            new rcProductSetModel(),
            new rcProductModel(),
            new rcIngredientModel(),
            new rcIngredientCategoryModel(),
            new rcCategoryIngredientsModel(),
            new rcScreenElementModel(),
            new rcScreenModel(),
            new rcSetModel(),
            new rcShopModel(),
            new rcShopMotivationsModel(),
            new rcShopProductAdditionsModel(),
            new rcShopOffersModel(),
            new rcSupplierModel(),
            new rcSupplierIngredientsModel(),
            new rcCustomerModel(),
            new rcSettingsModel(),
            new rcWorkerModel(),
            new rcLogModel(),
            new rcLogWorkerModel(),
            new rcLogCustomerModel(),
            new rcLogFranchiseeModel(),
            new rcLogShopModel(),
            new rcLogShopMotivationsModel(),
            new rcLogShopFranchiseesModel(),
            new rcLogIngredientModel(),
            new rcLogMotivationModel(),
            new rcLogMotivationItemsModel(),
            new rcLogMotivationWeekdaysModel(),
            new rcLogMotivationDatesModel(),
            new rcLogMotivationPeriodModel(),
            new rcLogProductModel(),
            new rcLogProductSkuModel(),
            new rcLogProductAdditionsModel(),
            new rcLogProductIngredientsModel(),
            new rcLogScreenModel(),
            new rcLogSetModel(),
            new rcLogSettingsModel(),
            new rcLogOfferModel(),
            new rcLogOfferModel(),
            new rcLogOfferWeekdaysModel(),
            new rcLogOfferDatesModel(),
            new rcLogOfferConditionItemsModel(),
            new rcLogOfferProfitItemsModel(),
            new rcLogOfferContactGroupModel(),
            new rcLogShopOffersModel(),
            new rcLogSupplierModel(),
            new rcLogIngredientCategoryModel(),
            new rcLogCategoryIngredientsModel(),
            new rcLogSupplierIngredientsModel(),
        );
        foreach ($tables as $model)
        {
            $model->clear();
        }
    }

    /**
     * @param $app
     * @throws waException
     */
    static public function dbPhpGenerate($app)
    {
        $model = new rcModel();
        $command = '';
        foreach ($model->getTables($app) as $t) {
            $t = array_values($t)[0];
            if (!empty($t)) {
                $command .= ' '.$t;
            }
        }
        if (!empty($command)) {
            $command = $app.$command;
            if (file_exists(wa()->getAppPath('lib/config/db.php', $app))) {
                $command .= ' -update';
            }
            `php wa.php generateDb $command`;
        }
    }
}