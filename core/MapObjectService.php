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
	protected $cacheManager;
	protected $dataProvider;

	/**
	 * MapObjectService constructor.
	 * @param MapServiceAdapter $dataProvider
	 * @param CacheInterface $cache
	 */
	public function __construct(MapServiceAdapter $dataProvider, CacheInterface $cache)
	{
		$this->dataProvider = $dataProvider;
		$this->cacheManager = $cache;
	}

	/**
	 * @param $point array формата [долгота, широта]
	 * @return mixed
	 *
	 * Получает данные объекта по координатам
	 */
	protected function getObjectByPoint($point)
	{
		$arObject = $this->dataProvider->getObjectByPoint($point, []);

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
		if ($objectFromCache = $this->cacheManager->load($address))
		{
			return $objectFromCache;
		}
		else
		{
			$arPoints = $this
				->dataProvider
				->getPointsByAddress($address, $limit, []);

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

				$this->cacheManager->save($address, $arObjects, 3600);

				return $arObjects;
			}
			else
			{
				return false;
			}
		}
	}
}