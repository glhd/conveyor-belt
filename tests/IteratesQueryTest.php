<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestCountableQueryCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestQueryCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use Glhd\ConveyorBelt\Tests\Concerns\TestsDatabaseTransactions;
use Glhd\ConveyorBelt\Tests\Models\User;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use SqlFormatter;

class IteratesQueryTest extends DatabaseTestCase
{
	use TestsDatabaseTransactions;
	use CallsTestCommands;
	
	#[DataProvider('dataProvider')]
	public function test_it_iterates_database_queries(string $case, bool $step, $exceptions, bool $transaction): void
	{
		$expectations = [
			'Bogdan Kharchenko',
			'Chris Morrell',
			'Mohamed Said',
			'Taylor Otwell',
		];
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $case, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row->name);
			
			if ('eloquent' === $case) {
				$this->assertInstanceOf(User::class, $row);
			}
			
			if ($exceptions) {
				$this->triggerExceptionAfterTimes(1);
			}
		});
		
		$this->callTestCommand(TestQueryCommand::class)
			->withArgument('case', $case)
			->withOption('transaction', $transaction)
			->withStepMode($step)
			->expectingSuccessfulReturnCode(false === $exceptions)
			->throwingExceptions('throw' === $exceptions)
			->run();
		
		if ($transaction) {
			$this->assertDatabaseTransactionWasCommitted();
		}
		
		$this->assertEmpty($expectations);
		$this->assertHookMethodsWereCalledInExpectedOrder();
	}
	
	public static function dataProvider()
	{
		return static::getDataProvider(
			['eloquent', 'base'],
			['' => false, 'step mode' => true],
			['' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
			['' => false, 'in transaction' => true],
		);
	}
	
	public function test_dump_sql(): void
	{
		$formatted = SqlFormatter::format('select * from "users" order by "name" asc');
		
		$this->artisan(TestQueryCommand::class, ['case' => 'eloquent', '--dump-sql' => true])
			->expectsOutput($formatted)
			->assertFailed();
	}
	
	public function test_belt_count_is_used_when_command_is_not_countable(): void
	{
		$this->artisan(TestQueryCommand::class, ['case' => 'eloquent'])
			->expectsOutput('Processing 4 records…')
			->assertSuccessful();
	}
	
	public function test_command_count_overrides_belt_count(): void
	{
		$handled = 0;
		$this->registerHandleRowCallback(function() use (&$handled) {
			$handled++;
		});
		
		DB::enableQueryLog();
		
		$this->artisan(TestCountableQueryCommand::class, ['case' => 'eloquent', '--count' => 10])
			->expectsOutput('Processing 10 records…')
			->doesntExpectOutput('Processing 4 records…')
			->assertSuccessful()
			->run();
		
		// The query still yields the 4 seeded users; only the reported count changed
		$this->assertEquals(4, $handled);
		
		// The belt's own COUNT(*) query should never have been executed
		$count_queries = collect(DB::getQueryLog())
			->filter(fn($log) => str_contains(strtolower($log['query']), 'count('));
		
		$this->assertTrue($count_queries->isEmpty(), 'Expected the belt count query to be skipped.');
	}
	
	public function test_command_count_of_zero_shows_no_matches_message(): void
	{
		$this->artisan(TestCountableQueryCommand::class, ['case' => 'eloquent', '--count' => 0])
			->expectsOutput('There are no records that match your query.')
			->doesntExpectOutputToContain('Processing')
			->assertSuccessful();
	}
}
