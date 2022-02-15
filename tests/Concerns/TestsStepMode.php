<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use Illuminate\Testing\PendingCommand;

trait TestsStepMode
{
	protected function assertStepCount(PendingCommand $command, int $times)
	{
		foreach (range(1, $times) as $_) {
			$command->expectsQuestion('Continue?', true);
		}
	}
}
