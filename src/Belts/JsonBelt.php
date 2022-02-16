<?php

namespace Glhd\ConveyorBelt\Belts;

use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;

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
