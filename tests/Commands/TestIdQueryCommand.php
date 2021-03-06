<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesIdQuery;
use Glhd\ConveyorBelt\Tests\Models\User;

class TestIdQueryCommand extends TestCommand
{
	use IteratesIdQuery;
	
	public $collect_exceptions = true;
	
	public $use_transaction = false;
	
	protected $signature = 'test:id-query {case} {--throw} {--transaction}';
	
	public function beforeFirstRow(): void
	{
		$this->collect_exceptions = ! $this->option('throw');
		$this->use_transaction = true === $this->option('transaction');
		
		$this->callTestCallback();
	}
	
	public function query()
	{
		switch ($this->argument('case')) {
			case 'eloquent':
				return User::query()->orderBy('id');
			case 'base':
				return User::query()->toBase()->orderBy('id');
		}
		
		$this->abort('Invalid case.');
	}
}
