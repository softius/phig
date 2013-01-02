<?php

namespace Phig;

/**
 * Migration Adapter interface defines all functions necessary to store, 
 * retrieve and in general manipulate the executed migrations.
 */
interface AdapterInterface
{
	/**
	 * Returns all the executed migrations as an associative array.
	 * The key holds the migration reference number
	 * @return array
	 */
	public function getMigrations();

	/**
	 * Adds the provided migration with the given reference number to the list 
	 * of executed migrations
	 * @param \Phig\MigratableInterface $migration migration object
	 * @param string $ref reference number of the provided migration
	 */
	public function addMigration(MigratableInterface $migration, $ref);

		/**
	 * Removes the provided migration from the list of the executed migrations
	 * @param \Phig\MigratableInterface $migration migration object
	 * @param string $ref reference number of the provided migration
	 */
	public function removeMigration(MigratableInterface $migration, $ref);
}