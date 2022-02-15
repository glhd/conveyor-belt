<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesSpreadsheet;

class TestSpreadsheetCommand extends TestCommand
{
	use IteratesSpreadsheet;
	
	public $collect_exceptions = true;
	
	protected $signature = 'test:spreadsheet {filename} {--throw}';
	
	public function beforeFirstRow(): void
	{
		$this->collect_exceptions = ! $this->option('throw');
		
		$this->callTestCallback();
	}
}
