<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesJson;

class TestJsonFileCommand extends TestCommand
{
	use IteratesJson;
	
	public $collect_exceptions = true;
	
	public $json_pointer = null;
	
	protected $signature = 'test:json-file {filename} {--throw} {--pointer=}';
	
	public function beforeFirstRow(): void
	{
		$this->collect_exceptions = ! $this->option('throw');
		$this->json_pointer = $this->option('pointer');
		
		$this->callTestCallback();
	}
}
