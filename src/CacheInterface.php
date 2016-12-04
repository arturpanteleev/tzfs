<?php

namespace Core;
/**
 * Interface CacheInterface
 * @package Core *
 */
interface CacheInterface {
	public function save($key, $data, $time);
	public function load($key);
	public function remove($key);
}