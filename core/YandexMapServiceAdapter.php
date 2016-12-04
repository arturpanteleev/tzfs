<?php

namespace Core;

use Yandex\Geo\Api;

class YandexMapServiceAdapter implements MapServiceAdapter
{
	protected $api;

	protected $arToponims = [
		'house',
		'street',
		'district',
		'metro'
	];

	/**
	 * @var array
	 *
	 * Это в теории должно браться из базы например, но пока будет заглушка такая
	 */
	protected $arRestrictions = [
		'moscow' => [
			0.5,
			0.36,
			37.6173,
			55.755826
		]
	];

	public function __construct()
	{
		/*
		 * тут получается жесткая приввязка к конкретному классу в коде, но здесь приемлимо,
		 * т.к реализуем обращения к определеннмоу api
		 */
		$this->api = new Api();

	}

	/**
	 * @param $point
	 * @return array|bool
	 *
	 * Получаем информацию об объекте по его координате, рассматриваемт только те объекты информация о которых
	 * указана с точностью до номера дома. Собираем данные до района. Метро выделенно в отдельный метод
	 */
	public function getObjectByPoint($point, $arToponims = [])
	{
		if (empty($arToponims))
		{
			$arToponims = $this->arToponims;
		}
		else
		{
			$arToponims = array_intersect($this->arToponims, $arToponims);
		}

		$this->api
			->setPoint($point[0], $point[1])
			->setLimit(100)
			->load();

		$response = $this->api->getResponse();
		$collection = $response->getList();
		if (!empty($collection))
		{
			$arObject = [];
			foreach ($arToponims as $toponim)
			{
				foreach ($collection as $key => $item)
				{
					$kind = $item->getKind();

					if ($kind == $toponim && $kind != Api::KIND_METRO)
					{
						if ($kind == Api::KIND_HOUSE)
						{
							$arObject[Api::KIND_HOUSE] = $item->getPremiseNumber();
							unset($collection[$key]);
							break;
						}
						elseif ($kind == Api::KIND_STREET)
						{
							$arObject[Api::KIND_STREET] = $item->getThoroughfareName();
							unset($collection[$key]);
							break;
						}
						elseif ($kind == Api::KIND_DISTRICT)
						{
							$arObject[Api::KIND_DISTRICT] = $item->getDependentLocalityName();
							unset($collection[$key]);
							break;
						}
					}
				}
			}

			if (empty($arObject[Api::KIND_HOUSE]))
			{
				return false;
			}
			else
			{
				if (!in_array($arObject[Api::KIND_METRO], $arToponims))
				{
					$arObject[Api::KIND_METRO] = $this->getMetroByPoint($point);
				}

				return $arObject;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $address
	 * @param $maxPoints
	 * @return array|bool
	 *
	 * Возвращет набор координат соответствующий адрессу в запросе. Возвращает только объекты у которых точность
	 * указана до номера дома
	 */
	public function getPointsByAddress($address, $maxPoints, $arRestrictArea = [])
	{
		$this->api->setQuery($address);

		if (empty($arRestrictArea))
		{
			$arRestrictArea = $this->arRestrictions['moscow'];
		}

		$this->api
			->setLimit($maxPoints)
			->setArea($arRestrictArea[0], $arRestrictArea[1], $arRestrictArea[2], $arRestrictArea[3])
			->load();

		$response = $this->api->getResponse();

		if ($response->getFoundCount() > 0)
		{
			$arPoints = [];
			$collection = $response->getList();

			foreach ($collection as $item)
			{
				if ($item->getKind() == Api::KIND_HOUSE)
				{
					$arPoints[] = [
						$item->getLongitude(),
						$item->getLatitude()
					];
				}
			}

			return $arPoints;

		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $point
	 * @param int $count
	 * @return array|bool
	 *
	 * Для ментро отдельный метод пришлось делать, т.к если не указывать топоним то не всегда его выбирает
	 */
	protected function getMetroByPoint($point, $count = 5)
	{
		$this->api
			->setPoint($point[0], $point[1])
			->setLimit($count)
			->setKind(Api::KIND_METRO)
			->load();
		$response = $this->api->getResponse();
		$collection = $response->getList();
		$arMetro = [];
		if (!empty($collection))
		{
			foreach ($collection as $item)
			{
				$meta = $item->getRawData();
				$arMetro[] = $meta['name'];
			}

			return $arMetro;
		}
		else
		{
			return false;
		}
	}
}