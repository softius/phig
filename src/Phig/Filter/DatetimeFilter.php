<?php

namespace Phig\Filter;

/**
 * Filters files located in the migration directory by their name,
 * assuming that the name convention follows the format YYMMDDHHIISS.php
 */
class DatetimeFilter extends \FilterIterator
{
	// @todo entiry remove from_version and rename to_version to target
	private $from_version;
	private $to_version;

	/**
	 * 
	 * @param \Iterator $iterator iterator to apply the filter on
	 * @param type $from_version
	 * @param type $to_version
	 */
	public function __construct(\Iterator $iterator, $from_version=null, $to_version=null)
	{
		parent::__construct($iterator);
		$this->setFromVersion($from_version);
		$this->setToVersion($to_version);
	}
	
	public function setFromVersion($version)
	{
		if (null !== $version)
			$this->from_version = \DateTime::createFromFormat('YmdHis', (string) $version);
	}

	public function setToVersion($version)
	{
		if (null !== $version)
			$this->to_version = \DateTime::createFromFormat('YmdHis', (string) $version);
	}

	public function accept()
	{
		$version = $this->getInnerIterator()->current();
		if (null === $this->to_version)
			$accept = ( self::compare($version, $this->from_version) >= 0 );
		else 
			$accept = ( self::compare($version, $this->from_version) >= 0 && self::compare($this->to_version, $version) >= 0 );
		return $accept;
	}
	
	public function compare($a, $b)
	{
		return $this->toTimestamp($a) - $this->toTimestamp($b);
	}
	
	private function toTimestamp($value)
	{
		if ($value instanceof \Datetime) {
			return $value->getTimestamp();
		} elseif(empty($value)) {
			return 0;
		} 
		
		return \DateTime::createFromFormat('YmdHis', (string) $value)->getTimestamp();
	}
}