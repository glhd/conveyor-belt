<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestIdQueryCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use Glhd\ConveyorBelt\Tests\Concerns\TestsDatabaseTransactions;
use Glhd\ConveyorBelt\Tests\Models\User;

class IteratesIdQueryTest extends DatabaseTestCase
{
	use TestsDatabaseTransactions;
	use CallsTestCommands;
	
	/** @dataProvider dataProvider */
	public function test_it_iterates_database_queries(string $case, bool $step, $exceptions, bool $transaction): void
	{
		$expectations = [
			'Chris Morrell',
			'Bogdan Kharchenko',
			'Taylor Otwell',
			'Mohamed Said',
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
		
		$this->callTestCommand(TestIdQueryCommand::class)
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
}
