<?php
require_once 'classes/YandexMetrika.php';

$mode=$argv[1];

$metrikaObj = new YandexMetrika();
$metrikaObj->start();
?>

