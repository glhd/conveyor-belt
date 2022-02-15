<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use Illuminate\Testing\PendingCommand;
use RuntimeException;

trait TestsCommonCommandVariations
{
	use TestsStepMode;
	
	protected function setUpCommandWithCommonAssertions($exceptions, $step, $command, array $args = []): PendingCommand
	{
		if ('throw' === $exceptions) {
			$this->expectException(RuntimeException::class);
		}
		
		$command = $this->artisan($command, array_merge([
			'--step' => $step,
			'--throw' => 'throw' === $exceptions,
		], $args));
		
		if ($step && 'throw' === $exceptions) {
			// If we're throwing exceptions, we'll only have 1 successful iteration
			$this->assertStepCount($command, 1);
		} elseif ($step) {
			// Otherwise we should have 4 iterations
			$this->assertStepCount($command, 4);
		}
		
		if ($exceptions) {
			$command->assertFailed();
		} else {
			$command->assertSuccessful();
		}
		
		return $command;
	}
}
