<?php

namespace Phig\Tests\Adapter;

use Phig\Tests\DummyMigration;
use Phig\Tests\Adapter\Filesystem as DummyFilesystem;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
	private $adapter;
	
	public function setUp()
	{
		$this->adapter = new DummyFilesystem(TEST_DIR .'/samples/data.test');
	}
	
	/**
     * @covers \Phig\Adapter\Filesystem::getSource
	 * @covers \Phig\Adapter\Filesystem::setSource
     */
	public function testGetSource()
	{
		$this->assertEquals($this->adapter->getSource(), TEST_DIR .'/samples/data.test'); 
	}
	
	/**
	 * @covers \Phig\Adapter\Filesystem::__construct
     * @covers \Phig\Adapter\Filesystem::getMigrations
     */
	public function testGetMigrations()
	{
		$this->assertEquals(count($this->adapter->getMigrations()), 3);
		$this->assertEquals(key($this->adapter->getMigrations()), '20121222114448');
	}
	
	/**
     * @covers \Phig\Adapter\Filesystem::addMigration
     */
	public function testAddMigration()
	{
		$migration = new DummyMigration();
		$this->adapter->addMigration($migration, 'dummy-migration');
		$this->assertEquals(count($this->adapter->getMigrations()), 4);
	}
	
	/**
     * @covers \Phig\Adapter\Filesystem::removeMigration
     */
	public function testRemoveMigration()
	{
		$migration = new DummyMigration();
		$this->adapter->removeMigration($migration, 'dummy-migration');
		$this->assertEquals(count($this->adapter->getMigrations()), 3);
	}
}
