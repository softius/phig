<?php

namespace Phig\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
	/**
	 * migrate
	 * migrate <ref>
	 * migrate <ref> --dump
	 * migrate <positive-int> --steps
	 * migrate <positive-int> --steps --dump
	 * migrate <positive-int> --steps --rollback
	 * migrate <positive-int> --steps --rollback --dump
	 * migrate <positive-int> --steps --redo
	 * migrate <positive-int> --steps --redo --dump
	 */
	protected function configure()
	{
		$this
			->setName('migrate')
			->setDescription('??????')
			->addArgument('target', InputArgument::OPTIONAL, 'The reference number to migrate to')
			// @todo ->addArgument('ref', InputArgument::OPTIONAL, 'Number of migrations to run')
			->addOption('--dump', null, InputOption::VALUE_NONE);
			// @todo add support for ->addOption('--redo', null, InputOption::VALUE_NONE);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$target = $input->getArgument('target');
		$manager = $this->getHelper('mm')->getMigrationManager();

		$migrations = $manager->getAvailableMigrations($target);
		if (0 == count($migrations)) {
			$output->writeln('<error>No migrations found for execution.</error>');
			return;
		}
		
		if ($input->getOption('dump')) {
			$messages = array('The following migrations will be executed:');
			foreach ($migrations as $mig_name => $mig_obj) {
				$messages[] = sprintf('* %s', $mig_name);
			}
			$output->writeln($messages);
		} else {
			$messages = $manager->migrate($target);
			if ( is_string($messages) || is_array($messages) ) {
				$output->writeln($messages);
			}
		}
	}

}