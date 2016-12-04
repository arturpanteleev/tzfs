<?php

require_once '../bootstrap.php';


$api = new \Yandex\Geo\Api();

// Можно искать по точке
//$api->setPoint(37.611347, 55.760241);

// Или можно икать по адресу
$api->setQuery('Тверская');

// Настройка фильтров
$api
	->setLimit(25) // кол-во результатов
	->setLang(\Yandex\Geo\Api::LANG_RU) // локаль ответа
	///->setKind(\Yandex\Geo\Api::KIND_METRO)
	->load();

$response = $api->getResponse();
$response->getFoundCount(); // кол-во найденных адресов
$response->getQuery(); // исходный запрос
$response->getLatitude(); // широта для исходного запроса
$response->getLongitude(); // долгота для исходного запроса

$arPoints = [];
// Список найденных точек
$collection = $response->getList();
foreach ($collection as $item) {
	echo '<pre>';
	var_dump($item->getRawData());
	echo '</pre>';
	echo '______________________________________________________________________________';
	$item->getAddress(); // вернет адрес
	$item->getLatitude(); // широта
	$item->getLongitude(); // долгота
	$item->getData(); // необработанные данные
}

