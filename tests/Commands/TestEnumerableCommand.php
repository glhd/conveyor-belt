<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesEnumerable;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;

class TestEnumerableCommand extends TestCommand
{
	use IteratesEnumerable;
	
	protected $signature = 'test:enumerable {data} {--throw}';
	
	public function shouldCollectExceptions(): bool
	{
		return ! $this->option('throw');
	}
	
	public function collect(): Enumerable
	{
		return new LazyCollection(function() {
			$data = json_decode($this->argument('data'), false, 512, JSON_THROW_ON_ERROR);
			
			foreach ($data as $datum) {
				yield $datum;
			}
		});
	}
}
