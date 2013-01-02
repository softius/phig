<?php

namespace Phig\Tests\Console;

use Symfony\Component\Console\Tester\CommandTester;

class MigrateCommandTest extends \PHPUnit_Framework_TestCase
{
	private $app;
	
	public function getApp()
	{
		$mm = new \Phig\MigrationManager();
		$mm->setAdapter(new \Phig\Tests\Adapter\Filesystem(TEST_DIR .'/samples/data.test'));
		$mm->setFilter(new \Phig\Filter\DatetimeFilter(new \DirectoryIterator(TEST_DIR.'/samples/datetime')));
		
		$app = new \Phig\Application();
		$app->setHelperSet(
				new \Symfony\Component\Console\Helper\HelperSet(
					array(
						'mm' => new \Phig\Command\Helper\MigrationManagerHelper($mm)
					)
				)
		);
		$app->initCommands();
		
		return $app;
	}
	
	public function testMigrateExecuteNoOptions()
    {
		$app = $this->getApp();
        $command = $app->find('migrate');
        $tester = new CommandTester($command);
		
		$tester->execute(array('command' => $command->getName(), '--dump' => true));
		$this->assertRegexp('/20121222114827/', $tester->getDisplay());
		
        $tester->execute(array('command' => $command->getName()));
		$this->assertRegexp('/M20121222114827::up/', $tester->getDisplay());
    }
	
	public function testMigrateExecuteNoMigrations()
	{
		$app = $this->getApp();
        $command = $app->find('migrate');
        $tester = new CommandTester($command);
		
        $tester->execute(array('command' => $command->getName(), 'target' => '20121222114824', '--dump' => true));
		$this->assertEquals('No migrations found for execution.', trim($tester->getDisplay()));
		
        $tester->execute(array('command' => $command->getName(), 'target' => '20121222114824'));
		$this->assertEquals('No migrations found for execution.', trim($tester->getDisplay()));
	}
	
	public function testMigrateExecuteRef()
	{
		$app = $this->getApp();
        $command = $app->find('migrate');
        $tester = new CommandTester($command);
		
		// normal migration
		$tester->execute(array('command' => $command->getName(), 'target' => '20121222114827', '--dump' => true));
		$this->assertRegexp('/20121222114827/'. PHP_EOL, $tester->getDisplay());
        $tester->execute(array('command' => $command->getName(), 'target' => '20121222114827'));
		$this->assertRegexp('/M20121222114827::up/'. PHP_EOL, $tester->getDisplay());
		
		// rollback
        $tester->execute(array('command' => $command->getName(), 'target' => '20121222114822', '--dump' => true));
		$this->assertRegexp('/20121222114827.*20121222114824/s', $tester->getDisplay());
        $tester->execute(array('command' => $command->getName(), 'target' => '20121222114822'));
		$this->assertRegexp('/M20121222114827::down\s+M20121222114824::down/', $tester->getDisplay());
	}
	
	public function testRollbackExecuteNoOptions()
	{
		$app = $this->getApp();
		$command = $app->find('rollback');
		$tester = new CommandTester($command);
		
		$tester->execute(array('command' => $command->getName(), '--dump' => true));
		$this->assertRegexp('/20121222114824/', $tester->getDisplay());
		$tester->execute(array('command' => $command->getName()));
		$this->assertRegexp('/M20121222114824::down/', $tester->getDisplay());
	}
	
	public function testRollbackExecuteRedo()
	{
		$app = $this->getApp();
		$command = $app->find('rollback');
		$tester = new CommandTester($command);
		
		$tester->execute(array('command' => $command->getName(), '--redo' => true));
		$this->assertRegexp('/M20121222114824::down\s+M20121222114824::up/', $tester->getDisplay());
	}
}