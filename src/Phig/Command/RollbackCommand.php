<?php

namespace Phig\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends Command
{

	protected function configure()
	{
		$this
			->setName('rollback')
			->setDescription('????')
			->addArgument('steps', InputArgument::OPTIONAL, '?????', 1)
			->addOption('--dump', null, InputOption::VALUE_NONE)
			->addOption('--redo', null, InputOption::VALUE_NONE);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$steps = $input->getArgument('steps');
		$manager = $this->getHelper('mm')->getMigrationManager();
		
		$migrations = $manager->getExecutedMigrations();
		$migrations = array_slice($migrations, -$steps, null, true);
		if ($input->getOption('dump')) {
			$messages = array('The following migrations will be reversed:');
			foreach ($migrations as $mig_name => $mig_obj) {
				$messages[] = sprintf('* %s', $mig_name);
			}
			$output->writeln($messages);
		} else {
			$messages = $manager->rollback($steps, $input->getOption('redo'));
			if ( is_string($messages) || is_array($messages) ) {
				$output->writeln($messages);
			}
		}
	}

}