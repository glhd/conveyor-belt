<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestIdQueryCommand;
use Glhd\ConveyorBelt\Tests\Models\User;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IteratesIdQueryTest extends DatabaseTestCase
{
	/** @dataProvider dataProvider */
	public function test_it_iterates_database_queries(string $case, bool $step, $exceptions, bool $transaction): void
	{
		$events = [
			TransactionBeginning::class => false,
			TransactionCommitted::class => false,
			TransactionRolledBack::class => false,
		];
		
		if ($transaction) {
			$dispatcher = DB::getEventDispatcher();
			$dispatcher->listen(array_keys($events), function($event) use (&$events) {
				$events[get_class($event)] = true;
			});
		}
		
		$expectations = [
			'Chris Morrell',
			'Bogdan Kharchenko',
			'Taylor Otwell',
			'Mohamed Said',
		];
		
		$this->app->instance('tests.row_handler', function($row) use (&$expectations, $case, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row->name);
			
			if ('eloquent' === $case) {
				$this->assertInstanceOf(User::class, $row);
			}
			
			if ($exceptions && 'Bogdan Kharchenko' === $row->name) {
				throw new RuntimeException('This should be caught.');
			}
		});
		
		if ('throw' === $exceptions) {
			$this->expectException(RuntimeException::class);
		}
		
		$command = $this->artisan(TestIdQueryCommand::class, [
			'case' => $case,
			'--step' => $step,
			'--throw' => 'throw' === $exceptions,
			'--transaction' => $transaction,
		]);
		
		if ($step && 'throw' === $exceptions) {
			// If we're throwing exceptions, we'll only have 1 successful iteration
			$command->expectsQuestion('Continue?', true);
		} elseif ($step) {
			// Otherwise we should have 4 iterations
			foreach (range(1, 4) as $_) {
				$command->expectsQuestion('Continue?', true);
			}
		}
		
		if ($exceptions) {
			$command->assertFailed();
		} else {
			$command->assertSuccessful();
		}
		
		$command->run();
		
		if ($transaction) {
			$this->assertTrue($events[TransactionBeginning::class]);
			$this->assertTrue($events[TransactionCommitted::class]);
			$this->assertFalse($events[TransactionRolledBack::class]);
		}
		
		$this->assertEmpty($expectations);
	}
	
	public function dataProvider()
	{
		$cases = [
			'eloquent',
			'base',
		];
		
		foreach ($cases as $case) {
			foreach ([false, true] as $step) {
				foreach ([false, 'throw', 'collect'] as $exceptions) {
					foreach ([false, true] as $transaction) {
						$label = (implode('; ', array_filter([
							$case,
							$step
								? 'step mode'
								: null,
							$exceptions
								? "{$exceptions} exceptions"
								: null,
							$transaction
								? 'in transaction'
								: null,
						])));
						
						yield $label => [$case, $step, $exceptions, $transaction];
					}
				}
			}
		}
	}
}
