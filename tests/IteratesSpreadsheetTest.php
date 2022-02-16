<?php

namespace Glhd\ConveyorBelt\Tests;

use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use RuntimeException;

class IteratesSpreadsheetTest extends TestCase
{
	use CallsTestCommands;
	
	/** @dataProvider dataProvider */
	public function test_it_reads_spreadsheets(string $filename, bool $step, $exceptions): void
	{
		// FIXME: Test dates
		
		$expectations = [
			(object) ['full_name' => 'Chris Morrell', 'company' => 'Galahad, Inc.', 'quote' => '"I hate final classes."'],
			(object) ['full_name' => 'Bogdan Kharchenko', 'company' => 'Galahad, Inc.', 'quote' => '"It works."'],
			(object) ['full_name' => 'Mohamed Said', 'company' => 'Laravel LLC', 'quote' => ''],
			(object) ['full_name' => 'Taylor Otwell', 'company' => 'Laravel LLC', 'quote' => '"No plans to merge."'],
		];
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($expected, $row);
			
			if ($exceptions && 'Bogdan Kharchenko' === $row->full_name) {
				throw new RuntimeException('This should be caught.');
			}
		});
		
		$this->callTestCommand(TestSpreadsheetCommand::class)
			->withArgument('filename', $filename)
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
			['CSV' => __DIR__.'/sources/people.csv', 'Excel' => __DIR__.'/sources/people.xlsx'],
			['' => false, 'step mode' => true],
			['' => false, 'throw exceptions' => 'throw', 'collect exceptions' => 'collect'],
		);
	}
}
