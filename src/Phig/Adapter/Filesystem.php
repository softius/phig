<?php

namespace Phig\Adapter;

use Phig\AdapterInterface;
use Phig\MigratableInterface;

/**
 * Migration adapter that uses the filesystem to store and retrieve migration details.
 * Information is stored as seriazed array in the specified file (source). It is
 * updated only the filesystem adapter destruction thus it is important to avoid
 * running more than one migration scripts at once.
 */
class Filesystem implements AdapterInterface
{
	/**
	 * Full path of the file
	 * @var string
	 */
	private $source;
	
	/**
	 * Migrations executed so far
	 * Associative array, the key holds the ref number
	 * @var array
	 */
	private $migrations;

	/**
	 * Class constructor
	 * @param string $source file to be used 
	 */
	public function __construct($source)
	{
		$this->setSource($source);
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		file_put_contents($this->getSource(), serialize($this->migrations));
	}

	/**
	 * Assigns the source and loads the migrations.
	 * @param type $source file to be used
	 */
	protected function setSource($source)
	{
		$this->source = $source;
		$this->migrations = unserialize(file_get_contents($this->source));
		if (!is_array($this->migrations)) {
			$this->migrations = array();
		}
	}

	/**
	 * Returns the assigned source for this adapter
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Returns all the executed migrations as an associative array.
	 * The key holds the migration reference number
	 * @return array
	 */
	public function getMigrations()
	{
		return $this->migrations;
	}

	/**
	 * Adds the provided migration with the given reference number to the list 
	 * of executed migrations
	 * @param \Phig\MigratableInterface $migration migration object
	 * @param string $ref reference number of the provided migration
	 */
	public function addMigration(MigratableInterface $migration, $ref)
	{
		$this->migrations[$ref] = get_class($migration);
	}

	/**
	 * Removes the provided migration from the list of the executed migrations
	 * @param \Phig\MigratableInterface $migration migration object
	 * @param string $ref reference number of the provided migration
	 */
	public function removeMigration(MigratableInterface $migration, $ref)
	{
		unset($this->migrations[$ref]);
	}
}