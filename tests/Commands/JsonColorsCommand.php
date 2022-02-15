<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesJson;
use Illuminate\Console\Command;

class JsonColorsCommand extends Command
{
	use IteratesJson;
	
	protected $signature = 'colors:json';
}
