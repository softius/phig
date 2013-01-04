<?php

class FirstMigration implements \Phig\MigratableInterface
{
	public function up()
	{
		return 'M20121222114448::up';
	}

	public function down()
	{
		return 'M20121222114448::down';
	}
}