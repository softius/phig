<?php

namespace Phig\Command\Helper;

use Symfony\Component\Console\Helper\Helper,
	Phig\MigrationManager;

class MigrationManagerHelper extends Helper
{
	private $mm;

	public function __construct(MigrationManager $mm)
	{
		$this->mm = $mm;
	}

	public function getMigrationManager()
	{
		return $this->mm;
	}

	public function getName()
	{
		return 'migrationManager';
	}
}