<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesJson;

class TestJsonEndpointCommand extends TestCommand
{
	use IteratesJson;
	
	public $json_pointer = '/data/equipment';
	
	public $json_endpoint = 'https://botw-compendium.herokuapp.com/api/v2/all';
	
	protected $signature = 'test:json-endpoint {--throw}';
	
	public function shouldCollectExceptions(): bool
	{
		return ! $this->option('throw');
	}
}
