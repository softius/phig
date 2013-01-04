<?php

namespace Phig;

/**
 * Acts as a proxy to all implementations of MigratableInterface
 */
class MigrationStep
{
	/**
	 * Source file that includes the implementation
	 * @var string
	 */
	private $source;
	
	/**
	 * Reference number, usually a string represending a datetime or a sequence
	 * @var string
	 */
	private $reference;
	
	/**
	 * Name of the implementation class
	 * @var string
	 */
	private $class_name;
	
	/**
	 * An instance of the implementation, must be of class $class_name
	 * @var \Phig\MigratableInterface
	 * @see $class_name
	 */
	private $instance;
	
	/**
	 * Default constructor. Based on the provided file path it extracts the 
	 * reference number and class name. The filename must follow the naming 
	 * convention <reference>-<ClassName>.php
	 * @param string $source full path of the source file that contains 
	 * the implementation
	 */
	public function __construct($source)
	{
		$this->source = $source;
		
		$filename = pathinfo($this->getSource(), PATHINFO_FILENAME);
		list($this->reference, $this->class_name) = explode('-', $filename, 2);
		
		$this->instance = null;
	}
	
	/**
	 * Returns the reference number for this migration step
	 * @return string
	 */
	public function getReference()
	{
		return $this->reference;
	}
	
	/**
	 * Returns the class name assigned to this migration step
	 * @return string
	 */
	public function getClassName()
	{
		return $this->class_name;
	}
	
	/**
	 * Returns the source as specified in constructor
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * Returns an instance of the class name for this migration step
	 * (also implements MigratableInterface). If you call this method more than
	 * once, the same object will be returned.
	 * @return \Phig\MigratableInterface
	 */
	public function getImplementation()
	{
		if (null == $this->instance) {
			require_once $this->getSource();
			$class_name = $this->getClassName();
			$this->instance = new $class_name();
		}
		
		return $this->instance;
	}
	
	/**
	 * Passes all unknown methods to implementation
	 * @param string $name
	 * @param array $params
	 * @return mixed
	 */
	public function __call($name, $params)
	{
		return call_user_func_array(array($this->getImplementation(), $name), $params);
	}
	
	/**
	 * As a string representation returns the reference number, which is unique.
	 * @return string
	 */
	public function __toString()
	{
		return $this->getReference();
	}
}
