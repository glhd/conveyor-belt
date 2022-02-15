<?php

namespace Glhd\ConveyorBelt\Belts;

use Countable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use JsonMachine\Items;
use SqlFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class JsonBelt extends ConveyorBelt
{
	protected function collect(): Enumerable
	{
		return new LazyCollection($this->getItems());
	}
	
	protected function getItems(): Items
	{
		if (method_exists($this->command, 'jsonFile')) {
			return Items::fromFile($this->command->jsonFile());
		}
		
		if (method_exists($this->command, 'jsonData')) {
			return Items::fromIterable($this->command->jsonData());
		}
		
		$basename = class_basename($this->command);
		$this->abort("You must implement {$basename}::jsonFile() or {$basename}::jsonData()", Command::INVALID);
	}
	
	protected function getItemsConfig(): array
	{
		$config = [];
		
		if (method_exists($this->command, 'jsonPointer')) {
			$config['pointer'] = $this->command->jsonPointer();
		}
		
		return $config;
	}
}
