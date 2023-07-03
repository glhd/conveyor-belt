<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestEnumerableCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;

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
	
	/** @dataProvider dataProvider */
	public function test_it_can_filter_rows($exceptions, $step): void
	{
		$data = [
			'A1',
			'A2',
			'B1',
			'B2',
			'C1',
			'C2',
		];
		
		$expectations = [
			'A1',
			'A2',
			'C1',
			'C2',
		];
		
		$filtered = false;
		$rejected = false;
		
		$this->registerFilterRowCallback(function($row) use (&$filtered) {
			if ('B1' === $row) {
				$filtered = true;
				return false;
			}
		});
		
		$this->registerRejectRowCallback(function($row) use (&$rejected) {
			if ('B2' === $row) {
				$rejected = true;
				return true;
			}
		});
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row);
			
			if ($exceptions) {
				$this->triggerExceptionAfterTimes(1);
			}
		});
		
		$this->callTestCommand(TestEnumerableCommand::class)
			->withArgument('data', json_encode($data, JSON_THROW_ON_ERROR))
			->withStepMode($step)
			->expectingSuccessfulReturnCode(false === $exceptions)
			->throwingExceptions('throw' === $exceptions)
			->run();
		
		$this->assertEmpty($expectations);
		$this->assertTrue($filtered);
		$this->assertTrue($rejected);
	}
	
	public function dataProvider()
	{
		return $this->getDataProvider(
			['no exceptions' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
			['' => false, 'step mode' => true],
		);
	}
}
