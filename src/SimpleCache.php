<?php

namespace Core;
/**
 * Class SimpleCache
 * @package Core
 *
 * Простейший класс для кеширования
 */
class SimpleCache implements CacheInterface
{
	protected $cacheFolder = __DIR__ . '/../cache/';
	protected $ttl = 3600;

	/**
	 * SimpleCache constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function load($key)
	{
		$file = $this->getFilenameFromKey($key);
		if (file_exists($file))
		{
			$data = unserialize(file_get_contents($file));
			if (time() <= $data['time'] + $data['ttl'])
			{
				return $data['data'];
			}
			else
			{
				unlink($file);

				return false;
			}
		}

		return false;
	}

	/**
	 * @param $key
	 * @param $data
	 * @param $time
	 * @return bool
	 */
	public function save($key, $data, $time)
	{
		$file = $this->getFilenameFromKey($key);
		$content['data'] = $data;
		$content['time'] = time();
		if (empty($time))
		{
			$time = $this->ttl;
		}
		$content['ttl'] = $time;
		if (file_put_contents($file, serialize($content)))
		{
			@chmod($file, 0777);

			return true;
		}

		return false;
	}

	/**
	 * @param $key
	 */
	public function remove($key)
	{
		$file = $this->getFilenameFromKey($key);
		if (file_exists($file))
		{
			unlink($file);
		}
	}

	/**
	 * @param $key
	 * @return string
	 */
	protected function getFilenameFromKey($key)
	{
		return $this->cacheFolder . md5($key);
	}
}
