<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\Tests\Commands\Concerns\CallsTestCallbacks;
use Illuminate\Console\Command;

abstract class TestCommand extends Command
{
	use CallsTestCallbacks;
}
