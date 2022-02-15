<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestQueryCommand;
use Glhd\ConveyorBelt\Tests\Concerns\TestsDatabaseTransactions;
use Glhd\ConveyorBelt\Tests\Concerns\TestsStepMode;
use Glhd\ConveyorBelt\Tests\Models\User;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SqlFormatter;

class IteratesQueryTest extends DatabaseTestCase
{
	use TestsDatabaseTransactions;
	use TestsStepMode;
	
	/** @dataProvider dataProvider */
	public function test_it_iterates_database_queries(string $case, bool $step, $exceptions, bool $transaction): void
	{
		$expectations = [
			'Bogdan Kharchenko',
			'Chris Morrell',
			'Mohamed Said',
			'Taylor Otwell',
		];
		
		$this->app->instance('tests.row_handler', function($row) use (&$expectations, $case, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row->name);
			
			if ('eloquent' === $case) {
				$this->assertInstanceOf(User::class, $row);
			}
			
			if ($exceptions && 'Chris Morrell' === $row->name) {
				throw new RuntimeException('This should be caught.');
			}
		});
		
		if ('throw' === $exceptions) {
			$this->expectException(RuntimeException::class);
		}
		
		$command = $this->artisan(TestQueryCommand::class, [
			'case' => $case,
			'--step' => $step,
			'--throw' => 'throw' === $exceptions,
			'--transaction' => $transaction,
		]);
		
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
		
		$command->run();
		
		if ($transaction) {
			$this->assertDatabaseTransactionWasCommitted();
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
	
	public function test_dump_sql(): void
	{
		$formatted = SqlFormatter::format('select * from "users" order by "name" asc');
		
		$this->artisan(TestQueryCommand::class, ['case' => 'eloquent', '--dump-sql' => true])
			->expectsOutput($formatted)
			->assertFailed();
	}
}
