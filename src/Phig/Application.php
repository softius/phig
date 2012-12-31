<?php

namespace Phig;

use Symfony\Component\Console\Input\InputInterface;

class Application extends \Symfony\Component\Console\Application
{
	public function __construct()
	{
		parent::__construct('Softius Migration', '0.1a');
	}

	public function initCommands()
	{
		$this->add(new Command\MigrateCommand());
		$this->add(new Command\RollbackCommand());
	}
}