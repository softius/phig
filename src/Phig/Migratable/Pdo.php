<?php

namespace Phig\Migratable;

abstract class Pdo implements \Phig\MigratableInterface
{
	/**
	 * PDO Connection instance
	 * @var \PDO
	 */
	private $connection;
	
	/**
	 * Returns the assigned PDO Connection
	 * @return \PDO
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * Assigns a PDO Connection to this migration
	 * @param \PDO $connection
	 */
	public function setConnection(\PDO $connection)
	{
		$this->connection = $connection;
	}
}
