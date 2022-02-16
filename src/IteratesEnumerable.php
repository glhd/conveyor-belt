<?php

namespace Glhd\ConveyorBelt;

use Glhd\ConveyorBelt\Belts\ConveyorBelt;
use Glhd\ConveyorBelt\Belts\EnumerableBelt;
use Glhd\ConveyorBelt\Belts\JsonBelt;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Http;
use JsonMachine\Items;

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
