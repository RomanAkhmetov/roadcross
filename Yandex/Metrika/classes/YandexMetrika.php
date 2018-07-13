<?php
require_once 'DB.php';

class YandexMetrika {

    private $_TOKEN = null;
    private $counter_id = null;
    private $api_data_url = 'https://api-metrika.yandex.ru/stat/v1/data.json?';
    private static $this_source_url = 'https://metrika.yandex.ru';
    private $data = null;
    private $db = null;

    public function __construct() {
        $config=require 'settings/config.php';
        $this->db = new DB();
        $this->_TOKEN=$config['OAUTH_TOKEN'];
        $this->counter_id=$counter_id['COUNTER_ID'];
    }

    /**
     * Основной метод для записи статистики
     */
    public function start() {

        $this->saveAll($this->getStatYesterday());  
        if ($this->saveAll($this->getStat90days())) {
            echo 'Success';
            die();
        }
    }

    /**
     * Возвращает статистику за 90 дней от вчерашнего дня включительно
     */
    private function getStat90days() {
        $params_string = "id=45275493&"
                . "accuracy=full&"
                . "limit=99999&"
                . "sort=-ym:pv:date&"
                . "dimensions=ym:pv:date,ym:pv:URL&"
                . "metrics=ym:pv:pageviews&"
                . "date1=90daysAgo&"
                . "date2=yesterday&"
                . "pretty=1&"
                . "oauth_token=$this->_TOKEN";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->api_data_url . $params_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response_json = curl_exec($curl);

        curl_close($curl);
        $this->data = json_decode($response_json, true); //Декодирует JSON в ассоциативный массив
        return $this->prepare($this->data);
    }

    /**
     * Возвращает статистику за прошлый день 
     */
    private function getStatYesterday() {
        $params_string = "id=45275493&"
                . "accuracy=full&"
                . "limit=99999&"
                . "sort=ym:pv:pageviews&"
                . "dimensions=ym:pv:date,ym:pv:URL&"
                . "metrics=ym:pv:pageviews&"
                . "date1=yesterday&"
                . "date2=yesterday&"
                . "pretty=1&"
                . "oauth_token=$this->_TOKEN";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->api_data_url . $params_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response_json = curl_exec($curl);

        curl_close($curl);
        $this->data = json_decode($response_json, true); //Декодирует JSON в ассоциативный массив
        return $this->prepare($this->data);
    }

    /**
     * Подготавливает данные к сохранению в БД
     */
    private function prepare($data = null) {
        if (!$data) {
            die('An empty error exception at ' . __METHOD__);
        }
        
        $prepared_data = [];
        foreach ($this->data['data'] as $value) {
            $prepared_data[] = [
                'date' => $value['dimensions']['0']['name'],
                'domain' => $value['dimensions']['1']['name'],
                'views' => $value['metrics']['0'],
                'source' => self::$this_source_url
            ];
        }
        return $prepared_data;
    }

    /**
     * Сохраняет переданный ассоциативный массив в БД
     * @param Array $prepared_data
     */
    private function saveAll($prepared_data = null) {
        if (!$prepared_data) {
            die('An empty error exception at ' . __METHOD__);
        }

        $stmt = $this->db->PDO->prepare("INSERT INTO pagehits (URL, HITS, DATE, SOURCE) VALUES (?,?,?,?)");

        $stmt->bindParam(':URL', $URL);
        $stmt->bindParam(':HITS', $HITS);
        $stmt->bindParam(':DATE', $DATE);
        $stmt->bindParam(':SOURCE', $SOURCE);

        foreach ($prepared_data as $data) {
            $URL = $data['domain'];
            $HITS = $data['views'];
            $DATE = $data['date'];
            $SOURCE = $data['source'];
            $stmt->execute([$URL, $HITS, $DATE, $SOURCE]);
        }
        return true;
    }

}

//end_class
?>