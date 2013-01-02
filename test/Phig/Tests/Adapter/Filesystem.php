<?php

namespace Phig\Tests\Adapter;

class Filesystem extends \Phig\Adapter\Filesystem
{
	public function __destruct()
	{
		// do nothing - we dont want to overwrite the test file
	}
}