<?php

namespace Core;

class SimpleCache implements CacheInterface
{
	private $_cacheFolder;

	public function __construct()
	{
		$this->_cacheFolder = __DIR__  . '/../cache/';
	}

	public function load($key)
	{
		$file = $this->_cacheFolder . md5($key);
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

	public function save($key, $data, $time = 60*60*24)
	{
		$file = $this->_cacheFolder . md5($key);
		$content['data'] = $data;
		$content['time'] = time();
		$content['ttl'] = $time;
		if (file_put_contents($file, serialize($content)))
		{
			@chmod($file, 0777);

			return true;
		}

		return false;
	}

	public function remove($key)
	{
		$file = $this->_cacheFolder . md5($key);
		if (file_exists($file))
		{
			unlink($file);
		}
	}
}
