<?php

class rcServerApi {

    protected $APIKey;

    public function __construct() {
        //$this->APIKey = "d67b7e3fef53b0914067f8d921fb86f0ed39b8a5f8e0d56f767e90c242b6f229";
        $this->APIKey = $this->setAPIToken();
    }

    public function executeCurl($url, $params = array()) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = array(
            "sessionToken" => $this->APIKey,
        );
        if ($params) {
            $data = array_merge($data, $params);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $resp = json_decode(curl_exec($curl));
        curl_close($curl);
        return $resp;
    }

    //todo save api key to memcached
    public function setAPIToken() {
        $url = "https://ruvds.com/api/logon/";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = array(
        // todo вынести в настройки
          "key" => "secret key",
          "username" => "username",
          "password" => "password",
          "endless" => 1 // todo remove endless
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        $resp = json_decode($result);

        curl_close($curl);
        return (string)$resp->sessionToken;
    }

    public function getServerInfo($id) {
        $data = $this->executeCurl("https://ruvds.com/api/servers/", array("id" => $id));
        return $data->items;
    }

    public function getDataCenters() {
        $data = $this->executeCurl("https://ruvds.com/api/datacenter/");
        print_r($data);
    }

    public function getTariffs() {
        $data = $this->executeCurl("https://ruvds.com/api/tariff/");
        print_r($data);
    }

    public function partUpload(){
        $server_info = $this->getServerInfo(511795);
        $domain = "domain";
        // todo 4 параметр это домен
        $res = exec($_SERVER['DOCUMENT_ROOT']."/httpdocs/server.py '{$server_info[0]->ip->assigned[0]}' '{$server_info[0]->defaultAdminPassword}' '511795' '{$domain}' > /dev/null &");
        return $res;
    }

    public function buyServer() {
        $params = array(
            "datacenter" => 1,
            "os" => 17, // не менять
            "tariff" => 14,
            "cpu" => 1,
            "ram" => 0.5,
            "vram" => 0,
            "drivesCount" => 1,
            "drive0Tariff" => 1,
            "drive0Capacity" => 10,
            "drive0System" => true,
            "ip" => 1,
            "paymentPeriod" => 2, // 2 - 1 месяц; 3 - 3 месяца; 4 - 6 месяцев; 5 - 1 год
            "promocode" => "PROMO-API"
        );
        $data = $this->executeCurl("https://ruvds.com/api/server/create/", $params);
        //$data->id = id созданного сервера, $data->cost = стоимость
        // чтобы отследить, что сервер настроился в питоне раз в 5-10 сек отправлять запрос на получение всей информации
        // о сервере по  его id, как только defaultAdminPassword будет не null - сервак готов, там есть поля createProgress (прогресс в процентах)
        $server_info = $this->getServerInfo($data->id);
        $domain = "asfaca.xyz";
        // todo 4 параметр это домен
        exec($_SERVER['DOCUMENT_ROOT']."/server.py '{$server_info[0]->ip->assigned[0]}' '{$server_info[0]->defaultAdminPassword}' '{$data->id}' '{$domain}' > /dev/null &");
        return $data;
    }
}