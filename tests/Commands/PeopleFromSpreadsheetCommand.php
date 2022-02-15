<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesSpreadsheet;
use Illuminate\Console\Command;

class PeopleFromSpreadsheetCommand extends Command
{
	use IteratesSpreadsheet;
	
	public static array $last_execution = [];
	
	protected $signature = 'people:spreadsheet {--format=csv}';
	
	public function getSpreadsheetFilename(): string
	{
		return __DIR__.'/../sources/people.'.$this->option('format');
	}
	
	public function beforeFirstRow(): void
	{
		static::$last_execution = [];
	}
	
	public function handleRow(\stdClass $item)
	{
		static::$last_execution[] = "{$item->full_name} from {$item->company} says: {$item->quote}";
	}
}
