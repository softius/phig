<?php

namespace Phig\Tests;

class DummyPdo extends \PDO
{
	public function __construct()
	{
	}
	
	/**
	 * Mock method of PDO::exec to return md5 hash of the provided query
	 * @param string $query
	 */
	public function exec($query)
	{
		return md5($query);
	}
}
