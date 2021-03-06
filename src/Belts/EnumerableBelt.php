<?php

namespace Glhd\ConveyorBelt\Belts;

use Illuminate\Support\Enumerable;

/**
 * @property \Glhd\ConveyorBelt\IteratesEnumerable|\Symfony\Component\Console\Command\Command $command
 */
class EnumerableBelt extends ConveyorBelt
{
	protected function collect(): Enumerable
	{
		return $this->command->collect();
	}
}
