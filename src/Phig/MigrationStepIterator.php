<?php

namespace Phig;

class MigrationStepIterator extends \FilesystemIterator
{
	/**
	 * Default constructor.
	 * Extends FilesystemIterator to iterate through the files and return an 
	 * instance of migrationstep
	 * @param type $path
	 */
	public function __construct($path) {
		parent::__construct($path);
	}
	
	/**
	 * Overwrites \FilesystemIterator::current() to return an instance of 
	 * \Phig\MigrationStep
	 * @return \Phig\MigrationStep
	 */
	public function current()
	{
		return new MigrationStep(parent::current()->getPathname());
	}
}
