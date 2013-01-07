<?php

namespace Phig\Tests\Console;

use Symfony\Component\Console\Tester\CommandTester;

class PdoMigrateCommandTest extends \PHPUnit_Framework_TestCase
{
	public function getApp()
	{
		$mm = new \Phig\MigrationManager();
		$mm->setAdapter(new \Phig\Tests\Adapter\Filesystem(TEST_DIR .'/samples/pdo_datetime.db'));
		$mm->setFilter(new \Phig\Filter\DatetimeFilter(new \Phig\MigrationStepIterator(TEST_DIR.'/samples/pdo_datetime')));
		$mm->setHelpers(array(
			'connection' => new \Phig\Tests\DummyPdo()
		));
		
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
	
	public function testMigrateAndRollback()
	{
		$app = $this->getApp();
		
		
        $command = $app->find('migrate');
        $tester = new CommandTester($command);
		$tester->execute(array('command' => $command->getName()));
		$this->assertRegexp('/24911338e1eda75ef1181696be4ea133/', $tester->getDisplay());
		
		$command = $app->find('rollback');
		$tester = new CommandTester($command);
		$tester->execute(array('command' => $command->getName()));
		$this->assertRegexp('/e0306cb3cc001db0591e5a6a18e37159/', $tester->getDisplay());
	}

}
