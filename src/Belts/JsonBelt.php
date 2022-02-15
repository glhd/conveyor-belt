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

/**
 * @property \Glhd\ConveyorBelt\IteratesJson|\Symfony\Component\Console\Command\Command $command
 */
class JsonBelt extends ConveyorBelt
{
	protected function collect(): Enumerable
	{
		return new LazyCollection($this->command->getItems($this->getItemsOptions()));
	}
	
	protected function getItemsOptions(): array
	{
		$config = [];
		
		if ($pointer = $this->command->getJsonPointer()) {
			$config['pointer'] = $pointer;
		}
		
		return $config;
	}
}
