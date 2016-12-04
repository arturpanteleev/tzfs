<?php

namespace Core;
/**
 * Class MapObjectService
 * @package Core
 *
 * Класс в который инкапсулируем всю логику получения объектов
 */
class MapObjectService
{
	protected $cacheTtl = 60 * 60 * 24;
	protected $dataProvider;

	/**
	 * MapObjectService constructor.
	 * @param MapServiceAdapter $dataProvider параметр принмает объект реализующий интерфейс для получения объектов с
	 * от сервисов карт
	 */
	public function __construct(MapServiceAdapter $dataProvider)
	{
		$this->dataProvider = $dataProvider;
	}

	/**
	 * @param $point array формата [долгота, широта]
	 * @return mixed
	 *
	 * Получает данные объекта по координатам
	 */
	protected function getObjectByPoint($point)
	{
		$arObject = $this->dataProvider->getObjectByPoint($point);

		return $arObject;
	}

	/**
	 * @param $address
	 * @param int $limit
	 * @return array|bool
	 *
	 * Метод возвращает информацию об объектах по запрашиваемому адрессу
	 */
	public function getObjectsByAddress($address, $limit = 10)
	{

		/**
		 * Конечно стоило бы передавать какой-нибудь объект, реализующий интерфейс кеширования в конструктор
		 * но тут я решил сделать упущение, т.к не подключал никаких кеширующих библиотек
		 */
		//	if ($serializedObjects = \apcu_fetch($address))
		if (false)
		{
			//return $serializedObjects;
		}
		else
		{
			$arPoints = $this
				->dataProvider
				->getPointsByAddress($address, $limit);

			$arObjects = [];

			if (!empty($arPoints))
			{
				foreach ($arPoints as $point)
				{
					$arOneObject = $this->getObjectByPoint($point);
					if ($arOneObject != false)
					{
						$arObjects[] = $arOneObject;
					}
				}

				//\apcu_add($address,$arObjects);
				return $arObjects;
			}
			else
			{
				return false;
			}
		}
	}
}