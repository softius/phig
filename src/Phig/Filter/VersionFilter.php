<?php

namespace Phig\Filter;

class VersionFilter extends \FilterIterator
{
	private $from_version;
	private $to_version;

	public function __construct($iterator, $from_version=null, $to_version=null)
	{
		parent::__construct($iterator);
		$this->setFromVersion($from_version);
		$this->setToVersion($to_version);
	}

	public function setFromVersion($version)
	{
		$this->from_version = $version;
	}

	public function setToVersion($version)
	{
		$this->to_version = $version;
	}

	public function accept()
	{
		$migration_file = $this->getInnerIterator()->current();
		if ( $migration_file->isDot() )
			return false;

		
		$version = $migration_file->getBasename( '.' . $migration_file->getExtension() );
		return (version_compare($version, $this->from_version, '>=') && version_compare($version, $this->to_version, '<='));
	}
}