<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesSpreadsheet;
use Illuminate\Console\Command;

class TestSpreadsheetCommand extends Command
{
	use IteratesSpreadsheet;
	
	public $collect_exceptions = true;
	
	protected $signature = 'test:spreadsheet {filename} {--throw}';
	
	public function beforeFirstRow(): void
	{
		$this->collect_exceptions = ! $this->option('throw');
	}
	
	public function handleRow(\stdClass $item)
	{
		$handler = app('tests.row_handler');
		$handler($item);
	}
}
