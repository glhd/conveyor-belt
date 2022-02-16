<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use RuntimeException;

trait ArtificiallyFails
{
	protected int $iteration_count_for_exception_trigger = 0;
	
	/** @before */
	public function resetArtificiallyFailsCounter()
	{
		$this->iteration_count_for_exception_trigger = 0;
	}
	
	protected function triggerExceptionAfterTimes(int $times, string $exception = RuntimeException::class, string $message = 'Artificial failure.')
	{
		if ($this->iteration_count_for_exception_trigger <= $times) {
			$this->iteration_count_for_exception_trigger++;
		}
		
		if ($this->iteration_count_for_exception_trigger > $times) {
			dump('throwing '.$exception);
			throw new $exception($message);
		}
	}
}
