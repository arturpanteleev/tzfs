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
	public function getObjectByPoint($point, $arToponims);
	public function getPointsByAddress($address, $maxPoint, $arRestrictArea);
}