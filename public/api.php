<?php

require_once '../vendor/autoload.php';

use Core\YandexMapServiceAdapter;
use Core\MapObjectService;
use Core\SimpleCache;

/*
 * тут можно было бы более детально обработать входные параметры но это оставим яндексу
 *
 * также здесь можно делать рзаные проверки по типу токена или защиты от csrf
 */
if (empty($_GET['query'])){
	die;
}

$query = $_GET['query'];
$limit = 10;

$objFactory = new MapObjectService(new YandexMapServiceAdapter(), new Core\SimpleCache());
$arObjects = $objFactory->getObjectsByAddress($query, $limit);

/**
 * Это просто для демонтсрации результата. По уму нужен объект, который будет
 * или отдавать json, или подключать вьюху или еще как то взаимодейстовать
 */
echo '<pre>';
    print_r($arObjects);
echo '</pre>';


