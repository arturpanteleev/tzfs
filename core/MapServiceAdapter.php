<?php

namespace Core;

/**
 * Interface MapServiceAdapter
 * @package Core
 *
 * Интерфейс-адаптер гарантирующий одинаковую работу со всеми провайдерами карт - гугл, яндекс, bing и .д
 */
interface MapServiceAdapter
{
	public function getObjectByPoint($point);

	public function getPointsByAddress($address, $maxPoint);

	public function getMetroByPoint($point, $count = 5);
	/*public function getHouseByPoint($point);
	public function getStreetByPoint($point);
	public function getDistrictByPoint($point);*/

}