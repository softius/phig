<?php

class LastMigration implements \Phig\MigratableInterface
{
	public function up()
	{
		return 'M20121222114827::up';
	}

	public function down()
	{
		return 'M20121222114827::down';
	}
}

