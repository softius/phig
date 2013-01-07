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
	 * Helpers to be passed to migrations
	 * @var array
	 */
	private $helpers;

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
	 * Assigns an array of helpers to be used with migrations
	 * @param array helpers
	 */
	public function setHelpers($helpers)
	{
		$this->helpers = $helpers;
	}
	
	/**
	 * Retrieves the helper associated with the specified name. 
	 * If no helper found, returns false.
	 * @param string $name
	 */
	public function getHelper($name)
	{
		return (array_key_exists($name, $this->helpers)) ? $this->helpers[$name] : false;
	}
	
	/**
	 * Returns true only and only if the target is less or equal 
	 * than the greatest migration executed so far.
	 * @param string $target reference number to be checked against executed
	 * migrations
	 * @return boolean 
	 */
	public function isDownwards($target)
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
		if (!$this->isDownwards($target)) {
			if (null !== $target) {
				$filter->setToVersion($target);
			}
		} else {	// rollback
			$filter->setFromVersion($target);
			$filter->setToVersion($this->getGreatestMigration());
		}

		$migration_steps = $this->getMigrationSteps();

		// migrations to be executed
		$to_execute = array();
		if ($this->isDownwards($target)) {
			// rollback: execute the common part of $executed and $migration_refs
			$executed = $this->getExecutedMigrations();
			foreach($migration_steps as $ref => $migration_class) {
				if (array_key_exists($ref, $executed)) {
					$to_execute[] = $ref;
				}
			}
		} else {
			// migration: execute what's missing - the different of $executed and $available
			$to_execute = array_diff(array_keys($migration_steps), array_keys($this->getExecutedMigrations()));
		}
		
		// go through $to_execute and find the migration steps
		$migrations = array();
		foreach ($to_execute as $ref) {
			$migrations[$ref] = $migration_steps[$ref];
		}
		
		// sort migrations according to filter and target
		// remove one so that to avoid executing target in the case of rollback
		$this->sortMigrations($migrations, $target);
		if ($this->isDownwards($target)) {
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
	
	/**
	 * Returns the reference number of the greatest migration
	 * @return string
	 */
	public function getGreatestMigration()
	{
		return (string) end(array_keys($this->getExecutedMigrations()));
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
		
		if (!$this->isDownwards($target)) {
			return $this->doMigration($migrations, AdapterInterface::UP);
		} else {
			return $this->doMigration($migrations, AdapterInterface::DOWN);
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
		$migrations = array_slice($this->getExecutedMigrations(), -$steps, null, true);
		
		$migration_steps = $this->getMigrationSteps();
		$to_execute = array();
		foreach ($migrations as $ref => $migration_class) {
			$to_execute[$ref] = $migration_steps[$ref];
		}
		
		$results = $this->doMigration($to_execute, AdapterInterface::DOWN);
		if ( $redo ) {
			reset($migrations);
			$target = key($migrations);
			$results = array_merge($results,$this->migrate($target));
		}
		
		return $results;
	}
	
	/**
	 * Calls the up or down method on the provided migrations 
	 * and notifies the adapter to updated its list, 
	 * based on the provided direction.
	 * @param array $migrations migrations to be executed in that order
	 * @param string $direction UP|DW 
	 * @return array
	 */
	private function doMigration($migrations, $direction)
	{
		$methods = array(
			AdapterInterface::UP => array('up', 'addMigration'),
			AdapterInterface::DOWN => array('down', 'removeMigration'),
		);
		
		$results = array();
		foreach($migrations as $ref => $migration) {
			list($migration_method, $adapter_method) = $methods[$direction];
			
			// assign helpers to instance
			$impl = $migration->getImplementation();
			foreach( get_class_methods($impl) as $method ) {
				// get only setters
				if ('set' !== substr($method, 0, 3)) {
					continue;
				}
				
				// a quick and dirty way to find the helper method
				// need to take care CamelCase methods
				$helper_name = strtolower(substr($method, 3));
				$helper = $this->getHelper($helper_name);
				if (!is_bool($helper)) {
					call_user_func(array($impl, $method), $helper);
				}
			}
			
			// call up or down method
			$results[] = call_user_func(array($migration, $migration_method));
			call_user_func(array($this->getAdapter(), $adapter_method), $impl, $ref);
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
		if (!$this->isDownwards($target)) {
			uksort($migrations, array($filter, 'compare'));
		} else {
			uksort($migrations, function($a, $b) use ($filter) {
				return $filter->compare($b, $a);	// reverse arguments
			} );
		}
		
	}
	
	/**
	 * Go through the filter iterator and stores the fetched migration steps
	 * in an associative array with the key holding the reference number for 
	 * easy access.
	 * @return array
	 */
	private function getMigrationSteps()
	{
		$migration_steps = array();
		foreach ($this->getFilter() as $step) {
			$migration_steps[$step->getReference()] = $step;
		}
		
		return $migration_steps;
	}
}