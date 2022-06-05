<?php

class rcBackendAction extends rcViewAction
{
    public function execute()
    {
        $this->setLayout(new rcBackendLayout());
    }

    protected function d()
    {
        $orderModel = new rcOrderModel();
        $orderModel->setFetch('all', 'shop_id', 1);
        $orderModel->setSelect(array(
            'shop_id' => null,// Идентификатор точки
            'COUNT(*)' => 'order_count',// Получаем кол-во заказов за день
            'AVG(price)' => 'avg_check',// Получаем средний чек за день
            'SUM(price)' => 'sales',// Получаем оборот за день
            'SUM(purchase)' => 'cost_price',// Получаем себестоимость заказов за день
            'SUM(discount)' => 'discounts',// Получаем скидку на заказы за день
        ));
        $orderModel->setWhere(array(
            'date_time' => array('simile' => 'BETWEEN', 'value' => array(
                'from' => date('Y-m-d H:i:s', time() - 24 * 3600),
                'to' => date('Y-m-d H:i:s', time()),
            ))
        ));
        $orderModel->setGroupBy(array('shop_id'));
        $order_statistics = $orderModel->queryRun();
        $shopModel = new rcShopModel();
        $shopModel->setFetch('all', 'id', 1);
        $shopModel->setSelect(array(
            'id' => null,
            'rent' => null,
        ));
        $shop_rent = $shopModel->queryRun();
        $workerActivityModel = new rcWorkerActivityModel();
        $workerModel = new rcWorkerModel();
        $workerActivityModel->setFetch('all', 'shop_id', 2);
        $workerActivityModel->setSelect(array(
            'MIN(date_time)' => 'start_working',//начало смены
            'MAX(date_time)' => 'end_working',//конец смены
            'salary' => null,//оклад работника
            'hour_payment' => null,//почасовая оплата работника
            'interest_rate' => null,//доля от продаж
            'shop_id' => null,//идентификатор точки
            'contact_id' => null,//идентификатор точки
        ));
        $workerActivityModel->setJoin(array(
            array('right' => $workerModel->getTableName(), 'on' => array('worker_id' => 'contact_id')),
        ));
        $workerActivityModel->setWhere(array(
            'date_time' => array('simile' => 'BETWEEN', 'value' => array(
                'from' => date('Y-m-d H:i:s', time() - 24 * 3600),
                'to' => date('Y-m-d H:i:s', time()),
            ))
        ));
        $workerActivityModel->setGroupBy(array('shop_id', 'worker_id'));
        $worker_data = $workerActivityModel->queryRun();
        $shopStatisticModel = new rcShopStatisticModel();
        $day_in_month = date('t');
        $insert = array();
        foreach ($shop_rent as $shop_id => $rent) {
            $data = array();
            $data['shop_id'] = $shop_id;
            $data['date'] = date('Y-m-d');
            $data['rent'] = round($rent/$day_in_month, 4);
            if (isset($order_statistics[$shop_id])) {
                $data += $order_statistics[$shop_id];//если есть информация по заказам, добавляем в массив
            }
            if (isset($worker_data[$shop_id])) {
                foreach ($worker_data[$shop_id] as $worker) {
                    if (empty($data['salary'])) {
                        $data['salary'] = $worker['salary'];
                    } else {
                        $data['salary'] += $worker['salary'];
                    }
                    if ($worker['hour_payment'] > 0) {
                        $work_time = round((strtotime($worker['end_working']) - strtotime($worker['start_working']))/3600, 2);//расчёт времени работы работника
                        if (empty($data['hour_payment'])) {
                            $data['hour_payment'] = $worker['hour_payment']*$work_time;
                        } else {
                            $data['hour_payment'] += $worker['hour_payment']*$work_time;
                        }
                    }
                    if ($worker['interest_rate'] > 0 && !empty($data['sale'])) {
                        if (empty($data['hour_payment'])) {
                            $data['interest_rate'] = $worker['interest_rate']*$data['sale']/100;
                        } else {
                            $data['interest_rate'] += $worker['interest_rate']*$data['sale']/100;
                        }
                    }
                }
            }
            if (empty($data['order_count'])) {
                $data['order_count'] = 0;
            }
            if (empty($data['avg_check'])) {
                $data['avg_check'] = 0;
            }
            if (empty($data['sales'])) {
                $data['sales'] = 0;
            }
            if (empty($data['cost_price'])) {
                $data['cost_price'] = 0;
            }
            if (empty($data['discounts'])) {
                $data['discounts'] = 0;
            }
            $data['profit'] = (empty($data['sales']) ? 0 : $data['sales']) - (empty($data['cost_price']) ? 0 : $data['cost_price'])
                - (empty($data['salary']) ? 0 : $data['salary']) - (empty($data['hour_payment']) ? 0 : $data['hour_payment'])
                - (empty($data['interest_rate']) ? 0 : $data['interest_rate']) - $data['rent'];//расчитываем прибыль
            foreach ($data as $k => $d) {
                if (is_null($d)) {
                    $data[$k] = 0;
                }
            }
            $insert[] = $data;
        }
        $shopStatisticModel->multipleInsert($insert);//запись в таблицу, хранящую статистические данные
    }
}