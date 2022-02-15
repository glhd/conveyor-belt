<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use Glhd\ConveyorBelt\Tests\PendingConveyorBeltCommand;

trait CallsTestCommands
{
	protected function callTestCommand(string $command, array $parameters): PendingConveyorBeltCommand
	{
		return new PendingConveyorBeltCommand($this, $this->app, $command, $parameters);
	}
}
