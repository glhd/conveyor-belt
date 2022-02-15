<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\JsonBelt;

/**
 * @property JsonBelt $conveyor_belt
 * @method string jsonFile()
 * @method \Generator jsonData()
 * @method string|array jsonPointer()
 */
trait IteratesJson
{
	use IteratesData;
	
	protected function makeConveyorBelt(): ConveyorBelt
	{
		return new JsonBelt($this);
	}
}
