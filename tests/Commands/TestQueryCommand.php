<?php

namespace Glhd\ConveyorBelt\Tests\Commands;

use Glhd\ConveyorBelt\IteratesQuery;
use Glhd\ConveyorBelt\Tests\Models\User;
use Illuminate\Console\Command;

class TestQueryCommand extends Command
{
	use IteratesQuery;
	
	public $collect_exceptions = true;
	
	public $use_transaction = false;
	
	protected $signature = 'test:query {case} {--throw} {--transaction}';
	
	public function beforeFirstRow(): void
	{
		$this->collect_exceptions = ! $this->option('throw');
		$this->use_transaction = true === $this->option('transaction');
	}
	
	public function query()
	{
		switch ($this->argument('case')) {
			case 'eloquent':
				return User::query()->orderBy('name');
			case 'base':
				return User::query()->toBase()->orderBy('name');
		}
		
		$this->abort('Invalid case.');
	}
	
	public function handleRow($item)
	{
		$handler = app('tests.row_handler');
		$handler($item);
	}
}
