<?php

namespace Core;

use Yandex\Geo\Api;

class YandexMapServiceAdapter implements MapServiceAdapter
{
	protected $api;

	/**
	 * @var array
	 *
	 * Это в теории должно браться из базы например, но пока будет заглушка така
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

		/*
		 * Здесь какая то фигня тут надо придумать что то
		 */
		$this->api->setArea(
			$this->arRestrictions['moscow'][0],
			$this->arRestrictions['moscow'][1],
			$this->arRestrictions['moscow'][2],
			$this->arRestrictions['moscow'][3]
		);
		$this->api->useAreaLimit(true);
	}

	/**
	 * @param $point
	 * @return array|bool
	 *
	 * Получаем информацию об объекте по его координате, рассматриваемт только те объекты информация о которых
	 * указана с точностью до номера дома. Собираем данные до района. Метро выделенно в отдельный метод
	 */
	public function getObjectByPoint($point)
	{
		/*
		 * помоему лимит не работает, хотя в запросе передается яндексу
		 */
		$this->api
			->setPoint($point[0], $point[1])
			->setLimit(3)
			->load();

		$response = $this->api->getResponse();
		$collection = $response->getList();

		if (!empty($collection))
		{
			$arObject = [];
			foreach ($collection as $item)
			{
				$kind = $item->getKind();
				if ($kind == 'house')
				{
					$arObject[Api::KIND_HOUSE] = $item->getPremiseNumber();
				}
				elseif ($kind == 'street')
				{
					$arObject[Api::KIND_STREET] = $item->getThoroughfareName();
				}
				elseif ($kind == 'district')
				{
					$arObject[Api::KIND_DISTRICT] = $item->getDependentLocalityName();
					break;
				}
			}

			if (empty($arObject[Api::KIND_HOUSE]))
			{
				return false;
			}
			else
			{
				$arObject[Api::KIND_METRO] = $this->getMetroByPoint($point);
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
	public function getPointsByAddress($address, $maxPoints)
	{
		$this->api->setQuery($address);
		$this->api
			->setLimit($maxPoints)
			->setLang(Api::LANG_RU)
			->load();

		$response = $this->api->getResponse();

		if ($response->getFoundCount() > 0)
		{
			$arPoints = [];
			$collection = $response->getList();

			foreach ($collection as $item)
			{
				if ($item->getKind() == 'house')
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
	 * Возвращает станции метро, близжайшие к заданной координате
	 */
	public function getMetroByPoint($point, $count = 5)
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
		} else {
			return false;
		}
	}

	/*
		public function getHouseByPoint($point)
		{
			$this->api
				->setPoint($point[0], $point[1])
				->setLimit(1)
				->setKind(Api::KIND_HOUSE)
				->load();
			$response = $this->api->getResponse();
			$collection = $response->getList();
			if (!empty($collection))
			{
				return $collection[0]->getRawData()['name'];
			}
			else
			{
				return false;
			}
		}

		public function getStreetByPoint($point)
		{
			$this->api
				->setPoint($point[0], $point[1])
				->setLimit(1)
				->setKind(Api::KIND_STREET)
				->load();
			$response = $this->api->getResponse();
			$collection = $response->getList();
			if (!empty($collection))
			{
				return $collection[0]->getRawData()['name'];
			}
			else
			{
				return false;
			}
		}

		public function getDistrictByPoint($point)
		{
			$this->api
				->setPoint($point[0], $point[1])
				->setLimit(1)
				->setKind(Api::KIND_DISTRICT)
				->load();

			$response = $this->api->getResponse();
			$collection = $response->getList();
			if (!empty($collection))
			{
				return $collection[0]->getRawData()['name'];
			}
			else
			{
				return false;
			}
		}*/


}