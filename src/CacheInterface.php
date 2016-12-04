<?php

namespace Core;

interface CacheInterface {
	public function save($key, $data, $time);
	public function load($key);
	public function remove($key);
}