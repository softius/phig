<?php

namespace Phig;

class MigrationManager 
{
	/**
	 * Migration Adapter
	 * @var \Phig\AdapterInterface
	 */
	private $adapter;
	
	/**
	 * FilterIterator to filter migration files
	 * @var \FilterIterator
	 */
	private $filter;

	/**
	 * Assigns the adapter to be used during migrations
	 * @param \Phig\AdapterInterface $adapter adapter to be used
	 */
	public function setAdapter(\Phig\AdapterInterface $adapter)
	{
		$this->adapter = $adapter;
	}

	/**
	 * Returns the assigned adapter
	 * @return \Phig\AdapterInterface
	 */
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * Assigns the filter iterator to be used during migrations
	 * @param \FilterIterator $filter filte to be used
	 */
	public function setFilter(\FilterIterator $filter)
	{
		$this->filter = $filter;
	}

	/**
	 * Returns the assigned filter iterator
	 * @return \FilterIterator
	 */
	public function getFilter()
	{
		return $this->filter;
	}
	
	/**
	 * Returns true only and only if the target is less or equal 
	 * than the greatest migration executed so far.
	 * @param string $target reference number to be checked against executed
	 * migrations
	 * @return boolean 
	 */
	public function isRollback($target)
	{
		return null !== $target && $this->getFilter()->compare($target, $this->getGreatestMigration()) <= 0;
	}
	
	/**
	 * Returns an array of all the migrations that should be executed to reach 
	 * the provided target, taking into consideration both the executed 
	 * migrations. This works for both normal migrations and rollbacks.
	 * @param string $target migration reference number to reach 
	 * @return array
	 */
	public function getAvailableMigrations($target=null)
	{
		// for normal migration we need all files to target
		// for rollbacks we need from target to the greatest migration
		$filter = $this->getFilter();
		if (!$this->isRollback($target)) {
			if (null !== $target) {
				$filter->setToVersion($target);
			}
		} else {	// rollback
			$filter->setFromVersion($target);
			$filter->setToVersion($this->getGreatestMigration());
		}
		
		// array to hold <migration ref> => <migration full path>
		$migration_refs = array();
		foreach ($filter as $migration_file) {
			$migration_refs[$migration_file->getBasename('.' . $migration_file->getExtension())] = $migration_file->getPathName();
		}

		// migrations to be executed
		$to_execute = array();
		if ($this->isRollback($target)) {
			// rollback: execute the common part of $executed and $migration_refs
			$executed = $this->getExecutedMigrations();
			foreach($migration_refs as $ref => $migration_class) {
				if (array_key_exists($ref, $executed)) {
					$to_execute[] = $ref;
				}
			}
		} else {
			// migration: execute what's missing - the different of $executed and $available
			$to_execute = array_diff(array_keys($migration_refs), array_keys($this->getExecutedMigrations()));
		}
		
		// go through $to_execute, load class file and build new class
		$migrations = array();
		foreach ($to_execute as $ref) {
			$migration_file = $migration_refs[$ref];
			
			require_once $migration_file;
			$class_name = 'M' . $ref;
			$migrations[$ref] = new $class_name();
		}
		
		// sort migrations according to filter and target
		// remove one so that to avoid executing target in the case of rollback
		$this->sortMigrations($migrations, $target);
		if ($this->isRollback($target)) {
			array_pop($migrations);
		}
		return $migrations;
	}
	
	/**
	 * Returns all the executed migrations. This acts as a proxy method to
	 * AdapterInterface::getMigrations plus the results are sorted 
	 * according to the assigned filter.
	 * @return array
	 */
	public function getExecutedMigrations()
	{
		$migrations = $this->getAdapter()->getMigrations();
		uksort($migrations, array($this->getFilter(), 'compare'));
		return $migrations;
	}
	
	public function getGreatestMigration()
	{
		// @todo: shall we wrap this to local getExecuted migrations
		return $this->getAdapter()->getGreatestMigration();
	}
	
	/**
	 * Executes a migration with the purpose to reach the specified target. 
	 * If target has been already executed we are doing a rollback.
	 * @param string $target migration ref number to reach
	 * @return array
	 */
	public function migrate($target=null)
	{	
		$migrations = $this->getAvailableMigrations($target);
		if (null === $target) {
			end($migrations);
			$target = key($migrations);
		}
		
		if (!$this->isRollback($target)) {
			return $this->executeUpMigrations($migrations);
		} else {
			return $this->executeDownMigrations($migrations);
		}
	}
	
	/**
	 * Executes rollback. It reverses a given number (steps) of the already 
	 * executed migrations.
	 * @param int $steps number of migraitons to be reversed
	 * @param boolean $redo rexecute the reversed migrations
	 * @return array
	 */
	public function rollback($steps=1, $redo=false)
	{
		// @todo make this a method?
		$filter = $this->getFilter();
		$migration_refs = array();
		foreach ($filter as $migration_file) {
			$migration_refs[$migration_file->getBasename('.' . $migration_file->getExtension())] = $migration_file->getPathName();
		}
		
		$migrations = array_slice($this->getExecutedMigrations(), -$steps, null, true);
		
		$to_execute = array();
		foreach ($migrations as $ref => $migration_class) {
			require_once $migration_refs[$ref];
			$to_execute[$ref] = new $migration_class();
		}
		
		$results = $this->executeDownMigrations($to_execute);
		if ( $redo ) {
			reset($migrations);
			$target = key($migrations);
			$results = array_merge($results,$this->migrate($target));
		}
		
		return $results;
	}
	
	/**
	 * Calls the up method on the provided migrations 
	 * and notifies the adapter to updated its list.
	 * @param array $migrations
	 */
	protected function executeUpMigrations($migrations)
	{
		$results = array();
		foreach ($migrations as $ref => $migration) {
			$results[] = $migration->up();
			$this->getAdapter()->addMigration($migration, $ref);
		}
		
		return $results;
	}

	/**
	 * Calls the down method on the provided migrations 
	 * and notifies the adapter to updated its list.
	 * @param array $migrations
	 * @return array
	 */
	protected function executeDownMigrations($migrations)
	{
		$results = array();
		foreach ($migrations as $ref => $migration) {
			$results[] = $migration->down();
			$this->getAdapter()->removeMigration($migration, $ref);
		}
		
		return $results;
	}
	
	/**
	 * Provided as a sorter on migrations by using adapter compare method and 
	 * taking into consideration the active target. If we are looking into a 
	 * rollback result the array is reversed.
	 * @param array $migrations
	 * @param string $target
	 * @return array
	 */
	private function sortMigrations(& $migrations, $target)
	{
		$filter = $this->getFilter();
		if (!$this->isRollback($target)) {
			uksort($migrations, array($filter, 'compare'));
		} else {
			uksort($migrations, function($a, $b) use ($filter) {
				return $filter->compare($b, $a);	// reverse arguments
			} );
		}
		
	}
}