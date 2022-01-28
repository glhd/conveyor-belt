<?php

namespace Glhd\ConveyorBelt\Support;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class CollectedException
{
	public Throwable $exception;
	
	public ?string $key = null;
	
	public function __construct(Throwable $exception, $item = null)
	{
		$this->exception = $exception;
		
		if ($item instanceof Model) {
			$this->key = $item->getKey();
		}
	}
	
	public function __toString()
	{
		return $this->exception->getMessage();
	}
}
