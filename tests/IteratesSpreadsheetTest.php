<?php

namespace Glhd\ConveyorBelt\Tests;

use Carbon\Carbon;
use Glhd\ConveyorBelt\Tests\Commands\TestSpreadsheetCommand;
use Glhd\ConveyorBelt\Tests\Concerns\CallsTestCommands;
use Illuminate\Support\Facades\Date;
use RuntimeException;

class IteratesSpreadsheetTest extends TestCase
{
	use CallsTestCommands;
	
	/** @dataProvider dataProvider */
	public function test_it_reads_spreadsheets(string $filename, bool $step, $exceptions): void
	{
		$expectations = [
			(object) ['full_name' => 'Chris Morrell', 'company' => 'Galahad, Inc.', 'quote' => '"I hate final classes."', 'quoted_at' => '2021-01-01'],
			(object) ['full_name' => 'Bogdan Kharchenko', 'company' => 'Galahad, Inc.', 'quote' => '"It works."', 'quoted_at' => '2020-01-15'],
			(object) ['full_name' => 'Mohamed Said', 'company' => 'Laravel LLC', 'quote' => '', 'quoted_at' => ''],
			(object) ['full_name' => 'Taylor Otwell', 'company' => 'Laravel LLC', 'quote' => '"No plans to merge."', 'quoted_at' => '2019-03-04'],
		];
		
		$this->registerHandleRowCallback(function($row) use (&$expectations, $exceptions) {
			$expected = array_shift($expectations);
			$this->assertEquals($this->normalizeData($expected), $this->normalizeData($row));
			
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
	
	protected function normalizeData($data)
	{
		if (! empty($data->quoted_at)) {
			if (! ($data->quoted_at instanceof Carbon)) {
				$data->quoted_at = Date::parse($data->quoted_at);
			}
			
			$data->quoted_at = $data->quoted_at->format('Y-m-d');
		}
			
		return $data;
	}
}
