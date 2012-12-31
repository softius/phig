<?php

namespace Phig;

/**
 * Migratable interface defines the methods currently required as part of the 
 * migration (up) and rollback (down) process.
 */
interface MigratableInterface 
{
	/**
	 * Called when executing migration
	 */
	public function up();

	/**
	 * Called when executing rollback
	 */
	public function down();
}