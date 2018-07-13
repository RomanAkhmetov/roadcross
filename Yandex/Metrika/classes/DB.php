<?php

class DB {

    public $PDO;

    public function __construct() {
        $host = 'localhost';
        $db = 'analytics';
        $user = 'root';
        $pass = '';

        try {
            $this->PDO = new PDO('mysql:host=' . $host . ';dbname=' . $db . '', $user, $pass);
        } catch (PDOException $e) {
            die('Подключение не удалось: ' . $e->getMessage);
        }
    }

}

//end_class