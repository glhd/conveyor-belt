<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\EnumerableBelt;
use Illuminate\Support\Enumerable;

/**
 * @property \Glhd\ConveyorBelt\Belts\EnumerableBelt $conveyor_belt
 */
trait IteratesEnumerable
{
	use IteratesData;
	
	abstract public function collect(): Enumerable;
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new EnumerableBelt($this);
	}
}
