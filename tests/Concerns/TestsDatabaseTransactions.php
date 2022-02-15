<?php

namespace Glhd\ConveyorBelt\Tests\Concerns;

use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;

trait TestsDatabaseTransactions
{
	protected array $transaction_events = [
		TransactionBeginning::class => false,
		TransactionCommitted::class => false,
		TransactionRolledBack::class => false,
	];
	
	/** @before */
	public function setUpTestsDatabaseTransactions()
	{
		$this->afterApplicationCreated(function() {
			$dispatcher = DB::getEventDispatcher();
			$dispatcher->listen(array_keys($this->transaction_events), function($event) {
				$this->transaction_events[get_class($event)] = true;
			});
		});
	}
	
	protected function assertDatabaseTransactionWasCommitted()
	{
		$this->assertTrue($this->transaction_events[TransactionBeginning::class]);
		$this->assertTrue($this->transaction_events[TransactionCommitted::class]);
		$this->assertFalse($this->transaction_events[TransactionRolledBack::class]);
	}
}
