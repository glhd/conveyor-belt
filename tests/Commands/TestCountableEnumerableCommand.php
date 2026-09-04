<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Countable;

class TestCountableEnumerableCommand extends TestEnumerableCommand implements Countable
{
	protected $signature = 'test:countable-enumerable {data} {--count=} {--throw}';
	
	public function count(): int
	{
		return (int) $this->option('count');
	}
}
