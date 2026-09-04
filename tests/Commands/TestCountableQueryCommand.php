<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Countable;

class TestCountableQueryCommand extends TestQueryCommand implements Countable
{
	protected $signature = 'test:countable-query {case} {--count=} {--throw} {--transaction}';
	
	public function count(): int
	{
		return (int) $this->option('count');
	}
}
