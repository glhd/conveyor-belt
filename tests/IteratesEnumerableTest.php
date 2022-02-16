<?php

namespace Glhd\ConveyorBelt\Tests;

use Carbon\Carbon;
use Glhd\ConveyorBelt\Tests\Commands\TestEnumerableCommand;
use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use Illuminate\Support\Facades\Date;
use RuntimeException;

class IteratesEnumerableTest extends TestCase
{
	use CallsTestCommands;
	
	/** @dataProvider dataProvider */
	public function test_it_can_handle_generic_enumerables($exceptions, $step): void
	{
		$expectations = [
			'Chris Morrell',
			'Bogdan Kharchenko',
			'Mohamed Said',
			'Taylor Otwell',
		];
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row);
			
			if ($exceptions) {
				$this->triggerExceptionAfterTimes(1);
			}
		});
		
		$this->callTestCommand(TestEnumerableCommand::class)
			->withArgument('data', json_encode($expectations, JSON_THROW_ON_ERROR))
			->withStepMode($step)
			->expectingSuccessfulReturnCode(false === $exceptions)
			->throwingExceptions('throw' === $exceptions)
			->run();
		
		$this->assertEmpty($expectations);
		$this->assertHookMethodsWereCalledInExpectedOrder();
	}
	
	public function dataProvider()
	{
		return $this->getDataProvider(
			['no exceptions' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
			['' => false, 'step mode' => true],
		);
	}
}
