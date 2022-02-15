<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesJson;
use Illuminate\Console\Command;

class TestJsonFileCommand extends Command
{
	use IteratesJson;
	
	public $collect_exceptions = true;

	public $json_pointer = null;
	
	protected $signature = 'test:json-file {filename} {--throw} {--pointer=}';
	
	public function beforeFirstRow(): void
	{
		$this->collect_exceptions = ! $this->option('throw');
		$this->json_pointer = $this->option('pointer');
	}
	
	public function handleRow(\stdClass $item)
	{
		$handler = app('tests.row_handler');
		$handler($item);
	}
}
